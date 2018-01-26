<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2017 - 2018 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

jimport('joomla.application.categories');

class JFormFieldExtCPlugins extends JFormFieldList {

	protected $type = 'ExtCPlugins';

	/**
	 * Get Options
	 *
	 * @return array
	 *
	 * @since Version 1.0.0.0
	 */
	public function getOptions()
	{
		// Default field
		$options[0] = new StdClass;
		$options[0]->value = 0;
		$options[0]->text  = JText::_('MOD_DD_GMAPS_MODULE_ADDON_DDGMAPS_LOCATIONS_EXTCID_SELECT');

		$i = 1;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->qn('element'))
			->from($db->qn('#__extensions'))
			->where($db->qn('folder') . ' = ' . $db->quote('dd_gmaps_locations'))
			->where($db->qn('element') . ' LIKE ' . $db->quote('dd_ext_c_%'));
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach($rows as $row)
		{
			$options[$i]        = new StdClass;

			switch ($row->element)
			{
				case 'dd_ext_c_content':
					$options[$i]->value = 'com_' . 'content';
					$options[$i]->text  = 'Content Component';
					break;
				case 'dd_ext_c_k2':
					$options[$i]->value = 'com_' . 'k2';
					$options[$i]->text  = 'K2 Component';
					break;
				default:
					$options[$i]->value = 'com_' . trim($row->element, 'dd_ext_c_');
					$options[$i]->text  = ucfirst(trim($row->element, 'dd_ext_c_')) . ' Component';
			}

			++$i;
		}

		return $options;
	}
}
