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

  function setOrder($field='from') {
    return $this->add('Controller_Model_Order')->setOrder($field);
  }

  //------------------------------------------------------------------------------------------------
  // round the price based on the rounding rules
  function round($price) {
      // find rounding for this price
    $found=false;$rounding=0;$offset=0;
    foreach($this as $key => $value) {
      if( $key > $price ) break;
      $found=$key;
    }
    if($found!==false) {
      $rounding=$roundings[$found]['rounding'];
      $offset=$roundings[$found]['offset'];
    }

    if($rounding < 1/100) $rounding=1/100;
    return ceil($price / $rounding) * $rounding + $offset;
  }

}

/*
  Model based on array
  [1] Model will only show 1 record, the active record
  [2] with controller_data_array it will allow to save and load the record
  [3] $this->data containts active record. $this->table contains all records.
*/