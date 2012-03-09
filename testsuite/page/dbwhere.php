<?php

class page_dbwhere extends Page_Tester {
    public $db;
    public $proper_responses=array(
        "Test_create"=>array (
  0 => '',
  1 => 
  array (
  ),
),
        "Test_insert"=>array (
  0 => '',
  1 => 
  array (
  ),
),
        "Test_where1"=>array (
  0 => 'select  * from `foo`  where `id` = :a    ',
  1 => 
  array (
    ':a' => 2,
  ),
),
        "Test_where2a"=>array (
  0 => 'select  * from `foo`  where `a` > :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2_compat"=>array (
  0 => 'select  * from `foo`  where `a` > :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2b"=>array (
  0 => 'select  * from `foo`  where `a` < :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2c"=>array (
  0 => 'select  * from `foo`  where `a` >= :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2d"=>array (
  0 => 'select  * from `foo`  where `a` <= :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2e"=>array (
  0 => 'select  * from `foo`  where `a` != :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2f"=>array (
  0 => 'select  * from `foo`  where `a` <> :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2g"=>array (
  0 => 'select  * from `foo`  where `a` in :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2h"=>array (
  0 => 'select  * from `foo`  where `a` not in :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2i"=>array (
  0 => 'select  * from `foo`  where `a` like :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2j"=>array (
  0 => 'select  * from `foo`  where `a` not like :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where2k"=>array (
  0 => 'select  * from `foo`  where `anot` like :a    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where3_or"=>array (
  0 => 'select  * from `foo`  where (`a` = :a or `b` = :a_2)    ',
  1 => 
  array (
    ':a' => 1,
    ':a_2' => 2,
  ),
),
        "Test_where4_in"=>array (
  0 => 'select  * from `foo`  where `a` in (:a,:a_2)    ',
  1 => 
  array (
    ':a' => 1,
    ':a_2' => 2,
  ),
),
        "Test_where5_1"=>array (
  0 => 'select  * from `foo`  where length(name) > :a    ',
  1 => 
  array (
    ':a' => 2,
  ),
),
        "Test_where5_2"=>array (
  0 => 'select  * from `foo`  where length(name) in (:a,:a_2)    ',
  1 => 
  array (
    ':a' => 2,
    ':a_2' => 4,
  ),
),
        "Test_where5_3"=>array (
  0 => 'select  * from `foo`  where (select  `name` from `foo`  where `id` = :a    ) in (:a_2,:a_3)    ',
  1 => 
  array (
    ':a' => 1,
    ':a_2' => 2,
    ':a_3' => 4,
  ),
),
        "Test_where5_4"=>array (
  0 => 'select  * from `foo`  where a=b    ',
  1 => 
  array (
  ),
),
        "Test_where6"=>array (
  0 => 'select  * from `foo`  where `id` = (select  `name` from `foo`  where `id` = :a    )    ',
  1 => 
  array (
    ':a' => 1,
  ),
),
        "Test_where7"=>array (
  0 => 'select  * from `foo`  where `id` = (select  `name` from `foo`  where `id` = :a    )  having `id` > :a_2  ',
  1 => 
  array (
    ':a' => 2,
    ':a_2' => 1,
  ),
),
        "Test_clone_param"=>array (
  0 => 'select  * from `foo`      ',
  1 => 
  array (
  ),
)
    );
    function init(){
        $this->db=$this->add('DB');
        $this->db->connect();
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
    function test_create($t){
        $this->db->query('drop temporary table if exists foo');
        $this->db->query('create temporary table if not exists foo (id int not null primary key auto_increment, name varchar(255), a int, b
                    int, c int)');
    }
    function test_insert($t){
        $this->db->query('insert into foo (name,a,b,c) values ("John", 1,2,3),("Peter", 2,4,7),("Ian", 2,4,7),'.
                '("Steve", 2,4,7),("Robert", 2,4,7),("Lucas", 2,4,7),("Jane", 2,4,7),("Dot", 2,4,7)');
    }
    function test_where1($t){
        return $t->where('id',2);
    }
    function test_where2a($t){
        return $t->where('a','>',1);
    }
    function test_where2_compat($t){
        return $t->where('a>',1);
    }
    function test_where2b($t){
        return $t->where('a<',1);
    }
    function test_where2c($t){
        return $t->where('a>=',1);
    }
    function test_where2d($t){
        return $t->where('a<=',1);
    }
    function test_where2e($t){
        return $t->where('a!=',1);
    }
    function test_where2f($t){
        return $t->where('a<>',1);
    }
    function test_where2g($t){
        return $t->where('a in',1);
    }
    function test_where2h($t){
        return $t->where('a not in',1);
    }
    function test_where2i($t){
        return $t->where('a like',1);
    }
    function test_where2j($t){
        return $t->where('a not like',1);
    }
    function test_where2k($t){
        return $t->where('anot like',1);
    }
    function test_where3_or($t){
        return $t->where(array(array('a',1),array('b',2)));
    }
    function test_where4_in($t){
        return $t->where('a',array(1,2));
    }
    function test_where5_1($t){
        return $t->where($t->expr('length(name)'),'>',2);
    }
    function test_where5_2($t){
        return $t->where($t->expr('length(name)'),array(2,4));
    }
    function test_where5_3($t){
        return $t->where(
                $t->dsql()
                ->table('foo')
                ->field('name')
                ->where('id',1)
                ,array(2,4)
                );
    }
    function test_where5_4($t){
        return $t->where('a=b');
    }
    function test_where6($t){
        return $t->where('id',$t->dsql()->table('foo')->field('name')->where('id',1));
    }
    function test_where7($t){
        // params should be merged
        return $t
            ->where('id',$t->dsql()->table('foo')->field('name')->where('id',2))
            ->having('id>',1);
    }
    function test_clone_param($t){
        // params should be empty
        $x=clone $t;
        $x->where('foo','bar');
        return $t;
    }
}

