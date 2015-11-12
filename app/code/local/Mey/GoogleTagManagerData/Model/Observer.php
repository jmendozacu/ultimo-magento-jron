<?php

class Mey_GoogleTagManagerData_Model_Observer {
    public function appendToDataLayer(Varien_Event_Observer $observer) {
        /** @var Varien_Object $dataLayer */
        $dataLayer = $observer->getEvent()->getDataLayer();
        $data = $dataLayer->getData();

        $request = Mage::app()->getFrontController()->getRequest();
        $route = $request->getModuleName() . '_' .
            $request->getControllerName() . '_' .
            $request->getActionName();
        switch($route) {
            case 'catalog_category_view':
                $category = Mage::registry('current_category');
                $data['categoryId'] = $category->getId();
                $data['pageType'] = 'category';

                $category = Mage::registry('current_category');
                $categoryLabel = "";
                if($category) {
                    $categoryLabel = $category->getName();
                }
                $data['categoryLabel'] = $categoryLabel;
                break;
            case 'catalog_product_view':
                $product = Mage::registry('current_product');
                $data['productId'] = $product->getIid();
                $data['productStyleId'] = $product->getNumber() . '-' . $product->getColorCode();
                $data['pageType'] = 'product';

                $category = Mage::registry('current_category');
                $categoryLabel = "";
                if($category) {
                    $categoryLabel = $category->getName();
                }
                $data['categoryLabel'] = $categoryLabel;
                $relatedProductCollection = $product->getRelatedProductCollection();
                $relatedProductIds = array();
                foreach($relatedProductCollection as $relatedProduct) {
                    $relatedProductIds[] = $relatedProduct->getIid();
                }
                $data['relatedProductIds'] = implode(',', $relatedProductIds);
                break;
            case 'cms_index_index':
                $data['pageType'] = 'home';
                break;
            case 'catalogsearch_result_index':
                $data['pageType'] = 'searchresults';
                break;
            case 'ajaxcart_checkout_cart_index':
            case 'checkout_cart_index':
                $cart = Mage::getSingleton('checkout/cart');
                $this->_fillCartInformation($data, $cart->getQuote());
                $data['pageType'] = 'cart';
                break;
            case 'onestepcheckout_index_index':
            case 'checkoutbyamazon_checkout_index':
                $cart = Mage::getSingleton('checkout/cart');
                $this->_fillCartInformation($data, $cart->getQuote());
                $data['pageType'] = 'checkout';
                break;
            case 'checkout_onepage_success':
            case 'checkoutbyamazon_checkout_success':
                $order = Mage::getSingleton('checkout/session')->getLastRealOrder();
                $this->_fillCartInformation($data, $order);
                $data['pageType'] = 'purchase';
                break;
            default:
                $data['pageType'] = 'other';
                break;
        }

        $dataLayer->setData($data);
    }

    /**
     * @param array $data
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     */
    private function _fillCartInformation(&$data, $object) {
        $productIids = array();
        $productQtys = array();
        $productStyleIds = array();
        /** @var Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item */
        foreach($object->getAllVisibleItems() as $item) {
            $productIids[] = $item->getProduct()->getIid();
            $productQtys[] = is_null($item->getQtyOrdered()) ? (int)$item->getQty() : (int)$item->getQtyOrdered();
            $productStyleIds[] = $item->getProduct()->getNumber() . '-' . $item->getProduct()->getColorCode();
        }
        $data['productStyleId'] = implode(',', $productStyleIds);
        $data['cartProductIds'] = implode(',', $productIids);
        $data['cartProductQtys'] = implode(',', $productQtys);
        $data['cartTotalNetto'] = round($object->getBaseSubtotal(), 2);
        $data['cartTotalBrutto'] = round($object->getBaseGrandTotal(), 2);
        $data['customerId'] = (int)$object->getCustomerId();

        // For zanox tracking
        if(array_key_exists('zanpid', $_COOKIE) && $_COOKIE['zanpid'] != '') {
            $data['zanpid'] = $_COOKIE['zanpid'];
        }
    }
}
