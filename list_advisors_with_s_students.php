function list_advisors_with_s_students_func() {

/* List Advisors with Incomplete Evaluations
 *
 * Presents a list of advisors where all their students
 * have a status of S
 *
 * This function is useful at the beginning of a semester
 *
 *
 	Modified 25Oct22 by Roland for the new timezone table format
 	Modified 13Jul23 by Roland to use consolidated tables
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];

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
	$advisorsDue				= 0;
	$myCount					= 0;
	$theURL						= "$siteURL/cwa-list-advisors-with-s-students/";
	$jobname					= "List Advisors with S Students";
	$errorArray					= array();

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
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
			if ($str_key 		== "inp_report_type") {
				$inp_report_type	 = $str_value;
				$inp_report_type	 = filter_var($inp_report_type,FILTER_UNSAFE_RAW);
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
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$studentTableName			= 'wpw1_cwa_student2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$content						.= "<p><b>Operating in test mode</b></p>";
	} else {
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$advisorTableName			= 'wpw1_cwa_advisor';
		$studentTableName			= 'wpw1_cwa_student';
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		
			$content 		.= "<h3>List Advisors with All S Stuents</h3>
								<p>The function cycles through all advisor classes for the current semester and generates a list of 
								advisors whose students have a status of S.</p>
								<p><form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								<table style='border-collapse:collapse;'>
								<tr><td style='vertical-align:top;'>Report Type</td>
									<td><input type='radio' class='formInputButton' name='inp_report_type' value='alls' required checkedk>Report only advisors with all S students<br />
										<input type='radio' class='formInputButton' name='inp_report_type' value='anys' required>Report advisors with any S students</td></tr>
								$testModeOption
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form></p>";


/////// Pass 2

	} elseif ("2" == $strPass) {
	
		$currentSemester		= $initializationArray['currentSemester'];
		$nextSemester			= $initializationArray['nextSemester'];
		$theSemester			= $currentSemester;
		if ($currentSemester == 'Not in Session') {
			$theSemester		= $nextSemester;
		}
		if ($doDebug) {
			echo "set theSemester to $theSemester<br />";
		}
		$doProceed				= TRUE;
		$alls					= FALSE;
		$anys					= FALSE;
		
		if ($inp_report_type == 'alls') {
			$alls				= TRUE;
			$myStr				= "all S";
		} else {
			$anys				= TRUE;
			$myStr				= "any S";
		}
		
		$sql			= "select * from $advisorClassTableName 
							left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
							where advisorclass_semester = '$theSemester' 
								and (user_survey_score != 6 
									and user_survey_score != 9) 
							order by advisorclass_call_sign, 
									 advisorclass_sequence";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				$content			.= "<h4>Advisors with $myStr students in the $currentSemester semester</h4>
										<table>
										<tr><th>Advisor</th>
											<th>Name</th>
											<th>Sequence</th>
											<th>Level</th>
											<th>Phone</th>
											<th>Email</th>
										</tr>";
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_master_ID 				= $advisorClassRow->user_ID;
					$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
					$advisorClass_first_name 				= $advisorClassRow->user_first_name;
					$advisorClass_last_name 				= $advisorClassRow->user_last_name;
					$advisorClass_email 					= $advisorClassRow->user_email;
					$advisorClass_phone 					= $advisorClassRow->user_phone;
					$advisorClass_city 						= $advisorClassRow->user_city;
					$advisorClass_state 					= $advisorClassRow->user_state;
					$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
					$advisorClass_country_code 				= $advisorClassRow->user_country_code;
					$advisorClass_whatsapp 					= $advisorClassRow->user_whatsapp;
					$advisorClass_telegram 					= $advisorClassRow->user_telegram;
					$advisorClass_signal 					= $advisorClassRow->user_signal;
					$advisorClass_messenger 				= $advisorClassRow->user_messenger;
					$advisorClass_master_action_log 		= $advisorClassRow->user_action_log;
					$advisorClass_timezone_id 				= $advisorClassRow->user_timezone_id;
					$advisorClass_languages 				= $advisorClassRow->user_languages;
					$advisorClass_survey_score 				= $advisorClassRow->user_survey_score;
					$advisorClass_is_admin					= $advisorClassRow->user_is_admin;
					$advisorClass_role 						= $advisorClassRow->user_role;
					$advisorClass_master_date_created 		= $advisorClassRow->user_date_created;
					$advisorClass_master_date_updated 		= $advisorClassRow->user_date_updated;

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
										where country_code = '$advisorClass_country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$advisorClass_country		= "UNKNOWN";
						$advisorClass_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$advisorClass_country		= $countryRow->country_name;
								$advisorClass_ph_code		= $countryRow->ph_code;
							}
						} else {
							$advisorClass_country			= "Unknown";
							$advisorClass_ph_code			= "";
						}
					}


					if ($doDebug) {
						echo "Processing class record $advisor_call_sign sequence $advisorClass_sequence<br />";
					}
					$gotAnyS				= FALSE;
					$gotAllS				= TRUE;
					$anyStudents			= FALSE;
					if ($advisorClass_number_students > 0) {
						for ($snum=1;$snum<=$advisorClass_number_students;$snum++) {
							if ($snum < 10) {
								$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
							} else {
								$strSnum		= strval($snum);
							}
							$theInfo			= ${'advisorClass_student' . $strSnum};
							// get the student_status
							$student_status		= $wpdb->get_var("select student_status from $studentTableName 
																 where student_call_sign = '$theInfo' 
																	and student_semester = '$theSemester'");
							if ($student_status == NULL) {
								$errorArray[]		= "$advisorClass_call_sign has a student $theInfo in 
	class $advisorClass_sequence not found in $studentTableName table";
							} else {
								if ($student_status == '' || $student_status == ' ') {
									$errorArray[]	= "$advisorClass_call_sign has a student $theInfo in 
	class $advisorClass_sequence with an empty student_status";
								} else {
									if ($student_status != 'S') {
										$gotAllS		= FALSE;
									} else {
										$gotAnyS		= TRUE;
									}
								}
							}
						}
						$thisPhone			= "+$advisorClass_ph_code $advisorClass_phone";
						if ($alls && $gotAllS) {
							/// output the info	
							$content		.= "<tr><td>$advisorClass_call_sign</td>
													<td>$advisorClass_last_name, $advisorClass_first_name</td>
													<td>$advisorClass_sequence</td>
													<td>$advisorClass_level</td>
													<td>$thisPhone</td>
													<td>$advisorClass_email</td></tr>";
							$myCount++;
						} elseif ($anys && $gotAnyS) {
							/// output the info	
							$content		.= "<tr><td>$advisorClass_call_sign</td>
													<td>$advisorClass_last_name, $advisorClass_first_name</td>
													<td>$advisorClass_sequence</td>
													<td>$advisorClass_level</td>
													<td>$thisPhone</td>
													<td>$advisorClass_email</td></tr>";
							$myCount++;
						}
					}
				}
				$content			.= "</table><p>$myCount Advisors with $myStr students</p>";
				if (count($errorArray) > 0) {
					$content		.= "<br /><h4>Errors Encountered</h4>";
					foreach($errorArray as $thisError);
						$content	.= "$thisError<br />";
				}
			} else {
				echo "No records found in $advisorClassTableName<br />";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('list_advisors_with_s_students', 'list_advisors_with_s_students_func');

