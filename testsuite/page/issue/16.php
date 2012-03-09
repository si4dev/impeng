<?php
class page_issue_16 extends Page {
    // http://github.com/atk4/atk4/issues/16 <-- report bug here
    // bug test-suite

    // reproduce bug here

    function initMainPage(){
        $c = $this->add("CRUD");
        $c->setModel("Issue16_Portfolio");
        if ($c->grid){
            $c->grid->addColumn("expander", "sub", array("descr" => "Sub portfolios"));
        }
    }
    function page_sub(){
        $this->api->stickyGET("id");
        $c = $this->add("CRUD");
        $id = $_GET["id"];
        $m = $this->add("Model_Issue16_Portfolio")->setMasterField("parent_portfolio_id", $id);
        $c->setModel($m);
        if ($c->grid){
            $c->grid->addColumn("expander", "bets");
        }
    }
    function page_sub_bets(){
        $this->api->stickyGET("id");
        $c = $this->add("CRUD");
        $m = $this->add("Model_Issue16_Bet")->setMasterField("portfolio_id", $_GET["id"]);
        $c->setModel($m);
    }
}
