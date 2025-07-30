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
 	Modified 21Oct24 by Roland for new database
*/

	global $wpdb, $doDebug, $testMode, $saveChanges, $jobname;

	$doDebug						= TRUE;
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
		$advisorUpdateURL			= "$siteURL/cwa-display-and-update-advisor-signup-information/";
		$studentUpdateURL			= "$siteURL/cwa-display-and-update-student-signup-information/";
		
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
				$studentTableName			= 'wpw1_cwa_student2';
				$advisorTableName			= 'wpw1_cwa_advisor2';
				$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
				$userMasterTableName		= 'wpw1_cwa_user_master2';
				$badActorTableName			= 'wpw1_cwa_bad_actor';
				echo "Function is under development. Using test data, not the production data.<br />";
				$theStatement				= "<p>Running in TESTMODE using Test Data.</p>";
			} else {
				$studentTableName			= 'wpw1_cwa_student';
				$advisorTableName			= 'wpw1_cwa_advisor';
				$advisorClassTableName		= 'wpw1_cwa_advisorclass';
				$userMasterTableName		= 'wpw1_cwa_user_master';
				$badActorTableName			= 'wpw1_cwa_bad_actor';
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
		
			$content				.= "<h3>$jobname</h3>";		

			$sql					= "select * from $advisorTableName 
										left join $userMasterTableName on user_call_sign = advisor_call_sign 
										where advisor_semester='$nextSemester' 
										order by advisor_call_sign";
			$wpw1_cwa_advisor	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				handelWPDBError($jobname,$doDebug);
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
						$advisor_ph_code 					= $advisorRow->user_ph_code;
						$advisor_phone 						= $advisorRow->user_phone;
						$advisor_city 						= $advisorRow->user_city;
						$advisor_state 						= $advisorRow->user_state;
						$advisor_zip_code 					= $advisorRow->user_zip_code;
						$advisor_country_code 				= $advisorRow->user_country_code;
						$advisor_country_code 			= $advisorRow->user_country;
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
	

						$advisorUpdateLink					= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$advisor_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$advisor_call_sign</a>";
						
						if ($doDebug) {
							echo "<br />Processing $advisor_call_sign. Survey score: $advisor_survey_score; Verify Response: $advisor_verify_response<br />";
						}
						if ($advisor_verify_response == 'R') {
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;&nbsp;advisor has a verify_respone of $advisor_verify_response. Bypassing. <br />";
							}
						} else {
							// see if advisor is in the bad actors table. If so, put on hold
							$putOnHold		= FALSE;
							$baSQL			= "select * from $badActorTableName 
												where call_sign = '$advisor_call_sign' 
												and status = 'A'";
							$baResult		= $wpdb->get_results($baSQL);
							if ($baResult === FALSE) {
								handleWPDBError($jobname,$doDebug,"attempting to check bad actor table");
								$putOnHold	= TRUE;
							} else {
								$numBARows	= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $baSQL<br />and retrieved $numBARows rows<br />";
								}
								if ($numBARows > 0) {
									$putOnHold	= TRUE;
								}
							}
							if ($putOnHold) {
								if ($doDebug) {
									echo "$advisorCallSign has an active bad actor record and will be put on hold<br />";
								}
								$content		.= "<p>Advisor $advisor_call_sign has an active Bad Actors record. Survey score has been set to 6</p>";
								$dumpAdvisor	= TRUE;
								$advisor_action_log	.= "$advisor_action_log / actionDate ADVPREP Past advisor record had survey_score of 6. Advisor is in bad actors table. Setting survey score to 6 ";
								$updateParams		= array('advisor_survey_score'=>6,
															'advisor-action_log'=>$advisor_action_log);
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
									}
								}
							} else {
								// general test for all advisors except those with a survey score of 6				
								if ($advisor_survey_score != 6 && $advisor_survey_score != 9 && $advisor_survey_score != 13) {
									// Look for this advisor in past semesters and see if survey score is 6 (bad actor)
									$dumpAdvisor				= FALSE;
									$sevenSurveyScore			= FALSE;
									$sql						= "select * from $advisorTableName 
																	where advisor_call_sign='$advisor_call_sign' 
																	and advisor_semester != '$nextSemester' 
																	order by advisor_date_created ";
									$wpw1_cwa_advisor	= $wpdb->get_results($sql);
									if ($wpw1_cwa_advisor === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {	
										$numPARows				= $wpdb->num_rows;
										if ($doDebug) {
											echo "ran $sql<br />and found $numPARows rows in $advisorTableName<br />";
										}
										if ($numPARows > 0) {
											$theSurveyScore								= 0;
											foreach ($wpw1_cwa_advisor as $advisorRow) {
												$advisor_ID						= $advisorRow->advisor_id;
												$advisor_call_sign 				= strtoupper($advisorRow->advisor_call_sign);
												$advisor_semester 					= $advisorRow->advisor_semester;
												$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
												$advisor_verify_email_date 		= $advisorRow->advisor_verify_email_date;
												$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
												$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
												$advisor_action_log 				= $advisorRow->advisor_action_log;
												$advisor_class_verified 			= $advisorRow->advisor_class_verified;
												$advisor_control_code 				= $advisorRow->advisor_control_code;
												$advisor_date_created 				= $advisorRow->advisor_date_created;
												$advisor_date_updated 				= $advisorRow->advisor_date_updated;
												$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;
										
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
												$updateParams		= array('advisor_survey_score'=>6,
																			'advisor-action_log'=>$advisor_action_log);
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
													$updateParams['advisor_survey_score']		= 7;
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
																			   where advisorclass_semester='$nextSemester' 
																					and advisorclass_call_sign='$advisor_call_sign'";
													$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
													if ($wpw1_cwa_advisorclass === FALSE) {
														handleWPDBError($jobname,$doDebug);
													} else {
														$numACRows						= $wpdb->num_rows;
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
	
									
																$advisorArrayKey							= "$advisorClass_call_sign|$advisorClass_level";
																$advisorArray[$advisorArrayKey]				= $advisorClass_sequence;
																if ($doDebug) {
																	echo "added $advisorArrayKey to advisorArray with value of $advisorClass_sequence<br />";
																}
	
															}
															$content					.= "<br />Processing $advisorUpdateLink who has a survey score of 7<br />";
	
															// read the class table for all previous advisor classes and build an array of students who
															// have taken a class from this advisor
															$studentArray				= array();
															$sql					 	= "select * from $advisorClassTableName 
																						   where advisorclass_call_sign='$advisor_call_sign' 
																						   and advisorclass_semester != '$nextSemester' ";
															$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
															if ($wpw1_cwa_advisorclass === FALSE) {
																handleWPDBError($jobname,$doDebug);
															} else {
																$numACRows				= $wpdb->num_rows;
																if ($doDebug) {
																	echo "ran $sql<br />and found $numACRows rows in $advisorClassTableName table<br />";
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
																			echo "<br />Processing $advisorClass_call_sign Semester: $advisorClass_semester sequence: $advisorClass_sequence<br />";
																		}
																		for ($snum=1;$snum<=$advisorClass_number_students;$snum++) {
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
 *		see if there is a registration in the student table
 *		if so, set advisor to advisor_call_sign (pre-assign)
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
																						where student_semester='$nextSemester' 
																						and student_call_sign like '$theStudent' 
																						and student_response='Y'";
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
	
	
																		$studentUpdateData						= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>";
	
																		$updateParams							= array();
																		$updateFormat							= array();
//																		if ($doDebug) {
//																			echo "Looking at $student_call_sign response: $student_response; status: $student_status; excluded advisor: $student_excluded_advisor<br />";
//																		}
																		if (!str_contains($student_excluded_advisor,$advisor_call_sign)) {
																			if ($student_pre_assigned_advisor == '' || $student_pre_assigned_advisor == $advisor_call_sign) { 		// must not already be pre-assigned
																				// see if advisor has a class at the level the student is registered for.
																				// if so, pre-assign the student
										
																				$advisorArrayKey							= "$advisorClass_call_sign|$student_level";
																				if ($doDebug) {
																					echo "looking for $advisorArrayKey in advisorArray<br />";
																				}
																				if (array_key_exists($advisorArrayKey,$advisorArray)) {   /// have a match
																					$theSequence							= $advisorArray[$advisorArrayKey];												
																					if ($doDebug) {
																						echo "have a match. Preassigning student to $advisorClass_call_sign class $theSequence<br />";
																					}
																					$updateParams['student_pre_assigned_advisor']	= $advisor_call_sign;
																					$updateFormat[]							= '%s';
																					$updateParams['student_assigned_advisor_class']	= $theSequence;
																					$updateFormat[]							= '%d';
																					$content								.= "&nbsp;&nbsp&nbsp;Previous student $student_call_sign ($studentUpdateData) pre-assigned to $advisor_call_sign sequence $theSequence<br />";
																					$preAssignedCount++;
																					$studentFound							= TRUE;
																					if ($doDebug) {
																						echo "setting pre_assigned_advisor to $advisor_call_sign for student $student_call_sign ($student_level) and assigned_advisor_class to $theSequence<br />";
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
																				} else {
																					if ($doDebug) {
																						echo "no match found<br />";
																					}
																				}
																			} else {
																				if ($doDebug) {
																					echo "student already has a pre-assigned advisor of $student_pre_assigned_advisor<br />";
																				}
																			}
																		} else {
																			if ($doDebug) {
																				echo "$advisorClass_call_sign is excluded<br />";
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
													} else {
														if ($doDebug) {
															echo "No past students found<br />";
														}
													}
												}
											}
										} 
									}
								}
							}
						}
					}			// End of all advisors
					if ($doDebug) {
						echo "End of all advisors while loop<br />";
					}
					$content		.= "<br />$preAssignedCount: Students updated with pre-assigned advisors<br />
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
