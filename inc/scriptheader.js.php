<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR-IT-Solutions GmbH Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2011 - 2018 HR-IT-Solutions GmbH
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die();

JText::script('MOD_DD_GMAPS_MODULE');
JText::script('MOD_DD_GMAPS_MODULE_FULLSIZE');
JText::script('MOD_DD_GMAPS_MODULE_FULLSIZE_CLOSE');

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
 * DDSanitize output function by Paul Phillips
 * http://stackoverflow.com/questions/6225351/how-to-minify-php-page-html-output#answer-6225706
 *
 * Output minimization
 *
 * @param   string  $buffer  javascript
 *
 * @return boolean
 *
 * @sincer Version 1.1.0.8
 **/
function DDSanitize_output($buffer)
{
	$search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
	$replace = array('>', '<', '\\1', '');
	$buffer = preg_replace($search, $replace, $buffer);

	return $buffer;
}

/**
 * NotEmptNotFlag mehtod
 *
 * @param   string  $string  wether to check if string ist not empty and not flagged as empty
 *
 * @return  bool true if emptyFlag
 */
function DDNotEmptyFlag($string)
{
	if ($string !== '' && $string !== '⚑')
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * @package    DD_GMaps_Module
 * @author     HR-IT-Solutions GmbH Florian Häusler <info@hr-it-solutions.com>
 *
 * Sanitize_output
 * @sincer   Version 1.1.0.8
 **/

ob_start();

echo 'jQuery(document).ready(function () { init_default_itemsJS(); });';
echo 'var home = new google.maps.LatLng(' . $instance->paramLatLong($params) . '), ';
echo "settingsClusterIcon = '" . $instance->paramClusterMarkerImage($params) . "',";
echo 'settingsZoomLevel   = ' . (int) $params->get('zoomlevel', 4) . ',  ';
echo "ZoomLevelInfoWindow = " . (int) $params->get('zoomlevel_infowindow', 9) . ',';

// Build - Locations array
echo 'GMapsLocations = [ ';

foreach ( $items as $i => $item ):

    // InfoWindow - icon
    {
        if ($extended_location && isset($item->category_params) && json_decode($item->category_params)->image)
        {
            $imagefile = str_replace('\\', '/', json_decode($item->category_params)->image);
            $icon      = JUri::base() . $imagefile;
            $size      = getimagesize($imagefile);

            // Calculate height based on image width
            $height = round($size[1] / $size[0] * 22);
        }
        else
        {
            $icon = $instance->paramMarkerImage($params);
            $height = 32;
        }
    }

    // InfoWindow - content
    {
        $title = $item->title;

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

            $title = '<a href="' . $title_link . '">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</a>';
        }

        if ($params->get('only_extended_locations') != '1' && $item->id == 0 && $params->get('infowindow_defaultaddress') == '0')
        {
            // Default module address hidden case
            $addresses = '';
        }
        else
        {
            // Collect and prepare address contents
            $addresses = [];

            if (DDNotEmptyFlag($item->street)) {
                $addresses[] = $item->street;
            }

            if (DDNotEmptyFlag($item->zip) && DDNotEmptyFlag($item->location)) {
                $addresses[] = $item->zip . ' ' . $item->location;
            }
            elseif (DDNotEmptyFlag($item->zip)) {
                $addresses[] = $item->zip;
            }
            elseif (DDNotEmptyFlag($item->location)) {
                $addresses[] = $item->location;
            }

            if (DDNotEmptyFlag($item->federalstate)) {
                $addresses[] = $item->federalstate;
            }
        }

        $infoContent = '<div class="info-content">';

        if(is_array($addresses))
        {
            $count = count($addresses);

            if (DDNotEmptyFlag($title))
            {
                $infoContent .= $title . '<br>';
            }

            foreach ($addresses as $key => $address)
            {
                $infoContent .= htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
                if (--$count <= 0){ break; }
                $infoContent .= '<br>';
            }
        }
        else
        {
            if (DDNotEmptyFlag($title)) {
                $infoContent .= $title;
            }
        }

        // Add Module default address info windows content
        if ($params->get('only_extended_locations') != '1' && $item->id == 0 && trim($params->get('infowindow_content')) != '')
        {
            $infoContent .= $params->get('infowindow_content');
        }

        $infoContent .= '</div>';
    }

    echo '{';
    echo 'id:' . (isset($item->id) ? $item->id : 0) . ',';
    echo 'key:' . $i . ',';
    echo 'lat:' . $item->latitude . ',';
    echo 'lng:' . $item->longitude . ',';
    echo 'icon: {';
        echo "url: '" . $icon . "',";
        // This marker is 22 pixels wide by 32 pixels high.
        echo 'size: new google.maps.Size(22, ' . $height . '),';
        // The origin for this image is (0, 0).
        echo 'origin: new google.maps.Point(0, 0),';
        // The anchor for this image is the base of the img arrow at (11px (width / 2) is center bottom pointer and $height).
        echo 'scaledSize: new google.maps.Size(22, ' . $height . '),';
        echo 'anchor: new google.maps.Point(11, ' . $height . ')';
    echo '},';
    echo "content: '" . $infoContent . "' },";

endforeach;

echo ']; var infowindow = new google.maps.InfoWindow(); var styles = {this:';

    if($params->get('stylepack'))
    {
        require_once 'media/mod_dd_gmaps_module/js/styles/' . $params->get('stylepack') . '.json';
    }
    else
    {
        echo "null";
    }

echo '};';

if (!$params->get('eu_privay_mode'))
{
	echo 'google.maps.event.addDomListener(window, \'load\', initialize);';
}

// Info Windows

// Geolocate info window launcher
if ($input->get('geolocate', 'STRING') == 'locate')
{
	$locationLatLng = explode(',', $input->get('locationLatLng', '', 'STRING'));
	$lat            = substr($locationLatLng[0], 0, 10);
	$lng            = substr($locationLatLng[1], 0, 10);
	$content        = '<h2>' . JText::_('MOD_DD_GMAPS_MODULE_YOUR_LOCATION') . '</h2><b>' .
                        JText::_('MOD_DD_GMAPS_MODULE_YOUR_LATITUDE') . ':</b> ' . $lat . '<br><b>' .
                        JText::_('MOD_DD_GMAPS_MODULE_YOUR_LONGITUDE') . ':</b> ' . $lng;
	$markertitle    = JText::_('MOD_DD_GMAPS_MODULE_YOUR_LOCATION');
	$markericon     = JUri::base() . 'media/mod_dd_gmaps_module/img/marker_position.png';
	echo "launchLocateInfoWindow($lat, $lng, '$content', ZoomLevelInfoWindow, '$markertitle', '$markericon');";
}

// Profile pages info window
if ($input->get('profile_id') != 0)
{
	echo 'setTimeout(function(){';
	    echo 'var profileObj = jQuery.grep(GMapsLocations, function(e){ ';
	        echo 'return e.id == ' . $input->get('profile_id', 0) . '; ';
        echo '});';
        echo 'if(typeof profileObj[0] !== "undefined"){launchInfoWindow(profileObj[0].key, ZoomLevelInfoWindow)}';
    echo '}, 800);';
}

// Default address info window
elseif ($params->get('infowindow_opendefault') && $params->get('only_extended_locations') != '1')
{
	echo 'setTimeout(function(){';
	    echo 'var profileObj = jQuery.grep(GMapsLocations, function(e){ ';
	        echo 'return e.id == 0;';
        echo '});';
        echo 'if(typeof profileObj[0] !== "undefined"){launchInfoWindow(profileObj[0].key, ZoomLevelInfoWindow)}';
    echo'}, 800);';
}

$ScriptHeader = ob_get_contents();

$ScriptHeader = DDSanitize_output($ScriptHeader);

ob_end_clean();

$doc = JFactory::getDocument();

if (!$params->get('eu_privay_mode')){
	$doc->addScriptDeclaration($ScriptHeader);
} else {

	$ScriptHeader = '<script>' . str_replace('"','\"',$ScriptHeader) . '<\/script>';

	$doc->addScriptDeclaration(
		"jQuery(document).ready(function () {" .
			"jQuery('#dd_gmaps').on('click',function () {" .
				"jQuery('#dd_gmaps').html('');" .
				"jQuery.getScript('$mapsScript').done(function(){" .
					"jQuery('head').append(\"$ScriptHeader\");" .
					"initialize();" .
				"});" .
			"});" .
		"});"
	);
}