function detailed_advisor_history_func() {

/*	Show Detailed History of an Advisor
	Reads current advisor and advisorClass pods and displays the info then goes through 
	all the pastAdvisorNew and pastAdvisorClass pods and displays that information. If 
	requested, will list the students for the classes 
	
	Modified 22Oct22 by Roland for new timezone table fields
	Modified 15Apr23 by Roland to fix action_log
	Modified 12Jul23 by Roland to use consolidated tables
	Modified 12Oct24 by Roland for new database
*/

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();

	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 						= $initializationArray['validUser'];
	$userName						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL						= $initializationArray['siteurl'];
	
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
	$inp_advisor				= '';
	$theURL						= "$siteURL/cwa-detailed-history-for-an-advisor/";
	$inp_liststudents			= '';
	$jobname					= "Detailed Advisor History";

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
			if ($str_key 		== "inp_advisor") {
				$inp_advisor	 = $str_value;
				$inp_advisor	 = strtoupper(filter_var($inp_advisor,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_liststudents") {
				$inp_liststudents	 = $str_value;
				$inp_liststudents	 = filter_var($inp_liststudents,FILTER_UNSAFE_RAW);
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
					$testMode == TRUE;
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
		if ($doDebug) {
			echo "Operating in TestMode<br />";
		}
		$advisorTableName		= 'wpw1_cwa_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$studentTableName			= 'wpw1_cwa_student2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$content					.= "<p><b>Operating in TestMode</b></p>";
	} else {
		$advisorTableName		= 'wpw1_cwa_advisor';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$studentTableName			= 'wpw1_cwa_student';
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Detailed Advisor History</h3>
							<p>Enter the advisor's call sign, select whether or not the students are to be included 
							in the report, and click 'Next'</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td>Advisor Call Sign</td>
								<td><input type='text' class='formInputTxt' name='inp_advisor' size='15' maxlength='15'></td></tr>
							<tr><td>Include list of students?</td>
								<td><input type='radio' class='formInputButton' name='inp_liststudents' value='No' checked> No<br />
									<input type='radio' class='formInputButton' name='inp_liststudents' value='Yes'> Yes</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 2 with $inp_advisor; liststudent: $inp_liststudents<br />";
		}
		$content				.= "<h3>Detailed Advisor History</h3>";
		// get the current advisor record, if any
		$sql					= "select * from $advisorTableName 
									left join $userMasterTableName on user_call_sign = advisor_call_sign 
								   where advisor_call_sign='$inp_advisor' 
								   order by advisor_date_created";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				$myStr			= $wpdb->last_query;
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_master_ID 					= $advisorRow->user_ID;
					$advisor_master_call_sign			= $advisorRow->user_call_sign;
					$advisor_first_name 				= $advisorRow->user_first_name;
					$advisor_last_name 					= $advisorRow->user_last_name;
					$advisor_email 						= $advisorRow->user_email;
					$advisor_phone 						= $advisorRow->user_phone;
					$advisor_city 						= $advisorRow->user_city;
					$advisor_state 						= $advisorRow->user_state;
					$advisor_zip_code 					= $advisorRow->user_zip_code;
					$advisor_country_code 				= $advisorRow->user_country_code;
					$advisor_whatsapp 					= $advisorRow->user_whatsapp;
					$advisor_telegram 					= $advisorRow->user_telegram;
					$advisor_signal 					= $advisorRow->user_signal;
					$advisor_messenger 					= $advisorRow->user_messenger;
					$advisor_master_action_log 			= $advisorRow->user_action_log;
					$advisor_timezone_id 				= $advisorRow->user_timezone_id;
					$advisor_languages 					= $advisorRow->user_languages;
					$advisor_survey_score 				= $advisorRow->user_survey_score;
					$advisor_is_admin					= $advisorRow->user_is_admin;
					$advisor_role 						= $advisorRow->user_role;
					$advisor_master_date_created 		= $advisorRow->user_date_created;
					$advisor_master_date_updated 		= $advisorRow->user_date_updated;

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

					// if you need the country name and phone code, include the following
					$countrySQL		= "select * from wpw1_cwa_country_codes  
										where country_code = '$advisor_country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$advisor_country		= "UNKNOWN";
						$advisor_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$advisor_country		= $countryRow->country_name;
								$advisor_ph_code		= $countryRow->ph_code;
							}
						} else {
							$advisor_country			= "Unknown";
							$advisor_ph_code			= "";
						}
					}
	
					if ($doDebug) {
						echo "<br />Have advisor record for $advisor_call_sign and semester $advisor_semester<br />";
					}
					$newActionLog	= formatActionLog($advisor_action_log);
					$content		.= "<h4>Advisor Record for $advisor_last_name, $advisor_first_name ($advisor_call_sign) in semester $advisor_semester</h4>
										<table>
										<tr><th>Semester</th>
											<th>Email</th>
											<th>Phone</th>
											<th>State</td></tr>
										<tr><td>$advisor_semester</td>
											<td>$advisor_email</td>
											<td>$advisor_phone</td>
											<td>$advisor_state</td></tr>
	
										<tr><th>Welcome Date</th>
											<th>Verify Date</th>
											<th>Verify Response</th>
											<th>Survey Score</th></tr>
										<tr><td>$advisor_welcome_email_date</td>
											<td>$advisor_verify_email_date</td>
											<td>$advisor_verify_response</td>
											<td>$advisor_survey_score</td></tr>
										<tr><td colspan='4'>$newActionLog</td></tr>
										<tr><td colspan='4'>&nbsp;</td></tr>";

					//// now get the class records for this advisor
					$sql					= "select * from $advisorClassTableName 
											   where advisorclass_call_sign='$inp_advisor' 
												and advisorclass_semester='$advisor_semester'";
					$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numACRows			= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and found $numACRows rows in $advisorClassTableName<br />";
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
						
								if ($doDebug) {
									echo "<br />Have advisorClass record for $advisorClass_call_sign. Class nmbr: $advisorClass_sequence; Level: $advisorClass_level<br />";
								}

								$content		.= "<tr><td><b>Class</b></td>
														<td><b>Level</b></td>
														<td><b>Local Schedule</b></td>
														<td><b>UTC Schedule</b></td></tr>
													<tr><td>$advisorClass_sequence</td>
														<td>$advisorClass_level</td>
														<td>$advisorClass_class_schedule_times $advisorClass_class_schedule_days</td>
														<td>$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc</td></tr>";
				
								//// if students are to be displayed, get them
								if ($inp_liststudents == 'Yes') {
									$content			.= "<tr><td><b>Student</b></td>
																<td colspan='2'><b>Email</b></td>
																<td><b>Promotable</b></td></tr>";
									if ($doDebug) {
										echo "<br />getting students to list<br />";
									}
									for ($snum=1;$snum<31;$snum++) {
										if ($snum < 10) {
											$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
										} else {
											$strSnum		= strval($snum);
										}
										$theInfo			= ${'advisorClass_student' . $strSnum};
										if ($doDebug) {
											echo "processing past_advisorClass_student$strSnum theInfo: $theInfo<br />";
										}
										if ($theInfo != '') {
											$sql				= "select * from $studentTableName 
																	left join $userMasterTableName on user_call_sign = student_call_sign 
																   where student_call_sign = '$theInfo' 
																	and student_semester='$advisor_semester' ";
											$wpw1_cwa_student	= $wpdb->get_results($sql);
											if ($wpw1_cwa_student === FALSE) {
												handleWPDBError($jobname,$doDebug);
											} else {
												$numSRows		= $wpdb->num_rows;
												if ($doDebug) {
													echo "ran $sql<br />and retrieved $numSRows rows<br />";
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
															echo "have student $student_call_sign<br />";
														}
														$content		.= "<tr><td>$student_last_name, $student_first_name ($student_call_sign)</td>
																				<td colspan='2'>$student_email</td>
																				<td>$student_promotable</td></tr>";
													}			/// end of student while
												} else {				/// no student records
													$content			.= "<tr><td colspan='4'>No student record found for $theInfo</td></tr>";
												}
											}
										}		
									}
								}					/// end of list students if requestef
							}						/// end of advisorClass while
							$content		.= "</table>";
						} else {					/// no advisorClass records
							$content		.= "<tr><td colspan='4'>No advisorClass records</td></tr>";
						}
					}
				}
			} else {		// no current advisor record
				$content			.= "<p>No current advisor record</p>";
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
add_shortcode ('detailed_advisor_history', 'detailed_advisor_history_func');

