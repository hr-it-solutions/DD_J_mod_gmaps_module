<?php
/**
 * @package    DD_GMaps_Module
 *
 * @author     HR IT-Solutions Florian Häusler <info@hr-it-solutions.com>
 * @copyright  Copyright (C) 2017 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die('Restricted access');

class JFormFieldGeoHardcodeScript extends JFormFieldSpacer {

	protected $type = 'GeoHardcodeScript';

	/**
	 * GeoHardCode Script as Label
	 *
	 * @return  string  the script as label.
	 *
	 * @since   1.0.0.0
	 */
	protected function getLabel()
	{
		$html = array();
		$flag = '⚑';

		$label_text = $flag . ' ' . JText::_('MOD_DD_GMAPS_MODULE_GEOHARDCODE_UNSET');

		$html[] = "<a href='javascript:void(0)' class='btn btn-danger' id='geoaddressclear'>$label_text</a>";

		$html[] = '<script type=\'text/javascript\'>';
			$html[] = 'jQuery(function(){ ';
				$html[] = 'var t=\'' . $flag . '\',';
				$html[] = '    p=\'#jform_params_\';';
				$html[] = 'jQuery(\'#geoaddressclear\').on(\'click\', function(){';
					$html[] = 'jQuery(p+\'street\').val(t);';
					$html[] = 'jQuery(p+\'location\').val(t);';
					$html[] = 'jQuery(p+\'zip\').val(t);';
				$html[] = '});';
			$html[] = '});';
		$html[] = '</script><!-- GeoHardCode Script -->';

		return implode('', $html);
	}
}
