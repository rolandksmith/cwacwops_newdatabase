function prepare_advisors_for_student_assignments_func() {

/* Prepare advisors for student assignments
 *
 *	Read each advisor record
 *		If the advisor's survey score is not 6 and not 7
 *			check advisor to see what the survey score was
 *			if it was a 6, set this advisor's survey_score to 6
 *
 *		If an advisor's survey score is 7, then any of the advisor's
 *			past students who have signed up for a class are to be assigned
 *			to this advisor. Then, the advisor's fifo date is set to 2030/12/31
 *			so that the advisor will be among the last to get any additional students
 *
 *
 *	modified 4Mar21 by Roland to change Joe Fisher's email address
 	modified 18July21 by Roland for the new advisor pod layouts
 	modified 18Jan2022 by Roland to use tables rather than pods
 	modified 25Oct22 by Roland for the new timezone table format
 	modified 16Apr23 by Roland to fix action_log
 	Modified 13Jul23 by Roland to use consolidated tables
*/

	global $wpdb, $doDebug, $testMode, $saveChanges, $jobname;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$saveChanges					= FALSE;
	$initializationArray 			= data_initialization_func();
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

//	if ($doDebug) {
			ini_set('display_errors','1');
			error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

		$strPass					= "1";
		$requestType				= '';
		$advisorArray				= array();
		$preAssignedCount			= 0;
		$updatedFifo				= 0;
		$sevenArray					= array();
		$actionDate					= date('dMY H:i');
		$trashCount					= 0;
		$emailsSent					= 0;
		$sevenSurveyScore			= FALSE;
		$jobname					= 'Prepare Advisors for Student Assignments';
		$logDate					= date('Y-m-d H:i');
		$nextSemester				= $initializationArray['nextSemester'];	
		$prevSemester				= $initializationArray['prevSemester'];
		$userName					= $initializationArray['userName'];
		$fieldTest					= array('action_log','post_status','post_title','control_code');
		$theURL						= "$siteURL/cwa-prepare-advisors-for-student-assignments/";
		$advisorUpdateURL			= "$siteURL/cwa-display-and-update-advisor-information/";
		$studentUpdateURL			= "$siteURL/cwa-display-and-update-student-information/";
		
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
				if ($str_key 		== "inp_save") {
					$inp_save	 = $str_value;
					$inp_save	 = filter_var($inp_save,FILTER_UNSAFE_RAW);
					if ($inp_save == 'save') {
						$saveChanges = TRUE;
					}
				}
			}
		}

	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
	<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE' checked> TESTMODE</td></tr>
<tr><td>Verbose Debugging?</td>
<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
	<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
		} else {
			$testModeOption	= '';
		}


	function sendAdvisorEmail($advisor_call_sign,$advisor_email) {

		global $doDebug, $testMode, $saveChanges, $jobname;
					
//		$testEmailTo				= 'rolandksmith@gmail.com';
		$testEmailTo				= 'kcgator@gmail.com';
		$emailContent				= "To: $advisor_call_sign at $advisor_email
<p>Your registration to be an advisor has been placed on hold. For further information 
and clarification please contact the appropriate person at 
<a href='https://cwops.org/cwa-class-resolution/'>CW Academy Class Resolution</a>.</p>
<p>73,
<br />CW Academy";
		if ($saveChanges) {
			$mySubject = "CW Academy Your CW Academy Advisor Registration Is On Hold";
			if ($testMode) {
				$myTo 		= $testEmailTo;
				$mySubject 	= "TESTMODE CWA $advisor_call_sign Your CW Academy Advisor Registration Is On Hold";
				$mailCode	= 5;
			} else {
				$myTo	= $advisor_email;
			}
			$increment		= 0;
			$mailCode		= 20;
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
													'theSubject'=>$mySubject,
													'jobname'=>$jobname,
													'theContent'=>$emailContent,
													'mailCode'=>$mailCode,
													'increment'=>$increment,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug));
// $mailResult = TRUE;
			if ($mailResult === TRUE) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			if ($doDebug) {
				echo "saveChanges is off, no email sent to $advisor_call_sign ($advisor_email)";
			}
		}
	}
		
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
		} else {
		}
			if ($testMode) {
				$studentTableName			= 'wpw1_cwa_consolidated_student2';
				$advisorTableName			= 'wpw1_cwa_consolidated_advisor2';
				$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass2';
				echo "Function is under development. Using test data, not the production data.<br />";
				$theStatement				= "<p>Running in TESTMODE using Test Data.</p>";
			} else {
				$studentTableName			= 'wpw1_cwa_consolidated_student';
				$advisorTableName			= 'wpw1_cwa_consolidated_advisor';
				$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
				$theStatement				= "";
			}

		if ("1" == $strPass) {
			if ($doDebug) {
				echo "Function starting.<br />";
			}
			$content 		.= "<h3>Prepare Advisors for Student Assignments</h3>
								$theStatement
								<p>Function will read each advisor record<p>
								<p>If the advisor's survey score is not 6 and not 7: 
								<br />check advisor to see what the survey score was.
								<br />if it was a 6, set this advisor verify to R.
								<br />Otherwise, continue with the next advisor record.</p>
								<p>If an advisor's survey score is 7, then any of the advisor's  
								past students who have signed up for a class are to be assigned 
								to this advisor. Then, the advisor's fifo date is set to 2030/12/31 
								so that the advisor will be among the last to get any additional students.</p>
								<p>If an advisor's survey score is 6, then set the advisor to R</p>
								<p>Click on 'Submit' to start the procss.</p> 
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								<table>
								$testModeOption
								<tr><td>Save Changes?</td>
									<td><input type='radio' name='inp_save' class='formInputButton' value='No' checked> No<br />
										<input type='radio' name='inp_save' class='formInputButton' value='save' > Yes</td></tr>
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form></p>";

///// Pass 2 -- do the work


		} elseif ("2" == $strPass) {
			if ($doDebug) {
				echo "<br />at pass 2<br />";
			}
		
			$content				.= "<h3>Prepare Advisors for Student Assignments</h3>";		

			$sql					= "select * from $advisorTableName 
										where semester='$nextSemester' 
										order by call_sign";
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

						$advisorUpdateLink					= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$advisor_call_sign&inp_table=advisor&strpass=2' target='_blank'>$advisor_call_sign</a>";
						
						if ($doDebug) {
							echo "<br />Processing $advisor_call_sign. Survey score: $advisor_survey_score; Verify Response: $advisor_verify_response<br />";
						}
						if ($advisor_verify_response == 'R') {
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;&nbsp;advisor has a verify_respone of $advisor_verify_response. Bypassing. <br />";
							}
						} else {
							// general test for all advisors except those with a survey score of 6				
							if ($advisor_survey_score != 6 && $advisor_survey_score != 9 && $advisor_survey_score != 13) {
								// Look for this advisor in past semesters and see if survey score is 6 (bad actor)
								$dumpAdvisor				= FALSE;
								$sevenSurveyScore			= FALSE;
								$sql						= "select * from $advisorTableName 
																where call_sign='$advisor_call_sign' 
																and semester != '$nextSemester' 
																order by date_created ";
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
								} else {	
									$numPARows				= $wpdb->num_rows;
									if ($doDebug) {
										$myStr				= $wpdb->last_query;
										echo "ran $myStr<br />and found $numPARows rows in $advisorTableName<br />";
									}
									if ($numPARows > 0) {
										$theSurveyScore								= 0;
										foreach ($wpw1_cwa_advisor as $advisorRow) {
											$advisor_ID						= $advisorRow->advisor_id;
											$advisor_select_sequence 			= $advisorRow->select_sequence;
											$advisor_call_sign 				= strtoupper($advisorRow->call_sign);
											$advisor_first_name 				= $advisorRow->first_name;
											$advisor_last_name 				= stripslashes($advisorRow->last_name);
											$advisor_email 					= $advisorRow->email;
											$advisor_ph_code					= $advisorRow->ph_code;
											$advisor_phone						= $advisorRow->phone;
											$advisor_text_message 				= $advisorRow->text_message;
											$advisor_city 						= $advisorRow->city;
											$advisor_state 					= $advisorRow->state;
											$advisor_zip_code 					= $advisorRow->zip_code;
											$advisor_country 					= $advisorRow->country;
											$advisor_country_code				= $advisorRow->country_code;
											$advisor_time_zone 				= $advisorRow->time_zone;
											$advisor_timezone_id				= $advisorRow->timezone_id;
											$advisor_timezone_offset			= $advisorRow->timezone_offset;
											$advisor_whatsapp					= $advisorRow->whatsapp_app;
											$advisor_signal					= $advisorRow->signal_app;
											$advisor_telgram					= $advisorRow->telegram_app;
											$advisor_messenger					= $advisorRow->messenger_app;
											$advisor_semester 					= $advisorRow->semester;
											$advisor_survey_score 				= $advisorRow->survey_score;
											$advisor_languages 				= $advisorRow->languages;
											$advisor_fifo_date 				= $advisorRow->fifo_date;
											$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
											$advisor_verify_email_date 		= $advisorRow->verify_email_date;
											$advisor_verify_email_number 		= $advisorRow->verify_email_number;
											$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
											$advisor_action_log 				= $advisorRow->action_log;
											$advisor_class_verified 			= $advisorRow->class_verified;
											$advisor_control_code 				= $advisorRow->control_code;
											$advisor_date_created 				= $advisorRow->date_created;
											$advisor_date_updated 				= $advisorRow->date_updated;
									
											if ($doDebug) {
												echo "Got $advisorTableName record for $advisor_call_sign. Checking survey score of $advisor_survey_score<br />";
											}
											if ($advisor_survey_score == 6) {
												$theSurveyScore			   = $advisor_survey_score;
											}
											if ($advisor_survey_score == 7) {
												$sevenSurveyScore			= TRUE;
											}
										}			// end of while loop
										if ($theSurveyScore == '6') {
											$content	.= "<br />Setting survey_score to 6 for $advisorUpdateLink as advisor shows a survey score or  6<br />";
											if ($doDebug) {
												echo "advisor survey score was 6. Setting survey_score for this advisor to 6<br />";
											}
											$advisor_action_log	.= "$advisor_action_log / actionDate ADVPREP Past advisor record had survey_score of 6. Setting survey_score to 6. ";
											$updateParams		= array('survey_score'=>6,
																		'action_log'=>$advisor_action_log);
											$updateFormat		= array('%d','%s');
											if ($saveChanges) {
												$advisorUpdateData		= array('tableName'=>$advisorTableName,
																				'inp_method'=>'update',
																				'inp_data'=>$updateParams,
																				'inp_format'=>$updateFormat,
																				'jobname'=>$jobname,
																				'inp_id'=>$advisor_ID,
																				'inp_callsign'=>$advisor_call_sign,
																				'inp_semester'=>$advisor_semester,
																				'inp_who'=>$userName,
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
														echo "Successfully updated $advisor_call_sign record at $advisor_ID<br />";
													}
													$trashCount++;
													$theResult	= sendAdvisorEmail($advisor_call_sign,$advisor_email);
													if ($theResult) {
														$emailsSent++;
													} else {
														echo "Email to $advisor_call_sign putting registration on hold failed<br />";
													}									
												}
											}
									
										} else {
											if ($doDebug) {
												echo "advisor survey score was not 6. Continuing<br />";
											}
											if ($sevenSurveyScore) {
												$advisor_survey_score 				= 7;
												$updateParams['survey_score']		= 7;
												$updateFormat[]						= '%d';
												if ($doDebug) {
													echo "$advisorTableName has a record with survey score of 7. Setting this advisor to 7<br />";
												}
											}					
											if ($advisor_survey_score == 7) {
												if ($doDebug) {
													echo "advisor has a survey score of 7<br />";
												}
												$studentFound			= FALSE;
												$studentArray			= array();
												/// build advisorArray of the advisor classes: advisorArray['advisor_call_sign|level] = sequence
												/// get all of the advisor classes for the next semester
												/// if advisor class is incomplete, ignore the record
								
												$sql					= "select * from $advisorClassTableName 
																		   where semester='$nextSemester' 
																		   		and advisor_call_sign='$advisor_call_sign'";
												$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
												if ($wpw1_cwa_advisorclass === FALSE) {
													$myError			= $wpdb->last_error;
													$myQuery			= $wpdb->last_query;
													if ($doDebug) {
														echo "Reading $advisorClassTableName table failed<br />
															  wpdb->last_query: $myQuery<br />
															  wpdb->last_error: $myError<br />";
													}
													$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
													sendErrorEmail($errorMsg);
												} else {
													$numACRows						= $wpdb->num_rows;
													if ($doDebug) {
														$myStr						= $wpdb->last_query;
														echo "ran $myStr<br />and found $numACRows rows<br />";
													}
													if ($numACRows > 0) {
														foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
															$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
															$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
															$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
															$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
															$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
															$advisorClass_sequence 					= $advisorClassRow->sequence;
															$advisorClass_semester 					= $advisorClassRow->semester;
															$advisorClass_timezone 					= $advisorClassRow->time_zone;
															$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
															$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
															$advisorClass_level 					= $advisorClassRow->level;
															$advisorClass_class_size 				= $advisorClassRow->class_size;
															$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
															$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
															$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
															$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
															$advisorClass_action_log 				= $advisorClassRow->action_log;
															$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
															$advisorClass_date_created				= $advisorClassRow->date_created;
															$advisorClass_date_updated				= $advisorClassRow->date_updated;
								
															$advisorArrayKey							= "$advisorClass_advisor_call_sign|$advisorClass_level";
															$advisorArray[$advisorArrayKey]				= $advisorClass_sequence;

														}
														$content					.= "<br />Processing $advisorUpdateLink who has a survey score of 7<br />";

														// read the class pod for all previous advisor classes and build an array of students who
														// have taken a class from this advisor
														$studentArray				= array();
														$sql					 	= "select * from $advisorClassTableName 
																					   where advisor_call_sign='$advisor_call_sign' 
																					   and semester != '$nextSemester' ";
														$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
														if ($wpw1_cwa_advisorclass === FALSE) {
															$myError			= $wpdb->last_error;
															$myQuery			= $wpdb->last_query;
															if ($doDebug) {
																echo "Reading $advisorClassTableName table failed<br />
																	  wpdb->last_query: $myQuery<br />
																	  wpdb->last_error: $myError<br />";
															}
															$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
															sendErrorEmail($errorMsg);
														} else {
															$numACRows				= $wpdb->num_rows;
															if ($doDebug) {
																$myStr				= $wpdb->last_query;
																echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName table<br />";
															}
															if ($numACRows > 0) {
																foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
																	$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
																	$advisorClass_advisor_callsign 		= $advisorClassRow->advisor_call_sign;
																	$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
																	$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
																	$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
																	$advisorClass_sequence 				= $advisorClassRow->sequence;
																	$advisorClass_semester 				= $advisorClassRow->semester;
																	$advisorClass_timezone 				= $advisorClassRow->time_zone;
																	$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
																	$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
																	$advisorClass_level 					= $advisorClassRow->level;
																	$advisorClass_class_size 				= $advisorClassRow->class_size;
																	$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
																	$advisorClass_class_schedule_times 	= $advisorClassRow->class_schedule_times;
																	$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
																	$advisorClass_class_schedule_times_utc	= $advisorClassRow->class_schedule_times_utc;
																	$advisorClass_action_log 				= $advisorClassRow->action_log;
																	$advisorClass_class_incomplete 		= $advisorClassRow->class_incomplete;
																	$advisorClass_date_created				= $advisorClassRow->date_created;
																	$advisorClass_date_updated				= $advisorClassRow->date_updated;
																	$advisorClass_student01 				= $advisorClassRow->student01;
																	$advisorClass_student02 				= $advisorClassRow->student02;
																	$advisorClass_student03 				= $advisorClassRow->student03;
																	$advisorClass_student04 				= $advisorClassRow->student04;
																	$advisorClass_student05 				= $advisorClassRow->student05;
																	$advisorClass_student06 				= $advisorClassRow->student06;
																	$advisorClass_student07 				= $advisorClassRow->student07;
																	$advisorClass_student08 				= $advisorClassRow->student08;
																	$advisorClass_student09 				= $advisorClassRow->student09;
																	$advisorClass_student10 				= $advisorClassRow->student10;
																	$advisorClass_student11 				= $advisorClassRow->student11;
																	$advisorClass_student12 				= $advisorClassRow->student12;
																	$advisorClass_student13 				= $advisorClassRow->student13;
																	$advisorClass_student14 				= $advisorClassRow->student14;
																	$advisorClass_student15 				= $advisorClassRow->student15;
																	$advisorClass_student16 				= $advisorClassRow->student16;
																	$advisorClass_student17 				= $advisorClassRow->student17;
																	$advisorClass_student18 				= $advisorClassRow->student18;
																	$advisorClass_student19 				= $advisorClassRow->student19;
																	$advisorClass_student20 				= $advisorClassRow->student20;
																	$advisorClass_student21 				= $advisorClassRow->student21;
																	$advisorClass_student22 				= $advisorClassRow->student22;
																	$advisorClass_student23 				= $advisorClassRow->student23;
																	$advisorClass_student24 				= $advisorClassRow->student24;
																	$advisorClass_student25 				= $advisorClassRow->student25;
																	$advisorClass_student26 				= $advisorClassRow->student26;
																	$advisorClass_student27 				= $advisorClassRow->student27;
																	$advisorClass_student28 				= $advisorClassRow->student28;
																	$advisorClass_student29 				= $advisorClassRow->student29;
																	$advisorClass_student30 				= $advisorClassRow->student30;
																	$class_number_students						= $advisorClassRow->number_students;
																	$class_evaluation_complete 					= $advisorClassRow->evaluation_complete;
																	$class_comments								= $advisorClassRow->class_comments;
																	$copycontrol								= $advisorClassRow->copy_control;

																	if ($doDebug) {
																		echo "Processing $advisorClass_advisor_callsign Semester: $advisorClass_semester sequence: $advisorClass_sequence<br />";
																	}
																	for ($snum=1;$snum<=$class_number_students;$snum++) {
																		if ($snum < 10) {
																			$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
																		} else {
																			$strSnum		= strval($snum);
																		}
																		if ($doDebug) {
																			echo "Processing advisorClass_student$strSnum<br />";
																		}
																		$theInfo			= ${'advisorClass_student' . $strSnum};
																		if ($theInfo != '') {
																			if (!in_array($theInfo,$studentArray)) {
																				$studentArray[]	= $theInfo;
																				if ($doDebug) {
																					echo "added $theInfo to studentArray<br />";
																				}
																			} else {
																				if ($doDebug) {
																					echo "$theInfo already in the array<br />";
																				}
																			}
																		}
																	}
																}				// end of while ... all class records processed
															} else {
																if ($doDebug) {
																	echo "No records found in $advisorClassTableName<br />";
																}
															}
														}
													}
												}
												if ((count($studentArray)) > 0) {
/* if there are records in the studentArray
 *	sort the array
 *	foreach student in the array
 *		see if there is a registration in the student pod
 *		if so, set advisor to advisor_call_sign (pre-assign)
 *	Set fifo date to 2030/12/31 for the advisor
 *
*/
													sort($studentArray);
													if ($doDebug) {
														echo "Finished building array for  $advisor_call_sign<br /><pre>";
														print_r($studentArray);
														echo "</pre><br />";
													}
													$studentFound				= FALSE;
													foreach ($studentArray as $theStudent) {
														if ($doDebug) {
															echo "processing studentArray $theStudent<br />";
														}
														$sql					= "select * from $studentTableName 
																					where semester='$nextSemester' 
																					and call_sign='$theStudent' 
																					and response='Y'";
														$wpw1_cwa_student		= $wpdb->get_results($sql);
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
																	$student_abandoned  				= $studentRow->abandoned;
																	$student_student_status  				= strtoupper($studentRow->student_status);
																	$student_action_log  					= $studentRow->action_log;
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

																	$studentUpdateData						= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_blank'>$student_call_sign</a>";

																	$updateParams							= array();
																	$updateFormat							= array();
																	if ($doDebug) {
																		echo "Looking at $student_call_sign response: $student_response; status: $student_student_status; excluded advisor: $student_excluded_advisor<br />";
																	}
																	if ($student_excluded_advisor != $advisor_call_sign) {
																		if ($student_pre_assigned_advisor == '' || $student_pre_assigned_advisor == $advisor_call_sign) { 		// must not already be pre-assigned
																			// see if advisor has a class at the level the student is registered for.
																			// if so, pre-assign the student
									
																			$advisorArrayKey							= "$advisor_call_sign|$student_level";
																			if (array_key_exists($advisorArrayKey,$advisorArray)) {   /// have a match
																				$theSequence							= $advisorArray[$advisorArrayKey];												
																				$updateParams['pre_assigned_advisor']	= $advisor_call_sign;
																				$updateFormat[]							= '%s';
																				$updateParams['assigned_advisor_class']	= $theSequence;
																				$updateFormat[]							= '%d';
																				$content								.= "&nbsp;&nbsp&nbsp;Previous student $student_call_sign ($studentUpdateData) pre-assigned to $advisor_call_sign sequence $theSequence<br />";
																				$preAssignedCount++;
																				$studentFound							= TRUE;
																				if ($doDebug) {
																					echo "setting advisor to $advisor_call_sign for student $student_call_sign ($student_level) and assigned_advisor_class to $theSequence<br />";
																				}
																				$student_action_log 					.= "$student_action_log / $actionDate ADVPREP pre-assigned student to $advisor_call_sign ";
																				$updateParams['action_log']				= $student_action_log;
																				$updateFormat[]							= '%s';
																				if ($saveChanges) {
																					$studentUpdateData		= array('tableName'=>$studentTableName,
																													'inp_method'=>'update',
																													'inp_data'=>$updateParams,
																													'inp_format'=>$updateFormat,
																													'jobname'=>$jobname,
																													'inp_id'=>$student_ID,
																													'inp_callsign'=>$student_call_sign,
																													'inp_semester'=>$student_semester,
																													'inp_who'=>$userName,
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
																				echo "student already has a pre-assigned advisor of $student_pre_assigned_advisor<br />";
																			}
																		}
																	}
																}	// end of the while statement ... should only have one record
															} else {
																if ($doDebug) {
																	echo "$theStudent has no class registrations<br />";
																}
															}
														}									
													}
												}
						
												if ($studentFound) {
													// set the fifo date to 2030/12/31
													if ($doDebug) {
														echo "One or more students preassigned. Setting fifo date to 2030/12/31<br />";
													}
													$updateParams					= array();
													$updateFormat					= array();
													$updateParams['fifo_date']		= '2030-12-31 01:00:00';
													$updateFormat[]					= '%s';
													$sevenArray[]					= $advisor_call_sign;
													$updatedFifo++;
													if ($sevenSurveyScore) {
														$updateParams['survey_score']	= 7;
														$updateFormat[]					= '%d';
													}
													$advisor_action_log .= '$advisor_action_log / $actionDate ADVPREP Survey score is 7. Setting fifo date to 2030/12/31 ';
													$updateParams['action_log']	= $advisor_action_log;
													$updateFormat					= '%s';
													if ($saveChanges) {
														$advisorUpdateData		= array('tableName'=>$advisorTableName,
																						'inp_method'=>'update',
																						'inp_data'=>$updateParams,
																						'inp_format'=>$updateFormat,
																						'jobname'=>$jobname,
																						'inp_id'=>$advisor_ID,
																						'inp_callsign'=>$advisor_call_sign,
																						'inp_semester'=>$advisor_semester,
																						'inp_who'=>$userName,
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
																echo "Successfully updated $student_call_sign record at $student_ID<br />";
															}
														}
													}
												} else {
													if ($doDebug) {
														echo "No past students found<br />";
													}
												}
											}
										}
//									} else {
//										$content	.= "XX No $advisorTableName table records for $advisorUpdateLink<br />";
									} 
								}
							}
						}
					}			// End of all advisors
					if ($doDebug) {
						echo "End of all advisors while loop<br />";
					}
					$content		.= "<br />$preAssignedCount: Students updated with pre-assigned advisors<br />
										$updatedFifo: advisors with updated FIFO dates<br />
										$trashCount: Advisor records updated due to previous survey_score = 6<br />
										$emailsSent: Emails sent placing advisor registration on hold<br />";
				} else {
					$content	.= "No records found in $advisorTableName table<br />";
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
		$result			= write_joblog_func("Prepare Advisors for Student Assignments|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
		if ($result == 'FAIL') {
			$content	.= "<p>writing to joblog.txt failed</p>";
		}
		return $content;
}
add_shortcode ('prepare_advisors_for_student_assignments', 'prepare_advisors_for_student_assignments_func');
