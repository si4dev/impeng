<?php
	
// [1] run $this->add('Dbug') in Frontend BEFORE parent::init() to get correct sequence of caughtException hook
//     as Logger.php will exit and then Dbug hook would not be processed
// [2] ensure to have a database connection before calling this class as it uses the Log model 

class Dbug extends AbstractController {

  public $logmsg = '';
  
  function init() {
    parent::init();

    // set and start model
    $this->setModel('Log')->start();

    //remove old hooks before add the new hooks
    $this->api->removeHook('caught-exception');

    $this->api->addHook('caught-exception',array($this,'caughtException'), array(), 5);
  }

  function caughtException($caller,$e){
    $i=0;
    $message='';
    do {
      $message .= "----------\r";
      $message .= 'Error :'.$e->getMessage()."\r";
      $message .= 'Code :'.$e->getCode()."\r";
      $message .= 'File :'.$e->getFile()."\r";
      $message .= 'Line :'.$e->getLine()."\r";
      $message .= 'Trace :'.$e->getTraceAsString()."\r";
      $message .= 'LIBXML :'.print_r(libxml_get_errors(), true)."\r";
      $i++;
    } while( $e = $e->getPrevious() );
    
    $message = $i." Exception(s)\r" . $message;

    $this->model->set('error',$message);     
  }  

  // -----------------------------------------------------------------------------------------------
  // on descrturct log the duratoin and (max) memory used
  function __destruct(){
    $this->model->end();
    parent::__destruct();
  }

	function set($msg){        
    $this->logmsg .= $msg;    
	}

  function addMoreInfo($key, $value){
    $this->logmsg .= ' ';
    $this->logmsg .= $key.': '.$value;
    $this->model->logMsg($this->logmsg, 'infos');
  } 
   
}
