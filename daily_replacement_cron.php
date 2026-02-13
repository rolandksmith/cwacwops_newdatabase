function daily_replacement_cron_func() {

global $wpdb, $testMode, $classesArray, $student_dal, $advisor_dal, $advisorclass_dal, $user_dal, $debugReport, 
	$proximateSemester, $studentUpdateURL, $siteURL, $jobname, $userName, $content, $operatingMode, 
	$replaceArrayBeginner, $replaceArrayIntermediate, $replaceArrayAdvanced, $replaceArrayFundamental, 
	$replReqTableName, $doDebug;

	$doDebug				= TRUE;
	$testMode				= FALSE;
	$versionNumber			= '1`';
	
//	$inp_mode				= '';
	ini_set('max_execution_time',0);
	set_time_limit(0);

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	$studentEmailCount			= 90;
	
	$initializationArray 	= data_initialization_func();
	$userName				= $initializationArray['userName'];
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');
	
	$debugReport = "";
	



// Needed variables initialization
	$processStudent			= TRUE;
	$replacedCount			= 0;
	$notReplacedCount		= 0;
	$jobname				= "Daily Replacement Cron";
	$currentTimestamp 		= $initializationArray['currentTimestamp'];
	$todaysDate 			= $initializationArray['currentDate'];
	$checkDate 				= date('Y-m-d',$currentTimestamp);
	$unixCheckDate			= strtotime($checkDate);
	$currentSemester		= $initializationArray['currentSemester'];
	$nextSemester 			= $initializationArray['nextSemester'];
	$semesterTwo 			= $initializationArray['semesterTwo'];
	$semesterThree 			= $initializationArray['semesterThree'];
	$semesterFour 			= $initializationArray['semesterFour'];
	$proximateSemester		= $initializationArray['proximateSemester'];
	$prevSemester			= $initializationArray['prevSemester'];
	$validEmailPeriod 		= $initializationArray['validEmailPeriod'];
	$daysToSemester			= $initializationArray['daysToSemester'];
	$validReplacementPeriod	= $initializationArray['validReplacementPeriod'];
	$actionDate				= date('dMy H:i',$currentTimestamp);
	$logDate				= date('Y-m-d H:i:s',$currentTimestamp);
	$validTestmode			= $initializationArray['validTestmode'];
	$siteURL				= $initializationArray['siteurl'];
	$advisorVerifyURL		= "$siteURL/cwa-manage-advisor-class-assignments/";	
	$studentRegistrationURL	= "$siteURL/cwa-student-registration/";
	$checkClassURL			= "$siteURL/cwa-check-student-status/";
	$classResolutionURL		= "https://cwops.org/cwa-class-resolution/";
	$advisorVerifyURL		= "$siteURL/cwa-manage-advisor-class-assignments/";
	$studentUpdateURL		= "$siteURL/cwa-display-and-update-student-signup-information/";
	$replaceArrayBeginner	= array();
	$replaceArrayIntermediate= array();
	$replaceArrayAdvanced	= array();
	$replaceArrayFundamental = array();
	$outstandingFulfilled	= 0;
	$actionDate = date('Y-m-d H:i:s');

	
	if ($testMode) {
		$operatingMode = 'Testmode';
		$replReqTableName = 'wpw1_cwa_replacement_requests2';
	} else {
		$operatingMode = 'Production';
		$replReqTableName = 'wpw1_cwa_replacement_requests';	
	}	

	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	$user_dal = new CWA_User_Master_DAL();


	$content = "";

///////////////// Debug log function

	function debugLog($message) {
		global $debugReport, $doDebug;
		$debugReport .= date('Y-m-d H:i:s') . " - " . $message . "<br />\n";
		if ($doDebug) {
			echo date('Y-m-d H:i:s') . " - " . $message . "<br />\n";
		}	
		return TRUE;
	}	


//////////////// findAReplacement function

	function findAReplacement($replacementInfo) {

	global $wpdb, $testMode, $classesArray, $student_dal, $advisor_dal, $advisorclass_dal, $user_dal, $debugReport, 
		$proximateSemester, $studentUpdateURL, $siteURL, $jobname, $userName, $content, $operatingMode, 
		$replaceArrayBeginner, $replaceArrayIntermediate, $replaceArrayAdvanced, $replaceArrayFundamental, 
		$replReqTableName, $doDebug;
	
	/**	findAReplacement
	 *	@param array $replacementInfo associative array with keys:
	 *		'advisorCallSign'	- call sign of advisor needing replacement
	 *		'advisorClass'		- class sequence number of advisor needing replacement
	 *		'replacement_level'	- level of replacement needed
	 *		'replacement_student'- student needing replacement
	 *	@return boolean|string   FALSE if no replacement found else 
	 *							returns replacement student information
									sequence			[0];
									studentCallSign 	[1];
									firstTime			[2];
									firstDays			[3];
									secondTime			[4];
									secondDays			[5];
									thirdTime			[6];
									thirdDays			[7];
									excludedAdvisor		[8];
									Language			[9];
									studentid			[10];

	 
	 */
	global $wpdb, $testMode, $classesArray, $student_dal, $advisor_dal, $advisorclass_dal, $user_dal, $debugReport, 
		$proximateSemester, $studentUpdateURL, $siteURL, $jobname, $userName, $content;

		foreach($replacementInfo as $key => $value) {
			$$key = $value;
		}

		if (! isset($advisorCallSign)) {
			debugLog("findAReplacement: advisorCallSign not set");
			return FALSE;
		}
		if (!isset($advisorClass)) {
			debugLog("findAReplacement: advisorClass not set");
			return FALSE;
		}
		if (! isset($replacement_level)) {
			debugLog("findAReplacement: replacement_level not set");
			return FALSE;
		}
		if (! isset($replacement_student)) {
			debugLog("findAReplacement: replacement_student not set");
			return FALSE;
		}

		// get the advisor class schedule and language
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				['field' => 'advisorclass_call_sign', 'value' => $advisorCallSign, 'compare' => '=' ],
				['field' => 'advisorclass_sequence', 'value' => $advisorClass, 'compare' => '=' ],
				['field' => 'advisorclass_semester', 'value' => $proximateSemester, 'compare' => '=' ]
			]
		];
		$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
		if ($advisorclassData === FALSE) {
			debugLog("get_advisorclasses for $replacement_call_sign returned FALSE");
		} else {
			if (! empty($advisorclassData)) {
				foreach($advisorclassData as $key => $value) {
					foreach($value as $thisField => $thisValue)	{
						$$thisField = $thisValue;
					}

					// now look for a potential replacement student
					$searchTime = intval($advisorclass_class_schedule_times_utc);
					$searchDays = $advisorclass_class_schedule_days_utc;
					debugLog("&nbsp;&nbsp;&nbsp;&nbsp;Looking for a replacement level $replacement_level $searchTime $searchDays");
					$callSign1	= '';
					$sequence1	= '';
					$callSign2	= '';
					$sequence2	= '';
					$callSign3	= '';
					$sequence3	= '';

					foreach(${'replaceArray' . $replacement_level} as $myKey=>$myValue) {
						debugLog("<br />Checking possible replacement: $myValue");
						$myArray = explode("|",$myValue);
						$thisSequence		= $myArray[0];
						$thisCallSign 		= $myArray[1];
						$firstTime 			= $myArray[2];
						$firstDays 			= $myArray[3];
						$secondTime 		= $myArray[4];
						$secondDays 		= $myArray[5];
						$thirdTime 			= $myArray[6];
						$thirdDays 			= $myArray[7];
						$excludedAdvisor	= $myArray[8];
						$thisLanguage		= $myArray[9];
						$thisStudentid		= $myArray[10];
						
						debugLog("checking possible replacement $thisCallSign");

						if (! str_contains($excludedAdvisor,$advisorclass_call_sign)) {
							if ($thisLanguage == $advisorclass_language) {
								if ($searchDays == $firstDays) {
									debugLog("first choice match on $searchDays. Checking poss replacement first choice $firstTime");
									if ($callSign1 == '') {		// don't continue if have a match
										$searchBegin = intval($firstTime) - 300;
										$searchEnd = intval($firstTime) + 300;
										if ($searchBegin < 0) {
											$searchBegin	= 0;
										}
										if ($searchEnd > 2400) {
											$searchEnd		= 2400;
										}
										debugLog("testing firstTime: $firstTime. Looking for student between $searchBegin and $searchEnd");
										if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
											$callSign1 = $thisCallSign;
											$sequence1 = $myKey;
											debugLog("1: Found $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd<br />
																	Set callSign1 to $thisCallSign and sequence1 to $myKey");
										} else {
											debugLog("1: No go $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd");
										}
									}
								} else {
									debugLog("Searching first choice days did not match");

								}
								if ($searchDays == $secondDays) {
									debugLog("second choice match on searchDays. Doing second choice $secondTime");
									if ($callSign2 == '') {
										$searchBegin 	= intval($secondTime) - 300;
										$searchEnd 		= intval($secondTime) + 300;
										if ($searchBegin < 0) {
											$searchBegin	= 0;
										}
										if ($searchEnd > 2400) {
											$searchEnd		= 2400;
										}
										debugLog("testing secondTime: $secondTime. Looking for student between $searchBegin and $searchEnd");
										if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
											$callSign2 = $thisCallSign;
											$sequence2 = $myKey;
											debugLog("2: Found $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd<br />
												Set callSign2 to $thisCallSign and sequence2 to $myKey");
										} else {
											debugLog("2: No go $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd");
										}
									} else {
										debugLog("searching second choice days did not match");
									}
								} else {
									debugLog("searching second choice days didn't match");
								}
								if ($searchDays == $thirdDays) {
									debugLog("third choice match on searchDays. Doing third choice $thirdTime");
									if ($callSign3 == '') {
										$searchBegin 	= intval($thirdTime) - 300;
										$searchEnd 		= intval($thirdTime) + 300;
										$searchEnd = $searchBegin + 300;
										if ($searchBegin < 0) {
											$searchBegin	= 0;
										}
										if ($searchEnd > 2400) {
											$searchEnd		= 2400;
										}
										debugLog("testing thirdTime: $thirdTime. Looking for student between $searchBegin and $searchEnd");
										if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
											$callSign3 = $thisCallSign;
											$sequence3 = $myKey;
											debugLog("3: Found $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd<br />
																	Set callSign3 to $thisCallSign and sequence3 to $myKey");
										} else {
											debugLog("3: No go $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd");
										}
									}	
								} else {
									debugLog("searching third choice days did not match");
								}
							} else {
								debugLog("$thisCallSign&apos;s language of $thisLanguage does not match advisorclass $advisorclass_language language");
							}
						} else {
							debugLog("possible advisor is excluded: $excludedAdvisor");
						} 
					}
				}
				debugLog("CallSign1: $callSign1 | $sequence1<br />
							CallSign2: $callSign2 | $sequence2<br />
							CallSign3: $callSign3 | $sequence3");
				$gotAReplacement			= FALSE;
				if ($callSign1 != '') {
					$replacingCallSign		= $callSign1;
					$replacingSequence		= $sequence1;
					$gotAReplacement		= TRUE;
					debugLog("$replacingCallSign (1) works as a replacement<br />");
				} elseif ($callSign2 != '' && !$gotAReplacement) {
					$replacingCallSign		= $callSign2;
					$replacingSequence		= $sequence2;
					$gotAReplacement		= TRUE;
					debugLog("$replacingCallSign (2) works as a replacement");
				} elseif ($callSign3 != '' && !$gotAReplacement) {
					$replacingCallSign		= $callSign3;
					$replacingSequence		= $sequence3;
					$gotAReplacement		= TRUE;
					debugLog("$replacingCallSign (3) works as a replacement");
				}

				if ($gotAReplacement) {
					debugLog("Have a replacement $replacingCallSign. Getting student record");
					///// get the replacement student record
					$replacementRecord = ${'replaceArray' . $replacement_level}[$replacingSequence];
					debuglog("replacement record: $replacementRecord");	
					///// unset the replacement student in replaceArray
					$replacingSequence = $replacementRecord[0];
					if (isset(${'replaceArray' . $replacement_level}[$replacingSequence])) {
						unset(${'replaceArray' . $replacement_level}[$replacingSequence]);
						debugLog("did unset replaceArray $replacement_level $replacingSequence");
					} else {
						debugLog("<b>ERROR</b> replaceArray $replacement_level $replacingSequence not available to unset");
					}

					return $replacementRecord;
				} else {
					debugLog("No replacement found for $replacement_student");
					return FALSE;
				}
			} else {
				debugLog("No advisor class found for $replacement_call_sign class $replacement_class semester $theSemester");
				return FALSE;
			}
		}
	}		//// end of findAReplacement function



	/////  doTheReplacement function
						
function doTheReplacement($studentCallSign, $studentid, $replacedStudent, $advisorCallSign, $advisorClass) {

	/**	doTheReplacement
	 *	@param string $studentCallSign		call sign of student to be assigned
	 *	@param int	 $studentid				id of student record to be assigned
	 * @param string $replacedStudent		call sign of student being replaced
	 *	@param string $advisorCallSign		call sign of advisor needing replacement
	 *	@param int	 $advisorClass			class sequence number needing replacement
	 *	@return boolean						TRUE if successful else FALSE

	*/


	global $wpdb, $testMode, $classesArray, $student_dal, $advisor_dal, $advisorclass_dal, $user_dal, $debugReport, 
		$proximateSemester, $studentUpdateURL, $siteURL, $jobname, $userName, $content, $operatingMode, 
		$replaceArrayBeginner, $replaceArrayIntermediate, $replaceArrayAdvanced, $replaceArrayFundamental, 
		$replReqTableName, $doDebug;

	$advisorVerifyURL		= "$siteURL/cwa-manage-advisor-class-assignments/";
	$actionDate = date('Y-m-d H:i:s');

	debugLog("Doing the replacement of $replacedStudent with $studentCallSign for $advisorCallSign class $advisorClass");	
	$studentReplaced		= FALSE;
	///// get the replacement student record
	$replacementStudentData = $student_dal->get_student_by_id( $studentid, $operatingMode ); 
	debugLog("got replacement student data for ID $studentid");
	if ($replacementStudentData === FALSE) {
		debugLog("get_student_by_id for ID $replacementRecordID returned FALSE");
	} else {
		if (! empty($replacementStudentData)) {
			foreach($replacementStudentData as $key => $value) {
				$$key = $value;
			}
			if (! isset($student_call_sign)) {
				debugLog("student_call_sign not set in replacement student data");
			}	
			// now get the advisor and the advisorclass records
			$advisorData = get_advisor_and_user_master($advisorCallSign, 'callsign', $proximateSemester, $operatingMode, $doDebug);
			debugLog("returned from get advisor and user master data for $advisorCallSign");
			if ($advisorData === FALSE) {
				debugLog("get_advisor_and_user_master for $advisorCallSign returned FALSE");
			} else {
				if (! empty($advisorData)) {
					foreach($advisorData as $key => $value) {
						$$key = $value;
					}
					if (! isset($user_call_sign)) {
						debugLog("user_call_sign not set in advisor data for $advisorCallSign");
					}
					// get the advisorclass record
					$criteria = [
						'relation' => 'AND',
						'clauses' => [
							['field' => 'advisorclass_call_sign', 'value' => $advisorCallSign, 'compare' => '=' ],
							['field' => 'advisorclass_sequence', 'value' => $advisorClass, 'compare' => '=' ],
							['field' => 'advisorclass_semester', 'value' => $proximateSemester, 'compare' => '=' ]
						]
					];
					$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
					debugLog("returned from advisorclass data for $advisorCallSign class $advisorClass");
					if ($advisorclassData === FALSE) {
						debugLog("get_advisorclasses for $advisorCallSign returned FALSE");
					} else {
						if (! empty($advisorclassData)) {
							foreach($advisorclassData as $key => $value) {
								foreach($value as $thisField => $thisValue)	{	
									$$thisField = $thisValue;
								}
								if (! isset($advisorclass_call_sign)) {
									debugLog("advisorclass_call_sign not set in advisorclass data for $advisorCallSign class $advisorClass");
								}
								if ($student_assigned_advisor == '') {
									//// add student to the advisor's class
									$inp_data			= array('inp_student'=>$student_call_sign, 
																'inp_semester'=>$proximateSemester, 
																'inp_assigned_advisor'=>$advisorclass_call_sign, 
																'inp_assigned_advisor_class'=>$advisorclass_sequence, 
																'inp_remove_status'=>'',
																'inp_arbitrarily_assigned'=>'',
																'inp_method'=>'add',
																'jobname'=>$jobname,
																'userName'=>$userName,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug);

									$addResult			= add_remove_student($inp_data);
									if ($addResult[0] === FALSE) {
										$thisReason		= $addResult[1];
										debugLog("attempting to add $student_call_sign to $advisorCallSign class failed:<br />$thisReason");
										sendErrorEmail("$jobname Attempting to add $student_call_sign to $advisorclass_call_sign class $advisorclass_sequence failed:<br />$thisReason");
										$content		.= "Attempting to add $student_call_sign to $$advisorclass_call_sign class $advisorclass_sequence failed:<br />$thisReason<br />";
									} else {
										$content	.= "<br />REPLACE Student <a href='$studentUpdateURL?request_type=callsign&request_info=$replacedStudent&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
														target='_blank'>$replacedStudent</a> was replaced by <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
														target='_blank'>$student_call_sign</a> who was assigned to $advisorclass_call_sign $advisorclass_sequence class.<br />";
										$studentReplaced	= TRUE;
										debugLog("&nbsp;&nbsp;&nbsp; student $replacedStudent was replaced by $student_call_sign");
										//// format replacement email to advisor
																
										///// send the email to the advisor
										$theSubject			= "CW Academy Replacement Student Information for your Class";
										if ($testMode) {
											$theRecipient			= 'rolandksmith@gmail.com';
											$theSubject				= "TESTMODE $theSubject";
											$mailCode				= 2;
										} else {
											$theRecipient			= $user_email;
											$mailCode				= 14;
										}
											
										$thisContent			= "<p>To: $user_last_name, $user_first_name ($advisorclass_call_sign):</p>
																	<p>You have requested a replacement student for 
																	$replacedStudent in your $advisorclass_level class number $advisorclass_sequence.</p>
																	<p>A replacement student has been added to your class. Please login to 
																	<a href='$siteURL/login'>CW Academy</a> and follow the directions there.
																	<p>If you have questions or concerns, do not reply to this email as the address is not monitored. 
																	Instead reach out to <a href='classResolutionURL' target='_blank'>CWA Class Resolution</a> and select the appropriate person.</p> 
																	<p>Thanks for your service as an advisor!<br />
																	CW Academy</p>";
										$mailResult				= emailFromCWA_v3(array('theRecipient'=>$theRecipient,
																						'theSubject'=>$theSubject,
																						'theContent'=>$thisContent,
																						'jobname'=>$jobname,
																						'mailCode'=>$mailCode,
																						'testMode'=>$testMode,
																						'doDebug'=>FALSE));
										// $mailResult = TRUE;
										if ($mailResult[0] === TRUE) {
											$content .= "&nbsp;&nbsp;&nbsp;&nbsp;An email was sent to <a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_callsign=$advisorclass_call_sign&strpass=2' target='_blank'>$theRecipient</a>.<br />";
											debugLog("$mailResult[1]");
											debugLog("Replacement email sent to the advisor at $theRecipient");
										} else {
											$content .= "&nbsp;&nbsp;&nbsp;&nbsp;The replacement email send function to advisor $advisorclass_call_sign email: $theRecipient failed.</p><br />";
											debugLog("$mailResult[1]");
											deblugLog("Replacement email FAILED to advisor at $theRecipient");
										}
										// add the reminder to the reminders table
										debugLog("Adding reminder to reminders table");
										$myStr				= date('Y-m-d 00:00:00');
										$closeStr			= strtotime("+10 days");
										$close_date			= date('Y-m-d H:i:s',$closeStr);
										$token				= mt_rand();
										$reminder_text		= "<b>Replacement Student:</b> Your class makeup has been revised and students need to be contacted 
and confirmed. Click on <a href='$advisorVerifyURL/?&token=$token' target='_blank'>Manage Advisor Class Assignments</a> to complete that task.";
										$inputParams		= array("effective_date|$myStr|s",
																	"close_date|$close_date|s",
																	"resolved_date||s",
																	"send_reminder|N|s",
																	"send_once|Y|s",
																	"call_sign|$advisorclass_call_sign|s",
																	"role||s",
																	"email_text||s",
																	"reminder_text|$reminder_text|s",
																	"resolved|N|s",
																	"token|$token|s");
										$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
										if ($insertResult[0] === FALSE) {
											debugLog("inserting reminder failed: $insertResult[1]");
											$content		.= "Inserting reminder failed: $insertResult[1]<br />";
										} else {
											debugLog("reminder inserted successfully for $advisorclass_call_sign");
											$content .= "&nbsp;&nbsp;&nbsp;&nbsp;A reminder was set up for 
														<a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_callsign=$advisorclass_call_sign&strpass=2' 
														target='_blank'>$advisorclass_call_sign</a> to confirm the class assignments.<br />";
										}
										// get the replaced student's data
										debugLog("getting replaced student ($replacedStudent) data");
										$criteria = [
											'relation' => 'AND',
											'clauses' => [
												['field' => 'student_call_sign', 'value' => $replacedStudent, 'compare' => '=' ],
												['field' => 'student_semester', 'value' => $proximateSemester, 'compare' => '=' ]
											]
										];
										$orderby = 'student_call_sign';
										$orderby = 'ASC';
										$order = 'ASC';
										$replacedStudentData = $student_dal->get_student_by_order( $criteria, $orderby, $order, $operatingMode ); 
										if ($replacedStudentData === FALSE) {
											debugLog("get_studentfor $replacedStudent returned FALSE");
										} else {
											if (! empty($replacedStudentData)) {
												foreach($replacedStudentData as $key => $value) {
													foreach($value as $thisField => $thisValue) {
														$$thisField = $thisValue;
													}
													if (! isset($student_call_sign)) {
														debugLog("student_call_sign not set in replaced student data for $replacedStudent");
													}
													// now remove student from the class and set the replaced student's status to 'C'
													$inp_data			= array('inp_student'=>$student_call_sign,
																		'inp_semester'=>$student_semester,
																		'inp_assigned_advisor'=>$student_assigned_advisor,
																		'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
																		'inp_remove_status'=>'C',
																		'inp_arbitrarily_assigned'=>$student_no_catalog,
																		'inp_method'=>'remove',
																		'jobname'=>$jobname,
																		'userName'=>$userName,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
															
													$removeResult		= add_remove_student($inp_data);
													if ($removeResult[0] === FALSE) {
														$thisReason		= $removeResult[1];
														debugReport("attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
														sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
														$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
													} else {
														debugLog("replaced student $replacedStudent status set to C successfully");
														$content		.= "&nbsp;&nbsp;&nbsp;&nbsp;Replaced student <a href='$studentUpdateURL?request_type=callsign&request_info=$replacedStudent&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																			target='_blank'>$replacedStudent</a> status set to C successfully.<br />";
													}
												}
											} else {
												debugLog("getting replaced student ($replaced_student) data returned no data");		
												$content .= "No data found for replaced student $replacedStudent<br />";
											}
										}
									}
								} else {
									debugLog("student already assigned to $student_assigned_advisor<br />");
									sendErrorEmail("$jobname doing replacements student $student_call_sign was in the available replacement students but had $student_assigned_advisor assigned");
								}
							}
						} else {
							debugLog("getting the advisorclass data for $advisor_call_sign returned no data");
							$content .= "Unable to retrieve the advisorclass data";
						}
					}
				} else {
					debugLog("getting the advisor record for $advisorCallSign returned no data");
				}
			}
		} else {
			debugLog("getting student data for ID $studentid returned no data");
			$content .= "No data found for student ID $studentid<br />";
		}
	}
	if ($studentReplaced) {
		return TRUE;
	} else {
		return FALSE;
	}
}		//// end of doTheReplacement function

/////////////	




////////////////////// Main processing logic starts here

	$runTheJob				= TRUE;
////// see if this is the time to actually run
	debugLog("<br />starting");
		
	if ($userName != '') {
		$content 			.= "<h3>$jobname Executed by $userName</h3>";
	} else {
		$content			.= "<h3>$jobname Process Automatically Executed</h3>";
		$userName			= "CRON";
		$runTheJob			= allow_job_to_run($doDebug);
	}

	
// $runTheJob = TRUE;	
	if ($runTheJob) {


		if ($validReplacementPeriod == 'Y') {
			debugLog("In the replacement period");
			if ($operatingMode == 'Testmode') {
				debugLog("<b>TESTMODE:</b> Building the replacement array of potential replacement students");
			}
			if ($operatingMode == 'Production') {	
				debugLog("<b>PRODUCTION MODE:</b> Building the replacement array of potential replacement students");
			}	
			debugLog("<b>REPLACE Array</b> In the replacement period<br />Building the array of 
							potential replacement students");
			////// build the array of potential replacement students
			////// replaceArray[level][priority-reqdate] = student call sign|first choice UTC|second choice UTC|thirdChoice UTC|excluded advisor

			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $proximateSemester, 'compare' => '=' ],
					['field' => 'student_response', 'value' => 'Y', 'compare' => '=' ],
					['field' => 'student_status', 'value' => '', 'compare' => '=' ],
					['field' => 'student_intervention_required', 'value' => 'H', 'compare' => '!=' ],
					['field' => 'student_status', 'value' => '', 'compare' => '=' ],
				]
			];
			$requestInfo = array('criteria'=>$criteria,
								 'orderby'=>'student_level, student_call_sign',
								 'order'=>'ASC');
			$studentData =  get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
			if ($studentData === FALSE) {
				debugLog("get_student_and_user_master all unassigned students returned FALSE");
			} else {
				if (! empty($studentData)) {
					$myInt = count($studentData);
					debugLog("Found $myInt potential replacement students");
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue)	{
							$$thisField = $thisValue;
						}
								
						$user_last_name 						= no_magic_quotes($user_last_name);


						if ($student_first_class_choice == '') {
							$student_first_class_choice			= 'None';
							$student_first_class_choice_utc		= 'None';
						}
						if ($student_second_class_choice == '') {
							$student_second_class_choice		= 'None';
							$student_second_class_choice_utc	= 'None';
						}
						if ($student_third_class_choice == '') {
							$student_third_class_choice			= 'None';
							$student_third_class_choice_utc		= 'None';
						}


						debugLog("<br />Processing $student_call_sign. Data read:<br />
									&nbsp;&nbsp;&nbsp;&nbsp;First class choice: $student_first_class_choice<br />
									&nbsp;&nbsp;&nbsp;&nbsp;Second class choice: $student_second_class_choice<br />
									&nbsp;&nbsp;&nbsp;&nbsp;Third class choice: $student_third_class_choice<br />
									&nbsp;&nbsp;&nbsp;&nbsp;First class choice UTC: $student_first_class_choice_utc<br />
									&nbsp;&nbsp;&nbsp;&nbsp;Second class choice UTC: $student_second_class_choice_utc<br />
									&nbsp;&nbsp;&nbsp;&nbsp;Third class choice UTC: $student_third_class_choice_utc<br />
									&nbsp;&nbsp;&nbsp;&nbsp;Student Intervention Required; $student_intervention_required<br />
									&nbsp;&nbsp;&nbsp;&nbsp;Third class choice UTC: $student_third_class_choice_utc<br />");
							
						$utc1Times					= '';
						$utc2Times					= '';
						$utc3Times					= '';
						$utc1Days					= '';
						$utc2Days					= '';
						$utc3Days					= '';
						$noGo						= FALSE;
						if ($student_intervention_required == 'H') {
							if (student_hold_reason_code != 'X') {
								debugLog("&nbsp;&nbsp;&nbsp;&nbsp;student is on hold");
							}
							$noGo					= TRUE;
						}
						if ($student_first_class_choice == '' || $student_first_class_choice == 'None') {
							debugLog("&nbsp;&nbsp;&nbsp;&nbsp;student has no first class choice");
							$noGo				= TRUE;
						} else {
							$myInt				= strtotime($student_request_date);
							if ($student_class_priority == '') {
								$student_class_priority	= 0;
							}
							$student_class_priority	= 4 - intval($student_class_priority);
							$thisSequence		= $student_class_priority . $myInt;

							/// if UTC field is set, use it. Otherwise calculate it
							if ($student_first_class_choice_utc != '' || $student_first_class_choice != 'None') {
								debugLog("Using student_first_class_choice_utc of $student_first_class_choice_utc");
								$myArray			 	= explode(" ",$student_first_class_choice_utc);
								if (count($myArray) == 2) {
									$utc1Times				= $myArray[0];
									$utc1Days			 	= $myArray[1];
								} else {
									debugLog("<br /><b>ERROR</b> $student_call_sign has an invalid first_class_choice_utc of $student_first_class_choice_utc");
								}
							} else {				/// first choice utc is empty. is there a first choice local?
								if ($student_first_class_choice != '' && $student_first_class_choice != 'None') {
									debugLog("&nbsp;&nbsp;&nbsp;&nbsp;converting first class choice of $student_first_class_choice to UTC");
									$myArray			= explode(" ",$student_first_class_choice);
									$thisTime			= $myArray[0];
									$thisDay			= $myArray[1];
									$result				= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
									if ($result[0] == 'FAIL') {
										debugLog("utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
																Error: $result[3]");
										$noGo			= TRUE;
									} else {
										$utc1Times			= $result[1];
										$utc1Days			= $result[2];
									}
								}
							}
							/// if UTC field is set, use it. Otherwise calculate it
							if ($student_second_class_choice_utc != '' && $student_second_class_choice_utc != 'None') {
								$myArray			 	= explode(" ",$student_second_class_choice_utc);
								$utc2Times				= $myArray[0];
								$utc2Days			 	= $myArray[1];
								debugLog("Using $student_second_class_choice_utc of $utc2Times $utc2Days");
							} else {				/// second choice utc is empty. is there a second choice local?
								if ($student_second_class_choice != '' && $student_second_class_choice != 'None') {
									debugLog("&nbsp;&nbsp;&nbsp;&nbsp;converting second class choice of $student_second_class_choice to UTC");
									$myArray			= explode(" ",$student_second_class_choice);
									$thisTime			= $myArray[0];
									$thisDay			= $myArray[1];
									$result				= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
									if ($result[0] == 'FAIL') {
										debugLog("utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
																Error: $result[3]");
										$utc2Times			= '';
										$utc2Days			= '';
									} else {
										$utc2Times			= $result[1];
										$utc2Days			= $result[2];
									}
								}
							}
							/// if UTC field is set, use it. Otherwise calculate it
							if ($student_third_class_choice_utc != '' && $student_third_class_choice_utc != 'None') {
								$myArray			 	= explode(" ",$student_third_class_choice_utc);
								$utc3Times				= $myArray[0];
								$utc3Days			 	= $myArray[1];
								debugLog("Using $student_third_class_choice_utc of $utc1Times $utc1Days");
							} else {				/// third choice utc is empty. is there a third choice local?
								if ($student_third_class_choice != '' && $student_third_class_choice != 'None') {
									debugLog("&nbsp;&nbsp;&nbsp;&nbsp;converting third class choice of $student_third_class_choice to UTC");
									$myArray			= explode(" ",$student_third_class_choice);
									$thisTime			= $myArray[0];
									$thisDay			= $myArray[1];
									$result				= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
									if ($result[0] == 'FAIL') {
										debugLog("utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
																Error: $result[3]");
										$utc3Times			= '';
										$utc3Days			= '';
									} else {
										$utc3Times			= $result[1];
										$utc3Days			= $result[2];
									}
								}
							}
							$myStr			= $student_excluded_advisor;
							if (!$noGo) {
								/// add to the array
								${'replaceArray' . $student_level}[] = "$thisSequence|$student_call_sign|$utc1Times|$utc1Days|$utc2Times|$utc2Days|$utc3Times|$utc3Days|$myStr|$student_class_language|$student_id";
								//										0             1                  2          3         4          5         6          7         8
								debugLog("data added to replaceArray: $student_level = $thisSequence|$student_call_sign|$utc1Times|$utc1Days|$utc2Times|$utc2Days|$utc3Times|$utc3Days|$myStr|$student_class_language|$student_id");
							} else {
								debugLog("student not added to the replaceArray");
							}
						}
					}				/// end of students
					sort($replaceArrayBeginner);
					sort($replaceArrayFundamental);
					sort($replaceArrayIntermediate);
					sort($replaceArrayAdvanced);
					/// dump the replace arrays if in debug mode

					$myInt 		= count($replaceArrayBeginner);
					$myStr =  "<br />replace array beginner ($myInt students):<br /><pre>";
					$myStr .= print_r($replaceArrayBeginner, TRUE);
					$myStr =  "</pre><br />";
					debugLog($myStr);

					$myInt		= count($replaceArrayFundamental);
					$myStr = "<br />replace array fundamental ($myInt students):<br /><pre>";
					$myStr .= print_r($replaceArrayFundamental, TRUE);
					$myStr .=  "</pre><br />";
					debugLog($myStr);

					$myInt		= count($replaceArrayIntermediate);
					$myStr =  "<br />replace array intermediate ($myInt students):<br /><pre>";
					$myStr .= print_r($replaceArrayIntermediate, TRUE);
					$myStr .= "</pre><br />";
					debugLog($myStr);

					echo$myInt		= count($replaceArrayAdvanced);
					$myStr =  "<br />replace array advanced ($myInt students):<br /><pre>";
					$myStr .= print_r($replaceArrayAdvanced, TRUE);
					$myStr .=  "</pre><br />";
					debugLog($myStr);
				} else {
					$content	.= "<p>In the replacement period. Reading the student table to build 
									the replacement array. No records found.</p>";
					debugLog("In the replacement period. Reading $studentTableName table to build 
								the replacement array. No records found. Turning validReplacementPeriod off");
				}
				debugLog("REPLACE Array: array built<br />");
			}		///////// end of build the replacement array





		//////// Begin process to handle outstanding replacement requests
			if ($currentSemester == 'Not in Session') {
				debugLog("<br /><b>OUTSTANDING</b> Outstanding Replacement Requests Process");
				$content	.= "<h4>Proccessing Outstanding Replacement Requests</h4>";
				// get the outstanding replacement requests and run any that 
				// haven't been fulfilled
				
				$sql		= "select * from $replReqTableName 
								where semester = '$proximateSemester' 
								and date_resolved = '' 
								order by date_created ";
				$wpw1_cwa_replacement_requests		= $wpdb->get_results($sql);
				debugLog("queried for outstanding replacement requests");
				if ($wpw1_cwa_replacement_requests === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numBARows = $wpdb->num_rows;
					$myStr = $wpdb->last_query;
					debugLog("ran $myStr<br />and found $numBARows rows");
					if ($numBARows > 0) {
						$outstandingRequests			= $numBARows;
						$content		.= "There are $outstandingRequests outstanding replacement requests<br />";
						foreach ($wpw1_cwa_replacement_requests as $replacement_requestsRow) {
							$replacement_id				= $replacement_requestsRow->record_id;
							$replacement_call_sign		= $replacement_requestsRow->call_sign;
							$replacement_class			= $replacement_requestsRow->class;
							$replacement_level			= $replacement_requestsRow->level;
							$replacement_semester		= $replacement_requestsRow->semester;
							$replacement_student		= $replacement_requestsRow->student;
							$replacement_date_resolved	= $replacement_requestsRow->date_resolved;
							$replacement_date_created	= $replacement_requestsRow->date_created;
							$replacement_date_updated	= $replacement_requestsRow->date_updated;
							
							debugLog("<br />Processing replacement request for advisor: $replacement_call_sign<br />
										student: $replacement_student<br />
										class: $replacement_class");

							/// package up the replacement student info and call findAReplacement function
							$replacementInfo = array('$advisorCallSign'=>$replacement_call_sign,
														'$advisorClass'=>$replacement_class,
														'$replacement_level'=>$replacement_level,
														'$replacement_student'=>$replacement_student	);
							$replacementResult = findAReplacement($replacementInfo);
							if ($replacementResult === FALSE) {
								debugLog("findAReplacement returned FALSE for $replacement_student");
							} else {
								debugLog("findAReplacement found a replacement for $replacement_student");
								$myArray = explode("|".$replacementResult);
								$studentCallSign 		= $myArray[1];
								$studentid		= $myArray[10];

								// now call the function to do the assignments, email, and reminder
								//	studentCallSign: student to be assigned
								//  studentid: id of the student record
								//  replacement_student: call sign of student being replaced
								//  replacement_call_sign: advisor needing replacement
								//  replacement_class: class sequence number needing replacement

								$replacementResult = doTheReplacement($studentCallSign, $studentid, $replacement_student, $replacement_call_sign, $replacement_class);
								if ($replacementResult === FALSE) {
									debugLog("doTheReplacement returned FALSE for $studentCallSign to $replacement_call_sign class $replacement_class");
								} else {
									/// mark the replacement request as fulfilled
									$myStr 		= date("Y-m-d H:i:s");
									$replResult		= $wpdb->update($replReqTableName,
																	array('date_resolved'=>$myStr), 
																	array('record_id'=>$replacementRecordID), 
																	array('%s'), 
																	array('%d'));
									if ($replResult === FALSE) {
										$myError			= $wpdb->last_error;
										$myQuery			= $wpdb->last_query;
										debugLog("updating $replacementRequests table failed<br />
													wpdb->last_query: $myQuery<br />
													wpdb->last_error: $myError");
										$errorMsg			= "$jobname updating replacement Requests failed.\nSQL: $myQuery\nError: $myError";
										sendErrorEmail($errorMsg);
										$content		.= "Unable to update replacement Requests<br />";
  									} else {
										$debugLog	.= "replacement request marked as filled<br />";
									}	
									$outstandingFulfilled++;													
									$replacedCount++;
									$studentReplaced	= TRUE;
									$outstandingFulfilled++;
									debugLog("doTheReplacement succeeded for $studentCallSign to $replacement_call_sign class $replacement_class");
								}							
							}
						}		//// end of loop through outstanding replacement requests
					} else {
						$content	.= "There are no outstanding replacement requests to process<br />";
						debugLog("There are no outstanding replacement requests to process");
					}
				}
			}		//////// end of outstanding replacement requests processing

		///////// Begin process to handle current replacement requests

			debugLog("<br />Current ReplacementRequests Process<br />
					Getting students with R or V status");

			// get the student records with status of R or V
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					// field1 = $value1
					['field' => 'student_semester', 'value' => $proximateSemester, 'compare' => '='],
					
					// (field2 = $value2 OR field2 = $value3)
					[
						'relation' => 'OR',
						'clauses' => [
							['field' => 'student_status', 'value' => 'R', 'compare' => '='],
							['field' => 'student_status', 'value' => 'V', 'compare' => '=']
						]
					]
				]
			];

			$requestInfo = array('criteria'=>$criteria,
									'orderby'=>'student_call_sign',
									'order'=>'ASC');
			$studentData = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
			debugLog("returned from get	_student_and_user_master");
			if ($studentData === FALSE) {
				debugLog("get_student_and_user_master for replacement requests returned FALSE");
			} else {
				if (! empty($studentData)) {
					$numRequests			= count($studentData);
					$content		.= "<h4>Processing $numRequests Current Replacement Requests</h4>";
					debugLog("Processing $numRequests current replacement requests");
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue)	{	
							$$thisField = $thisValue;
						}
						if (! isset($student_call_sign)) {	
							debugLog("<b>ERROR</b></b>student_call_sign is not set<br />");
						}
						debugLog("<br />Processing student $student_call_sign with status $student_status"); 

						// make sure the student is has an assigned advisor and has a class number of not 0
						if ($student_assigned_advisor != '' && $student_assigned_advisor_class != 0) {
							debugLog("student assigned advisor is set to $student_assigned_advisor and assigned_advisor_class is not 0");

							// get the advisor and advisorclass records then look for a doTheReplacement
							$advisorData = get_advisor_and_user_master($student_assigned_advisor, 'callsign', $proximateSemester, $operatingMode, $doDebug);
							debugLog("returned from get_advisor_and_user_master for $student_assigned_advisor");	
							if ($advisorData === FALSE) {
								debugLog("get_advisor_and_user_master for $student_assigned_advisor returned FALSE");
							} else {
								if (! empty($advisorData)) {
									foreach($advisorData as $key => $value) {
										$key = str_replace('user_','advisor_user_',$key);
										$$key = $value;
									}
									if (! isset($advisor_user_call_sign)) {
										debugLog("<b>ERROR</b> advisor_user_call_sign is not set<br />");
									}

									// get the advisorclass record	
									$criteria = [
										'relation' => 'AND',
										'clauses' => [
											['field' => 'advisorclass_call_sign', 'value' => $student_assigned_advisor, 'compare' => '=' ],
											['field' => 'advisorclass_sequence', 'value' => $student_assigned_advisor_class, 'compare' => '=' ],
											['field' => 'advisorclass_semester', 'value' => $proximateSemester, 'compare' => '=' ]
										]
									];
									$myStr = print_r($criteria, TRUE);
									debugLog("Getting advisorclass data with criteria<br /><pre>$myStr</pre>");
									$advisorclassData = $advisorclass_dal->get_advisorclasses_by_order( $criteria, 'advisorclass_sequence', 'ASC', $operatingMode );
									debugLog("returned from get_advisorclasses for $student_assigned_advisor class $student_assigned_advisor_class");	
									if ($advisorclassData === FALSE) {
										debugLog("get_advisorclasses for $student_assigned_advisor returned FALSE");
									} else {
										if (! empty($advisorclassData)) {
											foreach($advisorclassData as $key => $value) {
												foreach($value as $thisField => $thisValue)	{	
													$$thisField = $thisValue;
												}
												if (! isset($advisorclass_call_sign)) {
													debugLog("<b>ERROR</b> advisorclass_call_sign is not set<br />");	
												}


												// now call findAReplacement function
												$replacementInfo = array('advisorCallSign'=>$student_assigned_advisor,
																			'advisorClass'=>$student_assigned_advisor_class,
																			'replacement_level'=>$student_level,
																			'replacement_student'=>$student_call_sign);
												$replacementResult = findAReplacement($replacementInfo);
												debugLog("returned from findAReplacement for $student_call_sign with record $replacementResult");
												if ($replacementResult === FALSE) {
													debugLog("findAReplacement returned FALSE for $student_call_sign");
													// notify the advisor add the replqcement request to the oustanding requests table
													debugLog("No replacement found");
													$content	.= "<br />REPLACE Request: No replacement found for student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																	target='_blank'>$student_call_sign</a> for $advisorclass_call_sign $advisorclass_sequence class.<br />";
													//// format email to the advisor
													$email_content = "<p>Unfortunately, no students are available for assignment that meet the criteria for your class.<p>";
													$notReplacedCount++;
													// update advisorClass record with the replacement information
													if ($doDebug) {
														echo "Updating advisorClass record with the relplacement information<br />";
													}
													$advisorclass_action_log	.= " / $actionDate CRON Advisor requested replacement for $student_call_sign. No replacement found ";
													$updateParams = array('advisorclass_action_log'=>$advisorclass_action_log);
													$advisorClassUpdateResult = $advisorclass_dal->update( $advisorclass_id, $updateParams, $operatingMode );
													if ($advisorClassUpdateResult === FALSE) {
														$errorMsg			= "$jobname updating advisorclass record for $advisorclass_call_sign class $advisorclass_sequence failed while attempting to log replacement request. ";
														sendErrorEmail($errorMsg);
														$content		.= "Unable to update advisorclass record for $advisorclass_call_sign class $advisorclass_sequence<br />";
													} else {		// if update is successful, add the record to replacementRequests table
														$replParams 	= array('call_sign'=>$advisorclass_call_sign,
																				'class'=>$advisorclass_sequence,
																				'level'=>$advisorclass_level,
																				'semester'=>$student_semester,
																				'student'=>$student_call_sign);

														$replFormat 	= array('%s','%d','%s','%s','%s');
								
														$insertResult	= $wpdb->insert($replReqTableName,
																						$replParams,
																						$replFormat);
														if ($insertResult === FALSE) {
															$myError			= $wpdb->last_error;
															$myQuery			= $wpdb->last_query;
															debugLog("Inserting into $replacementRequests table failed<br />
																		wpdb->last_query: $myQuery<br />
																		wpdb->last_error: $myError");
															$errorMsg			= "$jobname inserting replacementRequests failed while attempting to move to past_student. <p>SQL: $myQuery</p><p> Error: $myError</p>";
															sendErrorEmail($errorMsg);
															$content		.= "Unable to insert into $replacementRequests<br />";
														} else {
															$newID			= $wpdb->insert_id;
															$myStr			= $wpdb->last_query;
															debugLog("ran $myStr<br />and inserted $newID into Replacement Requests<br />");

															// remove student from the advisor class and set the student status to C
															$inp_data			= array('inp_student'=>$student_call_sign,
																						'inp_semester'=>$student_semester,
																						'inp_assigned_advisor'=>$student_assigned_advisor,
																						'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
																						'inp_remove_status'=>'C',
																						'inp_arbitrarily_assigned'=>$student_no_catalog,
																						'inp_method'=>'remove',
																						'jobname'=>$jobname,
																						'userName'=>$userName,
																						'testMode'=>$testMode,
																						'doDebug'=>$doDebug);
																			
															$removeResult		= add_remove_student($inp_data);
															if ($removeResult[0] === FALSE) {
																$thisReason		= $removeResult[1];
																debugLog("attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />");
																sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
																$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
															} else {
																debugLog("student $student_call_sign status set to C successfully");
																$content		.= "&nbsp;&nbsp;&nbsp;&nbsp;Student status set to C<br />";
															}

															///// send the email to the advisor
															$theSubject			= "CW Academy Replacement Student Information for your Class";
															if ($testMode) {
																$theRecipient			= 'rolandksmith@gmail.com';
																$theSubject				= "TESTMODE $theSubject";
																$mailCode				= 2;
															} else {
																$theRecipient			= $advisor_user_email;
																$mailCode				= 14;
															}
															$strSemester				= $currentSemester;
															$thisContent			= "<p>To: $advisor_user_last_name, $advisor_user_first_name ($advisorclass_call_sign):</p>
																						<p>You have requested a replacement student for $user_last_name, $user_first_name 
																						($student_call_sign) in your $student_level class number $advisorclass_sequence.</p>
																						$email_content
																						<p>If you have questions or concerns, do not reply to this email as the address is not monitored. 
																						Instead reach out to <a href='classResolutionURL' target='_blank'>CWA Class Resolution</a> and select the appropriate person.</p> 
																						<p>Thanks for your service as an advisor!<br />
																						CW Academy</p>";
															$mailResult				= emailFromCWA_v3(array('theRecipient'=>$theRecipient,
																											'theSubject'=>$theSubject,
																											'theContent'=>$thisContent,
																											'jobname'=>$jobname,
																											'mailCode'=>$mailCode,
																											'testMode'=>$testMode,
																											'doDebug'=>FALSE));
															// $mailResult = TRUE;
															if ($mailResult[0] === TRUE) {
																$content .= "&nbsp;&nbsp;&nbsp;&nbsp;An email was sent to <a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_email=$theRecipient&strpass=2' 
																		target='_blank'>$theRecipient</a>.<br />";
																debugLog("$mailResult[1]");
																debugLog("Replacement email sent to the advisor at $theRecipient");
															} else {
																$content .= "&nbsp;&nbsp;&nbsp;&nbsp;The replacement email send function to advisor $advisor_call_sign; email: $theRecipient failed.<br />";
																debugLog("$mailResult[1]");
																debugLog("Replacement email FAILED to advisor at $theRecipient");
															}
														}
													}
												} else {		// replacement found
													debugLog("findAReplacement found a replacement for $student_call_sign");
													$myArray = explode("|",$replacementResult);
													$studentCallSign  = $myArray[1];
													$studentid		= $myArray[10];
													debugLog("replacement is studentCallSign: $studentCallSign, studentid: $studentid");

													// now call the function to do the assignments, email, and reminder
													//	studentCallSign: student to be assigned
													//  studentid: id of the student record
													//  replacement_call_sign: advisor needing replacement
													//  replacement_class: class sequence number needing replacement

													$replacementResult = doTheReplacement($studentCallSign, $studentid,   $student_call_sign, $student_assigned_advisor, $student_assigned_advisor_class);
													if ($replacementResult === FALSE) {
														debugLog("doTheReplacement returned FALSE for $studentCallSign to $student_assigned_advisor class $student_assigned_advisor_class");
													} else {
														debugLog("doTheReplacement succeeded for $studentCallSign to $student_assigned_advisor class $student_assigned_advisor_class");
														$replacedCount++;
													}
												}
											}
										} else {
											debugLog("getting the advisorclass record for $student_assigned_advisor class $student_assigned_advisor_class returned no data");
										}
									}
								} else {
									debugLog("getting the advisor record for $student_assigned_advisor returned no data");
								}
							}
						} else {
							debugLog("student $student_call_sign is assigned to $student_assigned_advisor for class $student_assigned_advisor_class");
							$content .= "Student $student_call_sign is assigned to $student_assigned_advisor for class $student_assigned_advisor_class<br />";
						}
					}		//// end of loop through advisorclass records
				} else {
					debugLog("getting the student data for studentso needing replacement returned no data");
					$content .= "Unable to retrieve the data on students needing replacment data";
				}
			}
			debugLog("<br />$jobname processing complete<br />
						Total outstanding replacement requests processed: $numBARows<br />
						Total outstanding replacement requests fulfiller: $outstandingFulfilled<br />
						Total current replacement requests processed: $numRequests<br />
						Total current replacement requests fulilled: $replacedCount<br />");
			$content	.= "<br /><h4>$jobname processing complete</h4>
						Total outstanding replacement requests processed: $numBARows<br />
						Total outstanding replacement requests fulfilled: $outstandingFulfilled<br />
						Total current replacement requests processed: $numRequests<br />
						Total current replacement requests fulilled: $replacedCount<br />";
			$nowDate		= date('Y-m-d');
			$nowTime		= date('H:i:s');
 			if ($testMode) {
				$storeResult	= storeReportData_v2("TESTMODE $jobname DEBUG",$debugRepprt);
				$storeResult	= TRUE;
			} else {
				$storeResult	= storeReportData_v2("$jobname DEBUG",$debugReport);
			}
			if ($storeResult !== FALSE) {
				$content	.= "<br />Debug report stored in reports table as $storeResult[1]";
			} else {
				$content .= "<br />Storing the report in the reports table failed";
			}

			// store the report in the reports table
			$storeResult	= storeReportData_v2($jobname,$content,$testMode,$doDebug);
			if ($storeResult[0] === FALSE) {
				$content	.= "Storing report failed. $storeResult[1]<br />";
			} else {
				$reportid	= $storeResult[2];
			}
			
			// store the reminder
			$effective_date		= date('Y-m-d 00:00:00');
			$closeStr			= strtotime("+ 2 days");
			$close_date			= date('Y-m-d 00:00:00',$closeStr);
			$token			= mt_rand();
			$reminder_text	= "<b>$jobname</b> To view the report for $nowDate $nowTime, click <a href='cwa-display-saved-report/?strpass=3&inp_callsign=XXXXX&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
			$inputParams		= array("effective_date|$effective_date|s",
										"close_date|$close_date|s",
										"resolved_date||s",
										"send_reminder|N|s",
										"send_once|N|s",
										"call_sign||s",
										"role|administrator|s",
										"email_text||s",
										"reminder_text|$reminder_text|s",
										"resolved|N|s",
										"token|$token|s");
			$reminderResult	= add_reminder($inputParams,$testMode,$doDebug);
			if ($reminderResult[0] === FALSE) {
				$content .= "adding reminder failed. $reminderResult[1]<br />";
			}

			$theSubject	= "$jobname";
			$theContent	= "The $jobname process was run at $nowDate $nowTime, Login to <a href='$siteURL/program-list'>CW Academy</a> to see the 
							report.";
			if ($testMode) {		
				$theRecipient	= '';
				$mailCode	= 2;
				$theSubject = "TESTMODE $theSubject";
			} else {
				$theRecipient	= '';
				$mailCode		= 18;
			}
			$result		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
													'theSubject'=>$theSubject,
													'jobname'=>$jobname,
													'theContent'=>$theContent,
													'mailCode'=>$mailCode,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug));
			if ($result === TRUE) {
				$content .= "<br />The final email was sent successfully.<br />";
			} else {
				$content .= "<br />The final mail send function failed>";
			}

			$thisTime 		= current_time('mysql', 1);
			$content		.= "<br />Function completed at $thisTime<br />";
			$theSubject		= "$jobname";
			$endingMicroTime = microtime(TRUE);
			$elapsedTime	= $endingMicroTime - $startingMicroTime;
			$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
			$content		.= "<p>Report pass 0 took $elapsedTime seconds to run</p>";
			$thisStr		= 'Production';
			if ($testMode) {
				$thisStr	= 'Testmode';
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
									'jobaddlinfo'	=> "0: $elapsedTime",
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
	}
}
add_shortcode('daily_replacement_cron', 'daily_replacement_cron_func');
