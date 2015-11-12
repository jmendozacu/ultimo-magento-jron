<?php

class Medma_Exportcms_Helper_Data extends Mage_Core_Helper_Data{

	/*
	 * Load CMS pages/blocks Export profile
	 */
	public function getExportProfileId($type){

		if($type){

			$collection = Mage::getResourceModel('dataflow/profile_collection');
			if($type == 'pages'){
				$collection->addFieldToFilter(
					'name',
					array('eq'=> Medma_Exportcms_Model_Resource_Setup::EXPORT_PROFILE_NAME)
				);
			}elseif($type == 'blocks'){
				$collection->addFieldToFilter(
					'name',
					array('eq'=> Medma_Exportcms_Model_Resource_Setup::EXPORT_PROFILE_NAME_FOR_BLOCKS)
				);
			}else{
				return NULL;
			}
		
			$collection->addFieldToSelect('profile_id');
			$collection->setPageSize(1);
			return $collection->getFirstItem()->getData('profile_id');
		}else{
			return NULL;
		}
	}

	/*
	 * Load CMS pages/blocks Import profile
	 */
	public function getImportProfileId($type){

		if($type){
			$collection = Mage::getResourceModel('dataflow/profile_collection');
			if($type == 'pages'){
				$collection->addFieldToFilter(
					'name',
					array('eq'=> Medma_Exportcms_Model_Resource_Setup::IMPORT_PROFILE_NAME)
				);
			}elseif($type == 'blocks'){
				$collection->addFieldToFilter(
					'name',
					array('eq'=> Medma_Exportcms_Model_Resource_Setup::IMPORT_PROFILE_NAME_FOR_BLOCKS)
				);
			}else{
				return NULL;
			}
		
			$collection->addFieldToSelect('profile_id');
			$collection->setPageSize(1);
			return $collection->getFirstItem()->getData('profile_id');
		}else{
			return NULL;
		}
	}

	/*
	 * Check massExportPagesAction() & massExportBlocksAction() in controllers
	 */
	public function _initProfile($profileId, $profileType){

		$profile = Mage::getModel('dataflow/profile');

        if ($profileId) {
            $profile->load($profileId);
            if (!$profile->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('The Exportcms profile no longer exists'));
                $this->_redirect('*/*');
                return false;
            }
        }

        Mage::register('current_convert_profile', $profile);
        Mage::getSingleton('adminhtml/session')->setExportCmsType($profileType);

        return $this;
	}

	/*
	 * Check CSV row data for valid XML structure
	 */
	public function _validatePostDataForPages($xmlField){
        $error = array();
        if (!empty($xmlField)) {
            /** @var $validatorCustomLayout Mage_Adminhtml_Model_LayoutUpdate_Validator */
            $validatorCustomLayout = Mage::getModel('adminhtml/layoutUpdate_validator');
            if (!$validatorCustomLayout->isValid($xmlField)) {
                foreach ($validatorCustomLayout->getMessages() as $message) {
					$error[] = $message;               
            	}
            }
        }
        return $error;
    }		
}
