<?php
class Model_Filter extends Model_Table {
  public $table='filter';
  function init() {
    parent::init();
    $this->hasOne('Shop');
    $this->hasOne('Category','source_category_id'); // supplier category
    $this->addField('keyword');
    $this->addField('margin_ratio');// ->defaultValue(1);
    $this->addField('margin_amount'); //->defaultValue(0);
    $this->hasOne('Category','target_category_id');
    $this->addField('import');
    $this->addField('active');
  }
  
  function getSupplier() {
    $this->addExpression('source_assortment_id')->set(function($m,$q){ 
        return $m->refSQL('source_category_id')->fieldQuery('assortment_id');
    });
    return $this;
  }
}
