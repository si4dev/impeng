<?php

class XML extends AbstractModel {
  function init() {
    parent::init();
    
  }
  
  function xmlToXml() {
  
    }
  
  
  
  function xmlToArray($xml,$row,$key) {
    $result=array();
    if($xml) {
      $dom = new DOMDocument("1.0", "UTF-8");
      $dom->loadXML('<root>'.$xml.'</root>');
      $xpath = new DOMXPath($dom);
      foreach( $xpath->query( '/root/'.$row ) as $node ) {
        $result[$node->getAttribute($key)]=$this->innerXml($node);
      }
    }
    return $result;
  }
  
  function innerXml($node,$outputXml=false) {
    $innerXml='';
    foreach( $node->childNodes as $child ) {
			if( !$outputXml ) {
  	   	// in case the field is not an xml field then replace back the xml characters
	   	  // most of the times the field is NOT an xml field
				$innerXml .= str_replace ( array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), array ( '&', '"', "'", '<', '>' ),$node->ownerDocument->saveXML($child));
			} else {
        if( $child->nodeType == XML_ELEMENT_NODE and $innerXml)
          $innerXml .= "\r\n";
        $innerXml .= (string)$node->ownerDocument->saveXML($child);
      }
    }
    return $innerXml;
  }
  
  function tryToXml($dom,$content) {
    
    if(!$content) return false;
    
    // xml well formed content can be loaded as xml node tree
    $fragment = $dom->createDocumentFragment();
    // wonderfull appendXML to add an XML string directly into the node tree!
    
    // todo: maybe better to make a dom document and exclude the xml declaration for injecting
    // as appendxml will fail on a xml declaration.
    if( substr( $content,0, 5) == '<?xml' ) {
      $content = substr($content,strpos($content,'>')+1);
      if( strpos($content,'<') ) {
        $content = substr($content,strpos($content,'<'));
      }
    }

/* todo: nice with exception however then each php error should result in exception
    try {
      $fragment->appendXML( $content );
      
    } catch(DOMException $e) {
      return htmlToXml;
    }
*/

    if(!@$fragment->appendXML( $content )) {
      return $this->htmlToXml($dom,$content);
    }

    return $fragment;    
  }
  
  // convert content into xml 
  // dom is only needed to prepare the xml which will be returned 
  function htmlToXml($dom, $content, $needEncoding=false, $bodyOnly=true) {

    // no xml when html is empty
    if(!$content) return false;
    
    // real content and possibly it needs encoding
    if( $needEncoding ) {
      // no need to convert character encoding as loadHTML will respect the content-type (only)
      $content =  '<meta http-equiv="Content-Type" content="text/html;charset='.$this->encoding.'">' . $content;
    }

    // return a dom from the content
    $domInject = new DOMDocument("1.0", "UTF-8");
    $domInject->preserveWhiteSpace = false;
    $domInject->formatOutput = true;

    // html type
    try {
      // Note: DOM extension uses UTF-8 encoding.
      // Use utf8_encode() and utf8_decode() to work with texts in ISO-8859-1 encoding or Iconv for other encodings.
      // check also how character encoding is handled http://www.onphp5.com/article/57
      // You must specify the character set in <HEAD> tag to be used by libxml2. This is how libxml2 works.
      // use $dom->actualEncoding whether DOM detected the encoding in the head.
      @$domInject->loadHTML( $content );
    } catch(Exception $e){
      // do nothing and continue as it's normal that warnings will occur on nasty HTML content
    }
		// to check encoding: echo $dom->encoding
		$this->reworkDom( $domInject );

    if( $bodyOnly ) {
      $fragment = $dom->createDocumentFragment();
      
      // retrieve nodes within /html/body
      foreach( $domInject->documentElement->childNodes as $elementLevel1 ) {
       if( $elementLevel1->nodeName == 'body' and $elementLevel1->nodeType == XML_ELEMENT_NODE ) {
         foreach( $elementLevel1->childNodes as $elementInject ) {
           $fragment->insertBefore( $dom->importNode($elementInject, true) );
         }
        }
      }
    } else {
      $fragment = $dom->importNode($domInject->documentElement, true);
    }
        

    return $fragment;
  }
    
  

	/*
	 DOM Constants:
	 XML_ELEMENT_NODE (integer)  1 Node is a DOMElement						OK
	 regular html or xml tag
	 XML_ATTRIBUTE_NODE (integer)  2 Node is a DOMAttr
	 XML_TEXT_NODE (integer)  3 Node is a DOMText							OK
	 text node containing valid xml text
	 XML_CDATA_SECTION_NODE (integer)  4 Node is a DOMCharacterData			OK
	 character data node to allow non xml text
	 XML_ENTITY_REF_NODE (integer)  5 Node is a DOMEntityReference
	 XML_ENTITY_NODE (integer)  6 Node is a DOMEntity
	 XML_PI_NODE (integer)  7 Node is a DOMProcessingInstruction
	 XML_COMMENT_NODE (integer)  8 Node is a DOMComment						OK BUT REPLACE '-' signs
	 strange issue if the value contains the '-' sign as this is not
	 allowed in XML W3S specs to contain '-' signs in comments!!!
	 (so while html will allow, the xml will not)
	 XML_DOCUMENT_NODE (integer)  9 Node is a DOMDocument					OK
	 root document node for XML document
	 XML_DOCUMENT_TYPE_NODE (integer)  10 Node is a DOMDocumentType			REMOVE
		first child node of the root HTML document to indicate the DOCTYPE
	 XML_DOCUMENT_FRAG_NODE (integer)  11 Node is a DOMDocumentFragment
	 XML_NOTATION_NODE (integer)  12 Node is a DOMNotation
	 XML_HTML_DOCUMENT_NODE (integer)  13									OK
	 root document node for HTML document, no need to move or change
	 as it's the root and the injection will scipt the root



	 Typical HTML document node tree structure:

	 .	13[#document]
	 .		10[html]
	 .		1[html]
	 .			1[head]
	 .				1[meta]
	 .				1[title]
	 .					3[#text]
	 .				1[script]
	 .					4[#cdata-section]
	 .				1[style]
	 .					4[#cdata-section]
	 .			1[body]
	 .				1[div]
	 .					3[#text]
	 .					1[a]
	 .						3[#text]
	 .					3[#text]
	 .				8[#comment]



	 */


	protected function reworkDom( $node, $level = 0 ) {

		// start with the first child node to iterate
		$nodeChild = $node->firstChild;
			
		while ( $nodeChild )  {
			$nodeNextChild = $nodeChild->nextSibling;

			switch ( $nodeChild->nodeType ) {
				case XML_ELEMENT_NODE:
					// iterate through children element nodes
					$this->reworkDom( $nodeChild, $level + 1);
					break;
				case XML_TEXT_NODE:
				case XML_CDATA_SECTION_NODE:
					// do nothing with text, cdata
					break;
				case XML_COMMENT_NODE:
					// ensure comments to remove - sign also follows the w3c guideline
					$nodeChild->nodeValue = str_replace("-","_",$nodeChild->nodeValue);
					break;
				case XML_DOCUMENT_TYPE_NODE:  // 10: needs to be removed
				case XML_PI_NODE: // 7: remove PI
					$node->removeChild( $nodeChild );
					$nodeChild = null; // make null to test later
					break;
				case XML_DOCUMENT_NODE:
					// should not appear as it's always the root, just to be complete
					// however generate exception!
				case XML_HTML_DOCUMENT_NODE:
					// should not appear as it's always the root, just to be complete
					// however generate exception!
				default:
					throw new exception("Engine: reworkDom type not declared [".$nodeChild->nodeType. "]");
			}




			$nodeChild = $nodeNextChild;
		} ;
	}


	/*
	 DOM Constants:
	 XML_ELEMENT_NODE (integer)  1 Node is a DOMElement
	 XML_ATTRIBUTE_NODE (integer)  2 Node is a DOMAttr
	 XML_TEXT_NODE (integer)  3 Node is a DOMText
	 XML_CDATA_SECTION_NODE (integer)  4 Node is a DOMCharacterData
	 XML_ENTITY_REF_NODE (integer)  5 Node is a DOMEntityReference
	 XML_ENTITY_NODE (integer)  6 Node is a DOMEntity
	 XML_PI_NODE (integer)  7 Node is a DOMProcessingInstruction
	 XML_COMMENT_NODE (integer)  8 Node is a DOMComment
	 XML_DOCUMENT_NODE (integer)  9 Node is a DOMDocument
	 XML_DOCUMENT_TYPE_NODE (integer)  10 Node is a DOMDocumentType
	 XML_DOCUMENT_FRAG_NODE (integer)  11 Node is a DOMDocumentFragment
	 XML_NOTATION_NODE (integer)  12 Node is a DOMNotation
	 XML_HTML_DOCUMENT_NODE (integer) 13
	 */

  }