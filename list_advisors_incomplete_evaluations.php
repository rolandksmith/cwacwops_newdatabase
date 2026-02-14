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
 	Modified 19Oct24 by Roland for new database
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
	$validUser = $context->validUser;
	$userName  = $context->userName;
	$validTestmode		= $context->validTestmode;
	$siteURL			= $context->siteurl;

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
	$inp_advisor				= '';
	$advisorArray				= array();
	$pastAdvisorErrors			= 0;
	$notEvaluatedCount			= 0;
	$advisorsDue				= 0;
	$theURL						= "$siteURL/cwa-list-advisors-with-incomplete-evaluations/";
	$advisorCount				= 0;
	$unevaluatedArray			= array();
	$jobname					= "List Advisors with Incomplete Evaluations";

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

	
	
	
	$content = "";	

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
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$studentTableName			= 'wpw1_cwa_student2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$content					.= "<p><b>Operating in test mode</b></p>";
	} else {
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$advisorTableName			= 'wpw1_cwa_advisor';
		$studentTableName			= 'wpw1_cwa_student';
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		
			$content 		.= "<h3>$jobname</h3>
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
	
		$currentSemester		= $context->currentSemester;
		$prevSemester			= $context->prevSemester;
		$theSemester			= $currentSemester;
		if ($currentSemester == 'Not in Session') {
			$theSemester		= $prevSemester;
		}

		$totalStudents			= 0;
		$totalEvaluated			= 0;
		$prevCallSign			= '';

		$content			.= "<h3>$jobname for $theSemester</h3>
								<table>
								<tr><th>Advisor</th>
									<th>Name</th>
									<th>Sequence</th>
									<th>Time Zone</th>
									<th>Level</th>
									<th>Nmbr Students</th>
									<th>Nmbr Eval</th>
									<th>Unevaluated Students</th></tr>";
		
		$sql							= "select * from $advisorClassTableName 
											left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
											where advisorclass_semester='$theSemester' 
											order by advisorclass_call_sign, 
													 advisorclass_sequence";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_master_ID 				= $advisorClassRow->user_ID;
					$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
					$advisorClass_first_name 				= $advisorClassRow->user_first_name;
					$advisorClass_last_name 				= $advisorClassRow->user_last_name;
					$advisorClass_email 					= $advisorClassRow->user_email;
					$advisorClass_phone 					= $advisorClassRow->user_phone;
					$advisorClass_city 						= $advisorClassRow->user_city;
					$advisorClass_state 					= $advisorClassRow->user_state;
					$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
					$advisorClass_country_code 				= $advisorClassRow->user_country_code;
					$advisorClass_whatsapp 					= $advisorClassRow->user_whatsapp;
					$advisorClass_telegram 					= $advisorClassRow->user_telegram;
					$advisorClass_signal 					= $advisorClassRow->user_signal;
					$advisorClass_messenger 				= $advisorClassRow->user_messenger;
					$advisorClass_master_action_log 		= $advisorClassRow->user_action_log;
					$advisorClass_timezone_id 				= $advisorClassRow->user_timezone_id;
					$advisorClass_languages 				= $advisorClassRow->user_languages;
					$advisorClass_survey_score 				= $advisorClassRow->user_survey_score;
					$advisorClass_is_admin					= $advisorClassRow->user_is_admin;
					$advisorClass_role 						= $advisorClassRow->user_role;
					$advisorClass_master_date_created 		= $advisorClassRow->user_date_created;
					$advisorClass_master_date_updated 		= $advisorClassRow->user_date_updated;

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


					// if you need the country name and phone code, include the following
					$countrySQL		= "select * from wpw1_cwa_country_codes  
										where country_code = '$advisorClass_country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$advisorClass_country		= "UNKNOWN";
						$advisorClass_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$advisorClass_country		= $countryRow->country_name;
								$advisorClass_ph_code		= $countryRow->ph_code;
							}
						} else {
							$advisorClass_country			= "Unknown";
							$advisorClass_ph_code			= "";
						}
					}

					if ($doDebug) {
						echo "<br />Processing advisorClass record $advisorClass_call_sign sequence $advisorClass_sequence with evaluation complete = $advisorClass_class_evaluation_complete<br />";
					}
					if ($advisorClass_call_sign != $prevCallSign) {
						$thisCallSign						= '';
						$thisName							= '';
						if ($doDebug) {
							echo "blanking out thisCallSign<br />";
						}
					}
					$prevCallSign							= $advisorClass_call_sign;
					if ($advisorClass_survey_score != 6) {
	
						$thisCallSign						= $advisorClass_call_sign;
						$thisName							= "$advisorClass_last_name, $advisorClass_first_name";
						$studentCount						= 0;
						$studentEval						= 0;
						if ($doDebug) {
							echo "Got advisor $advisorClass_call_sign sequence $advisorClass_sequence students<br />";
						}
						$unevaluatedStudents		= "";
						for ($snum=1;$snum<=$advisorClass_number_students;$snum++) {
							if ($snum < 10) {
								$strSnum 			= str_pad($snum,2,'0',STR_PAD_LEFT);
							} else {
								$strSnum			= strval($snum);
							}
							$studentCallSign		= ${'advisorClass_student' . $strSnum};
							if ($studentCallSign != '') {
								$studentCount++;
	//							$totalStudents++;
							
								// count number of students and number evaluated
								$sql					= "select * from $studentTableName 
															where student_semester='$theSemester' 
															and student_call_sign = '$studentCallSign'";
								$wpw1_cwa_student	= $wpdb->get_results($sql);
								if ($wpw1_cwa_student === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$numPSRows									= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $sql<br />and found $numPSRows rows in $studentTableName table<br />";
									}
									if ($numPSRows > 0) {
										foreach ($wpw1_cwa_student as $studentRow) {
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
											
											if ($doDebug) {
												echo "<br />processing past student $student_call_sign with promotable of $student_promotable<br />";
											}
											if ($student_promotable == 'P' || $student_promotable == 'N' || $student_promotable == 'W') {
												$studentEval++;
												if ($doDebug) {
													echo "counts: studentEval: $studentEval<br />";
												}
											} else {
												$unevaluatedStudents	.= " $student_call_sign";
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
							$thisLink		= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$thisCallSign&inp_depth=one&doDebug&testMode' target='_blank'>$thisCallSign</a>";
							$content		.= "<tr><td>$thisLink</td>
													<td>$thisName</td>
													<td>$advisorClass_sequence</td>
													<td>$advisorClass_timezone_id $advisorClass_timezone_offset</td>
													<td>$advisorClass_level</td>
													<td>$studentCount</td>
													<td>$studentEval</td>
													<td>$unevaluatedStudents</td></tr>";
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
add_shortcode ('list_advisors_incomplete_evaluations', 'list_advisors_incomplete_evaluations_func');


