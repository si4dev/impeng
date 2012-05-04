<?php
class Model_Prestashop_Lang extends Model_Table2 {
  public $table='ps_lang';
  public $id_field='id_lang';
  function init() {
    parent::init();
    $this->debug();
    $this->addField('name');
    $this->addField('iso_code');
    $this->addField('active');
    $this->addCondition('active',1);
  }
}
    