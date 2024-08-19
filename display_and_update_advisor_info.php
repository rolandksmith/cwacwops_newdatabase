function display_and_update_advisor_info_func() {

/*
	Function to display and update Advisor information
 

	mod 15feb20 Bob c - add find by email
	update semester - bc 20aug20 
 	Modified 23June2021 by Roland for the new advisor and advisorClass layout/
 	Modified 15Dec21 by Roland to be able to add additional advisor classes and moved 
		to V2
	Modified 14Jan2022 by Roland to use advisor and advisorClass tables
	Modified 23May22 by Roland to move records to advisor_deleted rather than just delete the record
	Modified 28Sep22 by Roland for the updated database information
	Modified 22Feb23 by Roland to have the same advisorclass format in current and past semesters
	Modified 15Apr23 by Roland to fix action_log
	Modified 13Jul23 by Roland to use consolidated tables
	Modifed 28Aug23 by Roland to add replacement_status field
*/

	global $wpdb;
	$doDebug 						= FALSE;
	$testMode						= FALSE;

	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
		ini_set('display_errors','1');
		error_reporting(E_ALL);	

	$siteURL			= $initializationArray['siteurl'];
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

// initial values	
	$requestType					= "";
	$requestInfo					= "";
	$strPass						= "1";
	$theURL							= "$siteURL/cwa-display-and-update-advisor-information/";
	$fieldTest						= array('action_log','post_status','post_title','control_code');
	$actionDate						= date('dMy H:i');
	$logDate						= date('Y-m-d H:i');
	$userName						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$inp_advisor					= '';
	$advisorID						= '';
	$inp_sequence					= 0;
	$inp_advisor_call_sign			= '';
	$inp_advisor_first_name			= '';
	$inp_advisor_last_name			= '';
	$inp_semester					= '';
	$inp_time_zone					= '';
	$inp_level						= '';
	$inp_class_schedule_days		= '';
	$inp_class_schedule_times		= '';
	$inp_recdel						= "";
	$inp_verbose					= "";
	$inp_ph_code					= "";
	$inp_country_code				= "";
	$inp_whatsapp					= '';
	$inp_signal						= '';
	$inp_telegram					= '';
	$inp_messenger					= '';
	$inp_timezone_id				= '';
	$inp_timezone_offset			= '';
	$jobname						= "Disp and Update Advisor";
	$inp_student01					= '';
	$inp_student02					= '';
	$inp_student03					= '';
	$inp_student04					= '';
	$inp_student05					= '';
	$inp_student06					= '';
	$inp_student07					= '';
	$inp_student08					= '';
	$inp_student09					= '';
	$inp_student10					= '';
	$inp_student11					= '';
	$inp_student12					= '';
	$inp_student13					= '';
	$inp_student14					= '';
	$inp_student15					= '';
	$inp_student16					= '';
	$inp_student17					= '';
	$inp_student18					= '';
	$inp_student19					= '';
	$inp_student20					= '';
	$inp_student21					= '';
	$inp_student22					= '';
	$inp_student23					= '';
	$inp_student24					= '';
	$inp_student25					= '';
	$inp_student26					= '';
	$inp_student27					= '';
	$inp_student28					= '';
	$inp_student29					= '';
	$inp_student30					= '';
	$inp_number_students			= 0;
	$inp_evaluation_complete		= '';
	$inp_class_comments				= '';
	$inp_copy_control				= '';
	$inp_advisor_id					= 0;
	$inp_class_id					= 0;
	$request_table					= '';
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_info") {
				$requestInfo	 = $str_value;
				$requestInfo		 = filter_var($requestInfo,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_table") {
				$inp_table		 = $str_value;
				$inp_table		 = filter_var($inp_table,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_type") {
				$requestType	 = $str_value;
				$requestType		 = filter_var($requestType,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_advisor") {
				$requestType	 = trim($str_value);
				$requestType		 = filter_var($requestType,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "advisorid") {
				$advisorid		 = $str_value;
				$advisorid		 = filter_var($advisorid,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_select_sequence") {
				$inp_select_sequence = $str_value;
				$inp_select_sequence = filter_var($inp_select_sequence,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_first_name") {
				$inp_first_name = $str_value;
				$inp_first_name = filter_var($inp_first_name,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_last_name") {
				$inp_last_name = no_magic_quotes($str_value);
//				$inp_last_name = filter_var($inp_last_name,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_email") {
				$inp_email = $str_value;
				$inp_email = filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_phone") {
				$inp_phone = $str_value;
				$inp_phone = filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_text_message") {
				$inp_text_message = $str_value;
				$inp_text_message = filter_var($inp_text_message,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_city") {
				$inp_city = $str_value;
				$inp_city = filter_var($inp_city,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_state") {
				$inp_state = $str_value;
				$inp_state = filter_var($inp_state,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_zip_code") {
				$inp_zip_code = $str_value;
				$inp_zip_code = filter_var($inp_zip_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_country") {
				$inp_country = $str_value;
				$inp_country = filter_var($inp_country,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_country_code") {
				$inp_country_code = $str_value;
				$inp_country_code = filter_var($inp_country_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_time_zone") {
				$inp_time_zone = $str_value;
				$inp_time_zone = filter_var($inp_time_zone,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_semester") {
				$inp_semester = $str_value;
				$inp_semester = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_survey_score") {
				$inp_survey_score = $str_value;
				$inp_survey_score = filter_var($inp_survey_score,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_languages") {
				$inp_languages = $str_value;
				$inp_languages = filter_var($inp_languages,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_fifo_date") {
				$inp_fifo_date = $str_value;
				$inp_fifo_date = filter_var($inp_fifo_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_welcome_email_date") {
				$inp_welcome_email_date = $str_value;
				$inp_welcome_email_date = filter_var($inp_welcome_email_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_verify_email_date") {
				$inp_verify_email_date = $str_value;
				$inp_verify_email_date = filter_var($inp_verify_email_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_verify_email_number") {
				$inp_verify_email_number = $str_value;
				$inp_verify_email_number = filter_var($inp_verify_email_number,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_verify_response") {
				$inp_verify_response = $str_value;
				$inp_verify_response = filter_var($inp_verify_response,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_action_log") {
				$inp_action_log = $str_value;
			}
			if ($str_key  == "inp_class_verified") {
				$inp_class_verified = $str_value;
				$inp_class_verified = filter_var($inp_class_verified,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_ph_code") {
				$inp_ph_code = $str_value;
				$inp_ph_code = filter_var($inp_ph_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_timezone_id") {
				$inp_timezone_id = $str_value;
				$inp_timezone_id = filter_var($inp_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_timezone_offset") {
				$inp_timezone_offset = $str_value;
				$inp_timezone_offset = filter_var($inp_timezone_offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_whatsapp") {
				$inp_whatsapp = $str_value;
				$inp_whatsapp = filter_var($inp_whatsapp,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_signal") {
				$inp_signal = $str_value;
				$inp_signal = filter_var($inp_signal,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_telegram") {
				$inp_telegram = $str_value;
				$inp_telegram = filter_var($inp_telegram,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_messenger") {
				$inp_messenger = $str_value;
				$inp_messenger = filter_var($inp_messenger,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_replacement_status") {
				$inp_replacement_status = $str_value;
				$inp_replacement_status = filter_var($inp_replacement_status,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_verbose") {
				$inp_verbose = $str_value;
				$inp_verbose = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_table" || $str_key === "inp_pod") {
				$inp_table		 = $str_value;
				$inp_table		 = filter_var($inp_table,FILTER_UNSAFE_RAW);
				if ($inp_table == 'advisor' || $inp_table == 'wpw1_cwa_advisor') {
					$advisorTableName	= 'wpw1_cwa_consolidated_advisor';
					$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
					$advisorDeletedTableName	= 'wpw1_cwa_advisor_deleted';
					$advisorClassDeletedTableName	= 'wpw1_cwa_advisorclass_deleted';
				}elseif ($inp_table == 'advisor2' || $inp_table == 'wpw1_cwa_advisor2') {
					$advisorTableName	= 'wpw1_cwa_consolidated_advisor2';
					$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
					$advisorDeletedTableName	= 'wpw1_cwa_advisor_deleted2';
					$advisorClassDeletedTableName	= 'wpw1_cwa_advisorclass_deleted2';
					$testMode		= TRUE;
				} else {
					echo "<b>ERROR:</b> No table specified!";
				}
			}
			if ($str_key  == "inp_advisor_call_sign") {
				$inp_advisor_call_sign = strtoupper(trim($str_value));
				$inp_advisor_call_sign = filter_var($inp_advisor_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_sequence") {
				$inp_sequence = $str_value;
				$inp_sequence = filter_var($inp_sequence,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_level") {
				$inp_level = trim($str_value);
				$inp_level = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_class_size") {
				$inp_class_size = $str_value;
				$inp_class_size = filter_var($inp_class_size,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_class_schedule_days") {
				$inp_class_schedule_days = $str_value;
				$inp_class_schedule_days = filter_var($inp_class_schedule_days,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_class_schedule_times") {
				$inp_class_schedule_times = $str_value;
				$inp_class_schedule_times = filter_var($inp_class_schedule_times,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_class_schedule_days_utc") {
				$inp_class_schedule_days_utc = $str_value;
				$inp_class_schedule_days_utc = filter_var($inp_class_schedule_days_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_class_schedule_times_utc") {
				$inp_class_schedule_times_utc = $str_value;
				$inp_class_schedule_times_utc = filter_var($inp_class_schedule_times_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_times") {
				$inp_times = $str_value;
				$inp_times = filter_var($inp_times,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_class_incomplete") {
				$inp_class_incomplete = $str_value;
				$inp_class_incomplete = filter_var($inp_class_incomplete,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_advisor_first_name") {
				$inp_advisor_first_name = $str_value;
				$inp_advisor_first_name = filter_var($inp_advisor_first_name,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_advisor_last_name") {
				$inp_advisor_last_name = no_magic_quotes($str_value);
			}
			if ($str_key  == "inp_recdel") {
				$inp_recdel = $str_value;
				$inp_recdel = filter_var($inp_recdel,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_student01") {
				$inp_student01 = trim($str_value);
				$inp_student01 = strtoupper(filter_var($inp_student01,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student02") {
				$inp_student02 = trim($str_value);
				$inp_student02 = strtoupper(filter_var($inp_student02,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student03") {
				$inp_student03 = trim($str_value);
				$inp_student03 = strtoupper(filter_var($inp_student03,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student04") {
				$inp_student04 = trim($str_value);
				$inp_student04 = strtoupper(filter_var($inp_student04,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student05") {
				$inp_student05 = trim($str_value);
				$inp_student05 = strtoupper(filter_var($inp_student05,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student06") {
				$inp_student06 = trim($str_value);
				$inp_student06 = strtoupper(filter_var($inp_student06,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student07") {
				$inp_student07 = trim($str_value);
				$inp_student07 = strtoupper(filter_var($inp_student07,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student08") {
				$inp_student08 = trim($str_value);
				$inp_student08 = strtoupper(filter_var($inp_student08,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student09") {
				$inp_student09 = trim($str_value);
				$inp_student09 = strtoupper(filter_var($inp_student09,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student10") {
				$inp_student10 = trim($str_value);
				$inp_student10 = strtoupper(filter_var($inp_student10,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student11") {
				$inp_student11 = trim($str_value);
				$inp_student11 = strtoupper(filter_var($inp_student11,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student12") {
				$inp_student12 = trim($str_value);
				$inp_student12 = strtoupper(filter_var($inp_student12,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student13") {
				$inp_student13 = trim($str_value);
				$inp_student13 = strtoupper(filter_var($inp_student13,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student14") {
				$inp_student14 = trim($str_value);
				$inp_student14 = strtoupper(filter_var($inp_student14,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student15") {
				$inp_student15 = trim($str_value);
				$inp_student15 = strtoupper(filter_var($inp_student15,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student16") {
				$inp_student16 = trim($str_value);
				$inp_student16 = strtoupper(filter_var($inp_student16,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student17") {
				$inp_student17 = trim($str_value);
				$inp_student17 = strtoupper(filter_var($inp_student17,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student18") {
				$inp_student18 = trim($str_value);
				$inp_student18 = strtoupper(filter_var($inp_student18,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student19") {
				$inp_student19 = trim($str_value);
				$inp_student19 = strtoupper(filter_var($inp_student19,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student20") {
				$inp_student20 = trim($str_value);
				$inp_student20 = strtoupper(filter_var($inp_student20,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student21") {
				$inp_student21 = trim($str_value);
				$inp_student21 = strtoupper(filter_var($inp_student21,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student22") {
				$inp_student22 = trim($str_value);
				$inp_student22 = strtoupper(filter_var($inp_student22,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student23") {
				$inp_student23 = trim($str_value);
				$inp_student23 = strtoupper(filter_var($inp_student23,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student24") {
				$inp_student24 = trim($str_value);
				$inp_student24 = strtoupper(filter_var($inp_student24,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student25") {
				$inp_student25 = trim($str_value);
				$inp_student25 = strtoupper(filter_var($inp_student25,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student26") {
				$inp_student26 = trim($str_value);
				$inp_student26 = strtoupper(filter_var($inp_student26,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student27") {
				$inp_student27 = trim($str_value);
				$inp_student27 = strtoupper(filter_var($inp_student27,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student28") {
				$inp_student28 = trim($str_value);
				$inp_student28 = strtoupper(filter_var($inp_student28,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student29") {
				$inp_student29 = trim($str_value);
				$inp_student29 = strtoupper(filter_var($inp_student29,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_student30") {
				$inp_student30 = trim($str_value);
				$inp_student30 = strtoupper(filter_var($inp_student30,FILTER_UNSAFE_RAW));
			}
			if ($str_key  == "inp_number_students") {
				$inp_number_students = $str_value;
				$inp_number_students = filter_var($inp_number_students,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_evaluation_complete") {
				$inp_evaluation_complete = $str_value;
				$inp_evaluation_complete = filter_var($inp_evaluation_complete,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_class_comments") {
				$inp_class_comments = $str_value;
				$inp_class_comments = filter_var($inp_class_comments,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_copy_control") {
				$inp_copy_control = $str_value;
				$inp_copy_control = filter_var($inp_copy_control,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_class_id") {
				$inp_class_id = $str_value;
				$inp_class_id = filter_var($inp_class_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_advisor_id") {
				$inp_advisor_id = $str_value;
				$inp_advisor_id = filter_var($inp_advisor_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "request_table") {
				$inp_table = $str_value;
				$inp_table = filter_var($inp_table,FILTER_UNSAFE_RAW);
			}
		}
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
		$content		.= "<p><b>Operating in testMode</b></p>";
	}
	
	
/*
 * When strPass is equal to 1 then get the information needed to access the advisor
 * The advisorcan be accessed by the advisorID, call sign, surname, or email
 *
*/
	if ("1" == $strPass) {
	
		$content .= "<p>Please select the type of request and enter the value to be searched 
					in the AdvisorNew table. Call sign can be either upper case or lower case. Last name must be 
					an exact match.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data''>
					<input type='hidden' name='strpass' value='2'>
					<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Request Type</td>
						<td><input class='formInputButton' type='radio' name='request_type' value='callsign' checked>Call Sign<br />
							<input class='formInputButton' type='radio' name='request_type' value='advisorid'>AdvisorID<br />
							<input class='formInputButton' type='radio' name='request_type' value='surname'>Surname<br />
							<input class='formInputButton' type='radio' name='request_type' value='givenname'>Given Name<br />
							<input class='formInputButton' type='radio' name='request_type' value='email'>Email</td></tr>
					<tr><td>RequestInfo</td>
						<td><input class='formInputText' type='text' maxlength='30' name='request_info' size='10' autofocus ></td></tr>
					<tr><td>Which Table</td>
						<td><input class='formInputButton' type='radio' name='inp_table' value='advisor' checked> Advisor<br />
							<input class='formInputButton' type='radio' name='inp_table' value='advisor2'> Advisor2</td></tr>
					<tr><td>Verbose Output?</td>
						<td><input class='formInputButton' type='radio' name='inp_verbose' value='n' checked> Normal Output<br />
							<input class='formInputButton' type='radio' name='inp_verbose' value='y'> Verbose Output</td></tr>
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
					</form>";


///////		Pass 2

	} elseif ("2" == $strPass) {
	
		if ($requestType == "callsign") {
			$requestInfo = strtoupper($requestInfo);
		}
		if ($doDebug) {
			echo "Supplied input: Request Type: $requestType; Request Info: $requestInfo inp_table: $inp_table<br />";
		}
//		if ($inp_table != '') {
//			$advisorTableName	= $inp_table;
//			if ($inp_table == 'wpw1_cwa_advisor') {
//				$advisorClassTableName	= 'wpw1_cwa_advisorclass';
//			} elseif ($inp_table == 'wpw1_cwa_advisor2') {
//				$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
//			}
//		}

// Set up the data request
		$goOn					= TRUE;
		if ($requestType == "callsign") {
			$sql		= "select * from $advisorTableName 
							where call_sign='$requestInfo' 
							order by date_created DESC";
		} elseif ($requestType == "advisorid") {
			$sql		= "select * from $advisorTableName 
							where advisor_id='$requestInfo' 
							order by date_created DESC";
		} elseif ($requestType == "surname") {	
			$myInt	= strpos($requestInfo,"'");
			if ($myInt !== FALSE) {
				$new_info	= substr($requestInfo,$myInt+1);
				$new_info	= "%$new_info%";
				if ($doDebug) {
					echo "requestInfo has an apostrophe. Searching for $new_info<br />";
				}
			} else {
				$new_info	= "%$requestInfo%";
			}
			$sql		= "select * from $advisorTableName 
							where last_name like '%$new_info%' 
							order by date_created DESC";
		} elseif ($requestType == "givenname") {	
			$sql		= "select * from $advisorTableName 
							where first_name like '%$requestInfo%' 
							order by date_created DESC";
		} elseif ($requestType == "email") {
			$sql		= "select * from $advisorTableName 
							where email='$requestInfo' order by 
							date_created DESC";
		} else {
			$content			.= "Hmmm ... requestType of $requestType didn't compute<br />";
			$goOn				= FALSE;
		}
		if ($goOn) {
			$tableCount 							= 0;	
			$content			.= "<h3>Display and Update Advisor $requestInfo</h3>";

			$wpw1_cwa_advisor			= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				$myError			= $wpdb->last_error;
				$myQuery			= $wpdb->last_query;
				if ($doDebug) {
					echo "Reading $advisorTableName table failed<br />
						  wpdb->last_query: $myQuery<br />
						  wpdb->last_error: $myError<br />";
				}
				$errorMsg			= "$jobname Pass 2 Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
				sendErrorEmail($errorMsg);
			} else {
				$numARows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$advisor_ID							= $advisorRow->advisor_id;
						$advisor_select_sequence 			= $advisorRow->select_sequence;
						$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
						$advisor_first_name 				= $advisorRow->first_name;
						$advisor_last_name 					= stripslashes($advisorRow->last_name);
						$advisor_email 						= strtolower($advisorRow->email);
						$advisor_phone						= $advisorRow->phone;
						$advisor_ph_code					= $advisorRow->ph_code;				// new
						$advisor_text_message 				= $advisorRow->text_message;
						$advisor_city 						= $advisorRow->city;
						$advisor_state 						= $advisorRow->state;
						$advisor_zip_code 					= $advisorRow->zip_code;
						$advisor_country 					= $advisorRow->country;
						$advisor_country_code				= $advisorRow->country_code;		// new
						$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
						$advisor_signal						= $advisorRow->signal_app;			// new
						$advisor_telegram					= $advisorRow->telegram_app;		// new
						$advisor_messenger					= $advisorRow->messenger_app;		// new
						$advisor_time_zone 					= $advisorRow->time_zone;
						$advisor_timezone_id				= $advisorRow->timezone_id;			// new
						$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
						$advisor_semester 					= $advisorRow->semester;
						$advisor_survey_score 				= $advisorRow->survey_score;
						$advisor_languages 					= $advisorRow->languages;
						$advisor_fifo_date 					= $advisorRow->fifo_date;
						$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
						$advisor_verify_email_date 			= $advisorRow->verify_email_date;
						$advisor_verify_email_number 		= $advisorRow->verify_email_number;
						$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
						$advisor_action_log 				= $advisorRow->action_log;
						$advisor_class_verified 			= $advisorRow->class_verified;
						$advisor_control_code 				= $advisorRow->control_code;
						$advisor_date_created 				= $advisorRow->date_created;
						$advisor_date_updated 				= $advisorRow->date_updated;
						$advisor_replacement_status 		= $advisorRow->replacement_status;

						$advisor_last_name 					= no_magic_quotes($advisor_last_name);
						
						$newActionLog		= formatActionLog($advisor_action_log);

						$tableCount++;
						$content .= "<b>Record $tableCount</b> Table $advisorTableName
									<p><a href='$theURL'>Display another advisor</a></p>	
									<table style='border-collapse:collapse;'>
									<tr><th style='width:200px;'>Field</th><th>Value</th></tr>
									<tr><td>AdvisorID</td><td><a href='$theURL?advisorid=$advisor_ID&inp_table=$inp_table&strpass=3&inp_verbose=$inp_verbose'>$advisor_ID</a></td></tr>
									<tr><td>Select Sequence</td><td>$advisor_select_sequence</td></tr>
									<tr><td>Call Sign</td><td>$advisor_call_sign</td></tr>
									<tr><td>Last Name</td><td>$advisor_last_name</td></tr>
									<tr><td>First Name</td><td>$advisor_first_name</td></tr>
									<tr><td>Email</td><td>$advisor_email</td></tr>
									<tr><td>Phone Code</td><td>$advisor_ph_code</td></tr>
									<tr><td>Phone</td><td>$advisor_phone</td></tr>
									<tr><td>Text Message</td><td>$advisor_text_message</td></tr>
									<tr><td>City</td><td>$advisor_city</td></tr>
									<tr><td>State</td><td>$advisor_state</td></tr>
									<tr><td>Zip Code</td><td>$advisor_zip_code</td></tr>
									<tr><td>Country</td><td>$advisor_country</td></tr>
									<tr><td>Country Code</td><td>$advisor_country_code</td></tr>
									<tr><td>Timezone ID</td><td>$advisor_timezone_id</td></tr>
									<tr><td>Timezone_offset</td><td>$advisor_timezone_offset</td></tr>
									<tr><td>Whatsapp</td><td>$advisor_whatsapp</td></tr>
									<tr><td>Signal</td><td>$advisor_signal</td></tr>
									<tr><td>Telegram</td><td>$advisor_telegram</td></tr>
									<tr><td>Messenger</td><td>$advisor_messenger</td></tr>
									<tr><td>Semester</td><td>$advisor_semester</td></tr>
									<tr><td>Survey Score</td><td>$advisor_survey_score</td></tr>
									<tr><td>Languages</td><td>$advisor_languages</td></tr>
									<tr><td>FIFO Date</td><td>$advisor_fifo_date</td></tr>
									<tr><td>Welcome Email Date</td><td>$advisor_welcome_email_date</td></tr>
									<tr><td>Verify Email Date</td><td>$advisor_verify_email_date</td></tr>
									<tr><td>Verify Email Number</td><td>$advisor_verify_email_number</td></tr>
									<tr><td>Verify Response</td><td>$advisor_verify_response</td></tr>
									<tr><td style='vertical-align:top;'>Action Log</td><td>$newActionLog</td></tr>
									<tr><td>Class Verified</td><td>$advisor_class_verified</td></tr>
									<tr><td>Replacement Status</td><td>$advisor_replacement_status</td></tr>
									<tr><td>Date Created</td><td>$advisor_date_created</td></tr>
									<tr><td>Date Updated</td><td>$advisor_date_updated</td></tr>
									</table>
									To update the advisor info, click on the advisorID, or click 
									<a href='$theURL?advisorid=$advisor_ID&inp_table=$inp_table&strpass=3&inp_verbose=$inp_verbose'>HERE</a><br /><br />";
					
						// now display all the advisorClass records for this advisor / semester
						$sql			= "select * from $advisorClassTableName 
											where advisor_call_sign = '$advisor_call_sign' 
												and semester='$advisor_semester' 
											order by sequence";
						$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
						if ($wpw1_cwa_advisorclass === FALSE) {
							$myError			= $wpdb->last_error;
							$myQuery			= $wpdb->last_query;
							if ($doDebug) {
								echo "Reading $advisorClassTableName table failed<br />
									  wpdb->last_query: $myQuery<br />
									  wpdb->last_error: $myError<br />";
							}
							$errorMsg			= "$jobname pass 2 Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
							sendErrorEmail($errorMsg);
						} else {
							$numACRows						= $wpdb->num_rows;
							if ($doDebug) {
								$myStr						= $wpdb->last_query;
								echo "ran $myStr<br />and found $numACRows rows<br />";
							}
							if ($numACRows > 0) {
								foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
									$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
									$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
									$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
									$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
									$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
									$advisorClass_sequence 					= $advisorClassRow->sequence;
									$advisorClass_semester 					= $advisorClassRow->semester;
									$advisorClass_timezone 					= $advisorClassRow->time_zone;
									$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
									$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
									$advisorClass_level 					= $advisorClassRow->level;
									$advisorClass_class_size 				= $advisorClassRow->class_size;
									$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
									$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
									$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
									$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
									$advisorClass_action_log 				= $advisorClassRow->action_log;
									$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
									$advisorClass_date_created				= $advisorClassRow->date_created;
									$advisorClass_date_updated				= $advisorClassRow->date_updated;
									$advisorClass_student01 				= $advisorClassRow->student01;
									$advisorClass_student02 				= $advisorClassRow->student02;
									$advisorClass_student03 				= $advisorClassRow->student03;
									$advisorClass_student04 				= $advisorClassRow->student04;
									$advisorClass_student05 				= $advisorClassRow->student05;
									$advisorClass_student06 				= $advisorClassRow->student06;
									$advisorClass_student07 				= $advisorClassRow->student07;
									$advisorClass_student08 				= $advisorClassRow->student08;
									$advisorClass_student09 				= $advisorClassRow->student09;
									$advisorClass_student10 				= $advisorClassRow->student10;
									$advisorClass_student11 				= $advisorClassRow->student11;
									$advisorClass_student12 				= $advisorClassRow->student12;
									$advisorClass_student13 				= $advisorClassRow->student13;
									$advisorClass_student14 				= $advisorClassRow->student14;
									$advisorClass_student15 				= $advisorClassRow->student15;
									$advisorClass_student16 				= $advisorClassRow->student16;
									$advisorClass_student17 				= $advisorClassRow->student17;
									$advisorClass_student18 				= $advisorClassRow->student18;
									$advisorClass_student19 				= $advisorClassRow->student19;
									$advisorClass_student20 				= $advisorClassRow->student20;
									$advisorClass_student21 				= $advisorClassRow->student21;
									$advisorClass_student22 				= $advisorClassRow->student22;
									$advisorClass_student23 				= $advisorClassRow->student23;
									$advisorClass_student24 				= $advisorClassRow->student24;
									$advisorClass_student25 				= $advisorClassRow->student25;
									$advisorClass_student26 				= $advisorClassRow->student26;
									$advisorClass_student27 				= $advisorClassRow->student27;
									$advisorClass_student28 				= $advisorClassRow->student28;
									$advisorClass_student29 				= $advisorClassRow->student29;
									$advisorClass_student30 				= $advisorClassRow->student30;
									$class_number_students					= $advisorClassRow->number_students;
									$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
									$class_comments							= $advisorClassRow->class_comments;
									$copycontrol							= $advisorClassRow->copy_control;

									$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
						
									$newActionLog							= formatActionLog($advisorClass_action_log);
						
									// Display this record
									$content		.= "<p><b>Advisor Class $advisorClass_sequence:</b></p>
														<table style='border-collapse:collapse;'>
														<tr><th style='width:200px;'>Field</th><th>Value</th></tr>
														<tr><td>Advisor Class ID</td>
															<td><a href='$theURL?inp_class_id=$advisorClass_ID&inp_table=$inp_table&strpass=5&inp_verbose=$inp_verbose'>$advisorClass_ID</a></td></tr>
														<tr><td>Advisor Class Call Sign</td>
															<td>$advisorClass_advisor_call_sign</td></tr>
														<tr><td>Advisor Record ID</td>
															<td>$advisorClass_advisor_id</td></tr>
														<tr><td>AdvisorClass Advisor First Name</td>
															<td>$advisorClass_advisor_first_name</td></tr>
														<tr><td>AdvisorClass Advisor Last Name</td>
															<td>$advisorClass_advisor_last_name</td></tr>
														<tr><td>advisorClass Sequence</td>
															<td>$advisorClass_sequence</td></tr>
														<tr><td>AdvisorClass Semester</td>
															<td>$advisorClass_semester</td></tr>
														<tr><td>AdvisorClass Timezone</td>
															<td>$advisorClass_timezone</td></tr>
														<tr><td>AdvisorClass Timezone ID</td>
															<td>$advisorClass_timezone_id</td></tr>
														<tr><td>AdvisorClass Timezone Offset</td>
															<td>$advisorClass_timezone_offset</td></tr>
														<tr><td>AdvisorClass Level</td>
															<td>$advisorClass_level</td></tr>
														<tr><td>AdvisorClass Class Size</td>
															<td>$advisorClass_class_size</td></tr>
														<tr><td>Action Log</td>
															<td>$newActionLog</td></tr>
														<tr><td>AdvisorClass Class Schedule Days</td>
															<td>$advisorClass_class_schedule_days</td></tr>
														<tr><td>AdvisorClass Class Schedule Times</td>
															<td>$advisorClass_class_schedule_times</td></tr>
														<tr><td>AdvisorClass Class Schedule Days UTC</td>
															<td>$advisorClass_class_schedule_days_utc</td></tr>
														<tr><td>AdvisorClass Class Schedule Times UTC</td>
															<td>$advisorClass_class_schedule_times_utc</td></tr>
														<tr><td colspan='2'><table>
															<tr><td>Student01: $advisorClass_student01</td>
																<td>Student02: $advisorClass_student02</td>
																<td>Student03: $advisorClass_student03</td></tr>
															<tr><td>Student04: $advisorClass_student04</td>
																<td>Student05: $advisorClass_student05</td>
																<td>Student06: $advisorClass_student06</td></tr>
															<tr><td>Student07: $advisorClass_student07</td>
																<td>Student08: $advisorClass_student08</td>
																<td>Student09: $advisorClass_student09</td></tr>
															<tr><td>Student10: $advisorClass_student10</td>
																<td>Student11: $advisorClass_student11</td>
																<td>Student12: $advisorClass_student12</td></tr>
															<tr><td>Student13: $advisorClass_student13</td>
																<td>Student14: $advisorClass_student14</td>
																<td>Student15: $advisorClass_student15</td></tr>
															<tr><td>Student16: $advisorClass_student16</td>
																<td>Student17: $advisorClass_student17</td>
																<td>Student18: $advisorClass_student18</td></tr>
															<tr><td>Student19: $advisorClass_student19</td>
																<td>Student20: $advisorClass_student20</td>
																<td>Student21: $advisorClass_student21</td></tr>
															<tr><td>Student22: $advisorClass_student22</td>
																<td>Student23: $advisorClass_student23</td>
																<td>Student24: $advisorClass_student24</td></tr>
															<tr><td>Student25: $advisorClass_student25</td>
																<td>Student26: $advisorClass_student26</td>
																<td>Student27: $advisorClass_student27</td></tr>
															<tr><td>Student28: $advisorClass_student28</td>
																<td>Student29: $advisorClass_student29</td>
																<td>Student30: $advisorClass_student30</td></tr>
																</table></td></tr>
														<tr><td>Number Students</td>
															<td>$class_number_students</td></tr>
														<tr><td>Evaluation Complete</td>
															<td>$class_evaluation_complete</td></tr>
														<tr><td>Class Comments</td>
															<td>$class_comments</td></tr>
														<tr><td>Copy Control</td>
															<td>$copycontrol</td></tr>
														<fe><td>Date Created</td>
															<td>$advisorClass_date_created</td></tr>
														<tr><td>Date Updated</td>
															<td>$advisorClass_date_updated</td></tr>
														</table>
														To update the advisorClass info, click on the advisorClass_ID, or click 
														<a href='$theURL?inp_class_id=$advisorClass_ID&inp_table=$inp_table&strpass=5&inp_verbose=$inp_verbose'>Update Class Info</a><br />";

								}		// end of the while for advisorClass
								$myInt		= $advisorClass_sequence + 1;
								$content	.= "<br />To add another advisorClass record, please click 					
												<a href='$theURL?inp_advisor_id=$advisor_ID&inp_sequence=$myInt&inp_table=$inp_table&strpass=10'>Add Advisor Class</a><br /><br />";
							} else {
								$content	.= "<p>No advisorClass records found.</p>";
							}
						}
					}						
				} else {
					$content	.= "No record found  in $advisorTableName table for $requestInfo<br />";
				}
			}
		}

	} elseif ("3" == $strPass) {

// display the request record to be modified
		$sql				= "select * from $advisorTableName where advisor_id='$advisorid'";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname  pass 3 Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;
					$advisor_replacement_status 		= $advisorRow->replacement_status;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);
			
				
					$content	.= "<h3>Display and Update Advisor $advisor_call_sign</h3>
									<p><a href='$theURL'>Display another advisor</a></p>
									<p>Table: $advisorTableName</p>
									<form method='post' name='selection_form' action='$theURL' 
									ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='4'>
									<input type='hidden' name='inp_table' value='$inp_table'>
									<input type='hidden' name='advisorid' value='$advisor_ID'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<p>To delete this entire sign-up record, check the 
									'Delete this record' box below. The advisor and class records will be deleted. Otherwise, 
									make any needed changes.<br />
									<input type='checkbox' class='formInputText' name='inp_recdel' value='recdel'> Delete this record</p>
									<table style='border-collapse:collapse;'>
									<tr><th style='width:200px;'>Field</th>
										<th>Value</th></tr>";
					if (in_array($userName,$validTestmode)) {
						$content	.= "<tr><td>Select Sequence</td>
											<td><input class='formInputText' type='text' name='inp_select_sequence' size='10' maxlenth='10' value='$advisor_select_sequence'></td></tr>";
					}
					$content		.= "<tr><td>Call Sign</td>
											<td><input class='formInputText' type='text' name='inp_advisor_call_sign' size='15' maxlength='15' value='$advisor_call_sign'></td></tr>
										<tr><td>Last Name</td>
											<td><input class='formInputText' type='text' name='inp_last_name' size='50' maxlenth='100' value=\"$advisor_last_name\"></td></tr>
										<tr><td>First Name</td>
											<td><input class='formInputText' type='text' name='inp_first_name' size='50' maxlength='100' value='$advisor_first_name'></td></tr>
										<tr><td>Email</td>
											<td><input class='formInputText' type='text' name='inp_email' size='50' maxlenth='100' value='$advisor_email'></td></tr>
										<tr><td>Phone Code</td>
											<td><input class='formInputText' type='text' name='inp_ph_code' size='5' maxlenth='55' value='$advisor_ph_code'></td></tr>
										<tr><td>Phone</td>
											<td><input class='formInputText' type='text' name='inp_phone' size='20' maxlenth='50' value='$advisor_phone'></td></tr>
										<tr><td>Text Message</td>
											<td><input class='formInputText' type='text' name='inp_text_message' size='5' maxlenth='5'value='$advisor_text_message'></td></tr>
										<tr><td>City</td>
											<td><input class='formInputText' type='text' name='inp_city' size='30' maxlenth='30'value='$advisor_city'></td></tr>
										<tr><td>State</td>
											<td><input class='formInputText' type='text' name='inp_state' size='30' maxlength='30' value='$advisor_state'></td></tr>
										<tr><td>Zip Code</td>
											<td><input class='formInputText' type='text' name='inp_zip_code' size='15' maxlength='15' value='$advisor_zip_code'></td></tr>
										<tr><td>Country</td>
											<td><input class='formInputText' type='text' name='inp_country' size='30' maxlength='30' value='$advisor_country'></td></tr>
										<tr><td>Country Code</td>
											<td><input class='formInputText' type='text' name='inp_country_code' size='5' maxlength='5' value='$advisor_country_code'></td></tr>
										<tr><td>Timezone ID</td>
											<td><input class='formInputText' type='text' name='inp_timezone_id' size='50' maxlength='50' value='$advisor_timezone_id'></td></tr>
										<tr><td>Timezone Offset</td>
											<td><input class='formInputText' type='text' name='inp_timezone_offset' size='6' maxlength='6' value='$advisor_timezone_offset'></td></tr>
										<tr><td>Whatsapp</td>
											<td><input class='formInputText' type='text' name='inp_whatsapp' size='20' maxlength='20' value='$advisor_whatsapp'></td></tr>
										<tr><td>Signal</td>
											<td><input class='formInputText' type='text' name='inp_signal' size='20' maxlength='20' value='$advisor_signal'></td></tr>
										<tr><td>Telegram</td>
											<td><input class='formInputText' type='text' name='inp_telegram' size='20' maxlength='20' value='$advisor_telegram'></td></tr>
										<tr><td>Messenger</td>
											<td><input class='formInputText' type='text' name='inp_messenger' size='20' maxlength='20' value='$advisor_messenger'></td></tr>
										<tr><td>Semester</td>
											<td><input class='formInputText' type='text' name='inp_semester' size='15' maxlength='51' value='$advisor_semester'></td></tr>
										<tr><td>Survey Score</td>
											<td><input class='formInputText' type='text' name='inp_survey_score' size='5' maxlength='5' value='$advisor_survey_score'></td></tr>
										<tr><td>Languages</td>
											<td><input class='formInputText' type='text' name='inp_languages' size='30' maxlength='50 'value='$advisor_languages'></td></tr>
										<tr><td>FIFO Date</td>
											<td><input class='formInputText' type='text' name='inp_fifo_date' size='15' maxlength='15'value='$advisor_fifo_date'></td></tr>
										<tr><td>Welcome Email Date</td>
											<td><input class='formInputText' type='text' name='inp_welcome_email_date' size='15' maxlength='15'value='$advisor_welcome_email_date'></td></tr>
										<tr><td>Verify Email Date</td>
											<td><input class='formInputText' type='text' name='inp_verify_email_date' size='15' maxlength='15'value='$advisor_verify_email_date'></td></tr>
										<tr><td>Verify Email Number</td>
											<td><input class='formInputText' type='text' name='inp_verify_email_number' size='5' maxlength='5' value='$advisor_verify_email_number'></td></tr>
										<tr><td>Verify Response</td>
											<td><input class='formInputText' type='text' name='inp_verify_response' size='5' maxlenth='5' value='$advisor_verify_response'></td></tr>
										<tr><td>Action Log</td>
											<td><textarea rows='4' cols='50' name='inp_action_log' class='formInputText'>$advisor_action_log</textarea></td></tr>
										<tr><td>Class Verified</td>
											<td><input class='formInputText' type='text' name='inp_class_verified' size='5' maxlenth='5' value='$advisor_class_verified'></td></tr>
										<tr><td>Replacement Status</td>
											<td><input class='formInputText' type='text' name='inp_replacement_status' size='5' maxlenth='5' value='$advisor_replacement_status'></td></tr>
										<tr><td>&nbsp;</td>
											<td><input class='formInputButton' type='submit' value='Submit' /></td></tr>
										</table></form>
										<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisor_call_sign&inp_table=$inp_table&strpass=2'>HERE</a></p><br />"; 

				}			// end of the advisor while
			} else {
				$content	.= "<p>No record found in $advisorTableName for the record with the id of $advisorid</p>";
			}
		}


////////  Pass 4 update advisor fields

	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "arrived at pass 4 with advisor_id of $advisorid to update<br />";
		}
		$actionContent			= "";
		$sql					= "select * from $advisorTableName where advisor_id='$advisorid'";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname pass 4 Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;
					$advisor_replacement_status 		= $advisorRow->replacement_status;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);
				}
				
				if ($inp_recdel == 'recdel') {			// delete the advisor and class records
					if ($doDebug) {
						echo "Advisor and class records were requested to be deleted<br />";
					}
					//// first move the record to the deleted table
					$sql		= "insert into $advisorDeletedTableName 
									select * from $advisorTableName 
									where advisor_id = $advisor_ID ";
					$myResult	= $wpdb->get_results($sql);
					if ($myResult === FALSE) {
						$myError			= $wpdb->last_error;
						$myQuery			= $wpdb->last_query;
						if ($doDebug) {
							echo "adding $advisor_call_sign to $advisorDeletedTableName table failed<br />
								  wpdb->last_query: " . $wpdb->last_query . "<br />
								  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
						}
						sendErrorEmail("$jobname pass 4 attempting to delete $advisor_call_sign from $advisorTableName failed.<br />SQL: $myQuery<br />ErrorL $myError");
					}
					if (sizeof($myResult) != 0 || $myResult === FALSE) {
						$myStr				= $wpdb->last_error;
						sendErrorEmail("Display and Update Advisor: attempting to move $advisor_call_sign from $advisorTableName to $advisorDeletedTablename failed. Last error: $myStr");
						$content .= "<p>The deletion was not successful. Notify the developer</p>";
					} else {
						//// then, if the move was successful, delete the record
						$myResult	= $wpdb->delete($advisorTableName,
													array('advisor_id'=>$advisor_ID),
													array('%d'));
						if ($myResult === FALSE) {
							$myError			= $wpdb->last_error;
							$myQuery			= $wpdb->last_query;
							if ($doDebug) {
								echo "deleting ID $advisor_ID from $advisorTableName table failed<br />
									  wpdb->last_query: $myQuery<br />
									  <b>wpdb->last_error:</b> $myError<br />";
							}
						} else {
							$content .= "<p>The deletion of the advisor record was successful.</p>";
							$content			.= "<p>Advisor record for $advisor_call_sign has been deleted.</p>";
							// write advisor audit log record
							if ($testMode) {
								$log_mode		= 'testMode';
								$log_file		= 'TestAdvisor';
							} else {
								$log_mode		= 'Production';
								$log_file		= 'Advisor';
							}
							$submitArray		= array('logtype'=>$log_file,
														'logmode'=>$log_mode,
														'logaction'=>'DELETE',
														'logsubtype'=>'Advisor',
														'logdate'=>$logDate,
														'logprogram'=>'ADVUPDATE',
														'logsemester'=>$advisor_semester,
														'logwho'=>$userName,
														'logid'=>$advisor_ID,
														'logcallsign'=>$advisor_call_sign,
														'call_sign'=>$advisor_call_sign,
														'first_name'=>$advisor_first_name,
														'last_name'=>$advisor_last_name);
							if ($doDebug){
								echo "submitArray:<br />";
								foreach($submitArray as $myKey=>$myValue) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;$myKey = $myValue<br />";
								}
							}
							$result		= storeAuditLogData_v3($submitArray);
							if ($result[0] === FALSE) {
								if ($doDebug) {
									echo "storeAuditLogData failed: $result[1]<br />";
								}
							}
							// now delete all the class records for this advisor / semester
							$sql					= "select * from $advisorClassTableName 
														where advisor_call_sign='$advisor_call_sign' 
														and semester = '$advisor_semester'";
							$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
							if ($wpw1_cwa_advisorclass === FALSE) {
								$myError			= $wpdb->last_error;
								$myQuery			= $wpdb->last_query;
								if ($doDebug) {
									echo "Reading $advisorClassTableName table failed<br />
										  wpdb->last_query: $myQuery<br />
										  wpdb->last_error: $myError<br />";
								}
								$errorMsg			= "$jobname pass 4 Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
								sendErrorEmail($errorMsg);
							} else {
								$numACRows						= $wpdb->num_rows;
								if ($doDebug) {
									$myStr						= $wpdb->last_query;
									echo "ran $myStr<br />and found $numACRows rows<br />";
								}
								if ($numACRows > 0) {
									foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
										$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
										$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
										$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
										$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
										$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
										$advisorClass_sequence 					= $advisorClassRow->sequence;
										$advisorClass_semester 					= $advisorClassRow->semester;
										$advisorClass_timezone 					= $advisorClassRow->time_zone;
										$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
										$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
										$advisorClass_level 					= $advisorClassRow->level;
										$advisorClass_class_size 				= $advisorClassRow->class_size;
										$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
										$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
										$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
										$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
										$advisorClass_action_log 				= $advisorClassRow->action_log;
										$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
										$advisorClass_date_created				= $advisorClassRow->date_created;
										$advisorClass_date_updated				= $advisorClassRow->date_updated;
										$advisorClass_student01 				= $advisorClassRow->student01;
										$advisorClass_student02 				= $advisorClassRow->student02;
										$advisorClass_student03 				= $advisorClassRow->student03;
										$advisorClass_student04 				= $advisorClassRow->student04;
										$advisorClass_student05 				= $advisorClassRow->student05;
										$advisorClass_student06 				= $advisorClassRow->student06;
										$advisorClass_student07 				= $advisorClassRow->student07;
										$advisorClass_student08 				= $advisorClassRow->student08;
										$advisorClass_student09 				= $advisorClassRow->student09;
										$advisorClass_student10 				= $advisorClassRow->student10;
										$advisorClass_student11 				= $advisorClassRow->student11;
										$advisorClass_student12 				= $advisorClassRow->student12;
										$advisorClass_student13 				= $advisorClassRow->student13;
										$advisorClass_student14 				= $advisorClassRow->student14;
										$advisorClass_student15 				= $advisorClassRow->student15;
										$advisorClass_student16 				= $advisorClassRow->student16;
										$advisorClass_student17 				= $advisorClassRow->student17;
										$advisorClass_student18 				= $advisorClassRow->student18;
										$advisorClass_student19 				= $advisorClassRow->student19;
										$advisorClass_student20 				= $advisorClassRow->student20;
										$advisorClass_student21 				= $advisorClassRow->student21;
										$advisorClass_student22 				= $advisorClassRow->student22;
										$advisorClass_student23 				= $advisorClassRow->student23;
										$advisorClass_student24 				= $advisorClassRow->student24;
										$advisorClass_student25 				= $advisorClassRow->student25;
										$advisorClass_student26 				= $advisorClassRow->student26;
										$advisorClass_student27 				= $advisorClassRow->student27;
										$advisorClass_student28 				= $advisorClassRow->student28;
										$advisorClass_student29 				= $advisorClassRow->student29;
										$advisorClass_student30 				= $advisorClassRow->student30;
										$class_number_students					= $advisorClassRow->number_students;
										$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
										$class_comments							= $advisorClassRow->class_comments;
										$copycontrol							= $advisorClassRow->copy_control;

										$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
								
										if ($doDebug) {
											echo "Got a class record sequence $advisorClass_sequence to delete<br />";
										}
										/// first copy the record to the deleted table
										$sql		= "insert into $advisorClassDeletedTableName 
														select * from $advisorClassTableName 
														where advisorclass_id = $advisorClass_ID";
										$myResult	= $wpdb->get_results($sql);
										if ($myResult === FALSE) {
											$myQuery		= $wpdb->last_query;
											$myError		= $wpdb->last_error;
											if ($doDebug) {
												echo "copying $advisorClass_call_sign from $advisorClassTableName to $advisorClassDeletedTableName failed<br />
													  wpdb->last_query: $myQuery<br />
													  <b>wpdb->last_error: </b>$myError<br />";
											}
											sendErrorEmail("$jobname pass 4 attempting to move $advisor_call_sign classes to deleted table failed.<br />SQL: $myQuery<br />Error: $myError");
										}
										if (sizeof($myResult) != 0 || $myResult === FALSE) {
											$myStr			= $wpdb->last_error;
											sendErrorEmail("$jobname pass 4 attempting to copy $advisorClass_call_sign sequence $advisorClass_sequence 
class record to $advisorClassDeletedTableName failed. Last error: $myStr");
										} else {
											$myResult	= $wpdb->delete($advisorClassTableName,array('advisorclass_id'=>$advisorClass_ID),array('%d'));
											if ($doDebug) {
												echo "deleting ID $advisorClass_ID from $advisorClassTableName table<br />";
												echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
												if ($wpdb->last_error != '') {
													echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
												}
											}
											if ($myResult === FALSE) {
												$myStr			= $wpdb->last_error;
												sendErrorEmail("$jobname pass 4 attempting to delete $advisorClass_call_sign 
from $advisorClassTableName failed after copying record to $advisorClassDeletedTableName. Last error: $myStr");
											} else {
												// write advisorclass audit log record
												if ($testMode) {
													$log_mode		= 'testMode';
													$log_file		= 'TestAdvisor';
												} else {
													$log_mode		= 'Production';
													$log_file		= 'Advisor';
												}
												$submitArray		= array('logtype'=>$log_file,
																			'logmode'=>$log_mode,
																			'logaction'=>'DELETE',
																			'logsubtype'=>'Class',
																			'logdate'=>$logDate,
																			'logprogram'=>'ADVUPDATE',
																			'logwho'=>$userName,
																			'logsemester'=>$advisorClass_semester,
																			'logid'=>$advisorClass_ID,
																			'logcallsign'=>$advisorClass_advisor_call_sign,
																			'call_sign'=>$advisorClass_advisor_call_sign,
																			'first_name'=>$advisorClass_advisor_first_name,
																			'last_name'=>$advisorClass_advisor_last_name);
												if ($doDebug){
													echo "submitArray:<br />";
													foreach($submitArray as $myKey=>$myValue) {
														echo "&nbsp;&nbsp;&nbsp;&nbsp;$myKey = $myValue<br />";
													}
												}
												$result		= storeAuditLogData_v3($submitArray);
												if ($result[0] === FALSE) {
													if ($doDebug) {
														echo "storeAuditLogData failed: $result[1]<br />";
													}
												}
											}
										}
									}
									$content	.= "<p>All class records for $advisor_call_sign deleted.</p>";
								} else {
									if ($doDebug) {
										echo "Didn't find any class records for $advisor_call_sign<br />";
									}
									$content	.= "<p>No class records for $advisor_call_sign were found.</p>";
								}
							}
						}
					}
				} else {								// update this record
				
					$content		= "<h3>Results of the Update of Advisor_ID $advisor_ID ($advisor_call_sign)</h3>";
					$doTheUpdate 						= FALSE;
					$updateData							= array();
					$updateFormat						= array();
					$changeFirstName					= FALSE;
					$changeLastName						= FALSE;
					$changeSemester						= FALSE;
					$changeTimeZone						= FALSE;
					$changeTimeZoneID					= FALSE;
					$changeClass						= FALSE;
					if ($inp_select_sequence != $advisor_select_sequence) { 
						$doTheUpdate = TRUE;
						$updateParams['select_sequence'] = $inp_select_sequence;
						$updateFormat[]					= '%d';
						$actionContent .= "Updating current select_sequence of $advisor_select_sequence to $inp_select_sequence. ";
					}
					if ($inp_advisor_call_sign != $advisor_call_sign) { 
						$doTheUpdate = TRUE;
						$updateParams['call_sign'] = $inp_advisor_call_sign;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current advisor call sign of $advisor_call_sign to $inp_advisor_call_sign. ";
					}
					if ($inp_first_name != $advisor_first_name) { 
						$doTheUpdate 				= TRUE;
						$changeClass				= TRUE;
						$changeFirstName			= TRUE;
						$updateParams['first_name'] = $inp_first_name;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current first_name of $advisor_first_name to $inp_first_name. ";
					}
					if ($inp_last_name != $advisor_last_name) { 
						$doTheUpdate 				= TRUE;
						$changeClass				= TRUE;
						$changeLastName				= TRUE;
						$actionContent 				.= "Updating current last_name of $advisor_last_name to $inp_last_name. ";
						$updateParams['last_name'] 	= addslashes($inp_last_name);
						$advisor_last_name			= $inp_last_name;
						$updateFormat[]					= '%s';
					}
					if ($inp_email != $advisor_email) { 
						$doTheUpdate = TRUE;
						$updateParams['email'] = $inp_email;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current email of $advisor_email to $inp_email. ";
					}
					if (strcmp($inp_phone,$advisor_phone) != 0) { 
						$doTheUpdate = TRUE;
						$updateParams['phone'] = $inp_phone;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current phone of $advisor_phone to $inp_phone. ";
					}
					if ($inp_text_message != $advisor_text_message) { 
						$doTheUpdate = TRUE;
						$updateFormat[]					= '%s';
						$updateParams['text_message'] = $inp_text_message;
						$actionContent .= "Updating current text_message of $advisor_text_message to $inp_text_message. ";
					}
					if ($inp_city != $advisor_city) { 
						$doTheUpdate = TRUE;
						$updateParams['city'] = $inp_city;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current city of $advisor_city to $inp_city. ";
					}
					if ($inp_state != $advisor_state) { 
						$doTheUpdate = TRUE;
						$updateParams['state'] = $inp_state;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current state of $advisor_state to $inp_state. ";
					}
					if ($inp_zip_code != $advisor_zip_code) { 
						$doTheUpdate = TRUE;
						$updateParams['zip_code'] = $inp_zip_code;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current zip_code of $advisor_zip_code to $inp_zip_code. ";
					}
					if ($inp_country != $advisor_country) { 
						$doTheUpdate = TRUE;
						$updateParams['country'] = $inp_country;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current country of $advisor_country to $inp_country. ";
					}
					if ($inp_semester != $advisor_semester) { 
						$doTheUpdate = TRUE;
						$updateParams['semester'] = $inp_semester;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current semester of $advisor_semester to $inp_semester. ";
						$advisor_semester	= $inp_semester;
					}
					if ($inp_survey_score != $advisor_survey_score) { 
						$doTheUpdate = TRUE;
						$updateParams['survey_score'] = $inp_survey_score;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current survey_score of $advisor_survey_score to $inp_survey_score. ";
					}
					if ($inp_languages != $advisor_languages) { 
						$doTheUpdate = TRUE;
						$updateParams['languages'] = $inp_languages;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current languages of $advisor_languages to $inp_languages. ";
					}
					if ($inp_fifo_date != $advisor_fifo_date) { 
						$doTheUpdate = TRUE;
						$updateParams['fifo_date'] = $inp_fifo_date;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current fifo_date of $advisor_fifo_date to $inp_fifo_date. ";
					}
					if ($inp_welcome_email_date != $advisor_welcome_email_date) { 
						$doTheUpdate = TRUE;
						$updateParams['welcome_email_date'] = $inp_welcome_email_date;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current welcome_email_date of $advisor_welcome_email_date to $inp_welcome_email_date. ";
					}
					if ($inp_verify_email_date != $advisor_verify_email_date) { 
						$doTheUpdate = TRUE;
						$updateParams['verify_email_date'] = $inp_verify_email_date;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current verify_email_date of $advisor_verify_email_date to $inp_verify_email_date. ";
					}
					if ($inp_verify_email_number != $advisor_verify_email_number) { 
						$doTheUpdate = TRUE;
						$updateParams['verify_email_number'] = $inp_verify_email_number;
						$updateFormat[]					= '%d';
						$actionContent .= "Updating current verify_email_number of $advisor_verify_email_number to $inp_verify_email_number. ";
					}
					if ($inp_verify_response != $advisor_verify_response) { 
						$doTheUpdate = TRUE;
						$updateParams['verify_response'] = $inp_verify_response;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current verify_response of $advisor_verify_response to $inp_verify_response. ";
					}
					if ($inp_class_verified != $advisor_class_verified) { 
						$doTheUpdate = TRUE;
						$updateParams['class_verified'] = $inp_class_verified;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current class_verified of $advisor_class_verified to $inp_class_verified. ";
					}
					if ($inp_country_code != $advisor_country_code) { 
						$doTheUpdate = TRUE;
						$updateParams['country_code'] = $inp_country_code;
						$updateFormat[]					= '%s';
						$changeCountryCode				= TRUE;
						$actionContent .= "Updating current country_code of $advisor_country_code to $inp_country_code. ";
					}
					if (strcmp($inp_ph_code,$advisor_ph_code) != 0) { 
						$doTheUpdate = TRUE;
						$updateParams['ph_code'] = $inp_ph_code;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current ph_code of $advisor_ph_code to $inp_ph_code. ";
					}
					if ($inp_timezone_id != $advisor_timezone_id) { 
						$doTheUpdate = TRUE;
						$updateParams['timezone_id'] = $inp_timezone_id;
						$updateFormat[]					= '%s';
						$changeTimeZoneID		= TRUE;
						$changeClass			= TRUE;
						$actionContent .= "Updating current timezone_id of $advisor_timezone_id to $inp_timezone_id. ";
					}
					if ($inp_timezone_offset != $advisor_timezone_offset) { 
						$doTheUpdate = TRUE;
						$updateParams['timezone_offset'] = $inp_timezone_offset;
						$updateFormat[]					= '%s';
						$changeTimeZoneID				= TRUE;
						$changeClass					= TRUE;
						$actionContent .= "Updating current timezone_offset of $advisor_timezone_offset to $inp_timezone_offset. ";
					}
					if ($inp_whatsapp != $advisor_whatsapp) { 
						$doTheUpdate = TRUE;
						$updateParams['whatsapp'] = $inp_whatsapp;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current whatsapp of $advisor_whatsapp to $inp_whatsapp. ";
					}
					if ($inp_signal != $advisor_signal) { 
						$doTheUpdate = TRUE;
						$updateParams['signal'] = $inp_signal;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current signal of $advisor_signal to $inp_signal. ";
					}
					if ($inp_telegram != $advisor_telegram) { 
						$doTheUpdate = TRUE;
						$updateParams['telegram'] = $inp_telegram;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current telegram of $advisor_telegram to $inp_telegram. ";
					}
					if ($inp_messenger != $advisor_messenger) { 
						$doTheUpdate = TRUE;
						$updateParams['messenger'] = $inp_messenger;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current messenger of $advisor_messenger to $inp_messenger. ";
					}
					if ($inp_replacement_status != $advisor_replacement_status) { 
						$doTheUpdate = TRUE;
						$updateParams['replacement_status'] = $inp_replacement_status;
						$updateFormat[]					= '%s';
						$actionContent .= "Updating current replacement_status of $advisor_replacement_status to $inp_replacement_status. ";
					}
					if ($doTheUpdate) {
						if ($doDebug) {
							echo "Doing the update. Contents of the updateParams array:<br /><pre>";
							print_r($updateParams);
							echo "</pre><br />";
						}
						if ($inp_action_log != $advisor_action_log) { 
							$advisor_action_log			= $inp_action_log;
						}
						$advisor_action_log				= "$advisor_action_log ADVUPDATE $actionDate $userName $actionContent ";
						$updateParams['action_log'] 	= $advisor_action_log;
						$updateFormat[]					= '%s';
						$actionContent .= "Updated action_log.<br />";

						$advisorUpdateData		= array('tableName'=>$advisorTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateParams,
														'inp_format'=>$updateFormat,
														'jobname'=>$jobname,
														'inp_id'=>$advisor_ID,
														'inp_callsign'=>$advisor_call_sign,
														'inp_semester'=>$advisor_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateAdvisor($advisorUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorTableName<br />";
						} else {
							$content		.= $actionContent;	

							// see if the class records have to be updated
							if ($changeClass) {				// yup
								$sql					= "select * from $advisorClassTableName 
															where advisor_call_sign='$advisor_call_sign' 
																and semester = '$advisor_semester' 
															order by sequence";
								$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
								if ($wpw1_cwa_advisorclass === FALSE) {
									$myError			= $wpdb->last_error;
									$myQuery			= $wpdb->last_query;
									if ($doDebug) {
										echo "Reading $advisorClassTableName table failed<br />
											  wpdb->last_query: $myQuery<br />
											  wpdb->last_error: $myError<br />";
									}
									$errorMsg			= "$jobname pass 4 Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
									sendErrorEmail($errorMsg);
								} else {
									$numACRows						= $wpdb->num_rows;
									if ($doDebug) {
										$myStr						= $wpdb->last_query;
										echo "ran $myStr<br />and found $numACRows rows<br />";
									}
									if ($numACRows > 0) {
										foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
											$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
											$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
											$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
											$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
											$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
											$advisorClass_sequence 					= $advisorClassRow->sequence;
											$advisorClass_semester 					= $advisorClassRow->semester;
											$advisorClass_timezone 					= $advisorClassRow->time_zone;
											$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
											$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
											$advisorClass_level 					= $advisorClassRow->level;
											$advisorClass_class_size 				= $advisorClassRow->class_size;
											$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
											$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
											$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
											$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
											$advisorClass_action_log 				= $advisorClassRow->action_log;
											$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
											$advisorClass_date_created				= $advisorClassRow->date_created;
											$advisorClass_date_updated				= $advisorClassRow->date_updated;
											$advisorClass_student01 				= $advisorClassRow->student01;
											$advisorClass_student02 				= $advisorClassRow->student02;
											$advisorClass_student03 				= $advisorClassRow->student03;
											$advisorClass_student04 				= $advisorClassRow->student04;
											$advisorClass_student05 				= $advisorClassRow->student05;
											$advisorClass_student06 				= $advisorClassRow->student06;
											$advisorClass_student07 				= $advisorClassRow->student07;
											$advisorClass_student08 				= $advisorClassRow->student08;
											$advisorClass_student09 				= $advisorClassRow->student09;
											$advisorClass_student10 				= $advisorClassRow->student10;
											$advisorClass_student11 				= $advisorClassRow->student11;
											$advisorClass_student12 				= $advisorClassRow->student12;
											$advisorClass_student13 				= $advisorClassRow->student13;
											$advisorClass_student14 				= $advisorClassRow->student14;
											$advisorClass_student15 				= $advisorClassRow->student15;
											$advisorClass_student16 				= $advisorClassRow->student16;
											$advisorClass_student17 				= $advisorClassRow->student17;
											$advisorClass_student18 				= $advisorClassRow->student18;
											$advisorClass_student19 				= $advisorClassRow->student19;
											$advisorClass_student20 				= $advisorClassRow->student20;
											$advisorClass_student21 				= $advisorClassRow->student21;
											$advisorClass_student22 				= $advisorClassRow->student22;
											$advisorClass_student23 				= $advisorClassRow->student23;
											$advisorClass_student24 				= $advisorClassRow->student24;
											$advisorClass_student25 				= $advisorClassRow->student25;
											$advisorClass_student26 				= $advisorClassRow->student26;
											$advisorClass_student27 				= $advisorClassRow->student27;
											$advisorClass_student28 				= $advisorClassRow->student28;
											$advisorClass_student29 				= $advisorClassRow->student29;
											$advisorClass_student30 				= $advisorClassRow->student30;
											$class_number_students					= $advisorClassRow->number_students;
											$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
											$class_comments							= $advisorClassRow->class_comments;
											$copycontrol							= $advisorClassRow->copy_control;

											$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
										
											$updateParams			= array();
											$updateFormat			= array();
											$doUTC					= FALSE;
											$doOffset				= FALSE;
											if ($changeFirstName) {
												$updateParams['advisor_first_name']		= $inp_first_name;
												$updateFormat[]							= '%s';
												$advisorClass_advisor_first_name		= $inp_first_name;									
											}
											if ($changeLastName) {
												$updateParams['advisor_last_name']		= addslashes($inp_last_name);
												$updateFormat[]							= '%s';
												$advisorClass_advisor_last_name			= $inp_last_name;
											}
											if ($changeSemester) {
												$updateParams['semester']				= $inp_semester;
												$updateFormat[]							= '%s';
												$advisorClass_semester					= $inp_semester;
												$doUTC									= TRUE;
												$doOffset								= TRUE;
											}
											if ($changeTimeZoneID) {
												$updateParams['timezone_id']			= $inp_timezone_id;
												$updateFormat[]							= '%s';
												$updateParams['timezone_offset']		= $inp_timezone_offset;
												$updateFormat[]							= '%f';
												$advisorClass_timezone_id				= $inp_timezone_id;
												$advisorClass_timezone_offset			= $inp_timezone_offset;
												$doUTC									= TRUE;
											}
											if ($doUTC) {	
												/// see if offset changed
												$result 			= getOffsetFromIdentifier($advisorClass_timezone_id,$advisorClass_semester,$doDebug);
												if ($result === FALSE) {
													if ($doDebug) {
														echo "Getting the timezone offset failed<br />";
													}
												} else {
													if ($result != $advisorClass_timezone_offset) {
														if ($doDebug) {
															echo "offset changed from $advisorCass_timezone_offset to $result<br />";
														}
														$advisorClass_timezone_offset	= $result;
														$updateParams['timezone_offset']	= $advisorClass_timezone_offset;
														$updateFormat[]						= '%f';
													}
												}
												// also now have to update the UTC days and class times
												$result						= utcConvert('toutc',$inp_timezone_offset,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days);
												if ($result[0] == 'FAIL') {
													if ($doDebug) {
														echo "utcConvert failed 'toutc',$inp_timezone_offset,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days<br />
															  Error: $result[3]<br />";
													}
													$advisorClass_class_schedule_days_utc			= "<b>ERROR</b>";
													$advisorClass_class_schedule_times_utc			= '';
												} else {
													$advisorClass_class_schedule_times_utc			= $result[1];
													$advisorClass_class_schedule_days_utc			= $result[2];
												}
												$updateParams['class_schedule_days_utc']			= $advisorClass_class_schedule_days_utc;
												$updateParams['class_schedule_times_utc']			= $advisorClass_class_schedule_times_utc;
												$updateFormat[]										= '%s';
												$updateFormat[]										= '%s';
	
												
											}
											// changes set. update record and write audit log
											$classUpdateData		= array('tableName'=>$advisorClassTableName,
																			'inp_method'=>'add',
																			'inp_data'=>$updateParams,
																			'inp_format'=>$updateFormat,
																			'jobname'=>$jobname,
																			'inp_id'=>$advisorClass_ID,
																			'inp_callsign'=>$advisorClass_advisor_call_sign,
																			'inp_semester'=>$advisorClass_semester,
																			'inp_who'=>$userName,
																			'testMode'=>$testMode,
																			'doDebug'=>$doDebug);
											$updateResult	= updateClass($classUpdateData);
											if ($updateResult[0] === FALSE) {
												$myError	= $wpdb->last_error;
												$mySql		= $wpdb->last_query;
												$errorMsg	= "A$jobname Processing $advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
												if ($doDebug) {
													echo $errorMsg;
												}
												sendErrorEmail($errorMsg);
												$content		.= "Unable to update content in $advisorClassTableName<br />";
											} else {
												if ($doDebug) {
													echo "AdvisorClass sequence $advisorClass_sequence with id of $advisorClass_ID updated<br />";
												}
											}
										}
									} else {
										echo "No $advisorClassTableName records found for advisor $advisorid<br />";
									}
								}
							}
						}				///// end of change class loop					
							$content		.= "<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisor_call_sign&inp_table=$inp_table&strpass=2'>HERE</a></p><br />";
					} else {
						if ($doDebug) {
							echo "No updates were entered.<br />";
						}
						$content .= "No updates were requested.<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "No record found in $advisorTableName table for ID $advisorid<br />";
				}
				$content	.= "No record found in $advisorTableName table for ID $advisorid<br />";
			}
		}
			
////////////	pass 5 ... show class record for modification		
		
	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 5 with $inp_class_id, and $inp_table<br />";
		}
// get the advisorClass record
		$sql					= "select * from $advisorClassTableName 
									where advisorclass_id=$inp_class_id";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorClassTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname pass 5 Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numACRows						= $wpdb->num_rows;
			if ($doDebug) {
				$myStr						= $wpdb->last_query;
				echo "ran $myStr<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
					$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
					$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
					$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
					$advisorClass_sequence 					= $advisorClassRow->sequence;
					$advisorClass_semester 					= $advisorClassRow->semester;
					$advisorClass_timezone 					= $advisorClassRow->time_zone;
					$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
					$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->level;
					$advisorClass_class_size 				= $advisorClassRow->class_size;
					$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
					$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
					$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
					$advisorClass_action_log 				= $advisorClassRow->action_log;
					$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
					$advisorClass_date_created				= $advisorClassRow->date_created;
					$advisorClass_date_updated				= $advisorClassRow->date_updated;
					$advisorClass_student01 				= $advisorClassRow->student01;
					$advisorClass_student02 				= $advisorClassRow->student02;
					$advisorClass_student03 				= $advisorClassRow->student03;
					$advisorClass_student04 				= $advisorClassRow->student04;
					$advisorClass_student05 				= $advisorClassRow->student05;
					$advisorClass_student06 				= $advisorClassRow->student06;
					$advisorClass_student07 				= $advisorClassRow->student07;
					$advisorClass_student08 				= $advisorClassRow->student08;
					$advisorClass_student09 				= $advisorClassRow->student09;
					$advisorClass_student10 				= $advisorClassRow->student10;
					$advisorClass_student11 				= $advisorClassRow->student11;
					$advisorClass_student12 				= $advisorClassRow->student12;
					$advisorClass_student13 				= $advisorClassRow->student13;
					$advisorClass_student14 				= $advisorClassRow->student14;
					$advisorClass_student15 				= $advisorClassRow->student15;
					$advisorClass_student16 				= $advisorClassRow->student16;
					$advisorClass_student17 				= $advisorClassRow->student17;
					$advisorClass_student18 				= $advisorClassRow->student18;
					$advisorClass_student19 				= $advisorClassRow->student19;
					$advisorClass_student20 				= $advisorClassRow->student20;
					$advisorClass_student21 				= $advisorClassRow->student21;
					$advisorClass_student22 				= $advisorClassRow->student22;
					$advisorClass_student23 				= $advisorClassRow->student23;
					$advisorClass_student24 				= $advisorClassRow->student24;
					$advisorClass_student25 				= $advisorClassRow->student25;
					$advisorClass_student26 				= $advisorClassRow->student26;
					$advisorClass_student27 				= $advisorClassRow->student27;
					$advisorClass_student28 				= $advisorClassRow->student28;
					$advisorClass_student29 				= $advisorClassRow->student29;
					$advisorClass_student30 				= $advisorClassRow->student30;
					$class_number_students					= $advisorClassRow->number_students;
					$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
					$class_comments							= $advisorClassRow->class_comments;
					$copycontrol							= $advisorClassRow->copy_control;

					$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);


					$content	.= "<h3>Update the Advisor Class $inp_sequence for $inp_advisor_call_sign</h3>
									<p>Table: $advisorClassTableName</p>
									<form method='post' name='selection_form' action='$theURL' 
									ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='6'>
									<input type='hidden' name='inp_table' value='$inp_table'>
									<input type='hidden' name='advisorid' value='$advisorClass_ID'>
									<input type='hidden' name='inp_verbose' value='inp_verbose'>
									<p>To delete this class record, check the 
									'Delete this record' box below. Otherwise, make any needed changes.<br />
									<input type='checkbox' class='formInputText' name='inp_recdel' value='recdel'> Delete this record</p>
									<table style='border-collapse:collapse;'>
									<tr><th style='width:200px;'>Field</th>
										<th>Value</th></tr>
									<tr><td>Call Sign</td>
										<td>$advisorClass_advisor_call_sign</td></tr>
									<tr><td>AdvisorNew ID</td>
										<td>$advisorClass_advisor_id</td></tr>
									<tr><td>First Name</td>
										<td>$advisorClass_advisor_first_name</td></tr>
									<tr><td>Last Name</td>
										<td>$advisorClass_advisor_last_name</td></tr>
									<tr><td>Sequence</td>
										<td>$advisorClass_sequence</td></tr>
									<tr><td>Semester</td>
										<td>$advisorClass_semester</td></tr>
									<tr><td>Level</td>
										<td><input class='formInputText' type='text' name='inp_level' size='15' maxlenth='15' value='$advisorClass_level'></td></tr>
									<tr><td>Class Size</td>
										<td><input class='formInputText' type='text' name='inp_class_size' size='5' maxlenth='5' value='$advisorClass_class_size'></td></tr>
									<tr><td>Action Log</td>
										<td><textarea rows='4' cols='50' name='inp_action_log' class='formInputText'>$advisorClass_action_log</textarea></td></tr>
									<tr><td>Class Schedule Days</td>
										<td><input class='formInputText' type='text' name='inp_class_schedule_days' size='20' maxlenth='30' value='$advisorClass_class_schedule_days'></td></tr>
									<tr><td>Class Schedule Times</td>
										<td><input class='formInputText' type='text' name='inp_class_schedule_times' size='15' maxlenth='15' value='$advisorClass_class_schedule_times'></td></tr>
									<tr><td>Class Schedule Days UTC</td>
										<td><input class='formInputText' type='text' name='inp_class_schedule_days_utc' size='20' maxlenth='30' value='$advisorClass_class_schedule_days_utc'></td></tr>
									<tr><td>Class Schedule Times UTC</td>
										<td><input class='formInputText' type='text' name='inp_class_schedule_times_utc' size='15' maxlenth='15' value='$advisorClass_class_schedule_times_utc'></td></tr>
									<tr><td>Class Incomplete</td>
										<td><input class='formInputText' type='text' name='inp_class_incomplete' size='10' maxlenth='50' value='$advisorClass_class_incomplete'></td></tr>
									<tr><td colspan='2'><table>
										<tr><td>Student01: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student01' value='$advisorClass_student01'></td>
											<td>Student02: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student02' value='$advisorClass_student02'></td>
											<td>Student03: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student03' value='$advisorClass_student03'></td></tr>
										<tr><td>Student04: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student04' value='$advisorClass_student04'></td>
											<td>Student05: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student05' value='$advisorClass_student05'></td>
											<td>Student06: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student06' value='$advisorClass_student06'></td></tr>
										<tr><td>Student07: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student07' value='$advisorClass_student07'></td>
											<td>Student08: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student08' value='$advisorClass_student08'></td>
											<td>Student09: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student09' value='$advisorClass_student09'></td></tr>
										<tr><td>Student10: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student10' value='$advisorClass_student10'></td>
											<td>Student11: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student11' value='$advisorClass_student11'></td>
											<td>Student12: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student12' value='$advisorClass_student12'></td></tr>
										<tr><td>Student13: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student13' value='$advisorClass_student13'></td>
											<td>Student14: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student14' value='$advisorClass_student14'></td>
											<td>Student15: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student15' value='$advisorClass_student15'></td></tr>
										<tr><td>Student16: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student16' value='$advisorClass_student16'></td>
											<td>Student17: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student17' value='$advisorClass_student17'></td>
											<td>Student18: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student19' value='$advisorClass_student18'></td></tr>
										<tr><td>Student19: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student19' value='$advisorClass_student19'></td>
											<td>Student20: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student20' value='$advisorClass_student20'></td>
											<td>Student21: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student21' value='$advisorClass_student21'></td></tr>
										<tr><td>Student22: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student22' value='$advisorClass_student22'></td>
											<td>Student23: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student23' value='$advisorClass_student23'></td>
											<td>Student24: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student24' value='$advisorClass_student24'></td></tr>
										<tr><td>Student25: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student25' value='$advisorClass_student25'></td>
											<td>Student26: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student26' value='$advisorClass_student26'></td>
											<td>Student27: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student27' value='$advisorClass_student27'></td></tr>
										<tr><td>Student28: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student28' value='$advisorClass_student28'></td>
											<td>Student29: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student29' value='$advisorClass_student29'></td>
											<td>Student30: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student30' value='$advisorClass_student30'></td></tr>
										</table></td></tr>
									<tr><td>Number Students</td>
										<td>$class_number_students (<i>automatically updated</i>)</td></tr>
									<tr><td>Evaluation Complete</td>
										<td><input type='text' class='formInputText' size='5' maxlength='5' name='inp_evaluation_complete' value='$class_evaluation_complete'></td></tr>
									<tr><td>Class Comments</td>
										<td><textarea class='formInputText' name='inp_class_comments' cols='50' rows='5'>$class_comments</textarea></td></tr>
									<tr><td>Copy Control</td>
										<td><input type='text' class='formInputText' size='15' maxlength='15' name='inp_copy_control' value='$copycontrol'></td></tr>
									<tr><td>&nbsp;</td>
										<td><input class='formInputButton' type='submit' value='Submit' /></td></tr>
									</table></form>
									<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisorClass_advisor_call_sign&inp_table=$inp_table&strpass=2'>HERE</a></p><br />"; 

				}			// end of the advisorClass while
			} else {
				$content	.= "<p>No record found in $advisorClassTableName for $inp_advisor_call_sign class $inp_sequence/p>";
			}
		}
		


////////  	Pass 6 	update  advisorClass fields


	} elseif ("6" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 6 with $advisorid and $inp_table<br />";
		}
		$doTheUpdate			= FALSE;
		$content				.= "<h3>Results from Updating the Advisor Class Record</h3>";
		// get the advisorClass record
		$sql					= "select * from $advisorClassTableName 	
									where advisorclass_id=$advisorid";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorClassTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname pass 6 Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numACRows						= $wpdb->num_rows;
			if ($doDebug) {
				$myStr						= $wpdb->last_query;
				echo "ran $myStr<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
					$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
					$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
					$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
					$advisorClass_sequence 					= $advisorClassRow->sequence;
					$advisorClass_semester 					= $advisorClassRow->semester;
					$advisorClass_timezone 					= $advisorClassRow->time_zone;
					$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
					$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->level;
					$advisorClass_class_size 				= $advisorClassRow->class_size;
					$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
					$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
					$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
					$advisorClass_action_log 				= $advisorClassRow->action_log;
					$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
					$advisorClass_date_created				= $advisorClassRow->date_created;
					$advisorClass_date_updated				= $advisorClassRow->date_updated;
					$advisorClass_student01 				= $advisorClassRow->student01;
					$advisorClass_student02 				= $advisorClassRow->student02;
					$advisorClass_student03 				= $advisorClassRow->student03;
					$advisorClass_student04 				= $advisorClassRow->student04;
					$advisorClass_student05 				= $advisorClassRow->student05;
					$advisorClass_student06 				= $advisorClassRow->student06;
					$advisorClass_student07 				= $advisorClassRow->student07;
					$advisorClass_student08 				= $advisorClassRow->student08;
					$advisorClass_student09 				= $advisorClassRow->student09;
					$advisorClass_student10 				= $advisorClassRow->student10;
					$advisorClass_student11 				= $advisorClassRow->student11;
					$advisorClass_student12 				= $advisorClassRow->student12;
					$advisorClass_student13 				= $advisorClassRow->student13;
					$advisorClass_student14 				= $advisorClassRow->student14;
					$advisorClass_student15 				= $advisorClassRow->student15;
					$advisorClass_student16 				= $advisorClassRow->student16;
					$advisorClass_student17 				= $advisorClassRow->student17;
					$advisorClass_student18 				= $advisorClassRow->student18;
					$advisorClass_student19 				= $advisorClassRow->student19;
					$advisorClass_student20 				= $advisorClassRow->student20;
					$advisorClass_student21 				= $advisorClassRow->student21;
					$advisorClass_student22 				= $advisorClassRow->student22;
					$advisorClass_student23 				= $advisorClassRow->student23;
					$advisorClass_student24 				= $advisorClassRow->student24;
					$advisorClass_student25 				= $advisorClassRow->student25;
					$advisorClass_student26 				= $advisorClassRow->student26;
					$advisorClass_student27 				= $advisorClassRow->student27;
					$advisorClass_student28 				= $advisorClassRow->student28;
					$advisorClass_student29 				= $advisorClassRow->student29;
					$advisorClass_student30 				= $advisorClassRow->student30;
					$class_number_students					= $advisorClassRow->number_students;
					$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
					$class_comments							= $advisorClassRow->class_comments;
					$copycontrol							= $advisorClassRow->copy_control;

					$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

					$updateParams	= array();
					$updateFormat	= array();

					if ($inp_recdel == 'recdel') {		// delete this class record
						if ($doDebug) {
							echo "Requested to delete this class record<br />";
						}
						//// copy class record to deleted table
						$sql		= "insert into $advisorClassDeletedTableName 
										select * from $advisorClassTableName 
										where advisorclass_id=$advisorClass_ID";
						$myResult	= $wpdb->get_results($sql);
						if ($myResult === FALSE) {
							$myQuery	= $wpdb->last_query;
							$myError	= $wpdb->last_error;
							if ($doDebug) {
								echo "copying $advisorClass_call_sign $advisorClass_sequence from $advisorClassTableName to $advisorClassDeletedTableName failed<br />
									  wpdb->last_query: $myQuery<br />
									  <b>wpdb->last_error: </b>$myError<br />";
							}
							sendErrorEmail("$jobname pass 6 copying $advisorClass_call_sign $advisorClass_sequence from $advisorClassTableName to $advisorClassDeletedTableName failed<br />Query: $myQuery<br />Error: $myError");
						}
						if (sizeof($myResult) != 0 || $myResult === FALSE) {
							$myStr			= $wpdb->last_error;
							sendErrorEmail("$jobname pass 6 attempting to copy $advisorClass_call_sign sequence $advisorClass_sequence 
class record to $advisorClassDeletedTableName failed. Last error: $myStr");
						} else {
							$myResult 	= $wpdb->delete($advisorClassTableName,
														array('advisorclass_id'=>$advisorClass_ID),
														array('%d'));
							if ($myResult === FALSE) {
								$myQuery	= $wpdb->last_query;
								$myError	= $wpdb->last_error;
								if ($doDebug) {
									echo "deleting ID $advisorClass_ID from $advisorClassTableName table failed<br />
										  wpdb->last_query: $myQuery<br />
										  <b>wpdb->last_error: </b>$myError<br />";
								}
								sendErrorEmail("$jobname pass 6 deleting ID $advisorClass_ID from $advisorClassTableName table failed<br />Query: $myQuery<br />Error: $myError");
							} else {
								// write advisor audit log record
								if ($testMode) {
									$log_mode		= 'testMode';
									$log_file		= 'TestAdvisor';
								} else {
									$log_mode		= 'Production';
									$log_file		= 'Advisor';
								}
								$submitArray		= array('logtype'=>$log_file,
															'logmode'=>$log_mode,
															'logaction'=>'DELETE',
															'logsubtype'=>'Class',
															'logdate'=>$logDate,
															'logprogram'=>'ADVUPDATE',
															'logsemester'=>$advisorClass_semester,
															'logwho'=>$userName,
															'logid'=>$advisorClass_ID,
															'logcallsign'=>$advisorClass_advisor_call_sign,
															'call_sign'=>$advisorClass_advisor_call_sign,
															'first_name'=>$advisorClass_advisor_first_name,
															'last_name'=>$advisorClass_advisor_last_name);
								foreach($updateParams as $myKey=>$myValue) {
									if (!in_array($myKey,$fieldTest)) {
										$submitArray[$myKey]	= $myValue;
									}
								}
								if ($doDebug){
									echo "submitArray:<br />";
									foreach($submitArray as $myKey=>$myValue) {
										echo "&nbsp;&nbsp;&nbsp;&nbsp;$myKey = $myValue<br />";
									}
								}
								$result		= storeAuditLogData_v3($submitArray);
								if ($result[0] === FALSE) {
									if ($doDebug) {
										echo "storeAuditLogData failed: $result[1]<br />";
									}
								}
								$content		.= "<p>Class #$advisorClass_sequence for $advisorClass_advisor_call_sign has been deleted.</p>";
							}
						}
					} else {							// update this class record
						$dotheUpdate	= FALSE;
						$actionContent	= '';
						$updateUTC		= FALSE;
						if ($advisorClass_level != $inp_level) { 
							$doTheUpdate = TRUE;
							$updateParams['level'] = $inp_level;
							$updateFormat[]		= '%s';
							$actionContent .= "Updating current level of $advisorClass_level to $inp_level.<br />";
						}
						if ($advisorClass_class_size != $inp_class_size) { 
							$doTheUpdate = TRUE;
							$updateParams['class_size'] = $inp_class_size;
							$updateFormat[]		= '%d';
							$actionContent .= "Updating current class_size of $advisorClass_class_size to $inp_class_size.<br />";
						}
						if ($advisorClass_action_log != $inp_action_log) { 
							$doTheUpdate = TRUE;
							$advisorClass_action_log	= $inp_action_log;
						}
						if ($advisorClass_class_schedule_days != $inp_class_schedule_days) { 
							$doTheUpdate = TRUE;
							$updateParams['class_schedule_days'] = $inp_class_schedule_days;
							$updateFormat[]		= '%s';
							$actionContent .= "Updating current class_schedule_days of $advisorClass_class_schedule_days to $inp_class_schedule_days.<br />";
							$updateUTC		= TRUE;
						}
						if ($advisorClass_class_schedule_times != $inp_class_schedule_times) { 
							$doTheUpdate = TRUE;
							$updateFormat[]		= '%s';
							$updateParams['class_schedule_times'] = $inp_class_schedule_times;
							$actionContent .= "Updating current class_schedule_times of $advisorClass_class_schedule_times to $inp_class_schedule_times.<br />";
							$updateUTC		= TRUE;
						}
						if ($advisorClass_class_schedule_days_utc != $inp_class_schedule_days_utc) { 
							$doTheUpdate = TRUE;
							$updateParams['class_schedule_days_utc'] = $inp_class_schedule_days_utc;
							$updateFormat[]		= '%s';
							$actionContent .= "Updating current class_schedule_days_utc of $advisorClass_class_schedule_days_utc to $inp_class_schedule_days_utc.<br />";
						}
						if ($advisorClass_class_schedule_times_utc != $inp_class_schedule_times_utc) { 
							$doTheUpdate = TRUE;
							$updateParams['class_schedule_times_utc'] = $inp_class_schedule_times_utc;
							$updateFormat[]		= '%s';
							$actionContent .= "Updating current class_schedule_times_utc of $advisorClass_class_schedule_times_utc to $inp_class_schedule_times_utc.<br />";
						}
						if ($advisorClass_class_incomplete != $inp_class_incomplete) { 
							$doTheUpdate = TRUE;
							$updateParams['class_incomplete'] = $inp_class_incomplete;
							$updateFormat[]		= '%s';
							$actionContent .= "Updating current class_incomplete of $advisorClass_class_incomplete to $inp_class_incomplete.<br />";
						}
						if ($advisorClass_student01 != $inp_student01) {
							if ($doDebug) {
								echo "$advisorClass_student01 not equal to $inp_student01<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student01'] = $inp_student01;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student01 of $advisorClass_student01 to $inp_student01.<br />";
						}
						if ($advisorClass_student02 != $inp_student02) {
							if ($doDebug) {
								echo "$advisorClass_student02 not equal to $inp_student02<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student02'] = $inp_student02;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student02 of $advisorClass_student02 to $inp_student02.<br />";
						}
						if ($advisorClass_student03 != $inp_student03) {
							if ($doDebug) {
								echo "$advisorClass_student03 not equal to $inp_student03<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student03'] = $inp_student03;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student03 of $advisorClass_student03 to $inp_student03.<br />";
						}
						if ($advisorClass_student04 != $inp_student04) {
							if ($doDebug) {
								echo "$advisorClass_student04 not equal to $inp_student04<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student04'] = $inp_student04;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student04 of $advisorClass_student04 to $inp_student04.<br />";
						}
						if ($advisorClass_student05 != $inp_student05) {
							if ($doDebug) {
								echo "$advisorClass_student05 not equal to $inp_student05<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student05'] = $inp_student05;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student05 of $advisorClass_student05 to $inp_student05.<br />";
						}
						if ($advisorClass_student06 != $inp_student06) {
							if ($doDebug) {
								echo "$advisorClass_student06 not equal to $inp_student06<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student06'] = $inp_student06;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student06 of $advisorClass_student06 to $inp_student06.<br />";
						}
						if ($advisorClass_student07 != $inp_student07) {
							if ($doDebug) {
								echo "$advisorClass_student07 not equal to $inp_student07<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student07'] = $inp_student07;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student07 of $advisorClass_student07 to $inp_student07.<br />";
						}
						if ($advisorClass_student08 != $inp_student08) {
							if ($doDebug) {
								echo "$advisorClass_student08 not equal to $inp_student08<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student08'] = $inp_student08;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student08 of $advisorClass_student08 to $inp_student08.<br />";
						}
						if ($advisorClass_student09 != $inp_student09) {
							if ($doDebug) {
								echo "$advisorClass_student09 not equal to $inp_student09<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student09'] = $inp_student09;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student09 of $advisorClass_student09 to $inp_student09.<br />";
						}
						if ($advisorClass_student10 != $inp_student10) {
							if ($doDebug) {
								echo "$advisorClass_student10 not equal to $inp_student10<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student10'] = $inp_student10;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student10 of $advisorClass_student10 to $inp_student10.<br />";
						}
						if ($advisorClass_student11 != $inp_student11) {
							if ($doDebug) {
								echo "$advisorClass_student11 not equal to $inp_student11<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student11'] = $inp_student11;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student11 of $advisorClass_student11 to $inp_student11.<br />";
						}
						if ($advisorClass_student12 != $inp_student12) {
							if ($doDebug) {
								echo "$advisorClass_student12 not equal to $inp_student12<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student12'] = $inp_student12;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student12 of $advisorClass_student12 to $inp_student12.<br />";
						}
						if ($advisorClass_student13 != $inp_student13) {
							if ($doDebug) {
								echo "$advisorClass_student13 not equal to $inp_student13<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student13'] = $inp_student13;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student13 of $advisorClass_student13 to $inp_student13.<br />";
						}
						if ($advisorClass_student14 != $inp_student14) {
							if ($doDebug) {
								echo "$advisorClass_student14 not equal to $inp_student14<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student14'] = $inp_student14;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student14 of $advisorClass_student14 to $inp_student14.<br />";
						}
						if ($advisorClass_student15 != $inp_student15) {
							if ($doDebug) {
								echo "$advisorClass_student15 not equal to $inp_student15<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student15'] = $inp_student15;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student15 of $advisorClass_student15 to $inp_student15.<br />";
						}
						if ($advisorClass_student16 != $inp_student16) {
							if ($doDebug) {
								echo "$advisorClass_student16 not equal to $inp_student16<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student16'] = $inp_student16;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student16 of $advisorClass_student16 to $inp_student16.<br />";
						}
						if ($advisorClass_student17 != $inp_student17) {
							if ($doDebug) {
								echo "$advisorClass_student17 not equal to $inp_student17<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student17'] = $inp_student17;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student17 of $advisorClass_student17 to $inp_student17.<br />";
						}
						if ($advisorClass_student18 != $inp_student18) {
							if ($doDebug) {
								echo "$advisorClass_student18 not equal to $inp_student18<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student18'] = $inp_student18;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student18 of $advisorClass_student18 to $inp_student18.<br />";
						}
						if ($advisorClass_student19 != $inp_student19) {
							if ($doDebug) {
								echo "$advisorClass_student19 not equal to $inp_student19<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student19'] = $inp_student19;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student19 of $advisorClass_student19 to $inp_student19.<br />";
						}
						if ($advisorClass_student20 != $inp_student20) {
							if ($doDebug) {
								echo "$advisorClass_student20 not equal to $inp_student20<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student20'] = $inp_student20;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student20 of $advisorClass_student20 to $inp_student20.<br />";
						}
						if ($advisorClass_student21 != $inp_student21) {
							if ($doDebug) {
								echo "$advisorClass_student21 not equal to $inp_student21<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student21'] = $inp_student21;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student21 of $advisorClass_student21 to $inp_student21.<br />";
						}
						if ($advisorClass_student22 != $inp_student22) {
							if ($doDebug) {
								echo "$advisorClass_student22 not equal to $inp_student22<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student22'] = $inp_student22;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student22 of $advisorClass_student22 to $inp_student22.<br />";
						}
						if ($advisorClass_student23 != $inp_student23) {
							if ($doDebug) {
								echo "$advisorClass_student23 not equal to $inp_student23<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student23'] = $inp_student23;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student23 of $advisorClass_student23 to $inp_student23.<br />";
						}
						if ($advisorClass_student24 != $inp_student24) {
							if ($doDebug) {
								echo "$advisorClass_student24 not equal to $inp_student<br />24";
							}
							$doTheUpdate = TRUE;
							$updateParams['student24'] = $inp_student24;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student24 of $advisorClass_student24 to $inp_student24.<br />";
						}
						if ($advisorClass_student25 != $inp_student25) {
							if ($doDebug) {
								echo "$advisorClass_student25 not equal to $inp_student25<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student25'] = $inp_student25;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student25 of $advisorClass_student25 to $inp_student25.<br />";
						}
						if ($advisorClass_student26 != $inp_student26) {
							if ($doDebug) {
								echo "$advisorClass_student26 not equal to $inp_student26<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student26'] = $inp_student26;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student26 of $advisorClass_student26 to $inp_student26.<br />";
						}
						if ($advisorClass_student27 != $inp_student27) {
							if ($doDebug) {
								echo "$advisorClass_student27 not equal to $inp_student27<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student27'] = $inp_student27;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student27 of $advisorClass_student27 to $inp_student27.<br />";
						}
						if ($advisorClass_student28 != $inp_student28) {
							if ($doDebug) {
								echo "$advisorClass_student28 not equal to $inp_student28<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student28'] = $inp_student28;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student28 of $advisorClass_student28 to $inp_student28.<br />";
						}
						if ($advisorClass_student29 != $inp_student29) {
							if ($doDebug) {
								echo "$advisorClass_student29 not equal to $inp_student29<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student29'] = $inp_student29;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student29 of $advisorClass_student29 to $inp_student29.<br />";
						}
						if ($advisorClass_student30 != $inp_student30) {
							if ($doDebug) {
								echo "$advisorClass_student30 not equal to $inp_student30<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['student30'] = $inp_student30;
							$updateFormat[] = '%s';
							$actionContent .= "updating current student30 of $advisorClass_student30 to $inp_student30.<br />";
						}
/*
						if ($class_number_students != $inp_number_students) {
							if ($doDebug) {
								echo "$class_number_students not equal to $inp_number_students<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['number_students'] = $inp_number_students;
							$updateFormat[] = '%s';
							$actionContent .= "updating current number_students of $class_number_students to $inp_number_students.<br />";
						}
*/
						if ($class_evaluation_complete != $inp_evaluation_complete) {
							if ($doDebug) {
								echo "$advisorClass_evaluation_complete not equal to $inp_evaluation_complete<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['evaluation_complete'] = $inp_evaluation_complete;
							$updateFormat[] = '%s';
							$actionContent .= "updating current evaluation_complete of $class_evaluation_complete to $inp_evaluation_complete.<br />";
						}
						if ($class_comments != $inp_class_comments) {
							if ($doDebug) {
								echo "$advisorClass_class_comments not equal to $inp_class_comments<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['class_comments'] = $inp_class_comments;
							$updateFormat[] = '%s';
							$actionContent .= "updating current class_comments of $class_comments to $inp_class_comments.<br />";
						}
						if ($copycontrol != $inp_copy_control) {
							if ($doDebug) {
								echo "$copy_control not equal to $inp_copy_control<br />";
							}
							$doTheUpdate = TRUE;
							$updateParams['copy_control'] = $inp_copy_control;
							$updateFormat[] = '%s';
							$actionContent .= "updating current copy_control of $copy_control to $inp_copy_control.<br />";
						}
						
						if ($updateUTC) {
							if ($doDebug) {
								echo "Updating UTC info due to a local time change<br />";
							}
							$utcResult		= utcConvert('toutc',$advisorClass_timezone_offset,$inp_class_schedule_times,$inp_class_schedule_days,$doDebug);
							if ($utcResult[0] == 'FAIL') {
								if ($doDebug) {
									echo "converting $advisorClass_timezone_offset,$inp_class_schedule_times,$inp_class_schedule_days to UTC failed<br />";
								}
							} else {
								$inp_class_schedule_times	= $utcResult[1];
								$inp_class_schedule_days	= $utcResult[2];
								$updateParams['class_schedule_days_utc'] = $inp_class_schedule_days_utc;
								$updateFormat[]				= '%s';
								$updateParams['class_schedule_times_utc'] = $inp_class_schedule_times_utc;
								$updateFormat[]				= '%s';
								$doTheUpdate				= TRUE;
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
							if (${'inp_student'.$strSnum} != '') {
								$myInt ++;
							}
						}
						if ($myInt != $inp_number_students) {
							if ($doDebug) {
								echo "inp_number_students is $inp_number_students which does not match $myInt actual students<br />";
							}
							$inp_number_students		= $myInt;
							$doTheUpdate = TRUE;
							$updateParams['number_students'] = $inp_number_students;
							$updateFormat[] = '%s';
							$actionContent .= "updating current number_students of $class_number_students to $inp_number_students.<br />";
						}
						
						if ($doTheUpdate) {
							if ($doDebug) {
								echo "Doing the update. Contents of the updateParams array:<br /><pre>";
								print_r($updateParams);
								echo "</pre><br />";
							}
							if ($advisorClass_action_log != $inp_action_log) { 
								$advisorClass_action_log	= $inp_action_log;
							}
							$advisorClass_action_log	= "$advisorClass_action_log ADVUPDATE $actionDate $userName $actionContent ";
							$updateParams['action_log'] = $advisorClass_action_log;
							$updateFormat[]		= '%s';
							$classUpdateData		= array('tableName'=>$advisorClassTableName,
															'inp_method'=>'update',
															'inp_data'=>$updateParams,
															'inp_format'=>$updateFormat,
															'jobname'=>$jobname,
															'inp_id'=>$advisorClass_ID,
															'inp_callsign'=>$advisorClass_advisor_call_sign,
															'inp_semester'=>$advisorClass_semester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateClass($classUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "A$jobname Processing $advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to update content in $advisorClassTableName<br />";
							} else {
								$content		.= $actionContent;	
							}			
						} else {
							if ($doDebug) {
								echo "No updates were entered.<br />";
							}
							$content .= "No updates were requested.<br />";
						}
					}
				}
				$content		.= "<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisorClass_advisor_call_sign&inp_table=$inp_table&strpass=2'>HERE</a></p><br />";
			} else {
				if ($doDebug) {
					echo "No record found in $advisorClassTableName pod for  id=$advisorid<br />";
				}
			}
		}




////////  	Pass 10 	add an advisorClass record


	} elseif ("10" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 10 with inp_advisor_id=$inp_advisor_id, inp_sequence: $inp_sequence and inp_table=$inp_table<br />";
		}
		
		$content			.= "<h3>Add an advisorClass Record</h3>";
		////		get the advisor record
		$sql				= "select * from $advisorTableName 
								where advisor_id=$inp_advisor_id";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname pass 10 Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

				
					if ($doDebug) {
						echo "preping to add sequence $inp_sequence to $inp_advisor advisorClass records<br />";
					}

					$content	.= "<form method='post' name='selection_form' action='$theURL' 
									ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='11'>
									<input type='hidden' name='inp_table' value='$inp_table'>
									<input type='hidden' name='advisorid' value='$advisor_ID'>
									<input type='hidden' name='inp_sequence' value='$inp_sequence'>
									<input type='hidden' name='inp_advisor_call_sign' value='$advisor_call_sign'>
									<input type='hidden' name='inp_first_name' value='$advisor_first_name'>
									<input type='hidden' name='inp_last_name' value=\"$advisor_last_name\">
									<input type='hidden' name='inp_semester' value='$advisor_semester'>
									<input type='hidden' name='inp_timezone_id' value='$advisor_timezone_id'>
									<input type='hidden' name='inp_timezone_offset' value='$advisor_timezone_offset'>
									<input type='hidden' name='inp_verbose' value='inp_verbose'>
									<table style='border-collapse:collapse;'>
									<tr><th style='width:200px;'>Field</th>
										<th>Value</th></tr>
									<tr><td style='vertical-align:top;'>Call Sign</td>
										<td>$advisor_call_sign</td></tr>
									<tr><td style='vertical-align:top;'>AdvisorNew ID</td>
										<td>$advisor_ID</td></tr>
									<tr><td style='vertical-align:top;'>First Name</td>
										<td>$advisor_first_name</td></tr>
									<tr><td style='vertical-align:top;'>Last Name</td>
										<td>$advisor_last_name</td></tr>
									<tr><td style='vertical-align:top;'>Sequence</td>
										<td>$inp_sequence</td></tr>
									<tr><td style='vertical-align:top;'>Semester</td>
										<td>$advisor_semester</td></tr>
									<tr><td style='vertical-align:top;'>Level</td>
										<td><input type='radio' class='formInputButton' name='inp_level' value='Beginner' checked='checked'> Beginner<br />
											<input type='radio' class='formInputButton' name='inp_level' value='Fundamental'> Fundamental<br />
											<input type='radio' class='formInputButton' name='inp_level' value='Intermediate'> Intermediate<br />
											<input type='radio' class='formInputButton' name='inp_level' value='Advanced'> Advanced<br /></td></tr>
									<tr><td style='vertical-align:top;'>Class Size</td>
										<td><input class='formInputText' type='text' name='inp_class_size' size='5' maxlenth='5' value='6'></td></tr>
									<tr><td style='vertical-align:top;'>Class Schedule Days</td>
										<td><input type='radio' class='formInputButton' name='inp_class_schedule_days' value='Sunday,Wednesday'> Sunday and Wednesday<br />
											<input type='radio' class='formInputButton' name='inp_class_schedule_days' value='Sunday,Thursday'> Sunday and Thursday<br />
											<input type='radio' class='formInputButton' name='inp_class_schedule_days' value='Monday,Thursday' checked> Monday and Thursday<br />
											<input type='radio' class='formInputButton' name='inp_class_schedule_days' value='Tuesday,Friday'> Tuesday and Friday</td></tr>
									<tr><td style='vertical-align:top;'>Class Schedule Times</td>
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
									</table></td></tr>
									<tr><td colspan='2'><table>
										<tr><td>Student01: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student01'></td>
											<td>Student02: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student02'></td>
											<td>Student03: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student03'></td></tr>
										<tr><td>Student04: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student04'></td>
											<td>Student05: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student05'></td>
											<td>Student06: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student06'></td></tr>
										<tr><td>Student07: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student07'></td>
											<td>Student08: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student08'></td>
											<td>Student09: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student09'></td></tr>
										<tr><td>Student10: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student10'></td>
											<td>Student11: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student11'></td>
											<td>Student12: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student12'></td></tr>
										<tr><td>Student13: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student13'></td>
											<td>Student14: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student14'></td>
											<td>Student15: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student15'></td></tr>
										<tr><td>Student16: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student16'></td>
											<td>Student17: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student17'></td>
											<td>Student18: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student18'></td></tr>
										<tr><td>Student19: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student10'></td>
											<td>Student20: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student20'></td>
											<td>Student21: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student21'></td></tr>
										<tr><td>Student22: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student22'></td>
											<td>Student23: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student23'></td>
											<td>Student24: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student24'></td></tr>
										<tr><td>Student25: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student25'></td>
											<td>Student26: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student26'></td>
											<td>Student27: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student27'></td></tr>
										<tr><td>Student28: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student28'></td>
											<td>Student29: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student29'></td>
											<td>Student30: <input type='text' class='formInputText' size='20' maxlength='20' name='inp_student30'></td></tr>
									</table></td></tr>
									<tr><td>Evaluation Complete</td>
										<td><input type='text' class='formInputText' size='5' maxlength='5' name='inp_evaluation_complete'></td></tr>
									<tr><td>Class Comments</td>
										<td><textarea class='formInputText' name='inp_class_comments' cols='50' rows='5'></textarea></td></tr>
									<tr><td>Copy Control</td>
										<td><input type='text' class='formInputText' size='15' maxlength='15' name='inp_copy_control'></td></tr>
									<tr><td>&nbsp;</td>
										<td><input class='formInputButton' type='submit' value='Add AdvisorClass Record' /></td></tr>
									</table></form>
									<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$advisor_call_sign&inp_table=$inp_table&strpass=2'>HERE</a></p><br />"; 
				}			// end of the advisorClass while
			} else {
				$content	.= "<p>No record found in $advisorTableName for $inp_advisor_call_sign</p>";
			}
		}



	} elseif ("11" == $strPass) {				//// do the advisorClass add
		if ($doDebug) {
			echo "Arrived at pass 11 with inp_advisor_all_sign=$inp_advisor_call_sign and inp_table=$inp_table<br />";
		}
		
		$content				.= "<h3>Adding advisorClass Sequence $inp_sequence for Advisor $inp_advisor_call_sign</h3>";
		
		//// convert class days and times to UTC
		
		$result						= utcConvert('toutc',$inp_timezone_offset,$inp_times,$inp_class_schedule_days,$doDebug);
		if ($result[0] == 'FAIL') {
			if ($doDebug) {
				echo "utcConvert failed 'toutc',$inp_timezone_offset,$inp_class_schedule_times,$inp_class_schedule_days<br />
Error: $result[3]<br />";
			}
			$displayDays			= "<b>ERROR</b>";
			$displayTimes			= '';
		} else {
			$displayTimes			= $result[1];
			$displayDays			= $result[2];
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
			if (${'inp_student'.$strSnum} != '') {
				$myInt ++;
			}
		}
		$inp_number_students		= $myInt;

		$log_actionDate				= date('Y-m-d H:i:s');
		$inp_last_name				= addslashes($inp_last_name);
		$action_log					= "$log_actionDate Class record added by $userName using Display and Update Advisor Info ";
		$insertParams				= array("advisor_call_sign|$inp_advisor_call_sign|s",		// 0
										   "advisor_id|$advisorid|d",							// 1
										   "advisor_first_name|$inp_first_name|s",				// 2
										   "advisor_last_name|$inp_last_name|s",				// 3
										   "sequence|$inp_sequence|d",							// 4
										   "semester|$inp_semester|s",							// 5
										   "timezone_id|$inp_timezone_id|s",					// 6
										   "timezone_offset|$inp_timezone_offset|f",			// 7
										   "level|$inp_level|s",								// 8
										   "action_log|$action_log|s",							// 9
										   "class_size|$inp_class_size|d",						// 10
										   "class_schedule_days|$inp_class_schedule_days|s",	// 11
										   "class_schedule_times|$inp_times|s",					// 12
										   "class_schedule_days_utc|$displayDays|s",			// 13
										   "class_schedule_times_utc|$displayTimes|s",			// 14
											"student01|$inp_student01|s",						// 15
											"student02|$inp_student02|s",						// 16
											"student03|$inp_student03|s",						// 17
											"student04|$inp_student04|s",						// 18
											"student05|$inp_student05|s",						// 19
											"student06|$inp_student06|s",						// 20
											"student07|$inp_student07|s",						// 21
											"student08|$inp_student08|s",						// 22
											"student09|$inp_student09|s",						// 23
											"student10|$inp_student10|s",						// 24
											"student11|$inp_student11|s",						// 25
											"student12|$inp_student12|s",						// 26
											"student13|$inp_student13|s",						// 27
											"student14|$inp_student14|s",						// 28
											"student15|$inp_student15|s",						// 28
											"student16|$inp_student16|s",						// 30
											"student17|$inp_student17|s",						// 31
											"student18|$inp_student18|s",						// 32
											"student19|$inp_student19|s",						// 33
											"student20|$inp_student20|s",						// 34
											"student21|$inp_student21|s",						// 35
											"student22|$inp_student22|s",						// 36
											"student23|$inp_student23|s",						// 37
											"student24|$inp_student24|s",						// 38
											"student25|$inp_student25|s",						// 39
											"student26|$inp_student26|s",						// 40
											"student27|$inp_student27|s",						// 41
											"student28|$inp_student28|s",						// 42
											"student29|$inp_student29|s",						// 43
											"student30|$inp_student30|s",						// 44
											"number_students|$inp_number_students|d",			// 45
											"evaluation_complete|$inp_evaluation_complete|s",	// 46
											"class_comments|$inp_class_comments|s",				// 47
											"copy_control|$inp_copy_control|s");				// 48
		$classUpdateData		= array('tableName'=>$advisorClassTableName,
										'inp_method'=>'add',
										'inp_data'=>$insertParams,
										'jobname'=>$jobname,
										'inp_id'=>0,
										'inp_callsign'=>$inp_advisor_call_sign,
										'inp_semester'=>$inp_semester,
										'inp_who'=>$userName,
										'testMode'=>$testMode,
										'doDebug'=>$doDebug);
		$updateResult	= updateClass($classUpdateData);
		if ($updateResult[0] === FALSE) {
			$myError	= $wpdb->last_error;
			$mySql		= $wpdb->last_query;
			$errorMsg	= "$jobname  pass 11 Processing $advisor_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
			if ($doDebug) {
				echo $errorMsg;
			}
			sendErrorEmail($errorMsg);
			$content		.= "Unable to update content in $advisorClassTableName<br />";
		} else {
			$advisorClass_advisor_call_sign			= $inp_advisor_call_sign;
			$advisorClass_advisor_id				= $advisorid;
			$advisorClass_sequence					= $inp_sequence;
			$advisorClass_semester					= $inp_semester;
			$advisorClass_timezone					= $inp_time_zone;
			$advisorClass_level						= $inp_level;
			$advisorClass_class_size				= $inp_class_size;
			$advisorClass_class_schedule_days		= $inp_class_schedule_days;
			$advisorClass_class_schedule_times		= $inp_times;
			$advisorClass_class_schedule_days_utc	= $displayDays;
			$advisorClass_class_schedule_times_utc	= $displayTimes;
			$advisorClass_student01 				= $inp_student01;
			$advisorClass_student02 				= $inp_student02;
			$advisorClass_student03 				= $inp_student03;
			$advisorClass_student04 				= $inp_student04;
			$advisorClass_student05 				= $inp_student05;
			$advisorClass_student06 				= $inp_student06;
			$advisorClass_student07 				= $inp_student07;
			$advisorClass_student08 				= $inp_student08;
			$advisorClass_student09 				= $inp_student09;
			$advisorClass_student10 				= $inp_student10;
			$advisorClass_student11 				= $inp_student11;
			$advisorClass_student12 				= $inp_student12;
			$advisorClass_student13 				= $inp_student13;
			$advisorClass_student14 				= $inp_student14;
			$advisorClass_student15 				= $inp_student15;
			$advisorClass_student16 				= $inp_student16;
			$advisorClass_student17 				= $inp_student17;
			$advisorClass_student18 				= $inp_student18;
			$advisorClass_student19 				= $inp_student19;
			$advisorClass_student20 				= $inp_student20;
			$advisorClass_student21 				= $inp_student21;
			$advisorClass_student22 				= $inp_student22;
			$advisorClass_student23 				= $inp_student23;
			$advisorClass_student24 				= $inp_student24;
			$advisorClass_student25 				= $inp_student25;
			$advisorClass_student26 				= $inp_student26;
			$advisorClass_student27 				= $inp_student27;
			$advisorClass_student28 				= $inp_student28;
			$advisorClass_student29 				= $inp_student29;
			$advisorClass_student30 				= $inp_student30;
			$advisorClass_evaluation_complete		= $inp_evaluation_complete;
			$advisorClass_number_students			= $inp_number_students;
			$advisorClass_class_comments			= $inp_class_comments;
			$copycontrol							= $inp_copy_control;
			$newid									= $wpdb->insert_id;

			// Now display the class record
			$content	.= "<b>Class $inp_sequence:</b>
							<p><a href='$theURL'>Display another advisor</a></p>
							<table style='width:600px;'>
							<tr><td style='width:250px;'><b>Level</b></td>
								<td>$advisorClass_level</td></tr>
							<tr><td><b>Class Size</b></td>
								<td>$advisorClass_class_size</td></tr>
							<tr><td><b>Class Schedule Days</b></td>
								<td>$advisorClass_class_schedule_days</td></tr>
							<tr><td><b>Class Schedule Time<b></td>
								<td>$advisorClass_class_schedule_times</td></tr>
							<tr><td><b>Class Schedule Days UTC</b></td>
								<td>$advisorClass_class_schedule_days_utc</td></tr>
							<tr><td><b>Class Schedule Times UTC<b></td>
								<td>$advisorClass_class_schedule_times_utc</td></tr>
							<tr><td colspan='2'><table>
								<tr><td>Student01: $advisorClass_student01</td>
									<td>Student02: $advisorClass_student02</td>
									<td>Student03: $advisorClass_student03</td></tr>
								<tr><td>Student04: $advisorClass_student04</td>
									<td>Student05: $advisorClass_student05</td>
									<td>Student06: $advisorClass_student06</td></tr>
								<tr><td>Student07: $advisorClass_student07</td>
									<td>Student08: $advisorClass_student08</td>
									<td>Student09: $advisorClass_student09</td></tr>
								<tr><td>Student10: $advisorClass_student10</td>
									<td>Student11: $advisorClass_student11</td>
									<td>Student12: $advisorClass_student12</td></tr>
								<tr><td>Student13: $advisorClass_student13</td>
									<td>Student14: $advisorClass_student14</td>
									<td>Student15: $advisorClass_student15</td></tr>
								<tr><td>Student16: $advisorClass_student16</td>
									<td>Student17: $advisorClass_student17</td>
									<td>Student18: $advisorClass_student18</td></tr>
								<tr><td>Student19: $advisorClass_student19</td>
									<td>Student20: $advisorClass_student20</td>
									<td>Student21: $advisorClass_student21</td></tr>
								<tr><td>Student22: $advisorClass_student22</td>
									<td>Student23: $advisorClass_student23</td>
									<td>Student24: $advisorClass_student24</td></tr>
								<tr><td>Student25: $advisorClass_student25</td>
									<td>Student26: $advisorClass_student26</td>
									<td>Student27: $advisorClass_student27</td></tr>
								<tr><td>Student28: $advisorClass_student28</td>
									<td>Student29: $advisorClass_student29</td>
									<td>Student30: $advisorClass_student30</td></tr>
									</table></td></tr>
							<tr><td>Number Students</td>
								<td>$advisorClass_number_students</td></tr>
							<tr><td>Evaluation Complete</td>
								<td>$advisorClass_evaluation_complete</td></tr>
							<tr><td>Class Comments</td>
								<td>$advisorClass_class_comments</td></tr>
							<tr><td>Copy Control</td>
								<td>$copycontrol</td></tr>
							</table>
							<p>To return to the advisor screen, click <a href='$theURL?request_type=callsign&request_info=$inp_advisor_call_sign&inp_table=$inp_table&strpass=2'>HERE</a></p><br />";
		}

	
	}
	$content					.= "<br /><p><a href='$theURL'>Display another advisor</a></p>";
	$thisTime 					= date('Y-m-d H:i:s');
	$content					.= "<br /><br /><br /><p>Report displayed at $thisTime.</p>";
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
	$result			= write_joblog_func("Display and Update Advisor Info|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('display_and_update_advisor_info', 'display_and_update_advisor_info_func');
