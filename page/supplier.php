<?php 
Class Page_supplier extends Page {

	function init(){
		parent::init();

		if($_GET['debugmode'] == 'on'){
			$dbug = $this->api->getLogger();
			$dbug->set('supplier page custom message');
		}
		
		$user = $this->api->auth->model;
		$shop_id = $this->api->recall('shop_id');
		
		$link = $this->add('Model_SupplierLink');
		$slink = $link->tryLoadBy('shop_id', $shop_id); 
		if($slink->Loaded()){
	
		// $link_id = $link->dsql()->field('supplier_id')->where('shop_id', $shop_id);
		
		$m = $this->add('Model_Supplier');
		// $m->addCondition('id', $link_id);
		$m->addCondition('id', $slink['supplier_id']);
		
		$g = $this->add('Grid');
		$g->setModel($m, array('name' ,'friendly_name','branch'));
		if($slink['is_owner'] == true){
			$g->addColumn('expander' ,'upload');	
		}
		
		}
	}
	

}