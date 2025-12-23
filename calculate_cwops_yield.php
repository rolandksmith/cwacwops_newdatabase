function calculate_cwops_yield_func() {

	global $wpdb, $doDebug, $content, $debugLog;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	$versionNumber				 	= "1";
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
    $pastSemestersArray = $initializationArray['pastSemestersArray'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
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
	$theURL						= "$siteURL/cwa-calculate-cwops-yield/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Calculate CWOps Yield";
    $totalYield                 = 0;
    $totalIntermediateCount     = 0;
	$totalAdvancedCount			= 0;
	$advancedCount				= 0;
	$intermediateCount			= 0;
    $debugLog                   = "";
    $totalStudents              = 0;
	$checkedStudents			= array();
	$studentCount				= 0;
	$onlyOne					= 0;

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
			if ($str_key 		== "inp_rsave") {
				$inp_rsave		 = $str_value;
				$inp_rsave		 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
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
			if ($str_key 		== "inp_level") {
				$inp_level	 = $str_value;
				$inp_level	 = filter_var($inp_level,FILTER_UNSAFE_RAW);
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
        $operatingMode              = 'Testmode';
    } else {
		$extMode					= 'pd';
        $operatingMode              = 'Production';
    }

    $student_dal = new CWA_Student_DAL();

    function debugReport($debugInfo) {
        global $doDebug, $debugLog, $content;

        $thisDateTime = date('Y-m-d H:i:s');
        $myStr = "$debugInfo ($thisDateTime)<br />";
        $debugLog .= $myStr;
        if ($doDebug) {
            echo $myStr;
        }

    }

	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Which Starting Level</td>
								<td><input type='radio' class='fromInputButton' name='inp_level' value='Beginner' required checked>Beginner<br />
									<input type='radio' class='fromInputButton' name='inp_level' value='Fundamental' required >Fundamental</td></tr>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
        debugReport("<br />Starting Pass 2<br />
                    Initialization Array:<br /><pre>");
		$myStr = print_r($initializationArray, TRUE);
        debugReport("$myStr</pre><br />
					inp_level: $inp_level");
	
		$content .= "<h3>$jobname Starting Level $inp_level</h3>
					<table>
					<tr><th>Semester</th>
					<th>promotable<br />$inp_level<br />Students</th>
					<th>Stopped at<br />Intermediate</th>
					<th>Intermediate<br />Yield</th>
					<th>Completed<br />Advanced</th>
					<th>Advanced<br />Yield</th>
					<th>Total<br />Count</th>
					<th>Overall<br />Yield</th></tr>";
					
		$myInt					= count($pastSemestersArray) - 1;
		for ($ii=$myInt;$ii>3;$ii--) {
			$theSemester = $pastSemestersArray[$ii];
			debugReport("<br />processing past semester $theSemester<br />");
			$yield = 0;
			$intermediateCount = 0;
			$studentArray = array();
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_semester', 'value' => $theSemester, 'compare' => '=' ],
					['field' => 'student_level', 'value' => $inp_level, 'compare' => '=' ],
					['field' => 'student_promotable', 'value' => 'P', 'compare' => '=' ]
				]
			];
			$studentData = $student_dal->get_student( $criteria, 'student_call_sign', 'ASC', $operatingMode );
			if ($studentData === FALSE || $studentData === NULL){
				debugReport("<b>ERROR</b> attempting to get Beginner students for $theSemester returned FALSE|NULL");
			} else {
				if (! empty($studentData)) {
					foreach($studentData as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						if ($inp_level == 'Beginner') {
							$studentArray[] = $student_call_sign;
						} else {
							// looking for Fundamental students who started at fundamenta.
							// see if the student has a Beginner class, if so,
							// add to the checkedStudents array and skip
							$addThisStudent = FALSE;
							$criteria = [
								'relation' => 'AND',
								'clauses' => [
									['field' => 'student_call_sign', 'value' => $student_call_sign, 'compare' => '=' ],
									['field' => 'student_level', 'value' => 'Beginner', 'compare' => '=' ]
								]
							];
							$checkData = $student_dal->get_student( $criteria, 'student_call_sign', 'ASC', $operatingMode );
							if ($checkData === 'False' || $checkData === NULL) {
								debugReport("getting the checkData for $student_call_sign student returned FALSE|NULL");
							} else {
								if (! empty($checkData)) {
									$myInt = count($checkData);
									if($myInt > 0) {		// have at least one beginner class for this student
										debugReport("Skipping $student_call_sign since he didn't start at Fundamental");
										$checkedStudents[] = $student_call_sign;
									} else {
										$addThisStudent = TRUE;
									}
								} else {
									$addThisStudent = TRUE;
								}
							}
							if ($addThisStudent) {
								$studentArray[] = $student_call_sign;
							}
						}
					}
					foreach($studentArray as $thisCallsign) {
						// if the student is in the checked array, skip
						if (! in_array($thisCallsign, $checkedStudents)) {
							$studentCount++;
							$checkedStudents[] = $thisCallsign;
							// get all student records in date ascending order
							$criteria = [
								'relation' => 'AND',
								'clauses' => [
									['field' => 'student_call_sign', 'value' => $thisCallsign, 'compare' => '=' ]
								]
							];
							$studentRecords = $student_dal->get_student( $criteria, 'student_date_created', 'ASC', $operatingMode );
							if ($studentRecords === FALSE) {
								debugReport("Error retrieving student record for $thisCallsign<br />");
							} else {
								$myInt = count($studentRecords);
								if ($myInt > 1) {
									debugReport("<br />$myInt Records for $thisCallsign<br />");
									foreach($studentRecords as $key => $value) {
										foreach($value as $thisField => $thisValue) {
											$$thisField = $thisValue;
										}
										debugReport("$student_semester $student_level $student_promotable<br />");
										if ($student_level == 'Advanced' && $student_promotable == 'P') {
											debugReport("  --> <b>Advanced Yield Record</b><br />");
											$advancedCount++;
										}
									}
									if ($student_level == 'Intermediate' && $student_promotable == 'P') {
										debugReport("Stopped at Intermediate<br />");
										$intermediateCount++;

									}
								} else {
									debugReport("<br />Only one record for $thisCallsign<br />");
									$onlyOne++;
								}
							}
						}
					}
					$totalStudents = $totalStudents + $studentCount;
					$totalAdvancedCount = $totalAdvancedCount + $advancedCount;
					$totalIntermediateCount = $totalIntermediateCount + $intermediateCount;
					$myStr1 = number_format( ($advancedCount / $studentCount) * 100, 2 );
					$myStr2 = number_format( ($intermediateCount / $studentCount) * 100, 2 );
					$newNumber = $intermediateCount + $advancedCount;
					$myStr3 = number_format( ($newNumber / $studentCount) * 100, 2 );
					$content .= "<tr><td>$theSemester</td>
									<td>$studentCount</td>
									<td>$intermediateCount</td>
									<td>$myStr2%</td>
									<td>$advancedCount</td>
									<td>$myStr1%</td>
									<td>$newNumber</td>
									<td>$myStr3%</td></tr>";
					$studentCount = 0;
					$intermediateCount = 0;
					$advancedCount = 0;
				} else {
					debugReport("No Beginner student data returned for $theSemester");
				}
			}
		}
		$newNumber = $totalIntermediateCount + $totalAdvancedCount;
		$myStr1 = number_format( ($totalIntermediateCount / $totalStudents) * 100, 2 );
		$myStr2 = number_format( ($totalAdvancedCount / $totalStudents) * 100, 2 );
		$myStr3 = number_format( ($newNumber / $totalStudents) * 100, 2 );
		$content .= "<tr><td>Total</td>
							<td>$totalStudents</td>
							<td>$totalIntermediateCount</td>
							<td>$myStr1%</td>
							<td>$totalAdvancedCount</td>
							<td>$myStr2%</td>
							<td>$newNumber</td>
							<td>$myStr3%</td></tr>
						</table>
						<p>$onlyOne: Student who sstarted and stopped at $inp_level</p>";


	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	///// uncomment if the code to save a report is needed
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		debugReport("Calling function to save the report as Current Student and Advisor Assignments<br />");
		$storeResult	= storeReportData_v2($jobname,$content);
		if ($storeResult[0] !== FALSE) {
			$reportName	= $storeResult[1];
			$reportID	= $storeResult[2];
			$content	.= "<br />Report stored in reports as $reportName<br />
							Go to'Display Saved Reports' or url<br/>
							$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID<br /><br />";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
		debugReport("Calling function to save the DebygReport as $jobname Debug`<br />");
		$storeResult	= storeReportData_v2("$jobname Debug",$debugLog);
		if ($storeResult[0] !== FALSE) {
			$reportName	= $storeResult[1];
			$reportID	= $storeResult[2];
			$content	.= "<br />Report stored in reports as $reportName<br />
							Go to'Display Saved Reports' or url<br/>
							$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID<br /><br />";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
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
add_shortcode ('calculate_cwops_yield', 'calculate_cwops_yield_func');
