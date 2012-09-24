<?php
class Model_Shop extends Model_Table {
  public $table='shop';
  function init() {
    parent::init();

    unset($this->config);
    $this->addField('name');
    $this->addField('schedule')->enum(array('disable','daily','manual','test'));
    $this->addField('config')->visible(false)->editable(false);
    $this->hasOne('User',null,'name'); 
    $this->hasMany('Pricelist');
    $this->hasMany('ProductForPricelist');
    $this->hasMany('Filter');
    $this->hasMany('CatShop');
    $this->hasMany('SupplierLink');
    
    $this->addHook('beforeSave',function($m){

      // xml to config
      if(isset($m->config)) {
        $r='';
        foreach($m->config->children() as $n) $r.=(string)$n->asXml(); // output without root node
        $m->set('config',$r);
      }
    });
    
  }




  function shopconfig($field,$value=null) {
    $this->config();
    if($value===null) return (string)$this->config->shopconfig->{$field};
    $this->config->shopconfig->{$field}->{0}=$value;
    return $this;
  }
  function shopsystem($v=null) {
    return $this->shopconfig('shopsystem',$v);
  }
  
  function config($cfg=null) {
    if(!isset($this->config)) {
      $this->config=new SimpleXMLElement('<config>'.$this->get('config').'</config>'); // add root node
    }
    
    
    return;
    
    // not ready:
    
    if($cfg) {
      foreach($cfg as $key => $value) {
        $n=$this->config;
        $key='q1_q2_q3_q4_q5';
        foreach(explode('_',$key) as $node) {
          if(!$n->{$node}) $n->addChild($node);
          $n=$n->{$node};
        }
        $n->{0}=$value; // unbelievable but it works to set current value
      }
      $x=$this->config->{'x1'}->{'x2'}->{'x3'}->{0};
      $x->{0}='X4';
      $this->config->testje='mooi<haha>ir</haha>lekker';
      $r='';
      foreach($this->config->children() as $n) $r.=(string)$n->asXml(); // output without root node
      $this->set('config',$r);
    }
  }


    

  

  function imagepath() {
    $this->config();
    return (string)$this->config->shopconfig->imagepath;
  }

  function thumbspath() {
    $this->config();
    return (string)$this->config->shopconfig->thumbspath;
  }

    
  function category_import() {
    $this->config();
    return (string)$this->config->category_import->supplier;
  }

  function roundings() {
    $this->config();
    $r=$this->add('Model_Rounding');
    $r->setSource('Array');
    foreach($this->config->roundings as $rounding) {
      $r->set('from',(string)$rounding->from)
        ->set('value',(string)$rounding->value)
        ->set('offset',(string)$rounding->offset)
        ->save();
    }
    
    
    foreach($r as $rr) print_r($rr);
    return $r;
  }
  
  // fill Fitler table for this shop with supplier categories when not already available
  function prepareFilter() {
    $filter=$this->ref('Filter');
    //$cl->dsql()->set('active',0)->update(); // wonderfull to update all records at once!

    // select `category`.`title`,`category`.`id`,`sl`.`shop_id`,`category`.`supplier_id` `sl` from `category` inner join `supplierlink` as `sl` on `sl`.`supplier_id` = `category`.`supplier_id` left join `catlink` as `cl` on cl.category_id=category.id and cl.shop_id=sl.shop_id where cl.id is null and `sl`.`shop_id` = 2
    $cat=$this->add('Model_Category');
    $cat->join('supplierlink.supplier_id','supplier_id',null,'sl')->addField('shop_id');
    $cat->leftJoin('filter',$cat->dsql()->expr('f.category_id=category.id and f.shop_id=sl.shop_id'),null,'f');
    $cat->addCondition($cat->dsql()->expr('f.id is null'));
    $cat->addCondition('shop_id',$this->id);

    foreach($cat as $category) {
      $filter->tryLoadBy('category_id',$category['id'])->save()->set('margin_ratio',null)->set('margin_amount',null)->save();
    }
    return $filter;
  }
  
  // get Shop categories, it's specific for the shop platform (prestashop etc) so leave to the controller
  function getShopCategories() {
    $shopsystem = ucwords($this->shopsystem());
    return $this->setController($shopsystem)->getShopCategories();
  }


  function getShopPricelist() {
    $shopsystem = ucwords($this->shopsystem());
    return $this->setController($shopsystem)->getShopPricelist();
  }
    
  
}   