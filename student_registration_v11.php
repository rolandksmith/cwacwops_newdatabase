function student_registration_v11_func() {

/*

	Modified 11Apr21 by Roland to add student_parent and parent_email for 
	students 17 years of age or younger
	Modified 18June21 by Roland to add maintenanceMode and v2 of the audit log process
	Modified 24July21 by Roland to add ability to check registration status
	Modified 2Aug21 by Roland to require parent name and email for a youth
	Modified 20Aug21 by Roland to validate the student before showing student info
	Modified 24Aug21 by Roland to interrupt the Fundamental signup if student has already 
		taken the class and was promotable
	Modified 29Aug21 by Roland to save class selections in both local time and utc. 
		changed the version to V7
	Modified 1Sep21 by Roland to move to 2-hour class block. Changed version to V8
	Modified 7Nov21 by Roland to add an encoded string to the welcome email and the verify email 
		links so they can come straight in. Moved the version to v9
	Modified 9Dec21 by Roland to allow registrations for the upcoming semester until the 
		semester starts. Students will be put on the waiting list. Move the version to v10
	Modified 11Jan2022 by Roland to move to tables rather than pods
	Modified 13Mar2022 by Roland to add the self assessment process
	odified 18Jun2022 by Roland to bypass the self assessment process if the student has 
		done an end-of-semester assessment within 45 days
	Modified 8Aug2022 by Roland to only do class selections 45 days before start of the 
		semester
	Modified 24Aug22 by Roland to use a standard catalog if more than 45 days to the start 
		of the semester
	Modified 25Aug22 by Roland to use timezone IDs rather than having the student enter their
		timezone
	Modified 21Mar23 by Roland to properly handle a bad zip code
	Modified 12Apr23 by Roland to remove student from an advisor class if the student 
		deletes the registration record
	Modified 17Apr23 by Roland to fix action_log
	Modified 16June23 by Roland to correct typos
	Modified 21June23 by Roland to allow signup for next semester
	Modified 1July23 by Roland to refactor the assessment process
	Modified 17Jul23 by Roland to use consolidated tables
	Modified 15Aug23 by Roland to handle situations where a student had previously refused 
		the verification request. Upgraded the version to V7
	Modified 24Aug23 by Roland to not delete the student record but change response to R 
		and remove from a class if assigned
	Modified 28Aug23 by Roland to change input pages display of country selection
	Modified 31Aug23 by Roland to turn off debug and testmode if the user is not signed in
	Modified 1Sep23 by Roland to use new scheme for the class catalog
	Modified 7Oct23 by Roland to use the new assessment process
	Modified 15oct23 by Roland to take the version number out of the url
	Modified 23Oct23 by Roland to fully incorporate the new assessment and make a number of 
		other updates and corrections. Version updated to V10
	Modified 12Nov23 by Roland in an attempt to prevent duplicate registrations
	Modified 20Nov23 by Roland for the portal process
	Modified 30Jan24 by Roland to allow Beginners to skip the assessment
	Modified 15Mar24 by Roland to fix changing the semester
	Modified 19July24 by Roland to fix the check for needing assessment in pass 101
		
*/
    

	global $wpdb,$doDebug,$testMode,$demoMode,$inp_verbose,$daysToGo;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$demoMode						= FALSE;
	$doAssessment					= FALSE;
	$maintenanceMode				= FALSE;
	$verifyMode						= FALSE;
	$versionNumber					= '11';
	$skipAssessment					= FALSE;
	
	$daysToGo						= 0;
	
	$initializationArray 			= data_initialization_func();
	$currentDate				= $initializationArray['currentDate'];
	$currentDateTime			= $initializationArray['currentDateTime'];
	$validTestmode				= $initializationArray['validTestmode'];
	$userName					= $initializationArray['userName'];
	$userRole					= $initializationArray['userRole'];
	$userEmail					= $initializationArray['userEmail'];
	$fakeIt						= "Y";
	$replacementPeriod			= $initializationArray['validReplacementPeriod'];
	if ($userRole != 'administrator') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
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
	$inp_first_class_choice		= '';
	$inp_second_class_choice	= '';
	$inp_third_class_choice		= '';
	$inp_messaging				= '';
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
	$proximateSemester				= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$proximateSemester			= $nextSemester;
	}
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
		$student_first_name				= ''; 
		$student_last_name				= ''; 
		$student_email					= ''; 
		$student_phone					= ''; 
		$student_messaging				= ''; 
		$student_city					= ''; 
		$student_state					= ''; 
		$student_zip_code				= ''; 
		$student_country				= ''; 
		$student_time_zone				= ''; 
		$student_level					= ''; 
		$student_semester				= ''; 
		$student_youth					= ''; 
		$student_age					= ''; 
		$student_student_parent			= '';
		$student_student_parent_email	= '';
		$student_first_class_choice		= ''; 
		$student_second_class_choice	= ''; 
		$student_third_class_choice		= ''; 
		$student_ID						= ''; 
		
		$student_wpm					= '';
		$student_waiting_list				= '';
		$student_request_date			= '';
		$student_notes					= '';
		$student_email_sent_date		= '';
		$student_email_number			= '';
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


		$inp_call_sign					= ''; 
		$inp_callsign					= '';
		$inp_firstname					= ''; 
		$inp_lastname					= ''; 
		$inp_email						= ''; 
		$inp_phone						= ''; 
		$inp_ph_code					= '+1';
		$inp_messaging					= ''; 
		$inp_city						= ''; 
		$inp_state						= ''; 
		$inp_zip						= ''; 
		$inp_country					= '';
		$inp_countrya					= '';
		$inp_countryb					= ''; 
		$inp_timezone					= ''; 
		$inp_level						= ''; 
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

		$inp_wpm						= '';
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
				$enstr			= filter_var($enstr,FILTER_UNSAFE_RAW);
				$encodedString	= base64_decode($enstr);
				$myArray		= explode("&",$encodedString);
				foreach($myArray as $thisValue) {
					$enArray	= explode("=",$thisValue);
					if ($enArray[0] == 'strPass') {
						$enArray[1] = substr($enArray[1],0,1);
					}
					${$enArray[0]}	= $enArray[1];
					if ($doDebug) {
						echo "enstr contained $enArray[0] = $enArray[1]<br />";
					}
				}
			}

			if ($str_key 				== "enstrInfo") {
				$enstrInfo			 	= $str_value;
				$enstrInfo				= filter_var($enstrInfo,FILTER_UNSAFE_RAW);
				$encodedString			= base64_decode($enstrInfo);
				$myArray				= explode("&",$encodedString);
				foreach($myArray as $thisValue) {
					$enArray			= explode("=",$thisValue);
					if ($enArray[0] 	== 'strPass') {
						$enArray[1] 	= substr($enArray[1],0,1);
					}
					${$enArray[0]}		= $enArray[1];
					if ($doDebug) {
						echo "enstrInfo contained $enArray[0] = $enArray[1]<br />";
					}
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
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
			if ($str_key 		== "allowSignup") {
				$allowSignup	 = $str_value;
				$allowSignup	 = filter_var($allowSignup,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "waitingList") {
				$waitingList	 = $str_value;
				$waitingList	 = filter_var($waitingList,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "new_semester") {
				$new_semester	 = $str_value;
				$new_semester	 = filter_var($new_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "submit") {
				$submit	 = $str_value;
				$submit	 = filter_var($submit,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_email') {
				$inp_email = trim($str_value);
				$inp_email = strtolower(filter_var($inp_email,FILTER_UNSAFE_RAW));
			}
			if ($str_key == 'inp_phone') {
				$inp_phone = trim($str_value);
				$inp_phone = filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_ph_code') {
				$inp_ph_code = trim($str_value);
				$inp_ph_code = filter_var($inp_ph_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_city') {
				$inp_city = $str_value;
				$inp_city = filter_var($inp_city,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_state') {
				$inp_state = $str_value;
				$inp_state = filter_var($inp_state,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_country') {
				$inp_country = $str_value;
				$inp_country = filter_var($inp_country,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_countrya') {
				$inp_countrya = $str_value;
				$inp_countrya = filter_var($inp_countrya,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_countryb') {
				$inp_countryb	= $str_value;
				$inp_countryb = filter_var($inp_countryb,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_age') {
				$inp_age = $str_value;
				$inp_age = filter_var($inp_age,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_student_parent') {
				$inp_student_parent = $str_value;
				$inp_student_parent = filter_var($inp_student_parent,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_student_parent_email') {
				$inp_student_parent_email = $str_value;
				$inp_student_parent_email = strtolower(filter_var($inp_student_parent_email,FILTER_UNSAFE_RAW));
			}
			if ($str_key == 'inp_zip') {
				$inp_zip = $str_value;
				$inp_zip = filter_var($inp_zip,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_timezone') {
				$inp_timezone = $str_value;
				$inp_timezone = filter_var($inp_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_callsign') {
				$inp_callsign = trim($str_value);
				$inp_callsign = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key == 'old_callsign') {
				$old_callsign = $str_value;
				$old_callsign = strtoupper(filter_var($old_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key == 'inp_lastname') {
				$inp_lastname = no_magic_quotes($str_value);
//				$inp_lastname = filter_var($inp_lastname,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_firstname') {
				$inp_firstname = $str_value;
				$inp_firstname = filter_var($inp_firstname,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_youth') {
				$inp_youth = $str_value;
				$inp_youth = filter_var($inp_youth,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_level') {
				$inp_level = $str_value;
				$inp_level = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_sked1') {
				$inp_sked1 = $str_value;
				$inp_sked1 = filter_var($inp_sked1,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_sked2') {
				$inp_sked2 = $str_value;
				$inp_sked2 = filter_var($inp_sked2,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_sked3') {
				$inp_sked3 = $str_value;
				$inp_sked3 = filter_var($inp_sked3,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_verify') {
				$inp_verify = $str_value;
				$inp_verify = filter_var($inp_verify,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_messaging') {
				$inp_messaging = $str_value;
				$inp_messaging = filter_var($inp_messaging,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'student_timezone') {
				$student_timezone = $str_value;
				$student_timezone = filter_var($student_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'student_level') {
				$student_level = $str_value;
				$student_level = filter_var($student_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'cur_semester') {
				$cur_semester = $str_value;
				$cur_semester = filter_var($cur_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'thisOption') {
				$thisOption = $str_value;
				$thisOption = filter_var($thisOption,FILTER_UNSAFE_RAW);
				if ($thisOption == 'assessment') {
					$doAssessment	= TRUE;
				}
			}
			if ($str_key == 'cur_level') {
				$cur_level = $str_value;
				$cur_level = filter_var($cur_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_doAgain') {
				$inp_doAgain = $str_value;
				$inp_doAgain = filter_var($inp_doAgain,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'newInput') {
				$newInput = $str_value;
				$newInput = filter_var($newInput,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'pass3FirstTime') {
				$pass3FirstTime = $str_value;
				$pass3FirstTime = filter_var($pass3FirstTime,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_first_class_choice') {
				$inp_first_class_choice = $str_value;
				$inp_first_class_choice = filter_var($inp_first_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_second_class_choice') {
				$inp_second_class_choice = $str_value;
				$inp_second_class_choice = filter_var($inp_second_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_third_class_choice') {
				$inp_third_class_choice = $str_value;
				$inp_third_class_choice = filter_var($inp_third_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_first_class_choice_utc') {
				$inp_first_class_choice_utc = $str_value;
				$inp_first_class_choice_utc = filter_var($inp_first_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_second_class_choice_utc') {
				$inp_second_class_choice_utc = $str_value;
				$inp_second_class_choice_utc = filter_var($inp_second_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_third_class_choice_utc') {
				$inp_third_class_choice_utc = $str_value;
				$inp_third_class_choice_utc = filter_var($inp_third_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'errorString') {
				$errorString = $str_value;
//				$errorString = filter_var($errorString,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_number') {
				$inp_number = $str_value;
				$inp_number = filter_var($inp_number,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'nextLevel') {
				$nextLevel = $str_value;
				$nextLevel = filter_var($nextLevel,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'audioFileName') {
				$audioFileName = $str_value;
				$audioFileName = filter_var($audioFileName,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'audioFileNumber') {
				$audioFileNumber = $str_value;
				$audioFileNumber = filter_var($audioFileNumber,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'firsttime') {
				$firsttime = $str_value;
				$firsttime = filter_var($firsttime,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'nocatalog') {
				$nocatalog = $str_value;
				$nocatalog = filter_var($nocatalog,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_delete') {
				$inp_delete = $str_value;
				$inp_delete = filter_var($inp_delete,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'timezone') {
				$timezone = $str_value;
				$timezone = filter_var($timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'browser_timezone_id') {
				$browser_timezone_id = $str_value;
				$browser_timezone_id = filter_var($browser_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'json_updateParams') {
				$json_updateParams = $str_value;
				$json_updateParams = stripslashes($json_updateParams);
			}
			if ($str_key == 'inp_whatsapp') {
				$inp_whatsapp = $str_value;
				$inp_whatsapp = filter_var($inp_whatsapp,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_telegram') {
				$inp_telegram = $str_value;
				$inp_telegram = filter_var($inp_telegram,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_signal') {
				$inp_signal = $str_value;
				$inp_signal = filter_var($inp_signal,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_messenger') {
				$inp_messenger = $str_value;
				$inp_messenger = filter_var($inp_messenger,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_timezone_id') {
				$inp_timezone_id = $str_value;
				$inp_timezone_id = filter_var($inp_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_timezone_offset') {
				$inp_timezone_offset = $str_value;
				$inp_timezone_offset = filter_var($inp_timezone_offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_days1') {
				$inp_days1 = $str_value;
				$inp_days1 = filter_var($inp_days1,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_days2') {
				$inp_days2 = $str_value;
				$inp_days2 = filter_var($inp_days2,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_days3') {
				$inp_days3 = $str_value;
				$inp_days3 = filter_var($inp_days3,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_times1') {
				$inp_times1 = $str_value;
				$inp_times1 = filter_var($inp_times1,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_times2') {
				$inp_times2 = $str_value;
				$inp_times2 = filter_var($inp_times2,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_times3') {
				$inp_times3 = $str_value;
				$inp_times3 = filter_var($inp_times3,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'student_ID') {
				$student_ID = $str_value;
				$student_ID = filter_var($student_ID,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_bypass') {
				$inp_bypass = $str_value;
				$inp_bypass = filter_var($inp_bypass,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'badTimezone') {
				$badTimezone = $str_value;
				$badTimezone = filter_var($badTimezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_student_catalog_options") {
				$inp_student_catalog_options	 = $str_value;
				$inp_student_catalog_options	 = filter_var($inp_student_catalog_options,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_student_flexible") {
				$inp_student_flexible	 = $str_value;
				$inp_student_flexible	 = filter_var($inp_student_flexible,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "result_option") {
				$result_option	 = $str_value;
				$result_option	 = filter_var($result_option,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_sked_times") {
				$inp_sked_times	 = $str_value;
//				$inp_sked_times	 = filter_var($inp_sked_times,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "doUpdate") {
				$doUpdate	 = $str_value;
//				$doUpdate	 = filter_var($doUpdate,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "continuePass8A") {
				$continuePass8A	 = $str_value;
				$continuePass8A	 = filter_var($continuePass8A,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_flex") {
				$inp_flex	 = $str_value;
				$inp_flex	 = filter_var($inp_flex,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token	 = $str_value;
				$token	 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "fakeIt") {
				$fakeIt	 = $str_value;
				$fakeIt	 = filter_var($fakeIt,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_available") {
				$inp_available	 = $str_value;
				$inp_available	 = filter_var($inp_available,FILTER_UNSAFE_RAW);
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

	
// get the timezone related arrays
//	$myResult			= get_timezone_arrays();
//	$timezoneArray		= $myResult[0];
//	$reverseArray		= $myResult[1];
//	$regionArray		= $myResult[2];
//	$countryCodeArray	= $myResult[3];


$countryCheckedArray = array(
'AR',
'AU',
'AT',
'BE',
'BR',
'CA',
'CL',
'CN',
'CZ',
'DK',
'FI',
'FR',
'DE',
'GB',
'GR',
'IN',
'IE',
'IL',
'IT',
'JP',
'KR',
'MX',
'NL',
'NZ',
'NO',
'PH',
'PL',
'PT',
'PR',
'KR',
'RU',
'ZA',
'ES',
'SE',
'CH',
'US');

$XX_checked = 'selected';	
$AR_checked = '';
$AU_checked = '';
$AT_checked = '';
$BS_checked = '';
$BH_checked = '';
$BY_checked = '';
$BE_checked = '';
$BA_checked = '';
$BR_checked = '';
$BN_checked = '';
$BG_checked = '';
$CA_checked = '';
$CL_checked = '';
$CN_checked = '';
$CO_checked = '';
$CR_checked = '';
$CU_checked = '';
$CZ_checked = '';
$DK_checked = '';
$DO_checked = '';
$GB_checked = '';
$EE_checked = '';
$FI_checked = '';
$FR_checked = '';
$DE_checked = '';
$GB_checked = '';
$GR_checked = '';
$IN_checked = '';
$ID_checked = '';
$IE_checked = '';
$IL_checked = '';
$IT_checked = '';
$JP_checked = '';
$JO_checked = '';
$KE_checked = '';
$KR_checked = '';
$LV_checked = '';
$MX_checked = '';
$MD_checked = '';
$MC_checked = '';
$NL_checked = '';
$NZ_checked = '';
$GB_checked = '';
$NO_checked = '';
$PE_checked = '';
$PH_checked = '';
$PL_checked = '';
$PT_checked = '';
$PR_checked = '';
$KR_checked = '';
$MD_checked = '';
$RO_checked = '';
$RU_checked = '';
$SA_checked = '';
$GB_checked = '';
$RS_checked = '';
$SG_checked = '';
$SK_checked = '';
$SI_checked = '';
$ZA_checked = '';
$ES_checked = '';
$SE_checked = '';
$CH_checked = '';
$TH_checked = '';
$TT_checked = '';
$TR_checked = '';
$GB_checked = '';
$US_checked = '';
$GB_checked = '';
$XX_checked = 'checked';
$AF_checked = '';
$AL_checked = '';
$DZ_checked = '';
$AX_checked = '';
$AS_checked = '';
$AD_checked = '';
$AO_checked = '';
$AI_checked = '';
$AQ_checked = '';
$AG_checked = '';
$AM_checked = '';
$AW_checked = '';
$AZ_checked = '';
$BD_checked = '';
$BB_checked = '';
$BZ_checked = '';
$BJ_checked = '';
$BM_checked = '';
$BT_checked = '';
$BO_checked = '';
$BQ_checked = '';
$BW_checked = '';
$BV_checked = '';
$IO_checked = '';
$BF_checked = '';
$BI_checked = '';
$KH_checked = '';
$CM_checked = '';
$CV_checked = '';
$KY_checked = '';
$CF_checked = '';
$TD_checked = '';
$CX_checked = '';
$CC_checked = '';
$KM_checked = '';
$CD_checked = '';
$CG_checked = '';
$CK_checked = '';
$HR_checked = '';
$CW_checked = '';
$CY_checked = '';
$KP_checked = '';
$DJ_checked = '';
$DM_checked = '';
$EC_checked = '';
$EG_checked = '';
$GQ_checked = '';
$ER_checked = '';
$ET_checked = '';
$FK_checked = '';
$FO_checked = '';
$FM_checked = '';
$FJ_checked = '';
$GF_checked = '';
$PF_checked = '';
$TF_checked = '';
$GA_checked = '';
$GM_checked = '';
$GE_checked = '';
$GH_checked = '';
$GI_checked = '';
$GL_checked = '';
$GD_checked = '';
$GP_checked = '';
$GU_checked = '';
$GT_checked = '';
$GG_checked = '';
$GW_checked = '';
$GN_checked = '';
$GY_checked = '';
$HT_checked = '';
$HM_checked = '';
$VA_checked = '';
$HN_checked = '';
$HK_checked = '';
$HU_checked = '';
$IS_checked = '';
$IR_checked = '';
$IQ_checked = '';
$IR_checked = '';
$IM_checked = '';
$CI_checked = '';
$JM_checked = '';
$JE_checked = '';
$KZ_checked = '';
$KI_checked = '';
$KW_checked = '';
$KG_checked = '';
$LA_checked = '';
$LA_checked = '';
$LB_checked = '';
$LS_checked = '';
$LR_checked = '';
$LY_checked = '';
$LI_checked = '';
$LT_checked = '';
$LU_checked = '';
$MO_checked = '';
$MK_checked = '';
$MG_checked = '';
$MW_checked = '';
$MY_checked = '';
$MV_checked = '';
$ML_checked = '';
$MT_checked = '';
$MH_checked = '';
$MQ_checked = '';
$MR_checked = '';
$MU_checked = '';
$YT_checked = '';
$FM_checked = '';
$MN_checked = '';
$ME_checked = '';
$MS_checked = '';
$MA_checked = '';
$MZ_checked = '';
$MM_checked = '';
$NA_checked = '';
$NR_checked = '';
$NP_checked = '';
$NC_checked = '';
$NI_checked = '';
$NE_checked = '';
$NG_checked = '';
$NU_checked = '';
$NF_checked = '';
$MP_checked = '';
$OM_checked = '';
$PK_checked = '';
$PW_checked = '';
$PS_checked = '';
$PA_checked = '';
$PG_checked = '';
$PY_checked = '';
$PN_checked = '';
$QA_checked = '';
$RE_checked = '';
$RW_checked = '';
$BL_checked = '';
$SH_checked = '';
$KN_checked = '';
$LC_checked = '';
$MF_checked = '';
$PM_checked = '';
$VC_checked = '';
$WS_checked = '';
$SM_checked = '';
$ST_checked = '';
$SN_checked = '';
$SC_checked = '';
$SL_checked = '';
$SX_checked = '';
$SB_checked = '';
$SO_checked = '';
$GS_checked = '';
$SS_checked = '';
$LK_checked = '';
$PS_checked = '';
$SD_checked = '';
$SR_checked = '';
$SJ_checked = '';
$SZ_checked = '';
$SY_checked = '';
$TW_checked = '';
$TJ_checked = '';
$CD_checked = '';
$TL_checked = '';
$TG_checked = '';
$TK_checked = '';
$TO_checked = '';
$TN_checked = '';
$TM_checked = '';
$TC_checked = '';
$TV_checked = '';
$UG_checked = '';
$UA_checked = '';
$AE_checked = '';
$TZ_checked = '';
$UM_checked = '';
$UY_checked = '';
$UZ_checked = '';
$VU_checked = '';
$VE_checked = '';
$VN_checked = '';
$VG_checked = '';
$VI_checked = '';
$WF_checked = '';
$EH_checked = '';
$YE_checked = '';
$ZM_checked = '';
$ZW_checked = '';




	
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
		$studentTableName			= 'wpw1_cwa_consolidated_student2';
		$oldAssessmentTableName		= "wpw1_cwa_audio_assessment2";
		$newAssessmentTableName		= "wpw1_cwa_new_assessment_data";
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
		$studentDeletedTableName	= 'wpw1_cwa_student_deleted2';
		$catalogMode				= 'TestMode';
	} elseif ($demoMode) {
		$content					.= "<p>Operating in <b>Demonstration Mode</b>.</p>";
		$studentTableName			= 'wpw1_cwa_consolidated_student3';
		$oldAssessmentTableName		= "wpw1_cwa_audio_assessment2";
		$newAssessmentTableName		= "wpw1_cwa_new_assessment_data";
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
		$studentDeletedTableName	= 'wpw1_cwa_student_deleted2';
		$catalogMode				= 'TestMode';
	} else {
		$studentTableName			= 'wpw1_cwa_consolidated_student';
		$oldAssessmentTableName		= "wpw1_cwa_audio_assessment";
		$newAssessmentTableName		= "wpw1_cwa_new_assessment_data";
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
		$studentDeletedTableName	= 'wpw1_cwa_student_deleted';
		$catalogMode				= 'Production';
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




	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$userName		= strtoupper($userName);
		$content 		.= "<h3>$jobname</h3>";
		$showSignup		= FALSE;
		$showAll		= FALSE;
		if ($maintenanceMode) {
			$content	.= "<p><b>The Student Sign-up process is currently undergoing 
							maintenance. That should be completed within the next hour. Please come back at that 
							time to sign up.</b></p>";
		} else {
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
				$sql	= "select semester, 
				                  promotable 
				            from $studentTableName 
							where call_sign = '$userName' and 
							(semester = '$currentSemester' or 
							semester = '$nextSemester' or 
							semester = '$semesterTwo' or
							semester = '$semesterThree' or 
							semester = '$semesterFour')";
				$studentData	= $wpdb->get_results($sql);
				if ($studentData === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numSRows	= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $numSRows rows<br />";
					}
					if ($numSRows == 0) {
						$gotCurrentSemester				= FALSE;
						$gotPromotable					= FALSE;
						$gotFutureSemester				= FALSE;
						if ($doDebug) {
							echo "no rows retrieved. All logicals set to FALSE<br />";
						}
					} else {
						foreach($studentData as $studentRow) {
							$thisSemester				= $studentRow->semester;
							$thisPromotable				= $studentRow->promotable;
							
							if ($doDebug) {
								echo "<br />thisSemester: $thisSemester<br />
									  thisPromotable: $thisPromotable<br />";
							}
							
							if ($thisSemester == $currentSemester) {
								$gotCurrentSemester 	= TRUE;
								if ($thisPromotable != '') {
									$gotPromotable		= TRUE;
								}
							} else {
								$gotFutureSemester		= TRUE;
							}
						}
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

				} elseif ($gotCurrentSemester === FALSE && $gotPromotable === FALSE && $gotFutureSemester === TRUE) {
					$showSignup			= FALSE;
					$showAll			= TRUE;
					
				} elseif ($gotCurrentSemester === FALSE && $gotPromotable === TRUE && $gotFutureSemester === FALSE) {
					$showSignup			= FALSE;
					$showAll			= FALSE;
					sendErrorEmail("$jobname Pass1 Have Promotable but no current or future semester");
					
				} elseif ($gotCurrentSemester === FALSE && $gotPromotable === TRUE && $gotFutureSemester === TRUE) {
					$showSignup			= FALSE;
					$showAll			= TRUE;
					sendErrorEmail("$jobname Pass1 Have Promotable but no current semester");
					
				} elseif ($gotCurrentSemester === TRUE && $gotPromotable === FALSE && $gotFutureSemester === FALSE) {
					$showSignup			= FALSE;
					$showAll			= TRUE;
					
				} elseif ($gotCurrentSemester === TRUE && $gotPromotable === FALSE && $gotFutureSemester === TRUE) {
					$showSignup			= FALSE;
					$showAll			= TRUE;
					sendErrorEmail("$jobname Pass1 Have current semester, no promotable, but have future semester as well");
					
				} elseif ($gotCurrentSemester === TRUE && $gotPromotable === TRUE && $gotFutureSemester === FALSE) {
					$showSignup			= TRUE;
					$showAll			= FALSE;
					
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
			
			$content		.= "<p>Welcome to the CW Academy where our mission is to increase the 
								number of competent CW operators on the amateur radio bands and our 
								goal is to guide you to becoming a better CW operator as you 
								increase your CW skills, speed, and activity!</p>
								<table style='width:800px;border:4px solid green;'>";
			if ($showSignup) {
				$content		.= "<tr><td style='vertical-align:top;'><b>Sign-up</b><br />You should now 
											sign up as a student for an upcoming semester<br />
											<form method='post' action='$theURL' 
											name='option1_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='100'>
											<input type='submit' class='formInputButton' name='option1submit' value='Sign-Up'>
											</form></td>";
			}
			if ($showAll) {
				$content			.= "<td style='vertical-align:top;'><b>Modify Signup Information</b><br />You have already signed up and wish 
											to update or modify your sign up information<br />
											<form method='post' action='$theURL' 
											name='option3_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='90'>
 											<input type='submit' class='formInputButton' name='option3submit' value='Update Registration'>
											</form></td>";
			}
			$content				.= "<td style='vertical-align:top;'><b>Check SignupStatus</b><br />You have already signed up and want to check 
											the status of your registration<br />
											<form method='post' action='$siteURL/cwa-check-student-status/' 
											name='option2_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='1'>
											<input type='submit' class='formInputButton' name='option2submit' value='Check Status'>
											</form></td>
										<td style='vertical-align:top;'><b>Practice Assessment</b><br />If you want to take a practice Morse Code 
											Proficiency Assessment, click the 'Practice Assessment' button below. You are allowed to take 
											a practice Morse Code Proficiency Assessment twice in a 45-day period.<br />
											<form method='post' action='$siteURL/cwa-practice-assessment/' 
											name='option4_form' ENCTYPE='multipart/form-data'>
 											<input type='submit' class='formInputButton' name='option4submit' value='Practice Assessment'>
											</form></td></tr>
									</table>";
		}



///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass2 with inp_callsign: $inp_callsign<br />
			      inp_email: $inp_email<br />
			      inp_phone: $inp_phone<br />
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
		// Look up the student to determine if a record already exists
		// if so, do the update process. Otherwise, do the new student (maybe)
		if ($allowSignup) {
			$semesterStr		= "(semester = '$nextSemester' 
									 or semester = '$semesterTwo' 
									 or semester = '$semesterThree' 
									 or Semester = '$semesterFour')"; 
		} else {
			$semesterStr		= "(semester = '$currentSemester' 
									 or semester = '$nextSemester' 
									 or semester = '$semesterTwo' 
									 or semester = '$semesterThree' 
									 or Semester = '$semesterFour')";		
		}

		$sql				= "select * from $studentTableName 
								where call_sign='$inp_callsign' 
								 and $semesterStr 
								order by date_created DESC 
								limit 1";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError("$jobname Pass 2",$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError("$jobname Pass 2",$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				$noRecord									= FALSE;
				$foundARecord								= TRUE;
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_phone  						= $studentRow->phone;
					$student_ph_code						= $studentRow->ph_code;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_country_code					= $studentRow->country_code;
					$student_time_zone  					= $studentRow->time_zone;
					$student_timezone_id					= $studentRow->timezone_id;
					$student_timezone_offset				= $studentRow->timezone_offset;
					$student_whatsapp						= $studentRow->whatsapp_app;
					$student_signal							= $studentRow->signal_app;
					$student_telegram						= $studentRow->telegram_app;
					$student_messenger						= $studentRow->messenger_app;					
					$student_wpm 	 						= $studentRow->wpm;
					$student_youth  						= $studentRow->youth;
					$student_age  							= $studentRow->age;
					$student_student_parent 				= $studentRow->student_parent;
					$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->level;
					$student_waiting_list 					= $studentRow->waiting_list;
					$student_request_date  					= $studentRow->request_date;
					$student_semester						= $studentRow->semester;
					$student_notes  						= $studentRow->notes;
					$student_welcome_date  					= $studentRow->welcome_date;
					$student_email_sent_date  				= $studentRow->email_sent_date;
					$student_email_number  					= $studentRow->email_number;
					$student_response  						= strtoupper($studentRow->response);
					$student_response_date  				= $studentRow->response_date;
					$student_abandoned  					= $studentRow->abandoned;
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog			 			= $studentRow->no_catalog;
					$student_hold_override  				= $studentRow->hold_override;
					$student_messaging  					= $studentRow->messaging;
					$student_assigned_advisor  				= $studentRow->assigned_advisor;
					$student_advisor_select_date  			= $studentRow->advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
					$student_hold_reason_code  				= $studentRow->hold_reason_code;
					$student_class_priority  				= $studentRow->class_priority;
					$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
					$student_promotable  					= $studentRow->promotable;
					$student_excluded_advisor  				= $studentRow->excluded_advisor;
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->available_class_days;
					$student_intervention_required  		= $studentRow->intervention_required;
					$student_copy_control  					= $studentRow->copy_control;
					$student_first_class_choice  			= $studentRow->first_class_choice;
					$student_second_class_choice  			= $studentRow->second_class_choice;
					$student_third_class_choice  			= $studentRow->third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
					$student_catalog_options				= $studentRow->catalog_options;
					$student_flexible						= $studentRow->flexible;
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$student_last_name 						= no_magic_quotes($student_last_name);
					
					if ($doDebug) {
						echo "country code: $student_country_code; country: $student_country<br />";
					}

					////// a record exists. Validate the student
					$newInput					= 'N';
					$noRecord					= FALSE;
					$waiting				= "";
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

					if ($student_messaging == 'Yes') {
						$textMsgYes			= "checked='checked'";
					} elseif ($student_messaging == 'No') {
						$textMsgNo			= "checked='checked'";
					} else {
						$textMsgYes			= "checked='checked'";
					}
					if ($student_youth == 'Yes') {
						$youthYesChecked	= "checked='checked'";
					} elseif ($student_youth == 'No') {
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



					if (isset(${$student_country_code . '_checked'})) {
						if (in_array($student_country_code,$countryCheckedArray)) {
							${$student_country_code . '_checked'}	= 'checked';
							$XX_checked								= '';
							if ($doDebug) { 
								echo "have set A $student_country_code" . "_checked to ${$student_country_code . '_checked'}<br />";
							}
						} else {
							${$student_country_code . '_checked'}	= 'selected';
							$XX_checked								= '';
							if ($doDebug) { 
								echo "have set B $student_country_code" . "_checked to ${$student_country_code . '_checked'}<br />";
							}
						}
					} else {
						$XX_checked								= 'checked';
						if ($doDebug) { 
							echo "have set XX_checked to $XX_checked<br />";
						}
					}
				
					$daysToGo			= days_to_semester($student_semester);
					if ($doDebug) {
						echo "daysToGo calculated to be $daysToGo<br />";
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
					if ($daysToGo < 21) {								// student askign for upcoming semester
						if ($student_assigned_advisor != '') {			// student is assigned to an advisor
							$canChangeAnything	= FALSE;				// limited changes only
							if ($doDebug) {
								echo "daysToGo is less than 21 and student has an assigned advisor<br />canChangeAnything set to FALSE<br />";
							}
						}
					} else {			// if assigned advisor, something is wrong
						if ($student_assigned_advisor != '') {
							sendErrorEmail("$jobname Student $student_call_sign more than 21 days to the semester and student has $student_assigned_advisor assigned as an advisor. Program being run by $userName");
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
						$content		.= "<p>Because you are signed up for the $student_semester semester and you have been 
											assigned to a class, you may only make limited 
											changes to your sign-up information. If you need to drop out of the 
											class or move to a different semester, please contact a 
											systems administrator at <a href='https://cwops.org/cwa-class-resolution/' target='_blank'>
											Class Resolution</a></p>";
						$firstLine		= "<tr><td style='vertical-align:top;'>
													<b>Semester</b><br />
													$student_semester</td>
												<td style='vertical-align:top;'>
													<b>Class Level</b><br />
													$student_level</td>
												<td style='vertical-align:top;'>
													&nbsp;</td></tr>";
						$extraHidden		= "<input type='hidden' name='inp_semester' value='$student_semester'>
											   <input type='hidden' name='inp_level' value='$student_level'>";
										   
					} else {
						if ($currentSemester == 'Not in Session' && $daysToGo > 10) {
							if ($doDebug) {
								echo "curent semester is Not in Session and daysToGo is greater than 10. Displaying $nextSemester and $semesterTwo<br />";
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
								echo "current semester is in session and validReplacementPeriod. Displaying $durrentSemester, $nextSemester, and $semesterTwo<br />";
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
						$content		.= "<p>You may make changes to your sign-up information 
											until about three weeks before the start of the semester at which time the process to assign 
											students to advisor classes will occur.</p>";
						$deleteMsg		= "<span style='color: brown;'><input type='checkbox' class='formInputButton' name='inp_delete' value='Y'> To delete your 
										   registration, click in the box and then click 'Next' below</span>";
						$firstLine		= "<tr><td style='vertical-align:top;'>
													<b>Semester</b><br />
													$semesterList</td>
												<td style='vertical-align:top;'>
													<b>Class Level</b><br />
													$student_level</td>
												<td style='vertical-align:top;'>
													&nbsp;</td></tr>";
					}
					$content			.= "<p><b>For Military or Civilians who will be deployed to an APO address:</b> Please enter 'APO' as your 
											city, your APO as your zip code, and then select the country where you are or will be deployed.</p>
											<p>It is CW Academy's policy that a student 
											should not register for another class until 
											the student has completed the current class.</p>
											<form method='post' action='$theURL' 
											name='studentform' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='$nextPass'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_semester' value='$student_semester'>
											<input type='hidden' name='inp_level' value='$student_level'>
											<input type='hidden' name='inp_ph_code' value='$student_ph_code'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<input type='hidden' name='demonstration' value='$demonstration'>
											<input type='hidden' name='verifyMode' value='$verifyMode'>
											<input type='hidden' name='inp_callsign' value='$student_call_sign'>
											<input type='hidden' name='inp_verify' value='$inp_verify'>
											<input type='hidden' name='token' value='$token'>
											<input type='hidden' name='student_ID' value='$student_ID'>
											$extraHidden
											$deleteMsg
											<table style='width:100%'>
											$firstLine
											<tr><td colspan='3'><hr></td></tr>
											<tr><td style='width:300px;vertical-align:top;'>
													<b>Call Sign*</b><br />
													$inp_callsign</td>
												<td style='width:300px;vertical-align:top;'>
													<b>Last Name*</b><br />
													<input type='text' class='formInputText' id='chk_lastname' name='inp_lastname' size='50' maxlength='50' value=\"$student_last_name\" required ></td>
												<td style='width:300px;vertical-align:top;'>
													<b>First Name*</b><br />
													<input type='text' class='formInputText' id='chk_firstname' name='inp_firstname' size='50' maxlength='50' value='$student_first_name' required ></td></tr>
											<tr><td colspan='3'><hr></td></tr>
											<tr><td style='vertical-align:top;'>
													<b>Email*</b><br />
													<input type='text' class='formInputText' id='chk_email' name='inp_email' size='30' maxlength='50' value='$student_email' required ></td>
												<td style='vertical-align:top;'>
													<b>Phone Number*</b><br />
													Enter the phone number as numbers only. No formatting or other special characters. Your country code is $student_ph_code. If you change your country, 
													the country code for the phone number will be automatically updated.
													<input type='text' class='formInputText' id='chk_phone' name='inp_phone' size='20' maxlenth='20' value='$student_phone' required ></td>
												<td style='vertical-align:top;'>
													<b>Can This Phone Receive Text Messages?</b><br />
													<input type='radio' name='inp_messaging' class='formInputButton' id='chk_messaging' value='Yes' $textMsgYes> Yes<br />
													<input type='radio' name='inp_messaging' class='formInputButton' id='chk_messaging' value='No' $textMsgNo> No</td></tr>
											<tr><td colspan='3'><hr><br />Please enter the address information for where you will be during the semester.</td></tr>
											<tr><td style='vertical-align:top;'>
													<b>City*</b><br />
													<input type='text' class='formInputText' name='inp_city' id='chk_city' size='50' maxlenth='50' value='$student_city'></td>
												<td style='vertical-align:top;'>
													<b>State / Province / Other</b><br />
													<input type='text' class='formInputText' name='inp_state' id='chk_state' size='50' maxlenth='50' value='$student_state'></td>
												<td style='vertical-align:top;'>
													<b>Zip or Postal Code (required for US residents)</b><br />
													<input type='text' class='formInputText' name='inp_zip' id='chk_zip' size='20' maxlenth='20' value='$student_zip_code'></td></tr>
											<tr><td colspan='3'><hr></td></tr>
											<tr><td colspan='3' style='vertical-align:top;'>
													<b>Country*</b><br />
													Please select your country:
											<table style='width:100%;'>
											<tr>
											<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='US|United States' $US_checked><b>United States</b> (continental US, Alaska, or Hawaii)</td>
											<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CA|Canada' $CA_checked><b>Canada</b></td>
											<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='GB|United Kingdom' $GB_checked><b>United Kingdom</b> (England, Scotland, Wales, Northern Ireland)</td>
											<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='IE|Ireland' $IE_checked><b>Ireland</b></td>
											<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='IT|Italy' $IT_checked><b>Italy</b></td>
											</tr><tr>
											<td colspan='5'></td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='AR|Argentina' $AR_checked>Argentina</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='AU|Australia' $AU_checked>Australia</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='AT|Austria' $AT_checked>Austria</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='BE|Belgium' $BE_checked>Belgium</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='BR|Brazil' $BR_checked>Brazil</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CL|Chile' $CL_checked>Chile</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CN|China' $CN_checked>China</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CZ|Czech Republic' $CZ_checked>Czech Republic</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='DK|Denmark' $DK_checked>Denmark</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='FI|Finland' $FI_checked>Finland</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='FI|Finland' $FI_checked>Finland</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='FR|France' $FR_checked>France</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='DE|Germany' $DE_checked>Germany</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='GR|Greece' $GR_checked>Greece</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='IN|India' $IN_checked>India</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='IL|Israel' $IL_checked>Israel</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='JP|Japan' $JP_checked>Japan</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='KR|South Korea' $KR_checked>South Korea</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='MX|Mexico' $MX_checked>Mexico</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='NL|Netherlands' $NL_checked>Netherlands</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='NZ|New Zealand' $NZ_checked>New Zealand</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='NO|Norway' $NO_checked>Norway</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='PH|Philippines' $PH_checked>Philippines</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='PL|Poland' $PL_checked>Poland</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='PT|Portugal' $PT_checked>Portugal</td>
											</tr><tr>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='PR|Puerto Rico' $PR_checked>Puerto Rico</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='KR|Republic of Korea' $KR_checked>Republic of Korea</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='RU|Russia' $RU_checked>Russia</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='ZA|South Africa' $ZA_checked>South Africa</td>
											<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='ES|Spain' $ES_checked>Spain</td>
											</tr><tr>
											<td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='SE|Sweden' $SE_checked>Sweden</td>
											<td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CH|Switzerland' $CH_checked>Switzerland</td>
											<td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='XX|No Selection' $XX_checked>No Selection (<i>You must then select from the list below</i>)</td>
											<td></td>
											<td></td>
											</tr>
											</table>
											<p><b>If your country is not listed above, please select it from the list below:</b></p>

													<select name='inp_countryb' id='chk_countryb' class='formSelect' size='5'>
														<option value='XX|No Selection' $XX_checked >No Selection</option>
														<option value='AD|Andorra' $AD_checked>Andorra</option>
														<option value='AE|United Arab Emirates' $AE_checked>United Arab Emirates</option>
														<option value='AF|Afghanistan' $AF_checked>Afghanistan</option>
														<option value='AG|Antigua and Barbuda' $AG_checked>Antigua and Barbuda</option>
														<option value='AI|Anguilla' $AI_checked>Anguilla</option>
														<option value='AL|Albania' $AL_checked>Albania</option>
														<option value='AM|Armenia' $AM_checked>Armenia</option>
														<option value='AO|Angola' $AO_checked>Angola</option>
														<option value='AQ|Antarctica' $AQ_checked>Antarctica</option>
														<option value='AR|Argentina' $AR_checked>Argentina</option>
														<option value='AS|American Samoa' $AS_checked>American Samoa</option>
														<option value='AT|Austria' $AT_checked>Austria</option>
														<option value='AU|Australia' $AU_checked>Australia</option>
														<option value='AW|Aruba' $AW_checked>Aruba</option>
														<option value='AX|Alland Islands' $AX_checked>Alland Islands</option>
														<option value='AZ|Azerbaijan' $AZ_checked>Azerbaijan</option>
														<option value='BA|Bosnia and Herzegovina' $BA_checked>Bosnia and Herzegovina</option>
														<option value='BB|Barbados' $BB_checked>Barbados</option>
														<option value='BD|Bangladesh' $BD_checked>Bangladesh</option>
														<option value='BE|Belgium' $BE_checked>Belgium</option>
														<option value='BF|Burkina Faso' $BF_checked>Burkina Faso</option>
														<option value='BG|Bulgaria' $BG_checked>Bulgaria</option>
														<option value='BH|Bahrain' $BH_checked>Bahrain</option>
														<option value='BI|Burundi' $BI_checked>Burundi</option>
														<option value='BJ|Benin' $BJ_checked>Benin</option>
														<option value='BL|Saint BarthÃ©lemy' $BL_checked>Saint BarthÃ©lemy</option>
														<option value='BM|Bermuda' $BM_checked>Bermuda</option>
														<option value='BN|Brunei' $BN_checked>Brunei</option>
														<option value='BO|Bolivia - Plurinational State of' $BO_checked>Bolivia - Plurinational State of</option>
														<option value='BQ|Bonaire - Sint Eustatius and Saba' $BQ_checked>Bonaire - Sint Eustatius and Saba</option>
														<option value='BR|Brazil' $BR_checked>Brazil</option>
														<option value='BS|Bahamas' $BS_checked>Bahamas</option>
														<option value='BT|Bhutan' $BT_checked>Bhutan</option>
														<option value='BV|Bouvet Island' $BV_checked>Bouvet Island</option>
														<option value='BW|Botswana' $BW_checked>Botswana</option>
														<option value='BY|Belarus' $BY_checked>Belarus</option>
														<option value='BZ|Belize' $BZ_checked>Belize</option>
														<option value='CA|Canada' $CA_checked>Canada</option>
														<option value='CC|Cocos (Keeling) Islands' $CC_checked>Cocos (Keeling) Islands</option>
														<option value='CD|Congo' $CD_checked>Congo</option>
														<option value='CD|The Democratic Republic of the Congo' $CD_checked>The Democratic Republic of the Congo</option>
														<option value='CF|Central African Republic' $CF_checked>Central African Republic</option>
														<option value='CG|Congo' $CG_checked>Congo</option>
														<option value='CH|Switzerland' $CH_checked>Switzerland</option>
														<option value='CI|Ivory Coast' $CI_checked>Ivory Coast</option>
														<option value='CK|Cook Islands' $CK_checked>Cook Islands</option>
														<option value='CL|Chile' $CL_checked>Chile</option>
														<option value='CM|Cameroon' $CM_checked>Cameroon</option>
														<option value='CN|China' $CN_checked>China</option>
														<option value='CO|Colombia' $CO_checked>Colombia</option>
														<option value='CR|Costa Rica' $CR_checked>Costa Rica</option>
														<option value='CU|Cuba' $CU_checked>Cuba</option>
														<option value='CV|Cape Verde' $CV_checked>Cape Verde</option>
														<option value='CW|Curascao' $CW_checked>Curascao</option>
														<option value='CX|Christmas Island' $CX_checked>Christmas Island</option>
														<option value='CY|Cyprus' $CY_checked>Cyprus</option>
														<option value='CZ|Czech Republic' $CZ_checked>Czech Republic</option>
														<option value='DE|Germany' $DE_checked>Germany</option>
														<option value='DJ|Djibouti' $DJ_checked>Djibouti</option>
														<option value='DK|Denmark' $DK_checked>Denmark</option>
														<option value='DM|Dominica' $DM_checked>Dominica</option>
														<option value='DO|Dominican Republic' $DO_checked>Dominican Republic</option>
														<option value='DZ|Algeria' $DZ_checked>Algeria</option>
														<option value='EC|Ecuador' $EC_checked>Ecuador</option>
														<option value='EE|Estonia' $EE_checked>Estonia</option>
														<option value='EG|Egypt' $EG_checked>Egypt</option>
														<option value='EH|Western Sahara' $EH_checked>Western Sahara</option>
														<option value='ER|Eritrea' $ER_checked>Eritrea</option>
														<option value='ES|Spain' $ES_checked>Spain</option>
														<option value='ET|Ethiopia' $ET_checked>Ethiopia</option>
														<option value='FI|Finland' $FI_checked>Finland</option>
														<option value='FJ|Fiji' $FJ_checked>Fiji</option>
														<option value='FK|Falkland Islands (Malvinas)' $FK_checked>Falkland Islands (Malvinas)</option>
														<option value='FM|Federated States of Micronesia' $FM_checked>Federated States of Micronesia</option>
														<option value='FM|Micronesia' $FM_checked>Micronesia</option>
														<option value='FO|Faroe Islands' $FO_checked>Faroe Islands</option>
														<option value='FR|France' $FR_checked>France</option>
														<option value='GA|Gabon' $GA_checked>Gabon</option>
														<option value='GB|United Kingdom' $GB_checked>United Kingdom</option>
														<option value='GD|Grenada' $GD_checked>Grenada</option>
														<option value='GE|Georgia' $GE_checked>Georgia</option>
														<option value='GF|French Guiana' $GF_checked>French Guiana</option>
														<option value='GG|Guernsey' $GG_checked>Guernsey</option>
														<option value='GH|Ghana' $GH_checked>Ghana</option>
														<option value='GI|Gibraltar' $GI_checked>Gibraltar</option>
														<option value='GL|Greenland' $GL_checked>Greenland</option>
														<option value='GM|Gambia' $GM_checked>Gambia</option>
														<option value='GN|Guinea' $GN_checked>Guinea</option>
														<option value='GP|Guadeloupe' $GP_checked>Guadeloupe</option>
														<option value='GQ|Equatorial Guinea' $GQ_checked>Equatorial Guinea</option>
														<option value='GR|Greece' $GR_checked>Greece</option>
														<option value='GS|South Georgia and the South Sandwich Islands' $GS_checked>South Georgia and the South Sandwich Islands</option>
														<option value='GT|Guatemala' $GT_checked>Guatemala</option>
														<option value='GU|Guam' $GU_checked>Guam</option>
														<option value='GW|Guinea-Bissau' $GW_checked>Guinea-Bissau</option>
														<option value='GY|Guyana' $GY_checked>Guyana</option>
														<option value='HK|Hong Kong' $HK_checked>Hong Kong</option>
														<option value='HM|Heard Island and McDonald Islands' $HM_checked>Heard Island and McDonald Islands</option>
														<option value='HN|Honduras' $HN_checked>Honduras</option>
														<option value='HR|Croatia' $HR_checked>Croatia</option>
														<option value='HT|Haiti' $HT_checked>Haiti</option>
														<option value='HU|Hungary' $HU_checked>Hungary</option>
														<option value='ID|Indonesia' $ID_checked>Indonesia</option>
														<option value='IE|Ireland' $IE_checked>Ireland</option>
														<option value='IL|Israel' $IL_checked>Israel</option>
														<option value='IM|Isle of Man' $IM_checked>Isle of Man</option>
														<option value='IN|India' $IN_checked>India</option>
														<option value='IO|British Indian Ocean Territory' $IO_checked>British Indian Ocean Territory</option>
														<option value='IQ|Iraq' $IQ_checked>Iraq</option>
														<option value='IR|Iran' $IR_checked>Iran</option>
														<option value='IR|Islamic Republic of Iran' $IR_checked>Islamic Republic of Iran</option>
														<option value='IS|Iceland' $IS_checked>Iceland</option>
														<option value='IT|Italy' $IT_checked>Italy</option>
														<option value='JE|Jersey' $JE_checked>Jersey</option>
														<option value='JM|Jamaica' $JM_checked>Jamaica</option>
														<option value='JO|Jordan' $JO_checked>Jordan</option>
														<option value='JP|Japan' $JP_checked>Japan</option>
														<option value='KE|Kenya' $KE_checked>Kenya</option>
														<option value='KG|Kyrgyzstan' $KG_checked>Kyrgyzstan</option>
														<option value='KH|Cambodia' $KH_checked>Cambodia</option>
														<option value='KI|Kiribati' $KI_checked>Kiribati</option>
														<option value='KM|Comoros' $KM_checked>Comoros</option>
														<option value='KN|Saint Kitts and Nevis' $KN_checked>Saint Kitts and Nevis</option>
														<option value='KP|Democratic Peoples Republic of Korea' $KP_checked>Democratic Peoples Republic of Korea</option>
														<option value='KR|Republic of Korea' $KR_checked>Republic of Korea</option>
														<option value='KR|South Korea' $KR_checked>South Korea</option>
														<option value='KW|Kuwait' $KW_checked>Kuwait</option>
														<option value='KY|Cayman Islands' $KY_checked>Cayman Islands</option>
														<option value='KZ|Kazakhstan' $KZ_checked>Kazakhstan</option>
														<option value='LA|Lao Peoples Democratic Republic' $LA_checked>Lao Peoples Democratic Republic</option>
														<option value='LA|Laos' $LA_checked>Laos</option>
														<option value='LB|Lebanon' $LB_checked>Lebanon</option>
														<option value='LC|Saint Lucia' $LC_checked>Saint Lucia</option>
														<option value='LI|Liechtenstein' $LI_checked>Liechtenstein</option>
														<option value='LK|Sri Lanka' $LK_checked>Sri Lanka</option>
														<option value='LR|Liberia' $LR_checked>Liberia</option>
														<option value='LS|Lesotho' $LS_checked>Lesotho</option>
														<option value='LT|Lithuania' $LT_checked>Lithuania</option>
														<option value='LU|Luxembourg' $LU_checked>Luxembourg</option>
														<option value='LV|Latvia' $LV_checked>Latvia</option>
														<option value='LY|Libya' $LY_checked>Libya</option>
														<option value='MA|Morocco' $MA_checked>Morocco</option>
														<option value='MC|Monaco' $MC_checked>Monaco</option>
														<option value='MD|Moldova' $MD_checked>Moldova</option>
														<option value='MD|Republic of Moldova' $MD_checked>Republic of Moldova</option>
														<option value='ME|Montenegro' $ME_checked>Montenegro</option>
														<option value='MF|Saint Martin (French part)' $MF_checked>Saint Martin (French part)</option>
														<option value='MG|Madagascar' $MG_checked>Madagascar</option>
														<option value='MH|Marshall Islands' $MH_checked>Marshall Islands</option>
														<option value='MK|Macedonia' $MK_checked>Macedonia</option>
														<option value='ML|Mali' $ML_checked>Mali</option>
														<option value='MM|Myanmar' $MM_checked>Myanmar</option>
														<option value='MN|Mongolia' $MN_checked>Mongolia</option>
														<option value='MO|Macao' $MO_checked>Macao</option>
														<option value='MP|Northern Mariana Islands' $MP_checked>Northern Mariana Islands</option>
														<option value='MQ|Martinique' $MQ_checked>Martinique</option>
														<option value='MR|Mauritania' $MR_checked>Mauritania</option>
														<option value='MS|Montserrat' $MS_checked>Montserrat</option>
														<option value='MT|Malta' $MT_checked>Malta</option>
														<option value='MU|Mauritius' $MU_checked>Mauritius</option>
														<option value='MV|Maldives' $MV_checked>Maldives</option>
														<option value='MW|Malawi' $MW_checked>Malawi</option>
														<option value='MX|Mexico' $MX_checked>Mexico</option>
														<option value='MY|Malaysia' $MY_checked>Malaysia</option>
														<option value='MZ|Mozambique' $MZ_checked>Mozambique</option>
														<option value='NA|Namibia' $NA_checked>Namibia</option>
														<option value='NC|New Caledonia' $NC_checked>New Caledonia</option>
														<option value='NE|Niger' $NE_checked>Niger</option>
														<option value='NF|Norfolk Island' $NF_checked>Norfolk Island</option>
														<option value='NG|Nigeria' $NG_checked>Nigeria</option>
														<option value='NI|Nicaragua' $NI_checked>Nicaragua</option>
														<option value='NL|Netherlands' $NL_checked>Netherlands</option>
														<option value='NO|Norway' $NO_checked>Norway</option>
														<option value='NP|Nepal' $NP_checked>Nepal</option>
														<option value='NR|Nauru' $NR_checked>Nauru</option>
														<option value='NU|Niue' $NU_checked>Niue</option>
														<option value='NZ|New Zealand' $NZ_checked>New Zealand</option>
														<option value='OM|Oman' $OM_checked>Oman</option>
														<option value='PA|Panama' $PA_checked>Panama</option>
														<option value='PE|Peru' $PE_checked>Peru</option>
														<option value='PF|French Polynesia' $PF_checked>French Polynesia</option>
														<option value='PG|Papua New Guinea' $PG_checked>Papua New Guinea</option>
														<option value='PH|Philippines' $PH_checked>Philippines</option>
														<option value='PK|Pakistan' $PK_checked>Pakistan</option>
														<option value='PL|Poland' $PL_checked>Poland</option>
														<option value='PM|Saint Pierre and Miquelon' $PM_checked>Saint Pierre and Miquelon</option>
														<option value='PN|Pitcairn' $PN_checked>Pitcairn</option>
														<option value='PR|Puerto Rico' $PR_checked>Puerto Rico</option>
														<option value='PS|Palestine' $PS_checked>Palestine</option>
														<option value='PS|State of Palestine' $PS_checked>State of Palestine</option>
														<option value='PT|Portugal' $PT_checked>Portugal</option>
														<option value='PW|Palau' $PW_checked>Palau</option>
														<option value='PY|Paraguay' $PY_checked>Paraguay</option>
														<option value='QA|Qatar' $QA_checked>Qatar</option>
														<option value='RE|Reunion' $RE_checked>Reunion</option>
														<option value='RO|Romania' $RO_checked>Romania</option>
														<option value='RS|Serbia' $RS_checked>Serbia</option>
														<option value='RU|Russia' $RU_checked>Russia</option>
														<option value='RW|Rwanda' $RW_checked>Rwanda</option>
														<option value='SA|Saudi Arabia' $SA_checked>Saudi Arabia</option>
														<option value='SB|Solomon Islands' $SB_checked>Solomon Islands</option>
														<option value='SC|Seychelles' $SC_checked>Seychelles</option>
														<option value='SD|Sudan' $SD_checked>Sudan</option>
														<option value='SE|Sweden' $SE_checked>Sweden</option>
														<option value='SG|Singapore' $SG_checked>Singapore</option>
														<option value='SH|Saint Helena Ascension and Tristan da Cunha' $SH_checked>Saint Helena Ascension and Tristan da Cunha</option>
														<option value='SI|Slovenia' $SI_checked>Slovenia</option>
														<option value='SJ|Svalbard and Jan Mayen' $SJ_checked>Svalbard and Jan Mayen</option>
														<option value='SK|Slovakia' $SK_checked>Slovakia</option>
														<option value='SL|Sierra Leone' $SL_checked>Sierra Leone</option>
														<option value='SM|San Marino' $SM_checked>San Marino</option>
														<option value='SN|Senegal' $SN_checked>Senegal</option>
														<option value='SO|Somalia' $SO_checked>Somalia</option>
														<option value='SR|Suriname' $SR_checked>Suriname</option>
														<option value='SS|South Sudan' $SS_checked>South Sudan</option>
														<option value='ST|Sao Tome and Principe' $ST_checked>Sao Tome and Principe</option>
														<option value='SX|Sint Maarten (Dutch part)' $SX_checked>Sint Maarten (Dutch part)</option>
														<option value='SY|Syrian Arab Republic' $SY_checked>Syrian Arab Republic</option>
														<option value='SZ|Swaziland' $SZ_checked>Swaziland</option>
														<option value='TC|Turks and Caicos Islands' $TC_checked>Turks and Caicos Islands</option>
														<option value='TD|Chad' $TD_checked>Chad</option>
														<option value='TF|French Southern Territories' $TF_checked>French Southern Territories</option>
														<option value='TG|Togo' $TG_checked>Togo</option>
														<option value='TH|Thailand' $TH_checked>Thailand</option>
														<option value='TJ|Tajikistan' $TJ_checked>Tajikistan</option>
														<option value='TK|Tokelau' $TK_checked>Tokelau</option>
														<option value='TL|Timor-Leste' $TL_checked>Timor-Leste</option>
														<option value='TM|Turkmenistan' $TM_checked>Turkmenistan</option>
														<option value='TO|Tonga' $TO_checked>Tonga</option>
														<option value='TR|Turkey' $TR_checked>Turkey</option>
														<option value='TT|Trinidad and Tobago' $TT_checked>Trinidad and Tobago</option>
														<option value='TV|Tuvalu' $TV_checked>Tuvalu</option>
														<option value='TW|Taiwan - Province of China' $TW_checked>Taiwan - Province of China</option>
														<option value='TZ|United Republic of Tanzania' $TZ_checked>United Republic of Tanzania</option>
														<option value='UA|Ukraine' $UA_checked>Ukraine</option>
														<option value='UG|Uganda' $UG_checked>Uganda</option>
														<option value='UM|United States Minor Outlying Islands' $UM_checked>United States Minor Outlying Islands</option>
														<option value='UY|Uruguay' $UY_checked>Uruguay</option>
														<option value='UZ|Uzbekistan' $UZ_checked>Uzbekistan</option>
														<option value='VA|Holy See (Vatican City State)' $VA_checked>Holy See (Vatican City State)</option>
														<option value='VC|Saint Vincent and the Grenadines' $VC_checked>Saint Vincent and the Grenadines</option>
														<option value='VE|Venezuela - Bolivarian Republic of' $VE_checked>Venezuela - Bolivarian Republic of</option>
														<option value='VG|Virgin Islands - British' $VG_checked>Virgin Islands - British</option>
														<option value='VI|Virgin Islands - U.S.' $VI_checked>Virgin Islands - U.S.</option>
														<option value='VN|Viet Nam' $VN_checked>Viet Nam</option>
														<option value='VU|Vanuatu' $VU_checked>Vanuatu</option>
														<option value='WF|Wallis and Futuna' $WF_checked>Wallis and Futuna</option>
														<option value='WS|Samoa' $WS_checked>Samoa</option>
														<option value='YE|Yemen' $YE_checked>Yemen</option>
														<option value='YT|Mayotte' $YT_checked>Mayotte</option>
														<option value='ZA|South Africa' $ZA_checked>South Africa</option>
														<option value='ZM|Zambia' $ZM_checked>Zambia</option>
														<option value='ZW|Zimbabwe' $ZW_checked>Zimbabwe</option>
											</select></td>
											</tr>
											<tr><td colspan='3'><hr></td></tr>
											<tr><td style='vertical-align:top;'>Do you use any of these messaging services? If so, please enter your <b>user ID</b> for the service that 
											you use, otherwise leave empty. DO NOT enter Yes or No. <em>Note: This is not required, but does provide your advisor with an alternate method to contact 
											you</em></td>
												<td style='vertical-align:top;'>
													WhatsApp<br />
													<input type='text' class='formInputText' name='inp_whatsapp' id='chk_whatsapp' size='20' maxlength='20' value='$student_whatsapp'><br />
													Telegram<br />
													<input type='text' class='formInputText' name='inp_telegram' id='chk_telegram' size='20' maxlength='20' value='$student_telegram'></td>		
													<td style='vertical-align:top;'>
													Signal<br />
													<input type='text' class='formInputText' name='inp_signal' id='chk_signal' size='20' maxlength='20' value='$student_signal'><br />
													Facebook Messenger<br >
													<input type='text' class='formInputText' name='inp_messenger' id='chk_messenger' size='20' maxlength='20' value='$student_messenger'></td></tr>		
											<tr><td colspan='3'><hr></td></tr>
											<tr><td style='vertical-align:top;'>
													<b>Youth?</b><br />Select 'Yes' if <b>20 years of age or younger</b><br />
													<input type='radio' class='formInputButton' name='inp_youth' id='chk_youth' value='Yes' $youthYesChecked > Yes<br />
													<input type='radio' class='formInutButton' name='inp_youth' id='chk_youth' value='No' $youthNoChecked > No</td>
												<td style='vertical-align:top;'>	
													If 20 years of age or younger, please enter your age<br />
													<input type='text' name='inp_age' id='chk_age' size='5' class='formInputText' maxlength='5' value='$student_age'></td>
												<td style='vertical-align:top;'>
													If you are 17 years of age or younger, please provide a parent or guardian name and email address<br />
													Parent or guardian Name<br />
													<input type='text' name='inp_student_parent' id='chk_student_parent' size='30' class='formInputText' maxlength='30' value='$student_student_parent' ><br />
													Parent or Guardian email<br />
													<input type='text' name='inp_student_parent_email' id='chk_student_parent_email' size='40' class='formInputText' maxlength='40' value='$student_student_parent_email' ></td></tr>
											<tr><td colspan='3'><hr></td></tr>
											<tr><td colspan='3'>You <span style='color:red;'><b>MUST</b></span> click <b>Next</b> to review and/or update your class preferences.</td></tr>	
											<tr><td colspan='3'><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Next' /></td></tr>
											</table>";
				}
			}
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
				// setup field defaults
				$student_call_sign				= ''; 
				$student_first_name				= ''; 
				$student_last_name				= ''; 
				$student_email					= $inp_email; 
				$student_phone					= $inp_phone; 
				$student_ph_code				= $inp_ph_code;
				$student_messaging				= ''; 
				$student_city					= ''; 
				$student_state					= ''; 
				$student_zip_code				= ''; 
				$student_country				= ''; 
				$student_youth					= ''; 
				$student_age					= ''; 
				$student_student_parent			= '';
				$student_student_parent_email	= '';
				$student_whatsapp				= '';
				$student_telegram				= '';
				$student_signal					= '';
				$student_messenger				= '';

				$textMsgYes						= '';
				$textMsgNo						= '';
				$youthYesChecked				= '';
				$youthNoChecked					= '';
	

				// see if there is a record in the database
				$sql	= "select * from $studentTableName 
							where call_sign = '$inp_callsign' 
							order by date_created DESC 
							limit 1";
				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						if (!$doDebug) {
							return $content;
						}
					}

					$numSRows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numSRows rows<br />";
					}
					if ($numSRows > 0) {
						foreach ($wpw1_cwa_student as $studentRow) {
							$student_ID								= $studentRow->student_id;
							$student_call_sign						= strtoupper($studentRow->call_sign);
							$student_first_name						= $studentRow->first_name;
							$student_last_name						= stripslashes($studentRow->last_name);
							$student_email  						= strtolower(strtolower($studentRow->email));
							$student_phone  						= $studentRow->phone;
							$student_ph_code						= $studentRow->ph_code;
							$student_city  							= $studentRow->city;
							$student_state  						= $studentRow->state;
							$student_zip_code  						= $studentRow->zip_code;
							$student_country  						= $studentRow->country;
							$student_country_code					= $studentRow->country_code;
							$student_whatsapp						= $studentRow->whatsapp_app;
							$student_signal							= $studentRow->signal_app;
							$student_telegram						= $studentRow->telegram_app;
							$student_messenger						= $studentRow->messenger_app;					
							$student_youth  						= $studentRow->youth;
							$student_age  							= $studentRow->age;
							$student_student_parent 				= $studentRow->student_parent;
							$student_student_parent_email  			= strtolower($studentRow->student_parent_email);

							$student_last_name 						= no_magic_quotes($student_last_name);
			



							if ($student_messaging == 'Yes') {
								$textMsgYes			= "checked='checked'";
							} elseif ($student_messaging == 'No') {
								$textMsgNo			= "checked='checked'";
							} else {
								$textMsgYes			= "checked='checked'";
							}
							if ($student_youth == 'Yes') {
								$youthYesChecked	= "checked='checked'";
							} elseif ($student_youth == 'No') {
								$youthNoChecked		= "checked='checked'";
							}

							if (in_array($student_country_code,$countryCheckedArray)) {
								${$student_country_code .'_checked'}	= 'checked';
								$XX_checked								= 'checked';
							} else {
								${$student_country_code .'_checked'}	= 'checked';
								$XX_checked								= '';
							}
							if ($doDebug) { 
								echo "have set $student_country_code _checked to ${$student_country_code . '_checked'}<br />";
							}
						}
					} else {			/// no record found
						$XX_checked	= 'checked';
					}
				}
				$newInput			= 'Y';
				$daysToGo			= days_to_semester($inp_semester);
				if ($doDebug) {
					echo "daysToGo: $daysToGo<br />";
				}
				///// if registering after the assignments to classes has been made, tell the student
				$waitListMsg		= "";
				$waiting			= "";
				if ($daysToGo > 0 && $daysToGo <= 22) {
					$waitListMsg	= "<table style='border:4px solid red;'><tr><td>
										<span style='font-size:12pt;'>Student assignment to classes has already occurred for the $nextSemester semester. If you 
										sign up for the $nextSemester semester, you will be placed on a waiting list. Students do drop 
										out and CW Academy pulls replacement students from the waiting list. If you aren't selected 
										from the waiting list, your registration will be automatically moved to the $semesterTwo 
										semester and you will be given heightened priority for assignment to a class.</span></td></tr></table>";
					$waitingList	= TRUE;
					$waiting		= "<b>(Waiting List)</b>";
					if ($doDebug) {
						echo "daysToGo <= 20 and next semester so will display the waitListMsg<br />";
					}
				} else {
					$waitListMsg	= "<p>Be advised that the process to assign students to classes will take 
										place about twenty days before the start of the semester and will be based on the actual classes 
										available at that time.</p>";

				}
				if ($errorString == '') {
					$content	.= "$waitListMsg
									<p>Please fill out the form below to register for a CW Academy class. 
									If have previously signed up, the form is filled in with your previous 
									information. <b>Please check that information!</b></p><p>
									Students will be assigned to classes in first-come, first-serve order. However, whether or not 
									you will be assigned to a class depends on the number of students who register and the number 
									of classes available.</p>
									<p><b>For Military or Civilians who will be deployed to an APO address:</b> Please enter 'APO' as your 
									city, your APO as your zip code, and then select the country where you are or will be deployed.</p>";
				} else {
					$content	.= "<p>Please correct the following errors<br /><br />
									$errorString</p>";
				}
					
				$content	.= "<form method='post' action='$theURL' 
								name='newregistrationform' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='3'>
								<input type='hidden' name='demonstration' value='$demonstration'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<input type='hidden' name='inp_email' value='$inp_email'>
								<input type='hidden' name='inp_phone' value='$inp_phone'>
								<input type='hidden' name='waitingList' value='$waitingList'>
								<input type='hidden' name='inp_callsign' value='$inp_callsign'>
								<input type='hidden' name='inp_semester' value='$inp_semester'>
								<input type='hidden' name='inp_level' value='$inp_level'>
								<input type='hidden' name='token' value='$token'>
								<input type='hidden' id='browser_timezone_id' name='browser_timezone_id' value='$browser_timezone_id' />
								<input type='hidden' name='student_id' value='$student_ID'>
								<p>* indicates a required field.</p>
								<table style='width:100%'>
								<tr><td style='vertical-align:top;'>
										<b>Semester</b><br />
										$inp_semester $waiting</td>
									<td style='vertical-align:top;'>
										<b>Class Level Based on the Self Assessment</b><br />
										$inp_level</td>
									<td style='vertical-align:top;'>
										&nbsp;</td></tr>
								<tr><td colspan='3'><hr></td></tr>
								<tr><td style='width:300px;vertical-align:top;'>
										<b>Call Sign*</b><br />
										$inp_callsign</td>
									<td style='width:300px;vertical-align:top;'>
										<b>Last Name*</b><br />
										<input type='text' class='formInputText' id='chk_lastname' name='inp_lastname' size='50' maxlength='50' value=\"$student_last_name\" required ></td>
									<td style='width:300px;vertical-align:top;'>
										<b>First Name*</b><br />
										<input type='text' class='formInputText' id='chk_firstname' name='inp_firstname' size='50' maxlength='50' value='$student_first_name' required ></td></tr>
								<tr><td colspan='3'><hr></td></tr>
								<tr><td style='vertical-align:top;'>
										<b>Email*</b><br />
										<input type='text' class='formInputText' id='chk_email' name='inp_email' size='30' maxlength='50' value='$student_email' required ></td>
									<td style='vertical-align:top;'>
										<b>Phone Number*</b><br />
										Enter the phone number as numbers only. No formatting or other special characters. Your country code is $student_ph_code. If you change your country, 
										the country code for the phone number will be automatically updated.
										<input type='text' class='formInputText' id='chk_phone' name='inp_phone' size='20' maxlenth='20' value='$student_phone' required ></td>
									<td style='vertical-align:top;'>
										<b>Can This Phone Receive Text Messages?</b><br />
										<input type='radio' name='inp_messaging' class='formInputButton' id='chk_messaging' value='Yes' checked> Yes<br />
										<input type='radio' name='inp_messaging' class='formInputButton' id='chk_messaging' value='No' > No</td></tr>
								<tr><td colspan='3'><hr><br />Please enter the address information for where you will be during the semester.</td></tr>
								<tr><td style='vertical-align:top;'>
										<b>City*</b><br />
										<input type='text' class='formInputText' name='inp_city' id='chk_city' size='50' maxlenth='50' value='$student_city'></td>
									<td style='vertical-align:top;'>
										<b>State / Province / Other</b><br />
										<input type='text' class='formInputText' name='inp_state' id='chk_state' size='50' maxlenth='50' value='$student_state'></td>
									<td style='vertical-align:top;'>
										<b>Zip or Postal Code (required for US residents)</b><br />
										<input type='text' class='formInputText' name='inp_zip' id='chk_zip' size='20' maxlenth='20' value='$student_zip_code'></td></tr>
								<tr><td colspan='3'><hr></td></tr>
								<tr><td colspan='3' style='vertical-align:top;'>
										<b>Country*</b><br />
										Please select your country:
										<table style='width:100%;'>
										<tr>
										<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='US|United States' $US_checked><b>United States</b> (continental US, Alaska, or Hawaii)</td>
										<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CA|Canada' $CA_checked><b>Canada</b></td>
										<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='GB|United Kingdom' $GB_checked><b>United Kingdom</b> (England, Scotland, Wales, Northern Ireland)</td>
										<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='IE|Ireland' $IE_checked><b>Ireland</b></td>
										<td style='width:200px;vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='IT|Italy' $IT_checked><b>Italy</b></td>
										</tr><tr>
										<td colspan='5'></td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='AR|Argentina' $AR_checked>Argentina</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='AU|Australia' $AU_checked>Australia</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='AT|Austria' $AT_checked>Austria</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='BE|Belgium' $BE_checked>Belgium</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='BR|Brazil' $BR_checked>Brazil</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CL|Chile' $CL_checked>Chile</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CN|China' $CN_checked>China</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CZ|Czech Republic' $CZ_checked>Czech Republic</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='DK|Denmark' $DK_checked>Denmark</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='FI|Finland' $FI_checked>Finland</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='FI|Finland' $FI_checked>Finland</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='FR|France' $FR_checked>France</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='DE|Germany' $DE_checked>Germany</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='GR|Greece' $GR_checked>Greece</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='IN|India' $IN_checked>India</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='IL|Israel' $IL_checked>Israel</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='JP|Japan' $JP_checked>Japan</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='KR|South Korea' $KR_checked>South Korea</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='MX|Mexico' $MX_checked>Mexico</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='NL|Netherlands' $NL_checked>Netherlands</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='NZ|New Zealand' $NZ_checked>New Zealand</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='NO|Norway' $NO_checked>Norway</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='PH|Philippines' $PH_checked>Philippines</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='PL|Poland' $PL_checked>Poland</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='PT|Portugal' $PT_checked>Portugal</td>
										</tr><tr>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='PR|Puerto Rico' $PR_checked>Puerto Rico</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='KR|Republic of Korea' $KR_checked>Republic of Korea</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='RU|Russia' $RU_checked>Russia</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='ZA|South Africa' $ZA_checked>South Africa</td>
										<td><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='ES|Spain' $ES_checked>Spain</td>
										</tr><tr>
										<td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='SE|Sweden' $SE_checked>Sweden</td>
										<td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='CH|Switzerland' $CH_checked>Switzerland</td>
										<td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_countrya' name='inp_countrya'  value='XX|No Selection' $XX_checked>No Selection (<i>You must select a country</i>)</td>
										<td></td>
										<td></td>
										</tr>
								</table>
								<p><b>If your country is not listed above, please select it from the list below:</b></p>

										<select name='inp_countryb' id='chk_countryb' class='formSelect' size='5'>
											<option value='XX|No Selection' selected >No Selection</option>
											<option value='AD|Andorra' $AD_checked>Andorra</option>
											<option value='AE|United Arab Emirates' $AE_checked>United Arab Emirates</option>
											<option value='AF|Afghanistan' $AF_checked>Afghanistan</option>
											<option value='AG|Antigua and Barbuda' $AG_checked>Antigua and Barbuda</option>
											<option value='AI|Anguilla' $AI_checked>Anguilla</option>
											<option value='AL|Albania' $AL_checked>Albania</option>
											<option value='AM|Armenia' $AM_checked>Armenia</option>
											<option value='AO|Angola' $AO_checked>Angola</option>
											<option value='AQ|Antarctica' $AQ_checked>Antarctica</option>
											<option value='AR|Argentina' $AR_checked>Argentina</option>
											<option value='AS|American Samoa' $AS_checked>American Samoa</option>
											<option value='AT|Austria' $AT_checked>Austria</option>
											<option value='AU|Australia' $AU_checked>Australia</option>
											<option value='AW|Aruba' $AW_checked>Aruba</option>
											<option value='AX|Alland Islands' $AX_checked>Alland Islands</option>
											<option value='AZ|Azerbaijan' $AZ_checked>Azerbaijan</option>
											<option value='BA|Bosnia and Herzegovina' $BA_checked>Bosnia and Herzegovina</option>
											<option value='BB|Barbados' $BB_checked>Barbados</option>
											<option value='BD|Bangladesh' $BD_checked>Bangladesh</option>
											<option value='BE|Belgium' $BE_checked>Belgium</option>
											<option value='BF|Burkina Faso' $BF_checked>Burkina Faso</option>
											<option value='BG|Bulgaria' $BG_checked>Bulgaria</option>
											<option value='BH|Bahrain' $BH_checked>Bahrain</option>
											<option value='BI|Burundi' $BI_checked>Burundi</option>
											<option value='BJ|Benin' $BJ_checked>Benin</option>
											<option value='BL|Saint BarthÃ©lemy' $BL_checked>Saint BarthÃ©lemy</option>
											<option value='BM|Bermuda' $BM_checked>Bermuda</option>
											<option value='BN|Brunei' $BN_checked>Brunei</option>
											<option value='BO|Bolivia - Plurinational State of' $BO_checked>Bolivia - Plurinational State of</option>
											<option value='BQ|Bonaire - Sint Eustatius and Saba' $BQ_checked>Bonaire - Sint Eustatius and Saba</option>
											<option value='BR|Brazil' $BR_checked>Brazil</option>
											<option value='BS|Bahamas' $BS_checked>Bahamas</option>
											<option value='BT|Bhutan' $BT_checked>Bhutan</option>
											<option value='BV|Bouvet Island' $BV_checked>Bouvet Island</option>
											<option value='BW|Botswana' $BW_checked>Botswana</option>
											<option value='BY|Belarus' $BY_checked>Belarus</option>
											<option value='BZ|Belize' $BZ_checked>Belize</option>
											<option value='CA|Canada' $CA_checked>Canada</option>
											<option value='CC|Cocos (Keeling) Islands' $CC_checked>Cocos (Keeling) Islands</option>
											<option value='CD|Congo' $CD_checked>Congo</option>
											<option value='CD|The Democratic Republic of the Congo' $CD_checked>The Democratic Republic of the Congo</option>
											<option value='CF|Central African Republic' $CF_checked>Central African Republic</option>
											<option value='CG|Congo' $CG_checked>Congo</option>
											<option value='CH|Switzerland' $CH_checked>Switzerland</option>
											<option value='CI|Ivory Coast' $CI_checked>Ivory Coast</option>
											<option value='CK|Cook Islands' $CK_checked>Cook Islands</option>
											<option value='CL|Chile' $CL_checked>Chile</option>
											<option value='CM|Cameroon' $CM_checked>Cameroon</option>
											<option value='CN|China' $CN_checked>China</option>
											<option value='CO|Colombia' $CO_checked>Colombia</option>
											<option value='CR|Costa Rica' $CR_checked>Costa Rica</option>
											<option value='CU|Cuba' $CU_checked>Cuba</option>
											<option value='CV|Cape Verde' $CV_checked>Cape Verde</option>
											<option value='CW|Curascao' $CW_checked>Curascao</option>
											<option value='CX|Christmas Island' $CX_checked>Christmas Island</option>
											<option value='CY|Cyprus' $CY_checked>Cyprus</option>
											<option value='CZ|Czech Republic' $CZ_checked>Czech Republic</option>
											<option value='DE|Germany' $DE_checked>Germany</option>
											<option value='DJ|Djibouti' $DJ_checked>Djibouti</option>
											<option value='DK|Denmark' $DK_checked>Denmark</option>
											<option value='DM|Dominica' $DM_checked>Dominica</option>
											<option value='DO|Dominican Republic' $DO_checked>Dominican Republic</option>
											<option value='DZ|Algeria' $DZ_checked>Algeria</option>
											<option value='EC|Ecuador' $EC_checked>Ecuador</option>
											<option value='EE|Estonia' $EE_checked>Estonia</option>
											<option value='EG|Egypt' $EG_checked>Egypt</option>
											<option value='EH|Western Sahara' $EH_checked>Western Sahara</option>
											<option value='ER|Eritrea' $ER_checked>Eritrea</option>
											<option value='ES|Spain' $ES_checked>Spain</option>
											<option value='ET|Ethiopia' $ET_checked>Ethiopia</option>
											<option value='FI|Finland' $FI_checked>Finland</option>
											<option value='FJ|Fiji' $FJ_checked>Fiji</option>
											<option value='FK|Falkland Islands (Malvinas)' $FK_checked>Falkland Islands (Malvinas)</option>
											<option value='FM|Federated States of Micronesia' $FM_checked>Federated States of Micronesia</option>
											<option value='FM|Micronesia' $FM_checked>Micronesia</option>
											<option value='FO|Faroe Islands' $FO_checked>Faroe Islands</option>
											<option value='FR|France' $FR_checked>France</option>
											<option value='GA|Gabon' $GA_checked>Gabon</option>
											<option value='GB|United Kingdom' $GB_checked>United Kingdom</option>
											<option value='GD|Grenada' $GD_checked>Grenada</option>
											<option value='GE|Georgia' $GE_checked>Georgia</option>
											<option value='GF|French Guiana' $GF_checked>French Guiana</option>
											<option value='GG|Guernsey' $GG_checked>Guernsey</option>
											<option value='GH|Ghana' $GH_checked>Ghana</option>
											<option value='GI|Gibraltar' $GI_checked>Gibraltar</option>
											<option value='GL|Greenland' $GL_checked>Greenland</option>
											<option value='GM|Gambia' $GM_checked>Gambia</option>
											<option value='GN|Guinea' $GN_checked>Guinea</option>
											<option value='GP|Guadeloupe' $GP_checked>Guadeloupe</option>
											<option value='GQ|Equatorial Guinea' $GQ_checked>Equatorial Guinea</option>
											<option value='GR|Greece' $GR_checked>Greece</option>
											<option value='GS|South Georgia and the South Sandwich Islands' $GS_checked>South Georgia and the South Sandwich Islands</option>
											<option value='GT|Guatemala' $GT_checked>Guatemala</option>
											<option value='GU|Guam' $GU_checked>Guam</option>
											<option value='GW|Guinea-Bissau' $GW_checked>Guinea-Bissau</option>
											<option value='GY|Guyana' $GY_checked>Guyana</option>
											<option value='HK|Hong Kong' $HK_checked>Hong Kong</option>
											<option value='HM|Heard Island and McDonald Islands' $HM_checked>Heard Island and McDonald Islands</option>
											<option value='HN|Honduras' $HN_checked>Honduras</option>
											<option value='HR|Croatia' $HR_checked>Croatia</option>
											<option value='HT|Haiti' $HT_checked>Haiti</option>
											<option value='HU|Hungary' $HU_checked>Hungary</option>
											<option value='ID|Indonesia' $ID_checked>Indonesia</option>
											<option value='IE|Ireland' $IE_checked>Ireland</option>
											<option value='IL|Israel' $IL_checked>Israel</option>
											<option value='IM|Isle of Man' $IM_checked>Isle of Man</option>
											<option value='IN|India' $IN_checked>India</option>
											<option value='IO|British Indian Ocean Territory' $IO_checked>British Indian Ocean Territory</option>
											<option value='IQ|Iraq' $IQ_checked>Iraq</option>
											<option value='IR|Iran' $IR_checked>Iran</option>
											<option value='IR|Islamic Republic of Iran' $IR_checked>Islamic Republic of Iran</option>
											<option value='IS|Iceland' $IS_checked>Iceland</option>
											<option value='IT|Italy' $IT_checked>Italy</option>
											<option value='JE|Jersey' $JE_checked>Jersey</option>
											<option value='JM|Jamaica' $JM_checked>Jamaica</option>
											<option value='JO|Jordan' $JO_checked>Jordan</option>
											<option value='JP|Japan' $JP_checked>Japan</option>
											<option value='KE|Kenya' $KE_checked>Kenya</option>
											<option value='KG|Kyrgyzstan' $KG_checked>Kyrgyzstan</option>
											<option value='KH|Cambodia' $KH_checked>Cambodia</option>
											<option value='KI|Kiribati' $KI_checked>Kiribati</option>
											<option value='KM|Comoros' $KM_checked>Comoros</option>
											<option value='KN|Saint Kitts and Nevis' $KN_checked>Saint Kitts and Nevis</option>
											<option value='KP|Democratic Peoples Republic of Korea' $KP_checked>Democratic Peoples Republic of Korea</option>
											<option value='KR|Republic of Korea' $KR_checked>Republic of Korea</option>
											<option value='KR|South Korea' $KR_checked>South Korea</option>
											<option value='KW|Kuwait' $KW_checked>Kuwait</option>
											<option value='KY|Cayman Islands' $KY_checked>Cayman Islands</option>
											<option value='KZ|Kazakhstan' $KZ_checked>Kazakhstan</option>
											<option value='LA|Lao Peoples Democratic Republic' $LA_checked>Lao Peoples Democratic Republic</option>
											<option value='LA|Laos' $LA_checked>Laos</option>
											<option value='LB|Lebanon' $LB_checked>Lebanon</option>
											<option value='LC|Saint Lucia' $LC_checked>Saint Lucia</option>
											<option value='LI|Liechtenstein' $LI_checked>Liechtenstein</option>
											<option value='LK|Sri Lanka' $LK_checked>Sri Lanka</option>
											<option value='LR|Liberia' $LR_checked>Liberia</option>
											<option value='LS|Lesotho' $LS_checked>Lesotho</option>
											<option value='LT|Lithuania' $LT_checked>Lithuania</option>
											<option value='LU|Luxembourg' $LU_checked>Luxembourg</option>
											<option value='LV|Latvia' $LV_checked>Latvia</option>
											<option value='LY|Libya' $LY_checked>Libya</option>
											<option value='MA|Morocco' $MA_checked>Morocco</option>
											<option value='MC|Monaco' $MC_checked>Monaco</option>
											<option value='MD|Moldova' $MD_checked>Moldova</option>
											<option value='MD|Republic of Moldova' $MD_checked>Republic of Moldova</option>
											<option value='ME|Montenegro' $ME_checked>Montenegro</option>
											<option value='MF|Saint Martin (French part)' $MF_checked>Saint Martin (French part)</option>
											<option value='MG|Madagascar' $MG_checked>Madagascar</option>
											<option value='MH|Marshall Islands' $MH_checked>Marshall Islands</option>
											<option value='MK|Macedonia' $MK_checked>Macedonia</option>
											<option value='ML|Mali' $ML_checked>Mali</option>
											<option value='MM|Myanmar' $MM_checked>Myanmar</option>
											<option value='MN|Mongolia' $MN_checked>Mongolia</option>
											<option value='MO|Macao' $MO_checked>Macao</option>
											<option value='MP|Northern Mariana Islands' $MP_checked>Northern Mariana Islands</option>
											<option value='MQ|Martinique' $MQ_checked>Martinique</option>
											<option value='MR|Mauritania' $MR_checked>Mauritania</option>
											<option value='MS|Montserrat' $MS_checked>Montserrat</option>
											<option value='MT|Malta' $MT_checked>Malta</option>
											<option value='MU|Mauritius' $MU_checked>Mauritius</option>
											<option value='MV|Maldives' $MV_checked>Maldives</option>
											<option value='MW|Malawi' $MW_checked>Malawi</option>
											<option value='MX|Mexico' $MX_checked>Mexico</option>
											<option value='MY|Malaysia' $MY_checked>Malaysia</option>
											<option value='MZ|Mozambique' $MZ_checked>Mozambique</option>
											<option value='NA|Namibia' $NA_checked>Namibia</option>
											<option value='NC|New Caledonia' $NC_checked>New Caledonia</option>
											<option value='NE|Niger' $NE_checked>Niger</option>
											<option value='NF|Norfolk Island' $NF_checked>Norfolk Island</option>
											<option value='NG|Nigeria' $NG_checked>Nigeria</option>
											<option value='NI|Nicaragua' $NI_checked>Nicaragua</option>
											<option value='NL|Netherlands' $NL_checked>Netherlands</option>
											<option value='NO|Norway' $NO_checked>Norway</option>
											<option value='NP|Nepal' $NP_checked>Nepal</option>
											<option value='NR|Nauru' $NR_checked>Nauru</option>
											<option value='NU|Niue' $NU_checked>Niue</option>
											<option value='NZ|New Zealand' $NZ_checked>New Zealand</option>
											<option value='OM|Oman' $OM_checked>Oman</option>
											<option value='PA|Panama' $PA_checked>Panama</option>
											<option value='PE|Peru' $PE_checked>Peru</option>
											<option value='PF|French Polynesia' $PF_checked>French Polynesia</option>
											<option value='PG|Papua New Guinea' $PG_checked>Papua New Guinea</option>
											<option value='PH|Philippines' $PH_checked>Philippines</option>
											<option value='PK|Pakistan' $PK_checked>Pakistan</option>
											<option value='PL|Poland' $PL_checked>Poland</option>
											<option value='PM|Saint Pierre and Miquelon' $PM_checked>Saint Pierre and Miquelon</option>
											<option value='PN|Pitcairn' $PN_checked>Pitcairn</option>
											<option value='PR|Puerto Rico' $PR_checked>Puerto Rico</option>
											<option value='PS|Palestine' $PS_checked>Palestine</option>
											<option value='PS|State of Palestine' $PS_checked>State of Palestine</option>
											<option value='PT|Portugal' $PT_checked>Portugal</option>
											<option value='PW|Palau' $PW_checked>Palau</option>
											<option value='PY|Paraguay' $PY_checked>Paraguay</option>
											<option value='QA|Qatar' $QA_checked>Qatar</option>
											<option value='RE|Reunion' $RE_checked>Reunion</option>
											<option value='RO|Romania' $RO_checked>Romania</option>
											<option value='RS|Serbia' $RS_checked>Serbia</option>
											<option value='RU|Russia' $RU_checked>Russia</option>
											<option value='RW|Rwanda' $RW_checked>Rwanda</option>
											<option value='SA|Saudi Arabia' $SA_checked>Saudi Arabia</option>
											<option value='SB|Solomon Islands' $SB_checked>Solomon Islands</option>
											<option value='SC|Seychelles' $SC_checked>Seychelles</option>
											<option value='SD|Sudan' $SD_checked>Sudan</option>
											<option value='SE|Sweden' $SE_checked>Sweden</option>
											<option value='SG|Singapore' $SG_checked>Singapore</option>
											<option value='SH|Saint Helena Ascension and Tristan da Cunha' $SH_checked>Saint Helena Ascension and Tristan da Cunha</option>
											<option value='SI|Slovenia' $SI_checked>Slovenia</option>
											<option value='SJ|Svalbard and Jan Mayen' $SJ_checked>Svalbard and Jan Mayen</option>
											<option value='SK|Slovakia' $SK_checked>Slovakia</option>
											<option value='SL|Sierra Leone' $SL_checked>Sierra Leone</option>
											<option value='SM|San Marino' $SM_checked>San Marino</option>
											<option value='SN|Senegal' $SN_checked>Senegal</option>
											<option value='SO|Somalia' $SO_checked>Somalia</option>
											<option value='SR|Suriname' $SR_checked>Suriname</option>
											<option value='SS|South Sudan' $SS_checked>South Sudan</option>
											<option value='ST|Sao Tome and Principe' $ST_checked>Sao Tome and Principe</option>
											<option value='SX|Sint Maarten (Dutch part)' $SX_checked>Sint Maarten (Dutch part)</option>
											<option value='SY|Syrian Arab Republic' $SY_checked>Syrian Arab Republic</option>
											<option value='SZ|Swaziland' $SZ_checked>Swaziland</option>
											<option value='TC|Turks and Caicos Islands' $TC_checked>Turks and Caicos Islands</option>
											<option value='TD|Chad' $TD_checked>Chad</option>
											<option value='TF|French Southern Territories' $TF_checked>French Southern Territories</option>
											<option value='TG|Togo' $TG_checked>Togo</option>
											<option value='TH|Thailand' $TH_checked>Thailand</option>
											<option value='TJ|Tajikistan' $TJ_checked>Tajikistan</option>
											<option value='TK|Tokelau' $TK_checked>Tokelau</option>
											<option value='TL|Timor-Leste' $TL_checked>Timor-Leste</option>
											<option value='TM|Turkmenistan' $TM_checked>Turkmenistan</option>
											<option value='TO|Tonga' $TO_checked>Tonga</option>
											<option value='TR|Turkey' $TR_checked>Turkey</option>
											<option value='TT|Trinidad and Tobago' $TT_checked>Trinidad and Tobago</option>
											<option value='TV|Tuvalu' $TV_checked>Tuvalu</option>
											<option value='TW|Taiwan - Province of China' $TW_checked>Taiwan - Province of China</option>
											<option value='TZ|United Republic of Tanzania' $TZ_checked>United Republic of Tanzania</option>
											<option value='UA|Ukraine' $UA_checked>Ukraine</option>
											<option value='UG|Uganda' $UG_checked>Uganda</option>
											<option value='UM|United States Minor Outlying Islands' $UM_checked>United States Minor Outlying Islands</option>
											<option value='UY|Uruguay' $UY_checked>Uruguay</option>
											<option value='UZ|Uzbekistan' $UZ_checked>Uzbekistan</option>
											<option value='VA|Holy See (Vatican City State)' $VA_checked>Holy See (Vatican City State)</option>
											<option value='VC|Saint Vincent and the Grenadines' $VC_checked>Saint Vincent and the Grenadines</option>
											<option value='VE|Venezuela - Bolivarian Republic of' $VE_checked>Venezuela - Bolivarian Republic of</option>
											<option value='VG|Virgin Islands - British' $VG_checked>Virgin Islands - British</option>
											<option value='VI|Virgin Islands - U.S.' $VI_checked>Virgin Islands - U.S.</option>
											<option value='VN|Viet Nam' $VN_checked>Viet Nam</option>
											<option value='VU|Vanuatu' $VU_checked>Vanuatu</option>
											<option value='WF|Wallis and Futuna' $WF_checked>Wallis and Futuna</option>
											<option value='WS|Samoa' $WS_checked>Samoa</option>
											<option value='YE|Yemen' $YE_checked>Yemen</option>
											<option value='YT|Mayotte' $YT_checked>Mayotte</option>
											<option value='ZA|South Africa' $ZA_checked>South Africa</option>
											<option value='ZM|Zambia' $ZM_checked>Zambia</option>
											<option value='ZW|Zimbabwe' $ZW_checked>Zimbabwe</option>
								</select></td>
								</tr>
								<tr><td colspan='3'><hr></td></tr>
								<tr><td style='vertical-align:top;'>Do you use any of these messaging services? If so, please enter your <b>user ID</b> for the service that 
								you use, otherwise leave empty. DO NOT enter Yes or No. <em>Note: This is not required, but does provide your advisor with an alternate method to contact 
								you</em></td>
									<td style='vertical-align:top;'>
										WhatsApp<br />
										<input type='text' class='formInputText' name='inp_whatsapp' id='chk_whatsapp' size='20' maxlength='20' value='$student_whatsapp'><br />
										Telegram<br />
										<input type='text' class='formInputText' name='inp_telegram' id='chk_telegram' size='20' maxlength='20' value='$student_telegram'></td>		
										<td style='vertical-align:top;'>
										Signal<br />
										<input type='text' class='formInputText' name='inp_signal' id='chk_signal' size='20' maxlength='20' value='$student_signal'><br />
										Facebook Messenger<br >
										<input type='text' class='formInputText' name='inp_messenger' id='chk_messenger' size='20' maxlength='20' value='$student_messenger'></td></tr>		
								<tr><td colspan='3'><hr></td></tr>
								<tr><td style='vertical-align:top;'>
										<b>Youth?</b><br />Select 'Yes' if <b>20 years of age or younger</b><br />
										<input type='radio' class='formInputButton' name='inp_youth' id='chk_youth' value='Yes' > Yes<br />
										<input type='radio' class='formInutButton' name='inp_youth' id='chk_youth' value='No' checked > No</td>
									<td style='vertical-align:top;'>	
										If 20 years of age or younger, please enter your age<br />
										<input type='text' name='inp_age' id='chk_age' size='5' class='formInputText' maxlength='5' value='$student_age'></td>
									<td style='vertical-align:top;'>
										If you are 17 years of age or younger, please provide a parent or guardian name and email address<br />
										Parent or guardian Name<br />
										<input type='text' name='inp_student_parent' id='chk_student_parent' size='30' class='formInputText' maxlength='30' value='$student_student_parent' ><br />
										Parent or Guardian email<br />
										<input type='text' name='inp_student_parent_email' id='chk_student_parent_email' size='40' class='formInputText' maxlength='40' value='$student_student_parent_email' ></td></tr>
								<tr><td colspan='3'><hr></td></tr>
								<tr><td colspan='3'>Please click <span style='color:red;'><b>Submit</b></span> to continue with the sign-up process.</td></tr>	
								<tr><td colspan='3'><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Submit' /></td></tr>
								</table>
								</form></p>
								<p><b>CW Academy Personal Identification Information Privacy Policy:</b></p>
								<p>The personal information collected above is used only by CW Academy in the management of 
								students and advisors. The data is stored in a protected database. The information is not 
								shared with any outside party, that is, the information is never shared with anyone who is 
								not part of CW Academy and then only as needed. The data is accessible by CW Academy System 
								Administrators and if you are assigned to a class, some information is shared with the assigned 
								advisor.</p>";
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
				  inp_callsign = $inp_callsign<br />
				  inp_semester = $inp_semester<br /><br />";
		}

		$userName			= $inp_callsign;
		$doProceed			= TRUE;
		$getOut				= FALSE;
		
		if ($inp_doAgain == 'Y') {					/// coming back from fundamental test
			if ($doDebug) {
				echo "Coming back from fundmental test. inp_doAgain is Y<br />";
			}

			/*	read the student data
			if submit is Switch to Intermediate
				update student record for intermediate level
			*/
			$result				= $wpdb->get_results("select * from $studentTableName 
														where call_sign='$inp_callsign' 
														and semester='$inp_semester'");
			if ($result === FALSE) {
				handleWPDBError("$jobname Pass 3",$doDebug);
			} else {
				$lastError			= $wpdb->last_error;
				if ($lastError != '') {
					handleWPDBError("$jobname Pass 3",$doDebug);
					$content		.= "Fatal program error. System Admin has been notified";
					if (!$doDebug) {
						return $content;
					}
				}

				$numRows		= $wpdb->num_rows;
				if ($doDebug) {
					$myStr		= $wpdb->last_query;
					echo "read $studentTableName for $inp_callsign returned $numRows. Query: $myStr<br />";				
				}
				if ($numRows > 0) {				// got a record
					foreach($result as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_first_name						= $studentRow->first_name;
						$student_last_name						= stripslashes($studentRow->last_name);
						$student_email  						= strtolower(strtolower($studentRow->email));
						$student_ph_code						= $studentRow->ph_code;
						$student_phone  						= $studentRow->phone;
						$student_city  							= $studentRow->city;
						$student_state  						= $studentRow->state;
						$student_zip_code  						= $studentRow->zip_code;
						$student_country_code					= $studentRow->country_code;
						$student_country  						= $studentRow->country;
						$student_time_zone  					= $studentRow->time_zone;
						$student_timezone_id					= $studentRow->timezone_id;
						$student_timezone_offset				= $studentRow->timezone_offset;
						$student_whatsapp						= $studentRow->whatsapp_app;
						$student_signal							= $studentRow->signal_app;
						$student_telegram						= $studentRow->telegram_app;
						$student_messenger						= $studentRow->messenger_app;					
						$student_wpm 	 						= $studentRow->wpm;
						$student_youth  						= $studentRow->youth;
						$student_age  							= $studentRow->age;
						$student_student_parent 				= $studentRow->student_parent;
						$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
						$student_level  						= $studentRow->level;
						$student_waiting_list 					= $studentRow->waiting_list;
						$student_request_date  					= $studentRow->request_date;
						$student_semester						= $studentRow->semester;
						$student_notes  						= $studentRow->notes;
						$student_welcome_date  					= $studentRow->welcome_date;
						$student_email_sent_date  				= $studentRow->email_sent_date;
						$student_email_number  					= $studentRow->email_number;
						$student_response  						= strtoupper($studentRow->response);
						$student_response_date  				= $studentRow->response_date;
						$student_abandoned  					= $studentRow->abandoned;
						$student_student_status  				= strtoupper($studentRow->student_status);
						$student_action_log  					= $studentRow->action_log;
						$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
						$student_selected_date  				= $studentRow->selected_date;
						$student_no_catalog  					= $studentRow->no_catalog;
						$student_hold_override  				= $studentRow->hold_override;
						$student_messaging  					= $studentRow->messaging;
						$student_assigned_advisor  				= $studentRow->assigned_advisor;
						$student_advisor_select_date  			= $studentRow->advisor_select_date;
						$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
						$student_hold_reason_code  				= $studentRow->hold_reason_code;
						$student_class_priority  				= $studentRow->class_priority;
						$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
						$student_promotable  					= $studentRow->promotable;
						$student_excluded_advisor  				= $studentRow->excluded_advisor;
						$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
						$student_available_class_days  			= $studentRow->available_class_days;
						$student_intervention_required  		= $studentRow->intervention_required;
						$student_copy_control  					= $studentRow->copy_control;
						$student_first_class_choice  			= $studentRow->first_class_choice;
						$student_second_class_choice  			= $studentRow->second_class_choice;
						$student_third_class_choice  			= $studentRow->third_class_choice;
						$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
						$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
						$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
						$student_catalog_options				= $studentRow->catalog_options;
						$student_flexible						= $studentRow->flexible;
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;

						$student_last_name 						= no_magic_quotes($student_last_name);
			
						if ($doDebug) {
							echo "got a record for $inp_callsign<br />";
						}
			
						if ($submit == 'Switch to Intermediate') {
							$inp_level							= 'Intermediate';
							$student_level						= 'Intermediate';
							$inp_action_log						= "$student_action_log / REGISTRATION $currentDateTime $inp_callsign student decided to switch to Intermediate ";
							$updateParams						= array('level'=>'Intermediate',
																		'action_log'=>$inp_action_log);
							$updateFormat						= array('%s','%s');
							$updateData	= array('tableName'=>$studentTableName,
												'inp_data'=>$updateParams,
												'inp_format'=>$updateFormat,
												'inp_method'=>'update',
												'jobname'=>'Registration 3a',
												'inp_id'=>$student_ID,
												'inp_callsign'=>$student_call_sign,
												'inp_semester'=>$student_semester,
												'inp_who'=>$student_call_sign,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
							$updateResult	= updateStudent($updateData);
							if ($updateResult[0] === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$lastError			= $wpdb->last_error;
								if ($lastError != '') {
									handleWPDBError($jobname,$doDebug);
									$content		.= "Fatal program error. System Admin has been notified";
									if (!$doDebug) {
										return $content;
									}
								}

								$inp_level 			= 'Intermediate';
								if ($doDebug) {
									echo "updating $student_call_sign switch to Intermedate succeed<br />";
								}
							}
						} else {
							$inp_level				= $student_level;
						}
						$inp_semester				= $student_semester;
						$inp_country				= $student_country;
						$inp_country_code			= $student_country_code;
						$inp_zip					= $student_zip_code;
						$inp_timezone_id			= $student_timezone_id;
						$inp_timezone_offset		= $student_timezone_offset;
						$inp_firstname				= $student_first_name;
						$inp_lastname				= $student_last_name;
						$inp_waiting_list			= $student_waiting_list;
					}
				} else {
					if ($doDebug) {
						echo "Reading $studentTableName at for $inp_callsign. 
							  Database says there was no record to update<br >";
					}
					$errorMsg			= "Student Registration pass 3 switching to Intermediate. Attempting to read 
$studentTableName for $inp_callsign. Database says there was no record to update";
					sendErrorEmail($errorMsg);
					$doProceed			= FALSE;
				}
			}
		} else {
			if ($doDebug) {
				echo "inp_doAgain was not Y<br />";
			}	
			$doProceed			= TRUE;
			/* if the student refreshes the page a duplicate record would be written.
				Checking to see if there is already a record, if so bail out
			*/
			$recordCount		= $wpdb->get_var("select count(call_sign)
													from $studentTableName 
													where call_sign = '$inp_callsign' 
													and (semester = '$nextSemester' or 
														 semester = '$semesterTwo' or 
														 semester = '$semesterThree' or 
														 semester = '$semesterFour')");
			if ($recordCount > 0) {
				$content		.= "There is a signup record in the database for $inp_callsign. If you 
									need to modify this record or make or change class preferences, 
									please restart this program and select the option to modify an 
									existing signup record.<br />";
				$doProceed		= FALSE;
			} else {



		
				$myArray			= explode("|",$inp_countrya);
				$inp_country_code	= $myArray[0];
				if ($inp_country_code == 'XX') {
					$myArray		= explode("|",$inp_countryb);
					$inp_country_code	= $myArray[0];
				}
				if ($doDebug) {
					echo "inp_countrya: $inp_countrya<br />
						  inp_countryb: $inp_countryb<br />
						  using $inp_country_code<br />";
				}
				if ($inp_country_code == '' || $inp_country_code == 'XX') {
					sendErrorEmail("$jobname Pass3 inp_country_code is blank; inp_callsign: $inp_callsign; inp_country: $inp_country; inp_countrya: $inp_countrya; inp_countrb: $inp_countryb");
					$doProceed		= FALSE;
				}

				if ($doProceed) {
					$inp_country		= $myArray[1];
					// if the country code is US verify the zip code
					if ($inp_country_code == 'US') {
						if ($doDebug) {
							echo "have a country code of US, verifying the zip code<br />";
						}
						$zipResult		= getOffsetFromZipCode($inp_zip,$inp_semester,TRUE,$doDebug);
						if ($zipResult[0] == 'NOK') {
							$content	.= "The supplied zipcode of $inp_zip is not a valid zipcode in the $inp_country. 
											Please push the 'Back' button and enter a valid zipcode.";
							return $content;
						}
					}
		
		
					if ($waitingList) {
						$inp_waiting_list 	= 'Y';
					} else {
						$inp_waiting_list	= 'N';
					}
					// get the phone code from wpw1_cwa_country_codes table
					if ($doDebug) {
						echo "getting the phone code for $inp_country_code<br />";
					}
					$inp_ph_code			= '';
					$resultPhCode			= $wpdb->get_results("select ph_code 
																from wpw1_cwa_country_codes 
																where country_code='$inp_country_code'");
					if ($resultPhCode === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$lastError			= $wpdb->last_error;
						if ($lastError != '') {
							handleWPDBError($jobname,$doDebug);
							$content		.= "Fatal program error. System Admin has been notified";
							if (!$doDebug) {
								return $content;
							}
						}

						$numPhRows		 	= $wpdb->num_rows;
						if ($numPhRows > 0) {
							foreach($resultPhCode as $phRows) {
								$inp_ph_code		= $phRows->ph_code;
							}
						}
					}


					$inp_action_log		= "REGISTRATION $currentDateTime $inp_callsign sign-up record stored ";
					if ($inp_semester == '') {
						sendErrorEmail("$jobname pass 3 inp_semeter is empty. Assuming nextSemster, inp_bypass: $inp_bypass; inp_doAgain: $inp_doAgain");
						$inp_semester	= $nextSemester;
						$inp_action_log = "$inp_action_log / MISSING SEMESTER. Arbitrarily assigned $nextSemester, inp_bypass: $inp_bypass; inp_doAgain: $inp_doAgain ";
					}
					/// insert the registration data into the database
					if ($doDebug) {
						echo "writing the registration data to the database<br />";
					}
					if ($inp_semester == '') {
						sendErrorEmail("$jobname pass 3 inp_semeter is empty. Assuming nextSemster");
						if ($doDebug) {
							echo "$jobname pass 3 inp_semeter is empty. Assuming nextSeemster<br />";
						}
						$inp_semester	= $nextSemester;
					}
					$inp_lastname		= addslashes($inp_lastname);
					$nowDate			= date('Y-m-d H:i:s');
					$insertParams		= array('call_sign'=>$inp_callsign,
												'first_name'=>$inp_firstname,
												 'last_name'=>$inp_lastname,
												 'ph_code'=>$inp_ph_code,
												 'phone'=>$inp_phone,
												 'email'=>$inp_email,
												 'city'=>$inp_city,
												 'state'=>$inp_state,
												 'zip_code'=>$inp_zip,
												 'country_code'=>$inp_country_code,
												 'country'=>$inp_country,
												 'time_zone'=>$browser_timezone_id,
												 'whatsapp_app'=>$inp_whatsapp,
												 'telegram_app'=>$inp_telegram,
												 'signal_app'=>$inp_signal,
												 'messenger_app'=>$inp_messenger,
												 'youth'=>$inp_youth,
												 'age'=>$inp_age,
												 'student_parent'=>$inp_student_parent,
												 'student_parent_email'=>$inp_student_parent_email,
												 'waiting_list'=>$inp_waiting_list,
												 'level'=>$inp_level,
												 'semester'=>$inp_semester,
												 'request_date'=>$nowDate,
												 'messaging'=>$inp_messaging,
												 'abandoned'=>'Y',
												 'action_log'=>$inp_action_log,
												 'request_date'=>$currentDateTime,
												 'first_class_choice'=>'None',
												 'second_class_choice'=>'None',
												 'third_class_choice'=>'None',
												 'first_class_choice_utc'=>'None',
												 'second_class_choice_utc'=>'None',
												 'third_class_choice_utc'=>'None');
					$insertFormat		= array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
												  '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
												  '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
												  '%s','%s','%s');
					$studentUpdateData		= array('tableName'=>$studentTableName,
													'inp_method'=>'add',
													'inp_data'=>$insertParams,
													'inp_format'=>$insertFormat,
													'jobname'=>$jobname,
													'inp_id'=>'',
													'inp_callsign'=>$inp_callsign,
													'inp_semester'=>$inp_semester,
													'inp_who'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
					$updateResult	= updateStudent($studentUpdateData);
					if ($updateResult[0] === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$lastError			= $wpdb->last_error;
						if ($lastError != '') {
							handleWPDBError($jobname,$doDebug);
							$content		.= "Fatal program error. System Admin has been notified";
							if (!$doDebug) {
								return $content;
							}
						}

						$student_ID			= $updateResult[1];
						if ($doDebug) {
							echo "student $inp_callsign record inserted at id $student_ID<br />";
						}
						$student_action_log = $inp_action_log;
						// write to the audit log
						if ($testMode) {
							$log_mode		= 'testMode';
							$log_file		= 'TestStudent';
						} else {
							$log_mode		= 'Production';
							$log_file		= 'Student';
						}
						$submitArray		= array('logtype'=>$log_file,
													'logmode'=>$log_mode,
													'logaction'=>'ADD',
													'logsubtype'=>'Student',
													'logdate'=>$logDate,
													'logprogram'=>'Registration 3b',
													'logwho'=>$userName,
													'student_ID'=>$student_ID,
													'call_sign'=>$inp_call_sign,
													'logsemester'=>$inp_semester,
													'first_name'=>$inp_firstname,
													'last_name'=>$inp_lastname);
						foreach($insertParams as $myKey=>$myValue) {
							if (!in_array($myKey,$fieldTest)) {
								$submitArray[$myKey]	= $myValue;
							}
						}
						$result		= storeAuditLogData_v3($submitArray);
						if ($doDebug) {
							echo "storeAuditLogData_v3 result: " . $result[1] . "<br />";
						}

						$student_level			= $inp_level;
						$student_semester		= $inp_semester;
	
						////// if signing up for a Fundamental class, check to  see if they've taken the class before
						if ($inp_level == 'Fundamental') {
							$result				= check_class($inp_callsign,$inp_level,$testMode,$doDebug);
							$theStatus			= $result[0];
							$theSemester		= $result[1];
							$thePromotable		= $result[2];


							if ($thePromotable == 'P') {
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
													<input type='hidden' name='token' value='$token'>
													<input type='hidden' id='browser_timezone_id' name='browser_timezone_id' value='$browser_timezone_id' />
													<h4>Fundamental Signup</h4>
													<p>According to our records, you have already successfully taken the CW Academy 
													Fundamental Level Class in the $theSemester semester.</p>
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
								$doProceed		= FALSE;
							}
						}
					}
					if ($doDebug) {
						echo "fundamental test complete. Proceeding to do the class selection<br />";
					}
				}
			}
		}
		if ($doProceed) {
			if (!isset($inp_country_code)) {
				if ($inp_countrya != '') {
					$myArray 			= explode("|",$inp_countrya);
					$inp_country_code 	= $myArray[1];
					$inp_country		= $myArray[1];
				} elseif ($inp_countryb != '') {
					$myArray 			= explode("|",$inp_countryg);
					$inp_country_code 	= $myArray[1];
					$inp_country		= $myArray[1];
				} else {
					if ($doDebug) {
						echo "inp_country_code is not set<br />
								inp_bypass: $inp_bypass<br />
								inp_doAgain: $inp_doAgain<br />";
					}
					sendErrorEmail("$jobname pass 3 inp_country_code is missing. inp_bypass: $inp_bypass; inp_doAgain: $inp_doAgain; inp_callsign: $inp_callsign; student_call_sign: $student_call_sign; inp_country: $inp_country; inp_countrya: $inp_countrya; inp_countryb: $inp_countryb");
					$content	.= "A fatal error has occurred. The country information is missing. It has been referred to a systems administrator.";
					return $content;
				}
			}
	
			if ($inp_country_code == 'US') {
			
				$matchMsg			= "";
	
				if ($doDebug) {
					echo "Working with a US timezone. Current information:<br />
							browser_timezone_id: $browser_timezone_id<br />
							inp_doAgain: $inp_doAgain<br />
							inp_country: $inp_country<br />
							inp_country_code: $inp_country_code<br />
							inp_zip: $inp_zip<br />
							inp_timezone_id: $inp_timezone_id<br />
							inp_timezone_offset: $inp_timezone_offset<br >
							inp_semester: $inp_semester<br />";
				}

				///// call get the timezone info here
				$myResult			= getOffsetFromZipcode($inp_zip,$inp_semester,TRUE,$testMode,$doDebug);
				$myStatus			= $myResult[0];
				$zipTimeZone		= $myResult[1];
				$offset				= $myResult[2];
				$matchMsg			= $myResult[3];
			
				if ($myStatus == 'NOK') {
					if ($doDebug) {
						echo "calling getOffsetFromZipcode using $inp_zip and $inp_semester failed<br />
							  Error: $matchMsg<br />";
					}
					$content		.= "The zipcode is not a valid zipcode. To correct the zipcode and 
										continue the sign up process, you will need to  
										click on <a href='$theURL'>Student Registration</a> and select 
										option 3 to modify your registration record.";
					return $content;
				}

				$student_action_log		.= " / $student_action_log calculated UTC offset to be $offset hours ";
				if ($doDebug) {
					echo "preparing to update for timezone info<br />";
				}
				
				$updateParams		= array("timezone_id|$zipTimeZone|s",
											"timezone_offset|$offset|f",
											"notes|$matchMsg|s",
											"action_log|$inp_action_log|s");

				if ($inp_callsign == '') {
					$thisCallSign	= $student_call_sign;
				} else {
					$thisCallSign	= $inp_callsign;
				}
/*
				$errorMsg			= "Student Registration Register 3c. Preparing to update the database for 
timezone_id: $zipTimeZone, offse: $offset.<br />
inp_callsign: $inp_callsign<br />
student_call_sign: $student_call_sign<br />
semester: $inp_semester<br />
student_semester: $student_semester<br />
thisCallSign: $thisCallSign";
				sendErrorEmail($errorMsg);
*/												
				$updateData	= array('tableName'=>$studentTableName,
									'inp_data'=>$updateParams,
									'jobname'=>'Registration 3c',
									'inp_id'=>$student_ID,
									'inp_method'=>'update',
									'inp_callsign'=>$inp_callsign,
									'inp_semester'=>$inp_semester,
									'inp_who'=>$thisCallSign,
									'testMode'=>$testMode,
									'doDebug'=>$doDebug);
				$updateResult	= updateStudent($updateData);
				if ($updateResult[0] === FALSE) {
					handlelWPDBError($jobname,$doDebug);
					$content		.= "Unable to update content in $studentTableName<br />";
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						if (!$doDebug) {
							return $content;
						}
					}

					$inp_timezone_id		= $zipTimeZone;
					$inp_timezone_offset	= $offset;
				}
			
			} else {
				if ($doDebug) {
					echo "dealing with a non-us country of $inp_country_code<br />";
				}
				$timezone_identifiers 		= DateTimeZone::listIdentifiers( DateTimeZone::PER_COUNTRY, $inp_country_code );
				$myInt						= count($timezone_identifiers);
				if ($doDebug) {
					echo "found $myInt identifiers for country code $inp_country_code";
				}
				
				if ($myInt == 1) {									//  only 1 found. Use that and continue
					$inp_timezone_id		= $timezone_identifiers[0];
					$myArray				= explode(" ",$inp_semester);
					$thisYear				= $myArray[0];
					$thisMonDay				= $myArray[1];
					$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
					$myMonDay				= $myConvertArray[$thisMonDay];
					$thisNewDate			= "$thisYear$myMonDay 00:00:00";
					if ($doDebug) {
						echo "converted $inp_semester to $thisNewDate<br />";
					}
					$dateTimeZoneLocal 		= new DateTimeZone($inp_timezone_id);
					$dateTimeZoneUTC 		= new DateTimeZone("UTC");
					$dateTimeLocal 			= new DateTime($thisNewDate,$dateTimeZoneLocal);
					$dateTimeUTC			= new DateTime($thisNewDate,$dateTimeZoneUTC);
					$php2 					= $dateTimeZoneLocal->getOffset($dateTimeUTC);
					$inp_timezone_offset 	= $php2/3600;
					if ($doDebug) {
						echo "have set inp_timezone_id to $inp_timezone_id with offset of $inp_timezone_offset<br />";
					}
					$student_action_log		= "$student_action_log / calculated UTC offset to be $inp_timezone_offset hours ";
					$updateParams			= array("timezone_id|$inp_timezone_id|s",
													"timezone_offset|$inp_timezone_offset|f",
													"action_log|$student_action_log|s");
					$theCallsign			= $inp_callsign;
					if ($inp_callsign == '') {
						$theCallsign		= $student_call_sign;
					}
					if ($doDebug) {
						echo "Preparing to update $theCallsign for foreign timezone info<br />";
					}
					$theSemester			= $inp_semester;
					if ($theSemester == '') {
						$theSemester		= $student_semester;
					}
					$updateData	= array('tableName'=>$studentTableName,
										'inp_data'=>$updateParams,
										'jobname'=>'Registration 3d',
										'inp_method'=>'update',
										'inp_id'=>$student_ID,
										'inp_callsign'=>$theCallsign,
										'inp_semester'=>$theSemester,
										'inp_who'=>$theCallsign,
										'testMode'=>$testMode,
										'doDebug'=>$doDebug);
					$updateResult	= updateStudent($updateData);
					if ($updateResult[0] === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$inp_timezone_id		= 'Unknown';
						$inp_timezone_offset	= -99.0;
					} else {
						$lastError			= $wpdb->last_error;
						if ($lastError != '') {
							handleWPDBError("$jobname Pass 2",$doDebug);
							$inp_timezone_id		= 'Unknown';
							$inp_timezone_offset	= -99.0;
						} else {

							$inp_timezone_id		= $inp_timezone_id;
							$inp_timezone_offset	= $inp_timezone_offset;
						}
					}
				} else {
				
					$timezoneSelector			= "<table>";
					$ii							= 1;
					if ($doDebug) {
						echo "have the list of identifiers for $inp_country_code<br />";
					}
					$oneChecked				= FALSE;
					foreach ($timezone_identifiers as $thisID) {
						if ($doDebug) {
							echo "Processing $thisID<br />";
						}
						$selector			= "";
						if ($browser_timezone_id == $thisID) {
							$selector		= "checked";
							$oneChecked		= TRUE;
						}
						$dateTimeZoneLocal 	= new DateTimeZone($thisID);
						$dateTimeLocal 		= new DateTime("now",$dateTimeZoneLocal);
						$localDateTime 		= $dateTimeLocal->format('h:i A');
						$myInt				= strpos($thisID,"/");
						$myCity				= substr($thisID,$myInt+1);
						switch($ii) {
							case 1:
								$timezoneSelector	.= "<tr><td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td>";
								$ii++;
								break;
							case 2:
								$timezoneSelector	.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td>";
								$ii++;
								break;
							case 3:
								$timezoneSelector	.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td></tr>";
								$ii					= 1;
								break;
						}
					}
					if (!$oneChecked) {					
						$selector					= 'checked';
					}
					if ($ii == 2) {			// need two blank cells
						$timezoneSelector			.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='None' $selector>None</td><td>&nbsp;</td></tr>";
					} elseif ($ii == 3) {	// need one blank cell
						$timezoneSelector			.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='None' $selector>None</td></tr>";
					} else {				// need new row
						$timezoneSelector			.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='None' $selector>None</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
					}
					if ($doDebug) {
						echo "Putting the form together<br />";
					}
					$content		.= "<h3>Select Time Zone City</h3>
										<p>Please select the city that best represents the timezone you will be in during the $inp_semester semester. 
										The current local time is displayed underneath the city.</p>
										<form method='post' action='$theURL' 
										name='tzselection' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='3A'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<input type='hidden' name='student_ID' value='$student_ID'>
											<input type='hidden' name='inp_semester' value='$inp_semester'>
											<input type='hidden' name='token' value='$token'>
											<input type='hidden' name='inp_callsign' value='$inp_callsign'>
										$timezoneSelector
										<tr><td colspan='3'><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Submit' /></td></tr>
										</table>
										<p>NOTE: If the program is asking you this question and you live in the United States, then you selected 
										the wrong country on the previous page. Start over with the sign up program from the 
										beginning.</p>
										</form>";

					$doProceed 		= FALSE;
				}
			}
			if ($doProceed) {
				$strPass			= "3B";		/// bypass 3A
			}
		}
	}
	if ("3A" == $strPass) {				/// only come here if timezone had to be selected
		if ($doDebug) {
			echo "at pass 3A<br />
				  inp_timezone_id: $inp_timezone_id<br />";
		}
		
		// figure out the offset and update the database
		$myArray				= explode(" ",$inp_semester);
		$thisYear				= $myArray[0];
		$thisMonDay				= $myArray[1];
		$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
		$myMonDay				= $myConvertArray[$thisMonDay];
		$thisNewDate			= "$thisYear$myMonDay 00:00:00";
		if ($doDebug) {
			echo "converted $inp_semester to $thisNewDate<br />";
		}
		$dateTimeZoneLocal 		= new DateTimeZone($inp_timezone_id);
		$dateTimeZoneUTC 		= new DateTimeZone("UTC");
		$dateTimeLocal 			= new DateTime($thisNewDate,$dateTimeZoneLocal);
		$dateTimeUTC			= new DateTime($thisNewDate,$dateTimeZoneUTC);
		$php2 					= $dateTimeZoneLocal->getOffset($dateTimeUTC);
		$inp_timezone_offset 	= $php2/3600;
		if ($doDebug) {
			echo "have set inp_timezone_id to $inp_timezone_id with offset of $inp_timezone_offset<br />";
		}
		$updateParams			= array("timezone_id|$inp_timezone_id|s",
										"timezone_offset|$inp_timezone_offset|f");
		$theCallsign			= $inp_callsign;
		if ($doDebug) {
			echo "Preparing to update $theCallsign for foreign timezone info<br />";
		}
		$theSemester			= $inp_semester;
		$updateData	= array('tableName'=>$studentTableName,
							'inp_data'=>$updateParams,
							'jobname'=>'Registration 3A',
							'inp_method'=>'update',
							'inp_id'=>$student_ID,
							'inp_callsign'=>$theCallsign,
							'inp_who'=>$theCallsign,
							'testMode'=>$testMode,
							'doDebug'=>$doDebug);
		$updateResult	= updateStudent($updateData);
		if ($updateResult[0] === FALSE) {
			handleWPDBError("$jobname 3A",$doDebug);
			$inp_timezone_id		= 'Unknown';
			$inp_timezone_offset	= -99.0;
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError("$jobname 3A",$doDebug);
				$inp_timezone_id		= 'Unknown';
				$inp_timezone_offset	= -99.0;

			} else {

				$inp_timezone_id		= $inp_timezone_id;
				$inp_timezone_offset	= $inp_timezone_offset;
			}
		}
		$strPass					= "3B";
		$doProceed					= TRUE;
	}
	
	if ("3B" == $strPass) {
		
		if ($doProceed) {
			// get the student record
			$sql				= "select * from $studentTableName 
									where call_sign='$inp_callsign' 
									and semester='$inp_semester'";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Unable to obtain content from $studentTableName<br />";
			} else {
				$lastError			= $wpdb->last_error;
				if ($lastError != '') {
					handleWPDBError($jobname,$doDebug);
					$content		.= "Fatal program error. System Admin has been notified";
					if (!$doDebug) {
						return $content;
					}
				}

				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_first_name						= $studentRow->first_name;
						$student_last_name						= stripslashes($studentRow->last_name);
						$student_email  						= strtolower(strtolower($studentRow->email));
						$student_phone  						= $studentRow->phone;
						$student_ph_code						= $studentRow->ph_code;
						$student_city  							= $studentRow->city;
						$student_state  						= $studentRow->state;
						$student_zip_code  						= $studentRow->zip_code;
						$student_country  						= $studentRow->country;
						$student_country_code					= $studentRow->country_code;
						$student_time_zone  					= $studentRow->time_zone;
						$student_timezone_id					= $studentRow->timezone_id;
						$student_timezone_offset				= $studentRow->timezone_offset;
						$student_whatsapp						= $studentRow->whatsapp_app;
						$student_signal							= $studentRow->signal_app;
						$student_telegram						= $studentRow->telegram_app;
						$student_messenger						= $studentRow->messenger_app;					
						$student_wpm 	 						= $studentRow->wpm;
						$student_youth  						= $studentRow->youth;
						$student_age  							= $studentRow->age;
						$student_student_parent 				= $studentRow->student_parent;
						$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
						$student_level  						= $studentRow->level;
						$student_waiting_list 					= $studentRow->waiting_list;
						$student_request_date  					= $studentRow->request_date;
						$student_semester						= $studentRow->semester;
						$student_notes  						= $studentRow->notes;
						$student_welcome_date  					= $studentRow->welcome_date;
						$student_email_sent_date  				= $studentRow->email_sent_date;
						$student_email_number  					= $studentRow->email_number;
						$student_response  						= strtoupper($studentRow->response);
						$student_response_date  				= $studentRow->response_date;
						$student_abandoned  					= $studentRow->abandoned;
						$student_student_status  				= strtoupper($studentRow->student_status);
						$student_action_log  					= $studentRow->action_log;
						$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
						$student_selected_date  				= $studentRow->selected_date;
						$student_no_catalog			 			= $studentRow->no_catalog;
						$student_hold_override  				= $studentRow->hold_override;
						$student_messaging  					= $studentRow->messaging;
						$student_assigned_advisor  				= $studentRow->assigned_advisor;
						$student_advisor_select_date  			= $studentRow->advisor_select_date;
						$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
						$student_hold_reason_code  				= $studentRow->hold_reason_code;
						$student_class_priority  				= $studentRow->class_priority;
						$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
						$student_promotable  					= $studentRow->promotable;
						$student_excluded_advisor  				= $studentRow->excluded_advisor;
						$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
						$student_available_class_days  			= $studentRow->available_class_days;
						$student_intervention_required  		= $studentRow->intervention_required;
						$student_copy_control  					= $studentRow->copy_control;
						$student_first_class_choice  			= $studentRow->first_class_choice;
						$student_second_class_choice  			= $studentRow->second_class_choice;
						$student_third_class_choice  			= $studentRow->third_class_choice;
						$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
						$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
						$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
						$student_catalog_options				= $studentRow->catalog_options;
						$student_flexible						= $studentRow->flexible;
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;
/*
if ($testMode) {
	echo "calling generate class info and testMode is TRUE<br />";
} else {
	echo "calling generate class info and testMode is FALSE<br />";
}
*/
		
						/// get the catalog information and display it
						$inp_data			= array('student_semester'=>$student_semester, 
													'student_level'=>$student_level, 
													'student_no_catalog'=>$student_no_catalog, 
													'student_catalog_options'=>$student_catalog_options,
													'student_flexible'=>$student_flexible,  
													'student_first_class_choice_utc'=>$student_first_class_choice_utc, 
													'student_second_class_choice_utc'=>$student_second_class_choice_utc, 
													'student_third_class_choice_utc'=>$student_third_class_choice_utc, 
													'student_timezone_offset'=>$student_timezone_offset,
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
							$date1			= $result[2];
							$date2			= $result[3];
							$date3			= $result[4];
							$schedAvail		= $result[5];
							$option_message	= "";
							if ($result_option == 'option') {
								$option_message	= "<p>You are signing up for a $student_level Level class in 
													the $student_semester semester. The catalog of available 
													classes will not be available until about 45 days before 
													the start of the semester. Please indicate when you will be 
													available to take a $student_level Level class by 
													selecting from the list below.</p>
													<p>Classes are an hour in length. The advisor decides when 
													the class will actually be held. Your indication of which part 
													of the day and which days of the week would work best for you 
													will help guide the advisor's schedule decision.<p>";
							} elseif ($result_option == 'catalog') {
								$result_option		= $result[0];
								$option_message		= "<p>The Class Catalog for $student_level Level classes is now available. 
														Select up to three class schedule options from the table below and 
														number them 1, 2, and 3.
														CW Acadamy will try to assign you to one of the class options in the order you 
														specify. Whether or not you are assigned to a class will depend on the 
														number of students selecting that class schedule and the number of 
														available seats in the classes held at that time.</p>";
							} elseif ($result_option == 'avail') {
								$result_option		= $result[0];
								if ($doDebug) {
									echo "result_option of $result_option<br />";
								}
								$option_message 	= "<p>Students have already been assigned to advisor classes. There may possibly 
														be classes with available seats. If so, they are listed below. If a class 
														schedule listed below will work for you, select the class and submit the 
														selection</p>";
							}
							$content		.= "<h3>$jobname</h3>
												$option_message
												<form method='post' action='$theURL' 
												name='classselection' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='4'>
												<input type='hidden' name='result_option' value='$result_option'>
												<input type='hidden' name='student_ID' value='$student_ID'>
												<input type='hidden' name='inp_callsign' value='$student_call_sign'>
												<input type='hidden' name='token' value='$token'>
												<input type='hidden' name='inp_semester' value='$student_semester'>
												<input type='hidden' name='schedAvail' value='$schedAvail'>
												$result[1]<br clear='all' />";
							if ($result_option == 'option') {
								$content	.= "<input class='formInputButton' type='submit' onclick=\"return validate_checkboxes(this.form);\" value='Submit' />";
//								$content	.= "<input class='formInputButton' type='submit' value='Submit' />";
							} else {			
								$content	.= "<input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Submit' />";
//								$content	.= "<input class='formInputButton' type='submit' value='Submit' />";
							}
								$content	.= "</form></p>";
						}
					}
				}
			}
		}


/////////////////// Pass 4		Write first, second, third choices to database and show student the registration info	
		
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 4<br />";
		}
		$userName				= $inp_callsign;


		if ($doDebug) {
			echo "Have the following information:
					inp_callsign: $inp_callsign<br />
					inp_semester: $inp_semester<br />
					student_ID: $student_ID<br />
					result_option: $result_option<br />";
		}
		
		$updateParams		= array();
		$doUpdateStudent		= FALSE;
		$actionLogUpdates	= "";
		$badActorResult		= FALSE;
		
		//// get the student record for update and display
		$sql				= "select * from $studentTableName 
							   where student_id=$student_ID";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError("$jobname Pass 4",$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError("$jobname Pass 4",$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}

			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				$myStr		= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numSRows rows from $studentTableName table<br >";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_ph_code						= $studentRow->ph_code;
					$student_phone  						= $studentRow->phone;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country_code					= $studentRow->country_code;
					$student_country  						= $studentRow->country;
					$student_time_zone  					= $studentRow->time_zone;
					$student_timezone_id					= $studentRow->timezone_id;
					$student_timezone_offset				= $studentRow->timezone_offset;
					$student_whatsapp						= $studentRow->whatsapp_app;
					$student_signal							= $studentRow->signal_app;
					$student_telegram						= $studentRow->telegram_app;
					$student_messenger						= $studentRow->messenger_app;					
					$student_wpm 	 						= $studentRow->wpm;
					$student_youth  						= $studentRow->youth;
					$student_age  							= $studentRow->age;
					$student_student_parent 				= $studentRow->student_parent;
					$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->level;
					$student_waiting_list 					= $studentRow->waiting_list;
					$student_request_date  					= $studentRow->request_date;
					$student_semester						= $studentRow->semester;
					$student_notes  						= $studentRow->notes;
					$student_welcome_date  					= $studentRow->welcome_date;
					$student_email_sent_date  				= $studentRow->email_sent_date;
					$student_email_number  					= $studentRow->email_number;
					$student_response  						= strtoupper($studentRow->response);
					$student_response_date  				= $studentRow->response_date;
					$student_abandoned  					= $studentRow->abandoned;
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog  					= $studentRow->no_catalog;
					$student_hold_override  				= $studentRow->hold_override;
					$student_messaging  					= $studentRow->messaging;
					$student_assigned_advisor  				= $studentRow->assigned_advisor;
					$student_advisor_select_date  			= $studentRow->advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
					$student_hold_reason_code  				= $studentRow->hold_reason_code;
					$student_class_priority  				= $studentRow->class_priority;
					$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
					$student_promotable  					= $studentRow->promotable;
					$student_excluded_advisor  				= $studentRow->excluded_advisor;
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->available_class_days;
					$student_intervention_required  		= $studentRow->intervention_required;
					$student_copy_control  					= $studentRow->copy_control;
					$student_first_class_choice  			= $studentRow->first_class_choice;
					$student_second_class_choice  			= $studentRow->second_class_choice;
					$student_third_class_choice  			= $studentRow->third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
					$student_catalog_options				= $studentRow->catalog_options;
					$student_flexible						= $studentRow->flexible;
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$student_last_name 						= no_magic_quotes($student_last_name);

					if ($result_option == 'option') {
						$student_no_catalog						= 'Y';
						$updateParams[]							= "no_catalog|Y|s";
						$student_abandoned						= 'N';
						$updateParams[]							= "abandoned|N|s";
						$actionLogUpdates						.= "Set no_catalog to Y, Set abandoned to N, ";
						$actionLogUpdates						.= "Set abandoned to N ";
						$doUpdateStudent						= TRUE;
						if (count($inp_sked_times) > 0) {
							$student_catalog_options 			= "";
							$haveAny							= FALSE;
							$firstTime							= TRUE;
							foreach($inp_sked_times as $thisValue) {
								if ($thisValue != 'ANY') {
									if ($firstTime) {
										$firstTime				= FALSE;
										$student_catalog_options	= $thisValue;
									} else {
										$student_catalog_options	= "$student_catalog_options,$thisValue";
									}
								} else {
									$haveAny					= TRUE;
								}
							}
							if ($haveAny) {
								$student_flexible				= 'Y';
								$updateParams[]					= 'flexible|Y|s';
								$actionLogUpdates				.= "Set flexible to Y, ";
								$doUpdateStudent					= TRUE;
								$student_catalog_options		= "";
							}
							$updateParams[]						= "catalog_options|$student_catalog_options|s";
							$actionLogUpdates					.= "set catalog_options to $student_catalog_options, ";
							$doUpdateStudent						= TRUE;
						}
					} elseif ($result_option == 'catalog') {
						if ($doDebug) {
							echo "handling the catalog option<br />
							inp_sked1: $inp_sked1<br />
							inp_sked2: $inp_sked2<br />
							inp_sked3: $inp_sked3<br />";
						}
						$student_no_catalog 				= 'N';
						$updateParams[]						= 'no_catalog|N|s';
						$student_abandoned					= 'N';
						$updateParams[]					 	= 'abandoned|N|s';
						$actionLogUpdates					.= "Set no_catalog to Y, ";
						$actionLogUpdates					.= "Set abandoned to N ";
						
						$myInt								= strpos($inp_sked1,"|");
						if ($myInt !== FALSE) {
							$myArray						= explode("|",$inp_sked1);						
							$student_first_class_choice		= $myArray[0];
							$student_first_class_choice_utc	= $myArray[1];
							$updateParams[]					= "first_class_choice|$student_first_class_choice|s";
							$updateParams[]					= "first_class_choice_utc|$student_first_class_choice_utc|s";
							$doUpdateStudent					= TRUE;
							$actionLogUpdates				.= "Set first_class_choices, ";
							$firstChoice					= $student_first_class_choice;
						} else {
							sendErrorEmail("$jobname pass $strPass inp_sked1 of $inp_sked1 is invalid for $student_call_sign");
							$student_first_class_choice		= "None";
							$student_first_class_choice_utc	= "None";
							$updateParams[]					= "first_class_choice|None|s";
							$updateParams[]					= "first_class_choice_utc|None|s";
							$doUpdateStudent					= TRUE;
							$actionLogUpdates				.= "Set first_class_choices to NONE, ";
							$firstChoice					= 'None';
						}

						if ($inp_sked2 == 'None') {
							$inp_sked2						= "None|None";
						}
						$myArray							= explode("|",$inp_sked2);						
						$student_second_class_choice		= $myArray[0];
						$student_second_class_choice_utc	= $myArray[1];
						$updateParams[]						= "second_class_choice|$student_second_class_choice|s";
						$updateParams[]						= "second_class_choice_utc|$student_second_class_choice_utc|s";
						$doUpdateStudent					= TRUE;
						$actionLogUpdates					.= "Set second_class_choices, ";
						if ($student_second_class_choice != 'None') {
							$secondChoice					= $student_second_class_choice;
						} else {
							$secondChoice					= '';
						}

						if ($inp_sked3 == 'None') {
							$inp_sked3						= "None|None";
						}
						$myArray							= explode("|",$inp_sked3);						
						$student_third_class_choice			= $myArray[0];
						$student_third_class_choice_utc		= $myArray[1];
						$updateParams[]						= "third_class_choice|$student_third_class_choice|s";
						$updateParams[]						= "third_class_choice_utc|$student_third_class_choice_utc|s";
						$doUpdateStudent					= TRUE;
						$actionLogUpdates					.= "Set third_class_choices, ";
						if ($student_third_class_choice != 'None') {
							$thirdChoice					= $student_third_class_choice;
						} else {
							$thirdChoice					= '';
						}
			
		
					} elseif ($result_option == 'avail') {
						if ($doDebug) {
							echo "<br />handling result_option avail<br />";
						}
						$updateParams[]							= 'abandoned|N|s';
						$actionLogUpdates						.= "Set abandoned to N ";
						$student_abandoned						= 'N';
						if ($inp_available == 'None') {
							$student_first_class_choice			= '';
							$student_first_class_choice_utc		= '';
							$updateParams[]						= "first_class_choice|$student_first_class_choice|s";
							$updateParams[]						= "first_class_choice_utc|$student_first_class_choice_utc|s";
							$doUpdateStudent					= TRUE;
							$firstChoice						= 'None';

							$student_second_class_choice		= '';
							$student_second_class_choice_utc	= '';
							$updateParams[]						= "second_class_choice|$student_second_class_choice|s";
							$updateParams[]						= "second_class_choice_utc|$student_second_class_choice_utc|s";
							$secondChoice						= 'None';

							$student_third_class_choice			= '';
							$student_third_class_choice_utc		= '';
							$updateParams[]						= "third_class_choice|$student_third_class_choice|s";
							$updateParams[]						= "third_class_choice_utc|$student_third_class_choice_utc|s";
							$thirdChoice						= 'None';

							$student_flexible					= "N";
							$updateParams[]						= 'flexible|N|s';

							$student_no_catalog					= 'N';
							$updateParams[]						= 'no_catalog|N|s';
							
							$student_waiting_list				= 'Y';
							$updateParams[]						= 'waiting_list|Y|s';

							$actionLogUpdates					.= "Removed class choices, set flexible to N, set no_catalog to N, set waiting_list to Y, ";
						} else {
							$myArray							= explode("|",$inp_available);
							$student_first_class_choice			= $myArray[0];
							$student_first_class_choice_utc		= $myArray[1];
							$updateParams[]						= "first_class_choice|$student_first_class_choice|s";
							$updateParams[]						= "first_class_choice_utc|$student_first_class_choice_utc|s";
							$firstChoice						= $student_first_class_choice;
							$doUpdateStudent					= TRUE;

							$student_second_class_choice		= 'None';
							$student_second_class_choice_utc	= 'None';
							$updateParams[]						= "second_class_choice|$student_second_class_choice|s";
							$updateParams[]						= "second_class_choice_utc|$student_second_class_choice_utc|s";
							$secondChoice						= 'None';

							$student_third_class_choice			= 'None';
							$student_third_class_choice_utc		= 'None';
							$updateParams[]						= "third_class_choice|$student_third_class_choice|s";
							$updateParams[]						= "third_class_choice_utc|$student_third_class_choice_utc|s";
							$thirdChoice						= 'None';

							$student_flexible					= "N";
							$updateParams[]						= 'flexible|N|s';

							$student_no_catalog					= 'N';
							$updateParams[]						= 'no_catalog|N|s';

							$actionLogUpdates					.= "Set first_class choices, removed second and third class choices, set flexible to N, set no_catalog to N, ";

						}
						$updateParams[]							= 'response|Y|s';
						$student_response						= 'Y';
						$myStr									= date('Y-m-d H:i:s');
						$updateParams[]							= "response_date|$myStr|s";
						$actionLogUpdates						.= "set response to Y and set response_date, ";
						$doUpdateStudent							= TRUE;
					}

					// check to see if the student is in the bad actors table
					$badActorResult			= checkForBadActor($student_call_sign,$doDebug);
					if ($badActorResult) {					/// is a bad actor
						$actionLogUpdates	.= "Student is in the bad actor table, ";
						$updateParams[]		= "intervention_required|H|s";
						$updateParams[]		= "hold_reason_code|B|s";
						$doUpdateStudent		= TRUE;
					}
					if ($doUpdateStudent) {
						$myInt					= strlen($actionLogUpdates) -2;
						$actionLogUpdates		= substr($actionLogUpdates,0,$myInt);
						$student_action_log		= "$student_action_log / $actionDate $userName STDREG $actionLogUpdates";
						$updateParams[]			= "action_log|$student_action_log|s";
						$studentUpdateData		= array('tableName'=>$studentTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateParams,
														'inp_format'=>array(''),
														'jobname'=>$jobname,
														'inp_id'=>$student_ID,
														'inp_callsign'=>$student_call_sign,
														'inp_semester'=>$student_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateStudent($studentUpdateData);
						if ($updateResult[0] === FALSE) {
							handleWPDBError($jobname,$doDebug);
							$content		.= "Unable to update content in $studentTableName<br />";
						} else {
							$lastError			= $wpdb->last_error;
							if ($lastError != '') {
								handleWPDBError($jobname,$doDebug);
								$content		.= "Fatal program error. System Admin has been notified";
								if (!$doDebug) {
									return $content;
								}
							}
					
							/// Now check to see if the student 
							/// is also signed up as an advisor. If so, set the advisor survey_score 
							/// to 13 and send an email about it to Roland and Bob
							if ($doDebug) {
								echo "checking to see if student is also signed up as an advisor<br />";
							}
							$sql				= "select advisor_id, 
														  call_sign, 
														  survey_score, 
														  semester, 
														  action_log, 
														  first_name, 
														  last_name, 
														  email, 
														  phone,
														  verify_response  
													from $advisorTableName 
													where call_sign='$inp_callsign' 
													and verify_response != 'R' 
													and semester='$inp_semester'";
							$wpw1_cwa_advisor		= $wpdb->get_results($sql);
							if ($wpw1_cwa_advisor === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$lastError			= $wpdb->last_error;
								if ($lastError != '') {
									handleWPDBError($jobname,$doDebug);
									$content		.= "Fatal program error. System Admin has been notified";
									if (!$doDebug) {
										return $content;
									}
								}

								$numARows									= $wpdb->num_rows;
								if ($doDebug) {
									$myStr			= $wpdb->last_query;
									echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
								}
								if ($numARows > 0) {
									foreach ($wpw1_cwa_advisor as $advisorRow) {
										$advisor_ID							= $advisorRow->advisor_id;
										$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
										$advisor_first_name 				= $advisorRow->first_name;
										$advisor_last_name 					= stripslashes($advisorRow->last_name);
										$advisor_email 						= strtolower($advisorRow->email);
										$advisor_phone						= $advisorRow->phone;
										$advisor_semester 				 	= $advisorRow->semester;
										$advisor_verify_response		 	= $advisorRow->verify_response;
										$advisor_survey_score 				= $advisorRow->survey_score;
										$advisor_action_log 				= $advisorRow->action_log;

										$advisor_action_log					= "$advisor_action_log / $actionDate $userName STDREG student signed up as advisor in the same semester. Set advisor survey_score to 13 ";
										$advisorUpdateData		= array('tableName'=>$advisorTableName,
																		'inp_method'=>'update',
																		'inp_data'=>array('survey_score'=>13,
																							 'action_log'=>$advisor_action_log),
																		'inp_format'=>array('%d','%s'),
																		'jobname'=>$jobname,
																		'inp_id'=>$advisor_ID,
																		'inp_callsign'=>$advisor_call_sign,
																		'inp_semester'=>$advisor_semester,
																		'inp_who'=>$userName,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
										$updateResult	= updateAdvisor($advisorUpdateData);
										if ($updateResult[0] === FALSE) {
											handleWPDBError($jobname,$doDebug);
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
												echo "Successfully updated $advisor_call_sign record at $advisor_ID<br />";
											}
										}
									}
								}
							}
						}			
					}
					

					
					$waitListMsg				= "";
					$nocatalogMsg				= "";
					if (!$badActorResult) {
						$hotmailStr				= '';
						if (strpos($student_email,'hotmail') !== FALSE) {
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
						$theContent		= "Student $student_call_sign signed up for a $student_level class for the $stdent_semester semester. 
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
											<table style='width:60%;'>
											<tr><td colspan='2'>$student_last_name, $student_first_name ($student_call_sign)</td></tr>
											<tr><td>Email: $student_email</td>
												<td>Phone: $student_ph_code $student_phone</td></tr>
											<tr><td>City: $student_city</td>
												<td>State: $student_state</td></tr>
											<tr><td>Zip Code: $student_zip_code</td>
												<td>Country: $student_country</td></tr>
											<tr><td>Level: $student_level</td>
												<td>Semester: $student_semester</td></tr>";
					if ($result_option == 'option') {
						$content	.= "<tr><td colspan='2'>Class Preferences<br />";
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
						$content		.= "<tr><td>First Class Choice</td><td>$firstChoice</td></tr>
											<tr><td>Second Class Choice</td><td>$secondChoice</td></tr>
											<tr><td>Third Class Choice</td><td>$thirdChoice</td></tr>";
					}
					$box1		= "Whatsapp<br />----";		
					$box2		= "Telegram<br />----";		
					$box3		= "Signal<br />----";		
					$box4		= "Messenger<br />----";		
					if ($student_whatsapp != '') {
						$box1	= "Whatsapp<<br />$student_whatsapp";
					}
					if ($student_telegram != '') {
						$box2	= "Telegram<br />$student_telegram";
					}
					if ($student_signal != '') {
						$box3	= "Signal<br />$student_signal";
					}
					if ($student_messenger != '') {
						$box4	= "Messenger<br />$student_messenger";
					}
					$content	.= "<tr><td colspan='2'>
									<table>
									<tr><td style='text-align:center;'>$box1</td>
										<td style='text-align:center;'>$box2</td>
										<td style='text-align:center;'>$box3</td>
										<td style='text-align:center;'>$box4</td></tr></table></td></tr>";
					if ($student_youth == 'Yes') {
						$content	.= "<tr><td style='text-align:center;'>Youth<br />$student_youth</td>
											<td style='text-align:center;'>Age<br />$student_age</td></tr>
										<tr><td>Parent / Guardian<br />$student_student_parent</td>
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

				}
			}
		}

/////////////	Pass 20 .... handle fundamental  class decision 

	} elseif ("20" == $strPass) {

		if ($doDebug) {
			echo "<br />At pass 20 with $submit<br />";
		}
		$userName			= $inp_callsign;
		$pass3FirstTime		= 'N';
		
		$switchChoice		= '';
		$myInt				= strpos($submit,"Fundamental");
		if ($myInt !== FALSE) {
			$thisMessage		= "<p>You have elected to continue
									signing up to take the Fundamental class again. 
									Please click 'Select Classes' to continue.</p>";
			$switchChoice	= 'stay';
		} else {
			$inp_level			= 'Intermediate';
			$thisMessage		= "<p>You have elected to switch to an Intermediate 
									class. Please click 'Select Classes' to continue.</p>";
			$switchChoice		= 'switch';
		}

		$theMessage				= "<p>Student $inp_callsign signed up for Fundamental, had 
already taken Fundamental and was promoted. At Interupt the student decided to $switchChoice.</p>";
		$theSubject				= "CW Academy Student Registration Fundamental Class Interupt";
		$theRecipient			= 'rolandksmith@gmail.com';
		$mailCode				= 18;
		$thisResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
													'theSubject'=>$theSubject,
													'jobname'=>$jobname,
													'theContent'=>$theMessage,
													'mailCode'=>$mailCode,
													'increment'=>$increment,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug));
		
		$content		.= "<h3>$jobname</h3>
							$thisMessage
							<p><form method='post' action='$theURL' 
							name='studentswitch' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='3'>
							<input type='hidden' name='newInput' value='$newInput'>
							<input type='hidden' name='inp_doAgain' value='$inp_doAgain'>
							<input type='hidden' name='pass3FirstTime' value='$pass3FirstTime'>
							<input type='hidden' name='demonstration' value='$demonstration'>
							<input type='hidden' name='inp_email' value='$inp_email'>
							<input type='hidden' name='inp_mode' value='$inp_mode'>
							<input type='hidden' name='inp_verbose' value='$inp_verbose'>
							<input type='hidden' name='thisOption' value='$thisOption'>
							<input type='hidden' name='inp_phone' value='$inp_phone'>
							<input type='hidden' name='inp_ph_code' value='$inp_ph_code'>
							<input type='hidden' name='inp_city' value='$inp_city'>
							<input type='hidden' name='inp_state' value='$inp_state'>
							<input type='hidden' name='inp_countrya' value='$inp_country_code|$inp_country'>
							<input type='hidden' name='inp_age' value='$inp_age'>
							<input type='hidden' name='inp_zip' value='$inp_zip'>
							<input type='hidden' name='inp_timezone' value=\"$inp_timezone\">
							<input type='hidden' name='inp_callsign' value='$inp_callsign'>
							<input type='hidden' name='inp_lastname' value=\"$inp_lastname\">
							<input type='hidden' name='inp_firstname' value='$inp_firstname'>
							<input type='hidden' name='inp_youth' value='$inp_youth'>
							<input type='hidden' name='inp_student_parent' value='$inp_student_parent'>
							<input type='hidden' name='inp_student_parent_email' value='$inp_student_parent_email'>
							<input type='hidden' name='inp_level' value='$inp_level'>
							<input type='hidden' name='inp_semester' value='$inp_semester'>
							<input type='hidden' name='inp_messaging' value='$inp_messaging'>
							<input type='hidden' name='token' value='$token'>
							<input type='hidden' name='inp_verify' value='$inp_verify'>
							<input type='submit' class='formInputButton' value='Select Classes' name='submit' value='Select Classes'></form>";



///////////	Pass 8

	} elseif ("8" == $strPass) {	//// do the update to the student record then if ok, update class choices
	
	
		if ($doDebug) {
			echo "<br />Arrived at pass 8<br />
				  inp_callsign: $inp_callsign<br />
				  inp_semester: $inp_semester<br />";
		}
		$userName				= $inp_callsign;
		$doProceed				= TRUE;
		$continuePate8A			= FALSE;
		$updateLog				= "";
	
		// get the record
		$sql					= "select * from $studentTableName 
									where student_id = $student_ID";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError("$jobname Pass8",$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}

			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and  retrieved $numSRows rows from $studentTableName table<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_ph_code						= $studentRow->ph_code;
					$student_phone  						= $studentRow->phone;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country_code					= $studentRow->country_code;
					$student_country  						= $studentRow->country;
					$student_time_zone  					= $studentRow->time_zone;
					$student_timezone_id					= $studentRow->timezone_id;
					$student_timezone_offset				= $studentRow->timezone_offset;
					$student_whatsapp						= $studentRow->whatsapp_app;
					$student_signal							= $studentRow->signal_app;
					$student_telegram						= $studentRow->telegram_app;
					$student_messenger						= $studentRow->messenger_app;					
					$student_wpm 	 						= $studentRow->wpm;
					$student_youth  						= $studentRow->youth;
					$student_age  							= $studentRow->age;
					$student_student_parent 				= $studentRow->student_parent;
					$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->level;
					$student_waiting_list 					= $studentRow->waiting_list;
					$student_request_date  					= $studentRow->request_date;
					$student_semester						= $studentRow->semester;
					$student_notes  						= $studentRow->notes;
					$student_welcome_date  					= $studentRow->welcome_date;
					$student_email_sent_date  				= $studentRow->email_sent_date;
					$student_email_number  					= $studentRow->email_number;
					$student_response  						= strtoupper($studentRow->response);
					$student_response_date  				= $studentRow->response_date;
					$student_abandoned  					= $studentRow->abandoned;
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog  					= $studentRow->no_catalog;
					$student_hold_override  				= $studentRow->hold_override;
					$student_messaging  					= $studentRow->messaging;
					$student_assigned_advisor  				= $studentRow->assigned_advisor;
					$student_advisor_select_date  			= $studentRow->advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
					$student_hold_reason_code  				= $studentRow->hold_reason_code;
					$student_class_priority  				= $studentRow->class_priority;
					$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
					$student_promotable  					= $studentRow->promotable;
					$student_excluded_advisor  				= $studentRow->excluded_advisor;
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->available_class_days;
					$student_intervention_required  		= $studentRow->intervention_required;
					$student_copy_control  					= $studentRow->copy_control;
					$student_first_class_choice  			= $studentRow->first_class_choice;
					$student_second_class_choice  			= $studentRow->second_class_choice;
					$student_third_class_choice  			= $studentRow->third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
					$student_catalog_options				= $studentRow->catalog_options;
					$student_flexible						= $studentRow->flexible;
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$student_last_name 						= no_magic_quotes($student_last_name);


					if ($inp_delete == 'Y') {
						if ($doDebug) {
							echo "would delete the record for $inp_callsign here";
						}
						$student_action_log				= "$student_action_log / REGISTER $actionDate student $inp_callsign requested registration to be deleted ";
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
//									$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
							} else {
								$student_action_log		= "$student_action_log /removed student from advisor: $student_assigned_advisor 
															class: $student_assigned_advisor_class and emailed advisor ";
								// get the advisor email address
								$advisorEmail	= $wpdb->get_var("select email from $advisorTableName 
																	where call_sign='$student_assigned_advisor' 
																	and semester = '$inp_semester'");
								if ($advisorEmail === NULL) {		// no record found
									sendErrorEmail("$jobname pass 8 delete student record. Retrieving email address for advisor $student_assigned_advisor failed");
									$advisorEmail	= 'rolandksmith@gmail.com';
								}
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
						$studentUpdateData		= array('tableName'=>$studentTableName,
														'inp_method'=>'update',
														'inp_data'=>array('action_log'=>$student_action_log,
																		  'response'=>'R',
																		  'student_status'=>'',
																		  'excluded_advisor'=>$student_excluded_advisor),
														'inp_format'=>array('%s','%s','%s','%s'),
														'jobname'=>$jobname,
														'inp_id'=>$student_ID,
														'inp_callsign'=>$student_call_sign,
														'inp_semester'=>$student_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateStudent($studentUpdateData);
						if ($updateResult[0] === FALSE) {
							handleWPDBError($jobname,$doDebug);
							$content		.= "Unable to update content in $studentTableName<br />";
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
						$strPass								= '8A';
						$continuePass8A							= TRUE;
						/// if the level, semester, country, US zipcode changes, the new class
						/// selections are required. That is controlled by the variable significantChange
			
						$significantChange						= FALSE;
						$zipChanged								= FALSE;
						$countryChanged							= FALSE;
						$semesterChanged						= FALSE;
						$levelChanged							= FALSE;

						$doUpdate								= FALSE;
						$updateParams							= array();
						$updateLog								= '';
						// see if the browser timezone has changed
						$browser_timezone_id	= $timezone;
						if ($doDebug) {
							echo "The browser returned a timezone_id of $browser_timezone_id<br >";
						}
						if ($browser_timezone_id != $student_time_zone) {
							$updateLog							.= "time_zone changed from $student_time_zone to $browser_timezone_id. ";
							$student_time_zone					= $inp_lastname;
							$updateParams[]						= "time_zone|$browser_timezone_id|s";
							$doUpdate							= TRUE;
						}					
						if ($inp_lastname != $student_last_name) {
							$updateLog							.= "last_name changed from $student_last_name to $inp_lastname. ";
							$student_last_name					= $inp_lastname;
							$myStr								= addslashes($inp_lastname);
							$updateParams[]						= "last_name|$myStr|s";
							$doUpdate							= TRUE;
						}
						if ($inp_firstname != $student_first_name) {
							$updateParams[]						= "first_name|$inp_firstname|s";
							$doUpdate							= TRUE;
							$updateLog							.= "first_name changd from $student_first_name to $inp_firstname. ";
							$student_first_name					= $inp_firstname;
						}
						if ($inp_email != $student_email) {
							$updateParams[]						= "email|$inp_email|s";
							$doUpdate							= TRUE;
							$updateLog							.= "email changed from $student_email to $inp_email. ";
							$student_email						= $inp_email;
						}
						if (strcmp($inp_phone,$student_phone) != 0) {
							$updateParams[]						= "phone|$inp_phone|s";
							$doUpdate							= TRUE;
							$updateLog							.= "phone changed from $student_phone to $inp_phone. ";
							$student_phone						= $inp_phone;
						}
						if (strcmp($inp_ph_code,$student_ph_code) != 0) {
							$updateParams[]						= "ph_code|$inp_ph_code|s";
							$doUpdate							= TRUE;
							$updateLog							.= "ph_code changed from $student_ph_code to $inp_ph_code. ";
							$student_ph_code					= $inp_ph_code;
						}
						if ($inp_messaging != $student_messaging) {
							$updateParams[]						= "messaging|$inp_messaging|s";
							$doUpdate							= TRUE;
							$updateLog							.= "messaging changed from $student_messaging to $inp_messaging. ";
							$student_messaging					= $inp_messaging;
						}
						if ($inp_city != $student_city) {
							$updateParams[]						= "city|$inp_city;|s";
							$doUpdate							= TRUE;
							$updateLog							.= "city changed from $student_city to $inp_city. ";
							$student_city						= $inp_city;
						}
						if ($inp_state != $student_state) {
							$updateParams[]						= "state|$inp_state|s";
							$doUpdate							= TRUE;
							$updateLog							.= "state changed from $student_state to $inp_state. ";
							$student_state						= $inp_state;
						}

						if ($inp_zip != $student_zip_code) {
							$updateParams[]						= "zip_code|$inp_zip|s";
							$doUpdate							= TRUE;
							$updateLog							.= "zip_code changed from $student_zip_code to $inp_zip. ";
							$student_zip_code					= $inp_zip;
							$zipChanged							= TRUE;
						}
						$myCountryCodeA							= "";
						$myCountryA								= "";
						$gotCountryA							= FALSE;
						$myCountryCodeB							= "";
						$myCountryB								= "";
						$gotCountryB							= "";
						if ($inp_countrya  != '') {
							$myArray							= explode("|",$inp_countrya);
							$myCountryCodeA						= $myArray[0];
							$myCountryA							= $myArray[1];
							$gotCountryA						= TRUE;
						}
						if ($inp_countryb  != '') {
							$myArray							= explode("|",$inp_countryb);
							$myCountryCodeB						= $myArray[0];
							$myCountryB							= $myArray[1];
							$gotCountryB						= TRUE;
						}
						/* 	deciding which country code to use
							gotCountryA		gotCountryB		action
							TRUE			FALSE			use countryA data
							FALSE			TRUE			use country B data
							TRUE			TRUE			see what student_country_code is
																if matches A, use B
																if matches B, use A	
						*/
						if ($gotCountryA == TRUE && $gotCountryB == FALSE) {
							if ($doDebug) {
								echo "gotCountryA is TRUE gotCountryB is FALSE. Using countryA $inp_countrya<br />";
							}
							$thisCountry						= $inp_countrya;
						} elseif ($gotCountryA == FALSE && $gotCountryB == TRUE) {
							if ($doDebug) {
								echo "gotCountryA is FALSE gotCountryB is TRUE. Using countryB $inp_countryb<br />";
							}
							$thisCountry						= $inp_countryb;
						} elseif ($gotCountryA == TRUE && $gotCountryB == TRUE) {
							if ($doDebug) {
								echo "gotCountryA is TRUE gotCountryB is TRUE. Making decision<br />";
							}
							$myArray							= explode("|",$inp_countrya);
							$thisCode							= $myArray[0];
							if ($thisCode == $student_country_code) {		// use b
								$thisCountry					= $inp_countryb;
								if ($doDebug) {
									echo "student_country_code is $student_country_code and matches $inp_countrya. Using $inp_countryb<br />";
								}
							} else {										// use a
								$thisCountry					= $inp_countrya;
								if ($doDebug) {
									echo "student_country_code is $student_country_code and does not match $inp_countrya. Using $inp_countrya<br />";
								}
							}
						} else {
							if ($doDebug) {
								echo "unclear which country info to use as both are false. Assuming no change<br />";
							}
						}
						$myArray								= explode("|",$thisCountry);
						$myCountryCode							= $myArray[0];
						$myCountryName							= $myArray[1];
						if ($myCountryCode != $student_country_code) {
							$updateParams[]						= "country_code|$myCountryCode|s";
							$updateParams[]						= "country|$myCountryName|s";
							$doUpdate							= TRUE;
							$updateLog							.= "country_code changed from $student_country_code to $myCountryCode and country changed from $student_country to $myCountryName. ";
							$student_country_code				= $myCountryCode;
							$student_country					= $myCountryName;
							$significantChange					= TRUE;
							$countryChanged						= TRUE;
						}
						if ($zipChanged && $student_country_code == 'US') {
							$significantChange					= TRUE;
						}
						if ($inp_youth != $student_youth) {
							$updateParams[]						= "youth|$inp_youth|s";
							$doUpdate							= TRUE;
							$updateLog							.= "youth changed from $student_youth to $inp_youth. ";
							$student_youth						= $inp_youth;
						}
						if ($inp_age != $student_age) {
							$updateParams[]						= "age|$inp_age|s";
							$doUpdate							= TRUE;
							$updateLog							.= "age changed from $student_age to $inp_age. ";
							$student_age						= $inp_age;
						}
						if ($inp_student_parent != $student_student_parent) {
							$updateParams[]						= "student_parent|$inp_student_parent|s";
							$doUpdate							= TRUE;
							$updateLog							.= "student_parent changed from $student_student_parent to $inp_student_parent. ";
							$student_student_parent				= $inp_student_parent;
						}
						if ($inp_student_parent_email != $student_student_parent_email) {
							$updateParams[]						= "student_parent_email|$inp_student_parent_email|s";
							$doUpdate								= TRUE;
							$updateLog								.= "student_parent_email changed from $student_student_parent_email to $inp_student_parent_email. ";
							$student_student_parent_email			= $inp_student_parent_email;
						}
						if ($new_semester != 'not supplied') {
							if ($new_semester != $student_semester) {
								$updateParams[]							= "semester|$new_semester|s";
								$doUpdate								= TRUE;
								$updateLog								.= "semester changed from $student_semester to $new_semester. ";
								$student_semester						= $new_semester;
								$semesterChanged						= TRUE;
								$updateParams[]							= "email_sent_date||s";
								$updateParams[]							= "email_number|0|s";
								$updateParams[]							= "response||s";
								$updateParams[]							= "response_date||s";
								$updateParams[]							= "welcome_date||s";
								$updateParams[]							= "first_class_choice|None|s";
								$updateParams[]							= "second_class_choice|None|s";
								$updateParams[]							= "third_class_choice|None|s";
								$updateParams[]							= "first_class_choice_utc|None|s";
								$updateParams[]							= "second_class_choice_utc|None|s";
								$updateParams[]							= "third_class_choice_utc|None|s";
								$updateParams[]							= "catalog_options||s";
								$updateParams[]							= "flexible||s";
								$significantChange						= TRUE;
							}
						}
						if ($inp_level != $student_level) {
							$updateParams[]						= "level|$inp_level|s";
							$doUpdate							= TRUE;
							$updateLog							.= "level changed from $student_level to $inp_level. ";
							$student_level						= $inp_level;
							$significantChange					= TRUE;
							$levelChanged						= TRUE;
							$updateParams[]							= "email_sent_date||s";
							$updateParams[]							= "email_number|0|s";
							$updateParams[]							= "response||s";
							$updateParams[]							= "response_date||s";
							$updateParams[]							= "welcome_date||s";
							$significantChange						= TRUE;
						}
						if ($inp_whatsapp != $student_whatsapp) {
							$updateParams[]						= "whatsapp_app|$inp_whatsapp|s";
							$doUpdate							= TRUE;
							$updateLog							.= "whatsapp changed from $student_whatsapp to $inp_whatsapp. ";
							$student_whatsapp					= $inp_whatsapp;
						}
						if ($inp_telegram != $student_telegram) {
							$updateParams[]						= "telegram_app|$inp_telegram|s";
							$doUpdate							= TRUE;
							$updateLog							.= "telegram changed from $student_telegram to $inp_telegram. ";
							$student_telegram					= $inp_telegram;
						}
						if ($inp_signal != $student_signal) {
							$updateParams[]						= "signal_app|$inp_signal|s";
							$doUpdate							= TRUE;
							$updateLog							.= "signal changed from $student_signal to $inp_signal. ";
							$student_signal						= $inp_signal;
						}
						if ($inp_messenger != $student_messenger) {
							$updateParams[]						= "messenger_app|$inp_messenger|s";
							$doUpdate							= TRUE;
							$updateLog							.= "messenger changed from $student_messenger to $inp_messenger. ";
							$student_messenger					= $inp_messenger;
						}
						/// if semester changed, check to see if the offset changes as well
						if ($semesterChanged) {
							if ($doDebug) {
								echo "semester has changed. Checking to see if offset changes<br />";
							}
							$myArray			= explode(" ",$new_semester);
							$thisYear			= $myArray[0];
							$thisMonDay			= $myArray[1];
							$myConvertArray		= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01','JAN/FEB'=>'-01-01','APR/MAY'=>'-04-01','MAY/JUN'=>'-05-01','SEP/OCT'=>'-09-01','Apr/May'=>'-04-01');
							$myMonDay			= $myConvertArray[$thisMonDay];
							$thisNewDate		= "$thisYear$myMonDay 00:00:00";
							if ($doDebug) {
								echo "converted $new_semester to $thisNewDate<br />";
							}
							$dateTimeZoneLocal 	= new DateTimeZone($student_timezone_id);
							$dateTimeZoneUTC 	= new DateTimeZone("UTC");
							$dateTimeLocal 		= new DateTime($thisNewDate,$dateTimeZoneLocal);
							$dateTimeUTC		= new DateTime($thisNewDate,$dateTimeZoneUTC);
							$php2 				= $dateTimeZoneLocal->getOffset($dateTimeUTC);
							$offset 			= $php2/3600;
							if ($offset != $student_timezone_offset) {
								$updateParams[]						= "timezone_offset|$offset|s";
								$doUpdate							= TRUE;
								if ($doDebug) {
									echo "changed offset from $student_timezone_offset to $offset<br />";
								}
								$student_timezone_offset	= $offset;
								$updateLog					.= "Timezone_offseet changed from $student_timezone_offset to $offset. ";
								$student_timezone_offset	= $offset;
							}
						}

						if ($significantChange) {				/// see what changed
							if ($doDebug) {
								echo "have a significantChange<br />";
								if ($zipChanged) {
									echo "Zip code changed<br />";
								}
								if ($countryChanged) {
									echo "Country changed<br />";
								}
								if ($semesterChanged) {
									echo "Semester changed<br />";
								}
								if ($levelChanged) {
									echo "Level changed<br />";
								}
							}
							$doProceed							= TRUE;
							if ($doDebug) {
								echo "Significant change occurred.<br />";
							}
							
/*
							$updateParams[]							= "first_class_choice|None|s";
							$updateParams[]							= "second_class_choice|None|s";
							$updateParams[]							= "third_class_choice|None|s";
							$updateParams[]							= "first_class_choice_utc|None|s";
							$updateParams[]							= "second_class_choice_utc|None|s";
							$updateParams[]							= "third_class_choice_utc|None|s";
							$updateParams[]							= "catalog_options||s";
							$updateParams[]							= "flexible||s";
*/
							if ($zipChanged && $student_country_code == 'US' || $countryChanged && $student_country_code == 'US') {	
								if ($doDebug) {
									echo "zipChanged and country is US OR country changed and country is US<br />";
								}
								// get the new timezone_id
								$new_timezone_info				= getOffsetFromZipcode($student_zip_code,$student_semester,TRUE,$testMode,$doDebug);
								// returns	array(status,timezone_id,UTC offset,matchMsg)
								if ($doDebug) {
									echo "got new timezone info:<br /><pre>";
									print_r($new_timezone_info);
									echo "</pre><br />";
								}
								if ($new_timezone_info[0] == 'NOK') {
									if ($doDebug) {
										echo "getOffsetFromZipcode returned NOK. Error: $new_timezone_info[3]<br />";
									}
									$errorMsg					= "Student Registration Pass 8. getOffsetFromZipcode returned NOK. Error: $new_timezone_info[3]";
									sendErrorEmail($errorMsg);
									$new_timezone_id			= $student_timezone_id;
									$new_timezone_offset		= $student_timezone_offset;
									$theMsg						= $new_timezone_info[3];
								} else {
									$new_timezone_id			= $new_timezone_info[1];
									$new_timezone_offset		= $new_timezone_info[2];
									$theMsg						= $new_timezone_info[3];
								}
								if ($doDebug) {
									echo "have new_timezone_id of $new_timezone_id. Student currently has $student_timezone_id<br />";
								}
								if ($new_timezone_offset != $student_timezone_offset) {
									if ($doDebug) {
										echo "updating timezone info and choices<br />";
									}
									$updateParams[]					= "timezone_id|$new_timezone_id|s";
									$updateParams[]					= "timezone_offset|$new_timezone_offset|f";
									$doUpdate						= TRUE;
									$updateLog						.= "timezone_id changed from $student_timezone_id to $inp_timezone_id. ";
									$updateLog						.= "timezone_offset changed from $student_timezone_offset to $new_timezone_offset. ";
									$updateParams[]					= "first_class_choice|None|s";
									$updateParams[]					= "second_class_choice|None|s";
									$updateParams[]					= "third_class_choice|None|s";
									$updateParams[]					= "first_class_choice_utc|None|s";
									$updateParams[]					= "second_class_choice_utc|None|s";
									$updateParams[]					= "third_class_choice_utc|None|s";
									$updateParams[]					= "catalog_options||s";
									$updateParams[]					= "flexible||s";
								} else {
									if ($doDebug) {
										echo "timezone did not change.<br />";
									}
//									$significantChange				= FALSE;
								}
							}
							if ($zipChanged && $student_country_code == 'CA') {
								$countryChanged						= TRUE;
							}
							if ($countryChanged && $student_country_code != 'US') {
								if ($doDebug) {
									echo "country changed. dealing with a non-us country of $student_country_code<br />";
								}
								$updateParams[]					= "first_class_choice|None|s";
								$updateParams[]					= "second_class_choice|None|s";
								$updateParams[]					= "third_class_choice|None|s";
								$updateParams[]					= "first_class_choice_utc|None|s";
								$updateParams[]					= "second_class_choice_utc|None|s";
								$updateParams[]					= "third_class_choice_utc|None|s";
								$updateParams[]					= "catalog_options||s";
								$updateParams[]					= "flexible||s";
								$timezone_identifiers 			= DateTimeZone::listIdentifiers( DateTimeZone::PER_COUNTRY, $student_country_code );
								$myInt							= count($timezone_identifiers);
								if ($doDebug) {
									echo "retrieved $myInt timezone identifiers for $student_country_code<br />";
								}
								if ($myInt == 1) {									//  only 1 found. Use that and continue
									$inp_timezone_id			= $timezone_identifiers[0];
									$myArray					= explode(" ",$inp_semester);
									$thisYear					= $myArray[0];
									$thisMonDay					= $myArray[1];
									$myConvertArray				= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
									$myMonDay					= $myConvertArray[$thisMonDay];
									$thisNewDate				= "$thisYear$myMonDay 00:00:00";
									if ($doDebug) {
										echo "converted $inp_semester to $thisNewDate for $student_country_code<br />";
									}
									$dateTimeZoneLocal 			= new DateTimeZone($inp_timezone_id);
									$dateTimeZoneUTC 			= new DateTimeZone("UTC");
									$dateTimeLocal 				= new DateTime($thisNewDate,$dateTimeZoneLocal);
									$dateTimeUTC				= new DateTime($thisNewDate,$dateTimeZoneUTC);
									$php2 						= $dateTimeZoneLocal->getOffset($dateTimeUTC);
									$inp_timezone_offset 		= $php2/3600;
									if ($doDebug) {
										echo "finished $student_country_code and have timezone_id of $inp_timezone_id and offset of $inp_timezone_offset. Updating database<br >";
									}

									$updateParams[]					= "timezone_id|$inp_timezone_id|s";
									$doUpdate						= TRUE;
									$updateLog						.= "timezone_id changed from $student_timezone_id to $inp_timezone_id. ";
									$student_timezone_id			= $inp_timezone_id;
									$updateParams[]					= "timezone_offset|$inp_timezone_offset|f";
									$updateLog						.= "timezone_offset changed from $student_timezone_offset to $inp_timezone_offset. ";
									$student_timezone_offset		= $inp_timezone_offset;
									
								} else {					// multiple timezone options
									$timezoneSelector			= "<table style='width:400px;'>";
									$ii							= 1;
									if ($doDebug) {
										echo "have the list of identifiers for $student_country_code<br />";
									}
									foreach ($timezone_identifiers as $thisID) {
										if ($doDebug) {
											echo "Processing $thisID<br />";
										}
										if ($myInt == 1) {
											$selector		= "checked";
										} else {
											$selector			= "";
											if ($browser_timezone_id == $thisID) {
												$selector		= "checked";
											}
										}
										$dateTimeZoneLocal 	= new DateTimeZone($thisID);
										$dateTimeLocal 		= new DateTime("now",$dateTimeZoneLocal);
										$localDateTime 		= $dateTimeLocal->format('h:i A');
										$myInt				= strpos($thisID,"/");
										$myCity				= substr($thisID,$myInt+1);
										switch($ii) {
											case 1:
												$timezoneSelector	.= "<tr><td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td>";
												$ii++;
												break;
											case 2:
												$timezoneSelector	.= "<td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td>";
												$ii++;
												break;
											case 3:
												$timezoneSelector	.= "<td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID' $selector>$myCity<br />$localDateTime</td></tr>";
												$ii					= 1;
												break;
										}
									}
									if ($ii == 2) {			// need two blank cells
										$timezoneSelector			.= "<td>&nbsp;</td><td>&nbsp;</td></tr>";
									} elseif ($ii == 3) {	// need one blank cell
										$timezoneSelector			.= "<td>&nbsp;</td></tr>";
									}
									if ($doDebug) {
										echo "Putting the form together<br />";
									}
									$json_updateParams		= json_encode($updateParams);
									$content		.= "<h3>$jobname</h3>
														<h4>Select Time Zone City</h4>
														<p>Since your location has changed, please select the city that best represents the timezone you will be in during the $inp_semester semester. 
														The current local time is displayed underneath the city.</p>
														<form method='post' action='$theURL' 
														name='tzselection' ENCTYPE='multipart/form-data'>
															<input type='hidden' name='strpass' value='8A'>
															<input type='hidden' name='inp_mode' value='$inp_mode'>
															<input type='hidden' name='inp_verbose' value='$inp_verbose'>
															<input type='hidden' name='waitingList' value='$waitingList'>
															<input type='hidden' name='nocatalog' value='$nocatalog'>
															<input type='hidden' name='student_ID' value='$student_ID'>
															<input type='hidden' name='inp_semester' value='$student_semester'>
															<input type='hidden' name='doUpdate' value='$doUpdate'>
															<input type='hidden' name='inp_callsign' value='$student_call_sign'>
															<input type='hidden' name='continuePass8A' value='$continuePass8A'>
															<input type='hidden' name='token' value='$token'>
															<input type='hidden' name='updateLog' value='$updateLog'>
															<input type='hidden' name='verifyMode' value='$verifyMode'>
															<input type='hidden' name='json_updateParams' value='$json_updateParams'>
															<input type='hidden' name='inp_bypass' value='Y'>
														$timezoneSelector
														<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
														</table>
														</form>";
									$doProceed		= FALSE;
									$continuePass8A	= FALSE;
									$strPass		= '8';							
								}
							}
						}
					}
				}
			} else {				/// no record found to update
				if ($doDebug) {
					echo "No $studentTableName record found for $inp_callsign<br />";
				}
				$errorMsg			= "Student Registration Pass 8 getting student record for $inp_callsign yielded 
no record. Can not store the update";
			}
		}						
		if ($doDebug) {
			$myStr    = 'FALSE';
			if ($continuePass8A) {
				$myStr = 'TRUE';
			}
			$myStr1   = 'FALSE';
			if ($significantChange) {
				$myStr1 = 'TRUE';
			}
			$myStr2		= 'FALSE';
			if ($doUpdate) {
				$myStr2 = 'TRUE';
			}
			echo "at end of pass8 ready for 8A<br />
					strPass: $strPass<br />
					continuePass8A: $myStr<br />
					 significantChange: $myStr1<br />
					 inp_bypass: $inp_bypass<br />
					 doUpdate: $myStr2<br />
					 current updateParams:<br /><pre>";
					 print_r($updateParams);
					 echo "</pre><br />";
		}
	}

	if ("8A" == $strPass) {
	
		if ($doDebug) {
			echo "<br />At pass 8A<br />verifyMode: $verifyMode<br />";
		}
		$goOn							= TRUE;
		$doProceed						= TRUE;
		$updateLog8A					= "";
		if ($continuePass8A) {
			if ($inp_bypass == 'Y') {				/// get the timezone_id and offset based on selection made above
				// get the student record
				$sql					= "select * from $studentTableName 
											where student_id=$student_ID";
				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError($jobname,$doDebug);
					$goOn			= FALSE;
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						if (!$doDebug) {
							return $content;
						}
					}

					$numSRows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and  retrieved $numSRows rows from $studentTableName table<br />";
					}
					if ($numSRows > 0) {
						foreach ($wpw1_cwa_student as $studentRow) {
							$student_ID								= $studentRow->student_id;
							$student_call_sign						= strtoupper($studentRow->call_sign);
							$student_first_name						= $studentRow->first_name;
							$student_last_name						= stripslashes($studentRow->last_name);
							$student_email  						= strtolower(strtolower($studentRow->email));
							$student_ph_code						= $studentRow->ph_code;
							$student_phone  						= $studentRow->phone;
							$student_city  							= $studentRow->city;
							$student_state  						= $studentRow->state;
							$student_zip_code  						= $studentRow->zip_code;
							$student_country_code					= $studentRow->country_code;
							$student_country  						= $studentRow->country;
							$student_time_zone  					= $studentRow->time_zone;
							$student_timezone_id					= $studentRow->timezone_id;
							$student_timezone_offset				= $studentRow->timezone_offset;
							$student_whatsapp						= $studentRow->whatsapp_app;
							$student_signal							= $studentRow->signal_app;
							$student_telegram						= $studentRow->telegram_app;
							$student_messenger						= $studentRow->messenger_app;					
							$student_wpm 	 						= $studentRow->wpm;
							$student_youth  						= $studentRow->youth;
							$student_age  							= $studentRow->age;
							$student_student_parent 				= $studentRow->student_parent;
							$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
							$student_level  						= $studentRow->level;
							$student_waiting_list 					= $studentRow->waiting_list;
							$student_request_date  					= $studentRow->request_date;
							$student_semester						= $studentRow->semester;
							$student_notes  						= $studentRow->notes;
							$student_welcome_date  					= $studentRow->welcome_date;
							$student_email_sent_date  				= $studentRow->email_sent_date;
							$student_email_number  					= $studentRow->email_number;
							$student_response  						= strtoupper($studentRow->response);
							$student_response_date  				= $studentRow->response_date;
							$student_abandoned  					= $studentRow->abandoned;
							$student_student_status  				= strtoupper($studentRow->student_status);
							$student_action_log  					= $studentRow->action_log;
							$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
							$student_selected_date  				= $studentRow->selected_date;
							$student_no_catalog  					= $studentRow->no_catalog;
							$student_hold_override  				= $studentRow->hold_override;
							$student_messaging  					= $studentRow->messaging;
							$student_assigned_advisor  				= $studentRow->assigned_advisor;
							$student_advisor_select_date  			= $studentRow->advisor_select_date;
							$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
							$student_hold_reason_code  				= $studentRow->hold_reason_code;
							$student_class_priority  				= $studentRow->class_priority;
							$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
							$student_promotable  					= $studentRow->promotable;
							$student_excluded_advisor  				= $studentRow->excluded_advisor;
							$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
							$student_available_class_days  			= $studentRow->available_class_days;
							$student_intervention_required  		= $studentRow->intervention_required;
							$student_copy_control  					= $studentRow->copy_control;
							$student_first_class_choice  			= $studentRow->first_class_choice;
							$student_second_class_choice  			= $studentRow->second_class_choice;
							$student_third_class_choice  			= $studentRow->third_class_choice;
							$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
							$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
							$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
							$student_catalog_options				= $studentRow->catalog_options;
							$student_flexible						= $studentRow->flexible;
							$student_date_created 					= $studentRow->date_created;
							$student_date_updated			  		= $studentRow->date_updated;

							$student_last_name 						= no_magic_quotes($student_last_name);
						}
					} else {
						if ($doDebug) {
							echo "rereading $studentTableName table for id $student_ID did not return 
									any records. Aborting<br />";
						}
						sendErrorEmail("$jobname Pass 8A Rereading $studentTableName table for id $student_ID did not return any records");
						$goOn		= FALSE;
					}
				}
				if ($goOn) {
					$updateParams		= json_decode($json_updateParams,TRUE);
					if ($doDebug) {
						echo "Handling the bypass. updateParams:<br /><pre>";
						print_r($updateParams);
						echo "</pre><br />have the new timezone_id as $inp_timezone_id. Now get the timezone offset for $student_semester<br />";
					}
					$myArray			= explode(" ",$student_semester);
					$thisYear			= $myArray[0];
					$thisMonDay			= $myArray[1];
					$myConvertArray		= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
					$myMonDay			= $myConvertArray[$thisMonDay];
					$thisNewDate		= "$thisYear$myMonDay 00:00:00";
					if ($doDebug) {
						echo "converted $student_semester to $thisNewDate<br />";
					}
					$dateTimeZoneLocal 	= new DateTimeZone($inp_timezone_id);
					$dateTimeZoneUTC 	= new DateTimeZone("UTC");
					$dateTimeLocal 		= new DateTime($thisNewDate,$dateTimeZoneLocal);
					$dateTimeUTC		= new DateTime($thisNewDate,$dateTimeZoneUTC);
					$php2 				= $dateTimeZoneLocal->getOffset($dateTimeUTC);
					$offset 			= $php2/3600;
					if ($doDebug) {
						echo "the offset is $offset<br />";
					}
					$updateParams[]					= "timezone_id|$inp_timezone_id|s";
					$updateParams[]					= "timezone_offset|$offset|f";
					$doUpdate						= TRUE;
					$updateLog8A					.= "timezone_id changed from $student_timezone_id to $inp_timezone_id. ";
					$updateLog8A					.= "timezone_offset changed from $student_timezone_offset to $offset. ";
					$theMsg							= '';
					$significantChange				= TRUE;
				} else {
					$doProceed						= FALSE;
				}
			}
			if ($doProceed) {
				if ($doUpdate) {
					if ($updateLog8A != '') {
						if (!isset($updateLog)) {
							$updateLog					= $updateLog8A;
						} else {
							$updateLog						.= $updateLog8A;
						}
					}
					$student_action_log					= "$student_action_log / $actionDate REGISTER $updateLog";
					$updateParams[]						= "action_log|$student_action_log|s";
//					$updateParams[]						= "notes|$theMsg|s";

					if ($doDebug) {
						echo "updateParams before filtering:<br /><pre>";
						print_r($updateParams);
						echo "</pre><br />";
					}

					// filter out any duplicate updates (take the last one)
					$thisArray						= array();
					foreach($updateParams as $thisKey => $thisValue) {
						$myArray					= explode("|",$thisValue);
						$thisArray[$myArray[0]]		= $thisValue;
					}
					$newParams						= array();
					foreach($thisArray as $thisKey => $thisValue) {
						$newParams[] 				= $thisValue;
					}
				
					if ($doDebug) {
						echo "Updating record for $inp_callsign<br />";
						foreach($newParams as $myKey=>$myValue) {
							echo "$myKey = $myValue<br />";
						}
					}
				
					$studentUpdateData		= array('tableName'=>$studentTableName,
													'inp_data'=>$newParams,
													'inp_format'=>array(''),
													'inp_method'=>'update',
													'jobname'=>$jobname,
													'inp_id'=>$student_ID,
													'inp_callsign'=>$student_call_sign,
													'inp_semester'=>$student_semester,
													'inp_who'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
					$updateResult	= updateStudent($studentUpdateData);
					if ($updateResult[0] === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$lastError			= $wpdb->last_error;
						if ($lastError != '') {
							handleWPDBError($jobname,$doDebug);
							$content		.= "Fatal program error. System Admin has been notified";
							if (!$doDebug) {
								return $content;
							}
						}

						$theMessage		= "";
					}
				} else {
					if ($doDebug) {
						echo "No updates requested for $inp_callsign<br />";
					}
				}
				// decide if the student can update class choices
				/// if less than 22 days until the semester starts and they have been assigned 
				/// to an advisor, they can only change
				/// personal information. Display the results and quit

				$daysToGo					= days_to_semester($student_semester);
				if ($verifyMode) {
					if ($student_semester == $nextSemester) {
						$daysToGo	= 45;
					}
				}
				if ($student_student_status == 'S' || $student_student_status == 'Y') {
					if ($student_semester == $nextSemester && $daysToGo < 22) {	// changes done
						$content			.= "<h3>$jobname Completed</h3>
												<p><p>Your registration has been updated. <b>Most communications from CW Academy will be by 
												email, so make sure these emails are not marked as spam</b>. In most email programs you do 
												that by adding <u>cwacademy@cwa.cwops.org</u> to your contact list in your email program. More 
												information is available at <a href='https://www.whitelist.guide/' target='_blank'>Email 
												Whitelist Guide</a>.</p>
												<button onClick=\"window.print()\">Click to print this<br />page for your records</button>
												<p> Your information:
												<table style='width:60%;'>
												<tr><td colspan='2'>$student_last_name, $student_first_name ($student_call_sign)</td></tr>
												<tr><td>Email: $student_email</td>
													<td>Phone: $student_ph_code $student_phone</td></tr>
												<tr><td>City: $student_city</td>
													<td>State: $student_state</td></tr>
												<tr><td>Zip Code: $student_zip_code</td>
													<td>Country: $student_country</td></tr>
												<tr><td>Level: $student_level</td>
													<td>Semester: $student_semester</td></tr>";
						if ($result_option == 'option') {
							$content	.= "<tr><td colspan='2'>Class Preferences<br />";
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
							$content		.= "<tr><td>First Class Choice</td><td>$firstChoice</td></tr>
												<tr><td>Second Class Choice</td><td>$secondChoice</td></tr>
												<tr><td>Third Class Choice</td><td>$thirdChoice</td></tr>";
						}
						$box1		= "Whatsapp<br />----";		
						$box2		= "Telegram<br />----";		
						$box3		= "Signal<br />----";		
						$box4		= "Messenger<br />----";		
						if ($student_whatsapp != '') {
							$box1	= "Whatsapp<<br />$student_whatsapp";
						}
						if ($student_telegram != '') {
							$box2	= "Telegram<br />$student_telegram";
						}
						if ($student_signal != '') {
							$box3	= "Signal<br />$student_signal";
						}
						if ($student_messenger != '') {
							$box4	= "Messenger<br />$student_messenger";
						}
						$content	.= "<tr><td colspan='2'>
										<table>
										<tr><td style='text-align:center;'>$box1</td>
											<td style='text-align:center;'>$box2</td>
											<td style='text-align:center;'>$box3</td>
											<td style='text-align:center;'>$box4</td></tr></table></td></tr>";
						if ($student_youth == 'Yes') {
							$content	.= "<tr><td style='text-align:center;'>Youth<br />$student_youth</td>
												<td style='text-align:center;'>Age<br />$student_age</td></tr>
											<tr><td>Parent / Guardian<br />$student_student_parent</td>
												<td>Parent / Guardian Email<br />$student_student_parent_email</td></tr>";
						}
						$content		.= "</table></p>
											<p>If circumstances or your information changes, you can update this information by returning to the 
											<a href='$theURL'>CW Academy Student Registration</a> page and 
											entering your call sign, email address, and phone number.</p>
											<p>Please print this page for your reference.<br /><br />
											73,<br />
											CW Academy</p>
											<br /><br />You may close this window";
						$doProceed		= FALSE;
					}
				}
				if ($doProceed) {
					// now get the catalog and display it


					$inp_data			= array('student_semester'=>$student_semester, 
												'student_level'=>$student_level, 
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
							$option_message		= "<p>The Class Catalog for $student_level Level classes is now available. 
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
											<input type='hidden' name='student_ID' value='$student_ID'>
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
			}
		} 		



	} elseif ("9" == $strPass) {				/// update the catalog choices
	
		if ($doDebug) {
			echo "<br />arrived at pass 9<br />";
		}
		
		$userName				= $inp_callsign;
		$firstChoice			= '';
		$secondChoice			= '';
		$thirdChoice			= '';

		if ($doDebug) {
			echo "Have the following information:
				inp_callsign: $inp_callsign<br />
				student_ID: $student_ID<br />
				inp_verify: $inp_verify<br />";
		}
		
		$actionLogUpdates	= "";
		$doUpdateStudent	= FALSE; 
		
		//// get the student record for update and display
		$sql				= "select * from $studentTableName 
							   where student_id=$student_ID";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}

			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "retrieved $numSRows rows from $studentTableName table<br >";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_ph_code						= $studentRow->ph_code;
					$student_phone  						= $studentRow->phone;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country_code					= $studentRow->country_code;
					$student_country  						= $studentRow->country;
					$student_time_zone  					= $studentRow->time_zone;
					$student_timezone_id					= $studentRow->timezone_id;
					$student_timezone_offset				= $studentRow->timezone_offset;
					$student_whatsapp						= $studentRow->whatsapp_app;
					$student_signal							= $studentRow->signal_app;
					$student_telegram						= $studentRow->telegram_app;
					$student_messenger						= $studentRow->messenger_app;					
					$student_wpm 	 						= $studentRow->wpm;
					$student_youth  						= $studentRow->youth;
					$student_age  							= $studentRow->age;
					$student_student_parent 				= $studentRow->student_parent;
					$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->level;
					$student_waiting_list 					= $studentRow->waiting_list;
					$student_request_date  					= $studentRow->request_date;
					$student_semester						= $studentRow->semester;
					$student_notes  						= $studentRow->notes;
					$student_welcome_date  					= $studentRow->welcome_date;
					$student_email_sent_date  				= $studentRow->email_sent_date;
					$student_email_number  					= $studentRow->email_number;
					$student_response  						= strtoupper($studentRow->response);
					$student_response_date  				= $studentRow->response_date;
					$student_abandoned  					= $studentRow->abandoned;
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog  					= $studentRow->no_catalog;
					$student_hold_override  				= $studentRow->hold_override;
					$student_messaging  					= $studentRow->messaging;
					$student_assigned_advisor  				= $studentRow->assigned_advisor;
					$student_advisor_select_date  			= $studentRow->advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
					$student_hold_reason_code  				= $studentRow->hold_reason_code;
					$student_class_priority  				= $studentRow->class_priority;
					$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
					$student_promotable  					= $studentRow->promotable;
					$student_excluded_advisor  				= $studentRow->excluded_advisor;
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->available_class_days;
					$student_intervention_required  		= $studentRow->intervention_required;
					$student_copy_control  					= $studentRow->copy_control;
					$student_first_class_choice  			= $studentRow->first_class_choice;
					$student_second_class_choice  			= $studentRow->second_class_choice;
					$student_third_class_choice  			= $studentRow->third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
					$student_catalog_options				= $studentRow->catalog_options;
					$student_flexible						= $studentRow->flexible;
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$student_last_name 						= no_magic_quotes($student_last_name);

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
							$updateParams[]						= "no_catalog|Y|s";
							$actionLogUpdates					.= "Set no_catalog to Y, ";
							$doUpdateStudent						= TRUE;
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
									$updateParams[]				= 'flexible|Y|s';
									$actionLogUpdates			.= "Set flexible to Y, ";
									$doUpdateStudent				= TRUE;
									$student_flexible			= "Y";
									$myStr						= "";
								}
							} else {
								if ($student_flexible != 'N') {
									$student_flexible			= 'N';
									$updateParams[]				= 'flexible|N|s';
									$actionLogUpdates			.= "Set flexible to N, ";
									$doUpdateStudent			= TRUE;
									$student_flexible			= "N";
								}
							}
							if ($student_catalog_options != '$myStr') {
								$student_catalog_options		= $myStr;
								$updateParams[]					= "catalog_options|$student_catalog_options|s";
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
							$updateParams[]			= 'no_catalog|N|s';
							$actionLogUpdates		.= "Set no_catalog to N, ";
						}
						$updateParams[]				= "abandoned|N|s";
						$doUpdateStudent			= TRUE;

						if ($inp_flex == 'Y') {
							$updateParams[]			= "flexible|Y|s";
							$actionLogUpdates		.= "set flexible to Y ";
							$doUpdateStudent		= TRUE;
						} else {
							$updateParams[]			= "flexible|N|s";
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
								$updateParams[]		= "first_class_choice|$myArray[0]|s";
								$updateParams[]		= "first_class_choice_utc|$myArray[1]|s";
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
									$updateParams[]		= "second_class_choice|$myArray[0]|s";
									$updateParams[]		= "second_class_choice_utc|$myArray[1]|s";
									$doUpdateStudent	= TRUE;
									$secondChoice	 	= $myArray[0];
									$actionLogUpdates	.= "set second_class_choice to $myArray[0] ";
								} else {
									$updateParams[]		= "second_class_choice|None|s";
									$updateParams[]		= "second_class_choice_utc|None|s";
									$doUpdateStudent	= TRUE;
									$secondChoice 		= 'None';
								}
							} else {
									$updateParams[]		= "second_class_choice|None|s";
									$updateParams[]		= "second_class_choice_utc|None|s";
									$doUpdateStudent	= TRUE;
									$secondChoice 		= 'None';
							}
							if ($inp_sked3 !== 'None') {
								$myArray			= explode("|",$inp_sked3);
								if (count($myArray) == 2) {
									$updateParams[]		= "third_class_choice|$myArray[0]|s";
									$updateParams[]		= "third_class_choice_utc|$myArray[1]|s";
									$thirdChoice 		= $myArray[0];
									$doUpdateStudent	= TRUE;
									$actionLogUpdates	.= "set third_class_choice to $myArray[0] ";
								} else {
									$updateParams[]		= "third_class_choice|None|s";
									$updateParams[]		= "third_class_choice_utc|None|s";
									$doUpdateStudent	= TRUE;
									$thirdChoice 		= 'None';
								}						
							} else {
									$updateParams[]		= "third_class_choice|None|s";
									$updateParams[]		= "third_class_choice_utc|None|s";
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
								$updateParams[]						= "first_class_choice|$student_first_class_choice|s";
								$updateParams[]						= "first_class_choice_utc|$student_first_class_choice_utc|s";
								$doUpdateStudent					= TRUE;
								$actionLogUpdates					.= "Removed first class choices, ";
								$firstChoice						= "None";
							}
							if ($student_second_class_choice != '') {
								$student_second_class_choice		= '';
								$student_second_class_choice_utc	= '';
								$updateParams[]						= "second_class_choice|$student_second_class_choice|s";
								$updateParams[]						= "second_class_choice_utc|$student_second_class_choice_utc|s";
								$actionLogUpdates					.= "Removed second class choices, ";
								$doUpdateStudent					= TRUE;
								$secondChoice						= "None";
							}
							if ($student_third_class_choice != '') {
								$student_third_class_choice			= '';
								$student_third_class_choice_utc		= '';
								$updateParams[]						= "third_class_choice|$student_third_class_choice|s";
								$updateParams[]						= "third_class_choice_utc|$student_third_class_choice_utc|s";
								$actionLogUpdates					.= "Removed third class choices, ";
								$doUpdateStudent					= TRUE;
								$thirdChoice						= "None";
							}
							if ($student_flexible != 'N') {
								$student_flexible					= "N";
								$updateParams[]						= 'flexible|N|s';
								$actionLogUpdates					.= "set flexible to N, ";
								$doUpdateStudent					= TRUE;
							}
							if ($student_no_catalog != 'N') {
								$student_no_catalog					= 'N';
								$updateParams[]						= 'no_catalog|N|s';
								$actionLogUpdates					.= "set no_catalog to N, ";
								$doUpdateStudent					= TRUE;
							}
							if ($student_waiting_list != 'Y') {	
								$student_waiting_list				= 'Y';
								$updateParams[]						= 'waiting_list|Y|s';
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
								$updateParams[]					= "first_class_choice|$student_first_class_choice|s";
								$updateParams[]					= "first_class_choice_utc|$student_first_class_choice_utc|s";
								$doUpdateStudent				= TRUE;
								$actionLogUpdates				.= "Updated first class choices, ";
								$firstChoice					= $student_first_class_choice;
							}
							if ($student_second_class_choice != 'None') {
								$student_second_class_choice		= 'None';
								$student_second_class_choice_utc	= 'None';
								$updateParams[]						= "second_class_choice|$student_second_class_choice|s";
								$updateParams[]						= "second_class_choice_utc|$student_second_class_choice_utc|s";
								$actionLogUpdates					.= "Set second class choices to None, ";
								$doUpdateStudent						= TRUE;
								$secondChoice						= "None";
							}
							if ($student_third_class_choice != 'None') {
								$student_third_class_choice			= 'None';
								$student_third_class_choice_utc		= 'None';
								$updateParams[]						= "third_class_choice|$student_third_class_choice|s";
								$updateParams[]						= "third_class_choice_utc|$student_third_class_choice_utc|s";
								$actionLogUpdates					.= "Set third class choices to None, ";
								$doUpdateStudent							= TRUE;
								$thirdChoice						= "None";
							}
							if ($student_flexible != 'N') {
								$student_flexible					= "N";
								$updateParams[]						= 'flexible|N|s';
								$actionLogUpdates					.= "Set flexible to N, ";
								$doUpdateStudent					= TRUE;
							}
							if ($student_no_catalog != 'N') {
								$student_no_catalog					= 'N';
								$updateParams[]						= 'no_catalog|N|s';
								$actionLogUpdates					.= "Set no_catalog to N, ";
								$doUpdateStudent					= TRUE;
							}
						}
						if ($student_response != 'Y') {
							$updateParams[]							= 'response|Y|s';
							$myStr									= date('Y-m-d H:i:s');
							$updateParams[]							= "response_date|$myStr|s";
							$actionLogUpdates						.= "set response to Y and set response_date, ";
							$doUpdateStudent						= TRUE;
						}
					}
					if ($student_abandoned == 'Y') {
						$updateParams[]					= 'abandoned|N|s';
						$doUpdateStudent					= TRUE;
						$actionLogUpdates				.= "Set abandoned to N, ";
					}
					if ($inp_verify == 'Y') {
						$updateParams[]					= "response|Y|s";
						$doUpdateStudent				= TRUE;
					} elseif ($student_response == '' && $validEmailPeriod) {
						$updateParams[]					= "response|Y|s";
						$doUpdateStudent				= TRUE;
					}

					if ($doUpdateStudent) {
						$myInt					= strlen($actionLogUpdates) -2;
						if ($doDebug) {
							echo "Making these changes: $actionLogUpdates<br />";
						}
						$actionLogUpdates		= substr($actionLogUpdates,0,$myInt);
						$student_action_log		= "$student_action_log / $actionDate $userName STDREG $actionLogUpdates";
						$updateParams[]			= "action_log|$student_action_log|s";
						$studentUpdateData		= array('tableName'=>$studentTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateParams,
														'inp_format'=>array(''),
														'jobname'=>$jobname,
														'inp_id'=>$student_ID,
														'inp_callsign'=>$student_call_sign,
														'inp_semester'=>$student_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateStudent($studentUpdateData);
						if ($updateResult[0] === FALSE) {
							handleWPDBError($jobname,$doDebug);
							$content		.= "Unable to update content in $studentTableName<br />";
						} else {
							$lastError			= $wpdb->last_error;
							if ($lastError != '') {
								handleWPDBError($jobname,$doDebug);
								$content		.= "Fatal program error. System Admin has been notified";
								if (!$doDebug) {
									return $content;
								}
							}
					

							/// Now check to see if the student 
							/// is also signed up as an advisor. If so, set the advisor survey_score 
							/// to 13 and send an email about it to Roland and Bob
							if ($doDebug) {
								echo "checking to see if the student is also an advisor<br />";
							}
							$sql				= "select advisor_id, 
														  call_sign, 
														  survey_score, 
														  semester, 
														  action_log, 
														  first_name, 
														  last_name, 
														  email, 
														  phone 
													from $advisorTableName 
													where call_sign='$inp_callsign' 
													and semester='$student_semester'";
							$wpw1_cwa_advisor		= $wpdb->get_results($sql);
							if ($wpw1_cwa_advisor === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$lastError			= $wpdb->last_error;
								if ($lastError != '') {
									handleWPDBError($jobname,$doDebug);
									$content		.= "Fatal program error. System Admin has been notified";
									if (!$doDebug) {
										return $content;
									}
								}

								$numARows									= $wpdb->num_rows;
								if ($doDebug) {
									echo "found $numARows rows in $advisorTableName table<br />";
								}
								if ($numARows > 0) {
									foreach ($wpw1_cwa_advisor as $advisorRow) {
										$advisor_ID							= $advisorRow->advisor_id;
										$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
										$advisor_first_name 				= $advisorRow->first_name;
										$advisor_last_name 					= stripslashes($advisorRow->last_name);
										$advisor_email 						= strtolower($advisorRow->email);
										$advisor_phone						= $advisorRow->phone;
										$advisor_semester 					= $advisorRow->semester;
										$advisor_survey_score 				= $advisorRow->survey_score;
										$advisor_action_log 				= $advisorRow->action_log;

										$advisor_last_name 					= no_magic_quotes($advisor_last_name);

										if ($doDebug) {
											echo "student is also an advisor. Updating advisor survey score to 13<br />";
										}
										$advisor_action_log				= "$advisor_action_log / $actionDate $userName STDREG student signed up as advisor in the same semester. Set advisor survey_score to 13 ";
										$advisorUpdateData		= array('tableName'=>$advisorTableName,
																		'inp_method'=>'update',
																		'inp_data'=>array('survey_score'=>13,
																						 'action_log'=>$advisor_action_log),
																		'inp_format'=>array('%d','%s'),
																		'jobname'=>$jobname,
																		'inp_id'=>$advisor_ID,
																		'inp_callsign'=>$advisor_call_sign,
																		'inp_semester'=>$advisor_semester,
																		'inp_who'=>$userName,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
										$updateResult	= updateAdvisor($advisorUpdateData);
										if ($updateResult[0] === FALSE) {
											handleWPDBError($jobname,$doDebug);
											$content		.= "Unable to update content in $advisorTableName<br />";
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
												echo "Successfully updated $advisor_call_sign record at $advisor_ID<br />";
											}
										}
									}
								} else {
									if ($doDebug) {
										echo "student is not also an advisor<br />";
									}
								}
							}
						}
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
							$nocatalogMsg			= "<p><b>NOTE:</b> You will 
														receive an email from CW Academy about 45 days before the start of the $inp_semester semester 
														asking you to review and select your class date and time preferences. You <span style='color:red;'><b>MUST</b></span> respond to 
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
						$content	 		.= "<h3>CWA Student Sign-up Completed</h3>
												$waitListMsg
												<p><p>Your registration update has been saved. <b>Most communications from CW Academy will be by 
												email, so make sure these emails are not marked as spam</b>. In most email programs you do 
												that by adding <u>cwacademy@cwa.cwops.org</u> to your contact list in your email program. More 
												information is available at <a href='https://www.whitelist.guide/' target='_blank'>Email 
												Whitelist Guide</a>.</p>
												$nocatalogMsg
												<button onClick=\"window.print()\">Click to print this<br />page for your records</button>";
					}
					$content			.= "<p>You are signed-up as follows:
											<table style='width:60%;'>
											<tr><td colspan='2'>$student_last_name, $student_first_name ($student_call_sign)</td></tr>
											<tr><td>Email: $student_email</td>
												<td>Phone: $student_ph_code $student_phone</td></tr>
											<tr><td>City: $student_city</td>
												<td>State: $student_state</td></tr>
											<tr><td>Zip Code: $student_zip_code</td>
												<td>Country: $student_country</td></tr>
											<tr><td>Level: $student_level</td>
												<td>Semester: $student_semester</td></tr>";
					if ($result_option == 'option') {
						$content	.= "<tr><td colspan='2'>Class Preferences<br />";
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
						$content		.= "<tr><td>First Class Choice</td><td>$firstChoice</td></tr>
											<tr><td>Second Class Choice</td><td>$secondChoice</td></tr>
											<tr><td>Third Class Choice</td><td>$thirdChoice</td></tr>";
					}
					$box1		= "Whatsapp<br />----";		
					$box2		= "Telegram<br />----";		
					$box3		= "Signal<br />----";		
					$box4		= "Messenger<br />----";		
					if ($student_whatsapp != '') {
						$box1	= "Whatsapp<<br />$student_whatsapp";
					}
					if ($student_telegram != '') {
						$box2	= "Telegram<br />$student_telegram";
					}
					if ($student_signal != '') {
						$box3	= "Signal<br />$student_signal";
					}
					if ($student_messenger != '') {
						$box4	= "Messenger<br />$student_messenger";
					}
					$content	.= "<tr><td colspan='2'>
									<table>
									<tr><td style='text-align:center;'>$box1</td>
										<td style='text-align:center;'>$box2</td>
										<td style='text-align:center;'>$box3</td>
										<td style='text-align:center;'>$box4</td></tr></table></td></tr>";
					if ($student_youth == 'Yes') {
						$content	.= "<tr><td style='text-align:center;'>Youth<br />$student_youth</td>
											<td style='text-align:center;'>Age<br />$student_age</td></tr>
										<tr><td>Parent / Guardian<br />$student_student_parent</td>
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
			}
		}


////////// pass 90


	} elseif ("90" == $strPass) {			/// enter validation information to update sign up record
		if ($doDebug) {
			echo "<br />Arrived at pass 90 with $userName<br />";
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
			$sql				= "select * from $studentTableName 
									where call_sign='$inp_callsign' 
									 and (semester = '$currentSemester' 
										 or semester = '$nextSemester' 
										 or semester = '$semesterTwo' 
										 or semester = '$semesterThree' 
										 or Semester = '$semesterFour') 
									order by date_created DESC 
									limit 1";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$lastError			= $wpdb->last_error;
				if ($lastError != '') {
					handleWPDBError($jobname,$doDebug);
					$content		.= "Fatal program error. System Admin has been notified";
					if (!$doDebug) {
						return $content;
					}
				}

				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					$noRecord									= FALSE;
					$foundARecord								= TRUE;
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_first_name						= $studentRow->first_name;
						$student_last_name						= stripslashes($studentRow->last_name);
						$student_email  						= strtolower(strtolower($studentRow->email));
						$student_phone  						= $studentRow->phone;
						$student_ph_code						= $studentRow->ph_code;
						$student_city  							= $studentRow->city;
						$student_state  						= $studentRow->state;
						$student_zip_code  						= $studentRow->zip_code;
						$student_country  						= $studentRow->country;
						$student_country_code					= $studentRow->country_code;
						$student_time_zone  					= $studentRow->time_zone;
						$student_timezone_id					= $studentRow->timezone_id;
						$student_timezone_offset				= $studentRow->timezone_offset;
						$student_whatsapp						= $studentRow->whatsapp_app;
						$student_signal							= $studentRow->signal_app;
						$student_telegram						= $studentRow->telegram_app;
						$student_messenger						= $studentRow->messenger_app;					
						$student_wpm 	 						= $studentRow->wpm;
						$student_youth  						= $studentRow->youth;
						$student_age  							= $studentRow->age;
						$student_student_parent 				= $studentRow->student_parent;
						$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
						$student_level  						= $studentRow->level;
						$student_waiting_list 					= $studentRow->waiting_list;
						$student_request_date  					= $studentRow->request_date;
						$student_semester						= $studentRow->semester;
						$student_notes  						= $studentRow->notes;
						$student_welcome_date  					= $studentRow->welcome_date;
						$student_email_sent_date  				= $studentRow->email_sent_date;
						$student_email_number  					= $studentRow->email_number;
						$student_response  						= strtoupper($studentRow->response);
						$student_response_date  				= $studentRow->response_date;
						$student_abandoned  					= $studentRow->abandoned;
						$student_student_status  				= strtoupper($studentRow->student_status);
						$student_action_log  					= $studentRow->action_log;
						$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
						$student_selected_date  				= $studentRow->selected_date;
						$student_no_catalog			 			= $studentRow->no_catalog;
						$student_hold_override  				= $studentRow->hold_override;
						$student_messaging  					= $studentRow->messaging;
						$student_assigned_advisor  				= $studentRow->assigned_advisor;
						$student_advisor_select_date  			= $studentRow->advisor_select_date;
						$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
						$student_hold_reason_code  				= $studentRow->hold_reason_code;
						$student_class_priority  				= $studentRow->class_priority;
						$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
						$student_promotable  					= $studentRow->promotable;
						$student_excluded_advisor  				= $studentRow->excluded_advisor;
						$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
						$student_available_class_days  			= $studentRow->available_class_days;
						$student_intervention_required  		= $studentRow->intervention_required;
						$student_copy_control  					= $studentRow->copy_control;
						$student_first_class_choice  			= $studentRow->first_class_choice;
						$student_second_class_choice  			= $studentRow->second_class_choice;
						$student_third_class_choice  			= $studentRow->third_class_choice;
						$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
						$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
						$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
						$student_catalog_options				= $studentRow->catalog_options;
						$student_flexible						= $studentRow->flexible;
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;
					
						$student_last_name 						= no_magic_quotes($student_last_name);

						$content			.= "<p>You are signed-up as follows:
												<table style='width:60%;'>
												<tr><td>Call Sign</td><td>$student_call_sign</td></tr>
												<tr><td>First Name</td><td>$student_first_name</td></tr>
												<tr><td>Last Name</td><td>$student_last_name</td></tr>
												<tr><td>Email</td><td>$student_email</td></tr>
												<tr><td>Phone</td><td>+$student_ph_code $student_phone</td></tr>
												<tr><td>Text Messaging</td><td>$student_messaging</td></tr>
												<tr><td>City</td><td>$student_city</td></tr>
												<tr><td>State</td><td>$student_state</td></tr>
												<tr><td>Zip Code</td><td>$student_zip_code</td></tr>
												<tr><td>Country</td><td>$student_country</td></tr>";
																if ($student_whatsapp != '') {
																	$content	.= "<tr><td>Whatsapp</td><td>$student_whatsapp</td></tr>";
																}
																if ($student_telegram != '') {
																	$content	.= "<tr><td>Telegram</td><td>$student_telegram</td></tr>";
																}
																if ($student_signal != '') {
																	$content	.= "<tr><td>Signal</td><td>$student_signal</td></tr>";
																}
																if ($student_messenger != '') {
																	$content	.= "<tr><td>Facebook Messenger</td><td>$student_messenger</td></tr>";
																}
																$content		.= "
												<tr><td>Level</td><td>$student_level</td></tr>
												<tr><td>Semester</td><td>$student_semester</td></tr>";
						if ($student_catalog_options != '') {
							$content	.= "<tr><td>Class Preferences</td>";
							if ($student_flexible == 'Y') {
								$content	.= "<td>My time is flexible</td></tr>";
							} else {
								$myArray	= explode(",",$student_catalog_options);
								$content	.= "<td>";
								foreach($myArray as $thisData) {
									$myStr		= $catalogOptions[$thisData];
									$content	.= "$myStr<br />";
								}
								$content		.= "</td></tr>";
							}
						} else {
							$content		.= "<tr><td>First Class Choice</td><td>$student_first_class_choice</td></tr>
												<tr><td>Second Class Choice</td><td>$student_second_class_choice</td></tr>
												<tr><td>Third Class Choice</td><td>$student_third_class_choice</td></tr>";
						}
						if ($student_youth == 'Yes') {
							$content	.= "<tr><td>Youth</td><td>$student_youth</td></tr>
											<tr><td>Age</td><td>$student_age</td></tr>
											<tr><td>Parent / Guardian</td><td>$student_student_parent</td></tr>
											<tr><td>Parent / Guardian Email</td><td>$student_student_parent_email</td></tr>";
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
											<input type='hidden' name='inp_email' value='$student_email'>
											<input type='hidden' name='inp_phone' value='$student_phone'>
											<table style='border-collapse:collapse;'>
											$testModeOption<br />
											<tr><td colspan='2'>'<input class='formInputButton' type='submit' value='Next' /></td></tr></table>
											</form>
											<p>If you are having difficulty gaining access to your registration information, please contact the 
											appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class Resolution</a>.</p>";
					}
				} else {
					$content		.= "<p>No signup record found for $inp_callsign. Have you signed up?
										If you are having difficulty gaining access to your registration information, please contact the 
										appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class Resolution</a>.</p>";
				}
			}
		}
		

///////////////// pass 100

	} elseif ("100" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass $strPass<br />";
		}
		$userName			= strtoupper($userName);
		$doProceed			= TRUE;
		// check to see there is a signup record
		$sql				= "select * from $studentTableName 
								where call_sign='$userName' 
								 and (semester = '$nextSemester' 
									 or semester = '$semesterTwo' 
									 or semester = '$semesterThree' 
									 or Semester = '$semesterFour') 
								order by date_created DESC 
								limit 1";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				$noRecord									= FALSE;
				$foundARecord								= TRUE;
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_phone  						= $studentRow->phone;
					$student_ph_code						= $studentRow->ph_code;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_country_code					= $studentRow->country_code;
					$student_time_zone  					= $studentRow->time_zone;
					$student_timezone_id					= $studentRow->timezone_id;
					$student_timezone_offset				= $studentRow->timezone_offset;
					$student_whatsapp						= $studentRow->whatsapp_app;
					$student_signal							= $studentRow->signal_app;
					$student_telegram						= $studentRow->telegram_app;
					$student_messenger						= $studentRow->messenger_app;					
					$student_wpm 	 						= $studentRow->wpm;
					$student_youth  						= $studentRow->youth;
					$student_age  							= $studentRow->age;
					$student_student_parent 				= $studentRow->student_parent;
					$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->level;
					$student_waiting_list 					= $studentRow->waiting_list;
					$student_request_date  					= $studentRow->request_date;
					$student_semester						= $studentRow->semester;
					$student_notes  						= $studentRow->notes;
					$student_welcome_date  					= $studentRow->welcome_date;
					$student_email_sent_date  				= $studentRow->email_sent_date;
					$student_email_number  					= $studentRow->email_number;
					$student_response  						= strtoupper($studentRow->response);
					$student_response_date  				= $studentRow->response_date;
					$student_abandoned  					= $studentRow->abandoned;
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog			 			= $studentRow->no_catalog;
					$student_hold_override  				= $studentRow->hold_override;
					$student_messaging  					= $studentRow->messaging;
					$student_assigned_advisor  				= $studentRow->assigned_advisor;
					$student_advisor_select_date  			= $studentRow->advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
					$student_hold_reason_code  				= $studentRow->hold_reason_code;
					$student_class_priority  				= $studentRow->class_priority;
					$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
					$student_promotable  					= $studentRow->promotable;
					$student_excluded_advisor  				= $studentRow->excluded_advisor;
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->available_class_days;
					$student_intervention_required  		= $studentRow->intervention_required;
					$student_copy_control  					= $studentRow->copy_control;
					$student_first_class_choice  			= $studentRow->first_class_choice;
					$student_second_class_choice  			= $studentRow->second_class_choice;
					$student_third_class_choice  			= $studentRow->third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
					$student_catalog_options				= $studentRow->catalog_options;
					$student_flexible						= $studentRow->flexible;
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$student_last_name 						= no_magic_quotes($student_last_name);

					$content			.= "<p>It seems you alreadh have signed up for a $student_level 
											Level class in the $student_semester semester. If you want 
											to modify this record, please click 
											<a href='$siteURL/cwa-student-registration/'>Student Sign Up</a> 
											and select Option 3</p>";
					$doProceed			= FALSE;
				}
			}
		}
		if ($doProceed) {
			$semesterSelection	= '';
			$content			.= "<h3>$jobname</h3>
									<h4>Signup Information</h4>";
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

		
		
			$content				.= "<p>CW Academy offers four levels of classes based upon operator CW proficiency: 
										Beginner, Fundamental, Intermediate or Advanced. 
										<ul><li>The Beginner level, as the name implies, is for people who have little or no 
												Morse code knowledge. Choose the Beginner class if that is your situation</li>
											<li>The Fundamental class is for people who have learned the Morse code characters and are capable of 
												sending and receiving CW at about 6 words per minute and wish to increase that capability 
												to about 12 words per minute</li>
											<li>The Intermediate class is for people who are capable of sending and receiving 
												Morse code at a minimum of 10 words per minute and wish to increase that capability to about 
												20 words per minute</li>
											<li>The Advanced class is for people who are capable of sending and receiving Morse 
												code at a minimum of 20 words per minute and wish to increase that capability to about 30 
												words per minute</li></ul></p>
										<p><b>Classes at the CW Academy are free of charge.</b> The advisors and staff are a volunteers who 
										have a passion for CW. Classes are eight weeks long and held in January - February, May - June, 
										and September - October. Classes meet twice a week for about an hour. Between classes the 
										students have daily homework assignments which will take about an hour to complete.</p>
										<p><b>Minimum essentials:</b>
										<ul><li>High speed broadband internet access
											<li>Computing device (desktop, laptop)
											<li>Webcam (camera, microphone) either built-in or USB add-on
											<li>Key paddle (single lever or dual lever. Straight keys or Bugs are not allowed)
											<li>Keyer with sidetone or radio with built-in keyer and sidetone
											<li>Dedication to 60 minutes of daily practice
											<li>Comfortable understanding and speaking the English language unless previous arrangements have been made
										</ul></p>
										<p>Students are limited to one registration at a time in the system. When you have 
										completed a class you may register for a future class.</p>
										<p>To get started, please enter your call sign (or your last name if you do not have a call sign) 
										and select the class level you are considering. Note: <em>call sign may not include a space, hyphen, 
										or a slash</em>.</p>
										<form method='post' action='$theURL' 
										name='pass01form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='101'>
										<input type='hidden' name='firsttime' value=''>
										<input type='hidden' name='thisOption' value='$thisOption'>
										<input type='hidden' name='token' value='$token'>
										<input type='hidden' name='timezone' value='' >
										<table style='border-collapse:collapse;'>
										<tr><td style='width:150px;vertical-align:top;'><b>Call Sign</b><br />(if you do not have a call sign, enter your last name)</td>
											<td><input type='text' class='formInputText' id='chk_callsign' name='inp_callsign' size='15' maxlength='30' value='$userName' required></td></tr>
										<tr><td style='vertical-align:top;'><b>Class Level</b></td>
											<td><input type='radio' class='formInputText' name='inp_level' id='chk_level' value='Beginner' required > Beginner<br />
												<input type='radio' class='formInputText' name='inp_level' id='chk_level' value='Fundamental' required > Fundamental<br />
												<input type='radio' class='formInputText' name='inp_level' id='chk_level' value='Intermediate' required > Intermediate<br />
												<input type='radio' class='formInputText' name='inp_level' id='chk_level' value='Advanced' required > Advanced</td></tr>
										<tr><td style='vertical-align:top;'><b>Email Address</b></td>
											<td><input type='text' class='formInputText' name='inp_email' id='chk_email' size='50' maxlength='100' value='$userEmail' required ><br />
												<b>Important!</b> Make certain that the email address you enter is spelled correctly and is complete. All communication from 
												CW Academy is by email.</td></tr>
										$semesterSelection
										$testModeOption<br />
										<tr><td colspan='2'>'<input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Next' /></td></tr></table>
										</form>";
		}

		
/////////////////////////	PASS 101

	} elseif ("101" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 101 with inp_callsign of $inp_callsign and inp_level of $inp_level<br />";
		}
		$enstr					= '';
		$doProceed				= TRUE;
		$allowSignup			= FALSE;
		$token					= mt_rand();

		// get any future student records from the database
		$sql			= "select * from $studentTableName 
							where call_sign = '$inp_callsign' 
							and (semester = '$nextSemester' 
							or semester = '$semesterTwo' 
							or semester = '$semesterThree' 
							or semester = '$semesterFour')";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content		.= "Unable to obtain content from $studentTableName<br />";
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}

			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_phone  						= $studentRow->phone;
					$student_ph_code						= $studentRow->ph_code;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_country_code					= $studentRow->country_code;
					$student_time_zone  					= $studentRow->time_zone;
					$student_timezone_id					= $studentRow->timezone_id;
					$student_timezone_offset				= $studentRow->timezone_offset;
					$student_whatsapp						= $studentRow->whatsapp_app;
					$student_signal							= $studentRow->signal_app;
					$student_telegram						= $studentRow->telegram_app;
					$student_messenger						= $studentRow->messenger_app;					
					$student_wpm 	 						= $studentRow->wpm;
					$student_youth  						= $studentRow->youth;
					$student_age  							= $studentRow->age;
					$student_student_parent 				= $studentRow->student_parent;
					$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->level;
					$student_waiting_list 					= $studentRow->waiting_list;
					$student_request_date  					= $studentRow->request_date;
					$student_semester						= $studentRow->semester;
					$student_notes  						= $studentRow->notes;
					$student_welcome_date  					= $studentRow->welcome_date;
					$student_email_sent_date  				= $studentRow->email_sent_date;
					$student_email_number  					= $studentRow->email_number;
					$student_response  						= strtoupper($studentRow->response);
					$student_response_date  				= $studentRow->response_date;
					$student_abandoned  					= $studentRow->abandoned;
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog			 			= $studentRow->no_catalog;
					$student_hold_override  				= $studentRow->hold_override;
					$student_messaging  					= $studentRow->messaging;
					$student_assigned_advisor  				= $studentRow->assigned_advisor;
					$student_advisor_select_date  			= $studentRow->advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
					$student_hold_reason_code  				= $studentRow->hold_reason_code;
					$student_class_priority  				= $studentRow->class_priority;
					$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
					$student_promotable  					= $studentRow->promotable;
					$student_excluded_advisor  				= $studentRow->excluded_advisor;
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->available_class_days;
					$student_intervention_required  		= $studentRow->intervention_required;
					$student_copy_control  					= $studentRow->copy_control;
					$student_first_class_choice  			= $studentRow->first_class_choice;
					$student_second_class_choice  			= $studentRow->second_class_choice;
					$student_third_class_choice  			= $studentRow->third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
					$student_catalog_options				= $studentRow->catalog_options;
					$student_flexible						= $studentRow->flexible;
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$student_last_name 						= no_magic_quotes($student_last_name);
					$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
							
					// Found a record. If the response is R, then the student has to reach out to class resolution
					
					if ($student_response == 'R') {
						$content		.= "<h3>Student Sign-up for $inp_callsign</h3>
											<p>You previously enrolled for the $student_semester semester and have declined the opportunity to 
											be assigned to a class. If you wish to get signed up, please contact the appropriate person 
											at <a href='https://cwops.org/cwa-class-resolution/'>CW Academy Class Resolution</a> for assistance.</p>";
						$doProceed		= FALSE;
						$allowSignup	= FALSE;
						if ($doDebug) {
							echo "Currently enrolled, response is R, doProceed and allowSignup set to FALSE<br />";
						}
					} else {
						$content	.= "<h3>Student Sign-up for $inp_callsign</h3>
										It seems that you have an active registration record for $student_semester  
										semester. If you wish to change or delete that registration, 
										please go to <a href='$theURL'>Student Signup</a> and 
										select option 3.";
						$doProceed		= FALSE;
						$allowSignup	= FALSE;
					}
				}
			}
		}
		if ($doProceed) {
			$lastLevel		= "";
			$lastPromotable	= "";
			$lastSemester	= "";
			// get the last class 
			$sql		= "select * from $studentTableName 
							where call_sign = '$inp_callsign' 
							order by date_created DESC 
							limit 1";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Unable to obtain content from $studentTableName<br />";
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_first_name						= $studentRow->first_name;
						$student_last_name						= stripslashes($studentRow->last_name);
						$student_email  						= strtolower(strtolower($studentRow->email));
						$student_phone  						= $studentRow->phone;
						$student_ph_code						= $studentRow->ph_code;
						$student_city  							= $studentRow->city;
						$student_state  						= $studentRow->state;
						$student_zip_code  						= $studentRow->zip_code;
						$student_country  						= $studentRow->country;
						$student_country_code					= $studentRow->country_code;
						$student_time_zone  					= $studentRow->time_zone;
						$student_timezone_id					= $studentRow->timezone_id;
						$student_timezone_offset				= $studentRow->timezone_offset;
						$student_whatsapp						= $studentRow->whatsapp_app;
						$student_signal							= $studentRow->signal_app;
						$student_telegram						= $studentRow->telegram_app;
						$student_messenger						= $studentRow->messenger_app;					
						$student_wpm 	 						= $studentRow->wpm;
						$student_youth  						= $studentRow->youth;
						$student_age  							= $studentRow->age;
						$student_student_parent 				= $studentRow->student_parent;
						$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
						$student_level  						= $studentRow->level;
						$student_waiting_list 					= $studentRow->waiting_list;
						$student_request_date  					= $studentRow->request_date;
						$student_semester						= $studentRow->semester;
						$student_notes  						= $studentRow->notes;
						$student_welcome_date  					= $studentRow->welcome_date;
						$student_email_sent_date  				= $studentRow->email_sent_date;
						$student_email_number  					= $studentRow->email_number;
						$student_response  						= strtoupper($studentRow->response);
						$student_response_date  				= $studentRow->response_date;
						$student_abandoned  					= $studentRow->abandoned;
						$student_student_status  				= strtoupper($studentRow->student_status);
						$student_action_log  					= $studentRow->action_log;
						$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
						$student_selected_date  				= $studentRow->selected_date;
						$student_no_catalog			 			= $studentRow->no_catalog;
						$student_hold_override  				= $studentRow->hold_override;
						$student_messaging  					= $studentRow->messaging;
						$student_assigned_advisor  				= $studentRow->assigned_advisor;
						$student_advisor_select_date  			= $studentRow->advisor_select_date;
						$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
						$student_hold_reason_code  				= $studentRow->hold_reason_code;
						$student_class_priority  				= $studentRow->class_priority;
						$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
						$student_promotable  					= $studentRow->promotable;
						$student_excluded_advisor  				= $studentRow->excluded_advisor;
						$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
						$student_available_class_days  			= $studentRow->available_class_days;
						$student_intervention_required  		= $studentRow->intervention_required;
						$student_copy_control  					= $studentRow->copy_control;
						$student_first_class_choice  			= $studentRow->first_class_choice;
						$student_second_class_choice  			= $studentRow->second_class_choice;
						$student_third_class_choice  			= $studentRow->third_class_choice;
						$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
						$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
						$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
						$student_catalog_options				= $studentRow->catalog_options;
						$student_flexible						= $studentRow->flexible;
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;

						$student_last_name 						= no_magic_quotes($student_last_name);
						$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
						
						if ($doDebug) {
							echo "found Level: $student_level, Promotable: $student_promotable<br />
									Response: $student_response, Status: $student_student_status<br />";
						}
												
						if ($student_response != 'R') {
							if ($student_student_status == 'Y' || $student_student_status == 'S') {
								if ($student_promotable == '') {
									$content	.= "<h3>Student Sign-up for $inp_callsign</h3>
													<p>If you are trying to register for the $nextSemester semester, your advisor needs to 
													complete your end-of-semester evaluation before you can register.</p>";
									$doProceed		= FALSE;
									$allowSignup	= FALSE;
									if ($doDebug) {
										echo "doProceed and allowSignup set to FALSE as end-of-semester evaluation not yet done<br />";
									}
								} else {
									$lastLevel		= $student_level;
									$lastPromotable	= $student_promotable;
									$lastSemester	= $student_semester;
									if ($doDebug) {
										echo "Set lastLevel to $lastLevel, lastPromotable to $lastPromotable, and lastSemester to $lastSemester<br />";
									}
								}
							} 
						}
					}
				} else {
					if ($doDebug) {
						echo "numSRows of $numSRows was not greater than zero<br />";
					}
				}
			}
		}
		if ($doProceed) {
			/// student doing a signup. Setup to carry info forward
			$allowSignup				= TRUE;
			$stringToPass 				= "inp_callsign=$inp_callsign&inp_phone=$inp_phone&inp_ph_code=$inp_ph_code&inp_email=$inp_email&inp_semester=$inp_semester&inp_mode=$inp_mode&inp_verbose=$inp_verbose&thisOption=$thisOption&firsttime=$firsttime&timezone=$timezone&allowSignup=$allowSignup&inp_level=$inp_level";
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
			if ($needsAssessment) {					// see if there is an assessment in the last 60 days
				if ($doDebug) {
					echo "needsAssessment is TRUE. Looking for previous assessments<br />";
				}
				$last60Days				= strtotime("-60 days");
				$last60Date				= date('Y-m-d 00:00:00',$last60Days);
				
				$sql					= "select score from wpw1_cwa_new_assessment_data 
											where callsign = '$inp_callsign' 
												and level = '$inp_level' 
												and date_written > '$last60Date'";
				$thisScore				= $wpdb->get_results($sql);
				if ($thisScore === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						if (!$doDebug) {
							return $content;
						}
					}

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
												<input type='hidden' name='inp_phone' value='$inp_phone'>
												<input type='hidden' name='inp_ph_code' value='$inp_ph_code'>
												<input type='hidden' name='inp_email' value='$inp_email'>
												<input type='hidden' name='inp_semester' value='$inp_semester'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<input type='hidden' name='inp_verbose' value='$inp_verbose'>
												<input type='hidden' name='thisOption' value='$thisOption'>
												<input type='hidden' name='firsttime' value='$firsttime'>
												<input type='hidden' name='timezone' value='$timezone'>
												<input type='hidden' name='allowSignup' value='$allowSignup'>
												<input type='hidden' name='inp_level' value='$inp_level'>
												<input type='submit' class='formInputButton' name='proficiency' value='Continue to Proficiency Assessment'>
												</form></td>
											<td><form method='post' action='$theURL' 
												name='pass101bform' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='2'>
												<input type='hidden' name='inp_callsign' value='$inp_callsign'>
												<input type='hidden' name='inp_phone' value='$inp_phone'>
												<input type='hidden' name='inp_ph_code' value='$inp_ph_code'>
												<input type='hidden' name='inp_email' value='$inp_email'>
												<input type='hidden' name='inp_semester' value='$inp_semester'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<input type='hidden' name='inp_verbose' value='$inp_verbose'>
												<input type='hidden' name='thisOption' value='$thisOption'>
												<input type='hidden' name='firsttime' value='$firsttime'>
												<input type='hidden' name='timezone' value='$timezone'>
												<input type='hidden' name='allowSignup' value='$allowSignup'>
												<input type='hidden' name='inp_level' value='$inp_level'>
												<input type='submit' class='formInputButton' name='noproficiency' value='Skip Proficiency Assessment'>
												</form></td></tr></table>";
			} else {
				$strPass			= "101a";
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
				$stringToPass 	= "inp_callsign=$inp_callsign&inp_phone=$inp_phone&inp_ph_code=$inp_ph_code&inp_email=$inp_email&inp_semester=$inp_semester&inp_mode=$inp_mode&inp_verbose=$inp_verbose&thisOption=$thisOption&firsttime=$firsttime&timezone=$timezone&allowSignup=$allowSignup&inp_level=$inp_level";
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
									<h4>Morse Code Proficiency Self-assessment</h4>
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
						$stringToPass 	= "inp_callsign=$inp_callsign&inp_phone=$inp_phone&inp_ph_code=$inp_ph_code&inp_email=$inp_email&inp_semester=$inp_semester&inp_mode=$inp_mode&inp_verbose=$inp_verbose&thisOption=$thisOption&firsttime=$firsttime&timezone=$timezone&allowSignup=$allowSignup&inp_level=$inp_level";
						$enstr			= base64_encode($stringToPass);
						$content		.= "<h3>$jobname</h3>
											<p><table style='border:4px solid green;width:auto;'><tr><td>You have recently successfly met the Morse code proficiency requirements 
											to take the CW Academy $inp_level level class.<br /><br />
											Click <a href='$theURL?strpass=2&enstr=$enstr'>HERE</a> to continue the sign-up process.</p></td></tr></table>";			
					}
				}
			}			
		} else {
			if ($doDebug) {
				echo "at end of pass 101 ... doProceed was FALSE which shouldn't be able to happen<br />";
			}
		}

////////// Pass 104

	} elseif ("104" == $strPass) {
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
						  inp_phone: $inp_phone<br />
						  inp_ph_code: $inp_ph_code<br />
						  inp_email: $inp_email<br />
						  inp_semester: $inp_semester<br />
						  inp_mode: $inp_mode<br />
						  inp_verbose: $inp_verbose<br />
						  thisOption: $thisOption<br />
						  firsttime: $firsttime<br />
						  timezone: $timezone<br />
						  allowSignup: $allowSignup<br />
						  inp_level: $inp_level<br />";
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
			$sql					= "select * from $newAssessmentTableName 
										where callsign = '$inp_callsign' 
										and token = '$token'";
			$assessmentResult		= $wpdb->get_results($sql);
			if ($assessmentResult === FALSE) {
				handleWPDBError("$jobname pass 104",$doDebug);
			} else {
				$lastError			= $wpdb->last_error;
				if ($lastError != '') {
					handleWPDBError($jobname,$doDebug);
					$content		.= "Fatal program error. System Admin has been notified";
					if (!$doDebug) {
						return $content;
					}
				}

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
								$bestResultBeginner 	= $assessment_score;
							}
						}
						if ($assessment_level == 'Fundamental') {
							$didFundamental		= TRUE;
							if ($assessment_score > $bestResultFundamental) {
								$bestResultFundamental 	= $assessment_score;
							}
						}
						if ($assessment_level == 'Intermediate') {
							$didIntermediate	= TRUE;
							if ($assessment_score > $bestResultIntermediate) {
								$bestResultIntermediate 	= $assessment_score;
							}
						}
						if ($assessment_level == 'Advanced') {
							$didAdvanced		= TRUE;
							if ($assessment_score > $bestResultAdvanced) {
								$bestResultAdvanced 	= $assessment_score;
							}
						}
			 		}
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
		}
		
		$stringToPass 	= "inp_callsign=$inp_callsign&inp_phone=$inp_phone&inp_ph_code=$inp_ph_code&inp_email=$inp_email&inp_semester=$inp_semester&inp_mode=$inp_mode&inp_verbose=$inp_verbose&thisOption=$thisOption&firsttime=$firsttime&timezone=$timezone&allowSignup=$allowSignup";
		$enstr			= base64_encode($stringToPass);


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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('student_registration_v11', 'student_registration_v11_func');
