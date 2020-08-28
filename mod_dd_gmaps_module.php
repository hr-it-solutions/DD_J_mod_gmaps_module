<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR-IT-Solutions GmbH Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2011 - 2020 HR-IT-Solutions GmbH
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\DD_GMaps_Module\Site\Helper\DD_GMaps_ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

$app = Factory::getApplication();

// Multiload prevention todo J3.8
if (false)
{
	$app->enqueueMessage(
		Text::_('MOD_DD_GMAPS_MODULE_WARNUNG_MODUL_EXISTS_ALREADY'), 'warning'
	);

	return false;
}

// Check if plugin geocode is enabled
if (!JPluginHelper::getPlugin('system', 'dd_gmaps_locations_geocode'))
{
	$app->enqueueMessage(
		Text::_('MOD_DD_GMAPS_MODULE_WARNING_GEOCODE_PLUGIN_MUST_BE_ENABLED'), 'warning'
	);
}

// API key (try loading default from component)
if (DD_GMaps_ModuleHelper::existsDDGMapsLocations())
{
	$API_Key = $params->get('google_api_key_js_places', ComponentHelper::getParams('com_dd_gmaps_locations')->get('google_api_key_js_places'));

	if (empty($API_Key))
	{
		$app->enqueueMessage(
			Text::_('MOD_DD_GMAPS_MODULE_API_KEY_REQUIRED_COMPONENT'), 'warning'
		);
	}
}
else
{
	$API_Key = $params->get('google_api_key_js_places', '');

	if (empty($API_Key))
	{
		$app->enqueueMessage(
			Text::_('MOD_DD_GMAPS_MODULE_API_KEY_REQUIRED'), 'warning'
		);
	}
}
$Places_API = 'js?&libraries=places&v=3';

$mapsScript = 'https://maps.google.com/maps/api/' . $Places_API . '&key=' . $API_Key;

$doc = Factory::getDocument();

if (!$params->get('eu_privay_mode') && !DD_GMaps_ModuleHelper::isset_Script($doc->_scripts, $Places_API))
{
	HTMLHelper::_('script', $mapsScript, array('relative' => false));
}

HTMLHelper::_('script', 'mod_dd_gmaps_module/markerclusterer_compiled.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'mod_dd_gmaps_module/dd_gmaps_module.min.js', array('version' => 'auto', 'relative' => true));

require_once "modules/mod_dd_gmaps_module/inc/scriptheader.js.php";

// Check for a custom CSS file
HTMLHelper::_('stylesheet', 'mod_dd_gmaps_module/user.css', array('version' => 'auto', 'relative' => true));

require_once ModuleHelper::getLayoutPath('mod_dd_gmaps_module', $params->get('layout', 'default'));
