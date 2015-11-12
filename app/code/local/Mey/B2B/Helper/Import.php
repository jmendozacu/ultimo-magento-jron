<?php

class Mey_B2B_Helper_Import extends Mage_Core_Helper_Abstract
{
    const XML_PATH_FTP_HOSTNAME = "mey_b2b/import/ftp_hostname";
    const XML_PATH_FTP_PORT = "mey_b2b/import/ftp_port";
    const XML_PATH_FTP_USERNAME = "mey_b2b/import/ftp_username";
    const XML_PATH_FTP_PASSWORD = "mey_b2b/import/ftp_password";
    const XML_PATH_FTP_ROOTPATH = "mey_b2b/import/ftp_rootpath";
    const XML_PATH_FTP_FOLDER_ACCOUNTS = "mey_b2b/import/ftp_folder_accounts";
    const XML_PATH_FTP_FOLDER_ACTIVE_PRICELISTS = "mey_b2b/import/ftp_folder_active_pricelists";

    const FILENAME_ACCOUNTS = "BTPJ5100onlinestorefinder.csv";

    /**
     * @return string
     */
    protected function getImportDir()
    {
        $baseDir = Mage::getBaseDir();
        $mediaDir = $baseDir . DS . "media";
        $importDir = $mediaDir . DS . "b2b_import";
        return $importDir;
    }

    /**
     * @param $priceListNumber
     * @return string
     */
    protected function generateEKFilename($priceListNumber)
    {
        // clean up parameters
        $priceListNumber = preg_replace("/\D/", "", $priceListNumber);
        $filename = "EK_PL_" . $priceListNumber . ".csv";
        return $filename;
    }

    /**
     * @param $priceListNumber
     * @param string $currency
     * @return string
     */
    protected function generateVKFilename($priceListNumber, $currency = "EUR")
    {
        // clean up parameters
        $priceListNumber = preg_replace("/\D/", "", $priceListNumber);
        $currency = preg_replace("/[^\da-z]/i", "", $currency);
        $filename = "VK_PL_" . $priceListNumber . "_" . $currency . ".csv";
        return $filename;
    }

    /**
     * @return resource
     */
    protected function getFtpConnection()
    {
        $ftp = ftp_connect(Mage::getStoreConfig(self::XML_PATH_FTP_HOSTNAME), Mage::getStoreConfig(self::XML_PATH_FTP_PORT));
        ftp_login($ftp, Mage::getStoreConfig(self::XML_PATH_FTP_USERNAME), Mage::getStoreConfig(self::XML_PATH_FTP_PASSWORD));
        ftp_pasv($ftp, true);
        ftp_chdir($ftp, Mage::getStoreConfig(self::XML_PATH_FTP_ROOTPATH));
        return $ftp;
    }

    /**
     * @param $priceListNumber
     * @return bool
     * @throws Exception
     */
    public function importEKPriceList($priceListNumber)
    {
        $importDir = $this->getImportDir();
        $remoteDir = Mage::getStoreConfig(self::XML_PATH_FTP_FOLDER_ACTIVE_PRICELISTS);

        $ftp = $this->getFtpConnection();
        $filename = $this->generateEKFilename($priceListNumber);
        $result = $this->compareAndDownload($ftp, $importDir . DS . $filename, $remoteDir . "/" . $filename);

        ftp_close($ftp);
        return $result;
    }

    /**
     * @param $priceListNumber
     * @param $currency
     * @return bool
     * @throws Exception
     */
    public function importVKPriceList($priceListNumber, $currency = "EUR")
    {
        $importDir = $this->getImportDir();
        $remoteDir = Mage::getStoreConfig(self::XML_PATH_FTP_FOLDER_ACTIVE_PRICELISTS);

        $ftp = $this->getFtpConnection();
        $filename = $this->generateVKFilename($priceListNumber, $currency);
        $result = $this->compareAndDownload($ftp, $importDir . DS . $filename, $remoteDir . "/" . $filename);

        ftp_close($ftp);
        return $result;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function importAccounts()
    {
        $importDir = $this->getImportDir();
        $remoteDir = Mage::getStoreConfig(self::XML_PATH_FTP_FOLDER_ACCOUNTS);

        $ftp = $this->getFtpConnection();

        $filename = self::FILENAME_ACCOUNTS;

        $result = $this->compareAndDownload($ftp, $importDir . DS . $filename, $remoteDir . "/" . $filename);

        ftp_close($ftp);
        return $result;
    }

    protected function compareAndDownload($ftp, $localFilepath, $remoteFilename)
    {
        $result = false;

        $file = new Varien_Io_File();
        $importDirResult = $file->checkAndCreateFolder($this->getImportDir());
        if (!$importDirResult) {
            throw new Exception("Could not create directory: " . $this->getImportDir());
        }

        $localFilesize = filesize($localFilepath);
        if (!$localFilesize) {
            // no local file, download
            $result = $this->downloadFile($ftp, $localFilepath, $remoteFilename);
        } else {
            // compare local and remote file sizes
            $remoteFilesize = ftp_size($ftp, $remoteFilename);
            if ($remoteFilesize === -1) {
                ftp_close($ftp);
                throw new Exception("File not found on FTP: " . $remoteFilename);
            }
            if ($localFilesize === $remoteFilesize) {
                // files match in size
                $result = true;
            } else {
                // file size does not match, download
                $result = $this->downloadFile($ftp, $localFilepath, $remoteFilename);
            }
        }
        return $result;
    }

    protected function downloadFile($ftp, $localFilepath, $remoteFilename)
    {
        $result = ftp_get($ftp, $localFilepath, $remoteFilename, FTP_BINARY);
        if (!$result) {
            ftp_close($ftp);
            throw new Exception("File not found on FTP: " . $remoteFilename);
        }
        return $result;
    }

    /**
     * @param $fileName
     * @return bool
     * @throws Exception
     */
    protected function fileExists($fileName)
    {
        $file = new Varien_Io_File();
        $file->cd($this->getImportDir());
        return $file->fileExists($fileName);
    }

    /**
     * @param $filename
     * @return array
     * @throws Exception
     */
    protected function getDataArrayFromCsvFile($filename)
    {
        $data = [];
        $filepath = $this->getImportDir() . DS . $filename;
        if (!$this->fileExists($filepath)) {
            throw new Exception("File not found: " . $filepath);
        }
        $file = fopen($filepath, "r");
        $header = fgetcsv($file, 0, ";");
        if (!is_array($header)) {
            throw new Exception("Could not read first row: " . $filepath);
        }
        while (!feof($file)) {
            $row = fgetcsv($file, 0, ";");
            if (!is_array($row)) {
                continue;
            }
            $data[] = array_combine($header, $row);
        }
        fclose($file);
        return $data;
    }

    /**
     * @param string $customerNumber
     * @return array
     * @throws Exception
     */
    public function getAccountsData($customerNumber = "")
    {
        $data = $this->getDataArrayFromCsvFile(self::FILENAME_ACCOUNTS);
        $customerData = null;
        if (!empty($customerNumber)) {
            foreach ($data as $row) {
                if (array_key_exists("Kunden-Nr", $row) && $row["Kunden-Nr"] == $customerNumber) {
                    if(is_null($customerData)) {
                        $customerData = $row;
                    } else {
                        $customerData["EK-Preisliste"] .= ",{$row["EK-Preisliste"]}";
                    }
                }
            }
            if(is_null($customerData)) {
                throw new Exception("Customer ID '" . $customerNumber . "' not found in " . self::FILENAME_ACCOUNTS);
            } else {
                $customerData["EK-Preisliste"] = trim($customerData["EK-Preisliste"], ",");
                return $customerData;
            }
        }
        return $data;
    }

    /**
     * Return array of DateTime objects
     *
     * @param string $customerNumber
     * @return array
     * @throws Exception
     */
    public function getAccountsBlockedDates($customerNumber)
    {
        $data = $this->getDataArrayFromCsvFile(self::FILENAME_ACCOUNTS);
        $dates = array();
        $hasMatch = false;
        foreach ($data as $row) {
            if (array_key_exists("Kunden-Nr", $row) && $row["Kunden-Nr"] == $customerNumber) {
                $hasMatch = true;
                for ($i = 1; $i < 4; $i++) {
                    if ($row["Sperr Kz {$i}"] == "J") {
                        if (empty($row["Datum bis {$i}"])) {
                            // generate temp future date
                            $dates[] = new DateTime("+1 year");
                        } else {
                            $dates[] = DateTime::createFromFormat("d.m.Y H:i:s", $row["Datum bis {$i}"] . " 23:59:59");
                        }
                    }
                }
            }
        }
        if (!$hasMatch) {
            throw new Exception("Customer ID '" . $customerNumber . "' not found in " . self::FILENAME_ACCOUNTS);
        }
        return $dates;
    }

    /**
     * @param $priceListNumber
     * @return array|bool
     * @throws Exception
     */
    public function getEKPriceListData($priceListNumber)
    {
        $filename = $this->generateEKFilename($priceListNumber);
        if ($this->fileExists($filename)) {
            return $this->getDataArrayFromCsvFile($filename);
        } else {
            return false;
        }
    }

    /**
     * @param $priceListNumber
     * @param string $currency
     * @return array|bool
     * @throws Exception
     */
    public function getVKPriceListData($priceListNumber, $currency = "EUR")
    {
        $filename = $this->generateVKFilename($priceListNumber, $currency);
        if ($this->fileExists($filename)) {
            return $this->getDataArrayFromCsvFile($filename);
        } else {
            return false;
        }
    }
}
