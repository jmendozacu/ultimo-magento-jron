<?php

class Medma_Exportcms_Model_Convert_Adapter_Cmsblocks extends Mage_Dataflow_Model_Convert_Adapter_Abstract{

    protected $_cmsBlockObj;

    protected $_reqFields = array(
		'title',
		'identifier',
		'content'
    );

    protected $_defaultScope = 0;

    protected $_defaultActive = 0;

    public function load() {

      $collection = Mage::getResourceModel('cms/block_collection');

	  $filterData = Mage::getSingleton('adminhtml/session')->getBlockFilterData();	
	  Mage::getSingleton('adminhtml/session')->unsBlockFilterData();  

	  if(is_array($filterData)){
		$filterData = array_filter($filterData);
	  }

	  if(count($filterData) > 0){
	  	$collection->addFieldToFilter('block_id', array('IN' => $filterData));
	  }	
	  	
	  $this->setData($collection);	
    }

    public function save() {
      // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface      
    }

    public function getBlockModel(){

        if (is_null($this->_cmsBlockObj)) {
            $blockModel = Mage::getModel('cms/block');
            $this->_cmsBlockObj = Mage::objects()->save($blockModel);
        }
        return Mage::objects()->load($this->_cmsBlockObj);
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
		$block = $this->getBlockModel();
		#column page_id
		if(!empty($importData['block_id'])){
			$blockId = (int)$importData['block_id'];
			$block->load($blockId);

			/*
			#check for a possible content overwrite when using a csv from some other Magento instance
			if($block->getIdentifier() !=  $importData['identifier'] ){
				Mage::throwException(
					Mage::helper('catalog')->__('System has same <strong>block_id</strong> but different <strong>identifier</strong> for <strong>%s</strong> column in your csv. Overwrite avoided.', $importData['identifier'])
				);
			}
			*/
		}

		#column store_id
		if(!empty($importData['stores'])){
			$storesAry = explode(',', $importData['stores']);
			$block->setStores($storesAry);
		}else{
			$block->setStores(array($this->_defaultScope));
		}

		#column title
		if(!empty($importData['title'])){
			$block->setTitle($importData['title']);
		}

		#column identifier
		if(!empty($importData['identifier'])){
			$block->setIdentifier($importData['identifier']);
		}

		#column content
		if(!empty($importData['content'])){
			$block->setContent($importData['content']);
		}	
		
		#column is_active
		if(!empty($importData['is_active'])){
			$block->setIsActive($importData['is_active']);
		}else{
			$block->setIsActive($this->_defaultActive);
		}

		$block->save();
		return true;

    }
}
