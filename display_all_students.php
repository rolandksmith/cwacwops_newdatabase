function display_all_students_func() {

// modified 17feb2020 by Bob C to select all students
// mod 01mar20 - bc - add more report fields and order by level
// modified 15Jan2022 by Roland to use tables instead of pods
// Modified 29Sep22 by Roland for the new timezone setup
// Modified 15Apr23 by Roland to fix the action_log
// Modified 13Jul23 by Roland to use consolidated tables
// Modified 13Oct24 by Roland for new database



	global $wpdb;
	
	$doDebug				= FALSE;
	$testMode				= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 				= $context->validUser;
	$userName				= $context->userName;
	$siteURL				= $context->siteurl;
	$currentSemester		= $context->currentSemester;
	$nextSemester	    	= $context->nextSemester;
	$semesterTwo	    	= $context->semesterTwo;
	$semesterThree  		= $context->semesterThree;

	if ($userName = '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
		ini_set('display_errors','1');
		error_reporting(E_ALL);
		ini_set('max_execution_time',60);	
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$inp_semester		= "";
	$strPass			= "1";
	$myCount			= 0;
	$theURL				= "$siteURL/cwa-display-all-students/";
	$jobname			= "Display All Students";
	$inp_orderby		= '';
	
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

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
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_orderby") {
				$inp_orderby	 = $str_value;
				$inp_orderby	 = filter_var($inp_orderby,FILTER_UNSAFE_RAW);
			}
	    }
    }

	$content = "";	

	if ($testMode) {
		if ($doDebug) {
			echo "<b>Operating in Test Mode</b><br />";
		}
		$content					.= "<p><b>Operating in Test Mode</b></p>";
		$studentTableName			= 'wpw1_cwa_student2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$studentTableName			= 'wpw1_cwa_student';
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}


	
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting.<br />";
		}
		$optionList		= "";
		if ($currentSemester != "Not in Session") {
			$optionList	.= "<option value='$currentSemester'>$currentSemester</option>";
		}
		$optionList		.= "<option value='$nextSemester'>$nextSemester</option>";
		$optionList		.= "<option value='$semesterTwo'>$semesterTwo</option>";
		$optionList		.= "<option value='$semesterThree'>$semesterThree</option>";
		
		$content 		.= "<h3>$jobname</h3>
							<p>Select the Semester of Interest from the list below:</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px;'>Semester</td>
								<td><select name='inp_semester' required='required' size='5' autofocus class='formSelect' required>
									$optionList
									</select></td></tr>
							<tr><td style='vertical-align:top;'>Order By:</td>
								<td><input type='radio' class='forminputbutton' name='inp_orderby' value='offset' required> Timezone Offset<br />
									<input type='radio' class='forminputbutton' name='inp_orderby' value='level' required> Level<br />
									<input type='radio' class='forminputbutton' name='inp_orderby' value='countrycode' required> Country Code<br />
									<input type='radio' class='forminputbutton' name='inp_orderby' value='callsign' required> Call Sign</td></tr>
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";

//////////////

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}
		if ($inp_orderby == '') {
			$inp_orderby		= 'callsign';
		}
		if ($inp_orderby == 'offset') {
			$thisOrderBy		= "student_timezone_offset,student_level,student_call_sign";
		} elseif ($inp_orderby == 'countrycode') {
			$thisOrderBy		= "user_country_code,student_level,student_call_sign";
		} elseif ($inp_orderby == 'level') {
			$thisOrderBy		= "student_level,student_call_sign";
		} elseif ($inp_orderby == 'callsign') {
			$thisOrderBy		= "student_call_sign";
		}

		$content 						.= "<h3>$jobname for $inp_semester Semester</h3>
											<p>Ordered by $thisOrderBy</p>
											<table>
											<h6>Student Status Legend: </h6><p>C - Student has been Removed may be Replaced<br />N - Advisor Declined Replacement Student<br />R - Student not yet Replaced<br />
											S - Assigned Advisor not yet Verified Student<br />Y - Student Verified and taking Class</p>
											<tr><th>Student ID</th>
												<th>Call Sign</th>
												<th>Last Name</th>
												<th>First Name</th>
												<th>Email</th>
												<th>Phone</th>
												<th>City</th>
												<th>State</th>
												<th>Country</th></tr>
											<tr><th>Timezone Offset</th>
												<th>Level</th>
												<th>Request Date</th>
												<th>Semester</th>
												<th>Class Choice</th>
												<th>Response</th>
												<th>Student Status</th>
												<th>Promotable</th>
												<th></th></tr>";
	
		$sql				= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign
								where student_semester='$inp_semester' 
								order by $thisOrderBy";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows		= $wpdb->num_rows;
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
					$student_student_parent 				= $studentRow->student_parent;
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
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
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
		
					$content .= "<tr><td>$student_ID</td>
									<td>$student_call_sign </td>
									<td>$student_last_name</td>
									<td>$student_first_name</td>
									<td>$student_email</td>
									<td>$student_phone</td>
									<td>$student_city</td>
									<td>$student_state</td>
									<td>$student_country</td></tr>
								<tr><td>$student_timezone_offset</td>
									<td>$student_level</td>
									<td>$student_request_date</td>
									<td>$student_semester</td>
									<td>$student_first_class_choice</td>
									<td>$student_response</td>
									<td>$student_status</td>
									<td>$student_promotable</td>
									<td></td></tr>
								<tr><td colspan='9'><hr /></td></tr>";
		
		  			$myCount++;	
		  		}
		  		$content		.= "</table><br />Records Printed: $myCount<br />";
		  	} else {
		  		if ($doDebug) {
		  			echo "No records found in $studentTableName table<br />";		  			
		  		}
		  		$content		.= "No records found in $studentTableName table<br />";		  			
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
add_shortcode ('display_all_students', 'display_all_students_func');

