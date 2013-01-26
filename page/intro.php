<?php
class page_intro extends Page {
   function init(){
       parent::init();
       $page=$this;
       $p=$page;
       $p->api->dbConnect();
       $f=$p->add("Form");
       $f->addField("line",'name');
       $fi=$f->addField('line','surname');
       $fi->validateField(function($fi) {
               if ($fi->get() != 'Chato') return 'You are not Chato';
       });
       $f->addSubmit('Hello');

       $g=$p->add('Grid');
       $g->addColumn('text','name');
       $g->addColumn('text','comment');
       $g->addColumn('text','liveplace');
       $g->setSource("friends");
       //$svalue=$g->getFieldContent('name','2');
       $p->add('H1')->set($value='Using Models');

       $gr=$page->add('MVCGrid');
       $gr->setModel('User')->addCondition('login','alm');//->debug();
       $gr->dq->limit(25);

       $p->add('H1')->set($value='Using Models and CRUD');
       $p->add('CRUD')->setModel('User');


   }
}