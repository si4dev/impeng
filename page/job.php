<?php

class Page_job extends Page{
	function init(){
		parent::init();

		$job = $this->add('Model_Job');

		if($job->check() == false){
			//processing import immediately
			$this->api->redirect($this->api->url('pricelist'), array('token'=>false));
		}
		else{
			//queue the job
			$s= $this->api->getShop();
			$shop_id = $s->get('id');
			$job->set('status', 'queued');
			$job->set('shop_id', $shop_id);
			$job->save();
		}
	}
}