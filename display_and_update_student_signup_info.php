function display_and_update_student_signup_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 						= $context->validUser;

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$userName			= $context->userName;
	$currentTimestamp	= $context->currentTimestamp;
	$validTestmode		= $context->validTestmode;
	$siteURL			= $context->siteurl;
	$userEmail			= $context->userEmail;
	$userDisplayName	= $context->userDisplayName;
	$userRole			= $context->userRole;
	$languageArray		= $context->languageArray;
	$proximateSemester	= $context->proximateSemester;
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
//		error_reporting(E_ALL);	
		error_reporting(error_reporting() & ~E_DEPRECATED);
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
	$inp_depth					= "one";
	$updateMaster				= "$siteURL/cwa-display-and-update-user-master-information/";
	$inp_mode					= '';
	$inp_verbose				= '';
	$operatingMode				= 'Production';
	
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
								'N'=>'Advisor declined replacement student',
								'R'=>'Advisor has requested a replacement',
								'S'=>'Advisor has not verified the student',
								'U'=>'No available class for student',
								'V'=>'Advisor has requested a replacement due to schedule',
								'Y'=>'Student verified');
	$reasonCode			= array(''=>'Not specified',
								'X'=>'Do not assign to same advisor',
								'E'=>'Student not evaluated but signed up for next level',
								'H'=>'Student not promotable but signed up for next level',
								'Q'=>'Advisor quite; student signed up for next level',
								'W'=>'Student withdrew but signed up for next level',
								'B'=>'Student is a bad actor',
								'N'=>'Student moved to next semester');
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
			if ($str_key		== "submit") {
				$submitValue	= $str_value;
				$submitValue	= filter_var($submitValue,FILTER_UNSAFE_RAW);
				
//				echo "submitValue: $submitValue<br />";
				if (preg_match('/Update Student Record/',$submitValue)) {
					if ($doDebug) {
						echo "have submitValue: $submitValue<br />";
					}
					$inp_student_id	= str_replace('Update Student Record ','',$submitValue);
					$getStudent		= TRUE;
					if ($doDebug) {
						echo "extracted inp_student_id: $inp_student_id and set getStudent to TRUE<br />";
					}
				}
				
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
			if ($str_key		== "doDebug") {
				$deDebug	= strtoupper($str_value);
				$deDebug	= filter_var($deDebug,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "testMode") {
				$testMode	= strtoupper($str_value);
				$testMode	= filter_var($testMode,FILTER_UNSAFE_RAW);
				if ($testMode) {
					$inp_mode	= 'TESTMODE';
					$operatingMode = 'Testmode';
				} else {
					$operatingMode = 'Production';
				}
			}
			if ($str_key		== "request_info") {
				$request_info	= strtoupper($str_value);
				$request_info	= filter_var($request_info,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "request_type") {
				$request_type	= $str_value;
				$request_type	= filter_var($request_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== "inp_list") {
				$inp_list		= $str_value;
				$inp_list	= filter_var($inp_list,FILTER_UNSAFE_RAW);
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
			if ($str_key == "inp_student_class_language") {
				$inp_student_class_language = $str_value;
				$inp_student_class_language = filter_var($inp_student_class_language,FILTER_UNSAFE_RAW);
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
			if ($str_key == "inp_student_date_created") {
				$inp_student_date_created = $str_value;
				$inp_student_date_created = filter_var($inp_student_date_created,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_date_updated") {
				$inp_student_date_updated = $str_value;
				$inp_student_date_updated = filter_var($inp_student_date_updated,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "inp_depth") {
				$inp_depth = $str_value;
				$inp_depth = filter_var($inp_depth,FILTER_UNSAFE_RAW);
			}
			if ($str_key  == "submit") {
				$submit = $str_value;
				$submit = filter_var($submit,FILTER_UNSAFE_RAW);
				$submitArray = explode(" ",$submit);
				$theUser_ID = $submitArray[3];
				if ($doDebug) {
					echo "extracted theUser_ID of $theUser_ID from $submit<br />";
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
	
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
	} else {
		$content					.= "<p><b>Operating in Production Mode</b></p>";
		$extMode					= 'pd';
	}

	$user_dal = new CWA_User_Master_DAL();
	$student_dal = new CWA_Student_DAL();

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
	
		$content .= "<h3>$jobname</h3>
					<p>Please select the type of request and enter the value to be searched 
					in the Student table. Call sign can be either upper case or lower case. Last name must be 
					an exact match.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data''>
					<input type='hidden' name='strpass' value='2'>
					<table style='border-collapse:collapse;'>
					<tr><td style='width:150px;'>Request Type</td>
						<td><input class='formInputButton' type='radio' name='request_type' value='callsign' checked>Call Sign<br />
							<input class='formInputButton' type='radio' name='request_type' value='studentid'>Student ID<br />
							<input class='formInputButton' type='radio' name='request_type' value='surname'>Surname<br />
							<input class='formInputButton' type='radio' name='request_type' value='givenname'>Given Name<br />
							<input class='formInputButton' type='radio' name='request_type' value='email'>Email</td></tr>
					<tr><td>RequestInfo</td>
						<td><input class='formInputText' type='text'size='30' maxlength='30' name='request_info' autofocus ></td></tr>
					<tr><td>Data Depth</td>
						<td><input type='radio' class='formInputButton' name='inp_depth' value='one' checked>Display most current data only<br />
							<input type='radio' class='formInputButton' name='inp_depth' value='all'>Display all data</td></tr>
					$testModeOption
					<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
					</form>";
	

///// Pass 2 -- find and display the record


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass2 with request_type: $request_type and request_info: $request_info<br />";
		}

	
		if ($request_type == "callsign") {
			$request_info = strtoupper($request_info);
		}
		if ($doDebug) {
			echo "<br />at pass 2<br />Supplied input: <br />
					Request Type: $request_type; <br />
					Request Info: $request_info <br />";
		}

		$content				.= "<h3>$jobname</h3>
									<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student</p>";

// Set up the data request
		$goOn					= TRUE;
		$getMethod				= "";
		$getInfo				= "";
		$trueRecordCount		= 0;
		
		// get the user_master for display
		$admin = 'N';
		if ($userRole == 'Administator') {
			$admin = 'Y';
		}
		$user_master_data = get_user_master_for_display($request_type,$request_info,$admin,$operatingMode,$doDebug);
		if ($user_master_data === FALSE) {
			if ($doDebug) {
				echo "get_user_master_for_display using $getMethod, $getInfo returned FALSE<br />";
			}
			$content	.= "Attempting to retrieve the user_master data returned FALSE<br />";
		} else {
			$myInt = count($user_master_data);
			if ($myInt > 0) {
//				if ($doDebug) {
//					echo "user_master_data:<br /><pre>";
//					print_r($user_master_data);
//					echo "</pre><br />";
//				}
				if ($myInt > 1) {
					// have multiple options. Show each with the ability to select the desired user
					
					$content .= "<p><b>There are $myInt users matching the requested criteria.</b> 
							Select the user from the list below.</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2A'>
							<input type='hidden' name='request_type' value='$request_type'>
							<input type='hidden' name='request_info' value='$request_info'>
							<input type='hidden' name='inp_depth' value='$inp_depth'>
							<input type='hidden' name='inp_verbose' value='$inp_verbose'>
							<input type='hidden' name='inp_mode' value='$inp_mode'>";
								
					foreach($user_master_data as $key => $value) {
						$theDisplay = $user_master_data[$key]['display'];
						$content .= "<table style='border-collapse:collapse;'>
									 $theDisplay
									 <tr><td></td><td colspan='3'><input type='submit' class='formInputButton' name='submit' value='Use User Master $key' /></td></tr>
											</table>
									 </table>
									 <hr>";
								
								
					
				
					}
				} else {		// only one matching record
					foreach($user_master_data as $key => $value) {
						$theUser_call_sign = $user_master_data[$key]['data']['user_call_sign'];
						$theUser_ID = $key;
					}
					if ($doDebug) {
						echo "have only one matching record for $request_type $getInfo<br />
							Going to pass 2C<br />";
					}
					$strPass = "2C";
				}
			} else {
				$content .= "<p>No user_master record found for $request_type $getInfo<br />";
			}
		}
	}
	if ($strPass == '2A') {
		if ($doDebug) {
			echo "<br />at pass 2A<br />
					request_type: $request_type<br />
					request_info: $request_info<br />
					theUser_ID: $theUser_ID<br />";
		}
		// get the callsign associated with $theUser_ID
		$user_master_data = $user_dal->get_user_master_by_id($theUser_ID,$operatingMode);
		if ($user_master_data === FALSE) {
			$content .= "Attempting to get the user_call_sign from user_master ID $theUser_ID returned FALSE<br />";
		} else {
			foreach($user_master_data as $key => $value) {
				foreach($value as $thisField => $thisValue) {
					$$thisField = $thisValue;
				}
				$theUser_call_sign = $user_call_sign;
				$strPass = '2C';
				$content .= "<h3>$jobname</h3>
							<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student</p>";
			}		
		}
	}
	if ($strPass == '2C') {
		if ($doDebug) {
			echo "<br />at pass 2C<br />
					request_type: $request_type<br />
					request_info: $request_info<br />
					inp_depth: $inp_depth<br />
					theUser_ID: $theUser_ID<br />
					theUser_call_sign: $theUser_call_sign";
		}
		// get the user_master data and display
		$admin = 'N';
		if ($userRole == 'Administrator') {
			$admin = 'Y';
		}
		$user_master_data =get_user_master_for_display('id',$theUser_ID,$admin,$operatingMode,$doDebug);
		if ($user_master_data === FALSE) {
			$content .= "Attempting to retrieve user_master for id $theUser_ID returend FALSE<br />";
		} else {
			if (count($user_master_data) > 0) {
				foreach($user_master_data as $key => $value) {
					$data_to_display = $user_master_data[$key]['display'];
					$content	.= "<form method='post' action='$updateMaster' 
									name='updateMaster_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='inp_callsign' value='$theUser_call_sign'>
									<input type='hidden' name='request_type' value='callsign'>
									<input type='hidden' name='request_info' value='$theUser_call_sign'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<table style='width:900px;'>
									$data_to_display
									<tr><td></td><td colspan='3'><input type='submit' class='formInputButton' name='submit' value='Update User Master Record' /></td></tr>
									</table></form>";
					
				}
				// get the student signup info
				if ($doDebug) {
					echo "getting student info for $theUser_call_sign depth $inp_depth<br />";
				}
				$criteria = [
					'relation' => 'AND',
					'clauses' => [
						[ 
							'field'   => 'student_call_sign', 
							'value'   => $theUser_call_sign, 
							'compare' => '=' 
						]
					]
				];
				$student_data = $student_dal->get_student_by_order( $criteria, 'student_date_created', 'DESC', $operatingMode );
				if ($student_data === FALSE) {
					$content .= "Attempting to get student $theUser_call_sign returned FALSE<br />";
				} else {
					$myInt = count($student_data);
					if ($doDebug) {
						echo "have $myInt student records available to display<br />";
					}
					if ($myInt > 0) {
						$doOnce = TRUE;
						if ($inp_depth == 'all') {
							$doOnce = FALSE;
						}
						foreach($student_data as $key => $value) {
							foreach($value as $thisField => $thisValue) {
								$$thisField = $thisValue;
							}
							if ($doDebug) {
								echo "student_assigned_advisor:<br /><pre>";
								var_dump($student_assigned_advisor);
								echo "</pre><br />";							
							}		
							if ($doOnce) {
								$doOnce = FALSE;
							} else {
								$content .= "<hr />";
							}
							// check the student record for errors
							$studentDataErrors = cwa_validate_student_record($student_id );
							if (! empty($studentDataErrors)) {
								$content .= "<h4>Data Errors Found in $student_call_sign ($student_id) Record</h4>";
								foreach($studentDataErrors as $thisError) {
									$content .= "$thisError<br />";
								}
								$content .= "<br />";
							}
							// display the student data
							$student_action_log	= formatActionLog($student_action_log);
							
							if ($doDebug) {
								echo "Displaying $student_call_sign for $student_semester semester<br />";
							}
							if (array_key_exists($student_response,$responseCode)) {
								$responseStr	= $responseCode[$student_response];
							} else {
								$responseStr	= "(undefined)";
							}
							if (array_key_exists($student_waiting_list,$waitingCode)) {
								$waitingStr		= $waitingCode[$student_waiting_list];
							} else {
								$waitingStr		= "(undefined)";
							}
							if (array_key_exists($student_abandoned,$abandonedCode)) {
								$abandonedStr	= $abandonedCode[$student_abandoned];
							} else {
								$abandonedStr	= "(undefined)";
							}
							if (array_key_exists($student_status,$statusCode)) {
								$statusStr		= $statusCode[$student_status];
							} else {
								$statusStr		= "(undefined)";
							}
							if (array_key_exists($student_hold_reason_code,$reasonCode)) {
								$reasonStr		= $reasonCode[$student_hold_reason_code];
							} else {
								$reasonStr		= "(undefined)";
							}
							if (array_key_exists($student_promotable,$promotableCode)) {
								$promotableStr	= $promotableCode[$student_promotable];
							} else {
								$promotableStr	= "(undefined)";
							}
							if (array_key_exists($student_flexible,$flexibleCode)) {
								$flexibleStr	= $flexibleCode[$student_flexible];
							} else {
								$flexibleStr	= "(undefined)";
							}
							if (array_key_exists($student_no_catalog,$catalogCode)) {
								$catalogStr		= $catalogCode[$student_no_catalog];
							} else {
								$catalogStr		= "(undefined)";
							}
		
							$updateLink			= "<a href='$theURL/?strpass=3&inp_callsign=$inp_callsign&inp_student_id=$student_id&inp_verbose=$inp_verbose&inp_mode=$inp_mode'>$student_id<a/>";
							$preAssignedLink	= '';
							if ($student_pre_assigned_advisor != '') {
								$preAssignedLink	= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$student_pre_assigned_advisor&inp_depth=one&doDebug&testMode' target='_blank'>$student_pre_assigned_advisor</a>";
							}
							$assignedLink		= '';
							if ($student_assigned_advisor != '') {
								$assignedLink		= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$student_assigned_advisor&inp_depth=one&doDebug&testMode' target='_blank'>$student_assigned_advisor</a>";
							}
							$content			.= "<h4>Student Signup Created $student_date_created</h4>
													<form method='post' action='$theURL' 
													name='updateStudent_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='3'>
													<input type='hidden' name='inp_callsign' value='$student_call_sign'>
													<input type='hidden' name='request_type' value='callsign'>
													<input type='hidden' name='request_info' value='$student_call_sign'>
													<input type='hidden' name='inp_student_id' value=$student_id>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='inp_mode' value='$inp_mode'>
													<table style='width:900px;'>
													<tr><td style='width:300px;'>Student id</td>
														<td>$updateLink</td></tr>
													<tr><td>Student Call Sign</td>
														<td>$student_call_sign</td></tr>
													<tr><td>Student Time Zone</td>
														<td>$student_time_zone</td></tr>
													<tr><td>Student Timezone Offset</td>
														<td>$student_timezone_offset</td></tr>
													<tr><td>Student Youth</td>
														<td>$student_youth</td></tr>
													<tr><td>Student Age</td>
														<td>$student_age</td></tr>
													<tr><td>Student Student Parent</td>
														<td>$student_parent</td></tr>
													<tr><td>Student Student Parent Email</td>
														<td>$student_parent_email</td></tr>
													<tr><td>Student Level</td>
														<td>$student_level</td></tr>
													<tr><td>Student Class Language</td>
														<td>$student_class_language</td></tr>
													<tr><td>Student Waiting List</td>
														<td>$student_waiting_list $waitingStr</td></tr>
													<tr><td>Student Request Date</td>
														<td>$student_request_date</td></tr>
													<tr><td>Student Semester</td>
														<td>$student_semester</td></tr>
													<tr><td>Student Notes</td>
														<td style='vertical-align:top;'>$student_notes</td></tr>
													<tr><td>Student Welcome Date</td>
														<td>$student_welcome_date</td></tr>
													<tr><td>Student Email Sent Date</td>
														<td>$student_email_sent_date</td></tr>
													<tr><td>Student Email Number</td>
														<td>$student_email_number</td></tr>
													<tr><td>Student Response</td>
														<td>$student_response $responseStr</td></tr>
													<tr><td>Student Response Date</td>
														<td>$student_response_date</td></tr>
													<tr><td>Student Abandoned</td>
														<td>$student_abandoned $abandonedStr</td></tr>
													<tr><td>Student Student Status</td>
														<td>$student_status $statusStr</td></tr>
													<tr><td style='vertical-align:top;'>Student Action Log</td>
														<td>$student_action_log</td></tr>
													<tr><td>Student Pre Assigned Advisor</td>
														<td>$preAssignedLink</td></tr>
													<tr><td>Student Selected Date</td>
														<td>$student_selected_date</td></tr>
													<tr><td>Student No Catalog</td>
														<td>$student_no_catalog $catalogStr</td></tr>
													<tr><td>Student Hold Override</td>
														<td>$student_hold_override</td></tr>
													<tr><td>Student Assigned Advisor</td>
														<td>$assignedLink</td></tr>
													<tr><td>Student Advisor Select Date</td>
														<td>$student_advisor_select_date</td></tr>
													<tr><td>Student Advisor Class Timezone</td>
														<td>$student_advisor_class_timezone</td></tr>
													<tr><td>Student Hold Reason Code</td>
														<td>$student_hold_reason_code $reasonStr</td></tr>
													<tr><td>Student Class Priority</td>
														<td>$student_class_priority</td></tr>
													<tr><td>Student Assigned Advisor Class</td>
														<td>$student_assigned_advisor_class</td></tr>
													<tr><td>Student Promotable</td>
														<td>$student_promotable $promotableStr</td></tr>
													<tr><td>Student Excluded Advisor</td>
														<td>$student_excluded_advisor</td></tr>
													<tr><td>Student Student Survey Completion Date</td>
														<td>$student_survey_completion_date</td></tr>
													<tr><td>Student Available Class Days</td>
														<td>$student_available_class_days</td></tr>
													<tr><td>Student Intervention Required</td>
														<td>$student_intervention_required</td></tr>
													<tr><td>Student Copy Control</td>
														<td>$student_copy_control</td></tr>
													<tr><td>Student First Class Choice</td>
														<td>$student_first_class_choice</td></tr>
													<tr><td>Student Second Class Choice</td>
														<td>$student_second_class_choice</td></tr>
													<tr><td>Student Third Class Choice</td>
														<td>$student_third_class_choice</td></tr>
													<tr><td>Student First Class Choice Utc</td>
														<td>$student_first_class_choice_utc</td></tr>
													<tr><td>Student Second Class Choice Utc</td>
														<td>$student_second_class_choice_utc</td></tr>
													<tr><td>Student Third Class Choice Utc</td>
														<td>$student_third_class_choice_utc</td></tr>
													<tr><td>Student Catalog Options</td>
														<td>$student_catalog_options</td></tr>
													<tr><td>Student Flexible</td>
														<td>$student_flexible $flexibleStr</td></tr>
													<tr><td>Student Date Created</td>
														<td>$student_date_created</td></tr>
													<tr><td>Student Date Updated</td>
														<td>$student_date_updated</td></tr>
													<tr><td></td>
														<td><input type='submit' class='formInputButton' name='submit' value='Update Student Record $student_id' /></td></tr>
													</table>
													<p><a href='$theURL'>Look Up a Different Student</a>";
							if ($doOnce) {
								break;
							}
						}
					} else {
						$content		.= "<p>No signup record found for $inp_callsign</p>";
					}
				}
			}
		}
		
		
/////// pass 3 - display record to be modified 
		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 3 with inp_callsign: $inp_callsign and inp_student_id: $inp_student_id<br />";
		}
		
		// get the record to be updated
		$student_data = $student_dal->get_student_by_id( $inp_student_id, $operatingMode );
		if ($student_data ===  NULL) {
			$content .= "<p>Attempting to retrieve $inp_student_id from student_dal returned NULL</p>";
		} else {
			if ($doDebug) {
				echo "have student_data:<br /><pre>";
				print_r($student_data);
				echo "</pre><br />";
			}
			if ($doDebug) {
				echo "decoding $inp_student_id record<br />";
			}
			foreach($student_data as $key => $value) {
				$$key = $value;
			}
					
			$youthBlank				= 'checked';
			$youthNo				= '';
			$youthYes				= '';
			if ($student_youth == '') {
				$youthBlank			= 'checked';
			} elseif ($student_youth == 'No') {
				$youthNo			= 'checked';
			} elseif ($student_youth == 'Yes') {
				$youthYes			= 'checked';
			}
			
			$promBlank				= 'checked';
			$promPromoted			= '';
			$promNotPromoted		= '';
			$promWithdrew			= '';
			$promQuit				= '';
			if ($student_promotable == '') {
				$promBlank			= 'checked';
			} elseif ($student_promotable == 'P') {
				$promPromoted		= 'checked';
			} elseif ($student_promotable == 'N') {
				$promNotPromoted 	= 'checked';
			} elseif ($student_promotable == 'W') {
				$promWithdrew		= 'checked';
			} elseif ($student_promotable == 'Q') {
				$promQuit			= 'checked';
			}
			
			$responseBlank			= 'checked';
			$responseY				= '';
			$responseR				= '';
			if ($student_response == '') {
				$responseBlank		= 'checked';
			} elseif ($student_response == 'Y') {
				$responseY			= 'checked';
			} elseif ($student_response == 'R') {
				$responseR			= 'checked';
			}
			
			$statusBlank			= 'checked';
			$statusC				= '';
			$statusR				= '';
			$statusS				= '';
			$statusU				= '';
			$statusV				= '';
			$statusY				= '';
			if ($student_status == '') {
				$statusBlank		= 'checked';
			} elseif ($student_status == 'C') {
				$statusC			= 'checked';
			} elseif ($student_status == 'R') {
				$statusR			= 'checked';
			} elseif ($student_status == 'S') {
				$statusS			= 'checked';
			} elseif ($student_status == 'U') {
				$statusU			= 'checked';
			} elseif ($student_status == 'V') {
				$statusV			= 'checked';
			} elseif ($student_status == 'Y') {
				$statusY			= 'checked';
			}
			
			$reasonBlank			= 'checked';
			$reasonX				= '';
			$reasonE				= '';
			$reasonH				= '';
			$reasonQ				= '';
			$reasonW				= '';
			$reasonB				= '';
			$reasonN				= '';
			if ($student_hold_reason_code == '') {
				$reasonBlank		= 'checked';
			} elseif ($student_hold_reason_code == 'X') {
				$reasonX			= 'checked';
			} elseif ($student_hold_reason_code == 'E') {
				$reasonE			= 'checked';
			} elseif ($student_hold_reason_code == 'H') {
				$reasonH			= 'checked';
			} elseif ($student_hold_reason_code == 'Q') {
				$reasonQ			= 'checked';
			} elseif ($student_hold_reason_code == 'W') {
				$reasonW			= 'checked';
			} elseif ($student_hold_reason_code == 'B') {
				$reasonB			= 'checked';
			} elseif ($student_hold_reason_code == 'N') {
				$reasonN			= 'checked';
			}
			
			$abandonedBlank			= 'checked';
			$abandonedY				= 'checked';
			$abandonedN				= 'checked';
			if ($student_abandoned == '') {
				$abandonedBlank		= 'checked';
			} elseif ($student_abandoned == 'Y') {
				$abandonedY			= 'checked';
			} elseif ($student_abandoned == 'N') {
				$abandonedN			= 'checked';
			}
			
			$levelBeg				= '';
			$levelFun				= '';
			$levelInt				= '';
			$levelAdv				= '';
			if ($student_level == 'Beginner') {
				$levelBeg			= 'checked';
			} elseif ($student_level == 'Fundamental') {
				$levelFun			= 'checked';
			} elseif ($student_level == 'Intermediate') {
				$levelInt			= 'checked';
			} elseif ($student_level == 'Advanced') {
				$levelAdv			= 'checked';
			}
			
			$waitingBlank			= 'checked';
			$waitingY				= '';
			$waitingN				= '';
			if ($student_waiting_list == '') {
				$waitingBlank		= 'checked';
			} elseif ($student_waiting_list == 'Y') {
				$waitingY			= 'checked';
			} elseif ($student_waiting_list == 'N') {
				$waitingN			= 'checked';
			}
			
			$flexibleBlank			= 'checked';
			$flexibleY				= '';
			$flexibleN				= '';
			if ($student_flexible == '') {
				$flexibleBlank		= 'checked';
			} elseif ($student_flexible == 'Y') {
				$flexibleY			= 'checked';
			} elseif ($student_flexible == 'N') {
				$flexibleN			= 'checked';
			}

			$catalogBlank			= 'checked';
			$catalogY				= '';
			$catalogN				= '';
			if ($student_no_catalog == '') {
				$catalogBlank		= 'checked';
			} elseif ($student_no_catalog == 'Y') {
				$catalogY			= 'checked';
			} elseif ($student_no_catalog == 'N') {
				$catalogN			= 'checked';
			}

			//Build language selection
			$languageOptions			= '';
			$firstLanguage				= TRUE;
			$languageChecked			= '';
			foreach($languageArray as $thisLanguage) {
				if ($thisLanguage == $student_class_language) {
					$languageChecked		= 'checked';
				} else {
					$languageChecked		= '';
				}
				if ($firstLanguage) {
					$firstLanguage			= FALSE;
					$languageOptions		.= "<input type='radio' class='formInputButton' name='inp_student_class_language' value='$thisLanguage' $languageChecked >$thisLanguage\n";
				} else {
					$languageOptions		.= "<br /><input type='radio' class='formInputButton' name='inp_student_class_language' value='$thisLanguage' $languageChecked >$thisLanguage\n";
				}
			}
			$content				.= "<h3>Update $inp_callsign Signup Record for $student_call_sign in $student_semester Semester</h3>
										<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student</p>
										<form method='post' action='$theURL' 
										name='deletion_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='5'>
										<input type='hidden' name='inp_student_id' value='$inp_student_id'>
										<input type='hidden' name='inp_callsign' value='$student_call_sign'>
										<input type='hidden' name='inp_semester' value='$student_semester'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input class='formInputButton' type='submit' onclick=\"return confirm('Are you sure?');\"  value='Delete This Record' />
										</form><br />
										<form method='post' action='$theURL' 
										name='selection_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='4'>
										<input type='hidden' name='inp_student_id' value='$inp_student_id'>
										<input type='hidden' name='inp_callsign' value='$inp_callsign'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<table style='width:900px;'>
										<tr><td>student_id</td>
											<td>$student_id</td></tr>
										<tr><td>student_call_sign</td>
											<td>$student_call_sign</td></tr>
										<tr><td>student_time_zone</td>
											<td><input type='text' class='formInputText' name='inp_student_time_zone' length='50' 
											maxlength='50' value='$student_time_zone'></td></tr>
										<tr><td>student_timezone_offset</td>
											<td><input type='text' class='formInputText' name='inp_student_timezone_offset' length='20' 
											maxlength='20' value='$student_timezone_offset'></td></tr>
										<tr><td style='vertical-align:top;'>student_youth</td>
											<td><input type='radio' class='formInputButton' name='inp_student_youth' value='' $youthBlank>Blank: Not Specified<br />
												<input type='radio' class='formInputButton' name='inp_student_youth' value='No' $youthNo>No<br />
												<input type='radio' class='formInputButton' name='inp_student_youth' value='Yes' $youthYes>Yes</td></tr>
										<tr><td>student_age</td>
											<td><input type='text' class='formInputText' name='inp_student_age' length='3' 
											maxlength='3' value='$student_age'></td></tr>
										<tr><td>student_parent</td>
											<td><input type='text' class='formInputText' name='inp_student_parent' length='50' 
											maxlength='50' value='$student_parent'></td></tr>
										<tr><td>student_parent_email</td>
											<td><input type='text' class='formInputText' name='inp_student_parent_email' length='50' 
											maxlength='50' value='$student_parent_email'></td></tr>
										<tr><td style='vertical-align:top;'>student_level</td>
											<td><input type='radio' class='formInputButton' name='inp_student_level' value='Beginner' $levelBeg>Beginner<br />
												<input type='radio' class='formInputButton' name='inp_student_level' value='Fundamental' $levelFun>Fundamental<br />
												<input type='radio' class='formInputButton' name='inp_student_level' value='Intermediate' $levelInt>Intermediate<br />
												<input type='radio' class='formInputButton' name='inp_student_level' value='Advanced' $levelAdv>Advanced</td></tr>
										<tr><td style='vertical-align:top;'>Student_class_language</td>
											<td>$languageOptions</td>
										<tr><td style='vertical-align:top;'>student_waiting_list</td>
											<td><input type='radio' class='formInputButton' name='inp_student_waiting_list' value='' $waitingBlank>Blank: Not Specified (same as not on the waiting list)<br />
												<input type='radio' class='formInputButton' name='inp_student_waiting_list' value='N' $waitingN>N: Not on waiting list<br />
												<input type='radio' class='formInputButton' name='inp_student_waiting_list' value='Y' $waitingY>Y: On waiting list</td></tr>
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
										<tr><td style='vertical-align:top;'>student_response</td>
											<td ><input type='radio' class='formInputButton' name='inp_student_response' value='' $responseBlank>Blank: Not Specified<br />
												<input type='radio' class='formInputButton' name='inp_student_response' value='Y' $responseY>Y: Available<br />
												<input type='radio' class='formInputButton' name='inp_student_response' value='R' $responseR>R: Declined<br />
										<tr><td>student_response_date</td>
											<td><input type='text' class='formInputText' name='inp_student_response_date' length='20' 
											maxlength='20' value='$student_response_date'></td></tr>
										<tr><td style='vertical-align:top;'>student_abandoned</td>
											<td><input type='radio' class='formInputButton' name='inp_student_abandoned' value='' $abandonedBlank>Blank: Not Specified<br />
												<input type='radio' class='formInputButton' name='inp_student_abandoned' value='Y' $abandonedY>Y: Student abandoned registration process<br />
												<input type='radio' class='formInputButton' name='inp_student_abandoned' value='N' $abandonedN>N: Student completed registration</td></tr>
										<tr><td style='vertical-align:top;'>student_status</td>
											<td><input type='radio' class='formInputButton' name='inp_student_status' value='' $statusBlank>Blank: Not Specified<br />
												<input type='radio' class='formInputButton' name='inp_student_status' value='C' $statusC>C: Student has been replaced<br />
												<input type='radio' class='formInputButton' name='inp_student_status' value='R' $statusR>R: Advisor has requested a replacement<br />
												<input type='radio' class='formInputButton' name='inp_student_status' value='S' $statusS>S: Advisor has not verified the student<br />
												<input type='radio' class='formInputButton' name='inp_student_status' value='S' $statusU>U: No available class for student<br />
												<input type='radio' class='formInputButton' name='inp_student_status' value='V' $statusV>V: Advisor has requested a replacement due to schedule<br />
												<input type='radio' class='formInputButton' name='inp_student_status' value='Y' $statusY>Y: Student verified</td>
										<tr><td style='vertical-align:top;'>student_action_log</td>
											<td><textarea class='formInputText' name='inp_student_action_log' rows='5' cols='50'>$student_action_log</textarea></td></tr>
										<tr><td>student_pre_assigned_advisor</td>
											<td><input type='text' class='formInputText' name='inp_student_pre_assigned_advisor' length='15' 
											maxlength='15' value='$student_pre_assigned_advisor'></td></tr>
										<tr><td>student_selected_date</td>
											<td><input type='text' class='formInputText' name='inp_student_selected_date' length='20' 
											maxlength='20' value='$student_selected_date'></td></tr>
										<tr><td style='vertical-align:top;'>student_no_catalog</td>
											<td><input type='radio' class='formInputButton' name='inp_student_no_catalog' value='' $catalogBlank>Blank - Not Specified<br />
												<input type='radio' class='formInputButton' name='inp_student_no_catalog' value='N' $catalogN>N: Signed up with full catalog<br />
												<input type='radio' class='formInputButton' name='inp_student_no_catalog' value='Y' $catalogY>Y: Signed up before full catalog</td></tr>
										<tr><td>student_hold_override</td>
											<td><input type='text' class='formInputText' name='inp_student_hold_override' length='1' 
											maxlength='1' value='$student_hold_override'></td></tr>
										<tr><td>student_assigned_advisor</td>
											<td><input type='text' class='formInputText' name='inp_student_assigned_advisor' length='15' 
											maxlength='15' value='$student_assigned_advisor'></td></tr>
										<tr><td>student_advisor_select_date</td>
											<td><input type='text' class='formInputText' name='inp_student_advisor_select_date' length='20' 
											maxlength='20' value='$student_advisor_select_date'></td></tr>
										<tr><td>student_advisor_class_timezone</td>
											<td><input type='text' class='formInputText' name='inp_student_advisor_class_timezone' length='10' 
											maxlength='10' value='$student_advisor_class_timezone'></td></tr>
										<tr><td style='vertical-align:top;'>student_hold_reason_code</td>
											<td><input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='' $reasonBlank>Blank: Not specified<br />
												<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='X' $reasonX>X: Do not assign to same advisor<br />
												<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='E' $reasonE>Student not evaluated but signed up for next level<br />
												<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='H' $reasonH>H: Student not promotable but signed up for next level<br />
												<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='Q' $reasonQ>Q: Advisor quit; student signed up for next level <br />
												<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='W' $reasonW>W: Student withdrew but signed up for next level<br />
												<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='B' $reasonB>B: Student is a bad actor<br />
												<input type='radio' class='formInputButton' name='inp_student_hold_reason_code' value='B' $reasonB>N: Student moved to next semester</td></tr>
										<tr><td>student_class_priority</td>
											<td><input type='text' class='formInputText' name='inp_student_class_priority' length='20' 
											maxlength='5' value='$student_class_priority'></td></tr>
										<tr><td>student_assigned_advisor_class</td>
											<td><input type='text' class='formInputText' name='inp_student_assigned_advisor_class' length='1' 
											maxlength='1' value='$student_assigned_advisor_class'></td></tr>
										<tr><td style='vertical-align:top;'>student_promotable</td>
											<td><input type='radio' class='formInputButton' name='inp_student_promotable' value='' $promBlank>Blank: Not Specified<br />
												<input type='radio' class='formInputButton' name='inp_student_promotable' value='P' $promPromoted>P: Promotable<br />
												<input type='radio' class='formInputButton' name='inp_student_promotable' value='N' $promNotPromoted>N: Not Promotable<br />
												<input type='radio' class='formInputButton' name='inp_student_promotable' value='W' $promWithdrew>W: Withdrew<br />
												<input type='radio' class='formInputButton' name='inp_student_promotable' value='Q' $promQuit>Q: Advisor quit</td></tr>
										<tr><td style='vertical-align:top;'>student_excluded_advisor</td>
											<td><textarea class='formInputText' name='inp_student_excluded_advisor' rows='5' cols='50'>$student_excluded_advisor</textarea></td></tr>
										<tr><td>student_survey_completion_date</td>
											<td><input type='text' class='formInputText' name='inp_student_survey_completion_date' length='20' 
											maxlength='20' value='$student_survey_completion_date'></td></tr>
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
										<tr><td style='vertical-align:top;'>student_flexible</td>
											<td><input type='radio' class='formInputButton' name='inp_student_flexible' value='' $flexibleBlank>Blank: Not specified<br />
												<input type='radio' class='formInputButton' name='inp_student_flexible' value='Y' $flexibleY>Y: Schedule is flexible<br />
												<input type='radio' class='formInputButton' name='inp_student_flexible' value='N' $flexibleN>N: Schedule is not flexible</td></tr>
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

/////// pass 4 - update the record and display it

	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass4 with inp_student_id: $inp_student_id and inp_callsign: $inp_callsign<br />";
		}
		// get the record
		if ($doDebug) {
			echo "calling student_dal get_student_by_id to get the student record by student_id<br />";
		}
		$student_data = $student_dal->get_student_by_id($inp_student_id,$operatingMode);
		if ($student_data === NULL) {
			$content .= "<p>Attempting to retrieve $inp_student_id returned NULL</p>";
		} else {
			if ($doDebug) {
				echo "decoding $inp_student_id record<br />";
			}
			foreach($student_data as $key => $value) {
				$$key = $value;
			}
			$content .= "<h3>$jobname</h3>
						<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student</p>
						<h4>Results of the Update</h4>";

			if ($doDebug) {
				echo "checking for any changes<br />";
			}
			$updateParams	= array();
			$thisDate		= date("Y-m-d H:i:s");
			$updateLog	= "/ $thisDate $userName did the following updates: ";
			if ($inp_student_time_zone != $student_time_zone) {
				$student_time_zone = $inp_student_time_zone;
				$updateParams['student_time_zone']	= $inp_student_time_zone;
				$content	.= "student_time_zone updated to $inp_student_time_zone<br />";
				$updateLog	.= " /student_time_zone updated to $inp_student_time_zone";
			}
			if ($inp_student_timezone_offset != $student_timezone_offset) {
				$student_timezone_offset = $inp_student_timezone_offset;
				$updateParams['student_timezone_offset']	= $inp_student_timezone_offset;
				$content	.= "student_timezone_offset updated to $inp_student_timezone_offset<br />";
				$updateLog	.= " /student_timezone_offset updated to $inp_student_timezone_offset";
			}
			if ($inp_student_youth != $student_youth) {
				$student_youth = $inp_student_youth;
				$updateParams['student_youth']	= $inp_student_youth;
				$content	.= "student_youth updated to $inp_student_youth<br />";
				$updateLog	.= " /student_youth updated to $inp_student_youth";
			}
			if ($inp_student_age != $student_age) {
				$student_age = $inp_student_age;
				$updateParams['student_age']	= $inp_student_age;
				$content	.= "student_age updated to $inp_student_age<br />";
				$updateLog	.= " /student_age updated to $inp_student_age";
			}
			if ($inp_student_parent != $student_parent) {
				$student_parent = $inp_student_parent;
				$updateParams['student_parent']	= $inp_student_parent;
				$content	.= "student_parent updated to $inp_student_parent<br />";
				$updateLog	.= " /student_parent updated to $inp_student_parent";
			}
			if ($inp_student_parent_email != $student_parent_email) {
				$student_parent_email = $inp_student_parent_email;
				$updateParams['student_parent_email']	= $inp_student_parent_email;
				$content	.= "student_parent_email updated to $inp_student_parent_email<br />";
				$updateLog	.= " /student_parent_email updated to $inp_student_parent_email";
			}
			if ($inp_student_level != $student_level) {
				$student_level = $inp_student_level;
				$updateParams['student_level']	= $inp_student_level;
				$content	.= "student_level updated to $inp_student_level<br />";
				$updateLog	.= " /student_level updated to $inp_student_level";
			}
			if ($inp_student_class_language != $student_class_language) {
				$student_class_language = $inp_student_class_language;
				$updateParams['student_class_language']	= $inp_student_class_language;
				$updateFormat[]	= "%s";
				$content	.= "student_class_language updated to $inp_student_class_language<br />";
				$updateLog	.= " /student_class_language updated to $inp_student_class_language";
			}
			if ($inp_student_waiting_list != $student_waiting_list) {
				$student_waiting_list = $inp_student_waiting_list;
				$updateParams['student_waiting_list']	= $inp_student_waiting_list;
				$content	.= "student_waiting_list updated to $inp_student_waiting_list<br />";
				$updateLog	.= " /student_waiting_list updated to $inp_student_waiting_list";
			}
			if ($inp_student_request_date != $student_request_date) {
				$student_request_date = $inp_student_request_date;
				$updateParams['student_request_date']	= $inp_student_request_date;
				$content	.= "student_request_date updated to $inp_student_request_date<br />";
				$updateLog	.= " /student_request_date updated to $inp_student_request_date";
			}
			if ($inp_student_semester != $student_semester) {
				$student_semester = $inp_student_semester;
				$updateParams['student_semester']	= $inp_student_semester;
				$content	.= "student_semester updated to $inp_student_semester<br />";
				$updateLog	.= " /student_semester updated to $inp_student_semester";
			}
			if ($inp_student_notes != $student_notes) {
				$student_notes = $inp_student_notes;
				$updateParams['student_notes']	= $inp_student_notes;
				$content	.= "student_notes updated to $inp_student_notes<br />";
				$updateLog	.= " /student_notes updated to $inp_student_notes";
			}
			if ($inp_student_welcome_date != $student_welcome_date) {
				$student_welcome_date = $inp_student_welcome_date;
				$updateParams['student_welcome_date']	= $inp_student_welcome_date;
				$content	.= "student_welcome_date updated to $inp_student_welcome_date<br />";
				$updateLog	.= " /student_welcome_date updated to $inp_student_welcome_date";
			}
			if ($inp_student_email_sent_date != $student_email_sent_date) {
				$student_email_sent_date = $inp_student_email_sent_date;
				$updateParams['student_email_sent_date']	= $inp_student_email_sent_date;
				$content	.= "student_email_sent_date updated to $inp_student_email_sent_date<br />";
				$updateLog	.= " /student_email_sent_date updated to $inp_student_email_sent_date";
			}
			if ($inp_student_email_number != $student_email_number) {
				$student_email_number = $inp_student_email_number;
				$updateParams['student_email_number']	= $inp_student_email_number;
				$content	.= "student_email_number updated to $inp_student_email_number<br />";
				$updateLog	.= " /student_email_number updated to $inp_student_email_number";
			}
			if ($inp_student_response != $student_response) {
				$student_response = $inp_student_response;
				$updateParams['student_response']	= $inp_student_response;
				$content	.= "student_response updated to $inp_student_response<br />";
				$updateLog	.= " /student_response updated to $inp_student_response";
			}
			if ($inp_student_response_date != $student_response_date) {
				$student_response_date = $inp_student_response_date;
				$updateParams['student_response_date']	= $inp_student_response_date;
				$content	.= "student_response_date updated to $inp_student_response_date<br />";
				$updateLog	.= " /student_response_date updated to $inp_student_response_date";
			}
			if ($inp_student_abandoned != $student_abandoned) {
				$student_abandoned = $inp_student_abandoned;
				$updateParams['student_abandoned']	= $inp_student_abandoned;
				$content	.= "student_abandoned updated to $inp_student_abandoned<br />";
				$updateLog	.= " /student_abandoned updated to $inp_student_abandoned";
			}
			if ($inp_student_status != $student_status) {
				$student_status = $inp_student_status;
				$updateParams['student_status']	= $inp_student_status;
				$content	.= "student_status updated to $inp_student_status<br />";
				$updateLog	.= " /student_status updated to $inp_student_status";
			}
			if ($inp_student_action_log != $student_action_log) {
				$student_action_log = $inp_student_action_log;
				$updateParams['student_action_log']	= $inp_student_action_log;
				$content	.= "student_action_log updated to $inp_student_action_log<br />";
			}
			if ($inp_student_pre_assigned_advisor != $student_pre_assigned_advisor) {
				$student_pre_assigned_advisor = $inp_student_pre_assigned_advisor;
				$updateParams['student_pre_assigned_advisor']	= $inp_student_pre_assigned_advisor;
				$content	.= "student_pre_assigned_advisor updated to $inp_student_pre_assigned_advisor<br />";
				$updateLog	.= " /student_pre_assigned_advisor updated to $inp_student_pre_assigned_advisor";
			}
			if ($inp_student_selected_date != $student_selected_date) {
				$student_selected_date = $inp_student_selected_date;
				$updateParams['student_selected_date']	= $inp_student_selected_date;
				$content	.= "student_selected_date updated to $inp_student_selected_date<br />";
				$updateLog	.= " /student_selected_date updated to $inp_student_selected_date";
			}
			if ($inp_student_no_catalog != $student_no_catalog) {
				$student_no_catalog = $inp_student_no_catalog;
				$updateParams['student_no_catalog']	= $inp_student_no_catalog;
				$content	.= "student_no_catalog updated to $inp_student_no_catalog<br />";
				$updateLog	.= " /student_no_catalog updated to $inp_student_no_catalog";
			}
			if ($inp_student_hold_override != $student_hold_override) {
				$student_hold_override = $inp_student_hold_override;
				$updateParams['student_hold_override']	= $inp_student_hold_override;
				$content	.= "student_hold_override updated to $inp_student_hold_override<br />";
				$updateLog	.= " /student_hold_override updated to $inp_student_hold_override";
			}
			if ($inp_student_assigned_advisor != $student_assigned_advisor) {
				$student_assigned_advisor = $inp_student_assigned_advisor;
				$updateParams['student_assigned_advisor']	= $inp_student_assigned_advisor;
				$content	.= "student_assigned_advisor updated to $inp_student_assigned_advisor<br />";
				$updateLog	.= " /student_assigned_advisor updated to $inp_student_assigned_advisor";
			}
			if ($inp_student_advisor_select_date != $student_advisor_select_date) {
				$student_advisor_select_date = $inp_student_advisor_select_date;
				$updateParams['student_advisor_select_date']	= $inp_student_advisor_select_date;
				$content	.= "student_advisor_select_date updated to $inp_student_advisor_select_date<br />";
				$updateLog	.= " /student_advisor_select_date updated to $inp_student_advisor_select_date";
			}
			if ($inp_student_advisor_class_timezone != $student_advisor_class_timezone) {
				$student_advisor_class_timezone = $inp_student_advisor_class_timezone;
				$updateParams['student_advisor_class_timezone']	= $inp_student_advisor_class_timezone;
				$content	.= "student_advisor_class_timezone updated to $inp_student_advisor_class_timezone<br />";
				$updateLog	.= " /student_advisor_class_timezone updated to $inp_student_advisor_class_timezone";
			}
			if ($inp_student_hold_reason_code != $student_hold_reason_code) {
				$student_hold_reason_code = $inp_student_hold_reason_code;
				$updateParams['student_hold_reason_code']	= $inp_student_hold_reason_code;
				$content	.= "student_hold_reason_code updated to $inp_student_hold_reason_code<br />";
				$updateLog	.= " /student_hold_reason_code updated to $inp_student_hold_reason_code";
			}
			if ($inp_student_class_priority != $student_class_priority) {
				$student_class_priority = $inp_student_class_priority;
				$updateParams['student_class_priority']	= $inp_student_class_priority;
				$content	.= "student_class_priority updated to $inp_student_class_priority<br />";
				$updateLog	.= " /student_class_priority updated to $inp_student_class_priority";
			}
			if ($inp_student_assigned_advisor_class != $student_assigned_advisor_class) {
				$student_assigned_advisor_class = $inp_student_assigned_advisor_class;
				$updateParams['student_assigned_advisor_class']	= $inp_student_assigned_advisor_class;
				$content	.= "student_assigned_advisor_class updated to $inp_student_assigned_advisor_class<br />";
				$updateLog	.= " /student_assigned_advisor_class updated to $inp_student_assigned_advisor_class";
			}
			if ($inp_student_promotable != $student_promotable) {
				$student_promotable = $inp_student_promotable;
				$updateParams['student_promotable']	= $inp_student_promotable;
				$content	.= "student_promotable updated to $inp_student_promotable<br />";
				$updateLog	.= " /student_promotable updated to $inp_student_promotable";
			}
			if ($inp_student_excluded_advisor != $student_excluded_advisor) {
				$student_excluded_advisor = $inp_student_excluded_advisor;
				$updateParams['student_excluded_advisor']	= $inp_student_excluded_advisor;
				$content	.= "student_excluded_advisor updated to $inp_student_excluded_advisor<br />";
				$updateLog	.= " /student_excluded_advisor updated to $inp_student_excluded_advisor";
			}
			if ($inp_student_survey_completion_date != $student_survey_completion_date) {
				$student_survey_completion_date = $inp_student_survey_completion_date;
				$updateParams['student_survey_completion_date']	= $inp_student_survey_completion_date;
				$content	.= "student_survey_completion_date updated to $inp_student_survey_completion_date<br />";
				$updateLog	.= " /student_survey_completion_date updated to $inp_student_survey_completion_date";
			}
			if ($inp_student_available_class_days != $student_available_class_days) {
				$student_available_class_days = $inp_student_available_class_days;
				$updateParams['student_available_class_days']	= $inp_student_available_class_days;
				$content	.= "student_available_class_days updated to $inp_student_available_class_days<br />";
				$updateLog	.= " /student_available_class_days updated to $inp_student_available_class_days";
			}
			if ($inp_student_intervention_required != $student_intervention_required) {
				$student_intervention_required = $inp_student_intervention_required;
				$updateParams['student_intervention_required']	= $inp_student_intervention_required;
				$content	.= "student_intervention_required updated to $inp_student_intervention_required<br />";
				$updateLog	.= " /student_intervention_required updated to $inp_student_intervention_required";
			}
			if ($inp_student_copy_control != $student_copy_control) {
				$student_copy_control = $inp_student_copy_control;
				$updateParams['student_copy_control']	= $inp_student_copy_control;
				$content	.= "student_copy_control updated to $inp_student_copy_control<br />";
				$updateLog	.= " /student_copy_control updated to $inp_student_copy_control";
			}
			if ($inp_student_first_class_choice != $student_first_class_choice) {
				$student_first_class_choice = $inp_student_first_class_choice;
				$updateParams['student_first_class_choice']	= $inp_student_first_class_choice;
				$content	.= "student_first_class_choice updated to $inp_student_first_class_choice<br />";
				$updateLog	.= " /student_first_class_choice updated to $inp_student_first_class_choice";
			}
			if ($inp_student_second_class_choice != $student_second_class_choice) {
				$student_second_class_choice = $inp_student_second_class_choice;
				$updateParams['student_second_class_choice']	= $inp_student_second_class_choice;
				$content	.= "student_second_class_choice updated to $inp_student_second_class_choice<br />";
				$updateLog	.= " /student_second_class_choice updated to $inp_student_second_class_choice";
			}
			if ($inp_student_third_class_choice != $student_third_class_choice) {
				$student_third_class_choice = $inp_student_third_class_choice;
				$updateParams['student_third_class_choice']	= $inp_student_third_class_choice;
				$content	.= "student_third_class_choice updated to $inp_student_third_class_choice<br />";
				$updateLog	.= " /student_third_class_choice updated to $inp_student_third_class_choice";
			}
			if ($inp_student_first_class_choice_utc != $student_first_class_choice_utc) {
				$student_first_class_choice_utc = $inp_student_first_class_choice_utc;
				$updateParams['student_first_class_choice_utc']	= $inp_student_first_class_choice_utc;
				$content	.= "student_first_class_choice_utc updated to $inp_student_first_class_choice_utc<br />";
				$updateLog	.= " /student_first_class_choice_utc updated to $inp_student_first_class_choice_utc";
			}
			if ($inp_student_second_class_choice_utc != $student_second_class_choice_utc) {
				$student_second_class_choice_utc = $inp_student_second_class_choice_utc;
				$updateParams['student_second_class_choice_utc']	= $inp_student_second_class_choice_utc;
				$content	.= "student_second_class_choice_utc updated to $inp_student_second_class_choice_utc<br />";
				$updateLog	.= " /student_second_class_choice_utc updated to $inp_student_second_class_choice_utc";
			}
			if ($inp_student_third_class_choice_utc != $student_third_class_choice_utc) {
				$student_third_class_choice_utc = $inp_student_third_class_choice_utc;
				$updateParams['student_third_class_choice_utc']	= $inp_student_third_class_choice_utc;
				$content	.= "student_third_class_choice_utc updated to $inp_student_third_class_choice_utc<br />";
				$updateLog	.= " /student_third_class_choice_utc updated to $inp_student_third_class_choice_utc";
			}
			if ($inp_student_catalog_options != $student_catalog_options) {
				$student_catalog_options = $inp_student_catalog_options;
				$updateParams['student_catalog_options']	= $inp_student_catalog_options;
				$content	.= "student_catalog_options updated to $inp_student_catalog_options<br />";
				$updateLog	.= " /student_catalog_options updated to $inp_student_catalog_options";
			}
			if ($inp_student_flexible != $student_flexible) {
				$student_flexible = $inp_student_flexible;
				$updateParams['student_flexible']	= $inp_student_flexible;
				$content	.= "student_flexible updated to $inp_student_flexible<br />";
				$updateLog	.= " /student_flexible updated to $inp_student_flexible";
			}
			if ($inp_student_date_created != $student_date_created) {
				$student_date_created = $inp_student_date_created;
				$updateParams['student_date_created']	= $inp_student_date_created;
				$content	.= "student_date_created updated to $inp_student_date_created<br />";
				$updateLog	.= " /student_date_created updated to $inp_student_date_created";
			}
			if ($inp_student_date_updated != $student_date_updated) {
				$student_date_updated = $inp_student_date_updated;
				$updateParams['student_date_updated']	= $inp_student_date_updated;
				$content	.= "student_date_updated updated to $inp_student_date_updated<br />";
				$updateLog	.= " /student_date_updated updated to $inp_student_date_updated";
			}					
			// ready to write to the database
			if (count($updateParams) > 0) {		// only update if there were changes
				// setup the action_log
				$student_action_log			.= $updateLog;
				$updateParams['student_action_log'] = $student_action_log;
				if ($doDebug) {
					echo "calling student_dal->update_student to do the updates<br >";
				}
				if ($doDebug) {
					echo "<br />updateParams:<br /><pre>";
					print_r($updateParams);
					echo "</pre><br />";
				}
				
				
				$update_result = $student_dal->update($inp_student_id,$updateParams,$operatingMode);
				if ($doDebug) {
					echo "update_result:<br /><pre>";
					print_r($update_result);
					echo "</pre><br />";
				}
				if ($update_result === FALSE) {
					$content	.= "Updating $inp_studet_id record failed";
				} else {
					$content		.= "<p>Student record updated</p>";
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
			$admin = 'N';
			if ($userRole == 'Administrator') {
				$admin = 'Y';
			}
			$user_data = get_user_master_for_display('callsign',$student_call_sign,$admin,$operatingMode,$doDebug=FALSE);
			if ($doDebug) {
				echo"user_data:<br /><pre>";
				print_r($user_data);
				echo "</pre><br />";
			}
			if (count($user_data) > 0) {
				foreach($user_data as $key => $value) {
					$theDisplay = $user_data[$key]['display'];
				}
				if ($doDebug) {
					echo "displaying the user_master data<br />";
				}
				$content		.= "<h4>Student Master Data</h4>
								<table style='width:900px;'>
								$theDisplay
								</table>
								<p>Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=10' 
								target='_blank'>HERE</a> to update the advisor Master Data</p>";
			
				// get the student signup info
				if ($doDebug) {
					echo "calling student_dal->get_student_by_id to display updated record<br />";
				}
				$student_data = $student_dal->get_student_by_id($student_id,$operatingMode);
				if ($student_data === NULL) {
					$content .= "<p>Attempting to retrieve $student_id returned NULL</p>";
				} else {
					foreach($student_data as $key=>$value) {
						$$key = $value;
					}
					
					if ($doDebug) {
						echo "displaying the student record<br />";
					}
					$student_action_log	= formatActionLog($student_action_log);
					
					$updateLink			= "<a href='$theURL/?strpass=3&inp_callsign=$inp_callsign&inp_student_id=$student_id&inp_verbose=$inp_verbose&inp_mode=$inp_mode'>$student_id<a/>";
					$preAssignedLink	= '';
					if ($student_pre_assigned_advisor != '') {
						$preAssignedLink	= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$student_pre_assigned_advisor&inp_depth=one&doDebug&testMode' target='_blank'>$student_pre_assigned_advisor</a>";
					}
					$assignedLink		= '';
					if ($student_assigned_advisor != '') {
						$assignedLink		= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$student_assigned_advisor&inp_depth=one&doDebug&testMode' target='_blank'>$student_assigned_advisor</a>";
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
											<tr><td style='width:300px;'>Student Student Id</td>
												<td>$updateLink</td></tr>
											<tr><td>Student Call Sign</td>
												<td>$student_call_sign</td></tr>
											<tr><td>Student Time Zone</td>
												<td>$student_time_zone</td></tr>
											<tr><td>Student Timezone Offset</td>
												<td>$student_timezone_offset</td></tr>
											<tr><td>Student Youth</td>
												<td>$student_youth</td></tr>
											<tr><td>Student Age</td>
												<td>$student_age</td></tr>
											<tr><td>Student Student Parent</td>
												<td>$student_parent</td></tr>
											<tr><td>Student Student Parent Email</td>
												<td>$student_parent_email</td></tr>
											<tr><td>Student Level</td>
												<td>$student_level</td></tr>
											<tr><td>Student Class Language</td>
												<td>$student_class_language</td></tr>
											<tr><td>Student Waiting List</td>
												<td>$student_waiting_list $waitingStr</td></tr>
											<tr><td>Student Request Date</td>
												<td>$student_request_date</td></tr>
											<tr><td>Student Semester</td>
												<td>$student_semester</td></tr>
											<tr><td>Student Notes</td>
												<td style='vertical-align:top;'>$student_notes</td></tr>
											<tr><td>Student Welcome Date</td>
												<td>$student_welcome_date</td></tr>
											<tr><td>Student Email Sent Date</td>
												<td>$student_email_sent_date</td></tr>
											<tr><td>Student Email Number</td>
												<td>$student_email_number</td></tr>
											<tr><td>Student Response</td>
												<td>$student_response $responseStr</td></tr>
											<tr><td>Student Response Date</td>
												<td>$student_response_date</td></tr>
											<tr><td>Student Abandoned</td>
												<td>$student_abandoned $abandonedStr</td></tr>
											<tr><td>Student Student Status</td>
												<td>$student_status $statusStr</td></tr>
											<tr><td style='vertical-align:top;'>Student Action Log</td>
												<td>$student_action_log</td></tr>
											<tr><td>Student Pre Assigned Advisor</td>
												<td>$preAssignedLink</td></tr>
											<tr><td>Student Selected Date</td>
												<td>$student_selected_date</td></tr>
											<tr><td>Student No Catalog</td>
												<td>$student_no_catalog $catalogStr</td></tr>
											<tr><td>Student Hold Override</td>
												<td>$student_hold_override</td></tr>
											<tr><td>Student Assigned Advisor</td>
												<td>$assignedLink</td></tr>
											<tr><td>Student Advisor Select Date</td>
												<td>$student_advisor_select_date</td></tr>
											<tr><td>Student Advisor Class Timezone</td>
												<td>$student_advisor_class_timezone</td></tr>
											<tr><td>Student Hold Reason Code</td>
												<td>$student_hold_reason_code $reasonStr</td></tr>
											<tr><td>Student Class Priority</td>
												<td>$student_class_priority</td></tr>
											<tr><td>Student Assigned Advisor Class</td>
												<td>$student_assigned_advisor_class</td></tr>
											<tr><td>Student Promotable</td>
												<td>$student_promotable $promotableStr</td></tr>
											<tr><td>Student Excluded Advisor</td>
												<td>$student_excluded_advisor</td></tr>
											<tr><td>Student Student Survey Completion Date</td>
												<td>$student_survey_completion_date</td></tr>
											<tr><td>Student Available Class Days</td>
												<td>$student_available_class_days</td></tr>
											<tr><td>Student Intervention Required</td>
												<td>$student_intervention_required</td></tr>
											<tr><td>Student Copy Control</td>
												<td>$student_copy_control</td></tr>
											<tr><td>Student First Class Choice</td>
												<td>$student_first_class_choice</td></tr>
											<tr><td>Student Second Class Choice</td>
												<td>$student_second_class_choice</td></tr>
											<tr><td>Student Third Class Choice</td>
												<td>$student_third_class_choice</td></tr>
											<tr><td>Student First Class Choice Utc</td>
												<td>$student_first_class_choice_utc</td></tr>
											<tr><td>Student Second Class Choice Utc</td>
												<td>$student_second_class_choice_utc</td></tr>
											<tr><td>Student Third Class Choice Utc</td>
												<td>$student_third_class_choice_utc</td></tr>
											<tr><td>Student Catalog Options</td>
												<td>$student_catalog_options</td></tr>
											<tr><td>Student Flexible</td>
												<td>$student_flexible $flexibleStr</td></tr>
											<tr><td>Student Date Created</td>
												<td>$student_date_created</td></tr>
											<tr><td>Student Date Updated</td>
												<td>$student_date_updated</td></tr>
											</table>
											<p>Click <a href='$theURL/?strpass=3&inp_callsign=$inp_callsign&inp_student_id=$student_id'>HERE</a>
											to modify this signup record</p>
											<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student</p>";
				}
			} else {
				$content			.= "<p>No user_master record found for $student_call_sign</p";
			}
		}

////// pass 5 - delete the signup record

	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 5 with inp_student_id: $inp_student_id<br />
			";
		}
		$content		.= "<h3>$jobname</h3>
							<p>Click <a href='$theURL'>HERE</a> to Look Up a Different Student</p>
							<h4>Deleting record ID $inp_student_id</h4>";
		$delete_result = $student_dal->delete($inp_student_id,$operatingMode);
		if ($delete_result === FALSE) {
			if ($doDebug) {
				echo "attempting to delete id $inp_student_id failed<br />";
			}
			$content	.= "Deleting id $inp_student_id failed<br />";
		} else {
			$content	.= "ID $inp_student_id deleted<br />";
		}
	}
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
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('display_and_update_student_signup', 'display_and_update_student_signup_func');

