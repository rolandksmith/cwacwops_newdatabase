function move_student_to_different_semester_func() {


/* 	Move a student to a different Semester
 *	
 *	input: 	student call sign
 *			semester the student is to be moved from
 *			target semester the student is to be moved to
 *
 *	looks for the student in the current semester, or if the semesterisn't in
 *	session looks for the student in the next semester
 *
 * 	if the student is found, the semester is changed to the requested semester.
 *	If the student is being move from a future semester to the current (or next if current
 *		not is session) and we're within the 45-day window, set the email number to 1,
 *		the response to 'Y', response date to today, and status to blank.
 *	if the student is being moved from the current (or next if current not in session) 
 *		set the email sent date, response, response date and status to blank and email number and 
 *		response number to 0 and set all class choices to None
 *
 	modified 18Jan2022 by Roland to use tables rather than pods
 	Modified 20Oct2022 by Roland to use new timezone table format
 	Modified 16Apr23 by Roland to fix action_log
 	Modified 13Jul23 by Roland to use consolidated tables
 	Modified 20Oct24 by Roland for new database
 
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName  			= $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];	
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$daysToSemester		= $initializationArray['daysToSemester'];
	$myDate				= $initializationArray['currentDate'];
	$actionDate			= date('Y/m/d H:i');

//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$inp_callSign				= '';
	$inp_callSign				= '';
	$theURL						= "$siteURL/cwa-move-student-to-different-semester/";
	$validTestmode				= $initializationArray['validTestmode'];
	$jobname					= "Move Student to Different Semester";

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
				$strPass		 = filter_var($strPass,FILTER_SANITIZE_STRING);
			}
			if ($str_key 		== "inp_callSign") {
				$inp_callSign	 = strtoupper($str_value);
				$inp_callSign	 = filter_var($inp_callSign,FILTER_SANITIZE_STRING);
			}
			if ($str_key 		== "inp_semesterA") {
				$inp_semesterA	 = $str_value;
				$inp_semesterA	 = filter_var($inp_semesterA,FILTER_SANITIZE_STRING);
			}
			if ($str_key 		== "inp_semesterB") {
				$inp_semesterB	 = $str_value;
				$inp_semesterB	 = filter_var($inp_semesterB,FILTER_SANITIZE_STRING);
			}
		}
	}

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
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$studentTableName			= "wpw1_cwa_student2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$studentTableName			= "wpw1_cwa_student";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		
		$optionListA		= "";
		$optionListB		= "";
		if ($currentSemester != 'Not in Session') {
			$optionListA	.= "<input type='radio' class='formInputButton' name='inp_semesterA' value='$currentSemester'> $currentSemester<br />";
			$optionListB	.= "<input type='radio' class='formInputButton' name='inp_semesterB' value='$currentSemester'> $currentSemester<br />";
		}
		$optionListA		.= "<input type='radio' class='formInputButton' name='inp_semesterA' value='$nextSemester'> $nextSemester<br />";
		$optionListB		.= "<input type='radio' class='formInputButton' name='inp_semesterB' value='$nextSemester'> $nextSemester<br />";
		$optionListA		.= "<input type='radio' class='formInputButton' name='inp_semesterA' value='$semesterTwo'> $semesterTwo<br />";
		$optionListB		.= "<input type='radio' class='formInputButton' name='inp_semesterB' value='$semesterTwo'> $semesterTwo<br />";
		$optionListA		.= "<input type='radio' class='formInputButton' name='inp_semesterA' value='$semesterThree'> $semesterThree<br />";
		$optionListB		.= "<input type='radio' class='formInputButton' name='inp_semesterB' value='$semesterThree'> $semesterThree<br />";
		$optionListA		.= "<input type='radio' class='formInputButton' name='inp_semesterA' value='$semesterFour'> $semesterFour<br />";
		$optionListB		.= "<input type='radio' class='formInputButton' name='inp_semesterB' value='$semesterFour'> $semesterFour<br />";
		
		$content 		.= "<h3>Move a Student to a Different Semester</h3>
							<p>Enter the call sign of the student to be moved and the student's current semester. 
							Then select the semester 
							that the student is to be moved to. The program will locate the student and 
							if the target semester is different than the student's current semester, the 
							student will be moved.</p>
							<p>If the student is being moved to the current semester (or the next semester if no
							current semester in process) and today's date is within 45 days of the start of the 
							semester, the student will be automatically verified. Otherwise, the normal verification 
							process will be run on the appropriate dates.</p>
							<p>Click Submit to Start the Process</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<b>Call Sign</b>: <input type='text' class='formInputText' name='inp_callSign' size='10' maxlength='15'><br />
							<b>Student's Current Semester</b>:<br />
							$optionListA
							<b>Target Semester</b>:<br />
							$optionListB
							<table>
							$testModeOption
							</table>
							<input class='formInputButton' type='submit' value='Submit' />
							</form>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "At pass 2 with $inp_callSign<br />";
		}
		$content		.= "<h3>$jobname</h3>";
		$doProceed		= TRUE;
		// the current and target semesters must be different
		if ($inp_semesterA == $inp_semesterB) {
			$content	.= "Current and Target semesters must be different.";
		} else {
		// Find the student in question	
			$sql				= "select * from $studentTableName 
									left join $userMasterTableName on user_call_sign = student_call_sign 
									where student_semester='$inp_semesterA' 
									and student_call_sign='$inp_callSign'";
			$wpw1_cwa_student				= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br />";
				}
				if ($numSRows > 0) {
					if ($doDebug) {
						echo "found $numSRows rows in $studentTableName<br />";
					}
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
					
						if ($student_assigned_advisor != '') {
							$content		.= "Student is currently assigned to $student_assigned_advisor advisor's class $student_assigned_advisor_class class. 
												Unassign the student before moving the student to a different semexter.";
							$doProceed		= FALSE;
						} 
						if ($doProceed) {
							$updateParams							= array();
							$updateFormat							= array();
							$doProceed								= TRUE;
							if ($doDebug) {
								echo "Got the source record for $student_call_sign at $student_level in TZ $student_time_zone in semester $student_semester<br />";
							}
							// Look to see if the student already exists in the target semester
							$studentCount			= $wpdb->get_var("select count(student_call_sign) 
														from $studentTableName 
														where student_semester = '$inp_semesterB' 
														and student_call_sign = '$inp_callSign'");
							if ($studentCount == NULL ) {
								$studentCount		= 0;
							}
							if ($studentCount > 0) {								
								if ($doDebug) {
									echo "Found a duplicate for $inp_callSign in $studentB_semester<br />";
								}
								$content	.= "<p>Student $inp_callSign has a registration record in the target 
												semester of $inp_semesterB.
												 No action taken.</p>";
								$doProceed	= FALSE;
							}
						}
						if ($doProceed) {
						// if we get here, the move can happen					
							if ($doDebug) {
								echo "the student can be moved<br />";
							}
							$content	.= "<h3>Moving $inp_callSign from $inp_semesterA to $inp_semesterB</h3>";
							// determine if the move is into the current/next semester
							$theSemester		= $currentSemester;
							if ($currentSemester == 'Not in Session') {
								$theSemester	= $nextSemester;
							}
							if ($theSemester == $inp_semesterB) { 	// moving into the current/next semester
								// within the 45-day window?
								$theDays		= intval($daysToSemester);
								if ($theDays <= 45) {		// if so, verify for this semester
									$updateParams['student_email_number'] 	= 1;
									$updateFormat[]				 	= '%d';
									$updateParams['student_response']		= 'Y';
									$updateFormat[]				 	= '%s';
									$updateParams['student_response_date']	= $myDate;
									$updateFormat[]				 	= '%s';
									$updateParams['student_status'] = '';
									$updateFormat[]				 	= '%s';
									$content	.= "<p>Moving into the proximate semester within the 45-day window.</p>
													<p>Set email_number to 1<br />
													response to 'Y'<br />
													student_status to ''<br />";
								} else {
									$updateParams['student_email_number'] 		= 0;
									$updateFormat[]				 		= '%d';
									$updateParams['student_email_sent_date'] 	= '';
									$updateFormat[]				 		= '%s';
									$updateParams['student_response']			= '';
									$updateFormat[]				 		= '%s';
									$updateParams['student_response_date']		= '';
									$updateFormat[]				 		= '%s';
									$updateParams['student_status'] 	= '';
									$updateFormat[]				 		= '%s';
									$updateParams['student_abandoned'] 			= '';
									$updateFormat[]				 		= '%s';
									$content	.= "<p>Moving into a future semester</p>
													<p>Set email_number to 0<br />
													email_sent_date to blank<br />
													response to blank<br />
													student_status to blank<br />
													abandoned to blank<br />";
							
								}
							} else {
								$updateParams['student_email_number'] 		= 0;
								$updateFormat[]				 		= '%d';
								$updateParams['student_email_sent_date'] 	= '';
								$updateFormat[]				 		= '%s';
								$updateParams['student_welcome_date']	 	= '';
								$updateFormat[]				 		= '%s';
								$updateParams['student_response']			= '';
								$updateFormat[]				 		= '%s';
								$updateParams['student_response_date']		= '';
								$updateFormat[]				 		= '%s';
								$updateParams['student_status'] 	= '';
								$updateFormat[]				 		= '%s';
								$updateParams['student_abandoned'] 			= '';
								$updateFormat[]				 		= '%s';
								$updateParams['student_no_catalog'] 		= 'Y';
								$updateFormat[]				 		= '%s';
								$updateParams['student_first_class_choice'] = 'None';
								$updateFormat[]				 		= '%s';
								$updateParams['student_second_class_choice'] 	= 'None';
								$updateFormat[]				 		= '%s';
								$updateParams['student_third_class_choice'] = 'None';
								$updateFormat[]				 		= '%s';
								$updateParams['student_first_class_choice_utc'] = 'None';
								$updateFormat[]				 		= '%s';
								$updateParams['student_second_class_choice_utc'] 	= 'None';
								$updateFormat[]				 		= '%s';
								$updateParams['student_third_class_choice_utc'] = 'None';
								$updateFormat[]				 		= '%s';
								$updateParams['student_waiting_list'] 		= '';
								$updateFormat[]				 		= '%s';
								
								$content	.= "<p>Moving into a future semester</p>
												<p>Set email_number to 0<br />
												email_sent_date to blank<br />
												response to blank<br />
												student_status to blank<br />
												abandoned to 0<br />";
							}
							$student_action_log		= "$student_action_log / $actionDate MGMT MOVE Student moved from $inp_semesterA to $inp_semesterB. ";
							$updateParams['student_action_log']			= $student_action_log;
							$updateFormat[]				 		= '%s';
							$updateParams['student_semester']			= $inp_semesterB;
							$updateFormat[]				 		= '%s';
							$content							.= "semester to $inp_semesterB<br />
																	action_log to $student_action_log</p>";
							$studentUpdateData		= array('tableName'=>$studentTableName,
															'inp_method'=>'update',
															'inp_data'=>$updateParams,
															'inp_format'=>$updateFormat,
															'jobname'=>$jobname,
															'inp_id'=>$student_ID,
															'inp_callsign'=>$student_call_sign,
															'inp_semester'=>$inp_semesterB,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateStudent($studentUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to update content in $studentTableName<br />";
							} else {
								if ($doDebug) {
									echo "Successfully updated $student_call_sign record at $student_ID<br />";
								}
							}
							
						} else {
							$content	.= "<p>The move could not proceed.</p>";
						}
					} 			// end of while loop
				} else {
					if ($doDebug) {
						echo "No records found for $inp_callSign in semester $inp_semesterA<br />";
					}
					$content	.= "No records found for $inp_callSign in semester $inp_semesterA";
 				}
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('move_student_to_different_semester', 'move_student_to_different_semester_func');
