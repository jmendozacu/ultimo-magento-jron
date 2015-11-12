<?php

class Medma_Exportcms_Model_Convert_Parser_Cmsblocksexport extends Mage_Dataflow_Model_Convert_Parser_Abstract{

	public function parse(){
	}

	public function unparse(){

		$blocks = $this->getData();
		$row = array();
		
		
		foreach($blocks as $i=> $block){
			if($blockId = $block->getBlockId()){
				$block->load($blockId);
			}
			
			$row['block_id']= $blockId; 
			$row['stores'] = implode(',', $block->getStoreId());
			$row['title']  = $block->getTitle();
			$row['identifier'] = $block->getIdentifier();
			$row['content'] = $block->getContent();
			$row['is_active'] = $block->getIsActive();
			
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
