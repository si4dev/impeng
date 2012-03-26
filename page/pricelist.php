<?php

class page_pricelist extends Page {
  function init() {
    parent::init();

    $this->add('H1')->set('Pricelist');
    if( $shop_id=$_GET['shop'] ) {
      $this->add('Text')->set('shop '.$shop_id);
      $shop=$this->add('Model_Xcart');
      $shop->import()
       $t=$this->add('P')->set(str_repeat('.',$i));  
    } else {
      $this->add('Text')->set('no shop selected');
    }
      
   
  }
    
}