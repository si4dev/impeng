<?php
class Page_Shopimport_Import extends Page {
  function init() {
    parent::init();

  $s=$this->api->getShop();

			$b=$this->add('Button');
			$b->setLabel('Pricelist');

			// TODO make a job queue so no large jobs can be done simultaniously and would kill the server resources
      // $url = $this->api->getDestinationURL('job');
			$url = $this->api->getDestinationURL('pricelist');

			$b->js('click')->univ()->redirect($url);
			/* going directly to the page pricelist
			if($b->isClicked()) {
		 //     echo 'test';
			  $this->js()->univ()->location($this->api->getDestinationURL(
								'pricelist',array('token'=>false)))->execute();
			}*/
  }
}