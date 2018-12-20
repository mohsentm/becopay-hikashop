<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopCompareType{
	function load(){
		$this->values = array();

		$this->values[] = JHTML::_('select.option', 0,JText::_('HIKASHOP_NO'));
		$this->values[] = JHTML::_('select.option', 1,JText::_('LINK') );
		$this->values[] = JHTML::_('select.option', 2,JText::_('FIELD_CHECKBOX'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', (int)$value );
	}
}
