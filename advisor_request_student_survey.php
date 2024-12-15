function advisor_request_student_survey_func() {

/* Request Student Survey

	Gives the advisor a page listing the students along with the default 
	settings. Advisor can choose which students get the survey. 

	forked from advisor_request_student_assessment 13Dec24 by Roland
*/

	global $wpdb, $doDebug, $testMode;



	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$userName						= $initializationArray['userName'];
	$userRole						= $initializationArray['userRole'];
	$holdUser						= $userName;
	$holdRole						= $userRole;
	
	if ($userName == '') {
		return "You are not authorized";
	}
	if ($userRole != 'administrator') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$siteURL			= $initializationArray['siteurl'];
	$currentDateTime	= $initializationArray['currentDateTime'];
	$versionNumber		= '1';
	$theSemester		= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$theSemester	= $nextSemester;
	}
	
	

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-advisor-request-student-survey/";
	$inp_semester				= '';
	$jobname					= "Advisor Request Student Survey";
	$studentCallSign			= "";
	$studentEmail				= "";
	$studentPhone				= "";
	$inp_callsign				= "";
	$studentName				= "";
	$advisorCallSign			= "";
	$studentLevel				= "";
	$inp_mode					= "";
	$inp_comments				= "";
	$inp_verbose				= 'N';
	$runtype					= 'student';
	$advisorCallSign			= '';
	$email						= '';
	$phone						= '';
	$studentSemester			= '';
	$inPastStudent				= TRUE;
	$actionDate					= date('Y-m-d H:i:s');
	$controlCode				= '';
	$inp_freq					= '';
	$inp_questions				= '';
	$inp_words					= '';
	$inp_chars					= '';
	$inp_cscount				= '';
	$inp_makeup					= '';
	$inp_answers				= '';
	$inp_vocab					= '';
	$inp_students				= array();
	$inp_wpm					= '';
	$inp_eff					= '';
	$advisor_first_name			= '';
	$advisor_last_name			= '';
	$advisorClassCount			= 0;
	$advisorEmail				= '';
	$incl_advisor				= 'N';
	$studentList				= '';
	$studentList				= '';

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br /><pre>";
					print_r($str_value);
					echo "</pre><br />";
				}
			}
			if ($str_key 		== "classesArray") {
				$classesArray		 = $str_value;
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 			== "inp_callsign") {
				$inp_callsign		 = $str_value;
				$inp_callsign		 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "inp_students") {
				$inp_students		 = $str_value;
//				$inp_students		 = strtoupper(filter_var($inp_students,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "token") {
				$token				 = $str_value;
				$token		 		= strtoupper(filter_var($token,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "inp_duration") {
				$inp_duration		 = $str_value;
				$inp_duration		 = strtoupper(filter_var($inp_duration,FILTER_UNSAFE_RAW));
			}
			if ($str_key 			== "advisorCallSign") {
				$advisorCallSign		 = $str_value;
				$advisorCallSign		 = strtoupper(filter_var($advisorCallSign,FILTER_UNSAFE_RAW));
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
				if ($inp_mode == 'TESTMODE' || $inp_mode == 'tm') {
					$testMode = TRUE;
				}
			}
			if ($str_key 				== "inp_selected_survey") {
				$inp_selected_survey	 = $str_value;
				$inp_selected_survey	 = filter_var($inp_selected_survey,FILTER_UNSAFE_RAW);
				$thisArray				= explode("|",$inp_selected_survey);
				$inp_survey_id			= intval($thisArray[0]);
				$inp_survey_name		= $thisArray[1];
				$updateSurvey			= TRUE;
				if ($doDebug) {
					echo "set inp_survey_id = $inp_survey_id<br />
						  set inp_survey_name = $inp_survey_name<br />";
				}
			}
		}
	}

	if ($inp_mode == 'tm') {
		$testMode	= TRUE;
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
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$surveysTableName			= "wpw1_cwa_survey_surveys2";
		$surveyContentTableName		= "wpw1_cwa_survey_content2";
		$surveyResponseTableName	= 'wpw1_cwa_survey_response2';
		
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
		$advisorTableName			= "wpw1_cwa_advisor";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$surveysTableName			= "wpw1_cwa_survey_surveys";
		$surveyContentTableName		= "wpw1_cwa_survey_content";
		$surveyResponseTableName	= 'wpw1_cwa_survey_response';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass $strPass with  userName of $userName<br />";
		}
//		$inp_callsign = 'W7GEM';
		if ($inp_callsign == '') {
			$inp_callsign 	= strtoupper($userName);
		}

		$content			.= "<h3>$jobname</h3>
								<p><b>How This Works:</b><br />
								Below is a list of your available questionnaires. 
								Select the questionnaire you want to use, indicate how many days 
								the survey should be available to the student, and submit.<br /><br />
								A list of your classes will then be displayed. Select 
								the class(es) that the questionnaire applies to and submit.<br /><br />
								A list of students in the selected classes will be displayed. All 
								of them will be pre-selected to receive the questionnaire. Deselect 
								any who should not receive the questionnaire and submit.<br /><br />
								An email will be sent to each selected student instructing the 
								student to log into the CW Academy website and follow the instructions 
								for filling out the survey at the top of their Student Portal.<br />
								When a student has completed the questionnaire you will get an email. 
								A task will be on your Advisor Portal with a link to view the 
								student responses.</p>";

		$surveySQL		= "select * from $surveysTableName 
							where survey_owner='$inp_callsign'";
		$surveyResult	= $wpdb->get_results($surveySQL);
		if ($surveyResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass 1 getting list of surveys");
		} else {
			$numSRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "Ran $surveySQL<br />and retrieved $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				$surveyRadioList			= "";
				foreach($surveyResult as $surveyResultRow) {
					$survey_record_id		= $surveyResultRow->survey_record_id;
					$survey_owner			= $surveyResultRow->survey_owner;
					$survey_name			= $surveyResultRow->survey_name;
					$survey_action_log		= $surveyResultRow->survey_action_log;
					$survey_date_created	= $surveyResultRow->survey_date_created;
					$survey_date_updated	= $surveyResultRow->survey_date_updated;
					
					$surveyRadioList		.= "<input type='radio' class='formInputButton' name='inp_selected_survey' value='$survey_record_id|$survey_name' required>$survey_name<br />";
				}

				$content	.= "<table><tr><td><form method='post' action='$theURL' 
								name='survey_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='2'>
								<input type='hidden' name='inp_callsign' value='$inp_callsign'>
								<br /><u>Available Surveys</u><br />
								$surveyRadioList<br />
								<br />How many days shall the recipient have to answer the questionnaire? 
								When the time expires, the survey will no longer be available to the student.<br />
								<input type='text' class='formInutText' name='inp_duration' size='5' maxlength='5' required><br />
								<br /><input class='formInputButton' name='submit' type='submit' value='Submit' />
								</form></tr></td></table>";
			} else {
				$content	.= "<p>No questionnaires found for $inp_callsign</p>";
			}
		}
	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass2 with survey $inp_selected_survey for advisor $inp_callsign<br />";
		}
		$content			.= "<h3>$jobname</h3>";
		// get the advisor information
		
		$sql				= "select * from $advisorTableName 
								left join $userMasterTableName on user_call_sign = advisor_call_sign 
								where advisor_call_sign = '$inp_callsign' 
								and advisor_semester = '$theSemester'";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass2 attempting to get the advisor record");
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

					// get the advisor classes
					$sql				= "select * from $advisorClassTableName 
											where advisorclass_call_sign = '$inp_callsign' 
											and advisorclass_semester = '$theSemester'";
					$advisorClassResult	= $wpdb->get_results($sql);
					if ($advisorClassResult === FALSE) {
						handleWPDBError($jobname,$doDebug,"pass2 attempting to get the advisor classes");
						$content		.= "<b>ERROR</b> There are no class records for advisor $inp_callsign";
					} else {
						$numACRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and retrieved $numACRows rows<br />";
						}
						if ($numACRows > 0) {
							$classesArray								= array();
							$selectionList								= "";
							foreach($advisorClassResult as $advisorClassRow) {
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
								
								if ($numACRows == 1) {
									$content		.= "<p>You have one $advisorClass_Level class. Next step is to 
														indicate which students should answer the questionnaire</p>";
								$strPass			= '3';
								$classesArray[]		= $advisorClass_sequence;
														
								} else {
									$selectionList	.= "<input type='checkbox' class='formInputButton' name='classesArray[]' value='$advisorClass_sequence'>Class $advisorClass_sequence $advisorClass_level <br />";
								}
							}
							if ($numACRows > 1) {
								$content			.= "<h4>Indicate Which Classes Should Have the Questionnaire</h4>
														<form method='post' action='$theURL' 
														name='survey_form' ENCTYPE='multipart/form-data'>
														<input type='hidden' name='strpass' value='3'>
														<input type='hidden' name='inp_callsign' value='$inp_callsign'>
														<input type='hidden' name='inp_duration' value='$inp_duration'>
														<input type='hidden' name='inp_selected_survey' value='$inp_selected_survey'>
														<input type='hidden' name='numACRows' value='$numACRows'>
														<table>
														<tr><td style='vertical-align:top;width:150px;'>Classes</td>
															<td>$selectionList</td>
														<tr><td></td>
															<td><input class='formInputButton' name='submit' type='submit' value='Submit' /></td></tr>
														</table></form>";
							}
								
						} else {
							$content		.= "<p>No class records found for $inp_callsign</p>";
						}
					}
				}
			} else {
				$content				.= "<p>No advisor record found for $inp_callsign</p>";
			}
		}
	}
	
	if ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass3<br /><pre>";
			print_r($classesArray);
			echo "</pre><br />";
		}

		$content		.= "<h3>$jobname</h3>";
		$studentList		= '';
		$studentListCount	= 0;

		foreach($classesArray as $thisClass) {
			
			$sql			= "select * from $advisorClassTableName 
								left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
								where advisorclass_call_sign = '$inp_callsign' 
								and advisorclass_semester = '$theSemester' 
								and advisorclass_sequence = $thisClass";
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
						$advisorClass_master_ID 				= $advisorClassRow->user_ID;
						$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
						$advisorClass_first_name 				= $advisorClassRow->user_first_name;
						$advisorClass_last_name 				= $advisorClassRow->user_last_name;
						$advisorClass_email 					= $advisorClassRow->user_email;
						$advisorClass_ph_code 					= $advisorClassRow->user_ph_code;
						$advisorClass_phone 					= $advisorClassRow->user_phone;
						$advisorClass_city 						= $advisorClassRow->user_city;
						$advisorClass_state 					= $advisorClassRow->user_state;
						$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
						$advisorClass_country_code 				= $advisorClassRow->user_country_code;
						$advisorClass_country 					= $advisorClassRow->user_country;
						$advisorClass_whatsapp 					= $advisorClassRow->user_whatsapp;
						$advisorClass_telegram 					= $advisorClassRow->user_telegram;
						$advisorClass_signal 					= $advisorClassRow->user_signal;
						$advisorClass_messenger 				= $advisorClassRow->user_messenger;
						$advisorClass_master_action_log 		= $advisorClassRow->user_action_log;
						$advisorClass_timezone_id 				= $advisorClassRow->user_timezone_id;
						$advisorClass_languages 				= $advisorClassRow->user_languages;
						$advisorClass_survey_score 				= $advisorClassRow->user_survey_score;
						$advisorClass_is_admin					= $advisorClassRow->user_is_admin;
						$advisorClass_role 						= $advisorClassRow->user_role;
						$advisorClass_master_date_created 		= $advisorClassRow->user_date_created;
						$advisorClass_master_date_updated 		= $advisorClassRow->user_date_updated;
	
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
	
						if ($advisorClass_number_students > 0) {
							// build list of students
							$studentList		.= "<br /><p>Students in class $advisorClass_sequence $advisorClass_level</p>";
							for ($snum=1;$snum<31;$snum++) {
								if ($snum < 10) {
									$strSnum 		= str_pad($snum,2,'0',STR_PAD_LEFT);
								} else {
									$strSnum		= strval($snum);
								}
								$studentCallSign	= ${'advisorClass_student' . $strSnum};
								if ($studentCallSign != '') {
									// get the student name
									$sql			= "select * from $userMasterTableName 
														where user_call_sign = '$studentCallSign'";
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
												$student_first_name						= $studentRow->user_first_name;
												$student_last_name						= $studentRow->user_last_name;
	
												
												$studentList	.= "<input type='checkbox' name='inp_students[]' value='$studentCallSign' checked>$student_last_name, $student_first_name ($studentCallSign)\n<br />";
												$studentListCount++;
											}
										}
									}
								}
							}
						} else {
							if ($doDebug) {
								echo "no students in the class<br />";
							}
						}
					}
				} else {
					if ($doDebug) {
						echo "no record found for $inp_callsign $thisClass<br />";
					}
				}
			}
		}
		if ($studentListCount > 0) {
			$content	.= "<form method='post' action='$theURL' 
							name='advisor_selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='5'>
							<input type='hidden' name='inp_duration' value='$inp_duration'>
							<input type='hidden' name='inp_selected_survey' value='$inp_selected_survey'>
							<input type='hidden' name='inp_mode' value='$inp_mode'>
							<input type='hidden' name='inp_verbose' value='$inp_verbose'>
							<table>
							<tr><td style='vertical-align:top;'>Select Students to Receive the Questionnaire<br />
											$studentList
											</td>
							<tr><td><input class='formInputButton' type='submit' value='Submit' /></td></tr>
							</table></form>";
		}

///// Pass 5 -- do the work


	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass $strPass<br />";
		}
		if ($doDebug) {
			echo "inp_students<br /><pre>";
			print_r($inp_students);
			echo "</pre><br />";
		}
		
		if (count($inp_students) > 0) {
			if ($doDebug) {
				echo "inp_student count is > 0<br />";
			}
			foreach($inp_students as $thisCallSign) {
				if ($doDebug) {
					echo "<br />Processing inp_students ... thisCallSign: $thisCallSign<br />";
				}
				$sql					= "select * from $studentTableName
											left join $userMasterTableName on user_call_sign = student_call_sign  
											where student_call_sign = '$thisCallSign' 
											and student_semester = '$theSemester'";
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
		
							// format and send the email to the student
							$theSubject			= "CW Academy Request for You";
							$theContent			= "To: $student_last_name, $student_first_name ($student_call_sign):
<p>Your $student_level Level class advisor $advisor_first_name $advisor_last_name ($inp_callsign) has requested 
that you fill out a questionnaire.</p>
<p>Please log into the CW Academy website by 
clicking <a href='https://cwa.cwops.org/'>cwa.cwops.org</a> and follow the instructions that 
will be displayed there.</p>";
							if ($testMode) {
								$theSubject	= "TESTMODE $theSubject";
								$myTo		= "rolandksmith@gmail.com";
								$mailCode	= '2';
							} else {
								$myTo		= $student_email;
								$mailCode	= '13';
							}
							$mailResult		= emailFromCWA_v2(array('theRecipient'=>$myTo,
																		'theSubject'=>$theSubject,
																		'theContent'=>$theContent,
																		'theCc'=>'',
																		'theAttachment'=>'',
																		'mailCode'=>$mailCode,
																		'jobname'=>$jobname,
																		'increment'=>0,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug));
							if ($mailResult === FALSE) {
								if ($doDebug) {
									echo "sending the email to $myTo failed<br />";
									sendErrorEmail("$jobname sending email to $myTo failed");
								}
							} else {
								if ($doDebug) {
									echo "email sent<br />";
								}
							}
			
							// format and store the reminder for the student
							$closeStr				= strtotime("$inp_duration days");
							$close_date				= date('Y-m-d H:i:s', $closeStr);
							$token					= mt_rand();
							$url					= "<a href='$siteURL/cwa-display-survey/?strpass=2&inp_survey_id=$inp_survey_id&token=$token&inp_callsign=$student_call_sign' target='_blank'>HERE</a>";
							$reminder_text			= "<b>Complete a Questionnaire</b> Your advisor $inp_callsign 
requests that you fill out a questionnaire. The questionnaire will be available for the 
next $inp_duration days. To start the questionnaire, please click $url.";
							$effective_date		 	= date('Y-m-d H:i:s');
							$inputParams			= array("effective_date|$effective_date|s",
															"close_date|$close_date|s",
															"resolved_date||s",
															"send_reminder|N|s",
															"send_once|N|s",
															"call_sign|$student_call_sign|s",
															"role||s",
															"email_text||s",
															"reminder_text|$reminder_text|s",
															"resolved||s",
															"token|$token|s");
							$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
							if ($insertResult[0] === FALSE) {
								if ($doDebug) {
									echo "inserting reminder failed: $insertResult[1]<br />";
								}
								$content		.= "Inserting reminder failed: $insertResult[1]<br />";
							} else {
								$content		.= "Email sent to $student_call_sign and reminder successfully added<br />";
							}
						}
					} else {
						if ($doDebug) {
							echo "No student record found for $thisCallSign. Skipping student<br />";
						}
					}
				}
			}
		} else {
			if ($doDebug) {
				echo "inp_student count is 0<br />";
			}
		}

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
add_shortcode ('advisor_request_student_survey', 'advisor_request_student_survey_func');
