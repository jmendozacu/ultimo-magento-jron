<?php
class Root_Advancesitemap_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function activeCategories()
	{
		return Mage::getStoreConfig('advancesitemap/rootsitemap/activecategories');
	}
	
	public function activeSitepages()
	{
		return Mage::getStoreConfig('advancesitemap/rootsitemap/activesitepages');
	}
	
	public function activeContact()
	{
		return Mage::getStoreConfig('advancesitemap/rootsitemap/activecontact');
	}
}