function make_or_restore_backups_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

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
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	
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
	$theURL						= "$siteURL/cwa-make-or-restore-backups/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Make or Restore Backups V$versionNumber";

	$copy_wpw1_cwa_advisor_wpw1_cwa_advisor2								= FALSE;
	$copy_wpw1_cwa_advisor2_wpw1_cwa_advisor								= FALSE;
	$copy_wpw1_cwa_advisorclass_wpw1_cwa_advisorclass2						= FALSE;
	$copy_wpw1_cwa_advisorclass2_wpw1_cwa_advisorclass						= FALSE;
	$copy_wpw1_cwa_audit_log_wpw1_cwa_audit_log2							= FALSE;
	$copy_wpw1_cwa_audit_log2_wpw1_cwa_audit_log							= FALSE;
	$copy_wpw1_cwa_current_catalog_wpw1_cwa_current_catalog2				= FALSE;
	$copy_wpw1_cwa_current_catalog2_wpw1_cwa_current_catalog				= FALSE;
	$copy_wpw1_cwa_deleted_advisor_wpw1_cwa_deleted_advisor2				= FALSE;
	$copy_wpw1_cwa_deleted_advisor2_wpw1_cwa_deleted_advisor				= FALSE;
	$copy_wpw1_cwa_deleted_advisorclass_wpw1_cwa_deleted_advisorclass2		= FALSE;
	$copy_wpw1_cwa_deleted_advisorclass2_wpw1_cwa_deleted_advisorclass		= FALSE;
	$copy_wpw1_cwa_deleted_student_wpw1_cwa_deleted_student2				= FALSE;
	$copy_wpw1_cwa_deleted_student2_wpw1_cwa_deleted_student				= FALSE;
	$copy_wpw1_cwa_deleted_user_master_wpw1_cwa_deleted_user_master2		= FALSE;
	$copy_wpw1_cwa_deleted_user_master2_wpw1_cwa_deleted_user_master		= FALSE;
	$copy_wpw1_cwa_evaluate_advisor_wpw1_cwa_evaluate_advisor2				= FALSE;
	$copy_wpw1_cwa_evaluate_advisor2_wpw1_cwa_evaluate_advisor				= FALSE;
	$copy_wpw1_cwa_joblog_wpw1_cwa_joblog2									= FALSE;
	$copy_wpw1_cwa_joblog2_wpw1_cwa_joblog									= FALSE;
	$copy_wpw1_cwa_new_assessment_data_wpw1_cwa_new_assessment_data2		= FALSE;
	$copy_wpw1_cwa_new_assessment_data2_wpw1_cwa_new_assessment_data		= FALSE;
	$copy_wpw1_cwa_production_email_wpw1_cwa_production_email2				= FALSE;
	$copy_wpw1_cwa_production_email2_wpw1_cwa_production_email				= FALSE;
	$copy_wpw1_cwa_reminders_wpw1_cwa_reminders2							= FALSE;
	$copy_wpw1_cwa_reminders2_wpw1_cwa_reminders							= FALSE;
	$copy_wpw1_cwa_replacement_requests_wpw1_cwa_replacement_requests2		= FALSE;
	$copy_wpw1_cwa_replacement_requests2_wpw1_cwa_replacement_requests		= FALSE;
	$copy_wpw1_cwa_reports_wpw1_cwa_reports2								= FALSE;
	$copy_wpw1_cwa_reports2_wpw1_cwa_reports								= FALSE;
	$copy_wpw1_cwa_student_wpw1_cwa_student2								= FALSE;
	$copy_wpw1_cwa_student2_wpw1_cwa_student								= FALSE;
	$copy_wpw1_cwa_temp_data_wpw1_cwa_temp_data2							= FALSE;
	$copy_wpw1_cwa_temp_data2_wpw1_cwa_temp_data							= FALSE;
	$copy_wpw1_cwa_user_master_wpw1_cwa_user_master2						= FALSE;
	$copy_wpw1_cwa_user_master2_wpw1_cwa_user_master						= FALSE;
	$copy_wpw1_cwa_user_master_deleted_wpw1_cwa_user_master_deleted2		= FALSE;
	$copy_wpw1_cwa_user_master_deleted2_wpw1_cwa_user_master_deleted		= FALSE;
	$copy_wpw1_cwa_user_master_history_wpw1_cwa_user_master_history2		= FALSE;
	$copy_wpw1_cwa_user_master_history2_wpw1_cwa_user_master_history		= FALSE;
	$copy_wpw1_usermeta_wpw1_usermeta2										= FALSE;
	$copy_wpw1_usermeta2_wpw1_usermeta										= FALSE;
	$copy_wpw1_users_wpw1_users2											= FALSE;
	$copy_wpw1_users2_wpw1_users											= FALSE;


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
			if ($str_key == 'advisor') {
				$advisor = $str_value;
				$advisor = filter_var($advisor,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_advisor_wpw1_cwa_advisor2 = TRUE;
			}
			if ($str_key == 'advisorclass') {
				$advisorclass = $str_value;
				$advisorclass = filter_var($advisorclass,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_advisorclass_wpw1_cwa_advisorclass2 = TRUE;
			}
			if ($str_key == 'audit_log') {
				$audit_log = $str_value;
				$audit_log = filter_var($audit_log,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_audit_log_wpw1_cwa_audit_log2 = TRUE;
			}
			if ($str_key == 'current_catalog') {
				$current_catalog = $str_value;
				$current_catalog = filter_var($current_catalog,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_current_catalog_wpw1_cwa_current_catalog2 = TRUE;
			}
			if ($str_key == 'deleted_advisor') {
				$deleted_advisor = $str_value;
				$deleted_advisor = filter_var($deleted_advisor,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_deleted_advisor_wpw1_cwa_deleted_advisor2 = TRUE;
			}
			if ($str_key == 'deleted_advisorclass') {
				$deleted_advisorclass = $str_value;
				$deleted_advisorclass = filter_var($deleted_advisorclass,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_deleted_advisorclass_wpw1_cwa_deleted_advisorclass2 = TRUE;
			}
			if ($str_key == 'deleted_student') {
				$deleted_student = $str_value;
				$deleted_student = filter_var($deleted_student,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_deleted_student_wpw1_cwa_deleted_student2 = TRUE;
			}
			if ($str_key == 'deleted_user_master') {
				$deleted_user_master = $str_value;
				$deleted_user_master = filter_var($deleted_user_master,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_deleted_user_master_wpw1_cwa_deleted_user_master2 = TRUE;
			}
			if ($str_key == 'evaluate_advisor') {
				$evaluate_advisor = $str_value;
				$evaluate_advisor = filter_var($evaluate_advisor,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_evaluate_advisor_wpw1_cwa_evaluate_advisor2 = TRUE;
			}
			if ($str_key == 'joblog') {
				$joblog = $str_value;
				$joblog = filter_var($joblog,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_joblog_wpw1_cwa_joblog2 = TRUE;
			}
			if ($str_key == 'joblog2') {
				$joblog2 = $str_value;
				$joblog2 = filter_var($joblog2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_joblog2_wpw1_cwa_joblog = TRUE;
			}
			if ($str_key == 'new_assessment_data') {
				$new_assessment_data = $str_value;
				$new_assessment_data = filter_var($new_assessment_data,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_new_assessment_data_wpw1_cwa_new_assessment_data2 = TRUE;
			}
			if ($str_key == 'production_email') {
				$production_email = $str_value;
				$production_email = filter_var($production_email,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_production_email_wpw1_cwa_production_email2 = TRUE;
			}
			if ($str_key == 'reminders') {
				$reminders = $str_value;
				$reminders = filter_var($reminders,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_reminders_wpw1_cwa_reminders2 = TRUE;
			}
			if ($str_key == 'replacement_requests') {
				$replacement_requests = $str_value;
				$replacement_requests = filter_var($replacement_requests,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_replacement_requests_wpw1_cwa_replacement_requests2 = TRUE;
			}
			if ($str_key == 'reports') {
				$reports = $str_value;
				$reports = filter_var($reports,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_reports_wpw1_cwa_reports2 = TRUE;
			}
			if ($str_key == 'student') {
				$student = $str_value;
				$student = filter_var($student,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_student_wpw1_cwa_student2 = TRUE;
			}
			if ($str_key == 'temp_data') {
				$temp_data = $str_value;
				$temp_data = filter_var($temp_data,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_temp_data_wpw1_cwa_temp_data2 = TRUE;
			}
			if ($str_key == 'user_master') {
				$user_master = $str_value;
				$user_master = filter_var($user_master,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_user_master_wpw1_cwa_user_master2 = TRUE;
			}
			if ($str_key == 'user_master_deleted') {
				$user_master_deleted = $str_value;
				$user_master_deleted = filter_var($user_master_deleted,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_user_master_deleted_wpw1_cwa_user_master_deleted2 = TRUE;
			}
			if ($str_key == 'user_master_history') {
				$user_master_history = $str_value;
				$user_master_history = filter_var($user_master_history,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_user_master_history_wpw1_cwa_user_master_history2 = TRUE;
			}
			if ($str_key == 'usermeta') {
				$usermeta = $str_value;
				$usermeta = filter_var($usermeta,FILTER_UNSAFE_RAW);
				$copy_wpw1_usermeta_wpw1_usermeta2 = TRUE;
			}
			if ($str_key == 'users') {
				$users = $str_value;
				$users = filter_var($users,FILTER_UNSAFE_RAW);
				$copy_wpw1_users_wpw1_users2 = TRUE;
			}
			
			if ($str_key == 'advisor2') {
				$advisor2 = $str_value;
				$advisor2 = filter_var($advisor2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_advisor2_wpw1_cwa_advisor = TRUE;
			}
			if ($str_key == 'advisorclass2') {
				$advisorclass2 = $str_value;
				$advisorclass2 = filter_var($advisorclass2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_advisorclass2_wpw1_cwa_advisorclass = TRUE;
			}
			if ($str_key == 'audit_log2') {
				$audit_log2 = $str_value;
				$audit_log2 = filter_var($audit_log2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_audit_log2_wpw1_cwa_audit_log = TRUE;
			}
			if ($str_key == 'current_catalog2') {
				$current_catalog2 = $str_value;
				$current_catalog2 = filter_var($current_catalog2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_current_catalog2_wpw1_cwa_current_catalog = TRUE;
			}
			if ($str_key == 'deleted_advisor2') {
				$deleted_advisor2 = $str_value;
				$deleted_advisor2 = filter_var($deleted_advisor2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_deleted_advisor2_wpw1_cwa_deleted_advisor = TRUE;
			}
			if ($str_key == 'deleted_advisorclass2') {
				$deleted_advisorclass2 = $str_value;
				$deleted_advisorclass2 = filter_var($deleted_advisorclass2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_deleted_advisorclass2_wpw1_cwa_deleted_advisorclass = TRUE;
			}
			if ($str_key == 'deleted_student2') {
				$deleted_student2 = $str_value;
				$deleted_student2 = filter_var($deleted_student2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_deleted_student2_wpw1_cwa_deleted_student = TRUE;
			}
			if ($str_key == 'deleted_user_master2') {
				$deleted_user_master2 = $str_value;
				$deleted_user_master2 = filter_var($deleted_user_master2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_deleted_user_master2_wpw1_cwa_deleted_user_master = TRUE;
			}
			if ($str_key == 'evaluate_advisor2') {
				$evaluate_advisor2 = $str_value;
				$evaluate_advisor2 = filter_var($evaluate_advisor2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_evaluate_advisor2_wpw1_cwa_evaluate_advisor = TRUE;
			}
			if ($str_key == 'new_assessment_data2') {
				$new_assessment_data2 = $str_value;
				$new_assessment_data2 = filter_var($new_assessment_data2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_new_assessment_data2_wpw1_cwa_new_assessment_data = TRUE;
			}
			if ($str_key == 'production_email2') {
				$production_email2 = $str_value;
				$production_email2 = filter_var($production_email2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_production_email2_wpw1_cwa_production_email = TRUE;
			}
			if ($str_key == 'reminders2') {
				$reminders2 = $str_value;
				$reminders2 = filter_var($reminders2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_reminders2_wpw1_cwa_reminders = TRUE;
			}
			if ($str_key == 'replacement_requests2') {
				$replacement_requests2 = $str_value;
				$replacement_requests2 = filter_var($replacement_requests2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_replacement_requests2_wpw1_cwa_replacement_requests = TRUE;
			}
			if ($str_key == 'reports2') {
				$reports2 = $str_value;
				$reports2 = filter_var($reports2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_reports2_wpw1_cwa_reports = TRUE;
			}
			if ($str_key == 'student2') {
				$student2 = $str_value;
				$student2 = filter_var($student2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_student2_wpw1_cwa_student = TRUE;
			}
			if ($str_key == 'temp_data2') {
				$temp_data2 = $str_value;
				$temp_data2 = filter_var($temp_data2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_temp_data2_wpw1_cwa_temp_data = TRUE;
			}
			if ($str_key == 'user_master2') {
				$user_master2 = $str_value;
				$user_master2 = filter_var($user_master2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_user_master2_wpw1_cwa_user_master = TRUE;
			}
			if ($str_key == 'user_master_deleted2') {
				$user_master_deleted2 = $str_value;
				$user_master_deleted2 = filter_var($user_master_deleted2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_user_master_deleted2_wpw1_cwa_user_master_deleted = TRUE;
			}
			if ($str_key == 'user_master_history2') {
				$user_master_history2 = $str_value;
				$user_master_history2 = filter_var($user_master_history2,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_user_master_history2_wpw1_cwa_user_master_history = TRUE;
			}
			if ($str_key == 'usermeta2') {
				$usermeta2 = $str_value;
				$usermeta2 = filter_var($usermeta2,FILTER_UNSAFE_RAW);
				$copy_wpw1_usermeta2_wpw1_usermeta = TRUE;
			}
			if ($str_key == 'users2') {
				$users2 = $str_value;
				$users2 = filter_var($users2,FILTER_UNSAFE_RAW);
				$copy_wpw1_users2_wpw1_users = TRUE;
			}
			if ($str_key == 'copymain') {
				$copymain = $str_value;
				$copymain = filter_var($copymain,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_advisor_wpw1_cwa_advisor2								= TRUE;
				$copy_wpw1_cwa_advisorclass_wpw1_cwa_advisorclass2						= TRUE;
				$copy_wpw1_cwa_audit_log_wpw1_cwa_audit_log2							= TRUE;
				$copy_wpw1_cwa_current_catalog_wpw1_cwa_current_catalog2				= TRUE;
				$copy_wpw1_cwa_deleted_advisor_wpw1_cwa_deleted_advisor2				= TRUE;
				$copy_wpw1_cwa_deleted_advisorclass_wpw1_cwa_deleted_advisorclass2		= TRUE;
				$copy_wpw1_cwa_deleted_student_wpw1_cwa_deleted_student2				= TRUE;
				$copy_wpw1_cwa_deleted_user_master_wpw1_cwa_deleted_user_master2		= TRUE;
				$copy_wpw1_cwa_evaluate_advisor_wpw1_cwa_evaluate_advisor2				= TRUE;
				$copy_wpw1_cwa_joblog_wpw1_cwa_joblog2									= TRUE;
				$copy_wpw1_cwa_new_assessment_data_wpw1_cwa_new_assessment_data2		= TRUE;
				$copy_wpw1_cwa_production_email_wpw1_cwa_production_email2				= TRUE;
				$copy_wpw1_cwa_reminders_wpw1_cwa_reminders2							= TRUE;
				$copy_wpw1_cwa_replacement_requests_wpw1_cwa_replacement_requests2		= TRUE;
				$copy_wpw1_cwa_reports_wpw1_cwa_reports2								= TRUE;
				$copy_wpw1_cwa_student_wpw1_cwa_student2								= TRUE;
				$copy_wpw1_cwa_temp_data_wpw1_cwa_temp_data2							= TRUE;
				$copy_wpw1_cwa_user_master_wpw1_cwa_user_master2						= TRUE;
				$copy_wpw1_cwa_user_master_deleted_wpw1_cwa_user_master_deleted2		= TRUE;
				$copy_wpw1_cwa_user_master_history_wpw1_cwa_user_master_history2		= TRUE;
				$copy_wpw1_usermeta_wpw1_usermeta2										= TRUE;
				$copy_wpw1_users_wpw1_users2											= TRUE;

			}
			if ($str_key == 'restoremain') {
				$restoremain = $str_value;
				$restoremain = filter_var($restoremain,FILTER_UNSAFE_RAW);
				$copy_wpw1_cwa_advisor2_wpw1_cwa_advisor								= TRUE;
				$copy_wpw1_cwa_advisorclass2_wpw1_cwa_advisorclass						= TRUE;
				$copy_wpw1_cwa_audit_log2_wpw1_cwa_audit_log							= TRUE;
				$copy_wpw1_cwa_current_catalog2_wpw1_cwa_current_catalog				= TRUE;
				$copy_wpw1_cwa_deleted_advisor2_wpw1_cwa_deleted_advisor				= TRUE;
				$copy_wpw1_cwa_deleted_advisorclass2_wpw1_cwa_deleted_advisorclass		= TRUE;
				$copy_wpw1_cwa_deleted_student2_wpw1_cwa_deleted_student				= TRUE;
				$copy_wpw1_cwa_deleted_user_master2_wpw1_cwa_deleted_user_master		= TRUE;
				$copy_wpw1_cwa_evaluate_advisor2_wpw1_cwa_evaluate_advisor				= TRUE;
				$copy_wpw1_cwa_joblog2_wpw1_cwa_joblog									= TRUE;
				$copy_wpw1_cwa_new_assessment_data2_wpw1_cwa_new_assessment_data		= TRUE;
				$copy_wpw1_cwa_production_email2_wpw1_cwa_production_email				= TRUE;
				$copy_wpw1_cwa_reminders2_wpw1_cwa_reminders							= TRUE;
				$copy_wpw1_cwa_replacement_requests2_wpw1_cwa_replacement_requests		= TRUE;
				$copy_wpw1_cwa_reports2_wpw1_cwa_reports								= TRUE;
				$copy_wpw1_cwa_student2_wpw1_cwa_student								= TRUE;
				$copy_wpw1_cwa_temp_data2_wpw1_cwa_temp_data							= TRUE;
				$copy_wpw1_cwa_user_master2_wpw1_cwa_user_master						= TRUE;
				$copy_wpw1_cwa_user_master_deleted2_wpw1_cwa_user_master_deleted		= TRUE;
				$copy_wpw1_cwa_user_master_history2_wpw1_cwa_user_master_history		= TRUE;
				$copy_wpw1_usermeta2_wpw1_usermeta										= TRUE;
				$copy_wpw1_users2_wpw1_users											= TRUE;
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
		text-align:right;margin-right:10px;position:relative;display:block;float:left;font-size:larger;}

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
		color:#300;background:#f99;padding:1px;border:solid 1px #f66;
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
		$TableName					= "wpw1_cwa_";
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='width:800px;'>
							<fieldset>
							<legend>Indicate which copies should be made</legend>
							
<tr><td><input type='checkbox' class='formInputButton' id='copymain' name='copymain' value='copymain'>
	<label for='copymain'>Copy All Tables to their backups</label></td></tr>							
<tr><td><input type='checkbox' class='formInputButton' id='restoremain' name='restoremain' value='restoremain'>
	<label for='restoremain'>Restore All Tables from their backups</label></td></tr>							
<tr><td><hr></td></tr>
							<tr><td><u>Main Tables to Backup Tables</u></td></tr>

<tr><td><input type='checkbox' class='formInputButton' id='advisor' name='advisor' value='advisor'>
	<label for='advisor'>Copy advisor to advisor2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='advisorclass' name='advisorclass' value='advisorclass'>
	<label for='advisorclass'>Copy advisorclass to advisorclass2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='audit_log' name='audit_log' value='audit_log'>
	<label for='audit_log'>Copy audit_log to audit_log2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='current_catalog' name='current_catalog' value='current_catalog'>
	<label for='current_catalog'>Copy current_catalog to current_catalog2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='deleted_advisor' name='deleted_advisor' value='deleted_advisor'>
	<label for='deleted_advisor'>Copy deleted_advisor to deleted_advisor2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='deleted_advisorclass' name='deleted_advisorclass' value='deleted_advisorclass'>
	<label for='deleted_advisorclass'>Copy deleted_advisorclass to deleted_advisorclass2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='deleted_student' name='deleted_student' value='deleted_student'>
	<label for='deleted_student'>Copy deleted_student to deleted_student2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='deleted_user_master' name='deleted_user_master' value='deleted_user_master'>
	<label for='deleted_user_master'>Copy deleted_user_master to deleted_user_master2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='evaluate_advisor' name='evaluate_advisor' value='evaluate_advisor'>
	<label for='evaluate_advisor'>Copy evaluate_advisor to evaluate_advisor2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='joblog|joblog2' name='joblog|joblog2' value='joblog|joblog2'>
	<label for='joblog|joblog2'>Copy joblog|joblog2 to joblog|joblog22</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='new_assessment_data' name='new_assessment_data' value='new_assessment_data'>
	<label for='new_assessment_data'>Copy new_assessment_data to new_assessment_data2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='production_email' name='production_email' value='production_email'>
	<label for='production_email'>Copy production_email to production_email2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='reminders' name='reminders' value='reminders'>
	<label for='reminders'>Copy reminders to reminders2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='replacement_requests' name='replacement_requests' value='replacement_requests'>
	<label for='replacement_requests'>Copy replacement_requests to replacement_requests2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='reports' name='reports' value='reports'>
	<label for='reports'>Copy reports to reports2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='student' name='student' value='student'>
	<label for='student'>Copy student to student2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='temp_data' name='temp_data' value='temp_data'>
	<label for='temp_data'>Copy temp_data to temp_data2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='user_master' name='user_master' value='user_master'>
	<label for='user_master'>Copy user_master to user_master2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='user_master_deleted' name='user_master_deleted' value='user_master_deleted'>
	<label for='user_master_deleted'>Copy user_master_deleted to user_master_deleted2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='user_master_history' name='user_master_history' value='user_master_history'>
	<label for='user_master_history'>Copy user_master_history to user_master_history2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='usermeta' name='usermeta' value='usermeta'>
	<label for='usermeta'>Copy usermeta to usermeta2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='users' name='users' value='users'>
	<label for='users'>Copy users to users2</label></td></tr>

							<tr><td><br /><u>Backup Tables to Main Tables</u></td></tr>


<tr><td style='width:800px;'><input type='checkbox' class='formInputButton' id='advisor2' name='advisor2' value='advisor2'> <label for='advisor2'>Copy advisor2 to advisor</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='advisorclass2' name='advisorclass2' value='advisorclass2'> <label for='advisorclass2'>Copy advisorclass2 to advisorclass</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='audit_log2' name='audit_log2' value='audit_log2'> <label for='audit_log2'>Copy audit_log2 to audit_log</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='current_catalog2' name='current_catalog2' value='current_catalog2'> <label for='current_catalog2'>Copy current_catalog2 to current_catalog</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='deleted_advisor2' name='deleted_advisor2' value='deleted_advisor2'> <label for='deleted_advisor2'>Copy deleted_advisor2 to deleted_advisor</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='deleted_advisorclass2' name='deleted_advisorclass2' value='deleted_advisorclass2'> <label for='deleted_advisorclass2'>Copy deleted_advisorclass2 to deleted_advisorclass</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='deleted_student2' name='deleted_student2' value='deleted_student2'> <label for='deleted_student2'>Copy deleted_student2 to deleted_student</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='deleted_user_master2' name='deleted_user_master2' value='deleted_user_master2'> <label for='deleted_user_master2'>Copy deleted_user_master2 to deleted_user_master</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='evaluate_advisor2' name='evaluate_advisor2' value='evaluate_advisor2'> <label for='evaluate_advisor2'>Copy evaluate_advisor2 to evaluate_advisor</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='joblog|joblog22' name='joblog|joblog22' value='joblog|joblog22'> <label for='joblog|joblog22'>Copy joblog|joblog22 to joblog|joblog2</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='new_assessment_data2' name='new_assessment_data2' value='new_assessment_data2'> <label for='new_assessment_data2'>Copy new_assessment_data2 to new_assessment_data</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='production_email2' name='production_email2' value='production_email2'> <label for='production_email2'>Copy production_email2 to production_email</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='reminders2' name='reminders2' value='reminders2'> <label for='reminders2'>Copy reminders2 to reminders</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='replacement_requests2' name='replacement_requests2' value='replacement_requests2'> <label for='replacement_requests2'>Copy replacement_requests2 to replacement_requests</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='reports2' name='reports2' value='reports2'> <label for='reports2'>Copy reports2 to reports</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='student2' name='student2' value='student2'> <label for='student2'>Copy student2 to student</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='temp_data2' name='temp_data2' value='temp_data2'> <label for='temp_data2'>Copy temp_data2 to temp_data</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='user_master2' name='user_master2' value='user_master2'> <label for='user_master2'>Copy user_master2 to user_master</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='user_master_deleted2' name='user_master_deleted2' value='user_master_deleted2'> <label for='user_master_deleted2'>Copy user_master_deleted2 to user_master_deleted</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='user_master_history2' name='user_master_history2' value='user_master_history2'> <label for='user_master_history2'>Copy user_master_history2 to user_master_history</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='usermeta2' name='usermeta2' value='usermeta2'> <label for='usermeta2'>Copy usermeta2 to usermeta</label></td></tr>
<tr><td><input type='checkbox' class='formInputButton' id='users2' name='users2' value='users2'> <label for='users2'>Copy users2 to users</label></td></tr>
<tr><td><hr></tr></td>
							<tr><td><table>
							$testModeOption
							</table>
							<tr><td><input class='formInputButton' type='submit' value='Submit' /></tr></td></table>
							</fieldset></form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}
		$copyArray		= array();
		if ($copy_wpw1_cwa_advisor_wpw1_cwa_advisor2) {
			$copyArray[] = 'wpw1_cwa_advisor|wpw1_cwa_advisor2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_advisor to wpw1_cwa_advisor2<br />";
			}
		}
		if ($copy_wpw1_cwa_advisorclass_wpw1_cwa_advisorclass2) {
			$copyArray[] = 'wpw1_cwa_advisorclass|wpw1_cwa_advisorclass2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_advisorclass to wpw1_cwa_advisorclass2<br />";
			}
		}
		if ($copy_wpw1_cwa_audit_log_wpw1_cwa_audit_log2) {
			$copyArray[] = 'wpw1_cwa_audit_log|wpw1_cwa_audit_log2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_audit_log to wpw1_cwa_audit_log2<br />";
			}
		}
		if ($copy_wpw1_cwa_current_catalog_wpw1_cwa_current_catalog2) {
			$copyArray[] = 'wpw1_cwa_current_catalog|wpw1_cwa_current_catalog2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_current_catalog to wpw1_cwa_current_catalog2<br />";
			}
		}
		if ($copy_wpw1_cwa_deleted_advisor_wpw1_cwa_deleted_advisor2) {
			$copyArray[] = 'wpw1_cwa_deleted_advisor|wpw1_cwa_deleted_advisor2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_deleted_advisor to wpw1_cwa_deleted_advisor2<br />";
			}
		}
		if ($copy_wpw1_cwa_deleted_advisorclass_wpw1_cwa_deleted_advisorclass2) {
			$copyArray[] = 'wpw1_cwa_deleted_advisorclass|wpw1_cwa_deleted_advisorclass2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_deleted_advisorclass to wpw1_cwa_deleted_advisorclass2<br />";
			}
		}
		if ($copy_wpw1_cwa_deleted_student_wpw1_cwa_deleted_student2) {
			$copyArray[] = 'wpw1_cwa_deleted_student|wpw1_cwa_deleted_student2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_deleted_student to wpw1_cwa_deleted_student2<br />";
			}
		}
		if ($copy_wpw1_cwa_deleted_user_master_wpw1_cwa_deleted_user_master2) {
			$copyArray[] = 'wpw1_cwa_deleted_user_master|wpw1_cwa_deleted_user_master2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_deleted_user_master to wpw1_cwa_deleted_user_master2<br />";
			}
		}
		if ($copy_wpw1_cwa_evaluate_advisor_wpw1_cwa_evaluate_advisor2) {
			$copyArray[] = 'wpw1_cwa_evaluate_advisor|wpw1_cwa_evaluate_advisor2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_evaluate_advisor to wpw1_cwa_evaluate_advisor2<br />";
			}
		}
		if ($copy_wpw1_cwa_joblog_wpw1_cwa_joblog2) {
			$copyArray[] = 'wpw1_cwa_joblog|wpw1_cwa_joblog2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_joblog to wpw1_cwa_joblog2<br />";
			}
		}
		if ($copy_wpw1_cwa_new_assessment_data_wpw1_cwa_new_assessment_data2) {
			$copyArray[] = 'wpw1_cwa_new_assessment_data|wpw1_cwa_new_assessment_data2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_new_assessment_data to wpw1_cwa_new_assessment_data2<br />";
			}
		}
		if ($copy_wpw1_cwa_production_email_wpw1_cwa_production_email2) {
			$copyArray[] = 'wpw1_cwa_production_email|wpw1_cwa_production_email2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_production_email to wpw1_cwa_production_email2<br />";
			}
		}
		if ($copy_wpw1_cwa_reminders_wpw1_cwa_reminders2) {
			$copyArray[] = 'wpw1_cwa_reminders|wpw1_cwa_reminders2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_reminders to wpw1_cwa_reminders2<br />";
			}
		}
		if ($copy_wpw1_cwa_replacement_requests_wpw1_cwa_replacement_requests2) {
			$copyArray[] = 'wpw1_cwa_replacement_requests|wpw1_cwa_replacement_requests2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_replacement_requests to wpw1_cwa_replacement_requests2<br />";
			}
		}
		if ($copy_wpw1_cwa_reports_wpw1_cwa_reports2) {
			$copyArray[] = 'wpw1_cwa_reports|wpw1_cwa_reports2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_reports to wpw1_cwa_reports2<br />";
			}
		}
		if ($copy_wpw1_cwa_student_wpw1_cwa_student2) {
			$copyArray[] = 'wpw1_cwa_student|wpw1_cwa_student2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_student to wpw1_cwa_student2<br />";
			}
		}
		if ($copy_wpw1_cwa_temp_data_wpw1_cwa_temp_data2) {
			$copyArray[] = 'wpw1_cwa_temp_data|wpw1_cwa_temp_data2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_temp_data to wpw1_cwa_temp_data2<br />";
			}
		}
		if ($copy_wpw1_cwa_user_master_wpw1_cwa_user_master2) {
			$copyArray[] = 'wpw1_cwa_user_master|wpw1_cwa_user_master2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_user_master to wpw1_cwa_user_master2<br />";
			}
		}
		if ($copy_wpw1_cwa_user_master_deleted_wpw1_cwa_user_master_deleted2) {
			$copyArray[] = 'wpw1_cwa_user_master_deleted|wpw1_cwa_user_master_deleted2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_user_master_deleted to wpw1_cwa_user_master_deleted2<br />";
			}
		}
		if ($copy_wpw1_cwa_user_master_history_wpw1_cwa_user_master_history2) {
			$copyArray[] = 'wpw1_cwa_user_master_history|wpw1_cwa_user_master_history2';
			 if ($doDebug) {
				echo "copying wpw1_cwa_user_master_history to wpw1_cwa_user_master_history2<br />";
			}
		}
		if ($copy_wpw1_usermeta_wpw1_usermeta2) {
			$copyArray[] = 'wpw1_usermeta|wpw1_usermeta2';
			 if ($doDebug) {
				echo "copying wpw1_usermeta to wpw1_usermeta2<br />";
			}
		}
		if ($copy_wpw1_users_wpw1_users2) {
			$copyArray[] = 'wpw1_users|wpw1_users2';
			 if ($doDebug) {
				echo "copying wpw1_users to wpw1_users2<br />";
			}
		}
				
		if ($copy_wpw1_cwa_advisor2_wpw1_cwa_advisor) {
			$copyArray[] = 'wpw1_cwa_advisor2|wpw1_cwa_advisor';
			 if ($doDebug) {
				echo "copying wpw1_cwa_advisor2 to wpw1_cwa_advisor<br />";
			}
		}
		if ($copy_wpw1_cwa_advisorclass2_wpw1_cwa_advisorclass) {
			$copyArray[] = 'wpw1_cwa_advisorclass2|wpw1_cwa_advisorclass';
			 if ($doDebug) {
				echo "copying wpw1_cwa_advisorclass2 to wpw1_cwa_advisorclass<br />";
			}
		}
		if ($copy_wpw1_cwa_audit_log2_wpw1_cwa_audit_log) {
			$copyArray[] = 'wpw1_cwa_audit_log2|wpw1_cwa_audit_log';
			 if ($doDebug) {
				echo "copying wpw1_cwa_audit_log2 to wpw1_cwa_audit_log<br />";
			}
		}
		if ($copy_wpw1_cwa_current_catalog2_wpw1_cwa_current_catalog) {
			$copyArray[] = 'wpw1_cwa_current_catalog2|wpw1_cwa_current_catalog';
			 if ($doDebug) {
				echo "copying wpw1_cwa_current_catalog2 to wpw1_cwa_current_catalog<br />";
			}
		}
		if ($copy_wpw1_cwa_deleted_advisor2_wpw1_cwa_deleted_advisor) {
			$copyArray[] = 'wpw1_cwa_deleted_advisor2|wpw1_cwa_deleted_advisor';
			 if ($doDebug) {
				echo "copying wpw1_cwa_deleted_advisor2 to wpw1_cwa_deleted_advisor<br />";
			}
		}
		if ($copy_wpw1_cwa_deleted_advisorclass2_wpw1_cwa_deleted_advisorclass) {
			$copyArray[] = 'wpw1_cwa_deleted_advisorclass2|wpw1_cwa_deleted_advisorclass';
			 if ($doDebug) {
				echo "copying wpw1_cwa_deleted_advisorclass2 to wpw1_cwa_deleted_advisorclass<br />";
			}
		}
		if ($copy_wpw1_cwa_deleted_student2_wpw1_cwa_deleted_student) {
			$copyArray[] = 'wpw1_cwa_deleted_student2|wpw1_cwa_deleted_student';
			 if ($doDebug) {
				echo "copying wpw1_cwa_deleted_student2 to wpw1_cwa_deleted_student<br />";
			}
		}
		if ($copy_wpw1_cwa_deleted_user_master2_wpw1_cwa_deleted_user_master) {
			$copyArray[] = 'wpw1_cwa_deleted_user_master2|wpw1_cwa_deleted_user_master';
			 if ($doDebug) {
				echo "copying wpw1_cwa_deleted_user_master2 to wpw1_cwa_deleted_user_master<br />";
			}
		}
		if ($copy_wpw1_cwa_evaluate_advisor2_wpw1_cwa_evaluate_advisor) {
			$copyArray[] = 'wpw1_cwa_evaluate_advisor2|wpw1_cwa_evaluate_advisor';
			 if ($doDebug) {
				echo "copying wpw1_cwa_evaluate_advisor2 to wpw1_cwa_evaluate_advisor<br />";
			}
		}
		if ($copy_wpw1_cwa_joblog2_wpw1_cwa_joblog) {
			$copyArray[] = 'wpw1_cwa_joblog2|wpw1_cwa_joblog';
			 if ($doDebug) {
				echo "copying wpw1_cwa_joblog2 to wpw1_cwa_joblog<br />";
			}
		}
		if ($copy_wpw1_cwa_new_assessment_data2_wpw1_cwa_new_assessment_data) {
			$copyArray[] = 'wpw1_cwa_new_assessment_data2|wpw1_cwa_new_assessment_data';
			 if ($doDebug) {
				echo "copying wpw1_cwa_new_assessment_data2 to wpw1_cwa_new_assessment_data<br />";
			}
		}
		if ($copy_wpw1_cwa_production_email2_wpw1_cwa_production_email) {
			$copyArray[] = 'wpw1_cwa_production_email2|wpw1_cwa_production_email';
			 if ($doDebug) {
				echo "copying wpw1_cwa_production_email2 to wpw1_cwa_production_email<br />";
			}
		}
		if ($copy_wpw1_cwa_reminders2_wpw1_cwa_reminders) {
			$copyArray[] = 'wpw1_cwa_reminders2|wpw1_cwa_reminders';
			 if ($doDebug) {
				echo "copying wpw1_cwa_reminders2 to wpw1_cwa_reminders<br />";
			}
		}
		if ($copy_wpw1_cwa_replacement_requests2_wpw1_cwa_replacement_requests) {
			$copyArray[] = 'wpw1_cwa_replacement_requests2|wpw1_cwa_replacement_requests';
			 if ($doDebug) {
				echo "copying wpw1_cwa_replacement_requests2 to wpw1_cwa_replacement_requests<br />";
			}
		}
		if ($copy_wpw1_cwa_reports2_wpw1_cwa_reports) {
			$copyArray[] = 'wpw1_cwa_reports2|wpw1_cwa_reports';
			 if ($doDebug) {
				echo "copying wpw1_cwa_reports2 to wpw1_cwa_reports<br />";
			}
		}
		if ($copy_wpw1_cwa_student2_wpw1_cwa_student) {
			$copyArray[] = 'wpw1_cwa_student2|wpw1_cwa_student';
			 if ($doDebug) {
				echo "copying wpw1_cwa_student2 to wpw1_cwa_student<br />";
			}
		}
		if ($copy_wpw1_cwa_temp_data2_wpw1_cwa_temp_data) {
			$copyArray[] = 'wpw1_cwa_temp_data2|wpw1_cwa_temp_data';
			 if ($doDebug) {
				echo "copying wpw1_cwa_temp_data2 to wpw1_cwa_temp_data<br />";
			}
		}
		if ($copy_wpw1_cwa_user_master2_wpw1_cwa_user_master) {
			$copyArray[] = 'wpw1_cwa_user_master2|wpw1_cwa_user_master';
			 if ($doDebug) {
				echo "copying wpw1_cwa_user_master2 to wpw1_cwa_user_master<br />";
			}
		}
		if ($copy_wpw1_cwa_user_master_deleted2_wpw1_cwa_user_master_deleted) {
			$copyArray[] = 'wpw1_cwa_user_master_deleted2|wpw1_cwa_user_master_deleted';
			 if ($doDebug) {
				echo "copying wpw1_cwa_user_master_deleted2 to wpw1_cwa_user_master_deleted<br />";
			}
		}
		if ($copy_wpw1_cwa_user_master_history2_wpw1_cwa_user_master_history) {
			$copyArray[] = 'wpw1_cwa_user_master_history2|wpw1_cwa_user_master_history';
			 if ($doDebug) {
				echo "copying wpw1_cwa_user_master_history2 to wpw1_cwa_user_master_history<br />";
			}
		}
		if ($copy_wpw1_usermeta2_wpw1_usermeta) {
			$copyArray[] = 'wpw1_usermeta2|wpw1_usermeta';
			 if ($doDebug) {
				echo "copying wpw1_usermeta2 to wpw1_usermeta<br />";
			}
		}
		if ($copy_wpw1_users2_wpw1_users) {
			$copyArray[] = 'wpw1_users2|wpw1_users';
			 if ($doDebug) {
				echo "copying wpw1_users2 to wpw1_users<br />";
			}
		}

		
		
		
		if ($doDebug) {
			echo "<br />copyArray:<br /><pre>";
			print_r($copyArray);
			echo "</pre><br />";
		}

		echo "<h3>$jobname</h3>";
		foreach ($copyArray as $myValue) {
			$myArray			= explode("|",$myValue);
			$sourceTable		= $myArray[0];
			$destinationTable	= $myArray[1];
				echo "<p>Copying $sourceTable to $destinationTable<br />";

			// truncate the destination table
			$result				= $wpdb->query("TRUNCATE $destinationTable");
			if ($result === FALSE) {
				echo "Truncating $destinationTable failed<br />
						Result: $result<br />
						wpdb->last_query: " . $wpdb->last_query . "<br />";
					if ($wpdb->last_error != '') {
						echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
					}
			} else {
				echo "Successfully truncated $destinationTable<br />";
	
				//// can proceed with the copy
				$result			= $wpdb->query("insert into $destinationTable select * from $sourceTable");
				if ($result === FALSE) {
					echo "Copying from $sourceTable to $destinationTable failed<br />
							Result: $result<br />
							wpdb->last_query: " . $wpdb->last_query . "<br />";
						if ($wpdb->last_error != '') {
							echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
						}
				} else {
					$sql		= "select count(*) from $destinationTable";
					$myInt		= $wpdb->get_var($sql);
					echo "Successfully copied from $sourceTable to $destinationTable<br />
						  $destinationTable has $myInt records<br />";
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
add_shortcode ('make_or_restore_backups', 'make_or_restore_backups_func');
