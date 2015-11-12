<?php

class Mey_Paypal_Model_Api_Nvp extends Mage_Paypal_Model_Api_Nvp {
    /**
     * Do the API call
     *
     * @param string $methodName
     * @param array $request
     * @return array
     * @throws Mage_Core_Exception
     */
    public function call($methodName, array $request)
    {
        $request = $this->_addMethodToRequest($methodName, $request);
        $eachCallRequest = $this->_prepareEachCallRequest($methodName);
        if ($this->getUseCertAuthentication()) {
            if ($key = array_search('SIGNATURE', $eachCallRequest)) {
                unset($eachCallRequest[$key]);
            }
        }
        $request = $this->_exportToRequest($eachCallRequest, $request);
        $debugData = array('url' => $this->getApiEndpoint(), $methodName => $request);

        try {
            $http = new Varien_Http_Adapter_Curl();
            $config = array(
                'timeout'    => 30,
                'verifypeer' => $this->_config->verifyPeer
            );

            if ($this->getUseProxy()) {
                $config['proxy'] = $this->getProxyHost(). ':' . $this->getProxyPort();
            }
            if ($this->getUseCertAuthentication()) {
                $config['ssl_cert'] = $this->getApiCertificate();
            }
            $http->setConfig($config);
            $http->write(
                Zend_Http_Client::POST,
                $this->getApiEndpoint(),
                '1.1',
                $this->_headers,
                $this->_buildQuery($request)
            );
            $response = $http->read();
        } catch (Exception $e) {
            $debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $this->_debug($debugData);
            throw $e;
        }

        $response = preg_split('/^\r?$/m', $response);
        $response = trim(end($response));
        $response = $this->_deformatNVP($response);

        $debugData['response'] = $response;
        $this->_debug($debugData);
        $response = $this->_postProcessResponse($response);

        // handle transport error
        if ($http->getErrno()) {
            Mage::logException(new Exception(
                sprintf('PayPal NVP CURL connection error #%s: %s', $http->getErrno(), $http->getError())
            ));
            $http->close();

            Mage::throwException(Mage::helper('paypal')->__('Unable to communicate with the PayPal gateway.'));
        }

        // cUrl resource must be closed after checking it for errors
        $http->close();

        if (!$this->_validateResponse($methodName, $response)) {
            Mage::logException(new Exception(
                Mage::helper('paypal')->__("PayPal response hasn't required fields.")
            ));
            Mage::throwException(Mage::helper('paypal')->__('There was an error processing your order. Please contact us or try again later.'));
        }

        $this->_callErrors = array();
        if ($this->_isCallSuccessful($response)) {
            if ($this->_rawResponseNeeded) {
                $this->setRawSuccessResponseData($response);
            }
            return $response;
        }
        $this->_handleCallErrors($response);
        return $response;
    }
}
