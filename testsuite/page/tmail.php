<?php
class page_tmail extends Page_Tester {
    function init(){
        parent::init();
    }
    function prepare(){
        return array($this->add('TMail'));
    }
    function test_verison($t){
        return $t->version;
    }
    function test_ex1($mail){
        $mail->addTransport('Tester');
        $mail->set('Quick Brown Fox');
        $mail->set('subject','Test Email');
        $mail->send('to@example.com','from@example.com');
        return $this->toutput;
    }
    function test_ex2($mail){
        $mail->addTransport('Tester');

        $mail->setTemplate('welcome');

        //$mail->addTransport('DBStore')->setModel('MailLog');
        $mail->addTransport('Fallback'); // use default sending method

        $mail->send('john@doe.uk');
        return $this->toutput;
    }
}
