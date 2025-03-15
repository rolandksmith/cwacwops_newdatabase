function display_and_update_reminders_func() {

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
	if ($userName == '') {
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
	$theURL						= "$siteURL/cwa-display-and-update-reminders/";
	$record_id					= '';	
	$do_add						= 'N';
	$jobname					= "Display and Update Reminders V$versionNumber";

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
			if ($str_key == 'record_id') {
				$record_id		= $str_value;
				$record_id		= filter_var($record_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'submit') {
				$submit		= $str_value;
				$submit		= filter_var($submit,FILTER_UNSAFE_RAW);
			}
			if ($str_key == 'do_add') {
				$do_add		= $str_value;
				$do_add		= filter_var($do_add,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_effective_date") {
				$inp_reminders_effective_date = $str_value;
				$inp_reminders_effective_date = filter_var($inp_reminders_effective_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_close_date") {
				$inp_reminders_close_date = $str_value;
				$inp_reminders_close_date = filter_var($inp_reminders_close_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_resolved_date") {
				$inp_reminders_resolved_date = $str_value;
				$inp_reminders_resolved_date = filter_var($inp_reminders_resolved_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_send_reminder") {
				$inp_reminders_send_reminder = $str_value;
				$inp_reminders_send_reminder = filter_var($inp_reminders_send_reminder,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_send_once") {
				$inp_reminders_send_once = $str_value;
				$inp_reminders_send_once = filter_var($inp_reminders_send_once,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_call_sign") {
				$inp_reminders_call_sign = $str_value;
				$inp_reminders_call_sign = filter_var($inp_reminders_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_role") {
				$inp_reminders_role = $str_value;
				$inp_reminders_role = filter_var($inp_reminders_role,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_email_text") {
				$inp_reminders_email_text = $str_value;
//				$inp_reminders_email_text = filter_var($inp_reminders_email_text,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_reminder_text") {
				$inp_reminders_reminder_text = stripslashes($str_value);
//				$inp_reminders_reminder_text = filter_var($inp_reminders_reminder_text,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_resolved") {
				$inp_reminders_resolved = $str_value;
				$inp_reminders_resolved = filter_var($inp_reminders_resolved,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_token") {
				$inp_reminders_token = $str_value;
				$inp_reminders_token = filter_var($inp_reminders_token,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_repeat_sent_date") {
				$inp_reminders_repeat_sent_date = $str_value;
				$inp_reminders_repeat_sent_date = filter_var($inp_reminders_repeat_sent_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_reminders_date_modified") {
				$inp_reminders_date_modified = $str_value;
				$inp_reminders_date_modified = filter_var($inp_reminders_date_modified,FILTER_UNSAFE_RAW);
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
		$remindersTableName			= "wpw1_cwa_reminders2";
	} else {
		$extMode					= 'pd';
		$remindersTableName			= "wpw1_cwa_reminders";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Reminder ID</td>
								<td><input type='text' class='formInputText' name='record_id' size='10' maxlength='10'></td></tr>
							<tr><td>Add a Reminder</td>
								<td><input type='radio' class='formInpuButton' name='do_add' value='Y'>Yes<br />
									<input type='radio' class='formInpuButton' name='do_add' checked value='N'>No</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2 with record_id of $record_id<br />";
		}
	
		$content	.= "<h3>$jobname</h3>";
		
		if ($do_add == 'Y') {
			$content	.= "<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='10'>
							<table style='width:1000px;'>
							<tr><td>reminders_record_id</td>
								<td>$reminders_record_id</td></tr>
							<tr><td>reminders_effective_date</td>
								<td><input type='text' class='formInputText' name='inp_reminders_effective_date' length='20' 
								maxlength='20' ></td></tr>
							<tr><td>reminders_close_date</td>
								<td><input type='text' class='formInputText' name='inp_reminders_close_date' length='20' 
								maxlength='20' ></td></tr>
							<tr><td>reminders_resolved_date</td>
								<td><input type='text' class='formInputText' name='inp_reminders_resolved_date' length='20' 
								maxlength='20' ></td></tr>
							<tr><td>reminders_send_reminder</td>
								<td><input type='text' class='formInputText' name='inp_reminders_send_reminder' length='1' 
								maxlength='1' value='N'></td></tr>
							<tr><td>reminders_send_once</td>
								<td><input type='text' class='formInputText' name='inp_reminders_send_once' length='1' 
								maxlength='1' value='Y'></td></tr>
							<tr><td>reminders_call_sign</td>
								<td><input type='text' class='formInputText' name='inp_reminders_call_sign' length='15' 
								maxlength='15' ></td></tr>
							<tr><td>reminders_role</td>
								<td><input type='text' class='formInputText' name='inp_reminders_role' length='20' 
								maxlength='20' ></td></tr>
							<tr><td>reminders_email_text</td>
								<td><textarea class='formInputText' name='inp_reminders_email_text' rows='5' cols='50'></textarea></td></tr>
							<tr><td>reminders_reminder_text</td>
								<td><textarea class='formInputText' name='inp_reminders_reminder_text' rows='5' cols='50'></textarea></td></tr>
							<tr><td>reminders_resolved</td>
								<td><input type='text' class='formInputText' name='inp_reminders_resolved' length='1' 
								maxlength='1' value='N'></td></tr>
							<tr><td>reminders_token</td>
								<td><input type='text' class='formInputText' name='inp_reminders_token' length='20' 
								maxlength='20' ></td></tr>
							<tr><td>reminders_repeat_sent_date</td>
								<td><input type='text' class='formInputText' name='inp_reminders_repeat_sent_date' length='20' 
								maxlength='20' ></td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' name='submit' value='Add Reminder' /></td></tr>
							</table></form>";
		
		} else {
		
			// get the record to update
			$sql		= "select * from $remindersTableName 
							where record_id = $record_id";
			$wpw1_cwa_reminders		= $wpdb->get_results($sql);
			if ($wpw1_cwa_reminders === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numRows 		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br /> and found $numRows rows in $remindersTableName table";
				}
				if ($numRows > 0) {
					foreach ($wpw1_cwa_reminders as $remindersRow) {
						$reminders_record_id 		= $remindersRow -> record_id;
						$reminders_effective_date	= $remindersRow -> effective_date;
						$reminders_close_date 		= $remindersRow -> close_date;
						$reminders_resolved_date 	= $remindersRow -> resolved_date;
						$reminders_send_reminder 	= $remindersRow -> send_reminder;
						$reminders_send_once 		= $remindersRow -> send_once;
						$reminders_call_sign 		= $remindersRow -> call_sign;
						$reminders_role 			= $remindersRow -> role;
						$reminders_email_text 		= stripslashes($remindersRow -> email_text);
						$reminders_reminder_text 	= stripslashes($remindersRow -> reminder_text);
						$reminders_resolved 		= $remindersRow -> resolved;
						$reminders_token 			= $remindersRow -> token;
						$reminders_repeat_sent_date = $remindersRow -> repeat_sent_date;
						$reminders_date_created 	= $remindersRow -> date_created;
						$reminders_date_modified 	= $remindersRow -> date_modified;
	
						$content	.= "<form method='post' action='$theURL' 
										name='selection_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='3'>
										<input type='hidden' name='record_id' value='$reminders_record_id'>
										<table style='width:1000px;'>
										<tr><td>Reminders Record ID</td>
											<td><a href='$theURL/?record_id=$record_id&strpass=3'>$reminders_record_id</a></td></tr>
										<tr><td>Reminders Effective Date</td>
											<td>$reminders_effective_date</td></tr>
										<tr><td>Reminders Close Date</td>
											<td>$reminders_close_date</td></tr>
										<tr><td>Reminders Resolved Date</td>
											<td>$reminders_resolved_date</td></tr>
										<tr><td>Reminders Send Reminder</td>
											<td>$reminders_send_reminder</td></tr>
										<tr><td>Reminders Send Once</td>
											<td>$reminders_send_once</td></tr>
										<tr><td>Reminders Call Sign</td>
											<td>$reminders_call_sign</td></tr>
										<tr><td>Reminders Role</td>
											<td>$reminders_role</td></tr>
										<tr><td>Reminders Email Text</td>
											<td>$reminders_email_text</td></tr>
										<tr><td>Reminders Reminder Text</td>
											<td>$reminders_reminder_text</td></tr>
										<tr><td>Reminders Resolved</td>
											<td>$reminders_resolved</td></tr>
										<tr><td>Reminders Token</td>
											<td>$reminders_token</td></tr>
										<tr><td>Reminders Repeat Sent Date</td>
											<td>$reminders_repeat_sent_date</td></tr>
										<tr><td>Reminders Date Created</td>
											<td>$reminders_date_created</td></tr>
										<tr><td>Reminders Date Modified</td>
											<td>$reminders_date_modified</td></tr>
										<tr><td colspan='2'><input type='submit' class='formInputButton' name='submit' value='Update' /></td></tr>
										</table></form>";
					}
				} else {
					$content	.= "<p>No record found for id $record_id in $remindersTableName table</p>";
				}
			}
		}
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 3 with record_id of $record_id<br />";
		}
		
		$content		.= "<h3>$jobname</h3>";
		
		// get the record
		$sql		= "select * from $remindersTableName 
						where record_id = $record_id";
		$wpw1_cwa_reminders		= $wpdb->get_results($sql);
		if ($wpw1_cwa_reminders === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows 		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br /> and found $numRows rows in $remindersTableName table";
			}
			if ($numRows > 0) {
				foreach ($wpw1_cwa_reminders as $remindersRow) {
					$reminders_record_id 		= $remindersRow -> record_id;
					$reminders_effective_date	= $remindersRow -> effective_date;
					$reminders_close_date 		= $remindersRow -> close_date;
					$reminders_resolved_date 	= $remindersRow -> resolved_date;
					$reminders_send_reminder 	= $remindersRow -> send_reminder;
					$reminders_send_once 		= $remindersRow -> send_once;
					$reminders_call_sign 		= $remindersRow -> call_sign;
					$reminders_role 			= $remindersRow -> role;
					$reminders_email_text 		= stripslashes($remindersRow -> email_text);
					$reminders_reminder_text 	= stripslashes($remindersRow -> reminder_text);
					$reminders_resolved 		= $remindersRow -> resolved;
					$reminders_token 			= $remindersRow -> token;
					$reminders_repeat_sent_date = $remindersRow -> repeat_sent_date;
					$reminders_date_created 	= $remindersRow -> date_created;
					$reminders_date_modified 	= $remindersRow -> date_modified;
		
					$content	.= "<form method='post' action='$theURL' 
									name='deletion_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='5'>
									<input type='hidden' name='record_id' value='$reminders_record_id'>
									<input class='formInputButton' type='submit' name='submit' 
									onclick=\"return confirm('Are you sure?');\"  
									value='Delete This Record' />
									</form><br />
									<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='5'>
									<input type='hidden' name='record_id' value='$reminders_record_id'>
									<table style='width:1000px;'>
									<tr><td>reminders_record_id</td>
										<td>$reminders_record_id</td></tr>
									<tr><td>reminders_effective_date</td>
										<td><input type='text' class='formInputText' name='inp_reminders_effective_date' length='20' 
										maxlength='20' value='$reminders_effective_date'></td></tr>
									<tr><td>reminders_close_date</td>
										<td><input type='text' class='formInputText' name='inp_reminders_close_date' length='20' 
										maxlength='20' value='$reminders_close_date'></td></tr>
									<tr><td>reminders_resolved_date</td>
										<td><input type='text' class='formInputText' name='inp_reminders_resolved_date' length='20' 
										maxlength='20' value='$reminders_resolved_date'></td></tr>
									<tr><td>reminders_send_reminder</td>
										<td><input type='text' class='formInputText' name='inp_reminders_send_reminder' length='1' 
										maxlength='1' value='$reminders_send_reminder'></td></tr>
									<tr><td>reminders_send_once</td>
										<td><input type='text' class='formInputText' name='inp_reminders_send_once' length='1' 
										maxlength='1' value='$reminders_send_once'></td></tr>
									<tr><td>reminders_call_sign</td>
										<td><input type='text' class='formInputText' name='inp_reminders_call_sign' length='15' 
										maxlength='15' value='$reminders_call_sign'></td></tr>
									<tr><td>reminders_role</td>
										<td><input type='text' class='formInputText' name='inp_reminders_role' length='20' 
										maxlength='20' value='$reminders_role'></td></tr>
									<tr><td>reminders_email_text</td>
										<td><textarea class='formInputText' name='inp_reminders_email_text' rows='5' cols='50'>$reminders_email_text</textarea></td></tr>
									<tr><td>reminders_reminder_text</td>
										<td><textarea class='formInputText' name='inp_reminders_reminder_text' rows='5' cols='50'>$reminders_reminder_text</textarea></td></tr>
									<tr><td>reminders_resolved</td>
										<td><input type='text' class='formInputText' name='inp_reminders_resolved' length='1' 
										maxlength='1' value='$reminders_resolved'></td></tr>
									<tr><td>reminders_token</td>
										<td><input type='text' class='formInputText' name='inp_reminders_token' length='20' 
										maxlength='20' value='$reminders_token'></td></tr>
									<tr><td>reminders_repeat_sent_date</td>
										<td><input type='text' class='formInputText' name='inp_reminders_repeat_sent_date' length='20' 
										maxlength='20' value='$reminders_repeat_sent_date'></td></tr>
									<tr><td>reminders_date_created</td>
										<td>$reminders_date_created</td></tr>
									<tr><td>reminders_date_modified</td>
										<td><input type='text' class='formInputText' name='inp_reminders_date_modified' length='20' 
										maxlength='20' value='$reminders_date_modified'></td></tr>
									<tr><td colspan='2'><input class='formInputButton' type='submit' name='submit' value='Submit Updates' /></td></tr>
									</table></form>";
				}
			} else {
				$content		.= "<p>No record found to update for record_id of $record_id<br />";
			}
		}
									
	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 5 with submit: $submit; record_id: $record_id<br />";
		}
		
		$content		.= "<h3>$jobname</h3>";
		
		if ($submit == 'Delete This Record') {
			$deleteResult	= $wpdb->delete($remindersTableName,
											array('record_id'=>$record_id),
											array('%d'));
			if ($deleteResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$content		.= "<p>Deleting $record_id failed</p>";
			} else {
				$content		.= "<p>Record $record_id successfully deleted</p>";
			}
		
		} elseif ($submit == 'Submit Updates') {
			
			// get the record again
			$sql		= "select * from $remindersTableName 
							where record_id = $record_id";
			$wpw1_cwa_reminders		= $wpdb->get_results($sql);
			if ($wpw1_cwa_reminders === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numRows 		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br /> and found $numRows rows in $remindersTableName table";
				}
				if ($numRows > 0) {
					foreach ($wpw1_cwa_reminders as $remindersRow) {
						$reminders_record_id 		= $remindersRow -> record_id;
						$reminders_effective_date	= $remindersRow -> effective_date;
						$reminders_close_date 		= $remindersRow -> close_date;
						$reminders_resolved_date 	= $remindersRow -> resolved_date;
						$reminders_send_reminder 	= $remindersRow -> send_reminder;
						$reminders_send_once 		= $remindersRow -> send_once;
						$reminders_call_sign 		= $remindersRow -> call_sign;
						$reminders_role 			= $remindersRow -> role;
						$reminders_email_text 		= stripslashes($remindersRow -> email_text);
						$reminders_reminder_text 	= stripslashes($remindersRow -> reminder_text);
						$reminders_resolved 		= $remindersRow -> resolved;
						$reminders_token 			= $remindersRow -> token;
						$reminders_repeat_sent_date = $remindersRow -> repeat_sent_date;
						$reminders_date_created 	= $remindersRow -> date_created;
						$reminders_date_modified 	= $remindersRow -> date_modified;

						$content		.= "<p>The following updates were applied:</p>";
						$thisDate = date('Y-m-d H:i:s');
						$updateParams	= array();
						$updateFormat	= array();
						$updateLog	= " /$thisDate $userName performed the following updates:";
						if ($inp_reminders_effective_date != $reminders_effective_date) {
							$reminders_effective_date = $inp_reminders_effective_date;
							$updateParams['effective_date']	= $inp_reminders_effective_date;
							$updateFormat[]	= "%s";
							$content	.= "reminders_effective_date updated to $inp_reminders_effective_date<br />";
							$updateLog	.= " / reminders_effective_date updated to $inp_reminders_effective_date";
						}
						if ($inp_reminders_close_date != $reminders_close_date) {
							$reminders_close_date = $inp_reminders_close_date;
							$updateParams['close_date']	= $inp_reminders_close_date;
							$updateFormat[]	= "%s";
							$content	.= "reminders_close_date updated to $inp_reminders_close_date<br />";
							$updateLog	.= " / reminders_close_date updated to $inp_reminders_close_date";
						}
						if ($inp_reminders_resolved_date != $reminders_resolved_date) {
							$reminders_resolved_date = $inp_reminders_resolved_date;
							$updateParams['resolved_date']	= $inp_reminders_resolved_date;
							$updateFormat[]	= "%s";
							$content	.= "reminders_resolved_date updated to $inp_reminders_resolved_date<br />";
							$updateLog	.= " / reminders_resolved_date updated to $inp_reminders_resolved_date";
						}
						if ($inp_reminders_send_reminder != $reminders_send_reminder) {
							$reminders_send_reminder = $inp_reminders_send_reminder;
							$updateParams['send_reminder']	= $inp_reminders_send_reminder;
							$updateFormat[]	= "%s";
							$content	.= "reminders_send_reminder updated to $inp_reminders_send_reminder<br />";
							$updateLog	.= " / reminders_send_reminder updated to $inp_reminders_send_reminder";
						}
						if ($inp_reminders_send_once != $reminders_send_once) {
							$reminders_send_once = $inp_reminders_send_once;
							$updateParams['send_once']	= $inp_reminders_send_once;
							$updateFormat[]	= "%s";
							$content	.= "reminders_send_once updated to $inp_reminders_send_once<br />";
							$updateLog	.= " / reminders_send_once updated to $inp_reminders_send_once";
						}
						if ($inp_reminders_call_sign != $reminders_call_sign) {
							$reminders_call_sign = $inp_reminders_call_sign;
							$updateParams['call_sign']	= $inp_reminders_call_sign;
							$updateFormat[]	= "%s";
							$content	.= "reminders_call_sign updated to $inp_reminders_call_sign<br />";
							$updateLog	.= " / reminders_call_sign updated to $inp_reminders_call_sign";
						}
						if ($inp_reminders_role != $reminders_role) {
							$reminders_role = $inp_reminders_role;
							$updateParams['role']	= $inp_reminders_role;
							$updateFormat[]	= "%s";
							$content	.= "reminders_role updated to $inp_reminders_role<br />";
							$updateLog	.= " / reminders_role updated to $inp_reminders_role";
						}
						if ($inp_reminders_email_text != $reminders_email_text) {
							$reminders_email_text = $inp_reminders_email_text;
							$updateParams['email_text']	= $inp_reminders_email_text;
							$updateFormat[]	= "%s";
							$content	.= "reminders_email_text updated to $inp_reminders_email_text<br />";
							$updateLog	.= " / reminders_email_text updated to $inp_reminders_email_text";
						}
						if ($inp_reminders_reminder_text != $reminders_reminder_text) {
							if ($doDebug) {
								echo "inp_reminders_reminder_text: $inp_reminders_reminder_text<br />
								      reminders_reminder_text: $reminders_reminder_text<br />";
							}
							$reminders_reminder_text = $inp_reminders_reminder_text;
							$updateParams['reminder_text']	= $inp_reminders_reminder_text;
							$updateFormat[]	= "%s";
							$content	.= "reminders_reminder_text updated to $inp_reminders_reminder_text<br />";
							$updateLog	.= " / reminders_reminder_text updated to $inp_reminders_reminder_text";
						}
						if ($inp_reminders_resolved != $reminders_resolved) {
							$reminders_resolved = $inp_reminders_resolved;
							$updateParams['resolved']	= $inp_reminders_resolved;
							$updateFormat[]	= "%s";
							$content	.= "reminders_resolved updated to $inp_reminders_resolved<br />";
							$updateLog	.= " / reminders_resolved updated to $inp_reminders_resolved";
						}
						if ($inp_reminders_token != $reminders_token) {
							$reminders_token = $inp_reminders_token;
							$updateParams['token']	= $inp_reminders_token;
							$updateFormat[]	= "%s";
							$content	.= "reminders_token updated to $inp_reminders_token<br />";
							$updateLog	.= " / reminders_token updated to $inp_reminders_token";
						}
						if ($inp_reminders_repeat_sent_date != $reminders_repeat_sent_date) {
							$reminders_repeat_sent_date = $inp_reminders_repeat_sent_date;
							$updateParams['repeat_sent_date']	= $inp_reminders_repeat_sent_date;
							$updateFormat[]	= "%s";
							$content	.= "reminders_repeat_sent_date updated to $inp_reminders_repeat_sent_date<br />";
							$updateLog	.= " / reminders_repeat_sent_date updated to $inp_reminders_repeat_sent_date";
						}
						if ($inp_reminders_date_modified != $reminders_date_modified) {
							$reminders_date_modified = $inp_reminders_date_modified;
							$updateParams['date_modified']	= $inp_reminders_date_modified;
							$updateFormat[]	= "%s";
							$content	.= "reminders_date_modified updated to $inp_reminders_date_modified<br />";
							$updateLog	.= " / reminders_date_modified updated to $inp_reminders_date_modified";
						}		

						if (count($updateParams) > 0) {
							if ($doDebug) {
								echo "ready to update $record_id:<br /><pre>";
								print_r($updateParams);
								echo "</pre><br >";
							}
							$updateResult	= $wpdb->update($remindersTableName,
															$updateParams,
															array('record_id'=>$record_id),
															$updateFormat,
															array('%d'));
							if ($updateResult === FALSE) {
								handleWPDBError($jobname,$doDebug,'trying to put changes into the record');
								$content		.= "<p>Update failed</p>";
							} else {
								$content		.= "<p>Record $record_id updated</p>";

							}
						} else {
							$content		.= "<p>No updates requested</p>";
						}
					}
				} else {
					$content		.= "<p>No record found for record_id of $record_id</p>";
				}
			}
		
		} else {
			$content		.= "<p>No action requested nor taken</p>";
		}
	
	} elseif ("10" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 10<br />";
		}
		
		$content	.= "<h3>$jobname</h3>";
		if ($inp_reminders_token == '') {
			$inp_reminders_token	= mt_rand();
		}
		$updateParams	=	array('effective_date'=>$inp_reminders_effective_date,
								  'close_date'=>$inp_reminders_close_date,
								  'resolved_date'=>$inp_reminders_resolved_date,
								  'send_reminder'=>$inp_reminders_send_reminder,
								  'send_once'=>$inp_reminders_send_once,
								  'call_sign'=>$inp_reminders_call_sign,
								  'role'=>$inp_reminders_role,
								  'email_text'=>$inp_reminders_email_text,
								  'reminder_text'=>$inp_reminders_reminder_text,
								  'resolved'=>$inp_reminders_resolved,
								  'token'=>$inp_reminders_token,
								  'repeat_sent_date'=>$inp_reminders_repeat_sent_date);
		$updateFormat	= array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');
		$insertResult	= $wpdb->insert($remindersTableName,
										$updateParams,
										$updateFormat);
		if ($insertResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content	.= "<p>Inserting the record failed</p>";
		} else {
			$insert_id	= $wpdb->insert_id;
			$content	.= "<p>Record inserted. ID: $insert_id</p>";
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
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}
	return $content;
}
add_shortcode ('display_and_update_reminders', 'display_and_update_reminders_func');
