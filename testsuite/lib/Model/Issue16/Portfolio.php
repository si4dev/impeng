<?php

class Model_Issue16_Portfolio extends Model_Table {
    public $entity_code='issue16_portfolio';
    public $table_alias='issue16_po';
    function init(){
        parent::init();
        $this->addField("name");
        $this->addField("parent_portfolio_id")->refModel("Model_Issue16_Portfolio");
    }
}
