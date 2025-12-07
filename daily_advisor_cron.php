function daily_advisor_cron_process_func() {

/*		Daily Advisor Cron
  validEmailPeriod is FALSE
   This job is run via a cron curl job to run the associated webpage
  
  	Check to send welcome email to advisor
  		Read the advisor pod. If the welcome_email_date is empty, see if the advisor
  		has already gotten a welcome email. If not, send a welcome email
  
  	Check to send verify email to advisor
  		if within the email period,
  		Read the advisor pod and for each advisor, get all advisor records.
  		If the record has an empty verify_email_date, set that date and set verify_email_number
  			to 1 and send the verification email
  
  
  	Date Formats:
  		Email Sent Date: 		Y-M-D
  		Welcome Date:			Y-M-D
  		Advisor Selected Date:	Y-M-D
  
*/

	global $wpdb;

	$doDebug				= TRUE;
	$testMode				= FALSE;
	$verifyMode				= FALSE;
	$demoMode				= FALSE;
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
	$versionNumber			= '3';
	
	
	$testEmailTo			= "kcgator@gmail.com,rolandksmith@gmail.com";
//	$testEmailTo			= "rolandksmith@gmail.com";
//	$inp_mode				= '';
	ini_set('max_execution_time',360);

	$initializationArray 	= data_initialization_func();

	if ($verifyMode && $testMode) {
		$initializationArray['validEmailPeriod']	= 'Y';
		$initializationArray['daysToSemester']		= 45;
		if ($doDebug) {
			echo "verifyMode and testMode. Have fudged the initializationArray<br />";
		}
	}
	
	
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName				= $initializationArray['userName'];
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');

// Needed variables initialization
	$processStudent			= TRUE;
	$blankArray				= array();
	$welcomeCount 			= 0;
	$verifyCount			= 0;
	$replacedCount			= 0;
	$notReplacedCount		= 0;
	$surveyScore6			= 0;
	$numARows				= 0;
	$jobname				= 'Daily Advisor Cron';
	$currentTimestamp 		= $initializationArray['currentTimestamp'];
	$todaysDate 			= $initializationArray['currentDate'];
	$checkDate 				= date('Y-m-d',$currentTimestamp);
	$unixCheckDate			= strtotime($checkDate);
	$currentSemester		= $initializationArray['currentSemester'];
	$proximateSemester		= $initializationArray['proximateSemester'];
	$nextSemester 			= $initializationArray['nextSemester'];
	$semesterTwo 			= $initializationArray['semesterTwo'];
	$semesterThree 			= $initializationArray['semesterThree'];
	$semesterFour 			= $initializationArray['semesterFour'];
	$validEmailPeriod 		= $initializationArray['validEmailPeriod'];
	$daysToSemester			= $initializationArray['daysToSemester'];
	$siteURL				= $initializationArray['siteurl'];
	$actionDate				= date('Y-m-d H:i:s');
	$logDate				= date('Y-m-d H:i:s');
	$advisorVerifyURL		= "$siteURL/cwa-process-advisor-verification/";
	$classResolutionURL		= "https://cwops.org/cwa-class-resolution/";
	$advisorRegistrationURL = "$siteURL/cwa-advisor-registration/";
	$recordsProcessed		= 0;
	$student45DayCount		= 0;
	$studentNoWelcomeDate	= 0;
	$studentInClass			= 0;
	$studentNoRequestDate	= 0;
	$studentWelcomeMatch	= 0;
	$emailErrors			= 0;
	$notEnoughTime			= 0;
	$noEmailSentYR			= 0;
	$studentHasResponded	= 0;
	$verifyEmailCount		= 0;
	$numberVRecords			= 0;
	$verifyCount			= 0;
	$dupEmailsSent			= 0;
	$advisorEmails			= 0;
	$advisorWelcomed		= 0;
	$advisorFirstCount		= 0;
	$advisorSecondCount		= 0;
	$advisorThirdCount		= 0;
	$advisorDropCount		= 0;
	$advisorEmailErrors		= 0;
	$confirmedClasses		= 0;
	$thisSemesterAdvisors	= 0;
	$futureSemesterAdvisors	= 0;
	$advisorWelcomeCount	= 0;
	$refusedClasses			= 0;
	$unconfirmedClasses		= 0;
	$advisorEmailCount		= 3;
	$verifyDoOnce			= TRUE;
	$theSemester			= '';
	$increment				= 0;
	$log_ID  								= '';    // id
	$log_call_sign  						= '';    // call_sign
	$log_first_name  						= '';    // first_name
	$log_last_name  						= '';    // last_name
	$log_email  							= '';    // email
	$log_phone  							= '';    // phone
	$log_city 		 						= '';    // city
	$log_state  							= '';    // state
	$log_country  							= '';    // country
	$log_zip_code							= '';
	$log_time_zone  						= '';    // time_zone
	$log_timezone_id  						= '';    
	$log_timezone_offset 					= '';    
	$log_whatsapp  							= '';
	$log_signal  							= '';
	$log_telegram  							= '';
	$log_messenger  						= '';
	$log_wpm  								= '';    // wpm
	$log_youth  							= '';    // youth
	$log_age 								= '';    // age
	$log_student_parent 					= '';    // student_parent
	$log_student_parent_email				= '';    // student_parent_email
	$log_level  							= '';    // level
	$log_start_time 						= '';    // start_time
	$log_request_date  						= '';    // request_date
	$log_semester  							= '';    // semester
	$log_notes  							= '';    // notes
	$log_email_sent_date  					= '';    // email_sent_date
	$log_email_number  						= '';    // email_number
	$log_response  							= '';    // response
	$log_response_date  					= '';    // response_date
	$log_response_number  					= '';    // response_number
	$log_student_status  					= '';    // student_status
	$log_action_log 						= '';    // action_log
	$log_advisor  							= '';    // advisor
	$log_selected_date  					= '';    // selected_date
	$log_welcome_date  						= '';    // welcome_date
	$log_passed_over_count  				= '';    // passed_over_count
	$log_hold_override  					= '';    // hold_override
	$log_messaging							= '';	 // messaging
	$log_student_code  						= '';    // student_code
	$log_assigned_advisor  					= '';    // assigned_advisor
	$log_advisor_class_timezone 			= '';    // advisor_class_timezone
	$log_advisor_select_date 				= '';    // advisor_select_date
	$log_hold_reason_code  					= '';    // hold_reason_code
	$log_class_priority  					= '';    // class_priority
	$log_assigned_advisor_class 			= '';    // assigned_advisor_class
	$log_promotable  						= '';    // promotable
	$log_excluded_advisor  					= '';    // excluded_advisor
	$log_student_survey_completion_date 	= '';    // student_survey_completion_date
	$log_available_class_days 				= '';    // available_class_days
	$log_intervention_required 				= '';    // intervention_required
	$log_copy_control  						= '';    // copy_control
	$log_first_class_choice					= '';
	$log_second_class_choice				= '';
	$log_third_class_choice					= '';
	$fieldTest								= array('action_log','post_status','post_title','control_code');
	$update_action_log						= '';
	$deleteArray			= array();
	$deleteParam			= array();
	$arrayLevels			= array('fundamental','Fundamental','FUNDAMENTAL',
									'beginner','Beginner','BEGINNER',
									'intermediate','Intermediate','INTERMEDIATE',
									'advanced','Advanced','ADVANCED');
	$advisorArray				= array();
	$errorArray					= array();
	$advisorClassInc			= 0;
	$doDaysTest					= TRUE;
	
	
	
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
				
				table{font:'Times New Roman', sans-serif;background-image:none;}
				
				th {color:#ffff;background-color:#000;padding:5px;font-size:small;}
				
				td {padding:5px;font-size:small;}
				</style>";

$runTheJob	= TRUE;
		
	if ($userName != '') {
		$content 		.= "<h3>Daily Cron Advisor Process Executed by $userName</h3>";
	} else {
		$content		.= "<h3>Daily Cron Advisor Process Automatically Executed</h3>";
		$userName		= "CRON";
		$runTheJob		= allow_job_to_run($doDebug);
	}
	if ($runTheJob) {
	
		if ($testMode) {
			$advisorTableName		= 'wpw1_cwa_advisor2';
			$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
			$userMasterTableName	= 'wpw1_cwa_user_master2';
			$content .= "<p><strong>Function is under development</strong></p>";
			if ($doDebug) {
				echo "<b>Operating in TestMode</b><br />";
			}
			$operatingMode			= 'Testmode';
			$xmode					= 'tm';
		} else {
			$advisorTableName		= 'wpw1_cwa_advisor';
			$advisorClassTableName	= 'wpw1_cwa_advisorclass';
			$userMasterTableName	= 'wpw1_cwa_user_master';
			$xmode					= 'pd';
			$operatingMode			= 'Production';
		}

		$advisor_dal = new CWA_Advisor_DAL();
		$advisorclass_dal = new CWA_Advisorclass_DAL();
		$user_dal = new CWA_User_Master_DAL();

	// Advisor welcome and verify process

	// $doDebug = TRUE;

		$advisorArray		= array();
		$advisorRecords		= 0;
		$addlAdvisorRecords	= 0;
	
		$doAdvisorWelcome	= FALSE;
		$doAdvisorVerify	= FALSE;
	
		if ($validEmailPeriod == 'N' || $daysToSemester > 45) {
			if ($doDebug) {
				echo "Send Welcome message<br >";
			}
			$doAdvisorWelcome	= TRUE;
		} else {
			if ($doDebug) {
				echo "Within the verification period<br >";
			}
			$doAdvisorVerify	= TRUE;
		}
		$doProceed				= TRUE;
		// get the advisor
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				// field1 = $value1
				['field' => 'advisor_call_sign', 'value' => '', 'compare' => '!='],
				
				// (field2 = $value2 OR field2 = $value3)
				[
					'relation' => 'OR',
					'clauses' => [
						['field' => 'advisor_semester', 'value' => $currentSemester, 'compare' => '='],
						['field' => 'advisor_semester', 'value' => $nextSemester, 'compare' => '='],
						['field' => 'advisor_semester', 'value' => $semesterTwo, 'compare' => '='],
						['field' => 'advisor_semester', 'value' => $semesterThree, 'compare' => '='],
						['field' => 'advisor_semester', 'value' => $semesterFour, 'compare' => '=']
					]
				]
			]
		];
		$advisorResult = $advisor_dal->get_advisor_by_order($criteria,'advisor_call_sign','ASC',$operatingMode);
		if ($advisorResult === FALSE) {
			$content .= "Attempting to retrieve all advisors for current or future semesters returned FALSE<br />";
		} else {
			$numARows = count($advisorResult);
			foreach($advisorResult as $key => $value) {
				foreach($value as $thisField => $thisValue) {
					$$thisField = $thisValue;
				}
				if ($doDebug) {
					echo "<br />Processing $advisor_call_sign<br />";
				}
				// get the user_master for this advisor
				$userResult = $user_dal->get_user_master_by_callsign($advisor_call_sign,$operatingMode);
				if ($userResult == NULL) {
					$content .= "Attempting to get user_master for $user_call_sign returned NULL<br />";
				} else {
//					if ($doDebug) {
//						echo "userResult:<br /><pre>";
//						print_r($userResult);
//						echo "</pre><br />";
//					}
					foreach($userResult as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
					}
					if ($doProceed) {
						$user_last_name 	= no_magic_quotes($user_last_name);
	
						$sendEmail			= FALSE;
						$doUpdate			= FALSE;
						$myWelcomeDate		= FALSE;
						$myVerifyDate		= FALSE;
						$myNotVerified		= TRUE;
						$isNextSemester		= FALSE;
						if ($advisor_semester == $nextSemester) {
							$isNextSemester	= TRUE;
						}
						$stringToPass		= "inp_callsign=$advisor_call_sign&inp_email=$user_email&inp_phone=$user_phone&inp_mode=$xmode";
						$enstr				= base64_encode($stringToPass);
						$stringToPass1		= "id=$advisor_id&action=Y&xmode=$xmode&validate=valid";
						$enstr1				= base64_encode($stringToPass1);
						$stringToPass2		= "id=$advisor_id&action=R&xmode=$xmode&validate=valid";
						$enstr2				= base64_encode($stringToPass2);
						$user_action_log	= "$advisor_action_log / $actionDate ";
						if ($user_whatsapp == '') {
							$user_whatsapp = '--';
						}
						if ($user_signal == '') {
							$user_signal = '--';
						}
						if ($user_telegram == '') {
							$user_telegram = '--';
						}
						if ($user_messenger == '') {
							$user_messenger = '--';
						}
	
	
						if ($doDebug) {
							echo "<br />Processing $advisor_call_sign<br />
								  &nbsp;&nbsp;&nbsp;advisor_welcome_email_date: $advisor_welcome_email_date<br />
								  &nbsp;&nbsp;&nbsp;advisor_verify_email_date: $advisor_verify_email_date<br />
								  &nbsp;&nbsp;&nbsp;advisor_verify_email_number: $advisor_verify_email_number<br />
								  &nbsp;&nbsp;&nbsp;advisor_verify_response: $advisor_verify_response<br />
								  &nbsp;&nbsp;&nbsp;user_timezone: $user_timezone_id <br />
								  &nbsp;&nbsp;&nbsp;advisor_semester:$advisor_semester<br />
								  &nbsp;&nbsp;&nbsp;user_survey_score:$user_survey_score<br />";
						}
	
						// if the advisor has a survey score of 6, bypass the advisor
						if ($user_survey_score == '6') {
							if ($doDebug) {
								echo "Survey score of 6. Bypassing<br />";
							}
							$surveyScore6++;
							$content		.= "Advisor $advisor_call_sign has a survey score of 6<br />";
						} else {
							if ($advisor_semester == $proximateSemester) {
								$isNextSemester	= TRUE;
								$thisSemesterAdvisors++;
							} else {
								$isNextSemester	= FALSE;
								$futureSemesterAdvisors++;
							}
							// if a welcome email has been sent and the advisor has verified, no action needed
							if ($advisor_welcome_email_date != '' and $advisor_verify_response !='') {	
								if ($doDebug) {
									echo "Advisor has been welcomed and has verified. Bypassing<br />";
								}
								if ($advisor_verify_response == 'Y') {
									$confirmedClasses++;
								} elseif ($advisor_verify_response == 'R') {
									$refusedClasses++;
								}
							} else {
								if ($doDebug) {
									echo "Advisor needs to be processed<br />";
								}
								if ($advisor_verify_response == '') {
									$unconfirmedClasses++;
								}					
								if ($advisor_welcome_email_date != '') {
									$myWelcomeDate	= TRUE;
									$advisorWelcomed++;
								}
								if ($advisor_verify_email_date != '') {
									$myVerifyDate	= TRUE;
								}
								if ($advisor_verify_response != '') {
									$myNotVerified	= FALSE;
								}
								if ($doDebug) {
									echo "Processing a record for $advisor_call_sign<br />";
									if ($isNextSemester) {
										echo "Has class in nextSemester<br />";
									} else {
										echo "Has class in a future semester<br />";
									}
									if ($myWelcomeDate) {
										echo "Welcome date of $advisor_welcome_email_date has been set & welcome email sent<br />";
									} else {
										echo "No welcome email has been sent<br />";
									}
									if ($myVerifyDate) {
										echo "Verify date of $advisor_verify_email_date has been set & verify email sent. Verify number: $advisor_verify_email_number<br />";
									} else {
										echo "No verify email has been sent<br />";
									}
									if ($myNotVerified) {
										echo "Advisor has not verified<br />";
									} else {
										echo "Advisor has verified advisor_verify_response = $advisor_verify_response<br />";
									}
								}
								$updateParams					= array();
								// fix some possible errors
								if (!$isNextSemester) {			// registered for a future semester
									if ($myVerifyDate)	{		// should be false - error
										if ($doDebug) {
											echo "myVerifyDate should be false and isn't. Resetting the verify date<br />";
										}
										$updateParams['advisor_verify_email_date'] = '';
										$myVerifyDate			= FALSE;
										$doUpdate				= TRUE;
										$advisor_action_log		.= "removed verify_email_date ";
									}
									if (!$myNotVerified) {		// should not be true. blank out verify_response
										if ($doDebug) {
											echo "myNotVerified should be false and is not. Blanking out verify_response<br />";
										}
										$updateParams['advisor_verify_response'] = '';
										$doUpdate				= TRUE;
										$myNotVerified			= FALSE;
										$advisor_action_log		.= "removed verify_response ";
									}
								}
/*
* 	Build the output record
* 	if welcome date is empty 
*		set up the welcome email
*		if advisor semester is next semester
*			if within verify period set verify_response=Y, verify_date to today, verify_email_number to 0
*	otherwise if within verify period and advisor is next semester and verify_response = ''
*		setup the verify email
*/

								$advisorInfo	= "<h4>Advisor Registration Record</h4>
													<table style='width:600px;'>
													<tr><td><b>Call Sign</b><br />$advisor_call_sign</td>
														<td><b>First Name</b><br />$user_first_name</td>
														<td><b>Last Name</b><br />$user_last_name</td></tr>
													<tr><td><b>Email</b><br />$user_email</td>
														<td><b>Phone</b><br />$user_ph_code $user_phone</td>
														<td><b>City</b><br />$user_city</td></tr>
													<tr><td><b>State / Region / Province</b><br />$user_state</td>
														<td><b>Zip / Postal Code</b><br />$user_zip_code</td>
														<td><b>Country</b><br />$user_country ($user_country_code)</td></tr>
													<tr><td><b>Languages</b><br />$user_languages</td>
														<td><b>Semester</b><br />$advisor_semester</td>
														<td ><b>Timezone</b><br />$user_timezone_id</td></tr>
													<tr><td colspan='3'><b>Other messaging apps</b><br />
														<table>
														<tr><td style='width:30%;'><b>Whatsapp</b><br />$user_whatsapp</td>
															<td style='width:30%;'><b>Signal</b><br />$user_signal</td>
															<td style='width:30%;'><b>Telegram</b><br />$user_telegram</td>
															<td><b>Messenger</b><br />$user_messenger</td></tr></table></tr>
													</table>";
	
								// Obtain the class record information
								$classRecord		= "";
								$updateClassParams = array();
								$criteria = [
									'relation' => 'AND',
									'clauses' => [
										['field' => 'advisorclass_call_sign', 'value' => $advisor_call_sign, 'compare' => '=' ],
										['field' => 'advisorclass_semester', 'value' => $advisor_semester, 'compare' => '=' ]
									]
								];
								$orderby = 'advisorclass_sequence';
								$order = 'ASC';								
								$wpw1_cwa_advisorclass = $advisorclass_dal->get_advisorclasses_by_order($criteria,$orderby,$order,$operatingMode);
								if ($wpw1_cwa_advisorclass === FALSE) {
									$content .= "Attempting to retrieve advisorclass record for $advisor_call_sign $advisor_semester returned FALSE<br />";
								} else {
									if (! empty($wpw1_cwa_advisorclass)) {
										foreach($wpw1_cwa_advisorclass as $key => $value) {
											foreach($value as $thisField => $thisValue) {
												$$thisField = $thisValue;
											}
											if ($doDebug) {
												echo "have $advisorclass_call_sign class $advisorclass_sequence<br />";
											}
										
											/// get the UTC times if these fields are empty for some reason
											if ($advisorclass_class_schedule_days_utc == '' || $advisorclass_class_schedule_times_utc == '') {									
												if ($advisorclass_class_incomplete != 'Y') {	
													$thisResult						= utcConvert('toutc',$advisorclass_timezone_offset,$advisorClass_class_schedule_times,$advisorClass_class_schedule_days);
													if ($thisResult[0] == 'FAIL') {
														if ($doDebug) {
															echo "utcConvert failed toutc,$advisorclass_timezone,$advisorclass_class_schedule_times,$advisorclass_class_schedule_days<br />Error: $result[3]<br />";
														}
														$advisorclass_class_schedule_days_utc	= "ERROR";
														$advisorclass_class_schedule_times_utc	= '';
														$advisorclass_class_incomplete	= 'Y';
														$updateClassParams['advisorclass_schedule_days_utc'] = $advisorClass_class_schedule_days_utc;
														$updateClassParams['advisorclass_schedule_times_utc'] = $advisorClass_class_schedule_times_utc;
														$updateParams['advisorclass_incomplete'] = 'Y';
														$advisorclass_action_log .= "updated UTC times ";
													} else {
														$advisorclass_class_schedule_times_utc	= $thisResult[1];
														$advisorclass_class_schedule_days_utc	= $thisResult[2];
														$updateClassParams['advisorclass_schedule_days_utc'] = $advisorClass_class_schedule_days_utc;
														$updateClassParams['advisorclass_schedule_times_utc'] = $advisorClass_class_schedule_times_utc;
														$advisorclass_action_log .= "updated UTC times ";
													}
												} else {
													$advisorclass_class_schedule_days_utc		= "ERROR";
												}
											}
											$classScheduleProblem	= "";
											if ($advisorclass_class_schedule_days_utc == "ERROR" || $advisorclass_class_incomplete == 'Y') {
												$classScheduleProblem	= "<b>There is an issue with your teaching schedule. Please go to 
																		   <a href='$advisorRegistrationURL?$enstr'>CWA Advisor Sign-up</a> and correct the problem.";
											}
											if ($advisorclass_sequence == 1) {
												$classRecord		.= "<table style='width:600px;'>";
											}
											$classRecord			.= "<tr><td style='width:33%;'><b>Class</b><br />$advisorclass_sequence</td>
																			<td style='width:33%;'><b>Level</b><br />$advisorclass_level</td>
																			<td><b>Class Size</b><br />$advisorclass_class_size</td></tr>
																		<tr><td colspan='3'><b>Class Schedule</b><br />$advisorclass_class_schedule_times $advisorclass_class_schedule_days local time</td></tr>";
											if ($classScheduleProblem != '') {
												$classRecord	.= "<tr><td colspan='3'>$classScheduleProblem</td></tr>";
											}
											$classRecord	.= "<tr><td colspan='3'><hr></td></tr>";
											if (! empty($updateClassParams)) {
												// need to update the advisor class record
												if ($doDebug) {
													echo "updateClassParams:<br /><pre>";
													print_r($updateClassParams);
													echo "</pre><br />";
												}
												$classUpdateResult = $advisorclass_dal->update($advisorclass_id,$updateClassParams,$operatingMode);
												if ($classUpdateResult === FALSE) {
													$content .= "Attempt to update advisorclass_id $advisorclass_id ($advisorclass_call_sign $advisorclass_semester $advisorclass_sequence) returned FALSE<br />";
												} else {
													$content .= "Advisor Class $advisorclass_call_sign sequence $advisorclass_sequence for $advisorclass_semester UTC times update<br />";
												}
											}

										}
										$classRecord		.= "</table>";
										if ($doDebug) {
											echo "got the advisor and class records and ready to proceed<br />";
										}
										if (!$myWelcomeDate) {			/// welcome email needed						
											$updateParams['advisor_welcome_email_date'] = $actionDate;
											$doUpdate				= TRUE;
											$sendEmail				= TRUE;
											$content				.= "Welcome email sent to $advisor_call_sign<br />";
											$advisorWelcomeCount++;
											if ($doDebug) {
												echo "updating welcome_email_date and sending welcome email<br />";
											}
											if ($validEmailPeriod == 'Y') {		// if so, verify the advisor
												if ($doDebug) {
													echo "validEMailPeriod is Y. Verifying the advisor<br />";
												}
												$advisor_action_log		.= "verify email sent to $user_email. ";
												$updateParams['advisor_verify_response'] = 'Y';
												$updateParams['advisor_verify_email_date'] = $actionDate;
												$updateParams['advisor_verify_email_number'] = 0;
												$doUpdate				= TRUE;
											}
							
											// welcome email 
											$mySubject					= "CW Academy - Thank You for Registering as an Advisor";
											$myContent					= "To: $user_last_name, $user_first_name ($advisor_call_sign):<br />
																			<p>Thank you for registering as a CW Academy advisor! Your registration information:</p>
																			$advisorInfo
																			<p>You have registered to be an advisor for the following class(es):</p>
																			$classRecord
																			<p>About 20 days before the semester starts, CW Academy will begin the process of 
																			matching students to advisors. To avoid potential issues, please review your class 
																			registration information above.</p>
																			<p>Your timezone information should be set to the timezone where you live. 
																			Further, your class start time should be in local time as well. Students 
																			are given a choice of classes which have been converted from the advisor's timezone to  the 
																			student's timezone, taking Daylight Savings Time (aka Summer Time) into account.</p>
																			<p>If for some reason your situation has changed (or changes before the semester starts) or 
																			you are not able to be an advisor in $nextSemester semester, 
																			please click <a href='$siteURL/login'>CW Academy</a> to either update or delete your registration.</p>
																			<p>About 45 days before the semester 
																			starts, CW Academy will send you another email reviewing your registration information should any 
																			changes be necessary.</p>
																			<p><span style='color:red;font-size:14pt;'><b>Do not reply to this email as the address is not monitored.</b> 
																			<br />Please refer to the appropriate person at <a href='$classResolutionURL'>CWA Class 
																			Resolution</a> for assistance.</span></p></td></tr></table></p> 
																			<p>Thank you for your willingness to be an advisor!<br />
																			CW Academy</p>
																			<br /><p>CW Academy has implemented usernames and passwords in order to better protect your personal 
																			information. All requests for advisor actions will ask you to login to CW Academy where the instructions 
																			for the specific action will be displayed. Please make sure that CW Academy is 'whitelisted' in 
																			your email program.</p>
																			<p>You can, at any time, log into <a href='$siteURL/login'>CW Academy</a> and see if any actious 
																			are outstanding, modify or delete your registration, and check the status of your registration.</p>";
										} else {
											if ($doDebug) {
												echo "Advisor $advisor_call_sign has already received a welcome email<br />";
											}
										}
									} else {
										$classRecord		= "<p>No class information is available. Please update your 
																sign-up information to include at least one class or delete your registration information. To do so, 
																go to <a href='$siteURL/logim'>CW Academy</a> and select the program to update your registration. If you have questions 
																or concerns, please refer to the appropriate person at <a href='$classResolutionURL'>CWA Class 
																Resolution</a> for assistance.</p>";
									}
									$verifyOption					= FALSE;
									if ($doDebug) {
										if ($validEmailPeriod == 'Y') {
											echo "validEmailPeriod is TRUE<br />";
										} else {
											echo "validEmailPeriod is FALSE<br />";
										}
										if ($myVerifyDate) {
											echo "myVerifyDate is TRUE already verified<br />";
										} else {
											echo "myVerifyDate is FALSE not yet verified<br />";
										}
										if ($verifyOption) {
											echo "verifyOption is TRUE<br />";
										} else {
											echo "verifyOption is FALSE<br />";
										}
										if ($advisor_welcome_email_date == '') {
											echo "welcome_email_date is EMPTY<br />";
										} else {
											echo "welcome_email_date is $advisor_welcome_email_date<br />";
										}
									}
									if ($validEmailPeriod == 'Y' && $myVerifyDate == FALSE) {	/// send verify email?
										if ($doDebug) {
											echo "Setting verifyOption to TRUE<br />";
										}
										$verifyOption				= TRUE;
										if ($doDebug) { 
											$myStr					 	= "";
											if ($isNextSemester) {
												$myStr					.= "isNextSemester is TRUE; ";
											} else {
												$myStr					.= "isNextSemester is FALSE; ";
											}
											if ($advisor_welcome_email_date == '') {
												$myStr					.= "advisor welcome email date is empty; ";
												$updateParams['advisor_welcome_email_date'] = $actionDate;
												$doUpdate				= TRUE;
											} else {
												$myStr					.= "advisor welcome email date is $advisor_welcome_email_date; ";							
											}
											if ($advisor_verify_response == '') {
												$myStr					.= "advisor verify response is empty; ";
											} else {
												$myStr					.= "advisor verify response is $advisor_verify_response; ";							
											}
											echo "$myStr<br />";
										}
	
	
										if ($isNextSemester && $verifyOption && $advisor_welcome_email_date != '' && $advisor_verify_response == '') {
											if ($doDebug) {
												echo "isNextSemester and verifyOption are true, welcome email date is set and verify response is empty. Doing verify process<br />";
											}
											$verifyEmailCount++;
											$mySubject					= "CW Academy Advisor Verification";
											$myContent					= "To: $user_last_name, $user_first_name ($advisor_call_sign):<br />
																			<p>This is a confirmation email being sent about 45 days before the start 
																			of the semester. <b>No action is needed on your part UNLESS your circumstances 
																			have changed</b>. You can update your registration information by logging into  
																			<a href='$siteURL/login'>CW Academy</a> and selected the program to update 
																			your registration information.</p>
																			<p>You have registered as follows:</p>
																			$advisorInfo
																			<p>You have registered to be an advisor for the following class(es):</p>
																			$classRecord
																			<p><em>If you need to change or update any of the above information, please go to 
																			<a href='$siteURL/login'>CW Academy</a> and select the program to update your 
																			registration information.</em></p>
																			<p>If you are not able to be an advisor in the $nextSemester semester, please log into 
																			 <a href='$siteURL/login'>CW Academy</a> and select the program to update your 
																			 registration information. You can then delete your registration.</p>
																			<p>Students will be assigned to advisor classes around the 
																			10th of next month. At that time you will receive an email listing the students assigned to you. 
																			You can then let your students know who you are, what your schedule is, and ask them 
																			to confirm. CW Academy is also asking students to verify their intent to participate 
																			in $nextSemester semester's class and those who have verified will be eligible for 
																			assignment to a class. This should save you time in trying to get a confirmation.</p>
																			<p><hr></p>
																			<p><table style='border:4px solid red;'><tr><td>
																			<p><span style='color:red;font-size:14pt;'><b>PLEASE Do not reply to this email as the address is not monitored.</b> 
																			<br />Instead refer to the appropriate person at <a href='$classResolutionURL'>CWA Class 
																			Resolution</a> for assistance.</span></p></td></tr></table></p>
																			<p>Thank you for your willingness to be an advisor!
																			<br />CW Academy</p>";
	
											$sendEmail				= TRUE;
											$advisor_action_log		.= "advisor verify email sent to $advisor_email. ";
											if ($doDebug) {
												echo "setting verify response to Y. isNextSemester and verifyOption are TRUE<br />";
											}
											$updateParams['advisor_verify_response'] = 'Y';
											$updateParams['advisor_verify_email_date'] = $actionDate;
											$updateParams['advisor_verify_email_number'] = 0;
											$doUpdate				= TRUE;
											$advisorFirstCount++;
											$content				.= "ADVISOR VERIFY $advisor_call_sign Verify Email will be sent to $advisor_email<br />";
										}
									}					
									if ($doDebug) {
										if ($sendEmail) {
											echo "Checking to send email. sendEmail is TRUE. Should send an email<br />";
										} else {
											echo "<br />Checking to send email. sendEmail is FALSE. No email should be sent<br />";
										}
										if ($doUpdate) {
											echo "doUpdate is TRUE. Record should be updated<br />";
										} else {
											echo "doUpdate is FALSE. No update should be performed<br />";
										}
									}
	
									if ($sendEmail) {
										if ($testMode) {
											$myTo 		= $testEmailTo;
											$myCode		= 2;
											$mySubject 	= "TESTMODE $mySubject";
											$increment++;
											if ($myContent == '') {
												if ($doDebug) {
													echo "email content is empty<br />";
												}
											}
										} else 	{	
											$myCode		= 12;
											$myTo		= $user_email;
										}
										$mailResult 	= emailFromCWA_v2(array('theRecipient'=>$myTo,
																				'theSubject'=>$mySubject,
																				'jobname'=>$jobname,
																				'theContent'=>$myContent,
																				'mailCode'=>$myCode,
																				'increment'=>$increment,
																				'testMode'=>$testMode,
																				'doDebug'=>$doDebug));
										if ($mailResult !== TRUE) {
											$content .= "<br /><b>ERROR:</b> The email send function failed to advisor $advisor_last_name, $advisor_first_name ($advisor_call_sign).<br /><pre>";
											print_r($mailResult);
											echo "</pre><br /><br />";
											$advisorEmailErrors++;
										} else {
											$advisorEmails++;
											if ($doDebug) {
												echo "Email sent to $myTo on behalf of $advisor_call_sign ($user_email)<br >";
											}
										}
									}
									if ($doUpdate) {
										$updateParams['advisor_action_log'] = $advisor_action_log;
										if ($doDebug) {
											echo "UpdateParams:<br /><pre>";
											print_r($updateParams);
											echo "</pre><br />";
										}
										$updateResult	= $advisor_dal->update($advisor_id,$updateParams,$operatingMode);
										if ($updateResult === FALSE) {
											$content		.= "Unable to update advisorclass content for $advisorclass_call_sign (id: $advisorclass_id)<br />";
										} else {
											if ($doDebug) {
												echo "Successfully updated $advisor_call_sign record at $advisor_ID<br />";
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

	

///// all processing done. Prepare totals	
		if ($doDebug) {
			echo "<br />Sending email with the totals<br />";
		}
		if ($validEmailPeriod == "N") {
			$myString	= "Outside of the Verification Email Window.";
		} else {
			$myString	= "Within the Verification Email Window.";
		}
		$content	.= "<br /><table><tr><th colspan='2'>Counts</td></tr>
						<tr><th colspan='2'>Advisor Verify Process</td></tr>
						<tr><td style='text-align:right;'>$numARows</td><td>Total advisor records read</td></tr>
						<tr><td style='text-align:right;'>$surveyScore6</td><td>Advisor records with a survey score of 6</td></tr>
						<tr><td style='text-align:right;'>$thisSemesterAdvisors</td><td>Advisor records for $proximateSemester</td></tr>
						<tr><td style='text-align:right;'>$confirmedClasses</td><td>Confirmed advisors</td></tr>
						<tr><td style='text-align:right;'>$refusedClasses</td><td>Refused advisors</td></tr>
						<tr><td style='text-align:right;'>$unconfirmedClasses</td><td>Total unconfirmed advisors</td></tr>
						<tr><td style='text-align:right;'>$verifyEmailCount</td><td>Advisor verify emails to be sent</td></tr>
						<tr><td style='text-align:right;'>$advisorEmailErrors</td><td>Advisor emails that failed to send</td></tr>
						<tr><td style='text-align:right;'>$advisorEmails</td><td>Advisor emails actually sent</td></tr>
						<tr><td style='text-align:right;'>$futureSemesterAdvisors</td><td>Future semester advisors</td></tr>					
						<tr><td colspan='2'><hr></td></tr>
						<tr><th colspan='2'>Advisor Welcome Emails</td></tr>
						<tr><td style='text-align:right;'>$advisorWelcomeCount</td><td>Advisor Welcome emails sent</td></tr>
						<tr><td colspan='2'><hr></td></tr>
						</table><br />";
	
		$thisTime 			= date('Y-m-d H:i:s');
		$content 			.= "<br /><br /><p>V$versionNumber. Prepared at $thisTime</p>";
		$endingMicroTime 	= microtime(TRUE);
		$elapsedTime		= $endingMicroTime - $startingMicroTime;
		$elapsedTime		= number_format($elapsedTime, 4, '.', ',');
		$content			.= "<p>Report pass 0 took $elapsedTime seconds to run</p>";
		$nowDate			= date('Y-m-d');
		$nowTime			= date('H:i:s');
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
		// store the report in the reports table
		$storeResult	= storeReportData_v2('Daily Advisor Cron',$content,$testMode,$doDebug);
		if ($storeResult[0] === FALSE) {
			if ($doDebug) {
				echo "storing report failed. $storeResult[1]<br />";
			}
			$content	.= "Storing report failed. $storeResult[1]<br />";
		} else {
			$reportid	= $storeResult[2];
		}
		// store the reminder
		$effective_date		= date('Y-m-d 00:00:00');
		$closeStr			= strtotime("+ 2 days");
		$close_date			= date('Y-m-d 00:00:00',$closeStr);

		$token			= mt_rand();
		$reminder_text	= "<b>Daily Advisor Cron</b> To view the Daily Advisor Cron report for $nowDate $nowTime, click <a href='cwa-display-saved-report/?strpass=3&inp_callsign=XXXXX&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
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
			if ($doDebug) {
				echo "adding reminder failed. $reminderResult[1]<br />";
			}
		}

		$theSubject	= "CWA Daily Advisor Cron Process";
		$theContent	= "The daily advisor cron process was run at $nowDate $nowTime, Login to <a href='$siteURL/program-list'>CW Academy</a> to see the 
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
			$myStr		= "Process completed";
			return $myStr;
		} else {
			$myStr  = "<br />The final mail send function to the admins failed.<br /><br />
						<a href='$siteURL/program-list/'>Return to Portal</a></p>";
			return $myStr;
		}
	}
}
add_shortcode ('daily_advisor_cron_process', 'daily_advisor_cron_process_func');
