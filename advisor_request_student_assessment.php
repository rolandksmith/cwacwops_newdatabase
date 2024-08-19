function advisor_request_student_assessment_func() {

/* Request Student Assessment

	Gives the advisor a page listing the students along with the default 
	settings. Advisor can choose which students get the assessment. 
	
	Upon completion of the assessment, the results are provided to the advisor
	
	created 30Dec23 by Roland
*/

	global $wpdb, $doDebug, $testMode, $audioAssessmentTableName, $alreadyPlayed;



	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$userName						= $initializationArray['userName'];
	$userRole						= $initializationArray['userRole'];
	$holdUser						= $userName;
	$holdRole						= $userRole;
	if ($userRole != 'administrator') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$siteURL			= $initializationArray['siteurl'];
	$currentDateTime	= $initializationArray['currentDateTime'];
	$versionNumber		= '1';
	

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-advisor-request-student-assessment/";
	$inp_semester				= '';
	$jobname					= "Advisor Request Student Assessment";
	$studentCallSign			= "";
	$studentEmail				= "";
	$studentPhone				= "";
	$inp_callsign				= "";
	$studentName				= "";
	$advisorCallSign			= "";
	$studentLevel				= "";
	$inp_mode					= "";
	$inp_comments				= "";
	$inp_verbose				= 'N';
	$runtype					= 'student';
	$advisorCallSign			= '';
	$email						= '';
	$phone						= '';
	$studentSemester			= '';
	$inPastStudent				= TRUE;
	$actionDate					= date('Y-m-d H:i:s');
	$controlCode				= '';
	$inp_freq					= '';
	$inp_questions				= '';
	$inp_words					= '';
	$inp_chars					= '';
	$inp_cscount				= '';
	$inp_makeup					= '';
	$inp_answers				= '';
	$inp_vocab					= '';
	$inp_students				= array();
	$inp_wpm					= '';
	$inp_eff					= '';
	$advisor_first_name			= '';
	$advisor_last_name			= '';
	$advisorClassCount			= 0;
	$advisorEmail				= '';
	$incl_advisor				= 'N';
	$studentList				= '';

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br />\n";
				}
			}
			if ($str_key 				== 'enstr') {
				$enstr					= $str_value;
				$stringToPass			= base64_decode($enstr);
// echo "stringToPass: $stringToPass<br />";
				$myArray				= explode("&",$stringToPass);
				foreach($myArray as $myValue) {
					$thisArray			= explode("=",$myValue);
					${$thisArray[0]}	= $thisArray[1];
					if ($doDebug) {
						echo "Key: $thisArray[0] | Value: $thisArray[1]<br />";
					}
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_freq") {
				$inp_freq		 = $str_value;
				$inp_freq		 = filter_var($inp_freq,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_questions") {
				$inp_questions		 = $str_value;
				$inp_questions		 = filter_var($inp_questions,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_words") {
				$inp_words		 = $str_value;
				$inp_words		 = filter_var($inp_words,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_chars") {
				$inp_chars		 = $str_value;
				$inp_chars		 = filter_var($inp_chars,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_cscount") {
				$inp_cscount		 = $str_value;
				$inp_cscount		 = filter_var($inp_cscount,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_makeup") {
				$inp_makeup		 = $str_value;
				$inp_makeup		 = filter_var($inp_makeup,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_answers") {
				$inp_answers		 = $str_value;
				$inp_answers		 = filter_var($inp_answers,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_vocab") {
				$inp_vocab		 = $str_value;
				$inp_vocab		 = filter_var($inp_vocab,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_students") {
				$inp_students		 = $str_value;
//				$inp_students		 = filter_var($inp_students,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_wpm") {
				$inp_wpm		 = $str_value;
				$inp_wpm		 = filter_var($inp_wpm,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_eff") {
				$inp_eff		 = $str_value;
				$inp_eff		 = filter_var($inp_eff,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_callsign") {
				$inp_callsign		 = $str_value;
				$inp_callsign		 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "incl_advisor") {
				$incl_advisor		 = $str_value;
				$incl_advisor		 = strtoupper(filter_var($incl_advisor,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "token") {
				$token				 = $str_value;
				$token		 		= strtoupper(filter_var($token,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "nextClass") {
				$nextClass		 = $str_value;
				$nextClass		 = strtoupper(filter_var($nextClass,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "advisorCallSign") {
				$advisorCallSign		 = $str_value;
				$advisorCallSign		 = strtoupper(filter_var($advisorCallSign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "advisorCallSign") {
				$advisorCallSign		 = $str_value;
				$advisorCallSign		 = strtoupper(filter_var($advisorCallSign,FILTER_UNSAFE_RAW));
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
				if ($inp_mode == 'TESTMODE' || $inp_mode == 'tm') {
					$testMode = TRUE;
				}
			}
		}
	}

	if ($inp_mode == 'tm') {
		$testMode	= TRUE;
	}
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
	<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
<tr><td>Verbose Debugging?</td>
	<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
		<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
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
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$studentTableName			= "wpw1_cwa_consolidated_student2";
		$audioAssessmentTableName	= "wpw1_cwa_new_assessment_data2";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_consolidated_student";
		$audioAssessmentTableName	= "wpw1_cwa_new_assessment_data";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass";
	}
	$theSemester				= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$theSemester			= $nextSemester;
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass $strPass with inp_callsign of $inp_callsign and userName of $userName<br />";
		}

		if ($inp_callsign == '') {
			$inp_callsign	= strtoupper($userName);
		}
		
		// get the advisor information
		
		$sql				= "select * from $advisorTableName 
								where call_sign = '$inp_callsign' 
								and semester = '$theSemester'";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				return $content;
			}
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				$content			.= "<h3>$jobname</h3>
										<p><b>Instructions:</b><br />
										Your first class will be displayed and all students 
										will be selected along with the default parameters 
										for the class level.<p>
										<p>Deselect any students that should not get the assessment
										request. If none of the students in this class are to 
										receive the assessment request, uncheck all of the students 
										and this class will be bypassed.</p>
										<p>Change any of the parameters for the assessment as needed.</p>
										<p><b>If you have more than one class</b>, after you make the 
										selections for the first class and send the assessment 
										request emails (if any), the program will then do the same for the 
										next class</p>
										<p>You can also include yourself as the advisor and receive the 
										assessment request</p>";
				$selectionTable	= "<table style='width:auto;'>";
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
					$advisor_replacement_status 		= $advisorRow->replacement_status;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

					// how many classes does this advisor have
					$sql			= "select count(advisor_call_sign) from $advisorClassTableName 
										where advisor_call_sign = '$inp_callsign' 
										and semester = '$theSemester'";
					$advisorClassCount	= $wpdb->get_var($sql);
					if ($advisorClassCount == NULL) {
						$advisorClassCount	= 0;
					}
					if ($advisorClassCount == 0) {
						$content		.= "<b>ERROR</b> There are no class records for advisor $inp_callsign";
					} else {
						$strtopass		= "inp_callsign=$inp_callsign&advisor_first_name=$advisor_first_name&advisor_last_name=$advisor_last_name&advisorClassCount=$advisorClassCount&advisorEmail=$advisor_email";
						$enstr			= base64_encode($strtopass);
						$strPass		= "2";
						$nextClass		= 1;
					}
				}
			} else {
				$content				.= "<h3>$jobname</h3><p>No advisor record found for $inp_callsign</p>";
			}
		}
	}
	 
	if ("2" == $strPass) {
		if($doDebug) {
			echo "<br />Arrived at $strPass<br />
					nextClass: $nextClass<br />
					advisorClassCount: $advisorClassCount<br />					
					inp_call_sign: $inp_callsign<br />";
		}
		
		if ($nextClass > 1) {
			$content	.= "<h3>$jobname</h3>";
		}
		$sql			= "select * from $advisorClassTableName 
							where advisor_call_sign = '$inp_callsign' 
							and semester = '$theSemester' 
							and sequence = $nextClass";
		$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				return $content;
			}
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
					$class_number_students					= $advisorClassRow->number_students;
					$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
					$class_comments							= $advisorClassRow->class_comments;
					$copycontrol							= $advisorClassRow->copy_control;

					$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

					if ($class_number_students > 0) {
						// build list of students
						$studentList	= '';
						for ($snum=1;$snum<31;$snum++) {
							if ($snum < 10) {
								$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
							} else {
								$strSnum		= strval($snum);
							}
							$studentCallSign	= ${'advisorClass_student' . $strSnum};
							if ($studentCallSign != '') {
								// get the student name
								$sql			= "select first_name, last_name, 
														student_id, student_status, 
														promotable  
													from $studentTableName 
													where call_sign = '$studentCallSign' 
													and semester = '$theSemester'";
								$wpw1_cwa_student		= $wpdb->get_results($sql);
								if ($wpw1_cwa_student === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$lastError			= $wpdb->last_error;
									if ($lastError != '') {
										handleWPDBError($jobname,$doDebug);
										$content		.= "Fatal program error. System Admin has been notified";
										return $content;
									}
									$numSRows			= $wpdb->num_rows;
									if ($doDebug) {
										$myStr			= $wpdb->last_query;
										echo "ran $myStr<br />and found $numSRows rows<br />";
									}
									if ($numSRows > 0) {
										foreach ($wpw1_cwa_student as $studentRow) {
											$student_ID								= $studentRow->student_id;
											$student_first_name						= $studentRow->first_name;
											$student_last_name						= stripslashes($studentRow->last_name);
											$student_status							= $studentRow->student_status;
											$student_promotable						= $studentRow->promotable;

											$student_last_name 						= no_magic_quotes($student_last_name);
											
											if ($student_promotable != 'W') {
												if ($student_status == 'Y' || $student_status == 'S') {
													$studentList	.= "<input type='checkbox' name='inp_students[]' value='$student_ID' checked>$student_last_name, $student_first_name ($studentCallSign)\n<br />";
												}
											}
										}
									}
								}
							}
						}
					} else {
						$content		.= "No students found for this class<br />";
					}
				}
				if ($studentList == '') {
					if ($doDebug) {
						echo "no students put into the student list<br />";
					}
					$content			.= "No students assigned to this advisor<br />";
				} else {
					// buildclass selection
					if ($advisorClass_level == 'Beginner') {
						$wpm_params			= "<input type='radio' class='formInputButton' name='inp_wpm' value='15'> 15cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='18' checked> 18cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='20'> 20cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='25'> 25cpm";
						$eff_params			= "<input type='radio' class='formInputButton' name='inp_eff' value='4'> 4wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='6' checked> 6wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='8'> 8wpm Effective";
						$freq_params		= "<input type='hidden' name='inp_freq' value='450,550,600,700'>";
						$questions_params	= "<input type='radio' class='formInputButton' name='inp_questions' value='3'>3 Questions<br />
											   <input type='radio' class='formInputButton' name='inp_questions' value='5' checked> 5 Questions<br />
											   <input type='radio' class='formInputButton' name='inp_questions' value='7'> 7 Questions";
						$words_params		= "<input type='hidden' name='inp_words' value='1'>";
						$characters_params	= "<input type='hidden' name='inp_chars' value='3'>";
						$callsigns_params	= "<input type='radio' class='formInputButton' name='inp_cscount' value='0' checked>No Callsigns";
						$makeup_params		= "<input type='hidden' name='inp_makeup' value=''>";
						$answers_params		= "<input type='hidden' name='inp_answers' value='5'>";
						$vocab_params		= "<input type='hidden' name='inp_vocab' value='threek'>";
					} elseif ($advisorClass_level == 'Fundamental') {
						$wpm_params			= "<input type='radio' class='formInputButton' name='inp_wpm' value='18'> 18cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='20'> 20cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='25' checked> 25cpm";
						$eff_params			= "<input type='radio' class='formInputButton' name='inp_eff' value='6'> 6wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='8'> 8wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='10' checked> 10wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='12'> 12wpm Effective";
						$freq_params		= "<input type='hidden' name='inp_freq' value='450,550,600,700'>";
						$questions_params	= "<input type='radio' class='formInputButton' name='inp_questions' value='3'>3 Questions<br />
											   <input type='radio' class='formInputButton' name='inp_questions' value='5' checked> 5 Questions<br />
											   <input type='radio' class='formInputButton' name='inp_questions' value='7'> 7 Questions";
						$words_params		= "<input type='hidden' name='inp_words' value='2'>";
						$characters_params	= "<input type='hidden' name='inp_chars' value='4'>";
						$callsigns_params	= "<input type='radio' class='formInputButton' name='inp_cscount' value='0'>No Callsigns<br />
											   <input type='radio' class='formInputButton' name='inp_cscount' value='1' checked>1 Callsign<br />
											   <input type='radio' class='formInputButton' name='inp_cscount' value='2'>2 Callsigns";
						$makeup_params		= "<input type='hidden' name='inp_makeup' value='3-4'>";
						$answers_params		= "<input type='hidden' name='inp_answers' value='5'>";
						$vocab_params		= "<input type='hidden' name='inp_vocab' value='threek'>";
					} elseif($advisorClass_level == 'Intermediate') {
						$wpm_params			= "<input type='radio' class='formInputButton' name='inp_wpm' value='18'> 18cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='20'> 20cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='25' vhrvkrf> 25cpm";
						$eff_params			= "<input type='radio' class='formInputButton' name='inp_eff' value='13'> 13wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='15'> 15wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='18'> 18wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='18' checked> 20wpm Effective";
						$freq_params		= "<input type='hidden' name='inp_freq' value='450,550,600,700'>";
						$questions_params	= "<input type='radio' class='formInputButton' name='inp_questions' value='3'>3 Questions<br />
											   <input type='radio' class='formInputButton' name='inp_questions' value='5' checked> 5 Questions<br />
											   <input type='radio' class='formInputButton' name='inp_questions' value='7'> 7 Questions";
						$words_params		= "<input type='hidden' name='inp_words' value='2'>";
						$characters_params	= "<input type='hidden' name='inp_chars' value='4'>";
						$callsigns_params	= "<input type='radio' class='formInputButton' name='inp_cscount' value='0'>No Callsigns<br />
											   <input type='radio' class='formInputButton' name='inp_cscount' value='1' checked>1 Callsign<br />
											   <input type='radio' class='formInputButton' name='inp_cscount' value='2'>2 Callsigns";
						$makeup_params		= "<input type='hidden' name='inp_makeup' value='3-5'>";
						$answers_params		= "<input type='hidden' name='inp_answers' value='5'>";
						$vocab_params		= "<input type='hidden' name='inp_vocab' value='threek'>";
					} elseif ($advisorClass_level == 'Advanced') {
						$wpm_params			= "<input type='radio' class='formInputButton' name='inp_wpm' value='20'> 20cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='25'> 25cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='30' checked> 30cpm<br />
												<input type='radio' class='formInputButton' name='inp_wpm' value='35'> 35cpm";
						$eff_params			= "<input type='radio' class='formInputButton' name='inp_eff' value='20'> 20wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='25'> 25wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='30' checked> 30wpm Effective<br />
												<input type='radio' class='formInputButton' name='inp_eff' value='35'> 35wpm Effective<br />";
						$freq_params		= "<input type='hidden' class='formInputButton' name='inp_freq' value='450,550,600,700'>";
						$questions_params	= "<input type='radio' class='formInputButton' name='inp_questions' value='3'>3 Questions<br />
											   <input type='radio' class='formInputButton' name='inp_questions' value='5' checked> 5 Questions<br />
											   <input type='radio' class='formInputButton' name='inp_questions' value='7'> 7 Questions";
						$words_params		= "<input type='hidden' name='inp_words' value='3'>";
						$characters_params	= "<input type='hidden' name='inp_chars' value='5'>";
						$callsigns_params	= "<input type='radio' class='formInputButton' name='inp_cscount' value='0'>No Callsigns<br />
											   <input type='radio' class='formInputButton' name='inp_cscount' value='1' checked>1 Callsign<br />
											   <input type='radio' class='formInputButton' name='inp_cscount' value='2'>2 Callsigns";
						$makeup_params		= "<input type='hidden' name='inp_makeup' value='3-5'>";
						$answers_params		= "<input type='hidden' name='inp_answers' value='7'>";
						$vocab_params		= "<input type='hidden' name='inp_vocab' value='threek'>";
					}
					$nextClass++;
					$content	.= "<h4>Class $advisorClass_sequence $advisorClass_level</h4>
									<form method='post' action='$theURL' 
									name='advisor_selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='5'>
									<input type='hidden' name='enstr' value='$enstr'>
									<input type='hidden' name='nextClass' value='$nextClass'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									$freq_params
									$words_params
									$characters_params
									$makeup_params
									$answers_params
									$vocab_params
									<table>
									<tr><td style='vertical-align:top;'>Select Students for Evaluation<br />
													$studentList
													<br />Include Advisor?<br />
													<input type='radio' class='formInputButton' name='incl_advisor' value='N' checked> No<br />
													<input type='radio' class='formInputButton' name='incl_advisor' value='Y'> Yes</td>
													</td>
										<td style='vertical-align:top;'>Select Speed Parameters<br /><br />
											Character Speed in words per Minute:<br />
											$wpm_params<br /><br />
											Effective (Farnsworth) Speed:<br />
											$eff_params</td>
										<td style='vertical-align:top;'>Select Questions Parameters<br /><br />
											Number of Questions<br />
											$questions_params<br /><br />
											How Many Callsigns to Include<br />
											$callsigns_params</td></tr>
									<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
									</table></form>";
					if ($advisorClass_level == 'Beginner') {
						$content	.= "<p>You can change the speed and number of questions parameters. Callsigns are not 
										available for Beginner Level students. The questions will consist of 2-3 character 
										words or abbreviations.</p>";
					} elseif ($advisorClass_level == 'Fundamental') {
						$content	.= "<p>You can change the speed and questions parameters. If any callsigns are selected, 
										they will be included in the number of questions selected. Questions will consist of 
										2-3 character words or abbreviations. Each question will be either a callsign (if 
										callsigns were selected) or two words or abbreviations.<p>";
					} elseif ($advisorClass_level == 'Intermediate') {
						$content	.= "<p>You can change the speed and questions parameters. If any callsigns are selected, 
										they will be included in the number of questions selected. Questions will consist of 
										2-4 character words or abbreviations. Each question will be either a callsign (if 
										callsigns were selected) or two words or abbreviations.<p>";
					} elseif ($advisorClass_level == 'Advanced') {
						$content	.= "<p>You can change the speed and questions parameters. If any callsigns are selected, 
										they will be included in the number of questions selected. Questions will consist of 
										2-5 character words or abbreviations. Each question will be either a callsign (if 
										callsigns were selected) or two words or abbreviations.<p>";
					}
				}
			} else {
				$content	.= "No advisor record found for $inp_callsign";
			}
		}
		

///// Pass 5 -- do the work


	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass $strPass<br />";
		}
		if ($incl_advisor == 'Y') {
			$inp_students[]			= 'advisor';
		}
		
		if ($doDebug) {
			echo "inp_students<br /><pre>";
			print_r($inp_students);
			echo "</pre><br />";
		}
		
		foreach($inp_students as $thisID) {
			if ($doDebug) {
				echo "<br />Processing inp_students ... thisID: $thisID<br />";
			}
			if ($thisID == 'advisor') {
				$student_first_name		= $advisor_first_name;
				$student_last_name		= $advisor_last_name;
				$student_call_sign		= $inp_callsign;
				$student_level			= "";
				$student_email			= $advisorEmail;
				$haveData				= TRUE;
				$haveUsername			= TRUE;						
			} else {

				$sql					= "select * from $studentTableName 
											where student_id = $thisID";
				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						return $content;
					}
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
							$student_catalog_options				= $studentRow->catalog_options;
							$student_flexible						= $studentRow->flexible;
							$student_date_created 					= $studentRow->date_created;
							$student_date_updated			  		= $studentRow->date_updated;
	
							$student_last_name 						= no_magic_quotes($student_last_name);
							$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
							
							// determine if the student has a username record
	
							$haveData			= TRUE;
							$haveUsername		= FALSE;						
							$sql				= "SELECT id
													FROM `wpw1_users` 
													where user_login like '$student_call_sign'";
							$result				= $wpdb->get_results($sql);
							if ($result === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$numRows		= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $sql<br />and retrieved $numRows rows<br />";
								}
								if ($numRows > 0) {
									$haveUsername	= TRUE;
								}
							}
						}
					}
				}
			}
			if ($doDebug) {
				if ($haveData) {
					echo "haveData is TRUE. Sending email and setting reminder<br />";
				} else {
					echo "haveData is FALSE. No email and no reminder<br />";
				}
				if ($haveUsername) {
					echo "haveUsername is TRUE<br />";
				} else {
					echo "haveUsername is FALSE<br />";
				}
			}	
			if ($haveData) {	
											
				// format and send the email to the student
				$theSubject			= "CW Academy Request for You";
				$theContent			= "To: $student_last_name, $student_first_name ($student_call_sign):
<p>Your $student_level Level class advisor $advisor_first_name $advisor_last_name ($inp_callsign) has requested 
that you do a Morse code proficiency assessment.</p>";
				if (!$haveUsername) {
					$theContent		.= "<p>Since you signed up for you $student_level Level 
class, CW Academy has implemented additional user data security measures. You will need to set up 
a username and password, read the email that will be sent to you, verify your username, and then 
log in to the CW Academy website. To set up your username and password, click 
<a href='https://cwa.cwops.org/register/'>HERE</a></p>";
				} else {
					$theContent		.= "<p>Please log into the CW Academy website by 
clicking <a href='https://cwa.cwops.org/program-list/'>HERE</a> and follow the instructions that 
will be displayed there.</p>";
				}
				$theContent		.= "<p>73,<br />CW Academy</p>";
				if ($testMode) {
					$theSubject	= "TESTMODE $theSubject";
					$myTo		= "rolandksmith@gmail.com";
					$mailCode	= '2';
				} else {
					$myTo		= $student_email;
					$mailCode	= '13';
				}
				$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
															'theSubject'=>$theSubject,
															'theContent'=>$theContent,
															'theCc'=>'',
															'theAttachment'=>'',
															'mailCode'=>$mailCode,
															'jobname'=>$jobname,
															'increment'=>0,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug));
				if ($mailResult === FALSE) {
					if ($doDebug) {
						echo "sending the email to $myTo failed<br />";
						sendErrorEmail("$jobname sending email to $myTo failed");
					}
				} else {
					if ($doDebug) {
						echo "email sent<br />";
					}
				}

				// format and store the reminder for the student
				$myStr				= '0';
				if ($inp_cscount != '' && $inp_cscount > 0) {
					$myStr			= $inp_cscount . "($inp_makeup)";
				}
				
				$thisCallsign		= $student_call_sign;		// person doing the assessment
				$thiswpm			= $inp_wpm;					// word per minute
				$thiseff			= $inp_eff;					// effective speed
				$thisFreq			= $inp_freq;				// list of frequencies 400 - 700
				$thisQuestions		= $inp_questions;			// number of questions 
				$thisWords			= $inp_words;				// number of words per question
				$thisCharacters		= $inp_chars;				// max characters per word 3,4,5,6
				$thisCallsigns		= $myStr;					// how many callsigns to include 2(2-5)
				$thisAnswers		= $inp_answers;				// how many answers to display
				$thisLevel			= $student_level;			// level of the exam
				$token				= mt_rand();				// token to identify this activity
				$thisVocab			= "threek";					// either threek or original
				$thisInfor			= "Advisor Request";		// reason for the assessment

				$url 		= "<a href='https://cw-assessment.vercel.app?mode=specific&callsign=$thisCallsign&cpm=$thiswpm&eff=$thiseff&freq=$thisFreq&questions=$thisQuestions&words=$thisWords&characters=$thisCharacters&callsigns=$thisCallsigns&answers=$thisAnswers&level=$thisLevel&token=$token&vocab=$thisVocab&infor=$thisInfor";
				$myStr		= "$siteURL/cwa-advisor-request-student-assessment/?strpass=10&inp_callsign=$student_call_sign&token=$token";
				$returnurl	= urlencode($myStr);
				$url		= "$url" . "&returnurl=$returnurl'>Perform Assessment</a>";
				
				$reminder_text		= "<b>Morse Code Assessment</b> Your advisor $inp_callsign 
requests that you do a Morse code proficiency assessment. The assessment program will give you $thisQuestions 
questions in Morse code and then display a set of multiple choice answers to chose from. The questions will 
consist of $thisWords words. Each word will have 2-$thisCharacters characters, some of which may be abbreviations 
ot random characters. After 
starting the assessment, the program will show some generic information about the process. When you 
complete the assessment, the program will display your results and will also make the results 
available to your advisor. To start the assessment, please click $url.";
				$effective_date		 	= date('Y-m-d H:i:s');
				$closeStr				= strtotime("+5 days");
				$close_date				= date('Y-m-d H:i:s', $closeStr);
				$inputParams		= array("effective_date|$effective_date|s",
											"close_date|$close_date|s",
											"resolved_date||s",
											"send_reminder|N|s",
											"send_once|N|s",
											"call_sign|$student_call_sign|s",
											"role||s",
											"email_text||s",
											"reminder_text|$reminder_text|s",
											"resolved||s",
											"token|$token|s");
				$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
				if ($insertResult[0] === FALSE) {
					if ($doDebug) {
						echo "inserting reminder failed: $insertResult[1]<br />";
					}
					$content		.= "Inserting reminder failed: $insertResult[1]<br />";
				} else {
					$content		.= "Reminder successfully added<br />";
				}
			}
		}
		if ($nextClass <= $advisorClassCount) {
			$content		.= "<h4>Next Class</h4>
								<p>All students processed for this class. You have 
								another class. Please click the 'Next' button 
								to do the next class</p>
								<form method='post' action='$theURL' 
								name='advisor_selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='2'>
								<input type='hidden' name = 'inp_callsign' value='$inp_callsign'>
								<input type='hidden' name='enstr' value='$enstr'>
								<input type='hidden' name='nextClass' value='$nextClass'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<table style='width:auto;'>
								<tr><td><input class='formInputButton' type='submit' value='Next' /></td></tr>
								</table></form>";
		} else {
			$content		.= "<h3>$jobname</h3>
								<p>All students and classes processed.</p>";
		}




	} elseif ("10" == $strPass) {
		
		if ($doDebug) {
			echo "<br />arrived at $strPass with <br />
					inp_callsign: $inp_callsign<br />
					token: $token<br />";
		}
		
		$doProceed	= TRUE;
		
		$content	.= "<h3>$jobname</h3>
						<h4>Morse Code Assessment Results</h4>";
		$bestResultBeginner		= 0;
		$didBeginner			= FALSE;
		$bestResultFundamental	= 0;
		$didFundamental			= FALSE;
		$bestResultIntermediate	= 0;
		$didIntermediate		= FALSE;
		$bestResultAdvanced		= 0;
		$didAdvanced			= FALSE;
		$retVal					= displayAssessment('',$token,$doDebug);
		if ($retVal[0] === FALSE) {
			if ($doDebug) {
				echo "displayAssessment returned FALSE. Called with $inp_callsign, $token<br />";
			}
			$content	.= "No data to display.<br />Reason: $retVal[1]";
		} else {
			$content	.= $retVal[1];
			$myArray	= explode("&",$retVal[2]);
			foreach($myArray as $thisValue) {
				$myArray1	= explode("=",$thisValue);
				$thisKey	= $myArray1[0];
				$thisData	= $myArray1[1];
				$$thisKey	= $thisData;
				if ($doDebug) {
					echo "$thisKey = $thisValue<br />";
				}
			}
			$content		.= "<p>You have completed the Morse Code Proficiency 
								assessment.<br />";
			if ($didBeginner) {
				$content	.= "Your Beginner Level assessment score was $bestResultBeginner%<br />";
			}
			if ($didFundamental) {
				$content	.= "Your Fundamental Level assessment score was $bestResultFundamental%<br />";
			}
			if ($didIntermediate) {
				$content	.= "Your Intermediate Level assessment score was $bestResultIntermediate%<br />";
			}
			if ($didAdvanced) {
				$content	.= "Your Advanced Level assessment score was $bestResultAdvanced%<br />";
			}
			//// resolve a reminder
			if ($token != '') {
				$resolveResult				= resolve_reminder($inp_callsign,$token,$testMode,$doDebug);
				if ($resolveResult === FALSE) {
					if ($doDebug) {
						echo "resolve_reminder for $inp_callsign and $token failed<br />";
					}
				}
				// get the advisor info from the student record
				$sql		= "select first_name,
									  last_name,
									   assigned_advisor, 
									    assigned_advisor_class 
								from $studentTableName 
								where call_sign = '$inp_callsign' 
								and semester = '$theSemester'";
				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						return $content;
					}
					$numSRows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numSRows rows<br />";
					}
					if ($numSRows > 0) {
						foreach ($wpw1_cwa_student as $studentRow) {
							$student_first_name						= $studentRow->first_name;
							$student_last_name						= stripslashes($studentRow->last_name);
							$student_assigned_advisor				= $studentRow->assigned_advisor;
							$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;

							// now get the advisor information
							$sql		= "select email 
											from $advisorTableName 
											where call_sign = '$student_assigned_advisor' 
											and semester='$theSemester'";
							$wpw1_cwa_advisor	= $wpdb->get_results($sql);
							if ($wpw1_cwa_advisor === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$lastError			= $wpdb->last_error;
								if ($lastError != '') {
									handleWPDBError($jobname,$doDebug);
									$content		.= "Fatal program error. System Admin has been notified";
									return $content;
								}
								$numARows			= $wpdb->num_rows;
								if ($doDebug) {
									$myStr			= $wpdb->last_query;
									echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
								}
								if ($numARows > 0) {
									foreach ($wpw1_cwa_advisor as $advisorRow) {
										$advisor_email 						= strtolower($advisorRow->email);

										// add advisor reminder

										$enstr		= base64_encode("advisor_call_sign=$student_assigned_advisor&inp_callsign=$inp_callsign&token=$token");
										$reminder_text		= "<b>Morse Code Assessment Result</b> Your student 
$student_last_name, $student_first_name ($inp_callsign) has completed the Morse code assessment you requested. 
Click <a href='$siteURL/cwa-view-a-student-cw-assessment-v2/?strpass=2&enstr=$enstr' target='_blank'>HERE</a> to view the results.";  
										$effective_date		 	= date('Y-m-d H:i:s');
										$closeStr				= strtotime("+5 days");
										$close_date				= date('Y-m-d H:i:s', $closeStr);
										$inputParams		= array("effective_date|$effective_date|s",
																	"close_date|$close_date|s",
																	"resolved_date||s",
																	"send_reminder|N|s",
																	"send_once|N|s",
																	"call_sign|$student_assigned_advisor|s",
																	"role||s",
																	"email_text||s",
																	"reminder_text|$reminder_text|s",
																	"resolved||s",
																	"token|$token|s");
										$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
										if ($insertResult[0] === FALSE) {
											if ($doDebug) {
												echo "inserting reminder failed: $insertResult[1]<br />";
											}
											sendErrorEmail("$jobname Pass10 Inserting advisor reminder failed: $insertResult[1]");
										}

										// finally, send email to advisor that the assessment is done
										$theSubject			= "CW Academy -- Student Completed Requested Assessment";
										$theContent			= "Your student $student_last_name, $student_first_name ($inp_callsign) 
has completed the requested Morse code assessment. Please log into 
<a href='$siteURL/program-list/'>CW Academy</a> to see the results.";			
										if ($testMode) {
											$theSubject	= "TESTMODE $theSubject";
											$myTo		= "rolandksmith@gmail.com";
											$mailCode	= '2';
										} else {
											$myTo		= $advisor_email;
											$mailCode	= '13';
										}
										$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
																					'theSubject'=>$theSubject,
																					'theContent'=>$theContent,
																					'theCc'=>'',
																					'theAttachment'=>'',
																					'mailCode'=>$mailCode,
																					'jobname'=>$jobname,
																					'increment'=>0,
																					'testMode'=>$testMode,
																					'doDebug'=>$doDebug));
										if ($mailResult === FALSE) {
											if ($doDebug) {
												echo "sending the email to $myTo failed<br />";
												sendErrorEmail("$jobname sending email to $myTo failed");
											}
										} else {
											if ($doDebug) {
												echo "email sent<br />";
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
	
	
	
	} elseif ("15" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass $strPass -- order up evaluations for an advisor<br />";
		}
		
		$content	.= "<h3>$jobname</h3>
						<p>Enter the advisor's callsign you wish to emulate/p>
						<form method='post' action='$theURL' 
						name='advisor_emulation_form' ENCTYPE='multipart/form-data'>
						<input type='hidden' name='strpass' value='1'>
						<table>
						<tr><td>Advisor Callsign</td>
							<td><input type='text' class='formInputText' size='15' maxlength='30' name='inp_callsign' autofocus></td></tr>
						$testModeOption
						<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
						</table></form>";
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('advisor_request_student_assessment', 'advisor_request_student_assessment_func');
