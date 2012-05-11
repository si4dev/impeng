<?php
class Model_Prestashop_Category extends Model_Table2 {
  public $table='ps_category';
  public $id_field='id_category';
  function init() {
    parent::init();
    $this->hasOne('Prestashop_Lang','id_lang');
    $this->addField('name');
    $this->addField('description');
    $this->addField('link_rewrite');
  }
}
    