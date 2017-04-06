<?php
/**
 * @version    1-1-0-0 // Y-m-d 2017-04-06
 * @author     HR IT-Solutions Florian Häusler https://www.hr-it-solutions.com
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

// Include the functions only once
JLoader::register('ModDD_GMaps_Module_Helper', __DIR__ . '/helper.php');

require JModuleHelper::getLayoutPath('mod_dd_gmaps_module', $params->get('layout', 'default'));
