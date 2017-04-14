<?php
/**
 * @version    1-1-0-0 // Y-m-d 2017-03-18
 * @author     HR IT-Solutions Florian Häusler https://www.hr-it-solutions.com
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

jimport('joomla.filesystem.file');

class JFormFieldCountry extends JFormFieldList {

	protected $type = 'Country';

	protected $countries_json = 'countries.json';

	protected $countries_json_path;

	/**
	 * Get Options
	 *
	 * @return array
	 *
	 * @since Version 1.0.0.0
	 */
	public function getOptions()
	{
		$countries = array();

		$options = array();

		$this->countries_json_path = dirname(__FILE__, 2) .
			'/countries/' . $this->countries_json;

		if (JFile::exists($this->countries_json_path))
		{
			$json = file_get_contents($this->countries_json_path);
			$obj = json_decode($json);
			$countries = $obj->extension->countries->country;
		}

		// Default field
		$options[0] = new StdClass;
		$options[0]->value = 0;
		$options[0]->text  = JText::_('MOD_DD_GMAPS_MODULE_COUNTRY_SELECT');

		$i = 1;

		foreach ($countries as $country)
		{
			$options[$i] = new StdClass;
			$options[$i]->value = 'MOD_DD_GMAPS_MODULE_COUNTRY_NAME_' . $country->name;
			$options[$i]->text  = JText::_('MOD_DD_GMAPS_MODULE_COUNTRY_NAME_' . $country->name);
			++$i;
		}

		return $options;
	}
}
