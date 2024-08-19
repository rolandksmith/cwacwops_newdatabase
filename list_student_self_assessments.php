function list_student_self_assessments_func() {

// modified 1Oct22 by Roland for new timezone process fields
// modified 13Jul23 by Roland to use consolidated tables
// Modified 24Oct23 by Roland to use new assessment file

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
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

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-list-student-self-assessments/";
	$inp_timeframe				= '';
	$scoreConversion			= array('50'=>'0-49%',
										'75'=>'50-89%',
										'90'=>'90+%');
	$firstTime					= TRUE;
	$assessmentCallSignArray	= array();
	$ACSCount					= 0;
	$studentArray				= array();
	$SCount						= 0;
	$studentDataArray			= array();
	$SDACount					= 0;
	$studentsDisplayed			= 0;
	$levelConvert				= array('Beginner'=>1,
										'Fundamental'=>2,
										'Intermediate'=>3,
										'Advanced'=>4);
	$levelBack					= array(1=>'Beginner',
										2=>'Fundamental',
										3=>'Intermediate',
										4=>'Advanced');

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
			if ($str_key 		== "inp_timeframe") {
				$inp_timeframe		 = $str_value;
				$inp_timeframe		 = filter_var($inp_timeframe,FILTER_UNSAFE_RAW);
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
		$assessmentTableName		= "wpw1_cwa_new_assessment_data";
		$studentTableName			= "wpw1_cwa_consolidated_student2";
	} else {
		$extMode					= 'pd';
		$assessmentTableName		= "wpw1_cwa_new_assessment_data";
		$studentTableName			= "wpw1_cwa_consolidated_student";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>List Student Self Assessments</h3>
<p>Select a time period from the list and submit. The program will then prepare a 
report of all students who registered during that time period and their self 
assessment information from the audio assessment log. The report will only include 
student who have taken the self assessment.
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td>Time Period of Interest</td>
	<td><input type='radio' class='formInputButton' name='inp_timeframe' value='past24' checked > Yesterday and Today<br />
		<input type='radio' class='formInputButton' name='inp_timeframe' value='3days' > Past 3 days<br />
		<input type='radio' class='formInputButton' name='inp_timeframe' value='week' > Past Week<br />
		<input type='radio' class='formInputButton' name='inp_timeframe' value='all' > All Students</td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "arrived at pass 2 with timeframe of $inp_timeframe<br />";
		}
		
		$prevCallsign		= "";
		
		$content			.= "<h3>List Student Self Assessments</h3><table>";		
		
		$today				= date('Y-m-d H:i:s');
		if ($inp_timeframe == "past24") {
			$thisTime		= strtotime("$today - 1 day");
			$fromDate		= date('Y-m-d H:i:s',$thisTime);
		} elseif ($inp_timeframe == '3days') {
			$thisTime		= strtotime("$today - 3 days");
			$fromDate		= date('Y-m-d H:i:s',$thisTime);
		} elseif ($inp_timeframe == 'week') {
			$thisTime		= strtotime("$today - 1 week");
			$fromDate		= date('Y-m-d H:i:s',$thisTime);
		} else {
			$fromDate		= '2001-01-01 00:00:00';
		}
		if ($doDebug) {
			echo "Today is $today. Using records with an assessment date from $fromDate<br />";
		}

		// get all assessment records meeting the date criteria
		$sql					= "select * from wpw1_cwa_new_assessment_data 
									where date_written >= '$fromDate' 
									and date_written <= '$today' 
									order by callsign, date_written";
		$assessmentResult		= $wpdb->get_results($sql);
		if ($assessmentResult === FALSE) {
			$thisError			= $wpdb->last_error;
			if ($doDebug) {
				echo "attempting to read from wpw1_cwa_new_assessment_data table failed. Error: $lastError<br />SQL: $sql<br />";
			}
//				sendErrorEmail("$jobname Pass 104 Attempting to read from wpw1_cwa_new_assessment_data failed. Error: $lastError. SQL: $sql");
			$doProceed			= FALSE;
		} else {
			$numASRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numASRows rows<br />";
			}
			if ($numASRows > 0) {
				foreach($assessmentResult as $newAssessment) {				
					$record_id		= $newAssessment->record_id;
					$thisCallsign	= $newAssessment->callsign;
					$thisLevel		= $newAssessment->level;
					$thiscpm		= $newAssessment->cpm;
					$thiseff		= $newAssessment->eff;
					$thisfreq		= $newAssessment->freq;
					$thisquestions	= $newAssessment->questions;
					$thiswords		= $newAssessment->words;
					$thischars		= $newAssessment->characters;
					$thisScore		= $newAssessment->score;
					$thisDetail		= $newAssessment->details;
					$thisDate		= $newAssessment->date_written;

					if ($thisCallsign != $prevCallsign) {
						$prevCallsign	= $thisCallsign;
						// new student. Get the student info
						$haveStudent	= FALSE;
						$sql			= "select call_sign, 
												  first_name, 
												  last_name, 
												  phone, 
												  email, 
												  country, 
												  level, 
												  semester, 
												  assigned_advisor, 
												  assigned_advisor_class, 
												  request_date 
											from $studentTableName 
											where call_sign = '$thisCallsign' 
											and response != 'R' 
											order by date_created DESC 
											limit 1";
						$wpw1_cwa_student	= $wpdb->get_results($sql);
						if ($doDebug) {
							echo "Reading $studentTableName table<br />";
							echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
							if ($wpdb->last_error != '') {
								echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
							}
						}
						if ($wpw1_cwa_student !== FALSE) {
							$numSRows									= $wpdb->num_rows;
							if ($doDebug) {
								echo "retrieved $numSRows rows from $studentTableName table<br />";
							}
							if ($numSRows > 0) {
								if ($doDebug) {
									echo "found $numSRows rows in $studentTableName<br />";
								}
								foreach ($wpw1_cwa_student as $studentRow) {
									$student_call_sign						= strtoupper($studentRow->call_sign);
									$student_first_name						= $studentRow->first_name;
									$student_last_name						= stripslashes($studentRow->last_name);
									$student_email  						= $studentRow->email;
									$student_phone  						= $studentRow->phone;
									$student_level  						= $studentRow->level;
									$student_country  						= $studentRow->country;
									$student_semester						= $studentRow->semester;
									$student_assigned_advisor  				= $studentRow->assigned_advisor;
									$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
									$student_request_date			 		= $studentRow->request_date;
									
									$haveStudent							= TRUE;
								}
							}
						}
						if ($haveStudent) {
							$studentsDisplayed++;
							$content				.= "<tr><th>Call Sign</th>
															<th colspan='2'>Name</th>
															<th>Email</th>
															<th>Phone</th>
															<th>Country</th>
															<th>Level</th>
															<th></th>
															<th></th>
															<th></th>
															<th></th></tr>
														<tr><td>$student_call_sign</td>
															<td colspan='2'>$student_last_name, $student_first_name</td>
															<td>$student_email</td>
															<td>$student_phone</td>
															<td>$student_country</td>
															<td>$thisLevel</td>
															<td></td>
															<td></td>
															<td></td>
															<td></td></tr>
														<tr><th>Score</th>
															<th>Level</th>
															<th>Char Speed</th>
															<th>Eff Speed</th>
															<th>Questions</th>
															<th>Words</th>
															<th>Word Length</th>
															<th>Question</th>
															<th>What Was Sent</th>
															<th>What Was Copied</th>
															<th>Points Gained</th></tr>";
						
						}
					
					}
					$content		.= "<tr><td style='text-align:center;vertical-align:top;'>$thisScore</td>
											<td style='vertical-align:top;'>$thisLevel</td>
											<td style='text-align:center;vertical-align:top;'>$thiscpm</td>
											<td style='text-align:center;vertical-align:top;'>$thiseff</td>
											<td style='text-align:center;vertical-align:top;'>$thisquestions</td>
											<td style='text-align:center;vertical-align:top;'>$thiswords</td>
											<td style='text-align:center;vertical-align:top;'>$thischars</td>";

					$firstTime		= TRUE;					
					$detailsArray	= json_decode($thisDetail,TRUE);
					foreach($detailsArray as $thisKey => $thisValue) {
						$thisSent		= $thisValue['sent'];
						$thisCopied		= $thisValue['copied'];
						$thisPoints		= $thisValue['points'];
						if ($firstTime) {
							$firstTime	= FALSE;
							$content	.= "<td>Question $thisKey</td>
												<td>$thisSent</td>
												<td>$thisCopied</td>
												<td>$thisPoints</td></tr>\n";
						} else {
							$content		.= "<tr><td style='vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td>Question $thisKey</td>
													<td>$thisSent</td>
													<td>$thisCopied</td>
													<td>$thisPoints</td></tr>\n";
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "No assessment records found<br />";
				}
			}
		}
		$content			.= "</table><p>$studentsDisplayed Student records displayed</p>";
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
	$result			= write_joblog_func("List Student Self Assessments|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('list_student_self_assessments', 'list_student_self_assessments_func');
