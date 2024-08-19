function advisor_service_report_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
 	$userName						= $initializationArray['userName'];
	$currentTimestamp				= $initializationArray['currentTimestamp'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL						= $initializationArray['siteurl'];
	$userName						= $initializationArray['userName'];
	$userEmail						= $initializationArray['userEmail'];
	$userDisplayName				= $initializationArray['userDisplayName'];
	$userRole						= $initializationArray['userRole'];

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
		$doDebug			= FALSE;
		$testMode			= FALSE;
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
	$theURL						= "$siteURL/cwa-advisor-service-report/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Advisor Service Report V$versionNumber";
	$categoryClass				= array(6,12,24,48,60);
	$limit_categories			= 'Y';

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
			if ($str_key 		== "limit_categories") {
				$limit_categories	 = $str_value;
				$limit_categories	 = filter_var($limit_categories,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_rsave") {
				$inp_rsave	 = $str_value;
				$inp_rsave	 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
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
		$advisorServiceTableName	= "wpw1_cwa_advisor_service2";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
	} else {
		$extMode					= 'pd';
		$advisorServiceTableName	= "wpw1_cwa_advisor_service";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Click submit to run the report</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top'>Limit Categories</td>
								<td><input type='radio' class='formInputButton' name='limit_categories' value='N' required>All categories<br />
									<input type='radio' class='formInputButton' name='limit_categories' value='Y' required>Only 6, 12, 24, 48, and 60</td></tr>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass with limit_categories: $limit_categories<br />";
		}
		
		$content			.= "<h3>$jobname</h3>";
		if ($limit_categories == 'Y') {
			$content		.= "Showing only 6, 12, 24, 48, and 60 classes";
		} else {
			$content		.= "Showing all class categories";
		}
		
		$advisorInfoArray	= array();
		// get the list of advisors 
		$sql				= "select distinct(advisor) as advisorcallsign 
								from $advisorServiceTableName 
								order by advisor";
		$advisorServiceResult	= $wpdb->get_results($sql);
		if ($advisorServiceResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numARows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numARows rows<br />";
			}
			if ($numARows > 0) {
				foreach($advisorServiceResult as $advisorServiceRow) {
					$advisor		= $advisorServiceRow->advisorcallsign;
					
					// get the number of classes for the advisor
					$sumSQL			= "select sum(classes) as advisorclasses 
										from $advisorServiceTableName 
										where advisor = '$advisor'";
					$sumResult		= $wpdb->get_var($sumSQL);
					if ($sumResult === NULL) {
						handleWPDBError($jobname,$doDebug);
					} else {
						if ($doDebug) {
							$lastQuery	= $wpdb->last_query;
							echo "ran $lastQuery<br />and retrieved $sumResult<br />";
						}
						if ($limit_categories == 'Y') {
							if (in_array($sumResult,$categoryClass)) {
								$sumStr		= str_pad($sumResult,3,'0',STR_PAD_LEFT);
								$advisorInfoArray[]	= "$sumStr&$advisor";
							}
						} else {
							$sumStr		= str_pad($sumResult,3,'0',STR_PAD_LEFT);
							$advisorInfoArray[]	= "$sumStr&$advisor";
						}
					}
				}
			} else {
				if ($doDebug) {
					echo "No records found in $advisorServiceTableName<br />";
				}
				$content	.= "No data found in $advisorServiceTableName";
			}
		}
		
		$myInt				= count($advisorInfoArray);
		if ($myInt > 0) {
			$prevCount		= 0;
			$firstTime		= TRUE;
			asort($advisorInfoArray);
			if ($doDebug) {
				echo "<br />advisorInfoArray:<br /><pre>";
				print_r($advisorInfoArray);
				echo "</pre><br />";
			}
//			$content		.= "<table style='width:auto'>
//								<tr><th>Number Classes</th>
//									<th>Advisor - Name - Latest Semester</th></tr>";
			$content		.= "<pre>\nclasses\tcallsign\tname\tsemester\n";
			foreach($advisorInfoArray as $thisData) {
				$myArray			= explode("&",$thisData);
				$classCount			= intval($myArray[0]);
				$advisor			= $myArray[1];

/*		
				if ($classCount != $prevCount) {
					if ($firstTime) {
						$firstTime	= FALSE;
					} else {
						$content	.= "<tr><td colspan='2'><hr></td></tr>";
					}
					$countStr		= $classCount;
				} else {
					$countStr		= "&nbsp;";
				}
				$prevCount			= $classCount;
*/

				$advisorSQL			= "select first_name,
											   last_name,
											   semester  
										from $advisorTableName 
										where call_sign = '$advisor' 
										order by date_created DESC 
										limit 1";
				$advisorResult		= $wpdb->get_results($advisorSQL);
				if ($advisorResult === FALSE) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numARows		= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $advisorSQL<br />and retrieved $numARows rows<br />";
					}
					if ($numARows > 0) {
						foreach($advisorResult as $advisorResultRow) {
							$advisorFirstName	= $advisorResultRow->first_name;
							$advisorLastName	= $advisorResultRow->last_name;
							$advisorSemester	= $advisorResultRow->semester;
							$nameStr			= "$advisorLastName, $advisorFirstName";
						}
					} else {
						$advisorFirstName	= "";
						$advisorLastName	= "";
						$advisorSemester	= "";
						$nameStr			= "";
					}

				}
				
				
//				$content			.= "<tr><td>$countStr</td>
//											<td>$advisor $nameStr</td></tr>";
				$content			.= "$classCount\t$advisor\t$nameStr\t$advisorSemester\n";
			}
//			$content				.= "<tr><td colspan='2'><hr></td></tr></table>
			$content				.= "</pre>
										<p>If last semester is empty, it was before 2020 Jan/Feb</p>
										<p>$myInt Advisors Reported</p>";
			
		}	
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as $jobname<br />";
		}
		$storeResult	= storeReportData_v2("$jobname",$content);
		if ($storeResult[0] !== FALSE) {
			$content	.= "<br />Report stored in reports pod as $storeResult[1]";
		} else {
			$content	.= "<br />Storing the report in the reports table failed";
		}
	}
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
add_shortcode ('advisor_service_report', 'advisor_service_report_func');
