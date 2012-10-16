<?php
class Model_ProductForPricelist extends Model_Product {
  public $table_alias='p';

  function init() {
    parent::init();
    $this->getField('info')->destroy('');
    $watch=$this->join('Watch.product_id');
    $watch->addField('price');
    $watch->addField('stock');
    $watch->addField('watch_last_checked','last_checked');
    
    
    $category=$this->join('Category');
    $category->addField('cattitle','title');
    
    
    $supplierlink=$this->join('supplierlink.supplier_id','supplier_id');
    $supplierlink->hasOne('Shop');
    $supplier=$supplierlink->join('supplier');
    $supplierlink->addField('prefix');
    
    // condition to only take supplier prices with latest 
    $this->_dsql()->where($watch->fieldExpr('last_checked'),'>=',$supplier->fieldExpr('import_full'));
    // only correct one pricebook
    $this->_dsql()->where($watch->fieldExpr('pricebook_id'),'=',$supplierlink->fieldExpr('pricebook_id'));
    


  
    // now join the filtering so per product we know which filter rule is affected as only one filter rule can be affected
    // working great, but not handy formatted:
    $q=$this->api->db->dsql();
    $q->table('product','p2')
        ->join('filter',$q->expr("p2.category_id=f.category_id and if(f.keyword!='',p2.title like concat('%',f.keyword,'%'),true)"),'inner','f')
        ->field('f.id')
        ->where('f.shop_id=',$supplierlink->fieldExpr('shop_id'))
        ->where('p2.id=',$q->expr('p.id'))
        ->order('rank','desc')
        ->limit(1) // this is THE reason to put it in the sub select with join conditions
        ;
        
    $filter=$this->join('filter',$this->dsql()->expr('ff.id=('.$q.')')->setCustom('id',$this->getElement('id') ),'left','ff');
    
    /*
      above query is based on this select query:

      select f.id,f.keyword, p.*
      from product p inner join filter f 
      on
      f.id=
      (
        select f.id 
        from product p2 
        inner join filter f on p2.category_id=f.category_id and if(f.keyword,p2.title like concat('%',f.keyword,'%'),true) 
        where f.shop_id=2 and p2.id=p.id
        order by rank desc limit 1
      )

      */
    
    
    $filter->addField('catshop_id');
    $filter->addField('keyword');
    $filter->addField('filter_id','id');
    $filter->addField('margin_ratio');
    $filter->addField('margin_amount');

  }
  
  // the base query model will show all products and with group() it will group by filter
  function group() {
    $this->addExpression('cnt',$this->dsql()->expr('count(1)'));
    $this->_dsql()->group('category_id')->group('ff.id');
    return $this;
  }
    
}
