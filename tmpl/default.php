<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR-IT-Solutions GmbH Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2011 - 2019 HR-IT-Solutions GmbH
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('stylesheet', 'mod_dd_gmaps_module/dd_gmaps_module.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('jquery.framework');

?>
<div class="dd_gmaps_module">
<?php if($params->get('eu_privay_mode') && $params->get('gdpr_cover')): ?>
<style>#dd_gmaps {cursor: pointer; background: url("<?php echo Uri::base() . $params->get('gdpr_cover'); ?>"); background-size: cover;}</style>
<?php endif; ?>
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
                    class="btn fullsize-btn"><?php echo Text::_('MOD_DD_GMAPS_MODULE_FULLSIZE'); ?></button>
        </div>
	<?php endif; ?>
    <div id="dd_gmaps">
        <?php if(!$params->get('eu_privay_mode')): ?>
        <p class="dd_gmaps_loader"><?php echo Text::_('MOD_DD_GMAPS_MODULE_MAPS_PRELOADER'); ?></p>
        <?php else: ?>
        <div id="dd_gmaps_gdpr_text"><?php echo Text::_('MOD_DD_GMAPS_MODULE_GDPR') . $params->get('gdpr_text'); ?></div>
        <?php endif; ?>
    </div>
    <div class="clear"></div>
</div>
