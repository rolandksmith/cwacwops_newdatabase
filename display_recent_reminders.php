function display_recent_reminders_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
/*
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
*/
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
	
//	CHECK THIS!								//////////////////////
//	if ($validUser == "N") {
//		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
//	}

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
	$theURL						= "$siteURL/cwa-display-recent-reminders/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Display Recent Reminders V$versionNumber";
	$effective_date				= "";
	$close_date					= "";
	$resolved_date				= "";
	$send_reminder				= "";
	$send_once					= "";
	$call_sign					= "";
	$role						= "";
	$email_text					= "";
	$reminder_text				= "";
	$resolved					= "";
	$inp_callsign				= "";
	$offset						= 0;
	$totalRecords				= 0;

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
				$inp_callsign	 = $str_value;
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "offset") {
				$offset = $str_value;
				$offset = filter_var($offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "totalRecords") {
				$totalRecords = $str_value;
				$totalRecords = filter_var($totalRecords,FILTER_UNSAFE_RAW);
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
		$TableName					= "wpw1_cwa_reminders";
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_reminders2";
	}



	if ("1" == $strPass) {
		if ($userRole == 'administrator') {
			$userNameUC		= strtoupper($userName);
			$content 		.= "<h3>$jobname (Administrator Role)</h3>
								<p>Displays reminders for the user starting with the newest 
								reminder. Displays 25 reminders at a time</p>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='2'>
								<table style='border-collapse:collapse;'>
								<tr><td>Advisor Callsign</td>
									<td><input type='text' class='formInputText' size='15' maxlength='30' name='inp_callsign' value='$userNameUC'></td></tr>
								$testModeOption
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form></p>";
		} else {
			$inp_callsign	= $userName;
			$strPass		= "2";
			$inp_mode		= 'Production';
			$inp_verbose	= 'N';
		}	
	}
///// Pass 2 -- do the work


	if ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2 with inp_callsign of $inp_callsign and offset of $offset and totalRecords of $totalRecords<br />";
		}
		
		if ($offset == 0) {			/// first time through get total number of reminders
			$totalRecords		= $wpdb->get_var("select count(record_id) 
													from wpw1_cwa_reminders 
													where call_sign = '$inp_callsign'");
			if ($doDebug) {
				echo "retrieved a total records count of $totalRecords<br />";
			}
		}
		$thisOffset		= $offset + 1;
		$thisSet		= $offset + 25;
		if ($thisSet > $totalRecords) {
			$thisSet	= $totalRecords;
		}
		$content		.= "<h3>$jobname for $inp_callsign</h3>
							<table style='width:1200px;'>
							<legend>Records $thisOffset to $thisSet of $totalRecords records</legend>
							<fieldset>
							<tr><th>Callsign</th>
								<th>Role</th>
								<th>Reminder Text</th>
								<th>Resolved</th>
								<th>Date Created</th></tr>";
								
			// get a list of reminders
			$sql			= "select * from wpw1_cwa_reminders 
								where call_sign = '$inp_callsign' 
								order by date_created DESC 
								LIMIT $offset,25";	
			$reminderResult	= $wpdb->get_results($sql);
			if ($reminderResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					foreach($reminderResult as $reminderRow) {
						$record_id			= $reminderRow->record_id;
						$effective_date		= $reminderRow->effective_date;
						$close_date			= $reminderRow->close_date;
						$resolved_date		= $reminderRow->resolved_date;
						$send_reminder		= $reminderRow->send_reminder;
						$call_sign			= $reminderRow->call_sign;
						$role				= $reminderRow->role;
						$email_text			= $reminderRow->email_text;
						$reminder_text		= $reminderRow->reminder_text;
						$resolved			= $reminderRow->resolved;
						$token				= $reminderRow->token;
						$date_created		= $reminderRow->date_created;
						$date_modified		= $reminderRow->date_modified;
	
						$content			.= "<tr><td style='vertical-align:top;'>$call_sign</td>
													<td style='vertical-align:top;'>$role</td>
													<td style='vertical-align:top;'>$reminder_text</td>
													<td style='vertical-align:top;'>$resolved</td>
													<td style='vertical-align:top;'>$date_created</td></tr>";
						$offset++;
					}
				}
				if ($offset < $totalRecords) {
					$content		.= "<form method='post' action='$theURL' 
										name='menu_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='2'>
										<input ty[e='hidden' name='inp_callsign' value='$inp_callsign'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='offset' value='$offset'>
										<input type='hidden' name='totalRecords' value='$totalRecords'>	
										<tr><td><input class='formInputButton' type='submit' value='Next 25 Reminders' /></td></tr>
										</fieldset>
										</table></form>";
				} else {		// no more records
					$content		.= "</fieldset></table>";
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
add_shortcode ('display_recent_reminders', 'display_recent_reminders_func');
