<?php
/**
 * @package    HR_AdSys_Modal_Reporting
 *
 * @author     HR-IT-Solutions GmbH <info@hr-it-solutions.com>
 * @version    2.0.0.0
 * @copyright  Copyright (C) 2020 - 2020 PWG Professional Werbegesellschaft mbH
 * @license    non-licensed - may contain parts of GPL !
 **/

namespace Joomla\Module\HR_AdSys_Modal_Reporting\Site\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Component\HR_AdSys\Site\Model\ContractsModel;
use Joomla\Component\HR_AdSys\Site\Helper\HR_AdSysHelper;

class ContractsField extends ListField {

	protected $type = 'Contracts';

	/**
	 * Get Options
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since Version 1.0.0.0
	 */
	public function getOptions()
	{
		$ContractsModel = new ContractsModel();

		$input = Factory::getApplication()->input;

		$ids = $input->post->get('ids', array(), 'array');
		$type = $input->post->get('type', 0, 'STRING');

		if(count($ids) || $type === 'contracts')
		{
			$items = $ContractsModel->getItems();
		}
		else
		{
			$items = $ContractsModel->getItemsNonFiltered();
		}

		// Default field
		$options[0] = new \StdClass;
		$options[0]->value = 1;
		$options[0]->text  = Text::_('COM_HR_ADSYS_SELECT');

		foreach ($items as $key => $item){

            // ToDo: #2336 Vertrag Prefixe automatisch generieren
            $item->title = str_replace(array('RV_', 'RVAV_'), '', $item->title);

			$options[$key] = new \StdClass;
			$options[$key]->value = $item->id;

			if(HR_AdSysHelper::isCustomerLoggedIn()) {
                $options[$key]->text  = $item->contract_name;
            } else {
                $options[$key]->text  = $item->title;
            }
		}

		return $options;
	}

	public function getInput()
	{
		$html = parent::getInput();

		$input = Factory::getApplication()->input;
		$ids = $input->post->get('ids', array(), 'array');

		$type = $input->post->get('type', 0, 'STRING');

		if($type === 'contracts')
		{
			foreach ($ids as $id)
			{
				$html = str_replace(
					'value="' . $id . '"',
					'value="' . $id . '" selected="selected"',
					$html);
			}
		}

		return $html;

	}

}