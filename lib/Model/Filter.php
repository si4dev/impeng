<?php
class Model_Filter extends Model_Table {
  public $table='filter';
  function init() {
    parent::init();
    $this->hasOne('Shop');
    $this->hasOne('Category');
    $this->addField('keyword');
    $this->addField('margin_ratio');// ->defaultValue(1);
    $this->addField('margin_amount'); //->defaultValue(0);
    $this->hasOne('CatShop');
    $this->addField('catshop_id');  
    $this->addField('import');
    $this->addField('active');
  }
  
  function getSupplier() {
    $this->addExpression('supplier')->set(
    function($m, $q){		
      $supl_id = $m->refSQL('category_id')->dsql()->field('supplier_id');
      return $m->ref('category_id')->ref('supplier_id')->dsql()->field('name')->where('id', $supl_id);
    });
    return $this;
  }
  
  /* function getCatShop(){
	return $this->addExpression('catshop')->set(
	 function ($m, $q){
		
		 return $q->dsql()->table('catshop')->field('ref')->where('id', $m->getElement('catshop_id');
			
			; //result not unique without a key id or like.
	 }
	 );
   }  */
}
