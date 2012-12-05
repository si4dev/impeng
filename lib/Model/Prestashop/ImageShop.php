<?php
class Model_Prestashop_ImageShop extends Model_Table2 {
  public $table='ps_image_shop';
  public $id_field='id_image';
  function init() {
    parent::init();
    $this->addField('id_shop');
    $this->addField('cover')->defaultValue(1);
  }
}
    