<?php
class Model_Prestashop_Supplier extends Model_Table2 {
  public $table='ps_supplier';
  public $id_field='id_supplier';
  function init() {
    parent::init();
    $this->addField('name');
    $this->addField('date_add'); // set only on insert
    $this->addField('date_upd'); // set during update
    $this->addField('active'); // set 1 always, not only by default as unset supplier should be set again.

  }
}
