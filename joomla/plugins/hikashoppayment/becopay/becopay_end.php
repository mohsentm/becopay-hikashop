<?php
/**
 * @package    Hikashop-Becopay-Gateway
 *
 * @author     Becopay Team <io@becopay.com>
 * @copyright  (C) 2018-2019 Becopay. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 * @link       http://becopay.com/en
 */
defined('_JEXEC') or die('Restricted access');
?>
<!-- Here is the ending page, called at the end of the checkout, just before the user is redirected to the payment plateform -->
<div class="hikashop_becopay_end" id="hikashop_becopay_end">
  <!-- Waiting message -->
	<span id="hikashop_becopay_end_message" class="hikashop_becopay_end_message"><?php
	  echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');
  ?></span>
	<span id="hikashop_becopay_end_spinner" class="hikashop_becopay_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>
	<form id="hikashop_becopay_form" name="hikashop_becopay_form" action="<?php echo $this->payment_params->payment_url;?>" method="post">
		<div id="hikashop_becopay_end_image" class="hikashop_becopay_end_image">
			<input id="hikashop_becopay_button" class="btn btn-primary" type="submit" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
<?php
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration('window.location="'.$this->payment_params->payment_url.'"');
?>
	</form>
</div>
