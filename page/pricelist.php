<?php

class page_pricelist extends Page {
  function init() {
    parent::init();

    $this->add('H1')->set('Pricelist');


    if(isset($_GET['key']) and $_GET['key']===$this->api->getConfig('key',null)) {
      $s=$this->add('Model_Shop')->loadBy('name',$_GET['shop']);
    } else {
      $si=$this->add('Controller_Shopimport');
      $s=$si->shop;
    }


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
      
    $this->add('Text')->set('shop '.$s->id);
      $s->pricelist();
    try{
  //    $s->import();
    } catch  (Exception_FTP $e){
       foreach($e->more_info as $key=>$value){
            $args[]=$key.'='.$value;
        }
        
      $this->add('View_Error')->set('Error '.$e->getMessage() . '['.implode(', ',$args).']' );
    }
      
      // temp solution for old database structure
      if($supplier=$s->category_import()) {
        
        $this->add('P')->set('Take over categories from supplier '.$supplier);
        //$supplier_id=$s->ref('SupplierLink')->tryLoadBy('supplier',$supplier)->get('supplier_id');
        
        $filter=$s->prepareFilter()
            ->addCondition('active', '>', '0');
        $filter
            ->getSupplier();
        $filter
            ->addCondition('Supplier',$supplier)
            ->addCondition('catshop',null);
        
        $s->importCategories( $filter );
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
      /*
      $s->ref('CatLink')->deleteAll();
        
        $sql="insert into catlink (id, shop_id, category_id, catshop_id, import, margin_ratio, margin_amount, timestamp)
select c.CategoryId, s.id, c.CategorySupplierID, c.CategoryShopID, c.CategoryImport, c.CategoryMarginRatio, c.CategoryMarginAmount, c.Timestamp
from shop s inner join tbltype_category c on (s.name = c.CategoryShop) 
where s.id=:shop";
        $cat = $this->api->db->query($sql,array('shop'=>$s->id));
      */

return; // phase out code after this line:
      // *** import categories ***
      $sc->import_categories();
      $this->add('P')->set('Imported categories: '.$sc->nb_categories);
      // *** import products ***
      $sc->import();
      $this->add('P')->set('Imported products: '.$sc->nb_products);
      
  }
}