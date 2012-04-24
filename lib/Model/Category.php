<?php
class Model_Category extends Model_Table {
  public $table='category';
  public $title_field='reference';
  function init() {
    parent::init();
    $this->hasOne('Supplier');
    $this->addField('reference');
    $this->addField('title');
  }

  // structure to create form Audio, Video, Image|Mounting solutions|Carts|Trolleys into  
  // <cat lang="nl"><node>Audio, Video, Image</node><node>Mounting solutions</node><node>Carts</node><node>Trolleys</node></cat>
  function category_title($cat) {
    $dom=new DOMDocument('1.0', 'UTF-8');
    $n=$dom->appendChild($dom->createElement('cat'));
    $n->setAttribute('lang','nl');
    foreach(explode('|',$cat) as $node) {
      // use createtextnode as it will escape xml better then value of createElement
      if($node!='-') {
        $n->appendChild($dom->createElement('node'))->appendChild($dom->createTextNode($node));
      }
    }
    $this->set('title',$dom->saveXml($n));
    return $this;
  }
}

/*
update category c inner join supplier s on (s.name = c.supplier) 
set c.supplier_id = s.id
where c.supplier_id =0
*/