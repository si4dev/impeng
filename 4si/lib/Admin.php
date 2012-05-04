<?php
class Admin extends ApiFrontend {
  public $is_admin=true;
  function init() {
    parent::init();
    
    
    
    $this->dbConnect();

    $this->addLocation('..',array(
                    'php'=>array(
                        'lib',
                        'atk4-addons/mvc',
                        'atk4-addons/billing/lib',
                        'atk4-addons/misc/lib',
                        )
                    ))
            ->setParent($this->pathfinder->base_location);

    $this->add('jUI');

    $this->js()
            ->_load('atk4_univ')
            ->_load('ui.atk4_notify');

//    $this->add('Auth')->setModel('User');
//    $this->auth->check();
    
    $m = $this->add('Menu', null, 'Menu');
    $m->addMenuItem('Users','users');      
    $m->addMenuItem('Log out','logout');      
  }
  
  function page_index($p){
      $p->add('HelloWorld');
  }
}