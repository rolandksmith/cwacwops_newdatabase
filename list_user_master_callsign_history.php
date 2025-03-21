function list_user_master_callsign_history_func() {

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
	$theURL						= "$siteURL/cwa-list-user-master-callsign-history/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "List User Master Callsign History V$versionNumber";

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
			if ($str_key 		== "inp_rsave") {
				$inp_rsave	 = strtoupper($str_value);
				$inp_rsave	 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
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
		$historyTableName			= "wpw1_cwa_user_master_history2";
		$mode						= "TESTMODE";
	} else {
		$extMode					= 'pd';
		$historyTableName			= "wpw1_cwa_user_master_history";
		$mode						= "PRODUCTION";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Which Callsign?</td>
								<td><input type='text' class='formInputText' size='20' maxlength='20' name='inp_callsign' autofocus required></td>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "<br />at pass 2 with inp_callsign $inp_callsign<br />";
		}
		$content		.= "<h3>$jobname for $inp_callsign</h3>
							<p><a href='$theURL'>List for a Different Callsign</a></p>";
		
		// get the data
		$sql			= "select * from $historyTableName 
							where historycallsign = '$inp_callsign' 
							and historymode = '$mode' 
							order by date_created";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content	.= "<p>Getting data from $historyTableName failed</p>";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows records<br />";
			}
			if ($numRows > 0) {
				$content				.= "<table width:1000px;'";
				foreach($sqlResult as $resultRow) {
					$record_id			= $resultRow->record_id;
					$historymode		= $resultRow->historymode;
					$historydate		= $resultRow->historydate;
					$historyprogram		= $resultRow->historyprogram;
					$historywho			= $resultRow->historywho;
					$historyid			= $resultRow->historyid;
					$historycallsign	= $resultRow->historycallsign;
					$historydata		= $resultRow->historydata;
					$date_created		= $resultRow->date_created;
					
					$content	.= "<tr><td style='vertical-align:top;width:250px;'><b>Record ID</b><br />$record_id</td>
										<td style='vertical-align:top;'width:250px;><b>Date Written</b><br />$historydate</td>
										<td style='vertical-align:top;'width:250px;><b>Program</b><br />$historyprogram</td>
										<td style='vertical-align:top;'width:250px;><b>Who</b><br />$historywho</td></tr>
									<tr><td style='vertical-align:top;'width:250px;><b>User Master ID</b><br />$historyid</td>
										<td style='vertical-align:top;'width:250px;><b>User Callsign</b><br />$historycallsign</td>
										<td></td>
										<td></td></tr>";
					$myArray	= json_decode($historydata,TRUE);
					$spot		= 1;
					foreach($myArray as $thisField=>$fieldValue) {
						switch ($spot) {
							case 1:
								$content	.= "<tr><td style='vertical-align:top;'><b>$thisField</b><br />$fieldValue</td>";
								$spot++;
								break;
							case 2:
								$content	.= "<td style='vertical-align:top;'><b>$thisField</b><br />$fieldValue</td>";
								$spot++;
								break;
							case 3:
								$content	.= "<td style='vertical-align:top;'><b>$thisField</b><br />$fieldValue</td>";
								$spot++;
								break;
							case 4:
								$content	.= "<td style='vertical-align:top;'><b>$thisField</b><br />$fieldValue</td></tr>";
								$spot		= 1;
								break;
						}
					}
					// finalize the table
					switch ($spot) {
						case 1:
							break;
						case 2:
							$content		.= "<td></td><td></td><td></td></tr>";
							break;
						case 3:
							$content		.= "<td></td><td></td></tr>";
							break;
						case 4:
							$content		.= "<td></td></tr>";
							break;
					}
					$content				.= "<tr><td colspan='4'><hr></td></tr>";
				}
				$content		.= "</table>
									<p><a href='$theURL'>List for a Different Callsign</a></p>";
			} else {
				$content		.= "<p>No data found for $inp_callsign</p>";
			}
		}
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";

	///// uncomment if the code to save a report is needed
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Current Student and Advisor Assignments<br />";
		}
		$thisDate		= date('dMy H:i');
		$storeResult	= storeReportData_v2("$jobname $inp_callsign $thisDate",$content,$testMode,$doDebug);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports pod as $storeResult[1]";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}

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
add_shortcode ('list_user_master_callsign_history', 'list_user_master_callsign_history_func');
