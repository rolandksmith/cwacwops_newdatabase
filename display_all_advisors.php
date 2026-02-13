function display_all_advisors_func() {

// modified 17feb2020 by Bob C to select all advisors
// Added display wants or want to be an Associate - preferred - order by "wants to be" - bc 29feb20
// modified 30Jun2020 to add advisor class days matrix at the end of the report
// Added display wants or want to be an Associate - preferred - order by "wants to be" - bc 04jul20
// Added unique advisor count - Roland 15Aug2020
// Removed Associates and added Class Verify - bc 16nov20  
// Added class start times /  bc 10Feb21 7:35 
// Modified 1Jun21 by Roland for the new advisor and class pods 
// Modified 14Jan2022 by Roland to use the new tables rather than pods	
// Modified 24Oct22 by Roland for the new Timezone table format
// Modified 15Apr23 by Roland to fix action_log
// modified 13Jul23 by Roland to use consolidated tables
// modified 13Oct24 by Roland for new database
	
	global $wpdb;
	
	$doDebug			= FALSE;
	$testMode			= FALSE;
	$initializationArray = data_initialization_func();
	$validUser			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester	    = $initializationArray['nextSemester'];
	$semesterTwo	    = $initializationArray['semesterTwo'];
	$semesterThree  	= $initializationArray['semesterThree'];
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	
	/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$inp_semester		= '';
//	$inp_semester		= $initializationArray['nextSemester'];
	$strPass			= "1";
	$theURL				= "$siteURL/cwa-display-all-advisors/";
	$jobname			= "Display all advisors";
	
	ini_set('display_errors','1');
	error_reporting(E_ALL);	

	// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
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

	$content = "";	
	
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$optionList		= "";
		if ($currentSemester != "Not in Session") {
			$optionList	.= "<option value='$currentSemester'>$currentSemester</option>";
		}
		$optionList		.= "<option value='$nextSemester'>$nextSemester</option>";
		$optionList		.= "<option value='$semesterTwo'>$semesterTwo</option>";
		$optionList		.= "<option value='$semesterThree'>$semesterThree</option>";
	
		$content 		.= "<h3>$jobname</h3>
							<p>Select the semester of interest from the list below:</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data''>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Semester</td><td>
							<select name='inp_semester' required='required' size='5' autofocus class='formSelect'>
							$optionList
							</select></td></tr>
							<tr><td>&nbsp</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form>";

//////////////

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}
		if ($testMode) {
			$advisorTableName			= 'wpw1_cwa_advisor2';
			$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
			$userMasterTableName		= 'wpw1_cwa_user_master2';
			if ($doDebug) {
				echo "Operating in testMode<br />";
			}
			$content					.= "<p><b>Operating in Test Mode</b></p>";
		} else {
			$advisorTableName			= 'wpw1_cwa_advisor';
			$advisorClassTableName		= 'wpw1_cwa_advisorclass';
			$userMasterTableName		= 'wpw1_cwa_user_master';
		}

		$advisorCount					= 0;
		$classesCount					= 0;
		$content 						.= "<h3>$jobname for $inp_semester Semester</h3><table>";
	
		$sql					= "select * from $advisorTableName 
									left join $userMasterTableName on user_call_sign = advisor_call_sign 
									where  advisor_semester='$inp_semester' 
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

					// if you need the country name and phone code, include the following
					$countrySQL		= "select * from wpw1_cwa_country_codes  
										where country_code = '$advisor_country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
						$advisor_country		= "UNKNOWN";
						$advisor_ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numCRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$advisor_country		= $countryRow->country_name;
								$advisor_ph_code		= $countryRow->ph_code;
							}
						} else {
							$advisor_country			= "Unknown";
							$advisor_ph_code			= "";
						}
					}

					if ($doDebug) {
						echo "<br />Processing $advisor_call_sign<br />";
					}
		
					$advisorCount++;
					
					$content					.= "<tr><th>Call Sign</th>
														<th>Last Name</th>
														<th>First Name</th>
														<th>Email</th>
														<th>Phone</th>
														<th>City</th>
														<th>State</th>
														<th>Country</th>
														<th>Survey Score</th>
														<th>Verify Response</th>
														<th>Timezone</th></tr>
													<tr><td>$advisor_call_sign</td>
														<td>$advisor_last_name</td>
														<td>$advisor_first_name</td>
														<td>$advisor_email</td>
														<td>+$advisor_ph_code $advisor_phone</td>
														<td>$advisor_city</td>
														<td>$advisor_state</td>
														<td>$advisor_country</td>
														<td>$advisor_survey_score</td>
														<td>$advisor_verify_response</td>
														<td>$advisor_timezone_id</td></tr>
													<tr><td><b>Class</b></td>
														<td><b>Class Size</b></td>
														<td><b>Level</b></td>
														<td><b>Class Teaching Times</b></td>
														<td colspan='2'><b>Class Teaching Days</b></td>
														<td colspan='3'><b>Class Teaching Times UTC</b></td>
														<td colspan='2'><b>Class Teaching Days UTC</b></td>
													</tr>";

					// now go get all the class records for this advisor
					$firstTime				= TRUE;
					$sql					= "select * from $advisorClassTableName 
												where advisorclass_call_sign='$advisor_call_sign' 
												and advisorclass_semester='$inp_semester' 
												order by advisorclass_sequence";
					$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numACRows			= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and found $numACRows rows<br />";
						}
						if ($numACRows > 0) {
							foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
								$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
								$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
								$advisorClass_sequence 					= $advisorClassRow->advisorclass_sequence;
								$advisorClass_semester 					= $advisorClassRow->advisorclass_semester;
								$advisorClass_timezone_offset			= $advisorClassRow->advisorclass_timezone_offset;	// new
								$advisorClass_level 					= $advisorClassRow->advisorclass_level;
								$advisorClass_class_size 				= $advisorClassRow->advisorclass_class_size;
								$advisorClass_class_schedule_days 		= $advisorClassRow->advisorclass_class_schedule_days;
								$advisorClass_class_schedule_times 		= $advisorClassRow->advisorclass_class_schedule_times;
								$advisorClass_class_schedule_days_utc 	= $advisorClassRow->advisorclass_class_schedule_days_utc;
								$advisorClass_class_schedule_times_utc 	= $advisorClassRow->advisorclass_class_schedule_times_utc;
								$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
								$advisorClass_class_incomplete 			= $advisorClassRow->advisorclass_class_incomplete;
								$advisorClass_date_created				= $advisorClassRow->advisorclass_date_created;
								$advisorClass_date_updated				= $advisorClassRow->advisorclass_date_updated;
						
								$classesCount++;
						
								$content .= "<tr><td>$advisorClass_sequence</td>
												<td>$advisorClass_class_size</td>
												<td>$advisorClass_level</td>
												<td>$advisorClass_class_schedule_times</td>
												<td colspan='2'>$advisorClass_class_schedule_days</td>
												<td colspan='3'>$advisorClass_class_schedule_times_utc</td>
												<td colspan='2'>$advisorClass_class_schedule_days_utc</td>
											</tr>";	
							}
						} else {
							$content			.= "<tr><td colspan='11'>No Class Information Available</td></tr>";
						}
					}
				}
			} else {
				$content		.= "No $advisorTableName records found<br />";
			}
		}
		$content		.= "</table>";
		$content .= "$advisorCount Advisor records<br />$classesCount Class records<br />";
	}

	$thisTime 					= date('Y-m-d H:i:s');
	$content					.= "<br /><p>Report displayed at $thisTime.</p>";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog failed</p>";
	}
	return $content;
}
add_shortcode ('display_all_advisors', 'display_all_advisors_func');

