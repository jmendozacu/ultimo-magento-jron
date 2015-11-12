<?php

class Codekunst_Looks_Model_Observer {
    public function catalogProductCollectionLoadBefore(Varien_Event_Observer $observer) {
        $collection = $observer->getCollection();
        /** @var Mage_Catalog_Model_Category $category */
        $category = Mage::registry('current_category');
        /** @var Mage_Catalog_Block_Product_List $productListBlock */
        $productListBlock = Mage::app()->getLayout()->getBlock('product_list');

        if($collection instanceof Mage_Catalog_Model_Resource_Product_Collection && $productListBlock && $category && $category->getIsLook()) {
            /** @var Mage_Catalog_Block_Product_List $categoryBlock */
            $categoryBlock = Mage::app()->getLayout()->getBlock('product_list');
            $categoryBlock->setTemplate('codekunst_looks/catalog/product/list.phtml');
            $categoryBlock->getToolbarBlock()->setData('_current_limit', 'all');
        }
    }

    public function controllerActionLayoutLoadBefore(Varien_Event_Observer $observer) {
        $product = Mage::registry('current_product');

        if($product && $product->getIsLook()) {
            $observer->getEvent()->getLayout()->getUpdate()->addHandle('product_look');
        }
    }
}
