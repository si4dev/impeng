<?php
class Model_Prestashop_Product extends Model_Table2 {
  public $table='ps_product';
  public $id_field='id_product';
  public $title_field='reference';
  function init() {
    parent::init();
    $this->addField('reference');
    $this->addField('supplier_reference');
    $this->addField('quantity');
    $this->addField('price');
    $this->addField('ean13');
    $this->addField('weight');
    $this->addField('location');
    $this->addField('id_category_default');
//    $this->addField('id_color_default');
    $this->addField('date_add');
    $this->addField('date_upd');
    $this->addField('active');
    $this->hasMany('Prestashop_CategoryProduct','id_product');
    $this->hasMany('Prestashop_ProductShop','id_product');
    $this->hasMany('Prestashop_ProductLang','id_product');
    $this->hasMany('Prestashop_Image','id_product');
    $this->hasOne('Prestashop_Supplier','id_supplier',null,'supplier');
    $this->hasOne('Prestashop_Manufacturer','id_manufacturer',null,'manufacturer');
    $this->hasOne('Prestashop_Tax','id_tax_rules_group');
  }
  
  function pricelist() {
    // might need this for some shops
    //$productfields=$this->_dsql()->owner->query("show columns from ps_product where field like 'reduction_price'")->fetch();
    $pl=$this->join('ps_product_lang.id_product','id_product');
    $pl->addField('name');
    $pl->addField('description_short');
    $pl->addField('link_rewrite');
    $pl->join('ps_lang.id_lang','id_lang')->addField('iso_code');
    $this->addCondition('iso_code','nl');
    $this->addExpression('manufacturer')->set(function($m,$q) {
      return $m->refSQL('id_manufacturer')->dsql()->field('name');
    });
    $this->addExpression('taxrate')->set(function($m,$q) {
      return $m->refSQL('id_tax_rules_group')->dsql()->field('rate');
    });
    $this->addExpression('image')->set(function($m,$q) {
      return $m->refSQL('Prestashop_Image')->dsql()->field('id_image')->limit(1);
    });
    $this->addExpression('specific_price')->set(function($m,$q) {
      return $q->dsql()->table('ps_specific_price')->field('price')->where('id_product',$q->getField('id_product'));
    });
    //$this->addExpression('price_incl',$this->dsql()->expr('100 * taxrate'));
    $this->addCondition('active',1);
    return $this;
  }
}


/*
      <connection>shop_<xsl:value-of select="$shop"/></connection>
      <query>
        select        
          d.name as title, 
          concat('http://<xsl:value-of select="web"/>/',d.id_product,'-',d.link_rewrite,'.html') as url, 
          d.description_short as description,
          <xsl:choose>
            <xsl:when test="pricelist_productfields/Field = 'reduction_price'">round((1+t.rate/100)* p.price - ifnull(p.reduction_price, 0)   ,2) as price,</xsl:when> 
            <xsl:otherwise>round((1+t.rate/100)* p.price,  2) as price,</xsl:otherwise>            
          </xsl:choose>          
          p.quantity as stock, 
          p.reference as productcode,
          if(i.id_image is null,'http://<xsl:value-of select="web"/>/img/p/nl-default-large.jpg', concat('http://<xsl:value-of select="web"/>/',d.id_product,'-',i.id_image,'-large/',d.link_rewrite,'.jpg')) as image,
                            m.name as manufacturer,
                            p.location as manufacturercode,
                        if(cl.name rlike '^[0-9]+\.', substring(cl.name,1+locate('.',cl.name) ) , cl.name) as category,
                  id_category_default category_id,
          ean13 ean
      from 
        <xsl:value-of select="shoptableprefix"/>product p 
        inner join <xsl:value-of select="shoptableprefix"/>lang l 
        inner join <xsl:value-of select="shoptableprefix"/>product_lang d on (p.id_product = d.id_product and d.id_lang = l.id_lang)
          <xsl:choose>
            <xsl:when test="pricelist_productfields/Field = 'id_tax_rules_group'">
              inner join ps_tax_rules_group trg on (trg.id_tax_rules_group = p.id_tax_rules_group)
              inner join ps_tax_rule tr on (tr.id_tax_rules_group = trg.id_tax_rules_group) 
              inner join  ps_country  cty on (cty.id_country  = tr.id_country and cty.iso_code = 'NL')
              inner join ps_tax t on (t.id_tax = tr.id_tax)
            </xsl:when> 
            <xsl:otherwise>
              inner join <xsl:value-of select="shoptableprefix"/>tax t on (t.id_tax = p.id_tax)
            </xsl:otherwise>            
          </xsl:choose>   
        left join <xsl:value-of select="shoptableprefix"/>image i on (i.id_product = p.id_product) 
        left join <xsl:value-of select="shoptableprefix"/>manufacturer m on (p.id_manufacturer = m.id_manufacturer)
        left join <xsl:value-of select="shoptableprefix"/>category c on (p.id_category_default = c.id_category)
                  left join <xsl:value-of select="shoptableprefix"/>category_lang cl on (c.id_category = cl.id_category and cl.id_lang = l.id_lang)
        where 
          p.active = 1 and l.iso_code = 'nl'
        order by p.date_add desc
      </query>
      <xmlfields></xmlfields>
*/
