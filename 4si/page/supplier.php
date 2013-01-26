<?php
class page_supplier extends Page {
  function init() {
    parent::init();
    
   
	
	
    $c=$this->add('CRUD');
    $c->setModel('Supplier',array('name','friendly_name','branch','config'),array('name','friendly_name','branch','import_start','import_full','import_end'));
	
    if($c->grid) {
      $c->grid->addColumn('button','import');
	  $c->grid->addColumn('expander', 'upload');
      $c->grid->getColumn('name')->makeSortable();
      if($_GET['import']){
        // learn how to redirect to other page. http://agiletoolkit.org/doc/grid/interaction 
        // replace dialogURL() with location() and drop first argument. 
        // also for non ajax add api redirect http://agiletoolkit.org/doc/form/submit
        $p=$this->api->getDestinationURL(
              'supplierimport',array(
              'supplier'=> $_GET['import']
              ));
        $c->js()->univ()->location($p)->execute();
        $this->api->redirect($p);
      }
    }
	
    if($c->form) {
      $cfg=$c->form->getElement('config');
      $cfg->set(str_replace(array('&'),array('&amp;'),$cfg->setAttr('rows',100)->get()));
      
    }
	
	

    }
}
