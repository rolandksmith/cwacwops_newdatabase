function student_email_response_func() {

/*	function to get student response and update the Student pod (incoming info only)
		renamed from Handle Email Response - bc 13mar20
	modified 27Oct22 by Roland to accomodate new timezone table format
  	modified 17Apr23 by Roland to fix action_log
  	modified 15Jul23 by Roland to use consolidated tables
	modified 31Aug23 by Roland to turn off dodebug and testmode if validUser is N
  	modified 12Nove23 by Roland to ask why the student is turning down the class
		The program now only handles students asking to be removed. Verification 
		is handled by the student registration program
	Modified 15Nov24 by Roland for new database

*/

	global $wpdb;

	$doDebug				= FALSE;
	$testMode				= FALSE;
	$myDate 				= date('Y-m-d');
	$studentID 				= '';
	$strPass				= "1";
	$increment				= 0;
	$token					= '';
	$initializationArray 	= data_initialization_func();
	$validUser 				= $initializationArray['validUser'];
	$userName				= $initializationArray['userName']; 
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	$versionNumber			= '2';
	$siteURL				= $initializationArray['siteurl'];
	$semesterTwo			= $initializationArray['semesterTwo'];
	$semesterThree			= $initializationArray['semesterThree'];
	$jobname				= "Student Email Response V$versionNumber";
	$theURL					= "$siteURL/cwa-thank-you-remove/";

 	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	
	$inp_work				= '';
	$inp_equip				= '';
	$inp_time				= '';
	$inp_health				= '';
	$inp_other				= '';
	$inp_other_reason		= '';
	$xmode					= '';
	$inp_option				= '';
	$token					= '';
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "appid") {
				$studentID		 = $str_value;
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
			}
			if ($str_key 		== "xmode") {
				$xmode		 = $str_value;
			}
			if ($str_key 		== "inp_option") {
				$inp_option	 = $str_value;
				$inp_option	 = filter_var($inp_option,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_work") {
				$inp_work	 = $str_value;
				$inp_work	 = filter_var($inp_work,FILTER_UNSAFE_RAW);
				if ($inp_work == 'on') {
					$inp_work = "Selected";
				}
			}
			if ($str_key 		== "inp_equip") {
				$inp_equip	 = $str_value;
				$inp_equip	 = filter_var($inp_equip,FILTER_UNSAFE_RAW);
				if ($inp_equip == 'on') {
					$inp_equip = "Selected";
				}
			}
			if ($str_key 		== "inp_time") {
				$inp_time	 = $str_value;
				$inp_time	 = filter_var($inp_time,FILTER_UNSAFE_RAW);
				if ($inp_time == 'on') {
					$inp_time = "Selected";
				}
			}
			if ($str_key 		== "inp_health") {
				$inp_health	 = $str_value;
				$inp_health	 = filter_var($inp_health,FILTER_UNSAFE_RAW);
				if ($inp_health == 'on') {
					$inp_health = "Selected";
				}
			}
			if ($str_key 		== "inp_other") {
				$inp_other	 = $str_value;
				$inp_other	 = filter_var($inp_other,FILTER_UNSAFE_RAW);
				if ($inp_other == 'on') {
					$inp_other = "X";
				}
			}
			if ($str_key 		== "inp_other_reason") {
				$inp_other_reason	 = stripslashes($str_value);
				$inp_other_reason	 = filter_var($inp_other_reason,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token	 = stripslashes($str_value);
				$token	 = filter_var($token,FILTER_UNSAFE_RAW);
			}
		}
	}
	if ($xmode == 'tm') {
		$testMode 			= TRUE;
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


	
  if ("1" == $strPass) {
 	$content	.= "<p>This function cannot be run directly. It is executed when a 
prospective student responds to the verification email.</p>";


  } elseif ("2" == $strPass) {
  	if ($doDebug) {
  		echo "<br />at pass 2<br />";
  	}

	sendErrorEmail("Info Only: student_email_response called with studentID: $studentID and inp_option: $inp_option");

	$content				.= "<h3>$jobname</h3>";

// Student can only respond during the period 45 days before the semester starts 
// to 10 days before the semester starts. Any response outside the time period
// doesn't work.
// echo "Starting. Studentid: $studentID<br />";
		$theSemester		= $initializationArray['currentSemester'];
		$nextSemester		= $initializationArray['nextSemester'];
		if ($theSemester == 'Not in Session') {
			$theSemester 	= $nextSemester;
		}
		$daysToSemester		= $initializationArray['daysToSemester'];
//	  echo "daysToSemester: $daysToSemester<br />";
		if ($daysToSemester > 10 && $daysToSemester < 45) {
			$goodtogo		= TRUE;
		} else {
			if ($daysToSemester < 11) {
				$content		.= "<p>Class assignments have already been made for the $theSemester 
semester and your registration has been cancelled because you missed the verification 
deadline. Please register for the upcoming semester. If you have issues or concerns, 
please contact the appropriate person at <a href='https://cwops.org/cwa-class-resolution/'>CWA 
Class Resolution</a>.</p>";
				return $content;
//			} else {
//				$content		.= "<p>You have clicked on an expired link.</p>";
//				return $content;
			}
		
		}
		if ($testMode) {
			$studentTableName	= 'wpw1_cwa_student2';
			$userMasterTableName = 'wpw1_cwa_user_master2';
		} else {
			$studentTableName	= 'wpw1_cwa_student';
			$userMasterTableName = 'wpw1_cwa_user_master';
		}
  
		if ($studentID == '') {
			return "<p>Incorrect information entered.</p>";
		}
		if (filter_var($studentID,FILTER_VALIDATE_INT) === false) {
			return "<p>Incorrect information entered.</p>";
		}
			
		$sql				= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_id='$studentID'";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass2 reading $studentTableName table. username: $userName");
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_master_ID 					= $studentRow->user_ID;
					$student_master_call_sign 			= $studentRow->user_call_sign;
					$student_first_name 				= $studentRow->user_first_name;
					$student_last_name 					= $studentRow->user_last_name;
					$student_email 						= $studentRow->user_email;
					$student_ph_code					= $studentRow->user_ph_code;
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
					$student_prev_callsign				= $studentRow->user_prev_callsign;
					$student_master_date_created 		= $studentRow->user_date_created;
					$student_master_date_updated 		= $studentRow->user_date_updated;

					$student_ID								= $studentRow->student_id;
					$student_call_sign						= $studentRow->student_call_sign;
					$student_time_zone  					= $studentRow->student_time_zone;
					$student_timezone_offset				= $studentRow->student_timezone_offset;
					$student_youth  						= $studentRow->student_youth;
					$student_age  							= $studentRow->student_age;
					$student_parent 						= $studentRow->student_parent;
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

					$actionDate				= date('dMY H:i');
					$doProceed				= TRUE;
					if ($doDebug) {
						echo "student has indicated option $inp_option<br />";
					}
					if ($inp_option == '1') {		// move registration to semester two
						$student_action_log		= "$student_action_log / $actionDate $student_call_sign student 
requested to be moved to $semesterTwo ";
						$updateParams[]		 	= "student_semester|$semesterTwo|s";
						$updateParams[]			= "student_abandoned||s";
						$updateParams[]			= "student_catalog_options||s";
						$updateParams[]			= "student_email_number|0|d";
						$updateParams[]			= "student_email_sent_date||s";
						$updateParams[]			= "student_no_catalog|Y|s";
						$updateParams[]			= "student_action_log|$student_action_log|s";
						$newSemester			= $semesterTwo;
					} elseif ($inp_option == '2') {  // move registration to semester three
						$student_action_log		= "$student_action_log / $actionDate $student_call_sign student 
requested to be moved to $semesterThree ";
						$updateParams[]		 	= "student_semester|$semesterThree|s";
						$updateParams[]			= "student_abandoned||s";
						$updateParams[]			= "student_catalog_options||s";
						$updateParams[]			= "student_email_number|0|d";
						$updateParams[]			= "student_email_sent_date||s";
						$updateParams[]			= "student_no_catalog|Y|s";
						$updateParams[]			= "student_action_log|$student_action_log|s";
						$newSemester			= $semesterThree;
					} elseif ($inp_option == '3') {
						$student_action_log		= "$student_action_log / $actionDate $student_call_sign Student responded to verify email with Remove ";
						$updateParams[]			= 'student_response|R|s';
						$updateParams[]			= "student_action_log|$student_action_log|s";
						$updateParams[]			= "response_date|$myDate|s";
					} else {
						if ($doDebug) {
							echo "no option found!<br />";
						}
						$doProceed				= FALSE;
					}
					if ($doProceed) {
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
								echo "Successfully updated $student_call_sign record at $student_ID<br />";
							}
							if ($token != '') {
								if ($doDebug) {
									echo "removing reminder for $student_call_sign token $token<br />";
								}
								resolve_reminder($student_call_sign,$token,$testMode,$doDebug);
							}
						}
					}
	
					if ($inp_option == "3") {
						$content	 	.= "<p>$student_call_sign</p>
							<p>Thanks for your response and your interest in the CW Academy.  
							Your registration for the upcoming semester has been cancelled.</p>
							<p>Is there a particular reason you will not be able to take a $student_level class? 
							Please select all that apply:
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='3'>
							<input type='hidden' name='inp_callsign' value='$student_call_sign'>
							<input type='hidden' name='inp_email' value='$student_email'>
							<input type='hidden' name='token' value='$stoken'>
							<input type='hidden' name='inp_level' value='$student_level'>
							<table>
							<fieldset>
							<legend>Indicate reason(s) you cannot take a class</legend>
							<tr><td><input type='checkbox' class='formInputButton' id='inp_work' name='inp_work'>
									<label for 'inp_work' style='width:500px;text-align:left;'>Work or Schedule Changes</label></td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='inp_equip' name='inp_equip'>
									<label for 'inp_equip' style='width:500px;text-align:left;'>Equipment Needed to Take the Class</label></td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='inp_time' name='inp_time'>
									<label for 'inp_time' style='width:500px;text-align:left;'>Time Commitment for the Class</label></td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='inp_health' name='inp_health'>
									<label for 'inp_health' style='width:500px;text-align:left;'>Health Concerns</label></td></tr>
							<tr><td><input type='checkbox' class='formInputButton' id='inp_other' name='inp_other'>
									<label for 'inp_other' style='width:500px;text-align:left;'>Other (Please elaborate)</label><br />
									<textarea class='formInputText' name='inp_other_reason' rows='5' cols='50'></textarea></td></tr>
							<tr><td><input class='formInputButton' type='submit' value='Submit' /></td></tr>
							</fieldset>
							</table>
							</form>";
					} else {
						$content		.= "<p>$student_call_sign</p>
<p>Thank you for interest in taking the CW Academy $student_level Level class. Your registration 
has been moved to the $newSemester semester. About 45 days before that semester starts, you 
will receive an email asking you to verify your availability and to select your class preferences.<br /><br />
CW Academy</p>";

					}
				}
			}
		}





	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 3 with inp_callsign: $inp_callsign<br />";
		}
		
		$content			.= "<h3>$jobname</h3>";
		$student_call_sign	= $inp_callsign;
		// send email with student response (if any)
		
		$myTo 			= "kcgator@gmail.com";
		$mySubject 		= "CW Academy Student Responded R to the Verification Email";
		$mailCode		= 18;
		if ($testMode) {
			$mySubject	= "TESTMODE $mySubject";
			$mailcode	= 5;
			$increment++;
		}
		$myContent = "The student record on CW Academy for $inp_callsign was set to R. 
						The survey responses (if any):<br />
						Work or Schedule: $inp_work<br />
						Equipment Needed: $inp_equip<br />
						Time Commitment: $inp_time<br />
						Health Concerns: $inp_health<br />
						Other: $inp_other Reason: $inp_other_reason<br />";
		$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
													'theSubject'=>$mySubject,
													'jobname'=>$jobname,
													'theContent'=>$myContent,
													'mailCode'=>$mailCode,
													'increment'=>$increment,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug));

		$content	.= "<p>If you have any further questions or concerns, please contact 
						the appropriate person at 
						<a href='https://cwops.org/cwa-class-resolution/'>CWA Class Resolution</a>.</p>
						<p>73,<br />CW Academy</p>";
	}
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$content 		.= "<br /><br /><p>V$versionNumber. Prepared at $nowDate $nowTime</p>";
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|$nowDate $nowTime|$thisStr|$strPass: $elapsedTime");
	return $content;
}
add_shortcode ('student_email_response', 'student_email_response_func');
