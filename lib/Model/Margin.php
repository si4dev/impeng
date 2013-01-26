<?php
class Model_Margin extends Model {

  function init() {
    parent::init();

    $this->addField('from')->type('money');
    $this->addField('ratio')->type('numeric')->defaultValue('1');
    $this->addField('amount')->type('money')->defaultValue('0')->type('money');
  }
/* not needed
  function defaultRow() {
    foreach($this->table as $row) {
      if($row['from'] == 0) return $this;
    }
    $this->unload()->set('from','0')->set('ratio','1')->set('amount','0')->save();
    return $this;
  }
*/
  function setOrder($field) {
    return $this->add('Controller_Model_Order')->setOrder($field);
  }

  function marge($price) {
    $found=false;$ratio=1;$amount=0;
    foreach($this as $key => $value) {
      if( $key > $price ) break;
      $found=$key;
    }
    if($found!==false) {
      $ratio=$value['ratio'];
      $amount=$value['amount'];
    }
    return $price*$ratio+$amount;
  }

}


/*
  Model based on array
  [1] Model will only show 1 record, the active record
  [2] with controller_data_array it will allow to save and load the record
  [3] $this->data containts active record. $this->table contains all records.
*/