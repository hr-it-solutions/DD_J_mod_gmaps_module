<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die();

$app      = JFactory::getApplication();
$instance = new ModDD_GMaps_Module_Helper;
$input    = $app->input;

if (!$instance->existsDDGMapsLocations())
{
	$extended_only = $extended_location = 0;
}
else
{
	$extended_location = $params->get('extended_location');
	$extended_only     = $params->get('only_extended_locations');
}

$isDDGMapsLocationsExtended = $instance->isDDGMapsLocationsExtended();
$items                      = $instance->getItems($extended_location, $extended_only);

/**
 * Sanitize output function by Paul Phillips
 * http://stackoverflow.com/questions/6225351/how-to-minify-php-page-html-output#answer-6225706
 *
 * Output minimization
 * Adds the ScriptDeclaration to header
 *
 * @param   string  $buffer  javascript
 *
 * @return boolean
 *
 * @sincer Version 1.1.0.8
 **/
function Sanitize_output($buffer)
{
	$search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
	$replace = array('>', '<', '\\1', '');
	$buffer = preg_replace($search, $replace, $buffer);

	JFactory::getDocument()->addScriptDeclaration($buffer);

	return true;
}

/**
 * @package    DD_GMaps_Module
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 *
 * Sanitize_output
 * @sincer   Version 1.1.0.8
 **/
ob_start("Sanitize_output");
?>

jQuery(document).ready(function () {
    init_default_itemsJS();
});

var home = new google.maps.LatLng(<?php echo $instance->paramLatLong($params); ?>),
    settingsClusterIcon = '<?php echo $instance->paramClusterMarkerImage($params); ?>',
    settingsZoomLevel = <?php  echo (int) $params->get('zoomlevel') ?>,

    <?php // Build - Locations array ?>
    GMapsLocations = [
		<?php
		foreach ( $items as $i => $item ):
		$title = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');

		if (($isDDGMapsLocationsExtended || $extended_location) && $item->id != 0)
		{
			if (isset($item->ext_c_id) && $item->ext_c_id !== '0' && isset($item->extc_link))
			{
				// Ext C 3rd Party Links
				$title_link = JRoute::_($item->extc_link);
			}
			else
			{
				$title_link = JRoute::_('index.php?option=com_dd_gmaps_locations&view=profile&id=' . (int) $item->id
                    . ':' . htmlspecialchars($item->alias, ENT_QUOTES, 'UTF-8'));
			}

			$title      = '<a href="' . $title_link . '">' . $title . '</a>';
		}

		if ($extended_location && isset($item->category_params) && json_decode($item->category_params)->image)
		{
			$imagefile = str_replace('\\', '/', json_decode($item->category_params)->image);
			$icon      = JUri::base() . $imagefile;
			$size      = getimagesize($imagefile);

			// Calculate height based on image width
			$height = round($size[1] / $size[0] * 30);
		}
		else
		{
			$icon = $instance->paramMarkerImage($params);
			$height = 42;
		}
		?>
        {

	        <?php
            if ($params->get('only_extended_locations') != '1' && $item->id == 0)
            {
	            $infoContent = $title;
                if(trim($params->get('infowindow_content')) != '')
                {
	                $infoContent = $params->get('infowindow_content') . $infoContent;
                }
                if ($params->get('infowindow_defaultaddress') == '1')
                {
                    $infoContent = $title . '<br>' . htmlspecialchars($item->street, ENT_QUOTES, 'UTF-8') .
                        '<br>' . htmlspecialchars($item->location, ENT_QUOTES, 'UTF-8') . $infoContent;
                }
            }
            else
            {
                $infoContent = $title . '<br>' . htmlspecialchars($item->street, ENT_QUOTES, 'UTF-8') .
                    '<br>' . htmlspecialchars($item->location, ENT_QUOTES, 'UTF-8');
            }
            ?>

            id:<?php echo isset($item->id) ? $item->id : 0; ?>,
            key:<?php echo $i; ?>,
            lat:<?php echo $item->latitude; ?>,
            lng:<?php echo $item->longitude; ?>,
            icon: {
                url: "<?php echo $icon; ?>",
                scaledSize: new google.maps.Size(30, <?php echo $height; ?>),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(0, 0)
            },
            content: '<?php echo '<span class="info-content">' . $infoContent . '</span>'; ?>'
        },<?php
		endforeach; ?>
    ];
    <?php // End - Loactions array ?>

<?php // Initialize Map ?>
var infowindow = new google.maps.InfoWindow();
google.maps.event.addDomListener(window, 'load', initialize);

<?php
// Info windows

// Geolocate info window launcher
if ($input->get("geolocate", "STRING") == "locate")
{
	$locationLatLng = explode(",", $input->get("locationLatLng", "", "STRING"));
	$lat            = substr($locationLatLng[0], 0, 10);
	$lng            = substr($locationLatLng[1], 0, 10);
	$content        = "<h2>" . JText::_('MOD_DD_GMAPS_MODULE_YOUR_LOCATION') . "</h2><b>" . JText::_('MOD_DD_GMAPS_MODULE_YOUR_LATITUDE') . ":</b> $lat<br><b>" . JText::_('MOD_DD_GMAPS_MODULE_YOUR_LONGITUDE') . ":</b> $lng";
	$zoom           = 9;
	$markertitle    = JText::_('MOD_DD_GMAPS_MODULE_YOUR_LOCATION');
	$markericon     = JUri::base() . 'media/mod_dd_gmaps_module/img/marker_position.png';
	echo "launchLocateInfoWindow($lat,$lng,'$content',$zoom,'$markertitle','$markericon');";
}

// Profile pages info window
if ($input->get('profile_id') != 0)
{
	echo 'setTimeout(function(){
            var profileObj = jQuery.grep(GMapsLocations, function(e){ return e.id == ' . $input->get('profile_id', 0) . '; });
            if(typeof profileObj[0] !== "undefined"){launchInfoWindow(profileObj[0].key)}
          }, 800);';
}

// Default address info window
elseif ($params->get('infowindow_opendefault') && $params->get('only_extended_locations') != '1')
{
	echo 'setTimeout(function(){
            var profileObj = jQuery.grep(GMapsLocations, function(e){ return e.id == 0; });
            if(typeof profileObj[0] !== "undefined"){launchInfoWindow(profileObj[0].key)}
          }, 800);';
}

ob_end_flush();
