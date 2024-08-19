function student_evaluation_of_advisor_func() {

/*

	Modified 15Jul23 by Roland to use consolidated tables
	Modified 3Mar24 by Roland to be started from a reminder

*/

	global $wpdb;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$initializationArray 		= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName					= $initializationArray['userName'];
	$validTestmode				= $initializationArray['validTestmode'];
	$siteURL					= $initializationArray['siteurl'];

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

td {padding:5px;font-size:small;border-bottom:1px solid black;}

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
		$studentTableName			= "wpw1_cwa_consolidated_student2";
		$evaluateAdvisorTableName	= "wpw1_cwa_evaluate_advisor2";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
	} else {
		$studentTableName			= "wpw1_cwa_consolidated_student";
		$evaluateAdvisorTableName	= "wpw1_cwa_evaluate_advisor";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		if ($validUser == "N") {
			return "YOU'RE NOT AUTHORIZED!<br />Goodby";
		} else {
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
		}


///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at $strPass pass with inp_student: $inp_student and token: $token<br />";
		}
		// get the student record from student
		$currentSemester	= $initializationArray['currentSemester'];
		$prevSemester		= $initializationArray['prevSemester'];
		if ($currentSemester == 'Not in Session') {
			$currentSemester	= $prevSemester;
		}
		if ($doDebug) {
			echo "using semester $currentSemester<br />";
		}
		if ($userName == '') {
			$userName		= $inp_student;
		}
		
		$sql				= "select * from $studentTableName 
								where call_sign='$inp_student' 
								and semester='$currentSemester'";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpdb->last_error != '') {
			$myStr			= $wpdb->last_error;
			sendErrorMessage("student_evaluation_ofadvisor reading $studentTableName for $inp_student yielded error $myStr");
		}
		if ($doDebug) {
			echo "Reading $studentTableName table<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			if ($wpdb->last_error != '') {
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		}
		if ($wpw1_cwa_student !== FALSE) {
			$numPSRows									= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
			}
			if ($numPSRows > 0) {
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
					$student_student_status  				= $studentRow->student_status;
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog		  				= $studentRow->no_catalog;
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
					$student_student_survey_completion_date = $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->available_class_days;
					$student_intervention_required  		= $studentRow->intervention_required;
					$student_copy_control  					= $studentRow->copy_control;
					$student_first_class_choice  			= $studentRow->first_class_choice;
					$student_second_class_choice  			= $studentRow->second_class_choice;
					$student_third_class_choice  			= $studentRow->third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$student_last_name 						= no_magic_quotes($student_last_name);
					$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
				
					if ($student_advisor_class_timezone == '') {
						$student_advisor_class_timezone 	= $student_time_zone;
					}

					if ($doDebug) {
						echo "Processing student $student_last_name, $student_first_name ($student_call_sign)<br />
							  &nbsp;&nbsp;&nbsp;Semester: $student_semester<br />
							  &nbsp;&nbsp;&nbsp;Time Zone: $student_time_zone<br />
							  &nbsp;&nbsp;&nbsp;Level: $student_level<br />
							  &nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor<br />
							  &nbsp;&nbsp;&nbsp;Advisor Class Time Zone: $student_advisor_class_timezone<br />
							  &nbsp;&nbsp;&nbsp;Assigned Advisor Class: $student_assigned_advisor_class<br />";
					}
				}
				
				// if student is a beginner, mark some fields as not applicable
				$beginner_checked			= "";
				if ($student_level == 'Beginner') {
					$beginner_checked		= " checked ";
				}
				
				// Now get the advisor's information
				$sql			= "select * from $advisorTableName 
									where call_sign='$student_assigned_advisor' 
									and semester='$currentSemester'";
				$wpw1_cwa_advisor			= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisor == FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numPARows		= $wpdb->num_rows;
					if ($doDebug) { 
						$myStr		= $wpdb->last_query;
						echo "ran $myStr<br />and found $numPARows rows in $advisorTableName<br />";
					}
					if ($numPARows > 0) {
						foreach ($wpw1_cwa_advisor as $advisorRow) {
							$advisor_ID						= $advisorRow->advisor_id;
							$advisor_call_sign 				= strtoupper($advisorRow->call_sign);
							$advisor_first_name 			= $advisorRow->first_name;
							$advisor_last_name 				= stripslashes($advisorRow->last_name);
					
							$gotAdvisor							= TRUE;
						}
						if ($gotAdvisor) {
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
												<td><input type='text' name='inp_call_sign' class='formInputText' size='10' maxlength='10' value='$student_call_sign'><br />
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
												<td style='vertical-align:top;'>Were the QSO's effective for you?</td>
												<td><input type='radio' name='inp_short_stories' class='formInputButton' value='Very Much'> Very Much<br />
													<input type='radio' name='inp_short_stories' class='formInputButton' value='Mostly'> Mostly<br />
													<input type='radio' name='inp_short_stories' class='formInputButton' value='Somewhat'> Somewhat<br />
													<input type='radio' name='inp_short_stories' class='formInputButton' value='Not Really'> Not Really<br />
													<input type='radio' name='inp_short_stories' class='formInputButton' value='Not Applicable' $beginner_checked> Not Applicable</td>
											</tr><tr>
												<td style='vertical-align:top;'>Were the Short Stories effective for you?</td>
												<td><input type='radio' name='inp_qsos' class='formInputButton' value='Very Much'> Very Much<br />
													<input type='radio' name='inp_qsos' class='formInputButton' value='Mostly'> Mostly<br />
													<input type='radio' name='inp_qsos' class='formInputButton' value='Somewhat'> Somewhat<br />
													<input type='radio' name='inp_qsos' class='formInputButton' value='Not Really'> Not Really<br />
													<input type='radio' name='inp_qsos' class='formInputButton' value='Not Applicable' $beginner_checked> Not Applicable</td>
											</tr><tr>
												<td style='vertical-align:top;'>Were the weekly CWT's effective for you?</td>
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
						} else {
							if ($doDebug) {
								echo "posting no advisor found message <br />";
							}
							$content	.= "<p>Fatal programming error. Assigned advisor record not found.</p>";
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "Student record not found in $studentTableName. Posting error message<br />";
				}
				$content				.= "<p>No record found for $inp_student</p>";
			}
		} else {
			if ($doDebug) {
				echo "Either $studentTableName not found or bad $sql 01<br />";
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
