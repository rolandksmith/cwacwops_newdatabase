function daily_temp_data_cleanup_func() {

/*		Daily temp_data Cleanup			

   This job is run via a cron curl job to run the associated webpage
   
   The job reads the temp_data table and reports any records where the token 
   is 'admin' (user has been taken over and needs to be restored)
   
   It reports any assessment records that occurred during the past 24 
   hours and deletes any assessment records more than 24 hours old. 
   
   Created 8Apr2025 by Roland

*/


	global $wpdb, $testMode, $doDebug, $printArray;

	$doDebug				= FALSE;
	$testMode				= FALSE;
	
	$versionNumber			= '1';
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	

	ini_set('max_execution_time',360);

	$initializationArray 	= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName				= $initializationArray['userName'];
	$siteURL				= $initializationArray['siteURL'];
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');


// Needed variables initialization
	$jobname				= "Daily temp_data Cleanup V$versionNumber";
	$recordsProcessed		= 0;
	$strPass				= "0";
	$currentTime			= strtotime(date('Y-m-d H:i:s'));
	
	
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
		
	$runTheJob				= TRUE;
		
	if ($userName != '') {
		$content 			.= "<h3>$jobname Process Executed by $userName</h3>";
	} else {
		$content			.= "<h3>$jobname Automatically Executed</h3>";
		$userName			= "CRON";

		$runTheJob			= allow_job_to_run($doDebug);
	}
	if ($runTheJob) {
	
		if ($testMode) {
			$content .= "<p><strong>Function is under development.</strong></p>";
			$tempDataTableName			= "wpw1_cwa_temp_data2";
			$mode						= 'TestMode';
		} else {
			$tempDataTableName			= "wpw1_cwa_temp_data";
			$mode						= 'Production';
		}
		
		$adminReport		= "<h4>Admin Report</h4>";
		$adminCount			= 0;
		$assessmentReport	= "<h4>Assessment Report</h4>";
		$assessmentCount	= 0;
		$otherReport		= "<h4>Unknown Record Types Report</h4>";
		$otherCount			= 0;
		
		// process all records in temp_data
		$sql			= "select * from $tempDataTableName 
							order by date_written";
		$tempResult		= $wpdb->get_results($sql);
		if ($tempResult === FALSE) {
			handleWPDBError($jobname,$doDebug,'initial read of temp_data');
			$content	.= "<p>Reading $tempDataTableName table failed</p>";
		} else {
			$numTRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numTRows rows<br />";
			}
			if ($numTRows > 0) {
				foreach ($tempResult as $tempResultRow) {
					$record_id		= $tempResultRow->record_id;
					$callsign		= $tempResultRow->callsign;
					$token			= $tempResultRow->token;
					$temp_data		= $tempResultRow->temp_data;
					$date_written	= $tempResultRow->date_written;
					
					$recordsProcessed++;
					
					// calculate how old the record is
					$recordTime 	= strtotime($date_written);
					$timeDiff		= $currentTime - $recordTime;
					$thisAge		= round($timeDiff / 3600.0,1);
					$someOther		= FALSE;
					$deleteThis		= FALSE;
					
					if ($doDebug) {
						echo "<br />Processing record_id $record_id<br />
								callsign: $callsign<br />
								token: $token<br />
								date_written: $date_written<br />
								age: $thisAge<br />";
					}
					
					if ($token == 'admin') {	// have a user taken over record
						$myArray		= json_decode($temp_data,TRUE);
						if ($doDebug) {
							echo "have admin record:<br /><pre>";
							print_r($myArray);
							echo "</pre><br />";
						}
						$userName		= $myArray['username'];
						$adminReport	.= "Account for $callsign was taken over by $userName on $date_written<br />";
						$adminCount++;
						if ($doDebug) {
							echo "adminReport record written<br />";
						}
					} else {	// some other kind of record
						$myStr		= base64_decode($temp_data,TRUE);	// if returns FALSE, it is not encoded but probably a json variable
						if ($myStr === FALSE) {
							$someOther	= TRUE;
							if ($doDebug) {
								echo "attempted base64_decode which returned FALSE<br />";
							}
						} else {
							$myInt			= strpos($myStr,'inp_method=studentreg');
							if ($myInt === FALSE) {
								$someOther	= TRUE;
							} else {
								if ($doDebug) {
									echo "Possibly have an assessment record:<br />
										$myStr<br />";
								}
								$inp_email				= '';
								$myArray				= explode("&",$myStr);
								foreach($myArray as $thisSeq => $thisValue) {
									$myArray1			= explode("=",$thisValue);
									$thisField		= $myArray1[0];
									$thisContent	= $myArray1[1];
									if ($thisField 	== 'inp_email') {
										$inp_email	= $thisContent;
									}
									if ($thisField 	== 'inp_callsign') {
										$inp_callsign	= $thisContent;
									}
								}
								$myStr				= "";
								if ($thisAge > 24.0) {
									$myStr			= "<em>To be Deleted</em>";
									$deleteThis		= TRUE;
								}
								$studentLink		= "<a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$callsign&inp_depth=one&doDebug&testMode' target='_blank'>$callsign</a>";
								$assessmentLink		= "<a href='siteURL/cwa-display-cw-assessment-for-a-callsign/?strpass=2&inp_callsign=$callsign' target='_blank'>View Assessment</a>";
								$userMasterLink		 = "<a href='$siteURL/cwa-display-and-update-user-master/?strpass=2&request_type=callsign&request_info=$callsign&doDebug&testMode' target='_blank'>View User_Master</a>";
								$emailLink			 = "<a href='mailto:$inp_email?subject=CW Academy -- Do You Need Help?'>$inp_email</a>";
								$assessmentReport	.= "Assessment Record <em>Callsign:</em> $studentLink <em>Email:</em> $emailLink <em>Age:</em> $thisAge hours $myStr<br />
														$assessmentLink ... $userMasterLink<br /><br />";
								$assessmentCount++;
								if ($doDebug) {
									echo "assessmentReport record written<br />";
								}
							}
						}
					}

					if ($someOther) {
						$myStr5				= addslashes($temp_data);
						$otherReport		.= "<b>Unknown</b> record type: record_id: $record_id<br />
												callsign:$callsign<br />
												temp_date: $myStr5<br />
												date_written: $date_written<br /><br />";
						$otherCount++;
						if ($doDebug) {
							echo "otherReport record writen<br />";
						}

					}
					
					if ($deleteThis) {
						$deleteResult		= $wpdb->delete($tempDataTableName,
												array('record_id'=>$record_id),
												array('%d'));
						if ($deleteResult === FALSE) {
							handleWPDBError($jobname,$doDebug,"trying to delete $record_id");
							$content		.= "Deleting $record_id failed<br />";
						} else {
//							$adminReport	.= "Deleting id $record_id affected $deleteResult rows<br />";
						}
					}					
				}
				if ($adminCount > 0) {
					$content	.= $adminReport;
				}
				if ($assessmentCount > 0) {
	 				$content	.= $assessmentReport;
	 			}
	 			if ($otherCount > 0) {
					$content	.= $otherReport;
				}
			} else {
				$content	.= "<p>No $tempDataTableName table records to process</p>";
			}
		}
							

		$thisTime 		= date('Y-m-d H:i:s');
		$content		.= "<br />Function completed at $thisTime<br />";
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
		$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|0: $elapsedTime");
		if ($result == 'FAIL') {
			$content	.= "<p>writing to joblog.txt failed</p>";
		}
		// store the report in the reports table
		$storeResult	= storeReportData_v2($jobname,$content,$testMode,$doDebug);
		if ($storeResult[0] === FALSE) {
			if ($doDebug) {
				echo "storing report failed. $storeResult[1]<br />";
			}
			$content	.= "Storing report failed. $storeResult[1]<br />";
		} else {
			$reportid	= $storeResult[2];
		}
		// store the reminder
		$closeStr		= strtotime("+2 days");
		$close_date		= date('Y-m-d H:i:s', $closeStr);
		$token			= mt_rand();
		$reminder_text	= "<b>$jobname</b> To view the Daily Temp Data Cleanup report for $nowDate $nowTime, click <a href='cwa-display-saved-report/?strpass=3&inp_callsign=K7OJL&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
		$inputParams		= array("effective_date|$nowDate $nowTime|s",
									"close_date|$close_date|s",
									"resolved_date||s",
									"send_reminder|N|s",
									"send_once|N|s",
									"call_sign|K7OJL|s",
									"role||s",
									"email_text||s",
									"reminder_text|$reminder_text|s",
									"resolved|N|s",
									"token|$token|s");
		$reminderResult	= add_reminder($inputParams,$testMode,$doDebug);
		if ($reminderResult[0] === FALSE) {
			if ($doDebug) {
				echo "adding reminder failed. $reminderResult[1]<br />";
			}
		}

		$theSubject	= "$jobname";
		$theContent	= "$jobname was run at $nowDate $nowTime, Login to <a href='$siteURL/program-list'>CW Academy</a> to see the 
						report.";
		if ($testMode) {		
			$theRecipient	= '';
			$mailCode	= 1;
			$theSubject = "TESTMODE $theSubject";
		} else {
			$theRecipient	= '';
			$mailCode		= 16;
		}
		$result		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
										 		  'theSubject'=>$theSubject,
										 		  'jobname'=>$jobname,
										 		  'theContent'=>$theContent,
										 		  'mailCode'=>$mailCode,
										 		  'testMode'=>$testMode,
										 		  'doDebug'=>$doDebug));
		if ($result === TRUE) {
			return "Process completed";
		} else {
			$content .= "<br />The final mail send function to $theRecipient failed.</p>";
			return $content;
		}
	}
}	
add_shortcode ('daily_temp_data_cleanup', 'daily_temp_data_cleanup_func');
