<?php
class page_supplierimport extends Page {
  function init() {
    parent::init();


    if($supplier_id=$_GET['supplier']) {
      $sup=$this->add('Model_Supplier')->load($supplier_id);
      $this->add('H1')->set('Supplier: '.$sup['friendly_name']);
      $this->add('h2')->set('Import');
      $sup->import_files($_GET['full']==='true');
    } // end if supplier
  }
}
