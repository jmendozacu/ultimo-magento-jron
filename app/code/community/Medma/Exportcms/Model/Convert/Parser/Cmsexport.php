<?php

class Medma_Exportcms_Model_Convert_Parser_Cmsexport extends Mage_Dataflow_Model_Convert_Parser_Abstract{

	public function parse(){
	}

	public function unparse(){

		$pages = $this->getData();
		$row = array();
		
		foreach($pages as $i=> $page){
			if($pageId = $page->getPageId()){
				$page->load($pageId);
			}
			
			$row['page_id']= $pageId; 
			$row['stores'] = implode(',', $page->getStoreId());
			$row['title']  = $page->getTitle();
			$row['root_template'] = $page->getRootTemplate();
			$row['identifier'] = $page->getIdentifier();
			$row['content'] = $page->getContent();
			$row['is_active'] = $page->getIsActive();
			$row['sort_order'] = $page->getSortOrder();
			$row['content_heading'] = $page->getContentHeading();
			$row['meta_keywords'] = $page->getMetaKeywords();
			$row['meta_description'] = $page->getMetaDescription();
			$row['layout_update_xml'] = $page->getLayoutUpdateXml();
			$row['custom_theme'] = $page->getCustomTheme();
			$row['custom_root_template'] = $page->getCustomRootTemplate();
			$row['custom_layout_update_xml'] = $page->getCustomLayoutUpdateXml();
			$row['custom_theme_from'] = $page->getCustomThemeFrom();
			$row['custom_theme_to'] = $page->getCustomThemeTo();
			
			$batchExport = $this->getBatchExportModel()
				        ->setId(null)
				        ->setBatchId($this->getBatchModel()->getId())
				        ->setBatchData($row)
				        ->setStatus(1)
				        ->save();
		}

		return $this;		        
	}	
}
