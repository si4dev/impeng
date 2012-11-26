<?php
// -------------------------------------------------------------------------------------------------
// error handling, generate events on all except notice.
// the default handler will be redirected also to capture errors to mysql
// when dbug switch is enabled then display_errors will be true later on

//error_reporting(E_ALL ^ E_NOTICE);
//ini_set("display_errors", 0);

// set the error handler to catch all the php errors
//libxml_use_internal_errors(true);
//register_shutdown_function('shutdown');

// -------------------------------------------------------------------------------------------------
// rethrow php errors 


if(!function_exists('exceptions_error_handler')){
function exceptions_error_handler($errno, $errstr, $errfile, $errline) {
  $errorType = Array (
                E_ERROR               => "Error",
                E_WARNING             => "Warning", // 8
                E_PARSE               => "Parsing Error",
                E_NOTICE              => "Notice",
                E_CORE_ERROR          => "Core Error",
                E_CORE_WARNING        => "Core Warning",
                E_COMPILE_ERROR       => "Compile Error",
                E_COMPILE_WARNING     => "Compile Warning",
                E_USER_ERROR          => "User Error",
                E_USER_WARNING        => "User Warning",
                E_USER_NOTICE         => "User Notice",
                4096                  => "Runtime Notice"
                );
  echo "[[$errno:$errstr]]";
  switch ($errno) {
    case 2:
      if(strpos($errstr,'mysql_connect')!==false)break;
    case 8:
      if(strpos($errstr,'Undefined offset') !==false)break;
      if(strpos($errstr,'Undefined index') !==false)break;
      if(strpos($errstr,'Undefined property') !==false)break;
      if(strpos($errstr,'in_array() expects parameter 2 to be array, null given') !==false)break;
    case 2048:
      if(strpos($errstr,'var: Deprecated') !==false)break;
      if(strpos($errstr,'Declaration of ') !==false)break;
      if(strpos($errstr,'Non-static method') !==false)break;
    case 8192:
      if(strpos($errstr,'is deprecated') !==false)break;
    default:
      throw new ErrorException($errorType[$errno].': ['.$errfile.':'.$errline.']'. $errstr, 0, $errno, $errfile, $errline);
      break;
  }
}
}    
//set_error_handler('exceptions_error_handler');

//throw new ErrorException('hello', 0, 12,'file',123);
	
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
  

	function caughtException($caller,$msg,$shift=0){
    $this->model->logMsg($msg,'exception');
    exit;
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
