function display_advisor_classes_func() {

/* Display the Advisor's classes
 * This function displays the class information that the advisor has
 * signed up for either through the gravity forms advisor sign up
 * or after entering student evaluations
 *
 * Input Parameters: 	Advisor's call sign (call_sign)
 *
 	Modified 22Oct22 by Roland for new timezone table format
 	Modified 15Apr23 by ROland to fix action_log
 	Modified 13Jul23 by ROland to use consolidated tables
*/

	global $wpdb, $studentTableName, $advisorTableName, $advisorClassTableName, 
	$doDebug, $initializationArray;
	
	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
//	CHECK THIS!								//////////////////////
	$validUser = $initializationArray['validUser'];
//	if ($validUser == "N") {
//		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
//	}

	$userName						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL						= $initializationArray['siteurl'];

	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	}

/// get the time that the process started
	$startingMicroTime				= microtime(TRUE);

	$strPass						= "1";
	$theURL							= "$siteURL/cwa-display-advisor-classes/";

	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
			}
			if ($str_key 		== "call_sign") {
				$inp_call_sign	 = strtoupper($str_value);
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
		$studentTableName			= "wpw1_cwa_consolidated_student2";
		$advisorTableName		= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
	} else {
		$studentTableName			= "wpw1_cwa_consolidated_student";
		$advisorTableName		= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass";
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




function getAdvisorNewInfoToDisplay($inp_callsign) {

	global $wpdb,$studentTableName,$advisorTableName,$advisorClassTableName,$doDebug,$initializationArray;

	if ($doDebug) {
		echo "<br />At getAdvisorInfoToDisplay<br />Parameters: $inp_callsign, $advisorTableName, $advisorClassTableName, $studentTableName<br />";
	}

	$content			= "";
	$initializationArray = data_initialization_func();
	$currentSemester	= $initializationArray['currentSemester'];
	$prevSemester		= $initializationArray['prevSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$defaultClassSize	= $initializationArray['defaultClassSize'];
	
	if ($currentSemester != 'Not in Session') {
		$theSemester	= $currentSemester;
	} else {
		$theSemester	= $prevSemester;
	}
	

	$advisor_select_sequence				= '';
	$advisor_call_sign						= '';
	$advisor_first_name						= '';
	$advisor_last_name						= '';
	$advisor_email							= '';
	$advisor_phone							= '';
	$advisor_text_message					= '';
	$advisor_city							= '';
	$advisor_state							= '';
	$advisor_zip_code						= '';
	$advisor_country						= '';
	$advisor_time_zone						= '';
	$advisor_semester						= '';
	$advisor_survey_score					= '';
	$advisor_languages_spoken				= '';
	$advisor_fifo_date						= '';
	$advisor_welcome_email_date				= '';
	$advisor_verify_email_date				= '';
	$advisor_verify_email_number			= '';
	$advisor_verify_response				= '';
	$advisor_action_log						= '';
	$advisor_class_verified					= '';
	$advisor_evaluations_complete			= '';


	$sql						= "select * from $advisorTableName 
									where call_sign='$inp_callsign' 
									order by date_created DESC";
	$wpw1_cwa_advisor	= $wpdb->get_results($sql);
	if ($wpw1_cwa_advisor === FALSE) {
		$myError			= $wpdb->last_error;
		$myQuery			= $wpdb->last_query;
		if ($doDebug) {
			echo "Reading $advisorClassTableName table failed<br />
				  wpdb->last_query: $myQuery<br />
				  wpdb->last_error: $myError<br />";
		}
		$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
		sendErrorEmail($errorMsg);
	} else {
		$numPARows				= $wpdb->num_rows;
		if ($doDebug) {
			$myStr				= $wpdb->last_query;
			echo "ran $myStr<br />and found $numPARows rows in $pastAdvisorNewTableName<br />";
		}
		if ($numPARows > 0) {
			foreach ($wpw1_cwa_advisor as $advisorRow) {
				$advisor_ID						= $advisorRow->advisor_id;
				$advisor_select_sequence 			= $advisorRow->select_sequence;
				$advisor_call_sign 				= strtoupper($advisorRow->call_sign);
				$advisor_first_name 				= $advisorRow->first_name;
				$advisor_last_name 				= stripslashes($advisorRow->last_name);
				$advisor_email 					= $advisorRow->email;
				$advisor_ph_code					= $advisorRow->ph_code;
				$advisor_phone						= $advisorRow->phone;
				$advisor_text_message 				= $advisorRow->text_message;
				$advisor_city 						= $advisorRow->city;
				$advisor_state 					= $advisorRow->state;
				$advisor_zip_code 					= $advisorRow->zip_code;
				$advisor_country 					= $advisorRow->country;
				$advisor_country_code				= $advisorRow->country_code;
				$advisor_time_zone 				= $advisorRow->time_zone;
				$advisor_timezone_id				= $advisorRow->timezone_id;
				$advisor_timezone_offset			= $advisorRow->timezone_offset;
				$advisor_whatsapp					= $advisorRow->whatsapp_app;
				$advisor_signal					= $advisorRow->signal_app;
				$advisor_telgram					= $advisorRow->telegram_app;
				$advisor_messenger					= $advisorRow->messenger_app;
				$advisor_semester 					= $advisorRow->semester;
				$advisor_survey_score 				= $advisorRow->survey_score;
				$advisor_languages 				= $advisorRow->languages;
				$advisor_fifo_date 				= $advisorRow->fifo_date;
				$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
				$advisor_verify_email_date 		= $advisorRow->verify_email_date;
				$advisor_verify_email_number 		= $advisorRow->verify_email_number;
				$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
				$advisor_action_log 				= $advisorRow->action_log;
				$advisor_class_verified 			= $advisorRow->class_verified;
				$advisor_control_code 				= $advisorRow->control_code;
				$advisor_date_created 				= $advisorRow->date_created;
				$advisor_date_updated 				= $advisorRow->date_updated;

				$advisor_last_name 				= no_magic_quotes($advisor_last_name);

				if ($advisor_survey_score == '6') {		// bad actor
					$isBanned							= "TRUE";
				} else {
					$isBanned							= '';
				}
				$gotData			= TRUE;
				$content			.= "<h3>Advisor and Student Information for $advisor_semester  Semester</h3>
										<table style='width:600px;'>
										<tr><td style='width:250px;'><b>Call Sign</b></td>
											<td>$advisor_call_sign</td></tr>
										<tr><td><b>First Name</b></td>
											<td>$advisor_first_name</td></tr>
										<tr><td><b>Last Name</b></td>
											<td>$advisor_last_name</td></tr>
										<tr><td><b>Semester</b></td>
											<td>$advisor_semester</td></tr>
										<tr><td ><b>Time zone where you live</td>
											<td>advisor_timezone_id $advisor_timezone_offset</td></tr>
										</table>";


	
// get all advisorClass records and display
				$sql							= "select * from $advisorClassTableName 
													where advisor_call_sign='$inp_callsign' 
													and semester='$advisor_semester' 
													order by sequence";
				$wpw1_cwa_advisorclass		= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisorclass === FALSE) {
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
					$numACRows					= $wpdb->num_rows;
					if ($doDebug) {
						$myStr					= $wpdb->last_query;
						echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName table<br />";
					}
					if ($numACRows > 0) {
						foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
							$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
							$advisorClass_advisor_callsign 		= $advisorClassRow->advisor_call_sign;
							$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
							$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
							$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
							$advisorClass_sequence 				= $advisorClassRow->sequence;
							$advisorClass_semester 				= $advisorClassRow->semester;
							$advisorClass_timezone 				= $advisorClassRow->time_zone;
							$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
							$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
							$advisorClass_level 					= $advisorClassRow->level;
							$advisorClass_class_size 				= $advisorClassRow->class_size;
							$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
							$advisorClass_class_schedule_times 	= $advisorClassRow->class_schedule_times;
							$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
							$advisorClass_class_schedule_times_utc	= $advisorClassRow->class_schedule_times_utc;
							$advisorClass_action_log 				= $advisorClassRow->action_log;
							$advisorClass_class_incomplete 		= $advisorClassRow->class_incomplete;
							$advisorClass_date_created				= $advisorClassRow->date_created;
							$advisorClass_date_updated				= $advisorClassRow->date_updated;
							$advisorClass_student01 				= $advisorClassRow->student01;
							$advisorClass_student02 				= $advisorClassRow->student02;
							$advisorClass_student03 				= $advisorClassRow->student03;
							$advisorClass_student04 				= $advisorClassRow->student04;
							$advisorClass_student05 				= $advisorClassRow->student05;
							$advisorClass_student06 				= $advisorClassRow->student06;
							$advisorClass_student07 				= $advisorClassRow->student07;
							$advisorClass_student08 				= $advisorClassRow->student08;
							$advisorClass_student09 				= $advisorClassRow->student09;
							$advisorClass_student10 				= $advisorClassRow->student10;
							$advisorClass_student11 				= $advisorClassRow->student11;
							$advisorClass_student12 				= $advisorClassRow->student12;
							$advisorClass_student13 				= $advisorClassRow->student13;
							$advisorClass_student14 				= $advisorClassRow->student14;
							$advisorClass_student15 				= $advisorClassRow->student15;
							$advisorClass_student16 				= $advisorClassRow->student16;
							$advisorClass_student17 				= $advisorClassRow->student17;
							$advisorClass_student18 				= $advisorClassRow->student18;
							$advisorClass_student19 				= $advisorClassRow->student19;
							$advisorClass_student20 				= $advisorClassRow->student20;
							$advisorClass_student21 				= $advisorClassRow->student21;
							$advisorClass_student22 				= $advisorClassRow->student22;
							$advisorClass_student23 				= $advisorClassRow->student23;
							$advisorClass_student24 				= $advisorClassRow->student24;
							$advisorClass_student25 				= $advisorClassRow->student25;
							$advisorClass_student26 				= $advisorClassRow->student26;
							$advisorClass_student27 				= $advisorClassRow->student27;
							$advisorClass_student28 				= $advisorClassRow->student28;
							$advisorClass_student29 				= $advisorClassRow->student29;
							$advisorClass_student30 				= $advisorClassRow->student30;
							$class_number_students						= $advisorClassRow->number_students;
							$class_evaluation_complete 					= $advisorClassRow->evaluation_complete;
							$class_comments								= $advisorClassRow->class_comments;
							$copycontrol								= $advisorClassRow->copy_control;

							$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
		
							$myInt										= intval($advisorClass_sequence);
							if ($doDebug) {
								echo "Found a class sequence $advisorClass_sequence<br />
									  &nbsp;&nbsp;&nbsp;&nbsp;Semester: $advisorClass_semester<br />
									  &nbsp;&nbsp;&nbsp;&nbsp;TZ: $advisorClass_timezone<br />
									  &nbsp;&nbsp;&nbsp;&nbsp;Level: $advisorClass_level<br />";
							}
			
							$content			.= "<b>Class $advisorClass_sequence:</b>
													<table style='width:600px;'>
													<tr><td style='width:250px;'><b>Level</b></td>
														<td>$advisorClass_level</td></tr>
													<tr><td><b>Class Size</b></td>
														<td>$advisorClass_class_size</td></tr>
													<tr><td><b>Teaching Days</b></td>
														<td>$advisorClass_class_schedule_days</td></tr>
													<tr><td><b>Teaching Time<b></td>
														<td>$advisorClass_class_schedule_times</td></tr>
													<tr><td><b>Class Incomplete</b></td>
														<td>$advisorClass_class_incomplete</td></tr>
													<tr><td><b>Number of Students</b></td>
														<td>$class_number_students</td></tr>
													<tr><td><b>Evaluations Complete</td>
														<td>$class_evaluation_complete</td></tr>";
							// display the student info
							for ($snum=1;$snum<31;$snum++) {
								if ($snum < 10) {
									$strSnum 				= str_pad($snum,2,'0',STR_PAD_LEFT);
								} else {
									$strSnum				= strval($snum);
								}
								$theInfo					= ${'advisorClass_student' . $strSnum};
								if ($theInfo != '') {
									
									$sql					= "select * from $studentTableName where call_sign='$theInfo' and semester='$advisorClass_semester' and level='$advisorClass_level' order by call_sign";
									$wpw1_cwa_student	= $wpdb->get_results($sql);
									if ($wpw1_cwa_student === FALSE) {
										$myError			= $wpdb->last_error;
										$myQuery			= $wpdb->last_query;
										if ($doDebug) {
											echo "Reading $studentTableName table failed<br />
												  wpdb->last_query: $myQuery<br />
												  wpdb->last_error: $myError<br />";
										}
										$errorMsg			= "$jobname Reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
										sendErrorEmail($errorMsg);
									} else {
										$numPSRows			= $wpdb->num_rows;
										if ($doDebug) {
											$myStr			= $wpdb->last_query;
											echo "ran $myStr<br />and found $numPSRows rows in $studentTableName tablle<br />";
										}
										if ($numPSRows > 0) {
											foreach ($wpw1_cwa_student as $studentRow) {
												$student_ID							= $studentRow->student_id;
												$student_call_sign						= strtoupper($studentRow->call_sign);
												$student_first_name					= $studentRow->first_name;
												$student_last_name						= stripslashes($studentRow->last_name);
												$student_email  						= strtolower(strtolower($studentRow->email));
												$student_ph_code						= $studentRow->ph_code;
												$student_phone  						= $studentRow->phone;
												$student_city  						= $studentRow->city;
												$student_state  						= $studentRow->state;
												$student_zip_code  					= $studentRow->zip_code;
												$student_country_code					= $studentRow->country_code;
												$student_country  						= $studentRow->country;
												$student_time_zone  					= $studentRow->time_zone;
												$student_timezone_id					= $studentRow->timezone_id;
												$student_timezone_offset				= $studentRow->timezone_offset;
												$student_whatsapp						= $studentRow->whatsapp_app;
												$student_signal						= $studentRow->signal_app;
												$student_telegram						= $studentRow->telegram_app;
												$student_messenger						= $studentRow->messenger_app;					
												$student_wpm 	 						= $studentRow->wpm;
												$student_youth  						= $studentRow->youth;
												$student_age  							= $studentRow->age;
												$student_student_parent 				= $studentRow->student_parent;
												$student_student_parent_email  		= strtolower($studentRow->student_parent_email);
												$student_level  						= $studentRow->level;
												$student_waiting_list 					= $studentRow->waiting_list;
												$student_request_date  				= $studentRow->request_date;
												$student_semester						= $studentRow->semester;
												$student_notes  						= $studentRow->notes;
												$student_welcome_date  				= $studentRow->welcome_date;
												$student_email_sent_date  				= $studentRow->email_sent_date;
												$student_email_number  				= $studentRow->email_number;
												$student_response  					= strtoupper($studentRow->response);
												$student_response_date  				= $studentRow->response_date;
												$student_abandoned  					= $studentRow->abandoned;
												$student_student_status  				= strtoupper($studentRow->student_status);
												$student_action_log  					= $studentRow->action_log;
												$student_pre_assigned_advisor  		= $studentRow->pre_assigned_advisor;
												$student_selected_date  				= $studentRow->selected_date;
												$student_no_catalog		  			= $studentRow->no_catalog;
												$student_hold_override  				= $studentRow->hold_override;
												$student_messaging  					= $studentRow->messaging;
												$student_assigned_advisor  			= $studentRow->assigned_advisor;
												$student_advisor_select_date  			= $studentRow->advisor_select_date;
												$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
												$student_hold_reason_code  			= $studentRow->hold_reason_code;
												$student_class_priority  				= $studentRow->class_priority;
												$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
												$student_promotable  					= $studentRow->promotable;
												$student_excluded_advisor  			= $studentRow->excluded_advisor;
												$student_student_survey_completion_date = $studentRow->student_survey_completion_date;
												$student_available_class_days  		= $studentRow->available_class_days;
												$student_intervention_required  		= $studentRow->intervention_required;
												$student_copy_control  				= $studentRow->copy_control;
												$student_first_class_choice  			= $studentRow->first_class_choice;
												$student_second_class_choice  			= $studentRow->second_class_choice;
												$student_third_class_choice  			= $studentRow->third_class_choice;
												$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
												$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
												$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
												$student_date_created 					= $studentRow->date_created;
												$student_date_updated			  		= $studentRow->date_updated;

												$student_last_name 					= no_magic_quotes($student_last_name);
											
												$dispPromotable 	= '';
												if ($student_promotable == 'P') {
													$dispPromotable 	= '(Promoted)';
												} elseif ($student_promotable == 'N') {
													$dispPromotable		= '(Not Promoted)';
												} elseif ($student_promotable == 'W') {
													$dispPromotable		= '(Withdrew)';
												} else {
													$dispPromotable		= "($student_promotable)";
												}
												$content		.= "<tr><td><b>Student$strSnum</b></td>
																		<td>$student_last_name, $student_first_name ($student_call_sign)&nbsp;&nbsp;&nbsp;$dispPromotable</td></tr>";
											}
										} else {
											$content		.= "<tr><td><b>Student$strSnum</b></td>
																	<td>Student record not found</td></tr>";
										
										}
									}
								}
							}
							$content		.= "</table>";
						}
					} else {
						if ($doDebug) {
							echo "no data found in $advisorClassTableName table for $inp_callsign<br />";
						}
					}
				}
			}				/// end of the class while loop
//			$content		.= "</table>";
		} else {
			if ($doDebug) {
				echo "No matching $advisorTableName record for $inp_callsign in semester $checkSemester<br />";
			}
		}
	}
	if ($gotData) {
		return array('OK',$content);	
	} else {
		return array('None','');
	}
}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		
		$content 		.= "<h3>Enter Advisor Call Sign</h3>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px;'>Advisor Call Sign</td>
								<td><input class='formInputText' type='text' maxlength='30' name='call_sign' size='10' autofocus></td></tr>
							$testModeOption
							<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";


///// Pass 2


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "Arrived at Pass 2<br />";
		}
/*		$content 	.= "<style type='text/css'>
th {border-bottom:1px solid black;}
td {border-bottom:1px solid black;}</style>";
*/

		$advisorInfo	= getAdvisorNewInfoToDisplay($inp_call_sign);
		if ($advisorInfo[0] == 'OK') {
			$myStr		= $advisorInfo[1];
			$content	.= $myStr;
		}
	}
	$thisTime 			= date('Y-m-d H:i:s');
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
	$result			= write_joblog_func("Display Advisor Classes|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	$content 			.= "<br /><br /><p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('display_advisor_classes', 'display_advisor_classes_func');
