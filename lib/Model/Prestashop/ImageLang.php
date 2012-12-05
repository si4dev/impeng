<?php
class Model_Prestashop_ImageLang extends Model_Table2 {
  public $table='ps_image_lang';
  public $id_field='id_image';
  function init() {
    parent::init();
    $this->addField('id_lang');
  }
}
    