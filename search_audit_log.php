function search_audit_log_func() {
	
/*	Search and Display Audit Log Data

*/

	global $doDebug, $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];

	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		ini_set('memory_limit','256M');

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$inp_log					= '';
	$inp_callsign				= '';
	$theURL						= "$siteURL/cwa-search-audit-log/";
	$filename					= '';
	$displayCount				= 0;
	$inp_verbose				= '';
	$jobname					= "Search Audit Log";
	$fieldArray					= array();

$headerArray	= array(
						'advisor' => 'Advisor',
						'advisor_class_timezone' => 'Advisor Class Timezone',
						'age' => 'Age',
						'assigned_advisor_class' => 'Assigned Advisor Class',
						'available_class_days' => 'Available Class Days',
						'call_sign' => 'Callsign',
						'callsign' => 'Callsign',
						'city' => 'City',
						'class_comments' => 'Class Comments',
						'class_priority' => 'Class Priority',
						'copy_control' => 'Copy Control',
						'country' => 'Country',
						'email' => 'Email',
						'email_number' => 'Email Number',
						'evaluationcomplete' => 'Evaluation Complete',
						'evaluation_complete' => 'Evaluation Complete',
						'excluded_advisor' => 'Excluded Advisor',
						'first_class_choice' => 'First ClassChoice',
						'first_class_choice_utc' => 'First Class Choice UTC',
						'first_name' => 'First Name',
						'firstname' => 'First Name',
						'hold_override' => 'Hold Override',
						'hold_reason_code' => 'Hold Reason Code',
						'intervention_required' => 'Intervention Required',
						'last_name' => 'Last Name',
						'lastname' => 'Last Name',
						'level' => 'Level',
						'logaction' => 'Log Action',
						'logdate' => 'Log Date',
						'logid' => 'Log ID',
						'logmode' => 'Log Mode',
						'logprogram' => 'Log Program',
						'logsemester' => 'Log Semester',
						'logsubtype' => 'Log Subtype',
						'logtype' => 'Log Type',
						'logwho' => 'Log Who',
						'messaging' => 'Messaging',
						'number_students' => 'Number Students',
						'phone' => 'Phone',
						'pre_assigned_advisor' => 'Pre-assigned Advisor',
						'promotable' => 'Promotable',
						'request_date' => 'Request Date',
						'second_class_choice' => 'Second Class Choice',
						'second_class_choice_utc' => 'Second Class Choice UTC',
						'semester' => 'Semester',
						'start_time' => 'Start Time',
						'state' => 'State',
						'student_ID' => 'Student ID',
						'student_parent' => 'Student Parent',
						'student_parent_email' => 'Student Parent Email',
						'student_status' => 'Student Status',
						'student_survey_completion_date' => 'Survey Completion Date',
						'third_class_choice' => 'Third Class Choice',
						'third_class_choice_utc' => 'Third Class Choice UTC',
						'time_zone' => 'Timezone',
						'welcome_date' => 'Welcome Date',
						'youth' => 'Youth',
						'zip_code' => 'Zip Code',
						'student01' => 'Student 01',
						'student02' => 'Student 02',
						'student03' => 'Student 03',
						'student04' => 'Student 04',
						'student05' => 'Student 05',
						'student06' => 'Student 06',
						'student07' => 'Student 07',
						'student08' => 'Student 08',
						'student09' => 'Student 09',
						'student10' => 'Student 10',
						'student11' => 'Student 11',
						'student12' => 'Student 12',
						'student13' => 'Student 13',
						'student14' => 'Student 14',
						'student15' => 'Student 15',
						'student16' => 'Student 16',
						'student17' => 'Student 17',
						'student18' => 'Student 18',
						'student19' => 'Student 19',
						'student20' => 'Student 20',
						'student21' => 'Student 21',
						'student22' => 'Student 22',
						'student23' => 'Student 23',
						'student24' => 'Student 24',
						'student25' => 'Student 25',
						'student26' => 'Student 26',
						'student27' => 'Student 27',
						'student28' => 'Student 28',
						'student29' => 'Student 29',
						'student30' => 'Student 30',
						'advisor_ID' => 'Advisor ID',
						'advisor_call_sign' => 'Advisor Callsign',
						'advisor_first_name' => 'Advisor First Name',
						'advisor_id' => 'Advisor ID',
						'advisor_last_name' => 'Advisor Last Name',
						'class_ID' => 'Class ID',
						'class_incomplete' => 'Class Incomplete',
						'class_schedule_days' => 'Class Schedule Days',
						'class_schedule_days_utc' => 'Class Schedule Days UTC',
						'class_schedule_times' => 'Class Schedule Times',
						'class_schedule_times_utc' => 'Class Schedule Times UTC',
						'class_size' => 'Class Size',
						'class_verified' => 'Class Verified',
						'country_code' => 'Country Code',
						'fifo_date' => 'FIFO Date',
						'languages' => 'Languages',
						'logcallsign' => 'Log Callsign',
						'messenger_app' => 'Messenger',
						'ph_code' => 'PH Code',
						'select_sequence' => 'Select Sequence',
						'sequence' => 'Sequence',
						'signal_app' => 'Signal',
						'survey_score' => 'Survey Score',
						'telegram_app' => 'Telegram',
						'text_message' => 'Text Message',
						'timezone_id' => 'Timezone ID',
						'timezone_offset' => 'Timezone Offset',
						'verify_email_date' => 'Verify Email Date',
						'verify_email_number' => 'Verify Email Number',
						'verify_response' => 'Verify Response',
						'welcome_email_date' => 'Welcome Email Date',
						'whatsapp_app' => 'whatsapp',
						'wpm' => 'Word per Minute',
						'abandoned' => 'Abandoned',
						'advisor_select_date' => 'Advisor Select Date',
						'assigned_advisor' => 'Assigned Advisor',
						'email_sent_date' => 'Email Sent Date',
						'no_catalog' => 'No Catalog',
						'notes' => 'Notes',
						'response' => 'Response',
						'response_date' => 'Response Date',
						'selected_date' => 'Selected Date',
						'waiting_list' => 'Waiting List');




	
	if ($testMode) {
		$auditLogTableName		= 'wpw1_cwa_audit_log2';
	} else {
		$auditLogTableName		= 'wpw1_cwa_audit_log';
	}



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
			if ($str_key 		== "inp_logtype") {
				$inp_logtype	 	= $str_value;
				$inp_logtype	 	= filter_var($inp_logtype,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_logsubtype") {
				$inp_logsubtype		 = $str_value;
				$inp_logsubtype		 = filter_var($inp_logsubtype,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	= $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'yes') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_testmode") {
				$inp_testmode	= $str_value;
				$inp_testmode	 = filter_var($inp_testmode,FILTER_UNSAFE_RAW);
				if ($inp_testmode == 'yes') {
					$testMode	= TRUE;
				}
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
		$content 		.= "<h3>$jobname</h3>

<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table>
tr><td style='vertical-align:top;'>Call Sign of Interest</td>
	<td style='vertical-align:top;'><input type='text' class='formInputText' name='inp_callsign' size='20' maxlength='20' autofocus required></td></tr>
<tr><td style='vertical-align:top;'>Verbosity</td>
	<td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_verbose' value='no' checked='checked'> Normal Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='yes'> Highly Verbose</td></tr>
<tr><td style='vertical-align:top;'>Test Mode</td>
	<td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_testmode' value='no' checked='checked'> Production<br />
									<input type='radio' class='formInputButton' name='inp_testmode' value='yes'> Test Mode</td></tr>
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
</table></form>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}
		$thisMode		= "Production";
		if ($testMode) {
			$thisMode	= "testMode";
		}
		$sql			= "select * from $auditLogTableName 
						   where logcallsign='$inp_callsign' 
						   and logmode = '$thisMode' 
						   order by logdate";
		$wpw1_cwa_auditlog	= $wpdb->get_results($sql);
		if ($wpw1_cwa_auditlog === FALSE) {
			if ($doDebug) {
				echo "Reading from $auditLogTableName failed<br />
					  <wpdb->last_query: " . $wpdb->last_query . "<br />
					  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
			$content	.= "<p>Invalid data obtained from $auditLogTableName</p>";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and retrieved $numRows records<br />";
			}
			if ($numRows > 0) {
				$content		.= "<h3>$jobname for $inp_callsign</h3><p>$numRows records to display</p>";
				$rowCount		= 0;
				foreach($wpw1_cwa_auditlog as $auditlogRow) {
					$rowCount++;
					$logtype		= $auditlogRow->logtype;
					$logmode		= $auditlogRow->logmode;
					$logsubtype		= $auditlogRow->logsubtype;
					$logaction		= $auditlogRow->logaction;
					$logdate		= $auditlogRow->logdate;
					$logprogram		= $auditlogRow->logprogram;
					$logwho			= $auditlogRow->logwho;
					$logid			= $auditlogRow->logid;
					$logsemester	= $auditlogRow->logsemester;
					$logcallsign	= $auditlogRow->logcallsign;
					$date_created	= $auditlogRow->date_created;
					$logdata		= $auditlogRow->logdata;
					$myArray		= json_decode($logdata,TRUE);
					$myStr	 		= json_last_error();
//					if ($myStr != '') {
//						$content	.= "Row: $rowCount json_last_error: $myStr<br />";
//					}
					if ($doDebug) {
						echo "Decoded line record $rowCount:<br /><pre>";
						print_r($myArray);
						echo "</pre><br />";
					}
					$myStr			= '';
					if ($logtype == 'STUDENT' && $logsubtype == 'STUDENT') {
						$myStr		= 'Student Record';
					}
					if ($logtype == 'ADVISOR' && $logsubtype == 'ADVISOR') {
						$myStr		= 'Advisor Record';
					}
					if ($logtype == 'ADVISOR' && $logsubtype == 'STUDENT') {
						$myStr 		= 'Advisor Update of Student';
					}
					if ($logtype == 'ADVISOR' && $logsubtype == 'CLASS') {
						$myStr		= 'Advisor Class Record';
					}
					$content		.= "<h4>Record $rowCount $myStr</h4>
										<table>";
					$ii				= 0;
					foreach($myArray as $myField=>$myValue) {
 						if (array_key_exists($myField,$headerArray)) {
 							$fieldHeader	= $headerArray[$myField];
 						} else {
							if (!in_array($myField,$fieldArray)) {
								$fieldArray[]		= $myField;
								$fieldHeader		= $myField;
//								echo "Got unknown field $myField on record $rowCount<br />";
							}
						}
						$ii++;
						switch($ii) {
							case 1:
								$content	.= "<tr><td style='vertical-align:top;'><u><b>$fieldHeader</b></u><br />$myValue</td>\n";
								break;
							case 2:
							case 3:
							case 4:
							case 5:
								$content	.= "<td style='vertical-align:top;'><u><b>$fieldHeader</b></u><br />$myValue</td>\n";
								break;
							case 6:
								$content	.= "<td style='vertical-align:top;'><u><b>$fieldHeader</b></u><br />$myValue</td></tr>\n";
								$ii			= 0;
								break;
						}
					}
					/// put out the date created					
					$ii++;
					switch($ii) {
						case 1:
							$content	.= "<tr><td style='vertical-align:top;'><u><b>Date Created</b></u><br />$date_created</td>\n";
							break;
						case 2:
						case 3:
						case 4:
						case 5:
							$content	.= "<td style='vertical-align:top;'><u><b>Date Created</b></u><br />$date_created</td>\n";
							break;
						case 6:
							$content	.= "<td style='vertical-align:top;'><u><b>Date Created</b></u><br />$date_created</td></tr>\n";
							$ii			= 0;
							break;
					}
					
					
					if ($ii<6) {
						for ($jj=$ii;$jj<6;$jj++) {
							$content		.= "<td></td>";
						}
						$content			.= "</td>";
					}
					$content	.= "</table>";
				}
			} else {
				$content	.= "No data found in $auditLogTableName";
			}
		}
		if (count($fieldArray) > 0) {
			$content			.= "<br /><h3>List of Fields Needing Headers</h3>";
			sort ($fieldArray);
			foreach($fieldArray as $thisValue) {
				$content		.= "$thisValue<br />";
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
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('search_audit_log','search_audit_log_func');
