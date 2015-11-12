<?php

class OrganicInternet_SimpleConfigurableProducts_Block_Catalog_Product_List_Upsell extends Mage_Catalog_Block_Product_List_Upsell {
    protected $_product = null;

    public function setProduct($product) {
        $this->_product = $product;
        return $this;
    }

    public function getProduct() {
        return $this->_product;
    }

    protected function _prepareData()
    {
        $product = Mage::registry('product');
        if(!is_null($this->_product)) {
            $product = $this->_product;
        }
        /* @var $product Mage_Catalog_Model_Product */
        $this->_itemCollection = $product->getUpSellProductCollection()
            ->setPositionOrder()
            ->addStoreFilter()
        ;
        if (Mage::helper('catalog')->isModuleEnabled('Mage_Checkout')) {
            Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($this->_itemCollection,
                Mage::getSingleton('checkout/session')->getQuoteId()
            );

            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_itemCollection);

        if ($this->getItemLimit('upsell') > 0) {
            $this->_itemCollection->setPageSize($this->getItemLimit('upsell'));
        }

        $this->_itemCollection->load();

        /**
         * Updating collection with desired items
         */
        Mage::dispatchEvent('catalog_product_upsell', array(
            'product'       => $product,
            'collection'    => $this->_itemCollection,
            'limit'         => $this->getItemLimit()
        ));

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }
}
