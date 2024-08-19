function update_unassigned_student_info_func() {

/*	Update Unassigned Student Info
 *	
 *	This function is part of Student Management
 
	Function allows the following information to be changed:
 		The student class priority
 		The first, second, and third class choice
 	of an unassigned student to be modified

	Modified 21June2021 by Roland to allow class choices to be changed and to incorporate
		the audit log v2 process
	Modified 17Apr23 by Roland to fix action_log
	Modified 16Jul23 by Roland to use consolidated tables
		
*/

	global 	$wpdb, $testMode, $doDebug;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 						= $initializationArray['validUser'];
	$validTestmode					= $initializationArray['validTestmode'];
	$userName						= $initializationArray['userName'];
	$siteURL						= $initializationArray['siteurl'];	
	$currentSemester				= $initializationArray['currentSemester'];	
	$nextSemester					= $initializationArray['nextSemester'];	
	
	$proximateSemester				= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$proximateSemester			= $nextSemester;
	}
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$inp_callsign				= '';
	$actionDate					= date('dMY H:i');
	$logDate					= date('Y-m-d');
	$fieldTest					= array('action_log','post_status','post_title','control_code');
	$userName					= $initializationArray['userName'];
	$theURL						= "$siteURL/cwa-update-unassigned-student-information/";

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
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_level") {
				$inp_level		 = $str_value;
				$inp_level		 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_class_priority") {
				$inp_class_priority	 = $str_value;
				$inp_class_priority	 = filter_var($inp_class_priority,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_id") {
				$inp_id	 		= $str_value;
				$inp_id	 		= filter_var($inp_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_sked1") {
				$inp_sked1	 		= $str_value;
				$inp_sked1	 		= filter_var($inp_sked1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_sked2") {
				$inp_sked2	 		= $str_value;
				$inp_sked2	 		= filter_var($inp_sked2,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_sked3") {
				$inp_sked3	 		= $str_value;
				$inp_sked3	 		= filter_var($inp_sked3,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_mode") {
				$inp_mode			 = $str_value;
				$inp_mode			 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode		= TRUE;
				}
			}
			if ($str_key 			== "inp_verbose") {
				$inp_verbose			 = $str_value;
				$inp_verbose			 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug		= TRUE;
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
		$studentTableName		= "wpw1_cwa_consolidated_student2";
		$catalogMode			= "TestMode";
	} else {
		$studentTableName		= "wpw1_cwa_consolidated_student";
		$catalogMode			= "Production";
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
		$content 		.= "<h3>Update Unassigned Student Info</h3>
<p>This function allows you to update an unassigned student's class priority and/or the student's class choices.</p>
<p>Enter the student's call sign and click submit.</p>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data''>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td style='width:150px;'>Student Call Sign</td><td>
	<input type='text' class='formInputText' name='inp_callsign' size='10' maxlength='20' autofocus></td></tr>
$testModeOption
	<td>&nbps;</td>
	<td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo  "Arrived at pass 2 with $inp_callsign<br />";
		}
		if ($inp_callsign == '') {
			$content	.= "Invalid Input";
		} else {
			$sql		= "select * from $studentTableName 
							where call_sign='$inp_callsign' 
							and semester = '$proximateSemester";
			$wpw1_cwa_student				= $wpdb->get_results($sql);
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
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_first_name						= $studentRow->first_name;
						$student_last_name						= stripslashes($studentRow->last_name);
						$student_email  						= $studentRow->email;
						$student_phone  						= $studentRow->phone;
						$student_city  							= $studentRow->city;
						$student_state  						= $studentRow->state;
						$student_zip_code  						= $studentRow->zip_code;
						$student_country  						= $studentRow->country;
						$student_time_zone  					= $studentRow->time_zone;
						$student_wpm 	 						= $studentRow->wpm;
						$student_youth  						= $studentRow->youth;
						$student_age  							= $studentRow->age;
						$student_student_parent 				= $studentRow->student_parent;
						$student_student_parent_email  			= $studentRow->student_parent_email;
						$student_level  						= $studentRow->level;
						$student_start_time 					= $studentRow->start_time;
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
						$student_passed_over_count  			= $studentRow->passed_over_count;
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
					
						if ($student_class_priority == '') {
							$student_class_priority		= '0';
						}
						if ($student_first_class_choice_utc == '') {
							$student_first_class_choice_utc = 'None';
						}
						if ($student_second_class_choice_utc == '') {
							$student_second_class_choice_utc = 'None';
						}
						if ($student_third_class_choice_utc == '') {
							$student_third_class_choice_utc = 'None';
						}
						if ($doDebug) {
							echo "Retrieved $student_call_sign.<br />
									Level: $student_level;<br />
									Class Priority: $student_class_priority;
									Student Status: $student_student_status<br />
									First Class Choice: $student_first_class_choice<br />
									First Class Choice UTC: $student_first_class_choice_utc<br />
									Second Class Choice: $student_first_class_choice<br />
									Second Class Choice UTC: $student_first_class_choice_utc<br />
									Third Class Choice: $student_third_class_choice<br />
									Third Class Choice UTC: $student_third_class_choice_utc<br />";
						}
						if ($student_student_status != '') {
							$content		.= "Student $student_call_sign is not unassigned.";
							if ($doDebug) {
								echo "Student is not unassigned. Quitting<br />";
							}
						} else {
							// set up to display the update form
							$zeroChecked		= '';
							$oneChecked			= '';
							$twoChecked			= '';
							$threeChecked		= '';
							if ($student_class_priority == '0') {
								$zeroChecked	= 'checked';
							} elseif ($student_class_priority == '1') {
								$oneChecked		= 'checked';
							} elseif ($student_class_priority == '2') {
								$twoChecked		= 'checked';
							} elseif ($student_class_priority == '3') {
								$threeChecked	= 'checked';
							} else {
								$zeroChecked	= 'checked';
							}
							$begChecked			= '';
							$basChecked			= '';
							$intChecked			= '';
							$advChecked			= '';
							switch($student_level) {
								case 'Beginner':
									$begChecked	= "checked='checked'";
									break;
								case 'Fundamental':
									$basChecked	= "checked='checked'";
									break;
								case 'Intermediate':
									$intChecked	= "checked='checked'";
									break;
								case 'Advanced':
									$advChecked = "checked='checked'";
									break;
							}
							$classArray	= generateClassTimes($student_time_zone,$student_level,$student_semester,$doDebug,$catalogMode);



							$content			.= "<h3>Update Unassigned Student Info for $inp_callsign</h3>
													<p>Enter the desired changes and press 'Submit'.</p>
													<p><form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data''>
														<input type='hidden' name='strpass' value='3'>
														<input type='hidden' name='inp_callsign' value='$inp_callsign'>
														<input type='hidden' name='inp_id' value='$student_ID'>
														<input type='hidden' name='inp_mode' value='$inp_mode'>
													<table><tr>
														<th style='width:90ps;'>Item</th>
														<th>Current Value</th>
														<th>Updated Value</th>
													</tr><tr>
														<td>Semester</td><td>$student_semester</td><td>&nbsp;</td>
													</tr><tr>
														<td style='vertical-align:top;'>Level</td>
														<td style='vertical-align:top;'>$student_level</td>
														<td style='vertical-align:top;'>&nbsp;</td>
													</tr><tr>
														<td style='vertical-align:top;'>Class Priority</td>
														<td style='vertical-align:top;'>$student_class_priority</td>
														<td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_class_priority' value='0' $zeroChecked> 0<br />
																						<input type='radio' class='formInputButton' name='inp_class_priority' value='1' $oneChecked> 1<br />
																						<input type='radio' class='formInputButton' name='inp_class_priority' value='2' $twoChecked> 2<br />
																						<input type='radio' class='formInputButton' name='inp_class_priority' value='3 $threeChecked'> 3</td>
													</tr><tr>
														<td colspan='3'><p>The <b>$student_level</b> level in time zone <b>$student_time_zone</b> has potential classes in each of the 
													following time blocks on the teaching days indicated. The times and days are local time in $student_time_zone.  
													Classes are one hour in length and their starting time will be within the time block. <br /><br />The color codes 
													represent: <br /><span style='background-color:#A5D6A7;'>7 or more potential classes</span>
													<span style='background-color:#8DC0ED;'>5 to 6 potential classes</span> 
													<span style='background-color:#FFCC80;'>3 to 4 potential classes</span> 
													<span style='background-color:#EDEDC3;'>1 to 2 potential classes</span> <br />Please select first, second, and third 
													choice for the time block and teaching days.</p>
													<table>";

							///// format the schedule for display
							$firstChoiceTime	= "";
							$firstChoiceDays	= "";
							$secondChoiceTime	= "";
							$secondChoiceDays	= "";
							$thirdChoiceTime	= "";
							$thirdChoiceDays	= "";

							if ($student_first_class_choice_utc != 'None') {
								$myArray			= explode(" ",$student_first_class_choice_utc);
								$firstChoiceTime	= $myArray[0];
								$firstChoiceDays	= $myArray[1];
							}
							if ($student_second_class_choice_utc != 'None') {
								$myArray			= explode(" ",$student_second_class_choice_utc);
								$secondChoiceTime	= $myArray[0];
								$secondChoiceDays	= $myArray[1];
							}
							if ($student_third_class_choice_utc != 'None') {
								$myArray			= explode(" ",$student_third_class_choice_utc);
								$thirdChoiceTime	= $myArray[0];
								$thirdChoiceDays	= $myArray[1];
							}
						
							$secondCheckedYes		= FALSE;
							$thirdCheckedYes		= FALSE;
						
							if ($doDebug) {
								echo "<br />Checking student class choices:<br />
										First Choice UTC: $student_first_class_choice_utc<br />
										Second Choice UTC: $student_second_class_choice_utc<br />
										Third Choice UTC: $student_third_class_choice_utc<br />";
							}
							foreach ($classArray[$student_level] as $myValue) {
								if ($doDebug) {
									echo "<br />Schedule line: $myValue<br />";
								}
								$classSked			= explode("|",$myValue);
								$newStart			= $classSked[0];
								$newDays			= $classSked[1];
								$classCount			= intval($classSked[2]);
								$classStartUTC		= $classSked[3];
								$classDaysUTC		= $classSked[4];
							
								$schedule		 	= "$classStartUTC $classDaysUTC";
								$classStartUTC		= intval($classStartUTC);
								$classEndUTC		= $classStartUTC + 159;
							
								//// check first class choice
								$firstChecked		= '';
								if ($doDebug) {
									echo "checking $classStartUTC $classDaysUTC against $student_first_class_choice_utc<br />";
								}
								if ($firstChoiceDays == $classDaysUTC) {
									if ($firstChoiceTime >= $classStartUTC && $firstChoiceTime <= $classEndUTC) {
										$firstChecked	= 'checked';
										if ($doDebug) {
											echo "First Choice UTC $student_first_class_choice_utc matched start:$classStartUTC end:$classEndUTC<br />";
										}
									}
								}					
								//// check second class choice
								$secondChecked		= '';
								if ($doDebug) {
									echo "checking $classStartUTC $classDaysUTC against $student_second_class_choice_utc<br />";
								}
								if ($secondChoiceDays == $classDaysUTC) {
									if ($secondChoiceTime >= $classStartUTC && $secondChoiceTime <= $classEndUTC) {
										$secondChecked	= 'checked';
										$secondCheckedYes	= TRUE;
										if ($doDebug) {
											echo "Second Choice UTC $student_second_class_choice_utc matched start:$classStartUTC end:$classEndUTC<br />";
										}
									}
								}					
								//// check third class choice
								$thirdChecked		= '';
								if ($doDebug) {
									echo "checking $classStartUTC $classDaysUTC against $student_third_class_choice_utc<br />";
								}
								if ($thirdChoiceDays == $classDaysUTC) {
									if ($thirdChoiceTime >= $classStartUTC && $thirdChoiceTime <= $classEndUTC) {
										$thirdChecked	= 'checked';
										$thirdCheckedYes	= TRUE;
										if ($doDebug) {
											echo "Third Choice UTC $student_third_class_choice_utc matched start:$classStartUTC end:$classEndUTC<br />";
										}
									}
								}					

								/// figure out the color of the time block
								$myStr				= str_pad($classStartUTC,4,0,STR_PAD_LEFT);
								$timeBlock			= "$newStart $newDays Local ($myStr $classDaysUTC UTC)";
								if ($classCount > 7) {
									$timeBlock			= "<span style='background-color:#A5D6A7;'>$newStart $newDays Local ($myStr $classDaysUTC UTC)</span>";
								} elseif ($classCount >= 5 && $classCount <= 6) {
									$timeBlock			= "<span style='background-color:#8DC0ED;'>$newStart $newDays Local ($myStr $classDaysUTC UTC)</span>";
								} elseif ($classCount >= 3 && $classCount <= 4) {
									$timeBlock			= "<span style='background-color:#FFCC80;'>$newStart $newDays Local ($myStr $classDaysUTC UTC)</span>";
								} elseif ($classCount >= 1 && $classCount <= 2) {
									$timeBlock			= "<span style='background-color:#EDEDC3;'>$newStart $newDays Local ($myStr $classDaysUTC UTC)</span>";
								}
								$content				.= "<tr><td><input type='radio' class='formInputButton' name='inp_sked1' value='$schedule' $firstChecked></td>
																<td><input type='radio' class='formInputButton' name='inp_sked2' value='$schedule' $secondChecked></td>
																<td><input type='radio' class='formInputButton' name='inp_sked3' value='$schedule' $thirdChecked></td>
																<td>$timeBlock</td></tr>";
							}
							$secondNoneChecked			= "";
							if (!$secondCheckedYes) {
								$secondNoneChecked		= 'checked';
							}
							$thirdNoneChecked			= "";
							if (!$thirdCheckedYes) {
								$thirdNoneChecked		= 'checked';
							}
							$content					.= "<tr><td>&nbsp;</td>
																<td><input type='radio' class='formInputButton' name='inp_sked2' value='None' $secondNoneChecked></td>
																<td><input type='radio' class='formInputButton' name='inp_sked3' value='None' $thirdNoneChecked></td>
																<td>No Selection</td></tr></table></td>
															</tr><tr>
															<td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td>
															</tr></table>
															</form></p>";

						}
					}
				} else {
					$content	.= "No record found in $studentTableName pod by call sign $inp_callsign";
				}	
			} else {
				if ($doDebug) {
					echo "Either $studentTableName table not found or bad $sql 01<br />";
				}
				$content		.= "Either $studentTableName table not found or bad $sql 01<br />";
			}	
		}

	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "At pass 3 with $inp_callsign ($inp_id), $inp_class_priority, $inp_sked1, $inp_sked2, $inp_sked3<br />";
		}
	
		// get the student record so we can see what needs to be updated
		$sql		= "select * from $studentTableName 
						where student_id='$inp_id'";
		$wpw1_cwa_student				= $wpdb->get_results($sql);
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
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= $studentRow->email;
					$student_phone  						= $studentRow->phone;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_time_zone  					= $studentRow->time_zone;
					$student_wpm 	 						= $studentRow->wpm;
					$student_youth  						= $studentRow->youth;
					$student_age  							= $studentRow->age;
					$student_student_parent 				= $studentRow->student_parent;
					$student_student_parent_email  			= $studentRow->student_parent_email;
					$student_level  						= $studentRow->level;
					$student_start_time 					= $studentRow->start_time;
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
					$student_passed_over_count  			= $studentRow->passed_over_count;
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
				
					if ($doDebug) {
						echo "Retreived $student_call_sign. Level: $student_level; Class Priority: $student_class_priority; Student Status: $student_student_status<br />";
					}
					if ($student_call_sign = $inp_callsign) {
						if ($student_class_priority == '') {
							$student_class_priority = '0';
						}
						$doUpdate						= FALSE;
						$logData						= '';
						$updateFormat					= array();
						if ($inp_class_priority != $student_class_priority) {
							$doUpdate	= TRUE;
							$updateParams['class_priority'] = $inp_class_priority;
							$updateArray[]				= "class_priority|$inp_class_priority|d";
							$logData					.= "class priority changed from $student_class_priority to $inp_class_priority&nbsp;&nbsp;&nbsp;";
						}
						if ($student_first_class_choice != $inp_sked1) {
							$doUpdate	= TRUE;
							$updateParams['first_class_choice']	= $inp_sked1;
							$updateArray[]				= "first_class_choice|$inp_sked1|s";
							$logData					.= "first_class_choice changed from $student_first_class_choice to $inp_sked1&nbsp;&nbsp;&nbsp; ";
						}
						if ($student_second_class_choice != $inp_sked2) {
							$doUpdate	= TRUE;
							$updateParams['second_class_choice']	= $inp_sked2;
							$updateArray[]				= "second_class_choice|$inp_sked2|s";
							$logData					.= "second_class_choice changed from $student_second_class_choice to $inp_sked2&nbsp;&nbsp;&nbsp; ";
						}
						if ($student_third_class_choice != $inp_sked3) {
							$doUpdate	= TRUE;
							$updateParams['third_class_choice']	= $inp_sked3;
							$updateArray[]				= "third_class_choice|$inp_sked3|s";
							$logData					.= "third_class_choice changed from $student_third_class_choice to $inp_sked3&nbsp;&nbsp;&nbsp; ";
						}
						if ($doUpdate) {
							$student_action_log			= "$student_action_log / $actionDate MGMTINFO $logData ";
							$updateParams['action_log']	= $student_action_log;
							$updateArray[]				= "third_class_choice|$inp_sked3|s";

							$needComma = FALSE;
							$sql 						= "update $studentTableName set ";
							foreach($updateArray as $myValue) {
								$myArray 				= explode("|",$myValue);
								$field 					= $myArray[0];
								$fieldValue 			= $myArray[1];
								$fieldFormat		 	= $myArray[2];
								if ($needComma) {
									$sql 				.= ", ";
								}
								$sql 					.= "$field=";
								if ($fieldFormat == "s") {
									$sql 				.= "'$fieldValue'";
								} else {
									$sql 				.= "$fieldValue";
								}
								$needComma = TRUE;
							}
							$sql						.= " where student_id=$student_ID";

							if ($doDebug) {
								echo "SQL: $sql <br />";
							}
							$result						= $wpdb->query($sql);
							if ($result === FALSE) {
								if ($doDebug) {
										echo "Updating $student_call_sign record at $student_ID failed<br />
												Result: $result<br />
												wpdb->last_query: " . $wpdb->last_query . "<br />";
									if ($wpdb->last_error != '') {
										echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
									}
								}
							} else {
								if ($doDebug) {
									echo "Successfully updated $student_call_sign record at $student_ID<br />
											Result: $result<br />
											wpdb->last_query: " . $wpdb->last_query . "<br />";
									if ($wpdb->last_error != '') {
										echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
									}

								}

								$content					.= "<h3>Update Unassigned Student Info for $inp_callsign</h3>
																<p>Requested updates performed:<br />$logData</p>";
								// write the student flat log record
								if ($testMode) {
									$log_mode		= 'testMode';
									$log_file		= 'TestStudent';
								} elseif ($demoMode) {
									$log_mode		= 'Demo';
									$log_file		= 'DemoStudent';
								} else {
									$log_mode		= 'Production';
									$log_file		= 'Student';
								}
								$submitArray		= array('logtype'=>$log_file,
															'logmode'=>$log_mode,
															'logaction'=>'UPDATE',
															'logsubtype'=>'Student',
															'logdate'=>$logDate,
															'logprogram'=>'MGMTINFO',
															'logwho'=>$userName,
															'student_ID'=>$student_ID,
															'logsemester'=>$student_semester,
															'call_sign'=>$student_call_sign,
															'logid'=>$student_ID,
															'logcallsign'=>$student_call_sign,
															'first_name'=>$student_first_name,
															'last_name'=>$student_last_name);
								foreach($updateParams as $myKey=>$myValue) {
									if (!in_array($myKey,$fieldTest)) {
										$submitArray[$myKey]	= $myValue;
									}
								}
								$result		= storeAuditLogData_v3($submitArray);
								if ($result[0] === FALSE) {
									if ($doDebug) {
										echo "storeAuditLogData failed: $result[1]<br />";
									}
								
								}
							}
						} else {
							$content					.= "<h3>Update Unassigned Student Info for $inp_callsign</h3>
															<p>No changes requested and none performed.</p>";	
						}
					} else {
						$content	.= "Record at id $inp_id in $studentTableName table has a call sign of $student_call_sign which doesn't match $inp_callsign.";
					}
				}
			} else {
				$content	.= "Couldn't find a record for $inp_callsign at id $inp_id to update.";
			}
		} else {
			if ($doDebug) {
				echo "Either $studentTableName table not found or bad $sql 02<br />";
			}
			$content		.= "Either $studentTableName table not found or bad $sql 01<br />";
		}	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>To update another student's info, click <a href='$siteURL/cw-academy-update-unassigned-student-info/'>here</a><br /><br />
						To return to Student Management, click <a href='$siteURL/cw-academy-student-management/'>here</a>
						<br /><br /><p>Prepared at $thisTime</p>";
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
	$result			= write_joblog_func("Update Unassigned Student Info|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('update_unassigned_student_info', 'update_unassigned_student_info_func');
