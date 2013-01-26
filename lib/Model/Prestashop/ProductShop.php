<?php
class Model_Prestashop_ProductShop extends Model_Table2 {
  public $table='ps_product_shop';
  public $id_field='id_product';
  function init() {
    parent::init();
    $this->addField('id_shop');
    $this->addField('id_category_default');
    $this->hasOne('Prestashop_Tax','id_tax_rules_group');
    $this->addField('price');
    $this->addField('active');
    $this->addField('date_add');
    $this->addField('date_upd');
    $this->addCondition('id_shop',1);
  }
}
    