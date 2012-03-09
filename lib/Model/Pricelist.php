<?php
class Model_Pricelist extends Model_Table {
  public $table='pricelist';
  function init() {
    parent::init();
    
    $this->addField('shop_productcode');
    $this->addField('product_title');
    $this->addField('supplier_category_id');
    $this->addField('shop_category_id');
    $this->addField('weight');
    $this->addField('short_description');
    $this->addField('specification');
    $this->addField('stock');
    $this->addField('entry_date');
    $this->addField('price');
    $this->hasOne('Shop');
    
  }

  function short_description() {

    $xml=new SimpleXMLElement('<root>'.$this->get('short_description').'</root>');
    $result='';
    foreach($xml->xpath("info[@type='short']/*") as $node) {
      $result.=(string)$node->asXML();
    }
    return $result;
  }
  function specification() {
//echo "<pre>" . htmlentities($this->get('specification'));
    $xml=new SimpleXMLElement('<root>'.$this->get('specification').'</root>');
    $result='';
    foreach($xml->xpath("info/*") as $node) {
      $result.=(string)$node->asXML();
    }
    return $result;
  }
}

/*

CREATE TABLE `pricelist` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(50) DEFAULT NULL,
  `supplier_id` varchar(50) DEFAULT NULL,
  `supplier_productcode` varchar(100) DEFAULT NULL,
  `shop_productcode` varchar(50) NOT NULL DEFAULT '',
  `product_title` varchar(250) DEFAULT NULL,
  `product_title_ml` mediumtext,
  `supplier_category_id` int(10) unsigned DEFAULT NULL,
  `shop_category_id` int(10) unsigned DEFAULT NULL,
  `tax` double DEFAULT NULL,
  `price` double DEFAULT NULL,
  `price_si` double DEFAULT NULL,
  `price_pe` double DEFAULT NULL,
  `promo` int(10) unsigned DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `manufacturer_code` varchar(100) DEFAULT NULL,
  `ean` varchar(100) DEFAULT NULL,
  `specification` mediumtext,
  `relations` mediumtext,
  `info_actualised` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `short_description` mediumtext,
  `features` mediumtext COMMENT 'XML',
  `weight` float DEFAULT '0',
  `media_id` int(10) unsigned DEFAULT NULL,
  `entry_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_checked` datetime DEFAULT NULL,
  `price_decline` double DEFAULT NULL,
  `price_advance` double DEFAULT NULL,
  `new` enum('yes','no') DEFAULT NULL,
  `shop_info_actualised` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iWebArticleShopSupplierProduct` (`shop_id`,`supplier_id`,`supplier_productcode`),
  KEY `iWebArticleShop` (`shop_id`),
  KEY `iWebArticleCode` (`shop_productcode`),
  KEY `iWebArticleProductCode` (`supplier_productcode`),
  FULLTEXT KEY `iSupplierArticleTitle` (`product_title`)
) ENGINE=MyISAM AUTO_INCREMENT=82294469 DEFAULT CHARSET=utf8;

*/