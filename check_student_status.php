function check_student_status_func() {

/*
	Modified 15Apr23 by Roland to fix action_log
	Modified 12Jul23 by Roland to use consolidated tables
	Modifled 17Nov23 by Roland for new Student Portal
	Modified 12Oct24 by Roland for new database
*/
	global $wpdb,$doDebug, $testMode, $advisorClassTableName, $userMasterTableName;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 					= $initializationArray['validUser'];
	$userName  					= $initializationArray['userName'];
	$userRole					= $initializationArray['userRole'];
	$validTestmode				= $initializationArray['validTestmode'];
	$siteURL					= $initializationArray['siteurl'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$semesterFour				= $initializationArray['semesterFour'];
	$jobname					= 'Check Student Status';

	if ($userName == '') {
		$content				= "You are not authorized";
		return $content;
	}
	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);


	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	}

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-check-student-status/";
	$inp_semester				= '';
	$inp_level					= '';
	$inp_callsign				= '';
	$inp_email					= '';
	$inp_phone					= '';
	$inp_mode					= '';
	$inp_verbose				= '';
	$inp_verified				= FALSE;
	$studentRegistrationURL		= "$siteURL/cwa-student-registration/";
	

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
			if ($str_key 		== "encstr") {
				$encstr			 = $str_value;
				$ensctr			= filter_var($encstr,FILTER_UNSAFE_RAW);
				$encodedString	= base64_decode($encstr);
				$myArray		= explode("&",$encodedString);
				foreach($myArray as $thisValue) {
					$encArray	= explode("=",$thisValue);
					$myStr		= $encArray[0];
					${$myStr}	= $encArray[1];
					if ($doDebug) {
						echo "encstr contained $encArray[0] = $encArray[1]<br />";
					}
				}
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
				if ($inp_verbose == 'verbose') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_email") {
				$inp_email	 = strtolower($str_value);
				$inp_email	 = filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_level") {
				$inp_level	 = $str_value;
				$inp_level	 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_phone") {
				$inp_phone	 = $str_value;
				$inp_phone	 = filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verified") {
				$inp_verified	 = $str_value;
				$inp_verified	 = filter_var($inp_verified,FILTER_UNSAFE_RAW);
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

	function getClassInfo($theSemester,$theAdvisor,$theClass,$student_time_zone) {

		global $wpdb,$doDebug, $testMode, $advisorClassTableName, $userMasterTableName;
	
		if ($doDebug) {
			echo "FUNCTION: getClassInfo: $theSemester, $theAdvisor, $theClass, $student_time_zone<br />";
		}
		$sql					= "select * from $advisorClassTableName 
									left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
									where advisor_call_sign='$theAdvisor' 
									and sequence=$theClass 
									and semester='$theSemester' 
									order by advisor_call_sign";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass !== FALSE) {
			if ($doDebug) {
				handleWPDBError($jobname,$doDebug);
			}
			$numACRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and obtained $numACRows from $advisorClassTableName table<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_master_ID 				= $advisorClassRow->user_ID;
					$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
					$advisorClass_first_name 				= $advisorClassRow->user_first_name;
					$advisorClass_last_name 				= $advisorClassRow->user_last_name;
					$advisorClass_email 					= $advisorClassRow->user_email;
					$advisorClass_phone 					= $advisorClassRow->user_phone;
					$advisorClass_city 						= $advisorClassRow->user_city;
					$advisorClass_state 					= $advisorClassRow->user_state;
					$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
					$advisorClass_country_code 				= $advisorClassRow->user_country_code;
					$advisorClass_whatsapp 					= $advisorClassRow->user_whatsapp;
					$advisorClass_telegram 					= $advisorClassRow->user_telegram;
					$advisorClass_signal 					= $advisorClassRow->user_signal;
					$advisorClass_messenger 				= $advisorClassRow->user_messenger;
					$advisorClass_action_log 				= $advisorClassRow->user_action_log;
					$advisorClass_timezone_id 				= $advisorClassRow->user_timezone_id;
					$advisorClass_languages 				= $advisorClassRow->user_languages;
					$advisorClass_survey_score 				= $advisorClassRow->user_survey_score;
					$advisorClass_is_admin					= $advisorClassRow->user_is_admin;
					$advisorClass_role 						= $advisorClassRow->user_role;
					$advisorClass_master_date_created 		= $advisorClassRow->user_date_created;
					$advisorClass_master_date_updated 		= $advisorClassRow->user_date_updated;

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
				}
				$result						= utcConvert('tolocal',$student_time_zone,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc);
				if ($result[0] == 'FAIL') {
					if ($doDebug) {
						echo "utcConvert failed 'tolocal',$student_time_zone,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc<br />
							  Error: $result[3]<br />";
					}
					$displayDays			= "ERROR: UTC Convert failed";
					$displayTimes			= '';
					$returnInfo				= array(FALSE,$displayTimes,$displayDays);
				} else {
					$displayTimes			= $result[1];
					$displayDays			= $result[2];
					$returnInfo				= array(TRUE,$displayTimes,$displayDays);
					if ($doDebug) {
						echo "Returned class schedue $displayTimes on $displayDays<br />";
					}
				}
			} else {
				$returnInfo					= array(FALSE,'','ERROR: No such class');
				if ($doDebug) {
					echo "No class record found. Should have been one.<br />";
				}
			}
		} else {
			if ($doDebug) {
				echo "Either $advisorClassTableName not found or bad $sql 01<br />";
			}
			$returnInfo		= array(FALSE,'','ERROR: SQL 01');
		}
		return $returnInfo;
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
		$studentTableName			= "wpw1_cwa_student2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$userMasterTableName		= "wpw1_cwa_user_master2";
	} else {
		$studentTableName			= "wpw1_cwa_student";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$userMasterTableName		= "wpw1_cwa_user_master";
	}

	$optionList						= "";
	if ($currentSemester != 'Not in Session') {
		$optionList					.= "<option value='$currentSemester'>$currentSemester</option><br />";
	}
	$optionList						.= "<option value='$nextSemester'>$nextSemester</option><br />
										<option value='$semesterTwo'>$semesterTwo</option><br />
										<option value='$semesterThree'>$semesterThree</option><br />
										<option value='$semesterFour'>$semesterFour</option><br />";

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting. userRole: $userRole<br />";
		}

		// set the userName
		if ($userRole == 'student') {
			$userName		= strtoupper($userName);
			$inp_callsign	= $userName;
			$strPass		= "2";
		} elseif ($userRole == 'administrator') {
			$content		.= "<h3>$jobname Administrator Role</h3>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								Call Sign: <br />
								<table style='border-collapse:collapse;'>
								<tr><td>Student Call Sign</td>
									<td><input type='text' class='formInputText' name='inp_callsign' size='10' maxlength='10' value='$inp_callsign' autofocus></td>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
								</form>";


		}
	}


///// Pass 2 -- do the work


	if ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass $strPass with:<br />
				  Call Sign: $inp_callsign<br />";
		}
		///// based on the info entered, get the student registration
		$sql				= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_call_sign='$inp_callsign'
								and (student_semester = '$currentSemester' 
								or student_semester = '$nextSemester' 
								or student_semester = '$semesterTwo' 
								or student_semester = '$semesterThree' 
								or student_semester = 'semesterFour')  
								order by student_date_created DESC 
								limit 1";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_master_ID 					= $studentRow->user_ID;
					$student_master_call_sign 			= $studentRow->user_call_sign;
					$student_first_name 				= $studentRow->user_first_name;
					$student_last_name 					= $studentRow->user_last_name;
					$student_email 						= $studentRow->user_email;
					$student_phone 						= $studentRow->user_phone;
					$student_city 						= $studentRow->user_city;
					$student_state 						= $studentRow->user_state;
					$student_zip_code 					= $studentRow->user_zip_code;
					$student_country_code 				= $studentRow->user_country_code;
					$student_whatsapp 					= $studentRow->user_whatsapp;
					$student_telegram 					= $studentRow->user_telegram;
					$student_signal 					= $studentRow->user_signal;
					$student_messenger 					= $studentRow->user_messenger;
					$student_action_log 				= $studentRow->user_action_log;
					$student_timezone_id 				= $studentRow->user_timezone_id;
					$student_languages 					= $studentRow->user_languages;
					$student_survey_score 				= $studentRow->user_survey_score;
					$student_is_admin					= $studentRow->user_is_admin;
					$student_role 						= $studentRow->user_role;
					$student_master_date_created 		= $studentRow->user_date_created;
					$student_master_date_updated 		= $studentRow->user_date_updated;

					$student_ID								= $studentRow->student_id;
					$student_call_sign						= $studentRow->student_call_sign;
					$student_time_zone  					= $studentRow->student_time_zone;
					$student_timezone_offset				= $studentRow->student_timezone_offset;
					$student_youth  						= $studentRow->student_youth;
					$student_age  							= $studentRow->student_age;
					$student_parent 				= $studentRow->student_parent;
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
				
					if ($doDebug) {
						echo "Processing $student_call_sign<br />
								Semester: $student_semester<br />
								Student Status: $student_status<br />
								Assigned advisor: $student_assigned_advisor<br />
								Assigned Class: $student_assigned_advisor_class<br />
								Time Zone: $student_time_zone<br />";
					}
					$catalogOptions		= "";
					$daysToSemester	= days_to_semester($student_semester);
					if ($doDebug) {
						echo "daysToSemester $student_semester: $daysToSemester<br />";
					}
					$content			.= "<h3>Check Student Status for $student_call_sign<h3>
											<p>Your current registration information:<br />
											<table style='width:auto;'>
											<tr><td>Semester</td>
												<td>$student_semester</td></tr>
											<tr><td>Level</td>
												<td>$student_level</td></tr>";
												
					if ($student_response == 'R') {
						$content		.= "<tr><td>Response</td>
												<td>REFUSED -- you have indicated that you are not 
													available to take a class.</td></tr></table>
													<p>If you have further questions, concerns, or want 
													the status changed, contact the appropriate person at 
													<a href='https://cwops.org/cwa-class-resolution/' 
													target='_blank'>CW Academy Class Resolution</a>.</p>";
					} else {
						if ($daysToSemester > 48) {
							if ($student_response == 'Y') {
								sendErrorEmail("$jobname -- $userName -- response is set to Y and more 
than 48 days before the semester. Possible error");
							}
							if ($student_no_catalog == 'Y') {
								$thisOptions				= '';
								if ($student_catalog_options != '') {
									$myArray				= explode(",",$student_catalog_options);
									foreach($myArray as $thisData) {
										$thisOptions	.= "$thisData<br >";
									}
								} else {
									if ($student_flexible == 'Y') {
										$thisOptions	= "Flexible";
									} else {
										$thisOptions		= 'None Selected';
									}
								}										
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>$thisOptions</td></tr>
												</table>
												<p>About 45 days before the semester begins 
												you will receive an email requesting you to review your class 
												preferences and update them. The NEW catalog will be available 
												by then. <span style='color:red;'>You <b>MUST</b> respond to that 
												email or you will not be considered for assignment to a class</span></p>";
							} else {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>First: $student_first_class_choice<br />
														Second: $student_second_class_choice<br />
														Third: $student_third_class_choice</td></tr>
												</table>
												<p>About 45 days before the semester begins 
												you will receive an email requesting you to review your class 
												preferences and update them. An updated catalog will be available 
												by then. <span style='color:red;'>You <b>MUST</b> respond to that 
												email or you will not be considered for assignment to a class</span></p>";
							}
						} elseif ($daysToSemester > 20) {			/// assignments haven't happened yet
							if ($student_no_catalog == 'Y' && $student_response == '') {
								$passPhone				= substr($student_phone,-5,5);
								$stringToPass			= "inp_callsign=$student_call_sign&inp_phone=$passPhone&inp_email=$student_email&inp_mode=$inp_mode&strPass=2&inp_verbose=$inp_verbose&inp_verify=Y";
								$content	.= "</table>
												<p>You have not verified your class 
												preferences as requested in earlier emails from CW Academy. 
												If you want to be considered for assignment to a class, 
												you <span style='color:red;'><b>MUST</b></span> go to 
												<a href='$studentRegistrationURL'>Student Sign-up</a> 
												and update your preferred class schedule and alternates.";
							} else {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>First: $student_first_class_choice<br />
														Second: $student_second_class_choice<br />
														Third: $student_third_class_choice</td></tr>
												</table>
												<p>Student assignment to advisor classes will not occur until about 
												20 days before the $student_semester starts. Until then, no additional status information is available.</p>";
								if ($doDebug) {
									echo "Student assignment has not happened<br />";
								}
							}
						} elseif ($daysToSemester > 0 && $daysToSemester < 21) {
							if ($student_response == '') {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>First: $student_first_class_choice<br />
														Second: $student_second_class_choice<br />
														Third: $student_third_class_choice</td></tr>
												</table>
												<p>You have not responded to CW Academy verification request emails. 
											   Check your email, including the spam and promotions folders for email from 
											   CW Academy and respond appropriately</p>";
							} elseif ($student_response == 'Y') {
								if ($student_status == '') {
									$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>$student_first_class_choice<br />
														$student_second_class_choice<br />
														$student_third_class_choice</td></tr>
												</table>
												<p>You are on a waiting list for assignment to a class should a student drop out and a 
													vacancy arise. You will receive more information before the semester starts. If you do not get assigned 
													to a class, your sign up will be automatically moved to the next semester and you will be given heightened 
													priority for assignment to a class.</p>";
								} elseif ($student_status == 'S') {
									$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
													<td>$student_first_class_choice<br />
														$student_second_class_choice<br />
														$student_third_class_choice</td></tr>
												</table>
												<p>You have been assigned to a class. 
													Your advisor should contact you within the next few days to give you the actual class schedule and 
													confirm that you will be able to participate in this class.</p>";								
								} elseif ($student_status == 'R' || $student_status == 'C' || $student_status == 'N' || $student_status == 'V') {
									$content	.= "</table><p>You were assigned to a class, however the 
													advisor has removed you from the class. Either the class did not 
													meet your needs or you did not respond to the advisor.</p>";
								} elseif ($student_status == 'Y') {
									$content	.= "<tr><td style='vertical-align:top;'>Assigned Advisor</td>
														<td>$student_assigned_advisor</td></tr>
													</table>
													<p>You have been assigned to a class and your advisor has contacted you to give 
													you the actual class schedule and confirmed that you will be able to participate in this class.</p>";
								}
							} else {
								$content	.= "<p>You were registered for the $student_semester semester for a 
												$student_level class. You said that you were not available 
												to take a class and your registration has been cancelled.</p>";
							}								
						} elseif ($daysToSemester < 0) {
							if ($student_status == '') {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
												<td>$student_first_class_choice<br />
													$student_second_class_choice<br />
													$student_third_class_choice</td></tr>
											</table>
											<p>The semester is already underway. You are on a waiting list for assignment to a class should a student drop out and a 
												vacancy arise. If you do not get assigned 
												to a class, your sign up will be automatically moved to the next semester and you will be given heightened 
												priority for assignment to a class.</p>";
							} elseif ($student_status == 'S') {
								$content	.= "<tr><td style='vertical-align:top;'>Class Preferences</td>
												<td>$student_first_class_choice<br />
													$student_second_class_choice<br />
													$student_third_class_choice</td></tr>
											</table>
											<p>You have been assigned to a class. 
												Your advisor should have already contacted you within the next few days to give you the actual class schedule and 
												confirm that you will be able to participate in this class. For further assistance, please contact the appropriate 
												person at <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA Class Resolution</a>.</p>";								
							} elseif ($student_status == 'R' || $student_status == 'C' || $student_status == 'C' || $student_status == 'V') {
								$content	.= "</table><p>You were assigned to a class, however the 
												advisor has removed you from the class. Either the class did not 
												meet your needs or you did not respond to the advisor. If you need further assistance, please contact 
												the appropriate person at <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA Class Resolution</a>.</p>";	
							} elseif ($student_status == 'Y' && $student_promotable == '') {
								$content	.= "<tr><td style='vertical-align:top;'>Assigned Advisor</td>
													<td>$student_assigned_advisor</td></tr>
												</table>
												<p>The semester is underway. You have been assigned to a class and your advisor has contacted you to give 
												you the actual class schedule and confirmed that you will be able to participate in this class.</p>";
							} elseif ($student_status == 'Y' && $student_promotable != '') {
								if ($student_promotable == 'P') {
									$content	.= "</table>
													<p>You have successfully completed the class and can sign up for the next level class</p>";
								} elseif ($student_promotable == 'W') {
									$content	.= "</table>
													<p>You withdrew from the class. You can sign up for a class</p>";
								} elseif ($student_promotable == 'N') {
									$content	.= "</table>
													<p>The class is complete, however your advisor marked you as not having met the class criteria. 
													You should sign up to take the class again</p>";
								} else {
									$content	.= "</table>
													<p>The class is complete. You may sign up for a future class</p>";
								}
								
							} else {
								$content	.= "<p>You were registered for the $student_semester semester for a 
												$student_level class. You said that you were not available 
												to take a class and your registration has been cancelled.</p>";
							}								
						}
				
						$content		.= "<p>If you have further questions or concerns, contact the appropriate person at 
											<a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CW Academy Class Resolution</a>.</p>
										<p>Thanks!<br />CW Academy</p>";
					}
				}
			} else {
				$content		.= "<h3>Student Registration Check</h3>
									<p>No sign up record found for Call Sign: $inp_callsign</p>
									<p>Have you signed up?</p>";
			}
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("Check Student Status|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('check_student_status', 'check_student_status_func');
