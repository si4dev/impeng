<?php
class Model_Prestashop_ProductJoin extends Model_Prestashop_Product {
  function init() {
    parent::init();
    $this->addField('id_manufacturer');
    $mfr=$this->join('ps_manufacturer.id_manufacturer','id_manufacturer','left','mfr');
    $mfr->addField('name');
    $mfr->addField('date_add')->defaultValue('now()');
    $mfr->addField('date_upd');
    $mfr->addField('active');
  }
}
    