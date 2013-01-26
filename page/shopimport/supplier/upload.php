<?php 
class Page_shopimport_supplier_upload extends Page {
	function init(){
		parent::init();
    
    $s=$this->api->getShop();
    if($_GET['assortment_link_id']){
			$this->api->stickyGET('assortment_link_id');
      
      $m=$s->ref('AssortmentLink')->load($_GET['assortment_link_id']);

      if(!$m->get('is_owner')) {
        $this->add('P')->set('Geen upload nodig');
        return;
      }
      $supplier = $this->add('Model_Supplier')->load($m['source_assortment_id']);
      $paths = $supplier->getFiles();
		
      foreach($paths as $path){
        $f=$this->add('Form');
        $f->add('p')->set($path['file_name']);
        $m = $this->add('Model_uploadPricelist');
        $m->supplierpath = $path['path'];		
        $f->addField('upload', 'supplier_file')->setModel($m);
      }		
    }
	}
}