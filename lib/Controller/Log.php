<?php
class Controller_Log extends AbstractController {
  function init() {
    parent::init();
  
  }
  
  function start() {
    // only log when log model is set
    if( $this->model ){
      $this->model->start(); // start logging in database
      
    
    