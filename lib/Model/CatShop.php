<?php

HOLD

class Model_CatShop extends Model_Table {
  public $table='catshop';
  public $title_field='title';
  function init() {
    parent::init();
    $this->hasOne('Shop');
    $this->addField('ref');
    $this->addField('title');

  }
  
}