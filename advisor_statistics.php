function advisor_statistics_func() {

/* Snippet to display advisor statistics

	Modified 22Oct22 by Roland for the new timezone table formats
	Modified 15Apr23 by Roland to correct action log handling
	Modified 12Jul23 by Roland to use consolidated tables
	Modified 11Oct24 by Roland for new database
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$validUser = $context->validUser;
	$userName  = $context->userName;
	$siteURL   = $context->siteurl;
	$currentTimestamp	= $context->currentTimestamp;

	if ($userName == '') {
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
	$nextSemester		= $context->nextSemester;
	$proximateSemester	= $context->proximateSemester;
	$myString			= '';
	$strPass			= '1';
	$theURL				= "$siteURL/cwa-advisor-statistics/";
	$jobname			= "Prepare and Display Advisor Statistics";

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
	
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$advisorTableName			= "wpw1_cwa_advisor2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$userMasterTableName		= "wpw1_cwa_user_master2";
	} else {
		$advisorTableName			= "wpw1_cwa_advisor";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$userMasterTableName		= "wpw1_cwa_user_master";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>$jobname for $proximateSemester</h3>
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
										left join $userMasterTableName on user_call_sign = advisor_call_sign 
									   where advisor_semester='$proximateSemester' 
										order by advisor_call_sign";

		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numARows			= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numARows rows in $advisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_master_ID 					= $advisorRow->user_ID;
					$advisor_master_call_sign			= $advisorRow->user_call_sign;
					$advisor_first_name 				= $advisorRow->user_first_name;
					$advisor_last_name 					= $advisorRow->user_last_name;
					$advisor_email 						= $advisorRow->user_email;
					$advisor_phone 						= $advisorRow->user_phone;
					$advisor_city 						= $advisorRow->user_city;
					$advisor_state 						= $advisorRow->user_state;
					$advisor_zip_code 					= $advisorRow->user_zip_code;
					$advisor_country_code 				= $advisorRow->user_country_code;
					$advisor_whatsapp 					= $advisorRow->user_whatsapp;
					$advisor_telegram 					= $advisorRow->user_telegram;
					$advisor_signal 					= $advisorRow->user_signal;
					$advisor_messenger 					= $advisorRow->user_messenger;
					$advisor_master_action_log 			= $advisorRow->user_action_log;
					$advisor_timezone_id 				= $advisorRow->user_timezone_id;
					$advisor_languages 					= $advisorRow->user_languages;
					$advisor_survey_score 				= $advisorRow->user_survey_score;
					$advisor_is_admin					= $advisorRow->user_is_admin;
					$advisor_role 						= $advisorRow->user_role;
					$advisor_master_date_created 		= $advisorRow->user_date_created;
					$advisor_master_date_updated 		= $advisorRow->user_date_updated;

					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_call_sign 					= strtoupper($advisorRow->advisor_call_sign);
					$advisor_semester 					= $advisorRow->advisor_semester;
					$advisor_welcome_email_date 		= $advisorRow->advisor_welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->advisor_verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->advisor_verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
					$advisor_action_log 				= $advisorRow->advisor_action_log;
					$advisor_class_verified 			= $advisorRow->advisor_class_verified;
					$advisor_control_code 				= $advisorRow->advisor_control_code;
					$advisor_date_created 				= $advisorRow->advisor_date_created;
					$advisor_date_updated 				= $advisorRow->advisor_date_updated;
					$advisor_replacement_status 		= $advisorRow->advisor_replacement_status;


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
		$content	.= "<h3>$jobname as of $thisTime</h3>";
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
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}
	$content		.= "<p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('advisor_statistics', 'advisor_statistics_func');

