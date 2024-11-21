function build_list_of_available_classes($semester='',$testMode=FALSE, $doDebug=FALSE) {

/*	Reads the advisor and advisorclass tables and builds a list of 
	classes with available seats
	
	If an advisor has indicated no additional replacement students, the advisor 
	is not incuded in the list. Also, advisors with a survey score of 6 or 13 and 
	advisors with a verify_response of R are not included
	
	Returns an array
		advisorclass_level[advisorclass_class_schedule_times_utc advisorclass_class_schedule_days_utc][advisorclass_advisor_call_sign] 	= advisorclass_sequence|advisorclass_class_schedule_times advisorclass_class_schedule_days|advisorclass_class_size|class_number_students
	
	For Example:
	Array ('Fundamental' => Array ('1700 Tuesday,Friday') => Array ('K7OJL') = '1|1130 Tuesday,Friday|9|8';
	
	Modified 12Oct24 by Roland for new database

*/

	global $wpdb;
// $doDebug = TRUE;
	if ($doDebug) {
		echo "<br /><b>FUNCTION: Build List of Available Classes</b><br />";
	}

	$initializationArray		= data_initialization_func();
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	
	if ($semester == '') {
		$theSemester				= $currentSemester;
		if ($theSemester == 'Not in Session') {
			$theSemester			= $nextSemester;
		}
	}

	if ($testMode) {
		$studentTableName		= 'wpw1_cwa_student2';
		$advisorTableName		= 'wpw1_cwa_advisor2';
		$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
		$userMasterDataTabeName	= '2p21_cwa_user_master2';
		$modeInfo				= "<p><b>Program Running in TestMode</b></p>";
	} else {
		$studentTableName		= 'wpw1_cwa_student';
		$advisorTableName		= 'wpw1_cwa_advisor';
		$advisorClassTableName	= 'wpw1_cwa_advisorclass';
		$userMasterDataTabeName	= '2p21_cwa_user_master';
		$modeInfo				= "";
	}

	if ($doDebug) {
		echo $modeInfo;
	}
	
	$classArray					= array();
	$classArray['Beginner']		= array();
	$classArray['Fundamental']	= array();
	$classArray['Intermediate']	= array();
	$classArray['Advanced']		= array();
	
	$sql			= "select * from $advisorClassTableName 
						left join $advisorTableName on advisor_call_sign = advisorclass_call_sign 
						left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
						where advisorclass_semester = '$theSemester' 
						order by advisorclass_call_sign, advisorclass_sequence";

	$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
	if ($wpw1_cwa_advisorclass === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {
		$numACRows			= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and found $numACRows rows<br />";
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

				$advisor_ID								= $advisorClassRow->advisor_id;
				$advisor_call_sign 						= strtoupper($advisorClassRow->advisor_call_sign);
				$advisor_semester 						= $advisorClassRow->advisor_semester;
				$advisor_welcome_email_date 			= $advisorClassRow->advisor_welcome_email_date;
				$advisor_verify_email_date 				= $advisorClassRow->advisor_verify_email_date;
				$advisor_verify_email_number 			= $advisorClassRow->advisor_verify_email_number;
				$advisor_verify_response 				= strtoupper($advisorClassRow->advisor_verify_response);
				$advisor_action_log 					= $advisorClassRow->advisor_action_log;
				$advisor_class_verified 				= $advisorClassRow->advisor_class_verified;
				$advisor_control_code 					= $advisorClassRow->advisor_control_code;
				$advisor_date_created 					= $advisorClassRow->advisor_date_created;
				$advisor_date_updated 					= $advisorClassRow->advisor_date_updated;
				$advisor_replacement_status 			= $advisorClassRow->advisor_replacement_status;

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


				// if you need the country name and phone code, include the following
				$countrySQL		= "select * from wpw1_cwa_country_codes  
									where country_code = '$advisorclass_country_code'";
				$countrySQLResult	= $wpdb->get_results($countrySQL);
				if ($countrySQLResult === FALSE) {
					handleWPDBError($jobname,$doDebug);
					$advisorclass_country		= "UNKNOWN";
					$advisorclass_ph_code		= "";
				} else {
					$numCRows		= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
					}
					if($numCRows > 0) {
						foreach($countrySQLResult as $countryRow) {
							$advisorclass_country		= $countryRow->country_name;
							$advisorclass_ph_code		= $countryRow->ph_code;
						}
					} else {
						$advisorclass_country			= "Unknown";
						$advisorclass_ph_code			= "";
					}
				}

				$availableSeats						= 0;
				$useAdvisor							= TRUE;
				
				if ($advisorClass_call_sign == 'K1BG') {
					$useAdvisor						= FALSE;
					if ($doDebug) {
						echo "advisor is $advisorClass_call_sign. Bypassed<br />";
					}
				}
				if ($advisorclass_survey_score == 6 || $advisorclass_survey_score == 13) {
					$useAdvisor						= FALSE;
					if($doDebug) {
						echo "survey score is $advisorclass_survey_score. Bypassed<br />";
					}
				}
				if ($advisor_verify_response == 'R') {
					$useAdvisor						= FALSE;
					if($doDebug) {
						echo "verify response is $advisor_verify_response. Bypassed<br />";
					}							
				}
				if ($advisor_replacement_status == 'N') {
					$useAdvisor						= FALSE;
					if($doDebug) {
						echo "replacement status is $advisor_replacement_status. Bypassed<br />";
					}							
				}
				if ($useAdvisor) {
					// see if there are any seats available
					if ($advisorClass_class_size - $advisorclass_number_students > 0) {
						$availableSeats				= $advisorClass_class_size - $advisorclass_number_students;
						if ($doDebug) {
							echo "advisor's class has $availableSeats seats available<br />";
						}
					}
					if ($availableSeats > 0) {
						if ($doDebug) {
							echo "adding advisorclass info to the classArray<br />";
						}
						$myStr						= substr($advisorClass_class_schedule_times_utc,0,2);
						$advisorClass_class_schedule_times_utc	= $myStr . "00"; 									$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_advisor_call_sign]['local'] 				= "$advisorClass_class_schedule_times $advisorClass_class_schedule_days";
						$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_call_sign]['class size'] 		= $advisorClass_class_size;
						$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_call_sign]['number_students'] 	= $advisorclass_number_students;
						$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_call_sign]['availableSeats'] 	= $availableSeats;
						$classArray[$advisorClass_level]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"][$advisorClass_call_sign]['sequence'] 			= $advisorClass_sequence;
						
					} else {
						if ($doDebug) {
							echo "no seats available<br />";
						}
					}
				}
			}
		}
	}
	if ($doDebug) {
		echo "returning classArray:<br /><pre>";
		print_r($classArray);
		echo "</pre><br />";
	}
				
	return $classArray;
}
add_action('build_list_of_available_classes', 'build_list_of_available_classes');
