<?php
/**
 * @version    1-1-0-0 // Y-m-d 2017-04-06
 * @author     HR IT-Solutions Florian Häusler https://www.hr-it-solutions.com
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
**/

defined('_JEXEC') or die;

$instance = new ModDD_GMaps_Module_Helper;

$isDDGMapsLocationsExtended = $instance->isDDGMapsLocationsExtended();

$items = $instance->getItems();

$sef_rewrite  = JFactory::getConfig()->get('sef_rewrite');
$active_alias = JFactory::getApplication()->getMenu()->getActive()->alias;

$varProducerIndex = 0;
?>
<style>
    #dd_gmaps {
        background-color: #e5e3df;
        height: auto;
        overflow: hidden;
        width: 100%;
        min-height: 320px;}
    }
    #dd_gmaps_overloader {
        background-color: #e5e3df;
        height: 450px;
        position: relative;
        margin-bottom: -450px;
        z-index: 9;
    }
</style>
<script type="text/javascript">

    var home = new google.maps.LatLng(48.0000000, 2.0000000);
    var map;
    var markers = [];

    var GMapsLocations = [
    <?php
    $i = 0;
    foreach ( $items as $item ):

        $title = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');

        if ($isDDGMapsLocationsExtended)
        {
	        $title_link = JRoute::_($sef_rewrite ? $active_alias . '/' . $item->alias : 'index.php?option=com_dd_gmaps_locations&view=profile&profile_id=' . $item->id);
	        $title = '<a href="' . $title_link .'">' . $title .'</a>';
        }
		?>
        {   lat:<?php echo $item->latitude; ?>, 
            lng:<?php echo $item->longitude; ?>,
            icon: "<?php echo JUri::base() ?>media/mod_dd_gmaps_module/img/marker.png",
            content:'<?php echo '<span class="info-content">' . $title . '<br>' . htmlspecialchars($item->street,ENT_QUOTES,'UTF-8') . '<br>' . htmlspecialchars($item->location,ENT_QUOTES,'UTF-8') . '</span>'; ?>'
        },<?php
    endforeach; ?>
    ];

    // Initializes InfoWindow global
    var infowindow = new google.maps.InfoWindow();

    function initialize() // Initializes Google Map
    {
        // Create map
        var googleMapOptions =
            {
                center: home,
                zoom: 4,
                panControl: true,
                zoomControl: true,
                scaleControl: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
        map = new google.maps.Map(document.getElementById("dd_gmaps"), googleMapOptions);

        // Add Locations
        var count = GMapsLocations.length; // Count Locations
        for(var i=0;i<count ;i++){
            var latLng = new google.maps.LatLng(
                GMapsLocations[i].lat,
                GMapsLocations[i].lng);
            var marker = new google.maps.Marker({
                position: latLng,
                map: map,
                icon: GMapsLocations[i].icon
            });

            google.maps.event.addListener(marker, 'mousedown', (function(marker, i) {
                var content = GMapsLocations[i].content;

                return function() {
                    infowindow.setContent(content);
                    infowindow.open(map, marker);
                }
            })(marker, i));

            markers.push(marker);
        }

        // Cluster Marker Option
        var clusterStyles = [
            {
                textColor: 'white',
                url: '<?php echo JUri::base() ?>media/mod_dd_gmaps_module/img/marker_cluster.png',
                height: 55,
                width: 56,
                backgroundPosition: '0 -1px'
            }];
        var mcOptions = {gridSize: 30, maxZoom: 14,styles: clusterStyles};
        var markerCluster = new MarkerClusterer(map, markers, mcOptions);
    }

    google.maps.event.addDomListener(window, 'load', initialize);

    function launchInfoWindow(i) {

        // Scroll to top
        window.scroll(0, 0);

        // Config and add marker
        var pushLocation = new google.maps.LatLng(GMapsLocations[i].lat, GMapsLocations[i].lng);
        var marker = new google.maps.Marker({ // add marker
            position:pushLocation,
            map: map,
            draggable:true,
            // animation: google.maps.Animation.DROP, // Animation adding feature
            icon: GMapsLocations[i].icon
        });

        map.setCenter(marker.getPosition());

        // Config and open infoWindows
        infowindow.setContent(GMapsLocations[i].content);
        infowindow.open(map, marker);
    }
</script>
<div id="dd_gmaps">
    <p class="dd_gmaps_loader">Kartendaten werden geladen...</p>
</div>
<div class="clear"></div>