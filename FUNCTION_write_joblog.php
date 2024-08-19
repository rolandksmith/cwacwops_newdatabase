function write_joblog_func($dataToWrite="",$doDebug=FALSE) {

/* Input data:
	job name|date (y-m-d)|time (h:i:s)|who|mode|data type|additional info|ip|comments
	
	Function writes the dataToWrite the database
	
	returns:
		array('SUCCESS','Write was successful'	if OK
		array('FALSE',wpdb last error			if failed
*/
	global $wpdb;
//	$doDebug				= FALSE;

	$job_name				= "";
	$job_date				= "";
	$job_time				= "";
	$job_who				= "";
	$job_mode				= "";
	$job_data_type			= "";
	$job_addl_info			= "";
	$job_ip_addr			= "";
	$job_comments			= "";
	$updateParams			= array();
	$updateFormat			= array();
	$joblogTableName		= "wpw1_cwa_joblog";

	if ($doDebug) {
		echo "<br />FUNCTION write_job_log called with dataToWrite: $dataToWrite<br />";
	}


	$initializationArray				= data_initialization_func();
	$myArray							= explode("|",$dataToWrite);
	$updateParams['job_name']			= $myArray[0];
	$updateParams['job_date']			= $myArray[1];
	$updateParams['job_time']			= $myArray[2];
	$updateParams['job_who']			= $myArray[3];
	$updateParams['job_mode']			= $myArray[4];
	$updateParams['job_data_type']		= $myArray[5];
	$updateParams['job_addl_info']		= $myArray[6];

	if (array_key_exists(7,$myArray)) {
		$updateParams['job_ip_addr']	= $myArray[7];
	} else {
		$ipAddr							= get_the_user_ip();
		$updateParams['job_ip_addr']	= $ipAddr;
	}

	if (array_key_exists(8,$myArray)) {
		$updateParams['job_comments']	= $myArrray[8];
	} else {
		$updateParams['job_comments']	= 'None supplied';
	}
	$updateParams['job_month']			= date('F Y');
	$updateFormat						= array('%s','%s','%s','%s','%s','%s','%s','%s','%s');

	$result							= $wpdb->insert($joblogTableName,
													$updateParams,
													$updateFormat);
	if ($result === FALSE) {
		if ($doDebug) {
			echo "Inserting $dataToWrite failed<br />
				  wpdb->last_query: " . $wpdb->last_query . "<br />
				  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
		$myStr				= $wpdb->last_error;
		return array('FALSE',$myStr);
	} else {
		return array('SUCCESS','Write was successful');
	}

}
add_action('write_joblog_func','write_joblog_func');