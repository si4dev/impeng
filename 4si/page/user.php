<?php
class page_user extends Page {
  function init() {
    parent::init();
	
		
	$tabs=$this->add('Tabs');
    $crud=$tabs->addTab('Users')->add('CRUD');
	$m = $this->add('Model_User');
  
    $crud->setModel($m);
    if($crud->grid){
      $crud->grid->addColumn('prompt','set_password');
      $crud->grid->getColumn('login')->makeSortable();
	  $b = $crud->grid->addColumn('button', 'login_as' ,'login_as');
	   
      $admin = $this->api->auth->model; //get the admin model
	  
      if($_GET['set_password']){
          $u=$this->add('Model_User')
              ->load($_GET['set_password']);
          $enc_p = $this->api->auth->encryptPassword($_GET['value'],$u->get('email'));
          $u->set('password',$enc_p)
              ->save();
          $this->js()->univ()->successMessage('Changed password for '.$u->get('login'))->execute();
      }
	  
	   if(isset($_GET['login_as'])){
			$token = md5($this->api->getConfig('token').date('d:M:Y'));
			$user = $_GET['login_as'].':'.$token;
			$this->js(null, $this->js()->univ()->redirect('../', array('login_as'=> $user)))->execute();				 
		}
	 }
    	
  }
}
