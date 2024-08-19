function advisor_class_reminder_func() {

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
*/	

	global $wpdb;

	$initializationArray 	= data_initialization_func();
	$validUser				= $initializationArray['validUser'];
	$userName  				= $initializationArray['userName'];
	$currentDate			= $initializationArray['currentDate'];
	$currentTimestamp		= $initializationArray['currentTimestamp'];
	$validTestmode			= $initializationArray['validTestmode'];
	$siteURL				= $initializationArray['siteurl'];
	$currentSemester		= $initializationArray['currentSemester'];
	$nextSemester			= $initializationArray['nextSemester'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$strPass					= "1";
	$testEmail					= "rolandksmith@gmail.com";
//	$testEmail					= "kcgator@gmail.com,rolandksmith@gmail.com";
	$jobname					= "Advisor Class Reminder";
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
	$currentDate				= $initializationArray['currentDate'];
	$theURL						= "$siteURL/cwa-advisor-class-reminder/";
	
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
			echo "Operating in TestMode<br />";
			$content				.= "<p><b>Operating in Test Mode</b></p>";
			$studentTableName		= 'wpw1_cwa_consolidated_student2';
			$advisorTableName	= 'wpw1_cwa_consolidated_advisor2';
			$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
			$thisMode				= 'TM';
		} else {
			$studentTableName		= 'wpw1_cwa_consolidated_student';
			$advisorTableName	= 'wpw1_cwa_consolidated_advisor';
			$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
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
		$content .= "<h3>Advisors with Unconfirmed Students</h3>";

		if ($doDebug) {
			echo "Got to pass 2 <br />";
		}
		
// build the array of advisors with unverified students
		$sql				= "select * from $studentTableName 
								where semester='$theSemester' 
									and student_status='S' ";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $studentTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_phone  						= $studentRow->phone;
					$student_ph_code						= $studentRow->ph_code;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_country_code					= $studentRow->country_code;
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
					$student_action_log  					= stripslashes($studentRow->action_log);
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

					$student_last_name 						= no_magic_quotes($student_last_name);

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
												where call_sign='$thisAdvisor' 
												and semester='$theSemester'";
						$wpw1_cwa_advisor	= $wpdb->get_results($sql);
						if ($wpw1_cwa_advisor === FALSE) {
							$myError			= $wpdb->last_error;
							$myQuery			= $wpdb->last_query;
							if ($doDebug) {
								echo "Reading $advisorTableName table failed<br />
									  wpdb->last_query: $myQuery<br />
									  wpdb->last_error: $myError<br />";
							}
							$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
							sendErrorEmail($errorMsg);
							$content			.= "<b>ERROR:</b> failure attempting to get $thisAdvisor from $advisorTableName<br />";
						} else {
							$numARows			= $wpdb->num_rows;
							if ($doDebug) {
								$myStr			= $wpdb->last_query;
								echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
							}
							if ($numARows > 0) {
								foreach ($wpw1_cwa_advisor as $advisorRow) {
									$advisor_ID							= $advisorRow->advisor_id;
									$advisor_select_sequence 			= $advisorRow->select_sequence;
									$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
									$advisor_first_name 				= $advisorRow->first_name;
									$advisor_last_name 					= stripslashes($advisorRow->last_name);
									$advisor_email 						= strtolower($advisorRow->email);
									$advisor_phone						= $advisorRow->phone;
									$advisor_ph_code					= $advisorRow->ph_code;				// new
									$advisor_text_message 				= $advisorRow->text_message;
									$advisor_city 						= $advisorRow->city;
									$advisor_state 						= $advisorRow->state;
									$advisor_zip_code 					= $advisorRow->zip_code;
									$advisor_country 					= $advisorRow->country;
									$advisor_country_code				= $advisorRow->country_code;		// new
									$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
									$advisor_signal						= $advisorRow->signal_app;			// new
									$advisor_telegram					= $advisorRow->telegram_app;		// new
									$advisor_messenger					= $advisorRow->messenger_app;		// new
									$advisor_time_zone 					= $advisorRow->time_zone;
									$advisor_timezone_id				= $advisorRow->timezone_id;			// new
									$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
									$advisor_semester 					= $advisorRow->semester;
									$advisor_survey_score 				= $advisorRow->survey_score;
									$advisor_languages 					= $advisorRow->languages;
									$advisor_fifo_date 					= $advisorRow->fifo_date;
									$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
									$advisor_verify_email_date 			= $advisorRow->verify_email_date;
									$advisor_verify_email_number 		= $advisorRow->verify_email_number;
									$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
									$advisor_action_log 				= $advisorRow->action_log;
									$advisor_class_verified 			= $advisorRow->class_verified;
									$advisor_control_code 				= $advisorRow->control_code;
									$advisor_date_created 				= $advisorRow->date_created;
									$advisor_date_updated 				= $advisorRow->date_updated;
	
									$advisor_last_name 					= no_magic_quotes($advisor_last_name);

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
				$content	.= "<b>ERROR:</b> No students found in $studentTableName table<br />";
			}
		}
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at Pass 3<br />";
		}
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
									where call_sign='$thisAdvisor' 
									and semester='$theSemester'";
			$wpw1_cwa_advisor	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				$myError			= $wpdb->last_error;
				$myQuery			= $wpdb->last_query;
				if ($doDebug) {
					echo "Reading $advisorTableName table failed<br />
						  wpdb->last_query: $myQuery<br />
						  wpdb->last_error: $myError<br />";
				}
				$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
				sendErrorEmail($errorMsg);
				$content			.= "<b>ERROR:</b> failure attempting to get $thisAdvisor from $advisorTableName<br />";
			} else {
				$numARows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$advisor_ID							= $advisorRow->advisor_id;
						$advisor_select_sequence 			= $advisorRow->select_sequence;
						$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
						$advisor_first_name 				= $advisorRow->first_name;
						$advisor_last_name 					= stripslashes($advisorRow->last_name);
						$advisor_email 						= strtolower($advisorRow->email);
						$advisor_phone						= $advisorRow->phone;
						$advisor_ph_code					= $advisorRow->ph_code;				// new
						$advisor_text_message 				= $advisorRow->text_message;
						$advisor_city 						= $advisorRow->city;
						$advisor_state 						= $advisorRow->state;
						$advisor_zip_code 					= $advisorRow->zip_code;
						$advisor_country 					= $advisorRow->country;
						$advisor_country_code				= $advisorRow->country_code;		// new
						$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
						$advisor_signal						= $advisorRow->signal_app;			// new
						$advisor_telegram					= $advisorRow->telegram_app;		// new
						$advisor_messenger					= $advisorRow->messenger_app;		// new
						$advisor_time_zone 					= $advisorRow->time_zone;
						$advisor_timezone_id				= $advisorRow->timezone_id;			// new
						$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
						$advisor_semester 					= $advisorRow->semester;
						$advisor_survey_score 				= $advisorRow->survey_score;
						$advisor_languages 					= $advisorRow->languages;
						$advisor_fifo_date 					= $advisorRow->fifo_date;
						$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
						$advisor_verify_email_date 			= $advisorRow->verify_email_date;
						$advisor_verify_email_number 		= $advisorRow->verify_email_number;
						$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
						$advisor_action_log 				= $advisorRow->action_log;
						$advisor_class_verified 			= $advisorRow->class_verified;
						$advisor_control_code 				= $advisorRow->control_code;
						$advisor_date_created 				= $advisorRow->date_created;
						$advisor_date_updated 				= $advisorRow->date_updated;

						$advisor_last_name 					= no_magic_quotes($advisor_last_name);
			

						/// start the email to the advisor
						$email_to_advisor			= "To: $advisor_last_name, $advisor_first_name ($advisor_call_sign):
$inp_msg
<p>You still have the following unconfirmed student assignments for the 
$theSemester Semester. Please verify the status of these students.<br /><p>";

						$prepareResult				= prepare_preassigned_class_display($advisor_call_sign,$theSemester,$inp_include,TRUE,FALSE,FALSE,$testMode,$doDebug);
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
	
}
add_shortcode ('advisor_class_reminder', 'advisor_class_reminder_func');
