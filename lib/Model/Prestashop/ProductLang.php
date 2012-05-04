<?php
class Model_Prestashop_ProductLang extends Model_Table2 {
  public $table='ps_product_lang';
  public $id_field='id_product';
  function init() {
    parent::init();
    $this->hasOne('Prestashop_Lang','id_lang');
    $this->addField('description');
    $this->addField('description_short');
    $this->addField('link_rewrite');
    $this->addField('meta_description');
    $this->addField('meta_keywords');
    $this->addField('meta_title');
    $this->addField('name');
  }
}
    