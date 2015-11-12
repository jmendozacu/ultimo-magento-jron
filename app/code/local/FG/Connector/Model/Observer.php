  <?php
/**
 * Fortuneglobe Connector Observer
 */
class FG_Connector_Model_Observer
{
    public function propagateOrder(Varien_Event_Observer $observer)
    {
        // Bestellung aus dem Observer Event holen
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        // Check if Order is by Amazon, iDEAL, Credit Card or PayPal and only send it if it is paid
        if($order->getPayment()->getMethod() != "checkoutbyamazon"  && $order->getPayment()->getMethod() != "saferpaycw_creditcard" && $order->getPayment()->getMethod() != "sofort_ideal" && $order->getPayment()->getMethod() != "paypal_standard" && $order->getPayment()->getMethod() != "paypal_express" ){
          // Move Order to Alvine
          $this->_enqueueOrder($order);
        }
    }

    public function sendamazonpayment(Varien_Event_Observer $observer)
    {
        // Bestellung aus dem Observer Event holen
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        // Check if Order is by Amazon, iDEAL, Credit Card or PayPal and only send it if it is paid
        if($order->getPayment()->getMethod() == "checkoutbyamazon"  || $order->getPayment()->getMethod() == "saferpaycw_creditcard" || $order->getPayment()->getMethod() == "sofort_ideal" || $order->getPayment()->getMethod() == "paypal_standard" || $order->getPayment()->getMethod() == "paypal_express" ){
          if($order->getStatus() == "processing"){
            // Move Order to Alvine
            $this->_enqueueOrder($order);
          }
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    protected function _enqueueOrder($order) {
        $order->setData('do_export_to_alvine', 1);
        $order->getResource()->saveAttribute($order, 'do_export_to_alvine');


        // Add Customer Source to Order if Exist
        if (array_key_exists('sxx_partner', $_COOKIE) && $_COOKIE['sxx_partner'] != "") {
            $partner = $_COOKIE['sxx_partner'];
            $order->setCustomerSource($partner);
            $order->getResource()->saveAttribute($order, "customer_source");
        }
    }

    public function processOrderQueue() {
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addAttributeToFilter('do_export_to_alvine', array('eq' => 1));
        $collection->addAttributeToSelect('entity_id');
        $collection->addAttributeToSelect('increment_id');

        foreach($collection as $order) {
            $this->prepareAndSend($order);
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param null|string $transid
     */
    public function prepareAndSend($order){
        // Bestellnummer in Logfile schreiben
        /** @var Mage_Sales_Model_Order $order */
        $order = $order->load($order->getId());
        $orderId = $order->getIncrementId();

        //Bestelldetails aus der SOAP holen
        $soapCall    = curl_init();
        $data_string = '{"data": {"action" : "advanced", "type" : "orderByOID", "data":{"increment_id":' . $orderId . '}}}';
        $soapUrl     = Mage::getStoreConfig('fg_options/messages/fg_soapurl');
        $esbUrl      = Mage::getStoreConfig('fg_options/messages/fg_esburl');
        
        $soapRequestOptions = array(
            CURLOPT_URL => $soapUrl, //"http://localhost/q1/service/index.php?alpha=q1Connector&beta=2z3fgOEc38UugPKqqXYntEq",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            ),
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true
        );
        curl_setopt_array($soapCall, $soapRequestOptions);
        $soapResponse  = curl_exec($soapCall);
        //Überprüfen ob eine Bestellung von der SOAP zurück gegeben wurde
        $completeOrder = json_decode($soapResponse, true);

        if (isset($completeOrder['increment_id']) && $orderId == $completeOrder['increment_id']) {
            $transid = $order->getPayment()->getLastTransId();

            //Add Transaction ID to Order if exist
            if($transid && ($completeOrder['payment_trans_id'] == "" || $completeOrder['payment_trans_id'] == null) ){
                $completeOrder['payment_trans_id'] = $transid;
            }

            $completeOrderJSON = json_encode(array(
                "orders" => array(
                    "order" => $completeOrder
                )
            ));
            
            //Bestellung an Talend übergeben
            $esbCall  = curl_init();
            $response = json_encode(array(
                "data" => array(
                    "data" => $completeOrderJSON,
                    "id" => $orderId
                )
            ));
            
            $esbRequestOptions = array(
                CURLOPT_URL => $esbUrl, //"http://localhost/soap/service/esb/esb_dummy.php",
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($response)
                ),
                CURLOPT_POSTFIELDS => $response,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_POST => true,
                CURLOPT_FRESH_CONNECT => true,
                CURLOPT_FORBID_REUSE => true
            );
            
            curl_setopt_array($esbCall, $esbRequestOptions);
            $esbResponse = curl_exec($esbCall);
            $httpStatus  = curl_getinfo($esbCall, CURLINFO_HTTP_CODE);
            curl_close($esbCall);
            if (isset($httpStatus) && $httpStatus == 200) {
                Mage::log("{$orderId} successfull transfered HTTP_STATUS_CODE: {$httpStatus}", null, 'fg-connector.log');
                $order->addStatusHistoryComment("Transferred to Alvine", false);
                $order->save();
            } else {
                Mage::log("ERROR: {$orderId} - unable to send oder to service HTTP_STATUS_CODE: {$httpStatus}", null, 'fg-connector.log');
                $order->addStatusHistoryComment("Transferred to Alvine via Stage because Alvine wasn't available", false);
                $order->save();

                $logFileName = '/var/www/mey/current/public/stage/' . $orderId . '.json';
                Mage::log("{$logFileName}", null, 'fg-pending-orders.log');
                
                file_put_contents($logFileName, $response);
            }

            $order->setData('do_export_to_alvine', 0);
            $order->getResource()->saveAttribute($order, 'do_export_to_alvine');
        } else {
            Mage::log("ERROR: {$orderId} - unable to load order from soap - {$soapResponse}", null, 'fg-connector.log');
        }
    }
}
