<?php
class Customweb_SaferpayCw_Model_Source_Cancelexistingorders{
	public function toOptionArray(){
		$options = array(
			array('value'=>'1', 'label'=>Mage::helper('adminhtml')->__("Enabled")),
			array('value'=>'0', 'label'=>Mage::helper('adminhtml')->__("Disabled"))
		);
		return $options;
	}
}
