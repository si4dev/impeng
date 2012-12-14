<?php
class Model_AttributeGroupLink extends Model_Table {
  public $table='attributegrouplink';
  function init() {
    parent::init();
    $this->hasOne('Shop');
    $this->hasOne('AttributeGroup');
    $this->addField('shopattr_ref');
    $this->addField('used')->editable(false);
  }
  
  
}
