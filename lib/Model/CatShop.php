<?php
class Model_CatShop extends Model_Table {
  public $table='catshop';
  public $id_field = 'ref';
  public $title_field='title';
  function init() {
    parent::init();
    $this->hasOne('Shop');
    //$this->addField('ref');
    $this->addField('title');
    $this->addField('status');
    $this->addCondition('shop_id',$this->api->getShop()->id);
    
  }  
}