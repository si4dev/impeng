<?php

class Model_X extends Model_Table {
    public $entity_code='x';
    function init(){
        parent::init();
        $this->addField('foo')->mandatory(true);
    }
}

class page_form extends Page {
    function init(){
        parent::init();
        $f=$this->add('Form');
        $a=$f->addSubmit('John Doe');
        $b=$f->addSubmit('b');
        if($f->isSubmitted()){
            if($f->isClicked($a)){
                $this->js()->univ()->alert('John Doe clicked')->execute();
                $this->add('View_Info')->set('John Doe clicked');
            }
            if($f->isClicked('b')){
                $this->js()->univ()->alert('b clicked')->execute();
                $this->add('View_Info')->set('b clicked');
            }
        }

        $this->add('MVCForm')->setModel('X');
    }

}
