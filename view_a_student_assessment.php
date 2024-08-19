function view_a_student_assessment_func() {

//	modified 2Oct2022 by Roland for the new timezone table format
//	modified 16Jul23 by Roland to use consolidated tables

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$proximateSemester 	= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$proximateSemester	= $nextSemester;
	}
	

	
//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
	$scoreConversion			= array('50'=>'0-49%',
										'75'=>'50-89%',
										'90'=>'90+%');

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-view-a-student-assessment/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$inp_callsign				= '';
	$advisor_call_sign			= '';
	$advisorCheck				= FALSE;

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
				$myArray				= explode("&",$stringToPass);
				foreach($myArray as $myValue) {
					$thisArray			= explode("=",$myValue);
					${$thisArray[0]}	= $thisArray[1];
					if ($doDebug) {
						echo "Key: $thisArray[0] | Value: $thisArray[1]<br />";
					}
				}
				$advisorCheck			= TRUE;
				if ($doDebug) {
					echo "strPass: $strPass<br />";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign		 = $str_value;
				$inp_callsign		 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
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
		}
	}
	if (!$advisorCheck) {	
		if ($validUser == "N") {
			return "YOU'RE NOT AUTHORIZED!<br />Goodby";
		}
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
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment2";
		$studentTableName			= "wpw1_cwa_consolidated_student2";
	} else {
		$extMode					= 'pd';
		$audioAssessmentTableName	= "wpw1_cwa_audio_assessment";
		$studentTableName			= "wpw1_cwa_consolidated_student";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		if ($validUser == "N") {
			return "YOU'RE NOT AUTHORIZED!<br />Goodby";
		} else {
			$content 		.= "<h3>Click Submit to Start the Process</h3>
<p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td>Enter Student's Call Sign</td>
	<td><input type='text' class='formInputText' size='25' maxlenth='25' name='inp_callsign'></td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";
		}
		
		
///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 2 with inp_callsign: $inp_callsign<br />";
		}
		$advisorOK				= TRUE;
		if (!$advisorOK) {						// check in past_student
		
			$sql				= "select assigned_advisor from $sudentTableName 
								   where call_sign='$inp_callsign' 
								   and semester='$proximateSemester'";
			$wpw1_cwa_student	= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				if ($doDebug) {
					echo "Reading $studentTableName table<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
				$advisorOK		= FALSE;
			} else {
				$numSRows		= $wpdb->num_rows;
				if ($doDebug) {
					$myStr		= $wpdb->last_query;
					echo "ran $myStr<br />and retrieved $numSRows rows from $studentTableName table<br />";
				}
				if ($numSRows > 0) {
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_assigned_advisor  	= $studentRow->assigned_advisor;
						if ($student_assigned_advisor == $advisor_call_sign) {
							$advisorOK				= TRUE;
							if ($doDebug) {
								echo "student_assigned_advisor of $student_assigned_advisor does not match $advisor_call_sign<br/>";
							}
						}
					}
				} else {
					$advisorOK 						= FALSE;
				}
			}			
		}
		if ($advisorOK) {
			if ($doDebug) {
				echo "Advisor check OK<br />";
			}
			$content					.= "<h3>Self Assessment Information for $inp_callsign</h3>";
			$sql						= "select * from $audioAssessmentTableName 
											where call_sign='$inp_callsign' 
											order by assessment_date";
			$wpw1_cwa_audio_assessment	= $wpdb->get_results($sql);
			if ($wpw1_cwa_audio_assessment === FALSE) {
				if ($doDebug) {
					echo "Reading wpw1_cwa_audio_assessment table<br />";
					echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
					echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			} else {
				$numAARows				= $wpdb->num_rows;
				if ($doDebug) {
					$myStr				= $wpdb->last_query;
					echo "ran $myStr<br />and retrieved $numAARows rows from wpw1_cwa_audio_assessment table<br />";
				}
				if ($numAARows > 0) {
					$content								.= "<table>
																<tr><th>Program</th>
																	<th>Date</th>
																	<th>Reason</th>
																	<th>Score</th>
																	<th>Explanation</th>";
				$myCount	= 0;
				$prev_level								= '';
				$prev_clip_name							= '';
				$prev_clip_score						= '';
				foreach ($wpw1_cwa_audio_assessment as $assessmentRow) {
					$assessment_ID						= $assessmentRow->record_id;
					$assessment_call_sign				= strtoupper($assessmentRow->call_sign);
					$assessment_assessment_date			= $assessmentRow->assessment_date;
					$assessment_level					= $assessmentRow->assessment_level;
					$assessment_clip_name				= $assessmentRow->assessment_clip_name;
					$assessment_clip					= $assessmentRow->assessment_clip;
					$assessment_score					= $assessmentRow->assessment_score;
					$assessment_notes					= $assessmentRow->assessment_notes;
					$assessment_program					= $assessmentRow->assessment_program;

					$doDisplay							= FALSE;
					if ($assessment_level != $prev_level || $assessment_clip_name != $prev_clip_name || $assessment_score !=$prev_score) {
//						$assessmentScore				= $scoreConversion[$assessment_score];
						$content						.= "<tr><td style='vertical-align:top;'>$assessment_program</td>
																<td style='vertical-align:top;'>$assessment_assessment_date</td>
																<td style='vertical-align:top;'>$assessment_level</td>
																<td style='text-align:center;vertical-align:top;'>$assessment_score</td>
																<td style='vertical-align:top;'>$assessment_notes</td></tr>";			
					}
					$myCount++;
				}
				$content		.= "</table>
									<p><b>How to Interpret This Information:</b><p>
									<p><b>Program:</b> 'registration' - student did the assessment as part of the registration process.<br />'e-o-s eval' - 
									student did the assessment at the end of the semester by request of the advisor</p>
									<p><b>Reason:</b> The assessment that the student took. Note that all Morse code was sent at a 
									character speed of 25wpm with appropriate Farnsworth spacing<br />
									<dl><dt>Beginner:</dt>
										<dd><em>During Registration:</em> Student requested a Beginner class and was given an assessment clip at an effective speed of 6 wpm. If the student 
									evaluated himself at more than 50%, the system gave the student the option to take a Fundamental level class instead</dd>
										<dd><em>During E-o-S Eval:</em> Student took an end-of-semester assessment at an effective speed of 6wpm</dd>
										<dt>Fundamental:</dt>
										<dd><em>During registration:</em> Student requested a Fundamental class and was given an assessment clip at an effective speed of 6 wpm. If the student 
									evaluated himself at more than 90%, the system gave the student the option to take an Intermediate assessment</dd>
										<dd><em>During E-o-S Eval:</em> Student took an end-of-semester assessment at an effective speed of 10wpm</dd>
										<dt>Intermediate:</dt>
										<dd><em>During Registration:</em> Student requested an Intermediate class and was given an assessment clip at an effective speed of 10wpm. If 
									the student evaluated himself at less than 50%, the system the system gave the student the option to take a Fundamental class. If the 
									student evaluated himself at better than 90%, the system gave the student option of taking the Advanced 
									assessment. The student needed to score himself at greater than 90% in the Advanced assessment to be offered the option to sign up 
									for an Advanced class</dd>
										<dd><em>During E-o-S Eval:</em> Student took an end-of-semester assessment at an effective speed of 20wpm</dd>
										<dt>Advanced</dt>
										<dd><em>During Registration:</em> Student requested an Advanced class and was given an assessment clip at an effective speed of 20 wpm. If the 
									student evaluated himself at less than 50% the student was given the option to take an Intermediate class. If the student 
									evaluated himself between 50 and 90% the student was given the option to sign up for a recommended Intermediate class or 
									for an Advanced class. If the student scored 90+%, the student was given the option to take the Advanced class</dd>
										<dd><em>During E-o-S Eval:</em> Student took an end-of-semester assessment at an effective speed of 25wpm</dd></dl>";
				} else {
					if ($doDebug) {
						echo "No records found in wpw1_cwa_audio_assessment table<br />";
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "Advisor check failed<br />";
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
	$result			= write_joblog_func("Search Audio Assessment Log|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('view_a_student_assessment', 'view_a_student_assessment_func');
