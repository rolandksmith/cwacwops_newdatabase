function student_and_advisor_assignments_v3_func() {

/*
 	
 	
 	modified 7Jan2020 by Roland to select semester to be displayed
	modified 12Mar2020 by Roland to eliminate duplicate records
	Modified 14Mar2020 by Roland to display advisors w/o students
	Modified 1Aug2020 by Roland to select either assigned or pre-assigned student
	modified 10Aug2020 by Roland significantly overhaul the logic
	modified 13Dec2020 by Roland to add the state to the advisor class display and 
		display the advisors with small classes
	modified 26Dec2020 by Roland to add class time to advisor class header
	modified 13Mar2021 by Roland to add large class report
	Extensively modified 9July2021 by Roland for the new formats and assignment process
	Modified 22Aug21 by Roland to
		sort advisor class slots available report by level
		do a lookback to previous semesters for Fundamental level students and indicate if the 
			student has taken a basic class previously and was promotable
		sort the unassigned student reports by class priority, add class priority to the 
			report
	Modified 29Oct2021 by Roland to select the semester rather than just show the current
		semester
	Modified 6Dec21 by Roland to add the arbitrary assignments information
	Modified 31Dec21 by Roland to move to table structure from pods
	Modified 29Oct22 by Roland for new timezone table format
	Modified 17Apr23 by Roland to fix action_log
	Modified 15July23 by Roland to use the consolidated tables
	Modified 16Aug23 by Roland ... extensively and updated to V3

*/
	global $wpdb, $advisorClassArray, $doDebug;

	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$doDebug 					= FALSE;
	$testMode					= FALSE;
	$thisVersion				= '3';
	$inp_semester				= $initializationArray['nextSemester'];
	$userName					= $initializationArray['userName'];
	$siteURL					= $initializationArray['siteurl'];
	$strPass					= "1";
	$errorCount					= 0;
	$xStudentCount				= 0;
	$defaultClassSize			= $initializationArray['defaultClassSize'];
	$inp_rsave				 	= '';
	$inp_verified				= '';
	$inp_verbose			 	= 'N';
	$theURL						= "$siteURL/cwa-student-and-advisor-assignments/";
	$studentUpdateURL			= "$siteURL/cwa-display-and-update-student-information/";	
	$studentHistoryURL		 	= "$siteURL/cwa-show-detailed-history-for-student/";	
	$advisorUpdateURL			= "$siteURL/cwa-display-and-update-advisor-information/";	
	$studentManagementURL		= "$siteURL/cwa-student-management/";
	$pastSemesters				= $initializationArray['pastSemesters'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$semesterTwo				= $initializationArray['semesterTwo'];
	$semesterThree				= $initializationArray['semesterThree'];
	$pastSemesterArray			= explode("|",$pastSemesters);
	$inp_semesterlist			= '';
	$validTestmode				= $initializationArray['validTestmode'];
	$jobname					= "Student and Advisor Assignments V$thisVersion";
	
	$levelConvert				= array('Beginner'=>1,'Fundamental'=>2,'Intermediate'=>3,'Advanced'=>4);
	$levelBack					= array(1=>'Beginner',2=>'Fundamental',3=>'Intermediate',4=>'Advanced');

	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',0);

	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "inp_debug") {
				$inp_debug		 = $str_value;
				$inp_debug		 = filter_var($inp_debug,FILTER_UNSAFE_RAW);
				if ($inp_debug == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester		 = $str_value;
				$inp_semester		 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semesterlist") {
				$inp_semesterlist		 = $str_value;
				$inp_semesterlist		 = filter_var($inp_semesterlist,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_type") {
				$inp_type		 = $str_value;
				$inp_type		 = filter_var($inp_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "level") {
				$inp_level		 = $str_value;
				$inp_level		 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "timezone") {
				$inp_timezone		 = $str_value;
				$inp_timezone		 = filter_var($inp_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_rsave") {
				$inp_rsave		 = $str_value;
				$inp_rsave		 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verified") {
				$inp_verified		 = $str_value;
				$inp_verified		 = filter_var($inp_verified,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode		 = $str_value;
				$inp_mode		 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode	= TRUE;
				}
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose		 = $str_value;
				$inp_verbose		 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
		}
	}
	if ($testMode) {
		$extMode				= 'tm';
		if ($doDebug) {
			echo "Function is under development. Using student2 and advisor2, not the production data.<br />";
		}
		$theStatement			= "<p>Running in TESTMODE using test data.</p>";
	} else {
		$extMode				= 'pd';
		$theStatement			= "";
	}


function getTheReason($strReasonCode) {
	if ($strReasonCode == 'H') {
		return "(H) Student not promotable but signed up for next class level";
	}
	if ($strReasonCode == 'Q') {
		return "(Q) Student not evaluated but signed up for next class level";
	}
	if ($strReasonCode == 'W') {
		return "(W) Student withdrew but signed up for next class level";
	}
	if ($strReasonCode == 'E') {
		return "(E) Advisor has not evaluated the student who has signed up for next class level";
	}
	if ($strReasonCode == 'A') {
		return "(A) Student hard-assigned to AC6AC";
	}
	if ($strReasonCode	== 'X') {
		return "(H) Student is being recycled for schedule issues";
	}
	return "($strReasonCode) unknown";
}	



	
// The content to be returned initially includes the special style information.
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

table {table-layout:auto;padding:5px;}

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
		if ($doDebug) {
			echo "Function starting. Building semester option list<br />";
		}
		$optionList			= "";
		$thisChecked		= "";
		if ($currentSemester != 'Not in Session') {
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$currentSemester' checked='checked'> $currentSemester<br />";
		} else {
			$thisChecked	= "checked";
		}
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$nextSemester' $thisChecked > $nextSemester<br />";		
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$semesterTwo'> $semesterTwo<br />";		
		$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$semesterThree'> $semesterThree<br />";
		$optionList		.= "----<br />";
		$myInt				= count($pastSemesterArray) - 1;
		for ($ii=$myInt;$ii>-1;$ii--) {
	 		$thisSemester		= $pastSemesterArray[$ii];
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semesterlist' value='$thisSemester'> $thisSemester<br />";
			if ($doDebug) {
				echo "Added $thisSemester to option list<br />";
			}
		}
		if ($doDebug) {
			echo "optionlist complete<br />";
		}
		
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
							<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
								<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
							<td><input type='radio' class='formInputButton' name='inp_debug' value='N' checked='checked'> Standard Output<br />
								<input type='radio' class='formInputButton' name='inp_debug' value='Y'> Turn on Debugging </td></tr>";
		} else {
			$testModeOption	= '';
		}
		$content 			.= "<h3>$jobname</h3>
								$theStatement
								<p>Select the semester of interest from the list below:</p>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data''>
								<input type='hidden' name='strpass' value='2'>
								<table style='border-collapse:collapse;'>
								<tr><td style='width:150px; vertical-align:top;'>Semester</td><td>
								$optionList
								</td></tr>
								<tr><td>Advisor Records to Include</td>
								<td><input type='radio' class='formInputButton' name='inp_type' value='assigned' checked='checked'>Assigned Advisors (use after students have been assigned)<br />
									<input type='radio' class='formInputButton' name='inp_type' value='pre-assigned'>Pre-assigned Advisors and Assigned Advisors (use before students have been assigned)</td></tr>
								<tr><td>Student Records to Include</td>
								<td><input type='radio' class='formInputButton' name='inp_verified' value='verified' checked='checked'>Verified students (use after students verified attendance)<br />
									<input type='radio' class='formInputButton' name='inp_verified' value='all'>All students except R</td></tr>
								$testModeOption
								<tr><td>Save this report to the reports achive?</td>
								<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
									<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
								<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form>";

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "At pass 2 in version $thisVersion<br />";
		}
 		
 		$inp_semester				= $inp_semesterlist;
 		
		if ($testMode) {
			$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
			$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass2';
			$studentTableName		= 'wpw1_cwa_consolidated_student2';
			$thisMode				= 'TM';
		} else {
			$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
			$advisorClassTableName	= 'wpw1_cwa_consolidated_advisorclass';
			$studentTableName		= 'wpw1_cwa_consolidated_student';
			$thisMode				= 'PD';
		}
		$doPreAssigned				= FALSE;
		$preAssignedStr				= 'FALSE';
 		if ($inp_type == 'pre-assigned') {
 			$doPreAssigned			= TRUE;
 			$preAssignedStr			= 'TRUE';
 		}
 		if ($doDebug) {
 			echo "Using inp_semester: $inp_semester<br />
					advisorTableName: $advisorTableName<br />
					advisorClassTableName: $advisorClassTableName<br />
					studentTableName: $studentTableName<br />
					doPreAssigned: $preAssignedStr<br />";
 		}
 
		$content .= "<h2>$jobname</h2>\n
					$theStatement
					<p><a href='#report1'>Go to the Advisor Assignment Information Report</a><br />\n
					<a href='#report2'>Go to the Students Assignment Information</a><br />\n
					<a href='#reportW'>Go to the Students Who Withdrew</a><br />\n
					<a href='#reportX'>Go to the Students Who Were Requested to be Replaced Report</a><br />\n
					<a href='#reportBB'>Go to All Advisors and Class Slots</a><br />\n
					<a href='#reportS'>Go to the Advisors with Small Classes Report</a><br />\n
					<a href='#reportY'>Go to the Advisor Class Slots Available Report</a><br />\n
					<a href='#report3'>Go to the Unassigned Student Information Report</a><br />\n
					<a href='#reportH'>Go to the Students on Hold Report</a><br />\n
					</p>\n";

		if ($doDebug) {
			echo "<br /><b>Starting the Advisor Assignment Information Report</b><br />\n";
		}
		
		if($doDebug) {
			echo "<br />Start of current advisor information report<br />";
		}
		$totalAdvisorClasses		= 0;
		$totalStudents				= 0;
		$badAdvisors				= array();
		$content					.= "<a name='report1'><h3>Current Advisor Assignment Information for $inp_semester</h3>\n";

 		// get each advisor
		$sql			= "select * from $advisorTableName 
							where semester='$inp_semester' 
							order by call_sign";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content		.= "Unable to obtain content from $advisorNewTableName<br />";
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
					
					// determine if we can use this advisor
					$doProceed							= TRUE;
					if ($advisor_survey_score == 6 || $advisor_survey_score == 13) {
						if ($doDebug) {
							echo "Can not use $advisor_call_sign due to survey score of $advisor_survey_score<br />";
						}
						$doProceed						= FALSE;
						$badAdvisors[]					= $advisor_call_sign;
					}
					if ($doProceed) {					
						if ($doDebug) {
							echo "Preparing advisor class display for $advisor_call_sign<br />";
						}
		
						$result				= prepare_preassigned_class_display($advisor_call_sign,
																				$inp_semester,
																				'Full', 			// full or sonly
																				FALSE,				// show verified
																				TRUE,				// include header
																				$doPreAssigned,		// do preassigned and assigned or assigned only
																				TRUE,				// doFind: whether or not to show link for finding students
																				$testMode,
																				$doDebug); 
						if ($result[0] === FALSE) {
							if ($doDebug) {
								echo "prepare_advisor for $advisor_call_sign failed<br />
									  reason: $result[1]<br />";
							}
						} else {
							$content				.= $result[1];
							$thisClasses			= $result[2];
							$thisStudentCount		= $result[3];
							$totalAdvisorClasses	= $totalAdvisorClasses + $thisClasses;
							$totalStudents			= $totalStudents + $thisStudentCount;
							if ($doDebug) {
								echo "displayed content for $advisor_call_sign<br />";
							}
						}
					}
 				}
 				$content			.= "<p>Total Advisors: $numARows<br />
 										Total Advisor Classes: $totalAdvisorClasses<br />
 										Total Assigned Students: $totalStudents</p>";
 			} else {
 				$content			.= "No advisor records found<br />";
 				if ($doDebug) {
 					echo "no advisor records found<br />";
 				}
 			}
 		}
		if($doDebug) {
			echo "<br />end of current advisor information report<br /><br />";
		}


		
//////// end of Current Advisor Assignment Information report			
	
	
//////// Start of Student Assignment Information Report		
// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Preparing Student Assignment Information Report<br />";
			}
			

			$thisBeginner			= 0;
			$thisFundamental		= 0;
			$thisIntermediate		= 0;
			$thisAdvanced			= 0;
			
			$content		.= "<a name='report2'><h3>Student Assignment Information for $inp_semester</h3></a>";

			$BeginnerArray		= array();
			$FundamentalArray	= array();
			$IntermediateArray	= array();
			$AdvancedArray		= array();	
			$studentCount		= 0;
			$verifiedCount		= 0;								

			$sql			= "select * from $studentTableName
								where semester='$inp_semester' 
								and response != 'R' 
								and promotable != 'W' 
								and (student_status = 'S' 
									or student_status = 'Y') 
								order by level, call_sign";
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
						$student_catalog_options				= $studentRow->catalog_options;
						$student_flexible						= $studentRow->flexible;
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;

						$student_last_name 						= no_magic_quotes($student_last_name);
						$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
						$studentCount++;
						if ($student_student_status == 'Y') {
							$verifiedCount++;
						}

						${$student_level . 'Array'}[]		 	= "<tr><td style='vertical-align:top;'>$student_level</td>
																		<td style='vertical-align:top;'>$student_assigned_advisor</td>
																		<td style='text-align:center;vertical-align:top;'>$student_assigned_advisor_class</td>
																		<td style='text-align:center;vertical-align:top;' style='vertical-align:top;'>$student_last_name, $student_first_name, ($student_call_sign)</td>
																		<td style='text-align:center;vertical-align:top;'>$student_timezone_offset</td>
																		<td style='text-align:center;vertical-align:top;'>$student_student_status</td>
																		<td style='vertical-align:top;'>$student_email</td>
																		<td style='vertical-align:top;'>+$student_ph_code $student_phone ($student_messaging)</td>
																		<td style='vertical-align:top;'>$student_state</td>
																		<td style='vertical-align:top;'>$student_country</td>
																		<td style='text-align:center;vertical-align:top;'>$student_promotable</td></tr>";
					}
					if (count($BeginnerArray) > 0) {
						$myCount			= count($BeginnerArray);
						$content			.= "<table style='width:1000px;'>
												<tr><th>Level</th>
													<th>Advisor</th>
													<th>Class</th>
													<th>Student</th>
													<th>TZ</th>
													<th>Status</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($BeginnerArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Assigned Beginner Students: $myCount</p>";
					}
					if (count($FundamentalArray) > 0) {
						$myCount			= count($FundamentalArray);
						$content			.= "<table style='width:1000px;'>
												<tr><th>Level</th>
													<th>Advisor</th>
													<th>Class</th>
													<th>Student</th>
													<th>TZ</th>
													<th>Status</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($FundamentalArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Assigned Fundamental Students: $myCount</p>";
					}
					if (count($IntermediateArray) > 0) {
						$myCount			= count($IntermediateArray);
						$content			.= "<table style='width:1000px;'>
												<tr><th>Level</th>
													<th>Advisor</th>
													<th>Class</th>
													<th>Student</th>
													<th>TZ</th>
													<th>Status</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($IntermediateArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Assigned Intermediate Students: $myCount</p>";
					}
					if (count($AdvancedArray) > 0) {
						$myCount			= count($AdvancedArray);
						$content			.= "<table style='width:1000px;'>
												<tr><th>Level</th>
													<th>Advisor</th>
													<th>Class</th>
													<th>Student</th>
													<th>TZ</th>
													<th>Status</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Prom</th></tr>";
						foreach($AdvancedArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Assigned Advanced Students: $myCount</p>
												<p>Total Assigned Students: $studentCount<br />
												Verified Students: $verifiedCount</p>";
					}
					unset($BeginnerArray);
					unset($FundamentalArray);
					unset($IntermediateArray);
					unset($advancedArray);
					
				} else {
					$content	.= "No assigned students found in $studentTableName table";
				}
			}	
			if ($doDebug) {
				echo "end of the student assignment information report<br />";
			}
// $doDebug = FAlSE;									
		
//////// End of Student Assignment Information Report	


//////// Start of the C&R Report
// $doDebug = TRUE;

			if ($doDebug) {
				echo "<br >Start of the C&R Report<br />";
			}
			// Prepare report of C and R Students
			$cCount			= 0;
			$rCouht			= 0;
			$content		.= "<a name='reportW'><h3>Students Who Have Withdrawn (promotable = W)</h3></a>
								<table style='width:900px;'><tr>
								<th>Name</th>
								<th>Email</th>
								<th>Phone</th>
								<th>State</th>
								<th>TZ</th>
								<th>Level</th>
								<th>Promotable</th>
								<th>Former<br />Advisor</th></tr>";
								
			$sql			= "select * from $studentTableName 
								where semester='$inp_semester' 
								and (student_status = 'Y' 
									or student_status = 'S') 
								and promotable = 'W'  
								order by call_sign";
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
						$student_catalog_options				= $studentRow->catalog_options;
						$student_flexible						= $studentRow->flexible;
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;

						$student_last_name 						= no_magic_quotes($student_last_name);
						$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
						
						if ($doDebug) {
							echo "processing student $student_call_sign with status of $student_student_status<br />";
						}

						$theLink			= "$student_last_name, $student_first_name <a href='$siteURL/cwa-display-and-update-student-information/?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_blank'>($student_call_sign)";
						$content			.= "<tr><td>$theLink</td>
													<td>$student_email</td>
													<td>$student_ph_code $student_phone</td>
													<td>$student_state</td>
													<td>$student_timezone_offset</td>
													<td>$student_level</td>
													<td>$student_promotable</td>
													<td>$student_assigned_advisor ($student_assigned_advisor_class)</td></tr>";
					}
					$content				.= "</table>
												<p>$numSRows students</p>";
				} else {
					$content				.= "<p>No students matching this criteria/p>";
				}
				if ($doDebug) {
					echo "end of the Withdrawn report<br /><br />";
				}
			}
// $doDebug = FALSE;			
//////// End of the Withdrawn Report




	
//////// Start of the C&R Report
// $doDebug = TRUE;

			if ($doDebug) {
				echo "<br >Start of the C&R Report<br />";
			}
			// Prepare report of C and R Students
			$cCount			= 0;
			$rCouht			= 0;
			$content		.= "<a name='reportX'><h3>Students with a Status of C, R, or V</h3></a>
								<table style='width:900px;'><tr>
								<th>Name</th>
								<th>Email</th>
								<th>Phone</th>
								<th>State</th>
								<th>TZ</th>
								<th>Level</th>
								<th>Status</th>
								<th>Former<br />Advisor</th></tr>";
								
			$sql			= "select * from $studentTableName 
								where semester='$inp_semester' 
								and (student_status = 'C' 
									or student_status = 'R' 
									or student_status = 'V') 
									order by student_status, call_sign";
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
						$student_catalog_options				= $studentRow->catalog_options;
						$student_flexible						= $studentRow->flexible;
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;

						$student_last_name 						= no_magic_quotes($student_last_name);
						$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
						
						if ($doDebug) {
							echo "processing student $student_call_sign with status of $student_student_status<br />";
						}

						$theLink			= "$student_last_name, $student_first_name <a href='$siteURL/cwa-display-and-update-student-information/?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_blank'>($student_call_sign)";
						$content			.= "<tr><td>$theLink</td>
													<td>$student_email</td>
													<td>$student_ph_code $student_phone</td>
													<td>$student_state</td>
													<td>$student_timezone_offset</td>
													<td>$student_level</td>
													<td style='text-align:center;'>$student_student_status</td>
													<td>$student_assigned_advisor ($student_assigned_advisor_class)</td></tr>";
					}
					$content				.= "</table>
												<p>$numSRows students</p>";
				} else {
					$content				.= "<p>No students matching this criteria/p>";
				}
				if ($doDebug) {
					echo "end of the CRV report<br /><br />";
				}
			}
// $doDebug = FALSE;			
//////// End of the C&R Report

// $doDebug = TRUE;
			// get the advisorclass information for the next few reports
			
			if ($doDebug) {
				echo "getting the advisorclass info for the next few reports<br />";
			}
			
			$advisorArray				= array();
			$sql						= "select * from $advisorClassTableName 
											where semester='$inp_semester' 
											order by advisor_call_sign, sequence";
			$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisorclass === FALSE) {
				$myError			= $wpdb->last_error;
				$myQuery			= $wpdb->last_query;
				if ($doDebug) {
					echo "Reading $advisorClassTableName table failed<br />
						  wpdb->last_query: $myQuery<br />
						  wpdb->last_error: $myError<br />";
				}
				$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
				sendErrorEmail($errorMsg);
			} else {
				$numACRows						= $wpdb->num_rows;
				if ($doDebug) {
					$myStr						= $wpdb->last_query;
					echo "ran $myStr<br />and found $numACRows rows<br />";
				}
				if ($numACRows > 0) {
					foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
						$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
						$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
						$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
						$advisorClass_sequence 					= $advisorClassRow->sequence;
						$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	
						$advisorClass_level 					= $advisorClassRow->level;
						$advisorClass_class_size 				= $advisorClassRow->class_size;
						$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
						$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
						$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
						$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
						$class_number_students					= $advisorClassRow->number_students;

						$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
						
						// see if we can use this advisor
						$doProceed				= TRUE;
						if (in_array($advisorClass_advisor_call_sign,$badAdvisors)) {
							if ($doDebug) {
								echo "can not use $advisorClass_advisor_call_sign due to bad survey score<br />";
							}
							$doProceed			= FALSE;
						}
						if ($doProceed) {						
							if ($doDebug) {
								echo "processing advisorClass $advisorClass_advisor_call_sign; class $advisorClass_sequence<br />";
							}
							$theLink			= "<a href='$siteURL/cwa-display-and-update-advisor-information/?request_type=callsign&request_info=$advisorClass_advisor_call_sign&inp_table=advisor&strpass=2' target='_blank'>($advisorClass_advisor_call_sign)</a>";
					
							$advisorArray[]		= "$advisorClass_level|$advisorClass_advisor_call_sign|$advisorClass_advisor_last_name, $advisorClass_advisor_first_name|$theLink|$advisorClass_sequence|$advisorClass_timezone_offset|$advisorClass_class_size|$advisorClass_class_schedule_days|$advisorClass_class_schedule_times|$advisorClass_class_schedule_days_utc|$advisorClass_class_schedule_times_utc|$class_number_students";
						}
					}
					if ($doDebug) {
						echo "<br />AdvisorArray:<br /><pre>";
						print_r($advisorArray);
						echo "</pre><br />";
					}
				} else {
					$content				.= "No advisor class records found";
					if ($doDebug) {
						echo "no advisorclass records found<br />";
					}
				}
			}
			if ($doDebug) {
				echo "end of getting the advisorClass information<br /><br />";
			}
				
// $doDebug = FALSE;

///////// 	Start of display all advisor slots
// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Start of display all advisor slots report<br />";
			}
			sort($advisorArray);
			$content			.= "<a name='reportBB'><h3>All Advisors and Class Slots</h3></a>
									<table>
									<tr><th>Name</th>
										<th>Sequence</th>
										<th>Level</th>
										<th>TZ</th>
										<th>Class Schedule</th>
										<th style='text-align:center;'>Class Size</th>
										<th style='text-align:center;'>Slots<br />Avail</th>
									</tr>";

			$totalSlots				= 0;
			$totalAdvisors			= 0;

			foreach($advisorArray as $thisValue) {
				$myArray								= explode("|",$thisValue);
				$advisorClass_level						= $myArray[0];
				$advisor_call_sign						= $myArray[1];
				$advisor_name 							= $myArray[2];
				$theLink								= $myArray[3];
				$advisorClass_sequence					= $myArray[4];
				$advisorClass_timezone_offset			= $myArray[5];
				$advisorClass_class_size				= $myArray[6];
				$advisorClass_class_schedule_days		= $myArray[7];
				$advisorClass_class_schedule_times		= $myArray[8];
				$advisorClass_class_schedule_days_utc	= $myArray[9];
				$advisorClass_class_schedule_times_utc	= $myArray[10];
				$class_number_students					= $myArray[11];
				
				if ($doDebug) {
					echo "<br />Processing $advisor_call_sign<br />
							advisorName: $advisor_name<br />
							theLink: $theLink<br />
							advisorClass_sequence: $advisorClass_sequence<br />
							advisorClass_tiezone_offset: $advisorClass_timezone_offset<br />
							advisorClass_level: $advisorClass_level<br />
							advisorClass_class_size: $advisorClass_class_size<br />
							advisorClass_class_schedule_days: $advisorClass_class_schedule_days<br />
							advisorClass_class_schedule_times: $advisorClass_class_schedule_times <br />
							advisor$advisorClass_class_schedule_days_utc<br />
							advisorCLass_class_schedule_times_utc: $advisorClass_class_schedule_times_utc<br />
							class_number_students: $class_number_students<br />";
				}
				

				$slotsAvailable			= 0;
				if ($advisorClass_class_size > 0) {
					$slotsAvailable		= $advisorClass_class_size - $class_number_students;
					if ($slotsAvailable > 0) {
						$totalSlots			= $totalSlots + $slotsAvailable;
					}
				} else {
					$slotsAvailable			= 0;
				}
				
				$content	.= "<tr><td style='vertical-align:top;'>$theLink</td>
									<td style='vertical-align:top;'>$advisorClass_sequence</td>
									<td style='vertical-align:top;'>$advisorClass_level</td> 
									<td style='vertical-align:top;'>$advisorClass_timezone_offset</td>
									<td style='vertical-align:top;'>$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc<br />$advisorClass_class_schedule_times $advisorClass_class_schedule_days Local</td>
									<td style='text-align:center;vertical-align:top;'>$advisorClass_class_size</td>
									<td style='text-align:center;vertical-align:top;'>$slotsAvailable</td></tr>";
				$totalAdvisors++;
				
			}
			$content		.= "</table>
								<p>Total Advisors: $totalAdvisors<br />
								Total slots Available: $totalSlots</p>";
// $doDebug = FALSE;

///////// 	End of display available advisor slots



/////////		Display advisors with small class sizes

// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Start of advisors with small class sizes report<br />";
			}	
			$advisorCount			= 0;
			$totalAvailable			= 0;
			$content			.= "<a name='reportS'><h3>Advisor with Small Classes</h3></a>
									<p><table>
									<tr><th>Name</th>
										<th>Sequence</th>
										<th>Level</th>
										<th>TZ</th>
										<th>Class Schedule</th>
										<th style='text-align:center;'>Class Size</th>
										<th style='text-align:center;'>Slots<br />Avail</th>
									</tr>";
			$totalSlots				= 0;

			foreach($advisorArray as $thisValue) {
				$myArray								= explode("|",$thisValue);
				$advisorClass_level						= $myArray[0];
				$advisor_call_sign						= $myArray[1];
				$advisor_name 							= $myArray[2];
				$theLink								= $myArray[3];
				$advisorClass_sequence					= $myArray[4];
				$advisorClass_timezone_offset			= $myArray[5];
				$advisorClass_class_size				= $myArray[6];
				$advisorClass_class_schedule_days		= $myArray[7];
				$advisorClass_class_schedule_times		= $myArray[8];
				$advisorClass_class_schedule_days_utc	= $myArray[9];
				$advisorClass_class_schedule_times_utc	= $myArray[10];
				$class_number_students					= $myArray[11];
				

				$slotsAvailable			= 0;
				if ($advisorClass_class_size > 0) {
					if ($class_number_students <= $advisorClass_class_size) {
						$slotsAvailable		= $advisorClass_class_size - $class_number_students;
						$totalSlots			= $totalSlots + $slotsAvailable;
					} else {
						$slotsAvailable		= 0;
					}
				} else {
					$slotsAvailable			= 0;
				}
						
				if ($class_number_students < 4) {
					$advisorCount++;
					$slotsAvailable		= $advisorClass_class_size - $class_number_students;
					$totalAvailable		= $totalAvailable + $slotsAvailable;
//					$theLink	= "$advisor_name <a href='$studentManagementURL?strpass=81&inp_advisor_callsign=$advisor_call_sign&inp_advisorClass=$advisorClass_sequence&inp_search=standard&inp_mode=$thisMode' target='_blank'>($advisor_call_sign)</a>";
					$content	.= "<tr><td style='vertical-align:top;'>$advisor_call_sign</td>
										<td style='vertical-align:top;'>$advisorClass_sequence</td>
										<td style='vertical-align:top;'>$advisorClass_level</td> 
										<td style='vertical-align:top;'>$advisorClass_timezone_offset</td>
										<td style='vertical-align:top;'>$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc<br />$advisorClass_class_schedule_times $advisorClass_class_schedule_days Local</td>
										<td style='text-align:center;vertical-align:top;'>$advisorClass_class_size</td>
										<td style='text-align:center;vertical-align:top;'>$slotsAvailable</td></tr>";
								
				}
			}
			$content		.= "</table>
								<p>$advisorCount: Small Classes<br />
								$totalAvailable: Slots available in small classes</p>";
									
									
// $doDebug = FALSE;
/////////		End of Display advisors with small class sizes


///////// 	Start of display available advisor slots
// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Start of Advisor Class Slots Available report<br />";
			}	
			$advisorCount			= 0;
			$totalAvailable			= 0;
			$advisorSortArray		= array();
			foreach($advisorArray as $thisValue) {
				$myArray								= explode("|",$thisValue);
				$advisorClass_level						= $myArray[0];
				$advisor_call_sign						= $myArray[1];
				$advisor_name 							= $myArray[2];
				$theLink								= $myArray[3];
				$advisorClass_sequence					= $myArray[4];
				$advisorClass_timezone_offset			= $myArray[5];
				$advisorClass_class_size				= $myArray[6];
				$advisorClass_class_schedule_days		= $myArray[7];
				$advisorClass_class_schedule_times		= $myArray[8];
				$advisorClass_class_schedule_days_utc	= $myArray[9];
				$advisorClass_class_schedule_times_utc	= $myArray[10];
				$class_number_students					= $myArray[11];
				$slotsAvailable		= $advisorClass_class_size - $class_number_students;
				if ($slotsAvailable > 0) {
					$advisorSortArray[] 	= "$advisorClass_level|$advisorClass_class_schedule_times_utc|$advisorClass_class_schedule_days_utc|$advisor_call_sign|$advisor_name|$theLink|$advisorClass_sequence|$advisorClass_timezone_offset|$advisorClass_class_size|$advisorClass_class_schedule_days|$advisorClass_class_schedule_times|$class_number_students";
 		 			//                          0                  1                                      2                                      3                  4              5        6                     7                             8                        9                                 10                                  11
				}
			}
// echo "advisorSortArray<br /><pre>";
// print_r($advisorSortArray);
// echo "</pre><br />";			
			
			sort($advisorSortArray);


			$content			.= "<a name='reportY'><h3>Advisor Class Slots Available</h3></a>
									<p><table>
									<tr><th>Name</th>
										<th>Sequence</th>
										<th>Level</th>
										<th>TZ</th>
										<th>Class Schedule</th>
										<th style='text-align:center;'>Class Size</th>
										<th style='text-align:center;'>Slots<br />Avail</th>
									</tr>";
// echo "<br />AdvisorSortArray values:<br />";
			foreach($advisorSortArray as $thisValue) {
				$myArray								= explode("|",$thisValue);
				$advisor_call_sign						= $myArray[3];
				$advisor_name 							= $myArray[4];
				$theLink								= $myArray[5];
				$advisorClass_sequence					= $myArray[6];
				$advisorClass_timezone_offset			= $myArray[7];
				$advisorClass_level						= $myArray[0];
				$advisorClass_class_size				= $myArray[8];
				$advisorClass_class_schedule_days		= $myArray[9];
				$advisorClass_class_schedule_times		= $myArray[10];
				$advisorClass_class_schedule_days_utc	= $myArray[2];
				$advisorClass_class_schedule_times_utc	= $myArray[1];
				$class_number_students					= $myArray[11];
				
// echo "advisorClassclass_schedule_days_utc: $advisorClass_class_schedule_days_utc<br />";
				$slotsAvailable			= 0;
				if ($advisorClass_class_size > 0) {
					if ($class_number_students <= $advisorClass_class_size) {
						$slotsAvailable		= $advisorClass_class_size - $class_number_students;
						$totalSlots			= $totalSlots + $slotsAvailable;
					} else {
						$slotsAvailable		= 0;
					}
				} else {
					$slotsAvailable			= 0;
				}
					
				if ($slotsAvailable > 0) {
					$advisorCount++;
					$totalAvailable		= $totalAvailable + $slotsAvailable;
					$theLink	= "$advisor_name <a href='$studentManagementURL?strpass=81&inp_advisor_callsign=$advisor_call_sign&inp_advisorClass=$advisorClass_sequence&inp_search=standard&inp_mode=$thisMode' target='_blank'>($advisor_call_sign)</a>";
					$content	.= "<tr><td style='vertical-align:top;'>$theLink</td>
										<td style='vertical-align:top;'>$advisorClass_sequence</td>
										<td style='vertical-align:top;'>$advisorClass_level</td> 
										<td style='vertical-align:top;'>$advisorClass_timezone_offset</td>
										<td style='vertical-align:top;'>$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc<br />$advisorClass_class_schedule_times $advisorClass_class_schedule_days Local</td>
										<td style='text-align:center;vertical-align:top;'>$advisorClass_class_size</td>
										<td style='text-align:center;vertical-align:top;'>$slotsAvailable</td></tr>";
								
				}
			}
			$content		.= "</table>
								<p>$advisorCount: Classes with slots available<br />
								$totalAvailable: Slots available</p>";
									
									
// $doDebug = FALSE;
			
///////// 	End of display available advisor slots



			
//////// Start of Unassigned Student Information Report				


// $doDebug = TRUE;
			if ($doDebug) {
				echo "<br />Start of Unassigned Student Information Report <br />";
			}

			$content		.= "<a name='report3'><h3>Unassigned Student Information for $inp_semester</h3></a>";

			$BeginnerArray		= array();
			$FundamentalArray	= array();
			$IntermediateArray	= array();
			$AdvancedArray		= array();
			$totalUnassigned	= 0;									

			$sql			= "select * from $studentTableName
								where semester='$inp_semester' 
								and response = 'Y' 
								and student_status = '' 
								and intervention_required != 'H' 
								order by request_date DESC, level, call_sign";
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
						$student_catalog_options				= $studentRow->catalog_options;
						$student_flexible						= $studentRow->flexible;
						$student_date_created 					= $studentRow->date_created;
						$student_date_updated			  		= $studentRow->date_updated;

						$student_last_name 						= no_magic_quotes($student_last_name);
						$student_excluded_advisor_array			= explode("|",$student_excluded_advisor);
						$totalUnassigned++;
						
						$theLink		= "<a href='$siteURL/cwa-student-management/?strpass=70&inp_student_callsign=$student_call_sign&inp_mode=$inp_mode' target='_blank'>$student_call_sign</a> (<a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_Blank'>UPD</a>)";
						$reqDate		= substr($student_request_date,0,10);
						
						${$student_level . 'Array'}[]		 	= "<tr><td style='vertical-align:top;'>$theLink</td>
																		<td style='vertical-align:top;'>$student_last_name, $student_first_name</td>
																		<td style='vertical-align:top;'>$student_first_class_choice_utc<br />
																										$student_second_class_choice_utc<br />
																										$student_third_class_choice_utc</td>
																		<td style='text-align:center;vertical-align:top;'>$student_timezone_offset</td>
																		<td style='vertical-align:top;'>$student_email</td>
																		<td style='vertical-align:top;'>+$student_ph_code $student_phone ($student_messaging)</td>
																		<td style='vertical-align:top;'>$student_state</td>
																		<td style='vertical-align:top;'>$student_country</td>
																		<td style='vertical-align:top;'>$reqDate</td>
																		<td style='text-align:center;vertical-align:top;'>$student_promotable</td></tr>";
					}
					if (count($BeginnerArray) > 0) {
						$myCount			= count($BeginnerArray);
						$content			.= "<h4>Beginner Unassigned</h4>
												<table style='width:1000px;'>
												<tr><th>Call Sign</th>
													<th>Student</th>
													<th style='width:180px;'>Choices UTC</th>
													<th>TZ</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Req Date</th>
													<th>Prom</th></tr>";
						foreach($BeginnerArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Unassigned Beginner Students: $myCount<br />
												<i>Clicking on the call sign link will open 'Find Possible Classes for a Student' in a new tab</i></p>";
					}
					if (count($FundamentalArray) > 0) {
						$myCount			= count($FundamentalArray);
						$content			.= "<h4>Fundamental Unassigned</h4>
												<table style='width:1000px;'>
												<tr><th>Call Sign</th>
													<th>Student</th>
													<th style='width:180px;'>Choices UTC</th>
													<th>TZ</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Req Date</th>
													<th>Prom</th></tr>";
						foreach($FundamentalArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Unassigned Fundamental Students: $myCount<br />
												<i>Clicking on the call sign link will open 'Find Possible Classes for a Student' in a new tab</i></p>";
					}
					if (count($IntermediateArray) > 0) {
						$myCount			= count($IntermediateArray);
						$content			.= "<h4>Intermediate Unassigned</h4>
												<table style='width:1000px;'>
												<tr><th>Call Sign</th>
													<th>Student</th>
													<th style='width:180px;'>Choices UTC</th>
													<th>TZ</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Req Date</th>
													<th>Prom</th></tr>";
						foreach($IntermediateArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Unassigned Intermediate Students: $myCount<br />
												<i>Clicking on the call sign link will open 'Find Possible Classes for a Student' in a new tab</i></p>";
					}
					if (count($AdvancedArray) > 0) {
						$myCount			= count($AdvancedArray);
						$content			.= "<h4>Advanced Unassigned</h4>
												<table style='width:1000px;'>
												<tr><th>Call Sign</th>
													<th>Student</th>
													<th style='width:180px;'>Choices UTC</th>
													<th>TZ</th>
													<th>Email</th>
													<th>Phone</th>
													<th>State</th>
													<th>Country</th>
													<th>Req Date</th>
													<th>Prom</th></tr>";
						foreach($AdvancedArray as $thisValue) {
							$content		.= $thisValue;
						}
						$content			.= "</table>
												<p>Total Unassigned Advanced Students: $myCount<br />
												<i>Clicking on the call sign link will open 'Find Possible Classes for a Student' in a new tab</i></p>
												<p>Total Overall Unassigned Students: $totalUnassigned";
					}
					unset($BeginnerArray);
					unset($FundamentalArray);
					unset($IntermediateArray);
					unset($advancedArray);
					
				} else {
					$content	.= "No assigned students found in $studentTableName table";
				}
			}	
// $doDebug = FAlSE;									
		


//////// End of Unassigned Student Information Report				
	
	


////////////	 students on hold report
			if ($doDebug) {
				echo "<br />Doing Students On Hold Report<br />";
			}
				
		
			$content		.= "<a name='reportH'><h3>Students On Hold Report</h3></a>
								<table>
								<tr><th>Student</th>
									<th>Level</th>
									<th>Response</th>
									<th>Status</th>
									<th>Hold Reason</th>
									<th>Action Log</th></tr>";
			$sql			= "SELECT * FROM `wpw1_cwa_consolidated_student` 
								WHERE intervention_required = 'H' 
								and semester = '$inp_semester' 
								order by call_sign";
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
						$student_level  						= $studentRow->level;
						$student_response  						= strtoupper($studentRow->response);
						$student_student_status  				= strtoupper($studentRow->student_status);
						$student_action_log  					= $studentRow->action_log;
						$student_hold_reason_code  				= $studentRow->hold_reason_code;

						$student_last_name 						= no_magic_quotes($student_last_name);

						$myStr				= getTheReason($student_hold_reason_code);
						$theLink			= "$student_last_name, $student_first_name <a href='$studentUpdateURL?request_type=callsign&request_info=$student_call_sign&request_table=$studentTableName&strpass=2' target='_blank'>($student_call_sign)</a>";
						$holdLink			= "<a href='$studentManagementURL?strpass=26&inp_student_callsign=$student_call_sign&inp_mode=$inp_mode&inp_verbose=$inp_verbose' target='_blank'>Remove Hold</a>";
						$findLink			= "<a href='$studentManagementURL?strpass=71&inp_student_callsign=$student_call_sign&inp_choice=option1&inp_mode=$inp_mode&inp_verbose=$inp_verbose' target='_blank'>Find Classes</a>";
						$thisLog			= formatActionLog($student_action_log);
						$content			.= "<tr><td style='vertical-align:top;'>$theLink<br />
														$holdLink<br />
														$findLink</td>
													<td style='vertical-align:top;'>$student_level</td>
													<td style='vertical-align:top;'>$student_response</td>
													<td style='vertical-align:top;'>$student_student_status</td>
													<td style='vertical-align:top;'>$myStr</td>
													<td style='vertical-align:top;'>$thisLog</td></tr>";
					}
					$content				.= "</table>
												<p><em>Clicking on the student call sign will open the Display and Update Student function</em></p>";
				} else {
					$content				.= "<tr><td colspan='6'>No students on hold<?td></tr></table>";
				}
			}
			if ($doDebug) {
				echo "end of the students on hold report<br />";
			}
//////////////	end of students on hold report

	}

	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>V$thisVersion Prepared at $thisTime</p>";
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
	$result			= write_joblog_func("jobname|$nowDate|$nowTime|$userName|$thisStr|Time|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as $jobname<br />";
		}
		$storeResult	= storeReportData_func($jobname,$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports pod as $storeResult";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}
	return $content;

}
add_shortcode ('student_and_advisor_assignments_v3', 'student_and_advisor_assignments_v3_func');
