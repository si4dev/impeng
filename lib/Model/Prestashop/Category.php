<?php
class Model_Prestashop_Category extends Model_Table2 {
  public $table='ps_category';
  public $id_field='id_category';
  public $title_field='id_category';
  function init() {
    parent::init();
    $this->debug();
    $this->addField('id_parent');
    $this->addField('active');
    $this->hasMany('Prestashop_CategoryLang','id_category');
    $this->hasMany('Prestashop_Category','id_parent');
      
    $catlang=$this->join('ps_category_lang.id_category','id_category');
    $catlang->addField('name');
    $catlang->addField('id_lang');
    $this->addCondition('id_lang',6);

  }
  
    

  function tree($prefix='') {
    $r=array();
    $childs=$this->ref('Prestashop_Category');
    
    foreach($childs as $child) {
      $r[$childs->id]=$prefix.$childs['name'];
      foreach($childs->tree($prefix.'---') as $key=>$value) {
        $r[$key]=$value;
      }
    }
    return $r;
  }

  
}
    