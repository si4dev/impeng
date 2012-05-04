<?php
class Model_CatLink extends Model_Table {
  public $table='catlink';
  function init() {
    parent::init();
    $this->hasOne('Category');
    $this->hasOne('Shop');
    $this->addField('import');
    $this->addField('margin_ratio');
    $this->addField('margin_amount');
  }
}