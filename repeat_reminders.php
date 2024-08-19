function repeat_reminders_func() {

/*	Repeat Reminders
	
	This job is usually run as part of the daily cron process
	
	Reads the reminders table looking for any unresolved reminders (resolved not Y)
	If the reminder has a send_reminder set (not equal N and a number greater than 0) 	
	and the email_text is not blank
		check to see if the repeat is due 
		If so, send the reminder and set the repeat_sent_date
		
	If today is > close date, set resolved date and set resolved to Y
		
	Finding if repeat is due:
		If repeat_sent_date is empty, the first repeat has not been sent
			if effective_date + send_reminder days <= current date
				and today is <= close_date
					repeat is due
		else if repeat_sent_date is not empty
			if repeat_sent_date + send_reminder days <= current date
				and today is <= close_date 
					repeat is due
		
		
*/
	global $wpdb;

	$doDebug						= TRUE;
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
	$userRole			= $initializationArray['userRole'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$theURL						= "$siteURL/cwa-repeat-reminders/";
	$jobname					= "Repeat Reminders V$versionNumber";

	
	
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

	$runTheJob				= TRUE;
	if ($userName != '') {
		$content 			.= "<h3>$jobname Executed by $userName</h3>";
	} else {
		$content			.= "<h3>$jobname Automatically Executed</h3>";
		$userName			= "CRON";
		$dst				= date('I');
		if ($dst == 0) {
			$checkBegin 	= strtotime('13:50:00');
			$checkEnd 		= strtotime('14:30:00');
			$thisTime 		= date('H:i:s');
		
		} else {
			$checkBegin 	= strtotime('12:50:00');
			$checkEnd 		= strtotime('13:30:00');
			$thisTime 		= date('H:i:s');
		}
		$nowTime = strtotime($thisTime);
		if ($nowTime >= $checkBegin && $nowTime <= $checkEnd) {
			$runTheJob = TRUE;
		} else {
			$runTheJob = FALSE;
			$userName	= "CRON Aborted";
			if ($doDebug) {
				echo "runTheJob is FALSE<br />";
			}
		}
	}
	if ($runTheJob) {
		if ($doDebug) {
			echo "<br />Function Started<br />";
		}	

		// get all unresolved reminders where email_text is not blank
		$sql			= "select * from wpw1_cwa_reminders 
							where resolved != 'Y' 
							and send_reminder != 'N' 
							and send_reminder != '' 
							and email_text != '' 
							order by effective_date";
		$reminderResult	= $wpdb->get_results($sql);
		if ($reminderResult === FALSE) {
			$lastError	= $wpdb->last_error;
			$content	.= "Attempting to ready wpw1_cwa_reminders failed.<br />
							Query: $sql<br />
							Error: $lastError<br />";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "Ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($reminderResult as $reminderResultRow) {
					$record_id				= $reminderResultRow->record_id;
					$effective_date			= $reminderResultRow->effective_date;
					$close_date				= $reminderResultRow->close_date;
					$resolved_date			= $reminderResultRow->resolved_date;
					$send_reminder			= $reminderResultRow->send_reminder;
					$send_once				= $reminderResultRow->send_once;
					$call_sign				= $reminderResultRow->call_sign;
					$role					= $reminderResultRow->role;
					$email_text				= $reminderResultRow->email_text;
					$reminder_text			= $reminderResultRow->reminder_text;
					$resolved				= $reminderResultRow->resolved;
					$token					= $reminderResultRow->token;
					$repeat_sent_date		= $reminderResultRow->repeat_sent_date;
					$date_created			= $reminderResultRow->date_created;
					$date_modified			= $reminderResultRow->date_modified;
					
					$doUpdate				= FALSE;
					$updateParams			= array();
					$nowDate				= date('Y-m-d H:i:s');
					$emailReminder			= FALSE;
					if ($nowDate >= $effective_date && $nowDate <= $close_date) {
						if ($doDebug) {
							echo "<br />today $nowDate >= $effective_date (effective_date) and <= $close_date (close_date)<br />";
						}
						// just for grins, see if resolved_date is set. If so, turn on resolved
						if ($resolved_date != '') {
							$content		.= "Reminder $record_id has a resolved_date of $resolved_date but resolved is not Y. Fixing<br />";
							$updateParams	= "resolved|Y|s";
							$doUpdate		= TRUE;
						} else {
							if ($doDebug) {
								echo "Have a repeat candidate:<br />
										record_id: $record_id<br />
										effective_date: $effective_date<br />
										close_date: $close_date<br />
										send_reminder: $send_reminder<br />
										send_once: $send_once<br />
										call_sign: $call_sign<br />
										role: $role<br />
										repeat_sent_date: $repeat_sent_date<br />";
							}
							if ($repeat_sent_date == '') {
								$testStr		= strtotime("$effective_date + $send_reminder days");
								$testDate		= date('Y-m-d H:i:s', $testStr);
								if ($testDate <= $nowDate) {
									if ($doDebug) {
										echo "effective_date $effective_date + $send_reminder is testDate $testDate is <= $nowDate. Need to send reminder<br />";
									}
									$emailReminder	= TRUE;
									
								}
							} else {				// a previous reminder has been sent
								$testStr		= strtotime("$repeat_sent_date + $send_reminder days");
								$testDate		= date('Y-m-d H:i:s', $testStr);
								if ($testDate <= $nowDate) {
									if ($doDebug) {
										echo "effective_date $effective_date + $send_reminder is testDate $testDate is <= $nowDate. Need to send reminder<br />";
									}
									$emailReminder	= TRUE;
									
								}
							}
						}
						if ($doDebug) {
							echo "reminder processed.<br />";
							if ($doUpdate) {
								echo "reminder will be updated<br />";
							}
							if ($emailReminder) {
								echo "reminder will be emailed<br />";
							}
						}
					}
					
				}
			} else {
				$content	.= "No unresolved reminders found<br />";
			}
		}
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
/*
	///// uncomment if the code to save a report is needed
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Current Student and Advisor Assignments<br />";
		}
		$storeResult	= storeReportData_func("Current Student and Advisor Assignments",$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports pod as $storeResult";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}
*/
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
add_shortcode ('repeat_reminders', 'repeat_reminders_func');
