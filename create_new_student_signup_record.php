function create_new_student_signup_record_func() {

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
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	if ($userRole != 'administrator') {
		return "Function Reserved for Administrators Only";
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
	$theURL						= "$siteURL/cwa-create-new-student-signup-record/";
	$updateMaster				= "$siteURL/cwa-display-and-update-user-master-information/";
	$updateStudent				= "$siteURL/cwa-display-and-update-student-signup-information/";
	$jobname					= "Create New Student Signup Record V$versionNumber";
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$proximateSemester			= $initializationArray['proximateSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$semesterFour				= $initializationArray['semesterFour'];
	$inp_callsign				= '';
	$student_call_sign				= "";
	$inp_student_time_zone  			= "";
	$inp_student_timezone_offset		= "";
	$inp_student_youth  				= "";
	$inp_student_age  					= "";
	$inp_student_parent 				= "";
	$inp_student_parent_email  			= "";
	$inp_student_level  				= "";
	$inp_student_waiting_list 			= "";
	$inp_student_request_date  			= "";
	$inp_semester						= "";
	$inp_student_notes  				= "";
	$inp_student_welcome_date  			= "";
	$inp_student_email_sent_date  		= "";
	$inp_student_email_number  			= "";
	$inp_student_response  				= "";
	$inp_student_response_date  		= "";
	$inp_student_abandoned  			= "";
	$inp_student_status  				= "";
	$inp_student_action_log  			= "";
	$inp_student_pre_assigned_advisor  = "";
	$inp_student_selected_date  		= "";
	$inp_student_no_catalog  			= "";
	$inp_student_hold_override  		= "";
	$inp_student_assigned_advisor  		= "";
	$inp_student_advisor_select_date  	= "";
	$inp_student_advisor_class_timezone = "";
	$inp_student_hold_reason_code  		= "";
	$inp_student_class_priority  		= "";
	$inp_student_assigned_advisor_class = "";
	$inp_student_promotable  			= "";
	$inp_student_excluded_advisor  		= "";
	$inp_student_survey_completion_date	= "";
	$inp_student_available_class_days  	= "";
	$inp_student_intervention_required  = "";
	$inp_student_copy_control  			= "";
	$inp_student_first_class_choice  	= "";
	$inp_student_second_class_choice  	= "";
	$inp_student_third_class_choice  	= "";
	$inp_student_first_class_choice_utc = "";
	$inp_student_second_class_choice_utc = "";
	$inp_student_third_class_choice_utc  = "";
	$inp_student_catalog_options		= "";
	$inp_student_flexible				= "";

	$responseCode		= array(''=>'Not Specified',
								'Y'=>'Available',
								'R'=>'Declined');
	$waitingCode		= array(''=>'Not Specified (same as not on waiting list)',
								'Y'=>'On waiting list',
								'N'=>'Not on waiting list');
	$abandonedCode		= array(''=>'Did not abandon',
								'Y'=>'Student abandoned registration process',
								'N'=>'Student completed registration process',
								'0'=>'Did not abandon');
	$statusCode			= array(''=>'Not specified',
								'C'=>'Student has been replaced',
								'R'=>'Advisor has requested a replacement',
								'S'=>'Advisor has not verified the studet',
								'U'=>'No available class for student',
								'V'=>'Advisor has requested a replacement due to schedule',
								'Y'=>'Student verified');
	$reasonCode			= array(''=>'Not specified',
								'X'=>'Do not assign to same advisor',
								'E'=>'Student not evaluated but signed up for next level',
								'H'=>'Student not promotable but signed up for next level',
								'Q'=>'Advisor quite; student signed up for next level',
								'W'=>'Student withdrew but signed up for next level',
								'B'=>'Student is a bad actor');
	$promotableCode		= array(''=>'Not specified',
								'P'=>'Promotable',
								'N'=>'Not Promotable',
								'W'=>'Withdrew',
								'Q'=>'Advisor quit');
	$flexibleCode		= array(''=>'Not specified',
								'Y'=>'Schedule is flexible',
								'N'=>'Schedule is not flexible');
	$catalogCode		= array(''=>'Not Specified',
								'N'=>'Signed up with full catalog',
								'Y'=>'Signed up before full catalog');
	

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
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_time_zone") {
				$inp_student_time_zone = $str_value;
				$inp_student_time_zone = filter_var($inp_student_time_zone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_timezone_offset") {
				$inp_student_timezone_offset = $str_value;
				$inp_student_timezone_offset = filter_var($inp_student_timezone_offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_youth") {
				$inp_student_youth = $str_value;
				$inp_student_youth = filter_var($inp_student_youth,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_age") {
				$inp_student_age = $str_value;
				$inp_student_age = filter_var($inp_student_age,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_parent") {
				$inp_student_parent = $str_value;
				$inp_student_parent = filter_var($inp_student_parent,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_parent_email") {
				$inp_student_parent_email = $str_value;
				$inp_student_parent_email = filter_var($inp_student_parent_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_level") {
				$inp_student_level = $str_value;
				$inp_student_level = filter_var($inp_student_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_waiting_list") {
				$inp_student_waiting_list = $str_value;
				$inp_student_waiting_list = filter_var($inp_student_waiting_list,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_request_date") {
				$inp_student_request_date = $str_value;
				$inp_student_request_date = filter_var($inp_student_request_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_semester") {
				$inp_semester = $str_value;
				$inp_semester = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_notes") {
				$inp_student_notes = $str_value;
				$inp_student_notes = filter_var($inp_student_notes,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_welcome_date") {
				$inp_student_welcome_date = $str_value;
				$inp_student_welcome_date = filter_var($inp_student_welcome_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_email_sent_date") {
				$inp_student_email_sent_date = $str_value;
				$inp_student_email_sent_date = filter_var($inp_student_email_sent_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_email_number") {
				$inp_student_email_number = $str_value;
				$inp_student_email_number = filter_var($inp_student_email_number,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_response") {
				$inp_student_response = $str_value;
				$inp_student_response = filter_var($inp_student_response,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_response_date") {
				$inp_student_response_date = $str_value;
				$inp_student_response_date = filter_var($inp_student_response_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_abandoned") {
				$inp_student_abandoned = $str_value;
				$inp_student_abandoned = filter_var($inp_student_abandoned,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_status") {
				$inp_student_status = $str_value;
				$inp_student_status = filter_var($inp_student_status,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_action_log") {
				$inp_student_action_log = $str_value;
				$inp_student_action_log = filter_var($inp_student_action_log,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_pre_assigned_advisor") {
				$inp_student_pre_assigned_advisor = $str_value;
				$inp_student_pre_assigned_advisor = filter_var($inp_student_pre_assigned_advisor,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_selected_date") {
				$inp_student_selected_date = $str_value;
				$inp_student_selected_date = filter_var($inp_student_selected_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_no_catalog") {
				$inp_student_no_catalog = $str_value;
				$inp_student_no_catalog = filter_var($inp_student_no_catalog,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_hold_override") {
				$inp_student_hold_override = $str_value;
				$inp_student_hold_override = filter_var($inp_student_hold_override,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_messaging") {
				$inp_student_messaging = $str_value;
				$inp_student_messaging = filter_var($inp_student_messaging,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_assigned_advisor") {
				$inp_student_assigned_advisor = $str_value;
				$inp_student_assigned_advisor = filter_var($inp_student_assigned_advisor,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_advisor_select_date") {
				$inp_student_advisor_select_date = $str_value;
				$inp_student_advisor_select_date = filter_var($inp_student_advisor_select_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_advisor_class_timezone") {
				$inp_student_advisor_class_timezone = $str_value;
				$inp_student_advisor_class_timezone = filter_var($inp_student_advisor_class_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_hold_reason_code") {
				$inp_student_hold_reason_code = $str_value;
				$inp_student_hold_reason_code = filter_var($inp_student_hold_reason_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_class_priority") {
				$inp_student_class_priority = $str_value;
				$inp_student_class_priority = filter_var($inp_student_class_priority,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_assigned_advisor_class") {
				$inp_student_assigned_advisor_class = $str_value;
				$inp_student_assigned_advisor_class = filter_var($inp_student_assigned_advisor_class,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_promotable") {
				$inp_student_promotable = $str_value;
				$inp_student_promotable = filter_var($inp_student_promotable,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_excluded_advisor") {
				$inp_student_excluded_advisor = $str_value;
				$inp_student_excluded_advisor = filter_var($inp_student_excluded_advisor,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_survey_completion_date") {
				$inp_student_survey_completion_date = $str_value;
				$inp_student_survey_completion_date = filter_var($inp_student_survey_completion_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_available_class_days") {
				$inp_student_available_class_days = $str_value;
				$inp_student_available_class_days = filter_var($inp_student_available_class_days,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_intervention_required") {
				$inp_student_intervention_required = $str_value;
				$inp_student_intervention_required = filter_var($inp_student_intervention_required,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_copy_control") {
				$inp_student_copy_control = $str_value;
				$inp_student_copy_control = filter_var($inp_student_copy_control,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_first_class_choice") {
				$inp_student_first_class_choice = $str_value;
				$inp_student_first_class_choice = filter_var($inp_student_first_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_second_class_choice") {
				$inp_student_second_class_choice = $str_value;
				$inp_student_second_class_choice = filter_var($inp_student_second_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_third_class_choice") {
				$inp_student_third_class_choice = $str_value;
				$inp_student_third_class_choice = filter_var($inp_student_third_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_first_class_choice_utc") {
				$inp_student_first_class_choice_utc = $str_value;
				$inp_student_first_class_choice_utc = filter_var($inp_student_first_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_second_class_choice_utc") {
				$inp_student_second_class_choice_utc = $str_value;
				$inp_student_second_class_choice_utc = filter_var($inp_student_second_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_third_class_choice_utc") {
				$inp_student_third_class_choice_utc = $str_value;
				$inp_student_third_class_choice_utc = filter_var($inp_student_third_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_catalog_options") {
				$inp_student_catalog_options = $str_value;
				$inp_student_catalog_options = filter_var($inp_student_catalog_options,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_flexible") {
				$inp_student_flexible = $str_value;
				$inp_student_flexible = filter_var($inp_student_flexible,FILTER_UNSAFE_RAW);
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
		$userMasterTableName		= "wpw1_cwa_user_master2";
		$studentTableName			= "wpw1_cwa_student2";
	} else {
		$extMode					= 'pd';
		$userMasterTableName		= "wpw1_cwa_user_master";
		$studentTableName			= "wpw1_cwa_student";
	}




	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width: 200px;'>Student Callsign</td>
								<td><input type='text' class='formInputText' size='25' maxlenth='25' name='inp_callsign' required autofocus></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work
	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Pass 2 with inp_callsign: $inp_callsign<br />";
		}
		
		$doProceed	= TRUE;
		
		$content	.= "<h3>$jobname for $inp_callsign</h3>";

		// get the user_master record
		$sql		= "select * from $userMasterTableName 
						where user_call_sign like '$inp_callsign'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_call_sign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_ph_code			= $sqlRow->user_ph_code;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_country			= $sqlRow->user_country;
					$user_whatsapp			= $sqlRow->user_whatsapp;
					$user_telegram			= $sqlRow->user_telegram;
					$user_signal			= $sqlRow->user_signal;
					$user_messenger			= $sqlRow->user_messenger;
					$user_action_log		= $sqlRow->user_action_log;
					$user_timezone_id		= $sqlRow->user_timezone_id;
					$user_languages			= $sqlRow->user_languages;
					$user_survey_score		= $sqlRow->user_survey_score;
					$user_is_admin			= $sqlRow->user_is_admin;
					$user_role				= $sqlRow->user_role;
					$user_prev_callsign		= $sqlRow->user_prev_callsign;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;
	
					$myStr			= formatActionLog($user_action_log);
					$content		.= "<h4>User Master Data</h4>
									<table style='width:900px;'>
									<tr><td><b>Callsign<br />$user_call_sign</b></td>
										<td><b>Name</b><br />$user_last_name, $user_first_name</td>
										<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
										<td><b>Email</b><br />$user_email</td></tr>
									<tr><td><b>City</b><br />$user_city</td>
										<td><b>State</b><br />$user_state</td>
										<td><b>Zip Code</b><br />$user_zip_code</td>
										<td><b>Country</b><br />$user_country</td></tr>
									<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
										<td><b>Telegram</b><br />$user_telegram</td>
										<td><b>Signal</b><br />$user_signal</td>
										<td><b>Messenger</b><br />$user_messenger</td></tr>
									<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
										<td><b>Languages</b><br />$user_languages</td>
										<td><b>Date Created</b><br />user_$user_date_created</td>
										<td><b>Date Updated</b><br />user_$user_date_updated</td></tr>";
					if ($userRole == 'administrator') {
						$content .= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
										<td><b>Is Admin</b><br />$user_is_admin</td>
										<td><b>Role</b><br />$user_role</td>
										<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
									<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
					}
					$content	.= "</table>
									<p>Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=10' 
									target='_blank'>HERE</a> to update the advisor Master Data</p>";
									
									
					// get any current or future signup records
					$sql			= "select * from $studentTableName 
										where student_call_sign like '$inp_callsign' 
										and (student_semester = '$currentSemester' 
										or student_semester = '$nextSemester' 
										or student_semester = '$semesterTwo' 
										or student_semester = '$semesterThree' 
										or student_semester = '$semesterFour')";
					$wpw1_cwa_student	= $wpdb->get_results($sql);
					if ($wpw1_cwa_student === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numSRows									= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br >";
						}
						if ($numSRows > 0) {
							foreach ($wpw1_cwa_student as $studentRow) {
								$student_semester		= $studentRow->student_semester;
								$content	.= "<p>A student signup record already exists for $student_semester semester. 
												Unable to create a new student signup record.</p>";
								$doProceed	= FALSE;
							}
						} else {
							// no signup record so display code to create the record
							// set up semester options
							$semesterStr		 	= "";
							if ($currentSemester != 'Not in Session') {
								$semesterStr		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$currentSemester' required>$currentSemester (Semester is in session)<br />";
							}
							$semesterStr			.= "<input type='radio' class='formInputButton' name='inp_semester' value='$nextSemester' required>$nextSemester<br />
														<input type='radio' class='formInputButton' name='inp_semester' value='$semesterTwo' required>$semesterTwo<br />
														<input type='radio' class='formInputButton' name='inp_semester' value='$semesterThree' required>$semesterThree<br />
														<input type='radio' class='formInputButton' name='inp_semester' value='$semesterFour' required>$semesterFour";
							$content				.= "<h3>Create $inp_callsign Signup Record</h3>
														<form method='post' action='$theURL' 
														name='selection_form' ENCTYPE='multipart/form-data'>
														<input type='hidden' name='strpass' value='3'>
														<input type='hidden' name='inp_callsign' value='$inp_callsign'>
														<input type='hidden' name='inp_verbose' value='$inp_verbose'>
														<input type='hidden' name='inp_mode' value='$inp_mode'>
														<table style='width:900px;'>
														<tr><td>student_time_zone</td>
															<td><input type='text' class='formInputText' name='inp_student_time_zone' length='50' 
															maxlength='50' value='$user_timezone_id'></td></tr>
														<tr><td>student_timezone_offset<br /><em>If left blank will be calculated</em></td>
															<td><input type='text' class='formInputText' name='inp_student_timezone_offset' length='8' 
															maxlength='8'></td></tr>
														<tr><td style='vertical-align:top;'>Student Semester</td>
															<td>$semesterStr</td></tr>
														<tr><td style='vertical-align:top;'>student_youth</td>
															<td><input type='radio' class='formInputButton' name='inp_student_youth' value='No' checked>No<br />
																<input type='radio' class='formInputButton' name='inp_student_youth' value='Yes'>Yes</td></tr>
														<tr><td>student_age</td>
															<td><input type='text' class='formInputText' name='inp_student_age' length='3' 
															maxlength='3'></td></tr>
														<tr><td>student_parent</td>
															<td><input type='text' class='formInputText' name='inp_student_parent' length='50' 
															maxlength='50'></td></tr>
														<tr><td>student_parent_email</td>
															<td><input type='text' class='formInputText' name='inp_student_parent_email' length='50' 
															maxlength='50'></td></tr>
														<tr><td style='vertical-align:top;'>student_level</td>
															<td><input type='radio' class='formInputButton' name='inp_student_level' value='Beginner' required>Beginner<br />
																<input type='radio' class='formInputButton' name='inp_student_level' value='Fundamental' required>Fundamental<br />
																<input type='radio' class='formInputButton' name='inp_student_level' value='Intermediate' required>Intermediate<br />
																<input type='radio' class='formInputButton' name='inp_student_level' value='Advanced' required>Advanced</td></tr>
														<tr><td style='vertical-align:top;'>student_waiting_list</td>
															<td><input type='radio' class='formInputButton' name='inp_student_waiting_list' value='N' checked>N: Not on waiting list<br />
																<input type='radio' class='formInputButton' name='inp_student_waiting_list' value='Y'>Y: On waiting list</td></tr>
														<tr><td>student_request_date</td>
															<td><input type='text' class='formInputText' name='inp_student_request_date' length='20' 
															maxlength='20'></td></tr>
														<tr><td style='vertical-align:top;'>student_notes</td>
															<td><textarea class='formInputText' name='inp_student_notes' rows='5' cols='50'></textarea></td></tr>
														<tr><td>student_welcome_date</td>
															<td><input type='text' class='formInputText' name='inp_student_welcome_date' length='20' 
															maxlength='20'></td></tr>
														<tr><td>student_email_sent_date</td>
															<td><input type='text' class='formInputText' name='inp_student_email_sent_date' length='20' 
															maxlength='20'></td></tr>
														<tr><td>student_email_number</td>
															<td><input type='text' class='formInputText' name='inp_student_email_number' length='20' 
															maxlength='20' value='0'></td></tr>
														<tr><td style='vertical-align:top;'>student_response</td>
															<td ><input type='radio' class='formInputButton' name='inp_student_response' value='' checked>Not Specified<br />
																<input type='radio' class='formInputButton' name='inp_student_response' value='Y'>Y: Available<br />
																<input type='radio' class='formInputButton' name='inp_student_response' value='R'>R: Declined<br />
														<tr><td>student_response_date</td>
															<td><input type='text' class='formInputText' name='inp_student_response_date' length='20' 
															maxlength='20'></td></tr>
														<tr><td style='vertical-align:top;'>student_abandoned</td>
															<td><input type='radio' class='formInputButton' name='inp_student_abandoned' value='' checked>Not specified<br />
																<input type='radio' class='formInputButton' name='inp_student_abandoned' value='Y'>Y: Student abandoned registration process<br />
																<input type='radio' class='formInputButton' name='inp_student_abandoned' value='N'>N: Student completed registration</td></tr>
														<tr><td style='vertical-align:top;'>student_status</td>
															<td><input type='radio' class='formInputButton' name='inp_student_status' value='' checked>Not Specified<br />
																<input type='radio' class='formInputButton' name='inp_student_status' value='C'>C: Student has been replaced<br />
																<input type='radio' class='formInputButton' name='inp_student_status' value='R'>R: Advisor has requested a replacement<br />
																<input type='radio' class='formInputButton' name='inp_student_status' value='S'>S: Advisor has not verified the student<br />
																<input type='radio' class='formInputButton' name='inp_student_status' value='S'>U: No matching class availble<br />
																<input type='radio' class='formInputButton' name='inp_student_status' value='V'>V: Advisor has requested a replacement due to schedule<br />
																<input type='radio' class='formInputButton' name='inp_student_status' value='Y'>Y: Student verified</td>
														<tr><td style='vertical-align:top;'>student_action_log</td>
															<td><textarea class='formInputText' name='inp_student_action_log' rows='5' cols='50'></textarea></td></tr>
														<tr><td>student_pre_assigned_advisor</td>
															<td><input type='text' class='formInputText' name='inp_student_pre_assigned_advisor' length='15' 
															maxlength='15'></td></tr>
														<tr><td>student_selected_date</td>
															<td><input type='text' class='formInputText' name='inp_student_selected_date' length='20' 
															maxlength='20'></td></tr>
														<tr><td style='vertical-align:top;'>student_no_catalog</td>
															<td><input type='radio' class='formInputButton' name='inp_student_no_catalog' value='' checked>Not specified<br />
																<input type='radio' class='formInputButton' name='inp_student_no_catalog' value=''>N: Signed up with full catalog<br />
																<input type='radio' class='formInputButton' name='inp_student_no_catalog' value=''>Y: Signed up before full catalog</td></tr>
														<tr><td>student_hold_override</td>
															<td><input type='text' class='formInputText' name='inp_student_hold_override' length='1' 
															maxlength='1'></td></tr>
														<tr><td>student_assigned_advisor</td>
															<td><input type='text' class='formInputText' name='inp_student_assigned_advisor' length='15' 
															maxlength='15'></td></tr>
														<tr><td>student_advisor_select_date</td>
															<td><input type='text' class='formInputText' name='inp_student_advisor_select_date' length='20' 
															maxlength='20'></td></tr>
														<tr><td>student_advisor_class_timezone</td>
															<td><input type='text' class='formInputText' name='inp_student_advisor_class_timezone' length='10' 
															maxlength='10'></td></tr>
														<tr><td style='vertical-align:top;'>student_hold_reason_code</td>
															<td><input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='' checked>Not specified<br />
																<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='X'>X: Do not assign to same advisor<br />
																<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='E'>Student not evaluated but signed up for next level<br />
																<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='H'>H: Student not promotable but signed up for next level<br />
																<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='Q'>Q: Advisor quit; student signed up for next level <br />
																<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='W'>W: Student withdrew but signed up for next level<br />
																<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='B'>B: Student is a bad actor</td></tr>
														<tr><td>student_class_priority</td>
															<td><input type='text' class='formInputText' name='inp_student_class_priority' length='20' 
															maxlength='5'></td></tr>
														<tr><td>student_assigned_advisor_class</td>
															<td><input type='text' class='formInputText' name='inp_student_assigned_advisor_class' length='1' 
															maxlength='1'></td></tr>
														<tr><td style='vertical-align:top;'>student_promotable</td>
															<td><input type='radio' class='formInputButton' name='inp_student_promotable' value='' checked>Not Specified<br />
																<input type='radio' class='formInputButton' name='inp_student_promotable' value='P'>Promotable<br />
																<input type='radio' class='formInputButton' name='inp_student_promotable' value='P'>Not Promotable<br />
																<input type='radio' class='formInputButton' name='inp_student_promotable' value='W'>Withdrew<br />
																<input type='radio' class='formInputButton' name='inp_student_promotable' value='Q'>Advisor quit</td></tr>
														<tr><td style='vertical-align:top;'>student_excluded_advisor</td>
															<td><textarea class='formInputText' name='inp_student_excluded_advisor' rows='5' cols='50'></textarea></td></tr>
														<tr><td>student_survey_completion_date</td>
															<td><input type='text' class='formInputText' name='inp_student_survey_completion_date' length='20' 
															maxlength='20'></td></tr>
														<tr><td>student_available_class_days</td>
															<td><input type='text' class='formInputText' name='inp_student_available_class_days' length='100' 
															maxlength='100'></td></tr>
														<tr><td>student_intervention_required</td>
															<td><input type='text' class='formInputText' name='inp_student_intervention_required' length='1' 
															maxlength='1'></td></tr>
														<tr><td>student_copy_control</td>
															<td><input type='text' class='formInputText' name='inp_student_copy_control' length='20' 
															maxlength='20'></td></tr>
														<tr><td>student_first_class_choice</td>
															<td><input type='text' class='formInputText' name='inp_student_first_class_choice' length='50' 
															maxlength='50' value='None'></td></tr>
														<tr><td>student_second_class_choice</td>
															<td><input type='text' class='formInputText' name='inp_student_second_class_choice' length='50' 
															maxlength='50' value='None'></td></tr>
														<tr><td>student_third_class_choice</td>
															<td><input type='text' class='formInputText' name='inp_student_third_class_choice' length='50' 
															maxlength='50' value='None'></td></tr>
														<tr><td>student_first_class_choice_utc</td>
															<td><input type='text' class='formInputText' name='inp_student_first_class_choice_utc' length='50' 
															maxlength='50' value='None'></td></tr>
														<tr><td>student_second_class_choice_utc</td>
															<td><input type='text' class='formInputText' name='inp_student_second_class_choice_utc' length='50' 
															maxlength='50' value='None'></td></tr>
														<tr><td>student_third_class_choice_utc</td>
															<td><input type='text' class='formInputText' name='inp_student_third_class_choice_utc' length='50' 
															maxlength='50' value='None'></td></tr>
														<tr><td>student_catalog_options</td>
															<td><input type='text' class='formInputText' name='inp_student_catalog_options' length='100' 
															maxlength='100'></td></tr>
														<tr><td style='vertical-align:top;'>student_flexible</td>
															<td><input type='radio' class='formInputButton' name='inp_student_flexible' value='' checked>Not specified<br />
																<input type='radio' class='formInputButton' name='inp_student_flexible' value='Y'>Y: Schedule is flexible<br />
																<input type='radio' class='formInputButton' name='inp_student_flexible' value='N'>N: Schedule is not flexible</td></tr>
														<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
														</table></form>";
						}
					}
						
							
				}
			} else {
				$content		.= "<p>No user master record found for $inp_callsign. Unable to create a student signup record.</p>";
			}
		}
//////// Pass 3
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 3<br />";
		}

// setup to add the student

		if ($inp_student_timezone_offset == '') {
			$inp_student_timezone_offset	= getOffsetFromIdentifier($inp_student_time_zone,$inp_semester,$doDebug);

		}
		$updateParams			= array('student_call_sign'=>$inp_callsign,
										'student_time_zone'=>$inp_student_time_zone,
										'student_timezone_offset'=>$inp_student_timezone_offset,
										'student_youth'=>$inp_student_youth,
										'student_age'=>$inp_student_age,
										'student_parent'=>$inp_student_parent,
										'student_parent_email'=>$inp_student_parent_email,
										'student_level'=>$inp_student_level,
										'student_waiting_list'=>$inp_student_waiting_list,
										'student_request_date'=>$inp_student_request_date,
										'student_semester'=>$inp_semester,
										'student_notes'=>$inp_student_notes,
										'student_welcome_date'=>$inp_student_welcome_date,
										'student_email_sent_date'=>$inp_student_email_sent_date,
										'student_email_number'=>$inp_student_email_number,
										'student_response'=>$inp_student_response,
										'student_response_date'=>$inp_student_response_date,
										'student_abandoned'=>$inp_student_abandoned,
										'student_status'=>$inp_student_status,
										'student_action_log'=>$inp_student_action_log,
										'student_pre_assigned_advisor'=>$inp_student_pre_assigned_advisor,
										'student_selected_date'=>$inp_student_selected_date,
										'student_no_catalog'=>$inp_student_no_catalog,
										'student_hold_override'=>$inp_student_hold_override,
										'student_assigned_advisor'=>$inp_student_assigned_advisor,
										'student_advisor_select_date'=>$inp_student_advisor_select_date,
										'student_advisor_class_timezone'=>$inp_student_advisor_class_timezone,
										'student_hold_reason_code'=>$inp_student_hold_reason_code,
										'student_class_priority'=>$inp_student_class_priority,
										'student_assigned_advisor_class'=>$inp_student_assigned_advisor_class,
										'student_promotable'=>$inp_student_promotable,
										'student_excluded_advisor'=>$inp_student_excluded_advisor,
										'student_survey_completion_date'=>$inp_student_survey_completion_date,
										'student_available_class_days'=>$inp_student_available_class_days,
										'student_intervention_required'=>$inp_student_intervention_required,
										'student_copy_control'=>$inp_student_copy_control,
										'student_first_class_choice'=>$inp_student_first_class_choice,
										'student_second_class_choice'=>$inp_student_second_class_choice,
										'student_third_class_choice'=>$inp_student_third_class_choice,
										'student_first_class_choice_utc'=>$inp_student_first_class_choice_utc,
										'student_second_class_choice_utc'=>$inp_student_second_class_choice_utc,
										'student_third_class_choice_utc'=>$inp_student_third_class_choice_utc,
										'student_catalog_options'=>$inp_student_catalog_options,
										'student_flexible'=>$inp_student_flexible);

		$updateFormat			= array('%s',
										'%s',
										'%f',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%d',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%d',
										'%d',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s',
										'%s');

		$studentUpdateData		= array('tableName'=>$studentTableName,
										'inp_method'=>'add',
										'inp_data'=>$updateParams,
										'inp_format'=>$updateFormat,
										'jobname'=>$jobname,
										'inp_id'=>0,
										'inp_callsign'=>$inp_callsign,
										'inp_semester'=>$inp_semester,
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
			$inp_id			= $updateResult[1];
			$content		.= "<p>Student $inp_callsign signup record has been added</p>";
			// get the user_master info and format it
			if ($doDebug) {
				echo "getting the user_master data<br />";
			}
			$sql			= "select * from $userMasterTableName 
								where user_call_sign like '$inp_callsign'";
			$sqlResult		= $wpdb->get_results($sql);
			if ($sqlResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					foreach($sqlResult as $sqlRow) {
						$user_id				= $sqlRow->user_ID;
						$user_call_sign			= $sqlRow->user_call_sign;
						$user_first_name		= $sqlRow->user_first_name;
						$user_last_name			= $sqlRow->user_last_name;
						$user_email				= $sqlRow->user_email;
						$user_ph_code			= $sqlRow->user_ph_code;
						$user_phone				= $sqlRow->user_phone;
						$user_city				= $sqlRow->user_city;
						$user_state				= $sqlRow->user_state;
						$user_zip_code			= $sqlRow->user_zip_code;
						$user_country_code		= $sqlRow->user_country_code;
						$user_country			= $sqlRow->user_country;
						$user_whatsapp			= $sqlRow->user_whatsapp;
						$user_telegram			= $sqlRow->user_telegram;
						$user_signal			= $sqlRow->user_signal;
						$user_messenger			= $sqlRow->user_messenger;
						$user_action_log		= $sqlRow->user_action_log;
						$user_timezone_id		= $sqlRow->user_timezone_id;
						$user_languages			= $sqlRow->user_languages;
						$user_survey_score		= $sqlRow->user_survey_score;
						$user_is_admin			= $sqlRow->user_is_admin;
						$user_role				= $sqlRow->user_role;
						$user_prev_callsign		= $sqlRow->user_prev_callsign;
						$user_date_created		= $sqlRow->user_date_created;
						$user_date_updated		= $sqlRow->user_date_updated;
		
						$myStr			= formatActionLog($user_action_log);
						
						$content		.= "<h4>Student Master Data</h4>
										<table style='width:900px;'>
										<tr><td><b>Callsign<br />$user_call_sign</b></td>
											<td><b>Name</b><br />$user_last_name, $user_first_name</td>
											<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
											<td><b>Email</b><br />$user_email</td></tr>
										<tr><td><b>City</b><br />$user_city</td>
											<td><b>State</b><br />$user_state</td>
											<td><b>Zip Code</b><br />$user_zip_code</td>
											<td><b>Country</b><br />$user_country</td></tr>
										<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
											<td><b>Telegram</b><br />$user_telegram</td>
											<td><b>Signal</b><br />$user_signal</td>
											<td><b>Messenger</b><br />$user_messenger</td></tr>
										<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
											<td><b>Languages</b><br />$user_languages</td>
											<td><b>Date Created</b><br />$user_date_created</td>
											<td><b>Date Updated</b><br />$user_date_updated</td></tr>
										<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>
										</table>
										<p>Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=10' 
										target='_blank'>HERE</a> to update the advisor Master Data</p>";
			
						// get the student signup info
						$sql			= "select * from $studentTableName 
											where student_id = $inp_id";
						$wpw1_cwa_student		= $wpdb->get_results($sql);
						if ($wpw1_cwa_student === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numRows 			= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $sql<br /> and found $numRows rows in $studentTableName table";
							}
							if ($numRows > 0) {
								foreach ($wpw1_cwa_student as $studentRow) {
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
						
									$student_action_log						= formatActionLog($student_action_log);
									$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
									
									$updateLink			= "<a href='$updateStudent/?strpass=3&inp_callsign=$inp_callsign&inp_student_id=$student_ID&inp_verbose=$inp_verbose&inp_mode=$inp_mode'>$student_ID<a/>";
									$preAssignedLink	= '';
									if ($student_pre_assigned_advisor != '') {
										$preAssignedLink	= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=10' target='_blank'>$student_pre_assigned_advisor</a>";
									}
									$assignedLink		= '';
									if ($student_assigned_advisor != '') {
										$assignedLink		= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=10' target='_blank'>$student_assigned_advisor</a>";
									}

									$responseStr	= $responseCode[$student_response];
									$waitingStr		= $waitingCode[$student_waiting_list];
									$abandonedStr	= $abandonedCode[$student_abandoned];
									$statusStr		= $statusCode[$student_status];
									$reasonStr		= $reasonCode[$student_hold_reason_code];
									$promotableStr	= $promotableCode[$student_promotable];
									$flexibleStr	= $flexibleCode[$student_flexible];
									$catalogStr		= $catalogCode[$student_no_catalog];

				
									$content			.= "<h4>Student Signup Created $student_date_created</h4>
															<table style='width:900px;'>
															<tr><td style='width:250px;'>Student Student Id<td>
																<td>$updateLink</td></tr>
															<tr><td>Student Call Sign<td>
																<td>$student_call_sign</td></tr>
															<tr><td>Student Time Zone<td>
																<td>$student_time_zone</td></tr>
															<tr><td>Student Timezone Offset<td>
																<td>$student_timezone_offset</td></tr>
															<tr><td>Student Youth<td>
																<td>$student_youth</td></tr>
															<tr><td>Student Age<td>
																<td>$student_age</td></tr>
															<tr><td>Student Student Parent<td>
																<td>$student_parent</td></tr>
															<tr><td>Student Student Parent Email<td>
																<td>$student_parent_email</td></tr>
															<tr><td>Student Level<td>
																<td>$student_level</td></tr>
															<tr><td>Student Waiting List<td>
																<td>$student_waiting_list $waitingStr</td></tr>
															<tr><td>Student Request Date<td>
																<td>$student_request_date</td></tr>
															<tr><td>Student Semester<td>
																<td>$student_semester</td></tr>
															<tr><td>Student Notes<td>
																<td style='vertical-align:top;'>$student_notes</td></tr>
															<tr><td>Student Welcome Date<td>
																<td>$student_welcome_date</td></tr>
															<tr><td>Student Email Sent Date<td>
																<td>$student_email_sent_date</td></tr>
															<tr><td>Student Email Number<td>
																<td>$student_email_number</td></tr>
															<tr><td>Student Response<td>
																<td>$student_response $responseStr</td></tr>
															<tr><td>Student Response Date<td>
																<td>$student_response_date</td></tr>
															<tr><td>Student Abandoned<td>
																<td>$student_abandoned $abandonedStr</td></tr>
															<tr><td>Student Student Status<td>
																<td>$student_status $statusStr</td></tr>
															<tr><td style='vertical-align:top;'>Student Action Log<td>
																<td>$student_action_log</td></tr>
															<tr><td>Student Pre Assigned Advisor<td>
																<td>$preAssignedLink</td></tr>
															<tr><td>Student Selected Date<td>
																<td>$student_selected_date</td></tr>
															<tr><td>Student No Catalog<td>
																<td>$student_no_catalog $catalogStr</td></tr>
															<tr><td>Student Hold Override<td>
																<td>$student_hold_override</td></tr>
															<tr><td>Student Assigned Advisor<td>
																<td>$assignedLink</td></tr>
															<tr><td>Student Advisor Select Date<td>
																<td>$student_advisor_select_date</td></tr>
															<tr><td>Student Advisor Class Timezone<td>
																<td>$student_advisor_class_timezone</td></tr>
															<tr><td>Student Hold Reason Code<td>
																<td>$student_hold_reason_code $reasonStr</td></tr>
															<tr><td>Student Class Priority<td>
																<td>$student_class_priority</td></tr>
															<tr><td>Student Assigned Advisor Class<td>
																<td>$student_assigned_advisor_class</td></tr>
															<tr><td>Student Promotable<td>
																<td>$student_promotable $promotableStr</td></tr>
															<tr><td>Student Excluded Advisor<td>
																<td>$student_excluded_advisor</td></tr>
															<tr><td>Student Student Survey Completion Date<td>
																<td>$student_survey_completion_date</td></tr>
															<tr><td>Student Available Class Days<td>
																<td>$student_available_class_days</td></tr>
															<tr><td>Student Intervention Required<td>
																<td>$student_intervention_required</td></tr>
															<tr><td>Student Copy Control<td>
																<td>$student_copy_control</td></tr>
															<tr><td>Student First Class Choice<td>
																<td>$student_first_class_choice</td></tr>
															<tr><td>Student Second Class Choice<td>
																<td>$student_second_class_choice</td></tr>
															<tr><td>Student Third Class Choice<td>
																<td>$student_third_class_choice</td></tr>
															<tr><td>Student First Class Choice utc<td>
																<td>$student_first_class_choice_utc</td></tr>
															<tr><td>Student Second Class Choice utc<td>
																<td>$student_second_class_choice_utc</td></tr>
															<tr><td>Student Third Class Choice utc<td>
																<td>$student_third_class_choice_utc</td></tr>
															<tr><td>Student Catalog Options<td>
																<td>$student_catalog_options</td></tr>
															<tr><td>Student Flexible<td>
																<td>$student_flexible $flexibleStr</td></tr>
															<tr><td>Student Date Created<td>
																<td>$student_date_created</td></tr>
															<tr><td>Student Date Updated<td>
																<td>$student_date_updated</td></tr>
															</table>
															<p>Click <a href='$theURL/?strpass=3&inp_callsign=$inp_callsign&inp_student_id=$student_ID'>HERE</a>
															to modify this signup record</p>
															<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student</p>";
								}
							} else {
								$content		.= "<p>No signup record found for $inp_callsign</p>";
							}
						}
					}
				} else {
					$content		.= "<p>No user master record found for $inp_callsign</p>";
				}
			}
		}
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
/*
	///// uncomment if the code to save a report is needed
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Current Student and Advisor Assignments<br />";
		}
		$storeResult	= storeReportData_v2($jobname,$content);
		if ($storeResult[0] !== FALSE) {
			$reportName	= $storeResult[1];
			$reportID	= $storeResult[2];
			$content	.= "<br />Report stored in reports as $reportName<br />
							Go to'Display Saved Reports' or url<br/>
							$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID<br /><br />";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}
*/
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
add_shortcode ('create_new_student_signup_record', 'create_new_student_signup_record_func');
