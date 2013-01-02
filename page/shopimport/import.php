<?php
class Page_Shopimport_Import extends Page {
  function init() {
    parent::init();


    $s=$this->api->getShop();
    $c=$this->add('Grid_AssortmentLink');
    $c->setModel($s->ref('AssortmentLink'));

$c->addColumn('import','import');

    /*
    if($slink->Loaded()){
	//verify if user can import
		if($slink['is_owner'] == true){
			$b=$this->add('Button');
			$b->setLabel('Pricelist');

			$url = $this->api->getDestinationURL('job');

			$b->js('click')->univ()->redirect($url);
		}
	}
  */
  }
}

class Grid_AssortmentLink extends Grid {

  function init_import($field) {
    parent::init_button($field);
  }

  function format_import($field){
    if($this->current_row['is_owner']) {
      parent::format_button($field);
    } else {
      $this->current_row_html[$field]='';
    }
  }
}