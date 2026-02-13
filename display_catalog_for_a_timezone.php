function display_catalog_for_a_timezone_func() {

/*
	Modified 16Apr23 by Roland to fix action_log
	Modified 17Dec24 by Roland to have the option of only showing 
		catalog entries with open seats
*/

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	$userName				= $initializationArray['userName'];
	$validTestmode			= $initializationArray['validTestmode'];
	$siteURL				= $initializationArray['siteurl'];
	$currentSemester		= $initializationArray['currentSemester'];
	$nextSemester			= $initializationArray['nextSemester'];
	$semesterTwo			= $initializationArray['semesterTwo'];
	$semesterThree			= $initializationArray['semesterThree'];
	$semesterFour			= $initializationArray['semesterFour'];
	$strPass				= "1";
	$inp_timezone_id		= '';
	$inp_timezone_offset	= -99.00;
	$inp_semester			= "";
	
	if ($userName == '') {
		return "You are not authorized";
	}


//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

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
			if ($str_key 		== "inp_timezone_offset") {
				$inp_timezone_offset	 = $str_value;
				$inp_timezone_offset	 = filter_var($inp_timezone_offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_timezone_id") {
				$inp_timezone_id	 = $str_value;
				$inp_timezone_id	 = filter_var($inp_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_show_advisors") {
				$inp_show_advisors	 = $str_value;
				$inp_show_advisors	 = filter_var($inp_show_advisors,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester	 = $str_value;
				$inp_semester	 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_display") {
				$inp_display	 = $str_value;
				$inp_display	 = filter_var($inp_display,FILTER_UNSAFE_RAW);
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
	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$finalTotal					= 0;
	$theURL						= "$siteURL/cwa-display-catalog-for-a-timezone/";

	$levelArray					= array(1=>'Beginner',
										2=>'Fundamental',
										3=>'Intermediate',
										4=>'Advanced');
										
	$catalogMode				= "Production";
	if ($testMode) {
		$catalogMode		= "TestMode";
	}
	
	$content = "";	


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


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$nextChecked	= "";
		$radioList		= "";
		if ($currentSemester != 'Not in Session') {
			$radioList	.= "<input type='radio' class='formInputButton' name='inp_semester' value='$currentSemester' checked>$currentSemester<br />";
		} else {
			$nextChecked	= 'checked';
		}
		$radioList		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$nextSemester' $nextChecked>$nextSemester<br />
							<input type='radio' class='formInputButton' name='inp_semester' value='$semesterTwo'>$semesterTwo<br />
							<input type='radio' class='formInputButton' name='inp_semester' value='$semesterThree'>$semesterThree<br />
							<input type='radio' class='formInputButton' name='inp_semester' value='$semesterFour'>$semesterFour<br />";



		$content 		.= "<h3>Display Class Catalog for a Time Zone</h3>
							<p>Select the desired semester, time zone, display option and submit.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td style='width:200px;'><b>Semester</b></td>
								<td>$radioList</td></tr>
							<tr><td colspan='2'><b>Enter either a Timezone Offset or a Timezone Identifier (such as America/Denver)</b> 
												The Timezone Identifier will override the Timezone Offset</td></tr>
							<tr><td>Timezone Offset</td>
								<td><input type='text' class='formInputText' name='inp_timezone_offset' size='8' maxlength='8'></td></tr>
							<tr><td>Timezone ID</td>
								<td><input type='text' class='formInputText' name='inp_timezone_id' size='50' maxlength='50'></td></tr>
							<tr><td>Display Contents</td>
								<td><input type='radio' class='formInputButton' name='inp_display' value='all' checked required>Show all catalog entries<br />
									<input type='radio' class='formInputButton' name='inp_display' value='seats' required>Show catalog entries with open seats<br />
							$testModeOption
							</table>
							<input class='formInputButton' type='submit' value='Submit' />
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass 2 with <br />
				  semester: $inp_semester<br />
				  timezone_offset: $inp_timezone_offset<br />
				  timezone_id: $inp_timezone_id<br />";
		}
		$doProceed					= TRUE;
		if ($inp_timezone_id != '') {
			$inp_timezone_offset	= getOffsetFromIdentifier($inp_timezone_id, $inp_semester,$doDebug);
			if ($inp_timezone_offset == FALSE) {
				$content			.= "The Timezone Identifier of $inp_timezone_id is unknown";
				$doProceed			= FALSE;
				if ($doDebug) {
					echo "timezone ID of $inp_timezone_id is unknown<br />";
				}
			} else {
				$tzString			= "TZ $inp_timezone_offset";
				if ($doDebug) {
					echo "got tzString: $tzString<br />";
				}
			}
		
		} else {
			$tzString				= "TZ $inp_timezone_offset";
		}

		if ($doProceed) {		
			foreach($levelArray as $myKey=>$myLevel) {
				if ($doDebug) {
					echo "<br />Getting catalog for $myLevel<br />";
				}
				$returnArray	= generateClassTimes($inp_timezone_offset,$myLevel,$inp_semester,$inp_display,$doDebug,$catalogMode);
				if ($doDebug) {
					echo "data from generateClassTimes<br /><pre>";
					print_r($returnArray);
					echo "</pre><br/>";
				}

 				//	[level][sequence] = language|localtime|localdays|nmbr classes|advisors

				$content		.= "<h3>CW Academy Course Catalog for $myLevel in $tzString for Semster $inp_semester (offset: $inp_timezone_offset)</h3>
									<table style='width:1000px;'>
									<tr><th style='width:200px;'>$tzString<br />Local Time</th>
										<th style='text-align:center;width:80px;'><br />Classes</th>
										<th style='width:200px;'>Class<br />Language</th>
										<th style='vertical-align:top;width:300px;'><br />Advisors</th>
										<th style='vertical-align:top;'>UTC Schedule</th></tr>";
				$totalClasses				= 0;
				foreach($returnArray as $thisLevel=>$myValue) {
					foreach($myValue as $thisSequence=>$thisInfo) {
						$myArray				= explode("|",$thisInfo);
						$thisLanguage			= $myArray[0];
						$thisLocalStart			= $myArray[3];
						$thisLocalDays			= $myArray[4];
						$thisUTCStart			= $myArray[1];
						$thisUTCDays			= $myArray[2];
						$thisClassCount			= $myArray[5];
						$thisClassAdvisors		= $myArray[6];
						$thisClassAdvisors		= str_replace(",",", ",$thisClassAdvisors);
						$content					.= "<tr><td style='vertical-align:top;'>$thisLocalStart $thisLocalDays</td>
															<td style='vertical-align:top;text-align:center;'>$thisClassCount</td>
															<td style='vertical-align:top;'>$thisLanguage</td>
															<td style='vertical-align:top;'>$thisClassAdvisors</td>
															<td style='vertical-align:top;'>$thisUTCStart $thisUTCDays</td></tr>";
						$totalClasses			= $totalClasses + $thisClassCount;
						$finalTotal				= $finalTotal + $thisClassCount;
					}
				}
				$content						.= "<tr><td colspan='6'>Number of $myLevel classes: $totalClasses</td></tr></table>";
			}
			$content	.= "<p>$finalTotal: Number of classes in the catalog</p>";
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("Display Catalog for a Timezone|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('display_catalog_for_a_timezone', 'display_catalog_for_a_timezone_func');

