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
// Modified 16Oct24 by Roland for new database

	global $wpdb;

	$doDebug			= FALSE;
	$testMode			= FALSE;
	
	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];

	if ($userName == '') {
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

	$content = "";	

	if ($testMode) {
		$studentTableName		= 'wpw1_cwa_student2';
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		$content			.= "<p><b>Operating in Testmode</b></p>";
		if ($doDebug) {
			echo "<b>Operating in Testmode</b><br />";
		}
	} else {
		$studentTableName		= 'wpw1_cwa_student';
		$userMasterTableName	= 'wpw1_cwa_user_master';
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
			echo "<br />at Pass 2 with $inp_semester semester and typerun = $inp_typerun<br />";
		}
		if ($inp_typerun != 'responders' && $inp_typerun != 'nonresponders') {
			if ($doDebug) {
				echo "invalid input. aborting<br />";
			}
			$content		.= "Invalid input. Aborting.";
		} else {
			$sql				= "select * from $studentTableName 
									left join $userMasterTableName on user_call_sign = student_call_sign 
									where student_semester='$inp_semester' 
									order by student_call_sign";
			$wpw1_cwa_student	= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
				}
				if ($numSRows > 0) {
					if ($inp_typerun == "responders") {
						$content		.= "<h3>Students Who have Responded for $inp_semester Semester</h3>
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
											</tr></table>";
					} else {
						$content		.= "<h3>Students Who have NOT Responded for $inp_semester Semester</h3>";
					}
					$content 			.= "<table><tr>
											<th>Student ID</th>
											<th>Call Sign</th>
											<th>Last Name</th>
											<th>First Name</th>
											<th>Phone</th>
											<th>City</th>
											<th>State</th>
											<th>Email</th>
											<th>TZ Offset</th>
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
							$content .= "<tr><td>$student_ID</td>
											<td>$student_call_sign</td>
											<td>$student_last_name</td>
											<td>$student_first_name</td>
											<td>$student_phone</td>
											<td>$student_city</td>
											<td>$student_state</td>
											<td>$student_email</td>
											<td>$student_timezone_offset</td></tr>
										<tr><td>$student_response</td>
											<td>$student_promotable</td>
											<td>$student_hold_reason_code</td>
											<td>$student_hold_override</td>
											<td>$student_intervention_required</td>
											<td>$student_status</td>
											<td>$student_email_number</td>
											<td>$student_level</td>
											<td>&nbsp;</td></tr>";

							// see if the student has taken a previous class. If so, list the classes

							$sql					= "select * from $studentTableName 
														where student_call_sign='$student_call_sign' 
														order by student_date_created";
							$wpw1_cwa_student	= $wpdb->get_results($sql);
							if ($wpw1_cwa_student === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$numSRows									= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
								}
								if ($numSRows > 0) {
									$firstTime										= TRUE;
									foreach ($wpw1_cwa_student as $studentRow) {
										$student1_ID								= $studentRow->student_id;
										$student1_call_sign						= $studentRow->student_call_sign;
										$student1_time_zone  					= $studentRow->student_time_zone;
										$student1_timezone_offset				= $studentRow->student_timezone_offset;
										$student1_youth  						= $studentRow->student_youth;
										$student1_age  							= $studentRow->student_age;
										$student1_student_parent 				= $studentRow->student_parent;
										$student1_parent_email  					= strtolower($studentRow->student_parent_email);
										$student1_level  						= $studentRow->student_level;
										$student1_waiting_list 					= $studentRow->student_waiting_list;
										$student1_request_date  					= $studentRow->student_request_date;
										$student1_semester						= $studentRow->student_semester;
										$student1_notes  						= $studentRow->student_notes;
										$student1_welcome_date  					= $studentRow->student_welcome_date;
										$student1_email_sent_date  				= $studentRow->student_email_sent_date;
										$student1_email_number  					= $studentRow->student_email_number;
										$student1_response  						= strtoupper($studentRow->student_response);
										$student1_response_date  				= $studentRow->student_response_date;
										$student1_abandoned  					= $studentRow->student_abandoned;
										$student1_status  						= strtoupper($studentRow->student_status);
										$student1_action_log  					= $studentRow->student_action_log;
										$student1_pre_assigned_advisor  			= $studentRow->student_pre_assigned_advisor;
										$student1_selected_date  				= $studentRow->student_selected_date;
										$student1_no_catalog  					= $studentRow->student_no_catalog;
										$student1_hold_override  				= $studentRow->student_hold_override;
										$student1_assigned_advisor  				= $studentRow->student_assigned_advisor;
										$student1_advisor_select_date  			= $studentRow->student_advisor_select_date;
										$student1_advisor_class_timezone 		= $studentRow->student_advisor_class_timezone;
										$student1_hold_reason_code  				= $studentRow->student_hold_reason_code;
										$student1_class_priority  				= $studentRow->student_class_priority;
										$student1_assigned_advisor_class 		= $studentRow->student_assigned_advisor_class;
										$student1_promotable  					= $studentRow->student_promotable;
										$student1_excluded_advisor  				= $studentRow->student_excluded_advisor;
										$student1_student_survey_completion_date	= $studentRow->student_survey_completion_date;
										$student1_available_class_days  			= $studentRow->student_available_class_days;
										$student1_intervention_required  		= $studentRow->student_intervention_required;
										$student1_copy_control  					= $studentRow->student_copy_control;
										$student1_first_class_choice  			= $studentRow->student_first_class_choice;
										$student1_second_class_choice  			= $studentRow->student_second_class_choice;
										$student1_third_class_choice  			= $studentRow->student_third_class_choice;
										$student1_first_class_choice_utc  		= $studentRow->student_first_class_choice_utc;
										$student1_second_class_choice_utc  		= $studentRow->student_second_class_choice_utc;
										$student1_third_class_choice_utc  		= $studentRow->student_third_class_choice_utc;
										$student1_catalog_options				= $studentRow->student_catalog_options;
										$student1_flexible						= $studentRow->student_flexible;
										$student1_date_created 					= $studentRow->student_date_created;
										$student1_date_updated			  		= $studentRow->student_date_updated;
							
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;Student in student with student status of $student1_status<br />";
										}
										if ($student_status == 'Y') {
											$content 	.= "<tr><td colspan='9'>Took $student1_level class during the $student1_semester semester. Promotable: $student1_promotable</td></tr>";
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
							$content .= "<tr><td colspan='9'>$newActionLog</td></tr>
										<tr><td colspan='9'><hr /></td></tr>";
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
