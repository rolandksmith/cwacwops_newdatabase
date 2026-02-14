function student_evaluation_of_advisor_func() {

	global $wpdb;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 						= $context->validUser;
	$userName					= $context->userName;
	$validTestmode				= $context->validTestmode;
	$siteURL					= $context->siteurl;
	$currentSemester			= $context->currentSemester;
	$prevSemester				= $context->prevSemester;
	if ($currentSemester == 'Not in Session') {
		$currentSemester		= $prevSemester;
	}

	if ($userName == '') {
		return "You are not authorized";
	}

	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$inp_id					 	= '';
	$inp_student				= '';
	$inp_advisor_name			= '';
	$inp_advisor_id				= '';
	$inp_survey_id				= '';
	$inp_class					= '';
	$inp_anonymous				= '';
	$inp_advisor_call_sign		= '';
	$inp_level					= '';
	$inp_effective				= '';
	$inp_expectations			= '';
	$inp_curriculum				= '';
	$inp_scales					= '';
	$inp_morse_trainer			= '';
	$inp_rufzxp					= '';
	$inp_morse_runner			= '';
	$inp_numorse_pro			= '';
	$inp_short_stories			= '';
	$inp_qsos					= '';
	$inp_cwt					= '';
	$inp_enjoy_class			= '';
	$inp_comments				= '';
	$inp_semester				= '';
	$inp_lcwo					= '';
	$token						= '';
	$extMode					= '';
	$theURL						= "$siteURL/cwa-student-evaluation-of-advisor/";
	$jobname					= "Student Evaluation of Advisor";
	
	
	
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
			if ($str_key 		== "inp_student") {
				$inp_student	 = strtoupper($str_value);
				$inp_student	 = filter_var($inp_student,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_advisor_name') {
				$inp_advisor_name		= $str_value;
				$inp_advisor_name		= filter_var($inp_advisor_name,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token			 = $str_value;
				$token			 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_advisor_id') {
				$inp_advisor_id		= $str_value;
				$inp_advisor_id		= filter_var($inp_advisor_id,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_inp_id') {
				$inp_inp_id		= $str_value;
				$inp_inp_id		= filter_var($inp_inp_id,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_student') {
				$inp_student		= $str_value;
				$inp_student		= filter_var($inp_student,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_advisor') {
				$inp_advisor		= $str_value;
				$inp_advisor		= filter_var($inp_advisor,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_class') {
				$inp_class		= $str_value;
				$inp_class		= filter_var($inp_class,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_level') {
				$inp_level		= $str_value;
				$inp_level		= filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_class_hours') {
				$inp_class_hours		= $str_value;
				$inp_class_hours		= filter_var($inp_class_hours,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_effective') {
				$inp_effective		= $str_value;
				$inp_effective		= filter_var($inp_effective,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_expectations') {
				$inp_expectations		= $str_value;
				$inp_expectations		= filter_var($inp_expectations,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_curriculum') {
				$inp_curriculum		= $str_value;
				$inp_curriculum		= filter_var($inp_curriculum,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_scales') {
				$inp_scales		= $str_value;
				$inp_scales		= filter_var($inp_scales,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_morse_trainer') {
				$inp_morse_trainer		= $str_value;
				$inp_morse_trainer		= filter_var($inp_morse_trainer,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_rufzxp') {
				$inp_rufzxp				= $str_value;
				$inp_rufzxp				= filter_var($inp_rufzxp,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_morse_runner') {
				$inp_morse_runner		= $str_value;
				$inp_morse_runner		= filter_var($inp_morse_runner,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_numorse_pro') {
				$inp_numorse_pro		= $str_value;
				$inp_numorse_pro		= filter_var($inp_numorse_pro,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_lcwo') {
				$inp_lcwo		= $str_value;
				$inp_lcwo		= filter_var($inp_lcwo,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_qsos') {
				$inp_qsos		= $str_value;
				$inp_qsos		= filter_var($inp_qsos,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_short_stories') {
				$inp_short_stories		= $str_value;
				$inp_short_stories		= filter_var($inp_short_stories,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_cwt') {
				$inp_cwt		= $str_value;
				$inp_cwt		= filter_var($inp_cwt,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_applications') {
				$inp_applications		= $str_value;
				$inp_applications		= filter_var($inp_applications,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_enjoy_class') {
				$inp_enjoy_class		= $str_value;
				$inp_enjoy_class		= filter_var($inp_enjoy_class,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_comments') {
				$inp_comments		= $str_value;
				$inp_comments		= filter_var($inp_comments,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_semester') {
				$inp_semester		= $str_value;
				$inp_semester		= filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'inp_timezone') {
				$inp_timezone		= $str_value;
				$inp_timezone		= filter_var($inp_timezone,FILTER_UNSAFE_RAW);
			}
			if($str_key == 'extMode') {
				$extMode		= $str_value;
				$extMode		= filter_var($extMode,FILTER_UNSAFE_RAW);
				if ($extMode == 'tm') {
					$testMode	= TRUE;
				}
			}
		}
	}
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$studentTableName			= "wpw1_cwa_student2";
		$evaluateAdvisorTableName	= "wpw1_cwa_evaluate_advisor2";
		$advisorTableName			= "wpw1_cwa_advisor2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$studentTableName			= "wpw1_cwa_student";
		$evaluateAdvisorTableName	= "wpw1_cwa_evaluate_advisor";
		$advisorTableName			= "wpw1_cwa_advisor";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting.<br />";
		}
		$content 		.= "<h3>$jobname</h3>
							<p>This function is normally run through a link in an email sent to the student 
							asking the student to evaluation their class and advisor.</p>
							<p>Click Submit to Start the Process</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							Student Call Sign: <input type='text' class='formInputText' name='inp_student' size='10' maxlength='10'><br />
							<input class='formInputButton' type='submit' value='Submit' />
							</form></p>";


///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at $strPass pass with inp_student: $inp_student and token: $token<br />";
		}
		// get the student record from student
		if ($doDebug) {
			echo "using semester $currentSemester<br />";
		}
		if ($userName == '') {
			$userName		= $inp_student;
		}
		
		$sql				= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_call_sign='$inp_student' 
								and student_semester='$currentSemester'";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content		.= "The program was not able to retrieve the student's information. The Sys Admin has been notified.";
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
					$student_ph_code 					= $studentRow->user_ph_code;
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
					$student_class_language					= $studentRow->student_class_language;
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
						echo "Processing student $student_last_name, $student_first_name ($student_call_sign)<br />
							  &nbsp;&nbsp;&nbsp;Semester: $student_semester<br />
							  &nbsp;&nbsp;&nbsp;Level: $student_level<br />
							  &nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor<br />
							  &nbsp;&nbsp;&nbsp;Advisor Class Time Zone: $student_advisor_class_timezone<br />
							  &nbsp;&nbsp;&nbsp;Assigned Advisor Class: $student_assigned_advisor_class<br />";
					}
					// if student is a beginner, mark some fields as not applicable
					$beginner_checked			= "";
					if ($student_level == 'Beginner') {
						$beginner_checked		= " checked ";
					}
					
					// Now get the advisor's information
					$advisorSQL			= "select * from $advisorTableName 
											left join $userMasterTableName on user_call_sign = advisor_call_sign 
											where advisor_call_sign = '$student_assigned_advisor' 
											and advisor_semester = '$student_semester'";
					$wpw1_cwa_advisor	= $wpdb->get_results($advisorSQL);
					if ($wpw1_cwa_advisor === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numARows			= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
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
								$advisor_country 					= $advisorRow->user_country;
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
			
								if ($doDebug) {
									echo "Got student and advisor. Now do the evaluation form<br />";
								}
								$content	.= "<h3>Student Evaluation of the CW Academy $student_level Class and Advisor $student_assigned_advisor</h3>
												<p>Thank you for participating in the CW Academy $student_level class with advisor $student_assigned_advisor. Please take a 
												few minutes to fill out the following evaluation form. Your input will help guide the Academy in the future.</p>
												<p><form mmethod='post' action='$theURL' 
												name='selection_form' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='3'>
												<input type='hidden' name='extMode' value='$extMode'>
												<input type='hidden' name='inp_id' value='$student_ID'>
												<input type='hidden' name='inp_advisor_id' value='$advisor_ID'>
												<input type='hidden' name='inp_advisor_name' value='$advisor_last_name, $advisor_first_name'>
												<input type='hidden' name='inp_student' value='$inp_student'>
												<input type='hidden' name='token' value='$token'>
												<input type='hidden' name='inp_semester' value='$currentSemester'>
												<input type='hidden' name='inp_advisor' value='$advisor_call_sign '>
												<input type='hidden' name='inp_level' value='$student_level'>
												<input type='hidden' name='inp_class' value='$student_assigned_advisor_class'>
												<table><tr><th colspan='2'>General Information</th></tr>
												<tr>
													<td style='vertical-align:top;width:250px;'>Your Call Sign</td>
													<td><input type='text' name='inp_callsign' class='formInputText' size='10' maxlength='10' value='$student_call_sign'><br />
													<i>To be anonymous, erase your call sign</i></td>
												</tr><tr>
													<td style='vertical-align:top;'>Advisor's Name and Call Sign</td>
													<td>$advisor_last_name, $advisor_first_name ($advisor_call_sign)</td>
												</tr><tr>
													<td style='vertical-align:top;'>Class Level</td>
													<td>$student_level</td>
												</tr><tr>
													<td style='vertical-align:top;'>Semester</td>
													<td>$student_semester</td>
												</tr><tr>
													<th colspan='2'>Survey Questions</th>
												</tr><tr>
													<th>Question</th>
													<th>Answer</th>
												</tr><tr>
													<td style='vertical-align:top;'>Was your advisor capable and effective?</td>
													<td><input type='radio' name='inp_effective' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_effective' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_effective' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_effective' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_effective' class='formInputButton' value='Not Applicable'> Not Applicable</td>
												</tr><tr>
													<td style='vertical-align:top;'>Were your expectations met?</td>
													<td><input type='radio' name='inp_expectations' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_expectations' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_expectations' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_expectations' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_expectations' class='formInputButton' value='Not Applicable'> Not Applicable</td>
												</tr><tr>
													<td style='vertical-align:top;'>Was the curriculum effective for you?</td>
													<td><input type='radio' name='inp_curriculum' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_curriculum' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_curriculum' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_curriculum' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_curriculum' class='formInputButton' value='Not Applicable'> Not Applicable</td>
												</tr><tr>
													<td style='vertical-align:top;'>Were scales effective for you?</td>
													<td><input type='radio' name='inp_scales' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_scales' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_scales' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_scales' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_scales' class='formInputButton' value='Not Applicable'> Not Applicable</td>
												</tr><tr>
													<td style='vertical-align:top;'>Was Morse Code Trainer, Word List, or ICR effective for you?</td>
													<td><input type='radio' name='inp_morse_trainer' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_morse_trainer' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_morse_trainer' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_morse_trainer' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_morse_trainer' class='formInputButton' value='Not Applicable' $beginner_checked> Not Applicable</td>
												</tr><tr>
													<td style='vertical-align:top;'>Was Morse Runner effective for you?</td>
													<td><input type='radio' name='inp_morse_runner' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_morse_runner' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_morse_runner' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_morse_runner' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_morse_runner' class='formInputButton' value='Not Applicable' $beginner_checked> Not Applicable</td>
												</tr><tr>
													<td style='vertical-align:top;'>Was LCWO (Learn CW Online) effective for you?</td>
													<td><input type='radio' name='inp_lcwo' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_lcwo' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_lcwo' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_lcwo' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_lcwo' class='formInputButton' value='Not Applicable' $beginner_checked> Not Applicable</td>
												</tr><tr>
													<td style='vertical-align:top;'>Was RufzXP effective for you?</td>
													<td><input type='radio' name='inp_rufzxp' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_rufzxp' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_rufzxp' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_rufzxp' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_rufzxp' class='formInputButton' value='Not Applicable' $beginner_checked> Not Applicable</td>
												</tr><tr>
													<td style='vertical-align:top;'>Were the Short Files (Words, Phrases, QSOs, POTA) effective for you?</td>
													<td><input type='radio' name='inp_short_stories' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_short_stories' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_short_stories' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_short_stories' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_short_stories' class='formInputButton' value='Not Applicable' $beginner_checked> Not Applicable</td>
												</tr><tr>
													
													<td style='vertical-align:top;'>Were the Prefix and Suffix files effective for you?</td>
													<td><input type='radio' name='inp_qsos' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_qsos' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_qsos' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_qsos' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_qsos' class='formInputButton' value='Not Applicable' $beginner_checked> Not Applicable</td>
												</tr><tr>
													<td style='vertical-align:top;'>Were the weekly MST, SST, and/or CWTs effective for you?</td>
													<td><input type='radio' name='inp_cwt' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_cwt' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_cwt' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_cwt' class='formInputButton' value='Not Really'> Not Really<br />
														<input type='radio' name='inp_cwt' class='formInputButton' value='Not Applicable' $beginner_checked> Not Applicable</td>
												</tr><tr>
													<td>Were there any other applications, either online or on your smartphone, that were effective for you?</td>
													<td><textarea name='inp_applications' class='formInputText' rows='5' cols=50'></textarea></td>
												</tr><tr>
													<td style='vertical-align:top;'>Did you enjoy the class?</td>
													<td><input type='radio' name='inp_enjoy_class' class='formInputButton' value='Very Much'> Very Much<br />
														<input type='radio' name='inp_enjoy_class' class='formInputButton' value='Mostly'> Mostly<br />
														<input type='radio' name='inp_enjoy_class' class='formInputButton' value='Somewhat'> Somewhat<br />
														<input type='radio' name='inp_enjoy_class' class='formInputButton' value='Not Really'> Not Really</td>
												</tr><tr>
													<td>Do you have any comments, positive and/or negative about the class?</td>
													<td><textarea name='inp_comments' class='formInputText' rows='5' cols=50'></textarea></td>
												</tr>
												</table>
												<input type='submit' value='Submit' class='formInputButton'>
												</form></p>";
							}
						} else {
							if ($doDebug) {
								echo "posting no advisor found message <br />";
							}
							$content	.= "<p>Fatal programming error. Assigned advisor record not found.</p>";
							sendErrorEmail("$jobname Fatal programming error. Assigned advisor record not found");
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "Student record not found in $studentTableName. Posting error message<br />";
				}
				$content				.= "<p>No record found for $inp_student</p>";
			}
		}
		
		
		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "at pass $strPass with inp_student: $inp_student and token: $token<br />";
		}
//		if ($userName == '') {
			$userName	= $inp_student;
//		}
		
		$updateParams 	= array("survey_id|$inp_id|s",
								"anonymous|$inp_student|s",
								"advisor_callsign|$inp_advisor|s",
								"level|$inp_level|s",
								"effective|$inp_effective|s",
								"expectations|$inp_expectations|s",
								"curriculum|$inp_curriculum|s",
								"scales|$inp_scales|s",
								"morse_trainer|$inp_morse_trainer|s",
								"rufzxp|$inp_rufzxp|s",
								"morse_runner|$inp_morse_runner|s",
								"lcwo|$inp_lcwo|s",
								"applications|$inp_applications|s",
								"qsos|$inp_qsos|s",
								"short_stories|$inp_short_stories|s",
								"cwt|$inp_cwt|s",
								"enjoy_class|$inp_enjoy_class|s",
								"student_comments|$inp_comments|s",
								"advisor_semester|$inp_semester|s",
								"advisor_class|$inp_class|d",
								"survey_id|$inp_id|d",
								"level|$inp_level|s");

		$fieldArray					= array();
		$formatArray				= array();
		foreach($updateParams as $myValue) {
			$myArray				= explode("|",$myValue);
			$fieldName				= $myArray[0];
			$fieldValue				= $myArray[1];
			$fieldFormat			= $myArray[2];
			$fieldArray[$fieldName]	= $fieldValue;
			if ($fieldFormat == 's') {
				$formatArray[]		= '%s';
			} else {
				$formatArray[]		= '%d';
			}
		}
		$result						= $wpdb->insert($evaluateAdvisorTableName,
													$fieldArray,
													$formatArray);
		if ($result === FALSE) {
			if ($wpdb->last_error != '') {
				$myStr			= $wpdb->last_error;
				sendErrorMessage("student_evaluation_ofadvisor writing $evaluateAdvisorTableName for $inp_student yielded error $myStr");
			}
			if ($doDebug) {
					echo "Inserting $evaluateAdvisorTableName record failed<br />
							Result: $result<br />
							wpdb->last_query: " . $wpdb->last_query . "<br />";
				if ($wpdb->last_error != '') {
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			}
		} else {
			if ($doDebug) {
				echo "Successfully inserted $evaluateAdvisorTableName record<br />
						Result: $result<br />
						wpdb->last_query: " . $wpdb->last_query . "<br />";
				if ($wpdb->last_error != '') {
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			}
			// resolve the reminder, if there is one
			if ($token != '') {
				$resolveResult				= resolve_reminder($inp_student,$token,$testMode,$doDebug);
				if ($resolveResult === FALSE) {
					if ($doDebug) {
						echo "resolve_reminder for $inp_callsign and $token failed<br />";
					}
				} else {
					if ($doDebug) {
						echo "reminder has been resolved<br />";
					}
				}
			}
			

			$content			.= "<h3>Student Evaluation Has Been Accepted</h3>
									<p>Your evaluation information has been saved. A week or so after the 
									end of your class the evaluation data will be shared 
									with your advisor.</p>";
			if ($inp_level == 'Intermediate' || $inp_level == 'Advanced') {
				$content			.= "<p>If you aren't planning to take a class next semester, please consider 
										becoming an advisor. Sign up on CWops.org and if you have any questions, 
										contact Bob Carter WR7Q at: kcgator@gmail.com</p> ";
			}
			$content	.= "<p>Thank you for participating this past semester!<br /><br />
							73 and hope to hear you on the bands!<br />
							CW Academy</p>";
		}
	}
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('student_evaluation_of_advisor', 'student_evaluation_of_advisor_func');

