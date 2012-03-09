<?php
class Model_Pricelist extends Model_Table {
  public $table='pricelist';
  function init() {
    parent::init();
    
    $this->addField('shop_productcode');
    $this->addField('product_title');
    $this->addField('price');
    $this->hasOne('Shop');
    
  }
}

