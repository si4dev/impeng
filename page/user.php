<?php
class page_user extends Page {
  function init() {
    parent::init();
    $c=$this->add('CRUD');
    $c->setModel('Model_User');
    if ($c->grid){ 
        $c->grid->addPaginator(5); 
    }  
  }
}

