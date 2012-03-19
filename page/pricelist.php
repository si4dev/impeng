<?php

class page_pricelist extends Page {
  function init() {
    parent::init();

    $this->add('H1')->set('Pricelist');
    if( $shop_id=$_GET['shop'] ) {
      $this->add('Text')->set('shop '.$shop_id);
      $shop = $this->add('Model_Shop');
      $shop->load($shop_id);
      $connection=$shop->connection();
      
      $filepath=$this->api->getConfig('supplier_image_path');
      $tmp=$this->api->getConfig('tmp');
      $ftproot=parse_url($shop->ftproot().'/');
      $imagespath=$shop->imagespath().'/';
      $thumbspath=$shop->thumbspath().'/';
      
      //$connection = array('mysql:host=vm08.shopimport.nl;dbname=nijtronics_xcart;charset=utf8','nijtronics_xcart','mybr3Da27');
      
      $this->api->db2=$this->api->add('DB')->connect($connection);
      
   

      
      
      
//      $g=$this->add('Grid');
      $pricelist = $shop->ref('Pricelist');

//     $g->setModel($pricelist);
//      $g->addFormatter('price','money');
//      $g->addPaginator(10);
      /*
      $text='';
      //print_r( $pricelist->getActualFields() );

 */     

      $m=$this->add('Model_Xcart_Product');
      
      $ftp=$this->add('FTP');
      $ftp->login($ftproot['host'],rawurldecode($ftproot['user']),$ftproot['pass']); 
      // print_r($ftp->dir());
      
      $img=$this->add('Actions_Image');
            
                        
      $pricelist->selectQuery(); // solves issue to get all fields and now it gets only applicable fields definined in model
      $i=0;
      
      foreach( $pricelist as $product ) {

        
        $m->tryLoadBy('productcode',$product['shop_productcode']);
        $this->add('P')->set('xcart productid ['.$m->get('productid').']');
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
          $media=$this->add('Model_Media');
          $media->load($product['media_id']);
          $filename=$media->get('file');
          $shopfilename=$product['shop_productcode'].'.jpg';

          // overrule filename for development as we don't have the file library          
          if( $this->api->getConfig('mode')=='dev') {
            $filename='test.jpg';
          }

          // handle image
          $this->add('P')->set('file: '.$filepath.$filename);
          copy($filepath.$filename,$tmp.$shopfilename);
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
          $this->add('P')->set('file: '.$filepath.$filename);

          
          //echo shell_exec('convert -define jpeg:size=250x250 '.$filepath.$filename.' -thumbnail 125x125^ -gravity center -extent 125x125 '.$tmp.$shopfilename);
          if( $this->api->getConfig('mode')!='dev') {
            echo shell_exec('convert -define jpeg:size=250x250 '.$filepath.$filename.' -thumbnail 125x125^ '.$tmp.$shopfilename);
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
          $imaget->set('date',strtotime($imedia_modified));
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
        
        // move to this in the future $m->import( $product );
        if( $i++ > 20 ) break; 

      }
      $t=$this->add('P')->set(str_repeat('.',$i));
    } else {
      $this->add('Text')->set('no shop selected');
    }
      
   
  }
    
}