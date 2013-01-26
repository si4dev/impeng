<?php

class page_pricelist extends Page {
  function init() {
    parent::init();

    $this->add('H1')->set('Pricelist');


    if(isset($_GET['key']) and $_GET['key']===$this->api->getConfig('key',null)) {
      $s=$this->add('Model_Shop')->loadBy('name',$_GET['shop']);
    } else {
      $s=$this->api->getShop();
    }


    // set the script memory limit
    if(isset($memory_limit)) {
      ini_set("memory_limit", $memory_limit);
    }
    // set the script timeout and database timeeout
    $timeout='6000';
    if( isset($timeout) ) {
      set_time_limit($timeout);
      ini_set('default_socket_timeout', ini_get('max_execution_time'));
    }

    $this->add('Text')->set('shop '.$s->id);
//    $s->pricelist(); // build pricelist WE DON"T NEED THIS STEP ANYLONGER
    try{
      $s->import();
    } catch  (Exception_FTP $e){
       foreach($e->more_info as $key=>$value){
            $args[]=$key.'='.$value;
        }

      $this->add('View_Error')->set('Error '.$e->getMessage() . '['.implode(', ',$args).']' );
    }




      /* keep as its mentioned to take over categories from one supplier however code is getting too complex and not working

      $s->getShopCategories(); // fill table catshop
      // get config for category import
      if($supplier_label=$s->category_import()) {
        $this->add('P')->set('Take over categories from supplier '.$supplier_label);
        // get supplier id from supplier label name
        $supplier_id=$s->ref('AssortmentLink')->loadBy('source_assortment',$supplier_label)->get('source_assortment_id');
        $filter=$s->ref('Filter');
        $s->prepareFilter();
        $filter->addCondition('active', '>', '0')
            ->getSupplier()
            ->addCondition('source_assortment_id',$supplier_id)
            ->addCondition('target_category_id',null);

        $s->importCategories( $filter );

        $s->getShopCategories(); // fill table catshop again as it's not up to date due to new categories

      }
        */

return; // phase out code after this line:
      // *** import categories ***
      $sc->import_categories();
      $this->add('P')->set('Imported categories: '.$sc->nb_categories);
      // *** import products ***
      $sc->import();
      $this->add('P')->set('Imported products: '.$sc->nb_products);

  }
}