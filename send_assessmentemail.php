function send_assessment_email_func() {

// modified 26Oct22 by Roland for the new timezone table format
// modified 17apr23 by Roland to fix action_log


/// not currently in the snippets database table

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
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
	$theURL						= "$siteURL/cwa-send-assessment-email/";
	$assessmentArray			= array();
	$nextSemester				= $initializationArray['nextSemester'];
	$emailCount					= 5;
	$emailsSent					= 0;
	$increment					= 0;
	$jobname					= 'Send Assessment Email';

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
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$studentTableName			= "wpw1_cwa_student2";
		$assessmentTableName		= "wpw1_cwa_audio_assessment2";
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
		$assessmentTableName		= "wpw1_cwa_audio_assessment";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Send Assessment Email to Students</h3>
<p>For each student in the student table:<br />
1. Check if they have class selection. If not queue update signup msg
2. Check if they have done a self assessment. If not queue assessment msg
3. Send the email
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "At pass 2<br />";
		}
		
		// get the list of students who have done the assessment into assessmentArray
		$sql						= "select distinct(call_sign) as assessment_call_sign from $assessmentTableName order by call_sign";
		$wpw1_cwa_audio_assessment	= $wpdb->get_results($sql);
		if ($wpw1_cwa_audio_assessment === FALSE) {
			if ($doDebug) {
				echo "Reading $assessmentTableName table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numAARows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numAARows rows from wpw1_cwa_audio_assessment table<br />";
			}
			if ($numAARows > 0) {
				foreach($wpw1_cwa_audio_assessment as $assessmentRow) {
					$thisStudent			= $assessmentRow->assessment_call_sign;
					$assessmentArray[]		= $thisStudent;
					if ($doDebug) {
						echo "added $thisStudent to assessmentArray<br />";
					}
				}
			}
		}
		
		// read student table and queue up the message(s) to be sent
		$sql				= "select * from $studentTableName where semester='$nextSemester' order by call_sign";
		$wpw1_cwa_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			if ($doDebug) {
				echo "Reading $studentTableName table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numSRows		= $wpdb->num_rows;
			if ($doDebug) {
				$myStr		= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numSRows rows from $studentTableName table<br />";
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
				
					if ($doDebug) {
						echo "<br />Processing $student_call_sign<br />
Response: $student_response<br />
First Class Choice: $student_first_class_choice<br />
copy control: $student_copy_control<br />";
					}
					if ($student_response == 'Y' && $student_copy_control != "assessment") {
						$assessmentMsg						= '';
						$scheduleMsg						= '';
						$sendAssessment						= FALSE;
						$sendSchedule						= FALSE;
						$sendEmail							= FALSE;
						if (!in_array($student_call_sign,$assessmentArray)) {
							if ($doDebug) {
								echo "Student gets the assessment message<br />";
							}
							$assessmentMsg					= "
<p>Since the time you signed up for the 2022 May/Jun semester, we have implemented a new system 
to do a Morse code 'self-evaluation'.  This will help you decide if you are in the right class 
based on your current Morse code skill level.</p>
<p>If you would like to check this out, please click on the following link:<br />
<a href='$siteURL/cwa-student-registration/'>SELF ASSESSMENT</a> and select Option 4.</p>
<p>There are no penalties or automatic changes.  You decide if you want to keep your existing 
class or move to one that might be a better fit for you.</p>";
							$sendEmail						= TRUE;
							$sendAssessment					= TRUE;
						}
						if ($student_first_class_choice == '' || $student_first_class_choice == 'None') {
							if ($doDebug) {
								echo "Student gets the schedule message<br />";
							}
							$scheduleMsg					= "
<p>You have no curent class schedule choices. You can update your class schedule choices by clicking<br />
<a href='$siteURL/cwa-student-registration/'>Student Registration</a> and select Option 3.</p>
<p>If you do not update your class schedule choices, you will be arbitrarily assigned to an advisor's class 
which may not suit your needs.</p>";
							$sendEmail						= TRUE;
							$sendSchedule					= TRUE;
						}
						if ($sendEmail) {
							if ($emailCount > 0) {
								$theSubject					= "CW Academy - New Self Assessment Capability";
								if ($testMode) {
									$theRecipient 			= 'rolandksmith@gmail.com';
									$mailCode				= 2;
									$theSubject				= "TESTMODE $theSubject";
									$increment++;
								} else {
									$theRecipient			= $student_email;
									$mailCode				= 11;
								}
								$theContent					= "To: $student_last_name, $student_first_name ($student_call_sign):";
								if ($sendAssessment) {
									$theContent				.= $assessmentMsg;
								}
								if ($sendSchedule) {
									$theContent				.= $scheduleMsg;
								}
								$theContent					.= "<p>73,<br />CW Academy</p>";
								$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
																			'theSubject'=>$theSubject,
																			'jobname'=>$jobname,
																			'theContent'=>$theContent,
																			'mailCode'=>$mailCode,
																			'increment'=>$increment,
																			'testMode'=>$testMode,
																			'doDebug'=>$doDebug));
 								if ($emailResult === FALSE) {
									echo "Mail failed to send<br />";
  								}
  								$emailCount--;
  								$emailsSent++;
  								if ($doDebug) {
  									echo "email sent to $student_call_sign<br />";
  								}
								$studentUpdateData		= array('tableName'=>$studentTableName,
																'inp_method'=>'update',
																'inp_data'=>array('copy_control'=>'assessment'),
																'inp_format'=>array('%s'),
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
  								}
							}
						}
					}
				}
			} else {
				$content	.= "No student records found in $studentTableName table";
			}
		}
		$content	.= "$emailsSent emails sent<br />";			
					
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('send_assessment_email', 'send_assessment_email_func');
