<?php
class Page_Shopimport_Test extends Page {
  function init() {
    parent::init();
    
    $this->add('P')->set('test');
  }
}