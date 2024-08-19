function list_student_responders_func() {

// modified 7Jan2020 by Roland to select the semester of interest
// modified 13feb2020 Bob c add Promotable and Intervention Required fields
// modified 17Feb2020 Roland added counts for R and Y responses	
// modified 18Feb2020 Roland added past_student info
// modified 5Mar2020 Roland added email_number to the report
// modified 5Mar2020 Bob extensively
// modified 28June2021 Roland to use new pod format
// modified 17Jan2022 by Roland to use tables instead of pods
// modified 1Oct22 by Roland for the updated timezone process
// Modified 16Apr23 by Roland to fix action_log
// Modified 13Jul23 by Roland to use consolidated tables

	global $wpdb;

	$doDebug			= FALSE;
	$testMode			= FALSE;
	
	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];

	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		ini_set('max_execution_time',0);
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$inp_semester		= $initializationArray['nextSemester'];
	$strPass			= "1";
	$totalR				= 0;
	$totalY				= 0;
	$pastStudents		= 0;
	$studentsDropped	= 0;
	$myCount			= 0;
	$noWelcomeEmail		= array();
	$theURL				= "$siteURL/cwa-list-student-responders/";
	
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
			if ($str_key 		== "inp_typerun") {
				$inp_typerun	 = $str_value;
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

	if ($testMode) {
		$studentTableName		= 'wpw1_cwa_consolidated_student2';
		$content			.= "<p><b>Operating in Testmode</b></p>";
		if ($doDebug) {
			echo "<b>Operating in Testmode</b><br />";
		}
	} else {
		$studentTableName		= 'wpw1_cwa_consolidated_student';
	}
	
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
		
		$content 		.= "<h3>List Student Responders</h3>
<p>Select the semester of interest from the list below:</p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data''>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Semester</td><td>
<select name='inp_semester' required='required' size='5' autofocus class='formSelect'>
$optionList
</select></td></tr>
<tr><td>Data to be listed</td><td>
<input type='radio' class='formInputButton' name='inp_typerun' value='responders' checked='checked' />Y and R Responders<br />
<input type='radio' class='formInputButton' name='inp_typerun' value='nonresponders' />Non Responders</td></tr>
<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form>";

//////////////

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "at Pass 2 with $inp_semester semester and typerun = $inp_typerun<br />";
		}
		if ($inp_typerun != 'responders' && $inp_typerun != 'nonresponders') {
			if ($doDebug) {
				echo "invalid input. aborting<br />";
			}
			$content		.= "Invalid input. Aborting.";
		} else {
			$sql				= "select * from $studentTableName 
									where semester='$inp_semester'";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				if ($doDebug) {
					echo "Reading $studentTableName<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			} else {
				$numSRows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "retrieved $numSRows rows from $studentTableName table<br />";
				}
				if ($numSRows > 0) {
					if ($inp_typerun == "responders") {
						$content					.= "<h3>Students Who have Responded for $inp_semester Semester</h3>
<table style='width:1300px;'><tr>
<th colspan='5'>Explanation for the Various Codes</th>
</tr><tr>
<th>Response</th>
<th>Hold Code</th>
<th>Hold Override</th>
<th>Inter Req</th>
<th>Status</th>
</tr><tr>
<td style='vertical-align:top;'>Blank: No Response<br />
<strong>Y</strong>: Want to take the class<br />
<strong>R</strong>: Refused Class</td>
<td style='vertical-align:top;'><strong>X</strong>: Not promotable, signed up for same class level. Don't assign to prev advisor<br />
<strong>E</strong>: Not evaluated, signed up for higher class level. Student on hold<br />
<strong>H</strong>: Not promotable, signed up for higher class level. Student on hold<br />
<strong>Q</strong>: Advisor quit, student not evaluated but signed up for higher class level. Student on hold<br />
<strong>W</strong>: Student withdrew from class but signed up for higher class level. Student on hold</td>
<td style='vertical-align:top;'><strong>R</strong>: Hold released</td>
<td style='vertical-align:top;'><strong>M</strong>: Moved<br />
<strong>Q</strong>: Advisor Quit<br />
<strong>H</strong>: Not Promotable<br />
<strong>A</strong>: Assigned to AC6AC</td>
<td style='vertical-align:top;'><strong>C</strong>: Student has been replaced<br />
<strong>N</strong>: Advisor decludned replacement student<br />
<strong>R</strong>: Student replacement requested, not yet done<br />
<strong>S</strong>: Advisor has not yet verified student<br />
<strong>Y</strong>: Student verified and taking class</td>
</tr></table>
";
					} else {
						$content				.= "<h3>Students Who have NOT Responded for $inp_semester Semester</h3>";
					}
					$content 						.= "<table><tr>
<th>Student ID</th>
<th>Call Sign</th>
<th>Last Name</th>
<th>First Name</th>
<th>Phone</th>
<th>City</th>
<th>State</th>
<th>Email</th>
<th>Time Zone</th>
</tr><tr>
<th>Response</th>
<th>Promotable</th>
<th>Hold Code</th>
<th>Hold Override</th>
<th>Inter Req</th>
<th>Status</th>
<th>email Nmbr</th>
<th>Level</th>
<th>&nbsp;</th>
</tr>";
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
						$student_abandoned  				= $studentRow->abandoned;
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

						if ($doDebug) {
							echo "<br />Processing call sign $student_call_sign with a response of $student_response<br />";
						}
						if ($student_welcome_date == '') {
							$noWelcomeEmail[]	= $student_call_sign;
						}
						$processRecord 		= FALSE;
						if ($inp_typerun == 'responders') {		
							if (($student_response == 'Y') || ($student_response == 'R')) {
								$processRecord	= TRUE;
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;Is a responder<br />";
								}	
								if ($student_response == 'R') {
									$totalR++;
								}
								if ($student_response == 'Y') {
									$totalY++;
								}
							}
						}
						if ($inp_typerun == 'nonresponders') {
							if ($student_response == '') {
								$processRecord	= TRUE;
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;Is NOT a responder<br />";
								}
							}	
						}
			
						if ($processRecord) {
							$content .= "
<tr><td>$student_ID</td>
	<td>$student_call_sign</td>
	<td>$student_last_name</td>
	<td>$student_first_name</td>
	<td>$student_phone</td>
	<td>$student_city</td>
	<td>$student_state</td>
	<td>$student_email</td>
	<td>$student_time_zone</td></tr>
<tr><td>$student_response</td>
	<td>$student_promotable</td>
	<td>$student_hold_reason_code</td>
	<td>$student_hold_override</td>
	<td>$student_intervention_required</td>
	<td>$student_student_status</td>
	<td>$student_email_number</td>
	<td>$student_level</td>
	<td>&nbsp;</td></tr>";

// see if the student has taken a previous class. If so, list the classes

							$sql					= "select * from $studentTableName 
														where call_sign='$student_call_sign' 
														order by date_created";
							$wpw1_cwa_student		= $wpdb->get_results($sql);
							if ($wpw1_cwa_student === FALSE) {
								if ($doDebug) {
									echo "Reading $studentTableName table<br />";
									echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
									echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
								}
							} else {
								$numPSRows									= $wpdb->num_rows;
								if ($doDebug) {
									echo "found $numPSRows rows in $studentTableName table<br />";
								}
								if ($numPSRows > 0) {
									$firstTime										= TRUE;
									foreach ($wpw1_cwa_student as $studentRow) {
										$student_ID							= $studentRow->student_id;
										$student_call_sign						= strtoupper($studentRow->call_sign);
										$student_first_name					= $studentRow->first_name;
										$student_last_name						= stripslashes($studentRow->last_name);
										$student_email  						= strtolower(strtolower($studentRow->email));
										$student_ph_code						= $studentRow->ph_code;
										$student_phone  						= $studentRow->phone;
										$student_city  						= $studentRow->city;
										$student_state  						= $studentRow->state;
										$student_zip_code  					= $studentRow->zip_code;
										$student_country_code					= $studentRow->country_code;
										$student_country  						= $studentRow->country;
										$student_time_zone  					= $studentRow->time_zone;
										$student_timezone_id					= $studentRow->timezone_id;
										$student_timezone_offset				= $studentRow->timezone_offset;
										$student_whatsapp						= $studentRow->whatsapp_app;
										$student_signal						= $studentRow->signal_app;
										$student_telegram						= $studentRow->telegram_app;
										$student_messenger						= $studentRow->messenger_app;					
										$student_wpm 	 						= $studentRow->wpm;
										$student_youth  						= $studentRow->youth;
										$student_age  							= $studentRow->age;
										$student_student_parent 				= $studentRow->student_parent;
										$student_student_parent_email  		= strtolower($studentRow->student_parent_email);
										$student_level  						= $studentRow->level;
										$student_waiting_list 					= $studentRow->waiting_list;
										$student_request_date  				= $studentRow->request_date;
										$student_semester						= $studentRow->semester;
										$student_notes  						= $studentRow->notes;
										$student_welcome_date  				= $studentRow->welcome_date;
										$student_email_sent_date  				= $studentRow->email_sent_date;
										$student_email_number  				= $studentRow->email_number;
										$student_response  					= strtoupper($studentRow->response);
										$student_response_date  				= $studentRow->response_date;
										$student_abandoned  				= $studentRow->abandoned;
										$student_student_status  				= strtoupper($studentRow->student_status);
										$student_action_log  					= $studentRow->action_log;
										$student_pre_assigned_advisor  		= $studentRow->pre_assigned_advisor;
										$student_selected_date  				= $studentRow->selected_date;
										$student_no_catalog		  			= $studentRow->no_catalog;
										$student_hold_override  				= $studentRow->hold_override;
										$student_messaging  					= $studentRow->messaging;
										$student_assigned_advisor  			= $studentRow->assigned_advisor;
										$student_advisor_select_date  			= $studentRow->advisor_select_date;
										$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
										$student_hold_reason_code  			= $studentRow->hold_reason_code;
										$student_class_priority  				= $studentRow->class_priority;
										$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
										$student_promotable  					= $studentRow->promotable;
										$student_excluded_advisor  			= $studentRow->excluded_advisor;
										$student_student_survey_completion_date = $studentRow->student_survey_completion_date;
										$student_available_class_days  		= $studentRow->available_class_days;
										$student_intervention_required  		= $studentRow->intervention_required;
										$student_copy_control  				= $studentRow->copy_control;
										$student_first_class_choice  			= $studentRow->first_class_choice;
										$student_second_class_choice  			= $studentRow->second_class_choice;
										$student_third_class_choice  			= $studentRow->third_class_choice;
										$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
										$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
										$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
										$student_date_created 					= $studentRow->date_created;
										$student_date_updated			  		= $studentRow->date_updated;

							
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;Student in student with student status of $student_student_status<br />";
										}
										if ($student_student_status == 'Y') {
											$content 	.= "<tr><td colspan='9'>Took $student_level class during the $student_semester semester. Promotable: $student_promotable</td></tr>";
											if ($firstTime) {
												$firstTime	= FALSE;
												$pastStudents++;
												if ($doDebug) {
													echo "&nbsp;&nbsp;&nbsp;Student has taken a class<br />";
												}
											}
										}
									}
								} else {
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp;Student has NOT taken a class<br />";
									}
								}
							}
							$newActionLog		= formatActionLog($student_action_log);
							$content .= "<tr><td colspan='9'>$newActionLog</td></tr><tr><td colspan='9'><hr /></td></tr>";
							$myCount++;
							if ($student_email_number==4) {
								$studentsDropped++;
							}
						}
					}	
					$content .= "</table><br />Records Printed: $myCount<br />
Total Past Students: $pastStudents<br />";
					if ($inp_typerun == 'responders') {
						$content .= "Total R Responses: $totalR<br />
Total Y Responses: $totalY<br />";
					} else {
						$content .= "Total Dropped Students: $studentsDropped<br />";
					}
				} else {
					$content				.= "<p>No records found in $studentTableName pod.</p>";
				}
			}
		}
	}
	$thisTime 					= date('Y-m-d H:i:s');
	$content					.= "<br /><p>Report displayed at $thisTime.</p>";
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
	$result			= write_joblog_func("List Student Responders|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('list_student_responders', 'list_student_responders_func');