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
	Modified 30Oct24 by Roland for new database
		
*/

	global 	$wpdb, $testMode, $doDebug;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$validUser 						= $context->validUser;
	$validTestmode					= $context->validTestmode;
	$userName						= $context->userName;
	$siteURL						= $context->siteurl;	
	$currentSemester				= $context->currentSemester;	
	$nextSemester					= $context->nextSemester;	
	
	$proximateSemester				= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$proximateSemester			= $nextSemester;
	}
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
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
	$userName					= $context->userName;
	$theURL						= "$siteURL/cwa-update-unassigned-student-information/";
	$haveInpCallsign			= FALSE;
	$haveInpID					= FALSE;
	$jobname					= 'Update Unassigned Student Info';
	$inp_class_priority			= '';

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
				$haveInpCallsign	= TRUE;
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
				$haveInpID		= TRUE;
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
	

	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$studentTableName		= "wpw1_cwa_student2";
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		$catalogMode			= "TestMode";
	} else {
		$studentTableName		= "wpw1_cwa_student";
		$catalogMode			= "Production";
		$userMasterTableName	= 'wpw1_cwa_user_master';
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

	$haveStudentData		= FALSE;
	$goAhead				= FALSE;
	if ($haveInpID) {
		$sql		= "select * from $studentTableName 
					left join $userMasterTableName on user_call_sign = student_call_sign 
					where student_id = $inp_id"; 
		$goAhead	= TRUE;
	}
	if ($haveInpCallsign && !$haveInpID) {
		$sql		= "select * from $studentTableName 
						left join $userMasterTableName on user_call_sign = student_call_sign 
						where student_call_sign = '$inp_callsign' 
						and student_semester = '$proximateSemester'"; 
		$goAhead	= TRUE;
	}
	if ($goAhead) {
		$wpw1_cwa_student	= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows									= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
				}
				if ($numSRows > 0) {
					$haveStudentData						= TRUE;
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_master_ID 					= $studentRow->user_ID;
						$student_master_call_sign 			= $studentRow->user_call_sign;
						$student_first_name 				= $studentRow->user_first_name;
						$student_last_name 					= $studentRow->user_last_name;
						$student_email 						= $studentRow->user_email;
						$student_ph_code 					= $studentRow->user_ph_code;
						$student_phone 						= $studentRow->user_phone;
						$student_city 						= $studentRow->user_city;
						$student_state 						= $studentRow->user_state;
						$student_zip_code 					= $studentRow->user_zip_code;
						$student_country_code 				= $studentRow->user_country_code;
						$student_country 					= $studentRow->user_country;
						$student_whatsapp 					= $studentRow->user_whatsapp;
						$student_telegram 					= $studentRow->user_telegram;
						$student_signal 					= $studentRow->user_signal;
						$student_messenger 					= $studentRow->user_messenger;
						$student_master_action_log 			= $studentRow->user_action_log;
						$student_timezone_id 				= $studentRow->user_timezone_id;
						$student_languages 					= $studentRow->user_languages;
						$student_survey_score 				= $studentRow->user_survey_score;
						$student_is_admin					= $studentRow->user_is_admin;
						$student_role 						= $studentRow->user_role;
						$student_master_date_created 		= $studentRow->user_date_created;
						$student_master_date_updated 		= $studentRow->user_date_updated;
	
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= $studentRow->student_call_sign;
						$student_time_zone  					= $studentRow->student_time_zone;
						$student_timezone_offset				= $studentRow->student_timezone_offset;
						$student_youth  						= $studentRow->student_youth;
						$student_age  							= $studentRow->student_age;
						$student_parent 				= $studentRow->student_parent;
						$student_parent_email  					= strtolower($studentRow->student_parent_email);
						$student_level  						= $studentRow->student_level;
						$student_waiting_list 					= $studentRow->student_waiting_list;
						$student_request_date  					= $studentRow->student_request_date;
						$student_semester						= $studentRow->student_semester;
						$student_notes  						= $studentRow->student_notes;
						$student_welcome_date  					= $studentRow->student_welcome_date;
						$student_email_sent_date  				= $studentRow->student_email_sent_date;
						$student_email_number  					= $studentRow->student_email_number;
						$student_response  						= strtoupper($studentRow->student_response);
						$student_response_date  				= $studentRow->student_response_date;
						$student_abandoned  					= $studentRow->student_abandoned;
						$student_status  						= strtoupper($studentRow->student_status);
						$student_action_log  					= $studentRow->student_action_log;
						$student_pre_assigned_advisor  			= $studentRow->student_pre_assigned_advisor;
						$student_selected_date  				= $studentRow->student_selected_date;
						$student_no_catalog  					= $studentRow->student_no_catalog;
						$student_hold_override  				= $studentRow->student_hold_override;
						$student_assigned_advisor  				= $studentRow->student_assigned_advisor;
						$student_advisor_select_date  			= $studentRow->student_advisor_select_date;
						$student_advisor_class_timezone 		= $studentRow->student_advisor_class_timezone;
						$student_hold_reason_code  				= $studentRow->student_hold_reason_code;
						$student_class_priority  				= $studentRow->student_class_priority;
						$student_assigned_advisor_class 		= $studentRow->student_assigned_advisor_class;
						$student_promotable  					= $studentRow->student_promotable;
						$student_excluded_advisor  				= $studentRow->student_excluded_advisor;
						$student_survey_completion_date	= $studentRow->student_survey_completion_date;
						$student_available_class_days  			= $studentRow->student_available_class_days;
						$student_intervention_required  		= $studentRow->student_intervention_required;
						$student_copy_control  					= $studentRow->student_copy_control;
						$student_first_class_choice  			= $studentRow->student_first_class_choice;
						$student_second_class_choice  			= $studentRow->student_second_class_choice;
						$student_third_class_choice  			= $studentRow->student_third_class_choice;
						$student_first_class_choice_utc  		= $studentRow->student_first_class_choice_utc;
						$student_second_class_choice_utc  		= $studentRow->student_second_class_choice_utc;
						$student_third_class_choice_utc  		= $studentRow->student_third_class_choice_utc;
						$student_catalog_options				= $studentRow->student_catalog_options;
						$student_flexible						= $studentRow->student_flexible;
						$student_date_created 					= $studentRow->student_date_created;
						$student_date_updated			  		= $studentRow->student_date_updated;
					}
				} else {
					$haveStudentData		= FALSE;
				}
			}
		}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Function starting.<br />";
		}
		$content 		.= "<h3>$jobname</h3>
							<p>This function allows you to update an unassigned student's class priority and/or the student's class choices.</p>
							<p>Enter the student's call sign and click submit.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px;'>Student Call Sign</td>
								<td><input type='text' class='formInputText' name='inp_callsign' size='10' maxlength='20' autofocus></td></tr>
							$testModeOption
							<tr><td>&nbps;</td>
								<td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo  "<br />Arrived at pass 2 with $inp_callsign<br />";
		}
		
		$content			.= "<h3>$jobname</h3>";
		
		if ($inp_callsign == '') {
			$content	.= "Invalid Input";
		} else {
			if ($haveStudentData) {
				if ($doDebug) {
					echo "have the student data<br />";
				}
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
					echo "<br />Retrieved $student_call_sign<br />
							Level: $student_level<br />
							Class Priority: $student_class_priority<br />
							Student Status: $student_status<br />
							First Class Choice: $student_first_class_choice<br />
							First Class Choice UTC: $student_first_class_choice_utc<br />
							Second Class Choice: $student_second_class_choice<br />
							Second Class Choice UTC: $student_second_class_choice_utc<br />
							Third Class Choice: $student_third_class_choice<br />
							Third Class Choice UTC: $student_third_class_choice_utc<br />";
				}
				if ($student_status != '') {
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
					$classArray	= generateClassTimes($student_timezone_offset,$student_level,$student_semester,'all',$doDebug,$catalogMode);

					$content			.= "<p>Enter the desired changes and press 'Submit'.</p>
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
						$classLanguage		= $classSked[0];
						$classStartUTC		= $classSked[1];
						$classDaysUTC		= $classSked[2];
						$newStart			= $classSked[3];
						$newDays			= $classSked[4];
						$classCount			= intval($classSked[5]);
					
						$schedule		 	= "$newStart $newDays|$classStartUTC $classDaysUTC";
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
														<td>$timeBlock $classLanguage</td></tr>";
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
			} else {
				$content	.= "No record found in $studentTableName pod by call sign $inp_callsign";
			}	
		}

	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 3 with $inp_callsign ($inp_id), $inp_class_priority, $inp_sked1, $inp_sked2, $inp_sked3<br />";
		}

		$content			.= "<h3>$jobname</h3>";
		// get the student record so we can see what needs to be updated
		if ($haveStudentData) {	
			if ($doDebug) {
				echo "Retreived $student_call_sign. Level: $student_level; Class Priority: $student_class_priority; Student Status: $student_status<br />";
			}
			if ($student_call_sign == $inp_callsign) {
				if ($student_class_priority == '') {
					if ($inp_class_priority == '') {
						$inp_class_priority = '0';
					}
				}
				$doUpdate						= FALSE;
				$logData						= '';
				$updateFormat					= array();
				
				if ($inp_sked1 != 'None') {
					$sked1array		= explode('|',$inp_sked1);   
					$sked1Local		= $sked1array[0];
					$sked1UTC		= $sked1array[1];
				}
				if ($inp_sked2 != 'None') {
					$sked2array		= explode('|',$inp_sked2);   
					$sked2Local		= $sked2array[0];
					$sked2UTC		= $sked2array[1];
				}
				if ($inp_sked3 != 'None') {
					$sked3array		= explode('|',$inp_sked3);   
					$sked3Local		= $sked3array[0];
					$sked3UTC		= $sked3array[1];
				}
				if ($inp_class_priority != $student_class_priority) {
					$doUpdate	= TRUE;
					$updateArray[]				= "student_class_priority|$inp_class_priority|d";
					$logData					.= "student_class priority changed from $student_class_priority to $inp_class_priority ";
				}
				if ($student_first_class_choice != $sked1Local) {
					$doUpdate	= TRUE;
					$updateArray[]				= "student_first_class_choice|$sked1Local|s";
					$logData					.= "student_first_class_choice changed from $student_first_class_choice to $inp_sked1 ";
				}
				if ($student_first_class_choice_utc != $sked1UTC) {
					$doUpdate	= TRUE;
					$updateArray[]				= "student_first_class_choice_utc|$sked1UTC|s";
					$logData					.= "student_first_class_choice changed from $student_first_class_choice to $inp_sked1 ";
				}
				if ($student_second_class_choice != $sked2Local) {
					$doUpdate	= TRUE;
					$updateArray[]				= "student_second_class_choice|$sked2Local|s";
					$logData					.= "student_second_class_choice changed from $student_second_class_choice to $sked2Local ";
				}
				if ($student_second_class_choice_utc != $sked2UTC) {
					$doUpdate	= TRUE;
					$updateArray[]				= "student_second_class_choice_utc|$sked2UTC|s";
					$logData					.= "student_second_class_choice changed from $student_second_class_choice to $sked2UTC ";
				}
				if ($student_third_class_choice != $sked3Local) {
					$doUpdate	= TRUE;
					$updateArray[]				= "student_third_class_choice|$sked3Local|s";
					$logData					.= "student_third_class_choice changed from $student_third_class_choice to $sked3Local ";
				}
				if ($student_third_class_choice_utc != $sked3UTC) {
					$doUpdate	= TRUE;
					$updateArray[]				= "student_third_class_choice_utc|$sked3UTC|s";
					$logData					.= "student_third_class_choice changed from $student_third_class_choice to $sked3UTC ";
				}
				if ($doUpdate) {
					$student_action_log			.= " / $actionDate MGMTINFO $logData ";
					$updateArray[]				= "student_action_log|$student_action_log|s";
					$studentUpdateData		= array('tableName'=>$studentTableName,
													'inp_method'=>'update',
													'inp_data'=>$updateArray,
													'inp_format'=>array(''),
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
					} else {
						$content		.= "Update completed successfully<br />";
					}
				} else {
					$content					.= "<h3>Update Unassigned Student Info for $inp_callsign</h3>
													<p>No changes requested and none performed.</p>";	
				}
			} else {
				$content	.= "Record at id $inp_id in $studentTableName table has a call sign of $student_call_sign which doesn't match $inp_callsign.";
			}
		} else {
			$content	.= "Couldn't find a record for $inp_callsign at id $inp_id to update.";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('update_unassigned_student_info', 'update_unassigned_student_info_func');

