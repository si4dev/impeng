<?php
class Model_User extends Model_Table {
  public $table='user';
  function init() {
    parent::init();
    
    $this->addField('login');
    $this->addField('name');
    $this->addField('email');
    $this->addField('password')->system(true);
    $this->hasMany('Shop');
  }
}
