<?php
class page_media extends Page {
  function init() {
    parent::init();
    
    $c=$this->add('CRUD');
    $c->setModel('Media')->debug();
    if ($c->grid){ 
      
        $c->grid->addPaginator(10);
    }
  }
}
