<?php
class page_userlog extends Page {
    //public $descr="I am playing with pages and Agile Tool Kit";
    function init(){
      parent::init();


      $user='alm';

      $p = $this;
      $p->add('H1')->set('You are logged and can proceed with work');
      $p->add('Button')->set('Logout')->js('click')->univ()->location($this->api->getDestinationURL('logout'));
    
      $f=$this->add('MVCForm');
      $f->setModel('User',array('login','password'))
          ->loadBy('login',$user);
      $f->addSubmit('Tell Me');
      
      $p->add('H2')->set($f->get('login').' has password '.$f->get('password'));

      if ($f->isSubmitted()) { 
          $varmsg = 'You '.$f->get('login').' told me password is '.$f->get('password');
          $f->js()->univ()->alert($varmsg)->execute();
      }
            
                                    
/*                
    $p = $page;
    $p->add('H1')->set('You are logged and can proceed with work');
    $p->add('Button')->set('Logout')->js('click')->univ()->location($this->api->getDestinationURL('logout'));

    $p->api->dbConnect();
    $sbanner = 'This stuff comes after the form';
    $suser = 'alm';  //I used 'shopuser' as one entry in 'login' field
    // the code for handling Model_user
    $usr=$p->add('Model_User');
    //$usr->debug();
    $usr->loadBy('login',$suser); //this will provoke Error in AJAX response: SyntaxError: invalid XML attribute value inside isSubmitted
    $pwd = $usr->get('password');
    $p->add('H2')->set($usr->get('login').' has password '.$pwd);
    $f=$p->add('Form');
    $f->addField('line','login','Login');
    $f->addField('line','password','Password');
    $f->addSubmit('Tell Me');  
    if ($f->isSubmitted()) { //if executing $usr->debug(); the submit will provoke the AJAX response error when submitting even if code is outside the isSubmitted
        $fvalue = true;
        $varmsg = 'You '.$f->get('login').' told me password is '.$f->get('password');
        $f->js()->univ()->alert($varmsg)->execute();
    }
    $p->add('H1')->set($sbanner);
*/
        }
    
}

class Model_User extends Model_Table {
    public $entity_code='user';
    public $table_alias='usr';

    function init(){
        parent::init();

        $this->addField('login');
    $this->addField('name');
    $this->addField('password');
    $this->addField('email');
    $this->addField('password_text');
    $this->addField('active')->type('int');
    }
}