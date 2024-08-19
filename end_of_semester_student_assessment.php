function end_of_semester_student_assessment_func() {

/* End of Semester Student Assessment

	Student is sent a link to this function by the advisor in program 
	Send End of Semester Evaluation Email to Advisors
	When student clicks on the link, the program does the following;
	1. 	gets the student info from either student or student 
	2. 	gets the level for the student
	3. 	Checks the audio assessment file to see if the student has already done the assessment. 
		if so, tell the student and end the program.
	4. 	Gets two audio files to play
		if the level is		Beginner		get BegBas files
							Fundamental		get BasInt files
							Intermediate	get IntAdv files
							Advanced		get Adv files
	5. 	Display a form with instructions along with the two audio files to be played
		with a text box below each file for the student to enter what was heard when the file was played
	6.	The student submits the info and the text from the two audio files along with the student's
		input is sent to pass 3 as an encoded string:
			
	7.	In pass 3 the text files are prepared:
		Remove any = signs
		Trim the text to get rid of white space on either end
		Set the text to lower case
	8. 	Do a levenshtein comparison of what the student entered to what the content of the 
		file was. The function returns how many characters had to change for the student's 
		response to match what was in the audio clip. If it returns a 0, the student had 
		100% copy
		
		If the return is > 0, then get the length of the audio clup and get the percentage 
		right: 100 - (returned info / length of the clip)
		
	9.	Show the student the audio clip text, what the student entered, and the percentage
		
	10.	Send an email to the advisor with the same information
		
	11.	Log the info in the audio assessment file
	
	
	When the student clicks on the link, an encoded string is sent to this program
		studentCallSign=(student call sign)
		studentEmail=(student email)
		studentPhone=(student phone)
		strpass=2
		
	Pass 1 is limited to people in the validTestMode array
	
	Modified 16Apr23 by Roland to fix action_log
	Modified 13Jul23 by Roland to use consolidated tables
	Modified 31Aug23 by Roland to turn off dodebug and testmode if validUser is N
	
*/

	global $wpdb, $doDebug, $testMode, $audioAssessmentTableName, $alreadyPlayed;



	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$currentSemester	= $initializationArray['currentSemester'];
	$prevSemester		= $initializationArray['prevSemester'];
	$siteURL			= $initializationArray['siteurl'];
	$currentDateTime	= $initializationArray['currentDateTime'];
	
//	CHECK THIS!								//////////////////////
//	if ($validUser == "N") {
//		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
//	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/end-of-semester-student-self-assessment/";
	$inp_semester				= '';
	$jobname					= "End of Semester Student Assessment";
	$studentCallSign			= "";
	$studentEmail				= "";
	$studentPhone				= "";
	$inp_callsign				= "";
	$studentName				= "";
	$advisorCallSign			= "";
	$studentLevel				= "";
	$audioClip1					= "";
	$audioClip2					= "";
	$audioClip1Text				= "";
	$audioClip2Text				= "";
	$audioFileName1				= "";
	$audioFileName2				= "";
	$audioFileNumber1			= "";
	$audioFileNumber2			= "";
	$perc1						= 100;
	$perc2						= 100;
	$inp_mode					= "";
	$inp_comments				= "";
	$inp_verbose				= 'N';
	$runtype					= 'student';
	$advisorCallSign			= '';
	$email						= '';
	$phone						= '';
	$alreadyPlayed				= array();		// clip_name
	$studentSemester			= '';
	$inPastStudent				= TRUE;
	$actionDate					= date('Y-m-d H:i:s');
	$controlCode				= '';

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
			if ($str_key 			== "inp_callsign") {
				$inp_callsign		 = $str_value;
				$inp_callsign		 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "advisorCallSign") {
				$advisorCallSign		 = $str_value;
				$advisorCallSign		 = strtoupper(filter_var($advisorCallSign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "studentCallSign") {
				$studentCallSign		 = $str_value;
				$studentCallSign		 = strtoupper(filter_var($studentCallSign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "studentName") {
				$studentName		 = no_magic_quotes($str_value);
//				$studentName		 = filter_var($studentName,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_comments") {
				$inp_comments		 = $str_value;
				$inp_comments		 = filter_var($inp_comments,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "studentLevel") {
				$studentLevel		 = $str_value;
				$studentLevel		 = filter_var($studentLevel,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "studentSemester") {
				$studentSemester		 = $str_value;
				$studentSemester		 = filter_var($studentSemester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inPastStudent") {
				$inPastStudent		 = $str_value;
				$inPastStudent		 = filter_var($inPastStudent,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "phone") {
				$phone		 = $str_value;
				$phone		 = filter_var($phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "runtype") {
				$runtype		 = $str_value;
				$runtype		 = filter_var($runtype,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "email") {
				$email		 = $str_value;
				$email		 = filter_var($email,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "audioClip2") {
				$audioClip2		 = $str_value;
				$audioClip2		 = filter_var($audioClip2,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "audioClip3") {
				$audioClip3		 = $str_value;
				$audioClip3		 = filter_var($audioClip3,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "audioClip1") {
				$audioClip1		 = $str_value;
				$audioClip1		 = filter_var($audioClip1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "audioFileName1") {
				$audioFileName1		 = $str_value;
				$audioFileName1		 = filter_var($audioFileName1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "audioFileName2") {
				$audioFileName2		 = $str_value;
				$audioFileName2		 = filter_var($audioFileName2,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "audioFileName3") {
				$audioFileName3		 = $str_value;
				$audioFileName3		 = filter_var($audioFileName3,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "audioFileNumber1") {
				$audioFileNumber1		 = $str_value;
				$audioFileNumber1		 = filter_var($audioFileNumber1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "audioFileNumber2") {
				$audioFileNumber2		 = $str_value;
				$audioFileNumber2		 = filter_var($audioFileNumber2,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "audioFileNumber3") {
				$audioFileNumber3		 = $str_value;
				$audioFileNumber3		 = filter_var($audioFileNumber3,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "controlCode") {
				$controlCode		 = $str_value;
				$controlCode		 = filter_var($controlCode,FILTER_UNSAFE_RAW);
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
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment2";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_consolidated_student";
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
	}
	$theSemester				= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$theSemester			= $prevSemester;
	}

	function alreadyPlayed($clipname) {

		/// returns an array
		///		[0]	Yes/No			whether clip has been played. Yes=has been played; No=has not been played
		///		[1] string			if an error, the error reason, otherwise empty

		global $wpdb, $doDebug, $testMode, $audioAssessmentTableName, $alreadyPlayed;
		
		$returnArray				= array();
		$clipPlayed					= "No";
		$reason						= "Not Played";
		
		if ($doDebug) {
			echo "<br />Starting alreadyPlayed function with $clipname<br />";
		}
		
		if ($clipname == '') {		/// missing data. return
			if ($doDebug) {
				echo "input data missing<br />";
			}
			$returnArray			= array('No','Missing Input Data');
		} else {
			$notPlayed				= TRUE;
			foreach($alreadyPlayed as $thisName) {
				if ($doDebug) {
					echo "Checking $thisName against $clipname ";
				}
				if ($thisName == $clipname) {
					$notPlayed		= FALSE;
					if ($doDebug) {
						echo "already played<br />";
					}
				} else {
					if ($doDebug) {
						echo "not played<br />";
					}
				}
			}				/// have checked all entries in alreadyPlayed
			
			if ($notPlayed === FALSE) {			// has been played
				return array('Yes','Already Played');
			} else {
				// add clip to the alreadyPlayed array
				$alreadyPlayed[]		= $clipname;
				return array('No','Not Played');
			}
		}
	}


	function stripEqual($myStr) {
		$myInt = strpos($myStr,"=");
		if ($myInt > 0) {
			$newStr = substr($myStr,0,strlen($myStr)-2);
		} else {
			$newStr = $myStr;
		}
		return $newStr;
	}


	if ("1" == $strPass) {
	
		if ($userName == '') {
			$userName = 'Pass1';
		}
		return;
		$content		.= "<h3>$jobname Demonstration</h3>
							<p>Please enter your call sign, your email address, your phone number, and the call sign 
							of a student in your class.</p>
							<p>The program will verify that you are an advisor and that the student is in your class. 
							It will then do the self assessment as though you are the student, however the results will 
							not be recorded.</p>
							<form method='post' action='$theURL' 
							name='advisor_selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<input type='hidden' name='runtype' value='advisor'>
							<table style='border-collapse:collapse;'>
							<tr><td>Advisor Call Sign</td>
								<td><input type='text' name='advisorCallSign' class='formInputText' size='15' maxlength='20'></td></tr>
							<tr><td>Advisor Email</td>
								<td><input type='text' name='email' class='formInputText' size='20' maxlength='50'></td></tr>
							<tr><td>Advisor Phone</td>
								<td><input type='text' name='phone' class='formInputText' size='20' maxlength='50'></td></tr>
							<tr><td>Student Call Sign</td>
								<td><input type='text' name='studentCallSign' class='formInputText' size='15' maxlength='20'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";


///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 2 with the following information:<br />
				inp_callsign: $inp_callsign<br />
				studentCallSign: $studentCallSign<br />
				studentEmail: $studentEmail<br />
				studentPhone: $studentPhone<br />
				advisorCallSign: $advisorCallSign<br />
				phone: $phone<br />
				email: $email<br />";
		}
		//// see if job being run by someone in validTestMode
		$testModeUser				= FALSE;
		if (in_array($userName,$validTestmode)) {			
			$testModeUser			= TRUE;
		}
		if ($userName == '') {
 			if ($inp_callsign != '') {
 				$userName = $inp_callsign;
 			}
 			if ($studentCallSign != '') {
 				$userName	= $studentCallSign;
 			}
		}
		$doProceed					= TRUE;
		if ($runtype == 'advisor') {
			$content				.= "<h3>$jobname Demonstration</h3>";
			//// if being run by an advisor, then get the advisor record from advisor 
			//// validate the advisor. If valid, proceed
		
			$advisorResult				= $wpdb->get_results("select call_sign, 
															  	phone, 
															  	email 
															  from $advisorTableName 
															  where semester='$theSemester' 
															  and call_sign='$advisorCallSign'");
			if ($advisorResult === FALSE) {
				$myError			= $wpdb->last_error;
				$myQuery			= $wpdb->last_query;
				if ($doDebug) {
					echo "Reading $advisorTableName table failed<br />
						  wpdb->last_query: $myQuery<br />
						  wpdb->last_error: $myError<br />";
				}
				$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
				sendErrorEmail($errorMsg);
				$content					.= "You're not authorized";
				$doProceed					= FALSE;
			} else {
				$numARows					= $wpdb->num_rows;
				if ($doDebug) {
					$myStr					= $wpdb->last_query;
					echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					foreach ($advisorResult as $advisorResultRow) {
						$advisor_call_sign	= $advisorResultRow->call_sign;
						$advisor_email		= $advisorResultRow->email;
						$advisor_phone		= trim($advisorResultRow->phone);
						if ($doDebug) {
							echo "Testing $email against $advisor_email<br />
								  Testing $phone against $advisor_phone<br />";
						}
						if ($email == '') {
							$content	.= "Please enter a valid email address<br />";
							$doProceed	= FALSE;
						} elseif (filter_var($email,FILTER_VALIDATE_EMAIL) === FALSE) {
							$content	.= "Please enter a valid email address<br />";
							$doProceed	= FALSE;
						} elseif ($email != $advisor_email) {
							$doProceed	= FALSE;
							$content	.= "You're not authorized";
						}
						if ($phone == '') {
							$content	.= "Please enter a valid phone number<br />";
							$doProceed	= FALSE;
						} else {
							$last4Digits			= substr($phone,-4,4);
							$advisor_last4Digits	= substr($advisor_phone,-4,4);
							if ($doDebug) {
								echo "testing 4 digits $last4Digits against $advisor_last4Digits<br />";
							}
							if ($last4Digits != $advisor_last4Digits) {
								$doProceed			= FALSE;
								$content			.= "You're not authorized";
							}
						}
					}
				} else {
					$content		.= "You're not authorized";
					$doProceed		= FALSE;
				}
			}
		} else {
			$content				.= "<h3>$jobname</h3>";
		}
		if ($doProceed) {
			if ($doDebug) {
				echo "got a valid set of data. Proceeding<br />";
			}

			$sql					= "select * from $studentTableName 
										where call_sign='$studentCallSign' 
										and semester='$theSemester'";
			$wpw1_cwa_student			= $wpdb->get_results($sql);
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
					if ($doDebug) {
						echo "found $numSRows rows in $studentTableName<br />";
					}
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_first_name						= $studentRow->first_name;
						$student_last_name						= stripslashes($studentRow->last_name);
						$student_email  						= $studentRow->email;
						$student_level  						= $studentRow->level;
						$student_semester						= $studentRow->semester;
						$student_response  						= strtoupper($studentRow->response);
						$student_student_status  				= strtoupper($studentRow->student_status);
						$student_action_log  					= $studentRow->action_log;
						$student_assigned_advisor  				= $studentRow->assigned_advisor;
						$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
						$student_promotable  					= $studentRow->promotable;
						$student_intervention_required  		= $studentRow->intervention_required;

						$student_last_name 						= no_magic_quotes($student_last_name);
					}
					if ($runtype == 'advisor' && $testModeUser == FALSE) {
						if ($student_assigned_advisor != $advisorCallSign) {
							$content		.= "You're not authorized";
							$doProceed		= FALSE;
						}
					}
					if ($doProceed) {
						//// validate the record

						if (!$runtype == 'student') {
							if ($studentEmail != $student_email) {
								$doProceed			= FALSE;
							}
							/// testing the phone number last 4 digits
							$testPhone		= substr($student_phone,-4,4);
							$last4Digits	= substr($studentPhone,-4,4);
							if ($testPhone != $last4Digits) {
								$content	.= "Supplied phone number doesn't match<br />";
								$doProceed	= FALSE;
							}	
						}
						if (!$doProceed) {
							$content		.= "Information supplied for $studentCallSign is invalid";
						} else {
							if ($doDebug) {
								echo "have the student record and all is valid<br />";
							}
				
							// get all past assessment records
				
							$assessmentResult		= $wpdb->get_results("select * from $audioAssessmentTableName 
																		  where call_sign='$student_call_sign' 
																		  order by assessment_date DESC");
							if ($assessmentResult === FALSE) {
								if ($doDebug) {
									echo "Reading $audioAssessmentTableName table failed<br />";
									echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
									echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
								}
							} else {
								$numASRows				= $wpdb->num_rows;
								if ($doDebug) {
									$myStr				= $wpdb->last_query;
									echo "ran $myStr<br />and found $numASRows rows in $audioAssessmentTableName table<br />";
								}
								if ($numASRows > 0) {
									foreach ($assessmentResult as $audioAssessmentRow) {
										$assessment_ID			= $audioAssessmentRow->record_id;
										$assessment_call_sign	= $audioAssessmentRow->call_sign;
										$assessment_date		= $audioAssessmentRow->assessment_date;
										$assessment_level		= $audioAssessmentRow->assessment_level;
										$assessment_clip		= $audioAssessmentRow->assessment_clip;
										$assessment_score		= $audioAssessmentRow->assessment_score;
										$assessment_clip_name	= $audioAssessmentRow->assessment_clip_name;
										$assessment_notes		= $audioAssessmentRow->assessment_notes;
										$assessment_program		= $audioAssessmentRow->assessment_program;
				
										if ($assessment_level == $student_level) {
											$thirtyDays			= strtotime("$currentDateTime - 30 days");
											$assessmentTime		= strtotime("$assessment_date");
											if ($assessmentTime >= $thirtyDays) {
												$alreadyPlayed[]	= "$assessment_clip_name";
											}
										}
									}
								}
							}
							if ($doDebug) {
								echo "alreadyPlayed array:<br /><pre>";
								print_r($alreadyPlayed);
								echo "</pre><br />";
							}
				
							// if 15 clips or more have been played, let the student know
							$myInt			= count($alreadyPlayed);
							if ($myInt > 14) {
								$content	.= "<p>You have already listened to $myInt $student_level audio clips. Unfortunately, 
												you have exhausted the library of available clips. Unable to complete the assessment.</p>";
								$errorMsg		= "End of Semester Student Assessment: $student_call_sign has already played $myInt clips<br />";
								$errorResult	= sendErrorEmail($errorMsg);
							} else {

								////// have the student record. All is valid. Proceed to display the info
								if ($student_level == 'Beginner') {
									$farnsworth	= "6";
									$character	= "25";
									$audioClip	= 'BegBas';
									$sample		= "$siteURL/wp-content/uploads/BegBasSample.mp3";
								} elseif ($student_level == 'Fundamental') {
									$farnsworth	= "10";
									$character	= "25";
									$audioClip	= 'BasInt';
									$sample		= "$siteURL/wp-content/uploads/BasIntSample.mp3";
								} elseif ($student_level == 'Intermediate') {
									$farnsworth = "20";
									$character	= "25";
									$audioClip	= 'IntAdv';
									$sample		= "$siteURL/wp-content/uploads/IntAdvSample.mp3";
								} else {
									$farnsworth = "25";
									$character	= "25";
									$audioClip	= 'Adv';
									$sample		= "$siteURL/wp-content/uploads/AdvSample.mp3";
								}
								if ($doDebug) {
									echo "have the sample clip set up: $sample<br />";
								}
								$controlCode	= mt_rand();
								$dContent		= "<p>For the assessment you will listen to two audio segments of Morse code and enter what you heard. 
													There is also an optional third audio clip. 
													The program will then calculate the score and report that to you and to your advisor. This 
													assessment is only one factor that the advisor considers for your final assessment.</p>
													<p>First, however, below is a sample audio segment that you can play to see what the actual 
													assessment files will sound like. You can play this segment as often as you want. When you are ready, 
													continue with the assessment. Note: there is no pause before the sample clip starts.</p>
													<p>Sample Morse Code Audio Clip:<br />
													<audio controls src=\"$sample\"></audio></p>
													<p>Following are two audio clips and an optional third audio clip. You are completing 
													the $student_level level. The audio clips are being played with a character speed of $character words per 
													minute with Farnsworth spacing giving an effective speed of $farnsworth words per minute. You can play 
													each clip only once. There will be about a three-second 
													pause before the Morse code starts playing.</p>
													<div style='margin-left:20px;'>
													<ul>
													<li>Click on the 'play' button</li>
													<li>Enter what you heard</li>
													</ul></div>
													<p>The optional third audio clip is available if you wish. The program will score the best two out of 
													three clips and present that information to you and to your advisor.</p>
													<p>If you have any issues, questions, or comments about this assessment, please enter those in the text 
													box below the second audio clip. When you have listened to 
													each clip and entered what you heard, submit your assessment. Your results will then be displayed and 
													sent to your advisor.</p>
													<form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='3'>
													<input type='hidden' name='inp_mode' value='$inp_mode'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='studentCallSign' value='$student_call_sign'>
													<input type='hidden' name='studentName' value=\"$student_last_name, $student_first_name\">
													<input type='hidden' name='advisorCallSign' value='$student_assigned_advisor'>
													<input type='hidden' name='studentLevel' value='$student_level'>
													<input type='hidden' name='studentSemester' value='$student_semester'>
													<input type='hidden' hame='inPastStudent' value='$inPastStudent'>
													<input type='hidden' name='runtype' value='$runtype'>
													<input type='hidden' name='controlCode' value='$controlCode'>
													<table style='border-collapse:collapse;'>";

								//// get an unplayed audio clip
								$gotAClip1					= FALSE;
								$gotAnError					= FALSE;
								$doProceedAudio				= FALSE;
								while ($gotAClip1 == FALSE) {
									$result					= playAudioFile($audioClip,"Random",1,$doDebug);
									if ($result[0] === FALSE) {
										if ($doDebug) {
											echo "<b>ERROR</b> $result[1]; File: $result[2]";
										}
										$errorMsg			= "Error in End of Semester Student Assessment. playAudioFile returned $result[1]; File: $result[2]";
										$thisResult			= sendErrorEmail($errorMsg);
									} else {
										$audioContent1		= $result[0];
										$audioFileName1		= $result[1];
										$audioFileNumber1	= $result[2];
										$audioFileText1		= $result[3];
										if ($doDebug) {
											echo "Got some content:<br /><pre>";
											print_r($result);
											echo "</pre><br />Checking $audioFileName1 if already played<br />";
										}
										$thisResult				= alreadyPlayed($audioFileName1);
										if ($doDebug) {
											echo "alreadyPlayed returned:<br /><pre>";
											print_r($thisResult);
											echo "</pre><br />";
										}
										if ($thisResult[0] == "No" && $thisResult[1] == 'Not Played') {				/// clip not played and no errors
											$gotAClip1			= TRUE;
										}
										if ($thisResult[0] == "No" && $thisResult[1] != 'Not Played') {				/// not played and have an error
											$gotAClip1			= FALSE;
											$gotAnError			= TRUE;
										}
										if ($doDebug) {
											echo "Finished clip 1 gotAClip1 is $gotAClip1 and gotAnError is $gotAnError<br />";
										}
									}
								}
								if (!$gotAClip1) {				/// no clip available
									if ($doDebug) {
										echo "no clip available to be played in slot 1<br />";
									}
								} else {
									/// have a clip. Put it in the form
									$dContent				.= "<tr><td>Audio Clip 1 (<em>Click 'play' and enter what you hear</em>):<br />
																	<div><p>$audioContent1</p></div></td></tr>
																<tr><td>Enter what you heard in the box below:<br />
																	<input type='text' id='audioClip1' name='audioClip1' class='formInputText' size='50' maxlength='50'></td></tr>";
								}
								///// do the same for clip 2
								//// get an unplayed audio clip
								$gotAClip2					= FALSE;
								$gotAnError					= FALSE;
								$doProceedAudio				= FALSE;
								while ($gotAClip2 == FALSE) {
									$result					= playAudioFile($audioClip,"Random",2,$doDebug);
									if ($result[0] === FALSE) {
										if ($doDebug) {
											echo "<b>ERROR</b> $result[1]; File: $result[2]";
										}
										$errorMsg			= "Error in End of Semester Student Assessment. playAudioFile returned $result[1]; File: $result[2]";
										$thisResult			= sendErrorEmail($errorMsg);
									} else {
										$audioContent2		= $result[0];
										$audioFileName2		= $result[1];
										$audioFileNumber2	= $result[2];
										$audioFileText2		= $result[3];
										if ($doDebug) {
											echo "Got some content:<br /><pre>";
											print_r($result);
											echo "</pre><br />Checking $audioFileName2 if already played<br />";
										}
										$thisResult				= alreadyPlayed($audioFileName2);
										if ($doDebug) {
											echo "alreadyPlayed returned:<br /><pre>";
											print_r($thisResult);
											echo "</pre><br />";
										}
										if ($thisResult[0] == "No" && $thisResult[1] == 'Not Played') {				/// clip not played and no errors
											$gotAClip2			= TRUE;
										}
										if ($thisResult[0] == "No" && $thisResult[1] != 'Not Played') {				/// not played and have an error
											$gotAClip2			= FALSE;
											$gotAnError			= TRUE;
										}
										if ($doDebug) {
											echo "Finished clip 2  gotAClip2 is $gotAClip2 and gotAnError is $gotAnError<br />";
										}
									}
								}
								if (!$gotAClip2) {				/// no clip available
									if ($doDebug) {
										echo "no clip availabe to be played in slot 2<br />";
									}
								} else {
									/// have a clip. Put it in the form
									$dContent				.= "<tr><td>Audio Clip 2 (<em>Click 'play' and enter what you hear</em>):<br />
																	<div><p>$audioContent2</p></div></td></tr>
																<tr><td>Enter what you heard in the box below:<br />
																	<input type='text' id='audioClip2' name='audioClip2' class='formInputText' size='50' maxlength='50'></td></tr>";
								}
			
								/// get the optional 3rd clip
								//// get an unplayed audio clip
								$gotAClip3					= FALSE;
								$gotAnError					= FALSE;
								$doProceedAudio				= FALSE;
								while ($gotAClip3 == FALSE) {
									$result					= playAudioFile($audioClip,"Random",3,$doDebug);
									if ($result[0] === FALSE) {
										if ($doDebug) {
											echo "<b>ERROR</b> $result[1]; File: $result[2]";
										}
										$errorMsg			= "Error in End of Semester Student Assessment. playAudioFile returned $result[1]; File: $result[2]";
										$thisResult			= sendErrorEmail($errorMsg);
									} else {
										$audioContent3		= $result[0];
										$audioFileName3		= $result[1];
										$audioFileNumber3	= $result[2];
										$audioFileText3		= $result[3];
										if ($doDebug) {
											echo "Got some content:<br /><pre>";
											print_r($result);
											echo "</pre><br />Checking $audioFileName3 if already played<br />";
										}
										$thisResult				= alreadyPlayed($audioFileName3);
										if ($doDebug) {
											echo "alreadyPlayed returned:<br /><pre>";
											print_r($thisResult);
											echo "</pre><br />";
										}
										if ($thisResult[0] == "No" && $thisResult[1] == 'Not Played') {				/// clip not played and no errors
											$gotAClip3			= TRUE;
										}
										if ($thisResult[0] == "No" && $thisResult[1] != 'Not Played') {				/// not played and have an error
											$gotAClip3			= TRUE;
											$gotAnError			= TRUE;
										}
										if ($doDebug) {
											echo "Finished clip 3 gotAClip3 is $gotAClip3 and gotAnError is $gotAnError<br />";
										}
									}
								}
								if (!$gotAClip3) {				/// no clip available
									if ($doDebug) {
										echo "no clip availabe to be played<br />";
									}
								} else {
									/// have a clip. Put it in the form
									$dContent				.= "<tr><td>Optional Audio Clip 3 (<em>Click 'play' and enter what you hear</em>):<br />
																	<div><p>$audioContent3</p></div></td></tr>
																<tr><td>Enter what you heard in the box below:<br />
																	<input type='text' id='audioClip3' name='audioClip3' class='formInputText' size='50' maxlength='50'></td></tr>";
								}
			
								// if all three slots filled, proceed. Otherwise send error msg and bail out
								if ($gotAClip1 && $gotAClip2 && $gotAClip3) {
									$content		.= $dContent;
									$stringToPass	= "audioClip1Text=$audioFileText1&audioClip2Text=$audioFileText2&audioClip3Text=$audioFileText3";
									$enstr			= base64_encode($stringToPass);
									$content		.= "<input type='hidden' name='enstr' value='$enstr'>
														<input type='hidden' name='audioFileName1' value='$audioFileName1'>
														<input type='hidden' name='audioFileName2' value='$audioFileName2'>
														<input type='hidden' name='audioFileName3' value='$audioFileName3'>
														<input type='hidden' name='audioFileNumber1' value='$audioFileNumber1'>
														<input type='hidden' name='audioFileNumber2' value='$audioFileNumber2'>
														<input type='hidden' name='audioFileNumber3' value='$audioFileNumber3'>
														<tr><td>Issues, Questions, or Comments:<br />
														<textarea class='formInputText' name='inp_comments' cols='50' rows='5'></textarea></td></tr>
														<tr><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
														</form></p>";
								} else {
									$errorMsg		= "End of Semester Student Assessment: unable to fill all three audio slots";
									$errorResult	= sendErrorMsg($errorMsg);
									$content		.= "<p>Fatal Program Error. Support has been notified. You may close this window.<br />";
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "gotARecord is FALSE. No record found<br />";
						}
						$content			.= "<p>No record found for $studentCallSign</p>";
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "doProceed is somehow false<br />";
			}
		}

		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "At pass three with the following data:<br />
				studentCallSign: $studentCallSign<br />
				studentName: $studentName<br />
				studentLevel: $studentLevel<br />
				advisorCallSign: $advisorCallSign<br />
				audioClip1: $audioClip1<br />
				audioClip1Text: $audioClip1Text<br />
				audioFileName1: $audioFileName1<br />
				audioFileNumber1: $audioFileNumber1<br />
				audioClip2: $audioClip2<br />
				audioClip2Text: $audioClip2Text<br />
				audioFileName2: $audioFileName2<br />
				audioFileNumber2: $audioFileNumber2<br />
				audioClip3: $audioClip3<br />
				audioClip3Text: $audioClip3Text<br />
				audioFileName3: $audioFileName3<br />
				audioFileNumber3: $audioFileNumber3<br />";
		}
		if ($userName == '') {
 			if ($inp_callsign != '') {
 				$userName = $inp_callsign;
 			}
 			if ($studentCallSign != '') {
 				$userName	= $studentCallSign;
 			}
		}
		
		// get the student data and see if this has already been done
		$alreadyDone			= FALSE;
		$wpw1_cwa_student		= $wpdb->get_results("select student_id,
															 first_name,
															 last_name,
															 call_sign,
															 semester,
															 copy_control,
															 action_log 
													  from $studentTableName 
													  where call_sign='$studentCallSign' 
													  and semester='$theSemester'");
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
						$student_semester						= $studentRow->semester;
						$student_copy_control					= $studentRow->copy_control;
						$student_action_log  					= $studentRow->action_log;

						$student_last_name 						= no_magic_quotes($student_last_name);

						if ($controlCode == $student_copy_control) {
							$alreadyDOne	= TRUE;			/// have already displayed this
						}				
		
		
		
						//// prepare the audio clips for processing
						$audioClip1			= strtolower($audioClip1);
						$audioClip1Text		= strtolower($audioClip1Text);
						$audioClip1Text		= trim($audioClip1Text);
	//					$audioClip1			= stripEqual($audioClip1);
	//					$audioClip1Text		= stripEqual($audioClip1Text);
		
						$audioClip2			= strtolower($audioClip2);
						$audioClip2Text		= strtolower($audioClip2Text);
						$audioClip2Text		= trim($audioClip2Text);
	//					$audioClip2			= stripEqual($audioClip2);
	//					$audioClip2Text		= stripEqual($audioClip2Text);

						if ($audioClip3 != '') {
							$audioClip3			= strtolower($audioClip3);
							$audioClip3Text		= strtolower($audioClip3Text);
							$audioClip3Text		= trim($audioClip3Text);
		//					$audioClip3			= stripEqual($audioClip3);
		//					$audioClip3Text		= stripEqual($audioClip3Text);
						}
	
						$content			.= "<h3>End of Semester $studentCallSign Evaluation</h3>";
						$sim1				= similar_text($audioClip1,$audioClip1Text,$percst1);
						$sim2				= similar_text($audioClip2,$audioClip2Text,$percst2);
						if ($doDebug) {
							echo "Similar Text with spaces for clip 1: sim1: $sim1 percst1: $percst1<br />
								  Similar Text with spaces for clip 2: sim2 :$sim2 percst2: $percst2<br />";
						}
						if ($audioClip3Text != '') {
							$sim3				= similar_text($audioClip3,$audioClip3Text,$percst3);
							if ($doDebug) {
								echo "Similar Text with spaces for clip 3: sim3: $sim3 percst3: $percst3<br />";
							}
						}
						/// do the same without spaces
						$audioClip1NS		= str_replace(" ","",$audioClip1);
						$audioClip1TextNS	= str_replace(" ","",$audioClip1Text);
						$sim1NS				= similar_text($audioClip1NS,$audioClip1TextNS,$percst1NS);
						$audioClip2NS		= str_replace(" ","",$audioClip2);
						$audioClip2TextNS	= str_replace(" ","",$audioClip2Text);
						$sim2NS				= similar_text($audioClip2NS,$audioClip2TextNS,$percst2NS);
						if ($doDebug) {
							echo "<br />Similar Text NO spaces for clip 1: sim1NS: $sim1NS percst1NS: $percst1NS<br />
								  Similar Text NO spaces for clip 2: sim2NS: $sim2NS percst2NS: $percst2NS<br />";
						}
						if ($audioClip3 != '') {
							$audioClip3NS		= str_replace(" ","",$audioClip3);
							$audioClip3TextNS	= str_replace(" ","",$audioClip3Text);
							$sim3NS				= similar_text($audioClip3NS,$audioClip3TextNS,$percst3NS);
							if ($doDebug) {
								echo "Similar Text NO spaces for clip 3: sim3NS: $sim3NS percst3NS: $percst3NS<br />";
							}
						}
		
						/// if the 3rd file was used, average the two best 
						if ($audioClip3 != '') {
							$myArray 		= array($percst1NS,$percst2NS,$percst3NS);
							rsort($myArray);
							$myInt1			= $myArray[0];
							$myInt2			= $myArray[1];
							if ($doDebug) {
								echo "<br />Got the two highest: $myInt1 and $myInt2<br /><pre>";
								print_r($myArray);
								echo "</pre><br />";
							}
						} else {
							$myInt1			= $percst1NS;
							$myInt2			= $percst2NS;
						}
		
						if ($doDebug) {
							echo "Taking the average of $myInt1 and $myInt2<br />";
						}
						$stAvg				= round(($myInt1 + $myInt2) / 2,0);
						$percst1NS			= round($percst1NS,0);
						$percst2NS			= round($percst2NS,0);
						if ($audioClip3 != '') {
							$percst3NS		= round($percst3NS,0);
							$myClips		= "three";	
						} else {
							$myClips		= "two";
							$percst3NS		= "n/a";
							$audioFileName3	= "n/a";
							$percst3		= "n/a";
							$percst3NS		= "n/a";
						}
		
						$myStr				= "<h3>$jobname";
						if ($runtype == 'advisor') {
							$myStr			.= " Demonstration</h3>";
						} else {
							$myStr			.= "</h3>";
						}
						$content			.= "$myStr
												<p>You listened to $myClips audio clips.<br />
												The first clip: $audioClip1Text<br />
												The accuracy score for the first clip was $percst1NS%.<br /><br />
												The second clip: $audioClip2Text<br />
												The accuracy score for the second clip was $percst2NS%.";
						if ($audioClip3 != '') {
							$content		.= "<br /><br />The third clip: $audioClip3Text<br />
												The accuracy score for the third clip was $percst3NS%.";
						}
						$content			.= "</p> 
												<p>Your average score for the best two was $stAvg%.</p>
												<p>Your comments (if any) were:<br />
												$inp_comments</p>
												<p>This information has been sent to your advisor. The assessment you just completed is only one 
												factor that the advisor considers when evaluating you promotability.</p>
												<p>Thank you for completing the class and being a part of the CW Academy family!<p>
												<p>You may close this window.</p>";
		
						if (!$alreadyDone) {
							// only write to the assement file if run by a student
							if ($runtype == 'student') {
		
								$assessment_notes	= "CW Sent: $audioClip1Text Copied: $audioClip1. 
Similar Text data: percst1NS: $percst1NS. 
Student comments: $inp_comments";
								// Now write the info to the assessment table
								$result				= $wpdb->insert($audioAssessmentTableName,
																	array('call_sign'=>$studentCallSign,
																		  'assessment_level'=>$studentLevel,
																		  'assessment_score'=>$percst1NS,
																		  'assessment_clip'=>$audioFileNumber1,
																		  'assessment_clip_name'=>$audioFileName1,
																		  'assessment_notes'=>$assessment_notes,
																		  'assessment_program'=>'e-o-s eval'),
																	array('%s','%s','%d','%s','%s'));
								if ($result === FALSE) {
									if ($doDebug) {
										echo "Inserting the audio assessment 1 failed<br />
											   wpdb->last_query: " . $wpdb->last_query . "<br />
											   <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
									}
								} else {
									if ($doDebug) {
										echo "Inserting clip 1 info succeeded<br />";
									}
								}
								$assessment_notes	= "CW Sent: $audioClip2Text Copied: $audioClip2. 
Similar Text data: percst2NS: $percst2NS. 
Student comments: $inp_comments";
								$result				= $wpdb->insert($audioAssessmentTableName,
																	array('call_sign'=>$studentCallSign,
																		  'assessment_level'=>$studentLevel,
																		  'assessment_score'=>$percst2NS,
																		  'assessment_clip'=>$audioFileNumber2,
																		  'assessment_clip_name'=>$audioFileName2,
																		  'assessment_notes'=>$assessment_notes,
																		  'assessment_program'=>'e-o-s eval'),
																	array('%s','%s','%d','%s','%s'));
								if ($result === FALSE) {
									if ($doDebug) {
										echo "Inserting the audio assessment 2 failed<br />
											  wpdb->last_query: " . $wpdb->last_query . "<br />
											  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
									}
								} else {
									if ($doDebug) {
										echo "Inserting clip 2 info succeeded<br />";
									}
								}
								if ($audioClip3 != '') {
									$assessment_notes	= "CW Sent: $audioClip3Text Copied: $audioClip3. 
Similar Text data: percst3NS: $percst3NS. 
Student comments: $inp_comments";
									$result				= $wpdb->insert($audioAssessmentTableName,
																		array('call_sign'=>$studentCallSign,
																			  'assessment_level'=>$studentLevel,
																			  'assessment_score'=>$percst3NS,
																			  'assessment_clip'=>$audioFileNumber3,
																			  'assessment_clip_name'=>$audioFileName3,
																			  'assessment_notes'=>$assessment_notes,
																			  'assessment_program'=>'e-o-s eval'),
																		array('%s','%s','%d','%s','%s'));
									if ($result === FALSE) {
										if ($doDebug) {
											echo "Inserting the audio assessment 3 failed<br />
												  wpdb->last_query: " . $wpdb->last_query . "<br />
												  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
										}
									} else {
										if ($doDebug) {
											echo "Inserting clip 3 info succeeded<br />";
										}
									}
								}
								// update student record to say they took the assessment
								$student_action_log			= "$student_action_log / E-O-S Assessment $studentCallSign $actionDate 
Student self-assessment results: $stAvg%";
								$studentUpdateData		= array('tableName'=>$studentTableName,
																'inp_method'=>'update',
																'inp_data'=>array('action_log'=>$student_action_log,
																				  'copy_control'=>$controlCode),
																'inp_format'=>array('%s','%s'),
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
								}
							}

/*		
							//// send the assessment info to Roland
							$myStr					= "Student Run";
							if ($runtype == 'advisor') {
								$myStr				= "Demonstration Run";
							}
							$emailContent			= "
$myStr<br />
studentCallSign: $studentCallSign<br />
studentName: $studentName<br />
studentLevel: $studentLevel<br />
advisorCallSign: $advisorCallSign<br />
audioClip1Text: $audioClip1Text<br />
Student Heard 1: $audioClip1<br />
Similar Text data: percst1: $percst1%; percst1NS: $percst1NS%<br />
audioFileName1: $audioFileName1<br />
audioClip2Text: $audioClip2Text<br />
Student Heard 2: $audioClip2<br />
audioFileName2: $audioFileName2<br />
Similar Text data: percst2: $percst2%; percst2NS: $percst2NS%<br />
audioClip3Text: $audioClip3Text<br />
Student Heard 3: $audioClip3<br />
audioFileName3: $audioFileName3<br />
Similar Text data: percst3: $percst3%; percst3NS: $percst3NS%<br />
Student comments: $inp_comments";
							$mailResult		= emailFromCWA_v2(array('theRecipient'=>'rolandksmith@gmail.com',
																	'theSubject'=>'CW Academy -- Morse Code Assessment Data',
																	'theContent'=>$emailContent,
																	'theCc'=>'',
																	'mailCode'=>16,
																	'jobname'=>$jobname,
																	'increment'=>0,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug));
							if ($mailResult === FALSE) {
								if ($doDebug) {
									echo "Sending email to $theRecipient failed<br />";
								}
							}
		
*/	
							//// send the assessment info to the advisor
							//// get the advisor info from advisor
							$gotAdvisor		= FALSE;
								$sql			= "select * from $advisorTableName 
												   where call_sign='$advisorCallSign' 
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
								} else {
									$numARows			= $wpdb->num_rows;
									if ($doDebug) {
										echo "found $numARows rows in $advisorTableName table<br />";
									}
									if ($numARows > 0) {			// no record found in advisor
										foreach ($wpw1_cwa_advisor as $advisorRow) {
											$advisor_ID							= $advisorRow->advisor_id;
											$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
											$advisor_first_name 				= $advisorRow->first_name;
											$advisor_last_name 					= stripslashes($advisorRow->last_name);
											$advisor_email 						= strtolower($advisorRow->email);

											$advisor_last_name 					= no_magic_quotes($advisor_last_name);
										}
										if ($inp_comments != '') {
											$myStr							= "<p>Student entered the following 
comment:<br />$inp_comments</p>";
										} else {
											$myStr							= "";
										}
										$advisorTypeRun						= '';
										if ($runtype == 'advisor') {
											$advisorTypeRun					= "<p>This was a demonstration run by you</p>";
										}
										$emailContent						= "
To: $advisor_last_name, $advisor_first_name ($advisor_call_sign):
$advisorTypeRun
<p>Student $studentName ($studentCallSign) in your $studentLevel Level class has completed 
the end-of-semester Morse code assessment. The student listened to $myClips audio clips.</p>
<p>The first clip was: $audioClip1Text<br />
The student heard: $audioClip1<br />
The accuracy score for the first clip was $percst1NS%.</p>
<p>The second clip was: $audioClip2Text<br />
The student heard: $audioClip2<br />
The accuracy score for the second clip was $percst2NS%.</p>";
										if ($audioClip3 != '') {
											$emailContent					.= "
<p>The optional third clip was: $audioClip3Text<br />
The student heard: $audioClip3<br />
The accuracy score for the third clip was $percst3NS%.</p>";
										}
										$emailContent							.= "
<p>The average score for the best two clips was $stAvg%.</p>
$myStr
<p>The assessment score can be one of the factors you consider as you evaluate the 
promotability of $studentCallSign.</p>
<p>Thank you for your sevice as an advisor!<br />
CW Academy</p>";
										$emailSubject						= "CW Academy Student End of Semester Morse Code Evaluation";
										if ($testMode) {
											$emailSubject					= "TESTMODE $emailSubject";
											$mailCode						= 2;
											$emailRecipient					= "rolandksmith@gmail.com";
										} else {
											$mailCode						= 15;
											$emailRecipient					= $advisor_email;
										}
										$mailResult		= emailFromCWA_v2(array('theRecipient'=>$emailRecipient,
																				'theSubject'=>$emailSubject,
																				'theContent'=>$emailContent,
																				'theCc'=>'',
																				'mailCode'=>$mailCode,
																				'jobname'=>$jobname,
																				'increment'=>0,
																				'testMode'=>$testMode,
																				'doDebug'=>$doDebug));
										if ($mailResult === FALSE) {
											if ($doDebug) {
												echo "Sending email to $theRecipient failed<br />";
											}
										}
								}			
							}		
						}
					}		
				} else {
					if ($doDebug) {
						echo "No rows found in $tableName for $studentCallSign";
					}
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
add_shortcode ('end_of_semester_student_assessment', 'end_of_semester_student_assessment_func');
