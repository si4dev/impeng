<?php
class Model_Assortment extends Model_Table {
  public $table='assortment';
  public $title_field='name';
  function init() {
    parent::init();
    // TODO: name should be label and friendly_name should be name
    $this->addField('label');
    $this->addField('name');
    $this->addField('branch');
    $this->hasOne('User',null,'name');
    $this->addField('is_supplier');
    $this->addField('is_shop');
    $this->addField('import_full');
    $this->addField('import_start');
    $this->addField('import_end');
    $this->addField('schedule')->enum(array('disable','daily','manual','test'));
    $this->addField('config')->type('text');
    $this->hasMany('Category');
    $this->hasMany('Attribute');
    $this->hasMany('AttributeGroup');
    $this->hasMany('Product');
  }




}