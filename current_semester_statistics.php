function current_semester_statistics_func() {

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
	$proximateSemester	= $initializationArray['proximateSemester'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return notAuthorized();
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
	$theURL						= "$siteURL/cwa-current-semester-statistics/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Current Semester Statistics V$versionNumber";
	$levelArray					= array('Beginner'=>'beg',
										'Fundamental'=>'fun',
										'Intermediate'=>'int',
										'Advanced'=>'adv');			
				
	$studentsCount				= 0;
	$beg_studentsCount			= 0;
	$fun_studentsCount			= 0;
	$int_studentsCount			= 0;
	$adv_studentsCount			= 0;
	
	$responseYCount				= 0;
	$beg_responseYCount			= 0;
	$fun_responseYCount			= 0;
	$int_responseYCount			= 0;
	$adv_responseYCount			= 0;
	
	$responseRCount				= 0;
	$beg_responseRCount			= 0;
	$fun_responseRCount			= 0;
	$int_responseRCount			= 0;
	$adv_responseRCount			= 0;
	
	$responseDefaultCount		= 0;
	$beg_responseDefaultCount	= 0;
	$fun_responseDefaultCount	= 0;
	$int_responseDefaultCount	= 0;
	$adv_responseDefaultCount	= 0;
	
	$statusBlankCount			= 0;
	$beg_statusBlankCount		= 0;
	$fun_statusBlankCount		= 0;
	$int_statusBlankCount		= 0;
	$adv_statusBlankCount		= 0;

	$statusSCount				= 0;
	$beg_statusSCount			= 0;
	$fun_statusSCount			= 0;
	$int_statusSCount			= 0;
	$adv_statusSCount			= 0;

	$statusYCount				= 0;
	$beg_statusYCount			= 0;
	$fun_statusYCount			= 0;
	$int_statusYCount			= 0;
	$adv_statusYCount			= 0;

	$statusCCount				= 0;
	$beg_statusCCount			= 0;
	$fun_statusCCount			= 0;
	$int_statusCCount			= 0;
	$adv_statusCCount			= 0;

	$statusRCount				= 0;
	$beg_statusRCount			= 0;
	$fun_statusRCount			= 0;
	$int_statusRCount			= 0;
	$adv_statusRCount			= 0;

	$statusVCount				= 0;
	$beg_statusVCount			= 0;
	$fun_statusVCount			= 0;
	$int_statusVCount			= 0;
	$adv_statusVCount			= 0;

	$statusDefaultCount			= 0;
	$beg_statusDefaultCount		= 0;
	$fun_statusDefaultCount		= 0;
	$int_statusDefaultCount		= 0;
	$adv_statusDefaultCount		= 0;

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
		$studentTableName			= "wpw1_cwa_student2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Click Submit to run the program
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}
		
		$content	.= "<h3>$jobname</h3>";
		// get the students
		$studentSQL		= "select * from $studentTableName 
							where student_semester = '$proximateSemester'";
		$wpw1_cwa_student	= $wpdb->get_results($studentSQL);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $studentSQL<br />and retrieved $numSRows rows from $studentTableName table<br >";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= $studentRow->student_call_sign;
					$student_time_zone  					= $studentRow->student_time_zone;
					$student_timezone_offset				= $studentRow->student_timezone_offset;
					$student_youth  						= $studentRow->student_youth;
					$student_age  							= $studentRow->student_age;
					$student_parent 						= $studentRow->student_parent;
					$student_parent_email  					= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->student_level;
					$student_waiting_list 					= $studentRow->student_waiting_list;
					$student_request_date  					= $studentRow->student_request_date;
					$student_semester						= $studentRow->student_semester;
					$student_notes  						= $studentRow->student_notes;
					$student_welcome_date  					= $studentRow->student_welcome_date;
					$student_email_sent_date  				= $studentRow->student_email_sent_date;
					$student_email_number  					= $studentRow->student_email_number;
					$student_response  						= strtoupper($studentRow->student_response);
					$student_response_date  				= $studentRow->student_response_date;
					$student_abandoned  					= $studentRow->student_abandoned;
					$student_status  						= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->student_action_log;
					$student_pre_assigned_advisor  			= $studentRow->student_pre_assigned_advisor;
					$student_selected_date  				= $studentRow->student_selected_date;
					$student_no_catalog  					= $studentRow->student_no_catalog;
					$student_hold_override  				= $studentRow->student_hold_override;
					$student_assigned_advisor  				= $studentRow->student_assigned_advisor;
					$student_advisor_select_date  			= $studentRow->student_advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->student_advisor_class_timezone;
					$student_hold_reason_code  				= $studentRow->student_hold_reason_code;
					$student_class_priority  				= $studentRow->student_class_priority;
					$student_assigned_advisor_class 		= $studentRow->student_assigned_advisor_class;
					$student_promotable  					= $studentRow->student_promotable;
					$student_excluded_advisor  				= $studentRow->student_excluded_advisor;
					$student_survey_completion_date	= $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->student_available_class_days;
					$student_intervention_required  		= $studentRow->student_intervention_required;
					$student_copy_control  					= $studentRow->student_copy_control;
					$student_first_class_choice  			= $studentRow->student_first_class_choice;
					$student_second_class_choice  			= $studentRow->student_second_class_choice;
					$student_third_class_choice  			= $studentRow->student_third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->student_first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->student_second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->student_third_class_choice_utc;
					$student_catalog_options				= $studentRow->student_catalog_options;
					$student_flexible						= $studentRow->student_flexible;
					$student_date_created 					= $studentRow->student_date_created;
					$student_date_updated			  		= $studentRow->student_date_updated;
					
					$prefix		= $levelArray[$student_level];
					
					$studentsCount++;
					${$prefix . '_studentsCount'}++;
				
					switch ($student_response) {
						case 'Y':
							$responseYCount++;
							${$prefix . '_responseYCount'}++;
							break;
						case 'R': 
							$responseRCount++;
							${$prefix . '_responseRCount'}++;
							break;
						default:
							$responseDefaultCount++;
							${$prefix . '_responseDefaultCount'}++;
					}
					
					switch ($student_status) {
						case (''):
							$statusBlankCount++;
							${$prefix . '_statusBlankCount'}++;
							break;
						case ('S'):
							$statusSCount++;
							${$prefix . '_statusSCount'}++;
							break;
						case ('Y'):
							$statusYCount++;
							${$prefix . '_statusYCount'}++;
							break;
						case ('C'):
							$statusCCount++;
							${$prefix . '_statusCCount'}++;
							break;
						case ('R'):
							$statusRCount++;
							${$prefix . '_statusRCount'}++;
							break;
						case ('V'):
							$statusVCount++;
							${$prefix . '_statusVCount'}++;
							break;
						default:
							$statusDefaultCount++;
							${$prefix . '_statusDefaultCount'}++;
						
					}
				}
				
				if($doDebug) {
					echo  "studentsCount: $studentsCount<br />
							beg_studentsCount: $beg_studentsCount<br />
							fun_studentsCount: $fun_studentsCount<br />
							int_studentsCount: $int_studentsCount<br />
							adv_studentsCount: $adv_studentsCount<br />
							responseYCount: $responseYCount<br />
							beg_responseYCount: $beg_responseYCount<br />
							fun_responseYCount: $fun_responseYCount<br />
							int_responseYCount: $int_responseYCount<br />
							adv_responseYCount: $adv_responseYCount<br />
							responseRCount: $responseRCount<br />
							beg_responseRCount: $beg_responseRCount<br />
							fun_responseRCount: $fun_responseRCount<br />
							int_responseRCount: $int_responseRCount<br />
							adv_responseRCount: $adv_responseRCount<br />
							responseDefaultCount: $responseDefaultCount<br />
							beg_responseDefaultCount: $beg_responseDefaultCount<br />
							fun_responseDefaultCount: $fun_responseDefaultCount<br />
							int_responseDefaultCount: $int_responseDefaultCount<br />
							adv_responseDefaultCount: $adv_responseDefaultCount<br />
							statusBlankCount: $statusBlankCount<br />
							beg_statusBlankCount: $beg_statusBlankCount<br />
							fun_statusBlankCount: $fun_statusBlankCount<br />
							int_statusBlankCount: $int_statusBlankCount<br />
							adv_statusBlankCount: $adv_statusBlankCount<br />
							statusSCount: $statusSCount<br />
							beg_statusSCount: $beg_statusSCount<br />
							fun_statusSCount: $fun_statusSCount<br />
							int_statusSCount: $int_statusSCount<br />
							adv_statusSCount: $adv_statusSCount<br />
							statusYCount: $statusYCount<br />
							beg_statusYCount: $beg_statusYCount<br />
							fun_statusYCount: $fun_statusYCount<br />
							int_statusYCount: $int_statusYCount<br />
							adv_statusYCount: $adv_statusYCount<br />
							statusCCount: $statusCCount<br />
							beg_statusCCount: $beg_statusCCount<br />
							fun_statusCCount: $fun_statusCCount<br />
							int_statusCCount: $int_statusCCount<br />
							adv_statusCCount: $adv_statusCCount<br />
							statusRCount: $statusRCount<br />
							beg_statusRCount: $beg_statusRCount<br />
							fun_statusRCount: $fun_statusRCount<br />
							int_statusRCount: $int_statusRCount<br />
							adv_statusRCount: $adv_statusRCount<br />
							statusVCount: $statusVCount<br />
							beg_statusVCount: $beg_statusVCount<br />
							fun_statusVCount: $fun_statusVCount<br />
							int_statusVCount: $int_statusVCount<br />
							adv_statusVCount: $adv_statusVCount<br />
							statusDefaultCount: $statusDefaultCount<br />
							beg_statusDefaultCount: $beg_statusDefaultCount<br />
							fun_statusDefaultCount: $fun_statusDefaultCount<br />
							int_statusDefaultCount: $int_statusDefaultCount<br />
							adv_statusDefaultCount: $adv_statusDefaultCount<br />";
				}
				$content	.= "<h4>Raw Counts</h4>
								<table>
								<tr><th style='width:175px;'>Category</th>
									<th style='width:140px;'>Total</th>
									<th style='width:140px;'>Beginner</th>
									<th style='width:140px;'>Fundamental</th>
									<th style='width:140px;'>Intermediate</th>
									<th style='width:140px;'>Advanced</th</tr>
								<tr><td style=vertical-align:top;'>Students Registered</td>
									<td>$studentsCount</td>
									<td>$beg_studentsCount</td>
									<td>$fun_studentsCount</td>
									<td>$int_studentsCount</td>
									<td>$adv_studentsCount</td></tr>

								<tr><td style=vertical-align:top;'>Verified Student</td>
									<td>$responseYCount</td>
									<td>$beg_responseYCount</td>
									<td>$fun_responseYCount</td>
									<td>$int_responseYCount</td>
									<td>$adv_responseYCount</td></tr>

								<tr><td style=vertical-align:top;'>Unverified Students</td>
									<td>$responseDefaultCount</td>
									<td>$beg_responseDefaultCount</td>
									<td>$fun_responseDefaultCount</td>
									<td>$int_responseDefaultCount</td>
									<td>$adv_responseDefaultCount</td></tr>

								<tr><td  style=vertical-align:top;'>Students who Refused</td>
									<td>$responseRCount</td>
									<td>$beg_responseRCount</td>
									<td>$fun_responseRCount</td>
									<td>$int_responseRCount</td>
									<td>$adv_responseRCount</td></tr>

								<tr><td style=vertical-align:top;'>Assigned Students</td>
									<td>$statusYCount</td>
									<td>$beg_statusYCount</td>
									<td>$fun_statusYCount</td>
									<td>$int_statusYCount</td>
									<td>$adv_statusYCount</td></tr>

								<tr><td style=vertical-align:top;'>Assigned but not Confirmed</td>
									<td>$statusSCount</td>
									<td>$beg_statusSCount</td>
									<td>$fun_statusSCount</td>
									<td>$int_statusSCount</td>
									<td>$adv_statusSCount</td></tr>

								<tr><td style=vertical-align:top;'>Unassigned Students</td>
									<td>$statusBlankCount</td>
									<td>$beg_statusBlankCount</td>
									<td>$fun_statusBlankCount</td>
									<td>$int_statusBlankCount</td>
									<td>$adv_statusBlankCount</td></tr>

								<tr><td style=vertical-align:top;'>Replaced Students</td>
									<td>$statusCCount</td>
									<td>$beg_statusCCount</td>
									<td>$fun_statusCCount</td>
									<td>$int_statusCCount</td>
									<td>$adv_statusCCount</td></tr>

								<tr><td style=vertical-align:top;'>Waiting Replacement</td>
									<td>$statusRCount</td>
									<td>$beg_statusRCount</td>
									<td>$fun_statusRCount</td>
									<td>$int_statusRCount</td>
									<td>$adv_statusRCount</td></tr>

								<tr><td style=vertical-align:top;'>Waiting for Reassignment</td>
									<td>$statusVCount</td>
									<td>$beg_statusVCount</td>
									<td>$fun_statusVCount</td>
									<td>$int_statusVCount</td>
									<td>$adv_statusVCount</td></tr>

								<tr><td style=vertical-align:top;'>Unknown Status</td>
									<td>$statusDefaultCount</td>
									<td>$beg_statusDefaultCount</td>
									<td>$fun_statusDefaultCount</td>
									<td>$int_statusDefaultCount</td>
									<td>$adv_statusDefaultCount</td></tr>
								</table>";

			} else {
				$content	.= "<p>No students found in $studentTableName table</p>";
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
		$storeResult	= storeReportData_func("Current Student and Advisor Assignments",$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports pod as $storeResult";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('current_semester_statistics', 'current_semester_statistics_func');
