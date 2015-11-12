<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml'). DS . 'Cms'. DS .'PageController.php';
class Medma_Exportcms_Adminhtml_Cms_PageController extends Mage_Adminhtml_Cms_PageController {
	
	public function massExportPagesAction(){

		$profileId = $this->getRequest()->getParam('id', null);
		$profileType = $this->getRequest()->getParam('profileType', null);

		$helper = Mage::helper('exportcms');
		$helper->_initProfile($profileId, $profileType);

		$pageIds = $this->getRequest()->getParam('page');
		if(is_array($pageIds) && count($pageIds) > 0){
			Mage::getSingleton('adminhtml/session')->setPageFilterData($pageIds);
		}

		$this->loadLayout();
        $this->renderLayout();
	}

	public function massDeleteAction(){

        $pageIds = $this->getRequest()->getParam('page');
        if (!is_array($pageIds)) {
            $this->_getSession()->addError($this->__('Please select page(s).'));
        } else {
            if (!empty($pageIds)) {
                try {
                    foreach ($pageIds as $pageId) {
                        $page = Mage::getSingleton('cms/page')->load($pageId);
                        Mage::dispatchEvent('cms_controller_page_delete', array('page' => $page));
                        $page->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($pageIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }
	
}
