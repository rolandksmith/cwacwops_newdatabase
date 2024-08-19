function show_offsets_func() {

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
	$theURL						= "$siteURL/utility-show-offsets-for-a-country-or-zip-code";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Show Offsets for a Country Code";
	$inp_type					= 'Not Given';
	$inp_code					= 'Not Given';
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
			if ($str_key 		== "inp_code") {
				$inp_code		 = $str_value;
				$inp_code		 = filter_var($inp_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_type") {
				$inp_type		 = $str_value;
				$inp_type		 = filter_var($inp_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester		 = $str_value;
				$inp_semester		 = filter_var($inp_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_name") {
				$inp_name		 = $str_value;
				$inp_name		 = filter_var($inp_name,FILTER_UNSAFE_RAW);
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


/*
	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td>Semester</td>
	<td><input type='text' class='formInputText' name='inp_semester' size='20' maxlength='20' required></td></tr>
<tr><td>Lookup Type</td>
	<td><input type='radio' class='formInputButton' name='inp_type' value='zc' required> Zip Code<br />
		<input type='radio' class='formInputButton' name='inp_type' value='cc' checked required> Country Code<br />
		<input type='radio' class='formInputButton' name='inp_type' value='country' required> Country Name</td></tr>
<tr><td>Country Code</td>
	<td><input type='text' class='formInputText' name='inp_code' size='5' maxlength='5'></td></tr>
<tr><td>Country Name</td>
	<td><input type='text' class='formInputText' name='inp_name' size='50' maxlength='50'></td></tr>
<tr><td>US Zip Code (5 digits max)</td>
	<td><input type='text' class='formInputText' name='inp_zip' size='5' maxlength='5'></td></tr>
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";
*/

	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td>Semester</td>
	<td><input type='text' class='formInputText' name='inp_semester' size='20' maxlength='20' required></td></tr>
<tr><td>Lookup Type</td>
	<td><input type='radio' class='formInputButton' name='inp_type' value='zc' required> Zip Code &nbsp;&nbsp;&nbsp;
		<input type='text' class='formInputText' name='inp_zip' size='5' maxlength='5'><br />
		<input type='radio' class='formInputButton' name='inp_type' value='cc' checked required> Country Code &nbsp;&nbsp;&nbsp;
		<input type='text' class='formInputText' name='inp_code' size='5' maxlength='5'><br />
		<input type='radio' class='formInputButton' name='inp_type' value='country' required> Country Name &nbsp;&nbsp;&nbsp
		<input type='text' class='formInputText' name='inp_name' size='50' maxlength='50'></td></tr>
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		if ($doDebug) {
			echo "At Pass 2 with:
inp_type: $inp_type<br />
inp_code: $inp_code<br />
inp_country: $inp_country<br />
inp_zip: $inp_zip<br />
inp_semester: $inp_semester<br/>";
		}
	
		if ($inp_type == 'country') {
			if ($inp_name == '') {
				$content		.= "The country name is required<br />";
			} else {
				$content		.= "<h3>Timezone Offsets for Country $inp_name</h3>
									<div style='clear:both;'>
									<table style='width:500px;'>
									<tr><th>Country</th>
										<th>Country Code</th>
										<th>Identifier</th>
										<th>Offset</th></tr>";
				$countryResult		= $wpdb->get_results("select * from wpw1_cwa_country_codes
														  where country_name like '%$inp_name%'");
				if ($countryResult === FALSE) {
					if ($doDebug) {
						echo "Reading wpw1_cwa_country_codes table failed<br />
							  wpdb->last_query: " . $wpdb->last_query . "<br />
							  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
					}
					echo "Reading wpw1_cwa_country_codes table failed<br />";
				} else {
					$numCCRows			= $wpdb->num_rows;
					if ($doDebug) {
						$myStr			= $wpdb->last_query;
						echo "ran $myStr<br />and retrieved $numCCRows rows<br />";
					}
					if ($numCCRows > 0) {
						foreach($countryResult as $countryRow) {
							$country_code		= $countryRow->country_code;
							$country_name		= $countryRow->country_name;
	
							$timezone_identifiers = DateTimeZone::listIdentifiers( DateTimeZone::PER_COUNTRY, "$country_code" );
							if (count($timezone_identifiers) > 0) {
								foreach($timezone_identifiers as $thisIdentifier) {
									$timezone_offset		= getOffsetFromIdentifier($thisIdentifier,$inp_semester,$doDebug);
									if ($timezone_offset === FALSE) {
										if ($doDebug) {
											echo "getOffsetFromIdentifier returned FALSE using $thisIdentifier, $inp_semester<br />";
										}
										$timezone_offset	= -99;
									}
									$content .= "<tr><td>$country_name</td>
													 <td>$country_code</td>
													 <td>$thisIdentifier</td>
													 <td>$timezone_offset</td></tr>";
								}
							} else {
								$content	.= "<tr><td>$country_name</td>
													<td>$country_code</td>
													<td colspan='2'>No Identifiers</td></tr>";
							}
						}
					} else {
						$content			.= "<tr><td cospan='4'>No country code found</td></tr>";
					}
				}
				$content .= "</table></div>";
			}
		}
	
		if ($inp_type == 'cc') {	
			if ($inp_code == '') {
				$content		.= "A country code is required<br />";
			} else {
				$timezone_identifiers = DateTimeZone::listIdentifiers( DateTimeZone::PER_COUNTRY, "$inp_code" );
				if (count($timezone_identifiers) > 0) {
					$content .= "<div style='clear:both;'><h3>Timezone Offsets for Country Code $inp_code</h3><table style='width:300px;'><th>Identifier</th><th>Offset</th></tr>";
					foreach($timezone_identifiers as $thisIdentifier) {
						$timezone_offset		= getOffsetFromIdentifier($thisIdentifier,$inp_semester,$doDebug);
						if ($timezone_offset === FALSE) {
							if ($doDebug) {
								echo "getOffsetFromIdentifier returned FALSE using $thisIdentifier, $inp_semester<br />";
							}
							$timezone_offset	= -99;
						}
						$content .= "<tr><td>$thisIdentifier</td><td>$timezone_offset</td></tr>";
					}
					$content .= "</table></div>";
				} else {
					$content .= "No identifiers for code $inp_code<br />";
				}
			}
		}
	
		if ($inp_type == 'zc') {
			if ($inp_zip == '') {
				$content		.= "A zip code is required<br />";
			} else {
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
add_shortcode ('show_offsets', 'show_offsets_func');
