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
abstract class Creativestyle_CheckoutByAmazon_Model_Mysql4_Log_Abstract extends Mage_Core_Model_Mysql4_Abstract {

    protected
        $_tableName = 'checkoutbyamazon/log_abstract';

    protected function _construct() {
        $this->_init($this->_tableName, 'log_id');
    }

    /**
     * Perform operations after object load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Creativestyle_CheckoutByAmazon_Model_Mysql4_Log_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        if ($object->getId()) {
            $orders = $this->lookupOrderIds($object->getId());
            $object->setData('order_id', $orders);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Perform operations after object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Creativestyle_CheckoutByAmazon_Model_Mysql4_Log_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        $oldOrders = $this->lookupOrderIds($object->getId());
        $newOrders = (array)$object->getOrderId();

        $table = $this->getTable($this->_tableName . '_order');
        $insert = array_diff($newOrders, $oldOrders);
        $delete = array_diff($oldOrders, $newOrders);

        if ($delete) {
            $where = array('log_id = ?' => (int)$object->getId(), 'order_id IN (?)' => $delete);
            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();
            foreach ($insert as $orderId) {
                $data[] = array('log_id' => (int)$object->getId(), 'order_id' => (int)$orderId);
            }
            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * Perform operations before object save
     *
     * @param Mage_Cms_Model_Block $object
     * @return Creativestyle_CheckoutByAmazon_Model_Mysql4_Log_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        if (!$object->getId()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }
        return parent::_beforeSave($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getOrderId()) {
            $select->join(array('order_table' => $this->getTable($this->_tableName . '_order')), $this->getMainTable() . '.log_id = order_table.log_id')
                ->where('order_table.order_id IN (?) ', $object->getOrderId())
                ->order('order_id DESC');
        }
        return $select;
    }

    /**
     * Get order IDs to which specified log is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupOrderIds($id) {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable($this->_tableName . '_order'), 'order_id')
            ->where('log_id = :log_id');
        $binds = array(':log_id' => (int)$id);
        return $this->_getReadAdapter()->fetchCol($select, $binds);
    }

    public function cleanLogs($dueDate = null) {
        if ($dueDate) {
            $this->_getWriteAdapter()->delete(
                $this->getTable($this->_tableName),
                $this->_getWriteAdapter()->quoteInto('creation_time < ?', $dueDate)
            );
        } else {
            $this->_getWriteAdapter()->delete(
                $this->getTable($this->_tableName)
            );
        }
    }

}
