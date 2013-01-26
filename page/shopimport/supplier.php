<?php
Class Page_shopimport_supplier extends Page {

	function init(){
		parent::init();

    // TODO: upload  button depending on is_owner field. Probably it needs to extend grud specific to this
    // TODO: very complicated, but need join on model in the future as now ugly join on table

    $s=$this->api->getShop();
    $m=$s->ref('AssortmentLink');
    $supplier=$m->join('assortment','source_assortment_id');
    $supplier->addField('name');
    $supplier->addField('branch');

		$g = $this->add('Grid');
		$g->setModel($m, array('name', 'branch'));
        
            $g->addColumn('expander' ,'upload');
       
	}


}