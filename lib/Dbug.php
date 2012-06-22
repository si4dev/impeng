<?php

class Dbug extends AbstractController {
  
  function init() {
    parent::init();

    // ensure to have database
    $this->setModel('Log');
    $this->model->start();
    //$this->model->end();

		$this->api->addHook('caught-exception',array($this,'caughtException'));
		$this->api->addHook('output-fatal',array($this,'outputFatal'));
		$this->api->addHook('output-warning',array($this,'outputWarning'));
		$this->api->addHook('output-info',array($this,'outputInfo'));
		$this->api->addHook('output-debug',array($this,'outputDebug'));
  }
  
  	function logLine($msg,$shiftfunc=null,$severity='info',$trace=null){
      // $this->log_output
      
      $this->model->logMsg($msg,$severity);
	}

  
	function caughtException($caller,$msg,$shift=0){
    $this->model->logMsg($msg,'exception');
	}  
	function outputFatal($caller,$msg,$shift=0){
    $this->model->logMsg($msg,'fatal');
	}  
	function outputWarning($caller,$msg,$shift=0){
    $this->model->logMsg($msg,'warning');
	}  
	function outputInfo($caller,$msg,$shift=0){
    $this->model->logMsg($msg,'info');
	}  
	function outputDebug($caller,$msg,$shift=0){
    $this->model->logMsg($msg,'debug');
	}  
}
