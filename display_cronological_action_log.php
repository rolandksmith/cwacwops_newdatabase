function display_cronological_action_log_func() {

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
	$sql				= '';
	
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
	$theURL						= "$siteURL/cwa-display-chronological-action-log/";
	$jobname					= "Display Cronological Action Log V$versionNumber";
	$recordCount				= 0;
	$inp_run_type				= '';
	$inp_callsign				= '';

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
			if ($str_key 		== "inp_run_type") {
				$inp_run_type	 = $str_value;
				$inp_run_type	 = filter_var($inp_run_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
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
		$TableName					= "wpw1_cwa_cron_record";
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_cron_record";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<p>$jobname
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;width:auto;'>
<tr><td style='vertical-align:top;'>Option</td>
	<td><input type='radio' class='formInputButton' name='inp_run_type' value='cron' selected> Cronological Order, Newest to Oldest<br />
		<input type='radio' class='formInputButton' name='inp_run_type' value='callsign'> Specific callsign, Oldest to Newest. Callsign:<br />
		<input type='text' class='formInputText' name='inp_callsign' size='15' maxlength='50'></td></tr>
<tr><td colspan='2'>Click submit to show up to 100 records in reverse chronological order 
(newest to oldest)</td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2 with record count of $recordCount<br />";
		}
		$doProceed			= TRUE;
		if ($recordCount == 0) {
			if ($inp_run_type == 'cron') {
				$sql		= "select * from $TableName 
								order by date_created DESC 
								limit 100";
			} elseif ($inp_run_type == 'callsign') {
				$sql		= "select * from $TableName 
								where callsign='$inp_callsign' 
								order by date_created 
								limit 100";
			}
		} else {
			if ($inp_run_type == 'cron') {
				$sql		= "select * from $TableName 
								order by date_created DESC 
								limit $recordCount, 100";
			} elseif ($inp_run_type == 'callsign') {
				$sql		= "select * from $TableName 
								where callsign='$inp_callsign' 
								order by date_created  
								limit $recordCount, 100";
			}
		}
		if ($sql == '') {
			$content			.= "invalid data. run type: $inp_run_type callsign: $inp_callsign";
			$doProceed			= FALSE;
		}
		if ($doProceed) {
			$wpw1_cwa_cron_record	= $wpdb->get_results($sql);
			if ($wpw1_cwa_cron_record === FALSE) {
				$myError			= $wpdb->last_error;
				$myQuery			= $wpdb->last_query;
				if ($doDebug) {
					echo "Reading $tableName table failed<br />
						  wpdb->last_query: $myQuery<br />
						  wpdb->last_error: $myError<br />";
				}
				$errorMsg			= "$jobname reading $tableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
				sendErrorEmail($errorMsg);
				$content		.= "Unable to obtain content from $tableName<br />";
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					$content						.= "<h3>$jobname</h3>
														<table style='width:1000px;'>
														<tr><th>Date Created</th>
															<th>Table</th>
															<th>ID</th>
															<th>Method</th>
															<th>Callsign</th>
															<th>Semester</th>
															<th>Who</th>
															<th>Jobname</th></tr>";
					$ii								= 0;
					foreach ($wpw1_cwa_cron_record as $cronRow) {
						$record_ID					= $cronRow->record_id;
						$date_created				= $cronRow->date_created;
						$tablename					= $cronRow->tablename;
						$method						= $cronRow->method;
						$update_data				= $cronRow->update_data;
						$table_id					= $cronRow->table_id;
						$callsign					= $cronRow->callsign;
						$semester					= $cronRow->semester;
						$who						= $cronRow->who;
						$jobname					= $cronRow->jobname;
						$other_info					= $cronRow->other_info;
					
						$ii++;
						$myArray					= json_decode($update_data);
						$myStr						= "";
						foreach($myArray as $thisField=>$thisValue) {
							if ($thisField != 'action_log') {
								$myStr				.= "$thisField => $thisValue<br />";
							}
						}
						$content					.= "<tr><td style='vertical-align:top;''>$date_created</td>
															<td style='vertical-align:top;'>$tablename</td>
															<td style='vertical-align:top;'>$table_id</td>
															<td style='vertical-align:top;'>$method</td>
															<td style='vertical-align:top;'>$callsign</td>
															<td style='vertical-align:top;'>$semester</td>
															<td style='vertical-align:top;'>$who</td>
															<td style='vertical-align:top;'>$jobname<?td></tr>
														<tr><td style='border-bottom:1px solid;'></td>
															<td colspan='4' style='border-bottom:1px solid;'>$myStr</td>
															<td colspan='3' style='border-bottom:1px solid;'>$other_info</td></tr>";
					}
					$content						.= "</table>";
					if ($ii == 100) {
						$recordCount				= $recordCount + 100;
						$content					.= "<p><form method='post' action='$theURL' 
														name='selection_form' ENCTYPE='multipart/form-data'>
														<input type='hidden' name='strpass' value='2'>
														<input type='hidden' name='recordCount' value='$recordCount'>
														<table style='border-collapse:collapse;width:auto;'>
														<tr><td>Click submit to show the next up to 100 records in reverse chronological order 
														(newest to oldest)</td></tr>
														$testModeOption
														<tr><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
														</form></p>";
					} else {
						$content					.= "<p>End of Records</p>";
					}
				} else {
					$content						.= "<p>No records found</p>";
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
add_shortcode ('display_cronological_action_log', 'display_cronological_action_log_func');
