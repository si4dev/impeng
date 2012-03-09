<?php

class page_db extends Page_DBTest {
    public $db;

        public $proper_responses=array(
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
        "Test_raw_select"=>array (
  0 => 'John, Peter, Ian, Steve, Robert, Lucas, Jane, Dot',
  1 => 
  array (
  ),
),
        "Test_simple"=>array (
  0 => 'select  `foo` from `bar`      ',
  1 => 
  array (
  ),
),
        "Test_simple_tostring"=>array (
  0 => 'select  `foo` from `bar`      ',
  1 => 
  array (
  ),
),
        "Test_simple_dot"=>array (
  0 => 'select  `x`.`foo.bar` from `bar`      ',
  1 => 
  array (
  ),
),
        "Test_multifields"=>array (
  0 => 'select  `a`,`b`,`c` from `bar`      ',
  1 => 
  array (
  ),
),
        "Test_multitable"=>array (
  0 => 'select  `foo`.`a`,`foo`.`b`,`foo`.`c`,`bar`.`x`,`bar`.`y` from `bar`,`baz`      ',
  1 => 
  array (
  ),
),
        "Test_selectall"=>array (
  0 => 'select  * from `bar`      ',
  1 => 
  array (
  ),
),
        "Test_select_opton1"=>array (
  0 => 'select SQL_CALC_FOUND_ROWS * from `foo`      ',
  1 => 
  array (
  ),
),
        "Test_select_calc_rows"=>array (
  0 => 'select SQL_CALC_FOUND_ROWS * from `foo`      limit 0, 5',
  1 => 
  array (
  ),
),
        "Test_select_calc_rows2"=>array (
  0 => '8',
  1 => 
  array (
  ),
),
        "Test_select_calc_rows3"=>array (
  0 => '8',
  1 => 
  array (
  ),
),
        "Test_row"=>array (
  0 => 'Array
(
    [id] => 2
    [name] => Peter
    [a] => 2
    [b] => 4
    [c] => 7
)
',
  1 => 
  array (
    ':a' => 2,
  ),
),
        "Test_getAll"=>array (
  0 => 'Array
(
    [0] => Array
        (
            [id] => 1
            [name] => John
            [a] => 1
            [b] => 2
            [c] => 3
        )

    [1] => Array
        (
            [id] => 2
            [name] => Peter
            [a] => 2
            [b] => 4
            [c] => 7
        )

)
',
  1 => 
  array (
    ':a' => 1,
    ':a_2' => 2,
  ),
),
        "Test_ts"=>array (
  0 => 'select  * from `foo`      ',
  1 => 
  array (
  ),
),
        "Test_expr"=>array (
  0 => 'call foobar()',
  1 => 
  array (
  ),
),
        "Test_expr2"=>array (
  0 => 'select  (select 1) `x1`,3+3 `x2`        ',
  1 => 
  array (
  ),
),
        "Test_expr3"=>array (
  0 => 'client',
  1 => 
  array (
  ),
),
        "Test_expr4"=>array (
  0 => 'foo',
  1 => 
  array (
  ),
),
        "Test_expr5"=>array (
  0 => 'foo..bar',
  1 => 
  array (
  ),
),
        "Test_update"=>array (
  0 => 'update `foo` set `name`=:a where `id` = :a_2',
  1 => 
  array (
    ':a' => 'Silvia',
    ':a_2' => '1',
  ),
),
        "Test_update2"=>array (
  0 => 'Array
(
    [0] => Array
        (
            [id] => 1
            [name] => Silvia
            [a] => 1
            [b] => 2
            [c] => 3
        )

    [1] => Array
        (
            [id] => 2
            [name] => Peter
            [a] => 2
            [b] => 4
            [c] => 7
        )

)
',
  1 => 
  array (
    ':a' => 1,
    ':a_2' => 2,
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
    function test_raw_select($t){
        $stmt=$this->db->query('select name from foo');
        $data=array();
        foreach($stmt as $row){
            $data[]=$row['name'];
        }
        return implode(', ',$data);
    }
    function test_simple($t){
        return $t->table('bar')->field('foo')->select();
    }
    function test_simple_tostring($t){
        return $t->table('bar')->field('foo');
    }
    function test_simple_dot($t){
        return $t->table('bar')->field('foo.bar','x')->select();
    }
    function test_multifields($t){
        return $t->table('bar')->field(array('a','b','c'))->select();
    }
    function test_multitable($t){
        return $t->table(array('bar','baz'))->field(array('a','b','c'),'foo')->field(array('x','y'),'bar')->select();
    }
    function test_selectall($t){
        return $t->table('bar')->select();
    }
    function test_select_opton1($t){
        return $t->table('foo')->option('SQL_CALC_FOUND_ROWS')->select();
    }
    function test_select_calc_rows($t){
        return $t->table('foo')->limit(5)->calc_found_rows()->select();
    }
    function test_select_calc_rows2($t){
        $data=$t->table('foo')->limit(5)->calc_found_rows()->do_getAll();
        return $t->foundRows();
    }
    function test_select_calc_rows3($t){
        $data=$t->table('foo')->limit(5)->do_getAll();// not using option, backward-compatible mode
        return $t->foundRows();
    }
    function test_row($t){
        return print_r($t->table('foo')->where('id',2)->fetch(),true);
    }
    function test_getAll($t){
        return print_r($t->table('foo')->where('id',array(1,2))->getAll(),true);
    }
    function test_ts($t){
        return $t->table('foo');
    }
    function test_expr($t){
        return $t->expr('call foobar()');
    }
    function test_expr2($t){
        return $t
            ->field($t->expr('(select 1)'),'x1')
            ->field($t->expr('3+3'),'x2');
    }
    function test_expr3($t){
        return $t->expr('show tables')->getOne();
    }
    function test_expr4($t){
        return $t->expr('select [args]')->args(array('foo'))->getOne();
    }
    function test_expr5($t){
        return implode(',',$t->expr('select concat_ws([args])')->args(array('..','foo','bar'))->getHash());
    }
    function test_update($t){
        return $t->table('foo')->where('id','1')->set('name','Silvia')->update();
    }
    function test_update2($t){
        $tt=clone $t;
        $tt->table('foo')->where('id','1')->set('name','Silvia')->do_update();
        return print_r($t->table('foo')->where('id',array(1,2))->getAll(),true);
    }
}
