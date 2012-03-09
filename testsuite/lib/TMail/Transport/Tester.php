<?php

class TMail_Transport_Tester extends TMail_Transport {
    function send($to,$from,$subject,$body,$headers){
        $this->owner->owner->toutput=$headers.$body;
    }
}
