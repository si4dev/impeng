<?php
class Page_Index extends Page {
  function init() {
    parent::init();
	if(isset($_GET['login_as'])){
		$this->api->auth->loginByID($_GET['login_as']);
	}
  }
}