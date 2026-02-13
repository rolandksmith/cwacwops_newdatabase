function list_student_self_assessments_func() {

// modified 1Oct22 by Roland for new timezone process fields
// modified 13Jul23 by Roland to use consolidated tables
// Modified 24Oct23 by Roland to use new assessment file
// Modified 19Oct24 by Roland for new database

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
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	
//	CHECK THIS!								//////////////////////
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

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-list-student-self-assessments/";
	$inp_timeframe				= '';
	$scoreConversion			= array('50'=>'0-49%',
										'75'=>'50-89%',
										'90'=>'90+%');
	$firstTime					= TRUE;
	$assessmentCallSignArray	= array();
	$ACSCount					= 0;
	$studentArray				= array();
	$SCount						= 0;
	$studentDataArray			= array();
	$SDACount					= 0;
	$studentsDisplayed			= 0;
	$jobname					= "List Student Self Assessments";
	$levelConvert				= array('Beginner'=>1,
										'Fundamental'=>2,
										'Intermediate'=>3,
										'Advanced'=>4);
	$levelBack					= array(1=>'Beginner',
										2=>'Fundamental',
										3=>'Intermediate',
										4=>'Advanced');

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
			if ($str_key 		== "inp_timeframe") {
				$inp_timeframe		 = $str_value;
				$inp_timeframe		 = filter_var($inp_timeframe,FILTER_UNSAFE_RAW);
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
	
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$assessmentTableName		= "wpw1_cwa_new_assessment_data2";
		$studentTableName			= "wpw1_cwa_student2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$extMode					= 'pd';
		$assessmentTableName		= "wpw1_cwa_new_assessment_data";
		$studentTableName			= "wpw1_cwa_student";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content	.= "<h3>$jobname</h3>
						<p>Select a time period from the list and submit. The program will then prepare a 
						report of all students who registered during that time period and their self 
						assessment information from the assessment log. The report will only include 
						student who have taken the self assessment.
						<form method='post' action='$theURL' 
						name='selection_form' ENCTYPE='multipart/form-data'>
						<input type='hidden' name='strpass' value='2'>
						<table style='border-collapse:collapse;'>
						<tr><td>Time Period of Interest</td>
							<td><input type='radio' class='formInputButton' name='inp_timeframe' value='past24' checked > Yesterday and Today<br />
								<input type='radio' class='formInputButton' name='inp_timeframe' value='3days' > Past 3 days<br />
								<input type='radio' class='formInputButton' name='inp_timeframe' value='week' > Past Week<br />
								<input type='radio' class='formInputButton' name='inp_timeframe' value='all' > All Students</td></tr>
						$testModeOption
						<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
						</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 2 with timeframe of $inp_timeframe<br />";
		}
		
		$prevCallsign		= "";
		
		$content			.= "<h3>$jobname</h3><table>";		
		
		$today				= date('Y-m-d H:i:s');
		if ($inp_timeframe == "past24") {
			$thisTime		= strtotime("$today - 1 day");
			$fromDate		= date('Y-m-d H:i:s',$thisTime);
		} elseif ($inp_timeframe == '3days') {
			$thisTime		= strtotime("$today - 3 days");
			$fromDate		= date('Y-m-d H:i:s',$thisTime);
		} elseif ($inp_timeframe == 'week') {
			$thisTime		= strtotime("$today - 1 week");
			$fromDate		= date('Y-m-d H:i:s',$thisTime);
		} else {
			$fromDate		= '2001-01-01 00:00:00';
		}
		if ($doDebug) {
			echo "Today is $today. Using records with an assessment date from $fromDate<br />";
		}

		// get all assessment records meeting the date criteria
		$sql					= "select * from $assessmentTableName 
									where date_written >= '$fromDate' 
									and date_written <= '$today'  
									and callsign != '' 
									order by callsign, date_written";
		$assessmentResult		= $wpdb->get_results($sql);
		if ($assessmentResult === FALSE) {
			$thisError			= $wpdb->last_error;
			if ($doDebug) {
				echo "attempting to read from wpw1_cwa_new_assessment_data table failed. Error: $lastError<br />SQL: $sql<br />";
			}
//				sendErrorEmail("$jobname Pass 104 Attempting to read from wpw1_cwa_new_assessment_data failed. Error: $lastError. SQL: $sql");
			$doProceed			= FALSE;
		} else {
			$numASRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numASRows rows<br />";
			}
			if ($numASRows > 0) {
				foreach($assessmentResult as $newAssessment) {				
					$record_id		= $newAssessment->record_id;
					$thisCallsign	= $newAssessment->callsign;
					$thisLevel		= $newAssessment->level;
					$thiscpm		= $newAssessment->cpm;
					$thiseff		= $newAssessment->eff;
					$thisfreq		= $newAssessment->freq;
					$thisquestions	= $newAssessment->questions;
					$thiswords		= $newAssessment->words;
					$thischars		= $newAssessment->characters;
					$thisScore		= $newAssessment->score;
					$thisDetail		= $newAssessment->details;
					$thisDate		= $newAssessment->date_written;


					if ($thisCallsign != $prevCallsign) {
						$prevCallsign	= $thisCallsign;
						// new student. Get the student info
						$haveStudent	= FALSE;
						$sql			= "select * from $studentTableName 
											left join $userMasterTableName on user_call_sign = student_call_sign 
											where student_call_sign = '$thisCallsign' 
											and student_response != 'R' 
											order by student_date_created DESC 
											limit 1";
						$wpw1_cwa_student	= $wpdb->get_results($sql);
						if ($wpw1_cwa_student === FALSE) {
							handleWPDBError($jobname,$doDebug);
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
									$student_ph_code					= $studentRow->user_ph_code;
									$student_phone 						= $studentRow->user_phone;
									$student_city 						= $studentRow->user_city;
									$student_state 						= $studentRow->user_state;
									$student_zip_code 					= $studentRow->user_zip_code;
									$student_country_code 				= $studentRow->user_country_code;
									$student_country 					= $studentRow->user_country;
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
									
									$haveStudent							= TRUE;
								}
							}
						}
						if ($haveStudent) {
							$studentsDisplayed++;
							$content				.= "<tr><th>Call Sign</th>
															<th colspan='2'>Name</th>
															<th colspan='2'>Email</th>
															<th>Phone</th>
															<th colspan='2'>Country</th>
															<th>Level</th>
															<th></th>
															<th></th></tr>
														<tr><td>$student_call_sign</td>
															<td colspan='2'>$student_last_name, $student_first_name</td>
															<td colspan='2'>$student_email</td>
															<td>$student_phone</td>
															<td colspan='2'>$student_country</td>
															<td>$thisLevel</td>
															<td></td>
															<td></td></tr>
													 	<tr><td style='vertical-align:bottom;border-bottom:solid;'><b>Score</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>Level</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>Char Speed</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>Eff Speed</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>Questions</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>Words</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>Word Length</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>Question</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>What Was Sent</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>What Was Copied</b></td>
															<td style='vertical-align:bottom;border-bottom:solid;'><b>Points Gained</b></td></tr>";
						
						}
					
					}
					$content		.= "<tr><td style='text-align:center;vertical-align:top;'>$thisScore</td>
											<td style='vertical-align:top;'>$thisLevel</td>
											<td style='text-align:center;vertical-align:top;'>$thiscpm</td>
											<td style='text-align:center;vertical-align:top;'>$thiseff</td>
											<td style='text-align:center;vertical-align:top;'>$thisquestions</td>
											<td style='text-align:center;vertical-align:top;'>$thiswords</td>
											<td style='text-align:center;vertical-align:top;'>$thischars</td>";

					$firstTime		= TRUE;					
					$detailsArray	= json_decode($thisDetail,TRUE);
					foreach($detailsArray as $thisKey => $thisValue) {
						$thisSent		= $thisValue['sent'];
						$thisCopied		= $thisValue['copied'];
						$thisPoints		= $thisValue['points'];
						if ($firstTime) {
							$firstTime	= FALSE;
							$content	.= "<td>Question $thisKey</td>
												<td>$thisSent</td>
												<td>$thisCopied</td>
												<td>$thisPoints</td></tr>\n";
						} else {
							$content		.= "<tr><td style='vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td>Question $thisKey</td>
													<td>$thisSent</td>
													<td>$thisCopied</td>
													<td>$thisPoints</td></tr>\n";
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "No assessment records found<br />";
				}
			}
		}
		$content			.= "</table><p>$studentsDisplayed Student records displayed</p>";
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
	$result			= write_joblog_func("List Student Self Assessments|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('list_student_self_assessments', 'list_student_self_assessments_func');

