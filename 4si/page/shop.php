<?php
class page_shop extends Page {
  function init() {
    parent::init();
    
    $c=$this->add('CRUD');
    $c->setModel('Shop');
    if($c->grid) {
      $c->grid->addColumn('button','config');
      $c->grid->getColumn('name')->makeSortable();
      if($_GET['config']){
        // learn how to redirect to other page. http://agiletoolkit.org/doc/grid/interaction 
        // replace dialogURL() with location() and drop first argument. 
        // also for non ajax add api redirect http://agiletoolkit.org/doc/form/submit
        $p=$this->api->getDestinationURL(
              'shopconfig',array(
              'shop'=> $_GET['config']
              ));
        $c->js()->univ()->location($p)->execute();
        $this->api->redirect($p);
      }
    }
    if($f=$c->form) {
      //$f->addField('dropdown','config')->setValueList(array('prestashop','opencart','xcart','magento','oscommerce'));
      //$f->addField('text','nice');
      //if($f->isSubmitted()) {
        //$c->model->save();
        //$f->js()->univ()->alert(print_r($f->get(),true))->execute();
//        $f->js()->univ()->successMessage('Got it')->execute();
      //}
    }
  }
}
