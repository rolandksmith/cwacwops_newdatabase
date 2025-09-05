function advisor_session_info_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$prevSemester		= $initializationArray['prevSemester'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
		$doDebug			= FALSE;
		$testMode			= FALSE;
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
//	} else {
//		$wpdb->hide_errors();
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-advisor-session-info/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Advisor Session Info";
	$nowDate					= date('Y-m-d');
	$studentCount				= 0;

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
			if ($str_key 		== "inp_session") {
				$inp_session		 = $str_value;
				$inp_session		 = filter_var($inp_session,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_class_sequence") {
				$inp_class_sequence		 = $str_value;
				$inp_class_sequence		 = filter_var($inp_class_sequence,FILTER_UNSAFE_RAW);
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
			if ($str_key 		== "inp_session_record_id") {
				$inp_session_record_id		 = $str_value;
				$inp_session_record_id		 = filter_var($inp_session_record_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_session_notes") {
				$inp_session_notes		 = $str_value;
				$inp_session_notes		 = filter_var($inp_session_notes,FILTER_UNSAFE_RAW);
			}
			for ($snum=1;$snum<31;$snum++) {
				if ($snum < 10) {
					$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
				} else {
					$strSnum		= strval($snum);
				}
				if ($str_key 		== "inp_attend_record_id_$strSnum") {
					${'inp_attend_record_id_' . $strSnum}		 = $str_value;
					${'inp_attend_record_id_' . $strSnum}		 = filter_var(${'inp_attend_record_id_' . $strSnum},FILTER_UNSAFE_RAW);
				}
				if ($str_key 		== "inp_attend_$strSnum") {
					${'inp_attend_' . $strSnum}		 = $str_value;
					${'inp_attend_' . $strSnum}		 = filter_var(${'inp_attend_' . $strSnum},FILTER_UNSAFE_RAW);
				}
				if ($str_key 		== "inp_notes_$strSnum") {
					${'inp_notes_' . $strSnum}		 = $str_value;
					${'inp_notes_' . $strSnum}		 = filter_var(${'inp_notes_' . $strSnum},FILTER_UNSAFE_RAW);
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
		$sessionNotesTableName		= 'wpw1_cwa_session_notes2';
		$sessionAttendTableName		= 'wpw1_cwa_session_attendance2';
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$advisorTableName			= 'wpw1_cwa_advisor2';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
	} else {
		$extMode					= 'pd';
		$sessionNotesTableName		= 'wpw1_cwa_session_notes';
		$sessionAttendTableName		= 'wpw1_cwa_session_attendance';
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$advisorTableName			= 'wpw1_cwa_advisor';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
	}



	$userName			= strtoupper($userName);
	
	// figure out the semester
	$theSemester		= $currentSemester;
	if ($theSemester == 'Not in Session') {
		$myInt			= days_to_semester($nextSemester);
		if ($myInt > 40) {
			$theSemester = $prevSemester;
		} else {
			$content	.= "<h3>$jobname</h3>
							<p>No semester in session</p>";
			goto Bypass;
		}
	
	}
	
	// get the advisor's user_master and advisor records
	$advisorSQL			= "select * from $advisorTableName 
							left join $userMasterTableName on user_call_sign = advisor_call_sign 
							where advisor_call_sign = '$userName' 
							and advisor_semester = '$theSemester'";
	$wpw1_cwa_advisor	= $wpdb->get_results($advisorSQL);
	if ($wpw1_cwa_advisor === FALSE) {
		handleWPDBError($jobname,$doDebug);
	} else {
		$numARows			= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $advisorSQL<br />and found $numARows rows in $advisorTableName table<br />";
		}
		if ($numARows > 0) {
			foreach ($wpw1_cwa_advisor as $advisorRow) {
				$advisor_master_ID 					= $advisorRow->user_ID;
				$advisor_master_call_sign			= $advisorRow->user_call_sign;
				$advisor_first_name 				= $advisorRow->user_first_name;
				$advisor_last_name 					= $advisorRow->user_last_name;
				$advisor_email 						= $advisorRow->user_email;
				$advisor_phone 						= $advisorRow->user_phone;
				$advisor_city 						= $advisorRow->user_city;
				$advisor_state 						= $advisorRow->user_state;
				$advisor_zip_code 					= $advisorRow->user_zip_code;
				$advisor_country_code 				= $advisorRow->user_country_code;
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

				// now get the advisorClass records
				$displayStr				= "";
				$firstTime				= TRUE;
				$classSQL				= "select * from $advisorClassTableName 
										   where advisorclass_call_sign = '$advisor_call_sign' 
										   and advisorclass_semester = '$theSemester' 
										   order by advisorclass_sequence";
				$wpw1_cwa_advisorclass	= $wpdb->get_results($classSQL);
				if ($wpw1_cwa_advisorclass === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numACRows			= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $classSQL<br />and found $numACRows rows<br />";
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
							
							// get the maximun session number from the sessionNotes table for this class
							$maxSession		= $wpdb->get_var("select max(session) from $sessionNotesTableName 
												where session_advisor_call_sign = '$advisor_call_sign' 
												and session_advisor_class_sequence = $advisorClass_sequence 
												and session_semester = '$theSemester'");
							if ($maxSession === NULL) {			// no result found
								if ($doDebug) {
									echo "attempted to get max session which returned NULL<br />";
								}
								$maxSession	= "0 (First time accessing this information)";
							}					
							if ($firstTime) {
								$displayStr		.= "<input type='radio' class='formInputButton' name='inp_class_sequence' value='$advisorClass_sequence' required checked>Class: $advisorClass_sequence Level: $advisorClass_level Last session: $maxSession<br />";
								$firstTime		= FALSE;
							} else {
								$displayStr		.= "<input type='radio' class='formInputButton' name='inp_class_sequence' value='$advisorClass_sequence' required>Class: $advisorClass_Sequence Level: $advisorClass_level Last session: $maxSession<br />";
							}
						}
					} else {
						$content	.= "<h3>$jobname for $userName</h3>
										<p>No classes found for advisor $userName</p>";
						goto Bypass;
					}
				}
			}
		} else {
			$content		.= "<h3>$jobname for $userName</h3>
								<p>No advisor record found for $userName<br />";
			goto Bypass;
		}
	}
							
	
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 1<br />";
		}
		$content 		.= "<h3>$jobname for $userName</h3>
							<p>Select the appropriate class and the session number. Then click 'Submit':
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Class</td>
								<td>$displayStr</td></tr>
							<tr><td style='vertical-align:top;'>Session:</td>
								<td><table>
									<tr><td><input type='radio' class='formInputButton' name='inp_session' value='1' required>Session 1<br />
											<input type='radio' class='formInputButton' name='inp_session' value='2' required>Session 2<br />
											<input type='radio' class='formInputButton' name='inp_session' value='3' required>Session 3<br />
											<input type='radio' class='formInputButton' name='inp_session' value='4' required>Session 4</td>
										<td><input type='radio' class='formInputButton' name='inp_session' value='5' required>Session 5<br />
											<input type='radio' class='formInputButton' name='inp_session' value='6' required>Session 6<br />
											<input type='radio' class='formInputButton' name='inp_session' value='7' required>Session 7<br />
											<input type='radio' class='formInputButton' name='inp_session' value='8' required>Session 8</td>
										<td><input type='radio' class='formInputButton' name='inp_session' value='9' required>Session 9<br />
											<input type='radio' class='formInputButton' name='inp_session' value='10' required>Session 10<br />
											<input type='radio' class='formInputButton' name='inp_session' value='11' required>Session 11<br />
											<input type='radio' class='formInputButton' name='inp_session' value='12' required>Session 12</td>
										<td><input type='radio' class='formInputButton' name='inp_session' value='13' required>Session 13<br />
											<input type='radio' class='formInputButton' name='inp_session' value='14' required>Session 14<br />
											<input type='radio' class='formInputButton' name='inp_session' value='15' required>Session 15<br />
											<input type='radio' class='formInputButton' name='inp_session' value='16' required>Session 16</td></tr>
									</table></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>
							<p>The program provides advisors the ability to enter notes about a class session, mark
							students assigned to the class as either 'in attendance' or 'absent', and enter any 
							notes about the student.<p>";

///// Pass 2 -- do the work
	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 2 with userName: $userName<br />
					inp_class_sequence: $inp_class_sequence<br />
					inp_session: $inp_session<br />";
		}
	
		$content	.= "<h3>$jobname for $userName</h3>";
		
		// get the advisorClass 
		$classSQL				= "select * from $advisorClassTableName 
								   where advisorclass_call_sign = '$userName' 
								   and advisorclass_semester = '$theSemester' 
								   and advisorclass_sequence = $inp_class_sequence";
		$wpw1_cwa_advisorclass	= $wpdb->get_results($classSQL);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $classSQL<br />and found $numACRows rows<br />";
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

					// get the sessionNotes data (if any)
					$notesSQL		= "select * from $sessionNotesTableName 
									   where session_advisor_call_sign = '$advisorClass_call_sign' 
									   and session_advisor_class_sequence = $advisorClass_sequence 
									   and session_semester = '$theSemester' 
									   and session = $inp_session";
					$notesResult	= $wpdb->get_results($notesSQL);
					if ($notesResult === FALSE) {
						handleWPDBError($jobname,$doDebug,"attempting to get the session info");
						$content	.= "<p>Fatal error. Sys Admin has been contacted</p>";
					} else {
						$numNRows	= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $notesSQL<br />and retrieved $numNRows rows<br />";
						}
						if ($numNRows == 0) {			// no record. Create it
///////// creating a new record
							if ($doDebug) {
								echo "creating sessionNotes record<br />";
							}
							
							$session_advisor_call_sign			= $userName;
							$session_advisor_class_sequence		= $advisorClass_sequence;
							$session							= $inp_session;
							$session_date						= $nowDate;
							$session_notes						= "";
							$session_semester					= $theSemester;
							
							$sessionInsert		= $wpdb->insert($sessionNotesTableName,
															array('session_advisor_call_sign' => $session_advisor_call_sign,
															      'session_advisor_class_sequence' => $session_advisor_class_sequence,
															      'session_semester' => $session_semester,
															      'session' => $session,
															      'session_date' => $session_date,
															      'session_notes' => $session_notes),
															array('%s','%d','%s','%s'));
							if ($sessionInsert === FALSE) {
								handleWPDBError($jobname,$doDebug,'attempting to insert a new session');
								
							} else {
								$session_record_id	= $wpdb->insert_id;
								if ($doDebug) {
									$myStr			= $wpdb->last_query;
									echo "ran $myStr<br />and inserted a row at id $session_record_id<br />";
								}
								$displayVariable		= "<input type='hidden' name='inp_session_record_id' value='$session_record_id'>
															<table>
															<tr><td><b>Advisor: $userName</b></td>
																<td><b>Class: </b>$advisorClass_sequence $advisorClass_level</td>
																<td><b>Semester: </b>$advisorClass_semester</td>
																<td><b>Session: </b>$session</td></tr>
															<tr><td style='vertical-align:top;'><b>Session Notes:</b></td>
																<td colspan='3'><textarea style='formInputText' name='inp_session_notes' rows='5' cols='50'>$session_notes</textarea></td></tr>";
								
								// add each of the students
								for ($snum=1;$snum<31;$snum++) {
									if ($snum < 10) {
										$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
									} else {
										$strSnum		= strval($snum);
									}
									$studentCallSign	= ${'advisorClass_student' . $strSnum};
									if ($studentCallSign != '') {
										$studentCount++;
										if ($doDebug) {
											echo "<br />processing student $studentCallSign<br />";
										}

										// get the student name from user_master
										$attend_student_name	= "Unknown";
										$userMasterSQL	= "select * from $userMasterTableName 
															where user_call_sign like '$studentCallSign'";
										$userMasterResult	= $wpdb->get_results($userMasterSQL);
										if ($userMasterResult === FALSE) {
											handleWPDBError($jobname,$doDebug,'attempting to read $userName userMaster record');
										} else {
											$numUMRows		= $wpdb->num_rows;
											if ($doDebug) {
												echo "ran $userMasterSQL<br />and retrieved $numUMRows rows<br />";
											}
											if ($numUMRows > 0) {
												foreach($userMasterResult as $userMasterRow) {
													$user_first_name		= $userMasterRow->user_first_name;
													$user_last_name			= $userMasterRow->user_last_name;
													
													$attend_student_name	= "$user_last_name, $user_first_name";
												}
											}
 										}
 										$attend_advisor_call_sign		= $userName;
 										$attend_advisor_class_sequence	= $advisorClass_sequence;
 										$attend_semester				= $theSemester;
 										$attend_session					= $inp_session;
 										$attend_session_date			= $nowDate;
 										$attend_student_call_sign		= $studentCallSign;
 										$attend_status					= 'Y';
 										$attend_notes					= "";
 										
 										$attendInsertResult		= $wpdb->insert($sessionAttendTableName,
 																		array('attend_advisor_call_sign' => $attend_advisor_call_sign,
 																		      'attend_advisor_class_sequence' => $advisorClass_sequence,
 																		      'attend_semester' => $attend_semester,
 																		      'attend_session' => $attend_session,
 																		      'attend_session_date' => $attend_session_date,
 																		      'attend_student_call_sign' => $attend_student_call_sign,
 																		      'attend_student_name' => $attend_student_name,
 																		      'attend_status' => $attend_status,
 																		      'attend_notes' => $attend_notes),
 																		array('%s','%d','%s','%d','%s','%s','%s','%s'));
 										if ($attendInsertResult == FALSE) {
 											handleWPDBError($jobname,$doDebug,'attempting to insert attend record');
										} else {
											$attend_record_id	= $wpdb->insert_id;
											
											if ($doDebug) {
												$myStr			= $wpdb->last_query;
												echo "ran $myStr<br />and inserted a record at id $attend_record_id<br />";
											}
											
											$yesStr				= 'checked';
											$noStr				= '';
											if ($attend_status == 'N') {
												$yesStr			= '';
												$noStr			= 'checked';
											}
											$displayVariable	.= "<input type='hidden' name='inp_attend_record_id_$strSnum' value='$attend_record_id'>
																	<tr><td><b>Student $snum:</b></td>
																		<td colspan='3'>$attend_student_name ($attend_student_call_sign)</td><tr>
																	<tr><td><b>Attended</td>
																		<td colspan='3'><input type='radio' class='formInputButton' name='inp_attend_$strSnum' value='Y' $yesStr >Yes<br />
																					    <input type='radio' class='formInputButton' name='inp_attend_$strSnum' value='N' $noStr >No</td></tr>
																	<tr><td style='vertical-align:top;'><b>Notes</b></td>
																		<td colspan='3'><textarea class='formInputText' name='inp_notes_$strSnum' rows='5' cols='50'>$attend_notes</textarea></td></tr>";
										}
									}
								}
							}
						
						
						
						} else {
////// updating existing session info
							// get the sessionNotes variables:
							foreach($notesResult as $notesRow) {
								$session_record_id				= $notesRow->session_record_id;
								$session_advisor_call_sign		= $notesRow->session_advisor_call_sign;
								$session_advisor_class_sequence	= $notesRow->session_advisor_class_sequence;
								$session_semester				= $notesRow->session_semester;
								$session						= $notesRow->session;
								$session_date					= $notesRow->session_date;
								$session_notes					= $notesRow->session_notes;
								$session_date_created			= $notesRow->session_date_created;
								$session_date_updated			= $notesRow->session_date_updated;
								
								$displayVariable		= "<input type='hidden' name='inp_session_record_id' value='$session_record_id'>
															<table>
															<tr><td><b>Advisor: $userName</b></td>
																<td><b>Class: </b>$advisorClass_sequence $advisorClass_level</td>
																<td><b>Semester: </b>$advisorClass_semester</td>
																<td><b>Session: </b>$session</td></tr>
															<tr><td style='vertical-align:top;'><b>Session Notes:</b></td>
																<td colspan='3'><textarea style='formInputText' name='inp_session_notes' rows='5' cols='50'>$session_notes</textarea></td></tr>";
								
								// now get the attend info
								for ($snum=1;$snum<31;$snum++) {
									if ($snum < 10) {
										$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
									} else {
										$strSnum		= strval($snum);
									}
									$studentCallSign	= ${'advisorClass_student' . $strSnum};
									if ($studentCallSign != '') {
										$studentCount++;
										if ($doDebug) {
											echo "<br />processing student $studentCallSign<br />";
										}
										
										$attendSQL		= "select * from $sessionAttendTableName 
															where attend_advisor_call_sign = '$advisorClass_call_sign' 
															and attend_advisor_class_sequence = $advisorClass_sequence 
															and attend_session = $session 
															and attend_semester = '$theSemester' 
															and attend_student_call_sign = '$studentCallSign'";
										$attendResult	= $wpdb->get_results($attendSQL);
										if ($attendResult === FALSE) {
											handleWPDBError($jobname,$doDebug,'attempting to read the attend info');
										} else {
											$numASRows	= $wpdb->num_rows;
											if ($doDebug) {
												echo "ran $attendSQL<br />and retrieved $numASRows rows<br />";
											}
											if ($numASRows > 0) {
												foreach ($attendResult as $attendRow) {
													$attend_record_id				= $attendRow->attend_record_id;
													$attend_advisor_call_sign		= $attendRow->attend_advisor_call_sign;
													$attend_advisor_class_sequence	= $attendRow->attend_advisor_class_sequence;
													$attend_semester				= $attendRow->attend_semester;
													$attend_session					= $attendRow->attend_session;
													$attend_session_date			= $attendRow->attend_session_date;
													$attend_student_call_sign		= $attendRow->attend_student_call_sign;
													$attend_student_name			= $attendRow->attend_student_name;
													$attend_status					= $attendRow->attend_status;
													$attend_notes					= $attendRow->attend_notes;
													$attend_date_created			= $attendRow->attend_date_created;
													$attend_date_updated			= $attendRow->attend_date_updated;
													
													$yesStr				= 'checked';
													$noStr				= '';
													if ($attend_status == 'N') {
														$yesStr			= '';
														$noStr			= 'checked';
													}
													$displayVariable	.= "<input type='hidden' name='inp_attend_record_id_$strSnum' value='$attend_record_id'>
																			<tr><td><b>Student $snum:</b></td>
																				<td colspan='3'>$attend_student_name ($attend_student_call_sign)</td><tr>
																			<tr><td><b>Attended</td>
																				<td colspan='3'><input type='radio' class='formInputButton' name='inp_attend_$strSnum' value='Y' $yesStr >Yes<br />
																							    <input type='radio' class='formInputButton' name='inp_attend_$strSnum' value='N' $noStr >No</td></tr>
																			<tr><td style='vertical-align:top;'><b>Notes</b></td>
																				<td colspan='3'><textarea class='formInputText' name='inp_notes_$strSnum' rows='5' cols='50'>$attend_notes</textarea></td></tr>";
												}
//												$displayVariable		.= "</table>";
											}
										}
									}
								}
								
							}
						}
					}
				}
				// end the table here???    end foreach advisorclass
			} else {
				// no advisorclass record found
			}
		}
//		$displayVariable		.= "</table>";
		$content				.= "<h4>Enter or Update Session Information and Attendance</h4>
									<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='3'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_class_sequence' value='$inp_class_sequence'>
									<input type='hidden' name='inp_session' value='$inp_session'>
									$displayVariable;
									<tr><td colspan='4'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
									</table></form>";
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 3<br />";
		}
		
		$content			.= "<h3>$jobname for $userName<h3>";
		$displayVariable	= "";
		
		// get the sessionNotes record and update
		$notesSQL		= "select * from $sessionNotesTableName 
							where session_record_id = $inp_session_record_id";
		$notesResult	= $wpdb->get_results($notesSQL);
		if ($notesResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"attempting to get the session info");
			$content	.= "<p>Fatal error. Sys Admin has been contacted</p>";
			goto Bypass;
		} else {
			$numNRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $notesSQL<br />and retrieved $numNRows rows<br />";
			}
			if ($numNRows > 0) {
				foreach($notesResult as $notesRow) {
					$session_record_id				= $notesRow->session_record_id;
					$session_advisor_call_sign		= $notesRow->session_advisor_call_sign;
					$session_advisor_class_sequence	= $notesRow->session_advisor_class_sequence;
					$session_semester				= $notesRow->session_semester;
					$session						= $notesRow->session;
					$session_date					= $notesRow->session_date;
					$session_notes					= $notesRow->session_notes;
					$session_date_created			= $notesRow->session_date_created;
					$session_date_updated			= $notesRow->session_date_updated;

					$doUpdate				= FALSE;
					if ($inp_session_notes != $session_notes) {
						$session_notes		= $inp_session_notes;
						$doUpdate			= TRUE;
						if ($doDebug) {
							echo "updated session_notes<br />";
						}
					}
					if ($doUpdate) {
						$sessionUpdateResult	= $wpdb->update($sessionNotesTableName,
															array('session_notes' => $inp_session_notes),
															array('session_record_id' => $inp_session_record_id),
															array('%s'),
															array('%d'));
						if ($sessionUpdateResult === FALSE) {
							handleWPDBError($jobname,$doDebug,"atempting to update record $inp_session_record_id");
							goto Bypass;
						}
					} else {
						if ($doDebug) {
							echo "no update to session_notes requested<br />";
						}
					}
					$displayVariable		= "<table>
												<tr><td><b>Advisor: $userName</b></td>
													<td><b>Class: </b>$advisorClass_sequence $advisorClass_level</td>
													<td><b>Semester: </b>$advisorClass_semester</td>
													<td><b>Session: </b>$session</td></tr>
												<tr><td style='vertical-align:top;'><b>Session Notes:</b></td>
													<td colspan='3'><textarea style='formInputText' name='inp_session_notes' rows='5' cols='50'>$session_notes</textarea></td></tr>";
					// now update the attend records
					if ($doDebug) {
						echo "<br />updating attend records<br />";
					}
					for ($snum=1;$snum<31;$snum++) {
						if ($snum < 10) {
							$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
						} else {
							$strSnum		= strval($snum);
						}
						if (isset(${'inp_attend_record_id_' . $strSnum})) {
							if ($doDebug) {
								echo "<br />processing student $strSnum<br />";
							}
							
							$attendSQL		= "select * from $sessionAttendTableName 
												where attend_record_id = ${'inp_attend_record_id_' . $strSnum}";
							$attendResult	= $wpdb->get_results($attendSQL);
							if ($attendResult === FALSE) {
								handleWPDBError($jobname,$doDebug,'pass 3 attempting to read the attend info');
							} else {
								$numASRows	= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $attendSQL<br />and retrieved $numASRows rows<br />";
								}
								if ($numASRows > 0) {
									foreach ($attendResult as $attendRow) {
										$attend_record_id				= $attendRow->attend_record_id;
										$attend_advisor_call_sign		= $attendRow->attend_advisor_call_sign;
										$attend_advisor_class_sequence	= $attendRow->attend_advisor_class_sequence;
										$attend_semester				= $attendRow->attend_semester;
										$attend_session					= $attendRow->attend_session;
										$attend_session_date			= $attendRow->attend_session_date;
										$attend_student_call_sign		= $attendRow->attend_student_call_sign;
										$attend_student_name			= $attendRow->attend_student_name;
										$attend_status					= $attendRow->attend_status;
										$attend_notes					= $attendRow->attend_notes;
										$attend_date_created			= $attendRow->attend_date_created;
										$attend_date_updated			= $attendRow->attend_date_updated;
										
										$doUpdate			= FALSE;
										$updateParams		= array();
										$updateFormat		= array();
										
										if (${'inp_attend_' . $strSnum} != $attend_status) {
											$attend_status					= ${'inp_attend_' . $strSnum};
											$updateParams['attend_status']	= $attend_status;
											$updateFormat[]					= '%s';
											$doUpdate						= TRUE;
										}
										if (${'inp_notes_' . $strSnum} != $attend_notes) {
											$attend_notes					= ${'inp_notes_' . $strSnum};
											$updateParams['attend_notes']	= $attend_notes;
											$updateFormat[]					= '%s';
											$doUpdate						= TRUE;
										}
										if ($doUpdate) {
											if ($doDebug) {
												echo "updates requested for student $strSnum:<br /><pre>";
												print_r($updateParams);
												echo "</pre><br />";
											}
											$updateNotesStatus		= $wpdb->update($sessionAttendTableName,
																					$updateParams,
																					array('attend_record_id' => ${'inp_attend_record_id_' . $strSnum}),
																					$updateFormat,
																					array('%d'));
											if ($updateNotesStatus === FALSE) {
												handleWPDBError($jobname,$doDebug,"attempting to update sessionAttend record ${'inp_attend_record_id_' . $strSnum}");
												goto Bypass;
											} 
										} else {
											if ($doDebug) {
												echo "no updates to student $strSnum requested<br />";
											}
										}
										$yesStr				= 'checked';
										$noStr				= '';
										if ($attend_status == 'N') {
											$yesStr			= '';
											$noStr			= 'checked';
										}
										$displayVariable	.= "<tr><td><b>Student $snum:</b></td>
																	<td colspan='3'>$attend_student_name ($attend_student_call_sign)</td><tr>
																<tr><td><b>Attended</td>
																	<td colspan='3'><input type='radio' class='formInputButton' name='inp_attend_$strSnum' value='Y' $yesStr >Yes<br />
																					<input type='radio' class='formInputButton' name='inp_attend_$strSnum' value='N' $noStr >No</td></tr>
																<tr><td style='vertical-align:top;'><b>Notes</b></td>
																	<td colspan='3'><textarea class='formInputText' name='inp_notes_$strSnum' rows='5' cols='50'>$attend_notes</textarea></td></tr>";
									}
								} else {
									$content		.= "</table><p>No sessionAttendance record found for record ${'inp_attend_record_id_' . $strSnum}</p>";
									goto Bypass;
								}
							}
						}
					}
				}
			
			} else {
				$content		.= "No sessionNotes record found for $inp_session_record_id";
				goto Bypass;
			}
		}
		$content		.= "$displayVariable</table>";
	}
	
	Bypass:
	
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
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
add_shortcode ('advisor_session_info', 'advisor_session_info_func');
