function daily_student_cron_func() {

/*	     	Daily Student Cron
      Function to send welcome emails to newly registered prospective students,
   Send verification emails to registered students,
   do any requested replacement students,
  
   This job is run via a cron curl job to run the associated webpage
  
   Get the Student table and for each student in the table:
  
  	check if a welcome email needs to be sent
  	  No welcome email has been sent
      and no verification email has been sent
      and there is no response then
  		  check to see if this is a duplicate registration
  			If so, send the duplicate email with bcc to Bob Carter
            else send the welcome email with a bcc to Bob Carter
  
  	check if a verification email is to be sent
      Verification emails are only sent when the initialization function
        returns 'validEmailPeriod' = Y. That happens during the periods
        2/15-3/10; 7/15-8/10;J1044
         and 11/15-12/10
      If the student has responded to a previous verification email, don't send another one
      If the request date was within 45 days of the beginning of the semester, then the
        student recently received a welcome email with the info. Don't send a verification email
      If the student is a past student and completed a class, don't send a verification email
      
    
  	Date Formats:
  		Request Date: 			Y-M-D
  		Email Sent Date: 		Y-M-D
  		Response Date:			Y-M-D
  		Selected Date:			Y-M-D 
  		Welcome Date:			Y-M-D
  		Advisor Selected Date:	Y-M-Dif ($doDebug)
  
 
*/

	global $wpdb, $testMode, $doDebug, $classesArray;

	$doDebug				= TRUE;
	$doDebugLog				= TRUE;
	$testMode				= FALSE;
	$verifyMode				= FALSE;
	$replaceMode			= FALSE;
	$debugContent			= '';
	$versionNumber			= '4';
	
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
	
	$debugLog				= "";
	

	if ($verifyMode) {
		$initializationArray['validEmailPeriod']	= 'Y';
		$initializationArray['daysToSemester']		= 45;
		$studentEmailCount							= 100;
		echo "<br /><b>Operating in VERIFY mode</b><br />";
	}
	
	if ($replaceMode) {
		$validReplacementPeriod						= 'Y';
	}


// Needed variables initialization
	$processStudent			= TRUE;
	$blankArray				= array();
	$welcomeCount 			= 0;
	$verifyCount			= 0;
	$replacedCount			= 0;
	$notReplacedCount		= 0;
	$abandonedCount			= 0;
	$badActorCount			= 0;
//	$emailCountArray		= array();
	$jobname				= "Daily Student Cron";
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
	
	$studentRegistrationURL	= "$siteURL/cwa-student-registration/";
	$checkClassURL			= "$siteURL/cwa-check-student-status/";
	$classResolutionURL		= "https://cwops.org/cwa-class-resolution/";
	$TUYesURL				= "$siteURL/cwa-thank-you-yes/";
	$TURemoveURL			= "$siteURL/cwa-thank-you-remove/";
	$advisorVerifyURL		= "$siteURL/cwa-manage-advisor-class-assignments/";
	$studentUpdateURL		= "$siteURL/cwa-display-and-update-student-signup-information/";	
	
	$recordsProcessed		= 0;
	$student45DayCount		= 0;
	$doOnce					= TRUE;
	$studentNoWelcomeDate	= 0;
	$studentInClass			= 0;
	$studentNoRequestDate	= 0;
	$studentWelcomeMatch	= 0;
	$emailErrors			= 0;
	$notEnoughTime			= 0;
	$noEmailSentYR			= 0;
	$studentHasResponded	= 0;
	$studentYes				= 0;
	$studentR				= 0;
	$email1Sent				= 0;
	$email2Sent				= 0;
	$email3Sent				= 0;
	$email4Sent				= 0;
	$prevDropped			= 0;
	$studentsSentFirstEmail	= 0;
	$numberVRecords			= 0;
	$verifyCount			= 0;
	$dupEmailsSent			= 0;
	$advisorWelcomeEmails	= 0;
	$advisorWelcomed		= 0;
	$advisorFirstCount		= 0;
	$advisorSecondCount		= 0;
	$advisorThirdCount		= 0;
	$advisorDropCount		= 0;
	$advisorEmailErrors		= 0;
	$confirmedClasses		= 0;
	$associateWelcome		= 0;
	$thisSemesterAdvisors	= 0;
	$advisorWelcomeCount	= 0;
	$refusedClasses			= 0;
	$unconfirmedClasses		= 0;
	$verifyDoOnce			= TRUE;
	$theSemester			= '';
	$studentsNoClass		= 0;
	$studentsFCFail			= 0;
	$studentsSCFail			= 0;
	$studentsTCFail			= 0;
	$outstandingRequests	= 0;
	$outstandingFulfilled	= 0;
	$outstandingNotFulfilled = 0;
	$inp_verbose		 	= 'N';
	$increment				= 0;
	$debugContent			= "";
	$classCatalog			= array();
	$semesterConvert		= array('Jan/Feb'=>'01/01','May/Jun'=>'05/01','Sep/Oct'=>'09/01');
	$fieldTest								= array('action_log','post_status','post_title','control_code');
	$update_action_log						= '';
	$deleteParam				= array();
	$semesterCountArray			= array();
	$arrayLevels				= array('fundamental','Fundamental','FUNDAMENTAL',
										'beginner','Beginner','BEGINNER',
										'intermediate','Intermediate','INTERMEDIATE',
										'advanced','Advanced','ADVANCED');
	$classesArray				= array();	// level|time|days|count|advisors
	$advisorArray				= array();
	$errorArray					= array();
	$advisorClassInc			= 0;
	$doDaysTest					= TRUE;
	
	$validStatusArray			= array('C','N','R','S','V','Y');

	$classessArray				= array();

	$matrixConvert	= array('MTM'=>'Monday, Thursday Mornings',
							'MTA'=>'Monday, Thursday Afternoons',
							'MTE'=>'Monday, Thursday Evenings',
							'TFM'=>'Tuesday, Friday Mornings',
							'TFA'=>'Tuesday, Friday Afternoons',
							'TFE'=>'Tuesday, Friday Evenings',
							'SWM'=>'Sunday, Wednesday Mornings',
							'SWA'=>'Sunday, Wednesday Afternoons',
							'SWE'=>'Sunday, Wednesday Evenings',
							'STM'=>'Sunday, Thursday Mornings',
							'STA'=>'Sunday, Thursday Afternoons',
							'STE'=>'Sunday, Thursday, Evenings',
							'WSM'=>'Wednesday, Sunday Mornings',
							'WSA'=>'Wednesday, Sunday Afternoons',
							'WSE'=>'Wednesday, Sunday Evenings',
							'None'=>'None');


	if ($doDebug) {
		if ($testMode) {
			echo "<b>OPERATING IN TEST MODE</b><br /><br />";
		} else {
			echo "<b>Operating in Production Mode</b><br /><br />";
		}
	}

	
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}


	
	$content = "";


/* 	function to check classes
	input: the student level followed by the three class choices
	Returns: an array of the three formatted class choices and logical haveClassMatch
		which if TRUE, at least one of the class choices matches the catalog
		or if FALSE, none of the class choices match the catalog
*/

	function checkClasses($inp_level,$inp_choice1,$inp_choice2,$inp_choice3) {
	
		global $classesArray, $doDebug;
		
		$returnArray			= array();
		$haveClassMatch			= FALSE;
		$choice1				= "";
		$choice2				= "";
		$choice3				= "";

	
		if ($doDebug) {
			echo "<br />At checkClasses with $inp_level,$inp_choice1,$inp_choice2,$inp_choice3<br />";
		}
		for ($ii=1;$ii<=3;$ii++) {
			$thisClass			= ${'inp_choice' . $ii};
			if ($doDebug) {
				echo "Checking inp_choice$ii which has a value of $thisClass<br />";
			}
			if ($thisClass == '' || $thisClass == 'None') {
				${'choice' . $ii} 	= "None made";
			} else {
				$myArray		= explode(" ",$thisClass);
				$thisTime		= $myArray[0];
				$thisDays		= $myArray[1];
				$thisValue		= "$inp_level|$thisTime|$thisDays";
				if ($doDebug) {
					echo "looking in classesArray for $thisValue<br />";
				}
				if (array_key_exists($thisValue,$classesArray)) {
					${'choice' . $ii} 	= "";
					$haveClassMatch		= TRUE;
					if ($doDebug) {
						echo "found a match<br />";
					}
				} else {
					${'choice' . $ii} 	= "(not in current catalog)";
					if ($doDebug) {
						echo "No match<br />";
					}
				}
			}
		}
		$returnArray					= array($choice1,$choice2,$choice3,$haveClassMatch);
		if ($doDebug) {
			echo "returnArray:<br /><pre>";
			print_r($returnArray);
			echo "</pre><br />";
		}
		return $returnArray;
	}
	
	///////////////// end of checkClasses function

// $validEmailPeriod = 'Y';

	$runTheJob				= TRUE;
////// see if this is the time to actually run
	if ($doDebug) {
		echo "<br />starting<br />";
	}
		
	if ($userName != '') {
		$content 			.= "<h3>$jobname Executed by $userName</h3>";
	} else {
		$content			.= "<h3>$jobname Process Automatically Executed</h3>";
		$userName			= "CRON";
		$runTheJob			= allow_job_to_run($doDebug);
	}

	
// $runTheJob = TRUE;	
	if ($runTheJob) {
		if ($testMode) {
			$audioAssessmentTableName = 'wpw1_cwa_audio_assessment2';
			$replacementRequests	= 'wpw1_cwa_replacement_requests2';
			$userMasterTableName	= 'wpw1_cwa_user_master2';
			$content .= "<p><strong>Function is under development.</strong></p>";
			$xmode					= 'tm';
			$inp_mode				= 'TESTMODE';
			$catalogMode			= 'TestMode';
			$operatingMode			= 'Testmode';
		} else {
			$audioAssessmentTableName = 'wpw1_cwa_audio_assessment';
			$replacementRequests	= 'wpw1_cwa_replacement_requests';
			$userMasterTableName	= 'wpw1_cwa_user_master';
			$xmode					= 'pd';
			$inp_mode				= 'Production';
			$catalogMode			= 'Production';
			$operatingMode			= 'Production';
		}
		
		$advisor_dal = new CWA_Advisor_DAL();
		$advisorclass_dal = new CWA_Advisorclass_DAL();
		$student_dal = new CWA_Student_DAL();
		$user_dal = new CWA_User_Master_DAL();
		
		
		if ($doDebug) {
			// dump the date information
			echo "<p><b>Operation Criteria:</b><br />
					todaysDate: $todaysDate<br />
					currentTimestamp: $currentTimestamp<br />
					theSemester: $theSemester<br />
					prevSemester: $prevSemester<br />
					currentSemester: $currentSemester<br />
					nextSemester: $nextSemester<br />
					validEmailPeriod: $validEmailPeriod<br />
					validReplacementPeriod: $validReplacementPeriod</p>";
		}
		$validSemesters				= array();
		if ($currentSemester != 'Not in Session') {
			$validSemesters[]		= $currentSemester;
		}
		$validSemesters[]			= $nextSemester;
		$validSemesters[]			= $semesterTwo;
		$validSemesters[]			= $semesterThree;
		$validSemesters[]			= $semesterFour;

/*
		//// get the class catalog and make it ready to check class choices
		if ($doDebug) {
			echo "<br /><b>Catalog</b> Loading the catalog<br />";
		}
		$catalogArray			= generateCatalog($theSemester);
		if ($catalogArray[0] === FALSE) {
			$myStr				= $catalogArray[1];
			$content			.= "<p>No catalog available: $myStr</p>";
		} else {
			foreach($catalogArray as $thisValue) {
				$myArray		= explode("|",$thisValue);
				$thisLevel		= $myArray[0];
				$thisLanguage	= $myArray[1];
				$thisTime		= $myArray[2];
				$thisDays		= $myArray[3];
				$thisCount		= $myArray[4];
				$thisAdvisors	= $myArray[5];
				$classesArray["$thisLevel|$thisLanguage|$thisTime|$thisDays"]	= "$thisCount|$thisAdvisors";
			}
		}
		if ($doDebug) {
			echo "<br />classesArray:<br /><pre>";
			print_r($classesArray);
			echo "</pre><br />";
		}
		
		if ($doDebug) {
			echo "Catalog ... catalog loaded<br >";
		}
		//// classesArray loaded
*/

////////////////	start student process



		if ($doDebug) {
			echo "<br /><b>STUDENT</b> Starting Student Process<br />";
		}
		
		$content				.= "<h4>Processing Student Records</h4>";
		$myDate = date('Y-m-d', $currentTimestamp);
		$myCount				= 0;
		$prevCallSign			= "";
		$prevSemester			= "";
		$addReminder			= FALSE;
		$close_date				= '';

		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				// field1 = $value1
				['field' => 'student_call_sign', 'value' => '', 'compare' => '!='],
				
				// (field2 = $value2 OR field2 = $value3)
				[
					'relation' => 'OR',
					'clauses' => [
						['field' => 'student_semester', 'value' => $currentSemester, 'compare' => '='],
						['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '='],
						['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '='],
						['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '='],
						['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '=']
					]
				]
			]
		];
		$requestInfo = array('criteria' => $criteria,
							 'orderby' => 'student_call_sign',
							 'order' => 'ASC');
		$orderby = 'student_call_sign';
		$order = 'ASC';
		$studentData = get_student_and_user_master('', 'complex', $requestInfo, $operatingMode, $doDebug);
		if ($studentData === FALSE) {
			if ($doDebug) {
				echo "get_student_and_user_master for complex criteria returned FALSE<br />";
			}
		} else {
			if (! empty($studentData)) {
				$studentRecordsFound = count($studentData);
				foreach($studentData as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					echo "<br />Processing $student_call_sign<br />";
					
					if ($student_call_sign == $prevCallSign && $student_semester == $prevSemester) {		/// duplicate!!
						// notify Roland, don't process the duplicate
						sendErrorEmail("Student Cron: callsign $student_call_sign has a duplicate");
					} else {
						$prevCallSign			= $student_call_sign;
						$prevSemester			= $student_semester;
						$recordsProcessed++;
					
						if ($student_intervention_required  == 'H') {
							if ($student_hold_reason_code == 'B') {
								echo "student is a bad actor and is on hold<br />";
								$badActorCount++;
								$content		.= "Student $user_last_name, $user_first_name (<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>) is on hold as a bad actor<br />";
							} else {
								if ($student_hold_reason_code != 'X' && $student_hold_reason_code != 'N') {
									$content		.= "Student $user_last_name, $user_first_name (<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>) is on hold<br />";
								}
							}
						} else {
					
							if ($student_timezone_offset == -99.0 || $user_timezone_id == '' || $user_timezone_id == 'Unknown') {
								$errorMsg	= "student $student_call_sign has a timezone issue.<br />timezone_id: $user_timezone_id<br />timezone_offset: $student_timezone_offset. Abandoned: $student_abandoned";
								sendErrorEmail($errorMsg);
							}
							$myInt						= strpos($user_timezone_id,'zip');
							if ($myInt !== FALSE) {
								$errorMsg	= "student $student_call_sign has a zipTimeZone issue.<br />timezone_id: $user_timezone_id<br />timezone_offset: $student_timezone_offset. Abandoned: $student_abandoned";
								sendErrorEmail($errorMsg);
							}
							if ($studentEmailCount > 0) {
		
		
								if ($student_request_date != '') {
									$unix_request_date		= strtotime($student_request_date);
								}
								if ($student_welcome_date != '') {
									$unix_welcome_date		= strtotime($student_welcome_date);
								}
								if ($student_email_sent_date != '') {
									$unix_email_sent_date	= strtotime($student_email_sent_date);
								}
								if ($student_response_date != '') {
									$unix_response_date		= strtotime($student_response_date);
								}
								if ($student_first_class_choice == '' || $student_first_class_choice == 'None') {
									$student_first_class_choice			= 'None';
									$student_first_class_choice_utc		= 'None';
								}
								if ($student_second_class_choice == '' || $student_second_class_choice == 'None') {
									$student_second_class_choice		= 'None';
									$student_second_class_choice_utc	= 'None';
								}
								if ($student_third_class_choice == '' || $student_third_class_choice == 'None') {
									$student_third_class_choice			= 'None';
									$student_third_class_choice_utc		= 'None';
								}
								$updateData					= array();
								$update_action_log			= '';
							
								// handle verify mode
								$daysToSemester				= days_to_semester($student_semester);
	//							if ($verifyMode) {			
	//								if ($student_semester == $nextSemester) {
	//									$daysToSemester		= $initializationArray['daysToSemester'];
	//								}				
	//							}
								if ($doDebug) {
									echo "<br />Processing student $student_call_sign (ID: $student_id). Data Read:<br />
										  &nbsp;&nbsp;&nbsp;Semester: $student_semester<br />
										  &nbsp;&nbsp;&nbsp;daysToSemester: $daysToSemester<br />
										  &nbsp;&nbsp;&nbsp;Level: $student_level<br />
										  &nbsp;&nbsp;&nbsp;Language: $student_class_language<br />
										  &nbsp;&nbsp;&nbsp;Time Zone: $student_timezone_offset<br />
										  &nbsp;&nbsp;&nbsp;Request Date: $student_request_date<br />
										  &nbsp;&nbsp;&nbsp;Class Priority: $student_class_priority<br />
										  &nbsp;&nbsp;&nbsp;Response: $student_response<br />
										  &nbsp;&nbsp;&nbsp;Abandoned: $student_abandoned<br />
										  &nbsp;&nbsp;&nbsp;Welcome Date: $student_welcome_date<br />
										  &nbsp;&nbsp;&nbsp;Email Date: $student_email_sent_date<br />
										  &nbsp;&nbsp;&nbsp;Email Number: $student_email_number<br />
										  &nbsp;&nbsp;&nbsp;Student Status: $student_status<br />
										  &nbsp;&nbsp;&nbsp;Assigned Advisor: $student_assigned_advisor<br />
										  &nbsp;&nbsp;&nbsp;First Class Choice: $student_first_class_choice<br />
										  &nbsp;&nbsp;&nbsp;Second Class Choice: $student_second_class_choice<br />
										  &nbsp;&nbsp;&nbsp;Third Class Choice: $student_third_class_choice<br />
										  &nbsp;&nbsp;&nbsp;First Class Choice UTC: $student_first_class_choice_utc<br />
										  &nbsp;&nbsp;&nbsp;Second Class Choice UTC: $student_second_class_choice_utc<br />
										  &nbsp;&nbsp;&nbsp;Third Class Choice UTC: $student_third_class_choice_utc<br />
										  &nbsp;&nbsp;&nbsp;No Catalog: $student_no_catalog<br />
										  &nbsp;&nbsp;&nbsp;Catalog Options: $student_catalog_options<br />
										  &nbsp;&nbsp;&nbsp;Flexible: $student_flexible";
								}
	
								/// setup the info to pass to student sign-up
								$passPhone		= substr($user_phone,-5,5);
								$statusString	= "inp_callsign=$student_call_sign&inp_email=$user_email&inp_phone=$user_phone&inp_level=$student_level&testMode=$testMode&verifyMode=$verifyMode";
								$encstr			= base64_encode($statusString);
		
								// set up some logicals
								$doUpdateStudent		= FALSE;			
								$myWelcomeTest 			= FALSE;
								$student_responseTest 	= FALSE;
	
								////////	set up the UTC class choices if these fields are empty
								if ($student_first_class_choice_utc == '') {
									if ($student_first_class_choice != '' && $student_first_class_choice != 'None') {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;converting first class choice of $student_first_class_choice to UTC<br />";
										}
										$myArray			= explode(" ",$student_first_class_choice);
										$thisTime			= $myArray[0];
										$thisDay			= $myArray[1];
										$result				= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
										if ($result[0] == 'FAIL') {
											if ($doDebug) {
												echo "utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
																  Error: $result[3]<br />";
											}
										} else {
											$utcTimes			= $result[1];
											$utcDays			= $result[2];
											$thisStr			= "$utcTimes $utcDays";
											$updateData['student_first_class_choice_utc'] = $thisStr;
											$doUpdateStudent	= TRUE;
										}
									}
								}
								if ($student_second_class_choice_utc == '') {
									if ($student_second_class_choice != '' && $student_second_class_choice != 'None') {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;converting second class choice of $student_second_class_choice to UTC<br />";
										}
										$myArray			= explode(" ",$student_second_class_choice);
										$thisTime			= $myArray[0];
										$thisDay			= $myArray[1];
										$result				= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
										if ($result[0] == 'FAIL') {
											if ($doDebug) {
												echo "utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
																  Error: $result[3]<br />";
											}
										} else {
											$utcTimes			= $result[1];
											$utcDays			= $result[2];
											$thisStr			= "$utcTimes $utcDays";
											$updateData['student_second_class_choice_utc'] = $thisStr;
											$doUpdateStudent		= TRUE;
										}
									}
								}
								if ($student_third_class_choice_utc == '') {
									if ($student_third_class_choice != '' && $student_third_class_choice != 'None') {
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;converting third class choice of $student_third_class_choice to UTC<br />";
										}
										$myArray			= explode(" ",$student_third_class_choice);
										$thisTime			= $myArray[0];
										$thisDay			= $myArray[1];
										$result				= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
										if ($result[0] == 'FAIL') {
											if ($doDebug) {
												echo "utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
																Error: $result[3]<br />";
											}
										} else {
											$utcTimes			= $result[1];
											$utcDays			= $result[2];
											$thisStr			= "$utcTimes $utcDays";
											$updateData['student_third_class_choice_utc'] = $thisStr;
											$doUpdateStudent		= TRUE;
										}
									}
								}
	
	
	
								// First set of work is to send a welcome email
								if ($doDebug) {
									echo "<br />Welcome Email Process<br />";
								}
					
								// Check if welcome email has been sent
								if ($student_welcome_date == "" || $student_welcome_date == " " || $student_welcome_date == "0000-00-00") {
									$myWelcomeTest = TRUE;				// welcome email has not been sent
									if ($user_email == '') {
										$content	.= "Email address for call sign <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a> at id $student_id missing. No welcome email sent.<br /><br />";
										$myWelcomeTest = FALSE;
										if ($doDebug) {
											echo "No email address. No welcome email will be sent<br />";
										}
									}
									if (!in_array($student_level,$arrayLevels)) {
										$content	.= "Level of $student_level for call sign <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a> missing or invalid. No welcome email sent.<br /><br />";
										$myWelcomeTest	= FALSE;
										if ($doDebug) {
											echo "No level for the student. No welcome email will be sent<br />";
										}
									}
									if ($student_status != '') {	// student has been assigned already
										$myWelcomeTest = FALSE;
									}
								}
								if ($myWelcomeTest) {
									if ($doDebug) {
										echo "Welcome email for call sign $student_call_sign requested on $student_request_date<br />with a response of $student_response and a welcome of $student_welcome_date<br>";
									}
	
									if ($student_abandoned == 'Y') {			// send abandoned email only
										$emailContent = "To: $user_last_name, $user_first_name ($student_call_sign):
														 <p>You registered for a CW Academy $student_level Level class but did not 
														 make any class date and time preference choices. In order to be eligible 
														 for possible assignment to an advisor's class, you must select your 
														 preferred class dates and times.<p>
														 <p>Please log into <a href='$siteURL/login'>CW Academy</a> and follow the instructions 
														 there.</p>";
										$abandonedCount++;
										if ($doDebug) {
											echo "adding reminder to reminders table<br />";
										}
										$token			= mt_rand();
										$reminder_text	= "<b>Select Class Schedule Preferences:</b> You need to update your registration information and identify your class preferences.</p>
<p>Click on <a href='$siteURL/cwa-student-registration/?token=$token'>Student Signup</a> 
															and select option three.</p>";
										// add the reminder
										$effective_date		= date('Y-m-d H:i:s');
										$closeStr			= strtotime("+20 days");
										$close_date			= date('Y-m-d H:i:s', $closeStr);
										$inputParams		= array("effective_date|$effective_date|s",
																	"close_date|$close_date|s",
																	"resolved_date||s",
																	"send_reminder|N|s",
																	"send_once|Y|s",
																	"call_sign|$student_call_sign|s",
																	"role||s",
																	"email_text||s",
																	"reminder_text|$reminder_text|s",
																	"resolved|N|s",
																	"token|$token|s");
										$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
										if ($insertResult[0] === FALSE) {
											if ($doDebug) {
												echo "inserting reminder failed: $insertResult[1]<br />";
											}
											$content		.= "Inserting reminder failed: $insertResult[1]<br />";
										}
	
	
	
										$updateData['student_welcome_date'] = $checkDate;
										$student_welcome_date		= $checkDate;
										$update_action_log			.= "ABANDONED email sent to student ";
										$doUpdateStudent			= TRUE;
										if ($doDebug) {
											echo "Student abandoned the registration. Sending abandoned email. Setting welcome date to $checkDate and action log to $update_action_log<br />";
										}
						
									} else {
										if ($student_waiting_list == 'Y') {
											$waiting		= 'on the waiting list';
										} else {
											$waiting		= 'registered';
										}
										$option1		= "<br />&nbsp;&nbsp;&nbsp;Your first class choice: $student_first_class_choice
															<br />&nbsp;&nbsp;&nbsp;Your second class choice: $student_second_class_choice
															<br />&nbsp;&nbsp;&nbsp;Your third class choice: $student_third_class_choice</p>";
										if ($student_no_catalog == 'Y') {
											if ($doDebug) {
												echo "no_catalog is Y<br />";
											}
											if ($student_flexible == 'Y') {
												if ($doDebug) {
													echo "flexible is Y<br />";
												}
												$option1	= "<br />&nbsp;&nbsp;&nbsp;My schedule is flexible</p>";
											} else {
												if ($student_catalog_options != '') {
													if ($doDebug) {
														echo "catalog options; $student_catalog_options<br />";
													}
													$option1		= "";
													$myArray		= explode(",",$student_catalog_options);
													foreach($myArray as $thisValue) {
														$myStr		= $matrixConvert[$thisValue];
														$option1	.= "<br />&nbsp;&nbsp;&nbsp;$myStr";
													}
													$option1		.= "</p>";
												} else {
													if ($doDebug) {
														echo "down to nothing selected<br />";
													}
													$option1		= "<br />&nbsp;&nbsp;&nbsp;Nothing Selected</p>";
												}
											}
										}
	
										$emailContent = "To: $user_last_name, $user_first_name ($student_call_sign):<br />
														<p>Welcome to the CW Academy and thank you for your student registration!  You are currently $waiting for:
														<br />&nbsp;&nbsp;&nbsp;$student_level CW class
														<br />&nbsp;&nbsp;&nbsp;For a class taught in $student_class_language 
														<br />&nbsp;&nbsp;&nbsp;For the $student_semester semester
														<br />Classes are one hour in length and their starting time will be within the time block indicated. 
														The class times and days you selected are (all dates and times in your local time):
														$option1
														<p>If you need to change any of your sign-up information, please log into 
														<a href='$siteURL/login'>CW Academy</a> and follow the directions for updating your registration 
														information.</p>
														<p>You can check the status of your registration at any time by logging into 
														<a href='$siteURL/login'>CW Academy</a> and running Check Student Status</p>
														<p>Please <b>save</b> this email in case you need to change something in the future....</p>";
										if (($student_semester == $nextSemester && $daysToSemester > 45) || ($student_semester != $nextSemester)) {
											$emailContent	.= "<p>About six weeks before the semester 
																starts, CW Academy will send you an email asking you to verify and update your class preferences.</p>  
																<p>In order to be considered for assignment to a class, you must follow the instructions 
																in that email. PLEASE KEEP AN EYE 
																ON YOUR SPAM/TRASH FILE IF YOU THINK YOU SHOULD HAVE RECEIVED IT BY THEN.</p>";
										}
						
										if ($daysToSemester <= 45 && $daysToSemester >= 22) {
											$emailContent	.= "<p>About 20 days before the semester starts, 
																CW Academy will begin the process of matching students to advisors. The assignment will be based on your registration 
																information, the available advisor classes, and whether there is room 
																in the class you desired. Regardless of whether you were selected or not, you will be 
																notified shortly thereafter.</p>";
										}
										if ($student_waiting_list == 'Y' && $student_abandoned != 'Y') {
											$emailContent		.= "<table style='border:4px solid red;'><tr><td>
																	<p>Student assignment to classes has already occurred for the $nextSemester semester. You 
																	have been placed on a waiting list. Students do drop 
																	out and CW Academy pulls replacement students from the waiting list. If you aren't selected 
																	from the waiting list, your registration will be automatically moved to the next 
																	semester and you will be given heightened priority for assignment to a class.</p></td></tr></table>";
										}
										$emailContent		.= "<p>CW Academy requires all potential students meet the following requirements:
																<ol>
																<li> A serious commitment of 60 minutes of daily practice</li>
																<li> Your availability to meet online for approximately 60 minutes, twice a week for 8 weeks</li>
																<li> Have a working paddle for sending Morse Code. Not a Straight Key or a Bug</li>
																<li> Have a keyer or radio that the advisor and other students can hear via computer audio</li>
																<li> A high-speed internet connection that will allow you to 
																participate in classes held via Zoom or another online meeting program
																<li> Access to a computer (tablets are strongly discouraged!)</p>
																</ol></p>
																<p>The philosophy of CW Academy is to teach CW at 25 wpm character speed, reinforcing 
																copying the code by sounds rather than by counting dits and dahs.  Spacing between characters (Farnsworth method) 
																may be used to slow the overall CW down to a manageable speed.</p>
																<p>CW Academy has an automated process to assign students to advisor classes. Previous students 
																are given priority. All other students are assigned in order by their sign-up date, after they 
																have completed their current class. With nearly a thousand 
																student sign-ups each semester, the demand exceeds the supply. Special requests are difficult to 
																honor and may delay getting you into the upcoming semester.</p>
																<table style='border:4px solid red;'><tr><td>
																<p>If you have questions or concerns, PLEASE do not reply to this email as the address is not monitored. 
																Instead, refer to the appropriate person at <a href='$classResolutionURL'>CWA Class 
																Resolution</a> for assistance.</p></td></tr></table>
																<p>73,
																<br />CW Academy</p>";
										$update_action_log			.= "WELCOME Welcome email sent to student ";
										$updateData['student_welcome_date'] = $checkDate;
										$student_welcome_date		= $checkDate;
										$doUpdateStudent				= TRUE;
							
										/// if in the verification period (validEmailPeriod is Y), verify the student
										if ($validEmailPeriod == 'Y' || $daysToSemester < 30) {
											$update_action_log		.= "Student automaticially verified ";
											$updateData['student_response'] = 'Y';
											$updateData['student_response_date'] = $checkDate;
											$updateData['student_email_sent_date'] = $checkDate;
											$updateData['student_email_number'] = 0;
											$student_response		= 'Y';
											$student_email_sent_date	= $checkDate;
											$student_email_number	= 0;
											$doUpdateStudent = TRUE;
											$student45DayCount++;
										}
									}
									$theSubject			= "CW Academy -- Thank You for Your Application";
									if ($testMode) {
										$theRecipient	= 'rolandksmith@gmail.com';
										$theSubject 	= "TESTMODE $theSubject";
										$mailCode		= 2;
										$increment++;
									} else {
										$theRecipient	= $user_email;
										$mailCode		= 14;
									}
								
									// is the email in the emailContent the same as the current student
								
								
									$mailResult			= emailFromCWA_v3(array('theRecipient'=>$theRecipient,
																			  'theSubject'=>$theSubject,
																			  'jobname'=>$jobname,
																			  'theContent'=>$emailContent,
																			  'mailCode'=>$mailCode,
																			  'testMode'=>$testMode,
																			  'increment'=>$increment,
																			  'doDebug'=>$doDebug));
									if ($mailResult[0] === TRUE) {
										$content 	.= "WELCOME An email was sent to <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
											target='_blank'>$student_call_sign</a> ($student_level $student_semester) at email address <a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_email=$theRecipient&strpass=2' 
											target='_blank'>$theRecipient</a><br />";
										if ($doDebug) {
											echo $mailResult[1];
											echo "Welcome email sent to $theRecipient<br />";
										}
										$studentEmailCount--;
										$welcomeCount++;
									} else {
										$content .= "The Welcome email send function failed. Student: <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>; email: $theRecipient<br />";
										if ($doDebug) {
											echo $mailResult[1];
											echo "The email send function failed<br />";
										}
									}
								} else {
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp;No Welcome Email needed<br />";
									}
								}				/// go on to do the verification email	
	
			
	
	
//	Starting verification email process
	
								if ($doDebug) {
									echo "<br />Verification Process<br />";
								}
								if ($validEmailPeriod != "N") {
									if ($doDebug) {
										echo "validEmailPeriod is $validEmailPeriod<br />";
									}
									$semesterArray				= explode(" ","$nextSemester");
									$partZero					= $semesterArray[0];
									$partOne					= $semesterArray[1];
									$newPartOne					= $semesterConvert[$partOne];
									$myNewSemester				= "$partZero/$newPartOne";
									if ($student_semester == $proximateSemester) {
										$numberVRecords++;
										$processStudent		= TRUE;
										/// setup the info to pass to student sign-up
										$passPhone				= substr($user_phone,-5,5);
										$stringToPass			= "inp_callsign=$student_call_sign&inp_phone=$passPhone&inp_email=$user_email&inp_mode=$inp_mode&strPass=2&inp_verbose=$inp_verbose&inp_verify=Y&verifyMode=$verifyMode";
										$enstrVerify			= base64_encode($stringToPass);
										$stringToPass			= "inp_callsign=$student_call_sign&inp_phone=$passPhone&inp_email=$user_email&inp_mode=$inp_mode&strPass=2&inp_verbose=$inp_verbose";
										$enstrNoVerify			= base64_encode($stringToPass);
	
/*	Check the student class choices
	If no class choices or none of the class choices matches an available class
	set the classChoiceFail to true. The student, depending on other factors
	should get a verification email
	
	Three situations are being handled:
	1.	Student has made class choices and at least one of the choices matches an available class
	2.	Student has made class choices and none of choices match an available class
	3.	Student class choices are empty
*/
	
										if ($student_response == "Y" || $student_response == "R") {
											$studentHasResponded++;
											$processStudent	= FALSE;
											if ($doDebug) {
												echo "Student has responded $student_response<br />";
											}
											if ($student_response == "Y") {
												$studentYes++;
											} else {
												$studentR++;
											}
/*											
											if ($student_response == 'Y' and $student_first_class_choice == 'None') {
												// student has responded but not made a class choice. Send an email!
												// set up the reminder and send the email
												$returnArray		= wp_to_local($user_timezone_id, 0, 5);
												if ($returnArray === FALSE) {
													if ($doDebug) {
														echo "called wp_to_local with $user_timezone_id, 0, 5 which returned FALSE<br />";
													} else {
														sendErrorEmail("$jobname calling wp_to_local with $user_timezone_id, 0, 5 returned FALSE");
													}
													$effective_date		= date('Y-m-d 00:00:00');
													$closeStr			= strtotime("+ 5 days");
													$close_date			= date('Y-m-d 00:00:00',$closeStr);
												} else {
													$effective_date		= $returnArray['effective'];
													$close_date			= $returnArray['expiration'];
												}
											
												$token				= mt_rand();
												$reminder_text		= "<b>Select Class Schedule Preferences:</b> You have responded 
that you are available to take a class in the $student_semester semester. HOWEVER, you 
have not made any class schedule preference choices. Unless you make a class schedule 
choice, you will not be assigned to a class. To make class schedule preference choices, 
click on 'Student Signup' below and then click on 'Update Registration'.";
												$inputParams		= array("effective_date|$effective_date|s",
																			"close_date|$close_date|s",
																			"resolved_date||s",
																			"send_reminder|N|s",
																			"send_once|Y|s",
																			"call_sign|$student_call_sign|s",
																			"role||s",
																			"email_text||s",
																			"reminder_text|$reminder_text|s",
																			"resolved|N|s",
																			"token|$token|s");
												$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
												if ($insertResult[0] === FALSE) {
													if ($doDebug) {
														echo "inserting class choice update reminder failed: $insertResult[1]<br />";
													}
													$content		.= "Inserting class choice reminder failed: $insertResult[1]<br />";
												}
											
											
												$theSubject				= "CW Academy -- Missing Class Schedule Preferences";
												$emailContent			= "You have responded 
that you are available to take a class in the $student_semester semester. HOWEVER, you 
have not made any class schedule preference choices. Unless you make a class schedule 
choice, you will not be assigned to a class. To make class schedule preference choices, 
go to <a href='$siteURL/program-list/'>CW Academy</a> and follow the instructions 
under 'Reminders and Actions Requested'.";
												if ($testMode) {
													$theRecipient		= 'rolandksmith@gmail.com';
													$theSubject			= "TESTMODE $theSubject";
													$mailCode			= 2;
													$increment++;
												} else {
													$theRecipient		= $user_email;
													$mailCode			= 13;
												}
												$mailResult			= emailFromCWA_v3(array('theRecipient'=>$theRecipient,
																							'theSubject'=>$theSubject,
																							'jobname'=>$jobname,
																							'theContent'=>$emailContent,
																							'mailCode'=>$mailCode,
																							'testMode'=>$testMode,
																							'increment'=>$increment,
																							'doDebug'=>TRUE));
												// $mailResult = TRUE;
												if ($mailResult[0] === TRUE) {
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;A class choice missing email was sent to $theRecipient ($student_level)<br />";
													}
												} else {
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;Sending class choice missing email failed to $theRecipient<br />";
													}
												}
											}
*/
										} 
										if ($student_email_number == 4 && $student_response == '') {
											$prevDropped++;
											$processStudent = FALSE;
											if ($doDebug) {
												echo "Student has been dropped<br />";
											}
											// echo "Dropped: $student_call_sign<br />";
										}
										if ($student_welcome_date != '') {
											if ($processStudent) {
												if ($doDebug) {
													echo "Student should get a verify email ... depending
														  <br />&nbsp;&nbsp;&nbsp;Call Sign: $student_call_sign
														  <br />&nbsp;&nbsp;&nbsp;Name: $user_last_name, $user_first_name
														  <br />&nbsp;&nbsp;&nbsp;Email: $user_email
														  <br />&nbsp;&nbsp;&nbsp;Level: $student_level
														  <br />&nbsp;&nbsp;&nbsp;Language: $student_class_language
														  <br />&nbsp;&nbsp;&nbsp;TZ: $student_timezone_offset
														  <br />&nbsp;&nbsp;&nbsp;Semester: $student_semester 
														  <br />&nbsp;&nbsp;&nbsp;Abandoned: $student_abandoned
														  <br />&nbsp;&nbsp;&nbsp;response: $student_response 	
														  <br />&nbsp;&nbsp;&nbsp;request date: $student_request_date 
														  <br />&nbsp;&nbsp;&nbsp;welcome_date: $student_welcome_date
														  <br />&nbsp;&nbsp;&nbsp;Verify email date: $student_email_sent_date
														  <br />&nbsp;&nbsp;&nbsp;Verify email number; $student_email_number
														  <br />&nbsp;&nbsp;&nbsp;First Class Choice UTC: $student_first_class_choice_utc
														  <br />&nbsp;&nbsp;&nbsp;Second Class Choice UTC: $student_second_class_choice_utc
														  <br />&nbsp;&nbsp;&nbsp;Third Class Choice UTC: $student_third_class_choice_utc
														  <br />";
												}
						
/* 
	At this point, student is to be processed
	IF the _email_sent_date has not been sent
	THEN start the verify process
	else (verify email has been sent)
	IF Email number > 0
		THEN continue the verify process
*/							
												if ($processStudent) {
													if ($doDebug) {
														echo "&nbsp;&nbsp;&nbsp;processStudent is true and email_number is $student_email_number<br />";
													}
/*
	The prospective student qualifies for a verification email.
	
	When email_number is 0 or blank that means no initial confirmation email has been sent. 
	Send the confirmation email. set the email_sent_date to today, set the email message 
	subject, and set email_number to 1
	
	When email_number is 1 that means an initial confirmation email has been sent. If response is not 
	Y nor R and three days (or more) have passed since the initial confirmation email has been sent then 
	set the subject to Second Attempt, send the confirmation email again and set email_number to 2. 
	
	When email_number is 2 that means an initial confirmation email and a followup email has been sent.
	If response is not Y nor R, and six days have passed, set the subject to Third Attempt, 
	send the confirmation email again and and set email_number to 3.
	
	When email_number is 3 that means the initial email and two followups have been sent. 
	If response is not Y nor R and ten days have passed, set up the No Response email,
	set email_number to 4, and send the no Response email.
*/ 	
	
													$sendEmail 		= FALSE;
													$setReminder	= FALSE;
													$finalNotice	= "";
													switch($student_email_number) {
														case 0:
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Going through case 0<br />";
															}
															if ($student_request_date == "") {
																$student_request_date 		= $todaysDate;
																if ($doDebug) {
																	echo "&nbsp;&nbsp;&nbsp;Request date is empty. Have set the request date to $todaysDate<br />";
																}
																$updateData['student_request_date'] = $todaysDate;
																$doUpdateStudent				= TRUE;
																$studentNoRequestDate++;
															}
															$theSubject 					= "ACTION REQUIRED: CWAcademy Morse Code Class Verification";
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Send email and set email_sent_date to $todaysDate and email_number to 1<br />";
															}
															$update_action_log					.= "VERIFY Set email_number to 1 and sent verification email ";
															$updateData['student_email_sent_date'] = $todaysDate;
															$updateData['student_email_number'] = 1;
															$doUpdateStudent						= TRUE;
															$email1Sent++;
															$studentsSentFirstEmail++;
															$content							.= "VERIFY Student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																target='_blank'>$student_call_sign</a> ($student_level $student_semester) was sent the first 
																<a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_callsign=$student_call_sign&strpass=2' target= '_blank'>verification email</a>.<br />";
															$sendEmail							= TRUE;
															$setReminder						= TRUE;
															break;
														case 1:
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Going through case 1<br />";
															}
															$testDate 							= strtotime("$student_email_sent_date + 3 days");
															if ($currentTimestamp >= $testDate) {
																$sendEmail 						= TRUE;
																$theSubject 					= "ACTION REQUIRED: CWAcademy Morse Code Class Application - Second Attempt";
																if ($doDebug) {
																	echo "VERIFY $student_call_sign send email and set email_number to 2<br />";
																}
																$update_action_log				.= "VERIFY Set email_number to 2 and sent verification email ";
																$updateData['student_email_number'] = 2;
																$doUpdateStudent				= TRUE;
																$setReminder					= TRUE;
																$email2Sent++;
																$content						.= "VERIFY Student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																	target='_blank'>$student_call_sign</a> ($student_level $student_semester) was sent the second 
																	<a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_callsign=$student_call_sign&strpass=2' target= '_blank'>verification email</a>.<br />";
															} else {
																// $content .= "&nbsp;&nbsp;&nbsp;Not enough time has passed between $student_email_sent_date and $todaysDate. No email sent<br />";
																$notEnoughTime++;
															}
															break;
														case 2:
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Going through case 2<br />";
															}
															$sendEmail							= FALSE;
															$testDate 							= strtotime("$student_email_sent_date + 6 days");
															if ($currentTimestamp >= $testDate) {
																$sendEmail 						= TRUE;
																$theSubject 					= "ACTION REQUIRED: CWAcademy Morse Code Class Application - Third Attempt";
																if ($doDebug) {
																	echo "$student_call_sign send email and set email_number to 3<br />";
																}
																$update_action_log				.= "VERIFY Set email_number to 3 and sent verification email ";
																$updateData['student_email_number'] = 3;
																$doUpdateStudent				= TRUE;
																$setReminder					= TRUE;
																$email3Sent++;
																$content						.= "VERIFY Student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																	target='_blank'>$student_call_sign</a> ($student_level $student_semester) was sent the third 
																	<a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_callsign=$student_call_sign&strpass=2' target='_blank'>verification email</a>.<br />";
															} else {
																// $content 						.= "&nbsp;&nbsp;&nbsp;Not enough time has passed between $student_email_sent_date and $todaysDate. No email sent<br />";
																$notEnoughTime++;
															}
															break;
														case 3:
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Going through case 3<br />";
															}
															$testDate 							= strtotime("$student_email_sent_date + 10 days");
															if ($currentTimestamp >= $testDate) {
																$theSubject						= "CW Academy Morse Code Class Verification - Final Notice";
																if ($student_abandoned != 'Y') {
																	$finalNotice				= "<p><b>NOTICE:</b> CW Academy has sent three emails to you 
																									requesting you to confirm your availability to take a class 
																									and verify your class choices. This is the final notice.  
																									Unless you click the link below to confirm your availability 
																									for the next semester, you will not be eligible for assignment 
																									to a CW Academy class.</p>";
																} else {
																	$finalNotice				= "<p><b>NOTICE:</b> You did not complete your CW Academy registration 
																									process. CW Academy has sent three emails to you 
																									requesting you to complete your registration, verify your class 
																									choices, and confirm your availability to take a class This is the final notice.  
																									Unless you click the link below to complete your registration process  
																									for the next semester, you will not be eligible for assignment 
																									to a CW Academy class.</p>";
																}
																$sendEmail						= TRUE;
																$verifyCount++;
																$studentEmailCount--;
																if ($doDebug) {
																	echo "&nbsp;&nbsp;&nbsp;Final Notice email sent and set email_number to 4<br />";
																}
																$update_action_log				.= "VERIFY Set email_number to 4 and sent final notice email ";
																$updateData['student_email_number'] = 4;
																$doUpdateStudent					= TRUE;
																$email4Sent++;
																$content						.= "VERIFY Student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																	target='_blank'>$student_call_sign</a> ($student_level $student_semester) was sent the 
																	<a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_callsign=$student_call_sign&strpass=2' target='_blank'>Final Notice</a> email.<br />";
															} else {					
																$notEnoughTime++;
															}
															break;
														case 4:
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Going through case 4<br />";
															}
															$sendEmail							= FALSE;
															break;
													}
														
													if ($sendEmail) {
														if ($doDebug) {
															echo "sendEmail is TRUE<br />";
														}
														$classChoiceMsg				= "";
														if ($student_abandoned != 'Y') {
															if ($doDebug) {
																echo "student_abandoned is NOT Y. Setting up new catalog Class Choice Msg<br />";
															}
							
															$emailContent 	= "To: $user_last_name, $user_first_name ($student_call_sign):
																				$finalNotice
																				<p>Thank you for your interest in CW Academy!!</p><p><b>Please read this email all the way through as there are 
																				actions you MUST take in order to be considered for assignment to a CW Academy class.</b> </p><p>You are 
																				receiving this email because you previously signed up for a CW Academy class.  You have been 
																				selected to potentially be a student in one of these classes.  CW Academy is 
																				currently preparing to form classes for the $nextSemester Semester and needs to verify 
																				that you are available for the upcoming semester and that you have selected your class preferences.  
																				Please note that we cannot guarantee that every student will be able to take a class.  This depends on 
																				the number of students who respond and the availability of advisor classes.  We make every 
																				effort to accommodate students but some students may have to be put on a waiting 
																				list or moved to the next semester.</p>";
															if ($student_catalog_options != '' && $student_catalog_options != 'None') {
																$myArray	= explode(",",$student_catalog_options);
																$myStr		= 'When you signed up, you indicated your possible availability as:<br />';
																foreach($myArray as $thisValue) {
																	$newStr	= $matrixConvert[$thisValue];
																	$myStr	.= "&nbsp;&nbsp;&nbsp;&nbsp;$newStr<br />";
																}
																
																$emailContent	.= "$myStr";
															}
															$emailContent		.= "<br /><table style='border:4px solid red;'><tr><td>
																				   <p><b>IMPORTANT!</b> The current catalog of CW Academy classes 
																				   for the $theSemester semester is now available. You <span style='color:red;'><b>MUST</b></span> 
																				   review your sign up information and select your preferred schedule preferences or <u><b>you will 
																				   not be eligible to be assigned to a class</b></u>.</p>
																				   <p>Please log into <a href='$siteURL/login'>CW Academy</a> and follow the instructions there. You 
																				   will have the options to select class choices, to change to a future semester, or cancel your 
																				   registration.</p></table>
																					<p>CW Academy requires all potential students meet the following requirements:
																					<br />1. A serious commitment of 60 minutes of daily practice
																					<br />2. Your availability to meet online 60 minutes twice a week for 8 weeks 
																					<br />3. Have a working paddle for sending Morse code
																					<br />4. Have a keyer or radio that the advisor and other students can hear your code
																					<br />5. A high-speed internet connection that will allow you to 
																					participate in classes held via Zoom or another online meeting program
																					<br />6. Access to a Windows PC or the Windows Operating System is needed for the Intermediate and Advanced Level classes</p>
																					<p>CW Academy has an automated process to assign students to advisor classes. Previous students 
																					are given priority. With nearly a thousand 
																					student sign-ups each semester, the demand for classes usually exceeds the supply. Consequently 
																					you may not get a class this semester. Special requests are difficult to 
																					honor in a timely fashion.</p>
																					<table style='border:4px solid blue;'><tr><td>
																					<p>If you have questions or concerns, <span style='color:red;'><b>PLEASE do not reply to this email</b></span> as the address is not monitored. 
																					Instead refer to appropriate person at <a href='$classResolutionURL'>CWA Class 
																					Resolution</a> for assistance.</p></td></tr></table>
																					<p>Note: Please save this email in case you need to change something in the future....</p>
																					<p>Regards,<br />
																					CW Academy</p>";
																					
																					
															$token				= mt_rand();
															$reminder_text		= "<b>Select Class Schedule and Language Preferences:</b> It is time to verify your availability to take a class in the upcoming semester 
and to select your class preferences. The class catalog is now available. Select one of the following:<br />
1. To verify your availability and indicate your desired class schedule and language, click <a href='$studentRegistrationURL?inp_verify=Y&strpass=2&inp_verify=Y&token=$token&enstr=$encstr'>Select Class Preferences</a>. 
Students will be assigned to advisor classes in about three weeks<br />
2. You can move your registration to the $semesterTwo semester by clicking <a href='$TURemoveURL?appid=$student_id&strpass=2&xmode=$xmode&inp_option=1&token=$token'>HERE</a><br />
3. You can move your registration to the $semesterThree semester by clicking <a href='$TURemoveURL?appid=$student_id&strpass=2&xmode=$xmode&inp_option=2&token=$token'>HERE</a><br /> 
4. Finally, you can set your registration aside and sign up again in the future when your circumstances allow by clicking <a href='$TURemoveURL?appid=$student_id&strpass=2&xmode=$xmode&inp_option=3&token=$token'>Cancel  
my registration</a>";
															if ($student_email_number < 3) {
																$closeStr			= strtotime("+3 days");
															} else {
																$closeStr			= strtotime("+10 days");
															}
															$close_date			= date('Y-m-d H:i:s',$closeStr);
															$addReminder		= TRUE;
														} else {
															// the student abandoned the signup
															$emailContent = "To: $user_last_name, $user_first_name ($student_call_sign):
																			$finalNotice
																			<p>You previously started registering for a $student_level Level class
																			but did not complete the registration process. If you are interested in 
																			being a student in the upcoming semester, you must complete your 
																			registration. In order to do that, go to <a href='$siteURL/login'>CW Academy</a> 
																			and follow the instructions given there.</p>
																			<table style='border:4px solid blue;'><tr><td>
																			<p>If you have questions or concerns, <span style='color:red;'><b>PLEASE do not reply to this email</b></span> as the address is not monitored. 
																			Instead refer to appropriate person at <a href='$classResolutionURL'>CWA Class 
																			Resolution</a> for assistance.</p></td></tr></table>
																			<p>Note: Please save this email in case you need to change something in the future....</p>
																			<p>Regards,<br />
																			CW Academy</p>";
															$token			= mt_rand();
															$reminder_text	= "<b>Complete Registration:</b> You previously started registering for a $student_level Level class
but did not complete the registration process. If you are interested in being a student in the upcoming semester, you must complete your 
registration. To do so, click <a href='$studentRegistrationURL?inp_verify=Y&token=$token&strpass=2&enstr=$encstr'>Student Registration</a>. 
Update your information as needed and make your class preference choices.";
															if ($student_email_number < 3) {
																$closeStr			= strtotime("+3 days");
															} else {
																$closeStr			= strtotime("+10 days");
															}
															$close_date			= date('Y-m-d H:i:s',$closeStr);
															$addReminder		= TRUE;
														}
	
														if ($doDebug) {
															echo "email message is set up<br />";
														}
														if ($testMode) {
															$theRecipient		= 'rolandksmith@gmail.com';
															$theSubject			= "TESTMODE $theSubject";
															$mailCode			= 2;
															$increment++;
														} else {
															$theRecipient		= $user_email;
															$mailCode			= 13;
														}
														$mailResult			= emailFromCWA_v3(array('theRecipient'=>$theRecipient,
																									'theSubject'=>$theSubject,
																									'jobname'=>$jobname,
																									'theContent'=>$emailContent,
																									'mailCode'=>$mailCode,
																									'testMode'=>$testMode,
																									'increment'=>$increment,
																									'doDebug'=>TRUE));
														// $mailResult = TRUE;
														if ($mailResult[0] === TRUE) {
															if ($doDebug) {
																echo $mailResult[1];
																echo "&nbsp;&nbsp;&nbsp;A verification email was sent to $theRecipient ($student_level)<br />";
															}
															$content	.= "Verification email was sent to $student_call_sign at <a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_email=$theRecipient&strpass=2' 
																target='_blank'>$theRecipient</a><br />";
															$verifyCount++;
															$studentEmailCount--;
															if ($addReminder && $setReminder) {
																$addReminder		= FALSE;
																$setReminder	 	= FALSE;
																// add the reminder to the reminders table
																if ($doDebug) {
																	echo "adding reminder to reminders table<br />";
																}
																$myStr				= date('Y-m-d H:i:s');
																$inputParams		= array("effective_date|$myStr|s",
																							"close_date|$close_date|s",
																							"resolved_date||s",
																							"send_reminder|N|s",
																							"send_once|Y|s",
																							"call_sign|$student_call_sign|s",
																							"role||s",
																							"email_text||s",
																							"reminder_text|$reminder_text|s",
																							"resolved|N|s",
																							"token|$token|s");
																$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
																if ($insertResult[0] === FALSE) {
																	if ($doDebug) {
																		echo "inserting reminder failed: $insertResult[1]<br />";
																	}
																	$content		.= "Inserting reminder failed: $insertResult[1]<br />";
																}
															}
														} else {
															$content .= "&nbsp;&nbsp;&nbsp;The verify mail send function failed. Student: $student_call_sign; email: $theRecipient<br />";
															$emailErrors++;
															if (doDebugLog) {
																$debugLog	.= $mailResult[1];
																$debugLog	.= "sending verification email failed<br />";
															}
														}
													} else {
														if ($doDebug) {
															echo "sendEmail is FALSE. No email to be sent<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "processStudent is FALSE<br />";
													}
												}
											} else {
												if ($doDebug) {
													echo "processStudent is FALSE <br />";
												}
											}
										} else {
											if ($doDebug) {
												echo "student_welcome_date is NOT empty<br />";
											}
										}
	
									} else {			// student is not registered for the next semester. Skip.
										if ($doDebug) {
											echo "Student not registered for $nextSemester semester<br />";
										}
									}
								} else {
									if ($doDebug) {
										echo "no verification process needed<br />";
									}
								}				// Finished with the verification emails
	
// End of the verification process
	
	
				
				
								//////// update student process				
								if ($doUpdateStudent) {
									$student_action_log					= "$student_action_log / $actionDate $update_action_log";
									$updateData['student_action_log'] = $student_action_log;
									if ($doDebug) {
										echo "<br />Updating student record for $student_call_sign <br /><pre>";
										print_r($updateData);
										echo "</pre><br />";
									}
									$updateResult = $student_dal->update( $student_id, $updateData, $operatingMode );
									if ($updateResult === FALSE) {
										if ($doDebug) {
											echo "failed to update $student_call_sign ID $student_id record<br />";
										}
										$content .= "<p>Failed to update $student_call_sign ID $student_id record</p>";
									} else {
										if ($doDebug) {
											echo "$student_call_sign record  successfully updated<br />";
										}
									}
								}
	
								// reread student record to do the counts
								$studentData = $student_dal->get_student_by_id( $student_id, $operatingMode );
								if ($studentData === FALSE) {
									if ($doDebug) {
										echo "get_student_by_id for $student_id returned FALSE<br />";
									}
								} else {
									if (! empty($studentData)) {
										foreach($studentData as $key => $value) {
											$$key = $value;
										}
	
										// update counts for this student record
										if (!array_key_exists($student_semester,$semesterCountArray)) {
											$semesterCountArray[$student_semester]['total']			= 0;
											$semesterCountArray[$student_semester]['dropped']		= 0;
											$semesterCountArray[$student_semester]['replaced']		= 0;
											$semesterCountArray[$student_semester]['verified']		= 0;
											$semesterCountArray[$student_semester]['assigned']		= 0;
											$semesterCountArray[$student_semester]['unassigned']	= 0;
											$semesterCountArray[$student_semester]['Beginner']		= 0;
											$semesterCountArray[$student_semester]['Fundamental']	= 0;
											$semesterCountArray[$student_semester]['Intermediate']	= 0;
											$semesterCountArray[$student_semester]['Advanced']		= 0;
											
										}
										$semesterCountArray[$student_semester]['total']++;
										if ($student_email_number == 4 && $student_response == '') {
											$semesterCountArray[$student_semester]['dropped']++;
										}
										if ($student_status == 'R' || $student_status == 'C' || $student_status == 'V') {
											$semesterCountArray[$student_semester]['replaced']++;
										}
										if ($student_response == 'Y') {
											$semesterCountArray[$student_semester]['verified']++;
										}
										if ($student_response == 'Y' && $student_status == 'Y') {
											$semesterCountArray[$student_semester]['assigned']++;
										}
										if ($student_response == 'Y' && $student_status == '') {
											$semesterCountArray[$student_semester]['unassigned']++;
										}
										if ($student_level == 'Beginner') {
											$semesterCountArray[$student_semester]['Beginner']++;
										}
										if ($student_level == 'Fundamental') {
											$semesterCountArray[$student_semester]['Fundamental']++;
										}
										if ($student_level == 'Intermediate') {
											$semesterCountArray[$student_semester]['Intermediate']++;
										}
										if ($student_level == 'Advanced') {
											$semesterCountArray[$student_semester]['Advanced']++;
										}
									} else {
										sendErrorEmail("$jobname rereading student record for student_id $student_id ($student_call_sign) failed");
									}
								}
							} else {								// end of the while verifyEmailCount > 0
								if ($doOnce) {
									$content	.= "<p>Maximum number of sent emails reached</p>";
									$doOnce		= FALSE;
								}
							}
						}					
					}
				}
			} else {
				$content	.= "No records found in the $studentTableName table<br />";
			}
		}

///// all processing done. Prepare totals	
		$content		.= "<h4>Processing Complete. Preparing Totals</h4>";
		if ($doDebug) {
			echo "<br />Sending email with the totals<br />";
		}
		if ($validEmailPeriod == "N") {
			$myString	= "Outside of the Verification Email Window.";
		} else {
			$myString	= "Within the Verification Email Window.";
		}
		$content	.= "<br /><table><tr><th colspan='2'>Counts</td></tr>
						<tr><td style='text-align:right;'>$recordsProcessed</td><td>Total records read ($studentRecordsFound)</td></tr>
						<tr><th colspan='2'>Welcome Email Process</td></tr>
						<tr><td style='text-align:right;'>$welcomeCount</td><td>Welcome Emails Sent</td></tr>
						<tr><td style='text-align:right;'>$abandonedCount</td><td>Of those that were Abandoned Emails</td></tr>
						<tr><td style='text-align:right;'>$badActorCount</td><td>Bad Actors</td></tr>
						<tr><td colspan='2'><hr></td></tr>
						<tr><th colspan='2'>Verify Process</td></tr>
						<tr><td colspan='2'>$myString</td></tr>
						<tr><td style='text-align:right;'>$numberVRecords</td><td>Records read for $nextSemester</td></tr>
						<tr><td style='text-align:right;'>$studentHasResponded</td><td>Students who have responded either Yes or Remove</td></tr>
						<tr><td style='text-align:right;'>$studentYes</td><td>Students who have responded Yes</td></tr>
						<tr><td style='text-align:right;'>$studentR</td><td>Students marked as Remove</td></tr>
						<tr><td style='text-align:right;'>$notEnoughTime</td><td>Students not sent an email as it is not due</td></tr>
						<tr><td style='text-align:right;'>$student45DayCount</td><td>Newly registered students whose request date is within 45-day window. No email. Response set to Y</td></tr>
						<tr><td style='text-align:right;'>$studentInClass</td><td>Newly registered students who have taken a class, no email sent. Response set to Y</td></tr>
						<tr><td style='text-align:right;'>$studentsSentFirstEmail</td><td>Students who need first verification email</td></tr>
						<tr><td style='text-align:right;'>$prevDropped</td><td>Students who were sent a dropped email and have not responded</td></tr>
						<tr><td style='text-align:right;'>$verifyCount</td><td>Emails sent requesting verification</td></tr>
						<tr><td style='text-align:right;'>$email1Sent</td><td>First emails sent</td></tr>
						<tr><td style='text-align:right;'>$email2Sent</td><td>Second emails sent</td></tr>
						<tr><td style='text-align:right;'>$email3Sent</td><td>Third emails sent</td></tr>
						<tr><td style='text-align:right;'>$email4Sent</td><td>Dropped emails sent</td></tr>		  
						<tr><td style='text-align:right;'>$emailErrors</td><td>Emails attempted that failed to send</td></tr>
						<tr><td style='text-align:right;'>$studentNoRequestDate</td><td>Students with no request date. Date set to today. Email may go next run.</td></tr>
						<tr><td style='text-align:right;'>$studentNoWelcomeDate</td><td>Students with no welcome date. Date set to today. Email may go next run.</td></tr>
						<tr><td colspan='2'><hr></td></tr>
						<tr><th colspan='2'><b>Semester Totals</b></td></tr>";

		foreach($validSemesters as $thisSemester) {
			if (array_key_exists($thisSemester,$semesterCountArray)) {
				if ($semesterCountArray[$thisSemester]['total'] > 0) {
					$myInt_total		= $semesterCountArray[$thisSemester]['total'];
					$myInt_dropped		= $semesterCountArray[$thisSemester]['dropped'];
					$myInt_replaced		= $semesterCountArray[$thisSemester]['replaced'];
					$myInt_verified		= $semesterCountArray[$thisSemester]['verified'];
					$myInt_assigned		= $semesterCountArray[$thisSemester]['assigned'];
					$myInt_unassigned	= $semesterCountArray[$thisSemester]['unassigned'];
					$myInt_Beginner		= $semesterCountArray[$thisSemester]['Beginner'];
					$myInt_Fundamental	= $semesterCountArray[$thisSemester]['Fundamental'];
					$myInt_Intermediate	= $semesterCountArray[$thisSemester]['Intermediate'];
					$myInt_Advanced		= $semesterCountArray[$thisSemester]['Advanced'];
					$content	.= "<tr><td colspan='2'>$thisSemester</td></tr>
									<tr><td style='text-align:right;'>$myInt_Beginner</td><td>Beginner Registrations</td></tr>
									<tr><td style='text-align:right;'>$myInt_Fundamental</td><td>Fundamental Registrations</td></tr>
									<tr><td style='text-align:right;'>$myInt_Intermediate</td><td>Intermediate Registrations</td></tr>
									<tr><td style='text-align:right;'>$myInt_Advanced</td><td>Advanced Registrations</td></tr>
									<tr><td style='text-align:right;'>$myInt_total</td><td>Total Registrations</td></tr>
									<tr><td style='text-align:right;'>$myInt_dropped</td><td>Registrations dropped</tr>
									<tr><td style='text-align:right;'>$myInt_replaced</td><td>Replacements</td></tr>
									<tr><td style='text-align:right;'>$myInt_verified</td><td>Verified Registrations</td></tr>
									<tr><td style='text-align:right;'>$myInt_assigned</td><td>Students assigned</td></tr>
									<tr><td style='text-align:right;'>$myInt_unassigned</td><td>Unassigned students</td></tr>";
				}
			}
		}
		$content	.= "</table><br />";

		if (count($errorArray) > 0) {
			$content	.= "<h4>Errors:</h4>";
			foreach($errorArray as $myValue) {
				$content	.= "$myValaue";
			}
			$content	.= "<br />";
		}
 		if ($doDebug) {
 			if ($testMode) {
				$storeResult	= storeReportData_v2("TESTMODE $jobname DEBUG",$debugLog);
				$storeResult	= TRUE;
			} else {
				$storeResult	= storeReportData_v2("$jobname DEBUG",$debugLog);
			}
			if ($storeResult !== FALSE) {
				$content	.= "<br />Debug report stored in reports table as $storeResult[1]";
			} else {
				echo "<br />Storing the report in the reports table failed";
			}
		}

		$thisTime 		= date('Y-m-d H:i:s');
		$content		.= "<br />Function completed at $thisTime<br />";
		$theSubject		= "CWA Daily Student Cron Process";
		$endingMicroTime = microtime(TRUE);
		$elapsedTime	= $endingMicroTime - $startingMicroTime;
		$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
		$content		.= "<p>Report pass 0 took $elapsedTime seconds to run</p>";
		$nowDate		= date('Y-m-d');
		$nowTime		= date('H:i:s');
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

		// store the report in the reports table
		$storeResult	= storeReportData_v2($jobname,$content,$testMode,$doDebug);
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
		$reminder_text	= "<b>Daily Student Cron:</b> To view the Daily Student Cron report for $nowDate $nowTime, click <a href='cwa-display-saved-report/?strpass=3&inp_callsign=XXXXX&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
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

		$theSubject	= "CWA Daily Student Cron Process";
		$theContent	= "The daily Student cron process was run at $nowDate $nowTime, Login to <a href='$siteURL/program-list'>CW Academy</a> to see the 
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
			$myStr		= "Process completed<br />";
			return $myStr;
		} else {
			$myStr .= "<br />The final mail send function failed.<br /><br />
						<a href='$siteURL/program-list/'>Return to Portal</a></p>";
			return "Process completed, sending email failed<br />";
		}
	}
}
add_shortcode ('daily_student_cron', 'daily_student_cron_func');
