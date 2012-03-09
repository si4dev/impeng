<?php
class Model_Shop extends Model_Table {
  public $table='shop';
  function init() {
    parent::init();
    
    $this->addField('name');
    $this->hasOne('User',null,'login'); 
    $this->hasMany('Pricelist');
  }
}   