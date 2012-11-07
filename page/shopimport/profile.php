<?php 

Class Page_shopimport_profile extends Page {
	function init(){
	  parent::init();
		$si=$this->add('Controller_Shopimport');
		$shop=$si->shop;
		
		$f=$this->add('Form');
		$d=$f->addField('line', 'domain')->setCaption('Domain :');
		$d->set($shop->shopconfig('domain'));		
		$d->addClass('span3');
		
		$e=$f->addField('line', 'email')->setCaption('Email :');
		$e->set($shop->shopconfig('email'));
		$e->addClass('span3');
		
		$f->addSubmit();
		
		if($f->isSubmitted()){
			$shop->shopconfig('domain', $f->get('domain'));
			$shop->shopconfig('email', $f->get('email'));
			$shop->save();
			$f->js()->univ()->successMessage('Changes Saved')->execute();
		}
	}
}