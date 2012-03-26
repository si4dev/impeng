<?php
class Model_Table2 extends Model_Table {
<<<<<<< HEAD
  function init() {
    parent::init();  
  }

  function initQuery(){
    $this->dsql=$this->api->db2->dsql();
    $table=$this->table?:$this->entity_code;
    if(!$table)throw $this->exception('$table property must be defined');
    $this->dsql->table($table,$this->table_alias);
    $this->dsql->default_field=$this->dsql->expr('*,'.
        $this->dsql->bt($this->table_alias?:$table).'.'.
        $this->dsql->bt($this->id_field))
        ;
  }
=======
    function init() {
/*
    try{
        $this->db=$this->add('DB')->connect('mysql://xcart:xcart@localhost/xcart');
    }catch(Exception $e){
        $this->add('View_Error')->set($e->getMessage());
        Page::init();
        return;
    }
*/  
    parent::init();  
  }

    function initQuery(){
        $this->dsql=$this->api->db2->dsql();
        $table=$this->table?:$this->entity_code;
        if(!$table)throw $this->exception('$table property must be defined');
        $this->dsql->table($table,$this->table_alias);
        $this->dsql->default_field=$this->dsql->expr('*,'.
            $this->dsql->bt($this->table_alias?:$table).'.'.
            $this->dsql->bt($this->id_field))
            ;
    }
>>>>>>> 8b4bd199e8d18e7240ccba6ba1497a1e165bdeb6
  
}
