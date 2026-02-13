function rkstest_func() {

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= TRUE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
/*
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
*/
	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
	} else {
		$wpdb->hide_errors();
	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/rkstest/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "rkstest (Fix Student Excluded Advisor)";
	$updated					= 0;

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
			if ($str_key 		== "inp_start") {
				$inp_start	 = $str_value;
				$inp_start	 = filter_var($inp_start,FILTER_UNSAFE_RAW);
				$offset		= intval($inp_start);
			}
			if ($str_key 		== "inp_do") {
				$inp_do	 = $str_value;
				$inp_do	 = filter_var($inp_do,FILTER_UNSAFE_RAW);
				$howMany	= intval($inp_do);
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
		$studentTableName			= "wpw1_cwa_student2";
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_student";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Run an audit
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>inp_value</td>
								<td><input type='text' class='formInputText' name='inp_start' size='30' maxlength='30' autofocus></td>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />pass 2<br />";
		}
		$content	.= "<h3>$jobname</h3>";
		// get the student records
		$sql		= "select student_id, student_excluded_advisor 
						from $studentTableName 
						order by student_id
						limit 10";
		$result		= $wpdb->get_results($sql);
		if ($result === FALSE) {
			handleWPDBError($jobname,$doDebug,'attempting to read student table');
			$content	.= "<p>Unable to read $studentTableName table</p>";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			$content	.= "<p>Processing $numRows from $studentTableName</p>";
			if ($numRows > 0) {
				foreach ($result as $resultRow) {
					$student_id					= $resultRow->student_id;
					$student_excluded_advisor	= $resultRow->student_excluded_advisor;
					
					$content			.= "<br />Processing id $student_id<br />";
					if ($student_excluded_advisor != '') {
						$myArray 					= explode('&',$student_excluded_advisor);
						$newExcluded 				= array_unique($myArray);
						$newStudentExcludedAdvisor 	= implode('&',$newExcluded);
						if ($newStudentExcludedAdvisor != $student_excluded_advisor) {
							$content		.= "updating $student_excluded_advisor to $newStudentExcludedAdvisor<br />";
							$updateResult	= $wpdb->update($studentTableName, 
															array('student_excluded_advisor'=>$newStudentExcludedAdvisor),
															array('student_id'=>$student_id),
															array('%s'),
															array('%d'));
							if ($updateResult === FALSE) {
								handleWPDBError($jobname,$doDebug,'attempting to update record $student_id failed');
							} else {
								$content	.= "Update complete<br />";
								$updated++;
							}
						}
					}
				}
				$content	.= "<p>Updated $updated records</p>";
			} else {
				$content	.= "<p>No records found in $studentTableName table</p>";
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
add_shortcode ('rkstest', 'rkstest_func');

