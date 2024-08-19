function generic_updater_func() {
	$doDebug						= FALSE;
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL			= $initializationArray['siteurl'];
	
	global $wpdb;
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-generic-updater/";

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
			if ($str_key 		== "inp_table") {
				$inp_table		 = $str_value;
				$inp_table		 = filter_var($inp_table,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_statement") {
				$inp_statement	 = $str_value;
				$inp_statement	 = filter_var($inp_statement,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_where") {
				$inp_where	 	= $str_value;
				$inp_where	 	= filter_var($inp_where,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "theSQL") {
				$theSQL			 = $str_value;
				$theSQL	 		 = filter_var($theSQL,FILTER_UNSAFE_RAW);
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
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Generic Updater</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Enter the Table Name</td>
								<td><input type='text' class='formInputText' name='inp_table' size='20' maxlength='50'></td></tr>
							<tr><td style='vertical-align:top;'>Where<br />field|operator|value|format</td>
								<td><textarea class='formInputText' name='inp_where' rows='5' cols='50'></textarea></td></tr>
							<tr><td style='vertical-align:top;'>Update Statements Separated by Commas<br />Format: field|value|format<br />i.e., first_name|roland|s</td>
								<td><textarea class='formInputText' name='inp_statement' rows='5' cols='50'></textarea></td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>
							<p>Where operators: =:equal to; <: less than; >:greater then; !=: not equal to<br />
							Examples: welcome_date| > |2022-03-12|s translates to where welcome_date > '2022-03-12'</p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		global $wpdb;

		$inp_where				= stripslashes($inp_where);

		$content				.= "<h3>Preparing the Update Query</h3>
									Table: $inp_table<br />
									Where: $inp_where<br />
									Statement: $inp_statement<br />";

		$updateFields			= "";
		$updateParams			= "set ";
		$statementArray			= explode(",",$inp_statement);
		$needComma				= FALSE;
		foreach ($statementArray as $myValue) {
			$valueArray			= explode("|",$myValue);
			$thisField			= $valueArray[0];
			$thisValue			= $valueArray[1];
			$thisFormat			= $valueArray[2];
			if ($needComma) {
				$updateParams	.= ", ";
				$updateFields	.= ", ";
			} else {
				$needComma		= TRUE;
			}
			if ($thisFormat == 's') {
				$updateParams		.= "$thisField = '$thisValue'";
			} else {
				$updateParams		.= "$thisField = $thisValue";
			}
		}
		
		$thisWhere					= "";
		if ($inp_where != '') {
			$thisWhere				= " where ";
			$needAnd				= FALSE;
			$whereArray				= explode(",",$inp_where);
			foreach($whereArray as $myValue) {
				$thisArray			= explode("|",$myValue);
				$whereField			= $thisArray[0];
				$whereOperator		= $thisArray[1];
				$whereValue			= $thisArray[2];
				$whereFormat		= $thisArray[3];
				if ($needAnd) {
					$thisWhere		.= " and ";
				} else {
					$needAnd		= TRUE;
				}
				if ($whereFormat == 's') {
					$thisWhere			.= "$whereField $whereOperator '$whereValue'";
				} else {
					$thisWhere			.= "$whereField $whereOperator $whereValue";
				}
			}
		}
		if ($doDebug) {
			echo "Here are the various segments<br />
UPDATE $inp_table $updateParams $thisWhere<br />";
		}   
		$theSQL			= "update $inp_table $updateParams $thisWhere";
		$countSQL		= "select count(date_created) as recordcount from $inp_table $thisWhere";
		$countResult	= $wpdb->get_var($countSQL);
		if ($countResult === FALSE) {
			$content	.= "Running $countSQL failed<br />
							wpdb->last_query: " . $wpdb->last_query . "<br />
							wpdb->last_error: " . $wpdb->last_error . "<br />";
		} else {
			$passSQL	= base64_encode($theSQL);
			$content	.= "SQL $theSQL<br />Will affect $countResult records<br />
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='3'>
							<input type='hidden' name='theSQL' value='$passSQL'>
							<input class='formInputButton' type='submit' value='Run the Query' />
							</form></p>";
		}

	} elseif ("3" == $strPass) {
		
		$theSQL		= base64_decode($theSQL);
		$content	.= "<h3>Execution</h3>
						<p>Running the statement: $theSQL<br />";
	
		$result		= $wpdb->query($theSQL);	
		if ($result === FALSE) {
			$content	.= "Executing the update statement returned FALSE<br />
							wpdb->last_query: " . $wpdb->last_query . "<br />
							wpdb->last_error: " . $wpdb->last_error . "<br />";
		} else {
			$content	.= "Executing the update statement was successful<br />
							wpdb->last_query: " . $wpdb->last_query . "<br />
							wpdb->last_error: " . $wpdb->last_error . "<br />";

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
	$result			= write_joblog_func("Generic Updater|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('generic_updater', 'generic_updater_func');
