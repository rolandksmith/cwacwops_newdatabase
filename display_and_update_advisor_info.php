function display_and_update_advisor_info_func() {

/*
	Function to display and update Advisor information
 
*/

	global $wpdb, $doDebug, $debugLog;
	
	$doDebug 						= FALSE;
	$testMode						= FALSE;

	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

	$siteURL			= $initializationArray['siteurl'];
	$languageArray		= $initializationArray['languageArray'];
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

// initial values	
	$request_type					= "";
	$request_info					= "";
	$strPass						= "1";
	$theURL							= "$siteURL/cwa-display-and-update-advisor-signup-info/";
	$fieldTest						= array('action_log','post_status','post_title','control_code');
	$actionDate						= date('dMy H:i');
	$logDate						= date('Y-m-d H:i');
	$userName						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$inp_depth						= "one";
	$inp_verbose					= 'N';
	$inp_mode						= 'Production';
	$jobname						= "Display and Update Advisor Signup Info";
	$request_table					= '';
	$updateMaster					= "$siteURL/cwa-display-and-update-user-master-information/";
	$advisorclass_id					= '';
	$studentUpdateURL				= "$siteURL/cwa-display-and-update-student-signup/";
	
    $inp_advisor_first_name = '';
    $inp_advisor_last_name = '';
    $inp_advisor_email = '';
    $inp_advisor_phone = '';
    $inp_advisor_city = '';
    $inp_advisor_state = '';
    $inp_advisor_zip_code = '';
    $inp_advisor_country_code = '';
    $inp_advisor_whatsapp = '';
    $inp_advisor_telegram = '';
    $inp_advisor_signal = '';
    $inp_advisor_messenger = '';
    $inp_advisor_timezone_id = '';
    $inp_advisor_languages = '';
    $inp_advisor_survey_score = '';
    $inp_advisor_is_admin = '';
    $inp_advisor_role = '';
    $inp_advisor_master_date_created = '';
    $inp_advisor_master_date_updated = '';
    $inp_advisor_master_action_log = '';
    $inp_advisor_semester = '';
    $inp_advisor_welcome_email_date = '';
    $inp_advisor_verify_email_date = '';
    $inp_advisor_verify_email_number = '';
    $inp_advisor_verify_response = '';
    $inp_advisor_class_verified = '';
    $inp_advisor_control_code = '';
    $inp_advisor_date_created = '';
    $inp_advisor_date_updated = '';
    $inp_advisor_replacement_status = '';
    $inp_advisor_action_log = '';
    $inp_advisorclass_sequence = '';
    $inp_advisorclass_semester = '';
    $inp_advisorclass_timezone_offset = '';
    $inp_advisorclass_level = '';
    $inp_advisorclass_class_size = '';
    $inp_advisorclass_class_schedule_days = '';
    $inp_advisorclass_class_schedule_times = '';
    $inp_advisorclass_class_schedule_days_utc = '';
    $inp_advisorclass_class_schedule_times_utc = '';
    $inp_advisorclass_class_incomplete = '';
    $inp_advisorclass_date_created = '';
    $inp_advisorclass_date_updated = '';
    $inp_advisorclass_student01 = '';
    $inp_advisorclass_student02 = '';
    $inp_advisorclass_student03 = '';
    $inp_advisorclass_student04 = '';
    $inp_advisorclass_student05 = '';
    $inp_advisorclass_student06 = '';
    $inp_advisorclass_student07 = '';
    $inp_advisorclass_student08 = '';
    $inp_advisorclass_student09 = '';
    $inp_advisorclass_student10 = '';
    $inp_advisorclass_student11 = '';
    $inp_advisorclass_student12 = '';
    $inp_advisorclass_student13 = '';
    $inp_advisorclass_student14 = '';
    $inp_advisorclass_student15 = '';
    $inp_advisorclass_student16 = '';
    $inp_advisorclass_student17 = '';
    $inp_advisorclass_student18 = '';
    $inp_advisorclass_student19 = '';
    $inp_advisorclass_student20 = '';
    $inp_advisorclass_student21 = '';
    $inp_advisorclass_student22 = '';
    $inp_advisorclass_student23 = '';
    $inp_advisorclass_student24 = '';
    $inp_advisorclass_student25 = '';
    $inp_advisorclass_student26 = '';
    $inp_advisorclass_student27 = '';
    $inp_advisorclass_student28 = '';
    $inp_advisorclass_student29 = '';
    $inp_advisorclass_student30 = '';
    $inp_advisorclass_number_students = '';
    $inp_advisorclass_class_evaluation_complete = '';
    $inp_advisorclass_class_comments = '';
    $inp_advisorclass_copy_control = '';
    $inp_advisorclass_action_log = '';
    $inp_advisorclass_language = '';
    
    function debugReport($message) {
    
    global $wpdb, $doDebug, $debugLog;
    
    	$thisTime = date('Y-m-d H:i:s');
    	$debugLog .= "$message ($thisTime)<br >";
    	if ($doDebug) {
    		echo "$message <br />";
    	}
    }
    
		debugReport("Initialization Array:<pre>");
		$myStr = print_r($initializationArray, TRUE);
		debugReport("$myStr</pre>");

    
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if (!is_array($str_value)) {
				debugReport("Key: $str_key | Value: $str_value");
			} else {
				debugReport("Key: $str_key (array)");
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_info") {
				$request_info	 = $str_value;
				$request_info		 = filter_var($request_info,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "doDebug") {
				$doDebug		 = $str_value;
				$doDebug		 = filter_var($doDebug,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "testMode") {
				$doDebug		 = $str_value;
				$doDebug		 = filter_var($doDebug,FILTER_UNSAFE_RAW);
				if ($testMode) {
					$inp_mode	= 'TESTMODE';
				}
			}
			if ($str_key 		== "request_type") {
				$request_type	 = $str_value;
				$request_type		 = filter_var($request_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_verbose") {
				$inp_verbose = $str_value;
				$inp_verbose = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key  == "inp_mode") {
				$inp_mode = $str_value;
				$inp_mode = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key  == "inp_depth") {
				$inp_depth = $str_value;
				$inp_depth = filter_var($inp_depth,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_list") {
				$inp_list = $str_value;
				$inp_list = filter_var($inp_list,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "trueRecordCount") {
				$trueRecordCount = $str_value;
				$trueRecordCount = filter_var($trueRecordCount,FILTER_UNSAFE_RAW);
			}

			if ($str_key == 'inp_advisor_call_sign') {
				$inp_advisor_call_sign = $str_value;
				$inp_advisor_call_sign = filter_var($inp_advisor_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_semester') {
				$inp_advisor_semester = $str_value;
				$inp_advisor_semester = filter_var($inp_advisor_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_welcome_email_date') {
				$inp_advisor_welcome_email_date = $str_value;
				$inp_advisor_welcome_email_date = filter_var($inp_advisor_welcome_email_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_verify_email_date') {
				$inp_advisor_verify_email_date = $str_value;
				$inp_advisor_verify_email_date = filter_var($inp_advisor_verify_email_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_verify_email_number') {
				$inp_advisor_verify_email_number = $str_value;
				$inp_advisor_verify_email_number = filter_var($inp_advisor_verify_email_number,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_verify_response') {
				$inp_advisor_verify_response = $str_value;
				$inp_advisor_verify_response = filter_var($inp_advisor_verify_response,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_class_verified') {
				$inp_advisor_class_verified = $str_value;
				$inp_advisor_class_verified = filter_var($inp_advisor_class_verified,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_control_code') {
				$inp_advisor_control_code = $str_value;
				$inp_advisor_control_code = filter_var($inp_advisor_control_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_date_created') {
				$inp_advisor_date_created = $str_value;
				$inp_advisor_date_created = filter_var($inp_advisor_date_created,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_date_updated') {
				$inp_advisor_date_updated = $str_value;
				$inp_advisor_date_updated = filter_var($inp_advisor_date_updated,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_replacement_status') {
				$inp_advisor_replacement_status = $str_value;
				$inp_advisor_replacement_status = filter_var($inp_advisor_replacement_status,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisor_action_log') {
				$inp_advisor_action_log = $str_value;
				$inp_advisor_action_log = filter_var($inp_advisor_action_log,FILTER_UNSAFE_RAW);
			}
		
			if ($str_key == 'inp_advisorclass_id') {
				$inp_advisorclass_id = $str_value;
				$inp_advisorclass_id = filter_var($inp_advisorclass_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_sequence') {
				$inp_advisorclass_sequence = $str_value;
				$inp_advisorclass_sequence = filter_var($inp_advisorclass_sequence,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_semester') {
				$inp_advisorclass_semester = $str_value;
				$inp_advisorclass_semester = filter_var($inp_advisorclass_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_timezone_offset') {
				$inp_advisorclass_timezone_offset = $str_value;
				$inp_advisorclass_timezone_offset = filter_var($inp_advisorclass_timezone_offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_level') {
				$inp_advisorclass_level = $str_value;
				$inp_advisorclass_level = filter_var($inp_advisorclass_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_language') {
				$inp_advisorclass_language = $str_value;
				$inp_advisorclass_language = filter_var($inp_advisorclass_language,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_class_size') {
				$inp_advisorclass_class_size = $str_value;
				$inp_advisorclass_class_size = filter_var($inp_advisorclass_class_size,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_class_schedule_days') {
				$inp_advisorclass_class_schedule_days = $str_value;
				$inp_advisorclass_class_schedule_days = filter_var($inp_advisorclass_class_schedule_days,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_class_schedule_times') {
				$inp_advisorclass_class_schedule_times = $str_value;
				$inp_advisorclass_class_schedule_times = filter_var($inp_advisorclass_class_schedule_times,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_class_schedule_days_utc') {
				$inp_advisorclass_class_schedule_days_utc = $str_value;
				$inp_advisorclass_class_schedule_days_utc = filter_var($inp_advisorclass_class_schedule_days_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_class_schedule_times_utc') {
				$inp_advisorclass_class_schedule_times_utc = $str_value;
				$inp_advisorclass_class_schedule_times_utc = filter_var($inp_advisorclass_class_schedule_times_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_class_incomplete') {
				$inp_advisorclass_class_incomplete = $str_value;
				$inp_advisorclass_class_incomplete = filter_var($inp_advisorclass_class_incomplete,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_date_created') {
				$inp_advisorclass_date_created = $str_value;
				$inp_advisorclass_date_created = filter_var($inp_advisorclass_date_created,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_date_updated') {
				$inp_advisorclass_date_updated = $str_value;
				$inp_advisorclass_date_updated = filter_var($inp_advisorclass_date_updated,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student01') {
				$inp_advisorclass_student01 = $str_value;
				$inp_advisorclass_student01 = filter_var($inp_advisorclass_student01,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student02') {
				$inp_advisorclass_student02 = $str_value;
				$inp_advisorclass_student02 = filter_var($inp_advisorclass_student02,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student03') {
				$inp_advisorclass_student03 = $str_value;
				$inp_advisorclass_student03 = filter_var($inp_advisorclass_student03,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student04') {
				$inp_advisorclass_student04 = $str_value;
				$inp_advisorclass_student04 = filter_var($inp_advisorclass_student04,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student05') {
				$inp_advisorclass_student05 = $str_value;
				$inp_advisorclass_student05 = filter_var($inp_advisorclass_student05,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student06') {
				$inp_advisorclass_student06 = $str_value;
				$inp_advisorclass_student06 = filter_var($inp_advisorclass_student06,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student07') {
				$inp_advisorclass_student07 = $str_value;
				$inp_advisorclass_student07 = filter_var($inp_advisorclass_student07,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student08') {
				$inp_advisorclass_student08 = $str_value;
				$inp_advisorclass_student08 = filter_var($inp_advisorclass_student08,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student09') {
				$inp_advisorclass_student09 = $str_value;
				$inp_advisorclass_student09 = filter_var($inp_advisorclass_student09,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student10') {
				$inp_advisorclass_student10 = $str_value;
				$inp_advisorclass_student10 = filter_var($inp_advisorclass_student10,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student11') {
				$inp_advisorclass_student11 = $str_value;
				$inp_advisorclass_student11 = filter_var($inp_advisorclass_student11,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student12') {
				$inp_advisorclass_student12 = $str_value;
				$inp_advisorclass_student12 = filter_var($inp_advisorclass_student12,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student13') {
				$inp_advisorclass_student13 = $str_value;
				$inp_advisorclass_student13 = filter_var($inp_advisorclass_student13,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student14') {
				$inp_advisorclass_student14 = $str_value;
				$inp_advisorclass_student14 = filter_var($inp_advisorclass_student14,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student15') {
				$inp_advisorclass_student15 = $str_value;
				$inp_advisorclass_student15 = filter_var($inp_advisorclass_student15,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student16') {
				$inp_advisorclass_student16 = $str_value;
				$inp_advisorclass_student16 = filter_var($inp_advisorclass_student16,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student17') {
				$inp_advisorclass_student17 = $str_value;
				$inp_advisorclass_student17 = filter_var($inp_advisorclass_student17,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student18') {
				$inp_advisorclass_student18 = $str_value;
				$inp_advisorclass_student18 = filter_var($inp_advisorclass_student18,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student19') {
				$inp_advisorclass_student19 = $str_value;
				$inp_advisorclass_student19 = filter_var($inp_advisorclass_student19,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student20') {
				$inp_advisorclass_student20 = $str_value;
				$inp_advisorclass_student20 = filter_var($inp_advisorclass_student20,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student21') {
				$inp_advisorclass_student21 = $str_value;
				$inp_advisorclass_student21 = filter_var($inp_advisorclass_student21,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student22') {
				$inp_advisorclass_student22 = $str_value;
				$inp_advisorclass_student22 = filter_var($inp_advisorclass_student22,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student23') {
				$inp_advisorclass_student23 = $str_value;
				$inp_advisorclass_student23 = filter_var($inp_advisorclass_student23,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student24') {
				$inp_advisorclass_student24 = $str_value;
				$inp_advisorclass_student24 = filter_var($inp_advisorclass_student24,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student25') {
				$inp_advisorclass_student25 = $str_value;
				$inp_advisorclass_student25 = filter_var($inp_advisorclass_student25,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student26') {
				$inp_advisorclass_student26 = $str_value;
				$inp_advisorclass_student26 = filter_var($inp_advisorclass_student26,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student27') {
				$inp_advisorclass_student27 = $str_value;
				$inp_advisorclass_student27 = filter_var($inp_advisorclass_student27,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student28') {
				$inp_advisorclass_student28 = $str_value;
				$inp_advisorclass_student28 = filter_var($inp_advisorclass_student28,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student29') {
				$inp_advisorclass_student29 = $str_value;
				$inp_advisorclass_student29 = filter_var($inp_advisorclass_student29,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_student30') {
				$inp_advisorclass_student30 = $str_value;
				$inp_advisorclass_student30 = filter_var($inp_advisorclass_student30,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_number_students') {
				$inp_advisorclass_number_students = $str_value;
				$inp_advisorclass_number_students = filter_var($inp_advisorclass_number_students,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_class_evaluation_complete') {
				$inp_advisorclass_class_evaluation_complete = $str_value;
				$inp_advisorclass_class_evaluation_complete = filter_var($inp_advisorclass_class_evaluation_complete,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_class_comments') {
				$inp_advisorclass_class_comments = $str_value;
				$inp_advisorclass_class_comments = filter_var($inp_advisorclass_class_comments,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_copy_control') {
				$inp_advisorclass_copy_control = $str_value;
				$inp_advisorclass_copy_control = filter_var($inp_advisorclass_copy_control,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'advisor_id') {
				$advisor_id = $str_value;
				$advisor_id = filter_var($advisor_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'advisorclass_id') {
				$advisorclass_id = $str_value;
				$advisorclass_id = filter_var($advisorclass_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_action_log') {
				$inp_advisorclass_action_log = $str_value;
				$inp_advisorclass_action_log = filter_var($inp_advisorclass_action_log,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_advisorclass_call_sign') {
				$inp_advisorclass_call_sign = $str_value;
				$inp_advisorclass_call_sign = filter_var($inp_advisorclass_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_sequence') {
				$inp_sequence = $str_value;
				$inp_sequence = filter_var($inp_sequence,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_times') {
				$inp_times = $str_value;
				$inp_times = filter_var($inp_times,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_semester') {
				$inp_semester = $str_value;
				$inp_semester = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'inp_class_schedule_days') {
				$inp_class_schedule_days = $str_value;
				$inp_class_schedule_days = filter_var($inp_class_schedule_days,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'submitswitch') {
				if ($str_value == 'Update this Advisor Record') {
					$strPass	= '3';
				} elseif ($str_value == 'Delete this Advisor and Classes') {
					$strPass	= '15';
				} elseif ($str_value == 'Update this Class') {
					$strPass	= '5';
				} elseif ($str_value == 'Delete this Class') {
					$strPass	= '20';
				} elseif ($str_value == 'Add a Class') {
					$strPass	= '10';
				}
			}

		}
	}
	
	$content = "";	

	if ($testMode) {
		$content						.= "<p><b>Operating in testMode</b></p>";
		$operatingMode					= 'Testmode';
	} else {
		$content						.= "<p><b>Operating in Production Mode</b></p>";
		$operatingMode					= 'Production';
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
	
	
/*
 * When strPass is equal to 1 then get the information needed to access the advisor
 * The advisorcan be accessed by the advisorID, call sign, surname, or email
 *
*/
	if ("1" == $strPass) {
	
		$content .= "<h3>$jobname</h3>
					<p>Please select the type of request and enter the value to be searched 
					in the Advisor table. Call sign can be either upper case or lower case. Last name must be 
					an exact match.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data''>
					<input type='hidden' name='strpass' value='2'>
					<table style='border-collapse:collapse;'>
					<tr><td style='width:150px;'>Request Type</td>
						<td><input class='formInputButton' type='radio' name='request_type' value='callsign' checked>Call Sign<br />
							<input class='formInputButton' type='radio' name='request_type' value='advisor_id'>AdvisorID<br />
							<input class='formInputButton' type='radio' name='request_type' value='surname'>Surname<br />
							<input class='formInputButton' type='radio' name='request_type' value='givenname'>Given Name<br />
							<input class='formInputButton' type='radio' name='request_type' value='email'>Email</td></tr>
					<tr><td>RequestInfo</td>
						<td><input class='formInputText' type='text' maxlength='50' name='request_info' size='30'  autofocus ></td></tr>
					<tr><td>Data Depth</td>
						<td><input type='radio' class='formInputButton' name='inp_depth' value='one' checked>Display most current data only<br />
							<input type='radio' class='formInputButton' name='inp_depth' value='all'>Display all data</td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
					</form>";


///////		Pass 2

	} elseif ("2" == $strPass) {
	
		if ($request_type == "callsign") {
			$request_info = strtoupper($request_info);
		}
		if ($doDebug) {
			debugReport("<br />at pass 2<br />Supplied input: <br />
					Request Type: $request_type; <br />
					Request Info: $request_info");
		}

		$content				.= "<h3>$jobname</h3>";

		if ($inp_depth == 'one') {
			$content			.= "<p><b>Showing Most Current Data Only</b></p>";
		}

		$haveUserMaster		= FALSE;
		// get the user_master info and format it
		if ($doDebug) {
			debugReport("getting the user_master data");
		}
		if ($request_type == 'callsign') {
			$request_info	= strtoupper($request_info);
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_call_sign', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
 				debugReport("set up the criteria for a callsign request type");
			}
		} elseif ($request_type == 'id') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_ID', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
				echo "set up the criteria for an id request type<br />";
			}
		} elseif ($request_type == 'surname') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_last_name', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
				debugReport("set up the criteria for a surname request type");
			}
		} elseif ($request_type == 'given') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_first_name', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
				debugReport("set up the criteria for a given name request type");
			}
		} elseif ($request_type == 'email') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_email', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
				debugReport("set up the criteria for an email request type");
			}
		} else {
			if ($doDebug) {
				debugReport("invalid request_type of $request_type");
			}
			$content	.= "<p>Sorry. Invalid method entered</p>";
			$doProceed	= FALSE;
		}
		
		if ($doProceed) {
			// get the user_master information
			if ($doDebug) {
				debugReport("calling user_dal->get_user_master. criteria:<pre>");
				$myStr = print_r($criteria, TRUE);
				debugReport("$myStr</pre>");
			}
			$user_data = $user_dal->get_user_master($criteria,'user_call_sign','ASC',$operatingMode);
			if ($doDebug) {
				debugReport("returned from get_user_master. user_data:<pre>");
				$myStr = print_r($user_data,TRUE);
				debugReport("$myStr</pre>");
			}
			if ($user_data === FALSE) {
				$content		.= "Attempting to retrieve $request_info failed<br />";
			} else {
				if (! empty($user_data)) {
					$myInt = count($user_data);
					if ($myInt > 1) {
						$strPass = '2a';
					} else
					if ($myInt == 1) {
						$haveUserMaster	= TRUE;
						$strPass = '2c';
					}
				}
			}
		}
	}


	if ($strPass == '2A') {
		if ($doDebug) {
			debugReport("<br />at pass 2A<br />
					request_type: $request_type<br />
					request_info: $request_info<br />
					trueRecordCount: $trueRecordCount<br />");
		}
		// build the select list
		$selectList	= "<select name='inp_list' class='formInputSelect'>";
		foreach ($user_data as $thisUser) {
			$thisCallsign	= $thisUser['user_call_sign'];
			$thisLastName	= $thisUser['user_last_name'];
			$thisFirstName	= $thisUser['user_first_name'];
			$thisID			= $thisUser['user_ID'];
			$selectList		.= "<option value='$thisCallsign|$thisLastName, $thisFirstName|$thisID'>
									$thisCallsign - $thisLastName, $thisFirstName</option>";
		}
		$selectList		.= "</select>";

		$content		.= "<p>Searching by $request_type for $request_info yielded 
							$myInt records. Select the record of interest</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2B'>
							<input type='hidden' name='request_type' value='$request_type'>
							<input type='hidden' name='request_info' value='$request_info'>
							<input type='hidden' name='inp_depth' value='$inp_depth'>
							<input type='hidden' name='inp_verbose' value='$inp_verbose'>
							<input type='hidden' name='inp_mode' value='$inp_mode'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Select from this list</tc>
								<td>$selectList</td>
							<tr><td></td>
									<td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";
									
			
	}
	if ($strPass == '2B') {
		if ($doDebug) {
			debugReport("<br />at pass 2B<br />
					request_type: $request_type<br />
					request_info: $request_info<br />
					inp_depth: $inp_depth<br />
					inp_list: $inp_list");
		}
// $thisCallsign|$thisLastName, $thisFirstName|$thisID
		$myArray	= explode("|",$inp_list);
		$thisCallsign	= $myArray[0];
		$thisName		= $myArray[1];
		$thisID			= $myArray[2];
		
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				[ 
					'field'   => 'user_call_sign', 
					'value'   => $thisCallsign, 
					'compare' => '=' 
				]
			]
		];
		$user_data = $user_dal->get_user_master($criteria,'user_call_sign','ASC',$operatingMode);
		if ($doDebug) {
			debugReport("returned from get_user_master. user_data:<pre>");
			$myStr = print_r($user_data,TRUE);
			debugReport("$myStr</pre>");
		}
		if ($user_data === FALSE) {
			$content		.= "Attempting to retrieve $request_info failed<br />";
		} else {
			if (! empty($user_data)) {
				foreach ($user_data as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					if (! isset($user_call_sign)) {
						if ($doDebug) {
							debugReport("loaded user_data but user_call_sing not set");
						}
					} else {
						// make sure we have all the data to continue
						$haveUserMaster	= TRUE;
						$strPass = '2c';
					}
				}
			} else {
				if ($doDebug) {
					debugReport("no user_data returned");
				}
			}
		}

	}
	if ($strPass == '2C') {
		if ($doDebug) {
			debugReport("<br />at pass 2C<br />
					request_type: $request_type<br />
					request_info: $request_info<br />
					haveUserMaster: $haveUserMaster");	
		}	
		$content .= "<h3>$jobname</h3>";
		$admin = 'N';
		if ($userRole == 'adminstrator') {
			$admin = 'Y';
		}
		
		$displayContent .= get_user_master_for_display('callsign',$user_call_sign,$admin,$operatingMode,$doDebug);
		if ($displayContent === FALSE) {
			$content .= "<p>Unable to retrieve user_master information for display</p>";
		} else {
			$content .= $displayContent;
			$content	.= "<p>Displaying Data for $getInfo resulting from searching 
							for $request_type of $request_info</p>
							$display";
	
			// now get the advisor record now that we have the callsign
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'advisor_call_sign', 'value' => $user_call_sign, 'compare' => '=' ],
				]
			];
			if ($inp_depth == 'one') {
				$order = 'DESC limit 1';
			} else {
				$order = 'DESC';
			}

			$advisor_data = $advisor_dal->get_advisor_by_order( $criteria, 'advisor_date_created', $order, $operatingMode );
			if ($advisor_data === FALSE) {
				$content		.= "Attempting to retrieve advisor data for $user_call_sign failed<br />";
			} else {
				if (! empty($advisor_data)) {
					foreach($advisor_data as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						if (! isset($advisor_call_sign)) {
						if ($doDebug) {
								debugReport("looking for advisor record but advisor_call_sign is not set");
							}
						}		
						$newActionLog		= formatActionLog($advisor_action_log);
	
						$tableCount++;
						$content .= "<form method='post' action='$theURL' 
									name='update_advisor_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='advisor_id' value='$advisor_id'>
									<input type='hidden' name='inp_advisor_call_sign' value='$inp_advisor_call_sign'>
									<input type='hidden'name='inp_advisor_semester' value='$inp_advisor_semester'>
									<input type='hidden' name='inp_depth' value='$inp_depth'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_depth' value='$inp_depth'>
									<h4><b>Record $tableCount</b> Table $advisorTableName</h4>
									<table style='border-collapse:collapse;'>
									<tr><td colspan='4'><b><u>$advisor_call_sign $advisor_semester Advisor Fields</u></b></td></tr>
									<tr><td style='vertical-align:top;'><b>Advisor<br />id</b><br />$advisor_ID</td>
										<td style='vertical-align:top;'><b>Advisor<br />call_sign</b><br />$advisor_call_sign</td>
										<td style='vertical-align:top;'><b>Advisor<br />semester</b><br />$advisor_semester</td>
										<td style='vertical-align:top;'><b>Advisor<br />welcome_email_date</b><br />$advisor_welcome_email_date</td></tr>
									<tr><td style='vertical-align:top;'><b>Advisor<br />verify_email_date</b><br />$advisor_verify_email_date</td>
										<td style='vertical-align:top;'><b>Advisor<br />verify_email_number</b><br />$advisor_verify_email_number</td>
										<td style='vertical-align:top;'><b>Advisor<br />verify_response</b><br />$advisor_verify_response</td>
										<td style='vertical-align:top;'><b>Advisor<br />class_verified</b><br />$advisor_class_verified</td></tr>
									<tr><td style='vertical-align:top;'><b>Advisor<br />control_code</b><br />$advisor_control_code</td>
										<td style='vertical-align:top;'><b>Advisor<br />date_created</b><br />$advisor_date_created</td>
										<td style='vertical-align:top;'><b>Advisor<br />date_updated</b><br />$advisor_date_updated</td>
										<td style='vertical-align:top;'><b>Advisor<br />replacement_status</b><br />$advisor_replacement_status</td></tr>
									<tr><td style='vertical-align:top;' colspan='4'><b>Advisor action_log</b><br />$advisor_action_log</td></tr>

									<tr><td colspan='2'><input type='submit' class='formInputButton' name='submitswitch' value='Update this Advisor Record' /></td>
										<td colspan='2'><input type='submit' class='formInputButton' name='submitswitch' value='Delete this Advisor and Classes' /></td></tr>
									</table></form><br /><br />";
									
						// get and display the advisorclass records
						$classCount			= 0;
						$criteria = [
							'relation' => 'AND',
							'clauses' => [
								['field' => 'advisorclass_call_sign', 'value' => $advisor_call_sign, 'compare' => '=' ],
								['field' => 'advisorclass_semester', 'value' => $advisor_semester, 'compare' => '=' ]
							]
						];
						$advisorclass_data = $advisor_dal->get_advisorclass_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
						if ($advisorclass_data === FALSE) {
							$content		.= "Attempting to retrieve advisorclass data for $advisor_call_sign failed<br />";
						} else {
							if (! empty($advisorclass_data)) {
								$numACRows = count($advisorclass_data);	
								foreach($advisorclass_data as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
									if (! isset($advisorclass_call_sign)) {
										if ($doDebug) {
											debugReport("looking for advisorclass record but advisorclass_call_sign is not set");
										}
									}
									$classCount++;
									$newACActionLog		= formatActionLog($advisorclass_action_log);
	
									// display the advisorclass record
									$content .= "<form method='post' action='$theURL' 
												name='update_advisor_form' ENCTYPE='multipart/form-data''>
												<input type='hidden' name='inp_advisorclass_id' value='$advisorclass_ID'>
												<input type='hidden' name='inp_advisorclass_call_sign' value='$advisorclass_call_sign'>
												<input type='hidden' name='inp_semester' value='$advisorclass_semester'>
												<input type='hidden' name='inp_depth' value='$inp_depth'>
												<input type='hidden' name='inp_verbose' value='$inp_verbose'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<h4><b>$advisorclass_call_sign $advisorclass_semester $advisorclass_level Class $advisorclass_sequence</b> Table $advisorClassTableName</h4>
												<table style='border-collapse:collapse;'>
												<tr><td colspan='4'><b><u>Advisor Class Fields</u></b></td></tr>
												<tr><td style='vertical-align:top;'><b>id</b><br />$advisorclass_ID</td>
													<td style='vertical-align:top;'><b>call_sign</b><br />$advisorclass_call_sign</td>
													<td style='vertical-align:top;'><b>sequence</b><br />$advisorclass_sequence</td>
													<td style='vertical-align:top;'><b>semester</b><br />$advisorclass_semester</td></tr>
												<tr><td style='vertical-align:top;'><b>timezone_offset</b><br />$advisorclass_timezone_offset</td>
													<td style='vertical-align:top;'><b>level</b><br />$advisorclass_level</td>
													<td style='vertical-align:top;'><b>Language</b><br />$advisorclass_language</td>
													<td style='vertical-align:top;'><b>class_size</b><br />$advisorclass_class_size</td></tr>
												<tr><td style='vertical-align:top;'><b>class_schedule_Local</b><br />$advisorclass_class_schedule_times $advisorclass_class_schedule_days</td>
													<td style='vertical-align:top;'><b>class_schedule_utc</b><br />$advisorclass_class_schedule_times_utc $advisorclass_class_schedule_days_utc</td>
													<td style='vertical-align:top;'><b>class_incomplete</b><br />$advisorclass_class_incomplete</td>
													<td></td></tr>
												<tr><td style='vertical-align:top;'><b>date_created</b><br />$advisorclass_date_created</td>
													<td style='vertical-align:top;'><b>date_updated</b><br />$advisorclass_date_updated</td>
													<td></td>
													<td></td></tr>
												<tr><td colspan='4'><table>
												<tr><td style='vertical-align:top;'><b>Student01</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student01&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student01</a></td>
													<td style='vertical-align:top;'><b>Student02</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student02&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student02</a></td>
													<td style='vertical-align:top;'><b>Student03</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student03&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student03</a></td>
													<td style='vertical-align:top;'><b>Student04</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student04&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student04</a></td>
													<td style='vertical-align:top;'><b>Student05</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student05&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student05</a></td></tr>
												<tr><td style='vertical-align:top;'><b>Student06</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student06&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student06</a></td>
													<td style='vertical-align:top;'><b>Student07</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student07&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student07</a></td>
													<td style='vertical-align:top;'><b>Student08</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student08&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student08</a></td>
													<td style='vertical-align:top;'><b>Student09</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student09&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student09</a></td>
													<td style='vertical-align:top;'><b>Student10</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student10&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student10</a></td></tr>
												<tr><td style='vertical-align:top;'><b>Student11</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student11&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student11</a></td>
													<td style='vertical-align:top;'><b>Student12</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student12&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student12</a></td>
													<td style='vertical-align:top;'><b>Student13</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student13&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student13</a></td>
													<td style='vertical-align:top;'><b>Student14</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student14&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student14</a></td>
													<td style='vertical-align:top;'><b>Student15</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student15&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student15</a></td></tr>
												<tr><td style='vertical-align:top;'><b>Student16</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student16&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student16</a></td>
													<td style='vertical-align:top;'><b>Student17</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student17&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student17</a></td>
													<td style='vertical-align:top;'><b>Student18</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student18&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student18</a></td>
													<td style='vertical-align:top;'><b>Student19</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student19&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student19</a></td>
													<td style='vertical-align:top;'><b>Student20</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student20&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student20</a></td></tr>
												<tr><td style='vertical-align:top;'><b>Student21</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student21&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student21</a></td>
													<td style='vertical-align:top;'><b>Student22</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student22&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student22</a></td>
													<td style='vertical-align:top;'><b>Student23</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student23&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student23</a></td>
													<td style='vertical-align:top;'><b>Student24</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student24&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student24</a></td>
													<td style='vertical-align:top;'><b>Student25</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student25&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student25</a></td></tr>
												<tr><td style='vertical-align:top;'><b>Student26</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student26&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student26</a></td>
													<td style='vertical-align:top;'><b>Student27</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student27&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student27</a></td>
													<td style='vertical-align:top;'><b>Student28</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student28&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student28</a></td>
													<td style='vertical-align:top;'><b>Student29</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student29&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student29</a></td>
													<td style='vertical-align:top;'><b>Student30</b><br />
															<a href='$studentUpdateURL?request_type=callsign&request_info=$advisorclass_student30&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
															target='_blank'>$advisorclass_student30</a></td></tr></table></td></tr>
												<tr><td style='vertical-align:top;'><b>number_students</b><br />$advisorclass_number_students</td>
													<td style='vertical-align:top;'><b>class_evaluation_complete</b><br />$advisorclass_class_evaluation_complete</td>
													<td style='vertical-align:top;'><b>class_comments</b><br />$advisorclass_class_comments</td>
													<td style='vertical-align:top;'><b>copycontrol</b><br />$advisorclass_copy_control</td></tr>
												<tr><td style='vertical-align:top;' colspan='4'><b>action_log</b><br />$advisorclass_action_log</td></tr>";

									if ($classCount == $numACRows) {			// show add a class
										$content	.= "<tr><td><input type='submit' class='formInputButton' name='submitswitch' value='Update this Class' /></td>
															<td><input type='submit' class='formInputButton' name='submitswitch' value='Delete this Class' /></td>
															<td><input type='submit' class='formInputButton' name='submitswitch' value='Add a Class' /></td>
															<td></td></tr>";
									} else {									// show only update and delete
										$content	.= "<tr><td><input type='submit' class='formInputButton' name='submitswitch' value='Update this Class' /></td>
															<td><input type='submit' class='formInputButton' name='submitswitch' value='Delete this Class' /></td>
															<td></td>
															<td></td></tr>";
									}										
									$content		.= "</table></form><br /><br />";
					
								}		// end of the while for advisorClass
							} else {
								$content	.= "<p>No advisorClass records found.</p>";
							}
						}
					}
				} else {
					if ($doDebug) {
						debugReport("no matching advisor records found");
					}
					$content	.= "<p>No advisor record found for $user_call_sign</p>";
				}
			}						
		}
		
		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			debugReport("<br />at pass 3 with id $advisor_id");
		}

		// display the request record to be modified
		$advisorData = $advisor_dal->get_advisor_by_id( $advisor_id, $operatingMode );
		if ($advisorData === FALSE || $advisorData === NULL) {
			debugReport("getting advisor by id for $advisor_id returned FALSE|NULL");
		} else {
			if (! empty($advisorData)) {
				foreach($advisorData as $key => $value) {
					$$key = $value;
				}
				if (! isset($advisor_call_sign)) {
					debugReport("supposedly have advisor record for id $advisor_id but advisor_call_sign not set");
				} else {				
					$content	.= "<h3>$jobname</h3>
									<p><a href='$theURL'>Display another advisor</a></p>
									<p>Table: $advisorTableName</p>
									<form method='post' name='updateAdvisor_form' action='$theURL' 
									ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='4'>
									<input type='hidden' name='advisor_id' value='$advisor_ID'>
									<input type='hidden' name='inp_depth' value='$inp_depth'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<table style='border-collapse:collapse;'>
									<tr><th style='width:200px;'>Field</th>
										<th>Value</th></tr>
								   <tr><td>Advisor ID</td>
										<td>$advisor_ID</td></tr>
									<tr><td>Advisor Call Sign</td>
										<td>$advisor_call_sign</td></tr>
									<tr><td>Advisor Semester</td>
										<td><input type='text' class='formInputText' name='inp_advisor_semester' size='15' maxlenth='15' value='$advisor_semester'></td></tr>
									<tr><td>Advisor Welcome Email Date</td>
										<td><input type='text' class='formInputText' name='inp_advisor_welcome_email_date' size='20' maxlenth='20' value='$advisor_welcome_email_date'></td></tr>
									<tr><td>Advisor Verify Email Date</td>
										<td><input type='text' class='formInputText' name='inp_advisor_verify_email_date' size='20' maxlenth='20' value='$advisor_verify_email_date'></td></tr>
									<tr><td>Advisor Verify Email Number</td>
										<td><input type='text' class='formInputText' name='inp_advisor_verify_email_number' size='5' maxlenth='5' value='$advisor_verify_email_number'></td></tr>
									<tr><td>Advisor Verify Response</td>
										<td><input type='text' class='formInputText' name='inp_advisor_verify_response' size='5' maxlenth='5' value='$advisor_verify_response'></td></tr>
									<tr><td>Advisor Class Verified</td>
										<td><input type='text' class='formInputText' name='inp_advisor_class_verified' size='5' maxlenth='5' value='$advisor_class_verified'></td></tr>
									<tr><td>Advisor Control Code</td>
										<td><input type='text' class='formInputText' name='inp_advisor_control_code' size='20' maxlenth='20' value='$advisor_control_code'></td></tr>
									<tr><td>Advisor Date Created</td>
										<td><input type='text' class='formInputText' name='inp_advisor_date_created' size='20' maxlenth='20' value='$advisor_date_created'></td></tr>
									<tr><td>Advisor Date Updated</td>
										<td><input type='text' class='formInputText' name='inp_advisor_date_updated' size='20' maxlenth='20' value='$advisor_date_updated'></td></tr>
									<tr><td>Advisor Replacement Status</td>
										<td><input type='text' class='formInputText' name='inp_advisor_replacement_status' size='5' maxlenth='5' value='$advisor_replacement_status'></td></tr>
									<tr><td>Advisor Action Log</td>
										<td><textarea class='formInputText' name='inp_advisor_action_log' rows='5' cols= '50'>$advisor_action_log</textarea></td></tr>
										<tr><td>&nbsp;</td>
											<td><input class='formInputButton' type='submit' value='Submit' /></td></tr>
										</table></form>
										<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisor_call_sign&strpass=2'&inp_depth=$inp_depth>HERE</a></p><br />"; 

				}			// end of the advisor while
			} else {
				$content	.= "<p>No record found for the record with the id of $advisor_id</p>";
			}
		}


////////  Pass 4 update advisor fields

	} elseif ("4" == $strPass) {
		if ($doDebug) {
			debugReport("<br />arrived at pass 4 with advisor_id of $advisor_id to update");
		}
		$actionContent			= "";
		$advisorData = $advisor_dal->get_advisor_by_id( $advisor_id, $operatingMode );
		if ($advisorData === FALSE || $advisorData === NULL) {
			debugReport("getting advisor by id for $advisor_id returned FALSE|NULL");
		} else {
			if (! empty($advisorData)) {
				foreach($advisorData as $key => $value) {
					$$key = $value;
				}
				if (! isset($advisor_call_sign)) {
					debugReport("supposedly have advisor record for id $advisor_id but advisor_call_sign not set");
				} else {								
					$content		= "<h3>Results $jobname $advisor_ID ($advisor_call_sign)</h3>";
					$doTheUpdate 						= FALSE;
					$updateData							= array();
					$updateFormat						= array();
					if ($inp_advisor_semester != $advisor_semester) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_semester'] = $inp_advisor_semester;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisor_semester of $advisor_semester to $inp_advisor_semester. ";
					}
					if ($inp_advisor_welcome_email_date != $advisor_welcome_email_date) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_welcome_email_date'] = $inp_advisor_welcome_email_date;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisor_welcome_email_date of $advisor_welcome_email_date to $inp_advisor_welcome_email_date. ";
					}
					if ($inp_advisor_verify_email_date != $advisor_verify_email_date) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_verify_email_date'] = $inp_advisor_verify_email_date;
						$updateFormat[] = "%2";
						$actionContent .= "Updated advisor_verify_email_date of $advisor_verify_email_date to $inp_advisor_verify_email_date. ";
					}
					if ($inp_advisor_verify_email_number != $advisor_verify_email_number) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_verify_email_number'] = $inp_advisor_verify_email_number;
						$updateFormat[] = "%d";
						$actionContent .= "Updated advisor_verify_email_number of $advisor_verify_email_number to $inp_advisor_verify_email_number. ";
					}
					if ($inp_advisor_verify_response != $advisor_verify_response) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_verify_response'] = $inp_advisor_verify_response;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisor_verify_response of $advisor_verify_response to $inp_advisor_verify_response. ";
					}
					if ($inp_advisor_class_verified != $advisor_class_verified) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_class_verified'] = $inp_advisor_class_verified;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisor_class_verified of $advisor_class_verified to $inp_advisor_class_verified. ";
					}
					if ($inp_advisor_control_code != $advisor_control_code) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_control_code'] = $inp_advisor_control_code;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisor_control_code of $advisor_control_code to $inp_advisor_control_code. ";
					}
					if ($inp_advisor_date_created != $advisor_date_created) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_date_created'] = $inp_advisor_date_created;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisor_date_created of $advisor_date_created to $inp_advisor_date_created. ";
					}
					if ($inp_advisor_date_updated != $advisor_date_updated) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_date_updated'] = $inp_advisor_date_updated;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisor_date_updated of $advisor_date_updated to $inp_advisor_date_updated. ";
					}
					if ($inp_advisor_replacement_status != $advisor_replacement_status) {
						$doTheUpdate = TRUE;
						$updateParams['advisor_replacement_status'] = $inp_advisor_replacement_status;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisor_replacement_status of $advisor_replacement_status to $inp_advisor_replacement_status. ";
					}
					if ($doTheUpdate) {
						if ($doDebug) {
							echo "Doing the update. Contents of the updateParams array:<br /><pre>";
							print_r($updateParams);
							echo "</pre><br />";
						}
						if ($inp_advisor_action_log != $advisor_action_log) { 
							$advisor_action_log				= $inp_advisor_action_log;
						}
						$advisor_action_log					= "$advisor_action_log ADVUPDATE $actionDate $userName $actionContent ";
						$updateParams['advisor_action_log'] = $advisor_action_log;
						$updateFormat[]						= '%s';
						$actionContent .= "Updated action_log.<br />";
						$updateResult = $advisor_dal->update( $advisor_id, $updateParams, $operatingMode );
						if ($updateResult === FALSE || $updateResult === NULL) {
							debugReport("attempting to update advisor record $advisor_id returned FALSE|NULL");
						} else {
							$content		.= $actionContent;	
						}				///// end of change class loop					
						$content		.= "<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisor_call_sign&strpass=2&inp_depth=$inp_depth&inp_verbose=$inp_verbose'>HERE</a></p><br />";
					} else {
						if ($doDebug) {
							debugReport("No updates were entered.");
						}
						$content .= "No updates were requested.<br />";
					}
				}
			} else {
				if ($doDebug) {
					debugReport("No record found for ID $advisor_id<br />");
				}
				$content	.= "No record found for ID $advisor_id<br />";
			}
		}
			
////////////	pass 5 ... show class record for modification		
		
	} elseif ("5" == $strPass) {
		if ($doDebug) {
			debugReport("Arrived at pass 5 with $inp_advisorclass_id");
		}

		// get the advisorClass record
		$advisorclassData = $advisorclass_dal->get_advisorclasses_by_id( $advisorclass_id, $operatingMode );
		if ($advisorclassData === FALSE || $advisorclassData === NULL) {
			debugReport("attempt to get advisorclass record $advisorclass_id returned FALSE|NULL");
		} else {
			if (! empty($advisorclassData)) {
				foreach($advisorclassData as $key => $value) {
					$$key = $value;
				}
				if (! isset($advisorclass_call_sign)) {
					debugReport("supposely have advisorclass record for advisorclas_id but advisorclass_call_sign not set");
				} else {					
					//Build language selection
					$languageOptions			= '';
					$firstTime					= TRUE;
					foreach($languageArray as $thisLanguage) {
						$thisChecked			= '';
						if ($advisorclass_language == $thisLanguage) {
							$thisChecked		= ' checked ';
						}
						if ($firstTime) {
							$firstTime			= FALSE;
							$languageOptions		.= "<input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage' $thisChecked>$thisLanguage";
						} else {
							$languageOptions		.= "<br /><input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage' $thisChecked>$thisLanguage";
						}
					}

					$content	.= "<h3>Update the Advisor Class $advisorclass_sequence for $advisorclass_call_sign</h3>
									<p>Table: $advisorClassTableName</p>
									<form method='post' name='selection_form' action='$theURL' 
									ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='6'>
									<input type='hidden' name='inp_advisorclass_id' value='$advisorclass_ID'>
									<input type='hidden' name='inp_depth' value='$inp_depth'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<table style='border-collapse:collapse;'>
									<tr><th style='width:280px;'>Field</th>
										<th>Value</th></tr>
								   <tr><td>ID</td>
										<td>$advisorclass_ID</td></tr>
									<tr><td>Call Sign</td>
										<td>$advisorclass_call_sign</td></tr>
									<tr><td>Sequence</td>
										<td>$advisorclass_sequence</td></tr>
									<tr><td>Semester</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_semester' size='15' maxlenth='15' value='$advisorclass_semester'></td></tr>
									<tr><td>Timezone Offset</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_timezone_offset' size='8' maxlenth='8' value='$advisorclass_timezone_offset'></td></tr>
									<tr><td>Level</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_level' size='15' maxlenth='15' value='$advisorclass_level'></td></tr>
									<tr><td style='vertical-align:top;'>Language</td>
										<td>$languageOptions</td></tr>
									<tr><td>Class Size</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_class_size' size='5' maxlenth='5' value='$advisorclass_class_size'></td></tr>
									<tr><td>Class Schedule Days</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_class_schedule_days' size='20' maxlenth='20' value='$advisorclass_class_schedule_days'></td></tr>
									<tr><td>Class Schedule Times</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_class_schedule_times' size='5' maxlenth='5' value='$advisorclass_class_schedule_times'></td></tr>
									<tr><td>Class Schedule Days Utc</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_class_schedule_days_utc' size='20' maxlenth='20' value='$advisorclass_class_schedule_days_utc'></td></tr>
									<tr><td>Class Schedule Times Utc</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_class_schedule_times_utc' size='5' maxlenth='5' value='$advisorclass_class_schedule_times_utc'></td></tr>
									<tr><td>Class Incomplete</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_class_incomplete' size='5' maxlenth='5' value='$advisorclass_class_incomplete'></td></tr>
									<tr><td>Date Created</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_date_created' size='20' maxlenth='20' value='$advisorclass_date_created'></td></tr>
									<tr><td>Date Updated</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_date_updated' size='20' maxlenth='20' value='$advisorclass_date_updated'></td></tr>

									<tr><td colspan='2'><table>
									<tr><td>Student01<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student01' size='20' maxlenth='20' value='$advisorclass_student01'></td>
										<td>Student02<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student02' size='20' maxlenth='20' value='$advisorclass_student02'></td>
										<td>Student03<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student03' size='20' maxlenth='20' value='$advisorclass_student03'></td>
										<td>Student04<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student04' size='20' maxlenth='20' value='$advisorclass_student04'></td>
										<td>Student05<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student05' size='20' maxlenth='20' value='$advisorclass_student05'></td></tr>
									<tr><td>Student06<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student06' size='20' maxlenth='20' value='$advisorclass_student06'></td>
										<td>Student07<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student07' size='20' maxlenth='20' value='$advisorclass_student07'></td>
										<td>Student08<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student08' size='20' maxlenth='20' value='$advisorclass_student08'></td>
										<td>Student09<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student09' size='20' maxlenth='20' value='$advisorclass_student09'></td>
										<td>Student10<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student10' size='20' maxlenth='20' value='$advisorclass_student10'></td></tr>
									<tr><td>Student11<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student11' size='20' maxlenth='20' value='$advisorclass_student11'></td>
										<td>Student12<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student12' size='20' maxlenth='20' value='$advisorclass_student12'></td>
										<td>Student13<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student13' size='20' maxlenth='20' value='$advisorclass_student13'></td>
										<td>Student14<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student14' size='20' maxlenth='20' value='$advisorclass_student14'></td>
										<td>Student15<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student15' size='20' maxlenth='20' value='$advisorclass_student15'></td></tr>
									<tr><td>Student16<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student16' size='20' maxlenth='20' value='$advisorclass_student16'></td>
										<td>Student17<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student17' size='20' maxlenth='20' value='$advisorclass_student17'></td>
										<td>Student18<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student18' size='20' maxlenth='20' value='$advisorclass_student18'></td>
										<td>Student19<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student19' size='20' maxlenth='20' value='$advisorclass_student19'></td>
										<td>Student20<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student20' size='20' maxlenth='20' value='$advisorclass_student20'></td></tr>
									<tr><td>Student21<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student21' size='20' maxlenth='20' value='$advisorclass_student21'></td>
										<td>Student22<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student22' size='20' maxlenth='20' value='$advisorclass_student22'></td>
										<td>Student23<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student23' size='20' maxlenth='20' value='$advisorclass_student23'></td>
										<td>Student24<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student24' size='20' maxlenth='20' value='$advisorclass_student24'></td>
										<td>Student25<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student25' size='20' maxlenth='20' value='$advisorclass_student25'></td></tr>
									<tr><td>Student26<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student26' size='20' maxlenth='20' value='$advisorclass_student26'></td>
										<td>Student27<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student27' size='20' maxlenth='20' value='$advisorclass_student27'></td>
										<td>Student28<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student28' size='20' maxlenth='20' value='$advisorclass_student28'></td>
										<td>Student29<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student29' size='20' maxlenth='20' value='$advisorclass_student29'></td>
										<td>Student30<br />
											<input type='text' class='formInputText' name='inp_advisorclass_student30' size='20' maxlenth='20' value='$advisorclass_student30'></td></tr></table>
									<tr><td>Number Students</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_number_students' size='5' maxlenth='5' value='$advisorclass_number_students'></td></tr>
									<tr><td>Class Evaluation Complete</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_class_evaluation_complete' size='5' maxlenth='5' value='$advisorclass_class_evaluation_complete'></td></tr>
									<tr><td>Class Comments</td>
										<td><textarea class='formInputText' name='inp_advisorclass_class_comments' rows='5' cols= '50'>$advisorclass_class_comments</textarea></td></tr>
									<tr><td>Copycontrol</td>
										<td><input type='text' class='formInputText' name='inp_advisorclass_copy_control' size='15' maxlenth='15' value='$advisorclass_copy_control'></td></tr>
									<tr><td>Action Log</td>
										<td><textarea class='formInputText' name='inp_advisorclass_action_log' rows='5' cols= '50'>$advisorclass_action_log</textarea></td></tr>
									<tr><td>&nbsp;</td>
										<td><input class='formInputButton' type='submit' value='Submit' /></td></tr>
									</table></form>
									<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisorclass_call_sign&strpass=2'>HERE</a></p><br />"; 

				}			// end of the advisorClass while
			} else {
				$content	.= "<p>No record found in $advisorClassTableName for $inp_advisor_call_sign class $inp_sequence/p>";
			}
		}
		


////////  	Pass 6 	update  advisorClass fields


	} elseif ("6" == $strPass) {
		if ($doDebug) {
			debugReport("<br />Arrived at pass 6 with $inp_advisorclass_id");
		}
		$doTheUpdate			= FALSE;
		$content				.= "<h3>Results from Updating the Advisor Class Record</h3>";
		// get the advisorClass record
		$advisorclassData = $advisorclass_dal->get_advisorclasses_by_id( $advisorclass_id, $operatingMode );
		if ($advisorclassData === FALSE || $advisorclassData === NULL) {
			debugReport("attempt to get advisorclass record $advisorclass_id returned FALSE|NULL");
		} else {
			if (! empty($advisorclassData)) {
				foreach($advisorclassData as $key => $value) {
					$$key = $value;
				}
				if (! isset($advisorclass_call_sign)) {
					debugReport("supposely have advisorclass record for advisorclas_id but advisorclass_call_sign not set");
				} else {					

					$updateParams	= array();
					$updateFormat	= array();

					$dotheUpdate	= FALSE;
					$actionContent	= '';
					$updateUTC		= FALSE;
					if ($inp_advisorclass_timezone_offset != $advisorclass_timezone_offset) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_timezone_offset'] = $inp_advisorclass_timezone_offset;
						$updateFormat[] = "%d";
						$actionContent .= "Updated advisorclass_timezone_offset of $advisorclass_timezone_offset to $inp_advisorclass_timezone_offset. ";
						$updateUTC	= TRUE;
						$advisorclass_timezone_offset	= $inp_advisorclass_timezone_offset;
					}
					if ($inp_advisorclass_level != $advisorclass_level) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_level'] = $inp_advisorclass_level;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_level of $advisorclass_level to $inp_advisorclass_level. ";
					}
					if ($inp_advisorclass_language != $advisorclass_language) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_language'] = $inp_advisorclass_language;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_language of $advisorclass_language to $inp_advisorclass_language. ";
					}
					if ($inp_advisorclass_class_size != $advisorclass_class_size) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_class_size'] = $inp_advisorclass_class_size;
						$updateFormat[] = "%d";
						$actionContent .= "Updated advisorclass_class_size of $advisorclass_class_size to $inp_advisorclass_class_size. ";
					}
					if ($inp_advisorclass_class_schedule_days != $advisorclass_class_schedule_days) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_class_schedule_days'] = $inp_advisorclass_class_schedule_days;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_class_schedule_days of $advisorclass_class_schedule_days to $inp_advisorclass_class_schedule_days. ";
						$updateUTC	= TRUE;
						$advisorclass_class_schedule_days	= $inp_advisorclass_class_schedule_days;
					}
					if ($inp_advisorclass_class_schedule_times != $advisorclass_class_schedule_times) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_class_schedule_times'] = $inp_advisorclass_class_schedule_times;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_class_schedule_times of $advisorclass_class_schedule_times to $inp_advisorclass_class_schedule_times. ";
						$updateUTC	= TRUE;
						$advisorclass_class_schedule_times	= $inp_advisorclass_class_schedule_times;
					}
					if ($inp_advisorclass_class_schedule_days_utc != $advisorclass_class_schedule_days_utc) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_class_schedule_days_utc'] = $inp_advisorclass_class_schedule_days_utc;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_class_schedule_days_utc of $advisorclass_class_schedule_days_utc to $inp_advisorclass_class_schedule_days_utc. ";
					}
					if ($inp_advisorclass_class_schedule_times_utc != $advisorclass_class_schedule_times_utc) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_class_schedule_times_utc'] = $inp_advisorclass_class_schedule_times_utc;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_class_schedule_times_utc of $advisorclass_class_schedule_times_utc to $inp_advisorclass_class_schedule_times_utc. ";
					}
					if ($inp_advisorclass_class_incomplete != $advisorclass_class_incomplete) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_class_incomplete'] = $inp_advisorclass_class_incomplete;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_class_incomplete of $advisorclass_class_incomplete to $inp_advisorclass_class_incomplete. ";
					}
					if ($inp_advisorclass_date_created != $advisorclass_date_created) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_date_created'] = $inp_advisorclass_date_created;
						$updateFormat[] = "%2";
						$actionContent .= "Updated advisorclass_date_created of $advisorclass_date_created to $inp_advisorclass_date_created. ";
					}
					if ($inp_advisorclass_date_updated != $advisorclass_date_updated) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_date_updated'] = $inp_advisorclass_date_updated;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_date_updated of $advisorclass_date_updated to $inp_advisorclass_date_updated. ";
					}
					if ($inp_advisorclass_student01 != $advisorclass_student01) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student01'] = $inp_advisorclass_student01;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student01 of $advisorclass_student01 to $inp_advisorclass_student01. ";
					}
					if ($inp_advisorclass_student02 != $advisorclass_student02) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student02'] = $inp_advisorclass_student02;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student02 of $advisorclass_student02 to $inp_advisorclass_student02. ";
					}
					if ($inp_advisorclass_student03 != $advisorclass_student03) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student03'] = $inp_advisorclass_student03;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student03 of $advisorclass_student03 to $inp_advisorclass_student03. ";
					}
					if ($inp_advisorclass_student04 != $advisorclass_student04) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student04'] = $inp_advisorclass_student04;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student04 of $advisorclass_student04 to $inp_advisorclass_student04. ";
					}
					if ($inp_advisorclass_student05 != $advisorclass_student05) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student05'] = $inp_advisorclass_student05;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student05 of $advisorclass_student05 to $inp_advisorclass_student05. ";
					}
					if ($inp_advisorclass_student06 != $advisorclass_student06) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student06'] = $inp_advisorclass_student06;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student06 of $advisorclass_student06 to $inp_advisorclass_student06. ";
					}
					if ($inp_advisorclass_student07 != $advisorclass_student07) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student07'] = $inp_advisorclass_student07;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student07 of $advisorclass_student07 to $inp_advisorclass_student07. ";
					}
					if ($inp_advisorclass_student08 != $advisorclass_student08) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student08'] = $inp_advisorclass_student08;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student08 of $advisorclass_student08 to $inp_advisorclass_student08. ";
					}
					if ($inp_advisorclass_student09 != $advisorclass_student09) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student09'] = $inp_advisorclass_student09;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student09 of $advisorclass_student09 to $inp_advisorclass_student09. ";
					}
					if ($inp_advisorclass_student10 != $advisorclass_student10) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student10'] = $inp_advisorclass_student10;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student10 of $advisorclass_student10 to $inp_advisorclass_student10. ";
					}
					if ($inp_advisorclass_student11 != $advisorclass_student11) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student11'] = $inp_advisorclass_student11;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student11 of $advisorclass_student11 to $inp_advisorclass_student11. ";
					}
					if ($inp_advisorclass_student12 != $advisorclass_student12) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student12'] = $inp_advisorclass_student12;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student12 of $advisorclass_student12 to $inp_advisorclass_student12. ";
					}
					if ($inp_advisorclass_student13 != $advisorclass_student13) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student13'] = $inp_advisorclass_student13;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student13 of $advisorclass_student13 to $inp_advisorclass_student13. ";
					}
					if ($inp_advisorclass_student14 != $advisorclass_student14) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student14'] = $inp_advisorclass_student14;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student14 of $advisorclass_student14 to $inp_advisorclass_student14. ";
					}
					if ($inp_advisorclass_student15 != $advisorclass_student15) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student15'] = $inp_advisorclass_student15;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student15 of $advisorclass_student15 to $inp_advisorclass_student15. ";
					}
					if ($inp_advisorclass_student16 != $advisorclass_student16) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student16'] = $inp_advisorclass_student16;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student16 of $advisorclass_student16 to $inp_advisorclass_student16. ";
					}
					if ($inp_advisorclass_student17 != $advisorclass_student17) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student17'] = $inp_advisorclass_student17;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student17 of $advisorclass_student17 to $inp_advisorclass_student17. ";
					}
					if ($inp_advisorclass_student18 != $advisorclass_student18) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student18'] = $inp_advisorclass_student18;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student18 of $advisorclass_student18 to $inp_advisorclass_student18. ";
					}
					if ($inp_advisorclass_student19 != $advisorclass_student19) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student19'] = $inp_advisorclass_student19;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student19 of $advisorclass_student19 to $inp_advisorclass_student19. ";
					}
					if ($inp_advisorclass_student20 != $advisorclass_student20) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student20'] = $inp_advisorclass_student20;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student20 of $advisorclass_student20 to $inp_advisorclass_student20. ";
					}
					if ($inp_advisorclass_student21 != $advisorclass_student21) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student21'] = $inp_advisorclass_student21;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student21 of $advisorclass_student21 to $inp_advisorclass_student21. ";
					}
					if ($inp_advisorclass_student22 != $advisorclass_student22) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student22'] = $inp_advisorclass_student22;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student22 of $advisorclass_student22 to $inp_advisorclass_student22. ";
					}
					if ($inp_advisorclass_student23 != $advisorclass_student23) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student23'] = $inp_advisorclass_student23;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student23 of $advisorclass_student23 to $inp_advisorclass_student23. ";
					}
					if ($inp_advisorclass_student24 != $advisorclass_student24) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student24'] = $inp_advisorclass_student24;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student24 of $advisorclass_student24 to $inp_advisorclass_student24. ";
					}
					if ($inp_advisorclass_student25 != $advisorclass_student25) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student25'] = $inp_advisorclass_student25;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student25 of $advisorclass_student25 to $inp_advisorclass_student25. ";
					}
					if ($inp_advisorclass_student26 != $advisorclass_student26) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student26'] = $inp_advisorclass_student26;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student26 of $advisorclass_student26 to $inp_advisorclass_student26. ";
					}
					if ($inp_advisorclass_student27 != $advisorclass_student27) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student27'] = $inp_advisorclass_student27;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student27 of $advisorclass_student27 to $inp_advisorclass_student27. ";
					}
					if ($inp_advisorclass_student28 != $advisorclass_student28) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student28'] = $inp_advisorclass_student28;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student28 of $advisorclass_student28 to $inp_advisorclass_student28. ";
					}
					if ($inp_advisorclass_student29 != $advisorclass_student29) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student29'] = $inp_advisorclass_student29;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student29 of $advisorclass_student29 to $inp_advisorclass_student29. ";
					}
					if ($inp_advisorclass_student30 != $advisorclass_student30) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_student30'] = $inp_advisorclass_student30;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_student30 of $advisorclass_student30 to $inp_advisorclass_student30. ";
					}
					if ($inp_advisorclass_number_students != $advisorclass_number_students) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_number_students'] = $inp_advisorclass_number_students;
						$updateFormat[] = "%d";
						$actionContent .= "Updated advisorclass_number_students of $advisorclass_number_students to $inp_advisorclass_number_students. ";
					}
					if ($inp_advisorclass_class_evaluation_complete != $advisorclass_class_evaluation_complete) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_evaluation_complete'] = $inp_advisorclass_class_evaluation_complete;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_class_evaluation_complete of $advisorclass_class_evaluation_complete to $inp_advisorclass_class_evaluation_complete. ";
					}
					if ($inp_advisorclass_class_comments != $advisorclass_class_comments) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_class_comments'] = $inp_advisorclass_class_comments;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_class_comments of $advisorclass_class_comments to $inp_advisorclass_class_comments. ";
					}
					if ($inp_advisorclass_copy_control != $advisorclass_copy_control) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_copy_control'] = $inp_advisorclass_copy_control;
						$updateFormat[] = "%s";
						$actionContent .= "Updated advisorclass_copy_control of $advisorclass_copy_control to $inp_advisorclass_copy_control. ";
					}
					if ($inp_advisorclass_action_log != $advisorclass_action_log) {
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_action_log'] = $inp_advisorclass_action_log;
						$actionContent .= "Updated advisorclass_action_log of $advisorclass_action_log to $inp_advisorclass_action_log. ";
					}
					
					if ($updateUTC) {
						if ($doDebug) {
							debugReport("Updating UTC info due to a local time change");
						}
						$utcResult		= utcConvert('toutc',$advisorclass_timezone_offset,$advisorclass_class_schedule_times,$advisorclass_class_schedule_days,$doDebug);
						if ($utcResult[0] == 'FAIL') {
							if ($doDebug) {
								debugReport("converting $advisorclass_timezone_offset,$inp_class_schedule_times,$inp_class_schedule_days to UTC failed");
							}
						} else {
							$inp_advisorclass_class_schedule_times_utc				= $utcResult[1];
							$inp_advisorclass_class_schedule_days_utc				= $utcResult[2];
							$updateParams['advisorclass_class_schedule_days_utc'] 	= $utcResult[2];
							$updateFormat[]											= '%s';
							$updateParams['advisorclass_class_schedule_times_utc']	= $utcResult[1];
							$updateFormat[]											= '%s';
							$doTheUpdate											= TRUE;
						}
					}
					// check number of students against the student list
					$myInt						= 0;
					for ($ii=1;$ii<31;$ii++) {
						$snum = $ii;
						if ($snum < 10) {
							$strSnum = str_pad($snum,2,'0',STR_PAD_LEFT);
						} else {
							$strSnum= strval($snum);
						}
						if (${'inp_advisorclass_student'.$strSnum} != '') {
							$myInt ++;
						}
					}
					if ($myInt != $advisorclass_number_students) {
						if ($doDebug) {
							debugReport("advisorclass_number_students is $advisorclass_number_students which does not match $myInt actual students");
						}
						$inp_number_students		= $myInt;
						$doTheUpdate = TRUE;
						$updateParams['advisorclass_number_students'] = $myInt;
						$updateFormat[] = '%d';
						$actionContent .= "updating current number_students to $inp_number_students. <br />";
					}
					
					if ($doTheUpdate) {
						if ($doDebug) {
							debugReport("Doing the update. Contents of the updateParams array:<pre>");
							$myStr = print_r($updateParams,TRUE);
							debugReport("$myStr</pre>");
						}
						$advisorclass_action_log .= " ADVUPDATE $actionDate $userName $actionContent ";
						$updateParams['advisorclass_action_log'] = $advisorclass_action_log;
						$updateResult = $advisorclass_dal->update( $advisorclass_id, $updateParams, $operatingMode );
						if ($updateResult === FALSE || $updateResult === NULL) {
							debugReport("Updating advisorclass id $advisorclass_id returned FALSE|NULL");
						} else {
							$content		.= $actionContent;	
						}			
					} else {
						if ($doDebug) {
							debugReport("No updates were entered");
						}
						$content .= "No updates were requested.<br />";
					}
				}
				$content		.= "<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisorclass_call_sign&strpass=2&inp_depth=$inp_depth'>HERE</a></p><br />";
			} else {
				if ($doDebug) {
					debugReport("No record found for id=$advisorclass_id");
				}
			}
		}




////////  	Pass 10 	add an advisorClass record


	} elseif ("10" == $strPass) {
		if ($doDebug) {
			debugReport("<br />Arrived at pass 10 with inp_advisorclass_id=$inp_advisorclass_id");
		}
		
		$content			.= "<h3>Add an advisorClass Record</h3>";
		// get the advisorClass record
		$advisorclassData = $advisorclass_dal->get_advisorclasses_by_id( $advisorclass_id, $operatingMode );
		if ($advisorclassData === FALSE || $advisorclassData === NULL) {
			debugReport("attempt to get advisorclass record $advisorclass_id returned FALSE|NULL");
		} else {
			if (! empty($advisorclassData)) {
				foreach($advisorclassData as $key => $value) {
					$$key = $value;
				}
				if (! isset($advisorclass_call_sign)) {
					debugReport("supposely have advisorclass record for advisorclas_id but advisorclass_call_sign not set");
				} else {					
					$inp_sequence = $advisorclass_sequence + 1;
					if ($doDebug) {
						debugReport("preping to add sequence $inp_sequence to $advisorclass_call_sign $advisorclass_semester semester");
					}

					//Build language selection
					$languageOptions			= '';
					$firstTime					= TRUE;
					foreach($languageArray as $thisLanguage) {
						if ($firstTime) {
							$firstTime			= FALSE;
							$languageOptions		.= "<input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage'>$thisLanguage";
						} else {
							$languageOptions		.= "<br /><input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage' >$thisLanguage";
						}
					}

					$content	.= "<form method='post' name='selection_form' action='$theURL' 
									ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='11'>
									<input type='hidden' name='inp_sequence' value='$inp_sequence'>
									<input type='hidden' name='inp_advisorclass_call_sign' value='$advisorclass_call_sign'>
									<input type='hidden' name='inp_advisorclass_semester' value='$advisorclass_semester'>
									<input type='hidden' name='inp_advisorclass_timezone_offset' value='$advisorclass_timezone_offset'>
									<input type='hidden' name='inp_depth' value='$inp_depth'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<table style='border-collapse:collapse;'>
									<tr><th style='width:200px;'>Field</th>
										<th>Value</th></tr>
									<tr><td style='vertical-align:top;'>Call Sign</td>
										<td>$advisorclass_call_sign</td></tr>
									<tr><td style='vertical-align:top;'>Sequence</td>
										<td>$inp_sequence</td></tr>
									<tr><td style='vertical-align:top;'>Semester</td>
										<td>$advisorclass_semester</td></tr>
									<tr><td>Class Size</td>
										<td>$advisorclass_timezone_offset</td></tr>
									<tr><td style='vertical-align:top;'>Level</td>
										<td><input type='radio' class='formInputButton' name='inp_advisorclass_level' value='Beginner' checked='checked'> Beginner<br />
											<input type='radio' class='formInputButton' name='inp_advisorclass_level' value='Fundamental'> Fundamental<br />
											<input type='radio' class='formInputButton' name='inp_advisorclass_level' value='Intermediate'> Intermediate<br />
											<input type='radio' class='formInputButton' name='inp_advisorclass_level' value='Advanced'> Advanced<br /></td></tr>
									<tr><td style='vertical-align:top;'>Language</td>
										<td>$languageOptions</td></tr>
									<tr><td style='vertical-align:top;'>Class Size</td>
										<td><input class='formInputText' type='text' name='inp_advisorclass_class_size' size='5' maxlenth='5' value='6'></td></tr>
									<tr><td style='vertical-align:top;'>Class Schedule Days</td>
										<td><input type='radio' class='formInputButton' name='inp_class_schedule_days' value='Sunday,Wednesday'> Sunday and Wednesday<br />
											<input type='radio' class='formInputButton' name='inp_class_schedule_days' value='Sunday,Thursday'> Sunday and Thursday<br />
											<input type='radio' class='formInputButton' name='inp_class_schedule_days' value='Monday,Thursday' checked> Monday and Thursday<br />
											<input type='radio' class='formInputButton' name='inp_class_schedule_days' value='Tuesday,Friday'> Tuesday and Friday</td></tr>
									<tr><td style='vertical-align:top;'>Class Schedule Time<br /><i>Select the local time where you will be teaching</i></td>
										<td colspan='2'><table><tr>
										<td style='width:110px;vertical-align:top;'>
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0600'  > 6:00am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0630' required  > 6:30am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0700'  > 7:00am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0730'  > 7:30am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0800'  > 8:00am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0830'  > 8:30am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0900'  > 9:00am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0930'  > 9:30am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1000'  > 10:00am</td>
										<td style='width:110px;vertical-align:top;'>
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1030'  > 10:30am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1100'  > 11:00am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1130'  > 11:30am<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1200'  > Noon<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1230'  > 12:30pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1300'  > 1:00pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1330'  > 1:30pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1400'  > 2:00pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1430'  > 2:30pm</td>
										<td style='width:110px;vertical-align:top;'>
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1500'  > 3:00pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1530'  > 3:30pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1600'  > 4:00pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1630'  > 4:30pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1700'  > 5:00pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1730'  > 5:30pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1800'  > 6:00pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1830'  > 6:30pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1900'  > 7:00pm</td>
										<td style='width:110px;vertical-align:top;'>
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1930' required  > 7:30pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2000'  > 8:00pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2030' required  > 8:30pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2100' required  > 9:00pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2130'  > 9:30pm<br />
											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2200'  > 10:00pm<br />
 											<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2230'  > 10:30pm</td></tr>
 									</table></tr>
									<tr><td style='vertical-align:top;'>Class Comments</td>
										<td><textarea class='formInputText' name='inp_class_comments' cols='50' rows='5'></textarea></td></tr>
									<tr><td>&nbsp;</td>
										<td><input class='formInputButton' type='submit' value='Add AdvisorClass Record' /></td></tr>
									</table></form>
									<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisorclass_call_sign&strpass=2&inp_depth=$inp_depth'>HERE</a></p><br />"; 
				}			// end of the advisorClass while
			} else {
				$content	.= "<p>No record found for $inp_advisor_call_sign</p>";
			}
		}



	} elseif ("11" == $strPass) {				//// do the advisorClass add
		if ($doDebug) {
			debugReport("<br />Arrived at pass 11 with inp_advisorclass_call_sign=$inp_advisorclass_call_sign");
		}
		
		$content				.= "<h3>Adding advisorClass Sequence $inp_sequence for Advisor $inp_advisorclass_call_sign</h3>";
		
		//// convert class days and times to UTC
		
		$result						= utcConvert('toutc',$inp_advisorclass_timezone_offset,$inp_times,$inp_class_schedule_days,$doDebug);
		if ($result[0] == 'FAIL') {
			if ($doDebug) {
				echo "utcConvert failed 'toutc',$inp_advisorclass_timezone_offset,$inp_class_schedule_times,$inp_class_schedule_days<br />
Error: $result[3]<br />";
			}
			$displayDays			= "<b>ERROR</b>";
			$displayTimes			= '';
		} else {
			$displayTimes			= $result[1];
			$displayDays			= $result[2];
		}
		
		$inp_number_students		= 9;

		$log_actionDate				= date('Y-m-d H:i:s');
		$advisorclass_action_log	= "$log_actionDate Class record added by $userName using Display and Update Advisor Info ";
		
		$insertParams=array('advisorclass_call_sign => $inp_advisorclass_call_sign',
							'advisorclass_sequence => $inp_sequence',
							'advisorclass_semester => $inp_advisorclass_semester',
							'advisorclass_timezone_offset => $inp_advisorclass_timezone_offset', 
							'advisorclass_level => $inp_advisorclass_level',
							'advisorclass_language => $inp_advisorclass_language',
							'advisorclass_action_log => $advisorclass_action_log',
							'advisorclass_class_size => $inp_advisorclass_class_size',
							'advisorclass_class_schedule_days => $inp_advisorclass_class_schedule_days',
							'advisorclass_class_schedule_times => $inp_times',
							'advisorclass_class_schedule_days_utc => $displayDays',
							'advisorclass_class_schedule_times_utc => $displayTimes',
							'advisorclass_number_students => $inp_advisorclass_number_students',
							'advisorclass_evaluation_complete => N',
							'advisorclass_class_comments => $inp_advisorclass_class_comments');
		$updateResult = $advisorclass_dal->insert( $insertParams, $operatingMode );									
		if ($updateResult === FALSE || $updateResult === NULL) {
			debugReport("addin gnew advisor class returned FALSE|NULL");
		} else {
			$advisorclass_call_sign					= $inp_advisorclass_call_sign;
			$advisorclass_sequence					= $inp_sequence;
			$advisorclass_semester					= $inp_advisorclass_semester;
			$advisorclass_timezone_offset			= $inp_advisorclass_timezone_offset;
			$advisorclass_level						= $inp_advisorclass_level;
			$advisorclass_language					= $inp_advisorclass_language;
			$advisorCLass_language					= $inp_advisorclass_language;
			$advisorclass_class_size				= $inp_advisorclass_class_size;
			$advisorclass_class_schedule_days		= $inp_class_schedule_days;
			$advisorclass_class_schedule_times		= $inp_times;
			$advisorclass_class_schedule_days_utc	= $displayDays;
			$advisorclass_class_schedule_times_utc	= $displayTimes;
			$advisorclass_evaluation_complete		= 'N';
			$advisorclass_number_students			= $inp_advisorclass_number_students;
			$advisorclass_class_comments			= $inp_advisorclass_class_comments;
			$newid									= $wpdb->insert_id;

			// Now display the class record
			$content	.= "<b>Class $inp_sequence:</b>
							<p><a href='$theURL'>Display another advisor</a></p>
							<table style='width:600px;'>
							<tr><td style='width:250px;'><b>Level</b></td>
								<td>$advisorclass_level</td></tr>
							<tr><td><b>Language</b></td>
								<td>$advisorclass_language</td></tr>
							<tr><td><b>Class Size</b></td>
								<td>$advisorclass_class_size</td></tr>
							<tr><td><b>Class Schedule Days</b></td>
								<td>$advisorclass_class_schedule_days</td></tr>
							<tr><td><b>Class Schedule Time<b></td>
								<td>$advisorclass_class_schedule_times</td></tr>
							<tr><td><b>Class Schedule Days UTC</b></td>
								<td>$advisorclass_class_schedule_days_utc</td></tr>
							<tr><td><b>Class Schedule Times UTC<b></td>
								<td>$advisorclass_class_schedule_times_utc</td></tr>
							<tr><td><b>Number Students</b></td>
								<td>$advisorclass_number_students</td></tr>
							<tr><td><b>Evaluation Complete</b></td>
								<td>$advisorclass_evaluation_complete</td></tr>
							<tr><td><b>Class Comments</b></td>
								<td>$advisorclass_class_comments</td></tr>
							</table>
							<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$inp_advisorclass_call_sign&strpass=2&inp_depth=$inp_depth'>HERE</a></p><br />";
		}

	} elseif ("15" == $strPass) {
		if ($doDebug) {
			debugReport("<br />at pass 15 Delete this advisor and classes");
		}
		
		$content			= "<h3>$jobname</h3>";
		
		// delete the advisor record
		$deleteResult = $advisor_dal->delete( $advisor_id, $operatingMode );
		if ($deleteResult === FALSE || $deleteResult === NULL) {
			debugReport("deleting advisor id $advisor_id returned FALSE|NULL");
		} else {
			$content .= "<p>Advisor record has been deleted</p>";
			if ($doDebug) {
				debugReport("advisor record deleted. now delete the class records");
			}
			/// delete the class records
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					 ['field' => 'advisorclass_call_sign', 'value' => $inp_advisor_call_sign, 'compare' => '=' ],
					['field' => 'advisorclass_semester', 'value' => $inp_advisor_semester, 'compare' => '=' ]
				]
			];
			$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
			if ($advisorclassData === FALSE || $advisorclassData === NULL) {
				debugReport("getting advisorclass data for deletion returned FALSE|NULL");
			} else {
				if (! empty($advisorclassData)) {
					foreach($advisorclassData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						if (! isset($advisorclass_call_sign)) {
							debugReport("supposedly have advisorclass data but advisorclass_call_sign not set");
						} else {
							// if there are any students, they need to be unassigned
							if ($advisorclass_number_students > 0) {
								if ($doDebug) {
									debugReport("have to unassign $advisorclass_number_students students");
								}
								$content		.= "<p>The class has $advisorclass_number_students students assigned. They 
													will each be unassigned</p>";	
								for ($snum=1;$snum<31;$snum++) {
									if ($snum < 10) {
										$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
									} else {
										$strSnum		= strval($snum);
									}
									$unassignCallSign	= ${'advisorclass_student' . $strSnum};
									if ($doDebug) {
										debugReport("obtained $unassignCallSign for snum $strSnum");
									}
									if ($unassignCallSign != '') {
										$inp_data			= array('inp_student'=>$unassignCallSign,
																	'inp_semester'=>$advisorclass_semester,
																	'inp_assigned_advisor'=>$advisorclass_call_sign,
																	'inp_assigned_advisor_class'=>$advisorclass_sequence,
																	'inp_remove_status'=>'',
																	'inp_arbitrarily_assigned'=>'',
																	'inp_method'=>'remove',
																	'jobname'=>$jobname,
																	'userName'=>$userName,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug);
														
										$removeResult		= add_remove_student($inp_data);
										if ($removeResult[0] === FALSE) {
											$thisReason		= $removeResult[1];
											if ($doDebug) {
												echo "attempting to remove $unassignCallSign from $advisorclass_call_sign class failed:<br />$thisReason<br />";
											}
											sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
											$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
										} else {
											$content		.= "Student $unassignCallSign removed from class and unassigned<br />";
				
										}
									}
								}
							}
				
							//// delete the class record
							$deleteResult = $advisorclass_dal->delete( $advisorclass_id, $operatingMode );
							if ($deleteResult === FALSE || $deleteResult === NULL) {
								debugReport("attempt to delete advisorclass id $advisorclass_id returned FALSE|NULL");
							} else {
								$content .= "<p>$advisor_call_sign class record $advisorclass_sequence deleted</p>";
							}
						}
						$content					.= "<p>The advisor and class records have been deleted.</p>
														<p>To return to the initial advisor page, click 
														<a href='$theURL?request_type=callsign&request_info=$advisorclass_call_sign&strpass=2'>HERE</a></p>
														<p>Otherwise, you can close this window</p>";
					}
				} else {
					$content .= "<p>No advisorclass data for $advisor_call_sign</p>";
				}
			}
		}
		
	} elseif ("20" == $strPass) {
		if ($doDebug) {
			debugReport("<br />at pass 20 Delete this Class");
		}	
		$content				.= "<h3>$jobname</h3>
									<p>You have requested the advisorClass record number $inp_advisorclass_id to be deleted</p>";
		
		// first, find out how many class records there are
		$goOn					= TRUE;
		// if only one, tell user to go back and delete the whole shebang
		$thisclasscount			= $wpdb->get_var("select count(advisorclass_call_sign) as thisclasscount 
									from $advisorClassTableName 
									where advisorclass_call_sign = '$inp_advisorclass_call_sign' 
									and advisorclass_semester = '$inp_semester'");
		if ($doDebug) {
			echo "thisclasscount: $thisclasscount<br />";
		}

		if ($thisclasscount == NULL) {
			handleWPDBError($jobname,$doDebug);
			$goOn				= FALSE;
		} else {
			if ($thisclasscount == 1) {		// can't delete this class
				$content		.= "<p>There is only one advisorClass record for 
									$advisor_call_sign in the $advisor_semester semester. 
									You must delete the advisor as well as the class.</p>
									<p>To return to the initial advisor page, click 
									<a href='$theURL?request_type=callsign&request_info=$inp_advisorclass_call_sign&strpass=2'>HERE</a></p>";
				$goOn			= FALSE;
			}
		}
		if ($goOn) {
			// get the advisorClass record
			$sql					= "select * from $advisorClassTableName 	
										where advisorclass_id=$inp_advisorclass_id";
			$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisorclass === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numACRows						= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numACRows rows<br />";
				}
				if ($numACRows > 0) {
					foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
						$advisorclass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorclass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
						$advisorclass_sequence 					= $advisorClassRow->advisorclass_sequence;
						$advisorclass_semester 					= $advisorClassRow->advisorclass_semester;
						$advisorclass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
						$advisorclass_level 					= $advisorClassRow->advisorclass_level;
						$advisorclass_language					= $advisorClassRow->advisorclass_language;
						$advisorclass_class_size 				= $advisorClassRow->advisorclass_class_size;
						$advisorclass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
						$advisorclass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
						$advisorclass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
						$advisorclass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
						$advisorclass_action_log 				= $advisorClassRow->advisorclass_action_log;
						$advisorclass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
						$advisorclass_date_created				= $advisorClassRow->advisorclass_date_created;
						$advisorclass_date_updated				= $advisorClassRow->advisorclass_date_updated;
						$advisorclass_student01 				= $advisorClassRow->advisorclass_student01;
						$advisorclass_student02 				= $advisorClassRow->advisorclass_student02;
						$advisorclass_student03 				= $advisorClassRow->advisorclass_student03;
						$advisorclass_student04 				= $advisorClassRow->advisorclass_student04;
						$advisorclass_student05 				= $advisorClassRow->advisorclass_student05;
						$advisorclass_student06 				= $advisorClassRow->advisorclass_student06;
						$advisorclass_student07 				= $advisorClassRow->advisorclass_student07;
						$advisorclass_student08 				= $advisorClassRow->advisorclass_student08;
						$advisorclass_student09 				= $advisorClassRow->advisorclass_student09;
						$advisorclass_student10 				= $advisorClassRow->advisorclass_student10;
						$advisorclass_student11 				= $advisorClassRow->advisorclass_student11;
						$advisorclass_student12 				= $advisorClassRow->advisorclass_student12;
						$advisorclass_student13 				= $advisorClassRow->advisorclass_student13;
						$advisorclass_student14 				= $advisorClassRow->advisorclass_student14;
						$advisorclass_student15 				= $advisorClassRow->advisorclass_student15;
						$advisorclass_student16 				= $advisorClassRow->advisorclass_student16;
						$advisorclass_student17 				= $advisorClassRow->advisorclass_student17;
						$advisorclass_student18 				= $advisorClassRow->advisorclass_student18;
						$advisorclass_student19 				= $advisorClassRow->advisorclass_student19;
						$advisorclass_student20 				= $advisorClassRow->advisorclass_student20;
						$advisorclass_student21 				= $advisorClassRow->advisorclass_student21;
						$advisorclass_student22 				= $advisorClassRow->advisorclass_student22;
						$advisorclass_student23 				= $advisorClassRow->advisorclass_student23;
						$advisorclass_student24 				= $advisorClassRow->advisorclass_student24;
						$advisorclass_student25 				= $advisorClassRow->advisorclass_student25;
						$advisorclass_student26 				= $advisorClassRow->advisorclass_student26;
						$advisorclass_student27 				= $advisorClassRow->advisorclass_student27;
						$advisorclass_student28 				= $advisorClassRow->advisorclass_student28;
						$advisorclass_student29 				= $advisorClassRow->advisorclass_student29;
						$advisorclass_student30 				= $advisorClassRow->advisorclass_student30;
						$advisorclass_number_students			= $advisorClassRow->advisorclass_number_students;
						$advisorclass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
						$advisorclass_class_comments			= $advisorClassRow->advisorclass_class_comments;
						$advisorclass_copycontrol				= $advisorClassRow->advisorclass_copy_control;
	
						// if there are any students, they need to be unassigned
						if ($advisorclass_number_students > 0) {
							if ($doDebug) {
								echo "have to unassign $advisorclass_number_students students<br />";
							}
							$content		.= "<p>The class has $advisorclass_number_students students assigned. They 
												will each be unassigned</p>";
			
							for ($snum=1;$snum<31;$snum++) {
								if ($snum < 10) {
									$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
								} else {
									$strSnum		= strval($snum);
								}
								$unassignCallSign	= ${'advisorclass_student' . $strSnum};
								if ($doDebug) {
									echo "obtained $unassignCallSign for snum $strSnum<br />";
								}
								if ($unassignCallSign != '') {
									$inp_data			= array('inp_student'=>$unassignCallSign,
																'inp_semester'=>$advisorclass_semester,
																'inp_assigned_advisor'=>$advisorclass_call_sign,
																'inp_assigned_advisor_class'=>$advisorclass_sequence,
																'inp_remove_status'=>'',
																'inp_arbitrarily_assigned'=>'',
																'inp_method'=>'remove',
																'jobname'=>$jobname,
																'userName'=>$userName,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug);
													
									$removeResult		= add_remove_student($inp_data);
									if ($removeResult[0] === FALSE) {
										$thisReason		= $removeResult[1];
										if ($doDebug) {
											echo "attempting to remove $unassignCallSign from $advisorclass_call_sign class failed:<br />$thisReason<br />";
										}
										sendErrorEmail("$jobname Attempting to remove $unassignCallSign from $advisorclass_call_sign class failed:<br />$thisReason");
										$content		.= "Attempting to remove $unassignCallSign from $advisorclass_call_sign class failed:<br />$thisReason<br />";
									} else {
										$content		.= "Student $unassignCallSign removed from class and unassigned<br />";
			
									}
								}
							}
						}
			
						// now delete the class
						$classUpdateData		= array('tableName'=>$advisorClassTableName,
														'inp_method'=>'delete',
														'inp_data'=>array(),
														'inp_format'=>array(),
														'jobname'=>$jobname,
														'inp_id'=>$advisorclass_ID,
														'inp_callsign'=>$advisorclass_call_sign,
														'inp_semester'=>$advisorclass_semester,
														'inp_sequence'=>$advisorclass_sequence,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateClass($classUpdateData);
						if ($updateResult[0] === FALSE) {
							handleWPDBError("FUNCTION Update Advisor Class $jobname",$doDebug);
							$content		.= "Unable to delete content in $advisorClassTableName<br />";
						} else {
							$content		.= "<br /><p>AdvisorClass record $advisorclass_ID for $advisorclass_call_sign $advisorclass_semester deleted</p>";
							
							// since there is more than one class record, possibly need to resequence them
							$reseqSQL		= "select * from $advisorClassTableName 
												where advisorclass_call_sign = '$advisorclass_call_sign' 
												and advisorclass_semester = '$advisorclass_semester' 
												order by advisorclass_sequence";
							$reseqResult	= $wpdb->get_results($reseqSQL);
							if ($reseqResult === FALSE) {
								handWPDBError($jobname,$doDebug);
							} else {
								$numRRows	= $wpdb->num_rows;
								if ($doDebug) {
									echo "resequencing. Ran $reseqSQL<br />and retrieved $numRRows rows<br />";
								}
								if ($numRRows > 0) {
									$kk		= 0;
									$content	.= "<p>Resquencing advisorClass records for $advisorclass_call_sign in $advisorclass_semester semester</p>";
									foreach($reseqResult as $reseqRow) {
										$reseqClass_ID		= $reseqRow->advisorclass_id;
										$reseq_sequence		= $reseqRow->advisorclass_sequence;
										
										$kk++;
										
										if ($doDebug) {
											echo "reseqClass_ID: $reseqClass_ID<br />
												  reseq_sequence: $reseq_sequence<br />
												  Sequence should be $kk<br />";
										}
					
										if ($reseq_sequence != $kk) { 	// have to update this record
											$thisUpdate		= $wpdb->update($advisorClassTableName, 
																array('advisorclass_sequence'=>$kk), 
																array('advisorclass_id'=>$reseqClass_ID),
																array('%d'),
																array('%d'));
											if($thisUpdate === FALSE) {
												handleWPDBError($jobname,$doDebug);
											} else {
												if ($doDebug) {
													echo "Class record $reseqClass_ID resequenced to $kk<br />";
												}
											}
										}
									}
								}
							}
							$content					.= "<p>The requested class record has been deleted.</p>
															<p>To return to the initial advisor page, click 
															<a href='$theURL?request_type=callsign&request_info=$inp_advisorclass_call_sign&strpass=2'>HERE</a></p>
															<p>Otherwise, you can close this window</p>";
						}
					}
				} else {
					if ($doDebug) {
						echo "no advisorClass record found for id $classID to delete<br />";
					}
					$content			.= "<b>Fatal Error</b> No class record found by that ID. Sys admin has been notified";
					sendErrorEmail("$jobname Pass 17 no record found for classID $classID");
				}
			}
		}
	}
	$content					.= "<br /><p><a href='$theURL'>Display another advisor</a></p>";
	$thisTime 					= date('Y-m-d H:i:s');
	$content					.= "<br /><br /><br /><p>Report displayed at $thisTime.</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d H:i:s');
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
add_shortcode ('display_and_update_advisor_info', 'display_and_update_advisor_info_func');

