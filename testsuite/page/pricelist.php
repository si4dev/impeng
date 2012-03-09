<?php

class page_pricelist extends Page {
  function init() {
    parent::init();

    $this->add('H1')->set('Pricelist');
    if( $shop_id=$_GET['shop'] ) {
      $this->add('Text')->set('shop '.$shop_id);
      $shop = $this->add('Model_Shop');
      $shop->load($shop_id);
//      $g=$this->add('Grid');
      $pricelist = $shop->ref('Pricelist');
//  $pricelist->debug();

//     $g->setModel($pricelist);
//      $g->addFormatter('price','money');
//      $g->addPaginator(10);
      /*
      $text='';
      //print_r( $pricelist->getActualFields() );

 */     

      $m=$this->add('Model_Xcart_Product');
      
      $pricelist->selectQuery();
      $i=0;
      foreach( $pricelist as $product ) {
        $m->loadBy('productcode',$product['shop_productcode']);

        $m->import( $product );
        $text .= '. ';
        if( $i++ > 2 ) break; 

      }
      $t=$this->add('P')->set($text);
    } else {
      $this->add('Text')->set('no shop selected');
    }
      
   
  }
    
}