<?php
class Model_Xcart_QuickFlags extends Model_Table2 {
  public $table='xcart_quick_flags';
  public $id_field='productid';
  public $title_field='productid';
  function init() {
    parent::init();
    $this->addField('is_variants')->defaultValue('');
    $this->addField('is_product_options')->defaultValue('');
    $this->addField('is_taxes')->defaultValue('');
    $this->addField('image_path_T')->defaultValue('');

  }
}