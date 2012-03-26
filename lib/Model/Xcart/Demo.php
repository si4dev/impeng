<?php
class Model_Xcart_Demo extends Model_Table2 {
  public $table='demo';
  function init() {
    parent::init();
    $this->addField('sometext');
    //$this->addField('title');
  }
}
