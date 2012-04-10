<?php
class Model_Xcart extends Model_Shop {
  function init() {
    parent::init();
  }
  
  
  function import_categories() { 
      // get the mysql connection for this specific shop
      $this->api->db2=$this->api->add('DB')->connect($this->connection());
      // prepare model for shop category to add categories
      $shopcat=$this->add('Model_Xcart_Category');
      // prepare model to loop through the available categories to be imported (where shopid=-1)
      $cats=$this->add('Model_CategorySupplier');
      $cats->selectQuery(); // bug fix to get the model fields
      $cats->dsql->where('CategoryShop',$this->get('name'))->where('CategoryShopID',-1);
      // loop through the categories to be imported to the shop (not all, only needed ones)
      $i=0;
      foreach($cats as $cat) {
        $defaultcat=$cats->categoryByLang('nl');
        
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
          $shopcat->addCondition('parentid',$lastshopcatid)
            ->addCondition('category',(string)$title)
            ->tryLoadAny()
            ->save()
            ;
          $lastshopcatid = $shopcat->get('categoryid');
          // check if old xcart categoryid_path field is used
          if( !$shopcat->tree ) {
            if($shopcatpath) $shopcatpath.='/';
            $shopcatpath.=$lastshopcatid;
            $shopcat->set('categoryid_path',$shopcatpath)->save();
          }
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
    $shopcat->treeRebuild();
  }
  
  /*
  performance: 600 per 100 seconds
  images: 100 images in 15 seconds
  */
          
  function import() {
    $this->i=0;
    $this->i1=0;
    $this->i2=0;
  
    $filepath=$this->api->getConfig('supplier_image_path');
    $tmp=$this->api->getConfig('tmp');
    $ftproot=parse_url($this->ftproot().'/');
    $imagespath=$this->imagespath().'/';
    $thumbspath=$this->thumbspath().'/';
    $shopimport_category=$this->category_import();

    // get the mysql connection for this specific shop
    $this->api->db2=$this->api->add('DB')->connect($this->connection());
    
    // get the time from mysql shop database which will be used later to disable products not longer updated
    $dsql2=$this->api->db2->dsql();
    $now=$dsql2->field($dsql2->expr('now()'))->getOne();

    // get the extra field which is used to track which products are imported by shopimport
    $extra=$this->add('Model_Xcart_ExtraField');
    $extra->tryLoadAny();
    $extra->save();
    $fieldid=$extra->get('fieldid');

    // the product model for the target shop      
    $m=$this->add('Model_Xcart_Product');
    
    // prepare FTP connection for images
    $ftp=$this->add('FTP');
    $ftp->login($ftproot['host'],rawurldecode($ftproot['user']),$ftproot['pass']); 

    // prepare image actions to be ready to retreive image information
    $img=$this->add('Actions_Image');
    
    // prepare media model
    $media=$this->add('Model_Media');
    
    // traverse through the pricelist and import each product one by one 
    $pricelist = $this->ref('Pricelist');
    $pricelist->selectQuery(); // solves issue to get all fields and now it gets only applicable fields definined in model
    $i=0;
    foreach( $pricelist as $product ) {
  
      if($i>=0) {
      //echo 'product ['.$product['shop_productcode'].']';
      // set the product fields      
      $m->tryLoadBy('productcode',$product['shop_productcode']);
      $m->set('productcode',$product['shop_productcode']); 
      $m->set('product',$product['product_title']); 
      $m->set('weight',$product['weight']); 
      $m->set('descr',$pricelist->short_description()); 
      $m->set('fulldescr',$pricelist->specification()); 
      $m->set('avail',$product['stock']); 
      $m->set('add_date',strtotime($product['entry_date'])); 
      $m->set('list_price',$product['price']); 
      $m->save();
          
      // handle pricing
      $pricing=$m->ref('Xcart_Pricing');
      //$pricing->dsql()->do_delete()->debug();
      $pricing->tryLoadAny();
      $pricing->set('price',$product['price']);
      $pricing->save(); // do not use saveAndUnload as values are needed later!
        
      $quickprices=$m->ref('Xcart_QuickPrices');
      $quickprices->tryLoadAny();
      $quickprices->set('priceid',$pricing->get('priceid'));
      $quickprices->saveAndUnload();

      // handle category
      $category=$m->ref('Xcart_ProductCategory');
      $found=false;
      foreach( $category as $cat ) {
        if( $cat['categoryid'] == $product['shop_category_id'] ) {
          $found = true;
        } else {
          $category->delete();
        }
      }
      if( !$found ) {
        $category->set('categoryid',$product['shop_category_id'])->save();
      }
     
      // media file
      $filename='';
      $imagep=$m->ref('Xcart_ImageP');
      $imagep->tryLoadAny();
      $media_modified=$product['media'];
      // check if image available and not already uploaded
      
      if($media_modified and strtotime($media_modified) != $imagep->get('date')) {
        // get filename from media table
        $media->load($product['media_id']);
        $filename=$media->get('file');
        echo'file ['.$filename.']';
        $shopfilename=$pricelist->image();

        // overrule filename for development as we don't have the file library          
        if( $this->api->getConfig('mode')=='dev') {
          $filename='test.jpg';
        }

        // handle image
        //copy($filepath.$filename,$tmp.$shopfilename);
        $s=microtime(true);
        $img->resizeImage($filepath.$filename, $tmp.$shopfilename, 1024, 1024, 90, 'Thumb');
        //shell_exec('convert -define jpeg:size=2048x2048 "'.$filepath.$filename.'" -resize 1024x1024 "'.$tmp.$shopfilename.'"');
        $this->i1+=(microtime(true)-$s);
        if( $this->api->getConfig('mode')!='dev') {
          $ftp->cd($ftproot['path'].$imagespath)
            ->setSource($tmp.$shopfilename)
            ->setTarget($shopfilename)
            ->save();
        }
        // img action to get image info like widht height etc
        $img->setFile($filepath.$filename);
        $imagep->set('filename',$shopfilename);
        $imagep->set('image_path',$imagespath.$shopfilename);
        $imagep->set('image_x',$img->imgWidth());
        $imagep->set('image_y',$img->imgHeight());
        $imagep->set('image_size',$img->fileSize());
        $imagep->set('date',strtotime($media_modified));
        $imagep->set('md5',$img->fileMd5());
        $imagep->saveAndUnload();
        
        // handle thumbs
        if( $this->api->getConfig('mode')!='dev') {
          $this->i++;
          $s=microtime(true);
          $img->resizeImage($filepath.$filename, $tmp.$shopfilename, 140, 140, 90, 'Thumb');
          //shell_exec('convert -define jpeg:size=250x250 "'.$filepath.$filename.'" -thumbnail 125x125^ "'.$tmp.$shopfilename.'"');
          $this->i2+=(microtime(true)-$s);
          $ftp->cd($ftproot['path'].$thumbspath)
            ->setSource($tmp.$shopfilename)
            ->setTarget($shopfilename)
            ->save();
        }

        $img->setFile($filepath.$filename);
        $imaget=$m->ref('Xcart_ImageT');
        $imaget->tryLoadAny();
        $imaget->set('filename',$filename);
        $imaget->set('image_path',$thumbspath.$shopfilename);
        $imaget->set('image_x',$img->imgWidth());
        $imaget->set('image_y',$img->imgHeight());
        $imaget->set('image_size',$img->fileSize());
        $imaget->set('date',strtotime($media_modified));
        $imaget->set('md5',$img->fileMd5());
        $imaget->saveAndUnload();
        
        unlink($tmp.$shopfilename);
      }

      // handle quick flags
      $quickflags=$m->ref('Xcart_QuickFlags');
      $quickflags->tryLoadAny();
      if($filename) {
        $quickflags->set('image_path_T',$thumbspath.$shopfilename);
      }
      $quickflags->saveAndUnload();
      
      // handle extra field value to know it's a shopimport product
$s1=microtime(true);
            $extravalue=$m->ref('Xcart_ExtraFieldValue');
$s2=microtime(true);
      $extravalue->tryLoadBy('fieldid',$fieldid);
      $extravalue->set('value',$now);
      $extravalue->saveAndUnload();
//      $extravalue->destroy();
}

$s+=$s2-$s1;

      if( ($i / 100) == round($i/100) ) { 
        if(!isset($starttime)) {
         $starttime=microtime(true);
        }
        $mem[$i]=ini_get("memory_limit")."; ".round(memory_get_peak_usage() / (1024 * 1024),3). "M; ". round(memory_get_usage() / (1024 * 1024), 3). "M; ".round(microtime(true)-$starttime,3)."s; ".round($s,3)."s";
        $starttime=microtime(true);
        $s=0;
      }
      if( $i >= 1000000 ) break;
      $i++;
    }
/*    echo "<pre>";
    var_dump($mem);
    echo "</pre>";
    
    echo 'timing ['.$this->i.']['.$this->i1.']['.$this->i2.']';  
*/
        $this->nb_products=$i;
  }

}