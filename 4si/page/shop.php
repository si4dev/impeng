<?php
class page_shop extends Page {
  function init() {
    parent::init();
    
    $crud=$this->add('CRUD');
    $crud->setModel('Shop',null,array('name','user'));
    if($crud->grid) {
      $crud->grid->getColumn('name')->makeSortable();
    }
    if($f=$crud->form) {
      //$f->addField('dropdown','config')->setValueList(array('prestashop','opencart','xcart','magento','oscommerce'));
      $f->addField('text','nice');
      if($f->isSubmitted()) {
        $crud->model->set('config','') // )
            ->save();
        
        $f->js()->univ()->alert(print_r($f->get(),true))->execute();
        
//        $f->js()->univ()->successMessage('Got it')->execute();
      }
    }
  }
}
