function display_all_students_func() {

// modified 17feb2020 by Bob C to select all students
// mod 01mar20 - bc - add more report fields and order by level
// modified 15Jan2022 by Roland to use tables instead of pods
// Modified 29Sep22 by Roland for the new timezone setup
// Modified 15Apr23 by Roland to fix the action_log
// Modified 13Jul23 by Roland to use consolidated tables



	global $wpdb;
	
	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
		ini_set('display_errors','1');
		error_reporting(E_ALL);
		ini_set('max_execution_time',60);	
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$doDebug			= FALSE;
	$testMode			= FALSE;
	$inp_semester		= "";
	$strPass			= "1";
	$userName			= $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];
	$myCount			= 0;
	$theURL				= "$siteURL/cwa-display-all-students/";
	$jobname			= "Display All Students";
	
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
	    }
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
	
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$currentSemester	= $initializationArray['currentSemester'];
		$nextSemester	    = $initializationArray['nextSemester'];
		$semesterTwo	    = $initializationArray['semesterTwo'];
		$semesterThree  	= $initializationArray['semesterThree'];
		$optionList		= "";
		if ($currentSemester != "Not in Session") {
			$optionList	.= "<option value='$currentSemester'>$currentSemester</option>";
		}
		$optionList		.= "<option value='$nextSemester'>$nextSemester</option>";
		$optionList		.= "<option value='$semesterTwo'>$semesterTwo</option>";
		$optionList		.= "<option value='$semesterThree'>$semesterThree</option>";
		
		$content 		.= "<h3>$jobname</h3><h4>Select the Semester of Interest</h4>
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

//////////////

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}
		if ($testMode) {
			if ($doDebug) {
				echo "<b>Operating in Test Mode</b><br />";
			}
			$content					.= "<p><b>Operating in Test Mode</b></p>";
			$studentTableName			= 'wpw1_cwa_consolidated_student2';
		} else {
			$studentTableName			= 'wpw1_cwa_consolidated_student';
		}
		$content 						.= "<h3>$jobname for $inp_semester Semester</h3><table>
											<h6>Student Status Legend: </h6><p>C - Student has been Removed may be Replaced<br />N - Advisor Declined Replacement Student<br />R - Student not yet Replaced<br />
											S - Assigned Advisor not yet Verified Student<br />Y - Student Verified and taking Class</p>
											<tr><th>Student ID</th>
												<th>Call Sign</th>
												<th>Last Name</th>
												<th>First Name</th>
												<th>Email</th>
												<th>Phone</th>
												<th>City</th>
												<th>State</th></tr>
											<tr><th>Timezone Offset</th>
												<th>Level</th>
												<th>Request Date</th>
												<th>Semester</th>
												<th>Class Choice</th>
												<th>Response</th>
												<th>Student Status</th>
												<th>Promotable</th></tr>";
	
// Body of function
		$sql				= "select * from $studentTableName 
								where semester='$inp_semester' 
								order by time_zone,level,call_sign";
		$wpw1_cwa_student				= $wpdb->get_results($sql);
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
		
					$content .= "<tr><td>$student_ID</td>
									<td>$student_call_sign </td>
									<td>$student_last_name</td>
									<td>$student_first_name</td>
									<td>$student_email</td>
									<td>$student_phone</td>
									<td>$student_city</td>
									<td>$student_state</td></tr>
								<tr><td>$student_timezone_offset</td>
									<td>$student_level</td>
									<td>$student_request_date</td>
									<td>$student_semester</td>
									<td>$student_first_class_choice</td>
									<td>$student_response</td>
									<td>$student_student_status</td>
									<td>$student_promotable</td></tr>
								<tr><td colspan='8'><hr /></td></tr>";
		
		  			$myCount++;	
		  		}
		  		$content		.= "</table><br />Records Printed: $myCount<br />";
		  	} else {
		  		if ($doDebug) {
		  			echo "No records found in $studentTableName table<br />";		  			
		  		}
		  		$content		.= "No records found in $studentTableName table<br />";		  			
		  	}
		}
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('display_all_students', 'display_all_students_func');
