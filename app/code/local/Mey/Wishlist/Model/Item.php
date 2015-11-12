<?php

class Mey_Wishlist_Model_Item extends Mage_Wishlist_Model_Item {
    /**
     * Add or Move item product to shopping cart
     *
     * Return true if product was successful added or exception with code
     * Return false for disabled or unvisible products
     *
     * @throws Mage_Core_Exception
     * @param Mage_Checkout_Model_Cart $cart
     * @param bool $delete  delete the item after successful add to cart
     * @return bool
     */
    public function addToCart(Mage_Checkout_Model_Cart $cart, $delete = false)
    {
        $product = $this->getProduct();
        $storeId = $this->getStoreId();

        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            throw new Mage_Core_Exception(null, self::EXCEPTION_CODE_NOT_SALABLE);
        }

        $buyRequest = $this->getBuyRequest();

        // FIX FORTUNEGLOBE FOR SCP:
        $product = $this->_initSCPProduct($product->getId(), $buyRequest->getSuperAttribute());

        $cart->addProduct($product, $buyRequest);
        if (!$product->isVisibleInSiteVisibility()) {
            $cart->getQuote()->getItemByProduct($product)->setStoreId($storeId);
        }

        if ($delete) {
            $this->delete();
        }

        return true;
    }

    /**
     * Initialize product instance from request data
     *
     * @param int $productId ID of the configurable product
     * @param array $params Configurable attribute values
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initSCPProduct($productId, $params)
    {
        if ($productId) {
            $parentProduct = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($parentProduct->getId()) {
                if(Mage::helper('core')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts')) {
                    // Load simple product with the configured super attribute values
                    /** @var Mage_Catalog_Model_Product_Type_Configurable $typeInstance */
                    $typeInstance = $parentProduct->getTypeInstance();
                    $product = $typeInstance->getProductByAttributes($params, $parentProduct);

                    $product = Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($product->getId());
                    $product->setCpid($parentProduct->getId());
                } else {
                    $product = $parentProduct;
                }
                return $product;
            }
        }
        return false;
    }
}
