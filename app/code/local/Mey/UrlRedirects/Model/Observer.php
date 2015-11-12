<?php

class Mey_UrlRedirects_Model_Observer {
    const REGEX_PATH = "/\/(?<productIID>\d{4,})[\/|$]?/";
    const REGEX_QUERY = "/\[IID\]\=(?<productIID>\d+)/";

    public function redirectToProduct(Varien_Event_Observer $observer) {
        $action = $observer->getEvent()->getControllerAction();
        $requestPath = $action->getRequest()->getPathInfo();
        $matches = array();

        if(strpos($requestPath, '/shop/') !== false) {
            preg_match(self::REGEX_PATH, $requestPath, $matches);
        } else {
            $query = $action->getRequest()->getRequestUri();
            preg_match(self::REGEX_QUERY, $query, $matches);
        }

        if(array_key_exists('productIID', $matches)) {
            $this->_redirectByIid($matches['productIID'], $action);
        }
    }

    /**
     * Return product ID of parent configurable product, if it exists. Return ID of given product otherwise.
     *
     * @param $product Mage_Catalog_Model_Product
     */
    protected function _fetchMainProduct($product) {
        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

        if(count($parentIds) > 0) {
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->addAttributeToSelect('url_seo_key');
            $collection->addAttributeToFilter('type_id', array('eq' => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE));
            $collection->addAttributeToFilter('entity_id', array('in' => $parentIds));
            $collection->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
            $collection->addAttributeToFilter('visibility', array('in' => array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH
            )));

            if($collection->getSize() > 0) {
                return $collection->getFirstItem();
            }
        }

        return $product;
    }

    /**
     * @param $matches
     * @param $action
     */
    protected function _redirectByIid($iid, $action)
    {

        $product = Mage::getModel('catalog/product')->loadByAttribute('iid', $iid);

        if ($product) {
            $mainProduct = $this->_fetchMainProduct($product);

            $url = Mage::getBaseUrl().$mainProduct->getUrlSeoKey();
            /** @var Mage_Core_Controller_Response_Http $response */
            $response = $action->getResponse();
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $response->setRedirect($url, 301);
        }
    }
}
