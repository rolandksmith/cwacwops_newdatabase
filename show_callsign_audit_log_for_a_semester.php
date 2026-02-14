function show_callsign_audit_log_for_a_semester_func() {

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
	if ($userName == '') {
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
	$theURL						= "$siteURL/cwa-show-callsign-audit-log-for-a-semester/";
	$inp_semester				= '';
	$inp_advisor				= '';
	$inp_student				= '';
	$jobname					= "Show Callsign Audit Log for a Semester V$versionNumber";
	$doAdvisor					= FALSE;
	$doStudent					= FALSE;

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
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
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
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$auditLogTableName			= "wpw1_cwa_audit_log2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$theMode					= "TESTMODE";
	} else {
		$extMode					= 'pd';
		$auditLogTableName			= "wpw1_cwa_audit_log";
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$theMode					= "PRODUCTION";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:200px;'>Call Sign</td>
								<td><input type='text' class='formInputText' name='inp_callsign' size='15' maxlength='15' autofocus required></td></tr>
							<tr><td>Semester</td>
								<td><input type='text' class='formInputText' name='inp_semester' size='25' maxlength='25'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 2 with advisor $inp_advisor and semester $inp_semester and student $inp_student<br />";
		}
		$doProceed			= TRUE;
		if ($inp_semester == '') {
			$content		.= "You must specify a semester";
			$doProceed		= FALSE;
		}
		if ($inp_callsign == '') {
			$content		.= "You must specify a callsign";
			$doProceed		= FALSE;
		}

		if ($doProceed) {
			$content			.= "<h3>$inp_callsign $jobname</h3>";
			// get and format the user_master data
			$sql			= "select * from $userMasterTableName 
								where user_call_sign = '$inp_callsign' ";
			$sqlResult		= $wpdb->get_results($sql);
			if ($sqlResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					foreach($sqlResult as $sqlRow) {
						$user_id				= $sqlRow->user_ID;
						$user_callsign			= $sqlRow->user_call_sign;
						$user_first_name		= $sqlRow->user_first_name;
						$user_last_name			= $sqlRow->user_last_name;
						$user_email				= $sqlRow->user_email;
						$user_phone				= $sqlRow->user_phone;
						$user_city				= $sqlRow->user_city;
						$user_state				= $sqlRow->user_state;
						$user_zip_code			= $sqlRow->user_zip_code;
						$user_country_code		= $sqlRow->user_country_code;
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
						$user_date_created		= $sqlRow->user_date_created;
						$user_date_updated		= $sqlRow->user_date_updated;
		
						$countrySQL				= "select * from wpw1_cwa_country_codes  
													where country_code = '$user_country_code'";
						$countrySQLResult		= $wpdb->get_results($countrySQL);
						if ($countrySQLResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
							$user_country		= "UNKNOWN";
							$user_ph_code		= "";
						} else {
							$numCRows		= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
							}
							if($numCRows > 0) {
								foreach($countrySQLResult as $countryRow) {
									$user_country		= $countryRow->country_name;
									$user_ph_code		= $countryRow->ph_code;
								}
							} else {
								$user_country			= "Unknown";
								$user_ph_code			= "";
							}
						}

						$myStr			= formatActionLog($user_action_log);
						$content		.= "<h4>User Master Data</h4>
										<table style='width:900px;'>
										<tr><td><b>Callsign<br />$user_callsign</b></td>
											<td><b>Name</b><br />$user_last_name, $user_first_name</td>
											<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
											<td><b>Email</b><br />$user_email</td></tr>
										<tr><td><b>City</b><br />$user_city</td>
											<td><b>State</b><br />$user_state</td>
											<td><b>Zip Code</b><br />$user_zip_code</td>
											<td><b>Country</b><br />$user_country</td></tr>
										<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
											<td><b>Telegram</b><br />$user_telegram</td>
											<td><b>Signal</b><br />$user_signal</td>
											<td><b>Messenger</b><br />$user_messenger</td></tr>
										<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
											<td><b>Date Created</b><br />user_$user_date_created</td>
											<td><b>Date Updated</b><br />user_$user_date_updated</td>
											<td></td></tr>
										<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>
										</table>
										<p>Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&inp_callsign=$inp_callsign' target='_blank'>HERE</a> to update the advisor Master Data</p>";
					}
				} else {
					if ($doDebug) {
						echo "no user_master record found for $inp_callsign<br />";
					}
					$doProceed	= FALSE;
				}
			}
			if ($doProceed) {
				$semester1			= str_replace("/","-",$inp_semester);
				$recordCount		= 0;
				$sql		= "SELECT * FROM $auditLogTableName  
								WHERE logmode = '$theMode' 
									and (logsemester = '$inp_semester'  
										or logsemester = '$semester1')
									and logcallsign like '%$inp_callsign%' 
									order by date_created";
				$audit1Result	= $wpdb->get_results($sql);
				if ($audit1Result === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$num1Rows	= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $num1Rows rows<br />";
					}
					if ($num1Rows > 0) {
						foreach($audit1Result as $audit1Row) {
							$record_id		= $audit1Row->record_id;
							$logtype		= $audit1Row->logtype;
							$logmode		= $audit1Row->logmode;
							$logdate		= $audit1Row->logdate;
							$logprogram		= $audit1Row->logprogram;
							$logwho			= $audit1Row->logwho;
							$logid			= $audit1Row->logid;
							$logsemester	= $audit1Row->logsemester;
							$logcallsign	= $audit1Row->logcallsign;
							$logsequence	= $audit1Row->logsequence;
							$logdata		= $audit1Row->logdata;
							$date_created	= $audit1Row->date_created;
							
							$recordCount++;
							$content		.= "<h4>$inp_callsign Record $recordCount for $inp_semester</h4>
												<table>
												<tr><td style='vertical-align:top;'><b>record_id</b><br />$record_id</td>
													<td style='vertical-align:top;'><b>logtype</b><br />$logtype</td>
													<td style='vertical-align:top;'><b>logmode</b><br />$logmode</td>
													<td style='vertical-align:top;'><b>logdate</b><br />$logdate</td></tr>
												<tr><td style='vertical-align:top;'><b>logwho</b><br />$logwho</td>
													<td style='vertical-align:top;'><b>logid</b><br />$logid</td>
													<td style='vertical-align:top;'><b>logprogram</b><br >$logprogram</td>
													<td style='vertical-align:top;'><b>logsemester</b><br />$logsemester</td></tr>
												<tr><td style='border-bottom:solid;vertical-align:top;'><b>logcallsign</b><br />$logcallsign</td>
													<td style='border-bottom:solid;vertical-align:top;'><b>date_created</b><br />$date_created</td>
													<td style='border-bottom:solid;vertical-align:top;'><b></b><br /></td>
													<td style='border-bottom:solid;vertical-align:top;'><b></b><br /></td></tr>";
							$myArray		= json_decode($logdata,TRUE);
							$myInt			= 0;
							foreach($myArray as $thisField=>$thisValue) {
								if (preg_match('/action_log/',$thisField)) {
									$thisField	= formatActionLog($thisField);
								}
								if ($myInt == 0) {
									$content	.= "<tr><td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td>";
									$myInt		= 1;
								} elseif ($myInt == 1) {
									$content	.= "<td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td>";
									$myInt		= 2;
								} elseif ($myInt == 2) {
									$content	.= "<td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td>";
									$myInt		= 3;
								} elseif ($myInt == 3) {
									$content	.= "<td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td></tr>";
									$myInt		= 0;
								}
							}
							if ($myInt == 0) {
								$content		.= "</table><br />";
							} elseif ($myInt == 1) {
								$content		.= "<td></td><td></td><td></td></table><br />";
							} elseif ($myInt == 2) {
								$content		.= "<td></td><td></td></table><br />";
							} elseif ($myInt == 3) {
								$content		.= "<td></td></table><br />";
							}
						}
					} else {
						$content				.= "<p>No records found running $sql<br />";
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
add_shortcode ('show_callsign_audit_log_for_a_semester', 'show_callsign_audit_log_for_a_semester_func');

