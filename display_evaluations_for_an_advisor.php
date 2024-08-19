function display_evaluations_for_an_advisor_func() {

/*	Display all student evaluation responses for an advisor
 *
 *	This is normally run from a link in an email sent to the advisors
 		Information coming in from the advisor: inp_advisor (call sign) inp_id (advisor_id) and mode=1
 	
 *	It can also be run by someone who is authorized
 *
 *	Pass one gets the advisor call sign and goes to pass 2
 *	
 *	Pass two checks to see what was requested. If it was a call sign 
 *	and the user is not authorized, the request is denied,
 *	
 *	If all is OK, then then the advisor is looked up to verify that 
 *	the advisor exists.
 *Name
 *	If so, then all evaluation records for that advisor are displayed
 *
 	Modified 13Oct21 by Roland to add ability to show all semesters data
 	Modified 24Oct22 by Roland for the new timezone table layouts
 	Modified 16Apr23 by Roland to fix action_log
 	Modified 12Jul23 by Roland to use consolidated table names
*/

	global $wpdb, $pastAdvisorTableName, $doDebug;
	
	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 						= $initializationArray['validUser'];
	$userName  						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
/*	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
*/

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$theURL						= "$siteURL/cwa-display-evaluations-for-an-advisor/";
	$strPass					= "1";
	$inp_semester				= '';
	$inp_id						= '';
	$inp_advisor				= '';
	$theSemester				= '';
	$mode						= '';
	$token						= '';

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
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_id") {
				$inp_id			 = $str_value;
				$inp_id			 = filter_var($inp_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token			 = $str_value;
				$token			 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "mode") {
				$mode			 = $str_value;
				$mode			 = filter_var($mode,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_advisor") {
				$inp_advisor	 = strtoupper($str_value);
				$inp_advisor	 = filter_var($inp_advisor,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "theSemester") {
				$theSemester	 = $str_value;
				$theSemester 	 = filter_var($theSemester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "newSemester") {
				$newSemester	 = $str_value;
				$newSemester 	 = filter_var($newSemester,FILTER_UNSAFE_RAW);
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
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$evaluateAdvisorTableName	= "wpw1_cwa_evaluate_advisor2";
		$studentTableName			= "wpw1_cwa_consolidated_student2";
	} else {
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$evaluateAdvisorTableName	= "wpw1_cwa_evaluate_advisor";
		$studentTableName			= "wpw1_cwa_consolidated_student";
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


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		if ($validUser == "N") {
			return "YOU'RE NOT AUTHORIZED!<br />Goodby";
		} else {
			$currentSemester	= $initializationArray['currentSemester'];
			$prevSemester		= $initializationArray['prevSemester'];
			if ($currentSemester == 'Not in Session') {
				$theSemester	= $prevSemester;
			} else {
				$theSemester	= $currentSemester;
			}
			$content 		.= "<h3>Display Evaluations for an Advisor</h3>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								<input type='hidden' name='mode' value='2'>
								<input type='hidden' name='inp_id' value='unspecified'>
								<table>
								<tr><td style='width:150px;'>Advisor Call Sign</td>
									<td><input type='text' class='formInputText' name='inp_advisor' size='10' maxlength='15' required></td></tr>
								<tr><td>Semester</td>
									<td><input type='radio' name='theSemester' value='$theSemester' class='formInputButton' checked='checked'> $theSemester<br />
										<input type='radio' name='theSemester' value='specified' class='formInputButton'> Specify semester:<br />
										<input type='text' name='newSemester' class='formInputText' size='15' maxlength='15'><br />
										<input type='radio' name='theSemester' value='all' class='formInputButton'> All Semesters</td></tr>
								$testModeOption
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form></p>";
		}
///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {

		$currentSemester		= $initializationArray['currentSemester'];
		$pastSemester			= $initializationArray['prevSemester'];
		if ($currentSemester == 'Not in Session') {
			$thisSemester		= $pastSemester;
		} else {
			$thisSemester		= $currentSemester;
		}

		$goodData			= FALSE;
		// if mode=1 then input came from advisor email and has advisor and id
		if ($mode == '1') {
			if ($inp_advisor != '') {
				if ($inp_id != '') {
					if (is_numeric($inp_id)) { 	// if true so far, have data to work with
						$goodData			= TRUE;
						

					} else {
						if ($doDebug) {
							echo "Mode 1: inp_id of $inp_id is not numeric<br />";
						}
					}
				} else {
					if ($doDebug) {
						echo "Mode 1: inp_id is empty<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "Mode 1: inp_advisor is empty<br />";
				}
			}
		} elseif ($mode == '2') {
			if ($inp_id == 'unspecified') {
				if ($validUser == "Y") {
					if ($inp_advisor != '') {
						$goodData			= TRUE;
					}
				} else {
					if ($doDebug) {
						echo "Mode 2: Must be an authorized user<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "Mode 2: inp_id contains $inp_id which is incorrect<br />";
				}
			}
		} else {
			if ($doDebug) {
				echo "Mode information of $mode is invalid<br />";
			}
		}
		if ($goodData) {
			// At this point, have the advisor call sign for the data to be displayed and the semester
	
			if ($doDebug) {
				echo "Ready to get the records for advisor $inp_advisor<br />";
			}
			$content					.= "<h3>Student Evaluations for $inp_advisor</h3>";
			if ($mode == '1') {
				$pastSemesterArray		= array($thisSemester);
			} else {
				if ($theSemester == 'Specified') {
					$pastSemesterArray	= array($newSemester);
				} else {
					if ($theSemester == 'all') {
						$pastSemesters		= $initializationArray['pastSemesters'];
						$pastSemesterArray	= explode("|",$pastSemesters);
					} else {
						$pastSemesterArray	= array($theSemester);
					}
				}
			}
			if ($doDebug) {
				echo "pastSemesterArray:<br /><pre>";
				print_r($pastSemesterArray);
				echo "</pre><br />";
			}
			$myInt					= count($pastSemesterArray) - 1;
			for ($ii=$myInt;$ii>-1;$ii--) {
				$thisSemester			= $pastSemesterArray[$ii];
				$content				.= "<h4>Data for $thisSemester Semester</h4>";
		
				//////// get the evaluate_advisor and advisor record here. We need to know what class is.				
				$sql					= "select a.evaluate_id, 
												  a.advisor_callsign, 
												  a.advisor_semester, 
												  a.advisor_class, a.survey_id, 
												  a.anonymous, 
												  a.level, a.expectations, 
												  a.effective, 
												  a.curriculum, 
												  a.scales, 
												  a.morse_trainer, 
												  a.morse_runner, 
												  a.rufzxp, 
												  a.numorse_pro, 
												  a.lcwo, 
												  a.cwt, 
												  a.applications, 
												  a.qsos, 
												  a.short_stories, 
												  a.enjoy_class, 
												  a.student_comments, 
												  b.first_name, 
												  b.last_name 
											from $evaluateAdvisorTableName as a 
											join $advisorTableName as b 
											where a.advisor_semester='$thisSemester' 
												and a.advisor_callsign='$inp_advisor' 
												and a.advisor_callsign=b.call_sign 
												and a.advisor_semester=b.semester 
											order by a.evaluate_id";
				$wpw1_cwa_evaluate_advisor	= $wpdb->get_results($sql);
				if ($wpw1_cwa_evaluate_advisor === FALSE) {
					if ($doDebug) {
						echo "Reading $evaluateAdvisorTableName / $evaluateAdvisorTableName tables failed<br />";
						echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
						echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
					}
				} else {
					$numEARows				= $wpdb->num_rows;
					if ($doDebug) {
						$myStr				=  $wpdb->last_query;
						echo "ran $myStr<br />and found $numEARows rows in $evaluateAdvisorTableName table<br />";
					}
					if ($numEARows > 0) {
						foreach ($wpw1_cwa_evaluate_advisor as $evaluateAdvisorRow) {
							$evaluateAdvisor_ID					= $evaluateAdvisorRow->evaluate_id;
							$evaluateAdvisor_advisor_callsign	= strtoupper($evaluateAdvisorRow->advisor_callsign);
							$evaluateAdvisor_advisor_semester	= $evaluateAdvisorRow->advisor_semester;
							$evaluateAdvisor_advisor_class		= $evaluateAdvisorRow->advisor_class;
							$evaluateAdvisor_survey_id			= $evaluateAdvisorRow->survey_id;
							$evaluateAdvisor_anonymous			= strtoupper($evaluateAdvisorRow->anonymous);
							$evaluateAdvisor_level	 			= $evaluateAdvisorRow->level;
							$evaluateAdvisor_expectations		= $evaluateAdvisorRow->expectations;
							$evaluateAdvisor_effective 			= $evaluateAdvisorRow->effective;
							$evaluateAdvisor_curriculum 		= $evaluateAdvisorRow->curriculum;
							$evaluateAdvisor_scales 			= $evaluateAdvisorRow->scales;
							$evaluateAdvisor_morse_trainer 		= $evaluateAdvisorRow->morse_trainer;
							$evaluateAdvisor_morse_runner 		= $evaluateAdvisorRow->morse_runner;
							$evaluateAdvisor_rufzxp 			= $evaluateAdvisorRow->rufzxp;
							$evaluateAdvisor_numorse_pro 		= $evaluateAdvisorRow->numorse_pro;
							$evaluateAdvisor_lcwo		 		= $evaluateAdvisorRow->lcwo;
							$evaluateAdvisor_cwt 				= $evaluateAdvisorRow->cwt;
							$evaluateAdvisor_applications 		= $evaluateAdvisorRow->applications;
							$evaluateAdvisor_qsos 				= $evaluateAdvisorRow->qsos;
							$evaluateAdvisor_short_stories	 	= $evaluateAdvisorRow->short_stories;
							$evaluateAdvisor_enjoy_class 		= $evaluateAdvisorRow->enjoy_class;
							$evaluateAdvisor_student_comments 	= $evaluateAdvisorRow->student_comments;
							$advisor_first_name					= $evaluateAdvisorRow->first_name;
							$advisor_last_name					= $evaluateAdvisorRow->last_name;

							$advisor_last_name 					= no_magic_quotes($advisor_last_name);
							
							$thisStudentName					= '';
							if ($evaluateAdvisor_anonymous != '') {
								// get the student name
								$studentSQL						= "select last_name, 
																		   first_name 
																	from $studentTableName 
																	where call_sign = '$evaluateAdvisor_anonymous' 
																	and semester = '$thisSemester'";
								$studentResponse				= $wpdb->get_results($studentSQL);
								if ($studentResponse === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$numSRows					= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $studentSQL<br />and retrieved $numSRows rows<br />";
									}
									if ($numSRows > 0) {
										foreach($studentResponse as $studentResponseRow) {
											$thisLastName		= $studentResponseRow->last_name;
											$thisFirstName		= $studentResponseRow->first_name;
											$thisStudentName	= "($thisLastName, $thisFirstName)";
										}
									}
								}
							}

							if ($evaluateAdvisor_student_comments != '') {
								$evaluateAdvisor_student_comments	= str_replace("<p>","",$evaluateAdvisor_student_comments);
								$evaluateAdvisor_student_comments	= str_replace("</p>","",$evaluateAdvisor_student_comments);
								$evaluateAdvisor_student_comments	= stripslashes($evaluateAdvisor_student_comments);
							}
							if ($evaluateAdvisor_applications != '') {
								$evaluateAdvisor_applications	= str_replace("<p>","",$evaluateAdvisor_applications);
								$evaluateAdvisor_applications	= str_replace("</p>","",$evaluateAdvisor_applications);
								$evaluateAdvisor_applications	= stripslashes($evaluateAdvisor_applications);
							}
			
							$content	.= "<table style='width:900px;'>
											<tr><th style='width:200px;'>Field</th><th>Value</th></tr>
											<tr><td>Advisor Call Sign:</td><td>$evaluateAdvisor_advisor_callsign</td></tr>
											<tr><td>Advisor Name:</td><td>$advisor_last_name, $advisor_first_name	</td></tr>
											<tr><td>Anonymous:</td><td>$evaluateAdvisor_anonymous ($thisLastName, $thisFirstName)</td></tr>
											<tr><td>Level:</td><td>$evaluateAdvisor_level</td></tr>
											<tr><td>Semester:</td><td>$evaluateAdvisor_advisor_semester</td></tr>
											<tr><td>Expectations:</td><td>$evaluateAdvisor_expectations</td></tr>
											<tr><td>Effective:</td><td>$evaluateAdvisor_effective</td></tr>
											<tr><td>Curriculum:</td><td>$evaluateAdvisor_curriculum</td></tr>
											<tr><td>Scales:</td><td>$evaluateAdvisor_scales</td></tr>
											<tr><td>MCT, Word List, or ICR:</td><td>$evaluateAdvisor_morse_trainer</td></tr>
											<tr><td>Morse Runner:</td><td>$evaluateAdvisor_morse_trainer</td></tr>
											<tr><td>LCWO</td><td>$evaluateAdvisor_lcwo</td></tr>
											<tr><td>RufzXP:</td><td>$evaluateAdvisor_rufzxp</td></tr>
											<tr><td>CWT:</td><td>$evaluateAdvisor_cwt</td></tr>
											<tr><td>QSOs</td><td>$evaluateAdvisor_qsos</td></tr>
											<tr><td>Short Stories</td><td>$evaluateAdvisor_short_stories</td></tr>
											<tr><td>Enjoy Class</td><td>$evaluateAdvisor_enjoy_class</td></tr>
											<tr><td style='vertical-align:top;'>Student Comments:</td><td>$evaluateAdvisor_student_comments</td></tr>
											<tr><td style='vertical-align:top;'>Applications:</td><td>$evaluateAdvisor_applications</td></tr>
											</table>";
		
						}
					} else {
						$content	.= "No Records found";
						if ($doDebug) {
							echo "Didn't find any $evaluateAdvisorTableName records<br />";
						}
					}
				}
			}
			// remove the reminder if applicable
			if ($token != '') {
				$result		= resolve_reminder($inp_advisor,$token,$testMode,$doDebug);			
			}
		} else {
			$content		.= "Invalid Request";
			if ($doDebug) {
				echo "Invalid request being returned<br />";
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
	$result			= write_joblog_func("Display Evaluations for an Advisor|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('display_evaluations_for_an_advisor', 'display_evaluations_for_an_advisor_func');
