<?php
 
class Mey_Turpentine_Model_Core_Url_Rewrite_Request extends Mage_Core_Model_Url_Rewrite_Request {
    /**
     * @param $request Zend_Controller_Request_Http
     */
    public function setRequest($request) {
        $this->_request = $request;
    }
}
