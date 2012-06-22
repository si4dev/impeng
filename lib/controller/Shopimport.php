<?php

class Controller_Shopimport extends AbstractController {
  public $user;
  public $shop;
  
  function init() {
    parent::init();


    $u=$this->api->auth->model;
    echo count($u->ref('Shop'))."==<<==";
    if($shop_id=$this->api->recall('shop_id')) {
      $s=$u->ref('Shop')->load($shop_id);
    } else {
      $s=$u->ref('Shop')->tryLoadAny();
      $this->api->memorize('shop_id',$s->id);
    }
    
    $this->user=$u;
    $this->shop=$s;
    
        
  }
}
  