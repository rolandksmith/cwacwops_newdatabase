function assign_students_to_advisors_func() {

/*	Run Options

	if 'Specific Advisors' is checked and the list of specific advisors is entered into 
	the textbox, then those advisors will be the only ones assigned students. 
	
	If 'All Advisors' is checked then students will be assigned to all advisors 
	except those which have already been assigned
	
	Class Size Overrides and Class Size Override Exemptions will be applied to the 
	Run Options noted above
	
	All of these options are stored in wpw1_cwa_program_settings under the program 
		assign_students_to_advisor_v3, one record for each job execution
		
	The settings are read at the beginning of the program and can be updated. Changed 
	settings are added to the settings file for the next run.
	

	advisorArray[advisor_call_sign]['first name'] 			= advisor_first_name;
									['last name'] 			= advisor_last_name;
									['email'] 				= advisor_email;
									['phone'] 				= advisor_phone;
									['text message'] 		= advisor_text_message;
									['state'] 				= advisor_state;
									['country'] 			= advisor_country;
									['time zone'] 			= advisor_time_zone;
									['fifo date'] 			= advisor_fifo_date;
	    
	studentArray[student_call_sign]['first name']			= student_first_name;
								   ['last name']			= student_last_name;
								   ['email']				= student_email;
								   ['phone']				= student_phone;
								   ['text message']			= student_text_message;
								   ['state']				= student_state;
								   ['country']				= student_country;
								   ['time zone']			= student_timezone_offset;
								   ['response']				= student_response;
								   ['status']				= student_status;
								   ['youth']				= student_youth;
								   ['parent email']			= student_parent_email;
								   ['class_language']		= student_class_language
									
	classArray[classKey][advisorCalcSequence][advisor_call_sign]['seq'] = advisorclass_sequence
	
	advisorClassArray[advisor_call_sign|advisorclass_sequence][0]				= available seats
															  [inc]				= student_call_sign
															  ['size']			= advisorclass_class_size
															  ['seq']			= advisorclass_sequence
															  ['level']			= advisorclass_level
															  ['language'] 		= advisorclass_language
															  ['time zone']		= advisorclass_timezone
															  ['time utc']		= advisorclass_class_schedule_days_utc
															  ['days utc']		= advisorclass_class_schedule_times_utc
															  ['time local']	= advisorclass_class_schedule_days
															  ['days local']	= advisorclass_class_schedule_times
	
	
	processStudentArray[student_level|student_class_language|student_class_priority|requestDate|student_call_sign|firstTimes|firstDays|secondTimes|secondDays|thirdTimes|thirdDays|student_timezone_offset";

	studentAssignedAdvisorArray[student_call_sign] = |	student_advisor|student_assigned_advisor_class
	
	arbitraryArray[student_call_sign]
	
	preAssignedArray[student_call_sign]
	
	smallClassesArray[$advisorCallSign|$advisorclass_sequence|$advisorclass_language|$thisClassStudents|$advisorclass_class_size|$advisorclass_level|$advisorclass_timezone|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_class_schedule_times_utc $advisorclass_class_schedule_days_utc]
					}

	seatsOpenArray[advisorCallSign|seatsOpen|advisorclass_class_size|advisorclass_level|advisorclass_language|advisorclass_timezone|advisorclass_class_schedule_times advisorclass_class_schedule_days|advisorclass_class_schedule_times_utc advisorclass_class_schedule_days_utc";
	
	unassignedArray[studentLevel|studentCallSign|studentTimeZone]
	
	AdvancedExemptionsArray
	IntermediateExemptionsArray
	FundamentalExemptionsArray
	BeginnerExemptionsArray



*/

	global $wpdb, $doDebug, $testMode, $advisorArray, $classArray, $advisorClassArray, 
	$processStudentArray, $studentAssignedAdvisorArray, $arbitraryArray, $theIncrement, 
	$doDebugReport, $keepDebug; 

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$bobTest						= FALSE;
	$keepDebug						= TRUE;
	$doDebugReport					= "";
	
//// FUNCTION debugReport
	function debugReport($debugValue) {
	
		global $wpdb, $doDebug, $testMode, $advisorArray, $classArray, $advisorClassArray,
	 		$processStudentArray, $studentAssignedAdvisorArray, $arbitraryArray, $theIncrement, 
	 		$doDebugReport, $keepDebug; 
	
	
		$doDebugReport		.= $debugValue;
		if ($doDebug) {
			echo $debugValue;
		}
		return;
	}
	
	
	$initializationArray = data_initialization_func();
		$thisStr	= "Initialization Array:<br /><pre>";
		$thisStr 	.= print_r($initializationArray,TRUE);
		$thisStr	.= "</pre><br />\n";
		debugReport($thisStr);
	
	
	
	$validUser 				= $initializationArray['validUser'];
	$userName				= $initializationArray['userName'];
	$siteURL   				= $initializationArray['siteurl'];
	$currentSemester		= $initializationArray['currentSemester'];
	$nextSemester			= $initializationArray['nextSemester'];
	$theSemester			= $currentSemester;
	if ($theSemester == 'Not in Session') {
		$theSemester		= $nextSemester;
	}
	
// CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',0);

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$testEmail						= 'rolandksmith@gmail.com';
//	$testEmail						= 'kcgator@gmail.com';
	$strPass						= "1";
	$theURL							= "$siteURL/cwa-assign-students-to-advisors/";
	$jobname						= "Assign Students to Advisors";
	$studentManagementURL			= "$siteURL/cwa-student-management/";
	$inp_semester					= '';
	$theStatement					= '';
	$totalStudentsOnHold			= 0;
	$theIncrement					= 0;
	$arbitraryAssignedCount			= 0;
	$firstChoiceCount				= 0;
	$secondChoiceCount				= 0;
	$thirdChoiceCount				= 0;
	$preAssignedCount				= 0;
	$totalStudents					= 0;
	$advisorMailCount				= 0;
	$increment1						= 0;
	$increment2						= 0;
	$inp_report						= '';
	$inp_addlInfo					= '';
	$actionDate						= date('Ymd Hi');
	$logDate						= date('Y-m-d H:i:s');
	$advisorArray					= array();
	$classArray						= array();
	$studentClassArray				= array();
	$advisorAssignedStudentArray	= array();
	$studentAssignedAdvisorArray	= array();
	$seatsOpenArray					= array();
	$smallClassesArray				= array();
	$errorArray						= array();
	$holdArray						= array();
	$arbitraryArray					= array();
	$preAssignedArray				= array();
	$overrideArray					= array();
	$defaultClassSize				= $initializationArray['defaultClassSize'];
	$userName						= $initializationArray['userName'];
	$studentUpdateURL				= "$siteURL/cwa-display-and-update-student-signup-information/";
	$advisorUpdateURL				= "$siteURL/cwa-display-and-update-advisor-signup-information/";
	$unassignedArraySequence		= 0;
	$studentTrace					= "";
	
	$showAdvisorClasses				= TRUE;
	
	$fieldTest						= array('action_log','post_status','post_title','control_code');
										
	$daysTestArray				= array('Sunday,Wednesday',
										'Monday,Thursday',
										'Monday,Friday',
										'Tuesday,Thursday',
										'Tuesday,Friday',
										'Tuesday,Saturday',
										'Wednesday,Friday',
										'Wednesday,Saturday');

	$timeZoneConverter		= array('-12'=>'UTC -12 (Pacific Islands)',
									'-11'=>'UTC -11 (Pacific Islands)',
									'-10'=>'UTC -10 (Hawaii Time)',
									'-9'=>'UTC -9 (Alaska Time)',
									'-8'=>'UTC -8 (Pacific Time)',
									'-7'=>'UTC -7 (Mountain Time)',
									'-6'=>'UTC -6 (Central Time)',
									'-5'=>'UTC -5 (Eastern Time)',
									'-4'=>'UTC -4 (Atlantic Time)',
									'-3'=>'UTC -3 (West Greenland Time)',
									'-2'=>'UTC -2 (Greenland)',
									'-1'=>'UTC -1 (Europe)',
									'0'=>'UTC 0 (Europe)',
									'1'=>'UTC +1 (Europe)',
									'2'=>'UTC +2 (Eastern Europe)',
									'3'=>'UTC +3 (Russia)',
									'4'=>'UTC +4 (Russia)',
									'5'=>'UTC +5 (India)',
									'6'=>'UTC +6 (India)',
									'7'=>'UTC +7 (Russia)',
									'8'=>'UTC +8 (China/Australia)',
									'9'=>'UTC +9 (Australia/Japan)',
									'10'=>'UTC +10 (Australia)',
									'11'=>'UTC +11 (Pacific Islands)',
									'12'=>'UTC +12 (New Zealand)',
									'13'=>'UTC +13 (Pacific Islands)',
									'14'=>'UTC +14 (Pacific Islands)');
										


// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
				if (!is_array($str_value)) {
					debugReport("Key: $str_key | Value: $str_value <br />\n");
				} else {
					debugReport("Key: $str_key (array)<br />\n");
				}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "doDebugReport") {
				$doDebugReport		 = $str_value;
//				$doDebugReport		 = filter_var($doDebugReport,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "force_class_size_beg") {
				$force_class_size_beg		 = $str_value;
				$force_class_size_beg		 = filter_var($force_class_size_beg,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "force_class_size_fun") {
				$force_class_size_fun		 = $str_value;
				$force_class_size_fun		 = filter_var($force_class_size_fun,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "force_class_size_int") {
				$force_class_size_int		 = $str_value;
				$force_class_size_int		 = filter_var($force_class_size_int,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "force_class_size_adv") {
				$force_class_size_adv		 = $str_value;
				$force_class_size_adv		 = filter_var($force_class_size_adv,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_type") {
				$request_type	 = $str_value;
				$request_type	 = filter_var($request_type,FILTER_UNSAFE_RAW);
				if ($request_type == 'A' || $request_type == 'B' || $request_type == 'F') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "verbosity") {
				$verbosity		 = $str_value;
				$verbosity		 = filter_var($verbosity,FILTER_UNSAFE_RAW);
				if ($verbosity == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "request_pwd1") {
				$request_pwd1		 = $str_value;
				$request_pwd1		 = filter_var($request_pwd1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "request_pwd2") {
				$request_pwd2		 = $str_value;
				$request_pwd2		 = filter_var($request_pwd2,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_report") {
				$inp_report		 = $str_value;
				$inp_report		 = filter_var($inp_report,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "BeginnerExemptions") {
				$BeginnerExemptions		 = strtoupper($str_value);
				$BeginnerExemptions		 = filter_var($BeginnerExemptions,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "FundamentalExemptions") {
				$FundamentalExemptions		 = strtoupper($str_value);
				$FundamentalExemptions		 = filter_var($FundamentalExemptions,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "IntermediateExemptions") {
				$IntermediateExemptions		 = strtoupper($str_value);
				$IntermediateExemptions		 = filter_var($IntermediateExemptions,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "AdvancedExemptions") {
				$AdvancedExemptions		 = strtoupper($str_value);
				$AdvancedExemptions		 = filter_var($AdvancedExemptions,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_advisors_param") {
				$inp_advisors_param		 = $str_value;
				$inp_advisors_param		 = filter_var($inp_advisors_param,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_specific_advisors") {
				$inp_specific_advisors		 = $str_value;
				$inp_specific_advisors		 = strtoupper(filter_var($inp_specific_advisors,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_locked_advisors") {
				$inp_locked_advisors		 = $str_value;
				$inp_locked_advisors		 = strtoupper(filter_var($inp_locked_advisors,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_advisor_verification") {
				$inp_advisor_verification		 = $str_value;
				$inp_advisor_verification		 = (filter_var($inp_advisor_verification,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_student_verification") {
				$inp_student_verification		 = $str_value;
				$inp_student_verification		 = (filter_var($inp_student_verification,FILTER_UNSAFE_RAW));
			}
			if ($str_key 						== "inp_addlInfo") {
				$inp_addlInfo					 = $str_value;
				$inp_addlInfo					 = (filter_var($inp_addlInfo,FILTER_UNSAFE_RAW));
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
		$theStatement	.= "<p><strong>Operating in Test Mode.</strong></p>";
			debugReport("<p><strong>Operating in Test Mode.</strong></p>");
		$advisorTableName			= "wpw1_cwa_advisor2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$studentTableName			= "wpw1_cwa_student2";
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$advisorLinkName			= "advisor2";
		$inp_mode					= "TESTMODE";
		$operatingMode				= 'Testmode';
	} else {
		$advisorTableName			= "wpw1_cwa_advisor";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$studentTableName			= "wpw1_cwa_student";
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment";
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$advisorLinkName			= "advisor";
		$inp_mode					= "PRODUCTION";
		$operatingMode				= 'Production';
	}
	
	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	$user_dal = new CWA_User_Master_DAL();
	
/////////// function to assign student to an advisor

	function assignStudentToAdvisor($studentCallSign,$advisorCallSign,$advisorSequence) {
	
		global $wpdb, $doDebug, $testMode, $advisorArray, $classArray, $advisorClassArray, 
				$processStudentArray, $studentAssignedAdvisorArray, $arbitraryArray, 
				$theIncrement, $doDebugReort, $keepDebug; 

		debugReport("<br />FUNCTION: assignStudentToAdvisor using $studentCallSign, $advisorCallSign, $advisorSequence<br />");
	
		$advisorClassKey	= "$advisorCallSign|$advisorSequence";
		if (array_key_exists($advisorClassKey,$advisorClassArray)) {
			$advisorClassArray[$advisorClassKey][0]--;			/// decrement class size
			$myInt				= $advisorClassArray[$advisorClassKey][0];
			debugReport("Decremented the number of seats for $advisorClassKey:<br />New number of seats: $myInt<br />");
			$theIncrement++;
			$advisorClassArray[$advisorClassKey][$theIncrement]	= $studentCallSign;
			$studentAssignedAdvisorArray[$studentCallSign]	= "$advisorCallSign|$advisorSequence";
				debugReport("Student added to advisorClassArray<br />
					  		 Student added to studentAssignedAdvisorArray<br />");
			return TRUE;
		} else {
				debugReport("ERROR No advisorClassArray with key of $advisorClassKey<br />");
			return FALSE;
		}
	}	
//////////// End of assignStudentToAdvisor function


///////////// Function: Find a Class

	function findAClass($studentLevel, $studentLanguage, $firstClassChoice, $secondClassChoice, $thirdClassChoice, $excludedAdvisor) {
	
	global $wpdb, $doDebug, $testMode, $advisorArray, $classArray, $advisorClassArray, 
		   $processStudentArray, $studentAssignedAdvisorArray, $arbitraryArray, 
		   $theIncrement, $doDebugReport, $keepDebug; 

/*
	look for a key of studentLevel|studentLanguage|classTime|classDays in the classArray
	if found, the array will have one or more advisors in the order that 
		the advisor should be selected
		Using the first advisor, look for the advisor|sequence in the advisorClassArray
		(if the advisor is K1BG or AC6AC, skip the advisor)
		if found, sequence 0 will be the number of seats left
			if seats are left, return the advisor and sequence
		If not found, or no seats left, go to the next advisor in the list
			if no advisor class found, return FALSE
*/

		$checkBlock		= array('00'=>'0000|0100|0200',
								'01'=>'0100|0000|0200',
								'02'=>'0200|0100|0300',
								'03'=>'0300|0200|0400',
								'04'=>'0400|0300|0500',
								'05'=>'0500|0400|0600',
								'06'=>'0600|0500|0700',
								'07'=>'0700|0600|0800',
								'08'=>'0800|0700|0900',
								'09'=>'0900|0800|1000',
								'10'=>'1000|0900|1100',
								'11'=>'1100|1000|1200',
								'12'=>'1200|1100|1300',
								'13'=>'1300|1200|1400',
								'14'=>'1400|1300|1500',
								'15'=>'1500|1400|1600',
								'16'=>'1600|1500|1700',
								'17'=>'1700|1600|1800',
								'18'=>'1800|1700|1900',
								'19'=>'1900|1800|2000',
								'20'=>'2000|1900|2100',
								'21'=>'2100|2000|2200',
								'22'=>'2200|2100|2300',
								'23'=>'2300|2200|2400');


		debugReport("<br /><b>FUNCTION:</b> findAClass with $studentLevel<br />
			  studentLanguage: $studentLanguage<br />
			  firstClassChoice: $firstClassChoice<br />
			  secondClassChoice: $secondClassChoice<br />
			  thirdClassChoice: $thirdClassChoice<br />
			  excluded advisors: $excludedAdvisor<br />");
				
		$gotAClass			= FALSE;
		$classData			= '';
		$optionArray		= array();
		$trackStudent		= "";
		for ($ii=1;$ii<4;$ii++) {
			if ($ii == 1) {
				$thisSchedule	= $firstClassChoice;
			} elseif ($ii == 2) {
				$thisSchedule	= $secondClassChoice;
			} elseif ($ii == 3) {
				$thisSchedule	= $thirdClassChoice;
			}
			if (!$gotAClass) {			
				debugReport("ii: $ii. Checking $thisSchedule schedule<br />");
				$trackStudent		.= "Checking $thisSchedule<br />";
				// see if there is actually a schedule
				if ($thisSchedule != '' && $thisSchedule != 'None') {
					$myArray			= explode(" ",$thisSchedule);
					$thisTime			= $myArray[0];
					$thisDays			= $myArray[1];
					$classArrayKey		= "$studentLevel|$studentLanguage|$thisTime|$thisDays";
						debugReport("looking for $classArrayKey in classArray<br />");
					if (array_key_exists($classArrayKey,$classArray)) {
						debugReport("$classArrayKey found in classArray<br />");
						$thisChoiceArray = array();
						foreach($classArray[$classArrayKey] as $myKey=>$myValue) {
							foreach($myValue as $advisorCallSign => $advisorSequence) {
								if (! str_contains($excludedAdvisor,$advisorCallSign)) {
									if ($advisorCallSign != 'K1BG' && $advisorCallSign != 'AC6AC') {
										$thisSequence = $advisorSequence['seq'];
										$thisChoiceArray[] = "$myKey|$advisorCallSign|$thisSequence";
										debugReport("added $myKey|$advisorCallSign|$thisSequence to thisChoiceArray<br />");
									}
								} else {
										debugReport("advisor $advisorCallSign is excluded<br />");
								}
							}
						}
						sort($thisChoiceArray);
							$thisStr	= "Class options found:<br /><pre>";
							$thisStr	.= print_r($thisChoiceArray,TRUE);
							$thisStr	= "</pre><br />";
							debugReport($thisStr);

						/// have a list of possible classes. Find the first one with seats available
						if (count($thisChoiceArray) > 0) {
							foreach($thisChoiceArray as $myValue) {
								if (!$gotAClass) {
										debugReport("Checking thisChoiceArray entry of $myValue for seats available<br />");
									$choiceArray		= explode("|",$myValue);
									$advisorChoice		= $choiceArray[2];
									$seqChoice			= $choiceArray[3];
									$thisSeats		= $advisorClassArray["$advisorChoice|$seqChoice"][0];
									if ($thisSeats > 0) {
										$gotAClass	= TRUE;
										$classData	= "$advisorChoice|$seqChoice";
										$trackStudent	.= "Advisor $advisorChoice Sequence $seqChoice: Seats Available: $thisSeats; Class made available to student<br />";
										debugReport("$thisSeats seats available. Returning $advisorChoice|$seqChoice<br />");
									} else {
										debugReport("No seats available<br />");
										$optionArray[]		= "$advisorChoice|$seqChoice|$thisSeats";
										$trackStudent	.= "Advisor $advisorChoice Sequence $seqChoice: Seats Available: $thisSeats<br />";
									}
								}
							}
						}
					} else {
							debugReport("No classArray entry for $classArrayKey<br />");
					}
				} else {
						debugReport("schedule of $thisSchedule not valid to be checked<br />");
				}
			}
		}
		if ($gotAClass) {
			debugReport("returning from findAClass with class found<br /><br />");
			return array(TRUE,$classData,$optionArray,$trackStudent);
		} else {
			debugReport("returning from findAClass with NO class found<br /><br />");
			return array(FALSE,'',$optionArray,$trackStudent);
		}
	}
/////////// end of findAClass function



////////// Move three hours function

	function moveThreeHours($direction,$time,$days) {

		global $doDebug, $testMode, $advisorArray, $classArray, $advisorClassArray, 
				$processStudentArray, $studentAssignedAdvisorArray, $arbitraryArray, 
				$theIncrement, $doDebugReport, $keepDebug; 
	
		$forwardDays		= array('Monday,Thursday'=>'Tuesday,Friday',
									'Tuesday,Friday'=>'Wednesday,Saturday',
									'Wednesday,Saturday'=>'Sunday,Thursday',
									'Sunday,Wednesday'=>'Monday,Thursday',
									'Tuesday,Thursday'=>'Wednesday,Friday');

		$backwardDays		= array('Monday,Thursday'=>'Sunday,Wednesday',
									'Tuesday,Friday'=>'Monday,Thursday',
									'Wednesday,Saturday'=>'Tuesday,Friday',
									'Sunday,Wednesday'=>'Tuesday,Saturday',
									'Tuesday,Thursday'=>'Monday,Wednesday',
									'Tuesday,Saturday'=>'Sunday,Wednesday',
									'Monday,Friday'=>'Sunday,Thursday',
									'Wednesday,Friday'=>'Tuesday,Thursday');
	
	
		debugReport("<br />FUNCTION: moveThreeHours $direction, $time, $days<br />");
	
		if ($direction == 'forward') {
			$time			= $time + 300;
			if ($time == 2400){
				$time		= 0;
				$days		= $forwardDays[$days];
			} elseif ($time > 2400) {
				$time		= $time -  2400;
				$days		= $forwardDays[$days];
			} elseif ($time < 0) {
				$time		= $time + 2400;
				$days	=	 $backwardDays[$days];
			}
			$time	= str_pad($time,4,'0',STR_PAD_LEFT);
		} else {
			$time			= $time - 300;
			if ($time == 2400){
				$time		= 0;
				$days		= $forwardDays[$days];
			} elseif ($time > 2400) {
				$time		= $time -  2400;
				$days		= $forwardDays[$days];
			} elseif ($time < 0) {
				$time		= $time + 2400;
				$days	=	 $backwardDays[$days];
			}
			$time	= str_pad($time,4,'0',STR_PAD_LEFT);
		}
		debugReport("Finished conversion: $time, $days<br />");
		return array($time,$days);
	}


	if ("1" == $strPass) {
			debugReport("<br />Pass 1: Function starting<br />");
		$content .= "<h2>$jobname</h2>$theStatement";
		
		// get job log entries for the current month and display
		
		// get the job parameters and prepare to display the run options
		$inp_advisors_param_all			= '';
		$inp_advisors_param_specific	= '';
		$inp_advisors_param_update		= '';
		$inp_specific_advisors			= '';
		$inp_locked_advisors			= '';
		$inp_force_class_size_beg		= '';
		$inp_force_class_size_fun		= '';
		$inp_force_class_size_int		= '';
		$inp_force_class_size_adv		= '';
		$inp_beginner_exemptions		= '';
		$inp_fundamental_exemptions		= '';
		$inp_intermediate_exemptions	= '';
		$inp_advanced_exemptions		= '';
		$inp_advisor_verification_u		= '';
		$inp_student_verification_u		= '';
		$inp_advisor_verification_v		= '';
		$inp_student_verification_v		= '';
		
		
		$thisMode						= 'Production';
		if ($testMode) {
			$thisMode					= 'testMode';
		}
		$sql							= "select settings from wpw1_cwa_program_settings 
											where program_name = '$jobname' 
											and semester = '$theSemester' 
											order by record_id DESC 
											limit 1";
		$lastParameters					= $wpdb->get_var($sql);
		if ($lastParameters != NULL) {
			$lastParameters				= stripslashes($lastParameters);
			$parametersArray			= json_decode($lastParameters,TRUE);
				$thisStr	= "parametersArray:<br /><pre>";
				$thisStr 	.= print_r($parametersArray,TRUE);
				$thisStr	.= "</pre><br />";
				debugReport($thisStr);
			foreach($parametersArray as $thisKey=>$thisValue) {
				${$thisKey}					= $thisValue;
			}
		} else {
				$myStr					= $wpdb->last_error;
				$mySQL					= $wpdb->last_query;
				debugReport("failed to get the settings.<br />last_error; $myStr<br />last_query: $mySQL<br />");
		}
		
		
		$content .=	"<p>Please enter/modify the job parameters as described.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
					<input type='hidden' name='strpass' value='2'>
					<input type='hidden' name='doDebugReport' value='$doDebugReport'>
					<table style='border-collapse:collapse;'>
					<tr><td style='vertical-align:top;'><b>Advisors to Include</b></td>
						<td><input type='radio' class='formInputButton' name='inp_advisors_param' $inp_advisors_param_all value='All' required>All<br />
							<input type='radio' class='formInputButton' name='inp_advisors_param' $inp_advisors_param_specific value='Specific' required>Specific Advisors (List the specific advisors below, comma separated):
							<textarea class='formInputText' name='inp_specific_advisors' rows='5' cols='30'>$inp_specific_advisors</textarea></td></tr>
					<tr><td style='vertical-align:top;'><b>Select Verification Status</b></td>
						<td><input type='radio' class='formInputButton' name='inp_student_verification' value='Verified' $inp_student_verification_v>Include only verified students<br />
							<input type='radio' class='formInputButton' name='inp_student_verification' value='unverified' $inp_student_verification_u>Include all students<br /><br />
							<input type='radio' class='formInputButton' name='inp_advisor_verification' value='Verified' $inp_advisor_verification_v>Include only verified advisors<br />
							<input type='radio' class='formInputButton' name='inp_advisor_verification' value='unverified' $inp_advisor_verification_u>Include all advisors<br />
					<tr><td style='vertical-align:top;'><b>Set Class Size Limit</b></td>
						<td>Enter a number to override the advisors' class size otherwise leave empty<br />
							Beginner Class Size Limit:<br />
							<input type='text' class='formInputText' name='force_class_size_beg' size='5' maxlength='5' value='$inp_force_class_size_beg'><br />
							Fundamental Class Size Limit:<br />
							<input type='text' class='formInputText' name='force_class_size_fun' size='5' maxlength='5' value='$inp_force_class_size_fun'><br />
							Intermediate Class Size Limit:<br />
							<input type='text' class='formInputText' name='force_class_size_int' size='5' maxlength='5' value='$inp_force_class_size_int'><br />
							Advanced Class Size Limit:<br />
							<input type='text' class='formInputText' name='force_class_size_adv' size='5' maxlength='5' value='$inp_force_class_size_adv'><br />
							<hr></td></tr>
					<tr><td style='vertical-align:top;'><b>Class Size Limit Exemptions</b></td>
						<td>Enter a comma-separated list of advisors exempt from the class size limits<br />
							Beginner Exemptions:<br />
							<input type='text' class='formInputText' name='BeginnerExemptions' size='50' maxlength='100' value='$inp_beginner_exemptions'><br />
							Fundamental Exemptions:<br />
							<input type='text' class='formInputText' name='FundamentalExemptions' size='50' maxlength='100' value='$inp_fundamental_exemptions'><br />
							Intermediate Exemptions:<br />
							<input type='text' class='formInputText' name='IntermediateExemptions' size='50' maxlength='100' value='$inp_intermediate_exemptions'><br />
							Advanced Exemptions:<br />
							<input type='text' class='formInputText' name='AdvancedExemptions' size='50' maxlength='100' value='$inp_advanced_exemptions'><br />
					<tr><td style='vertical-align:top;'><b>Additional Comments to be included in email to advisors</b></td>
						<td><textarea class='formInputText' cols='50' rows='5' name='inp_addlInfo'></textarea></td></tr>
					<tr><td style='width:150px;vertical-align:top;'><b>TEST Mode Requests</b></td>
						<td><input class='formInputButton' type='radio' name='request_type' value='A' checked>Assign Students and Display Results using Test Data (no updates and no emails)<br />
							<input class='formInputButton' type='radio' name='request_type' value='F'>Assign Students, Display Results, and Update (no emails sent) using Test Data (Update password required)<br />
							<input class='formInputButton' type='radio' name='request_type' value='B'>Assign Students, Display Results, produce test emails to Bob Carter, and update the Test Data<br />
							Password needed to do updates and/or send emails: <input class='formInputText' type='text' name='request_pwd1' size='5' maxlength='5'><br />
							<hr></td></tr>
					<tr><td style='vertical-align:top;'><b>PRODUCTION Mode Request</b></td>
						<td><input class='formInputButton' type='radio' name='request_type' value='C'>Assign Students and Display Results using PRODUCTION Data<br />
							<input class='formInputButton' type='radio' name='request_type' value='E'>'BobTest' Assign Students and Display Results using PRODUCTION Data, Send emails to BobTest, NO UPDATE<br />
							<input class='formInputButton' type='radio' name='request_type' value='D'>Assign Students, Display Results, produce emails to advisors, and update PRODUCTION Data<br />
							Password needed to do updates and send emails: 
							<input class='formInputText' type='text' name='request_pwd2' size='5' maxlength='5'><br />
							<hr></td></tr>
					<tr><td style='vertical-align:top;'><b>Verbosity</b></td>
						<td><input class='formInputButton' type='radio' name='verbosity' value='N' checked>Standard output (normal process)<br />
						<input class='formInputButton' type='radio' name='verbosity' value='Y'>Highly Verbose (used for debugging purposess)</td></tr>
					<tr><td><b>Save Report to Reports Table?</b></td>
						<td><input type='radio' name='inp_report' class='formInputButton' value='N' > Do not save<br />
						<input type='radio' name='inp_report' class='formInputButton' value='Y' checked > Save the report</td></tr>
					<tr><td>&nbsp;</td>
						<td><input class='formInputButton' type='submit' value='Submit' /></td></tr>
					</table>
					</form>";

	} elseif ("2" == $strPass) {
		debugReport("<br />Arrived at pass 2<br />");
		
		$parameterArray 	= array('inp_advisors_param_all'=>'',
									'inp_advisors_param_specific'=>'',
									'inp_specific_advisors'=>'',
									'inp_force_class_size_beg'=>'',
									'inp_force_class_size_fun'=>'',
									'inp_force_class_size_int'=>'',
									'inp_force_class_size_adv'=>'',
									'inp_beginner_exemptions'=>'',
									'inp_fundamental_exemptions'=>'',
									'inp_intermediate_exemptions'=>'',
									'inp_advanced_exemptions'=>'',
									'inp_advisor_verification'=>'',
									'inp_student_verification'=>'');		
		$content			.= "<h3>$jobname</h3>
								$theStatement
								<h4>Job Parameters</h4>";
		// set some logicals
		$allAdvisors										= FALSE;
		$specificAdvisors									= FALSE;
		$updateAdvisors										= FALSE;
		$lockedAdvisors										= FALSE;
		$verifiedAdvisors									= FALSE;
		$verifiedStudents									= FALSE;
		if ($inp_advisors_param == 'All') {
			$content										.= "Processing all advisors<br />";
			$parameterArray['inp_advisors_param_all'] 		= 'checked';
			$allAdvisors									= TRUE;
		} elseif ($inp_advisors_param == 'Specific') {
			$content										.= "Processing only these advisors: $inp_specific_advisors<br />";
			$parameterArray['inp_advisors_param_specific']	= 'checked';
			$parameterArray['inp_specific_advisors']		= $inp_specific_advisors;			
			$specificAdvisorArray							= explode(",",$inp_specific_advisors);
			$specificAdvisors								= TRUE;
		}
		if ($inp_advisor_verification == 'Verified') {
			$parameterArray['inp_advisor_verification_v']		= 'checked';
			$verifiedAdvisors								= TRUE;
			$content										.= "Processing only verified advisors<br />";
			debugReport("set inp_advisor_verification_v to checked<br />");
		} else {
			$parameterArray['inp_advisor_verification_u']		= 'checked';
			$content										.= "Processing all advisors<br />";
		}
		if ($inp_student_verification == 'Verified') {
			$parameterArray['inp_student_verification_v']	= 'checked';
			$verifiedStudents								= TRUE;
			$content										.= "Processing only verified students<br />";
		} else {
			$parameterArray['inp_student_verification_u']	= 'checked';
			$content										.= "Processing all students<br />";
		}
		if ($allAdvisors) {
			debugReport("allAdvisors is TRUE<br />");
		}
		if ($specificAdvisors) {
			debugReport("specificAdvisors is TRUE<br />");
		}
		
		
		if ($force_class_size_beg != '' && (!is_numeric($force_class_size_beg))) {
			$content		.= "Beginner class size override is not numeric. Setting to blank<br />";
			$force_class_size_beg = '';
		}
		if ($force_class_size_fun != '' && (!is_numeric($force_class_size_fun))) {
			$content		.= "Fundamental class size override is not numeric. Setting to blank<br />";
			$force_class_size_fun = '';
		}
		if ($force_class_size_int != '' && (!is_numeric($force_class_size_int))) {
			$content		.= "Intermediate class size override is not numeric. Setting to blank<br />";
			$force_class_size_int = '';
		}
		if ($force_class_size_adv != '' && (!is_numeric($force_class_size_adv))) {
			$content		.= "Advanced class size override is not numeric. Setting to blank<br />";
			$force_class_size_adv = '';
		}
		$content			.= "<p>Class size limits:<br />
								Beginner class size limit: $force_class_size_beg<br />
								Beginner class size limit exemptions: $BeginnerExemptions<br >
								Fundamental class size limit: $force_class_size_fun<br />
								Fundamental class size limit exemptions: $FundamentalExemptions<br >
								Intermediate class size limit: $force_class_size_int<br />
								Intermediate class size limit exemptions: $IntermediateExemptions<br >
								Advanced class size limit: $force_class_size_adv<br />
								Advanced class size limit exemptions: $AdvancedExemptions</p>";
		if ($force_class_size_beg != '') {
			$parameterArray['inp_force_class_size_beg']		= $force_class_size_beg;
		}
		if ($force_class_size_fun != '') {
			$parameterArray['inp_force_class_size_fun']		= $force_class_size_fun;
		}
		if ($force_class_size_int != '') {
			$parameterArray['inp_force_class_size_int']		= $force_class_size_int;
		}
		if ($force_class_size_adv != '') {
			$parameterArray['inp_force_class_size_adv']		= $force_class_size_adv;
		}
		if ($BeginnerExemptions != '') {
			$parameterArray['inp_beginner_exemptions']		= $BeginnerExemptions;
		}
		if ($FundamentalExemptions != '') {
			$parameterArray['inp_fundamental_exemptions']		= $FundamentalExemptions;
		}
		if ($IntermediateExemptions != '') {
			$parameterArray['inp_intermediate_exemptions']		= $IntermediateExemptions;
		}
		if ($AdvancedExemptions != '') {
			$parameterArray['inp_advanced_exemptions']		= $AdvancedExemptions;
		}
	
		$thisMode						= 'Production';
		if ($testMode) {
			$thisMode					= 'testMode';
		}

		$thisStr	= "parameterArray:<br /><pre>";
		$thisStr 	.= print_r($parameterArray,TRUE);
		$thisStr	.= "</pre><br />";
		debugReport($thisStr);

		$settings			= json_encode($parameterArray);
		$settings			= addslashes($settings);
		// see if a record exists. If so, update otherwise insert
		$settingsSQL		= "select * from wpw1_cwa_program_settings 
								where program_name = '$jobname' 
								and semester = '$theSemester' 
								and mode = '$thisMode' 
								order by record_id 
								limit 1";
		$sqlResult			= $wpdb->get_results($settingsSQL);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"Pass 2 seeing if program settings record exists");
		} else {
			$numSRows		= $wpdb->num_rows;
			debugReport("ran $settingsSQL<br />and retrieved $numSRows rows<br />");
			if ($numSRows	== 0) {		// no record. do insert
				$settingsResult	= $wpdb->insert('wpw1_cwa_program_settings',
												array('program_name'=>$jobname,
													  'semester'=>$theSemester,
													  'settings'=>$settings,
													  'mode'=>$thisMode),
												array('%s','%s','%s','%s'));
			} else {		// update existing row
				foreach($sqlResult as $resultRow) {
					$thisID	= $resultRow->record_id;

					$settingsResult	= $wpdb->update('wpw1_cwa_program_settings', 
											array('settings'=>$settings),
											array('record_id'=>$thisID), 
											array('%s'), 
											array('%d'));
			
				}
			}			
			if ($settingsResult === FALSE){
				$myError		= $wpdb->last_error;
				$myQuery		= $wpdb->last_query;
				debugReport("Inserting into wpw1_cwa_program_settings failed<br />
						  wpdb->last_query: $myQuery<br />
						  wpdb->last_error: $myError<br />");
				$errorMsg			= "$jobname Pass 2 Inserting into wpw1_cwa_program_settings failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
				sendErrorEmail($errorMsg);
				return "Unable to continue";
			} 
		}
		
//		return $content;

		if ($request_type == 'B' || $request_type == 'F') { 		// password required
				debugReport("Checking password 1<br />");
			if ($request_pwd1 != 'dxcc') {
				debugReport("Proper password required to do updates and send emails.<br />");
				$content .= "<p>A password is required to do updates</p>";
				goto Bypass;
			}
		}
		if ($request_type == 'D') { 		// password required
				debugReport("Checking password 2<br />");
			if ($request_pwd2 != 'udxa') {
				debugReport("Proper password required to do updates and send emails.<br />");
				$content .= "<p>A password is required to do updates</p>";
				goto Bypass;
			}
		}

		// setup the exemptions array
		$BeginnerExemptions				= str_replace(" ","",$BeginnerExemptions);
		$BeginnerExemptionsArray		= explode(",",$BeginnerExemptions);
		$FundamentalExemptions			= str_replace(" ","",$FundamentalExemptions);
		$FundamentalExemptionsArray		= explode(",",$FundamentalExemptions);
		$IntermediateExemptions			= str_replace(" ","",$IntermediateExemptions);
		$IntermediateExemptionsArray	= explode(",",$IntermediateExemptions);
		$AdvancedExemptions				= str_replace(" ","",$AdvancedExemptions);
		$AdvancedExemptionsArray		= explode(",",$AdvancedExemptions);
	
		// Build the advisor array and the advisorClass array
		$prevAdvisor		= "";

		// get the list of advisors with classes in nextSemester
		$sql		= "select distinct(advisorclass_call_sign) as advisorclass_call_sign 
						from TABLENAME 
						where advisorclass_semester = '$nextSemester' 
						order by advisorclass_call_sign";
		$wpw1_cwa_advisorCallSign	= $advisorclass_dal->run_sql($sql, $operatingMode);
		if ($wpw1_cwa_advisorCallSign === FALSE) {
			debugReport("get_sql returned FALSE<br />");
		} else {
			if (! empty($wpw1_cwa_advisorCallSign)) {
				$numCSRows = count($wpw1_cwa_advisorCallSign);
				debugReport("ran $sql<br />and found $numCSRows rows<br />");
				if ($numCSRows > 0) {
					foreach ($wpw1_cwa_advisorCallSign as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						$advisorCallSign		= $advisorclass_call_sign;
						
						$haveUserMaster			= FALSE;
						$haveAdvisor			= FALSE;
	
						debugReport("<br />processing $advisorCallSign<br />");
	
						// get the user_master record for advisorCallSign
						$userData = $user_dal->get_user_master_by_callsign($advisorCallSign,$operatingMode);
						if ($userData === FALSE) {
							debugReport("get_user_master_by_callsign returned FALSE<br />");
						} else {
							if (! empty($userData)) {
								foreach($userData as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
									$haveUserMaster = TRUE;
								}
							} else {
								$content	.= "<p>No User Master record found for $advisorCallSign</p>";
								debugReport("No User Master record found for $advisorCallSign<br />");
							}
						}
						// get the advisor record
						if ($haveUserMaster) {
							$criteria = [
								'relation' => 'AND',
								'clauses' => [
									['field' => 'advisor_call_sign', 'value' => $advisorCallSign, 'compare' => '=' ],
 									['field' => 'advisor_semester', 'value' => $nextSemester, 'compare' => '=' ]
								]
							];
							$advisorData = $advisor_dal->get_advisor($criteria,$operatingMode);
							if ($advisorData === FALSE) {
								debugReport("get_advisor for $advisorCallSign returned FALSE<br />");
							} else {
								if (! empty($advisorData)) {
									foreach($advisorData as $key => $value) {
										foreach($value as $thisField => $thisValue) {
											$$thisField = $thisValue;
										}
										$haveAdvisor = TRUE;
									}
								} else {
									debugReport("get_advisor for $advisorCallSign returned an empty set<br />");
								}
							}
						}
						if ($haveUserMaster && $haveAdvisor) {
							// Now we can process the advisorclass records
							$criteria = [
								'relation' => 'AND',
								'clauses' => [
									['field' => 'advisorclass_call_sign', 'value' => $advisorCallSign, 'compare' => '=' ],
									['field' => 'advisorclass_semester', 'value' => $nextSemester, 'compare' => '=' ]
								]
							];
							$orderby = 'advisorclass_sequence';
							$order = 'ASC';
							$advisorclassResult = $advisorclass_dal->get_advisorclasses_by_order($criteria,$orderby,$order,$operatingMode);
							if ($advisorclassResult === FALSE) {
								debugReport("get_advisorclasses_by_order returned FALSE<br />");
							} else {
								if (! empty($advisorclassResult)) {
									foreach($advisorclassResult as $key => $value) {
										foreach($value as $thisField => $thisValue) {
											$$thisField = $thisValue;
										}
					
										$advisor_select_sequence				= 0;
					
										$advisorUpdateLink	= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$advisor_call_sign&strpass=2&inp_depth=one&inp_depth=one&doDebug=$doDebug&testMode=$testMode' target='_blank'>$advisor_call_sign</a>";
					
										debugReport("<br />Processing advisor $advisorclass_call_sign class $advisorclass_sequence with survey_score: $user_survey_score and verified: $advisor_verify_response <br />");
										$doAdvisor								= TRUE;
										if ($user_survey_score == 6 || $user_survey_score == 13) {
											debugReport("Advisor bypassed with survey_score of $user_survey_score<br />");
											$doAdvisor							= FALSE;
											$errorArray[]						= "Advisor $advisor_call_sign has a survey score of $user_survey_score. Bypassed<br />";
										}
										if ($advisor_verify_response  == 'R') {
											debugReport("Advisor bypassed with verified of $advisor_verify_response <br />");
											$errorArray[]						= "Advisor $advisor_call_sign has a response of R. Bypassed<br />";
											$doAdvisor							= FALSE;
										}
										if ($advisor_verify_email_number == '4' && $advisor_verify_response == '') {
											debugReport("Advisor bypassed with verify_email_number of $advisor_verify_email_number<br />");
											$errorArray[]						= "Advisor $advisor_call_sign has verify_email_nimber of 4 and has not verified. Bypassed<br />";
											$doAdvisor							= FALSE;
										}
										if ($verifiedAdvisors && $advisor_verify_response != 'Y') {
											debugReport("Advisor bypassed with verify_response of $advisor_verify_response<br />");
											$errorArray[]						= "Advisor $advisor_call_sign is not verified. Bypassed<br />";
											$doAdvisor							= FALSE;
										}
										if ($specificAdvisors) {
											if (!in_array($advisor_call_sign,$specificAdvisorArray)) {
												debugReport("doing specific advisors and $advisor_call_sign is not one of them. Bypassing<br />");
												$doAdvisor						= FALSE;
											}
										}
										if ($advisorclass_level == 'Beginner' && $force_class_size_beg != '') {
											if (!in_array($advisor_call_sign,$BeginnerExemptionsArray)) {
												if ($advisorclass_class_size >= $force_class_size_beg) {
													$overrideArray[]				= "$advisor_call_sign Beginner class size of $advisorclass_class_size overriden to $force_class_size_beg<br />";
												}
												$advisorclass_class_size		= $force_class_size_beg;
											} 
										}
										if ($advisorclass_level == 'Fundamental' && $force_class_size_fun != '') {
											if (!in_array($advisor_call_sign,$FundamentalExemptionsArray)) {
												if ($advisorclass_class_size >= $force_class_size_fun) {
													$overrideArray[]				= "$advisor_call_sign Fundamental class size of $advisorclass_class_size overriden to $force_class_size_fun<br />";
												}
												$advisorclass_class_size		= $force_class_size_fun;
											}
										}
										if ($advisorclass_level == 'Intermediate' && $force_class_size_int != '') {
											if (!in_array($advisor_call_sign,$IntermediateExemptionsArray)) {
												if ($advisorclass_class_size >= $force_class_size_int) {
													$overrideArray[]				= "$advisor_call_sign Intermediate class size of $advisorclass_class_size overriden to $force_class_size_int<br />";
												}
												$advisorclass_class_size		= $force_class_size_int;
											}
										}
										if ($advisorclass_level == 'Advanced' && $force_class_size_adv != '') {
											if (!in_array($advisor_call_sign,$AdvancedExemptionsArray)) {
												if ($advisorclass_class_size >= $force_class_size_adv) {
													$overrideArray[]				= "$advisor_call_sign Advanced class size of $advisorclass_class_size overriden to $force_class_size_adv<br />";
												}
												$advisorclass_class_size		= $force_class_size_adv;
											}
										}
										$advisorOK								= TRUE;
										if ($advisor_call_sign == $prevAdvisor) {
											$advisorOK							= FALSE;
										}
										$prevAdvisor							= $advisor_call_sign;
										if ($doAdvisor) {
											if ($advisorOK) {
										
												$fifoDate						= strtotime($advisor_date_created);
//												$sequenceTransform										= array(3=>0,2=>1,1=>2,0=>3);
//												$selectSequence											= $sequenceTransform[$advisor_select_sequence];
//												$selectSequence 										= str_pad($selectSequence,4,'0',STR_PAD_LEFT);
												$advisorCalcSequence									= "0|$fifoDate";
												$advisorArray[$advisor_call_sign]['first name'] 		= $user_first_name;
												$advisorArray[$advisor_call_sign]['last name'] 			= $user_last_name;
												$advisorArray[$advisor_call_sign]['email'] 				= $user_email;
												$advisorArray[$advisor_call_sign]['phone'] 				= $user_phone;
												$advisorArray[$advisor_call_sign]['text message'] 		= '';
												$advisorArray[$advisor_call_sign]['state'] 				= $user_state;
												$advisorArray[$advisor_call_sign]['country'] 			= $user_country;
												$advisorArray[$advisor_call_sign]['time zone']			= $advisorclass_timezone_offset;
												$advisorArray[$advisor_call_sign]['fifo date'] 			= $user_date_created;
												$advisorArray[$advisor_call_sign]['ID'] 				= $advisor_id;
												$advisorArray[$advisor_call_sign]['time zone id'] 		= $user_timezone_id;
											}
											debugReport("Processing advisorClass for $advisorclass_call_sign sequence $advisorclass_sequence<br />");
											/// fix up the class schedule time
											
											$thisTime			= substr($advisorclass_class_schedule_times_utc,0,2) . "00";
											$classKey			= "$advisorclass_level|$advisorclass_language|$thisTime|$advisorclass_class_schedule_days_utc";
											$classArray[$classKey][$advisorCalcSequence][$advisor_call_sign]['seq'] = $advisorclass_sequence;
											debugReport("added $classKey $advisorCalcSequence $advisor_call_sign seq as $advisorclass_sequence to classArray<br />");
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"][0]				= $advisorclass_class_size;
										
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"]['size']			= $advisorclass_class_size;
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"]['seq']			= $advisorclass_sequence;
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"]['level']		= $advisorclass_level;
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"]['language']		= $advisorclass_language;
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"]['time zone']	= $advisorclass_timezone_offset;
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"]['time utc']		= $advisorclass_class_schedule_times_utc;
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"]['days utc']		= $advisorclass_class_schedule_days_utc;
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"]['time local']	= $advisorclass_class_schedule_times;
											$advisorClassArray["$advisor_call_sign|$advisorclass_sequence"]['days local']	= $advisorclass_class_schedule_days;
											
											debugReport("added all other advisorClass info to advisorClassArray<br />");
					
					
											debugReport("Processed advisorClass call sign: $advisor_call_sign - $advisorclass_sequence<br />");
										}
									}
								} else {
									debugReport("No records found in advisorclass for $advisorCallSign<br />");
									$errorArray[]			= "No records found in advisorclass for $advisorCallSign<br />";
								}
							}
						}
					}
					debugReport("<br /><b>Finished Processing Advisors</b><br />");
/*	
					$thisStr	= "<br />advisor Array:<br /><pre>";
					$thisStr 	.= print_r($advisorArray,TRUE);
					$thisStr	.= "</pre><br />";
					debugReport($thisStr);
				
					$thisStr	= "<br />advisor Class Array:<br /><pre>";
					$thisStr 	.= print_r($advisorClassArray,TRUE);
					$thisStr	.= "</pre><br />";
					debugReport($thisStr);
// goto Bypass;
*/					


	
				} else {
					$content			.= "<p>No records found in $advisorClassTablerName</p>";
				}
			} else {
				debugReport("getting list of advisors returned an empty data set<br />");
			}
		}

///////		build the studentClass array
		debugReport("<br />Reading $studentTableName table and <b>building studentClassArray</b><br />");
		$prevStudent		= '';
		$haveStudentData = FALSE;
		$haveStudentUser = FALSE;
		if ($inp_student_verification == 'unverified') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '=' ],
					['field' => 'student_response', 'value' => 'R', 'compare' => '!=' ]				]
			];
		} else {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '=' ],
					['field' => 'student_response', 'value' => 'Y', 'compare' => '=' ]
				]
			];
		}
		$orderby = 'student_call_sign';
		$order = 'ASC';
		$studentData = $student_dal->get_student($criteria,$orderby,$order,$operatingMode);
		if ($studentData === FALSE) {
			$debugReport("get_student returned FALSE<br />");
		} else {
			if (! empty($studentData)) {
				$myInt = count($studentData);
				debugReport("have $myInt student records to process<br />");
				foreach($studentData as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					$haveStudentData = TRUE;
					$student_excluded_advisor = str_replace('|','&',$student_excluded_advisor);

					debugReport("<br />have studentData for $student_call_sign. Getting user_master<br />");
					// if have studentData, get the user_master data
					$userData = $user_dal->get_user_master_by_callsign( $student_call_sign, $operatingMode );
					if ($userData === FALSE) {
						debugReport("get_user_master_by_callsign for $student_call_sign returned FALSE<br />");
					} else {
						if (! empty($userData)) {
							foreach($userData as $key => $value) {
								foreach($value as $thisField => $thisValue) {
									$$thisField = $thisValue;
								}
								$haveStudentUser = TRUE;
								
							}
						} else {
							debugReport("get_user_master_by_callsign for $student_call_sign returned an empty data set<br />");
						}
					}
					if ($haveStudentData && $haveStudentUser) {
						debugReport("have both studentData and StudentUser<br />");
						$processStudent = TRUE;
						$studentUpdateLink		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&strpass=2&inp_depth=all&doDebug=$doDebug&testMode=FALSE' target='_blank'>$student_call_sign</a>";
	
						debugReport("Processing student $studentUpdateLink<br />
									pre-assigned Advisor: $student_pre_assigned_advisor $student_assigned_advisor_class<br />
									Assigned Advisor: $student_assigned_advisor $student_assigned_advisor_class<br />
									Abandoned: $student_abandoned<br />
									Timezone Offset: $student_timezone_offset<br />
									first_class_choice: $student_first_class_choice | $student_first_class_choice_utc<br />
									second_class_choice: $student_second_class_choice | $student_second_class_choice_utc<br />
									third_class_choice: $student_third_class_choice | $student_third_class_choice_utc<br />
									Excluded advisors: $student_excluded_advisor<br />");
						if ($student_call_sign == $prevStudent) {
							$errorArray[]						= "Student $studentUpdateLink ($student_level) has a duplicate student record. Duplicate bypassed<br />";
							debugReport("Student $studentUpdateLink ($student_level) has a duplicate student record. Duplicate bypassed<br />");
							$processStudent						= FALSE;
							$prevStudent						= $student_call_sign;
						}
						if ($student_abandoned == 'Y') {
							$errorArray[]						= "Student $studentUpdateLink ($student_level) abandoned the signup. Bypassed.<br />";
							$processStudent						= FALSE;
						}
						if ($student_timezone_offset == -99.0) {
							$errorArray[]						= "Student $studentUpdateLink ($student_level) has a timezone_offset of -99. Bypassing<br />";
							$processStudent						= FALSE;
						}
						if ($processStudent) {
							$studentArray[$student_call_sign]['first name']				= $user_first_name;
							$studentArray[$student_call_sign]['last name']				= $user_last_name;
							$studentArray[$student_call_sign]['email']					= $user_email;
							$studentArray[$student_call_sign]['phone']					= $user_phone;
							$studentArray[$student_call_sign]['text message']			= '';
							$studentArray[$student_call_sign]['city']					= $user_city;
							$studentArray[$student_call_sign]['state']					= $user_state;
							$studentArray[$student_call_sign]['country']				= $user_country;
							$studentArray[$student_call_sign]['time_zone']				= $student_timezone_offset;
							$studentArray[$student_call_sign]['response']				= $student_response;
							$studentArray[$student_call_sign]['status']					= $student_status;
							$studentArray[$student_call_sign]['youth']					= $student_youth;
							$studentArray[$student_call_sign]['age']					= $student_age;
							$studentArray[$student_call_sign]['parent email']			= $student_parent_email;
							$studentArray[$student_call_sign]['parent']					= $student_parent;
							$studentArray[$student_call_sign]['first choice']			= $student_first_class_choice;
							$studentArray[$student_call_sign]['second choice']			= $student_second_class_choice;
							$studentArray[$student_call_sign]['third choice']			= $student_third_class_choice;
							$studentArray[$student_call_sign]['first choice utc']		= $student_first_class_choice_utc;
							$studentArray[$student_call_sign]['second choice utc']		= $student_second_class_choice_utc;
							$studentArray[$student_call_sign]['third choice utc']		= $student_third_class_choice_utc;
							$studentArray[$student_call_sign]['level']					= $student_level;
							$studentArray[$student_call_sign]['language']				= $student_class_language;
							$studentArray[$student_call_sign]['ID']						= $student_id;
							$studentArray[$student_call_sign]['messenger']				= $user_messenger;
							$studentArray[$student_call_sign]['telegram']				= $user_telegram;
							$studentArray[$student_call_sign]['signal']					= $user_signal;
							$studentArray[$student_call_sign]['whatsapp']				= $user_whatsapp;
							$studentArray[$student_call_sign]['excluded advisor']		= $student_excluded_advisor;
							$studentArray[$student_call_sign]['pre_assigned advisor']	= $student_pre_assigned_advisor;						
							$studentArray[$student_call_sign]['assigned advisor']		= $student_assigned_advisor;
							$studentArray[$student_call_sign]['assigned advisor class']	= $student_assigned_advisor_class;
	
							// if student is on hold, add to the hold array
							if ($student_intervention_required == 'H') {
								debugReport("Student is on Hold. Added to holdArray<br />");
								$holdArray[]	= $student_call_sign;
								$totalStudentsOnHold++;
								$processStudent		= FALSE;
							}
							if ($student_intervention_required == 'Q' && $student_hold_reason_code == 'Q') {
								debugReport("Student is on Hold. Added to holdArray<br />");
								$holdArray[]	= $student_call_sign;
								$totalStudentsOnHold++;
								$processStudent		= FALSE;
							}
							if ($verifiedStudents && $student_response != 'Y') {
								debugReport("Student not verified. Bypassed<br />");
								$processStudent		= FALSE;			
							} 
							// if student is already assigned, handle that situation
							if ($processStudent && $student_assigned_advisor != '') {
								$thisResult			= assignStudentToAdvisor($student_call_sign,$student_assigned_advisor,$student_assigned_advisor_class);
								if ($thisResult) {
									$processStudent	= FALSE;
									debugReport("student assignment to assigned advisor $student_assigned_advisor accomplished<br />");
								} else {
									debugReport("Student $student_call_sign with level $student_level assigned to $student_assigned_advisor at sequence $student_assigned_advisor_class. Advisor does not have that class. Student bypassed<br />");
									$errorArray[]	= "Student $studentUpdateLink ($student_level) with level $student_level assigned to $student_assigned_advisor at sequence $student_assigned_advisor_class. Advisor does not have that class. <b>Student Bypassed</b><br />";
									$processStudent	= FALSE;
								}							
							}
							
							if ($processStudent) {
								if ($student_first_class_choice == '' || $student_first_class_choice == 'None') {
									$student_first_class_choice = 'None';
									$student_first_class_choice_utc	= 'None';
									$firstTimes		= '';
									$firstDays		= '';
									$studentArray[$student_call_sign]['first choice utc'] = 'None';
									$studentArray[$student_call_sign]['first choice'] = 'None';
								} else {
									if ($student_first_class_choice_utc == '') {			/// convert to UTC
										$errorArray[]		= "For Roland: Student $student_call_sign had no first UTC schedule<br />";
										// break student info into time and days
										$myArray		= explode(" ",$student_first_class_choice);
										$myStr			= $myArray[0];
										$localDays		= $myArray[1];
										$localTime		= substr($myStr,0,5);
										$localTime		= str_replace(":","",$localTime);
										$result			= utcConvert('toutc',$student_timezone_offset,$localTime,$localDays);
										if ($result[0] == 'OK') {
											$firstTimes	= $result[1];
											$firstDays	= $result[2];
											$studentArray[$student_call_sign]['first choice utc'] = "$firstTimes $firstDays";
										} else {
											$firstTimes		= '';
											$firstDays		= '';
										}
									} else {
										$myArray		= explode(" ",$student_first_class_choice_utc);
										$firstTimes		= $myArray[0];
										$firstDays		= $myArray[1];
									}
								}
								if ($student_second_class_choice == '' || $student_second_class_choice == 'None') {
									$student_second_class_choice = 'None';
									$student_second_class_choice_utc	= 'None';
									$secondTimes		= '';
									$secondDays			= '';
								} else {
									if ($student_second_class_choice_utc == '') {
										if ($student_second_class_choice != 'None') {			/// convert to UTC
											$errorArray[]		= "For Roland: Student $student_call_sign had no secibd UTC schedule<br />";
											// break student info into time and days
											$myArray		= explode(" ",$student_second_class_choice);
											$myStr			= $myArray[0];
											$localDays		= $myArray[1];
											$localTime		= substr($myStr,0,5);
											$localTime		= str_replace(":","",$localTime);
											$result			= utcConvert('toutc',$student_timezone_offset,$localTime,$localDays);
											if ($result[0] == 'OK') {
												$secondTimes	= $result[1];
												$secondDays	= $result[2];	
												$studentArray[$student_call_sign]['first choice utc'] = "$secondTimes $secondDays";
											} else {
												$secondTimes		= '';
												$secondDays		= '';
											}
										}
									} else {
										$myArray		= explode(" ",$student_second_class_choice_utc);
										$secondTimes	= $myArray[0];
										$secondDays		= $myArray[1];
									}
								}
								if ($student_third_class_choice == '' || $student_third_class_choice == 'None') {
									$student_third_class_choice = 'None';
									$student_third_class_choice_utc	= 'None';
									$thirdTimes		= '';
									$thirdDays		= '';
								} else {
									if ($student_third_class_choice_utc == '') {
										if ($student_third_class_choice != 'None') {			/// convert to UTC
											$errorArray[]		= "For Roland: Student $student_call_sign had no third UTC schedule<br />";
											// break student info into time and days
											$myArray		= explode(" ",$student_third_class_choice);
											$myStr			= $myArray[0];
											$localDays		= $myArray[1];
											$localTime		= substr($myStr,0,5);
											$localTime		= str_replace(":","",$localTime);
											$result			= utcConvert('toutc',$student_timezone_offset,$localTime,$localDays);
											if ($result[0] == 'OK') {
												$thirdTimes	= $result[1];
												$thirdDays	= $result[2];
												$studentArray[$student_call_sign]['first choice utc'] = "$thirdTimes $thirdDays";
											} else {
												$thirdTimes		= '';
												$thirdDays		= '';
											}
										}
									} else {
										$myArray		= explode(" ",$student_third_class_choice_utc);
										$thirdTimes		= $myArray[0];
										$thirdDays		= $myArray[1];
									}
								}
/*				
								if ($student_first_class_choice == 'None' && $student_second_class_choice == 'None' && $student_third_class_choice == 'None') {
									$student_first_class_choice	= "1900 Monday,Thursday";
									$studentArray[$student_call_sign]['first choice'] = "1900 Monday,Thursday";
									$result			= utcConvert('toutc',$student_timezone_offset,'1900','Monday,Thursday');
									if ($result[0] == 'OK') {
										$thisTimes	= $result[1];
										$thisDays	= $result[2];
										$student_first_class_choice_utc	= "$thisTimes $thisDays";
										$studentArray[$student_call_sign]['first choice utc'] = "$thisTimes $thisDays";
										$arbitraryArray[]	= $student_call_sign;
//										$errorArray[]		= "Student $studentUpdateLink ($student_level) has response of Y and no class choices. Set to 1900 Monday,Thursday<br />";
									} else {
										$errorArray[]		= "Student $studentUpdateLink ($student_level) has response of Y and no class choices. Set to 1900 Monday,Thursday failed UTC conversion<br />";
									}
								}
*/
							}
						}
						if ($processStudent) {
	
							// if the student is pre-assigned, do the pre-assign
							if ($student_pre_assigned_advisor != '' && $student_assigned_advisor == '') {
								debugReport("Student pre-assigned to $student_pre_assigned_advisor sequence $student_assigned_advisor_class<br />");
								$doPreAssign				= TRUE;
								// see if the advisor is excluded
								if (str_contains($student_excluded_advisor,$student_pre_assigned_advisor)) {
									$doPreAssign		= FALSE;
									debugReport("advisor $student_pre_assigned_advisor is exlcuded. No assignment made<br />");
								}
								// if doing specific advisors, only assign if one of the specific advisors
								if ($specificAdvisors) {
									if (!in_array($student_pre_assigned_advisor,$specificAdvisorArray)) {
										$doPreAssign		= FALSE;
										debugReport("advisor $student_pre_assigned_advisor is not being processed. No assignment made<br />");
									} 								
								}
								if ($doPreAssign) {
									$thisResult			= assignStudentToAdvisor($student_call_sign,$student_pre_assigned_advisor,$student_assigned_advisor_class);
									if ($thisResult) {
										$processStudent	= FALSE;
										$preAssignedCount++;
										$preAssignedArray[]	= $student_call_sign;
										debugReport("student pre-assignment to $student_pre_assigned_advisor accomplished<br />");
									} else {
										debugReport("Student $student_call_sign with level $student_level pre-assigned to $student_pre_assigned_advisor at sequence $student_assigned_advisor_class. Advisor does not have that class<br />");
										$errorArray[]	= "Student $studentUpdateLink ($student_level) with level $student_level pre-assigned to $student_pre_assigned_advisor at sequence $student_assigned_advisor_class. Advisor does not have that class<br />";
										$processStudent	= TRUE;
									}
								}
							}					/// student is assigned. Keep the assignment
							if ($student_assigned_advisor != '') {
								// if doing specific advisors, only assign if one of the specific advisors
								$doAssign				= TRUE;
								if ($specificAdvisors) {
									if (!in_array($student_assigned_advisor,$specificAdvisorArray)) {
										$doAssign		= FALSE;
										debugReport("advisor $student_assigned_advisor is not being processed. No assignment made<br />");
									} 								
								}
								if ($doAssign) {
									$thisResult			= assignStudentToAdvisor($student_call_sign,$student_assigned_advisor,$student_assigned_advisor_class);
									if ($thisResult) {
										$processStudent	= FALSE;
										$preAssignedCount++;
										$preAssignedArray[]	= $student_call_sign;
										debugReport("student assignment to $student_assigned_advisor accomplished<br />");
									} else {
										debugReport("Student $student_call_sign with level $student_level assigned to $student_assigned_advisor at sequence $student_assigned_advisor_class. Advisor does not have that class<br />");
										$errorArray[]	= "Student $studentUpdateLink with level $student_level assigned to $student_assigned_advisor at sequence $student_assigned_advisor_class. Advisor does not have that class<br />";
										$processStudent	= TRUE;
									}
								}
							}
						}
					
						// if student is a beginner youth, assign to K1BG
						if ($processStudent) {
							if ($student_level == 'Beginner' && ($student_youth == 'Yes' || $student_youth == 'Y')) {
								if ($student_class_language == 'English') {
									$doK1BG				= TRUE;
									debugReport("$student_call_sign is a youth. assign to K1BG?<br />");
									if ($specificAdvisors) {
										if (!in_array('K1BG',$specificAdvisorArray)) {
											$doK1BG		= FALSE;
											debugReport("doing specificAdvisors and K1BG not included<br />");
										}
									}
									if ($doK1BG) {
										debugReport("Student is a Beginner and a Youth. Assigning to K1BG<br />");
										$student_advisor				= 'K1BG';
										$student_assigned_advisor_class	= '1';
										$thisResult			= assignStudentToAdvisor($student_call_sign,'K1BG','1');
										if ($thisResult) {
											$processStudent	= FALSE;
											$preAssignedCount++;
											debugReport("youth student assignment to K1BG accomplished<br />");
										} else {
											debugReport("Student $student_call_sign with level $student_level pre-assigned to $student_advisor at sequence $student_assigned_advisor_class. Advisor does not have that classy<br />");
											$errorArray[]	= "Student $studentUpdateLink with level $student_level pre-assigned to $student_advisor at sequence $student_assigned_advisor_class. Advisor does not have that class<br />";
											$processStudent	= FALSE;
										}
									}
								}
							}
						}
						
						// if processStudent then add to processStudentArray
						if ($processStudent) {
							$requestDate			= strtotime($student_request_date);
							$processStudentArray[] 	= "$student_level|$student_class_language|$student_class_priority|$requestDate|$student_call_sign|$student_first_class_choice_utc|$student_second_class_choice_utc|$student_third_class_choice_utc|$student_timezone_offset|$student_excluded_advisor";
							debugReport("student assigned to processStudentArray<br />");
						}
					}
				}
			} else {
				debugReport("get_student returned an empty data set<br />");
			}
		}


///// student arrays built		
				
		rsort($processStudentArray);
		ksort($classArray);
		
		$thisStr	=  "<br />advisor Array:<br /><pre>";
		$thisStr	.= print_r($advisorArray,TRUE);
		$thisStr	.=  "</pre><br />";
		debugReport($thisStr);
	
		$thisStr	=  "<br />student Array:<br /><pre>";
		$thisStr	.= print_r($studentArray,TRUE);
		$thisStr	.=  "</pre><br />";
		debugReport($thisStr);
	
		$thisStr	=  "<br />class Array:<br /><pre>";
		$thisStr	.= print_r($classArray,TRUE);
		$thisStr	.=  "</pre><br />";
		debugReport($thisStr);

		$thisStr	=  "<br />advisor Class Array:<br /><pre>";
		$thisStr	.= print_r($advisorClassArray,TRUE);
		$thisStr	.=  "</pre><br />";
		debugReport($thisStr);
		
		debugReport("<br />student Assigned Advisor Array<br />");
		ksort($studentAssignedAdvisorArray);
		foreach($studentAssignedAdvisorArray as $myKey=>$myValue) {
		debugReport("$myKey = $myValue<br />");
		}
		
		$thisStr	=  "<br />process Student Array:<br /><pre>";
		$thisStr	.= print_r($processStudentArray,TRUE);
		$thisStr	.=  "</pre><br />";
		debugReport($thisStr);
		
		$thisStr = "<br />Error Array:<br /><pre>";
		$thisStr .= print_r($errorArray,TRUE);
		$thisStr .= "</pre><br />";
		debugReport($thisStr);
	
// goto Bypass;

//////////	assign students to advisors
/*	processStudentArray is sorted by level, language, priority, request date and has the first, second,
		and third class choices in UTC
	for each of the class choices, where there is a class choice
		run the findAClass function with the student level, languae, class time, class day, excluded advisor
		if the return is not FALSE, run assignStudentToAdvisor function with student_call_sign,
			advisor_call_sign, and advisor class sequence
			count the student as assigned
		if the return is FALSE, do the next class choice
		If none of the class choices work, do the arbitrary class assignment

	if no class found, make an arbitrary assignment
		calculate 1900 in student's local time in UTC
		Look for a class at the student's level, language, utc time, Monday,Thursday
			if found, assign the student to that class
				and add the student to the arbitraryArray
			if not found, add to errorArray

*/

		debugReport("<br /><b>Assigning Students</b><br />");
		foreach($processStudentArray as $studentValue) {
			$myArray					= explode("|",$studentValue);
			$studentLevel				= $myArray[0];
			$studentLanguage			= $myArray[1];
			$studentPriority			= $myArray[2];
			$studentReqDate				= $myArray[3];
			$studentCallSign			= $myArray[4];
			$studentFirstClassChoice	= $myArray[5];		// times and days are in UTC
			$studentSecondClassChoice	= $myArray[6];
			$studentThirdClassChoice	= $myArray[7];
			$studentTimeZone			= $myArray[8];
			$studentExcludedAdvisor		= $myArray[9];
			
			debugReport("<br />Processing $studentCallSign $studentLevel<br />
						language: $studentLanguage<br />
						first choice: $studentFirstClassChoice<br />
						second choice: $studentSecondClassChoice<br />
						third choice: $studentThirdClassChoice<br />
						time zone: $studentTimeZone<br />");						

			$gotAClass			= FALSE;
			$studentTrace		.= "<br /><b>$studentCallSign</b><br />";
			debugReport("Look for a class for student choices<br />");
			$thisResult		= findAClass($studentLevel,$studentLanguage,$studentFirstClassChoice,$studentSecondClassChoice,$studentThirdClassChoice,$studentExcludedAdvisor);
			if ($thisResult[0] !== FALSE) {
				$gotAClass	= TRUE;
				$studentTrace		.= $thisResult[3];
				debugReport("Got a class: $thisResult[1]<br />");
			} else {
				$studentTrace		.= $thisResult[3];
				$thisStr			= "No class match. Options checked:<br /><pre>";
				$thisStr			.= print_r($thisResult[2],TRUE);
				$thisStr			.=  "</pre><br />";
				debugReport($thisStr);
			}

			if (!$gotAClass) {	
				debugReport("<br />no classes found. Attempting arbitrary assignment<br />");
				/// if still no match, look at a number of  options
				$studentTrace		.= "Attempting arbitrary assignment<br />";
				// figure out what 1900 in local time is in UTC
				$thisUTC			= 1900 - ($studentTimeZone * 100);
				if ($thisUTC == 2400) {
					$thisUTC		= 0;
				}
				if ($thisUTC > 2400) {
					$thisUTC		= $thisUTC - 2400;
				} elseif ($thisUTC < 0) {
					$thisUTC		= $thisUTC + 2400;
				}
				$firstUTC			= str_pad($thisUTC,4,'0',STR_PAD_LEFT);

				// figure out what 2000 in local time is in UTC
				$thisUTC			= 2000 - ($studentTimeZone * 100);
				if ($thisUTC == 2400) {
					$thisUTC		= 0;
				}
				if ($thisUTC > 2400) {
					$thisUTC		= $thisUTC - 2400;
				} elseif ($thisUTC < 0) {
					$thisUTC		= $thisUTC + 2400;
				}
				$secondUTC			= str_pad($thisUTC,4,'0',STR_PAD_LEFT);

				// figure out what 1800 in local time is in UTC
				$thisUTC			= 1800 - ($studentTimeZone * 100);
				if ($thisUTC == 2400) {
					$thisUTC		= 0;
				}
				if ($thisUTC > 2400) {
					$thisUTC		= $thisUTC - 2400;
				} elseif ($thisUTC < 0) {
					$thisUTC		= $thisUTC + 2400;
				}
				$thirdUTC			= str_pad($thisUTC,4,'0',STR_PAD_LEFT);
				debugReport("Attempting arbitrary assignment. TZ of $studentTimeZone at 1900 converted to UTC is $thisUTC<br />");
				foreach($daysTestArray as $myValue) {
					$schedule1		= "$firstUTC $myValue";
					$schedule2		= "$secondUTC $myValue";
					$schedule3		= "$thirdUTC $myValue";
					$thisResult			= findAClass($studentLevel,$studentLanguage,$schedule1,$schedule2,$schedule3,$studentExcludedAdvisor);
					if ($thisResult[0] !== FALSE) {
						$gotAClass		= TRUE;
						$studentTrace		.= $thisResult[3];
						debugReport("findAClass matched $thisResult[1]<br />");
						$arbitraryArray[]		= $studentCallSign;
						$arbitraryAssignedCount++;
						break;
					} else {
						$studentTrace		.= $thisResult[3];
						$thisStr	=  "No arbitrary class choice match. Options checked:<br /><pre>";
						$thisStr	.= print_r($thisResult[2],TRUE);
						$thisStr	.=  "</pre><br />";
						debugReport($thisStr);
					}
				}
			}
			
			
			if (!$gotAClass) { 		// now what: stick in unassigned array
				debugReport("No class option found for $studentCallSign ($studentLevel). Adding to unassignedArray<br />");
				$unassignedArraySequence++;
				$unassignedArray[$studentLevel][$unassignedArraySequence]	= $studentCallSign;
			} else {			//// assign student to the class
				$myArray			= explode("|",$thisResult[1]);
				$advisorCallSign	= $myArray[0];
				$advisorSequence	= $myArray[1];
				$thisResult			= assignStudentToAdvisor($studentCallSign,$advisorCallSign,$advisorSequence);
				if ($thisResult) {
					debugReport("Student assigned to $advisorCallSign, $advisorSequence<br />");
				} else {
					debugReport("ERROR No class for $studentCallSign ($studentLevel). Added to errorArray<br />");
					$errorArray[]		= "Attempt to assign student $studentUpdateLink with level $student_level to $advisorCallSign at sequence $advisorSequence failed. Advisor does not have that class<br />";
					$unassignedArraySequence++;
					$unassignedArray[$studentLevel][$unassignedArraySequence]	= $studentCallSign;
				}
			}
			
		}				// done with the processStudent array
				
		////// dump the arrays

		sort($arbitraryArray);
		ksort($studentAssignedAdvisorArray);
		ksort($unassignedArray);
		
		$thisStr	=  "<br />advisor Class Array:<br /><pre>";
		$thisStr	.= print_r($advisorClassArray,TRUE);
		$thisStr	.=  "</pre><br />";
		debugReport($thisStr);
		
		debugReport("<br />student Assigned Advisor Array<br />");
		foreach($studentAssignedAdvisorArray as $myKey=>$myValue) {
			debugReport("$myKey = $myValue<br />");
		}
		
		$thisStr	=  "<br />Arbitrary Array:<br /><pre>";
		$thisStr	.= print_r($arbitraryArray,TRUE);
		$thisStr	.=  "</pre><br />";
		debugReport($thisStr);
		
		$thisStr	=  "<br />Unassigned Array:<br /><pre>";
		$thisStr	.= print_r($unassignedArray,TRUE);
		$thisStr	.=  "</pre><br />";
		debugReport($thisStr);
		
		debugReport("<br />Hold Array:<br />");
		foreach($holdArray as $myValue) {
			debugReport("$myValue<br />");
		}
// goto Bypass;

/* 	prepare report of advisors and classes
	
	read the advisorArray for each advisor and put out the header
		read the advisorClassArray and put out a line for each student
		count the students
		give a count at the end of the class
		
	prepare the email to the advisor. Only send if requested
	
*/
		debugReport("<br />Preparing Advisors and Classes Report<br />");
		if ($testMode) {
			$thisMode	= 'TM';
		} else {
			$thisMode	= 'PM';
		}
		if ($force_class_size_beg == '') {
			$force_class_size_beg = 'No override';
		}
		if ($force_class_size_fun == '') {
			$force_class_size_fun = 'No override';
		}
		if ($force_class_size_int == '') {
			$force_class_size_int = 'No override';
		}
		if ($force_class_size_adv == '') {
			$force_class_size_adv = 'No override';
		}
		$content				.= "<h2>$jobname</h2>
									<p><a href='#report1'>Go to the Advisor Classes and Assigned Students Report</a><br />
									<a href='#report3'>Go to the Unassigned Students Report</a><br />
									<a href='#report2'>Go to the Student Assignment Information Report</a><br />
									<a href='#reportA'>Go to the Arbitrarily Assigned Students Report</a><br />
									<a href='#reportS'>Go to the Advisors with Small Classes Report</a><br />
									<a href='#reportY'>Go to the Advisors With Open Seats Report</a><br />
									<a href='#reportH'>Go to the Students on Hold Report</a><br />
									<a href='#reportO'>Go to the Advisor Class Size Overridden Report</a><br />
									<a href='#reportE'>Go to the Errors Report</a><br />
									</p>";



		$content					.= "<a name='report1'><h3>Advisor Classes and Assigned Students</h3></a><table>";
		
		$thisNumberClasses			= 0;
		$thisNumberAdvisors			= 0;
		$thisAdvisorList			= array_keys($advisorArray);
		foreach($thisAdvisorList as $advisorCallSign) {
			$advisor_first_name		= $advisorArray[$advisorCallSign]['first name'];
			$advisor_last_name		= $advisorArray[$advisorCallSign]['last name'];
			$advisor_email			= $advisorArray[$advisorCallSign]['email'];
			$advisor_phone			= $advisorArray[$advisorCallSign]['phone'];
			$advisor_text_message	= $advisorArray[$advisorCallSign]['text message'];
			$advisor_state			= $advisorArray[$advisorCallSign]['state'];
			$advisor_country		= $advisorArray[$advisorCallSign]['country'];
			$advisor_time_zone		= $advisorArray[$advisorCallSign]['time zone'];
			$advisor_fifo_date		= $advisorArray[$advisorCallSign]['fifo date'];
			$advisor_ID				= $advisorArray[$advisorCallSign]['ID'];
			
			$advisorUpdateLink		= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$advisorCallSign&strpass=2&inp_depth=one&doDebug=$doDebug&testMode=FALSE' target='_blank'>$advisorCallSign</a>";
			debugReport("<br />Processing advisor $advisorUpdateLink<br />");
			$thisNumberAdvisors++;
								
			$content				.= "<table style='width:900px;'>
										<tr><th style='width:200px;'>Advisor</th>
											<th style='width:190px;'>Email</th>
											<th style='width:190px;'>Phone</th>
											<th style='width:190px;'>State</th>
											<th>Country</th>
											<th></th></tr>
										<tr><td><b>$advisor_last_name, $advisor_first_name ($advisorUpdateLink)</b></td>
											<td><b>$advisor_email</b></td>
											<td><b>$advisor_phone ($advisor_text_message)</b></td>
											<td><b>$advisor_state</b></td>
											<td><b>$advisor_country</b></td>
											<td></td></tr>";				
				
			$studentCount			= 0;
			// get the advisorClass records
			debugReport("Getting advisorClass records<br />");
			$doOnce											= TRUE;
			for($ii=1;$ii<=5;$ii++) {
				$advisorClassKey			= "$advisorCallSign|$ii";
				if (array_key_exists($advisorClassKey,$advisorClassArray)) {
					debugReport("Found a class for key $advisorClassKey<br />");
					
					$advisorclass_class_size				= $advisorClassArray["$advisorCallSign|$ii"]['size'];
					$advisorclass_sequence					= $advisorClassArray["$advisorCallSign|$ii"]['seq'];
					$advisorclass_level						= $advisorClassArray["$advisorCallSign|$ii"]['level'];
					$advisorclass_language					= $advisorClassArray["$advisorCallSign|$ii"]['language'];
					$advisorclass_timezone					= $advisorClassArray["$advisorCallSign|$ii"]['time zone'];
					$advisorclass_class_schedule_days_utc	= $advisorClassArray["$advisorCallSign|$ii"]['days utc'];
					$advisorclass_class_schedule_times_utc	= $advisorClassArray["$advisorCallSign|$ii"]['time utc'];
					$advisorclass_class_schedule_days		= $advisorClassArray["$advisorCallSign|$ii"]['days local'];
					$advisorclass_class_schedule_times		= $advisorClassArray["$advisorCallSign|$ii"]['time local'];
					
		
					$content				.= "<tr><td colspan='6'><em><b>Advisor Class #$ii</b></em><br />
														<b>Class Size:</b> $advisorclass_class_size&nbsp;&nbsp;&nbsp;&nbsp;
														<b>Level:</b> $advisorclass_level&nbsp;&nbsp;&nbsp;&nbsp;
														<b>Language:</b> $advisorclass_language&nbsp;&nbsp;&nbsp;&nbsp;
														<b>Local:</b> $advisorclass_class_schedule_times $advisorclass_class_schedule_days&nbsp;&nbsp;&nbsp;&nbsp;
														<b>UTC:</b> $advisorclass_class_schedule_times_utc $advisorclass_class_schedule_days_utc</td></tr>
												<tr><td><b>Student</b></td>
													<td><b>Email</b></td>
													<td><b>Phone</b></td>
													<td><b>State</b></td>
													<td><b>Country</b></td>
													<td><b>Language</td></tr>";
	
					$thisNumberClasses++;
					$myStr						= "";
					if ($doOnce) {
						$doOnce				 	= FALSE;
					} else {
						$myStr					= "<br />";
					}

													
					$thisClassStudents			= 0;

					foreach($advisorClassArray[$advisorClassKey] as $thisSequence=>$thisStudent) {
						if (is_numeric($thisSequence) && $thisSequence > 0) {
							$student_first_name		= $studentArray[$thisStudent]['first name'];
							$student_last_name		= $studentArray[$thisStudent]['last name'];
							$student_email			= $studentArray[$thisStudent]['email'];
							$student_phone			= $studentArray[$thisStudent]['phone'];
							$student_text_message	= $studentArray[$thisStudent]['text message'];
							$student_city			= $studentArray[$thisStudent]['city'];
							$student_state			= $studentArray[$thisStudent]['state'];
							$student_country		= $studentArray[$thisStudent]['country'];
							$student_time_zone		= $studentArray[$thisStudent]['time_zone'];
							$student_response		= $studentArray[$thisStudent]['response'];
							$student_status			= $studentArray[$thisStudent]['status'];
							$student_youth			= $studentArray[$thisStudent]['youth'];
							$student_age			= $studentArray[$thisStudent]['age'];
							$student_parent			= $studentArray[$thisStudent]['parent'];
							$student_parent_email	= $studentArray[$thisStudent]['parent email'];
							$student_whatsapp		= $studentArray[$thisStudent]['whatsapp'];
							$student_signal			= $studentArray[$thisStudent]['signal'];
							$student_telegram		= $studentArray[$thisStudent]['telegram'];
							$student_messenger		= $studentArray[$thisStudent]['messenger'];
							$student_first_choice	= $studentArray[$thisStudent]['first choice utc'];
							$student_second_choice	= $studentArray[$thisStudent]['second choice utc'];
							$student_third_choice	= $studentArray[$thisStudent]['third choice utc'];
							$student_language		= $studentArray[$thisStudent]['language'];
							
							$studentUpdateLink		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$thisStudent&strpass=2&inp_depth=all&doDebug=$doDebug&testMode=FALSE' target='_blank'>$thisStudent</a>";
							debugReport("Adding line for $thisStudent<br />");
							$hasAssessment			= FALSE;
							$assessmentStr			= '';
							$sql					= "select * from $audioAssessmentTableName 
														where call_sign='$thisStudent'";
							$wpw1_cwa_audio_assessment	= $wpdb->get_results($sql);
							if ($wpw1_cwa_audio_assessment === FALSE) {
								debugReport("reading $audioAssessmentTableName table failed<br />
										  wpdb->last_query: " . $wpdb->last_query . "<br />
										  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />");
							} else {
								$numAARows									= $wpdb->num_rows;
								debugReport("retrieved $numAARows rows from wpw1_cwa_audio_assessment table<br />");
								if ($numAARows > 0) {
									$hasAssessment	= TRUE;
									$enstr			= base64_encode("advisor_call_sign=$advisorCallSign&inp_callsign=$thisStudent");
									$assessmentStr	.= "Click 
														<a href='$siteURL/cwa-view-a-student-assessment/?strpass=2&enstr=$enstr' 
														target='_blank'>HERE</a> to review the student's self assessment";
								}
							}
							$isArbitrary			= FALSE;
							if (in_array($thisStudent,$arbitraryArray)) {
								$myStr				= "$student_last_name, $student_first_name ($studentUpdateLink)<br /><em><b>Arbitrarily Assigned</b></em>";
								$isArbitrary		= TRUE;
							} elseif (in_array($thisStudent,$preAssignedArray)) {
								$myStr				= "$student_last_name, $student_first_name ($studentUpdateLink)<br /><em><b>Pre-assigned</b></em>";
							} else {
								$myStr				= "$student_last_name, $student_first_name ($studentUpdateLink)<br />&nbsp;";
							}
							$studentCount++;
							$totalStudents++;
							$thisClassStudents++;
							$content				.= "<tr><td colspan='6'><table style='border-bottom-style:solid;width:900px;'>
														<td style='text-align:top;width:200px;'>$myStr</td>
															<td style='text-align:top;width:190px;'>$student_email<br />$student_first_choice</td>
															<td style='text-align:top;width:190px;'>$student_phone<br />$student_second_choice</td>
															<td style='text-align:top;width:190px;'>$student_state<br />$student_third_choice</td>
															<td style='text-align:top;'>$student_country</td>
															<td style='text-align:top;'>$student_language</td></tr>";
							$thisExtras				= 'Additional Contact Options: ';
							$hasExtras				= FALSE;
							if ($student_whatsapp != '') {
								$thisExtras			.= "WhatsApp: $student_whatsapp ";
								$hasExtras			= TRUE; 
							}						
							if ($student_signal != '') {
								$thisExtras			.= "Signal: $student_signal ";
								$hasExtras			= TRUE; 
							}						
							if ($student_telegram != '') {
								$thisExtras			.= "Telegram: $student_telegram ";
								$hasExtras			= TRUE; 
							}						
							if ($student_messenger != '') {
								$thisExtras			.= "Messenger: $student_messenger ";
								$hasExtras			= TRUE; 
							}
							if ($hasExtras) {
								$content			.= "<tr><td colspan='5'>$thisExtras<td></tr>";
							}
							if ($hasAssessment) {
								$content			.= "<tr><td colspan='5'>$assessmentStr</td></tr>";
							}
							if ($student_youth == 'Yes') {
								if ($student_age < 18) { 
									if ($student_parent == '') {
										$student_parent	= 'Not Given';
									}
									if ($student_parent_email == '') {
										$student_parent_email = 'Not Given';
									}
									$content		.= "<tr><td colspan='5'>The student is a youth under the age of 18. 
																	Parent or guardian is $student_parent at email address $student_parent_email.</td></tr>";
								}
							}
							$content				.= "</table></td></tr>";
						}
					}
					$content			.= "<tr><td colspan='6'>$thisClassStudents Students<br /><hr></td></tr>";
					if ($thisClassStudents < 4) {
						$smallClassesArray[] =	"$advisorCallSign|$advisorclass_sequence|$advisorclass_language|$thisClassStudents|$advisorclass_class_size|$advisorclass_level|$advisorclass_timezone|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_class_schedule_times_utc $advisorclass_class_schedule_days_utc";
					}
					debugReport("calculating seatsOpen. advisorclass_class_size: $advisorclass_class_size; thisClassStudents: $thisClassStudents<br />");
					$seatsOpen 				= $advisorclass_class_size - $thisClassStudents;
					if ($seatsOpen > 0) {
						$seatsOpenArray[]	= "$advisorCallSign|$seatsOpen|$advisorclass_class_size|$advisorclass_level|$advisorclass_language|$advisorclass_timezone|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_class_schedule_times_utc $advisorclass_class_schedule_days_utc|$thisClassStudents";
					}
				}
			
			}
			
			if ($request_type == 'B' || $request_type == 'D' || $request_type == 'E') {			/// send advisor email
				if ($studentCount != 0) {
					$theSubject				= "CWA Student Assignments for the $nextSemester Semester";

					if ($testMode) {
						$theRecipient		= 'rolandksmith@gmail.com';
						$emailCode			= 2;
						$theSubject			= "TESTMODE $theSubject";
					} elseif ($request_type == 'E') {
						$theRecipient		= 'rolandksmith@gmail.com';
						$emailCode			= 2;
						$theSubject			= "BOBTEST $theSubject";
					} else {
						$theRecipient		= $advisor_email;
						$emailCode			= 12;
					}
					$increment1++;

					$thisStr				= '';
					if ($inp_addlInfo != '') {
						$thisStr			= "<p>$inp_addlInfo</p>";
					}
					$advisorEmail			= "<p>To: $advisor_last_name, $advisor_first_name ($advisorCallSign):</p>
													$thisStr
													<p>The process to make initial student assignments for the $nextSemester Semester is complete. 
													Please log into <a href='$siteURL/program-list'>CW Academy</a> to obtain your student 
													information and confirm student participation.</p>
													<p>For detailed information on accessing your Advisor Portal, click 
													<a href='https://cwa.cwops.org/wp-content/uploads/Advisor-Portal-Instructions.pdf'>HERE</a>.</p>
													<p>73,<br />CW Academy</p>";

					$emailArray				= array('theRecipient'=>$theRecipient,
													'theSubject'=>$theSubject,
													'jobname'=>$jobname,
													'theContent'=>$advisorEmail,
													'mailCode'=>$emailCode,
													'increment'=>$increment1,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
					$mailResult		= emailFromCWA_v2($emailArray);
					if ($mailResult === TRUE) {
						debugReport("An email with student info was sent to $theRecipient<br />");
						$advisorMailCount++;
					} else {
						$content .= "The mail send function to $theRecipient failed.<br /><br />";
					}
					
					// add the reminder to the advisor portal
					$returnArray		= wp_to_local($advisorclass_timezone, 0, 14);
					if ($returnArray === FALSE) {
						debugReport ("called wp_to_local with $advisor_time_zone 0, 14 which returned FALSE<br />");
						$effective_date		= date('Y-m-d 00:00:00');
						$closeStr			= strtotime("+ 14 days");
						$close_date			= date('Y-m-d 00:00:00',$closeStr);
					} else {
						$effective_date		= $returnArray['effective'];
						$close_date			= $returnArray['expiration'];
					}

					$token				= mt_rand();

					$email_text			= "<p>To: $advisor_last_name, $advisor_first_name ($advisorCallSign):</p>
<p>The process to make initial student assignments for the $nextSemester Semester is complete. 
Please log into <a href='$siteURL/program-list'>CW Academy</a> to obtain your student information and confirm student participation.</p>
<p>For detailed information on accessing your Advisor Portal, click 
<a href='https://cwa.cwops.org/wp-content/uploads/Advisor-Portal-Instructions.pdf'>HERE</a>.</p>
<p>73,<br />CW Academy</p>";

					$reminder_text		= "<b>Student Participation Confirmation:</b> Students  have been 
											assigned to your class. You should now contact each student, 
											verify if that student will be attending, and then update the 
											student status. Click on <a href='cwa-manage-advisor-class-assignments/?strpass=5&inp_callsign=$advisorCallSign&token=$token'
											target='_blank'>Manage Advisor Class</a> to perform this task."; 
					
					$inputParams		= array("effective_date|$effective_date|s",
												"close_date|$close_date|s",
												"resolved_date||s",
												"send_reminder|4|s",
												"send_once|N|s",
												"call_sign|$advisorCallSign|s",
												"role||s",
												"email_text|$email_text|s",
												"reminder_text|$reminder_text|s",
												"resolved||s",
												"token|$token|s");
					$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
					if ($insertResult[0] === FALSE) {
						debugReport("inserting reminder failed: $insertResult[1]<br />");
						$content		.= "Inserting reminder failed: $insertResult[1]<br />";
					}

					
					
					if ($request_type == 'B' || $request_type == 'D' || $request_type == 'F') {			/// update advisor action log?
		
						///// update advisor action log
						debugReport("Updating advisor after sending the email<br />");
						$advisorData = $advisor_dal->get_advisor_by_id( $advisor_id, $operatingMode );
						if ($advisorData === FALSE || $advisorData === NULL) {
							debugReport("geting advisor_by_id for $advisor_id returned FALSE|NULL<br />");
						} else {
							if (! empty($advisorData)) {
								foreach($advisorData as $key => $value) {
									$$key = $value;
								}
								$advisor_action_log		= "$advisor_action_log / $actionDate ASSIGN $userName Email sent to advisor with student assignments ";
								$updateParams			= array('action_log'=>$advisor_action_log);
								$updateResult = $advisor_dal->update( $advisor_id, $updateParams, $operatingMode );
								if ($updateResult === FALSE || $updateResult === NULL) {
									if ($doDebug) {
										echo "attempting to update $advisor_id returned FALSE|NULL<br />";
									}
								} else {
									debugReport("Successfully updated $advisor_call_sign record at $advisor_ID<br />");
									debugReport("Advisor $advisorCallSign (ID: $advisor_ID) action log updated<br />");
								}
							} else {
								debugReport("No record found for $advisor_id to update<br />");
								$errorArray[]	= "No record found in $advisorTableName table for $advisor_ID to update<br />";
							}
						}
					}
				}
			}	
		}
		$content					.= "</table>
										<p>$preAssignedCount Students pre-assigned to an advisor<br />
										$arbitraryAssignedCount Students with arbitrary assignment<br />
										$totalStudents Total Assigned Students<br />
										$thisNumberClasses Number of classes<br />
										$thisNumberAdvisors Number of advisors</p>";

/////////////	end of the student and advisor assignments

// goto Bypass;

////////////	Unassigned students report
		debugReport("Doing Unassigned Students Report<br />");
//	unassignedArray[studentLevel][sequence]	= student_call_sign

		$levelCount		= 0;
		$begCount		= 0;
		$funCount		= 0;
		$intCount		= 0;
		$advCount		= 0;
		$orderArray = array('Beginner','Fundamental','Intermediate','Advanced');
		
		$content		.= "<a name='report3'><br /><h3>Unassigned Students Report</h3></a>";
		$unassignedCount = 0;
		ksort($unassignedArray);
		foreach($orderArray as $reportLevel) {
			$myStr = ucwords($reportLevel);
			$levelCount = 0;
			$content .= "<br /><h4>Unassigned $myStr Students</h4>
						 <table style = 'width:1000px;'>
						 <tr><th>Student<br />First Choice</th>
						 	<th>Email<br />Second Choice</th>
						 	<th>Phone<br />Third Choice</th>
						 	<th>City</th>
						 	<th>Country</th>
						 	<th>Language</th>
						 	<th>Excl Advisors<br /></th></tr>";
			foreach($unassignedArray[$reportLevel] as $thisSequence => $thisStudent) {
				$studentCallSign		= $thisStudent;
				$student_level			= $reportLevel;
			
			
				$student_first_name		= $studentArray[$thisStudent]['first name'];
				$student_last_name		= $studentArray[$thisStudent]['last name'];
				$student_email			= $studentArray[$thisStudent]['email'];
				$student_phone			= $studentArray[$thisStudent]['phone'];
				$student_text_message	= $studentArray[$thisStudent]['text message'];
				$student_city			= $studentArray[$thisStudent]['city'];
				$student_state			= $studentArray[$thisStudent]['state'];
				$student_country		= $studentArray[$thisStudent]['country'];
				$student_language		= $studentArray[$thisStudent]['language'];
				$student_time_zone		= $studentArray[$thisStudent]['time_zone'];
				$student_response		= $studentArray[$thisStudent]['response'];
				$student_status			= $studentArray[$thisStudent]['status'];
				$student_youth			= $studentArray[$thisStudent]['youth'];
				$student_parent_email	= $studentArray[$thisStudent]['parent email'];
				$student_first_choice	= $studentArray[$thisStudent]['first choice utc'];
				$student_second_choice	= $studentArray[$thisStudent]['second choice utc'];
				$student_third_choice	= $studentArray[$thisStudent]['third choice utc'];
				$student_excluded_advisor	= $studentArray[$thisStudent]['excluded advisor'];
				
				$student_excluded_advisor = str_replace('&',' ',$student_excluded_advisor);
			
				$studentUpdateLink = "<a href='$studentUpdateURL?request_type=callsign&request_info=$studentCallSign&strpass=2&inp_depth=all&doDebug=$doDebug&testMode=0' target='_blank'>$studentCallSign</a>";
				$findClassLink = "<a href='$studentManagementURL?strpass=70&inp_student_callsign=$studentCallSign&inp_mode=$inp_mode' target='_blank'><b>Find Class</b></a>";

				$unassignedCount++;
				$levelCount++;
				switch ($reportLevel) {
					case "Beginner":
						$begCount++;
						break;
					case "Fundamental":
						$funCount++;
						break;
					case "Intermediate":
						$intCount++;
						break;
					case "Advanced":
						$advCount++;
						break;
				}
				$content .= "<tr><td style='vertical-align:top;'>$student_last_name, $student_first_name ($studentUpdateLink) <br />
																$student_first_choice utc</td>
								<td style='vertical-align:top;'>$student_email<br />$student_second_choice utc</td>
								<td style='vertical-align:top;'>$student_phone<br />$student_third_choice utc</td>
								<td style='vertical-align:top;'>$student_state</td>
								<td style='vertical-align:top;'>$student_country</td>
								<td style='vertical-align:top;'>$student_language</td>
								<td style='vertical-align:top;'>$student_excluded_advisor<br />($findClassLink)</td></tr>";	
			}
			$content .= "<tr><td colspan='10'>$levelCount Students<br /><hr></td></tr></table>";
		}
		$content			.= "<tr><td colspan='10'>$levelCount $reportLevel Students<br /><hr></td></tr></table>
								<p>$begCount Unassigned Beginner Students<br />
								$funCount Unassigned Fundamental Students<br />
								$intCount Unassigned Intermediate Students<br />
								$advCount Unassigned Advanced students<br />
								$unassignedCount Total Unassigned Students</p>";

//////////////	end of unassigned report

// goto Bypass;


/////////////	student assignment information report

//	studentAssignedAdvisorArray[student_call_sign] = |	student_advisor|student_assigned_advisor_class

		debugReport("Doing the student assignment information report<br />");
		ksort($studentAssignedAdvisorArray);
		$content				.= "<a name='report2'><h3>Student Assignment Information Report</h3></a>
									<table style='width:1000px;'>
									<tr><th>Student</th>
										<th>Level</th>
										<th>Language</th>
										<th style='width:200px;'>Advisor</th>
										<th style='width:200px;'>Email</th>
										<th style='width:200px;'>Phone</th>
										<th>State</th>
										<th>Country</th></tr>";
				
		$thisCount					= 0;
		foreach($studentAssignedAdvisorArray as $studentCallSign=>$myValue) {
			$myArray				= explode("|",$myValue);
			$thisAdvisor			= $myArray[0];
			$thisClass				= $myArray[1];
			
			$student_first_name		= $studentArray[$studentCallSign]['first name'];
			$student_last_name		= $studentArray[$studentCallSign]['last name'];
			$student_email			= $studentArray[$studentCallSign]['email'];
			$student_phone			= $studentArray[$studentCallSign]['phone'];
			$student_text_message	= $studentArray[$studentCallSign]['text message'];
			$student_city			= $studentArray[$studentCallSign]['city'];
			$student_state			= $studentArray[$studentCallSign]['state'];
			$student_country		= $studentArray[$studentCallSign]['country'];
			$student_time_zone		= $studentArray[$studentCallSign]['time_zone'];
			$student_level			= $studentArray[$studentCallSign]['level'];
			$student_language		= $studentArray[$studentCallSign]['language'];
			$student_first_choice	= $studentArray[$studentCallSign]['first choice utc'];
			$student_second_choice	= $studentArray[$studentCallSign]['second choice utc'];
			$student_third_choice	= $studentArray[$studentCallSign]['third choice utc'];
			
			$studentUpdateLink		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$studentCallSign&strpass=2&inp_depth=all&doDebug=$doDebug&testMode=FALSE' target='_blank'>$studentCallSign</a>";
			$advisorUpdateLink		= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$thisAdvisor&strpass=2&inp_depth=one&doDebug=$doDebug&testMode=FALSE' target='_blank'>$thisAdvisor</a>";

			$thisCount++;
			$content				.= "<tr><td style='vertical-align:top;'>$student_last_name, $student_first_name ($studentUpdateLink)</td>
											<td style='vertical-align:top;'>$student_level</td>
											<td style='vertical-align:top;'>$student_language</td>
											<td style='vertical-align:top;'>$advisorUpdateLink ($thisClass)<br />$student_first_choice</td>
											<td style='vertical-align:top;'>$student_email<br />$student_second_choice</td>
											<td style='vertical-align:top;'>$student_phone<br />$student_third_choice</td>
											<td style='vertical-align:top;'>$student_state</td>
											<td style='vertical-align:top;'>$student_country</td></tr>";
		}
		$content					.= "</table>$thisCount assigned students<br />";


/////////////	end of student assignment information report

//////////////	arbitrarily assigned students report

		$content		.= "<a name='reportA'><h3>Arbitrarily Assigned Students Report</h3></a>
							<p>(Students who had class choices none of which are in the current catalog or none of which have 
							available seats)</p>
							<table>
							<tr><th>Level</th>
								<th>Language</th>
								<th>TZ</th>
								<th style='column-width:150px;'>Student</th>
								<th>Email</th>
								<th>Phone</th>
								<th>City</th>
								<th>State</th>
								<th>Country</th>
								<th>Advisor</th>
								<th style='column-width:200px;'>Class Choices</th></tr>";
		$myInt	 		= count($arbitraryArray);
		if ($myInt > 0) {
			
			sort($arbitraryArray);
			$myInt		= 0;
			foreach($arbitraryArray as $studentCallSign) {
				if (array_key_exists($studentCallSign,$studentAssignedAdvisorArray)) {
					//// get the assignment from studentAssignedAdvisorArray
					$studentAssignedInfo		= $studentAssignedAdvisorArray[$studentCallSign];
					$myArray					= explode("|",$studentAssignedInfo);
					$thisAdvisor				= $myArray[0];
					$thisClass					= $myArray[1];
					$student_first_name		= $studentArray[$studentCallSign]['first name'];
					$student_last_name		= $studentArray[$studentCallSign]['last name'];
					$student_email			= $studentArray[$studentCallSign]['email'];
					$student_phone			= $studentArray[$studentCallSign]['phone'];
					$student_text_message	= $studentArray[$studentCallSign]['text message'];
					$student_city			= $studentArray[$studentCallSign]['city'];
					$student_state			= $studentArray[$studentCallSign]['state'];
					$student_country		= $studentArray[$studentCallSign]['country'];
					$student_time_zone		= $studentArray[$studentCallSign]['time_zone'];
					$student_response		= $studentArray[$studentCallSign]['response'];
					$student_status			= $studentArray[$studentCallSign]['status'];
					$student_youth			= $studentArray[$studentCallSign]['youth'];
					$student_parent_email	= $studentArray[$studentCallSign]['parent email'];
					$student_first_choice	= $studentArray[$studentCallSign]['first choice'];
					$student_second_choice	= $studentArray[$studentCallSign]['second choice'];
					$student_third_choice	= $studentArray[$studentCallSign]['third choice'];
					$student_level			= $studentArray[$studentCallSign]['level'];
					$student_language		= $studentArray[$studentCallSign]['language'];
					
					$studentUpdateLink		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$studentCallSign&strpass=2&inp_depth=all&doDebug=$doDebug&testMode=FALSE' target='_blank'>$studentCallSign</a>";
					$advisorUpdateLink		= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$thisAdvisor&strpass=2&inp_depth=one&doDebug=$doDebug&testMode=FALSE' target='_blank'>$thisAdvisor</a>";
			
					$content				.= "<tr><td style='vertical-align:top;'>$student_level</td>
													 <td style='vertical-align:top;'>$student_language</td>															<td style='vertical-align:top;'>$student_time_zone</td>
													 <td style='vertical-align:top;'>$student_last_name, $student_first_name ($studentUpdateLink)</td>
													  <td style='vertical-align:top;'>$student_email</td>
													  <td style='vertical-align:top;'>$student_phone</td>
													  <td style='vertical-align:top;'>$student_city</td>
													  <td style='vertical-align:top;'>$student_state</td>
													  <td style='vertical-align:top;'>$student_country</td>
													  <td style='vertical-align:top;'>$advisorUpdateLink-$thisClass</td>
													  <td style='vertical-align:top;'>$student_first_choice<br />$student_second_choice<br />$student_third_choice</td></tr>";
					$myInt++;	
				}	
			}
		} else {
			$content	.= "<tr><td colspan='9'>No Students arbitrarily assigned</td></tr>";
		}
		$content			.= "</table>
								<p>$myInt Students arbitrarily assigned</p>";



///////////// 	small classes report
		debugReport("<br />Starting the small classes report<br />");
//		smallClassesArray[$advisorCallSign|$advisorclass_sequence|$advisorclass_language|$thisClassStudents|$advisorclass_class_size|$advisorclass_level|$advisorclass_timezone|$advisorclass_class_schedule_times $advisorclass_class_schedule_days|$advisorclass_class_schedule_times_utc $advisorclass_class_schedule_days_utc]

		sort($smallClassesArray);
		$availSeats		= 0;
		$content		.= "<a name='reportS'><h3>Advisors With Small Classes</h3></a>
							<table>
							<tr><th>Advisor</th>
								<th>Class</th>
								<th>Level</th>
								<th>Language</th>
								<th>TZ</th>
								<th>Class Size</th>
								<th>Students</th>
								<th>Class Schedule</th></tr>";
		$smallClassCount	= 0;
		foreach($smallClassesArray as $myValue) {
			$myArray		= explode("|",$myValue);
			$thisAdvisor	= $myArray[0];
			$thisClass		= $myArray[1];
			$thisLanguage	= $myArray[2];
			$thisStudents	= $myArray[3];
			$thisSize		= $myArray[4];
			$thisLevel		= $myArray[5];
			$thisTZ			= $myArray[6];
			$thisLocal		= $myArray[7];
			$thisUTC		= $myArray[8];
					
			$advisorUpdateLink		= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$thisAdvisor&strpass=2&inp_depth=one&doDebug=$doDebug&testMode=FALSE' target='_blank'>$thisAdvisor</a>";

			$smallClassCount++;
			$myInt				= $thisSize - $thisStudents;
			$availSeats			= $availSeats + $myInt;
			$content		.= "<tr><td style='vertical-align:top;'>$advisorUpdateLink</td>
									<td style='vertical-align:top;'>$thisClass</td>
									<td style='vertical-align:top;'>$thisLevel</td>
									<td style='vertical-align:top;'>$thisLanguage</td>
									<td style='vertical-align:top;'>$thisTZ</td>
									<td style='text-align:center;vertical-align:top;'>$thisSize</td>
									<td style='text-align:center;vertical-align:top;'>$thisStudents</td>
									<td>$thisLocal Local<br />$thisUTC UTC</td></tr>";
		}
		$content			.= "</table>
								<p>$smallClassCount Total Small Classes<br />
								$availSeats Number of unfilled seats</p>";

/////////////	end of small classes report

/////////////	Start of advisors with seats open report

		debugReport("<br />Starting the advisors with open seats report<br />");
		
		sort($seatsOpenArray);
		$content		.= "<a name='reportY'><h3>Advisors With Open Seats</h3></a>
							<table>
							<tr><th>Advisor</th>
								<th>Level</th>
								<th>Language</th>
								<th>TZ</th>
								<th>Class Size</th>
								<th>Students</th>
								<th>Open Seats</th>
								<th>Class Schedule</th></tr>";
		$seatsOpenCount	= 0;
		foreach($seatsOpenArray as $myValue) {
			$myArray		= explode("|",$myValue);
			$thisAdvisor	= $myArray[0];
			$thisOpen		= $myArray[1];
			$thisSize		= $myArray[2];
			$thisLevel		= $myArray[3];
			$thisLanguage	= $myArray[4];
			$thisTZ			= $myArray[5];
			$thisLocal		= $myArray[6];
			$thisUTC		= $myArray[7];
			$thisStudents	= $myArray[8];
					
			$advisorUpdateLink		= "<a href='$advisorUpdateURL?request_type=callsign&request_info=$thisAdvisor&strpass=2&inp_depth=one&doDebug=$doDebug&testMode=FALSE' target='_blank'>$thisAdvisor</a>";
			
			$seatsOpenCount	= $seatsOpenCount + $thisOpen;
			$content		.= "<tr><td style='vertical-align:top;'>$advisorUpdateLink</td>
									<td style='vertical-align:top;'>$thisLevel</td>
									<td style='vertical-align:top;'>$thisLanguage</td>
									<td style='vertical-align:top;'>$thisTZ</td>
									<td style='text-align:center;vertical-align:top;'>$thisSize</td>
									<td style='text-align:center;vertical-align:top;'>$thisStudents</td>
									<td style='text-align:center;vertical-align:top;'>$thisOpen</td>
									<td>$thisLocal Local<br />$thisUTC UTC</td></tr>";
		}
		$content			.= "</table>
								<p>$seatsOpenCount Total Seats Open</p>";

	
/////////////	End of advisors with seats open report


////////////	 students on hold report
		debugReport("Doing Students On Hold Report<br />");
//	holdArray[studentCallSign]
		$content		.= "<a name='reportH'><h3>Students On Hold Report</h3></a>
							<table>
							<tr><th>Level</th>
								<th>Language</th>
									<th>TZ</th>
									<th style='column-width:150px;'>Student</th>
									<th>Email</th>
									<th>Phone</th>
									<th>City</th>
									<th>State</th>
									<th>Country</th>
									<th style='column-width:200px;'>Class Choices</th></tr>";
		$holdCount			= 0;
		$myInt	 		= count($holdArray);
		if ($myInt > 0) {
			
			sort($holdArray);
			foreach($holdArray as $studentCallSign) {
				$student_first_name		= $studentArray[$studentCallSign]['first name'];
				$student_last_name		= $studentArray[$studentCallSign]['last name'];
				$student_email			= $studentArray[$studentCallSign]['email'];
				$student_phone			= $studentArray[$studentCallSign]['phone'];
				$student_text_message	= $studentArray[$studentCallSign]['text message'];
				$student_city			= $studentArray[$studentCallSign]['city'];
				$student_state			= $studentArray[$studentCallSign]['state'];
				$student_country		= $studentArray[$studentCallSign]['country'];
				$student_time_zone		= $studentArray[$studentCallSign]['time_zone'];
				$student_response		= $studentArray[$studentCallSign]['response'];
				$student_status			= $studentArray[$studentCallSign]['status'];
				$student_youth			= $studentArray[$studentCallSign]['youth'];
				$student_parent_email	= $studentArray[$studentCallSign]['parent email'];
				$student_first_choice	= $studentArray[$studentCallSign]['first choice'];
				$student_second_choice	= $studentArray[$studentCallSign]['second choice'];
				$student_third_choice	= $studentArray[$studentCallSign]['third choice'];
				$student_level			= $studentArray[$studentCallSign]['level'];
				$student_language		= $studentArray[$studentCallSign]['language'];
				$holdCount++;
					
				$studentUpdateLink		= "<a href='$studentUpdateURL?request_type=callsign&request_info=$studentCallSign&strpass=2&inp_depth=all&doDebug=$doDebug&testMode=FALSE' target='_blank'>$studentCallSign</a>";
			
				$content				.= "<tr><td style='vertical-align:top;'>$student_level</td>
												<td style='vertical-align:top;'>$student_language</td>
												<td style='vertical-align:top;'>$student_time_zone</td>
													<td style='vertical-align:top;'>$student_last_name, $student_first_name ($studentUpdateLink)</td>
													<td style='vertical-align:top;'>$student_email</td>
													<td style='vertical-align:top;'>$student_phone</td>
													<td style='vertical-align:top;'>$student_city</td>
													<td style='vertical-align:top;'>$student_state</td>
													<td style='vertical-align:top;'>$student_country</td>
													<td style='vertical-align:top;'>$student_first_choice<br />$student_second_choice<br />$student_third_choice</td></tr>";	
					
			}
		} else {
			$content	.= "<tr><td colspan='10'>No Students on Hold</td></tr>";
		}
		$content			.= "</table>
								<p>$holdCount Students on hold</p>";

//////////////	end of students on hold report
	
//////////////	override report
		debugReport("Doing the override Report<br />");

		$content		.= "<a name='reportO'><h3>Advisor Class Size Overriden Report</h3></a>
							<table>
							<tr><th>Advisor Classes Overridden</th></tr>";
		if (count($overrideArray) > 0) {
			foreach($overrideArray as $myValue) {
				$content	.= "<tr><td>$myValue</td></tr>";
			}
		}
		$content		.= "</table>";

//////////////	end of override report


//////////////	error report
		debugReport("Doing the error Report<br />");
//	errorArray[error]
		$content		.= "<a name='reportE'><h3>Errors Report</h3></a>
							<table>
							<tr><th>Error</th></tr>";
		if (count($errorArray) > 0) {
			foreach($errorArray as $myValue) {
				$content	.= "<tr><td>$myValue</td></tr>";
			}
		} else {
			$content		.= "<tr><td>No errors noted</td></tr>";
		}
		$content		.= "</table>";

//////////////	end of error report


		$content				.= "<p><a href='#report1'>Go to the Advisor Classes and Assigned Students Report</a><br />
									<a href='#report3'>Go to the Unassigned Students Report</a><br />
									<a href='#report2'>Go to the Student Assignment Information Report</a><br />
									<a href='#reportA'>Go to the Arbitrarily Assigned Students Report</a><br />
									<a href='#reportS'>Go to the Advisors with Small Classes Report</a><br />
									<a href='#reportY'>Go to the Advisors With Open Seats Report</a><br />
									<a href='#reportH'>Go to the Students on Hold Report</a><br />
									<a href='#reportO'>Go to the Advisor Class Size Overridden Report</a><br />
									<a href='#reportE'>Go to the Errors Report</a><br />
									</p>";



////////////// 	if requested, update student records with the assigned advisor

//	studentAssignedAdvisorArray[student_call_sign] = student_advisor|student_assigned_advisor_class

		if ($request_type == 'B' || $request_type == 'D' || $request_type == 'F') {			/// update students

			debugReport("<br /><b>Updating Students with their assignments</b><br />");
			foreach($studentAssignedAdvisorArray as $thisCallSign=>$myValue) {
				if ($studentArray[$thisCallSign]['assigned advisor'] == '') {
					$updateParams		= array();
					$myArray		= explode("|",$myValue);
					$thisAdvisor	= $myArray[0];
					$thisClass		= $myArray[1];

					debugReport("<br />Processing student $thisCallSign assigned to advisor $thisAdvisor class $thisClass<br />");
				
					$arbitrarilyAssigned		= '';
					if (in_array($thisCallSign,$arbitraryArray)) {
						$myStr				= 'arbitrary assignment ';
						$arbitrarilyAssigned = 'Y';
					} else {
						$myStr				= '';
					}
					$inp_data			= array('inp_student'=>$thisCallSign,
												'inp_semester'=>$nextSemester,
												'inp_assigned_advisor'=>$thisAdvisor,
												'inp_assigned_advisor_class'=>$thisClass,
												'inp_remove_status'=>'',
												'inp_arbitrarily_assigned'=>$myStr,
												'inp_method'=>'add',
												'jobname'=>$jobname,
												'userName'=>$userName,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);
								
					$addResult			= add_remove_student($inp_data);
					if ($addResult[0] === FALSE) {
						debugReport("<b>adding student $thisCallSign to advisor $thisAdvisor $thisClass failed: $addResult[1]</b><br />");
						$content		.= "could not find an open slot for student $thisCallSign in $thisAdvisor class $thisClass<br />";
						sendErrorEmail("$jobname adding student $thisCallSign to advisor $thisAdvisor $thisClass failed: $addResult[1]");
					}
				}
			}
		}
		
		
		
//// send email to arbitrarily assigned students
		
		if ($request_type == 'B' || $request_type == 'D' || $request_type == 'E') {

			debugReport("<br /><b>Send Email to Arbitrarily Assigned Students</b><br />");

			foreach($arbitraryArray as $thisCallSign) {
				if (array_key_exists($thisCallSign,$studentAssignedAdvisorArray)) {
					//// get the assignment from studentAssignedAdvisorArray
					$studentAssignedInfo		= $studentAssignedAdvisorArray[$thisCallSign];
					$myArray					= explode("|",$studentAssignedInfo);
					$thisAdvisor				= $myArray[0];
					$thisClass					= $myArray[1];
					
					/////// get the student data from studentArray
					$thisFirstName				= $studentArray[$thisCallSign]['first name'];
					$thisLastName				= $studentArray[$thisCallSign]['last name'];
					$thisFirstChoice			= $studentArray[$thisCallSign]['first choice'];
					$thisLevel					= $studentArray[$thisCallSign]['level'];
					$thisLanguage				= $studentArray[$thisCallSign]['language'];
					$thisTZ						= $studentArray[$thisCallSign]['time_zone'];
					$thisEmail					= $studentArray[$thisCallSign]['email'];
					
					/////// Now get the class times from advisorClassArray and convert to local
					$advisorClassArrayKey		= "$thisAdvisor|$thisClass";
					$thisClassTimeUTC			= $advisorClassArray[$advisorClassArrayKey]['time utc'];
					$thisClassDaysUTC			= $advisorClassArray[$advisorClassArrayKey]['days utc'];
					$result						= utcConvert('tolocal',$thisTZ,$thisClassTimeUTC,$thisClassDaysUTC);
					if ($result[0] == 'FAIL') {
						debugReport("utcConvert failed 'tolocal',$thisTZ,$thisClassTimeUTC,$thisClassDaysUTC<br />
								  Error: $result[3]<br />");
						$displayTimes			= 'ERROR';
						$displayDays			= 'ERROR';
					} else {
						debugReport("utcConvert returned $result[1] $result[2]<br />");
						$displayTimes			= $result[1];
						$displayDays			= $result[2];
					}
					debugReport("<br />Ready to send arbitrary email. Student data:<br />
								Advisor: $thisAdvisor<br />
								Class: $thisClass<br />
								Student: $thisCallSign<br />
								First Name: $thisFirstName<br />
								Last Name: $thisLastName<br />
								First Choice: $thisFirstChoice<br />
								Level: $thisLevel<br />
								Language: $thislanguage<br />
								TZ: $thisTZ<br />
								Email: $thisEmail<br />
								Advisor class schedule: $thisClassTimeUTC $thisClassDaysUTC<br />
								Local class schedule: $displayTimes $displayDays<br />");


					if ($thisFirstChoice == '') {
						$thisContent			= "
<p>To: $thisLastName, $thisFirstName ($thisCallSign):</p>
CW Academy is in the process of assigning students to advisor classes. You did not make any 
class times and days preferences. As a result, you have been <b>arbitrarily assigned</b> to 
the $thisAdvisor's $thisLevel $thisLanguage class being held around $displayTimes on $displayDays local time. $thisAdvisor 
will be contacting you to determine if this assignment will work for you. If not, please let 
the advisor know when you are contacted.</p>
<p>Thank you for your interest in the CW Academy<br />
CW Academy</p>
<table style='border:4px solid red;'><tr><td>
<p><span style='color:red;font-size:14pt;'><b>Do not reply to this email as the address is not monitored.</b> 
<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
Resolution</a> for assistance.</span></p></td></tr></table>";
					} else {
						$thisContent			= "
<p>To: $thisLastName, $thisFirstName ($thisCallSign):</p>
CW Academy is in the process of assigning students to advisor classes. Unfortunately, all of your 
class choice preferences were full. CW Academy has <b>arbitrarily assigned</b> you to 
the $thisAdvisor's $thisLevel $thisLanguage class being held around $displayTimes on $displayDays local time. $thisAdvisor 
will be contacting you to determine if this assignment will work for you. If not, please let 
the advisor know when you are contacted.</p>
<p>Thank you for your interest in the CW Academy<br />
CW Academy</p>
<table style='border:4px solid red;'><tr><td>
<p><span style='color:red;font-size:14pt;'><b>Do not reply to this email as the address is not monitored.</b> 
<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
Resolution</a> for assistance.</span></p></td></tr></table>";
					}
					$theSubject			= "CWA Update to Your Class Preferences";
					if ($testMode) {
						$theRecipient	= 'rolandksmith@gmail.com';
						$emailCode		= 2;
						$theSubject		= "TESTMODE $theSubject";
					} elseif ($request_type == 'E') {		//// BOBTEST	
						$theRecipient	= 'rolandksmith@gmail.com';
						$emailCode		= 2;
						$theSubject		= "BOBTEST $theSubject";
					} else {
						$theRecipient	= $thisEmail;
						$emailCode		= 12;
					}
					$increment2++;
					$mailResult			= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
															    'theSubject'=>$theSubject,
															    'jobname'=>$jobname,
															    'theContent'=>$thisContent,
															    'mailCode'=>$emailCode,
															    'increment'=>$increment2,
															    'testMode'=>$testMode,
															    'doDebug'=>$doDebug));
					if ($mailResult === TRUE) {
						debugReport("An email about the arbitrary assignment was sent to $theRecipient<br />");
						$advisorMailCount++;
					} else {
						$content .= "The mail send function to $theRecipient failed.<br /><br />";
					}
				}
			}
		}
	}
// Bypass:
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	debugReport("<br />Testing to save report: $inp_report<br />");
	if ($inp_report == 'Y') {
		debugReport("Calling function to save the report as Assign Students to Advisors<br />");
		$storeResult	= storeReportData_v2("Assign Students to Advisors",$content,0,0);
		if ($storeResult[0] !== FALSE) {
			$content	.= "<br />Report stored in reports table as $storeResult[1] with id $storeResult[2]";
		} else {
			$content	.= "<br />Storing the report in the reports table failed";
		}
	}
	if ("2" == $strPass) {	
		if ($keepDebug) {
			$storeResult	= storeReportData_v2("Assign Students to Advisors Debug",$doDebugReport,0,0);
			if ($storeResult !== FALSE) {
				$content	.= "<br />Debug Report stored in reports table as $storeResult[1] with id $storeResult[2]
								Click <a href='$siteURL/cwa-display-saved-report/?strpass=3&inp_id=$storeResult[2]' target='_blank'>HERE</a>";
			} else {
				$content	.= "<br />Storing the Debug report in the reports table failed";
			}
		}
	}

	Bypass:

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
add_shortcode ('assign_students_to_advisors', 'assign_students_to_advisors_func');
