<?php

class Mey_B2B_Block_Catalog_Product_View extends Webtex_CustomerPrices_Block_Catalog_Product_View {
    protected function _toHtml() {
        if(Mage::app()->getWebsite()->getId() === Mage::helper("mey_b2b")->getWebsiteId()
            && $this->getTemplate() == "catalog/product/view.phtml"
            && Mage::helper("mobiledetect")->isMobile() && !Mage::helper("mobiledetect")->isTablet()) {
            $this->setTemplate("catalog/product/view_b2c.phtml");
        }

        return parent::_toHtml();
    }
}
