<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$app = JFactory::getApplication();

// Multiload prevention
if (!isset($checkMultiload_DD_GMaps_Module))
{
	$app->enqueueMessage(
		JText::_('MOD_DD_GMAPS_MODULE_WARNUNG_MODUL_EXISTS_ALREADY'), 'warning'
	);

	return false;
}

$doc = JFactory::getDocument();

// Include the functions only once
JLoader::register('ModDD_GMaps_Module_Helper', __DIR__ . '/helper.php');

// Check if plugin geocode is enabled
if (!JPluginHelper::getPlugin('system', 'dd_gmaps_locations_geocode'))
{
	$app->enqueueMessage(
		JText::_('MOD_DD_GMAPS_MODULE_WARNING_GEOCODE_PLUGIN_MUST_BE_ENABLED'), 'warning'
	);
}

$google_PlacesAPI = 'js?&libraries=places&v=3';
$google_PlacesAPI_Key = '&key=' . $params->get('google_api_key_js_places', '');

if (!ModDD_GMaps_Module_Helper::isset_Script($doc->_scripts, $google_PlacesAPI) && !empty($params->get('google_api_key_js_places')))
{
	// With API key
	$doc->addScript('https://maps.google.com/maps/api/' . $google_PlacesAPI . $google_PlacesAPI_Key);
}
elseif (!ModDD_GMaps_Module_Helper::isset_Script($doc->_scripts, $google_PlacesAPI))
{
	// Without API key
	$doc->addScript('https://maps.google.com/maps/api/' . $google_PlacesAPI);
}

$doc->addScript(JUri::base() . 'media/mod_dd_gmaps_module/js/markerclusterer_compiled.min.js');
$doc->addScript(JUri::base() . 'media/mod_dd_gmaps_module/js/dd_gmaps_module.min.js');
$doc->addStyleSheet(JUri::base() . 'media/mod_dd_gmaps_module/css/dd_gmaps_module.min.css');

require_once "modules/mod_dd_gmaps_module/inc/scriptheader.js.php";
require_once JModuleHelper::getLayoutPath('mod_dd_gmaps_module', $params->get('layout', 'default'));
