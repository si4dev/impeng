<?php

class Model_Xcart_Category extends Model_Table2 {
  public $table='xcart_categories';
  public $id_field='categoryid';
  public $title_field='category';
  function init() {
    parent::init();
    $this->addField('category');
    $this->addField('parentid');


    $res=$this->api->db2->query("show columns from xcart_categories like 'lpos'")->fetch();
    $this->tree=$res[0]=='lpos'?true:false;
    if($this->tree) {
      $this->addField('lpos');
      $this->addField('rpos');
    } else {
      $this->addField('categoryid_path');
    }
    $this->addField('order_by')->type('int');
  //  $this->hasOne('Xcart_Category','parentid');
   $this->hasMany('Xcart_Category','parentid');
   
   
  }
  
  

  function treeRebuild($left = 0) {
    if(!$this->tree) { // no treebuild needed for old xcart versions
      return $this;
    }
    $right = $left + 1;
    if($left==0) {
      // main call has no initial load() so we need to setup all parent=0 records
      $childs=$this->add('Model_Xcart_Category');
      $childs->addCondition('parentid','0');
    } else {
      // recursive call allows to use ref() for subcategories
      $childs=$this->ref('Xcart_Category');
    }
    $childs->setOrder('order_by')->setOrder('category');
    foreach($childs as $child) {
      $right=$childs->treeRebuild($right);
    }
    if($left!=0) {
      // processes when returning from recursive call
      $this->set('lpos',$left)->set('rpos',$right)->save();
      return $right+1;
    } else {
      // processes when returning from main call
      return $this;
    }
  }

}