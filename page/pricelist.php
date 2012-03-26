<?php

class page_pricelist extends Page {
  function init() {
    parent::init();

    $this->add('H1')->set('Pricelist');
<<<<<<< HEAD
    if( $shop_id=$_GET['shop'] ) {
      $this->add('Text')->set('shop '.$shop_id);
      $shop=$this->add('Model_Xcart');
      $shop->import()
       $t=$this->add('P')->set(str_repeat('.',$i));  
=======

    //      $g=$this->add('Grid');
    //  $pricelist = $this->ref('Pricelist');

//     $g->setModel($pricelist);
//      $g->addFormatter('price','money');
//      $g->addPaginator(10);
      /*
      $text='';
      //print_r( $pricelist->getActualFields() );
 */     

        
    if( $shop_id=$_GET['shop'] ) {
      $this->add('Text')->set('shop '.$shop_id);
      $shop = $this->add('Model_Shop');
      $shop->load($shop_id);
      $shopsystem = ucwords('xcart');
      $shop->unload();
      
      $shop = $this->add('Model_'.$shopsystem);
      $shop->load($shop_id)->import_categories();
      //$shop->load($shop_id)->import();
      //$t=$this->add('P')->set('Imported products: '.$shop->nb_products);
>>>>>>> 8b4bd199e8d18e7240ccba6ba1497a1e165bdeb6
    } else {
      $this->add('Text')->set('no shop selected');
    }
      
   
  }
    
}