<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2011 - 2018 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

JHtml::_('stylesheet', 'mod_dd_gmaps_module/dd_gmaps_module.min.css', array('version' => 'auto', 'relative' => true));

JHtml::_('jQuery.Framework');

?>
<div class="dd_gmaps_module">
	<?php
	// If force_map_size is enabled
	if ($params->get('force_map_size'))
	{
		$width  = intval($params->get('width')) . 'px';
		$height = intval($params->get('height')) . 'px';
		echo "<style>#dd_gmaps { width: $width; height: $height; }</style>";
	}
	// Show fullsize
	if ($params->get('fullsize')): ?>
        <div id="dd_gmaps_fullsize" class="pull-left">
            <button id="toggleFullSize"
                    class="btn fullsize-btn"><?php echo JText::_('MOD_DD_GMAPS_MODULE_FULLSIZE'); ?></button>
        </div>
	<?php endif; ?>
    <div id="dd_gmaps">
        <p class="dd_gmaps_loader"><?php echo JText::_('MOD_DD_GMAPS_MODULE_MAPS_PRELOADER'); ?></p>
    </div>
    <div class="clear"></div>
</div>
