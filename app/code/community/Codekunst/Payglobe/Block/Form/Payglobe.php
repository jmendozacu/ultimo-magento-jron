<?php
class Codekunst_Payglobe_Block_Form_Payglobe extends Mage_Payment_Block_Form {
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('codekunst_payglobe/form/payglobe.phtml');
    }
}
