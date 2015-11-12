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
class Creativestyle_CheckoutByAmazon_Model_Logger extends Creativestyle_CheckoutByAmazon_Model_Abstract {

    protected static function _extractOrderIds($orders = null) {
        $orderIds = array();

        if (is_null($orders)) $orders = Mage::registry('amazon_processing_orders');

        if (null !== $orders) {
            if ($orders instanceof Mage_Sales_Model_Order) {
                $orderIds = array($orders->getId());
            } else if ($orders instanceof Mage_Sales_Model_Resource_Order_Collection) {
                foreach($orders as $order) {
                    $orderIds[] = $order->getId();
                }
            } else if (is_array($orders)) {
                foreach ($orders as $orderId) {
                    $_order = Mage::getModel('sales/order')->load($orderId);
                    if ($_order->getId()) {
                        $orderIds[] = $_order->getId();
                    } else {
                        $_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                        if ($_order->getId()) {
                            $orderIds[] = $_order->getId();
                        }
                    }
                    unset($_order);
                }
                //$orderIds = $orders;
            } else if (is_scalar($orders)) {
                $orderIds = array($orders);
            }
        }
        return $orderIds;
    }

    protected static function _isEnabled() {
        return self::getConfigData('logger_enabled');
    }

    public static function cleanLogs() {
        $cleanAfter = self::getConfigData('clean_logs_after');
        if ($cleanAfter) {
            $dueDate = Mage::getModel('core/date')->date('Y-m-d H:i:s', '-' . $cleanAfter . ' days');
            Mage::getModel('checkoutbyamazon/log_api')->cleanLogs($dueDate);
            Mage::getModel('checkoutbyamazon/log_exception')->cleanLogs($dueDate);
            Mage::getModel('checkoutbyamazon/log_feed')->cleanLogs($dueDate);
            Mage::getModel('checkoutbyamazon/log_iopn')->cleanLogs($dueDate);
            Mage::getModel('checkoutbyamazon/log_report')->cleanLogs($dueDate);
        }
    }

    public static function logException($message, $errorCode = null, $stackTrace = '', $area = 'General', $requestId = null, $orders = null) {
        if (self::_isEnabled()) {
            $log = Mage::getModel('checkoutbyamazon/log_exception');
            $log->setMessage($message);
            $log->setErrorCode($errorCode);
            $log->setStackTrace($stackTrace);
            $log->setArea($area);
            $log->setRequestId($requestId);
            $log->setOrderId(self::_extractOrderIds($orders));
            $log->save();
        }
    }

    public static function logFeed($feedType, $feedSubmissionId, $orders = null, $feedContent = null, $storeId = null) {
        if (self::_isEnabled()) {
            $log = Mage::getModel('checkoutbyamazon/log_feed');
            $log->setFeedType($feedType);
            $log->setSubmissionId($feedSubmissionId);
            $log->setFeedContent($feedContent);
            $log->setProcessingResult(null);
            $log->setOrderId(self::_extractOrderIds($orders));
            if ($storeId) $log->setStoreId($storeId);
            $log->save();
        }
    }

    public static function logIopn($notificationType, $uuid, $orders = null, $notificationContent = null, $processingResult = null) {
        if (self::_isEnabled()) {
            $log = Mage::getModel('checkoutbyamazon/log_iopn');
            $log->setNotificationType($notificationType);
            $log->setUuid($uuid);
            $log->setNotificationContent($notificationContent);
            $log->setProcessingResult($processingResult);
            $log->setOrderId(self::_extractOrderIds($orders));
            $log->save();
        }
    }

    public static function logReport($reportType, $reportId, $reportRequestId = null, $orders = null, $reportContent = null) {
        if (self::_isEnabled()) {
            $log = Mage::getModel('checkoutbyamazon/log_report');
            $log->setReportType($reportType);
            $log->setReportId($reportId);
            $log->setReportRequestId($reportRequestId);
            $log->setReportContent($reportContent);
            $log->setOrderId(self::_extractOrderIds($orders));
            $log->save();
        }
    }

    public static function logApiCall($host, $action, $orders = null, $requestMethod = 'GET', $headers = null, $getData = null, $postData = null, $fileData = null, $responseCode = null, $response = null) {
        if (self::_isEnabled()) {
            $log = Mage::getModel('checkoutbyamazon/log_api');
            $log->setHost($host);
            $log->setAction($action);
            $log->setRequestMethod($requestMethod);
            $log->setHeaders($headers);
            $log->setGetData($getData);
            $log->setPostData($postData);
            $log->setFileData($fileData);
            $log->setResponseCode($responseCode);
            $log->setResponse($response);
            $log->setOrderId(self::_extractOrderIds($orders));
            $log->save();
        }
    }

}
