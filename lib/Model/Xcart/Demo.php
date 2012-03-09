<?php
class Model_Xcart_Demo extends Model_Xcart {
  public $table='demo';
  function init() {
    parent::init();
    $this->addField('sometext');
  }
}
