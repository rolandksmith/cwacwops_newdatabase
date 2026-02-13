function display_replacement_requests_func() {

//	Modified 19Oct24 by Roland for new database

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$versionNumber				 	= "1";
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
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	
//	CHECK THIS!								//////////////////////
	if ($userName = '') {
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
	$theURL						= "$siteURL/cwa-display-replacement-requests/";
	$jobname					= "Display Replacement Requests V$versionNumber";
	$inp_mode					= "Production";
	$inp_verbose				= "N";

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
			if ($str_key 		== "inp_id") {
				$inp_id	 = $str_value;
				$inp_id	 = filter_var($inp_id,FILTER_UNSAFE_RAW);
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
		$extMode						= 'tm';
		$replacementRequestsTableName	= "wpw1_cwa_replacement_requests2";
	} else {
		$extMode						= 'pd';
		$replacementRequestsTableName	= "wpw1_cwa_replacement_requests";
	}

	$theSemester						= $currentSemester;
	if ($currentSemester == 'Not in Session') {
		$theSemester					= $nextSemester;
	}
	

	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Displays the replacement requests for $theSemester semester</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 2<br />";
		}
		
		$content			.= "<h3>$jobname for $theSemester Semester</h3>
								<table>
								<tr><th>Advisor</th>
									<th>Class</th>
									<th>Level</th>
									<th>Replaced Student</th>
									<th>Date Requested</th>
									<th>Date Fulfilled</th>
									<th>Find Class</th>
									<th>Close</th></tr>";
									
		// get the replacement requests
		$sql		= "select * from $replacementRequestsTableName 
						where semester = '$theSemester' 
						order by call_sign";
 		$wpw1_cwa_replacement_requests		= $wpdb->get_results($sql);
		if ($wpw1_cwa_replacement_requests === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $replacementRequests table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname reading $replacementRequests failed.\nSQL: $myQuery\nError: $myError";
			sendErrorEmail($errorMsg);
			$content		.= "Unable to obtain content from $replacementRequests<br />";
		} else {
			$numBARows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numBARows rows<br />";
			}
			if ($numBARows > 0) {
				foreach ($wpw1_cwa_replacement_requests as $replacement_requestsRow) {
					$replacement_id				= $replacement_requestsRow->record_id;
					$replacement_call_sign		= $replacement_requestsRow->call_sign;
					$replacement_class			= $replacement_requestsRow->class;
					$replacement_level			= $replacement_requestsRow->level;
					$replacement_semester		= $replacement_requestsRow->semester;
					$replacement_student		= $replacement_requestsRow->student;
					$replacement_date_resolved	= $replacement_requestsRow->date_resolved;
					$replacement_date_created	= $replacement_requestsRow->date_created;
					$replacement_date_updated	= $replacement_requestsRow->date_updated;
					
					$dateCreated				= substr($replacement_date_created,0,10);
					$dateFulfilled				= substr($replacement_date_resolved,0,10);
					$advisorLink				= "<a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$replacement_call_sign&inp_depth=one&doDebug&testMode' target='_blank'>$replacement_call_sign</a>";
					$studentLink				= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$replacement_student&inp_depth=one&doDebug&testMode' target='_blank'>$replacement_student</a>";
					$findLink					= "<a href='$siteURL/cwa-student-management/?strpass=70&inp_student_callsign=$replacement_student&inp_mode=$inp_mode' target='_blank'>Find Class</a>";
					$closeLink					= "<a href='$theURL?strpass=4&inp_id=$replacement_id' target='_blank'>Close</a>";
					$classLink					= "<a href='$siteURL/cwa-student-management/?strpass=81&inp_advisor_callsign=$replacement_call_sign&inp_advisorClass=$replacement_class&inp_search=standard&inp_mode=$inp_mode' target='_blank'>(Find)</a>";
					if ($dateFulfilled == '') {
						$myStr					= $findLink;
						$myStr1					= $closeLink;
						$myStr3					= $classLink;
					} else {
						$myStr					= "";
						$myStr1					= "";
						$myStr3					= "";
					}
						
					
					$content 					.= "<tr><td>$advisorLink $myStr3</td>
														<td>$replacement_class</td>
														<td>$replacement_level</td>
														<td>$studentLink</td>
														<td>$dateCreated</td>
														<td>$dateFulfilled</td>
														<td>$myStr</td>
														<td>$myStr1</td></tr>";
				}
				$content						.= "</table>";
			} else {
				$content						.= "<tr><td colspan='6'>No requests found</td></tr></table>";
			}
		}
	
	
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass4 with inp_id or $inp_id<br />";
		}
		
		$content	.= "<h3>$jobname</h3>
						<h4>Closing $inp_id</h4>";
		$nowDate	= date('Y-m-d H:i:s');
		$updateResult	= $wpdb->update($replacementRequestsTableName,
										array('date_resolved'=>$nowDate), 
										array('record_id'=>$inp_id),
										array('%s'), 
										array('%d'));
		if ($updateResult === FALSE) {
			handleWPDBError($jobname,$doDebug,"pass 3 attempting to close $inp_id");
			$content	.= "<p>Closing $inp_id failed</p>";
		} else {
			if ($doDebug) {
				$lastQuery	= $wpdb->last_query;
				echo "successfully ran $lastQuery<br />resulting in $updateResult rows updated<br />";
			}
			$content	.= "<p>Replacement request successfully closed</p>";
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
add_shortcode ('display_replacement_requests', 'display_replacement_requests_func');

