function audit_student_record($inp_value='',$doDebug=FALSE) {

/*	Audit Student Record

	inp_value: either the id of a student record or the student's callsign
	Returns: array(TRUE/FALSE,
					errors found);
					
	created: 23Mar2025 by Roland
	
*/

	global $wpdb;
$doDebug = TRUE;	
	if ($doDebug) {
		echo "<br /><b>Audit Student Record</b> inp_value: $inp_value<br />";
	}
	
	$jobname				= "Audit Student Record";
	$initializationArray 	= data_initialization_func();
 	$prevSemester			= $initializationArray['prevSemester'];
	$currentSemester		= $initializationArray['currentSemester'];
	$nextSemester			= $initializationArray['nextSemester'];
	$semesterTwo			= $initializationArray['semesterTwo'];
	$semesterThree			= $initializationArray['semesterThree'];
	$semesterFour			= $initializationArray['semesterFour'];
	$proximateSemester		= $initializationArray['proximateSemester'];
	$pastSemestersArray		= $initializationArray['pastSemestersArray'];
	$issuesFound			= "";	
	$issuesCount			= 0;
	$doProceed				= TRUE;
	
	$studentTableName		= 'wpw1_cwa_student';
	$userMasterTableName	= 'wpw1_cwa_user_master';
	$advisorTableName		= 'wpw1_cwa_advisor';
	$advisorClassTableName	= 'wpw1_cwa_advisorclass';
	
/**
 * Checks if a string has leading or trailing spaces.
 *
 * @param string $string The string to check.
 * @return string An error message if there are leading or trailing spaces,
 * or an empty string if there are none.
 */
function check_for_spaces(string $string): string {
    if (empty($string)) {
        return "ok"; //  Empty string is considered valid in this case
    }

    if (strspn($string, " ") === strlen($string)) {
        return "Error: $string contains only spaces.";
    }

    if ($string[0] === ' ' || $string[strlen($string) - 1] === ' ') {
        return "Error: $string has leading or trailing spaces.";
    }

    return "ok"; // No errors, return an empty string
}
	
	
	
	if ($doDebug) {
		echo "checking input<br />";
	}
	// see if inp_value is an integer or something else
	if ($inp_value == '') {		// no input -- report and return
		$issuesFound	= "<h4>$jobname</h4><ul><li>No student ID or callsign provided</li></ul>\n";
		$issuesCount++;
		goto end;
	}
	$result			= filter_var($inp_value,FILTER_VALIDATE_INT);
	if ($result === FALSE) {	// not an ID. Setup sql for a callsign
		$sql		= "select * from $studentTableName 
						left join $userMasterTableName on user_call_sign = student_call_sign 
						where student_call_sign like '$inp_value' 
						order by student_date_created DESC
						limit 1";
		if ($doDebug) {
			echo "looking up $inp_value as a callsign<br />";
		}

	} else {					// is an integer. Treat as an ID
		$sql		= "select * from $studentTableName 
						left join $userMasterTableName on user_call_sign = student_call_sign 
						where student_id like $inp_value 
						order by student_date_created DESC
						limit 1";
		if ($doDebug) {
			echo "looking up $inp_value as an id<br />";
		}

	}
	$wpw1_cwa_student	= $wpdb->get_results($sql);
	if ($wpw1_cwa_student === FALSE) {
		handleWPDBError($jobname,$doDebug,"attempting to obtain student record for $inp_value");
		$issuesFound	= "<h4>$jobname</h4><ul><li>Unable to read $studentTableName for inp_value $inp_value</li></ul>\n";
		$issuesCount++;
		goto end;
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

				$issuesFound			= "<h4>$jobname ID: $student_ID Name: $student_last_name, $student_first_name Callsign: $student_call_sign</h4><ul>";

				if ($doDebug) {
					echo "checking callsign<br />";
				}

				// there should only be characters or numbers in the callsign, no other characters or spaces
				if (!preg_match("/^[a-zA-Z0-9]+$/", $student_call_sign)) {
					$issuesFound		.= "<li>User Callsign of $student_call_sign contains unallowable characters. User Login likely also</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}

				if ($doDebug) {
					echo "checking name<br />";
				}				
				// checks on student last name
				// No characters other than an apostrophe in the last name
				if (!preg_match("/^[a-zA-Z\s']+$/",$student_last_name)) {
					$issuesFound		.= "<li>User Last Name of $student_last_name contains unallowable characters</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				$result					= check_for_spaces($student_last_name);
				if ($result != 'ok') {
					$issuesFound		.= "<li>$user Last Name $result</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				
				// checks on student first name
				// No characters in the first name
				if (!preg_match("/^[a-zA-Z\s]+$/",$student_last_name)) {
					$issuesFound		.= "<li>User First Name of $student_first_name contains unallowable characters</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				$result					= check_for_spaces($student_first_name);
				if ($result != 'ok') {
					$issuesFound		.= "<li>$user First Name $result</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
			}
			
			// checks on the email address
			if ($doDebug) {
				echo "checking email address<br />";
			}
			// must be present
			if ($student_email == '') {
				$issuesFound			.= "<li>User Email of $student_email missing</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			$result				= check_for_spaces($student_email);
			if ($result != 'ok') {
				$issuesFound	.= "<li>User Email $result</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			// check for valid format
			$result				= filter_var($student_email,FILTER_VALIDATE_EMAIL);
			if ($result === FALSE) {
				$issuesFound	.= "<li>User Email of $student_email has invalid format</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			// check phone number
			if ($doDebug) {
				echo "checking phone code and number<br />";
			}
			// ph_code must be there
			if ($student_ph_code == '') {
				$issuesFound	.= "<li>User ph_code missing</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			// No spaces
			$result				= check_for_spaces($student_ph_code);
			if ($result != 'ok') {
				$issuesFound	.= "<li>User ph_code $result</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			// if country is US, ph_code should be 1
			if ($student_country_code == 'US') {
				if ($student_ph_code != '1') {
					$issuesFound	.= "<li>User country_code is $student_country_code; ph_code is $student_ph_code; should be 1</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				
				}
			}			
			// phone number must be there
			if ($student_phone == '') {
				$issuesFound	.= "<li>User phone is missing</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
						
			// there should not be anything other than numbers in the phone number
			if (!preg_match('/^[0-9]+$/', $student_phone)) {
				$issuesFound	.= "<li>User phone of $student_phone contains non-digit characters</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}

			}
			// the ph_code should not be at the beginning of the phone number
			$myInt		= strlen($student_ph_code);
			$myStr		= substr($student_phone,0,$myInt);
			if ($student_ph_code == $myStr)	{
				$issuesFound	.= "<li>User ph_code of $student_ph_code is also at the beginning of User Phone: $student_phone</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			if ($doDebug) {
				echo "checking zip code<br />";
			}
			
			// if country code is US, then zip code is required
			if ($student_country_code == 'US') {
				if ($student_zip_code == '') {
					$issuesFound	.= "<li>Country code is US. Zip code is missing</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				// if country code is US, zip code should only be only 5 characters long
				if (strlen($student_zip_code) != 5) {
					$issuesFound	.= "<li>Country code is US. Zip code of $student_zip_code is not 5 characters in length</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}	
				// if country code is US, zip code should be only numbers
				if (!preg_match('/^[0-9]+$/', $student_zip_code)) {
					$issuesFound	.= "<li>User zip code of $student_zip_code contains invalid characters</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
			}
			
			if ($doDebug) {
				echo "checking timezone id<br />";
			}
			
			// timezone id must be present
			if ($student_timezone_id == '') {
				$issuesFound	.= "<li>User timezone id is missing</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			// timezone id must not be ??
			if ($student_timezone_id == '??') {
				$issuesFound	.= "<li>User timezone id of $student_timezone_id was not calculated</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			// timezone must be valid
			try {
				$dateTimeZoneLocal = new DateTimeZone($student_timezone_id);
			}
			catch (Exception $e) {
				$issuesFound	.= "<li>User timezone id or $student_timezone_id is invalid</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			if ($doDebug) {
				echo "finished checking user_master fields<br /><br />
						starting student record<br />";
			}
			
			// get the semester dates based on the student_semester
			$semesterDates		= semester_dates($student_semester,$doDebug);
			$semesterStart		= $semesterDates['semesterStart'];
			$catalogAvailable	= $semesterDates['catalogAvailable'];
			$assignDate			= $semesterDates['assignDate'];
			$daysToSemester		= $semesterDates['daysToSemester'];
			
			if ($doDebug) {
				echo "checking timezone offset<br />";
			}
			if ($student_timezone_offset == -99.0) {
				$issuesFound	.= "<li>Student timezone offset of $student_timezone_offset should not be -99.0</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			if ($doDebug) {
				echo "checking youth<br />";
			}
			if ($student_youth == 'Yes') {
				// age must be there
				if ($student_age == '') {
					$issuesFound	.= "<li>Student youth is $student_youth but no age is specified</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				} else {
					$myInt		= intval($student_age);
					if ($myInt == 0) {
						$issuesFound	.= "<li>Student age of $student_age has invalid characters</li>\n";
						$issuesCount++;
						if ($doDebug) {
							echo "issue found<br />";
						}
					} else {
						if ($myInt > 21) {
							$issuesFound	.= "<li>Student age of $student_age is greater than 21. Is not a youth</li>\n";
							$issuesCount++;
							if ($doDebug) {
								echo "issue found<br />";
							}
						} else {
							if ($student_parent_email == '') {
								$issuesFound	.= "<li>Student age or $student_age is less than 21 but no parent email available</li>\n";
								$issuesCount++;
								if ($doDebug) {
									echo "issue found<br />";
								}
							}
						}
					}
				}
			} else {
				if ($student_youth != 'No' && $student_youth != '') {
					$issuesFound	.= "<li>Student age of $student_age is invalid. Must be Yes, No, or blank</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}

				}
			}
			
			if ($doDebug) {
				echo "checking level<br />";
			}
			$levelArray			= array('Beginner',
										'Fundamental',
										'Intermediate',
										'Advanced');
			if (!in_array($student_level,$levelArray)) {
				$issuesFound	.= "<li>Student level or $student_level is invalid</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			if ($doDebug) {
				echo "checking waiting list<br />";
			}
			if ($student_waiting_list != 'Y' && $student_waiting_list != 'N' && $student_waiting_list != '') {
				$issuesFound	.= "<li></li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			if ($doDebug) {
				echo "checking request date<br />";
			}
			// should be a date
			if ($student_request_date == '') {
				$issuesFound	.= "<li>Student request date is missing</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			if ($doDebug) {
				echo "checking welcome date<br />";
			}
			// should be a date
			if ($student_welcome_date == '') {
				$issuesFound	.= "<li>Student welcome date is missing</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			if ($doDebug) {
				echo "checking email number and dates<br />";
			}
			// if email number > 0 then should be an email sent date
			if ($student_email_number > 0) {
				if ($student_email_sent_date == '') {
					$issuesFound	.= "<li>\Student email number > 0. Student email sent date missing</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
			}
			
			// email number between 0-4
			if ($student_email_number > 4) {
				$issuesFound	.= "<li>Student email number of $student_email_number is not valid</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
		
			if ($doDebug) {
				echo "checking response<br />";
			}
			// if more than 50 days to the semester, response should not be Y
			if ($daysToSemester > 49) {
				if ($student_response == 'Y') {
					$issuesFound	.= "<li>$daysToSemester days to $student_semester. Student response of $student_response is invalid</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				} 
			} else {
				// response should be blank, Y, or R
				if ($student_response != '' && $student_response != 'Y' && $student_response != 'R') {
					$issuesFound	.= "<li>Student response of $student_response is invalid. Valid codes are blank, Y, or R</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				// if response is Y or R, there should be a student response date
				if ($student_response != '') {
					if ($student_response_date == '') {
						$issuesFound	.= "<li>Student response is not blank. Student response date is missing</li>\n";
						$issuesCount++;
						if ($doDebug) {
							echo "issue found<br />";
						}
					}
				}
			}
			
			if ($doDebug) {
				echo "Checking abandoned<br />";
			}
			// should be Y, N, or blank
			if ($student_abandoned != 'Y' && $student_abandoned != 'N' && $student_abandoned != '') {
				$issuesFound	.= "<li>Student abandoned of $student_abandoned is invalid. Valid entries are Y, N, or blank</li>\n";
				$issuesCount++;
				if ($doDebug) {
					echo "issue found<br />";
				}
			}
			
			// if abandoned is Y, then preferences should be None, response should not be Y and status should be blank
			if ($student_abandoned == 'Y') {
				if ($student_response == 'Y') {
					$issuesFound	.= "<li>Agandoned is Y. Student response should not be Y</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				if ($student_status != '') {
					$issuesFound	.= "<li>Agandoned is Y. Student status should be blank, not $student_status</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				if ($student_first_class_choice != 'None') {
					$issuesFound	.= "<li>Abandoned is Y. Student first class choice should be None but is $student_first_class_choice</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				if ($student_second_class_choice != 'None') {
					$issuesFound	.= "<li>Abandoned is Y. Student second class choice should be None but is $student_second_class_choice</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				if ($student_third_class_choice != 'None') {
					$issuesFound	.= "<li>Abandoned is Y. Student third class choice should be None but is $student_third_class_choice</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				if ($student_first_class_choice_utc != 'None') {
					$issuesFound	.= "<li>Abandoned is Y. Student first class choice utc should be None but is $student_first_class_choice_utc</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				if ($student_second_class_choice_utc != 'None') {
					$issuesFound	.= "<li>Abandoned is Y. Student second class choice utc should be None but is $student_second_class_choice_utc</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				if ($student_third_class_choice_utc != 'None') {
					$issuesFound	.= "<li>Abandoned is Y. Student third class choice utc should be None but is $student_third_class_choice_utc</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
			}
			
			if ($doDebug) {
				echo "checking student status<br />";
			}
			
			$validStatus		= array('C','R','S','V','Y');
			if ($student_status != '') {
				if (!in_array($student_status,$validStatus)) {
					$issuesFound	.= "<li>Student status of $student_status is invalid. Valid codes are C, R, S, V, Y</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
			}
			if ($student_status == 'C') {
				// should be no assigned advisor and no assigned advisor class
				if ($student_assigned_advisor != '') {
					$issuesFound	.= "<li>Student status is C. Student assigned advisor of $student_assigned_advisor invalid. Should be blank</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				if ($student_assigned_advisor_class != 0) {
					$issuesFound	.= "<li>Student status is C. Student assigned advisor class of $student_assigned_advisor_class invalid. Should be 0</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
			}
			if ($student_status == 'R' || $student_status == 'S' || $student_status == 'V') {
				// should have an assigned advisor and assigned advisor class
				
				if ($student_assigned_advisor == '') {
					$issuesFound	.= "<li>Student status is $student_status. Student assigned advisor is invalid. Should not be blank</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
				if ($student_assigned_advisor_class == 0) {
					$issuesFound	.= "<li>Student status is $student_status. Student assigned advisor class is invalid. Should not be 0</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}
			}
			
			if ($doDebug) {
				echo "checking pre-assigned advisor and assigned_advisor_class<br />";
			}
			// if pre-assigned advisor is not blank, there should be an advisor record
			if ($student_pre_assigned_advisor != '') {
				$myInt		= $wpdb->get_var("select count($advisor_call_sign) from $advisorTableName 
												where advisor_call_sign = $student_pre_advisor 
												and advisor_semester = '$student_semester'");
				if ($myInt === FALSE) {
					handleWPDBError($jobname,$doDebug,"validating pre-assigned advisor");
					$issuesFound	.= "<li>Unable to read $advisorTableName table for $student_pre_assigned_advisor</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}

				}	
				$myInt		= $wpdb->get_var("select count($advisorclass_call_sign) from $advisorClassTableName 
												where advisorclass_call_sign = $student_pre_advisor 
												and advisorclass_semester = '$student_semester'");
				if ($myInt === FALSE) {
					handleWPDBError($jobname,$doDebug,"validating pre-assigned advisor class");
					$issuesFound	.= "<li>Unable to read $advisorClassTableName table for $student_pre_assigned_advisor</li>\n";
					$issuesCount++;
					if ($doDebug) {
						echo "issue found<br />";
					}
				}	
			}
			
			
			
			if ($doDebug) {
				echo "checking no catalog<br />";
			}
			// no catalog should be blank, Y, or N
			
			// if no catalog is Y then the response should not be Y
			
			
			
			
			
		} else {				// no record found
			$issuesFound		= "<h4>$jobname</h4><ul><li>No record found for $inp_value</li></ul>";
			$issuesCount++;
		}
	}
	end:
	if ($issuesCount != 0) {
		$returnValue			= array(FALSE,$issuesFound);
	} else {
		$returnValue			= array(TRUE,"No Issues Found");
	}
	if ($doDebug) {
		echo "Returning from $jobname:<br /><pre>";
		print_r($returnValue);
		echo "</pre></br /><br />";
	}
	return $returnValue;

}
add_action('audit_student_record','audit_student_record');
