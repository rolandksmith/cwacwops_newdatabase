function display_data_log_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
		$doDebug			= FALSE;
		$testMode			= FALSE;
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
//	} else {
//		$wpdb->hide_errors();
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-display-data-log/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Display Data Log V$versionNumber";

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br />\n";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_rsave") {
				$inp_rsave		 = $str_value;
				$inp_rsave		 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
				}
			}
			if ($str_key 		== "inp_table_name") {
				$inp_table_name	 = $str_value;
				$inp_table_name	 = filter_var($inp_table_name,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_record_id") {
				$inp_record_id	 = strtoupper($str_value);
				$inp_record_id	 = filter_var($inp_record_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_jobsince") {
				$inp_jobsince	 = strtoupper($str_value);
				$inp_jobsince	 = filter_var($inp_jobsince,FILTER_UNSAFE_RAW);
			}
		}
	}
	
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
							<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
								<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
	}
	
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$dataLogTableName			= "wpw1_cwa_data_log2";
	} else {
		$extMode					= 'pd';
		$dataLogTableName			= "wpw1_cwa_data_log";
	}

	$productionTableNameArray = array('advisor'=>'wpw1_cwa_advisor',
									  'advisorclass'=>'wpw1_cwa_advisorclass',
									  'student'=>'wpw1_cwa_student',
									  'user'=>'wpw1_cwa_user_master',
									  'everything'=>'everything',
									  'joblog'=>'joblog');
	$testmodeTableNameArray = array('advisor'=>'wpw1_cwa_advisor2',
									  'advisorclass'=>'wpw1_cwa_advisorclass2',
									  'student'=>'wpw1_cwa_student2',
									  'user'=>'wpw1_cwa_user_master2',
									  'everything'=>'everything',
									  'joblog'=>'joblog');

	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Select Table Name
								<td><input type='radio' class='formInputButton' name='inp_table_name' value='advisor' >Advisor<br />
									<input type='radio' class='formInputButton' name='inp_table_name' value='advisorclass' >AdvisorClass<br />
									<input type='radio' class='formInputButton' name='inp_table_name' value='student' >Student<br />
									<input type='radio' class='formInputButton' name='inp_table_name' value='user' >User_Master<br />
									<input type='radio' class='formInputButton' name='inp_table_name' value='joblog' >Job Log<br />
									<input type='radio' class='formInputButton' name='inp_table_name' value='everything' checked>Everything</td></tr>
							<tr><td style='vertical-align:top;'>Callsign to Display</td>
								<td><input type='text' class='formInputText' size='25' maxlength='25' name='inp_callsign'></td></tr>
							<tr><td style='vertical-align:top;'>Everything since record</td>
								<td><input type='text' class='formInputText' size='10' maxlength='10' name='inp_record_id' ></td></tr>
							<tr><td style='vertical-align:top;'>Job Log since</td>
								<td><input type='text' class='formInputText' size='20' maxlength='20' name='inp_jobsince' value='0000-00-00 00:00:0' ></td></tr>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2 with inp_table_name $inp_table_name and inp_callsign $inp_callsign<br />";
		}
		
		$theTableName = 'everything';
		if ($inp_table_name != 'everything') {
			// get the correct table name
			if ($testMode) {
				$theTableName = $testmodeTableNameArray[$inp_table_name];
			} else {
				$theTableName = $productionTableNameArray[$inp_table_name];
			}
		}
		$content .= "<h3>$jobname</h3>";
		
		$sql = '';

		// set up the sql based on what was requested
		if ($inp_callsign != '') {
			if ($inp_table_name == 'everything') {
				$sql = "select * from $dataLogTableName 
						where data_call_sign = '$inp_callsign' 
						order by data_date_written ASC";
			} else {
				$sql = "select * from $dataLogTableName 
						where data_call_sign = '$inp_callsign' 
						and data_table_name = '$theTableName'
						order by data_date_written ASC";
			}
		} elseif ($inp_callsign == '' && $inp_table_name == 'joblog') {
				$sql = "select * from $dataLogTableName 
						where data_call_sign = '$inp_callsign' 
						and data_table_name = '$theTableName' 
						and data_date_written >= '$inp_jobsince' 
						order by data_date_written ASC";
		} else {
			$myInt = intval($inp_record_id);
			$sql = "select * from $dataLogTableName 
					where data_record_id >= $myInt  
					order by data_record_id ASC";
		}
		
		if ($sql != '') {
			$getResults = $wpdb->get_results($sql);
			if ($getResults === FALSE) {
				$content .= "<p>Attempting to get data from $theTableName returned FALSE</p>";
			} else {
				$numRecords = $wpdb->num_rows;
				$myLastQuery = $wpdb->last_query;
				if ($doDebug) {
					echo "ran $myLastQuery<br />and retrieved $numRecords rows<br />";
				}
				if ($numRecords > 0) {
					$content .= "Parameters:<br />
								 Table Name: $inp_table_name<br />
								 Call Sign: $inp_callsign<br />
								 Starting Record ID: $inp_record_id<br />
								 Joblog Since: $inp_jobsince<br />";
					$content .= "<table style='width:1200px;'>
								<tr><th>Record</th>
									<th>Date</th>
									<th>User</th>
									<th>Call Sign</th>
									<th>Table</th>
									<th>Action</th>
									<th>Affected Fields</th></tr>";
					foreach($getResults as $getResultsRow) {
						$data_record_id = $getResultsRow->data_record_id;
						$data_date_written = $getResultsRow->data_date_written;
						$data_user = $getResultsRow->data_user;
						$data_call_sign = $getResultsRow->data_call_sign;
						$data_table_name = $getResultsRow->data_table_name;
						$data_action = $getResultsRow->data_action;
						$data_field_values = $getResultsRow->data_field_values;
						
						$fieldsArray = json_decode($data_field_values,TRUE);
						if ($doDebug) {
							echo "fieldsArray:<br /><pre>";
							print_r($fieldsArray);
							echo "</pre><br />";
						}
						$fieldsDisplay = '';
						foreach($fieldsArray as $thisField => $thisValue) {
							if (preg_match("/action_log/i",$thisField)) {
								$thisValue = formatActionLog($thisValue);
							}
							$fieldsDisplay .= "<b>$thisField set to:</b> $thisValue<br />";
						}
						
						$content .= "<tr><td style='vertical-align:top;'>$data_record_id</td>
										<td style='vertical-align:top;'>$data_date_written</td>
										<td style='vertical-align:top;'>$data_user</td>
										<td style='vertical-align:top;'>$data_call_sign</td>
										<td style='vertical-align:top;'>$data_table_name</td>
										<td style='vertical-align:top;'>$data_action</td>
										<td style='vertical-align:top;'>$fieldsDisplay</td></tr>";
					}
					$content .= "</table>";
				} else {
					$content .= "<p>No records found</p>";
				}
			}
		} else {
			$content .= "<p>Invalid Data Request</p>";
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";

	///// uncomment if the code to save a report is needed
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Current Student and Advisor Assignments<br />";
		}
		$storeResult	= storeReportData_v2($jobname,$content);
		if ($storeResult[0] !== FALSE) {
			$reportName	= $storeResult[1];
			$reportID	= $storeResult[2];
			$content	.= "<br />Report stored in reports as $reportName<br />
							Go to'Display Saved Reports' or url<br/>
							$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID<br /><br />";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}

	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
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
	return $content;
}
add_shortcode ('display_data_log', 'display_data_log_func');

