function list_students_for_a_semester_func() {

// Add Status Legend - 19 Dec 19 - 00:03z bc
// turn off Y status - 14feb20 bc
// turned Y status back on - 17feb20 bc
// add word status to student status in title - bc 18aug20	
// modified 18Jan2022 to use tables rather than pods
// added utc first class  /  bc 19Apr22 18:51 
// modified 1Oct22 by Roland for new timezone process 	
// Modified 16Apr23 by Roland to fix action_log
// Modified 13Jul23 by Roland to use consolidated tables
	
	global $wpdb;
	
	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];

	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	$doDebug						= FALSE;
	$testMode						= FALSE;
	$strPass						= "1";
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
			}
		}
	}
	
	$totalUnassigned				= 0;
	$totalStudents					= 0;
	$totalConfirmed					= 0;
	$totalReplaced					= 0;
	$totalDeclined					= 0;
	$totalSelected					= 0;
	$totalOnHold					= 0;
	$theURL							= "$siteURL/cwa-list-students-for-a-semester/";
	
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
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$currentSemester	= $initializationArray['currentSemester'];
		$nextSemester		= $initializationArray['nextSemester'];
		$semesterTwo		= $initializationArray['semesterTwo'];
		$semesterThree		= $initializationArray['semesterThree'];
		$optionList		= "";
		if ($currentSemester != "Not in Session") {
			$optionList	.= "<option value='$currentSemester'>$currentSemester</option>";
		}
		$optionList		.= "<option value='$nextSemester'>$nextSemester</option>";
		$optionList		.= "<option value='$semesterTwo'>$semesterTwo</option>";
		$optionList		.= "<option value='$semesterThree'>$semesterThree</option>";
		
		$content 		.= "<h3>Select the Semester of Interest</h3>
<p>Select from the list below:</p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data''>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Semester</td><td>
<select name='inp_semester' required='required' size='5' autofocus class='formSelect'>
$optionList
</select></td></tr>
<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form>";
		return $content;
	} elseif ("2" == $strPass) {

		$currentSemester	= $initializationArray['currentSemester'];
		$nextSemester		= $initializationArray['nextSemester'];
		$semesterTwo		= $initializationArray['semesterTwo'];
		$theSemester		= $currentSemester;
		if ($theSemester == 'Not in Session') {
			$theSemester	= $nextSemester;
		}

	
	if ($testMode) {
		$studentTableName	= 'wpw1_cwa_consolidated_student2';
		$advisorTableName	= 'wpw1_cwa_consolidated_advisor2';
		echo "Function is under development.<br />";
		$content .= "Function is under development.<br />";
	} else {
		$studentTableName = 'wpw1_cwa_consolidated_student';
		$advisorTableName	= 'wpw1_cwa_consolidated_advisor';
	}
	
	
	
		if ($inp_semester == $theSemester) {
			$sql		= "select * from $studentTableName 
							where semester='$inp_semester' 
							order by response, 
							         assigned_advisor, 
							         timezone_offset, 
							         level, 
							         student_status";
		} else {
			$sql		= "select * from $studentTableName where 
							semester='$inp_semester' 
							order by response, 
									 assigned_advisor, 
									 level, 
									 timezone_offset, 
									 student_status";
		}
		$wpw1_cwa_student				= $wpdb->get_results($sql);
		if ($doDebug) {
			echo "Reading $studentTableName table<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			if ($wpdb->last_error != '') {
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		}
		if ($wpw1_cwa_student !== FALSE) {
			$numSRows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "retrieved $numSRows rows from $studentTableName table<br />";
			}
			if ($numSRows > 0) {
				if ($doDebug) {
					echo "found $numSRows rows in $studentTableName<br />";
				}
				$content .= "<h3>Student Status for $inp_semester with Response of Y or R</h3>
<h6>Student Status Legend: </h6>
<p>C - Student has been Removed may be Replaced<br />
N - Advisor Declined Replacement Student<br />
R - Student not yet Replaced<br />
S - Assigned Advisor not yet Verified Student<br />
Y - Student Verified and taking Class</p>
<table>
<tr><th>ID</th>
	<th>Call Sign</th>
	<th>Name</th>
	<th>Level</th>
	<th>Time<br />Zone</th>
	<th>Req Date</th>
	<th>Response</th>
	<th>Student Status</th>
	<th>Intervention<br />Required</th>
	<th>Semester</td>
	<th>UTC First Choice</th>
	</tr>";
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_ph_code						= $studentRow->ph_code;
					$student_phone  						= $studentRow->phone;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country_code					= $studentRow->country_code;
					$student_country  						= $studentRow->country;
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
					$student_abandoned  				= $studentRow->abandoned;
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
	
					$student_level							= substr($student_level,0,3);
			
			
					if ($doDebug) {
						echo "Processing call sign $student_call_sign with response $student_response and status $student_student_status<br />";
					}
					$totalStudents++;
					$theStatus					= '';
					if ($student_student_status == 'S') {
						$theStatus				= "Selected but not confirmed";
						$totalSelected++;
					} elseif ($student_student_status == 'Y') {
						$theStatus				= "Selected and Attending";
						$totalConfirmed++;
					} elseif ($student_student_status == 'N') {
						$theStatus				= "Selected and declined";
						$totalDeclined++;
					} elseif ($student_student_status == 'R' || $student_student_status == 'C') {
						$theStatus				= "Replacement Requested";
						$totalReplaced++;
					}
					if ($student_response == 'Y' and $student_student_status == '') {
						$totalUnassigned++;
						$student_assigned_advisor	= "unassigned";
					}
					if ($student_intervention_required == 'H') {
						$totalOnHold++;
					}
			
					$content					.= "
<tr><td>$student_ID</td>
	<td>$student_call_sign</td>
	<td>$student_last_name, $student_first_name</td>
	<td>$student_level</td>
	<td>$student_timezone_id $student_timezone_offset</td>
	<td>$student_request_date</td>
	<td>$student_response</td>
	<td>$student_student_status</td>
	<td>$student_intervention_required</td>
	<td>$student_semester</td>
	<td>$student_first_class_choice_utc</td>
	</tr>";
				}
				$content .= "</table><p>Statistics:<br />
$totalStudents Total Students<br />
$totalUnassigned Total unassigned students<br />
$totalSelected	Total selected but not confirmed<br />
$totalConfirmed Selected and confirmed<br />
$totalDeclined Selected and declined<br />
$totalReplaced Replacements Requested<br />
$totalOnHold Students On Hold</p>";
			} else {
				if ($doDebug) {
					echo "No records found in $studentTableName table<br >";
				}
				$content	.= "No records found in $studentTableName table<br >";
			}
		} else {
			if ($doDebug) {
				echo "Either $studentTableName not found or bad $sql 01<br />";
			}
			$content		.= "Either $studentTableName not found or bad $sql 01<br />";
		}



		$thisTime 					= date('Y-m-d H:i:s');
		$content					.= "<br /><br /><br /><p>Report displayed at $thisTime.</p>";
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
		$result			= write_joblog_func("List Students for a Semester|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
		if ($result == 'FAIL') {
			$content	.= "<p>writing to joblog.txt failed</p>";
		}
			return $content;
	}
}
add_shortcode ('list_students_for_a_semester', 'list_students_for_a_semester_func');
