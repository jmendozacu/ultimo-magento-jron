<?php

class Codekunst_Payglobe_Model_Api_Authorization {
    const XML_TEST_MODE = 'payment/codekunst_payglobe/test';
    const XML_DEBUG_MODE = 'payment/codekunst_payglobe/debug';
    const XML_CLIENT_ID = 'payment/codekunst_payglobe/client_id';
    const XML_CLIENT_SECRET = 'payment/codekunst_payglobe/client_secret';

    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    const RESPONSE_RETRY = 1;
    const RESPONSE_FAIL = -1;
    const RESPONSE_SUCCESS = 0;

    const API_URL_LIVE = 'https://payglobe.fortuneglobe.com';
    const API_URL_TEST = 'http://api.payglobe.test.fortuneglobe.com';

    const MAX_RETRIES = 3;

    protected $_accessToken = null;

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function getAuthorization($quote) {
        $cache = Mage::getSingleton('codekunst_payglobe/api_authorization_cache');
        $params = $this->_buildParams($quote);

        if($cache->isCached($params)) {
            Mage::helper('codekunst_payglobe')->log('Using cached recommendation.');

            $responseObject = $cache->getAuthorization($params);
            return $this->getRecommendation($responseObject, $params);
        } else {
            $responseObject = $this->_getAuthorizationFromLive($params);
        }

        $cache->setAuthorization($params, $responseObject);
        return $this->getRecommendation($responseObject, $params);
    }

    protected function _getAuthorizationFromLive($params) {
        if(is_null($this->_getAccessToken())) {
            return false;
        }

        $request = $this->_getClient($this->_getApiUrl());
        $response = $this->_doRequest($request, '/authorizations', $params);
        $tries = 0;
        $status = $this->_handleAuthorizationResponse($response);
        while($status == self::RESPONSE_RETRY && $tries < self::MAX_RETRIES) {
            $tries++;
            $response = $request->restPost('/authorizations', $params);
            $status = $this->_handleAuthorizationResponse($response);

            if($tries == self::MAX_RETRIES) {
                Mage::helper('codekunst_payglobe')->log('Aborting POST /authorizations after ' . self::MAX_RETRIES . ' retries.');
                return false;
            }
        }

        if($status == self::RESPONSE_FAIL) {
            return false;
        } else {
            $responseObject = $this->_parseResponse($response);
            return $responseObject;
        }
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    protected function _buildParams($quote) {
        $billingAddress = $quote->getBillingAddress();

        $gender = 'female';
        if($quote->getCustomerGender() == self::GENDER_MALE) {
            $gender = 'male';
        }

        $street = preg_split("/\s+(?=\S*+$)/", $billingAddress->getStreet1());

        $params = array(
            'gender' => $gender,
            'firstname' => $billingAddress->getFirstname(),
            'lastname' => $billingAddress->getLastname(),
            'country' => $billingAddress->getCountry(),
            'street' => $street[0],
            'housenumber' => $street[1],
            'zip' => $billingAddress->getPostcode(),
            'city' => $billingAddress->getCity(),
            'birthday' => $this->_formatDob($quote->getCustomerDob()),
            'currency' => 'EUR',
            'amount' => $this->_formatAmount($quote->getGrandTotal())
        );

        return $params;
    }

    /**
     * @return string
     */
    protected function _getApiUrl() {
        if($this->_isTestMode()) {
            return self::API_URL_TEST;
        }
        return self::API_URL_LIVE;
    }

    /**
     * @return bool
     */
    protected function _isTestMode() {
        return Mage::getStoreConfig(self::XML_TEST_MODE);
    }

    /**
     * @param float $amount The amount in Euros
     * @return int The amount in cents
     */
    protected function _formatAmount($amount) {
        return (int)($amount * 100);
    }

    /**
     * @param bool $reGenerate Whether to re-generate the token even if one exists.
     * @return bool
     */
    protected function _getAccessToken($reGenerate = false) {
        if(!$reGenerate && !is_null($this->_accessToken)) {
            return $this->_accessToken;
        }
        $this->_accessToken = null;

        $request = $this->_getClient($this->_getApiUrl(), false);
        $params = array(
            'grant_type' => 'client_credentials',
            'client_id' => Mage::getStoreConfig(self::XML_CLIENT_ID),
            'client_secret' => Mage::getStoreConfig(self::XML_CLIENT_SECRET)
        );
        $response = $this->_doRequest($request, '/oauth/token', $params);
        $responseCode = $response->getStatus();
        if($responseCode != 200) {
            Mage::helper('codekunst_payglobe')->log('Can\'t get an access token.');
        } else {
            $responseObject = $this->_parseResponse($response);
            $this->_accessToken = $responseObject->access_token;
        }

        return $this->_accessToken;
    }

    protected function _getClient($baseUrl, $includeAccessToken = true) {
        $client = new Zend_Rest_Client($baseUrl);
        $headers = array(
            'Accept' => 'application/json'
        );

        if($includeAccessToken) {
            $headers['Authorization'] = 'Bearer ' . $this->_getAccessToken();
        }

        $client->getHttpClient()->setHeaders($headers);
        return $client;
    }

    /**
     * @param Zend_Http_Response $response
     * @return object
     */
    protected function _parseResponse($response) {
        return json_decode($response->getBody());
    }

    protected function _isDebugMode() {
        return Mage::getStoreConfig(self::XML_DEBUG_MODE);
    }

    /**
     * @param string $dob
     * @return string
     */
    protected function _formatDob($dob) {
        $date = new DateTime($dob);
        return $date->format('Y-m-d');
    }

    /**
     * @param Zend_Rest_Client $request
     * @param string $path
     * @param array $params
     * @param string $method
     * @return Zend_Http_Response
     */
    protected function _doRequest($request, $path, $params, $method = 'POST') {
        if($this->_isDebugMode()) {
            $message = "{$method} {$request->getUri()}{$path} with params:\n". print_r($params, true);
            Mage::helper('codekunst_payglobe')->log($message);
        }

        switch($method) {
            case 'POST':
            default:
                $response = $request->restPost($path, $params);
                break;
        }

        if($this->_isDebugMode()) {
            $message = "Response: {$response->getStatus()} with body:\n{$response->getBody()}";
            Mage::helper('codekunst_payglobe')->log($message);
        }

        return $response;
    }

    /**
     * @param Zend_Http_Response $response
     * @return bool
     */
    protected function _handleAuthorizationResponse($response) {
        $status = $response->getStatus();
        switch($status) {
            case 401:
                $this->_getAccessToken(true);
                return self::RESPONSE_RETRY;
            case 400:
            case 404:
                return self::RESPONSE_FAIL;
            default:
                return self::RESPONSE_SUCCESS;
        }
    }

    /**
     * @param $responseObject
     * @return bool
     */
    protected function getRecommendation($response, $params)
    {
        if (is_object($response) && $response->recommendation && $params['amount'] <= $response->responseMoney->amount) {
            return true;
        } else {
            return false;
        }
    }
}
