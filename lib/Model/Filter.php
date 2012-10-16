<?php
class Model_Filter extends Model_Table {
  public $table='filter';
  function init() {
    parent::init();
    $this->hasOne('Shop');
    $this->hasOne('Category');
    $this->addField('keyword');
    $this->addField('margin_ratio');// ->defaultValue(1);
    $this->addField('margin_amount'); //->defaultValue(0);
    $this->hasOne('CatShop');
    $this->addField('import');
    $this->addField('active');

  }
  
}
