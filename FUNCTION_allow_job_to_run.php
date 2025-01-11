function allow_job_to_run($doDebug = FALSE) {

/*	Determines if the job should be run based on what time it is
	
	
	Old method:

		$dst				= date('I');
		if ($dst == 1) {
			$checkBegin 	= strtotime('13:50:00');
			$checkEnd 		= strtotime('14:30:00');
			$thisTime 		= date('H:i:s');
		
		} else {
			$checkBegin 	= strtotime('13:50:00');
			$checkEnd 		= strtotime('14:30:00');
			$thisTime 		= date('H:i:s');
		}

		$nowTime = strtotime($thisTime);
		if ($nowTime >= $checkBegin && $nowTime <= $checkEnd) {
			$runTheJob = TRUE;
		} else {
			$runTheJob = FALSE;
		}
*/
	date_default_timezone_set('America/Chicago');
	$checkBegin			= date('08:00:00');
	$checkEnd			= date('08:30:00');
	$nowTime			= date('H:i:s');
	if ($nowTime >= $checkBegin && $nowTime <= $checkEnd) {
		$runTheJob		= TRUE;
	} else {
		$runTheJob		= FALSE;
	}
	if ($doDebug) {
		if ($runTheJob) {
			$myStr		= 'TRUE';
		} else {
			$myStr		= 'FALSE';
		}
		echo "<br /><b>Allow Job to Run</b><br />
				checkBegin: $checkBegin<br />
				checkEnd: $checkEnd<br />
				nowTime: $nowTime<br />
				runTheJob: $myStr<br />";
	}
	return $runTheJob;

}
add_action('allow_job_to_run','allow_job_to_run');