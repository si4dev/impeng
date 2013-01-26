<?php
class Model_Prestashop_CategoryGroup extends Model_Table2 {
  public $table='ps_category_group';
  public $id_field='id_category';
  function init() {
    parent::init();
    $this->addField('id_group');
  }
}