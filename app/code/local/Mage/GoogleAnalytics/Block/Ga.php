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
 * @package     Mage_GoogleAnalytics
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * GoogleAnalitics Page Block
 *
 * @category   Mage
 * @package    Mage_GoogleAnalytics
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleAnalytics_Block_Ga extends Mage_Core_Block_Template
{
    /**
     * @deprecated after 1.4.1.1
     * @see self::_getOrdersTrackingCode()
     * @return string
     */
    public function getQuoteOrdersHtml()
    {
        return '';
    }

    /**
     * @deprecated after 1.4.1.1
     * self::_getOrdersTrackingCode()
     * @return string
     */
    public function getOrderHtml()
    {
        return '';
    }

    /**
     * @deprecated after 1.4.1.1
     * @see _toHtml()
     * @return string
     */
    public function getAccount()
    {
        return '';
    }

    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName()
    {
        return $this->_getData('page_name');
    }

    /**
     * Render regular page tracking javascript code
     * The custom "page name" may be set from layout or somewhere else. It must start from slash.
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiBasicConfiguration.html#_gat.GA_Tracker_._trackPageview
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApi_gaq.html
     * @param string $accountId
     * @return string
     */
    protected function _getPageTrackingCode($accountId)
    {
        //$pageName   = trim($this->getPageName()) ? trim($this->getPageName()) : substr(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),0,-1);
        $optPageURL = '';

        $url = Mage::helper('core/url')->getCurrentUrl();
        //$pagepath =  Mage::getSingleton('core/url')->escape($_SERVER['REQUEST_URI']);
		
		$parts = parse_url($url);
		
		
		//delete and cut stupid analyticaa query parameters and build new pagepath @pa 22102014 
		if(array_key_exists('query', $parts) && $parts['query']) {
			$unsets = array('affmt','affmn');
			parse_str($parts['query'],$arr);
			
			while(list($k,$v)=each($unsets)){
				unset($arr[$v]);
			}
			if(array_key_exists('ref', $arr) && $arr['ref']) {
				$arr['ref'] = strstr($arr['ref'],'-',true);
			}
			while(list($k,$v)=each($arr)) {
				$arr[$k] = $k.'='.$v;	
			}
			
			$parts['query'] = '?'.implode('&',$arr);
		}
		
		$pagepath = $parts['path'];
        if(array_key_exists('query', $parts)) {
            $pagepath .= $parts['query'];
        }
		
        $optPageURL = " '{$this->jsQuoteEscape($pagepath)}'";

        return "
        ga('create', '{$this->jsQuoteEscape($accountId)}','auto');
        " . $this->_getAnonymizationCode() . "
        ga('send', 'pageview', {$optPageURL});
        ";
    }

    /**
     * Render information about specified orders and their items
     *
     * @link http://code.google.com/apis/analytics/docs/gaJS/gaJSApiEcommerce.html#_gat.GA_Tracker_._addTrans
     * @return string
     */
    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $result = array();
		$result[] = "ga('require', 'ecommerce', 'ecommerce.js');";
        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            $result[] = sprintf("ga('ecommerce:addTransaction', {id:'%s', affiliation:'%s', revenue:'%s', shipping:'%s', tax:'%s'});",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                round($order->getBaseGrandTotal(), 2),
                round($order->getBaseShippingAmount(), 2),
                round($order->getBaseTaxAmount(), 2)
            );
            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf("ga('ecommerce:addItem', {id:'%s', sku:'%s', name:'%s', category:'%s', price:'%s', quantity:'%s'});",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getSku()), $this->jsQuoteEscape($item->getName()),
                    null, // there is no "category" defined for the order item
                    round($item->getBasePriceInclTax(), 2), $item->getQtyOrdered()
                );
            }
            $result[] = "ga('ecommerce:send');";
        }
        return implode("\n", $result);
    }

    /**
     * Render GA tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Render IP anonymization code for page tracking javascript code
     *
     * @return string
     */
    protected function _getAnonymizationCode()
    {
        if (!Mage::helper('googleanalytics')->isIpAnonymizationEnabled()) {
            return '';
        }
        return "ga('set', 'anonymizeIp', true);";
    }
}
