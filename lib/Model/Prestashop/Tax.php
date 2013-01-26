<?php
class Model_Prestashop_Tax extends Model_Table2 {
  public $table='ps_tax_rules_group';
  public $id_field='id_tax_rules_group';
  public $title_field='id_tax_rules_group';
  function init() {
    parent::init();
    $this->debug();
    $rule=$this->join('ps_tax_rule.id_tax_rules_group','id_tax_rules_group');
    $tax=$rule->join('ps_tax.id_tax','id_tax');
    $tax->addField('rate');
    $country=$rule->join('ps_country.id_country','id_country');
    $country->addField('iso_code');
    $this->addCondition('iso_code','NL');
  }
}
  
  /*
    (select tr.id_tax_rules_group 
                      from ps_tax_rules_group trg inner join ps_tax_rule tr on (tr.id_tax_rules_group = trg.id_tax_rules_group) inner join  ps_country  c on (c.id_country  = tr.id_country)
                      where iso_code = 'NL' and tr.id_tax = t.id_tax limit 0,1)
  */