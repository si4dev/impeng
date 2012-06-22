<?php
class Model_Supplier extends Model_Table {
  public $table='supplier';
  function init() {
    parent::init();
    $this->addField('name');
    $this->addField('friendly_name');
    $this->addField('config');
    $this->hasMany('Category');
    $this->hasMany('Product');
  }
  
 
  private function config() {
     return new SimpleXMLElement('<config>'.$this->get('config').'</config>');
     
  }
  
  function import_files() {
    $config=$this->config();
    foreach($config->import as $import) {
      $file=$this->api->getConfig('path_supplier_date').$this->get('name').'_'.(string)$import->name.'.'.(string)$import->type;
//      copy((string)$import->url,$file);
		  $fp=fopen($file,"r");
      $line=fgetcsv( $fp, 10000, (string)$import->seperator, ((string)$import->enclosure?:'"') );
      foreach($line as &$value) {
        $value = trim($value, (string)$import->trim);
      }
      if(!isset($fields)) {
        foreach($line as $value) {
          $field[strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $value), '_'))]='varchar(255)';
        }
      }
      
      // overrule default type and key when defined
      $primary=array();
      $set=array();
      $var=$field;
      foreach($import->fields->field as $fieldDef) {
        if($field[(string)$fieldDef->name]) {
          if((string)$fieldDef->type) { 
            $field[(string)$fieldDef->name]=(string)$fieldDef->type;
          }
          if((string)$fieldDef->key) {
            $primary[]=(string)$fieldDef->name;
          }
          if((string)$fieldDef->var) {
            $var[(string)$fieldDef->var]=$var[(string)$fieldDef->name];
            unset($var[(string)$fieldDef->name]);
            $set[(string)$fieldDef->name]=(string)$fieldDef->set;
          }
        }
      }
      
      // by default the first column is the primary key
      if(!$primary) {
        $primary[]=key($field);
      }

      foreach($field as $key=>$value) {
        $fieldAndType[]=$key.' '.$value.' DEFAULT NULL';
      }
      $setAndName=array();
      foreach($set as $key=>$value) {
        $setAndName[]=$key.'='.$value;
      }
      $var=array_keys($var);

      $db=$this->api->add('DB')->connect($this->api->getConfig('dsn_supplierdata'));
      $table=$this->get('name').'_'.$import->name;
      $db->query("drop table if exists {$table}_previous");
      if($db->getOne("show tables like '{$table}'")) {
        $db->query("alter table {$table} RENAME {$table}_previous");
      }
 
      $create='create table '.$table.' ('.implode(', ',$fieldAndType) .', PRIMARY KEY ('.implode(', ',$primary).') ) ENGINE=MyISAM DEFAULT CHARSET=utf8';
      $db->query($create);
      
          
            
      // now table is created and we can use fast load data infile
      $load="load data infile '".realpath($file)."' ".($import->duplicate?:'').' into table '.$table.
      ($import->characterset?" character set '".$import->characterset."'":'').
      " fields terminated by '".($import->seperator?:',')."'". // the mysql default is '\t' now it is ','
      " enclosed by '".($import->enclosure?:'"')."'". // the mysql default is '', now it's '"'
      ($import->escape?" escaped by '".$import->escape."'":''). // the mysql default is '\\'
      ($import->terminate?" lines terminated by '".$import->terminate."'":''). // the mysql default is '\n'
      ' ignore 1 lines '.
      '('.implode(', ',$var).') '.
      ($setAndName?'set ':'').implode(', ',$setAndName);
      $db->query($load);   
      
      
   //   $this->import_category();
      $this->import_product();
      $this->import_watch();
    }
    return $this;
  }

  // helper to get fields defined in the xml config doc
  private function import_fields($doc) {
    foreach($doc->xpath('fieldmap/*') as $v) {
      $content='';
      foreach (dom_import_simplexml($v)->childNodes as $child ) {
        if( $child->nodeType == XML_ELEMENT_NODE ) {
          if($child->nodeName=='field') {
            $content.=' t1.'.$child->getAttribute('ref').' ';            
          }
        } else {
          $content .= $child->ownerDocument->saveXML( $child ); 
        }
      }
      $fields[$v->getName()]=$content;
    }
    return $fields;
  }

  // helper to format the fields for select statement  
  private function import_fields_select($doc) {
    foreach($this->import_fields($doc) as $k=>$v) 
      $fields[]=$v.' '.$k;
    return $fields;
  } 
   
  // helper to get the supplier table field names
  private function import_supfields($doc,$path='.') {
    $supfields=array();
    // we need to know which fields of the supplier table are needed and only once (so in array key)
    foreach($doc->xpath($path.'//field') as $v) {
      $supfields[(string)$v['ref']]='';
    }
    // now chnage array keys to array values
    return array_keys($supfields);
  }

  
    // import categories
  function import_category() {
    $node=$this->config()->category;
    $supfields=$this->import_supfields($node);
    $fields=$this->import_fields_select($node);
    $table='impeng_supplierdata.'.$this->get('name').'_'.$node->use->table;

    $select='select '.implode(', ',($fields)).' from ( select '.implode(', ',($supfields)).' from '.$table.' group by '.implode(', ',($supfields)).') t1'.
        ' left join ( select '.implode(', ',($supfields)).' from '.$table.'_previous group by '.implode(', ',($supfields)).') t2'.
        ' using  ('.implode(', ',($supfields)).') '.
        ' where t2.'.$supfields[0].' is null';
            
    $cat=$this->ref('Category');
    foreach($this->api->db->query($select) as $row) {
      $cat->unload()
          ->set('reference',$row['reference'])
          ->category_title($row['title'])
          ->save();
    }
  }

  // import products
  function import_product() {
    $node=$this->config()->product;
    $supfields=$this->import_supfields($node);
    $supfields_productcode=$this->import_supfields($node,'fieldmap/productcode');
    $fields=$this->import_fields_select($node);
    $table='impeng_supplierdata.'.$this->get('name').'_'.$node->use->table;

    foreach($supfields as $f) { 
      $supfields1[]='t1.'.$f; 
      $supfields2[]='t2.'.$f; 
    }

    $select='select '.implode(', ',($fields)).
        ' from '.$table.' t1 '.
        ' left join '.$table.'_previous2 t2 '.
        ' using  ('.implode(', ',($supfields_productcode)).') '.
        ' where t2.'.$supfields[0].' is null'.
        ' or ('.implode(', ',($supfields1)).') != ('.implode(', ',($supfields2)).')';
                        
    $prod=$this->ref('Product');
    $cat=$this->add('Model_Category');
    $i=0;
    foreach($this->api->db->query($select) as $row) {
//      echo '['.$row['productcode'].':::';
//      echo ''.$row['info_long_nl'].']';
      // TODO we can keep $cat when category_ref is same for next product, maybe it saves time
      $cat->loadBy('reference',$row['category_ref']);
      $prod->tryLoadBy('productcode',$row['productcode'])
          ->set('productcode',$row['productcode'])
          ->set('title',$row['title'])
          ->set('category_id',$cat->id)
          ->set('manufacturer',$row['manufacturer'])
          ->set('manufacturer_code',$row['manufacturer_code'])
          ->set('ean',$row['ean'])
          ->set('weight',$row['weight'])
          ->set('tax',$row['tax'])
          ->set('last_checked',$prod->dsql->expr('now()'));

      // get all rows with info_type_lang format, use array_keys as we don't need the big data values.
      foreach(array_keys($row) as $key) {
        list($info,$type,$lang)=explode('_',$key);
        if($info=='info') {
          $prod->addInfo($type,$row[$key],$lang);
        }
      }
      $prod->setInfo()->saveAndUnload();
    
      if($i++ > 20000) {
        break;
      }
    }
  }

  // import watch price and stock  
  function import_watch() {
    $node=$this->config()->watch;
    $fields=$this->import_fields($node);
    $table='impeng_supplierdata.'.$this->get('name').'_'.$node->use->table;
    
    $query='insert into watch (product_id, pricebook_id, stock, price, last_checked, modified) '.
        'select p.id, :pricebook, '.$fields['stock'].', '.$fields['price'].', now(), now() '.
        'from '.$table.' t1 inner join product p on (p.productcode='.$fields['productcode'].' and p.supplier_id=:supplier) '.
        'where '.$fields['productcode']."!='' ".
        'on duplicate key update '.
        ' modified=if(price!=values(price),now(),modified), price=values(price), stock=values(stock), last_checked=now()';
        
    $this->api->db->query($query,array('supplier'=>$this->id,'pricebook'=>(int)$node->pricebook));
  }
}

/*
<import>
  <name>pricelist</name>
  <url>http://www.complies.nl/clientexport.aspx?name=complies01&amp;type=csv&amp;key=3692pcfast</url>
  <type>csv</type>
  <encoding>utf8</encoding>
  <seperator xml:space="preserve">,</seperator>
  <enclosure>"</enclosure>
  <trim xml:space="preserve"> </trim>
  <escape></escape>
  <terminate>\n</terminate>
  <table>pricelist</table>
    <fields>
      <field><name>artikelnumber</name><key>primary</key></field>
      <field><name>prijs_ex_btw</name><type>double</type></field>
      <field><name>PriceLevel1</name><type>double</type></field>
      <field><name>InStockCentral</name><type>double</type></field>
      <field><name>priceSingle</name><type>double</type><var>@priceSingle</var><set>replace(@priceSingle, ',', '.')</set></field>
      <field><name>inStock</name><type>double</type><var>@inStock</var><set>replace(@inStock, '+', '')</set></field>
    <fields>
  <duplicate>ignore</duplicate>
</import>

<category>
  <use>
    <table>pricelist</table>
  </use>          
  <fieldmap>          
    <CategoryReference><field ref="productgroepnr"/></CategoryReference>          
    <CategoryTitle>replace(<field ref="groepomschrijving"/>, '/', '|')</CategoryTitle>
  </fieldmap>
</category>

<product>
  <use>
    <table>pricelist</table>
  </use>        
  <fieldmap> 
    <ProductCode><field ref="artikelnummer"/></ProductCode>
    <ProductTitle><field ref="artikelomschrijving"/></ProductTitle>
    <ProductManufacturer><field ref="merk"/></ProductManufacturer>
    <ProductManufacturerCode><field ref="artnrfabrikant"/></ProductManufacturerCode>
    <ProductCategoryReference><field ref="productgroepnr"/></ProductCategoryReference>          
    <ProductEan><field ref="EAN"/></ProductEan>
    <ProductExpected><field ref="ontvangstdatum"/></ProductExpected>
    <ProductTax>19</ProductTax>
  </fieldmap>          
</product>        

<watch>
  <WatchPricebook>1</WatchPricebook>
  <use>
    <table>pricelist</table>
  </use>
  <fieldmap> 
    <ProductCode><field ref="artikelnummer"/></ProductCode>
    <WatchPrice><field ref="prijsdealer"/></WatchPrice>
    <WatchStock><field ref="voorraad"/></WatchStock>
  </fieldmap>
</watch>

 */