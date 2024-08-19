function display_catalog_for_a_timezone_func() {

/*
	Modified 16Apr23 by Roland to fix action_log
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
							<p>Select the desired semester and time zone and submit.</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td style='width:200px;'><b>Semester</b></td>
								<td>$radioList</td></tr>
							<tr><td colspan='2'><b>Enter either a Timezone Offset or a Timezone Identifier (such as America/Denver).</b> 
												The Timezone Identifier will override the Timezone Offset</td></tr>
							<tr><td>Timezone Offset</td>
								<td><input type='text' class='formInputText' name='inp_timezone_offset' size='8' maxlength='8'></td>
							<tr><td>Timezone ID</td>
								<td><input type='text' class='formInputText' name='inp_timezone_id' size='50' maxlength='50'></td>
							</tr>
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
			} else {
				$tzString			= "TZ $inp_timezone_id";
			}
		
		} else {
			$tzString				= "TZ $inp_timezone_offset";
		}

		if ($doProceed) {		
			foreach($levelArray as $myKey=>$myLevel) {
				if ($doDebug) {
					echo "<br />Getting catalog for $myLevel<br />";
				}
				$returnArray	= generateClassTimes($inp_timezone_offset,$myLevel,$inp_semester,$doDebug,$catalogMode);
				//	[level][sequence] = localtime|localdays|nmbr classes|utctime|utcdays|advisors

				$content		.= "<h3>CW Academy Course Catalog for $myLevel in $tzString for Semster $inp_semester</h3>
									<table style='width:1000px;'>
									<tr><th>$tzString</th>
										<th>UTC</th>
										<th>&nbsp;</th>
										<th>&nbsp;</tr>
									<tr><th>Local Time</th>
										<th>UTC Time</th>
										<th style='text-align:center;'>Classes</th>
										<th>Advisors</th></tr>";
				$totalClasses				= 0;
				foreach($returnArray as $thisLevel=>$myValue) {
					foreach($myValue as $thisSequence=>$thisInfo) {
						$myArray				= explode("|",$thisInfo);
						$thisLocalStart			= $myArray[0];
						$thisLocalDays			= $myArray[1];
						$thisClassCount			= $myArray[2];
						$thisClassStartUTC		= $myArray[3];
						$thisClassDaysUTC		= $myArray[4];
						$thisClassAdvisors		= $myArray[5];
						$thisClassAdvisors		= str_replace(",",", ",$thisClassAdvisors);
						$content					.= "<tr><td style='vertical-align:top;'>$thisLocalStart $thisLocalDays</td>
															<td style='vertical-align:top;'>$thisClassStartUTC $thisClassDaysUTC</td>
															<td style='vertical-align:top;text-align:center;'>$thisClassCount</td>
															<td style='vertical-align:top;'>$thisClassAdvisors</td></tr>";
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
