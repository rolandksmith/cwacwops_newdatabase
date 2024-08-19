function list_advisors_incomplete_evaluations_func() {

/* List Advisors with Incomplete Evaluations
 *
 * Presents a list of advisors who have not completed the evaluation
 * for the current or immediate past semester
 *
 *
 	Modified 24Oct22 by Roland to accomodate new timezone table layouts
 	Modified 16Apr23 by Roland to fix action_log
 	Modified 12Jul23 by Roland to use consolidated tables
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
	$inp_advisor				= '';
	$advisorArray				= array();
	$pastAdvisorErrors			= 0;
	$notEvaluatedCount			= 0;
	$advisorsDue				= 0;
	$theURL						= "$siteURL/cwa-list-advisors-with-incomplete-evaluations/";
	$advisorCount				= 0;
	$unevaluatedArray			= array();

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
		$content					.= "<p><b>Operating in test mode</b></p>";
	} else {
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
		$studentTableName			= 'wpw1_cwa_consolidated_student';
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		
			$content 		.= "<h3>List Advisors with Incomplete Evaluations</h3>
<p>The function cycles through all advisor classes for the current semester (or the 
previous semester if the current semester is not in session) and generates a list of 
all advisors who have not completed their evaluations.</p>
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
		$prevSemester			= $initializationArray['prevSemester'];
		$theSemester			= $currentSemester;
		if ($currentSemester == 'Not in Session') {
			$theSemester		= $prevSemester;
		}

		$totalStudents			= 0;
		$totalEvaluated			= 0;
		$prevCallSign			= '';

		$content			.= "<h3>Advisors with Incomplete Evaluations for $theSemester</h3>
								<table>
								<tr><th>Advisor</th>
									<th>Name</th>
									<th>Sequence</th>
									<th>Time Zone</th>
									<th>Level</th>
									<th>Nmbr Students</th>
									<th>Nmbr Eval</th></tr>";
		
		$sql							= "select * from $advisorClassTableName 
											where semester='$theSemester' 
											order by advisor_call_sign,sequence";
		$wpw1_cwa_advisorclass		= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorClassTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numACRows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName table<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_advisor_callsign 			= $advisorClassRow->advisor_call_sign;
					$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
					$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
					$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
					$advisorClass_sequence 					= $advisorClassRow->sequence;
					$advisorClass_semester 					= $advisorClassRow->semester;
					$advisorClass_timezone 					= $advisorClassRow->time_zone;
					$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
					$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->level;
					$advisorClass_class_size 				= $advisorClassRow->class_size;
					$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
					$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
					$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc	= $advisorClassRow->class_schedule_times_utc;
					$advisorClass_action_log 				= $advisorClassRow->action_log;
					$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
					$advisorClass_date_created				= $advisorClassRow->date_created;
					$advisorClass_date_updated				= $advisorClassRow->date_updated;
					$advisorClass_student01 				= $advisorClassRow->student01;
					$advisorClass_student02 				= $advisorClassRow->student02;
					$advisorClass_student03 				= $advisorClassRow->student03;
					$advisorClass_student04 				= $advisorClassRow->student04;
					$advisorClass_student05 				= $advisorClassRow->student05;
					$advisorClass_student06 				= $advisorClassRow->student06;
					$advisorClass_student07 				= $advisorClassRow->student07;
					$advisorClass_student08 				= $advisorClassRow->student08;
					$advisorClass_student09 				= $advisorClassRow->student09;
					$advisorClass_student10 				= $advisorClassRow->student10;
					$advisorClass_student11 				= $advisorClassRow->student11;
					$advisorClass_student12 				= $advisorClassRow->student12;
					$advisorClass_student13 				= $advisorClassRow->student13;
					$advisorClass_student14 				= $advisorClassRow->student14;
					$advisorClass_student15 				= $advisorClassRow->student15;
					$advisorClass_student16 				= $advisorClassRow->student16;
					$advisorClass_student17 				= $advisorClassRow->student17;
					$advisorClass_student18 				= $advisorClassRow->student18;
					$advisorClass_student19 				= $advisorClassRow->student19;
					$advisorClass_student20 				= $advisorClassRow->student20;
					$advisorClass_student21 				= $advisorClassRow->student21;
					$advisorClass_student22 				= $advisorClassRow->student22;
					$advisorClass_student23 				= $advisorClassRow->student23;
					$advisorClass_student24 				= $advisorClassRow->student24;
					$advisorClass_student25 				= $advisorClassRow->student25;
					$advisorClass_student26 				= $advisorClassRow->student26;
					$advisorClass_student27 				= $advisorClassRow->student27;
					$advisorClass_student28 				= $advisorClassRow->student28;
					$advisorClass_student29 				= $advisorClassRow->student29;
					$advisorClass_student30 				= $advisorClassRow->student30;
					$class_number_students					= $advisorClassRow->number_students;
					$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
					$class_comments							= $advisorClassRow->class_comments;
					$copycontrol							= $advisorClassRow->copy_control;

					$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

					if ($doDebug) {
						echo "<br />Processing class record $advisorClass_advisor_callsign sequence $advisorClass_sequence with evaluation complete = $class_evaluation_complete<br />";
					}
					if ($advisorClass_advisor_callsign == $prevCallSign) {
						$thisCallSign						= '';
						$thisName							= '';
						if ($doDebug) {
							echo "blanking out thisCallSign<br />";
						}
					}
					$prevCallSign							= $advisorClass_advisor_callsign;
					if ($class_evaluation_complete != 'Y') {
						$sql						= "select * from $advisorTableName 
														where semester='$theSemester' 
														and call_sign='$advisorClass_advisor_callsign'";
						$wpw1_cwa_advisor	= $wpdb->get_results($sql);
						if ($wpw1_cwa_advisor === FALSE) {
							$myError			= $wpdb->last_error;
							$myQuery			= $wpdb->last_query;
							if ($doDebug) {
								echo "Reading $advisorTableName table failed<br />
									  wpdb->last_query: $myQuery<br />
									  wpdb->last_error: $myError<br />";
							}
							$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
							sendErrorEmail($errorMsg);
						} else {	
							$numPARows				= $wpdb->num_rows;
							if ($doDebug) {
								$myStr				= $wpdb->last_query;
								echo "ran $myStr<br />and found $numPARows rows in $advisorTableName<br />";
							}
							if ($numPARows > 0) {
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

									$advisor_last_name 				= no_magic_quotes($advisor_last_name);

							
									if ($advisor_survey_score != 6) {
					
										$thisCallSign						= $advisor_call_sign;
										$thisName							= "$advisor_last_name, $advisor_first_name";
										$studentCount						= 0;
										$studentEval						= 0;
										if ($doDebug) {
											echo "Got advisor $advisor_call_sign<br />
												  processing $advisorClass_advisor_callsign sequence $advisorClass_sequence students<br />";
										}
					
										for ($snum=1;$snum<31;$snum++) {
											if ($snum < 10) {
												$strSnum 			= str_pad($snum,2,'0',STR_PAD_LEFT);
											} else {
												$strSnum			= strval($snum);
											}
											$studentCallSign		= ${'advisorClass_student' . $strSnum};
											if ($studentCallSign != '') {
												$studentCount++;
//												$totalStudents++;
											
												// count number of students and number evaluated
												$sql					= "select * from $studentTableName 
																			where semester='$theSemester' 
																			and call_sign = '$studentCallSign'";
												$wpw1_cwa_student	= $wpdb->get_results($sql);
												if ($wpw1_cwa_student === FALSE) {
													$myError			= $wpdb->last_error;
													$myQuery			= $wpdb->last_query;
													if ($doDebug) {
														echo "Reading $studentTableName table failed<br />
															  wpdb->last_query: $myQuery<br />
															  wpdb->last_error: $myError<br />";
													}
													$errorMsg			= "$jobname Reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
													sendErrorEmail($errorMsg);
												} else {
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

															$student_last_name 					= no_magic_quotes($student_last_name);
															
															if ($doDebug) {
																echo "<br />processing past student $student_call_sign with promotable of $student_promotable<br />";
															}
															if ($student_promotable == 'P' || $student_promotable == 'N' || $student_promotable == 'W') {
																$studentEval++;
//																$totalEvaluated++;
																if ($doDebug) {
																	echo "counts: studentEval: $studentEval<br />";
																}
															}
														}
													} else {
														if ($doDebug) {
															echo "No records found in $studentTableName for student $studentCallSign at id $studentid<br />";
														}
													}
												}
											}
										}
										if ($studentCount > 0 && $studentEval < $studentCount) {
											$content		.= "<tr><td>$thisCallSign</td>
																	<td>$thisName</td>
																	<td>$advisorClass_sequence</td>
																	<td>$advisor_timezone_id $advisor_timezone_offset</td>
																	<td>$advisorClass_level</td>
																	<td>$studentCount</td>
																	<td>$studentEval</td></tr>";
											if ($thisCallSign != '') {
												$advisorCount++;
											}
											$totalStudents 	= $totalStudents + $studentCount;
											$totalEvaluated	= $totalEvaluated + $studentEval;
											if (!in_array($thisCallSign,$unevaluatedArray)) {
												$unevaluatedArray[]	= $thisCallSign;
											}
										}
									}
								}
							}
						}
					}
				}
				$myInt		= 	$totalStudents - $totalEvaluated;
				$advisorCount	= count($unevaluatedArray);
				$content	.= "</table><br />
								$myInt: Total Unevaluated Students<br />
								$advisorCount: Advisors with incomplete evaluations<br />";
			} else {
				$content	.= "No records found in $advisorClassTableName</table>";
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
	$result			= write_joblog_func("List Advisors with Incomplete Evaluations|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('list_advisors_incomplete_evaluations', 'list_advisors_incomplete_evaluations_func');

