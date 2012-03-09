<?php

class Model_Issue16_Bet extends Model_Table {
    public $entity_code='issue16_bet';
    public $table_alias='issue16_po';
    function init(){
        parent::init();
        $this->addField("name");
        $this->addField("portfolio_id")->refModel("Model_Issue16_Portfolio");
    }
}
