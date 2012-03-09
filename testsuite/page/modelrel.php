<?php

class page_modelrel extends Page_Tester {
    public $db;
    function init(){
        $this->db=$this->add('DB');
        parent::init();
    }
    function prepare(){
        $m=$this->add('Model_Table');
        $m->table='user';
        $m->addField('name');
        $m->addField('age')->calculated(function(){
            return 'length(name)';
        });

        return array($m);
    }
    function test_select($m){
        $m->load();
        return $m->get();
    }
    function test_join($m){
        $rel=$m->addRelation('company_id');
        $rel->setModel('Company');
        $rel->importField('name','company_name');
        return $m->load()->get();
    }
    function test_join2($m){
        $m->importFields('Company',array('name'=>'company_name'));
        return $m->load()->get();
    }
}
