function show_detailed_history_for_student_func() {

// Modified 1Oct22 by Roland for new timezone fields
// Modified 17Apr23 by Roland to fix action_log
// Modified 14Jul23 by Roland to use consolidated tables
// Modified 18Jan24 by Roland to show user_login information
// Modified 24Sep24 by Roland for new database
// Modified 24Oct24 by Roland for new database

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$validUser = $context->validUser;
	$userName  = $context->userName;
	$siteURL			= $context->siteurl;
	
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
	$inp_student				= "";
	$firstTime					= TRUE;
	$beginning					= TRUE;
	$firstAdvisor				= TRUE;
	$firstPastAdvisor			= TRUE;
	$gotStudent					= FALSE;
	$url						= "$siteURL/cwa-show-detailed-history-for-student/";
	$studentManagementURL		= "$siteURL/cwa-student-management/";
	$jobname					= "Show Detailed History for Student";

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
	
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$studentTableName			= "wpw1_cwa_student2";
		$advisorTableName			= "wpw1_cwa_advisor2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment2";
		$newAssessmentData			= "wpw1_cwa_new_assessment_data2";
		$userTableName				= "wpw1_users";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$studentTableName			= "wpw1_cwa_student";
		$advisorTableName			= "wpw1_cwa_advisor";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment";
		$newAssessmentData			= "wpw1_cwa_new_assessment_data";
		$userTableName				= "wpw1_users";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>$jobname</h3>
							<p><form method='post' action='$url' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td style='width:200px;'>Student Call Sign</td>
								<td><input type='text' class='formInputText' name='inp_student' size='15' maxlength='15' autofocus></td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
							</table></form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2 with $inp_student<br />";
		}
		$doProceed		= TRUE;
		$content		.= "<h3>$jobname $inp_student</h3>";

		// get the user information and display it
		$sql			= "select * from $userMasterTableName 
							where user_call_sign = '$inp_student'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$doProceed	= FALSE;
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_callsign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_whatsapp			= $sqlRow->user_whatsapp;
					$user_telegram			= $sqlRow->user_telegram;
					$user_signal			= $sqlRow->user_signal;
					$user_messenger			= $sqlRow->user_messenger;
					$user_action_log		= $sqlRow->user_action_log;
					$user_timezone_id		= $sqlRow->user_timezone_id;
					$user_languages			= $sqlRow->user_languages;
					$user_survey_score		= $sqlRow->user_survey_score;
					$user_is_admin			= $sqlRow->user_is_admin;
					$user_role				= $sqlRow->user_role;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;
	
					$countrySQL				= "select * from wpw1_cwa_country_codes  
												where country_code = '$user_country_code'";
					$countrySQLResult		= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$user_country		= "UNKNOWN";
						$user_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$user_country		= $countryRow->country_name;
								$user_ph_code		= $countryRow->ph_code;
							}
						} else {
							$user_country			= "Unknown";
							$user_ph_code			= "";
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "no user_master record found for $inp_student<br />";
				}
				$content			.= "<p>No user master record found for $inp_student</p>";
				$doProceed			= FALSE;
			}
			if ($doProceed) {
				$user_myStr				= formatActionLog($user_action_log);
				$content	.= "<table style='width:900px;'>
								<tr><td><b>Callsign<br />$user_callsign</b></td>
									<td><b>Name</b><br />$user_last_name, $user_first_name</td>
									<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
									<td><b>Email</b><br />$user_email</td></tr>
								<tr><td><b>City</b><br />$user_city</td>
									<td><b>State</b><br />$user_state</td>
									<td><b>Zip Code</b><br />$user_zip_code</td>
									<td><b>Country</b><br />$user_country</td></tr>
								<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
									<td><b>Telegram</b><br />$user_telegram</td>
									<td><b>Signal</b><br />$user_signal</td>
									<td><b>Messenger</b><br />$user_messenger</td></tr>
								<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
									<td><b>Languages</b><br />$user_languages</td>
									<td><b>Date Created</b><br />$user_date_created</td>
									<td><b>Date Updated</b><br />$user_date_updated</td></tr>
								<tr><td colspan='4'><b>Action Log</b><br />$user_myStr</td></tr>
								</table>";
		
				$content		.= "<h4>Student History</h4>
									<table style='width:1000px;'>
									<tr><th style='width:90px;'>Semester</th>
										<th style='width:90px;'>Level</th>
										<th style='width:50px;'>Response</th>
										<th style='width:50px;'>Status</th>
										<th style='width:50px;'>Pre-Advisor</th>
										<th style='width:50px;'>Advisor</th>
										<th style='width:50px;'>Class</th>
										<th style='width:50px;'>Prom</th>
										<th style='width:40px;'>IR</th>
										<th style='width:40px;'>HRC</th>
										<th style='width:40px;'>HO</th>
										<th>Exc Advisor</th>
									</tr>";
		
				// get the student info
				$sql				= "select * from $studentTableName 
										where student_call_sign='$inp_student' 
										order by student_date_created";
				$wpw1_cwa_student				= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numSRows		= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br />";
					}
					if ($numSRows > 0) {
						foreach ($wpw1_cwa_student as $studentRow) {
							$student_ID								= $studentRow->student_id;
							$student_call_sign						= $studentRow->student_call_sign;
							$student_time_zone  					= $studentRow->student_time_zone;
							$student_timezone_offset				= $studentRow->student_timezone_offset;
							$student_youth  						= $studentRow->student_youth;
							$student_age  							= $studentRow->student_age;
							$student_parent 				= $studentRow->student_parent;
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
		
							if ($doDebug) {
								echo "Got a student record for $inp_student<br />";
							}
							$gotStudent			= TRUE;
							if ($beginning) {
								$content		.= "<tr><td colspan='13'><b>Name: </b>$user_last_name, $user_first_name ($user_callsign)</td></tr>";
								$beginning		= FALSE;
							}
							$newActionLog		= formatActionLog($student_action_log);
							$content			.= "<tr><td style='vertical-align:top;'>$student_semester</td>
														<td style='vertical-align:top;'>$student_level</td>
														<td style='vertical-align:top;'>$student_response</td>
														<td style='vertical-align:top;'>$student_status</td>
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
						$content		.= "</table>";
					} else {
						$content		.= "<tr><td colspan='13' style='border-bottom: 1px solid black;'>No student record for $inp_student</td></tr>";
					}
				}
			}
		}
			
		// See if there are advisorClass records for this call sign
		if ($gotStudent) {
			// get the advisorClass records, if any
			$sql					= "select * from $advisorClassTableName 
									   where advisorclass_call_sign='$inp_student' 
									   order by advisorclass_date_created";
			$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisorclass === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numACRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numACRows rows<br />";
				}
				if ($numACRows > 0) {
					$content		.= "<h4>Advisor Records</h4>
										<table>
										<tr><th>Semester</th>
											<th>Level</th>
											<th>Students</th>
											<th>Eval Complete</th></tr>";
					foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
						$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
						$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
						$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
						$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
						$advisorClass_level 					= $advisorClassRow->advisorclass_level;
						$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
						$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
						$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
						$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
						$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
						$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
						$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
						$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
						$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
						$advisorClass_student01 				= $advisorClassRow->advisorclass_student01;
						$advisorClass_student02 				= $advisorClassRow->advisorclass_student02;
						$advisorClass_student03 				= $advisorClassRow->advisorclass_student03;
						$advisorClass_student04 				= $advisorClassRow->advisorclass_student04;
						$advisorClass_student05 				= $advisorClassRow->advisorclass_student05;
						$advisorClass_student06 				= $advisorClassRow->advisorclass_student06;
						$advisorClass_student07 				= $advisorClassRow->advisorclass_student07;
						$advisorClass_student08 				= $advisorClassRow->advisorclass_student08;
						$advisorClass_student09 				= $advisorClassRow->advisorclass_student09;
						$advisorClass_student10 				= $advisorClassRow->advisorclass_student10;
						$advisorClass_student11 				= $advisorClassRow->advisorclass_student11;
						$advisorClass_student12 				= $advisorClassRow->advisorclass_student12;
						$advisorClass_student13 				= $advisorClassRow->advisorclass_student13;
						$advisorClass_student14 				= $advisorClassRow->advisorclass_student14;
						$advisorClass_student15 				= $advisorClassRow->advisorclass_student15;
						$advisorClass_student16 				= $advisorClassRow->advisorclass_student16;
						$advisorClass_student17 				= $advisorClassRow->advisorclass_student17;
						$advisorClass_student18 				= $advisorClassRow->advisorclass_student18;
						$advisorClass_student19 				= $advisorClassRow->advisorclass_student19;
						$advisorClass_student20 				= $advisorClassRow->advisorclass_student20;
						$advisorClass_student21 				= $advisorClassRow->advisorclass_student21;
						$advisorClass_student22 				= $advisorClassRow->advisorclass_student22;
						$advisorClass_student23 				= $advisorClassRow->advisorclass_student23;
						$advisorClass_student24 				= $advisorClassRow->advisorclass_student24;
						$advisorClass_student25 				= $advisorClassRow->advisorclass_student25;
						$advisorClass_student26 				= $advisorClassRow->advisorclass_student26;
						$advisorClass_student27 				= $advisorClassRow->advisorclass_student27;
						$advisorClass_student28 				= $advisorClassRow->advisorclass_student28;
						$advisorClass_student29 				= $advisorClassRow->advisorclass_student29;
						$advisorClass_student30 				= $advisorClassRow->advisorclass_student30;
						$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
						$advisorClass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
						$advisorClass_class_comments			= $advisorClassRow->advisorclass_class_comments;
						$advisorClass_copycontrol				= $advisorClassRow->advisorclass_copy_control;
		
						$newActionLog	= formatActionLog($advisor_action_log);
						$content				.= "<tr><td>$advisorClass_semester</td>
														<td>$advisorClass_level</td>
														<td style='text-align:center;'>$advisorClass_number_students</td>
														<td style='text-align:center;'>$advisorClass_evaluation_complete</td></tr>
													<tr><td colspan='4'>$newActionLog</td></tr>";
					}						/// end of advisorClass while
					$content		.= "</table>";
				} else {					/// no advisorClass records
					$content		.= "<p>No advisorClass records</p>";
				}
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
			handleWPDBError($jobname,$doDebug);
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
				$content		.= "<p>The Morse Code Proficiency 
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

