<?php
class FTP extends AbstractModel {
  public $transfermode=FTP_BINARY;
  public $error;
  function init() {
    parent::init();
  }

  function login($server,$user,$password,$pasv=true) {
    if($this->connection=@ftp_connect($server,21,180)) {
      if(@ftp_login($this->connection, $user, $password) ) {
        ftp_pasv($this->connection,$pasv);
      } else {
      throw $this->exception('FTP: cannot login')
          ->addMoreInfo('path',$path);
      }
    } else {
      throw $this->exception('FTP: cannot connect')
          ->addMoreInfo('server',$server);
    }
    return $this;
  }

  function getDir() {
    return @ftp_nlist($this->connection,'.');
  }
  
  function getPath() {
    return @ftp_pwd($this->connection);
  }
  

  function cd($path) {
    if( !@ftp_chdir($this->connection,$path) ) {
      throw $this->exception('FTP: cannot cd')
          ->addMoreInfo('path',$path);
    }
    return $this;
  }

  function mkdir($dir) {
    if( !@ftp_mkdir($this->connection,$dir) ) {
      throw $this->exception('FTP: cannot mkdir')
          ->addMoreInfo('path',$path);
    }
    return $this;
  }

  function setSource($source) {
    $this->source=$source;
    return $this;
  }
  
  function setTarget($target) {
    $this->target=$target;
    return $this;
  }

  function save() {
    // from local source to ftp remote target
    if( !@ftp_put($this->connection,$this->target,$this->source,$this->transfermode) ) {
      throw $this->exception('FTP: cannot put')
          ->addMoreInfo('path',$path);
    }
    return $this;
  }

    
  function delete() {
    // delete target remote ftp
    if( !@ftp_delete($this->connection,$this->target) ) {
      throw $this->exception('FTP: cannot delete')
          ->addMoreInfo('path',$path);
    }
    return $this;
  }
  
  function load() {
    // from ftp remote source to local target
    if( !@ftp_get($this->connection,$this->target,$this->source,$this->transfermode) ) {
      throw $this->exception('FTP: cannot get')
          ->addMoreInfo('path',$path);
    }
    return $this;
  }
    
  
  function logout()
	{
		@ftp_quit($this->connection);
		return $this;
	}
}

/*

	function listFilesDates()
	{
  // http://bugs.php.net/bug.php?id=25641&edit=1
  // ftp_rawlist($conn_if, "-lR /");
  // http://bugs.php.net/bug.php?id=7245
  // ftp_rawlist($fp,"-l")
    $file_list_dates = array();
    foreach( ftp_rawlist($this->conn_id, "-l") as $key => $value) {
      $split    = preg_split("/[\s]+/", $value, 9, PREG_SPLIT_NO_EMPTY);
      // ( [0] => -rw-r--rw- [1] => 1 [2] => 10kshop.nl_admin [3] => web136 [4] => 20431 [5] => Oct [6] => 26 [7] => 2009 [8] => 1-1.jpg ) 
      $file = $split[8];
      if( $file != '.' and $file != '..' ) {
        $timeoryear = $split[7];
        $day = $split[6];
        $month = $split[5];
        if( strpos($timeoryear, ':') ) {
          $date = date("Y-m-d",strtotime( $day . ' ' . $month )) ;
          if( $date > date("Y-m-d") ) {
            $date = date("Y-m-d",strtotime( $day . ' ' . $month . ' ' . (date('Y') -1) ));
          }
        } else {
          $date = date("Y-m-d",strtotime( $day . ' ' . $month .  ' ' . $timeoryear  )) ;
        }
        if( $date < '1990-01-01' or $date > date("Y-m-d") ) {
  				throw new exception("Engine: ftp rawlist not able to retrieve valid date [".$file."][".$date."]");
        }
        $file_list_dates[] = array( 'file' => $file, 'date' => $date );
      }
    }
		return $file_list_dates;
	}
*/