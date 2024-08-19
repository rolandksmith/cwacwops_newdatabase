function recover_deleted_record_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$isConsolidated					= TRUE;
	$initializationArray 			= data_initialization_func();
	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$versionNumber		= '1';
	
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
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-recover-deleted-record/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Recover Deleted Record V$versionNumber";

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
			if ($str_key		== 'inp_callsign') {
				$inp_callsign	= strtoupper($str_value);
				$inp_callsign	= filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_table') {
				$inp_table	= $str_value;
				$inp_table	= filter_var($inp_table,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_id') {
				$inp_id	= $str_value;
				$inp_id	= filter_var($inp_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_tableName') {
				$inp_tableName	= $str_value;
				$inp_tableName	= filter_var($inp_tableName,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'tableName') {
				$tableName	= $str_value;
				$tableName	= filter_var($tableName,FILTER_UNSAFE_RAW);
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




	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<p>Recover a record from the deleted tables back to the original table.<br />
1. Enter the callsign to be recovered<br />
2. Select the record type to use and submit<br /><br />
Program will show all deleted records for that callsign and record type.<br />
Select the record to be recovered and submit.<br /><br />
The requested record will be removed from the deleted table and written to the appropriate 
data table.</p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td style='width:150px;vertical-align:top;'><b>Callsign</b></td>
	<td><input type='text' class='formInputText' name='inp_callsign' size='50' maxlength='100'></td></tr>
<tr><td style='width:150px;vertical-align:top;'><b>Table Type</b></td>
	<td><input type='radio' class='formInputButton' name='inp_table' value='advisor' >Advisor Record<br />
		<input type='radio' class='formInputButton' name='inp_table' value='advisorclass' >Advisor Class Record<br />
		<input type='radio' class='formInputButton' name='inp_table' value='student' >Student Record</td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "<br />At pass 2 with inp_callsign: $inp_callsign and inp_table: $inp_table<br />";
		}	
		$doProceed				= TRUE;
		if ($inp_callsign == '') {
			if ($doDebug) {
				echo "invalid inp_callsign entered<br />";
			}
			$content			.= "The callsign is required<br/>";
			$doProceed			= FALSE;
		}
		
		// determine which file type to use
		if ($testMode) {
			$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
			if ($doDebug) {
				echo "<p><strong>Operating in Test Mode.</strong></p>";
			}
			$extMode					= 'tm';
			if ($isConsolidated) {
				if ($inp_table == 'advisor') {
					$tableName			= "wpw1_cwa_consolidated_advisor2";			
					$tableDeleted		= "wpw1_cwa_advisor_deleted2";
					$sql		= "select * from $tableDeleted 
									where call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} elseif ($inp_table == 'advisorclass') {
					$tableName			= "wpw1_cwa_consolidated_advisorclass2";				
					$tableDeleted		= "wpw1_cwa_advisorclass_deleted2";
					$sql		= "select * from $tableDeleted 
									where advisor_call_sign = '$inp_callsign' 
									order by date_created DESC";
				} elseif ($inp_table == 'student') {
					$tableName			= "wpw1_cwa_consolidated_student2";
					$tableDeleted		= "wpw1_cwa_student_deleted2";
					$sql		= "select * from $tableDeleted 
									where call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} else {
					if ($doDebug) {
						echo "Invalid inp_table information<br />";
					}
					$content			.= "The table type is required<br />";
					$doProceed			= FALSE;
				}
			} else {
				if ($inp_table == 'advisor') {
					$tableName			= "wpw1_cwa_advisor2";			
					$tableDeleted		= "wpw1_cwa_advisor_deleted2";
					$sql		= "select * from $tableDeleted 
									where call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} elseif ($inp_table == 'advisorclass') {
					$tableName			= "wpw1_cwa_advisorclass2";				
					$tableDeleted		= "wpw1_cwa_advisorclass_deleted2";
					$sql		= "select * from $tableDeleted 
									where advisor_call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} elseif ($inp_table == 'student') {
					$tableName			= "wpw1_cwa_student2";
					$tableDeleted		= "wpw1_cwa_student_deleted2";
					$sql		= "select * from $tableDeleted 
									where call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} else {
					if ($doDebug) {
						echo "Invalid inp_table information<br />";
					}
					$content			.= "The table type is required<br />";
					$doProceed			= FALSE;
				}
			}
		} else {
			$extMode					= 'pd';
			if ($isConsolidated) {
				if ($inp_table == 'advisor') {
					$tableName			= "wpw1_cwa_consolidated_advisor";			
					$tableDeleted		= "wpw1_cwa_advisor_deleted";
					$sql		= "select * from $tableDeleted 
									where call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} elseif ($inp_table == 'advisorclass') {
					$tableName			= "wpw1_cwa_consolidated_advisorclass";				
					$tableDeleted		= "wpw1_cwa_advisorclass_deleted";
					$sql		= "select * from $tableDeleted 
									where advisor_call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} elseif ($inp_table == 'student') {
					$tableName			= "wpw1_cwa_consolidated_student";
					$tableDeleted		= "wpw1_cwa_student_deleted";
					$sql		= "select * from $tableDeleted 
									where call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} else {
					if ($doDebug) {
						echo "Invalid inp_table information<br />";
					}
					$content			.= "The table type is required<br />";
					$doProceed			= FALSE;
				}
			} else {
				if ($inp_table == 'advisor') {
					$tableName			= "wpw1_cwa_advisor";			
					$tableDeleted		= "wpw1_cwa_advisor_deleted";
					$sql		= "select * from $tableDeleted 
									where call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} elseif ($inp_table == 'advisorclass') {
					$tableName			= "wpw1_cwa_advisorclass";				
					$tableDeleted		= "wpw1_cwa_advisorclass_deleted";
					$sql		= "select * from $tableDeleted 
									where advisor_call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} elseif ($inp_table == 'student') {
					$tableName			= "wpw1_cwa_student";
					$tableDeleted		= "wpw1_cwa_student_deleted";
					$sql		= "select * from $tableDeleted 
									where call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} else {
					if ($doDebug) {
						echo "Invalid inp_table information<br />";
					}
					$content			.= "The table type is required<br />";
					$doProceed			= FALSE;
				}
			}
		}
		
		if ($doDebug) {
			echo "using tableName $tableName<br />
				  SQL: $sql<br /><br />";
		}
	
		$result				= $wpdb->get_results($sql);
		if ($result === FALSE) {
			$myError		= $wpdb->last_error;
			$myQuery		= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $tableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname reading $tableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
			$content		.= "Unable to obtain content from tableName for $inp_callsign<br />";
		} else {
			$numRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numRows rows<br />";
			}
			if ($numRows > 0) {
				$content			.= "<h3>$jobname for $inp_callsign</h3>
										<p>Select the record to be recovered</p>
										<form method='post' action='$theURL' 
										name='selection_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='3'>
										<input type='hidden' name='inp_callsign' value='$inp_callsign'>
										<input type='hidden' name='inp_table' value='$inp_table'>
										<input type='hidden' name='tableName' value='$tableName'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<table>
										<tr><th>Select?</th>
											<th>Callsign</th>
											<th>Name</th>
											<th>Semester</th>
											<th>Level</th>
											<th>Sequence</th></tr>";
		
				// get the deleted records according to the table type
				if ($inp_table == 'advisor') {
					if ($doDebug) {
						echo "working with deleted advisor records<br />";
					}
					foreach ($result as $advisorRow) {
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
						$content			.= "<tr><td><input type='radio' class='formInputButton' name='inp_id' value='$advisor_ID' required>$advisor_ID</td>
													<td>$advisor_call_sign</td>
													<td>$advisor_last_name, $advisor_first_name</td>
													<td>$advisor_semester</td>
													<td>--</td>
													<td>--</td></tr>";
					}
				} elseif ($inp_table == 'advisorclass') {
					if ($doDebug) {
						echo "working with deleted advisorClass records<br />";
					}
					foreach ($result as $advisorClassRow) {
						$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
						$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
						$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
						$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
						$advisorClass_sequence 					= $advisorClassRow->sequence;
						$advisorClass_semester 					= $advisorClassRow->semester;
						$advisorClass_timezone 					= $advisorClassRow->time_zone;
						$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
						$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
						$advisorClass_level 					= $advisorClassRow->level;
						$advisorClass_class_size 				= $advisorClassRow->class_size;
						$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
						$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
						$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
						$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
						$advisorClass_action_log 				= $advisorClassRow->action_log;
						$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
						$advisorClass_date_created				= $advisorClassRow->date_created;
						$advisorClass_date_updated				= $advisorClassRow->date_updated;
						$advisorClass_student01 				= $advisorClassRow->student01;
						$advisorClass_student02 				= $advisorClassRow->student02;
						$advisorClass_student03 				= $advisorClassRow->student03;
						$advisorClass_student04 				= $advisorClassRow->student04;
						$advisorClass_student05 				= $advisorClassRow->student05;
						$advisorClass_student06 				= $advisorClassRow->student06;
						$advisorClass_student07 				= $advisorClassRow->student07;
						$advisorClass_student08 				= $advisorClassRow->student08;
						$advisorClass_student09 				= $advisorClassRow->student09;
						$advisorClass_student10 				= $advisorClassRow->student10;
						$advisorClass_student11 				= $advisorClassRow->student11;
						$advisorClass_student12 				= $advisorClassRow->student12;
						$advisorClass_student13 				= $advisorClassRow->student13;
						$advisorClass_student14 				= $advisorClassRow->student14;
						$advisorClass_student15 				= $advisorClassRow->student15;
						$advisorClass_student16 				= $advisorClassRow->student16;
						$advisorClass_student17 				= $advisorClassRow->student17;
						$advisorClass_student18 				= $advisorClassRow->student18;
						$advisorClass_student19 				= $advisorClassRow->student19;
						$advisorClass_student20 				= $advisorClassRow->student20;
						$advisorClass_student21 				= $advisorClassRow->student21;
						$advisorClass_student22 				= $advisorClassRow->student22;
						$advisorClass_student23 				= $advisorClassRow->student23;
						$advisorClass_student24 				= $advisorClassRow->student24;
						$advisorClass_student25 				= $advisorClassRow->student25;
						$advisorClass_student26 				= $advisorClassRow->student26;
						$advisorClass_student27 				= $advisorClassRow->student27;
						$advisorClass_student28 				= $advisorClassRow->student28;
						$advisorClass_student29 				= $advisorClassRow->student29;
						$advisorClass_student30 				= $advisorClassRow->student30;
						$class_number_students					= $advisorClassRow->number_students;
						$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
						$class_comments							= $advisorClassRow->class_comments;
						$copycontrol							= $advisorClassRow->copy_control;

						$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
						$content			.= "<tr><td><input type='radio' class='formInputButton' name='inp_id' value='$advisorClass_ID' required>$advisorClass_ID</td>
													<td>$advisorClass_advisor_call_sign</td>
													<td>$advisorClass_advisor_last_name, $advisorClass_advisor_first_name</td>
													<td>$advisorClass_semester</td>
													<td>$advisorClass_level</td>
													<td>$advisorClass_sequence</td></tr>";
					}
		
				} else {			// inp_table is student
					if ($doDebug) {
						echo "working with deleted student records<br />";
					}
					foreach ($result as $studentRow) {
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

						$content			.= "<tr><td><input type='radio' class='formInputButton' name='inp_id' value='$student_ID' required>$student_ID</td>
													<td>$student_call_sign</td>
													<td>$student_last_name, $student_first_name</td>
													<td>$student_semester</td>
													<td>$student_level</td>
													<td>--</td></tr>";
					}
				}
			} else {
				$content					.= "No record found for $inp_callsign";
				$doProceed					= FALSE;
			}
			if ($doProceed) {
				$content						.= "<tr><tr><td colspan='6'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
													</form></p>";
			}

		}
		
	} elseif ("3" == $strPass) {
	
		if ($doDebug) {
			echo "<br />landed at pass 3<br />
				  inp_id: $inp_id<br />
				  inp_callsign: $inp_callsign<br />
				  inp_table: $inp_table<br />
				  tableName: $tableName<br />";
		}
		
		// determine which file type to use
		$doProceed						= TRUE;
		if ($testMode) {
			$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
			if ($doDebug) {
				echo "<p><strong>Operating in Test Mode.</strong></p>";
			}
			$extMode					= 'tm';
			if ($isConsolidated) {
				if ($inp_table == 'advisor') {
					$tableName			= "wpw1_cwa_consolidated_advisor2";			
					$tableDeleted		= "wpw1_cwa_advisor_deleted2";
				} elseif ($inp_table == 'advisorclass') {
					$tableName			= "wpw1_cwa_consolidated_advisorclass2";				
					$tableDeleted		= "wpw1_cwa_advisorclass_deleted2";
				} elseif ($inp_table == 'student') {
					$tableName			= "wpw1_cwa_consolidated_student2";
					$tableDeleted		= "wpw1_cwa_student_deleted2";
				} else {
					if ($doDebug) {
						echo "Invalid inp_table information<br />";
					}
					$content			.= "The table type is required<br />";
					$doProceed			= FALSE;
				}
			} else {
				if ($inp_table == 'advisor') {
					$tableName			= "wpw1_cwa_advisor2";			
					$tableDeleted		= "wpw1_cwa_advisor_deleted2";
				} elseif ($inp_table == 'advisorclass') {
					$tableName			= "wpw1_cwa_advisorclass2";				
					$tableDeleted		= "wpw1_cwa_advisorclass_deleted2";
					$sql		= "select * from $tableName 
									where advisor_call_sign = '$inp_callsign' 
									order by date_created DESC ";
				} elseif ($inp_table == 'student') {
					$tableName			= "wpw1_cwa_student2";
					$tableDeleted		= "wpw1_cwa_student_deleted2";
				} else {
					if ($doDebug) {
						echo "Invalid inp_table information<br />";
					}
					$content			.= "The table type is required<br />";
					$doProceed			= FALSE;
				}
			}
		} else {
			$extMode					= 'pd';
			if ($isConsolidated) {
				if ($inp_table == 'advisor') {
					$tableName			= "wpw1_cwa_consolidated_advisor";			
					$tableDeleted		= "wpw1_cwa_advisor_deleted";
				} elseif ($inp_table == 'advisorclass') {
					$tableName			= "wpw1_cwa_consolidated_advisorclass";				
					$tableDeleted		= "wpw1_cwa_advisorclass_deleted";
				} elseif ($inp_table == 'student') {
					$tableName			= "wpw1_cwa_consolidated_student";
					$tableDeleted		= "wpw1_cwa_student_deleted";
				} else {
					if ($doDebug) {
						echo "Invalid inp_table information<br />";
					}
					$content			.= "The table type is required<br />";
					$doProceed			= FALSE;
				}
			} else {
				if ($inp_table == 'advisor') {
					$tableName			= "wpw1_cwa_advisor";			
					$tableDeleted		= "wpw1_cwa_advisor_deleted";
				} elseif ($inp_table == 'advisorclass') {
					$tableName			= "wpw1_cwa_advisorclass";				
					$tableDeleted		= "wpw1_cwa_advisorclass_deleted";
				} elseif ($inp_table == 'student') {
					$tableName			= "wpw1_cwa_student";
					$tableDeleted		= "wpw1_cwa_student_deleted";
				} else {
					if ($doDebug) {
						echo "Invalid inp_table information<br />";
					}
					$content			.= "The table type is required<br />";
					$doProceed			= FALSE;
				}
			}
		}
		if ($doProceed) {
			if ($inp_table == 'advisor') {
				if ($doDebug) {
					echo "setting up to get the deleted record from $tableDeleted<br />";
				}
				$sql		= "select * from $tableDeleted 
								where advisor_id=$inp_id";
				$wpw1_cwa_advisor	= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisor === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $tableDeleted table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname Reading $tableDeleted table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
					$content		.= "Unable to obtain content from $tableDeleted<br />";
				} else {
					$numARows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and found $numARows rows in $tableDeleted table<br />";
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

							if ($doDebug) {
								echo "have advisor $advisor_call_sign at id $advisor_ID fron $tableDeleted table<br />
									  preparing to insert into $tableName table<br />";
							}

							//// Insert advisor record
							$updateParams		= array('select_sequence'=>$advisor_select_sequence,
														'call_sign'=>$advisor_call_sign,
														'first_name'=>$advisor_first_name,
														'last_name'=>$advisor_last_name,
														'email'=>$advisor_email,
														'phone'=>$advisor_phone,
														'ph_code'=>$advisor_ph_code,
														'text_message'=>$advisor_text_message,
														'city'=>$advisor_city,
														'state'=>$advisor_state,
														'zip_code'=>$advisor_zip_code,
														'country'=>$advisor_country,
														'country_code'=>$advisor_country_code,
														'time_zone'=>$advisor_time_zone,
														'timezone_id'=>$advisor_timezone_id,
														'timezone_offset'=>$advisor_timezone_offset,
														'whatsapp_app'=>$advisor_whatsapp,
														'signal_app'=>$advisor_signal,
														'telegram_app'=>$advisor_telegram,
														'messenger_app'=>$advisor_messenger,
														'semester'=>$advisor_semester,
														'survey_score'=>$advisor_survey_score,
														'languages'=>$advisor_languages,
														'fifo_date'=>$advisor_fifo_date,
														'welcome_email_date'=>$advisor_welcome_email_date,
														'verify_email_date'=>$advisor_verify_email_date,
														'verify_email_number'=>$advisor_verify_email_number,
														'verify_response'=>$advisor_verify_response,
														'action_log'=>$advisor_action_log,
														'class_verified'=>$advisor_class_verified,
														'control_code'=>$advisor_control_code);
							$updateFormat		= array('%d',				// select_sequence
														'%s',				// call_sign
														'%s',				// first_name
														'%s',				// last_name
														'%s',				// email
														'%s',				// phone
														'%s',				// ph_code
														'%s',				// text_message
														'%s',				// city
														'%s',				// state
														'%s',				// zip_code
														'%s',				// country
														'%s',				// country_code
														'%d',				// time_zone
														'%s',				// timezone_id
														'%f',				// timezone_offset
														'%s',				// whatsapp_app
														'%s',				// signal_app
														'%s',				// telegram_app
														'%s',				// messenger_app
														'%s',				// semester
														'%d',				// survey_score
														'%s',				// languages
														'%s',				// fifo_date
														'%s',				// welcome_email_date
														'%s',				// verify_email_date
														'%d',				// verify_email_number
														'%s',				// verify_response
														'%s',				// action_log
														'%s',				// class_verified
														'%s');				// control_code
							$result				= $wpdb->insert($tableName,
																$updateParams,
																$updateFormat);
							if ($result === FALSE) {
								if ($doDebug) {
									echo "Inserting $advisor_call_sign record failed<br />
											wpdb->last_query: " . $wpdb->last_query . "<br />
											<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
								}
							} else {
								$nextID				= $wpdb->insert_id;
								if ($doDebug) {
									$myStr			 	= $wpdb->last_query;
									echo "ran $myStr<br/>and successfully inserted $advisor_call_sign record at $nextID<br />";
								}
								$content			.= "Successfully transferred $inp_callsign to $tableName table at id $nextID.";
								
								// now delete the deleted record
								$sql			= "delete from $tableDeleted 
													where advisor_id=$inp_id";
								$result			= $wpdb->get_results($sql);
								if ($result === FALSE) {
									$myError			= $wpdb->last_error;
									$myQuery			= $wpdb->last_query;
									if ($doDebug) {
										echo "Reading $tableDeleted table failed<br />
											  wpdb->last_query: $myQuery<br />
											  wpdb->last_error: $myError<br />";
									}
									$errorMsg			= "$jobname reading $tableDeleted failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
									sendErrorEmail($errorMsg);
									$content		.= "Unable to obtain content from $tableDeleted to delete<br />";
								} else {
									$content		.= "<br />Successfully deleted $inp_callsign from $tableDeleted table.<br />
														<br /><b>NOTE:</b> If you will be recovering the associated advisorClass record(s), 
														make a note of the advisor_id above as you will need it after the advisorClass 
														record(s) have been recovered.<br />";
									if ($doDebug) {
										echo "deleted $inp_id from $tableDeleted<br />";
									}
								}
							}
						}
					} else {
						$content		.= "tried to get $inp_id for $inp_callsign from $tableName table. Did not find.<br />
											SQL: $sql<br />";
					}
				}
			
			} elseif ($inp_table == 'advisorclass') {
				if ($doDebug) {
					echo "Recovering advisor $inp_callsign ID: $inp_id<br />";
				}
				$sql		= "select * from $tableDeleted 
								where advisorclass_id=$inp_id";
				$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
				if ($wpw1_cwa_advisorclass === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $tableDeleted table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname Reading $tableDeleted table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
				} else {
					$numACRows						= $wpdb->num_rows;
					if ($doDebug) {
						$myStr						= $wpdb->last_query;
						echo "ran $myStr<br />and found $numACRows rows<br />";
					}
					if ($numACRows > 0) {
						foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
							$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
							$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
							$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
							$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
							$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
							$advisorClass_sequence 					= $advisorClassRow->sequence;
							$advisorClass_semester 					= $advisorClassRow->semester;
							$advisorClass_timezone 					= $advisorClassRow->time_zone;
							$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
							$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
							$advisorClass_level 					= $advisorClassRow->level;
							$advisorClass_class_size 				= $advisorClassRow->class_size;
							$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
							$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
							$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
							$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
							$advisorClass_action_log 				= $advisorClassRow->action_log;
							$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
							$advisorClass_date_created				= $advisorClassRow->date_created;
							$advisorClass_date_updated				= $advisorClassRow->date_updated;
							$advisorClass_student01 				= $advisorClassRow->student01;
							$advisorClass_student02 				= $advisorClassRow->student02;
							$advisorClass_student03 				= $advisorClassRow->student03;
							$advisorClass_student04 				= $advisorClassRow->student04;
							$advisorClass_student05 				= $advisorClassRow->student05;
							$advisorClass_student06 				= $advisorClassRow->student06;
							$advisorClass_student07 				= $advisorClassRow->student07;
							$advisorClass_student08 				= $advisorClassRow->student08;
							$advisorClass_student09 				= $advisorClassRow->student09;
							$advisorClass_student10 				= $advisorClassRow->student10;
							$advisorClass_student11 				= $advisorClassRow->student11;
							$advisorClass_student12 				= $advisorClassRow->student12;
							$advisorClass_student13 				= $advisorClassRow->student13;
							$advisorClass_student14 				= $advisorClassRow->student14;
							$advisorClass_student15 				= $advisorClassRow->student15;
							$advisorClass_student16 				= $advisorClassRow->student16;
							$advisorClass_student17 				= $advisorClassRow->student17;
							$advisorClass_student18 				= $advisorClassRow->student18;
							$advisorClass_student19 				= $advisorClassRow->student19;
							$advisorClass_student20 				= $advisorClassRow->student20;
							$advisorClass_student21 				= $advisorClassRow->student21;
							$advisorClass_student22 				= $advisorClassRow->student22;
							$advisorClass_student23 				= $advisorClassRow->student23;
							$advisorClass_student24 				= $advisorClassRow->student24;
							$advisorClass_student25 				= $advisorClassRow->student25;
							$advisorClass_student26 				= $advisorClassRow->student26;
							$advisorClass_student27 				= $advisorClassRow->student27;
							$advisorClass_student28 				= $advisorClassRow->student28;
							$advisorClass_student29 				= $advisorClassRow->student29;
							$advisorClass_student30 				= $advisorClassRow->student30;
							$class_number_students					= $advisorClassRow->number_students;
							$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
							$class_comments							= $advisorClassRow->class_comments;
							$copycontrol							= $advisorClassRow->copy_control;

							$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);

							if ($doDebug) {
								echo "have advisorClass $advisorClass_advisor_call_sign at id $advisorClass_ID fron $tableDeleted table<br />
									  preparing to insert into $tableName table<br />";
							}

							//// Insert into advisorClass
							$inputParams		= array('advisor_call_sign'=>$advisorClass_advisor_call_sign,
														'advisor_first_name'=>$advisorClass_advisor_first_name,
														'advisor_last_name'=>$advisorClass_advisor_last_name,
														'advisor_id'=>$advisorClass_advisor_id,
														'sequence'=>$advisorClass_sequence,
														'semester'=>$advisorClass_semester,
														'time_zone'=>$advisorClass_timezone,
														'timezone_id'=>$advisorClass_timezone_id,
														'timezone_offset'=>$advisorClass_timezone_offset,
														'level'=>$advisorClass_level,
														'class_size'=>$advisorClass_class_size,
														'class_schedule_days'=>$advisorClass_class_schedule_days,
														'class_schedule_times'=>$advisorClass_class_schedule_times,
														'class_schedule_days_utc'=>$advisorClass_class_schedule_days_utc,
														'class_schedule_times_utc'=>$advisorClass_class_schedule_times_utc,
														'action_log'=>$advisorClass_action_log,
														'class_incomplete'=>$advisorClass_class_incomplete,
														'date_created'=>$advisorClass_date_created,
														'date_updated'=>$advisorClass_date_updated,
														'student01'=>$advisorClass_student01,
														'student02'=>$advisorClass_student02,
														'student03'=>$advisorClass_student03,
														'student04'=>$advisorClass_student04,
														'student05'=>$advisorClass_student05,
														'student06'=>$advisorClass_student06,
														'student07'=>$advisorClass_student07,
														'student08'=>$advisorClass_student08,
														'student09'=>$advisorClass_student09,
														'student10'=>$advisorClass_student10,
														'student11'=>$advisorClass_student11,
														'student12'=>$advisorClass_student12,
														'student13'=>$advisorClass_student13,
														'student14'=>$advisorClass_student14,
														'student15'=>$advisorClass_student15,
														'student16'=>$advisorClass_student16,
														'student17'=>$advisorClass_student17,
														'student18'=>$advisorClass_student18,
														'student19'=>$advisorClass_student19,
														'student20'=>$advisorClass_student20,
														'student21'=>$advisorClass_student21,
														'student22'=>$advisorClass_student22,
														'student23'=>$advisorClass_student23,
														'student24'=>$advisorClass_student24,
														'student25'=>$advisorClass_student25,
														'student26'=>$advisorClass_student26,
														'student27'=>$advisorClass_student27,
														'student28'=>$advisorClass_student28,
														'student29'=>$advisorClass_student29,
														'student30'=>$advisorClass_student30,
														'number_students'=>$class_number_students,
														'evaluation_complete'=>$class_evaluation_complete,
														'class_comments'=>$class_comments,
														'copy_control'=>$copycontrol);

							$inputFormat		= array('%s','%s','%s','%d','%d','%s','%s','%f','%s','%s',
														'%d','%s','%s','%s','%s','%s','%s','%s','%s','%s',
														'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
														'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
														'%s','%s','%s','%s','%s','%s','%s','%s','%s','%d',
														'%s','%s','%s');

										
							$insertResult	= $wpdb->insert($tableName,
															$inputParams,
															$inputFormat);
							if ($insertResult === FALSE) {
								$myError			= $wpdb->last_error;
								$myQuery			= $wpdb->last_query;
								if ($doDebug) {
									echo "Inserting into $tableName table failed<br />
										  wpdb->last_query: $myQuery<br />
										  wpdb->last_error: $myError<br />";
								}
								$errorMsg			= "$jobname inserting $tableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
								sendErrorEmail($errorMsg);
								$content		.= "Unable to insert into $tableName<br />";
							} else {
								$newID			= $wpdb->insert_id;
								if ($doDebug) {
									$myStr			= $wpdb->last_query;
									echo "ran $myStr<br />and inserted $newID into $tableName<br />";
								}
								
								$content			.= "Successfully transferred $inp_callsign to $tableName table at id $newID.";
								// now delete the deleted record
								$sql			= "delete from $tableDeleted 
													where advisorclass_id=$inp_id";
								$result			= $wpdb->get_results($sql);
								if ($result === FALSE) {
									$myError			= $wpdb->last_error;
									$myQuery			= $wpdb->last_query;
									if ($doDebug) {
										echo "Reading $tableDeleted table failed<br />
											  wpdb->last_query: $myQuery<br />
											  wpdb->last_error: $myError<br />";
									}
									$errorMsg			= "$jobname reading $tableDeleted failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
									sendErrorEmail($errorMsg);
									$content		.= "Unable to obtain content from $tableDeleted to delete<br />";
								} else {
									$content		.= "<br />Successfully deleted $inp_callsign from $tableDeleted table.<br />
														<br /><b<NOTE:</b> You need to update the advisor_id field in the advisorClass record.<br />";
									if ($doDebug) {
										echo "deleted $inp_id from $tableDeleted<br />";
									}
								}
							}
						}
					} else {
						$content		.= "tried to get $inp_id for $inp_callsign from $tableName table. Did not find.<br />
											SQL: $sql<br />";
					}
				}
	
			} else {		
				if ($doDebug) {
					echo "Recovering student $inp_callsign id: $inp_id<br />";
				}
				$sql		= "select * from $tableDeleted 
								where student_id=$inp_id";
				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					$myError			= $wpdb->last_error;
					$myQuery			= $wpdb->last_query;
					if ($doDebug) {
						echo "Reading $tableName table failed<br />
							  wpdb->last_query: $myQuery<br />
							  wpdb->last_error: $myError<br />";
					}
					$errorMsg			= "$jobname reading $tableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
					sendErrorEmail($errorMsg);
					$content		.= "Unable to obtain content from $tableName<br />";
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

							if ($doDebug) {
								echo "have student $student_call_sign at id $student_ID fron $tableDeleted table<br />
									  preparing to insert into $tableName table<br />";
							}

							//// insert into student table
							$inputParams 	= array('call_sign'=>$student_call_sign,
													'first_name'=>$student_first_name,
													'last_name'=>$student_last_name,
													'email'=>$student_email,
													'phone'=>$student_phone,
													'ph_code'=>$student_ph_code,
													'city'=>$student_city,
													'state'=>$student_state,
													'zip_code'=>$student_zip_code,
													'country'=>$student_country,
													'country_code'=>$student_country_code,
													'time_zone'=>$student_time_zone,
													'timezone_id'=>$student_timezone_id,
													'timezone_offset'=>$student_timezone_offset,
													'whatsapp_app'=>$student_whatsapp,
													'signal_app'=>$student_signal,
													'telegram_app'=>$student_telegram,
													'messenger_app'=>$student_messenger,
													'wpm'=>$student_wpm,
													'youth'=>$student_youth,
													'age'=>$student_age,
													'student_parent'=>$student_student_parent,
													'student_parent_email'=>$student_student_parent_email,
													'level'=>$student_level,
													'waiting_list'=>$student_waiting_list,
													'request_date'=>$student_request_date,
													'semester'=>$student_semester,
													'notes'=>$student_notes,
													'welcome_date'=>$student_welcome_date,
													'email_sent_date'=>$student_email_sent_date,
													'email_number'=>$student_email_number,
													'response'=>$student_response,
													'response_date'=>$student_response_date,
													'abandoned'=>$student_abandoned,
													'student_status'=>$student_student_status,
													'action_log'=>$student_action_log,
													'pre_assigned_advisor'=>$student_pre_assigned_advisor,
													'selected_date'=>$student_selected_date,
													'no_catalog'=>$student_no_catalog,
													'hold_override'=>$student_hold_override,
													'messaging'=>$student_messaging,
													'assigned_advisor'=>$student_assigned_advisor,
													'advisor_select_date'=>$student_advisor_select_date,
													'advisor_class_timezone'=>$student_advisor_class_timezone,
													'hold_reason_code'=>$student_hold_reason_code,
													'class_priority'=>$student_class_priority,
													'assigned_advisor_class'=>$student_assigned_advisor_class,
													'promotable'=>$student_promotable,
													'excluded_advisor'=>$student_excluded_advisor,
													'student_survey_completion_date'=>$student_student_survey_completion_date,
													'available_class_days'=>$student_available_class_days,
													'intervention_required'=>$student_intervention_required,
													'copy_control'=>$student_copy_control,
													'first_class_choice'=>$student_first_class_choice,
													'second_class_choice'=>$student_second_class_choice,
													'third_class_choice'=>$student_third_class_choice,
													'first_class_choice_utc'=>$student_first_class_choice_utc,
													'second_class_choice_utc'=>$student_second_class_choice_utc,
													'third_class_choice_utc'=>$student_third_class_choice_utc,
													'date_created'=>$student_date_created,
													'date_updated'=>$student_date_updated);

							$inputFormat 	= array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
													'%s','%d','%s','%f','%s','%s','%s','%s','%s','%s',
													'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
													'%d','%s','%s','%s','%s','%s','%s','%s','%s','%s',
													'%s','%s','%s','%d','%s','%d','%s','%s','%s','%s',
													'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
													'%s');
										
							$insertResult	= $wpdb->insert($tableName,
															$inputParams,
															$inputFormat);
							if ($insertResult === FALSE) {
								$myError			= $wpdb->last_error;
								$myQuery			= $wpdb->last_query;
								if ($doDebug) {
									echo "Inserting into $tableName table failed<br />
										  wpdb->last_query: $myQuery<br />
										  wpdb->last_error: $myError<br />";
								}
								$errorMsg			= "$jobname inserting $tableName failed while attempting to move to past_student. <p>SQL: $myQuery</p><p> Error: $myError</p>";
								sendErrorEmail($errorMsg);
								$content		.= "Unable to insert into $tableName<br />";
							} else {
								$newID			= $wpdb->insert_id;
								if ($doDebug) {
									$myStr			= $wpdb->last_query;
									echo "ran $myStr<br />and inserted $newID into $tableName<br />";
								}
								$content			.= "Successfully transferred $inp_callsign to $tableName table at id $newID.";
								
								// now delete the deleted record
								$sql			= "delete from $tableDeleted 
													where student_id=$inp_id";
								$result			= $wpdb->get_results($sql);
								if ($result === FALSE) {
									$myError			= $wpdb->last_error;
									$myQuery			= $wpdb->last_query;
									if ($doDebug) {
										echo "Reading $tableDeleted table failed<br />
											  wpdb->last_query: $myQuery<br />
											  wpdb->last_error: $myError<br />";
									}
									$errorMsg			= "$jobname reading $tableDeleted failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
									sendErrorEmail($errorMsg);
									$content		.= "Unable to obtain content from $tableDeleted to delete<br />";
								} else {
									$content		.= "<br />Successfully deleted $inp_callsign from $tableDeleted table.";
									if ($doDebug) {
										echo "deleted $inp_id from $tableDeleted<br />";
									}
								}
							}
						}
					} else {
						$content		.= "tried to get $inp_id for $inp_callsign from $tableName table. Did not find.<br />
											SQL: $sql<br />";
					}
				}
			}
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
add_shortcode ('recover_deleted_record', 'recover_deleted_record_func');
