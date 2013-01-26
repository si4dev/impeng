<?php
class Model_AssortmentLink extends Model_Table {
  public $table='assortment_link';
  public $title_field='name';
  function init() {
    parent::init();
    $this->hasOne('Assortment','target_assortment_id'); // the assortment itself, e.g. the shop
    $this->hasOne('Assortment','source_assortment_id'); // the source assortments, e.g. the suppliers
    $this->hasOne('Pricebook')->defaultValue(1);
    $this->addField('prefix');
    $this->addField('login');
	  $this->addField('is_owner')->type('boolean')->defaultValue(false);
  }  
}
