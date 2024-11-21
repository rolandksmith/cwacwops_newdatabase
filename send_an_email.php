function send_an_email_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
/*
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
*/
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
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-send-an-email/";
	$offset						= 0;
	$emailLimit					= 100;
	$emailCount					= 0;
	$jobname					= "Send an Email V$versionNumber";
	$inp_email					= '';
	$inp_attachment				= '';
	$inp_who					= '';
	$inp_where					= '';
	$increment					= 0;

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
			if ($str_key 		== "inp_who") {
				$inp_who	 = $str_value;
				$inp_who	 = filter_var($inp_who,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_where") {
				$inp_where	 = stripslashes($str_value);
//				$inp_where	 = filter_var($inp_where,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_email") {
				$inp_email	 = stripslashes($str_value);
//				$inp_email	 = filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_subject") {
				$inp_subject	 = stripslashes($str_value);
//				$inp_subject	 = filter_var($inp_subject,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_attachment") {
				$inp_attachment	 = stripslashes($str_value);
//				$inp_attachment	 = filter_var($inp_attachment,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_names") {
				$inp_names	 = $str_value;
//				$inp_names	 = filter_var($inp_names,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "offset") {
				$offset	 = $str_value;
//				$offset	 = filter_var($offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "jsonVar") {
				$jsonVar	 = stripslashes($str_value);
//				$jsonVar	 = filter_var($jsonVar,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "email64") {
				$email64	 = stripslashes($str_value);
//				$email64	 = filter_var($email64,FILTER_UNSAFE_RAW);
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
		$advisorTableName			= "wpw1_cwa_advisor2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
		$advisorTableName			= "wpw1_cwa_advisor";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>This job can send an email to either advisors or 
							students based on the selection criteria entered in 
							the 'Where' field.</p>
							<form method='post' action='$theURL' 
							name='main_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>To whom</td>
								<td><input type='radio' class='formInputButton' name='inp_who' value='advisors' checked>Advisors<br />
									<input type='radio' class='formInputButton' name='inp_who' value='students'>Studentss</td></tr>
							<tr><td style='vertical-align:top;'>Where clause:</td>
								<td><textarea class='formInputText' name='inp_where' rows='5' cols='50'></textarea></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug)	{
			echo "<br />Arrived at pass $strPass with<br />
					inp_who: $inp_who<br />
					inp_where: $inp_where<br />";
		}
		$thisWhere		= '';
		if ($inp_where != '') {
			$thisWhere	= "where $inp_where ";
		}
		$content		.= "<h3>$jobname</h3>
							<p>Unselect any below who should not get an email. Enter the 
							email message to be sent. Finally indicate the attachment 
							(if any) to be included.</p>\n
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='3'>
							<input type='hidden' name=inp_who' value='$inp_who'> 
							<input type='hidden' name='inp_mode' value='$inp_mode'>
							<input type='hidden' name='inp_verbose' value='$inp_verbose'>
							<table style='border-collapse:collapse;width:auto;'>
							<tr><td style='vertical-align:top;'>Email Subject:</td>
								<td><input type='text' class='formInputText' name='inp_subject' 
										size='50' maxlength='100'>
							<tr><td style='vertical-align:top;'>Email</td>
								<td><textarea class='formInputButton' name='inp_email' 
									rows='5' cols='50'></textarea></td></tr>\n
							<tr><td style='vertical-align:top;'>Attachment (if any)</td>
								<td><input type='text' class='formInputText' size='50' 
									maxlength='100' name='inp_attachment'></td></tr>
							<tr><th style='vertical-align:top;width:150px;'>Potential Recipients</th>
								<th>Name and Callsign</th></tr>
							<tr><td></td><td>";
		
		if ($inp_who == 'students') {
			$sql		= "select * from $studentTableName 
							left join $userMasterTableName on user_call_sign = student_call_sign 
							$thisWhere 
							order by student_call_sign ";
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
						$student_master_ID 					= $studentRow->user_ID;
						$student_master_call_sign 			= $studentRow->user_call_sign;
						$student_first_name 				= $studentRow->user_first_name;
						$student_last_name 					= $studentRow->user_last_name;
						$student_email 						= $studentRow->user_email;
						$student_phone 						= $studentRow->user_phone;
						$student_city 						= $studentRow->user_city;
						$student_state 						= $studentRow->user_state;
						$student_zip_code 					= $studentRow->user_zip_code;
						$student_country_code 				= $studentRow->user_country_code;
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
	
						// if you need the country name and phone code, include the following
						$countrySQL		= "select * from wpw1_cwa_country_codes  
											where country_code = '$student_country_code'";
						$countrySQLResult	= $wpdb->get_results($countrySQL);
						if ($countrySQLResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
							$student_country		= "UNKNOWN";
							$student_ph_code		= "";
						} else {
							$numCRows		= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
							}
							if($numCRows > 0) {
								foreach($countrySQLResult as $countryRow) {
									$student_country		= $countryRow->country_name;
									$student_ph_code		= $countryRow->ph_code;
								}
							} else {
								$student_country			= "Unknown";
								$student_ph_code			= "";
							}
						}
						$content			.= "<input type='checkbox' class='formInputButton'
												 name='inp_names[]' 
												 value='$student_email|$student_last_name, $student_first_name ($student_call_sign)' 
												 checked>$student_last_name, $student_first_name ($student_call_sign)<br />\n";
					}
				} else {
					$content			.= "<p>No students match the criteria</p>";
				}
			}
		} else {
			$sql		= "select * from $advisorTableName 
							left join $userMasterTableName on user_call_sign = advisor_call_sign 
							$thisWhere 
							order by advisor_call_sign";
			$wpw1_cwa_advisor	= $wpdb->get_results($sql);
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
	
						// if you need the country name and phone code, include the following
						$countrySQL		= "select * from wpw1_cwa_country_codes  
											where country_code = '$advisor_country_code'";
						$countrySQLResult	= $wpdb->get_results($countrySQL);
						if ($countrySQLResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
							$advisor_country		= "UNKNOWN";
							$advisor_ph_code		= "";
						} else {
							$numCRows		= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
							}
							if($numCRows > 0) {
								foreach($countrySQLResult as $countryRow) {
									$advisor_country		= $countryRow->country_name;
									$advisor_ph_code		= $countryRow->ph_code;
								}
							} else {
								$advisor_country			= "Unknown";
								$advisor_ph_code			= "";
							}
						}

						$content			.= "<input type='checkbox' class='formInputButton'
												 name='inp_names[]' 
												 value='$advisor_email|$advisor_last_name, $advisor_first_name ($advisor_call_sig)' 
												 checked>$advisor_last_name, $advisor_first_name ($advisor_call_sign)<br />\n";
					}
					
				} else {
					$content		.= "No data found";
				}
			}
		}
		$content				.= "</td></tr>
									<tr><td colspan='2'><input type='submit' class='formINputButton' 
											name='submit' value='submit'></td></tr>
									</table></form>";	
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass $strPass<br />";
		}
		
//		if ($doDebug) {
//			echo "inp_email: $inp_email<br />";
//		}
		// json encode the array of folks to get this email and go on to pass 4
		$jsonVar	= json_encode($inp_names);
		if ($doDebug) {
			echo "jsonVar:<br />$jsonVar<br />";
		}

		// fix up the email
		$email64		= base64_encode($inp_email);			
		$strPass		= 4;
	}
	
	
	
	
	
	if ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass $strPass<br />
					offset: $offset<br />";
		}
		
		$inp_email			= base64_decode($email64);
		$emailArray			= json_decode($jsonVar,TRUE);

   switch (json_last_error()) {
        case JSON_ERROR_NONE:
 //           echo ' - No errors';
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
    }
		if ($doDebug) {
			echo "inp_email: $inp_email<br />";
		}		
		
		for($ii = 0;$ii < $emailLimit;$ii++) {
			if ($doDebug) {
				echo "Processing number $offset: $emailArray[$offset]<br />";
			}
			$myArray		= explode("|",$emailArray[$offset]);
			
			
			$theRecipient	= $myArray[0];
			$theContent		= "To: $myArray[1]<br />$inp_email";
			$theCc			= '';
			$mailCode		= 13;
			$increment		= 0;
			if ($inp_attachment != '') {
				$newAttachment	= array(WP_CONTENT_DIR . $inp_attachment);
			} else {
				$newAttachment	= array();
			}
			
			if ($testMode) {
				$theRecipient	= "rolandksmith@gmail.com";
				$mailCode		= 1;
				$increment++;
				$inp_subject	= "TESTMODE $inp_subject";
			}
			$theContent		= "To: $myArray[1]<br />$inp_email";
			if ($inp_attachment != '') {
				$newAttachment	= array(WP_CONTENT_DIR . $inp_attachment);
			} else {
				$newAttachment	= array();
			}
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
														'theSubject'=>$inp_subject,
														'theContent'=>$theContent,
														'theCc'=>$theCc,
														'theAttachment'=>$newAttachment,
														'mailCode'=>$mailCode,
														'jobname'=>$jobname,
														'increment'=>$increment,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug));

			
			$offset++;
			if (!isset($emailArray[$offset])) {
				break;
			}
		}
		$emailCount			= count($emailArray);
		if ($offset < $emailCount) {
			if ($doDebug) {
				echo "not finished. Offset: $offset; emailCount: $emailCount <br />";
			}
			$content		.= "<h3>$jobname</h3>
								<p>Have sent $offset emails. Need to send next batch</p>
								<form method='post' action='$theURL' 
								name='continue_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='4'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<input type='hidden' name=inp_who' value='$inp_who'> 
								<input type='hidden' name=inp_attachment' value='$inp_attachment'> 
								<input type='hidden' name='offset' value='$offset'>
								<input type='hidden' name='jsonVar' value='$jsonVar'>
								<input type='hidden' name='inp_subject' value='$inp_subject'>
								<input type='hidden' name='email64' value='$email64'>
								<input type='submit' class='formInputButton' name='submit' value='Next Batch' />
								</form>";
		} else {
			$content		.= "<h3>$jobname</h3>
								<p>All $offset emails sent</p>";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('send_an_email', 'send_an_email_func');
