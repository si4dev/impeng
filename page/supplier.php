<?php 
Class Page_Supplier extends Page {

	function init(){
		parent::init();
		
		$link = $this->add('Model_SupplierLink');
		$si = $this->add('Controller_Shopimport');
	
		$link_id = $link->dsql()->field('supplier_id')->where('shop_id', $si->shop->id);
		
		$m = $this->add('Model_Supplier');
		$m->addCondition('id', $link_id);
		$g = $this->add('Grid');
		$g->setModel($m, array('name' ,'friendly_name','branch'));
		$g->addColumn('expander' ,'upload');
		
	}
}