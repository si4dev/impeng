<?php
class Model_Rounding extends Model {
  
  function init() {
    parent::init();
    
    $this->addField('from');
    $this->addField('value');
    $this->addField('offset');
    
  }
}

/* 
  Model based on array
  [1] Model will only show 1 record, the active record
  [2] with controller_data_array it will allow to save and load the record
  [3] $this->data containts active record. $this->table contains all records.
*/