<?php
class Customweb_SaferpayCw_Model_Source_Showimage{
	public function toOptionArray(){
		$options = array(
			array('value'=>'1', 'label'=>Mage::helper('adminhtml')->__("Show")),
			array('value'=>'0', 'label'=>Mage::helper('adminhtml')->__("Hide"))
		);
		return $options;
	}
}
