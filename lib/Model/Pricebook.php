<?php
class Model_Pricebook extends Model_Table {
  public $table='pricebook';
  function init() {
    parent::init();
    $this->addField('name');
  }
}
