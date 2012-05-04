<?php
class page_users extends Page {
  function init() {
    parent::init();
    $c=$this->add('CRUD');
    $c->setModel('Model_User');
    if ($c->grid){ 
      $c->grid->addPaginator(5); 
      $c->grid->getColumn('login')->makeSortable();
    }  
    
    $tabs=$this->add('Tabs');
       $crud=$tabs->addTab('Users')->add('CRUD');
        $m=$crud->setModel('User');
$m->addField('text','text');
        if($crud->grid){
            $crud->grid->addColumn('prompt','set_password');

  $crud->grid->getColumn('login')->makeSortable();
            
            if($_GET['set_password']){
                $auth = $this->add('RentalAuth');
                $model = $auth->getModel()->loadData($_GET['set_password']);
                $enc_p = $auth->encryptPassword($_GET['value'],$model->get('email'));
                $model->set('password',$enc_p)->update();
                $this->js()->univ()->successMessage('Changed password for '.$model->get('email'))
                    ->execute();
            }
        }
    
  }
}
