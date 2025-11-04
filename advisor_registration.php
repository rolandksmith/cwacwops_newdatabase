function advisor_registration_func() {

/*	Advisor Sign-up
 *
 *
*/

	global $wpdb, $advisorTableName, $advisorClassTableName, $userMasterTableName, 
		   $daysToSemester, $doDebug,$testMode,$theURL, $allowSignup, $siteURL;


	$doDebug					= FALSE;
	$testMode					= FALSE;
	$maintenanceMode			= FALSE;
	$initializationArray 		= data_initialization_func();
	$validUser 					= $initializationArray['validUser'];
	$defaultClassSize			= $initializationArray['defaultClassSize'];
	$currentTimestamp			= $initializationArray['currentTimestamp'];
	$validTestmode				= $initializationArray['validTestmode'];
	$userName					= $initializationArray['userName'];
	$currentSemester			= $initializationArray['currentSemester'];
	$prevSemester				= $initializationArray['prevSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$semesterFour				= $initializationArray['semesterFour'];
	$daysToSemester				= intval($initializationArray['daysToSemester']);
	$userRole					= $initializationArray['userRole'];
	$siteURL					= $initializationArray['siteurl'];
	$languageArray				= $initializationArray['languageArray'];
	$allowSignup				= FALSE;
	
	if ($userName == '') {
		$content				= "You are not authorized";
		return $content;
	}

	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}


//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass						= "1";
	$inp_recdel						= '';
	$inp_mode						= '';
	$inp_verbose					= '';
	$versionNumber					= '4';
	$fieldTest						= array('action_log','post_status','post_title','control_code');
	$jobname						= "Advisor Registration V$versionNumber";

	$user_ID 							= '';
	$user_call_sign						= '';
	$user_first_name 					= '';
	$user_last_name 					= '';
	$user_email 						= '';
	$user_phone 						= '';
	$user_city 							= '';
	$user_state 						= '';
	$user_zip_code 						= '';
	$user_country_code 					= '';
	$user_whatsapp 					= '';
	$user_telegram 					= '';
	$user_signal 					= '';
	$user_messenger 				= '';
	$user_action_log 					= '';
	$user_timezone_id 					= '';
	$user_languages 					= '';
	$user_survey_score 					= '';
	$user_is_admin						= '';
	$user_role 							= '';
	$user_date_created 					= '';
	$user_date_updated 					= '';

	$advisor_ID							= '';
	$advisor_call_sign 					= '';
	$advisor_semester 					= '';
	$advisor_welcome_email_date 		= '';
	$advisor_verify_email_date 			= '';
	$advisor_verify_email_number 		= '';
	$advisor_verify_response 			= '';
	$advisor_action_log 				= '';
	$advisor_class_verified 			= '';
	$advisor_control_code 				= '';
	$advisor_date_created 				= '';
	$advisor_date_updated 				= '';
	$advisor_replacement_status 		= '';



	$increment						=   0;
	$timezone						= 	"";

	
	
	$inp_ID 						=   '';   // id
	$inp_post_title 				=   '';   // post_title
	$inp_select_sequence 			=   '';   // select_sequence
	$inp_callsign 					=   '';   // call_sign
	$inp_firstname 					=   '';   // first_name
	$inp_lastname 					=   '';   // last_name
	$inp_email 						=   '';   // email
	$inp_ph_code					=	'';
	$inp_phone 						=   '';   // phone
	$inp_text_message				=	'';   // text message
	$inp_city 						=   '';   // city
	$inp_state 						=   '';   // state
	$inp_zip 						=   '';   // zip_code
	$inp_country 					=   '';   // country
	$inp_countryb					= 	'';
	$inp_country_code				=	'';
	$inp_timezone 					=   '';   // time_zone
	$inp_timezone_id				=	'';
	$inp_timezone_offset			=	'';
	$inp_whatsapp					=	'';
	$inp_signal						=	'';
	$inp_telegram					=	'';
	$inp_messenger					=	'';
	$inp_semester 					=   '';   // semester
	$inp_survey_score 				=   '';   // survey_score
	$inp_languages_spoken 			=   '';   // languages
	$inp_fifo_date 					=   '';   // fifo_date
	$inp_welcome_email_date 		=   '';   // welcome_email_date
	$inp_verify_email_date 			=   '';   // verify_email_date
	$inp_verify_email_number 		=   '';   // verify_email_number
	$inp_verify_response 			=   '';   // verify_response
	$inp_action_log 				=   '';   // action_log
	$inp_class_verified 			=   '';   // class_verified
	$inp_evaluations_complete		=   '';   // evaluations_complete
	$inp_control_code				= 	''; 
	$inp_level	 					= '';
	$inp_teaching_days	 			= '';
	$inp_times	 					= '';
	$inp_class_size 				= '';
	$inp_bypass						= 'N';
	$browser_timezone_id			= '';
	$advisorClass_class_incomplete	= '';
	$noUpdate						= FALSE;
	$newID							= 0;
	$theURL									= "$siteURL/cwa-advisor-registration/";
	$actionDate								= date('dMy H:i',$currentTimestamp);
	$log_actionDate							= date('Y-m-d H:i',$currentTimestamp);	
	$pageSource								= "";
	
	
	$inp_classdel					= '';
	$inp_nbrClasses					= 0;
	$classID						= 0;



	

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
			if ($str_key 				== 'enstr') {
				$enstr					= $str_value;
				$stringToPass			= base64_decode($enstr);
				$myArray				= explode("&",$stringToPass);
				foreach($myArray as $myValue) {
					$thisArray			= explode("=",$myValue);
					${$thisArray[0]}	= filter_var($thisArray[1],FILTER_UNSAFE_RAW);
					if ($doDebug) {
						echo "ENC Key: $thisArray[0] | Value: $thisArray[1]<br />\n";
					}
					if ($thisArray[0] == 'semester') {
						$allowSignup	= TRUE;
						$inp_semester	= $thisArray[1];
						if ($doDebug) {
							echo "allowSignup is TRUE<br />inp_semester set to $inp_semester<br />";
						}
					}
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 	= $str_value;
				$inp_mode	 	= filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
//					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "classID") {
				$classID	 = $str_value;
				$classID	 = filter_var($classID,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "newID") {
				$newID	 		= $str_value;
				$newID	 		= filter_var($newID,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "classcount") {
				$classcount	 = $str_value;
				$classcount	 = filter_var($classcount,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_recdel") {
				$inp_recdel	 = $str_value;
				$inp_recdel	 = filter_var($inp_recdel,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "call_sign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key == "inp_firstname") {
				$inp_firstname 	= $str_value;
				$inp_firstname 	= filter_var($inp_firstname,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_lastname") {
				$inp_lastname 		= $str_value;
				$inp_lastname		= no_magic_quotes($inp_lastname);
//				$inp_lastname 		= filter_var($inp_lastname,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_email") {
				$inp_email 			= $str_value;
				$inp_email 			= filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_phone") {
				$inp_phone 			= $str_value;
				$inp_phone 			= filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_ph_code") {
				$inp_ph_code 			= $str_value;
				$inp_ph_code 			= filter_var($inp_ph_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_text_message") {
				$inp_text_message 			= $str_value;
				$inp_text_message 			= filter_var($inp_text_message,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_city") {
				$inp_city 			= $str_value;
				$inp_city 			= filter_var($inp_city,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_state") {
				$inp_state 			= $str_value;
				$inp_state 			= filter_var($inp_state,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_zip") {
				$inp_zip 			= $str_value;
				$inp_zip 			= filter_var($inp_zip,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_country") {
				$inp_country 		= $str_value;
				$inp_country 		= filter_var($inp_country,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_countryb") {
				$inp_country 		= $str_value;
				$inp_country 		= filter_var($inp_country,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_country_code") {
				$inp_country_code 			= $str_value;
				$inp_country_code 			= filter_var($inp_country_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_timezone") {
				$inp_timezone 		= $str_value;
				$inp_timezone 		= filter_var($inp_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_timezone_id") {
				$inp_timezone_id 			= $str_value;
				$inp_timezone_id 			= filter_var($inp_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_timezone_offset") {
				$inp_timezone_offset 			= $str_value;
				$inp_timezone_offset 			= filter_var($inp_timezone_offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_whatsapp") {
				$inp_whatsapp 			= $str_value;
				$inp_whatsapp 			= filter_var($inp_whatsapp,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_signal") {
				$inp_signal 			= $str_value;
				$inp_signal 			= filter_var($inp_signal,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_telegram") {
				$inp_telegram 			= $str_value;
				$inp_telegram 			= filter_var($inp_telegram,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_messenger") {
				$inp_messenger 			= $str_value;
				$inp_messenger 			= filter_var($inp_messenger,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_languages") {
				$inp_languages 		= $str_value;
				$inp_languages 		= filter_var($inp_languages,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_number_classes") {
				$inp_number_classes = $str_value;
				$inp_number_classes = filter_var($inp_number_classes,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_id") {
				$inp_id = $str_value;
				$inp_id = filter_var($inp_id,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_level") {
				$inp_level = $str_value;
				$inp_level = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_teaching_days") {
				$inp_teaching_days = $str_value;
				$inp_teaching_days = filter_var($inp_teaching_days,FILTER_UNSAFE_RAW);
				$inp_class_schedule_days 	= $inp_teaching_days;
			}
			if($str_key == "inp_class_schedule_days") {
				$inp_class_schedule_days = $str_value;
				$inp_class_schedule_days = filter_var($inp_class_schedule_days,FILTER_UNSAFE_RAW);
				$inp_teaching_days 	= $inp_class_schedule_days;
			}
			if($str_key == "inp_times") {
				$inp_times = $str_value;
				$inp_times = filter_var($inp_times,FILTER_UNSAFE_RAW);
				$inp_class_schedule_times	= $inp_times;
			}
			if($str_key == "inp_class_schedule_times") {
				$inp_class_schedule_times = $str_value;
				$inp_class_schedule_times = filter_var($inp_class_schedule_times,FILTER_UNSAFE_RAW);
				$inp_times	= $inp_class_schedule_times;
			}
			if($str_key == "inp_class_size") {
				$inp_class_size = $str_value;
				$inp_class_size = filter_var($inp_class_size,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_advisorclass_language") {
				$inp_advisorclass_language = $str_value;
				$inp_advisorclass_language = filter_var($inp_advisorclass_language,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_classdel") {
				$inp_classdel = $str_value;
				$inp_classdel = filter_var($inp_classdel,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_classrecdel") {
				$inp_classrecdel = $str_value;
				$inp_classrecdel = filter_var($inp_classrecdel,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_advisor_id") {
				$inp_advisor_id = $str_value;
				$inp_advisor_id = filter_var($inp_advisor_id,FILTER_UNSAFE_RAW);
			}
			if($str_key == "inp_nbrClasses") {
				$inp_nbrClasses = $str_value;
				$inp_nbrClasses = filter_var($inp_nbrClasses,FILTER_UNSAFE_RAW);
			}
			if($str_key == "timezone") {
				$timezone = $str_value;
				$timezone = filter_var($timezone,FILTER_UNSAFE_RAW);
				$browser_timezone_id	= $timezone;
			}
			if($str_key == "inp_bypass") {
				$inp_bypass = $str_value;
				$inp_bypass = filter_var($inp_bypass,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'browser_timezone_id') {
				$browser_timezone_id = $str_value;
				$browser_timezone_id = filter_var($browser_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'pageSource') {
				$pageSource = $str_value;
				$pageSource = filter_var($pageSource,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'allowSignup') {
				$allowSignup = $str_value;
				$allowSignup = filter_var($allowSignup,FILTER_UNSAFE_RAW);
			}
		}
	}
	if ($inp_mode		== 'TESTMODE' || $inp_mode == 'tm') {
		$testMode		= TRUE;
	}
	if ($inp_verbose == 'Y') {
		$doDebug		= TRUE;
	}


function getAdvisorInfoToDisplay($inp_callsign,$inp_semester,$noUpdate) {

	global $wpdb, $advisorTableName, $advisorClassTableName, $userMasterTableName, 
	$doDebug, $testMode, $theURL, $allowSignup, $siteURL;

	if ($doDebug) {
		echo "<br />Entered getAdvisorInfoToDisplay with $inp_callsign and $inp_semester<br />";
	}

	$result					= '';
	$inp_nbrClasses			= 0;
	$classcount				= 0;
	$daysToSemester			= days_to_semester($inp_semester);
	$rtnResult				= "";
	$doProceed				= TRUE;
	
	// get the advisor and user_master info
	$sql				= "select * from $advisorTableName 
							left join $userMasterTableName on user_call_sign = advisor_call_sign 
							where advisor_call_sign ='$inp_callsign' 
								and advisor_semester ='$inp_semester'";
	$wpw1_cwa_advisor	= $wpdb->get_results($sql);
	if ($wpw1_cwa_advisor === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {
		$numARows			= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
		}
		if ($numARows > 0) {
			foreach ($wpw1_cwa_advisor as $advisorRow) {
				$user_ID 							= $advisorRow->user_ID;
				$user_call_sign						= $advisorRow->user_call_sign;
				$user_first_name 					= $advisorRow->user_first_name;
				$user_last_name 					= $advisorRow->user_last_name;
				$user_email 						= $advisorRow->user_email;
				$user_ph_code 						= $advisorRow->user_ph_code;
				$user_phone 						= $advisorRow->user_phone;
				$user_city 							= $advisorRow->user_city;
				$user_state 						= $advisorRow->user_state;
				$user_zip_code 						= $advisorRow->user_zip_code;
				$user_country_code 					= $advisorRow->user_country_code;
				$user_country	 					= $advisorRow->user_country;
				$user_whatsapp 						= $advisorRow->user_whatsapp;
				$user_telegram 						= $advisorRow->user_telegram;
				$user_signal 						= $advisorRow->user_signal;
				$user_messenger 					= $advisorRow->user_messenger;
				$user_action_log 					= $advisorRow->user_action_log;
				$user_timezone_id 					= $advisorRow->user_timezone_id;
				$user_languages 					= $advisorRow->user_languages;
				$user_survey_score 					= $advisorRow->user_survey_score;
				$user_is_admin						= $advisorRow->user_is_admin;
				$user_role 							= $advisorRow->user_role;
				$user_date_created 					= $advisorRow->user_date_created;
				$user_date_updated 					= $advisorRow->user_date_updated;

				$advisor_ID							= $advisorRow->advisor_id;
				$advisor_call_sign 					= strtoupper($advisorRow->advisor_call_sign);
				$advisor_semester 					= $advisorRow->advisor_semester;
				$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
				$advisor_verify_email_date 			= $advisorRow->advisor_verify_email_date;
				$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
				$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
				$advisor_action_log 				= $advisorRow->advisor_action_log;
				$advisor_class_verified 			= $advisorRow->advisor_class_verified;
				$advisor_control_code 				= $advisorRow->advisor_control_code;
				$advisor_date_created 				= $advisorRow->advisor_date_created;
				$advisor_date_updated 				= $advisorRow->advisor_date_updated;
				$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;


				$rtnResult	.= "<h4>User Master Data</h4>
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
									<td><b>Date Updated</b><br />$user_date_updated</td>
									<td></td></tr></table>";
				if ($user_timezone_id == 'XX') {
					$rtnResult	.= "<p><b>CRITICAL!</b> There are multiple timezones for your address area. 
					Please click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$inp_callsign&inp_depth=one&doDebug=$doDebug&testMode=$testMode' target='_blank'>HERE</a> 
					to verify your information and select the most appropriate timezone ID for where you live. Then close that tab 
					and refresh this tab.</p>";
					$doProceed	= FALSE;
				} else {
					$rtnResult	.= "<p>Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$inp_callsign&inp_depth=one&doDebug=$doDebug&testMode=$testMode' target='_blank'>HERE</a> to update the advisor Master Data</p>";
				}
	
				if ($doProceed) {
					if ($doDebug) {
						echo "<br />Have an advisor record for $inp_callsign<br />";
					}
					
					$inp_mode				= '';
					if ($testMode) {
						$inp_mode			= 'TESTMODE';
					}
					$inp_verbose			= 'N';
					if ($doDebug) {
						$inp_verbose		= 'Y';
					}
					$stringToPass	= "inp_callsign=$advisor_call_sign&inp_mode=$inp_mode&inp_verbose=$inp_verbose&allowSignup=$allowSignup&inp_semester=$inp_semester";
					$enstr			= base64_encode($stringToPass);
					$rtnResult			.= "<p>You have signed up as an advisor. 
											Your current information is as follows.</p>";
					if (!$noUpdate) {
						$rtnResult	.= "<p>To update, delete, or add information, click 
											on the appropriate link</p>
										<p><button class='formInputButton' onClick=\"window.print()\">Print this page</button></p>";
					}
					$rtnResult		.= "<table style='width:900px;'>
										<tr><td><b>Advisor:</b> $inp_callsign</td>
											<td><b>Semester:</b> $advisor_semester</td></tr>";
					if (!$noUpdate) {					/// can update the record												
						$rtnResult	.= "<tr><td><form method='post' action='$theURL' 
												name='advisor_option2_form' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='enstr' value='$enstr'>
												<input type='hidden' name='strpass' value='20'>
												<input class='formInputButton' type='submit' 
												onclick=\"return confirm('Are you sure you want to delete this the advisor and class records?');\" value='Delete Advisor and Classes'>
												</form></td>
											<td></td></tr></table>";
					} else {
						if ($doDebug) {
							echo "not allowing updates to the advisor record<br />";
						}
						$rtnResult	.= "</table>";
					}
	
					$sql						= "select * from $advisorClassTableName 
													where advisorclass_call_sign='$inp_callsign' 
													  and advisorclass_semester='$inp_semester' 
													order by advisorclass_sequence";
					$cwa_advisorclass			= $wpdb->get_results($sql);
					if ($cwa_advisorclass === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numACRows				= $wpdb->num_rows;
						if ($doDebug) {
							$myStr				= $wpdb->last_query;
							echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName<br />";
						}
						$firstTime										= TRUE;
						if ($numACRows > 0) {
							foreach ($cwa_advisorclass as $advisorClassRow) {
								$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
								$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
								$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
								$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
								$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
								$advisorClass_level 					= $advisorClassRow->advisorclass_level;
								$advisorClass_language					= $advisorClassRow->advisorclass_language;
								$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
								$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
								$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
								$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
								$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
								$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
								$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
								$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
								$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
	
								// close out previous record??
								if ($advisorClass_sequence > 1) {
									$rtnResult				.= "<td></td></table>";
								}
	
								$myInt						= $advisorClass_sequence;
								if ($doDebug) {
									echo "Found a class sequence $advisorClass_sequence<br />";
			
								}
								if ($advisorClass_class_incomplete == 'Y') {
									$thisIncomplete	= "<td><b>Class Incomplete</b><br />
														The information for this class is incomplete. Please update this class record</td></tr>";
								} else {
									$thisIncomplete = "";
								}
								$rtnResult			.= "<br /><b>Class $advisorClass_sequence:</b>
														<table style='width:900px;'>
														<tr><td style='vertical-align:top;width:300px;'><b>Level</b><br />
																$advisorClass_level</td>
															<td style='vertical-align:top;'><b>Class Size</b><br />
																$advisorClass_class_size</td>
															<td><b>Class Language</b><br />$advisorClass_language</td></tr>
														<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />
																$advisorClass_class_schedule_days</td>
															<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />
																$advisorClass_class_schedule_times</td>
															<td>$thisIncomplete</td></tr>";
								$inp_nbrClasses++;
								$classcount		= $inp_nbrClasses + 1;
								if ($doDebug) {
									echo "Data at this point:<br />
											daysToSemester: $daysToSemester<br />
											inp_nbrClasses: $inp_nbrClasses<br/>
											classcount: $classcount<br />";
								}
								if (!$noUpdate) {
									$rtnResult	.= "<tr><td><form method='post' action='$theURL' 
															name='class_option1_form' ENCTYPE='multipart/form-data'>
															<input type='hidden' name='enstr' value='$enstr'>
															<input type='hidden' name='classID' value='$advisorClass_sequence'>
															<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
															<input type='hidden' name='strpass' value='15'>
															<input class='formInputButton' type='submit' value='Modify this Class'>
															</form></td>
													<td><form method='post' action='$theURL' 
														name='class_option2_form' ENCTYPE='multipart/form-data'>
														<input type='hidden' name='enstr' value='$enstr'>
														<input type='hidden' name='classID' value='$advisorClass_ID'>
														<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
														<input type='hidden' name='strpass' value='17'>
														<input class='formInputButton' type='submit' value='Delete this Class'>
														</form></td>";
								} else {
									if ($doDebug) {
										echo "Not allowing update to the class record<br />";
									}
									$rtnResult	.= "</table>";
								}
							}
							if (!$noUpdate) {
								$rtnResult		.= "<td><form method='post' action='$theURL' 
														name='class_option2_form' ENCTYPE='multipart/form-data'>
														<input type='hidden' name='enstr' value='$enstr'>
														<input type='hidden' name='classID' value='$advisorClass_ID'>
														<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
														<input type='hidden' name='strpass' value='5'>
														<input type='hidden' name='classcount' value='$classcount'>
														<input class='formInputButton' type='submit' value='Add Another Class'>
														</form></td></tr></table>";
								$rtnResult			.= "<p>You may close this window</p>";
							} else {
								$rtnResult		.= "<p><form method='post' action='$theURL' 
														name='class_option2_form' ENCTYPE='multipart/form-data'>
														<input type='hidden' name='enstr' value='$enstr'>
														<input type='hidden' name='classID' value='$advisorClass_ID'>
														<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
														<input type='hidden' name='strpass' value='5'>
														<input type='hidden' name='classcount' value='$classcount'>
														<input class='formInputButton' type='submit' value='Add Another Class'>
														</form></p><br />";
								$rtnResult			.= "<br /><p>You may close this window</p>";
							
							}
						} else {
							if ($doDebug) {
								echo "<b>ERROR:</b> There should be at least 1 advisorclass record for $inp_callsign<br />";
							}
							$rtnResult			.= "<td></td></table>
												<p>You have not specified any classes. Please click 'Add a Class' and enter the appropriate information.</p>
												<form method='post' action='$theURL' 
													name='class_option2_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='enstr' value='$enstr'>
													<input type='hidden' name='inp_callsign' value='$advisor_call_sign'>
													<input type='hidden' name='strpass' value='5'>
													<input type='hidden' name='classcount' value='$classcount'>
													<input class='formInputButton' type='submit' value='Add a Class'>
													</form>";
						}
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "<b>FATAL ERROR:</b> Can not find advisor record for $inp_callsign<br />";
			}
		}
	}
	return array($rtnResult,$classcount);
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
				
				button.formInputButton {vertical-align:middle;font-weight:bolder;
				text-align:center;color:#300;background:#f99;padding:1px;border:solid 1px #f66;
				cursor:pointer;position:relative;float:left;}
				
				input.formInputButton:hover {color:#f8f400;}
				
				input.formInputButton:active {color:#00ffff;}
				
				td {color:#666;background:#fee;padding:2px;
				margin-right:5px;margin-bottom:5px;}
				
				tr {color:#333;background:#eee;}
				
				table{font:'Times New Roman', sans-serif;background-image:none;border-collapse:collapse;background:#fee;}
				
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
		$advisorTableName				= "wpw1_cwa_advisor2";
		$advisorClassTableName			= "wpw1_cwa_advisorclass2";
		$advisorDeletedTableName		= "wpw1_cwa_advisor_deleted2";
		$advisorClassDeletedTableName 	= "cwa_deleted_advisorclass2";
		$userMasterTableName			= "wpw1_cwa_user_master2";
	} else {
		$advisorTableName				= "wpw1_cwa_advisor";
		$advisorClassTableName			= "wpw1_cwa_advisorclass";
		$advisorDeletedTableName		= "wpw1_cwa_advisor_deleted";
		$advisorClassDeletedTableName 	= "wpw1_cwa_deleted_advisorclass";
		$userMasterTableName			= "wpw1_cwa_user_master";
	}

	$testModeOption	= '';
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	 = "<tr><td>Operation Mode</td>
								<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
									<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	}


	if ("1" == $strPass) {
		if ($maintenanceMode) {
			$content	.= "<p><b>The Advisor Sign-up process is currently undergoing 
							maintenance. That should be completed within the next hour. Please come back at that 
							time to sign up.</b></p>";
		} else {
			if ($doDebug) {
				echo "<br />Function starting.<br />";
			}
			// set the userName
			if ($userRole == 'advisor') {
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
									<tr><td>Advisor Call Sign</td>
										<td><input type='text' class='formInputText' name='inp_callsign' size='10' maxlength='10' value='$inp_callsign' autofocus></td>
									$testModeOption
									<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
									</form>";


			} else {
				$content			.= "You are not authorized";
			}
		}
	}


///// Pass 2 -- do the work


	if ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass $strPass with callsign: $inp_callsign and inp_semester: $inp_semester<br />";
		}
		$userName					= $inp_callsign;		
		
		$haveAdvisorRecord	= FALSE;
		$doProceed			= TRUE;

		$content 			.= "<h3>$jobname</h3>";	
		$sql				= "select * from $userMasterTableName 
								where user_call_sign = '$inp_callsign'";
		$sqlResult			= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"Pass 2 attempting to read user_master for $inp_callsign");
			$content		.= "<b>FATAL ERROR</b> The sysadmin has been notified.";
		} else {
			$numRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_call_sign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_ph_code			= $sqlRow->user_ph_code;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_country			= $sqlRow->user_country;
					$user_whatsapp			= $sqlRow->user_whatsapp;
					$user_telegram			= $sqlRow->user_telegram;
					$user_signal			= $sqlRow->user_signal;
					$user_messenger			= $sqlRow->user_messenger;
					$user_action_log		= $sqlRow->user_action_log;
					$user_timezone_id		= $sqlRow->user_timezone_id;
					$user_languages			= $sqlRow->user_languages;
					$user_survey_score		= $sqlRow->user_survey_score;
					$user_is_admin			= $sqlRow->user_is_admin;
					$user_role				= $sqlRow->user_role;
					$user_prev_callsign		= $sqlRow->user_prev_callsign;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;
									
			
					if (!$allowSignup) {			// if allowSignup is true, have come here from evaluate_student
						// See if there is already a record for the requested semester
						// if so, go into modify mode. Otherwise, see if the advisor can signup
						$sql					= "select * from $advisorTableName 
													where advisor_call_sign='$inp_callsign' 
													and (advisor_semester = '$currentSemester' 
													or advisor_semester = '$nextSemester' 
													or advisor_semester = '$semesterTwo' 
													or advisor_semester = '$semesterThree' 
													or advisor_semester = '$semesterFour')";
						$cwa_advisor			= $wpdb->get_results($sql);
						if ($cwa_advisor === FALSE) {
							handleWPDBError($jobname,$doDebug);
							$doProceed			= FALSE;
						} else {
							$numARows			= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
							}
							if ($numARows > 0) {
								foreach ($cwa_advisor as $advisorRow) {
									$advisor_ID							= $advisorRow->advisor_id;
									$advisor_call_sign 					= strtoupper($advisorRow->advisor_call_sign);
									$advisor_semester 					= $advisorRow->advisor_semester;
									$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
									$advisor_verify_email_date 			= $advisorRow->advisor_verify_email_date;
									$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
									$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
									$advisor_action_log 				= $advisorRow->advisor_action_log;
									$advisor_class_verified 			= $advisorRow->advisor_class_verified;
									$advisor_control_code 				= $advisorRow->advisor_control_code;
									$advisor_date_created 				= $advisorRow->advisor_date_created;
									$advisor_date_updated 				= $advisorRow->advisor_date_updated;
									$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;
				
									$inp_semester						= $advisor_semester;
									
									$noUpdate							= FALSE;
									$doProceed							= TRUE;			
									if ($doDebug) {
										echo "<br />Have an advisor record for $inp_callsign in semester $advisor_semester. <br />";
									}
				
									if ($advisor_class_verified == 'R') {
										$doProceed			= FALSE;
										if ($doDebug) {
											echo "class_verified is R. Take no action<br />";
										}
										$content		.= "You need to contact the appropriate person at 
															<a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA 
															Class Resolution</a><br />";
									}
									if ($doProceed) {
										$haveAdvisorRecord	= TRUE;				/// go into modify mode
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;set haveAdvisorRecord true<br />";
										}
										$daysToGo				= days_to_semester($inp_semester);
										if ($daysToGo < 0) {
											if ($doDebug) {
												echo "daysToGo is negative. No update allowed<br />";
											}
											$noUpdate			= TRUE;
										}
										if($daysToGo > 0 && $daysToGo < 21) {
											if ($doDebug) {
												echo "daysToGo to $inp_semester semester is $daysToGo and no update allowed<br />";
											}
											$noUpdate			= TRUE;
										}						
									}
								}
							}
						}
						if ($doProceed) {
							if ($doDebug) {
								echo "advisor records?<br />";
								if ($haveAdvisorRecord) {
									echo "haveAdvisorRecord: TRUE<br />";
								} else {
									echo "haveAdvisorRecord: FALSE<br />";
								}
							}
						}
					} else {
						$haveAdvisorRecord		= FALSE;
						if ($doDebug) {
							echo "came here from evaluate_student. Proceeding directly to signup<br />";
						}
					}
		/*
			If haveAdvisorRecord then the advisor has already signed up for the current or a 
			future semester. Do the modify and update procedure.
			
			If no record, then do the signup process	
		*/
		
					// doing the no record process
					if (!$haveAdvisorRecord) {			/// determine if the advisor can signup
						/// don't allow to signup if a bad actor
						if ($user_survey_score == '6') {		// bad actor
							$doProceed			= FALSE;
							$content		.= "You need to contact the appropriate person at 
												<a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CWA 
												Class Resolution</a> before you can sign up as an advisor<br />";
							if ($doDebug) {
								echo "Survey score of six. Take no action<br />";
							}
						} else {
							// get the advisorclass records for the most recent semester
							$checkSemester			= $currentSemester;
							if ($currentSemester == 'Not in Session') {
								$checkSemester		= $prevSemester;
							}
							$allComplete			= TRUE;
							$sql					= "select advisorclass_evaluation_complete 
														from $advisorClassTableName 
														where advisorclass_semester = '$checkSemester' 
														and advisorclass_call_sign = '$inp_callsign'";
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
										$advisorClass_evaluation_complete		= $advisorClassRow->advisorclass_evaluation_complete;
									
										if ($advisorClass_evaluation_complete != 'Y') {
											$allComplete		= FALSE;
											if ($doDebug) {
												echo "evaluations not complete. allComplete set to FALSE<br />";
											}
											$content				.= "<h3>$jobname</h3>
																		<p>You need to complete the promotability evaluations 
																		for your students in the $checkSemester semester 
																		before you can register for a future semester. The 
																		instructions on how to do that should be on your 
																		Advisor Portal. If that reminder has expired, please contact 
																		the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Contact</a>.</p>";
											$doProceed				= FALSE;
										}
									}
								}
							}
							
							if ($doProceed) {
								// show the user_master info and the link to update it. Then show the advisor form
								$content		.= "<h4>Advisor Master Data</h4>
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
										<td><b>Date Updated</b><br />$user_date_updated</td>
										<td></td></tr>
									<td></td></tr>
									</table>";

								//Build language selection
								$languageOptions			= '';
								$firstTime					= TRUE;
								$languageChecked			= '';
								foreach($languageArray as $thisLanguage) {
//									if ($thisLanguage == $advisorClass_language) {
//										$languageChecked	= 'checked';
//									} else {
//										$languageCHecked	= '';
//									}
									if ($firstTime) {
										$firstTime			= FALSE;
										$languageOptions		.= "<input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage' $languageChecked required>$thisLanguage";
									} else {
										$languageOptions		.= "<br /><input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage' $languageChecked required>$thisLanguage";
									}
								}

								if ($user_timezone_id == 'XX') {
									$content	.= "<p><b>URGENT:</b> Please go to 
													<a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$inp_callsign&inp_depth=one&doDebug=$doDebug&testMode=$testMode' target='_blank'>Update User Master Data</a> 
													to determine the correct timezone identifier for where you are living. Until that is done, the 
													system will not be able to properly calculate class schedule times for the class catalog that 
													students will use to select classes.</p>
													<p>When that update is done, please restart the sign-up process.</p>";
								} else {
									$content	.= "<p>If any of the above information needs to be updated, 
													please click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$inp_callsign&inp_depth=one&doDebug=$doDebug&testMode=$testMode' target='_blank'>HERE</a> 
													to update the advisor Master Data before proceeding with the sign up process</p>
		
													<p>Please fill out the following form and submit it. You must sign up for 
													one class. You will be able to sign up for as many classes as you wish.</p> 
													<form method='post' action='$theURL' 
													name='advisor_signup_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='inp_callsign' value='$inp_callsign'>
													<input type='hidden' name='strpass' value='3'>
													<input type='hidden' name='inp_mode' value='$inp_mode'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='allowSignup' value=$allowSignup>
													<table style='width:1000px;'>
													<tr><td style='vertical-align:top;'><b>Call Sign</b><br />
															$inp_callsign</td>
														<td style='vertical-align:top;'><b>Sequence</b><br />
															1</td>
														<td style='vertical-align:top;'><b>Semester</b><br />
															<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$nextSemester' checked> $nextSemester<br />
															<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$semesterTwo' > $semesterTwo<br />
															<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$semesterThree'> $semesterThree</td></tr>
													<tr><td style='vertical-align:top;width:330px;'><b>Level</b><br />
															<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Beginner' required> Beginner<br />
															<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Fundamental' required> Fundamental (formerly Basic)<br />
															<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Intermediate' required> Intermediate<br />
															<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Advanced' required> Advanced<br /></td>
														<td style='vertical-align:top;'><b>Language</b><br />
															$languageOptions
															
														<td style='vertical-align:top;'><b>Class Size</b><br />
															<input type='text'class='formInputText' id='chk_class_size' name='inp_class_size' size='5' maxlength='5' value='6'><br />
																(default class size is 6)</td></tr>
													<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />Note that most advisors hold classes on Monday and Thursday<br />
															<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Sunday,Wednesday' required > Sunday and Wednesday<br />
															<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Sunday,Thursday' required > Sunday and Thursday<br />
															<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Monday,Thursday' required  checked> Monday and Thursday<br />
															<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Tuesday,Friday' required > Tuesday and Friday</td>
														<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />Specify start time in local time  
															where you live. Select the time where this class will start. The program will account for standard or daylight 
															savings time or summer time as needed.<br />
														<table><tr>
														<td style='width:110px;vertical-align:top;'>
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0600'  required > 6:00am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0630' required  > 6:30am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0700'  required > 7:00am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0730'  required > 7:30am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0800'  required > 8:00am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0830'  required > 8:30am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0900'  required > 9:00am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0930'  required > 9:30am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1000'  required > 10:00am</td>
														<td style='width:110px;vertical-align:top;'>
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1030'  required > 10:30am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1100'  required > 11:00am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1130'  required > 11:30am<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1200'  required > Noon<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1230'  required > 12:30pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1300'  required > 1:00pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1330'  required > 1:30pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1400'  required > 2:00pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1430'  required > 2:30pm</td>
														<td style='width:110px;vertical-align:top;'>
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1500'  required > 3:00pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1530'  required > 3:30pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1600'  required > 4:00pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1630'  required > 4:30pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1700'  required > 5:00pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1730'  required > 5:30pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1800'  required > 6:00pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1830'  required > 6:30pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1900'  required > 7:00pm</td>
														<td style='width:110px;vertical-align:top;'>
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1930' required  > 7:30pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2000'  required > 8:00pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2030' required  > 8:30pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2100' required  > 9:00pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2130'  required > 9:30pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2200'  required > 10:00pm<br />
															<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2230'  required > 10:30pm</td></tr>
														</table></td></tr>
													<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
													</form>";
								}		
							}
						}
					} else {
						if ($doDebug) {
							echo "calling getAdvisorInfoToDisplay with $inp_callsign, $inp_semester, $noUpdate<br />";
						}
						$displayResult	= getAdvisorInfoToDisplay($inp_callsign,$inp_semester,$noUpdate);
						$content		.= $displayResult[0];
					}
				}
			} else {
				$content			.= "<p>No record found for $inp_callsign. The sysadmin has been notified 
										and will contact you with the resolution.</p>";
				sendErrorEmail("$jobname Pass 2. Getting user_master for $inp_callsign returned no data");
			}
		}	

/////////////////			Pass 3		add the advisor and class record

	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 3 with inp_callsign: $inp_callsign and inp_bypass: $inp_bypass<br />";
		}
		if ($userName = '') {
			$userName			= $inp_callsign;
		}
		$content				.= "<h3>$jobname</h3>";
		$doProceed				= TRUE;
		// get the user_master record
		$sql				= "select * from $userMasterTableName 
								where user_call_sign = '$inp_callsign'";
		$sqlResult			= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"Pass 2 attempting to read user_master for $inp_callsign");
			$content		.= "<b>FATAL ERROR</b> The sysadmin has been notified.";
		} else {
			$numRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_call_sign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_ph_code			= $sqlRow->user_ph_code;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_country			= $sqlRow->user_country;
					$user_whatsapp			= $sqlRow->user_whatsapp;
					$user_telegram			= $sqlRow->user_telegram;
					$user_signal			= $sqlRow->user_signal;
					$user_messenger			= $sqlRow->user_messenger;
					$user_action_log		= $sqlRow->user_action_log;
					$user_timezone_id		= $sqlRow->user_timezone_id;
					$user_languages			= $sqlRow->user_languages;
					$user_survey_score		= $sqlRow->user_survey_score;
					$user_is_admin			= $sqlRow->user_is_admin;
					$user_role				= $sqlRow->user_role;
					$user_prev_callsign		= $sqlRow->user_prev_callsign;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;

					$inp_action_log			= "$actionDate ADVREG ";
	
					//// check to see if the advisor is also signed up as a student
					$studentResult			= get_student_last_class($inp_callsign,$doDebug,$testMode);
					if ($doDebug) {
						echo "studentResult:<br /><pre>";
						print_r($studentResult);
						echo "</pre><br />";
					}
					//// if result['Current']['Semester'] is not empty, the person is signed up to be a student
					//// if semesters match, then set his survey_score to 13
					$myStr			= $studentResult['Current']['Semester'];
					if ($doDebug) {
						echo "Checking if advisor is also a student. Result should be blank: $myStr<br />";
					}
					if ($myStr != '' && $myStr == $inp_semester) {
						$theSubject				= "CW Academy - Advisor Is Also a Student";
						$theContent				= "Advisor $inp_callsign is also registered as a student in $myStr semester";
						$theRecipient			= '';
						$mailCode				= 18;
						$mailResult				= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
																		'theSubject'=>$theSubject,
																		'jobname'=>$jobname,
																		'theContent'=>$theContent,
																		'mailCode'=>$mailCode,
																		'increment'=>$increment,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug));
						if ($doDebug) {
							echo "emailed Roland and Bob<br />";
						}
						$inp_action_log				.= "Advisor is also signed up as a student. ";
					}
		
					// Write the advisor record and set up to get the classes informations
		
					$inp_action_log					.= "advisor record added ";
					if ($doDebug) {
						echo "preparing to write the $advisorTableName<br />";
					}
					$updateParams				= array("advisor_call_sign|$inp_callsign|s",
														"advisor_semester|$inp_semester|s",
														"advisor_welcome_email_date||s",
														"advisor_verify_email_date||s",
														"advisor_verify_response||s",
														"advisor_verify_email_number|0|d",
														"advisor_class_verified|N|s",
														"advisor_replacement_status|N|s",
														"advisor_action_log|$inp_action_log|s");	
					if ($doDebug) {
						echo "Would insert the following into the $advisorTableName:<br /><pre>";
						print_r($updateParams);
						echo "</pre><br />";
					}
		
					$advisorUpdateData		= array('tableName'=>$advisorTableName,
													'inp_method'=>'add',
													'inp_data'=>$updateParams,
													'jobname'=>$jobname,
													'inp_id'=>0,
													'inp_callsign'=>$inp_callsign,
													'inp_semester'=>$inp_semester,
													'inp_who'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
					$updateResult	= updateAdvisor($advisorUpdateData);
					if ($updateResult[0] === FALSE) {
						if ($doDebug) {
							echo "ERROR<br />";
						}
						$myError	= $wpdb->last_error;
						$mySql		= $wpdb->last_query;
						$errorMsg	= "$jobname inserting $inp_callsign advisor to $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
						if ($doDebug) {
							echo $errorMsg;
						}
						sendErrorEmail($errorMsg);
						$content		.= "Unable to update content in $advisorTableName<br />";
					} else {
						$advisor_id		= $updateResult[1];				///// function returns the id
						if ($doDebug) {
							echo "advisor record add completed. ID: $advisor_id<br />";
						}
						
						// figure out the timezone offset
						$timezone_offset	= getOffsetFromIdentifier($user_timezone_id,$inp_semester,$doDebug);
						if ($timezone_offset === FALSE) {
							if ($doDebug) {
								echo "calculating the timezone offset returned FALSE<br />";
							}
							if ($doDebug == FALSE) {
								sendErrorEmail("$jobname Pass 3 calculating timezone offset for id $timezone_id failed");
							}
							$timezone_offset = 0.00;
						}
						$inp_timezone_offset 				= $timezone_offset;
						////////// insert the class record
						$inp_class_action_log				= "$actionDate ADVREG class record added ";
						$inp_class_schedule_days			= $inp_teaching_days;
						$inp_class_schedule_times			= $inp_times;
						$convertResult		= utcConvert('toutc',$inp_timezone_offset,$inp_times,$inp_teaching_days,$doDebug);
						if ($doDebug) {
							echo "Convert Result:<br /><pre >";
							print_r($convertResult);
							echo "</pre><br />";
						}
						$thisResult			= $convertResult[0];
						if ($thisResult[0] == 'FAIL') {
							$thisReason		= $convertResult[3];
							if ($doDebug) {
								echo "converting $inp_times / $inp_teaching_days to UTC failed: $thisReason<br />";
							}
							$inp_class_schedule_days_utc 	= $inp_teaching_days;
							$inp_class_schedule_times_utc	= $inp_times;
						} else {
							$inp_class_schedule_times_utc	= $convertResult[1];
							$inp_class_schedule_days_utc	= $convertResult[2];
						}
						$updateParams		= array("advisorclass_call_sign|$inp_callsign|s",
													"advisorclass_sequence|1|d",
													"advisorclass_semester|$inp_semester|s",
													"advisorclass_timezone_offset|$inp_timezone_offset|f",
													"advisorclass_level|$inp_level|s",
													"advisorclass_class_size|$inp_class_size|d",
													"advisorclass_language|$inp_advisorclass_language|s",
													"advisorclass_class_schedule_days|$inp_class_schedule_days|s",
													"advisorclass_class_schedule_times|$inp_class_schedule_times|s",
													"advisorclass_class_schedule_days_utc|$inp_class_schedule_days_utc|s",
													"advisorclass_class_schedule_times_utc|$inp_class_schedule_times_utc|s",
													"advisorclass_action_log|$inp_class_action_log|s",
													"advisorclass_class_incomplete|N|s");
		
						if ($doDebug) {
							echo "Would insert the following into $advisorClassTableName:<br /><pre>";
							print_r($updateParams);
							echo "</pre><br />";
						}
		
						$classUpdateData		= array('tableName'=>$advisorClassTableName,
														'inp_method'=>'add',
														'inp_data'=>$updateParams,
														'jobname'=>$jobname,
														'inp_id'=>0,
														'inp_callsign'=>$inp_callsign,
														'inp_semester'=>$inp_semester,
														'inp_sequence'=>1,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateClass($classUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "$jobname Inserting $advisor_call_sign into $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorClassTableName<br />";
						} else {
							// display the advisor signup record	
							if ($allowSignup) {
								$inp_semester 	= $nextSemester;
							}
							$displayResult		= getAdvisorInfoToDisplay($inp_callsign,$inp_semester,$noUpdate);
							$content			.= $displayResult[0];
							$content			.= "<p><b>Note:</b> The process to assign students to advisor classes will occur 
													about twenty days before the start of the semester.</p>";
						}
					}
				}
			} else {
				$content			.= "<p>No record found for $inp_callsign. The sysadmin has been notified 
										and will contact you with the resolution.</p>";
				sendErrorEmail("$jobname Pass 3. Getting user_master for $inp_callsign returned no data");
			}
		}




///////////	pass 5		input info for another class

	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "at pass 5 with classcount: $classcount and callsign $inp_callsign and semester $inp_semester<br />";
		}
		
		/// get the user_master and advisor records
		$cwa_advisor			= $wpdb->get_results("select * from $advisorTableName 
													  left join $userMasterTableName on user_call_sign = advisor_call_sign 
													  where advisor_call_sign='$inp_callsign' 
														and advisor_semester='$inp_semester'");
		if ($cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($cwa_advisor as $advisorRow) {
					$user_ID 							= $advisorRow->user_ID;
					$user_call_sign						= $advisorRow->user_call_sign;
					$user_first_name 					= $advisorRow->user_first_name;
					$user_last_name 					= $advisorRow->user_last_name;
					$user_email 						= $advisorRow->user_email;
					$user_ph_code						= $advisorRow->user_ph_code;
					$user_phone 						= $advisorRow->user_phone;
					$user_city 							= $advisorRow->user_city;
					$user_state 						= $advisorRow->user_state;
					$user_zip_code 						= $advisorRow->user_zip_code;
					$user_country_code 					= $advisorRow->user_country_code;
					$user_country						= $advisorRow->user_country;
					$user_whatsapp 						= $advisorRow->user_whatsapp;
					$user_telegram 						= $advisorRow->user_telegram;
					$user_signal 						= $advisorRow->user_signal;
					$user_messenger 					= $advisorRow->user_messenger;
					$user_action_log 					= $advisorRow->user_action_log;
					$user_timezone_id 					= $advisorRow->user_timezone_id;
					$user_languages 					= $advisorRow->user_languages;
					$user_survey_score 					= $advisorRow->user_survey_score;
					$user_is_admin						= $advisorRow->user_is_admin;
					$user_role 							= $advisorRow->user_role;
					$user_date_created 					= $advisorRow->user_date_created;
					$user_date_updated 					= $advisorRow->user_date_updated;

					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_call_sign 					= strtoupper($advisorRow->advisor_call_sign);
					$advisor_semester 					= $advisorRow->advisor_semester;
					$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->advisor_verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
					$advisor_action_log 				= $advisorRow->advisor_action_log;
					$advisor_class_verified 			= $advisorRow->advisor_class_verified;
					$advisor_control_code 				= $advisorRow->advisor_control_code;
					$advisor_date_created 				= $advisorRow->advisor_date_created;
					$advisor_date_updated 				= $advisorRow->advisor_date_updated;
					$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;

					//Build language selection
					$languageOptions			= '';
					$firstTime					= TRUE;
					$languageChecked			= '';
					foreach($languageArray as $thisLanguage) {
						if ($thisLanguage == 'English') {
							$languageChecked	= 'checked';
						} else {
							$languageChecked	= '';
						}
						if ($firstTime) {
							$firstTime			= FALSE;
							$languageOptions		.= "<input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage $languageChecked' >$thisLanguage";
						} else {
							$languageOptions		.= "<br /><input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage' $languageChecked >$thisLanguage";
						}
					}


					if ($classcount == 0) {
						$classcount						= 1;
					}
					// display form to add a class
					$content		.= "<h3>Add a Class for Advisor $advisor_call_sign in $inp_semester Semester</h3>
										<p>Enter the information for the class to be added. After you click 'Add Class', the class will 
										be added to the database and you will have the opportunity to add another class if you wish. </p>
										<form method='post' action='$theURL' 
										name='class_add1_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='6'>
										<input type='hidden' name='inp_callsign' value='$inp_callsign'>
										<input type='hidden' name='classcount' value='$classcount'>
										<input type='hidden' name='inp_timezone_id' value='$user_timezone_id'>
										<input type='hidden' name='inp_semester' value='$advisor_semester'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<table style='width:1000px;'>
										<tr><td style='vertical-align:top;'><b>Call Sign</b><br />
												$inp_callsign</td>
											<td style='vertical-align:top;'><b>Sequence</b><br />
												$classcount</td>
											<td style='vertical-align:top;'><b>Semester</b><br />
												<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$nextSemester' checked> $nextSemester<br />
												<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$semesterTwo' > $semesterTwo<br />
												<input type='radio' class='formInputButton' id='chk_semester' name='inp_semester' value='$semesterThree'> $semesterThree</td></tr>
										<tr><td style='vertical-align:top;width:330px;'><b>Level</b><br />
												<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Beginner' required> Beginner<br />
												<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Fundamental' required> Fundamental<br />
												<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Intermediate' required> Intermediate<br />
												<input type='radio' class='formInputButton' id='chk_level' name='inp_level' value='Advanced' required> Advanced</td>
											<td style='vertical-align:top;'><b>Language</b><br />
												$languageOptions</td>												
											<td style='vertical-align:top;'><b>Class Size</b><br />
												<input type='text'class='formInputText' id='chk_class_size' name='inp_class_size' size='5' maxlength='5' value='6'><br />
													(default class size is 6)</td></tr>
										<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />Note that most advisors hold classes on Monday and Thursday<br />
												<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Sunday,Wednesday' required > Sunday and Wednesday<br />
												<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Sunday,Thursday' required > Sunday and Thursday<br />
												<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Monday,Thursday' required  checked> Monday and Thursday<br />
												<input type='radio' class='formInputButton' id='check_days' name='inp_teaching_days' value='Tuesday,Friday' required > Tuesday and Friday</td>
											<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />Specify start time in local time  
												where you live. Select the time where this class will start. The program will account for standard or daylight 
												savings time or summer time as needed.<br />
											<table><tr>
											<td style='width:110px;vertical-align:top;'>
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0600'  required > 6:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0630' required  > 6:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0700'  required > 7:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0730'  required > 7:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0800'  required > 8:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0830'  required > 8:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0900'  required > 9:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='0930'  required > 9:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1000'  required > 10:00am</td>
											<td style='width:110px;vertical-align:top;'>
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1030'  required > 10:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1100'  required > 11:00am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1130'  required > 11:30am<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1200'  required > Noon<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1230'  required > 12:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1300'  required > 1:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1330'  required > 1:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1400'  required > 2:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1430'  required > 2:30pm</td>
											<td style='width:110px;vertical-align:top;'>
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1500'  required > 3:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1530'  required > 3:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1600'  required > 4:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1630'  required > 4:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1700'  required > 5:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1730'  required > 5:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1800'  required > 6:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1830'  required > 6:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1900'  required > 7:00pm</td>
											<td style='width:110px;vertical-align:top;'>
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='1930' required  > 7:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2000'  required > 8:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2030' required  > 8:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2100' required  > 9:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2130'  required > 9:30pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2200'  required > 10:00pm<br />
												<input type='radio' class='formInputButton' id='chk_times' name='inp_times' value='2230'  required > 10:30pm</td></tr>
											</table></td></tr>
										<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Add the Class' /></td></tr></table>

										</form>";		
				}
			} else {
				if ($doDebug) {
					echo "No $inp_callsign advisor record found. Should not be able to happen<br />";
				}
				$content		.= "<b>Fatal Error</b> The sys admin has been notified";
				sendErrorEmail("$jobname Pass 5 no advisor record found for $inp_callsign");
			}
		}


///////////// Pass 6
	} elseif ("6" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 6 with a class record to be added<br />";
		}
		if ($userName == '') {
			$userName						= $inp_callsign;
		}
		////////// insert the class record
		$inp_class_action_log				= "$actionDate ADVREG class record added ";
		$inp_class_schedule_days			= $inp_teaching_days;
		$inp_class_schedule_times			= $inp_times;
		
		// get the timezone_offset
		$inp_timezone_offset		= getOffsetFromIdentifier($inp_timezone_id,$inp_semester,$doDebug);
		if ($inp_timezone_offset === FALSE) {
			if ($doDebug) {
				echo "timezone_id of $timezone_id for semester $inp_semester returned falls from getOffsetFromIdentifier<br />";
			}
			sendErrorEmail("$jobname Pass 6 timezone_id of $timezone_id for semester $inp_semester returned falls from getOffsetFromIdentifier");
			$content		.= "<b>Fatal Error</b> Sys admin has been notified";
		} else {
			$convertResult		= utcConvert('toutc',$inp_timezone_offset,$inp_times,$inp_teaching_days,$doDebug);
			if ($doDebug) {
				echo "Convert Result:<br /><pre >";
				print_r($convertResult);
				echo "</pre><br />";
			}
			$thisResult			= $convertResult[0];
			if ($thisResult[0] == 'FAIL') {
				$thisReason		= $convertResult[3];
				if ($doDebug) {
					echo "converting $inp_times / $inp_teaching_days to UTC failed: $thisReason<br />";
				}
				$inp_class_schedule_days_utc 	= $inp_teaching_days;
				$inp_class_schedule_times_utc	= $inp_times;
			} else {
				$inp_class_schedule_times_utc	= $convertResult[1];
				$inp_class_schedule_days_utc	= $convertResult[2];
			}
			$updateParams		= array("advisorclass_call_sign|$inp_callsign|s",
										"advisorclass_sequence|$classcount|d",
										"advisorclass_semester|$inp_semester|s",
										"advisorclass_timezone_offset|$inp_timezone_offset|f",
										"advisorclass_level|$inp_level|s",
										"advisorclass_language|$inp_advisorclass_language|s",
										"advisorclass_class_size|$inp_class_size|d",
										"advisorclass_class_schedule_days|$inp_class_schedule_days|s",
										"advisorclass_class_schedule_times|$inp_class_schedule_times|s",
										"advisorclass_class_schedule_days_utc|$inp_class_schedule_days_utc|s",
										"advisorclass_class_schedule_times_utc|$inp_class_schedule_times_utc|s",
										"advisorclass_action_log|$inp_class_action_log|s",
										"advisorclass_class_incomplete|N|s");
	
			if ($doDebug) {
				echo "Would insert the following into $advisorClassTableName:<br /><pre>";
				print_r($updateParams);
				echo "</pre><br />";
			}
			$classUpdateData		= array('tableName'=>$advisorClassTableName,
											'inp_method'=>'add',
											'inp_data'=>$updateParams,
											'jobname'=>$jobname,
											'inp_id'=>0,
											'inp_callsign'=>$inp_callsign,
											'inp_semester'=>$inp_semester,
											'inp_sequence'=>$classcount,
											'inp_who'=>$userName,
											'testMode'=>$testMode,
											'doDebug'=>$doDebug);
											
			$updateResult	= updateClass($classUpdateData);
			if ($updateResult[0] === FALSE) {
				$myError	= $wpdb->last_error;
				$mySql		= $wpdb->last_query;
				$errorMsg	= "$jobname Inserting $inp_callsign into $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
				if ($doDebug) {
					echo $errorMsg;
				}
				sendErrorEmail($errorMsg);
				$content		.= "Unable to update content in $advisorClassTableName<br />";
			} else {
				// display the advisor signup record	
				$displayResult		= getAdvisorInfoToDisplay($inp_callsign,$inp_semester,$noUpdate);
				$content			.= $displayResult[0];
			}
		}


/////////////////		pass 15				update a class record

	} elseif ("15" == $strPass) {
	
		if ($doDebug) {
			echo "At pass 15 ... update a class record<br />";
		}
		
		
		//// get the record
		$sql					= "select * from $advisorClassTableName 
									where advisorclass_call_sign='$inp_callsign' 
									  and advisorclass_sequence=$classID
									  and advisorclass_semester='$inp_semester'";
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
					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
					$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
					$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
					$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->advisorclass_level;
					$advisorClass_language 					= $advisorClassRow->advisorclass_language;
					$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
					$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
					$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
					$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
					$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
					$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;

	
					$sunWed				= '';
					$sunThu				= '';
					$monWed				= '';
					$monThu				= '';
					$tueThu				= '';
					$tueFri				= '';
					$wedFri				= '';
					$wedSat				= '';
					
					if ($advisorClass_class_schedule_days == 'Sunday,Wednesday') {
						$sunWed			= "checked='checked'";
					} elseif ($advisorClass_class_schedule_days == 'Sunday,Thursday') {
						$sunThu			= "checked='checked'";
					} elseif ($advisorClass_class_schedule_days == 'Monday,Thursday') {
						$monThu			= "checked='checked'";
					} elseif ($advisorClass_class_schedule_days == 'Tuesday,Friday') {
						$tueFri			= "checked='checked'";
					} else {
						if ($doDebug) {
							echo "<b>Error</b> advisorClass_class_schedule_days of $advisorClass_class_schedule_days do not compute<br />";
						}
					}
					$time0600			= '';
					$time0630			= '';
					$time0700			= '';
					$time0730			= '';
					$time0800			= '';
					$time0830			= '';
					$time0900			= '';
					$time0930			= '';
					$time1000			= '';
					$time1030			= '';
					$time1100			= '';
					$time1130			= '';
					$time1200			= '';
					$time1230			= '';
					$time1300			= '';
					$time1330			= '';
					$time1400			= '';
					$time1430			= '';
					$time1500			= '';
					$time1530			= '';
					$time1600			= '';
					$time1630			= '';
					$time1700			= '';
					$time1730			= '';
					$time1800			= '';
					$time1830			= '';
					$time1900			= '';
					$time1930			= '';
					$time2000			= '';
					$time2030			= '';
					$time2100			= '';
					$time2130			= '';
					$time2200			= '';
					$time2230			= '';
					
					if ($advisorClass_class_schedule_times == '0600') {
						$time0600		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0630') {
						$time0630		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0700') {
						$time0700		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0730') {
						$time0730		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0800') {
						$time0800		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0830') {
						$time0830		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0900') {
						$time0900		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '0930') {
						$time0930		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1000') {
						$time1000		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1030') {
						$time1030		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1100') {
						$time1100		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1130') {
						$time1130		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1200') {
						$time1200		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1230') {
						$time1230		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1300') {
						$time1300		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1330') {
						$time1330		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1400') {
						$time1400		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1430') {
						$time1430		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1500') {
						$time1500		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1530') {
						$time1530		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1600') {
						$time1600		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1630') {
						$time1630		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1700') {
						$time1700		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1730') {
						$time1730		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1800') {
						$time1800		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1830') {
						$time1830		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1900') {
						$time1900		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '1930') {
						$time1930		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2000') {
						$time2000		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2030') {
						$time2030		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2100') {
						$time2100		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2130') {
						$time2130		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2200') {
						$time2200		= "checked='checked'";
					}
					if ($advisorClass_class_schedule_times == '2230') {
						$time2230		= "checked='checked'";
					}
					$begChecked			= '';
					$funChecked			= '';
					$intChecked			= '';
					$advChecked			= '';
					if ($advisorClass_level == 'Beginner') {
						$begChecked		= "checked='checked' ";
					}
					if ($advisorClass_level == 'Fundamental') {
						$funChecked		= "checked='checked' ";
					}
					if ($advisorClass_level == 'Intermediate') {
						$intChecked		= "checked='checked' ";
					}
					if ($advisorClass_level == 'Advanced') {
						$advChecked		= "checked='checked' ";
					}
				
					//Build language selection
					$languageOptions			= '';
					$firstTime					= TRUE;
					foreach($languageArray as $thisLanguage) {
						$thisChecked			= '';
						if ($advisorClass_language == $thisLanguage) {
							$thisChecked		= ' checked ';
						}
						if ($firstTime) {
							$firstTime			= FALSE;
							$languageOptions		.= "<input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage' $thisChecked>$thisLanguage";
						} else {
							$languageOptions		.= "<br /><input type='radio' class='formInputButton' name='inp_advisorclass_language' value='$thisLanguage' $thisChecked>$thisLanguage";
						}
					}

	
	
					$content			.= "<b>Class $advisorClass_sequence:</b>
											<p>Please update the class record and then submit the changes to be updated in the database:</p>
											<form method='post' action='$theURL' 
											name='advisorclass_update_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='16'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_callsign' value='$advisorClass_call_sign'>
											<input type='hidden' name='inp_id' value='$advisorClass_ID'>
											<input type='hidden' name='inp_sequence' value='$advisorClass_sequence'>
											<input type='hidden' name='allowSignup' value='$allowSignup'>

											<table style='width:1000px;'>
											<tr><td style='vertical-align:top;width:330px;'><b>Level</b><br />
													<input type='radio' class='formInputButton' name='inp_level' value='Beginner' $begChecked /> Beginner<br />
													<input type='radio' class='formInputButton' name='inp_level' value='Fundamental' $funChecked /> Fundamental<br />
													<input type='radio' class='formInputButton' name='inp_level' value='Intermediate' $intChecked /> Intermediate<br />
													<input type='radio' class='formInputButton' name='inp_level' value='Advanced' $advChecked /> Advanced</td>	
												<td style='vertical-align:top;'><b>Language</b><br />
													$languageOptions</td>
												<td style='vertical-align:top;'width:330px;><b>Class Size</b><br />
													<input type='text'class='formInputText' id='chk_class_size' name='inp_class_size' size='5' maxlength='5' value='$advisorClass_class_size'><br />
														(default class size is 6)</td></tr>
											<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />Note that most advisors hold classes on Monday and Thursday<br />
													<input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Wednesday' $sunWed> Sunday and Wednesday<br />
													<input type='radio' class='formInputButton' name='inp_teaching_days' value='Sunday,Thursday' $sunThu> Sunday and Thursday<br />
													<input type='radio' class='formInputButton' name='inp_teaching_days' value='Monday,Thursday' $monThu> Monday and Thursday<br />
													<input type='radio' class='formInputButton' name='inp_teaching_days' value='Tuesday,Friday' $tueFri> Tuesday and Friday<br /></td>
												<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />Specify start time in local time in the 
											time zone where you live. Select the time where this class will start. The program will account for standard or daylight 
											savings time or summer time as needed.<br />
											<table><tr>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' name='inp_times' value='0600' $time0600> 6:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0630' $time0630> 6:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0700' $time0700> 7:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0730' $time0730> 7:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0800' $time0800> 8:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0830' $time0830> 8:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0900' $time0900> 9:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='0930' $time0930> 9:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1000' $time1000> 10:00am</td>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' name='inp_times' value='1030' $time1030> 10:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1100' $time1100> 11:00am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1130' $time1130> 11:30am<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1200' $time1200> Noon<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1230' $time1230> 12:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1300' $time1300> 1:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1330' $time1330> 1:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1400' $time1400> 2:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1430' $time1430> 2:30pm</td>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' name='inp_times' value='1500' $time1500> 3:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1530' $time1530> 3:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1600' $time1600> 4:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1630' $time1630> 4:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1700' $time1700> 5:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1730' $time1730> 5:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1800' $time1800> 6:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1830' $time1830> 6:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='1900' $time1900> 7:00pm</td>
												<td style='width:110px;vertical-align:top;'>
													<input type='radio' class='formInputButton' name='inp_times' value='1930' $time1930> 7:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2000' $time2000> 8:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2030' $time2030> 8:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2100' $time2100> 9:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2130' $time2130> 9:30pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2200' $time2100> 10:00pm<br />
													<input type='radio' class='formInputButton' name='inp_times' value='2230' $time2130> 10:30pm</td>
												<td>&nbsp;</td>
												</tr></table></td></tr>
											<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Update Info' /></td></tr>
											</table></form>";


				}
			} else {
				if ($doDebug) {
					echo "No advisorclass record found for advisor_call_sign=$inp_callsign and sequence=$classID<br />";
				}
				$content			.= "System Error. The appropriate people have been notified<br />";
			}
		}


///////////////////		pass 16				do the actual update to the class record

	} elseif ("16" == $strPass) {
		if ($doDebug) {
			echo "At pass 16 to do the actual update to the class record<br />";
		}

		$wpw1_cwa_advisorclass	= $wpdb->get_results("select * from $advisorClassTableName 
															where advisorclass_id=$inp_id");
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows						= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
					$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
					$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
					$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->advisorclass_level;
					$advisorClass_language 					= $advisorClassRow->advisorclass_language;
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

					$thisErrors									= '';
					if ($doDebug) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;Processing sequence $advisorClass_sequence<br />";
						
					}
					$doUpdate					= FALSE;
					$newUTC						= FALSE;
					$updateParams				= array();
					$updateFormats				= array();
					$actionLogData				= '';
					$updateContent				= '';
					if ($userName == '') {
						$userName				= $advisorClass_call_sign;
					}



					if ($inp_level != $advisorClass_level) {
						if ($doDebug) {
							echo "level: $inp_level != $advisorClass_level<br />";
						}
						$updateParams['advisorclass_level']	= $inp_level;
						$updateFormats[]		= '%s';
						$doUpdate				= TRUE;
						$actionLogData			.= " Updated advisorClass level from $advisorClass_level to $inp_level ";
						$updateContent			.= "Updated advisorClass level from $advisorClass_level to $inp_level<br />";
						$advisorClass_level		= $inp_level;
					}
					if ($inp_advisorclass_language != $advisorClass_language) {
						if ($doDebug) {
							echo "language: $inp_advisorclass_language != $advisorClass_language<br />";
						}
						$updateParams['advisorclass_language']	= $inp_advisorclass_language;
						$updateFormats[]		= '%s';
						$doUpdate				= TRUE;
						$actionLogData			.= " Updated advisorClass language from $advisorClass_language to $inp_advisorclass_language ";
						$updateContent			.= "Updated advisorClass language from $advisorClass_language to $inp_advisorclass_language<br />";
						$advisorClass_language		= $inp_advisorclass_language;
					}
					if ($inp_class_size != $advisorClass_class_size) {
						if ($inp_class_size == 0 || $inp_class_size == '') {
							$inp_class_size = $defaultClassSize;
						}
						if ($doDebug) {
							echo "class_size: $inp_class_size != $advisorClass_class_size<br />";
						}
						$updateParams['advisorclass_class_size']	= $inp_class_size;
						$updateFormats[]		= '%d';
						$doUpdate				= TRUE;
						$actionLogData			.= " Updated class_size from $advisorClass_class_size to $inp_class_size ";
						$updateContent			.= "Updated class_size from $advisorClass_class_size to $inp_class_size<br />";
						$advisorClass_class_size		= $inp_class_size;
					}
					if ($inp_teaching_days != $advisorClass_class_schedule_days) {
						if ($doDebug) {
							echo "teaching_days: $inp_teaching_days != $advisorClass_class_schedule_days<br />";
						}
						$updateParams['advisorclass_class_schedule_days']	= $inp_teaching_days;
						$updateFormats[]		= '%s';
						$doUpdate				= TRUE;
						$actionLogData			.= " Updated  class_schedule_days from $advisorClass_class_schedule_days to $inp_teaching_days ";
						$updateContent			.= "Updated class_schedule_days from $advisorClass_class_schedule_days to $inp_teaching_days<br />";
						$advisorClass_class_schedule_days		= $inp_teaching_days;
						$myStr					= str_replace(",","/",$advisorClass_class_schedule_days);
						$newUTC					= TRUE;
					}
					if ($inp_times != $advisorClass_class_schedule_times) {
						if ($doDebug) {
							echo "teaching_times: $inp_times != $advisorClass_class_schedule_times<br />";
						}
						$updateParams['advisorclass_class_schedule_times']	= $inp_times;
						$updateFormats[]		= '%s';
						$doUpdate				= TRUE;
						$actionLogData			.= " Updated class_schedule_times from $advisorClass_class_schedule_times to $inp_times ";
						$updateContent			.= "Updated class_schedule_times from $advisorClass_class_schedule_times to $inp_times<br />";
						$advisorClass_class_schedule_times		= $inp_times;
						$newUTC					= TRUE;
					}
					if ($advisorClass_class_schedule_times_utc == '' && $advisorClass_class_schedule_times != '') {
						$newUTC				= TRUE;
						if ($doDebug) {
							echo "missing teaching times in UTC<br />";
						}
					}
					if ($advisorClass_class_schedule_days_utc == '' && $advisorClass_class_schedule_days != '') {
						$newUTC				= TRUE;
						if ($doDebug) {
							echo "missing teaching days in UTC<br />";
						}
					}
					// see if class incomplete.if so, see if that's been fixed
					if ($advisorClass_class_incomplete != '') {
						$incompleteCleared		= TRUE;
						if ($advisorClass_level == '') {
							$incompleteCleared	= FALSE;
						}
						if ($advisorClass_class_schedule_days == '') {
							$incompleteCleared	= FALSE;
							$newUTC				= FALSE;
						}
						if ($advisorClass_class_schedule_times == '') {
							$incompleteCleared	= FALSE;
							$newUTC				= FALSE;
						}
						if ($incompleteCleared) {
							$advisorClass_class_incomplete	= '';
							$actionLogData		.= " Cleared class_incomplete flag ";
							$updateParams['advisorclass_class_incomplete'] = '';
							$updateFormats[]		= '%s';
							$updateContent		.= "Class Incomplete flag cleared<br />";
						}
					}
					if ($newUTC) {
						if ($doDebug) {
							echo  "Calculating UTC for $advisorClass_timezone_offset,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days<br />";
						}
						$result						= utcConvert('toutc',$advisorClass_timezone_offset,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days);
						if ($result[0] == 'FAIL') {
							if ($doDebug) {
								echo "utcConvert failed 'toutc',$advisorClass_timezone,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days<br />
										Error: $result[3]<br />";
							}
							$updateParams['advisorclass_class_schedule_times_utc']			= '';
							$updateParams['advisorclass_class_schedule_days_utc']			= "ERROR";
							$updateParams['advisorclass_class_incomplete']					= 'Y';
							$updateFormats[]		= '%s';
							$updateFormats[]		= '%s';
							$updateFormats[]		= '%s';
							$doUpdate				= TRUE;
						} else {
							$updateParams['advisorclass_class_schedule_times_utc']			= $result[1];
							$updateParams['advisorclass_class_schedule_days_utc']			= $result[2];
							$updateFormats[]		= '%s';
							$updateFormats[]		= '%s';
							$doUpdate				= TRUE;
						}
					}
					if ($doUpdate) {
						$content				.= "<p><b>Class $advisorClass_sequence Updates</b><br />
													$updateContent</p>";
						$advisorClass_action_log	= "$advisorClass_action_log / $actionDate ADVREG $actionLogData ";
						$updateParams['advisorclass_action_log']	= $advisorClass_action_log;
						$updateFormats[]			= '%s';
						$classUpdateData		= array('tableName'=>$advisorClassTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateParams,
														'inp_format'=>$updateFormats,
														'jobname'=>$jobname,
														'inp_id'=>$advisorClass_ID,
														'inp_callsign'=>$advisorClass_call_sign,
														'inp_semester'=>$advisorClass_semester,
														'inp_sequence'=>$advisorClass_sequence,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateClass($classUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "A$jobname Processing $advisorClass_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorClassTableName<br />";
						}				
					} else {
						if ($doDebug) {
							echo "No updates to class record<br /><br >";
						}
						$content				.= "<p><b>Class $advisorClass_sequence Updates</b>
													<br />No updates requested. ";
						if ($advisorClass_class_incomplete != '') {
							$content			.= "HOWEVER, errors need correction: $advisorClass_class_incomplete.</p>";
						}
						$content				.= "</p>";
					}
					////// now display advisor and class records and provide options
					$result				= getAdvisorInfoToDisplay($inp_callsign,$advisorClass_semester,$noUpdate);
					$content			.= $result[0];
				}
			} else {
				if ($doDebug) {
					echo "No $advisorClassTableName table records for $advisor_call_sign<br/>";
				}
			}
		}

//////////////////		pass 17				Delete a class record

	} elseif ("17" == $strPass) {
		if ($doDebug) {
			echo "At pass 17 to delete a class record: $classID for advisor $inp_callsign<br />";
		}
		if ($userName == '') {
			$userName		= $inp_callsign;
		}
		$content				.= "<p>You have requested the advisorClass record number $advisorclass_ID to be deleted</p>";
		
		// first, find out how many class records there are
		$goOn					= TRUE;
		// if only one, tell user to go back and delete the whole shebang
		$thisclasscount			= $wpdb->get_var("select count(advisorclass_call_sign) as thisclasscount 
									from $advisorClassTableName 
									where advisorclass_call_sign = '$inp_callsign' 
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
									You must delete the advisor as well as the class.</p>";
				$goOn			= FALSE;
			}
		}
		if ($goOn) {
			// get the advisorClass record
			$sql					= "select * from $advisorClassTableName 	
										where advisorclass_id=$classID";
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
						$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
						$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
						$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
						$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
						$advisorClass_level 					= $advisorClassRow->advisorclass_level;
						$advisorClass_language 					= $advisorClassRow->advisorclass_language;
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
	
						// if there are any students, they need to be unassigned
						if ($advisorClass_number_students > 0) {
							if ($doDebug) {
								echo "have to unassign $class_number_students students<br />";
							}
							$content		.= "<p>The class has $class_number_students students assigned. They 
												will each be unassigned</p>";
			
							for ($snum=1;$snum<31;$snum++) {
								if ($snum < 10) {
									$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
								} else {
									$strSnum		= strval($snum);
								}
								$unassignCallSign	= ${'advisorClass_student' . $strSnum};
								if ($doDebug) {
									echo "obtained $unassignCallSign for snum $strSnum<br />";
								}
								if ($unassignCallSign != '') {
									$inp_data			= array('inp_student'=>$unassignCallSign,
																'inp_semester'=>$advisorClass_semester,
																'inp_assigned_advisor'=>$advisorClass_call_sign,
																'inp_assigned_advisor_class'=>$advisorClass_sequence,
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
											echo "attempting to remove $unassignCallSign from $advisorClass_call_sign class failed:<br />$thisReason<br />";
										}
										sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
										$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
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
														'inp_id'=>$advisorClass_ID,
														'inp_callsign'=>$advisorClass_call_sign,
														'inp_semester'=>$advisorClass_semester,
														'inp_sequence'=>$advisorClass_sequence,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateClass($classUpdateData);
						if ($updateResult[0] === FALSE) {
							handleWPDBError("FUNCTION Update Advisor Class $jobname",$doDebug);
							$content		.= "Unable to delete content in $advisorClassTableName<br />";
						} else {
							$content		.= "<br /><p>AdvisorClass record $advisorClass_ID for $advisorClass_call_sign $advisorClass_semester deleted</p>";
							
							// since there is more than one class record, possibly need to resequence them
							$reseqSQL		= "select * from $advisorClassTableName 
												where advisorclass_call_sign = '$advisorClass_call_sign' 
												and advisorclass_semester = '$advisorClass_semester' 
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
									$content	.= "<p>Resquencing advisorClass records for $advisorClass_call_sign in $advisorClass_semester semester</p>";
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
							////// now display advisor and class records and provide options
							$result				= getAdvisorInfoToDisplay($inp_callsign,$advisorClass_semester,$noUpdate);
							$content			.= $result[0];
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

//////////////////		pass 20	 			Delete the advisor and class records
		
		
	} elseif ("20" == $strPass) {			/// delete advisor and advisorclass records

		if ($doDebug) {
			echo "entire advisor and advisorClass records for $inp_callsign are to be deleted<br />";
		}
		if ($userName == '') {
			$userName 			= $inp_callsign;
		}

		// get the advisor record and delete it
		$cwa_advisor			= $wpdb->get_results("select * from $advisorTableName 
													where advisor_call_sign='$inp_callsign' 
													  and advisor_semester='$inp_semester'");
		if ($cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numARows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_semester 					= $advisorRow->semester;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					//// delete the record
					$advisorUpdateData		= array('tableName'=>$advisorTableName,
													'inp_method'=>'delete',
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
						if ($doDebug) {
							echo "now delete the class records<br />";
						}
						/// delete the class records
						$cwa_advisorclass	= $wpdb->get_results("select * from $advisorClassTableName 
																	where advisorclass_call_sign = '$inp_callsign' 
																	  and advisorclass_semester='$inp_semester' 
																	order by advisorclass_sequence");
						if ($cwa_advisorclass === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numACRows									= $wpdb->num_rows;
							if ($doDebug) {
								echo "obtained $numACRows rows from $advisorClassTableName table<br />";
							}
							if ($numACRows > 0) {
								foreach ($cwa_advisorclass as $advisorClassRow) {
									$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
									$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
									$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
									$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
									$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
									$advisorClass_level 					= $advisorClassRow->advisorclass_level;
									$advisorClass_language 					= $advisorClassRow->advisorclass_language;
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
				
									// if there are any students, they need to be unassigned
									if ($advisorClass_number_students > 0) {
										if ($doDebug) {
											echo "have to unassign $advisorClass_number_students students<br />";
										}
										$content		.= "<p>The class has $advosprClass_number_students students assigned. They 
															will each be unassigned</p>";
						
										for ($snum=1;$snum<31;$snum++) {
											if ($snum < 10) {
												$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
											} else {
												$strSnum		= strval($snum);
											}
											$unassignCallSign	= ${'advisorClass_student' . $strSnum};
											if ($doDebug) {
												echo "obtained $unassignCallSign for snum $strSnum<br />";
											}
											if ($unassignCallSign != '') {
												$inp_data			= array('inp_student'=>$unassignCallSign,
																			'inp_semester'=>$advisorClass_semester,
																			'inp_assigned_advisor'=>$advisorClass_call_sign,
																			'inp_assigned_advisor_class'=>$advisorClass_sequence,
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
														echo "attempting to remove $unassignCallSign from $advisorClass_call_sign class failed:<br />$thisReason<br />";
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
									$classUpdateData		= array('tableName'=>$advisorClassTableName,
																	'inp_method'=>'delete',
																	'jobname'=>$jobname,
																	'inp_id'=>$advisorClass_ID,
																	'inp_callsign'=>$advisorClass_call_sign,
																	'inp_semester'=>$advisorClass_semester,
																	'inp_sequence'=>$advisorClass_sequence,
																	'inp_who'=>$userName,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug);
									$updateResult	= updateClass($classUpdateData);
									if ($updateResult[0] === FALSE) {
										$myError	= $wpdb->last_error;
										$mySql		= $wpdb->last_query;
										$errorMsg	= "A$jobname Processing $advisorClass_call_sign in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
										if ($doDebug) {
											echo $errorMsg;
										}
										sendErrorEmail($errorMsg);
										$content		.= "Unable to update content in $advisorClassTableName<br />";
									}
								}
								$content					.= "<h3>Advisor Registration</h3>
																<p>The advisor and class records have been deleted.</p>
																<p>To return to the initial registration page, click 
																<a href='$theURL'>HERE</a></p>
																<p>Otherwise, you can close this window</p>";
							}
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "No advisor record found for $inp_callsign to delete<br />";
				}
			}
		}					
		
		
		
		
///////////////////// pass 25		
		
		
	} elseif ("25" == $strPass) {			/// handle new identifier for non US country
	
		if ($doDebug) {
			echo "<br />at Pass 25 HOW DID WE GET HERE?<br />";
		}
		$content	.= "How did the program get to pass 25?<br />";
	}

	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<br /><br /><p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$thisTime 		= date('Y-m-d H:i:s',$currentTimestamp);
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
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('advisor_registration', 'advisor_registration_func');
