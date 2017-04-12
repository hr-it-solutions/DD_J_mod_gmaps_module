/**
 * @version    1-0-0-0 // Y-m-d 2017-04-06
 * @author     HR IT-Solutions Florian Häusler https://www.hr-it-solutions.com
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

var init_default_itemsJS = function () {

    jQuery('.showOnMap').click(function (e) {

        var elementID = e.target.id.replace('showID','');
        launchInfoWindow(elementID);

        jQuery("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });

};