<?php

require_once Mage::getBaseDir() . DS . "app" . DS . "code" . DS . "local" . DS . "Mey" . DS . "B2B" . DS . "Lib" . DS . "PhpBarcode" . DS . "php-barcode.php";

class Mey_B2B_BarcodeController extends Mage_Core_Controller_Front_Action {
    public function eanAction() {
        $ean = $this->getRequest()->get("ean");

        $this->getResponse()->clearAllHeaders();
        barcode_print($ean, "EAN", 2, "png");
    }
}
