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

    //remove old hooks before add the new hooks
    $this->api->removeHook('caught-exception');

    $this->api->addHook('caught-exception',array($this,'caughtException'), array(), 5);
    

   
  }



	function caughtException($caller,$e){
      $msg = $e->getMessage();
      $msg .= $this->backtrace($e->shift,$e->getTrace());

      $this->model->logMsg($msg,'Exception');   
	}  


  function backtrace($sh=null,$backtrace=null){
    $output = "\n";
    $output .= "Stack trace:\n";
    if(!isset($backtrace)) $backtrace=debug_backtrace();

    $n=0;
    foreach($backtrace as $bt){
      $n++;
      $args = '';
      if(!isset($bt['args']))continue;
      foreach($bt['args'] as $a){
        if(!empty($args)){
          $args .= ', ';
        }
        switch (gettype($a)) {
          case 'integer':
          case 'double':
            $args .= $a;
            break;
          case 'string':
            $a = htmlspecialchars(substr($a, 0, 128)).((strlen($a) > 128) ? '...' : '');
            $args .= "\"$a\"";
            break;
          case 'array':
            $args .= "Array(".count($a).")";
            break;
          case 'object':
            $args .= "Object(".get_class($a).")";
            break;
          case 'resource':
            $args .= "Resource(".strstr($a, '#').")";
            break;
          case 'boolean':
            $args .= $a ? 'True' : 'False';
            break;
          case 'NULL':
            $args .= 'Null';
            break;
          default:
            $args .= 'Unknown';
        }
      }

      if(($sh==null && strpos($bt['file'],'/atk4/lib/')===false) || (!is_int($sh) && $bt['function']==$sh)){
        $sh=$n;
      }

      $output .= dirname($bt['file'])."/".basename($bt['file'])."";
      $output .= "{$bt['line']}";
      $name=(!isset($bt['object']->name))?get_class($bt['object']):$bt['object']->name;
      if($bt['object'])$output .= $name;else $output.="";
      $output .= ">".get_class($bt['object'])."{$bt['type']}{$bt['function']}($args)\n";
    }
    $output .= "\n";
    return $output;
  }
   
}
