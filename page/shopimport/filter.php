<?php
class Page_Shopimport_Filter extends Page {
  function init() {
    parent::init();
  }
  
  function initMainPage() {
      
    $this->add('hr');
    $form= $this->add('form',null,null,array('form_horizontal'));
    
    $s=$this->api->getShop();

    $non_active = $form->addfield('dropdown', 'non-active')->setValueList(array('hide', 'show'));

    $supplierModel=$s->ref('AssortmentLink');
    $supplierModel->join('assortment','source_assortment_id')->addField('name');
        
    $slist=$form->addField('dropdown' , 'supplier'); //slist as supplier list
    $slist->setModel($supplierModel);
    $slist->setEmptyText('Alle leveranciers');   

    $c=$this->add('CRUD');
    $filter=$s->ref('Filter');
    if(!$this->api->isAjaxOutput()) { // used to check if we rebuild category table and get store categories
      // load the categories from the shop itself into table catshop
      $s->getShopCategories();
      
      // prepare filters with new categories from suppliers, it's done in the shop model
      $s->prepareFilter();
    }
  //      $filter->getField('catshop_id')->datatype('list')->setValueList($shopcats);  //datatype('list')->setValueList(array(1=>'een',2=>'twee')); //$shopcats);

	
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
	  $filter->addCondition('source_assortiment', $s_name);
	}

//TODO:check field names
// array(13) { [0]=> string(2) "id" [1]=> string(14) "assortment_id" [2]=> string(11) "assortment" [3]=> string(18) "source_category_id" [4]=> string(15) "source_category" [5]=> string(7) "keyword" [6]=> string(12) "margin_ratio" [7]=> string(13) "margin_amount" [8]=> string(18) "target_category_id" [9]=> string(15) "target_category" [10]=> string(6) "import" [11]=> string(6) "active" [12]=> string(21) "source_assortment_id" }
    $c->setModel($filter,array('source_category_id','target_category_id','margin_ratio','margin_amount','keyword'),array('products', 'source_assortiment','source_category','target_category','keyword','margin_ratio','margin_amount','active' ));
//    $c->setModel($filter,array('target_category_id','target_category','margin_ratio','margin_amount','keyword'),array('products', 'source_assortment_id','source_category','target_category','keyword','margin_ratio','margin_amount','active' ));
    $c->dq->order('source_category_id');

//foreach($filter->elements as $key=>$value) var_dump($key);
    if($c->form){	
      $filter->getElement('target_category_id')->model->addCondition('assortment_id',$s->id); 
      
      
    }



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

