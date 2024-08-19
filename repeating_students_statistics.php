function repeating_students_statistics_func() {

/*	How Many Students at Each Level Sign Up In the Next or Subsequent Semesters
 
 	// modified 1Oct22 by Roland for new timezone process
 	// modified 17Apr23 by Roland to fix action_log
 	// modified 13Jul23 by Roland to use consolidated tables
 	
*/
	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 						= $initializationArray['validUser'];
	$validUser 						= $initializationArray['validUser'];
	$userName						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


	ini_set('display_errors','1');
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',720);
	error_reporting(E_ALL);	
	
	$strPass					= "1";
	$errorCount					= 0;

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

/// build the semesterArray and semesterSequence
	$theURL						= "$siteURL/cwa-repeating-student-statistics/";
	$pastSemesters				= $initializationArray['pastSemesters'];
	$currentSemester			= $initializationArray['currentSemester'];
	$prevSemester				= $initializationArray['prevSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$semesterFour				= $initializationArray['semesterFour'];
	$semesterArray				= explode("|",$pastSemesters);
	if ($currentSemester != 'Not in Session') {
		$semesterArray[]		= $currentSemester;
	}
	$semesterArray[]			= $nextSemester;
	$semesterArray[]			= $semesterTwo;
	$semesterArray[]			= $semesterThree;
	$semesterArray[]			= $semesterFour;
	$semesterCount				= count($semesterArray) - 1;
	$semesterSequence 			= array();
	for($ii=0;$ii<$semesterCount;$ii++) {
		$firstSemester 			= $semesterArray[$ii];
		$remainingSemesters 	= "";
		$needSeparator			= FALSE;
		for($jj=$ii+1;$jj<=$semesterCount;$jj++) {
			if ($needSeparator) {
				$remainingSemesters .= "|$semesterArray[$jj]";
				$needSeparator		= TRUE;
			} else {
				$remainingSemesters .= "$semesterArray[$jj]";
				$needSeparator		= TRUE;		
			}
		}
		$semesterSequence[$firstSemester] = $remainingSemesters;
	}
	$currentSemester			= $initializationArray['currentSemester'];
	$lastSemester				= $currentSemester;
	$prevSemester				= $initializationArray['prevSemester'];
	if ($currentSemester == 'Not in Session') {
		$lastSemester			= $prevSemester;
	}
	if ($doDebug) {
		echo "Have built the semesterArray and semesterSequence array<br />
		semesterArray:<br /><pre>";
		print_r($semesterArray);
		echo "</pre><br /><br />semesterSequence array:<br /><pre>";
		print_r($semesterSequence);
		echo "</pre><br /><br />lastSemester: $lastSemester<br />";
		
	}

	$statsArray					= array();
	$classesArray				= array();
	$checkStudentArray			= array();
	$levelSequence				= array('Beginner',
										'Fundamental',
										'Intermediate');
	$levelUpArray				= array('Beginner'=>'Fundamental',
										'Fundamental'=>'Intermediate',
										'Intermediate'=>'Advanced');

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
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
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
		$studentTableName				= "wpw1_cwa_consolidated_student2";
	} else {
		$studentTableName				= "wpw1_cwa_consolidated_student";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at $strPass pass<br />";
		}
		$content 		.= "<h3>Repeating Students Statisics</h3>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data''>
<input type='hidden' name='strpass' value='2'>
<input class='formInputButton' type='submit' value='Next' />
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at $strPass pass<br />";
		}
	
// generate statsArray data
		foreach($semesterArray as $thisSemester) {
			foreach($levelSequence as $thisLevel) {
				if ($doDebug) {
					echo "Building statsArray|$thisSemester|$thisLevel|<br />";
				}
				$statsArray[$thisSemester][$thisLevel]['signedup']		= 0;
				$statsArray[$thisSemester][$thisLevel]['notverified']	= 0;
				$statsArray[$thisSemester][$thisLevel]['verified']		= 0;
				$statsArray[$thisSemester][$thisLevel]['notassigned']	= 0;
				$statsArray[$thisSemester][$thisLevel]['assigned']		= 0;
				$statsArray[$thisSemester][$thisLevel]['notpromoted']	= 0;
				$statsArray[$thisSemester][$thisLevel]['promoted']		= 0;
				for($ii=1;$ii<=$semesterCount;$ii++) {
					$semesterName										= $semesterArray[$ii];
					$statsArray[$thisSemester][$thisLevel][$semesterName] = 0;
				}
			}
		}
//		if ($doDebug) {
//			echo "<br />statsArray built:<br /><pre>";
//			print_r($statsArray);
//			echo "</pre><br />";
//		}
		
// go through the student table and total up the info
		$sql				= "select * from $studentTableName 
								order by call_sign";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			if ($doDebug) {
				echo "Reading $studentTableName table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numSRows rows from $studentTableName table<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_ph_code						= $studentRow->ph_code;
					$student_phone  						= $studentRow->phone;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country_code					= $studentRow->country_code;
					$student_country  						= $studentRow->country;
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
					$student_abandoned  				= $studentRow->abandoned;
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
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					if ($student_semester == '2020 Apr/May') {
						$student_semester	= '2020 APR/MAY';
					}
					if ($student_semester == '2020 Sep/Oct') {
						$student_semester	= '2020 SEP/OCT';
					}
					if ($student_semester == '2021 JAN/FEB') {
						$student_semester	= '2021 Jan/Feb';
					}
					if ($student_level == 'Advanced') {
						$classesArray[$student_call_sign][$student_level]		= $student_semester;
					} else {
						$classesArray[$student_call_sign][$student_level]	= $student_semester;
						if (in_array($student_semester,$semesterArray)) {
							if ($doDebug) {
								echo "<br />Processing $student_call_sign; semester: $student_semester; level: $student_level<br />";
							}
							$statsArray[$student_semester][$student_level]['signedup']++;
							if ($student_response == 'Y') {
								$statsArray[$student_semester][$student_level]['verified']++;
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;verified<br />";
								}
								if ($student_student_status == 'Y') {
									$statsArray[$student_semester][$student_level]['assigned']++;
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp;&nbsp;assigned<br />";
									}
									if ($student_promotable == 'P' || $student_promotable == '') {
										$statsArray[$student_semester][$student_level]['promoted']++;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;promotable<br />";
										}
										$checkStudentArray[]			= "$student_call_sign|$student_level|$student_semester";
									} else {
										$statsArray[$student_semester][$student_level]['notpromoted']++;
										if ($doDebug) {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;not promoted<br />";
										}
									}
								} else {
									$statsArray[$student_semester][$student_level]['notassigned']++;
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp;&nbsp;not assigned<br />";
									}
								}
							} else {
								$statsArray[$student_semester][$student_level]['notverified']++;
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;not verified<br />";
								}
							}
						} else {
							if ($doDebug) {
								echo "ERROR: Semester of $student_semester for $student_call_sign not in semesterArray<br />";
								$errorCount++;
							}
						}
					}
				}
			} else {
				echo "No records found in $thisTableName table<br />";
			}
		}
/// figure out which students have signed up for the next level class
		if ($doDebug) {
			echo "<br />classesArray:<br /><pre>";
			print_r($classesArray);
			echo "</pre><br /><br />checkStudentArray<br /><pre>";
			print_r($checkStudentArray);
			echo "</pre><br /><br />Checking next class for each student<br />";
		}
		foreach($checkStudentArray as $myValue) {
			if ($doDebug) {
				echo "<br />checking $myValue<br />";
			}
			$myArray					= explode("|",$myValue);
			$student_call_sign			= $myArray[0];
			$student_level				= $myArray[1];
			$student_semester			= $myArray[2];
			$levelUp					= $levelUpArray[$student_level];
			$nextClass					= '';
			if ($doDebug) {
				echo "checking for classesArray | $student_call_sign | $levelUp<br />";
			}
			if (array_key_exists($student_call_sign,$classesArray)) {
				foreach($classesArray[$student_call_sign] as $thisKey=>$thisValue) {
					if($thisKey == $levelUp) {
						$nextClass		= $classesArray[$student_call_sign][$levelUp];
					}
				}
			}
			if ($nextClass != '') {
				if (isset($statsArray[$student_semester][$student_level][$nextClass])) {
					$statsArray[$student_semester][$student_level][$nextClass]++;
					if ($doDebug) {
						echo "Got a match. Adding 1 to statsArray $student_semester | $levelUp | $nextClass<br />";
					}
				} else {
					if ($doDebug) {
						echo "ERROR: statsArray | $student_semester | $student_level | $nextClass doesn't exist<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "No match found<br />";
				}
			}
		}		
		
		if ($doDebug) {	
			echo "<br /><br />statsArray:<br /><pre>";
			print_r($statsArray);
			echo "</pre><br /><br />Error count: $errorCount<br />";
			
			
		}
		$content		.= "<h3>Repeating Students Statistics</h3>";

		foreach($semesterArray as $thisSemester) {
			$myInt01	= $statsArray[$thisSemester]['Beginner']['signedup'];
			$myInt02	= $statsArray[$thisSemester]['Fundamental']['signedup'];
			$myInt03	= $statsArray[$thisSemester]['Intermediate']['signedup'];
			$myInt04	= $statsArray[$thisSemester]['Beginner']['notverified'];
			$myInt05	= $statsArray[$thisSemester]['Fundamental']['notverified'];
			$myInt06	= $statsArray[$thisSemester]['Intermediate']['notverified'];
			$myInt07	= $statsArray[$thisSemester]['Beginner']['verified'];
			$myInt08	= $statsArray[$thisSemester]['Fundamental']['verified'];
			$myInt09	= $statsArray[$thisSemester]['Intermediate']['verified'];
			$myInt10	= $statsArray[$thisSemester]['Beginner']['notassigned'];
			$myInt11	= $statsArray[$thisSemester]['Fundamental']['notassigned'];
			$myInt12	= $statsArray[$thisSemester]['Intermediate']['notassigned'];
			$myInt13	= $statsArray[$thisSemester]['Beginner']['assigned'];
			$myInt14	= $statsArray[$thisSemester]['Fundamental']['assigned'];
			$myInt15	= $statsArray[$thisSemester]['Intermediate']['assigned'];
			$myInt16	= $statsArray[$thisSemester]['Beginner']['notpromoted'];
			$myInt17	= $statsArray[$thisSemester]['Fundamental']['notpromoted'];
			$myInt18	= $statsArray[$thisSemester]['Intermediate']['notpromoted'];
			$myInt19	= $statsArray[$thisSemester]['Beginner']['promoted'];
			$myInt20	= $statsArray[$thisSemester]['Fundamental']['promoted'];
			$myInt21	= $statsArray[$thisSemester]['Intermediate']['promoted'];

		
 			$content	.= "<p>Semester: $thisSemester</p>
							<table style='width:auto;'>
							<tr>
								<th style='width:190px;'>Statistic</th>
								<th style='width:90px;'>Beginner</th>
								<th style='width:90px;'>Fundamental</th>
								<th style='width:90px;'>Intermediate</th>
							</tr>
							<tr>
								<td>Signed Up</td>
								<td>$myInt01</td>
								<td>$myInt02</td>
								<td>$myInt03</td>
							</tr><tr>
								<td>Unverified</td>
								<td>$myInt04</td>
								<td>$myInt05</td>
								<td>$myInt06</td>
							</tr><tr>
								<td>Verified</td>
								<td>$myInt07</td>
								<td>$myInt08</td>
								<td>$myInt09</td>
							</tr><tr>
								<td>Not Assigned</td>
								<td>$myInt10</td>
								<td>$myInt11</td>
								<td>$myInt12</td>
							</tr><tr>
								<td>Assigned</td>
								<td>$myInt13</td>
								<td>$myInt14</td>
								<td>$myInt15</td>
							</tr><tr>
								<td>Not Promoted</td>
								<td>$myInt16</td>
								<td>$myInt17</td>
								<td>$myInt18</td>
							</tr><tr>
								<td>Promoted</td>
								<td>$myInt19</td>
								<td>$myInt20</td>
								<td>$myInt21</td>
							</tr><tr>
								<td colspan='4'><b>Students that were promoted (above) who signed up to take the next level class</td></tr>";
			
			if (array_key_exists($thisSemester,$semesterSequence)) {
				$thisSequence			= $semesterSequence[$thisSemester];
				$sequenceArray			= explode("|",$thisSequence);
				foreach($sequenceArray as $checkSemester) {
					$myIntBeg			= 0;
					$myIntBas			= 0;
					$myIntInt			= 0;
					$myIntBeg			= $statsArray[$thisSemester]['Beginner'][$checkSemester];
					if ($myIntBeg == 0) {
						$myIntBeg		= "&nbsp;";
					} else {
						$myIntBeg		= "&gt;$myIntBeg";
					}
					$myIntBas			= $statsArray[$thisSemester]['Fundamental'][$checkSemester];
					if ($myIntBas == 0) {
						$myIntBas		= "&nbsp;";
					} else {
						$myIntBas		= "&gt;$myIntBas";
					}
					$myIntInt			= $statsArray[$thisSemester]['Intermediate'][$checkSemester];
					if ($myIntInt == 0) {
						$myIntInt		= "&nbsp;";
					} else {
						$myIntInt		= "&gt;$myIntInt";
					}
					$content			.= "<tr>
												<td>Signed-up $checkSemester</td>
												<td style='text-align:right;'>$myIntBeg</td>
												<td style='text-align:right;'>$myIntBas</td>
												<td style='text-align:right;'>$myIntInt</td>
											</tr>";
				
				}
			}

			$content			.= "</table>";
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
	$result			= write_joblog_func("Repeating Student Statistics|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('repeating_students_statistics', 'repeating_students_statistics_func');
