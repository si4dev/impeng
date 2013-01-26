<?php
class Model_Xcart_ProductImport extends Model_Xcart_Product {
  
  function init() {
    parent::init();
    
    $pricing=$this->join('xcart_pricing.productid');
    $pricing->addField('quantity');
    $pricing->addField('price');
    
    
    
    
    
  
  }
}
