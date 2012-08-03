<?php
class page_catlink extends Page {
  function init() {
    parent::init();
    $p=$this;
    
    $tabs=$p->add('Tabs');
//    $p=$tabs->addTab('Shop Cats');

    
    
    $s=$p->add('Model_Shop')->load(10);
    $shopsystem = ucwords($s->shopsystem());
    $s->setController($shopsystem);
    
    $shopcats=$this->add('Model_Prestashop_Category')->load(1)->tree();

    //var_dump($shopcats);
    $f=$p->add('Form');
    $f->addField('text','test');
    $f->addField('drilldown','dropdown')->setModel('Model_Prestashop_Category');// setValueList(array('a','b'));
    $f->addField('dropdown','dropdown')->setValueList($shopcats);
    $f->addSubmit();
    
    
    
    $c=$p->add('CRUD');
    $m=$s->ref('CatLink');
    $m->getField('category_id')->getModel()->addCondition('supplier_id','1');
    $m->getField('catshop_id')->datatype('list')->setValueList($shopcats);
    
  //  $m->getField('catshop_id');//->addCondition('supplier_id','1');
    
    $m->getField('margin_ratio')->type('inline,html');
    $m->getField('margin_amount')->type('inline,html');

   // var_dump($m->getActualFields()); // setModel needs second form aray and third grid array
    $c->setModel($m) ;//,array('category_id','catshop_id','margin_ratio','margin_amount'),array('catshop_id', 'category','category_id','margin_ratio','margin_amount'));
    if($c->form) {
//      $c->form->addField('dropdown','category shop')->setValueList($shopcats);
      if( $c->form->isSubmitted()) {
      $m->set('catshop_id',1240)->save();
        $c->form->js()->univ()->alert('Hello World')->execute();
        $this->text('test');
      }
    }
    if($c->grid) {
      $c->grid->addColumn('expander','expander');
      $c->grid->addPaginator(10);
    }
    
      /*      
      exit();
      
      
      
          $j_catlang=$m_shopcat->join('ps_category_lang.id_category','id_category');
    $j_catlang->addField('name');
    $j_catlang->addField('id_lang');
    $m_shopcat->addCondition('id_lang',6);

    $shopcat=$m_shopcat->getRows();
      
      
      
      
var_dump($shopcat);

foreach($shopcat as $cat) {
  $r[$cat['id_category']]=$cat['name'];
}
*/
//        $sc=$this->add('Grid');
//    $sc->setSource($shopcat);
    
//    $f->addField('dropdown','category')->setValueList($r);
    
      
        
/*
      $shopcat=$this->add('Model_Prestashop_Category');
          $catlang=$shopcat->join('ps_category_lang.id_category','id_category');
    $catlang->addField('name');
    $catlang->addField('id_lang');
    $shopcat->addCondition('id_lang',6);

      
      
      
    $sc=$this->add('CRUD');
    $sc->setModel($shopcat);
*/

    
  }
}
