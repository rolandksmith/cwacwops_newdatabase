function manage_reminders_func() {

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
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
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
	$theURL						= "$siteURL/cwa-manage-reminders/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Manage Reminders V$versionNumber";
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
			if ($str_key 		== "inp_method") {
				$inp_method	 = $str_value;
				$inp_method	 = filter_var($inp_method,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "effective_date") {
				$effective_date = $str_value;
				$effective_date = filter_var($effective_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "close_date") {
				$close_date = $str_value;
				$close_date = filter_var($close_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "resolved_date") {
				$resolved_date = $str_value;
				$resolved_date = filter_var($resolved_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "send_reminder") {
				$send_reminder = $str_value;
				$send_reminder = filter_var($send_reminder,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "call_sign") {
				$call_sign = $str_value;
				$call_sign = filter_var($call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "role") {
				$role = $str_value;
				$role = filter_var($role,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "email_text") {
				$email_text = $str_value;
//				$email_text = filter_var($email_text,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "reminder_text") {
				$reminder_text = $str_value;
//				$reminder_text = filter_var($reminder_text,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "send_immediately") {
				$send_immediately = $str_value;
				$send_immediately = filter_var($send_immediately,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "send_once") {
				$send_once = $str_value;
				$send_once = filter_var($send_once,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "resolved") {
				$resolved = $str_value;
				$resolved = filter_var($resolved,FILTER_UNSAFE_RAW);
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
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td colspan='2'><input type='radio' class='formInputButton' name='inp_method' value='add'> Add a new reminder</td></tr>
							<tr><td colspan='2'><input type='radio' class='formInputButton' name='inp_method' value='modify'> Modify an exising reminder</td></tr>
							<tr><td colspan='2'><input type='radio' class='formInputButton' name='inp_method' value='delete'> Delete a reminder</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2 with inp_method of $inp_method<br />";
		}

		if ($inp_method == 'add') {
			$content			.= "<h3>$jobname</h3>
									<h4>Add A New Reminder</h4>
									<p>Leave Effective Date blank to set the current date & time as the effective date.<br />
									For the Close Date, enter the number of days from the effective date that the reminder is to close. 
									If Close Date is blank, then ten days will be assumed</p>
									<form method='post' action='$theURL' 
									name='menu_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='5'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<table style='width:900px;'>
									<tr><td style='vertical-align:top;width:150px;'>Effective Date</td>
										<td><input type='text' class='formInputText' size='20' maxlength='20' name='effective_date'></td></tr>
									<tr><td style='vertical-align:top;'>Close Date</td>
										<td><input type='text' class='formInputText' size='20' maxlength='20' name='close_date'></td></tr>
									<tr><td style='vertical-align:top;'>Resolved Date</td>
										<td><input type='text' class='formInputText' size='20' maxlength='20' name='resolved_date'></td></tr>
									<tr><td style='vertical-align:top'>Send Reminder</td>
										<td><input type='radio' class='formInputButton' name='send_reminder' value='Y' checked> Yes<br />
											<input type='radio' class='formInputButton' name='send_reminder' value='N'> No</td></tr>
									<tr><td style='vertical-align:top'>Send Once</td>
										<td><input type='radio' class='formInputButton' name='send_once' value='Y'> Yes<br />
											<input type='radio' class='formInputButton' name='send_once' value='N' checked> No</td></tr>
									<tr><td style='vertical-align:top;'>Callsign</td>
										<td><input type='text' class='formInputText' size='20' maxlength='20' name='call_sign'></td></tr>
									<tr><td style='vertical-align:top;'>Role</td>
										<td><input type='text' class='formInputText' size='20' maxlength='20' name='role'></td></tr>
									<tr><td style='vertical-align:top;'>Email Text</td>
										<td><textarea name='email_text' class='formInputText' rows='5' cols='50'></textarea></td></tr>
									<tr><td style='vertical-align:top;'>Reminder Text</td>
										<td><textarea name='reminder_text' class='formInputText' rows='5' cols='50'></textarea></td></tr>
									<tr><td style='vertical-align:top;'>Resolved</td>
										<td><input type='radio' class='formInputButton' name='resolved' value='Y'> Yes<br />
											<input type='radio' class='formInputButton' name='resolved' value='N' checked> No</td></tr>
									<tr><td style='vertical-align:top;'>Send Immediately</td>
										<td><input type='radio' class='formInputButton' name='send_immediately' value='Y'> Yes<br />
											<input type='radio' class='formInputButton' name='send_immediately' value='N' checked> No</td></tr>
									<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
									</form>";
	

			
		} elseif ($inp_method = 'modify' || $inp_method == 'delete') {
			$content		.= "<h3>$jobname</h3>
								<form method='post' action='$theURL' 
								name='menu_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='3'>
								<input type='hidden' name='inp_method' value='$inp_method'>
								<table style='width:1200px;'>
								<legend>Select Desired Reminder</legend>
								<fieldset>
								<tr><th>Select</th>
									<th>ID</th>
									<th>Effective Date</th>
									<th>Close Date</th>
									<th>Resolved Date</th>
									<th>Send Reminder</th>
									<th>Callsign</th>
									<th>Role</th>
									<th>Email Text</th>
									<th>Reminder Text</th>
									<th>Resolved</th>
									<th>Token</th>
									<th>Date Created</th>
									<th>Date Modified</th></tr>";
								
			// get a list of reminders
			$sql			= "select * from wpw1_cwa_reminders 
								order by date_created";	
			$reminderResult	= $wpdb->get_results($sql);
			if ($reminderResult === FALSE) {
				$lastError	= $wpdb->last_error;
				$lastQuery	=	$wpdb->last_query;
				if ($doDebug) {
					echo "unable to access wpw1_cwa_reminders. Error:$lastError<br />$lastQuery<br />";
				}
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
	
						$content			.= "<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_id' value='$record_id'></td>
												<td style='vertical-align:top;text-align:center;'>$record_id</td>
												<td style='vertical-align:top;'>$effective_date</td>
												<td style='vertical-align:top;'>$close_date</td>
												<td style='vertical-align:top;'>$resolved_date</td>
												<td style='vertical-align:top;text-align:center;'>$send_reminder</td>
												<td style='vertical-align:top;'>$call_sign</td>
												<td style='vertical-align:top;'>$role</td>
												<td style='vertical-align:top;width:250px;'>$email_text</td>
												<td style='vertical-align:top;width:250px;'>$reminder_text</td>
												<td style='vertical-align:top;text-align:center;'>$resolved</td>
												<td style='vertical-align:top;'>$token</td>
												<td style='vertical-align:top;'>$date_created</td>
												<td style='vertical-align:top;'>$date_modified</td></tr>";
		
					}
				}
				$content		.= "<tr><td><input class='formInputButton' type='submit' value='Submit' /></td></tr>
									</table></form>";
			}
		}
	} elseif ("3" == $strPass) {
	
	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 5 with:<br/>
				  effective_date = $effective_date<br />
				  close_date = $close_date<br />
				  resolved_date = $resolved_date<br />
				  send_reminder: $send_reminder<br />
				  send_once: $send_once<br />
				  call_sign: $call_sign<br />
				  role: $role<br />
				  email_text: $email_text<br />
				  reminder_text: $reminder_text<br />
				  send_immediately: $send_immediately<br />
				  resolved: $resolved<br />";
		}
		
		if ($effective_date == '') {
			$effective_date		= date('Y-m-d H:i:s');
		}
		if ($close_date != '') {
			$close_date = date('Y-m-d H:i:s', strtotime($effective_date . " + $close_date days"));
		} else {
			$close_date = date('Y-m-d H:i:s', strtotime($effective_date . " + 10 days"));			
		}
		// package up the data and call function to add it to the reminders table
		$inputParams		= array("effective_date|$effective_date|s",
									"close_date|$close_date|s",
									"resolved_date|$resolved_date|s",
									"send_reminder|$send_reminder|s",
									"send_once|$send_once|s",
									"call_sign|$call_sign|s",
									"role|$role|s",
									"email_text|$email_text|s",
									"reminder_text|$reminder_text|s",
									"resolved|$resolved|s",
									"token||s");
		$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
		if ($insertResult[0] === FALSE) {
			if ($doDebug) {
				echo "inserting reminder failed: $insertResult[1]<br />";
			}
			$content		.= "Inserting reminder failed: $insertResult[1]<br />";
		} else {
			$content		.= "Reminder successfully added<br />";
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
add_shortcode ('manage_reminders', 'manage_reminders_func');
