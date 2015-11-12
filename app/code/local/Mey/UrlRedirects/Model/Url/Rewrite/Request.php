<?php

class Mey_UrlRedirects_Model_Url_Rewrite_Request extends Mage_Core_Model_Url_Rewrite_Request {
    /**
     * Process redirect (R) and permanent redirect (RP)
     * Modified to keep the query string even if it's a redirect.
     *
     * @return Mage_Core_Model_Url_Rewrite_Request
     */
    protected function _processRedirectOptions()
    {
        $isPermanentRedirectOption = $this->_rewrite->hasOption('RP');

        $external = substr($this->_rewrite->getTargetPath(), 0, 6);
        if ($external === 'http:/' || $external === 'https:') {
            $destinationStoreCode = $this->_app->getStore($this->_rewrite->getStoreId())->getCode();
            $this->_setStoreCodeCookie($destinationStoreCode);
            $this->_sendRedirectHeaders($this->_rewrite->getTargetPath(), $isPermanentRedirectOption);
        }

        $targetUrl = $this->_request->getBaseUrl() . '/' . $this->_rewrite->getTargetPath();

        $storeCode = $this->_app->getStore()->getCode();
        if (Mage::getStoreConfig('web/url/use_store') && !empty($storeCode)) {
            $targetUrl = $this->_request->getBaseUrl() . '/' . $storeCode . '/' . $this->_rewrite->getTargetPath();
        }

        $queryString = $this->_getQueryString();
        if ($queryString) {
            $targetUrl .= '?' . $queryString;
        }

        if ($this->_rewrite->hasOption('R') || $isPermanentRedirectOption) {
            $this->_sendRedirectHeaders($targetUrl, $isPermanentRedirectOption);
        }

        $this->_request->setRequestUri($targetUrl);
        $this->_request->setPathInfo($this->_rewrite->getTargetPath());

        return $this;
    }
}
