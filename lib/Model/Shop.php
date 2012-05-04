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
    $this->hasMany('CatLink');
  }

  private function config() {
     $this->config=new SimpleXMLElement('<config>'.$this->get('config').'</config>');
  }

  function connection() {
    if(!isset($this->config)) $this->config();
    return (string)$this->config->shopconfig->connection;
  }

  function shopsystem() {
    if(!isset($this->config)) $this->config();
    echo (string)$this->config->shopconfig->shopsystem;
    return (string)$this->config->shopconfig->shopsystem;
  }


  function ftproot() {
    if(!isset($this->config)) $this->config();
    return (string)$this->config->shopconfig->ftproot;
  }

  function imagepath() {
    if(!isset($this->config)) $this->config();
    return (string)$this->config->shopconfig->imagepath;
  }

  function thumbspath() {
    if(!isset($this->config)) $this->config();
    return (string)$this->config->shopconfig->thumbspath;
  }

    
  function category_import() {
    if(!isset($this->config)) $this->config();
    return (string)$this->config->category_import->supplier;
  }

    

}   