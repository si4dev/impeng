<?php

class page_demo extends Page {
  function init() {
    parent::init();


    $sql="CREATE TABLE IF NOT EXISTS `demo` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `productcode` varchar(250) NOT NULL DEFAULT '',
      `price` double NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
//    $q=$this->api->db->query($sql);

    $m=$this->add('Model_Demo');
    $m->tryLoadBy('productcode','123');
    $m->set('price',1000);
    $m->save();
    $m->tryLoadBy('productcode','124');
    $m->set('price',1001);
    $m->save();
  }
}

class Model_Demo extends Model_Table {
  public $table='demo';
  function init() {
    parent::init();
    $this->debug();
    $this->addField('productcode');
    $this->addField('price');
  }
}
