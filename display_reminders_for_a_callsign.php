function display_reminders_for_a_callsign_func() {

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
	$theURL						= "$siteURL/cwa-display-reminders-for-a-callsign/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Display Reminders for a Callsign V$versionNumber";
	$lines						= 0;
	$recordsPrinted				= 0;
	$rightNow					= date('Y-m-d H:i:s');

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
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 	== "inp_type") {
				$inp_type	 = $str_value;
				$inp_type	 = filter_var($inp_type,FILTER_UNSAFE_RAW);
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
		$reminderTableName			= "wpw1_cwa_reminders2";
	} else {
		$extMode					= 'pd';
		$reminderTableName			= "wpw1_cwa_reminders";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Callsign</td>
								<td><input type='text' class='formInputText' name='inp_callsign' size='15' maxlength='16'></td></tr>
							<tr><td>Type Run</td>
								<td><input type='radio' class='formInputButton' name='inp_type' value='open' checked>Open Reminders Only<br />
									<input type='radio' class='formInputButton' name='inp_type' value='all'>All Reminders</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2 with inp_callsign of $inp_callsign and inp_type of $inp_type<br />";
		}
	
		$content			= "<h3>$jobname</h3>";
		
		if ($inp_type == 'all') {
			$content		.= "<p>Listing all reminders</p>";
			$sql			= "select * from $reminderTableName 
								where call_sign = '$inp_callsign' 
								order by date_created";
		} else {
			$content		.= "<p>Listing open reminders only</p>";
			$sql			= "select * from $reminderTableName 
								where call_sign = '$inp_callsign' 
									and resolved != 'Y' 
									and close_date > '$rightNow'  
								order by date_created";
		}
								
		$remindersResult	= $wpdb->get_results($sql);
 		if ($remindersResult === FALSE) {
 			handleWPDBError($jobname,$doDebug);
 		} else {
 			$numRows		= $wpdb->num_rows;
 			if ($doDebug) {
 				echo "ran $sql<br />and retrieved $numRows rows<br />";
 			}
 			if ($numRows > 0) {
 				$content				.= "<table style='width:1000px;'>";
 				foreach($remindersResult as $remindersRow) {
 					$record_id			= $remindersRow->record_id;
 					$effective_date		= $remindersRow->effective_date;
 					$close_date			= $remindersRow->close_date;
 					$resolved_date		= $remindersRow->resolved_date;
 					$send_reminder		= $remindersRow->send_reminder;
 					$send_once			= $remindersRow->send_once;
 					$call_sign			= $remindersRow->call_sign;
 					$role				= $remindersRow->role;
 					$email_text			= $remindersRow->email_text;
 					$reminder_text		= $remindersRow->reminder_text;
 					$resolved			= $remindersRow->resolved;
 					$token				= $remindersRow->token;
 					$repeat_sent_date	= $remindersRow->repeat_sent_date;
 					$date_created		= $remindersRow->date_created;
 					$date_modified		= $remindersRow->date_modified;
 					
 					if ($doDebug) {
 						echo "got a reminder created $date_created<br />";
 					}
 					
 					
 					$content			.= "<tr><td style='vertical-align:top; width:250px;'><b>Record id:</b> $record_id</td>
 												<td colspan='3'>$reminder_text</td></tr>
 											<tr><td style='vertical-align:top;'><b>Effective Date:</b> $effective_date</td>
 												<td style='vertical-align:top; width:250px;'><b>Close Date:</b> $close_date</td>
 												<td style='vertical-align:top; width:250px;'><b>Resolved:</b> $resolved</td>
 												<td style='vertical-align:top; width:250px;'><b>Resolved Date:</b> $resolved_date</td></tr>
 											<tr><td style='vertical-align:top;'><b>Send Reminder:</b> $send_reminder</td>
 												<td style='vertical-align:top;'><b>Send Once:</b> $send_once</td>
 												<td style='vertical-align:top;'><b>Role:</b> $role</td>
 												<td style='vertical-align:top;'><b>Callsign:</b> $call_sign</td></tr>
 											<tr><td style='vertical-align:top;'><b>Token:</b> $token</td>
 												<td style='vertical-align:top;'><b>Repeat Sent Date:</b> $repeat_sent_date</td>
 												<td style='vertical-align:top;'><b>Date Created:</b> $date_created</td>
 												<td style='vertical-align:top;'><b>Date Modifed:</b> $date_modified</td></tr>
 											<tr><td colspan='4'><hr></td></tr>";
 					$recordsPrinted++;
 				}
 				$content				.= "</table>
 											<p>$recordsPrinted records printed</p>";
 			} else {
 				$content	.= "No data found for callsign $inp_callsign";
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
add_shortcode ('display_reminders_for_a_callsign', 'display_reminders_for_a_callsign_func');
