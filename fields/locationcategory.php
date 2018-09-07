<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR-IT-Solutions GmbH Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2017 - 2018 HR-IT-Solutions GmbH
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

jimport('joomla.application.categories');

class JFormFieldLocationCategory extends JFormFieldList {

	protected $type = 'LocationCategory';

	/**
	 * Get Options
	 *
	 * @return array
	 *
	 * @since Version 1.0.0.0
	 */
	public function getOptions()
	{
		$categories = JCategories::getInstance('DD_GMaps_Locations');
		$subCategories = $categories->get()->getChildren(true);

		// Default field
		$options[0] = new StdClass;
		$options[0]->value = 0;
		$options[0]->text  = JText::_('JOPTION_SELECT_CATEGORY');

		$i = 1;

		// If DD GMaps Locations
		if (file_exists(JPATH_ADMINISTRATOR . '/components/com_dd_gmaps_locations/dd_gmaps_locations.php')
			&& JComponentHelper::getComponent('com_dd_gmaps_locations', true)->enabled)
		{
			foreach ($subCategories as $category)
			{
				$options[$i]        = new StdClass;
				$options[$i]->value = $category->id;
				$options[$i]->text  = $category->title;
				++$i;
			}
		}

		return $options;
	}
}
