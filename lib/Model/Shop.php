<?php
class Model_Shop extends Model_Table {
  public $table='shop';
  function init() {
    parent::init();

    unset($this->config);
    $this->addField('name');
    $this->addField('config');
    $this->hasOne('User',null,'email'); 
    $this->hasMany('Pricelist');
    $this->hasMany('ProductForPricelist');
    $this->hasMany('CatLink');
    $this->hasMany('CatShop');
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

  function roundings() {
    if(!isset($this->config)) $this->config();
    $r=$this->add('Model_Rounding');
    $r->setSource('Array');
    foreach($this->config->roundings as $rounding) {
      $r->set('from',(string)$rounding->from)
        ->set('value',(string)$rounding->value)
        ->set('offset',(string)$rounding->offset)
        ->save();
    }
    
    
    foreach($r as $rr) print_r($rr);
    return $r;
  }
    

}   