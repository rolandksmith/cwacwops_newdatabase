function manage_advisor_class_assignments_func() {

/*
	
	Provides an advisor the ability  to get the advisor's class(es) and confirm
	the students
	
	Created 21Nov23 by Roland from show_advisor_class_assignments
	Modified 3Jan24 by Roland to make the student unassigned if the advisor does not 
		want the student and does not want a replacement
	Modified 25Sep24 by Roland for new database
	
*/	

	global $wpdb,$userName,$validTestmode;

	$doDebug					= TRUE;
	$testMode					= FALSE;
	$initializationArray 		= data_initialization_func();
	$validUser 					= $initializationArray['validUser'];
	$userName					= $initializationArray['userName'];

	$versionNumber				= '1';
	$jobname					= "Manage Advisor Class Assignments V$versionNumber";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userRole					= $initializationArray['userRole'];
	$validTestmode				= $initializationArray['validTestmode'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$userName					= $initializationArray['userName'];
	$siteURL					= $initializationArray['siteurl'];	
	$validReplacementPeriod		= $initializationArray['validReplacementPeriod'];	

// must be a logged-in user
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";		
	}

	
	$proximateSemester			= $currentSemester;
	if ($proximateSemester == 'Not in Session') {
		$proximateSemester		= $nextSemester;
	}
	if ($userRole != 'administrator') {
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
	$inp_callsign				= "";
	$confirmationMsg			= '';
	$inp_attend					= '';
	$inp_comment_attend			= '';
	$inp_comment				= '';
	$inp_replacement			= '';
	$token						= '';
	$submit						= '';
	$inp_mode					= '';
	$inp_verbose				= '';
	$actionDate					= date('Y-m-d H:i:s');
	$updateStudentInfoURL		= "$siteURL/cwa-display-and-update-student-signup-information/";
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
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
				$inp_callsign	= strtoupper($inp_callsign);
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
			if ($str_key 		== "unconfirm") {
				$unconfirm			 = $str_value;
				$unconfirm			 = filter_var($unconfirm,FILTER_UNSAFE_RAW);
				$unconfirm			= str_replace('Change ','',$unconfirm);
				$studentToProcess	= str_replace(' Confirmation','',$unconfirm);
				if ($doDebug) {
					echo "set studentToProcess to $studentToProcess<br />";
				}
			}
			if ($str_key 		== "confirm") {
				$confirm			 = $str_value;
				$confirm			 = filter_var($confirm,FILTER_UNSAFE_RAW);
				$studentToProcess	= str_replace('Confirm ','',$confirm);
				if ($doDebug) {
					echo "set studentToProcess to $studentToProcess<br />";
				}
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
			if ($str_key 		== "inp_comment1") {
				$inp_comment1			 = $str_value;
				$inp_comment1			= str_replace("'","&apos;",$inp_comment1);
				$inp_comment1			 = filter_var($inp_comment1,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_comment2") {
				$inp_comment2			 = $str_value;
				$inp_comment2			= str_replace("'","&apos;",$inp_comment2);
				$inp_comment2			 = filter_var($inp_comment2,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_comment3") {
				$inp_comment3			 = $str_value;
				$inp_comment3			= str_replace("'","&apos;",$inp_comment3);
				$inp_comment3			 = filter_var($inp_comment3,FILTER_UNSAFE_RAW);
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
		$studentTableName			= 'wpw1_cwa_student2';
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment2';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$thisMode					= 'TM';
		$theStatement				= "<p>Function is running in TEST MODE using test files.</p>";
	} else {
		$studentTableName			= 'wpw1_cwa_student';
		$advisorTableName			= 'wpw1_cwa_advisor';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$audioAssessmentTableName	= 'wpw1_cwa_audio_assessment';
		$newAssessmentData			= 'wpw1_cwa_new_assessment_data';
		$userMasterTableName		= 'wpw1_cwa_user_master';
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

	if ($submit == 'Prepare CSV') {
		$strPass	= '10';
	}

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at $strPass with <br />
				inp_callsign: $inp_callsign<br />
				studentToProcess: $studentToProcess<br />
				token: $token<br />";
		}


		///// get the student information
		$sql				= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_semester='$proximateSemester' 
								and student_call_sign='$studentToProcess'";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numSRows rows<br />";
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
					$student_survey_completion_date			= $studentRow->student_survey_completion_date;
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

					/////// see if student is actually assigned to the advisor
					if ($student_assigned_advisor != $inp_callsign) {
						$content	.= "Incompatible Information Entered.<br />";
						if ($doDebug) {
							echo "$student_call_sign assigned advisor of $student_assigned_advisor is not $inp_callsign<br />";
						}
					} else {
						/////////// 	if student status is already C, R, or V, no sense in going further
						if ($student_status == 'C' || $student_status == 'R' || $student_status == 'V') {
							if ($doDebug) {
								echo "Student status is $student_status. No further action available<br />";
							}
							$strPass		= '5';		// redisplay the class
						} else {
					
							// display the form for the advisor to fill out and process the form in pass 3
	
							$content		.= "<h2>Advisor Confirmation of Student $student_call_sign</h2>
												<p><form method='post' action='$theURL' 
												name='verification_form' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='3'>
												<input type='hidden' name='inp_callsign' value='$inp_callsign'>
												<input type='hidden' name='student_call_sign' value='$student_call_sign'>
												<input type='hidden' name='studentid' value='$student_ID'>
												<input type='hidden' name='inp_vebose' value='$inp_verbose'>
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
														<textarea class=formInputText' name='inp_comment1' id='inp_comment1' rows='2' cols='50'></textarea></td></tr>
												<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_attend' value='advisor'> 
														<b>Advisor Doesn't Want the Student:</b><br />
														If you don't want the student in your class for whatever reason, select this option. The student will be unassigned and 
														returned to the unassigned pool for possible assignment to another advisor's class. Comments would be helpful but 
														not required.</td></tr>
												<tr><td><b>Advisor Comments (<em>optional</em>):</b><br />
														<textarea class=formInputText' name='inp_comment2' id='inp_comment2' rows='2' cols='50'></textarea></td></tr>
												<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' id='chk_attend' name='inp_attend' value='other'> 
														<b>Some Other Reason</b> (such as no responding)<br />
														Please enter as much information as you have about why. 
														The student will be marked as unavailable for reassignment.</td></tr>
												<tr><td><b>Advisor Comments (<em>Required</em>):</b><br />
														<textarea class=formInputText' name='inp_comment3' id='inp_comment3' rows='2' cols='50'></textarea></td></tr></table>";
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

//		$strPass 		= '5';

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
		$goOn				= TRUE;

		// first, see if the advisor has written required comments
		if ($inp_attend == 'schedule' && $inp_comment_attend == '') {
			$goOn			= FALSE;
			$content		.= "<h3>$jobname</h3>
								<h4>Advisor Schedule Comments Missing</h4>
								<p>You have marked the student as not attending because 
								of schedule issues. You were requested to indicate when 
								the student might be able to take a class. 
								Please find out when the student is available to take a class and enter that information below. 
								CW Academy will  try to assign the student to a different class based on your comments:</td></tr>
								<form method='post' action='$theURL' 
								name='comments_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='3'>
								<input type='hidden' name='inp_attend' value='$inp_attend'>
								<input type='hidden' name='inp_callsign' value='$inp_callsign'>
								<input type='hidden' name='student_call_sign' value='$student_call_sign'>
								<input type='hidden' name='studentid' value='$studentid'>
								<input type='hidden' name='inp_replace' value='$inp_replace'>
								<input type='hidden' name='inp_vebose' value='$inp_verbose'>
								<input type='hidden' name='token' value='$token'>
								<table>
								<tr><td><b>Advisor Comments (<em>Required</em>):</b><br />
										<textarea class=formInputText' name='inp_comment_attend' id='inp_comment_attend' rows='2' cols='50'></textarea></td></tr>
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
								</table></form>";
		} else {
			if ($goOn) {
				//////////	get the advisor record
				$sql				= "select * from $advisorTableName 
										left join $userMasterTableName on user_call_sign = advisor_call_sign 
										where advisor_semester='$proximateSemester'
											and advisor_call_sign='$inp_callsign'";
				$wpw1_cwa_advisor			= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisor === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numARows			= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
					}
					if ($numARows > 0) {
						foreach ($wpw1_cwa_advisor as $advisorRow) {
							$advisor_master_ID 					= $advisorRow->user_ID;
							$advisor_master_call_sign			= $advisorRow->user_call_sign;
							$advisor_first_name 				= $advisorRow->user_first_name;
							$advisor_last_name 					= $advisorRow->user_last_name;
							$advisor_email 						= $advisorRow->user_email;
							$advisor_ph_code 					= $advisorRow->user_ph_code;
							$advisor_phone 						= $advisorRow->user_phone;
							$advisor_city 						= $advisorRow->user_city;
							$advisor_state 						= $advisorRow->user_state;
							$advisor_zip_code 					= $advisorRow->user_zip_code;
							$advisor_country_code 				= $advisorRow->user_country_code;
							$advisor_country 					= $advisorRow->user_country;
							$advisor_whatsapp 					= $advisorRow->user_whatsapp;
							$advisor_telegram 					= $advisorRow->user_telegram;
							$advisor_signal 					= $advisorRow->user_signal;
							$advisor_messenger 					= $advisorRow->user_messenger;
							$advisor_master_action_log 			= $advisorRow->user_action_log;
							$advisor_timezone_id 				= $advisorRow->user_timezone_id;
							$advisor_languages 					= $advisorRow->user_languages;
							$advisor_survey_score 				= $advisorRow->user_survey_score;
							$advisor_is_admin					= $advisorRow->user_is_admin;
							$advisor_role 						= $advisorRow->user_role;
							$advisor_master_date_created 		= $advisorRow->user_date_created;
							$advisor_master_date_updated 		= $advisorRow->user_date_updated;
		
							$advisor_ID							= $advisorRow->advisor_id;
							$advisor_call_sign 					= strtoupper($advisorRow->advisor_call_sign);
							$advisor_semester 					= $advisorRow->advisor_semester;
							$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
							$advisor_verify_email_date 			= $advisorRow->advisor_verify_email_date;
							$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
							$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
							$advisor_action_log 				= $advisorRow->advisor_action_log;
							$advisor_class_verified 			= $advisorRow->advisor_class_verified;
							$advisor_control_code 				= $advisorRow->advisor_control_code;
							$advisor_date_created 				= $advisorRow->advisor_date_created;
							$advisor_date_updated 				= $advisorRow->advisor_date_updated;
							$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;
		
							if ($userName == '') {
								$userName 		= $advisor_call_sign;
							}
							if ($doDebug) {
								echo "Got $advisor_call_sign's records from $advisorTableName table<br />";
							}
						
							$doProceed		= TRUE;
							
							///// now get the student information
							$sql			= "select * from $studentTableName 
												left join $userMasterTableName on user_call_sign = student_call_sign 
												where student_semester='$proximateSemester' 
												and student_id=$studentid";
							$wpw1_cwa_student				= $wpdb->get_results($sql);
							if ($wpw1_cwa_student === FALSE) {
								handleWPDBError($jobname,$doDebug);
								$doProceed 	= FALSE;
							} else {
								$numSRows			= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $sql<br />and found $numSRows rows<br />";
								}
								if ($numSRows > 0) {
									foreach ($wpw1_cwa_student as $studentRow) {
										$student_master_ID 					= $studentRow->user_ID;
										$student_master_call_sign 			= $studentRow->user_call_sign;
										$student_first_name 				= $studentRow->user_first_name;
										$student_last_name 					= $studentRow->user_last_name;
										$student_email 						= $studentRow->user_email;
										$student_ph_cpde 					= $studentRow->user_ph_code;
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
										$student_survey_completion_date			= $studentRow->student_survey_completion_date;
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
					
									}
									if ($doProceed) {
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
																				"student_action_log|$student_action_log|s");
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
												$updateParams			= array("advisor_action_log|$advisor_action_log|s");
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
													$studentUpdateParams[]	= "student_action_log|$student_action_log|s";
													$advisorUpdateParams[]	= "advisor_action_log|$advisor_action_log|s";
													$studentUpdateParams[]	= "student_status|V|s";
													$confirmationMsg		= "Student $student_call_sign confirmed as not attending and a replacement has been requested.<br />";
													$removeStudent			= FALSE;
												} elseif ($inp_attend == 'class') {
													if ($doDebug) {
														echo "Doing Class; Replacement Yes<br />";
													}
													if ($inp_comment1 != '') {
														$myStr				= "Advisor comments: $inp_comment1 ";
													}
													$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student does not want the class. $myStr Replacement requested ";
													$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign does not want the class. $myStr Replacement requested ";
													$studentUpdateParams[]	= "student_action_log|$student_action_log|s";
													$advisorUpdateParams[]	= "advisor_action_log|$advisor_action_log|s";
													$studentUpdateParams[]	= "student_status|R|s";
													$confirmationMsg		= "Student $student_call_sign confirmed as not attending and a replacement has been requested.<br />";
													$removeStudent			= FALSE;
												} elseif ($inp_attend == 'advisor') {
													if ($doDebug) {
														echo "Doing advisor; Replacement Yes<br />";
													}
													$newStudentExcludedAdvisor	= updateExcludedAdvisor($student_excluded_advisor,$advisor_call_sign,'add',$doDebug);
													if ($inp_comment2 != '') {
														$myStr				= "Advisor comments: $inp_comment2 ";
													}
													$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor does not want the student. $myStr Replacement requested ";
													$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor does not want $student_call_sign. $myStr Replacement requested ";
													$studentUpdateParams[]	= "student_action_log|$student_action_log|s";
													$studentUpdateParams[]	= "student_excluded_advisor|$newStudentExcludedAdvisor|s";
													$studentUpdateParams[]	= "student_hold_reason_code|X|s";
													$advisorUpdateParams[]	= "advisor_action_log|$advisor_action_log|s";
													$studentUpdateParams[]	= "student_status|R|s";
													$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
													$removeStudent				= FALSE;
												} else {
													if ($doDebug) {
														echo "Doing Other; Replacement Yes<br />";
													}
													if ($inp_comment3 != '') {
														$myStr				= "Advisor comments: $inp_coment3 ";
													}
													$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend. $myStr Replacement requested ";
													$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign will not attend. Advisor comments: $inp_comment. Replacement requested ";
													$studentUpdateParams[]	= "student_action_log|$student_action_log|s";
													$advisorUpdateParams[]	= "advisor_action_log|$advisor_action_log|s";
													$studentUpdateParams[]	= "student_status|R|s";
													$confirmationMsg		= "Student $student_call_sign confirmed as not attending and a replacement has been requested.<br />";
													$removeStudent			= FALSE;
												}
											} else {
												if ($inp_attend == 'schedule') {
													if ($doDebug) {
														echo "Doing Schedule; Replacement No<br />";
													}
													$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign 
student will not attend due to schedule. Advisor comments: $inp_comment_attend. Unassigned from $student_assigned_advisor class $student_assigned_advisor_class. 
No replacement requested.  ";
													$newStudentExcludedAdvisor	= updateExcludedAdvisor($student_excluded_advisor,$advisor_call_sign,'add',$doDebug);
													$studentUpdateParams[]		= "student_action_log|$student_action_log|s";
													$studentUpdateParams[]		= "student_hold_reason_code|X|s";
													$studentUpdateParams[]		= "student_class_priority|2|d";
													$studentUpdateParams[]		= "student_excluded_advisor|$newStudentExcludedAdvisor|s";
													$studentUpdateParams[]		= "student_intervention_required|H|s";
													$student_remove_status		= '';
													$removeStudent				= TRUE;
													$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM 
$student_call_sign will not attend due to schedule. Advisor comments: $inp_comment_attend. Unassigned from $student_assigned_advisor class $student_assigned_advisor_class. 
No replacement requested. ";
													$advisorUpdateParams	= array("advisor_action_log|$advisor_action_log|s");
				
												} elseif ($inp_attend == 'class') {
													if ($doDebug) {
														echo "Doing Class; Replacement No<br />";
													}
													$myStr					= "";
													if ($inp_comment1 != '') {
														$myStr				= "Advisor comments: $inp_comment1 ";
													}
													$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student does not want the class. $myStr No replacement requested ";
													$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign does not want the class. $myStr No replacement requested ";
													$studentUpdateParams[]	= "student_action_log|$student_action_log|s";
													$advisorUpdateParams[]	= "advisor_action_log|$advisor_action_log|s";
													$student_remove_status	= 'C';
													$removeStudent			= TRUE;
													$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
												} elseif ($inp_attend == 'advisor') {
													if ($doDebug) {
														echo "Doing advisor; Replacement No<br />";
													}
													$newStudentExcludedAdvisor		= updateExcludedAdvisor($student_excluded_advisor,$advisor_call_sign,'add',$doDebug);
													$myStr					= "";
													if ($inp_comment2 != '') {
														$myStr				= "Advisor comments: $inp_comment2 ";
													}
													$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign advisor does not want the student. $myStr No replacement requested. Student set to unassigned ";
													$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM advisor does not want $student_call_sign. $myStr No replacement requested ";
													$studentUpdateParams[]	= "student_action_log|$student_action_log|s";
													$studentUpdateParams[]	= "student_excluded_advisor|$newStudentExcludedAdvisor|s";
													$studentUpdateParams[]	= "student_hold_reason_code|X|s";
													$advisorUpdateParams[]	= "advisor_action_log|$advisor_action_log|s";
													$student_remove_status	= '';
													$removeStudent			= TRUE;
													$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
												} else {
													if ($doDebug) {
														echo "Doing Other; Replacement No<br />";
													}
													$myStr					= "";
													if ($inp_comment3 != '') {
														$myStr				= "Advisor comments: $inp_comment3 ";
													}
													$student_action_log		= "$student_action_log / $actionDate CONFIRM $advisor_call_sign student will not attend. $myStr No replacement requested ";
													$advisor_action_log		= "$advisor_action_log / $actionDate CONFIRM $student_call_sign will not attend. $myStr No replacement requested ";
													$newStudentExcludedAdvisor		= updateExcludedAdvisor($student_excluded_advisor,$advisor_call_sign,'add',$doDebug);
													$studentUpdateParams[]	= "student_hold_reason_code|X|s";
													$studentUpdateParams[]	= "student_class_priority|2|d";
													$studentUpdateParams[]	= "student_excluded_advisor|$newStudentExcludedAdvisor|s";
													$studentUpdateParams[]	= "student_action_log|$student_action_log|s";
													$advisorUpdateParams[]	= "advisor_action_log|$advisor_action_log|s";
													$student_remove_status	= 'C';
													$removeStudent			= TRUE;
													$confirmationMsg		= "Student $student_call_sign confirmed as not attending and no replacement was requested.<br />";
												}
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
											if ($doDebug) {
												echo "Ready to update student:<br /><pre>";
												print_r($updateData);
												echo "</pre><br />";
											}
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
											if ($doDebug) {
												echo "doing update advisor:<br /><pre>";
												print_r($advisorUpdateData);
												echo "</pre><br />";
											}
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
											}
										}
										if ($removeStudent) {
										// remove the student
										
											if (!isset($student_assigned_advisor)) {
												if ($doDebug) {
													echo "student_assigned_advisor is MISSING!<br />";
												}
												$nowInfo		= date('Y-m-d H:i:s');
												sendErrorEmail("$jobname $userName $nowInfo attempting to remove student $student_call_sign. student_assigned_advisor is missing");
												$student_assigned_advisor	= $userName;
											}
										
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
											} else {
												if ($doDebug) {
													echo "student was successfully removed from the advisor's class<br />";
												}
											}
										}
									}
								}
							}
						}
					}
				}
				$strPass		= '5';
			}
		}
	}
	if ("5" == $strPass) {
	
		if ($doDebug) {
			echo "<br />At pass $strPass with<br />
				  inp_callsign: $inp_callsign<br />
				  token: $token<br />";
		}
		
		if ($inp_callsign == '') {
			$inp_callsign	= strtoupper($userName);
			$token			= '';
		}
		$unconfirmedCount	= 0;
		$jjCount			= 0;
		$content			.= "$confirmationMsg 
								<h4>$inp_callsign Classes and Students for $proximateSemester Semester</h4>
								<div><p><table style='width:900px;'>
								<tr><td>If you would like a .csv dump of the students assigned 
										to your $proximateSemester semester classes, please click</td>
									<td><form method='post' action='$theURL' 
										name='dump_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='10'>
										<input type='hidden' name='inp_callsign' value='$inp_callsign'>
										<input class='formInputButton' type='submit' name='submit' value='Prepare CSV' /></td></tr></table></p></div>";
		
		$sql	= "select * from $advisorClassTableName 
					where advisorclass_call_sign = '$inp_callsign' 
					  and advisorclass_semester = '$proximateSemester' 
					order by advisorclass_sequence";


		$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows						= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
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
						echo "there are $advisorClass_number_students students in the class<br />";
					}
					$displayClass			= FALSE;										
					if ($advisorClass_number_students > 0) {	
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
																							left join $userMasterTableName on user_call_sign = student_call_sign 
																							where student_semester='$proximateSemester' 
																							and student_call_sign = '$theInfo'");
									if ($wpw1_cwa_student === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {
										$numSRows									= $wpdb->num_rows;
										if ($doDebug) {
											$myStr					= $wpdb->last_query;
											echo "ran $myStr<br />and found $numSRows rows<br />";
										}
										if ($numSRows > 0) {
											foreach ($wpw1_cwa_student as $studentRow) {
												$student_master_ID 					= $studentRow->user_ID;
												$student_master_call_sign 			= $studentRow->user_call_sign;
												$student_first_name 				= $studentRow->user_first_name;
												$student_last_name 					= $studentRow->user_last_name;
												$student_email 						= $studentRow->user_email;
												$student_ph_code 					= $studentRow->user_ph_code;
												$student_phone 						= $studentRow->user_phone;
												$student_city 						= $studentRow->user_city;
												$student_state 						= $studentRow->user_state;
												$student_zip_code 					= $studentRow->user_zip_code;
												$student_country_code 				= $studentRow->user_country_code;
												$student_country					= $studentRow->user_country;
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
												$student_survey_completion_date			= $studentRow->student_survey_completion_date;
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
													echo "&nbsp;&nbsp;&nbsp;&nbsp;Level: $student_level<br />
														  &nbsp;&nbsp;&nbsp;&nbsp;Class: $student_assigned_advisor_class<br />
														  &nbsp;&nbsp;&nbsp;&nbsp;Status: $student_status<br />
														  &nbsp;&nbsp;&nbsp;&nbsp;Promotable: $student_promotable<br />";
												}
												
												if ($student_status == 'S' || $student_status == 'Y') {
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
													if ($student_status == 'S') {
														$unconfirmedCount++;
														$myStr					= "<table style='border:4px solid green;'>
																					<tr><td><form method='post' action='$theURL' 
																							name='confirm_form' ENCTYPE='multipart/form-data'>
																							<input type='hidden' name='strpass' value='1'>
																							<input type='hidden' name='inp_callsign' value='$advisorClass_call_sign'>
																							<input type='hidden' name='token' value='$token'>
																							<input type='hidden' name='inp_mode' value='$inp_mode'>
																							<input type='hidden' name='inp_verbose' value='$inp_verbose'>
																							<input class='formInputButton' type='submit' name='confirm' value='Confirm $student_call_sign' />
																							</td></tr></table>\n";
													} elseif ($student_status == 'Y') {
														$myStr					= "Confirmed<br />
																					<form method='post' action='$theURL' 
																					name='unconfirm_form' ENCTYPE='multipart/form-data'>
																					<input type='hidden' name='strpass' value='1'>
																					<input type='hidden' name='token' value='$token'>
																					<input type='hidden' name='inp_mode' value='$inp_mode'>
																					<input type='hidden' name='inp_verbose' value='$inp_verbose'>
																					<input class='formInputButton' type='submit' name='unconfirm' value='Change $student_call_sign Confirmation' />";
													}
													if ($doDebug) {
														echo "displaying student $student_call_sign information<br />";
													}
													$content						.= "<tr><td style='vertical-align:top;'>$student_call_sign</td>\n
																							<td style='vertical-align:top;'>$student_last_name, $student_first_name</td>\n
																							<td style='vertical-align:top;'>$student_email</td>\n
																							<td style='vertical-align:top;'>+$student_ph_code $student_phone</td>\n
																							<td style='vertical-align:top;'>$student_country</td>\n
																							<td style='vertical-align:top;'>$myStr</td></tr>\n";
													if ($haveExtras) {
														$content					.= "<tr><td colspan='7'>$extras</td></tr>";
													}
													$thisParent			= '';
													$thisParentEmail	= '';
													if ($student_youth == 'Yes') {
														if ($student_age < 18) { 
															if ($student_parent == '') {
																$thisParent	= 'Not Given';
															} else {
																$thisParent	= $student_parent;
															}
															if ($student_parent_email == '') {
																$thisParentEmail = 'Not Given';
															} else {
																$thisParentEmail = $student_parent_email;
															}
															$content	.= "<tr><td colspan='7'>The student has registered as a youth under the age of 18. The student's 
																			parent or guardian is $thisParent at email address $thisParentEmail.</td></tr>\n";
														}
													}

													if ($hasAssessment) {
														$enstr		= base64_encode("advisor_call_sign=$student_assigned_advisor&inp_callsign=$student_call_sign");
														$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>Click <a href='$siteURL/cwa-view-a-student-assessment/?strpass=2&enstr=$enstr' target='_blank'>HERE</a> to review $student_call_sign's self assessment</td></tr>\n";
													} else {
														$content	.= "<tr><td colspan='7' style='border-bottom: 1px solid #000;'>&nbsp;</td></tr>";
													}
												}
											}	/// end of the student foreach
										} else {		/// no student record found ... send error message
											sendErrorEmail("Prepare Advisor Class Display: no $studentTableName table record found for student$strSnum in advisor $advisorClass_call_sign class $advisorClass_sequence");
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
					$resolveResult				= resolve_reminder($inp_callsign,'studentConfirmation',$testMode,$doDebug);
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
			advisor: $inp_callsign<br />
			semester: $proximateSemester<br />
			submit: $submit<br />";
			
		}
		if ($submit == 'Prepare CSV') {
			// get all students assigned to this advisor for the specified semester
			$sql			= "select * from $studentTableName 
								left join $userMasterTableName on user_call_sign = student_call_sign 
								where student_assigned_advisor = '$inp_callsign' 
								and student_semester = '$proximateSemester' 
								and student_response = 'Y' 
								and (student_status = 'S' or student_status = 'Y') 
								order by student_assigned_advisor_class, 
								student_call_sign";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					$content		.= "<h3>$jobname</h3>
										<h4>CSV Dump of Students in $inp_callsign $proximateSemester Semester Class(es)</h4>
										<p>The table below shows what the csv file contains for your students by class number in student call sign 
										order. The fields are separated by tabs. The first row are the field names.</p>
										<p>The link to download the csv file along with instructions are below the table.</p>
										<pre>class\tcall_sign\tfirst_name\tlast_name\temail\tphone\tstate\tcountry\twhatsapp\tsignal\ttelegram\tmessenger\n";
										
					// prepare the csv file and write the headers
					$thisStr		= "$inp_callsign" . "_class_download.csv";
					if (preg_match('/localhost/',$siteURL)) {
						$thisFileName	= "wp-content/uploads/$thisStr";
					} else {
						$thisFileName	= "/home/cwacwops/public_html/wp-content/uploads/$thisStr";
					}
					$thisFP			= fopen($thisFileName,'w');
					$thisList		= ['class','call_sign','first_name','last_name','email','phone','state','country','whatsapp','signal','telegram','messenger'];
					fputcsv($thisFP,$thisList,"\t");										
			
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_master_ID 					= $studentRow->user_ID;
						$student_master_call_sign 			= $studentRow->user_call_sign;
						$student_first_name 				= $studentRow->user_first_name;
						$student_last_name 					= $studentRow->user_last_name;
						$student_email 						= $studentRow->user_email;
						$student_ph_code 					= $studentRow->user_ph_code;
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
						$student_survey_completion_date			= $studentRow->student_survey_completion_date;
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
	
						$content			.= "$student_assigned_advisor_class\t$student_call_sign\t$student_first_name\t$student_last_name\t$student_email\t+$student_ph_code $student_phone\t$student_state\t$student_country\t$student_whatsapp\t$student_signal\t$student_telegram\t$student_messenger\n";	
						$thisPhone			= "+$student_ph_code $student_phone";
						$thisList			= [$student_assigned_advisor_class,$student_call_sign,$student_first_name,$student_last_name,$student_email,$thisPhone,$student_state,$student_country,$student_whatsapp,$student_signal,$student_telegram,$student_messenger];
						fputcsv($thisFP,$thisList,"\t");
					}
					fclose($thisFP);
					if ($doDebug) {
						echo "table is written and the file is ready to download<br />";
					}
					$content				.= "</pre><br />
												<p>Click <a href='$siteURL/wp-content/uploads/$thisStr'>$thisStr</a> to download the csv file</p>
												<p>To use this data on a Windows computer do the following:<br />
												Open Excel (or your preferred spreadsheet program) and import the newly 
												downloaded document. You may need to specify that Whatsapp, Signal, 
												Telegram, and Messenger are 'text' fields.</p>";
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
								<td><input type='text' class='formInputText' size='15' maxlength = '15' name='inp_callsign'></td></tr>
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
add_shortcode ('manage_advisor_class_assignments', 'manage_advisor_class_assignments_func');
