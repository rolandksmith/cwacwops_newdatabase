function cross_check_student_assignments_func() {

/*	Cross Check Student Assignments

	Reads student table and compares assignment to what is in the advisorClass table
	Then reads the advisorclass table and compares that to the student record
	
	Created 16Dec24 by Roland
	
*/

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "DEBUG Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	$proximateSemester	= $initializationArray['proximateSemester'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
//		$doDebug			= FALSE;
//		$testMode			= FALSE;
//	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
//	} else {
//		$wpdb->hide_errors();
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-cross-check-student-assignments/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Cross Check Student Assignments V$versionNumber";
	$studentArray				= array();
	$advisorArray				= array();

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "DEBUG Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "DEBUG Key: $str_key (array)<br />\n";
				}
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
			echo "DEBUG <p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$studentTableName			= "wpw1_cwa_student2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Click Submit to start the job
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "DEBUG <br />at pass2<br />";
		}
		
		$content		.= "<h3>$jobname</h3>
							<h4>Comparing Student Record to Advisor Record</h4>
							<table>
							<tr><th>Student</th>
								<th>Assigned Advisor</th>
								<th>Assigned Class</th>
								<th>Error</th></tr>";
	
		$sql		= "select * from $studentTableName 
						where student_semester = '$proximateSemester' 
						and student_response = 'Y' 
						and (student_status = 'Y' 
							or student_status = 'S') 
						order by student_call_sign ";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "DEBUG ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= $studentRow->student_call_sign;
					$student_time_zone  					= $studentRow->student_time_zone;
					$student_timezone_offset				= $studentRow->student_timezone_offset;
					$student_youth  						= $studentRow->student_youth;
					$student_age  							= $studentRow->student_age;
					$student_parent 						= $studentRow->student_parent;
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
	
					$studentLink			= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug&testMode' target='_blank'>$student_call_sign</a>";
					$assignedAdvisorLink	= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$student_assigned_advisor&inp_depth=one&doDebug&testMode' target='_blank'>$student_assigned_advisor</a>";
	
					// see if the student is in the advisorclass record	
					$foundStudent	= FALSE;
					$studentCount	= 0;
					$sql		= "select * from $advisorClassTableName 
									where advisorclass_semester = '$proximateSemester' 
									and advisorclass_call_sign = '$student_assigned_advisor' 
									and advisorclass_sequence = '$student_assigned_advisor_class'";
					$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numACRows			= $wpdb->num_rows;
						if ($doDebug) {
							echo "DEBUG ran $sql<br />and found $numACRows rows<br />";
						}
						if ($numACRows > 0) {
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
					
								$advisorLink	= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$advisorClass_call_sign&inp_depth=one&doDebug&testMode' target='_blank'>$advisorClass_call_sign</a>";
								$classCount				= 0;
								for ($snum=1;$snum<31;$snum++) {
									if ($snum < 10) {
										$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
									} else {
										$strSnum		= strval($snum);
									}
									$theInfo			= ${'advisorClass_student' . $strSnum};
									if ($theInfo != '') {
										if ($theInfo == $student_call_sign) {
											$foundStudent = TRUE;
											$studentCount++;
										}
									}
								}
							}
						}
					}
					$errorStr		= "";
					if (!$foundStudent) {
						$errorStr	.= "Student not found in advisorClass record";
					}
					if ($studentCount > 1) {
						if ($errorStr == '') {
							$errorStr	.= "Student found $studentCount times in advisorClass record";
						} else {
							$errorStr	.= "<br />Student found $studentCount times in advisorClass record";
						}
					}
					if ($errorStr != '') {
						$content		.= "<tr><td>$studentLink</td>
												<td>$assignedAdvisorLink</td>
												<td>$student_assigned_advisor_class</td>
												<td>$errorStr</td></tr>";
					}
				}
			} else {
				$content	.= "<p>No students found!</p>";
			}
		}
		$content			.= "</table>";
		flush();

		// now check advisorClass against student record
		$content		.= "<h4>Comparing AdvisorClass Record to Student Record</h4>
							<table>
							<tr><th>Advisor</th>
								<th>Class</th>
								<th>Student</th>
								<th>Error</th></tr>";
		$sql		= "select * from $advisorClassTableName 
						where advisorclass_semester = '$proximateSemester' 
						order by advisorclass_call_sign, advisorclass_sequence ";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "DEBUG ran $sql<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				$theSeq			= 0;
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
		
					$advisorLink	= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$advisorClass_call_sign&inp_depth=one&doDebug&testMode' target='_blank'>$advisorClass_call_sign</a>";
					for ($snum=1;$snum<31;$snum++) {
						if ($snum < 10) {
							$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
						} else {
							$strSnum		= strval($snum);
						}
						$theInfo			= ${'advisorClass_student' . $strSnum};
						if ($theInfo != '') {
							$foundStudent	= FALSE;
							$studentCount	= 0;
							$errorStr		= "";
							
							// get any matching student records
							$studentSQL		= "select * from $studentTableName 
												where student_call_sign like '$theInfo'  
												and student_semester = '$proximateSemester' 
												and student_response = 'Y' 
												and (student_status = 'Y' 
													or student_status = 'S')";

							$wpw1_cwa_student	= $wpdb->get_results($studentSQL);
							if ($wpw1_cwa_student === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$numSRows									= $wpdb->num_rows;
								if ($doDebug) {
									echo "DEBUG ran $studentSQL<br />and retrieved $numSRows rows from $studentTableName table<br >";
								}
								if ($numSRows > 0) {
									foreach ($wpw1_cwa_student as $studentRow) {
										$student_ID								= $studentRow->student_id;
										$student_call_sign						= $studentRow->student_call_sign;
										$student_time_zone  					= $studentRow->student_time_zone;
										$student_timezone_offset				= $studentRow->student_timezone_offset;
										$student_youth  						= $studentRow->student_youth;
										$student_age  							= $studentRow->student_age;
										$student_parent 						= $studentRow->student_parent;
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
						
										$studentLink	= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug&testMode' target='_blank'>$student_call_sign</a>";
										
										if ($student_assigned_advisor != $advisorClass_call_sign) {
											if ($student_assigned_advisor != '') {
												$assignedAdvisorLink	= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$student_assigned_advisor&inp_depth=one&doDebug&testMode' target='_blank'>$student_assigned_advisor</a>";
											} else {
												$assignedAdvisorLink	= "empty";
											}
											$content					.= "<tr><td>$advisorLink</td>
																				<td>$advisorClass_sequence</td>
																				<td>$studentLink</td>
																				<td>Student Assigned Advisor is $assignedAdvisorLink Class: $student_assigned_advisor_class</td></tr>";
										}
									}
								} else {
									$studentLink	= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$theInfo&inp_depth=one&doDebug&testMode' target='_blank'>$theInfo</a>";
									$content		.= "<tr><td>$advisorLink</td>
															<td>$advisorClass_sequence</td>
															<td>$studentLink</td>
															<td>No corresonding student record found</td></tr>";
								}
							}
						}
					}
				}
				$content	.= "</table>";
			} else {
				$content	.= "No advisorclass records found<br />";
			}
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";

	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('cross_check_student_assignments', 'cross_check_student_assignments_func');

