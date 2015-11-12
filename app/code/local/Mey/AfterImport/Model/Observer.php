<?php

class Mey_AfterImport_Model_Observer {
    public function clearAfterImport() {
        $this->_fixImageCache();
        $this->disableEmptyCategories();
        $this->_reindex();
        $this->_clearVarnishCache();
        $this->_clearCache();
        $this->_notifyTalend();
    }

    protected function _fixImageCache() {
        $this->_log("Fixing image cache");
        $mediaDir = Mage::getBaseDir('media');
        // Delete all broken cache files (size of 0 bytes)
        shell_exec("/bin/find {$mediaDir}/catalog/product/cache -type f -size 0 -delete");
        // Fix access rights for product image directories
        shell_exec("/bin/find {$mediaDir}/catalog/product -type d -exec chmod 777 {} \\;");
    }

    protected function _clearCache() {
        $this->_log("Clearing cache");
        Mage::dispatchEvent('adminhtml_cache_flush_all');
        Mage::app()->getCacheInstance()->flush();
        Mage::getModel("aoeasynccache/cleaner")->processQueue();
    }

    protected function _notifyTalend() {
        $this->_log("Creating talend trigger file");
        $file = fopen(Mage::getBaseDir('media') . "/after-import-done.txt", "w");
        fclose($file);
    }

    protected function _clearVarnishCache() {
        $this->_log("Clearing varnish cache");
        Mage::getModel("turpentine/varnish_admin")->flushAll();
    }

    protected function _reindex() {
        $factory = new Mage_Core_Model_Factory();
        $processes = array();

        $indexer = $factory->getSingleton($factory->getIndexClassAlias());

        $collection = $indexer->getProcessesCollection();
        foreach ($collection as $process) {
            if ($process->getIndexer()->isVisible() === false) {
                continue;
            }
            $processes[] = $process;
        }

        foreach ($processes as $process) {
            /* @var $process Mage_Index_Model_Process */
            try {
                $this->_log("Reindexing " . $process->getIndexerCode());
                $process->reindexEverything();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function disableEmptyCategories() {
        // Iterate over all stores
        $stores = Mage::app()->getStores();
        /** @var Mage_Core_Model_Store $store */
        foreach($stores as $store) {
            $appEmulation = Mage::getSingleton('core/app_emulation');

            // Start environment emulation of the current store
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store->getId());

            // Iterate store categories
            $categoryCollection = Mage::getResourceModel('catalog/category_collection');
            $categoryCollection->addAttributeToSelect(array(
                'path',
                'is_anchor',
                'is_active'
            ));
            $categoryCollection->addAttributeToFilter('entity_id', array('nin' => array(1, 2, 35570))); // Skip root categories
            $categoryCollection->addAttributeToFilter('level', array('gteq' => 2)); // Also, only look at categories on 2nd level or deeper (below root categories)
            $categoryCollection->setProductStoreId($store->getId());
            $categoryCollection->setLoadProductCount(true);
            $categoryCollection->setStoreId($store->getId());

            foreach($categoryCollection as $category) {
                /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
                $productCollection = $category->getProductCollection();
                $productCollection->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));

                // If category (or subcategory) has active products, do not deactivate.
                if($productCollection->getSize() > 0) continue;

                $this->_log("Deactivating category " . $category->getId() . " in store " . $store->getCode());
                $category = Mage::getModel('catalog/category')->setStoreId($store->getId())->load($category->getId());
                $category->setIsActive(0);
                $category->save();
            }

            // Stop environment emulation and restore original store
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
    }

    protected function _log($message) {
        Mage::log($message, null, 'after_import.log');
    }
}
