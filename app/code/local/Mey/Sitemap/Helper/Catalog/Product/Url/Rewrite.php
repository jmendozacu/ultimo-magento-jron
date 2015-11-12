<?php
 
class Mey_Sitemap_Helper_Catalog_Product_Url_Rewrite extends Mage_Catalog_Helper_Product_Url_Rewrite {
    /**
     * Prepare url rewrite left join statement for given select instance and store_id parameter.
     *
     * @param Varien_Db_Select $select
     * @param int $storeId
     * @return Mage_Catalog_Helper_Product_Url_Rewrite_Interface
     */
    public function joinTableToSelect(Varien_Db_Select $select, $storeId)
    {
        $select->joinLeft(
            array('url_rewrite' => $this->_resource->getTableName('core/url_rewrite')),
            'url_rewrite.target_path = CONCAT("catalog/product/view/id/", main_table.entity_id) AND url_rewrite.is_system = 0 AND ' .
            $this->_connection->quoteInto('url_rewrite.category_id IS NULL AND url_rewrite.store_id = ? AND ',
                (int)$storeId) .
            $this->_connection->prepareSqlCondition('url_rewrite.id_path', array('like' => (int)$storeId . 'userproduct/%')),
            array('request_path' => 'url_rewrite.request_path'));
        return $this;
    }
}
