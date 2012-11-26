<?php

class page_errors extends Page{
	function init(){
		parent::init();

		

		$url = $this->api->getDestinationURL($this->api->url('/'));
		$url->useAbsoluteURL();

		
		

		if($_GET['debugmode'] == 'on'){
			$off = $this->add('Button')->setLabel('DebugMode off');
			$off->js('click')->univ()->redirect($url,  array('debugmode' => 'off'));
		}
		else
		{
			$on = $this->add('Button')->setLabel('DebugMode on');
			$on->js('click')->univ()->redirect($url, array('debugmode' => 'on'));
		}

		$this->add('hr');
		
		

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