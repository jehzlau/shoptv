<?php
class IWD_NewsPopup_Model_System_Config_Source_Design_Icon_Arrangement
{
	public function toOptionArray()
	{
		return array(
				array('value' => '2',	'label' => Mage::helper('iwdpopup')->__('2 icons')),
				array('value' => '3',	'label' => Mage::helper('iwdpopup')->__('3 icons')),
				array('value' => '4',	'label' => Mage::helper('iwdpopup')->__('4 icons')),
				array('value' => '5',	'label' => Mage::helper('iwdpopup')->__('5 icons')),
				array('value' => '6',	'label' => Mage::helper('iwdpopup')->__('6 icons')),
				array('value' => 'auto', 'label' => Mage::helper('iwdpopup')->__('Auto width'))
		);
	}
}
?>