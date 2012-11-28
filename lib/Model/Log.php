<?php
class Model_Log extends Model_Table {
  public $table='log';
  public $title_field='command';
  public $verbose_level=0;
  function init() {
    parent::init();
    
    $this->addField('start_date');
    $this->addField('started_by');
    $this->addField('command');
    $this->addField('completed');
    $this->addField('duration');
    $this->addField('error')->defaultValue('');
    $this->addField('memory');
    $this->addField('path');
    $this->addField('label');
    $this->addField('version');
    $this->addField('verbose_level');
    $this->hasMany('LogMsg');
  }
  
  private function memory() {
		return ini_get("memory_limit")."; ".round(memory_get_peak_usage() / (1024 * 1024),3). "M; ". round(memory_get_usage() / (1024 * 1024), 3). "M";
  }
  
  function start($dbug='') {
    $this->starttime=microtime(true);
    
    $dbug=($_GET['dbug']?:$dbug);
    
	  list($label,$this->verbose_level)=explode(':',$dbug);
        
    $this->set('start_date',$this->_dsql()->expr('now()'));
    $this->set('started_by',$_SERVER["REMOTE_ADDR"]);
    $this->set('command',$_SERVER["QUERY_STRING"]);
    $this->set('path',$_SERVER["SCRIPT_FILENAME"]);
    $this->set('label',$label);
    $this->set('verbose_level',$this->verbose_level);
    $this->save();
  }
 
  function logMsg($msg,$severity) {
    $this->ref('LogMsg')->set('message',$msg)->set('severity',$severity)->saveAndUnload();
  }
      
  function end() {
    $this->set('duration',round(microtime(true)-$this->starttime,3));
    $this->set('memory',$this->memory());
    $this->set('completed',1);
    $this->saveAndUnload();
  }


}

class Model_LogMsg extends Model_Table {
  public $table='logmsg';
  public $title_field='message';
  public $verbose_level=0;
  function init() {
    parent::init();
    
    $this->addField('log_id');
    $this->addField('message');
    $this->addField('severity');
  }
}
