<?php
function exceptions_error_handler($severity, $message, $filename, $lineno, $vars) {
	throw new ErrorException($message, 0, $severity, $filename, $lineno);
	//print_r($vars);
}

class Frontend extends ApiFrontend {
  protected $user;
  protected $shop;
  function md5($value) {return md5($value);}
	function init(){
		parent::init();
		$this->addLocation('atk4-addons',array(
					'php'=>array(
                        'mvc',
						'misc/lib',
						'filestore/lib',
						)
					))
			->setParent($this->pathfinder->base_location);
		$this->add('jUI');
		$this->js()
			->_load('atk4_univ')
			// ->_load('ui.atk4_expander')
			;

    $this->dbConnect();
    // $this->add('Dbug');
	
	

    $this->add('Auth')->setModel('User'); // email and password are default to login
    //$this->auth->usePasswordEncryption('md5')->check();
	
	  if(isset($_GET['login_as'])){
	  //first, logout the current user
		if($this->auth->isLoggedIn()){
			$this->auth->logout();			
		}
		$this->api->redirect($this->api->url(),array('admin_as'=> $_GET['login_as'])) ;
	  }
	
	 if(isset($_GET['admin_as'])){
	  //now , login as the user
		list($user, $token) = explode(':', $_GET['admin_as']);
		
		if($token == md5($this->api->getConfig('token').date('d:M:Y'))){
			$this->auth->loginByID($user);
			$this->auth->memorize('admin_as_user', 'admin');
		}
		else{
			throw new exception("Attempt to hack");
		}
	 }
	
    if($key=$this->api->getConfig('key',null) and $_GET['key']===$key) {
      // admin or cron
    } else {
      $this->auth->usePasswordEncryption(function($v) { return md5($v); } )->check();
    }
    $m=$this->add('Menu',null,'Menu');
    $m->addMenuItem('shopimport/margin','Marge');
    $m->addMenuItem('shopimport/filter','Filter');
    $m->addMenuItem('shopimport/import','Import');
	$m->addMenuItem('shopimport/profile', 'profile');
	$m->addMenuItem('supplier', 'Supplier');
    $m->addMenuItem('logout','Logout');
 

 
    $this->user=$this->api->auth->model;
    if($shop_id=$this->api->recall('shop_id')) {
      $this->shop=$this->user->ref('Shop')->load($shop_id);
    } else {
      $this->shop=$this->user->ref('Shop')->tryLoadAny();
      $this->api->memorize('shop_id',$this->shop->id);
    }
	
	if($this->auth->recall('admin_as_user') == 'admin'){
		$pp=$this->api->add('P',null,'UserInfo');
		$pp->add('Text')->set('Admin as: '.$this->user['email']);
	}
	else {

    $pp=$this->api->add('P',null,'UserInfo');
    $pp->add('Text')->set('user: '.$this->user['email']);
	}
    $pp->add('HTML')->set('<br/>');
    $pp->add('Text')->set('shop: '.$this->shop['name']);

    // button change shop when count($u->ref('Shop')) > 1
    
    /*
		$nav=explode('_',$this->api->page);
		switch($nav[0]){
			case'shopimport':
        $m2=$this->add('Menu',null,'SubMenu');
        $m2->addMenuItem('shopimport/test','Test');
        $m2->addMenuItem('shopimport/margin','Marge');
        $m2->addMenuItem('shopimport/filter','Filter');
        $m2->addMenuItem('shopimport/import','Import');
    }
    */

    //$this->add('performance/Controller_Profiler');
	}
  
  function getUser() {
    return $this->user;
  }
  
  function getShop() {
    return $this->shop;
  }
  
}
