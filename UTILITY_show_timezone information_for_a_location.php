function show_timezone_information_for_a_location_func() {

	global $wpdb;

	$doDebug						= TRUE;
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
	$proximateSemester	= $initializationArray['proximateSemester'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
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
	$theURL						= "$siteURL/cwa-show-timezone-information-for-a-location/";
	$jobname					= "Show Timezone Information for a Location";
	$inp_city					= 'Not Given';
	$inp_state					= 'Not Given';
	$inp_country				= 'Not Given';
	$inp_semester				= 'Not Given';
	$inp_zip					= 'Not Given';

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
			if ($str_key 		== "inp_city") {
				$inp_city		 = $str_value;
				$inp_city		 = filter_var($inp_city,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_state") {
				$inp_state		 = $str_value;
				$inp_state		 = filter_var($inp_state,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_country") {
				$inp_country		 = $str_value;
				$inp_country		 = filter_var($inp_country,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester		 = $str_value;
				$inp_semester		 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_zip") {
				$inp_zip		 = $str_value;
				$inp_zip		 = filter_var($inp_zip,FILTER_UNSAFE_RAW);
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
		$TableName					= "wpw1_cwa_";
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Lookup either by zipcode or by city, state, country. If 
							semester is not given, the proximate semester will be used. 
							If zipcode is entered and no country, the program look up 
							the zipcode in the US Zipcode Table. If no zipcode, then the 
							city and state must be entered. If the country is not entered, 
							then United States is assumed.
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Semester</td>
								<td><input type='text' class='formInputText' name='inp_semester' size='20' maxlength='20'></td></tr>
							<tr><td style='vertical-align:top;'>Lookup by Zipcode</td>
								<td>Zipcode: <input type='text' class='formInputText' name='inp_zip' size='5' maxlength='5'><br />
									City: <input type='text' class='formInputText' name='inp_city' size='30' maxlength='50'><br />
									State: <input type='text' class='formInputText' name='inp_state' size='30' maxlength='50'><br />
									Country: <input type='text' class='formInputText' name='inp_country' size='30' maxlength='50'></td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";

	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "<br />At Pass 2 with:
				inp_semester: $inp_semester<br/>
				inp_zip: $inp_zip<br />
				inp_city: $inp_city<br />
				inp_state: $inp_state<br />
				inp_country: $inp_country<br />";
				
				
		}
		
		$USZipLookup		= FALSE;
		$doProceed			= TRUE;
		if ($inp_zip != '' && $inp_country == '') {
			$USZipLookup	= TRUE;
		}
		if ($inp_zip != '' && $inp_country == 'US') {
			$USZipLookup	= TRUE;
		}

		if (!$USZipLookup) {	
			if ($inp_city == '') {
				$content	.= "Not doing a Zipcode lookup so city is required<br />";
				$doContinue	= FALSE;	
			}
			if ($inp_state == '') {
				$content	.= "Not doing a Zipcode lookup so state is required<br />";
				$doContinue	= FALSE;	
			}
		}
		
//		$doProceed			= FALSE;
		if ($doProceed) {
			if ($inp_semester == '') {
				$inp_semester = $proximateSemester;
			}
			if ($inp_country == '') {
				$inp_country = 'United States';
			}
			$content		.= "<h3>$jobname</h3>
								<p>Results for:<br />
								Semester: $inp_semester<br/>
								Zip: $inp_zip<br />
								City: $inp_city<br />
								State: $inp_state<br />
								Country: $inp_country<br />";
			if ($USZipLookup) {
				if ($doDebug) {
					echo "doing a zipcode lookup<br />";
				}
				$myResult						= getOffsetFromZipcode($inp_zip,$inp_semester,TRUE,FALSE,FALSE);
				$thisStatus					 	= $myResult[0];
				$thisTimezoneID					= $myResult[1];
				$thisOffset						= $myResult[2];
				$thisMsg						= $myResult[3];
				if($thisStatus == 'OK') {
					$this_timezone_id			= $thisTimezoneID;
					$this_timezone_offset		= $thisOffset;
					$content					.= "Zip Code $inp_zip has a timezone_id of $this_timezone_id and offset of $this_timezone_offset<br />";
				} else {
					$content					.=  "Getting timezone info from zip code $inp_zip FAILED: $thisMsg<br />";
				}
			} else {
				if ($doDebug) {
					echo "doing a city, state, country lookup<br />";
				}
				$checkStr			= array('city'=>$inp_city,
											'state'=>$inp_state,
											'country'=>$inp_country,
											'doDebug'=>$doDebug);
				$this_timezone_id		= getTimeZone($checkStr);
				if($doDebug) {
					echo "got $this_timezone_id back from getTImeZone<br />";
				}
				if ($this_timezone_id == '??') {
					$content				.= "$inp_city, $inp_state, $inp_country a timezone_id of ??<br />";
				} else {
					$this_timezone_offset	= getOffsetFromIdentifier($this_timezone_id,$inp_semester,$doDebug);
					$content				.= "$inp_city, $inp_state, $inp_country has a timezone_id of $this_timezone_id and offset of $this_timezone_offset<br />";
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
add_shortcode ('show_timezone_information_for_a_location', 'show_timezone_information_for_a_location_func');
