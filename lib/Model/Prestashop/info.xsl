<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="xml" encoding="UTF-8" omit-xml-declaration="yes"/>
<xsl:include href="engine.xsl"/>

<!-- keys -->
<xsl:key name="shop_manufacturer" match="shop_manufacturer/row" use="translate(name,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
<xsl:key name="shop_product" match="shop_product/row" use="reference"/>
<xsl:key name="pricelist_product" match="pricelist_product/row" use="WebArticleCode"/>
<xsl:key name="pricelist_feature" match="pricelist_feature/row" use="featmapFeatureType"/>

<xsl:key name="dir_shop_images" match="dir_shop_images/file" use="."/>
<xsl:key name="media_pricelist" match="media_pricelist/row" use="WebArticleCode"/>
<xsl:key name="shop_media_files" match="shop_media_files/file" use="."/>
<xsl:key name="shop_media_database" match="shop_media_database/row" use="id_product_and_image"/>


<!-- **********************************************************************************************
      SHOPIMPORT PRESTASHOP CONNECTOR
 -->


<!-- **********************************************************************************************
      SHOP
      
      To transfer the prepared pricelist to the shop 
      
          <job cmd="shop_images">
      <xsl:copy-of select="shop"/>
      <xsl:copy-of select="shopconfig"/>
    </job>
    
    
-->

<xsl:template match="job[@cmd='shop']">
  <xsl:variable name="shoptableprefix">
    <xsl:choose>
      <xsl:when test="shopconfig/shoptableprefix"><xsl:value-of select="shopconfig/shoptableprefix"/></xsl:when>
      <xsl:otherwise>ps_</xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <job cmd="shop_show">
    <xsl:copy-of select="shop"/>
    <xsl:copy-of select="email"/>
    <xsl:copy-of select="sendmail"/>
    
    <job cmd="shop_aftervalidate">
      <xsl:copy-of select="shop"/>
      <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>

      <job cmd="shop_validate">
        <xsl:copy-of select="shop"/>
        <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>
      </job>

      <job cmd="shop_taxgroup">
        <xsl:copy-of select="shop"/>
        <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>
      </job>

      <job cmd="shop_languages">
        <xsl:copy-of select="shop"/>
        <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>
      </job>

      <job cmd="shop_supplier_ids">
        <xsl:copy-of select="shop"/>
      </job>
      
    </job>
    

  </job>
</xsl:template>


<!-- **********************************************************************************************
      SHOP_AFTERVALIDATE
      
      To transfer the prepared pricelist to the shop 
-->

<xsl:template match="job[@cmd='shop_aftervalidate']">

  <xsl:choose>
    <xsl:when test="validate='true'">

      <!-- now only for manufacturer, supplier and deletion -->
      <job cmd="shop_storedata">
        <xsl:copy-of select="shop"/>
        <xsl:copy-of select="shoptableprefix"/>
        <xsl:copy-of select="shop_supplier_ids"/>
        <xsl:copy-of select="lang"/>
        <xsl:copy-of select="languages"/>
        <job cmd="shop_getdata">
          <xsl:copy-of select="shop"/>
          <xsl:copy-of select="shoptableprefix"/>
          <xsl:copy-of select="shop_supplier_ids"/>
          <xsl:copy-of select="lang"/>
        </job>
      </job>

      <!-- now only for product -->
      <job cmd="shop_storedata_product">
        <xsl:copy-of select="shop"/>
        <xsl:copy-of select="shoptableprefix"/>
        <xsl:copy-of select="shop_supplier_ids"/>
        <xsl:copy-of select="lang"/>
        <xsl:copy-of select="languages"/>
        <xsl:copy-of select="pricelist_taxgroup"/>
        
        <job cmd="shop_getdata_product">
          <xsl:copy-of select="shop"/>
        </job>
      </job>
  
      <action cmd="url">
        <url>http://www.shopimport.nl/impeng/presta/searchcron.php?shop=<xsl:value-of select="shop"/></url>
    	</action>

    </xsl:when>
    <xsl:otherwise>
      <message>No nl language or 19% (or even 6%) tax defined.</message>
    </xsl:otherwise>
  </xsl:choose>

</xsl:template>



<!-- ***********************************************************************************************
      SHOW and EMAIL
-->

<xsl:template match="job[@cmd='shop_show']">
  
  <xsl:call-template name="showcontent"/>
  
  <xsl:if test="not(sendmail = 'no')">
    <action cmd="mail">
      <from>info@shopimport.nl</from>
      <to><xsl:value-of select="email"/></to>
      <bcc>bcc@shopimport.nl</bcc>
      <subject>SHOPIMPORT notificatie <xsl:value-of select="shop"/></subject>
      <type>html</type>
      <content xml="true">
        Shopimport resultaat voor <xsl:value-of select="shop"/>
        
        <xsl:call-template name="showcontent"/>
    
        Dit is een automatisch bericht naar <xsl:value-of select="email"/>. Voor vragen kunt u contact opnemen met het shopimport team.
        
        Met vriendelijke groet,
        Het shopimport team,
        info@shopimport.nl
      </content>
    </action>
  </xsl:if>
</xsl:template>


<xsl:template name="showcontent">
  
  <xsl:if test="message"><h3 style="color:red;"><xsl:copy-of select="message"/></h3></xsl:if>

  <p>Fabrikanten toegevoegd: <xsl:value-of select="count(insert_manufacturer/insertid)"/></p>
  <p>Producten: <xsl:value-of select="count(insert_product/insertid)"/> toegevoegd, 
      <xsl:value-of select="count(update_product/rowcount)"/> geupdate,
      <xsl:value-of select="count(delete_product/rowcount)"/> verwijderd</p>
  <p>Product beschrijvingen: <xsl:value-of select="count(insert_productdescription/insertid)"/> toegevoegd, 
      <xsl:value-of select="count(insert_productdescription/rowcount)"/> geupdate,
      <xsl:value-of select="count(delete_productdescription)"/> verwijderd</p>
  <p>Product categorien: <xsl:value-of select="count(insert_productcategory/insertid)"/> geupdate</p>

</xsl:template>




<!-- ***********************************************************************************************
      shop_storedata

      Store the data to the live shop 

      INPUT:
      WebArticleFeatures: XML structure of features:          
        <features>
          <feature type="tireWidth">175</feature>
          <feature type="tireDiameter">13</feature>
          <feature type="tireAspectRatio">70</feature>
        </features>
      
-->

<xsl:template match="job[@cmd='shop_storedata']">
  <xsl:variable name="shop"><xsl:value-of select="shop"/></xsl:variable>
  <xsl:variable name="shoptableprefix"><xsl:value-of select="shoptableprefix"/></xsl:variable>

	<xsl:for-each select="pricelist_manufacturer/row[not(key('shop_manufacturer',translate(WebArticleManufacturer,'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')))]">
	
    <insert_manufacturer>
      <action cmd="recordset">
        <connection>shop_<xsl:value-of select="$shop"/></connection>
        <query>
          INSERT <xsl:value-of select="$shoptableprefix"/>manufacturer 
          SET 
            name = :WebArticleManufacturer,
            date_add = now(),
            date_upd = now()
        </query>
        <params>
          <xsl:copy-of select="WebArticleManufacturer"/>
        </params>
      </action>
    </insert_manufacturer>
  </xsl:for-each>

  
	<xsl:for-each select="pricelist_supplier/row[not(current()/shop_supplier/row/name = SupplierCode)]">
	
    <!-- smart supplier update to ensure it will not generate double entries and to get the id_supplier for the next query -->
    <insert_supplier>
      <action cmd="recordset">
        <connection>shop_<xsl:value-of select="$shop"/></connection>
        <query>
          INSERT <xsl:value-of select="$shoptableprefix"/>supplier 
            (id_supplier, name, date_add, date_upd)
          VALUES (
            (SELECT s.id_supplier FROM (select * from <xsl:value-of select="$shoptableprefix"/>supplier) s WHERE s.name = :SupplierCode ),
            :SupplierCode, now(), now() )
          ON DUPLICATE KEY UPDATE id_supplier = last_insert_id(id_supplier),
            date_upd = VALUES( date_upd )
        </query>
        <params>
          <xsl:copy-of select="SupplierCode"/>
        </params>
      </action>
    </insert_supplier>
    <insert_supplier_lang>
      <action cmd="recordset">
        <connection>shop_<xsl:value-of select="$shop"/></connection>
        <query>
          INSERT IGNORE <xsl:value-of select="$shoptableprefix"/>supplier_lang
            (id_supplier, id_lang, description )
            SELECT last_insert_id(), id_lang, ''
            FROM <xsl:value-of select="$shoptableprefix"/>lang WHERE active=1
        </query>
        <params/>
      </action>
    </insert_supplier_lang>


    </xsl:for-each>
  
  
  

  <!-- delete changed to update
    delete from c using ps_category_product c left join `ps_product`  p  on ( c.id_product = p.id_product) 
    where p.id_product is null
  -->
	<xsl:for-each select="shop_product/row[active != 0]">
      <xsl:if test="not(key('pricelist_product',reference)) or (key('pricelist_product',reference)/WebArticleRelations/relations/relation[@type='main']/productcode != reference)">
         <delete_product>
          <action cmd="recordset">
            <connection>shop_<xsl:value-of select="$shop"/></connection>
            <query>
              UPDATE <xsl:value-of select="$shoptableprefix"/>product SET active = 0 
              WHERE id_product = :id_product
            </query>
            <params>
              <xsl:copy-of select="id_product"/>
            </params> 
          </action>
        </delete_product>        
      </xsl:if>
  </xsl:for-each>
	<xsl:for-each select="shop_subproduct/row">
    <xsl:if test="not(key('pricelist_product',reference)) or not(key('pricelist_product',reference)/WebArticleRelations/relations/relation/@type='main')">
       <delete_product>
        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="$shop"/></connection>
          <query>
            DELETE FROM <xsl:value-of select="$shoptableprefix"/>product_attribute 
            WHERE id_product_attribute = :id_product_attribute
          </query>
          <params>
            <xsl:copy-of select="id_product_attribute"/>
          </params> 
        </action>
      </delete_product>        
      <delete_product_combination>
        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="$shop"/></connection>
          <query>
            DELETE FROM <xsl:value-of select="$shoptableprefix"/>product_attribute_combination 
            WHERE id_product_attribute = :id_product_attribute
          </query>
          <params>
            <xsl:copy-of select="id_product_attribute"/>
          </params> 
        </action>
      </delete_product_combination>        
    </xsl:if>
 </xsl:for-each>

</xsl:template>





<!-- ***********************************************************************************************
      shop_storedata_product
-->

<xsl:template match="job[@cmd='shop_storedata_product']">
      <!-- here new streaming update -->
      <action cmd="recordset" stream="product">
        <connection>impeng</connection>
        <query>
          SELECT WebArticleCode, WebArticleManufacturer, WebArticleEntryDate, 
            WebArticleLastChecked, WebArticleTitle, WebArticleTitleLang, WebArticleSpecification,
            WebArticlePrice, WebArticleCategoryShopRef, WebArticleTax, WebArticleStock,
            WebArticleProductCode, WebArticleManufacturerCode, WebArticleWeight, WebArticleContact,
            WebArticleFeatures, WebArticleRelations, WebArticleShortDescription, ContactID,
            WebArticlePromoSI, WebArticleEan 
          FROM tbldata_webarticle INNER JOIN tbltype_contact ON (ContactLabel = WebArticleContact and ContactShop = WebArticleShop)
          WHERE WebArticleShop = :shop 
        </query>
        <params>
          <shop><xsl:value-of select="shop"/></shop>
        </params>
        <xmlfields><WebArticleTitleLang/><WebArticleSpecification/><WebArticleShortDescription/><WebArticleRelations/><WebArticleFeatures/></xmlfields>
      </action>

      <job cmd="shop_storedata_product_stream" while="product">
        <xsl:copy-of select="shop"/>
        <xsl:copy-of select="shoptableprefix"/>
        <xsl:copy-of select="shop_supplier_ids"/>
        <xsl:copy-of select="lang"/>
        <xsl:copy-of select="languages"/>
        <xsl:copy-of select="pricelist_taxgroup"/>
        <xsl:copy-of select="pricelist_feature"/>
        <pricelist_product>
          <action cmd="recordset" stream="product"/>
        </pricelist_product>        
      </job>

</xsl:template>
<!-- ***********************************************************************************************
      shop_storedata_product
-->

<xsl:template match="job[@cmd='shop_storedata_product_stream']">
  <xsl:variable name="shop"><xsl:value-of select="shop"/></xsl:variable>
  <xsl:variable name="shoptableprefix"><xsl:value-of select="shoptableprefix"/></xsl:variable>
  <xsl:variable name="lang"><xsl:value-of select="lang"/></xsl:variable>

  <xsl:for-each select="pricelist_product/row">
      <!-- xsl:if test="not(WebArticleRelations/relations/relation[@type='main'] and (WebArticleRelations/relations/relation[@type='main']/productcode != WebArticleCode))" -->

        <job engine="engineImpEngShopPrestashop.xsl" cmd="shop_storedata_description">
          <shop><xsl:value-of select="$shop"/></shop>
          <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>
          <lang><xsl:value-of select="$lang"/></lang>
          <xsl:copy-of select="../../languages"/>

    
          <xsl:copy-of select="*"/>

          <pricelist_features>
            <xsl:for-each select="WebArticleFeatures/features/feature">
              <xsl:if test="key('pricelist_feature',@type)/featmapShopFeatureID">
                <feature>
                  <id><xsl:value-of select="key('pricelist_feature',@type)/featmapShopFeatureID"/></id>
                  <value><xsl:value-of select="."/></value>
                </feature>
              </xsl:if>
            </xsl:for-each>   
          </pricelist_features>
          

          <insert_product>
            <action cmd="recordset">
              <connection>shop_<xsl:value-of select="$shop"/></connection>
              <query>
                INSERT INTO <xsl:value-of select="$shoptableprefix"/>product 
                  (id_product,
                  id_supplier, id_manufacturer, 
                  <xsl:choose>
                    <xsl:when test="../../pricelist_taxgroup/Field"><xsl:value-of select="../../pricelist_taxgroup/Field"/></xsl:when>
                    <xsl:otherwise>id_tax</xsl:otherwise>
                  </xsl:choose>                                      
                  , 
                  id_category_default, quantity, price, 
                  reference, supplier_reference, location,
                  weight, on_sale, ean13,
                  date_add, date_upd, active) 
                SELECT
                  (select p.id_product from <xsl:value-of select="$shoptableprefix"/>product p 
                   where p.reference = :WebArticleCode limit 0,1), 
                  s.id_supplier, m.id_manufacturer, 
                  <xsl:choose>
                    <xsl:when test="../../pricelist_taxgroup/Field">
                      (select tr.id_tax_rules_group 
                      from ps_tax_rules_group trg inner join ps_tax_rule tr on (tr.id_tax_rules_group = trg.id_tax_rules_group) inner join  ps_country  c on (c.id_country  = tr.id_country)
                      where iso_code = 'NL' and tr.id_tax = t.id_tax limit 0,1)
                    </xsl:when>
                    <xsl:otherwise>t.id_tax</xsl:otherwise>
                  </xsl:choose>                                      
                  ,
                  :WebArticleCategoryShopRef, :WebArticleStock, :WebArticlePrice,
                  :WebArticleCode, :WebArticleProductCode, :WebArticleManufacturerCode,
                  :WebArticleWeight, :OnSale, :WebArticleEan,
                  :WebArticleEntryDate, :WebArticleLastChecked, 1
                FROM 
                  <xsl:value-of select="$shoptableprefix"/>manufacturer m, <xsl:value-of select="$shoptableprefix"/>tax t,
                  <xsl:value-of select="$shoptableprefix"/>supplier s
                WHERE
                  m.name = :WebArticleManufacturer
                  and t.rate = :WebArticleTax
                  and s.name = :SupplierCode
                ON DUPLICATE KEY UPDATE 
                  id_product = if( :parent != '' and <xsl:value-of select="$shoptableprefix"/>product.date_upd = VALUES(date_upd), id_product, last_insert_id(id_product) ), 
                  id_supplier = VALUES(id_supplier),
                  id_manufacturer = VALUES(id_manufacturer),
                   <xsl:choose>
                    <xsl:when test="../../pricelist_taxgroup/Field"><xsl:value-of select="../../pricelist_taxgroup/Field"/> = VALUES(<xsl:value-of select="../../pricelist_taxgroup/Field"/>)</xsl:when>
                    <xsl:otherwise>id_tax = VALUES(id_tax)</xsl:otherwise>
                  </xsl:choose>  
                  ,
                  
                  id_category_default = VALUES(id_category_default),
                  quantity = VALUES(quantity),
                  price =  if( :parent != '' and <xsl:value-of select="$shoptableprefix"/>product.date_upd = VALUES(date_upd), price, VALUES(price)),
                  supplier_reference = VALUES(supplier_reference),
                  location = VALUES(location),
                  weight = VALUES(weight),
                  on_sale = VALUES(on_sale),
                  ean13 = VALUES(ean13),
                  date_add = VALUES(date_add),
                  date_upd = VALUES(date_upd),
                  active = 1
              </query>
              <params>
                <xsl:choose>
                  <xsl:when test="WebArticleRelations/relations/relation[@type='main']/productcode">
                    <WebArticleCode><xsl:value-of select="WebArticleRelations/relations/relation[@type='main']/productcode"/></WebArticleCode>
                    <WebArticleProductCode/>
                    <parent><xsl:value-of select="WebArticleCode"/></parent>
                  </xsl:when>
                  <xsl:otherwise>                  
                    <xsl:copy-of select="WebArticleCode"/>
                    <xsl:copy-of select="WebArticleProductCode"/>
                    <parent/>
                  </xsl:otherwise>
                </xsl:choose>                  
                <xsl:copy-of select="WebArticleCategoryShopRef"/>
                <xsl:copy-of select="WebArticleTax"/>
                <xsl:copy-of select="WebArticleStock"/>
                <xsl:copy-of select="WebArticlePrice"/>
                <WebArticlePrice><xsl:choose><xsl:when test="WebArticlePromoSI &gt; 0"><xsl:value-of select="WebArticlePromoSI div (1 + WebArticleTax div 100)"/></xsl:when><xsl:otherwise><xsl:value-of select="WebArticlePrice"/></xsl:otherwise></xsl:choose></WebArticlePrice>
                <xsl:copy-of select="WebArticleWeight"/>
                <OnSale><xsl:choose><xsl:when test="WebArticlePromoSI &gt; 0">1</xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose></OnSale>
                <WebArticleEan><xsl:value-of select="translate(WebArticleEan,' ','')"/></WebArticleEan>                
                <xsl:copy-of select="WebArticleManufacturerCode"/>
                <xsl:copy-of select="WebArticleEntryDate"/>
                <xsl:copy-of select="WebArticleLastChecked"/>
                <xsl:copy-of select="WebArticleManufacturer"/>
                <SupplierCode><xsl:value-of select="ContactID"/></SupplierCode>
              </params>
            </action>
          </insert_product>
        </job>
      <!-- /xsl:if -->
      <!-- relation -->
      <xsl:if test="WebArticleRelations/relations/relation[@type='main']">
        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="$shop"/></connection>
          <query>
            insert into <xsl:value-of select="$shoptableprefix"/>attribute 
              (id_attribute, id_attribute_group, color)
            VALUES (
              (select a.id_attribute from <xsl:value-of select="$shoptableprefix"/>attribute a 
                inner join <xsl:value-of select="$shoptableprefix"/>attribute_lang al 
                on (a.id_attribute = al.id_attribute )
               where al.name = :name and a.id_attribute_group = (select id_attribute_group from <xsl:value-of select="$shoptableprefix"/>attribute_group_lang where name = :groupname limit 0,1) 
               limit 0,1 )
               , (select id_attribute_group from <xsl:value-of select="$shoptableprefix"/>attribute_group_lang where name = :groupname limit 0,1),'' )
            on duplicate key update id_attribute = last_insert_id(id_attribute);
          </query>
          <params>
            <groupname><xsl:value-of select="WebArticleRelations/relations/relation[@type='main']/attribute/@type"/></groupname>
            <name><xsl:value-of select="WebArticleRelations/relations/relation[@type='main']/attribute"/></name>
          </params>
        </action>
        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="$shop"/></connection>
          <query>
            insert ignore into <xsl:value-of select="$shoptableprefix"/>attribute_lang 
              (id_attribute, id_lang, name)
            select last_insert_id(), id_lang, :name
            from <xsl:value-of select="$shoptableprefix"/>lang where active=1
         </query>
          <params>
            <name><xsl:value-of select="WebArticleRelations/relations/relation[@type='main']/attribute"/></name>
          </params>
        </action>

        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="$shop"/></connection>
          <query>
            INSERT INTO <xsl:value-of select="$shoptableprefix"/>product_attribute 
              (id_product_attribute, 
              id_product, 
              reference, supplier_reference, 
              location, ean13, price, 
              quantity, weight, default_on )
            SELECT
              (SELECT id_product_attribute FROM <xsl:value-of select="$shoptableprefix"/>product_attribute 
                 WHERE reference = :WebArticleCode ),
              p.id_product, 
              :WebArticleCode, :WebArticleProductCode,
              :WebArticleManufacturerCode, '', (:WebArticlePrice - p.price) * (1 + :WebArticleTax / 100),
              :WebArticleStock, :WebArticleWeight - p.weight, 0
            FROM <xsl:value-of select="$shoptableprefix"/>product p WHERE p.reference = :parent 
              
            ON DUPLICATE KEY UPDATE 
              id_product_attribute = last_insert_id(id_product_attribute), 
              supplier_reference = VALUES(supplier_reference),
              location = VALUES(location),
              ean13 = VALUES(ean13),
              price = VALUES(price),
              quantity = VALUES(quantity),
              weight = VALUES(weight),
              default_on = VALUES(default_on)
          </query>
          <params>
            <xsl:copy-of select="WebArticleCode"/>
            <xsl:copy-of select="WebArticleProductCode"/>
            <xsl:copy-of select="WebArticleTax"/>
            <xsl:copy-of select="WebArticleStock"/>
            <xsl:copy-of select="WebArticlePrice"/>
            <xsl:copy-of select="WebArticleWeight"/>
            <xsl:copy-of select="WebArticleManufacturerCode"/>
            <parent><xsl:value-of select="WebArticleRelations/relations/relation[@type='main']/productcode"/></parent>
          </params>
        </action>

        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="$shop"/></connection>
          <query>
            INSERT ignore INTO <xsl:value-of select="$shoptableprefix"/>product_attribute_combination 
              (id_attribute, id_product_attribute)
            SELECT
              id_attribute, last_insert_id() FROM <xsl:value-of select="$shoptableprefix"/>attribute_lang 
            WHERE name = :name
          </query>
          <params>
            <name><xsl:value-of select="WebArticleRelations/relations/relation[@type='main']/attribute"/></name>
          </params>
        </action>

      </xsl:if>        

  </xsl:for-each>

</xsl:template>

<!-- ***********************************************************************************************
      shop_storedata_description

      Store the data to the live shop. The product table is already inserted or updated and now
      to continue with the description etc.
      
      INPUT
      id_product: when previous action was to update the product
      insert_product/insertid: when previous action was to insert the product
     

-->


<xsl:template match="job[@cmd='shop_storedata_description']">
  <xsl:variable name="shop"><xsl:value-of select="shop"/></xsl:variable>
  <xsl:variable name="shoptableprefix"><xsl:value-of select="shoptableprefix"/></xsl:variable>
  <xsl:variable name="id_product"><xsl:value-of select="insert_product/insertid"/></xsl:variable>

  
  
  
  <xsl:if test="insert_product/insertid &gt; 0">
    
    <xsl:copy-of select="insert_product"/>
    <xsl:copy-of select="update_product"/>
      
    <!-- description -->
    <xsl:call-template name="shop_storedata_productdescription">
      <xsl:with-param name="id_product"><xsl:value-of select="$id_product"/></xsl:with-param>
      <xsl:with-param name="lang"><xsl:copy-of select="languages/language[1]//lang"/></xsl:with-param>
      <xsl:with-param name="lang_id"><xsl:copy-of select="languages/language[1]/id"/></xsl:with-param>
    </xsl:call-template>    
  
    <xsl:if test="languages/language[2]">
      <xsl:call-template name="shop_storedata_productdescription">
        <xsl:with-param name="id_product"><xsl:value-of select="$id_product"/></xsl:with-param>
        <xsl:with-param name="lang"><xsl:copy-of select="languages/language[2]//lang"/></xsl:with-param>
        <xsl:with-param name="lang_id"><xsl:copy-of select="languages/language[2]/id"/></xsl:with-param>
      </xsl:call-template>    
    </xsl:if>
    
    <xsl:if test="languages/language[3]">
      <xsl:call-template name="shop_storedata_productdescription">
        <xsl:with-param name="id_product"><xsl:value-of select="$id_product"/></xsl:with-param>
        <xsl:with-param name="lang"><xsl:copy-of select="languages/language[3]//lang"/></xsl:with-param>
        <xsl:with-param name="lang_id"><xsl:copy-of select="languages/language[3]/id"/></xsl:with-param>
      </xsl:call-template>    
    </xsl:if>

    <xsl:if test="languages/language[4]">
      <xsl:call-template name="shop_storedata_productdescription">
        <xsl:with-param name="id_product"><xsl:value-of select="$id_product"/></xsl:with-param>
        <xsl:with-param name="lang"><xsl:copy-of select="languages/language[4]//lang"/></xsl:with-param>
        <xsl:with-param name="lang_id"><xsl:copy-of select="languages/language[4]/id"/></xsl:with-param>
      </xsl:call-template>    
    </xsl:if>
      
    <!-- category -->
    
    <delete_productcategory>
      <action cmd="recordset">
        <connection>shop_<xsl:value-of select="$shop"/></connection>
        <query>
          DELETE FROM <xsl:value-of select="$shoptableprefix"/>category_product 
          WHERE id_category != 1 AND id_product = :id_product
        </query>
        <params>
          <id_product><xsl:value-of select="$id_product"/></id_product>
        </params>
      </action>
    </delete_productcategory>
    <insert_productcategory>
      <action cmd="recordset">
        <connection>shop_<xsl:value-of select="$shop"/></connection>
        <query>
          INSERT <xsl:value-of select="$shoptableprefix"/>category_product (id_category, id_product, position)
          VALUES (:WebArticleCategoryShopRef, :id_product, 1)
        </query>
        <params>
          <id_product><xsl:value-of select="$id_product"/></id_product>
          <xsl:copy-of select="WebArticleCategoryShopRef"/>
        </params>
      </action>
    </insert_productcategory>
  
    <xsl:for-each select="pricelist_features/feature">
        
        <job cmd="shop_feature_validate">
          <shop><xsl:value-of select="$shop"/></shop>
          <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>
          <id_product><xsl:value-of select="$id_product"/></id_product>
          <xsl:copy-of select="../../lang"/>
          <xsl:copy-of select="id"/>
          <xsl:copy-of select="value"/>
          <action cmd="recordset">
            <connection>shop_<xsl:value-of select="$shop"/></connection>
            <query>
              SELECT value
              FROM 
                <xsl:value-of select="$shoptableprefix"/>feature_product as f
                INNER JOIN <xsl:value-of select="$shoptableprefix"/>feature_value as v ON (f.id_feature_value = v.id_feature_value)
                INNER JOIN <xsl:value-of select="$shoptableprefix"/>feature_value_lang as l ON (v.id_feature_value = l.id_feature_value and l.id_lang = :lang)  
              WHERE
                id_product = :id_product
            </query>
            <params>
              <id_product><xsl:value-of select="$id_product"/></id_product>
              <xsl:copy-of select="../../lang"/>
            </params>
          </action>    
        </job>
    </xsl:for-each>
  </xsl:if>  

</xsl:template>


<!-- **********************************************************************************************
      cleanMeta
-->

<xsl:template name="shop_storedata_productdescription">
  <xsl:param name="lang_id"/>
  <xsl:param name="lang"/>
  <xsl:param name="id_product"/>


  <xsl:variable name="title">
      <xsl:choose>
      <xsl:when test="WebArticleTitleLang/info[@lang=$lang]">
        <xsl:copy-of select="WebArticleTitleLang/info[@lang=$lang]/node()"/>
      </xsl:when>
      <xsl:when test="WebArticleTitleLang/info[not(@lang)]">
        <xsl:copy-of select="WebArticleTitleLang/info[not(@lang)]/node()"/>
      </xsl:when>
      <xsl:when test="WebArticleTitleLang/info[@lang = 'nl']">
        <xsl:copy-of select="WebArticleTitleLang/info[@lang = 'nl']/node()"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:copy-of select="WebArticleTitle/node()"/>
      </xsl:otherwise>
    </xsl:choose>              

  
    </xsl:variable>  
  
    
  <insert_productdescription>
    <action cmd="recordset">
      <connection>shop_<xsl:value-of select="shop"/></connection>
      <query>
        INSERT INTO <xsl:value-of select="shoptableprefix"/>product_lang
          (id_product, id_lang, description,
          description_short, name, link_rewrite,
          meta_description, meta_keywords, meta_title
           ) 
        VALUES
          (:id_product, :lang, :WebArticleSpecification,
          :WebArticleShortDescription, :WebArticleTitle, :rewrite,
          :meta_description, :meta_keywords, :meta_title
          )	          
        ON DUPLICATE KEY UPDATE 
          description = :WebArticleSpecification,
          description_short = :WebArticleShortDescription,
          name = :WebArticleTitle,
          link_rewrite = :rewrite,
          meta_description = :meta_description, 
          meta_keywords = :meta_keywords,
          meta_title = :meta_title
      </query>
      <params>
        <id_product><xsl:value-of select="$id_product"/></id_product>
        <WebArticleTitle><xsl:copy-of select="$title"/></WebArticleTitle>        
        <WebArticleSpecification>
          <xsl:choose>
            <xsl:when test="WebArticleSpecification/info[@lang=$lang]/node()">
              <xsl:copy-of select="WebArticleSpecification/info[@lang=$lang]/node()"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:copy-of select="WebArticleSpecification/info[not(@lang) or @lang='nl']/node()"/>
            </xsl:otherwise>
          </xsl:choose>                                  
        </WebArticleSpecification>        
        <WebArticleShortDescription>
          <xsl:choose>
            <xsl:when test="WebArticleShortDescription/info[@lang=$lang]/node()">
              <xsl:copy-of select="WebArticleShortDescription/info[@lang=$lang]/node()"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:copy-of select="WebArticleShortDescription/info[not(@lang) or @lang='nl']/node()"/>
            </xsl:otherwise>
          </xsl:choose>      
        </WebArticleShortDescription>        
        <lang><xsl:value-of select="$lang_id"/></lang>        
        <rewrite><xsl:value-of select="translate(normalize-space(translate($title,translate($title,'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_',''),' ')),' ','-')"/></rewrite>
        <meta_description><xsl:call-template name="clean_meta">
          <xsl:with-param name="raw"><xsl:value-of select="$title"/><xsl:text> </xsl:text><xsl:value-of select="WebArticleManufacturer"/><xsl:text> </xsl:text><xsl:value-of select="WebArticleManufacturerCode"/></xsl:with-param>
        </xsl:call-template></meta_description>
        <meta_keywords><xsl:call-template name="clean_meta">
          <xsl:with-param name="raw"><xsl:value-of select="translate(concat($title,',',WebArticleManufacturer,',',WebArticleManufacturerCode),' ',',')"/>,<xsl:value-of select="WebArticleManufacturer"/>,<xsl:value-of select="WebArticleManufacturerCode"/></xsl:with-param>
        </xsl:call-template></meta_keywords>
        <meta_title><xsl:call-template name="clean_meta">
          <xsl:with-param name="raw"><xsl:value-of select="$title"/><xsl:text> </xsl:text><xsl:value-of select="WebArticleManufacturer"/><xsl:text> </xsl:text><xsl:value-of select="WebArticleManufacturerCode"/></xsl:with-param>
        </xsl:call-template></meta_title>
      </params>
    </action>
  </insert_productdescription>
</xsl:template>


<!-- **********************************************************************************************
      cleanMeta
-->

<xsl:template name="clean_meta">
  <xsl:param name="raw"/>
  <xsl:value-of select="translate($raw,';=#{}&lt;&gt;','-------')"/>
</xsl:template>




<!-- **********************************************************************************************
      shop_feature_validate
-->

<xsl:template match="job[@cmd='shop_feature_validate']">

  <xsl:if test="not(value = row/value)">
    <job cmd="shop_feature_product">
      <xsl:copy-of select="shop"/>
      <xsl:copy-of select="shoptableprefix"/>
      <xsl:copy-of select="id_product"/>
      <xsl:copy-of select="id"/>
      <job cmd="shop_feature_value">
        <xsl:copy-of select="shop"/>
        <xsl:copy-of select="shoptableprefix"/>
        <xsl:copy-of select="id"/>
        <xsl:copy-of select="value"/>
        <xsl:copy-of select="lang"/>
        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="shop"/></connection>
          <rowtag/>
          <query>select id_feature_value from <xsl:value-of select="shoptableprefix"/>feature_value_lang where id_lang = :lang and value = :value limit 0,1 </query>
          <params>
            <xsl:copy-of select="value"/>
            <xsl:copy-of select="lang"/>
          </params>
        </action>    
      </job>
    </job>
  </xsl:if>

</xsl:template>


<!-- **********************************************************************************************
      shop_feature_product
-->

<xsl:template match="job[@cmd='shop_feature_product']">
  <xsl:variable name="id_feature_value">
    <xsl:choose>
      <xsl:when test="id_feature_value"><xsl:value-of select="id_feature_value"/></xsl:when>
      <xsl:otherwise><xsl:value-of select="insertid"/></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <action cmd="recordset">
    <connection>shop_<xsl:value-of select="shop"/></connection>
    <rowtag/>
    <query>
      INSERT INTO <xsl:value-of select="shoptableprefix"/>feature_product (id_feature, id_product, id_feature_value)
      VALUES (:id, :id_product, :id_feature_value)
      ON DUPLICATE KEY UPDATE 
        id_feature_value = :id_feature_value
    </query>
    <params>
      <xsl:copy-of select="id"/>
      <xsl:copy-of select="id_product"/>
      <id_feature_value><xsl:value-of select="$id_feature_value"/></id_feature_value>
    </params>
  </action> 
</xsl:template>

<!-- **********************************************************************************************
      shop_feature_value
-->

<xsl:template match="job[@cmd='shop_feature_value']">
  <xsl:choose>
    <xsl:when test="id_feature_value">
      <xsl:copy-of select="id_feature_value"/>
    </xsl:when>
    <xsl:otherwise>
      <job cmd="shop_feature_value_lang">
        <xsl:copy-of select="shop"/>
        <xsl:copy-of select="shoptableprefix"/>
        <xsl:copy-of select="lang"/>
        <xsl:copy-of select="value"/>
        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="shop"/></connection>
          <rowtag/>
          <query>
            INSERT <xsl:value-of select="shoptableprefix"/>feature_value (id_feature)
            VALUES (:id)
          </query>
          <params>
            <xsl:copy-of select="id"/>
          </params>
        </action>    
      </job>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- **********************************************************************************************
      shop_feature_value
-->

<xsl:template match="job[@cmd='shop_feature_value_lang']">

  <id_feature_value><xsl:value-of select="insertid"/></id_feature_value>

  <action cmd="recordset">
    <connection>shop_<xsl:value-of select="shop"/></connection>
    <query>
      INSERT <xsl:value-of select="shoptableprefix"/>feature_value_lang (id_feature_value, id_lang, value)
      VALUES (:insertid, :lang, :value)
    </query>
    <params>
      <xsl:copy-of select="insertid"/>
      <xsl:copy-of select="lang"/>
      <xsl:copy-of select="value"/>
    </params>
  </action>    

</xsl:template>
<!--    
	<xsl:for-each select="shop_product/row">
      <xsl:if test="not(key('pricelist_product',reference))">
        <delete_productcategory>
          <action cmd="recordset">
            <connection>shop_<xsl:value-of select="$shop"/></connection>
            <query>DELETE FROM ps_category_product WHERE id_product = :id_product</query>
            <params>
              <xsl:copy-of select="id_product"/>
            </params> 
          </action>
        </delete_productcategory>
        <delete_productdescription>
          <action cmd="recordset">
            <connection>shop_<xsl:value-of select="$shop"/></connection>
            <query>DELETE FROM ps_product_lang WHERE id_product = :id_product</query>
            <params>
              <xsl:copy-of select="id_product"/>
            </params> 
          </action>
        </delete_productdescription>
        <delete_product>
          <action cmd="recordset">
            <connection>shop_<xsl:value-of select="$shop"/></connection>
            <query>DELETE FROM ps_product WHERE id_product = :id_product</query>
            <params>
              <xsl:copy-of select="id_product"/>
            </params> 
          </action>
        </delete_product>        
      </xsl:if>
 </xsl:for-each>
-->    
      


<!-- ***********************************************************************************************
      shop_validate
      
      Find nl language id then also validate = true

      INPUT
      shop: shop name
      
      OUTPUT:
      lang: id for nl language
      validate: true when language has been found

-->

<xsl:template match="job[@cmd='shop_taxgroup']">
  
   <pricelist_taxgroup>
      <action cmd="recordset">
        <connection>shop_<xsl:value-of select="shop"/></connection>
        <rowtag/>
        <query>
          show columns from ps_product where Field = 'id_tax_rules_group'
        </query>      
      </action>
    </pricelist_taxgroup>


</xsl:template>


<!-- ***********************************************************************************************
      shop_validate
      
      Find nl language id then also validate = true

      INPUT
      shop: shop name
      
      OUTPUT:
      lang: id for nl language
      validate: true when language has been found

-->

<xsl:template match="job[@cmd='shop_validate']">

  <job cmd="shop_validate_check">
    <xsl:copy-of select="shop"/>
    <xsl:copy-of select="shoptableprefix"/>
    <pricelist_tax>
      <action cmd="recordset">
        <connection>impeng</connection>
        <rowtag/>
        <query>
          SELECT WebArticleTax tax FROM tbldata_webarticle WHERE WebArticleShop = :shop GROUP BY WebArticleTax 
        </query>
        <params>
          <xsl:copy-of select="shop"/>
        </params>
      </action>
    </pricelist_tax>
  </job>  
</xsl:template>



<xsl:template match="job[@cmd='shop_validate_check']">

  <xsl:if test="pricelist_tax/tax">
    <action cmd="recordset">
      <connection>shop_<xsl:value-of select="shop"/></connection>
      <rowtag/>
      <query>
        SELECT cfg.value as lang, 'true' as validate, count(1) as chkall  
        FROM <xsl:value-of select="shoptableprefix"/>configuration cfg,<xsl:value-of select="shoptableprefix"/>tax 
        WHERE cfg.name = 'PS_LANG_DEFAULT' and rate in (
        <xsl:for-each select="pricelist_tax/tax"><xsl:value-of select="."/><xsl:if test="position() != last()">,</xsl:if></xsl:for-each>                
        ) 
        group by lang, validate
        having chkall = :chkall
      </query>
      <params>
        <chkall><xsl:value-of select="count(pricelist_tax/tax)"/></chkall>        
      </params>      
    </action>
  </xsl:if>  

</xsl:template>



<!-- ***********************************************************************************************
      shop_languages
      
      INPUT
      languages: nodeset for requested languages
-->

<xsl:template match="job[@cmd='shop_languages']">

  <languages>
    <action cmd="recordset">
      <connection>shop_<xsl:value-of select="shop"/></connection>
      <rowtag>language</rowtag>    
      <query>
        SELECT id_lang id, iso_code lang
        FROM <xsl:value-of select="shoptableprefix"/>lang
        WHERE active = 1
      </query>
      <params/>
    </action>
  </languages>  
</xsl:template>


<!-- ***********************************************************************************************
      shop_getdata
      
      Prepare the data to analyse which tables need updates

INSERT INTO "ps_category_product" ("id_category", "id_product", "position") VALUES
	('4','11','2');

INSERT INTO "ps_product" ("id_product", "id_supplier", "id_manufacturer", "id_tax", "id_category_default", "id_color_default", "on_sale", "ean13", "ecotax", "quantity", "price", "wholesale_price", "reduction_price", "reduction_percent", "reduction_from", "reduction_to", "reference", "supplier_reference", "location", "weight", "out_of_stock", "quantity_discount", "customizable", "uploadable_files", "text_fields", "active", "indexed", "date_add", "date_upd") VALUES
	('11','0','0','0','4','0',0,'','0','1','12345','0','0','0','2010-01-22','2010-01-22','','','','0','2',0,0,0,0,1,1,'2010-01-22 23:22:27','2010-01-22 23:22:27');

INSERT INTO "ps_product_lang" ("id_product", "id_lang", "description", "description_short", "link_rewrite", "meta_description", "meta_keywords", "meta_title", "name", "available_now", "available_later") VALUES
	('11','1','','','shopimportq01','','','','ShopimportQ01','','');
INSERT INTO "ps_product_lang" ("id_product", "id_lang", "description", "description_short", "link_rewrite", "meta_description", "meta_keywords", "meta_title", "name", "available_now", "available_later") VALUES
	('11','3','','','shopimportq01','','','','ShopimportQ01','','');

INSERT INTO "ps_search_index" ("id_product", "id_word", "weight") VALUES
	(11,1129,6);
INSERT INTO "ps_search_index" ("id_product", "id_word", "weight") VALUES
	(11,350,3);
INSERT INTO "ps_search_index" ("id_product", "id_word", "weight") VALUES
	(11,1130,6);
INSERT INTO "ps_search_index" ("id_product", "id_word", "weight") VALUES
	(11,1131,3);

INSERT INTO "ps_search_word" ("id_word", "id_lang", "word") VALUES
	('1131','3','laptops');
INSERT INTO "ps_search_word" ("id_word", "id_lang", "word") VALUES
	('1130','3','shopimportq01');
INSERT INTO "ps_search_word" ("id_word", "id_lang", "word") VALUES
	('1129','1','shopimportq01');

-->

<xsl:template match="job[@cmd='shop_getdata']">
  <xsl:variable name="shop"><xsl:value-of select="shop"/></xsl:variable>
  <xsl:variable name="shoptableprefix"><xsl:value-of select="shoptableprefix"/></xsl:variable>



  <xsl:if test="not(shop_supplier_ids/supplier_id)">
    <action cmd="error">
      <message>no supplier ids </message>
    </action>
  </xsl:if>

  <pricelist_manufacturer>
    <action cmd="recordset">
      <connection>impeng</connection>
      <query>
        SELECT WebArticleManufacturer FROM tbldata_webarticle
        WHERE WebArticleShop = :shop GROUP BY WebArticleManufacturer
      </query>
      <params>
        <shop><xsl:value-of select="$shop"/></shop>
      </params>
    </action>
  </pricelist_manufacturer>
  <shop_manufacturer>
    <action cmd="recordset">
      <connection>shop_<xsl:value-of select="$shop"/></connection>
      <query>
        SELECT id_manufacturer, name FROM <xsl:value-of select="$shoptableprefix"/>manufacturer
      </query>
    </action>
  </shop_manufacturer>
  
  <pricelist_supplier>
    <action cmd="recordset">
      <connection>impeng</connection>
      <query>
        SELECT ContactID SupplierCode FROM tbltype_contact WHERE ContactShop = :shop 
      </query>
      <params>
        <shop><xsl:value-of select="$shop"/></shop>
      </params>
    </action>
  </pricelist_supplier>
  <shop_supplier>
    <action cmd="recordset">
      <connection>shop_<xsl:value-of select="$shop"/></connection>
      <query>
        SELECT name FROM <xsl:value-of select="$shoptableprefix"/>supplier
      </query>
    </action>
  </shop_supplier>

  
  <pricelist_product>
    <action cmd="recordset">
      <connection>impeng</connection>
      <query>
        SELECT WebArticleCode
        FROM tbldata_webarticle
        WHERE WebArticleShop = :shop 
      </query>
      <params>
        <shop><xsl:value-of select="$shop"/></shop>
      </params>
      <xmlfields><WebArticleTitleLang/><WebArticleSpecification/><WebArticleShortDescription/><WebArticleRelations/><WebArticleFeatures/></xmlfields>
    </action>
  </pricelist_product>  
  <shop_product>
    <action cmd="recordset">
      <connection>shop_<xsl:value-of select="$shop"/></connection>
      <query>
        SELECT p.id_product, reference, p.active 
        FROM <xsl:value-of select="$shoptableprefix"/>product p 
          inner join <xsl:value-of select="$shoptableprefix"/>supplier s on (s.id_supplier = p.id_supplier)
          LEFT JOIN <xsl:value-of select="$shoptableprefix"/>product_lang d ON (p.id_product = d.id_product and d.id_lang = :lang)
        WHERE 
          s.name in (<xsl:for-each select="shop_supplier_ids/supplier_id">'<xsl:value-of select="."/>'<xsl:if test="position() != last()">,</xsl:if></xsl:for-each> )
      </query>
      <params>
        <xsl:copy-of select="lang"/>
      </params>
    </action>
  </shop_product>
  <shop_subproduct>
    <action cmd="recordset">
      <connection>shop_<xsl:value-of select="$shop"/></connection>
      <query>
        SELECT a.id_product_attribute, a.reference
        FROM <xsl:value-of select="$shoptableprefix"/>product_attribute a
          INNER JOIN <xsl:value-of select="$shoptableprefix"/>product p on (p.id_product = a.id_product)
          inner join <xsl:value-of select="$shoptableprefix"/>supplier s on (s.id_supplier = p.id_supplier)
        WHERE s.name in (<xsl:for-each select="shop_supplier_ids/supplier_id">'<xsl:value-of select="."/>'<xsl:if test="position() != last()">,</xsl:if></xsl:for-each> )
      </query>
    </action>
  </shop_subproduct>
</xsl:template>


<xsl:template match="job[@cmd='shop_getdata_product']">

  <pricelist_feature>
    <action cmd="recordset">
      <connection>impeng</connection>
      <query>
        SELECT featmapShopFeatureID, featmapFeatureType
        FROM feature_mapping WHERE featmapShop = :shop 
      </query>
      <params>
        <shop><xsl:value-of select="shop"/></shop>
      </params>
    </action>
  </pricelist_feature>  
  
</xsl:template>











<!--
      #############################################################################################
      #############################################################################################
      Image FILES from shopimport server to Prestashop
 -->


<!-- **********************************************************************************************
      shop_media

      Get the available image files from the FTP and generate a list of all images from the database
      
      Compare to find what new images are needed.
      
      If needed then copy the image, make the compresesed versions.
      
-->

<xsl:template match="job[@cmd='shop_media']">
  <xsl:variable name="shoptableprefix">
    <xsl:choose>
      <xsl:when test="shopconfig/shoptableprefix"><xsl:value-of select="shopconfig/shoptableprefix"/></xsl:when>
      <xsl:otherwise>ps_</xsl:otherwise>
    </xsl:choose>
  </xsl:variable>


  <job engine="engineImpEngMedia.xsl" cmd="media_pricelist_update">
    <xsl:copy-of select="shop"/>
  </job>


    
  <job engine="engineImpEngShopPrestashop.xsl" cmd="shop_media_show">
    <job engine="engineImpEngShopPrestashop.xsl" cmd="shop_media_preparetransfer">
      <xsl:copy-of select="shop"/>
      <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>
      <xsl:copy-of select="tmpPath"/>
      <xsl:copy-of select="supplierImagesPath"/>
      <xsl:copy-of select="shopconfig/imagespath"/>
  
      <job cmd="shop_validate">
        <xsl:copy-of select="shop"/>
        <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>
      </job>
  
  
      <job engine="engineImpEngShopPrestashop.xsl" cmd="shop_media_database">
        <xsl:copy-of select="shop"/>

        <job cmd="shop_supplier_ids">
          <xsl:copy-of select="shop"/>
        </job>

        <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>
        <job cmd="shop_validate">
          <xsl:copy-of select="shop"/>
          <shoptableprefix><xsl:value-of select="$shoptableprefix"/></shoptableprefix>
        </job>
      </job>
      
      <job engine="engineImpEngMedia.xsl" cmd="media_pricelist_getdataraw">
        <xsl:copy-of select="shop"/>
      </job>
  
      <job engine="engineImpEngShopPrestashop.xsl" cmd="shop_media_files">
        <xsl:copy-of select="shopconfig/imagespath"/>
      </job>
    </job>
  </job>

</xsl:template>


<!-- **********************************************************************************************
      show

-->

<xsl:template match="job[@cmd='shop_media_show']">
  <xsl:apply-templates select="//message"/>  
  <br/><br/>
  Upload shop images: <xsl:value-of select="count(upload_image)"/><br/>
  Upload shop thumbs added: <xsl:value-of select="count(upload_thumb)"/><br/>
  
  <xsl:if test="//error">
    Upload errors: <xsl:value-of select="count(//error)"/><br/>
    <xsl:copy-of select="//*[error]"/>
  </xsl:if>
</xsl:template>


<!-- **********************************************************************************************
      media_prepare_transfer
      
      Loop through the pricelist (table) and discover the needed images
      
      The source image: <WebArticleProductCode/>.jpg 
      The target image: <WebArticleCode/>.jpg
        
      CALLED BY
      engineImpEngMedia.xsl as part of [do=media]

      INPUT
      tmpPath : path to use for temp image handling like compression to thumbnail 
      supplierImagesPath : the master path to the supplier image library
      imagespath : path to the regular big images (mostly full FTP URL)
      thumbspath : path to the thumbnail images (mostly full FTP URL)
      dir_shop_images : directory with existing shop big images to be able to only upload missing files
      dir_shop_thumbs : directory with existing shop thumbnail images to be able to only upload missing files

      OUTPUT:
      upload_image: to count how many big images are uploaded
      upload_thumb: to count how many thumbnail images are uploaded


            <xsl:if test="../../shop_media_database/row[id_image = substring-before(translate(substring-after(current(),'-'),'.','-'),'-')]"> ++++++ </xsl:if>            
      
-->


<xsl:template match="job[@cmd='shop_media_preparetransfer']">

  <xsl:choose>
    <xsl:when test="validate='true'">
      <xsl:variable name="shop"><xsl:value-of select="shop"/></xsl:variable>
      <xsl:variable name="shoptableprefix"><xsl:value-of select="shoptableprefix"/></xsl:variable>
      <xsl:variable name="tmpPath" select="tmpPath"/>
      <xsl:variable name="supplierImagesPath"><xsl:value-of select="supplierImagesPath"/></xsl:variable>
      <xsl:variable name="imagespath"><xsl:value-of select="imagespath"/></xsl:variable>
        
      <xsl:for-each select="shop_media_database/row[shopimport=1]">

      
        <xsl:variable name="media_pricelist" select="key('media_pricelist', reference)"/>
        <xsl:variable name="shop_media_files" select="key('shop_media_files', concat(id_product,'-',id_image, '.jpg'))"/>

        
        <xsl:if test="id_image/text() and (not($media_pricelist) or (concat('0',translate($shop_media_files/@date,'-','')) &lt; translate($media_pricelist/MediaFileModified,'-','')))">
        
          <xsl:if test="$shop_media_files">
            <job cmd="shop_media_delete">
              <xsl:copy-of select="../../shop_media_type"/>
              <media_target_path><xsl:value-of select="$imagespath"/></media_target_path>
              <media_target_file><xsl:value-of select="id_product"/>-<xsl:value-of select="id_image"/></media_target_file>
            </job>
          </xsl:if>

          <action cmd="recordset">
            <connection>shop_<xsl:value-of select="$shop"/></connection>
            <query>
              DELETE FROM <xsl:value-of select="$shoptableprefix"/>image 
              WHERE id_image = :id_image
            </query>
            <params>
              <xsl:copy-of select="id_image"/>
            </params>
          </action>      
          <action cmd="recordset">
            <connection>shop_<xsl:value-of select="$shop"/></connection>
            <query>
              DELETE FROM <xsl:value-of select="$shoptableprefix"/>image_lang 
              WHERE id_image = :id_image
            </query>
            <params>
              <xsl:copy-of select="id_image"/>
            </params>
          </action>      


        </xsl:if>
        
        <xsl:if test="$media_pricelist and (not(id_image/text()) or (concat('0',translate($shop_media_files/@date,'-','')) &lt; translate($media_pricelist/MediaFileModified,'-','')) )">
          <job cmd="shop_media_transfer">
          
            <xsl:copy-of select="../../shop_media_type"/>
            <xsl:copy-of select="$tmpPath"/>
          
            <media_source_path><xsl:value-of select="$supplierImagesPath"/></media_source_path>
            <media_source_file><xsl:value-of select="$media_pricelist/MediaFileDir"/>/<xsl:value-of select="$media_pricelist/MediaFileName"/>.<xsl:value-of select="$media_pricelist/MediaFileExt"/></media_source_file>
            <media_target_path><xsl:value-of select="$imagespath"/></media_target_path>
            <job cmd="shop_media_preparefilename">
              <xsl:copy-of select="id_product"/>
              
              <image_insert>
                <action cmd="recordset">
                  <connection>shop_<xsl:value-of select="$shop"/></connection>
                  <rowtag/>
                  <query>
                    INSERT INTO <xsl:value-of select="$shoptableprefix"/>image (id_product, position, cover)
                    VALUES (:id_product, 1, 1)
                  </query>
                  <params>
                    <xsl:copy-of select="id_product"/>
                  </params>
                </action>
              </image_insert>
              <image_lang_insert>              
                <action cmd="recordset">
                  <connection>shop_<xsl:value-of select="$shop"/></connection>
                  <rowtag/>
                  <query>
                    INSERT INTO <xsl:value-of select="$shoptableprefix"/>image_lang (id_image, id_lang, legend)
                    SELECT id_image, d.id_lang, d.name
                    FROM <xsl:value-of select="$shoptableprefix"/>image i INNER JOIN <xsl:value-of select="$shoptableprefix"/>product_lang d ON (i.id_product = d.id_product)
                    WHERE i.id_product = :id_product
                  </query>
                  <params>
                    <xsl:copy-of select="id_product"/>
                  </params>
                </action>
              </image_lang_insert>                    
            </job>
          </job>
        </xsl:if>    
        
      </xsl:for-each>
    
      <action cmd="recordset">
        <connection>shop_<xsl:value-of select="$shop"/></connection>
        <rowtag/>
        <query>
          update <xsl:value-of select="$shoptableprefix"/>image_lang l 
          inner join <xsl:value-of select="$shoptableprefix"/>image i on (l.id_image = i.id_image)
          INNER JOIN <xsl:value-of select="$shoptableprefix"/>product_lang d ON (i.id_product = d.id_product and d.id_lang = l.id_lang)
          set legend = d.name
        </query>
      </action>  

      <!-- delete image files which are not available in the database -->
      <xsl:for-each select="shop_media_files/file">
        <!--  id_image is second number in the file name -->
        <xsl:if test="number(substring-before(translate(substring-after(.,'-'),'.','-'),'-')) &gt; 0 
        and not(key('shop_media_database', concat(substring-before(.,'-'), '-', substring-before(translate(substring-after(.,'-'),'.','-'),'-') ) ))">
          <message><br/>[delete image <xsl:value-of select="."/>]</message>
          <action cmd="file">
            <delete><xsl:value-of select="$imagespath"/><xsl:value-of select="."/></delete>
          </action>
        </xsl:if>            
      </xsl:for-each>

    
    </xsl:when>
    <xsl:otherwise>
      <message>No nl language or 19% tax defined.</message>
    </xsl:otherwise>
  </xsl:choose>

</xsl:template>


<!-- **********************************************************************************************
      shop_media_preparefilename

-->

<xsl:template match="job[@cmd='shop_media_preparefilename']">
  <xsl:if test="not(image_insert/insertid &gt; 0)">
    <action cmd="error">
      <message>
        Couldn't insert image into table for id_product [<xsl:value-of select="id_product"/>]
      </message>
    </action>
  </xsl:if>            

  <media_target_file><xsl:value-of select="id_product"/>-<xsl:value-of select="image_insert/insertid"/></media_target_file>
</xsl:template>
 

<!-- **********************************************************************************************
      shop_media_transfer

-->

<xsl:template match="job[@cmd='shop_media_transfer']">
  <xsl:variable name="media_source_path" select="media_source_path"/>
  <xsl:variable name="media_source_file" select="media_source_file"/>
  <xsl:variable name="media_target_path" select="media_target_path"/>
  <xsl:variable name="media_target_file" select="media_target_file"/>
  <xsl:variable name="tmpPath" select="tmpPath"/>
  
  <upload_image>
    <action cmd="file">
      <file><xsl:value-of select="$media_source_path"/><xsl:value-of select="$media_source_file"/></file>
      <copy><xsl:value-of select="$media_target_path"/><xsl:value-of select="$media_target_file"/>.jpg</copy>
    </action>

    <message><br/>[upload image <xsl:value-of select="$media_source_file"/>]</message>
  </upload_image>

  <xsl:for-each select="shop_media_type/row">
    <upload_thumb>
      <action cmd="image">
        <source><xsl:value-of select="$media_source_path"/><xsl:value-of select="$media_source_file"/></source>
        <target><xsl:value-of select="$tmpPath"/><xsl:value-of select="$media_target_file"/>-<xsl:value-of select="name"/>.jpg</target>
        <width><xsl:value-of select="width"/></width>
        <height><xsl:value-of select="height"/></height>
        <compression>90</compression>
        <type>Thumb</type>
      </action>
      <action cmd="file">
        <file><xsl:value-of select="$tmpPath"/><xsl:value-of select="$media_target_file"/>-<xsl:value-of select="name"/>.jpg</file>
        <copy><xsl:value-of select="$media_target_path"/><xsl:value-of select="$media_target_file"/>-<xsl:value-of select="name"/>.jpg</copy>
      </action>
      <action cmd="file">
        <delete><xsl:value-of select="$tmpPath"/><xsl:value-of select="$media_target_file"/>-<xsl:value-of select="name"/>.jpg</delete>
      </action>

      <message>[upload thumb <xsl:value-of select="$media_target_file"/>-<xsl:value-of select="name"/>.jpg]</message>
    </upload_thumb>
  </xsl:for-each>
      
</xsl:template>


<!-- **********************************************************************************************
      shop_media_delete

-->

<xsl:template match="job[@cmd='shop_media_delete']">
  <xsl:variable name="media_target_path" select="media_target_path"/>
  <xsl:variable name="media_target_file" select="media_target_file"/>
  
  <delete_image>
    <action cmd="file">
      <delete><xsl:value-of select="$media_target_path"/><xsl:value-of select="$media_target_file"/>.jpg</delete>
    </action>
  </delete_image>

  <xsl:for-each select="shop_media_type/row">
    <delete_thumb>
      <action cmd="file">
        <delete><xsl:value-of select="$media_target_path"/><xsl:value-of select="$media_target_file"/>-<xsl:value-of select="name"/>.jpg</delete>
      </action>
    </delete_thumb>
  </xsl:for-each>
    
</xsl:template>


<!-- ***********************************************************************************************
      shop_supplier_ids
      
      Retrieve the list of supplier ids used for this shop

      CALLED BY
            
      INPUT
      
      OUTPUT
-->

<xsl:template match="job[@cmd='shop_supplier_ids']">
  <shop_supplier_ids>
    <action cmd="recordset">
      <connection>impeng</connection>
      <rowtag/>
      <query>
        select contactid supplier_id from tbltype_contact where contactshop = :shop
      </query>
      <params>
        <xsl:copy-of select="shop"/>
      </params>
    </action>
  </shop_supplier_ids>   
</xsl:template>
 

<!-- **********************************************************************************************
      shop_media_database

      list of all images from the shop database
      
      INPUT
      shop: the concerning shop to connect to the tables 
      
      OUTPUT
      shop_media_database: the images as registered in the shop database

-->

<xsl:template match="job[@cmd='shop_media_database']">



  <xsl:choose>
    <xsl:when test="validate='true'">
  
      <xsl:if test="not(shop_supplier_ids/supplier_id)">
        <action cmd="error">
          <message>no supplier ids </message>
        </action>
      </xsl:if>
  
      <shop_media_database>
        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="shop"/></connection>
          <query>
            SELECT p.id_product, ifnull(a.reference, p.reference) reference, d.name, i.id_image, concat(p.id_product,'-',i.id_image) id_product_and_image, if( s.name in (
            <xsl:for-each select="shop_supplier_ids/supplier_id">'<xsl:value-of select="."/>'<xsl:if test="position() != last()">,</xsl:if></xsl:for-each>                
            ) and p.active = 1,1,0) shopimport
            FROM (<xsl:value-of select="shoptableprefix"/>product p LEFT JOIN <xsl:value-of select="shoptableprefix"/>product_lang d ON (p.id_product = d.id_product and id_lang = :lang ) ) 
              left join <xsl:value-of select="shoptableprefix"/>supplier s on (s.id_supplier = p.id_supplier)
              LEFT JOIN <xsl:value-of select="shoptableprefix"/>image i ON (i.id_product = p.id_product)
              left join <xsl:value-of select="shoptableprefix"/>product_attribute a on (a.id_product = p.id_product)
            group by p.id_product  
          </query>
          <params>
            <xsl:copy-of select="lang"/>
          </params>
        </action>
      </shop_media_database>
      
      <shop_media_type>
        <action cmd="recordset">
          <connection>shop_<xsl:value-of select="shop"/></connection>
          <query>
            select name, width, height from <xsl:value-of select="shoptableprefix"/>image_type where products = 1
          </query>
        </action>      
      </shop_media_type>

    </xsl:when>
    <xsl:otherwise>
      <message>No nl language or 19% tax defined.</message>
    </xsl:otherwise>
  </xsl:choose>

</xsl:template>
 


<!-- **********************************************************************************************
      shop_media_files

      Get the available image files from the FTP 
      
      INPUT
      imagespath: the FTP path to the shop images
      
      OUTPUT
      shop_media_files: the images as available on FTP
-->

<xsl:template match="job[@cmd='shop_media_files']">
    
    <shop_media_files>
      <action cmd="dir">
        <path><xsl:value-of select="imagespath"/></path>
      </action>
    </shop_media_files>
 
</xsl:template>

</xsl:stylesheet> 

