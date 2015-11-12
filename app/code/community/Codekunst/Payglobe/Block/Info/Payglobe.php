<?php
class Codekunst_Payglobe_Block_Info_Payglobe extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('codekunst_payglobe/info/payglobe.phtml');
    }
}
