<?php
class page_import extends Page {
  function init() {
    parent::init();


        
    if( $supplier_id=$_GET['supplier'] ) {
    
      $supplier=$this->add('Model_Supplier');
      $supplier->load($supplier_id);
     
      $this->add('h1')->set('Supplier Import '.$supplier->get('friendly_name'));
      $supplier->import();  
    
    }
  }
}
    

/*
<import>
          <file><xsl:value-of select="supplierdataPath"/>/pricelist.csv</file>
          <encoding>utf8</encoding>
          <seperator xml:space="preserve">,</seperator>
          <enclosure>"</enclosure>
          <trim xml:space="preserve"> </trim>
          <escape></escape>
          <terminate>\n</terminate>
          <table><xsl:value-of select="supplier"/>_pricelist</table>
          <fields>
            <field><name>NedisPartnr</name><key>primary</key></field>
            <field><name>Weight</name><type>double</type></field>
            <field><name>PriceLevel1</name><type>double</type></field>
            <field><name>InStockCentral</name><type>double</type></field>

          </fields>
        </import>

        

        <category>
          <use>
            <table><xsl:value-of select="supplier"/>_pricelist</table>
          </use>          
          <fieldmap>          
            <CategoryReference>concat(<field ref="CategoryLevel1_id"/>,'|',<field ref="CategoryLevel2_id"/>,'|',<field ref="CategoryLevel3_id"/>)</CategoryReference>
            <CategoryTitle>concat(<field ref="CategoryLevel1_text"/>,'|',<field ref="CategoryLevel2_text"/>,'|',<field ref="CategoryLevel3_text"/>)</CategoryTitle>
          </fieldmap>
        </category>

        <product>
          <use>
            <table><xsl:value-of select="supplier"/>_pricelist</table>
          </use>        
          <fieldmap> 
            <ProductCode><field ref="NedisPartnr"/></ProductCode>
            <ProductTitle><field ref="HeaderText"/></ProductTitle>
            <ProductManufacturer><field ref="Brand"/></ProductManufacturer>
            <ProductManufacturerCode><field ref="VendorPartNr"/></ProductManufacturerCode>
            <ProductCategoryReference>concat(<field ref="CategoryLevel1_id"/>,'|',<field ref="CategoryLevel2_id"/>,'|',<field ref="CategoryLevel3_id"/>)</ProductCategoryReference>
            <ProductEan><field ref="EAN"/></ProductEan>
            <ProductTax>19</ProductTax>
            <ProductImage><field ref="Picture"/></ProductImage>
            <ProductDescription><field ref="GeneralText"/></ProductDescription>
            <ProductDescriptionShort><field ref="InternetText"/></ProductDescriptionShort>
          </fieldmap>          
        </product>        

        <watch>
          <WatchPricebook>1</WatchPricebook>
          <use>
            <table><xsl:value-of select="supplier"/>_pricelist</table>
          </use>
          <fieldmap> 
            <ProductCode><field ref="NedisPartnr"/></ProductCode>
            <WatchPrice><field ref="PriceLevel1"/></WatchPrice>
            <WatchStock><field ref="InStockCentral"/></WatchStock>
          </fieldmap>
        </watch>
        
      </definition>
*/

