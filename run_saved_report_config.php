function run_saved_report_config_func() {

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 						= $context->validUser;
/*
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
*/
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
	$theURL						= "$siteURL/cwa-run-saved-report-configuration/";
	$jobname					= "Run Saved Report Config V$versionNumber";
	$inp_record_id				= '';

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
			if ($str_key 		== "inp_record_id") {
				$inp_record_id	 = $str_value;
				$inp_record_id	 = filter_var($inp_record_id,FILTER_UNSAFE_RAW);
			}
		}
	}
	
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td style='vertical-align:top;'>Operation Mode</td>
							<td colspan='2'><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
								<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td style='vertical-align:top;'>Verbose Debugging?</td>
								<td colspan='2'><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
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
		$reportConfigTableName		= "wpw1_cwa_report_configurations";
	} else {
		$extMode					= 'pd';
		$reportConfigTableName		= "wpw1_cwa_report_configurations";
	}



	if ("1" == $strPass) {
	
		$sql			= "select record_id, rg_report_name, rg_table from $reportConfigTableName 
							order by rg_table, date_written";
		$configs		= $wpdb->get_results($sql);
		if ($configs === FALSE)  {
		
		} else {
			$numRows 	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				$reportList	= "<tr><th>Select</th>
									<th>Report Table</th>
									<th>Report Name</th></tr>";
				foreach($configs as $configsRow) {
					$record_id		= $configsRow->record_id;
					$rg_table		= $configsRow->rg_table;
					$report_name 	= $configsRow->rg_report_name;
					
					$reportList		.= "<tr><td><input type='radio' class='formInputButton' name='inp_record_id' value='$record_id'></td>
											<td>$rg_table</td>
											<td>$report_name</td></tr>";
				}
			}
		}
	
		$content 		.= "<h3>$jobname</h3>
<p>Select the desired report from the following list of Saved Report Configurations:<br />
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;width:auto;'>
$reportList
$testModeOption
<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 2 with record_id of $inp_record_id<br />";
		}
		$content		.= "<h3>$jobname</h3>";
		if ($inp_record_id == '') {
			$content	.= "No report selected";
		} else {
			// get the report config
			$sql			= "select * from $reportConfigTableName 
								where record_id = $inp_record_id";
			$configs		= $wpdb->get_results($sql);
			if ($configs === FALSE)  {
				$lastError	= $wpdb->last_error;
				$lastSQL	= $wpdb->last_query;
				if ($doDebug) {
					echo "reading $reportConfigTableName failed. Error: $lastError<br />Query: $lastQuery<br />";
				}
				$content	.= "Could not get data from $reportConfigTableName<br />";
			} else {
				$numRows 	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					foreach($configs as $configsRow) {
						$record_id		= $configsRow->record_id;
						$rg_table		= $configsRow->rg_table;
						$report_name 	= $configsRow->rg_report_name;
						$rg_config		= $configsRow->rg_config;
					
						$jsonVar		= html_entity_decode($rg_config);
						$enstr			= base64_encode($jsonVar);
						
						$doProceed = TRUE;
						if ($rg_table == 'wpw1_cwa_consolidated_student') {
							$newURL		= "$siteURL/cwa-student-report-generator-v2/";
							$doProceed	= TRUE;
						} else {
							$content	.= "not available yet";
							$doProceed	= FALSE;
						}
						if ($doProceed) {
							$content	.= "<h3>$jobname</h3>
											<p>Click on 'Submit' to run the report:
											<form method='post' action='$newURL' 
											name='selection_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='2'>
											<input type='hidden' name='enstr' value='$enstr'>
											<input class='formInputButton' type='submit' value='Submit' />\n
												</form>";
						}
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
add_shortcode ('run_saved_report_config', 'run_saved_report_config_func');

