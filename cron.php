<?php 

include 'atk4/loader.php';
$api= new ApiCLI('shopassist');

$job=$api->add('Model_Job');
$j = $job->dsql()
	->where('status', 'queued')
	->get('id');

	foreach ($j as $q) {
		$qj = $q->TryLoadBy('id', $q);
		if($qj->loaded()){
			$qj->process($q);
			//launch import processing..
			
			$qj->end($q);
		}
	}
