function semester_raw_comparison_statistics_func() {

	global $wpdb;

	$doDebug						= TRUE;
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
	$theURL						= "$siteURL/cwa-semester-raw-comparison-statistics/";
	$inp_semester				= FALSE;
	$inp_rsave					= FALSE;
	$inp_students				= FALSE;
	$inp_verified				= FALSE;
	$inp_declined				= FALSE;
	$inp_assigned				= FALSE;
	$inp_replaced				= FALSE;
	$inp_promoted				= FALSE;
	$inp_withdrawn				= FALSE;
	$inp_notpromoted			= FALSE;
	$inp_advisors				= FALSE;
	$inp_classes				= FALSE;
	
	$jobname					= "Semester Raw Comparison Statistics V$versionNumber";
	$levelArray					= array('Beginner'=>'beg',
										'Fundamental'=>'fun',
										'Intermediate'=>'int',
										'Advanced'=>'adv');			
	$countsArray				= array();				

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
//				$inp_semesterlist	 = filter_var($inp_semesterlist,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_students") {
				if ($str_value == 'students') {
					$inp_students	= TRUE;
					if ($doDebug) {
						echo "set inp_students to TRUE<br />";
					}
				}
			}
			if ($str_key 		== "inp_verified") {
				if ($str_value == 'verified') {
					$inp_verified	= TRUE;
					if ($doDebug) {
						echo "set inp_verified to TRUE<br />";
					}
				}
			}
			if ($str_key 		== "inp_declined") {
				if ($str_value == 'declined') {
					$inp_declined	= TRUE;
					if ($doDebug) {
						echo "set inp_declined to TRUE<br />";
					}
				}
			}
			if ($str_key 		== "inp_assigned") {
				if ($str_value == 'assigned') {
					$inp_assigned	= TRUE;
					if ($doDebug) {
						echo "set inp_assigned to TRUE<br />";
					}
				}
			}
			if ($str_key 		== "inp_replaced") {
				if ($str_value == 'replaced') {
					$inp_replaced	= TRUE;
					if ($doDebug) {
						echo "set inp_replaced to TRUE<br />";
					}
				}
			}
			if ($str_key 		== "inp_promoted") {
				if ($str_value == 'promoted') {
					$inp_promoted	= TRUE;
					if ($doDebug) {
						echo "set inp_promoted to TRUE<br />";
					}
				}
			}
			if ($str_key 		== "inp_withdrawn") {
				if ($str_value == 'withdrawn') {
					$inp_withdrawn	= TRUE;
					if ($doDebug) {
						echo "set inp_withdrawn to TRUE<br />";
					}
				}
			}
			if ($str_key 		== "inp_notpromoted") {
				if ($str_value == 'notpromoted') {
					$inp_notpromoted	= TRUE;
					if ($doDebug) {
						echo "set inp_notpromoted to TRUE<br />";
					}
				}
			}
			if ($str_key 		== "inp_advisors") {
				if ($str_value == 'advisors') {
					$inp_advisors	= TRUE;
					if ($doDebug) {
						echo "set inp_advisors to TRUE<br />";
					}
				}
			}
			if ($str_key 		== "inp_classes") {
				if ($str_value == 'classes') {
					$inp_classes	= TRUE;
					if ($doDebug) {
						echo "set inp_classes to TRUE<br />";
					}
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
	
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$studentTableName			= "wpw1_cwa_student2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting. Building semester option list<br />";
		}
		$thisIndex			= 0;
		$optionList			= "";
		$myInt				= count($pastSemestersArray) - 1;
		for ($ii=$myInt;$ii>-1;$ii--) {
	 		$thisSemester		= $pastSemestersArray[$ii];
	 		$thisIndex++;
			$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$thisSemester'> $thisSemester<br />";
			if ($doDebug) {
				echo "Added $thisSemester to option list<br />";
			}
		}
		$optionList		.= "----<br />";
		if ($currentSemester != 'Not in Session') {
			$thisIndex++;
			$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$currentSemester'> $currentSemester<br />";
		}
		$thisIndex++;
		$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$nextSemester' > $nextSemester<br />";		
		$thisIndex++;
		$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$semesterTwo'> $semesterTwo<br />";		
		$thisIndex++;
		$optionList		.= "<input type='checkbox' class='formInputButton' name='inp_semesterlist[$thisIndex]' value='$semesterThree'> $semesterThree<br />";
		if ($doDebug) {
			echo "optionlist complete<br />";
		}
		$content 		.= "<h3>$jobname</h3>
							<p>Select the semesters to be included and click Submit to run the program
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px; vertical-align:top;'>Semester</td><td>
								$optionList
								</td></tr>
							<tr><td style='vertical-align:top;'><b>Counts to be Included</b></td>
								<td><input type='checkbox' class='formInputButton' name='inp_students' value='students'>Enrolled Students<br />
									<input type='checkbox' class='formInputButton' name='inp_verified' value='verified'>Verified Students<br />
									<input type='checkbox' class='formInputButton' name='inp_declined' value='declined'>Students who Declined<br />
									<input type='checkbox' class='formInputButton' name='inp_assigned' value='assigned'>Assigned Students<br />
									<input type='checkbox' class='formInputButton' name='inp_replaced' value='replaced'>Replaced Students<br />
									<input type='checkbox' class='formInputButton' name='inp_promoted' value='promoted'>Promoted Students<br />
									<input type='checkbox' class='formInputButton' name='inp_withdrawn' value='withdrawn'>Withdrawn Students<br />
									<input type='checkbox' class='formInputButton' name='inp_notpromoted' value='notpromoted'>Not Promoted Students<br />
									<input type='checkbox' class='formInputButton' name='inp_advisors' value='advisors'>Advisor Counts<br />
									<input type='checkbox' class='formInputButton' name='inp_classes' value='classes'>Advisor Classes<br />
								</td></tr>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />
			inp_semesterlist: <br /><pre>";
			print_r($inp_semesterlist);
			echo "</pre><br />";
		}
		
		$content	.= "<h3>$jobname</h3>";
		foreach($inp_semesterlist as $thisSeq => $theSemester) {
			// setup the array
			$countsArray[$theSemester]['students']['total']		= 0;
			$countsArray[$theSemester]['students']['beg']		= 0;
			$countsArray[$theSemester]['students']['fun']		= 0;
			$countsArray[$theSemester]['students']['int']		= 0;
			$countsArray[$theSemester]['students']['adv']		= 0;

			$countsArray[$theSemester]['verified']['total']	= 0;
			$countsArray[$theSemester]['verified']['beg']		= 0;
			$countsArray[$theSemester]['verified']['fun']		= 0;
			$countsArray[$theSemester]['verified']['int']		= 0;
			$countsArray[$theSemester]['verified']['adv']		= 0;

			$countsArray[$theSemester]['assigned']['total']		= 0;
			$countsArray[$theSemester]['assigned']['beg']		= 0;
			$countsArray[$theSemester]['assigned']['fun']		= 0;
			$countsArray[$theSemester]['assigned']['int']		= 0;
			$countsArray[$theSemester]['assigned']['adv']		= 0;

			$countsArray[$theSemester]['promoted']['total']		= 0;
			$countsArray[$theSemester]['promoted']['beg']		= 0;
			$countsArray[$theSemester]['promoted']['fun']		= 0;
			$countsArray[$theSemester]['promoted']['int']		= 0;
			$countsArray[$theSemester]['promoted']['adv']		= 0;

			$countsArray[$theSemester]['advisors']['total']		= 0;
			$countsArray[$theSemester]['advisors']['beg']		= 0;
			$countsArray[$theSemester]['advisors']['fun']		= 0;
			$countsArray[$theSemester]['advisors']['int']		= 0;
			$countsArray[$theSemester]['advisors']['adv']		= 0;

			$countsArray[$theSemester]['classes']['total']		= 0;
			$countsArray[$theSemester]['classes']['beg']		= 0;
			$countsArray[$theSemester]['classes']['fun']		= 0;
			$countsArray[$theSemester]['classes']['int']		= 0;
			$countsArray[$theSemester]['classes']['adv']		= 0;

			$countsArray[$theSemester]['declined']['total']		= 0;
			$countsArray[$theSemester]['declined']['beg']		= 0;
			$countsArray[$theSemester]['declined']['fun']		= 0;
			$countsArray[$theSemester]['declined']['int']		= 0;
			$countsArray[$theSemester]['declined']['adv']		= 0;

			$countsArray[$theSemester]['replaced']['total']		= 0;
			$countsArray[$theSemester]['replaced']['beg']		= 0;
			$countsArray[$theSemester]['replaced']['fun']		= 0;
			$countsArray[$theSemester]['replaced']['int']		= 0;
			$countsArray[$theSemester]['replaced']['adv']		= 0;

			$countsArray[$theSemester]['withdrawn']['total']		= 0;
			$countsArray[$theSemester]['withdrawn']['beg']		= 0;
			$countsArray[$theSemester]['withdrawn']['fun']		= 0;
			$countsArray[$theSemester]['withdrawn']['int']		= 0;
			$countsArray[$theSemester]['withdrawn']['adv']		= 0;

			$countsArray[$theSemester]['notpromoted']['total']		= 0;
			$countsArray[$theSemester]['notpromoted']['beg']		= 0;
			$countsArray[$theSemester]['notpromoted']['fun']		= 0;
			$countsArray[$theSemester]['notpromoted']['int']		= 0;
			$countsArray[$theSemester]['notpromoted']['adv']		= 0;

			// get the students
			$studentSQL		= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_semester = '$theSemester'";
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
						
						$countsArray[$theSemester]['students']['total']++;
						$countsArray[$theSemester]['students'][$prefix]++;
						
						if ($student_response == 'Y') {
							$countsArray[$theSemester]['verified']['total']++;
							$countsArray[$theSemester]['verified'][$prefix]++;
						}
						if ($student_status == 'S' || $student_status == 'Y') {
							$countsArray[$theSemester]['assigned']['total']++;
							$countsArray[$theSemester]['assigned'][$prefix]++;
						}
						if ($student_promotable == 'P') {
							$countsArray[$theSemester]['promoted']['total']++;
							$countsArray[$theSemester]['promoted'][$prefix]++;
						}
						
						if ($student_response == 'R') {
							$countsArray[$theSemester]['declined']['total']++;
							$countsArray[$theSemester]['declined'][$prefix]++;
						}
						
						if ($student_status == 'C' || $student_status == 'R' || $student_status == 'V') {
							$countsArray[$theSemester]['replaced']['total']++;
							$countsArray[$theSemester]['replaced'][$prefix]++;
						}
						
						if ($student_promotable == 'W') {
							$countsArray[$theSemester]['withdrawn']['total']++;
							$countsArray[$theSemester]['withdrawn'][$prefix]++;
						}

						if ($student_promotable == 'N') {
							$countsArray[$theSemester]['notpromoted']['total']++;
							$countsArray[$theSemester]['notpromoted'][$prefix]++;
						}
						
						
					}
				} else {
					$content	.= "<p>No students found in $studentTableName table for $theSemester semester</p>";
				}
			}
			// get the advisors
			$advisorClassSQL	= "select * from $advisorClassTableName 
									where advisorclass_semester = '$theSemester'";
			$wpw1_cwa_advisorclass	= $wpdb->get_results($advisorClassSQL);
			if ($wpw1_cwa_advisorclass === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numACRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $advisorClassSQL<br />and found $numACRows rows<br />";
				}
				if ($numACRows > 0) {
					$prevAdvisor	= "";
					foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
						$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
						$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
						$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
						$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
						$advisorClass_level 					= $advisorClassRow->advisorclass_level;
						$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
						$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
						$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
						$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
						$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
						$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
						$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
						$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
						$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
						$advisorClass_student01 				= $advisorClassRow->advisorclass_student01;
						$advisorClass_student02 				= $advisorClassRow->advisorclass_student02;
						$advisorClass_student03 				= $advisorClassRow->advisorclass_student03;
						$advisorClass_student04 				= $advisorClassRow->advisorclass_student04;
						$advisorClass_student05 				= $advisorClassRow->advisorclass_student05;
						$advisorClass_student06 				= $advisorClassRow->advisorclass_student06;
						$advisorClass_student07 				= $advisorClassRow->advisorclass_student07;
						$advisorClass_student08 				= $advisorClassRow->advisorclass_student08;
						$advisorClass_student09 				= $advisorClassRow->advisorclass_student09;
						$advisorClass_student10 				= $advisorClassRow->advisorclass_student10;
						$advisorClass_student11 				= $advisorClassRow->advisorclass_student11;
						$advisorClass_student12 				= $advisorClassRow->advisorclass_student12;
						$advisorClass_student13 				= $advisorClassRow->advisorclass_student13;
						$advisorClass_student14 				= $advisorClassRow->advisorclass_student14;
						$advisorClass_student15 				= $advisorClassRow->advisorclass_student15;
						$advisorClass_student16 				= $advisorClassRow->advisorclass_student16;
						$advisorClass_student17 				= $advisorClassRow->advisorclass_student17;
						$advisorClass_student18 				= $advisorClassRow->advisorclass_student18;
						$advisorClass_student19 				= $advisorClassRow->advisorclass_student19;
						$advisorClass_student20 				= $advisorClassRow->advisorclass_student20;
						$advisorClass_student21 				= $advisorClassRow->advisorclass_student21;
						$advisorClass_student22 				= $advisorClassRow->advisorclass_student22;
						$advisorClass_student23 				= $advisorClassRow->advisorclass_student23;
						$advisorClass_student24 				= $advisorClassRow->advisorclass_student24;
						$advisorClass_student25 				= $advisorClassRow->advisorclass_student25;
						$advisorClass_student26 				= $advisorClassRow->advisorclass_student26;
						$advisorClass_student27 				= $advisorClassRow->advisorclass_student27;
						$advisorClass_student28 				= $advisorClassRow->advisorclass_student28;
						$advisorClass_student29 				= $advisorClassRow->advisorclass_student29;
						$advisorClass_student30 				= $advisorClassRow->advisorclass_student30;
						$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
						$advisorClass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
						$advisorClass_class_comments			= $advisorClassRow->advisorclass_class_comments;
						$advisorClass_copycontrol				= $advisorClassRow->advisorclass_copy_control;

						$prefix				= $levelArray[$advisorClass_level];

						if ($advisorClass_call_sign != $prevAdvisor) {
							$countsArray[$theSemester]['advisors']['total']++;
							$countsArray[$theSemester]['advisors'][$prefix]++;
						}
						$prevAdvisor		= $advisorClass_call_sign;

						$countsArray[$theSemester]['classes']['total']++;
						$countsArray[$theSemester]['classes'][$prefix]++;
					}
				} else {
					$content		.= "<p>No advisorClass records found in $advisorClassTableName table</p>";
				}
			}
		}
		// counts obtained display and export
		
		// setup csv file
		$outFileName		= "semester_comparison_" . $userName . ".csv";
		if (preg_match('/localhost/',$siteURL)) {
			$thisFileName	= "wp-content/uploads/$outFileName";
		} else {
			$thisFileName	= "/home/cwacwops/public_html/wp-content/uploads/$outFileName";
		}
		$thisFP			= fopen($thisFileName,'w');

	//// output the semesters
		$csvRecord				= array();
		$displayReport			= "<h4>Raw Data</h4>
									<table>
									<tr><th>Category</th>";
		$csvRecord[]			= 'Category';
		foreach($inp_semesterlist as $thisSeq => $thisSemester) {
			$displayReport		.= "<th>$thisSemester</th>";
			$csvRecord[]		= $thisSemester;
		}
		fputcsv($thisFP,$csvRecord,"\t");

	// output the students
		if ($inp_students) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total Enrolled</td>";
			$csvRecord[]			= 'Total Enrolled';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['students']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg Enrolled</td>";
			$csvRecord[]			= 'Beg Enrolled';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['students']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun Enrolled</td>";
			$csvRecord[]			= 'Fun Enrolled';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['students']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int Enrolled</td>";
			$csvRecord[]			= 'Int Enrolled';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['students']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Enrolled</td>";
			$csvRecord[]			= 'Adv Enrolled';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['students']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}

	// output declined, if requested	
		if ($inp_declined) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total Declined</td>";
			$csvRecord[]			= 'Total Declined';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['declined']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg Declined</td>";
			$csvRecord[]			= 'Beg Declined';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['declined']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun Declined</td>";
			$csvRecord[]			= 'Fun Declined';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['declined']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int Declined</td>";
			$csvRecord[]			= 'Int Declined';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['declined']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Declined</td>";
			$csvRecord[]			= 'Adv Declined';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['declined']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}
		

	// output verified
		if ($inp_verified) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total Verified</td>";
			$csvRecord[]			= 'Total Verified';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['verified']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg Verified</td>";
			$csvRecord[]			= 'Beg Verified';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['verified']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun Verified</td>";
			$csvRecord[]			= 'Fun Verified';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['verified']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int Verified</td>";
			$csvRecord[]			= 'Int Verified';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['verified']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Verified</td>";
			$csvRecord[]			= 'Adv Verified';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['verified']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}

	// output assigned
		if ($inp_assigned) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total Assigned</td>";
			$csvRecord[]			= 'Total Assigned';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['assigned']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg Assigned</td>";
			$csvRecord[]			= 'Beg Assigned';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['assigned']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun Assigned</td>";
			$csvRecord[]			= 'Fun Assigned';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['assigned']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int Assigned</td>";
			$csvRecord[]			= 'Int Assigned';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['assigned']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Assigned</td>";
			$csvRecord[]			= 'Adv Assigned';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['assigned']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}

	// output Replaced if requested
		if ($inp_replaced) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total Replaced</td>";
			$csvRecord[]			= 'Total Replaced';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['replaced']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg Replaced</td>";
			$csvRecord[]			= 'Beg Replaced';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['replaced']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun Replaced</td>";
			$csvRecord[]			= 'Fun Replaced';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['replaced']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int Replaced</td>";
			$csvRecord[]			= 'Int Replaced';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['replaced']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Replaced</td>";
			$csvRecord[]			= 'Adv Replaced';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['replaced']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}

	// output promoted
		if ($inp_promoted) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total Promoted</td>";
			$csvRecord[]			= 'Total Promoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['promoted']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg Promoted</td>";
			$csvRecord[]			= 'Beg Promoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['promoted']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun Promoted</td>";
			$csvRecord[]			= 'Fun Promoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['promoted']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int Promoted</td>";
			$csvRecord[]			= 'Int Promoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['promoted']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Promoted</td>";
			$csvRecord[]			= 'Adv Promoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['promoted']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}

	// output Withdrawn, if requested
		if ($inp_withdrawn) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total Withdrawn</td>";
			$csvRecord[]			= 'Total Withdrawn';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['withdrawn']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg Withdrawn</td>";
			$csvRecord[]			= 'Beg Withdrawn';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['withdrawn']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun Withdrawn</td>";
			$csvRecord[]			= 'Fun Withdrawn';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['withdrawn']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int Withdrawn</td>";
			$csvRecord[]			= 'Int Withdrawn';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['withdrawn']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Withdrawn</td>";
			$csvRecord[]			= 'Adv Withdrawn';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['withdrawn']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}

	// output NotPromoted, if requested
		if ($inp_notpromoted) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total NotPromoted</td>";
			$csvRecord[]			= 'Total NotPromoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['notpromoted']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg NotPromoted</td>";
			$csvRecord[]			= 'Beg NotPromoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['notpromoted']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun NotPromoted</td>";
			$csvRecord[]			= 'Fun NotPromoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['notpromoted']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int NotPromoted</td>";
			$csvRecord[]			= 'Int NotPromoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['notpromoted']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv NotPromoted</td>";
			$csvRecord[]			= 'Adv NotPromoted';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['notpromoted']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}

	// output advisors
		if ($inp_advisors) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total Advisors</td>";
			$csvRecord[]			= 'Total Advisors';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['advisors']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg Advisors</td>";
			$csvRecord[]			= 'Beg Advisors';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['advisors']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun Advisors</td>";
			$csvRecord[]			= 'Fun Advisors';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['advisors']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int Advisors</td>";
			$csvRecord[]			= 'Int Advisors';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['advisors']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Advisors</td>";
			$csvRecord[]			= 'Adv Advisors';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['advisors']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}

	// output classes
		if ($inp_classes) {
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Total Classes</td>";
			$csvRecord[]			= 'Total Classes';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['classes']['total'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Beg Classes</td>";
			$csvRecord[]			= 'Beg Classes';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['classes']['beg'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Fun Classes</td>";
			$csvRecord[]			= 'Fun Classes';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['classes']['fun'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td>Int Classes</td>";
			$csvRecord[]			= 'Int Classes';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['classes']['int'];
				$displayReport		.= "<td>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
			$csvRecord				= array();
			$displayReport			.= "</tr>\n<tr><td style='border-bottom:1px solid black;'>Adv Classes</td>";
			$csvRecord[]			= 'Adv Classes';
			foreach($inp_semesterlist as $thisSeq => $thisSemester) {
				$thisData			= $countsArray[$thisSemester]['classes']['adv'];
				$displayReport		.= "<td style='border-bottom:1px solid black;'>$thisData</td>";
				$csvRecord[]		= $thisData;
			}
			fputcsv($thisFP,$csvRecord,"\t");
		}
		
		$displayReport			.= "</tr></table>";
		fclose($thisFP);		
		
		$content				.= "$displayReport
									<p>Click <a href='$siteURL/wp-content/uploads/$outFileName'>$outFileName</a> to 
									download $outFileName file</p>
									<p>To use this data, import the downloaded $outFileName file into a spreadsheet.</p>";
		
		
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
add_shortcode ('semester_raw_comparison_statistics', 'semester_raw_comparison_statistics_func');

