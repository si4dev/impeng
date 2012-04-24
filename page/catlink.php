<?php
class page_catlink extends Page {
  function init() {
    parent::init();
    
    
    $c=$this->add('CRUD');
    $c->setModel('CatLink');
    if($c->grid) {
      $c->grid->addPaginator(20);
    }
  }
}
