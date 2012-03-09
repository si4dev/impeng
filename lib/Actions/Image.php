<?php
class Actions_Image extends AbstractModel {
  function init() {
    parent::init();
  }
  
  // can also extend with path info pathinfo( $filename );

  function setFile($file) {
    $this->file=$file;
    return $this;
  }
  
  function fileSize() {
    return filesize($this->file);
  }
  
  function imgWidth() {
    $i=getimagesize($this->file);
    return $i[0]; 
  }
  function imgHeight() {
    $i=getimagesize($this->file);
    return $i[1]; 
  }
  function fileModified() {
    return date("Y-m-d H:i:s",filemtime($this->file));
  }

  function fileMd5() {
    return md5_file($this->file);
  }
}