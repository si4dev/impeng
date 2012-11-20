<?php
class page_shopconfig extends Page {
  function init() {
    parent::init();


    if($shop_id=$_GET['shop']) {
      $this->api->stickyGET('shop'); // very important as otherwise 'shop' is lost on next call to this form
      $s=$this->add('Model_Shop')->load($shop_id);
      $this->add('H1')->set('Shop: '.$s['name']);
      $this->add('h2')->set('Config');
      if($shopsystem = ucwords($s->shopsystem())) {
        $sc=$s->setController($shopsystem);
      } 
      $f=$this->add('Form');
      $platforms=array('prestashop','opencart','xcart','magento','oscommerce');
      $f->addField('dropdown','shopsystem')->setValueList(array_combine($platforms,$platforms));
	  if(isset($sc)) $sc->shopconfig($f); // show specific shopsystem fields and also arange to store it

      $f->addSubmit();
      if($f->isSubmitted()){
        $this->api->stickyForget($shop_id);
        $s->shopsystem($f->get('shopsystem')); // store shopsystem
        $s->save();
        $action1=$f->js()->univ()->successMessage('Opgeslagen');
        $action2=$f->js()->reload(); 
        $f->js(null,array($action1,$action2))->execute();
        $this->api->redirect($p); // in case of no js

        //$f->js()->univ()->alert('Saved '.print_r($f->get(),true))->execute();
      }

      $this->add('h2')->set('Suppliers');
      $this->add('CRUD')->setModel($s->ref('SupplierLink'),array('supplier_id','prefix','pricebook_id', 'is_owner'),array('supplier','prefix','pricebook'));

    } // end if shop
  }
}
