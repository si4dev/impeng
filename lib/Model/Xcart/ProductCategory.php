<?php
class Model_Xcart_ProductCategory extends Model_Table2 {
  public $table='xcart_products_categories';
  public $id_field='categoryid';
  function init() {
  
    parent::init();
    $this->hasOne('Xcart_Product','productid');
    $this->addField('main');
    $this->setMasterField('main','Y');
    $this->addField('orderby')->defaultValue(0);
  }
}