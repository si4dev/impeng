<?php
class Model_Prestashop_Category extends Model_Table2 {
  public $table='ps_category';
  public $id_field='id_category';
  public $title_field='name';
  function init() {
    parent::init();
    
    $this->addField('id_parent');
    $this->addField('active')->defaultValue(1);
    $this->hasMany('Prestashop_CategoryLang','id_category');
    $this->hasMany('Prestashop_CategoryGroup','id_category');
    $this->hasMany('Prestashop_Category','id_parent');
    $this->addField('date_add');
    $this->addField('date_upd');
    
    if(!isset($this->version)) {
      $table='ps_category_shop';
      if($this->_dsql()->owner->getOne("show tables like '{$table}'")) {
        $this->version='1.5';
      } else {
        $this->version='1.3';
      }
    }
        
  }
  
  
  // for prestashop 1.5 we need to join ps_category_shop
  function joinCategoryShop() { 
    if($this->version=='1.5') {
      $catshop=$this->join('ps_category_shop.id_category','id_category');
      $catshop->addField('id_shop');
      $this->addCondition('id_shop',1);
    }
    return $this;
  }
  
    
  function joinName() { // used by importCategory() to get also the name of the category
    $catlang=$this->join('ps_category_lang.id_category','id_category');
    $catlang->addField('name');
    $catlang->addField('id_lang');
    return $this;
  }
  
  function lang($lang) {
    if(!isset($this->lang)) {
      $this->lang=$lang;
      $this->joinName();
      $this->addCondition('id_lang',$this->lang);
    }
    return $this;
  }

  // before calling also set ->lang variable to the id of the default language
  function tree($prefix='') {
    $r=array();
    $childs=$this->ref('Prestashop_Category')->lang($this->lang);
    
    foreach($childs as $child) {
      $r[$childs->id]=$prefix.$childs['name'];
      foreach($childs->tree($prefix.$childs['name'].'|') as $key=>$value) {
        $r[$key]=$value;
      }
    }
    return $r;
  }

  

  function getRoot() {
    if($this->version=='1.5') $home=2;
    $home=1;
    return $home;
  }

  
}
    