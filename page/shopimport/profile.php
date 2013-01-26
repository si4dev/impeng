<?php 

Class Page_shopimport_profile extends Page {
	function init(){
	  parent::init();
    $shop=$this->api->getShop();
		$f=$this->add('Form');
		$d=$f->addField('line', 'domain')->setCaption('Domain :');
		$d->set($shop->config('profile/domain'));		
		$d->addClass('span3');
		
		$e=$f->addField('line', 'email')->setCaption('Email :');
		$e->set($shop->config('profile/email'));
		$e->addClass('span3');
		
		$f->addSubmit();
		
		if($f->isSubmitted()){
			$shop->config('profile/domain', $f->get('domain'));
			$shop->config('profile/email', $f->get('email'));
      
			$shop->save();
			$f->js()->univ()->successMessage('Changes Saved')->execute();
		}
	}
}