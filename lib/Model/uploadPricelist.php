<?php 

Class Model_UploadPricelist extends filestore\Model_file {
	
	function getPath(){
		return $this->supplierpath;		
	}
	function generateFilename(){
	}
}