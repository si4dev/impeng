<?php

class page_demo extends Page {
  function init() {
    parent::init();

    $c=$this->add('CRUD');
    $c->setModel('Model_Shop');
    if ($c->grid){ 
      
        $c->grid->addQuickSearch(array('name'));
        $c->grid->addPaginator(10);
    }
  }
    
}