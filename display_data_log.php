function display_data_log_func() {

	global $wpdb;

	$doDebug						= TRUE;
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
	
	
	$content = "<style type='text/css'>
		fieldset {font:'Times New Roman', sans-serif;color:#666;background-image:none;
		background:#efefef;padding:2px;border:solid 1px #d3dd3;}

		legend {font:'Times New Roman', sans-serif;color:#666;font-weight:bold;
		font-variant:small-caps;background:#d3d3d3;padding:2px 6px;margin-bottom:8px;}

		label {font:'Times New Roman', sans-serif;font-weight:bold;line-height:normal;
		text-align:right;margin-right:10px;position:relative;display:block;float:left;width:150px;}

		textarea.formInputText {font:'Times New Roman', sans-serif;color:#666;
		background:#fee;padding:2px;border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}

		textarea.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

		textarea.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}

		input.formInputText {color:#666;background:#fee;padding:2px;
		border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}

		input.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

		input.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}

		input.formInputFile {color:#666;background:#fee;padding:2px;border:
		solid 1px #f66;margin-right:5px;margin-bottom:5px;height:20px;}

		input.formInputFile:focus {color:#000;background:#ffffff;border:solid 1px #006600;}

		select.formSelect {color:#666;background:#fee;padding:2px;
		border:solid 1px #f66;margin-right:5px;margin-bottom:5px;cursor:pointer;}

		select.formSelect:hover {color:#333;background:#ccffff;border:solid 1px #006600;}

		input.formInputButton {vertical-align:middle;font-weight:bolder;
		text-align:center;color:#300;background:#f99;padding:1px;border:solid 1px #f66;
		cursor:pointer;position:relative;float:left;}

		input.formInputButton:hover {color:#f8f400;}

		input.formInputButton:active {color:#00ffff;}

		tr {color:#333;background:#eee;}

		table{font:'Times New Roman', sans-serif;background-image:none;border-collapse:collapse;}

		th {color:#ffff;background-color:#000;padding:5px;font-size:small;}

		td {padding:5px;font-size:small;}

		th:first-child,
		td:first-child {
		 padding-left: 10px;
		}

		th:last-child,
		td:last-child {
			padding-right: 5px;
		}
		</style>";	

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
									  'user'=>'wpw1_cwa_user_master');
	$testmodeTableNameArray = array('advisor'=>'wpw1_cwa_advisor2',
									  'advisorclass'=>'wpw1_cwa_advisorclass2',
									  'student'=>'wpw1_cwa_student2',
									  'user'=>'wpw1_cwa_user_master2');

	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Select Table Name
								<td><input type='radio' class='formInputButton' name='inp_table_name' value='advisor' required>Advisor<br />
									<input type='radio' class='formInputButton' name='inp_table_name' value='advisorclass' required>AdvisorClass<br />
									<input type='radio' class='formInputButton' name='inp_table_name' value='student' required>Student<br />
									<input type='radio' class='formInputButton' name='inp_table_name' value='user' required>User_Master<br />
									<input type='radio' class='formInputButton' name='inp_table_name' value='everything' checked required>Everything</td></tr>
							<tr><td style='vertical-align:top;'>Callsign to Display</td>
								<td><input type='text' class='formInputText' size='25' maxlength='25' name='inp_callsign' reqired></td></tr>
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
		if ($inp_table_name != 'everything') {
			// get the correct table name
			if ($testMode) {
				$theTableName = $testmodeTableNameArray[$inp_table_name];
			} else {
				$theTableName = $productionTableNameArray[$inp_table_name];
			}
			$content .= "<h3>$jobname</h3>
						<h4>Showing Entries for $inp_callsign in Table $theTableName</h4>";
			$getResults = $wpdb->get_results("select * from $dataLogTableName 
											where data_call_sign = '$inp_callsign' and data_table_name = '$theTableName'  
											order by data_date_written ASC");
			if ($getResults === FALSE) {
				$content .= "<p>Attempting to get data from $theTableName for callsign $inp_callsign returned FALSE</p>";
			} else {
				$numRecords = $wpdb->num_rows;
				if ($numRecords > 0) {
					$content .= "<table>
								<tr><th style='width:150px;'>Date</th>
									<th>Action</th>
									<th>Affected Fields</th></tr>";
					foreach($getResults as $getResultsRow) {
						$data_record_id = $getResultsRow->data_record_id;
						$data_date_written = $getResultsRow->data_date_written;
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
							$fieldsDisplay .= "<b>$thisField changed to:</b> $thisValue<br />";
						}
						
						$content .= "<tr><td style='vertical-align:top;'>$data_date_written</td>
										<td style='vertical-align:top;'>$data_action</td>
										<td style='vertical-align:top;'>$fieldsDisplay</td></tr>";
					}
					$content .= "</table>";
				} else {
					$content .= "<p>No records found in $theTableName for callsign $inp_callsign</p>";
				}
			}
		} else {			// show everything
			// set up the tablename check
			$everythingTableNameArray = array();
			if ($testMode) {
				foreach($testmodeTableNameArray as $shortName => $tableName) {
					$everythingTableNameArray[$tableName] = $shortName;
				}
			} else {
				foreach($productionTableNameArray as $shortName => $tableName) {
					$everythingTableNameArray[$tableName] = $shortName;
				}
			}
			$content .= "<h3>$jobname</h3>
						<h4>Showing All Entries for $inp_callsign</h4>";
			$getResults = $wpdb->get_results("select * from $dataLogTableName 
											where data_call_sign = '$inp_callsign' 
											order by data_date_written ASC");
			if ($getResults === FALSE) {
				$content .= "<p>Attempting to get data from $theTableName for callsign $inp_callsign returned FALSE</p>";
			} else {
				$numRecords = $wpdb->num_rows;
				if ($numRecords > 0) {
					$content .= "<table>
								<tr><th style='width:150px;'>Date</th>
									<th>Type</th>
									<th>Action</th>
									<th>Affected Fields</th></tr>";
					foreach($getResults as $getResultsRow) {
						$data_record_id = $getResultsRow->data_record_id;
						$data_date_written = $getResultsRow->data_date_written;
						$data_call_sign = $getResultsRow->data_call_sign;
						$data_table_name = $getResultsRow->data_table_name;
						$data_action = $getResultsRow->data_action;
						$data_field_values = $getResultsRow->data_field_values;

						// see if we want this record
						if (array_key_exists($data_table_name, $everythingTableNameArray)) {
							$shortTableName = $everythingTableNameArray[$data_table_name];
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
								$fieldsDisplay .= "<b>$thisField changed to:</b> $thisValue<br />";
							}
							
							$content .= "<tr><td style='vertical-align:top;'>$data_date_written</td>
											<td style='vertical-align:top;'>$shortTableName</td>
											<td style='vertical-align:top;'>$data_action</td>
											<td style='vertical-align:top;'>$fieldsDisplay</td></tr>";
						}
					}
					$content .= "</table>";
				} else {
					$content .= "<p>No records found in $theTableName for callsign $inp_callsign</p>";
				}
			}
		
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
