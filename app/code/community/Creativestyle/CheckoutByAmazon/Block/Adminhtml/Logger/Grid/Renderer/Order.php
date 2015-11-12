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
class Creativestyle_CheckoutByAmazon_Block_Adminhtml_Logger_Grid_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected static $_orderIdMapper = array();

    public function render(Varien_Object $row) {
        $realOrderIds = array();
        if (is_array($row->getOrderId()) && count($row->getOrderId())) {
            foreach ($row->getOrderId() as $orderId) {

                if (!isset(self::$_orderIdMapper[$orderId])) {
                    $order = Mage::getModel('sales/order')->load($orderId);
                    if ($order->getId()) self::$_orderIdMapper[$orderId] = $order->getIncrementId();
                    unset($order);
                }

                if (isset(self::$_orderIdMapper[$orderId])) {
                    $realOrderIds[] = sprintf('<a target="_blank" href="%s">%s</a>', Mage::getModel('adminhtml/url')->getUrl('adminhtml/sales_order/view', array('order_id' => $orderId)), self::$_orderIdMapper[$orderId]);
                }
            }
        }
        return implode(', ', $realOrderIds);
    }

}
