function advisor_class_history_func() {

/*


	Modified 1Oct24 by Roland for new database
*/

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$userName						= $initializationArray['userName'];
	$userRole						= $initializationArray['userRole'];
	$currentTimestamp				= $initializationArray['currentTimestamp'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL						= $initializationArray['siteurl'];

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
		print_r($initializationArray);
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
	$pastSemestersArray			= $initializationArray['pastSemestersArray'];
	$currentSemester			= $initializationArray['currentSemester'];
	$nextSemester				= $initializationArray['nextSemester'];
	$prevSemester				= $initializationArray['prevSemester'];
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
		
		// get the advisor record for that semester
		
		
		$classArray			= array();
		$myInc				= 0;
//		$inp_semester 		= str_replace("/","-",$inp_semester);
		if ($doDebug) {
			echo "transformed inp_semester to $inp_semester<br />";
		}
		$doProceed		= TRUE;
		
		$sql 			= "SELECT * FROM $tableName  
							WHERE logtype = 'ADVISOR' 
							and logmode = 'PRODUCTION' 
							and logcallsign = '$inp_advisor' 
							and logsemester like '%$inp_semester%' 
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
				$content			.= "<h3>$jobname for Advisor $inp_advisor in $inp_semester Semester</h3>";
				$prevLogid			= '';
				$prevLogdate		= '';
				foreach ($auditResult as $auditResultRow) {
					$record_id		= $auditResultRow->record_id;
					$logtype		= $auditResultRow->logtype;
					$logmode		= $auditResultRow->logmode;
					$logdate		= $auditResultRow->logdate;
					$logprogram		= $auditResultRow->logprogram;
					$logwho			= $auditResultRow->logwho;
					$logid			= $auditResultRow->logid;
					$logsemester	= $auditResultRow->logsemester;
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
						$thisArray		= json_decode($logdata,TRUE);
						if ($doDebug) {
							echo "triggered<br />";
						}
					} else {
						if ($doDebug) {
							echo "no student. Not triggered<br />";
						}
					}
	
					if ($trigger) {
						if ($doDebug) {
							echo "data array:<br /><pre>";
							print_r($thisArray);
							echo "</pre><br />";
						}
						if ($doDebug) {
							echo "checking to see if $logid is a new id<br />";
						}
						if ($logid != $prevLogid) {
							if ($doDebug) {
								echo "new $logid of $logid<br />";
							}
							$prevLogid				= $logid;
							$prevLogDate			= '';
							$classArray[$logid]			= array();
							// get the advisor information
							$classSQL					= "select * from $advisorClassTableName 
															left join $userMasterTableName on advisorclass_call_sign = user_call_sign 
															where advisorclass_id = $logid";
							$wpw1_cwa_advisorclass				= $wpdb->get_results($classSQL);
							if ($wpw1_cwa_advisorclass === FALSE) {
								handleWPDBError($jobname,$doDebug);
								$doProceed				= FALSE;
							} else {
								$numACRows						= $wpdb->num_rows;
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
										$advisorClass_phone 					= $advisorClassRow->user_phone;
										$advisorClass_city 						= $advisorClassRow->user_city;
										$advisorClass_state 					= $advisorClassRow->user_state;
										$advisorClass_zip_code 					= $advisorClassRow->user_zip_code;
										$advisorClass_country_code 				= $advisorClassRow->user_country_code;
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
										
										if ($advisor_last_name == NULL) {
											$advisor_last_name = 'Unknown';
											$advisor_first_name = '';
										}
										
										
										$advisorData[$logid]['name']			= "$advisorClass_last_name, $advisorClass_first_name";
										$advisorData[$logid]['level']			= $advisorClass_level;
										$advisorData[$logid]['size']			= $advisorClass_class_size;
										$advisorData[$logid]['count']			= $class_number_students;
										$advisorData[$logid]['student01']		= $advisorClass_student01;
										$advisorData[$logid]['student02']		= $advisorClass_student02;
										$advisorData[$logid]['student03']		= $advisorClass_student03;
										$advisorData[$logid]['student04']		= $advisorClass_student04;
										$advisorData[$logid]['student05']		= $advisorClass_student05;
										$advisorData[$logid]['student06']		= $advisorClass_student06;
										$advisorData[$logid]['student07']		= $advisorClass_student07;
										$advisorData[$logid]['student08']		= $advisorClass_student08;
										$advisorData[$logid]['student09']		= $advisorClass_student09;
										$advisorData[$logid]['student10']		= $advisorClass_student10;
										$advisorData[$logid]['student11']		= $advisorClass_student11;
										$advisorData[$logid]['student12']		= $advisorClass_student12;
										$advisorData[$logid]['student13']		= $advisorClass_student13;
										$advisorData[$logid]['student14']		= $advisorClass_student14;
										$advisorData[$logid]['student15']		= $advisorClass_student15;
										$advisorData[$logid]['student16']		= $advisorClass_student16;
										$advisorData[$logid]['student17']		= $advisorClass_student17;
										$advisorData[$logid]['student18']		= $advisorClass_student18;
										$advisorData[$logid]['student19']		= $advisorClass_student19;
										$advisorData[$logid]['student20']		= $advisorClass_student20;
										$advisorData[$logid]['student21']		= $advisorClass_student21;
										$advisorData[$logid]['student22']		= $advisorClass_student22;
										$advisorData[$logid]['student23']		= $advisorClass_student23;
										$advisorData[$logid]['student24']		= $advisorClass_student24;
										$advisorData[$logid]['student25']		= $advisorClass_student25;
										$advisorData[$logid]['student26']		= $advisorClass_student26;
										$advisorData[$logid]['student27']		= $advisorClass_student27;
										$advisorData[$logid]['student28']		= $advisorClass_student28;
										$advisorData[$logid]['student29']		= $advisorClass_student29;
										$advisorData[$logid]['student30']		= $advisorClass_student30;
										
										if ($doDebug) {
											echo "created a new classArray for $logid<br />
												  Inserted a new record in advisorData:<br /><pre>";
											print_r($advisorData);
											echo "</pre><br /
												  end of new logid logic<br /><br />";
										}
									}
								} else {
									if ($doDebug) {
										echo "No advisorclass record found for advisor $inp_advisor record_id $logid<br />";
									}
									$content	.= "No advisorclass record found for advisor $inp_advisor record_id $logid<br />";
									$doProceed	= FALSE;
								}
							}
						}
						if ($doProceed) {
							$myInt			= strtotime($logdate);
							$logdate		= date('Y-m-d H:i',$myInt);
							if ($logdate != $prevLogDate) {
								$prevLogDate					= $logdate;
								$myInc++;
								if ($doDebug) {
									echo "new logdate $logdate - adding a new $logid row at sequence $myInc<br />";
								}
								$classArray[$logid][$myInc]['program']		= $logprogram;
								$classArray[$logid][$myInc]['logdate']		= $logdate;
								$classArray[$logid][$myInc]['logaction']	= $logaction;
		
								for($ii=1;$ii<=30;$ii++) {
									if ($ii < 10) {
										$strSnum	= str_pad($ii,2,'0',STR_PAD_LEFT);
									} else {
										$strSnum	= strval($ii);
									}
									$classArray[$logid][$myInc]['student' . $strSnum] = ''; 
								}
							}
							foreach($thisArray as $thisKey=>$thisValue) {
								$result = preg_match('/student\\d\\d/i',$thisKey);
								if ($result == 1) {
									if ($thisValue == '') {
										$thisValue			= "(deleted)";
									}
									$classArray[$logid][$myInc][$thisKey]	= $thisValue;
									if($doDebug) {
										echo "set $logid - $myInc - $thisKey to $thisValue<br />";
									}
								}
							}
							if ($showArrayDetail) {
								if ($doDebug) {
									echo "<br />classArray:<br /><pre>";
									print_r($classArray);
									echo "</pre><br />";
								}
							}
						}
					}
				}
// return $content;
	//			ksort($classArray);
			
				if (count($classArray) > 0) {
			
			
					if ($doDebug) {
						echo "<br />classArray:<br /><pre>";
						print_r($classArray);
						echo "</pre><br />";
					}
		
					ksort($classArray);
		// $doDebug = TRUE;	
					$mm						= 0;
					$previd					= '';
					$firstTime				= TRUE;
					foreach($classArray as $logid=>$thisSequence) {
						if ($logid != $previd) {
							if ($doDebug) {
								echo "<br />Hae a new logid of $logid<br />";
							}
							$thisName		= $advisorData[$logid]['name'];
							$thisLevel		= $advisorData[$logid]['level'];
							if ($firstTime) {
								if ($doDebug) {
									echo "first time through, so no totals<br />";
								}
								$firstTime	= FALSE;
								$mm++;
								$content			.= "<h4>$thisName Class $mm $thisLevel</h4>
													<table style='width:1200px;'>";
							} else {
								if ($doDebug) {
									echo "Finishing previous logid, starting a new one<br />";
								}
								$mm++;
								$student01		= $advisorData[$previd]['student01'];
								$student02		= $advisorData[$previd]['student02'];
								$student03		= $advisorData[$previd]['student03'];
								$student04		= $advisorData[$previd]['student04'];
								$student05		= $advisorData[$previd]['student05'];
								$student06		= $advisorData[$previd]['student06'];
								$student07		= $advisorData[$previd]['student07'];
								$student08		= $advisorData[$previd]['student08'];
								$student09		= $advisorData[$previd]['student09'];
								$student10		= $advisorData[$previd]['student10'];
								$student11		= $advisorData[$previd]['student11'];
								$student12		= $advisorData[$previd]['student12'];
								$student13		= $advisorData[$previd]['student13'];
								$student14		= $advisorData[$previd]['student14'];
								$student15		= $advisorData[$previd]['student15'];
								$student16		= $advisorData[$previd]['student16'];
								$student17		= $advisorData[$previd]['student17'];
								$student18		= $advisorData[$previd]['student18'];
								$student19		= $advisorData[$previd]['student19'];
								$student20		= $advisorData[$previd]['student20'];
								$student21		= $advisorData[$previd]['student21'];
								$student22		= $advisorData[$previd]['student22'];
								$student23		= $advisorData[$previd]['student23'];
								$student24		= $advisorData[$previd]['student24'];
								$student25		= $advisorData[$previd]['student25'];
								$student26		= $advisorData[$previd]['student26'];
								$student27		= $advisorData[$previd]['student27'];
								$student28		= $advisorData[$previd]['student28'];
								$student29		= $advisorData[$previd]['student20'];
								$student30		= $advisorData[$previd]['student30'];
								$classSize		= $advisorData[$previd]['size'];
								$classCount		= $advisorData[$previd]['count'];
								$content	.= "<tr><td style='vertical-align:top;'><b>Current Class</b></td>
													<td style='vertical-align:top;'>Student01/16</td>
													<td style='vertical-align:top;'>Student02/17</td>
													<td style='vertical-align:top;'>Student03/18</td>
													<td style='vertical-align:top;'>Student04/19</td>
													<td style='vertical-align:top;'>Student05/20</td>
													<td style='vertical-align:top;'>Student06/21</td>
													<td style='vertical-align:top;'>Student07/22</td>
													<td style='vertical-align:top;'>Student08/23</td>
													<td style='vertical-align:top;'>Student09/24</td>
													<td style='vertical-align:top;'>Student10/25</td>
													<td style='vertical-align:top;'>Student11/26</td>
													<td style='vertical-align:top;'>Student12/27</td>
													<td style='vertical-align:top;'>Student13/28</td>
													<td style='vertical-align:top;'>Student14/29</td>
													<td style='vertical-align:top;'>Student15/30</td></tr>
												<tr><td style='vertical-align:top;'>$classSize&nbsp;&nbsp;$classCount</td>
													<td style='vertical-align:top;'>$student01</td>
													<td style='vertical-align:top;'>$student02</td>
													<td style='vertical-align:top;'>$student03</td>
													<td style='vertical-align:top;'>$student04</td>
													<td style='vertical-align:top;'>$student05</td>
													<td style='vertical-align:top;'>$student06</td>
													<td style='vertical-align:top;'>$student07</td>
													<td style='vertical-align:top;'>$student08</td>
													<td style='vertical-align:top;'>$student09</td>
													<td style='vertical-align:top;'>$student10</td>
													<td style='vertical-align:top;'>$student11</td>
													<td style='vertical-align:top;'>$student12</td>
													<td style='vertical-align:top;'>$student13</td>
													<td style='vertical-align:top;'>$student14</td>
													<td style='vertical-align:top;'>$student15</td></tr>
												<tr><td style='vertical-align:top;'></td>
													<td style='vertical-align:top;'>$student16</td>
													<td style='vertical-align:top;'>$student17</td>
													<td style='vertical-align:top;'>$student18</td>
													<td style='vertical-align:top;'>$student19</td>
													<td style='vertical-align:top;'>$student20</td>
													<td style='vertical-align:top;'>$student21</td>
													<td style='vertical-align:top;'>$student22</td>
													<td style='vertical-align:top;'>$student23</td>
													<td style='vertical-align:top;'>$student24</td>
													<td style='vertical-align:top;'>$student25</td>
													<td style='vertical-align:top;'>$student26</td>
													<td style='vertical-align:top;'>$student27</td>
													<td style='vertical-align:top;'>$student28</td>
													<td style='vertical-align:top;'>$student20</td>
													<td style='vertical-align:top;'>$student30</td></tr>
												</table>
												<h4>$thisName Class $mm $thisLevel</h4>
												<table style='width:1200px;'>";
							}
							$previd			= $logid;
							ksort($thisSequence);
							foreach($thisSequence as $myInc=>$myData) {
								$logdate		= $myData['logdate'];
								$logprogram		= $myData['program'];
								$logaction		= $myData['logaction'];
								$student01		= $myData['student01'];
								$student02		= $myData['student02'];
								$student03		= $myData['student03'];
								$student04		= $myData['student04'];
								$student05		= $myData['student05'];
								$student06		= $myData['student06'];
								$student07		= $myData['student07'];
								$student08		= $myData['student08'];
								$student09		= $myData['student09'];
								$student10		= $myData['student10'];
								$student11		= $myData['student11'];
								$student12		= $myData['student12'];
								$student13		= $myData['student13'];
								$student14		= $myData['student14'];
								$student15		= $myData['student15'];
								$student16		= $myData['student16'];
								$student17		= $myData['student17'];
								$student18		= $myData['student18'];
								$student19		= $myData['student19'];
								$student20		= $myData['student20'];
								$student21		= $myData['student21'];
								$student22		= $myData['student22'];
								$student23		= $myData['student23'];
								$student24		= $myData['student24'];
								$student25		= $myData['student25'];
								$student26		= $myData['student26'];
								$student27		= $myData['student27'];
								$student28		= $myData['student28'];
								$student29		= $myData['student20'];
								$student30		= $myData['student30'];
								$content	.= "<tr><td style='vertical-align:top;'>$logdate</td>
													<td style='vertical-align:top;'>Student01/16</td>
													<td style='vertical-align:top;'>Student02/17</td>
													<td style='vertical-align:top;'>Student03/18</td>
													<td style='vertical-align:top;'>Student04/19</td>
													<td style='vertical-align:top;'>Student05/20</td>
													<td style='vertical-align:top;'>Student06/21</td>
													<td style='vertical-align:top;'>Student07/22</td>
													<td style='vertical-align:top;'>Student08/23</td>
													<td style='vertical-align:top;'>Student09/24</td>
													<td style='vertical-align:top;'>Student10/25</td>
													<td style='vertical-align:top;'>Student11/26</td>
													<td style='vertical-align:top;'>Student12/27</td>
													<td style='vertical-align:top;'>Student13/28</td>
													<td style='vertical-align:top;'>Student14/29</td>
													<td style='vertical-align:top;'>Student15/30</td></tr>
												<tr><td style='vertical-align:top;'>$logprogram</td>
													<td style='vertical-align:top;'>$student01</td>
													<td style='vertical-align:top;'>$student02</td>
													<td style='vertical-align:top;'>$student03</td>
													<td style='vertical-align:top;'>$student04</td>
													<td style='vertical-align:top;'>$student05</td>
													<td style='vertical-align:top;'>$student06</td>
													<td style='vertical-align:top;'>$student07</td>
													<td style='vertical-align:top;'>$student08</td>
													<td style='vertical-align:top;'>$student09</td>
													<td style='vertical-align:top;'>$student10</td>
													<td style='vertical-align:top;'>$student11</td>
													<td style='vertical-align:top;'>$student12</td>
													<td style='vertical-align:top;'>$student13</td>
													<td style='vertical-align:top;'>$student14</td>
													<td style='vertical-align:top;'>$student15</td></tr>
												<tr><td style='vertical-align:top;'>$logaction</td>
													<td style='vertical-align:top;'>$student16</td>
													<td style='vertical-align:top;'>$student17</td>
													<td style='vertical-align:top;'>$student18</td>
													<td style='vertical-align:top;'>$student19</td>
													<td style='vertical-align:top;'>$student20</td>
													<td style='vertical-align:top;'>$student21</td>
													<td style='vertical-align:top;'>$student22</td>
													<td style='vertical-align:top;'>$student23</td>
													<td style='vertical-align:top;'>$student24</td>
													<td style='vertical-align:top;'>$student25</td>
													<td style='vertical-align:top;'>$student26</td>
													<td style='vertical-align:top;'>$student27</td>
													<td style='vertical-align:top;'>$student28</td>
													<td style='vertical-align:top;'>$student20</td>
													<td style='vertical-align:top;'>$student30</td></tr>";
							}
						}
					}
					if ($doDebug) {
						echo "Done with all logids. Putting out final class makeup<br />";
					}
					$student01		= $advisorData[$logid]['student01'];
					$student02		= $advisorData[$logid]['student02'];
					$student03		= $advisorData[$logid]['student03'];
					$student04		= $advisorData[$logid]['student04'];
					$student05		= $advisorData[$logid]['student05'];
					$student06		= $advisorData[$logid]['student06'];
					$student07		= $advisorData[$logid]['student07'];
					$student08		= $advisorData[$logid]['student08'];
					$student09		= $advisorData[$logid]['student09'];
					$student10		= $advisorData[$logid]['student10'];
					$student11		= $advisorData[$logid]['student11'];
					$student12		= $advisorData[$logid]['student12'];
					$student13		= $advisorData[$logid]['student13'];
					$student14		= $advisorData[$logid]['student14'];
					$student15		= $advisorData[$logid]['student15'];
					$student16		= $advisorData[$logid]['student16'];
					$student17		= $advisorData[$logid]['student17'];
					$student18		= $advisorData[$logid]['student18'];
					$student19		= $advisorData[$logid]['student19'];
					$student20		= $advisorData[$logid]['student20'];
					$student21		= $advisorData[$logid]['student21'];
					$student22		= $advisorData[$logid]['student22'];
					$student23		= $advisorData[$logid]['student23'];
					$student24		= $advisorData[$logid]['student24'];
					$student25		= $advisorData[$logid]['student25'];
					$student26		= $advisorData[$logid]['student26'];
					$student27		= $advisorData[$logid]['student27'];
					$student28		= $advisorData[$logid]['student28'];
					$student29		= $advisorData[$logid]['student20'];
					$student30		= $advisorData[$logid]['student30'];
					$classSize		= $advisorData[$logid]['size'];
					$classCount		= $advisorData[$logid]['count'];
					$content	.= "<tr><td style='vertical-align:top;'><b>Current Class</b></td>
										<td style='vertical-align:top;'>Student01/16</td>
										<td style='vertical-align:top;'>Student02/17</td>
										<td style='vertical-align:top;'>Student03/18</td>
										<td style='vertical-align:top;'>Student04/19</td>
										<td style='vertical-align:top;'>Student05/20</td>
										<td style='vertical-align:top;'>Student06/21</td>
										<td style='vertical-align:top;'>Student07/22</td>
										<td style='vertical-align:top;'>Student08/23</td>
										<td style='vertical-align:top;'>Student09/24</td>
										<td style='vertical-align:top;'>Student10/25</td>
										<td style='vertical-align:top;'>Student11/26</td>
										<td style='vertical-align:top;'>Student12/27</td>
										<td style='vertical-align:top;'>Student13/28</td>
										<td style='vertical-align:top;'>Student14/29</td>
										<td style='vertical-align:top;'>Student15/30</td></tr>
									<tr><td style='vertical-align:top;'>$classSize&nbsp;&nbsp;$classCount</td>
										<td style='vertical-align:top;'>$student01</td>
										<td style='vertical-align:top;'>$student02</td>
										<td style='vertical-align:top;'>$student03</td>
										<td style='vertical-align:top;'>$student04</td>
										<td style='vertical-align:top;'>$student05</td>
										<td style='vertical-align:top;'>$student06</td>
										<td style='vertical-align:top;'>$student07</td>
										<td style='vertical-align:top;'>$student08</td>
										<td style='vertical-align:top;'>$student09</td>
										<td style='vertical-align:top;'>$student10</td>
										<td style='vertical-align:top;'>$student11</td>
										<td style='vertical-align:top;'>$student12</td>
										<td style='vertical-align:top;'>$student13</td>
										<td style='vertical-align:top;'>$student14</td>
										<td style='vertical-align:top;'>$student15</td></tr>
									<tr><td style='vertical-align:top;'></td>
										<td style='vertical-align:top;'>$student16</td>
										<td style='vertical-align:top;'>$student17</td>
										<td style='vertical-align:top;'>$student18</td>
										<td style='vertical-align:top;'>$student19</td>
										<td style='vertical-align:top;'>$student20</td>
										<td style='vertical-align:top;'>$student21</td>
										<td style='vertical-align:top;'>$student22</td>
										<td style='vertical-align:top;'>$student23</td>
										<td style='vertical-align:top;'>$student24</td>
										<td style='vertical-align:top;'>$student25</td>
										<td style='vertical-align:top;'>$student26</td>
										<td style='vertical-align:top;'>$student27</td>
										<td style='vertical-align:top;'>$student28</td>
										<td style='vertical-align:top;'>$student20</td>
										<td style='vertical-align:top;'>$student30</td></tr>
									</table>";
					if ($doDebug) {
						echo "all output finished<br />";
					}
				} else {
					$content	.= "<p>No student information available</p>";
				}
			} else {
				if ($doDebug) {
					echo "no data found<br />";
				}
				$content			.= "<h3>$jobname</h3><p>No data found</p>";
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
