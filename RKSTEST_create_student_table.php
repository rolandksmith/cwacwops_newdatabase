function RKSTEST_create_student_table_func() {

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
	$theURL						= "$siteURL/rksmith-create-student-table/";
	$insertCount				= 0;
	$jobname					= "RKSTEST: Create Student Table V$versionNumber";

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
		$content	.= "<p><strong>Operating in Test Mode</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode						= 'TESTMODE';
		$consolidatedStudentTableName	= "wpw1_cwa_consolidated_student2";
		$studentTableName				= "wpw1_cwa_student2";
		$auditLogTableName				= "wpw1_cwa_audit_log2";
	} else {
		$extMode					= 'PRODUCTION';
		$consolidatedStudentTableName	= "wpw1_cwa_consolidated_student";
		$studentTableName				= "wpw1_cwa_student";
		$auditLogTableName				= "wpw1_cwa_audit_log";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>This program truncates the student table and then 
							reads all records from the consolidated student table 
							and writes the appropriate information to the student 
							table, that is, removing the user_master data.</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}	
		$doProceed				= TRUE;
		$content				.= "<h3>$jobname</h3>"; 
		
		// truncate the student table
		
		if ($doDebug) {
			echo "truncating $studentTableName<br />";
		}
		$deleteUsers 		= $wpdb->query("TRUNCATE TABLE $studentTableName");
		if ($deleteUsers === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$doProceed		= FALSE;
		} else {
			$content		.= "$studentTableName table has been truncated<br />";

			/// clean student records out of the audit log
			$deleteAuditLog	= $wpdb->query("delete from $auditLogTableName 
											where logtype = 'STUDENT' 
												and logmode='$extMode'");
			if ($deleteAuditLog === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$doProceed			= FALSE;
			} else {
				// all is well ... start the process
				$sql					= "select * from $consolidatedStudentTableName 
											order by call_sign";		 
				$wpw1_cwa_student		= $wpdb->get_results($sql);
				if ($wpw1_cwa_student === FALSE) {
					handleWPDBError($jobname,$doDebug);
					$doProceed			= FALSE;
				} else {
					$numSRows			= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and found $numSRows rows<br />";
					}
					if ($numSRows > 0) {
						$content		.= "<p>Creating $numSRows in $studentTableName<br />";
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
							$student_survey_completion_date			= $studentRow->student_survey_completion_date;
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
							
							if ($doDebug) {
								echo "<br >Processing $student_last_name, $student_first_name ($student_call_sign)<br />";
							}
							if ($student_youth == 'Yes') {
								$student_youth		= 'Y';
							}
							if ($student_youth == 'No') {
								$student_youth		= 'N';
							}

							$updateParams			= array('student_call_sign'=>$student_call_sign,
															'student_time_zone'=>$student_time_zone,
															'student_timezone_offset'=>$student_timezone_offset,
															'student_youth'=>$student_youth,
															'student_age'=>$student_age,
															'student_parent'=>$student_student_parent,
															'student_parent_email'=>$student_student_parent_email,
															'student_level'=>$student_level,
															'student_waiting_list'=>$student_waiting_list,
															'student_request_date'=>$student_request_date,
															'student_semester'=>$student_semester,
															'student_notes'=>$student_notes,
															'student_welcome_date'=>$student_welcome_date,
															'student_email_sent_date'=>$student_email_sent_date,
															'student_email_number'=>$student_email_number,
															'student_response'=>$student_response,
															'student_response_date'=>$student_response_date,
															'student_abandoned'=>$student_abandoned,
															'student_status'=>$student_student_status,
															'student_action_log'=>$student_action_log,
															'student_pre_assigned_advisor'=>$student_pre_assigned_advisor,
															'student_selected_date'=>$student_selected_date,
															'student_no_catalog'=>$student_no_catalog,
															'student_hold_override'=>$student_hold_override,
															'student_messaging'=>$student_messaging,
															'student_assigned_advisor'=>$student_assigned_advisor,
															'student_advisor_select_date'=>$student_advisor_select_date,
															'student_advisor_class_timezone'=>$student_advisor_class_timezone,
															'student_hold_reason_code'=>$student_hold_reason_code,
															'student_class_priority'=>$student_class_priority,
															'student_assigned_advisor_class'=>$student_assigned_advisor_class,
															'student_promotable'=>$student_promotable,
															'student_excluded_advisor'=>$student_excluded_advisor,
															'student_survey_completion_date'=>$student_survey_completion_date,
															'student_available_class_days'=>$student_available_class_days,
															'student_intervention_required'=>$student_intervention_required,
															'student_copy_control'=>$student_copy_control,
															'student_first_class_choice'=>$student_first_class_choice,
															'student_second_class_choice'=>$student_second_class_choice,
															'student_third_class_choice'=>$student_third_class_choice,
															'student_first_class_choice_utc'=>$student_first_class_choice_utc,
															'student_second_class_choice_utc'=>$student_second_class_choice_utc,
															'student_third_class_choice_utc'=>$student_third_class_choice_utc,
															'student_catalog_options'=>$student_catalog_options,
															'student_flexible'=>$student_flexible);
							$updateFormat			= array('%s','%s','%f','%s','%s','%s','%s','%s','%s',
															  '%s','%s','%s','%s','%s','%d','%s','%s','%s','%s',
															  '%s','%s','%s','%s','%s','%s','%s','%s','%d','%s',
															  '%d','%s','%s','%s','%s','%s','%s','%s','%s','%s',
															  '%s','%s','%s','%s','%s','%s','%s','%s');
		
							$studentUpdateData		= array('tableName'=>$studentTableName,
															'inp_method'=>'add',
															'inp_data'=>$updateParams,
															'inp_format'=>$updateFormat,
															'jobname'=>$jobname,
															'inp_id'=>0,
															'inp_callsign'=>$student_call_sign,
															'inp_semester'=>$student_semester,
															'inp_who'=>$userName,
															'testMode'=>$testMode,
															'doDebug'=>$doDebug);
							$updateResult	= updateStudent($studentUpdateData);
							if ($updateResult[0] === FALSE) {
								$myError	= $wpdb->last_error;
								$mySql		= $wpdb->last_query;
								$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
								if ($doDebug) {
									echo $errorMsg;
								}
								sendErrorEmail($errorMsg);
								$content		.= "Unable to insert content into $studentTableName<br />";
							} else {
//								$content		.= "Record for $student_call_sign successfully processed<br />";
								$insertCount++;
							}
		
						}
						$content		.= "<p>All records processed, $insertCount records written to $studentTableName table</p>";
					} else {
						$content		.= "<p>No records found in $consolidatedStudentTableName table to process</p>";
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
add_shortcode ('RKSTEST_create_student_table', 'RKSTEST_create_student_table_func');

