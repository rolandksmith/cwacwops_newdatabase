function detailed_advisor_history_func() {

/*	Show Detailed History of an Advisor
	Reads current advisor and advisorClass pods and displays the info then goes through 
	all the pastAdvisorNew and pastAdvisorClass pods and displays that information. If 
	requested, will list the students for the classes 
	
	Modified 22Oct22 by Roland for new timezone table fields
	Modified 15Apr23 by Roland to fix action_log
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
	$validUser 						= $initializationArray['validUser'];
	$userName						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL						= $initializationArray['siteurl'];
	
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
	$inp_advisor				= '';
	$theURL						= "$siteURL/cwa-detailed-history-for-an-advisor/";
	$inp_liststudents			= '';
	$jobname					= "Detailed Advisor History";

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
			if ($str_key 		== "inp_advisor") {
				$inp_advisor	 = $str_value;
				$inp_advisor	 = strtoupper(filter_var($inp_advisor,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_liststudents") {
				$inp_liststudents	 = $str_value;
				$inp_liststudents	 = filter_var($inp_liststudents,FILTER_UNSAFE_RAW);
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
					$testMode == TRUE;
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
		if ($doDebug) {
			echo "Operating in TestMode<br />";
		}
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass2';
		$studentTableName			= 'wpw1_cwa_consolidated_student2';
		$content					.= "<p><b>Operating in TestMode</b></p>";
	} else {
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
		$studentTableName			= 'wpw1_cwa_consolidated_student';
		$past_studentTableName		= 'wpw1_cwa_past_student';
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Detailed Advisor History</h3>
							<p>Enter the advisor's call sign, select whether or not the students are to be included 
							in the report, and click 'Next'</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td>Advisor Call Sign</td>
								<td><input type='text' class='formInputTxt' name='inp_advisor' size='15' maxlength='15'></td></tr>
							<tr><td>Include list of students?</td>
								<td><input type='radio' class='formInputButton' name='inp_liststudents' value='No' checked> No<br />
									<input type='radio' class='formInputButton' name='inp_liststudents' value='Yes'> Yes</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "At pass 2 with $inp_advisor; liststudent: $inp_liststudents<br />";
		}
		$content				.= "<h3>Detailed Advisor History</h3>";
		// get the current advisor record, if any
		$sql					= "select * from $advisorTableName 
								   where call_sign='$inp_advisor' 
								   order by date_created";
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
				$myStr			= $wpdb->last_query;
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

	
					if ($doDebug) {
						echo "<br />Have advisor record for $advisor_call_sign and semester $advisor_semester<br />";
					}
					$newActionLog	= formatActionLog($advisor_action_log);
					$content		.= "<h4>Advisor Record for $advisor_last_name, $advisor_first_name ($advisor_call_sign) in semester $advisor_semester</h4>
										<table>
										<tr><th>Semester</th>
											<th>Email</th>
											<th>Phone</th>
											<th>State</td></tr>
										<tr><td>$advisor_semester</td>
											<td>$advisor_email</td>
											<td>$advisor_phone</td>
											<td>$advisor_state</td></tr>
	
										<tr><th>Welcome Date</th>
											<th>Verify Date</th>
											<th>Verify Response</th>
											<th>Survey Score</th></tr>
										<tr><td>$advisor_welcome_email_date</td>
											<td>$advisor_verify_email_date</td>
											<td>$advisor_verify_response</td>
											<td>$advisor_survey_score</td></tr>
										<tr><td colspan='4'>$newActionLog</td></tr>
										<tr><td colspan='4'>&nbsp;</td></tr>";

					//// now get the class records for this advisor
					$sql					= "select * from $advisorClassTableName 
											   where advisor_call_sign='$inp_advisor' 
												and semester='$advisor_semester'";
					$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
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
						$numACRows			= $wpdb->num_rows;
						if ($doDebug) {
							$myStr			= $wpdb->last_query;
							echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName<br />";
						}
						if ($numACRows > 0) {
							foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
								$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
								$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
								$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
								$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
								$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
								$advisorClass_sequence 					= $advisorClassRow->sequence;
								$advisorClass_semester 					= $advisorClassRow->semester;
								$advisorClass_timezone 					= $advisorClassRow->time_zone;
								$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
								$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
								$advisorClass_level 					= $advisorClassRow->level;
								$advisorClass_class_size 				= $advisorClassRow->class_size;
								$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
								$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
								$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
								$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
								$advisorClass_action_log 				= $advisorClassRow->action_log;
								$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
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
								$class_number_students					= $advisorClassRow->number_students;
								$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
								$class_comments							= $advisorClassRow->class_comments;
								$copycontrol							= $advisorClassRow->copy_control;


								$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

						
								if ($doDebug) {
									echo "<br />Have advisorClass record for $advisorClass_advisor_callsign. Class nmbr: $advisorClass_sequence; Level: $advisorClass_level<br />";
								}

								$content		.= "<tr><td><b>Class</b></td>
														<td><b>Level</b></td>
														<td><b>Local Schedule</b></td>
														<td><b>UTC Schedule</b></td></tr>
													<tr><td>$advisorClass_sequence</td>
														<td>$advisorClass_level</td>
														<td>$advisorClass_class_schedule_times $advisorClass_class_schedule_days</td>
														<td>$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc</td></tr>";
				
								//// if students are to be displayed, get them
								if ($inp_liststudents == 'Yes') {
									$content			.= "<tr><td><b>Student</b></td>
																<td colspan='2'><b>Email</b></td>
																<td><b>Promotable</b></td></tr>";
									if ($doDebug) {
										echo "<br />getting students to list<br />";
									}
									for ($snum=1;$snum<31;$snum++) {
										if ($snum < 10) {
											$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
										} else {
											$strSnum		= strval($snum);
										}
										$theInfo			= ${'advisorClass_student' . $strSnum};
										if ($doDebug) {
											echo "processing past_advisorClass_student$strSnum theInfo: $theInfo<br />";
										}
										if ($theInfo != '') {
											$sql				= "select * from $studentTableName 
																   where call_sign = '$theInfo' 
																	and semester='$advisor_semester' ";
											$wpw1_cwa_student	= $wpdb->get_results($sql);
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
												$numSRows		= $wpdb->num_rows;
												if ($numSRows > 0) {
													$myStr		= $wpdb->last_query;
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
										
														if ($doDebug) {
															echo "have student $student_call_sign<br />";
														}
														$content		.= "<tr><td>$student_last_name, $student_first_name ($student_call_sign)</td>
																				<td colspan='2'>$student_email</td>
																				<td>$student_promotable</td></tr>";
													}			/// end of student while
												} else {				/// no student records
													$content			.= "<tr><td colspan='4'>No student record found for $theInfo</td></tr>";
												}
											}
										}		
									}
								}					/// end of list students if requestef
							}						/// end of advisorClass while
							$content		.= "</table>";
						} else {					/// no advisorClass records
							$content		.= "<tr><td colspan='4'>No advisorClass records</td></tr>";
						}
					}
				}
			} else {		// no current advisor record
				$content			.= "<p>No current advisor record</p>";
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
add_shortcode ('detailed_advisor_history', 'detailed_advisor_history_func');
