<?php
class Model_Rounding extends Model {
  function init() {
    parent::init();
    
    $this->addField('from');
    $this->addField('value');
    $this->addField('offset');
  }
}