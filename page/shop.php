<?php
class page_shop extends Page {
  function init() {
    parent::init();
    
    
    
    
    
    $c=$this->add('CRUD');
    $c->setModel('Model_Shop');
    if ($c->grid){ 
      
        $c->grid->addPaginator(10); 
        $c->grid->addColumn('button','pricelist');
        if($_GET['pricelist']){
          //$this->api->memorize('shop',$_GET['pricelist']);
           // $c->grid->js(null,$c->grid->js()->univ()->successMessage('Imported batch #'.$_GET['pricelist'].''.$r))->reload()->execute();
          
          // learn how to redirect to other page. http://agiletoolkit.org/doc/grid/interaction 
          // replace dialogURL() with location() and drop first argument. 
          // also for non ajax add api redirect http://agiletoolkit.org/doc/form/submit
          $p=$this->api->getDestinationURL(
                'pricelist',array(
                'shop'=> $_GET['pricelist']
                ));
          $c->js()->univ()->location($p)->execute();
        
          $this->api->redirect($p);
        }

    } 
    

  // http://codepad.agiletoolkit.org/hangman 
  // memorize !



  }

 
}

