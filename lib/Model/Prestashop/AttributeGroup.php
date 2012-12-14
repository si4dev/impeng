<?php
class Model_Prestashop_AttributeGroup extends Model_Table2 {
  public $table='ps_attribute_group';
  public $id_field='id_attribute_group';
  function init() {
    parent::init();
  }
  
  
  
  function joinName() { 
    $catlang=$this->join('ps_attribute_group_lang.id_attribute_group','id_attribute_group');
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

}