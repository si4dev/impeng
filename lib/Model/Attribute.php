<?php
class Model_Attribute extends Model_Table {
  public $table='attribute';
  public $title_field='value';
  function init() {
    parent::init();
    $this->hasOne('AttributeGroup');
    $this->addField('value');
    $this->addExpression('supplier_id')->set(function($m,$q) {
      return $m->refSQL('attributegroup_id')->dsql()->field('supplier_id')->limit(1);
    });
  }

}

