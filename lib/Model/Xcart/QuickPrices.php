<?php
class Model_Xcart_QuickPrices extends Model_Table2 {
  public $table='xcart_quick_prices';
  public $id_field='productid';
  function init() {
  
    parent::init();
    $this->addField('priceid');
    // xcart_quick_prices table has primary key priceid + membershipid. 
    // So by keeping membership=0 with master field it will remain unique as atk4 will only allow 1 field as primary id
    $this->addField('membershipid')->defaultValue(0);
    $this->setMasterField('membershipid',0);
    $this->addField('variantid')->defaultValue(0);
  }
}

