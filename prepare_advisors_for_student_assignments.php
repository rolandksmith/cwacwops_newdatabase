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
*/

	global $wpdb, $doDebug, $testMode, $saveChanges, $jobname;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$saveChanges					= FALSE;
	$context = CWA_Context::getInstance();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$validUser 						= $context->validUser;
	$userName  						= $context->userName;
	$validTestmode					= $context->validTestmode;
	$siteURL						= $context->siteurl;

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
		$actionDate					= date('Y-m-d H:i:s');
		$sevenSurveyScore			= FALSE;
		$jobname					= 'Prepare Advisors for Student Assignments';
		$logDate					= date('Y-m-d H:i');
		$nextSemester				= $context->nextSemester;	
		$prevSemester				= $context->prevSemester;
		$userName					= $context->userName;
		$pastSemesterArray			= $context->pastSemestersArray;
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



	function checkChoice($studentLevel,$studentChoice,$advisorClasses,$doDebug) {
		if ($doDebug) {
			echo "<br /><b>checkChoice function</b><br />";
		}
		
		$bestOutcome = '';
		$bestQuality = 0;
		
		$myArray = explode(' ',$studentChoice);
		$studentTime = $myArray[0];
		$studentDays = $myArray[1];
		
		foreach($advisorClasses as $thisValue) {
			$outcomeQuality = 0;
			$myArray = explode('|',$thisValue);
			$advisorCallSign = $myArray[0];
			$advisorLevel = $myArray[1];
			$advisorDays = $myArray[2];
			$advisorTime = $myArray[3];
			$advisorSequence = $myArray[4];
			
			$outcome = '';
			$outcomeQuality = 0;
						
			if ($doDebug) {
				echo "testing studentLevel $studentLevel against advisorLevel $advisorLevel<br />";
			}	
			if ($studentLevel == $advisorLevel) {
				$outcome = "$advisorCallSign|$advisorSequence|Level";
				$outcomeQuality = 1;
				if ($doDebug) {
					echo "level match; outcome: $outcome<br />";
				}
				
				if ($doDebug) {
					echo "testing studentDays: $studentDays against advisorDays: $advisorDays<br />";
				}
				if ($studentDays == $advisorDays) {
					$outcome = "$advisorCallSign|$advisorSequence|Days";
					$outcomeQuality = 2;
					if ($doDebug) {
						echo "days match; outcome: $outcome<br />";
					}
					
					if ($doDebug) {
						echo "testing studentTime: $studentTime against advisorTime: $advisorTime<br />";
					}
					if ($studentTime == $advisorTime) {
						$outcome = "$advisorCallSign|$advisorSequence|Time";
						$outcomeQuality = 3;
						if ($doDebug) {
							echo "time match; outcome: $outcome<br />";
						}
					}
				}
			}
			if ($outcomeQuality > $bestQuality) {
				$bestQuality = $outcomeQuality;
				$bestOutcome = $outcome;
				if ($doDebug) {
					echo "better quality. Set bestOutcome to $bestOutcome<br />";
				}
			}
		}
		return array('bestQuality' => $bestQuality,'bestOutcome' => $bestOutcome);
	}

		
	$content = "";	

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
				$operatingMode				= 'Testmode';
			} else {
				$studentTableName			= 'wpw1_cwa_student';
				$advisorTableName			= 'wpw1_cwa_advisor';
				$advisorClassTableName		= 'wpw1_cwa_advisorclass';
				$userMasterTableName		= 'wpw1_cwa_user_master';
				$badActorTableName			= 'wpw1_cwa_bad_actor';
				$theStatement				= "";
 				$operatingMode				= 'Production';
			}

			$student_dal = new CWA_Student_DAL();
			$advisor_dal = new CWA_Advisor_DAL();
			$advisorclass_dal = new CWA_Advisorclass_DAL();
			$user_dal = new CWA_User_Master_DAL();

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
									<td><input type='radio' name='inp_save' class='formInputButton' value='No' > No<br />
										<input type='radio' name='inp_save' class='formInputButton' value='save' checked > Yes</td></tr>
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form></p>";

///// Pass 2 -- do the work


		} elseif ("2" == $strPass) {
			if ($doDebug) {
				echo "<br />at pass 2<br />";
			}
		
			$content				.= "<h3>$jobname</h3>";		

			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'advisor_semester', 'value' => $nextSemester, 'compare' => '=' ]
				]
			];
			$orderby = 'advisor_call_sign';
			$order = 'ASC';
			$advisorData = $advisor_dal->get_advisor_by_order( $criteria, $orderby, $order, $operatingMode );
			if ($advisorData === FALSE) {
				if ($doDebug) {
					echo "get_advisor_by_order returned FALSE<br />";
				}
			} else {
				if (! empty($advisorData)) {
					$myInt = count($advisorData);
					foreach($advisorData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						// get the user_master record
						$user_first_name = '';
						$user_last_name = '';
						$user_survey_score = 99;
						
						$userData = $user_dal->get_user_master_by_callsign( $advisor_call_sign, $operatingMode );
						if ($userData === FALSE) {
							if ($doDebug) {
								echo "get_user_master_by_callsign returned FALSE<br />";
							}
						} else {
							if ($doDebug) {
								echo "userData: <br /><pre>";
								print_r($userData);
								echo "</pre><br />";
							}
							if (! empty($userData)) {
								foreach($userData as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
									if ($doDebug) {
										echo "user_survey_score = $user_survey_score<br />";
									}
								}
							}
						}
						if ($user_survey_score == 99) {
							if ($doDebug) {
								echo "did not get a user record for $advisor_call_sign<br />";
							}
						}
						$advisorUpdateLink = "<a href='$advisorUpdateURL?request_type=callsign&request_info=$advisor_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$advisor_call_sign</a>";
						
						if ($doDebug) {
							echo "<br />Processing $advisor_call_sign. Survey score: $user_survey_score; Verify Response: $advisor_verify_response<br />";
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
									echo "$advisor_call_sign has an active bad actor record and will be put on hold<br />";
								}
								$content		.= "<p>Advisor $advisor_call_sign has an active Bad Actors record. Survey score has been set to 6</p>";
								$dumpAdvisor	= TRUE;
								$advisor_action_log	.= "$advisor_action_log / actionDate ADVPREP Past advisor record had survey_score of 6. Advisor is in bad actors table. Setting survey score to 6 ";
								$updateParams		= array('advisor_survey_score'=>6,
															'advisor-action_log'=>$advisor_action_log);
								if ($saveChanges) {
									$updateResult = $advisor_dal->update( $advisor_id, $updateParams, $operatingMode );
									if ($updateResult === FALSE || $updateResult === NULL) {
										if ($doDebug) {
											echo "update advisor returned FALSE|NULL<br />";
										}
									} else {
										if ($doDebug) {
											echo "Successfully updated $advisor_call_sign record at $advisor_ID<br />";
										}
									}
								}
							} else {
								$dumpAdvisor = FALSE;
							}
							if ($dumpAdvisor === FALSE) {
								if ($user_survey_score == 7) {
									if ($doDebug) {
										echo "advisor has a survey score of 7<br />";
									}
									// get an array of all past promotable students for this advisor
									$gotStudents = FALSE;
									$gotAdvisorClasses = FALSE;
									$studentArray = array();
									$criteria = [
										'relation' => 'AND',
										'clauses' => [
											// field1 = $value1
											['field' => 'student_assigned_advisor', 'value' => $advisor_call_sign, 'compare' => '='],
											['field' => 'student_promotable', 'value' => 'P', 'compare' => '='],
											
											// (field2 = $value2 OR field2 = $value3)
											[
												'relation' => 'OR',
												'clauses' => [
													['field' => 'student_status', 'value' => 'Y', 'compare' => '='],
													['field' => 'advisor_status', 'value' => 'S', 'compare' => '=']
												]
											]
										]
									];
									$orderby = 'student_semester,student_call_sign';
									$order = 'ASC';
									$studentData = $student_dal->get_student_by_order( $criteria, $orderby, $order, $operatingMode );
									if ($studentData === FALSE) {
										if ($doDebug) {
											echo "get_student returned FALSE<br />";
										}
									} else {
										if (! empty($studentData)) {
											foreach($studentData as $key => $value) {
												foreach($value as $thisField => $thisValue) {
													$$thisField = $thisValue;
												}
												if (! str_contains($student_excluded_advisor,$advisor_call_sign)) {
													if (!in_array($student_call_sign,$studentArray)) {
														$studentArray[]	= $student_call_sign;
														if ($doDebug) {
															echo "added $student_call_sign to studentArray<br />";
														}
													} else {
														if ($doDebug) {
															echo "$student_call_sign already in the array<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "advisor_call_sign is excluded from $student_call_sign<br />";
													}
												}
											}
										}
									}
									if (! empty($studentArray)) {
										$gotStudents = TRUE;
										// get an array of the advisorclasses for next semester
										$advisorClasses = array();
										$criteria = [
											'relation' => 'AND',
											'clauses' => [
												['field' => 'advisorclass_call_sign', 'value' => $advisor_call_sign, 'compare' => '=' ],
												['field' => 'advisorclass_semester', 'value' => $nextSemester, 'compare' => '=' ]
											]
										];
										$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
										if ($advisorclassData === FALSE) {
											if ($doDebug) {
												echo "get_advisorclasses returned FALSE<br />";
											}
										} else {
											if (! empty($advisorclassData)) {
												$gotAdvisorClasses = TRUE;
												foreach($advisorclassData as $key => $value) {
													foreach($value as $thisField => $thisValue) {
														$$thisField = $thisValue;
													}
													$advisorClasses[] = "$advisorclass_call_sign|$advisorclass_level|$advisorclass_class_schedule_days_utc|$advisorclass_class_schedule_times_utc|$advisorclass_sequence";
												}
											}
										}
									}
									if ($gotStudents && $gotAdvisorClasses) {
										if ($doDebug) {
											echo "<br />have students<br />
												  have advisorClasses:<br /><pre>";
											print_r($advisorClasses);
											echo "</pre><br />";
										}
									
									
/* if there are records in the studentArray
 *	sort the array
 *	foreach student in the array
 *		see if there is a registration in the student table
 *		if so, set advisor to advisor_call_sign (pre-assign)
 *
*/
										sort($studentArray);
//										if ($doDebug) {
//											echo "Finished building array for  $advisor_call_sign<br /><pre>";
//											print_r($studentArray);
//											echo "</pre><br />";
//										}
										$studentFound				= FALSE;
										foreach ($studentArray as $theStudent) {
											if ($doDebug) {
												echo "<br />processing studentArray $theStudent<br />";
											}
											// see if the student is registered in nextSemester
											$criteria = [
												'relation' => 'AND',
												'clauses' => [
													['field' => 'student_call_sign', 'value' => $theStudent, 'compare' => '=' ],
													['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '=' ],
													['field' => 'student_response', 'value' => 'R', 'compare' => '!=' ]
												]
											];
											$orderby = 'student_call_sign';
											$order = 'ASC';
											$studentData = $student_dal->get_student_by_order( $criteria, $orderby, $order, $operatingMode );
											if ($studentData === FALSE) {
												if ($doDebug) {
													echo "get_student returned FALSE<br />";
												}
											} else {
 												if (! empty($studentData)) {		// has a record
 													foreach($studentData as $key => $value) {
 														foreach($value as $thisField => $thisValue) {
 															$$thisField = $thisValue;
 														}
 														if($doDebug) {
 															echo "$student_call_sign Level: $student_level<br />
 																  $student_call_sign pre_assigned_advisor: $student_pre_assigned_advisor<br />
 															      $student_call_sign Fist Choice UTC: $student_first_class_choice_utc<br />
 															      $student_call_sign Second Choice UTC: $student_second_class_choice_utc<br />
 															      $student_call_sign Third Choice UTC: $student_third_class_choice_utc<br />";
 														}



														if ($student_pre_assigned_advisor == '') {
															$useQuality = 0;
															$useOutcome = '';
															
															
															// check first class choice
															if ($student_first_class_choice_utc != 'None' && $student_first_class_choice_utc != '') {
																$checkResult = checkChoice($student_level,$student_first_class_choice_utc,$advisorClasses,$doDebug);
																$thisQuality = $checkResult['bestQuality'];
																$thisOutcome = $checkResult['bestOutcome'];
																if ($thisQuality > $useQuality) {
																	$useQuality = $thisQuality;
																	$useOutcome = $thisOutcome;
																	if ($doDebug) {
																		echo "set useQuality to $useQuality; useOutcome to $useOutcome<br />";
																	}
																} else {
																	if ($doDebug) {
																		echo "no change to useOutcome<br />";
																	}
																}	
															}
	
															// check second class choice
															if ($student_second_class_choice_utc != 'None' && $student_second_class_choice_utc != '') {
																$checkResult = checkChoice($student_level,$student_second_class_choice_utc,$advisorClasses,$doDebug);
																$thisQuality = $checkResult['bestQuality'];
																$thisOutcome = $checkResult['bestOutcome'];
																if ($thisQuality > $useQuality) {
																	$useQuality = $thisQuality;
																	$useOutcome = $thisOutcome;
																	if ($doDebug) {
																		echo "set useQuality to $useQuality; useOutcome to $useOutcome<br />";
																	}
																} else {
																	if ($doDebug) {
																		echo "no change to useOutcome<br />";
																	}
																}	
															}
	
															// check third class choice
															if ($student_third_class_choice_utc != 'None' && $student_third_class_choice_utc != '') {
																$checkResult = checkChoice($student_level,$student_third_class_choice_utc,$advisorClasses,$doDebug);
																$thisQuality = $checkResult['bestQuality'];
																$thisOutcome = $checkResult['bestOutcome'];
																if ($thisQuality > $useQuality) {
																	$useQuality = $thisQuality;
																	$useOutcome = $thisOutcome;
																	if ($doDebug) {
																		echo "set useQuality to $useQuality; useOutcome to $useOutcome<br />";
																	}
																} else {
																	if ($doDebug) {
																		echo "no change to useOutcome<br />";
																	}
																}	
															}
															if ($doDebug) {
																echo "have best quality: $useQuality, best outcome: $useOutcome<br />";
															}
															if ($useQuality > 0) {		// have some kind of a match. Pre-assign
																$myArray = explode('|',$useOutcome);
																$thisAdvisorCallSign = $myArray[0];
																$thisAdvisorSequence = $myArray[1];
																$thisMatch = $myArray[2];
																
																$student_action_log .= " / ASSIGNPREP $actionDate $userName best pre-assign match: $thisMatch ";
																$updateParams = array('student_pre_assigned_advisor' => $thisAdvisorCallSign,
																					  'student_assigned_advisor_class' => $thisAdvisorSequence,
																					  'student_action_log' => $student_action_log);
																
																if ($inp_save == 'save') {					  
																	// update the student record
																	$updateResult = $student_dal->update( $student_id, $updateParams, $operatingMode );
																	if ($updateResult === FALSE) {
																		echo "updating id $student_id returned FALSE<br />";
																	} else {
																		$content .= "Pre-assigned $student_call_sign ($student_level) to $thisAdvisorCallSign class $thisAdvisorSequence<br />";
																		$preAssignedCount++;
																		if ($doDebug) {
																			echo "pre-assign saved<br />";
																		}
																	}
																} else {
																	$content .= "Save Changes is OFF: Student $student_call_sign not updated<br />
																				Would have set  pre_assigned advisor to $thisAdvisorCallSign and class to $thisAdvisorSequence<br /><br />";
																	if ($doDebug) {
																		echo "Save is off. Pre-assign not saved<br />";
																	}
																}
															}
														} else {
															$content .= "Student $student_call_sign already pre-assigned to $student_pre_assigned_advisor clss $student_assigned_advisor_class<br/><br />";
															if ($doDebug) {
																echo "student already pre-assiged to $student_pre_assigned_advisor<br />";
															}
														}
 													}
 												}
 											}
 										}	// end of studentArray array
 									}
 									if ($doDebug) {
 										echo "either gotStudents or gotAdvisorClasses is FALSE<br />";
 									}
 								} else {
 									if ($doDebug) {
 										echo "advisor survey score is not 7<br />";
 									}
 								}
 							}
 						}
 					}		// end of big loop
 				} else {
 					if ($doDebug) {
 						echo "no advisor data!<br />";
 					}
 					$content .= "<p>No Advisor Data Found!</p>";
 				}
 			}
			$content		.= "<br />$preAssignedCount: Students updated with pre-assigned advisors<br />";
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

