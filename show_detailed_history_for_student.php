function show_detailed_history_for_student_func() {

// Modified 1Oct22 by Roland for new timezone fields
// Modified 17Apr23 by Roland to fix action_log
// Modified 14Jul23 by Roland to use consolidated tables
// Modified 18Jan24 by Roland to show user_login information

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
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
	$inp_student				= "";
	$firstTime					= TRUE;
	$beginning					= TRUE;
	$firstAdvisor				= TRUE;
	$firstPastAdvisor			= TRUE;
	$gotStudent					= FALSE;
	$url						= "$siteURL/cwa-show-detailed-history-for-student/";
	$studentManagementURL		= "$siteURL/cwa-student-management/";

	$scoreConversion			= array('0'=>'0%',
										'50'=>'0-49%',
										'75'=>'50-89%',
										'90'=>'90+%',
										'100'=>'100%');


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
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$studentTableName			= "wpw1_cwa_consolidated_student2";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment2";
		$newAssessmentData			= "wpw1_cwa_new_assessment_data2";
		$userTableName				= "wpw1_users";
	} else {
		$studentTableName			= "wpw1_cwa_consolidated_student";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass";
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment";
		$newAssessmentData			= "wpw1_cwa_new_assessment_data";
		$userTableName				= "wpw1_users";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Display History for a Student</h3>
							<p><form method='post' action='$url' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							Student Call Sign:<br />
							<input type='text' class='formInputText' name='inp_student' size='15' maxlength='15' autofocus><br />
							<input class='formInputButton' type='submit' value='Submit' />
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "at pass 2 with $inp_student<br />";
		}
		$content		.= "<h3>Show Detailed History for $inp_student</h3>
							<h4>CW Academy Website Signup Information</h4>
							<table>";

		// get the user information and display it

		$cs1				= strtoupper($inp_student);
		$cs2				= strtolower($inp_student);
		$user_first_name	= '';
		$user_last_name		= 'N/A';
		$user_role			= '';
		$verifiedUser		= "FALSE";
		$user_login			= "Not Found";
		$user_registered	= "";
		
		$sql				= "SELECT id, 
									   user_login, 
									   user_email, 
									   display_name, 
									   user_registered 
								FROM $userTableName
								where (user_login = '$cs1' or 
										user_login = '$cs2')"; 
		$result				= $wpdb->get_results($sql);
		if ($result === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($result as $resultRow) {
					$user_id			= $resultRow->id;
					$user_login			= $resultRow->user_login;
					$user_email			= $resultRow->user_email;
					$display_name		= $resultRow->display_name;
					$user_registered	= $resultRow->user_registered;
				
					$metaSQL		= "select meta_key, meta_value 
										from `wpw1_usermeta` 
										where user_id = $user_id 
										and (meta_key = 'first_name' 
											or meta_key = 'last_name' 
											or meta_key = 'wpw1_capabilities' 
											or meta_key = 'wpumuv_needs_verification')";
					$metaResult		= $wpdb->get_results($metaSQL);
					if ($metaResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numMRows	= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $metaSQL<br />and retrieved $numMRows rows<br />";
						}
						foreach($metaResult as $metaResultRow) {
							$meta_key		= $metaResultRow->meta_key;
							$meta_value		= $metaResultRow->meta_value;
					
							if ($meta_key == 'last_name') {
								$user_last_name	= $meta_value;
							}
							if ($meta_key == 'first_name') {
								$user_first_name = $meta_value;
							}
							if ($meta_key == 'wpw1_capabilities') {
					
								$myInt			= strpos($meta_value,'administrator');
								if ($myInt !== FALSE) {
									$user_role	= 'aministrator';
								}
								$myInt			= strpos($meta_value,'student');
								if ($myInt !== FALSE) {
									$user_role	= 'student';
								}
								$myInt			= strpos($meta_value,'advisor');
								if ($myInt !== FALSE) {
									$user_role	= 'advisor';
								}
							}
							if ($meta_key == 'wpumuv_needs_verification') {
								$verifiedUser				= "FALSE";
							} else {
								$verifiedUser				= "TRUE";
							}
						}
					}
				}
			}
		}
		$content		.= "<tr><th>User Login Information</th>
								<th>Name</th>
								<th>Role</th>
								<th>Verified</th>
								<th>Sign Up Date</th></tr>
							<tr><td>$user_login</td>
								<td>$user_last_name, $user_first_name</td>
								<td>$user_role</td>
								<td>$verifiedUser</td>
								<td>$user_registered</td></tr>
							</table>";

		$content		.= "<h4>Student History</h4>
							<table>
							<tr><th style='width:40px;'>TZ</th>
								<th style='width:90px;'>Level</th>
								<th style='width:90px;'>Semester</th>
								<th style='width:50px;'>Response</th>
								<th style='width:50px;'>Status</th>
								<th style='width:50px;'>Pre-Advisor</th>
								<th style='width:50px;'>Advisor</th>
								<th style='width:50px;'>Class</th>
								<th style='width:50pz;'>Prom</th>
								<th style='width:40px;'>IR</th>
								<th style='width:40px;'>HRC</th>
								<th style='width:40px;'>HO</th>
								<th style='width:50px;'>Exc Advisor</th>
							</tr>";

		// get the student info
		$sql				= "select * from $studentTableName 
								where call_sign='$inp_student' 
								order by date_created";
		$wpw1_cwa_student				= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			if ($doDebug) {
				echo "Reading $studentTableName table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "retrieved $numSRows rows from $studentTableName table<br />";
			}
			if ($numSRows > 0) {
				if ($doDebug) {
					echo "found $numSRows rows in $studentTableName<br />";
				}
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
						echo "Got a student record for $inp_student<br />";
					}
					$gotStudent			= TRUE;
					if ($beginning) {
						$content		.= "<tr><td colspan='13'><b>Name: </b>$student_last_name, $student_first_name</td></tr>";
						$beginning		= FALSE;
					}
					$newActionLog		= formatActionLog($student_action_log);
					$content			.= "</tr><tr>
												<td style='vertical-align:top;'>$student_timezone_id $student_timezone_offset</td>
												<td style='vertical-align:top;'>$student_level</td>
												<td style='vertical-align:top;'>$student_semester</td>
												<td style='vertical-align:top;'>$student_response</td>
												<td style='vertical-align:top;'>$student_student_status</td>
												<td style='vertical-align:top;'>$student_pre_assigned_advisor</td>
												<td style='vertical-align:top;'>$student_assigned_advisor</td>
												<td style='vertical-align:top;'>$student_assigned_advisor_class</td>
												<td style='vertical-align:top;'>$student_promotable</td>
												<td style='vertical-align:top;'>$student_intervention_required</td>
												<td style='vertical-align:top;'>$student_hold_reason_code</td>
												<td style='vertical-align:top;'>$student_hold_override</td>
												<td style='vertical-align:top;'>$student_excluded_advisor</td>
											</tr><tr>
												<td colspan='13' style='border-bottom: 1px solid black;' >$newActionLog</td>
											</tr>";	
				}
			} else {
				$content		.= "<tr><td colspan='13' style='border-bottom: 1px solid black;'>No student record for $inp_student</td></tr>";
			}
		}
			
		// See if there is an advisor record for this call sign
		if ($gotStudent) {
			$sql				= "select a.call_sign,a.timezone_id,a.timezone_offset,b.sequence,b.level,b.semester 
									from $advisorTableName as a, $advisorClassTableName as b 
									where a.call_sign='$inp_student' 
									and a.call_sign=b.advisor_call_sign 
									and a.semester=b.semester 
									order by a.date_created, b.sequence";
			$wpw1_cwa_advisor		= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				if ($doDebug) {
					echo "Reading $advisorTableName, $advisorClassTableName table<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			} else {
				$numARows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "found $numARows rows in the tables<br />";
				}
				if ($numARows > 0) {
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$advisor_call_sign			= $advisorRow->call_sign;
						$advisor_timezone_id		= $advisorRow->timezone_id;
						$advisor_timezone_offset	= $advisorRow->timezone_offset;
						$advisor_level 				= $advisorRow->level;
						$advisor_semester 			= $advisorRow->semester;
				
						if ($firstAdvisor) {
							$content	.= "<tr>
												<td colspan='13'><b>Advisor Info</b></td>
											</tr><tr>
												<th>TZ</th>
												<th>Level</th>
												<th>Semester</th>
												<th colspan='10'>&nbsp;</th>
											</tr>";
							$firstAdvisor = FALSE;
						}
						$content		.= "<tr>
												<td>$advisor_timezone_id $advisor_timezone_offset</td>
												<td>$advisor_level</td>
												<td>$advisor_semester</td>
												<td colspan='10'>&nbsp;</td>
											</tr><tr>
												<td colspan='13'><hr></td>
											</tr>";
				
				
					}  	// end of the while loop
				} else {		// end of the numberRecords section
					$content			.= "<tr><td colspan='13'>No advisor records for $inp_student</td></tr>";
				}
				$content		.= "</table>";
			}
		}

		$content					.= "<h3>Self Assessment Information for $inp_student</h3>
										<h4>Method 1 Assessments</h4>
										<table style='width:auto;'>
										<tr><th>Score</th>
											<th>Level</th>
											<th>Date</th>
											<th>Program</th></tr>";
		$sql						= "select * from $audioAssessmentTableName 
										where call_sign='$inp_student' 
										order by assessment_date";
		$wpw1_cwa_audio_assessment	= $wpdb->get_results($sql);
		if ($wpw1_cwa_audio_assessment === FALSE) {
			if ($doDebug) {
				echo "Reading wpw1_cwa_audio_assessment table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numAARows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numAARows rows from wpw1_cwa_audio_assessment table<br />";
			}
			if ($numAARows > 0) {
				$myCount	= 0;
				$prev_level								= '';
				$prev_clip_name							= '';
				$prev_clip_score						= '';
				foreach ($wpw1_cwa_audio_assessment as $assessmentRow) {
					$assessment_ID						= $assessmentRow->record_id;
					$assessment_call_sign				= strtoupper($assessmentRow->call_sign);
					$assessment_assessment_date			= $assessmentRow->assessment_date;
					$assessment_level					= $assessmentRow->assessment_level;
					$assessment_clip_name				= $assessmentRow->assessment_clip_name;
					$assessment_clip					= $assessmentRow->assessment_clip;
					$assessment_score					= $assessmentRow->assessment_score;
					$assessment_notes					= $assessmentRow->assessment_notes;
					$assessment_program					= $assessmentRow->assessment_program;

					if (array_key_exists($assessment_score,$scoreConversion)) {
						$convertedScore					= $scoreConversion[$assessment_score];
					} else {
						$convertedScore					= $assessment_score;
					}
					$content							.= "<tr><td style='text-align:center;vertical-align:top;'>$convertedScore</td>
																<td style='vertical-align:top;'>$assessment_level</td>
																<td style='vertical-align:top;'>$assessment_assessment_date</td>
																<td style='vertical-align:top;'>$assessment_program</td></tr>";			
					$myCount++;
				}
			} else {
				$content	.= "<tr><td colspan='3'>No Assessments</td></tr>";
			}
			$content		.= "</table>";


			// now get the data from the new assessment data table
			$bestResultBeginner		= 0;
			$didBeginner			= FALSE;
			$bestResultFundamental	= 0;
			$didFundamental			= FALSE;
			$bestResultIntermediate	= 0;
			$didIntermediate		= FALSE;
			$bestResultAdvanced		= 0;
			$didAdvanced			= FALSE;
			$retVal					= displayAssessment($inp_student,'',$doDebug);
// echo "retVal:<br /><pre>";
// print_r($retVal);
// echo "</pre><br />";
			if ($retVal[0] === FALSE) {
				if ($doDebug) {
					echo "displayAssessment returned FALSE. Called with $inp_callsign, $inp_token<br />";
				}
				$content			.= "No data to display.<br />Reason: $retVal[1]";
			} else {
				$content			.= $retVal[1];
				if ($doDebug) {
					echo "returned data: $retVal[2]<br />";
				}
				$myArray			= explode("&",$retVal[2]);
				foreach($myArray as $thisValue) {
					$myArray1		= explode("=",$thisValue);
					$thisKey		= $myArray1[0];
					$thisData		= $myArray1[1];
					$$thisKey		= $thisData;
					if ($doDebug) {
						echo "$thisKey = $thisValue<br />";
					}
				}
				$content		.= "<p>YThe Morse Code Proficiency 
									Assessment Results:<br />";
				if ($didBeginner) {
					$content	.= "The highest Beginner Level assessment score was $bestResultBeginner<br />";
				}
				if ($didFundamental) {
					$content	.= "The highest Fundamental Level assessment score was $bestResultFundamental<br />";
				}
				if ($didIntermediate) {
					$content	.= "The highest Intermediate Level assessment score was $bestResultIntermediate<br />";
				}
				if ($didAdvanced) {
					$content	.= "The highest Advanced Level assessment score was $bestResultAdvanced<br />";
				}
			
			}
		}
		$content		.= "<p>click <a href='$url'>here</a> to show another student's history<br />
							Click <a href='$studentManagementURL'>here</a> to return to Student Management</p>";
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
	$result			= write_joblog_func("Show Detailed History for a Student|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('show_detailed_history_for_student', 'show_detailed_history_for_student_func');
