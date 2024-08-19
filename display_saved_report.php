function display_saved_report_func() {

/*	Function shows list of saved reports. Once selected, a report can
 *	be displayed, a link to the report can be sent to someone else, or 
 *	the report can be deleted
 *
 *  Modified 17June21 by Roland to use /home/cwopsorg/CwAT for storing the reports
 	Modified 24Jan2022 by Roland to use tables rather than pods
 	Modified 19Nov23 by Roland for new portal process
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName  			= $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];
	$versionNumber		= '2a';
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$reportNameArray			= array();
	$optionList					= '';
	$inp_id						= 0;
	$increment					= 0;
	$theURL						= "$siteURL/cwa-display-saved-report/";
	$token						= '';
	$inp_callsign				= '';
	$jobname					= "Display Saved Report V$versionNumber";
	

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
			if ($str_key 		== "inp_id") {
				$inp_id		 = $str_value;
//				$inp_id		 = filter_var($inp_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token		 = $str_value;
				$token		 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_callsign") {
				$inp_callsign		 = $str_value;
				$inp_callsign		 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
		}
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
		echo "Operating in Test Mode<br />";
		$content		.= "<b><b>Operating in Test Mode</b></p>";
		$reportsTableName	= 'wpw1_cwa_reports2';
	} else {
		$reportsTableName	= 'wpw1_cwa_reports';
	}

	

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting. Building the option list<br />";
		}
		$content 		.= "<h3>$jobname</h3>
						<table style='width:500px;'>
						<tr><td style='align:center;width:200px;'>
						<div>
						<p>Click on the desired option:</p>
						<form method='post' action='$theURL' 
						name='option1Form' ENCTYPE='multipart/form-data'>
						<input type='hidden' name='strpass' value='5'>
						<input class='formInputButton' type='submit' value='Show Available Reports' />
						</form></p>
						</div>
						</td><td style='align:center; width:200px;'>
						<div clear='all'>
						<form method='post' action='$theURL' 
						name='option2Form' ENCTYPE='multipart/form-data'>
						<input type='hidden' name='strpass' value='3'>
						<input type='hidden' name='inp_id' value='recent'>
						<input class='formInputButton' type='submit' value='Show Most Reent Reports' />
						</form></p>
						</div></td></tr></table>";

	} elseif ("5" == $strPass) {
		$content					.= "<h3>$jobname</h3>
<p>The saved report names are shown below along with the most recent three 
reports. Select one of the reports or 'Show More'</p>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='3'>
<table>
<tr><th colspan='2'>Report Name</th></tr>";
		
		// get all report titles from the reports pod
		$sql			= "select distinct(report_name) as report_name 
							from $reportsTableName 
							order by report_name";
		$wpw1_cwa_reports	= $wpdb->get_results($sql);
		if ($wpw1_cwa_reports === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRRows									= $wpdb->num_rows;
			if ($numRRows > 0) {
				if ($doDebug) {
					echo "found $numRRows rows in $reportsTableName<br />";
				}
				foreach ($wpw1_cwa_reports as $reportsRow) {
					$reports_report_name		= $reportsRow->report_name;
					$content					.= "<tr><td colspan='2'><b>$reports_report_name</b></td></tr>";


					$sql						= "select * from $reportsTableName 
													where report_name='$reports_report_name' 
													order by date_created DESC limit 3";
					$wpw1_cwa_reports_detail			= $wpdb->get_results($sql);
					if ($wpw1_cwa_reports_detail === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$linesOut						= 0;
						foreach($wpw1_cwa_reports_detail as $reportsDetailRow) {
							$reports_ID					= $reportsDetailRow->report_id;
							$reports_report_name		= $reportsDetailRow->report_name;
							$reports_report_date		= $reportsDetailRow->report_date;
							$reports_report_path		= $reportsDetailRow->report_path;
							$reports_report_url			= $reportsDetailRow->report_url;
							$reports_report_filename	= $reportsDetailRow->report_filename;
							$reports_report_data		= $reportsDetailRow->report_data;
							
							$content					.= "
<tr><td style='width:50px;'><input type='radio' class='formInputButton' name='inp_id' value='$reports_ID|id'></td>
	<td>$reports_report_date</td></tr>";
							$linesOut++;
						
						}
						$sql			= "SELECT count(report_id) as report_count 
											from $reportsTableName 
											where report_name = '$reports_report_name'";
						$reports_count	= $wpdb->get_var($sql);

						if ($reports_count > 3) {
							$content					.= "<tr><td><input type='radio' class='formInputButton' name='inp_id' value='$reports_report_name|name'></td>
															<td>Show All</td></tr>";
						}
					}
				}
				$content								.= "<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table></form>";	
			} else {
				$content								.= "<p>No Data Found in $reportsTableName</p>";
			}
		}
	
////////////////  Pass 3 display the requested report
	} elseif ("3" == $strPass) {
	
		if ($doDebug) {
			echo "<br />at pass 3 with inp_id of $inp_id and token of $token<br />";
		}
		$doById			= FALSE;
		$doByName		= FALSE;
		$doByRecent		= FALSE;
		$myInt			= strpos($inp_id,"|");
		if ($myInt !== FALSE) {
			$myArray	= explode("|",$inp_id);
			if ($myArray[1] == 'id') {
				$inp_id		= $myArray[0];		
				$doById		= TRUE;	
			} else {
				$inp_report_name = $myArray[0];
				$doByName	= TRUE;
			}
		} else {
			if ($inp_id == 'recent') {
				$doByRecent	= TRUE;
			} else {
				$doById			= TRUE;
			}
		} 
		if ($doById) {
			// get the requested report
			$sql				= "select * from $reportsTableName where report_id=$inp_id";
			$wpw1_cwa_reports	= $wpdb->get_results($sql);
			if ($wpw1_cwa_reports === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numRRows							= $wpdb->num_rows;
				if ($doDebug) {
					echo "retrieved $numRRows rows from $reportsTableName table<br />";
				}
				if ($numRRows > 0) {
					if ($doDebug) {
						echo "found $numRRows rows in $reportsTableName<br />";
					}
					foreach ($wpw1_cwa_reports as $reportsRow) {
						$reports_ID					= $reportsRow->report_id;
						$reports_report_name		= $reportsRow->report_name;
						$reports_report_date		= $reportsRow->report_date;
						$reports_report_path		= $reportsRow->report_path;
						$reports_report_url			= $reportsRow->report_url;
						$reports_report_filename	= $reportsRow->report_filename;
						$reports_report_data		= $reportsRow->report_data;
								
						if ($doDebug) {
							echo "Have the report info<br />";
						}
						if ($reports_report_data == '') {
							$reports_report_data	= "<p>Report Data No Longer Available</p>";
						}
						$content					.= "<h3>$jobname</h3><h4>$reports_report_name for $reports_report_date</h4>
														$reports_report_data<br /><br /><hr><br /><br />";
					}
					if ($token != '') {
						$resolveResult				= resolve_reminder($inp_callsign,$token,$testMode,$doDebug);
						if ($resolveResult === FALSE) {
							if ($doDebug) {
								echo "resolve_reminder for $inp_callsign and $token failed<br />";
							}
						}
					}
				} else{
					$content						.= "<p>No data found for this report</p>";
				}
			}
		} elseif ($doByName) {
			if ($doDebug) {
				echo "Get the whole report list for $inp_report_name<br />";
			}
			$sql						= "select * from $reportsTableName 
											where report_name='$inp_report_name' 
											order by report_date DESC";
			$wpw1_cwa_reports_detail			= $wpdb->get_results($sql);
			if ($wpw1_cwa_reports_detail === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$linesOut						= 0;
				$content						.= "<h3>Display Saved Report</h3>
													<p>Select the desired report</p>
													<p><form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='3'>
													<table>
													<tr><th colspan='2'>Report Name</th></tr>
													<tr><td colspan='2'>$inp_report_name</td></tr>";
				foreach($wpw1_cwa_reports_detail as $reportsDetailRow) {
					$reports_ID					= $reportsDetailRow->report_id;
					$reports_report_name		= $reportsDetailRow->report_name;
					$reports_report_date		= $reportsDetailRow->report_date;
					$reports_report_path		= $reportsDetailRow->report_path;
					$reports_report_url			= $reportsDetailRow->report_url;
					$reports_report_filename	= $reportsDetailRow->report_filename;
					$reports_report_data		= $reportsDetailRow->report_data;
					
					$content					.= "<tr><td style='width:50px;'><input type='radio' class='formInputButton' name='inp_id' value='$reports_ID|id'></td>
													<td>$reports_report_date</td></tr>";
					$linesOut++;
				
				}
				$content								.= "<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table></form>";	
			}
		} elseif ($doByRecent) {
			if ($doDebug) {
				echo "Get the 30 recent reports<br />";
			}
			$sql						= "select * from $reportsTableName 
											order by report_date DESC 
											limit 30";
			$wpw1_cwa_reports_detail			= $wpdb->get_results($sql);
			if ($wpw1_cwa_reports_detail === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$linesOut						= 0;
				$content						.= "<h3>$jobname</h3>
													<p>Showing the 30 most recent reports</p>
													<p>Select the desired report</p>
													<p><form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='3'>
													<table style='width:400px;'>
													<tr><th>Select</th>
														<th>Report Name</th>
														<th>Report Date</th></tr>";
				foreach($wpw1_cwa_reports_detail as $reportsDetailRow) {
					$reports_ID					= $reportsDetailRow->report_id;
					$reports_report_name		= $reportsDetailRow->report_name;
					$reports_report_date		= $reportsDetailRow->report_date;
					$reports_report_path		= $reportsDetailRow->report_path;
					$reports_report_url			= $reportsDetailRow->report_url;
					$reports_report_filename	= $reportsDetailRow->report_filename;
					$reports_report_data		= $reportsDetailRow->report_data;
					
					$content					.= "<tr><td style='width:50px;'><input type='radio' class='formInputButton' name='inp_id' value='$reports_ID|id'></td>
													<td>$reports_report_name</td>
													<td>$reports_report_date</td></tr>";
					$linesOut++;
				
				}
				$content								.= "<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table></form>";	
			}
		}


	}	
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>V$versionNumber. Prepared at $thisTime</p>";
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
	$result			= write_joblog_func("Display Saved Report|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	
	return $content;
}
add_shortcode ('display_saved_report', 'display_saved_report_func');
