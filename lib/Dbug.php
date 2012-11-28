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
     $error = $e->getMessage();
     $error .= $this->backtrace($e->shift, $e->getTrace());
     $this->model->set('error',$error);     
        
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
