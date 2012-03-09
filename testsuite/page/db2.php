<?php

class page_db2 extends Page_DBTest {
    public $db;
    public $proper_responses=array(
        "Test_create"=>array (
  0 => '',
  1 => 
  array (
  ),
),
        "Test_raw_insert"=>array (
  0 => '',
  1 => 
  array (
  ),
),
        "Test_raw_getOne"=>array (
  0 => 'John',
  1 => 
  array (
  ),
),
        "Test_table1"=>array (
  0 => 'user',
  1 => 
  array (
  ),
),
        "Test_table2"=>array (
  0 => 'a',
  1 => 
  array (
  ),
),
        "Test_table3"=>array (
  0 => 'user',
  1 => 
  array (
  ),
),
        "Test_table4"=>array (
  0 => 'a',
  1 => 
  array (
  ),
),
        "Test_table5"=>array (
  0 => 'false',
  1 => 
  array (
  ),
),
        "Test_table6"=>array (
  0 => 'NULL',
  1 => 
  array (
  ),
),
        "Test_table_get1"=>array (
  0 => '`foo`',
  1 => 
  array (
  ),
),
        "Test_table_get2"=>array (
  0 => '`foo`,`bar`',
  1 => 
  array (
  ),
),
        "Test_table_get3"=>array (
  0 => '`foo,bar`',
  1 => 
  array (
  ),
),
        "Test_table_get4"=>array (
  0 => '`foo` `bar`',
  1 => 
  array (
  ),
),
        "Test_table_get5"=>array (
  0 => '`foo`,`bar`',
  1 => 
  array (
  ),
),
        "Test_table_get6"=>array (
  0 => '`foo` `a`,`bar` `b`',
  1 => 
  array (
  ),
),
        "Test_table_get7"=>array (
  0 => '`foo`,`foo`',
  1 => 
  array (
  ),
),
        "Test_table_get_na1"=>array (
  0 => '`foo`',
  1 => 
  array (
  ),
),
        "Test_table_get_na2"=>array (
  0 => '`foo`, `bar`',
  1 => 
  array (
  ),
),
        "Test_table_get_na3"=>array (
  0 => '`foo,bar`',
  1 => 
  array (
  ),
),
        "Test_table_get_na4"=>array (
  0 => '`foo`',
  1 => 
  array (
  ),
),
        "Test_table_get_na5"=>array (
  0 => '`foo`, `bar`',
  1 => 
  array (
  ),
),
        "Test_table_get_na6"=>array (
  0 => '`foo`, `bar`',
  1 => 
  array (
  ),
),
        "Test_table_get_na7"=>array (
  0 => '`foo`, `foo`',
  1 => 
  array (
  ),
)
    );
    function test_raw_insert($t){
        $this->db->query('insert into foo (name,a,b,c) values ("John", 1,2,3)');
        $this->db->query('insert into foo (name,a,b,c) values ("Peter", 2,4,7)');
        $this->db->query('insert into foo (name,a,b,c) values ("Ian", 2,4,7)');
        $this->db->query('insert into foo (name,a,b,c) values ("Steve", 2,4,7)');
        $this->db->query('insert into foo (name,a,b,c) values ("Robert", 2,4,7)');
        $this->db->query('insert into foo (name,a,b,c) values ("Lucas", 2,4,7)');
        $this->db->query('insert into foo (name,a,b,c) values ("Jane", 2,4,7)');
        $this->db->query('insert into foo (name,a,b,c) values ("Dot", 2,4,7)');
    }
    function test_raw_getOne($t){
        return $this->db->getOne('select name from foo');
    }

    function test_table1($t){
        return $t->table('user')->table();
    }
    function test_table2($t){
        return $t->table('user','a')->table();
    }
    function test_table3($t){
        return $t->table(array('user'))->table();
    }
    function test_table4($t){
        return $t->table(array('a'=>'user'))->table();
    }
    function test_table5($t){
        return var_export($t->table(array('user','address'))->table(),true);
    }
    function test_table6($t){
        return var_export($t->table(),true);
    }

    function test_table_get1($t){
        $t->table('foo');
        return $t->render_table();
    }
    function test_table_get2($t){
        $t->table('foo')->table('bar');
        return $t->render_table();
    }
    function test_table_get3($t){
        $t->table('foo,bar');
        return $t->render_table();
    }
    function test_table_get4($t){
        $t->table('foo','bar');
        return $t->render_table();
    }
    function test_table_get5($t){
        $t->table(array('foo','bar'));
        return $t->render_table();
    }
    function test_table_get6($t){
        $t->table(array('a'=>'foo','b'=>'bar'));
        return $t->render_table();
    }
    function test_table_get7($t){
        $t->table('foo')->table('foo');
        return $t->render_table();
    }

    function test_table_get_na1($t){
        $t->table('foo');
        return $t->render_table_noalias();
    }
    function test_table_get_na2($t){
        $t->table('foo')->table('bar');
        return $t->render_table_noalias();
    }
    function test_table_get_na3($t){
        $t->table('foo,bar');
        return $t->render_table_noalias();
    }
    function test_table_get_na4($t){
        $t->table('foo','bar');
        return $t->render_table_noalias();
    }
    function test_table_get_na5($t){
        $t->table(array('foo','bar'));
        return $t->render_table_noalias();
    }
    function test_table_get_na6($t){
        $t->table(array('a'=>'foo','b'=>'bar'));
        return $t->render_table_noalias();
    }
    function test_table_get_na7($t){
        $t->table('foo')->table('foo');
        return $t->render_table_noalias();
    }
}
