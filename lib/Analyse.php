<?php
class Analyse extends AbstractModel {
  function init() {
    parent::init();
    
  }

  function file($file,$type=array('csv')) {
    
    foreach($type as $t) {
      
      switch ($t) {
        case 'csv':
          // *** analyse first line to determine field names ***
          $fp=fopen($file,"r");
          $header=fgets($fp,10000);
          fclose($fp);
          
          $cnt=1;
          $bestDelimiter=false;
          $delimiters=array(',',';','|',"\t");
          foreach($delimiters as $delimiter) {
            if(substr_count($header,$delimiter)>$cnt) {
              $bestDelimiter=$delimiter;
            }
          }
          
          // to check for delimiter
          $bestEnclosure=false;
          $enclosures=array('"');
          foreach($enclosures as $enclosure) {
            $fp=fopen($file,"r");
            $header=fgets($fp,10000);
          
            $cnt=false;
            while ($line=fgetcsv($fp,10000,$bestDelimiter,$enclosure)) {
              
          //    if(strpos($line
              
              if(!$cnt) { // first so nothing to compare
                $cnt=count($line);
              } elseif(!count($line)) { // last could have empty line and do nothing
              } elseif($cnt==count($line)) { // nicely the same
                $cnt=count($line);
                $bestEnclosure=$enclosure;
              } elseif($cnt!=count($line)) { // not the same and not empty so break
                $cnt=false;
                $bestEnclosure=false;
                break;
              }
            }
            fclose($fp);
          }
          
          if($bestDelimiter) {
            $this->conclusion=array(
                'type'=>'csv',
                'delimiter'=>$bestDelimiter,
                'enclosure'=>$bestEnclosure,
                );
          }
          
          
          
          
          break;
        default:
          break;
      }
      return $this;
    }
    
  }

  function getConclusion() {
    return $this->conclusion;
  }

}

