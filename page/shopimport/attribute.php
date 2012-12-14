<?php
class Page_Shopimport_Attribute extends Page {
  function init() {
    parent::init();
    
    
    $s=$this->api->getShop();
	  $ag=$s->prepareAttributeGroupLink();
    
    // sag=shop attribute group 
    $sag=$s->getShopAttributes();
    
    foreach($sag as $row) {
      $list[]=$row['name'];
    }
    
    
    // need array in model but how? setValueList is same as listData
    $ag->getElement('shopattr_ref')->listData($list);
    $this->add('CRUD')->setModel($ag);
    
     
    
  }
}