function advisors_with_unconfirmed_students_func() {

/*
	Send a reminder email to the advisor on students not yet confirmed
	Sent one week after class assignments are sent. Then every three
	days after until the semester starts
	
	Created 21March2020 by Roland
	modified 4Mar21 by Roland to change Joe Fisher's email address
	modified 9Aug21 by Roland for advisor and advisorClass pods
	modified and version changed to v2 15Aug2021 by Roland to display the 
		unverified students in a much more compact table form
	Modified 19Jan2022 by Roland to use tables rather than  pods
	Modified 21Oct22 by Roland for the new timezone table formats
	Modified 28Dec22 by Roland to show the advisors, allow a message to be entered, 
		and then send the email
	Modified 15Apr23 by Roland to correct action log handling
	Modified 12Jul23 by Roland to use current tables only
	Modified 11Oct24 by Roland for new database
*/	

	global $wpdb;

	$context = CWA_Context::getInstance();
	$validUser				= $context->validUser;
	$userName  				= $context->userName;
	$currentDate			= $context->currentDate;
	$currentTimestamp		= $context->currentTimestamp;
	$validTestmode			= $context->validTestmode;
	$siteURL				= $context->siteurl;
	$currentSemester		= $context->currentSemester;
	$nextSemester			= $context->nextSemester;
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$strPass					= "1";
	$testEmail					= "rolandksmith@gmail.com";
//	$testEmail					= "kcgator@gmail.com,rolandksmith@gmail.com";
	$jobname					= "Advisors with Unconfirmed Students";
	$requestString				= "";
	$request_type				= "full";
	$inp_semester				= "";
	$emailCount					= 0;
	$totalUnconfirmed			= 0;
	$advisorArray				= array();		
	$advisorsToEmail			= array();
	$inp_msg					= '';
	$encodedArray				= array();
	$myInt						= 0;
	$increment					= 0;
	$inp_include				= 'sonly';
	$currentDate				= $context->currentDate;
	$theURL						= "$siteURL/cwa-advisors-with-unconfirmed-students/";
	
	$theSemester				= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$theSemester			= $nextSemester;
	}

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
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "inp_msg") {
				$inp_msg		 = $str_value;
				$inp_msg		 = filter_var($inp_msg,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_include") {
				$inp_include		 = $str_value;
				$inp_include		 = filter_var($inp_include,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "encodedArray") {
				$encodedArray		 = stripslashes($str_value);
//				$encodedArray		 = filter_var($encodedArray,FILTER_UNSAFE_RAW);
			}
		}
	}
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}



	$content = "";	
	
		if ($testMode) {
			echo "Operating in TestMode<br />";
			$content				.= "<p><b>Operating in Test Mode</b></p>";
			$studentTableName		= 'wpw1_cwa_student2';
			$advisorTableName	= 'wpw1_cwa_advisor2';
			$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
			$userMasterTableName	= 'wpw1_cwa_user_master2';
			$thisMode				= 'TM';
		} else {
			$studentTableName		= 'wpw1_cwa_student';
			$advisorTableName	= 'wpw1_cwa_advisor';
			$advisorClassTableName	= 'wpw1_cwa_advisorclass';
			$userMasterTableName	= 'wpw1_cwa_user_master';
			$thisMode				= 'PD';
		}
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
								<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
									<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>";
	} else {
		$testModeOption	= '';
	}
	

	if ("1" == $strPass) {
		$content .= "<h3>Send Advisors Reminder Emails to Confirm Class Participation</h3>
					<p>Emails can be sent to advisors who have students assigned but not yet confirmed. 
					Click 'Submit' below to run the program.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data''>
					<input type='hidden' name='strpass' value='2'>
					$testModeOption<br /><br />
					<input class='formInputButton' type='submit' value='Submit' />
					</form>";
		
	} elseif ("2" == $strPass) {
		$content .= "<h3>$jobname for $theSemester Semester</h3>";

		if ($doDebug) {
			echo "Got to pass 2 <br />";
		}
		
		// build the array of advisors with unverified students
		$sql				= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_status = 'S' 
								and student_semester = '$theSemester'";
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

					if ($doDebug) {
						echo "processing student $student_call_sign. Assigned advisor: $student_assigned_advisor<br />";
					}
					if (!array_key_exists($student_assigned_advisor,$advisorArray)) {
						$advisorArray[$student_assigned_advisor]	= 1;
					} else {
						$advisorArray[$student_assigned_advisor]++;
					}
				}
				ksort($advisorArray);
				if ($doDebug) {
					echo "<br /><b>Advisor Array</b>:<br /><pre>";
					print_r($advisorArray);
					echo "</pre><br />";
				}
				$myInt				= count($advisorArray);
				if ($myInt == 0) {
					$content		.= "<p>There are no unconfirmed students.</p>";
				} else {
					$content		.= "<p>$myInt advisors with unconfirmed students</p>";
					foreach ($advisorArray as $thisAdvisor=>$studentCount) {
					
						if ($doDebug) {
							echo "<br />Processing $thisAdvisor<br />";
						}

						// getting the advisor information
						$sql				= "select * from $advisorTableName 
												left join $userMasterTableName on user_call_sign = advisor_call_sign 
												where advisor_call_sign='$thisAdvisor' 
												and advisor_semester='$theSemester'";
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


									$content			.= "$advisor_last_name, $advisor_first_name ($advisor_call_sign) has $studentCount unverified students<br />";

									$advisorsToEmail[]	= $advisor_call_sign;
								}
							} else {
								$content				.= "<b>ERROR:</b> $thisAdvisor not found in $advisorTableName<br />";
							}
						}
					}
					if ($doDebug) {
						echo "have advisorsToEmail: <br /><pre>";
						print_r($advisorsToEmail);
						echo "</pre><br />";
					}
					$encodedArray		= json_encode($advisorsToEmail);
					if ($doDebug) {
						echo "encoded: $encodedArray<br />";
					}
					$content			.= "<br /><p>Please enter any message to be included in the email to advisors with 
											unconfirmed students. Select whether to send the full class or only the 
											unconfirmed student. Then click Submit to send the emails.</p>
											<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data''>
											<input type='hidden' name='strpass' value='3'>
											<input type='hidden' name='encodedArray' value='$encodedArray'>
											<textarea class='formInputText' name='inp_msg' cols='50' rows='5'></textarea><br />
											Full Class: <input type='radio' class='formInputButton' name='inp_include' value='Full'><br />
											Unconfirmed: <input type='radio' class='formInputButton' name='inp_include' value='sonly' checked><br /><br />
											<input type='submit' class='formInputButton' name='submit' value='Submit'></form>";
				}
			} else {
				$content	.= "<p>No $theSemester unconfirmed students found in $studentTableName table</p>";
			}
		}


	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at Pass 3<br />";
		}
		
		$content				.= "<h3>$jobname</h3>";


		$advisorsToEmail		= json_decode($encodedArray);
		if ($doDebug) {
			echo "decoded the array. Results:<br /><pre>";
			print_r($advisorsToEmail);
			echo "</pre><br />";
		}
		
		if ($inp_msg != '') {
			$inp_msg			= "<p>$inp_msg</p>";
		}
		
		foreach($advisorsToEmail as $thisAdvisor) {
			// get the advisor information
			$sql				= "select * from $advisorTableName 
									left join $userMasterTableName on user_call_sign = advisor_call_sign 
									where advisor_call_sign='$thisAdvisor' 
									and advisor_semester='$theSemester'";
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


						/// start the email to the advisor
						$email_to_advisor			= "To: $user_last_name, $user_first_name ($user_call_sign):
$inp_msg
<p>You still have the following unconfirmed student assignments for the 
$theSemester Semester. Please verify the status of these students.<br /><p>";

						$prepareResult				= prepare_preassigned_class_display($user_call_sign,$theSemester,$inp_include,TRUE,FALSE,FALSE,$testMode,$doDebug);
						if ($prepareResult[0] == FALSE) {
							$content				.= "<p>Getting the data to send to the advisor failed. $prepareResult[1]<br />";
							$errorMsg				= "prepare_preassigned_class_display failed: $prepareResult[1]";
							sendErrorEmail($errorMsg);
						} else {
							$email_to_advisor		.= $prepareResult[1];
							$email_to_advisor .= "<p>If you have any questions or issues with the assignments go to the CW Academy 
												  website under CW Class Resolution and contact the appropriate person.<br /><br />
												  Thanks for your service as an advisor!<br />
												  CW Academy</p>";

							$theSubject				= "CW Academy Reminder: Unconfirmed Class Participants";
							if ($testMode) {
								$theSubject			= "TESTMODE $theSubject";
								$mailCode			= 2;
								$theRecipient		= 'rolandksmith@gmail.com';
								$increment++;
							} else {
								$theRecipient		= $advisor_email;
								$mailCode			= 12;
							}
							$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
																		'theSubject'=>$theSubject,
																		'jobnamd'=>'Advisor Class Reminder',
																		'theContent'=>$email_to_advisor,
																		'mailCode'=>$mailCode,
																		'increment'=>$increment,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug));
							if ($mailResult === TRUE) {
								if ($doDebug) {
									echo "An email was sent to $theRecipient<br />";
								}
								$content .= "<p>Advisor $thisAdvisor has unconfirmed students. An email was sent to $theRecipient</p>";
								$emailCount++;
							} else {
								$content .= "The mail send function to $email_to failed.<br /><br />";
							}
						}
					}
				} else {
					$content			.= "<p>SERIOUS ERROR: No advisor record found in $advisorTableName for $thisAdvisor</p>";
				}
			}
		}
		$content	.= "<br />$emailCount advisor emails sent<br />";
		
		
	}
	$thisTime 					= date('Y-m-d H:i:s',$currentTimestamp);
	$content					.= "<br /><br /><br /><p>Report displayed at $thisTime.</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d',$currentTimestamp);
	$nowTime		= date('H:i:s',$currentTimestamp);
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
add_shortcode ('advisors_with_unconfirmed_students', 'advisors_with_unconfirmed_students_func');

