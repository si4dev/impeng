<?php
class Page_Index extends Page {
  function init() {
    parent::init();

    $si=$this->add('Controller_Shopimport');
    $s=$si->shop;
    $u=$si->user;

    $this->add('H1')->set('Welkom');
    $this->add('P')->set('user: '.$u['email']);
    $this->add('P')->set('shop: '.$s['name']);
    // button change shop when count($u->ref('Shop')) > 1
  }
}