<?php
class Model_Media_Requested extends Model_Media {
  function init() {
    parent::init();
    
    $this->addExpression('supplier_id')->set(function($m,$q) {
      return $m->refSQL('product_id')->dsql()->field('supplier_id');
    });
    $this->addExpression('productcode')->set(function($m,$q) {
      return $m->refSQL('product_id')->dsql()->field('productcode');
    });
    
  }
}
