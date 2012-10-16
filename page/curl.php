<?php
class Page_Curl extends Page {
  function init() {
    parent::init();
    
    $url='https://www.vakantieveilingen.nl/dagje-weg/voetbaltickets/nederland_andorra.html';
    
    $this->add('P')->set('Curl');
    // first url
    $c=$this->add('Curl');
    $c->url($url)
        ->useragent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:15.0) Gecko/20100101 Firefox/15.0.1')
        ;
    $c->go()->get();

    
    //handshake json
    
    $data=array(
      "channel"=>'/meta/handshake',
      "version"=>"1.0",
      "supportedConnectionTypes"=>array("long-polling"),
      "id"=>"1",
      ); 
    
    $data='['.stripslashes(json_encode($data)).']';
    $c->url('https://www.vakantieveilingen.nl/live/')
        ->useragent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:15.0) Gecko/20100101 Firefox/15.0.1')
        ->cookieExists(true)
        ->data($data)
        ->method('POST')
        ->contenttype('application/json')
        ->referer($url)
        ;
    $r= $c->go()->get();
    $r=json_decode($r,true);
var_dump($r);
    $clientid=$r[0]['clientId'];
    $id=$r[0]['id'];
    
    
    $id++;
    // get bids
        $data=array(
      "channel"=>'/meta/connect',
      "clientId"=>$clientid,
      "connectionType"=>"long-polling",
      "id"=>($id<10?chr(ord('0')+$id):chr(ord('a')+$id-10)),
      ); 
    
 echo   $data='['.stripslashes(json_encode($data)).']';
    
    $c->url('https://www.vakantieveilingen.nl/live/')
        ->useragent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:15.0) Gecko/20100101 Firefox/15.0.1')
        ->cookieExists(true)
        ->data($data)
        ->method('POST')
        ->contenttype('application/json')
        ->referer($url)
        ;
    $r= $c->go()->get();
    $r=json_decode($r);
var_dump($r);
  }
}