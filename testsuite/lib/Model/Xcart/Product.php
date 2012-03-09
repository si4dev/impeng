<?php
class Model_Xcart_Product extends Model_Table {
  public $table='xcart_products';
  public $id_field='productid';
  function init() {
        $this->db=$this->add('DB')->connect('mysql://xcart:xcart@localhost/xcart');
  
    $this->addField('productcode');
    $this->addField('product');
    $this->addField('list_price');
  
    parent::init();
  }
  
  function import( $product ) {
   
    var_dump( $product ); 

    
    
        echo '---------------<br/><br/><br/>';
  }
}