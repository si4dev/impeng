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
    if( strpos($this->get('SupplierCategoryTitle'),'<node') ) {
      $titleXml=$this->get('SupplierCategoryTitle');
    } else {
      // in case the category is not yet in xml structure for old supplier import
      $titleXml='<cat lang="nl"><node>'.$this->get('SupplierCategoryTitle').'</node></cat>';
    }
    $this->title=new SimpleXMLElement('<title>'.$titleXml.'</title>');
  }

  function categoryByLang($iso) {
    if(!isset($this->title)) $this->titleXml();
    
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
