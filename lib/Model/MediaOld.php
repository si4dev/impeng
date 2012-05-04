<?php
class Model_MediaOld extends Model_Table {
  public $table='tbldata_media';
  public $id_field='MediaID';
  
  function init() {
    parent::init();

    $this->addField('MediaProductID');
    $this->addField('MediaFileDir');
    $this->addField('MediaFileName');
    $this->addField('MediaFileExt');
    $this->addField('MediaWidth');
    $this->addField('MediaHeight');
    $this->addField('MediaFileModified');
    $this->addExpression('file')->set("concat(MediaFileDir,'/',MediaFileName,'.',MediaFileExt)");
    $this->hasMany('Pricelist');
  }

    
    

}   