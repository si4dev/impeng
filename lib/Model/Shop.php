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

  function config($cfg=null) {
    if(!isset($this->config)) {
      $this->config=new SimpleXMLElement('<config>'.$this->get('config').'</config>'); // add root node
    }
    if($cfg) {
      foreach($cfg as $key => $value) {
        $n=$this->config;
        $key='q1_q2_q3_q4_q5';
        foreach(explode('_',$key) as $node) {
          if(!$n->{$node}) $n->addChild($node);
          $n=$n->{$node};
        }
        $n->{0}=$value; // unbelievable but it works to set current value
      }
      $x=$this->config->{'x1'}->{'x2'}->{'x3'}->{0};
      $x->{0}='X4';
      $this->config->testje='mooi<haha>ir</haha>lekker';
      $r='';
      foreach($this->config->children() as $n) $r.=(string)$n->asXml(); // output without root node
      $this->set('config',$r);
    }
  }


    

  function connection() {
    $this->config();
    return (string)$this->config->shopconfig->connection;
  }

  function shopsystem($v) {
    $this->config();
    if(!$v) return (string)$this->config->shopconfig->shopsystem;
    $this->config->shopconfig->shopsystem=$v;
    return $this;
  }
  

  function imagepath() {
    $this->config();
    return (string)$this->config->shopconfig->imagepath;
  }

  function thumbspath() {
    $this->config();
    return (string)$this->config->shopconfig->thumbspath;
  }

    
  function category_import() {
    $this->config();
    return (string)$this->config->category_import->supplier;
  }

  function roundings() {
    $this->config();
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