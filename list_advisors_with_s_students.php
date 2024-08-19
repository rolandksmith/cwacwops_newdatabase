function list_advisors_with_s_students_func() {

/* 
 * Presents a list of advisors where any of their students
 * have a status of S
 *
 * This function is useful at the beginning of a semester
 *
 *
 	Modified 25Oct22 by Roland for the new timezone table format
 	Modified 13Jul23 by Roland to use consolidated tables
 	Modified 23Aug23 by Roland to list advisors with any S students
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser			= $initializationArray['validUser'];
	$userName  			= $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$versionNumber		= '1';

	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$advisorsDue				= 0;
	$myCount					= 0;
	$theURL						= "$siteURL/cwa-list-advisors-with-s-students/";
	$jobname					= "List Advisors With S Students V$versionNumber";

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
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
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


	if ($testMode) {
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass2';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor2';
		$studentTableName			= 'wpw1_cwa_consolidated_student2';
		$content						.= "<p><b>Operating in test mode</b></p>";
	} else {
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor';
		$studentTableName			= 'wpw1_cwa_consolidated_student';
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		
			$content 		.= "<h3>List Advisors with Any S Stuents</h3>
								<p>The function cycles through all advisor classes for the current semester 
								and generates a list of 
								advisors who have students with a status of S.</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								<table style='border-collapse:collapse;'>
								$testModeOption
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form></p>";


/////// Pass 2

	} elseif ("2" == $strPass) {
	
		$currentSemester		= $initializationArray['currentSemester'];
		$nextSemester			= $initializationArray['nextSemester'];
		$theSemester			= $currentSemester;
		if ($currentSemester == 'Not in Session') {
			$theSemester		= $nextSemester;
		}
		if ($doDebug) {
			echo "set theSemester to $theSemester<br />";
		}
		$doProceed				= TRUE;
		$prevAdvisor			= '';
		$firstTime				= TRUE;
		$advisorCount			= 0;
		$content				.= "<h3>Advisors with S Students in $theSemester Semester</h3>
									<table style='width:1000px;'>
									<tr><th>Advisor</th>
										<th>Class</th>
										<th>Level</th>
										<th>Advisor Email</th>
										<th>Student</th>
										<th>Student Email</th>
									</tr>";
		
		$sql					= "select * from $studentTableName 
									where semester = '$theSemester' 
									and student_status = 'S' 
									order by assigned_advisor, 
									assigned_advisor_class, 
									call_sign";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $studentTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
			$content		.= "Unable to obtain content from $studentTableName<br />";
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_phone  						= $studentRow->phone;
					$student_ph_code						= $studentRow->ph_code;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_country_code					= $studentRow->country_code;
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
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog			 			= $studentRow->no_catalog;
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
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
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
					
					if ($doDebug) {
						echo "<br />new student: $student_call_sign advisor: $student_assigned_advisor class: $student_assigned_advisor_class<br />";
					}
					
					if ($student_assigned_advisor != $prevAdvisor) {
						if ($doDebug) {
							echo "Got a new advisor $student_assigned_advisor<br >";
						}
						if ($firstTime) {
							$firstTime			= FALSE;
						} else {
							$content			.= "<tr><td colspan='6'></td></tr>";
						}
						$prevAdvisor			= $student_assigned_advisor;
						$advisorCount++;
					}

					// get the advisor information
					$sql		= "select * from $advisorTableName 
									where call_sign = '$student_assigned_advisor' 
									and semester = '$theSemester'";
					$wpw1_cwa_advisor	= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisor === FALSE) {
						$myError			= $wpdb->last_error;
						$myQuery			= $wpdb->last_query;
						if ($doDebug) {
							echo "Reading $advisorNewTableName table failed<br />
								  wpdb->last_query: $myQuery<br />
								  wpdb->last_error: $myError<br />";
						}
						$errorMsg			= "$jobname Reading $advisorNewTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
						sendErrorEmail($errorMsg);
						$content		.= "Unable to obtain content from $advisorNewTableName<br />";
					} else {
						$numARows			= $wpdb->num_rows;
						if ($doDebug) {
							$myStr			= $wpdb->last_query;
							echo "ran $myStr<br />and found $numARows rows in $advisorNewTableName table<br />";
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

								$content			.= "<tr><td style='vertical-align:top;'>$advisor_call_sign $advisor_last_name, $advisor_first_name</td>
															<td style='vertical-align:top;text-align:center;'>$student_assigned_advisor_class</td>
															<td style='vertical-align:top;'>$student_level</td>
															<td style='vertical-align:top;'>$advisor_email</td>
															<td style='vertical-align:top;'>$student_last_name, $student_first_name ($student_call_sign)</td>
															<td style='vertical-align:top;'>$student_email</td></tr>";
								
							}
						} 
					}
				}
			} else {
				$content				.= "<tr><td colspan='6'>No Students Found</td></tr>";
			}
		}
		$content						.= "</table>
											<p>$numSRows Students with status of S<br />
											$advisorCount Advisors with S students</p>";
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
add_shortcode ('list_advisors_with_s_students', 'list_advisors_with_s_students_func');

