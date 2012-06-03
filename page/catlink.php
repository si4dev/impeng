<?php
class page_catlink extends Page {
  function init() {
    parent::init();
    $p=$this;
    
    $s=$p->add('Model_Shop')->load(10);
    $shopsystem = ucwords($s->shopsystem());
    $s->setController($shopsystem);
    $shopcats=$this->add('Model_Prestashop_Category')->load(1)->tree();

    $c=$p->add('CRUD');
    $m=$s->ref('CatLink');
    $m->getField('category_id')->getModel()->addCondition('supplier_id','1');
    $m->getField('catshop_id')->datatype('list')->setValueList($shopcats);
    $m->getField('margin_ratio')->type('inline,html');
    $m->getField('margin_amount')->type('inline,html');

    //var_dump($m->getActualFields()); // array(9) { [0]=> string(2) "id" [1]=> string(11) "category_id" [2]=> string(8) "category" [3]=> string(7) "shop_id" [4]=> string(4) "shop" [5]=> string(12) "margin_ratio" [6]=> string(10) "catshop_id" [7]=> string(6) "import" [8]=> string(13) "margin_amount" } 
   // setModel needs second form aray and third grid array
    $c->setModel($m,array('category_id','catshop_id','margin_ratio','margin_amount'),array( 'category','catshop_id','margin_ratio','margin_amount'));
    if($c->form) {
    }
    if($c->grid) {
      $c->grid->addPaginator(10);
    }
    
  }
}
