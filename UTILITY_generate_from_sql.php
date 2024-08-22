function utility_generate_from_sql_func() {

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
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
		$doDebug			= FALSE;
		$testMode			= FALSE;
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
//	} else {
//		$wpdb->hide_errors();
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/utility-generate-from-sql/";
	$jobname					= "UTILITY Generate from SQL V$versionNumber";
	
	$runType					= "";
	$databaseTableName			= "";
	$tableName					= "";
	$tableAbbreviation			= "";
	$fieldArray					= array();
	

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
			if ($str_key 		== "inp_tableData") {
				$inp_tableData	 = $str_value;
				$inp_tableData	 = filter_var($inp_tableData,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_runType") {
				$inp_runType	 = $str_value;
				$inp_runType	 = filter_var($inp_runType,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_time_zone") {
				$inp_student_time_zone = $str_value;
				$inp_student_time_zone = filter_var($inp_student_time_zone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_timezone_id") {
				$inp_student_timezone_id = $str_value;
				$inp_student_timezone_id = filter_var($inp_student_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_timezone_offset") {
				$inp_student_timezone_offset = $str_value;
				$inp_student_timezone_offset = filter_var($inp_student_timezone_offset,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_youth") {
				$inp_student_youth = $str_value;
				$inp_student_youth = filter_var($inp_student_youth,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_age") {
				$inp_student_age = $str_value;
				$inp_student_age = filter_var($inp_student_age,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_student_parent") {
				$inp_student_student_parent = $str_value;
				$inp_student_student_parent = filter_var($inp_student_student_parent,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_student_parent_email") {
				$inp_student_student_parent_email = $str_value;
				$inp_student_student_parent_email = filter_var($inp_student_student_parent_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_level") {
				$inp_student_level = $str_value;
				$inp_student_level = filter_var($inp_student_level,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_waiting_list") {
				$inp_student_waiting_list = $str_value;
				$inp_student_waiting_list = filter_var($inp_student_waiting_list,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_request_date") {
				$inp_student_request_date = $str_value;
				$inp_student_request_date = filter_var($inp_student_request_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_semester") {
				$inp_student_semester = $str_value;
				$inp_student_semester = filter_var($inp_student_semester,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_notes") {
				$inp_student_notes = $str_value;
				$inp_student_notes = filter_var($inp_student_notes,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_welcome_date") {
				$inp_student_welcome_date = $str_value;
				$inp_student_welcome_date = filter_var($inp_student_welcome_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_email_sent_date") {
				$inp_student_email_sent_date = $str_value;
				$inp_student_email_sent_date = filter_var($inp_student_email_sent_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_email_number") {
				$inp_student_email_number = $str_value;
				$inp_student_email_number = filter_var($inp_student_email_number,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_response") {
				$inp_student_response = $str_value;
				$inp_student_response = filter_var($inp_student_response,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_response_date") {
				$inp_student_response_date = $str_value;
				$inp_student_response_date = filter_var($inp_student_response_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_abandoned") {
				$inp_student_abandoned = $str_value;
				$inp_student_abandoned = filter_var($inp_student_abandoned,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_student_status") {
				$inp_student_student_status = $str_value;
				$inp_student_student_status = filter_var($inp_student_student_status,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_action_log") {
				$inp_student_action_log = $str_value;
				$inp_student_action_log = filter_var($inp_student_action_log,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_pre_assigned_advisor") {
				$inp_student_pre_assigned_advisor = $str_value;
				$inp_student_pre_assigned_advisor = filter_var($inp_student_pre_assigned_advisor,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_selected_date") {
				$inp_student_selected_date = $str_value;
				$inp_student_selected_date = filter_var($inp_student_selected_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_no_catalog") {
				$inp_student_no_catalog = $str_value;
				$inp_student_no_catalog = filter_var($inp_student_no_catalog,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_hold_override") {
				$inp_student_hold_override = $str_value;
				$inp_student_hold_override = filter_var($inp_student_hold_override,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_messaging") {
				$inp_student_messaging = $str_value;
				$inp_student_messaging = filter_var($inp_student_messaging,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_assigned_advisor") {
				$inp_student_assigned_advisor = $str_value;
				$inp_student_assigned_advisor = filter_var($inp_student_assigned_advisor,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_advisor_select_date") {
				$inp_student_advisor_select_date = $str_value;
				$inp_student_advisor_select_date = filter_var($inp_student_advisor_select_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_advisor_class_timezone") {
				$inp_student_advisor_class_timezone = $str_value;
				$inp_student_advisor_class_timezone = filter_var($inp_student_advisor_class_timezone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_hold_reason_code") {
				$inp_student_hold_reason_code = $str_value;
				$inp_student_hold_reason_code = filter_var($inp_student_hold_reason_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_class_priority") {
				$inp_student_class_priority = $str_value;
				$inp_student_class_priority = filter_var($inp_student_class_priority,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_assigned_advisor_class") {
				$inp_student_assigned_advisor_class = $str_value;
				$inp_student_assigned_advisor_class = filter_var($inp_student_assigned_advisor_class,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_promotable") {
				$inp_student_promotable = $str_value;
				$inp_student_promotable = filter_var($inp_student_promotable,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_excluded_advisor") {
				$inp_student_excluded_advisor = $str_value;
				$inp_student_excluded_advisor = filter_var($inp_student_excluded_advisor,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_student_survey_completion_date") {
				$inp_student_student_survey_completion_date = $str_value;
				$inp_student_student_survey_completion_date = filter_var($inp_student_student_survey_completion_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_available_class_days") {
				$inp_student_available_class_days = $str_value;
				$inp_student_available_class_days = filter_var($inp_student_available_class_days,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_intervention_required") {
				$inp_student_intervention_required = $str_value;
				$inp_student_intervention_required = filter_var($inp_student_intervention_required,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_copy_control") {
				$inp_student_copy_control = $str_value;
				$inp_student_copy_control = filter_var($inp_student_copy_control,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_first_class_choice") {
				$inp_student_first_class_choice = $str_value;
				$inp_student_first_class_choice = filter_var($inp_student_first_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_second_class_choice") {
				$inp_student_second_class_choice = $str_value;
				$inp_student_second_class_choice = filter_var($inp_student_second_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_third_class_choice") {
				$inp_student_third_class_choice = $str_value;
				$inp_student_third_class_choice = filter_var($inp_student_third_class_choice,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_first_class_choice_utc") {
				$inp_student_first_class_choice_utc = $str_value;
				$inp_student_first_class_choice_utc = filter_var($inp_student_first_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_second_class_choice_utc") {
				$inp_student_second_class_choice_utc = $str_value;
				$inp_student_second_class_choice_utc = filter_var($inp_student_second_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_third_class_choice_utc") {
				$inp_student_third_class_choice_utc = $str_value;
				$inp_student_third_class_choice_utc = filter_var($inp_student_third_class_choice_utc,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_catalog_options") {
				$inp_student_catalog_options = $str_value;
				$inp_student_catalog_options = filter_var($inp_student_catalog_options,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_flexible") {
				$inp_student_flexible = $str_value;
				$inp_student_flexible = filter_var($inp_student_flexible,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_date_created") {
				$inp_student_date_created = $str_value;
				$inp_student_date_created = filter_var($inp_student_date_created,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_student_date_updated") {
				$inp_student_date_updated = $str_value;
				$inp_student_date_updated = filter_var($inp_student_date_updated,FILTER_UNSAFE_RAW);
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
/*
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
*/


	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>This is a utility probram used by Roland to <br />
							1. Generate the include file to read a table<br />
							2. Generate the code to maintain a table
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Run Type</td>
								<td><input type='radio' class='formInputButton' name='inp_runType' value='read'> Generate Read Code<br />
									<input type='radio' class='formInputButton' name='inp_runType' value='update'> Generate update code</td></tr>
							<tr><td style='vertical-align:top;'>Table Data</td>
								<td><textarea class='formInputText' name='inp_tableData' cols='50' rows='5'></textarea></td></tr>
							<tr><td>Verbose Debugging?</td>
								<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass2 with runType of $inp_runType<br />";
		}

		$content		.= "<h3>$jobname</h3>";
				
		if ($inp_tableData == '') {
			$content	.= "Table Data missing";
		} else {
//			$inp_tableData	= str_replace("\n","",$inp_tableData);
			$thisTableData = explode("|",$inp_tableData);
			
			if ($doDebug) {
				echo "here is the table data:<br /><pre>";
				print_r($thisTableData);
				echo "</pre><br />";
			}
			$increment						= 0;
			foreach($thisTableData as $thisRow) {
				$gotARow	= explode("&",$thisRow);
				if ($doDebug) {
					echo "gotARow:<br /><pre>";
					print_r($gotARow);
					echo "</pre><br />";
				}
				if ($gotARow[0] == 'table') {
					$databaseTableName			= $gotARow[1];
					$tableName					= $gotARow[2];
					$tableAbbreviation			= $gotARow[3];
				} else {
					$myInt						= strpos($gotARow[0],"field");
					if ($myInt !== FALSE) {
						$increment++;
						$fieldArray[$increment]['fieldname']	= $gotARow[1];
						$fieldArray[$increment]['fieldtype']	= $gotARow[2];
						$fieldArray[$increment]['fieldupdate']	= $gotARow[3];
					} else {
						if ($doDebug) {
							echo "Do not recognize this row<br />";
						}
					}
				}
			}
			if ($doDebug) {
				echo "<br />fieldArray:<br /><pre>";
				print_r($fieldArray);
				echo "</pre><br />";
			}				
			
			if ($inp_runType == 'read') {
				$content			.= "<h4>Generating the Database Read Code</h4><pre>
\$$databaseTableName\t\t= \$wpdb->get_results(\$sql);
if (\$$databaseTableName === FALSE) {
\thandleWPDBError(\$jobname,\$doDebug);
} else {
\t\$numRows \t\t= \$wpdb->num_rows;
\tif (\$doDebug) {
\t\techo \"ran \$sql&lt;br /&gt; and found \$numRows rows in \$$databaseTableName table\";
\t}
\tif (\$numRows > 0) {
\t\tforeach (\$$databaseTableName as \$$tableAbbreviation" . "Row) {\n";

				$fieldCount			= count($fieldArray);
				for($ii=1;$ii<=$fieldCount;$ii++) {
					$myStr			= "\$$tableAbbreviation" . "Row";
					$thisFieldName	= $fieldArray[$ii]['fieldname'];
					$myStr1			= "\$$tableAbbreviation" . "_" . "$thisFieldName";
					$content		.= "\t\t\t$myStr1 \t\t= $myStr -> $thisFieldName;\n";
				}

				$content			.= "\t\t}
										</pre><br />";

/////////??????????/////////

			} elseif ($inp_runType == 'update') {
			
				$content			.= "<h4>Generating the Databse Display Code</h4>
										<pre><code>&lt;table style='width:1000px;'&gt;\n";
				if ($doDebug) {
					echo "<br /><b>Generating the Database Display Code</b><br />";
				}

				for($ii=1;$ii<=count($fieldArray);$ii++) {
					$thisFieldName	= $fieldArray[$ii]['fieldname'];
					$thisFieldType	= $fieldArray[$ii]['fieldtype'];
					$thisFieldUpdate	= $fieldArray[$ii]['fieldupdate'];
					$displayFieldName	= "$tableAbbreviation" . "_" . "$thisFieldName";
					$myStr				= str_replace("_"," ",$displayFieldName);
					$myStr				=ucwords($myStr);
					if ($doDebug) {
						echo "<br />processing $thisFieldName type $thisFieldType<br />";
					}
					$content		.= "&lt;tr&gt;&lt;td&gt;$myStr&lt;/td&gt;
\t&lt;td&gt;\$$displayFieldName&lt;/td&gt;&lt;/tr&gt;\n";
				}
				$content			.= "&lt;/table&gt;\n</code></pre><br />";
				
			
//////////?????????///////			
			
				$content			.= "<h4>Generating the Databse Update Code</h4>
										<pre><code>
\$content\t.= \"
&lt;form method='post' action='\$theURL' 
name='deletion_form' ENCTYPE='multipart/form-data'&gt;
&lt;input type='hidden' name='strpass' value='5'&gt;
&lt;input type='hidden' name='inp_???_id' value='\$inp_???_id'&gt;
&lt;input type='hidden' name='inp_callsign' value='\$inp_callsign'&g6;
&lt;input class='formInputButton' type='submit' 
onclick=\"return confirm('Are you sure?');\"  
value='Delete This Record' /&gt;
&lt;/form&gt;&lt;br /&gt;
&lt;form method='post' action='\$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'&gt;
&lt;input type='hidden' name='strpass' value='4'&gt;
&lt;input type='hidden' name='inp_???_id' value='\$inp_???_id'&gt;
&lt;table style='width:1000px;'&gt;\n";
				
				if ($doDebug) {
					echo "<br /><b>Generating the Databse Update Code<</b><br />";
				}

				for($ii=1;$ii<=count($fieldArray);$ii++) {
					$thisFieldName	= $fieldArray[$ii]['fieldname'];
					$thisFieldType	= $fieldArray[$ii]['fieldtype'];
					$thisFieldUpdate	= $fieldArray[$ii]['fieldupdate'];
					$displayFieldName	= "$tableAbbreviation" . "_" . "$thisFieldName";
					if ($doDebug) {
						echo "<br />processing $thisFieldName type $thisFieldType<br />";
					}
					
					if ($thisFieldUpdate == 'X') {
						$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;\$$displayFieldName&lt;/td&gt;&lt;/tr&gt;\n";
						if ($doDebug) {
							echo "processing noupdate line $thisFieldName<br />";
						}
					} else {

/*
	int			type='text' size='20' maxlength='20'
	varchar(n)	type='text'	size='n' maxlength='n'
	char(n)		type='text' if n < 5 size='5' maxlength='5' else size='n' maxlength='n'
	text		textarea cols='50' rows='5'
	tinyint		type='text' size='10' maxlength='10'
	float(n,n)	type='text' size='20' maxlength='20'
	datetime	type='text' size='20' maxlength='20'
	timestamp	type='text' size='20' maxlength='20'
	smallint	type='text' size='15' maxlength='15'
*/						
						
						if (preg_match("/int/i",$thisFieldType)) {
							$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;&lt;input type='text' class='formInputText' name='inp_$displayFieldName' length='20' 
\tmaxlength='20' value='$$displayFieldName'&gt;&lt;/td&gt;&lt;/tr&gt;\n";
						}
						
						if (preg_match("/varchar/i",$thisFieldType)) {
							$myStr			= str_replace("varchar","",$thisFieldType);
							$myStr			= str_replace("(","",$myStr);
							$myStr			= str_replace(")","",$myStr);
							if ($doDebug) {
								echo "processing varchar line $thisFieldName yielding length of $myStr<br />";
							}
							$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;&lt;input type='text' class='formInputText' name='inp_$displayFieldName' length='$myStr' 
\tmaxlength='$myStr' value='\$$displayFieldName'&gt;&lt;/td&gt;&lt;/tr&gt;\n";

						} elseif (preg_match("/char/i",$thisFieldType)) {
							$myStr			= str_replace("char","",$thisFieldType);
							$myStr			= str_replace("(","",$myStr);
							$myStr			= str_replace(")","",$myStr);
							if ($doDebug) {
								echo "processing char line $thisFieldName yielding length of $myStr<br />";
							}
							$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;&lt;input type='text' class='formInputText' name='inp_$displayFieldName' length='$myStr' 
\tmaxlength='$myStr' value='\$$displayFieldName'&gt;&lt;/td&gt;&lt;/tr&gt;\n";

						} elseif (preg_match("/text/i",$thisFieldType)) {
							$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;&lt;textarea class='formInputText' name='inp_$displayFieldName' rows='5' cols='50'&gt;\$$displayFieldName&lt;/textarea&gt;&lt;/td&gt;&lt;/tr&gt;\n";

						} elseif (preg_match("/tinyint/i",$thisFieldType)) {
							$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;&lt;input type='text' class='formInputText' name='inp_$displayFieldName' length='10' 
\tmaxlength='10' value='$$displayFieldName'&gt;&lt;/td&gt;&lt;/tr&gt;\n";
							
						
						} elseif (preg_match("/float/i",$thisFieldType)) {
							$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;&lt;input type='text' class='formInputText' name='inp_$displayFieldName' length='20' 
\tmaxlength='20' value='$$displayFieldName'&gt;&lt;/td&gt;&lt;/tr&gt;\n";
						
						} elseif (preg_match("/datetime/i",$thisFieldType)) {
							$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;&lt;input type='text' class='formInputText' name='inp_$displayFieldName' length='20' 
\tmaxlength='20' value='\$$displayFieldName'&gt;&lt;/td&gt;&lt;/tr&gt;\n";
						
						} elseif (preg_match("/timestamp/i",$thisFieldType)) {
							$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;&lt;input type='text' class='formInputText' name='inp_$displayFieldName' length='20' 
\tmaxlength='20' value='\$$displayFieldName'&gt;&lt;/td&gt;&lt;/tr&gt;\n";
						
						} elseif (preg_match("/smallint/i",$thisFieldType)) {
							$content		.= "&lt;tr&gt;&lt;td&gt;$displayFieldName&lt;/td&gt;
\t&lt;td&gt;&lt;input type='text' class='formInputText' name='inp_$displayFieldName' length='15' 
\tmaxlength='15' value='$$displayFieldName'&gt;&lt;/td&gt;&lt;/tr&gt;\n";
						
						}
						
					}
				}	
				$content			.= "&lt;tr&gt;&lt;td colspan='2'&gt;&lt;input class='formInputButton' type='submit' value='Submit Updates' /&gt;&lt;/td&gt;&lt;/tr&gt;
&lt;/table&gt;&lt;/form&gt;\";\n</code></pre><br />";


//////////?????????///////
				
				$content			.= "<h4>Generating _Request Code</h4>
										<pre><code>\n";
				if ($doDebug) {
					echo "<br /><b>Generating _Request Code</b><br />";
				}

/*
			if ($str_key 		== "inp_tableData") {
				$inp_tableData	 = $str_value;
				$inp_tableData	 = filter_var($inp_tableData,FILTER_UNSAFE_RAW);
			}
*/

				for($ii=1;$ii<=count($fieldArray);$ii++) {
					$thisFieldName		= $fieldArray[$ii]['fieldname'];
					$thisFieldType		= $fieldArray[$ii]['fieldtype'];
					$thisFieldUpdate	= $fieldArray[$ii]['fieldupdate'];
					$displayFieldName	= "$tableAbbreviation" . "_" . "$thisFieldName";
					if ($doDebug) {
						echo "<br />processing $thisFieldName type $thisFieldType<br />";
					}
					
					if ($thisFieldUpdate != 'X') {
						$content		.= "if (\$str_key == \"inp_$displayFieldName\") {
\t\$inp_$displayFieldName = \$str_value;
\t\$inp_$displayFieldName = filter_var(\$inp_$displayFieldName,FILTER_UNSAFE_RAW);
}\n";
					}
				}
				$content			.= "</code></pre><br />";

//////???????////	
		
				$content			.= "<h4>Generating Field Update Code</h4>
										<pre><code>\n";
				if ($doDebug) {
					echo "<br /><b>Generating Field Update Code</b><br />";
				}
				
				$content				.= "\$thisDate = date('Y-m-d H:i:s');
\$updateParams\t= array();\n\$updateFormat\t= array();\n\$updateLog\t= \" /\$thisDate \$userName performed the following updates:\";\n";
				for($ii=1;$ii<=count($fieldArray);$ii++) {
					$thisFieldName		= $fieldArray[$ii]['fieldname'];
					$thisFieldType		= $fieldArray[$ii]['fieldtype'];
					$thisFieldUpdate	= $fieldArray[$ii]['fieldupdate'];
					$displayFieldName	= "$tableAbbreviation" . "_" . "$thisFieldName";
					if ($doDebug) {
						echo "<br />processing $thisFieldName type $thisFieldType<br />";
					}
					
					if ($thisFieldUpdate != 'X') {
						if ($doDebug) {
							echo "field can be updated<br />";
						}
						// figure out the field format
						$myStr				= "";
						if (preg_match("/int/i",$thisFieldType)) {
							$myStr 	= "\"%s\"";
						} elseif (preg_match("/varchar/i",$thisFieldType)) {
							$myStr 	= "\"%s\"";
						} elseif (preg_match("/char/i",$thisFieldType)) {
							$myStr 	= "\"%s\"";
						} elseif (preg_match("/text/i",$thisFieldType)) {
							$myStr 	= "\"%s\"";
						} elseif (preg_match("/tinyint/i",$thisFieldType)) {
							$myStr	= "\"%d\"";
						} elseif (preg_match("/float/i",$thisFieldType)) {
							$myStr	= "\"%f\"";
						} elseif (preg_match("/datetime/i",$thisFieldType)) {
							$myStr	= "\"%d\"";
						} elseif (preg_match("/timestamp/i",$thisFieldType)) {
							$myStr	= "\"%d\"";
						} elseif (preg_match("/smallint/i",$thisFieldType)) {
							$myStr	= "\"%d\"";
						}
						
						$content		.= "if (\$inp" . "_" ."$displayFieldName != \$$displayFieldName) {
\t\$$displayFieldName = \$inp" . "_" . "$displayFieldName;
\t\$updateParams['$thisFieldName']\t= \$inp" . "_" . "$displayFieldName;
\t\$updateFormat[]\t= $myStr;
\t\$content\t.= \"$displayFieldName updated to \$inp" . "_" . "$displayFieldName&lt;br /&gt;\";
\t\$updateLog\t.= \" / $displayFieldName updated to \$inp" . "_" . "$displayFieldName\";\n}\n";
					}
				}
				$content			.= "</code></pre><br />";
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
add_shortcode ('utility_generate_from_sql', 'utility_generate_from_sql_func');
