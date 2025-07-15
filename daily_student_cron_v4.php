function daily_student_cron_v4_func() {

/*	     	Daily Student Cron
      Function to send welcome emails to newly registered prospective students,
   Send verification emails to registered students,
   do any requested replacement students,
  
   This job is run via a cron curl job to run the associated webpage
  
  	If validReplacementPeriod is Y, see if any outstanding replacement requests 
  	can be accomodated
  	
  		for each replacement request
  			Get the advisorclass info
  			Find any students whose class choices match the advisor's schedule
  			If found, add student to the class
  				send the advisor the new class information
  				markk the replacement request as fulfulled
  
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
      
    check whether there are any outstanding replacement requests
    	If in the valid replacement period
    		read any replacement requests from wpw1_cwa_replacement_requests that have not 
    			been fulfilled
    		Look in the pool of available students to see if a student matches 
    			the criteria.
    		If so, assign the student
    			send the advisor an email with the replacement information
  
  	check to process a replacement student
      If the student status is 'R' or 'V' then a replacement has been requested.
      Remove the student from the class
      	if status was R, set the student status to 'C'
      	if status was V, set the student status to blank
      Look in the pool of available students to see if a student matches the
        criteria. 
      If so, assign the student
      	Send the advisor an email either with the replacement
      	Update replacement request with date fulfilled
    
  	Date Formats:
  		Request Date: 			Y-M-D
  		Email Sent Date: 		Y-M-D
  		Response Date:			Y-M-D
  		Selected Date:			Y-M-D 
  		Welcome Date:			Y-M-D
  		Advisor Selected Date:	Y-M-Dif ($doDebug)
  
 	Created from the large daily cron job on 6July2021 by Roland
 	Modified to add ability in testmode to modify the dates so that the 
		verify processes could be tested. Both verifyMode and testMode must be true to 
		do this test method
	Modified 26July21 by Roland to change the replacement process. The process now is 
		limited to the periods 4/10 - 5/10; 8/10 - 9/10; and 12/10 - 01/10
		The replacement criteria is also changed to use student class choices 
	Modified 29Aug21 by Roland to make the class choice conversion to utc if the utc fields are empty
		moved the version to V3
	Modified 2Jan22 by Roland to fix the link to update student information and to add link to check 
		student status
	Modified 13Jul22 by Roland to simplify several processes and to check class availability when 
		sending welcome or verify emails
	Modified 1Nov22 by Roland to accommodate timezone table changes
	Modified 3Feb23 by Roland to handle multiple excluded advisors
	Modified 15Apr23 by Roland to fix action_log
	Modified 12Jul23 by Roland to use consolidated tables
	Modified 27Jul23 by Roland to incorporate the outstanding replacement requests
	Modified 28Oct23 by Roland to correct the welcome message when no catalog
	Modified 12Nov23 by Roland to notify Roland when a duplicate student callsign is found
	Modified 17Nov23 by Roland to store actions in the reminders table
	Modified 25Dec23 by Roland to store the debug log correctly. Version upgraded to 4
	Modified 26Feb24 by Roland to add additional counts
	Modified 12Oct24 by Roland for new database
 
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
	$jobname				= "Daily Student Cron V$versionNumber";
	$currentTimestamp 		= $initializationArray['currentTimestamp'];
	$todaysDate 			= $initializationArray['currentDate'];
	$checkDate 				= date('Y-m-d',$currentTimestamp);
	$unixCheckDate			= strtotime($checkDate);
	$currentSemester		= $initializationArray['currentSemester'];
	$nextSemester 			= $initializationArray['nextSemester'];
	$semesterTwo 			= $initializationArray['semesterTwo'];
	$semesterThree 			= $initializationArray['semesterThree'];
	$semesterFour 			= $initializationArray['semesterFour'];
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
	$advisorVerifyURL		= "$siteURL/cwa-advisor-verification-of-student/";
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
	$log_ID  								= '';    // id
	$log_call_sign  						= '';    // call_sign
	$log_first_name  						= '';    // first_name
	$log_last_name  						= '';    // last_name
	$log_email  							= '';    // email
	$log_phone  							= '';    // phone
	$log_ph_code							= '';
	$log_city 		 						= '';    // city
	$log_state  							= '';    // state
	$log_country  							= '';    // country
	$log_country_codoe						= '';
	$log_zip_code							= '';
	$log_time_zone  						= '';    // time_zone
	$log_timezone_id						= '';
	$log_timezone_offset					= '';
	$log_whatsapp							= '';
	$log_signal								= '';
	$log_telegram							= '';
	$log_messenger							= '';
	$log_wpm  								= '';    // wpm
	$log_youth  							= '';    // youth
	$log_age 								= '';    // age
	$log_student_parent 					= '';    // student_parent
	$log_student_parent_email				= '';    // student_parent_email
	$log_level  							= '';    // level
	$log_waiting_list 						= '';    
	$log_request_date  						= '';    // request_date
	$log_semester  							= '';    // semester
	$log_notes  							= '';    // notes
	$log_email_sent_date  					= '';    // email_sent_date
	$log_email_number  						= '';    // email_number
	$log_response  							= '';    // response
	$log_response_date  					= '';    // response_date
	$log_abandoned  						= '';    // abandoned
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
	$log_first_class_choice_utc				= '';
	$log_second_class_choice_utc			= '';
	$log_third_class_choice_utc				= '';
	$log_no_catalog							= '';
	$fieldTest								= array('action_log','post_status','post_title','control_code');
	$update_action_log						= '';
	$replaceArrayBeginner		= array();
	$replaceArrayFundamental			= array();
	$replaceArrayIntermediate	= array();
	$replaceArrayAdvanced		= array();
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

	$catalogMode			= "Production";
	if ($testMode) {
		$catalogMode		= "TestMode";
	}
	

	
	if ($currentSemester == 'Not in Session') {
		$theSemester		= $nextSemester;
	} else {
		$theSemester		= $currentSemester;
	}
	
	if ($doDebug) {
		echo "Operating using $theSemester semester<br />";
	}

	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
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
				
				table{font:'Times New Roman', sans-serif;background-image:none;}
				
				th {color:#ffff;background-color:#000;padding:5px;font-size:small;}
				
				td {padding:5px;font-size:small;}
				</style>";


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


	$runTheJob				= TRUE;
////// see if this is the time to actually run
	if ($doDebug) {
		echo "<br />starting<br />";
	}
// $validReplacementPeriod = 'Y';
		
	if ($userName != '') {
		$content 			.= "<h3>$jobname Executed by $userName</h3>";
	} else {
		$content			.= "<h3>$jobname Process Automatically Executed</h3>";
		$userName			= "CRON";
		$runTheJob				= allow_job_to_run($doDebug);
	}

	
// $runTheJob = TRUE;	
	if ($runTheJob) {
		if ($testMode) {
			$studentTableName		= 'wpw1_cwa_student2';
			$advisorTableName		= 'wpw1_cwa_advisor2';
			$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
			$audioAssessmentTableName = 'wpw1_cwa_audio_assessment2';
			$replacementRequests	= 'wpw1_cwa_replacement_requests2';
			$userMasterTableName	= 'wpw1_cwa_user_master2';
			$content .= "<p><strong>Function is under development.</strong></p>";
			$xmode					= 'tm';
			$inp_mode				= 'TESTMODE';
			$catalogMode			= 'TestMode';
		} else {
			$studentTableName	 	= 'wpw1_cwa_student';
			$advisorTableName		= 'wpw1_cwa_advisor';
			$advisorClassTableName	= 'wpw1_cwa_advisorclass';
			$audioAssessmentTableName = 'wpw1_cwa_audio_assessment';
			$replacementRequests	= 'wpw1_cwa_replacement_requests';
			$userMasterTableName	= 'wpw1_cwa_user_master';
			$xmode					= 'pd';
			$inp_mode				= 'Production';
			$catalogMode			= 'Production';
		}
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
				$thisTime		= $myArray[1];
				$thisDays		= $myArray[2];
				$thisCount		= $myArray[3];
				$thisAdvisors	= $myArray[4];
				$classesArray["$thisLevel|$thisTime|$thisDays"]	= "$thisCount|$thisAdvisors";
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

		if ($validReplacementPeriod == 'Y') {
			if ($doDebug) {
				echo "<br /><b>REPLACE Array</b> In the replacement period<br />Building the array of 
					  potential replacement students<br />";
			}
		 	////// build the array of potential replacement students
			////// replaceArray[level][priority-reqdate] = student call sign|first choice UTC|second choice UTC|thirdChoice UTC|excluded advisor
		
			$sql				= "select * from $studentTableName 
									left join $userMasterTableName on user_call_sign = student_call_sign 
								   where student_semester='$theSemester' 
								   	and student_response='Y' 
								   	and student_status='' 
								   	and student_intervention_required != 'H' 
								   	and student_email_number != 4 
								   order by student_level, student_call_sign";
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
						$student_master_ID 					= $studentRow->user_ID;
						$student_master_call_sign 			= $studentRow->user_call_sign;
						$student_first_name 				= $studentRow->user_first_name;
						$student_last_name 					= $studentRow->user_last_name;
						$student_email 						= $studentRow->user_email;
						$student_ph_code 					= $studentRow->user_ph_code;
						$student_phone 						= $studentRow->user_phone;
						$student_city 						= $studentRow->user_city;
						$student_state 						= $studentRow->user_state;
						$student_zip_code 					= $studentRow->user_zip_code;
						$student_country_code 				= $studentRow->user_country_code;
						$student_country 					= $studentRow->user_country;
						$student_whatsapp 					= $studentRow->user_whatsapp;
						$student_telegram 					= $studentRow->user_telegram;
						$student_signal 					= $studentRow->user_signal;
						$student_messenger 					= $studentRow->user_messenger;
						$student_master_action_log 			= stripslashes($studentRow->user_action_log);
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
						$student_parent 						= $studentRow->student_parent;
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
						$student_action_log  					= stripslashes($studentRow->student_action_log);
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
	
								
						$student_last_name 						= no_magic_quotes($student_last_name);
	
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
	
	
						if ($doDebug) {
							echo "<br />Processing $student_call_sign. Data read:<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;First class choice: $student_first_class_choice<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Second class choice: $student_second_class_choice<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Third class choice: $student_third_class_choice<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;First class choice UTC: $student_first_class_choice_utc<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Second class choice UTC: $student_second_class_choice_utc<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Third class choice UTC: $student_third_class_choice_utc<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Student email sent date: $student_email_sent_date<br />
								  &nbsp;&nbsp;&nbsp;&nbsp;Student Email Number; $student_email_number<br />";
						  
						}
						$utc1Times					= '';
						$utc2Times					= '';
						$utc3Times					= '';
						$utc1Days					= '';
						$utc2Days					= '';
						$utc3Days					= '';
						$noGo						= FALSE;
						if ($student_intervention_required == 'H') {
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;&nbsp;student is on hold<br />";
							}
							$noGo					= TRUE;
						}
						if ($student_first_class_choice == '' || $student_first_class_choice == 'None') {
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;&nbsp;student has no first class choice<br />";
							}
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
								if ($doDebug) {
									echo "Using $student_first_class_choice_utc of $student_first_class_choice_utc<br />";
								}
								$myArray			 	= explode(" ",$student_first_class_choice_utc);
								if (count($myArray) == 2) {
									$utc1Times				= $myArray[0];
									$utc1Days			 	= $myArray[1];
								} else {
									echo "<br /><b>ERROR</b> $student_call_sign has an invalid first_class_choice_utc of $student_first_class_choice_utc<br /><br />";
								}
							} else {				/// first choice utc is empty. is there a first choice local?
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
								if ($doDebug) {
									echo "Using $student_second_class_choice_utc of $utc2Times $utc2Days<br />";
								}
							} else {				/// second choice utc is empty. is there a second choice local?
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
								if ($doDebug) {
									echo "Using $student_third_class_choice_utc of $utc1Times $utc1Days<br />";
								}
							} else {				/// third choice utc is empty. is there a third choice local?
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
								${'replaceArray' . $student_level}[] = "$thisSequence|$student_call_sign|$utc1Times|$utc1Days|$utc2Times|$utc2Days|$utc3Times|$utc3Days|$myStr";
								//										0             1                  2          3         4          5         6          7         8
								if ($doDebug) {
									echo "data added to replaceArray$student_level: $thisSequence|$student_call_sign|$utc1Times|$utc1Days|$utc2Times|$utc2Days|$utc3Times|$utc3Days|$myStr<br />";
								}
							} else {
								if ($doDebug) {
									echo "student not added to the replaceArray<br />";
								}
							}
						}
					}			///// end of the student while
				
					sort($replaceArrayBeginner);
					sort($replaceArrayFundamental);
					sort($replaceArrayIntermediate);
					sort($replaceArrayAdvanced);
					/// dump the replace arrays if in debug mode
					if ($doDebug) {
						$myInt 		= count($replaceArrayBeginner);
						echo "<br />replace array beginner ($myInt students):<br /><pre>";
						print_r($replaceArrayBeginner);
						echo "</pre><br />";
						$myInt		= count($replaceArrayFundamental);
						echo "<br />replace array fundamental ($myInt students):<br /><pre>";
						print_r($replaceArrayFundamental);
						echo "</pre><br />";
						$myInt		= count($replaceArrayIntermediate);
						echo "<br />replace array intermediate ($myInt students):<br /><pre>";
						print_r($replaceArrayIntermediate);
						echo "</pre><br />";
						echo$myInt		= count($replaceArrayAdvanced);
						echo "<br />replace array advanced ($myInt students):<br /><pre>";
						print_r($replaceArrayAdvanced);
						echo "</pre><br />";
					}
				} else {
					$content	.= "<p>In the replacement period. Reading $studentTableName table to build 
									the replacement array. No records found.</p>";
					if ($doDebug) {
						echo "In the replacement period. Reading $studentTableName table to build 
							 the replacement array. No records found. Turning validReplacementPeriod off<br />";

						$validReplacementPeriod 	= 'N';
					}
				}
			}
			if ($doDebug) {
				echo "REPLACE Array: array built<br />";
			}
		}		///////// end of build the replacement array




//////// Begin process to handle outstanding replacement requests
		if ($validReplacementPeriod == 'Y' && $currentSemester == 'Not in Session') {
			if ($doDebug) {
				echo "<br /><b>OUTSTANDING</b> Outstanding Replacement Requests Process<br />";
			}
			$content	.= "<h4>Proccessing Outstanding Replacement Requests</h4>";
			// get the outstanding replacement requests and run any that 
			// haven't been fulfilled
			
			$sql		= "select * from $replacementRequests 
							where semester = '$theSemester' 
							and date_resolved = '' 
							order by date_created ";
			$wpw1_cwa_replacement_requests		= $wpdb->get_results($sql);
			if ($wpw1_cwa_replacement_requests === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numBARows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numBARows rows<br />";
				}
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
						
						if ($doDebug) {
							echo "<br />Processing replacement request for advisor: $replacement_call_sign<br />
									student: $replacement_student<br />
									class: $replacement_class<br />";
						}

						// get the advisor class schedule
						$sql			= "select * from $advisorClassTableName  
											left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
											where advisorclass_call_sign = '$replacement_call_sign' 
											and advisorclass_sequence = $replacement_class 
											and advisorclass_semester = '$theSemester'";

						$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
						if ($wpw1_cwa_advisorclass === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numACRows						= $wpdb->num_rows;
							if ($doDebug) {
								$myStr						= $wpdb->last_query;
								echo "ran $myStr<br />and found $numACRows rows<br />";
							}
							if ($numACRows > 0) {
								foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
									$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
									$advisorClass_advisor_call_sign 		= $advisorClassRow->advisorclass_call_sign;
									$advisorClass_advisor_first_name 		= $advisorClassRow->user_first_name;
									$advisorClass_advisor_last_name 		= $advisorClassRow->user_last_name;
									$advisorEmail							= $advisorClassRow->user_email;
									$advisorClass_sequence					= $advisorClassRow->advisorclass_sequence;
									$advisorClass_level						= $advisorClassRow->advisorclass_level;
									$advisorClass_action_log				= stripslashes($advisorClassRow->advisorclass_action_log);
									$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
									$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
									$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
									$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;

									if ($doDebug) {
										echo "got the advisor class schedule<br />";
									}

									// now look for a potential replacement student
									$searchTime = intval($advisorClass_class_schedule_times_utc);
									$searchDays = $advisorClass_class_schedule_days_utc;
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp;&nbsp;Looking for a replacement level $replacement_level $searchTime $searchDays<br />";
									}
									$callSign1	= '';
									$sequence1	= '';
									$callSign2	= '';
									$sequence2	= '';
									$callSign3	= '';
									$sequence3	= '';

									foreach(${'replaceArray' . $replacement_level} as $myKey=>$myValue) {
										if ($doDebug) {
											echo "<br />Checking possible replacement: $myValue<br />";
										}
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
			
										$myInt				= strpos($excludedAdvisor,$advisorClass_advisor_call_sign);
										if ($myInt === FALSE) {
											if ($searchDays == $firstDays) {
												if ($doDebug) {
													echo "first choice match on $searchDays. Checking poss replacement first choice $firstTime<br />";
												}
												if ($callSign1 == '') {		// don't continue if have a match
													$searchBegin = intval($firstTime) - 300;
													$searchEnd = intval($firstTime) + 300;
													if ($searchBegin < 0) {
														$searchBegin	= 0;
													}
													if ($searchEnd > 2400) {
														$searchEnd		= 2400;
													}
													if ($doDebug) {
														echo "testing firstTime: $firstTime. Looking for student between $searchBegin and $searchEnd<br />";
													}
													if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
														$callSign1 = $thisCallSign;
														$sequence1 = $myKey;
														if ($doDebug) {
															echo "1: Found $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd<br />
																			  Set callSign1 to $thisCallSign and sequence1 to $myKey<br />";
														}
													} else {
														if ($doDebug) {
															echo "1: No go $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd<br />";
														}
													}
												}
											} else {
												if ($doDebug) {
													echo "Searching first choice days did not match<br />";
												}
				
											}
											if ($searchDays == $secondDays) {
												if ($doDebug) {
													echo "second choice match on searchDays. Doing second choice $secondTime<br />";
												}
												if ($callSign2 == '') {
													$searchBegin 	= intval($secondTime) - 300;
													$searchEnd 		= intval($secondTime) + 300;
													if ($searchBegin < 0) {
														$searchBegin	= 0;
													}
													if ($searchEnd > 2400) {
														$searchEnd		= 2400;
													}
													if ($doDebug) {
														echo "testing secondTime: $secondTime. Looking for student between $searchBegin and $searchEnd<br />";
													}
													if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
														$callSign2 = $thisCallSign;
														$sequence2 = $myKey;
														if ($doDebug) {
															echo "2: Found $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd<br />
															Set callSign2 to $thisCallSign and sequence2 to $myKey<br />";
														}
													} else {
														if ($doDebug) {
															echo "2: No go $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd<br />";
														}
													}
												} else {
													if ($doDebug) {
														echo "searching second choice days did not match<br />";
													}
												}
											} else {
												if ($doDebug) {
													echo "searching second choice days didn't match<br />";
												}
											}
											if ($searchDays == $thirdDays) {
												if ($doDebug) {
													echo "third choice match on searchDays. Doing third choice $thirdTime<br />";
												}
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
													if ($doDebug) {
														echo "testing thirdTime: $thirdTime. Looking for student between $searchBegin and $searchEnd<br />";
													}
													if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
														$callSign3 = $thisCallSign;
														$sequence3 = $myKey;
														if ($doDebug) {
															echo "3: Found $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd<br />
																			  Set callSign3 to $thisCallSign and sequence3 to $myKey<br />";
														}
													} else {
														if ($doDebug) {
															echo "3: No go $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd<br />";
														}
													}
												}	
											} else {
												if ($doDebug) {
													echo "searching third choice days did not match<br />";
												}
											}
										} else {
											if ($doDebug) {
												echo "possible advisor is excluded: $excludedAdvisor<br />";
											}
										} 
									}
									if ($doDebug) {
										echo "CallSign1: $callSign1 | $sequence1<br />
											  CallSign2: $callSign2 | $sequence2<br />
											  CallSign3: $callSign3 | $sequence3<br />";
									}
									$gotAReplacement			= FALSE;
									if ($callSign1 != '') {
										$replacingCallSign		= $callSign1;
										$replacingSequence		= $sequence1;
										$gotAReplacement		= TRUE;
										if ($doDebug) {
											echo "$replacingCallSign (1) works as a replacement<br />";
										}
									} elseif ($callSign2 != '') {
										$replacingCallSign		= $callSign2;
										$replacingSequence		= $sequence2;
										$gotAReplacement		= TRUE;
										if ($doDebug) {
											echo "$replacingCallSign (2) works as a replacement<br />";
										}
									} elseif ($callSign3 != '') {
										$replacingCallSign		= $callSign3;
										$replacingSequence		= $sequence2;
										$gotAReplacement		= TRUE;
										if ($doDebug) {
											echo "$replacingCallSign (3) works as a replacement<br />";
										}
									}
		
									if ($gotAReplacement) {
										if ($doDebug) {
											echo "Have a replacement $replacingCallSign. Getting student record<br />";
										}
										///// get the replacement student record
										$sql						= "select * from $studentTableName 
																		left join $userMasterTableName on user_call_sign = student_call_sign 
																		where student_semester='$theSemester' 
																		and student_call_sign='$replacingCallSign'";
										$wpw1_cwa_replace				= $wpdb->get_results($sql);
										if ($wpw1_cwa_replace === FALSE) {
											handleWPDBError($jobname,$doDebug);
										} else {
											$numRRows				= $wpdb->num_rows;
											if ($doDebug) {
												echo "ran $sql<br />and retrieved $numRRows rows from $studentTableName table<br />";
											}
											if ($numRRows > 0) {
												foreach ($wpw1_cwa_replace as $replaceRow) {
													$replace_master_ID 					= $studentRow->user_ID;
													$replace_master_call_sign 			= $studentRow->user_call_sign;
													$replace_first_name 				= $studentRow->user_first_name;
													$replace_last_name 					= $studentRow->user_last_name;
													$replace_email 						= $studentRow->user_email;
													$replace_ph_code 					= $studentRow->user_ph_code;
													$replace_phone 						= $studentRow->user_phone;
													$replace_city 						= $studentRow->user_city;
													$replace_state 						= $studentRow->user_state;
													$replace_zip_code 					= $studentRow->user_zip_code;
													$replace_country_code 				= $studentRow->user_country_code;
													$replace_country 					= $studentRow->user_country;
													$replace_whatsapp 					= $studentRow->user_whatsapp;
													$replace_telegram 					= $studentRow->user_telegram;
													$replace_signal 					= $studentRow->user_signal;
													$replace_messenger 					= $studentRow->user_messenger;
													$replace_master_action_log 			= stripslashes($studentRow->user_action_log);
													$replace_timezone_id 				= $studentRow->user_timezone_id;
													$replace_languages 					= $studentRow->user_languages;
													$replace_survey_score 				= $studentRow->user_survey_score;
													$replace_is_admin					= $studentRow->user_is_admin;
													$replace_role 						= $studentRow->user_role;
													$replace_master_date_created 		= $studentRow->user_date_created;
													$replace_master_date_updated 		= $studentRow->user_date_updated;

													$replace_ID								= $studentRow->student_id;
													$replace_call_sign						= $studentRow->student_call_sign;
													$replace_time_zone  					= $studentRow->student_time_zone;
													$replace_timezone_offset				= $studentRow->student_timezone_offset;
													$replace_youth  						= $studentRow->student_youth;
													$replace_age  							= $studentRow->student_age;
													$replace_replace_parent 				= $studentRow->student_parent;
													$replace_replace_parent_email  			= strtolower($studentRow->student_parent_email);
													$replace_level  						= $studentRow->student_level;
													$replace_waiting_list 					= $studentRow->student_waiting_list;
													$replace_request_date  					= $studentRow->student_request_date;
													$replace_semester						= $studentRow->student_semester;
													$replace_notes  						= $studentRow->student_notes;
													$replace_welcome_date  					= $studentRow->student_welcome_date;
													$replace_email_sent_date  				= $studentRow->student_email_sent_date;
													$replace_email_number  					= $studentRow->student_email_number;
													$replace_response  						= strtoupper($studentRow->student_response);
													$replace_response_date  				= $studentRow->student_response_date;
													$replace_abandoned  					= $studentRow->student_abandoned;
													$replace_replace_status  				= strtoupper($studentRow->student_status);
													$replace_action_log  					= stripslashes($studentRow->student_action_log);
													$replace_pre_assigned_advisor  			= $studentRow->student_pre_assigned_advisor;
													$replace_selected_date  				= $studentRow->student_selected_date;
													$replace_no_catalog  					= $studentRow->student_no_catalog;
													$replace_hold_override  				= $studentRow->student_hold_override;
													$replace_assigned_advisor  				= $studentRow->student_assigned_advisor;
													$replace_advisor_select_date  			= $studentRow->student_advisor_select_date;
													$replace_advisor_class_timezone 		= $studentRow->student_advisor_class_timezone;
													$replace_hold_reason_code  				= $studentRow->student_hold_reason_code;
													$replace_class_priority  				= $studentRow->student_class_priority;
													$replace_assigned_advisor_class 		= $studentRow->student_assigned_advisor_class;
													$replace_promotable  					= $studentRow->student_promotable;
													$replace_excluded_advisor  				= $studentRow->student_excluded_advisor;
													$replace_replace_survey_completion_date	= $studentRow->student_survey_completion_date;
													$replace_available_class_days  			= $studentRow->student_available_class_days;
													$replace_intervention_required  		= $studentRow->student_intervention_required;
													$replace_copy_control  					= $studentRow->student_copy_control;
													$replace_first_class_choice  			= $studentRow->student_first_class_choice;
													$replace_second_class_choice  			= $studentRow->student_second_class_choice;
													$replace_third_class_choice  			= $studentRow->student_third_class_choice;
													$replace_first_class_choice_utc  		= $studentRow->student_first_class_choice_utc;
													$replace_second_class_choice_utc  		= $studentRow->student_second_class_choice_utc;
													$replace_third_class_choice_utc  		= $studentRow->student_third_class_choice_utc;
													$replace_catalog_options				= $studentRow->student_catalog_options;
													$replace_flexible						= $studentRow->student_flexible;
													$replace_date_created 					= $studentRow->student_date_created;
													$replace_date_updated			  		= $studentRow->student_date_updated;
													
													if ($replace_assigned_advisor == '') {
														//// add student to the advisor's class
														$inp_data			= array('inp_student'=>$replace_call_sign, 
																					'inp_semester'=>$replace_semester, 
																					'inp_assigned_advisor'=>$replacement_call_sign, 
																					'inp_assigned_advisor_class'=>$replacement_class, 
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
															if ($doDebug) {
																echo "attempting to add $replace_call_sign to $replacement_call_sign class failed:<br />$thisReason<br />";
															}
															sendErrorEmail("$jobname Attempting to add $replace_call_sign to $replacement_call_sign class failed:<br />$thisReason");
															$content		.= "Attempting to add $replace_call_sign to $replacement_call_sign class failed:<br />$thisReason<br />";
														} else {
															$content	.= "&nbsp;&nbsp;&nbsp;REPLACE student <a href='$studentUpdateURL?request_type=callsign&request_info=$replace_call_sign&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																			target='_blank'>$replacement_student</a> was replaced by <a href='$studentUpdateURL?request_type=callsign&request_info=$replace_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' 
																			target='_blank'>$replace_call_sign</a> in $advisorClass_advisor_call_sign $advisorClass_sequence class.<br />";
															$studentReplaced	= TRUE;
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp; student $replacement_student was replaced by $replace_call_sign<br />";
															}
															///// unset the replacement student in replaceArray
															if (isset(${'replaceArray' . $replacement_level}[$replacingSequence])) {
																unset(${'replaceArray' . $replacement_level}[$replacingSequence]);
																if ($doDebug) {
																	echo "did unset replaceArray $replacement_level $replacingSequence<br />";
																}
															} else {
																if ($doDebug) {
																	echo "<b>ERROR</b> replaceArray $replacement_level $replacingSequence not available to unset<br />";
																}
															}
														}
														//// format replacement email to advisor
																				
														///// send the email to the advisor
														$theSubject			= "CW Academy Replacement Student Information for your Class";
														if ($testMode) {
															$theRecipient			= 'rolandksmith@gmail.com';
															$theSubject				= "TESTMODE $theSubject";
															$mailCode				= 2;
														} else {
															$theRecipient			= $advisorEmail;
															$mailCode				= 14;
														}
															
														$thisContent			= "<p>To: $advisorClass_advisor_last_name, $advisorClass_advisor_first_name ($advisorClass_advisor_call_sign):</p>
																					<p>You have requested a replacement student for 
																					$replacement_student in your $replacement_level class number $advisorClass_sequence.</p>
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
															$content .= "&nbsp;&nbsp;&nbsp;&nbsp;An email was sent to <a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/?inp_callsign=$advisorClass_advisor_call_sign&strpass=2' target='_blank'>$theRecipient</a>.<br />";
															if ($doDebug) {
																echo $mailResult[1];
																echo "Replacement email sent to the advisor at $theRecipient<br />";
															}
															$studentEmailCount--;
														} else {
															$content .= "&nbsp;&nbsp;&nbsp;&nbsp;The replacement email send function to advisor $advisor_call_sign; email: $theRecipient failed.</p><br />";
															if ($doDebug) {
																echo $mailResult[1];
																echo "Replacement email FAILED to advisor at $theRecipient<br />";
															}
														}
														// add the reminder to the reminders table
														if ($doDebug) {
															echo "Adding reminder to reminders table<br />";
														}
														$myStr				= date('Y-m-d H:i:s');
														$closeStr			= strtotime("+10 days");
														$close_date			= date('Y-m-d H:i:s',$closeStr);
														$token				= mt_rand();
														$reminder_text		= "<b>Replacement Student:</b> Your class makeup has been revised and students need to be contacted 
and verified. Click on <a href='$advisorVerifyURL/?callsign=$advisorClass_advisor_call_sign&token=$token'>Advisor Verification of Students</a> to complete that task.";
														$inputParams		= array("effective_date|$myStr|s",
																					"close_date|$close_date|s",
																					"resolved_date||s",
																					"send_reminder|N|s",
																					"send_once|Y|s",
																					"call_sign|$advisorClass_advisor_call_sign|s",
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
														} else {
																				
															/// mark the replacement request as fulfilled
															$myStr 		= date("Y-m-d H:i:s");
															$replResult		= $wpdb->update($replacementRequests,
																							array('date_resolved'=>$myStr), 
																							array('record_id'=>$replacement_id), 
																							array('%s'), 
																							array('%d'));
															if ($replResult === FALSE) {
																$myError			= $wpdb->last_error;
																$myQuery			= $wpdb->last_query;
																if ($doDebug) {
																	echo "updating $replacementRequests table failed<br />
																		  wpdb->last_query: $myQuery<br />
																		  wpdb->last_error: $myError<br />";
																}
																$errorMsg			= "$jobname updating $replacementRequests failed.\nSQL: $myQuery\nError: $myError";
																sendErrorEmail($errorMsg);
																$content		.= "Unable to update $replacementRequests<br />";
															} else {
																$outstandingFulfilled++;
																					
																$replacedCount++;
																if ($doDebug) {
																	$debugLog	.= "replacement request marked as filled<br />";
																}
															}
														}
													} else {
														if ($doDebug) {
															echo "student already assigned to $replace_assigned_advisor<br />";
														}
														sendErrorEmail("$jobname doing replacements student $replace_call_sign was in the available replacement students but had $replace_assigned_advisor assigned");
													}
												}
											} else {
												if ($doDebug) {
													echo "Unable to retrieve the replacement student info ($replaceCallSign) from $studentTableName table<br />";
												}
												$content			.= "Unable to retrieve the replacement student info ($replaceCallSign) from $studentTableName table<br />";
											}
										}	
									} else {			/// no replacement found
										if ($doDebug) {
											echo "No replacement found<br />";
										}
										$outstandingNotFulfilled++;
										$notReplacedCount++;
										$content	.= "No replacement found for $replacement_student in $replacement_call_sign $replacement_level class $replacement_class<br />";
									}
								}
							} else {
								$content			.= "Attempting to do a replacement for $replacement_call_sign sequence $replacement_class. 
														Could not find the associated advisorclass record<br />";
							}
						}
					}
				} else {
					$content						.= "No outstanding replacement requests<br />";
				}
			}
			if ($doDebug) {
				echo "OUTSTANDING: end of outstanding replacement process<br />";
			}
		}
		
///////// end of outstanding replacement requests process
		
		




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

		$sql					= "select * from $studentTableName 
									left join $userMasterTableName on user_call_sign = student_call_sign 
									where (student_semester    = '$currentSemester' 
									       or student_semester = '$nextSemester' 
									       or student_semester = '$semesterTwo' 
									       or student_semester = '$semesterThree' 
									       or student_semester = '$semesterFour') 
									order by student_call_sign";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				$debugLog	.= "ran $sql<br />retrieved $numSRows rows from $studentTableName table<br >";
			}
			$studentRecordsFound		= $numSRows;
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_master_ID 					= $studentRow->user_ID;
					$student_master_call_sign 			= $studentRow->user_call_sign;
					$student_first_name 				= $studentRow->user_first_name;
					$student_last_name 					= $studentRow->user_last_name;
					$student_email 						= $studentRow->user_email;
					$student_phone 						= $studentRow->user_phone;
					$student_ph_code 					= $studentRow->user_ph_code;
					$student_city 						= $studentRow->user_city;
					$student_state 						= $studentRow->user_state;
					$student_zip_code 					= $studentRow->user_zip_code;
					$student_country_code 				= $studentRow->user_country_code;
					$student_country 					= $studentRow->user_country;
					$student_whatsapp 					= $studentRow->user_whatsapp;
					$student_telegram 					= $studentRow->user_telegram;
					$student_signal 					= $studentRow->user_signal;
					$student_messenger 					= $studentRow->user_messenger;
					$student_master_action_log 			= stripslashes($studentRow->user_action_log);
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
					$student_parent 						= $studentRow->student_parent;
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
					$student_action_log  					= stripslashes($studentRow->student_action_log);
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
					$student_survey_completion_date			= $studentRow->student_survey_completion_date;
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
								$content		.= "Student $student_last_name, $student_first_name (<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>) is on hold as a bad actor<br />";
							} else {
								if ($student_hold_reason_code != 'X') {
									$content		.= "Student $student_last_name, $student_first_name (<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a>) is on hold<br />";
								}
							}
						} else {
					
							if ($student_timezone_offset == -99.0 || $student_timezone_id == '' || $student_timezone_id == 'Unknown') {
								$errorMsg	= "student $student_call_sign has a timezone issue.<br />timezone_id: $student_timezone_id<br />timezone_offset: $student_timezone_offset. Abandoned: $student_abandoned";
								sendErrorEmail($errorMsg);
							}
							$myInt						= strpos($student_timezone_id,'zip');
							if ($myInt !== FALSE) {
								$errorMsg	= "student $student_call_sign has a zipTimeZone issue.<br />timezone_id: $student_timezone_id<br />timezone_offset: $student_timezone_offset. Abandoned: $student_abandoned";
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
								$updateFormat				= array();
							
								// handle verify mode
								$daysToSemester				= days_to_semester($student_semester);
								if ($verifyMode) {			
									if ($student_semester == $nextSemester) {
										$daysToSemester		= $initializationArray['daysToSemester'];
									}				
								}
								if ($doDebug) {
									echo "<br />Processing student $student_call_sign (ID: $student_ID). Data Read:<br />
										  &nbsp;&nbsp;&nbsp;Semester: $student_semester<br />
										  &nbsp;&nbsp;&nbsp;daysToSemester: $daysToSemester<br />
										  &nbsp;&nbsp;&nbsp;Level: $student_level<br />
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
								$passPhone		= substr($student_phone,-5,5);
								$statusString	= "inp_callsign=$student_call_sign&inp_email=$student_email&inp_phone=$student_phone&inp_level=$student_level&testMode=$testMode&verifyMode=$verifyMode";
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
										$result						= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
										if ($result[0] == 'FAIL') {
											if ($doDebug) {
												echo "utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
																  Error: $result[3]<br />";
											}
										} else {
											$utcTimes			= $result[1];
											$utcDays			= $result[2];
											$thisStr			= "$utcTimes $utcDays";
											$updateData[]		= "student_first_class_choice_utc|$thisStr|s";
											$doUpdateStudent		= TRUE;
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
										$result						= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
										if ($result[0] == 'FAIL') {
											if ($doDebug) {
												echo "utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
																  Error: $result[3]<br />";
											}
										} else {
											$utcTimes			= $result[1];
											$utcDays			= $result[2];
											$thisStr			= "$utcTimes $utcDays";
											$updateData[]		= "student_second_class_choice_utc|$thisStr|s";
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
										$result						= utcConvert('toutc',$student_timezone_offset,$thisTime,$thisDay);
										if ($result[0] == 'FAIL') {
											if ($doDebug) {
												echo "utcConvert failed 'toutc',$student_time_zone,$thisTime,$thisDay<br />
																Error: $result[3]<br />";
											}
										} else {
											$utcTimes			= $result[1];
											$utcDays			= $result[2];
											$thisStr			= "$utcTimes $utcDays";
											$updateData[]		= "student_third_class_choice_utc|$thisStr|s";
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
								}

								// Check if a response has been received
								if ($student_response == "" || $student_response == " ") {
									$student_responseTest = TRUE;		// student has not responded
								}
								if ($myWelcomeTest && $student_responseTest) {
									if ($student_email == '') {
										$content	.= "Email address for call sign <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a> at id $student_ID missing. No welcome email sent.<br /><br />";
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
								}
								if ($myWelcomeTest && $student_responseTest) {
									if ($doDebug) {
										echo "Welcome email for call sign $student_call_sign requested on $student_request_date<br />with a response of $student_response and a welcome of $student_welcome_date<br>";
									}

									if ($student_abandoned == 'Y') {			// send abandoned email only
										$emailContent = "To: $student_last_name, $student_first_name ($student_call_sign):
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



										$updateData[]				= "student_welcome_date|$checkDate|s";
										$student_welcome_date		= $checkDate;
										$log_welcome_date			= $checkDate;
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

										$emailContent = "To: $student_last_name, $student_first_name ($student_call_sign):<br />
														<p>Welcome to the CW Academy and thank you for your student registration!  You are currently $waiting for:
														<br />&nbsp;&nbsp;&nbsp;$student_level CW class
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
																<li> Access to a Windows PC or the Windows Operating System is needed for the Intermediate and Advanced Level classes</p>
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
										$updateData[]				= "student_welcome_date|$checkDate|s";
										$student_welcome_date		= $checkDate;
										$log_welcome_date			= $checkDate;
										$doUpdateStudent				= TRUE;
							
										/// if in the verification period (validEmailPeriod is Y), verify the student
										if ($validEmailPeriod == 'Y' || $daysToSemester < 30) {
											$update_action_log		.= "Student automaticially verified ";
											$updateData[]			= "student_response|Y|s";
											$updateData[]			= "student_response_date|$checkDate|s";
											$updateData[]			= "student_email_sent_date|$checkDate|s";
											$updateData[]			= "student_email_number|0|s";
											$student_response		= 'Y';
											$student_email_sent_date	= $checkDate;
											$student_email_number	= 0;
											$doUpdateStudent			= TRUE;
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
										$theRecipient	= $student_email;
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
									if ($student_semester == $nextSemester) {
										$numberVRecords++;
										$processStudent		= TRUE;
										/// setup the info to pass to student sign-up
										$passPhone				= substr($student_phone,-5,5);
										$stringToPass			= "inp_callsign=$student_call_sign&inp_phone=$passPhone&inp_email=$student_email&inp_mode=$inp_mode&strPass=2&inp_verbose=$inp_verbose&inp_verify=Y&verifyMode=$verifyMode";
										$enstrVerify			= base64_encode($stringToPass);
										$stringToPass			= "inp_callsign=$student_call_sign&inp_phone=$passPhone&inp_email=$student_email&inp_mode=$inp_mode&strPass=2&inp_verbose=$inp_verbose";
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
											if ($student_response == 'Y' and $student_first_class_choice == 'None') {
												// student has responded but not made a class choice. Send an email!
												// set up the reminder and send the email
												$returnArray		= wp_to_local($student_timezone_id, 0, 5);
												if ($returnArray === FALSE) {
													if ($doDebug) {
														echo "called wp_to_local with $student_timezone_id, 0, 5 which returned FALSE<br />";
													} else {
														sendErrorEmail("$jobname calling wp_to_local with $student_timezone_id, 0, 5 returned FALSE");
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
													$theRecipient		= $student_email;
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
														  <br />&nbsp;&nbsp;&nbsp;Name: $student_last_name, $student_first_name
														  <br />&nbsp;&nbsp;&nbsp;Email: $student_email
														  <br />&nbsp;&nbsp;&nbsp;Level: $student_level
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
																$updateData[]				= "student_request_date|$todaysDate|s";
																$log_request_date			= $todaysDate;
																$doUpdateStudent				= TRUE;
																$studentNoRequestDate++;
															}
															$theSubject 					= "ACTION REQUIRED: CWAcademy Morse Code Class Verification";
															if ($doDebug) {
																echo "&nbsp;&nbsp;&nbsp;Send email and set email_sent_date to $todaysDate and email_number to 1<br />";
															}
															$update_action_log					.= "VERIFY Set email_number to 1 and sent verification email ";
															$updateData[]						= "student_email_sent_date|$todaysDate|s";
															$updateData[]						= "student_email_number|1|d";
															$doUpdateStudent						= TRUE;
															$log_email_sent_date				= $todaysDate;
															$log_email_number					= '1';
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
																$updateData[]					= "student_email_number|2|d";
																$log_email_number				= '2';
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
																$updateData[]					= "student_email_number|3|d";
																$log_email_number				= '3';
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
																$updateData[]					= "student_email_number|4|d";
																$log_mail_number				= '4';
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
							
															$emailContent 	= "To: $student_last_name, $student_first_name ($student_call_sign):
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
															$reminder_text		= "<b>Select Class Schedule Preferences:</b> It is time to verify your availability to take a class in the upcoming semester 
and to select your class preferences. The class catalog is now available. Select one of the following:<br />
1. To verify your availability and indicate your desired class schedule click <a href='$studentRegistrationURL?inp_verify=Y&strpass=2&inp_verify=Y&token=$token&enstr=$encstr'>Select Class Preferences</a>. 
Students will be assigned to advisor classes in about three weeks<br />
2. You can move your registration to the $semesterTwo semester by clicking <a href='$TURemoveURL?appid=$student_ID&strpass=2&xmode=$xmode&inp_option=1&token=$token'>HERE</a><br />
3. You can move your registration to the $semesterThree semester by clicking <a href='$TURemoveURL?appid=$student_ID&strpass=2&xmode=$xmode&inp_option=2&token=$token'>HERE</a><br /> 
4. Finally, you can set your registration aside and sign up again in the future when your circumstances allow by clicking <a href='$TURemoveURL?appid=$student_ID&strpass=2&xmode=$xmode&inp_option=3&token=$token'>Cancel  
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
															$emailContent = "To: $student_last_name, $student_first_name ($student_call_sign):
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
															$theRecipient		= $student_email;
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




// Beginning of the handle replacement students process

/*	The Replacement Process

Student status of R (replace the student) or V (replace the student and return to 
	unassigned pool) triggers the replacement process
Do remove student
	if the student status is R, set remove status to C
	If the student status is V, set the remove status to blank
	
Find a possible replacement student
	The student level, the assigned advisor, and the assigned advisor class define the
		class. Get the class record and the class time and days in UTC
			Cycle through the replaceArray records using the level
				if the class day matches first class choice days and the class 
					times is within the students first class times, there is a match
				if no match, check the 2nd class choice
				if still no match, check the 3rd class choice
				if still no match, go on to the next replaceArray record
	If there is a match
		Get the replacement student record
			assign the student to the advisor class
			delete the replaceArray record so the student does not get double assigned
			send the email to the advisor
	If there is no match
		send the no replacement available to the advisor
		add the replacement request to the replacementRequsts table
	
*/

								if ($doDebug) {
									echo "<br />Replacement Student Process<br />";
								}
								if ($validReplacementPeriod == 'Y' && $currentSemester == 'Not in Session') {
									if ($student_status == 'R' || $student_status == 'V') {
										// make sure the student is assigned and has a class number
										if ($student_assigned_advisor == '' || $student_assigned_advisor_class == 0) {
											if ($doDebug) {
												echo "student assigned advisor is empty or assigned_advisor_class is 0<br />";
											}
										} else {
									
											// save off the assigned advisor and class so we can remove the student
											$str_assigned_advisor		= $student_assigned_advisor;
											$str_assigned_advisor_class	= $student_assigned_advisor_class;
											
											$content					.= "REPLACE Student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a> at level $student_level in advisor $str_assigned_advisor class $str_assigned_advisor_class<br />";
											if ($doDebug) {
												echo "REPLACE Student $student_call_sign at level $student_level in advisor $str_assigned_advisor class $str_assigned_advisor_class<br />";
											}
											// Now remove the student from the advisor's class
											if ($student_status == 'V') {
												$removeStatus	= '';
												$updateData[]		= 'student_intervention_required|H|s';
												$doUpdateStudent	= TRUE;
	
												// add the reminder
												if ($doDebug) {
													echo "adding reminder to reminders table<br />";
												}
												$effective_date		= date('Y-m-d H:i:s');
												$closeStr			= strtotime("+10 days");
												$close_date			= date('Y-m-d H:i:s', $closeStr);
												$token				= mt_rand();
												$reminder_text		= "<b>Signup on Hold:</b> Student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a> 
had a student status of V and is on hold waiting for a possible reassignment. When the issue is resolved, click 'Remove Item'.";
												$inputParams		= array("effective_date|$effective_date|s",
																			"close_date|$close_date|s",
																			"resolved_date||s",
																			"send_reminder||s",
																			"send_once||s",
																			"call_sign||s",
																			"role|administrator|s",
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
												} else {
													if ($doDebug) {
														$debugLog	.= "On hold reminder set<br />";
													}
													// put the student on hold so the student does not get processed again
													$updateData[] 	= 'student_intervention_required|H|s';
													$updateData[]	= 'student_status||s';
													$update_action_log	.= "placed student on hold ";
												}
	
											} else {
												$removeStatus	= 'C';
											}
											$inp_data			= array('inp_student'=>$student_call_sign,
																		'inp_semester'=>$student_semester,
																		'inp_assigned_advisor'=>$student_assigned_advisor,
																		'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
																		'inp_remove_status'=>$removeStatus,
																		'inp_arbitrarily_assigned'=>'',
																		'inp_method'=>'remove',
																		'jobname'=>$jobname,
																		'userName'=>$userName,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
					
											$removeResult		= add_remove_student($inp_data);
											if ($removeResult[0] === FALSE) {
												$thisReason		= $removeResult[1];
												if ($doDebug) {
													echo "attempting to remove $student_call_sign from $advisorClass_advisor_call_sign class failed:<br />$thisReason<br />";
												}
												sendErrorEmail("$jobname Attempting to remove $student_call_sign from $advisorClass_advisor_call_sign class failed:<br />$thisReason");
												$content		.= "Attempting to remove $student_call_sign from $advisorClass_advisor_call_sign class failed:<br />$thisReason<br />";
											} else {
												if ($doDebug) {
													$debugLog	.= "Student removed from advisor class<br />";
												}
											}
											
											/// figure out if there is a replacement student
											//// get the advisor and advisorClass records
					
											$sql			= "select * from $advisorClassTableName  
																left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
																where advisorclass_call_sign = '$str_assigned_advisor' 
																and advisorclass_sequence = $str_assigned_advisor_class 
																and advisorclass_semester = '$theSemester'";
					
											$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
											if ($wpw1_cwa_advisorclass === FALSE) {
												handleWPDBError($jobname,$doDebug);
											} else {
												$numACRows						= $wpdb->num_rows;
												if ($doDebug) {
													$myStr						= $wpdb->last_query;
													echo "ran $myStr<br />and found $numACRows rows<br />";
												}
												if ($numACRows > 0) {
													foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
														$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
														$advisorClass_advisor_call_sign 		= $advisorClassRow->advisorclass_call_sign;
														$advisorClass_advisor_first_name 		= $advisorClassRow->user_first_name;
														$advisorClass_advisor_last_name 		= $advisorClassRow->user_last_name;
														$advisorEmail							= $advisorClassRow->user_email;
														$advisorClass_sequence					= $advisorClassRow->advisorclass_sequence;
														$advisorClass_level						= $advisorClassRow->advisorclass_level;
														$advisorClass_action_log				= stripslashes($advisorClassRow->advisorclass_action_log);
														$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
														$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
														$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
														$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
	
														///// find a replacement student
														$searchTime = intval($advisorClass_class_schedule_times_utc);
														$searchDays = $advisorClass_class_schedule_days_utc;
														if ($doDebug) {
															echo "&nbsp;&nbsp;&nbsp;&nbsp;Looking for a replacement level $student_level $searchTime $searchDays<br />";
														}
														$callSign1	= '';
														$sequence1	= '';
														$callSign2	= '';
														$sequence2	= '';
														$callSign3	= '';
														$sequence3	= '';
	
														foreach(${'replaceArray' . $student_level} as $myKey=>$myValue) {
															if ($doDebug) {
																echo "<br />Checking possible replacement: $myKey - $myValue<br />";
															}
															$myArray = explode("|",$myValue);
															$student_sequence		= $myArray[0];
															$thisCallSign 		= $myArray[1];
															$firstTime 			= $myArray[2];
															$firstDays 			= $myArray[3];
															$secondTime 		= $myArray[4];
															$secondDays 		= $myArray[5];
															$thirdTime 			= $myArray[6];
															$thirdDays 			= $myArray[7];
															$excludedAdvisor	= $myArray[8];
								
															$myInt				= strpos($excludedAdvisor,$advisorClass_advisor_call_sign);
															if ($myInt === FALSE) {
																if ($searchDays == $firstDays) {
																	if ($doDebug) {
																		echo "first choice match on $searchDays. Checking poss replacement first choice $firstTime<br />";
																	}
																	if ($callSign1 == '') {		// don't continue if have a match
																		$searchBegin = intval($firstTime) - 300;
																		$searchEnd = intval($firstTime) + 300;
																		if ($searchBegin < 0) {
																			$searchBegin	= 0;
																		}
																		if ($searchEnd > 2400) {
																			$searchEnd		= 2400;
																		}
																		if ($doDebug) {
																			echo "testing firstTime: $firstTime. Looking for student between $searchBegin and $searchEnd<br />";
																		}
																		if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
																			$callSign1 = $thisCallSign;
																			$sequence1 = $myKey;
																			if ($doDebug) {
																				echo "1: Found $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd<br />
																								  Set callSign1 to $thisCallSign and sequence1 to $myKey<br />";
																			}
																		} else {
																			if ($doDebug) {
																				echo "1: No go $searchDays in $firstDays and $searchTime between $searchBegin and $searchEnd<br />";
																			}
																		}
																	}
																} else {
																	if ($doDebug) {
																		echo "Searching first choice days did not match<br />";
																	}
									
																}
																if ($searchDays == $secondDays) {
																	if ($doDebug) {
																		echo "second choice match on searchDays. Doing second choice $secondTime<br />";
																	}
																	if ($callSign2 == '') {
																		$searchBegin 	= intval($secondTime) - 300;
																		$searchEnd 		= intval($secondTime) + 300;
																		if ($searchBegin < 0) {
																			$searchBegin	= 0;
																		}
																		if ($searchEnd > 2400) {
																			$searchEnd		= 2400;
																		}
																		if ($doDebug) {
																			echo "testing secondTime: $secondTime. Looking for student between $searchBegin and $searchEnd<br />";
																		}
																		if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
																			$callSign2 = $thisCallSign;
																			$sequence2 = $myKey;
																			if ($doDebug) {
																				echo "2: Found $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd<br />
																				Set callSign2 to $thisCallSign and sequence2 to $myKey<br />";
																			}
																		} else {
																			if ($doDebug) {
																				echo "2: No go $searchDays in $secondDays and $searchTime between $searchBegin and $searchEnd<br />";
																			}
																		}
																	} else {
																		if ($doDebug) {
																			echo "searching second choice days did not match<br />";
																		}
																	}
																} else {
																	if ($doDebug) {
																		echo "searching second choice days didn't match<br />";
																	}
																}
																if ($searchDays == $thirdDays) {
																	if ($doDebug) {
																		echo "third choice match on searchDays. Doing third choice $thirdTime<br />";
																	}
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
																		if ($doDebug) {
																			echo "testing thirdTime: $thirdTime. Looking for student between $searchBegin and $searchEnd<br />";
																		}
																		if ($searchTime >= $searchBegin && $searchTime < $searchEnd) {
																			$callSign3 = $thisCallSign;
																			$sequence3 = $myKey;
																			if ($doDebug) {
																				echo "3: Found $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd<br />
																								  Set callSign3 to $thisCallSign and sequence3 to $myKey<br />";
																			}
																		} else {
																			if ($doDebug) {
																				echo "3: No go $searchDays in $thirdDays and $searchTime between $searchBegin and $searchEnd<br />";
																			}
																		}
																	}	
																} else {
																	if ($doDebug) {
																		echo "searching third choice days did not match<br />";
																	}
																}
															} else {
																if ($doDebug) {
																	echo "possible advisor $advisor_call_sign is excluded: $excludedAdvisor<br />";
																}
															} 
														}
														if ($doDebug) {
															echo "CallSign1: $callSign1 | $sequence1<br />
																  CallSign2: $callSign2 | $sequence2<br />
																  CallSign3: $callSign3 | $sequence3<br />";
														}
	
														$gotAReplacement			= FALSE;
														if ($callSign1 != '') {
															$replacingCallSign		= $callSign1;
															$replacingSequence		= $sequence1;
															$gotAReplacement		= TRUE;
														} elseif ($callSign2 != '') {
															$replacingCallSign		= $callSign2;
															$replacingSequence		= $sequence2;
															$gotAReplacement		= TRUE;
														} elseif ($callSign3 != '') {
															$replacingCallSign		= $callSign3;
															$replacingSequence		= $sequence3;
															$gotAReplacement		= TRUE;
														}
							
														if ($gotAReplacement) {
															if ($doDebug) {
																echo "Have a replacement $replacingCallSign. Getting student record<br />";
															}
															///// get the replacement student record
															$sql						= "select * from $studentTableName 
																							left join $userMasterTableName on user_call_sign = student_call_sign 
																							where student_semester='$theSemester' 
																							and student_call_sign='$replacingCallSign'";
															$wpw1_cwa_replace				= $wpdb->get_results($sql);
															if ($wpw1_cwa_replace === FALSE) {
																handleWPDBError($jobname,$doDebug);
															} else {
																$numRRows				= $wpdb->num_rows;
																if ($doDebug) {
																	echo "ran $sql<br />and retrieved $numRRows rows from $studentTableName table<br />";
																}
																if ($numRRows > 0) {
																	foreach ($wpw1_cwa_replace as $replaceRow) {
																		$replace_master_ID 					= $replaceRow->user_ID;
																		$replace_master_call_sign 			= $replaceRow->user_call_sign;
																		$replace_first_name 				= $replaceRow->user_first_name;
																		$replace_last_name 					= $replaceRow->user_last_name;
																		$replace_email 						= $replaceRow->user_email;
																		$replace_ph_code					= $replaceRow->user_ph_code;
																		$replace_phone 						= $replaceRow->user_phone;
																		$replace_city 						= $replaceRow->user_city;
																		$replace_state 						= $replaceRow->user_state;
																		$replace_zip_code 					= $replaceRow->user_zip_code;
																		$replace_country_code 				= $replaceRow->user_country_code;
																		$replace_country	 				= $replaceRow->user_country;
																		$replace_whatsapp 					= $replaceRow->user_whatsapp;
																		$replace_telegram 					= $replaceRow->user_telegram;
																		$replace_signal 					= $replaceRow->user_signal;
																		$replace_messenger 					= $replaceRow->user_messenger;
																		$replace_master_action_log 			= stripslashes($replaceRow->user_action_log);
																		$replace_timezone_id 				= $replaceRow->user_timezone_id;
																		$replace_languages 					= $replaceRow->user_languages;
																		$replace_survey_score 				= $replaceRow->user_survey_score;
																		$replace_is_admin					= $replaceRow->user_is_admin;
																		$replace_role 						= $replaceRow->user_role;
																		$replace_master_date_created 		= $replaceRow->user_date_created;
																		$replace_master_date_updated 		= $replaceRow->user_date_updated;
					
																		$replace_ID								= $replaceRow->student_id;
																		$replace_call_sign						= $replaceRow->student_call_sign;
																		$replace_time_zone  					= $replaceRow->student_time_zone;
																		$replace_timezone_offset				= $replaceRow->student_timezone_offset;
																		$replace_youth  						= $replaceRow->student_youth;
																		$replace_age  							= $replaceRow->student_age;
																		$replace_replace_parent 				= $replaceRow->student_parent;
																		$replace_replace_parent_email  			= strtolower($replaceRow->student_parent_email);
																		$replace_level  						= $replaceRow->student_level;
																		$replace_waiting_list 					= $replaceRow->student_waiting_list;
																		$replace_request_date  					= $replaceRow->student_request_date;
																		$replace_semester						= $replaceRow->student_semester;
																		$replace_notes  						= $replaceRow->student_notes;
																		$replace_welcome_date  					= $replaceRow->student_welcome_date;
																		$replace_email_sent_date  				= $replaceRow->student_email_sent_date;
																		$replace_email_number  					= $replaceRow->student_email_number;
																		$replace_response  						= strtoupper($replaceRow->student_response);
																		$replace_response_date  				= $replaceRow->student_response_date;
																		$replace_abandoned  					= $replaceRow->student_abandoned;
																		$replace_replace_status  				= strtoupper($replaceRow->student_status);
																		$replace_action_log  					= stripslashes($replaceRow->student_action_log);
																		$replace_pre_assigned_advisor  			= $replaceRow->student_pre_assigned_advisor;
																		$replace_selected_date  				= $replaceRow->student_selected_date;
																		$replace_no_catalog  					= $replaceRow->student_no_catalog;
																		$replace_hold_override  				= $replaceRow->student_hold_override;
																		$replace_assigned_advisor  				= $replaceRow->student_assigned_advisor;
																		$replace_advisor_select_date  			= $replaceRow->student_advisor_select_date;
																		$replace_advisor_class_timezone 		= $replaceRow->student_advisor_class_timezone;
																		$replace_hold_reason_code  				= $replaceRow->student_hold_reason_code;
																		$replace_class_priority  				= $replaceRow->student_class_priority;
																		$replace_assigned_advisor_class 		= $replaceRow->student_assigned_advisor_class;
																		$replace_promotable  					= $replaceRow->student_promotable;
																		$replace_excluded_advisor  				= $replaceRow->student_excluded_advisor;
																		$replace_replace_survey_completion_date	= $replaceRow->student_survey_completion_date;
																		$replace_available_class_days  			= $replaceRow->student_available_class_days;
																		$replace_intervention_required  		= $replaceRow->student_intervention_required;
																		$replace_copy_control  					= $replaceRow->student_copy_control;
																		$replace_first_class_choice  			= $replaceRow->student_first_class_choice;
																		$replace_second_class_choice  			= $replaceRow->student_second_class_choice;
																		$replace_third_class_choice  			= $replaceRow->student_third_class_choice;
																		$replace_first_class_choice_utc  		= $replaceRow->student_first_class_choice_utc;
																		$replace_second_class_choice_utc  		= $replaceRow->student_second_class_choice_utc;
																		$replace_third_class_choice_utc  		= $replaceRow->student_third_class_choice_utc;
																		$replace_catalog_options				= $replaceRow->student_catalog_options;
																		$replace_flexible						= $replaceRow->student_flexible;
																		$replace_date_created 					= $replaceRow->student_date_created;
																		$replace_date_updated			  		= $replaceRow->student_date_updated;
	
																		// make sure the student really unassigned
																		if ($replace_assigned_advisor == '') {
																			//// add student to the advisor's class
																			$inp_data			= array('inp_student'=>$replace_call_sign,
																										'inp_semester'=>$replace_semester,
																										'inp_assigned_advisor'=>$advisorClass_advisor_call_sign,
																										'inp_assigned_advisor_class'=>$advisorClass_sequence ,
																										'inp_remove_status'=>'', 
																										'inp_arbitrarily_assigned'=>'',
																										'inp_method'=>'add',
																										'jobname'=>$jobname,
																										'userName'=>$userName,
																										'testMode'=>$testMode,
																										'doDebug'=>$doDebug);
																			if ($doDebug) {
																				echo "Attempting to add $replace_call_sign to $advisorClass_advisor_call_sign<br /><pre>";
																				print_r($inp_data);
																				echo "</pre><br />";
																			}
										
																			$addResult			= add_remove_student($inp_data);
																			if ($addResult[0] === FALSE) {
																				$thisReason		= $addResult[1];
																				if ($doDebug) {
																					echo "attempting to add $student_call_sign to $advisorClass_advisor_call_sign class failed:<br />$thisReason<br />";
																				}
																				sendErrorEmail("$jobname Attempting to add $student_call_sign to $advisorClass_advisor_call_sign class failed:<br />$thisReason");
																				$content		.= "Attempting to add $student_call_sign to $advisorClass_advisor_call_sign class failed:<br />$thisReason<br />";
																			} else {
																				$content	.= "&nbsp;&nbsp;&nbsp;REPLACE student <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$student_call_sign</a> was replaced by <a href='$studentUpdateURL?request_type=callsign&request_info=$replace_call_sign&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode&strpass=2' target='_blank'>$replace_call_sign</a> in $str_assigned_advisor $str_assigned_advisor_class class.<br />";
																				$studentReplaced	= TRUE;
																				if ($doDebug) {
																					echo "student $student_call_sign was replaced by $replace_call_sign<br />";
																				}
																				///// unset the replacement student in replaceArray
																				if (isset(${'replaceArray' . $advisorClass_level}[$replacingSequence])) {
																					unset(${'replaceArray' . $advisorClass_level}[$replacingSequence]);
																					if ($doDebug) {
																						echo "did unset replaceArray$advisorClass_level $replacingSequence<br />";
																					}
																				}
																				//// format replacement email to advisor
																				$email_content			= "<p>You requested a replacement student. A replacement has been found and 
																											added to your class. Please go to <a href='$siteURL/program-list/'>CW Academy</a> 
																											and follow the instructions there.";
																				if ($doDebug) {
																					echo "adding reminder to reminders table<br />";
																				}
																				$closeStr				= strtotime("+5 days");
																				$close_date				= date('Y-m-d H:i:s',$closeStr);
																				$token					= mt_rand();
																				$effective_date			= date('Y-m-d H:i:s');
																				$reminder_text			= "<b>Manage Students:</b> To see your current class makeup and and verify the student(s) click on 
<a href='$siteURL/cwa-manage-advisor-class?strpass=2&callsign=$advisorClass_advisor_call_sign&token=$token'>Manage Advisor Class</a> to complete that task.";
																				$inputParams		= array("effective_date|$effective_date|s",
																											"close_date|$close_date|s",
																											"resolved_date||s",
																											"send_reminder|n|s",
																											"send_once|Y|s",
																											"call_sign|$str_assigned_advisor|s",
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
																				} else {
																					$replacedCount++;
																				}
																			}
																		} else {
																			if ($doDebug) {
																				echo "$replace_call_sign is assigned to $replace_assigned_advisor class $replace_assigned_advisor_class<br />";
																			}
																		}
																	}
																} else {
																	if ($doDebug) {
																		echo "Unable to retrieve the replacement student info ($replaceCallSign) from $studentTableName table (no record found)<br />";
																	}
																	$content			.= "Unable to retrieve the replacement student info ($replaceCallSign) from $studentTableName table (no record found)<br />";
																}
															}	
														} else {			/// no replacement found
															if ($doDebug) {
																echo "No replacement found<br />";
															}
															//// format email to the advisor
															$email_content			= "<p>Unfortunately, no students are available for 
assignment that meet the criteria for your class.<p>";
															$notReplacedCount++;
															// update advisorClass record with the replacement information
															if ($doDebug) {
																echo "Updating advisorClass record with the relplacement information<br />";
															}
															$advisorClass_action_log	= "$advisorClass_action_log / $actionDate CRON Advisor requested replacement for $student_call_sign. No replacement found ";
															$classUpdateData		= array('tableName'=>$advisorClassTableName,
																							'inp_method'=>'update',
																							'inp_data'=>array('advisorclass_action_log'=>$advisorClass_action_log),
																							'inp_format'=>array('%s'),
																							'jobname'=>$jobname,
																							'inp_id'=>$advisorClass_ID,
																							'inp_callsign'=>$str_assigned_advisor,
																							'inp_semester'=>$theSemester,
																							'inp_sequence'=>$advisorClass_sequence,
																							'inp_who'=>$userName,
																							'testMode'=>$testMode,
																							'doDebug'=>$doDebug);
															$updateResult	= updateClass($classUpdateData);
															if ($updateResult[0] === FALSE) {
																$myError	= $wpdb->last_error;
																$mySql		= $wpdb->last_query;
																$errorMsg	= "$jobname Processing $str_assigned_advisor in $advisorClassTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
																if ($doDebug) {
																	echo $errorMsg;
																}
																sendErrorEmail($errorMsg);
																$content		.= "Unable to update content in $advisorClassTableName<br />";
															} else {		// if update is successful, add the record to replacementRequests table
																$replParams 	= array('call_sign'=>$advisorClass_advisor_call_sign,
																						'class'=>$advisorClass_sequence,
																						'level'=>$advisorClass_level,
																						'semester'=>$student_semester,
																						'student'=>$student_call_sign);
	
																$replFormat 	= array('%s','%d','%s','%s','%s');
										
																$insertResult	= $wpdb->insert($replacementRequests,
																								$replParams,
																								$replFormat);
																if ($insertResult === FALSE) {
																	$myError			= $wpdb->last_error;
																	$myQuery			= $wpdb->last_query;
																	if ($doDebug) {
																		echo "Inserting into $replacementRequests table failed<br />
																			  wpdb->last_query: $myQuery<br />
																			  wpdb->last_error: $myError<br />";
																	}
																	$errorMsg			= "$jobname inserting $replacementRequests failed while attempting to move to past_student. <p>SQL: $myQuery</p><p> Error: $myError</p>";
																	sendErrorEmail($errorMsg);
																	$content		.= "Unable to insert into $replacementRequests<br />";
																} else {
																	$newID			= $wpdb->insert_id;
																	if ($doDebug) {
																		$myStr			= $wpdb->last_query;
																		echo "ran $myStr<br />and inserted $newID into $replacementRequests<br />";
																	}
																	$outstandingRequests++;
																}
	
															}
														}		
														///// send the email to the advisor
														$theSubject			= "CW Academy Replacement Student Information for your Class";
														if ($testMode) {
															$theRecipient			= 'rolandksmith@gmail.com';
															$theSubject				= "TESTMODE $theSubject";
															$mailCode				= 2;
														} else {
															$theRecipient			= $advisorEmail;
															$mailCode				= 14;
														}
														$strSemester				= $currentSemester;
														if ($currentSemester == 'Not in Session') {
															$strSemester 			= $nextSemester;
														}
														$thisContent			= "<p>To: $advisorClass_advisor_last_name, $advisorClass_advisor_first_name ($advisorClass_advisor_call_sign):</p>
																					<p>You have requested a replacement student for $student_last_name, $student_first_name 
																					($student_call_sign) in your $student_level class number $advisorClass_sequence.</p>
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
															if ($doDebug) {
																echo $mailResult[1];
																echo "Replacement email sent to the advisor at $theRecipient<br />";
															}
															$studentEmailCount--;
														} else {
															$content .= "&nbsp;&nbsp;&nbsp;&nbsp;The replacement email send function to advisor $advisor_call_sign; email: $theRecipient failed.<br />";
															if ($doDebug) {
																echo $mailResult[1];
																echo "Replacement email FAILED to advisor at $theRecipient<br />";
															}
														}
													}
												} else {		//// no records found in advisorClass tabke
													if ($doDebug) {
														echo "No matching advisorClass record<br />";
													}
													$content	.= "<p>Student $student_call_sign replacement was requested by 
																	advisor $str_assigned_advisor in the advisor's $str_assigned_advisor_class class. No 
																	$advisorClassTableName pod record found for that class. No replacement made.</p>";
												}
											}
										}
									}
								} else {
									if ($doDebug) {
										echo "End of replacement process<br />";
									}
								}			///// end of replacement process
			
			
								//////// update student process				
								if ($doUpdateStudent) {
									$student_action_log					= "$student_action_log / $actionDate $update_action_log";
									$updateData[]						= "student_action_log|$student_action_log|s";
									if ($doDebug) {
										echo "<br />Updating student record for $student_call_sign in table $studentTableName:<br /><pre>";
										print_r($updateData);
										echo "</pre><br />";
									}
									$updateStudent	= array('tableName'=>$studentTableName,
														'inp_data'=>$updateData,
														'inp_method'=>'update',
														'jobname'=>'CRON',
														'inp_id'=>$student_ID,
														'inp_callsign'=>$student_call_sign,
														'inp_semester'=>$student_semester,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
									$updateResult	= updateStudent($updateStudent);
								
									if ($updateResult[0] === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {
										if ($doDebug) {
											echo "$student_call_sign record in $studentTableName successfully updated<br />";
										}
										
										// reread student record to do the counts
										$sql		= "select * from $studentTableName 
														where student_id = $student_ID";
										
										$wpw1_cwa_student		= $wpdb->get_results($sql);
										if ($wpw1_cwa_student === FALSE) {
											$myError			= $wpdb->last_error;
											$myQuery			= $wpdb->last_query;
											if ($doDebug) {
												echo "Reading $studentTableName table failed<br />
													  wpdb->last_query: $myQuery<br />
													  wpdb->last_error: $myError<br />";
											}
											$errorMsg			= "Daily Student Cron (B) reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>r";
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
													$student_call_sign						= $studentRow->student_call_sign;
													$student_time_zone  					= $studentRow->student_time_zone;
													$student_timezone_offset				= $studentRow->student_timezone_offset;
													$student_youth  						= $studentRow->student_youth;
													$student_age  							= $studentRow->student_age;
													$student_parent 						= $studentRow->student_parent;
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
													$student_action_log  					= stripslashes($studentRow->student_action_log);
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
													$student_survey_completion_date			= $studentRow->student_survey_completion_date;
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
												}
											} else {
												sendErrorEmail("$jobname rereading student record for student_id $student_ID ($student_call_sign) failed");
											}
										}
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
				}				// end of the student foreach		((((this is true))))
				if ($doDebug) {
					echo "STUDENT end of student process<br />";
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
						<tr><th colspan='2'>Replacement Process</td></tr>
						<tr><td style='text-align:right;'>$outstandingRequests</td><td>Outstanding Replacement Requests.</td></tr>
						<tr><td style='text-align:right;'>$outstandingFulfilled</td><td>Outstanding Replacement Requests Fulfilled.</td></tr>
						<tr><td style='text-align:right;'>$outstandingNotFulfilled</td><td>Outstanding Replacement Requests Still Open.<br /></td></tr>
						<tr><td style='text-align:right;'>$replacedCount</td><td>Replacement requests were able to be fulfilled.</td></tr>
						<tr><td style='text-align:right;'>$notReplacedCount</td><td>Replacment requests were NOT able to be fulfilled.</td></tr>
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
add_shortcode ('daily_student_cron_v4', 'daily_student_cron_v4_func');
