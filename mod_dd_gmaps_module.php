<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2011 - 2018 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

JLoader::register('ModDD_GMaps_Module_Helper', __DIR__ . '/helper.php');

$app = JFactory::getApplication();

// Multiload prevention todo J3.8
if (false)
{
	$app->enqueueMessage(
		JText::_('MOD_DD_GMAPS_MODULE_WARNUNG_MODUL_EXISTS_ALREADY'), 'warning'
	);

	return false;
}

// Check if plugin geocode is enabled
if (!JPluginHelper::getPlugin('system', 'dd_gmaps_locations_geocode'))
{
	$app->enqueueMessage(
		JText::_('MOD_DD_GMAPS_MODULE_WARNING_GEOCODE_PLUGIN_MUST_BE_ENABLED'), 'warning'
	);
}

// API key (try loading default from component)
if (ModDD_GMaps_Module_Helper::existsDDGMapsLocations())
{
	$API_Key = $params->get('google_api_key_js_places', JComponentHelper::getParams('com_dd_gmaps_locations')->get('google_api_key_js_places'));

	if (empty($API_Key))
	{
		$app->enqueueMessage(
			JText::_('MOD_DD_GMAPS_MODULE_API_KEY_REQUIRED_COMPONENT'), 'warning'
		);
	}
}
else
{
	$API_Key = $params->get('google_api_key_js_places', '');

	if (empty($API_Key))
	{
		$app->enqueueMessage(
			JText::_('MOD_DD_GMAPS_MODULE_API_KEY_REQUIRED'), 'warning'
		);
	}
}

$Places_API = 'js?&libraries=places&v=3';

$doc = JFactory::getDocument();

if (!ModDD_GMaps_Module_Helper::isset_Script($doc->_scripts, $Places_API))
{
	JHTML::_('script', 'https://maps.google.com/maps/api/' . $Places_API . '&key=' . $API_Key, array('relative' => false));
}

JHTML::_('script', 'mod_dd_gmaps_module/markerclusterer_compiled.min.js', array('version' => 'auto', 'relative' => true));
JHTML::_('script', 'mod_dd_gmaps_module/dd_gmaps_module.min.js', array('version' => 'auto', 'relative' => true));


// Check for a custom CSS file
JHtml::_('stylesheet', 'mod_dd_gmaps_module/user.css', array('version' => 'auto', 'relative' => true));

require_once "modules/mod_dd_gmaps_module/inc/scriptheader.js.php";
require_once JModuleHelper::getLayoutPath('mod_dd_gmaps_module', $params->get('layout', 'default'));
