<?php 


class Page_supplier_upload extends Page {
	function init(){
		parent::init();
		
		if($_GET['supplier_id']){
			$this->api->stickyGET('supplier_id');
			$id = $_GET['supplier_id'];
		}
			
		$s= $this->add('Model_Supplier');
		$s->load($id);
		$paths = $s->getFiles();
		
		foreach($paths as $path){
			$f=$this->add('Form');
			$f->add('p')->set($path['file_name']);
			$m = $this->add('Model_uploadPricelist');
			$m->supplierpath = $path['path'];		
			$f->addField('upload', 'supplier_file')->setModel($m);
		}		
	}
}