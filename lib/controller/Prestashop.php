<?php
class Controller_Prestashop extends AbstractController {
  function init() {
    parent::init();
    
    
    // use with setController on the Model_Shop
    // then the $this->owner is the Model_Shop
    
    $this->owner->addMethod('ftproot',array($this,'ftproot'));
    $this->owner->addMethod('connection',array($this,'connection'));
    
    unset($this->api->db2);
    if($connection=$this->owner->connection()) {
      $this->api->db2=$this->api->add('DB')->connect($connection);
    }
  }

  function shopconfig($f) {
    $f->addField('line','ftproot')->set($this->owner->ftproot());
    $f->addField('line','connection')->set($this->owner->connection());
    $f->addField('line','domain')->set($this->owner->shopconfig('domain'));
    
    if($f->isSubmitted()){
      $this->owner->ftproot($f->get('ftproot'));
      $this->owner->connection($f->get('connection'));
      $this->owner->shopconfig('domain',$f->get('domain'));
    }
  }
  function ftproot($m,$v=null) { return $m->shopconfig('ftproot',$v); }
  function connection($m,$v=null) { return $m->shopconfig('connection',$v); }

      
  // imports shop categories into local table --- We do not use anymore.. 
  // decided to get it instantly from source shop.
  function import_shopcat() {
    $m=$this->owner->ref('CatShop')->deleteAll();
    $shopcats=$this->add('Model_Prestashop_Category')->load(1)->tree();
    foreach($shopcats as $ref => $title) {
      $m->unload()
          ->set('ref',$ref)
          ->set('title',$title)->save();
    }
  }
  
  // get shop categories is called in the Model_Shop and left over to the controller like here:
  function getShopCategories() {
    return $this->add('Model_Prestashop_Category')->load(1)->tree();
  }
    
    
  function import_categories($lang='nl') { 
    echo '###CAT###';
    // prepare model for shop category to add categories
    $s=$this->owner; // the shop model 
    $shopcat=$this->add('Model_Prestashop_Category');
    // prepare model to loop through the available categories to be imported (where shopid=-1)
    $cats=$this->add('Model_CatLink');
    $cats->selectQuery(); // bug fix to get the model fields
    $cats->addCondition('shop_id',$s->id)->addCondition('catshop_id',-1);
    // loop through the categories to be imported to the shop (not all, only needed ones)
    $i=0;
    foreach($cats as $cat) {
      $defaultcat=$cats->categoryByLang($lang);
      
      $level = 1; // not used for this xcart shop 
      $lastshopcatid = 0;
      $shopcatpath='';
      // loop through the levels of one category path (like breadcrumb)
      foreach( $defaultcat->node as $title ) {
        if( !(string)$title ) {
          throw $this->exception('Shopassist: supplier title node empty')->addMoreInfo('Category ID',$cats->id);
        }

        $dsql=clone $shopcat->dsql;
        // this will save or update or do nothing
        // select `categoryid`,`category`,`parentid`,`lpos`,`rpos`,`order_by` from `xcart_categories` where `parentid` = 0 and `category` = "LED verlichting" limit 0, 1 [:a_2, :a]
        // insert into `xcart_categories` (`category`,`parentid`) values ("LED verlichting",0) [:a_2, :a]
        $shopcat->addCondition('id_parent',$lastshopcatid)
          ->addCondition('name',(string)$title)
          ->addCondition('id_lang',6) // TODO loop through languages
          ->tryLoadAny()
          ->save()
          ;
        $lastshopcatid = $shopcat->get('categoryid');
        $shopcat->dsql=$dsql; // restore dsql as we added two conditions
        $level++;
      }
      // due to bug we cannot save the join, ok the bug is solved by $this->data=$this->dsql->data in Model_Table,
      // however you cannot use ->save() as it will not be possible to load again with condition CategoryShopID=-1
      $cats->set('CategoryShopID',$lastshopcatid)->saveAndUnload();
      $i++;
    }

    $this->nb_categories=$i;
    // rebuild category tree of the shop to nicely set the lpos and rpos again
    //    $shopcat->treeRebuild();
  }

  // collect all taxes used in the shop
  protected function tax() {
    foreach($this->add('Model_Prestashop_Tax') as $t) {
      if( !isset($this->tax[$t['rate']]) ) {
        $this->tax[floatval($t['rate'])]=$t['id_tax_rules_group'];
      }
    }
  }

    /*
  performance: 600 per 100 seconds
  images: 100 images in 15 seconds
  */
          
  function import() {
    echo '###import###';
    
    $s=$this->owner;
    $filepath=$this->api->getConfig('supplier_image_path'); // root path location for supplier images
    $tmp=$this->api->getConfig('tmp'); // used to compress images
            // get the mysql connection for this specific shop
    $this->api->db2=$this->api->add('DB')->connect($s->connection());
    
    // languages by this shop
    foreach($this->add('Model_Prestashop_Lang') as $l) {
      $this->languages[$l['iso_code']]=$l['id_lang'];
    }
    

    $this->tax();
    // get the time from mysql shop database which will be used later to disable products not longer updated
    $dsql2=$this->api->db2->dsql();
    $now=$dsql2->field($dsql2->expr('now()'))->getOne();

    // the product model for the target shop      
    $m=$this->add('Model_Prestashop_Product');
    // prepare FTP connection for images
    $ftp=$this->add('FTP');
    $ftp->url($this->api->getConfig('ftproot',$s->ftproot()).'/')
        ->login();
    $ftpimagespath=$ftp->path().$s->imagepath().'/'; // root path plus image relative path
    $ftp->path($ftpimagespath)->cd();
    $ftplist=$ftp->lsDates();

    // prepare image actions to be ready to retreive image information
    $img=$this->add('Actions_Image');
    
    // prepare media model
    $media=$this->add('Model_MediaOld');
    // prepare image type model
    $imgtypes=$this->add('Model_Prestashop_ImageType')->addCondition('products',1)->getRows();
    $imgtypes[]=array('name'=>'','width'=>1024,'height'=>1024);
    // traverse through the pricelist and import each product one by one 
    $pricelist = $s->ref('Pricelist');
    $pricelist->addCondition('shop_action','!=','DELETE');
    $pricelist->selectQuery(); // solves issue to get all fields and now it gets only applicable fields definined in model
    $i=0;
    foreach( $pricelist as $product ) {
      // load the prestashop product
      
     
      echo 'product ['.$product['shop_productcode'].']';
      
      $m->tryLoadBy('reference',$product['shop_productcode']);
      
      // hande manufacturer
      if($m['manufacturer']!==$product['manufacturer']) { // use !== as one shop mfr can be null and other one empty
        $mfr=$m->ref('id_manufacturer');
        $mfr
          ->tryLoadBy('name',$product['manufacturer'])
          ->set('name',$product['manufacturer'])
          ->set('active',1)
          ->save();
        $m->set('id_manufacturer',$mfr->id);
      }
      // hande supplier
      if($m['supplier']!==$product['supplier_id']) { // use !== as one shop mfr can be null and other one empty
        $supplier=$m->ref('id_supplier');
        $supplier
          ->tryLoadBy('name',$product['supplier_id'])
          ->set('name',$product['supplier_id'])
          ->set('active',1)
          ->save();
        $m->set('id_supplier',$supplier->id);
      }


      // set more product fields   
      $m->set('reference',$product['shop_productcode']); 
      $m->set('supplier_reference',$product['productcode']); 
      $m->set('quantity',$product['stock']); 
      $m->set('price',$product['price']);
      $m->set('weight',$product['weight']); 
      $m->set('ean13',$product['ean']);
      $m->set('location',$product['manufacturer_code']);
      $m->set('date_add',$product['entry_date']); 
      $m->set('date_upd',$m->dsql()->expr('now()')); 
      $m->set('id_category_default',$product['catshop_id']); 
      $m->set('id_color_default',0); 
      $m->set('id_tax_rules_group',$this->tax[floatval($product['tax'])]); 
      $m->set('active',1);
      $m->debug();
      $m->save();
    
      // handle title based on product id
      $productlang=$m->ref('Prestashop_ProductLang');
      foreach($this->languages as $langiso => $langid) {
        $pricelist->lang=$langiso;
        $productlang->tryLoadBy('id_lang',$langid);
        $productlang->set('id_lang',$langid); 
        $productlang->set('description',$pricelist->info_long()); 
        $productlang->set('description_short',$pricelist->info_short());
        $productlang->set('name',$pricelist->title());
        $productlang->set('link_rewrite',$pricelist->rewrite());
        $productlang->set('meta_description',$pricelist->meta_description());
        $productlang->set('meta_keywords',$pricelist->meta_keywords());
        $productlang->set('meta_title',$pricelist->meta_title());
        $productlang->saveAndUnload();
      }
      


      // handle category
      $category=$m->ref('Prestashop_CategoryProduct');
      $found=false;
      
      
      foreach( $category as $cat ) {
        if( $cat['id_category'] == $product['catshop_id'] ) {
          $found = true;
        } elseif ($cat['id_category'] != 1) {
          $category->delete();
        }
      }
      if( !$found ) {
        $category->set('id_category',$product['catshop_id'])->saveAndUnload();
      }
      // media file
      $media_modified=$product['media'];
      $image=$m->ref('Prestashop_Image');
      $image->tryLoadBy('cover','1');
      
      if($image->id and !$media_modified) { // no source image anylonger so delete shop image
        foreach($imgtypes as $imgtype) { // delete the differen image types
          $shopfilename=$image['filebase'].($imgtype['name']?'-'.$imgtype['name']:'').'.jpg';
          $ftp->setTarget($shopfilename)->delete();
        }
        $image->delete(); // delete entry from table
      } 
      
      
      if($media_modified) { // for sure a source image is available
        if( !$image['filebase']) { // no image in shop then first generate database entry and use id for filename
          $image->save();
        }
        if($media_modified > $ftplist[$image['filebase'].'.jpg']['date']) {
          // now we have an entry in the image table so we can upload the image to ftp
          foreach($imgtypes as $imgtype) {
            $filename=$media['file'];
            // overrule filename for development as we don't have the file library          
            if( $this->api->getConfig('mode')=='dev') $filename='test.jpg';
            $shopfilename=$image['filebase'].($imgtype['name']?'-'.$imgtype['name']:'').'.jpg';
            
            $img->resizeImage($filepath.$filename, $tmp.$shopfilename, $imgtype['width'], $imgtype['height'], 90, 'Thumb');
            $ftp->setSource($tmp.$shopfilename)
              ->setTarget($shopfilename)
              ->save();
            unlink($tmp.$shopfilename);
          }
        }
      }
          

      $i++;
      if( $i >= 10 ) break;
    }

    $this->nb_products=$i;
  }

  // function to return the shop pricelist for external purposes
  function importCategories($c) {
    echo 'TEST';
    return $this->add('Model_Prestashop_Category')->import($c);
  }


  // function to return the shop pricelist for external purposes
  function getShopPricelist() {
    return $this->add('Model_Prestashop_Product')->pricelist();
        
  }
}