<?php
class Model_Shop extends Model_Assortment {
  function init() {
    parent::init();

    unset($this->config);
    $this->addCondition('is_shop',1);
    $this->hasMany('Pricelist');
    $this->hasMany('ProductForPricelist','target_assortment_id');
    $this->hasMany('Category');
    $this->hasMany('Filter');
    $this->hasMany('AttributeGroupLink');
    $this->hasMany('AssortmentLink','target_assortment_id');

    $this->addHook('beforeSave',function($m){
      // xml to config
      if(isset($m->config)) {
        $r='';
        foreach($m->config->children() as $n) $r.=(string)$n->asXml(); // output without root node
        $m->set('config',$r);
      }
    });

  }


  // when no value is set it will return Model_Margin, otherwise it will set margin in XML tree and return $this
  function margin($value=null) {

    if($value===null) {
      $m=$this->add('Model_margin');
      $shop=$this;
      $m->addHook('afterDelete',function($o) use($shop) { $shop->config('shopconfig/margin',$o)->save(); });
      $m->addHook('afterSave',function($o) use($shop) { $shop->config('shopconfig/margin',$o)->save(); });
      return $m->setSource('Array',(array)$this->config('shopconfig/margin'))->setOrder('from');
    } else {
      $this->config('shopconfig/margin',$value);
    }
    return $this;
  }

  // when no value is set it will return Model_Rounding, otherwise it will set rounding in XML tree and return $this
  function rounding($value=null) {

    if($value===null) {
      $m=$this->add('Model_Rounding');
      $shop=$this;
      $m->addHook('afterDelete',function($o) use($shop) { $shop->config('shopconfig/rounding',$o)->save(); });
      $m->addHook('afterSave',function($o) use($shop) { $shop->config('shopconfig/rounding',$o)->save(); });
      return $m->setSource('Array',(array)$this->config('shopconfig/rounding'))->setOrder('from');
    } else {
      $this->config('shopconfig/rounding',$value);
    }
    return $this;
  }




  function shopsystem($value=undefined) {
    return $this->config('shopconfig/shopsystem',$value);
  }

  // this function will be able to read or store a string value or an array into an XML field
  function config($fieldpath=null,$value=undefined) {
    // first get the field into XML (only once)
    if(!isset($this->config)) {
      $this->config=new SimpleXMLElement('<config>'.$this->get('config').'</config>'); // add root node
    }
    if($fieldpath===null) return $this->config;


    // set root node
    $n=$this->config;
    // test for read or store
    if($value===undefined) {


      // GET the config variable
      foreach(explode('/',$fieldpath) as $node) $n=$n->{$node};
      if(isset($n) and $n->row) {
        // if a row element is found then it's an array
        $r=array();$i=1;
        foreach($n->children() as $row) $r[$i++]=(array)$row;
        return $r;
      } elseif( !(string)$n ) {
        return $n;
      } else {
        // else it's not an array but just a string value
        return (string)$n;
      }
    } else {


      // SET the config variable
      foreach(explode('/',$fieldpath) as $node) {
        if(!$n->{$node}) $n->addChild($node);
        $n=$n->{$node};
      }

      // $value can be array or model non-relational object
      if(is_array($value) or is_object($value)) {
        // set array

        // first remove previous XML array and repeat to find the node
        unset($n->{0});
        $n=$this->config;
        foreach(explode('/',$fieldpath) as $node) {
          if(!$n->{$node}) $n->addChild($node);
          $n=$n->{$node};
        }

        // loop through the array with several rows of information and store it as XML
        $i=0;
        foreach($value as $row) {
          foreach($row as $key=>$v) {
            if($key!='id') $n->row[$i]->{$key}=$v;
          }
          $i++;
        }
      } elseif(is_null($value)) {
        // specific to unset the XML value
        unset($n->{0});
      } else {
        // set the value as string
        $n->{0}=$value; // unbelievable but it works to set current value
      }
    }
    return $this;
  }

  // returns the supplier name in case automatically category creation is required
  function category_import() {
    return (string)$this->config('category_import/supplier');
  }


  function refRounding() {
    $this->config();
    $r=$this->add('Model_Rounding');
    $r->setSource('ArrayAssoc');
    foreach($this->config->roundings as $rounding) {
      $r->set('from',(string)$rounding->from)
        ->set('value',(string)$rounding->value)
        ->set('offset',(string)$rounding->offset)
        ->save();
        print($rounding);
    }
    return $r;
  }



  function prepareAttributeGroupLink() {

    // model with attribute groups for this shop
    // TODO: now via supplier link, later via product for pricelist
    $attrGroup=$this->add('Model_AttributeGroup');
    $attrGroup->join('supplierlink.supplier_id','supplier_id')->addField('shop_id');
    $attrGroup->addCondition('shop_id',$this->id);

    // model with attribute groups to be linked to the shop
    $attrGroupLink=$this->ref('AttributeGroupLink');
    $attrGroupLink->dsql()->set('used',0)->update(); // wonderfull to update all records at once!

    foreach($attrGroup as $ag) {
      $attrGroupLink->tryLoadBy('attributegroup_id',$attrGroup->id)
          // TODO: when we use ProductForPricelist then we can also now used exactly in nr of products
          ->set('used',1)
          ->saveAndUnload();
    }

    return $attrGroupLink;

  }


  // fill Fitler table for this shop with supplier categories when not already available
  function prepareFilter() {
    $filter=$this->ref('Filter');

    // TODO: rename active into used
    $filter->dsql()->set('active',0)->update(); // wonderfull to update all records at once!

    $m=$this->ref('ProductForPricelist')->group();
    foreach($m as $active) {
      if($active['filter_id']) {
        $filter->load($active['filter_id']);
      } else {
        $filter->set('source_category_id',$active['category_id'])->set('margin_ratio',null)->set('margin_amount',null);
      }
      $filter->set('active',$active['cnt'])->saveAndUnload();
    }

    return $this;
  }

  //------------------------------------------------------------------------------------------------
  // get Shop categories, it's specific for the shop platform (prestashop etc) so leave to the controller
  function getShopCategories($target_category_reference=null) {
    // get categories from shop
    $rows=$this->controller()->getShopCategories($target_category_reference);
    // and keep them in local catshop table
    $cat=$this->ref('Category');
    if(!$target_category_reference) $cat->dsql()->set('status',0)->where('status',1)->update();
    foreach($rows as $key=>$value) {
      $cat->tryLoadBy('ref',$key);
      if($cat->get('name')!=$value) $cat->set('name',$value)->setTitle($value);
      $cat->set('status',1)->debug()->save();
    }
    //    $catshop->dsql()->where('status',1)->delete(); // do not delete to keep link from filter table
    return $cat;
  }

  function controller() {
    if(!$this->controller) {
      $shopsystem = ucwords($this->shopsystem());
      $this->controller=$this->setController($shopsystem);
    }
    return $this->controller;
  }


  function getShopPricelist() {
    return $this->controller()->getShopPricelist();
  }

  function getShopAttributes() {
    return $this->controller()->getShopAttributes();
  }

  function importCategories($filter) {
    return $this->controller()->importCategories($filter);
  }

  //------------------------------------------------------------------------------------------------
  // build pricelist!
  // fill pricelist table from supplier product tables
  function ZZZpricelist() {

    // prepare ProductForPricelist with the condition to link to a shop(=target) category
    $s=$this;
    $pricelist=$s->ref('Pricelist');

    $trgProduct=$s->ref('Product');
    $products=$s->ref('ProductForPricelist')->addCondition('target_category_id','is not',null);

    // prepare import start time
    $dsql=$this->api->db->dsql();
    $pricelist_start=$dsql->field($dsql->expr('now()'))->getOne();

    // prepare roundings
    $roundings=array();
    foreach($s->rounding()->setOrder('from') as $row) {
      $roundings[$row['from']]=array('rounding'=>$row['value'],'offset'=>$row['offset']);
    }

    // prepare margins
    $margins=array();
    foreach($s->margin()->setOrder('from') as $row) {
      $margins[$row['from']]=array('ratio'=>$row['ratio'],'amount'=>$row['amount']);
    }

    // import each product
    foreach($products as $product) {
      //print_r($product); echo '<br/><br/>';

      // find global margin for this price
      $found=false;$ratio=1;$amount=0;
      foreach($margins as $key => $value) {
        if( $key > $product['price'] ) break;
        $found=$key;
      }
      if($found!==false) {
        $ratio=$value['ratio'];
        $amount=$value['amount'];
      }
      $price_si=($product['price']*$ratio+$amount)*(1+$product['tax']/100);

      // find rounding for this price
      $found=false;$rounding=0;$offset=0;
      foreach($roundings as $key => $value) {
        if( $key > $price_si ) break;
        $found=$key;
      }
      if($found!==false) {
        $rounding=$roundings[$found]['rounding'];
        $offset=$roundings[$found]['offset'];
      }

      if($rounding < 1/100) $rounding=1/100;
      $price_si=ceil($price_si / $rounding) * $rounding + $offset;
      $price_se=$price_si / (1+$product['tax']/100);

      $price_se=round($price_se,5); // reason for rounding is that otherwise it might detect 'dirty' but it's not and it will update price every product while it's not changed due to nature of atk4


      $trgProduct->tryLoadBy('source_product_id',$product['id'])
          ->set('productcode',$product['prefix'].$product['productcode'])
          //->set('title',$product['title'])
          ->set('category_id',$product['target_category_id'])
          //->set('tax',$product['tax'])
          //->set('manufacturer',$product['manufacturer'])
          //->set('manufacturer_code',$product['manufacturer_code'])
          //->set('ean',$product['ean'])
          //->set('weight',$product['weight']!=null?$product['weight']:1)
          //->set('entry_date',$product['entry_date'])
          //->set('last_checked',$product['watch_last_checked'])
          //->set('actualised',$dsql->expr('now()'))
          //->set('shop_action','UPDATE')
          ->save();

//          ->set('price',$price_se)
//          ->set('price_si',$price_si)
//          ->set('stock',$product['stock'])
//          ;
      /*
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
      */
    }
//    $pricelist->dsql()->set('shop_action','DELETE')->where('actualised','<',$pricelist_start)->update();

    // phase 2 for all pricelist items with out of date info

if(1==0) {
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

}
    $products->setActualFields(array()); // resets to default values


    return $this;

  }


  function import() {
    return $this->controller()->import();
  }



}