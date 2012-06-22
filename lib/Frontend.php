<?php
function exceptions_error_handler($severity, $message, $filename, $lineno, $vars) {
	throw new ErrorException($message, 0, $severity, $filename, $lineno);
	//print_r($vars);
}

/*
   Commonly you would want to re-define ApiFrontend for your own application.
 */
class Link extends HtmlElement {
    function init(){
        parent::init();
        $this->setElement('a');
    }
    function set($name,$descr){
        $this->setAttr('href',$this->api->getDestinationURL($name));
        return parent::set($descr);
    }
}

class Model_Examples extends Model {
    public $dir;
    function init(){
        parent::init();

        $this->addField('name');

        $p=$this->api->pathfinder->searchDir('page');
        $this->setSource('ArrayAssoc',$p);
        return $this;
    }

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
    $this->add('Dbug');

    $this->add('Auth')->setModel('User'); // email and password are default to login
    //$this->auth->usePasswordEncryption('md5')->check();
    $this->auth->usePasswordEncryption(function($v) { return md5($v); } )->check();
    $m=$this->add('Menu',null,'Menu');
		$m->addMenuItem('shopimport/test','Shopimport');
    $m->addMenuItem('logout','Logout');

		$nav=explode('_',$this->api->page);
		switch($nav[0]){
			case'shopimport':
        $m2=$this->add('Menu',null,'SubMenu');
        $m2->addMenuItem('shopimport/test','Test');
        $m2->addMenuItem('shopimport/margin','Marge');
    }

    //$this->add('performance/Controller_Profiler');
	}
}
