function automatic_student_backfill_v1_func() {

/*	Autmatically find classes for unassigned students and make class assignments

	This program is run on demand. Eventually it'll become a cron job to run 
	automatically during the valid replacement period
	
	The program calls a function that builds a list of all advisor classes where 
		the advisor has seats open
		the advisor has not indicated that the advisor does not want any more replacement students
		
	The program then reads the student table for all unassigned students in order by the 
		oldest to the newest (ordered by request date)

	The program goes through all unassigned student's first class choices
		if a match is found the student assignment is suggested 
	
	The program then does the same thing with the remaining student's second class choices
		if a matchis found the student  assignment is suggested
		
	The program finally does te same thing with the remaining student's third class choices
		if a matchis found the student  assignment is suggested
		
	The 'fuzzySwitch' logical sets the time spread for the match
		if TRUE, the program searches for a class +- one hour
			that is, if the student says 1300, the program will look for a class at 
			1200, 1300, or 1400
		if FALSE, the program searches for direct matches
			that is, if the student says 1300, the program will look for a class at 1300
 	
 	After all possible matches have been found, the program displays a list and asks for 
 	confirmation
 	
 	The affirmed matches are then assigned and the new class information sent to the advisors
 	
 	Created 26Aug23 by Roland
 	
*/
	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$theSemester		= $initializationArray['proximateSemester'];
	$siteURL			= $initializationArray['siteurl'];
	
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
	$theURL						= "$siteURL/cwa-automatic-student-backfill-v1/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Automatic Student Backfill V$versionNumber";
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$fuzzySearch				= FALSE;
	$matchArray					= array();
	$jsonObject					= '';
	
	$daysBack					= array('Sunday,Wednesday'=>'Tuesday,Saturday', 
										'Sunday,Thursday'=>'Wednesday,Saturday', 
										'Monday,Thursday'=>'Sunday,Wednesday', 
										'Tuesday,Friday'=>'Monday,Thursday',
										'Wednesday,Saturday'=>'Tuesday,Thursday');
	$daysForward				= array('Sunday,Wednesday'=>'Monday,Thursday', 
										'Sunday,Thursday'=>'Monday,Friday', 
										'Monday,Thursday'=>'Tuesday,Friday', 
										'Tuesday,Friday'=>'Wednesday,Saturday', 
										'Wednesday,Saturday'=>'Sunday,Thursday');
	
	$theSemester				= $currentSemester;
	if ($theSemester == 'Not in Session') {
		$theSemester			= $nextSemester;
	}

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
			if ($str_key 		== "inp_option") {
				$inp_option		 = $str_value;
				$inp_option		 = filter_var($inp_option,FILTER_UNSAFE_RAW);
				if ($inp_option == 'option2') {
					$fuzzySearch	= TRUE;
				}
			}
			if ($str_key 		== "inp_assign") {
				$inp_assign		 = $str_value;
//				$inp_assign		 = filter_var($inp_assign,FILTER_UNSAFE_RAW);
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
	
	if ($testMode) {
		$studentTableName		= 'wpw1_cwa_consolidated_student2';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
		$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
		$modeInfo				= "<p><b>Program Running in TestMode</b></p>";
	} else {
		$studentTableName		= 'wpw1_cwa_consolidated_student';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
		$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
		$modeInfo				= "";
	}
	
	function fixTime($theStr) {
		$myArray				= explode(" ",$theStr);
		$thisTime				= $myArray[0];
		$thisDays				= $myArray[1];
		$myStr					= substr($thisTime,0,2);
		$returnString			= $myStr . "00 $thisDays";
		return $returnString;
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



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
$modeInfo
<p>Program searches for possible classes for unassigned students. If any are found, 
the potential assignments are displayed for verification. If verified, the assignments 
are made and the new class assignment information sent to the advisor.</p>
<p>There are two options for finding possible class matches. Option 1 will find classes 
that exactly match the student's preferred class choices. Option 2 will find classes plus 
or minus one hour from the student's preferred class coices.</p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td>Search Option 1:</td>
	<td><input type='radio' class='formInputButton' name='inp_option' value='option1' required checked>Exact Time Match</td></tr>
<tr><td>Search Option 2:</td>
	<td><input type='radio' class='formInputButton' name='inp_option' value='option2' required>Plus/Minus one hour time match <b>NOT YET WORKING</b></td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 2<br />
				  inp_option: $inp_option<br />";
		}
	
		// get the advisors with available seats
		$availableAdvisors		= build_list_of_available_classes($theSemester,$testMode,$doDebug);
		
//		if ($doDebug) {
//			// export the array as a json object
//			$thisObject				= json_encode($availableAdvisors);
//			echo "$thisObject<br />";
//		}
		
		
		// get unassigned students and build that array
		$studentArray	= array();
		$sql			= "select * from $studentTableName
							where semester='$theSemester' 
							and response = 'Y' 
							and student_status = '' 
							and intervention_required != 'H' 
							order by request_date, level, call_sign";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
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
			$content		.= "Unable to obtain content from $studentTableName<br />";
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
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$student_last_name 						= no_magic_quotes($student_last_name);
					
					if ($student_first_class_choice_utc != '' && $student_first_class_choice_utc != 'None') {
						$student_first_class_choice_utc		= fixTime($student_first_class_choice_utc);
					}
					if ($student_second_class_choice_utc != '' && $student_second_class_choice_utc != 'None') {
						$student_second_class_choice_utc		= fixTime($student_second_class_choice_utc);
					}
					if ($student_third_class_choice_utc != '' && $student_third_class_choice_utc != 'None') {
						$student_third_class_choice_utc		= fixTime($student_third_class_choice_utc);
					}

					

					$studentArray[$student_call_sign]['name']				= "$student_last_name, $student_first_name";
					$studentArray[$student_call_sign]['level']				= $student_level;
					$studentArray[$student_call_sign]['firstChoiceUTC']		= $student_first_class_choice_utc;
					$studentArray[$student_call_sign]['secondChoiceUTC']	= $student_second_class_choice_utc;
					$studentArray[$student_call_sign]['thirdChoiceUTC']		= $student_third_class_choice_utc;
					$studentArray[$student_call_sign]['firstChoice']		= $student_first_class_choice;
					$studentArray[$student_call_sign]['secondChoice']		= $student_second_class_choice;
					$studentArray[$student_call_sign]['thirdChoice']		= $student_third_class_choice;
					$studentArray[$student_call_sign]['excludedAdvisor']	= $student_excluded_advisor;
					$studentArray[$student_call_sign]['requestDate']		= $student_request_date;
				}
			}
		}
		if ($doDebug) {
			echo "<br /><b>student Array:</b><br /><pre>";
			print_r($studentArray);
			echo "</pre><br />";
		}
		$myStr			= "<h4>Searching for Exact Advisor Schedule Match</h4>";
		if ($inp_option == 'option2') {
			$myStr		= "<h4>Searching for Advisor Schedule Plus/Minus One Hour from Student Choice</h4>";
		}
		$content		.= "<h3>$jobname</h3>
							$myStr
							<form method='post' action='$theURL' 
							name='proceed_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='3'>
							<table style='width:1000px'>
							<tr><th>Assign?</th>
								<th>Advisor</th>
								<th>Class</th>
								<th>Level</th>
								<th>Advisor Class Schedule UTC</th>
								<th>Class Size</th>
								<th>Available Seats</th>
								<th>Student</th>
								<th>Name</th>
								<th>Student Class Choice UTC</th></tr>";


		// process class choices




		$choiceArray			= array('firstChoiceUTC', 
										'secondChoiceUTC', 
										'thirdChoiceUTC');
										
		foreach($choiceArray as $thisChoice) {
			if ($doDebug) {
				echo "<br />checking $thisChoice<br />";
			}
			foreach($studentArray as $thisCallSign => $thisData) {
				$studentName					= $thisData['name'];
				$studentLevel					= $thisData['level'];
				$studentfirstChoiceUTC			= $thisData['firstChoiceUTC'];
				$studentsecondChoiceUTC			= $thisData['secondChoiceUTC'];
				$studentthirdChoiceUTC			= $thisData['thirdChoiceUTC'];
				$studentfirstChoice				= $thisData['firstChoice'];
				$studentsecondChoice			= $thisData['secondChoice'];
				$studentthirdChoice				= $thisData['thirdChoice'];
				$studentExcludedAdvisor			= $thisData['excludedAdvisor'];
				$studentRequestDate				= $thisData['requestDate'];

				$gotMatch						= FALSE;
				if ($doDebug) {
					echo "<br />processing student $thisCallSign<br />
							level: $studentLevel<br />
							choice: ${'student' . $thisChoice}<br />";
				}
				if ($inp_option == 'option1') {
					// test for an exact match
					$thisTest			= $availableAdvisors[$studentLevel];
//					if ($doDebug) {
//						echo "availableAdvisors:<br /><pre>";
//						print_r($thisTest);
//						echo "</pre><br />";
//					}
					foreach($thisTest as $thisTime=>$thisData) {
						if (${'student' . $thisChoice} == $thisTime && !$gotMatch) {
							if ($doDebug) {
								echo "gotMatch is false<br />";
								echo "testing student choice ${'student' . $thisChoice} against $thisTime ";
							}
							foreach($thisData as $advisorCallSign=>$advisorData) {
								if (!$gotMatch) {
									$advisorLocal			= $advisorData['local'];
									$advisorClassSize		= $advisorData['class size'];
									$advisorNumberStudents	= $advisorData['number_students'];
									$advisorAvailableSeats	= $advisorData['availableSeats'];
									$advisorSequence		= $advisorData['sequence'];
									$myInt					= strpos($studentExcludedAdvisor,$advisorCallSign);
									if ($myInt === FALSE) {
										if ($doDebug) {
											echo "<b>GOT A MATCH</b> for real<br />";
										}
										$gotMatch			= TRUE;
										$content		.= "<tr><td><input type='checkbox' class='formInputButton' name='inp_assign[]' id='$thisCallSign|$advisorCallSign|$advisorSequence' value='$thisCallSign|$advisorCallSign|$advisorSequence' checked> Assign</td>
																<td>$advisorCallSign</td>
																<td style='text-align:center;'>$advisorSequence</td>
																<td>$studentLevel</td>
																<td>$thisTime</td>
																<td style='text-align:center;'>$advisorClassSize</td>
																<td style='text-align:center;'>$advisorAvailableSeats</td>
																<td>$thisCallSign</td>
																<td>$studentName</td>
																<td>${'student' . $thisChoice}</td></tr>";
										$matchArray[]	= "$thisCallSign|$advisorCallSign|$advisorSequence";
										// unset the student record
										unset($studentArray[$thisCallSign]);
										if ($doDebug) {
											if (array_key_exists($thisCallSign,$studentArray)) {
												echo "unsetting the student <b>DID NOT</b> work<br />";
											} else {
												echo "student record removed from studentArray<br />";
											}
										}
										// reduce number of seats. If less than one, unset the advisor record
										$advisorAvailableSeats--;
										$advisorNumberStudents++;
										if ($advisorAvailableSeats < 1) {
											unset($availableAdvisors[$studentLevel][$thisTime][$advisorCallSign]);
											if ($doDebug) {
												echo "advisor available seats less than 1. Unset the advisor record<br />";
											}
										} else {
											$availableAdvisors[$studentLevel][$thisTime][$advisorCallSign]['number_students']	= $advisorNumberStudents;
											$availableAdvisors[$studentLevel][$thisTime][$advisorCallSign]['availableSeats']	= $advisorAvailableSeats;
										}	
									}					
								}
							}
						}
						if (!$gotMatch) {
							if ($doDebug) {
								echo "no match<br />";
							}
						}
					}
				} else {				// option2
					$myArray				= explode(" ",${'student' . $thisChoice});
					$thisTime				= intval($myArray[0]);
					$thisDays				= $myArray[1];
					$lowTime				= $thisTime - 100;
					$lowDays				= $thisDays;
					if ($lowTime < 0) {
						$lowTime			= $lowTime + 2400;
						$lowDays			= $daysBack[$lowDays];
					}
					$lowTime			= str_pad($lowTime,4,'0',STR_PAD_LEFT);
					$lowSearch			= "$lowTime $lowDays";

					$highTime			= $thisTime + 100;
					$highDays			= $thisDays;
					if ($highTime > 2400) {
						$highTime		= $highTime - 2400;
						$highDays		= $daysForward[$highDays];
					}
					$highTime			= str_pad($highTime,4,'0',STR_PAD_LEFT);
					$highSearch			= "$highTime $highDays";
					
					if ($doDebug) {
						echo "option2 search criteria:<br />
								$lowSearch<br />
								$highSearch<br />";
					}
					$thisTest			= $availableAdvisors[$studentLevel];
//					if ($doDebug) {
//						echo "availableAdvisors:<br /><pre>";
//						print_r($thisTest);
//						echo "</pre><br />";
//					}
					foreach($thisTest as $thisTime=>$thisData) {
						if (${'student' . $thisChoice} == $lowSearch) {
							if ($doDebug) {
								echo "<b>GOT A MATCH</b><br />";
							}
						} elseif(${'student' . $thisChoice} == $highSearch) {
							if ($doDebug) {
								echo "<b>GOT A MATCH</b><br />";
							}
						} else {
							echo "no match<br />";
						}
					}	
				}		
			}	
		}
		$content				.= "</table>
									<input class='formInputButton' type='submit' value='Make Assignments' />
									</form>
									<br clear='all' />
									<p>Clicking on 'Make Assignments' will assign the selected students and send 
									the advisor(s) a new class update</p>";
									
		if ($doDebug) {
			echo "<br />available Advisors<br /><pre>";
			print_r($availableAdvisors);
			echo "</pre><br /><br />student Array<br /><pre>";
			print_r($studentArray);
			echo "</pre><br />";
		}






	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass3<br /><pre>";
			print_r($inp_assign);
			echo "</pre><br />";
		}
		$content				.= "<h3>$jobname</h3>";
		$advisorArray			= array();
		$increment				= 0;
		
		foreach($inp_assign as $thisValue) {
			$myArray			= explode("|",$thisValue);
			$studentCallSign	= $myArray[0];
			$advisorCallSign	= $myArray[1];
			$advisorClass		= $myArray[2];
			
			// assign student to the advisor class
			$inp_data			= array('inp_student'=>$studentCallSign,
										'inp_semester'=>$theSemester,
										'inp_assigned_advisor'=>$advisorCallSign,
										'inp_assigned_advisor_class'=>$advisorClass,
										'inp_remove_status'=>'',
										'inp_arbitrarily_assigned'=>'',
										'inp_method'=>'add',
										'jobname'=>$jobname,
										'userName'=>$userName,
										'testMode'=>$testMode,
										'doDebug'=>$doDebug);
						
	 		$addResult			= add_remove_student($inp_data);
			if ($addResult[0] === FALSE) {
				$thisReason		= $removeResult[1];
				if ($doDebug) {
					echo "attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason<br />";
				}
				sendErrorEmail("$jobname Attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason");
				$content		.= "Attempting to add $student_call_sign to $student_assigned_advisor class failed:<br />$thisReason<br />";
			} else {
				if (!in_array($advisorCallSign,$advisorArray)) {
					$advisorArray[]		= $advisorCallSign;
				}
				$content				.= "$studentCallSign has been assigned to advisor $advisorCallSign's class $advisorClass<br />";
			}
		}
		
		// send new class information to affected advisors
		foreach($advisorArray as $advisorCallSign) {

			$sql						 	= "select email 
												from $advisorTableName 
												where call_sign = '$advisorCallSign' 
												order by date_created DESC 
												limit 1 ";
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
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$advisor_email				= $advisorRow->email;
						$advisor_subject			= "CW Academy - Complete List of Students in Your Class";
						$email_to_advisor			= "<p>On a regular basis the system checks the unassigned students 
														against advisor classes with seats available. One or more 
														unassigned students matched your class level and schedule. 
														You may keep or drop as you choose.</p>
														<p>If you do not wish to have any more students automatically 
														assigned to your class, go to cwops.org --> CW Academy --> 
														Advisor Resources --> Show Advisor Class Assignments and 
														click on the option 'No more students'.</p>";

						if ($testMode) {
							$advisor_subject		= "TESTMODE $advisor_subject";
						}
						$prepareResult				= prepare_preassigned_class_display($advisorCallSign,
																					$theSemester,
																					'Full',
																					TRUE,
																					FALSE,
																					FALSE,
																					$testMode,
																					$doDebug);
						if ($prepareResult[0] == FALSE) {
							$content				.= "<p>Getting the data to send to the advisor failed. $prepareResult[1]<br />";
							$errorMsg				= "prepare_preassigned_class_display failed: $prepareResult[1]";
							sendErrorEmail($errorMsg);
						} else {
							$email_to_advisor		.= $prepareResult[1];

							$email_to_advisor .= "<p>You can at any time see your class and assigned students as well as update the student's verification 
													status by going to cwops.org --> CW Academy --> Advisor Resources --> Show Advisor Class Assignments.</p>
													<p>Thanks for your service as an advisor!<br />
													CW Academy</p>
													<p><span style='color:red;font-size:14pt;'><b>Do not reply to this email as the address is not monitored. 
													<br />Please refer to the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA Class 
													Resolution</a> for assistance.</b></span><br /></p>";


							if ($testMode) {
								$mailCode				= 5;
								$email_to				= 'rolandksmith@gmail.com';
								$increment++;
							} else {
								$email_to				= $advisor_email;
								$mailCode				= 21;
								$increment++;
							}
							$mailResult		= emailFromCWA_v2(array('theRecipient'=>$email_to,
																	'theSubject'=>$advisor_subject,
																	'jobname'=>$jobname,
																	'theContent'=>$email_to_advisor,
																	'theCc'=>'',
																	'mailCode'=>$mailCode,
																	'increment'=>$increment,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug));
							if ($mailResult === TRUE) {
								if ($doDebug) {
									echo "An email for $advisorCallSign was sent to $email_to<br />";
								}
								$content .= "A Push email for $advisorCallSign was sent to $email_to<br />";
							} else {
								$content .= "The mail send function to $email_to failed.<br /><br />";
							}
						}
					}
				} else {
					$content	.= "No $advisorTableName record found for $advisorCallSign<br />";
				}
			}
		}




	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog failed</p>";
	}
	return $content;
}
add_shortcode ('automatic_student_backfill_v1', 'automatic_student_backfill_v1_func');
