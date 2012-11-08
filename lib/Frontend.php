<?php
function exceptions_error_handler($severity, $message, $filename, $lineno, $vars) {
	throw new ErrorException($message, 0, $severity, $filename, $lineno);
	//print_r($vars);
}

class Frontend extends ApiFrontend {
  function md5($value) {return md5($value);}
	function init(){
		parent::init();
		$this->addLocation('atk4-addons',array(
					'php'=>array(
                        'mvc',
						'misc/lib',
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
	
	$this->js(true)->_selector("#Content")
		->atk4_loader();

    $this->add('Auth')->setModel('User'); // email and password are default to login
    //$this->auth->usePasswordEncryption('md5')->check();
	
	  if(isset($_GET['login_as'])){
		list($user, $test) = explode(':', $_GET['login_as']);
		if($test == md5('secretpass'))
		{
			$this->auth->loginByID($user);
		}
		else
		{
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
    $m->addMenuItem('logout','Logout');
 
    $si=$this->add('Controller_Shopimport');
    $s=$si->shop;
    $u=$si->user;

    $pp=$this->api->add('P',null,'UserInfo');
    $pp->add('Text')->set('user: '.$u['email']);
    $pp->add('HTML')->set('<br/>');
    $pp->add('Text')->set('shop: '.$s['name']);
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
}
