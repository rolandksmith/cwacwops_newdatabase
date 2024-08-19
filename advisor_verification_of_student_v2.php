function advisor_verification_of_student_v2_func() {

/*	This is incoming information from the advisor after contacting the student 
	assigned to the advisor's class
	
	Input: 	advisor_call_sign
			student_call_sign
			mode				(TM: test; PM: production)
			
	Modified 15Apr23 by Roland to correct action log handling
	Modified 12Jul23 by Roland to use current files only
	Modified 31Aug23 by Roland to turn dodebug and testmode off if validUser is N
*/

	global $wpdb, $testMode, $doDebug;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser				= $initializationArray['validUser'];
	$currentTimestamp		= $initializationArray['currentTimestamp'];
	$validReplacementPeriod	= $initializationArray['validReplacementPeriod'];
	$siteURL				= $initializationArray['siteurl'];
	$thisVersion			= '2';
	
/*	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
*/

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$testEmail					= "rolandksmith@gmail.com";
//	$testEmail					= "kcgator@gmail.com";
	$productionEmail			= "kcgator@gmail.com";
	$strPass					= "1";
	$theURL						= "$siteURL/cwa-advisor-verification-of-student/";
	$jobname					= "Advisor Verification of Student V$thisVersion";
	$advisor_call_sign			= '';
	$student_call_sign			= '';
	$mode						= '';
	$debugging					= '';
	$advisorid					= '';
	$studentid					= '';
	$inp_attend					= '';
	$inp_replace				= '';
	$increment					= 0;
	$logDate					= date('Y-m-d H:i',$currentTimestamp);
	$actionDate					= date('dMY Hi',$currentTimestamp);
	$userName					= $initializationArray['userName'];
	$nextSemester				= $initializationArray['nextSemester'];
	$currentSemester			= $initializationArray['currentSemester'];	
	$fieldTest					= array('action_log','post_status','post_title','control_code');
	$inp_semester				= '';




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
			if ($str_key 		== "advisor_call_sign") {
				$advisor_call_sign	 = $str_value;
				$advisor_call_sign	 = filter_var($advisor_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "advisorCallSign") {
				$advisor_call_sign	 = $str_value;
				$advisor_call_sign	 = filter_var($advisor_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "student_call_sign") {
				$student_call_sign	 = $str_value;
				$student_call_sign	 = filter_var($student_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "studentCallSign") {
				$student_call_sign	 = $str_value;
				$student_call_sign	 = filter_var($student_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "advisorid") {
				$advisorid	 = $str_value;
				$advisorid	 = filter_var($advisorid,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "studentid") {
				$studentid	 = $str_value;
				$studentid	 = filter_var($studentid,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_attend") {
				$inp_attend	 = $str_value;
				$inp_attend	 = filter_var($inp_attend,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_comment") {
				$inp_comment	 = $str_value;
				$inp_comment	 = filter_var($inp_comment,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_comment_attend") {
				$inp_comment_attend	 = $str_value;
				$inp_comment_attend	 = filter_var($inp_comment_attend,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_replace") {
				$inp_replace	 = $str_value;
				$inp_replace	 = filter_var($inp_replace,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "mode") {
				$mode			 = $str_value;
				$mode	 		= strtoupper(filter_var($mode,FILTER_UNSAFE_RAW));
				if ($mode == 'TM') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "debugging") {
				$debugging		= $str_value;
				$debugging	 	= filter_var($debugging,FILTER_UNSAFE_RAW);
				if ($debugging == 'Yes') {
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
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$studentTableName				= "wpw1_cwa_consolidated_student2";
	} else {
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$studentTableName				= "wpw1_cwa_consolidated_student";
	}

	if ($inp_semester == '') {
		if ($currentSemester == 'Not in Session') {
			$theSemester				= $nextSemester;
		} else {
			$theSemester				= $currentSemester;
		}
	} else {
		$theSemester					= $inp_semester;
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		if ($validUser == "N") {
			return "YOU'RE NOT AUTHORIZED!<br />Goodby";
		} else {
			$content 		.= "<h3>$jobname</h3>
<p>This function is normally run through a link provided to the advisor 
in the email informing the advisor of the student assignments to the 
advisor's class.<p>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
Advisor Call Sign:<br />
<input type='text' class='formInputText' name='advisor_call_sign' size='15' maxlength='15'><br />
Student Call Sign:<br />
<input type='text' class='formInputText' name='student_call_sign' size='15' maxlength='15'><br /><br />
Mode:<br />
<input type='radio' class='formInputButton' name='mode' value='TM'> TestMode<br />
<input type='radio' class='formInputButton' name='mode' value='PM'> Production<br /><br />
Verbosity:<br />
<input type='radio' class='formInputButton' name='debugging' value='Yes'> Verbose<br />
<input type='radio' class='formInputButton' name='debugging' value='No'> Non-verbose<br /><br />
<input class='formInputButton' type='submit' value='Submit' />
</form></p>";
		}	

//////////////////////////		pass 2


///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass2 with:<br />
advisor_call_sign: $advisor_call_sign<br />
student_call_sign: $student_call_sign<br />
mode: $mode<br />
verbosity: $debugging<br />";
		}
		
		if ($userName == '') {
			$userName 		= $advisor_call_sign;
		}
		////// get the advisor information
		$sql				= "select * from $advisorTableName 
								where semester='$theSemester' 
									and call_sign='$advisor_call_sign'";
		$wpw1_cwa_advisor		= $wpdb->get_results($sql);
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
					echo "Got $advisor_call_sign record from $advisorTableName table<br />";
				}
				///// now get the student information
				$sql				= "select * from $studentTableName 
										where semester='$theSemester' 
										and call_sign='$student_call_sign'";
				$wpw1_cwa_student		= $wpdb->get_results($sql);
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
						if ($student_assigned_advisor != $advisor_call_sign) {
							$content	.= "Incompatible Information Entered.<br />";
							if ($doDebug) {
								echo "$student_call_sign assigned advisor of $student_assigned_advisor is not $advisor_call_sign<br />";
							}
						} else {
						/////////// 	if student status is already C, R, or V, no sense in going further
							if ($student_student_status == 'C' || $student_student_status == 'R' || $student_student_status == 'V') {
								if ($doDebug) {
									echo "Student status is $student_student_status. No further action available<br />";
								}
								$content	.= "<h2>Advisor Verification of Student $student_call_sign</h2>
												<p>Student $student_call_sign was previous confirmed as <b>not attending</b>. If that status 
												needs to change, please contact the appropriate person at 
												<a href='https://cwops.org/cwa-class-resolution/' target='_blank'>CW Academy Class Resolution</a>.</p>";
							} else {
						
/*	Have the student and advisor records and the student is assigned to the advisor.
			display the form for the advisor to fill out and process the form in pass 3
*/

								/////// has the student already been confirmed?
								$myStr			= '';
								if ($student_student_status == 'Y') {
									$myStr		= "<p>Student was previously confirmed as attending. 
													If that is still the case, you can close this window. Otherwise, indicate the new status below.</p>";
								}
								$content		.= "<h2>Advisor Verification of Student $student_call_sign</h2>
													$myStr
													<p><form method='post' action='$theURL' 
													name='verification_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='3'>
													<input type='hidden' name='advisor_call_sign' value='$advisor_call_sign'>
													<input type='hidden' name='student_call_sign' value='$student_call_sign'>
													<input type='hidden' name='advisorid' value='$advisor_ID'>
													<input type='hidden' name='studentid' value='$student_ID'>
													<input type='hidden' name='inp_semester' value='$theSemester'>
													<input type='hidden' name='mode' value='$mode'>
													<input type='hidden' name='debugging' value='$debugging'>
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
															Student can not attend your class because of the scheduled time. 
															Enter the reason in the comments field below. Please include as much information as possible, 
															including when the student could take a class. CW Academy will unassign 
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
// onclick=\"return validate_form(this.form);\"
							}
						}
					} else {
						$content	.= "<p>The student is no longer assigned to you.</p>";
						if ($doDebug) {
							echo "No record found in $studentTableName pod for $student_call_sign<br />";
						}
					}
				}
			} else {
				$content	.= "Incomplete Information Entered. If you get this message, please take a screenshot and forward
it to rolandksmith@gmail.com. Let me know the student call sign you were trying to verify. Thanks! Roland K7OJL CW Academy Programmer<br />";
				$myStr				= $wpdb->last_query;
				$myStr1				= $wpdb->last_error;
				$myIP				= get_the_user_ip();
				if ($doDebug) {
					echo "no record found in $advisorTableName pod for $advisor_call_sign<br />";
				}
				$errorString	= "Program: advisor_verification_of_student.</p><p>At pass 2. No record found in $advisorTableName for $advisor_call_sign. 
student_call_sign: $student_call_sign<br />SQL: $myStr<br />Last error: $myStr1<br />IP Address: $myIP";
				sendErrorEmail($errorString);
			}
		}


//////////////////////////		pass 3

				
	} elseif ("3" == $strPass) {
	
		if ($doDebug) {
			echo "Arrived at pass 3 with:<br />
					advisor_call_sign: $advisor_call_sign ($advisorid)<br />
					student_call_sign: $student_call_sign ($studentid)<br />
					mode: $mode<br />
					inp_attend: $inp_attend<br />
					inp_replace: $inp_replace<br />";
		}

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
			If reason is other
				set the student remove_status to C
				setup the student action log
				setup the advisorclass action log
	If the advisor doesn't want the student, exclude the advisor from the student and 
		unassign the student
*/

//////////	get the advisor record
		$sql				= "select * from $advisorTableName 
								where semester='$theSemester'
									and advisor_id=$advisorid";
		$wpw1_cwa_advisor			= $wpdb->get_results($sql);
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
									where semester='$theSemester' 
									and student_id=$studentid";
				$wpw1_cwa_student				= $wpdb->get_results($sql);
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
						$updateFiles					= FALSE;
						if ($inp_attend == 'Yes') {
							if ($doDebug) {
								echo "<br />attend is Yes. Updating student and advisor<br />";
							}
							$student_action_log			= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor confirmed student participation ";
							$updateParams				= array("student_status|Y|s", 
																"action_log|$student_action_log|s");
							$updateFormat				= array('');
							$advisorReply				= "<p>You have confirmed that $student_call_sign will attend your  class.</p>";
							if ($doDebug) {
								echo "inp_attend is $inp_attend. Set status to Y and updated action log<br />";
							}
							$studentUpdateData		= array('tableName'=>$studentTableName,
															'inp_method'=>'update',
															'inp_data'=>$updateParams,
															'inp_format'=>$updateFormat,
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
									echo "student record updated. Now updating advisor record<br />";
								}
								$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor confirmed $student_call_sign attendance ";
								$updateParams			= array("action_log|$advisor_action_log|s");
								$updateFormat			= array('');
								$advisorUpdateData		= array('tableName'=>$advisorTableName,
																'inp_method'=>'update',
																'inp_data'=>$updateParams,
																'inp_format'=>$updateFormat,
																'jobname'=>$jobname,
																'inp_id'=>$advisor_ID,
																'inp_callsign'=>$advisor_call_sign,
																'inp_semester'=>$advisor_semester,
																'inp_who'=>$userName,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug);
								$updateResult	= updateAdvisor($advisorUpdateData);
								if ($updateResult[0] === FALSE) {
									$myError	= $wpdb->last_error;
									$mySql		= $wpdb->last_query;
									$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorNewTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
									if ($doDebug) {
										echo $errorMsg;
									}
									sendErrorEmail($errorMsg);
									$content		.= "Unable to update content in $advisorNewTableName<br />";
								} else {
									if ($doDebug) {
										echo "advisor record successfully updated<br />";
									}
								}
							}

							
						} else {			// student not attending
							if ($doDebug) {
								echo "Student not attending<br />";
							}
							$updateFiles					= TRUE;
							if ($inp_replace == 'replace') {			// asking for a replacement
								if ($inp_attend == 'schedule') {
									if ($doDebug) {
										echo "Doing Schedule; Replacement Yes<br />";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend due to schedule. Advisor comments: $inp_comment_attend. Replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $advisor_call_sign $student_call_sign will not attend due to schedule. Advisor comments: $inp_comment_attend. Replacement requested ";
									$studentUpdateParams	= array("action_log|$student_action_log|s");
									$studentFormatParams	= array('');
									$advisorUpdateParams	= array("action_log|$advisor_action_log|s");
									$advisorFormatParams	= array('');
									$student_remove_status	= 'V';
									$advisorReply			= "You have confirmed that student $student_call_sign will NOT be attending your class. 
																The student will be placed on the waiting list to possibly be assigned to a different advisor. 
																You have requested a replacement. Thanks for putting in a useful comment.</p>";
								} elseif ($inp_attend == 'class') {
									if ($doDebug) {
										echo "Doing Class; Replacement Yes<br />";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student does not want the class. Replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign does not want the class. Replacement requested ";
									$studentUpdateParams	= array("action_log|$student_action_log|s");
									$studentFormatParams	= array('');
									$advisorUpdateParams	= array("action_log|$advisor_action_log|s");
									$advisorFormatParams	= array('');
									$student_remove_status	= 'R';
									$advisorReply			= "You have confirmed that student $student_call_sign will NOT be attending your  class. 
																You have requested a replacement.</p>";
								} else {
									if ($doDebug) {
										echo "Doing Other; Replacement Yes<br />";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend. Advisor comments: $inp_comment. Replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign will not attend. Advisor comments: $inp_comment. Replacement requested ";
									$studentUpdateParams	= array("action_log|$student_action_log|s");
									$studentFormatParams	= array('');
									$advisorUpdateParams	= array("action_log|$advisor_action_log|s");
									$advisorFormatParams	= array('');
									$student_remove_status	= 'R';
									$advisorReply			= "You have confirmed that student $student_call_sign will NOT be attending your  class. 
																You have requested a replacement. Thanks for putting in a useful comment.</p>";
								}
							} else {
								if ($inp_attend == 'schedule') {
									if ($doDebug) {
										echo "Doing Schedule; Replacement No<br />";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign 
student will not attend due to schedule. Advisor comments: $inp_comment_attend. Unassigned from $student_assigned_advisor class $student_assigned_advisor_class. 
No replacement requested. Email sent to Bob ";
									if ($student_excluded_advisor == '') {
										$student_excluded_advisor	= $advisor_call_sign;
									} else {
										$student_excluded_advisor	= "$student_excluded_advisor|$advisor_call_sign";
									}
									$student_excluded_advisor	= str_replace("|","&",$student_excluded_advisor);
									$studentUpdateParams	= array("action_log|$student_action_log|s",
																	"hold_reason_code|X|s",
																	"class_priority|2|d",
																	"excluded_advisor|$student_excluded_advisor|s",
																	"intervention_required|H|s");
									$studentFormatParams	= array('');
									$student_remove_status	= '';
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM 
$student_call_sign will not attend due to schedule. Advisor comments: $inp_comment_attend. Unassigned from $student_assigned_advisor class $student_assigned_advisor_class. 
No replacement requested. Email sent to Bob ";
									$advisorUpdateParams	= array("action_log|$advisor_action_log|s");
									$advisorFormatParams	= array('');
									$advisorReply			= "You have confirmed that student $student_call_sign will NOT be attending your class. 
																The student will be placed on the waiting list for possible assignment to a different advisor. You did not request a replacement. Thanks for putting in a useful comment.</p>";

									$theSubject			= "CW Academy Recycled Replacement Student";
									if ($testMode) {
										$theRecipient		= 'rolandksmith@gmail.com';
										$mailCode			= 2;
										$theSubject			= "TESTMODE $theSubject";
										$increment++;
									} else {
										$mailCode			= 17;
										$theRecipient		= 'rolandksmith@gmail.com';
									}
									$email_content			= "<p>Student $student_last_name, $student_first_name ($student_call_sign) 
was not able to take the $student_level class from advisor $student_assigned_advisor due to 
scheduling issues. The advisor comments were: $inp_comment_attend The student is now on hold waiting further action.</p>
Student action Log: $student_action_log</p>";
									$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
																				'theSubject'=>$theSubject,
																				'jobname'=>$jobname,
																				'theContent'=>$email_content,
																				'mailCode'=>$mailCode,
																				'increment'=>$increment,
																				'testMode'=>$testMode,
																				'doDebug'=>$doDebug));
									if ($mailResult === FALSE) {
										sendErrorEmail("Email to Bob about student $student_call_sign from advisor_verification_of_student. Advisor: $advisor_call_sign");
									}
								} elseif ($inp_attend == 'class') {
									if ($doDebug) {
										echo "Doing Class; Replacement No<br />";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student does not want the class. No replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign does not want the class. No replacement requested ";
									$studentUpdateParams	= array("action_log|$student_action_log|s");
									$studentFormatParams	= array('');
									$advisorUpdateParams	= array("action_log|$advisor_action_log|s");
									$advisorFormatParams	= array('');
									$student_remove_status	= 'C';
									$advisorReply			= "You have confirmed that student $student_call_sign will NOT be attending your  class. 
You did not request a replacement.</p>";
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
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor does not want the student. No replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor does not want $student_call_sign. No replacement requested ";
									$studentUpdateParams	= array("action_log|$student_action_log|s",
																	"excluded_advisor|$student_excluded_advisor|S",
																	"hold_reason_code|X|s");
									$studentFormatParams	= array('');
									$advisorUpdateParams	= array("action_log|$advisor_action_log|s");
									$advisorFormatParams	= array('');
									$student_remove_status	= '';
									$advisorReply			= "You have confirmed that student $student_call_sign will NOT be attending your  class. 
You did not request a replacement.</p>";
								} else {
									if ($doDebug) {
										echo "Doing Other; Replacement No<br />";
									}
									$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend. Advisor comments: $inp_comment. No replacement requested ";
									$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign will not attend. Advisor comments: $inp_comment. No replacement requested ";
									if ($student_excluded_advisor == '') {
										$student_excluded_advisor	= $advisor_call_sign;
									} else {
										$student_excluded_advisor	= "$student_excluded_advisor|$advisor_call_sign";
									}
									$studentUpdateParams	= array("hold_reason_code|X|s",
																	"class_priority|2|d",
																	"excluded_advisor|$student_assigned_advisor|s",
																	"action_log|$student_action_log|s");
									$studentFormatParams	= array('');
									$advisorUpdateParams	= array("action_log|$advisor_action_log|s");
									$advisorFormatParams	= array('');
									$student_remove_status	= 'C';
									$advisorReply			= "You have confirmed that student $student_call_sign will not attend your class. 
There will be no replacement. Thanks for putting in a useful comment.</p>";
								}
							}
						}
						if ($updateFiles) {
							//// now update the student and write to the audit log
							$updateData	= array('tableName'=>$studentTableName,
												'inp_data'=>$studentUpdateParams,
												'inp_format'=>$studentFormatParams,
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
															'inp_format'=>$advisorFormatParams,
															'jobname'=>$jobname,
															'inp_id'=>$advisor_ID,
															'inp_callsign'=>$advisor_call_sign,
															'inp_semester'=>$advisor_semester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateAdvisor($advisorUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "$jobname Processing $advisor_call_sign in $advisorTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to update content in $advisorTableName<br />";
							} else {
								if ($doDebug) {
									echo "updating $advisor_call_sign in $advisorTableName succeeded<br />";
								}
//								$content			.= "Student status verification was successful<br />";
							}
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
								$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
							} else {
								if ($doDebug) {
									echo "student was successfully remived from the advisor's class<br />";
								}
//								$content		.= "Student removed from the advisor's class<br />";
							}
						}
						$content			.= "<h3>$jobname</h3>
												<p>$advisorReply</p>";
					}
				}
			} else {
				$errorString	= "<p>Program: advisor verification of students</p>
<p>At pass 3. No student found running $sql.<br /><br />
advisor_call_sign: $advisor_call_sign ($advisor_ID)<br />
student_call_sign: $student_call_sign ($student_ID)<br />
mode: $mode<br />
inp_attend: $inp_attend<br />
inp_replace: $inp_replace</p>";
				sendErrorEmail($errorString);
				$content	.= "Fatal program error. System administration has been notified.";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$advisor_call_sign|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('advisor_verification_of_student_v2', 'advisor_verification_of_student_v2_func');
