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
use Joomla\Component\HR_AdSys\Site\Helper\HR_AdSysHelper;

class CustomerField extends ListField {

	protected $type = 'Customer';

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
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id AS value, customer_name AS text');
        $query->from('#__hr_adsys_customers');
        $query->where('published=1');

        if(HR_AdSysHelper::isCustomerLoggedIn())
        {
            $companies = HR_AdSysHelper::getCompanies();
            if(count($companies)){
                $query->where($db->qn('id') . 'IN(' . implode(',', $companies) . ')');
            }
        }

        $query->order('customer_name');

        // Get the options.
        $db->setQuery($query);

        try
        {
            $options = $db->loadObjectList();
        }
        catch (\RuntimeException $e)
        {
            throw new \Exception($e->getMessage(), 500, $e);
        }

        $blankValue = [];

        if(!$this->multiple)
        {
            $values = new \stdClass();
            $values->text = Text::_('- Kunde wählen -');
            $values->value = "";
            $blankValue[] = $values;
        }

        $options = array_merge($blankValue, $options);

        return $options;
	}

}