<?php
# Execution Command: php -f ImportSublogin.php - --behavior [append|replace|delete] --source PATH-TO-CSV-FILE.CSV --verbose
# Example: php -f ImportSublogin.php - --behavior append --source var/import/sublogin.csv --verbose

require '../app/Mage.php';

function parseArgs(array $data) {
    $args    = array();
    $current = null;
    foreach ($data as $arg) {
        $match = array();
        if (preg_match('#^--([\w\d_-]{1,})$#', $arg, $match) || preg_match('#^-([\w\d_]{1,})$#', $arg, $match)) {
            $current = $match[1];
            $args[$current] = true;
        } else {
            if ($current) {
                $args[$current] = $arg;
            } else if (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                $args[$match[1]] = true;
            }
        }
    }

    return $args;
}


function printLog(Varien_Object $import) {
    // magento 1.6 feature
    if (method_exists($import, 'getFormatedLogTrace')) {
        echo $import->getFormatedLogTrace(), "\n";
    } else {
        $validationResult = $import->getValidationResult();
        $errors = array();
        if (!$import->getProcessedRowsCount()) {
            $errors[] = Mage::helper('importexport')->__('File does not contain data. Please specify another one');
        } else {
            if (!$validationResult) {
                if ($import->getProcessedRowsCount() == $import->getInvalidRowsCount()) {
                    $errors[] = Mage::helper('importexport')->__('File is totally invalid. Please fix errors');
                } elseif ($import->getErrorsCount() >= $import->getErrorsLimit()) {
                    $errors[] = Mage::helper('importexport')->__(
                        'Errors limit (%d) reached. Please fix errors',
                        $import->getErrorsLimit()
                    );
                } else {
                    if ($import->isImportAllowed()) {
                        $errors[] = Mage::helper('importexport')->__('Import process continue with some errors');
                    } else {
                        $errors[] = Mage::helper('importexport')->__('File is partially valid, but import is not possible');
                    }
                }
                // errors info
                foreach ($import->getErrors() as $errorCode => $rows) {
                    $error = $errorCode . ' ' . Mage::helper('importexport')->__('in rows:') . ' ' . implode(', ', $rows);
                    $errors[] = $error;
                }
            } else {
                if ($import->isImportAllowed()) {
                    $errors[] = Mage::helper('importexport')->__('File is valid');
                } else {
                    $errors[] = Mage::helper('importexport')->__('File is valid, but import is not possible');
                }
            }
            $errors = array_merge($errors, $import->getNotices());
            $errors[] = Mage::helper('importexport')->__(
                'Checked rows: %d, checked entities: %d, invalid rows: %d, total errors: %d',
                $import->getProcessedRowsCount(), $import->getProcessedEntitiesCount(),
                $import->getInvalidRowsCount(), $import->getErrorsCount()
            );
        }

        $trace = array();
        $i   = 1;
        foreach ($errors as $error) {
            $trace[$i] = $i . ': ' . $error;
        }
        echo join("\n", $trace);
    }
}

// check is a web request
if (!empty($_SERVER['REQUEST_METHOD'])) {
    $args = $_GET;
} else {
    $args = parseArgs($_SERVER['argv']);
}

try {
    // initialize mage app
    Mage::app('admin');

    // add default options
    $args = $args + array(
        'entity'   => 'sublogin',
        'behavior' => 'replace',
        'source'   => Mage::getBaseDir('var') . DS . 'import' . DS . $args['entity'] . '.csv'
    );

    if (empty($args['entity'])) {
        throw new Exception('Entity type is missed');
    }

    if (empty($args['source']) || !is_readable($args['source'])) {
        throw new Exception(sprintf('Invalid source file "%s"', $args['source']));
    }

    $import = Mage::getModel('importexport/import')->setData(array(
        'entity'   => $args['entity'],
        'behavior' => $args['behavior']
    ));

    $result   = $import->validateSource($args['source']);
    $import->setValidationResult($result);

    $canForce = $import->getProcessedRowsCount() != $import->getInvalidRowsCount();
    $canForce = $canForce && $import->getErrorsLimit() > $import->getErrorsCount();
    if ($canForce || $result) {
        $result = $import->importSource();
        // reindexAll();
    }

    if (isset($args['verbose'])) {
        printLog($import);
    }
} catch (Exception $e) {
    echo 'Script has thrown an exception: ', $e->getMessage(), "\n";
}
