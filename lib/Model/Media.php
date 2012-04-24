<?php
class Model_Media extends Model_Table {
  public $table='media';
  
  function init() {
    parent::init();

    $this->hasOne('Product');
    $this->addField('file_dir');
    $this->addField('file_name');
    $this->addField('file_ext');
    $this->addField('width');
    $this->addField('height');
    $this->addField('file_modified');
    $this->addExpression('file')->set("concat(file_dir,'/',file_name,'.',file_ext)");
  }

    
    

}   