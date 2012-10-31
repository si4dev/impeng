<?php
class Page_Shopimport_Filter extends Page {
  function init() {
    parent::init();

    $si=$this->add('Controller_Shopimport'); // get user and shop
    $this->shop=$si->shop;
  }
  
  function initMainPage() {
  
	$cform= $this->add('form');
	$cbox = $cform->addfield('checkbox', 'non-active')->set(0);	
	$cbox->js('change', $cform->js()->submit());	
	
	if($cform->isSubmitted()){
		$this->api->redirect($this->api->url, array('non-active' => $cform->get('non-active')));
	}
	

        
    $s=$this->shop;

    // load the categories from the shop itself into table catshop
    $s->getShopCategories();
	
	$c=$this->add('CRUD');
    
    // prepare filters with new categories from suppliers, it's done in the shop model
    $filter=$s->prepareFilter();
//      $filter->getField('catshop_id')->datatype('list')->setValueList($shopcats);  //datatype('list')->setValueList(array(1=>'een',2=>'twee')); //$shopcats);

    $filter->getElement('catshop_id')->model->addCondition('shop_id',$s->id);	
	
	//check for active only (active > 1)
	if(!isset($_GET['non-active']) || $_GET['non-active'] == 0){		
		 $filter->addCondition('active', '>', '0');
	}
	else{
		$cbox->set(1);
	}
	
    // show filters
    if($c->grid) {
      $c->grid->addColumn('expander','products');
      $c->grid->addPaginator(50);
      $c->grid->addQuickSearch(array('category'));
    }
    
    $c->setModel($filter,array('catshop_id','catshop','margin_ratio','margin_amount','keyword'),array('products','category','catshop','keyword','margin_ratio','margin_amount','active'));
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
    $m=$this->shop->ref('ProductForPricelist');
    // not working: $m->addCondition($m->getElement('filter_id'),$_GET['id'])->debug();
    $m->_dsql()->where('ff.id',$_GET['id']);
    $this->add('Grid')
        ->addPaginator(500)
        ->setModel($m,array('productcode','title','manufacturer','manufacturer_code','ean','price','stock'));
     
  }
}

