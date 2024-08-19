function update_advisor_service_func() {

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
	$pastSemestersArray				= $initializationArray['pastSemestersArray'];
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
	$theURL						= "$siteURL/cwa-update-advisor-service/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Update Advisor Service V$versionNumber";
	
	$numAdded					= 0;
	$numUpdated					= 0;

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
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
		$advisorServiceTableName	= "wpw1_cwa_advisor_service2";
	} else {
		$extMode					= 'pd';
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass";
		$advisorServiceTableName	= "wpw1_cwa_advisor_service";
	}



	if ("1" == $strPass) {
		$semesterList				= '';
		foreach($pastSemestersArray as $thisSemester) {
			$semesterList			.= "<input type='radio' class='formInputbutton' name='inp_semester' value='$thisSemester' required>$thisSemester<br />";
		}
	
	
		$content 		.= "<h3>$jobname</h3>
							<p>Select the semester of interest</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='width:150px;vertical-align:top;'>Semester</td>
								<td>$semesterList</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass with inp_semetser $inp_semester<br />";
		}
		$content				.= "<h3>$jobname</h3><h4>$inp_semester Semester</h4>";
		
		$advisorArray			= array();
		// get the classes from the requested semester
		$sql			= "select advisor_call_sign, 
								  sequence 
							from $advisorClassTableName 
							where semester = '$inp_semester'";
							
		$advisorClassResult	= $wpdb->get_results($sql);
		if ($advisorClassResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach($advisorClassResult as $advisorClassResultRow) {
					$advisorCallSign		= $advisorClassResultRow->advisor_call_sign;
					$advisorSequence		= $advisorClassResultRow->sequence;
					
					if (array_key_exists($advisorCallSign, $advisorArray)) {
						$advisorArray[$advisorCallSign]['classes']++;
					} else {
						$advisorArray[$advisorCallSign]['classes']	= 1;
					}
				}
			} else {
				$content			.= "No records found for $inp_semester semester";
			}
		}
		
		if (count($advisorArray) > 0) {
			$dateWritten	= date('Y-m-d H:i:s');
			ksort($advisorArray);
			if ($doDebug) {
				echo "<br />Advisor Array:<br /><pre>";
				print_r($advisorArray);
				echo "</pre><br />";
			}
			foreach($advisorArray as $advisorCallsign=>$class) {
				$numClasses			= $advisorArray[$advisorCallsign]['classes'];
				
				$sql				= "select record_id, 
											  advisor, 
											  semester, 
											  classes 
										from $advisorServiceTableName 
										where advisor = '$advisorCallsign' 
										and semester = '$inp_semester'";
				$classResult		= $wpdb->get_results($sql);
				if ($classResult === NULL) {
					handleWPDBError($jobname,$doDebug);
				} else {
					$numCRows		= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $numCRows rows<br />";
					}
					if ($numCRows > 0) {
						foreach($classResult as $classResultRow) {
							$record_id		= $classResultRow->record_id;
							$advisor		= $classResultRow->advisor;
							$semester		= $classResultRow->semester;
							$classes		= $classResultRow->classes;
							
							if ($classes != $numClasses) {
								if ($doDebug) {
									echo "table classes of $classes does not match $numClasses for $advisorCallsign $semester<br />";
								}							
								$dateWritten	= date('Y-m-d H:i:s');
								$updateResult	= $wpdb->update($advisorServiceTableName,
																array('classes'=>$numClasses,
																	  'date_written'=>$dateWritten),
																array('record_id'=>$record_id),
																array('%d','%s'),
																array('%d'));
								if ($updateResult === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									if ($doDebug) {
										echo "classes updated<br />";
									}
									$content	.= "Advisor $advisorCallsign classes updated from $classes to $numClasses<br />";
									$numUpdated++;
								}
							}
						}
					} else {		// no record, add the record
						$addResult		= $wpdb->insert($advisorServiceTableName,
														array('advisor'=>$advisorCallsign,
															  'semester'=>$inp_semester,
															  'classes'=>$numClasses,
															  'date_written'=>$dateWritten),
														array('%s','%s','%d','%s'));
						if ($addResult === FALSE) {
							handlWPDBError($jobname,$doDebug);
						} else {
							if ($doDebug) {
								echo "advisorCallsign added with $numClasses classes<br />";
							}
							$content			.= "Advisor $advisorCallsign added with classes of $numClasses<br />";
							$numAdded++;
						}
					}
				}
			
			}
			$content		.= "<br />$numUpdated Advisor records updated<br />
								$numAdded Advisor Records Added<br />";
			
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
add_shortcode ('update_advisor_service', 'update_advisor_service_func');
