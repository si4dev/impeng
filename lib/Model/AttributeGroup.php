<?php
class Model_AttributeGroup extends Model_Table {
  public $table='attributegroup';
  public $title_field='name';
  function init() {
    parent::init();
    $this->addField('name');
    $this->addField('supplier_id');
  }

}

