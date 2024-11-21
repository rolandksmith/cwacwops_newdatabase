function recover_deleted_record_func() {

/*
	Modified 24Oct24 by Roland for new database
	
*/
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
	if ($userName == '') {
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
									<input type='radio' class='formInputButton' name='inp_table' value='student' >Student Record<br />
									<input type='radio' class='formInputButton' name='inp_table' value='user' >User Master<br /></td></tr>
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
			$userMasterTableName	= 'wpw1_cwa_user_master2';
			if ($inp_table == 'advisor') {
				$tableName			= "wpw1_cwa_advisor2";			
				$tableDeleted		= "wpw1_cwa_deleted_advisor2";
				$sql		= "select * from $tableDeleted 
								where advisor_call_sign = '$inp_callsign' 
								order by advisor_date_created DESC ";
			} elseif ($inp_table == 'advisorclass') {
				$tableName			= "wpw1_cwa_advisorclass2";				
				$tableDeleted		= "wpw1_cwa_deleted_advisorclass2";
				$sql		= "select * from $tableDeleted 
								where advisorclass_call_sign = '$inp_callsign' 
								order by advisorclass_date_created DESC ";
			} elseif ($inp_table == 'student') {
				$tableName			= "wpw1_cwa_student2";
				$tableDeleted		= "wpw1_cwa_deleted_student2";
				$sql		= "select * from $tableDeleted 
								where student_call_sign = '$inp_callsign' 
								order by student_date_created DESC ";
			} elseif ($inp_table == 'user') {
				$tableName		= 'wpw1_cwa_user_master2';
				$tableDeleted	= 'wpw1_cwa_user_master_deleted2';
				$sql			= "select * from $tableDeleted 
									where user_call_sign = '$inp_callsign'";
			} else {
				if ($doDebug) {
					echo "Invalid inp_table information<br />";
				}
				$content			.= "The table type is required<br />";
				$doProceed			= FALSE;
			}
		} else {
			$extMode					= 'pd';
			$userMasterTableName	= 'wpw1_cwa_user_master';
			if ($inp_table == 'advisor') {
				$tableName			= "wpw1_cwa_advisor";			
				$tableDeleted		= "wpw1_cwa_deleted_advisor";
				$sql		= "select * from $tableDeleted 
								left join  $userMasterTableName on user_call_sign = advisor_call_sign 
								where advisor_call_sign = '$inp_callsign' 
								order by advisor_date_created DESC ";
			} elseif ($inp_table == 'advisorclass') {
				$tableName			= "wpw1_cwa_advisorclass";				
				$tableDeleted		= "wpw1_cwa_deleted_advisorclass";
				$sql		= "select * from $tableDeleted 
								left join  $userMasterTableName on user_call_sign = advisorclass_call_sign 
								where advisor_call_sign = '$inp_callsign' 
								order by advisor_date_created DESC ";
			} elseif ($inp_table == 'student') {
				$tableName			= "wpw1_cwa_student";
				$tableDeleted		= "wpw1_cwa_deleted_student";
				$sql		= "select * from $tableDeleted 
								left join  $userMasterTableName on user_call_sign = student_call_sign 
								where student_call_sign = '$inp_callsign' 
								order by student_date_created DESC ";
			} elseif ($inp_table == 'user') {
				$tableName		= 'wpw1_cwa_user_master';
				$tableDeleted	= 'wpw1_cwa_user_master_deleted';
				$sql			= "select * from $tableDeleted 
									where user_call_sign = '$inp_callsign'";
			} else {
				if ($doDebug) {
					echo "Invalid inp_table information<br />";
				}
				$content			.= "The table type is required<br />";
				$doProceed			= FALSE;
			}
		}
		
		if ($doDebug) {
			echo "using tableName $tableName<br />
				  SQL: $sql<br /><br />";
		}
	
		$result				= $wpdb->get_results($sql);
		if ($result === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content		.= "Unable to obtain content from tableName for $inp_callsign<br />";
		} else {
			$numRows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numRows rows<br />";
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
											<th>Semester/Created</th>
											<th>Level/Updated</th>
											<th>Sequence</th></tr>";
		
				// get the deleted records according to the table type
				if ($inp_table == 'advisor') {
					if ($doDebug) {
						echo "working with deleted advisor records<br />";
					}
					foreach ($result as $advisorRow) {
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
						$advisorClass_master_ID 				= $advisorClassRow->user_ID;
						$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
						$advisorClass_first_name 				= $advisorClassRow->user_first_name;
						$advisorClass_last_name 				= $advisorClassRow->user_last_name;
						$advisorClass_email 					= $advisorClassRow->user_email;
						$advisorClass_phone 					= $advisorClassRow->user_phone;
						$advisorClass_city 						= $advisorClassRow->user_city;
						$advisorClass_state 					= $advisorClassRow->user_state;
						$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
						$advisorClass_country_code 				= $advisorClassRow->user_country_code;
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

						$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
						$content			.= "<tr><td><input type='radio' class='formInputButton' name='inp_id' value='$advisorClass_ID' required>$advisorClass_ID</td>
													<td>$advisorClass_advisor_call_sign</td>
													<td>$advisorClass_advisor_last_name, $advisorClass_advisor_first_name</td>
													<td>$advisorClass_semester</td>
													<td>$advisorClass_level</td>
													<td>$advisorClass_sequence</td></tr>";
					}
		
				} elseif ($inp_table == 'student'){			// inp_table is student
					if ($doDebug) {
						echo "working with deleted student records<br />";
					}
					foreach ($result as $studentRow) {
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

						$content			.= "<tr><td><input type='radio' class='formInputButton' name='inp_id' value='$student_ID' required>$student_ID</td>
													<td>$student_call_sign</td>
													<td>$student_last_name, $student_first_name</td>
													<td>$student_semester</td>
													<td>$student_level</td>
													<td>--</td></tr>";
					}
				} elseif ($inp_table == 'user') {
					foreach($result as $sqlRow) {
						$user_id				= $sqlRow->user_id;
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

						$content			.= "<tr><td><input type='radio' class='formInputButton' name='inp_id' value='$user_id' required>$user_id</td>
													<td>$user_call_sign</td>
													<td>$user_last_name, $user_first_name</td>
													<td>$user_date_created</td>
													<td>$user_date_updated</td>
													<td>--</td></tr>";
				
					}
				} else {
					$content				.= "No user master records found for $inp_callsign";
					$doProceed				= FALSE;
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
			if ($inp_table == 'advisor') {
				$tableName			= "wpw1_cwa_advisor2";
				$tableDeleted		= "wpw1_cwa_deleted_advisor2";
				$tableIDName		= 'advisor_id';
			} elseif ($inp_table == 'advisorclass') {
				$tableName			= "wpw1_cwa_advisorclass2";				
				$tableDeleted		= "wpw1_cwa_deleted_advisorclass2";
				$tableIDName		= 'advisorclass_id';
			} elseif ($inp_table == 'student') {
				$tableName			= "wpw1_cwa_student2";
				$tableDeleted		= "wpw1_cwa_deleted_student2";
				$tableIDName		= 'student_id';
			} elseif ($inp_table == 'user') {
				$tableName		= 'wpw1_cwa_user_master2';
				$tableDeleted	= 'wpw1_cwa_user_master_deleted2';
				$tableIDName	= 'user_id';
			} else {
				if ($doDebug) {
					echo "Invalid inp_table information<br />";
				}
				$content			.= "The table type is required<br />";
				$doProceed			= FALSE;
			}
		} else {
			$extMode					= 'pd';
			if ($inp_table == 'advisor') {
				$tableName			= "wpw1_cwa_advisor";			
				$tableDeleted		= "wpw1_cwa_deleted_advisor";
				$tableIDName		= 'advisor_id';
			} elseif ($inp_table == 'advisorclass') {
				$tableName			= "wpw1_cwa_advisorclass";				
				$tableDeleted		= "wpw1_cwa_deleted_advisorclass";
				$tableIDName		= 'advisorclass_id';
			} elseif ($inp_table == 'student') {
				$tableName			= "wpw1_cwa_student";
				$tableDeleted		= "wpw1_cwa_deleted_student";
				$tableIDName		= 'student_id';
			} elseif ($inp_table == 'user') {
				$tableName		= 'wpw1_cwa_user_master';
				$tableDeleted	= 'wpw1_cwa_user_master_deleted';
				$tableIDName	= 'user_id';
			} else {
				if ($doDebug) {
					echo "Invalid inp_table information<br />";
				}
				$content			.= "The table type is required<br />";
				$doProceed			= FALSE;
			}
		}
		if ($doProceed) {
			$content				.= "<h3>$jobname $inp_id to $tableName</h3>";
			// see if the record id is available
			$idCount		= $wpdb->get_var("select count($tableIDName) 
											from $tableName 
											where $tableIDName = $inp_id");
			if ($idCount == NULL){
				$content	.= "<p>An error occurred determining if $tableIDName of 
								$inp_id was available in $tableName</p>";
				$doProceed	= FALSE;
			} else {
				if ($idCount > 0) {
					$content	.= "<p>The $tableIDName of $inp_id is already in 
									use. Can not restore the deleted record</p>";
					$doProceed	= FALSE;
				} else {			/// id is available
					if ($doDebug) {
						echo "$inp_id is available in $tableName. Doing the insert<br />";
					}
					$content	.= "<p>Record $inp_id is available in $tableName</p>";
					$myResult	= $wpdb->get_results("insert into $tableName 
														select * from $tableDeleted
														where $tableIDName = $inp_id");
			
					if (sizeof($myResult) != 0 || $myResult === FALSE) {
						$myStr			= $wpdb->last_query;
						$myStr1			= $wpdb->last_error;
						if ($doDebug) {
	 						echo "adding $inp_id to $deleteTable  table failed<br />
	 							  last query: $myStr<br />
	 							  last error: $myStr1<br />";
	 					}
	 					$content			.= "<p>Inserting $inp_id into $tableName failed.<br />
	 											Error: $myStr1</p>";
	 					$doProceed			= FALSE;
					} else {				// insert worked. Now delete the deleted record
						$content			.= "<p>Record $inp_id restored to $tableName</p>";
						$deleteResult		= $wpdb->get_results("delete from $tableDeleted 
												where $tableIDName = $inp_id");
						if (sizeof($deleteResult) != 0 || $deleteResult === FALSE) {
							$myStr			= $wpdb->last_query;
							$myStr1			= $wpdb->last_error;
							if ($doDebug) {
								echo "deleting $inp_id from $deleteTable  table failed<br />
									  last query: $myStr<br />
									  last error: $myStr1<br />";
							}
							$content			.= "<p>Deleting $inp_id from $tableDeleted failed.<br />
													Error: $myStr1</p>";
							$doProceed			= FALSE;
						} else {
							if ($doDebug) {
								echo "delete was successful<br />";
							}
							$content			.= "$inp_id successfully deleted from $tableDeleted</p>";
						}
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
