<?php
class Controller_Prestashop extends AbstractController {
  function init() {
    parent::init();
    
    // use with setController on the Model_Shop
    // then the $this->owner is the Model_Shop
    $this->api->db2=$this->api->add('DB')->connect($this->owner->connection());

  }
  
  function import_shopcat() {
    $m=$this->owner->ref('CatShop')->deleteAll();
    $shopcats=$this->add('Model_Prestashop_Category')->load(1)->tree();
    foreach($shopcats as $ref => $title) {
      $m->unload()
          ->set('ref',$ref)
          ->set('title',$title)->save();
    }
  }
    
  
}