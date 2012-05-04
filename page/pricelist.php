<?php

class page_pricelist extends Page {
  function init() {
    parent::init();

    $this->add('H1')->set('Pricelist');

    //      $g=$this->add('Grid');
    //  $pricelist = $this->ref('Pricelist');

//     $g->setModel($pricelist);
//      $g->addFormatter('price','money');
//      $g->addPaginator(10);
      /*
      $text='';
      //print_r( $pricelist->getActualFields() );
 */     

        
    if( $shop_id=$_GET['shop'] ) {
      $starttime=microtime(true);
      
      // 
      // -------------------------------------------------------------------------------------------------
      // set the script memory limit
      if(isset($memory_limit)) {
        ini_set("memory_limit", $memory_limit);
      }
      // -------------------------------------------------------------------------------------------------
      // set the script timeout and database timeeout
      $timeout='6000';
      if( isset($timeout) ) {
        set_time_limit($timeout);
        ini_set('default_socket_timeout', ini_get('max_execution_time'));
      }
      
      
      $this->add('Text')->set('shop '.$shop_id);
      $shop = $this->add('Model_Shop');
      $shop->load($shop_id);
      
      // temp solution for old database structure
      if($supplier=$shop->category_import()) {
        /* hold for the moment as it's old structure
        $this->add('P')->set('look for supplier categories to import ['.$supplier.']');
        $sql="insert ignore into  tbltype_category (categoryshop,categorysupplierid,categoryshopid)
          select '".$shop->get('name')."', c.`SupplierCategoryId`,-1 from tbltype_suppliercategory c 
          inner join tbldata_product p on (p.`ProductCategoryID` = c.`SupplierCategoryId`)
          inner join watch w on (w.`WatchProductID`=p.`ProductID`) 
          inner join supplier s on (s.`SupplierName`=p.`ProductSupplier`) 
          where w.`WatchLastChecked` >= s.`SupplierImportFull` and s.suppliername = '".$supplier."' 
          group by c.`SupplierCategoryId`";
        $cat = $this->api->db->query($sql);
        */
      }
      $shop->ref('CatLink')->deleteAll();
        
        $sql="insert into catlink (id, shop_id, category_id, catshop_id, import, margin_ratio, margin_amount, timestamp)
select c.CategoryId, s.id, c.CategorySupplierID, c.CategoryShopID, c.CategoryImport, c.CategoryMarginRatio, c.CategoryMarginAmount, c.Timestamp
from shop s inner join tbltype_category c on (s.name = c.CategoryShop) 
where s.id=:shop";
        $cat = $this->api->db->query($sql,array('shop'=>$shop->id));
        
      $shopsystem = ucwords($shop->shopsystem());
      $shop->unload(); // unload generic shop..
       // load specific shop system
      $shop = $this->add('Model_'.$shopsystem);
      $shop->load($shop_id);
      
      // *** import categories ***
//      $shop->import_categories();
      $this->add('P')->set('Imported categories: '.$shop->nb_categories);
      $starttime2=microtime(true);
      // *** import products ***
      $shop->load($shop_id)->import();
       $starttime3=microtime(true);
      $this->add('P')->set('Imported products: '.$shop->nb_products);
    } else {
      $this->add('Text')->set('no shop selected');
    }
    $this->add('P')->set('Took before ['.round($starttime2-$starttime,3).'] seconds');
    $this->add('P')->set('Took import ['.round($starttime3-$starttime2,3).'] seconds');
    $this->add('P')->set('Took after ['.round(microtime(true)-$starttime3,3).'] seconds');
  }
}