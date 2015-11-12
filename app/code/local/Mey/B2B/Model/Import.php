<?php

class Mey_B2B_Model_Import
{
    const LOG_FILE = "accounts_import.log";

    public function importAccountsAndPriceLists()
    {
        Mage::log("Starting import of accounts and related price lists...", Zend_Log::INFO, self::LOG_FILE);
        try {
            $importHelper = Mage::helper("mey_b2b/import");
            $importHelper->importAccounts();

            $arrAccounts = $importHelper->getAccountsData();

            // collect available prices lists (EK and VK)
            $arrEK = [];
            $arrVK = [];
            $keyEK = "EK-Preisliste";
            $keyVK = "VK-Preisliste";
            foreach ($arrAccounts as $row) {
                if (array_key_exists($keyEK, $row)) {
                    $value = $row[$keyEK];
                    if (!empty($value) && $value !== "0") {
                        $arrEK[] = $value;
                    }
                }
                if (array_key_exists($keyVK, $row)) {
                    $value = $row[$keyVK];
                    if (!empty($value) && $value !== "0") {
                        $arrVK[] = $value;
                    }
                }
            }

            // clean up arrays
            $arrEK = array_values(array_unique($arrEK));
            $arrVK = array_values(array_unique($arrVK));

            // import EK files
            $this->importEKPriceList($importHelper, $arrEK);

            // import VK files
            // todo: currency (using default "EUR" for now)
            $this->importVKPriceList($importHelper, $arrVK);

        } catch (Exception $e) {
            Mage::log($e->getMessage() . "\n" . $e->getTraceAsString(), Zend_Log::CRIT, self::LOG_FILE);
        }
        Mage::log("Finished import of accounts and related price lists.", Zend_Log::INFO, self::LOG_FILE);
    }

    protected function importEKPriceList(Mey_B2B_Helper_Import $helper, $data)
    {
        Mage::log("Importing EK price lists: " . implode(", ", $data), Zend_Log::INFO, self::LOG_FILE);
        foreach ($data as $priceListNumber) {
            try {
                $helper->importEKPriceList($priceListNumber);
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::WARN, self::LOG_FILE);
            }
        }
        Mage::log("Done.", Zend_Log::INFO, self::LOG_FILE);
    }

    protected function importVKPriceList(Mey_B2B_Helper_Import $helper, $data)
    {
        Mage::log("Importing VK price lists: " . implode(", ", $data), Zend_Log::INFO, self::LOG_FILE);
        foreach ($data as $priceListNumber) {
            try {
                $helper->importVKPriceList($priceListNumber);
            } catch (Exception $e) {
                Mage::log($e->getMessage(), Zend_Log::WARN, self::LOG_FILE);
            }
        }
        Mage::log("Done.", Zend_Log::INFO, self::LOG_FILE);
    }
}
