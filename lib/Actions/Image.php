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
  


  function resizeImage($InputFile, $TargetFile, $MaxWidth, $MaxHeigth, $CompressionFactor = 80, $Type="Photo") {
    if (strtolower (substr($TargetFile,-4))!=".jpg" && strtolower (substr($TargetFile,-5))!=".jpeg" && strtolower (substr($TargetFile,-4))!=".png") {
      copy($InputFile, $TargetFile);		
      return Array('status'=>0, 'msg'=>"Not a valid image type for resizing. <BR/>Supported types are: JP(E)G and PNG."); 
    }
    //Get Image size info
    $info = @getimagesize( $InputFile );

    if( $info === false )
      return false;
    else
      switch ($info[2])
      {
          case 1:
            if(($sourcefile_id = imagecreatefromgif($InputFile)) === false) 
          	  return Array('status'=>0, 'msg'=>"Not a valid GIF type."); 
            break;
          case 2:
            if(($sourcefile_id = imagecreatefromjpeg($InputFile)) === false) 
          	  return Array('status'=>0, 'msg'=>"Not a valid JPG type."); 
            break;
          case 3:
            if(($sourcefile_id = imagecreatefrompng($InputFile)) === false) 
          	  return Array('status'=>0, 'msg'=>"Not a valid PNG type."); 
            break;
          case 6:
            if(($sourcefile_id = imagecreatefrombmp($InputFile)) === false) 
          	  return Array('status'=>0, 'msg'=>"Not a valid BMP type."); 
            break;
          default:
         	  return Array('status'=>0, 'msg'=>"Not a valid type."); 
      }
    // Get dimensions
    $sourcefile_width=imageSX($sourcefile_id); 
    $sourcefile_height=imageSY($sourcefile_id); 
    
    // If Picture is smaller then $Max, do nothing:
    if(($sourcefile_width < $MaxWidth) && ($sourcefile_height < $MaxHeigth) AND ($Type == "Photo")) {
      copy($InputFile, $TargetFile);
    }	else {
      // Calculate resizing factor in case of Thumb
    
    if($Type == "Thumb") {
      if($sourcefile_width >= $sourcefile_height) {
        $SetWidth = $MaxWidth; 
        $onepercent = ($sourcefile_width / 100);
        $factor = $SetWidth / $onepercent;
        $SetHeight = ($sourcefile_height / 100) * $factor;
      } else {
        $SetHeight = $MaxHeigth;
        $onepercent = ($sourcefile_height / 100);
        $factor = $SetHeight / $onepercent;
        $SetWidth = ($sourcefile_width / 100) * $factor;
      }
      // Make a new image 
      $Newsourcefile_id = ImageCreate($MaxWidth, $MaxHeigth);
      // Format colors
      $Newsourcefile_id = ImageCreateTrueColor($MaxWidth, $MaxHeigth);
      // Calculate alignment
      $leftalign = ($MaxWidth - $SetWidth) / 2;
      $topalign = ($MaxHeigth - $SetHeight) / 2;
      // Fill the rest with white color:
      if($sourcefile_width > $sourcefile_height) {
          // Horizontal Fill
        $white = ImageColorAllocate ($Newsourcefile_id, 255, 255, 255);
        ImageFilledRectangle($Newsourcefile_id, 0,0, $MaxWidth, $topalign, $white);
        $bottomoffset = ($MaxHeigth - 1) - $topalign;
        ImageFilledRectangle($Newsourcefile_id, 0,$bottomoffset, $MaxWidth, $MaxHeigth, $white);
      } else {		
          // Vertical fill
        $white = ImageColorAllocate ($Newsourcefile_id, 255, 255, 255);
        ImageFilledRectangle($Newsourcefile_id,0,0,$leftalign,$MaxHeigth,$white);
        $rightoffset = ($MaxWidth - 1) - $leftalign;
        ImageFilledRectangle($Newsourcefile_id, $rightoffset,0,$MaxWidth,$MaxHeigth,$white);
      }
		} elseif($Type=="Photo") {
      if($sourcefile_width >= $sourcefile_height) {
        $SetWidth = $MaxWidth; 
        $onepercent = ($sourcefile_width / 100);
        $factor = $SetWidth / $onepercent;
        $SetHeight = ($sourcefile_height / 100) * $factor;
      } else {
        $SetHeight = $MaxHeigth;
        $onepercent = ($sourcefile_height / 100);
        $factor = $SetHeight / $onepercent;
        $SetWidth = ($sourcefile_width / 100) * $factor;
		  }

      // Make a new image 
      $Newsourcefile_id = ImageCreate($SetWidth, $SetHeight);
      // Format colors
      $Newsourcefile_id = ImageCreateTrueColor($SetWidth, $SetHeight);
      // Calculate alignment
      $leftalign = ($SetWidth - $SetWidth) / 2;
      $topalign = ($SetHeight - $SetHeight) / 2;
	  }
	
    // Create resampled and resized image
    ImageCopyResampled($Newsourcefile_id,$sourcefile_id,$leftalign,$topalign,0,0,$SetWidth,$SetHeight,$sourcefile_width,$sourcefile_height); 
    // Create the Image
    
    imagejpeg ($Newsourcefile_id,$TargetFile, $CompressionFactor);
    return Array('status'=>1, 'msg'=>""); 
	}
}



  
}