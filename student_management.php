function student_management_func() {

/* CW Academy Student Management
 
 
 
  modified 3Mar20 by Roland changed class_completed_date to excluded_advisor
 		and take_previous_class to hold_reason_code
  Modified 5Mar20 by Roland changed last_passed_over_date to hold_override
  		and added hold_override to the Resolve Hold code
  Modified 27May20 by Roland to add a radio button visible only if wr7q is logged in 
 		to cause testMode operation
  Modified 13Oct20 by Roland to handle the advisor_class_timezone field
  Modified 25Oct20 by Roland to include both versions of the color chart
  Modified 15Dec20 by Roland to keep the revised color chart, added student move to a 
 	new semester, and unassigning a replaced student.
  Modified 1Feb2021 by Roland to change student_code to messaging
  Modified 26Mar2021 by Roland to add ability to reassign a student
  Modified 21Jun2021 by Roland to add audit log v2 process
  Modified 30Aug2021 by Roland to add functions to find possible unassigned students
  	for an advisor's class and to find possible advisor classes for an unassigned
  	student. Moved the version to V2
  Modified 27Feb2022 by Roland to add the ability to change a student call sign
  Modified 13Oct22 by Roland for the new timezone process
  Modified 17Apr23 by Roland to fix action_log
  Modified 15Jul23 by Roland to use consolidated tables
  Modified 28Oct24 by Roland for new database formats
  Modified 30Mar25 by Roland to add ability to remove or add an excluded advisor
  
  	
*/

	global $wpdb, $studentTableName, $advisorTableName, $advisorClassTableName, $theSemester, $doDebug;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$initializationArray 		= data_initialization_func();
	$userName 					= $initializationArray['userName'];
	$currentDate 				= $initializationArray['currentDate'];
	$daysToSemester				= $initializationArray['daysToSemester'];
	$validTestmode				= $initializationArray['validTestmode'];
	$siteURL					= $initializationArray['siteurl'];
	$userName 					= $initializationArray['userName'];
	$currentDate 				= $initializationArray['currentDate'];
	$daysToSemester				= $initializationArray['daysToSemester'];
	$validTestmode				= $initializationArray['validTestmode'];
	$siteURL					= $initializationArray['siteurl'];
	$proximateSemester			= $initializationArray['proximateSemester'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$semesterFour				= $initializationArray['semesterFour'];
	$proximateSemester			= $initializationArray['proximateSemester'];
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

	$strPass					= "1";
	$requestType				= '';
	$inp_student_callsign		= '';
	$inp_advisor_callsign		= '';
	$level						= '';
	$actionDate					= date('dMY H:i');
	$logDate					= date('Y-m-d H:i');
	$inp_level					= '';
	$inp_additional				= '';
	$inp_advisorClass			= '';
	$inp_callsign				= '';
	$inp_search					= '';
	$fieldTest					= array('action_log','post_status','post_title','control_code');
	$inp_mode					= '';
	$inp_choice					= '';
	$search_days				= '';
	$search_time				= '';
	$jobname					= 'Student Mgmt: Not Specified';
	$inp_prev_advisor			= '';
	$inp_prev_advisor_class		= '';
	$theURL						= "$siteURL/cwa-student-management/";
	$prodURL					= "https://cwa.cwops.org/cwa-student-management/";
	$colorChartURL				= "$siteURL/cwa-student-and-advisor-color-chart/";
	$studentHistoryURL			= "$siteURL/cwa-show-detailed-history-for-student/";
	$changeSemesterURL			= "$siteURL/cwa-move-student-to-different-semester/";
	$updateUnassignedInfoURL	= "$siteURL/cwa-update-unassigned-student-information/";
	$updateStudentInfoURL		= "$siteURL/cwa-display-and-update-student-signup-information/";
	$pushURL					= "$siteURL/cwa-push-advisor-class/";
	
	$searchRange				= array(0=>'0|400',
										100=>'0|500',
										200=>'0|600',
										300=>'0|700',
										400=>'100|800',
										500=>'200|900',
										600=>'300|1000',
										700=>'400|1100',
										800=>'500|1200',
										900=>'600|1300',
										1000=>'700|1400',
										1100=>'800|1500',
										1200=>'900|1600',
										1300=>'1000|1700',
										1400=>'1100|1800',
										1500=>'1200|1900',
										1600=>'1300|22000',
										1700=>'1400|2100',
										1800=>'1500|2200',
										1900=>'1600|2300',
										2000=>'1700|2400',
										2100=>'1800|2400',
										2200=>'1900|2400',
										2300=>'2000|2400');
	
	if ($testMode) {
		$studentTableName			= 'wpw1_cwa_student2';
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$newAssessmentTableName		= 'wpw1_cwa_new_assessment_data2';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$tempDataTableName			= 'wpw1_cwa_temp_data2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		if ($testMode) {
			echo "Function is under development.<br />";
		}
		$content .= "<p><strong>Function is under development.</strong></p>";
	} else {
		$studentTableName			= 'wpw1_cwa_student';
		$advisorTableName			= 'wpw1_cwa_advisor';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$newAssessmentTableName		= 'wpw1_cwa_new_assessment_data';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment';
		$tempDataTableName			= 'wpw1_cwa_temp_data';
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}



// get the input information
	// set some logicals
	$getStudent					= FALSE;
	$getAdvisor					= FALSE;
	$getClass					= FALSE;
	$getStudentByCallsign		= FALSE;
	$getStudentById				= FALSE;
	$getAdvisorByCallsign		= FALSE;
	$getAdvisorById				= FALSE;
	$getClassByCallsign			= FALSE;
	$getClassById				= FALSE;
	$haveSemester				= FALSE;
	$haveStudent				= FALSE;
	$haveAdvisor				= FALSE;
	$haveClass					= FALSE;
	$preNumSRows				= 0;
	$preNumARows				= 0;
	$preNumCRows				= 0;

	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 				== "inp_student_callsign") {
				$inp_student_callsign 	= $str_value;
				$inp_student_callsign 	= strtoupper(filter_var($inp_student_callsign,FILTER_UNSAFE_RAW));
				$getStudent				= TRUE;
				$getStudentByCallsign	= TRUE;
			}
			if ($str_key 			== "inp_new_callsign") {
				$inp_new_callsign = $str_value;
				$inp_new_callsign = strtoupper(filter_var($inp_new_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 				== "inp_advisor_callsign") {
				$inp_advisor_callsign 	= $str_value;
				$inp_advisor_callsign 	= strtoupper(filter_var($inp_advisor_callsign,FILTER_UNSAFE_RAW));
				$getAdvisor				= TRUE;
				$getAdvisorByCallsign	= TRUE;
			}
			if ($str_key 			== "inp_prev_advisor_class") {
				$inp_prev_advisor_class = $str_value;
				$inp_prev_advisor_class = filter_var($inp_prev_advisor_class,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_prev_advisor") {
				$inp_prev_advisor = $str_value;
				$inp_prev_advisor = strtoupper(filter_var($inp_prev_advisor,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "inp_semester") {
				$inp_semester		= $str_value;
				$inp_semester 		= filter_var($inp_semester,FILTER_UNSAFE_RAW);
				$haveSemester		= TRUE;
			}
			if ($str_key 			== "inp_choice") {
				$inp_choice		 = $str_value;
				$inp_choice 		= filter_var($inp_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "search_days") {
				$search_days		 = $str_value;
				$search_days 		= filter_var($search_days,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "search_time") {
				$search_time		 = $str_value;
				$search_time 		= filter_var($search_time,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_callsign") {
				$inp_callsign		 = strtoupper($str_value);
				$inp_callsign 		= filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "timezone") {
				$inp_timezone		 = $str_value;
				$inp_timezone		 = filter_var($inp_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "level") {
				$inp_level			 = $str_value;
				$inp_level			 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_level") {
				$inp_level			 = $str_value;
				$inp_level			 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_search") {
				$inp_search			 = $str_value;
				$inp_search			 = filter_var($inp_search,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "reqtype") {
				$inp_reqtype		 = $str_value;
				$inp_reqtype		 = filter_var($inp_reqtype,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "studentid") {
				$inp_studentid		 = $str_value;
				$inp_studentid		 = filter_var($inp_studentid,FILTER_UNSAFE_RAW);
				$getStudent			= TRUE;
				$getStudentById		= TRUE;
			}
			if ($str_key 			== "inp_additional") {
				$inp_additional		 = $str_value;
				$inp_additional		 = filter_var($inp_additional,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_advisorClass") {
				$inp_advisorClass		 = $str_value;
				$inp_advisorClass		 = filter_var($inp_advisorClass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "submit") {
				$submit		 = $str_value;
				$submit		 = filter_var($submit,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose		 = $str_value;
				$inp_verbose		 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
		}
	}

//	get the data based on above requests
	if ($doDebug) {
		echo "<br />Get Logicals:<br />";
		if ($getStudent) {
			echo "getStudent is TRUE<br />";
			if ($getStudentByCallsign) {
				echo "getStudentByCallsign is TRUE<br />";
			} else {
				echo "getStudentByCallsign is FALSE<br />";
			}
			if ($getStudentById) {
				echo "getStudentById is TRUE<br />";
			} else {
				echo "getStudentById is FALSE<br />";
			}
		} else {
			echo "getStudent is FALSE<br />";
		}
		if ($getAdvisor) {
			echo "getAdvisor is TRUE<br />";
			if ($getAdvisorByCallsign) {
				echo "getAdvisorByCallsign is TRUE<br />";
			} else {
				echo "getAdvisorByCallsign is FALSE<br />";
			}
			if ($getAdvisorById) {
				echo "getAdvisorById is TRUE<br />";
			} else {
				echo "getAdvisorById is FALSE<br />";
			}
		} else {
			echo "getAdvisor is FALSE<br />";
		}
	}


	// student information
	if ($getStudent) {
		if ($doDebug) {
			echo "<br/>Getting the student information<br />";
		}
		if ($getStudentByCallsign && !$getStudentById) {
			if ($haveSemester) {	
				$sql			= "select * from $studentTableName 
									left join $userMasterTableName on user_call_sign = student_call_sign 
									where student_call_sign = '$inp_student_callsign' 
									and semester = '$inp_semester'";
			} else {			/// no semester. Get the record in the current or future semester
				if ($doDebug) {
					echo "No semester available. Getting current or future record<br >";
				}
				$sql			= "select * from $studentTableName 
									left join $userMasterTableName on user_call_sign = student_call_sign 
									where student_call_sign = '$inp_student_callsign' 
									and (student_semester = '$currentSemester' or 
										 student_semester = '$nextSemester' or 
										 student_semester = '$semesterTwo' or 
										 student_semester = '$semesterThree' or 
										 student_semester = '$semesterFour') 
									limit 1";
			}
		} elseif ($getStudentById) {
			$sql				= "select * from $studentTableName 
									left join $userMasterTableName on user_call_sign = student_call_sign 
									where student_id = $inp_studentid";
		} else {
			if ($doDebug) {
				echo "getStudent is TRUE but neither getStudentByCallsign nor getStudentById is set<br />";
			}
			$getStudent			= FALSE;
		}
		if ($getStudent) {
			$studentResult		= $wpdb->get_results($sql);
			if ($studentResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"getting student infor from _REQUEST");
			} else {
				$preNumSRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $preNumSRows rows<br />";
				}
				if ($preNumSRows > 0) {
					$haveStudent	= TRUE;
					if ($doDebug) {
						echo "haveStudent is TRUE<br />";
					}
					foreach($studentResult as $studentRow) {
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
					}
				}
			}
		}
	}
		
	// get the advisor information
	if ($getAdvisor) {
		if ($doDebug) {
			echo "<br />getting the advisor information<br />";
		}
		if ($getAdvisorByCallsign) {
			if ($haveSemester) {
				$sql			= "select * from $advisorTableName 
									left join $userMasterTableName on user_call_sign = advisor_call_sign 
									where advisor_call_sign = '$inp_advisor_callsign' 
									and advisor_semester = '$inp_semester'";
			} else {
				if ($doDebug) {
					echo "No semester available. Getting current or future record<br >";
				}
				$sql			= "select * from $advisorTableName 
									left join $userMasterTableName on user_call_sign = advisor_call_sign 
									where advisor_call_sign = '$inp_advisor_callsign' 
									and (advisor_semester = '$currentSemester' or 
										 advisor_semester = '$nextSemester' or 
										 advisor_semester = '$semesterTwo' or 
										 advisor_semester = '$semesterThree' or 
										 advisor_semester = '$semesterFour') 
									limit 1";
			}
		} elseif ($getAdvisorById) {
			$sql				= "select * from $advisorTableName 
									left join $userMasterTableName on user_call_sign = advisor_call_sign 
									where advisor_id = $advisorid";
		}
		if ($getAdvisor) {
			$wpw1_cwa_advisor	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				handleWPDBError($jobname,$doDebug,"getting advisor from _REQUEST");
			} else {
				$numARows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					$haveAdvisor	= TRUE;
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$advisor_master_ID 					= $advisorRow->user_ID;
						$advisor_master_call_sign			= $advisorRow->user_call_sign;
						$advisor_first_name 				= $advisorRow->user_first_name;
						$advisor_last_name 					= $advisorRow->user_last_name;
						$advisor_email 						= $advisorRow->user_email;
						$advisor_phone 						= $advisorRow->user_phone;
						$advisor_city 						= $advisorRow->user_city;
						$advisor_state 						= $advisorRow->user_state;
						$advisor_zip_code 					= $advisorRow->user_zip_code;
						$advisor_country_code 				= $advisorRow->user_country_code;
						$advisor_whatsapp 					= $advisorRow->user_whatsapp;
						$advisor_telegram 					= $advisorRow->user_telegram;
						$advisor_signal 					= $advisorRow->user_signal;
						$advisor_messenger 					= $advisorRow->user_messenger;
						$advisor_master_action_log 			= $advisorRow->user_action_log;
						$advisor_timezone_id 				= $advisorRow->user_timezone_id;
						$advisor_languages 					= $advisorRow->user_languages;
						$advisor_survey_score 				= $advisorRow->user_survey_score;
						$advisor_is_admin					= $advisorRow->user_is_admin;
						$advisor_role 						= $advisorRow->user_role;
						$advisor_master_date_created 		= $advisorRow->user_date_created;
						$advisor_master_date_updated 		= $advisorRow->user_date_updated;
	
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
	
						$advisor_last_name 					= no_magic_quotes($advisor_last_name);
		
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

	
function getTheReason($strReasonCode) {
	if ($strReasonCode == 'H') {
		return "(H) Student not promotable but signed up for next class level";
	}
	if ($strReasonCode == 'Q') {
		return "(Q) Student not evaluated but signed up for next class level";
	}
	if ($strReasonCode == 'W') {
		return "(W) Student withdrew but signed up for next class level";
	}
	if ($strReasonCode == 'E') {
		return "(E) Advisor has not evaluated the student who has signed up for next class level";
	}
	if ($strReasonCode == 'A') {
		return "(A) Student hard-assigned to AC6AC";
	}
	if ($strReasonCode == 'B') {
		return "(B) Student is a Bad Actor";
	}
	return "($strReasonCode) unknown";
}	


	function getStudentCount($advisor_callsign,$advisor_sequence) {

		global $wpdb, $studentTableName, $advisorTableName, $advisorClassTableName, $proximateSemester, $doDebug;
		
		$numSRows			= 0;

		$sql				= "select count($student_call_sign) from $studentTableName 
								where student_semester='$proximateSemester' 
									and student_assigned_advisor='$advisor_callsign' 
									and student_assigned_advisor_class='$advisor_sequence'
									and (student_status='Y' or student_status='S')";
		$wpw1_cwa_student		= $wpdb->get_var($sql);
		if ($wpw1_cwa_student == NULL) {
			$numSRows			= 0;
		} else {
			$numSRows			= $wpw1_cwa_student;
		}
		return $numSRows;
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
	
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting. daysToSemester: $daysToSemester<br />";
		}
		$jobname		= "Student Mgmt: ";
		$content 		.= "<h3>$jobname Pass 1</h3>
							<p>Click on the links below to perform the indicated action:</p>
							<ol>
							<li><strong>Useful Functions Before Students are Assigned to Advisors</strong>
								<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>

							<div>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=2' target='_blank'>Pre-assign 
							Student to an Advisor</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('If a 
							student has not been already pre-assigned to an advisor, stores the requested advisor and advisor class 
							in the student record. If the advisor has a class when assigning students to classes, the student will be 
							assigned to that advisor\'s class. Function is only effective before student assignment to advisors is performed.');\">
							<span style='color:orange;'><em>Note</em></span></div></div>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=5' target='_blank'>Delete Student's 
							Pre-assigned Advisor</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('Removes the pre-assigned 
							advisor from a student record. Function is only effective before student assignment to advisors is performed.');\">
							<span style='color:orange;'><em>Note</em></span></div></div>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$colorChartURL' target='_blank'><b>Color Chart</b> - Display 
							Student and Advisor Statistics</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('Displays 
							a chart of the number of students requesting classes at a certain time and the number of advisors offering 
							classes at that time. The list of students or advisors can be displayed and individual student or advisor 
							records updated. Function is only effective before student assignment to advisors is performed. Once the 
							student assignment to advisors is completed, use the Number One report.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=7' target='_blank'>List Students Needing Intervention</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('Displays any students in the upcoming semester that have some type if intervention required. 
							The program Resolve Student Hold is used to resolve the situation.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=25' target='_blank'>Resolve Student 
							Hold<a/></div>
							<div style='float:right;'><a href=\"javascript:window.alert('Removes the intervention_required hold code 
							and sets the hold_override code so this hold will not be applied again to this student record. Function will have 
							no effect after students are assigned to advisors.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=100' target='_blank'>Confirm One or More Students</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The function will verify the each student is unassigned and not 
							on hold. If verified, the response is set to Y');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>
							
							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=110' target='_blank'>Add Excluded Advisor to a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('If the specified advisor is not excluded from the 
							specified student already, then the student record is updated with the specified excluded advisor');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>
							
							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=120' target='_blank'>Remove Excluded Advisor from a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The specified advisor will be removed from all specified student 
							records');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>

							<div style='clear:both;'></div>
								</ol>
	
								<br />
							<li><strong>Useful Functions Any Time</strong>
								<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$studentHistoryURL' target='_blank'>Show Detailed History for a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('For a specific student call sign, reads the student table and past_student table 
							and displays an entry for each record for that student.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$changeSemesterURL' target='_blank'>Change unassigned student's semester</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The program allows the student semester as well as class choices 
							to be updated. The student must be unassigned.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$updateUnassignedInfoURL' target='_blank'>Update Unassigned Student Info</a> (class priority and/or class choices)</div>
							<div style='float:right;'><a href=\"javascript:window.alert('Allows a student to be selected by student ID, student name, 
							or student call sign (most common use). The information about the student is display and can be updated.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=60' target='_blank'>Unassign and Remove a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('This function removes ths student from an advisor class and sets the 
								student_status to C. Otherwise, sets the student response to R');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=20' target='_blank'>Exclude an Advisor from being Assigned to a Specific Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('This is the opposite of overriding an excluded advisor. The program places an X 
							in the Intervention Required field and and the advisor to be excluded in the excluded_advisor part of the student record.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=30' target='_blank'>Override Excluded Advisor</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('Program removes the excluded_advisor, setting that field to blank,
							and removes the X from intervention_required. The advisor is no longer excluded.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

								</ol><br /><br />
							<li><strong>Useful Functions After Students Have Been Assigned to Advisors</strong>
								<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=35' target='_blank'>Move Student to a Different Level and Unassign</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The student must have a status of Y or S, otherwise no action will be taken 
							The student is unassigned from the current class, if already assigned. 
							The student level is changed to the desired level,and the student is returned to the unassigned pool.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=40' target='_blank'>Add Unassigned Student to an Advisor's Class</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The student call sign, advisor call sign, and the advisor and class where the student will be 
							assigned should be known. If the student is not unassigned, the function will fail.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=50' target='_blank'>Unassign a Student Regardless of Status</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('This is a Brute Force function that will unassign a student 
							regardless of current situations.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em'><a href='$theURL?strpass=55' target='_blank'>Re-assign a Student to Another Advisor</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The program removes the student from an advisor and reassigns 
							the student to a different advisor. The advisors must have classes at the students requested time.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=70' target='_blank'>Find Possible Classes for a Student</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The program takes a student current class choices and looks for any class that might match the 
							student class selection criteria. If a possibility is found, the matching information is displayed and the student can be 
							added to the requested class.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=80' target='_blank'>Find Possible Students for an Advisor's Class</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The program takes the class schedule from the requested advisor and 
							looks at all unassigned students to determine of one of them can be assigned to the advisor. The program considers student shoices 
							an hour earlier and an hour later.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=90' target='_blank'>Assign a Student to an Advisor</a> regardless of status or semester</div>
							<div style='float:right;'><a href=\"javascript:window.alert('This function is used to pull a student from a future semester registration 
							and assigning the student to an advisor. ');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>	

							<div style='clear:both;'>
							<div style='float:left;'>
								<li style='margin-left:2em;'><a href='$theURL?strpass=85' target='_blank'>Verify One or More Students</a></div>
							<div style='float:right;'><a href=\"javascript:window.alert('The function will verify that each 
							student is assigned to a class and has a student_status of S. If so, the student_status will be set to Y.');\">
							<span style='color:orange;'><em>Note</em></span></span></div></div>

							<div style='clear:both;'>
							</div>
								</ol>
							</ol>";

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}
		$jobname		= "Student Management Pre-assign Student to an Advisor";
		$content .= "<h3>Pre-assign Student to an Advisor</h3>
					<p><form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data''>
					<input type='hidden' name='strpass' value='3'>
					<input type='hidden' name='inp_mode' value='$inp_mode'>
					<table style='border-collapse:collapse;'>
					<tr><th colspan='2'>Pre-Assign Student to an Advisor</th></tr>
					<tr><td style='width:150px;'>Student Call Sign</td><td>
					<input class='formInputText' type='text' name='inp_student_callsign' size='10' maxlenth='10' value='$inp_student_callsign' autofocus /></td></tr>
					<tr><td style='width:150px;'>Advisor Call Sign</td><td>
					<input class='formInputText' type='text' name='inp_advisor_callsign' size='10' maxlenth='10' value='$inp_advisor_callsign' /></td></tr>
					<tr><td style='vertical-align:top;'>Additional Comments</td><td>
					<textarea class='formInputText' name='inp_additional' rows='5' cols='50'></textarea></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
					</form>
					<br />
					<p>Enter the student call sign that is to be pre-assigned (must be 
					registered in the upcoming semester) and then 
					the advisor call sign to whom the student is to be assigned. If there are any add'l 
					comments to be added to the action_log, enter those as well. If the student is registered 
					and the advisor is registered, a list of the classes the advisor is teaching will be displayed. 
					Select the appropriate class for the student. After that the pre-assignment will be made.</p>
					<p>This function updates the \"pre_assigned_advisor\" field in the student's record. When the program is 
					run to do the student assignments to advisors, this student will be assigned to the 
					advisor first before any other unassigned students are put in the advisor's class.</p>";

	} elseif ("3" == $strPass) {
// $doDebug = TRUE;
		if ($doDebug) {
			echo "<br />At pass 3 with student $inp_student_callsign<br />
					advisor $inp_advisor_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE. preNumSRows: $preNumSRows<br />";
				if ($preNumSRows > 0) {
					echo "Semester: $student_semester; level=$student_level<br />";
				}
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE. preNumARows: $preNumARows<br />";
				if ($preNumARows > 0) {
					echo "Semester: $advisor_semester<br />";
				}	
			}
		}

		$jobname			= "Student Management Pre-assign Student to an Advisor";
		$content			.= "<h3>$jobname</h3>";
		if ($haveStudent) {
			if ($student_response == '') {
				$responseSTR	= 'blank';
			} else {
				 $responseSTR	= $student_response;
			}
			if ($student_response != 'Y') {
				$content 		.= "<p><b>NOTE:</b> Student is not validated (response = $responseSTR)</p>";
			}
		} else {
			$content		.= "<p>No student record found for $inp_student_callsign</p>";
		}
		// log that the job was run
		$thisDate			= date('Y-m-d');
		$thisTime			= date('H:i:s');

		// do we have student and advisor records?
		
		if ($haveStudent && $haveAdvisor && $student_semester == $advisor_semester) {
			if ($doDebug) {
				echo "have most of the data. Need class records<br />";
			}

			$gotError				= FALSE;
			if ($student_pre_assigned_advisor != '') {
				if ($doDebug) {
					echo "Student has a pre-assigned advisor already<br />";
				}
				$content			.= "<p>Student $inp_student_callsign already has a pre-assigned advisor of $student_pre_assigned_advisor. 
										Please unassign this advisor before making a new pre-assignment.<p>";
				$gotError			= TRUE;
			}
			if ($student_email_number == 4 && $student_response != 'Y') {
				if ($doDebug) {
					echo "Student has been dropped<br />";
				}
				$content			.= "<p>Student $inp_student_callsign failed to respond to verification requests and has been dropped. Pre-assignment refused.</p>";
				$gotError			= TRUE;
			}
			if ($student_response == 'R') {
				if ($doDebug) {
					echo "Student has refused the verification<br />";
				}
				$content			.= "<p>Student $inp_student_callsign responded 'refused' to the verification request. Pre-assignement refused.</p>";
				$gotError			= TRUE;
			}
			if (!$gotError) {
				if ($doDebug) {
					echo "no errors. Checking advisor<br />";
				}
				// get the classes for the advisor
				$optionList				= '';
				$optionCount			= 0;
				$listOptionClass		= 0;
				$sql					= "select * from $advisorClassTableName 
											where advisorclass_semester='$student_semester' 
												and advisorclass_call_sign='$inp_advisor_callsign' 
												and advisorclass_level='$student_level'";
				$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisorclass === FALSE) {
					handleWPDBError("$jobname MGMT 3",$doDebug);
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
							$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
							$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
							$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
							$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
							$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
							$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
							$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
							$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
							$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;

							$thisChecked	= '';
							if ($numACRows == 1) {
								$thisChecked	= ' checked ';
							}
							$optionList		.= "<input type='radio' class='formInputButton' name='inp_advisorClass' value='$advisorClass_sequence' $thisChecked required> Class nbr $advisorClass_sequence $advisorClass_level at $advisorClass_class_schedule_times local on $advisorClass_class_schedule_days<br />";
							$optionCount++;
							$lastOptionClass	= $advisorClass_sequence;
						}
						$content	.= "<p>Select the class to which the student $inp_student_callsign is to  be assigned and click 'Next'</p>
										<p><form method='post' action='$theURL' 
										name='selection_form' ENCTYPE='multipart/form-data''>
										<input type='hidden' name='strpass' value='4'>
										<input type='hidden' name='inp_student_callsign' value='$inp_student_callsign'>
										<input type='hidden' name='inp_advisor_callsign' value='$inp_advisor_callsign'>
										<input type='hidden' name='inp_additional' value='$inp_additional'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<table style='border-collapse:collapse;'>
										<tr><th colspan='2'>Pre-Assign Student to Advisor $inp_advisor_callsign's Class</th></tr>
										<tr><td style='width:150px;'>Advisor Class(es)</td><td>
										$optionList
										</td></tr>
										<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
										</form></p>";
					} else {
						if ($doDebug) {
							echo "No advisor found with the call sign $inp_advisor_callsign in the $nextSemester semester in level $student_level<br />";
						}
						$content	.= "Advisor $inp_advisor_callsign does not have a $student_level class in the $nextSemester semester.";
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "Do not have the matching information<br />";
			}
			$myStr			= "";
			if ($haveStudent) {
				$myStr		.= "A student record at level $student_level for $student_semester semester was found<br />";
			} else {
				$myStr		.= "No student record for current or future semesters found<br />";
			}
			if ($haveAdvisor) {
				$myStr		.= "An advisor record for $advisor_semester semester was found<br />";
			} else {
				$myStr		.= "No advisor record for $inp_advisor_callsign found in the current and future semesters<br />";
			}
			if ($haveStudent && $haveAdvisor) {
				if ($student_semester != $advisor_semester) {
					$myStr	.= "Student semester ($student_semester) and advisor semester ($advisor_semester) do not match<br />";
				} else {
					$myStr	.= "Student semester ($student_semester) and advisor semester ($advisor_semester) match. NO IDEA why this didn't work!<br />";
				}
			}
			$content		.= "The pre-assignment can not be made:<br />$myStr";
		}


////////////// pass 4

	
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 4 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}
		// do the actual pre-assignment

// $doDebug	= TRUE;

		$jobname			= "Student Management Pre-assign Student to an Advisor";
		$content			.= "<h3>$jobname</h3>";

		if ($haveStudent && $haveAdvisor) {			
			// set up and do the pre-assignment
			$updateParams	= array();
			$student_action_log 					= "$student_action_log / $actionDate MGMT1 $userName pre-assigned advisor $inp_advisor_callsign ";
			$updateParams['student_pre_assigned_advisor'] 	= $inp_advisor_callsign;
			$updateParams['student_assigned_advisor_class']	= $inp_advisorClass;
			if ($inp_additional != '') {
				$student_action_log					= "$student_action_log $inp_additional ";
			}
			$updateParams['student_action_log'] 			= $student_action_log;
			$updateFormat							= array('%s','%d','%s');
			$studentUpdateData		= array('tableName'=>$studentTableName,
											'inp_data'=>$updateParams,
											'inp_format'=>$updateFormat,
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
				handleWPDBError("$jobname MGMT 4",$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
			} else {
				$content	.= "<p>Student $inp_student_callsign now has advisor $inp_advisor_callsign pre-assigned and advisor
								has a class at the student's level.</p>";
			}
		}
		$content	.= "<p>Click <a href='$theURL?strpass=2'>here</a> to do another pre-assignment.</p>";





////  pass 5   Delete a pre-assigned advisor

	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 5<br />";
		}
	
		$jobname	= "Student Management Delete a Pre-assigned Advisor";
		$content 	.= "<h3>$jobname</h3>
						<p>Enter the student call sign that has the pre-assigned advisor.<p>
						<p>This process works until the program is run to assign students to 
						advisors and make up classes. Once that has happened, using this function 
						will have no effect.</p>
						<p><form method='post' action='$theURL' 
						name='selection_form' ENCTYPE='multipart/form-data''>
						<input type='hidden' name='strpass' value='6'>
						<input type='hidden' name='inp_mode' value='$inp_mode'>
						<table style='border-collapse:collapse;'>
						<tr><th colspan='2'>Delete Student's Pre-assigned Advisor</th></tr>
						<tr><td style='width:150px;'>Student Call Sign</td><td>
						<input class='formInputText' type='text' name='inp_student_callsign' size='10' maxlenth='10' /></td></tr>
						<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Make the Deletion' /></td></tr></table>
						</form>
						<br />
						<p>This function updates the 'pre_assigned_advisor' field in the student's record. When the program is 
						run to do the student assignments to advisors, this student will be handled as a normal 
						unassigned student.</p>";


/////// Pass 6			Delete the pre-assigned advisor


	} elseif ("6" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 6 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}
	
		// do the actual deletion
		$jobname				= "Student Management Delete a Pre-assigned Advisor";	
		if ($inp_student_callsign == '') {
			$content 			.= "No student call sign entered. Process aborted.";
		} else {
			// log that the job was run
			$thisDate			= date('Y-m-d');
			$thisTime			= date('H:i:s');

			if ($haveStudent) {	
				if ($doDebug) {
					echo "Retrieved $student_call_sign with pre-assigned advisor of $student_pre_assigned_advisor<br />";
				}
				$student_action_log 					= "$student_action_log / $actionDate MGMT2 $userName deleted pre-assigned advisor $student_pre_assigned_advisor ";
				$updateParams							= array();
				$updateParams['student_intervention_required']	= '';
				$updateParams['student_pre_assigned_advisor']	= '';
				$updateParams['student_assigned_advisor_class']	= '0';
				$updateParams['student_action_log']				= $student_action_log;
				$updateFormat							= array('%s','%s','%s','%s');
				
				$studentUpdateData		= array('tableName'=>$studentTableName,
												'inp_data'=>$updateParams,
												'inp_format'=>$updateFormat,
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
					handleWPDBError("$jobname MGMT 6",$doDebug);
				} else {
					$content	.= "<p>Pre-assigned advisor removed</p>";
				}
			} else {
				$content	.= "No student with call sign $inp_callsign found in $studentTableName.<br />";
			}
		}


////  	pass 7	List student needing intervention		
	} elseif ("7" == $strPass) {

		$jobname			= "Student Management List Students Needing Intervention";
		$content			.= "<h3>$jobname</h3>
								<p>Click 'List Students Needing Intervention' to run the report</p><table>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='8'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								$testModeOption
								<tr><td><input class='formInputButton' type='submit' value='List Students Needing Intervention' /></td></tr></table>
								</form>";
		
	} elseif ("8" == $strPass) {
		
	///// list students needing intervention assistance
		$jobname			= "Student Management List Students Needing Intervention";

		//	get the student records

		$sql					= "select * from $studentTableName 
									left join $userMasterTableName on user_call_sign = student_call_sign 
									where student_intervention_required != '' 
									and (student_semester = '$proximateSemester' or 
									     student_semester = '$nextSemester' or 
									     student_semester = '$semesterTwo' or 
									     student_semester = '$semesterThree' or 
									     student_semester = '$semesterFour') 
									     order by student_call_sign";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError("$jobname MGMT 8",$doDebug);
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				$content	.= "<h3>Students Needing Intervention</h3>
								<table><tr>
									<th>ID</th>
									<th>Name</th>
									<th>Email</th>
									<th>Phone</th>
									<th>City</th>
									<th>State</th>
								</tr><tr>
									<th>Time Zone</th>
									<th>Level</th>
									<th>Semester</th>
									<th>Response</th>
									<th>Status</th>
									<th>Advisor</th>
								</tr><tr>
									<th>Type Intervention</th>
									<th colspan='5'>Reason</th>
								</tr>";
				$myInt										= 0;
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
					$student_master_action_log 			= $studentRow->user_action_log;
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

					// if you need the country name and phone code, include the following
					$countrySQL		= "select * from wpw1_cwa_country_codes  
										where country_code = '$student_country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$student_country		= "UNKNOWN";
						$student_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$student_country		= $countryRow->country_name;
								$student_ph_code		= $countryRow->ph_code;
							}
						} else {
							$student_country			= "Unknown";
							$student_ph_code			= "";
						}
					}

					$student_last_name 						= no_magic_quotes($student_last_name);
						
					if ($doDebug) {
						echo "Processing call sign $student_call_sign. Intervention Required: $student_intervention_required; hold_reason_code: $student_hold_reason_code<br />";
					}
					// don't worry about hold_reason_code = A
					if ($student_hold_reason_code != 'A') {
						if ($student_intervention_required == 'H') {   	// only interested in those requiring intervention
							$IRType				= '';
							$IRReason			= '';
							if ($student_hold_reason_code == 'M') {
								$IRType			= "M: Moved";
								$IRReason		= "unknown";
							}
							if ($student_hold_reason_code == 'Q') {
								$IRType			= "Q: Advisor Quit";
								$IRReason		= getTheReason($student_hold_reason_code);
							}
							if ($student_hold_reason_code == 'H') {
								$IRType			= "H: Assignment on Hold";
								$IRReason		= getTheReason($student_hold_reason_code);
							}
							if ($student_hold_reason_code == 'E') {
								$IRType			= "H: Student on Hold";
								$IRReason		= "(E) Student not evaluated but asking for next class level";
							}
//??							if ($student_hold_reason_code == 'X') {
//??								$IRType			= "H: Student on Hold";
//??								$IRReason		= "(X) Student is being recycled to unassigned";
//??							}
							if ($student_hold_reason_code == 'W') {
								$IRType			= "H: Student on Hold";
								$IRReason		= "(W) Student withdrew but asking for next class level";
							}
							$thisAdvisor		= '';
							if ($student_assigned_advisor != '') {
								$thisAdvisor	= $student_assigned_advisor;
							} else {
								$thisAdvisor		= $student_pre_assigned_advisor;
							}
							$newActionLog		= formatActionLog($student_action_log);
							$content	.= "<tr><td>$student_ID</td>
												<td>$student_last_name, $student_first_name, ($student_call_sign)</td>
												<td>$student_email</td>
												<td>$student_phone</td>
												<td>$student_city</td>
												<td>$student_state</td>
											</tr><tr>
												<td>$student_timezone_id $student_timezone_offset</td>
												<td>$student_level</td>
												<td>$student_semester</td>
												<td>$student_response</td>
												<td>$student_status</td>
												<td>$thisAdvisor</td>
											</tr><tr>
												<td colspan='2'>$IRType</td>
												<td colspan='4'>$IRReason</td>
											</tr><tr>
												<td colspan='6'>$newActionLog</td>
											</tr><tr>
												<td colspan='6'><hr /></td>
											</tr>";
							$myInt++;
						}
					}
				}
				$content	.= "</table>$myInt records displayed<br />";
			} else {			// no records in the table
				$content 	.= "No students needing intervention records found in $studentTableName table<br />";
			}
		}
		

		
//// 	pass 20		Exclude an advisor from being assigned to a specific student		
		
	} elseif ("20" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 20<br />";
		}

		$jobname		= "Student Management Exclude an Advisor";
		$content 		.= "<h3>$jobname from being Assigned to a Specific Student</h3>
							<p>Enter the student call sign of the record to be updated and the advisor 
							call sign to be excluded from assignment to the student.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='21'>
							<table style='border-collapse:collapse;'>
							<tr><th colspan='2'>Exclude an Advisor</th></tr>
							<tr><td style='width:150px;'>Student Call Sign</td><td>
							<input class='formInputText' type='text' name='inp_student_callsign' size='10' maxlenth='10' /></td></tr>
							<tr><td style='width:150px;'>Advisor Call Sign to be Excluded</td><td>
							<input class='formInputText' type='text' name='inp_advisor_callsign' size='10' maxlenth='10' /></td></tr>
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Make the Exclusion' /></td></tr></table>
							</form>";


////////	pass 21 	Do the actual advisor exclusion

	} elseif ("21" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 21 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}
	
		$jobname			= "Student Management Exclude an Advisor";

		if ($haveStudent) {				
			if ($doDebug) {
				echo "Processing student $student_call_sign<br />
					  &nbsp;&nbsp;&nbsp;Advisor: $student_advisor<br />
					  &nbsp;&nbsp;&nbsp;Excluded Advisor: $student_assigned_advisor<br />
					  &nbsp;&nbsp;&nbsp;Hold Reason Code: $student_hold_reason_code<br />
					  &nbsp;&nbsp;&nbsp;Intervention Required: $student_intervention_required<br />";
			}
			$daysToSemester							= days_to_semester($student_semester);
			if ($daysToSemester < 19) { 				// student assignment to advisor has probably been run
				$content .= "<p>Since the process to assign students to an advisor has been run
							for this semester, changing or removing a pre-assigned advisor will have no affect. 
							This process only works 
							until the program is run to assign students to 
							advisors and make up classes. Once that has happened, using this function 
							to attempt to exclude an advisor will have no effect. The appropriate action may be to click on 
							<a href='$theURL?strpass=45'>Remove Student from an Advisor's Class</a></p>";
			} else {					
				if ($student_intervention_required != '') {
					$content .= "<p>Student has intervention required: $student_intervention_required</p>";
				}
				$isExcluded		= FALSE;
				if ($student_excluded_advisor != '') {
					/// see if the advisor is already excluded
					if (str_contains($student_excluded_advisor,$inp_advisor_callsign)) {
//					$myInt	= strpos($student_excluded_advisor,$inp_advisor_callsign);
//					if ($myInt !== FALSE) {
						$content .= "<p>Student already has an excluded advisor. Process aborted.</p>";
						$isExcluded	= TRUE;
					}
				}
				if (!$isExcluded) {	
					if ($student_hold_reason_code != '') {
						$content .= "<p>Student 'hold_reason_code' is not blank: $student_hold_reason_code. Process aborted.</p>";
					} else {			// all OK. can do the update
						if ($student_action_log == '') {
							$student_action_log	= "$actionDate MGMT3 $userName Excluded advisor $inp_advisor_callsign from being assigned to student ";
						} else {
							$student_action_log	.= " / $actionDate MGMT3 $userName Excluded advisor $inp_advisor_callsign from being assigned to student ";
						}
						if ($student_excluded_advisor == '') {
							$student_excluded_advisor	= $inp_advisor_callsign;
						} else {
							$student_excluded_advisor	.= "&$inp_advisor_callsign";
						}
						$updateParams	= array('student_excluded_advisor'=>"$student_excluded_advisor",
												'student_action_log'=>"$student_action_log");
						$updateFormat	= array('%s','%s');
	
						$studentUpdateData		= array('tableName'=>$studentTableName,
														'inp_data'=>$updateParams,
														'inp_format'=>$updateFormat,
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
							handleWPDBError("$jobname MGMT 21",$doDebug);
						} else {
							$content .= "<p>Student record updated to exclude advisor $inp_advisor_callsign.</p>";
						}
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "No record found in $studentTableName table for $inp_student_callsign<br />";
			}
			$content			.= "No record found in $studentTableName table for $inp_student_callsign";
		}



		
///// 	pass 25		Resolve Student Hold		
		
		
	} elseif ("25" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 25<br />";
		}
	
// $testMode	= TRUE;
		$jobname		= "Student Management Resolve Student Hold";
		$content 		.= "<h3>$jobname</h3>
							<p>Enter the student call sign below. The hold information will be displayed after which 
							you can decide to proceed to remove the hold information.</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='26'>
							<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Student Call Sign</td><td>
							<input class='formInputText' type='text' maxlength='30' name='inp_student_callsign' size='10' autofocus></td></tr>
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";

/////	pass 26		display student hold information

	} elseif ("26" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 26 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}

// $testMode	= TRUE;
		$jobname				= "Student Management Resolve Student Hold";
		if ($haveStudent) {
			if ($doDebug) {
				echo "found intevention_required of $student_intervention_required<br />";
			}
			if ($student_intervention_required != '') {   	// only interested in those requiring intervention
				$content	.= "<h3>$jobname on $inp_student_callsign</h3>
								<table><tr>
									<th>ID</th>
									<th>Name</th>
									<th>Email</th>
									<th>Phone</th>
									<th>City</th>
									<th>State</th>
								</tr><tr>
									<th>Time Zone</th>
									<th>Level</th>
									<th>Semester</th>
									<th>Response</th>
									<th>Status</th>
									<th>Pre-assigned<br />Advisor</th>
								</tr><tr>
									<th>Type Intervention</th>
									<th colspan='5'>Reason</th>
								</tr>";

				$IRType				= '';
				$IRReason			= '';
				if ($student_intervention_required == 'M') {
					$IRType			= "Moved";
					$IRReason		= "unknown";
				}
				if ($student_intervention_required == 'Q') {
					$IRType			= "Advisor Quit";
					$IRReason		= getTheReason($student_hold_reason_code);
				}
				if ($student_intervention_required == 'H') {
					$IRType			= "Assignment on Hold";
					$IRReason		= getTheReason($student_hold_reason_code);
				}
				$newActionLog		= formatActionLog($student_action_log);
				$content	.= "<p>Click on 'Delete this Hold' to remove the hold.
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='27'>
								<input type='hidden' name='studentid' value='$student_ID'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<tr><td>$student_ID</td>
									<td>$student_last_name, $student_first_name, ($student_call_sign)</td>
									<td>$student_email</td>
									<td>$student_phone</td>
									<td>$student_city</td>
									<td>$student_state</td>
								</tr><tr>
									<td>$student_timezone_id $student_timezone_offset</td>
									<td>$student_level</td>
									<td>$student_semester</td>
									<td>$student_response</td>
									<td>$student_status</td>
									<td>$student_pre_assigned_advisor</td>
								</tr><tr>
									<td>$IRType</td>
									<td colspan='5'>$IRReason</td>
								</tr><tr>
									<td colspan='6'>$newActionLog</td>
								</tr><tr>
									<td colspan='6'><hr /></td>
								</tr></table>
								<input type='submit' class='formInputButton' name='Delete this Hold' value='Delete this Hold'
								onclick=\"return confirm('Are you sure you want to delete this hold information?');\">
								</form></p>";
			} else {
				$content	.= "<p>The student $student_call_sign is not on hold. No action taken.</p>";
			}
		} else {
			if ($doDebug) {
				echo "no record found in $studentTableName table for $inp_student_callsign<br />";
			}
			$content		.= "No record found in $studentTableName table for $inp_student_callsign";
		}
		
		
		
////////	pass 27		Do the hold removal
	} elseif ("27" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 27 with studentid of $inp_studentid<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}
	
// $testMode	= TRUE;
//   $doDebug		= TRUE;
		$jobname			= "Student Management Resolve Student Hold";
		if ($haveStudent) {
			$updateParams							= array();
			$updateParams['student_intervention_required']	= '';
			$updateParams['student_hold_override']			= 'Y';
			$doUpdate								= TRUE;
			$student_action_log 					.= " / $actionDate MGMT27 $userName removed hold_reason_code of $student_hold_reason_code ";
			$updateParams['student_action_log']				= $student_action_log;
			$updateFormat							= array('%s','%s','%s');
			$studentUpdateData		= array('tableName'=>$studentTableName,
											'inp_data'=>$updateParams,
											'inp_format'=>$updateFormat,
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
				handleWPDBError("$jobname MGMT 27",$doDebug);
			} else {
				$content			.= "Hold is removed from $student_call_sign.<br />"; 
			}
		} else {
			$content	.= "Did not retrieve any records with student ID of $inp_studentid";
		}


////////	Pass 30			Override Excluded Advisor
	} elseif ("30" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 30<br />";
		}
	
// $testMode	= TRUE;
		$jobname			= "Student Management Override Excluded Advisor";

		$content 		.= "<h3>$jobname</h3>
							<p>Enter the student call sign below where the exclusion is to be removed.</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='31'>
							<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign</td>
									<td><input class='formInputText' type='text' maxlength='30' name='inp_student_callsign' size='10' autofocus></td></tr>
								<tr><td>Advisor exclusion to be removed</td>
									<td><input class='formInputText' type='text' maxlength='30' name='inp_advisor_callsign' size='10' autofocus></td></tr>
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";


///////		Pass 31			Remove the exclusion
	} elseif ("31" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 31 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}

// $testMode	= TRUE;
		$jobname			= "Student Management Override Excluded Advisor";
		$madeChange			= FALSE;
		if ($haveStudent) {
			$test1			= "/$inp_advisor_callsign/";
			$test2			= "/\|$inp_advisor_callsign/";
			$test3			= "/&$inp_advisor_callsign/";
			if (preg_match($test1,$student_excluded_advisor)) {
				if (preg_match($test2,$student_excluded_advisor)) {
					$student_excluded_advisor = str_replace("\|$inp_advisor_callsign","",$student_excluded_advisor);
					$madeChange			  = TRUE;
				} elseif (preg_match($test3,$student_excluded_advisor)) {
					$student_excluded_advisor = str_replace("&$inp_advisor_callsign","",$student_excluded_advisor);
					$madeChainge			  = TRUE;
				}
			} else {
				$student_excluded_advisor = str_replace($inp_advisor_callsign,"",$student_excluded_advisor);
				$madeChainge			  = TRUE;
			}
			if ($madeChange) {
				$student_action_log		= "$student_action_log / $actionDate MGMT30 removed excluded advisor $inp_advisor_callsign";
				$updateParams			= array('student_excluded_advisor'=>$student_excluded_advisor,
												'student_action_log'=>$student_action_log);
				$updateFormat			= array('%s','%s');
				$studentUpdateData		= array('tableName'=>$studentTableName,
												'inp_data'=>$updateParams,
												'inp_format'=>$updateFormat,
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
					handleWPDBError("$jobname MGMT 31",$doDebug);
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError("$jobname MGMT 31",$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						if (!$doDebug) {
							return $content;
						}
					}

					$content		.= "<br />Sucessfully overode the exluded advisor<br />";
				}
			}
		} else {
			$content	.= "Found incongruous number of records for $inp_student_callsign: $numberSRows. Process aborted.";
		}
					

		
//////		Pass 35		Change a student level and put in unassigned pool
	} elseif ("35" == $strPass) {
	
		if ($doDebug) {
			echo "<br />at pass 35<br />";
		}
		$jobname		= "Student Management Move Student to a Different Level";
		$content 		.= "<h3>$jobname</h3>
							<p>Enter the student call sign below and select the new level for the student. 
							If the student is currently assigned to an advisor's class, the student will be 
							removed from the class. The student status will be set to blank, meaning the 
							student is unassigned and available for assignment.</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='36'>
							<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Student Call Sign:</td><td>
							<input class='formInputText' type='text' maxlength='30' name='inp_student_callsign' size='10' autofocus></td></tr>
							<tr><td style='width:150px;'>New Level:</td><td>
							<input type='radio' class='formInputButton' name='level' value='Beginner' required>Beginner<br />
							<input type='radio' class='formInputButton' name='level' value='Fundamental' required>Fundamental<br />
							<input type='radio' class='formInputButton' name='level' value='Intermediate' required>Intermediate<br />
							<input type='radio' class='formInputButton' name='level' value='Advanced' required>Advanced</td></tr>
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";



	} elseif ("36" == $strPass) {				// do the actual level move
		if ($doDebug) {
			echo "<br />at pass 36 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}
// $testMode	= TRUE;
// $doDebug		= TRUE;

		$jobname		= "Student Management Move Student to a Different Level";
		$content		.= "<h3>$jobname</h3>";
		if ($inp_level == '') {
			$content	.= "The level to which the student is to be moved was not specified.";
		} else {
			if ($haveStudent) {
				$doUnassign			= FALSE;
				$changeLevel		= FALSE;

				if ($doDebug) {
					echo "Student call sign: $student_call_sign<br />
						  &nbsp;&nbsp;&nbsp;Response: $student_response<br />
						  &nbsp;&nbsp;&nbsp;Status: $student_status<br />
						  &nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor<br />";
				}

				if ($inp_level == $student_level) {
					if ($student_status == 'Y') {
						$content	.= "<p>Student is currently at the requested level of $inp_level, is
										assigned to $student_assigned_advisor's class. The level
										will remain unchanged but $student_call_sign will be removed 
										from the class and returned to the unassigned pool.</p>";
						$doUnassign	= TRUE;
					}
					if ($student_status == 'S') {
						$content	.= "<p>Student is currently at the requested level of $inp_level, and
										while assigned to $student_assigned_advisor, the advisor has not
										verified the student. Student will be moved to the unassigned pool.</p>";
						$doUnassign	= TRUE;
					}
					if ($student_status == 'C') {
						$content	.= "<p>Student is currently at the requested level of $inp_level. The student
										was already removed from $student_assigned_advisor's class and replaced. 
										No action taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == 'N') {
						$content	.= "<p>Student is currently at the requested level of $inp_level. The student
										was already removed from $student_assigned_advisor's class. 
										No action taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == 'R') {
						$content	.= "<p>Student is currently at the requested level of $inp_level. The student
										was already removed from $student_assigned_advisor's class and a replacement
										requested. No action taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == '') {			// unassigned
						$content	.= "<p>Student is currently at the requested level of $inp_level. The student 
										is also unassigned. No action taken.</p>";
					}
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}

				} else {				// student level is not same as requested level

					if ($student_status == 'Y') {
						$content	.= "<p>Student is assigned to $student_assigned_advisor's class. The level
										will be changed from $student_level to $inp_level, $student_call_sign will be removed 
										from the class, and returned to the unassigned pool.</p>";
						$doUnassign		= TRUE;
						$changeLevel	= TRUE;
					}
					if ($student_status == 'S') {
						$content	.= "<p>Student is assigned to $student_assigned_advisor, but the advisor has not
										verified the student. Student will be moved to the unassigned pool 
										and the level changed from $student_level to $inp_level.</p>";
						$doUnassign		= TRUE;
						$changeLevel	= TRUE;
					}
					if ($student_status == 'C') {
						$content	.= "<p>The student was already removed from 
										$student_assigned_advisor's class and replaced. 
										No action needs to be taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == 'N') {
						$content	.= "<p>The student was already removed from 
										$student_assigned_advisor's class and replaced. 
										No action needs to be taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == 'R') {
						$content	.= "<p>The student was already removed from 
										$student_assigned_advisor's class and replaced. 
										No action needs to be taken.</p>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;No action<br />";
						}
					}
					if ($student_status == '') {
						$content	.= "<p>The student is unassigned. Level will be changed from
										$student_level to $inp_level.</p>";
						$doUnassign		= TRUE;
						$changeLevel	= TRUE;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;Level changed.<br />";
						}	
					}
				}
				if ($changeLevel) {
					$student_action_log .= " / $actionDate $jobname $userName Level changed to $inp_level ";
					$updateParams		= array('student_level'=>$inp_level,
												' student_action_log'=>$student_action_log);
					$updateFormat		= array('%s','%s');
					$studentUpdateData	= array('tableName'=>$studentTableName,
													'inp_data'=>$updateParams,
													'inp_format'=>$updateFormat,
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
						handleWPDBError("$jobname MGMT 36",$doDebug);
					} else {
						$lastError			= $wpdb->last_error;
						if ($lastError != '') {
							handleWPDBError("$jobname MGMT 36",$doDebug);
							$content		.= "Fatal program error. System Admin has been notified";
							if (!$doDebug) {
								return $content;
							}
						}

						$content		.= "Student level updated to $inp_level<br />";
					}
				}
				if ($doUnassign) {
					if ($student_assigned_advisor != '') {
						$inp_data			= array('inp_student'=>$student_call_sign,
													'inp_semester'=>$student_semester,
													'inp_assigned_advisor'=>$student_assigned_advisor,
													'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
													'inp_remove_status'=>'',
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
							$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
						} else {
							$content		.= "Student removed from class and unassigned<br />
												<p>Click 'Push' to push the information to the advisor:<br />
												<form method='post' action='$pushURL' 
												name='selection_form_41' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='2'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<input type='hidden' name='inp_semester' value='$student_semester'>
												<input type='hidden' name='request_info' value='$student_assigned_advisor'>
												<input type='hidden' name='request_type' value= 'Full'>
												<input type='submit' class='formInputButton' value='Push'></form></p><br /><br />";
						}
					}
				}
			} else {
				$content	.= "No student record found in $studentTableName table for $inp_student_callsign in the $proximateSemester semester.";
			}
		}



//////		Pass 40		Add a student to an advisor's class

	} elseif ("40" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 40 with inp_advisor_callsign: $inp_advisor_callsign, 
					inp_student_callsign: $inp_student_callsign, and level: $inp_level<br />";
		}
		$jobname						= "Student Management Add Student to Advisor Class";
		$beginnerChecked				= "";
		$fundamentalChecked				= "";
		$intermediateChecked			= "";
		$advancedChecked				= "";
		if ($inp_level != '') {
			if ($inp_level == 'Beginner') {
				$beginnerChecked		= "checked";
			} elseif ($inp_level == 'Fundamental') {
				$fundamentalChecked		= "checked";
			} elseif ($inp_level == 'Intermediate') {
				$intermediateChecked	= "checked";
			} elseif ($inp_level == 'Advanced') {
				$advancedChecked		= "checked";
			}
		}
		$content .= "<h3>$jobname</h3>
					<p>Enter the student call sign to be added, the advisor's call sign, and select the class 
					level that the advisor is teaching. The advisor must have a class in the indicated level. 
					The student must be unassigned or no action will be taken.</p>
					<p><b>Note:</b> If this action is taken before students are assigned to advisor classes, 
					that process will override this action. In that case the better option is to pre-assign 
					the student to the advisor's class.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
					<input type='hidden' name='strpass' value='41'>
					<table style='border-collapse:collapse;'>
					<tr><td style='width:150px;'>Student Call Sign:</td>
						<td><input class='formInputText' type='text' size= '30' maxlength='30' name='inp_student_callsign' value='$inp_student_callsign' autofocus></td></tr>
					<tr><td style='width:150px;'>Advisor Call Sign:</td>
						<td><input class='formInputText' type='text' size = '30' maxlength='30' name='inp_advisor_callsign' value='$inp_advisor_callsign'></td></tr>
					<tr><td style='width:150px;'>Advisor's Teaching Level:</td>
						<td><input type='radio' class='formInputButton' name='level' value='Beginner' $beginnerChecked>Beginner<br />
							<input type='radio' class='formInputButton' name='level' value='Fundamental' $fundamentalChecked>Fundamental<br />
							<input type='radio' class='formInputButton' name='level' value='Intermediate' $intermediateChecked>Intermediate<br />
							<input type='radio' class='formInputButton' name='level' value='Advanced' $advancedChecked>Advanced</td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
					</form>";

	} elseif ("41" == $strPass) {				// get the class 
// $doDebug = TRUE;
		if ($doDebug) {
			echo "<br />at pass 41 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />
					level of $level<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}
		$jobname						= "Student Management Add Student to Advisor Class";
		if ($haveStudent) {
			$gotError				= FALSE;
			if ($student_assigned_advisor != '') {
				if ($doDebug) {
					echo "Student has an assigned advisor already<br />";
				}
				$content			.= "<p>Student $inp_student_callsign already has an assigned advisor of $student_assigned_advisor. 
										Please unassign this advisor before making a new assignment.<p>";
				$gotError			= TRUE;
			}
			if ($student_email_number == 4 && $student_response != 'Y') {
				if ($doDebug) {
					echo "Student has been dropped<br />";
				}
				$content			.= "<p>Student $inp_student_callsign failed to respond to verification requests and has been dropped. Pre-assignment refused.</p>";
				$gotError			= TRUE;
			}
			if ($student_response == 'R') {
				if ($doDebug) {
					echo "Student has refused the verification<br />";
				}
				$content			.= "<p>Student $inp_student_callsign responded 'refused' to the verification request. Assignement refused.</p>";
				$gotError			= TRUE;
			}
			if (str_contains($student_excluded_advisor,$inp_advisor_callsign)) {
//			$myInt					= strpos($student_excluded_advisor,$inp_advisor_callsign);
//			if ($myInt !== FALSE) {
				if ($doDebug) {
					echo "$inp_advisor_callsign is an excluded advisor<br />";
				}
				$content			.= "<p>$inp_advisor_callsign is student's excluded advisor. Assignement refused.</p>";
				$gotError			= TRUE;
			}
			if (!$gotError) {
				// see if there is an advisor in the next semester. If so, get the classes at that level for the advisor
				$optionList				= '';
				if ($haveAdvisor && $student_semester == $advisor_semester) {					
					// get the classes
					$sql				= "select * from $advisorClassTableName 
											where advisorclass_call_sign = '$inp_advisor_callsign' 
											and advisorclass_semester = '$student_semester' 
											and advisorclass_level = '$student_level' 
											order by advisorclass_sequence";
					$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$content		.= "<p>Getting the advisorClass record failed<br />";
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
											

								$optionList	.= "<input type='radio' class='formInputButton' name='inp_advisorClass' value='$advisorClass_sequence'> $advisorClass_call_sign $advisorClass_level Class nbr $advisorClass_sequence at $advisorClass_class_schedule_times on $advisorClass_class_schedule_days<br />";
								if ($doDebug) {
									echo "making class $advisorClass_sequence available<br />";
								}
							}
							if ($optionList != '') {
								$content	.= "<h3>Assign Student to an Advisor</h3>
												<p>Select the class to which the student $inp_student_callsign is to  be assigned and click 'Next'</p>
												<p><form method='post' action='$theURL' 
												name='selection_form' ENCTYPE='multipart/form-data''>
												<input type='hidden' name='strpass' value='42'>
												<input type='hidden' name='inp_mode' value='$inp_mode'>
												<input type='hidden' name='inp_verbose' value='$inp_verbose'>
												<input type='hidden' name='inp_student_callsign' value='$inp_student_callsign'>
												<input type='hidden' name='inp_advisor_callsign' value='$inp_advisor_callsign'>
												<input type='hidden' name='inp_level' value='$student_level'>
												<input type='hidden' name='studentid' value='$student_ID'>
												<input type='hidden' name='inp_semester' value='$student_semester'>
												<table style='border-collapse:collapse;'>
												<tr><th colspan='2'>The Advisor $inp_advisor_callsign's Class</th></tr>
												<tr><td style='width:150px;'>Advisor Class(es)</td><td>
												$optionList
												</td></tr>
												<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
												</form></p>";
							} else {
								$content	.= "<h3>$jobname</h3>
												<p>The requested advisor $inp_advisor_callsign does not have a class at student $inp_student_callsign 
												$student_level level. No action taken.</p>";
							}
						} else {
							$content	.= "<h3>$jobname</h3>
											<p>The requested advisor $inp_advisor_callsign does not have a class at student $inp_student_callsign 
											$student_level level. No action taken.</p>";
						}
					}
				} else {
					$content			.= "<h3>$jobname</h3>
											<p>No matching $inp_advisor_callsign record found</p>";
				}
			}
		} else {		// no student found
			if ($doDebug) {
				echo "No student found with call sign $inp_student_callsign in the $studentTableName table<br />";
			}
			$content		.= "Student $inp_student_callsign not found in the $studentTableName table.";
		}


	} elseif ("42" == $strPass) {				// do the actual assignment
		if ($doDebug) {
			echo "<br />at pass 42 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />
					studentid of $inp_studentid<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}
// $testMode	= TRUE;
// $doDebug	= TRUE;
		$jobname						= "Student Management Add Student to Advisor Class";
		if ($doDebug) {
			echo "inp_student_callsign: $inp_student_callsign<br />
			      inp_advisor_callsign: $inp_advisor_callsign<br />
			      inp_advisorClass: $inp_advisorClass<br />
			      inp_semester: $inp_semester<br />
			      inp_level: $inp_level<br />";
		}
		$content .= "<h3>$jobname</h3>";

		if ($haveStudent) {
			if ($doDebug) {
				echo "Processing $student_call_sign:<br />
					  &nbsp&nbsp;&nbsp;with a level of $student_level<br />
					  &nbsp&nbsp;&nbsp;and a status of $student_status<br />";
			}
			$student_action_log	= "$student_action_log / $actionDate $jobname $userName Student assigned to $inp_advisor_callsign";
			$inp_data			= array('inp_student'=>$student_call_sign,
										'inp_semester'=>$inp_semester,
										'inp_assigned_advisor'=>$inp_advisor_callsign,
										'inp_assigned_advisor_class'=>$inp_advisorClass,
										'inp_remove_status'=>'',
										'inp_arbitrarily_assigned'=>'',
										'inp_method'=>'add',
										'jobname'=>$jobname,
										'userName'=>$userName,
										'testMode'=>$testMode,
										'doDebug'=>$doDebug);
					
			$addResult			= add_remove_student($inp_data);
			if ($addResult[0] === FALSE) {
				$thisReason		= $addResult[1];
				if ($doDebug) {
					echo "attempting to add $student_call_sign to $inp_advisor_callsign class failed:<br />$thisReason<br />";
				}
				sendErrorEmail("$jobname Attempting to add $student_call_sign to $inp_advisor_callsign class failed:<br />$thisReason");
				$content		.= "Attempting to add $student_call_sign to $inp_advisor_callsign class failed:<br />$thisReason<br />";
			} else {
				$content		.= "Student added to $inp_advisor_callsign class $inp_advisorClass<br />
									<p>Click 'Push' to push the information to the advisor:<br />
									<form method='post' action='$pushURL' 
									name='selection_form_41' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_semester' value='$student_semester'>
									<input type='hidden' name='request_info' value='$inp_advisor_callsign'>
									<input type='hidden' name='request_type' value= 'Full'>
									<input type='submit' class='formInputButton' value='Push'></form></p><br /><br />";

				if ($doDebug) {
					echo "&nbsp;&nbsp;&nbsp;Student assignment made:<br />";
				}
			}
				
		} else {					
			if ($doDebug) {
				echo "Student call sign of $inp_student_callsign not found in the $studentTableName table. This process was aborted.<br />";
			}
			$content	.= "Student call sign of $inp_student_callsign not found in the $studentTableName table. This request was aborted.<br />";
		}



/////		Pass 50		unassign a  student

	} elseif ("50" == $strPass) {
		$jobname		= "Student Management Unassign a Student";
		$content .= "<h3>$jobname</h3>
					<p>Enter the student call sign to be unassigned. This will result in the student being 
					removed from a class, if assigned, student_status set to blank, assigned_advisor set 
					to blank, and the class number removed.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
						<input type='hidden' name='strpass' value='51'>
					<table style='border-collapse:collapse;'>
					<tr><td style='width:150px;'>Student Call Sign:</td><td>
						<input class='formInputText' type='text' size= '30' maxlength='30' name='inp_student_callsign' autofocus></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
					</form>";



	} elseif ("51" == $strPass) {				// do the actual unassignment
// $testMode	= TRUE;
// $doDebug	= TRUE;
		if ($doDebug) {
			echo "<br />at pass 51 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}

		$jobname		= "Student Management Unassign a Student";
		$isSuccessful	= TRUE;
		$content .= "<h3>$jobname $inp_student_callsign</h3>";

		// get the student info and process
		if ($haveStudent) {
			if ($doDebug) {
				echo "<br />Processing student $student_call_sign:<br />
				&nbsp;&nbsp;&nbsp;Student Status: $student_status<br />
				&nbsp;&nbsp;&nbsp;Level: $student_level<br />
				&nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor<br />
				&nbsp;&nbsp;&nbsp;Assigned Advisor's Class: $student_assigned_advisor_class<br />";
			}
			
			if ($student_assigned_advisor != '') {
				$inp_data			= array('inp_student'=>$student_call_sign,
											'inp_semester'=>$student_semester,
											'inp_assigned_advisor'=>$student_assigned_advisor,
											'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
											'inp_remove_status'=>'',
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
					$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
				} else {
					$content		.= "<p>Student $student_call_sign has been unassigned from $student_assigned_advisor class</p>
										<p>Click 'Push' to push the information to the advisor:<br />
										<form method='post' action='$pushURL' 
										name='selection_form_51' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='2'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_semester' value='$student_semester'>
										<input type='hidden' name='request_info' value='$student_assigned_advisor'>
										<input type='hidden' name='request_type' value= 'Full'>
										<input type='submit' class='formInputButton' value='Push'></form></p><br /><br />";
				}
			} else {
				$content			.= "<p>Student is not assigned to an advisor</p>";
			} 
		} else {
			$content 	.= "<p>No student with the call sign of $inp_student_callsign found in the $studentTableName table. No action taken.</p>";
		}


//////		Pass 55		Re-assign a student to a different advisor

	} elseif ("55" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 55<br />";
		}
		$jobname		= "Student Management Reassign Student to Another Advisor";
		$content	.= "<h3>$jobname</h3>
						<p>Enter the student call sign that is to be re-assigned (must be 
						registered in the upcoming semester) and then 
						the advisor call sign to whom the student is to be assigned. If there are any add'l 
						comments to be added to the action_log, enter those as well. If the student is registered 
						and the advisor is registered, a list of the classes the advisor is teaching will be displayed. 
						Select the appropriate class for the student. After that the reassignment will be made.</p>
						<p><form method='post' action='$theURL' 
						name='selection_form' ENCTYPE='multipart/form-data''>
						<input type='hidden' name='strpass' value='56'>
						<table style='border-collapse:collapse;'>
						<tr><td style='width:150px;'>Student Call Sign</td>
							<td><input class='formInputText' type='text' name='inp_student_callsign' size='10' maxlenth='10' autofocus /></td></tr>
						<tr><td style='vertical-align:top;'>Advisor Call Sign<br /><i>This is the advisor the student will be reassigned to</i></td>
							<td><input class='formInputText' type='text' name='inp_advisor_callsign' size='10' maxlenth='10' /></td></tr>
						<tr><td style='vertical-align:top;'>Additional Comments</td>
							<td><textarea class='formInputText' name='inp_additional' rows='5' cols='50'></textarea></td></tr>
						$testModeOption
						<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
						</form></p>";

	} elseif ("56" == $strPass) {
// $doDebug = TRUE;
		if ($doDebug) {
			echo "<br />at pass 56 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}

		$jobname			= "Student Management Reassign Student to Another Advisor";
		$content			.= "<h3>$jobname</h3>";
		
		if ($haveStudent && $student_semester == $proximateSemester) {
			$gotError				= FALSE;
			if ($student_response == 'R') {
				if ($doDebug) {
					echo "Student has refused the verification<br />";
				}
				$content			.= "<p>Student $inp_student_callsign responded 'refused' to the verification request.</p>";
				$gotError			= TRUE;
			}
			if ($student_assigned_advisor == '') {
				if ($doDebug) {
					echo "Student has no assigned advisor<br />";
				}
				$content			.= "<p>Student $inp_student_callsign has no assigned advisor.</p>";
			}
			if ($student_status == 'C' || $student_status == 'R' || $student_status == 'V') {
				if ($doDebug) {
					echo "Student status is C, R, or V<br />";
				}
				$content			.= "<p>Student $inp_student_callsign has a status of $student_status (replaced).</p>";
				$gotError			= TRUE;
			}
			if (strPos($student_excluded_advisor,$inp_advisor_callsign) !== FALSE) {
				if ($doDebug) {
					echo "$inp_advisor_callsign is an excluded advisor<br />";
				}
				$content			.= "<p>$inp_advisor_callsign is an excluded advisor.</p>";
				$gotError			= TRUE;
			} 
			if ($gotError) {
				$content			.= "<p>If the student should not be reassigned, close this window without 
										taking any further action.<br />";
			} else {
				if ($haveAdvisor && $advisor_semester == $proximateSemester) {
					if ($doDebug) {
						echo "looking for matching class records<br />";
					}
					$optionList				= '';							
					$sql					= "select* from $advisorClassTableName 
												where advisorclass_semester='$proximateSemester' 
													and advisorclass_call_sign='$inp_advisor_callsign' 
													and advisorclass_level='$student_level'";
					$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						handleWPDBError("$jobname MGMT 56",$doDebug);
					} else {
						$numACRows			= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and retrieved $numACRows rows<br />";
						}
						if ($numACRows > 0) {
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

	
								$optionList	.= "<input type='radio' class='formInputButton' name='inp_advisorClass' value='$advisorClass_sequence'> Class nbr $advisorClass_sequence $advisorClass_level at $advisorClass_class_schedule_times on $advisorClass_class_schedule_days<br />";
							}
							$content	.= "<p>Select the class to which the student $inp_student_callsign is to  be assigned and click 'Next'</p>
											<p><form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data''>
											<input type='hidden' name='strpass' value='57'>
											<input type='hidden' name='theSemester' value='$proximateSemester'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<input type='hidden' name='inp_student_callsign' value='$inp_student_callsign'>
											<input type='hidden' name='inp_advisor_callsign' value='$inp_advisor_callsign'>
											<input type='hidden' name='inp_prev_advisor' value='$student_assigned_advisor'>
											<input type='hidden' name='inp_prev_advisor_class' value='$student_assigned_advisor_class'>
											<input type='hidden' name='studentid' value='$student_ID'>
											<input type='hidden' name='inp_additional' value='$inp_additional'>
											<table style='border-collapse:collapse;'>
											<tr><th colspan='2'>Re-assign Student to Advisor $inp_advisor_callsign's Class</th></tr>
											<tr><td style='width:150px;'>Advisor Class(es)</td><td>
											$optionList
											</td></tr>
											<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Next' /></td></tr></table>
											</form></p>";
						} else {
							if ($doDebug) {
								echo "No advisorClass records found with the call sign $inp_advisor_callsign at level $student_level in the $proximateSemester semester<br />";
							}
							$content	.= "Advisor $inp_advisor_callsign is not signed up for a $student_level level class in the $proximateSemester semester.";
						}
					}
				} else {
					if ($doDebug) {
						echo "No advisor record found with the call sign $inp_advisor_callsign at level $student_level in the $proximateSemester semester<br />";
					}
					$content	.= "Advisor $inp_advisor_callsign is not signed up for a $student_level level class in the $proximateSemester semester.";
				}
			}
		} else {		// no student found
			if ($doDebug) {
				echo "No student found with call sign $inp_student_callsign in the $proximateSemester semester<br />";
			}
			$content		.= "Student $inp_student_callsign is not registered in the $proximateSemester semester.";
		}

	
	} elseif ("57" == $strPass) {
// do the actual re-assignment

// $doDebug	= TRUE;
		if ($doDebug) {
			echo "<br />at pass 57 with inp_student_callsign of $inp_student_callsign<br />
					inp_advisor_callsign of $inp_advisor_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($haveAdvisor) {
				echo "haveAdvisor is TRUE<br />";
			} else {
				echo "haveAdvisor is FALSE<br />";
			}
		}

		$jobname			= "Student Management Reassign Student to Another Advisor";
		$content			.= "<h3>$jobname</h3>";

		if ($doDebug) {
			echo "inp_student_callsign: $inp_student_callsign<br />
				  inp_studentid: $inp_studentid<br />
				  inp_advisor_callsign: $inp_advisor_callsign<br />
				  inp_advisorClass: $inp_advisorClass<br />
				  inp_prev_advisor: $inp_prev_advisor<br />
				  inp_prev_advisor_class: $inp_prev_advisor_class<br />";
		}


		//	get the student record in the next semester along with the new advisor
		// 	if either doesn't exist, don't do anything
		if ($haveStudent && $student_semester == $proximateSemester) {
			if ($haveAdvisor && $advisor_semester == $proximateSemester) {
				if ($doDebug) {
					echo "have everything needed to remove the student and reassign<br />";
				}			
				// if there was a previous advisor, remove the student from that class
				if ($inp_prev_advisor != '') {
					if ($doDebug) {
						echo "removing student from prev advisor: $inp_prev_advisor<br />";
					}
					$inp_data			= array('inp_student'=>$inp_student_callsign,
												'inp_semester'=>$proximateSemester,
												'inp_assigned_advisor'=>$inp_prev_advisor,
												'inp_assigned_advisor_class'=>$inp_prev_advisor_class,
												'inp_remove_status'=>'',
												'inp_arbitrarily_assigned'=>'',
												'inp_method'=>'remove',
												'jobname'=>$jobname,
												'userName'=>$userName,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
							
					$addResult			= add_remove_student($inp_data);
					if ($addResult[0] === FALSE) {
						handleWPDBError("$jobname MGMT 57",$doDebug);
					} else {
						$content		.= "Student $inp_student_callsign successfully removed from $inp_prev_advisor class $inp_prev_advisor_class<br />";
				
						// add the student to the advisor's class
						$inp_data			= array('inp_student'=>$inp_student_callsign,
													'inp_semester'=>$theSemester,
													'inp_assigned_advisor'=>$inp_advisor_callsign,
													'inp_assigned_advisor_class'=>$inp_advisorClass,
													'inp_remove_status'=>'',
													'inp_arbitrarily_assigned'=>'',
													'inp_method'=>'add',
													'jobname'=>$jobname,
													'userName'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
									
						$addResult			= add_remove_student($inp_data);
						if ($addResult[0] === FALSE) {
							$thisReason		= $addResult[1];
							if ($doDebug) {
								echo "attempting to add $inp_student_callsign to $inp_advisor_callsign class $inp_advisorClass failed:<br />$thisReason<br />";
							}
							sendErrorEmail("$jobname Attempting to add $inp_student_callsign to $inp_advisor_callsign class $inp_advisorClass failed:<br />$thisReason");
							$content		.= "Attempting to add $inp_student_callsign to $inp_advisor_callsign class $inp_advisorClass failed:<br />$thisReason<br />";
						} else {
							if ($doDebug) {
								echo "deciding how many pushes are needed<br />inp_prev_advisor: $inp_prev_advisor<br />inp_advisor_callsign: $inp_advisor_callsign<br />";
							}
							if ($inp_prev_advisor == '') {
								if ($doDebug) {
									echo "No previous advisor. Pushing new advisor<br />";
								}
								$content		.= "<p>Student $inp_student_callsign assigned to advisor $inp_advisor_callsign.</p>
													<p>Click 'Push' to push the information to the advisor:<br />
													<form method='post' action='$pushURL' 
													name='selection_form_41' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='2'>
													<input type='hidden' name='inp_semester' value='$proximateSemester'>
													<input type='hidden' name='request_info' value='$inp_advisor_callsign'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='request_type' value= 'Full'>
													<input type='hidden' name='inp_mode' value='$inp_mode'>
													<input type='submit' class='formInputButton' value='Push'></form>";
							} else {
								if ($doDebug) {
									echo "need to push previous and new advisor classes<br />"; 
								}
								$content		.= "<p>Student $inp_student_callsign re-assigned from advisor $inp_prev_advisor class $inp_prev_advisor_class 
													to advisor $inp_advisor_callsign class $inp_advisorClass.</p>
													<p>Both advisors need to be informed of the class change. Click 'Push $inp_prev_advisor' to push the 
													information to the previous advisor. The 'push' will open in a new tab. 
													When that is done, return here and click 'Push $inp_advisor_callsign'.</p>
													<table style='width:auto;'>
													<tr><td><form method='post' action='$pushURL' target='_blank' 
															name='selection_form_41' ENCTYPE='multipart/form-data'> 
															<input type='hidden' name='strpass' value='2'>
															<input type='hidden' name='inp_semester' value='$proximateSemester'>
															<input type='hidden' name='request_info' value='$inp_prev_advisor'>
															<input type='hidden' name='inp_verbose' value='$inp_verbose'>
															<input type='hidden' name='request_type' value= 'Full'>
															<input type='hidden' name='inp_mode' value='$inp_mode'>
															<input type='submit' class='formInputButton' value='Push $inp_prev_advisor'></form></td>
														<td style='width:50px;'></td>
														<td><form method='post' action='$pushURL' target='_blank' 
															name='selection_form_41' ENCTYPE='multipart/form-data'> 
															<input type='hidden' name='strpass' value='2'>
															<input type='hidden' name='inp_semester' value='$proximateSemester'>
															<input type='hidden' name='request_info' value='$inp_advisor_callsign'>
															<input type='hidden' name='inp_verbose' value='$inp_verbose'>
															<input type='hidden' name='request_type' value= 'Full'>
															<input type='hidden' name='inp_mode' value='$inp_mode'>
															<input type='submit' class='formInputButton' value='Push $inp_advisor_callsign'></form></td></tr>
													</table>";
							}
						}
					}
				} else {
					if ($doDebug) {
						echo "no previous advisor. No action taken<br />";
					}
					$content			.= "<p>Student was not assigned to a class. No action taken</p>";
				}
			} else {
				if ($doDebug) {
					echo "no advisor record for the new advisor. No action taken<br />";
				}
				$content				.= "<p>No advisor record found for the new advisor. No action taken</p>";
			}
		} else {
			if ($doDebug) {
				echo "No student record found<br />";
			}
			$content					.= "<p>No student record found for $inp_student_callsign. No action taken</p>";
		}
		


/////		Pass 60		remove a  student

	} elseif ("60" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 60<br />";
		}
	
		$jobname			= "Student Management Unassign and Remove a Student";
		$content .= "<h3>$jobname</h3>
					<p>Enter the student call sign to be removed. If the student is assigned to an advisor, 
					this will result in the student status being set to C, the student  
					removed from a class, assigned_advisor set to blank, and the class number set to blank.</p>
					<p>If the student is not assigned, then the student response will be set to R (refused).</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
					<input type='hidden' name='strpass' value='61'>
					<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Student Call Sign:</td><td>
					<input class='formInputText' type='text' size= '30' maxlength='30' name='inp_student_callsign' autofocus></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
					</form>";



	} elseif ("61" == $strPass) {				// do the actual unassignment
// $testMode	= TRUE;
// $doDebug	= TRUE;

		if ($doDebug) {
			echo "<br />at pass 61 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
		}

		$jobname			= "Student Management Unassign and Remove a Student";
		$content 			.= "<h3>$jobname</h3>";
		if ($haveStudent && $student_semester == $proximateSemester) {
			if ($doDebug) {
				echo "<br />Processing student $student_call_sign:<br />
				&nbsp;&nbsp;&nbsp;Student Status: $student_status<br />
				&nbsp;&nbsp;&nbsp;Level: $student_level<br />
				&nbsp;&nbsp;&nbsp;Semester: $student_semester<br />
				&nbsp;&nbsp;&nbsp;Pre-assigned Advisor: $student_pre_assigned_advisor<br />
				&nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor<br />
				&nbsp;&nbsp;&nbsp;Assigned Advisor's Class: $student_assigned_advisor_class<br />";
			}
			if ($student_assigned_advisor != '') {
				// remove the student
				$inp_data			= array('inp_student'=>$student_call_sign,
											'inp_semester'=>$student_semester,
											'inp_assigned_advisor'=>$student_assigned_advisor,
											'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
											'inp_remove_status'=>'C',
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
						echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
					}
					sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
					$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
				} else {
					if ($doDebug) {
						echo "<br />&nbsp;&nbsp;&nbsp;Student has been removed:<br />";
						foreach($updateArray as $myKey=>$myValue) {
							echo "$myKey = $myValue<br />";
						}
					}
					$content	.= "<p>Student $student_call_sign has been removed and unassigned</p>
									<p>Click 'Push' to push the information to the advisor:<br />
									<form method='post' action='$pushURL' 
									name='selection_form_41' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='inp_semester' value='$student_semester'>
									<input type='hidden' name='request_info' value='$student_assigned_advisor'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='request_type' value= 'Full'>
									<input type='submit' class='formInputButton' value='Push'></form>";
				}
			} else {					// student not assigned ... set the response to R
				$updateParams			= array('student_response|R|s');
				$updateFormat			= array('');
				$studentUpdateData		= array('tableName'=>$studentTableName,
												'inp_method'=>'update',
												'inp_data'=>$updateParams,
												'inp_format'=>$updateFormat,
												'jobname'=>$jobname,
												'inp_id'=>$student_ID,
												'inp_callsign'=>$student_call_sign,
												'inp_semester'=>$student_semester,
												'inp_who'=>$userName,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
				$updateResult	= updateStudent($studentUpdateData);
				if ($updateResult[0] === FALSE) {
					$myError	= $wpdb->last_error;
					$mySql		= $wpdb->last_query;
					$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
					if ($doDebug) {
						echo $errorMsg;
					}
					sendErrorEmail($errorMsg);
					$content		.= "Unable to update content in $studentTableName<br />";
				} else {
					$content		.= "Student response set to R<br />";
				}
			}
		} else {
			$content 	.= "<p>No student with the call sign of $inp_student_callsign in semester $proximateSemester found in the $studentTableName table. No action taken.</p>";
		}


///////		pass 70		Find Possible Classes for a Student

	} elseif ("70" == $strPass) {
		if ($doDebug) {
			echo "arrived at pass 70<br />";
		}
		$jobname			= "Student Management Find Possible Classes for a Student";
		$content			.= "<h3>$jobname</h3>
								<p>There are two options:
								<dl>
								<dt>1. Search Using Student's Choices</dt>
								<dd>The option will search through the advisor classes to find any classes that 
								may meet the student's first, second  and thirdclass choices</dd>
								<dt>2. Search Using A New Time and Class Days</dt>
								<dd>Enter the approximate time (the system will search two hours before and two hours after) 
								and select the class days. The function will search through the advisor classes to find 
								any classes that may meet the search criteria</dd>
								</dl></p>
								<p>If the student is on hold or if the student is already assigned to an advisor, 
								it will be necessary to use the function 'Assign a Student to an Advisor Regardless of Status or Semester' 
								to make the assignment</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='71'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign:</td>
									<td><input class='formInputText' type='text' size= '30' maxlength='30' value='$inp_student_callsign' name='inp_student_callsign'></td></tr>
								<tr><td>Option 1 Search Using Student's Choices</td>
									<td><input type='radio' class='formInputButton' name='inp_choice' value='option1' checked='checked'> Option 1</td></tr>
								<tr><td style='vertical-align:top;'>Option 2 Search Using a New Time and Class Days<br />Specify in Student Local Time</td>
									<td><input type='radio' class='formInputButton' name='inp_choice' value='option2'> Option 2<br /><br />
										<input type='text' class='formInputText' name='search_time' size='5 maxlenth='5'><br />
										<input type='radio' class='formInputButton' name='search_days' value='Saturday,Wednesday'> Saturday,Wednesday<br />
										<input type='radio' class='formInputButton' name='search_days' value='Sunday,Wednesday'> Sunday,Wednesday<br />
										<input type='radio' class='formInputButton' name='search_days' value='Monday,Thursday' checked='checked'> Monday,Thursday<br />
										<input type='radio' class='formInputButton' name='search_days' value='Tuesday,Friday'> Tuesday,Friday</td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";




	} elseif ("71" == $strPass) {
// $doDebug = TRUE;
		if ($doDebug) {
			echo "<br />at pass 71 with inp_student_callsign of $inp_student_callsign<br />";
			if ($haveStudent) {
				echo "haveStudent is TRUE<br />";
			} else {
				echo "haveStudent is FALSE<br />";
			}
			if ($inp_choice == 'choice2') {
				echo "requested time: $search_time and requested days: $search_days<br />";
			}
		}
		$jobname				= "Student Management Find Possible Classes for a Student";
		$classAvailableArray	= array();
		
		$content				.= "<h3>$jobname $inp_student_callsign</h3>";
		$doActions				= TRUE;
		if ($haveStudent && $student_semester == $proximateSemester) {						
			if ($doDebug) {
				echo "<br />Processing $student_call_sign<br />
					  &nbsp;&nbsp;&nbsp;&nbsp;response: $student_response<br />
					  &nbsp;&nbsp;&nbsp;&nbsp;status: $student_status<br />";
			}
			$statusComment							= '';
			$doProceed								= TRUE;
			/// check to see if the student meetinp the criteria
			if ($student_response != 'Y') {
				if ($student_response == '') {
					$myStr		= 'blank';
				} else {
					$myStr		= $student_response;
				}
				$content							.= "<b>Student is not verified (Response is $myStr)</b><br />";
				$doActions							= FALSE;
			}						
				/// inform if on hold
			if ($student_intervention_required == 'H') {
				$content							.= "Student is on hold<br />";
//				$doActions							= FALSE;
			}
			if ($student_status == 'Y') {
				$content							.= "Student already assigned to $student_assigned_advisor - $student_assigned_advisor_class. Student status: $student_status<br />";
				$doActions							= FALSE;
			} elseif ($student_status != '') {
				$content							.= "Student has a status of $student_status and is currently not available to be assigned<br />";
				$doActions							= FALSE;
			}
			if ($doActions) {			
				if ($inp_choice == 'option1') {
					$testFirst					= TRUE;
					$searchFirstBegin			= "";
					$searchFirstEnd				= "";
					$testSecond					= TRUE;
					$searchSecondBegin			= "";
					$searchSecondEnd				= "";
					$testThird					= TRUE;
					$searchThirdBegin			= "";
					$searchThirdEnd				= "";
					////// get the student's search values
					if ($student_first_class_choice_utc == '' || $student_first_class_choice_utc == 'None') {
						$testFirst				= FALSE;
						$searchFirstTime		= '';
						$searchFirstDays		= '';
						if ($doDebug) {
							echo "No first choice. Not searching first choice<br />";
						}
					} else {
						$myArray				= explode(" ",$student_first_class_choice_utc);
						$myTime					= substr($myArray[0],0,2);
						$myInt					= intval($myTime) * 100;
						$searchValues			= $searchRange[$myInt];
						$thisArray				= explode("|",$searchValues);
						$searchFirstBegin		= intval($thisArray[0]);
						$searchFirstEnd			= intval($thisArray[1]);
						$searchFirstDays		= $myArray[1];
						if ($doDebug) {
							echo "searchFirst: $searchFirstBegin - $searchFirstEnd $searchFirstDays<br />";
						}
					}
					if ($student_second_class_choice_utc == '' || $student_second_class_choice_utc == 'None') {
						$searchSecondTime		= "";
						$searchSecondDays		= "";
						$testSecond				= FALSE;
						if ($doDebug) {
							echo "No second choice. Not searching second choice<br />";
						}
					} else {
						$myArray				= explode(" ",$student_second_class_choice_utc);
						$myTime					= substr($myArray[0],0,2);
						$myInt					= intval($myTime) * 100;
						$searchValues			= $searchRange[$myInt];
						$thisArray				= explode("|",$searchValues);
						$searchSecondBegin		= intval($thisArray[0]);
						$searchSecondEnd		= intval($thisArray[1]);
						$searchSecondDays		= $myArray[1];
						if ($doDebug) {
							echo "searchSecond: $searchSecondBegin - $searchSecondEnd $searchSecondDays<br />";
						}
					}
					if ($student_third_class_choice_utc == '' || $student_third_class_choice_utc == 'None') {
						$searchThirdTime		= "";
						$searchThirdDays		= "";
						$testThird				= FALSE;
						if ($doDebug) {
							echo "No third choice. Not searching third choice<br />";
						}
					} else {
						$myArray				= explode(" ",$student_third_class_choice_utc);
						$myTime					= substr($myArray[0],0,2);
						$myInt					= intval($myTime) * 100;
						$searchValues			= $searchRange[$myInt];
						$thisArray				= explode("|",$searchValues);
						$searchThirdBegin		= intval($thisArray[0]);
						$searchThirdEnd			= intval($thisArray[1]);
						$searchThirdDays		= $myArray[1];
						if ($doDebug) {
							echo "searchThird: $searchThirdBegin - $searchThirdEnd $searchThirdDays<br />";
						}
					}
				} else {					//// doing a option2 search rather than the student choices
					/// convert input times and days to UTC
					$result						= utcConvert('toutc',$student_timezone_offset,$search_time,$search_days);
					if ($result[0] == 'FAIL') {
						if ($doDebug) {
							echo "utcConvert failed 'toutc',$student_timezone_offset,$search_time,$search_days<br />
								  Error: $result[3]<br />";
						}
						$displayDays			= "<b>ERROR</b>";
						$displayTimes			= '';
					} else {
						$displayTimes			= $result[1];
						$displayDays			= $result[2];
					}
					$myTime					= substr($displayTimes[0],0,2);
					$myInt					= intval($myTime) * 100;
					$searchValues			= $searchRange[$myInt];
					$thisArray				= explode("|",$searchValues);
					$searchFirstBegin		= intval($thisArray[0]);
					$searchFirstEnd			= intval($thisArray[1]);
					$searchFirstDays		= $displayDays;
					if ($doDebug) {
						echo "searchFirst: $searchFirstBegin - $searchFirstEnd $searchFirstDays<br />";
					}
					$testFirst					= TRUE;
					$testSecond					= FALSE;
					$testThird					= FALSE;
				}
				// check to see if should test student choices
				$doTesting			= FALSE;
				if ($testFirst || $testSecond || $testThird) {
					$doTesting		= TRUE;
				} else {
					$content		.= "<p><b>Student has not selected any class choices</b></p>";
				}
				if ($doTesting) {
					//// get all the advisorClass records for the student's level
					$sql					= "select * from $advisorClassTableName 
											   where advisorclass_semester='$proximateSemester' 
												and advisorclass_level='$student_level'";
					$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						handleWPDBError("$jobname MGMT 71",$doDebug);
					} else {
						$numACRows					= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and retrieved $numACRows rows<br />";
						}
						if ($numACRows > 0) {
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
							
								if ($doDebug) {
									echo "<br />Have $advisorClass_level advisorClass for $advisorClass_call_sign held on $advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc<br />
										advisorClass_class_size: $advisorClass_class_size<br />
										class_number_students: $advisorClass_number_students<br />";
								}
								$doProceed					= TRUE;
								//// see if this advisor is excluded
								if (strPos($student_excluded_advisor,$advisorClass_call_sign) !== FALSE) {
									if ($doDebug) {
										echo "This advisor is excluded .... bypassed<br />";
									}
									$doProceed				= FALSE;
								}
								if ($doProceed) {
									////// get the advisor record and see if we can use this advisor
									$sql					= "select * from $advisorTableName 
																left join $userMasterTableName on user_call_sign = advisor_call_sign 
																where advisor_call_sign='$advisorClass_call_sign' 
																and advisor_semester='$proximateSemester'";
									$wpw1_cwa_advisor	= $wpdb->get_results($sql);
									if ($wpw1_cwa_advisor === FALSE) {
										handleWPDBError("$jobname MGMT 71",$doDebug);
										$doProceed		= FALSE;
									} else {
										$numARows			= $wpdb->num_rows;
										if ($doDebug) {
											echo "ran $sql<br />and retrieved $numARows rows<br />";
										}
										if ($numARows > 0) {
											foreach ($wpw1_cwa_advisor as $advisorRow) {
												$advisor_master_ID 					= $advisorRow->user_ID;
												$advisor_master_call_sign			= $advisorRow->user_call_sign;
												$advisor_first_name 				= $advisorRow->user_first_name;
												$advisor_last_name 					= $advisorRow->user_last_name;
												$advisor_email 						= $advisorRow->user_email;
												$advisor_ph_code 					= $advisorRow->user_ph_code;
												$advisor_phone 						= $advisorRow->user_phone;
												$advisor_city 						= $advisorRow->user_city;
												$advisor_state 						= $advisorRow->user_state;
												$advisor_zip_code 					= $advisorRow->user_zip_code;
												$advisor_country_code 				= $advisorRow->user_country_code;
												$advisor_country					= $advisorRow->user_country;
												$advisor_whatsapp 					= $advisorRow->user_whatsapp;
												$advisor_telegram 					= $advisorRow->user_telegram;
												$advisor_signal 					= $advisorRow->user_signal;
												$advisor_messenger 					= $advisorRow->user_messenger;
												$advisor_master_action_log 			= $advisorRow->user_action_log;
												$advisor_timezone_id 				= $advisorRow->user_timezone_id;
												$advisor_languages 					= $advisorRow->user_languages;
												$advisor_survey_score 				= $advisorRow->user_survey_score;
												$advisor_is_admin					= $advisorRow->user_is_admin;
												$advisor_role 						= $advisorRow->user_role;
												$advisor_master_date_created 		= $advisorRow->user_date_created;
												$advisor_master_date_updated 		= $advisorRow->user_date_updated;
							
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
							
		
												
												if ($advisor_verify_response == 'R') {
													if ($doDebug) {
														echo "Advisor has a response of R. Bypassing<br />";
													}
													$doProceed		= FALSE;
												}
												if ($advisor_survey_score == '6' || $advisor_survey_score == 9 || $advisor_survey_score == 13) {
													if ($doDebug) {
														echo "Advisor has a survey score of $advisor_survey_score. Bypassing<br />";
													}
													$doProceed		= FALSE;
												}
												if ($advisor_verify_email_number == '4') {
													if ($doDebug) {
														echo "Advisor was dropped. Bypassing<br />";
													}
													$doProceed		= FALSE;
												}
											}				///// end of advisor while
										} else {
											if ($doDebug) {
												echo "No advisor record found for $advisorClass_advisor_callsign<br />";
											}
											$doProceed				= FALSE;
										}
									}
									if ($doProceed) {
										if ($doDebug) {
											echo "testing advisor $advisor_call_sign<br />";
										}
										$gotAMatch				= FALSE;
										$advisorClass_class_schedule_times_utc		= intval($advisorClass_class_schedule_times_utc);
										//// does the advisor's class meet the student's requirements?
										if ($testFirst) {
											if ($doDebug) {
												echo "checking $advisor_call_sign schedule against student first class choice<br />";
											}
											if ($searchFirstDays == $advisorClass_class_schedule_days_utc) {		/// half way there
												if ($advisorClass_class_schedule_times_utc >= $searchFirstBegin && $advisorClass_class_schedule_times_utc < $searchFirstEnd) {		/// have a match
													$result						= utcConvert('tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc,$doDebug);
													if ($result[0] == 'FAIL') {
														if ($doDebug) {
															echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc<br />
																  Error: $result[3]<br />";
														}
														$displayDays			= "<b>ERROR</b>";
														$displayTimes			= "";
													} else {
														$displayTimes			= $result[1];
														$displayDays			= $result[2];
														$classAvailableArray[]	= "1|$advisorClass_call_sign|$advisor_last_name, $advisor_first_name<br />$advisor_state, $advisor_country|$advisorClass_sequence|$advisorClass_class_schedule_times_utc|$advisorClass_class_schedule_days_utc|First|$advisorClass_class_size|$advisorClass_number_students|$advisorClass_class_schedule_times $advisorClass_class_schedule_days|$advisorClass_timezone_offset|$displayTimes $displayDays|$student_level";
														if ($doDebug) {
															echo "Got a match. Added $advisorClass_call_sign|$advisorClass_sequence|$advisorClass_class_schedule_times_utc|$advisorClass_class_schedule_days_utc|First|$advisorClass_class_size|$advisorClass_number_students|$advisorClass_class_schedule_times $advisorClass_class_schedule_days|$advisorClass_timezone_offset|$displayTimes $displayDays|$student_level<br />";
														}
														$gotAMatch	= TRUE;
													}
												} else {
													if ($doDebug) {
														echo "no match<br /";
													}
												}
											}
										}
										if ($testSecond) {
											if ($doDebug) {
												echo "checking $advisor_call_sign schedule against student second class choice<br />";
											}
											if ($searchSecondDays == $advisorClass_class_schedule_days_utc) {		/// half way there
												if ($advisorClass_class_schedule_times_utc >= $searchSecondBegin && $advisorClass_class_schedule_times_utc < $searchSecondEnd) {		/// have a match
													$result						= utcConvert('tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc,$doDebug);
													if ($result[0] == 'FAIL') {
														if ($doDebug) {
															echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc<br />
																  Error: $result[3]<br />";
														}
														$displayDays			= "<b>ERROR</b>";
														$displayTimes			= "";
													} else {
														$displayTimes			= $result[1];
														$displayDays			= $result[2];
//														$studentCount			= getStudentCount($advisorClass_call_sign,$advisorClass_sequence);
														$classAvailableArray[]			= "2|$advisorClass_call_sign|$advisor_last_name, $advisor_first_name|$advisorClass_sequence|$advisorClass_class_schedule_times_utc|$advisorClass_class_schedule_days_utc|Second|$advisorClass_class_size|$advisorClass_number_students|$advisorClass_class_schedule_times $advisorClass_class_schedule_days|$advisorClass_timezone_offset|$displayTimes $displayDays|$student_level";
														if ($doDebug) {
															echo "Got a match. Added 2|$advisorClass_call_sign|$advisor_last_name, $advisor_first_name<br />$advisor_state, $advisor_country|$advisorClass_sequence|$advisorClass_class_schedule_times_utc|$advisorClass_class_schedule_days_utc|Second|$advisorClass_class_size|$advisorClass_number_students|$advisorClass_class_schedule_times $advisorClass_class_schedule_days|$advisorClass_timezone_offset|$displayTimes $displayDays|$student_level<br />";
														}
														$gotAMatch		= TRUE;
													}
												} else {
													if ($doDebug) {
														echo "no match<br /";
													}
												}
											}
										}
										if ($testThird) {
											if ($doDebug) {
												echo "checking $advisor_call_sign schedule against student third class choice<br />";
											}
											if ($searchThirdDays == $advisorClass_class_schedule_days_utc) {		/// half way there
												if ($advisorClass_class_schedule_times_utc >= $searchThirdBegin && $advisorClass_class_schedule_times_utc < $searchThirdEnd) {		/// have a match
													$result						= utcConvert('tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc,$doDebug);
													if ($result[0] == 'FAIL') {
														if ($doDebug) {
															echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc<br />
																  Error: $result[3]<br />";
														}
														$displayDays			= "<b>ERROR</b>";
														$displayTimes			= "";
													} else {
														$displayTimes			= $result[1];
														$displayDays			= $result[2];
														$classAvailableArray[]			= "3|$advisorClass_call_sign|$advisor_last_name, $advisor_first_name<br />$advisor_state, $advisor_country|$advisorClass_sequence|$advisorClass_class_schedule_times_utc|$advisorClass_class_schedule_days_utc|Third|$advisorClass_class_size|$advisorClass_number_students|$advisorClass_class_schedule_times $advisorClass_class_schedule_days|$advisorClass_timezone_offset|$displayTimes $displayDays|$student_level";
														if ($doDebug) {
															echo "Got a match. Added $advisorClass_call_sign|$advisorClass_sequence|$advisorClass_class_schedule_times_utc|$advisorClass_class_schedule_days_utc|Third|$advisorClass_class_size|$advisorClass_number_students|$advisorClass_class_schedule_times $advisorClass_class_schedule_days|$advisorClass_timezone_offset|$displayTimes $displayDays|$student_level<br />";
														}
														$gotAMatch				= TRUE;
													}
												} else {
													if ($doDebug) {
														echo "no match<br /";
													}
												}
											}
										}
									}
								}
							}			//// end of advisorClass while
							if (count($classAvailableArray) > 0) {
								/// output the data
								sort($classAvailableArray);
								if ($doDebug) {
									echo "<br />class available array:<br /><pre>";
									print_r($classAvailableArray);
									echo "</pre><br />";
									if ($gotAMatch) {
										echo "gotAMatch is TRUE<br />";
									} else {
										echo "gotAMatch is FALSE<br />";
									}
								}
								if ($testFirst) {
									$searchFirstBegin		= str_pad($searchFirstBegin,4,'0',STR_PAD_LEFT);
									$searchFirstEnd			= str_pad($searchFirstEnd,4,'0',STR_PAD_LEFT);
								} else {
									$searchFirstBegin		= '';
									$searchFirstEnd			= '';
								}
								if ($testSecond) {
									$searchSecondBegin		= str_pad($searchSecondBegin,4,'0',STR_PAD_LEFT);
									$searchSecondEnd		= str_pad($searchSecondEnd,4,'0',STR_PAD_LEFT);
								} else {
									$searchSecondBegin		= '';
									$searchSecondEnd		= '';
								}
								if ($testThird) {
									$searchThirdBegin		= str_pad($searchThirdBegin,4,'0',STR_PAD_LEFT);
									$searchThirdEnd			= str_pad($searchThirdEnd,4,'0',STR_PAD_LEFT);
								} else {
									$searchThirdBegin		= '';
									$searchThirdEnd			= '';
								}
								if ($inp_choice == 'option1') {
									$thisType			 	= "Search Using Student's Choices";
									$thisSearch				= "<table style='width:1000px;'>
																<tr><th>Student<br />
																		$student_last_name, $student_first_name</th>
																	<th>Call Sign<br />
																		$student_call_sign</th>
																	<th>Level<br />
																		$student_level</th>
																	<th>Location<br />
																		$student_state, $student_country</th></tr>
																<tr><td>&nbsp;</td>
																	<td><b>Local Time</b></td>
																	<td><b>UTC Time</b></td>
																	<td></td></tr>
																<tr><td style='vertical-align:top;'>First Choice</td>
																	<td style='vertical-align:top;'>$student_first_class_choice</td>
																	<td style='vertical-align:top;'>$student_first_class_choice_utc<br />
																		Search GE $searchFirstBegin LT $searchFirstEnd</td>
																	<td></td></tr>
																<tr><td style='vertical-align:top;'>Second Choice</td>
																	<td style='vertical-align:top;'>$student_second_class_choice</td>
																	<td style='vertical-align:top;'>$student_second_class_choice_utc<br />
																		Search GE $searchSecondBegin LT $searchSecondEnd</td>
																	<td></td></tr>
																<tr><td style='vertical-align:top;'>Third Choice</td>
																	<td style='vertical-align:top;'>$student_third_class_choice</td>
																	<td style='vertical-align:top;'>$student_third_class_choice_utc<br />
																		Search GE $searchThirdBegin LT $searchThirdEnd</td>
																	<td></td></tr>
																<tr><td colspan='4'>Search Using Student Choices</td></tr>
																</table>";
								} else {
									$thisType			 	= "Search Using a New Time and Class Days";
									$thisSearch				= "New Search Criteria: $search_time $search_days Local ($displayTimes $displayDays UTC)<br />";
								}
								$showPreAssign				= TRUE;
								if ($daysToSemester > 0 && $daysToSemester <= 19) {		/// show pre-assign or not??
									$showPreAssign			= FALSE;
									if ($doDebug) {
										echo "showPreAssign set to FALSE as $daysToSemester is gt 0 and le 19<br />";
									}
								}
							
								$content		.= "$thisSearch
													$statusComment</p>
													<table style='width:1000px;'>
													<tr><th>Advisor</th>
														<th>Name</th>
														<th>Class</th>
														<th>Advisor Class Schedule</th>
														<th>Choice Match</th>
														<th>Class Size</th>
														<th>Seats<br />Taken</th>
														<th>Seats<br />Available</th>
														<th>Student Class Time Local</th>
														<th>Pre-Assign</th>
														<th>Assign</th></tr>";
		
/*
		classAvailableArray = 
		0	1, 2, or 3 depending on which class choice matched
		1	advisorClass_call_sign 
		2	advisorClass_advisor_last_name, advisorClass_advisor_first_name
		3	advisorClass_sequence
		4	advisorClass_class_schedule_times_utc
		5	advisorClass_class_schedule_days_utc
		6	Third
		7	advisorClass_class_size
		8	studentCount
		9	advisorClass_class_schedule_times advisorClass_class_schedule_days
		10	advisorClass_timezone
		11  Class time in student's timezone
		12	level
	
	
		Truth table
		doActions 			FALSE	FALSE	FALSE	TRUE	TRUE	TRUE	TRUE	
		classFull			FALSE	TRUE	TRUE	FALSE	FALSE	TRUE	TRUE
		showPreAssign		TRUE	FALSE	TRUE	FALSE	TRUE	FALSE	TRUE
	
		PreAssign											xxx				
		Assign										xxx		xxx		xxx		xxx
		Neither				xxx		xxx		xxx		
			
*/
								if (count($classAvailableArray) > 0) {
									$gotAMatch			= TRUE;
								}
								foreach ($classAvailableArray as $myValue) {
									$myArray			= explode("|",$myValue);
									$thisCallSign		= $myArray[1];
									$thisName			= $myArray[2];
									$thisClass			= $myArray[3];
									$thisClassSkedTime	= $myArray[4];
									$thisClassSkedDays	= $myArray[5];
									$thisMatch			= $myArray[6];
									$thisSize			= $myArray[7];
									$thisCount			= $myArray[8];
									$thisLocal			= $myArray[9];
									$thisTimeZone		= $myArray[10];
									$thisClassTime		= $myArray[11];
									$thisLevel			= $myArray[12];
								
									$thisClassSkedTime	= str_pad($thisClassSkedTime,4,0,STR_PAD_LEFT);
									$classFull			= TRUE;
									if ($thisCount < $thisSize) {
										$classFull		= FALSE;
									}
									if ($doDebug) {
										echo "Logicals: doActions: $doActions; classFull: $classFull; showPreAssign: $showPreAssign<br />";
									}
									$myInt				= $thisSize - $thisCount;
									$thisAvail			= 0;
									if ($myInt < 0) {
										$thisAvail	= 0;
									}
									if ($myInt > 0) {
										$thisAvail		= "<b>$myInt</b>";
									}
									$content			.= "<tr><td style='vertical-align:top;'>$thisCallSign</td>
																<td style='vertical-align:top;'>$thisName</td>
																<td style='vertical-align:top;text-align:center;'>$thisClass</td>
																<td style='vertical-align:top;'>$thisClassSkedTime $thisClassSkedDays UTC<br />$thisLocal Local</td>
																<td style='vertical-align:top;'>$thisMatch</td>
																<td style='vertical-align:top;text-align:center;'>$thisSize</td>
																<td style='vertical-align:top;text-align:center;'>$thisCount</td>
																<td style='vertical-align:top;text-align:center;'>$thisAvail</td>
																<td style='vertical-align:top;'>$thisClassTime</td>";
									if ($doActions && !$classFull && $showPreAssign) {
										$content		.= "	<td style='vertical-align:top;'><a href='$theURL?strpass=2&inp_advisor_callsign=$thisCallSign&inp_student_callsign=$inp_student_callsign' target='_blank'>Pre-Assign</a></td>
																<td style='vertical-align:top;'><a href='$theURL?strpass=40&inp_advisor_callsign=$thisCallSign&inp_student_callsign=$inp_student_callsign&level=$thisLevel' target='_blank'>Assign</a></td>
															</tr>";
									} elseif ($doActions) {
										$content		.= "	<td style='vertical-align:top;'></td>
																<td style='vertical-align:top;'><a href='$theURL?strpass=40&inp_advisor_callsign=$thisCallSign&inp_student_callsign=$inp_student_callsign&level=$thisLevel' target='_blank'>Assign</a></td>
															</tr>";
									} else {
										$content		.="<td style='vertical-align:top;'></td>
															<td style='vertical-align:top;'></td></tr>";						
									}
								}
								$content				.= "</table>
															<p><u>The Pre-Assign Link</u>: Clicking on this link will open the 'Pre-assign Student to an Advisor' function. Use this capability before 
															students have been actually assigned to an advisor.</p>
															<p><u>The Assign Link</u>: Clicking on this link will open the 'Add Unassigned Student to an Advisors Class' function. Use this capability 
															after students have been assigned to an advisor, otherwise the assignment will be ignored when assigning students to an advisor.</p>";
							} else {	/// no matching records found
								$content	.= "<h3>Find Possible Classes for Unassigned $inp_student_callsign<h3>
												<p>No advisor classes found that match the criteria";
							}
						} else {		//// no advisorClass records 
							$content		.= "<h3>Find Possible Classes for Unassigned $inp_student_callsign<h3>
												<p>No advisorClass records found for the student level of $student_level";
						}
					} 
				}
			} else {
				$content	.= "<p>No further action taken</p>";
			}
		} else {			/// no student record found
			$content			.= "<p>No student record found in $proximateSemester semester</p>";
		}



///////		pass 80			Find Possible Unassigned Students for an Advisor's Class
	} elseif ("80" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 80<br />";
		}
		$standardChecked	= "";
		$expandedChecked	= "";
		if ($inp_search == 'expanded') {
			$expandedChecked	= "checked='checked'";
		} else {
			$standardChecked	= "checked='checked'";
		}
		$jobname			= "Student Management Find Possible Unassigned Students for an Advisor";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the advisor's call sign and class number. The function will search through the 
								unassigned students to see if there are any students that may meet the class time. The 
								function will look at unassigned student's first, second, and third class choices.</p>
								<p>If 'Expanded Search' is selected, the program will also look students in the next 
								semester.</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='81'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Advisor Call Sign:</td>
									<td><input class='formInputText' type='text' size= '30' maxlength='30' name='inp_advisor_callsign' value='$inp_advisor_callsign' autofocus></td></tr>
								<tr><td style='width:150px;'>Advisor Class Number:</td>
									<td><input class='formInputText' type='text' size= '5' maxlength='5' name='inp_advisorClass' value='$inp_advisorClass'></td></tr>
								<tr><td>Search Parameter</td>
									<td><input type='radio' class='formInputButton' name='inp_search' value='standard' $standardChecked> Current Semester<br />
										<input type='radio' class='formInputButton' name='inp_search' value='expanded' $expandedChecked> Expanded Search</td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";



	} elseif ("81" == $strPass) {
// $doDebug = TRUE;
		if ($doDebug) {
			echo "Arrived at pass 81 with inp_advisor_callsign: $inp_advisor_callsign, inp_advisorClass: $inp_advisorClass, and search: $inp_search<br />";
		}
		$jobname			= "Student Management Find Possible Unassigned Students for an Advisor";
		if ($inp_advisor_callsign != '' && $inp_advisorClass != '') {
			$studentsFirstChoiceMatch					= array();
			$studentsSecondChoiceMatch					= array();
			$studentsThirdChoiceMatch					= array();



			//// get the advisor class level and class times
			$sql					= "select * from $advisorClassTableName 
										where advisorclass_semester='$proximateSemester' 
											and advisorclass_call_sign = '$inp_advisor_callsign' 
											and advisorclass_sequence = $inp_advisorClass";
			$wpw1_cwa_advisorclass		= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisorclass === FALSE) {
				handleWPDBError("$jobname MGMT 81",$doDebug);
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
						$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
						$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
						$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
						$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
						$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
						$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
						$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
						$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
						$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;

				
						if ($doDebug) {
							echo "Have an advisor class at level $advisorClass_level for $inp_advisor_callsign and class $inp_advisorClass<br />";
						}
						$searchTime 					= substr($advisorClass_class_schedule_times_utc,0,2);
						$searchTime						= intval($searchTime) * 100;
						$searchDays 					= $advisorClass_class_schedule_days_utc;
						$searchValues					= $searchRange[$searchTime];
						$thisArray						= explode("|",$searchValues);
						$searchBegin					= intval($thisArray[0]);
						$searchEnd						= intval($thisArray[1]);

						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;class schedule: $advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Looking for a replacement between $searchBegin and $searchEnd on $searchDays<br />";
						}

						$expandedSemester	= $nextSemester;
						if ($currentSemester == 'Not in Session') {
							$expandedSemester	= $semesterTwo;
						}					
						
						///// 		now get all unassigned students and see if any can fill the class criteria
						if ($inp_search == 'standard') {
							$sql				= "select * from $studentTableName 
													left join $userMasterTableName on user_call_sign = student_call_sign 
													where student_semester='$proximateSemester' 
														and student_level='$advisorClass_level' 
														and student_response='Y' 
														and student_status='' 
													order by student_class_priority DESC, 
														student_request_date, 
														student_call_sign";
						} else {
							$sql				= "select * from $studentTableName 
													left join $userMasterTableName on user_call_sign = student_call_sign 
													where (student_semester='$proximateSemester' or semester='$expandedSemester') 
														and student_level='$advisorClass_level' 
														and (student_response ='Y' or response = '') 
														and student_status='' 
													order by student_class_priority DESC, 
														student_request_date, 
														student_call_sign";
						}
						$wpw1_cwa_student		= $wpdb->get_results($sql);
						if ($wpw1_cwa_student === FALSE) {
							handleWPDBError("$jobname MGMT 81",$doDebug);
						} else {
							$numSRows			= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $sql<br />and found $numSRows rows<br />";
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

									if ($doDebug) {
										echo "<br />Found $student_call_sign<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;semester: $student_semester<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;response: $student_response<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;first choice UTC: $student_first_class_choice_utc<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;second choice UTC: $student_second_class_choice_utc<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;third choice UTC: $student_third_class_choice_utc<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;Catalog Options: $student_catalog_options<br />
											  &nbsp;&nbsp;&nbsp;&nbsp;Flexible: $student_flexible<br />";
									}
									$doProceed								= TRUE;
									//////	make sure this isn't an excluded advisor
//									if ($doDebug) {
//										echo "student_excluded_advisor: $student_excluded_advisor<br />
//										      advisorClass_call_sign: $advisorClass_call_sign<br />";
//									}
//									if (strPos($student_excluded_advisor,$advisorClass_call_sign) !== FALSE) {
									if (str_contains($student_excluded_advisor,$advisorClass_call_sign)) {
										$doProceed							= FALSE;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Advisor is excluded. Bypassing<br />";
										}
									} else {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Advisor not excluded. Continue<br />";
										}
									}
									if ($student_email_number == '4' and $student_response != 'Y') {
										$doProceed							= FALSE;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;student was dropped. Bypassing<br />";
										}
									}
									if ($student_semester == $proximateSemester && $student_response != 'Y') {
										$doProceed							= FALSE;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;Semester is $theSemester and student_response of $student_response != 'Y'. Bypassing<br />";
										}
									}
									if ($student_assigned_advisor != '') {			/// already assigned
										$doProceed							= FALSE;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;already assigned to $student_assigned_advisor. Bypassing<br />";
										}
									}
									if ($doProceed) {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;processing this student<br />";
										}
										$gotMatch				= FALSE;
										if ($student_semester == $proximateSemester) {
											////// check first class choice for a match
											if ($student_first_class_choice_utc != '' && $student_first_class_choice_utc != 'None') {
												$myArray			= explode(" ",$student_first_class_choice_utc);
												$needleTime			= intval($myArray[0]);
												$origtime			= $myArray[0];
												$needleDays			= $myArray[1];
												if ($doDebug) {
													echo "first needleTime: $needleTime, needleDays: $needleDays<br />
														  Checking against $searchBegin - $searchEnd $searchDays<br />";
												}
										
												if ($needleDays == $searchDays) {			/// halfway there
													if ($doDebug) {
														echo "first choice days match<br />";
													}
													if ($needleTime >= $searchBegin && $needleTime < $searchEnd) {		/// got the rest of the match
														/// convert the advisors class time to student local time
														$result						= utcConvert('tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc,$doDebug);
														if ($result[0] == 'FAIL') {
															if ($doDebug) {
																echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc<br />
																	  Error: $result[3]<br />";
															}
															$displayDays			= "<b>ERROR</b>";
															$displayTimes			= '';
														} else {
															$displayTimes			= $result[1];
															$displayDays			= $result[2];
														}

													
														/// add the student to the studentsFirstChoiceMatch array
														$studentsFirstChoiceMatch[]		= "$student_semester|$student_first_class_choice_utc|$student_request_date|$student_call_sign|$student_last_name, $student_first_name|$student_timezone_offset|$student_first_class_choice|$student_email|$student_phone|$student_class_priority|$displayTimes|$displayDays";
														if ($doDebug) {
															echo "Found a match. Added $student_semester|$student_first_class_choice_utc|$student_request_date|$student_call_sign|$student_last_name, $student_first_name|$student_timezone_offset|$student_first_class_choice|$student_email|$student_phone|$student_class_priority|$displayTimes|$displayDays to studentsFirstChoiceMatch array<br />";
														}
														$gotMatch 					= TRUE;
													} else {
														if ($doDebug) {
															echo "No match on first class choice<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "No match on first class choice<br />";
													}
												}
											}
											if ($student_second_class_choice_utc != '' && $student_second_class_choice_utc != 'None' && $gotMatch == FALSE) {
												////// check second class choice for a match
												$myArray			= explode(" ",$student_second_class_choice_utc);
												$needleTime			= intval($myArray[0]);
												$origTime			= $myArray[0];
												$needleDays			= $myArray[1];
												if ($doDebug) {
													echo "second needleTime: $needleTime, needleDays: $needleDays<br />";
												}
											
												if ($needleDays == $searchDays) {			/// halfway there
													if ($doDebug) {
														echo "second choice days match<br />";
													}
													if ($needleTime >= $searchBegin && $needleTime < $searchEnd) {		/// got the rest of the match
														/// convert the advisors class time to student local time
														$result						= utcConvert('tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc,$doDebug);
														if ($result[0] == 'FAIL') {
															if ($doDebug) {
																echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc<br />
																	  Error: $result[3]<br />";
															}
															$displayDays			= "<b>ERROR</b>";
															$displayTimes			= '';
														} else {
															$displayTimes			= $result[1];
															$displayDays			= $result[2];
														}
														/// add the student to the studentsSecondChoiceMatch array
														$studentsSecondChoiceMatch[]		= "$student_semester|$student_second_class_choice_utc|$student_request_date|$student_call_sign|$student_last_name, $student_first_name|$student_timezone_offset|$student_second_class_choice|$student_email|$student_phone|$student_class_priority|$displayTimes|$displayDays";
														if ($doDebug) {
															echo "Found a match. Added $student_semester|$student_second_class_choice_utc|$student_request_date|$student_call_sign|$student_last_name, $student_first_name|$student_timezone_offset|$student_second_class_choice|$student_email|$student_phone|$student_class_priority|$displayTimes|$displayDays to studentsSecondChoiceMatch array<br />";
														}
														$gotMatch					= TRUE;
													} else {
														if ($doDebug) {
															echo "No match on second class choice<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "No match on second class choice<br />";
													}
												}
											}
											if ($student_third_class_choice_utc != '' && $student_third_class_choice_utc != 'None' && $gotMatch == FALSE ) {
												////// check third class choice for a match
												$myArray			= explode(" ",$student_third_class_choice_utc);
												$needleTime			= intval($myArray[0]);
												$origTime			= $myArray[0];
												$needleDays			= $myArray[1];
												if ($doDebug) {
													echo "third needleTime: $needleTime, needleDays: $needleDays<br />";
												}
											
												if ($needleDays == $searchDays) {			/// halfway there
													if ($doDebug) {
														echo "third choice days match<br />";
													}
													if ($needleTime >= $searchBegin && $needleTime < $searchEnd) {		/// got the rest of the match
														/// convert the advisors class time to student local time
														$result						= utcConvert('tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc,$doDebug);
														if ($result[0] == 'FAIL') {
															if ($doDebug) {
																echo "utcConvert failed 'tolocal',$student_timezone_offset,$advisorClass_class_schedule_times_utc,$advisorClass_class_schedule_days_utc<br />
																	  Error: $result[3]<br />";
															}
															$displayDays			= "<b>ERROR</b>";
															$displayTimes			= '';
														} else {
															$displayTimes			= $result[1];
															$displayDays			= $result[2];
														}
														/// add the student to the studentsThirdChoiceMatch array
														$studentsThirdChoiceMatch[]		= "$student_semester|$student_third_class_choice_utc|$student_request_date|$student_call_sign|$student_last_name, $student_first_name|$student_timezone_offset|$student_third_class_choice|$student_email|$student_phone|$student_class_priority|$displayTimes|$displayDays";
														if ($doDebug) {
															echo "Found a match. Added $student_semester|$student_third_class_choice_utc|$student_request_date|$student_call_sign|$student_last_name, $student_first_name|$student_timezone_offset|$student_third_class_choice|$student_email|$student_phone|$student_class_priority|$displayTimes|$displayDays to studentsThirdChoiceMatch array<br />";
														}
													} else {
														if ($doDebug) {
															echo "No match on third class choice<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "No match on third class choice<br />";
													}
												}
											}
										} else {		/// student is in the next semester
										
										}
									}
								}				/// end of the student while
								///// display the output
								if ($doDebug) {
									$myInt			= count($studentsFirstChoiceMatch);
									if ($myInt > 0) {
										echo "studentsFirstChoiceMatch array has $myInt entries<br /><pre>";
										print_r($studentsFirstChoiceMatch);
										echo "</pre><br />";
									} else {
										echo "studentsFirstChoiceMatch array is empty<br />";
									}
									$myInt			= count($studentsSecondChoiceMatch);
									if ($myInt > 0) {
										echo "studentsSecondChoiceMatch array has $myInt entries<br /><pre>";
										print_r($studentsSecondChoiceMatch);
										echo "</pre><br />";
									} else {
										echo "studentsSecondChoiceMatch array is empty<br />";
									}
									$myInt			= count($studentsThirdChoiceMatch);
									if ($myInt > 0) {
										echo "studentsThirdChoiceMatch array has $myInt entries<br /><pre>";
										print_r($studentsThirdChoiceMatch);
										echo "</pre><br />";
									} else {
										echo "studentsThirdChoiceMatch array is empty<br />";
									}
								}
								//// display the result
								
//						studentsFirstChoiceMatch student_semester|student_first_class_choice_utc|student_request_date|student_call_sign|student_last_name, student_first_name|student_time_zone|student_first_class_choice|student_email|student_phone|student_class_priority|$displayTimes|$displayDays
//												 0                1                              2                    3                 4                                     5                 6                          7             8				9                      10             11
								
								sort($studentsFirstChoiceMatch);
								sort($studentsSecondChoiceMatch);
								sort($studentsThirdChoiceMatch);
								$noPreAssign			= FALSE;
								if ($daysToSemester > 0 && $daysToSemester <= 19) {
									$noPreAssign		= TRUE;
								}
								$content		.= "<h3>Find Possible Students for $inp_advisor_callsign's $advisorClass_level Class Number $advisorClass_sequence</h3>
													<p>Search type: $inp_search<br />
													Search Range: $searchBegin - $searchEnd $searchDays<br />
													Advisor Time Zone: $advisorClass_timezone_offset<br />
													Class is held at about $advisorClass_class_schedule_times_utc UTC on $advisorClass_class_schedule_days_utc 
													($advisorClass_class_schedule_times on $advisorClass_class_schedule_days Local)</p>
													<p><b>Any Pre-Assignments Occur in Production</b></p>
													<table>
													<tr><th>Call Sign</th>
														<th>Name</th>
														<th>Time Zone</th>
														<th>Semester</th>
														<th>Email</th>
														<th>Phone</th>
														<th>Register Date</th>
														<th>Priority</th>
														<th>Student Choice UTC</th>
														<th>Student Choice Local</th>
														<th>Pre-Assign</th>
														<th>Assign</th></tr>";
								$myInt			= count($studentsFirstChoiceMatch);
								if ($myInt > 0) {
									$content				.= "<tr><td colspan='12'>Students whose First Class Choice matches</td></tr>";
									foreach($studentsFirstChoiceMatch as $myValue) {
										$myArray	= explode("|",$myValue);
										$thisCallSign	= $myArray[3];
										$thisName		= $myArray[4];
										$thisTimeZone	= $myArray[5];
										$thisClassChoice	= $myArray[1];
										$thisClassLocal		= $myArray[6];
										$thisEmail			= $myArray[7];
										$thisPhone			= $myArray[8];
										$thisSemester		= $myArray[0];
										$thisRequestDate	= $myArray[2];
										$thisClassPriority	= $myArray[9];
										$content			.= "<tr><td><a href='$updateStudentInfoURL?request_type=callsign&request_info=$thisCallSign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$thisCallSign</a></td>
																	<td>$thisName</td>
																	<td>$thisTimeZone</td>
																	<td>$thisSemester</td>
																	<td>$thisEmail</td>
																	<td>$thisPhone</td>
																	<td>$thisRequestDate</td>
																	<td>$thisClassPriority</td>
																	<td>$thisClassChoice</td>
																	<td>$thisClassLocal</td>";
										if ($noPreAssign) {
											$content		.= "<td>&nbsp;</td>
																<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorClass_level' target='_blank'>Assign</a></td></tr>";
										} else {
											$content		.= "<td><a href='$prodURL?strpass=2&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign' target='_blank'>Pre-Assign</a></td>
																<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorClass_level' target='_blank'>Assign</a></td></tr>";
										}
									}
									$content				.= "<tr><td colspan='12'>&nbsp;</td></tr>";
								} else {
									$content				.= "<tr><td colspan='12'>No students matched on first class choice</td></tr>";
								}
								$myInt			= count($studentsSecondChoiceMatch);
								if ($myInt > 0) {
									$content				.= "<tr><td colspan='13'>Students whose Second Class Choice matches</td></tr>";
									foreach($studentsSecondChoiceMatch as $myValue) {
										$myArray	= explode("|",$myValue);
										$thisCallSign	= $myArray[3];
										$thisName		= $myArray[4];
										$thisTimeZone	= $myArray[5];
										$thisClassChoice	= $myArray[1];
										$thisClassLocal		= $myArray[6];
										$thisEmail			= $myArray[7];
										$thisPhone			= $myArray[8];
										$thisSemester		= $myArray[0];
										$thisRequestDate	= $myArray[2];
										$thisClassPriority	= $myArray[9];
										$thisAdvisorTime	= $myArray[10];
										$thisAdvisorDays	= $myArray[11];
										$content			.= "<tr><td><a href='$updateStudentInfoURL?request_type=callsign&request_info=$thisCallSign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$thisCallSign</a></td>
																	<td>$thisName</td>
																	<td>$thisTimeZone</td>
																	<td>$thisSemester</td>
																	<td>$thisEmail</td>
																	<td>$thisPhone</td>
																	<td>$thisRequestDate</td>
																	<td>$thisClassPriority</td>
																	<td>$thisClassChoice</td>
																	<td>$thisClassLocal</td>";
										if ($noPreAssign) {
											$content		.= "<td>&nbsp;</td>
																<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorClass_level' target='_blank'>Assign</a></td></tr>";
										} else {
											$content		.= "<td><a href='$theURL?strpass=2&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign' target='_blank'>Pre-Assign</a></td>
																<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorClass_level' target='_blank'>Assign</a></td></tr>";
										}
									}
									$content				.= "<tr><td colspan='12'>&nbsp;</td></tr>";
								} else {
									$content				.= "<tr><td colspan='12'>No students matched on Second Class Choice</td></tr>";
								}
								$myInt			= count($studentsThirdChoiceMatch);
								if ($myInt > 0) {
									$content				.= "<tr><td colspan='13'>Students whose Third Class Choice matches</td></tr>";
									foreach($studentsThirdChoiceMatch as $myValue) {
										$myArray	= explode("|",$myValue);
										$thisCallSign	= $myArray[3];
										$thisName		= $myArray[4];
										$thisTimeZone	= $myArray[5];
										$thisClassChoice	= $myArray[1];
										$thisClassLocal		= $myArray[6];
										$thisEmail			= $myArray[7];
										$thisPhone			= $myArray[8];
										$thisSemester		= $myArray[0];
										$thisRequestDate	= $myArray[2];
										$thisClassPriority	= $myArray[9];
										$thisAdvisorTime	= $myArray[10];
										$thisAdvisorDays	= $myArray[11];
										$content			.= "<tr><td><a href='$updateStudentInfoURL?request_type=callsign&request_info=$thisCallSign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$thisCallSign</a></td>
																	<td>$thisName</td>
																	<td>$thisTimeZone</td>
																	<td>$thisSemester</td>
																	<td>$thisEmail</td>
																	<td>$thisPhone</td>
																	<td>$thisRequestDate</td>
																	<td>$thisClassPriority</td>
																	<td>$thisClassChoice</td>
																	<td>$thisClassLocal</td>";
										if ($noPreAssign) {
											$content		.= "<td>&nbsp;</td>
																<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorClass_level' target='_blank'>Assign</a></td></tr>";
										} else {
											$content		.= "<td><a href='$theURL?strpass=2&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign' target='_blank'>Pre-Assign</a></td>
																<td><a href='$theURL?strpass=40&inp_advisor_callsign=$inp_advisor_callsign&inp_student_callsign=$thisCallSign&level=$advisorClass_level' target='_blank'>Assign</a></td></tr>";
										}
									}
								$content				.= "<tr><td colspan='12'>&nbsp;</td></tr>";
								} else {
									$content				.= "<tr><td colspan='12'>No students matched on Third Class Choice</td></tr>";
								}
								$content					.= "</table>
																<p>Clicking on the 'Pre-Assign' link will open the'Pre-assign a Student' page in a new tab with the
																form partially filled out<br />
																Clicking on the 'Assign' link will open the 'Adding a Student to 
																an Advisor's Class' page in a new tab with the form filled out</p>";
							} else {			/// no students meeting the criteria
								$content		.= "<h3>Find Possible Students for $inp_advisor_callsign's $advisorClass_level Class Number $advisorClass_sequence</h3>
													<p>No unassigned students found who met the class criteria</p>";					
							}
						}
					}						/// end of the advisorClass while
				} else {					/// no advisorClass records for this advisor
					$content		.= "<h3>Find Possible Students for $inp_advisor_callsign Class Number $inp_advisorClass</h3>
										<p>No advisorClass record found for $inp_advisor_callsign's $inp_advisorClass class</p>";
				}
			}
		} else {	
			$content				.= "Incomplete or Incorrect Input";
		}	



///////		pass 85			Verify One or More Students
	} elseif ("85" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 85<br />";
		}
		$jobname			= "Student Management Verify One or More Students";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the student's call sign or a list of student call signs separated by 
								commas. The function will verify that each student is assigned to a class and 
								has a student_status of S. If so, the student_status will be set to Y.</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='86'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign:<br /><em>Enter one or more call signs 
								separated by commas</em></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_callsign' autofocus></td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";

	} elseif ("86" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 86 with inp_callsign of $inp_callsign<br />";
		}
		$jobname			= "Student Management Verify One or More Students";
		$content			.= "<h3>$jobname</h3>";
		if ($inp_callsign == '') {
			$content		.= "Invalid or Incomplete information provided";
		} else {
			$inp_callsign	= str_replace(" ","",$inp_callsign);
			$callsignArray	= explode(",",$inp_callsign);
			foreach($callsignArray as $thisCallSign) {
				if ($doDebug) {
					echo "<br />Processing $thisCallSign<br />";
				}
				$content			.= "<br />Processing $thisCallSign<br />";
				$sql				= "select * from $studentTableName 
										left join $userMasterTableName on user_call_sign = student_call_sign 
									 	where student_semester='$proximateSemester' 
									 		and student_call_sign='$thisCallSign' 
									 	order by student_call_sign";
				$wpw1_cwa_student	= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError("$jobname MGMT 86",$doDebug);
				} else {
					$numSRows			= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and found $numSRows rows<br />";
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
				
				
							if ($doDebug) {
								echo "have student record for $student_call_sign:<br />
										Response: $student_response<br />
										Status: $student_status<br />
										Intervention Required: $student_intervention_required<br />
										Assigned Advisor: $student_assigned_advisor<br />
										Assigned Advisor Class: $student_assigned_advisor_class<br />";
							}	
							$content				.= 	"&nbsp;&nbsp;&nbsp;&nbsp;$student_last_name, $student_first_name Advisor: $student_assigned_advisor<br />";	
							$doProcess				= TRUE;
							$updateParams			= array();
							if ($student_intervention_required == 'H') {
								$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student is on hold. Bypassed<br />";
								$doProcess			= FALSE;
							}
							if ($student_response != 'Y') {
								if ($student_response == '') {
									$student_response	= 'blank';
								}
								$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student response is $student_response. Did not verify. Bypassed<br />";
								$doProcess			= FALSE;
							}
							if ($student_status != 'S') {
								if ($student_status == '') {
									$student_status	= 'blank';
								}
								$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student status is $student_status. Must be S. Bypassed<br />";
								$doProcess			= FALSE;
							}
							if ($student_assigned_advisor == '') {
								$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student does not have an assigned advisor. Bypassed<br />";
								$doProcess			= FALSE;
							}
							if ($student_assigned_advisor_class == '') {
								$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student does not have an assigned advisor class. Bypassed<br />";
								$doProcess			= FALSE;
							}
							if ($doProcess) {
								$student_action_log			= "$student_action_log / $actionDate MGMT85 $userName confirmed student participation ";
								$updateParams				= array('student_status'=>'Y',
																	'student_action_log'=>$student_action_log);
								$updateFormat				= array('%s','%s');
								$studentUpdateData			= array('tableName'=>$studentTableName,
																	'inp_data'=>$updateParams,
																	'inp_format'=>$updateFormat,
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
									handleWPDBError("$jobname MGMT 86",$doDebug);
								} else {
									$lastError			= $wpdb->last_error;
									if ($lastError != '') {
										handleWPDBError("$jobname MGMT 86",$doDebug);
										$content		.= "Fatal program error. System Admin has been notified";
										if (!$doDebug) {
											return $content;
										}
									}

									$content		.= "Student $student_call_sign verified<br />";
								}
							}
						}
					} else {
						$content		.= "&nbsp;&nbsp&nbsp;&nbsp;No $studentTableName table record. Bypassed<br />";
					}
				}
			}
			$content				.= "<p>Processing completed</p>";
		}



	} elseif ("90" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 90<br />";
		}
		$jobname	= "Student Management Add a Student to an Advisor Class Regardless";
		$content .= "<h3>$jobname of Semester or Status</h3>
					<p>Enter the student call sign to be added, the advisor's call sign, and the class 
					number that the advisor is teaching. If the class number isn't known, click the 
					'Show Classes' button and the program 
					will show a list of classes matching the student's level (if only one class, the function 
					will proceed with the assignment). Otherwise, click the 'Assign' 
					button.</p>
					<p>The student must currently be unassigned (student status not Y)<br />
					The student will be pulled into the current semester, if necessary<br />
					The student level will be changed, if necessary<br />
					The student response will be set to Y<br />
					The student status will be set to S (awaiting verification)<br />
					The select date will be set to the current date<br />
					If student has been dropped, email number will be set to 3<br />
					Any holds will be released</p>
					<p><b>Note:</b> If this action is taken before students are assigned to advisor classes, 
					that process will override this action. In that case the better option is to pre-assign 
					the student to the advisor's class.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
					<input type='hidden' name='strpass' value='91'>
					<table style='border-collapse:collapse;'>
					<tr><td style='width:150px;'>Student Call Sign:</td>
						<td><input class='formInputText' type='text' size= '30' maxlength='30' name='inp_student_callsign' autofocus></td></tr>
					<tr><td style='width:150px;'>Class Level:</td>
						<td><input class='formInputButton' type='radio' name='inp_level' value='Beginner'> Beginner<br />
							<input class='formInputButton' type='radio' name='inp_level' value='Fundamental'> Fundamental<br />
							<input class='formInputButton' type='radio' name='inp_level' value='Intermediate'> Intermediate<br />
							<input class='formInputButton' type='radio' name='inp_level' value='Advanced'> Advanced</td></tr>
					<tr><td style='width:150px;'>Advisor Call Sign:</td>
						<td><input class='formInputText' type='text' size = '30' maxlength='30' name='inp_advisor_callsign'></td></tr>
					<tr><td style='width:150px;'>Advisor Class:</td>
						<td><input class='formInputText' type='text' size = '5' maxlength='5' name='inp_advisorClass'></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td>
						<td><input class='formInputButton' name='submit' type='submit' value='Assign' /></td></tr>
					</table>
					</form>";

	} elseif ("91" == $strPass) {				// get the class 
// $doDebug = TRUE;
		if ($doDebug) {
			echo "<br />At pass 91 with student $inp_student_callsign; level: $inp_level; advisor: $inp_advisor_callsign; class: $inp_advisorClass; submit: $submit<br />";
		}
		$jobname	= "Student Management Add a Student to an Advisor Class Regardless";
		
		$theSemester		= $initializationArray['currentSemester'];
		$nextSemester		= $initializationArray['nextSemester'];
		if ($theSemester == 'Not in Session') {
			$theSemester	= $nextSemester;
		}
		$assignMatch		= FALSE;
				
		$class_sequence		= 0;                            

		// See if the student is in the student table
		if ($haveStudent) {
			$reqDate								= substr($student_request_date,0,10);
			$doProceed								= TRUE;
			if ($doDebug) {
				echo "Student $student_call_sign student status is $student_status<br />
					  assigned advisor is $student_assigned_advisor<br />";
			}
		}
		if ($doProceed) {
			// see if there is an advisor in the proximate semester. If so, get the classes at that level for the advisor
			if ($haveAdvisor && $advisor_semester == $theSemester) {
				$optionList				= '';
				$sql					= "select * from $advisorClassTableName 
											where advisorclass_semester='$theSemester' 
												and advisorclass_call_sign = '$inp_advisor_callsign' 
												and advisorclass_level='$inp_level' 
											order by advisorclass_sequence";
				$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisorclass === FALSE) {
					handleWPDBError("$jobname MGMT 91",$doDebug);
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
							$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
							$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
							$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
							$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
							$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
							$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
							$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
							$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
							$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
	
							if ($inp_advisorClass == $advisorClass_sequence) {
								$class_ID							= $advisorClass_ID;	
								$class_sequence					 	= $advisorClass_sequence;
								$assignMatch						= TRUE;
								if ($doDebug) {
									echo "Have a class match. Saving id: $class_ID; sequence: $class_sequence<br />";
								}
							}
						}
						if ($assignMatch) {
							$content			.= "<h3>Add Student $inp_student_callsign to Advisor $inp_advisor_callsign Class Regardless of Semester or Status</h3>";
							if ($doDebug) {
								echo "<br />Doing the assignment<br />
										advisor callsign: $inp_advisor_callsign<br />
										advisor class: $inp_advisorClass<br />
										level: $inp_level<br />
										student: $student_call_sign<br />
										current semester: $student_semester<br />
										current level: $student_level<br />
										current response: $student_response<br />
										current status: $student_status<br />
										current assigned advisor: $student_assigned_advisor<br />
										current assigned advisor class: $student_assigned_advisor_class<br />
										current intervention required: $student_intervention_required<br />
										current email number: $student_email_number<br />";
							}
							// if the student is assigned elsewhere, first remove the student
							if ($student_assigned_advisor != '' && ($student_status == 'S' || $student_status == 'Y')) {
								if ($doDebug) {
									echo "student has to be removed from $student_assigned_advisor $advisor_class_squence class<br />";
								}
								$inp_data			= array('inp_student'=>$student_call_sign,
															'inp_semester'=>$student_semester,
															'inp_assigned_advisor'=>$student_assigned_advisor,
															'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
															'inp_remove_status'=>'',
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
									$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
								} else {
									$content		.= "Student removed from $student_assigned_advisor $student_assigned_advisor_class class and unassigned<br />";
								}
							}
							// if the student_semester is not the proximate semester, change the semester
							if ($student_semester != $theSemester) {
								if ($doDebug) {
									echo "Student semester $student_semester needs to be changed to $theSemester<br />";
								}
								$updateParams		= array("student_semester|$theSemester|s",
															"student_response|Y|s");
								$studentUpdateData			= array('tableName'=>$studentTableName,
																	'inp_data'=>$updateParams,
																	'inp_method'=>'update',
																	'jobname'=>$jobname,
																	'inp_id'=>$student_ID,
																	'inp_callsign'=>$student_call_sign,
																	'inp_semester'=>$theSemester,
																	'inp_who'=>$userName,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug);
								$updateResult	= updateStudent($studentUpdateData);
								if ($updateResult[0] === FALSE) {
									handleWPDBError("$jobname MGMT 91",$doDebug);
								} else {
									$lastError			= $wpdb->last_error;
									if ($lastError != '') {
										handleWPDBError("$jobname MGMT 91",$doDebug);
										$content		.= "Fatal program error. System Admin has been notified";
										if (!$doDebug) {
											return $content;
										}
									}
	
									$content		.= "Updated student semester to $theSemester<br />";
								}
							}								
							// now assign the student
							if ($doDebug) {
								echo "doing the assignment to $inp_advisor_callsign $class_sequence class<br />";
							}
							$inp_data			= array('inp_student'=>$student_call_sign,
														'inp_semester'=>$theSemester,
														'inp_assigned_advisor'=>$inp_advisor_callsign,
														'inp_assigned_advisor_class'=>$class_sequence,
														'inp_remove_status'=>'',
														'inp_arbitrarily_assigned'=>'',
														'inp_method'=>'add',
														'jobname'=>$jobname,
														'userName'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
				
							$addResult			= add_remove_student($inp_data);
							if ($addResult[0] === FALSE) {
								$thisReason		= $removeResult[1];
								if ($doDebug) {
									echo "attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason<br />";
								}
								sendErrorEmail("$jobname Attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason");
								$content		.= "Attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason<br />";
							} else {
								$content		.= "Student has been added to $inp_advisor_callsign class $class_sequence<br />
								<p>Click 'Push' to push the information to the advisor:<br />
								<form method='post' action='$pushURL' 
								name='selection_form_41' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='2'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_semester' value='$student_semester'>
								<input type='hidden' name='request_info' value='$inp_advisor_callsign'>
								<input type='hidden' name='request_type' value= 'Full'>
								<input type='submit' class='formInputButton' value='Push'></form></p><br /><br />";
							}								
						} else {
							$content	.= "<p>No advisorClass record found for $inp_advisor_callsign class $inp_advisorClass</p>";
						}
					} else {
						if ($doDebug) {
							echo "No $advisorClassTableName entry for $inp_advisor_callsign<br />";
						}
						$content		.= "No $advisorClassTableName class entry found for $inp_advisor_callsign";
					}
	
				}
			} else {
				if ($doDebug) {
					echo "no advisor $inp_advisor found in $theSemester semester<br />";
				}
				$content		.= "No advisor $inp_advisor_callsign found in $advisorTableName for the $theSemester semester.";
			}
		} else {		// no student found
			if ($doDebug) {
				echo "No student found with call sign $inp_student_callsign<br />";
			}
			$content		.= "Student $inp_student_callsign is not registered.";
		}





///////		pass 100			Confirm one or more students
	} elseif ("100" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 100<br />";
		}
		$jobname			= "Student Management Confirm One or More Students";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the student's call sign or a list of student call signs separated by 
								commas. The function will verify the each student is unassigned and not 
								on hold. If so, the response is set to Y.</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='101'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign:<br /><em>Enter one or more call signs 
								separated by commas</em></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_callsign' autofocus></td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";

	} elseif ("101" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 101 with inp_callsign of $inp_callsign<br />";
		}
		$jobname			= "Student Management Confirm One or More Students";
		$content			.= "<h3>$jobname</h3>";
		if ($inp_callsign == '') {
			$content		.= "Invalid or Incomplete information provided";
		} else {
			$inp_callsign	= str_replace(" ","",$inp_callsign);
			$callsignArray	= explode(",",$inp_callsign);
			foreach($callsignArray as $thisCallSign) {
				if ($doDebug) {
					echo "<br />Processing $thisCallSign<br />";
				}
				$content			.= "<br />Processing $thisCallSign<br />";
				$sql				= "select * from $studentTableName 
										left join $userMasterTableName on user_call_sign = student_call_sign 
										where student_semester='$proximateSemester' 
											and student_call_sign='$thisCallSign'";
				$wpw1_cwa_student	= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError("$jobname MGMT 101",$doDebug);
				} else {
					$numSRows			= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and found $numSRows rows<br />";
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
							$student_master_action_log 			= $studentRow->user_action_log;
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
		
							// if you need the country name and phone code, include the following
							$countrySQL		= "select * from wpw1_cwa_country_codes  
												where country_code = '$student_country_code'";
							$countrySQLResult	= $wpdb->get_results($countrySQL);
							if ($countrySQLResult === FALSE) {
								handleWPDBError($jobname,$doDebug);
								$student_country		= "UNKNOWN";
								$student_ph_code		= "";
							} else {
								$numCRows		= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
								}
								if($numCRows > 0) {
									foreach($countrySQLResult as $countryRow) {
										$student_country		= $countryRow->country_name;
										$student_ph_code		= $countryRow->ph_code;
									}
								} else {
									$student_country			= "Unknown";
									$student_ph_code			= "";
								}
							}
				
							if ($doDebug) {
								echo "have student record for $student_call_sign:<br />
										Response: $student_response<br />
										Status: $student_status<br />
										Intervention Required: $student_intervention_required<br />";
							}	
							$content				.= 	"&nbsp;&nbsp;&nbsp;&nbsp;$student_last_name, $student_first_name ($student_call_sign)<br />";	
							$doProcess				= TRUE;
							$updateParams			= array();
							if ($student_intervention_required == 'H') {
								$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student is on hold. Bypassed<br />";
								$doProcess			= FALSE;
							}
							if ($student_response != '') {
								$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student response is $student_response. Did not confirm. Bypassed<br />";
								$doProcess			= FALSE;
							}
							if ($student_status != '') {
								$content			.= "&nbsp;&nbsp;&nbsp;&nbsp;Student status is $student_status. Must be blank. Bypassed<br />";
								$doProcess			= FALSE;
							}
							if ($doProcess) {
								$student_action_log			= "$student_action_log / $actionDate MGMT100 $userName confirmed student interest ";
								$updateParams				= array('student_response'=>'Y',
																	'student_response_date'=>$currentDate,
																	'student_action_log'=>$student_action_log);
								$updateFormat				= array('%s','%s','%s');
								$studentUpdateData		= array('tableName'=>$studentTableName,
																'inp_data'=>$updateParams,
																'inp_format'=>$updateFormat,
																'inp_method'=>'update',
																' jobname'=>$jobname,
																'inp_id'=>$student_ID,
																'inp_callsign'=>$student_call_sign,
																'inp_semester'=>$student_semester,
																'inp_who'=>$userName,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug);
								$updateResult	= updateStudent($studentUpdateData);
								if ($updateResult[0] === FALSE) {
									$myError	= $wpdb->last_error;
									$mySql		= $wpdb->last_query;
									$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
									if ($doDebug) {
										echo $errorMsg;
									}
									sendErrorEmail($errorMsg);
									$content		.= "Unable to update content in $studentTableName<br />";
								} else {
									$content		.= "Student $student_call_sign confirmed<br />";
								}
							}
						}
					} else {
						$content		.= "&nbsp;&nbsp&nbsp;&nbsp;No $studentTableName table record. Bypassed<br />";
					}
				}
			}
			$content				.= "<p>Processing completed</p>";
		}
		
		
/////// pass 110 -- add excluded advisor		
		
		
	} elseif ("110" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 110 -- add excluded advisor<br />";
		}
		$jobname			= "Student Management Add Excluded Advisor";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the student's call sign to which the specified advisor should be 
								excluded</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='112'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign:<br /></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_student_callsign' autofocus></td></tr>
								<tr><td style='width:150px;'>Excluded advisor callsign:<br /></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_callsign'></td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";
	} elseif ("112" == $strPass) {

// $doDebug	= TRUE;

		if ($doDebug) {
			echo "<br />At pass 112 with inp_student_callsign $inp_student_callsign and advisor callsign: $inp_callsign<br />";
		}
		$jobname			= "Student Management Add Excluded Advisor";
		$content			.= "<h3>$jobname</h3>";

		$doProceed			= TRUE;		
		if ($haveStudent) {
			if ($doDebug) {
				echo "Have student record. Preparing to update<br />
				student_excluded_advisor: $student_excluded_advisor<br />";
			}
		} else {
			if ($doDebug) {
				echo "haveStudent is FALSE<br />";
				$content		.= "<p>No record found for student $inp_student_callsign<br />";
				$doProceed 		= FALSE;
			}
		}
		if ($doProceed) {
			$newExcludedAdvisorList	= updateExcludedAdvisor($student_excluded_advisor,$inp_callsign,'add',$doDebug);
			if ($newExcludedAdvisorList === FALSE) {
				$content		.= "<p>Attempt to add $inp_callsign as an excluded advisor for $student_call_sign failed</p>";
			} else {
				// update if excluded advisors has changed
				if ($student_excluded_advisor != $newExcludedAdvisorList) {
					$updateParams		= array('student_assigned_advisor'=>$newExcludedAdvisorList);
					$updateFormat		= array('%s');
					$studentUpdateData		= array('tableName'=>$studentTableName,
													'inp_method'=>'update',
													'inp_data'=>$updateParams,
													'inp_format'=>$updateFormat,
													'jobname'=>$jobname,
													'inp_id'=>$student_ID,
													'inp_callsign'=>$student_call_sign,
													'inp_semester'=>$student_semester,
													'inp_who'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
					$updateResult	= updateStudent($studentUpdateData);
					if ($updateResult[0] === FALSE) {
						$myError	= $wpdb->last_error;
						$mySql		= $wpdb->last_query;
						$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
						if ($doDebug) {
							echo $errorMsg;
						}
						sendErrorEmail($errorMsg);
						$content		.= "Unable to update content in $studentTableName<br />";
					} else {
						$content		.= "<p>Advisor $inp_callsign has been excluded for student $inp_student_callsign</p>";
					}
				} else {
					$content	.= "<p>No change to student_excluded_advisor was needed</p>";
				}
			}
		}

/////// pass 120 -- remove excluded advisor		
		
		
	} elseif ("120" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 120 -- remove excluded advisor<br />";
		}
		$jobname			= "Student Management Remove Excluded Advisor";
		$content			.= "<h3>$jobname</h3>
								<p>Enter the student's call sign from which the specified advisor should be 
								no longer be excluded</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='122'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px;'>Student Call Sign:<br /></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_student_callsign' autofocus></td></tr>
								<tr><td style='width:150px;'>Excluded advisor callsign:<br /></td>
									<td><input class='formInputText' type='text' size= '50' maxlength='100' name='inp_callsign'></td></tr>
								$testModeOption
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";
	} elseif ("122" == $strPass) {

// $doDebug	= TRUE;

		if ($doDebug) {
			echo "<br />At pass 122 with inp_student_callsign $inp_student_callsign and advisor callsign: $inp_callsign<br />";
		}
		$jobname			= "Student Management Remove Excluded Advisor";
		$content			.= "<h3>$jobname</h3>";

		$doProceed			= TRUE;		
		// get all the student records
		// remove the advisor to be removed from each one, if present
		
		$sql			= "select student_id, 
								  student_call_sign, 
								  student_semester, 
								  student_excluded_advisor 
							from $studentTableName 
							where student_call_sign = '$inp_student_callsign' 
							order by student_request_date DESC";
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
					$student_id					= $studentRow->student_id;
					$student_call_sign			= $studentRow->student_call_sign;
					$student_semester			= $studentRow->student_semester;
					$student_excluded_advisor	= $studentRow->student_excluded_advisor;

					if ($doDebug) {
						echo "<br />processing id $student_id ($student_call_sign)<br />";
					}
					$newExcludedAdvisorList		= updateExcludedAdvisor($student_excluded_advisor,$inp_callsign,'delete',$doDebug);
					if ($newExcludedAdvisorList === FALSE) {
						$content				.= "<p>Attempt to delete $inp_callsign from excluded advisors $student_excluded_advisor failed for id $student_id</p>";
					} else {
						if ($student_excluded_advisor != $newExcludedAdvisorList) {
							$studentUpdateData		= array('tableName'=>$studentTableName,
															'inp_method'=>'update',
															'inp_data'=>array('student_excluded_advisor'=>$newExcludedAdvisorList),
															'inp_format'=>array('%s'),
															'jobname'=>$jobname,
															'inp_id'=>$student_id,
															'inp_callsign'=>$student_call_sign,
															'inp_semester'=>$student_semester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateStudent($studentUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to update content in $studentTableName<br />";
							} else {
								$content		.= "<p>Advisor $inp_callsign has been removed as an excluded advisor in student record for $student_semester semester</p>";
							}							
						} else {
							$content			.= "<p>No changes needed for id $student_id ($student_call_sign) for semester $student_semester</p>";
						}
					}
				}
			} else {
				$content		.= "<p>No student records found for $student_call_sign</p>";
			}
		} 

	}

	
		
//	$content		.= "<br /><p>To return to the Student Management menu, click
//						<a href='$theURL?strpass=1'>HERE</a>.</p>";
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
add_shortcode ('student_management', 'student_management_func');
