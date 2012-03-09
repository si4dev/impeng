<?php

class page_db3 extends Page_DBTest {
    public $db;
    public $proper_responses=array(
        "Test_render1"=>array (
  0 => 'hello world',
  1 => 
  array (
  ),
),
        "Test_render2"=>array (
  0 => 'hello `user`',
  1 => 
  array (
  ),
),
        "Test_expr"=>array (
  0 => 'hello world',
  1 => 
  array (
  ),
),
        "Test_recursive_render"=>array (
  0 => 'hello 1+1',
  1 => 
  array (
  ),
),
        "Test_render3"=>array (
  0 => 'hello [some_unknown_tag]',
  1 => 
  array (
  ),
),
        "Test_field1"=>array (
  0 => 'select `name`',
  1 => 
  array (
  ),
),
        "Test_field2"=>array (
  0 => 'select `name`,`surname`',
  1 => 
  array (
  ),
),
        "Test_field3"=>array (
  0 => 'select `name`,`surname`',
  1 => 
  array (
  ),
),
        "Test_field3a"=>array (
  0 => 'select `user`.`name`,`address`.`postcode`',
  1 => 
  array (
  ),
),
        "Test_field4"=>array (
  0 => 'select `address`.`name` `address_name`,`address`.`postcode`,`user`.`name`,`user`.`surname`',
  1 => 
  array (
  ),
),
        "Test_field5"=>array (
  0 => 'select len(name) `name_length`',
  1 => 
  array (
  ),
),
        "Test_field6"=>array (
  0 => 'Exception: Specified expression without alias',
  1 => 
  array (
  ),
),
        "Test_field_subquery1"=>array (
  0 => 'select  (select  sum(pages) `pages` from `book`  where `author_id` = `author`.`id`    ) `total_pages` from `author`      ',
  1 => 
  array (
  ),
)
    );
    function test_render1($t){
        return $t->template('hello world');
    }
    function test_render2($t){
        return $t->template('hello [table]')->table('user');
    }
    function test_expr($t){
        return $t->useExpr('hello world');
    }
    function test_recursive_render($t){
        return $t->template('hello [table]')->table($t->expr('1+1'));
    }
    function test_render3($t){
        return $t->template('hello [some_unknown_tag]');
    }
    function test_field1($t){
        return $t->template('select [field]')
            ->field('name');
    }
    function test_field2($t){
        return $t->template('select [field]')
            ->field('name,surname');
    }
    function test_field3($t){
        return $t->template('select [field]')
            ->field('name')->field('surname');
    }
    function test_field3a($t){
        return $t->template('select [field]')
            ->field('name','user')->field('postcode','address');
    }
    function test_field4($t){
        return $t->template('select [field]')
            ->field(array('address_name'=>'name','postcode'),'address')
            ->field(array('name','surname'),'user');
    }
    function test_field5($t){
        return $t->template('select [field]')
            ->field($t->expr('len(name)'),'name_length');
    }
    function test_field6($t){
        return $t->template('select [field]')
            ->field($t->expr('len(name)')); // missing alias
    }
    function test_field_subquery1($t){
        return $t
            ->table('author')
            ->field(
                $t->dsql()
                ->table('book')
                ->where('author_id',$t->getField('id'))
                ->field($t->expr('sum(pages)'),'pages'),
                    'total_pages');
    }
    function test_union($t){
        return $t
            ->expr('[q1] UNION [q2]')
            ->setCustom('q1',$t->dsql()->table('book'))
            ->setCustom('q2',$t->dsql()->table('book'));
    }

}
