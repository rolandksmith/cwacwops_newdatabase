function advisor_statistics_func() {

/* Snippet to display advisor statistics

	Modified 22Oct22 by Roland for the new timezone table formats
	Modified 15Apr23 by Roland to correct action log handling
	Modified 12Jul23 by Roland to use consolidated tables
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL   = $initializationArray['siteurl'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$countArray			= array();
	$thisTime 			= date('Y-m-d H:i:s',$currentTimestamp);
	$test1				= 0;
	$test2				= 0;
	$unconfirmedArray	= array();
	$nextSemester		= $initializationArray['nextSemester'];
	$myString			= '';
	$strPass			= '1';
	$theURL				= "$siteURL/cwa-advisor-statistics/";

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
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
		}
	}

/*	array setup
 *		[semester][total]value		total advisor records for the semester
 *		[semester][confirmed]value	read advisor records confirmed
 *		[semester][verify]value		registered within 45-day window
 *		[semester][date0]date		date verify email was sent
 *		[semester][verify0]value	number of advisors confirmed on date0
 *		[semester][date1]date		date 2nd verify email would be sent
 *		[semester][verify1]value	number of advisors confirmed on date1
 *		[semester][date2]date		date 3rd verify email would be sent
 *		[semester][verify2]value	number of advisors confirmed on date2
 *		[semester][date3]date		date drop email would be sent
 *		[semester][verify3]value	number of dropped advisors
 *		[semester][unconfirmed]value	number of unconfirmed advisor records
 *		[semester][removed]value	number of removed advisor records
*/		
	
	
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
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
		$past_advisorTableName	= "wpw1_cwa_consolidated_past_advisor2";
	} else {
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass";
		$past_advisorTableName	= "wpw1_cwa_consolidated_past_advisor";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Prepare and Display Advisor Statistics for $nextSemester</h3>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<input class='formInputButton' type='submit' value='Submit' />
							</form></p><br /><br /><br />";

///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {
// Read the advisor table
		if ($doDebug) {
			echo "Arrived at pass 2<br />";
		}

		$sql						= "select * from $advisorTableName 
									   where semester='$nextSemester' 
										order by call_sign";
		$wpw1_cwa_advisor		= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numARows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= stripslashes($advisorRow->action_log);
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

					if ($doDebug) {
						echo "<br />Processing $advisor_call_sign: $advisor_semester | email_date:$advisor_verify_email_date | verify_number:$advisor_verify_email_number | verify_response:$advisor_verify_response<br />";
					}
					if (!array_key_exists($advisor_semester,$countArray)) {
						$countArray[$advisor_semester]['total']			= 0; 	//	total advisor records for the semester
						$countArray[$advisor_semester]['confirmed']		= 0; 	//	read advisor records confirmed
						$countArray[$advisor_semester]['verify']		= 0;	//	Registered within 45-day window
						$countArray[$advisor_semester]['date0']			= ''; 	//	date verify email was sent
						$countArray[$advisor_semester]['verify0']		= 0; 	//	number of advisors confirmed on date0
						$countArray[$advisor_semester]['date1']			= ''; 	//	date 2nd verify email would be sent
						$countArray[$advisor_semester]['verify1']		= 0; 	//	number of advisors confirmed on date1
						$countArray[$advisor_semester]['date2']			= ''; 	//	date 3rd verify email would be sent
						$countArray[$advisor_semester]['verify2']		= 0; 	//	number of advisors confirmed on date2
						$countArray[$advisor_semester]['date3']			= ''; 	//	date drop email would be sent
						$countArray[$advisor_semester]['verify3']		= 0; 	//	number of dropped advisors
						$countArray[$advisor_semester]['unconfirmed']	= 0; 	//	number of unconfirmed advisor records
						$countArray[$advisor_semester]['removed']		= 0; 	//	number of removed advisor records
					}
					$countArray[$advisor_semester]['total']++;
					if ($doDebug) {
						echo "&nbsp;&nbsp;&nbsp;Counted $advisor_semester/total<br />";
					}
					if ($advisor_verify_response == 'Y') {
						$countArray[$advisor_semester]['confirmed']++;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;Counted $advisor_semester/confirmed<br />";
						}
					} elseif ($advisor_verify_response == 'R') {
						$countArray[$advisor_semester]['removed']++;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;Counted $advisor_semester/removed<br />";
						}
					} else {
						$countArray[$advisor_semester]['unconfirmed']++;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;Counted $advisor_semester/unconfirmed<br />";
						}
						if ($advisor_semester == $nextSemester) {
							$myString			= '';
							if ($advisor_verify_email_number == 1) {
								$myString		= "1st Verify Email Sent<br />";
							} elseif ($advisor_verify_email_number == 2) {
								$myString		= "2nd Verify Email Sent<br />";
							} elseif ($advisor_verify_email_number == 3) {
								$myString		= "3rd Verify Email Sent<br />";
							} elseif ($advisor_verify_email_number == 4) {
								$myString		= "DROP Email Sent<br />";
							}
							$thisData			= "$advisor_call_sign $advisor_last_name, $advisor_first_name";
							if ($myString != '') {
								$thisData		.= "<br />&nbsp;&nbsp;&nbsp;$myString<br />";
							} else {
								$thisData		.= "<br />";
							}
								
							$unconfirmedArray[]	= $thisData;
						}
					}
					if ($advisor_verify_email_date != '') {
						if ($countArray[$advisor_semester]['date0'] == '') {
							$countArray[$advisor_semester]['date0']	= $advisor_verify_email_date;
						}
						if ($advisor_verify_response == 'Y' && $advisor_verify_email_number == 1) {
							$countArray[$advisor_semester]['verify0']++;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Counted $advisor_semester/verify0<br />";
							}
						}
						if ($countArray[$advisor_semester]['date1'] == '') {
							$myInt			= strtotime("$advisor_verify_email_date + 5 days");
							$newDate		= date('Y/m/d',$myInt);
							$countArray[$advisor_semester]['date1']	= $newDate;
						}
						if ($advisor_verify_response == 'Y' && $advisor_verify_email_number == 2) {
								$countArray[$advisor_semester]['verify1']++;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Counted $advisor_semester/verify1<br />";
							}
						}
						if ($countArray[$advisor_semester]['date2'] == '') {
							$myInt			= strtotime("$advisor_verify_email_date + 10 days");
							$newDate		= date('Y/m/d',$myInt);
							$countArray[$advisor_semester]['date2']	= $newDate;
						}
						if ($advisor_verify_response == 'Y' && $advisor_verify_email_number == 3) {
							$countArray[$advisor_semester]['verify2']++;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Counted $advisor_semester/verify2<br />";
							}
						}
						if ($countArray[$advisor_semester]['date3'] == '') {
							$myInt			= strtotime("$advisor_verify_email_date + 15 days");
							$newDate		= date('Y/m/d',$myInt);
							$countArray[$advisor_semester]['date3']	= $newDate;
						}
						if ($advisor_verify_response == 'Y' && $advisor_verify_email_number == 4) {
							$countArray[$advisor_semester]['verify3']++;
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;Counted $advisor_semester/verify3<br />";
							}
						}
					}
					if ($advisor_verify_email_date == '' && $advisor_verify_response == 'Y') {
						$countArray[$advisor_semester]['verify']++;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;Counted $advisor_semester/verify<br />";
						}
					}
				
				} 	// end of the while loop
			}		// end of the numberRecords section
		}
		
		if ($doDebug) {
			echo "countArray:<br /><pre>";
			print_r($countArray);
			echo "</pre><br />";
		}
		$content	.= "<h3>Advisor Statistics as of $thisTime</h3>";
		foreach ($countArray as $key0=>$value0) {
			$content	.= "<h4>$key0 Semester</h4><table><tr><th>Item</th><th>Value</th></tr>";
			$theCount	= $value0['total'];
			$content	.= "<tr><td>Total advisors</td><td>$theCount</td></tr>";
			$theCount	= $value0['confirmed'];
			$content	.= "<tr><td>Confirmed Advisors</td><td>$theCount</td></tr>";
			$theCount	= $value0['verify'];
			$content	.= "<tr><td>Advisors who registered within the 45-day window</td><td>$theCount</td></tr>";
			$theDate	= $value0['date0'];
			$theCount	= $value0['verify0'];
			$content	.= "<tr><td>Advisors who verified as a result of the 1st email on $theDate</td><td>$theCount</td></tr>";
			$theDate	= $value0['date1'];
			$theCount	= $value0['verify1'];
			$content	.= "<tr><td>Advisors who verified as a result of the 2st email on $theDate</td><td>$theCount</td></tr>";
			$theDate	= $value0['date2'];
			$theCount	= $value0['verify2'];
			$content	.= "<tr><td>Advisors who verified as a result of the 3rd email on $theDate</td><td>$theCount</td></tr>";
			$theCount	= $value0['verify3'];
			$content	.= "<tr><td>Dropped Advisors</td><td>$theCount</td></tr>";
			$theCount	= $value0['unconfirmed'];
			$content	.= "<tr><td>Unconfirmed Advisors</td><td>$theCount</td></tr>";
			$theCount	= $value0['removed'];
			$content	.= "<tr><td>Removed Advisors</td><td>$theCount</td></tr></table>";
		}
		if ($doDebug) {
			echo "unconfirmedArray:<br /><pre>";
			print_r($unconfirmedArray);
			echo "</pre><br />";
		}
		if (count($unconfirmedArray) > 0) {
			$content	.= "<h4>Unconfirmed Advisor Registrations for $nextSemester</h4>";
			foreach($unconfirmedArray as $theValue) {
				$content	.= "$theValue";
			}
		}
	}
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d',$currentTimestamp);
	$nowTime		= date('H:i:s',$currentTimestamp);
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("Advisor Statistics|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	$content		.= "<p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('advisor_statistics', 'advisor_statistics_func');
