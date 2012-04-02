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
      $this->add('Text')->set('shop '.$shop_id);
      $shop = $this->add('Model_Shop');
      $shop->load($shop_id);
      
      // temp solution for old database structure
      if($supplier=$shop->category_import()) {
        $sql="insert ignore into  tbltype_category (categoryshop,categorysupplierid,categoryshopid)
          select '".$shop->get('name')."', c.`SupplierCategoryId`,-1 from tbltype_suppliercategory c 
          inner join tbldata_product p on (p.`ProductCategoryID` = c.`SupplierCategoryId`)
          inner join watch w on (w.`WatchProductID`=p.`ProductID`) 
          inner join supplier s on (s.`SupplierName`=p.`ProductSupplier`) 
          where w.`WatchLastChecked` >= s.`SupplierImportFull` and s.suppliername = 'gistron' 
          group by c.`SupplierCategoryId`";
        $cat = $this->api->db->query($sql);
      }
      $shopsystem = ucwords($shop->shopsystem());
      $shop->unload(); // unload generic shop..
      
       // load specific shop system
      $shop = $this->add('Model_'.$shopsystem);
      $shop->load($shop_id)->import_categories();
      $shop->load($shop_id)->import();
      $this->add('P')->set('Imported products: '.$shop->nb_products);
    } else {
      $this->add('Text')->set('no shop selected');
    }
  }
}