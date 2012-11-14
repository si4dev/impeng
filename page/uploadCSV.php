<?php 

Class Page_UploadCSV extends Page {
	function init(){
		parent::init();
		
		$this->add('h1')->set('Upload CSV Supplier file');
		$this->add('hr');
				
		$f = $this->add('Form');
		
		$f->addField('upload','myfile')
			->setModel('uploadCSV')
		;	
		
	}
}