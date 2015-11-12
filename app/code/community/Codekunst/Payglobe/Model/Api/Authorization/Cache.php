<?php

class Codekunst_Payglobe_Model_Api_Authorization_Cache {
    const CACHE_KEY = 'codekunst_payglobe_cache';

    public function __construct() {
        if(count($this->_getCache()) == 0) {
            Mage::getSingleton('checkout/session')->setData(self::CACHE_KEY, array());
        }
    }

    public function isCached($params) {
        return (false !== $this->getAuthorization($params));
    }

    public function getAuthorization($params) {
        $cache = $this->_getCache();
        $key = $this->_hash($params);

        if(array_key_exists($key, $cache)) {
            return $cache[$key];
        } else {
            return false;
        }
    }

    public function setAuthorization($params, $data) {
        $cache = $this->_getCache();
        $key = $this->_hash($params);
        $cache[$key] = $data;
        Mage::getSingleton('checkout/session')->setData(self::CACHE_KEY, $cache);
    }

    public function cleanCache() {
        Mage::helper('codekunst_payglobe')->log('Cleaning recommendation cache after order placement.');
        Mage::getSingleton('checkout/session')->unsetData(self::CACHE_KEY);
    }

    protected function _hash($params) {
        return sha1(serialize($params));
    }

    protected function _getCache() {
        $cache = Mage::getSingleton('checkout/session')->getData(self::CACHE_KEY);
        if(is_array($cache)) {
            return $cache;
        } else {
            return array();
        }
    }
}
