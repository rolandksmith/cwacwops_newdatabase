function list_students_for_a_semester_func() {

// Add Status Legend - 19 Dec 19 - 00:03z bc
// turn off Y status - 14feb20 bc
// turned Y status back on - 17feb20 bc
// add word status to student status in title - bc 18aug20	
// modified 18Jan2022 to use tables rather than pods
// added utc first class  /  bc 19Apr22 18:51 
// modified 1Oct22 by Roland for new timezone process 	
// Modified 16Apr23 by Roland to fix action_log
// Modified 13Jul23 by Roland to use consolidated tables
// Modified 19Oct24 by Roland for new database
	
	global $wpdb;
	
	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	$doDebug						= TRUE;
	$testMode						= FALSE;
	$strPass						= "1";
	$jobname						= "List Students for a Semester";
	
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
			}
		}
	}
	
	$totalUnassigned				= 0;
	$totalStudents					= 0;
	$totalConfirmed					= 0;
	$totalReplaced					= 0;
	$totalDeclined					= 0;
	$totalSelected					= 0;
	$totalOnHold					= 0;
	$theURL							= "$siteURL/cwa-list-students-for-a-semester/";
	
	$content = "";	

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$currentSemester	= $initializationArray['currentSemester'];
		$nextSemester		= $initializationArray['nextSemester'];
		$semesterTwo		= $initializationArray['semesterTwo'];
		$semesterThree		= $initializationArray['semesterThree'];
		$optionList		= "";
		if ($currentSemester != "Not in Session") {
			$optionList	.= "<option value='$currentSemester'>$currentSemester</option>";
		}
		$optionList		.= "<option value='$nextSemester'>$nextSemester</option>";
		$optionList		.= "<option value='$semesterTwo'>$semesterTwo</option>";
		$optionList		.= "<option value='$semesterThree'>$semesterThree</option>";
		
		$content 		.= "<h3>Select the Semester of Interest</h3>
							<p>Select from the list below:</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Semester</td><td>
							<select name='inp_semester' required='required' size='5' autofocus class='formSelect'>
							$optionList
							</select></td></tr>
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";

	} elseif ("2" == $strPass) {

		$currentSemester	= $initializationArray['currentSemester'];
		$nextSemester		= $initializationArray['nextSemester'];
		$semesterTwo		= $initializationArray['semesterTwo'];
		$theSemester		= $currentSemester;
		if ($theSemester == 'Not in Session') {
			$theSemester	= $nextSemester;
		}

	
		if ($testMode) {
			$studentTableName	= 'wpw1_cwa_student2';
			$advisorTableName	= 'wpw1_cwa_advisor2';
			$userMasterTableName	= 'wpw1_cwa_user_master2';
			echo "Function is under development.<br />";
			$content .= "Function is under development.<br />";
		} else {
			$studentTableName = 'wpw1_cwa_student';
			$advisorTableName	= 'wpw1_cwa_advisor';
			$userMasterTableName	= 'wpw1_cwa_user_master';
		}
	
	
	
		if ($inp_semester == $theSemester) {
			$sql		= "select * from $studentTableName 
							left join $userMasterTableName on user_call_sign = student_call_sign 
							where student_semester='$inp_semester' 
							order by student_response, 
							     	 student_assigned_advisor, 
							         student_timezone_offset, 
							         student_level, 
							         student_status";
		} else {
			$sql		= "select * from $studentTableName 
							left join $userMasterTableName on user_call_sign = student_call_sign 
							where student_semester='$inp_semester' 
							order by student_response, 
									 student_assigned_advisor, 
									 student_level, 
									 student_timezone_offset, 
									 student_status";
		}
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
			}
			if ($numSRows > 0) {
				$content .= "<h3>Student Status for $inp_semester with Response of Y or R</h3>
							<h6>Student Status Legend: </h6>
							<p>C - Student has been Removed may be Replaced<br />
							N - Advisor Declined Replacement Student<br />
							R - Student not yet Replaced<br />
							S - Assigned Advisor not yet Verified Student<br />
							Y - Student Verified and taking Class</p>
							<table>
							<tr><th>ID</th>
								<th>Call Sign</th>
								<th>Name</th>
								<th>Level</th>
								<th>Time<br />Zone</th>
								<th>Req Date</th>
								<th>Response</th>
								<th>Student Status</th>
								<th>Intervention<br />Required</th>
								<th>Semester</td>
								<th>UTC First Choice</th>
								</tr>";
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
	
					$student_level							= substr($student_level,0,3);
			
			
					if ($doDebug) {
						echo "Processing call sign $student_call_sign with response $student_response and status $student_status<br />";
					}
					$totalStudents++;
					$theStatus					= '';
					if ($student_status == 'S') {
						$theStatus				= "Selected but not confirmed";
						$totalSelected++;
					} elseif ($student_status == 'Y') {
						$theStatus				= "Selected and Attending";
						$totalConfirmed++;
					} elseif ($student_status == 'N') {
						$theStatus				= "Selected and declined";
						$totalDeclined++;
					} elseif ($student_status == 'R' || $student_status == 'C') {
						$theStatus				= "Replacement Requested";
						$totalReplaced++;
					}
					if ($student_response == 'Y' and $student_status == '') {
						$totalUnassigned++;
						$student_assigned_advisor	= "unassigned";
					}
					if ($student_intervention_required == 'H') {
						$totalOnHold++;
					}
			
					$content	.= "<tr><td>$student_ID</td>
										<td>$student_call_sign</td>
										<td>$student_last_name, $student_first_name</td>
										<td>$student_level</td>
										<td>$student_timezone_id $student_timezone_offset</td>
										<td>$student_request_date</td>
										<td>$student_response</td>
										<td>$student_status</td>
										<td>$student_intervention_required</td>
										<td>$student_semester</td>
										<td>$student_first_class_choice_utc</td>
										</tr>";
				}
				$content .= "</table><p>Statistics:<br />
								$totalStudents Total Students<br />
								$totalUnassigned Total unassigned students<br />
								$totalSelected	Total selected but not confirmed<br />
								$totalConfirmed Selected and confirmed<br />
								$totalDeclined Selected and declined<br />
								$totalReplaced Replacements Requested<br />
								$totalOnHold Students On Hold</p>";
			} else {
				if ($doDebug) {
					echo "No records found in $studentTableName table<br >";
				}
				$content	.= "No records found in $studentTableName table<br >";
			}
		}
	}


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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('list_students_for_a_semester', 'list_students_for_a_semester_func');

