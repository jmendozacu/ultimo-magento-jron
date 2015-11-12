<?php

/**
 * This file is part of The Official Amazon Payments Magento Extension
 * (c) creativestyle GmbH <amazon@creativestyle.de>
 * All rights reserved
 *
 * Reuse or modification of this source code is not allowed
 * without written permission from creativestyle GmbH
 *
 * @category   Creativestyle
 * @package    Creativestyle_CheckoutByAmazon
 * @copyright  Copyright (c) 2011 - 2013 creativestyle GmbH (http://www.creativestyle.de)
 * @author     Marek Zabrowarny / creativestyle GmbH <amazon@creativestyle.de>
 */
class Creativestyle_CheckoutByAmazon_Model_Mysql4_Log_Collection_Abstract extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected
        $_modelName = 'checkoutbyamazon/log_abstract';

    public function _construct() {
        parent::_construct();
        $this->_init($this->_modelName);
    }

    protected function _afterLoad() {
        parent::_afterLoad();
        foreach ($this as $item) {
            $orders = $this->getResource()->lookupOrderIds($item->getId());
            $item->setData('order_id', $orders);
        }
        return $this;
    }

    public function addFieldToFilter($field, $condition = null) {
        switch ($field) {
            case 'order_increment_id':
                $this->getSelect()->where('log_id IN (?)', new Zend_Db_Expr(
                    'SELECT DISTINCT log_id FROM ' . $this->getResource()->getMainTable() . '_order JOIN ' . $this->getTable('sales/order') . '
                    ON ' . $this->getTable('sales/order') . '.entity_id = ' . $this->getResource()->getMainTable() . '_order.order_id
                    WHERE ' . $this->_getConditionSql($this->getTable('sales/order') . '.increment_id', $condition)
                ));
                return $this;
            default:
                return parent::addFieldToFilter($field, $condition);
        }
    }

}
