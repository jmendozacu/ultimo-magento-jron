<?php
//require_once 'Customweb/SaferpayCw/Model/ConfigurationAdapter.php';
//require_once 'Customweb/Payment/Endpoint/AbstractAdapter.php';


/**
 * @Bean
 */
class Customweb_SaferpayCw_Model_EndpointAdapter extends Customweb_Payment_Endpoint_AbstractAdapter
{
	protected function getBaseUrl() {
		return Mage::getUrl('SaferpayCw/endpoint/index', array('_store' => Customweb_SaferpayCw_Model_ConfigurationAdapter::getStoreId()));
	}
	
	protected function getControllerQueryKey() {
		return 'c';
	}
	
	protected function getActionQueryKey() {
		return 'a';
	}
	
	public function getFormRenderer() {
		return Mage::getModel('saferpaycw/formRenderer');
	}
}