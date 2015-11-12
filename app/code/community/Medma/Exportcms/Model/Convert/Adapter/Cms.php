<?php

class Medma_Exportcms_Model_Convert_Adapter_Cms extends Mage_Dataflow_Model_Convert_Adapter_Abstract{

    protected $_cmsPageObj;

    protected $_reqFields = array(
		'title',
		'root_template',
		'identifier',
		'content'
    );

    protected $_validateXMlFields = array(
		'layout_update_xml',
		'custom_layout_update_xml',
		
    );
    protected $_defaultScope = 0;
    protected $_defaultActive = 0;

    public function load() {

      $collection = Mage::getResourceModel('cms/page_collection');

	  $filterData = Mage::getSingleton('adminhtml/session')->getPageFilterData();	
	  Mage::getSingleton('adminhtml/session')->unsPageFilterData();  

	  if(is_array($filterData)){
		$filterData = array_filter($filterData);
	  }

	  if(count($filterData) > 0){
	  	$collection->addFieldToFilter('page_id', array('IN' => $filterData));
	  }
	  
	  $this->setData($collection);	
    }

    public function save() {
      // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface      
    }

	protected function _getHelper(){
		return Mage::helper('exportcms');
	}

    public function getPageModel(){

        if (is_null($this->_cmsPageObj)) {
            $pageModel = Mage::getModel('cms/page');
            $this->_cmsPageObj = Mage::objects()->save($pageModel);
        }
        return Mage::objects()->load($this->_cmsPageObj);
    }

    public function saveRow(array $importData){

		#validate import data for required fields
		foreach($this->_reqFields as $_reqField){
			if(isset($importData[$_reqField]) && empty($importData[$_reqField])){
				Mage::throwException(
					Mage::helper('catalog')->__('Row skipped due to non-existence of one of required field: %s', implode(',', $this->_reqFields))
				);
			}
		}

		#Validate XML information
		$errorMerged = array();
		foreach($this->_validateXMlFields as $_xmlField){
			if(isset($importData[$_xmlField])){
				$hasError = $this->_getHelper()->_validatePostDataForPages($importData[$_xmlField]);
				if(!empty($hasError)){
					$errorMerged[$_xmlField] = $hasError; 
				}	
			}
		}

		if(!empty($errorMerged)){
				Mage::throwException(
					Mage::helper('catalog')->__('Invalid XML in one or more of these fields <strong>\'%s\'</strong>', implode(',', $this->_validateXMlFields))
				);
		}

		$page = $this->getPageModel();
		#column page_id
		if(!empty($importData['page_id'])){
			$pageId = (int)$importData['page_id'];
			$page->load($pageId);

			/*
			if($page->getIdentifier() !=  $importData['identifier'] ){
				Mage::throwException(
					Mage::helper('catalog')->__('System has same <strong>page_id</strong> but different <strong>identifier</strong> for <strong>%s</strong> column in your csv. Overwrite avoided.', $importData['identifier'])
				);
			}
			*/
		}

		#column store_id
		if(!empty($importData['stores'])){
			$storesAry = explode(',', $importData['stores']);
			$page->setStores($storesAry);
		}else{
			$page->setStores(array($this->_defaultScope));
		}

		#column title
		if(!empty($importData['title'])){
			$page->setTitle($importData['title']);
		}

		#column root_template
		if(!empty($importData['root_template'])){
			$page->setRootTemplate($importData['root_template']);
		}

		#column identifier
		if(!empty($importData['identifier'])){
			$page->setIdentifier($importData['identifier']);
		}

		#column content
		if(!empty($importData['content'])){
			$page->setContent($importData['content']);
		}	
		
		#column is_active
		if(!empty($importData['is_active'])){
			$page->setIsActive($importData['is_active']);
		}else{
			$page->setIsActive($this->_defaultActive);
		}

		#column sort_order
		if(!empty($importData['sort_order'])){
			$page->setSortOrder($importData['sort_order']);
		}

		#content_heading
		if(!empty($importData['content_heading'])){
			$page->setContentHeading($importData['content_heading']);
		}
		
		#creation_time
		#update_time

		#meta_keywords
		if(!empty($importData['meta_keywords'])){
			$page->setMetaKeywords($importData['meta_keywords']);
		}

		#meta_description
		if(!empty($importData['meta_description'])){
			$page->setMetaDescription($importData['meta_description']);
		}
		#layout_update_xml
		if(!empty($importData['layout_update_xml'])){
			$page->setLayoutUpdateXml($importData['layout_update_xml']);
		}

		#custom_theme
		if(!empty($importData['custom_theme'])){
			$page->setCustomTheme($importData['custom_theme']);
		}

		#custom_root_template
		if(!empty($importData['custom_root_template'])){
			$page->setCustomRootTemplate($importData['custom_root_template']);
		}

		#custom_layout_update_xml
		if(!empty($importData['custom_layout_update_xml'])){
			$page->setCustomLayoutUpdateXml($importData['custom_layout_update_xml']);
		}
		
		#custom_theme_from
		if(!empty($importData['custom_theme_from'])){
			$page->setCustomThemeFrom($importData['custom_theme_from']);
		}

		#custom_theme_to
		if(!empty($importData['custom_theme_to'])){
			$page->setCustomThemeTo($importData['custom_theme_to']);
		}

		$page->save();
		return true;

    }
}
