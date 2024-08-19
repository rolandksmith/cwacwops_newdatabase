function practiceAssessment_func() {

/*	Linked from student regisration, option 4

	Allows the student to take a practice assessment
	
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}

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
//	if ($validUser == "N") {
//		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
//	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-practice-assessment/";
	$jobname					= "Practice Assessment V$versionNumber";

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
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = strtoupper($str_value);
				$inp_mode	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_level") {
				$inp_level	 = $str_value;
				$inp_mode	 = filter_var($inp_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token	 = $str_value;
				$inp_mode	 = filter_var($token,FILTER_UNSAFE_RAW);
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
		$ipAddressTableName			= "wpw1_cwa_ip_address_check2";
	} else {
		$extMode					= 'pd';
		$ipAddressTableName			= "wpw1_cwa_ip_address_check2";
	}



	if ("1" == $strPass) {
			$content 		.= "<h3>$jobname</h3>
								<p>You have requested to take a practice Morse Code Proficiency Assessment. Please 
								enter your call sign in the space below. If you don't have a call sign, enter your 
								last name.</p>
								<p>The purpose of the assessment is to help prospective students get into the 
								proper class based on their Morse code proficiency. Each of the four classes 
								offered by CW Academy has different proficiency requirements:
								<ul><li>Little or no knowledge of Morse code: The Beginner Level class is appropriate
									<li>Capable of copying Morse code at about 6 words per minute: The Fundamental Level 
										class is appropriate
									<li>Capable of copying Morse code at about 10 words per minute: The Intermediate Level 
										class is appropriate
									<li>Capable of copying Morse code at about 20 words per minute: The Advanced Level class 
										is appropriate
								</ul>
								<p>This is not a 'head copy' test. You may want to consider writing down what you hear before 
								selecting your answer.</p>
								<p>After submitting this form, select the level that you feel best fits your capability and start the assessment. 
								The assessment will give you five questions in Morse code. You can 
								then select one of five multiple choice answers that best represents what you heard. 
								After the five questions have been answered the program will allow you to do the assessment 
								once more if you wish. Depending on your score, it may offer you the option to try the next 
								higher level assessment, or the next lower level assessment.</p>
								<p>When you have completed the assessment, the program will display your results and recommendation.</p>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='2'>
								<table style='border-collapse:collapse;'>
								<tr><td>Call Sign</td>
									<td><input type='text' class='formInputText' name='inp_callsign' size='15' maxlength='20'></td></tr>
								$testModeOption
								<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
								</form></p>
								<br /><p>NOTE: you can take the practice assessment twice in a 45-day period.</p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2.<br />
				  inp_callsign: $inp_callsign<br />";
		}
		
		$doProceed		= TRUE;
		if (!in_array($userName,$validTestmode)) {			
			// check the ip address to see how many times the practice assessment has been done
			$fortyFiveDays	= strtotime("-45 days");
			$doProceed		= TRUE;		
			$userIP			= get_the_user_ip();
			$startDate		= date('Y-m-d H:i:s',$fortyFiveDays);
			$sql			= "select count(*) from $ipAddressTableName 
								where program = 'Practice Assessment' 
								and date_written < 'start_date' 
								and ip_address = '$userIP' 
								order by date_written";
			$ipCount		= $wpdb->get_var($sql);
			if ($ipCount !== NULL) {
				if ($ipCount > 1) {
					$content	.= "<h3>$jobname</h3>
									<p>You have exceeded the maximum number of practice Morse Code Proficiency 
									Assessments in a 45-day period.</p>";
					$doProceed	= FALSE;
					if($doDebug) {
						echo "found $ipCount practice assessments for ip address $userIP<br />";
					}
				}
			}
		}
		if ($doProceed) {
			if (!in_array($userName,$validTestmode)) {			
		
				$dateWritten		= date('Y-m-d H:i:s');
				$ip_address			= get_the_user_ip();
				$ipResult			= $wpdb->insert($ipAddressTableName,
														array('ip_address'=>$ip_address,
															   'callsign'=>$inp_callsign,
															   'program'=>'Practice Assessment',
															   'token'=>'',
																'date_written'=>$dateWritten),
														 array('%s','%s','%s','%s','%s'));
				if ($ipResult === FALSE) {
					if ($doDebug) {
						$lastError		= $wpdb->last_error;
						$lastQuery		= $wpdb->last_query;
						echo "inserting $ip_address into $ipAddressTableName failed.<br />
								Error: $lastError<br />
								SQL: $lastQuery<br />";
					}
				}
			}		
			$token					= mt_rand();
			$returnURL				= "$siteURL/cwa-practice-assessment/?strpass=3&inp_callsign=$inp_callsign&token=$token";
			$returnURL				= urlencode($returnURL);		
			$content				= "<h3>$jobname</h3>
										<p>Please click on the link corresponding to your 
										desired level. That will take you to the Morse Code 
										Proficiency Assessment.</p>
										<p><table style='border:4px solid green;width:auto;'><tr><td>
										For a Beginner Level assessment please click 
										<a href='https://cw-assessment.vercel.app/?mode=Beginner&callsign=$inp_callsign&token=$token&infor=Practice%20Assessment&returnurl=$returnURL'>Beginner Level Assessment</a>
										</td></tr></table></p>
										<p><table style='border:4px solid green;width:auto;'><tr><td>
										For a Fundamental Level assessment please click
										<a href='https://cw-assessment.vercel.app/?mode=Fundamental&callsign=$inp_callsign&token=$token&infor=Practice%20Assessment&returnurl=$returnURL'>Fundamental Level Assessment</a>
										</td></tr></table></p>
										<p><table style='border:4px solid green;width:auto;'><tr><td>
										For an Intermediate Level assessment please click
										<a href='https://cw-assessment.vercel.app/?mode=Intermediate&callsign=$inp_callsign&token=$token&infor=Practice%20Assessment&returnurl=$returnURL'>Intermediate Level Assessment</a>
										</td></tr></table></p>
										<p><table style='border:4px solid green;width:auto;'><tr><td>
										For an Advanced Level assessment please click
										<a href='https://cw-assessment.vercel.app/?mode=Advanced&callsign=$inp_callsign&token=$token&infor=Practice%20Assessment&returnurl=$returnURL'>Advanced Level Assessment</a>
										</td></tr></table></p>
										<br /><br /><p>NOTE: the selected level assessment will be the starting point for 
										the assessment. Depending on the results, you may be offered a higher or 
										lower level assessment.</p>";

									
	
		}	
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 3<br />
				  inp_callsign: $inp_callsign<br />
				  token: $token<br />";
		}
		$doProceed	= TRUE;
		
		$content	.= "<h3>$jobname</h3>
						<h4>Database Info</h4>";
		$bestResultBeginner		= 0;
		$didBeginner			= FALSE;
		$bestResultFundamental	= 0;
		$didFundamental			= FALSE;
		$bestResultIntermediate	= 0;
		$didIntermediate		= FALSE;
		$bestResultAdvanced		= 0;
		$didAdvanced			= FALSE;
		$retVal			= displayAssessment('',$token,$doDebug);
		if ($retVal[0] === FALSE) {
			if ($doDebug) {
				echo "displayAssessment returned FALSE. Called with $inp_callsign, $inp_token<br />";
			}
			$content	.= "No data to display.<br />Reason: $retVal[1]";
		} else {
			$content	.= $retVal[1];
			$myArray	= explode("&",$retVal[2]);
			foreach($myArray as $thisValue) {
				$myArray1	= explode("=",$thisValue);
				$thisKey	= $myArray1[0];
				$thisData	= $myArray1[1];
				$$thisKey	= $thisData;
				if ($doDebug) {
					echo "$thisKey = $thisValue<br />";
				}
			}
			$content		.= "<p>You have completed the Morse Code Proficiency 
								assessment.<br />";
			if ($didBeginner) {
				$content	.= "Your highest Beginner Level assessment score was $bestResultBeginner<br />";
			}
			if ($didFundamental) {
				$content	.= "Your highest Fundamental Level assessment score was $bestResultFundamental<br />";
			}
			if ($didIntermediate) {
				$content	.= "Your highest Intermediate Level assessment score was $bestResultIntermediate<br />";
			}
			if ($didAdvanced) {
				$content	.= "Your highest Advanced Level assessment score was $bestResultAdvanced<br />";
			}


			$doProceed				= TRUE;
			if ($didAdvanced) {
				if ($bestResultAdvanced < 70) {
					$content 		.= "<p>You took an Advanced Level assessment. Recommend: taking 
										an Intermediate Level class.";
				} else {
					$content 		.= "<p>You took an Advanced Level assessment. Recommend: taking 
										an Advanced Level class. However, you could also 
										take an Intermediate Level class.";
				}
				$doProceed			= FALSE;
			}
			if ($doProceed) {
				if ($didIntermediate) {
					if ($bestResultIntermediate <= 40) {
						$content	.= "<p>You took an Intermediate Level assessment. Based on your 
										assessment score, CW Academy recommends that you 
										take the Fundamental Level class.";
					} elseif ($bestResultIntermediate > 40 && $bestResultIntermediate <= 60) {
						$content	.= "<p>You took an Intermediate Level assessment. Based on your 
										assessment score, CW Academy recommends that you 
										take the Fundamental Level class.";
					} elseif ($bestResultIntermediate > 60) {
						$content	.= "<p>You took an Intermediate Level assessment. Based on your 
										assessment score, CW Academy recommends that you 
										take the Intermediate Level class.";
					}
					$doProceed		= FALSE;
				}
			}
			if ($doProceed) {
				if ($didFundamental) {
					if ($bestResultFundamental <= 40) {
						$content	.= "<p>You took the Fundamental Level assessment. Based on your 
										assessment score, CW Academy recommends that you take a 
										Beginner Level class.</p>";
					} elseif ($bestResultFundamental > 40) {
						$content	.= "<p>You took a Fundamental Level assessment. Based on your 
										assessment score, CW Academy recommends that you take a
										Fundamental Level class.<p>";
					}
					$doProceed		= FALSE;
				}
			}

			if ($doProceed) {
				if ($didBeginner) {
					if ($bestResultBeginner <= 40) {
						if ($doDebug) {
							echo "beginner result <= 40<br />";
						}
						$content			.= "<p>You took a Beginner Level assessment. Based on your
												assessment score, CW Academy recommends that you 
												take a Beginner Level class</p>";
					} elseif ($bestResultBeginner > 40 && $bestResultBeginner <= 60) {
						if ($doDebug) {
							echo "beginner result between 40 and 60<br />";
						}
						$content		.= "<p>You took a Beginner Level assessment. Based on your 
											assessment score, CW Academy recommends that you take 
											a Beginner Level Class. However,   
											a Fundamental Level class may also be a choice.</p>";
				
					} elseif ($bestResultBeginner >= 60) {
						$content		.= "<p>You took a Beginner Level assessment. Based on your 
											assessment score, CW Academy recommends that you take 
											a Fundamental Level Class.</p>";
					}
				}
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
add_shortcode ('practiceAssessment', 'practiceAssessment_func');
