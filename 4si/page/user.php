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
          $u=$this->add('Model_User')
              ->load($_GET['set_password']);
          $enc_p = $this->api->auth->encryptPassword($_GET['value'],$u->get('email'));
          $u->set('password',$enc_p)
              ->save();
          $this->js()->univ()->successMessage('Changed password for '.$u->get('login'))->execute();
      }
    }
  }
}
