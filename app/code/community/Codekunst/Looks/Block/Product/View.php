<?php

class Codekunst_Looks_Block_Product_View extends Mage_Catalog_Block_Product_View {
    protected $_product = null;

    public function setProduct($product) {
        $this->_product = $product;
    }

    public function getProduct() {
        if(!is_null($this->_product)) {
            return $this->_product;
        } elseif(!is_null($this->getData("product"))) {
            return $this->getData("product");
        }
        return parent::getProduct();
    }

    public function getJsonConfig($product) {

        $config = array();
        if (!$this->hasOptions()) {
            return Mage::helper('core')->jsonEncode($config);
        }

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);

        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true,
                null, null, null, null, null, false);
            $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, false,
                null, null, null, null, null, false);
        } else {
            $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
            $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        }
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('core')->currency($tierPrice['website_price'], false, false);
            $_tierPricesInclTax[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (int)$tierPrice['website_price'], true),
                false, false);
        }
        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'plusDispositionTax'  => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }
}
