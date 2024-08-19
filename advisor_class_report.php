function advisor_class_report_func() {

/*	Prepares a report listing advisors in advisor call sign order
	and their classes with information about class size and the 
	number of students assigned to the class
	
	Created 2Aug2021 by Roland
	
	classCountArray[advisor call sign][class sequence] = count
	
	Modified 15Apr23 by Roland to correct action log handling
	Modified 12Jul23 by Roland to use consolidated tables
	
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
	$validUser 			= $initializationArray['validUser'];
	$userName  			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		ini_set('memory_limit','256M');
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-advisor-class-report/";
	$inp_semester				= '';
	$goOn						= TRUE;
	$totalClassSize				= 0;
	$totalStudents				= 0;
	$totalClasses				= 0;
	$totalAvail					= 0;
	$availArray					= array();
	$errorArray					= array();
	$jobname					= "Advisor Class Report";

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
				$strPass		 = filter_var($strPass,FILTER_SANITIZE_STRING);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		 = filter_var($inp_mode,FILTER_SANITIZE_STRING);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_SANITIZE_STRING);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
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
		$advisorTableName		= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass2";
		$studentTableName		= "wpw1_cwa_consolidated_student2";
	} else {
		$advisorTableName		= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass";
		$studentTableName		= "wpw1_cwa_consolidated_student";
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
		$content 		.= "<h3>Advisor Class Report</h3>
							<p>This program prepares a report of all advisors, their classes, 
							and the number of students currently assigned to their classes. 
							The program reports from the current semester, if it is in session. 
							Otherwise it reports from the upcoming semester. The report is mainly 
							useful after students have been assigned to advisor classes.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p><br /><br />";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 2<br />";
		}
		
		$content				.= "<h3>Advisor Class Report</h3>";
		
		$currentSemester		= $initializationArray['currentSemester'];
		$nextSemester			= $initializationArray['nextSemester'];
		if ($currentSemester == 'Not in Session') {
			$theSemester		= $nextSemester;
		} else {
			$theSemester		= $currentSemester;
		}
		////////	Get all the advisors and their classes and stick in an array

		$prevAdvisor	= '';
		$sql			= "select a.call_sign, 
								  a.last_name, 
								  a.first_name, 
								  a.ph_code, 
								  a.phone, 
								  a.email, 
								  a.city,
								   a.state, 
								   a.country, 
								   a.timezone_id, 
								   a.timezone_offset, 
								   b.sequence, 
								   b.level, 
								   b.class_size, 
								   b.class_schedule_days, 
								   b.class_schedule_times, 
								   b.class_schedule_days_utc, 
								   b.class_schedule_times_utc, 
								   b.class_incomplete 
							from $advisorTableName as a 
							join $advisorClassTableName as b 
							where a.call_sign=b.advisor_call_sign 
							and a.semester = '$theSemester' 
							and a.semester = b.semester 
							order by b.advisor_call_sign, 
									b.sequence";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName/$advisorClassTableName tables failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName/$advisorClassTableName tables failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_call_sign 						= strtoupper($advisorRow->call_sign);
					$advisor_first_name 					= $advisorRow->first_name;
					$advisor_last_name 						= stripslashes($advisorRow->last_name);
					$advisor_email 							= strtolower($advisorRow->email);
					$advisor_phone							= $advisorRow->phone;
					$advisor_ph_code						= $advisorRow->ph_code;				// new
					$advisor_city 							= $advisorRow->city;
					$advisor_state 							= $advisorRow->state;
					$advisor_country 						= $advisorRow->country;
					$advisor_timezone_id					= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset				= $advisorRow->timezone_offset;		// new
					$advisorClass_sequence 					= $advisorRow->sequence; 
					$advisorClass_level 					= $advisorRow->level; 
					$advisorClass_class_size 				= $advisorRow->class_size; 
					$advisorClass_class_schedule_days		= $advisorRow->class_schedule_days; 
					$advisorClass_class_schedule_times 		= $advisorRow->class_schedule_times; 
					$advisorClass_class_schedule_days_utc 	= $advisorRow->class_schedule_days_utc; 
					$advisorClass_class_schedule_times_utc 	= $advisorRow->class_schedule_times_utc; 
					$advisorClass_class_incomplete 			= $advisorRow->class_incomplete;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

					if ($doDebug) {
						echo "<br />Processing $advisor_call_sign<br />";
					}
					if ($advisorClass_class_incomplete == 'Y') {
						$errorArray[]						= "Advisor $advisor_call_sign Class $advisorClass_sequence class_incomplete is $advisorClass_class_incomplete<br />";
					}
					if ($doDebug) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;Doing class $advisorClass_sequence<br />";
					}
					if ($advisor_call_sign != $prevAdvisor) {
						$advisorDataArray[$advisor_call_sign]['name']		= "$advisor_last_name, $advisor_first_name";
						$advisorDataArray[$advisor_call_sign]['email']		= $advisor_email;
						$advisorDataArray[$advisor_call_sign]['phone']		= $advisor_phone;
						$advisorDataArray[$advisor_call_sign]['ph_code']	= $advisor_ph_code;
						$advisorDataArray[$advisor_call_sign]['city']		= $advisor_city;
						$advisorDataArray[$advisor_call_sign]['state']		= $advisor_state;
						$advisorDataArray[$advisor_call_sign]['country']	= $advisor_country;
						$advisorDataArray[$advisor_call_sign]['tz']			= "$advisor_timezone_id $advisor_timezone_offset";
						$prevAdvisor		= $advisor_call_sign;
					}

					$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['level']				= $advisorClass_level;
					$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['class size']		= $advisorClass_class_size;
					$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['schedule utc']		= "$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc";
					$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['schedule local']	= "$advisorClass_class_schedule_times $advisorClass_class_schedule_days";
					$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['count']				= 0;
					$advisorClassArray[$advisor_call_sign][$advisorClass_sequence]['incomplete']		= $advisorClass_class_incomplete;
				}
			} else {
				if ($doDebug) {
					echo "No records found in $advisorTableName, $advisorClassTableName that met the criteria<br />";
				}
				$content		.= "No records found in $advisorTableName, $advisorClassTableName that met the criteria<br />";
				$goOn			= FALSE;
			}
			if ($doDebug) {			/// dump the two arrays
				echo "<br />Done with the advisors.<br /><br />advisor data array:<br /><pre>";
				ksort($advisorDataArray);
				print_r($advisorDataArray);
				echo "</pre><br />";
			
				echo "<br />advisor class array:<br /><pre>";
				ksort($advisorClassArray);
				print_r($advisorClassArray);
				echo "</pre><br />";
			}

			if ($goOn) {		
				//////// 	get all the students who are assigned to an advisor and
				////////	get a count of the number of students in a class	

				if ($doDebug) {
					echo "<br />getting the student counts<br />";
				}
				$sql			 	= "select * from $studentTableName 
										where semester='$theSemester' 
										and response='Y'
										 and (student_status='Y' or student_status='S') order 
										 by call_sign";
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
							$student_abandoned  				= $studentRow->abandoned;
							$student_student_status  				= strtoupper($studentRow->student_status);
							$student_action_log  					= stripslashes($studentRow->action_log);
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
					
							if ($doDebug) {
								echo "<br />Processing student $student_call_sign<br />";
							}
							if (isset($advisorClassArray[$student_assigned_advisor][$student_assigned_advisor_class]['count'])) {
								$advisorClassArray[$student_assigned_advisor][$student_assigned_advisor_class]['count']++;
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp;&nbsp;incremented advisorClassArray |$student_assigned_advisor|$student_assigned_advisor_class|'count'<br />";
								}
							} else {
								if ($doDebug) {
									echo "$student_assigned_advisor|$student_assigned_advisor_class|'count' doesn't exist in advisorClassArray<br />";
								}
							}
						}				/// end of student while
					} else {			/// no students?
						if ($doDebug) {
							echo "No records found in $studentTableName table<br />";
						}
					}
				}

				///// prepare the report
				$content		.= "<table style='width:1000;'>
									<tr><th>Advisor</th>
										<th colspan='2'>Name</th>
										<th>State</th>
										<th>Country</th>
										<th colspan='2'>Email</th>
										<th>Phone</th>
										<th>TZ</th></tr>";

				$prevAdvisor	= '';
				$firstTime		= TRUE;
				$firstSubTitle	= TRUE;
				ksort($advisorDataArray);
				foreach($advisorDataArray as $thisAdvisor=>$myValue) {
					if ($thisAdvisor != $prevAdvisor) {
						if ($firstTime) {
							$firstTime	= FALSE;
						} else {
							$content	.= "<tr><td colspan='9'><hr></td></tr>";
						}
						$prevAdvisor	= $thisAdvisor;
						$thisName		= $advisorDataArray[$thisAdvisor]['name'];
						$thisState		= $advisorDataArray[$thisAdvisor]['state'];
						$thisCountry	= $advisorDataArray[$thisAdvisor]['country'];
						$thisEmail		= $advisorDataArray[$thisAdvisor]['email'];
						$thisPhone		= $advisorDataArray[$thisAdvisor]['phone'];
						$thisPh_code	= $advisorDataArray[$thisAdvisor]['ph_code'];
						$thisTZ			= $advisorDataArray[$thisAdvisor]['tz'];
						$content		.= "<tr><td style='vertical-align:top;'>$thisAdvisor</td>
												<td colspan='2' style='vertical-align:top;'>$thisName</td>
												<td style='vertical-align:top;'>$thisState</td>
												<td style='vertical-align:top;'>$thisCountry</td>
												<td colspan='2' style='vertical-align:top;'>$thisEmail</td>
												<td style='vertical-align:top;'>(+$thisPh_code) $thisPhone</td>
												<td style='vertical-align:top;'>$thisTZ</td></tr>";
					}
					for($ii=1;$ii<=6;$ii++) {
						if (isset($advisorClassArray[$thisAdvisor][$ii])) {
							$thisLevel		= $advisorClassArray[$thisAdvisor][$ii]['level'];
							$thisClassSize	= $advisorClassArray[$thisAdvisor][$ii]['class size'];
							$thisCount		= $advisorClassArray[$thisAdvisor][$ii]['count'];
							$thisSkedUTC	= $advisorClassArray[$thisAdvisor][$ii]['schedule utc'];
							$thisSkedLocal	= $advisorClassArray[$thisAdvisor][$ii]['schedule local'];
	
							if ($firstSubTitle) {
								$firstSubTitle	= FALSE;
								$content	.= "<tr><td style='vertical-align:top;'><b>Class</b></td>
													<td style='vertical-align:top;'><b>Level</b></td>
													<td style='vertical-align:top;text-align:center;'><b>Class Size</b></td>
													<td style='vertical-align:top;text-align:center;'><b>Nmbr Students</b></td>
													<td style='vertical-align:top;text-align:center;'><b>Available</b></td>
													<td colspan='2' style='vertical-align:top;'><b>UTC Class Schedule</b></td>
													<td colspan='2' style='vertical-align:top;'><b>Local Class Schedule</b></td></tr>";
							}
							$thisInt		= $thisClassSize - $thisCount;
							$content		.= "<tr><td style='vertical-align:top;'>$ii</td>
													<td style='vertical-align:top;'>$thisLevel</td>
													<td style='vertical-align:top;text-align:center;'>$thisClassSize</td>
													<td style='vertical-align:top;text-align:center;'>$thisCount</td>
													<td style='vertical-align:top;text-align:center;'>$thisInt</td>
													<td colspan='2' style='vertical-align:top;'>$thisSkedUTC</td>
													<td colspan='2' style='vertical-align:top;'>$thisSkedLocal</td></tr>";
							$totalClassSize	= $totalClassSize + $thisClassSize;
							$totalStudents	= $totalStudents + $thisCount;
							$totalClasses++;
							if ($thisInt > 0) {
								$availArray[]	= "$thisAdvisor|$thisLevel|$ii|$thisInt";
								if ($doDebug) {
									echo "$thisAdvisor class $ii has $thisInt seats avaiable<br />";
								}
							}
						}
					}
				}
				$content		.= "</table>
									Total Class Size: $totalClassSize<br />
									Total Students: $totalStudents<br />
									Total Number of Classes: $totalClasses<br />
									<br /><br /><b>Advisors with Seats Available</b><table style='width:450px;'>
									<tr><th style='width:150px;'>Advisor</th>
										<th style='width:100px;'>Level</th>
										<th style='width:100px; text-align:center;'>Class</th>
										<th style='width:100px; text-align:center;'>Seats</th></tr>";
		
				sort($availArray);
				foreach($availArray as $myValue) {
					$myArray		= explode("|",$myValue);
					$thisAdvisor	= $myArray[0];
					$thisLevel		= $myArray[1];
					$thisClass		= $myArray[2];
					$thisAvail		= $myArray[3];
					$totalAvail		= $totalAvail + $thisAvail;			
					$content		.= "<tr><td>$thisAdvisor</td>
											<td>$thisLevel</td>
											<td style='text-align:center;'>$thisClass</td>
											<td style='text-align:center;'>$thisAvail</td></tr>";
				}
				$content			.= "</table>
										$totalAvail: Seats available<br /><br />";


				if (count($errorArray) > 0) {
					$content		.= "<br /><b>ERRORS</b><br />";
					foreach($errorArray as $myValue) {
						$content	.= $myValue;
					}
				}
			}
		}
	}
	
	$thisTime 		= date('Y-m-d H:i:s',$currentTimestamp);
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d',$currentTimestamp);
	$nowTime		= date('H:i:s',$currentTimestamp);
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('advisor_class_report', 'advisor_class_report_func');
