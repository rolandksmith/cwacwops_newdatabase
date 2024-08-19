function advisorclass_report_generator_func() {

/*

	Modified 15Apr23 by Roland to correct action log handling
	Modified 12Jul23 by Roland to use consolidated tables
*/

global $wpdb;

$doDebug = FALSE;
$testMode = FALSE;
$initializationArray = data_initialization_func();
if ($doDebug) {
echo "Initialization Array:<br /><pre>";
print_r($initializationArray);
echo "</pre><br />";
}
$validUser = $initializationArray['validUser'];
$userName  = $initializationArray['userName'];
$siteURL   = $initializationArray['siteurl'];
	
if ($validUser == 'N') {
	return "YOU'RE NOT AUTHORIZED!<br />Goodby";
}

ini_set('display_errors','1');
error_reporting(E_ALL);	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

$strPass					= '1';
$myCount					= 0;
$theURL						= "$siteURL/cwa-advisorclass-report-generator/";
// Initialization
	$advisorclass_id = '';
	$post_title = '';
	$advisor_call_sign = '';
	$advisor_id = '';
	$advisor_first_name = '';
	$advisor_last_name = '';
	$sequence = '';
	$semester = '';
	$time_zone = '';
	$timezone_id = '';
	$timezone_offset = '';
	$level = '';
	$class_size = '';
	$class_schedule_days = '';
	$class_schedule_times = '';
	$class_schedule_days_utc = '';
	$class_schedule_times_utc = '';
	$action_log = '';
	$class_incomplete = '';
	$date_created				= '';
	$date_updated				= '';
	$mod_type					= '';

// get the input information
if (isset($_REQUEST)) {
foreach($_REQUEST as $str_key => $str_value) {
if ($doDebug) {
	echo "Key: $str_key | Value: $str_value <br />";
}
if($str_key== "inp_debug") {
	$inp_debug = $str_value;
$inp_debug = filter_var($inp_debug,FILTER_UNSAFE_RAW);
if ($inp_debug == 'Y') {
$doDebug = TRUE;
}
}
if ($str_key == "strpass") {
$strPass = $str_value;
$strPass = filter_var($strPass,FILTER_UNSAFE_RAW);
}

// Pass Ins
if($str_key == "advisorclass_id") {
$advisorclass_id = 'X';
if($doDebug) {
echo "set advisorclass_id to X<br />";
}
}
if($str_key == "advisor_call_sign") {
$advisor_call_sign = 'X';
if($doDebug) {
echo "set advisor_call_sign to X<br />";
}
}
if($str_key == "advisor_id") {
$advisor_id = 'X';
if($doDebug) {
echo "set advisor_id to X<br />";
}
}
if($str_key == "advisor_first_name") {
$advisor_first_name = 'X';
if($doDebug) {
echo "set advisor_first_name to X<br />";
}
}
if($str_key == "advisor_last_name") {
$advisor_last_name = 'X';
if($doDebug) {
echo "set advisor_last_name to X<br />";
}
}
if($str_key == "sequence") {
$sequence = 'X';
if($doDebug) {
echo "set sequence to X<br />";
}
}
if($str_key == "semester") {
$semester = 'X';
if($doDebug) {
echo "set semester to X<br />";
}
}
if($str_key == "time_zone") {
$time_zone = 'X';
if($doDebug) {
echo "set time_zone to X<br />";
}
}
if($str_key == "timezone_id") {
$timezone_id = 'X';
if($doDebug) {
echo "set timezone_id to X<br />";
}
}
if($str_key == "timezone_offset") {
$timezone_offset = 'X';
if($doDebug) {
echo "set timezone_offset to X<br />";
}
}
if($str_key == "level") {
$level = 'X';
if($doDebug) {
echo "set level to X<br />";
}
}
if($str_key == "class_size") {
$class_size = 'X';
if($doDebug) {
echo "set class_size to X<br />";
}
}
if($str_key == "class_schedule_days") {
$class_schedule_days = 'X';
if($doDebug) {
echo "set class_schedule_days to X<br />";
}
}
if($str_key == "class_schedule_times") {
$class_schedule_times = 'X';
if($doDebug) {
echo "set class_schedule_times to X<br />";
}
}
if($str_key == "class_schedule_days_utc") {
$class_schedule_days_utc = 'X';
if($doDebug) {
echo "set class_schedule_days_utc to X<br />";
}
}
if($str_key == "class_schedule_times_utc") {
$class_schedule_times_utc = 'X';
if($doDebug) {
echo "set class_schedule_times_utc to X<br />";
}
}
if($str_key == "action_log") {
$action_log = 'X';
if($doDebug) {
echo "set action_log to X<br />";
}
}
if($str_key == "class_incomplete") {
	$class_incomplete = 'X';
	if($doDebug) {
		echo "set class_incomplete to X<br />";
	}
}
if($str_key					== "date_created") {
	$date_created					= 'X';		
	if($doDebug) {
		echo "set class_incomplete to X<br />";
	}
}
if($str_key					== "date_updated") {
	$date_updated					= 'X';		
	if($doDebug) {
		echo "set class_incomplete to X<br />";
	}
}


if($str_key == "where") {
$where = $str_value;
// $where = filter_var($where,FILTER_UNSAFE_RAW);
$where= str_replace("&#39;","'",$where);
$where= stripslashes($where);
}
if($str_key == "orderby") {
$orderby= $str_value;
$orderby = filter_var($orderby,FILTER_UNSAFE_RAW);
$orderby= stripslashes($orderby);
}
if($str_key == "output_type") {
$output_type = $str_value;
$output_type = filter_var($output_type,FILTER_UNSAFE_RAW);
}
if($str_key == "mode_type") {
$mode_type = $str_value;
$mode_type = filter_var($mode_type,FILTER_UNSAFE_RAW);
}
if($str_key== "inp_report") {
$inp_report = $str_value;
$inp_report = filter_var($inp_report,FILTER_UNSAFE_RAW);
}
}
}

// Input
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
			echo "<b>Function starting.</b><br />";
		}
$content .= "<h3>Generate Report</h3>
<p><form method='post' action='$theURL'
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<p>Select the fields to be on the report:
<table>
<tr><th>Report Field</th><th>Pod Name</th></tr>
<tr><td><input type='checkbox' name='advisorclass_id' value='advisorclass_id'> AdvisorClass_ID</td><td>advisorclass_id</td></tr>
<tr><td><input type='checkbox' name='advisor_call_sign' value='advisor_call_sign'> Call Sign</td><td>advisor_call_sign</td></tr>
<tr><td><input type='checkbox' name='advisor_id' value='advisor_id'> Advisor ID</td><td>advisor_id</td></tr>
<tr><td><input type='checkbox' name='advisor_first_name' value='advisor_first_name'> First Name</td><td>advisor_first_name</td></tr>
<tr><td><input type='checkbox' name='advisor_last_name' value='advisor_last_name'> Last Name</td><td>advisor_last_name</td></tr>
<tr><td><input type='checkbox' name='sequence' value='sequence'> Sequence</td><td>sequence</td></tr>
<tr><td><input type='checkbox' name='semester' value='semester'> Semester</td><td>semester</td></tr>
<tr><td><input type='checkbox' name='time_zone' value='time_zone'> Time Zone</td><td>time_zone</td></tr>
<tr><td><input type='checkbox' name='timezone_id' value='timezone_id'> Timezone ID</td><td>timezone_id</td></tr>
<tr><td><input type='checkbox' name='timezone_offset' value='timezone_offset'> Timezone Offset</td><td>timezone_offset</td></tr>
<tr><td><input type='checkbox' name='level' value='level'> Level</td><td>level</td></tr>
<tr><td><input type='checkbox' name='class_size' value='class_size'> Class Size</td><td>class_size</td></tr>
<tr><td><input type='checkbox' name='class_schedule_days' value='class_schedule_days'> Teaching Days</td><td>class_schedule_days</td></tr>
<tr><td><input type='checkbox' name='class_schedule_times' value='class_schedule_times'> Teaching Time</td><td>class_schedule_times</td></tr>
<tr><td><input type='checkbox' name='class_schedule_days_utc' value='class_schedule_days_utc'> UTC Teaching Days</td><td>class_schedule_days_utc</td></tr>
<tr><td><input type='checkbox' name='class_schedule_times_utc' value='class_schedule_times_utc'> UTC Teaching Time</td><td>class_schedule_times_utc</td></tr>
<tr><td><input type='checkbox' name='action_log' value='action_log'> Action Log</td><td>action_log</td></tr>
<tr><td><input type='checkbox' name='class_incomplete' value='class_incomplete'> Class Incomplete</td><td>class_incomplete</td></tr>
<tr><td><input type='checkbox' id='date_created' name='date_created' value='date_created'> Date Created</td><td>date_created</td></tr>
<tr><td><input type='checkbox' id='date_updated' name='date_updated' value='date_updated'> Date Updated</td><td>date_updated</td></tr>
</table>
</p><p>Select Output Format<br />
<input type='radio' id='table' name='output_type' value='table' checked='checked'> Table Report<br />
<input type='radio' id='comma' name='output_type' value='comma'> Comma Separated Report<br /></p>
<p>Which Table to Read<br />
<input type='radio' name='mode_type' value='wpw1_cwa_consolidated_advisorclass' checked> advisorClass<br />
<input type='radio' name='mode_type' value='wpw1_cwa_consolidated__advisorclass2'> advisorClass2<br />
</p><p>Enter the 'Where' clause:<br />
<textarea class='formInputText' id='where' name='where' rows='5' cols='80'></textarea><br />
</p><p>Enter the 'Orderby' clause:<br />
<textarea class='formInputText' id='orderby' name='orderby' rows='5' cols='80'>advisor_call_sign</textarea><br /></p>
<p>Verbose Debugging?<br />
<input type='radio' id='inp_debug' name='inp_debug' value='N' checked='checked'> Debugging off<br />
<input type='radio' id='inp_debug' name='inp_debug' value='Y' > Turn Debugging on<br /></p>
<p>Save Report to Reports Table? <br />
<input type='radio' id='inp_report' name='inp_report' value='N' checked='checked'> Do not save<br />
<input type='radio' id='inp_report' name='inp_report' value='Y' > Save the report<br /></p>
<p><input class='formInputButton' type='submit' value='Submit' />
</form></p>";

} elseif ("2" == $strPass) {

	if ($doDebug) {
		echo "At pass 2. Reading from $mode_type table<br />";
	}

// Array to convert database name to display name
$nameConversionArray = array();
$nameConversionArray['advisorclass_id'] = 'AdvisorClass ID';
$nameConversionArray['advisor_call_sign'] = 'Call Sign';
$nameConversionArray['advisor_id'] = 'Advisor ID';
$nameConversionArray['advisor_first_name'] = 'First Name';
$nameConversionArray['advisor_last_name'] = 'Last Name';
$nameConversionArray['sequence'] = 'Sequence';
$nameConversionArray['semester'] = 'Semester';
$nameConversionArray['time_zone'] = 'Time Zone';
$nameConversionArray['timezone_id'] = 'Timezone ID';
$nameConversionArray['timezone_offset'] = 'Timezone Offset';
$nameConversionArray['level'] = 'Level';
$nameConversionArray['class_size'] = 'Class Size';
$nameConversionArray['class_schedule_days'] = 'Teaching Days';
$nameConversionArray['class_schedule_times'] = 'Teaching Time';
$nameConversionArray['class_schedule_days_utc'] = 'UTC Teaching Days';
$nameConversionArray['class_schedule_times_utc'] = 'UTC Teaching Time';
$nameConversionArray['action_log'] = 'Action Log';
$nameConversionArray['class_incomplete'] = 'Class Incomplete';
$nameConversionArray['class_verified'] = 'Class Verified';
$nameConversionArray['date_created'] = 'Date Created';
$nameConversionArray['date_updated'] = 'Date Updated';


// Begin the Report Output

$content .= "<h2>Generated Report from the $mode_type Table</h2>
<p>Parameters:<br />
Where: $where<br />Ordered By: $orderby<br />Debugging: $inp_debug<br />Save report: $inp_report<br />Table name: $mode_type<br />";
		$sql = "select * from $mode_type";
		if ($where != '') {
			$sql	= "$sql where $where ";
		}
		if ($orderby != '') {
			$sql	= "$sql order by $orderby";
		}
		$content .= "SQL: $sql</p>";
// return $content;

if ($output_type == 'table') {
$content .= "<table><tr>";

if ($advisorclass_id == 'X') {
$headerName = $nameConversionArray['advisorclass_id'];
$content .= "<th>$headerName</th>";
}

if ($advisor_call_sign == 'X') {
$headerName = $nameConversionArray['advisor_call_sign'];
$content .= "<th>$headerName</th>";
}

if ($advisor_id == 'X') {
$headerName = $nameConversionArray['advisor_id'];
$content .= "<th>$headerName</th>";
}

if ($advisor_first_name == 'X') {
$headerName = $nameConversionArray['advisor_first_name'];
$content .= "<th>$headerName</th>";
}

if ($advisor_last_name == 'X') {
$headerName = $nameConversionArray['advisor_last_name'];
$content .= "<th>$headerName</th>";
}

if ($sequence == 'X') {
$headerName = $nameConversionArray['sequence'];
$content .= "<th>$headerName</th>";
}

if ($semester == 'X') {
$headerName = $nameConversionArray['semester'];
$content .= "<th>$headerName</th>";
}

if ($time_zone == 'X') {
$headerName = $nameConversionArray['time_zone'];
$content .= "<th>$headerName</th>";
}

if ($timezone_id == 'X') {
$headerName = $nameConversionArray['timezone_id'];
$content .= "<th>$headerName</th>";
}

if ($timezone_offset == 'X') {
$headerName = $nameConversionArray['timezone_offset'];
$content .= "<th>$headerName</th>";
}

if ($level == 'X') {
$headerName = $nameConversionArray['level'];
$content .= "<th>$headerName</th>";
}

if ($class_size == 'X') {
$headerName = $nameConversionArray['class_size'];
$content .= "<th>$headerName</th>";
}

if ($class_schedule_days == 'X') {
$headerName = $nameConversionArray['class_schedule_days'];
$content .= "<th>$headerName</th>";
}

if ($class_schedule_times == 'X') {
$headerName = $nameConversionArray['class_schedule_times'];
$content .= "<th>$headerName</th>";
}

if ($class_schedule_days_utc == 'X') {
$headerName = $nameConversionArray['class_schedule_days_utc'];
$content .= "<th>$headerName</th>";
}

if ($class_schedule_times_utc == 'X') {
$headerName = $nameConversionArray['class_schedule_times_utc'];
$content .= "<th>$headerName</th>";
}

if ($action_log == 'X') {
$headerName = $nameConversionArray['action_log'];
$content .= "<th>$headerName</th>";
}

if ($class_incomplete == 'X') {
$headerName = $nameConversionArray['class_incomplete'];
$content .= "<th>$headerName</th>";
}
			if ($date_created == 'X') {
				$headerName = $nameConversionArray['date_created'];
				$content .= "<th>$headerName</th>";
			}
			if ($date_updated == 'X') {
				$headerName = $nameConversionArray['date_updated'];
				$content .= "<th>$headerName</th>";
			}
$content	.= "</tr>";	
} else {
$needComma = FALSE;
if ($advisorclass_id == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'advisorclass_id'";
$needComma = TRUE;
}
if ($advisor_call_sign == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'advisor_call_sign'";
$needComma = TRUE;
}
if ($advisor_id == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'advisor_id'";
$needComma = TRUE;
}
if ($advisor_first_name == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'advisor_first_name'";
$needComma = TRUE;
}
if ($advisor_last_name == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'advisor_last_name'";
$needComma = TRUE;
}
if ($sequence == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'sequence'";
$needComma = TRUE;
}
if ($semester == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'semester'";
$needComma = TRUE;
}
if ($time_zone == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'time_zone'";
$needComma = TRUE;
}
if ($timezone_id == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'timezone_id'";
$needComma = TRUE;
}
if ($timezone_offset == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'timezone_offset'";
$needComma = TRUE;
}
if ($level == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'level'";
$needComma = TRUE;
}
if ($class_size == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'class_size'";
$needComma = TRUE;
}
if ($class_schedule_days == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'class_schedule_days'";
$needComma = TRUE;
}
if ($class_schedule_times == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'class_schedule_times'";
$needComma = TRUE;
}
if ($class_schedule_days_utc == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'class_schedule_days_utc'";
$needComma = TRUE;
}
if ($class_schedule_times_utc == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'class_schedule_times_utc'";
$needComma = TRUE;
}
if ($action_log == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'action_log'";
$needComma = TRUE;
}
if ($class_incomplete == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'class_incomplete'";
$needComma = TRUE;
}
			if ($date_created == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "date_created";
				$needComma = TRUE;
			}
			if ($date_updated == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "date_updated";
				$needComma = TRUE;
			}
$content .= "<br />";
}
		if ($doDebug) {
			echo "Ready to read from $mode_type table. SQL: $sql<br />";
		}

		$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
		if (FALSE === $wpw1_cwa_advisorclass) {
			if ($doDebug) {
				echo "<b>Error</b> Reading $mode_type table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
			}
		} else {
			$numARows									= $wpdb->num_rows;
			if ($doDebug) {
				$mySQL		= $wpdb->last_query;
				echo "Retrieved $numARows from $mod_type table: $mySQL<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
				
//					if ($doDebug) {
//						echo "<br /><pre>";
//						var_dump($advisorClassRow);
//						echo "</pre><br />";
//					}
				
					$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
					$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
					$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
					$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
					$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
					$advisorClass_sequence 					= $advisorClassRow->sequence;
					$advisorClass_semester 					= $advisorClassRow->semester;
					$advisorClass_timezone 					= $advisorClassRow->time_zone;
					$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
					$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
					$advisorClass_level 					= $advisorClassRow->level;
					$advisorClass_class_size 				= $advisorClassRow->class_size;
					$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
					$advisorClass_class_schedule_times 		= $advisorClassRow->class_schedule_times;
					$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
					$advisorClass_class_schedule_times_utc 	= $advisorClassRow->class_schedule_times_utc;
					$advisorClass_action_log 				= $advisorClassRow->action_log;
					$advisorClass_class_incomplete 			= $advisorClassRow->class_incomplete;
					$advisorClass_date_created				= $advisorClassRow->date_created;
					$advisorClass_date_updated				= $advisorClassRow->date_updated;


					if ($doDebug) {
						echo "Processing record $advisorClass_advisor_call_sign<br />";
					}
					$myCount++;
					if ($output_type == 'table') {
						$content	.= "<tr>";
						if ($advisorclass_id 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_ID</td>";
						}
						if ($advisor_call_sign 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_advisor_call_sign</td>";
						}
						if ($advisor_id 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_advisor_id</td>";
						}
						if ($advisor_first_name 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_advisor_first_name</td>";
						}
						if ($advisor_last_name 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_advisor_last_name</td>";
						}
						if ($sequence 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_sequence	</td>";
						}
						if ($semester 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_semester</td>";
						}
						if ($time_zone 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_timezone</td>";
						}
						if ($timezone_id 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_timezone</td>";
						}
						if ($timezone_offset 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_timezone</td>";
						}
						if ($level 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_level</td>";
						}
						if ($class_size 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_class_size</td>";
						}
						if ($class_schedule_days 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_class_schedule_days</td>";
						}
						if ($class_schedule_times 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_class_schedule_times</td>";
						}
						if ($class_schedule_days_utc 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_class_schedule_days_utc</td>";
						}
						if ($class_schedule_times_utc 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_class_schedule_times_utc</td>";
						}
						if ($action_log 	== 'X') {
							$newActionLog							= formatActionLog($advisorClass_action_log);
							$content .= "<td style='vertical-align:top;'>$newActionLog</td>";
						}
						if ($class_incomplete 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_class_incomplete</td>";
						}
						if ($date_created == 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_date_created</td>";
						}
						if ($date_updated == 'X') {
							$content .= "<td style='vertical-align:top;'>$advisorClass_date_updated</td>";
						}
						$content .= "</tr>";
					} else { // output will be a comma separated file
						$needComma = FALSE;
						if ($advisorclass_id == 'X') {
							if ($needComma) {
								$content .= ',';
							}
							$content .= "'$advisorClass_ID'";
							$needComma = TRUE;
						}
						if ($advisor_call_sign == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_advisor_call_sign'";
							$needComma = TRUE;
						}
						if ($advisor_id == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_advisor_id'";
							$needComma = TRUE;
						}
						if ($advisor_first_name == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_advisor_first_name'";
							$needComma = TRUE;
						}
						if ($advisor_last_name == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_advisor_last_name'";
							$needComma = TRUE;
						}
						if ($sequence == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_sequence	'";
							$needComma = TRUE;
						}
						if ($semester == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_semester'";
							$needComma = TRUE;
						}
						if ($time_zone == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_timezone'";
							$needComma = TRUE;
						}
						if ($timezone_id == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_timezone'";
							$needComma = TRUE;
						}
						if ($timezone_offset == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_timezone'";
							$needComma = TRUE;
						}
						if ($level == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_level'";
							$needComma = TRUE;
						}
						if ($class_size == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_class_size'";
							$needComma = TRUE;
						}
						if ($class_schedule_days == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_class_schedule_days'";
							$needComma = TRUE;
						}
						if ($class_schedule_times == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_class_schedule_times'";
							$needComma = TRUE;
						}
						if ($class_schedule_days_utc == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_class_schedule_days_utc'";
							$needComma = TRUE;
						}
						if ($class_schedule_times_utc == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_class_schedule_times_utc'";
							$needComma = TRUE;
						}
						if ($action_log == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_action_log'";
							$needComma = TRUE;
						}
						if ($class_incomplete == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_class_incomplete'";
							$needComma = TRUE;
						}
						if ($date_created == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_date_created'";
							$needComma = TRUE;
						}
						if ($date_updated == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "'$advisorClass_date_updated'";
							$needComma = TRUE;
						}
						$content	.= "<br />";
					}
				}
				if ($output_type == 'table') {
					$content .= "</table>";
				}
				$content .= "<br /><br />$myCount records printed<br />";
			} else {
				$content .= "No records found matching the criteria";
			}
		}

	$thisTime = date('Y-m-d H:i:s');
	$content .= "<br /><br /><p>Prepared at $thisTime</p>";
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
	$result			= write_joblog_func("AdvisorClass Report Generator|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	if ($doDebug) {
		echo "<br />Testing to save report: $inp_report<br />";
	}
	if ($inp_report == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as $reportName Report Generator<br />";
		}
		$storeResult = storeReportData_func("Advisor Class Report Generator",$content);
		if ($storeResult !== FALSE) {
			$content .= "<br />Report stored in reports pod as $storeResult";
		} else {
			$content .= "<br />Storing the report in the reports pod failed";
		}
	}
	}

return $content;

}
add_shortcode ('advisorclass_report_generator', 'advisorclass_report_generator_func');
