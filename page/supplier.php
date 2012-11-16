<?php 
Class Page_Supplier extends Page {

	function init(){
		parent::init();
		
		$link = $this->add('Model_SupplierLink');
		$shop_id = $this->api->recall('shop_id');
	
		$link_id = $link->dsql()->field('supplier_id')->where('shop_id', $shop_id);
		
		$m = $this->add('Model_Supplier');
		$m->addCondition('id', $link_id);
		$g = $this->add('Grid');
		$g->setModel($m, array('name' ,'friendly_name','branch'));
		$g->addColumn('expander' ,'upload');
		
	}
}