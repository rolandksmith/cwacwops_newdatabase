function verify_temp_data_func() {

/*	Checks temp_data records against username records

	Reads temp_data 
	
	Check to see if there is a user_login for that callsign
	
	If no user_login record
		and token is 'register', 'ignore', or 'tracking'
			delete the temp_data record
		otherwise, if date_written more than 90 days old
			delete the temp_data record
	
	Program is expected to run as a cron job

	
*/

	global $wpdb, $tempTableName, $doDebug, $jobname;

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
	$userName			= $initializationArray['userName'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	$tokenList			= array('register','ignore','tracking');
	$deleteList			= array();
	$deleteCount		= 0;
	$currentDate		= date('Y-m-d H:i:s');
	

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
	$theURL						= "$siteURL/cwa-verify-temp-data/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Verify Temp Data V$versionNumber";

	
	
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
		$tempTableName				= "wpw1_cwa_temp_data2";
		$userTableName				= "wpw1_users";
	} else {
		$extMode					= 'pd';
		$tempTableName				= "wpw1_cwa_temp_data";
		$userTableName				= "wpw1_users";
	}



	$runTheJob				= TRUE;
////// see if this is the time to actually run
	if ($doDebug) {
		echo "<br />starting<br />";
	}
// $validReplacementPeriod = 'Y';
		
	if ($userName != '') {
		$content 			.= "<h3>$jobname Process Executed by $userName</h3>";
	} else {
		$content			.= "<h3>$jobname Process Automatically Executed</h3>";
		$userName			= "CRON";

		$dst				= date('I');
		if ($dst == 1) {
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
			if ($doDebugLog) {
				$debugLog .= "runTheJob is FALSE<br />";
			}
			$theRecipient	= 'rolandksmith@gmail.com';
			$theSubject		= 'CW Academy - Cron Triggered';
			$theContent		= "$jobname was triggered at $thisTime. It did not run. 
checkBegin: $checkBegin. checkEnd: $checkEnd. nowTime: $nowTime";
			$mailCode		= 16;
			$result			= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
													'theSubject'=>$theSubject,
													'jobname'=>$jobname,
													'theContent'=>$theContent,
													'mailCode'=>$mailCode,
													'testMode'=>$testMode,
													'doDebug'=>FALSE));
		}
	}
	if ($runTheJob) {

		function deleteTempRecord($record_id) {
		
			global $wpdb, $tempTableName, $doDebug, $jobname;
			
			$deleteResult	= $wpdb->delete($tempTableName, 
											array('record_id'=>$record_id), 
											array('%d'));
			if ($deleteResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
				return FALSE;
			} else {
				return $deleteResult;
			}
		}

		$content		.= "<h3>$jobname</h3>";
		$sql 			= "select * from $tempTableName 
							order by date_written";
		$tempResult		= $wpdb->get_results($sql);
		if ($tempResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($tempResult as $tempResultRow) {
					$record_id			= $tempResultRow->record_id;
					$callsign			= strtolower($tempResultRow->callsign);
					$token				= $tempResultRow->token;
					$temp_data			= $tempResultRow->temp_data;
					$date_written		= $tempResultRow->date_written;

					// see if there is a user_login record
					
					$userSQL			= "select user_login 
										   from $userTableName 
										   where lower(user_login) = '$callsign'";
					$user_login			= $wpdb->get_var($userSQL);
					if ($user_login === NULL) {
//						$content		.= "Getting user_login returned NULL<br />";
						if (in_array($token,$tokenList)) {		/// delete the record
							$content	.= "<br />Processing $callsign Token of $token and no user_login. Deleting<br />";
							$deleteResult 	= deleteTempRecord($record_id);
							if ($deleteResult === FALSE) {
								$content	.= "Deletion failed<br />";
							} elseif ($deleteResult == 1) {
								$content	.= "Record successfully deleted<br />";
								$deleteCount++;
							} elseif ($deleteResult == 0) {
								$content	.= "No record found by id $record_id Nothing deleted<br />";
							}							
							
						} else {
							$pastDate 		= date('Y-m-d H:i:s', strtotime($currentDate . ' -90 days'));						
							if ($date_written < $pastDate) {			// older than 90 days
								$content	.= "<br />Processing $callsign Record older than 90 days and token is not in the list. Deleting<br />";
								$deleteResult 	= deleteTempRecord($record_id);
								if ($deleteResult === FALSE) {
									$content	.= "Deletion failed<br />";
								} elseif ($deleteResult == 1) {
									$content	.= "Record successfully deleted<br />";
									$deleteCount++;
								} elseif ($deleteResult == 0) {
									$content	.= "No record found by id $record_id Nothing deleted<br />";
								}							
							} else {
//								$content		.= "Record less than 90 days old. No deletion<br />";
							}
						}
					} else {
//						$content	.= "has user_login of  $user_login<br />";
						// if the token is not in tokenList and record older than 90 days, delete
						if (!in_array($token,$tokenList)) {
//							$content		.= "Token not in token list, eligible to be deleted<br />";
							$pastDate 		= date('Y-m-d H:i:s', strtotime($currentDate . ' -90 days'));						
							if ($date_written < $pastDate) {			// older than 90 days
								$content	.= "<br />Processing $callsign Record older than 90 days. Deleting<br />";
								$deleteResult 	= deleteTempRecord($record_id);
								if ($deleteResult === FALSE) {
									$content	.= "Deletion failed<br />";
								} elseif ($deleteResult == 1) {
									$content	.= "Record successfully deleted<br />";
									$deleteCount++;
								} elseif ($deleteResult == 0) {
									$content	.= "No record found by id $record_id Nothing deleted<br />";
								}
							} else {
//								$content		.= "Record less than 90 days old. No deletion<br />";
							}
						} else {
//							$content			.= "Token of $token. Will not be deleted<br />";
						}
					}
				}
			}
		}
		$content		.= "<br />$deleteCount temp_data records deleted<br />";
	
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
		// store the report in the reports table
		$storeResult	= storeReportData_v2($jobname,$content,$testMode,$doDebug);
		if ($storeResult[0] === FALSE) {
			if ($doDebug) {
				echo "storing report failed. $storeResult[1]<br />";
			}
			$content	.= "Storing report failed. $storeResult[1]<br />";
		} else {
			$reportid	= $storeResult[2];
		}
		
		// store the reminder
		$closeStr		= strtotime("+2 days");
		$close_date		= date('Y-m-d H:i:s', $closeStr);
		$token			= mt_rand();
		$reminder_text	= "<b>Verify Temp Data</b> To view the $jobname report for $nowDate $nowTime, click <a href='cwa-display-saved-report/?strpass=3&inp_callsign=K7OJL&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
		$inputParams		= array("effective_date|$nowDate $nowTime|s",
									"close_date|$close_date|s",
									"resolved_date||s",
									"send_reminder|N|s",
									"send_once|N|s",
									"call_sign|K7OJL|s",
									"role||s",
									"email_text||s",
									"reminder_text|$reminder_text|s",
									"resolved|N|s",
									"token|$token|s");
		$reminderResult	= add_reminder($inputParams,$testMode,$doDebug);
		if ($reminderResult[0] === FALSE) {
			if ($doDebug) {
				echo "adding reminder failed. $reminderResult[1]<br />";
			}
		}

		$theSubject	= "$jobname Process";
		$theContent	= "The $jobname process was run at $nowDate $nowTime, Login to <a href='$siteURL/program-list'>CW Academy</a> to see the 
						report.";
		if ($testMode) {		
			$theRecipient	= '';
			$mailCode	= 2;
			$theSubject = "TESTMODE $theSubject";
		} else {
			$theRecipient	= '';
			$mailCode		= 16;
		}
		$result		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
												  'theSubject'=>$theSubject,
												  'jobname'=>$jobname,
												  'theContent'=>$theContent,
												  'mailCode'=>$mailCode,
												  'testMode'=>$testMode,
												  'doDebug'=>$doDebug));
		if ($result === TRUE) {
			$myStr		= "Process completed";
			return $myStr;
		} else {
			$myStr .= "<br />The final mail send function to $myTo failed.<br /><br />";
			return $myStr;
		}

	}
}
add_shortcode ('verify_temp_data', 'verify_temp_data_func');
