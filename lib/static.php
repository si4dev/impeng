<?php

if(!function_exists('mime_content_type')){
	function mime_content_type($filename)
	{
		/*  $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $fileMimeType = finfo_file($finfo, $filename) ;
        finfo_close($finfo); 		
		return $fileMimeType ; }*/
		

    /* $result = new finfo();

    if (is_resource($result) === true)
    {
    	return $result->file($filename, FILEINFO_MIME_TYPE);
    }
		return false;
	} */
	
		return 'image/png';
	}
	
};
    
 
		
		




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

                    if(ini_get('display_errors') == 1 || ini_get('display_errors') == 'ON')
                        echo "$str<br />\n";
                    if(ini_get('log_errors') == 1 || ini_get('log_errors') == 'ON')
                        error_log(" $errfile:$errline\n[".$errorType[$errno]."] ".strip_tags($errstr),0);
                    break;
            }
        }
    }
    set_error_handler("error_handler");

    /*
       };if(!function_exists('htmlize_exception')){
       function htmlize_exception($e,$msg){
//$e->HTMLize();
echo $e->getMessage()."<br>\n";
}
     */
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



/*
foreach(explode(':',get_include_path()) as $p) {
  
  if( realpath($p) == realpath(dirname(__FILE__))) {
    $foundMe=true;
  } elseif( isset($foundMe) ) {
      $f=$p.'/static.php';
      if( file_exists( $f ) ) {
        include $f; // it will actually include atk4/static.php
        break;
      }
  }
}
*/