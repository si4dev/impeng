<?php 

Class Model_UploadPricelist extends filestore\Model_file {

  function init() {
    parent::init();
    
    $this->addHook('beforeGenerateFilename',$this);
  }
  function beforeGenerateFilename($o) {
    $o->set('filename',$this->supplierpath);
  }
  	
	function getPath(){
		return $this->supplierpath;		
	}
    
  
}