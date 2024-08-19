function manage_advisor_class_assignments_func() {

/*
	
	Provides an advisor the ability  to get the advisor's class(es) and confirm
	the students
	
	Created 21Nov23 by Roland from show_advisor_class_assignments
	Modified 3Jan24 by Roland to make the student unassigned if the advisor does not 
		want the student and does not want a replacement
	
*/	

	global $wpdb,$userName,$validTestmode;

	$doDebug					= FALSE;
	$testMode					= FALSE;
	$initializationArray 		= data_initialization_func();
	$validUser 					= $initializationArray['validUser'];
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	$versionNumber				= '1';
	$jobname					= "Manage Advisor Class Assignments V$versionNumber";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 					= $initializationArray['validUser'];
	$userName					= $initializationArray['userName'];
	$validTestmode				= $initializationArray['validTestmode'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$userName					= $initializationArray['userName'];
	$siteURL					= $initializationArray['siteurl'];	
	$validReplacementPeriod		= $initializationArray['validReplacementPeriod'];	
	
	$proximateSemester			= $currentSemester;
	if ($proximateSemester == 'Not in Session') {
		$proximateSemester		= $nextSemester;
	}
	if ($userName != 'administrator') {
		$doDebug		= FALSE;
		$testMode		= FALSE;
	}
	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
	} else {
		$wpdb->hide_errors();
	}

	ini_set('memory_limit','256M');
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "5";
	$studentArray				= array();
	$studentDataArray			= array();
	$totalStudents				= 0;
	$inp_call_sign				= "";
	$confirmationMsg			= '';
	$inp_attend					= '';
	$inp_comment_attend			= '';
	$inp_comment				= '';
	$inp_replacement			= '';
	$token						= '';
	$actionDate					= date('Y-m-d H:i:s');
	$updateStudentInfoURL		= "$siteURL/cwa-display-and-update-student-information/";
	$statusArray				= array('Y'=>'Verified',
										'S'=>'Assigned',
										'C'=>'Dropped');
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (is_array($str_value) === FALSE) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "submit") {
				$submit	 = $str_value;
				$submit	 = filter_var($submit,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_call_sign") {
				$inp_call_sign	 = $str_value;
				$inp_call_sign	 = filter_var($inp_call_sign,FILTER_UNSAFE_RAW);
				$inp_call_sign	= strtoupper($inp_call_sign);
			}
			if ($str_key 		== "student_call_sign") {
				$student_call_sign	 = $str_value;
				$student_call_sign	 = filter_var($student_call_sign,FILTER_UNSAFE_RAW);
				$student_call_sign	= strtoupper($student_call_sign);
			}
			if ($str_key 		== "token") {
				$token			 = $str_value;
				$token			 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_attend") {
				$inp_attend			 = $str_value;
				$inp_attend			 = filter_var($inp_attend,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_comment_attend") {
				$inp_comment_attend			 = $str_value;
				$inp_comment_attend			= str_replace("'","&apos;",$inp_comment_attend);
				$inp_comment_attend			 = filter_var($inp_comment_attend,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_comment") {
				$inp_comment			 = $str_value;
				$inp_comment			= str_replace("'","&apos;",$inp_comment);
				$inp_comment			 = filter_var($inp_comment,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_replace") {
				$inp_replace			 = $str_value;
				$inp_replace			 = filter_var($inp_replace,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "studentid") {
				$studentid			 = $str_value;
				$studentid			 = filter_var($studentid,FILTER_UNSAFE_RAW);
			}
		}
	}

	$firstTime						= TRUE;
	$advisor_call_sign				= '';
	$advisor_email					= '';
	$advisor_phone					= '';
	$theURL							= "$siteURL/cwa-manage-advisor-class-assignments/";
	$errorArray						= array();

	if ($testMode) {
		$studentTableName			= 'wpw1_cwa_consolidated_student2';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass2';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$thisMode					= 'TM';
		$theStatement				= "<p>Function is running in TEST MODE using test files.</p>";
	} else {
		$studentTableName			= 'wpw1_cwa_consolidated_student';
		$advisorTableName			= 'wpw1_cwa_consolidated_advisor';
		$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$thisMode					= 'PM';
		$theStatement				= "";
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

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at $strPass with <br />
				inp_call_sign: $inp_call_sign<br />
				student_call_sign: $student_call_sign<br />
				token: $token<br />";
		}

		///// get the student information
		$sql				= "select * from $studentTableName 
								where semester='$proximateSemester' 
								and call_sign='$student_call_sign'";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}
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
					$student_abandoned  					= $studentRow->abandoned;
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

				}
				/////// see if student is actually assigned to the advisor
				if ($student_assigned_advisor != $inp_call_sign) {
					$content	.= "Incompatible Information Entered.<br />";
					if ($doDebug) {
						echo "$student_call_sign assigned advisor of $student_assigned_advisor is not $inp_call_sign<br />";
					}
				} else {
					/////////// 	if student status is already C, R, or V, no sense in going further
					if ($student_student_status == 'C' || $student_student_status == 'R' || $student_student_status == 'V') {
						if ($doDebug) {
							echo "Student status is $student_student_status. No further action available<br />";
						}
						$strPass		= '5';		// redisplay the class
					} else {
				
						// display the form for the advisor to fill out and process the form in pass 3

						$content		.= "<h2>Advisor Confirmation of Student $student_call_sign</h2>
											<p><form method='post' action='$theURL' 
											name='verification_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='3'>
											<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
											<input type='hidden' name='student_call_sign' value='$student_call_sign'>
											<input type='hidden' name='studentid' value='$student_ID'>
											<input type='hidden' name='token' value='$token'>
											<table>
											<tr><td>Student $student_call_sign has been assigned to your class. After contacting 
													the student, please select any of the following that apply:</td></tr>
											<table style='border:4px solid green;'>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_attend' name='inp_attend' value='Yes' checked='checked'><b> 
													The student has responded and will attend my $student_semester Class</b></td></tr></table></td></tr>
											<table style='border:4px solid red;'>
											<tr><td style='vertical-align:top;'><b>STUDENT WILL NOT BE ATTENDING my $student_semester class for the following reason:</td></tr>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_attend' name='inp_attend' value='schedule'> 
													<b>Will Not Attend Due To Schedule Issues:</b><br />
													Student can not attend your class because of the scheduled time, but could take a class at a 
													different day or time.   
													Please find out when the student is available to take a class and enter that information below. <b>ONLY SELECT THIS ANSWER 
													if the student can indeed take a class at another time.</b> IF NOT, select 'Student Unable to Take a Class'.
													CW Academy will unassign 
													the student from your class and try to assign the student to a different class, based on your comments</td></tr>
											<tr><td><b>Advisor Comments (<em>Required</em>):</b><br />
													<textarea class=formInputText' name='inp_comment_attend' id='inp_comment_attend' rows='2' cols='50'></textarea></td></tr>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_attend' name='inp_attend' value='class'> 
													<b>Student Unable to Take the Class:</b><br />
													If the student is unable to take the class this semester due to circumstances like health issues or changes in schedules, 
													select this option. CW Academy will unassign the student from your class and the student will be unavailable 
													for reassignment. Comments would be helpful but not required.</td></tr>
											<tr><td><b>Advisor Comments (<em>optional</em>):</b><br />
													<textarea class=formInputText' name='inp_comment' id='inp_comment' rows='2' cols='50'></textarea></td></tr>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_attend' value='advisor'> 
													<b>Advisor Doesn't Want the Student:</b><br />
													If you don't want the student in your class for whatever reason, select this option. The student will be unassigned and 
													returned to the unassigned pool for possible assignment to another advisor's class. Comments would be helpful but 
													not required.</td></tr>
											<tr><td><b>Advisor Comments (<em>optional</em>):</b><br />
													<textarea class=formInputText' name='inp_comment' id='inp_comment' rows='2' cols='50'></textarea></td></tr>
											<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_attend' name='inp_attend' value='other'> 
													<b>Some Other Reason</b> (such as no responding)<br />
													Please enter as much information as you have about why. 
													The student will be marked as unavailable for reassignment.</td></tr>
											<tr><td><b>Advisor Comments (<em>Required</em>):</b><br />
													<textarea class=formInputText' name='inp_comment' id='inp_comment' rows='2' cols='50'></textarea></td></tr></table>";
						if ($validReplacementPeriod == 'Y') {
							$content		.= "<table style='border:4px solid blue;'>
												<tr><td style='vertical-align:top;width:500px;'><b>If the student will <em>NOT</em> be attending your class, do you want the 
												system to attempt to replace the student?</b><br /><br /><em>Note that there may not be any replacement students 
												available.</em></td>
													<td style='vertical-align:top;text-align:left;width:150px;'>
													<input type='radio' class='formInputButton' name='inp_replace' value='replace'> Yes<br />
													<input type='radio' class='formInputButton' name='inp_replace' value='no' checked> No</td></tr>
												</table>";
						} else {
							$content		.= "<table style='border:4px solid blue;'>
												<tr><td style='vertical-align:top;width:650px;'><b>No Replacement Students are available</b></td></tr>
												</table>
												<input type='hidden' name='inp_replace' value='no'>";
					
						}
						$content			.= "</td></tr>
												<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
												</table></form></p>";
					}
				}
			} else {
				if ($doDebug) {
					echo "No record found in $studentTableName pod for $student_call_sign<br />";
				}
				$strPass		= '5';			// redisplay the class
			}
		}

	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass $strPass<br />";
		}

		$strPass 		= '5';

/*	if the advisor says the student is attending (inp_attend = Yes)
		ignore the other responses
		set student status to Y
		set up the student action log
		set up the advisorclass action log
		Update the student record
		Update the advisorclass record
	if the advisor says the student is not attending (inp_attend != Yes)
		if the advisor says to replace the student
		 	if the reason is schedule (student to be put back in unassigned pool)
		 		set student remove_status to V
		 		setup the student action log
		 		setup the advisorclass action log
		 	if the reason is class (student not to be put back in unassigned pool)
		 		set the student remove_status to R
		 		Setup the student action log
		 		Setup the advisor action log
		 	if the reason is advisor (advisor does not want the student)
		 		Set the student remove status to R
		 		Setup the student action log
		 		Set up the advisor action log
		 	if the reason is other
		 		set the student revmoe_status to R
		 		setup the student action log
		 		setup the advisorclass action log
		If the advisor says not to replace the student
			if the reason is schedule
				set the student remove_status to blank
				set intervention required to H
				set hold reason code to X
				set excluded advisor to the advisor requesting the student
				update the student
				setup the advisorclass action log
				send an email to Bob
			if the reason is class
				set the student remove_status to C
				setup the student action log
				setup the advisorclass action log
			If the reason is advisor
				set the student remove_status to blank (unassigned)
				Set excljuded advisor 
				Setup the advisorClass action log
				Update the student
			If reason is other
				set the student remove_status to C
				setup the student action log
				setup the advisorclass action log
	If the advisor doesn't want the student, exclude the advisor from the student and 
		unassign the student
*/



		//////////	get the advisor record
		$sql				= "select * from $advisorTableName 
								where semester='$proximateSemester'
									and call_sign='$inp_call_sign'";
		$wpw1_cwa_advisor			= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
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
					$advisor_action_log 				= stripslashes($advisorRow->action_log);
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

					if ($userName == '') {
						$userName 		= $advisor_call_sign;
					}
		
		
				}
				if ($doDebug) {
					echo "Got $advisor_call_sign's records from $advisorTableName table<br />";
				}
			
				
				
				///// now get the student information
				$sql			= "select * from $studentTableName 
									where semester='$proximateSemester' 
									and student_id=$studentid";
				$wpw1_cwa_student				= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$lastError			= $wpdb->last_error;
					if ($lastError != '') {
						handleWPDBError($jobname,$doDebug);
						$content		.= "Fatal program error. System Admin has been notified";
						if (!$doDebug) {
							return $content;
						}
					}
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

						}
						$student_remove_status			= '';
						$myStr							= '';
						$updateFiles					= FALSE;
						$removeStudent					= FALSE;
						if ($inp_attend == 'Yes') {
							if ($doDebug) {
								echo "<br />attend is Yes. Updating student and advisor<br />";
							}
							$student_action_log			= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor confirmed student participation ";
							$updateParams				= array("student_status|Y|s", 
																"action_log|$student_action_log|s");
							$advisorReply				= "<p>You have confirmed that $student_call_sign will attend your  class.</p>";
							if ($doDebug) {
								echo "inp_attend is $inp_attend. Set status to Y and updated action log<br />";
							}
							$studentUpdateData		= array('tableName'=>$studentTableName,
															'inp_method'=>'update',
															'inp_data'=>$updateParams,
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
								handleWPDBError($jobname,$doDebug);
							} else {
								$lastError			= $wpdb->last_error;
								if ($lastError != '') {
									handleWPDBError($jobname,$doDebug);
									$content		.= "Fatal program error. System Admin has been notified";
									if (!$doDebug) {
										return $content;
									}
								}
								if ($doDebug) {
									echo "student record updated. Now updating advisor record<br />";
								}
								$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor confirmed $student_call_sign attendance ";
								$updateParams			= array("action_log|$advisor_action_log|s");
								$advisorUpdateData		= array('tableName'=>$advisorTableName,
																'inp_method'=>'update',
																'inp_data'=>$updateParams,
																'inp_format'=>array(''),
																'jobname'=>$jobname,
																'inp_id'=>$advisor_ID,
																'inp_callsign'=>$advisor_call_sign,
																'inp_semester'=>$advisor_semester,
																'inp_who'=>$userName,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug);
								$updateResult	= updateAdvisor($advisorUpdateData);
								if ($updateResult[0] === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$lastError			= $wpdb->last_error;
									if ($lastError != '') {
										handleWPDBError($jobname,$doDebug);
										$content		.= "Fatal program error. System Admin has been notified";
										if (!$doDebug) {
											return $content;
										}
									}
									if ($doDebug) {
										echo "advisor record successfully updated<br />";
									}
								}
								$confirmationMsg	= "Student $student_call_sign has been confirmed as attending<br />";
								if ($doDebug) {
									echo "strPass: $strPass<br />";
								}
							}

							
						} else {			// student not attending
							if ($doDebug) {
								echo "Student not attending<br />";
							}
							$updateFiles					= TRUE;
							$studentUpdateParams			= array();
							$advisorUpdateParams			= array();
							if ($inp_replace == 'replace') {			// asking for a replacement
								if ($inp_attend == 'schedule') {
									if ($doDebug) {
										echo "Doing Schedule; Replacement Yes<br />";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend due to schedule. Advisor comments: $inp_comment_attend. Replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $advisor_call_sign $student_call_sign will not attend due to schedule. Advisor comments: $inp_comment_attend. Replacement requested ";
									$studentUpdateParams[]	= "action_log|$student_action_log|s";
									$advisorUpdateParams[]	= "action_log|$advisor_action_log|s";
									$studentUpdateParams[]	= "student_status|V|s";
									$confirmationMsg		= "Student $student_call_sign confirmed as not attending and a replacement has been requested.<br />";
									$removeStudent				= FALSE;
								} elseif ($inp_attend == 'class') {
									if ($doDebug) {
										echo "Doing Class; Replacement Yes<br />";
									}
									if ($inp_comment != '') {
										$myStr				= "Advisor comments: $inp_coment ";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student does not want the class. $myStr Replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign does not want the class. $myStr Replacement requested ";
									$studentUpdateParams[]	= "action_log|$student_action_log|s";
									$advisorUpdateParams[]	= "action_log|$advisor_action_log|s";
									$studentUpdateParams[]	= "student_status|R|s";
									$confirmationMsg		= "Student $student_call_sign confirmed as not attending and a replacement has been requested.<br />";
									$removeStudent				= FALSE;
		 						} elseif ($inp_attend == 'advisor') {
									if ($doDebug) {
										echo "Doing advisor; Replacement Yes<br />";
									}
									if ($student_excluded_advisor == '') {
										$student_excluded_advisor	= $advisor_call_sign;
									} else {
										$student_excluded_advisor	= "$student_excluded_advisor|$advisor_call_sign";
									}
									if ($inp_comment != '') {
										$myStr				= "Advisor comments: $inp_coment ";
									}
									$student_excluded_advisor	= str_replace("|","&",$student_excluded_advisor);
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor does not want the student. $myStr Replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor does not want $student_call_sign. $myStr Replacement requested ";
									$studentUpdateParams[]	= "action_log|$student_action_log|s";
									$studentUpdateParams[]	= "excluded_advisor|$student_excluded_advisor|s";
									$studentUpdateParams[]	= "hold_reason_code|X|s";
									$advisorUpdateParams[]	= "action_log|$advisor_action_log|s";
									$studentUpdateParams[]	= "student_status|R|s";
									$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
									$removeStudent				= FALSE;
								} else {
									if ($doDebug) {
										echo "Doing Other; Replacement Yes<br />";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend. Advisor comments: $inp_comment. Replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign will not attend. Advisor comments: $inp_comment. Replacement requested ";
									$studentUpdateParams[]	= "action_log|$student_action_log|s";
									$advisorUpdateParams[]	= "action_log|$advisor_action_log|s";
									$studentUpdateParams[]	= "student_status|R|s";
									$confirmationMsg		= "Student $student_call_sign confirmed as not attending and a replacement has been requested.<br />";
									$removeStudent				= FALSE;
								}
							} else {
								if ($inp_attend == 'schedule') {
									if ($doDebug) {
										echo "Doing Schedule; Replacement No<br />";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign 
student will not attend due to schedule. Advisor comments: $inp_comment_attend. Unassigned from $student_assigned_advisor class $student_assigned_advisor_class. 
No replacement requested.  ";
									if ($student_excluded_advisor == '') {
										$student_excluded_advisor	= $advisor_call_sign;
									} else {
										$student_excluded_advisor	= "$student_excluded_advisor|$advisor_call_sign";
									}
									$student_excluded_advisor	= str_replace("|","&",$student_excluded_advisor);
									$studentUpdateParams[]		= "action_log|$student_action_log|s";
									$studentUpdateParams[]		= "hold_reason_code|X|s";
									$studentUpdateParams[]		= "class_priority|2|d";
									$studentUpdateParams[]		= "excluded_advisor|$student_excluded_advisor|s";
									$studentUpdateParams[]		= "intervention_required|H|s";
									$student_remove_status		= '';
									$removeStudent				= TRUE;
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM 
$student_call_sign will not attend due to schedule. Advisor comments: $inp_comment_attend. Unassigned from $student_assigned_advisor class $student_assigned_advisor_class. 
No replacement requested. ";
									$advisorUpdateParams	= array("action_log|$advisor_action_log|s");

								} elseif ($inp_attend == 'class') {
									if ($doDebug) {
										echo "Doing Class; Replacement No<br />";
									}
									if ($inp_comment != '') {
										$myStr				= "Advisor comments: $inp_coment ";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student does not want the class. $myStr No replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign does not want the class. $myStr No replacement requested ";
									$studentUpdateParams[]	= "action_log|$student_action_log|s";
									$advisorUpdateParams[]	= "action_log|$advisor_action_log|s";
									$student_remove_status	= 'C';
									$removeStudent			= TRUE;
									$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
								} elseif ($inp_attend == 'advisor') {
									if ($doDebug) {
										echo "Doing advisor; Replacement No<br />";
									}
									if ($student_excluded_advisor == '') {
										$student_excluded_advisor	= $advisor_call_sign;
									} else {
										$student_excluded_advisor	= "$student_excluded_advisor|$advisor_call_sign";
									}
									$student_excluded_advisor	= str_replace("|","&",$student_excluded_advisor);
									if ($inp_comment != '') {
										$myStr				= "Advisor comments: $inp_coment ";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor does not want the student. $myStr No replacement requested. Student set to unassigned ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor does not want $student_call_sign. $myStr No replacement requested ";
									$studentUpdateParams[]	= "action_log|$student_action_log|s";
									$studentUpdateParams[]	= "excluded_advisor|$student_excluded_advisor|s";
									$studentUpdateParams[]	= "hold_reason_code|X|s";
									$advisorUpdateParams[]	= "action_log|$advisor_action_log|s";
									$student_remove_status	= '';
									$removeStudent			= TRUE;
									$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
								} else {
									if ($doDebug) {
										echo "Doing Other; Replacement No<br />";
									}
									if ($inp_comment != '') {
										$myStr				= "Advisor comments: $inp_comment ";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend. $myStr No replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign will not attend. $myStr No replacement requested ";
									if ($student_excluded_advisor == '') {
										$student_excluded_advisor	= $advisor_call_sign;
									} else {
										$student_excluded_advisor	= "$student_excluded_advisor|$advisor_call_sign";
									}
									$studentUpdateParams[]	= "hold_reason_code|X|s";
									$studentUpdateParams[]	= "class_priority|2|d";
									$studentUpdateParams[]	= "excluded_advisor|$student_assigned_advisor|s";
									$studentUpdateParams[]	= "action_log|$student_action_log|s";
									$advisorUpdateParams[]	= "action_log|$advisor_action_log|s";
									$student_remove_status	= 'C';
									$removeStudent			= TRUE;
									$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
								}
							}
						}
						if ($removeStudent) {
						// remove the student
							$inp_data			= array('inp_student'=>$student_call_sign,
														'inp_semester'=>$student_semester,
														'inp_assigned_advisor'=>$student_assigned_advisor,
														'inp_assigned_advisor_class'=>$student_assigned_advisor_class,
														'inp_remove_status'=>$student_remove_status,
														'inp_arbitrarily_assigned'=>$student_no_catalog,
														'inp_method'=>'remove',
														'jobname'=>$jobname,
														'userName'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
				
							$removeResult		= add_remove_student($inp_data);
							if ($removeResult[0] === FALSE) {
								$thisReason		= $removeResult[1];
								if ($doDebug) {
									echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
								}
								sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
//								$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
							} else {
								if ($doDebug) {
									echo "student was successfully remived from the advisor's class<br />";
								}
//								$content		.= "Student removed from the advisor's class<br />";
							}
						}
						if ($updateFiles) {
							//// now update the student and write to the audit log
							$updateData	= array('tableName'=>$studentTableName,
												'inp_data'=>$studentUpdateParams,
												'inp_format'=>array(''),
												'inp_method'=>'update',
												'jobname'=>$jobname,
												'inp_id'=>$student_ID,
												'inp_callsign'=>$student_call_sign,
												'inp_semester'=>$student_semester,
												'inp_who'=>$userName,
												'testMode'=>$testMode,
												'doDebug'=>$doDebug);

							$updateResult				= updateStudent($updateData);
							if ($updateResult[0] === FALSE) {
								if ($doDebug) {
									echo "updating $student_call_sign table entry failed. Reason: $updateResult[1]";
								}
							} else {
								if ($doDebug) {
									echo "student record successfully updated<br />";
								}
							}
							// update the advisor
							$advisorUpdateData		= array('tableName'=>$advisorTableName,
															'inp_method'=>'update',
															'inp_data'=>$advisorUpdateParams,
															'inp_format'=>array(''),
															'jobname'=>$jobname,
															'inp_id'=>$advisor_ID,
															'inp_callsign'=>$advisor_call_sign,
															'inp_semester'=>$advisor_semester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateAdvisor($advisorUpdateData);
							if ($updateResult[0] === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$lastError			= $wpdb->last_error;
								if ($lastError != '') {
									handleWPDBError($jobname,$doDebug);
									$content		.= "Fatal program error. System Admin has been notified";
									if (!$doDebug) {
										return $content;
									}
								}
								if ($doDebug) {
									echo "updating $advisor_call_sign in $advisorTableName succeeded<br />";
								}
//								$content			.= "Student status verification was successful<br />";
							}
						}
					}
				}
			}
		}
		$strPass		= '5';


	}
	if ("5" == $strPass) {
	
		if ($doDebug) {
			echo "At pass $strPass with<br />
				  inp_call_sign: $inp_call_sign<br />
				  token: $token<br />";
		}
		
		if ($inp_call_sign == '') {
			$inp_call_sign	= strtoupper($userName);
			$token			= '';
		}
		$unconfirmedCount	= 0;
		$content			.= "$confirmationMsg 
								<h4>$inp_call_sign Classes and Students for $proximateSemester Semester</h4>
								<div><p><table style='width:900px;'>
								<tr><td>If you would like a .csv dump of the students assigned 
										to your $proximateSemester semester classes, please click</td>
									<td><form method='post' action='$theURL' target='_blank' 
										name='dump_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='10'>
										<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
										<input class='formInputButton' type='submit' name='submit' value='Prepare CSV' /></td></tr></table></p></div>";
		
		$sql	= "select * from $advisorClassTableName 
				where advisor_call_sign = '$inp_call_sign' 
				  and semester = '$proximateSemester' 
				order by sequence";


		$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				if (!$doDebug) {
					return $content;
				}
			}
			$numACRows						= $wpdb->num_rows;
			if ($doDebug) {
				$myStr						= $wpdb->last_query;
				echo "ran $myStr<br />and found $numACRows rows<br />";
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

					if ($doDebug) {
						echo "have advisorclass sequence $advisorClass_sequence record<br />";
					}
					$content	.= "<b>Class $advisorClass_sequence:</b>
									<table style='width:900px;'>
									<tr><td style='vertical-align:top;width:300px;'><b>Sequence</b><br />
											$advisorClass_sequence</td>
										<td style='vertical-align:top;width:300px;'><b>Level</b><br />
											$advisorClass_level</td>
										<td style='vertical-align:top;'><b>Class Size</b><br />
											$advisorClass_class_size</td></tr>
									<tr><td style='vertical-align:top;'><b>Class Teaching Days</b><br />
											$advisorClass_class_schedule_days</td>
										<td style='vertical-align:top;' colspan='2'><b>Local Start Time</b><br />
											$advisorClass_class_schedule_times</td></tr></table>";

					// if there are students, then show the student name list
					if ($doDebug) {
						echo "there are $class_number_students students in the class<br />";
					}
					$displayClass			= FALSE;										
					if ($class_number_students > 0) {	
						// put out the student header
						$content				.= "<table style='border-collapse:collapse;'>";
						
						$daysToGo				= days_to_semester($proximateSemester);
						if($doDebug) {
							echo "preparing to display student info. Days to $advisorClass_semester semester: $daysToGo<br />";
						}
						if ($daysToGo > 0 && $daysToGo < 21) {
							$content				.= "<p>Students have been assigned to classes for the $advisorClass_semester semester. 
														The semester has not yet started. You have been assigned the 
														following students:</p>\n";
							$displayClass		= TRUE;
						} elseif ($daysToGo < 0 && $daysToGo > 20) {
							$content				.= "<p>Students have not yet been assigned to advisor classes.</p>";
						} elseif ($daysToGo <0 && $daysToGo > -60) {								
							$content				.= "<p>Students have been assigned to classes for the $advisorClass_semester semester. 
														The semester is underway. You have been assigned the 
														following students:</p>\n";
							$displayClass			= TRUE;
						} else {
							$content				.= "<p>Students have been assigned to classes for the $proximateSemester semester. 
														The semester is completed. You were assigned the 
														following students:</p>\n";
							$displayClass			= TRUE;
						}
						if ($displayClass) {
							if ($doDebug) {
								echo "displaying class information<br />";
							}
							$studentCount			= 0;
							for ($snum=1;$snum<31;$snum++) {
								if ($snum < 10) {
									$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
								} else {
									$strSnum		= strval($snum);
								}
								$theInfo			= ${'advisorClass_student' . $strSnum};
								if ($theInfo != '') {
									if ($doDebug) {
										echo "<br />processing student$strSnum $theInfo<br />";
									}
									$wpw1_cwa_student				= $wpdb->get_results("select * from $studentTableName 
																							where semester='$proximateSemester' 
																							and call_sign = '$theInfo'");
									if ($wpw1_cwa_student === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {
										$lastError			= $wpdb->last_error;
										if ($lastError != '') {
											handleWPDBError($jobname,$doDebug);
											$content		.= "Fatal program error. System Admin has been notified";
											if (!$doDebug) {
												return $content;
											}
										}

										$numSRows									= $wpdb->num_rows;
										if ($doDebug) {
											$myStr					= $wpdb->last_query;
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


												if ($doDebug) {
													echo "<br />Processing student $student_call_sign<br />
															&nbsp;&nbsp;&nbsp;&nbsp;Level: $student_level<br />
															&nbsp;&nbsp;&nbsp;&nbsp;Class: $student_assigned_advisor_class<br />
															&nbsp;&nbsp;&nbsp;&nbsp;Status: $student_student_status<br />
															&nbsp;&nbsp;&nbsp;&nbsp;Promotable: $student_promotable<br />";
												}
												
												if ($student_student_status == 'S' || $student_student_status == 'Y') {
													$studentCount++;
													$content			.= "<tr><td style='vertical-align:top;width:100px;'><b>Call Sign</b></td>\n
																				<td style='vertical-align:top;width:150px;'><b>Name</b></td>\n
																				<td style='vertical-align:top;width:200px;'><b>Email</b></td>\n
																				<td style='vertical-align:top;width:200px;'><b>Phone</b></td>\n
																				<td style='vertical-align:top;width:100px;'><b>Country</b></td>\n
																				<td style='vertical-align:top;width:100px;'><b>Action</b></td></tr>\n";
													/// check to see if there are assessment records for this student
													if ($doDebug) {
														echo "looking for audio assessment records<br />";
													}
													$hasAssessment			= FALSE;
													$assessment_count	= $wpdb->get_var("select count(record_id) 
																			   from $audioAssessmentTableName 
																				where call_sign='$student_call_sign'");
													if ($assessment_count > 0) {
														$hasAssessment	= TRUE;
														if ($doDebug) {
															echo "have assessment records<br />";
														}
													}
													$newAssessmentCount		= $wpdb->get_var("select count(record_id) 
																			   from $newAssessmentData 
																				where callsign='$student_call_sign'");
																				
													if ($newAssessmentCount > 0) {
														$hasAssessment	= TRUE;
														if ($doDebug) {
															echo "have assessment records<br />";
														}
													}
																				
													$extras							= "Additional contact options: ";
													$haveExtras						= FALSE;
													if ($student_whatsapp != '' ) {
														$extras						.= "WhatsApp: $student_whatsapp ";
														$haveExtras					= TRUE;
													}
													if ($student_signal != '' ) {
														$extras						.= "Signal: $student_signal ";
														$haveExtras					= TRUE;
													}
													if ($student_telegram != '' ) {
														$extras						.= "Telegram: $student_telegram ";
														$haveExtras					= TRUE;
													}
													if ($student_messenger != '' ) {
														$extras						.= "Messenger: $student_messenger ";
														$haveExtras					= TRUE;
													}
				

													$myStr							= "";
													if ($student_student_status == 'S') {
														$unconfirmedCount++;
														$myStr					= "<table style='border:4px solid red;'>
																					<tr><td><p><a href='$theURL/?strpass=1&inp_call_sign=$advisorClass_advisor_call_sign&student_call_sign=$student_call_sign&token=$token'>Confirm 
																					$student_call_sign</a></p></td></tr></table>\n";
													} elseif ($student_student_status == 'Y') {
														$myStr					= "Confirmed<br /><a href='$theURL/?strpass=1&inp_call_sign=$advisorClass_advisor_call_sign&student_call_sign=$student_call_sign&token=$token'>Change $student_call_sign Confirmation</a>\n";
													}
													if ($doDebug) {
														echo "displaying student $student_call_sign information<br />";
													}
													$content						.= "<tr><td style='vertical-align:top;'>$student_call_sign</td>\n
																							<td style='vertical-align:top;'>$student_last_name, $student_first_name</td>\n
																							<td style='vertical-align:top;'>$student_email</td>\n
																							<td style='vertical-align:top;'>+$student_ph_code $student_phone ($student_messaging)</td>\n
																							<td style='vertical-align:top;'>$student_country</td>\n
																							<td style='vertical-align:top;'>$myStr</td></tr>\n";
													if ($haveExtras) {
														$content					.= "<tr><td colspan='7'>$extras</td></tr>";
													}
													$thisParent			= '';
													$thisParentEmail	= '';
													if ($student_youth == 'Yes') {
														if ($student_age < 18) { 
															if ($student_student_parent == '') {
																$thisParent	= 'Not Given';
															} else {
																$thisParent	= $student_student_parent;
															}
															if ($student_student_parent_email == '') {
																$thisParentEmail = 'Not Given';
															} else {
																$thisParentEmail = $student_student_parent_email;
															}
															$content	.= "<tr><td colspan='7'>The student has registered as a youth under the age of 18. The student's 
																			parent or guardian is $thisParent at email address $thisParentEmail.</td></tr>\n";
														}
													}

													if ($hasAssessment) {
														$enstr		= base64_encode("advisor_call_sign=$student_assigned_advisor&inp_callsign=$student_call_sign");
											 			$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>Click <a href='$siteURL/cwa-view-a-student-cw-assessment-v2/?strpass=2&enstr=$enstr' target='_blank'>HERE</a> to review $student_call_sign's self assessment</td></tr>\n";
													} else {
														$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>&nbsp;</td></tr>";
													}
												}
											}	/// end of the student foreach
										} else {		/// no student record found ... send error message
											sendErrorEmail("Prepare Advisor Class Display: no $studentTableName table record found for student$strSnum in advisor $advisorClass_advisor_call_sign class $advisorClass_sequence");
										}
									}	
								}
							}
							$content				.= "</table>$studentCount Students<br /><br />";
							if ($doDebug) {
								echo "have processed all students for this class<br /><br />";
							}
						}
					} else {
						$content					.= "<p>No students are currently assigned to this class.</p>";
						if ($doDebug) {
							echo "No students assigned to this class<br /><br />";
						}
					}
				}			/// have processed all advisor classes. See if reminder to be resolved
				if ($unconfirmedCount == 0 && $token != '') {
					if ($doDebug) {
						echo "DELETE THE TOKEN HERE<br />";
					}
					$resolveResult				= resolve_reminder($inp_call_sign,'studentConfirmation',$testMode,$doDebug);
					if ($resolveResult === FALSE) {
						if ($doDebug) {
							echo "resolve_reminder for $inp_callsign and $token failed<br />";
						}
					}
					
				} else {
					echo "Still have $unconfirmedCount unconfirmed students<br />";
				}
			}
		}




	} elseif ("10" == $strPass) {

		if ($doDebug) {
			echo "<br />arrived at pass $strPass<br />
			advisor: $inp_call_sign<br />
			semester: $proximateSemester<br />
			submit: $submit<br />";
			
		}
		if ($submit == 'Prepare CSV') {
			// get all students assigned to this advisor for the specified semester
			$sql			= "select * from $studentTableName 
								where assigned_advisor = '$inp_call_sign' 
								and semester = '$proximateSemester' 
								and response = 'Y' 
								and (student_status = 'S' or student_status = 'Y') 
								order by assigned_advisor_class, 
								call_sign";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$lastError			= $wpdb->last_error;
				if ($lastError != '') {
					handleWPDBError($jobname,$doDebug);
					$content		.= "Fatal program error. System Admin has been notified";
					if (!$doDebug) {
						return $content;
					}
				}

				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
			
					$content		.= "<h3>$jobname</h3>
										<h4>CSV Dump of Students in $inp_call_sign $proximateSemester Semester Class(es)</h4>
										<p>The table below lists your students by class number in student call sign 
										order. The fields are separated by tabs. The first row are the field names.</p>
										<pre>class\tcall_sign\tfirst_name\tlast_name\temail\tphone\tstate\tcountry\twhatsapp\tsignal\ttelegram\tmessenger\n";
			
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
						$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);

						$content			.= "$student_assigned_advisor_class\t$student_call_sign\t$student_first_name\t$student_last_name\t$student_email\t+$student_ph_code $student_phone\t$student_state\t$student_country\t$student_whatsapp\t$student_signal\t$student_telegram\t$student_messenger\n";	
					}
					$content				.= "</pre><br />
												<p>To use this data on a Windows computer do the following:<br />
												1. Highlight the information in the table above<br />
												2. Copy this data to the clipboard (highlight and press control-c)<br />
												3. Right click on the desktop and select New --> Text Document<br />
												4. Name the document<br />
												5. Double click on the document<br />
												6. Paste the clipboard into the document and save the document<br />
												7. Open Excel and import the newly created document <i>(Click on Data --> From Text/CSV)</i></p>";
				} else {
					$content				.= "no students found<br />";
				}	
			}	
		}
		
	} elseif ("20" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at $strPass pass<br />";
		}
		// get the advisor callsign and go to pass 5 to do the work
		$content		.= "<h3>$jobname</h3>
							<p>Enter the advisor's callsign and click 'Submit' to 
							see what the advisor sees.</p>
							<form method='post' action='$theURL' 
							name='fake_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='5'>
							<input type='hidden' name='token' value=''>
							<table style='width:auto;'>
							<tr><td>Advisor Callsign</td>
								<td><input type='text' class='formInputText' size='15' maxlength = '15' name='inp_call_sign'></td></tr>
							$testMode
							<tr><td colspan='2'><input class='formInputButton' type='submit' name='submit' value='Submit' /></td></tr></table></form>";
		
	}
	$thisTime 					= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>V$versionNumber. Prepared at $thisTime</p>";
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
add_shortcode ('manage_advisor_class_assignments', 'manage_advisor_class_assignments_func');
