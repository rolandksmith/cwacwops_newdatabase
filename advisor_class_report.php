function advisor_class_report_func() {

/*	Prepares a report listing advisors in advisor call sign order
	and their classes with information about class size and the 
	number of students assigned to the class
	
	Created 2Aug2021 by Roland
	
	classCountArray[advisor call sign][class sequence] = count
	
	Modified 15Apr23 by Roland to correct action log handling
	Modified 12Jul23 by Roland to use consolidated tables
	Modified 11Oct24 by Roland for new database
	
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
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	$currentSemester		= $initializationArray['currentSemester'];
	$nextSemester			= $initializationArray['nextSemester'];
	if ($currentSemester == 'Not in Session') {
		$theSemester		= $nextSemester;
	} else {
		$theSemester		= $currentSemester;
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
		$advisorTableName		= "wpw1_cwa_advisor2";
		$advisorClassTableName	= "wpw1_cwa_advisorclass2";
		$studentTableName		= "wpw1_cwa_student2";
		$userMasterTableName 	= "wpw1_cwa_user_master2";
	} else {
		$advisorTableName		= "wpw1_cwa_advisor";
		$advisorClassTableName	= "wpw1_cwa_advisorclass";
		$studentTableName		= "wpw1_cwa_student";
		$userMasterTableName 	= "wpw1_cwa_user_master";
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
			echo "<br />Arrived at pass 2<br />";
		}
		
		$content				.= "<h3>$jobname for $theSemester Semester</h3>";
		
		////////	Get all the advisors and their classes and stick in an array

		$prevAdvisor	= '';
		$sql					= "select * from $advisorClassTableName 
									left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
									where advisorclass_semester = '$theSemester' 
									order by advisorclass_call_sign";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$user_ID 								= $advisorClassRow->user_ID;
					$user_call_sign							= $advisorClassRow->user_call_sign;
					$user_first_name 						= $advisorClassRow->user_first_name;
					$user_last_name 						= $advisorClassRow->user_last_name;
					$user_email 							= $advisorClassRow->user_email;
					$user_phone 							= $advisorClassRow->user_phone;
					$user_city 								= $advisorClassRow->user_city;
					$user_state 							= $advisorClassRow->user_state;
					$user_zip_code 							= $advisorClassRow->user_zip_code;
					$user_country_code 						= $advisorClassRow->user_country_code;
					$user_whatsapp 						= $advisorClassRow->user_whatsapp;
					$user_telegram 						= $advisorClassRow->user_telegram;
					$user_signal 						= $advisorClassRow->user_signal;
					$user_messenger 					= $advisorClassRow->user_messenger;
					$user_action_log 						= $advisorClassRow->user_action_log;
					$user_timezone_id 						= $advisorClassRow->user_timezone_id;
					$user_languages 						= $advisorClassRow->user_languages;
					$user_survey_score 						= $advisorClassRow->user_survey_score;
					$user_is_admin							= $advisorClassRow->user_is_admin;
					$user_role 								= $advisorClassRow->user_role;
					$user_date_created 						= $advisorClassRow->user_date_created;
					$user_date_updated 						= $advisorClassRow->user_date_updated;

					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
					$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
					$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
					$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->advisorclass_level;
					$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
					$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
					$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
					$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
					$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
					$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
					$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
					$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
					$advisorClass_student01 				= $advisorClassRow->advisorclass_student01;
					$advisorClass_student02 				= $advisorClassRow->advisorclass_student02;
					$advisorClass_student03 				= $advisorClassRow->advisorclass_student03;
					$advisorClass_student04 				= $advisorClassRow->advisorclass_student04;
					$advisorClass_student05 				= $advisorClassRow->advisorclass_student05;
					$advisorClass_student06 				= $advisorClassRow->advisorclass_student06;
					$advisorClass_student07 				= $advisorClassRow->advisorclass_student07;
					$advisorClass_student08 				= $advisorClassRow->advisorclass_student08;
					$advisorClass_student09 				= $advisorClassRow->advisorclass_student09;
					$advisorClass_student10 				= $advisorClassRow->advisorclass_student10;
					$advisorClass_student11 				= $advisorClassRow->advisorclass_student11;
					$advisorClass_student12 				= $advisorClassRow->advisorclass_student12;
					$advisorClass_student13 				= $advisorClassRow->advisorclass_student13;
					$advisorClass_student14 				= $advisorClassRow->advisorclass_student14;
					$advisorClass_student15 				= $advisorClassRow->advisorclass_student15;
					$advisorClass_student16 				= $advisorClassRow->advisorclass_student16;
					$advisorClass_student17 				= $advisorClassRow->advisorclass_student17;
					$advisorClass_student18 				= $advisorClassRow->advisorclass_student18;
					$advisorClass_student19 				= $advisorClassRow->advisorclass_student19;
					$advisorClass_student20 				= $advisorClassRow->advisorclass_student20;
					$advisorClass_student21 				= $advisorClassRow->advisorclass_student21;
					$advisorClass_student22 				= $advisorClassRow->advisorclass_student22;
					$advisorClass_student23 				= $advisorClassRow->advisorclass_student23;
					$advisorClass_student24 				= $advisorClassRow->advisorclass_student24;
					$advisorClass_student25 				= $advisorClassRow->advisorclass_student25;
					$advisorClass_student26 				= $advisorClassRow->advisorclass_student26;
					$advisorClass_student27 				= $advisorClassRow->advisorclass_student27;
					$advisorClass_student28 				= $advisorClassRow->advisorclass_student28;
					$advisorClass_student29 				= $advisorClassRow->advisorclass_student29;
					$advisorClass_student30 				= $advisorClassRow->advisorclass_student30;
					$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
					$advisorClass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
					$advisorClass_class_comments			= $advisorClassRow->advisorclass_class_comments;
					$advisorClass_copycontrol				= $advisorClassRow->advisorclass_copy_control;


					// if you need the country name and phone code, include the following
					$countrySQL		= "select * from wpw1_cwa_country_codes  
										where country_code = '$user_country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError("FUNCTION_User_Master_Data",$doDebug);
						$user_country		= "UNKNOWN";
						$user_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$user_country		= $countryRow->country_name;
								$user_ph_code		= $countryRow->ph_code;
							}
						} else {
							$user_country			= "Unknown";
							$user_ph_code			= "";
						}
					}

					if ($doDebug) {
						echo "<br />Processing $advisorClass_call_sign<br />";
					}
					if ($advisorClass_class_incomplete == 'Y') {
						$errorArray[]						= "Advisor $advisorClass_call_sign Class $advisorClass_sequence class_incomplete is $advisorClass_class_incomplete<br />";
					}
					if ($doDebug) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;Doing class $advisorClass_sequence<br />";
					}
					if ($advisorClass_call_sign != $prevAdvisor) {
						$advisorDataArray[$advisorClass_call_sign]['name']		= "$user_last_name, $user_first_name";
						$advisorDataArray[$advisorClass_call_sign]['email']		= $user_email;
						$advisorDataArray[$advisorClass_call_sign]['phone']		= $user_phone;
						$advisorDataArray[$advisorClass_call_sign]['ph_code']	= $user_ph_code;
						$advisorDataArray[$advisorClass_call_sign]['city']		= $user_city;
						$advisorDataArray[$advisorClass_call_sign]['state']		= $user_state;
						$advisorDataArray[$advisorClass_call_sign]['country']	= $user_country;
						$advisorDataArray[$advisorClass_call_sign]['tz']			= "$user_timezone_id $advisorClass_timezone_offset";
						$prevAdvisor		= $advisorClass_call_sign;
					}

					$advisorClassArray[$advisorClass_call_sign][$advisorClass_sequence]['level']			= $advisorClass_level;
					$advisorClassArray[$advisorClass_call_sign][$advisorClass_sequence]['class size']		= $advisorClass_class_size;
					$advisorClassArray[$advisorClass_call_sign][$advisorClass_sequence]['schedule utc']		= "$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc";
					$advisorClassArray[$advisorClass_call_sign][$advisorClass_sequence]['schedule local']	= "$advisorClass_class_schedule_times $advisorClass_class_schedule_days";
					$advisorClassArray[$advisorClass_call_sign][$advisorClass_sequence]['count']			= 0;
					$advisorClassArray[$advisorClass_call_sign][$advisorClass_sequence]['incomplete']		= $advisorClass_class_incomplete;
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
										left join $userMasterTableName on user_call_sign = student_call_sign 
										where student_semester='$theSemester' 
										and student_response='Y'
										 and (student_status='Y' 
										 	or student_status='S') 
										 	order by student_call_sign";
				$wpw1_cwa_student	= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numSRows									= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
					}
					if ($numSRows > 0) {
						foreach ($wpw1_cwa_student as $studentRow) {
							$user_ID 								= $studentRow->user_ID;
							$user_call_sign 						= $studentRow->user_call_sign;
							$user_first_name 						= $studentRow->user_first_name;
							$user_last_name 						= $studentRow->user_last_name;
							$user_email 							= $studentRow->user_email;
							$user_phone 							= $studentRow->user_phone;
							$user_city 								= $studentRow->user_city;
							$user_state 							= $studentRow->user_state;
							$user_zip_code 							= $studentRow->user_zip_code;
							$user_country_code 						= $studentRow->user_country_code;
							$user_whatsapp 						= $studentRow->user_whatsapp;
							$user_telegram 						= $studentRow->user_telegram;
							$user_signal 						= $studentRow->user_signal;
							$user_messenger 					= $studentRow->user_messenger;
							$user_action_log 						= $studentRow->user_action_log;
							$user_timezone_id 						= $studentRow->user_timezone_id;
							$user_languages 						= $studentRow->user_languages;
							$user_survey_score 						= $studentRow->user_survey_score;
							$user_is_admin							= $studentRow->user_is_admin;
							$user_role 								= $studentRow->user_role;
							$user_date_created 						= $studentRow->user_date_created;
							$user_date_updated 						= $studentRow->user_date_updated;
		
							$student_ID								= $studentRow->student_id;
							$student_call_sign						= $studentRow->student_call_sign;
							$student_time_zone  					= $studentRow->student_time_zone;
							$student_timezone_offset				= $studentRow->student_timezone_offset;
							$student_youth  						= $studentRow->student_youth;
							$student_age  							= $studentRow->student_age;
							$student_student_parent 				= $studentRow->student_parent;
							$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
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
							$student_student_status  				= strtoupper($studentRow->student_status);
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
							$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
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
		
							// if you need the country name and phone code, include the following
							$countrySQL		= "select * from wpw1_cwa_country_codes  
												where country_code = '$user_country_code'";
							$countrySQLResult	= $wpdb->get_results($countrySQL);
							if ($countrySQLResult === FALSE) {
								handleWPDBError("FUNCTION_User_Master_Data",$doDebug);
								$user_country		= "UNKNOWN";
								$user_ph_code		= "";
							} else {
								$numCRows		= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
								}
								if($numCRows > 0) {
									foreach($countrySQLResult as $countryRow) {
										$user_country		= $countryRow->country_name;
										$user_ph_code		= $countryRow->ph_code;
									}
								} else {
									$user_country			= "Unknown";
									$user_ph_code			= "";
								}
							}
					
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

	$ipAddr			= get_the_user_ip();
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}
	return $content;
}
add_shortcode ('advisor_class_report', 'advisor_class_report_func');
