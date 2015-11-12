<?php

class Codekunst_Looks_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getConfigurableProductCollectionForBundle($bundle) {

        $arrSortedOptions = array();
        $optionCollection = $bundle->getTypeInstance(true)->getOptionsCollection($bundle);
        foreach ($optionCollection as $option) {
            $arrSortedOptions[] = $option->getOptionId();
        }

        $selectionCollection = $bundle->getTypeInstance(true)->getSelectionsCollection(
            $arrSortedOptions, $bundle);

        $arrSortedSimples = array();
        foreach($selectionCollection as $simpleProduct) {
            $optionId = $simpleProduct->getOptionId();
            $key = array_search($optionId, $arrSortedOptions);
            $arrSortedSimples[$key] = $simpleProduct;
        }
        ksort($arrSortedSimples);

        $uniqueConfigurableIds = array();
        foreach ($arrSortedSimples as $simpleProduct) {
            $configurableIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                ->getParentIdsByChild($simpleProduct->getId());

            Mage::log($simpleProduct->getName(), null, 'looks-test.log', true);

            foreach ($configurableIds as $confId) {
                if(!in_array($confId, $uniqueConfigurableIds)) {
                    $uniqueConfigurableIds[] = $confId;
                }
            }
        }

        $confCollection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', array('in' => $uniqueConfigurableIds))
            ->addAttributeToFilter('look_part', '1');

        $arrSortedConfs = array();
        foreach($confCollection as $configurable) {
            $id = $configurable->getId();
            $key = array_search($id, $uniqueConfigurableIds);
            $arrSortedConfs[$key] = $configurable;
        }
        ksort($arrSortedConfs);

        $configurableCollection = new Varien_Data_Collection();
        foreach ($arrSortedConfs as $configurable) {
            $configurableCollection->addItem($configurable);
        }

        return $configurableCollection;
    }
}