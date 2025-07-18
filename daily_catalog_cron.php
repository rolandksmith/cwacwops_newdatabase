function daily_catalog_cron_process_func() {

/*		Daily Catalog Cron				

   This job is run via a cron curl job to run the associated webpage

	The catalog table contains the advisor classes for each semester
	
	If the current semester is in session, the catalog for the current semester is update, 
		if there are any changes
		
	If the current semester is not in session, the program builds a catalog for the proximate 
		semester if no catalog record exists. If a catalog record exists for the proximate 
		semester, the program will update the catalog.
 
	After the catalog is built, the program displays a nicely formated catalog

	advisorArray: 	advisor callsign
	classesArray: 	[level|time ITC|days] = number of classes
	advisorClasses:	[level|time UTC|days][advisorClassInc] = advisor_call_sign-advisorClass_sequence

	Catalog format:
		level|time UTC|days|number of classes|advisor-sequence ....

	If the catalog is to be generated
		Read the advisor/advisorClass records for the upcoming semester
		

	This job generates the class catalog
	which is stored in a table wpw1_cwa_current_catalog	
	
*/


	global $wpdb, $testMode, $doDebug, $printArray;

	$doDebug				= TRUE;
	$testMode				= FALSE;
	
	$versionNumber			= '5';
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);
	
//	$testEmailTo			= "kcgator@gmail.com,rolandksmith@gmail.com";
	$testEmailTo			= "rolandksmith@gmail.com";

	ini_set('max_execution_time',360);

	$initializationArray 	= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName				= $initializationArray['userName'];
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');


// Needed variables initialization
	$currentSemester		= $initializationArray['currentSemester'];
	$proximateSemester		= $initializationArray['proximateSemester'];
	$siteURL				= $initializationArray['siteurl'];

	$replacementPeriod		= $initializationArray['validReplacementPeriod'];
	$validReplacementPeriod	= FALSE;
	if ($replacementPeriod == 'Y') {
		 $validReplacementPeriod = TRUE;
	}
	$jobname				= "Daily Catalog Cron";
	$catalogReport			= '';
	$errorArray				= array();
	$recordsProcessed		= 0;
	$strPass				= "0";
	$semester				= array();
	$oldCatalog				= array();
	$newCatalogArray		= array();
	$additionsArray			= array();
	$deletionsArray			= array();
	$changesArray			= array();
	
	
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
			$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
			$advisorTableName			= 'wpw1_cwa_advisor2';
			$catalogTableName			= 'wpw1_cwa_current_catalog';
			$userMasterTableName		= "wpw1_cwa_user_master2";
			$mode						= 'TestMode';
		} else {
			$advisorClassTableName		= 'wpw1_cwa_advisorclass';
			$advisorTableName			= 'wpw1_cwa_advisor';
			$catalogTableName			= 'wpw1_cwa_current_catalog';
			$userMasterTableName		= "wpw1_cwa_user_master";
			$mode						= 'Production';
		}
		
		// set up theSemester
		$theSemester					= $currentSemester;
		if ($currentSemester == 'Not in Session') {
			$theSemester				= $proximateSemester;
		}

		/////////// get the current catalog for comparison purposes. It'll either be for 
		/////////// the current or the proximate semester depending on what's in session
		if ($doDebug) {
			echo "<br />Getting the old catalog for comparison purposes<br />";
		}
		$gotOldCatalog				= FALSE;
		$sql 						= "select * from $catalogTableName 
										where semester='$theSemester' 
										and mode='$mode'
										order by date_created DESC 
										limit 1";
		$result						= $wpdb->get_results($sql);
		if ($result === FALSE) {
			$errorArray[]			= "unable to find $catalogTableName table to read the catalog<br />";
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows				= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows records from $catalogTableName<br />";
			}
			if ($numRows > 0) {
				foreach ($result as $catalogRow) {
					$record_id		= $catalogRow->record_id;
					$jsonCatalog	= $catalogRow->catalog;
					$gotOldCatalog	= TRUE;
					
					$oldCatalog		= json_decode($jsonCatalog,TRUE);
					if ($doDebug) {
						echo "got a catalog for $theSemester in mode $mode<br />";
					}
				}
			} else {
				$errorArray[]		= "No previous catalog records found in $catalogTableName table<br />";
				if ($doDebug) {
					echo "No previous catalog records found in $catalogTableName table<br />";
				}
			}
		}

		if ($doDebug) {
			echo "Now get the advisors and their classes<br />";
		}


		//////////	Build the arrays

		/// get each advisor and associated class record for that advisor

		$sql	= "SELECT 
						ac.advisorclass_call_sign, 
						ac.advisorclass_sequence, 
						ac.advisorclass_semester, 
						ac.advisorclass_level, 
						ac.advisorclass_language,
						ac.advisorclass_class_size, 
						ac.advisorclass_class_incomplete, 
						ac.advisorclass_class_schedule_days_utc, 
						ac.advisorclass_class_schedule_times_utc, 
						a.advisor_verify_response, 
						um.user_survey_score 
					FROM wpw1_cwa_advisorclass ac 
					LEFT JOIN wpw1_cwa_user_master um ON ac.advisorclass_call_sign = um.user_call_sign 
					LEFT JOIN wpw1_cwa_advisor a ON ac.advisorclass_call_sign = a.advisor_call_sign 
						and a.advisor_semester = ac.advisorclass_semester 
					WHERE ac.advisorclass_semester = '$proximateSemester' 
					order by ac.advisorclass_call_sign";
							   
		$cwa_advisor		= $wpdb->get_results($sql);
		if ($cwa_advisor === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numARows									= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and found $numARows rows<br />";
			}
			if ($numARows > 0) {
				$newCatalog			= array();
				foreach ($cwa_advisor as $advisorRow) {
					$advisorClass_call_sign 				= strtoupper($advisorRow->advisorclass_call_sign);
					$advisorClass_survey_score 				= $advisorRow->user_survey_score;
					$advisorClass_verify_response 			= strtoupper($advisorRow->advisor_verify_response);
					$advisorClass_sequence 					= $advisorRow->advisorclass_sequence;
					$advisorClass_semester					= $advisorRow->advisorclass_semester;
					$advisorClass_level 					= $advisorRow->advisorclass_level;
					$advisorClass_language					= $advisorRow->advisorclass_language;
					$advisorClass_class_size				= $advisorRow->advisorclass_class_size;
					$advisorClass_class_incomplete 			= $advisorRow->advisorclass_class_incomplete;
					$advisorClass_class_schedule_days_utc 	= $advisorRow->advisorclass_class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc 	= $advisorRow->advisorclass_class_schedule_times_utc;

					if ($doDebug) {
						echo "<br /><b>Processing Advisor $advisorClass_call_sign Sequence $advisorClass_sequence</b> ($advisorClass_survey_score | $advisorClass_verify_response)<br />
							  Level: $advisorClass_level<br />
							  Size: $advisorClass_class_size<br />
							  schedule Days: $advisorClass_class_schedule_days_utc 
							  schedule times: $advisorClass_class_schedule_times_utc<br />";
					}
					if ($advisorClass_survey_score != '6' and $advisorClass_verify_response != 'R') {
						if ($doDebug) {
							echo "Adding $advisorClass_call_sign to advisorArray and processing classes<br />";
						}
						if ($advisorClass_class_incomplete == 'Y') {
							if ($doDebug) {
								echo "&nbsp;&nbsp;&nbsp;&nbsp;advisorClass incomplete. Skipping<br />
									  &nbsp;&nbsp;&nbsp;&nbsp;Value: $advisorClass_class_incomplete<br />";
							}
							$errorArray[]	= "advisorClass incomplete for $advisorClass_call_sign, $advisorClass_sequence. Skipped.<br />";
						} else {
							// fix up the class schedule times to be on the hour
							$myStr1 		= substr($advisorClass_class_schedule_times_utc,0,2);
							$advisorClass_class_schedule_times_utc	= $myStr1 . "00";

							// add this class to the newCatalog
							if (isset($newCatalog[$advisorClass_level][$advisorClass_language]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"])) {
								$classArray		= $newCatalog[$advisorClass_level][$advisorClass_language]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"];
								$classArray[]	= "$advisorClass_call_sign-$advisorClass_sequence";
								$newCatalog[$advisorClass_level][$advisorClass_language]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"] 	= $classArray;
							} else {
								$newCatalog[$advisorClass_level][$advisorClass_language]["$advisorClass_class_schedule_times_utc $advisorClass_class_schedule_days_utc"] 	= array("$advisorClass_call_sign-$advisorClass_sequence");
							}

						}
					} else {
						if ($doDebug) {
							echo "$advisorClass_call_sign has issues with survey score or verify response<br />";
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "No records found for $nextSemester<br />";
				}
			}
		}

		if ($gotOldCatalog) {
			ksort($oldCatalog);
			if ($doDebug) {
				echo "<br />oldCatalog:<br /><pre>";
				print_r($oldCatalog);
				echo "</pre><br />";
			}
		} else {
			if ($doDebug) {
				echo "no oldCatalog!!!!<br />";
			}
		}
		ksort($newCatalog);
		if ($doDebug) {
			echo "<br />newCatalog:<br /><pre>";
			print_r($newCatalog);
			echo "</pre><br />";
		}



		// get the differences
		$flatOldCatalog		= array();
		foreach($oldCatalog as $thisLevel => $levelData) {
			foreach($levelData as $thisLanguage => $languageData) {
				foreach($languageData as $thisSched => $schedData) {
					foreach($schedData as $thisSeq => $thisClass) {
						$flatOldCatalog[] = "$thisLevel | $thisLanguage | $thisSched | $thisClass";
					}
				}
			}
		}
		sort($flatOldCatalog);
		
		$flatNewCatalog		= array();
		foreach($newCatalog as $thisLevel => $levelData) {
			foreach($levelData as $thisLanguage => $languageData) {
				foreach($languageData as $thisSched => $schedData) {
					foreach($schedData as $thisSeq => $thisClass) {
						$flatNewCatalog[] = "$thisLevel | $thisLanguage | $thisSched | $thisClass";
					}
				}
			}
		}
		sort($flatNewCatalog);
		
		if ($doDebug) {
			echo "flatOldCatalog:<br /><pre>";
			print_r($flatOldCatalog);
			echo "</pre><br />flatNewCatalog:<br /><pre>";
			print_r($flatNewCatalog);
			echo "</pre><br /><br />";
		}
		$differences		= array_diff($flatNewCatalog,$flatOldCatalog);
		if ($doDebug) {
			echo "array addition differences:<br /><pre>";
			print_r($differences);
			echo "</pre><br />";
		}
		
		$gotDifferences		= FALSE;
		$content	.= "<h4>Additions to the Catalog</h4>";
		if (count($differences) > 0) {
			$gotDifferences	= TRUE;
			$content		.= "<table><tr><th>Level</th><th>Language</th><th>UTC Schedule</th><th>Advisor</th></tr>";
			foreach($differences as $thisSeq => $thisData) {
				$thisData	= "<tr><td>$thisData</td></tr>";
				$thisData	= str_replace(' | ','</td><td>',$thisData);
				$content	.= $thisData;
			}
			$content		.= "</table>";
		}
		$differences		= array_diff($flatOldCatalog,$flatNewCatalog);
		if ($doDebug) {
			echo "array deletion differences:<br /><pre>";
			print_r($differences);
			echo "</pre><br />";
		}
		$content	.= "<h4>Deletions from the Catalog</h4>";
		if (count($differences) > 0) {
			$gotDifferences	= TRUE;
			$content		.= "<table><tr><th>Level</th><th>Language</th><th>UTC Schedule</th><th>Advisor</th></tr>";
			foreach($differences as $thisSeq => $thisData) {
				$thisData	= "<tr><td>$thisData</td></tr>";
				$thisData	= str_replace(' | ','</td><td>',$thisData);
				$content	.= $thisData;
			}
			$content		.= "</table>";
		}
		
		
		$catalogReport			= "<h4>Generated Class Catalog for $theSemester</h4>";
		$beginnerCatalog		= "";
		$beginnerCount			= 0;
		$fundamentalCatalog		= "";
		$fundamentalCount		= 0;
		$intermediateCatalog	= "";
		$intermediateCount		= 0;
		$advancedCatalog		= "";
		$advancedCount			= 0;
		$totalCount				= 0;
		foreach($flatNewCatalog as $thisSeq => $thisData) {
			$displayData		= "<tr><td>$thisData</td></tr>";
			$displayData		= str_replace(' | ','</td><td>',$displayData);
			if (str_contains($thisData,'Beginner')) {
				$beginnerCatalog	.= $displayData;
				$beginnerCount++;
				$totalCount++;
			} elseif (str_contains($thisData,'Fundamental')) {
				$fundamentalCatalog	.= $displayData;
				$fundamentalCount++;
				$totalCount++;
			} elseif (str_contains($thisData,'Intermediate')) {
				$intermediateCatalog	.= $displayData;
				$intermediateCount++;
				$totalCount++;
			} elseif (str_contains($thisData,'Advanced')) {
				$advancedCatalog	.= $displayData;
				$advancedCount++;
				$totalCount++;
			} else {
				if ($doDebug) {
					echo "don't know what to do with $thisData<br />";
				}
			}
		}
		
		$content		.= "<br /><h4>Current Catalog</h4>
							<table>
							<tr><th>Level</th>
								<th>Language</th>
								<th>UTC Schedule</th>
								<th>Advisor</th></tr>
							$beginnerCatalog
							<tr><td colspan='4'>$beginnerCount Beginner classes</td></tr>
							<tr><th>Level</th>
								<th>Language</th>
								<th>UTC Schedule</th>
								<th>Advisor</th></tr>
							$fundamentalCatalog
							<tr><td colspan='4'>$fundamentalCount Fundamental classes</td></tr>
							<tr><th>Level</th>
								<th>Language</th>
								<th>UTC Schedule</th>
								<th>Advisor</th></tr>
							$intermediateCatalog
							<tr><td colspan='4'>$intermediateCount Intermediate classes</td></tr>
							<tr><th>Level</th>
								<th>Language</th>
								<th>UTC Schedule</th>
								<th>Advisor</th></tr>
							$advancedCatalog
							<tr><td colspan='4'>$advancedCount Advanced classes</td></tr>
							<tr><td colspan='4'>$totalCount Total classes</td></tr>
							</table>";
		
		// update the catalog if needed
		if ($gotDifferences) {
			$catalogToStore		= json_encode($newCatalog);
			if ($doDebug) {
				echo "<br />have Differences - CatalogToStore:<br />";
				echo "$catalogToStore<br /><br />";
			}
			
			if ($gotOldCatalog) {			// update if record exists else insert
				if ($doDebug) {
					echo "updating catalog<br />";
				}
				$updateResult	= $wpdb->update($catalogTableName,
												array('catalog'=>$catalogToStore),
												array('record_id'=>$record_id),
												array('%s'),
												array('%d'));
				if ($updateResult === FALSE) {
					handleWPDBError($jobname,$doDebug,'attempting to update $catalogTableName');
					$content	.= "<p>Updating $catalogTableName failed</p>";
				} else {
					$content	.= "<p>$catalogTableName updated</p>";
				}
			} else {
				if ($doDebug) {
					echo "inserting new record<br />";
				}
				$insertResult	= $wpdb->insert($catalogTableName,
												array('semester'=>$theSemester,
													  'mode'=>$mode,
													  'catalog'=>$catalogToStore),
												array('%s','%s','%s'));
				if ($insertResult === FALSE) {
					handleWPDBError($jobname,$doDebug,"attempting to insert into $catalogTableName");
					$content	.= "<p>Inserting new $catalogTableName record failed</p>";
				} else {
					$contet		.= "<p>$catalogTableName updated</p>";
				}
			}
		} else {
			$content	.= "<p>No changes to the catalog</p>";
		}

		$nowDate		= date('Y-m-d');
		$nowTime		= date('H:i:s');

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
		$reminder_text	= "<b>Daily Catalog Cron</b> To view the Daily Catalog Cron report for $nowDate $nowTime, click <a href='cwa-display-saved-report/?strpass=3&inp_callsign=XXXXX&inp_id=$reportid&token=$token' target='_blank'>Display Report</a>";
		$inputParams		= array("effective_date|$effective_date|s",
									"close_date|$close_date|s",
									"resolved_date||s",
									"send_reminder|N|s",
									"send_once|N|s",
									"call_sign||s",
									"role|administrator|s",
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
/*
		$theSubject	= "CWA Daily Catalog Cron Process";
		$theContent	= "The daily catalog cron process was run at $nowDate $nowTime, Login to <a href='$siteURL/program-list'>CW Academy</a> to see the 
						report.";
		if ($testMode) {		
			$theRecipient	= '';
			$mailCode	= 2;
			$theSubject = "TESTMODE $theSubject";
		} else {
			$theRecipient	= '';
			$mailCode		= 18;
		}
		$result		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
										 		  'theSubject'=>$theSubject,
										 		  'jobname'=>$jobname,
										 		  'theContent'=>$theContent,
										 		  'mailCode'=>$mailCode,
										 		  'testMode'=>$testMode,
										 		  'doDebug'=>$doDebug));
*/		

		$thisTime 		= date('Y-m-d H:i:s');
		$content		.= "<br />Function completed at $thisTime<br />";
		$endingMicroTime = microtime(TRUE);
		$elapsedTime	= $endingMicroTime - $startingMicroTime;
		$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
		$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
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
		return $content;
	}
}	
add_shortcode ('daily_catalog_cron_process', 'daily_catalog_cron_process_func');
