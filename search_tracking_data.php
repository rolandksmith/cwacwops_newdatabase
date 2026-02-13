function search_tracking_data_func() {

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
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
	$theURL						= "$siteURL/cwa-search-tracking-data/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Search Tracking Data";

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
			if ($str_key 		== "inp_date") {
				$inp_date	 = $str_value;
				$inp_date	 = filter_var($inp_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_ip") {
				$inp_ip	 = $str_value;
				$inp_ip	 = filter_var($inp_ip,FILTER_UNSAFE_RAW);
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
		$TableName					= "wpw1_cwa_";
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Search by call sign, date, or IP Address
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:200px;'>Call Sign</td>
								<td><input type='text' class='formInputText' name='inp_callsign' size='20' maxlength='20'></td></tr>
							<tr><td style='width:200px;'>Date</td>
								<td><input type='text' class='formInputText' name='inp_date' size='20' maxlength='20'></td></tr>
							<tr><td style='width:200px;'>IP Address</td>
								<td><input type='text' class='formInputText' name='inp_ip' size='20' maxlength='20'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($testMode) {
			$thisMode	= "TestMode";
		} else {
			$thisMode	= "Production";
		}
	
		if ($inp_callsign != '') {
			$sql		= "select * from wpw1_cwa_data_tracking 
							where tracking_callsign = '$inp_callsign' 
								and tracking_mode = '$thisMode' 
							order by tracking_timestamp";
			$heading	= "Call Sign: $inp_callsign";
		}
		if ($inp_date != '') {
			$sql		= "select * from wpw1_cwa_data_tracking 
							where tracking_timestamp like '$inp_date%' 
								and tracking_mode = '$thisMode' 
							order by tracking_timestamp";
			$heading	= "Date: $inp_date";
		}
		if ($inp_ip != '') {
			$sql		= "select * from wpw1_cwa_data_tracking 
							where tracking_ip = '$inp_ip' 
								and tracking_mode = '$thisMode' 
							order by tracking_timestamp";
			$heading	= "IP Address: $inp_ip";
		}
		$trackingResult	= $wpdb->get_results($sql);
		if ($trackingResult === FALSE) {
			$myQuery	= $wpdb->last_query;
			$myError	= $wpdb->last_error;
			if ($doDebug) {
				echo "getting record from wpw1_cwa_tracking failed<br />
						Last Query: $myQuery<br />
						Last Error: $myError<br />";
			}
			sendErrorEmail("$jobname pass 2 getting record from qpq1_cwa_tracking failed.<br />Query: $myQuery<br />Error: $myError");
		} else {
			$numDRows		= $wpdb->num_rows;
			if ($doDebug) {
				$myStr		= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numDRows rows<br />";
			}
			if ($numDRows > 0) {
				$content	.= "<h3>Tracking Data for $heading</h3>
								<table>
								<tr><th style='width:'150px;'>Program</th>
									<th stule='width:150px;'>Time Stamp</th>
									<th style='width: 100px;'>Call Sign</th>
									<th style='width:100px;'>IP Address</th>
									<th>Tracking Data</th></tr>";
				foreach($trackingResult as $trackingRow) {
					$tracking_ID		= $trackingRow->tracking_id;
					$tracking_program	= $trackingRow->tracking_program;
					$tracking_timestamp	= $trackingRow->tracking_timestamp;
					$tracking_callsign	= $trackingRow->tracking_callsign;
					$tracking_ip		= $trackingRow->tracking_ip;
					$tracking_data		= $trackingRow->tracking_data;
					
					$content			.= "<tr><td>$tracking_program</td>
												<td>$tracking_timestamp</td>
												<td>$tracking_callsign</td>
												<td>$tracking_ip</td>
												<td>$tracking_data</td></tr>";
				}
				$content				.= "</table>";
			} else {
				$content				.= "<p>No data for $heading found</p>";
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
add_shortcode ('search_tracking_data', 'search_tracking_data_func');

