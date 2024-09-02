function display_and_update_student_signup_func() {

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
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
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
	$theURL						= "$siteURL/cwa-display-and-update-student-signup-information/";
	$inp_semester				= '';
	$inp_callsign				= "";
	$jobname					= "Display and Update Student Signup Information V$versionNumber";

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
			if ($str_key		== "inp_callsign") {
				$inp_callsign	= strtoupper($str_value);
				$inp_callsign	= filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_student_id") {
				$inp_student_id	= strtoupper($str_value);
				$inp_student_id	= filter_var($inp_student_id,FILTER_UNSAFE_RAW);
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
			if ($str_key == "inp_student_student_parent") {
				$inp_student_student_parent = $str_value;
				$inp_student_student_parent = filter_var($inp_student_student_parent,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_student_parent_email") {
				$inp_student_student_parent_email = $str_value;
				$inp_student_student_parent_email = filter_var($inp_student_student_parent_email,FILTER_UNSAFE_RAW);
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
			if ($str_key == "inp_student_semester") {
				$inp_student_semester = $str_value;
				$inp_student_semester = filter_var($inp_student_semester,FILTER_UNSAFE_RAW);
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
			if ($str_key == "inp_student_student_status") {
				$inp_student_student_status = $str_value;
				$inp_student_student_status = filter_var($inp_student_student_status,FILTER_UNSAFE_RAW);
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
			if ($str_key == "inp_student_student_survey_completion_date") {
				$inp_student_student_survey_completion_date = $str_value;
				$inp_student_student_survey_completion_date = filter_var($inp_student_student_survey_completion_date,FILTER_UNSAFE_RAW);
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
			if ($str_key == "inp_student_date_created") {
				$inp_student_date_created = $str_value;
				$inp_student_date_created = filter_var($inp_student_date_created,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_date_updated") {
				$inp_student_date_updated = $str_value;
				$inp_student_date_updated = filter_var($inp_student_date_updated,FILTER_UNSAFE_RAW);
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
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
	}



	if ("1" == $strPass) {
	
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Student Callsign</td>
								<td><input type='text' class='formInputText' name='inp_callsign' size='15' maxlength='20' autofocus'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 2 with inp_+callsign: $inp_callsign<br />";
		}
		
		$content		.= "<h3>Display and Update $inp_callsign Signup Info</h3>
							<p><a href='$theURL'>Look Up a Different Student</a>";
		
		// get the user_master info and format it
		if ($doDebug) {
			echo "getting the user_master data<br />";
		}
		$result			= "";
		$reason			= "";
		$callsign		= "";
		$first_name		= "";
		$last_name		= "";
		$email			= "";
		$phone			= "";
		$ph_code		= "";
		$city			= "";
		$state			= "";
		$zip_code		= "";
		$country_code	= "";
		$country		= "";
		$whatsapp		= "";
		$telegram		= "";
		$signal			= "";
		$messenger		= "";
		$languages		= "";
		$timezone_id	= "";
		$date_created	= "";
		$date_updated	= "";

		$dataArray			= array('callsign'=>$inp_callsign,
									'action'=>'get',
									'debugging'=> $doDebug,
									'testing'=> $testMode);
		$dataResult				= user_master_data($dataArray);
		foreach($dataResult as $thisField => $thisValue) {
			$$thisField		= $thisValue;
		}		
		if ($result == FALSE) {
			$content		.= "Failed to get the student master information. Reason: $reason";			
		} else {
			$myStr			= formatActionLog($user_action_log);
			$content		.= "<h4>Student Master Data</h4>
							<table style='width:900px;'>
							<tr><td><b>Callsign<br />$callsign</b></td>
								<td><b>Name</b><br />$last_name, $first_name</td>
								<td><b>Phone</b><br />+$ph_code $phone</td>
								<td><b>Email</b><br />$email</td></tr>
							<tr><td><b>City</b><br />$city</td>
								<td><b>State</b><br />$state</td>
								<td><b>Zip Code</b><br />$zip_code</td>
								<td><b>Country</b><br />$country</td></tr>
							<tr><td><b>WhatsApp</b><br />$whatsapp</td>
								<td><b>Telegram</b><br />$telegram</td>
								<td><b>Signal</b><br />$signal</td>
								<td><b>Messenger</b><br />$messenger</td></tr>
							<tr><td><b>Timezone ID</b><br />$timezone_id</td>
								<td><b>Languages</b><br />$languages</td>
								<td><b>Date Created</b><br />$date_created</td>
								<td><b>Date Updated</b><br />$date_updated</td></tr>
							<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>
							</table>
							<p>Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=3&inp_callsign=$inp_callsign' target='_blank'>HERE</a> to update the Student Master Data</p>";
		}

		// get the student signup info
		$sql			= "select * from $studentTableName 
							where call_sign = '$inp_callsign' 
							order by date_created DESC";
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
					$student_student_id 					= $studentRow -> student_id;
					$student_call_sign 						= $studentRow -> call_sign;
					$student_time_zone 						= $studentRow -> time_zone;
					$student_timezone_offset 				= $studentRow -> timezone_offset;
					$student_youth 							= $studentRow -> youth;
					$student_age 							= $studentRow -> age;
					$student_student_parent 				= $studentRow -> student_parent;
					$student_student_parent_email 			= $studentRow -> student_parent_email;
					$student_level 							= $studentRow -> level;
					$student_waiting_list 					= $studentRow -> waiting_list;
					$student_request_date 					= $studentRow -> request_date;
					$student_semester 						= $studentRow -> semester;
					$student_notes 							= $studentRow -> notes;
					$student_welcome_date 					= $studentRow -> welcome_date;
					$student_email_sent_date 				= $studentRow -> email_sent_date;
					$student_email_number 					= $studentRow -> email_number;
					$student_response 						= $studentRow -> response;
					$student_response_date 					= $studentRow -> response_date;
					$student_abandoned 						= $studentRow -> abandoned;
					$student_student_status 				= $studentRow -> student_status;
					$student_action_log 					= $studentRow -> action_log;
					$student_pre_assigned_advisor 			= $studentRow -> pre_assigned_advisor;
					$student_selected_date 					= $studentRow -> selected_date;
					$student_no_catalog 					= $studentRow -> no_catalog;
					$student_hold_override 					= $studentRow -> hold_override;
					$student_messaging 						= $studentRow -> messaging;
					$student_assigned_advisor 				= $studentRow -> assigned_advisor;
					$student_advisor_select_date 			= $studentRow -> advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow -> advisor_class_timezone;
					$student_hold_reason_code 				= $studentRow -> hold_reason_code;
					$student_class_priority 				= $studentRow -> class_priority;
					$student_assigned_advisor_class 		= $studentRow -> assigned_advisor_class;
					$student_promotable 					= $studentRow -> promotable;
					$student_excluded_advisor 				= $studentRow -> excluded_advisor;
					$student_student_survey_completion_date = $studentRow -> student_survey_completion_date;
					$student_available_class_days 			= $studentRow -> available_class_days;
					$student_intervention_required 			= $studentRow -> intervention_required;
					$student_copy_control 					= $studentRow -> copy_control;
					$student_first_class_choice 			= $studentRow -> first_class_choice;
					$student_second_class_choice 			= $studentRow -> second_class_choice;
					$student_third_class_choice 			= $studentRow -> third_class_choice;
					$student_first_class_choice_utc 		= $studentRow -> first_class_choice_utc;
					$student_second_class_choice_utc 		= $studentRow -> second_class_choice_utc;
					$student_third_class_choice_utc 		= $studentRow -> third_class_choice_utc;
					$student_catalog_options 				= $studentRow -> catalog_options;
					$student_flexible 						= $studentRow -> flexible;
					$student_date_created 					= $studentRow -> date_created;
					$student_date_updated 					= $studentRow -> date_updated;
		
					$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
					$student_action_log						= formatActionLog($student_action_log);
					
					$updateLink			= "<a href='$theURL/?strpass=3&inp_callsign=$inp_callsign&inp_student_id=$student_student_id'>$student_student_id<a/>";

					$content			.= "<h4>Student Signup Created $student_date_created</h4>
											<table style='width:900px;'>
											<tr><td>Student Student Id<td>
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
												<td>$student_student_parent</td></tr>
											<tr><td>Student Student Parent Email<td>
												<td>$student_student_parent_email</td></tr>
											<tr><td>Student Level<td>
												<td>$student_level</td></tr>
											<tr><td>Student Waiting List<td>
												<td>$student_waiting_list</td></tr>
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
												<td>$student_response</td></tr>
											<tr><td>Student Response Date<td>
												<td>$student_response_date</td></tr>
											<tr><td>Student Abandoned<td>
												<td>$student_abandoned</td></tr>
											<tr><td>Student Student Status<td>
												<td>$student_student_status</td></tr>
											<tr><td style='vertical-align:top;'>Student Action Log<td>
												<td>$student_action_log</td></tr>
											<tr><td>Student Pre Assigned Advisor<td>
												<td>$student_pre_assigned_advisor</td></tr>
											<tr><td>Student Selected Date<td>
												<td>$student_selected_date</td></tr>
											<tr><td>Student No Catalog<td>
												<td>$student_no_catalog</td></tr>
											<tr><td>Student Hold Override<td>
												<td>$student_hold_override</td></tr>
											<tr><td>Student Messaging<td>
												<td>$student_messaging</td></tr>
											<tr><td>Student Assigned Advisor<td>
												<td>$student_assigned_advisor</td></tr>
											<tr><td>Student Advisor Select Date<td>
												<td>$student_advisor_select_date</td></tr>
											<tr><td>Student Advisor Class Timezone<td>
												<td>$student_advisor_class_timezone</td></tr>
											<tr><td>Student Hold Reason Code<td>
												<td>$student_hold_reason_code</td></tr>
											<tr><td>Student Class Priority<td>
												<td>$student_class_priority</td></tr>
											<tr><td>Student Assigned Advisor Class<td>
												<td>$student_assigned_advisor_class</td></tr>
											<tr><td>Student Promotable<td>
												<td>$student_promotable</td></tr>
											<tr><td>Student Excluded Advisor<td>
												<td>$student_excluded_advisor</td></tr>
											<tr><td>Student Student Survey Completion Date<td>
												<td>$student_student_survey_completion_date</td></tr>
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
											<tr><td>Student First Class Choice Utc<td>
												<td>$student_first_class_choice_utc</td></tr>
											<tr><td>Student Second Class Choice Utc<td>
												<td>$student_second_class_choice_utc</td></tr>
											<tr><td>Student Third Class Choice Utc<td>
												<td>$student_third_class_choice_utc</td></tr>
											<tr><td>Student Catalog Options<td>
												<td>$student_catalog_options</td></tr>
											<tr><td>Student Flexible<td>
												<td>$student_flexible</td></tr>
											<tr><td>Student Date Created<td>
												<td>$student_date_created</td></tr>
											<tr><td>Student Date Updated<td>
												<td>$student_date_updated</td></tr>
											</table>
											<p>Click <a href='$theURL/?strpass=3&inp_callsign=$inp_callsign&inp_student_id=$student_student_id'>HERE</a>
											to modify this signup record</p>
											<p>Click <a href='$theURL'>Look Up a Different Student</a>";
				}
			} else {
				$content		.= "<p>No signup record found for $inp_callsign</p>";
			}
		}
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2 with inp_callsign: $inp_callsign and inp_student_id: $inp_student_id<br />";
		}
		
		// get the record to be updated
		$sql			= "select * from $studentTableName 
							where student_id = $inp_student_id";
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
					$student_student_id 					= $studentRow -> student_id;
					$student_call_sign 						= $studentRow -> call_sign;
					$student_time_zone 						= $studentRow -> time_zone;
					$student_timezone_id 					= $studentRow -> timezone_id;
					$student_youth 							= $studentRow -> youth;
					$student_age 							= $studentRow -> age;
					$student_student_parent 				= $studentRow -> student_parent;
					$student_student_parent_email 			= $studentRow -> student_parent_email;
					$student_level 							= $studentRow -> level;
					$student_waiting_list 					= $studentRow -> waiting_list;
					$student_request_date 					= $studentRow -> request_date;
					$student_semester 						= $studentRow -> semester;
					$student_notes 							= $studentRow -> notes;
					$student_welcome_date 					= $studentRow -> welcome_date;
					$student_email_sent_date 				= $studentRow -> email_sent_date;
					$student_email_number 					= $studentRow -> email_number;
					$student_response 						= $studentRow -> response;
					$student_response_date 					= $studentRow -> response_date;
					$student_abandoned 						= $studentRow -> abandoned;
					$student_student_status 				= $studentRow -> student_status;
					$student_action_log 					= $studentRow -> action_log;
					$student_pre_assigned_advisor 			= $studentRow -> pre_assigned_advisor;
					$student_selected_date 					= $studentRow -> selected_date;
					$student_no_catalog 					= $studentRow -> no_catalog;
					$student_hold_override 					= $studentRow -> hold_override;
					$student_messaging 						= $studentRow -> messaging;
					$student_assigned_advisor 				= $studentRow -> assigned_advisor;
					$student_advisor_select_date 			= $studentRow -> advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow -> advisor_class_timezone;
					$student_hold_reason_code 				= $studentRow -> hold_reason_code;
					$student_class_priority 				= $studentRow -> class_priority;
					$student_assigned_advisor_class 		= $studentRow -> assigned_advisor_class;
					$student_promotable 					= $studentRow -> promotable;
					$student_excluded_advisor 				= $studentRow -> excluded_advisor;
					$student_student_survey_completion_date = $studentRow -> student_survey_completion_date;
					$student_available_class_days 			= $studentRow -> available_class_days;
					$student_intervention_required 			= $studentRow -> intervention_required;
					$student_copy_control 					= $studentRow -> copy_control;
					$student_first_class_choice 			= $studentRow -> first_class_choice;
					$student_second_class_choice 			= $studentRow -> second_class_choice;
					$student_third_class_choice 			= $studentRow -> third_class_choice;
					$student_first_class_choice_utc 		= $studentRow -> first_class_choice_utc;
					$student_second_class_choice_utc 		= $studentRow -> second_class_choice_utc;
					$student_third_class_choice_utc 		= $studentRow -> third_class_choice_utc;
					$student_catalog_options 				= $studentRow -> catalog_options;
					$student_flexible 						= $studentRow -> flexible;
					$student_date_created 					= $studentRow -> date_created;
					$student_date_updated 					= $studentRow -> date_updated;
					
					$content				.= "<h3>Update $inp_callsign Signup Record</h3>
												<form method='post' action='$theURL' 
												name='deletion_form' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='5'>
												<input type='hidden' name='inp_student_id' value='$inp_student_id'>
												<input class='formInputButton' type='submit' onclick=\"return confirm('Are you sure?');\"  value='Delete This Record' />
												</form><br />
												<form method='post' action='$theURL' 
												name='selection_form' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='4'>
												<input type='hidden' name='inp_student_id' value='$inp_student_id'>
												<input type='hidden' name='inp_callsign' value='$inp_callsign'>
												<table style='width:900px;'>
												<tr><td>student_student_id</td>
													<td>$student_student_id</td></tr>
												<tr><td>student_call_sign</td>
													<td>$student_call_sign</td></tr>
												<tr><td>student_time_zone</td>
													<td><input type='text' class='formInputText' name='inp_student_time_zone' length='50' 
													maxlength='50' value='$student_time_zone'></td></tr>
												<tr><td>student_timezone_offset</td>
													<td><input type='text' class='formInputText' name='inp_student_timezone_offset' length='20' 
													maxlength='20' value='$student_timezone_offset'></td></tr>
												<tr><td>student_youth</td>
													<td><input type='text' class='formInputText' name='inp_student_youth' length='3' 
													maxlength='3' value='$student_youth'></td></tr>
												<tr><td>student_age</td>
													<td><input type='text' class='formInputText' name='inp_student_age' length='3' 
													maxlength='3' value='$student_age'></td></tr>
												<tr><td>student_student_parent</td>
													<td><input type='text' class='formInputText' name='inp_student_student_parent' length='50' 
													maxlength='50' value='$student_student_parent'></td></tr>
												<tr><td>student_student_parent_email</td>
													<td><input type='text' class='formInputText' name='inp_student_student_parent_email' length='50' 
													maxlength='50' value='$student_student_parent_email'></td></tr>
												<tr><td>student_level</td>
													<td><input type='text' class='formInputText' name='inp_student_level' length='15' 
													maxlength='15' value='$student_level'></td></tr>
												<tr><td>student_waiting_list</td>
													<td><input type='text' class='formInputText' name='inp_student_waiting_list' length='5' 
													maxlength='5' value='$student_waiting_list'></td></tr>
												<tr><td>student_request_date</td>
													<td><input type='text' class='formInputText' name='inp_student_request_date' length='20' 
													maxlength='20' value='$student_request_date'></td></tr>
												<tr><td>student_semester</td>
													<td><input type='text' class='formInputText' name='inp_student_semester' length='15' 
													maxlength='15' value='$student_semester'></td></tr>
												<tr><td style='vertical-align:top;'>student_notes</td>
													<td><textarea class='formInputText' name='inp_student_notes' rows='5' cols='50'>$student_notes</textarea></td></tr>
												<tr><td>student_welcome_date</td>
													<td><input type='text' class='formInputText' name='inp_student_welcome_date' length='20' 
													maxlength='20' value='$student_welcome_date'></td></tr>
												<tr><td>student_email_sent_date</td>
													<td><input type='text' class='formInputText' name='inp_student_email_sent_date' length='20' 
													maxlength='20' value='$student_email_sent_date'></td></tr>
												<tr><td>student_email_number</td>
													<td><input type='text' class='formInputText' name='inp_student_email_number' length='20' 
													maxlength='20' value='$student_email_number'></td></tr>
												<tr><td>student_response</td>
													<td><input type='text' class='formInputText' name='inp_student_response' length='1' 
													maxlength='1' value='$student_response'></td></tr>
												<tr><td>student_response_date</td>
													<td><input type='text' class='formInputText' name='inp_student_response_date' length='20' 
													maxlength='20' value='$student_response_date'></td></tr>
												<tr><td>student_abandoned</td>
													<td><input type='text' class='formInputText' name='inp_student_abandoned' length='1' 
													maxlength='1' value='$student_abandoned'></td></tr>
												<tr><td>student_student_status</td>
													<td><input type='text' class='formInputText' name='inp_student_student_status' length='1' 
													maxlength='1' value='$student_student_status'></td></tr>
												<tr><td style='vertical-align:top;'>student_action_log</td>
													<td><textarea class='formInputText' name='inp_student_action_log' rows='5' cols='50'>$student_action_log</textarea></td></tr>
												<tr><td>student_pre_assigned_advisor</td>
													<td><input type='text' class='formInputText' name='inp_student_pre_assigned_advisor' length='15' 
													maxlength='15' value='$student_pre_assigned_advisor'></td></tr>
												<tr><td>student_selected_date</td>
													<td><input type='text' class='formInputText' name='inp_student_selected_date' length='20' 
													maxlength='20' value='$student_selected_date'></td></tr>
												<tr><td>student_no_catalog</td>
													<td><input type='text' class='formInputText' name='inp_student_no_catalog' length='5' 
													maxlength='5' value='$student_no_catalog'></td></tr>
												<tr><td>student_hold_override</td>
													<td><input type='text' class='formInputText' name='inp_student_hold_override' length='1' 
													maxlength='1' value='$student_hold_override'></td></tr>
												<tr><td>student_messaging</td>
													<td><input type='text' class='formInputText' name='inp_student_messaging' length='3' 
													maxlength='3' value='$student_messaging'></td></tr>
												<tr><td>student_assigned_advisor</td>
													<td><input type='text' class='formInputText' name='inp_student_assigned_advisor' length='15' 
													maxlength='15' value='$student_assigned_advisor'></td></tr>
												<tr><td>student_advisor_select_date</td>
													<td><input type='text' class='formInputText' name='inp_student_advisor_select_date' length='20' 
													maxlength='20' value='$student_advisor_select_date'></td></tr>
												<tr><td>student_advisor_class_timezone</td>
													<td><input type='text' class='formInputText' name='inp_student_advisor_class_timezone' length='20' 
													maxlength='20' value='$student_advisor_class_timezone'></td></tr>
												<tr><td>student_advisor_class_timezone</td>
													<td><input type='text' class='formInputText' name='inp_student_advisor_class_timezone' length='10' 
													maxlength='10' value='$student_advisor_class_timezone'></td></tr>
												<tr><td>student_hold_reason_code</td>
													<td><input type='text' class='formInputText' name='inp_student_hold_reason_code' length='1' 
													maxlength='1' value='$student_hold_reason_code'></td></tr>
												<tr><td>student_class_priority</td>
													<td><input type='text' class='formInputText' name='inp_student_class_priority' length='20' 
													maxlength='20' value='$student_class_priority'></td></tr>
												<tr><td>student_class_priority</td>
													<td><input type='text' class='formInputText' name='inp_student_class_priority' length='10' 
													maxlength='10' value='$student_class_priority'></td></tr>
												<tr><td>student_assigned_advisor_class</td>
													<td><input type='text' class='formInputText' name='inp_student_assigned_advisor_class' length='1' 
													maxlength='1' value='$student_assigned_advisor_class'></td></tr>
												<tr><td>student_promotable</td>
													<td><input type='text' class='formInputText' name='inp_student_promotable' length='1' 
													maxlength='1' value='$student_promotable'></td></tr>
												<tr><td style='vertical-align:top;'>student_excluded_advisor</td>
													<td><textarea class='formInputText' name='inp_student_excluded_advisor' rows='5' cols='50'>$student_excluded_advisor</textarea></td></tr>
												<tr><td>student_student_survey_completion_date</td>
													<td><input type='text' class='formInputText' name='inp_student_student_survey_completion_date' length='20' 
													maxlength='20' value='$student_student_survey_completion_date'></td></tr>
												<tr><td>student_available_class_days</td>
													<td><input type='text' class='formInputText' name='inp_student_available_class_days' length='100' 
													maxlength='100' value='$student_available_class_days'></td></tr>
												<tr><td>student_intervention_required</td>
													<td><input type='text' class='formInputText' name='inp_student_intervention_required' length='1' 
													maxlength='1' value='$student_intervention_required'></td></tr>
												<tr><td>student_copy_control</td>
													<td><input type='text' class='formInputText' name='inp_student_copy_control' length='20' 
													maxlength='20' value='$student_copy_control'></td></tr>
												<tr><td>student_first_class_choice</td>
													<td><input type='text' class='formInputText' name='inp_student_first_class_choice' length='50' 
													maxlength='50' value='$student_first_class_choice'></td></tr>
												<tr><td>student_second_class_choice</td>
													<td><input type='text' class='formInputText' name='inp_student_second_class_choice' length='50' 
													maxlength='50' value='$student_second_class_choice'></td></tr>
												<tr><td>student_third_class_choice</td>
													<td><input type='text' class='formInputText' name='inp_student_third_class_choice' length='50' 
													maxlength='50' value='$student_third_class_choice'></td></tr>
												<tr><td>student_first_class_choice_utc</td>
													<td><input type='text' class='formInputText' name='inp_student_first_class_choice_utc' length='50' 
													maxlength='50' value='$student_first_class_choice_utc'></td></tr>
												<tr><td>student_second_class_choice_utc</td>
													<td><input type='text' class='formInputText' name='inp_student_second_class_choice_utc' length='50' 
													maxlength='50' value='$student_second_class_choice_utc'></td></tr>
												<tr><td>student_third_class_choice_utc</td>
													<td><input type='text' class='formInputText' name='inp_student_third_class_choice_utc' length='50' 
													maxlength='50' value='$student_third_class_choice_utc'></td></tr>
												<tr><td>student_catalog_options</td>
													<td><input type='text' class='formInputText' name='inp_student_catalog_options' length='100' 
													maxlength='100' value='$student_catalog_options'></td></tr>
												<tr><td>student_flexible</td>
													<td><input type='text' class='formInputText' name='inp_student_flexible' length='3' 
													maxlength='3' value='$student_flexible'></td></tr>
												<tr><td>student_date_created</td>
													<td><input type='text' class='formInputText' name='inp_student_date_created' length='20' 
													maxlength='20' value='$student_date_created'></td></tr>
												<tr><td>student_date_updated</td>
													<td><input type='text' class='formInputText' name='inp_student_date_updated' length='20' 
													maxlength='20' value='$student_date_updated'></td></tr>
												<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit Updates' /></td></tr>
												</table></form>
												<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student";
				}
			} else {
				if ($doDebug) {
					echo "fatal error: no record found for id $inp_student_id<br />";
				}
			}
		}
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass4 with inp_student_id: $inp_student_id and inp_callsign: $inp_callsign<br />";
		}
		// get the record
		$sql			= "select * from $studentTableName 
							where student_id = $inp_student_id";
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
					$student_student_id 					= $studentRow -> student_id;
					$student_call_sign 						= $studentRow -> call_sign;
					$student_time_zone 						= $studentRow -> time_zone;
					$student_timezone_offset 				= $studentRow -> timezone_offset;
					$student_youth 							= $studentRow -> youth;
					$student_age 							= $studentRow -> age;
					$student_student_parent 				= $studentRow -> student_parent;
					$student_student_parent_email 			= $studentRow -> student_parent_email;
					$student_level 							= $studentRow -> level;
					$student_waiting_list 					= $studentRow -> waiting_list;
					$student_request_date 					= $studentRow -> request_date;
					$student_semester 						= $studentRow -> semester;
					$student_notes 							= $studentRow -> notes;
					$student_welcome_date 					= $studentRow -> welcome_date;
					$student_email_sent_date 				= $studentRow -> email_sent_date;
					$student_email_number 					= $studentRow -> email_number;
					$student_response 						= $studentRow -> response;
					$student_response_date 					= $studentRow -> response_date;
					$student_abandoned 						= $studentRow -> abandoned;
					$student_student_status 				= $studentRow -> student_status;
					$student_action_log 					= $studentRow -> action_log;
					$student_pre_assigned_advisor 			= $studentRow -> pre_assigned_advisor;
					$student_selected_date 					= $studentRow -> selected_date;
					$student_no_catalog 					= $studentRow -> no_catalog;
					$student_hold_override 					= $studentRow -> hold_override;
					$student_messaging 						= $studentRow -> messaging;
					$student_assigned_advisor 				= $studentRow -> assigned_advisor;
					$student_advisor_select_date 			= $studentRow -> advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow -> advisor_class_timezone;
					$student_hold_reason_code 				= $studentRow -> hold_reason_code;
					$student_class_priority 				= $studentRow -> class_priority;
					$student_assigned_advisor_class 		= $studentRow -> assigned_advisor_class;
					$student_promotable 					= $studentRow -> promotable;
					$student_excluded_advisor 				= $studentRow -> excluded_advisor;
					$student_student_survey_completion_date = $studentRow -> student_survey_completion_date;
					$student_available_class_days 			= $studentRow -> available_class_days;
					$student_intervention_required 			= $studentRow -> intervention_required;
					$student_copy_control 					= $studentRow -> copy_control;
					$student_first_class_choice 			= $studentRow -> first_class_choice;
					$student_second_class_choice 			= $studentRow -> second_class_choice;
					$student_third_class_choice 			= $studentRow -> third_class_choice;
					$student_first_class_choice_utc 		= $studentRow -> first_class_choice_utc;
					$student_second_class_choice_utc 		= $studentRow -> second_class_choice_utc;
					$student_third_class_choice_utc 		= $studentRow -> third_class_choice_utc;
					$student_catalog_options 				= $studentRow -> catalog_options;
					$student_flexible 						= $studentRow -> flexible;
					$student_date_created 					= $studentRow -> date_created;
					$student_date_updated 					= $studentRow -> date_updated;

					$content		.= "<h3>Display and Update $student_call_sign Signup Information</h3>
										<h4>Results of the Update</h4>";

					$updateParams	= array();
					$updateFormat	= array();
					$thisDate		= date("Y-m-d H:i:s");
					$updateLog	= "/ $thisDate $userName did the following updates: ";
					if ($inp_student_time_zone != $student_time_zone) {
						$student_time_zone = $inp_student_time_zone;
						$updateParams['time_zone']	= $inp_student_time_zone;
						$updateFormat[]	= "%s";
						$content	.= "student_time_zone updated to $inp_student_time_zone<br />";
						$updateLog	.= " /student_time_zone updated to $inp_student_time_zone";
					}
					if ($inp_student_timezone_offset != $student_timezone_offset) {
						$student_timezone_offset = $inp_student_timezone_offset;
						$updateParams['timezone_offset']	= $inp_student_timezone_offset;
						$updateFormat[]	= "%f";
						$content	.= "student_timezone_offset updated to $inp_student_timezone_offset<br />";
						$updateLog	.= " /student_timezone_offset updated to $inp_student_timezone_offset";
					}
					if ($inp_student_youth != $student_youth) {
						$student_youth = $inp_student_youth;
						$updateParams['youth']	= $inp_student_youth;
						$updateFormat[]	= "%s";
						$content	.= "student_youth updated to $inp_student_youth<br />";
						$updateLog	.= " /student_youth updated to $inp_student_youth";
					}
					if ($inp_student_age != $student_age) {
						$student_age = $inp_student_age;
						$updateParams['age']	= $inp_student_age;
						$updateFormat[]	= "%s";
						$content	.= "student_age updated to $inp_student_age<br />";
						$updateLog	.= " /student_age updated to $inp_student_age";
					}
					if ($inp_student_student_parent != $student_student_parent) {
						$student_student_parent = $inp_student_student_parent;
						$updateParams['student_parent']	= $inp_student_student_parent;
						$updateFormat[]	= "%s";
						$content	.= "student_student_parent updated to $inp_student_student_parent<br />";
						$updateLog	.= " /student_student_parent updated to $inp_student_student_parent";
					}
					if ($inp_student_student_parent_email != $student_student_parent_email) {
						$student_student_parent_email = $inp_student_student_parent_email;
						$updateParams['student_parent_email']	= $inp_student_student_parent_email;
						$updateFormat[]	= "%s";
						$content	.= "student_student_parent_email updated to $inp_student_student_parent_email<br />";
						$updateLog	.= " /student_student_parent_email updated to $inp_student_student_parent_email";
					}
					if ($inp_student_level != $student_level) {
						$student_level = $inp_student_level;
						$updateParams['level']	= $inp_student_level;
						$updateFormat[]	= "%s";
						$content	.= "student_level updated to $inp_student_level<br />";
						$updateLog	.= " /student_level updated to $inp_student_level";
					}
					if ($inp_student_waiting_list != $student_waiting_list) {
						$student_waiting_list = $inp_student_waiting_list;
						$updateParams['waiting_list']	= $inp_student_waiting_list;
						$updateFormat[]	= "%s";
						$content	.= "student_waiting_list updated to $inp_student_waiting_list<br />";
						$updateLog	.= " /student_waiting_list updated to $inp_student_waiting_list";
					}
					if ($inp_student_request_date != $student_request_date) {
						$student_request_date = $inp_student_request_date;
						$updateParams['request_date']	= $inp_student_request_date;
						$updateFormat[]	= "%s";
						$content	.= "student_request_date updated to $inp_student_request_date<br />";
						$updateLog	.= " /student_request_date updated to $inp_student_request_date";
					}
					if ($inp_student_semester != $student_semester) {
						$student_semester = $inp_student_semester;
						$updateParams['semester']	= $inp_student_semester;
						$updateFormat[]	= "%s";
						$content	.= "student_semester updated to $inp_student_semester<br />";
						$updateLog	.= " /student_semester updated to $inp_student_semester";
					}
					if ($inp_student_notes != $student_notes) {
						$student_notes = $inp_student_notes;
						$updateParams['notes']	= $inp_student_notes;
						$updateFormat[]	= "%s";
						$content	.= "student_notes updated to $inp_student_notes<br />";
						$updateLog	.= " /student_notes updated to $inp_student_notes";
					}
					if ($inp_student_welcome_date != $student_welcome_date) {
						$student_welcome_date = $inp_student_welcome_date;
						$updateParams['welcome_date']	= $inp_student_welcome_date;
						$updateFormat[]	= "%s";
						$content	.= "student_welcome_date updated to $inp_student_welcome_date<br />";
						$updateLog	.= " /student_welcome_date updated to $inp_student_welcome_date";
					}
					if ($inp_student_email_sent_date != $student_email_sent_date) {
						$student_email_sent_date = $inp_student_email_sent_date;
						$updateParams['email_sent_date']	= $inp_student_email_sent_date;
						$updateFormat[]	= "%s";
						$content	.= "student_email_sent_date updated to $inp_student_email_sent_date<br />";
						$updateLog	.= " /student_email_sent_date updated to $inp_student_email_sent_date";
					}
					if ($inp_student_email_number != $student_email_number) {
						$student_email_number = $inp_student_email_number;
						$updateParams['email_number']	= $inp_student_email_number;
						$updateFormat[]	= "%s";
						$content	.= "student_email_number updated to $inp_student_email_number<br />";
						$updateLog	.= " /student_email_number updated to $inp_student_email_number";
					}
					if ($inp_student_response != $student_response) {
						$student_response = $inp_student_response;
						$updateParams['response']	= $inp_student_response;
						$updateFormat[]	= "%s";
						$content	.= "student_response updated to $inp_student_response<br />";
						$updateLog	.= " /student_response updated to $inp_student_response";
					}
					if ($inp_student_response_date != $student_response_date) {
						$student_response_date = $inp_student_response_date;
						$updateParams['response_date']	= $inp_student_response_date;
						$updateFormat[]	= "%s";
						$content	.= "student_response_date updated to $inp_student_response_date<br />";
						$updateLog	.= " /student_response_date updated to $inp_student_response_date";
					}
					if ($inp_student_abandoned != $student_abandoned) {
						$student_abandoned = $inp_student_abandoned;
						$updateParams['abandoned']	= $inp_student_abandoned;
						$updateFormat[]	= "%s";
						$content	.= "student_abandoned updated to $inp_student_abandoned<br />";
						$updateLog	.= " /student_abandoned updated to $inp_student_abandoned";
					}
					if ($inp_student_student_status != $student_student_status) {
						$student_student_status = $inp_student_student_status;
						$updateParams['student_status']	= $inp_student_student_status;
						$updateFormat[]	= "%s";
						$content	.= "student_student_status updated to $inp_student_student_status<br />";
						$updateLog	.= " /student_student_status updated to $inp_student_student_status";
					}
					if ($inp_student_action_log != $student_action_log) {
						$student_action_log = $inp_student_action_log;
						$updateParams['action_log']	= $inp_student_action_log;
						$updateFormat[]	= "%s";
						$content	.= "student_action_log updated to $inp_student_action_log<br />";
//						$updateLog	.= " /student_action_log updated to $inp_student_action_log";
					}
					if ($inp_student_pre_assigned_advisor != $student_pre_assigned_advisor) {
						$student_pre_assigned_advisor = $inp_student_pre_assigned_advisor;
						$updateParams['pre_assigned_advisor']	= $inp_student_pre_assigned_advisor;
						$updateFormat[]	= "%s";
						$content	.= "student_pre_assigned_advisor updated to $inp_student_pre_assigned_advisor<br />";
						$updateLog	.= " /student_pre_assigned_advisor updated to $inp_student_pre_assigned_advisor";
					}
					if ($inp_student_selected_date != $student_selected_date) {
						$student_selected_date = $inp_student_selected_date;
						$updateParams['selected_date']	= $inp_student_selected_date;
						$updateFormat[]	= "%s";
						$content	.= "student_selected_date updated to $inp_student_selected_date<br />";
						$updateLog	.= " /student_selected_date updated to $inp_student_selected_date";
					}
					if ($inp_student_no_catalog != $student_no_catalog) {
						$student_no_catalog = $inp_student_no_catalog;
						$updateParams['no_catalog']	= $inp_student_no_catalog;
						$updateFormat[]	= "%s";
						$content	.= "student_no_catalog updated to $inp_student_no_catalog<br />";
						$updateLog	.= " /student_no_catalog updated to $inp_student_no_catalog";
					}
					if ($inp_student_hold_override != $student_hold_override) {
						$student_hold_override = $inp_student_hold_override;
						$updateParams['hold_override']	= $inp_student_hold_override;
						$updateFormat[]	= "%s";
						$content	.= "student_hold_override updated to $inp_student_hold_override<br />";
						$updateLog	.= " /student_hold_override updated to $inp_student_hold_override";
					}
					if ($inp_student_messaging != $student_messaging) {
						$student_messaging = $inp_student_messaging;
						$updateParams['messaging']	= $inp_student_messaging;
						$updateFormat[]	= "%s";
						$content	.= "student_messaging updated to $inp_student_messaging<br />";
						$updateLog	.= " /student_messaging updated to $inp_student_messaging";
					}
					if ($inp_student_assigned_advisor != $student_assigned_advisor) {
						$student_assigned_advisor = $inp_student_assigned_advisor;
						$updateParams['assigned_advisor']	= $inp_student_assigned_advisor;
						$updateFormat[]	= "%s";
						$content	.= "student_assigned_advisor updated to $inp_student_assigned_advisor<br />";
						$updateLog	.= " /student_assigned_advisor updated to $inp_student_assigned_advisor";
					}
					if ($inp_student_advisor_select_date != $student_advisor_select_date) {
						$student_advisor_select_date = $inp_student_advisor_select_date;
						$updateParams['advisor_select_date']	= $inp_student_advisor_select_date;
						$updateFormat[]	= "%s";
						$content	.= "student_advisor_select_date updated to $inp_student_advisor_select_date<br />";
						$updateLog	.= " /student_advisor_select_date updated to $inp_student_advisor_select_date";
					}
					if ($inp_student_advisor_class_timezone != $student_advisor_class_timezone) {
						$student_advisor_class_timezone = $inp_student_advisor_class_timezone;
						$updateParams['advisor_class_timezone']	= $inp_student_advisor_class_timezone;
						$updateFormat[]	= "%s";
						$content	.= "student_advisor_class_timezone updated to $inp_student_advisor_class_timezone<br />";
						$updateLog	.= " /student_advisor_class_timezone updated to $inp_student_advisor_class_timezone";
					}
					if ($inp_student_hold_reason_code != $student_hold_reason_code) {
						$student_hold_reason_code = $inp_student_hold_reason_code;
						$updateParams['hold_reason_code']	= $inp_student_hold_reason_code;
						$updateFormat[]	= "%s";
						$content	.= "student_hold_reason_code updated to $inp_student_hold_reason_code<br />";
						$updateLog	.= " /student_hold_reason_code updated to $inp_student_hold_reason_code";
					}
					if ($inp_student_class_priority != $student_class_priority) {
						$student_class_priority = $inp_student_class_priority;
						$updateParams['class_priority']	= $inp_student_class_priority;
						$updateFormat[]	= "%s";
						$content	.= "student_class_priority updated to $inp_student_class_priority<br />";
						$updateLog	.= " /student_class_priority updated to $inp_student_class_priority";
					}
					if ($inp_student_assigned_advisor_class != $student_assigned_advisor_class) {
						$student_assigned_advisor_class = $inp_student_assigned_advisor_class;
						$updateParams['assigned_advisor_class']	= $inp_student_assigned_advisor_class;
						$updateFormat[]	= "%s";
						$content	.= "student_assigned_advisor_class updated to $inp_student_assigned_advisor_class<br />";
						$updateLog	.= " /student_assigned_advisor_class updated to $inp_student_assigned_advisor_class";
					}
					if ($inp_student_promotable != $student_promotable) {
						$student_promotable = $inp_student_promotable;
						$updateParams['promotable']	= $inp_student_promotable;
						$updateFormat[]	= "%s";
						$content	.= "student_promotable updated to $inp_student_promotable<br />";
						$updateLog	.= " /student_promotable updated to $inp_student_promotable";
					}
					if ($inp_student_excluded_advisor != $student_excluded_advisor) {
						$student_excluded_advisor = $inp_student_excluded_advisor;
						$updateParams['excluded_advisor']	= $inp_student_excluded_advisor;
						$updateFormat[]	= "%s";
						$content	.= "student_excluded_advisor updated to $inp_student_excluded_advisor<br />";
						$updateLog	.= " /student_excluded_advisor updated to $inp_student_excluded_advisor";
					}
					if ($inp_student_student_survey_completion_date != $student_student_survey_completion_date) {
						$student_student_survey_completion_date = $inp_student_student_survey_completion_date;
						$updateParams['student_survey_completion_date']	= $inp_student_student_survey_completion_date;
						$updateFormat[]	= "%s";
						$content	.= "student_student_survey_completion_date updated to $inp_student_student_survey_completion_date<br />";
						$updateLog	.= " /student_student_survey_completion_date updated to $inp_student_student_survey_completion_date";
					}
					if ($inp_student_available_class_days != $student_available_class_days) {
						$student_available_class_days = $inp_student_available_class_days;
						$updateParams['available_class_days']	= $inp_student_available_class_days;
						$updateFormat[]	= "%s";
						$content	.= "student_available_class_days updated to $inp_student_available_class_days<br />";
						$updateLog	.= " /student_available_class_days updated to $inp_student_available_class_days";
					}
					if ($inp_student_intervention_required != $student_intervention_required) {
						$student_intervention_required = $inp_student_intervention_required;
						$updateParams['intervention_required']	= $inp_student_intervention_required;
						$updateFormat[]	= "%s";
						$content	.= "student_intervention_required updated to $inp_student_intervention_required<br />";
						$updateLog	.= " /student_intervention_required updated to $inp_student_intervention_required";
					}
					if ($inp_student_copy_control != $student_copy_control) {
						$student_copy_control = $inp_student_copy_control;
						$updateParams['copy_control']	= $inp_student_copy_control;
						$updateFormat[]	= "%s";
						$content	.= "student_copy_control updated to $inp_student_copy_control<br />";
						$updateLog	.= " /student_copy_control updated to $inp_student_copy_control";
					}
					if ($inp_student_first_class_choice != $student_first_class_choice) {
						$student_first_class_choice = $inp_student_first_class_choice;
						$updateParams['first_class_choice']	= $inp_student_first_class_choice;
						$updateFormat[]	= "%s";
						$content	.= "student_first_class_choice updated to $inp_student_first_class_choice<br />";
						$updateLog	.= " /student_first_class_choice updated to $inp_student_first_class_choice";
					}
					if ($inp_student_second_class_choice != $student_second_class_choice) {
						$student_second_class_choice = $inp_student_second_class_choice;
						$updateParams['second_class_choice']	= $inp_student_second_class_choice;
						$updateFormat[]	= "%s";
						$content	.= "student_second_class_choice updated to $inp_student_second_class_choice<br />";
						$updateLog	.= " /student_second_class_choice updated to $inp_student_second_class_choice";
					}
					if ($inp_student_third_class_choice != $student_third_class_choice) {
						$student_third_class_choice = $inp_student_third_class_choice;
						$updateParams['third_class_choice']	= $inp_student_third_class_choice;
						$updateFormat[]	= "%s";
						$content	.= "student_third_class_choice updated to $inp_student_third_class_choice<br />";
						$updateLog	.= " /student_third_class_choice updated to $inp_student_third_class_choice";
					}
					if ($inp_student_first_class_choice_utc != $student_first_class_choice_utc) {
						$student_first_class_choice_utc = $inp_student_first_class_choice_utc;
						$updateParams['first_class_choice_utc']	= $inp_student_first_class_choice_utc;
						$updateFormat[]	= "%s";
						$content	.= "student_first_class_choice_utc updated to $inp_student_first_class_choice_utc<br />";
						$updateLog	.= " /student_first_class_choice_utc updated to $inp_student_first_class_choice_utc";
					}
					if ($inp_student_second_class_choice_utc != $student_second_class_choice_utc) {
						$student_second_class_choice_utc = $inp_student_second_class_choice_utc;
						$updateParams['second_class_choice_utc']	= $inp_student_second_class_choice_utc;
						$updateFormat[]	= "%s";
						$content	.= "student_second_class_choice_utc updated to $inp_student_second_class_choice_utc<br />";
						$updateLog	.= " /student_second_class_choice_utc updated to $inp_student_second_class_choice_utc";
					}
					if ($inp_student_third_class_choice_utc != $student_third_class_choice_utc) {
						$student_third_class_choice_utc = $inp_student_third_class_choice_utc;
						$updateParams['third_class_choice_utc']	= $inp_student_third_class_choice_utc;
						$updateFormat[]	= "%s";
						$content	.= "student_third_class_choice_utc updated to $inp_student_third_class_choice_utc<br />";
						$updateLog	.= " /student_third_class_choice_utc updated to $inp_student_third_class_choice_utc";
					}
					if ($inp_student_catalog_options != $student_catalog_options) {
						$student_catalog_options = $inp_student_catalog_options;
						$updateParams['catalog_options']	= $inp_student_catalog_options;
						$updateFormat[]	= "%s";
						$content	.= "student_catalog_options updated to $inp_student_catalog_options<br />";
						$updateLog	.= " /student_catalog_options updated to $inp_student_catalog_options";
					}
					if ($inp_student_flexible != $student_flexible) {
						$student_flexible = $inp_student_flexible;
						$updateParams['flexible']	= $inp_student_flexible;
						$updateFormat[]	= "%s";
						$content	.= "student_flexible updated to $inp_student_flexible<br />";
						$updateLog	.= " /student_flexible updated to $inp_student_flexible";
					}
					if ($inp_student_date_created != $student_date_created) {
						$student_date_created = $inp_student_date_created;
						$updateParams['date_created']	= $inp_student_date_created;
						$updateFormat[]	= "%d";
						$content	.= "student_date_created updated to $inp_student_date_created<br />";
						$updateLog	.= " /student_date_created updated to $inp_student_date_created";
					}
					if ($inp_student_date_updated != $student_date_updated) {
						$student_date_updated = $inp_student_date_updated;
						$updateParams['date_updated']	= $inp_student_date_updated;
						$updateFormat[]	= "%d";
						$content	.= "student_date_updated updated to $inp_student_date_updated<br />";
						$updateLog	.= " /student_date_updated updated to $inp_student_date_updated";
					}					
					// ready to write to the database
					if ($doDebug) {
						echo "<br />updateParams:<br /><pre>";
						print_r($updateParams);
						echo "</pre><br />";
					}
					if (count($updateParams) > 0) {		// only update if there were changes
						// setup the action_log
						$student_action_log			.= $updateLog;
						$updateParams['action_log'] = $student_action_log;
						$updateFormat[]	= '%s';
						
						$updateResult		= $wpdb->update($studentTableName,
														$updateParams,
														array('student_id'=>$inp_student_id),
														$updateFormat,
														array("%d"));
						if ($updateResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {			
							if ($doDebug) {
								echo "update was successful<br />";
							}
						}
					} else {
						$content				.= "<p>No Updates Requested</p>";
						if ($doDebug) {
							echo "no updates requested<br />";
						}
					}
					// get the user_master info and format it
					if ($doDebug) {
						echo "getting the user_master data<br />";
					}
					$result			= "";
					$reason			= "";
					$callsign		= "";
					$first_name		= "";
					$last_name		= "";
					$email			= "";
					$phone			= "";
					$ph_code		= "";
					$city			= "";
					$state			= "";
					$zip_code		= "";
					$country_code	= "";
					$country		= "";
					$whatsapp		= "";
					$telegram		= "";
					$signal			= "";
					$messenger		= "";
					$languages		= "";
					$timezone_id	= "";
					$date_created	= "";
					$date_updated	= "";
			
					$dataArray			= array('callsign'=>$inp_callsign,
												'action'=>'get',
												'debugging'=> $doDebug,
												'testing'=> $testMode);
					$dataResult				= user_master_data($dataArray);
					foreach($dataResult as $thisField => $thisValue) {
						$$thisField		= $thisValue;
					}		
					if ($result == FALSE) {
						$content		.= "Failed to get the student master information. Reason: $reason";			
					} else {
						$myStr			= formatActionLog($user_action_log);
						$content		.= "<h4>Student Master Data</h4>
										<table style='width:900px;'>
										<tr><td><b>Callsign<br />$callsign</b></td>
											<td><b>Name</b><br />$last_name, $first_name</td>
											<td><b>Phone</b><br />+$ph_code $phone</td>
											<td><b>Email</b><br />$email</td></tr>
										<tr><td><b>City</b><br />$city</td>
											<td><b>State</b><br />$state</td>
											<td><b>Zip Code</b><br />$zip_code</td>
											<td><b>Country</b><br />$country</td></tr>
										<tr><td><b>WhatsApp</b><br />$whatsapp</td>
											<td><b>Telegram</b><br />$telegram</td>
											<td><b>Signal</b><br />$signal</td>
											<td><b>Messenger</b><br />$messenger</td></tr>
										<tr><td><b>Timezone ID</b><br />$timezone_id</td>
											<td><b>Languages</b><br />$languages</td>
											<td><b>Date Created</b><br />$date_created</td>
											<td><b>Date Updated</b><br />$date_updated</td></tr>
										<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>
										</table>
										<p>Click HERE to update the Student Master Data</p>";
					}
			
					// get the student signup info
					$sql			= "select * from $studentTableName 
										where student_id = $inp_student_id";
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
								$student_student_id 					= $studentRow -> student_id;
								$student_call_sign 						= $studentRow -> call_sign;
								$student_time_zone 						= $studentRow -> time_zone;
								$student_timezone_offset 				= $studentRow -> timezone_offset;
								$student_youth 							= $studentRow -> youth;
								$student_age 							= $studentRow -> age;
								$student_student_parent 				= $studentRow -> student_parent;
								$student_student_parent_email 			= $studentRow -> student_parent_email;
								$student_level 							= $studentRow -> level;
								$student_waiting_list 					= $studentRow -> waiting_list;
								$student_request_date 					= $studentRow -> request_date;
								$student_semester 						= $studentRow -> semester;
								$student_notes 							= $studentRow -> notes;
								$student_welcome_date 					= $studentRow -> welcome_date;
								$student_email_sent_date 				= $studentRow -> email_sent_date;
								$student_email_number 					= $studentRow -> email_number;
								$student_response 						= $studentRow -> response;
								$student_response_date 					= $studentRow -> response_date;
								$student_abandoned 						= $studentRow -> abandoned;
								$student_student_status 				= $studentRow -> student_status;
								$student_action_log 					= $studentRow -> action_log;
								$student_pre_assigned_advisor 			= $studentRow -> pre_assigned_advisor;
								$student_selected_date 					= $studentRow -> selected_date;
								$student_no_catalog 					= $studentRow -> no_catalog;
								$student_hold_override 					= $studentRow -> hold_override;
								$student_messaging 						= $studentRow -> messaging;
								$student_assigned_advisor 				= $studentRow -> assigned_advisor;
								$student_advisor_select_date 			= $studentRow -> advisor_select_date;
								$student_advisor_class_timezone 		= $studentRow -> advisor_class_timezone;
								$student_hold_reason_code 				= $studentRow -> hold_reason_code;
								$student_class_priority 				= $studentRow -> class_priority;
								$student_assigned_advisor_class 		= $studentRow -> assigned_advisor_class;
								$student_promotable 					= $studentRow -> promotable;
								$student_excluded_advisor 				= $studentRow -> excluded_advisor;
								$student_student_survey_completion_date = $studentRow -> student_survey_completion_date;
								$student_available_class_days 			= $studentRow -> available_class_days;
								$student_intervention_required 			= $studentRow -> intervention_required;
								$student_copy_control 					= $studentRow -> copy_control;
								$student_first_class_choice 			= $studentRow -> first_class_choice;
								$student_second_class_choice 			= $studentRow -> second_class_choice;
								$student_third_class_choice 			= $studentRow -> third_class_choice;
								$student_first_class_choice_utc 		= $studentRow -> first_class_choice_utc;
								$student_second_class_choice_utc 		= $studentRow -> second_class_choice_utc;
								$student_third_class_choice_utc 		= $studentRow -> third_class_choice_utc;
								$student_catalog_options 				= $studentRow -> catalog_options;
								$student_flexible 						= $studentRow -> flexible;
								$student_date_created 					= $studentRow -> date_created;
								$student_date_updated 					= $studentRow -> date_updated;
					
								$student_action_log						= formatActionLog($student_action_log);
								$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
								
								$updateLink			= "<a href='$theURL/?strpass=3&inp_callsign=$inp_callsign&inp_student_id=$student_student_id'>$student_student_id<a/>";
			
								$content			.= "<h4>Student Signup Created $student_date_created</h4>
														<table style='width:900px;'>
														<tr><td>Student Student Id<td>
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
															<td>$student_student_parent</td></tr>
														<tr><td>Student Student Parent Email<td>
															<td>$student_student_parent_email</td></tr>
														<tr><td>Student Level<td>
															<td>$student_level</td></tr>
														<tr><td>Student Waiting List<td>
															<td>$student_waiting_list</td></tr>
														<tr><td>Student Request Date<td>
															<td>$student_request_date</td></tr>
														<tr><td>Student Semester<td>
															<td>$student_semester</td></tr>
														<tr><td style='vertical-align:top;'>Student Notes<td>
															<td>$student_notes</td></tr>
														<tr><td>Student Welcome Date<td>
															<td>$student_welcome_date</td></tr>
														<tr><td>Student Email Sent Date<td>
															<td>$student_email_sent_date</td></tr>
														<tr><td>Student Email Number<td>
															<td>$student_email_number</td></tr>
														<tr><td>Student Response<td>
															<td>$student_response</td></tr>
														<tr><td>Student Response Date<td>
															<td>$student_response_date</td></tr>
														<tr><td>Student Abandoned<td>
															<td>$student_abandoned</td></tr>
														<tr><td>Student Student Status<td>
															<td>$student_student_status</td></tr>
														<tr><td style='vertical-align:top;'>Student Action Log<td>
															<td>$student_action_log</td></tr>
														<tr><td>Student Pre Assigned Advisor<td>
															<td>$student_pre_assigned_advisor</td></tr>
														<tr><td>Student Selected Date<td>
															<td>$student_selected_date</td></tr>
														<tr><td>Student No Catalog<td>
															<td>$student_no_catalog</td></tr>
														<tr><td>Student Hold Override<td>
															<td>$student_hold_override</td></tr>
														<tr><td>Student Messaging<td>
															<td>$student_messaging</td></tr>
														<tr><td>Student Assigned Advisor<td>
															<td>$student_assigned_advisor</td></tr>
														<tr><td>Student Advisor Select Date<td>
															<td>$student_advisor_select_date</td></tr>
														<tr><td>Student Advisor Class Timezone<td>
															<td>$student_advisor_class_timezone</td></tr>
														<tr><td>Student Hold Reason Code<td>
															<td>$student_hold_reason_code</td></tr>
														<tr><td>Student Class Priority<td>
															<td>$student_class_priority</td></tr>
														<tr><td>Student Assigned Advisor Class<td>
															<td>$student_assigned_advisor_class</td></tr>
														<tr><td>Student Promotable<td>
															<td>$student_promotable</td></tr>
														<tr><td>Student Excluded Advisor<td>
															<td>$student_excluded_advisor</td></tr>
														<tr><td>Student Student Survey Completion Date<td>
															<td>$student_student_survey_completion_date</td></tr>
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
														<tr><td>Student First Class Choice Utc<td>
															<td>$student_first_class_choice_utc</td></tr>
														<tr><td>Student Second Class Choice Utc<td>
															<td>$student_second_class_choice_utc</td></tr>
														<tr><td>Student Third Class Choice Utc<td>
															<td>$student_third_class_choice_utc</td></tr>
														<tr><td>Student Catalog Options<td>
															<td>$student_catalog_options</td></tr>
														<tr><td>Student Flexible<td>
															<td>$student_flexible</td></tr>
														<tr><td>Student Date Created<td>
															<td>$student_date_created</td></tr>
														<tr><td>Student Date Updated<td>
															<td>$student_date_updated</td></tr>
														</table>
														<p>Click <a href='$theURL/?strpass=3&inp_callsign=$inp_callsign&inp_student_id=$student_student_id'>HERE</a>
														to modify this signup record</p>
														<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student";
							}
						} else {
							$content		.= "<p>No signup record found for $inp_callsign</p>";
						}
					}
				}
			} else {
				$content			.= "FATAL Error: Unable to retrieve record being updated<br /";
				if ($doDebug) {
					echo "FATAL Error: Unable to retrieve record being updated<br /";
				}
			}
		}

	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 5 with inp_student_id: $inp_student_id<br />";
		}
		
		$content		.= "<h3>Display and Update Student Information</h3>
							<h4>Deleting record ID $inp_student_id</h4>";
							
		//// first move the record to the deleted table
		
		$sql		= "insert into $deletedStudentTableName 
						select * from $studentTableName 
						where ID = $inp_student_id";
		$myResult	= $wpdb->get_results($sql);
		if ($myResult === FALSE) {
			handledWPDBError($jobname,$doDebug);
			$content .= "<p>The deletion was not successful. Sys Admin has been notified</p>";
		} else {
			//// then, if the move was successful, delete the record
			$myResult	= $wpdb->delete($studentTableName,
									array('ID'=>$inp_student_id),
									array('%d'));
			if ($myResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$content	.= "<p>The deletion was not successful. Sys Admin has been notified</p>";
			}
		}
		$content		.= "<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student";
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
		$storeResult	= storeReportData_func("Current Student and Advisor Assignments",$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports pod as $storeResult";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('display_and_update_student_signup', 'display_and_update_student_signup_func');
