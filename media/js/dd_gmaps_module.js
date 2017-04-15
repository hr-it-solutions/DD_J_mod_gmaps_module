/**
 * @version    1-0-0-0 // Y-m-d 2017-04-06
 * @author     HR IT-Solutions Florian Häusler https://www.hr-it-solutions.com
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

var map;
var markers = [];

var init_default_itemsJS = function () {

    jQuery('.showOnMap').click(function (e) {

        var elementID = e.target.id.replace('showID','');
        launchInfoWindow(elementID);

        jQuery("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });

};

var initialize = function initialize() // Initializes Google Map
{
    // Create map
    var googleMapOptions =
        {
            center: home,
            zoom: settingsZoomLevel, // from settings
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
            url: settingsClusterIcon, // from settings
            height: 55,
            width: 56,
            backgroundPosition: '0 -1px'
        }];
    var mcOptions = {gridSize: 30, maxZoom: 14,styles: clusterStyles};
    var markerCluster = new MarkerClusterer(map, markers, mcOptions);
};

function launchInfoWindow(i) {
    setTimeout(function(){

        if (typeof map !== 'undefined') {

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
        else
        {
            launchInfoWindow(i)
        }
    }, 400);
}

function launchLocateInfoWindow(lat,lng,content,zoom,markertitle,merkericon) {
    setTimeout(function(){
        if (typeof map !== 'undefined')
        {
            // Scroll to top
            window.scroll(0, 0);

            // Config and add marker
            var pushLocation = new google.maps.LatLng(lat, lng);
            var marker = new google.maps.Marker({ // add marker
                position: pushLocation,
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP, // Animation adding feature
                title: markertitle,
                icon: merkericon
            });

            map.setZoom(zoom);
            map.setCenter(marker.getPosition());

            // Config and open infoWindows
            infowindow.setContent(content);
            infowindow.open(map, marker);
        }
        else
        {
            launchLocateInfoWindow(lat,lng,content,zoom,markertitle,merkericon)
        }
    }, 400);
}
