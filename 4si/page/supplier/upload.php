<?php 


class Page_Supplier_Upload extends Page {
	function init(){
		parent::init();
		
		if($_GET['supplier_id']){
			$id = $_GET['supplier_id'];
			$this->api->memorize('sup_id', $id);
		}
		else
		{
			$id = $this->api->recall('sup_id');
		}
		
			
		$s= $this->add('Model_Supplier');
		$s->load($id);
		$paths = $s->getFiles();
		
		foreach($paths as $path){
			$f=$this->add('Form');
			$f->add('p')->set($path);
			$m = $this->add('Model_uploadPricelist');
			$m->supplierpath = $path;		
			$f->addField('upload', 'supplier_file')->setModel($m);
		}		
	}
}