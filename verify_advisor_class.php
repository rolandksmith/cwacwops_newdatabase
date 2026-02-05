function verify_advisor_class_func() {

/*	Verify Advisor Class
 *
 *	This function is used around the middle of the semester for advisors to verify which 
 *	students are actually in their class.
 *
 *	Input: 	Advisor call sign
 *	Process:
 *	Get all records for this advisor from the advisor table for the current semester
 *	For each of the advisor records
 *		Get all students for the current semester with a response of Y assigned to that advisor
 *			at that level, ordered by the student last name, first name
 *		List the students with a radio button 'attending' or 'not attending'
 *		Give a text box for the advisor to indicate any additional students
 *	When all records have been displayed, the advisor can submit the response
 *	Update the action log that the advisor has responded
 *	Mark each non-attending student as withdrawn (promotable -> W)
 *	If there are any extras, send that info to Bob
 *	Update a new field 'class verified' with the date the advisor responded
 *
 * 	Modified 4Mar21 by Roland to change joe fisher's email address 
 	Modified 5Oct21 by Roland to use the advisor and advisorClass pod formats
 	Modified 27Oct22 by Roland to accomodate new timezone table format
 	Modified 17Apr23 by Roland to fix action_log
 	Modified 16Jul23 by Roland to use consolidated tables
 	Modified 31Aug23 by Roland to turn off dodebug and testmode if validUser is N
 	Modified 30Sep24 by Roland for new database
*/ 

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$myDate							= date('dMy hi') . 'z';
	$initializationArray			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$userName						= $initializationArray['userName'];
	$userRole						= $initializationArray['userRole'];
	$currentSemester				= $initializationArray['currentSemester'];
	$prevSemester					= $initializationArray['prevSemester'];
	$siteURL						= $initializationArray['siteurl'];
	$validTestmode					= $initializationArray['validTestmode'];

	if ($userName == '') {
		return "You are not authorized";
	}

	if ($userRole != 'administrator') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}

	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$inp_advisor				= '';
	$extmode					= 'pd';
	$inp_attending				= array();
	$inp_extras					= array();
	$gotStudent					= FALSE;
	$token						= '';
	$studentRecordCount			= 0;
	$studentExtraCount			= 0;
	$increment					= 0;
	$fieldTest					= array('action_log','post_status','post_title','control_code');
	$logDate					= date('Y-m-d H:i');
	$theURL						= "$siteURL/cwa-verify-advisor-class/";
	$jobname					= 'Verify Advisor Class';
	
	if ($userName == '') {
		return "You are not authorized";
	}

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
			if ($str_key 		== "extmode") {
				$extmode	 = $str_value;
				$extmode	 = filter_var($extmode,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_advisor_id") {
				$inp_advisor_id	 = $str_value;
				$inp_advisor_id	 = filter_var($inp_advisor_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token			 = $str_value;
				$token			 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_attending") {
				$inp_attending	 = $str_value;
			}
			if ($str_key 		== "inp_extras") {
				$inp_extras		 = $str_value;
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
					$extmode	= "tm";
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
	

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h2>$jobname</h2>
							<p>This function is normally run from an email sent the advisor around the middle of 
							the semester.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table style='width:600px;'>
							<tr><td>Advisor Call Sign</td>
								<td><input type='text' class='formInputText' name='inp_advisor' size='10' maxlength='15'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 2<br />Looking for classes for $inp_advisor in mode $extmode<br />";
		}
		// Verify that we've got something in inp_advisor
		if ($inp_advisor != '' && $extmode != '') {
			if ($extmode == 'tm') {
				if ($doDebug) {
					echo "extmode = $extmode. Setting testMode to TRUE<br />";
				}
				$testMode	= TRUE;
			}
			
			if ($testMode) {
				$studentTableName		= 'wpw1_cwa_student2';
				$advisorTableName		= 'wpw1_cwa_advisor2';
				$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
				$userMasterTableName	= 'wpw1_cwa_user_master2';
				if ($doDebug) {
					echo "Function is under development.<br />";
				}
				$content .= "Function is under development.<br />";
			} else {
				$studentTableName 		= 'wpw1_cwa_student';
				$advisorTableName		= 'wpw1_cwa_advisor';
				$advisorClassTableName	= 'wpw1_cwa_advisorclass';
				$userMasterTableName	= 'wpw1_cwa_user_master';
			}
			
			$theBigHeader				= TRUE;
		

			// Get advisor record for this advisor
			if ($currentSemester == 'Not in Session') {
				$currentSemester	= $prevSemester;
			}
			$sql				= "select * from $advisorTableName 
									left join $userMasterTableName on user_call_sign = advisor_call_sign 
									where advisor_call_sign='$inp_advisor' 
									and advisor_semester='$currentSemester'";
			$wpw1_cwa_advisor	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numARows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
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
	
							
						$content			.= "<h3>$jobname for $advisor_call_sign</h3>
												<p>Please mark each student as either YES (attending) or NO (not attending). 
												If there are students attending not listed, please follow the instructions at the 
												bottom of the form.  
												Be sure to submit the form. You will continue to get reminders until 
												the verification is complete.<p>
												<p>Thank you for your service as an advisor!</p>
												<form method='post' action='$theURL' 
												name='selection_form' ENCTYPE='multipart/form-data''>
												<input type='hidden' name='strpass' value='3'>
												<input type='hidden' name='inp_advisor' value='$inp_advisor'>
												<input type='hidden' name='inp_advisor_id' value='$advisor_ID'>
												<input type='hidden' name='extmode' value='$extmode'>
												<input type='hidden' name='token' value='$token'>
												<table>";
			
						// get the advisorClass records for this advisor

						$sql					= "select * from $advisorClassTableName 
												   where advisorclass_call_sign='$inp_advisor' 
												   and advisorclass_semester='$currentSemester' 
												   order by advisorclass_sequence";
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

									$firstTime				= TRUE;

									for ($snum=1;$snum<=$advisorClass_number_students;$snum++) {
										if ($snum < 10) {
											$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
										} else {
											$strSnum		= strval($snum);
										}
										$studentCallSign	= ${'advisorClass_student' . $strSnum};
										if ($studentCallSign != '') {

											// Now get the student info
											$sql				= "select * from $studentTableName 
																	left join $userMasterTableName on user_call_sign = student_call_sign 
																   where student_semester='$currentSemester' 
																   and student_call_sign = '$studentCallSign' ";
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

														if ($doDebug) {
															echo "Have a student $student_call_sign status: $student_status; level: $student_level<br />";
														}

														$doProceed								= FALSE;
														if ($student_status == 'Y' || $student_student_status == 'S') {
															$doProceed							= TRUE;
														} else {
															$doProceed							= FALSE;
															if ($doDebug) {
																echo "Bypassing this student<br/>";
															}
														}
//														if ($student_promotable == 'W') {
//															$doProceed							= FALSE;
//															if ($doDebug) {
//																echo "Bypassing student already withdrawn<br />";
//															}
//														}
														if ($doProceed) {								
															if ($doDebug) {
																echo "Got a student $student_last_name, $student_first_name ($student_call_sign) $student_time_zone; $student_level<br />";
															}
															$gotStudent				= TRUE;
															if ($firstTime) {
																$firstTime			= FALSE;
																$content		.= "
																				<tr><th>$advisor_last_name, $advisor_first_name ($advisor_call_sign)</th>
																						<th>Level $advisorClass_level; Class: $advisorClass_sequence</th></tr>
																					<tr><th>Student</th>
																						<th>Attending?</th>
																					</tr>";
															}
															$yesChecked = '';
															$noChecked = '';
															if ($student_promotable == 'W') {
																$noChecked = 'checked';
															} else {
																$yesChecked = 'checked';
															}
															
															$content			.= "<tr><td>$student_last_name, $student_first_name ($student_call_sign)</td>
																						<td>YES <input type='radio' class='formInputButton' name='inp_attending[$studentRecordCount|$student_ID]' value='Y|$student_call_sign' $yesChecked ><br />
																							NO <input type='radio' class='formInputButton' name='inp_attending[$studentRecordCount|$student_ID]' value='N|$student_call_sign' $noChecked ></td>
																					</tr>";
															$studentRecordCount++;
														}
													}
												}
											}
										}
									}
									$content		.= "<tr><td colspan='2'><b>$advisorClass_number_students Students</b></td></tr>";
								}			/// end of advisorClass
								if ($gotStudent) {
									$content	.= "
													<tr><td colspan='2'>If there are any other students in your class not listed above:<br />
													<br />1. Please have them register for this class even though the registration will be in the next semester. When they are registered, 
													a system admin will move their registration to this semester and add them to your class<br />  
													<br />2. Please enter their name and call sign in the space below. <br />
													<br />3. If more than one student needs to be added, put a period or semicolon between each student. 
													<br /><br />
														<textarea class = 'formInputText' name='inp_extras[$advisorClass_sequence|$advisorClass_level|$studentExtraCount]' rows='5' cols='50'></textarea></td></tr>
													</table>
													<p><input type='submit' value='Submit' class='formInputButton'></p></form>";
									$studentExtraCount++;
								}
							} else {
								if ($doDebug) {
									echo "No advisorClass records found for advisor $advisor_call_sign<br />";
								}
								$content	.= "No advisorClass records found for advisor $advisor_call_sign<br />";
							}
						}
					}	// end of advisor foreach
				} else {
					if ($doDebug) {
						echo "No advisor records found for $inp_advisor<br />";
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "Input inp_advisor is empty<br />";
			}
			$content		.= "Input information missing";
		}
		if ($doDebug) {
			echo "studentRecordCount: $studentRecordCount<br />studentExtraCount: $studentExtraCount<br />";
		}



	
//////////////		Pass 3	
	
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 3<br >";
		}
	
		if ($extmode == 'tm') {
			if ($doDebug) {
				echo "extmode = $extmode. Setting testMode to TRUE<br />";
			}
			$testMode	= TRUE;
		}
		if ($testMode) {
			$studentTableName		= 'wpw1_cwa_student2';
			$advisorTableName		= 'wpw1_cwa_advisor2';
			$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
			$userMasterTableName	= 'wpw1_cwa_user_master2';
			if ($doDebug) {
				echo "Function is under development.<br />";
			}
			$content .= "Function is under development.<br />";
		} else {
			$studentTableName 		= 'wpw1_cwa_student';
			$advisorTableName		= 'wpw1_cwa_advisor';
			$advisorClassTableName	= 'wpw1_cwa_advisorclass';
			$userMasterTableName	= 'wpw1_cwa_user_master';
		}
		$nextSemester	= $initializationArray['nextSemester'];

		$content		.= "<h3>$jobname for $inp_advisor</h3>";

		// Handling students who are attending or have dropped
		if ($doDebug) {
			echo "<br />inp_attending:<br ><pre>";
			print_r($inp_attending);
			echo "</pre><br />";
		}
		foreach($inp_attending as $key=>$value) {
			if ($doDebug) {
				echo "$key=>$value<br />";
			}
			$thisArray 				= explode("|",$value);
			$theAttending			= $thisArray[0];
			$theAttendingCallSign	= $thisArray[1];
			$myArray				= explode("|",$key);
			$theRecordCount			= $myArray[0];
			$theRecordID			= $myArray[1];

			/// get the student record
			$sql					= "select * from $studentTableName 
										where student_id='$theRecordID'";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numSRows rows<br />";
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
						
						$updateStudent				= FALSE;
						if ($theAttending == 'N') {
							if ($doDebug) {
								echo "Handling non-attending student at id $theRecordID<br />";
							}
							// if promtable already set to 'W', don't do anything
							if ($student_promotable == 'W') {
								if ($doDebug) {
									echo "student already marked as not attending<br />";
								}
							} else {
								
								$student_action_log		.= " / $myDate CLASSVERIFY $inp_advisor marked student as not attending. Promotable set to W ";
								$updateData				= array('student_promotable'=>'W',
																'student_action_log'=>$student_action_log);
								$updateFormat			= array('%s',
																'%s');
								$content				.= "Marking $theAttendingCallSign as not attending<br />";
								if ($student_status == 'S') {			// make status a Y
									$updateData['student_status']	= 'Y';
									$updateFormat[]					= '%s';
								}
								$updateStudent			= TRUE;
							}

						} else {			// student attending. Is the student status an 'S'?
							// if promotable marked as 'W', change it back to empty
							if ($student_promotable == 'W') {
								$student_action_log		.= " / $myDate CLASSVERIFY $inp_advisor marked student as not attending. Promotable changed from W to blank ";
								$updateData				= array('student_promotable'=>'',
																'student_action_log'=>$student_action_log);
								$updateFormat			= array('%s',
																'%s');
								$content				.= "Marking $theAttendingCallSign as attending<br />";
								if ($student_status == 'S') {			// make status a Y
									$updateData['student_status']	= 'Y';
									$updateFormat[]					= '%s';
								}
								$updateStudent			= TRUE;
							
							} else {
								if ($student_status == 'S') {			// make this a 'Y'
									$updateData		= array('student_status'=>'Y');
									$updateFormat	= array('%s');
									$updateStudent	= TRUE;
									if ($doDebug) {
										echo "Student status of 'Y' has been saved for $student_call_sign<br />";
									}
								}
							}
						}
						if ($updateStudent) {
							$studentUpdateData		= array('tableName'=>$studentTableName,
															'inp_method'=>'update',
															'inp_data'=>$updateData,
															'inp_format'=>$updateFormat,
															'jobname'=>$jobname,
															'inp_id'=>$student_ID,
															'inp_callsign'=>$student_call_sign,
															'inp_semester'=>$student_semester,
															'inp_who'=>$student_assigned_advisor,
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
						}
					}
				} else {
					if ($doDebug) {
						echo "No student record found for $theAtteningCallSign at id $theRecordID<br />";
					}
				}				
			}
		}
		$content						.= "<br />";

		// Now handle the extras
		if ($doDebug) {
			echo "inp_extras:<br />";
		}
		$gotExtras				= FALSE;
		$emailData				= '';
		foreach($inp_extras as $key=>$value) {
			if ($doDebug) {
				echo "$key=>$value<br />";
			}
			if ($value != '') {
				$myArray		= explode("|",$key);
				$theSequence	= $myArray[0];
				$theLevel		= $myArray[1];
				$theII			= $myArray[2];
				$content		.= "$value will be added to $inp_advisor's $theLevel class number $theSequence<br />";
				$emailData		.= "$inp_advisor has indicated the following students need to be added to the advisor's $theLevel class number $theSequence:<br />$value<br />";
				$gotExtras		= TRUE;
			}
			// If have an Extras, send an email with the information
			if ($gotExtras) {
				$mySubject				= "CW Academy Additional Students to be Added to Advisor Class";
				if ($testMode) {
					$email_to				= "kcgator@gmail.com";
					$increment++;
					$mailCode				= 5;
					$mySubject				= "TESTMODE $mySubject";
				} else {
					$email_to				= "kcgator@gmail.com";
					$mailCode				= 19;
				}
				$myContent					= "<p>Advisor $inp_advisor has verified his students. 
												<p>$emailData<br /><br />
												73,<br/>
												CW Academy</p>";
				$mailResult		= emailFromCWA_v2(array('theRecipient'=>$email_to,
															'theSubject'=>$mySubject,
															'jobname'=>$jobname,
															'theContent'=>$myContent,
															'mailCode'=>$mailCode,
															'increment'=>$increment,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug));
				if ($mailResult === FALSE) {
					if ($doDebug) {
						echo "The mail function failed<br />";
					}
				}
			}
		
			// Finally, update the advisor's class_verified so we don't ask again.
			$currentDate		= $initializationArray['currentDate'];

			$sql						= "select advisor_call_sign, 
												  advisor_action_log 
											from $advisorTableName 
											where advisor_id=$inp_advisor_id";
			$wpw1_cwa_advisor			= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numARows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$advisor_call_sign					= $advisorRow->advisor_call_sign;
						$advisor_action_log					= $advisorRow->advisor_action_log;
						if ($gotExtras) {
							$advisor_action_log	.= " / $myDate CLASSVERIFY $inp_advisor has verified the students in his class and has 
additional students: $emailData ";
						} else {
							$advisor_action_log		.= " / $myDate CLASSVERIFY $inp_advisor has verified the students in his class ";
						}
						$updateData				= array('advisor_class_verified'=>'Y',
														'advisor_action_log'=>$advisor_action_log);
						$updateFormat			= array('%s','%s');
						$advisorUpdateData		= array('tableName'=>$advisorTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateData,
														'inp_format'=>$updateFormat,
														'jobname'=>$jobname,
														'inp_id'=>$inp_advisor_id,
														'inp_callsign'=>$advisor_call_sign,
														'inp_semester'=>$student_semester,
														'inp_who'=>$advisor_call_sign,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= updateAdvisor($advisorUpdateData);
						if ($updateResult[0] === FALSE) {
							$myError	= $wpdb->last_error;
							$mySql		= $wpdb->last_query;
							$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
							if ($doDebug) {
								echo $errorMsg;
							}
							sendErrorEmail($errorMsg);
							$content		.= "Unable to update content in $advisorTableName<br />";
						} else {
							if ($doDebug) {
								echo "Successfully updated $advisor_call_sign record at $inp_advisor_id<br />";
							}
							if ($doDebug) {
								echo "Advisor $inp_advisor (ID: $inp_advisor_id) Updated<br />";
							}
							// now resolve the reminder
							resolve_reminder($advisor_call_sign,$token,$testMode,$doDebug);						
						}
					}
				} else {
					if ($doDebug) {
						echo "Advisor record for $inp_advisor not found to update.<br />";
					}
				}
			}
		}
		$content	.= "<p>Verification Completed. Thank you. You can close this window.</p>";		
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<br /><br /><p>Report pass $strPass took $elapsedTime seconds to run</p>";
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
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('verify_advisor_class', 'verify_advisor_class_func');