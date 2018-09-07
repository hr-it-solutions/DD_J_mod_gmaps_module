<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR-IT-Solutions GmbH Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2011 - 2018 HR-IT-Solutions GmbH
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

		$this->countries_json_path = __DIR__ . '/..' .
			'/countries/' . $this->countries_json;

		if (JFile::exists($this->countries_json_path))
		{
			$json = file_get_contents($this->countries_json_path);
			$obj = json_decode($json);
			$countries = $obj->extension->countries->country;
		}

		foreach ($countries as $country)
		{
			$options[] = JHtml::_('select.option', 'MOD_DD_GMAPS_MODULE_COUNTRY_NAME_' . $country->name, JText::_('MOD_DD_GMAPS_MODULE_COUNTRY_NAME_' . $country->name));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
