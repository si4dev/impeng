<?php

class Model_Job extends Model_Table{
	public $table = 'queue';

	function init(){
		parent::init();
		$this->addField('status');
		$this->hasOne('Shop');

	}

	function process($q){
		$job = $this->load($this->id);
		$job['status'] = 'processing';
		$job->save();
	}

	function end($id){
		//delete line when job is finished
		$this->delete($id);
	}
	//check if any job is running
	function check(){

		if(is_numeric($this->dsql()->where('status','processing')->get('id') )){
			return true;
		}
		else{
			return false;
		}
	}


}