<?php
class Model_Product extends Model_Table {
  public $table='product';
  public $title_field='productcode';
  function init() {
    parent::init();
    $this->hasOne('Supplier');
    $this->addField('productcode');
    $this->hasOne('Product','source_product_id');
    $this->addField('title');
    $this->hasOne('Category');
    $this->addField('tax');
    $this->addField('manufacturer');
    $this->addField('manufacturer_code');
    $this->addField('ean');
    $this->addField('weight');
    $this->addField('info');
    $this->addField('info_modified');
//    $this->hasMany('Media');
    $this->addField('entry_date');
    $this->addField('last_checked');

    $this->hasMany('Media');

    $this->addHook('beforeInsert',function($m,$dsql){
      $dsql->set('entry_date',$dsql->expr('now()'));
    });
    $this->addHook('beforeLoad',function($o){
      unset($o->infoField);
    });
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




  //------------------------------------------------------------------------------------------------
  // returns one inner xml title for the specified language
  public $title;
  function title() {
    if(!isset($this->title)) {
      $this->title=$this->add('XML')->xmlToArray($this->getInfoTitle(),'info','lang');
    }
    $result=$this->title[$this->lang];
    if(!$result) $result = $this->title[null];
    if(!$result) $result = $this->title['nl'];
    if(!$result) $result = $this->get('title');
    return $result;
  }


  //------------------------------------------------------------------------------------------------
  // getInfo to use to get info[type='long'] or short
  protected function getInfo($type) {
    if(!isset($this->infoRead)) {
      $this->infoRead=new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><root>'.$this->get('info').'</root>');
    }
    $result='';
    foreach($this->infoRead->xpath("info[@type='".$type."']/*") as $node) {
      $result.=(string)$node->asXML()."\r\n";
    }
    return $result;
  }
  function getInfoLong() { return $this->getInfo('long'); }
  function getInfoShort() { return $this->getInfo('short'); }
  function getInfoTitle() { return $this->getInfo('title')?:$this->get('title'); }


  function meta_title() {
    $content= $this->getInfoTitle().' '.$this->get('manufacturer').' '.$this->get('manufacturer_code');
    return str_replace(array(';','=','#','{','}','<','>'),'-',$content);
  }

  function meta_description() {
    $content= $this->getInfoTitle().' '.$this->get('manufacturer').' '.$this->get('manufacturer_code');
    return str_replace(array(';','=','#','{','}','<','>'),'-',$content);
  }

  function meta_keywords() {
    $content= $this->getInfoTitle().','.$this->get('manufacturer').','.$this->get('manufacturer_code');
    $content=str_replace(array(';','=','#','{','}','<','>'),'-',$content);
    $content=str_replace(' ',',',$content);
    return $content;
  }

  function rewrite() {
    return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $this->getInfoTitle()), '-'));
  }



}

/*
update product p inner join supplier s on (s.name = p.supplier)
set p.supplier_id = s.id
where p.supplier_id =0
*/