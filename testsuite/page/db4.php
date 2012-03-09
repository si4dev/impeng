<?php

class page_db4 extends Page_DBTest {
    public $db;
        public $proper_responses=array(
        "Test_combi"=>array (
  0 => 'select  * from `book`  where `id` = :a     => update `book` set `name`=:a where `id` = :a_2',
  1 => 
  array (
    ':a' => 'Foo',
    ':a_2' => 1,
  ),
),
        "Test_join1"=>array (
  0 => 'select  * from `user`      ',
  1 => 
  array (
  ),
),
        "Test_join2"=>array (
  0 => 'select  * from `user` left join `address` on `address`.`id` = `user`.`address_id`     ',
  1 => 
  array (
  ),
),
        "Test_join3"=>array (
  0 => 'select  * from `user` left join `address` on `address`.`user_id` = `user`.`id`     ',
  1 => 
  array (
  ),
),
        "Test_join4"=>array (
  0 => 'select  * from `user` inner join `address` on `address`.`code` = `user`.`code`     ',
  1 => 
  array (
  ),
),
        "Test_join5"=>array (
  0 => 'select  * from `user` left join `address` on `address`.`id` = `user`.`address_id` left join `portfolio` on `portfolio`.`id` = `user`.`portfolio_id`     ',
  1 => 
  array (
  ),
),
        "Test_join6"=>array (
  0 => 'select  * from `user` left join `address` as `a` on `a`.`id` = `user`.`address_id` left join `portfolio` as `p` on `p`.`id` = `user`.`portfolio_id`     ',
  1 => 
  array (
  ),
),
        "Test_call"=>array (
  0 => 'call myfunc(:a, :a_2, :a_3)',
  1 => 
  array (
    ':a' => '1',
    ':a_2' => 'test,def',
    ':a_3' => 'abc',
  ),
),
        "Test_group"=>array (
  0 => 'select  `name`,count(*) from `user`   group by `name`, `surname`   ',
  1 => 
  array (
  ),
),
        "Test_order"=>array (
  0 => 'select  * from `user`     order by `name`, `surname` ',
  1 => 
  array (
  ),
),
        "Test_order2"=>array (
  0 => 'select  * from `user`     order by `name`, `surname` ',
  1 => 
  array (
  ),
),
        "Test_limit"=>array (
  0 => 'select  * from `user`      limit 0, 5',
  1 => 
  array (
  ),
),
        "Test_insert"=>array (
  0 => 'insert  into `user` (`foo`) values (:a)',
  1 => 
  array (
    ':a' => 123,
  ),
)
    );
    function test_combi($t){
        $t->table('book')->where('id',1)->set('name','Foo');
        return $t->select().' => '.$t->update();
    }
    function test_join1($t){
        return $t->table('user');
    }
    function test_join2($t){
        return $t->table('user')->join('address');
    }
    function test_join3($t){
        return $t->table('user')->join('address.user_id');
    }
    function test_join4($t){
        return $t->table('user')->join('address.code','code','inner');
    }
    function test_join5($t){
        return $t->table('user')
            ->join('address')
            ->join('portfolio');
    }
    function test_join6($t){
        return $t->table('user')->join(array('a'=>'address','p'=>'portfolio'));
    }
    function test_call($t){
        return $t->call('myfunc',array('1','test,def','abc'));
    }
    function test_group($t){
        return $t->table('user')->field('name,count(*)')->group('name,surname');
    }
    function test_order($t){
        return $t->table('user')->order('name,surname');
    }
    function test_order2($t){
        return $t->table('user')->order('name')->order('surname');
    }
    function test_limit($t){
        return $t->table('user')->limit(5);
    }
    function test_insert($t){
        return $t->table('user')->set('foo',123)->insert();
    }

}
