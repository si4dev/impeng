<?php

class page_errors extends Page{
	function init(){
		parent::init();

		$on = $this->add('Button')->setLabel('DebugMode on');
		$off = $this->add('Button')->setLabel('DebugMode off');

		$url = $this->api->getDestinationURL($this->api->url('/'));
		$url->useAbsoluteURL();

		$on->js('click')->univ()->redirect($url, array('debugmode' => 'on'));
		$off->js('click')->univ()->redirect($url,  array('debugmode' => 'off'));

		$tt = $this->add('Tabs');
		$log = $tt->addTab('Log');
		$msg = $tt->addTab('Messages');

		$gl = $log->add('Grid');
		$gl->setModel('Log');
		$gl->addPaginator(500);

		$gm = $msg->add('Grid');
		$gm->setModel('logMsg');
		
	}
}