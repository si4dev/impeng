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



    $r=$this->add('Model_Rounding');
    $r->setSource('Array',$s->shopconfig_r('rounding'));
/*
    $r->set('from',123);
    $r->set('value',1);
    $r->set('offset',-0.05);
    $r->save();
    
    $r->unload();
    $r->set('from',11);
    $r->set('value',1);
    $r->set('offset',-0.05);
    $r->save();
    $r->unload();
  
    $s->shopconfig2('rounding',$r)->save();
*/
    
    
    
$c=$this->add('CRUD');
$c->setModel($r);
if($c->form && $c->form->isSubmitted() ) {
  $s->shopconfig_r('rounding',$r)->save();
  }



$t=$r->table;



return;
  


    // margins: margin: from,marginfactor
    // roundings: from,rounding,offset


    $r=$this->add('Model_Rounding');
    //$r->setSource('Array',array('1'=>'100'));
   $r->setSource('Array',array(array('from'=>'100','value'=>'123','offset'=>'-0.95')));
    //$r->setStaticSource(array(array('from'=>'100','value'=>'123','offset'=>'-0.95')));
    $c=$this->add('CRUD');
    $r=$s->refRounding(); // returns model array associated for rounding
    
   // $c->setModel($r,array('from','value','offset'),array('from','value','offset') );
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