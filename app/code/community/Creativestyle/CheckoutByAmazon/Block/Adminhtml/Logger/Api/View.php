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
class Creativestyle_CheckoutByAmazon_Block_Adminhtml_Logger_Api_View extends Mage_Adminhtml_Block_Widget_Container {

    protected
        $_model = null;

    public function __construct() {
        parent::__construct();
        $this->_controller = 'adminhtml_logger_api';
        $this->_headerText = $this->__('Amazon API call');
        $this->setTemplate('creativestyle/checkoutbyamazon/logger/api/view.phtml');

        $this->_addButton('back', array(
            'label'     => Mage::helper('adminhtml')->__('Back'),
            'onclick'   => 'window.location.href=\'' . $this->getUrl('*/*/') . '\'',
            'class'     => 'back',
        ));

    }

    public function getLogModel() {
        return $this->_model;
    }

    public function setLogModel($model) {
        $this->_model = $model;
        if ($this->getLogModel()->getId()) {
            $this->_headerText = $this->__('%s API call | %s',
                $this->getAction(),
                $this->getCreationTime()
            );
        }
        return $this;
    }

    public function getAssignedOrders() {
        $realOrderIds = array();
        $row = $this->getLogModel();
        if (is_array($row->getOrderId()) && count($row->getOrderId())) {
            foreach ($row->getOrderId() as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order->getId())
                    $realOrderIds[] = sprintf('<a target="_blank" href="%s">%s</a>', $this->getUrl('adminhtml/sales_order/view', array('order_id' => $order->getId())), $order->getIncrementId());
                unset($order);
            }
        }
        return implode(', ', $realOrderIds);
    }

    public function getCreationTime() {
        return $this->formatDate($this->getLogModel()->getCreationTime(), 'medium', true);
    }

    public function getRequestMethod() {
        return $this->getLogModel()->getRequestMethod();
    }

    public function getFormattedReportContent() {
        if (!$this->getLogModel()->getReportContent()) return null;
        return $this->helper('checkoutbyamazon')->prettifyXml($this->getLogModel()->getReportContent(), true);
    }

    public function getHost() {
        return $this->getLogModel()->getHost();
    }

    public function getAction() {
        return $this->getLogModel()->getAction();
    }

    public function getHeaders() {
        return $this->getLogModel()->getHeaders();
    }

    public function getGetData() {
        return str_replace('&', "\r\n&", $this->getLogModel()->getGetData());
    }

    public function getPostData() {
        return str_replace('&', "\r\n&", $this->getLogModel()->getPostData());
    }

    public function getFileData() {
        //return $this->getLogModel()->getFileData();
        return $this->helper('checkoutbyamazon')->prettifyXml($this->getLogModel()->getFileData(), true);
    }

    public function getResponseCode() {
        return $this->getLogModel()->getResponseCode();
    }

    public function getResponse() {
        //return $this->getLogModel()->getResponse();
        return $this->helper('checkoutbyamazon')->prettifyXml($this->getLogModel()->getResponse(), true);
    }

    public function getHeaderCssClass() {
        return 'icon-head head-' . strtr($this->_controller, '_', '-');
    }

}
