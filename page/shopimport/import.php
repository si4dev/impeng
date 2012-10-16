<?php
class Page_Shopimport_Import extends Page {
  function init() {
    parent::init();
    
    $b=$this->add('Button');
    $b->setLabel('Pricelist');
    if($b->isClicked()) {
 //     echo 'test';
      $this->js()->univ()->location($this->api->getDestinationURL(
                        'pricelist',array('token'=>false)))->execute();
    }
  }
}