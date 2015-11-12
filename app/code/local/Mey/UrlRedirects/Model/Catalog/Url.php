<?php

class Mey_UrlRedirects_Model_Catalog_Url extends Dnd_Patchindexurl_Model_Url {
    protected $_categoryUrlKeyBlacklist = array(
        'kollektionen',
        'collecties',
        'collections',
        'kategorien',
        'categorieen',
        'categories',
        'serienname',
        'seriennamen',
        'serienaam',
        'series-name'
    );

    /**
     * Get unique category request path
     *
     * @param Varien_Object $category
     * @param string $parentPath
     * @return string
     */
    public function getCategoryRequestPath($category, $parentPath)
    {
        $storeId = $category->getStoreId();
        $idPath  = $this->generatePath('id', null, $category);
        $suffix  = $this->getCategoryUrlSuffix($storeId);

        if (isset($this->_rewrites[$idPath])) {
            $this->_rewrite = $this->_rewrites[$idPath];
            $existingRequestPath = $this->_rewrites[$idPath]->getRequestPath();
        }

        if ($category->getUrlKey() == '') {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getName());
        }
        else {
            $urlKey = $this->getCategoryModel()->formatUrlKey($category->getUrlKey());
        }

        $categoryUrlSuffix = $this->getCategoryUrlSuffix($category->getStoreId());
        if (null === $parentPath) {
            $parentPath = $this->getResource()->getCategoryParentPath($category);
        }
        elseif ($parentPath == '/') {
            $parentPath = '';
        }
        $parentPath = Mage::helper('catalog/category')->getCategoryUrlPath($parentPath,
            true, $category->getStoreId());

        // Only filter category URL paths when not in B2B store.
        if(Mage::helper("mey_b2b")->getStoreId() != $storeId) {
            $parentPath = $this->_filterCategoriesFromRequestPath($parentPath);
        }
        $requestPath = $parentPath . $urlKey . $categoryUrlSuffix;
        if (isset($existingRequestPath) && $existingRequestPath == $requestPath . $suffix) {
            return $existingRequestPath;
        }

        if ($this->_deleteOldTargetPath($requestPath, $idPath, $storeId)) {
            return $requestPath;
        }

        return $this->getUnusedPath($category->getStoreId(), $requestPath,
            $this->generatePath('id', null, $category)
        );
    }

    /**
     * @param $category
     * @return string
     */
    protected function _filterCategoriesFromRequestPath($path)
    {
        $pathParts = explode('/', $path);
        $newPathParts = array();
        foreach($pathParts as $part) {
            if(!in_array($part, $this->_categoryUrlKeyBlacklist)) {
                $newPathParts[] = $part;
            }
        }

        return implode('/', $newPathParts);
    }
}
