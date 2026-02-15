function semester_raw_statistics_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 						= $context->validUser;

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$userName			= $context->userName;
	$currentTimestamp	= $context->currentTimestamp;
	$validTestmode		= $context->validTestmode;
	$proximateSemester	= $context->proximateSemester;
	$currentSemester			= $context->currentSemester;
	$nextSemester				= $context->nextSemester;
	$semesterTwo				= $context->semesterTwo;
	$semesterThree				= $context->semesterThree;
	$pastSemestersArray			= $context->pastSemestersArray;
	$inp_semesterlist			= '';
	$siteURL			= $context->siteurl;
	$userEmail			= $context->userEmail;
	$userDisplayName	= $context->userDisplayName;
	$userRole			= $context->userRole;
	
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
	$theURL						= "$siteURL/cwa-semester-raw-statistics/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Semester Raw Statistics V$versionNumber";
	$levelArray					= array('Beginner'=>'beg',
										'Fundamental'=>'fun',
										'Intermediate'=>'int',
										'Advanced'=>'adv');			
				
	$studentsCount				= 0;
	$beg_studentsCount			= 0;
	$fun_studentsCount			= 0;
	$int_studentsCount			= 0;
	$adv_studentsCount			= 0;
	
	$validatedCount				= 0;
	$beg_validatedCount			= 0;
	$fun_validatedCount			= 0;
	$int_validatedCount			= 0;
	$adv_validatedCount			= 0;
	

	$assignedCount				= 0;
	$beg_assignedCount			= 0;
	$fun_assignedCount			= 0;
	$int_assignedCount			= 0;
	$adv_assignedCount			= 0;

	$promotedCount				= 0;
	$beg_promotedCount			= 0;
	$fun_promotedCount			= 0;
	$int_promotedCount			= 0;
	$adv_promotedCount			= 0;
	
	// counts by country
	$countryArray				= array();

	// country => prefix => count

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
			if ($str_key 		== "inp_semesterlist") {
				$inp_semesterlist	 = $str_value;
				$inp_semesterlist	 = filter_var($inp_semesterlist,FILTER_UNSAFE_RAW);
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
		$studentTableName			= "wpw1_cwa_student2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting. Building semester option list<br />";
		}
		$optionList			= "";
		$thisChecked		= "";
		if ($currentSemester != 'Not in Session') {
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$currentSemester' checked='checked'> $currentSemester<br />";
		} else {
			$thisChecked	= "checked";
		}
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$nextSemester' $thisChecked > $nextSemester<br />";		
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$semesterTwo'> $semesterTwo<br />";		
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$semesterThree'> $semesterThree<br />";
		$optionList		.= "----<br />";
		$myInt				= count($pastSemestersArray) - 1;
		for ($ii=$myInt;$ii>-1;$ii--) {
	 		$thisSemester		= $pastSemestersArray[$ii];
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$thisSemester'> $thisSemester<br />";
			if ($doDebug) {
				echo "Added $thisSemester to option list<br />";
			}
		}
		if ($doDebug) {
			echo "optionlist complete<br />";
		}
		$content 		.= "<h3>$jobname</h3>
							<p>Select the semester and click Submit to run the program
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px; vertical-align:top;'>Semester</td><td>
							$optionList
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
							left join $userMasterTableName on user_call_sign = student_call_sign 
							where student_semester = '$inp_semesterlist'";
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
					$student_master_ID 					= $studentRow->user_ID;
					$student_master_call_sign 			= $studentRow->user_call_sign;
					$student_first_name 				= $studentRow->user_first_name;
					$student_last_name 					= $studentRow->user_last_name;
					$student_email 						= $studentRow->user_email;
					$student_ph_code					= $studentRow->user_ph_code;
					$student_phone 						= $studentRow->user_phone;
					$student_city 						= $studentRow->user_city;
					$student_state 						= $studentRow->user_state;
					$student_zip_code 					= $studentRow->user_zip_code;
					$student_country_code 				= $studentRow->user_country_code;
					$student_country 					= $studentRow->user_country;
					$student_whatsapp 					= $studentRow->user_whatsapp;
					$student_telegram 					= $studentRow->user_telegram;
					$student_signal 					= $studentRow->user_signal;
					$student_messenger 					= $studentRow->user_messenger;
					$student_master_action_log 			= $studentRow->user_action_log;
					$student_timezone_id 				= $studentRow->user_timezone_id;
					$student_languages 					= $studentRow->user_languages;
					$student_survey_score 				= $studentRow->user_survey_score;
					$student_is_admin					= $studentRow->user_is_admin;
					$student_role 						= $studentRow->user_role;
					$student_prev_callsign				= $studentRow->user_prev_callsign;
					$student_master_date_created 		= $studentRow->user_date_created;
					$student_master_date_updated 		= $studentRow->user_date_updated;

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
				
					if ($student_response == 'Y') {
						$validatedCount++;
						${$prefix . '_validatedCount'}++;
					}
					if ($student_status == 'S' || $student_status == 'Y') {
						$assignedCount++;
						${$prefix . '_assignedCount'}++;
					}
					if (!array_key_exists($student_country,$countryArray)) {
						$countryArray[$student_country]['enrolled']['total']	= 0;	
						$countryArray[$student_country]['enrolled']['beg']	= 0;	
						$countryArray[$student_country]['enrolled']['fun']	= 0;	
						$countryArray[$student_country]['enrolled']['int']	= 0;	
						$countryArray[$student_country]['enrolled']['adv']	= 0;	
						$countryArray[$student_country]['promoted']['total']	= 0;	
						$countryArray[$student_country]['promoted']['beg']	= 0;	
						$countryArray[$student_country]['promoted']['fun']	= 0;	
						$countryArray[$student_country]['promoted']['int']	= 0;	
						$countryArray[$student_country]['promoted']['adv']	= 0;	
					}
					if ($student_promotable == 'P') {
						$promotedCount++;
						${$prefix . '_promotedCount'}++;
						$countryArray[$student_country]['promoted'][$prefix]++;
						$countryArray[$student_country]['promoted']['total']++;
					}

					$countryArray[$student_country]['enrolled'][$prefix]++;
					$countryArray[$student_country]['enrolled']['total']++;
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
									<td>$validatedCount</td>
									<td>$beg_validatedCount</td>
									<td>$fun_validatedCount</td>
									<td>$int_validatedCount</td>
									<td>$adv_validatedCount</td></tr>

								<tr><td style=vertical-align:top;'>Assigned Students</td>
									<td>$assignedCount</td>
									<td>$beg_assignedCount</td>
									<td>$fun_assignedCount</td>
									<td>$int_assignedCount</td>
									<td>$adv_assignedCount</td></tr>

								<tr><td style=vertical-align:top;'>Promoted Students</td>
									<td>$promotedCount</td>
									<td>$beg_promotedCount</td>
									<td>$fun_promotedCount</td>
									<td>$int_promotedCount</td>
									<td>$adv_promotedCount</td></tr>";
				ksort($countryArray);
				foreach($countryArray as $country => $countryData) {
					$enrTotal	= $countryArray[$country]['enrolled']['total'];
					$enrBeg		= $countryArray[$country]['enrolled']['beg'];
					$enrFun		= $countryArray[$country]['enrolled']['fun'];
					$enrInt		= $countryArray[$country]['enrolled']['int'];
					$enrAdv		= $countryArray[$country]['enrolled']['adv'];
					$proTotal	= $countryArray[$country]['promoted']['total'];
					$proBeg		= $countryArray[$country]['promoted']['beg'];
					$proFun		= $countryArray[$country]['promoted']['fun'];
					$proInt		= $countryArray[$country]['promoted']['int'];
					$proAdv		= $countryArray[$country]['promoted']['adv'];
					$content	.= "<tr><td>$country Enrolled</td>
										<td>$enrTotal</td>
										<td>$enrBeg</td>
										<td>$enrFun</td>
										<td>$enrInt</td>
										<td>$enrAdv</td></tr>
									<tr><td>$country Promoted</td>
										<td>$proTotal</td>
										<td>$proBeg</td>
										<td>$proFun</td>
										<td>$proInt</td>
										<td>$proAdv</td></tr>";
				}
				$content		.= "</table>";

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
add_shortcode ('semester_raw_statistics', 'semester_raw_statistics_func');

