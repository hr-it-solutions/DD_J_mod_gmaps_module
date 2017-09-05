<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

/**
 * Helper for mod_dd_gmaps_module
 *
 * @since  Version 1.0.0.0
 */
class ModDD_GMaps_Module_Helper
{
	protected $params;

	/**
	 * existsDDGMapsLocations
	 *
	 * @since Version 1.1.0.6
	 *
	 * @return boolean
	 */
	public static function existsDDGMapsLocations()
	{
		// If DD GMaps Locations
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_dd_gmaps_locations/dd_gmaps_locations.php')
			&& JComponentHelper::getComponent('com_dd_gmaps_locations', true)->enabled)
		{
			return true;
		}

		return false;
	}

	/**
	 * isDDGMapsLocationsExtended
	 *
	 * @since Version 1.0.0.0
	 *
	 * @return mixed
	 */
	public function isDDGMapsLocationsExtended()
	{
		// If DD GMaps Locations
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_dd_gmaps_locations/dd_gmaps_locations.php')
			&& JComponentHelper::getComponent('com_dd_gmaps_locations', true)->enabled
			&& JFactory::getApplication()->input->get('option') == 'com_dd_gmaps_locations')
		{
			return true;
		}

		return false;
	}

	/**
	 * getItems
	 *
	 * @param   boolean  $extended_location  extend single locations with DGMapsLocations locations
	 * @param   boolean  $extended_only      load only extend locations
	 *
	 * @since Version 1.0.0.0
	 *
	 * @return mixed
	 */
	public function getItems($extended_location = false, $extended_only = false)
	{
		if ($extended_location == false)
		{
			$items = $this->getItem();
		}
		elseif ($extended_location && !$extended_only)
		{
			$items = array_merge(
				$this->getDDGMapsLocatiosItems(),
				$this->getItem()
			);
		}
		elseif ($this->isDDGMapsLocationsExtended() || $extended_only)
		{
			$items = $this->getDDGMapsLocatiosItems();
		}

		return $items;
	}

	/**
	 * Addon Module > Extension Access
	 *
	 * Get DD GMaps Locations Items
	 *
	 * @since Version 1.0.0.0
	 *
	 * @return mixed
	 */
	protected function getDDGMapsLocatiosItems()
	{
		jimport('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dd_gmaps_locations/models');

		$model = JModelLegacy::getInstance('Locations', 'DD_GMaps_LocationsModel');

		$db = JFactory::getDbo();
		$query = $model->getListQuery();

		$jinput = JFactory::getApplication()->input;

		$module = JModuleHelper::getModule('mod_dd_gmaps_module');
		$params = new JRegistry($module->params);

		// Load only locationcategory items outside of com_dd_gmaps_locations view locations
		if ($jinput->get('option') !== 'com_dd_gmaps_locations') // Important case to not break locations association
		{
			if ($params->get('locationcategory') !== 0)
			{
				$query->where($db->quoteName('catid') . '= ' . (int) $params->get('locationcategory'));
			}
		}

		$db->setQuery($query);

		$results = $db->loadObjectList();

		// Extc Plugins
		if ($params->get('extcplugins') !== '0')
		{
			// Get param, expected 'com_k2' etc...
			$extc_plugin = $params->get('extcplugins');

			JPluginHelper::importPlugin('dd_gmaps_locations');
			$dispatcher = JEventDispatcher::getInstance();
			$plg_results = $dispatcher->trigger('onextc', array(&$results, &$extc_plugin))[0];

			if (!empty($plg_results))
			{
				// Prepear plg_results in loop
				$results = $plg_results;
			}
		}

		return $results;

	}

	/**
	 * Get DDG Maps Maps Item
	 *
	 * @since Version 1.0.0.0
	 *
	 * @return mixed
	 */
	protected function getItem()
	{
		$return = array();
		$return[0] = new stdClass;

		$module = JModuleHelper::getModule('mod_dd_gmaps_module');
		$params = new JRegistry($module->params);

		$return[0]->id             = 0;
		$return[0]->category_title = 'standalone';
		$return[0]->alias          = '';

		$return[0]->title          = $params->get('location_name', '');
		$return[0]->profileimage   = $params->get('location_image', '');
		$return[0]->street         = $params->get('street', '');
		$return[0]->location       = $params->get('location', '');
		$return[0]->zip            = $params->get('zip', '');
		$return[0]->country        = $params->get('country', '');

		// Try to get geoCode address parameter > geoCoded via dd_gmaps_locations_geocode plugin or default value
		$return[0]->latitude       = $params->get('latitude', '48.0000000');
		$return[0]->longitude      = $params->get('longitude', '2.0000000');

		// Try to get geoCode HardCoding
		if (($params->get('geohardcode') !== '0'))
		{
			if ($this->validateLatLong($params->get('latitude_hardcode'), $params->get('longitude_hardcode')))
			{
				$return[0]->latitude       = $params->get('latitude_hardcode');
				$return[0]->longitude      = $params->get('longitude_hardcode');
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(
					JText::_('MOD_DD_GMAPS_MODULE_API_ALERT_GEOLOCATION_FAILED_ZERO_RESULTS_HARDCODE'),
					'warning'
				);
				goto fallbackGeoCode;
			}
		}

		// If geoCode plugin is not enabled and geoCode HardCoding ist not enabled,
		// geCode addresses on the fly without saving!
		if (!JPluginHelper::getPlugin('system', 'dd_gmaps_locations_geocode')
			&& $params->get('geohardcode') !== '1' )
		{
			fallbackGeoCode:
			// Get latitude and longitude
			$latlng = $this->Geocode_Location_To_LatLng($return, $params->get('google_api_key_geocode'));
			$return[0]->latitude   = $latlng['latitude'];
			$return[0]->longitude  = $latlng['longitude'];
		}

		return $return;
	}

	/**
	 * Validates coordinate
	 * Adapted from https://gist.github.com/arubacao/b5683b1dab4e4a47ee18fd55d9efbdd1
	 *
	 * @param   float  $lat   Latitude
	 * @param   float  $long  Longitude
	 *
	 * @return  bool `true` if the coordinate is valid, `false` if not
	 */
	private function validateLatLong($lat, $long)
	{
		$latlong = preg_replace("/[^0-9,.]/", "", $lat . ',' . $long);

		return preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?),[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $latlong);
	}

	/**
	 * Get latitude and longitude by address from Google GeoCode API
	 *
	 * @param   mixed   $data                    The form data which must include 'street' 'zip' 'location' 'federalstate' and 'country'
	 * @param   string  $google_api_key_geocode  GeoCode API code
	 *
	 * @return  array   latitude and longitude
	 *
	 * @since   Version 1.1.0.0
	 */
	protected function Geocode_Location_To_LatLng($data, $google_api_key_geocode)
	{
		// Get Location Data
		$address = array(
			'street'        => $data[0]->street,
			'zip'           => $data[0]->zip ,
			'location'      => $data[0]->location,
			'country'       => JText::_($data[0]->country) // Convert language string to country name
		);

		// Get API Key if key is set
		$google_api_URL_pram = '';

		if ($google_api_key_geocode)
		{
			$google_api_URL_pram    = '&key=' . trim($google_api_key_geocode);
		}
		// Prepare Address
		$prepAddr = implode('+', $address);

		// Get Contents and decode
		$geoCode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($prepAddr) . '&sensor=false' . $google_api_URL_pram);
		$output  = json_decode($geoCode);

		if (@$output->error_message != "") // If Error on API Connection, display error not
		{
			JFactory::getApplication()->enqueueMessage($output->error_message, 'Note');

			return false;
		}
		elseif($output->status == 'ZERO_RESULTS')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('MOD_DD_GMAPS_MODULE_API_ALERT_GEOLOCATION_FAILED_ZERO_RESULTS'), 'warning');
		}

		// Build array latitude and longitude
		$latlng = array("latitude"  => $output->results[0]->geometry->location->lat,
						"longitude" => $output->results[0]->geometry->location->lng);

		// Return Array
		return $latlng;
	}

	/**
	 * isset_Script checks if a subString src exists in script header
	 *
	 * @param   array   $doc_scripts  JFactory Document $doc->_scripts
	 * @param   string  $subString    Substring to check
	 *
	 * @return  boolean
	 *
	 * @since   Version 1.1.0.0
	 */
	public static function isset_Script($doc_scripts, $subString)
	{
		$return = false;

		foreach ($doc_scripts as $key => $value)
		{
			$pos = strpos($key, $subString);

			if ($pos === false)
			{
				$return = false;
			}
			else
			{
				// String found in key
				$return = true;
				break;
			}
		}

		return $return;
	}

	/**
	 * Params helper to get latitude latitude
	 *
	 * @param   string  $params  parameter
	 *
	 * @return  boolean
	 *
	 * @since   Version 1.1.0.0
	 */
	public function paramLatLong($params)
	{
		if ($params->get('set_as_default_position'))
		{
			if (($params->get('geohardcode') !== '0'))
			{
				if ($this->validateLatLong($params->get('latitude_hardcode'), $params->get('longitude_hardcode')))
				{
					return (float) $params->get('latitude_hardcode') . ', ' . (float) $params->get('longitude_hardcode');
				}
			}

			return (float) $params->get('latitude') . ', ' . (float) $params->get('longitude');
		}

		return '48.0000000, 2.0000000';
	}

	/**
	 * Parameter helper to get marker image
	 *
	 * @param   string  $params  parameter
	 *
	 * @return  boolean
	 *
	 * @since   Version 1.1.0.0
	 */
	public function paramMarkerImage($params)
	{
		if (strlen($params->get('marker_image')))
		{
			return JUri::base() . (string) $params->get('marker_image');
		}
		else
		{
			return JUri::base() . 'media/mod_dd_gmaps_module/img/marker.png';
		}
	}

	/**
	 * Parameter helper to get cluster marker image
	 *
	 * @param   string  $params  parameter
	 *
	 * @return  boolean
	 *
	 * @since   Version 1.1.0.0
	 */
	public function paramClusterMarkerImage($params)
	{
		if (strlen($params->get('clustermarker_image')))
		{
			return JUri::base() . (string) $params->get('clustermarker_image');
		}
		else
		{
			return JUri::base() . 'media/mod_dd_gmaps_module/img/marker_cluster.png';
		}
	}
}
