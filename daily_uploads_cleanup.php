function daily_uploads_cleanup_func() {

/*		Daily Uploads Cleanup			

   This job is run via a cron curl job to run the associated webpage
   
   The job reads the uploads directory on cwa.cwops.org and deletes 
   any .csv files that are more than 4 hours old
   and displays all other non-mp3 files
   
   Created 10Apr2025 by Roland

*/


	global $wpdb, $testMode, $doDebug, $printArray;

	$doDebug				= TRUE;
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
	$jobname				= "Daily uploads Cleanup V$versionNumber";
	$recordsProcessed		= 0;
	$strPass				= "0";
	$dirFiles				= array();
	$deletedCount			= 0;
	
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
			$mode						= 'TestMode';
		} else {
			$mode						= 'Production';
		}
		
		$content			.= "<h3>$jobname</h3>";
		
		// get the directory content
		if (str_contains($siteURL,"localhost") {
			$filePath	= "/Users/rksmih/cwa-docker/www/wp-content/uploads/";
		} else {
			$filePath	= "/home/cwacwops/public_html/wp-content/uploads/";
		}
		$dirFiles		= scandir($filePath);
		$myInt			= count($dirFiles);
		$content		.= "<p>Have $myInt entries in $filePath</p>";
		foreach($dirFiles as $thisSeq => $thisFileName) {
//			if ($doDebug) {
//				echo "processing thisFileName: $thisFileName<br />";
//			}
			$doBypass	= FALSE;
			if ($thisFileName == '.') {
				$doBypass	= TRUE;
			} elseif ($thisFileName == '..') {
				$doBypass	= TRUE;
			} elseif ($thisFileName == '2022') {
				$doBypass	= TRUE;
			} elseif (preg_match('/mp3/',$thisFileName)) {
				$doBypass	= TRUE;
			}
			if (!$doBypass) {
//				if ($doDebug) {
//					echo "not bypassing<br />";
//				}
//				$content	.= "File: $thisFileName<br />";
				$wholePath	= $filePath . $thisFileName;

				if (preg_match('/.csv/',$thisFileName)) {
					// delete the file if more than 4 hours old
					$fileTime	= filectime($wholePath);
					$timeDiff	= $currentTime - $fileTime;
					$age		= round($timeDiff / 3600.0,1);
					if ($doDebug) {
						echo "processing $wholePath which is $age hours old<br />";
					}
					if ($age > 4.0) {
						$result	= unlink($wholePath);
// $result = TRUE;
						if ($result === TRUE) {
							$content	.= "File: $thisFileName DELETED<br />";
							$deletedCount++;
						} else {
							$content	.= "File: $thisFileName DELETION FAILED<br />";
						}
					}
				}
				if (preg_match('/daily_student_cron_debug/',$thisFileName)) {
					// delete file if more than 7 days old
					$fileTime	= filectime($wholePath);
					$timeDiff	= $currentTime - $fileTime;
					$age		= round($timeDiff / 3600.0,1);
					if ($age > 168.0) {
						$result	= unlink($wholePath);
// $result = TRUE;
						if ($result === TRUE) {
							$content	.= "File: $thisFileName DELETED<br />";
							$deletedCount++;
						} else {
							$content	.= "File: $thisFileName DELETION FAILED<br />";
						}
					}					
				}

			}
		}
		$content		.= "<br />$deletedCount Files Deleted<br />";

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
		$effective_date		= date('Y-m-d 00:00:00');
		$closeStr			= strtotime("+ 2 days");
		$close_date			= date('Y-m-d 00:00:00',$closeStr);

		$token			= mt_rand();
		$reminder_text	= "<b>$jobname</b> To view the Daily Uploads Cleanup report for $effective_date, click <a href='cwa-display-saved-report/?strpass=3&inp_callsign=K7OJL&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
		$inputParams		= array("effective_date|$effective_date|s",
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

//		$theSubject	= "$jobname";
//		$theContent	= "$jobname was run at $nowDate $nowTime, Login to <a href='$siteURL/program-list'>CW Academy</a> to see the 
//						report.";
//		if ($testMode) {		
//			$theRecipient	= '';
//			$mailCode	= 1;
//			$theSubject = "TESTMODE $theSubject";
//		} else {
//			$theRecipient	= '';
//			$mailCode		= 16;
//		}
//		$result		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
//										 		  'theSubject'=>$theSubject,
//										 		  'jobname'=>$jobname,
//										 		  'theContent'=>$theContent,
//										 		  'mailCode'=>$mailCode,
//										 		  'testMode'=>$testMode,
//										 		  'doDebug'=>$doDebug));
//		if ($result === TRUE) {
			$content	.= "Process completed";
//		} else {
//			$content .= "<br />The final mail send function to $theRecipient failed.</p>";
			return $content;
//		}
	}
}	
add_shortcode ('daily_uploads_cleanup', 'daily_uploads_cleanup_func');
