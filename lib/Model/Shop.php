<?php
class Model_Shop extends Model_Table {
  public $table='shop';
  function init() {
    parent::init();

    unset($this->config);
    $this->addField('name');
    $this->addField('config');
    $this->hasOne('User',null,'login'); 
    $this->hasMany('Pricelist');
  }

  private function config() {
     $this->config=new SimpleXMLElement('<config>'.$this->get('config').'</config>');
  }

  function connection() {
    if(!isset($this->config)) $this->config();
    return (string)$this->config->shopconfig->connection;
  }

  function ftproot() {
    if(!isset($this->config)) $this->config();
    return (string)$this->config->shopconfig->ftproot;
  }

  function imagespath() {
    if(!isset($this->config)) $this->config();
    return (string)$this->config->shopconfig->imagespath;
  }

  function thumbspath() {
    if(!isset($this->config)) $this->config();
    return (string)$this->config->shopconfig->thumbspath;
  }

    

}   