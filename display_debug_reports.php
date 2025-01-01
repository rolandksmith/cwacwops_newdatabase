function display_debug_reports_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}


	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	$wpdb->show_errors();

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-display-debug-reports/";
	$jobname					= "Display Debug Reports V$versionNumber";
	$submit						= '';


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
			if ($str_key 		== "inp_file") {
				$inp_file	 = $str_value;
				$inp_file	 = filter_var($inp_file,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "submit") {
				$submit	 = $str_value;
				$submit	 = filter_var($submit,FILTER_UNSAFE_RAW);
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
	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br/>at pass1<br/>";
		}
		
		// get list of filenames

		$directory = '/home/cwacwops/public_html/wp-content/uploads/';
		$matchingFiles = [];
		
		// Get all files in the directory
		$allFiles = scandir($directory);
		
		// Filter files starting with 'daily_student_cron_debug'
		foreach ($allFiles as $file) {
		  if (strpos($file, 'daily_student_cron_debug') === 0) {
			$matchingFiles[] = $file;
		  }
		}
		
		// Now $matchingFiles contains the filenames you need
		rsort($matchingFiles);
		if ($doDebug) {
			echo "<br />matchingFiles:<br /><pre>";
			print_r($matchingFiles); 
			echo "</pre><br />";
		}
		$content 		.= "<h3>$jobname</h3>
							<p>Select the report to be displayed (or deleted)</p>
							<table>";
		
		foreach($matchingFiles as $thisFileName) {
			$fileNameArray	= explode("_",$thisFileName);
			$fileNameDate	= $fileNameArray[4];
			$fileNameYear 	= substr($fileNameDate,0,4);
			$fileNameDay 	= substr($fileNameDate,4,2);
			$fileNameMonth 	= substr($fileNameDate,4,2);
			$fileNameDay 	= substr($fileNameDate,6,2);
			$fileNameHour 	= substr($fileNameDate,8,2);
			$fileNameMinut 	= substr($fileNameDate,10,2);
			$fileNameMinute	= substr($fileNameDate,10,2);
			$fileInfo 		= $fileNameYear . "-" . $fileNameMonth . "-" . $fileNameDay . " " . $fileNameHour . ":" . $fileNameMinute;
			$content	.= "<tr><td>$thisFileName ($fileInfo)<br />
							<table>
							<tr><td style='width:100px;'><form method='post' action='$siteURL/wp-content/uploads/$thisFileName' target='_blank' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input class='formInputButton' type='submit' name='submit' value='Display' />
									</form></td>
								<td></td>
								<td></td></tr>
							</table><br /><hr></td></tr>";
		}
		$content		.= "</table>";	
	}	
//								<td style='width:100px;'><form method='post' action=$theURL 
//									name='delete_form' ENCTYPE='multipart/form-data'>
//									<input type='hidden' name='strpass' value='3'>
//									<input type='hidden' name='inp_file' value='$thisFileName'>
//									<input style='padding:0 0 0 0;' class='formInputButton' name='submit' type='submit' value='Delete' /></td>
	if ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 3 with file $inp_file<br />";
		}
		$content		.= "<h3>$jobname</h3>";
		// The full path to the file you want to delete
		$file_to_delete = '/home/cwacwops/public_html/wp-content/uploads/' . $inp_file; 
		
		// Use the WordPress function wp_delete_file() for safer file deletion
		if (wp_delete_file($file_to_delete)) {
			$content	.= "File deleted successfully.";
		} else {
			$content	.= "Error deleting file. Please check file permissions and path.";
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
add_shortcode ('display_debug_reports', 'display_debug_reports_func');
