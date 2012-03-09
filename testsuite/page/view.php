<?php

class MyInvisible extends AbstractView {
    function recursiveRender(){
    }
}

class page_view extends Page_Tester {
    function init(){
        $this->i=$this->add('MyInvisible');
        parent::init();
        $this->i->destroy();
    }
    function prepare($junk,$method){
        return array($this->i->add('View',$method));
    }
    function test_empty($t){
        $x=$t->add('HelloWorld');
        return $x;
    }
    function test_frame($t){
        $this->api->add('Controller_Compat','compat');

        $x=$t->frame('FrameTitle');
        $x->add('HelloWorld');
        return $x;
    }

}

