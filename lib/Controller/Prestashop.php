<?php
class Controller_Prestashop extends AbstractController {
  protected $lang='nl';
  protected $stats=array(); // stats how many products are imported
  function init() {
    parent::init();
    $this->version='1.5';

    if(!isset($this->owner->platformCtrl)) {
      $this->owner->platformCtrl=$this;

      // use with setController on the Model_Shop
      // then the $this->owner is the Model_Shop

      $this->owner->addMethod('ftproot',array($this,'ftproot'));
      $this->owner->addMethod('connection',array($this,'connection'));

      unset($this->api->db2);
      if($connection=$this->owner->connection()) {
        $this->api->db2=$this->api->add('DB')->connect($connection);
      }
    }
  }

  //------------------------------------------------------------------------------------------------
  // prepare an extra column 'shopimport' in the product table

  function prepareProductShopimportField() {
    $p=$this->add('Model_Prestashop_Product');
    $db=$p->_dsql()->owner;
    $res=$db->query("show columns from {$p->table} like 'shopimport'")->fetch();
    if(!$res) $db->query("alter table {$p->table} ADD `shopimport` VARCHAR(250) NOT NULL DEFAULT '' AFTER `location`");
    return $this;
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
  function getShopCategories($id_category=null) {
    $defaultlangid=$this->add('Model_Prestashop_Lang')->loadBy('iso_code',$this->lang)->id;
    $cat=$this->add('Model_Prestashop_Category')->lang($defaultlangid);
    if(!$id_category) $id_category=$cat->getRoot();
    return $cat->load($id_category)->tree();
  }

  function getShopAttributes() {
    $defaultlangid=$this->add('Model_Prestashop_Lang')->loadBy('iso_code',$this->lang)->id;
    $attr=$this->add('Model_Prestashop_AttributeGroup')->lang($defaultlangid);
    return $attr;
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
    $filter->debug();
    foreach( $filter as $f) {

      // get all source titles in XML <cat lang='nl'><node>..</node><node>..</node>..</cat> format
      $catxml=$cat->load($f['source_category_id'])->getTitleXml();
      // find the default source category to be used to match and for defaults
      $defaultcatxml=$catxml->xpath('cat[@lang="'.$this->lang.'"]');

      if(!$defaultcatxml) {
        throw $this->exception('Shopassist: category title not available')
            ->addMoreInfo('Category ID',$f['source_category_id'])
            ->addMoreInfo('lang',$this->lang);
      }

      $level=1; // keep starting at ONE for category node xpath start at ONE (node[1])
      $lastshopcatid = $shopcat->getRoot(); // root category in prestashop
      // loop through the levels of one category path (like breadcrumb)
      foreach( current($defaultcatxml)->node as $defaulttitle ) { // current($defaultcatxml) is for the default language
        if( !(string)$defaulttitle ) {
          throw $this->exception('Shopassist: supplier title node empty')->addMoreInfo('Category ID',$f['source_category_id']);
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


      $target_category_id=$s->getShopCategories($lastshopcatid)->get('id'); // import the prestashop category into the impeng category table
      echo "==$target_category_id== ";
      $filter->debug();

      $filter
          ->set('target_category_id',$target_category_id)
          ->set('margin_ratio',1)
          ->set('margin_amount',0)
          ->saveAndUnload();
      $i++;
      if($i>4) break;
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

  protected function getLanguages() {
    // get languages used in this shop and store in array $this->lanuguages
    foreach($this->add('Model_Prestashop_Lang') as $l) {
      $this->languages[$l['iso_code']]=$l['id_lang'];
    }
  }





  //************************************************************************************************
  // import stats
  function getImportStats() {
    return $this->stats;
  }


  //************************************************************************************************
  // import product list to the prestashop
  function import() {
    $this->stats=array();
    $s=$this->owner;

    $s->set('import_start',$s->dsql()->expr('now()') )->save();
    $start=$s->get('import_start');

    // prepare product table with shopimport column which will track shopimport products
    $this->prepareProductShopimportField();

    $productList=$s->ref('ProductForPricelist');
    $productList->addCondition('target_category_ref','!=','');
    // Array ( [id] => 3132 [assortment_id] => 3 [assortment] => uwhulpmiddelisonderweg [productcode] => AL 200116 -s [source_product_id] => [source_product] => [title] => Alpine oordopjes [category_id] => 3220 [category] => Slapen [tax] => 21 [manufacturer] => [manufacturer_code] => [ean] => [weight] => 0 [info_modified] => 2012-12-18 17:37:48 [entry_date] => 2012-11-09 19:01:55 [last_checked] => 2012-12-18 17:37:48 [price] => 11.95 [stock] => 1 [watch_last_checked] => 2012-12-18 17:37:49 [cattitle] => Slapen [target_assortment_id] => 5 [prefix] => [target_category_id] => [keyword] => [filter_id] => 2941 [margin_ratio] => [margin_amount] => )

    // init prestashop models
    $m=$this->add('Model_Prestashop_Product')->addCondition('shopimport','!=',''); // the product model for the target shop
    $this->tax(); // get tax rules and cache in array
    $this->getLanguages(); // get languages and cache in array $this->languages
    $rounding=$s->rounding()->setOrder('from');
    $margin=$s->margin()->setOrder('from');

    // the loop to drill through all products
    $i=0;
    foreach( $productList as $product ) {

      // check for available tax, otherwise error
      if(!$this->tax[floatval($product['tax'])]) throw $this->exception('impeng: tax not available')->addMoreInfo('tax',$product['tax'])->addMoreInfo('product',$product['productcode']);

//      echo '<pre/>'.htmlspecialchars(print_r($product,false)).'</pre>';
      $m->tryLoadBy('reference',$product['productcode']);

      // hande manufacturer
      if($product['manufacturer']) {
        if($m['manufacturer']!==$product['manufacturer']) {
          $mfr=$m->ref('id_manufacturer')->tryLoadBy('name',$product['manufacturer']);
          if(!$mfr->loaded()) {
            $mfr->set('date_add',$start)->set('date_upd',$start)->set('active',1)->save();
          }
          $m->set('id_manufacturer',$mfr->id);
        }
      } else {
        $m->set('id_manufacturer','0');
      }

      // hande supplier
      if($m['supplier']!==$product['assortment_id']) { // use !== as one shop mfr can be null and other one empty
        $supplier=$m->ref('id_supplier')->tryLoadBy('name',$product['assortment_id']);
        if(!$supplier->loaded()) {
          $supplier->set('date_add',$start)->set('date_upd',$start)->set('active',1)->save();
        }
        $m->set('id_supplier',$supplier->id);
      }

      // calculate the sales price based on purchase price, rounding and margin
      //TODO: filter margin
      $price_si=$rounding->round($margin->marge($product['price'])*(1+$product['tax']/100));
      $price_se=round($price_si/(1+$product['tax']/100),5); // reason for rounding is that otherwise it might detect 'dirty' but it's not and it will update price every product while it's not changed due to nature of atk4

      // set more product fields
      $m->set('reference',$product['prefix'].$product['productcode']);
      $m->set('supplier_reference',$product['productcode']);
      $m->set('quantity',$product['stock']);
      $m->set('price',$price_se);
      $m->set('wholesale_price',$product['price']);
      $m->set('weight',$product['weight']);
      $m->set('ean13',$product['ean']);
      $m->set('location',$product['manufacturer_code']);
      $m->set('date_add',$product['entry_date']);
      $m->set('date_upd',$start);
      $m->set('id_category_default',$product['target_category_ref']);
      //$m->set('id_color_default',0); // not longer on prestashop 1.5
      $m->set('id_tax_rules_group',$this->tax[floatval($product['tax'])]);
      $m->set('active',1);
      //TODO:set with date of start import based on si server time, not prestashop server time to compare later
      $m->set('shopimport','recent');
      $m->save();

      // since prestashop 1.5 there is a table to link products to (multi)shops
      if( $this->version >= '1.5' ) {
        $productshop=$m->ref('Prestashop_ProductShop')->tryLoadAny();
        $productshop->set('id_tax_rules_group',$this->tax[floatval($product['tax'])]);
        $productshop->set('price',$product['price']);
        $productshop->set('id_category_default',$product['target_category_ref']);
        $productshop->set('date_add',$product['entry_date']);
        $productshop->set('date_upd',$start);
        $productshop->set('active',1);
        $productshop->saveAndUnload();
      }

      // handle title based on product id
      $productlang=$m->ref('Prestashop_ProductLang');
      foreach($this->languages as $langiso => $langid) {
        $productList->lang=$langiso;
        $productlang->tryLoadBy('id_lang',$langid);
        $productlang->set('id_lang',$langid);
        $productlang->set('description',$productList->getInfoLong());
        $productlang->set('description_short',$productList->getInfoShort());
        $productlang->set('name',$productList->getInfoTitle());
        $productlang->set('link_rewrite',$productList->rewrite());
        $productlang->set('meta_description',$productList->meta_description());
        $productlang->set('meta_keywords',$productList->meta_keywords());
        $productlang->set('meta_title',$productList->meta_title());
        $productlang->saveAndUnload();
      }

      // handle category
      $category=$m->ref('Prestashop_CategoryProduct');
      $found=false;
      foreach( $category as $cat ) {
        if( $cat['id_category'] == $product['target_category_ref'] ) {
          $found = true;
        } elseif ($cat['id_category'] != 1) {
          $category->delete();
        }
      }
      if( !$found ) {
        $category->set('id_category',$product['target_category_ref'])->saveAndUnload();
      }




      $i++;
      if( $i >= 2 ) break;
    }

    //TODO: disable products based on date_upd
    //TODO: disable products based on date_upd older then 100 days and deleted

  }








   /*
  performance: 600 per 100 seconds
  images: 100 images in 15 seconds
  */


  function ZZZimport() {
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

    $ftp->url($s->ftproot())
        ->login();
    $ftpimagespath=$ftp->path().'/img/p/'; // root path plus image relative path

    $ftp->path($ftpimagespath)->cd();
    $ftplist=$ftp->lsDates();

    // prepare image actions to be ready to retreive image information
    $img=$this->add('Actions_Image');

    // prepare image type model
    $imgtypes=$this->add('Model_Prestashop_ImageType')->addCondition('products',1)->getRows();
    $imgtypes[]=array('name'=>'','width'=>1024,'height'=>1024);
    // traverse through the pricelist and import each product one by one
    $pricelist = $s->ref('Pricelist');
    $pricelist->selectQuery(); // solves issue to get all fields and now it gets only applicable fields definined in model
    $i=0;
    foreach( $pricelist as $product ) {
      // load the prestashop product
      echo 'product ['.$product['shop_productcode'].']';
// TODO: here we need new structure
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
      if($media=$pricelist->ref('product_id')->ref('Media')->tryLoadAny()) {


        $media_modified=$media->get('file_modified');
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
              $filename=$media->get('file');
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
        $s=microtime(true);
        // set languages for this image
        $imagelang=$image->ref('Prestashop_ImageLang')->debug();
        foreach($this->languages as $langiso => $langid) {
          $imagelang->tryLoadBy('id_lang',$langid);
          $imagelang->saveAndUnload();
        }
        echo "T:".round(microtime(true)-$s,3)." ";
        /*
        // set shop for this image
        $imageshop=$image->ref('Prestashop_ImageShop');
        $imageshop->tryLoadBy('id_shop',1);
        $imageshop->saveAndUnload();
        */
      } // end if media

      $i++;
      if( $i >= 2 ) break;
    }

    $this->nb_products=$i;
  }


  // function to return the shop pricelist for external purposes
  function getShopPricelist() {
    return $this->add('Model_Prestashop_Product')->pricelist();

  }
}