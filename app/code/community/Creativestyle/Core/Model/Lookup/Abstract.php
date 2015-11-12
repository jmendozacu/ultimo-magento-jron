<?php

/**
 * @category   Creativestyle
 * @package    Creativestyle_Core
 * @copyright  Copyright (c) 2011 - 2013 creativestyle GmbH (http://www.creativestyle.de)
 * @author     Marek Zabrowarny / creativestyle GmbH <support@creativestyle.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creativestyle_Core_Model_Lookup_Abstract extends Varien_Object {

    protected $_options = null;

    public function getOptions() {
        $result = array();
        $_options = $this->toOptionArray();
        foreach ($_options as $_option) {
            if (isset($_option['label']) && isset($_option['value']))
                $result[$_option['value']] = $_option['label'];
        }
        return $result;
    }

}
