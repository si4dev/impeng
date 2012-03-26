<?php
class Model_Xcart_ExtraField extends Model_Table2 {
  public $table='xcart_extra_fields';
  public $id_field='fieldid';
  public $title_field='field';
  function init() {
    parent::init();
    $this->addField('provider')->defaultValue(1);
    $this->addField('field')->defaultValue('Shopimport');
    $this->addField('service_name');
    $this->setMasterField('service_name','SHOPIMPORT');
//     $this->hasMany('Xcart_Product','filedid');
  }
}

