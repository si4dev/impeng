<?php

class MyObject extends AbstractObject {
    public $x=1;
}
class MyController extends AbstractObject {
    public $x=1;
}
class Exception_Logic_Ouch extends Exception_Logic {
}

class page_core extends Page_Tester {
    public $proper_responses=array(
        "Test_empty"=>'1',
        "Test_clone"=>'1',
        "Test_destroy"=>'1',
        "Test_getElement"=>'1',
        "Test_getElement2"=>'1',
        "Test_hasElement"=>'1',
        "Test_hasElement2"=>'1',
        "Test_add"=>'1',
        "Test_add2_controller"=>'',
        "Test_session"=>'1',
        "Test_session2"=>'1',
        "Test_session3"=>'1',
        "Test_exception"=>'1',
        "Test_exception2"=>'1',
        "Test_exception3"=>'1',
        "Test_hook1"=>'',
        "Test_hook2"=>'1',
        "Test_hook3"=>'1',
        "Test_addMethod"=>'',
        "Test_addGlobalMethod"=>'1'
    );

    function prepare(){
        return array($this->add('MyObject'));
    }
    function test_empty($t){
        return $t->x;
    }
    function test_clone($t){
        $t2=clone $t;
        return $t2->x;
    }
    function test_destroy($t){
        $s=serialize($t->elements);
        $t->add('MyObject')->destroy();
        $s2=serialize($t->elements);
        return $s==$s2;
    }
    function test_getElement($t){
        return $t->owner->getElement($t->short_name)->x;
    }
    function test_getElement2($t){
        try{
            $t->owner->getElement('no such element');
            return false;
        }catch(Exception $e){
            return true;
        }
    }
    function test_hasElement($t){
        return !$t->owner->hasElement('no such element');
    }
    function test_hasElement2($t){
        return $t->owner->hasElement($t->short_name)->x;
    }
    function test_add($t){
        $o1=$t->add('MyObject');
        $o2=$t->add('MyObject');
        $o1->x++;
        return $o1->x != $o2->x;
    }
    function test_add2_controller($t){
        $o1=$t->add('MyController','c1');
        $o2=$t->add('MyController','c1');
        $o1->x++;
        return $o1->x == $o2->x;
    }

    function test_session($t){
        $t->memorize('foo',true);
        return $t->recall('foo');
    }
    function test_session2($t){
        $t->learn('foo',null,true);
        return $t->recall('foo');
    }
    function test_session3($t){
        $t->learn('foo',1);
        $t->forget('foo');
        return $t->recall('foo',2)===2;
    }

    function test_exception($t){
        try{
            throw $t->exception('test');
        }catch(Exception $e){
            return true;
        }
        return false;
    }
    function test_exception2($t){
        try{
            throw $t->exception('test','Logic');
        }catch(Exception_Logic $e){
            return true;
        }
        return false;
    }
    function test_exception3($t){

        try{
            $t->default_exception='Exception_Logic';
            throw $t->exception('test','_Ouch');
        }catch(Exception_Logic_Ouch $e){
            return true;
        }
        return false;
    }


    function myfunc($t,$br=false){
        if($br)$t->breakHook(65);
        return 42;
    }
    function myfunc2($x,$t){
        return 44+$t->x;
    }
    function test_hook1($t){
        $t->addHook('test',array($this,'myfunc'));
        $r=$t->hook('test',array($t));
        return $r[0]==42;
    }
    function test_hook2($t){
        $t->addHook('test',array($this,'myfunc'));
        $r=$t->hook('test',array($t,true));
        return $r==65;
    }
    function test_hook3($t){
        $t->addHook('test',array($this,'myfunc'));
        $t->removeHook('test');
        $r=$t->hook('test',array($t,true));
        return !$r;
    }

    function test_addMethod($t){
        $t->addMethod('testmethod',array($this,'myfunc'));
        return $t->testmethod()==42;
    }
    function test_addGlobalMethod($t){
        $t->api->addGlobalMethod('testmethod_gl',array($this,'myfunc2'));
        $res=($x=$t->testmethod_gl())==45;
        $t->api->removeGlobalMethod('testmethod');
        return $res;
    }

}

