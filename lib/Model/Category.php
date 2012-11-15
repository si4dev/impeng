<?php
class Model_Category extends Model_Table {
  public $table='category';
  public $title_field='reference';
  public $titleXml;
  function init() {
    parent::init();
    $this->hasOne('Supplier');
    $this->addField('title');
    $this->addField('reference');


    $this->addHook('beforeLoad',function($o){ unset($o->titleXml); });

    
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
  
  
  
  /*
  
     $this->title=new SimpleXMLElement('<title>'.
     '<cat lang="nl">
        <node>VOGELS2</node>
        <node>Knaagdieren konijnen</node>
      </cat>'.
      '</title>');
  */
  public function getTitleXml() {
    
    if(!isset($this->titleXml)) {
      $title=$this->get('title');
      /* should not be needed anymore 
      if( strpos($title,'<node') === false ) {
        // in case the category is not yet in xml structure for old supplier import
        $title=trim($title)?:'-';
        $this->title=new SimpleXMLElement('<title></title>');
        $cat=$this->title->addChild('cat');
        $cat->addAttribute('lang','nl');
        $cat->{"node"}[]=$title; // this way XML is properly escaped, http://www.php.net/manual/en/simplexmlelement.addchild.php
      } else {
      }
      */
      $this->titleXml=new SimpleXMLElement('<title>'.$title.'</title>');
    }
    return $this->titleXml;
  }


  // returns array of xml node 
  function ZZZgetTitleXmlByLang() {
    $t=array();
    foreach( $this->getTitleXml()->cat as $cat ) {
      $t[(string)$cat['lang']]=$cat;
    }
    return $t;
  }



  function ZZZgetTitleByLang($iso) {
    foreach( $this->getTitleXml()->cat as $cat ) {
      if( (string)$cat['lang'] == $iso ) {
        return $cat;
      }
    }
    return false;
  }
    

  function ZZZtitle() {
    if(!isset($this->title)) $this->titleXml();
      foreach($this->title->cat as $cat) {
        foreach($cat->node as $node) {
        echo '[['.(string)$node.'==';
        }
      }
    return 'tet';
  }

}

/*
update category c inner join supplier s on (s.name = c.supplier) 
set c.supplier_id = s.id
where c.supplier_id =0
*/