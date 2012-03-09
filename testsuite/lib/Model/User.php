<?php
class Model_User extends Model_Table {
  public $table='user';
  public $title_field = 'login';
  function init() {
    parent::init();
    
    $this->addField('login');
    $this->addField('email');
  }
}
