<?php
class Curl extends AbstractModel {
	protected $url;
	protected $encode;
	protected $useragent;
	protected $method;
	protected $separator;
	protected $cookieExists;
	protected $username;
	protected $password;
	protected $timeout;
	protected $redir;
	protected $data;
	protected $fields = array();
	protected $file;
	protected $tourl;
	protected $type;
	protected $contenttype;
	protected $referer;

  function init() {
    parent::init();
  }
  function setterGetter($type,$value=undefined){
      if($value === undefined){
          return $this->$type;
      }
      $this->$type=$value;
      return $this;
  }
  
  
  function url($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function encode($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function useragent($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function method($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function separator($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function cookieExists($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function username($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function password($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function timeout($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function redit($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function data($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function fields($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function file($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function tourl($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function type($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function contenttype($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }
  function referer($value=undefined) { return $this->setterGetter(__FUNCTION__,$value); }


	/*  input of the action */
	protected function needEncoding() {
			
		switch ($this->encoding) {
			case "latin1":
			case "windows-1252":
				return true; // it needs conversion
				break;
			default:
				return false; // no conversion need
				break;
		}
	}

	/*  input of the action */
	protected function fromUtf8( $value ) {
			
		switch ($this->encoding) {
			case "latin1":
				$value = utf8_decode( $value );
				break;
			case "windows-1252":
				throw new exception("Engine: utf8 to windows-1252 not implemented yet");
				break;
      case "":
      case "utf8":
				break;
			default:
				throw new exception("Engine: Decoding from utf8 not implemented yet [".$this->encoding."]");
				break;
		}
		return $value;
	}

	/*  output of the action */
	protected function toUtf8( $value ) {

		switch ($this->encoding) {
			case "latin1":
				$value = utf8_encode( $value );
				break;
			case "windows-1252":
  			 // to convert windows-1252 into utf8 use the htmlentities in between
				 // latin1 does not allow characters 0x80 tot 0x9F
         // while windows-1252 / CP1252 does like the trade mark 
				 $value = htmlentities($value, ENT_QUOTES, "Windows-1252");
				 $value = html_entity_decode($value, ENT_QUOTES , "UTF-8");
				break;
      case "":
      case "utf8":
				break;
			default:
				throw new exception("Engine: Encoding to utf8 not implemented yet [".$this->encoding."]");
				break;
		}
  	return $value;
	}



  protected function parseUrl( $url ) {
  
    if( !isset($url) ) {
      $parsed['scheme'] = 'string';
    } else {
      // parse the from and to urls to be used later
  	  // scheme://user:pass@host/path?query#fragment
      $parsed = parse_url( $url );
  
      // default scheme will be set to file
      if( !isset( $parsed['scheme'] ) ) $parsed['scheme'] = 'file';
  
      // extend the parsed url info with the mime type
      switch ($parsed['scheme']) {
        case 'file':
          if( file_exists( $parsed['path'] ) ) {
            $parsed['exists'] = true;
  
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
            $parsed['mimetype'] = finfo_file($finfo, $parsed['path'] ) ;
            finfo_close($finfo);
          }
          break;
        default:
          break;
      }
    }
  
    return $parsed;
  }
  
  function initCurl() {
    $this->url = str_replace(' ', '%20', $this->url);
    if( !isset($this->timout) ) $this->timout = 5;

    // scheme://user:pass@host/path?query#fragment
    $this->to = $this->parseUrl( $this->tourl );

		// encode data for POST data from UTF8 when encoding is requested
		if( $this->data ) {
			if( $this->needEncoding()) {
				$this->data = $this->fromUtf8( $this->data );
			}
		}

		// encode fields for POST data from UTF8 when encoding is requested
		if( $this->fields ) {
			if( $this->needEncoding( )) {
				foreach( $this->fields as $key => &$value) {
					$value = $this->fromUtf8( $value );
				}
			}
		}

		// ensure to have a valid method POST or GET
		$this->method = ( strtoupper( $this->method) == "POST" ? "POST" : "GET") ;

    $this->type = ( strtolower( $this->type) == "xml" ? "xml" : (strtolower( $this->type) == "htmlbody" ? "htmlbody" : "html") ) ;

		// ensure to have cookieExists = false / true
		$this->cookieExists = $this->cookieExists ? true : false ;

		// redir option will control the CURLOPT_FOLLOWLOCATION and the CURLOPT_MAXREDIRS
		if( !isset($this->redir) ) $this->redir = 2;
    if( !isset( $this->separator ) ) $this->separator = '&';
    
    return $this;
  }
  
  
  
  
	function go() {
	  $this->initCurl();
    
	  if( $this->method == 'GET' and $this->fields ) {
      $query = http_build_query( $this->fields, '', $this->separator );
      if( strpos( $this->url, '?' ) ) {
        $this->url .= $this->separator . $query;
      } else {
        $this->url .= '?'.$query;
      }
    }
	
		// initiate the curl handler
		$ch = curl_init( $this->url );

		//  curl_setopt ($ch, CURLOPT_PROXY, 'http://proxy.hetnet.nl:8080'); // for debugging
		//  curl_setopt ($ch, CURLOPT_PROXY, 'http://localhost-DISABLED:8080'); // for debugging
		//  curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 ); // force HTTP 1.0

    // curl_setopt($ch,CURLOPT_ENCODING , "gzip"); // just test 

		curl_setopt( $ch, CURLINFO_HEADER_OUT, 1 ); // 0= no header OUT info in the output
		curl_setopt( $ch, CURLOPT_HEADER, 0 ); // 0= no header IN info in the output
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );  // return as string and no echo to buffer
		// do not use CUSTOMREQUEST as it will prevent that a POST can be followed by a GET
		// http://bugs.php.net/bug.php?id=49571&thanks=4
		// curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $this->method);

		if( $this->method == "POST" ) {
			curl_setopt( $ch, CURLOPT_POST, 1 ); // enable post data

			// string like 'para1=val1&para2=val2&...'
			if( $this->data ) {
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->data ); // the post raw data
			} else {
				// or as an array with the field name as key and field data as value.
				// If value is an array, the Content-Type header will be set to multipart/form-data.
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->fields ); // fields as array
			}

		}

//    $header_array[]='Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
//		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header_array);


	 	$cookieFile = sys_get_temp_dir() . "/cookieFileName.txt";
		// handle cookies
		if( $this->cookieExists ) {
      echo 'KKKKKKKKKKKKKKKKKKKKK';
			curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookieFile );
		}
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookieFile );

		// handle user agent
		if( isset($this->useragent) ) {
			curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		}

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // very important for SSL to avoid: 'SSL certificate problem, verify that the CA cert is OK'
		// TODO follow location terugzetten naar 1


		if( $this->redir > 0 ) {
			curl_setopt($ch, CURLOPT_MAXREDIRS, $this->redir ); // max redirs by followlocation
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // follow the location: in the header
		} else {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); // don't follow the location: in the header
		}
		curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);

    if( $this->contenttype ) {
       curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: '.$this->contenttype));
    }
    if($this->referer) {
      curl_setopt($ch, CURLOPT_REFERER, $this->referer);
    }
		// ***********************************************************
		// retrieve the HTTP content either to file or to buffer





  	if( isset($this->file) ) {
    	$fh = fopen( $this->file, 'w');
		  curl_setopt ($ch, CURLOPT_FILE, $fh );
  		curl_exec( $ch );
    } else {
		  $this->content = curl_exec( $ch );
    }
    $curlError = curl_error( $ch );
		echo'<pre>';print_r(curl_getinfo($ch));
    curl_close( $ch );
      
    if( isset($fh) ) {
      fclose($fh);
    }


		// in case of error then log the error but continue and return the <error> message
		if( $curlError ) {
      $this->exception('error in curl');
		} 

    

    return $this;
  }
  
  function get() {
    return $this->content;
  }
  
  function convert() {
		// no curl error so handle the content

  	if( isset($this->file) ) {
      return true;
    } else {
  		// real content from the url and possibly it needs encoding
			if( $this->needEncoding() && ($this->type == 'html' or $this->type == 'htmlbody') ) {
	   		// no need to convert character encoding as loadHTML will respect the content-type (only)
				$content =  '<meta http-equiv="Content-Type" content="text/html;charset='.$this->encoding.'">' . $content;
			}
			
  		// return a dom from the content
	   	$domInject = new DOMDocument("1.0", "UTF-8");

  		$domInject->preserveWhiteSpace = false;
	   	$domInject->formatOutput = true;

  		if( $this->type == 'xml') {
  			// load xml in case the content type is xml
	   		// TODO: possible character encoding
	   		
	   		if(  $this->needEncoding() ) {
          $content = $this->toUtf8( $content );
        }
	 			try {
  
       		$domInject->loadXML( $content );
  			} catch(Exception $e){
  			   if( strpos( $e->getMessage(), 'Input is not proper UTF-8' ) ) {
           		$domInject->loadXML( utf8_encode( $content ) );
           } else {
             	throw $e;
           }
  			}
       		
       		
		  } else {
		    // html type
  			try {
  				// Note: DOM extension uses UTF-8 encoding.
  				// Use utf8_encode() and utf8_decode() to work with texts in ISO-8859-1 encoding or Iconv for other encodings.
  				// check also how character encoding is handled http://www.onphp5.com/article/57
  				// You must specify the character set in <HEAD> tag to be used by libxml2. This is how libxml2 works.
  				// use $domInject->actualEncoding whether DOM detected the encoding in the head.
  				$domInject->loadHTML( $content );
  
  			} catch(Exception $e){
  				// do nothing and continue as it's normal that warnings will occur on nasty HTML content
  			}

  			$this->reworkDom( $domInject );
		  } 

      try {
        if( $this->type == 'htmlbody' ) {
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
          if( is_null($domInject->documentElement) ) {
            $fragment = $dom->createDocumentFragment();
          } else {
  		      $fragment = $dom->importNode($domInject->documentElement, true);
          }
        }
        
        
        switch ($this->to['scheme']) {
          case 'engine':
            return $fragment;
            break;
          case 'memory':
            DomMemoryFactory::setDomMemory( $this->to['path'], $domInject );
    
            return $dom->createElement( 'info', 'ok in memory' );

            break;
          default:
          	throw new exception("Engine: to url scheme not recognized [".($this->tourl)."] ");
        }
        
        
        
        
      }
      catch(Exception $e) {
        $message = "";
        $message .= "Engine: Tried result for URL :\r";
        $message .= $this->url . "\r";

        // throw extra error message and the original error exception (3rd parameter for previous error)
        throw new exception($message, 0, $e);
      }					
		
    } 
		
	}
}
