<?php
class Model_Demo extends Model_Table {
  public $table='demo';
  function init() {
    parent::init();
    $this->addField('sometext');
    $this->addField('age');
    //$this->addField('title');
  }
}

class page_demo extends Page {
  function init() {
    parent::init();


$user=$this->add('Model_Demo')->debug();
$user->addCondition('age','>','20');
$user->set('age',11);
$user->saveAndUnload();           // exception!
echo '++'.$user->id;

/*
    $c=$this->add('CRUD');
    $c->setModel('Model_Shop');
    if ($c->grid){ 
      
        $c->grid->addQuickSearch(array('name'));
        $c->grid->addPaginator(10);
    }
*/
  
  }
    
}