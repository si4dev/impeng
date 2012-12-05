<?php
class Admin extends ApiFrontend {
  public $is_admin=true;
  function init() {
    ini_set('display_errors',1);
    
    $this->addLocation('..',array(
                    'php'=>array(
                        'lib',
                        'atk4-addons/mvc',
                        'atk4-addons/billing/lib',
                        'atk4-addons/misc/lib',	
                        )
						,
					'addons' => 'atk4-addons'
                    ))
            ->setParent($this->pathfinder->base_location);

    $this->dbConnect();
    $this->add('Dbug');
    
    parent::init();
	
    $this->add('jUI');

    $this->js()
            ->_load('atk4_univ')
            ->_load('ui.atk4_notify');

    $this->add('Auth')->setModel('Admin'); // email and password are default to login
    $this->auth->usePasswordEncryption(function($v) { return md5($v); } );
    $this->auth->check();

    
    $m = $this->add('Menu', null, 'Menu');
    $m->addMenuItem('user','Users');      
    $m->addMenuItem('shop','Shops');      
    $m->addMenuItem('supplier','Suppliers');
  	$m->addMenuItem('fileadmin', 'Manage Uploads');
    $m->addMenuItem('dbug','Debug');
    $m->addMenuItem('logout','Log out'); 

  }
}
