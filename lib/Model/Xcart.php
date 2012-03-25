<?php
class Model_Xcart extends Model_Shop {
  function init() {
    parent::init();
  }
  
  
  function import_categories() { 
      // get the mysql connection for this specific shop
      $this->api->db2=$this->api->add('DB')->connect($this->connection());
      $this->add('Text')->set('tenstead.');
      $shopcat=$this->add('Model_Xcart_Category');
      
      $cats=$this->add('Model_CategorySupplier');
      $cats->selectQuery();
      $cats->dsql->where('CategoryShop','pcfast2')->where('CategoryShopID',-1);
      foreach($cats as $cat) {
        echo $cat['CategorySupplierID'].$cat['SupplierCategoryTitle'].'<br/>';
        
        $defaultcat=$cats->categoryByLang('nl');
        
        $this->add('HTML')->set('"<pre>".htmlentities($defaultcat->asXml())."</pre>"');
        
        $level = 1;
        $parent = 0;
        $need=false;
        foreach( $defaultcat->node as $title ) {
          $dsql=clone $shopcat->dsql;
          $shopcat->addCondition('parentid',$parent)
            ->addCondition('category',(string)$title)
            ->tryLoadAny();
          if(!$need) $need=!$shopcat->loaded();
          $shopcat->set('parentid',$parent)
            ->set('category',(string)$title)
            ->save();
          $shopcatid = $shopcat->get('categoryid');
          $shopcat->dsql=$dsql; // restore dsql as we added two conditions
          $level++;
        }
        // due to bug we cannot save the join, ok the bug is solved by $this->data=$this->dsql->data in Model_Table,
        // however you cannot use ->save() as it will not be possible to load again with condition CategoryShopID=-1
        $cats->set('CategoryShopID',$shopcatid)->saveAndUnload();
      }


    $shopcat->treeRebuild();
  }
  
      
  function import() {
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
        $pricing->save();
        
        $quickprices=$m->ref('Xcart_QuickPrices');
        $quickprices->tryLoadAny();
        $quickprices->set('priceid',$pricing->get('priceid'));
        $quickprices->save();
        
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
          $shopfilename=$product['shop_productcode'].'.jpg';

          // overrule filename for development as we don't have the file library          
          if( $this->api->getConfig('mode')=='dev') {
            $filename='test.jpg';
          }

          // handle image
          //copy($filepath.$filename,$tmp.$shopfilename);
          shell_exec('convert -define jpeg:size=2048x2048 '.$filepath.$filename.' -resize 1024x1024 '.$tmp.$shopfilename);
          if( $this->api->getConfig('mode')!='dev') {
            $ftp->cd($ftproot['path'].$imagespath)
              ->setSource($tmp.$shopfilename)
              ->setTarget($shopfilename)
              ->save();
          }

          // img action to get image info like widht height etc
          $img->setFile($filepath.$filename);
          $imagep->set('filename',$filename);
          $imagep->set('image_path',$imagespath.$shopfilename);
          $imagep->set('image_x',$img->imgWidth());
          $imagep->set('image_y',$img->imgHeight());
          $imagep->set('image_size',$img->fileSize());
          $imagep->set('date',strtotime($media_modified));
          $imagep->set('md5',$img->fileMd5());
          $imagep->save();
          
          // handle thumbs
          if( $this->api->getConfig('mode')!='dev') {
            //echo shell_exec('convert -define jpeg:size=250x250 '.$filepath.$filename.' -thumbnail 125x125^ -gravity center -extent 125x125 '.$tmp.$shopfilename);
            shell_exec('convert -define jpeg:size=250x250 '.$filepath.$filename.' -thumbnail 125x125^ '.$tmp.$shopfilename);
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
          $imaget->save();
          
          unlink($tmp.$shopfilename);
        }

        // handle quick flags
        $quickflags=$m->ref('Xcart_QuickFlags');
        $quickflags->tryLoadAny();
        if($filename) {
          $quickflags->set('image_path_T',$thumbspath.$shopfilename);
        }
        $quickflags->save();
        
        // handle extra field value to know it's a shopimport product
        $extravalue=$m->ref('Xcart_ExtraFieldValue');
        $extravalue->tryLoadBy('fieldid',$fieldid);
        $extravalue->set('value',$now);
        $extravalue->save();
                
        $i++;
        if( $i >= 20 ) break; 
      }
    $this->nb_products=$i;
  }
}