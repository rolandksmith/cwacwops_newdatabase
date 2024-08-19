function send_email_to_student_to_evaluate_advisor_func() {

/* Send Email to Student to Evaluate Advisor
 * 
 * Reads Class pod for the recent semester and formulates an email
 * to each student in the class asking the student to do an evaluation
 * of the class advisor.
 *
 * The email contains a link to the web page 
 * which has the evaluation form
 *
 * The function will send an email to each student in the semester which can be anywhere 
 * from 300 - 800 emails. The function will time out trying to send that many emails, so 
 * the function writes a date/time to the past_student.student_survey_completion_date for 
 * each email sent and will send up to 150 emails per execution. It will skip any students 
 * that have a student_survey_completion_date. Run the function over again until no emails
 * are sent
 *
 * Created 16 May 2020 by Roland
 	Modified 2Nov2021 by Roland to accomodate testMode
 	Modified 26Oct22 by Roland to accomodate new timezone table formats
 	Modified 17Apr23 by Roland to fix action_log
 	Modified 16June23 by Roland to use current tables rather than past tables
 	Modified 14Jul23 by Roland to use consolidated tables
 	Modified 2Mar24 by Roland to use send reminder email to the students
 *
*/

	global $wpdb;

	$doDebug				= FALSE;
	$testMode				= FALSE;

	$initializationArray 	= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	$userName			= $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];

	ini_set('memory_limit','256M');
	ini_set('max_execution_time',0);
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}
	$myInt					= 100;
	$myDate					= date('d/m/Y');
	$totalStudents			= 0;
	$increment				= 0;
	$theURL					= "$siteURL/cwa-send-email-to-student-to-evaluate-advisor/";
	$evaluateAdvisorURL		= "$siteURL/cwa-student-evaluation-of-advisor/";
	$jobname				= 'Send Email to Student to Evaluate Advisor';

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$requestType				= '';
	$advisorsProcessed			= 0;
	$emailsSent					= 0;

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

table{font:'Times New Roman', sans-serif;background-image:none;}

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
		$extMode					= 'tm';
		$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass2";
		$studentTableName		= "wpw1_cwa_consolidated_student2";
		$advisorTableName		= "wpw1_cwa_consolidated_advisor2";
		if ($doDebug) {
			echo "<strong>Operating in Test Mode against class2, student2, and advisor2</strong><br />";
		}
		$content	.= 	"<p><strong>Operating in Test Mode against class2, student2, and advisor2</strong></p>";
	} else {
		$extMode					= 'pd';
		$advisorClassTableName	= "wpw1_cwa_consolidated_advisorclass";
		$studentTableName		= "wpw1_cwa_consolidated_student";
		$advisorTableName		= "wpw1_cwa_consolidated_advisor";
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}

		$content 		.= "<h3>Send Email to Student to Evaluate Advisor Setup</h3>
<p>Reads advisorClass table for the recent semester and formulates an email 
to each student in the class asking the student to do an evaluation 
of the class advisor and the curriculum.</p>
<p>The email contains a link to the CW Academy web page Student Portal which will have 
the reminder giving the student the link to do the actual evaluation.</p>


$siteURL/cw-academy-student-evaluation-of-advisor/
which has the evaluation form.</p>


<p>The function will send an email to each student in the semester which can be anywhere 
from 300 - 800 emails. The function will time out trying to send that many emails, so 
the function writes a date/time to the student.student_survey_completion_date for 
each email sent and will send up to 500 emails per execution. It will skip any students 
that have a student_survey_completion_date. Run the function over again until no emails 
are sent.</p>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data''>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
// if in testmode, set the number of emails to 5
//		if($testMode) {
//			$myInt	= 5;
//		}
// Get the semester
		$currentSemester	= $initializationArray['currentSemester'];
		$prevSemester		= $initializationArray['prevSemester'];
		if ($currentSemester != 'Not in Session') {
			$theSemester	= $currentSemester;
		} else {
			$theSemester	= $prevSemester;
		}

		$content			.= "<h3>Sending Email to Students to Evaluate Advisor</h3>";

// Read the advisorClass pod and process each student in the pod

		$sql						= "select * from $advisorClassTableName 
										where semester='$theSemester' 
										order by advisor_call_sign";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			if ($doDebug) {
				echo "Reading $advisorClassTableName table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numACRows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and found $numACRows rows in $advisorClassTableName table<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_advisor_callsign 			= $advisorClassRow->advisor_call_sign;
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
					$advisorClass_class_schedule_times_utc	= $advisorClassRow->class_schedule_times_utc;
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
				
					if ($doDebug) {
						echo "<br />Processing $advisorClassTableName $advisorClass_advisor_callsign<br />";
					}
					$advisorName		= "$advisorClass_advisor_first_name  $advisorClass_advisor_last_name";
					// cycle through thru all students in the class and output the information
					for ($snum=1;$snum<=$class_number_students;$snum++) {
						if ($snum < 10) {
							$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
						} else {
							$strSnum		= strval($snum);
						}
						$theInfo			= ${'advisorClass_student' . $strSnum};
						if ($doDebug) {
							echo "processing student $strSnum whose info is $theInfo<br />";
						}
						if ($theInfo != '') {
							$totalStudents++;
							$studentCallSign = $theInfo;
							// Get the student info from student table
							$sql			= "select * from $studentTableName 
												where semester='$theSemester' 
												and call_sign='$studentCallSign' 
												and assigned_advisor='$advisorClass_advisor_callsign' 
												and assigned_advisor_class='$advisorClass_sequence'";
							$wpw1_cwa_student	= $wpdb->get_results($sql);
							if ($wpw1_cwa_student === FALSE) {
								if ($doDebug) {
									echo "Reading $studentTableName table failed<br />";
									echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
									echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
								}
							} else {
								$numPSRows									= $wpdb->num_rows;
								if ($doDebug) {
									$myStr			= $wpdb->last_query;
									echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
								}
								if ($numPSRows > 0) {
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
										$student_abandoned  					= $studentRow->abandoned;
										$student_student_status  				= strtoupper($studentRow->student_status);
										$student_action_log  					= $studentRow->action_log;
										$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
										$student_selected_date  				= $studentRow->selected_date;
										$student_no_catalog		  				= $studentRow->no_catalog;
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
										$student_student_survey_completion_date = $studentRow->student_survey_completion_date;
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
									}
									$studentQualifies			= FALSE;
									if ($student_student_status == 'Y') {			// status must be y
										if ($student_promotable == 'P' || $student_promotable == 'N') {
											if ($student_student_survey_completion_date == "") {		// do not send if already sent
												if ($myInt > 0) {
													$studentQualifies	= TRUE;
													// formulate and send the email to the student
													$theURL	= "click <a href='$evaluateAdvisorURL?inp_student=$student_call_sign&strpass=2&extMode=$extMode' target='_blank'>Student Evaluation Survey</a>";
													if ($doDebug) {
														echo "Have all the data to formulate and send an email to<br />
															  Student: $student_last_name, $student_first_name ($student_call_sign)<br />
															  Email: $student_email<br />
															  Status: $student_student_status<br />
															  Level: $advisorClass_level<br />
															  Survey: $student_student_survey_completion_date<br />
																	  Advisor: $advisorName ($advisorClass_advisor_callsign)<br /><br />";
													}
													$my_message		= '';
													$my_subject 	= "CW Academy -- Request to Evaluate Your Recent Class, Curriculum, and Advisor";
													if ($testMode) {		// no emails to students!
														$my_to		= "kcgator@gmail.com";
														$mailCode	= 2;
														$increment++;
														$my_message .= "<p>Email would have been sent to $student_email ($student_call_sign)</p>";
														$my_subject	= "TESTMODE $my_subject";
													} else {
														$my_to		= $student_email;
														$mailCode	= 15;
													}
													$currentDate	= date('Y-m-d H:i:s');
													$expireDate		= date('M d, Y', strtotime($currentDate . ' +5 days'));
													$my_message 	.= "<p>To: $student_last_name, $student_first_name ($student_call_sign):</p>
<p>Thank you for your participating in the $advisorClass_level CW Acadamy class with advisor $advisorName ($advisorClass_advisor_callsign). As the semester is concluding, 
the CW Academy would like your opinion on the class, curriculum, and your advisor.</p>
<p>The survey will take just a few minutes and your input will help CW Academy continue to innovate and improve.</p>
<table style='border:4px solid red;'><tr><td><p>Please go to <a href='$siteURL/program-list/'>CW Academy</a> and fill out the survey linked in the Reminder 
at the top of your Student Portal. <b>NOTE: </b>The link to the survey will expire on $expireDate</p></td></tr></table>
<p>Do not reply to this email as the mailbox is not monitored.</p>
<p>Thanks and 73,<br />
CW Academy</p>";

													$mailResult		= emailFromCWA_v2(array('theRecipient'=>$my_to,
																								'theSubject'=>$my_subject,
																								'jobname'=>$jobname,
																								'theContent'=>$my_message,
																								'mailCode'=>$mailCode,
																								'increment'=>$increment,
																								'testMode'=>$testMode,
																								'doDebug'=>$doDebug));
													if ($mailResult === TRUE) {
														if ($doDebug) {
															echo "A email was sent to $my_to<br /><br />";
														}
														$content .= "A survey request email was sent to $my_to ($student_call_sign).<br />";
														$emailsSent++;
														$myInt--;
														
														// set up the reminder
														$effective_date		 	= date('Y-m-d H:i:s');
														$closeStr				= strtotime("+5 days");
														$close_date				= date('Y-m-d H:i:s', $closeStr);
														$token					= mt_rand();
														$email_text				= "<p></p>";
														$reminder_text			= "<b>Evaluate Class, Curriculum, and Advisor:</b> CW Academy is asking you to 
																					fill out a survey form evaluating your class, the curriculum, and the 
																					advisor. To fill out the survey, please click 
																					<a href='$evaluateAdvisorURL?inp_student=$student_call_sign&strpass=2&extMode=$extMode&token=$token' target='_blank'>Student Evaluation of Advisor</a>.
																					The link to the survey will on after $expireDate.";
														$inputParams		= array("effective_date|$effective_date|s",
																					"close_date|$close_date|s",
																					"resolved_date||s",
																					"send_reminder||s",
																					"send_once|Y|s",
																					"call_sign|$student_call_sign|s",
																					"role||s",
																					"email_text|$email_text|s",
																					"reminder_text|$reminder_text|s",
																					"resolved||s",
																					"token|$token|s");
														$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
														if ($insertResult[0] === FALSE) {
															handleWPDBError($jobname,$doDebug);
														} else {
															$content		.= "Reminder successfully added<br />";
														}

														$student_action_log	= "$student_action_log / $myDate ADVISOREVAL Email sent to student requesting advisor evaluation ";
														$studentUpdateData		= array('tableName'=>$studentTableName,
																						'inp_method'=>'update',
																						'inp_data'=>array('student_survey_completion_date'=>$myDate,
																										  'action_log'=>$student_action_log),
																						'inp_format'=>array('%s','%s'),
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
															if ($doDebug) {
																echo "Successfully updated $studentTableName record at $student_ID<br />";
															}
														}
													} else {
														if ($doDebug) {
															echo "The email function failed for $my_to ($student_call_sign)<br />";
														}
														$content .= "The email function failed for $my_to ($student_call_sign)<br />";
													}
												}
											}
										}
									}
									if (!$studentQualifies) {
										if ($doDebug) {
											echo "Student does not meet the criteria:<br />
												  Student: $student_last_name, $student_first_name ($student_call_sign)<br />
												  Email: $student_email<br />
												  Status: $student_student_status<br />
												  Level: $advisorClass_level<br />
												  Survey: $student_student_survey_completion_date<br />";
										}
									}
								} else {
									$content	.= "<p>No record for student $studentCallSign in $studentTableName. Advisor: $advisorClass_advisor_callsign $advisorClass_sequence</p>";
								}
							}
						}
					}
				} 		// end of while loop
				$content		.= "<p>$totalStudents Total Students<br />$emailsSent Emails sent</p><br />";
			} else {
				$content	.= "<p>No records found in $advisorClassTableName pod</p>";
			}			// end of numberRecords section
		}
	}

	$thisTime 		= date('Y-m-d H:i:s');
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
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('send_email_to_student_to_evaluate_advisor', 'send_email_to_student_to_evaluate_advisor_func');
