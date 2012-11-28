<?php

function exceptions_error_handler($severity, $message, $filename, $lineno, $vars) {
	throw new ErrorException($message, 0, $severity, $filename, $lineno);
}


if(!function_exists('error_handler')){
    function error_handler($errno, $errstr, $errfile, $errline){
        $errorType = Array (
                E_ERROR               => "Error",
                E_WARNING             => "Warning",
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

        if((error_reporting() & $errno)!=0) {
            $errfile=dirname($errfile).'/<b>'.basename($errfile).'</b>';
            $str="<font style='font-family: verdana;  font-size:10px'><font color=blue>$errfile:$errline</font> <font color=red>[$errno] <b>$errstr</b></font></font>";

            switch ($errno) {
                case 2:
                    if(strpos($errstr,'mysql_connect')!==false)break;
                case 8:
                    if(strpos($errstr,'Undefined offset')===0) break;
                    if(strpos($errstr,'Undefined index')===0) break;
                    if(strpos($errstr,'Undefined property')===0) break;
                case 2048:
                    if(strpos($errstr,'var: Deprecated')===0) break;
                    if(strpos($errstr,'Declaration of ')===0) break;
                    if(strpos($errstr,'Non-static method')===0) break;
                case 8192:
                    if(strpos($errstr,'is deprecated')!==false) break;
                default:
                    throw new ErrorException($errorType[$errno].': ['.$errfile.':'.$errline.']'. $errstr, 0, $errno, $errfile, $errline);
                    break;
            }
        }
    }
    set_error_handler("error_handler");
};


register_shutdown_function('handleShutdown');

function handleShutdown() {
  $error = error_get_last();
  if($error !== NULL){
      $info = "[SHUTDOWN] file:".$error['file']." | ln:".$error['line']." | msg:".$error['message'] .PHP_EOL;
      echo ' FATALTJE '.$info;
  }
}
	

include realpath('.').'/atk4/lib/static.php';

