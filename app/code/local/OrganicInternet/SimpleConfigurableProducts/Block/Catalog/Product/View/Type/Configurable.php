<?php

class OrganicInternet_SimpleConfigurableProducts_Block_Catalog_Product_View_Type_Configurable
    extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    // Workaround for "Looks"-Feature
    public function getOriginalJsonConfig()
    {
        return parent::getJsonConfig();
    }

    public function getUpsellJsonConfig() {
        $config = Zend_Json::decode(parent::getJsonConfig());

        $childProducts = array();

        //Create the extra price and tier price data/html we need.
        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $childProducts[$productId] = array(
                "price" => $this->_registerJsPrice($this->_convertPrice(Mage::helper("tax")->getPrice($product, $product->getPrice()))),
                "finalPrice" => $this->_registerJsPrice($this->_convertPrice(Mage::helper("tax")->getPrice($product, $product->getFinalPrice()))),
                "sku" => $product->getSku(),
                "qty" => floor($product->getStockItem()->getQty()),
            );
        }

        $p = $this->getProduct();
        $config['childProducts'] = $childProducts;
        if ($p->getMaxPossibleFinalPrice() != $p->getFinalPrice()) {
            $config['priceFromLabel'] = $this->__('Price From:');
        } else {
            $config['priceFromLabel'] = $this->__('');
        }
        $config['ajaxBaseUrl'] = Mage::getUrl('oi/ajax/');

        if (Mage::getStoreConfig('SCP_options/product_page/show_price_ranges_in_options')) {
            $config['showPriceRangesInOptions'] = true;
            $config['rangeToLabel'] = $this->__('to');
        }
        return Zend_Json::encode($config);
    }

    public function getJsonConfig()
    {
        $config = Zend_Json::decode(parent::getJsonConfig());

        $childProducts = array();

        //Create the extra price and tier price data/html we need.
        $upsellbuffer = array();
        foreach ($this->getAllowProducts() as $product) {
            $productId  = $product->getId();
            $childProducts[$productId] = array(
                "price" => $this->_registerJsPrice($this->_convertPrice($product->getPrice())),
                "finalPrice" => $this->_registerJsPrice($this->_convertPrice($product->getFinalPrice())),
                "sku" => $product->getSku(),
            );

            if (Mage::getStoreConfig('SCP_options/product_page/change_name')) {
                $childProducts[$productId]["productName"] = $product->getName();
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_description')) {
                $childProducts[$productId]["description"] = $this->helper('catalog/output')->productAttribute($product, $product->getDescription(), 'description');
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_short_description')) {
                $childProducts[$productId]["shortDescription"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getShortDescription()), 'short_description');
            }

            if (Mage::getStoreConfig('SCP_options/product_page/change_attributes')) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
                $childProducts[$productId]["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
                    ->setProduct($product)
                    ->toHtml();
            }

            /* FG Modify BEGIN */
                $childProducts[$productId]["sellingpoints"] = $this->getLayout()->createBlock('catalog/product_view_attributes')->setTemplate('catalog/product/view/details.phtml')->setProduct($product)->toHtml();
                $childProducts[$productId]["number"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getNumber()), 'number');
                $childProducts[$productId]["colorcode"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getColorCode()), 'color_code');
                $childProducts[$productId]["iid"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getIid()), 'iid');
                $childProducts[$productId]["ean"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getEan()), 'ean');
                $childProducts[$productId]["careinsandmaterials"] = $this->getLayout()->createBlock('catalog/product_view_attributes')->setTemplate('catalog/product/view/materialcare.phtml')->setProduct($product)->toHtml();
                $childProducts[$productId]["badge"] = $this->getLayout()->createBlock('catalog/product_view_attributes')->setTemplate('catalog/product/view/description.phtml')->setProduct($product)->toHtml();

                $childProducts[$productId]["storefinder"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getStorefinder()), 'storefinder');
                $childProducts[$productId]["stylee"] = $this->helper('catalog/output')->productAttribute($product, nl2br($product->getStyle()), 'style');

            /* FG Modify END */

            $bChangeStock = Mage::getStoreConfig('SCP_options/product_page/change_stock');
            if ($bChangeStock) {
                // Stock status HTML
                $oStockBlock = $this->getLayout()->createBlock('catalog/product_view_type_simple')->setTemplate('catalog/product/view/scpavailability.phtml');
                $childProducts[$productId]["stockStatus"] = $oStockBlock->setProduct($product)->toHtml();

                // Add to cart button
                $oAddToCartBlock = $this->getLayout()->createBlock('catalog/product_view_type_simple')->setTemplate('catalog/product/view/addtocart.phtml');
                $childProducts[$productId]["addToCart"] = $oAddToCartBlock->setProduct($product)->toHtml();
            }

            $bShowProductAlerts = Mage::getStoreConfig(Mage_ProductAlert_Model_Observer::XML_PATH_STOCK_ALLOW);
            if ($bShowProductAlerts && !$product->isAvailable()) {
                $oAlertBlock = $this->getLayout()->createBlock('productalert/product_view')
                        ->setTemplate('productalert/product/view.phtml')
                        ->setSignupUrl(Mage::helper('productalert')->setProduct($product)->getSaveUrl('stock'));;
                $childProducts[$productId]["alertHtml"] = $oAlertBlock->toHtml();
            }

            #if image changing is enabled..
            if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
                #but dont bother if fancy image changing is enabled
                if (!Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                    #If image is not placeholder...
                    if($product->getImage()!=='no_selection') {
                        $productMag = Mage::getModel('catalog/product')->load($productId);
                        foreach($productMag->getMediaGalleryImages() as $image)
                        {
                            $childProducts[$productId]["imageUrl"][] = (string)Mage::helper('catalog/image')->init($product, 'image', $image->getFile());
                        }
                    }
                }
            }

            if (Mage::getStoreConfig('SCP_options/product_page/change_upsell')) {
                /** @var Mage_Catalog_Block_Product_List_Upsell $childBlock */
                $childBlock = $this->getLayout()->getBlock('product.info.upsell');
                $oldProduct = $childBlock->getProduct();
                $childBlock->setProduct($product);
                $tmp = $childBlock->getData();
                $tmp["ColorCode"] = $product->getColorCode();
                if(!isset($upsellbuffer[$tmp["ColorCode"]])){
                    // Add Parameter for Color_Code
                    $childBlock->setData($tmp);
                    $upsellbuffer[$tmp["ColorCode"]] = $childBlock->toHtml();
                    $childProducts[$productId]['upsell'] = $upsellbuffer[$tmp["ColorCode"]];
                }else{
                    $childProducts[$productId]['upsell'] = $upsellbuffer[$tmp["ColorCode"]];
                }              
                $childBlock->setProduct($oldProduct);
            }
        }

        //Remove any existing option prices.
        //Removing holes out of existing arrays is not nice,
        //but it keeps the extension's code separate so if Varien's getJsonConfig
        //is added to, things should still work.
        if (is_array($config['attributes'])) {
            foreach ($config['attributes'] as $attributeID => &$info) {
                if (is_array($info['options'])) {
                    foreach ($info['options'] as &$option) {
                        unset($option['price']);
                    }
                    unset($option); //clear foreach var ref
                }
            }
            unset($info); //clear foreach var ref
        }

        $p = $this->getProduct();
        $config['childProducts'] = $childProducts;
        if ($p->getMaxPossibleFinalPrice() != $p->getFinalPrice()) {
            $config['priceFromLabel'] = $this->__('Price From:');
        } else {
            $config['priceFromLabel'] = $this->__('');
        }
        $config['ajaxBaseUrl'] = Mage::getUrl('oi/ajax/');
        $config['productName'] = $p->getName();
        $config['description'] = $this->helper('catalog/output')->productAttribute($p, $p->getDescription(), 'description');
        $config['shortDescription'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getShortDescription()), 'short_description');

        /* FG Modify BEGIN */
            $config['sellingpoints'] = $this->getLayout()->createBlock('catalog/product_view_attributes')->setTemplate('catalog/product/view/details.phtml')->setProduct($p)->toHtml();
            $config['number'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getNumber()), 'number');
            $config['colorcode'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getColorCode()), 'color_code');
            $config['iid'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getIid()), 'iid');
            $config["ean"] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getEan()), 'ean');
            $config["careinsandmaterials"] = $this->getLayout()->createBlock('catalog/product_view_attributes')->setTemplate('catalog/product/view/materialcare.phtml')->setProduct($p)->toHtml();
            $config['badge'] = $this->getLayout()->createBlock('catalog/product_view_description')->setTemplate('catalog/product/view/description.phtml')->setProduct($p)->toHtml();
            $config['storefinder'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getStorefinder()), 'storefinder');
            $config['stylee'] = $this->helper('catalog/output')->productAttribute($p, nl2br($p->getStyle()), 'style');
        /* FG Modify End */

        if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
            foreach($p->getMediaGalleryImages() as $image)
            {
                $config["imageUrl"][] = (string)Mage::helper('catalog/image')->init($p, 'image', $image->getFile());
            }
        }

        $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
        $config["productAttributes"] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
            ->setProduct($p)
            ->toHtml();

        $bShowProductAlerts = Mage::getStoreConfig(Mage_ProductAlert_Model_Observer::XML_PATH_STOCK_ALLOW);
        if ($bShowProductAlerts && !Mage::registry('child_product')->isAvailable()) {
            $oAlertBlock = $this->getLayout()->createBlock('productalert/product_view')
                    ->setTemplate('productalert/product/view.phtml')
                    ->setSignupUrl(Mage::helper('productalert')->setProduct(Mage::registry('child_product'))->getSaveUrl('stock'));;
            $config["alertHtml"] = $oAlertBlock->toHtml();
        }

        if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
            if (Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                $childBlock = $this->getLayout()->createBlock('infortis_cloudzoom/product_view_media');
                $config["imageZoomer"] = $childBlock->setTemplate('infortis/cloudzoom/product/view/media.phtml')
                    ->setProduct($p)
                    ->toHtml();
            }
        }

        if (Mage::getStoreConfig('SCP_options/product_page/change_upsell')) {
            /** @var Mage_Catalog_Block_Product_List_Upsell $childBlock */
            $childBlock = $this->getLayout()->getBlock('product.info.upsell');
            $oldProduct = $childBlock->getProduct();
            $childBlock->setProduct($p);

            $config['upsell'] = $childBlock->toHtml();

            $childBlock->setProduct($oldProduct);
        }

        if (Mage::getStoreConfig('SCP_options/product_page/show_price_ranges_in_options')) {
            $config['showPriceRangesInOptions'] = true;
            $config['rangeToLabel'] = $this->__('to');
        }
        return Zend_Json::encode($config);
        //parent getJsonConfig uses the following instead, but it seems to just break inline translate of this json?
        //return Mage::helper('core')->jsonEncode($config);
    }
}
