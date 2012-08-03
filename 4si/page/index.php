<?php
class page_index extends page {
  function init() {
    parent::init();
    $this->add('H1')->set('Welkom '.$this->api->auth->get('login'));
  }
}