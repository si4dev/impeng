<?php
class Controller_Prestashop extends AbstractController {
  protected $lang='nl';
  function init() {
    parent::init();
    $this->version='1.5';
    
    if(!isset($this->owner->platformCtrl)) {
      $this->owner->platformCtrl=$this;
    
      // use with setController on the Model_Shop
      // then the $this->owner is the Model_Shop
      
      $this->owner->addMethod('ftproot',array($this,'ftproot'));
      $this->owner->addMethod('connection',array($this,'connection'));
      $this->owner->addMethod('imagepath',array($this,'imagepath'));
      
      unset($this->api->db2);
      if($connection=$this->owner->connection()) {
        $this->api->db2=$this->api->add('DB')->connect($connection);
      }
    }
  }

  function shopconfig($f) {
    $f->addField('line','ftproot')->set($this->owner->ftproot());
    $f->addField('line','connection')->set($this->owner->connection());
    $f->addField('line','domain')->set($this->owner->config('profile/domain'));
	$f->addField('line', 'email')->set($this->owner->config('profile/email'));
    
    if($f->isSubmitted()){
      $this->owner->ftproot($f->get('ftproot'));
      $this->owner->connection($f->get('connection'));
      $this->owner->config('profile/domain',$f->get('domain'));
      $this->owner->config('profile/email',$f->get('email'));
    }
  }
  function ftproot($m,$v=undefined) { return $m->config('shopconfig/ftproot',$v); }
  function connection($m,$v=undefined) { return $m->config('shopconfig/connection',$v); }
  function imagepath($m,$v=undefined) { return $m->config('shopconfig/imagepath',$v); }

      
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
    $defaultlangid=$this->add('Model_Prestashop_Lang')->loadBy('iso_code',$this->lang)->id;
    $cat=$this->add('Model_Prestashop_Category')->lang($defaultlangid);
    return $cat->load($cat->getTreeHome())->tree();
  }

    
  // function to return the shop pricelist for external purposes
  function importCategories($filter) {
    $s=$this->owner; // the shop model
    // languages by this shop
    foreach($this->add('Model_Prestashop_Lang') as $l) {
      $this->languages[$l['iso_code']]=$l['id_lang'];
    }

    $shopgroup=$this->add('Model_Prestashop_Group');
    $shopcat=$this->add('Model_Prestashop_Category'); // the shop category model is the destination
    $shopcat->joinCategoryShop();
    $shopcatname=clone $shopcat;
    $shopcatname
        ->joinName()
        ->addCondition('id_lang',$this->languages[$this->lang]) // default language to lookup name
        ;
    $q=$shopcat->dsql(); // needed for some expressions
    $cat=$this->add('Model_Category'); // this is only used to get title in proper language from the category model
               
    $i=0;
    foreach( $filter as $f) {
      
      
      // get all source titles in XML <cat lang='nl'><node>..</node><node>..</node>..</cat> format
      $catxml=$cat->load($f['category_id'])->getTitleXml();
      // find the default source category to be used to match and for defaults      
      $defaultcatxml=$catxml->xpath('cat[@lang="'.$this->lang.'"]');
      
      if(!$defaultcatxml) {
        throw $this->exception('Shopassist: category title not available')
            ->addMoreInfo('Category ID',$f['category_id'])
            ->addMoreInfo('lang',$this->lang);
      }
      
      $level=1; // keep starting at ONE for category node xpath start at ONE (node[1])
      $lastshopcatid = $shopcatname->getTreeHome(); // home category in prestashop
      // loop through the levels of one category path (like breadcrumb)
      foreach( current($defaultcatxml)->node as $defaulttitle ) { // current($defaultcatxml) is for the default language
        if( !(string)$defaulttitle ) {
          throw $this->exception('Shopassist: supplier title node empty')->addMoreInfo('Category ID',$f['category_id']);
        }

        // check if the default language category exists and get the id
        $shopcatnamelookup=$shopcatname->dsql();
        $shopcatid=$shopcatnamelookup
            ->where('id_parent',$lastshopcatid)
            ->where('name',(string)$defaulttitle)
            ->limit(1) // to ensure it will fetch all as we will only fetch one (mysql issue to always fetch all).
            ->getOne('category_id');

        if($shopcatid) {
          $shopcat->load($shopcatid); // only load in case of existing record
        } else {
          $shopcat->unload() // otherwise unload and save as new
              ->set('id_parent',$lastshopcatid)
              ->set('date_add',$q->expr('now()'))
              ->set('date_upd',$q->expr('now()'))
              ->save();
        }
        //handle the category_group to link the category to all the customer groups
        $shopcatgroup=$shopcat->ref('Prestashop_CategoryGroup');
        foreach($shopgroup as $row) {
          $shopcatgroup->tryLoadBy('id_group',$row['id_group'])->saveAndUnload();
        }
        // handle the category translations in the shop
        $shopcatlang=$shopcat->ref('Prestashop_CategoryLang');
        foreach($this->languages as $langiso => $langid) {
          $title=current($catxml->xpath('cat[@lang="'.$langiso.'"]/node['.$level.']'))?:$defaulttitle; // current is to get $title[0]
          $shopcatlang->tryLoadBy('id_lang',$langid);
          $shopcatlang->set('name',(string)$title); 
          $shopcatlang->set('link_rewrite',strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', (string)$title ), '-'))); 
          $shopcatlang->save();
        }
        
        $lastshopcatid = $shopcat->get('id_category');
        $level++;
      }
      // due to bug we cannot save the join, ok the bug is solved by $this->data=$this->dsql->data in Model_Table,
      // however you cannot use ->save() as it will not be possible to load again with condition CategoryShopID=-1
      $filter
          ->set('catshop_id',$lastshopcatid)
          ->set('margin_ratio',1)
          ->set('margin_amount',0)
          ->saveAndUnload();
      $i++;
      //if($i>4) break;
    }

    $this->nb_categories=$i;
    // rebuild category tree of the shop to nicely set the lpos and rpos again
    //    $shopcat->treeRebuild();
    return $this;
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
      //$m->set('id_color_default',0); 
      $m->set('id_tax_rules_group',$this->tax[floatval($product['tax'])]); 
      $m->set('active',1);
      $m->save();

      if( $this->version >= '1.5' ) {
        $productshop=$m->ref('Prestashop_ProductShop')->tryLoadAny();
        $productshop->set('id_tax_rules_group',$this->tax[floatval($product['tax'])]); 
        $productshop->set('price',$product['price']);
        $productshop->set('id_category_default',$product['catshop_id']); 
        $productshop->set('date_add',$product['entry_date']); 
        $productshop->set('date_upd',$m->dsql()->expr('now()')); 
        $productshop->set('active',1);
        $productshop->saveAndUnload();
      }

                
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
  function getShopPricelist() {
    return $this->add('Model_Prestashop_Product')->pricelist();
        
  }
}