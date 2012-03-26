<?php
class Model_Xcart_ExtraFieldValue extends Model_Table2 {
  public $table='xcart_extra_field_values';
  public $id_field='productid';
  public $title_field='productid';
  function init() {
    parent::init();
//    $this->hasOne('Xcart_ExtraField','fieldid');
    $this->addField('fieldid');
    $this->addField('value');
  }
}
