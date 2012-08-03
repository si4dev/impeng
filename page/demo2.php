<?php

class Model_Demo2 extends Model_Table {
  public $table='demo';
  function init() {
    parent::init();
    $this->addField('sometext');
    $demo2=$this->join('demo2','demo2_id');
    $demo2->addField('sometext2');
  }
}

class page_demo2 extends Page {
    public $proper_responses=array(
        "Test_simple"=>array (
  0 => '1',
  1 => 
  array (
  ),
)
    );
    
    function init() {
      
        parent::init();
        $this->db = $this->api->db;
        //$this->tableInit();
        $this->Test_simple();
    }
    
    
    
    function tableInit(){
        if($this->db->type=='mysql'){
            $this->db->query('drop temporary table if exists demo');
            $this->db->query("create temporary table `demo` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `demo2_id` int(11) unsigned NOT NULL,
  `sometext` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)");
            $this->db->query('drop temporary table if exists demo2');
            $this->db->query("create temporary table `demo2` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sometext2` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
)");
        }elseif($this->db->type=='sqlite'){

        }
        
    }
    function test_simple(){
        // this part is ok
        $demo=$this->add('Model_Demo2');
        $demo->tryLoadAny();
        $demo->set('sometext','yes');
        $demo->set('sometext2','yes');
        $demo->save();

        // however when iterating it will not work
        $demo2=$this->add('Model_Demo2');
        $demo2->selectQuery();
        foreach($demo2 as $d) {
          $demo2->set('sometext','yes');
          $demo2->set('sometext2','yes');
          $demo2->save();
        }
        
        return $demo2->id;
    }
}