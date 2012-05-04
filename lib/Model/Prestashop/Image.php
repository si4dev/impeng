<?php
class Model_Prestashop_Image extends Model_Table2 {
  public $table='ps_image';
  public $id_field='id_image';
  function init() {
    parent::init();
    $this->hasOne('Prestashop_Product','id_product');
    $this->addField('position');
    $this->addField('cover');
    $this->addExpression('filebase')->set("concat(id_product,'-',id_image)");
  }
}
    