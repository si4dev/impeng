<?php
class Model_Prestashop_Product extends Model_Table2 {
  public $table='ps_product';
  public $id_field='id_product';
  public $title_field='reference';
  function init() {
    parent::init();
    $this->addField('reference');
    $this->addField('supplier_reference');
    $this->addField('quantity');
    $this->addField('price');
    $this->addField('ean13');
    $this->addField('weight');
    $this->addField('location');
    $this->addField('id_category_default');
    $this->addField('id_color_default');
    $this->addField('active');
    $this->hasMany('Prestashop_CategoryProduct','id_product');
    $this->hasMany('Prestashop_ProductLang','id_product');
    $this->hasMany('Prestashop_Image','id_product');
    $this->hasOne('Prestashop_Manufacturer','id_manufacturer');
    $this->hasOne('Prestashop_Tax','id_tax_rules_group');
  }
  
}
    