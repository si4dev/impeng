<?php
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


		$m=$this->add('Menu',null,'Menu');
		$m->addMenuItem('Back','index');
        $this->dbConnect();
	}
    function page_index($page){
        /*
        $page->add('Link')->set('core','AbstractObject');
        $page->add('Link')->set('db','PDO compatible Database Tests');
        $page->add('Link')->set('dbwhere','Where clause testing');
        $page->add('Link')->set('dbparam','Parametric arguments and subqueries');

        $this->add('Grid')->setModel('Examples');
         */
        $m=$this->add('Model_Examples');
        $l=$this->add('CompleteLister');
        $l->setModel('Examples',array('name'));
        $l->addTotals();
        $l->addHook('formatRow',function($l){
            $n=$l->current_row['name'];
            $n=str_replace('.php','',$n);
            $n='<a href="'.$l->api->url($n).'">'.$n.'</a>';

            $l->current_row['name']=$n;
        });
    }
}
