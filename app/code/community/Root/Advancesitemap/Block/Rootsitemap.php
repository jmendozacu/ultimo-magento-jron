<?php   
class Root_Advancesitemap_Block_Rootsitemap extends Mage_Core_Block_Template
{   
	public function getPagesTree()
	{	
		/* * Return Tree of Site Pages * */			
		$storeId = $this->helper('core')->getStoreId(); 
		$cms = Mage::getModel('cms/page')->getCollection()->addFieldToFilter('is_active',1)->addStoreFilter($storeId);
		$url = Mage::getBaseUrl();
		$returnStr = "";
			foreach($cms as $cmspage):
				$page = $cmspage->getData();	
				if(!in_array($page['identifier'],array("no-route","enable-cookies","empty"))): 
					if($page['identifier'] == "home"):
						$returnStr .= '<li><a href='.$url.' title="'.$page['title'].'">'.$page['title'].'</a></li>';
					else:
						$returnStr .= '<li><a href="'.$url.$page['identifier'].'" title="'.$page['title'].'">'.$page['title'].'</a></li>';
					endif;
				endif;
			endforeach;		
		return $returnStr;	
	} 
	
	public function getCategoriesTree($parentId)
	{
		/* * Return Tree of Categories * */
		$currentCat = Mage::getModel('catalog/category')->load($parentId);
		
		$allCats = Mage::getModel('catalog/category')->getCollection()
							->addAttributeToSelect('*')
							->addAttributeToFilter('is_active','1')
							->addAttributeToFilter('include_in_menu','1')
							->addAttributeToFilter('parent_id',array('eq'=>$parentId))
							->addAttributeToSort('position', 'asc');		
							
		$returnStr = "";				
									
		$returnStr .= '<ul class="root-level'.$currentCat->getLevel().'">';
			foreach($allCats as $category):
				$subcats = $category->getChildren();
				$returnStr .= '<li>';
					if($subcats!=''):
						$returnStr .= '<span class="root-closeicon"></span><span class="liststyle"></span>';
					else:
						$returnStr .= '<span class="root-noicon"></span><span class="noliststyle"></span>';
					endif;
					$returnStr .= '<a href="'.$category->getUrl().'">'.$category->getName().'</a>';					
					if($subcats!=''):
						$returnStr .= $this->getCategoriesTree($category->getId());
					endif;
				$returnStr .= '</li>';
			endforeach;
		$returnStr .= '</ul>';
		
		return $returnStr;
	}
}