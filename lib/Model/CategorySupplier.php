<?php
class Model_CategorySupplier extends Model_Category {
  function init() {
    parent::init();
        
    $supcat=$this->join('tbltype_suppliercategory.SupplierCategoryID','CategorySupplierID');
    $supcat->addField('SupplierCategoryTitle');
  }
}