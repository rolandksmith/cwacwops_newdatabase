function run_saved_report_generator_func() {

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
	$theURL						= "$siteURL/cwa-run-saved-report-generator/";
	$jobname					= "Run Saved Report Generator V$versionNumber";

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
			if ($str_key 		== "inp_report") {
				$inp_report	 = $str_value;
				$inp_report	 = filter_var($inp_report,FILTER_UNSAFE_RAW);
			}
		}
	}
	
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
								<td colspan='2'><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
									<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td colspan='2'><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
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
		$TableName					= "wpw1_cwa_report_configurations2";
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_report_configurations";
	}



	if ("1" == $strPass) {
	
		if ($doDebug) {
			echo "<br />at pass1<br />";
		}
		$content	.= "<h3>$jobname</h3>";
		$sql		= "select * from $TableName 
						order by rg_report_name";
		$sqlResult	= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass 1 trying to read $TableName table");
			$content	.= "<p>Trying to read $TableName table failed</p>";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows records<br />";
			}
			if ($numRows > 0) {
				$content				.= "<h4>Available Report Configurations</h4>
											<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='2'>
											<table>
											<tr><th>Report Configuration</th>
												<th>Program</th>
												<th>Date Created</th></tr>";
				foreach($sqlResult as $resultRow) {
					$record_id			= $resultRow->record_id;
					$rg_report_name		= $resultRow->rg_report_name;
					$rg_table			= $resultRow->rg_table;
					$rg_config			= $resultRow->rg_config;
					$date_written		= $resultRow->date_written;

					$runProgram			= '';					
					if ($rg_table == 'student') {
						$runProgram		= 'Student Report Generator';
					} elseif ($rg_table == 'advisor') {
						$runProgram		= 'AdvisorClass Report Generator';
					} elseif ($rg_table == 'user') {
						$runProgram		= 'User Master Report Generator';
					}
					$content			.= "<tr><td style=vertical-align;top;'>
												<input type='radio' class='formInputButton' name='inp_report' value='$record_id' required>$rg_report_name</td>
												<td>$runProgram</td>
												<td>$date_written</td></tr>";
				}
				$content	.= "<tr><td colspan='3'><hr></td></tr>
								$testModeOption
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Select Report' /></td></tr></table>
								</form></p>";
			}
		}
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass2 with id $inp_report<br />";
		}
		
		$content			.= "<h3>$jobname</h3>";
		$sql		= "select * from $TableName 
						where record_id = $inp_report";
		$sqlResult	= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass 2 trying to get $inp_report from $TableName table");
			$content	.= "<p>Trying to read $TableName table failed</p>";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows records<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $resultRow) {
					$record_id			= $resultRow->record_id;
					$rg_report_name		= $resultRow->rg_report_name;
					$rg_table			= $resultRow->rg_table;
					$rg_config			= $resultRow->rg_config;
					$date_written		= $resultRow->date_written;

					$rg_config			= stripslashes($rg_config);
					$myArray			= json_decode($rg_config,TRUE);
					$where				= $myArray['where'];
					$configWhere		= base64_encode($where);
					$orderby			= $myArray['orderby'];
					$mode_type			= $myArray['mode_type'];
					$tabType			= $myArray['tabType'];
					$inp_debug			= $myArray['inp_debug'];
					$inp_config			= $myArray['inp_config'];
					$reportConfig		= $myArray['reportConfig'];
					$sequenceArray		= $myArray['sequenceArray'];

					$hiddenStr			= "";
					$hiddenStr			.= "<input type='hidden' name='configWhere' value='$configWhere'>";
					if ($doDebug) {
						echo "added configWhere: $configWhere to hiddenStr<br />";
					}
					$hiddenStr			.= "<input type='hidden' name='orderby' value='$orderby'>";
					if ($doDebug) {
						echo "added orderby: $orderby to hiddenStr<br />";
					}
					$hiddenStr			.= "<input type='hidden' name='mode_type' value='$mode_type'>";
					if ($doDebug) {
						echo "added mode_type: $mode_type to hiddenStr<br />";
					}
					$hiddenStr			.= "<input type='hidden' name='tabType' value='$tabType'>";
					if ($doDebug) {
						echo "added tabType: $tabType to hiddenStr<br />";
					}
					$hiddenStr			.= "<input type='hidden' name='inp_debug' value='$inp_debug'>";
					if ($doDebug) {
						echo "added inp_debug: $inp_debug to hiddenStr<br />";
					}
					$hiddenStr			.= "<input type='hidden' name='inp_config' value='$inp_config'>";
					if ($doDebug) {
						echo "added inp_config: $inp_config to hiddenStr<br />";
					}
					$configArray		= json_decode($reportConfig,TRUE);
					if ($doDebug) {
						echo "configArray:<br /><pre>";
						print_r($configArray);
						echo"</pre><br />";
					}
					foreach($configArray as $thisKey=>$thisValue) {
						$thisKey		= str_replace("_checked","",$thisKey);
						$hiddenStr		.= "<input type='hidden' name='$thisKey' value='$thisValue'>";
						if ($doDebug) {	
							echo "added input type='hidden' name='$thisKey' value='$thisValue'<br />";
						}
					}
					$seqArray			= json_decode($sequenceArray,TRUE);
					if ($doDebug) {
						echo "seqArray:<br /><pre>";
						print_r($seqArray);
						echo "</pre><br />";
					}
					foreach($seqArray as $thisKey => $thisValue) {
						$thisField		= $thisValue . "_sequence";
						$hiddenStr		.= "<input type='hidden' name='$thisField' value='$thisKey'>";
						if ($doDebug) {	
							echo "added input type='hidden' name='$thisField' value='$thisKey'<br />";
						}
					}
				
					$runURL		= '';
					if ($rg_table == 'student') {
						$runURL	= "$siteURL/cwa-new-student-report-generator/";
					} elseif ($rg_table == 'advisor') {
						$runURL	= "$siteURL/cwa-advisorclass-report-generator/";
					} elseif ($rg_table == 'user') {
						$runURL	= "$siteURL/cwa-user-master-report-generator/";
					}
					$content 	= "<h4>Generate $rg_report_name</h4>
									<form method='post' action=$runURL target='_blank' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='2'>
									<input type='hidden' name='inp_report_name' value='$rg_report_name'>
									$hiddenStr
									<input type='submit' name='submit' value='Generate Report' />";
	
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
add_shortcode ('run_saved_report_generator', 'run_saved_report_generator_func');
