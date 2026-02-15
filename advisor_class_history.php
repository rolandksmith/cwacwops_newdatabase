function advisor_class_history_func() {

/*


	Modified 1Oct24 by Roland for new database
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 						= $context->validUser;
	$userName						= $context->userName;
	$userRole						= $context->userRole;
	$currentTimestamp				= $context->currentTimestamp;
	$validTestmode					= $context->validTestmode;
	$siteURL						= $context->siteurl;

	if ($userName == '') {
		return "You are not authorized";
	}
	
	if ($userRole != 'administrator') {		// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
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
	$theURL						= "$siteURL/cwa-advisor-class-history/";
	$inp_semester				= '';
	$jobname					= "Advisor Class History V$versionNumber";
	$pastSemestersArray			= $context->pastSemestersArray;
	$currentSemester			= $context->currentSemester;
	$nextSemester				= $context->nextSemester;
	$prevSemester				= $context->prevSemester;
	$advisorData				= array();
	$showArrayDetail			= FALSE;

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
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_advisor") {
				$inp_advisor	 = strtoupper($str_value);
				$inp_advisor	 = filter_var($inp_advisor,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_detail") {
				$inp_detail	 	= $str_value;
				$inp_detail	 	= filter_var($inp_detail,FILTER_UNSAFE_RAW);
				if ($inp_detail == 'Y') {
					$showArrayDetail	= TRUE;
				}
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
		$tableName					= "wpw1_cwa_audit_log2";
		$advisorClassTableName		= "wpw1_cwa_advisorclass2";
		$userMasterTableName		= 'wpw1_cwa_user_master2';
	} else {
		$extMode					= 'pd';
		$tableName					= "wpw1_cwa_audit_log";
		$advisorClassTableName		= "wpw1_cwa_advisorclass";
		$userMasterTableName		= 'wpw1_cwa_user_master';
	}



	if ("1" == $strPass) {
		$optionList			= "";
		$thisChecked		= "";
		if ($currentSemester != 'Not in Session') {
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$currentSemester' checked='checked'> $currentSemester<br />";
			if ($doDebug) {
				echo "added $currentSemester to option list<br />";
			}
		} else {
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$nextSemester' checked='checked'> $nextSemester<br />";
			$thisChecked	= "checked";
			if ($doDebug) {
				echo "added $prevSemester to option list<br />";
			}
		}
		foreach($pastSemestersArray as $thisSemester) {
			$optionList		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$thisSemester'> $thisSemester<br />";
			if ($doDebug) {
				echo "Added $thisSemester to option list<br />";
			}
		}
		if ($doDebug) {
			echo "optionlist complete<br />";
		}


		$content 		.= "<h3>$jobname</h3>
							<p>This program shows the evolution of the students in 
							an advisor's class from the data stored in the audit log<p>
							<p>Select the semester and enter the advisor callsign<br />
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;width:auto;'>
							<tr><td>Advisor</td>
								<td><input type='text' class='formInputText' name='inp_advisor' size='15' maxlength='50' autofocus></td>
							<tr><td style='vertical-align:top;'>Semester</td>
								<td>$optionList</td></tr>
							$testModeOption
							<tr><td style='vertical-align:top;'>Show array detail</td>
								<td><input type='radio' class='formInputButton' name='inp_detail' value='N' checked>No<br />
									<input type='radio' class='formInputButton' name='inp_detail' value='Y'>Yes</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at $strPass pass<br />
				inp_advisor: $inp_advisor<br />
				inp_semester: $inp_semester<br />";
		}
		$content		.= "<h3>$jobname</h3>";
		$doProceed		= TRUE;
		// get the advisor record for that semester
		$classSQL					= "select * from $advisorClassTableName 
										left join $userMasterTableName on user_call_sign = advisorclass_call_sign 
										where advisorclass_call_sign = '$inp_advisor' 
										and advisorclass_semester = '$inp_semester'";
		$wpw1_cwa_advisorclass		= $wpdb->get_results($classSQL);
		if ($wpw1_cwa_advisorclass === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$doProceed				= FALSE;
		} else {
			$numACRows				= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $classSQL<br />and found $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
					$advisorClass_master_ID 				= $advisorClassRow->user_ID;
					$advisorClass_master_call_sign			= $advisorClassRow->user_call_sign;
					$advisorClass_first_name 				= $advisorClassRow->user_first_name;
					$advisorClass_last_name 				= $advisorClassRow->user_last_name;
					$advisorClass_email 					= $advisorClassRow->user_email;
					$advisorClass_ph_code 					= $advisorClassRow->user_ph_code;
					$advisorClass_phone 					= $advisorClassRow->user_phone;
					$advisorClass_city 						= $advisorClassRow->user_city;
					$advisorClass_state 					= $advisorClassRow->user_state;
					$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
					$advisorClass_country_code 				= $advisorClassRow->user_country_code;
					$advisorClass_country	 				= $advisorClassRow->user_country;
					$advisorClass_whatsapp 					= $advisorClassRow->user_whatsapp;
					$advisorClass_telegram 					= $advisorClassRow->user_telegram;
					$advisorClass_signal 					= $advisorClassRow->user_signal;
					$advisorClass_messenger 				= $advisorClassRow->user_messenger;
					$advisorClass_master_action_log 		= $advisorClassRow->user_action_log;
					$advisorClass_timezone_id 				= $advisorClassRow->user_timezone_id;
					$advisorClass_languages 				= $advisorClassRow->user_languages;
					$advisorClass_survey_score 				= $advisorClassRow->user_survey_score;
					$advisorClass_is_admin					= $advisorClassRow->user_is_admin;
					$advisorClass_role 						= $advisorClassRow->user_role;
					$advisorClass_master_date_created 		= $advisorClassRow->user_date_created;
					$advisorClass_master_date_updated 		= $advisorClassRow->user_date_updated;

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
					$advisorClass_student01 				= $advisorClassRow->advisorclass_student01;
					$advisorClass_student02 				= $advisorClassRow->advisorclass_student02;
					$advisorClass_student03 				= $advisorClassRow->advisorclass_student03;
					$advisorClass_student04 				= $advisorClassRow->advisorclass_student04;
					$advisorClass_student05 				= $advisorClassRow->advisorclass_student05;
					$advisorClass_student06 				= $advisorClassRow->advisorclass_student06;
					$advisorClass_student07 				= $advisorClassRow->advisorclass_student07;
					$advisorClass_student08 				= $advisorClassRow->advisorclass_student08;
					$advisorClass_student09 				= $advisorClassRow->advisorclass_student09;
					$advisorClass_student10 				= $advisorClassRow->advisorclass_student10;
					$advisorClass_student11 				= $advisorClassRow->advisorclass_student11;
					$advisorClass_student12 				= $advisorClassRow->advisorclass_student12;
					$advisorClass_student13 				= $advisorClassRow->advisorclass_student13;
					$advisorClass_student14 				= $advisorClassRow->advisorclass_student14;
					$advisorClass_student15 				= $advisorClassRow->advisorclass_student15;
					$advisorClass_student16 				= $advisorClassRow->advisorclass_student16;
					$advisorClass_student17 				= $advisorClassRow->advisorclass_student17;
					$advisorClass_student18 				= $advisorClassRow->advisorclass_student18;
					$advisorClass_student19 				= $advisorClassRow->advisorclass_student19;
					$advisorClass_student20 				= $advisorClassRow->advisorclass_student20;
					$advisorClass_student21 				= $advisorClassRow->advisorclass_student21;
					$advisorClass_student22 				= $advisorClassRow->advisorclass_student22;
					$advisorClass_student23 				= $advisorClassRow->advisorclass_student23;
					$advisorClass_student24 				= $advisorClassRow->advisorclass_student24;
					$advisorClass_student25 				= $advisorClassRow->advisorclass_student25;
					$advisorClass_student26 				= $advisorClassRow->advisorclass_student26;
					$advisorClass_student27 				= $advisorClassRow->advisorclass_student27;
					$advisorClass_student28 				= $advisorClassRow->advisorclass_student28;
					$advisorClass_student29 				= $advisorClassRow->advisorclass_student29;
					$advisorClass_student30 				= $advisorClassRow->advisorclass_student30;
					$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
					$advisorClass_class_evaluation_complete = $advisorClassRow->advisorclass_evaluation_complete;
					$advisorClass_class_comments			= $advisorClassRow->advisorclass_class_comments;
					$advisorClass_copycontrol				= $advisorClassRow->advisorclass_copy_control;
		
					$content		.= "<h4>$inp_advisor Class $advisorClass_sequence Level $advisorClass_level</h4>";
		
					$sql 			= "SELECT * FROM $tableName  
										WHERE logtype = 'CLASS' 
										and logmode = 'PRODUCTION' 
										and logcallsign = '$inp_advisor' 
										and logsemester like '%$inp_semester%' 
										and logsequence = $advisorClass_sequence 
										order by logid, logdate, date_created";
					$auditResult	= $wpdb->get_results($sql);
					if ($auditResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numRows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and retrieved $numRows rows<br />";
						}
						if ($numRows > 0) {
							$prevLogid			= '';
							$prevLogdate		= '';
							$content			.= "<table>";
							$rowCount			= 0;
							foreach ($auditResult as $auditResultRow) {
								$record_id		= $auditResultRow->record_id;
								$logtype		= $auditResultRow->logtype;
								$logmode		= $auditResultRow->logmode;
								$logdate		= $auditResultRow->logdate;
								$logprogram		= $auditResultRow->logprogram;
								$logwho			= $auditResultRow->logwho;
								$logid			= $auditResultRow->logid;
								$logsemester	= $auditResultRow->logsemester;
								$logsequence	= $auditResultRow->logsequence;
								$logcallsign	= $auditResultRow->logcallsign;
								$logdata		= $auditResultRow->logdata;
								$date_created	= $auditResultRow->date_created;
				
								$myInt			= strtotime($logdate);
								$logDate		= date('Y-m-d H:i',$myInt);
				
								if ($doDebug) {
									echo "<br />logid: $logid; logdate: $logdate<br />";
								}
				
								$trigger			= FALSE;
								$result = preg_match('/student\\d\\d/i',$logdata);
								if ($result == 1) {
									$trigger 		= TRUE;
									$rowCount++;
									$myArray		= json_decode($logdata,TRUE);
									if ($doDebug) {
										echo "triggered<br /><pre>";
										print_r($myArray);
										echo "</pre><br />";
									}
									$content		.= "<tr><td colspan='4'><b>Record $rowCount</b></td></tr>
														<tr><td style='width:250px;vertical-align:top;'><b>Audit Record ID</b><br />$record_id</td>
															<td style='width:250px;vertical-align:top;'><b>Date</b><br />$logdate</td>
															<td style='width:250px;vertical-align:top;'><b>Program</b><br />$logprogram</td>
															<td style='width:250px;vertical-align:top;'><b>Type</b><br />$logtype</td></tr>
														<tr><td style='vertical-align:top;'><b>who</b><br />$logwho</td>
															<td style='vertical-align:top;'><b>ID</b><br />$logid</td>
															<td style='vertical-align:top;'><b>Semester</b><br />$logsemester</td>
															<td style='vertical-align:top;'><b>Sequence</b><br />$logsequence</td></tr>
														<tr><td style='vertical-align:top;'><b>Callsign</b><br />$logcallsign</td>
															<td style='vertical-align:top;'><b>Date Created</b><br />$date_created</td>
															<td style='vertical-align:top;'></td>
															<td style='vertical-align:top;'></td></tr>";
									$spot			= 1;
									foreach($myArray as $thisField => $thisValue) {
										switch ($spot) {
											case 1:
												$content	.= "<tr><td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td>";
												$spot		= 2;
												break;
											case 2:
												$content	.= "\t<td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td>";
												$spot		= 3;
												break;
											case 3:
												$content	.= "\t<td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td>";
												$spot		= 4;
												break;
											Case 4:
												$content	.= "\t<td style='vertical-align:top;'><b>$thisField</b><br />$thisValue</td></tr>";
												$spot		= 1;
												break;
										}					
									}
									// finish off this row
									switch ($spot) {
										case 1:
											break;
										case 2:
											$content		.= "\t<td></td><td></td><td></td></tr>";
											break;
										case 3:
											$content		.= "\t<td></td><td></td></tr>";
											break;
										case 4:
											$content		.= "\t<td></td></tr>";
											break;
									}
									$content				.= "<tr><td colspan='4'><hr></td></tr>";
								} else {
									if ($doDebug) {
										echo "no student. Not triggered<br />";
									}
								}
							}
							$content		.= "</table>";
						} else {
							echo "<p>No CLASS audit log records found for $inp_advisor</p>";
						}
					}
				}
			} else {
				$content		.= "<p>No advisorClass record found for $inp_advisor</p>";
				$doProceed		= FALSE;
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
add_shortcode ('advisor_class_history', 'advisor_class_history_func');

