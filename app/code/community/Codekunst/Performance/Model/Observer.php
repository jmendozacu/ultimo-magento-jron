<?php

class Codekunst_Performance_Model_Observer {
    public function optimizeCmsBlocksCache(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();
        if($block instanceof Mage_Cms_Block_Widget_Block || $block instanceof Mage_Cms_Block_Block) {
            $cacheKeyData = array(
                Mage_Cms_Model_Block::CACHE_TAG,
                $block->getBlockId(),
                Mage::app()->getStore()->getId()
            );

            $block->setCacheKey(implode('_', $cacheKeyData));
            $block->setCacheTags(
                array(Mage_Cms_Model_Block::CACHE_TAG)
            );
            $block->setCacheLifetime(false);
        }
    }
}
