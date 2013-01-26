<?php
class Model_Prestashop_Manufacturer extends Model_Table2 {
  public $table='ps_manufacturer';
  public $id_field='id_manufacturer';
  function init() {
    parent::init();
    $this->debug();
    $this->addField('name');
    $this->addField('date_add')->defaultValue('now()'); // set only on insert
    $this->addField('date_upd'); // set during update
    $this->addField('active'); // set 1 always, not only by default as unset mfg should be set again.
    // $this->hasMany('Prestashop_ManufacturerLang'); // does work without
  }


}
