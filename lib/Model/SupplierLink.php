<?php
class Model_SupplierLink extends Model_Table {
  public $table='supplierlink';
  function init() {
    parent::init();
    $this->hasOne('Supplier');
    $this->hasOne('Shop');
    $this->hasOne('Pricebook')->defaultValue(1);
    $this->addField('prefix');
    $this->addField('login');
	  $this->addField('is_owner')->type('boolean')->defaultValue(false);
  }  
}
