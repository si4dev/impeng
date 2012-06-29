<?php
class page_user extends Page {
  function init() {
    parent::init();
    
    $tabs=$this->add('Tabs');
    $crud=$tabs->addTab('Users')->add('CRUD');
    $crud->setModel('User');
    if($crud->grid){
      $crud->grid->addColumn('prompt','set_password');
      $crud->grid->getColumn('login')->makeSortable();
            
      if($_GET['set_password']){
          $auth = $this->api->auth;
          $model = $auth->getModel()->loadData($_GET['set_password']);
          $enc_p = $auth->encryptPassword($_GET['value'],$model->get('email'));
          $model->set('password',$enc_p)->update();
          $this->js()->univ()->successMessage('Changed password for '.$model->get('email'))->execute();
      }
    }
  }
}
