function view_a_student_cw_assessment_func() {

/*
	modified 2Oct2022 by Roland for the new timezone table format
	modified 16Jul23 by Roland to use consolidated tables
	Modified 25Sep24 by Roland for new database and to provide a list 
	of advisor's students
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$validUser 			= $context->validUser;
	$userName			= $context->userName;
	$validTestmode		= $context->validTestmode;
	$siteURL			= $context->siteurl;
	$currentSemester	= $context->currentSemester;
	$proximateSemester	= $context->proximateSemester;
	$nextSemester		= $context->nextSemester;
	
// must be a logged-in user
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";		
	}
	
//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
	$scoreConversion			= array('0'=>'0%',
										'50'=>'0-49%',
										'75'=>'50-89%',
										'90'=>'90+%',
										'100'=>'100%');

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-view-a-student-assessment/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$inp_callsign				= '';
	$advisor_call_sign			= '';
	$advisorCheck				= FALSE;
	$versionNumber				= '3';
	$jobname					= "View a Student CW Assessment";
	$token						= '';
	$inp_advisor				= '';
	$inp_token					= '';

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
			if ($str_key 				== 'enstr') {
				$enstr					= $str_value;
				$stringToPass			= base64_decode($enstr);
				$myArray				= explode("&",$stringToPass);
				foreach($myArray as $myValue) {
					$thisArray			= explode("=",$myValue);
					${$thisArray[0]}	= $thisArray[1];
					if ($doDebug) {
						echo "Key: $thisArray[0] | Value: $thisArray[1]<br />";
					}
				}
				$advisorCheck			= TRUE;
				if ($doDebug) {
					echo "strPass: $strPass<br />";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign		 = $str_value;
				$inp_callsign		 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
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
			if ($str_key 		== "inp_id") {
				$inp_id	 = $str_value;
				$inp_id	 = filter_var($inp_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_advisor") {
				$advisor_call_sign	 = $str_value;
				$advisor_call_sign	 = filter_var($advisor_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token	 = $str_value;
				$token	 = filter_var($token,FILTER_UNSAFE_RAW);
			}
		}
	}
//	if (!$advisorCheck) {	
//		if ($validUser == "N") {
//			return "YOU'RE NOT AUTHORIZED!<br />Goodby";
//		}
//	}


	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
								<td colspan = '3'><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
												  <input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td colspan = '3'><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
												  <input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
	}
	
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment2";
		$studentTableName			= "wpw1_cwa_student2";
		$newAssessmentData			= "wpw1_cwa_new_assessment_data2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$extMode					= 'pd';
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment";
		$studentTableName			= "wpw1_cwa_student";
		$newAssessmentData			= "wpw1_cwa_new_assessment_data";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}


if ($doDebug) {
	echo "doDebug is set to TRUE<br />";
}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting.<br />";
		}
		
		$doProceed		= TRUE;
		$firstTime		= TRUE;
		$slot			= 0;
		$studentList	= "";
		
		// format a list of students in the advisor's class(es)
		if ($doDebug) {
			echo "getting students for advisor $userName<br />";
		}
		$sql			= "select * from $studentTableName 
							left join $userMasterTableName on user_call_sign = student_call_sign 
							where student_semester = '$proximateSemester' 
								and student_assigned_advisor = '$userName' 
								order by student_assigned_advisor_class, 
										 student_call_sign";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$doProceed		= FALSE;
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

					if ($firstTime) {
						$studentList	.= "<tr><th colspan='4'>Students in Class $student_assigned_advisor_class ($student_level)</th></tr>";
						$firstTime		= FALSE;
						$prevSequence	= $student_assigned_advisor_class;
						$slot			= 0;
					}
					if ($prevSequence != $student_assigned_advisor_class) {
						$prevSequence	= $student_assigned_advisor_class;

						// fix up slots
						switch ($slot) {
							case 0:
								break;
							case 1:
								$studentList	.= "<td></td><td></td><td></td></tr>";
								break;
							case 2;
								$studentList	.= "<td></td><td></td></tr>";
								break;
							case 3:
								$studentList	.= "<td></td></tr>";
								break;
						}

						$studentList	.= "<tr><td colspan='4'><hr></td></tr>
											<tr><th colspan='4'>Students in Class $student_assigned_advisor_class ($student_level)</th></tr>";
						$slot			= 0;
					}
					switch ($slot) {
						case 0:
							$studentList	.= "<tr><td><input type='radio' class='formInputButton' name='inp_callsign' value='$student_call_sign'>$student_last_name, $student_first_name ($student_call_sign)</td>";
							$slot++;
							break;
						case 1:
							$studentList	.= "<td><input type='radio' class='formInputButton' name='inp_callsign' value='$student_call_sign'>$student_last_name, $student_first_name ($student_call_sign)</td>";
							$slot++;
							break;
						case 2:
							$studentList	.= "<td><input type='radio' class='formInputButton' name='inp_callsign' value='$student_call_sign'>$student_last_name, $student_first_name ($student_call_sign)</td>";
							$slot++;
							break;
						case 3:
							$studentList	.= "<td><input type='radio' class='formInputButton' name='inp_callsign' value='$student_call_sign'>$student_last_name, $student_first_name ($student_call_sign)</td></tr>";
							$slot		= 0;
							break;
					}
				}
				// fix up slots
				switch ($slot) {
					case 0:
						break;
					case 1:
						$studentList	.= "<td></td><td></td><td></td></tr>";
						break;
					case 2;
						$studentList	.= "<td></td><td></td></tr>";
						break;
					case 3:
						$studentList	.= "<td></td></tr>";
						break;
				}

				$studentList	.= "<tr><td colspan='4'><hr></td></tr>";

			} else {
				if ($doDebug) {
					echo "no students found for this advisor<br />";
				}
				$content		.= "<p>No students found</p>";
				$doProceed		= FALSE;
			}	
		}			
		if ($doProceed) {
			$content 		.= "<h3>$jobname</h3>
								<p>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='2'>
								<table style='border-collapse:collapse;'>
								<tr><td colspan='4'><b>Select a Student's Call Sign</b></td></tr>
								$studentList
								$testModeOption
								<tr><td colspan='4'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form></p>";
		}		
		
///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2 with inp_callsign: $inp_callsign<br />";
		}

		// get the person's name from user_master
		$sql				= "select * from $userMasterTableName 
								where user_call_sign = '$inp_callsign'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$user_last_name				= "Unknown";
			$user_first_name			= "";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_call_sign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_ph_code			= $sqlRow->user_ph_code;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_country			= $sqlRow->user_country;
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
					$user_prev_callsign		= $sqlRow->user_prev_callsign;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;
				}
			} else {
				$user_last_name				= "Unknown";
				$user_first_name			= "";
			}
		}
		$advisorOK				= TRUE;
		if ($advisorOK) {
			if ($doDebug) {
				echo "Advisor check OK<br />";
			}
			$content					.= "<h3>Self Assessment Information for $user_last_name, $user_first_name ($inp_callsign)</h3>
											<h4>Method 1 Assessments</h4>
											<table style='width:auto;'>
											<tr><th>Score</th>
												<th>Level</th>
												<th>Date</th>
												<th>Program</th></tr>";
			$sql						= "select * from $audioAssessmentTableName 
											where call_sign='$inp_callsign' 
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

//						$convertedScore						= $scoreConversion[$assessment_score];
						$content							.= "<tr><td style='text-align:center;vertical-align:top;'>$assessment_score</td>
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
				$content				.= "<h4>Method 2 Assessments</h4>";
				$bestResultBeginner		= 0;
				$didBeginner			= FALSE;
				$bestResultFundamental	= 0;
				$didFundamental			= FALSE;
				$bestResultIntermediate	= 0;
				$didIntermediate		= FALSE;
				$bestResultAdvanced		= 0;
				$didAdvanced			= FALSE;
				$retVal					= displayAssessment($inp_callsign,'',$doDebug);

// if ($doDebug) {
// echo "retVal:<br /><pre>";
// print_r($retVal);
// echo "</pre><br />";
// }

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
				$content	.= "<p><b>Explanation:</b></p>
								<p><b>Method 1 Assessments:</b><br />
								This method was introduced in the spring of 2022. Students 
								were given an assessment during the registration process. An 
								audio clip played and the student indicated how much of the 
								Morse code was understood. The student could select three options:<br />
								Less than half<br />
								More than half but less than 90%<br />
								More than 90%</p>
								<p>That assessment method was later expanded to allow advisors to request 
								students to take an assessment at the end of the semester.</p>
								<p>While this process significantly reduced the number of students 
								registering for the wrong level, the process could be improved.</p>
								<p><b>Method 2 Assessments:</b><br />
								This method was introduced in October, 2023. Rather than having the student 
								'guestimate' at how much was understood, this method gives the student 
								a number of two-word questions in Morse code and then displays five 
								options for the answer. The student garners points for each word 
								correctly selected.</p>";
								
				// if there is a reminder, resolve it
				if ($token != '') {
					$myStr				= strtoupper($userName);
					$resolveResult		= resolve_reminder($myStr,$token,$testMode,$doDebug);
					if ($resolveResult === FALSE) {
						if ($doDebug) {
							echo "resolve_reminder for $inp_callsign and $token failed<br />";
						}
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "Advisor check failed<br />";
			}
		}


	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass with assessment_id of $inp_id<br />";
		}
		$sql						= "select * from $audioAssessmentTableName 
										where record_id = $inp_id";
		$wpw1_cwa_audio_assessment	= $wpdb->get_results($sql);
		if ($wpw1_cwa_audio_assessment === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numAARows				= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numAARows rows from wpw1_cwa_audio_assessment table<br />";
			}
			if ($numAARows > 0) {
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
	
					$convertedScore	= $scoreConversion[$assessment_score];
					$content		.= "<h3>$jobname</h3>
										<h4>Assessment Details for $assessment_call_sign</h4>
										<p>Assessment initiated by $assessment_program<br >
										Assessment Date: $assessment_assessment_date<br />
										Assessment Level: $assessment_level<br />
										Student listened to a Morse code audio clip and claimed ability to copy $convertedScore of the clip<br />
										Outcome: $assessment_notes<br /></p>
										<p>You may close this window / tab</p>";
				}
			} else {
				$content		.= "Program error. There should be some data here!";
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
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}
	return $content;
}
add_shortcode ('view_a_student_cw_assessment', 'view_a_student_cw_assessment_func');

