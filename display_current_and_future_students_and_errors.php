function display_current_and_future_students_and_errors_func() {

/*	displays students by semester and callsign
	checks for and displays any errors found
	
	Forked from list_all_students on 17July2024 by Roland
	Modified 19Oct24 by Roland for new database
	
*/


	global $wpdb, $testMode, $doDebug, $advisorTableName, $printArray, $timeToBlock;

	
	$doDebug						= FALSE;
	$testMode						= FALSE;
	
	$initializationArray		 	= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 						= $initializationArray['validUser'];
	$userName  						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL						= $initializationArray['siteurl'];
	
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('max_execution_time',0);
	set_time_limit(0);

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-display-current-and-future-students-and-errors/";
	$jobname					= "Display Current and Future Students and Errors";
	$inpType					= '';

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
			if ($str_key 		== "inpType") {
				$inpType		 = $str_value;
				$inpType		 = filter_var($inpType,FILTER_UNSAFE_RAW);
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
	
	if ($testMode) {
		$studentTableName		= 'wpw1_cwa_student2';
		$advisorTableName		= 'wpw1_cwa_advisorew2';
		$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		if ($doDebug) {
			echo "Operating in TestMode<br />";
		}
		
	} else {
		$studentTableName		= 'wpw1_cwa_student';
		$advisorTableName		= 'wpw1_cwa_advisor';
		$advisorClassTableName	= 'wpw1_cwa_advisorclass';
		$userMasterTableName	= 'wpw1_cwa_user_master';
	}
	

	$doHeader						= TRUE;
	$headerInterval					= 10;
	$headerCount					= 0;
	$myUnixTime						= $initializationArray['currentTimestamp'];
	$totalStudents					= 0;
	$totalThisSemester				= 0;
	$prev_semester					= "xyz";
	$firstTime						= TRUE;
	$studentArray					= array();
	$ii								= 0;
	$validLevels					= array('Fundamental','Beginner','Intermediate','Advanced');
	$validResponses					= array('Y','R');
	$validStatuses					= array('Y','S','R','C','N');
	$validIntReq					= array('M','Q','H','A','R');
	$myIntRegex						= '/^(\+|-)?\d+$/';
	$issue01						= 0;
	$issue02						= 0;
	$issue03						= 0;
	$issue04						= 0;
	$issue05						= 0;
	$issue06						= 0;
	$issue07						= 0;
	$issue08						= 0;
	$issue09						= 0;
	$issue10						= 0;
	$issue11						= 0;
	$issue12						= 0;
	$issue13						= 0;
	$issue14						= 0;
	$issue15						= 0;
	$issue16						= 0;
	$issue17						= 0;
	$issue18						= 0;
	$issue19						= 0;
	$issue20						= 0;
	$issue21						= 0;
	$issue22						= 0;
	$issue23						= 0;
	$issue24						= 0;
	$issue25						= 0;
	$issue26						= 0;
	$issue27						= 0;
	$issue28						= 0;
	$issue29						= 0;
	$issue30						= 0;
	
	$issueTxt01						= 'Student has invalid level';
	$issueTxt02						= 'Student has no call sign';
	$issueTxt03						= 'Student has an invalid Response';
	$issueTxt04						= 'Student has an invalid Student Status';
	$issueTxt05						= 'Student has an invalid Timezone ID or Offset';
	$issueTxt06						= 'Student has no Request Date';
	$issueTxt07						= 'Student has an future Request Date';
	$issueTxt08						= 'Student should have a welcome Date';
	$issueTxt09						= 'Student has an empty first class choice';
	$issueTxt10						= 'Student\'s first class choice test failed';
	$issueTxt11						= 'Student\'s second class choice test failed';
	$issueTxt12						= 'Student\'s third class choice test failed';
	$issueTxt13						= 'None of student\'s class choices match an available class';
	$issueTxt14						= 'Student should not have a Response';
	$issueTxt15						= 'Student should not have a Student Status';
	$issueTxt16						= 'Student\'s assigned advisor should be blank';
	$issueTxt17						= 'Student\'s hold reason code should be blank';
	$issueTxt18						= 'Student\'s intervention required of should be blank';
	$issueTxt19						= 'Student has an unknown pre-assigned Advisor';
	$issueTxt20						= 'Student has an unknown Assigned Advisor';
	$issueTxt21						= 'Student has an unknown semester';
	$issueTxt22						= 'Student has an invalid Hold Reason Code';
	$issueTxt23						= 'Student has an invalid Student Intervention Required';
	$issueTxt24						= 'Student first class choice not in the catalog';
	$issueTxt25						= 'Student second class choice not in the catalog';
	$issueTxt26						= 'Student third class choice not in the catalog';
	$issueTxt27						= 'Student country and country_code do not match';
	$issueTxt28						= 'Student missing country information';
	$issueTxt29						= 'Student Phone Code invalid or incorrect';
	$issueTxt30						= 'Student has not verified';

	$issue01Array					= array();
	$issue02Array					= array();
	$issue03Array					= array();
	$issue04Array					= array();
	$issue05Array					= array();
	$issue06Array					= array();
	$issue07Array					= array();
	$issue08Array					= array();
	$issue09Array					= array();
	$issue10Array					= array();
	$issue11Array					= array();
	$issue12Array					= array();
	$issue13Array					= array();
	$issue14Array					= array();
	$issue15Array					= array();
	$issue16Array					= array();
	$issue17Array					= array();
	$issue18Array					= array();
	$issue19Array					= array();
	$issue20Array					= array();
	$issue21Array					= array();
	$issue22Array					= array();
	$issue23Array					= array();
	$issue24Array					= array();
	$issue25Array					= array();
	$issue26Array					= array();
	$issue27Array					= array();
	$issue28Array					= array();
	$issue29Array					= array();
	$issue30Array					= array();

	
	$validWelcomeDate				= strtotime('2019-11-30 23:59');
	$validFutureDate				= strtotime('2019-12-10 23:59');
	$prevSemester					= $initializationArray['prevSemester'];
	$currentSemester				= $initializationArray['currentSemester'];
	$nextSemester					= $initializationArray['nextSemester'];
	$semesterTwo					= $initializationArray['semesterTwo'];
	$semesterThree					= $initializationArray['semesterThree'];
	$semesterFour					= $initializationArray['semesterFour'];
	$updateStudentURL 				= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?request_type=callsign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2"; 
	if ($currentSemester == 'Not in Session') {
		$theSemester	= $prevSemester;
		$futureSemesters			= array($semesterTwo,$semesterThree,$semesterFour);
	} else {
		$theSemester	= $currentSemester;
		$futureSemesters			= array($nextSemester,$semesterTwo,$semesterThree,$semesterFour);
	}
	
	$allSemester					= array($theSemester=>0,$nextSemester=>1,$semesterTwo=>2,$semesterThree=>3,$semesterFour=>4);
	$findSemester					= array(0=>$theSemester,1=>$nextSemester,2=>$semesterTwo,3=>$semesterThree,4=>$semesterFour);


	
	$content = "";	

	function checkAdvisor($myStr,$mySemester) {

		global $wpdb, $testMode, $doDebug, $advisorTableName;

		if ($doDebug) {
			echo "At function checkAdvisor with input: $myStr and semester of $mySemester<br />";
		}

		if ($myStr == '') {
			return FALSE;
		}
		$haveAMatch					= FALSE;
		$sql						= "select * from $advisorTableName 
										where advisor_call_sign='$myStr' 
										and advisor_semester = '$mySemester' ";
		$wpw1_cwa_advisor		= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError("Display Current and Future Students and Errors FUNCTION checkAdvisor",$doDebug);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_call_sign 					= strtoupper($advisorRow->advisor_call_sign);
					$advisor_semester 					= $advisorRow->advisor_semester;
					$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->advisor_verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
					$advisor_action_log 				= $advisorRow->advisor_action_log;
					$advisor_class_verified 			= $advisorRow->advisor_class_verified;
					$advisor_control_code 				= $advisorRow->advisor_control_code;
					$advisor_date_created 				= $advisorRow->advisor_date_created;
					$advisor_date_updated 				= $advisorRow->advisor_date_updated;
					$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;
					
					$haveAMatch							= TRUE;
				}
			}
		}
		if ($haveAMatch) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	

	function displayHeaders() {
		$returnData			= "<tr><th>ID</th>
									<th>Call Sign</th>
									<th>Name</th>
									<th>City</th>
									<th>State</th>
									<th>Country</th>
									<th>Phone</th>
									<th>Email</th>
								</tr><tr>
									<th>TZ</th>
									<th>Level</th>
									<th>Semester</th>
									<th>Response</th>
									<th>Status</th>
									<th>Assigned Advisor</th>
									<th>Req Date</th>
									<th>Welcome Date</th>
								</tr><tr>
									<th>Hld Reason</th>
									<th>Int Req</th>
									<th colspan='2'>First Class Choice</th>
									<th colspan='2'>Second Class Choice</th>
									<th colspan='2'>Third Class Choice</th>
								</tr>";
		return $returnData;
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



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>$jobname</h3>
							<p>Select report type and then click 'Submit' to start the process.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td>Report Type</td>
								<td><input type='radio' class='formInputButton' name='inpType' value='Full'>Full Report<br />
									<input type='radio' class='formInputButton' name='inpType' value='Errors'>Only Students with Errors</td></tr>
							$testModeOption
							</table>
							<input class='formInputButton' type='submit' value='Submit' />
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}
// read and process the student table
		$sql					= "select * from $studentTableName 
									left join $userMasterTableName on user_call_sign = student_call_sign 
									where (student_semester = '$currentSemester' 
										   or student_semester = '$nextSemester' 
										   or student_semester = '$semesterTwo' 
										   or student_semester = '$semesterThree' 
										   or student_semester = '$semesterFour') 
									order by student_semester, student_call_sign";
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
				
					$totalStudents++;
					$totalThisSemester++;
					if ($doDebug) {
						echo "<br />Processing $student_call_sign<br />";
					}
					if ($student_first_class_choice == '') {
						$student_first_class_choice		= 'None';
					}
					if ($student_second_class_choice == '') {
						$student_second_class_choice		= 'None';
					}
					if ($student_third_class_choice == '') {
						$student_third_class_choice		= 'None';
					}
				
					if ($student_semester != $prev_semester) {
						if ($doDebug) {
							echo "got a new semester of $student_semester<br />";
						}
						if ($firstTime) {
							$firstTime 			= FALSE;
						} else {
							$content			.= "</table style='width:1000px;'><p>$totalThisSemester: Total students in $prev_semester semester</p>";
							$totalThisSemester	= 0;
						}
						$prev_semester			= $student_semester;
						$content				.= "<h3>Students for Semester $student_semester</h3><table style='width:1000px;'>";
						$content				.= displayHeaders();
						$headerCount			= $headerInterval;
						$daysToSemester			= days_to_semester($student_semester);
						if ($doDebug) {
							echo "Days to $student_semester is $daysToSemester days<br />";
						}
					}


					
					$hasError					= FALSE;
					$errorReport				= "";
					
					
					
					
					$errorInfo			= '';
					// do the verifications	
					if ($doDebug) {
						echo "<br />Processing ID $student_ID ($student_call_sign)<br />";
					}

					// Check for a valid level
					if ($doDebug) {
						echo "Checking $student_level for a valid level<br />";
					}
					if (!in_array($student_level,$validLevels)) {
						$errorReport	.= "<b>Issue 01</b> Student has invalid level of |$student_level|<br />";
						$issue01++;
						$hasError			= TRUE;
						$issue01Array[]		= $updateStudentURL . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; Level Error or $student_level found<br />";
						}
					}

					// check for call sign existence
					if ($doDebug) {
						echo "Checking $student_call_sign for a call sign<br />";
					}
					if ($student_call_sign == "") {
						$errorReport .= "<b>Issue 02</b> Student has no call sign.<br />";
						$issue02Array[]		= $updateStudentURL . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
						$issue02++;
						$hasError			= TRUE;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; Call sign error found<br />";
						}
					}

					// check for valid country, country_code, and ph_code
					if ($student_country_code == 'XX' || $student_country_code == '') {
						$errorReport	.= "<b>Issue 28</b> Country code and country missing<br />";
						$issue28Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
						$issue28++;
						$hasError			= TRUE;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; Missing country code<br />";
						}
					} else {
						$countryRow	= $wpdb->get_results("select * from wpw1_cwa_country_codes 
															where country_code = '$student_country_code'");
						if ($countryRow === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numRows		= $wpdb->num_rows;
							if ($numRows > 0) {
								foreach($countryRow as $thisRow) {
									$thisCountryCode	= $thisRow->country_code;
									$thisCountry		= $thisRow->country_name;
									$thisPhCode			= $thisRow->ph_code;
								}
						
								if ($student_country != $thisCountry) {
									$errorReport		.= "<b>Issue 27</b> Country code does not agree with country<br />";
									$issue27++;
									$hasError			= TRUE;
									$issue27Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp; country code and country do not match<br />";
									}
								}
								if ($student_ph_code != $thisPhCode) {
									$errorReport		.= "<b>Issue 29</b> Student ph_code of $student_ph_code does not match country ph code of $thisPhCode<br />";
									$issue29++;
									$hasError			= TRUE;
									$issue29Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
									if ($doDebug) {
										echo "Student ph_code of $student_ph_code does not match country ph code of $thisPhCode<br />";
									}
								}
							} else {
								$errorReport		.= "<b>Issue 28</b> Country code not found in database<br />";
								$issue28Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
								$issue28++;
								$hasError			= TRUE;
								$errorReport		.= "<b>Issue 29</b> Unable to verify phone code<br />";
								$issue29Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
								$issue29++;								
							}
						}
					}

					// check for valid response
					if ($doDebug) {
						echo "Checking $student_response for a valid reponse<br />";
					}
					if ($student_response != '') {
						if (!in_array($student_response,$validResponses)) {
							$errorReport	.= "<b>Issue 03</b> Student has an invalid Response of |$student_response|<br />";
							$issue03Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							$issue03++;
							$hasError			= TRUE;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp; Resonse error found<br />";
							}
						}
					}
					if ($currentSemester == 'Not in Session' && $student_semester == $nextSemester) {
						if ($student_response == '') {
							$errorReport	.= "<b>Issue 30</b> $issueTxt30<br />";
							$issue30Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							$issue30++;
							$hasError			= TRUE;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp; Resonse not found<br />";
							}
						}
					}

					// check for valid student status
					if ($doDebug) {
						echo "Checking $student_status for a valid student status<br />";
					}
					if ($student_status != '') {
						if (!in_array($student_status,$validStatuses)) {
							$errorReport	.= "<b>Issue 04</b> Student has an invalid Student Status of |$student_status|<br />";
							$issue04Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							$issue04++;
							$hasError			= TRUE;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student Status error found<br />";
							}
						}
					}

					// check for valid hold reason code
					if ($doDebug) {
						echo "Checking $student_hold_reason_code for a valid code<br />";
					}
					if ($student_hold_reason_code != '' && $student_hold_override != 'Y') {
						$myStr				= translate_hold_reason_code($student_hold_reason_code,$doDebug);
						if ($myStr === FALSE) {
							$errorReport	.= "<b>Issue 22</b> Student has an invalid Hold Reason Code of |$student_hold_reason_code|<br />";
							$issue22++;
							$hasError			= TRUE;
							$issue22Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student hold reason code error found<br />";
							}
						}
					}

					// check for valid intervention required
					if ($doDebug) {
						echo "Checking $student_intervention_required for a valid status<br />";
					}
					if ($student_intervention_required != '') {
						if (!in_array($student_intervention_required,$validIntReq)) {
							$errorReport	.= "<b>Issue 23</b> Student has an invalid Student Intervention Required of |$student_intervention_required|<br />";
							$issue23++;
							$hasError			= TRUE;
							$issue23Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student Intervention Required error found<br />";
							}
						}
					}

					// check for a valid time zone
					if ($doDebug) {
						echo "Checking  $student_timezone_id and $student_timezone_offset<br />";
					}
					$timezoneOK				= TRUE;
					if ($student_timezone_id == '' || $student_timezone_id == 'Unknown') {
						$errorReport	.= "<b>Issue 05</b> Student has an invalid timezone_id of $student_timezone_id<br />";
						$timezoneOK			= FALSE;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; timezone_id error found<br />";
						}
					}
					if ($student_timezone_offset == -99) {
						$errorReport	.= "<b>Issue 05</b> Student has an invalid timezone_offset of $student_timezone_offset<br />";
						$timezone			= FALSE;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; timezone_offset error found<br />";
						}
					}
					if (!$timezoneOK) {
						$issue05++;
						$hasError			= TRUE;
						$issue05Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
					}

					// check for a valid request date
					if ($doDebug) {
						echo "Checking $student_request_date for a valid request date<br />";
					}
					if ($student_request_date == '') {
						$errorReport	.= "<b>Issue 06</b> Student has no Request Date<br />";
						$issue06++;
						$hasError			= TRUE;
						$issue06Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; Request date error found<br />";
						}
					} else {
						$rdate		= strtotime($student_request_date);
						if ($rdate > $myUnixTime) {
							$errorReport	.= "<b>Issue 07</b> Student has an future Request Date of |$student_request_date|<br />";
							$issue07++;
							$hasError			= TRUE;
							$issue07Array[]		= updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp; Request date error found<br />";
							}
						}
						$myInt		= strtotime("$student_request_date + 1 day");
						if ($myInt < $myUnixTime) {
							if ($student_welcome_date == '') {
								$errorReport	.= "<b>Issue 08</b> Student should have a welcome Date<br />";
								$issue08++;
								$hasError			= TRUE;
								$issue08Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp; Welcome dateError found<br />";
								}
							}
						}
					}

					// check student class choices
					if ($daysToSemester <= 48) {
						if ($doDebug) {
							echo "Checking first class choice of $student_first_class_choice<br />";
						}
						$classChoiceTest	= FALSE;
						if ($student_first_class_choice == '' || $student_first_class_choice == 'None') {
							$errorReport	.= "<b>Issue 09</b> Student has an empty first class choice<br />";
							$issue09++;
							$hasError			= TRUE;
							$issue09Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student first class choice missing error <br />";
							}
						} else {		// has at least one class choice. Check to  see if a class is available
							$classChoiceTest	= TRUE;
						}
					} else {
						if ($doDebug) {
							echo "did not check catalog entries<br />";
						}
					}

					if ($doDebug) {
						echo "Checking that Response of $student_response and Student Status of $student_status are blank for future semesters<br />";
					}
					if (in_array($student_semester,$futureSemesters)) {
						// check that Response and Student Status are blank for future semesters
						if ($student_response != '' && $student_response != 'R') {
							$errorReport	.= "<b>Issue 14</b> Student should not have a Response of |$student_response|<br />";
							$issue14Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							$issue14++;
							$hasError			= TRUE;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp; Response Error found<br />";
							}
						}
						if ($student_status != '') {
							$errorReport	.= "<b>Issue 15</b> Student should not have a Student Status of |$student_status|<br />";
							$issue15++;
							$hasError			= TRUE;
							$issue15Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student Status error found<br />";
							}
						}

						// assigned advisor should be blank in future semesters
						if ($student_assigned_advisor != '') {
							$errorReport	.= "<b>Issue 16</b> Student's assigned advisor of |$student_assigned_advisor| should be blank<br />";
							$issue16++;
							$hasError			= TRUE;
							$issue16Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
						}

						// hold reason code should be blank
						if ($student_hold_reason_code != '' && $student_hold_reason_code != 'X') {
							$errorReport	.= "<b>Issue 17</b> Student's hold reason code of |$student_hold_reason_code| should be blank<br />";
							$issue17++;
							$hasError			= TRUE;
							$issue17Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
						}
						if ($student_intervention_required != '') {
							$errorReport	.= "<b>Issue 18</b> Student's intervention required of |$student_intervention_required| should be blank<br />";
							$issue18++;
							$hasError			= TRUE;
							$issue18Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
						}
					}	

					// check advisor and assigned advisor
					if ($doDebug) {
						echo "Checking pre_assigned_advisor $student_pre_assigned_advisor and assigned advisor $student_assigned_advisor<br />";
					}
					if ($student_pre_assigned_advisor != '') {
						if (checkAdvisor($student_pre_assigned_advisor,$student_semester) == FALSE) {
							$errorReport	.= "<b>Issue 19</b> Student has an unknown pre_assigned advisor of |$student_pre_assigned_advisor|<br />";
							$issue19++;
							$hasError			= TRUE;
							$issue19Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Pre_assigned_advisor error found<br />";
							}
						} 
					}		
					if ($student_assigned_advisor != '') {
						if (checkAdvisor($student_assigned_advisor,$student_semester) == FALSE) {
							$errorReport	.= "<b>Issue 20</b> Student has an unknown Assigned Advisor of |$student_assigned_advisor|<br />";
							$issue20++;
							$hasError			= TRUE;
							$issue20Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp; Assigned advisor error found<br />";
							}
						} 
					}		

					if (!array_key_exists($student_semester,$allSemester)) {
						if ($doDebug) {
							echo "Got a strange semester of |$student_semester| for $student_call_sign ($student_ID).<br />";
						}
						$errorReport .= "<b>Issue 21</b> Student has an unknown semester of $student_semester<br />";
						$issue21++;
						$hasError			= TRUE;
						$issue21Array[]		= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
					}
					$errorReport	.= "</td></tr>";
					

					if ($inpType == 'Full' || ($inpType == 'Errors' && $hasError)) {
					

						$headerCount--;
						if ($doDebug) {
							echo "headerCount: $headerCount<br />";
						}
						if ($headerCount < 0) {
							if ($doDebug) {
								echo "time to display the headers<br />";
							}
							$content				.= displayHeaders();
							$headerCount			= $headerInterval;
						}

					
						$myStr1						= '';
						if ($student_hold_reason_code == 'X') {
							$myStr1					= "X ($student_excluded_advisor)";
						} else {
							if ($student_hold_reason_code != '') {
								$myStr1					= translate_hold_reason_code($student_hold_reason_code);
							}
						}
						$thisURL			= $updateStudentURL  . "&request_info=$student_call_sign' target='_blank'>$student_call_sign</a>";
						$content			.= "<tr><td style='vertical-align:top;'>$student_ID</td>
													<td style='vertical-align:top;'>$thisURL</td>
													<td style='vertical-align:top;'>$student_last_name, $student_first_name</td>
													<td style='vertical-align:top;'>$student_city</td>
													<td style='vertical-align:top;'>$student_state</td>
													<td style='vertical-align:top;'>$student_country</td>
													<td style='vertical-align:top;'>$student_ph_code $student_phone</td>
													<td style='vertical-align:top;'>$student_email</td>
												</tr><tr>
													<td style='vertical-align:top;'>$student_timezone_id $student_timezone_offset</td>
													<td style='vertical-align:top;'>$student_level</td>
													<td style='vertical-align:top;'>$student_semester</td>
													<td style='vertical-align:top;'>$student_response</td>
													<td style='vertical-align:top;'>$student_status</td>
													<td style='vertical-align:top;'>$student_assigned_advisor</td>
													<td style='vertical-align:top;'>$student_request_date</td>
													<td style='vertical-align:top;'>$student_welcome_date</td>
												</tr><tr>
													<td style='vertical-align:top;'>$myStr1</td>
													<td style='vertical-align:top;'>$student_intervention_required</td>
													<td colspan='2' style='vertical-align:top;'>$student_first_class_choice</td>
													<td colspan='2' style='vertical-align:top;'>$student_second_class_choice</td>
													<td colspan='2 style='vertical-align:top;''>$student_third_class_choice</td>
												</tr>";
						if ($hasError) {
							$content		.= "<tr><td colspan='8'>$errorReport</td></tr>";
						}
						$content			.= "<tr><td colspan='8'><hr></td></tr>";
	
					
					}
					
				}
				$content		.= "</tr></table style='width:1000px;''><p>$totalThisSemester: Total students in $student_semester semester</p>
									<p>$totalStudents: Total student records processed</p>
									<h4>Errors Encountered</h4>\n
									<table>\n
									<tr><th>Count</th>\n
										<th>Error Numbr</th>\n
										<th>Error</th></tr>\n";
				if ($doDebug) {
					echo "<br /><b>Putting Out Totals</b><br />";
				}
				for($ii=1;$ii<=30;$ii++) {
					$strSnum 		= str_pad($ii,2,'0',STR_PAD_LEFT);
					if ($doDebug) {
						echo "Processing ii: $ii; strSnum: $strSnum<br />";
					}
					$thisCount		= ${'issue' . $strSnum};
					if ($thisCount > 0) {
						if ($doDebug) {
							echo "putting out totals for issue $strSnum<br />";
						}
						$thisText		= ${'issueTxt' . $strSnum};
						$content		.= "<tr><td>$thisCount</td>\n
												<td>Issue $strSnum</td>\n
												<td>$thisText</td></tr>\n";
						if (count(${'issue' . $strSnum . 'Array'}) > 0) {
							if ($doDebug) {
								echo "have call signs to display for issue $strSnum<br ><pre>";
								print_r(${'issue' . $strSnum . 'Array'});
								echo "</pre><br />";
							}
							$content	.= "<tr><td>&nbsp;</td>\n<td>&nbsp;<td><table>\n";
							$arrayCount	= 4;
							foreach(${'issue' . $strSnum . 'Array'} as $thisValue) {
								switch ($arrayCount) {
									case 4:
										$content	.= "<tr><td style='width:100px;'>$thisValue</td>\n";
										$arrayCount--;
										if ($doDebug) {
											echo "inserted $thisValue into slot 1<br />";
										}
										break;
									case 3:
										$content	.= "<td style='width:100px;'>$thisValue</td>\n";
										$arrayCount--;
										if ($doDebug) {
											echo "inserted $thisValue into slot2<br />";
										}
										break;
									case 2:
										$content	.= "<td style='width:100px;'>$thisValue</td>\n";
										$arrayCount--;
										if ($doDebug) {
											echo "inserted $thisValue into slot 3<br />";
										}
										break;
									case 1:
										$content	.= "<td style='width:100px;'>$thisValue</td>\n";
										$arrayCount--;
										if ($doDebug) {
											echo "inserted $thisValue into slot 4<br />";
										}
										break;
									case 0:
										$content	.= "<td style='width:100px;'>$thisValue</td></tr>\n";
										$arrayCount	= 4;
										if ($doDebug) {
											echo "inserted $thisValue into slot 5<br />";
										}
										break;
								}
							}
							if ($arrayCount != 4) {
								for ($jj=$arrayCount;$jj>=0;$jj--) {
									$content	.= "<td style='width:100px;'>&nbsp;</td>\n";
									if ($doDebug) {
										echo "inserted space into slot $jj<br />";
									}

								}
								$content		.= "</tr>\n</table>\n</td></tr>\n";
							} else {
								$content		.= "</table>\n</td></tr>\n";
							}
						}
					} else {
						if ($doDebug) {
							echo "No callsigns for 'issue' . $strSnum . 'Array'<br />";
						}
					}
				}
				$content			.= "<table>\n";
			} else {
				if ($doDebug) {
					echo "No $studentTableName records found<br />";
				}
				$content	.= "<p>No $studentTableName records found</p>";
			}
		}
	}
	$thisTime 					= date('Y-m-d H:i:s');
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<br /><br /><br /><p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("List All Students|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	$content					.= "<br /><br /><br /><p>Report displayed at $thisTime.</p>";
	return $content;
}
add_shortcode ('display_current_and_future_students_and_errors', 'display_current_and_future_students_and_errors_func');

