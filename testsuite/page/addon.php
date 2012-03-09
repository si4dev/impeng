<?php
class page_addon extends Page_Tester {
    function prepare(){
        return array($this->add('helloworld\Core'));
    }
    function test_adding($a){
        return $a->name;
    }
}
