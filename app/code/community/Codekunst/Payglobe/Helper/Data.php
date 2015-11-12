<?php

class Codekunst_Payglobe_Helper_Data extends Mage_Core_Helper_Abstract {
    public function log($message, $level = null) {
        Mage::log($message, $level, 'payglobe.log');
    }
}
