function student_registration_func() {

	global $wpdb,$doDebug,$testMode,$demoMode,$inp_verbose,$daysToGo;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$demoMode						= FALSE;
	$doAssessment					= FALSE;
	$maintenanceMode				= FALSE;
	$verifyMode						= FALSE;
	$versionNumber					= '14a';
	$skipAssessment					= FALSE;
	
	$daysToGo						= 0;
	
	$initializationArray 		= data_initialization_func();
	$currentDate				= $initializationArray['currentDate'];
	$currentDateTime			= $initializationArray['currentDateTime'];
	$validTestmode				= $initializationArray['validTestmode'];
	$userName					= $initializationArray['userName'];
	$userRole					= $initializationArray['userRole'];
	$userEmail					= $initializationArray['userEmail'];
	$languageArray				= $initializationArray['languageArray'];
	$fakeIt						= "Y";
	$replacementPeriod			= $initializationArray['validReplacementPeriod'];
	
	if ($userName == '') {
		$content				= "Your are not authorized";
		return $content;
	}

	// see if we're running in Docker
	$dockerMode						= FALSE;
	if (is_file("/.dockerenv")) {
		// running in docker
		$dockerMode					= TRUE;
		if ($doDebug) {
			echo "running in dockerMode<br />";
		}
	}
	if (!$dockerMode) {
		if ($userRole != 'administrator') {				// turn off debug and testmode
			$doDebug					= FALSE;
			$testMode					= FALSE;
		}
	}

	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}

	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
	} else {
		$wpdb->hide_errors();
	}


/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$demonstration				= 'No';
	$jobname					= "Student Registration V$versionNumber";
	$errorString				= '';
	$inp_sked1					= '';
	$inp_sked2					= '';
	$inp_sked3					= '';
	$inp_sked_timex				= array();
	$inp_first_class_choice		= '';
	$inp_second_class_choice	= '';
	$inp_third_class_choice		= '';
	$inp_verbose				= '';
	$student_timezone			= '';
	$student_level				= '';
	$inp_mode					= '';
	$timezone					= '';
	$pass3FirstTime				= 'Y';
	$newInput					= 'N';
	$inp_doAgain				= '';
	$cur_level					= '';
	$cur_semester				= '';
	$waitingList				= FALSE;
	$increment					= 0;
	$token						= '';
	$validReplacementPeriod		= FALSE;
	if ($replacementPeriod == 'Y') {
		 $validReplacementPeriod = TRUE;
	}
	$siteURL					= $initializationArray['siteurl'];
	$thisMode						= ''; 	// Production(TESTMODE)
	$fieldTest						= array('action_log','control_code');
	$logDate						= date('Y-m-d H:i');
	$currentSemester				= $initializationArray['currentSemester'];
	$nextSemester					= $initializationArray['nextSemester'];
	$semesterTwo					= $initializationArray['semesterTwo'];
	$semesterThree					= $initializationArray['semesterThree'];
	$semesterFour					= $initializationArray['semesterFour'];
	$daysToSemester					= $initializationArray['daysToSemester'];
	$prevSemester					= $initializationArray['prevSemester'];
	$validEmailPeriod				= $initializationArray['validEmailPeriod'];
	$proximateSemester				= $initializationArray['proximateSemester'];
	$thisIP							= get_the_user_ip();
	$browser_timezone_id		 	= "";
	$submit							= "";
	$updateParams					= array();
	$firsttime							= '';
	$thisOption							= '';
	$enstrInfo							= '';
	$enstr								= '';
	$badTimezone						= "No";
	$allowSignup						= FALSE;
	$lastLevel							= "";
		$thisAction						= ''; 	// |UPDATE(ADD)
		$student_call_sign				= ''; 
		$user_first_name				= ''; 
		$user_last_name				= ''; 
		$user_email					= ''; 
		$user_phone					= ''; 
		$user_city					= ''; 
		$user_state					= ''; 
		$user_zip_code				= ''; 
		$user_country				= ''; 
		$student_time_zone				= ''; 
		$student_level					= ''; 
		$student_class_language			= '';
		$student_semester				= ''; 
		$student_youth					= ''; 
		$student_age					= ''; 
		$student_student_parent			= '';
		$student_student_parent_email	= '';
		$student_first_class_choice		= ''; 
		$student_second_class_choice	= ''; 
		$student_third_class_choice		= ''; 
		$student_id						= ''; 
		
		$student_waiting_list				= '';
		$student_request_date			= '';
		$student_notes					= '';
		$user_email_sent_date		= '';
		$user_email_number			= '';
		$student_response				= '';
		$student_response_date			= '';
		$student_abandoned 		= '';
		$student_student_status			= '';
		$student_advisor				= '';
		$student_selected_date			= '';
		$student_welcome_date			= '';
		$student_no_catalog				= '';
		$student_hold_override			= '';
		$student_assigned_advisor		= '';
		$student_advisor_class_timezone	= '';
		$student_advisor_select_date	= '';
		$student_hold_reason_code		= '';
		$student_class_priority			= '';
		$student_assigned_advisor_class	= '';
		$student_promotable				= '';
		$student_excluded_advisor		= '';
		$student_survey_completion_date = '';
		$student_available_class_days	= '';
		$student_intervention_required	= '';
		$thisProgram					= "REGISTRATION";
		$thisWho						= "Student";	
		$audioFileName					= '';
		$audioFileNumber				= '';	
		$new_semester					= 'not supplied';
		$doUpdate						= FALSE;


		$inp_callsign					= ''; 
		$inp_firstname					= ''; 
		$inp_lastname					= ''; 
		$inp_email						= ''; 
		$inp_phone						= ''; 
		$inp_ph_code					= '+1';
		$inp_city						= ''; 
		$inp_state						= ''; 
		$inp_zip						= ''; 
		$inp_country					= '';
		$inp_countrya					= '';
		$inp_countryb					= ''; 
		$inp_timezone					= ''; 
		$inp_level						= ''; 
		$inp_class_language				= '';
		$inp_semester					= '';
		$newSemester					= ''; 
		$inp_youth						= ''; 
		$inp_age						= ''; 
		$inp_student_parent				= '';
		$inp_student_parent_email		= '';
		$inp_first_class_choice			= ''; 
		$inp_second_class_choice		= ''; 
		$inp_third_class_choice			= ''; 
		$inp_ID							= ''; 
		$inp_number						= 0;
		$inp_whatsapp					= '';
		$inp_telegram					= '';
		$inp_signal						= '';
		$inp_messenger					= '';
		$inp_timezone_id				= '';
		$inp_timezone_offset			= '';
		$inp_days1						= '';
		$inp_days2						= '';
		$inp_days3						= '';
		$inp_times1						= '';
		$inp_times2						= '';
		$inp_times3						= '';
		$inp_bypass						= '';
		$inp_flex						= '';

		$inp_waiting_list				= '';
		$inp_request_date				= '';
		$inp_notes						= '';
		$inp_email_sent_date			= '';
		$inp_email_number				= '';
		$inp_response					= '';
		$inp_response_date				= '';
		$inp_abandoned 			= '';
		$inp_student_status				= '';
		$inp_advisor					= '';
		$inp_selected_date				= '';
		$inp_welcome_date				= '';
		$inp_no_catalog					= '';
		$inp_hold_override				= '';
		$inp_assigned_advisor			= '';
		$inp_advisor_class_timezone		= '';
		$inp_advisor_select_date		= '';
		$inp_hold_reason_code			= '';
		$inp_class_priority				= '';
		$inp_assigned_advisor_class		= '';
		$inp_promotable					= '';
		$inp_excluded_advisor			= '';
		$inp_survey_completion_date 	= '';
		$inp_available_class_days		= '';
		$inp_intervention_required		= '';
		$inp_sked_times					= array();
		$inp_verify						= '';
		$inp_delete						= '';
		$nocatalog						= '';
		$youthYesChecked				= '';
		$youthNoChecked					= '';
		$beginnerChecked				= '';
		$fundamentalChecked				= '';
		$intermediateChecked			= '';
		$advancedChecked				= '';
		$nextChecked					= '';
		$twoChecked						= '';
		$threeChecked					= '';
		$fourChecked					= '';
		$theURL							= "$siteURL/cwa-student-registration/";
		$actionDate						= date('Y-m-d h:i');
		$textMsgYes						= '';
		$textMsgNo						= '';
		$levelUp						= array('Beginner'=>'Fundamental',
												'Fundamental'=>'Intermediate',
												'Intermediate'=>'Advanced',
												'Advanced'=>'Advanced');
		$levelDown						= array('Beginner'=>'Beginner',
												'Fundamental'=>'Beginner',
												'Intermediate'=>'Fundamental',
												'Advanced'=>'Intermediate');
		$catalogOptions		= array('MTM'=>'Monday and Thursday mornings', 
									'MTA'=>'Monday and Thursday afternoons',
									'MTE'=>'Monday and Thursday evenings',
									'TFM'=>'Tuesday and Friday mornings',
									'TFA'=>'Tuesday and Friday afternoons',
									'TFE'=>'Tuesday and Friday evenings',
									'SWM'=>'Sunday and Wednesday mornings',
									'SWA'=>'Sunday and Wednesday afternoons',
									'SWE'=>'Sunday and Wednesday evenings',
									'STM'=>'Sunday and Thursday mornings',
									'STA'=>'Sunday and Thursday afternoons',
									'STE'=>'Sunday and Thursday evenings');



	$haveInpCallsign		= FALSE;
	$haveInpSemester		= FALSE;
	$haveStudentData		= FALSE;
	$haveStudentID			= FALSE;
	$allowSignup			= FALSE;

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
			if ($str_key 		== "enstr") {
				$enstr			 = $str_value;
//				$enstr			= filter_var($enstr,FILTER_UNSAFE_RAW);
				$encodedString	= base64_decode($enstr);
				if ($doDebug) {
					echo "Key: $str_key | value: $encodedString<br />";
				}
				$myArray		= explode("&",$encodedString);
				foreach($myArray as $thisValue) {
					$enArray	= explode("=",$thisValue);
					if ($enArray[0] == 'strPass') {
						$enArray[1] = substr($enArray[1],0,1);
					} else {
						${$enArray[0]}	= $enArray[1];
						if ($doDebug) {
							echo "set $enArray[0] to $enArray[1]<br />";
						}
						if ($enArray[0] == 'inp_callsign') {
							$haveInpCallsign	= TRUE;
						}
						if ($enArray[0] == 'inp_semester') {
							$haveInpSemester	= TRUE;
						}
						if ($doDebug) {
							echo "enstr contained $enArray[0] = $enArray[1]<br />";
						}
					}
				}
			}

			if ($str_key 				== "enstrInfo") {
				$enstrInfo			 	= $str_value;
//				$enstrInfo				= filter_var($enstrInfo,FILTER_UNSAFE_RAW);
				$encodedString			= base64_decode($enstrInfo);
				$myArray				= explode("&",$encodedString);
				foreach($myArray as $thisValue) {
					$enArray			= explode("=",$thisValue);
					if ($enArray[0] 	== 'strPass') {
						$enArray[1] 	= substr($enArray[1],0,1);
					} else {
						$$enArray[0]		= $enArray[1];
						if ($doDebug) {
							echo "enstrInfo contained $enArray[0] = $enArray[1]<br />";
						}
						if ($enArray[0] == 'inp_callsign') {
							$haveInpCallsign	= TRUE;
						}
						if ($enArray[0] == 'inp_semester') {
							$haveInpSemester	= TRUE;
						}
						if ($doDebug) {
							echo "enstr contained $enArray[0] = $enArray[1]<br />";
						}
					}
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set strPass to $strPass<br />";
				}
			}
			if ($str_key 		== "demonstration") {
				$demonstration		 = $str_value;
				$demonstration		 = filter_var($demonstration,FILTER_UNSAFE_RAW);
				if ($demonstration == 'Yes') {
					$demoMode		 = TRUE;
				}
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
			if ($str_key 		== "verifyMode") {
				$verifyMode	 = $str_value;
				$verifyMode	 = filter_var($verifyMode,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "needsAssessment") {
				$needsAssessment	 = $str_value;
				$needsAssessment	 = filter_var($needsAssessment,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set needsAssessment to $needsAssessment<br />";
				}
			}
			if ($str_key 		== "allowSignup") {
				$allowSignup	 = $str_value;
				$allowSignup	 = filter_var($allowSignup,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set allowSignup to $allowSignup<br />";
				}
			}
			if ($str_key 		== "waitingList") {
				$waitingList	 = $str_value;
				$waitingList	 = filter_var($waitingList,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set waitingList to $waitingList<br />";
				}
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
				$haveInpSemester	= TRUE;
				if ($doDebug) {
					echo "set inp_semester to $inp_semester<br />";
				}
			}
			if ($str_key 		== "new_semester") {
				$new_semester	 = $str_value;
				$new_semester	 = filter_var($new_semester,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set new_semester to $new_semester<br />";
				}
			}
			if ($str_key 		== "submit") {
				$submit	 = $str_value;
				$submit	 = filter_var($submit,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set submit to $submit<br />";
				}
			}
			if ($str_key == 'inp_email') {
				$inp_email = trim($str_value);
				$inp_email = strtolower(filter_var($inp_email,FILTER_UNSAFE_RAW));
				if ($doDebug) {
					echo "set inp_email to $inp_email<br />";
				}
			}
			if ($str_key == 'inp_phone') {
				$inp_phone = trim($str_value);
				$inp_phone = filter_var($inp_phone,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_phone to $inp_phone<br />";
				}
			}
			if ($str_key == 'inp_ph_code') {
				$inp_ph_code = trim($str_value);
				$inp_ph_code = filter_var($inp_ph_code,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_ph_code to $inp_ph_code<br />";
				}
			}
			if ($str_key == 'inp_city') {
				$inp_city = $str_value;
				$inp_city = filter_var($inp_city,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_city to $inp_city<br />";
				}
			}
			if ($str_key == 'inp_state') {
				$inp_state = $str_value;
				$inp_state = filter_var($inp_state,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_state to $inp_state<br />";
				}
			}
			if ($str_key == 'inp_country') {
				$inp_country = $str_value;
				$inp_country = filter_var($inp_country,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_country to $inp_country<br />";
				}
			}
			if ($str_key == 'inp_countrya') {
				$inp_countrya = $str_value;
				$inp_countrya = filter_var($inp_countrya,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_countrya to $inp_countrya<br />";
				}
			}
			if ($str_key == 'inp_countryb') {
				$inp_countryb	= $str_value;
				$inp_countryb = filter_var($inp_countryb,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_countryb to $inp_countryb<br />";
				}
			}
			if ($str_key == 'inp_age') {
				$inp_age = $str_value;
				$inp_age = filter_var($inp_age,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_age to $inp_age<br />";
				}
			}
			if ($str_key == 'inp_student_parent') {
				$inp_student_parent = $str_value;
				$inp_student_parent = filter_var($inp_student_parent,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_student_parent to $inp_student_parent<br />";
				}
			}
			if ($str_key == 'inp_student_parent_email') {
				$inp_student_parent_email = $str_value;
				$inp_student_parent_email = strtolower(filter_var($inp_student_parent_email,FILTER_UNSAFE_RAW));
				if ($doDebug) {
					echo "set inp_student_parent_email to $inp_student_parent_email<br />";
				}
			}
			if ($str_key == 'inp_zip') {
				$inp_zip = $str_value;
				$inp_zip = filter_var($inp_zip,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_zip to $inp_zip<br />";
				}
			}
			if ($str_key == 'inp_timezone') {
				$inp_timezone = $str_value;
				$inp_timezone = filter_var($inp_timezone,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_timezone to $inp_timezone<br />";
				}
			}
			if ($str_key == 'inp_callsign') {
				$inp_callsign = trim($str_value);
				$inp_callsign = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
				$haveInpCallsign = TRUE;
				if ($doDebug) {
					echo "set inp_callsign to $inp_callsign<br />";
				}
			}
			if ($str_key == 'old_callsign') {
				$old_callsign = $str_value;
				$old_callsign = strtoupper(filter_var($old_callsign,FILTER_UNSAFE_RAW));
				if ($doDebug) {
					echo "set old_callsign to $old_callsign<br />";
				}
			}
			if ($str_key == 'inp_lastname') {
				$inp_lastname = no_magic_quotes($str_value);
//				$inp_lastname = filter_var($inp_lastname,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_lastname to $inp_lastname<br />";
				}
			}
			if ($str_key == 'inp_firstname') {
				$inp_firstname = $str_value;
				$inp_firstname = filter_var($inp_firstname,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_youth') {
				$inp_youth = $str_value;
				$inp_youth = filter_var($inp_youth,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_youth to $inp_youth<br />";
				}
			}
			if ($str_key == 'inp_level') {
				$inp_level = $str_value;
				$inp_level = filter_var($inp_level,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_level to $inp_level<br />";
				}
			}
			if ($str_key == 'inp_sked1') {
				$inp_sked1 = $str_value;
				$inp_sked1 = filter_var($inp_sked1,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_sked1 to $inp_sked1<br />";
				}
			}
			if ($str_key == 'inp_sked2') {
				$inp_sked2 = $str_value;
				$inp_sked2 = filter_var($inp_sked2,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_sked2 to $inp_sked2<br />";
				}
			}
			if ($str_key == 'inp_sked3') {
				$inp_sked3 = $str_value;
				$inp_sked3 = filter_var($inp_sked3,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_sked3 to $inp_sked3<br />";
				}
			}
			if ($str_key == 'inp_verify') {
				$inp_verify = $str_value;
				$inp_verify = filter_var($inp_verify,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_verify to $inp_verify<br />";
				}
			}
			if ($str_key == 'student_timezone') {
				$student_timezone = $str_value;
				$student_timezone = filter_var($student_timezone,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set student_timezone to $student_timezone<br />";
				}
			}
			if ($str_key == 'student_level') {
				$student_level = $str_value;
				$student_level = filter_var($student_level,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set student_level to $student_level<br />";
				}
			}
			if ($str_key == 'cur_semester') {
				$cur_semester = $str_value;
				$cur_semester = filter_var($cur_semester,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set cur_semester to $cur_semester<br />";
				}
			}
			if ($str_key == 'thisOption') {
				$thisOption = $str_value;
				$thisOption = filter_var($thisOption,FILTER_UNSAFE_RAW);
				if ($thisOption == 'assessment') {
					$doAssessment	= TRUE;
				if ($doDebug) {
					echo "set thisOption to $thisOption<br />";
				}
				}
			}
			if ($str_key == 'cur_level') {
				$cur_level = $str_value;
				$cur_level = filter_var($cur_level,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set cur_level to $cur_level<br />";
				}
			}
			if ($str_key == 'inp_doAgain') {
				$inp_doAgain = $str_value;
				$inp_doAgain = filter_var($inp_doAgain,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_doAgain to $inp_doAgain<br />";
				}
			}
			if ($str_key == 'newInput') {
				$newInput = $str_value;
				$newInput = filter_var($newInput,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set newInput to $newInput<br />";
				}
			}
			if ($str_key == 'pass3FirstTime') {
				$pass3FirstTime = $str_value;
				$pass3FirstTime = filter_var($pass3FirstTime,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set pass3FirstTime to $pass3FirstTime<br />";
				}
			}
			if ($str_key == 'inp_first_class_choice') {
				$inp_first_class_choice = $str_value;
				$inp_first_class_choice = filter_var($inp_first_class_choice,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_first_class_choice to $inp_first_class_choice<br />";
				}
			}
			if ($str_key == 'inp_second_class_choice') {
				$inp_second_class_choice = $str_value;
				$inp_second_class_choice = filter_var($inp_second_class_choice,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_second_class_choice to $inp_second_class_choice<br />";
				}
			}
			if ($str_key == 'inp_third_class_choice') {
				$inp_third_class_choice = $str_value;
				$inp_third_class_choice = filter_var($inp_third_class_choice,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_third_class_choice to $inp_third_class_choice<br />";
				}
			}
			if ($str_key == 'inp_first_class_choice_utc') {
				$inp_first_class_choice_utc = $str_value;
				$inp_first_class_choice_utc = filter_var($inp_first_class_choice_utc,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_first_class_choice_utc to $inp_first_class_choice_utc<br />";
				}
			}
			if ($str_key == 'inp_second_class_choice_utc') {
				$inp_second_class_choice_utc = $str_value;
				$inp_second_class_choice_utc = filter_var($inp_second_class_choice_utc,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_second_class_choice_utc to $inp_second_class_choice_utc<br />";
				}
			}
			if ($str_key == 'inp_third_class_choice_utc') {
				$inp_third_class_choice_utc = $str_value;
				$inp_third_class_choice_utc = filter_var($inp_third_class_choice_utc,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_third_class_choice_utc to $inp_third_class_choice_utc<br />";
				}
			}
			if ($str_key == 'errorString') {
				$errorString = $str_value;
//				$errorString = filter_var($errorString,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set errorString to $errorString<br />";
				}
			}
			if ($str_key == 'inp_number') {
				$inp_number = $str_value;
				$inp_number = filter_var($inp_number,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_number to $inp_number<br />";
				}
			}
			if ($str_key == 'nextLevel') {
				$nextLevel = $str_value;
				$nextLevel = filter_var($nextLevel,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set nextLevel to $nextLevel<br />";
				}
			}
			if ($str_key == 'audioFileName') {
				$audioFileName = $str_value;
				$audioFileName = filter_var($audioFileName,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set audioFileName to $audioFileName<br />";
				}
			}
			if ($str_key == 'audioFileNumber') {
				$audioFileNumber = $str_value;
				$audioFileNumber = filter_var($audioFileNumber,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set audioFileNumber to $audioFileNumber<br />";
				}
			}
			if ($str_key == 'firsttime') {
				$firsttime = $str_value;
				$firsttime = filter_var($firsttime,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set firsttime to $firsttime<br />";
				}
			}
			if ($str_key == 'nocatalog') {
				$nocatalog = $str_value;
				$nocatalog = filter_var($nocatalog,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set nocatalog to $nocatalog<br />";
				}
			}
			if ($str_key == 'inp_delete') {
				$inp_delete = $str_value;
				$inp_delete = filter_var($inp_delete,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_delete to $inp_delete<br />";
				}
			}
			if ($str_key == 'timezone') {
				$timezone = $str_value;
				$timezone = filter_var($timezone,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set timezone to $timezone<br />";
				}
			}
			if ($str_key == 'browser_timezone_id') {
				$browser_timezone_id = $str_value;
				$browser_timezone_id = filter_var($browser_timezone_id,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set browser_timezone_id to $browser_timezone_id<br />";
				}
			}
			if ($str_key == 'json_updateParams') {
				$json_updateParams = $str_value;
				$json_updateParams = stripslashes($json_updateParams);
				if ($doDebug) {
					echo "set json_updateParams to $json_updateParams<br />";
				}
			}
			if ($str_key == 'inp_whatsapp') {
				$inp_whatsapp = $str_value;
				$inp_whatsapp = filter_var($inp_whatsapp,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_whatsapp to $inp_whatsapp<br />";
				}
			}
			if ($str_key == 'inp_telegram') {
				$inp_telegram = $str_value;
				$inp_telegram = filter_var($inp_telegram,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_telegram to $inp_telegram<br />";
				}
			}
			if ($str_key == 'inp_signal') {
				$inp_signal = $str_value;
				$inp_signal = filter_var($inp_signal,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_signal to $inp_signal<br />";
				}
			}
			if ($str_key == 'inp_messenger') {
				$inp_messenger = $str_value;
				$inp_messenger = filter_var($inp_messenger,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_messenger to $inp_messenger<br />";
				}
			}
			if ($str_key == 'inp_timezone_id') {
				$inp_timezone_id = $str_value;
				$inp_timezone_id = filter_var($inp_timezone_id,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_timezone_id to $inp_timezone_id<br />";
				}
			}
			if ($str_key == 'inp_timezone_offset') {
				$inp_timezone_offset = $str_value;
				$inp_timezone_offset = filter_var($inp_timezone_offset,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_timezone_offset to $inp_timezone_offset<br />";
				}
			}
			if ($str_key == 'inp_days1') {
				$inp_days1 = $str_value;
				$inp_days1 = filter_var($inp_days1,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_days1 to $inp_days1<br />";
				}
			}
			if ($str_key == 'inp_days2') {
				$inp_days2 = $str_value;
				$inp_days2 = filter_var($inp_days2,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_days2 to $inp_days2<br />";
				}
			}
			if ($str_key == 'inp_days3') {
				$inp_days3 = $str_value;
				$inp_days3 = filter_var($inp_days3,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_days3 to $inp_days3<br />";
				}
			}
			if ($str_key == 'inp_times1') {
				$inp_times1 = $str_value;
				$inp_times1 = filter_var($inp_times1,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set strPass to $strPass<br />";
				}
			}
			if ($str_key == 'inp_times2') {
				$inp_times2 = $str_value;
				$inp_times2 = filter_var($inp_times2,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_times2 to $inp_times2<br />";
				}
			}
			if ($str_key == 'inp_times3') {
				$inp_times3 = $str_value;
				$inp_times3 = filter_var($inp_times3,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_times3 to $inp_times3<br />";
				}
			}
			if ($str_key == 'student_id') {
				$student_id = $str_value;
				$student_id = filter_var($student_id,FILTER_UNSAFE_RAW);
				if ($student_id != '') {
					$haveStudentID	= TRUE;
				}
				if ($doDebug) {
					echo "set student_id to $student_id<br />";
				}
			}
			if ($str_key == 'inp_bypass') {
				$inp_bypass = $str_value;
				$inp_bypass = filter_var($inp_bypass,FILTER_UNSAFE_RAW);
					if ($doDebug) {
					echo "set inp_bypass to $inp_bypass<br />";
				}
		}
			if ($str_key == 'badTimezone') {
				$badTimezone = $str_value;
				$badTimezone = filter_var($badTimezone,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set badTimezone to $badTimezone<br />";
				}
			}
			if ($str_key 		== "inp_student_catalog_options") {
				$inp_student_catalog_options	 = $str_value;
				$inp_student_catalog_options	 = filter_var($inp_student_catalog_options,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_student_catalog_options to $inp_student_catalog_options<br />";
				}
			}
			if ($str_key 		== "inp_student_flexible") {
				$inp_student_flexible	 = $str_value;
				$inp_student_flexible	 = filter_var($inp_student_flexible,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_student_flexible to $inp_student_flexible<br />";
				}
			}
			if ($str_key 		== "result_option") {
				$result_option	 = $str_value;
				$result_option	 = filter_var($result_option,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set result_option to $result_option<br />";
				}
			}
			if ($str_key 		== "inp_sked_times") {
				$inp_sked_times	 = $str_value;
//				$inp_sked_times	 = filter_var($inp_sked_times,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_sked_times:<br /><pre>";
					var_dump($inp_sked_times);
					echo "</pre><br />";
				}
			}
			if ($str_key 		== "doUpdate") {
				$doUpdate	 = $str_value;
//				$doUpdate	 = filter_var($doUpdate,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set doUpdate to $doUpdate<br />";
				}
			}
			if ($str_key 		== "continuePass8A") {
				$continuePass8A	 = $str_value;
				$continuePass8A	 = filter_var($continuePass8A,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set continuePass8A to $continuePass8A<br />";
				}
			}
			if ($str_key 		== "inp_flex") {
				$inp_flex	 = $str_value;
				$inp_flex	 = filter_var($inp_flex,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_flex to $inp_flex<br />";
				}
			}
			if ($str_key 		== "token") {
				$token	 = $str_value;
				$token	 = filter_var($token,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set token to $token<br />";
				}
			}
			if ($str_key 		== "fakeIt") {
				$fakeIt	 = $str_value;
				$fakeIt	 = filter_var($fakeIt,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set fakeIt to $fakeIt<br />";
				}
			}
			if ($str_key 		== "inp_available") {
				$inp_available	 = $str_value;
				$inp_available	 = filter_var($inp_available,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_available to $inp_available<br />";
				}
			}
			if ($str_key 		== "inp_class_language") {
				$inp_class_language	 = $str_value;
				$inp_class_language	 = filter_var($inp_class_language,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set inp_class_language to $inp_class_language<br />";
				}
			}
			if ($str_key 		== "insertDataJson") {
				$insertDataJson	 = $str_value;
				$insertDataJson	 = filter_var($insertDataJson,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "set insertDataJson to $insertDataJson<br />";
				}
			}
		}
	}
	
	if ($thisOption == 'assessment') {
		$doAssessment		= TRUE;
	}

	if ($testMode) {
		$inp_mode	= "TESTMODE";
	}

//	if ($testMode) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

	if ($verifyMode) {
		$daysToGo					= 45;
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
		$content					.= "<p>Operating in <b>Test Mode</b></p>";
		$studentTableName			= 'wpw1_cwa_student2';
		$oldAssessmentTableName		= "wpw1_cwa_audio_assessment2";
		$newAssessmentTableName		= "wpw1_cwa_new_assessment_data";
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$studentDeletedTableName	= 'wpw1_cwa_deleted_student2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$catalogMode				= 'TestMode';
		$operatingMode				= 'Testmode';
	} elseif ($demoMode) {
		$content					.= "<p>Operating in <b>Demonstration Mode</b>.</p>";
		$studentTableName			= 'wpw1_cwa_student3';
		$oldAssessmentTableName		= "wpw1_cwa_audio_assessment2";
		$newAssessmentTableName		= "wpw1_cwa_new_assessment_data";
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$studentDeletedTableName	= 'wpw1_cwa_deleted_student2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$catalogMode				= 'TestMode';
		$operatingMode				= 'Testmode';
	} else {
		$studentTableName			= 'wpw1_cwa_student';
		$oldAssessmentTableName		= "wpw1_cwa_audio_assessment";
		$newAssessmentTableName		= "wpw1_cwa_new_assessment_data";
		$advisorTableName			= 'wpw1_cwa_advisor';
		$studentDeletedTableName	= 'wpw1_cwa_deleted_student';
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$catalogMode				= 'Production';
		$operatingMode				= 'Production';
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

	$student_dal = new CWA_Student_DAL();
	$user_dal = new CWA_User_Master_DAL();
	$advisor_dal = new CWA_Advisor_DAL();



	// get the student info
	$haveStudentData			= FALSE;
	$haveMasterData				= FALSE;
	$badTimezoneID				= FALSE;


	
	if ($doDebug) {
		echo "<br />Get the Student Info Truth<br />
				student_id: $student_id<br />";
		if ($haveStudentID) {
			echo "haveStudentID is TRUE<br />";
		} else {
			echo "haveStudentID is FALSE<br />";
		}
		echo "inp_callsign: $inp_callsign<br />";
		if ($haveInpCallsign) {
			echo "haveInpCallsign is TRUE<br />";
		} else {
			echo "haveInpCallsign is FALSE<br />";
		}
		echo "inp_semester: $inp_semester<br />";
		if ($haveInpSemester) {
			echo "haveInpSemester is TRUE<br />";
		} else {
			echo "haveInpSemester is FALSE<br />";
		}
		if ($haveMasterData) {
			echo "haveMasterData is TRUE<br />";
		} else {
			echo "haveMasterData is FALSE<br />"; 
		}
		if ($allowSignup) {
			echo "allowSignup is TRUE<br />";
		} else {
			echo "allowSignup is FALSE<br /><br />";
		}
	}
	
	// if haveStudentID that takes precedence
	if ($haveStudentID) {
		// get the student information and then get the user_master data using the student_call_sign
		$student_data = $student_dal->get_student_by_id($student_id,$operatingMode);
		if ($student_data !== NULL) {
			foreach($student_data as $key => $value) {
				$$key = $value;
			}
			$haveStudentData = TRUE;
			if ($doDebug) {
				echo "setting haveStudentData to TRUE<br />";
			}
			// now get the user_master
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_call_sign', 
						'value'   => $student_call_sign, 
						'compare' => '=' 
					]
				]
			];
			$user_data = $user_dal->get_user_master($criteria,'user_call_sign','ASC',$operatingMode);
			if ($user_data !== FALSE) {
				foreach($user_data as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					$haveMasterData = TRUE;
					if ($doDebug) {
						echo "setting haveMasterData to TRUE<br />";
					}
				}
			}
		}
	} else {
		if ($haveInpCallsign) {
			// get the user_master data based on $inp_callsign
			if ($doDebug) {
				echo "getting user_master based on $inp_callsign<br />";
			}
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_call_sign', 
						'value'   => $inp_callsign, 
						'compare' => '=' 
					]
				]
			];
			$user_data = $user_dal->get_user_master($criteria,'user_call_sign','ASC',$operatingMode);
			if ($user_data !== FALSE) {
				foreach($user_data as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					$haveMasterData = TRUE;
					if ($doDebug) {
						echo "setting haveMasterData to TRUE<br />";
					}
				}
			}
		} else {
			// get the user master based on the userName
			if ($doDebug) {
				echo "getting the user-master based on userName $userName<br />";
			}
			$myStr = strtoupper($userName);
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_call_sign', 
						'value'   => $myStr, 
						'compare' => '=' 
					]
				]
			];
			$user_data = $user_dal->get_user_master($criteria,'user_call_sign','ASC',$operatingMode);
			if ($user_data !== FALSE) {
				foreach($user_data as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					$haveMasterData = TRUE;
					if ($doDebug) {
						echo "setting haveMasterData to TRUE<br />";
					}
				}
			}
			
		}
		if ($haveInpCallsign && $haveInpSemester && !$haveStudentID) {
			if ($doDebug) {
				echo "haveInpCallsign and haveInpSemester<br />";
			}
			
			// now get the student information
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_call_sign', 'value' => $inp_callsign, 'compare' => '='],
					['field' => 'student_semester', 'value' => $inp_semester, 'compare' => '=']
				]
			];
			$orderby = 'student_call_sign';
			$student_data = $student_dal->get_student($criteria,$orderby,'ASC',$operatingMode);
			if ($student_data !== FALSE) {
				foreach($student_data as $key => $vaue) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					$haveStudentData = TRUE;
					if ($doDebug) {
						echo "setting haveStudentData to TRUE<br />";
					}
				}
			}
		} elseif ($haveInpCallsign && $allowSignup && !$haveStudentID) {
			
			// now get the student information
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					// field1 = $value1
					['field' => 'student_call_sign', 'value' => $inp_callsign, 'compare' => '='],
					
					// (field2 = $value2 OR field2 = $value3)
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_semester', 'value' => $currentSemester, 'compare' => '='],
							['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '=']
						]
					]
				]
			];
			$orderby = 'student_call_sign';
			$student_data = $student_dal->get_student($criteria,$orderby,'ASC',$operatingMode);
			if ($student_data !== FALSE) {
				foreach($student_data as $key => $vaue) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					$haveStudentData = TRUE;
					if ($doDebug) {
						echo "setting haveStudentData to TRUE<br />";
					}
				}
			}
		} elseif ($haveInpCallsign && !$allowSignup && !$haveStudentID) {
			if ($doDebug) {
				echo "haveInpCallsign but not allowSignup<br />";
			}
			// now get the student information
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					// field1 = $value1
					['field' => 'student_call_sign', 'value' => $inp_callsign, 'compare' => '='],
					
					// (field2 = $value2 OR field2 = $value3)
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '=']
						]
					]
				]
			];
			$orderby = 'student_call_sign';
			$student_data = $student_dal->get_student($criteria,$orderby,'ASC',$operatingMode);
			if ($student_data !== FALSE) {
				$myInt = count($student_data);
				if ($myInt > 0) {
					if ($doDebug) {
						echo "have $myInt student_data records<br />";
					}
					foreach($student_data as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
							if ($doDebug) {
								echo "set $thisField to $thisValue<br />";
							}
						}
						$haveStudentData = TRUE;
						if ($doDebug) {
							echo "setting haveStudentData to TRUE<br />";
						}
					}
				} else {
					if ($doDebug) {
						echo "No data found<br />";
					}
				}  
			}
		} elseif (!$haveInpCallsign && !$haveInpSemester && !$allowSignup && !$haveStudentID) {
			if ($doDebug) {
				echo "none of haveInpCallsign, haveInpSemester, allowSignup, haveStudentID<br />";
			}
			$myStr	= strtoupper($userName);
			// get the student information
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					// field1 = $value1
					['field' => 'student_call_sign', 'value' => $myStr, 'compare' => '='],
					
					// (field2 = $value2 OR field2 = $value3)
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '=']
						]
					]
				]
			];
			$orderby = 'student_call_sign';
			$student_data = $student_dal->get_student($criteria,$orderby,'ASC',$operatingMode);
			if ($student_data !== FALSE) {
				if (! empty($student_data)) {
					foreach($student_data as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						$haveStudentData = TRUE;
						if ($doDebug) {
							echo "setting haveStudentData to TRUE<br />";
						}
					}
				} else {
					if ($doDebug) {
						echo "get_student returned an empty dataset<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "get_student returned FALSE<br />";
				}
			}
		} elseif (!$haveInpCallsign && !$haveStudentID && $allowSignup) {
			$myStr	= strtoupper($userName);
			// get the student information
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					// field1 = $value1
					['field' => 'student_call_sign', 'value' => $myStr, 'compare' => '='],
					
					// (field2 = $value2 OR field2 = $value3)
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '='],
							['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '=']
						]
					]
				]
			];
			$orderby = 'student_call_sign';
			$student_data = $student_dal->get_student($criteria,$orderby,'ASC',$operatingMode);
			if ($student_data !== FALSE) {
				foreach($student_data as $key => $vaue) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					$haveStudentData = TRUE;
					if ($doDebug) {
						echo "setting haveStudentData to TRUE<br />";
					}
				}
			}
		}
	}
	if ($haveStudentData) {
		if ($student_time_zone == '') {
			if ($doDebug) {
				echo "$student_call_sign has empty time_zone. Set badTimezoneID to TRUE<br />
					  zipcode: $user_zip_code<br />
					  country code: $user_country_code<br />";
			}
			$badTimezoneID		= TRUE;
		}
		if ($doDebug) {
			echo "haveStudentData is set to TRUE<br />
					student_semester: $student_semester<br /><br />";
		}
	}
	if ($haveMasterData) {
		if ($doDebug) {
			echo "haveMasterData is set to TRUE<br />
			master callsign: $user_call_sign<br />";
		}
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Pass 1: Function starting.<br />";
		}
		$userName		= strtoupper($userName);
		$content 		.= "<h3>$jobname</h3>";
		
		if (!$haveStudentData && !$haveMasterData) {
			if ($doDebug) {
				echo "somehow no student data and no master data<br />";
			}
			$content	.= "<b>FATAL PROGRAM ERROR</b> The sysadmin has been notified";
			sendErrorEmail("$jobname Pass1 username $userName. No student data and no master data. How is that possible?");		
		} else {
			$showSignup		= FALSE;
			$showAll		= FALSE;
			if ($maintenanceMode) {
				$content	.= "<p><b>The Student Sign-up process is currently undergoing 
								maintenance. That should be completed within the next hour. Please come back at that 
								time to sign up.</b></p>";
			} else {
				$doProceed	= TRUE;
				if ($userRole == 'student') {
					// find out if a student record exists
					
					/*	see if a current or future semester record exists
					
						CurrentSemester		Promotable			FutureSemester	 set showSignup		set showAll
						FALSE				FALSE				FALSE				TRUE				FALSE
						FALSE				FALSE				TRUE				FALSE				TRUE
						FALSE				TRUE				FALSE				Not possible
						FALSE				TRUE				TRUE				Not possible
						TRUE				FALSE				FALSE				FALSE				TRUE
						TRUE				FALSE				TRUE				Not possible
						TRUE				TRUE				FALSE				TRUE				FALSE
						TRUE				TRUE				TRUE				FALSE				TRUE
					
					*/			
	
					$gotCurrentSemester				= FALSE;
					$gotPromotable					= FALSE;
					$gotFutureSemester				= FALSE;
	
					if ($haveStudentData) {
						if ($doDebug) {
							echo "haveStudentData is TRUE<br />";
						}
						$thisSemester				= $student_semester;
						$thisPromotable				= $student_promotable;
						$thisStudentStatus			= $student_status;
						
						if ($doDebug) {
							echo "<br />thisSemester: $thisSemester<br />
								  thisPromotable: $thisPromotable<br />
								  thisStudentStatus: $thisStudentStatus<br />";
						}
								
						if ($thisSemester == $currentSemester) {
							$gotCurrentSemester 	= TRUE;
							if ($thisPromotable != '') {
								$gotPromotable		= TRUE;
							}
							if ($thisStudentStatus == 'C' || $thisStudentStatus == 'R' || $thisStudentStatus == 'V' || $thisStudentStatus == 'N') {
								$gotCurrentSemester	= FALSE;
								$allowSignup		= TRUE;
							}
						} else {
							$gotFutureSemester		= TRUE;
						}
					} else {
						if (!$haveMasterData) {
						}
					}
					if ($doDebug) {
						echo "<br />Truth Settings<br />";
						if ($gotCurrentSemester) {
							echo "gotCurrentSemester is TRUE<br />";
						} else {
							echo "gotCurrentSemester is FALSE<br />";
						}
						if ($gotPromotable) {
							echo "gotPromotable is TRUE<br />";
						} else {
							echo "gotPromotable is FALSE<br />";
						}
						if ($gotFutureSemester) {
							echo "gotFutureSemester is TRUE<br />";
						} else {
							echo "gotFutureSemester is FALSE<br />";
						}
					}
					if ($gotCurrentSemester === FALSE && $gotPromotable === FALSE && $gotFutureSemester === FALSE) {
						$showSignup			= TRUE;
						$showAll			= FALSE;
						$allowSignup		= TRUE;
	
					} elseif ($gotCurrentSemester === FALSE && $gotPromotable === FALSE && $gotFutureSemester === TRUE) {
						$showSignup			= FALSE;
						$showAll			= TRUE;
						
					} elseif ($gotCurrentSemester === FALSE && $gotPromotable === TRUE && $gotFutureSemester === FALSE) {
						$showSignup			= FALSE;
						$showAll			= FALSE;
						sendErrorEmail("$jobname Pass1 Have Promotable but no current or future semester userName: $userName");
						
					} elseif ($gotCurrentSemester === FALSE && $gotPromotable === TRUE && $gotFutureSemester === TRUE) {
						$showSignup			= FALSE;
						$showAll			= TRUE;
						sendErrorEmail("$jobname Pass1 Have Promotable but no current semester userName: $userName");
						
					} elseif ($gotCurrentSemester === TRUE && $gotPromotable === FALSE && $gotFutureSemester === FALSE) {
						$showSignup			= FALSE;
						$showAll			= TRUE;
						
					} elseif ($gotCurrentSemester === TRUE && $gotPromotable === FALSE && $gotFutureSemester === TRUE) {
						$showSignup			= FALSE;
						$showAll			= TRUE;
						sendErrorEmail("$jobname Pass1 Have current semester, no promotable, but have future semester as well userName: $userName");
						
					} elseif ($gotCurrentSemester === TRUE && $gotPromotable === TRUE && $gotFutureSemester === FALSE) {
						$showSignup			= TRUE;
						$showAll			= FALSE;
						$allowSignup		= TRUE;
						
					} elseif ($gotCurrentSemester === TRUE && $gotPromotable === TRUE && $gotFutureSemester === TRUE) {
						$showSignup			= FALSE;
						$showAll			= TRUE;
						
					} else {
						$showSignup			= TRUE;
						$showAll			= FALSE;
						
					}
				
				} elseif ($userRole == 'administrator') {
					$showAll	= TRUE;
					$showSignup	= TRUE;
				}
				if ($doDebug) {
					if ($showSignup) {
						echo "showSignup is TRUE<br />";
					} else {
						echo "showSignup is FALSE<br />";
					}
					if ($showAll) {
						echo "showAll is TRUE<br />";
					} else {
						echo "showAll is FALSE<br />";
					}
				}
				
				$goOn						= TRUE;
				if ($user_timezone_id == '??') {
					$timezoneMsg	= "<p><b>IMPORTANT!</b> The timezone information in your master record needs to 
										be updated. Please go to 
										<a href='$siteURL/cwa-display-and-update-user-master-information/'>
										Display and Update User Master Information</a> and click on 
										'Update This Information'. Then verify that your 
										address information, particularly your ZipCode if you live in the US is correct. If not, 
										please correct it. Then click on 'Submit Updates'. The 
										list of possible timezones will be displayed. Please select 
										the appropriate information. Then start the 'Student Signup' 
										again.<br ><br />";
					$goOn			= FALSE;
					
				} else {
					$timezoneMsg 	= "<p><b>NOTE: </b>If any of the above information needs to 
										be updated, please please first go to 
										<a href='$siteURL/cwa-display-and-update-user-master-information/'>
										Display and Update User Master Information</a> and make any 
										needed updates. Pay particular attention to your address information 
										as that is what the system uses to calculate your local time. When 
										the updates are done, start this program again.</p>
										<br /><br />";
				}
				$content		.= "<p>Welcome to the CW Academy where our mission is to increase the 
									number of competent CW operators on the amateur radio bands and our 
									goal is to guide you to becoming a better CW operator as you 
									increase your CW skills, speed, and activity!</p>
									<h4>Advisor Master Data</h4>
									<table style='width:900px;'>
									<tr><td><b>Callsign<br />$user_call_sign</b></td>
										<td><b>Name</b><br />$user_last_name, $user_first_name</td>
										<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
										<td><b>Email</b><br />$user_email</td></tr>
									<tr><td><b>City</b><br />$user_city</td>
										<td><b>State</b><br />$user_state</td>
										<td><b>Zip Code</b><br />$user_zip_code</td>
										<td><b>Country</b><br />$user_country</td></tr>
									<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
										<td><b>Telegram</b><br />$user_telegram</td>
										<td><b>Signal</b><br />$user_signal</td>
										<td><b>Messenger</b><br />$user_messenger</td></tr>
									<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
										<td><b>Date Created</b><br />$user_date_created</td>
										<td><b>Date Updated</b><br />$user_date_created</td>
										<td></td></tr></table>
									$timezoneMsg";
				if ($goOn) {										
					$content		.= "<table style='width:800px;border:4px solid green;'>";
					if ($showSignup) {
						$content		.= "<tr><td style='vertical-align:top;'><b>Sign-up</b><br />You should now 
													sign up as a student for an upcoming semester. There are 4 steps 
													to sign up for a class.<br />
													<form method='post' action='$theURL' 
													name='option1_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='100'>
													<input type='hidden' name='allowSignup' value='$allowSignup'>
													<input type='submit' class='formInputButton' name='option1submit' value='Sign-Up'>
													</form></td>";
					} else {
						if ($student_response == 'R') {
							$content		.= "<p>You have previouly signed up for $student_semester semester and subsequently deleted 
												that record. To reinstate that registration, click on 'Update Registration' below. Otherwise, 
												please contact the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>
												CW Academy Class Resolution</a></p>";
						}
					
						$content			.= "<td style='vertical-align:top;'><b>Modify Signup Information</b><br />You have already signed up and wish 
													to update or modify your sign up information<br />
													<form method='post' action='$theURL' 
													name='option3_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='90'>
													<input type='submit' class='formInputButton' name='option3submit' value='Update Registration'>
													</form></td>";
						$content			.= "<td style='vertical-align:top;'><b>Check SignupStatus</b><br />You have already signed up and want to check 
													the status of your registration<br />
													<form method='post' action='$siteURL/cwa-check-student-status/' 
													name='option2_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='1'>
													<input type='submit' class='formInputButton' name='option2submit' value='Check Status'>
													</form></td>";
					}
					$content				.= "<td style='vertical-align:top;'><b>Practice Assessment</b><br />If you want to take a practice Morse Code 
													Proficiency Assessment, click the 'Practice Assessment' button below. You are allowed to take 
													a practice Morse Code Proficiency Assessment twice in a 45-day period.<br />
													<form method='post' action='$siteURL/cwa-practice-assessment/' 
													name='option4_form' ENCTYPE='multipart/form-data'>
													<input type='submit' class='formInputButton' name='option4submit' value='Practice Assessment'>
													</form></td></tr>
											</table>";
				}
			}
		}


///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			if ($haveStudentData) {
				echo "<br />haveStudentData is TRUE<br />";
			} else {
				echo "<br />haveStudentData is FALSE<br />";
			}
			if ($haveMasterData) {
				echo "haveMasterData is TRUE<br />";
			} else {
				echo "haveMasterData is FALSE<br />";
			}
			if ($haveInpSemester) {
				echo "haveInpSemester is TRUE<br />";
			} else {
				echo "haveInpSemester is FALSE<br />";
			}
			if ($allowSignup) {
				echo "allowSignup is TRUE<br />";
			} else {
				echo "allowSignup is FALSE<br />";
			}
			echo "<br />Arrived at pass2 with inp_callsign: $inp_callsign<br />
				  inp_level: $inp_level<br />
				  inp_semester: $inp_semester<br />
			      allowSignup: $allowSignup<br />
			      inp_verify: $inp_verify<br />";
		}
		$userName				= $inp_callsign;
		$content				.= "<h3>$jobname</h3>";
		$doProceed				= TRUE;
		$myStr					= str_replace(" ","",$inp_callsign);
		
		$browser_timezone_id	= $timezone;
		if ($doDebug) {
			echo "The browser returned a timezone_id of $browser_timezone_id<br >";
		}
		
		$noRecord				= TRUE;
		$foundARecord			= FALSE;
		$doProceed				= TRUE;
		
/*

	allowSignup	haveStudentData	haveInpSemester				NR		FAR
	Y			Y				Y				update 		FALSE	TRUE
	Y			Y				N				update		FALSE	TRUE
	Y			N				Y				New signup	TRUE	FALSE
	Y			N				N				New Signup	TRUE	FALSE
	N			Y				Y				Update		FALSE	TRUE
	N			Y				N				Update		FALSE	TRUE
	N			N				N				error		
*/		
		
		if ($allowSignup && $haveStudentData && $haveInpSemester) {
			$noRecord		= FALSE;
			$foundARecord	= TRUE;
		} elseif ($allowSignup && $haveStudentData && !$haveInpSemester) {
			$noRecord		= FALSE;
			$foundARecord	= TRUE;
		} elseif ($allowSignup && !$haveStudentData && $haveInpSemester) {
			$noRecord		= TRUE;
			$foundARecord	= FALSE;
		} elseif ($allowSignup && !$haveStudentData && !$haveInpSemester) {
			$noRecord		= TRUE;
			$foundARecord	= FALSE;
		} elseif (!$allowSignup && $haveStudentData && $haveInpSemester) {
			$noRecord		= FALSE;
			$foundARecord	= TRUE;
		} elseif (!$allowSignup && $haveStudentData && !$haveInpSemester) {
			$noRecord		= FALSE;
			$foundARecord	= TRUE;
		} elseif (!$allowSignup && !$haveStudentData && $haveInpSemester) {
			$noRecord		= TRUE;
			$foundARecord	= FALSE;
		} elseif (!$allowSignup && !$haveStudentData && !$haveInpSemester) {
			$noRecord		= TRUE;
			$foundARecord	= FALSE;
			$doProceed		= FALSE;
		}
		
		// Look up the student to determine if a record already exists
		// if so, do the update process. Otherwise, do the new student (maybe)

		if ($haveStudentData) {
					
			if ($doDebug) {
				echo "country code: $user_country_code; country: $user_country<br />";
			}
			$noRecord									= FALSE;
			$foundARecord								= TRUE;

			////// a record exists. Validate the student
			$newInput					= 'N';
			$noRecord					= FALSE;
			$waiting					= "";
			if ($student_waiting_list == 'Y') {
				$waiting			= "<b>(Wait List)</b>";
			}
			
			if ($student_second_class_choice == '') {
				$student_second_class_choice 		= 'None';
				$student_second_class_choice_utc	= 'None';
			}
			if ($student_third_class_choice == '') {
				$student_third_class_choice 		= 'None';
				$student_third_class_choice_utc		= 'None';
			}

			if ($student_youth == 'Yes' || $student_youth == 'Y') {
				$youthYesChecked	= "checked='checked'";
			} elseif ($student_youth == 'No' || $student_youth == 'N') {
				$youthNoChecked		= "checked='checked'";
			}

			if ($student_level == 'Beginner') {
				$beginnerChecked	= "checked='checked' ";
			} elseif ($student_level == 'Fundamental') {
				$fundamentalChecked		= "checked='checked' ";
			} elseif ($student_level == 'Intermediate') {
				$intermediateChecked = "checked='checked '";
			} elseif ($student_level == 'Advanced') {
				$advancedChecked	= "checked='checked' ";
			}
			$levelList				= "<input type='radio' class='formInputButton' name='inp_level' value='Beginner' $beginnerChecked>Beginner<br />
										<input type='radio' class='formInputButton' name='inp_level' value='Fundamental' $fundamentalChecked>Fundamental<br />
										<input type='radio' class='formInputButton' name='inp_level' value='Intermediate' $intermediateChecked>Intermediate<br />
										<input type='radio' class='formInputButton' name='inp_level' value='Advanced' $advancedChecked>Advanced";



		
			$daysToGo			= days_to_semester($student_semester);
			if ($doDebug) {
				echo "daysToGo calculated to be $daysToGo to $student_semester<br />";
			}
		
			$currentSemester_checked 	= '';
			if ($student_semester == $currentSemester) {
				$currentSemester_checked 	= "checked";
			} 
			$nextSemester_checked 	= '';
			if ($student_semester == $nextSemester) {
				$nextSemester_checked 	= "checked";
			} 
			$semesterTwo_checked = '';
			if ($student_semester == $semesterTwo) {
				$semesterTwo_checked 	= "checked";
			}
			$semesterThree_checked = '';
			if ($student_semester == $semesterThree) {
				$semesterThree_checked 	= "checked";
			}

			$nextPass				= "8";
			$extraHidden			= "";
/* What changes are possible

	If there are less than 21 days until the start of the semester and 
	student has been assigned to an advisor, then only limited changes 
	are allowed
	
	If the student semetser equal to current semester and the student is 
	assigned to an advisor, only limited changes are allowed
	
	Otherwise, student can change anything
*/		

			$canChangeAnything			= TRUE;
			if ($doDebug) {
				echo "verifying what can be changed<br />daysToGo: $daysToGo<br />assigned advisor: $student_assigned_advisor<br />";
			}
			if ($daysToGo < 21) {								// student asking for upcoming semester
//				if ($student_assigned_advisor != '') {			// student is assigned to an advisor
					$canChangeAnything	= FALSE;				// limited changes only
					if ($doDebug) {
						echo "daysToGo is less than 21 <br />canChangeAnything set to FALSE<br />";
					}
//				}
			} else {			// if assigned advisor, something is wrong
				if ($student_assigned_advisor != '') {
					if ($student_assigned_advisor != 'AC6AC') {
						sendErrorEmail("$jobname Student $student_call_sign more than 21 days to the semester and student has $student_assigned_advisor assigned as an advisor. Program being run by $userName");
						$canChangeAnyting	= FALSE;
					} else {
						$canChangeAnything 	= FALSE;
					}
				}
			}

			if ($student_semester == $currentSemester) {		// semester is underway
				if ($student_assigned_advisor != '') {			//student is assigned
					$canChangeAnything	= FALSE;				// limited changes only
					if ($doDebug) {
						echo "semester is underway and student has an assigned advisor<br />canChangeAnything set to FALSE<br />";
					}
				}
			}
			if ($doDebug) {
				if ($canChangeAnything) {
					echo "Can change anything<br />";
				} else {
					echo "Limited changes<br />";
				}
			}
			$semesterList		= "";
			$deleteMsg			= "";
			$content			.= "<p>You have a student registration record which was entered 
									on $student_request_date.</p>";
			if (!$canChangeAnything) {
				if ($doDebug) {
					echo "putting out limited changes option<br />";
				}
				if ($student_waiting_list == 'Y') {
					$content		.= "<p>Because you are signed up for the $student_semester semester and students 
										have already been assigned to classes, you are on the waiting list.  
										You may only make changes to your personal information.";
				} else {
					$content		.= "<p>Because you are signed up for the $student_semester semester and you have been 
										assigned to a class, you may only make  
										changes to your personal information.";
				}
				$content			.= "To do so, go to 
										<a href='$siteURL/cwa-display-and-update-user-master-information/'>
										Display and Update User Master Information</a> and make any 
										needed updates. Pay particular attention to your address information 
										as that is what the system uses to calculate your local time.<p>
										<p>If you need to drop out of the 
										class or move to a different semester, please contact your advisor, or a 
										systems administrator at <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>
										CWA Contact Information</a></p>";
								   
			} else {
				if ($currentSemester == 'Not in Session' && $daysToGo > 10) {
					if ($doDebug) {
						echo "current semester is Not in Session and daysToGo is greater than 10. Displaying $nextSemester and $semesterTwo<br />";
					}
					$semesterList	= "<input type='radio' class='formInputButton' name='new_semester' value='$nextSemester' $nextSemester_checked>$nextSemester<br />
									   <input type='radio' class='formInputButton' name='new_semester' value='$semesterTwo' $semesterTwo_checked>$semesterTwo";
				} elseif ($currentSemester == 'Not in Session' && $daysToGo <= 10) {
					if ($doDebug) {
						echo "curent semester is Not in Session and daysToGo is less-equal than 10. Displaying $nextSemester, $semesterTwo, and $semesterThree<br />";
					}
					$semesterList	= "<input type='radio' class='formInputButton' name='new_semester' value='$nextSemester' $nextSemester_checked>$nextSemester<br />
									   <input type='radio' class='formInputButton' name='new_semester' value='$semesterTwo' $semesterTwo_checked>$semesterTwo<br />
									   <input type='radio' class='formInputButton' name='new_semester' value='$semesterThree' $semesterThree_checked>$semesterThree";
				} elseif ($currentSemester != 'Not in Session' && $validReplacementPeriod) {
					if ($doDebug) {
						echo "current semester is in session and validReplacementPeriod. Displaying $currentSemester, $nextSemester, and $semesterTwo<br />";
					}
					$semesterList	= "<input type='radio' class='formInputButton' name='new_semester' value='$currentSemester' $currentSemester_checked>$currentSemester<br />
									   <input type='radio' class='formInputButton' name='new_semester' value='$nextSemester' $nextSemester_checked>$nextSemester<br />
									   <input type='radio' class='formInputButton' name='new_semester' value='$semesterTwo' $semesterTwo_checked>$semesterTwo";
			
				} else {
					if ($doDebug) {
						echo "No conditions. Dislaying $nextSemester and $semesterTwo<br />";
					}
					$semesterList	= "<input type='radio' class='formInputButton' name='inp_semester' value='$nextSemester' $nextSemester_checked>$nextSemester<br />
									   <input type='radio' class='formInputButton' name='inp_semester' value='$semesterTwo' $semesterTwo_checked>$semesterTwo";
			
				}
				if ($doDebug) {
					echo "can change anything<br />";
				}
				$verifyBanner	= "";
				if ($inp_verify == 'Y') {
					$content	.= "<table style='border:4px solid red;'>
									<tr><td><p>There are two steps to the verification process. 
									You are on the first step. You <b>MUST</b> complete both 
									steps or your confirmation information <u>will not be 
									recorded in the database</u>.</p></td></tr></table>";
				}
				//Build language selection
				$languageOptions			= '';
				$firstTime					= TRUE;
				$languageChecked			= '';
				foreach($languageArray as $thisLanguage) {
					if ($thisLanguage == $student_class_language) {
						$languageChecked		= 'checked';
					} else {
						$languageChecked		= '';
					}
					if ($firstTime) {
						$firstTime			= FALSE;
						$languageOptions		.= "<input type='radio' class='formInputButton' name='inp_class_language' value='$thisLanguage' $languageChecked required>$thisLanguage";
					} else {
						$languageOptions		.= "<br /><input type='radio' class='formInputButton' name='inp_class_language' value='$thisLanguage' $languageChecked required>$thisLanguage";
					}
				}

				$content		.= "<p>You may make changes to your sign-up information 
									until about three weeks before the start of the semester at which time the process to assign 
									students to advisor classes will occur.</p>";
				$deleteMsg		= "<span style='color: brown;'><input type='checkbox' class='formInputButton' name='inp_delete' value='Y'> To delete your 
								   registration, click in the box and then click 'Next' below</span>";
				$firstLine		= "<tr><td style='vertical-align:top;'>
											<b>Semester</b><br />
											$semesterList</td>
										<td style='vertical-align:top;'>
											<b>Class Language</b><br />
											$languageOptions</td>
										<td style='vertical-align:top;'>
											<b>Class Level</b><br />
											$levelList</td>
										<td>&nbsp;</td></tr>";
				$content			.= "<p>It is CW Academy's policy that a student 
										should not register for another class until 
										the student has completed the current class.</p>
										<form method='post' action='$theURL' 
										name='studentform' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='8'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_semester' value='$student_semester'>
										<input type='hidden' name='inp_level' value='$student_level'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='demonstration' value='$demonstration'>
										<input type='hidden' name='verifyMode' value='$verifyMode'>
										<input type='hidden' name='inp_callsign' value='$student_call_sign'>
										<input type='hidden' name='inp_verify' value='$inp_verify'>
										<input type='hidden' name='token' value='$token'>
										<input type='hidden' name='inp_class_language' value='$inp_class_language'>
										<input type='hidden' name='student_id' value='$student_id'>
										$extraHidden
										$deleteMsg											
										<h4>Student Master Data</h4>
										<table style='width:900px;'>
										<table style='width:900px;'>
										<tr><td><b>Callsign<br />$userName</b></td>
											<td><b>Name</b><br />$user_last_name, $user_first_name</td>
											<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
											<td><b>Email</b><br />$user_email</td></tr>
										<tr><td><b>City</b><br />$user_city</td>
											<td><b>State</b><br />$user_state</td>
											<td><b>Zip Code</b><br />$user_zip_code</td>
											<td><b>Country</b><br />$user_country</td></tr>
										<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
											<td><b>Telegram</b><br />$user_telegram</td>
											<td><b>Signal</b><br />$user_signal</td>
											<td><b>Messenger</b><br />$user_messenger</td></tr>
									<tr><td colspan='4'><hr></td></tr>
									<tr><td colspan='4'><h4>You Can Make Class Sign-up Changes Here:</h4></td></tr>											
									$firstLine
									<tr><td colspan='4'><hr></td></tr>
									<tr><td style='vertical-align:top;'>
											<b>Youth?</b><br />Select 'Yes' if <b>20 years of age or younger</b><br />
											<input type='radio' class='formInputButton' name='inp_youth' id='chk_youth' value='Yes' $youthYesChecked > Yes<br />
											<input type='radio' class='formInutButton' name='inp_youth' id='chk_youth' value='No' $youthNoChecked > No</td>
										<td style='vertical-align:top;'><br />	
											If 20 years of age or younger, please enter your age<br />
											<input type='text' name='inp_age' id='chk_age' size='5' class='formInputText' maxlength='5' value='$student_age'></td>
										<td style='vertical-align:top;'><br />
											If you are 17 years of age or younger, please provide a parent or guardian name and email address<br />
											Parent or guardian Name<br />
											<input type='text' name='inp_student_parent' id='chk_student_parent' size='30' class='formInputText' maxlength='30' value='$student_student_parent' ><br />
											Parent or Guardian email<br />
											<input type='text' name='inp_student_parent_email' id='chk_student_parent_email' size='40' class='formInputText' maxlength='40' value='$student_student_parent_email' ></td>
										<td></td></tr></table>
																				
										<p>Click <b>Next</b> to select your class preferences:<br />	
										<input class='formInputButton' type='submit' value='Next' /></p>
										</form>";
			}
		} else {
			$noRecord									= TRUE;
			$foundARecord								= FALSE;
		}
		if ($noRecord && $allowSignup) {	
			if ($doDebug) {
				echo "allowSignup is TRUE. Student is allowed to sign up for an upcoming semester<br />
					  inp_callsign: $inp_callsign<br />
			      	  inp_email: $inp_email<br />
			      	  inp_phone: $inp_phone<br />
			      	  inp_level: $inp_level<br />
			       	  allowSignup: $allowSignup<br />";
			}
			//// if the variable firsttime is set to first, then the student has come 
			//// here via the modify my data route and has not taken the self 
			//// assessment. In this case, bail out and send the student back to the 
			//// beginning	

			if ($firsttime == 'first') {
				$content	.= "<h3>$jobname</h3><p>No sign up record found to 
								modify. If you wish to sign up for a class, please click  
								<a href='$theURL'>HERE</a> and select the option to register for an upcoming semester</p>";
			} else {			
				$daysToGo			= days_to_semester($inp_semester);
				if ($doDebug) {
					echo "daysToGo: $daysToGo<br />";
				}
				///// if registering after the assignments to classes has been made, tell the student
				$waitListMsg		= "";
				$waiting			= "";
				if ($daysToGo > 0 && $daysToGo <= 22) {
					$waitListMsg	= "<br /><table style='border:4px solid red;'><tr><td>
										<span style='font-size:12pt;'>Student assignment to classes has already occurred for the $nextSemester semester. 
										You will be placed on a waiting list. Students do drop 
										out and CW Academy pulls replacement students from the waiting list. If you aren't selected 
										from the waiting list, your registration will be automatically moved to the $semesterTwo 
										semester and you will be given heightened priority for assignment to a class.</span></td></tr></table>";
					$waitingList	= TRUE;
					if ($doDebug) {
						echo "daysToGo <= 20 and next semester so will display the waitListMsg<br />";
					}
				} else {
					$waitListMsg	= "<br />Be advised that the process to assign students to classes will take 
										place about twenty days before the start of the semester and will be based on the actual classes 
										available at that time.";

				}
				$content	.= "<h4>Step 3 of 4 Steps</h4>
								<p>You have have requested the $inp_level Level class to be held in the $inp_semester Semester. 
								In this step, students 20 years of age or younger are requested to provide parent / guardian 
								information. If you are older than 20 years of age, click 'Submit to go to Step 3. If you are 
								20 years old or younger, please fill out the information below and click 'Submit' to go to Step 3.</p>
								<form method='post' action='$theURL' 
								name='newregistrationform' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='3'>
								<input type='hidden' name='demonstration' value='$demonstration'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<input type='hidden' name='waitingList' value='$waitingList'>
								<input type='hidden' name='inp_callsign' value='$inp_callsign'>
								<input type='hidden' name='inp_semester' value='$inp_semester'>
								<input type='hidden' name='inp_level' value='$inp_level'>
								<input type='hidden' name='allowSignup' value='$allowSignup'>
								<input type='hidden' name='inp_class_language' value='$inp_class_language'>
								<input type='hidden' name='token' value='$token'>
								<input type='hidden' id='browser_timezone_id' name='browser_timezone_id' value='$browser_timezone_id' />
								<input type='hidden' name='student_id' value='$student_id'>
								<h4>Student Master Data</h4><p>
								<table style='width:1000px;'>											
								<tr><td style='vertical-align:top;'>
										Select 'Yes' if <b>20 years of age or younger</b><br />
										<input type='radio' class='formInputButton' name='inp_youth' id='chk_youth' value='Yes' > Yes<br />
										<input type='radio' class='formInutButton' name='inp_youth' id='chk_youth' value='No' checked > No</td>
									<td style='vertical-align:top;'>	
										If 20 years of age or younger, please enter <b>your age</b><br />
										<input type='text' name='inp_age' id='chk_age' size='5' class='formInputText' maxlength='5' value='$student_age'></td>
									<td style='vertical-align:top;'>
										If you are 17 years of age or younger, please provide a parent or guardian name and email address<br />
										Parent or guardian <b>Name</b> and email address<br />
										<input type='text' name='inp_student_parent' id='chk_student_parent' size='30' class='formInputText' maxlength='30' value='$student_student_parent' ><br />
										<b>Parent or Guardian email</b><br />
										<input type='text' name='inp_student_parent_email' id='chk_student_parent_email' size='40' class='formInputText' maxlength='40' value='$student_student_parent_email' ></td></tr></table>
								Parent or Guardian name and email address are required for students 20 years of age or younger. Providing 
								that information indicates their agreement for you to be in a Morse code class with other adults.
								<table style='border:4px solid green;'><tr><td>Please click <span style='color:red;'><b>Submit</b></span> to continue with the sign-up process and make your class prefence choices:<br />
								<input class='formInputButton' type='submit' value='Submit' />
								</td></tr></table></form></p>";
			}
		} else {
			if (!$foundARecord) {
				$myStr			= 'enstr is blank ';
				if ($enstr != '') {
					$myStr		= "enstr decoded to $encodedString ";
				}
				if ($foundARecord) {
					$myStr		.= "foundARecord is TRUE ";
				} else {
					$myStr		.= "foundARecord is FALSE ";
				}
				$variableDump	= get_defined_vars();
				$newStr			= print_r($variableDump,TRUE);
				sendErrorEmail("$jobname Pass2: no record found for $inp_callsign but allowSignup is FALSE. email: $inp_email. Phone: $inp_phone. $myStr\n<br /><pre>$newStr</pre>");
				$content		.= "A fatal program error has occurred. System admin has been notified.";
			}
		}



////////////////// Pass 3	store sign-up info, get utc offset, and select classes


	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 3<br />
				  inp_callsign: $inp_callsign<br />
				  inp_semester: $inp_semester<br />
				  inp_level: $inp_level<br/><br />";
		}
		$content	.= "<h3>$jobname</h3>
						<h4>Step 4 of 4 Steps</h4>";

		if ($doDebug) {
			if ($haveStudentData) {
				echo "haveStudentData is TRUE<br />";
			} else {
				echo "haveStudentData is FALSE<br />";
			}
			if ($haveMasterData) {
				echo "haveMasterData is TRUE<br />";
			} else {
				echo "haveMasterData is FALSE<br />";
			}
			if ($haveInpSemester) {
				echo "haveInpSemester is TRUE<br />";
			} else {
				echo "haveInpSemester is FALSE<br />";
			}
			if ($allowSignup) {
				echo "allowSignup is TRUE<br />";
			} else {
				echo "allowSignup is FALSE<br />";
			}
		}
		$userName			= $inp_callsign;
		$doProceed			= TRUE;
		$getOut				= FALSE;
		
		
		// calculate the utc offset from the master records timezone_id
		$myArray				= explode(" ",$inp_semester);
		$thisYear				= $myArray[0];
		$thisMonDay				= $myArray[1];
		$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
		$myMonDay				= $myConvertArray[$thisMonDay];
		$thisNewDate			= "$thisYear$myMonDay 00:00:00";
		if ($doDebug) {
			echo "converted $inp_semester to $thisNewDate<br />";
		}
		$dateTimeZoneLocal 		= new DateTimeZone($user_timezone_id);
		$dateTimeZoneUTC 		= new DateTimeZone("UTC");
		$dateTimeLocal 			= new DateTime($thisNewDate,$dateTimeZoneLocal);
		$dateTimeUTC			= new DateTime($thisNewDate,$dateTimeZoneUTC);
		$php2 					= $dateTimeZoneLocal->getOffset($dateTimeUTC);
		$inp_timezone_offset 	= $php2/3600;
		if ($doDebug) {
			echo "used timezone_id of $user_timezone_id to calculate offset of $inp_timezone_offset<br />";
		}

		$inp_action_log		= "STDREG $currentDateTime $inp_callsign sign-up record prepared ";
		if ($inp_semester == '') {
			sendErrorEmail("$jobname pass 3 inp_semeter is empty. Assuming nextSemster, inp_bypass: $inp_bypass; inp_doAgain: $inp_doAgain");
			$inp_semester	= $nextSemester;
			$inp_action_log = "$inp_action_log / MISSING SEMESTER. Arbitrarily assigned $nextSemester, inp_bypass: $inp_bypass; inp_doAgain: $inp_doAgain ";
		}

///		$inp_lastname		= addslashes($inp_lastname);
		$nowDate			= date('Y-m-d H:i:s');

		$ageError					= "";
		if ($inp_youth == 'Yes') {
			if ($doDebug) {
				echo "student_youth set to $inp_youth<br />
						Age: $inp_age<br />
						Parent: $inp_student_parent<br />
						Parent Email: $inp_student_parent_email<br />";
			}
			$ageStr					= $inp_age;
			$parentStr				= $inp_student_parent;
			if ($inp_student_parent == '') {
				$parentStr			= "<em>Not Provided</em>";
			}
			$parentEmailStr			= $inp_student_parent_email;
			if ($inp_student_parent_email == '') {
				$parentEmailStr		= "<em>Not Provided</em>";
			}
			if ($inp_age == '') {
				$ageError			.= "You indicated that you are 20 years old or younger 
										but did not give your age. Please click the 'Back' button 
										and enter your age.<br />";
				$doProceed			= FALSE;
			} elseif ($inp_age > 20) {
				$ageError			.= "You entered your age as $inp_age years old. 
										Because you are older than 20 years old, your age 
										has been deleted.<br />";
				$inp_youth		= '';
				$inp_age		= '';
				$inp_student_parent		= '';
				$inp_student_parent_email	= '';
			}
			if ($inp_youth == 'Yes' && ($inp_student_parent	== '' || $inp_student_parent_email == '')) {
				$ageError			.= "The name of a parent or guardian as well as an email 
										address for the parent or guardian is required. Please 
										click the 'Back' button and enter that information.<br />";
				$doProceed			= FALSE; 
			}
			
			$content		.= "<p>You indicated that you are 20 years old or younger 
								and have given your age as $ageStr years old. Your 
								parent / guardian is $parentStr whose email address 
								is $parentEmailStr<p>";
			if ($ageError != '') {
				$content	.= $ageError;
			} 
		} else {
			$content		.= "<p>You have indicated that you are older than 20 years old.</p>";
		}
		
		if ($doProceed) {
			/// save the registration data to be entered into the database after class selection
			if ($doDebug) {
				echo "saving the registration data in a json object<br />";
			}

			$student_call_sign			= $inp_callsign;
			 $student_time_zone			= $user_timezone_id;
			 $student_timezone_offset	= $inp_timezone_offset;
			 $student_youth				= $inp_youth;
			 $student_age				= $inp_age;
			 $student_parent			= $inp_student_parent;
			 $student_parent_email		= $inp_student_parent_email;
			 $student_waiting_list		= $inp_waiting_list;
			 $student_level				= $inp_level;
			 $student_class_language	= $inp_class_language;
			 $student_semester			= $inp_semester;
			 $student_request_date		= $nowDate;
			 $student_action_log		= $inp_action_log;
			 $student_class_language	= $inp_class_language;
	

			$insertParams		= array('student_call_sign'=>$student_call_sign,
										 'student_time_zone'=>$user_timezone_id,
										 'student_timezone_offset'=>$student_timezone_offset,
										 'student_youth'=>$student_youth,
										 'student_age'=>$student_age,
										 'student_parent'=>$student_student_parent,
										 'student_parent_email'=>$student_parent_email,
										 'student_waiting_list'=>$student_waiting_list,
										 'student_level'=>$student_level,
										 'student_class_language'=>$student_class_language,
										 'student_semester'=>$student_semester,
										 'student_request_date'=>$nowDate,
										 'student_action_log'=>$student_action_log);
										  
			$insertDataJson		= json_encode($insertParams);
	


			/// get the catalog information and display it
			$inp_data			= array('student_semester'=>$student_semester, 
										'student_level'=>$student_level, 
										'student_class_language'=>$student_class_language,
										'student_no_catalog'=>'', 
										'student_catalog_options'=>'',
										'student_flexible'=>'',  
										'student_first_class_choice_utc'=>'None', 
										'student_second_class_choice_utc'=>'None', 
										'student_third_class_choice_utc'=>'None', 
										'student_timezone_offset'=>$inp_timezone_offset,
										'doDebug'=>$doDebug,
										'testMode'=>$testMode);
			if ($doDebug) {
				echo "sending inp_data:<br /><pre>";
				print_r($inp_data);
				echo "</pre><br />";
			}
			$result				= generate_catalog_for_student($inp_data);
			if ($doDebug) {
				echo "returned from generate_catalog_for_student<br /><br />";
			}
			if ($result[0] === FALSE) {
				echo "generate_catalog_for_student returned FALSE. Reason: $result[1]<br />";
			} else {
				$result_option	= $result[0];
				$result_catalog	= $result[1];
				$date1			= $result[2];			// semester start date
				$date2			= $result[3];			// catalog available date
				$date3			= $result[4];			// student assigned date
				$schedAvail		= $result[5];
				
				$catAvailDate	= date('d M Y', $date2);
				$assignedDate	= date('d M Y',$date3);
				$option_message	= "";
				if ($result_option == 'option') {
					$option_message	= "<p>You are signing up for a $student_level Level class in 
										the $student_semester semester. The catalog of available 
										classes will not be available until $catAvailDate. 
										Please indicate when you will be 
										available to take a $student_level Level class by 
										selecting from the list below.</p>
										<p>Classes are an hour in length. The advisor decides when 
										the class will actually be held. Your indication of which part 
										of the day and which days of the week would work best for you 
										will help guide the advisor's schedule decision.<p>";
				} elseif ($result_option == 'catalog') {
					$result_option		= $result[0];
					$option_message		= "<p>The Class Catalog for $student_level Level classes is now available. 
											Select up to three class schedule options from the table below, 
											selecting one from each column. 
											CW Acadamy will try to assign you to one of the class options in the order you 
											specify. Whether or not you are assigned to a class will depend on the 
											number of students selecting that class schedule and the number of 
											available seats in the classes held at that time.</p>";
				} elseif ($result_option == 'avail') {
					$result_option		= $result[0];
					if ($doDebug) {
						echo "result_option of $result_option<br />";
					}
					$option_message 	= "<p>Students have already been assigned to advisor classes for the 
											$nextSemester semester. Consequently, 
											you are being placed on a waiting list. Students do drop out and 
											replacement students will be selected from the waiting list. 
											if you are not selected for a class, you will automatically be 
											moved to the next semester and given priority to be assigned 
											to a class.</p>
											<p>Select up to three preference options from the list below to indicate 
											when you would be available to take a class:</p>";
				}
				$content		.= "$option_message
									<form method='post' action='$theURL' 
									name='classselection' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='4'>
									<input type='hidden' name='result_option' value='$result_option'>
									<input type='hidden' name='inp_callsign' value='$student_call_sign'>
									<input type='hidden' name='token' value='$token'>
									<input type='hidden' name='inp_semester' value='$student_semester'>
									<input type='hidden' name='inp_level' value='$student_level'>
									<input type='hidden' name='inp_class_language' value='$inp_class_language'>
									<input type='hidden' name='schedAvail' value='$schedAvail'>
									<input type='hidden' name='insertDataJson' value='$insertDataJson'>
									$result[1]<br clear='all' />
									<input class='formInputButton' type='submit' value='Submit' />
									</form></p>";
			}
		}

/////////////////// Pass 4		Write first, second, third choices to database and show student the registration info	
		
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 4<br />";
		}
		$userName				= $inp_callsign;

		if ($doDebug) {
			if ($haveStudentData) {
				echo "haveStudentData is TRUE<br />";
			} else {
				echo "haveStudentData is FALSE<br />";
			}
			if ($haveMasterData) {
				echo "haveMasterData is TRUE<br />";
			} else {
				echo "haveMasterData is FALSE<br />";
			}
			if ($haveInpSemester) {
				echo "haveInpSemester is TRUE<br />";
			} else {
				echo "haveInpSemester is FALSE<br />";
			}
			if ($allowSignup) {
				echo "allowSignup is TRUE<br />";
			} else {
				echo "allowSignup is FALSE<br />";
			}
		}
		
		if ($haveStudentData) {		// has refreshed the page
			if ($doDebug) {
				echo "page has been refreshed. Bypassing to pass4Bypass<br />";
			}
			if ($student_hold_reason_code == 'B') {
				$badActorResult	= TRUE;
			} else {
				$badActorResult = FALSE;
			}
			$firstChoice		= $student_first_class_choice;
			$secondChoice		= $student_second_class_choice;
			$thirdChoice		= $student_third_class_choice;
			goto pass4Bypass;
		}
		
		if ($doDebug) {
			echo "Have the following information:
					inp_callsign: $inp_callsign<br />
					inp_semester: $inp_semester<br />
					result_option: $result_option<br />";
		}

		$insertDataJson1 	= stripslashes($insertDataJson);
		$updateParams		= json_decode($insertDataJson1,TRUE);		
		
		$doUpdateStudent	= FALSE;
		$actionLogUpdates	= "";
		$badActorResult		= FALSE;

		if ($result_option == 'option') {
			$student_no_catalog						= 'Y';
			$updateParams['student_no_catalog']		= 'Y';
			$student_abandoned						= 'N';
			$updateParams['student_abandoned']		= 'N';
			$actionLogUpdates						.= "Set no_catalog to Y, Set abandoned to N, ";
			$actionLogUpdates						.= "Set abandoned to N ";
			$doUpdateStudent						= TRUE;
		
			if (count($inp_sked_times) > 0) {
				$student_catalog_options 			= "";
				$haveAny							= FALSE;
				$firstTime							= TRUE;
				foreach($inp_sked_times as $thisValue) {
					if ($firstTime) {
						$firstTime				= FALSE;
						$student_catalog_options	= $thisValue;
					} else {
						$student_catalog_options	= "$student_catalog_options,$thisValue";
					}
				}
			}
			if ($inp_flex == 'ANY') {
				$student_flexible				= 'Y';
				$updateParams['student_flexible']	= 'Y';
				$actionLogUpdates				.= "Set flexible to Y, ";
				$doUpdateStudent				= TRUE;
				$student_catalog_options		= "";
			}
			$updateParams['student_catalog_options']	= $student_catalog_options;
			$actionLogUpdates					.= "set catalog_options to $student_catalog_options, ";
			$doUpdateStudent					= TRUE;
		} elseif ($result_option == 'catalog' || $result_option == 'avail') {
			if ($doDebug) {
				echo "handling the catalog option<br />
				inp_sked1: $inp_sked1<br />
				inp_sked2: $inp_sked2<br />
				inp_sked3: $inp_sked3<br />";
			}
			$student_no_catalog 				= 'N';
			$updateParams['student_no_catalog']	= 'N';
			$student_abandoned					= 'N';
			$updateParams['student_abandoned']	= 'N';
			$updateFormat[]						= '%s';
			$actionLogUpdates					.= "Set no_catalog to Y, ";
			$actionLogUpdates					.= "Set abandoned to N ";
			if ($result_option == 'avail') {
				$updateParams['student_waiting_list']					= 'Y';
				$actionLogUpdates										.= "Set waiting_list to Y ";
				$student_waiting_list									= 'Y';
			}
			$myInt												= strpos($inp_sked1,"|");
			if ($myInt !== FALSE) {
				$myArray										= explode("|",$inp_sked1);						
				$student_first_class_choice						= $myArray[0];
				$student_first_class_choice_utc					= $myArray[1];
				$updateParams['student_first_class_choice']		= $student_first_class_choice;
				$updateParams['student_first_class_choice_utc']	= $student_first_class_choice_utc;
				$doUpdateStudent								= TRUE;
				$actionLogUpdates								.= "Set first_class_choices, ";
				$firstChoice									= $student_first_class_choice;
			} else {
				sendErrorEmail("$jobname pass $strPass inp_sked1 of $inp_sked1 is invalid for $inp_callsign");
				$student_first_class_choice						= "None";
				$student_first_class_choice_utc					= "None";
				$updateParams['student_first_class_choice']		= 'None';
				$updateParams['student_first_class_choice_utc']	= 'None';
				$doUpdateStudent								= TRUE;
				$actionLogUpdates								.= "Set first_class_choices to NONE, ";
				$firstChoice									= 'None';
			}

			$myInt												= strpos($inp_sked2,"|");
			if ($myInt !== FALSE) {
				$myArray										= explode("|",$inp_sked2);						
				$student_second_class_choice					= $myArray[0];
				$student_second_class_choice_utc				= $myArray[1];
				$updateParams['student_second_class_choice']		= $student_second_class_choice;
				$updateParams['student_second_class_choice_utc']	= $student_second_class_choice_utc;
				$doUpdateStudent								= TRUE;
				$actionLogUpdates								.= "Set second_class_choices, ";
				$secondChoice									= $student_second_class_choice;
			} else {
				sendErrorEmail("$jobname pass $strPass inp_sked2 of $inp_sked2 is invalid for $inp_callsign");
				$student_second_class_choice					= "None";
				$student_second_class_choice_utc				= "None";
				$updateParams['student_second_class_choice']	= 'None';
				$updateParams['student_second_class_choice_utc']	= 'None';
				$doUpdateStudent								= TRUE;
				$actionLogUpdates								.= "Set second_class_choices to NONE, ";
				$secondChoice									= 'None';
			}

			$myInt												= strpos($inp_sked3,"|");
			if ($myInt !== FALSE) {
				$myArray										= explode("|",$inp_sked3);						
				$student_third_class_choice						= $myArray[0];
				$student_third_class_choice_utc					= $myArray[1];
				$updateParams['student_third_class_choice']		= $student_third_class_choice;
				$updateParams['student_third_class_choice_utc']	= $student_third_class_choice_utc;
				$doUpdateStudent								= TRUE;
				$actionLogUpdates								.= "Set third_class_choices, ";
				$thirdChoice									= $student_third_class_choice;
			} else {
				sendErrorEmail("$jobname pass $strPass inp_sked3 of $inp_sked3 is invalid for $inp_callsign");
				$student_third_class_choice						= "None";
				$student_third_class_choice_utc					= "None";
				$updateParams['student_third_class_choice']		= 'None';
				$updateParams['student_third_class_choice_utc']	= 'None';
				$doUpdateStudent								= TRUE;
				$actionLogUpdates								.= "Set third_class_choices to NONE, ";
				$thirdChoice									= 'None';
			}
			
			if ($inp_flex == 'ANY') {
				$student_flexible								= 'Y';
				$updateParams['student_flexible']				= 'Y';
				$actionLogUpdates								.= "Set flexible to Y, ";
				$doUpdateStudent								= TRUE;
			}

			$updateParams['student_response']						= 'Y';
			$student_response										= 'Y';
			$myStr													= date('Y-m-d H:i:s');
			$updateParams['student_response_date']					= $myStr;
			$actionLogUpdates										.= "set response to Y and set response_date, ";
			$doUpdateStudent										= TRUE;
		}

		// check to see if the student is in the bad actors table
		$badActorResult			= checkForBadActor($inp_callsign,$doDebug);
		if ($badActorResult) {					/// is a bad actor
			$actionLogUpdates	.= "Student is in the bad actor table, ";
			$updateParams['student_intervention_required']			= 'H';
			$updateParams['student_hold_reason_code']				= 'B';
			$doUpdateStudent										= TRUE;
		}
		if ($doUpdateStudent) {
			if ($doDebug) {
				echo "setting up to insert a new student record<br />";
			}
			$myInt = strlen($actionLogUpdates) -2;
			$actionLogUpdates = substr($actionLogUpdates,0,$myInt);
			$updateParams['student_action_log']	= " / $actionDate $userName STDREG $actionLogUpdates";
			$updateParams['student_call_sign'] = $inp_callsign;

			// insert the record
			$insertResult = $student_dal->insert($updateParams,$operatingMode);
			if ($insertResult === FALSE) {
				if ($doDebug) {
					echo "insert failed. Check debug.log<br />";
				}
			} else {
				$insertID			= $insertResult;
				if ($doDebug) {
					echo "successfully inserted record $insertID<br />";
				}
				/// Now check to see if the student 
				/// is also signed up as an advisor. If so, set the advisor survey_score 
				/// to 13 and send an email about it to Roland and Bob
				if ($doDebug) {
					echo "checking to see if student is also signed up as an advisor<br />";
				}
				$criteria = [
					'relation' => 'AND',
					'clauses' => [
						['field' => 'advisor_call_sign','value' => $inp_callsign,'compare' => '='], 
						['field' => 'adviso_verify_response','value' => 'R','compare' => '!='], 
						['field' => 'advisor_semester','value' => $student_semester,'compare' => '='] 
					]
				];
				$wpw1_cwa_advisor = $advisor_dal->get_advisor($criteria,$operatingMode);
				if ($wpw1_cwa_advisor === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					if (! empty($wpw1_cwa_advisor)) {
						// student is also signed up as an advisor. Set the user_master survey score to 13
						$user_result = $user_dal->get_user_master_by_callsign($inp_callsign,$operatingMode);
						if ($user_result === NULL) {
							sendErrorEmail("$jobname Pass4 Bad Actor Attempting to get user_master by callsign for $inp_callsign returned NULL");
						} else {
							foreach($user_result as $key => $value) {
								$$key = $value;
							}
							$user_action_log .= " / $actionDate $userName STDREG student signed up as advisor in the same semester. Set advisor survey_score to 13 ";
							$updateParams = array('user_action_log'=>$user_action_log,
												  'user_survey_score'=>13);
							$userUpdateResult = $user_dal->	update($user_ID,$updateParams,$operatingMode);
							if ($userUpdateResult === FALSE) {
								sendErrorMessage("$jobname Pass4 BAD ACTOR Attempting to update user_master for $inp_callsign returned FALSE");
							}
						}						
					}
				}
			}			
		}
		
		// come here if pass 4 being run a 2nd time
		pass4Bypass:

		
		$waitListMsg				= "";
		$nocatalogMsg				= "";
		if (!$badActorResult) {
			$hotmailStr				= '';
			if (strpos($user_email,'hotmail') !== FALSE) {
				$hotmailStr			= "<b>NOTE:</b> Whitelisting the CW Academy is particularly important 
									   for you as you have a Hotmail account. Hotmail will 
									   arbitrarily discard emails it thinks are spam unless 
									   the email address has been whitelisted.</p>";
			}
			if ($student_no_catalog == 'Y') {
				$nocatalogMsg			= "<p><b>NOTE:</b> You will 
											receive an email from CW Academy about 45 days before the start of the $inp_semester semester 
											asking you to select your class date and time preferences. You <span style='color:red;'><b>MUST</b></span> 
											follow the instructions in that 
											that email in order to be considered for assignment to a class.</p>";
			}
			if ($student_waiting_list == 'Y') {
				$waitListMsg	= "<table style='border:4px solid red;'><tr><td>
									<span style='font-size:12pt;'>Note that Student assignment to classes has already occurred for 
									the $inp_semester semester. You are on the waiting list. Students do drop 
									out and CW Academy pulls replacement students from the waiting list. If you aren't selected 
									from the waiting list, your registration will be automatically moved to the next  
									semester and you will be given heightened priority for assignment to a class.</span></td></tr></table>";
			}
			$content			.= "<h3>$jobname Completed</h3>
									$waitListMsg
									<p><p>Welcome to the CW Academy! Your registration has been saved. You will receive 
									a welcome email tomorrow. <b>Most communications from CW Academy will be by 
									email, so make sure these emails are not marked as spam</b>. In most email programs you do 
									that by adding <u>cwacademy@cwa.cwops.org</u> to your contact list in your email program. More 
									information is available at <a href='https://www.whitelist.guide/' target='_blank'>Email 
									Whitelist Guide</a>.</p>
									$hotmailStr
									<p>If you do not 
									receive the welcome email from CW Academy tomorrow, check your spam folder or your Promotions 
									folder and add cwacademy@cwa.cwops.org to your contact list.</p>
									$nocatalogMsg
									<button onClick=\"window.print()\">Click to print this<br />page for your records</button>";
		} else {
			$theSubject		= "CWA Error Report -- Bad Actor Registration";
			$theContent		= "Student $inp_callsign signed up for a $student_level class for the $stdent_semester semester. 
								The student is in the Bad Actors table. The registration is on hold.";
			$increment		= 0;
			if ($testMode) {
				$theSubject	= "TestMode $theSubject";
				$mailCode	= 2;
			} else {
				$mailCode	= 18;
			}
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
								'theSubject'=>$theSubject,
								'theContent'=>$theContent,
								'theCc'=>'',
								'mailCode'=>$mailCode,
								'jobname'=>$jobname,
								'increment'=>$increment,
								'testMode'=>$testMode,
								'doDebug'=>$doDebug));

			$content			.= "<h3>$jobname</h3>
									<h4>Your Sign-up Information</h4>
									<p>Your sign-up information has been recorded. However, you must discuss your sign-up with 
									the appropriate person at <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA Class Resolution</a>. 
									Your sign-up is on hold.</p>";
		}
		$content			.= "<p>You are signed-up as follows:
								<table style='width:900px;'>
								<tr><td><b>Callsign<br />$inp_callsign</b></td>
									<td><b>Name</b><br />$user_last_name, $user_first_name</td>
									<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
									<td><b>Email</b><br />$user_email</td></tr>
								<tr><td><b>City</b><br />$user_city</td>
									<td><b>State</b><br />$user_state</td>
									<td><b>Zip Code</b><br />$user_zip_code</td>
									<td><b>Country</b><br />$user_country</td></tr>
								<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
									<td><b>Telegram</b><br />$user_telegram</td>
									<td><b>Signal</b><br />$user_signal</td>
									<td><b>Messenger</b><br />$user_messenger</td></tr>
								<tr><td><b>Level: </b>$inp_level</td>
									<td><b>Language: </b>$inp_class_language</td>
									<td colspan='2'><b>Semester: </b>$inp_semester</td></tr>";
		if ($result_option == 'option') {
			$content	.= "<tr><td colspan='5'><b>Class Preferences</b><br />";
			if ($inp_flex == 'ANY') {
				$content	.= "My time is flexible</td></tr>";
			} else {
				$myArray	= explode(",",$student_catalog_options);
				foreach($myArray as $thisData) {
					$myStr		= $catalogOptions[$thisData];
					$content	.= "$myStr<br />";
				}
				$content		.= "</td></tr>";
			}
		} else {
			$content		.= "<tr><td><b>First Class Choice</b><br />$firstChoice</td>
									<td><b>Second Class Choice</b><br />$secondChoice</td>
									<td><b>Third Class Choice</b><br />$thirdChoice</td>
									<td>All times local time</td></tr>";
		}
		if ($student_youth == 'Yes') {
			$content	.= "<tr><td style='text-align:center;'>Youth<br />$student_youth</td>
								<td style='text-align:center;'>Age<br />$student_age</td>
								<td>Parent / Guardian<br />$student_student_parent</td>
								<td>Parent / Guardian Email<br />$student_student_parent_email</td></tr>";
		}
		$content		.= "</table></p>";
		if ($result_option != 'avail') {
			$content	.= "<p>If circumstances or your information changes, you can update this information up to 
								three weeks before the start of the $inp_semester semester by returning to the 
								<a href='$theURL'>CW Academy Student Registration</a> page.</p>";
		}
		$content		.= "<p>Please print this page for your reference.<br /><br />
								73,<br />
								CW Academy</p>
								<br /><br />You may close this window";



///////////	Pass 8

	} elseif ("8" == $strPass) {	//// do the update to the student record then if ok, update class choices
	
	
		if ($doDebug) {
			echo "<br />Arrived at pass 8<br />
				  inp_callsign: $inp_callsign<br />
				  inp_semester: $inp_semester<br />
				  inp_level: $inp_level<br />";
		}
		if ($doDebug) {
			if ($haveStudentData) {
				echo "haveStudentData is TRUE<br />";
			} else {
				echo "haveStudentData is FALSE<br />";
			}
			if ($haveMasterData) {
				echo "haveMasterData is TRUE<br />";
			} else {
				echo "haveMasterData is FALSE<br />";
			}
			if ($haveInpSemester) {
				echo "haveInpSemester is TRUE<br />";
			} else {
				echo "haveInpSemester is FALSE<br />";
			}
			if ($allowSignup) {
				echo "allowSignup is TRUE<br />";
			} else {
				echo "allowSignup is FALSE<br />";
			}
		}
		$userName				= $inp_callsign;
		$doProceed				= TRUE;
		$updateLog				= "";
		$semesterChanged		= FALSE;
		$levelChanged			= FALSE;
	
		if ($haveStudentData) {
			if ($inp_delete == 'Y') {
				if ($doDebug) {
					echo "would delete the record for $inp_callsign here";
				}
				$student_action_log				= "$student_action_log / STDREG $actionDate student $inp_callsign requested registration to be deleted ";
/*	the student record is not actually deleted
	the response is set to R
	if the student status is S or Y, remove the student from the advisor's class
		and send an email to the advisor CC bob and roland
	Update the student action log
*/
				if ($student_student_status == 'S' || $student_student_status == 'Y') {
					// student is assigned to an advisor, remove that assignment and let Bob and Roland know
					$inp_data			= array('inp_student'=>$student_call_sign,
												'inp_semester'=>$student_semester,
												'inp_assigned_advisor'=>$student_assigned_advisor,
												'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
												'inp_remove_status'=>'C',
												'inp_arbitrarily_assigned'=>$student_no_catalog,
												'inp_method'=>'remove',
												'jobname'=>$jobname,
												'userName'=>$userName,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
			
					$removeResult		= add_remove_student($inp_data);
					if ($removeResult[0] === FALSE) {
						$thisReason		= $removeResult[1];
						if ($doDebug) {
							echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
						}
						sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
//						$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
					} else {
						$student_action_log		= "$student_action_log /removed student from advisor: $student_assigned_advisor 
													class: $student_assigned_advisor_class and emailed advisor ";
						// get the advisor email address
						$advisorEmail = '';
						$advisorEmailData = $user_dal->get_user-master_by_callsign($student_assigned_advisor,$operatingMode);
						if ($advisorEmailData === FALSE) {
							if ($doDebug) {
								echo "attempting to get user_master for $student_assigned_advisor returned FALSE<br />";
							}
						} else {
							foreach($advisorEmailData as $key => $value) {
								foreach($value as $thisField => $thisValue) {
									if ($thisField == 'user_email') {
										$advisorEmail = $thisValue;
									}
								}
							}
						}
						if ($advisorEmail != '') {
							// send email to the advisor, cc roland and bob
							$theSubject		= "CW Academy -- Student Deleted Registration";
							$theContent		= "<p>Student $student_call_sign deleted his registration after being assigned to 
												$student_assigned_advisor class $student_assigned_advisor_class at level 
												$student_level. Consequently, the student has been removed from your class.</p>
												<p>If you would like the system to look for a replacement student, please email 
												 <a href='mailto:kcgator@gmail.com'>Bob Carter WR7Q</a>.</p>
												 <p>73,<br />CW Academy</p>";
							if ($testMode) {
								$mailCode	= 2;
								$increment	= 1;
								$theSubject	= "TESTMODE $theSubject";
							} else {
								$mailCode	= 11;
								$increment	= 0;
							}
							$mailResult		= emailFromCWA_v2(array('theRecipient'=>$advisorEmail,
																		'theSubject'=>$theSubject,
																		'theContent'=>$theContent,
																		'theCc'=>'',
																		'mailCode'=>$mailCode,
																		'jobname'=>$jobname,
																		'increment'=>$increment,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug));
						} else {
							if ($doDebug) {
								echo "Attempting to get user_email for $student_assigned_advisor failed<br />";
							}
						}
					}
				}
				
				// update student record
				$student_action_log		= "$student_action_log / set response to R and student_status to blank ";
				if ($student_assigned_advisor != 'AC6AC') {		// exclude the advisor if not Buzz
					if ($student_excluded_advisor == '') {
						$student_excluded_advisor 	= $student_assigned_advisor;
					} else {
						$student_excluded_advisor	= "$student_excluded_advisor|$student_assigned_advisor";
					}
				}
				$inp_data = array('student_action_log'=>$student_action_log,
								  'student_response'=>'R',
								  'student_status'=>'',
								  'student_excluded_advisor'=>$student_excluded_advisor);
				$updateResult = $student_dal->update($student_id,$inp_data,$operatingMode);
				if ($updateResult === FALSE) {
					if ($doDebug) {
						echo "Attempting to update student_response returned FALSE<br />";
					}
					$content		.= "Unable to update student_response<br />";
				} else {
					if ($doDebug) {
						echo "student record updated<br />";
					}
					$content		.= "<h3>$jobname</h3>
										<h4>Request to Delete Registration for $inp_callsign</h4>
										Your request has been processed. When your circumstances allow, please sign up for a future 
										CW Academy class. You may close this window.";
					$doProceed		= FALSE;
				}
			} else {					// no deletion. Update the record
				if ($doDebug) {
					echo "Updating $student_call_sign Student Record<br />";
				}
				if ($student_response == 'R') {
					if ($doDebug) {
						echo "student_response was R. Changing to empty<br />";
					}
					$updateParams['student_response'] = '';
					$doUpdate				= TRUE;
				}
				if ($inp_semester != $student_semester) {
					$updateParams['student_semester'] = $inp_semester;
					$doUpdate				= TRUE;
					$updateLog				.= "semester changed to $inp_semester. ";
					$student_semester		= $inp_semester;
					$semesterChanged		= TRUE;
					$updateParams['student_first_class_choice'] = 'None';
					$updateParams['student_second_class_choice'] = 'None';
					$updateParams['student_third_class_choice'] = 'None';
					$updateParams['student_first_class_choice_utc'] = 'None';
					$updateParams['student_second_class_choice_utc'] = 'None';
					$updateParams['student_third_class_choice_utc'] = 'None';
					$updateParams['student_catalog_options'] = '';
					$updateParams['student_flexible'] = '';
					$updateParams['student_response'] = '';
					$updateParams['student_status'] = '';
				}
				if ($inp_level != $student_level) {
					$updateParams['student_level'] = $inp_level;
					$doUpdate = TRUE;
					$updateLog				.= "level changed to $inp_level. ";
					$student_level			= $inp_level;
					$levelChanged			= TRUE;
				}
				if ($inp_class_language != $student_class_language) {
					$updateParams['student_class_language'] = $inp_class_language;
					$doUpdate				= TRUE;
					$updateLog				.= "class language changed to $inp_class_language. ";
					$student_class_language	= $inp_class_language;
				}
				if ($semesterChanged || $levelChanged) {						
					$updateParams['student_email_sent_date'] = '';
					$updateParams['student_email_number'] = '';
					$updateParams['student_response'] = '';
					$updateParams['student_response_date'] = '';
					$updateParams['student_welcome_date'] = '';
				}
				if ($inp_youth != $student_youth) {
					$updateParams['student_youth'] = $inp_youth;
					$doUpdate				= TRUE;
					$updateLog				.= "youth changed from $student_youth to $inp_youth. ";
					$student_youth			= $inp_youth;
				}
				if ($inp_age != $student_age) {
					$updateParams['student_age'] = $inp_age;
					$doUpdate				= TRUE;
					$updateLog				.= "age changed from $student_age to $inp_age. ";
					$student_age			= $inp_age;
				}
				if ($inp_student_parent != $student_parent) {
					$updateParams['student_parent'] = $inp_student_parent;
					$doUpdate				= TRUE;
					$updateLog				.= "student_parent changed from $student_student_parent to $inp_student_parent. ";
					$student_student_parent	= $inp_student_parent;
				}
				if ($inp_student_parent_email != $student_parent_email) {
					$updateParams['student_parent_email'] = $inp_student_parent_email;
					$doUpdate				= TRUE;
					$updateLog				.= "student_parent_email changed from $student_student_parent_email to $inp_student_parent_email. ";
					$student_student_parent_email	= $inp_student_parent_email;
				}
	
				/// if semester changed, check to see if the offset changes as well
				if ($semesterChanged) {
					if ($doDebug) {
						echo "semester has changed. Checking to see if offset changes<br />";
					}
					$myArray			= explode(" ",$inp_semester);
					$thisYear			= $myArray[0];
					$thisMonDay			= $myArray[1];
					$myConvertArray		= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01','JAN/FEB'=>'-01-01','APR/MAY'=>'-04-01','MAY/JUN'=>'-05-01','SEP/OCT'=>'-09-01','Apr/May'=>'-04-01');
					$myMonDay			= $myConvertArray[$thisMonDay];
					$thisNewDate		= "$thisYear$myMonDay 00:00:00";
					if ($doDebug) {
						echo "converted $new_semester to $thisNewDate<br />";
					}
					$dateTimeZoneLocal 	= new DateTimeZone($user_timezone_id);
					$dateTimeZoneUTC 	= new DateTimeZone("UTC");
					$dateTimeLocal 		= new DateTime($thisNewDate,$dateTimeZoneLocal);
					$dateTimeUTC		= new DateTime($thisNewDate,$dateTimeZoneUTC);
					$php2 				= $dateTimeZoneLocal->getOffset($dateTimeUTC);
					$offset 			= $php2/3600;
					if ($offset != $student_timezone_offset) {
						$updateParams['student_timezone_offset'] = $offset;
						$doUpdate							= TRUE;
						if ($doDebug) {
							echo "changed offset from $student_timezone_offset to $offset<br />";
						}
						$student_timezone_offset	= $offset;
						$updateLog					.= "Timezone_offseet changed from $student_timezone_offset to $offset. ";
					}
				}
			}
						
			if ($doUpdate) {
				$student_action_log					= "$student_action_log / $actionDate STDREG $updateLog";
				$updateParams['student_action_log'] = $student_action_log;

				if ($doDebug) {
					echo "updateParams before filtering:<br /><pre>";
					print_r($updateParams);
					echo "</pre><br />";
				}

				// filter out any duplicate updates (take the last one)
				$newParams = array();
				foreach($updateParams as $thisKey => $thisValue) {
					$newParams[$thisKey] = $thisValue;
				}
			
				if ($doDebug) {
					echo "Updating record for $inp_callsign<br /><pre>";
					print_r($newParams);
					echo "</pre><br />";
					
				}
				$updateResult = $student_dal->update($student_id,$newParams,$operatingMode);
				if ($updateResult === FALSE) {
					if ($doDebug) {
						echo "attempting toupdate $student_id returned FALSE";
					}
				} else {
					$theMessage		= "";
				}
			} else {
				if ($doDebug) {
					echo "No updates requested for $inp_callsign<br />";
				}
			}
			if ($doProceed) {
				// now get the catalog and display it


				$inp_data			= array('student_semester'=>$student_semester, 
											'student_level'=>$student_level,
											'student_class_language'=>$student_class_language, 
											'student_no_catalog'=>$student_no_catalog, 
											'student_catalog_options'=>$student_catalog_options,
											'student_flexible'=>$student_flexible,  
											'student_first_class_choice_utc'=>$student_first_class_choice_utc, 
											'student_second_class_choice_utc'=>$student_second_class_choice_utc, 
											'student_third_class_choice_utc'=>$student_third_class_choice_utc, 
											'student_timezone_offset'=>$student_timezone_offset,
											'doDebug'=>$doDebug,
											'testMode'=>$testMode,
											'verifyMode'=>$verifyMode);
				if ($doDebug) {
					echo "sending inp_data:<br /><pre>";
					print_r($inp_data);
					echo "</pre><br />";
				}
				$result				= generate_catalog_for_student($inp_data);
				if ($doDebug) {
					echo "returned from generate_catalog_for_student<br /><br />";
				}
				if ($result[0] === FALSE) {
					echo "generate_catalog_for_student returned FALSE. Reason: $result[1]<br />";
				} else {
					$result_option	= $result[0];
					$result_catalog	= $result[1];
					$date1			= $result[2];
					$date2			= $result[3];
					$date3			= $result[4];
					$schedAvail		= $result[5];
					$option_message	= "";
					if ($result_option == 'option') {
						$option_message	= "<p>You are updating your registration for a $student_level Level class in 
											the $student_semester semester. The catalog of available 
											classes will not be available until about 45 days before 
											the start of the semester. Please indicate when you will be 
											available to take a $student_level Level class by 
											selecting from the list below:</p>";
					} elseif ($result_option == 'catalog') {
						$result_option		= $result[0];
						$languageStr		= '';
						if ($student_class_language != 'English') {
							$languageStr	= "Your preferred class language is $student_class_language. If a class 
												is being offered in your preferred language, it will be shown below along 
												with classes being offered in English. ";
						}
						$option_message		= "<p>The Class Catalog for $student_level Level classes is now available. 
												$languageStr
												Select up to three class schedule options from the table below. 
												CW Acadamy will try to assign you to one of the class options in the order you 
												specify. Whether or not you are assigned to a class will depend on the 
												number of students selecting that class schedule and the number of 
												available seats in the classes held at that time. Note that the 
												more classes being held the higher the probability of being assigned 
												to a class.</p>";
					} elseif ($result_option == 'avail') {
						$result_option		= $result[0];
						if ($doDebug) {
							echo "result_option of $result_option<br />";
						}
						$option_message 	= "<p>Students have already been assigned to advisor classes. There may possibly 
												be classes with available seats. If so, they are listed below. If any of the class 
												schedule listed below will work for you, select that class and submit the 
												selection. Only one choice can be selected.</p>";
					}
					$content		.= "<h3>$jobname</h3>
										$option_message
										<form method='post' action='$theURL' 
										name='classselection' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='9'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='result_option' value='$result_option'>
										<input type='hidden' name='student_id' value='$student_id'>
										<input type='hidden' name='inp_callsign' value='$student_call_sign'>
										<input type='hidden' name='inp_verify' value='$inp_verify'>
										<input type='hidden' name='token' value='$token'>
										<input type='hidden' name='inp_semester' value='$student_semester'>
										<input type='hidden' name='schedAvail' value='$schedAvail'>
										$result[1]<br clear='all' />";
					if ($result_option == 'option') {
						$content	.= "<input class='formInputButton' type='submit' onclick=\"return validate_checkboxes(this.form);\" value='Submit' />";
					} else {			
						$content	.= "<input class='formInputButton' onclick=\"return validate_form(this.form);\" type='submit' value='Submit' />";
					}
					$content	.= "</form></p>";
				}
			}
		} else {
			if ($doDebug) {
				echo "no record found for the student_id<br />";
			}
			$content		.= "<p>Fatal Error: No record found to update</p>";
		} 		



	} elseif ("9" == $strPass) {				/// update the catalog choices
	
		if ($doDebug) {
			echo "<br />arrived at pass 9<br />";
		}
		if ($doDebug) {
			if ($haveStudentData) {
				echo "haveStudentData is TRUE<br />";
			} else {
				echo "haveStudentData is FALSE<br />";
			}
			if ($haveMasterData) {
				echo "haveMasterData is TRUE<br />";
			} else {
				echo "haveMasterData is FALSE<br />";
			}
			if ($haveInpSemester) {
				echo "haveInpSemester is TRUE<br />";
			} else {
				echo "haveInpSemester is FALSE<br />";
			}
			if ($allowSignup) {
				echo "allowSignup is TRUE<br />";
			} else {
				echo "allowSignup is FALSE<br />";
			}
		}
		
		$userName				= $inp_callsign;
		$firstChoice			= '';
		$secondChoice			= '';
		$thirdChoice			= '';

		if ($doDebug) {
			echo "Have the following information:
				inp_callsign: $inp_callsign<br />
				student_id: $student_id<br />
				inp_verify: $inp_verify<br />";
		}
		
		$actionLogUpdates	= "";
		$doUpdateStudent	= FALSE; 
		
		//// get the student record for update and display
		if ($haveStudentData) {

			$doUpdateStudent						= FALSE;
			$firstChoice							= $student_first_class_choice;
			$secondChoice							= $student_second_class_choice;
			$thirdChoice							= $student_third_class_choice;

			if ($result_option == 'option') {
				if ($doDebug) {
					echo "going through option logic<br >";
				}
				if ($student_no_catalog != 'Y') {
					$student_no_catalog					= 'Y';
					$updateParams['student_no_catalog'] = 'Y';
					$actionLogUpdates					.= "Set no_catalog to Y, ";
					$doUpdateStudent					= TRUE;
				}
				if (count($inp_sked_times) > 0) {
					$myStr					 			= "";
					$haveAny							= FALSE;
					$firstTime							= TRUE;
					foreach($inp_sked_times as $thisValue) {
						if ($doDebug) {
							echo "inp_sked_times is $thisValue<br />";
						}
						if ($thisValue != 'ANY') {
							if ($firstTime) {
								$firstTime					= FALSE;
								$myStr						= $thisValue;
							} else {
								$myStr						= "$myStr,$thisValue";
							}
						} else {
							$haveAny					= TRUE;
							if ($doDebug) {
								echo "found ANY<br />";
							}
						}
					}
					if ($haveAny) {
						if ($student_flexible != 'Y') {
							$student_flexible			= 'Y';
							$updateParams['student_flexible'] = 'Y';
							$actionLogUpdates			.= "Set flexible to Y, ";
							$doUpdateStudent			= TRUE;
							$student_flexible			= "Y";
							$myStr						= "";
						}
					} else {
						if ($student_flexible != 'N') {
							$student_flexible			= 'N';
							$updateParams['student_flexible'] = 'N';
							$actionLogUpdates			.= "Set flexible to N, ";
							$doUpdateStudent			= TRUE;
							$student_flexible			= "N";
						}
					}
					if ($student_catalog_options != '$myStr') {
						$student_catalog_options		= $myStr;
						$updateParams['student_catalog_options'] = $student_catalog_options;
						$actionLogUpdates				.= "set catalog_options to $student_catalog_options, ";
						$doUpdateStudent				= TRUE;
					}
				}
			} elseif ($result_option == 'catalog') {
				if ($doDebug) {
					echo "handling the catalog option<br />";
				}
				if ($student_no_catalog != 'N') {
					$student_no_catalog 	= 'N';
					$updateParams['student_no_catalog'] = 'N';
					$actionLogUpdates		.= "Set no_catalog to N, ";
				}
				$updateParams['student_abandoned'] = 'N';
				$doUpdateStudent			= TRUE;

				if ($inp_flex == 'ANY') {
					$updateParams['student_flexible'] = 'Y';
					$actionLogUpdates		.= "set flexible to Y ";
					$doUpdateStudent		= TRUE;
				} else {
					$updateParams['student_flexible'] = 'N';
					$actionLogUpdates		.= "set flexible to N ";
					$doUpdateStudent		= TRUE;
				}
				if ($inp_sked1 == 'None') {
					if ($doDebug) {
						echo "inp_sked1 is None. Should not happen!!<br />";
					}
					sendErrorEmail("$jobname pass9 first class choice is None. Should not happen. Student $student_call_sign");
				} else {
					$myArray			= explode("|",$inp_sked1);
					if (count($myArray) == 2) {
						$updateParams['student_first_class_choice'] = $myArray[0];
						$updateParams['student_first_class_choice_utc'] = $myArray[1];
						$doUpdateStudent	= TRUE;
						$firstChoice 		= $myArray[0];
						$actionLogUpdates	.= "set first_class_choice to $myArray[0] ";
					} else {
						if ($doDebug) {
							echo "inp_sked1 is $inp_sked1 and it does not compute<br />";
						}
					}						
					if ($inp_sked2 !== 'None') {
						$myArray			= explode("|",$inp_sked2);
						if (count($myArray) == 2) {
							$updateParams['student_second_class_choice'] = $myArray[0];
							$updateParams['student_second_class_choice_utc'] = $myArray[1];
							$doUpdateStudent	= TRUE;
							$secondChoice	 	= $myArray[0];
							$actionLogUpdates	.= "set second_class_choice to $myArray[0] ";
						} else {
							$updateParams['second_class_choice'] = 'None';
							$updateParams['second_class_choice_utc'] = 'None';
							$doUpdateStudent	= TRUE;
							$secondChoice 		= 'None';
						}
					} else {
							$updateParams['student_second_class_choice'] = 'None';
							$updateParams['student_second_class_choice_utc'] = 'None';
							$doUpdateStudent	= TRUE;
							$secondChoice 		= 'None';
					}
					if ($inp_sked3 !== 'None') {
						$myArray			= explode("|",$inp_sked3);
						if (count($myArray) == 2) {
							$updateParams['student_third_class_choice'] = $myArray[0];
							$updateParams['student_third_class_choice_utc'] = $myArray[1];
							$thirdChoice 		= $myArray[0];
							$doUpdateStudent	= TRUE;
							$actionLogUpdates	.= "set third_class_choice to $myArray[0] ";
						} else {
							$updateParams['student_third_class_choice'] = 'None';
							$updateParams['student_third_class_choice_utc'] = 'None';
							$doUpdateStudent	= TRUE;
							$thirdChoice 		= 'None';
						}						
					} else {
							$updateParams['student_third_class_choice'] = 'None';
							$updateParams['student_third_class_choice_utc'] = 'None';
							$doUpdateStudent	= TRUE;
							$thirdChoice 		= 'None';
					}
				}


			} elseif ($result_option == 'avail') {
				if ($doDebug) {
					echo "<br />handling result_option avail<br />";
				}
				if ($inp_available == 'None') {
					if ($student_first_class_choice != '') {
						$student_first_class_choice			= '';
						$student_first_class_choice_utc		= '';
						$updateParams['student_first_class_choice'] = $student_first_class_choice;
						$updateParams['student_first_class_choice_utc'] = $student_first_class_choice_utc;
						$doUpdateStudent					= TRUE;
						$actionLogUpdates					.= "Removed first class choices, ";
						$firstChoice						= "None";
					}
					if ($student_second_class_choice != '') {
						$student_second_class_choice		= '';
						$student_second_class_choice_utc	= '';
						$updateParams['student_second_class_choice'] = $student_second_class_choice;
						$updateParams['student_second_class_choice_utc'] = $student_second_class_choice_utc;
						$actionLogUpdates					.= "Removed second class choices, ";
						$doUpdateStudent					= TRUE;
						$secondChoice						= "None";
					}
					if ($student_third_class_choice != '') {
						$student_third_class_choice			= '';
						$student_third_class_choice_utc		= '';
						$updateParams['student_third_class_choice'] = $student_third_class_choice;
						$updateParams['student_third_class_choice_utc'] = $student_third_class_choice_utc;
						$actionLogUpdates					.= "Removed third class choices, ";
						$doUpdateStudent					= TRUE;
						$thirdChoice						= "None";
					}
					if ($student_flexible != 'N') {
						$student_flexible					= "N";
						$updateParams['student_flexible'] = 'N';
						$actionLogUpdates					.= "set flexible to N, ";
						$doUpdateStudent					= TRUE;
					}
					if ($student_no_catalog != 'N') {
						$student_no_catalog					= 'N';
						$updateParams['student_no_catalog'] = 'N';
						$actionLogUpdates					.= "set no_catalog to N, ";
						$doUpdateStudent					= TRUE;
					}
					if ($student_waiting_list != 'Y') {	
						$student_waiting_list				= 'Y';
						$updateParams['student_waiting_list'] = 'Y';
						$actionLogUpdates					.= "set waiting_list to Y, ";
						$doUpdateStudent					= TRUE;
					}
				} else {
					$myArray							= explode("|",$inp_available);
					$this_first_class_choice			= $myArray[0];
					$this_first_class_choice_utc		= $myArray[1];
					if ($student_first_class_choice != $this_first_class_choice) {
						$student_first_class_choice		= $this_first_class_choice;
						$student_first_class_choice_utc	= $this_first_class_choice_utc;
						$updateParams['student_first_class_choice'] = $student_first_class_choice;
						$updateParams['student_first_class_choice_utc'] = $student_first_class_choice_utc;
						$doUpdateStudent				= TRUE;
						$actionLogUpdates				.= "Updated first class choices, ";
						$firstChoice					= $student_first_class_choice;
					}
					if ($student_second_class_choice != 'None') {
						$student_second_class_choice		= 'None';
						$student_second_class_choice_utc	= 'None';
						$updateParams['student_second_class_choice'] = $student_second_class_choice;
						$updateParams['student_second_class_choice_utc'] = $student_second_class_choice_utc;
						$actionLogUpdates					.= "Set second class choices to $student_second_class_choice ";
						$doUpdateStudent						= TRUE;
						$secondChoice						= "None";
					}
					if ($student_third_class_choice != 'None') {
						$student_third_class_choice			= 'None';
						$student_third_class_choice_utc		= 'None';
						$updateParams['third_class_choice'] = $student_third_class_choice;
						$updateParams['student_third_class_choice_utc'] = $student_third_class_choice_utc;
						$actionLogUpdates					.= "Set third class choices to $student_third_class_choice ";
						$doUpdateStudent					= TRUE;
						$thirdChoice						= "None";
					}
					if ($student_flexible != 'N') {
						$student_flexible					= "N";
						$updateParams['student_flexible'] = 'N';
						$actionLogUpdates					.= "Set flexible to N, ";
						$doUpdateStudent					= TRUE;
					}
					if ($student_no_catalog != 'N') {
						$student_no_catalog					= 'N';
						$updateParams['student_no_catalog'] = 'N';
						$actionLogUpdates					.= "Set no_catalog to N, ";
						$doUpdateStudent					= TRUE;
					}
				}
				if ($student_response != 'Y') {
					$updateParams['student_response'] = 'Y';
					$myStr									= date('Y-m-d H:i:s');
					$updateParams['student_response_date'] = $myStr;
					$actionLogUpdates						.= "set response to Y and set response_date, ";
					$doUpdateStudent						= TRUE;
				}
			}
			if ($student_abandoned == 'Y') {
				$updateParams['student_abandoned'] = 'N';
				$doUpdateStudent				= TRUE;
				$actionLogUpdates				.= "Set abandoned to N, ";
			}
			if ($inp_verify == 'Y') {
				$updateParams['student_response'] = 'Y';
				$doUpdateStudent				= TRUE;
			} elseif ($student_response == '' && $validEmailPeriod) {
				$updateParams['student_response'] = 'Y';
				$doUpdateStudent				= TRUE;
			}

			if ($doUpdateStudent) {
				$myInt					= strlen($actionLogUpdates) -2;
				if ($doDebug) {
					echo "Making these changes: $actionLogUpdates<br />";
				}
				$actionLogUpdates		= substr($actionLogUpdates,0,$myInt);
				$student_action_log		= "$student_action_log / $actionDate $userName STDREG $actionLogUpdates";
				$updateParams['student_action_log'] = $student_action_log;
	
				$updateResult = $student_dal->update($student_id,$updateParams,$operatingMode);
				if ($updateResult === FALSE) {
					if ($doDebug) {
						echo "attempting to update $student_id returned FALSE<br />";
					}
					$content		.= "Unable to update content in $studentTableName<br />";
				} else {

					/// Now check to see if the student 
					/// is also signed up as an advisor. If so, set the advisor survey_score 
					/// to 13 and send an email about it to Roland and Bob
					if ($doDebug) {
						echo "checking to see if student is also signed up as an advisor<br />";
					}
					$criteria = [
						'relation' => 'AND',
						'clauses' => [
							['field' => 'advisor_call_sign','value' => $inp_callsign,'compare' => '='], 
							['field' => 'adviso_verify_response','value' => 'R','compare' => '!='], 
							['field' => 'advisor_semester','value' => $student_semester,'compare' => '='] 
						]
					];
					$wpw1_cwa_advisor = $advisor_dal->get_advisor($criteria,$operatingMode);
					if ($wpw1_cwa_advisor === FALSE) {
						if ($doDebug) {
							echo "getting the advisor info returned FALSE<br />";
						}
					} else {
						if (count($wpw1_cwa_advisor) > 0) {
							// student is also signed up as an advisor. Set the user_master survey score to 13
							$user_result = $user_dal->get_user_master_by_callsign($inp_callsign,$operatingMode);
							if ($user_result === NULL) {
								sendErrorEmail("$jobname Pass4 Bad Actor Attempting to get user_master by callsign for $inp_callsign returned NULL");
							} else {
								foreach($user_result as $key => $value) {
									$$key = $value;
								}
								$user_action_log .= " / $actionDate $userName STDREG student signed up as advisor in the same semester. Set advisor survey_score to 13 ";
								$updateParams = array('user_action_log'=>$user_action_log,
													  'user_survey_score'=>13);
								$userUpdateResult = $user_dal->	update($user_ID,$updateParams,$operatingMode);
								if ($userUpdateResult === FALSE) {
									sendErrorMessage("$jobname Pass4 BAD ACTOR Attempting to update user_master for $inp_callsign returned FALSE");
								}
							}						
						}
					}
				}
			}
			$content	 		.= "<h3>CWA Student Sign-up Completed</h3>";

			// read the data again
			$wpw1_cwa_student = $student_dal->get_student_by_id($student_id,$operatingMode);
			if ($wpw1_cwa_student === FALSE) {
				if ($doDebug) {
					echo "attempting to get student id $student_id returned FALSE<br />";
				}
			} else {
				foreach($wpw1_cwa_student as $thisField => $thisValue) {
					$$thisField = $thisValue;
				}
			
				if ($student_intervention_required == 'H' and $student_hold_reason_code == 'B') {
					$content			.= "<h3>CWA Student Sign-up</h3>
											<h4>Record Updated</h4>
											<p>Your sign-up is on hold pending a discussion with the appropriate 
											person at <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA Class Resolution</a>.";
				} else {
					$waitListMsg				= "";
					$nocatalogMsg				= "";
					if ($student_no_catalog == 'Y') {
						$content			.= "<p><b>NOTE:</b> You will 
													receive an email from CW Academy about 45 days before the start of the $inp_semester semester 
													asking you to review and select your class date and time preferences. You <span style='color:red;'><b>MUST</b></span> respond to 
													that email in order to be considered for assignment to a class.</p>";
					}
					if ($student_waiting_list == 'Y') {
						$content			.= "<table style='border:4px solid red;'><tr><td>
												<span style='font-size:12pt;'>Note that Student assignment to classes has already occurred for 
												the $inp_semester semester. You are on the waiting list. Students do drop 
												out and CW Academy pulls replacement students from the waiting list. If you aren't selected 
												from the waiting list, your registration will be automatically moved to the next  
												semester and you will be given heightened priority for assignment to a class.</span></td></tr></table>";
					} 
					$content	 			.= "<p><p>Your registration update has been saved. <b>Most communications from CW Academy will be by 
												email, so make sure these emails are not marked as spam</b>. In most email programs you do 
												that by adding <u>cwacademy@cwa.cwops.org</u> to your contact list in your email program. More 
												information is available at <a href='https://www.whitelist.guide/' target='_blank'>Email 
												Whitelist Guide</a>.</p>
												<button onClick=\"window.print()\">Click to print this<br />page for your records</button>";
				}
				$content			.= "<p>You are signed-up as follows:
										<table style='width:900px;'>
										<tr><td><b>Callsign<br />$student_call_sign</b></td>
											<td><b>Name</b><br />$user_last_name, $user_first_name</td>
											<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
											<td><b>Email</b><br />$user_email</td></tr>
										<tr><td><b>City</b><br />$user_city</td>
											<td><b>State</b><br />$user_state</td>
											<td><b>Zip Code</b><br />$user_zip_code</td>
											<td><b>Country</b><br />$user_country</td></tr>
										<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
											<td><b>Telegram</b><br />$user_telegram</td>
											<td><b>Signal</b><br />$user_signal</td>
											<td><b>Messenger</b><br />$user_messenger</td></tr>
										<tr><td><b>Level: </b>$student_level</td>
											<td><b>Language: </b>$student_class_language</td>
											<td colspan='2'><b>Semester: </b>$student_semester</td></tr>";
				if ($student_no_catalog == 'Y') {
					$content	.= "<tr><td colspan='5'><b>Class Preferences</b><br />";
					if ($student_flexible == 'Y') {
						$content	.= "My time is flexible</td></tr>";
					} else {
						$myArray	= explode(",",$student_catalog_options);
						foreach($myArray as $thisData) {
							$myStr		= $catalogOptions[$thisData];
							$content	.= "$myStr<br />";
						}
						$content		.= "</td></tr>";
					}
				} else {
					$content		.= "<tr><td><b>First Class Choice</b><br />$firstChoice</td>
											<td><b>Second Class Choice</b><br />$secondChoice</td>
											<td><b>Third Class Choice</b><br />$thirdChoice</td>
											<td>All times local time</td></tr>";
				}
				if ($student_youth == 'Yes') {
					$content	.= "<tr><td style='text-align:center;'>Youth<br />$student_youth</td>
										<td style='text-align:center;'>Age<br />$student_age</td>
										<td>Parent / Guardian<br />$student_student_parent</td>
										<td>Parent / Guardian Email<br />$student_student_parent_email</td></tr>";
				}
				$content		.= "</table></p>
									<p>If circumstances or your information changes, you can update this information up to 
										three weeks before the start of the $inp_semester semester by returning to the 
										<a href='$theURL'>CW Academy Student Registration</a> page and 
										entering your call sign, email address, and phone number.</p>
										<p>Please print this page for your reference.<br /><br />
										73,<br />
										CW Academy</p>
										<br /><br />You may close this window";

				if ($token != '') {
					if ($doDebug) {
						echo "going to resolve_reminder<br />";
					}
					$result		= resolve_reminder($student_call_sign,$token,$testMode,$doDebug);
					if ($result === FALSE) {
						if ($doDebug) {
							echo "resolve_reminder failed<br />";
						}
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "No student record ... should never happen<br />";
			}
			$content .= "FATAL ERROR. Should never happen!";
		}


////////// pass 90


	} elseif ("90" == $strPass) {			/// enter validation information to update sign up record
		if ($doDebug) {
			echo "<br />Arrived at pass 90 with inp_callsign: $inp_callsign<br />";
			if ($haveStudentData) {
				echo "haveStudentData is TRUE<br />";
			} else {
				echo "haveStudentData is FALSE<br />";
			}
			if ($haveMasterData) {
				echo "haveMasterData is TRUE<br />";
			} else {
				echo "haveMasterData is FALSE<br />";
			}
			if ($haveInpSemester) {
				echo "haveInpSemester is TRUE<br />";
			} else {
				echo "haveInpSemester is FALSE<br />";
			}
			if ($allowSignup) {
				echo "allowSignup is TRUE<br />";
			} else {
				echo "allowSignup is FALSE<br />";
			}
			echo "inp_level: $inp_level<br />
				  inp_semester: $inp_semester<br />
			      allowSignup: $allowSignup<br />
			      inp_verify: $inp_verify<br />";
		}

		if ($userRole == 'administrator' && $fakeIt == 'Y') {
			$fakeIt			= 'N';
			$content		.= "<h3>FAKE IT</h3>
								Enter the student callsign you are faking<br />
								<form method='post' action='$theURL' 
								name='fakingform' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='90'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<input type='hidden' name='token' value='$token'>
								<input type='hidden' name='timezone' value=''>
								<input type='hidden' name='fakeIt' value='$fakeIt'>
								<table style='border-collapse:collapse;'>
								<tr><td>Student Callsign</td>
									<td><input type='text' class='formInputText' name='inp_callsign' size='15' maxlength='30'></td></tr>
								$testModeOption<br />
								<tr><td colspan='2'>'<input class='formInputButton' type='submit' value='Next' /></td></tr></table>
								</form>";
			
		} else {
			if ($inp_callsign == '') {
				$inp_callsign			= strtoupper($userName);
			}
			$content			.= "<h3>$jobname</h3>
									<h4>Modify $inp_callsign Registration Information</h4>";
			if ($haveStudentData) {
			
				// remove student_response of 'R' if present
				if ($student_response == 'R') {
					if ($doDebug) {
						echo "have a student_response of 'R'. Resetting to blank<br />";
					}
					$actionDate			= date('dMy H:i');
					$student_action_log	.= " / $actionDate STDREG $student_call_sign removed student_response of R ";
					$updateParams		= array('student_action_log'=>$student_action_log,
												'student_status'=>'');
					$updateResult = $student_dal->update($student_id,$updateParams,$operatingMode);
					if ($updateResult === FALSE) {
						$content		.= "Unable to update content<br />";
					} else {
						if ($doDebug) {
							echo "response of R removed from $student_call_sign<br />";
						}
					}
				}
			
			
				$content			.= "<p>You are signed-up as follows:
										<table style='width:900px;'>
										<tr><td><b>Callsign<br />$student_call_sign</b></td>
											<td><b>Name</b><br />$user_last_name, $user_first_name</td>
											<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
											<td><b>Email</b><br />$user_email</td></tr>
										<tr><td><b>City</b><br />$user_city</td>
											<td><b>State</b><br />$user_state</td>
											<td><b>Zip Code</b><br />$user_zip_code</td>
											<td><b>Country</b><br />$user_country</td></tr>
										<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
											<td><b>Telegram</b><br />$user_telegram</td>
											<td><b>Signal</b><br />$user_signal</td>
											<td><b>Messenger</b><br />$user_messenger</td></tr>
										<tr><td><b>Level: </b>$student_level</td>
											<td><b>Language: </b>$student_class_language</td>
											<td colspan='2'><b>Semester: </b>$student_semester</td></tr>";
				if ($student_no_catalog == 'Y') {
					$content	.= "<tr><td colspan='5'><b>Class Preferences</b><br />";
					if ($student_flexible == 'Y') {
						$content	.= "My time is flexible</td></tr>";
					} else {
						$myArray	= explode(",",$student_catalog_options);
						foreach($myArray as $thisData) {
							$myStr		= $catalogOptions[$thisData];
							$content	.= "$myStr<br />";
						}
						$content		.= "</td></tr>";
					}
				} else {
					$content		.= "<tr><td><b>First Class Choice</b><br />$student_first_class_choice</td>
											<td><b>Second Class Choice</b><br />$student_second_class_choice</td>
											<td><b>Third Class Choice</b><br />$student_third_class_choice</td>
											<td>All times local time</td></tr>";
				}
				if ($student_youth == 'Yes') {
					$content	.= "<tr><td style='text-align:center;'>Youth<br />$student_youth</td>
										<td style='text-align:center;'>Age<br />$student_age</td>
										<td>Parent / Guardian<br />$student_student_parent</td>
										<td>Parent / Guardian Email<br />$student_student_parent_email</td></tr>";
				}
				$content		.= "</table></p>
									<p>If you wish to update or cancel this registration, please click the 
									'Next' button.</p>
									<form method='post' action='$theURL' 
									name='validationform' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='firsttime' value='first'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<input type='hidden' name='token' value='$token'>
									<input type='hidden' name='timezone' value=''>
									<input type='hidden' name='inp_callsign' value='$inp_callsign'>
									<table style='border-collapse:collapse;'>
									$testModeOption<br />
									<tr><td colspan='2'>'<input class='formInputButton' type='submit' value='Next' /></td></tr></table>
									</form>
									<p>If you are having difficulty gaining access to your registration information, please contact the 
									appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class Resolution</a>.</p>";
			} else {
				$content		.= "<p>No signup record found for $inp_callsign. Have you signed up?
									If you are having difficulty gaining access to your registration information, please contact the 
									appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class Resolution</a>.</p>";
			}
		}
	}

///////////////// pass 100

	if ("100" == $strPass) {

		if ($doDebug) {
			echo "<br />Arrived at pass 100<br />";
			if ($haveStudentData) {
				echo "haveStudentData is TRUE<br />";
			} else {
				echo "haveStudentData is FALSE<br />";
			}
			if ($haveMasterData) {
				echo "haveMasterData is TRUE<br />";
			} else {
				echo "haveMasterData is FALSE<br />";
			}
			if ($haveInpSemester) {
				echo "haveInpSemester is TRUE<br />";
			} else {
				echo "haveInpSemester is FALSE<br />";
			}
			if ($allowSignup) {
				echo "allowSignup is TRUE<br />";
			} else {
				echo "allowSignup is FALSE<br />";
			}
		}

		if ($doDebug)  {
			echo "would go on to error if there was a student record. 
					otherwise would setup for an assessment<br />";
		}


		$userName			= strtoupper($userName);
		$doProceed			= TRUE;
		$content			.= "<h3>$jobname</h3>
								<h4>Signup Information</h4>";
		
		if ($haveStudentData) {		// shouldn't be here
			$content			.= "<p>It seems you already have signed up for a $student_level 
									Level class in the $student_semester semester. If you want 
									to modify this record, please click 
									<a href='$siteURL/cwa-student-registration/'>Student Sign Up</a> 
									and select the option to Modify Signup Information.</p>";
			$doProceed			= FALSE;
		} else {
			if ($doDebug) {
				echo "no signup record found<br />";
			}
		}
		if ($doProceed) {
			$semesterSelection	= '';
			if ($daysToGo == 0) {
				$daysToGo			= days_to_semester($nextSemester);
			}

			if ($doDebug) {
				echo "daysToGo: $daysToGo<br />
						nextSemester: $nextSemester<br />
						semesterTwo: $semesterTwo<br />
						semesterThree: $semesterThree<br />";
			}
			if ($currentSemester == 'Not in Session' && $daysToGo > 10) {
				$semesterList	= "<input type='radio' class='formInputButton' name='inp_semester' id='chk_semester' value='$nextSemester' checked required > $nextSemester<br />
								   <input type='radio' class='formInputButton' name='inp_semester' id='chk_semester' value='$semesterTwo' required > $semesterTwo<br />
								   <input type='radio' class='formInputButton' name='inp_semester' id='chk_semester' value='$semesterThree' required > $semesterThree</td>";
				if ($doDebug) {
					echo "Semester not in session and more than 10 days to nextSemester. showing $nextSemester and $semesterTwo<br />";
				}
			} elseif ($currentSemester == 'Not in Session' && $daysToGo <= 10) {
				$semesterList	= "<input type='radio' class='formInputButton' name='inp_semester' value='$nextSemester' checked required > $nextSemester (<b>Waiting List</b>)<br />
								  <input type='radio' class='formInputButton' name='inp_semester' id='chk_semester' value='$semesterTwo'  required > $semesterTwo<br />
								  <input type='radio' class='formInputButton' name='inp_semester' id='chk_semester' value='$semesterThree' required > $semesterThree</td>";
				if ($doDebug) {
					echo "Semester not in session and equal or less than 10 days to nestSemester. Showing $nextSemester, $semesterTwo, and $semesterThree<br />";
				}
			} else {
				$semesterList	= "<input type='radio' class='formInputButton' name='inp_semester' id='chk_semester' value='$nextSemester' checked required > $nextSemester<br />
							   <input type='radio' class='formInputButton' name='inp_semester' id='chk_semester' value='$semesterTwo' required > $semesterTwo</td>";
				if ($doDebug) {
					echo "Semester in session and not in validReplacementPeriod. showing $nextSemester and $semesterTwo<br />";
				}
			}			
			$semesterSelection	= "<tr><td style='vertical-align:top;'><b>Semester</b></td>
								   <td>$semesterList</td></tr>";

			// get the user_master data
			if (!$haveMasterData) {
				// this is a problem. There should always be a user_master
				$content .= "<p>FATAL ERROR. SysAdmin has been notified<br />";
				error_log("$jobname Pass 100 FATAL ERROR There should be a user_master record");
				sendErrorEmail("$jobname Pass 100 FATAL ERROR There should be a user_master record");
			} else {
				//Build language selection
				$languageOptions			= '';
				$firstTime					= TRUE;
				$englishChecked				= '';
				foreach($languageArray as $thisLanguage) {
					if ($thisLanguage == 'English') {
						$englishChecked		= 'checked';
					} else {
						$englishChecked		= '';
					}
					if ($firstTime) {
						$firstTime			= FALSE;
						$languageOptions		.= "<input type='radio' class='formInputButton' name='inp_class_language' value='$thisLanguage' $englishChecked required>$thisLanguage";
					} else {
						$languageOptions		.= "<br /><input type='radio' class='formInputButton' name='inp_class_language' value='$thisLanguage' $englishChecked required>$thisLanguage";
					}
				}
		
				$content				.= "<h4>Sign Up for a Class at CW Academy</h4>
											<p>Ready to boost your Morse Code proficiency? CW Academy offers free, volunteer-led classes to help you achieve your goals.</p>
											<h4>How to Sign Up (4 Easy Steps)</h4>
											<p>The sign-up process is straightforward. You'll need to complete all four steps to secure your spot in a class:
											<ol>
											<li><b>Choose Your Class, Preferred Class Language, and Semester:</b> Select the specific class you want to attend, your preferred class language, and the semester that works best for you.
											<li><b>Take a Morse Code Proficiency Assessment:</b> This short process will help verify which of the four class levels you should take. Note: 
													if you have taken a proficiency assessment in the previous 10 days and scored 60% or higher, you will not need to repeat the assessment.  
											<li><b>Provide Parent/Guardian Information (if applicable):</b> If you're under 21, you'll need to provide information for a parent or guardian.
											<li><b>Select Your Availability:</b> Finally, you'll pick your preferred class times and days from a list of available options.
											</ol></p>
											<h4>CW Academy Class Levels</h4>
											<p>We offer four levels of classes, tailored to your current Morse Code (CW) proficiency. Please select the level that best matches your skills:
											<ul>
											<li><b>Beginner:</b> Perfect for those with little to no prior Morse code knowledge.
											<li><b>Fundamental:</b> For students who have learned the Morse code characters, can send and receive at about 6 words per minute (WPM), and aim to reach 12 WPM.
											<li><b>Intermediate:</b> Designed for those capable of sending and receiving at a minimum of 10 WPM, looking to increase their speed to about 20 WPM.
											<li><b>Advanced:</b> For experienced operators who can send and receive at a minimum of 20 WPM and aspire to reach 30 WPM.
											</ul></p>
											<h4>CW Academy Class Languages</h4>
											<p>Most CW Academy classes are taught in English. A few classes are available in other languages. You can select your preferred language 
											and if there is a class available in that language that meets your availability, you will be assigned to that class. Otherwise, you will 
											be assigned to a class taught in English.</p>
											<h4>Important Class Information</h4>
											<ul>
											<li><b>Free of Charge:</b> All CW Academy classes are completely free, taught by passionate volunteer advisors and staff.
											<li><b>Class Schedule:</b> Classes run for eight weeks during three main periods: January-February, May-June, and September-October.
											<li><b>Time Commitment:</b> Classes meet twice a week for approximately one hour. You'll also have daily homework assignments, taking up to an hour to complete between sessions.
											<li><b>Language Options:</b> Most classes are taught in English, but some are available in other languages. Be sure to check the class language when making your time selections.
											<li><b>One Registration at a Time:</b> Students can only be registered for one class at a time. Once you've completed a class, you're welcome to register for another!
											</ul>
											<h4>Minimum Essentials for Participation</h4>
											<p>To ensure a successful learning experience, please make sure you have the following:
											<ul>
											<li><b>High-speed broadband internet access</b>
											<li><b>Computing device</b> (desktop or laptop. Tablet or SmartPhone strongly discouraged)
											<li><b>Webcam</b> (built-in or USB add-on, with camera and microphone)
											<li><b>Key paddle</b> (single or dual lever; straight keys or bugs are not permitted)
											<li><b>Keyer with sidetone</b> or a radio with a built-in keyer and sidetone
											<li><b>Dedication:</b> Be prepared for up to 60 minutes of daily practice!
											</ul></p>
											<h4>Step 1 of 4 Steps</h4>
											<p>Select the specific class you want to attend and the semester that works best for you 
											and click 'Next'.</p> 
											<form method='post' action='$theURL' 
											name='pass01form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='101'>
											<input type='hidden' name='firsttime' value=''>
											<input type='hidden' name='thisOption' value='$thisOption'>
											<input type='hidden' name='token' value='$token'>
											<input type='hidden' name='timezone' value='' >
											<input type='hidden' name='inp_callsign' value='$userName'>
											<table style='border-collapse:collapse;'>
											<tr><td style='width:150px;vertical-align:top;'><b>Call Sign</b></td>
												<td>$userName</td></tr>
											<tr><td><b>Name</b></td>
												<td>$user_last_name, $user_first_name</td></tr>
											<tr><td><b>Email Address</b></td>
												<td>$user_email</td></tr>
											<tr><td colspan='2'><b>If any of this information is incorrect,</b> go back to 
												your Student Portal, update your information, and then start the sign-up</td></tr>
											<tr><td style='vertical-align:top;'><b>What Class Level Do You Want?</b></td>
												<td><input type='radio' class='formInputText' name='inp_level' id='chk_level' value='Beginner' required > Beginner<br />
													<input type='radio' class='formInputText' name='inp_level' id='chk_level' value='Fundamental' required > Fundamental<br />
													<input type='radio' class='formInputText' name='inp_level' id='chk_level' value='Intermediate' required > Intermediate<br />
													<input type='radio' class='formInputText' name='inp_level' id='chk_level' value='Advanced' required > Advanced</td></tr>
											<tr><td style='vertical-align:top;'><b>Your preferred Class Language</b></td>
												<td>$languageOptions</td>
											$semesterSelection
											$testModeOption<br />
											<tr><td colspan='2'>'<input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Next' /></td></tr></table>
											</form>";
			}
		}
	}
		
/////////////////////////	PASS 101

	if ("101" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 101 with inp_callsign of $inp_callsign<br />
				inp_level of $inp_level<br />
				inp_semester of $inp_semester<br />
				inp_doAgain of $inp_doAgain<br />";
			if ($haveStudentData) {
				echo "haveStudentData is TRUE<br />";
			} else {
				echo "haveStudentData is FALSE<br />";
			}
			if ($haveMasterData) {
				echo "haveMasterData is TRUE<br />";
			} else {
				echo "haveMasterData is FALSE<br />";
			}
			if ($haveInpSemester) {
				echo "haveInpSemester is TRUE<br />";
			} else {
				echo "haveInpSemester is FALSE<br />";
			}
			if ($allowSignup) {
				echo "allowSignup is TRUE<br />";
			} else {
				echo "allowSignup is FALSE<br />";
			}
		}
		
		if ($inp_doAgain == 'Y' and $submit == 'Switch to Intermediate') {
			$inp_level					= "Intermediate";
		}
		$enstr					= '';
		$doProceed				= TRUE;
		$allowSignup			= FALSE;
		$token					= mt_rand();
		$lastLevel		= "";
		$lastPromotable	= "";
		$lastSemester	= "";
		// get the last class 
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				['field' => 'student_call_sign', 'value' => $inp_callsign, 'compare' => '=' ]
			]
		];
		$wpw1_cwa_student = $student_dal->get_student($criteria,'student_date_created','DESC',$operatingMode);
		if ($wpw1_cwa_student === FALSE) {
			$content		.= "Unable to obtain content from $studentTableName<br />";
		} else {
			$myFirst =TRUE;
			$haveLastClass = FALSE;
			foreach ($wpw1_cwa_student as $studentRow) {
				foreach($studentRow as $thisField => $thisValue) {
					$thisField .= "_fix";
					$$thisField = $thisValue;
				}
				$haveLastClass = TRUE;
				if ($myFirst) {
					$myFirst = FALSE;
					break;
				}
			}
			if ($haveLastClass) {
				if ($doDebug) {
					echo "found Level: $student_level_fix, Promotable: $student_promotable_fix<br />
							Response: $student_response_fix, Status: $student_student_status_fix<br />";
				}
										
				if ($student_response_fix != 'R') {
					if ($student_status_fix == 'Y' || $student_status_fix == 'S') {
						if ($student_promotable_fix == '') {
							$content	.= "<h3>Student Sign-up for $inp_callsign</h3>
											<p>If you are trying to register for the $nextSemester semester, your advisor needs to 
											complete your end-of-semester evaluation before you can register.</p>";
							$doProceed		= FALSE;
							$allowSignup	= FALSE;
							if ($doDebug) {
								echo "doProceed and allowSignup set to FALSE as end-of-semester evaluation not yet done<br />";
							}
						} else {
							$lastLevel		= $student_level_fix;
							$lastPromotable	= $student_promotable_fix;
							$lastSemester	= $student_semester_fix;
							if ($doDebug) {
								echo "Set lastLevel to $lastLevel, lastPromotable to $lastPromotable, and lastSemester to $lastSemester<br />";
							}
						}
					} 
				}
			}
		}
		if ($doProceed) {
		
			// Do the fundamental check
			if ($inp_level == 'Fundamental' and $lastLevel == 'Fundamental') {
				if ($lastPromotable == 'P') {
					$content		.= "<h3>$jobname</h3>
										<p><form method='post' action='$theURL' 
										name='newstudentform' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='3'>
										<input type='hidden' name='inp_doAgain' value='Y'>
										<input type='hidden' name='demonstration' value='$demonstration'>
										<input type='hidden' name='inp_semester' value='$inp_semester'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='inp_callsign' value='$inp_callsign'>
										<input type='hidden' name='waitingList' value='$waitingList'>
										<input type='hidden' name='inp_level' value='$inp_level'>
										<input type='hidden' name='token' value='$token'>
										<input type='hidden' id='browser_timezone_id' name='browser_timezone_id' value='$browser_timezone_id' />
										<h4>Fundamental Signup</h4>
										<p>According to our records, you have already successfully taken the CW Academy 
										Fundamental Level Class in the $lastSemester semester.</p>
										<p>As you are trying to retake this course again ... we would  like you to 
										consider a few important items to  help you move along in your Morse code training:</p>
										<ul><li>The Fundamental level class is not 'advanced' Beginner. It was designed to be 
										the 'preparatory' course for the  Intermediate Level class
											<li>The Intermediate level class should be the next logical step in your progress
											<li>The sooner you take it, the more comfortable you will be 'getting on the air'
											<li>The requirements to  take Intermediate should not be intimidating
											<ul><li>Be able to  copy 'most' letters at around 10 words per minute
												<li>'Head Copy' skills will be taught in the Intermediate course as the semester progresses
												<li>The majority of the students that sign up for the Intermediate class successfully complete the course
												<li>The advisor generally makes accommodations for the skill level of the students in the class
											</ul>
										</ul>
										<p>If you  still have concerns and would  like to  discuss your options, please 
										contact someone at:<br />
										<a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA Class Resolution</a></p>
										<p>After all these annoying suggestions, if  you still want to take the Fundamental level class, 
										please feel free to do so. Click <br />
										<input type='submit' class='formInputButton' name='submit' value='Continue to  Sign up for Fundamental'>.<p>
										<p>If, however, you are willing to switch to an Intermediate class, please click <br />
										<input type='submit' class='formInputButton' name='submit' value='Switch to Intermediate'>.</p>
										</form>";
				
				}
			} 	// continuing with fundamental comes here
	
			/// student doing a signup. Setup to carry info forward
			$allowSignup				= TRUE;
			$stringToPass 				= "inp_callsign=$inp_callsign&inp_semester=$inp_semester&inp_level=$inp_level&inp_mode=$inp_mode&inp_verbose=$inp_verbose&thisOption=$thisOption&firsttime=$firsttime&timezone=$timezone&allowSignup=$allowSignup&inp_class_language=$inp_class_language";
			$enstr						= base64_encode($stringToPass);
			if ($doDebug) {
				echo "enstr encoded from: $stringToPass<br />";
			}
			
			$levelDown					= array('Advanced' => 'Intermediate', 
												'Intermediate' => 'Fundamental', 
												'Fundamental' => 'Beginner', 
												'Beginner' => 'Beginner');
			$lookLevel					= $levelDown[$inp_level];

			$needsAssessment			= TRUE;
/*
			// see if student has previous class in the last semester
			if ($doDebug) {
				echo "checking to see if needs assessment<br />
					   lastLevel: $lastLevel (if blank, must take assessment)<br />
					   if lastLevel of $lastLevel equal to lookLevel of $lookLevel OR<br />
					   lastLevel of $lastLevel equal to inp_level of $inp_level THEN<br />
					   Check semester<br />
					   Comparing $lastSemester to $prevSemester. If same, check promotable<br />
					   lastPromotable: $lastPromotable if P, no assessment needed<br />";
			}
			if ($lastLevel != '') {		// there is a previous class
				if ($doDebug) {
					echo "lastLevel of $lastLevel is not blank<br />";
				}
				if ($student_level == $lookLevel || $lastLevel == $inp_level) {	// Levels ok, check semester
					if ($doDebug) {
						echo "student_level of $student_level is equal to lookLevel of $lookLevel<br />
							  or lastLevel of $lastLevel is equal to $inp_level<br />";
					}
					if ($lastSemester == $prevSemester) {						// semester ok. Promotable?
						if ($doDebug) {
							echo "lastSemester of $lastSemester equal to prevSemester of $prevSemester. Check promotable<br />";
						}
						$needsAssessment	= FALSE;
						if ($doDebug) {
							echo "did not check promotable, set needsAssessment to FALSE<br />";
						}
					}
				}				
			}
*/			
			
			if ($userRole == 'administrator') {
				$needsAssessment = FALSE;
			}
			if ($needsAssessment) {					// see if there is an assessment in the last 60 days
				if ($doDebug) {
					echo "needsAssessment is TRUE. Looking for previous assessments<br />";
				}
				$last10Days				= strtotime("-10 days");
				$last10Date				= date('Y-m-d 00:00:00',$last10Days);
				
				$sql					= "select score from wpw1_cwa_new_assessment_data 
											where callsign = '$inp_callsign' 
												and level = '$inp_level' 
												and date_written >= '$last10Date'";
				$thisScore				= $wpdb->get_results($sql);
				if ($thisScore === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numRows			= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $numRows rows<br />";
					}
					if ($numRows > 0) {
						$maxScore		= 0;
						foreach($thisScore as $scoreRow) {
							$scoreValue		= $scoreRow->score;
							if ($scoreValue > $maxScore) {
								$maxScore	= $scoreValue;
							}
						}
						if ($maxScore >= 60) {
							$needsAssessment	= FALSE;
							$doProceed			= TRUE;
							if ($doDebug) {
								echo "have recent assessment. Bypassing take assessment<br />";
								$inp_verbose	= 'Y';
							}
							$token				= 99999;
							goto bypassAssessment;

						}
						if ($inp_level == 'Beginner') {
							$needsAssessment	= FALSE;
						}
					}
				}
			}
			if ($skipAssessment) {
				$needsAssessment 	= FALSE;
			}

			// if needs assessment and beginner allow skipping the assessment
			if ($needsAssessment and $inp_level == 'Beginner') {
				$content			.= "<h3>$jobname</h3>
										<h4>Step 2 of 4 Steps</h4>
										<p>At this point in the signup process you will be asked 
										to take a Morse code proficiency assessment to help 
										you get into the right class based on your knowledge 
										of Morse code. You have requested a Beginner Level class, 
										meaning you do not know any Morse code. The Fundamental 
										Level class is intended for amateur radio operators with 
										some Morse code ability. 
										If you would like to try the proficiency assessment to 
										see if you qualify for a Fundamental Level class, 
										select the 'Continue to Proficiency Assessment
										 button 
										below. Otherwise, select the 'Skip Proficiency Assessment' 
										button.</p>
										<table style='width:auto;'>
										<tr><td><form method='post' action='$theURL' 
												name='pass101aform' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='101a'>
												<input type='hidden' name='inp_callsign' value='$inp_callsign'>
												<input type='hidden' name='inp_semester' value='$inp_semester'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<input type='hidden' name='inp_verbose' value='$inp_verbose'>
												<input type='hidden' name='thisOption' value='$thisOption'>
												<input type='hidden' name='firsttime' value='$firsttime'>
												<input type='hidden' name='timezone' value='$timezone'>
												<input type='hidden' name='allowSignup' value='$allowSignup'>
												<input type='hidden' name='inp_level' value='$inp_level'>
												<input type='hidden' name='inp_class_language' value='$inp_class_language'>
												<input type='hidden' name='needsAssessment' value='1'>
												<input type='submit' class='formInputButton' name='proficiency' value='Continue to Proficiency Assessment'>
												</form></td>
											<td><form method='post' action='$theURL' 
												name='pass101bform' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='2'>
												<input type='hidden' name='inp_callsign' value='$inp_callsign'>
												<input type='hidden' name='inp_semester' value='$inp_semester'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<input type='hidden' name='inp_verbose' value='$inp_verbose'>
												<input type='hidden' name='thisOption' value='$thisOption'>
												<input type='hidden' name='firsttime' value='$firsttime'>
												<input type='hidden' name='timezone' value='$timezone'>
												<input type='hidden' name='allowSignup' value='$allowSignup'>
												<input type='hidden' name='inp_level' value='$inp_level'>
												<input type='hidden' name='inp_class_language' value='$inp_class_language'>
												<input type='submit' class='formInputButton' name='noproficiency' value='Skip Proficiency Assessment'>
												</form></td></tr></table>";
			} else {
				$strPass			= "101a";
			}
		}
	}
	if ("101a" == $strPass) {

		if ($needsAssessment) {	
			$token			= mt_rand();			
			if ($doDebug) {
				echo "Doing the assessment.<br />
						doProceed is TRUE<br />
						allowSignup: $allowSignup<br />";
			}
			$needsAssessment = FALSE;
			$stringToPass 	= "inp_method=studentreg&inp_callsign=$inp_callsign&inp_phone=$user_phone&inp_ph_code=$inp_ph_code&inp_email=$user_email&inp_semester=$inp_semester&inp_mode=$inp_mode&inp_verbose=$inp_verbose&thisOption=$thisOption&firsttime=$firsttime&timezone=$timezone&allowSignup=$allowSignup&inp_level=$inp_level&inp_class_language=$inp_class_language";
			$enstr			= base64_encode($stringToPass);
			$thisDate		= date('Y-m-d H:i:s');
			// save the enstr info in the temporary table
			$insertResult	= $wpdb->insert('wpw1_cwa_temp_data',
											array('callsign'=>$inp_callsign,
												  'token'=>$token,
												  'temp_data'=>$enstr,
												  'date_written'=>$thisDate),
											array('%s','%s','%s','%s'));
			if ($insertResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			}
				$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}

			
			$doProceed		= FALSE;
			$myStr			= "$theURL?strpass=104&inp_callsign=$inp_callsign&token=$token";
			$returnurl		= urlencode($myStr);
			$content		.= "<h3>$jobname</h3>
								<h4>Step 2 of 4 Steps: Morse Code Proficiency Self-assessment</h4>
								<p>The next step in the sign-up process is to do a Morse Code 
								Proficiency Assessment. The purpose is to assist you to sign up 
								for the class most suitable for you. You have indicated that your are 
								interested in a $inp_level Level CW Academy Class. The assessment will 
								begin at that level and, depending on your result, may ask you to take 
								a higher-level or lower-level assessment. </p>
								<p>Upon completion, you will come back to the sign-up program, which 
								will show you your sign-up options.</p>
								<p><table style='border:4px solid green;width:auto;'><tr><td>
								Click <a href='https://cw-assessment.vercel.app?mode=$inp_level&callsign=$inp_callsign&token=$token&infor=Registration&returnurl=$returnurl'>HERE</a> 
								to start the assessment.</p></td></tr></table>"; 

		} else {
			if ($doProceed) {
				if (!$needsAssessment) {			/// doesn't need assessment. Continue to signup
					$stringToPass 	= "inp_callsign=$inp_callsign&inp_phone=$inp_phone&inp_ph_code=$inp_ph_code&inp_email=$inp_email&inp_semester=$inp_semester&inp_mode=$inp_mode&inp_verbose=$inp_verbose&thisOption=$thisOption&firsttime=$firsttime&timezone=$timezone&allowSignup=$allowSignup&inp_level=$inp_level&inp_class_language=$inp_class_language";
					$enstr			= base64_encode($stringToPass);
					$content		.= "<h3>$jobname</h3>
										<p><table style='border:4px solid green;width:auto;'><tr><td>You have recently successfly met the Morse code proficiency requirements 
										to take the CW Academy $inp_level level class.<br /><br />
										Click <a href='$theURL?strpass=2&enstr=$enstr'>HERE</a> to continue the sign-up process.</p></td></tr></table>";			
				}
			}
		}			
	}
////////// Pass 104

	if ("104" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 104 with<br />
			  inp_callsign: $inp_callsign<br />
			  token: $token<br />";
		}
		$doProceed		= TRUE;
		$content		.= "<h3>$jobname</h3>";
		// get the info from temp_data and decode
		$sql			= "select * from wpw1_cwa_temp_data 
							where token = '$token' and 
							callsign = '$inp_callsign'";
		$tempResult		= $wpdb->get_results($sql);
		if ($tempResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content	.= "A fatal program error has occured";
			return $content;
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}
			$numRows 	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($tempResult as $tempRow) {
					$record_id		= $tempRow->record_id;
					$temp_callsign	= $tempRow->callsign;
					$temp_token		= $tempRow->token;
					$temp_data		= $tempRow->temp_data;
					$temp_date		= $tempRow->date_written;
				}
				$theseParams		= base64_decode($temp_data);
				if ($doDebug) {
					echo "temp data of $temp_data<br />decoded as $theseParams<br />";
				}
				$thisArray			= explode("&",$theseParams);
				foreach($thisArray as $thisValue) {
					if ($doDebug) {
						echo "processing $thisValue<br />";
					}
					$myArray		= explode("=",$thisValue);
					$myField		=  $myArray[0];
					$myData			= $myArray[1];
					$$myField		= $myData;
				}
				if ($inp_verbose == 'Y') {
					$doDebug		= TRUE;
				}
				if ($inp_mode == 'TESTMODE') {
					$testMode		= TRUE;
				}
				if ($doDebug) {
					echo "enstr decoded data:<br />
						  inp_callsign: $inp_callsign<br />
						  inp_semester: $inp_semester<br />
						  inp_mode: $inp_mode<br />
						  inp_verbose: $inp_verbose<br />
						  thisOption: $thisOption<br />
						  firsttime: $firsttime<br />
						  timezone: $timezone<br />
						  allowSignup: $allowSignup<br />
						  inp_level: $inp_level<br />
						  inp_class_language: $inp_class_language<br />";
				}

				// now delete the temp_data record
				$tempDelete			= $wpdb->delete('wpw1_cwa_temp_data',
													array('record_id'=>$record_id),
													array('%d'));
				if ($tempDelete === FALSE) {
					handleWPDBError($jobname,$doDebug);
					$content		.= "Fatal program error";
					$doProceed		= FALSE;
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						if (!$doDebug) {
							return $content;
						}
					}

					if ($doDebug) {
						echo "record_id $record_id deleted from wpw1_cwa_temp_data<br />";
					}
				}
				
			} else {
				if ($doDebug) {
					echo "getting data from wpw1_cwa_temp_data for callsign: $inp_callsign and token: $token failed. No rows found<br />";
				}
				$content			.= "Fatal programming error";
				$doProceed			= FALSE;
			}
		}
////////////////// come here if didn't need to do an assessment
		bypassAssessment:
		if ($doDebug) {
			echo "<br />at bypassAssessment:<br />
				  inp_callsign: $inp_callsign<br />
				  inp_semester: $inp_semester<br />
				  inp_mode: $inp_mode<br />
				  inp_verbose: $inp_verbose<br />
				  thisOption: $thisOption<br />
				  firsttime: $firsttime<br />
				  timezone: $timezone<br />
				  allowSignup: $allowSignup<br />
				  inp_level: $inp_level<br />
				  inp_class_language: $inp_class_language<br />";
		}
		
		if ($doProceed) {
			// check to see what is in the new assessment table for this student
			$bestResultBeginner		= 0;
			$didBeginner			= FALSE;
			$bestResultFundamental	= 0;
			$didFundamental			= FALSE;
			$bestResultIntermediate	= 0;
			$didIntermediate		= FALSE;
			$bestResultAdvanced		= 0;
			$didAdvanced			= FALSE;
//			if (!$dockerMode) {			// if running in docker, no results will be available
				if ($token != '99999') {
					$sql					= "select * from $newAssessmentTableName 
												where callsign = '$inp_callsign' 
												and token = '$token'";
				} else {
					$sql					= "select * from $newAssessmentTableName 
												where callsign = '$inp_callsign' 
												and date_written >= '$last10Date'";
				}
				$assessmentResult		= $wpdb->get_results($sql);
				if ($assessmentResult === FALSE) {
					handleWPDBError("$jobname pass 104",$doDebug);
				} else {
					$numASRows		= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $numASRows rows<br />";
					}
					if ($numASRows > 0) {
						foreach($assessmentResult as $assessmentRow) {
							$assessment_id		 	= $assessmentRow->record_id;
							$assessment_callsign	= $assessmentRow->callsign;
							$assessment_level		= $assessmentRow->level;
							$assessment_score		= $assessmentRow->score;
							$assessment_date		= $assessmentRow->date_written;
							
							if ($assessment_level == 'Beginner') {
								$didBeginner		= TRUE;
								if ($assessment_score > $bestResultBeginner) {
									$bestResultBeginner = $assessment_score;
								}
							} elseif ($assessment_level == 'Fundamental') {
								$didFundamental		= TRUE;
								if ($assessment_score > $bestResultFundamental) {
									$bestResultFundamental = $assessment_score;
								}
							} elseif ($assessment_level == 'Intermediate') {
								$didIntermediate		= TRUE;
								if ($assessment_score > $bestResultIntermediate) {
									$bestResultIntermediate = $assessment_score;
								}
							} elseif ($assessment_level == 'Advanced') {
								$didAdvanced		= TRUE;
								if ($assessment_score > $bestResultAdvanced) {
									$bestResultAdvanced = $assessment_score;
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "no assessment records found for token $token<br />";
						}
						$content		.= "<p>Thank you for your interest in CW Academy. If you 
											wish to start a new sign-up, please click 
											<a href='$theURL'>Sign Up</a>. Otherwise you can 
											close this window.</p>";
						$doProceed		= FALSE;
					}
				}
//			} else {					// in dockerMode. fake the data
//				$assessment_level		= $inp_level;
//				$assessment_score		= 100;
//				$bestResultBeginner		= 0;
//				$bestResultFundamental	= 0;
//				$bestResultIntermediate	= 0;
//				$bestResultAdvanced		= 0;
//			}
			if ($doProceed) {	
				if ($doDebug) {
					echo "have assessment data:<br />";
					if ($didBeginner) {
						echo "bestResultBeginner; $bestResultBeginner<br />";
					} else {
						echo "no Beginner assessment<br />";
					}
					if ($didFundamental) {
						echo "bestResultFundamental: $bestResultFundamental<br />";
					} else {
						echo "no Fundamental assessment<br />";
					}
					if ($didIntermediate) {
						echo "bestResultIntermediate: $bestResultIntermediate<br />";
					} else {
						echo "no Intermediate assessment<br />";
					}
					if ($didAdvanced) {
						echo "bestResultAdvanced: $bestResultAdvanced<br />";
					} else {
						echo "no Advanced assessment<br />";
					}
				}
				$content		.= "<p>You have completed the Morse Code Proficiency 
									assessment.<br />";
				if ($didBeginner) {
					$content	.= "Your Beginner Level assessment score was $bestResultBeginner<br />";
				}
				if ($didFundamental) {
					$content	.= "Your Fundamental Level assessment score was $bestResultFundamental<br />";
				}
				if ($didIntermediate) {
					$content	.= "Your Intermediate Level assessment score was $bestResultIntermediate<br />";
				}
				if ($didAdvanced) {
					$content	.= "Your Advanced Level assessment score was $bestResultAdvanced<br />";
				}
				$content		.= "</p>";
		
				$stringToPass 	= "inp_callsign=$inp_callsign&inp_semester=$inp_semester&$inp_level=$inp_level&inp_mode=$inp_mode&inp_verbose=$inp_verbose&thisOption=$thisOption&firsttime=$firsttime&allowSignup=$allowSignup&inp_class_language=$inp_class_language";
				$enstr			= base64_encode($stringToPass);
				if ($doDebug) {
					echo "set up enstr using $stringToPass<br />";
				}
		
				$registerAsBeginner				= "<p><table style='border:4px solid green;width:auto;'><tr><td>
													To continue signing up for a Beginner Level class please click 
													<a href='$theURL?strpass=2&inp_level=Beginner&enstr=$enstr'>Sign-Up for a Beginner Class</a>
													</td></tr></table></p>";
		
				$registerAsFundamental		 	= "<p><table style='border:4px solid green;width:auto;'><tr><td>
													To continue signing up for a Fundamental Level class please click
													<a href='$theURL?strpass=2&inp_level=Fundamental&enstr=$enstr'>Sign-Up for a Fundamental Level class</a>
													</td></tr></table></p>";
		
				$registerAsIntermediate			= "<p><table style='border:4px solid green;width:auto;'><tr><td>
													To continue signing up for an Intermediate Level class please click
													<a href='$theURL?strpass=2&inp_level=Intermediate&enstr=$enstr'>Sign-Up for an Intermediate Level class</a>
													</td></tr></table></p>";
		
				$registerAsAdvanced				= "<p><table style='border:4px solid green;width:auto;'><tr><td>
													To continue signing up for an Advanced Level class please click
													<a href='$theURL?strpass=2&inp_level=Advanced&enstr=$enstr'>Sign-Up for an Advanced Level class</a>
													</td></tr></table></p>";
		
				if ($doProceed) {
					if ($doDebug) {
						echo "ready to evaluate the assessment<br />";
					}
					
					if ($inp_level == 'Beginner') {
						if (!$didBeginner) {
							if ($doDebug) {
								echo "No beginner assessment found<br />";
							}
							$content			.= "<p>You have requested a Beginner Level CW Academy class. However, 
													no Beginner Morse Code Proficiency Assessment results were recorded 
													in the database.</p>
													<p><table style='border:4px solid green;width:auto;'><tr><td>Please click 
													<a href='$theURL?strpass=101&inp_Level=Beginner&enstr=$enstr'>Perform 
													Morse Code Assessment</a></td></tr></table>";
						} elseif ($bestResultBeginner <= 40) {
							if ($doDebug) {
								echo "beginner result <= 40<br />";
							}
							$content			.= "<p>You have requested a Beginner Level CW Academy 
													class. Your Morse Code Proficiency Assessment confirms 
													yourchoice.</p>
													$registerAsBeginner";
						} elseif ($bestResultBeginner > 40 && $bestResultBeginner <= 60) {
							if ($doDebug) {
								echo "beginner result between 40 and 60<br />";
							}
							$content		.= "<p>You have requested a Beginner Level CW Academy class. Based on your 
												Morse Code Proficiency Assessment results, a Beginner class is 
												recommended. However,   
												you may switch to a Fundamental Level class if you wish.</p>
												$registerAsBeginner
												$registerAsFundamental";
							
						} elseif ($bestResultBeginner >= 60) {
							if ($doDebug) {
								echo "beginner result > 60<br />";
							}
							if (!$didFundamental) { 
								$content			.= "<p>You have requested a Beginner Level CW Academy 
														class. Your Morse Code Proficiency Assessment confirms 
														you rchoice.</p>
														$registerAsBeginner";
							}
							if ($didFundamental) {
								if ($bestFundamentalScore < 60) {
									$content		.= "<p>You have requested a Beginner Level CW Academy class. However, based on 
														your Morse Code Proficiency Assessment result,  
														CW Academy recommends that you switch to a Fundamental Level class. However, 
														you also have the option to sigh up for a Beginner level class, if you wish.</p>
														$registerAsFundamental
														$registerAsBeginner";
								
								} elseif ($didIntermediate) {
									if ($bestIntermediateScore < 60) {
										$content 		.= "<p>You have requested a Beginner Level CW Academy class. However, based on 
															your Morse Code Proficiency Assessment result,  
															CW Academy recommends that you switch to a Fundamental Level class. However, 
															you also have the option to sigh up for a Beginner Level class, if you wish.</p>
															$registerAsFundamental
															$registerAsBeginner";
									} else {
										if ($didAdvanced) {
											if ($bestAdvancedScore < 70) {
												$content 		.= "<p>You have requested a Beginner Level CW Academy class. However, based on 
																	your Morse Code Proficiency Assessment result,  
																	recommends that you, at minimum, switch to a Fundamental Level class. In addition, 
																	you should consider switching to an Intermediate Level class. Taking a Beginner 
																	Level class is highly discouraged.</p>
																	$registerAsFundamental
																	$registerAsIntermediate
																	$registerAsBeginner";
											} else {
												$content 		.= "<p>You have requested a Beginner Level CW Academy class. However, based on 
																	your Morse Code Proficiency Assessment result,  
																	CW Academy recommends that you, at minimum, switch to 
																	an Intermediate Level class. You should also consider an Advanced Lvel 
																	class. Both the Beginner Level class and the Fundamental Level class 
																	are highly discouraged for you.</p>
																	$registerAsIntermediate
																	$regsiterAsAdvanced
																	$registerAsFundamental
																	$registerAsBeginner";
											}
										}
									}
								}
							}
						}
					
					} elseif ($inp_level == 'Fundamental') {
						if (!$didFundamental) {
							$content			.= "<p>You have requested a Fundamental Level CW Academy class. However, 
													no Fundamental Morse Code Proficiency Assessment results were recorded 
													in the database.</p>
													<p><table style='border:4px solid green;width:auto;'><tr><td>Please click 
													<a href='$theURL?strpass=101&inp_level=Fundamental&enstr=$enstr'>Perform 
													Morse Code Assessment</a></td></tr></table>";
						}
						if ($bestResultFundamental <= 40) {
							$content	.= "<p>You have requested a Fundamental Level class. Your Morse Code Proficiency 
											Assessment results indicate that you may want to consider taking a 
											Beginner Level class instead. You also continue signing up for a Fundamental Level class, if you wish.</p>
											$registerAsBeginner
											$registerAsFundamental";
						} elseif ($bestResultFundamental > 40 && $bestResultFundamental < 60) {
							$content	.= "<p>You requested a Fundamental Level class and your Morse Code Proficiency 
											Assessment supports that request.</p>
											$registerAsFundamental";
						} else {				
							if (!$didIntermediate) {
								$content	.= "<p>You have requested a Fundamental Level CW Academy class and your 
												Morse Code Proficiency Assessment supports that request.</p>
												$registerAsFundamental";
							} else {
								if ($bestResultIntermediate < 60) {
									$content	.= "<p>You have requested a Fundamental Level CW Academy class and your 
													Morse Code Proficiency Assessment supports that request.</p>
													$registerAsFundamental";
								} else {
									if (!$didAdvanced) {
										$content	.= "<p>You have requested a Fundamental Level CW Academy class and your 
														Morse Code Proficiency Assessment supports that request.</p>
														$registerAsFundamental";							
									} elseif ($bestResultAdvanced < 70) {
										$content	.= "<p>You have requested a Fundamental Level CW Academy class. Your 
														Morse Code Proficiency Assessment results indicate that you should 
														at minimum switch to an Intermediate Level class. However, you can also 
														continue signing up for a Fundamental Level class if you wish.</p>
														$registerAsIntermediate
														$registerAsFundamental";
									} else {
										$content	.= "<p>You have requested a Fundamental Level CW Academy class. However, your 
														Morse Code Proficiency Assessment results indicate that  
														switching to an Intermediate Level class is a better option. You can also 
														sign up for an Advanced class, based on your assessment score. 
														Finally, signing up for a Fundamental Level class is highly discouraged.</p>
														$registerAsIntermediate
														$registerAsAdvanced
														$registerAsFundamental";
									}
								}
							}
						}
					} elseif ($inp_level == 'Intermediate') {
						if (!$didIntermediate) {
							$content			.= "<p>You have requested an Intermediate Level CW Academy class. However, 
													no Intermediate Morse Code Proficiency Assessment results were recorded 
													in the database.</p>
													<p><table style='border:4px solid green;width:auto;'><tr><td>Please click 
													<a href='$theURL?strpass=101&inp_level=Intermediate&enstr=$enstr'>Perform 
													Morse Code Assessment</a></td></tr></table>";				
						} else {
							if ($bestResultIntermediate <= 40) {
								$content	.= "<p>You have requested an Intermediate Level CW Academy class. However, 
												your Morse Code Proficiency Assessment result indicate that switching 
												to a Fundamental Level class is more appropriate for you. Nevertheless, 
												you can continue signing up for an Intermediate Level class if you wish, 
												and will bring up your proficiency in the meantime.</p>
												$registerAsFundamental
												$registerAsIntermediate";
							} elseif ($bestResultIntermediate > 40 && $bestResultIntermediate <= 60) {
								$content	.= "<p>You have requested an Intermediate Level CW Academy class. Your 
												Morse Code Proficiency Assessment results support that option.</p>
												$registerAsIntermediate";
							} elseif ($bestResultIntermediate > 60) {
								if (!$didAdvanced) {
									$content	.= "<p>You have requested an Intermediate Level CW Academy class. Your 
													Morse Code Proficiency Assessment results support that option.</p>
													$registerAsIntermediate";
								} else {
									if ($bestResultAdvanced <= 70) {
										$content	.= "<p>You have requested an Intermediate Level CW Academy class. Your 
														Morse Code Proficiency Assessment results support that option.</p>
														$registerAsIntermediate";
									} else {
										$content	.= "<p>You have requested an Intermediate Level CW Academy class. Your 
														Morse Code Proficiency Assessment results for both Intermediate and 
														Advanced Levels indicate that you have the option to either 
														continue signing up for an Intermediate Level class or switch to an 
														Advanced Level class.</p>
														$registerAsIntermediate
														$registerAsAdvanced";
									}
								}
							}
						} 
					
					} elseif ($inp_level == 'Advanced') {
						if (!$didAdvanced) {
							$content			.= "<p>You have requested an Advanced Level CW Academy class. However, 
													no Advanced Morse Code Proficiency Assessment results were recorded 
													in the database.</p>
													<p><table style='border:4px solid green;width:auto;'><tr><td>Please click 
													<a href='$theURL?strpass=101&inp_level=Advamced&enstr=$enstr'>Perform 
													Morse Code Assessment</a></td></tr></table>";
						} else {
							if ($bestResultAdvanced <= 70) {
								$content		.= "<p>You have requested an Advanced Level CW Academy class. However, your Morse Code 
													Proficiency Assessment results do not support that option. CW Academy strongly recommends that you 
													switch to an Intermediate Level class.</p>
													<p>If you disagree with switching to Intermediate, please contact the appropriate person 
													at <a href='https://cwops.org/cwa-class-resolution/'>CW Academy Class Resolution</a></p>
													$registerAsIntermediate";
							} else {
								$content		.= "<p>You have requested an Advanced Level CW Academy Class and your 
													Morse Code Proficiency Assessment supports that request.</p>
													$registerAsAdvanced";
							}
						}
					}
				}
			}
		}

	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>V$versionNumber. Prepared at $thisTime</p>";
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
add_shortcode ('student_registration', 'student_registration_func');
