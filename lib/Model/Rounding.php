<?php
class Model_Rounding extends Model {
  
  function init() {
    parent::init();
    
    $this->addField('from')->type('money');
    $this->addField('value')->type('money')->defaultValue('0.01');
    $this->addField('offset')->type('money')->defaultValue('0');
  }
/*
  function defaultRow() {
    foreach($this->table as $row) {
      if($row['from'] == 0) return $this;
    }
    $this->unload()->set('from','0')->set('value','0.01')->set('offset','0')->save();
    return $this;
  }
*/

  function setOrder($field) {
    return $this->add('Controller_Model_Order')->setOrder($field);
  }
}

/* 
  Model based on array
  [1] Model will only show 1 record, the active record
  [2] with controller_data_array it will allow to save and load the record
  [3] $this->data containts active record. $this->table contains all records.
*/