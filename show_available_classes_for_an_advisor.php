function show_availble_classes_for_an_advisor_func() {

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
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
		$doDebug			= FALSE;
		$testMode			= FALSE;
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
	$theURL						= "$siteURL/cwa-show-available-classes-for-an-advisor/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Show Available Classes for an Advisor V$versionNumber";

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
			if ($str_key 	== "inp_advisor") {
				$inp_advisor	= $str_value;
				$inp_advisor	= filter_var($inp_advisor,FILTER_UNSAFE_RAW);
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
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
	} else {
		$extMode					= 'pd';
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Program will show information about the available classes 
							for an advisor. The class size and available seats are 
							not taken into consideration.
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Advisor Callsign</td>
								<td><input type='text' class='formInputText' name='inp_advisor' size='15' amxlenth='15'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br >arrived at pass 2 with inp_advisor of $inp_advisor<br />";
		}	
		
		$theSemester		= $currentSemester;
		if ($currentSemester == 'Not in Session') {
			$theSemester	= $nextSemester;
		}
	
	$content		.= "<h3>$jobname</h3>
						<table style='width:600px;'>
						<tr><th colspan = '3'>$inp_advisor Available Classes</th></tr>
						<tr><th>Class</th>
							<th>level</th>
							<th>UTC Teaching Schedule</th></tr>";
	$sql			= "select * from $advisorClassTableName 
						where advisor_call_sign = '$inp_advisor' 
						and semester = '$theSemester' 
						order by sequence";
		$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$lastError			= $wpdb->last_error;
			if ($lastError != '') {
				handleWPDBError($jobname,$doDebug);
				$content		.= "Fatal program error. System Admin has been notified";
				return $content;
			}
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
					
					$content		.= "<tr><td>$advisorClass_sequence</td>'
											<td>$advisorClass_level</td>
											<td>$advisorClass_class_schedule_days_utc  $advisorClass_class_schedule_times_utc</td></tr>";
				}
				$content 	.= "</table>";
			} else {
				$content	.= "<tr><td colspan='3'>No Classes Found</td></tr>";
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
add_shortcode ('show_availble_classes_for_an_advisor', 'show_availble_classes_for_an_advisor_func');
