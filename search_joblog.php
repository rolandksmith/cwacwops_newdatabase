function search_joblog_func() {

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
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

		ini_set('display_errors','1');
		error_reporting(E_ALL);	

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$inp_field					= "";
	$inp_search					= "";
	$inp_search2				= "";
	$inp_who					= "";

	$theURL						= "$siteURL/cwa-search-joblog/";

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
			if ($str_key 		== "inp_field") {
				$inp_field	 = $str_value;
				$inp_field	 = filter_var($inp_field,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_search") {
				$inp_search	 = $str_value;
				$inp_search	 = filter_var($inp_search,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_date") {
				$inp_date	 = $str_value;
				$inp_date	 = filter_var($inp_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_date2") {
				$inp_date2	 = $str_value;
				$inp_date2	 = filter_var($inp_date2,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_date3") {
				$inp_date3	 = $str_value;
				$inp_date3	 = filter_var($inp_date3,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_who") {
				$inp_who	 = $str_value;
				$inp_who	 = filter_var($inp_who,FILTER_UNSAFE_RAW);
			}
		}
	}
	
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "
<tr><td colspan='2'>Verbose Debugging?</td>
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
	} else {
		$extMode					= 'pd';
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		
		$content 		.= "<h3>Search the Joblog</h3>
<p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
Select the field and the search criteria:<br /><br '>
<table>
<tr><td style='border-style=none;width=6px;vertical-align:top;'><input type='radio' class='formInputButton' name='inp_field' value='searchJobName'></td>
	<td style='vertical-align:top;'>Job Name</td>
	<td style='border-style=none;'>like: <input type='text' class='formInputText' name='inp_search' size='50' maxlength='150'></td></tr>
<tr><td style='border-style=none;vertical-align:top;'><input type='radio' class='formInputButton' name='inp_field' value='searchDate'></td>
	<td style='vertical-align:top;'>Date Run</td>
	<td style='border-style=none;vertical-align:top;'>Date between <em>(yyyy-mm-dd)</em><input type='text' class='formInputText' name='inp_date' size='30' maxlength='30'> 
		and <em>(yyyy-mm-dd)</em> inclusive<input type='text' class='formInputText' name='inp_date2' size='30' maxlength='30'></td></tr>
<tr><td style='border-style=none;'><input type='radio' class='formInputButton' name='inp_field' value='searchWho'></td>
	<td>Who</td>
	<td style='border-style=none;'><input type='text' class='formInputText' name='inp_who' size='20' maxlength='20'></td></tr>
<tr><td style='border-style=none;'><input type='radio' class='formInputButton' name='inp_field' value='searchTime'></td>
	<td>Time</td>
	<td style='border-style=none;'><input type='text' class='formInputText' name='inp_date3' size='30' maxlength='30'></td></tr>
$testModeOption
<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($inp_field == '') {
			$content	.= "Invalid file or search term entered";
		} else {
			if ($doDebug) {
				echo "At pass 2 with inp_field of $inp_field and search criteria of:<br />
inp_search: $inp_search<br />
inp_date: $inp_date<br />
inp_date2: $inp_date2<br />
inp_who: $inp_who<br />";
			}
		
// job name|date (y-m-d)|time (h:i:s)|who|mode|data type|additional info|ip addr		
			$searchWhere			= '';
			if ($inp_field == 'searchJobName') {
				$inp_search			= strtolower($inp_search);
				$searchWhere		= "where lower(job_name) like '%$inp_search%'";
			} elseif ($inp_field == 'searchDate') {
				$myArray			= explode(" ",$inp_date);
				if (count($myArray) == 2) {
					$part1			= "job_date >= '$myArray[0]' and job_time >= '$myArray[1]'";
				} else {
					$part1			= "job_date >= '$inp_date'";
				}
				$myArray			= explode(" ",$inp_date2);
				if (count($myArray) == 2) {
					$part2			= "job_date <= '$myArray[0]' and job_time <= '$myArray[1]'";
				} else {
					$part2			= "job_date <= '$inp_date2'";
				}
			 	$searchWhere		= "where $part1 and $part2";
			} elseif ($inp_field == 'searchWho') {
				$inp_search			= strtolower($inp_who);
				$searchWhere		= "where lower(job_who) like '%$inp_who%'";
			} elseif ($inp_field == 'searchTime') {
				$myArray			= explode(" ",$inp_date3);
				if (count($myArray) == 2) {
					$part1			= "job_date = '$myArray[0]'";
					$part2			= "job_time like '$myArray[1]%'";
				}
			 	$searchWhere		= "where $part1 and $part2";
			} else {
				$content			.= "No valid search criteria entered";
			}
			if ($searchWhere != '') {
			
				$sql				= "select * from wpw1_cwa_joblog $searchWhere order by job_date ASC, job_time ASC";
				$wpw1_cwa_joblog			= $wpdb->get_results($sql);
				if ($wpw1_cwa_joblog === FALSE) {
					if ($doDebug) {
						echo "Reading the database failed<br />";
						echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
						echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
					}
					$content		.= "No data obtained";
				} else {
					$numRows		= $wpdb->num_rows;
					if ($doDebug) {
						echo "successfully ran<br />";
						echo "wpdb->last_query: " . $wpdb->last_query . "<br />and retreived $numRows rows<br />";
					}
					if ($numRows > 0) {
						$content	.= "<h3> Results of Searching Job Log</h3>
										<p>Search Criteria: $searchWhere</p>
										<table>
										<tr><th>Program Name</th>
											<th>Date</th>
											<th>Who</th>
											<th>Mode</th>
											<th>Data Type</th>
											<th>Addl</th>
											<th>IP</th>
											<th>Date Created</td></tr>";
						foreach($wpw1_cwa_joblog as $joblogRow) {
							$job_name		= $joblogRow->job_name;
							$job_date		= $joblogRow->job_date;
							$job_time		= $joblogRow->job_time;
							$job_who		= $joblogRow->job_who;
							$job_mode		= $joblogRow->job_mode;
							$job_data_type	= $joblogRow->job_data_type;
							$job_addl_info	= $joblogRow->job_addl_info;
							$job_ip_addr	= $joblogRow->job_ip_addr;
							$job_date_created	= $joblogRow->job_date_created;
							$content	.= "
<tr><td>$job_name</td>
	<td>$job_date $job_time</td>
	<td>$job_who</td>
	<td>$job_mode</td>
	<td>$job_data_type</td>
	<td>$job_addl_info</td>
	<td>$job_ip_addr</td>
	<td>$job_date_created</td></tr>";
						}
					} else {
						$content			.= "<h3> Results of Searching Job Log $inp_field for $inp_search</h3>No data found matching the search criteria";
					}
				}
			}
			$content			.= "</table>";
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
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("Search joblog|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('search_joblog', 'search_joblog_func');
