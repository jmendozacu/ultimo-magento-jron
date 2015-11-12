<?php

class Mey_B2BCustomer_Model_ApplyPriceLists
{
    const LOG_FILE = "pricelist.log";

    public function applyPriceLists()
    {
        Mage::log("Gathering approved accounts to apply price lists...", Zend_Log::INFO, self::LOG_FILE);

        Mage::app()->setCurrentStore(Mage::helper("mey_b2b")->getStore());

        // get all approved customers (objects) without price lists
        $customerCollection = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_b2b_approved', true)
            ->addAttributeToFilter('retail_price_list', array('eq' => "-1"))
            ->addAttributeToFilter('purchase_price_list', array('eq' => "-1"));

        Mage::log("Done. Found: " . count($customerCollection), Zend_Log::INFO, self::LOG_FILE);

        foreach ($customerCollection as $customer) {
            // found recently approved customer without associated price list (price import pending)
            try {
                $priceData = $this->getPriceDataForCustomer($customer);

                Mage::log("Importing price list for account (" . $customer->getId() . ") with customer number: " . $customer->getCustomerNumber(), Zend_Log::INFO, self::LOG_FILE);
                $this->updateCustomerPrices($customer, $priceData["prices"]);
                Mage::log("Finished price list import for account (" . $customer->getId() . ") with customer number: " . $customer->getCustomerNumber(), Zend_Log::INFO, self::LOG_FILE);

                Mage::log("Saving price list IDs in customer entity.", Zend_Log::INFO, self::LOG_FILE);
                $customer->setPurchasePriceList($priceData["EK-Preisliste"]);
                $customer->setRetailPriceList($priceData["VK-Preisliste"]);

                // save blocked state
                $isBlocked = Mage::helper("mey_b2bcustomer")->isCustomerBlocked($customer->getCustomerNumber());
                $customer->setIsB2bBlocked($isBlocked);

                $customer->save();

                Mage::log("Sending approval email to customer...", Zend_Log::INFO, self::LOG_FILE);
                // send an email to the customer with activation link
                $emailResult = Mage::helper("mey_b2bcustomer")->sendCustomerNotificationEmail($customer, true);
                if ($emailResult) {
                    Mage::log("Success.", Zend_Log::INFO, self::LOG_FILE);
                } else {
                    Mage::log("An unknown error occurred while sending mail.", Zend_Log::CRIT, self::LOG_FILE);
                }
            } catch (Exception $e) {
                Mage::logException($e);
                throw $e;
            }
        }

        Mage::log("All done.", Zend_Log::INFO, self::LOG_FILE);
    }

    public function updateExistingCustomers() {
        Mage::log("Gathering customer accounts to update price lists...", Zend_Log::INFO, self::LOG_FILE);

        Mage::app()->setCurrentStore(Mage::helper("mey_b2b")->getStore());

        // get all approved customers (objects) with valid purchase price lists
        $collection1 = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_b2b_approved', true)
            ->addAttributeToFilter('purchase_price_list', array('neq' => "-1"));

        // get all approved customers (objects) with valid retail price lists
        $collection2 = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_b2b_approved', true)
            ->addAttributeToFilter('retail_price_list', array('neq' => "-1"));

        // merge both lists
        // load an empty collection (filter-less collections will auto-lazy-load everything)
        $customerCollection = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('entity_id', -1);

        // add items from the first collection
        foreach($collection1 as $customer) {
            $customerCollection->addItem($customer);
        }

        // add items from the second collection
        foreach($collection2 as $customer) {
            // Magento won't let you add two of the same thing to a collection
            // so make sure the item doesn't already exist
            if(!$customerCollection->getItemById($customer->getId())) {
                $customerCollection->addItem($customer);
            }
        }

        Mage::log("Done. Found: " . count($customerCollection), Zend_Log::INFO, self::LOG_FILE);

        $i = 0;
        foreach ($customerCollection as $customer) {
            try {
                Mage::log("Updating customer prices for account (" . $customer->getId() . ") with customer number: " . $customer->getCustomerNumber(), Zend_Log::INFO, self::LOG_FILE);
                $priceData = $this->getPriceDataForCustomer($customer);
                $this->updateCustomerPrices($customer, $priceData["prices"]);
                Mage::log("Finished customer price import for account (" . $customer->getId() . ") with customer number: " . $customer->getCustomerNumber(), Zend_Log::INFO, self::LOG_FILE);
                $i++;
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::ERR, self::LOG_FILE);
                Mage::logException($e);
            }
        }
        $errorCount = count($customerCollection)-$i;
        $infoText = $errorCount == 0 ? "No problems occurred." : $errorCount . " critical errors occurred, please see Magento exception.log and log entries above." ;
        Mage::log("Finished all customer price updates. " . $infoText, Zend_Log::INFO, self::LOG_FILE);
    }

    protected function getPriceDataForCustomer($customer) {
        $importHelper = Mage::helper("mey_b2b/import");
        $accountData = $importHelper->getAccountsData($customer->getCustomerNumber());

        $ekPriceListIds = $accountData["EK-Preisliste"];
        $vkPriceListId = $accountData["VK-Preisliste"];

        $priceData = array();
        if (!empty($ekPriceListIds)) {
            foreach(array_unique(explode(",", $ekPriceListIds)) as $list) {
                $data = $importHelper->getEKPriceListData($list);
                if (!is_array($priceData)) {
                    throw new Exception("Purchase price list file not found: " . $list);
                }

                $priceData = array_merge($priceData, $data);
            }
        } else {
            $priceData = $importHelper->getVKPriceListData($vkPriceListId);
            if (!is_array($priceData)) {
                throw new Exception("Retail price list file not found: " . $vkPriceListId);
            }
        }
        $data = array(
            "prices" => $priceData,
            "EK-Preisliste" => $ekPriceListIds,
            "VK-Preisliste" => $vkPriceListId,
        );
        return $data;
    }

    protected function updateCustomerPrices($customer, $priceData)
    {
        $eanPriceMapping = array();
        // pre-process data
        foreach ($priceData as $line => $row) {
            if (!array_key_exists("E A N", $row) || (!array_key_exists("EK-Preis", $row) && !array_key_exists("VK-Preis", $row))) {
                Mage::log("Skipping line " . $line . " (not enough data)", Zend_Log::WARN, self::LOG_FILE);
                continue;
            }
            if (empty($row["E A N"]) || (empty($row["EK-Preis"]) && empty($row["VK-Preis"]))) {
                Mage::log("Skipping line " . $line . " (not enough data)", Zend_Log::WARN, self::LOG_FILE);
                continue;
            }
            $price = floatval(str_replace(",", ".", array_key_exists("EK-Preis", $row) ? $row["EK-Preis"] : $row["VK-Preis"]));
            $eanPriceMapping[$row["E A N"]] = $price;
        }

        // statistics
        $count = 0;

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $varcharTableName = $resource->getTableName('catalog_product_entity_varchar');
        $pricesTableName = $resource->getTableName("customerprices/prices");
        $storeId = Mage::helper("mey_b2b")->getStoreId();

        $attributeId = Mage::getModel('catalog/product')->getResource()->getAttribute("ean")->getId();

        // create chunks for more efficiency
        $chunkedEanPriceMapping = array_chunk($eanPriceMapping, 100, true);

        foreach ($chunkedEanPriceMapping as $eanPriceMapping) {
            $eans = implode(",", array_keys($eanPriceMapping));

            $query = 'SELECT `entity_id`, `value` FROM ' . $varcharTableName . ' WHERE `attribute_id` = ' . $attributeId . ' AND `value` IN (' . $eans . ');';

            $results = $readConnection->fetchAll($query);

            $eanProductIdMapping = array();
            foreach ($results as $result) {
                $eanProductIdMapping[$result["value"]] = $result["entity_id"];
            }

            $eansNotFound = array_diff(array_keys($eanPriceMapping), array_keys($eanProductIdMapping));
            if (count($eansNotFound) > 0) {
                Mage::log("Skipping following products (EANs not found in Magento): " . implode(", ", $eansNotFound), Zend_Log::WARN, self::LOG_FILE);
            };

            $sql = "";
            foreach ($eanProductIdMapping as $ean => $productId) {
                if (!array_key_exists($ean, $eanProductIdMapping)) {
                    Mage::log("EAN-Key " . $ean . " not found in Price-Array", Zend_Log::WARN, self::LOG_FILE);
                    continue;
                }

                $price = array(
                    "customer_id"       => $customer->getId(),
                    "customer_email"    => $customer->getEmail(),
                    "product_id"        => $productId,
                    "store_id"          => $storeId,
                    "quantity"          => 1,
                    "price"             => $eanPriceMapping[$ean],
                    "special_price"     => "NULL",
                );

                $sql .= "INSERT INTO {$pricesTableName} (customer_id, product_id, customer_email, store_id, qty, price, special_price, updated_at, created_at) VALUES(
                  {$price["customer_id"]}, {$price["product_id"]}, '{$price["customer_email"]}', '{$price["store_id"]}', '{$price["quantity"]}', '{$price["price"]}', {$price["special_price"]}, NOW(), NOW()
                ) ON DUPLICATE KEY UPDATE price = '{$price["price"]}', updated_at = NOW();";
                $count++;
            }

            // One last query to import the remaining prices.
            if(!empty($sql)) {
                $writeConnection->query($sql);
            }
        }

        Mage::log("Processed " . count($priceData) . " products", Zend_Log::INFO, self::LOG_FILE);
        Mage::log("Found an saved " . $count . " products", Zend_Log::INFO, self::LOG_FILE);
        Mage::log((count($priceData) - $count) . " products were not found", Zend_Log::INFO, self::LOG_FILE);
    }
}
