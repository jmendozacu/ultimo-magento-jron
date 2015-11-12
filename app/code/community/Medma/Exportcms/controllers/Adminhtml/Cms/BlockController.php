<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml'). DS . 'Cms'. DS .'BlockController.php';
class Medma_Exportcms_Adminhtml_Cms_BlockController extends Mage_Adminhtml_Cms_BlockController{

	public function massExportBlocksAction(){

		$profileId = $this->getRequest()->getParam('id', null);
		$profileType = $this->getRequest()->getParam('profileType', null);

		$helper = Mage::helper('exportcms');
		$helper->_initProfile($profileId, $profileType);

		$blockIds = $this->getRequest()->getParam('block');
		if(is_array($blockIds) && count($blockIds) > 0){
			Mage::getSingleton('adminhtml/session')->setBlockFilterData($blockIds);
		}

		$this->loadLayout();
        $this->renderLayout();
	}

	public function massDeleteAction(){

        $blockIds = $this->getRequest()->getParam('block');
        if (!is_array($blockIds)) {
            $this->_getSession()->addError($this->__('Please select block(s).'));
        } else {
            if (!empty($blockIds)) {
                try {
                    foreach ($blockIds as $blockId) {
                        $block = Mage::getSingleton('cms/block')->load($blockId);
                        Mage::dispatchEvent('cms_controller_block_delete', array('block' => $block));
                        $block->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($blockIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

}
