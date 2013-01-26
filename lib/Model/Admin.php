<?php
class Model_Admin extends Model_User {
  function init(){
    parent::init();
    $this->addField('is_admin')->type('boolean');
    $this->setMasterField('is_admin',true);
  }
}