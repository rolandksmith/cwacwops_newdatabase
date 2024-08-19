function calculateUTC_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$pastSemesters		= $initializationArray['pastSemesters'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

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
	$theURL						= "$siteURL/utility-calculate-utc/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "UTLITY: Calculate UTC";
	$inp_zip					= '';
	$inp_country				= '';
	$inp_country_code			= '';
	$inp_timezone_id			= '';
	$inp_local					= '';
	$inp_bypass					= '';
	$inp_direction				= '';

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
			if ($str_key		== 'inp_semester') {
				$inp_semester		= $str_value;
				$inp_semester		= filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_direction') {
				$inp_direction		= $str_value;
				$inp_direction		= filter_var($inp_direction,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_bypass') {
				$inp_bypass		= $str_value;
				$inp_bypass		= filter_var($inp_bypass,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_zip') {
				$inp_zip		= $str_value;
				$inp_zip		= filter_var($inp_zip,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_country') {
				$inp_country		= $str_value;
				$inp_country		= filter_var($inp_country,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_country_code') {
				$inp_country_code		= $str_value;
				$inp_country_code		= filter_var($inp_country_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_timezone_id') {
				$inp_timezone_id		= $str_value;
				$inp_timezone_id		= filter_var($inp_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key		== 'inp_local') {
				$inp_local		= $str_value;
				$inp_local		= filter_var($inp_local,FILTER_UNSAFE_RAW);
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


	if ("1" == $strPass) {
		$nextChecked	= '';
		$radioList		= "<table>
							<tr><td>Current and Future</td>
								<td>Past</td>
							<tr><td style='vertical-align:top;'>";
		if ($currentSemester != 'Not in Session') {
			$radioList	.= "<input type='radio' class='formInputButton' name='inp_semester' value='$currentSemester' checked>$currentSemester<br />";
		} else {
			$nextChecked	= 'checked';
		}
		$radioList		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$nextSemester' $nextChecked>$nextSemester<br />";
		$radioList		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$semesterTwo'>$semesterTwo<br />";
		$radioList		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$semesterThree'>$semesterThree<br />";
		$radioList		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$semesterFour'>$semesterFour<br />";
		$radioList		.= "</td><td style='vertical-align:top;'>";
		$myArray			= explode("|",$pastSemesters);
		$myInt				= count($myArray) -1;
		for ($ii=count($myArray)-1;$ii>-1;$ii--) {
			$thisSemester	= $myArray[$ii];
			$radioList		.= "<input type='radio' class='formInputButton' name='inp_semester' value='$thisSemester'>$thisSemester<br />";
		}
		$radioList		.= "</td></tr></table>";
		


		$content 		.= "<h3>$jobname</h3>
							<p>This utility will figure out the UTC dates and times. Input 
							is a semester and conversion direction. Then select one of a zip code, country, country code, or timezone identifier
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td colspan='2'>$radioList</td></tr>
							<tr><td>Direction</td>
								<td><input type='radio' class='formInputButton' name='inp_direction' value='toutc' checked> To UTC<br />
									<input type='radio' class='formInputButton' name='inp_direction' value='tolocal'> To Local</td></tr>
							<tr><td colspan='2'>Zip Code<input type='text' class='formInputText' name='inp_zip' size='15' maxlength='15'></td></tr>
							<tr><td colspan='2'>Country<input type='text' class='formInputText' name='inp_country' size='50' maxlength='50'></td></tr>
							<tr><td colspan='2'>Country Code<input type='text' class='formInputText' name='inp_country_code' size='5' maxlength='5'></td></tr>
							<tr><td colspan='2'>Timezone ID<input type='text' class='formInputText' name='inp_timezone_id' size='30' maxlength='30'></td></tr>
							<tr><td colspan='2'>Source time / days (for example: 0200 Monday,Thursday)<input type='text' class='formInputText' name='inp_local' size='50' maxlength='50' required></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2 with <br />
					inp_semester: $inp_semester<br/>
					inp_direction: $inp_direction<br />
					inp_zip: $inp_zip<br/>
					inp_country: $inp_country<br/> 
					inp_country_code: $inp_country_code<br/>
					inp_timezone_id: $inp_timezone_id<br />";
		}
		$content					.= "<h3>Calculate Move $inp_direction</h3>";

		if ($inp_bypass != 'Y') {


			$doProceed					= TRUE;
			if ($inp_zip != '') {
				$content				.= "<p>Using inp_zip of $inp_zip<br />";
				$resultArray			= getOffsetFromZipcode($inp_zip,$inp_semester,TRUE,$testMode,$doDebug);
				$status					= $resultArray[0];
				if ($status == 'OK') {
					$inp_timezone_id		= $resultArray[1];
					$inp_timezone_offset	= $resultArray[2];
					$matchMsg				= $resultArray[3];
					$content				.= "<p>Timezone Identifier: $inp_timezone_id<br />
												UTC Offset: $inp_timezone_offset<br />
												matchMsg: $matchMsg<br />";
				} else {
					$errorMsg			= $resultArray[3];
					$content			.= "<p>Lookup for $inp_zip failed. Error: $errorMsg</p>";
					$doProceed			= FALSE;
				}
			} elseif ($inp_country != '') {
				$content				.= "<p>Using inp_country of $inp_country</p>";
				$countryResult			= getCountryData($inp_country,'countryname',$doDebug);
				$inp_country_code		= $countryResult[0];
				if ($inp_country_code == FALSE) {
					$content			.= "<p>getCountryData returned FALSE</p>";
					$doProceed			= FALSE;
					$inp_country_code	= '';
				} else {
					$content			.= "Looking up $inp_country returned a country_code of $inp_country_code<br />";
				}
			} elseif ($inp_timezone_id != '') {
				$content					.= "<p>Using inp_timezone_id of $inp_timezone_id</p><p>";
				$inp_timezone_offset		= getOffsetFromIdentifier($inp_timezone_id, $inp_semester, $doDebug);
				if ($inp_timezone_offset == FALSE) {
					$content				.= "getOffsetFromIdentifier using $inp_timezone_id and semester $inp_semester failed<br />";
					$doProceed				= FALSE;
				} else {
					$doProceed					= TRUE;
					$content 				.= "getOffsetFromIdentifier returned an offset of $inp_timezone_offset<br />";
				}
			
			}
			if ($inp_country_code != '') {
				$content				.= "<p>Using country code of $inp_country_code</p><p>";
				$timezone_identifiers 		= DateTimeZone::listIdentifiers( DateTimeZone::PER_COUNTRY, $inp_country_code );
				$myInt						= count($timezone_identifiers);
				$content					.= "Found $myInt timezone identifiers for $inp_country_code<br />";
				if ($myInt == 1) {									//  only 1 found. Use that and continue
					$inp_timezone_id		= $timezone_identifiers[0];
					$inp_timezone_offset	= getOffsetFromIdentifier($inp_timezone_id, $inp_semester, $doDebug);
					if ($inp_timezone_offset === FALSE) {
						$content			.=  "getOffsetFromIdentifier returned FALSE from $inp_timezone_ID and $inp_semester<br />";
						$doProceed			= FALSE;
					} else {
						$content			.= "timezone_id: $inp_timezone_id<br />
												timezone_offset: $inp_timezone_offset<br />";
					}
				} elseif ($myInt > 1) {
					$timezoneSelector		= "<table>";
					$ii						= 1;
//					$content				.= "have the list of identifiers for $inp_country_code<br />";
					foreach ($timezone_identifiers as $thisID) {
//						$content			.= "Processing $thisID<br />";
						$dateTimeZoneLocal 	= new DateTimeZone($thisID);
						$dateTimeLocal 		= new DateTime("now",$dateTimeZoneLocal);
						$localDateTime 		= $dateTimeLocal->format('h:i A');
						$myInt				= strpos($thisID,"/");
						$myCity				= substr($thisID,$myInt+1);
						switch($ii) {
							case 1:
								$timezoneSelector	.= "<tr><td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID'>$myCity<br />$localDateTime</td>";
								$ii++;
								break;
							case 2:
								$timezoneSelector	.= "<td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID'>$myCity<br />$localDateTime</td>";
								$ii++;
								break;
							case 3:
								$timezoneSelector	.= "<td><input type='radio' class='formInputButton' name='inp_timezone_id' value='$thisID'>$myCity<br />$localDateTime</td></tr>";
								$ii					= 1;
								break;
						}
					}
					if ($ii == 2) {			// need two blank cells
						$timezoneSelector			.= "<td>&nbsp;</td><td>&nbsp;</td></tr>";
					} elseif ($ii == 3) {	// need one blank cell
						$timezoneSelector			.= "<td>&nbsp;</td></tr>";
					}
					$doProceed		= FALSE;
					$content		.= "<h3>Select Time Zone City</h3>
										<p>Please select the city that best represents the timezone of interest.</p>
										<form method='post' action='$theURL' 
										name='tzselection' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='2'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<input type='hidden' name='inp_semester' value='$inp_semester'>
											<input type='hidden' name='inp_country_code' value='$inp_country_code'>
											<input type='hidden' name='inp_country' value='$inp_country'>
											<input type='hidden' name='inp_local' value='$inp_local'>
											<input type='hidden' name='inp_bypass' value='Y'>
										$timezoneSelector
										<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
										</table>
										</form>";
				} else {
					$content				.= "found no timezone_identifiers for country code of $inp_country_code<br />";
					$doProceed				= FALSE;
				}
			}
		} else {
			$content					.= "<br />Returning from inp_bypass. Received inp_timzone_id of $inp_timezone_id<br />";
			// now get the offset
			$inp_timezone_offset		= getOffsetFromIdentifier($inp_timezone_id, $inp_semester, $doDebug);
			if ($inp_timezone_offset == FALSE) {
				$content				.= "getOffsetFromIdentifier using $inp_timezone_id and semester $inp_semester failed<br />";
				$doProceed				= FALSE;
			} else {
				$doProceed					= TRUE;
				$content 				.= "getOffsetFromIdentifier returned an offset of $inp_timezone_offset<br />";
			}
			
		}	
		if ($doProceed) {
			$myArray			= explode(" ",$inp_local);
			if (count($myArray) != 2) {
				$content		.= "<b>ERROR:</b> Source time / days of $inp_local has incorrect format</p>";
			} else {
				$thisTimes		= $myArray[0];
				$thisDays		= $myArray[1];
				$utcResult		= utcConvert($inp_direction,$inp_timezone_offset,$thisTimes,$thisDays,$doDebug);
				$result			= $utcResult[0];
				if ($result == 'OK') {
					$newTime	= $utcResult[1];
					$newDays	= $utcResult[2];
					
					$content	.= "Moving $inp_direction with source of $inp_local: $newTime $newDays</p>";
				} else {
					$status		= $utcResult[3];
					$content	.= "utcConvert failed. Error: $status</p>";
				}
			}
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
add_shortcode ('calculateUTC', 'calculateUTC_func');
