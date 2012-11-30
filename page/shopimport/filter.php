<?php
class Page_Shopimport_Filter extends Page {
  function init() {
    parent::init();

  }
  
  function initMainPage() {

	  
	$this->add('hr');
	$form= $this->add('form',null,null,array('form_horizontal'));
	
	$non_active = $form->addfield('dropdown', 'non-active')->setValueList(array('hide', 'show'));
	$slist= $form->addField('dropdown' , 'supplier'); //slist as supplier list
	
	$supplier = $this->add('Model_Supplier');
	//manualy add valuelist because setModel dont give 0 as default value.
	$list = $supplier->dsql()->field('name')->get();
	$valuelist= array();
	$valuelist[] = 'All'; //0 default value
	foreach($list as $datas){
		foreach($datas as $key => $value){
		  $valuelist[]=$value;
		}
	}
	
	$slist->setValueList($valuelist);
	
    $s=$this->api->getShop();


    // load the categories from the shop itself into table catshop
    $s->getShopCategories();
		
	$c=$this->add('CRUD');
    // prepare filters with new categories from suppliers, it's done in the shop model
    $filter=$s->prepareFilter();
//      $filter->getField('catshop_id')->datatype('list')->setValueList($shopcats);  //datatype('list')->setValueList(array(1=>'een',2=>'twee')); //$shopcats);

	if($filter->loaded()){	$filter->getElement('catshop_id')->model->addCondition('shop_id',$s->id); }
	
	// show filters
	 if($c->grid) {
      $g = $c->grid;
	  $g->addColumn('expander','products');
      $g->addPaginator(100);
      $g->addQuickSearch(array('category'));
	  
	  $non_active->js('change', array(
		$g->js()->reload(array('non-active' => $non_active->js()->val()))
		) );
	  $slist->js('change', $g->js()->reload(array('supplier' => $slist->js()->val())) );
	}
	//get Supplier name
	$filter->getSupplier();
	
	//check for active only (active > 1)
	if(!isset($_GET['non-active']) || $_GET['non-active'] == 0){		
		 $filter->addCondition('active', '>', '0');
	}
	
	//Supplier filter
	if(isset($_GET['supplier']) && $_GET['supplier'] != 0){
	  $s_id= $_GET['supplier'];
	  $slist->set($s_id);
	  $s_name = $supplier->dsql()->field('name')->where('id', $s_id);
	  $filter->addCondition('supplier', $s_name);
	}
	 $sl = $this->add('Model_Supplierlink'); // sl as supplierlink
	 $sl->tryloadBy('shop_id', $s['id']);

	 if($sl->loaded()){
	  	$filter->addCondition('id' , $sl['supplier_id'] );
	 }
	  

	$filter->debug();
	
    $c->setModel($filter,array('catshop_id','catshop','margin_ratio','margin_amount','keyword'),array('products', 'supplier','category','catshop','keyword','margin_ratio','margin_amount','active', ));
    $c->dq->order('category_id');


	//$c->addFormatter('category','grid/inline'); //->editFields(array('catshop_id'));  
    if($c->form) {
      $f=$c->form->getElement('margin_ratio');
      if($f->get() == NULL) $f->set(1);
      $f=$c->form->getElement('margin_amount');
      if($f->get() == NULL) $f->set(0); // ->set() should set to default value !!
    }
  }

  function page_products() {
    $this->api->stickyGET('id');
    $s=$this->api->getShop();
    $m=$s->ref('ProductForPricelist');
    // not working: $m->addCondition($m->getElement('filter_id'),$_GET['id'])->debug();
    $m->_dsql()->where('ff.id',$_GET['id']);
    $this->add('Grid')
        ->addPaginator(500)
        ->setModel($m,array('productcode','title','manufacturer','manufacturer_code','ean','price','stock'));     
  }

}

