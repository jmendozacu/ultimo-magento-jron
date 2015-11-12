<?php
/**
 * Medma_Exportcms Module
 */
class Medma_Exportcms_Block_Adminhtml_Cms_Page_Grid extends Mage_Adminhtml_Block_Cms_Page_Grid{

	public function __construct(){

        parent::__construct();

		$this->setBlockType('pages');
		$this->setTemplate('exportcms/widget/grid.phtml');

		#register domain event starts
        $domainName = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$generalEmail = Mage::getStoreConfig('trans_email/ident_general/email');
		
		Mage::dispatchEvent('medma_domain_authentication',
			array(
			'domain_name'=>$domainName,
			'email' => $generalEmail,
			)

		);
		#register domain event ends
    }
	
	protected function _prepareLayout(){
		
		$this->setChild('csv_import_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Import from CSV'),
                    'onclick'   => "attemptImport();",
                    'class'   => 'task'
                ))
        );
		$this->setChild('csv_export_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Export All to CSV'),
                    'onclick'   => "attemptExport();",
                    'class'   => 'task'
                ))
        );

        return parent::_prepareLayout(); 
    }

	protected function _prepareColumns(){

		$this->addColumn('page_id',array(
			'header' => Mage::helper('cms')->__('Page Id'),
			'width'  => '50px',
			'index'  => 'page_id',
		));

		parent::_prepareColumns();
	}

	protected function _prepareMassaction(){
        $this->setMassactionIdField('page_id');
        $this->getMassactionBlock()->setFormFieldName('page');

		$exportProfile = $this->_getHelper()->getExportProfileId('pages');
		
		$this->getMassactionBlock()->addItem('export', array(
             'label'=> Mage::helper('cms')->__('Export to Csv'),
             'url'  => $this->getUrl('*/*/massExportPages', array('id' => $exportProfile, 'profileType' => 'pages')),
        ));

		$this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('cms')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
        ));

        parent::_prepareMassaction();
    }

	protected function _getHelper(){
		return $this->helper('exportcms');
	}

	public function getCsvExportButtonHtml(){
		return $this->getChildHtml('csv_export_button');
	}

	public function getCsvImportButtonHtml(){
		return $this->getChildHtml('csv_import_button');
	}	

	protected function getImportUrl(){
		$importProfile = $this->_getHelper()->getImportProfileId('pages');
		$url = $this->helper('adminhtml')->getUrl('*/system_convert_profile/run', array('id' => $importProfile));
		return $url;
	}

	protected function getExportUrl(){
		$importProfile = $this->_getHelper()->getExportProfileId('pages');
		$url = $this->helper('adminhtml')->getUrl('*/system_convert_profile/run', array('id' => $importProfile));
		return $url;
	}
	
}
