<?php
class Model_Category extends Model_Table {
  public $table='tbltype_category';
  public $id_field='CategoryID';
  public $title_field='CategoryID';
  public $title;
  
  function init() {
    parent::init();
    
    $this->addField('CategoryShop');
    $this->addField('CategorySupplierID');
    $this->addField('CategoryShopID');
    
    
    
//    $this->addHook('beforeLoad',function($o){       unset($o->title);        echo 'closure'; });
  }
  
  
  /*
  
     $this->title=new SimpleXMLElement('<title>'.
     '<cat lang="nl">
        <node>VOGELS2</node>
        <node>Knaagdieren konijnen</node>
      </cat>'.
      '</title>');
  */
  private function titleXml() {
    $title=$this->get('SupplierCategoryTitle');
    if( strpos($title,'<node') === false ) {
      // in case the category is not yet in xml structure for old supplier import
      $title=trim($title)?:'-';
      $this->title=new SimpleXMLElement('<title></title>');
      $cat=$this->title->addChild('cat');
      $cat->addAttribute('lang','nl');
      $cat->{"node"}[]=$title; // this way XML is properly escaped, http://www.php.net/manual/en/simplexmlelement.addchild.php
    } else {
      $this->title=new SimpleXMLElement('<title>'.$title.'</title>');
    }
  }

  function categoryByLang($iso) {
    $this->titleXml();
    
    foreach( $this->title->cat as $cat ) {
      if( (string)$cat['lang'] == $iso ) {
        return $cat;
      }
    }
  }
    

  function title() {
    if(!isset($this->title)) $this->titleXml();
      foreach($this->title->cat as $cat) {
        foreach($cat->node as $node) {
        echo '[['.(string)$node.'==';
        }
      }
    return 'tet';
  }

}
