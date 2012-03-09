<?php

class page_dbwhere extends Page_Tester {
    public $db;
    function init(){
        $this->db=$this->add('DB');
        parent::init();
    }
    function runTests(){
        $this->grid->addColumn('text','Test_para');
        return parent::runTests();
    }
    function formatResult(&$row,$key,$result){
        //parent::formatResult($row,$key,$result);
        $x=parent::formatResult($row,$key,array($result,$this->input[0]->params));
        return $x;
    }
    function prepare(){
        return array($this->db->dsql()->table('foo'));
    }
    function test_where_or1($t){
        return $t->where(array('i is null','o is null'));
    }
    function test_where_or2($t){
        return $t->where(array($t->expr('i is null'),$t->expr('o is null')));
    }
    function test_where_or3($t){
        return $t->where(array(array('x',$t->dsql()->table('foo')),$t->expr('o is null')));
    }
}

