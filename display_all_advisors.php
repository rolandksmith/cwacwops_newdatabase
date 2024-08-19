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
	
	global $wpdb;
	
	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$doDebug			= FALSE;
	$testMode			= FALSE;
	$userName			= $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];
	$inp_semester		= '';
//	$inp_semester		= $initializationArray['nextSemester'];
	$strPass			= "1";
	$theURL				= "$siteURL/cwa-display-all-advisors/";
	
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
	
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$currentSemester	= $initializationArray['currentSemester'];
		$nextSemester	    = $initializationArray['nextSemester'];
		$semesterTwo	    = $initializationArray['semesterTwo'];
		$semesterThree  	= $initializationArray['semesterThree'];
		$optionList		= "";
		if ($currentSemester != "Not in Session") {
			$optionList	.= "<option value='$currentSemester'>$currentSemester</option>";
		}
		$optionList		.= "<option value='$nextSemester'>$nextSemester</option>";
		$optionList		.= "<option value='$semesterTwo'>$semesterTwo</option>";
		$optionList		.= "<option value='$semesterThree'>$semesterThree</option>";
	
		$content 		.= "<h3>Select the Semester of Interest</h3>
<p>Select from the list below:</p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data''>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'><tr><td style='width:150px;'>Semester</td><td>
<select name='inp_semester' required='required' size='5' autofocus class='formSelect'>
$optionList
</select></td></tr>
<tr><td>&nbsp</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form>";
		return $content;

//////////////

	} elseif ("2" == $strPass) {
		if ($testMode) {
			$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
			$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass2';
			if ($doDebug) {
				echo "Operating in testMode<br />";
			}
			$content					.= "<p><b>Operating in Test Mode</b></p>";
		} else {
			$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
			$advisorClassTableName		= 'wpw1_cwa_consolidated_advisorclass';
		}

		$advisorCount					= 0;
		$classesCount					= 0;
		$content 						.= "<h3>Display all advisors for $inp_semester Semester</h3><table>";
	
		$sql					= "select * from $advisorTableName 
									where  semester='$inp_semester' 
									order by call_sign";
		$wpw1_cwa_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_advisor === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $advisorTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $advisorTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numARows									= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;	
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
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;

					$advisor_last_name 					= no_magic_quotes($advisor_last_name);

				
					if ($doDebug) {
						echo "<br />Processing $advisor_call_sign<br />";
					}
		
					$myInt						= strtotime($advisor_fifo_date);
					$advisor_fifo_date			= date('d/m/Y H:i',$myInt);
					$advisorCount++;
					
					$content					.= "<tr><th>Call Sign</th>
														<th>Last Name</th>
														<th>First Name</th>
														<th>Email</th>
														<th>Phone</th>
														<th>City</th>
														<th>State</th>
														<th>Country</th>
														<th>FIFO Date</th>
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
														<td>$advisor_fifo_date</td>
														<td>$advisor_survey_score</td>
														<td>$advisor_verify_response</td>
														<td>$advisor_timezone_id $advisor_timezone_offset</td></tr>
													<tr><td><b>Class</b></td>
														<td><b>Class Size</b></td>
														<td><b>Level</b></td>
														<td><b>Class Teaching Times</b></td>
														<td colspan='2'><b>Class Teaching Days</b></td>
														<td colspan='3'><b>Class Teaching Times UTC</b></td>
														<td colspan='3'><b>Class Teaching Days UTC</b></td>
													</tr>";

					// now go get all the class records for this advisor
					$firstTime				= TRUE;
					$sql					= "select * from $advisorClassTableName 
												where advisor_call_sign='$advisor_call_sign' 
												and semester='$inp_semester' 
												order by sequence";
					$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
					if ($wpw1_cwa_advisorclass === FALSE) {
						$myError			= $wpdb->last_error;
						$myQuery			= $wpdb->last_query;
						if ($doDebug) {
							echo "Reading $advisorClassTableName table failed<br />
								  wpdb->last_query: $myQuery<br />
								  wpdb->last_error: $myError<br />";
						}
						$errorMsg			= "$jobname Reading $advisorClassTableName table failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
						sendErrorEmail($errorMsg);
					} else {
						$numACRows									= $wpdb->num_rows;
						if ($doDebug) {
							$myStr			= $wpdb->last_query;
							echo "ran $myStr<br />and obtained $numACRows from $advisorClassTableName table<br />";
						}
						if ($numACRows > 0) {
							foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
								$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
								$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
								$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
								$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
								$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
								$advisorClass_sequence 					= $advisorClassRow->sequence;
								$advisorClass_semester 					= $advisorClassRow->semester;
								$advisorClass_timezone 					= $advisorClassRow->time_zone;
								$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
								$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
								$advisorClass_level 					= $advisorClassRow->level;
								$advisorClass_class_size 				= $advisorClassRow->class_size;
								$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
								$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
								$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
								$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
								$advisorClass_action_log 				= $advisorClassRow->action_log;
								$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
								$advisorClass_date_created				= $advisorClassRow->date_created;
								$advisorClass_date_updated				= $advisorClassRow->date_updated;

								$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
						
								$classesCount++;
						
								$content .= "<tr><td>$advisorClass_sequence</td>
												<td>$advisorClass_class_size</td>
												<td>$advisorClass_level</td>
												<td>$advisorClass_class_schedule_times</td>
												<td colspan='2'>$advisorClass_class_schedule_days</td>
												<td colspan='3'>$advisorClass_class_schedule_times_utc</td>
												<td colspan='3'>$advisorClass_class_schedule_days_utc</td>
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
	$result			= write_joblog_func("Display All Advisors|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('display_all_advisors', 'display_all_advisors_func');
