<?php
class Controller_Model_Order extends AbstractController {
  function init() {
    parent::init();
  }
  
  
    
  function setOrder($field) {
    if($this->owner->table) {
      uasort($this->owner->table, 
        function ($a, $b) use ($field) {
            return strnatcmp($a[$field], $b[$field]);
        }
      );
    }
    return $this->owner;
  }
}