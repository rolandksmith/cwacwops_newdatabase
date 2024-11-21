function promotable_students_repeating_func() {

/* 	generates a list of current semester students who have taken the same 
	level class in the past, were promotable, and have signed up for the 
	same level class again
	
	created 21Aug2021 by Roland
	Modified 19Jan2022 by Roland to use tables rather than pods
	Modified 1Oct22 by Roland for new timezone process
	Modified 16Apr23 by Roland to fix action_log
	Modified 13Jul23 by Roland to use consolidated tables
	Modified 24Oct24 by Roland for new database

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
	$currentSemester  	= $initializationArray['currentSemester'];
	$nextSemester	  	= $initializationArray['nextSemester'];
	$siteURL			= $initializationArray['siteurl'];
	$pastSemestersArray	= $initializationArray['pastSemesters'];
	$theSemester		= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$theSemester	= $nextSemester;
	}
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$countable					= 0;
	$theURL						= "$siteURL/cwa-promotable-students-repeating/";
	$studentHistoryURL			= "$siteURL/cwa-show-detailed-history-for-student/";
	$theSemester				= $initializationArray['proximateSemester'];
	$pastSemestersArray			= $initializationArray['pastSemestersArray'];
	$nextStudents				= array();
	$countBeginner				= 0;
	$countFundamental			= 0;
	$countIntermediate			= 0;
	$countAdvanced				= 0;
	$jobname					= "Promotable Students Repeating";

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
				$inp_semester		 = $str_value;
				$inp_semester		 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
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
		$studentTableName				= "wpw1_cwa_student2";
		$userMasterTableName			= 'wpw1_cwa_user_master2';
	} else {
		$studentTableName				= "wpw1_cwa_student";
		$userMasterTableName			= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$optionList		= "";
		foreach($pastSemestersArray as $theValue) {
			$optionList	.= "<input type='radio' class='formInputButton' name='inp_semester' value='$theValue'>$theValue<br />";
		}
		
		$content 		.= "<h3>$jobname</h3>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px;vertical-align:top;'>Semester of Interest</td>
								<td>$optionList</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2 with inp_semester of $inp_semester<br />";
		}
		
		$checkSemester				= array();
		$ii							= -1;
		$jj							= 0;
		$numSemesters				= count($pastSemestersArray) -1;
		if ($doDebug) {
			echo "pastSemestersArray:<br /><pre>";
			print_r($pastSemestersArray);
			echo "</pre><br />numSemesters: $numSemesters (plus 1)<br />";
		}
		foreach($pastSemestersArray as $thisSemester) {
			$ii++;
			$jj++;
			if ($doDebug) {
				echo "Processing $thisSemester. ii: $ii; jj: $jj<br />";
			}
			if ($ii < $numSemesters) {
				$checkSemester[$thisSemester]	= $pastSemestersArray[$jj];
			} else {
				$checkSemester[$thisSemester]	= $theSemester;
			}
		}
		if ($doDebug) {
			echo "checkSemester array:<br /><pre>";
			print_r($checkSemester);
			echo "</pre><br />";
		}
		
		$newSemester				= $checkSemester[$inp_semester];
		if ($doDebug) {
			echo "Preparing info for $inp_semester promotable students repeating same class in $newSemester<br />";
		}
		$validStatus				= array('','Y','S');
		$content					.= "<h3>$inp_semester $jobname Same Level in $newSemester Semester</h3>
										<table>
										<tr><th>Student</th>
											<th>Email</th>
											<th>Level</th>
											<th>Semester</th>
											<th>New Advisor</th>
											<th>Prev Advisor</tr>";
	
		//// get all students for the new semester who have a response of blank or Y
		//// and have a student_status of blank, S, or Y ordered by level
		//// put in the nextStudents array
				
		$sql				= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_semester='$newSemester' 
								and (student_response='' or student_response='Y') 
								and (student_status = '' 
									  or student_status = 'Y' 
									  or student_status = 'S')  
								order by student_call_sign";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "$sql<br />and retrieved $numSRows rows from $studentTableName table<br />";
			}
			if ($numSRows > 0) {
				if ($doDebug) {
					echo "found $numSRows rows in $studentTableName<br />";
				}
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_master_ID 					= $studentRow->user_ID;
					$student_master_call_sign 			= $studentRow->user_call_sign;
					$student_first_name 				= $studentRow->user_first_name;
					$student_last_name 					= $studentRow->user_last_name;
					$student_email 						= $studentRow->user_email;
					$student_phone 						= $studentRow->user_phone;
					$student_city 						= $studentRow->user_city;
					$student_state 						= $studentRow->user_state;
					$student_zip_code 					= $studentRow->user_zip_code;
					$student_country_code 				= $studentRow->user_country_code;
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
		
					$nextStudents[$student_call_sign]		= "$student_level|$student_promotable|$student_assigned_advisor";
				}
				/// have the nextStudents array
				if ($doDebug) {
					$myInt = count($nextStudents);
					echo "have $myInt nextStudents<br />";
				}
				/// now get all promotable students from the inp_semester and process
				$sql			= "select * from $studentTableName 
									where student_semester = '$inp_semester' 
									and student_promotable = 'P' 
									order by student_level";
				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numSRows									= $wpdb->num_rows;
					if ($doDebug) {
						echo "$sql<br />and retrieved $numSRows rows from $studentTableName table<br />";
					}
					if ($numSRows > 0) {
						if ($doDebug) {
							echo "ran $sql<br />and found $numSRows rows in $studentTableName<br />";
						}
						foreach ($wpw1_cwa_student as $studentRow) {
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
							
							if ($doDebug) {
								echo "Processing $student_call_sign Level: $student_level Promotable: $student_promotable<br />";
							}

							if (array_key_exists($student_call_sign,$nextStudents)) {
								if ($doDebug) {
									echo "$student_call_sign found in nextStudents<br />";
								}
								$studentData				= $nextStudents[$student_call_sign];
								$myArray					= explode("|",$studentData);
								$thisLevel					= $myArray[0];
								$thisPromotable				= $myArray[1];
								$thisAdvisor				= $myArray[2];
								if ($doDebug) {
									echo "nextStudent Info:<br /> 
											Level: $thisLevel<br /> 
											Promotable: $thisPromotable<br />
											Assigned advisor: $thisAdvisor<br />";
								}
								if ($student_level == $thisLevel) {   // repeating student
									$theLink			= "<a href='$studentHistoryURL?strpass=2&inp_student=$student_call_sign' target='_blank'>$student_last_name $student_first_name ($student_call_sign)</a>";
									$content			.= "<tr><td>$theLink</td>
																<td>$student_email</td>
																<td>$student_level</td>
																<td>$student_semester</td>
																<td>$student_assigned_advisor</td>
																<td>$thisAdvisor</td></tr>";
									$countable++;
									${'count' . $thisLevel}++;
								}
							}
						}
						$content						.= "</table>
															<p>Beginner: $countBeginner<br />
															Fundamental: $countFundamental<br />
															Intermediate: $countIntermediate<br />
															Advanced: $countAdvanced<br />
															Total repeating students: $countable<br />";
						
					} else {
						if ($doDebug) {
							echo "no students found in $inp_semester semester<br />";
						}
						$content			.= "No students found in $inp_semester semester<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "no students found in the $newSemester semester<br />";
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
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("Promotable Students Repeating|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('promotable_students_repeating', 'promotable_students_repeating_func');
