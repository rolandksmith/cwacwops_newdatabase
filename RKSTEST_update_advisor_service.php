function RKSTEST_update_advisor_service_func() {

/*	Updates wpw1_cwa_advisor_service table with the classes each advisor 
	has taught
	
	Related programs:
		advisor_service_report
		removeServiceDuplicates
		update_advisor_service

*/

	global $wpdb;

	$doDebug						= TRUE;
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
	$currentSemester				= $initializationArray['currentSemester'];
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
	$theURL						= "$siteURL/RKSTEST-update-advisor-service/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "RKSTESt Update Advisor Service V$versionNumber";
	
	$numRead					= 0;
	$numUpdated					= 0;

$convertArray = array(
'Apr/May 2013' => '2013 Apr/May',
'Apr/May 2014' => '2014 Apr/May',
'Apr/May 2015' => '2015 Apr/May',
'Apr/May 2016' => '2016 Apr/May',
'Apr/May 2017' => '2017 Apr/May',
'Apr/May 2018' => '2018 Apr/May',
'Apr/May 2019' => '2019 Apr/May',
'Apr/May 2020' => '2020 Apr/May',
'Apr/May 2021' => '2021 Apr/May',
'Jan/Feb 2013' => '2013 Jan/Feb',
'Jan/Feb 2014' => '2014 Jan/Feb',
'Jan/Feb 2015' => '2015 Jan/Feb',
'Jan/Feb 2016' => '2016 Jan/Feb',
'Jan/Feb 2017' => '2017 Jan/Feb',
'Jan/Feb 2018' => '2018 Jan/Feb',
'Jan/Feb 2019' => '2019 Jan/Feb',
'Jan/Feb 2020' => '2020 Jan/Feb',
'Jan/Feb 2021' => '2021 Jan/Feb',
'Jan/Feb 2022' => '2022 Jan/Feb',
'Jan/Feb 2023' => '2023 Jan/Feb',
'May/Jun 2022' => '2022 May/Jun',
'May/Jun 2023' => '2023 May/Jun',
'May/June 2011' => '2011 May/Jun',
'Sep/Oct 2012' => '2012 Sep/Oct',
'Sep/Oct 2013' => '2013 Sep/Oct',
'Sep/Oct 2014' => '2014 Sep/Oct',
'Sep/Oct 2015' => '2015 Sep/Oct',
'Sep/Oct 2016' => '2016 Sep/Oct',
'Sep/Oct 2017' => '2017 Sep/Oct',
'Sep/Oct 2018' => '2018 Sep/Oct',
'Sep/Oct 2019' => '2019 Sep/Oct',
'Sep/Oct 2020' => '2020 Sep/Oct',
'Sep/Oct 2021' => '2021 Sep/Oct',
'Sep/Oct 2022' => '2022 Sep/Oct');



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
	
	
	$content = "";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$advisorServiceTableName	= "wpw1_cwa_advisor_service2";
	} else {
		$extMode					= 'pd';
		$advisorServiceTableName	= "wpw1_cwa_advisor_service";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass<br />";
		}
		$content				.= "<h3>$jobname</h3>";
		
		$sql			= "select * from $advisorServiceTableName 
								order by advisor";
							
		$advisorService	= $wpdb->get_results($sql);
		if ($advisorService === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numACRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numACRows rows<br />";
			}
			if ($numACRows > 0) {
				foreach($advisorService as $advisorServiceRow) {
					$record_id		= $advisorServiceRow->record_id;
					$advisor		= $advisorServiceRow->advisor;
					$semester		= $advisorServiceRow->semester;
					$classes		= $advisorServiceRow->classes;
					$date_written	= $advisorServiceRow->date_written;
					
					if ($doDebug) {
						echo "read $record_id $advisor $semester<br />";
					}
					$numRead++;
					
					if (array_key_exists($semester,$convertArray)) {
						$newSemester	= $convertArray[$semester];
						
						$updateResult	= $wpdb->update($advisorServiceTableName,
													array('semester'=>$newSemester),
													array('record_id'=>$record_id),
													array('%s'),
													array('%d'));
						if ($updateResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numUpdated++;
							if ($doDebug) {
								echo "Updated semester $semester to $newSemester<br />";
							}
						}
					}
				}
			} else {
				$content			.= "No records found for $inp_semester semester";
			}
		}
		$content		.= "<br />$numUpdated Advisor records updated<br />
							$numRead Advisor Records Read<br />";
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
add_shortcode ('RKSTEST_update_advisor_service', 'RKSTEST_update_advisor_service_func');

