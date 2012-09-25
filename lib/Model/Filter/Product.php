<?php
class Model_Filter_Product extends Model_Filter {
  public $table_alias='f';
  function init() {
    parent::init();
    
    // maybe to use this: ->expr(���� and cl.shop_id=[shop_id]�)->setCustom(�shop_id�, $cat->getElement(�shop_id�));
    $this->debug();


/* worked great, but not handy formatted: */
    $q=$this->api->db->dsql();
    $q->table('product','p2')
        ->join('filter',$q->expr("p2.category_id=f.category_id and if(f.keyword,p2.title like concat('%',f.keyword,'%'),true)"),'inner','f')
        ->field('f.id')
        ->where('f.shop_id=',$q->expr('2'))
        ->where('p2.id=',$q->expr('p.id'))
        ->limit(1)
        ;
        
    $p=$this->join('product',$this->dsql()->expr('f.id=('.$q.')'),null,'p' );



  }
  
  
  function group() {
    $this->addField('products',$this->dsql()->expr('count(1)'));
    $this->_dsql()->group('f.id');
    return $this;
  }

}

/*
select cl.id,cl.keyword, p.*
from catlink cl inner join product p
on
cl.id=
(
select cl.id 
from product p2 
inner join catlink cl on p2.category_id=cl.category_id and if(cl.keyword,p2.title like concat('%',cl.keyword,'%'),true) 
where cl.shop_id=2 and p2.id=p.id
order by rank desc limit 1
)

*/