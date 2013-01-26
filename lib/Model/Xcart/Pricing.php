<?php
class Model_Xcart_Pricing extends Model_Table2 {
  public $table='xcart_pricing';
  public $id_field='priceid';
  function init() {
  
    parent::init();
    $this->hasOne('Xcart_Product','productid');
    $this->addField('quantity')->defaultValue(1);
    $this->setMasterField('quantity',1);
    $this->addField('price');
    $this->hasMany('Xcart_QuickPrices','priceid'); // to allow to go to quick_prices
    }
}