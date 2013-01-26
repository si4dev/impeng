<?php
class Model_Prestashop_CategoryLang extends Model_Table2 {
  public $table='ps_category_lang';
  public $id_field='id_category';
  function init() {
    parent::init();
    $this->hasOne('Prestashop_Lang','id_lang');
    $this->addField('name');
    $this->addField('description');
    $this->addField('link_rewrite');
  }
}
    