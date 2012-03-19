<?php
class Model_Xcart_Product extends Model_Xcart {
  public $table='xcart_products';
  public $id_field='productid';
  public $title_field='product';
  function init() {
  
    parent::init();
    $this->addField('productcode');
    $this->addField('product');
    $this->addField('provider')->defaultValue(1);
    $this->addField('weight');
    $this->addField('descr');
    $this->addField('fulldescr');
    $this->addField('avail');
    $this->addField('list_price');
    $this->addField('add_date');
    $this->hasMany('Xcart_Pricing','productid');
    $this->hasMany('Xcart_ProductCategory','productid');
    $this->hasMany('Xcart_QuickPrices','productid'); 
    $this->hasMany('Xcart_QuickFlags','productid'); 
    $this->hasMany('Xcart_ImageP','id'); 
    $this->hasMany('Xcart_ImageT','id'); 
    
  }
  
  function import( $product ) {
   
    //var_dump( $product ); 
  }
}

/*

CREATE TABLE `xcart_products` (
  `productid` int(11) NOT NULL AUTO_INCREMENT,
  `productcode` varchar(32) NOT NULL DEFAULT '',
  `product` varchar(255) NOT NULL DEFAULT '',
  `provider` int(11) NOT NULL DEFAULT '0',
  `distribution` varchar(255) NOT NULL DEFAULT '',
  `weight` decimal(12,2) NOT NULL DEFAULT '0.00',
  `list_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `descr` text NOT NULL,
  `fulldescr` text NOT NULL,
  `avail` int(11) NOT NULL DEFAULT '0',
  `rating` int(11) NOT NULL DEFAULT '0',
  `forsale` char(1) NOT NULL DEFAULT 'Y',
  `add_date` int(11) NOT NULL DEFAULT '0',
  `views_stats` int(11) NOT NULL DEFAULT '0',
  `sales_stats` int(11) NOT NULL DEFAULT '0',
  `del_stats` int(11) NOT NULL DEFAULT '0',
  `shipping_freight` decimal(12,2) NOT NULL DEFAULT '0.00',
  `free_shipping` char(1) NOT NULL DEFAULT 'N',
  `discount_avail` char(1) NOT NULL DEFAULT 'Y',
  `min_amount` int(11) NOT NULL DEFAULT '1',
  `length` decimal(12,2) NOT NULL DEFAULT '0.00',
  `width` decimal(12,2) NOT NULL DEFAULT '0.00',
  `height` decimal(12,2) NOT NULL DEFAULT '0.00',
  `low_avail_limit` int(11) NOT NULL DEFAULT '10',
  `free_tax` char(1) NOT NULL DEFAULT 'N',
  `product_type` char(1) NOT NULL DEFAULT 'N',
  `manufacturerid` int(11) NOT NULL DEFAULT '0',
  `return_time` int(11) NOT NULL DEFAULT '0',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `meta_description` text NOT NULL,
  `meta_keywords` text NOT NULL,
  `small_item` char(1) NOT NULL DEFAULT 'N',
  `separate_box` char(1) NOT NULL DEFAULT 'N',
  `items_per_box` int(11) NOT NULL DEFAULT '1',
  `title_tag` text NOT NULL,
  PRIMARY KEY (`productid`),
  UNIQUE KEY `productcode` (`productcode`,`provider`),
  KEY `product` (`product`),
  KEY `rating` (`rating`),
  KEY `add_date` (`add_date`),
  KEY `provider` (`provider`),
  KEY `avail` (`avail`),
  KEY `best_sellers` (`sales_stats`,`views_stats`),
  KEY `categories` (`forsale`),
  KEY `fi` (`forsale`,`productid`),
  KEY `fia` (`forsale`,`productid`,`avail`),
  KEY `ppp` (`productcode`,`provider`,`productid`),
  KEY `manufacturerid` (`manufacturerid`)
) ENGINE=MyISAM AUTO_INCREMENT=17518 DEFAULT CHARSET=latin1;




*/