<?php
class Page_Shopimport_Filter extends Page {
  function init() {
    parent::init();

    $si=$this->add('Controller_Shopimport'); // get user and shop
    $this->shop=$si->shop;
  }
  
  function initMainPage() {
        
    $s=$this->shop;

    // load the categories from the shop itself
    $shopcats=$s->getShopCategories();
    
    
    $filter=$s->prepareFilter();
    $filter->getField('catshop_id')->datatype('list')->setValueList($shopcats);


    $c=$this->add('CRUD');
    if($c->grid) {
      $c->grid->addColumn('expander','more');
      $c->grid->addColumn('expander','products');
      $c->grid->addPaginator(10);
    }
    $c->setModel($filter,array('catshop_id','margin_ratio','margin_amount'),array('category','catshop_id','margin_ratio','margin_amount','products'));
    //$c->addFormatter('category','grid/inline'); //->editFields(array('catshop_id'));  
    if($c->form) {
      $f=$c->form->getElement('margin_ratio');
      if($f->get() == NULL) $f->set(1);
      $f=$c->form->getElement('margin_amount');
      if($f->get() == NULL) $f->set(0); // ->set() should set to default value !!
    }
    
  }

  function page_more() {  
    $tabs=$this->add('Tabs');
    $p=$tabs->addTab('settings');
    $p=$tabs->addTabUrl('shopimport_category_product','products');
  }

  function page_products() {
    $this->api->stickyGET('id');
    $this->add('P')->set('id2='.$_GET['id']);
    $catid=$this->shop->ref('Filter')->load($_GET['id'])->get('category_id');
    $m=$this->shop->ref('ProductForPricelist')->addCondition('category_id',$catid);
    $this->add('Grid')
        ->addPaginator(10)
        ->setModel($m,array('productcode','title','manufacturer','manufacturer_code','ean','price','stock'));
     
  }
}

