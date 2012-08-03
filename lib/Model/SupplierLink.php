<?php
class Model_SupplierLink extends Model_Table {
  public $table='supplierlink';
  function init() {
    parent::init();
    $this->hasOne('Supplier');
    $this->hasOne('Shop');
    $this->hasOne('Pricebook');
    $this->addField('prefix');
    $this->addField('login');
  }
  
}
