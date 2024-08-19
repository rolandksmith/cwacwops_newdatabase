function manage_directory_func() {


	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 		= $initializationArray['validUser'];
	$userName  		= $initializationArray['userName'];
	$flatFilePath 	= $initializationArray['flatFilePath'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-manage-directory/";
	$inp_directory				= $flatFilePath;
	$inp_action					= '';
	$inp_copyfile				= '';
	$inp_actiontype				= 'normal';
	$nextPass					= '3';
	$filesDeleted				= 0;
	$filesFailed				= 0;

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
			if ($str_key 		== "inp_directory") {
				$inp_directory	 = $str_value;
				$inp_directory	 = filter_var($inp_directory,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_action") {
				$inp_action	 = $str_value;
				$inp_action	 = filter_var($inp_action,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_actiontype") {
				$inp_actiontype	 = $str_value;
				$inp_actiontype	 = filter_var($inp_actiontype,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_copyfile") {
				$inp_copyfile	 = $str_value;
				$inp_copyfile	 = filter_var($inp_copyfile,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_delete") {
				$inp_delete	 = $str_value;
				if ($doDebug) {
					echo "inp_delete:<br /><pre>";
					print_r($inp_delete);
					echo "</pre><br />";
				}
//				$inp_delete	 = filter_var($inp_delete,FILTER_UNSAFE_RAW);
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
		echo "<b>Operating in TestMode</b><br />";
		$reportsTableName		= 'wpw1_cwa_reports2';
	} else {
		$reportsTableName		= 'wpw1_cwa_reports';
}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Manage a Directory</h3>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
Directory Path:<br />
<input type='text' class='formInputText' name='inp_directory' value='$inp_directory' size='50' maxlength='100' value='$inp_directory'><br />
<br />Action Type:<br />
<input type='radio' class='formInputText' name='inp_actiontype' value='normal' checked> Normal Process<br />
<input type='radio' class='formInputText' name='inp_actiontype' value='bulk'> Bulk Deletion<br /><br />
<input class='formInputButton' type='submit' value='Submit' />
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "arrived at pass 2 with $inp_directory<br />";
		}	
		$myFileArray				= scandir($inp_directory);
		if ($myFileArray === FALSE) {
			$content				.= "No directory named $inp_directory found.";
		} else {
			if ($doDebug) {
				echo "Got the myFileArray:<br /><pre>";
				print_r($myFileArray);
				echo "</pre><br />";
			}
			if ($inp_actiontype == 'normal') {
				$nextPass			= '3';
			} else {
				$nextPass			= '5';
			}
		
		
			$content				.= "<h3>Manage Directory</h3>
Directory $inp_directory contents:
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='$nextPass'>
<input type='hidden' name='inp_directory' value='$inp_directory'>
<table>
<tr><th>Options</th>
	<th>File Name</th></tr>";
			if ($inp_actiontype == 'normal') {
				foreach($myFileArray as $myKey=>$myValue) {
					if ($myKey > 1) {
						$content		.= "
<tr><td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_action' value='D-$myValue'> Display File<br />
									<input type='radio' class='formInputButton' name='inp_action' value='U-$myValue'> Delete File</td>
	<td style='vertical-align:top;'>$myValue</td></tr>";
					}
				}
				$content				.= "
<tr><td colspan='2'><hr></td></tr>
<tr><td>Copy a file to this directory:</td>
	<td><input type='radio' class='formInputButton' name='inp_action' value='M-blank'> Copy a file</td></tr>
<tr><td colspan='2'>Path and File to be copied:<br />
					<input type='text' class='formInputText' name='inp_copyfile' size='50' maxlength='100'><br />
					<em>File to  be copied will be deleted if successfully copied</em></td></tr>";
			} else {
				foreach($myFileArray as $myKey=>$myValue) {
					if ($myKey > 1) {
						$content		.= "
<tr><td style='vertical-align:top;'><input type='checkbox' class='formInputButton' name='inp_delete[]' value='$myValue'> Delete?</td>
	<td style='vertical-align:top;'>$myValue</td></tr>";
					}
				}
			}
			$content					.= "			
</table><br />
<input class='formInputButton' type='submit' value='Submit' />
</form>";
		}	
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "arrived at pass 3 with $inp_action and $inp_directory<br />";
		}
		$content		.= "<h3>Manage Directory</h3>";
		$myArray		= explode("-",$inp_action);
		if ($myArray[0] == 'D') {			// display the file
			$content	.= "<h4>Displaying File $inp_directory/$myArray[1]:</h4>";
			$handle		= fopen("$inp_directory/$myArray[1]","r");
			if ($handle === FALSE) {
				$content	.= "File $inp_directory/$myArray[1] failed to open.";
			} else {
				while (($buffer = fgets($handle, 4096)) !== false) {
					$buffer		= trim($buffer);
					$content	.= "$buffer</b><br />";
				}
			}
		} elseif ($myArray[0] == 'U') {		// unlink the file
			$thisFilename		= $myArray[1];
			$result		= unlink("$inp_directory/$thisFilename");
			if ($result === FALSE) {
				$content		.= "Unlinking $inp_directory/$thisFilename failed<br />";
			} else {
				$content		.= "Unlinking $inp_directory/$thisFilename was successful<br />";
			}

		} elseif ($myArray[0] == 'M') {		// move a file to this directory
			if ($doDebug) {
				echo "Preparing to move $inp_copyfile to $inp_directory<br />";
			}
			if ($inp_copyfile == '') {
				if ($doDebug) {
					echo "File to be copied not specified<br />";
				}
			} else {
				$thisArray		= explode("/",$inp_copyfile);
				$myInt			= count($thisArray) -1;
				$outFile		= $thisArray[$myInt];
				$outPath		= "$inp_directory/$outFile";
				if ($doDebug) {
					echo "attempting to copy $inp_copyfile to $outPath<br />";
				}
				if (copy($inp_copyfile,$outPath)) {
					if ($doDebug) {
						echo "Copy was successful<br />";
					}
					$content	.= "$inp_copyfile successfully copied to $outPath<br />";
					$result		= unlink($inp_copyfile);
					if ($result === FALSE) {
						$content	.= "Unlinking $inp_copyfile failed<br />";
					} else {
						$content	.= "Unlinking $inp_copyfile was successful<br />";
					}

				}
			}
		}
	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "At pass 5<br />";
		}
		
		$content			.= "<h3>Manage Directory</h3>
<h4>Bulk Deletion</h4>
<table>
<tr><th>File Name</th>
	<th>Deletion Status</th></tr>";
		foreach($inp_delete as $myValue) {
			$thisFilename	= "$inp_directory/$myValue";
			if (file_exists($thisFilename)) {
				$result		= unlink($thisFilename);
// $result = TRUE;
				if ($result === FALSE) {
					$thisMessage		= "Unlinking $thisFilename failed";
					$filesFailed++;
				} else {
					$thisMessage		= "Unlinking successful";
					$filesDeleted++;
					
// see if this file is in the reports table. If so, delete the entry
					$sql				= "select * from $reportsTableName where report_url='$thisFilename'";
					$wpw1_cwa_reports		= $wpdb->get_results($sql);
					if ($doDebug) {
						echo "Reading $reportsTableName table<br />";
						echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
						if ($wpdb->last_error != '') {
							echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
						}
					}
					if ($wpw1_cwa_reports !== FALSE) {
						$numRRows									= $wpdb->num_rows;
						if ($doDebug) {
							echo "retrieved $numRRows rows from $reportsTableName table<br />";
						}
						if ($numRRows > 0) {
							if ($doDebug) {
								echo "found $numRRows rows in $reportsTableName<br />";
							}
							foreach ($wpw1_cwa_reports as $reportsRow) {
								$reports_ID						= $reportsRow->report_id;
								$reports_report_name			= $reportsRow->report_name;
								$reports_report_date			= $reportsRow->report_date;
								$reports_report_path			= $reportsRow->report_path;
								$reports_report_url				= $reportsRow->report_url;
								$reports_report_filename		= $reportsRow->report_filename;

/// report table record exists. Delete it
								$result				= $wpdb->delete($reportsTableName,
																	array('report_id'=>$reports_ID),
																	array('%d'));
								if ($result === FALSE) {
									if ($doDebug) {
											echo "Deleting $reportsTableName record at $reports_ID failed<br />
Result: $result<br />
wpdb->last_query: " . $wpdb->last_query . "<br />";
										if ($wpdb->last_error != '') {
											echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
										}
									}
								} else {
									if ($doDebug) {
										echo "Successfully deleted $reportsTableName record at $reports_ID<br />
Result: $result<br />
wpdb->last_query: " . $wpdb->last_query . "<br />";
										if ($wpdb->last_error != '') {
											echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
										}

									}
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "Either $reportsTableName table not found or bad $sql 01<br />";
						}
					}
					
				}
				$content		.= "
<tr><td>$myValue</td>
	<td>$thisMessage</td></tr>";

			} else {
				$content		.= "
<tr><td>$myValue</td>
	<td>$thisFilename does not exist</td></tr>";
				$filesFailed++;
			}
		}
		$content					.= "</table>
$filesDeleted files successfully unlinked<br />
$filesFailed files not able to be unlinked<br />";
		
		
	}
	$content		.= "<br /><a href='$theURL'>Do It Again</a><br />";
	$thisTime 		= date('Y-m-d H:i:s');
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
	$result			= write_joblog_func("Manage Directory|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	return $content;
}
add_shortcode ('manage_directory', 'manage_directory_func');
