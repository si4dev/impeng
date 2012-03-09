<?php
class Model_Xcart extends Model_Table {
    function init() {

    try{
        $this->db=$this->add('DB')->connect('mysql://xcart:xcart@localhost/xcart');
    }catch(Exception $e){
        $this->add('View_Error')->set($e->getMessage());
        Page::init();
        return;
    }
  
    parent::init();  
  }
}
