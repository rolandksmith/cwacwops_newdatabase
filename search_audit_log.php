function search_audit_log_func() {
	
/*	Search and Display Audit Log Data

*/

	global $doDebug, $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$validUser = $context->validUser;
	$userName			= $context->userName;
	$validTestmode		= $context->validTestmode;
	$siteURL			= $context->siteurl;

	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		ini_set('memory_limit','256M');

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$inp_log					= '';
	$inp_callsign				= '';
	$theURL						= "$siteURL/cwa-search-audit-log/";
	$filename					= '';
	$displayCount				= 0;
	$inp_verbose				= '';
	$jobname					= "Search Audit Log";
	$fieldArray					= array();


	
	if ($testMode) {
		$auditLogTableName		= 'wpw1_cwa_audit_log2';
	} else {
		$auditLogTableName		= 'wpw1_cwa_audit_log';
	}



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
			if ($str_key 		== "inp_logtype") {
				$inp_logtype	 	= $str_value;
				$inp_logtype	 	= filter_var($inp_logtype,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	= $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'yes') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_testmode") {
				$inp_testmode	= $str_value;
				$inp_testmode	 = filter_var($inp_testmode,FILTER_UNSAFE_RAW);
				if ($inp_testmode == 'yes') {
					$testMode	= TRUE;
				}
			}
		}
	}
	
	
	$content = "";	





	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>$jobname</h3>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td style='vertical-align:top;'>Call Sign of Interest</td>
								<td style='vertical-align:top;'><input type='text' class='formInputText' name='inp_callsign' size='20' maxlength='20' autofocus required></td></tr>
							<tr><td style='vertical-align:top;'>Verbosity</td>
								<td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_verbose' value='no' checked='checked'> Normal Output<br />
																<input type='radio' class='formInputButton' name='inp_verbose' value='yes'> Highly Verbose</td></tr>
							<tr><td style='vertical-align:top;'>Test Mode</td>
								<td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_testmode' value='no' checked='checked'> Production<br />
																<input type='radio' class='formInputButton' name='inp_testmode' value='yes'> Test Mode</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
							</table></form>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}
		$thisMode		= "Production";
		if ($testMode) {
			$thisMode	= "testMode";
		}
		$sql			= "select * from $auditLogTableName 
						   where logcallsign='$inp_callsign' 
						   and logmode = '$thisMode' 
						   order by logdate";
		$wpw1_cwa_auditlog	= $wpdb->get_results($sql);
		if ($wpw1_cwa_auditlog === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass2 initial read of $auditLogTableName for $inp_callsign");
			$content	.= "<p>Invalid data obtained from $auditLogTableName</p>";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows records<br />";
			}
			if ($numRows > 0) {
				$content		.= "<h3>$jobname for $inp_callsign</h3><p>$numRows records to display</p><table style='width:1000px;'>";
				$rowCount		= 0;
				foreach($wpw1_cwa_auditlog as $auditlogRow) {
					$rowCount++;
					$record_id		= $auditlogRow->record_id;
					$logtype		= $auditlogRow->logtype;
					$logmode		= $auditlogRow->logmode;
					$logdate		= $auditlogRow->logdate;
					$logprogram		= $auditlogRow->logprogram;
					$logwho			= $auditlogRow->logwho;
					$logid			= $auditlogRow->logid;
					$logsemester	= $auditlogRow->logsemester;
					$logsequence	= $auditlogRow->logsequence;
					$logcallsign	= $auditlogRow->logcallsign;
					$date_created	= $auditlogRow->date_created;
					$logdata		= $auditlogRow->logdata;

					$myArray		= json_decode($logdata,TRUE);
					$myStr	 		= json_last_error();
//					if ($myStr != '') {
//						$content	.= "Row: $rowCount json_last_error: $myStr<br />";
//					}
					if ($doDebug) {
						echo "Decoded line record $rowCount:<br /><pre>";
						print_r($myArray);
						echo "</pre><br />";
					}
					
					$content		.= "<tr><td colspan='4'><b>Record $rowCount</b></td></tr>
										<tr><td style='width:250px;vertical-align:top;'><b>Audit Record ID</b><br />$record_id</td>
											<td style='width:250px;vertical-align:top;'><b>Date</b><br />$logdate</td>
											<td style='width:250px;vertical-align:top;'><b>Program</b><br />$logprogram</td>
											<td style='width:250px;vertical-align:top;'><b>Type</b><br />$logtype</td></tr>
										<tr><td style='vertical-align:top;'><b>who</b><br />$logwho</td>
											<td style='vertical-align:top;'><b>ID</b><br />$logid</td>
											<td style='vertical-align:top;'><b>Semester</b><br />$logsemester</td>
											<td style='vertical-align:top;'><b>Sequence</b><br />$logsequence</td></tr>
										<tr><td style='vertical-align:top;'><b>Callsign</b><br />$logcallsign</td>
											<td style='vertical-align:top;'><b>Date Created</b><br />$date_created</td>
											<td style='vertical-align:top;'></td>
											<td style='vertical-align:top;'></td></tr>";
					$spot			= 1;
					foreach($myArray as $thisField => $thisValue) {
						switch ($spot) {
							case 1:
								$content	.= "<tr><td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td>";
								$spot		= 2;
								break;
							case 2:
								$content	.= "\t<td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td>";
								$spot		= 3;
								break;
							case 3:
								$content	.= "\t<td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td>";
								$spot		= 4;
								break;
							Case 4:
								$content	.= "\t<td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td></tr>";
								$spot		= 1;
								break;
						}					
					}
					// finish off this row
					switch ($spot) {
						case 1:
							break;
						case 2:
							$content		.= "\t<td></td><td></td><td></td></tr>";
							break;
						case 3:
							$content		.= "\t<td></td><td></td></tr>";
							break;
						case 4:
							$content		.= "\t<td></td></tr>";
							break;
					}
					$content				.= "<tr><td colspan='4'><hr></td></tr>";
				}
				$content		.= "</table>";
			} else {
				$content		.= "<h3>$jobname</h3>
									<p>No audit log records found for $inp_callsign</p>";
			}
		}
					
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('search_audit_log','search_audit_log_func');

