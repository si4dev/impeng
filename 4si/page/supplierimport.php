<?php
class page_supplierimport extends Page {
  function init() {
    parent::init();


    if($supplier_id=$_GET['supplier']) {
      $sup=$this->add('Model_Supplier')->load($supplier_id);
      $this->add('H1')->set('Supplier: '.$sup['name']);
      $this->add('h2')->set('Import');
      
      if($_GET['do']!='media') {
        $sup->import($_GET['full']==='true');
      }
//      $sup->importMedia(5);
      
      
    } // end if supplier
  }
}
