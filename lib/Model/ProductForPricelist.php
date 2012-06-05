<?php
class Model_ProductForPricelist extends Model_Product {
  function init() {
    parent::init();
    $this->getField('info')->destroy('');
    $watch=$this->join('Watch.product_id');
    $watch->addField('price');
    $watch->addField('stock');
    $watch->addField('watch_last_checked','last_checked');
    $category=$this->join('Category');
    $category->addField('cattitle','title');
    $catlink=$this->join('CatLink.category_id','category_id');
    $catlink->addField('catshop_id');
    $supplierlink=$this->join('supplierlink.supplier_id','supplier_id');
//    $supplierlink->addField('shop_id');
    $supplierlink->hasOne('Shop');
    $supplierlink->addField('prefix');
    
 //   $catlink->addField('margin_ratio');
  //  $catlink->addField('margin_amount');
    $this->dsql->limit(10);


  }
}
