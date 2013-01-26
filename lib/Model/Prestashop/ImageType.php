<?php
class Model_Prestashop_ImageType extends Model_Table2 {
  public $table='ps_image_type';
  public $id_field='id_image_type';
  function init() {
    parent::init();
    $this->debug();
    $this->addField('name');
    $this->addField('width');
    $this->addField('height');
    $this->addField('products');
    $this->addField('categories');
    $this->addField('manufacturers');
  }
}
    