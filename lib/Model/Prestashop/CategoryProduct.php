<?php
class Model_Prestashop_CategoryProduct extends Model_Table2 {
  public $table='ps_category_product';
  public $id_field='id_category';
  public $title_field='id_category';
  function init() {
    parent::init();
    $this->addField('id_product');
    $this->addField('position')->defaultValue(0);
  }
  
}
    