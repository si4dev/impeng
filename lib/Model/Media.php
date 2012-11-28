<?php
class Model_Media extends Model_Table {
  public $table='media';
  
  function init() {
    parent::init();
    $this->hasOne('Product');
    
    $this->addField('last_requested'); // Keep date of latest request for this media
    $this->addField('request_count'); // Increase count for every request to determine priority  
    $this->addField('last_checked'); // Last checked with the source / supplier
    $this->addField('purpose'); //The purpose of the media, e.g. main for regular product image
    $this->addField('url'); // The URL pointing to the original downloadable image
    $this->addField('file_dir');
    $this->addField('file_name');
    $this->addField('file_ext');
    $this->addField('file_modified');
    $this->addField('file_size');
    $this->addField('file_md5');
    $this->addField('mime_type');
    $this->addField('width');
    $this->addField('height');
    $this->addField('valid');
    $this->addExpression('file')->set("concat(file_dir,'/',file_name,'.',file_ext)");
  }

  // ------------------------------------------------------------------------------------------------
  //   adds or updates one or more records to the media table with the given url or url array
  //   used by Model_Product->mediaFound()
  function found($url,$purpose='main') {
    if(is_array($url)) {
      foreach($url as $u) $this->found($u,$purpose);
    } elseif($url) {
      $this->debug()->tryLoadBy('url',$url)
          ->set('purpose',$purpose)
          ->saveAndUnload();
    }
    return $this;
  }

  // ------------------------------------------------------------------------------------------------
  //   analyse file 
  function setFileInfo() {
    $this->set('valid',0);
    $file=realpath($this->api->getConfig('path_suppliermedia').$this->get('file_dir'))
        .'/'.$this->get('file_name').'.'.$this->get('file_ext');
    if(!file_exists($file)) throw $this->exception('Engine: media file does not exist') 
        ->addMoreInfo('file',$file)
        ;
    $this->set('file_size',filesize( $file ))
        ->set('file_modified',date ("Y-m-d H:i:s", filemtime( $file ) ))
        ->set('file_md5',md5_file( $file ))
        ;
    $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
    $this->set('mime_type',finfo_file($finfo, $file));
    finfo_close($finfo);

    // if mime type starts with image/png 
    if( strpos($this->get('mime_type'), 'image') === 0 ) {
      switch( $this->get('mime_type') ) {
        case 'image/gif':
        case 'image/jpeg':
        case 'image/png':
        case 'image/x-ms-bmp':
        case 'image/tiff':
            $imageInfo = getimagesize( $file );
            $this->set('width', $imageInfo[0] )
                ->set('height', $imageInfo[1] );
            break;
        default:
            throw $this->exception('Engine: image type not yet supported')
                ->addMoreInfo('file',$file)
                ->addMoreInfo('mime-type',$this->get('mime_type'))
            ;
            break;
       }                       
    }       
    return $this;
  }
  
  // ------------------------------------------------------------------------------------------------
  //   validate if it's image 
  function validateImage() {
   // $this->set('errors','1');
    if(strpos($this->get('mime_type'), 'image') === 0 ) {
      $this->set('valid',1);
    } 
    return $this;
    
  }
} 