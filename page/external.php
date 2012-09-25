<?php
class Page_External extends Page {
  function init() {
    parent::init();
    
    if($key=$this->api->getConfig('key',null) and $_GET['key']===$key) {
      $s=$this->add('Model_Shop')->loadBy('name',$_GET['shop']);
    } else {
      $si=$this->add('Controller_Shopimport');
      $s=$si->shop;
    }
    
    $product=$s->getShopPricelist();
    //print_r($products);
/*        
    $c=$this->add('Grid');
    $c->setModel($products,array('test'=>'reference','name','description_short','image','manufacturer','taxrate','price_incl'));
    $c->addFormatter('description_short','shorttext');
    return;
*/
    // Prijzen, EAN code, SKU code, levertijden, productnamen en natuurlijk de deeplinks. 
    $file=$this->api->getConfig('path_externaldata').'tweakers/'.$s['name'].'.csv';
    if(!($fp = fopen($file, 'w+'))) {
      throw $this->exception('cannot write to csv file')->addMoreInfo('file',$file);
    }
    
  //  $product->addCondition('reference','A6E60EA');
    
    $domain=$s->shopconfig('domain');
    $product->setActualFields(array('taxrate','price','reference','name','ean13','quantity','id_product','link_rewrite','specific_price'));
    foreach($product as $p) {
      $price=round((1+$p['taxrate']/100)*($p['specific_price']?:$p['price']),2);
      $line=array( 
          'SKU' => $p['reference'],
          'title' => $p['name'],
          'EAN' => $p['ean13'],
          'price' => number_format($price,2,'.',''),
          'stock' => ($p['quantity']>0?'2 dagen':'8 dagen'),
          'URL' => 'http://'.$domain.'/'.$p['id_product'].'-'.$p['link_rewrite'].'.html',
          );
      if(!isset($header)) {
        fputcsv($fp, array_keys($line), ',', '"' );
        $header=true;
      }
      fputcsv($fp, $line, ',', '"' );
    }
    fclose($fp);
    
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