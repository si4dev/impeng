<?php
class Model_Product extends Model_Table {
  public $table='product';
  function init() {
    parent::init();
    $this->hasOne('Supplier');
    $this->addField('productcode');
    $this->addField('title');
    $this->hasOne('Category');
    $this->addField('tax');
    $this->addField('manufacturer');
    $this->addField('manufacturer_code');
    $this->addField('ean');
    $this->addField('info');
    $this->addField('info_modified');
//    $this->hasMany('Media');
    $this->addField('entry_date');
    $this->addField('last_checked');
    

    $this->addHook('beforeInsert',function($m,$dsql){
      $dsql->set('entry_date',$dsql->expr('now()'));
    });
    $this->addHook('beforeLoad',function($o){ unset($o->infoField); });
  }
  
  
  
  function addInfo($type,$value,$lang='nl') {
    if(!isset($this->infoField) ) {
      $this->infoField=new DOMDocument('1.0', 'UTF-8'); // $this->info already used by atk4
    }
    $dom=$this->infoField;
    $n=$dom->appendChild($dom->createElement('info'));
    $n->setAttribute('type',$type);
    $n->setAttribute('lang',$lang);
    // createTextNode itself will translate &,",',<,> into &amp;,&quot;,$apos;,$lt;,&gt;
    // $n->appendChild($dom->createTextNode($value));

    if(!isset($this->xml)) {
      $this->xml=$this->add('XML');
    }
    if( $valueXml=$this->xml->tryToXml($dom,$value) ) {
      $n->appendChild($valueXml);
    }
 /*   
    if( $valueXml=$this->xml->htmlToXml($dom,$value) ) {
      $n->parentNode->insertBefore($valueXml , $dom->nextSibling );
    }
*/
    return $this;
  }


  function setInfo() {
    if(isset($this->infoField)) {
      $dom=$this->infoField;
      $info='';
      foreach($dom->childNodes as $n) {
        $info.=$dom->saveXml($n);
      }
      $this->set('info',$info);
    }
    return $this;
  }
}

/*
update product p inner join supplier s on (s.name = p.supplier) 
set p.supplier_id = s.id
where p.supplier_id =0
*/