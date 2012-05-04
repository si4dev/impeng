<?php
class FTP extends AbstractModel {
  public $transfermode=FTP_BINARY;
  public $error;
  public $scheme='ftp';
  public $pasv=true;
  function init() {
    parent::init();
  }

  function scheme($scheme) {
    $this->scheme=($scheme?:'file'); // when scheme is empty then it's file, although default is ftp
    return $this;
  }
  function host($host) {
    $this->host=$host;
    return $this;
  }
  function port($port) {
    $this->port=($port?:21);
    return $this;
  }
  function user($user) {
    $this->user=$user;
    return $this;
  }
  function pass($pass) {
    $this->pass=$pass;
    return $this;
  }
  function path($path=null) {
    // you can also get path
    if($path===null) {
      return $this->path;
    }
    $this->path=$path;
    return $this;
  }
  function pasv($pasv=true) {
    $this->pasv=$pasv;
    return $this;
  }
  function url($url) {
    $url=parse_url($url);
    $this->scheme($url['scheme'])
        ->host($url['host'])
        ->port($url['port'])
        ->user($url['user'])
        ->pass($url['pass'])
        ->path($url['path'])
        ;
    return $this;
  }
    
  // login with info already stored in object which also allows to reconnect
  function login() {
    switch($this->scheme) {
      case 'ftp':
        if($this->connection=@ftp_connect($this->host,21,180)) {
          if(@ftp_login($this->connection, $this->user, $this->pass) ) {
            ftp_pasv($this->connection,$this->pasv);
          } else {
          throw $this->exception('FTP: cannot login')
              ->addMoreInfo('user',$this->user);
          }
        } else {
          throw $this->exception('FTP: cannot connect')
              ->addMoreInfo('host',$this->host);
        }
        break;
      case 'file': // no login
        break;
    }
    return $this;
  }

  function ls() {
    return @ftp_nlist($this->connection,'.');
  }
  
  function getPath() {
    return @ftp_pwd($this->connection);
  }
  

  function cd() {
    switch ($this->scheme) {
      case 'ftp':
        if( !@ftp_chdir($this->connection,$this->path) ) {
          throw $this->exception('FTP: cannot cd')
              ->addMoreInfo('path',$this->path);
        }
        break;
      case 'file':
        break; 
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
    switch ($this->scheme) {
      case 'ftp':
        // from local source to ftp remote target
        if( !@ftp_put($this->connection,$this->target,$this->source,$this->transfermode) ) {
          throw $this->exception('FTP: cannot put')
              ->addMoreInfo('source',$this->source)
              ->addMoreInfo('target',$this->target);
        }
        break;
      case 'file':
        copy($this->source,$this->path.$this->target);
        break;
    }
    return $this;
  }

    
  function delete() {
    // delete target remote ftp
    if( !@ftp_delete($this->connection,$this->target) ) {
      throw $this->exception('FTP: cannot delete')
          ->addMoreInfo('target',$this->target);
    }
    return $this;
  }
  
  function load() {
    // from ftp remote source to local target
    if( !@ftp_get($this->connection,$this->target,$this->source,$this->transfermode) ) {
      throw $this->exception('FTP: cannot get')
          ->addMoreInfo('target',$this->target);
    }
    return $this;
  }
    
  
  function logout()
	{
		@ftp_quit($this->connection);
		return $this;
	}



	function lsDates()
	{
    $file_list_dates = array();
    switch ($this->scheme) {
      case 'ftp':
        // http://bugs.php.net/bug.php?id=25641&edit=1
        // ftp_rawlist($conn_if, "-lR /");
        // http://bugs.php.net/bug.php?id=7245
        // ftp_rawlist($fp,"-l")
        foreach( ftp_rawlist($this->connection, "-l") as $key => $value) {
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
            $file_list_dates[$file] = array( 'file' => $file, 'date' => $date ); // to support 2 formats
          }
        }
        break;
      case 'file':
        $dir = new DirectoryIterator($this->path);
        foreach ($dir as $fileinfo) {     
           $file_list_dates[$fileinfo->getFilename()] = array('file'=>$fileinfo->getFilename(),'date'=>date ("Y-m-d H:i:s",$fileinfo->getMTime()));
        }
      }
		return $file_list_dates;
	}
}