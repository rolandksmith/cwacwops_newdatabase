function list_advisors_with_all_s_students_func() {

/* List Advisors with Incomplete Evaluations
 *
 * Presents a list of advisors where all their students
 * have a status of S
 *
 * This function is useful at the beginning of a semester
 *
 *
 	Modified 25Oct22 by Roland for the new timezone table format
 	Modified 13Jul23 by Roland to use consolidated tables
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
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];

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
	$theURL						= "$siteURL/cwa-list-advisors-with-all-s-students/";

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
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
		$studentTableName			= 'wpw1_cwa_consolidated_student2';
		$content						.= "<p><b>Operating in test mode</b></p>";
	} else {
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
		$studentTableName			= 'wpw1_cwa_consolidated_student';
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		
			$content 		.= "<h3>List Advisors with All S Stuents</h3>
								<p>The function cycles through all advisor classes for the current semester and generates a list of 
								advisors whose students all have a status of S.</p>
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
		
		$sql					= "select 
										a.call_sign,
										a.first_name,
										a.last_name,
										a.email,
										a.phone,
										a.survey_score,
										b.sequence,
										b.level,
										b.class_incomplete 
										from $advisorClassTableName as b join $advisorTableName as a 
										where b.advisor_call_sign = a.call_sign
										and b.semester = a.semester
										and b.semester='$theSemester' 
										and (a.survey_score != 6 and a.survey_score != 9)
										order by b.advisor_call_sign,b.sequence";
		$wpw1_cwa_advisorclass			= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorClassTableName / $advisorTableName tables failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numACRows					= $wpdb->num_rows;
			if ($doDebug) {
				$myStr					= $wpdb->last_query;
				echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName table<br />";
			}
			if ($numACRows > 0) {
				$content			.= "<h3>Advisors with All S Students in $currentSemester</h3>
										<table>
										<tr><th>Advisor</th>
											<th>Name</th>
											<th>Sequence</th>
											<th>Level</th>
											<th>Phone</th>
											<th>Email</th>
										</tr>";
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_sequence 					= $advisorClassRow->sequence;
					$advisorClass_level 					= $advisorClassRow->level;
					$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
					$advisor_call_sign 						= strtoupper($advisorClassRow->call_sign);
					$advisor_first_name 					= $advisorClassRow->first_name;
					$advisor_last_name 						= $advisorClassRow->last_name;
					$advisor_email 							= $advisorClassRow->email;
					$advisor_phone							= $advisorClassRow->phone;
					$advisor_survey_score 					= $advisorClassRow->survey_score;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

					if ($doDebug) {
						echo "Processing class record $advisor_call_sign sequence $advisorClass_sequence<br />";
					}

					/// get all students for this class and check their status
					$sql				= "select * from $studentTableName 
											where semester='$theSemester' 
												and response='Y' 
												and student_status !='C' 
												and assigned_advisor='$advisor_call_sign' 
												and assigned_advisor_class=$advisorClass_sequence";
					$wpw1_cwa_student	= $wpdb->get_results($sql);
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

								if ($student_student_status == 'Y') {
									$allS								= FALSE;
								}
							}
							if ($allS) {
								/// output the info	
								$content		.= "<tr><td>$advisor_call_sign</td>
														<td>$advisor_last_name, $advisor_first_name</td>
														<td>$advisorClass_sequence</td>
														<td>$advisorClass_level</td>
														<td>$advisor_phone</td>
														<td>$advisor_email</td></tr>";
								$myCount++;
							}
						}
					}			 
				}
				$content			.= "</table><p>$myCount Advisors with all S students</p>";
			} else {
				echo "No records found in $advisorClassTableName<br />";
			}
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
	$result			= write_joblog_func("List Advisors with All S Students|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('list_advisors_with_all_s_students', 'list_advisors_with_all_s_students_func');

