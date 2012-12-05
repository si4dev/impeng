<?php
class Page_Shopimport_Import extends Page {
  function init() {
    parent::init();
	
	$user = $this->api->auth->model;
		
	$link = $this->add('Model_SupplierLink');
	$slink = $link->tryLoadBy('shop_id', $this->api->recall('shop_id')); 
    if($slink->Loaded()){
	//verify if user can import
		if($slink['is_owner'] == true){
			$b=$this->add('Button');
			$b->setLabel('Pricelist');

			$url = $this->api->getDestinationURL('job');

			$b->js('click')->univ()->redirect($url);
			/* going directly to the page pricelist
			if($b->isClicked()) {
		 //     echo 'test';
			  $this->js()->univ()->location($this->api->getDestinationURL(
								'pricelist',array('token'=>false)))->execute();
			}*/
		}
	}
  }
}