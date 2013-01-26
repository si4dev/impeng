<?php

class page_dbug extends Page{
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
		$gl->addPaginator(50);
		$gl->addColumn('Button', 'DeleteLog', 'Delete');
		$gl->addFormatter('error','wrap');
		$gl->addFormatter('command','wrap');

		$gm = $msg->add('Grid');
		$gm->setModel('logMsg');
		$gm->addPaginator(50);	
		$gm->addColumn('Button', 'DeleteMessage', 'Delete');

		if($_GET['DeleteMessage']){
			$m = $gm->getModel();
			$m->delete($_GET['DeleteMessage']);
			$this->js(null, array(
				$this->js()->univ()->successMessage("Delete Successfull"),
				$gm->js()->reload()
				) )->execute();
		}
		
		if($_GET['DeleteLog']){
			$m = $gl->getModel();
			$m->delete($_GET['DeleteLog']);
			$this->js(null, array(
				$this->js()->univ()->successMessage("Delete Successfull"),
				$gl->js()->reload()
				) )->execute();
		}
		
	}
}