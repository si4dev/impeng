<?php
class Page_Shopimport_Margin extends Page {
  function init() {
    parent::init();
    
    $this->add('H1')->set('margin');
    $this->add('P')->set('Hier kunnen de globale marge factors worden veranderd. Een marge factor moet boven de 0 zijn en zal meestal boven de 1 zijn. Een marge factor van 1.15 betekent 15% marge.De inkoop prijs wordt dan met 15% verhoogd en dat wordt de verkoop prijs. Per categorie kan er ook een marge factor worden ingesteld. De formule wordt dan :');
    $this->add('HTML')->set('<p><b>[verkoopprijs inclusief BTW] = ( [inkoopprijs exclusief BTW] * [globale marge factor] * [category marge factor] * [BTW] )</b></p>');
    $this->add('P')->set('De uitkomst wordt daarna afgerond. Per prijs groep kan een andere afronding worden ingesteld. Er kan bijvoorbeeld op hele of halve euro\'s worden afgerond. Ook kan met een offset van bijvoorbeeld -0.05 gezorgd worden dat de prijs altijd eindigd op xxx.95 euro. De offset wordt na de afronding doorgevoerd. Een offset zal meestal negatief en klein zijn.');
    
    
    
    $si=$this->add('Controller_Shopimport');
    $s=$si->shop;
    $u=$si->user;


    // margins: margin: from,marginfactor
    // roundings: from,rounding,offset

    $r=$this->add('Model_Rounding');
    //$r->setSource('Array',array('1'=>'100'));
    $r->setSource('Array',array(array('from'=>'100','value'=>'123','offset'=>'-0.95')));
    //$r->setStaticSource(array(array('from'=>'100','value'=>'123','offset'=>'-0.95')));
    $c=$this->add('CRUD');
    $c->setModel($r,array('from','value','offset'),array('from','value','offset') );
    
/*
    $r->set('id',1)
      ->set('from','v1')
      ->set('value','v2')
      ->set('offset','v3')
      ->save();
*/        
            
//    $roundings=$s->roundings();
  }
  
}