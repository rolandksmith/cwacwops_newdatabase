function list_all_students_func() {

// lists all students in the student pod sorted by semester and call sign
// modified 24Aug2020 to correctly handle future semesters
// significantly updated 5July2021 by Roland
//	Modified 25Oct22 by Roland for new timezone tabe format
//	Modified 16Apr23 by Roland to fix action_log
//	Modified 13Jul23 by Roland to use consolidated tables

	global $wpdb, $testMode, $doDebug, $advisorTableName, $printArray, $timeToBlock;

	
	$doDebug						= FALSE;
	$testMode						= FALSE;
	
	$initializationArray		 	= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 						= $initializationArray['validUser'];
	$userName  						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL						= $initializationArray['siteurl'];
	
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('max_execution_time',0);
	set_time_limit(0);

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-list-all-students/";

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
		}
	}
	
	if ($testMode) {
		$studentTableName		= 'wpw1_cwa_consolidated_student2';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisorew2';
		$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
		if ($doDebug) {
			echo "Operating in TestMode<br />";
		}
		
	} else {
		$studentTableName		= 'wpw1_cwa_consolidated_student';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
		$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
	}
	

	$doHeader						= TRUE;
	$headerInterval					= 10;
	$headerCount					= 0;
	$myUnixTime						= $initializationArray['currentTimestamp'];
	$totalStudents					= 0;
	$totalThisSemester				= 0;
	$prev_semester					= "xyz";
	$firstTime						= TRUE;
	$studentArray					= array();
	$ii								= 0;
	$validLevels					= array('Fundamental','Beginner','Intermediate','Advanced');
	$validResponses					= array('Y','R');
	$validStatuses					= array('Y','S','R','C','N');
	$validIntReq					= array('M','Q','H','A','R');
	$validHRC						= array('X','E','H','W');
	$myIntRegex						= '/^(\+|-)?\d+$/';
	$issue01						= 0;
	$issue02						= 0;
	$issue03						= 0;
	$issue04						= 0;
	$issue05						= 0;
	$issue06						= 0;
	$issue07						= 0;
	$issue08						= 0;
	$issue09						= 0;
	$issue10						= 0;
	$issue11						= 0;
	$issue12						= 0;
	$issue13						= 0;
	$issue14						= 0;
	$issue15						= 0;
	$issue16						= 0;
	$issue17						= 0;
	$issue18						= 0;
	$issue19						= 0;
	$issue20						= 0;
	$issue21						= 0;
	$issue22						= 0;
	$issue23						= 0;
	$issue24						= 0;
	$issue25						= 0;
	$issue26						= 0;
	$issue27						= 0;
	$issue28						= 0;
	$issue29						= 0;
	$issueTxt01						= 'Student has invalid level';
	$issueTxt02						= 'Student has no call sign';
	$issueTxt03						= 'Student has an invalid Response';
	$issueTxt04						= 'Student has an invalid Student Status';
	$issueTxt05						= 'Student has an invalid Timezone ID or Offset';
	$issueTxt06						= 'Student has no Request Date';
	$issueTxt07						= 'Student has an future Request Date';
	$issueTxt08						= 'Student should have a welcome Date';
	$issueTxt09						= 'Student has an empty first class choice';
	$issueTxt10						= 'Student\'s first class choice test failed';
	$issueTxt11						= 'Student\'s second class choice test failed';
	$issueTxt12						= 'Student\'s third class choice test failed';
	$issueTxt13						= 'None of student\'s class choices match an available class';
	$issueTxt14						= 'Student should not have a Response';
	$issueTxt15						= 'Student should not have a Student Status';
	$issueTxt16						= 'Student\'s assigned advisor should be blank';
	$issueTxt17						= 'Student\'s hold reason code should be blank';
	$issueTxt18						= 'Student\'s intervention required of should be blank';
	$issueTxt19						= 'Student has an unknown pre-assigned Advisor';
	$issueTxt20						= 'Student has an unknown Assigned Advisor';
	$issueTxt21						= 'Student has an unknown semester';
	$issueTxt22						= 'Student has an invalid Hold Reason Code';
	$issueTxt23						= 'Student has an invalid Student Intervention Required';
	$issueTxt24						= 'Student first class choice not in the catalog';
	$issueTxt25						= 'Student second class choice not in the catalog';
	$issueTxt26						= 'Student third class choice not in the catalog';
	$issueTxt27						= 'Student country and country_code do not match';
	$issueTxt28						= 'Student missing country information';
	$issueTxt29						= 'Student Phone Code invalid or incorrect';

	$issue01Array					= array();
	$issue02Array					= array();
	$issue03Array					= array();
	$issue04Array					= array();
	$issue05Array					= array();
	$issue06Array					= array();
	$issue07Array					= array();
	$issue08Array					= array();
	$issue09Array					= array();
	$issue10Array					= array();
	$issue11Array					= array();
	$issue12Array					= array();
	$issue13Array					= array();
	$issue14Array					= array();
	$issue15Array					= array();
	$issue16Array					= array();
	$issue17Array					= array();
	$issue18Array					= array();
	$issue19Array					= array();
	$issue20Array					= array();
	$issue21Array					= array();
	$issue22Array					= array();
	$issue23Array					= array();
	$issue24Array					= array();
	$issue25Array					= array();
	$issue26Array					= array();
	$issue27Array					= array();
	$issue28Array					= array();
	$issue29Array					= array();

	
	$validWelcomeDate				= strtotime('2019-11-30 23:59');
	$validFutureDate				= strtotime('2019-12-10 23:59');
	$prevSemester					= $initializationArray['prevSemester'];
	$currentSemester				= $initializationArray['currentSemester'];
	$nextSemester					= $initializationArray['nextSemester'];
	$semesterTwo					= $initializationArray['semesterTwo'];
	$semesterThree					= $initializationArray['semesterThree'];
	$semesterFour					= $initializationArray['semesterFour'];
	$updateStudentURL				= "$siteURL/cwa-display-and-update-student-information/";
	if ($currentSemester == 'Not in Session') {
		$theSemester	= $prevSemester;
		$futureSemesters			= array($semesterTwo,$semesterThree,$semesterFour);
	} else {
		$theSemester	= $currentSemester;
		$futureSemesters			= array($nextSemester,$semesterTwo,$semesterThree,$semesterFour);
	}
	
	$allSemester					= array($theSemester=>0,$nextSemester=>1,$semesterTwo=>2,$semesterThree=>3,$semesterFour=>4);
	$findSemester					= array(0=>$theSemester,1=>$nextSemester,2=>$semesterTwo,3=>$semesterThree,4=>$semesterFour);


	
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

table{font:'Times New Roman', sans-serif;background-image:none;}

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

	function checkAdvisor($myStr,$mySemester) {

		global $wpdb, $testMode, $doDebug, $advisorTableName;

		if ($doDebug) {
			echo "At function checkAdvisor with input: $myStr and semester of $mySemester<br />";
		}

		if ($myStr == '') {
			return FALSE;
		}
		$haveAMatch					= FALSE;
		$sql						= "select * from $advisorTableName 
										where call_sign='$myStr' 
										and semester = '$mySemester' ";
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
//			$content		.= "Unable to obtain content from $advisorTableName<br />";
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
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);
					
					$haveAMatch							= TRUE;
				}
			}
		}
		if ($haveAMatch) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	

	function displayHeaders() {
		$returnData			= "<tr><th>ID</th>
									<th>Call Sign</th>
									<th>Name</th>
									<th>City</th>
									<th>State</th>
									<th>Country</th>
									<th>Phone</th>
									<th>Email</th>
								</tr><tr>
									<th>TZ</th>
									<th>Level</th>
									<th>Semester</th>
									<th>Response</th>
									<th>Status</th>
									<th>Assigned Advisor</th>
									<th>Req Date</th>
									<th>Welcome Date</th>
								</tr><tr>
									<th>Hld Reason</th>
									<th>Int Req</th>
									<th colspan='2'>First Class Choice</th>
									<th colspan='2'>Second Class Choice</th>
									<th colspan='2'>Third Class Choice</th>
								</tr>";
		return $returnData;
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



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>List All Students and Errors</h3>
							<p>Click 'Submit' to start the process.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							$testModeOption
							</table>
							<input class='formInputButton' type='submit' value='Submit' />
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}
// read and process the student table
		$sql					= "select * from $studentTableName 
									where (semester = '$currentSemester' 
										   or semester = '$nextSemester' 
										   or semester = '$semesterTwo' 
										   or semester = '$semesterThree' 
										   or semester = '$semesterFour') 
									order by semester, call_sign";
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
			$content		.= "Unable to obtain content from $studentTableName<br />";
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
				
					$totalStudents++;
					$totalThisSemester++;
					if ($doDebug) {
						echo "<br />Processing $student_call_sign<br />";
					}
					if ($student_first_class_choice == '') {
						$student_first_class_choice		= 'None';
					}
					if ($student_second_class_choice == '') {
						$student_second_class_choice		= 'None';
					}
					if ($student_third_class_choice == '') {
						$student_third_class_choice		= 'None';
					}
				
					if ($student_semester != $prev_semester) {
						if ($doDebug) {
							echo "got a new semester of $student_semester<br />";
						}
						if ($firstTime) {
							$firstTime 			= FALSE;
						} else {
							$content			.= "</table style='width:1000px;'><p>$totalThisSemester: Total students in $prev_semester semester</p>";
							$totalThisSemester	= 0;
						}
						$prev_semester			= $student_semester;
						$content				.= "<h3>Students for Semester $student_semester</h3><table>";
						$content				.= displayHeaders();
						$headerCount			= $headerInterval;
						$daysToSemester			= days_to_semester($student_semester);
						if ($doDebug) {
							echo "Days to $student_semester is $daysToSemester days<br />";
						}
					}
					$headerCount--;
					if ($doDebug) {
						echo "headerCount: $headerCount<br />";
					}
					if ($headerCount < 0) {
						if ($doDebug) {
							echo "time to display the headers<br />";
						}
						$content				.= displayHeaders();
						$headerCount			= $headerInterval;
					}
					if ($student_hold_reason_code == 'X') {
						$myStr1					= "X ($student_excluded_advisor)";
					} else {
						$myStr1					= $student_hold_reason_code;
					}
					$content			.= "<tr><td style='vertical-align:top;'>$student_ID</td>
												<td style='vertical-align:top;'><a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a></td>
												<td style='vertical-align:top;'>$student_last_name, $student_first_name</td>
												<td style='vertical-align:top;'>$student_city</td>
												<td style='vertical-align:top;'>$student_state</td>
												<td style='vertical-align:top;'>$student_country</td>
												<td style='vertical-align:top;'>$student_ph_code $student_phone</td>
												<td style='vertical-align:top;'>$student_email</td>
											</tr><tr>
												<td style='vertical-align:top;'>$student_timezone_id $student_timezone_offset</td>
												<td style='vertical-align:top;'>$student_level</td>
												<td style='vertical-align:top;'>$student_semester</td>
												<td style='vertical-align:top;'>$student_response</td>
												<td style='vertical-align:top;'>$student_student_status</td>
												<td style='vertical-align:top;'>$student_assigned_advisor</td>
												<td style='vertical-align:top;'>$student_request_date</td>
												<td style='vertical-align:top;'>$student_welcome_date</td>
											</tr><tr>
												<td style='vertical-align:top;'>$myStr1</td>
												<td style='vertical-align:top;'>$student_intervention_required</td>
												<td colspan='2' style='vertical-align:top;'>$student_first_class_choice</td>
												<td colspan='2' style='vertical-align:top;'>$student_second_class_choice</td>
												<td colspan='2 style='vertical-align:top;''>$student_third_class_choice</td>
											</tr><tr>
												<td>&nbsp;</td>
												<td colspan='7'>";
	
					$errorInfo			= '';
					// do the verifications	
					if ($doDebug) {
						echo "<br />Processing ID $student_ID ($student_call_sign)<br />";
					}

					// Check for a valid level
					if ($doDebug) {
						echo "Checking $student_level for a valid level<br />";
					}
					if (!in_array($student_level,$validLevels)) {
						$content	.= "<b>Issue 01</b> Student has invalid level of |$student_level|<br />";
						$issue01++;
						$issue01Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; Level Error or $student_level found<br />";
						}
					}

					// check for call sign existence
					if ($doDebug) {
						echo "Checking $student_call_sign for a call sign<br />";
					}
					if ($student_call_sign == "") {
						$content .= "<b>Issue 02</b> Student has no call sign.<br />";
						$issue02Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
						$issue02++;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; Call sign error found<br />";
						}
					}

					// check for valid country, country_code, and ph_code
					if ($student_country_code == 'XX' || $student_country_code == '') {
						$content	.= "<b>Issue 28</b> Country code and country missing<br />";
						$issue28Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
						$issue28++;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; Missing country code<br />";
						}
					} else {
						$countryRow	= $wpdb->get_results("select * from wpw1_cwa_country_codes 
															where country_code = '$student_country_code'");
						if ($countryRow === FALSE) {
							if ($doDebug) {
								echo "Reading wpw1_cwa_country_codes table failed<br />";
								echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
								echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
							}
						} else {
							$numRows		= $wpdb->num_rows;
							if ($numRows > 0) {
								foreach($countryRow as $thisRow) {
									$thisCountryCode	= $thisRow->country_code;
									$thisCountry		= $thisRow->country_name;
									$thisPhCode			= $thisRow->ph_code;
								}
						
								if ($student_country != $thisCountry) {
									$content		.= "<b>Issue 27</b> Country code does not agree with country<br />";
									$issue27++;
									$issue27Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
									if ($doDebug) {
										echo "&nbsp;&nbsp;&nbsp; country code and country do not match<br />";
									}
								}
								if ($student_ph_code != $thisPhCode) {
									$content		.= "<b>Issue 29</b> Student ph_code of $student_ph_code does not match country ph code of $thisPhCode<br />";
									$issue29++;
									$issue29Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
									if ($doDebug) {
										echo "Student ph_code of $student_ph_code does not match country ph code of $thisPhCode<br />";
									}
								}
							} else {
								$content			.= "<b>Issue 28</b> Country code not found in database<br />";
								$issue28Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
								$issue28++;
								$content			.= "<b>Issue 29</b> Unable to verify phone code<br />";
								$issue29Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
								$issue29++;								
							}
						}
					}

					// check for valid response
					if ($doDebug) {
						echo "Checking $student_response for a valid reponse<br />";
					}
					if ($student_response != '') {
						if (!in_array($student_response,$validResponses)) {
							$content	.= "<b>Issue 03</b> Student has an invalid Response of |$student_response|<br />";
							$issue03Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							$issue03++;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp; Resonse error found<br />";
							}
						}
					}

					// check for valid student status
					if ($doDebug) {
						echo "Checking $student_student_status for a valid student status<br />";
					}
					if ($student_student_status != '') {
						if (!in_array($student_student_status,$validStatuses)) {
							$content	.= "<b>Issue 04</b> Student has an invalid Student Status of |$student_student_status|<br />";
							$issue04Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							$issue04++;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student Status error found<br />";
							}
						}
					}

					// check for valid hold reason code
					if ($doDebug) {
						echo "Checking $student_hold_reason_code for a valid code<br />";
					}
					if ($student_hold_reason_code != '') {
						if (!in_array($student_hold_reason_code,$validHRC)) {
							$content	.= "<b>Issue 22</b> Student has an invalid Hold Reason Code of |$student_hold_reason_code|<br />";
							$issue22++;
							$issue22Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student hold reason code error found<br />";
							}
						}
					}

					// check for valid intervention required
					if ($doDebug) {
						echo "Checking $student_intervention_required for a valid status<br />";
					}
					if ($student_intervention_required != '') {
						if (!in_array($student_intervention_required,$validIntReq)) {
							$content	.= "<b>Issue 23</b> Student has an invalid Student Intervention Required of |$student_intervention_required|<br />";
							$issue23++;
							$issue23Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student Intervention Required error found<br />";
							}
						}
					}

					// check for a valid time zone
					if ($doDebug) {
						echo "Checking  $student_timezone_id and $student_timezone_offset<br />";
					}
					$timezoneOK				= TRUE;
					if ($student_timezone_id == '' || $student_timezone_id == 'Unknown') {
						$content	.= "<b>Issue 05</b> Student has an invalid timezone_id of $student_timezone_id<br />";
						$timezoneOK			= FALSE;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; timezone_id error found<br />";
						}
					}
					if ($student_timezone_offset == -99) {
						$content	.= "<b>Issue 05</b> Student has an invalid timezone_offset of $student_timezone_offset<br />";
						$timezone			= FALSE;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; timezone_offset error found<br />";
						}
					}
					if (!$timezoneOK) {
						$issue05++;
						$issue05Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
					}

					// check for a valid request date
					if ($doDebug) {
						echo "Checking $student_request_date for a valid request date<br />";
					}
					if ($student_request_date == '') {
						$content	.= "<b>Issue 06</b> Student has no Request Date<br />";
						$issue06++;
						$issue06Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp; Request date error found<br />";
						}
					} else {
						$rdate		= strtotime($student_request_date);
						if ($rdate > $myUnixTime) {
							$content	.= "<b>Issue 07</b> Student has an future Request Date of |$student_request_date|<br />";
							$issue07++;
							$issue07Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp; Request date error found<br />";
							}
						}
						$myInt		= strtotime("$student_request_date + 1 day");
						if ($myInt < $myUnixTime) {
							if ($student_welcome_date == '') {
								$content	.= "<b>Issue 08</b> Student should have a welcome Date<br />";
								$issue08++;
								$issue08Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
								if ($doDebug) {
									echo "&nbsp;&nbsp;&nbsp; Welcome dateError found<br />";
								}
							}
						}
					}

					// check student class choices
					if ($daysToSemester <= 47 || $student_no_catalog == 'Y') {
						if ($doDebug) {
							echo "Checking first class choice of $student_first_class_choice<br />";
						}
						$classChoiceTest	= FALSE;
						if ($student_first_class_choice == '' || $student_first_class_choice == 'None') {
							$content	.= "<b>Issue 09</b> Student has an empty first class choice<br />";
							$issue09++;
							$issue09Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student first class choice missing error <br />";
							}
						} else {		// has at least one class choice. Check to  see if a class is available
							$classChoiceTest	= TRUE;
						}
					} else {
						if ($doDebug) {
							echo "did not check catalog entries<br />";
						}
					}

					if ($doDebug) {
						echo "Checking that Response of $student_response and Student Status of $student_student_status are blank for future semesters<br />";
					}
					if (in_array($student_semester,$futureSemesters)) {
						// check that Response and Student Status are blank for future semesters
						if ($student_response != '' && $student_response != 'R') {
							$content	.= "<b>Issue 14</b> Student should not have a Response of |$student_response|<br />";
							$issue14Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							$issue14++;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp; Response Error found<br />";
							}
						}
						if ($student_student_status != '') {
							$content	.= "<b>Issue 15</b> Student should not have a Student Status of |$student_student_status|<br />";
							$issue15++;
							$issue15Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Student Status error found<br />";
							}
						}

						// assigned advisor should be blank in future semesters
						if ($student_assigned_advisor != '') {
							$content	.= "<b>Issue 16</b> Student's assigned advisor of |$student_assigned_advisor| should be blank<br />";
							$issue16++;
							$issue16Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
						}

						// hold reason code should be blank
						if ($student_hold_reason_code != '' && $student_hold_reason_code != 'X') {
							$content	.= "<b>Issue 17</b> Student's hold reason code of |$student_hold_reason_code| should be blank<br />";
							$issue17++;
							$issue17Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
						}
						if ($student_intervention_required != '') {
							$content	.= "<b>Issue 18</b> Student's intervention required of |$student_intervention_required| should be blank<br />";
							$issue18++;
							$issue18Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
						}
					}	

					// check advisor and assigned advisor
					if ($doDebug) {
						echo "Checking pre_assigned_advisor $student_pre_assigned_advisor and assigned advisor $student_assigned_advisor<br />";
					}
					if ($student_pre_assigned_advisor != '') {
						if (checkAdvisor($student_pre_assigned_advisor,$student_semester) == FALSE) {
							$content	.= "<b>Issue 19</b> Student has an unknown pre_assigned advisor of |$student_pre_assigned_advisor|<br />";
							$issue19++;
							$issue19Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Pre_assigned_advisor error found<br />";
							}
						} 
					}		
					if ($student_assigned_advisor != '') {
						if (checkAdvisor($student_assigned_advisor,$student_semester) == FALSE) {
							$content	.= "<b>Issue 20</b> Student has an unknown Assigned Advisor of |$student_assigned_advisor|<br />";
							$issue20++;
							$issue20Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp; Assigned advisor error found<br />";
							}
						} 
					}		

					if (!array_key_exists($student_semester,$allSemester)) {
						if ($doDebug) {
							echo "Got a strange semester of |$student_semester| for $student_call_sign ($student_ID).<br />";
						}
						$content .= "<b>Issue 21</b> Student has an unknown semester of $student_semester<br />";
						$issue21++;
						$issue21Array[]		= "<a href='$updateStudentURL?strpass=2&request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName' target='_blank'>$student_call_sign</a>";
					}
					$content	.= "<hr></tr></td>";
				}
				$content		.= "<hr></td></tr></table><p>$totalThisSemester: Total students in $student_semester semester</p>
									<p>$totalStudents: Total student records processed</p>
									<h4>Errors Encountered</h4>\n
									<table>\n
									<tr><th>Count</th>\n
										<th>Error Numbr</th>\n
										<th>Error</th></tr>\n";
				if ($doDebug) {
					echo "<br /><b>Putting Out Totals</b><br />";
				}
				for($ii=1;$ii<=29;$ii++) {
					$strSnum 		= str_pad($ii,2,'0',STR_PAD_LEFT);
					if ($doDebug) {
						echo "Processing ii: $ii; strSnum: $strSnum<br />";
					}
					$thisCount		= ${'issue' . $strSnum};
					if ($thisCount > 0) {
						if ($doDebug) {
							echo "putting out totals for issue $strSnum<br />";
						}
						$thisText		= ${'issueTxt' . $strSnum};
						$content		.= "<tr><td>$thisCount</td>\n
												<td>Issue $strSnum</td>\n
												<td>$thisText</td></tr>\n";
						if (count(${'issue' . $strSnum . 'Array'}) > 0) {
							if ($doDebug) {
								echo "have call signs to display for issue $strSnum<br ><pre>";
								print_r(${'issue' . $strSnum . 'Array'});
								echo "</pre><br />";
							}
							$content	.= "<tr><td>&nbsp;</td>\n<td>&nbsp;<td><table>\n";
							$arrayCount	= 4;
							foreach(${'issue' . $strSnum . 'Array'} as $thisValue) {
								switch ($arrayCount) {
									case 4:
										$content	.= "<tr><td style='width:100px;'>$thisValue</td>\n";
										$arrayCount--;
										if ($doDebug) {
											echo "inserted $thisValue into slot 1<br />";
										}
										break;
									case 3:
										$content	.= "<td style='width:100px;'>$thisValue</td>\n";
										$arrayCount--;
										if ($doDebug) {
											echo "inserted $thisValue into slot2<br />";
										}
										break;
									case 2:
										$content	.= "<td style='width:100px;'>$thisValue</td>\n";
										$arrayCount--;
										if ($doDebug) {
											echo "inserted $thisValue into slot 3<br />";
										}
										break;
									case 1:
										$content	.= "<td style='width:100px;'>$thisValue</td>\n";
										$arrayCount--;
										if ($doDebug) {
											echo "inserted $thisValue into slot 4<br />";
										}
										break;
									case 0:
										$content	.= "<td style='width:100px;'>$thisValue</td></tr>\n";
										$arrayCount	= 4;
										if ($doDebug) {
											echo "inserted $thisValue into slot 5<br />";
										}
										break;
								}
							}
							if ($arrayCount != 4) {
								for ($jj=$arrayCount;$jj>=0;$jj--) {
									$content	.= "<td style='width:100px;'>&nbsp;</td>\n";
									if ($doDebug) {
										echo "inserted space into slot $jj<br />";
									}

								}
								$content		.= "</tr>\n</table>\n</td></tr>\n";
							} else {
								$content		.= "</table>\n</td></tr>\n";
							}
						}
					} else {
						if ($doDebug) {
							echo "No callsigns for 'issue' . $strSnum . 'Array'<br />";
						}
					}
				}
				$content			.= "<table>\n";
			} else {
				if ($doDebug) {
					echo "No $studentTableName records found<br />";
				}
				$content	.= "<p>No $studentTableName records found</p>";
			}
		}
	}
	$thisTime 					= date('Y-m-d H:i:s');
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<br /><br /><br /><p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("List All Students|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	$content					.= "<br /><br /><br /><p>Report displayed at $thisTime.</p>";
	return $content;
}
add_shortcode ('list_all_students', 'list_all_students_func');
