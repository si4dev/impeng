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
    
    if( !$this->api->isAjaxOutput() ) {

      $filter->dsql()->set('active',0)->update(); // wonderfull to update all records at once!
    

    
      /*
      // select `category`.`title`,`category`.`id`,`sl`.`shop_id`,`category`.`supplier_id` `sl` from `category` inner join `supplierlink` as `sl` on `sl`.`supplier_id` = `category`.`supplier_id` left join `catlink` as `cl` on cl.category_id=category.id and cl.shop_id=sl.shop_id where cl.id is null and `sl`.`shop_id` = 2
      $cat=$this->add('Model_Category');
      $cat->join('supplierlink.supplier_id','supplier_id',null,'sl')->addField('shop_id');
      $cat->leftJoin('filter',$cat->dsql()->expr('f.category_id=category.id and f.shop_id=sl.shop_id'),null,'f');
      $cat->addCondition($cat->dsql()->expr('f.id is null'));
      $cat->addCondition('shop_id',$this->id);

      foreach($cat as $category) {
        $filter->tryLoadBy('category_id',$category['id'])->save()->set('margin_ratio',null)->set('margin_amount',null)->save();
      }
      */
    
      $m=$this->ref('ProductForPricelist')->group();
      foreach($m as $active) {
        
        if($active['filter_id']) {
          $filter->load($active['filter_id']);
        } else {
          $filter->set('category_id',$active['category_id'])->set('margin_ratio',null)->set('margin_amount',null);
        }
          
        $filter->set('active',$active['cnt'])->saveAndUnload();
        
      }
    }
  
    
    return $filter;
  }
  
  // get Shop categories, it's specific for the shop platform (prestashop etc) so leave to the controller
  function getShopCategories() {
    // get categories from shop
    $shopsystem = ucwords($this->shopsystem());
    $rows=$this->setController($shopsystem)->getShopCategories();
    // and keep them in local catshop table
    $catshop=$this->ref('CatShop');
    $catshop->dsql()->set('status',1)->where('status',0)->update();
    foreach($rows as $key=>$value) {
      $catshop->tryLoadBy('ref',$key)->set('title',$value)->set('status',0)->saveAndUnload();
    }
    $catshop->dsql()->where('status',1)->delete();
    return $this;
  }
  


  function getShopPricelist() {
    $shopsystem = ucwords($this->shopsystem());
    return $this->setController($shopsystem)->getShopPricelist();
  }
    
      
  // build pricelist!
  function pricelist() {
    // fill pricelist table from supplier product tables
    $s=$this;
    $pricelist=$s->ref('Pricelist');
    $products=$s->ref('ProductForPricelist')->addCondition('catshop_id','is not',null);
    
    $dsql=$this->api->db->dsql();
    $pricelist_start=$dsql->field($dsql->expr('now()'))->getOne();

    $margins=array('0'=>'1','200'=>'0.1');
    $roundings[0]=array('rounding'=>'1','offset'=>'-0.05');
    $roundings[100]=array('rounding'=>'1','offset'=>'0');
    
    foreach($products as $product) {
      //print_r($product); echo '<br/><br/>';
      $margin=1;
      foreach($margins as $key => $value) {
        if( $key > $product['price'] ) break;
        $margin=$value;
      }
  
      $price_si=$product['price']*$margin*(1+$product['tax']/100);

      $rounding=0;
      $offset=0;
      foreach($roundings as $key => $value) {
        if( $key > $price_si ) break;
        $found=$key;
      }
      $rounding=$roundings[$found]['rounding'];
      $offset=$roundings[$found]['offset'];
      if($rounding < 1/100) $rounding=1/100;
      $price_si=ceil($price_si / $rounding) * $rounding + $offset;
      $price_se=$price_si / (1+$product['tax']/100);
      $pricelist->tryLoadBy('product_id',$product['id'])
          ->set('productcode',$product['productcode'])
          ->set('shop_productcode',$product['prefix'].$product['productcode'])
          ->set('title',$product['title'])
          ->set('supplier_id',$product['supplier_id'])
          ->set('category_id',$product['category_id'])
          ->set('catshop_id',$product['catshop_id'])
          ->set('tax',$product['tax'])
          ->set('manufacturer',$product['manufacturer'])
          ->set('manufacturer_code',$product['manufacturer_code'])
          ->set('price',$price_se)
          ->set('price_si',$price_si)
          ->set('price_pe',$product['price'])
          ->set('stock',$product['stock'])
          ->set('ean',$product['ean'])
          ->set('weight',$product['weight']!=null?$product['weight']:1)
          ->set('entry_date',$product['entry_date'])
          ->set('last_checked',$product['watch_last_checked'])
          ->set('actualised',$dsql->expr('now()'))
          ->set('shop_action','UPDATE');
      $pricelist->saveAndUnload();
      
    }
    $pricelist->dsql()->set('shop_action','DELETE')->where('actualised','<',$pricelist_start)->update();
    
    // phase 2 for all pricelist items with out of date info
    
    
    $products=$s->ref('Pricelist');
    $products->join('product')->addField('info');
    $products->addCondition('info_actualised','<',$products->dsql()->expr('info_modified'));  
    $products->setActualFields(array('id','info'));
    /*
          	WebArticleID, WebArticleCode, p.ProductTitle, p.ProductSupplier,
        p.ProductManufacturer, p.ProductManufacturerCode, p.ProductEan,
        p.ProductSpecification,  
        icecat.ProductSpecification as ProductInfo
*/
    

    $pricelist=$this->add('Model_Pricelist')->setActualFields(array('info_long','info_actualised'));
    $pricelist->selectQuery();
    $now=$pricelist->dsql()->expr('now()');
    foreach($products as $product) {
      echo 'INFO:';
      $pricelist->load($product['id'])
          ->setInfo($product['info'])
          ->set('info_actualised',$now)
          ->saveAndUnload();
      print_r($product);
      echo '<br/><br/>';
    }
    $products->setActualFields(array()); // resets to default values
    $pricelist->setActualFields(array()); // resets to default values

    
    return $this;
    
  }
  
  
  function import() {
    $shopsystem = ucwords($this->shopsystem());
    return $this->setController($shopsystem)->import();
  }
    

  
}   