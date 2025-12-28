function write_joblog2_func($dataToWrite=array()) {

/* Input data:
	$ipAddr			= get_the_user_ip();
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}
				
	returns:
		array(TRUE,'Write was successful')	if OK
		array(FALSE,wpdb last error)			if failed
*/
	global $wpdb;
	$doDebug				= FALSE;
 	if (isset($dataToWrite['doDebug'])) {
 		$doDebug			= $dataToWrite['doDebug'];
 	}

	$jobname				= "Not Given";
	$jobdate				= "";				// will use current date time
	$jobtime				= "Not Given";
	$jobwho					= "Not Given";
	$jobmode				= "Not Given";
	$jobdatatype			= "Not Given";
	$jobaddlinfo			= "Not Given";
	$jobip					= "Not Given";
	$jobmonth				= "Not Given";
	$jobcomments			= "Not Given";
	$jobtitle				= "Not Given";
	$updateParams			= array();
	$updateFormat			= array();
	$joblogTableName		= "wpw1_cwa_joblog";

	if ($doDebug) {
		echo "<br />FUNCTION write_job_log called with dataToWrite:<br /><pre>";
		print_r($dataToWrite);
		echo "</pre><br />";
	}


	$initializationArray	= data_initialization_func();
	foreach($dataToWrite as $thisKey=>$thisValue) {
		$$thisKey			= $thisValue;
	}
	if ($jobdate == 'Not Given') {
		$jobdate			= date('Y-m-d H:i:s');
		if ($doDebug) {
			echo "jobdate not given. Using $jobdate<br />";
		}
	}
	if ($jobtitle == 'Not Given') {
		$jobtitle			= esc_html(get_the_title());
	}
	if ($jobip == 'Not Given') {
		$jobip				= get_the_user_ip();
	}
	if ($jobmonth == 'Not Given') {
		$jobmonth			= date('F Y');
	}
	
	$badJobname				= FALSE;
	if (preg_match('/jobname/',$jobname)) {
		$badJobname			= TRUE;
		$badCode			= 1;
	}
	if (preg_match('/FIX/',$jobname)) {
		$badJobname			= TRUE;
		$badCode			= 2;
	}
	if ($jobname == '') {
		$badJobname			= TRUE;
		$badCode			= 3;
	}
	if ($jobname == 'Not Given') {
		$badJobname			= TRUE;
		$badCode			= 4;
	}
	if ($badJobname) {
		$myStr				= print_r($dataToWrite,TRUE);
		sendErrorEmail("function_write_joblog2: Bad jobname $badCode. $jobtitle\n<br /><pre>$myStr</pre>");
	}
	
	// figure out if the job is automated
	if (stripos($_SERVER['HTTP_USER_AGENT'], 'curl') === 0) {
		// Define a constant so you know this is a background process
		if (!defined('IS_CRON')) {
			define('IS_CRON', true);
		}
	}

    if (defined('IS_CRON')) {
        $jobwho = 'CRON';
		$thisBrowser 		= '';
		$thisVersion 		= '';
		$thisOS 			= '';
		$thisMfgr 			= '';
		$thisDevice 		= '';
    } else {
		$jobIPData			= get_the_user_ip_data();
		if ($doDebug) {
			echo "jobIPData:<br /><pre>";
			print_r($jobIPData);
			echo "</pre><br />";
		}
		$thisBrowser 		= $jobIPData['browser'];
		$thisVersion 		= $jobIPData['version'];
		$thisOS 			= $jobIPData['OS'];
		$thisMfgr 			= $jobIPData['Mfgr'];
		$thisDevice 		= $jobIPData['device'];
	}
	$myStr = current_time('mysql', 1);
	$myDateTime = strtotime($myStr);
	$jobdate = date('Y-m-d', $myDateTime);
	$jobtime = date('H:i:s', $myDateTime);
	$updateParams			= array('job_name' 		=> $jobname,
									'job_date' 		=> $jobdate,
									'job_time'		=> $jobtime,
									'job_who' 		=> $jobwho,
									'job_mode'		=> $jobmode,
									'job_data_type' => $jobdatatype,
									'job_addl_info'	=> $jobaddlinfo,
									'job_ip_addr' 	=> $jobip,
									'job_month' 	=> $jobmonth,
									'job_comments' 	=> $jobcomments,
									'job_title' 	=> $jobtitle,
									'job_browser'	=> $thisBrowser,
									'job_version'	=> $thisVersion,
									'job_OS'		=> $thisOS,
									'job_Mfgr'		=> $thisMfgr,
									'job_device'	=> $thisDevice);
											

	$updateFormat			= array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');
		
	$result							= $wpdb->insert($joblogTableName,
													$updateParams,
													$updateFormat);
	if ($result === FALSE || $result === NULL) {
		if ($doDebug) {
			echo "Inserting $dataToWrite failed<br />
				  wpdb->last_query: " . $wpdb->last_query . "<br />
				  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
		$myStr				= $wpdb->last_error;
		sendErrorEmail("$jobname Writing joblog2 failed: $myStr");
		return FALSE;
	} else {
		if ($doDebug) {
			$insertID			= $wpdb->insert_id;
			echo "write to $joblogTableName was successful<br />
					id $insertID was inserted<br />
					updateParams:<br /><pre>";
			print_r($updateParams);
			echo "</pre><br />";
		}
//		// now add this info to the data_log
//		$fields_json = json_encode($updateParams);
//		$dateWritten = date('Y-m-d H:i:s');
//		$dataLogResult = $wpdb->insert('wpw1_cwa_data_log', 
//								array('data_date_written' => $dateWritten,
//									  'data_user' => $jobwho,
//									  'data_call_sign' => '',
//									  'data_table_name' => 'joblog',
//									  'data_action' => 'insert',
//									  'data_field_values' => $fields_json),
//								array('%s','%s','%s','%s','%s','%s'));
//		if ($dataLogResult === FALSE || $dataLogResult === NULL) {
//			handleWPDBError("FUNCTION_write_joblog2 ($jobname) insert returned FALSE");
//			return FALSE;
//		}
		return TRUE;
	}

}
add_action('write_joblog2_func','write_joblog2_func');