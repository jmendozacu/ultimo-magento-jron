<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Custom Zend_Controller_Response_Http class (formally)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Controller_Response_Http extends Zend_Controller_Response_Http
{
    /**
     * Transport object for observers to perform
     *
     * @var Varien_Object
     */
    protected static $_transportObject = null;

    /**
     * Fixes CGI only one Status header allowed bug
     * 17.10.2014 added Bugfix (2nd link)
     *
     * @link  http://bugs.php.net/bug.php?id=36705
     * @link  http://blog.swiftcore.com/2012/07/magento-duplicate-header-content-type-fastcgi-comm-with-server-xxxxxxx-aborted-error-parsing-headers.html
     *
     * @return Mage_Core_Controller_Response_Http
     */
    public function sendHeaders()
    {
        if (!$this->canSendHeaders()) {
            Mage::log('HEADERS ALREADY SENT: '.mageDebugBacktrace(true, true, true));
            return $this;
        }
        if (in_array(substr(php_sapi_name(), 0, 3), array('cgi', 'fpm')))
        {
            // remove duplicate headers
            $remove = array('status', 'content-type');

            // already sent headers
            $sent = array();
            foreach (headers_list() as $header)
            {
                // parse name
                if (!$pos = strpos($header, ':'))
                        continue;
                $sent[strtolower(substr($header, 0, $pos))] = true;
            }

            // raw headers
            $headersRaw = array();
            foreach ($this->_headersRaw as $i=>$header)
            {
                // parse name
                if (!$pos = strpos($header, ':'))
                        continue;
                $name = strtolower(substr($header, 0, $pos));

                if (in_array($name, $remove))
                {
                        // check sent headers
                        if ($sent[$name])
                        {
                            unset($this->_headersRaw[$i]);
                            continue;
                        }

                        // check header
                        if (!is_null($existing = $headers[$name]))
                        {
                            $this->_headersRaw[$existing] = $header;
                            unset($this->_headersRaw[$i]);
                        }
                        else
                            $headersRaw[$name] = $i;
                }
            }

            // object headers
            $headers = array();
            foreach ($this->_headers as $i=>$header)
            {
                $name = strtolower($header['name']);
                if (in_array($name, $remove))
                {
                        // check sent headers
                        if ($sent[$name])
                        {
                            unset($this->_headers[$i]);
                            continue;
                        }

                        // check header
                        if (!is_null($existing = $headers[$name]))
                        {
                            $this->_headers[$existing] = $header;
                            unset($this->_headers[$i]);
                        }
                        else
                            $headers[$name] = $i;

                        // check raw headers
                        if (!is_null($existing = $headersRaw[$name]))
                            unset($this->_headersRaw[$existing]);
                }
            }
        }

        return parent::sendHeaders();
    }

    public function sendResponse()
    {
        Mage::dispatchEvent('http_response_send_before', array('response'=>$this));
        return parent::sendResponse();
    }

    /**
     * Additionally check for session messages in several domains case
     *
     * @param string $url
     * @param int $code
     * @return Mage_Core_Controller_Response_Http
     */
    public function setRedirect($url, $code = 302)
    {
        /**
         * Use single transport object instance
         */
        if (self::$_transportObject === null) {
            self::$_transportObject = new Varien_Object;
        }
        self::$_transportObject->setUrl($url);
        self::$_transportObject->setCode($code);
        Mage::dispatchEvent('controller_response_redirect',
                array('response' => $this, 'transport' => self::$_transportObject));

        return parent::setRedirect(self::$_transportObject->getUrl(), self::$_transportObject->getCode());
    }
}
