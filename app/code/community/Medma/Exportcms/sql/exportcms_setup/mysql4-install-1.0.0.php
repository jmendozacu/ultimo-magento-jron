<?php

$installer = $this;
$installer->startSetup();	

$read = $installer->getConnection('core_read');
$write = $installer->getConnection('core_write');

$table = $installer->getTable('dataflow/profile');
$cmsPageExportName = Medma_Exportcms_Model_Resource_Setup::EXPORT_PROFILE_NAME;	
$cmsPageImportName = Medma_Exportcms_Model_Resource_Setup::IMPORT_PROFILE_NAME;	
$cmsBlocksExportName = Medma_Exportcms_Model_Resource_Setup::EXPORT_PROFILE_NAME_FOR_BLOCKS;	
$cmsBlocksImportName = Medma_Exportcms_Model_Resource_Setup::IMPORT_PROFILE_NAME_FOR_BLOCKS;		

/*Insert CMS pages Import Profile*/
$isImportProfileExist = $read->select()
	 			->from(array('p' => $table), new Zend_Db_Expr('count(*)'))
	 			->where('name = ?', $cmsPageImportName);	
$importExist = $read->fetchOne($isImportProfileExist);
if($importExist == 0){
	$this->_profilesForPages['import']['created_at'] = date('Y-m-d H:i:s');
	$this->_profilesForPages['import']['updated_at'] = date('Y-m-d H:i:s');	
	$write->insert($table, $this->_profilesForPages['import']);
}

/*Insert CMS pages Export Profile*/
$isExportProfileExist = $read->select()
	 			->from(array('p' => $table), new Zend_Db_Expr('count(*)'))
	 			->where('name = ?', $cmsPageExportName);	
$exportExist = $read->fetchOne($isExportProfileExist);
if($exportExist == 0){
	$this->_profilesForPages['export']['created_at'] = date('Y-m-d H:i:s');
	$this->_profilesForPages['export']['updated_at'] = date('Y-m-d H:i:s');	
	$write->insert($table, $this->_profilesForPages['export']);
}

/*Insert CMS Blocks Export Profile*/
$exportBlockProfileSelect = $read->select()
	 			->from(array('p' => $table), new Zend_Db_Expr('count(*)'))
	 			->where('name = ?', $cmsBlocksExportName);	
$exportExist = $read->fetchOne($exportBlockProfileSelect);
if($exportExist == 0){
	$this->_profilesForBlocks['export']['created_at'] = date('Y-m-d H:i:s');
	$this->_profilesForBlocks['export']['updated_at'] = date('Y-m-d H:i:s');	
	$write->insert($table, $this->_profilesForBlocks['export']);
}
/*Insert CMS Blocks Import Profile*/
$importBlockProfileSelect = $read->select()
	 			->from(array('p' => $table), new Zend_Db_Expr('count(*)'))
	 			->where('name = ?', $cmsBlocksImportName);	
$importExist = $read->fetchOne($importBlockProfileSelect);
if($importExist == 0){
	$this->_profilesForBlocks['import']['created_at'] = date('Y-m-d H:i:s');
	$this->_profilesForBlocks['import']['updated_at'] = date('Y-m-d H:i:s');	
	$write->insert($table, $this->_profilesForBlocks['import']);
}

//$write->insertMultiple($table, $this->_profiles);
$installer->endSetup();	
