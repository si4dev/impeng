<?php
class page_shopconfig extends Page {
  function init() {
    parent::init();

/*      
    $f=$this->add('Form');
$f->addField('line','name');
      $f->addField('dropdown','shopsystem')->setValueList(array('prestashop','opencart','xcart','magento','oscommerce'));
      $f->addField('line','ftp');
      $f->addField('line','web');
$f->addSubmit('Greet Me');
if($f->isSubmitted()){
    $f->js()->univ()->alert('Hello, '.
            $f->get('name'))->execute();
}
  */  
    
    if($shop_id=$_GET['shop_id']) {
      $this->api->stickyGET('shop_id'); // very important as otherwise shop_id is lost on next call to this form
      $s=$this->add('Model_Shop')->load($shop_id);
      if($shopsystem = ucwords($s->shopsystem())) {
        $sc=$s->setController($shopsystem);
      }
      $f=$this->add('Form');
      
      $f->addField('dropdown','shopsystem')->setValueList(array('prestashop','opencart','xcart','magento','oscommerce'));
      $f->addField('line','shopconfigQ_ftprootQ');
     // $f->addField('line','web');
      $f->addSubmit();
      if($f->isSubmitted()){
        $this->api->stickyForget($shop_id);
        $s->setConfig($f->get());
        $s->save();
        $f->js()->univ()->alert('Saved '.print_r($f->get(),true))->execute();
      }

    }
  }
}
