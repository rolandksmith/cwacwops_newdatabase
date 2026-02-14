function write_joblog_func($dataToWrite="",$doDebug=FALSE) {

/* Input data:
	job name|date (y-m-d)|time (h:i:s)|who|mode|data type|additional info|ip|comments
	
	Function formats the data to call write_joblog2_func
	
	returns:
		OK if successful
		FAIL if not successful

*/
	global $wpdb;
	$doDebug				= TRUE;

	$job_name				= "";
	$job_date				= "";
	$job_time				= "";
	$job_who				= "";
	$job_mode				= "";
	$job_data_type			= "";
	$job_addl_info			= "";
	$job_ip_addr			= "";
	$job_month				= "";
	$job_comments			= "";
	$job_title				= "";

	$updateParams			= array();

	if ($doDebug) {
		echo "<br />FUNCTION write_job_log called with dataToWrite: $dataToWrite<br />";
	}


	$context = CWA_Context::getInstance();
	$myArray							= explode("|",$dataToWrite);
	$thisII								= 0;
	for ($thisII= 0;$thisII < count($myArray);$thisII++) {
		switch($thisII) {
			case 0:
				$updateParams['jobname']			= $myArray[0];
				break;
			case 1:
				$updateParams['jobdate']			= $myArray[1];
				break;
			case 2:
				$updateParams['jobtime']			= $myArray[2];
				break;
			case 3:
				$updateParams['jobwho']				= $myArray[3];
			case 4:
				$updateParams['jobmode']			= $myArray[4];
				break;
			case 5:
				$updateParams['jobdatatype']		= $myArray[5];
				break;
			case 6:
				$updateParams['jobaddlinfo']		= $myArray[6];
				break;
			case 7:
				$updateParams['jobip']				= $myArray[7];
				break;
			case 8:
				$updateParams['jobcomments']		= $myArray[8];
				break;
		}
	}
	$updateParams['doDebug']						= $doDebug;
	
	if ($doDebug) {
		echo "passing these parameters to write_joblog2:<br /><pre>";
		print_r($updateParams);
		echo "</pre><br />";
	}
	$result		= write_joblog2_func($updateParams);
	if ($doDebug) {
		echo "joblog2 result:<br /><pre>";	
		print_r($result);
		echo "</pre><br />";	
	}
	if ($result) {
		return 'OK';
	} else {
		return 'FAIL';
	}
}
add_action('write_joblog_func','write_joblog_func');