function advisor_report_generator_func() {

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
$validUser 		= $initializationArray['validUser'];
$userName  		= $initializationArray['userName'];
$siteURL   		= $initializationArray['siteurl'];
$versionNumber	= '2';
$jobname		= "Advisor Report Generator V$versionNumber";
	
if ($validUser == 'N') {
	return "YOU'RE NOT AUTHORIZED!<br />Goodby";
}
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

ini_set('display_errors','1');
error_reporting(E_ALL);	

$strPass					= '1';
$myCount					= 0;
$theURL						= "$siteURL/cwa-advisor-report-generator/";
// Initialization
	$advisor_id = '';
	$select_sequence = '';
	$call_sign = '';
	$first_name = '';
	$last_name = '';
	$email = '';
	$phone = '';
	$text_message = '';
	$city = '';
	$state = '';
	$zip_code = '';
	$country = '';
	$time_zone = '';
	$semester = '';
	$survey_score = '';
	$languages = '';
	$fifo_date = '';
	$welcome_email_date = '';
	$verify_email_date = '';
	$verify_email_number = '';
	$verify_response = '';
	$action_log = '';
	$class_verified = '';
	$ph_code = '';
	$country_code = '';
	$timezone_id = '';
	$timezone_offset = '';
	$whatsapp = '';
	$signal = '';
	$telegram = '';
	$messenger = '';
	$date_created				= '';
	$date_updated				= '';
	$replacement_status			= '';

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
			if($str_key == "advisor_id") {
				$advisor_id = 'X';
				if($doDebug) {
					echo "set advisor_id to X<br />";
				}
			}
			if($str_key == "select_sequence") {
				$select_sequence = 'X';
				if($doDebug) {
					echo "set select_sequence to X<br />";
				}
			}
			if($str_key == "call_sign") {
				$call_sign = 'X';
				if($doDebug) {
					echo "set call_sign to X<br />";
				}
			}
			if($str_key == "first_name") {
				$first_name = 'X';
				if($doDebug) {
					echo "set first_name to X<br />";
				}
			}
			if($str_key == "last_name") {
				$last_name = 'X';
				if($doDebug) {
					echo "set last_name to X<br />";
				}
			}
			if($str_key == "email") {
			$email = 'X';
			if($doDebug) {
			echo "set email to X<br />";
			}
			}
			if($str_key == "phone") {
			$phone = 'X';
			if($doDebug) {
			echo "set phone to X<br />";
			}
			}
			if($str_key == "text_message") {
			$text_message = 'X';
			if($doDebug) {
			echo "set text_message to X<br />";
			}
			}
			if($str_key == "city") {
			$city = 'X';
			if($doDebug) {
			echo "set city to X<br />";
			}
			}
			if($str_key == "state") {
			$state = 'X';
			if($doDebug) {
			echo "set state to X<br />";
			}
			}
			if($str_key == "zip_code") {
			$zip_code = 'X';
			if($doDebug) {
			echo "set zip_code to X<br />";
			}
			}
			if($str_key == "country") {
			$country = 'X';
			if($doDebug) {
			echo "set country to X<br />";
			}
			}
			if($str_key == "time_zone") {
			$time_zone = 'X';
			if($doDebug) {
			echo "set time_zone to X<br />";
			}
			}
			if($str_key == "semester") {
			$semester = 'X';
			if($doDebug) {
			echo "set semester to X<br />";
			}
			}
			if($str_key == "survey_score") {
			$survey_score = 'X';
			if($doDebug) {
			echo "set survey_score to X<br />";
			}
			}
			if($str_key == "languages") {
			$languages = 'X';
			if($doDebug) {
			echo "set languages to X<br />";
			}
			}
			if($str_key == "fifo_date") {
			$fifo_date = 'X';
			if($doDebug) {
			echo "set fifo_date to X<br />";
			}
			}
			if($str_key == "welcome_email_date") {
			$welcome_email_date = 'X';
			if($doDebug) {
			echo "set welcome_email_date to X<br />";
			}
			}
			if($str_key == "verify_email_date") {
			$verify_email_date = 'X';
			if($doDebug) {
			echo "set verify_email_date to X<br />";
			}
			}
			if($str_key == "verify_email_number") {
			$verify_email_number = 'X';
			if($doDebug) {
			echo "set verify_email_number to X<br />";
			}
			}
			if($str_key == "verify_response") {
			$verify_response = 'X';
			if($doDebug) {
			echo "set verify_response to X<br />";
			}
			}
			if($str_key == "ph_code") {
			$ph_code = 'X';
			if($doDebug) {
			echo "set ph_code to X<br />";
			}
			}
			if($str_key == "country_code") {
			$country_code = 'X';
			if($doDebug) {
			echo "set country_code to X<br />";
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
			if($str_key == "whatsapp") {
			$whatsapp = 'X';
			if($doDebug) {
			echo "set whatsapp to X<br />";
			}
			}
			if($str_key == "signal") {
			$signal = 'X';
			if($doDebug) {
			echo "set signal to X<br />";
			}
			}
			if($str_key == "telegram") {
			$telegram = 'X';
			if($doDebug) {
			echo "set telegram to X<br />";
			}
			}
			if($str_key == "messenger") {
			$messenger = 'X';
			if($doDebug) {
			echo "set messenger to X<br />";
			}
			}
			if($str_key == "action_log") {
			$action_log = 'X';
			if($doDebug) {
			echo "set action_log to X<br />";
			}
			}
			if($str_key == "class_verified") {
				$class_verified = 'X';
				if($doDebug) {
					echo "set class_verified to X<br />";
				}
			}
			if($str_key					== "date_created") {
				$date_created					= 'X';	
				if ($doDebug) {
					echo "set date_created to X<br />";
				}	
			}
			if($str_key					== "date_updated") {
				$date_updated					= 'X';		
				if ($doDebug) {
					echo "set date_updated to X<br />";
				}	
			}
			if($str_key					== "replacement_status") {
				$replacement_status					= 'X';		
				if ($doDebug) {
					echo "set replacement_status to X<br />";
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
			echo "Function starting.<br />";
		}
$content .= "<h3>$jobname</h3>
<p><form method='post' action='$theURL'
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<p>Select the fields to be on the report:
<table>
<tr><th>Report Field</th><th>Table Name</th></tr>
<tr><td><input type='checkbox' name='advisor_id' value='advisor_id'> Advisor ID</td><td>advisor_id</td></tr>
<tr><td><input type='checkbox' name='select_sequence' value='select_sequence'> Select Sequence</td><td>select_sequence</td></tr>
<tr><td><input type='checkbox' name='call_sign' value='call_sign'> Call Sign</td><td>call_sign</td></tr>
<tr><td><input type='checkbox' name='first_name' value='first_name'> First Name</td><td>first_name</td></tr>
<tr><td><input type='checkbox' name='last_name' value='last_name'> Last Name</td><td>last_name</td></tr>
<tr><td><input type='checkbox' name='email' value='email'> Email</td><td>email</td></tr>
<tr><td><input type='checkbox' name='ph_code' value='ph_code'> Phone Code</td><td>ph_code</td></tr>
<tr><td><input type='checkbox' name='phone' value='phone'> Phone</td><td>phone</td></tr>
<tr><td><input type='checkbox' name='text_message' value='text_message'> Text Msg</td><td>text_message</td></tr>
<tr><td><input type='checkbox' name='city' value='city'> City</td><td>city</td></tr>
<tr><td><input type='checkbox' name='state' value='state'> State</td><td>state</td></tr>
<tr><td><input type='checkbox' name='zip_code' value='zip_code'> Zip Code</td><td>zip_code</td></tr>
<tr><td><input type='checkbox' name='country' value='country'> Country</td><td>country</td></tr>
<tr><td><input type='checkbox' name='country_code' value='country_code'> Country Code</td><td>country_code</td></tr>
<tr><td><input type='checkbox' name='time_zone' value='time_zone'> Time Zone</td><td>time_zone</td></tr>
<tr><td><input type='checkbox' name='timezone_id' value='timezone_id'> Timezone ID</td><td>timezone_id</td></tr>
<tr><td><input type='checkbox' name='timezone_offset' value='timezone_offset'> Timezone Offset</td><td>timezone_offset</td></tr>
<tr><td><input type='checkbox' name='whatsapp' value='whatsapp'> Whatsapp</td><td>whatsapp</td></tr>
<tr><td><input type='checkbox' name='signal' value='signal'> Signal</td><td>signal</td></tr>
<tr><td><input type='checkbox' name='telegram' value='telegram'> Telegram</td><td>telegram</td></tr>
<tr><td><input type='checkbox' name='messenger' value='messenger'> Messenger</td><td>messenger</td></tr>
<tr><td><input type='checkbox' name='semester' value='semester'> Semester</td><td>semester</td></tr>
<tr><td><input type='checkbox' name='survey_score' value='survey_score'> Survey Score</td><td>survey_score</td></tr>
<tr><td><input type='checkbox' name='languages' value='languages'> Languages</td><td>languages</td></tr>
<tr><td><input type='checkbox' name='fifo_date' value='fifo_date'> FIFO Date</td><td>fifo_date</td></tr>
<tr><td><input type='checkbox' name='welcome_email_date' value='welcome_email_date'> Welcome Date</td><td>welcome_email_date</td></tr>
<tr><td><input type='checkbox' name='verify_email_date' value='verify_email_date'> Verify Email Date</td><td>verify_email_date</td></tr>
<tr><td><input type='checkbox' name='verify_email_number' value='verify_email_number'> Verify Email Number</td><td>verify_email_number</td></tr>
<tr><td><input type='checkbox' name='verify_response' value='verify_response'> Verify Response</td><td>verify_response</td></tr>
<tr><td><input type='checkbox' name='action_log' value='action_log'> Action Log</td><td>action_log</td></tr>
<tr><td><input type='checkbox' name='class_verified' value='class_verified'> Class Verified</td><td>class_verified</td></tr>
<tr><td><input type='checkbox' name='replacement_status' value='replacement_status'> Replacement Status</td><td>replacement_status</td></tr>
<tr><td><input type='checkbox' id='date_created' name='date_created' value='date_created'> Date Created</td><td>date_created</td></tr>
<tr><td><input type='checkbox' id='date_updated' name='date_updated' value='date_updated'> Date Updated</td><td>date_updated</td></tr>
</table>
</p><p>Select Output Format<br />
<input type='radio' id='table' name='output_type' value='table' checked='checked'> Table Report<br />
<input type='radio' id='comma' name='output_type' value='comma'> Comma Separated Report<br /></p>
<p>Which Table to Read<br />
<input type='radio' name='mode_type' value='wpw1_cwa_consolidated_advisor' checked='checked'> advisor Table<br />
<input type='radio' name='mode_type' value='wpw1_cwa_consolidated_advisor2'> advisor2 Table<br />
</p><p>Enter the 'Where' clause:<br />
<textarea class='formInputText' id='where' name='where' rows='5' cols='80'></textarea><br />
</p><p>Enter the 'Orderby' clause:<br />
<textarea class='formInputText' id='orderby' name='orderby' rows='5' cols='80'>call_sign</textarea><br /></p>
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
	if ($date_created == 'X') {
		echo "Date Created still set<br />";
	}
	if ($date_updated == 'X') {
		echo "Date Updated still set<br />";
	}
}

// Array to convert database name to display name
$nameConversionArray = array();
$nameConversionArray['advisor_id'] = 'Advisor ID';
$nameConversionArray['select_sequence'] = 'Select Sequence';
$nameConversionArray['call_sign'] = 'Call Sign';
$nameConversionArray['first_name'] = 'First Name';
$nameConversionArray['last_name'] = 'Last Name';
$nameConversionArray['email'] = 'Email';
$nameConversionArray['phone'] = 'Phone';
$nameConversionArray['text_message'] = 'Text Msg';
$nameConversionArray['city'] = 'City';
$nameConversionArray['state'] = 'State';
$nameConversionArray['zip_code'] = 'Zip Code';
$nameConversionArray['country'] = 'Country';
$nameConversionArray['time_zone'] = 'Time Zone';
$nameConversionArray['semester'] = 'Semester';
$nameConversionArray['survey_score'] = 'Survey Score';
$nameConversionArray['languages'] = 'Languages';
$nameConversionArray['fifo_date'] = 'FIFO Date';
$nameConversionArray['welcome_email_date'] = 'Welcome Date';
$nameConversionArray['verify_email_date'] = 'Verify Email Date';
$nameConversionArray['verify_email_number'] = 'Verify Email Number';
$nameConversionArray['verify_response'] = 'Verify Response';
$nameConversionArray['action_log'] = 'Action Log';
$nameConversionArray['class_verified'] = 'Class Verified';
$nameConversionArray['ph_code'] = 'Phone Code';
$nameConversionArray['country_code'] = 'Country Code';
$nameConversionArray['timezone_id'] = 'Timezone ID';
$nameConversionArray['timezone_offset'] = 'Timezone Offset';
$nameConversionArray['whatsapp'] = 'Whatsapp';
$nameConversionArray['signal'] = 'Signal';
$nameConversionArray['telegram'] = 'Telegram';
$nameConversionArray['messenger'] = 'Messenger';
$nameConversionArray['replacement_status'] = 'Replacement Status';
$nameConversionArray['date_created']					= 'Date Created';
$nameConversionArray['date_updated']					= 'Date Updated';


$inputPodName = $mode_type . 'Table';
// Begin the Report Output
$content .= "<h2>AdvisorNew Generated Report from the $mode_type Table</h2>
<p>Where: $where<br />Ordered By: $orderby<br />Debugging: $inp_debug<br />Save report: $inp_report<br />";
		$sql = "select * from $mode_type";
		if ($where != '') {
			$sql	= "$sql where $where ";
		}
		if ($orderby != '') {
			$sql	= "$sql order by $orderby";
		}
		$content		.= "SQL: $sql</p>";
if ($output_type == 'table') {
$content .= "<table><tr>";

if ($advisor_id == 'X') {
$headerName = $nameConversionArray['advisor_id'];
$content .= "<th>$headerName</th>";
}

if ($select_sequence == 'X') {
$headerName = $nameConversionArray['select_sequence'];
$content .= "<th>$headerName</th>";
}

if ($call_sign == 'X') {
$headerName = $nameConversionArray['call_sign'];
$content .= "<th>$headerName</th>";
}

if ($first_name == 'X') {
$headerName = $nameConversionArray['first_name'];
$content .= "<th>$headerName</th>";
}

if ($last_name == 'X') {
$headerName = $nameConversionArray['last_name'];
$content .= "<th>$headerName</th>";
}

if ($email == 'X') {
$headerName = $nameConversionArray['email'];
$content .= "<th>$headerName</th>";
}

if ($ph_code == 'X') {
$headerName = $nameConversionArray['ph_code'];
$content .= "<th>$headerName</th>";
}

if ($phone == 'X') {
$headerName = $nameConversionArray['phone'];
$content .= "<th>$headerName</th>";
}

if ($text_message == 'X') {
$headerName = $nameConversionArray['text_message'];
$content .= "<th>$headerName</th>";
}

if ($city == 'X') {
$headerName = $nameConversionArray['city'];
$content .= "<th>$headerName</th>";
}

if ($state == 'X') {
$headerName = $nameConversionArray['state'];
$content .= "<th>$headerName</th>";
}

if ($zip_code == 'X') {
$headerName = $nameConversionArray['zip_code'];
$content .= "<th>$headerName</th>";
}

if ($country == 'X') {
$headerName = $nameConversionArray['country'];
$content .= "<th>$headerName</th>";
}

if ($country_code == 'X') {
$headerName = $nameConversionArray['country_code'];
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

if ($whatsapp == 'X') {
$headerName = $nameConversionArray['whatsapp'];
$content .= "<th>$headerName</th>";
}

if ($signal == 'X') {
$headerName = $nameConversionArray['signal'];
$content .= "<th>$headerName</th>";
}

if ($telegram == 'X') {
$headerName = $nameConversionArray['telegram'];
$content .= "<th>$headerName</th>";
}

if ($messenger == 'X') {
$headerName = $nameConversionArray['messenger'];
$content .= "<th>$headerName</th>";
}

if ($semester == 'X') {
$headerName = $nameConversionArray['semester'];
$content .= "<th>$headerName</th>";
}

if ($survey_score == 'X') {
$headerName = $nameConversionArray['survey_score'];
$content .= "<th>$headerName</th>";
}

if ($languages == 'X') {
$headerName = $nameConversionArray['languages'];
$content .= "<th>$headerName</th>";
}

if ($fifo_date == 'X') {
$headerName = $nameConversionArray['fifo_date'];
$content .= "<th>$headerName</th>";
}

if ($welcome_email_date == 'X') {
$headerName = $nameConversionArray['welcome_email_date'];
$content .= "<th>$headerName</th>";
}

if ($verify_email_date == 'X') {
$headerName = $nameConversionArray['verify_email_date'];
$content .= "<th>$headerName</th>";
}

if ($verify_email_number == 'X') {
$headerName = $nameConversionArray['verify_email_number'];
$content .= "<th>$headerName</th>";
}

if ($verify_response == 'X') {
$headerName = $nameConversionArray['verify_response'];
$content .= "<th>$headerName</th>";
}

if ($action_log == 'X') {
$headerName = $nameConversionArray['action_log'];
$content .= "<th>$headerName</th>";
}

if ($class_verified == 'X') {
$headerName = $nameConversionArray['class_verified'];
$content .= "<th>$headerName</th>";
}
if ($replacement_status == 'X') {
$headerName = $nameConversionArray['replacement_status'];
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
if ($advisor_id == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$advisor_id'";
$needComma = TRUE;
}
if ($select_sequence == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$select_sequence'";
$needComma = TRUE;
}
if ($call_sign == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$call_sign'";
$needComma = TRUE;
}
if ($first_name == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$first_name'";
$needComma = TRUE;
}
if ($last_name == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$last_name'";
$needComma = TRUE;
}
if ($email == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$email'";
$needComma = TRUE;
}
if ($ph_code == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$ph_code'";
$needComma = TRUE;
}
if ($phone == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$phone'";
$needComma = TRUE;
}
if ($text_message == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$text_message'";
$needComma = TRUE;
}
if ($city == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$city'";
$needComma = TRUE;
}
if ($state == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$state'";
$needComma = TRUE;
}
if ($zip_code == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$zip_code'";
$needComma = TRUE;
}
if ($country == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$country'";
$needComma = TRUE;
}
if ($country_code == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$country_code'";
$needComma = TRUE;
}
if ($time_zone == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$time_zone'";
$needComma = TRUE;
}
if ($timezone_id == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$timezone_id'";
$needComma = TRUE;
}
if ($timezone_offset == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$timezone_offset'";
$needComma = TRUE;
}
if ($whatsapp == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$whatsapp'";
$needComma = TRUE;
}
if ($signal == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$signal'";
$needComma = TRUE;
}
if ($telegram == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$telegram'";
$needComma = TRUE;
}
if ($messenger == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$messenger'";
$needComma = TRUE;
}
if ($semester == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$semester'";
$needComma = TRUE;
}
if ($survey_score == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$survey_score'";
$needComma = TRUE;
}
if ($languages == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$languages'";
$needComma = TRUE;
}
if ($fifo_date == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$fifo_date'";
$needComma = TRUE;
}
if ($welcome_email_date == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$welcome_email_date'";
$needComma = TRUE;
}
if ($verify_email_date == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$verify_email_date'";
$needComma = TRUE;
}
if ($verify_email_number == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$verify_email_number'";
$needComma = TRUE;
}
if ($verify_response == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$verify_response'";
$needComma = TRUE;
}
if ($action_log == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$action_log'";
$needComma = TRUE;
}
if ($class_verified == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$class_verified'";
$needComma = TRUE;
}
if ($replacement_status == 'X') {
if ($needComma) {
$content .= '	';
}
$content .= "'$replacement_status'";
$needComma = TRUE;
}
			if ($date_created == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "'$date_created'";
				$needComma = TRUE;
			}
			if ($date_updated == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "'$date_updated'";
				$needComma = TRUE;
			}
			$content .= "<br />";
		}

		$wpw1_cwa_advisor				= $wpdb->get_results($sql);
		if ($doDebug) {
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
		}
		if (FALSE === $wpw1_cwa_advisor || $wpw1_cwa_advisor == NULL) {		// no record found
			if ($doDebug) {
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
			}
		} else {
			$numANRows										= $wpdb->num_rows;
			if ($numANRows > 0) {
				foreach ($wpw1_cwa_advisor as $advisorRow) {
					$advisor_ID							= $advisorRow->advisor_id;
					$advisor_select_sequence 			= $advisorRow->select_sequence;
					$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
					$advisor_first_name 				= $advisorRow->first_name;
					$advisor_last_name 					= stripslashes($advisorRow->last_name);
					$advisor_email 						= strtolower($advisorRow->email);
					$advisor_phone						= $advisorRow->phone;
					$advisor_ph_code					= $advisorRow->ph_code;				// new
					$advisor_text_message 				= $advisorRow->text_message;
					$advisor_city 						= $advisorRow->city;
					$advisor_state 						= $advisorRow->state;
					$advisor_zip_code 					= $advisorRow->zip_code;
					$advisor_country 					= $advisorRow->country;
					$advisor_country_code				= $advisorRow->country_code;		// new
					$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
					$advisor_signal						= $advisorRow->signal_app;			// new
					$advisor_telegram					= $advisorRow->telegram_app;		// new
					$advisor_messenger					= $advisorRow->messenger_app;		// new
					$advisor_time_zone 					= $advisorRow->time_zone;
					$advisor_timezone_id				= $advisorRow->timezone_id;			// new
					$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
					$advisor_semester 					= $advisorRow->semester;
					$advisor_survey_score 				= $advisorRow->survey_score;
					$advisor_languages 					= $advisorRow->languages;
					$advisor_fifo_date 					= $advisorRow->fifo_date;
					$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
					$advisor_verify_email_date 			= $advisorRow->verify_email_date;
					$advisor_verify_email_number 		= $advisorRow->verify_email_number;
					$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
					$advisor_action_log 				= $advisorRow->action_log;
					$advisor_class_verified 			= $advisorRow->class_verified;
					$advisor_control_code 				= $advisorRow->control_code;
					$advisor_replacement_status			= $advisorRow->replacement_status;
					$advisor_date_created 				= $advisorRow->date_created;
					$advisor_date_updated 				= $advisorRow->date_updated;


					if ($doDebug) {
						echo "Processing record $advisor_call_sign<br />";
					}
					$myCount++;
					if ($output_type == 'table') {
						$content	.= "<tr>";
						if ($advisor_id 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_ID</td>";
						}
						if ($select_sequence 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_select_sequence</td>";
						}
						if ($call_sign 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_call_sign</td>";
						}
						if ($first_name 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_first_name</td>";
						}
						if ($last_name 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_last_name</td>";
						}
						if ($email 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_email</td>";
						}
						if ($ph_code 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_ph_code</td>";
						}
						if ($phone 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_phone</td>";
						}
						if ($text_message 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_text_message</td>";
						}
						if ($city 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_city</td>";
						}
						if ($state 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_state</td>";
						}
						if ($zip_code 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_zip_code</td>";
						}
						if ($country 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_country</td>";
						}
						if ($country_code 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_country_code</td>";
						}
						if ($time_zone 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_time_zone</td>";
						}
						if ($timezone_id 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_timezone_id</td>";
						}
						if ($timezone_offset 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_timezone_offset</td>";
						}
						if ($whatsapp 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_whatsapp</td>";
						}
						if ($signal 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_signal</td>";
						}
						if ($telegram 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_telegram</td>";
						}
						if ($messenger 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_messenger</td>";
						}
						if ($semester 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_semester</td>";
						}
						if ($survey_score 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_survey_score</td>";
						}
						if ($languages 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_languages</td>";
						}
						if ($fifo_date 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_fifo_date</td>";
						}
						if ($welcome_email_date 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_welcome_email_date</td>";
						}
						if ($verify_email_date 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_verify_email_date</td>";
						}
						if ($verify_email_number 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_verify_email_number</td>";
						}
						if ($verify_response 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_verify_response</td>";
						}
						if ($action_log 	== 'X') {
						$newActionLog = formatActionLog($advisor_action_log);
						$content .= "<td style='vertical-align:top;'>$newActionLog</td>";
						}
						if ($class_verified 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_class_verified</td>";
						}
						if ($replacement_status 	== 'X') {
						$content .= "<td style='vertical-align:top;'>$advisor_replacement_status</td>";
						}
						if ($date_created == 'X') {
							$content .= "<td style='vertical-align:top;'>$advisor_date_created</td>";
						}
						if ($date_updated == 'X') {
							$content .= "<td style='vertical-align:top;'>$advisor_date_updated</td>";
						}
						$content .= "</tr>";
					} else { // output will be a comma separated file
						$needComma = FALSE;
						if ($advisor_id == 'X') {
						if ($needComma) {
						$content .= ',';
						}
						$content .= "'$advisor_ID'";
						$needComma = TRUE;
						}
						if ($select_sequence == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_select_sequence'";
						$needComma = TRUE;
						}
						if ($call_sign == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_call_sign'";
						$needComma = TRUE;
						}
						if ($first_name == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_first_name'";
						$needComma = TRUE;
						}
						if ($last_name == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_last_name'";
						$needComma = TRUE;
						}
						if ($email == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_email'";
						$needComma = TRUE;
						}
						if ($ph_code == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_ph_code'";
						$needComma = TRUE;
						}
						if ($phone == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_phone'";
						$needComma = TRUE;
						}
						if ($text_message == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_text_message'";
						$needComma = TRUE;
						}
						if ($city == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_city'";
						$needComma = TRUE;
						}
						if ($state == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_state'";
						$needComma = TRUE;
						}
						if ($zip_code == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_zip_code'";
						$needComma = TRUE;
						}
						if ($country == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_country'";
						$needComma = TRUE;
						}
						if ($country_code == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_country_code'";
						$needComma = TRUE;
						}
						if ($time_zone == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_time_zone'";
						$needComma = TRUE;
						}
						if ($timezone_id == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_timezone_id'";
						$needComma = TRUE;
						}
						if ($timezone_offset == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_timezone_offset'";
						$needComma = TRUE;
						}
						if ($whatsapp == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_whatsapp'";
						$needComma = TRUE;
						}
						if ($signal == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_signal'";
						$needComma = TRUE;
						}
						if ($telegram == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_telegram'";
						$needComma = TRUE;
						}
						if ($messenger == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_messenger'";
						$needComma = TRUE;
						}
						if ($semester == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_semester'";
						$needComma = TRUE;
						}
						if ($survey_score == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_survey_score'";
						$needComma = TRUE;
						}
						if ($languages == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_languages'";
						$needComma = TRUE;
						}
						if ($fifo_date == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_fifo_date'";
						$needComma = TRUE;
						}
						if ($welcome_email_date == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_welcome_email_date'";
						$needComma = TRUE;
						}
						if ($verify_email_date == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_verify_email_date'";
						$needComma = TRUE;
						}
						if ($verify_email_number == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_verify_email_number'";
						$needComma = TRUE;
						}
						if ($verify_response == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_verify_response'";
						$needComma = TRUE;
						}
						if ($action_log == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_action_log'";
						$needComma = TRUE;
						}
						if ($class_verified == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_class_verified'";
						$needComma = TRUE;
						}
						if ($replacement_status == 'X') {
						if ($needComma) {
						$content .= '	';
						}
						$content .= "'$advisor_replacement_status'";
						$needComma = TRUE;
						}
						if ($date_created == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$advisor_date_created";
							$needComma = TRUE;
						}
						if ($date_updated == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$advisor_date_updated";
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
		$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
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
			$storeResult = storeReportData_func($jobname,$content);
			if ($storeResult !== FALSE) {
				$content .= "<br />Report stored in reports pod as $storeResult";
			} else {
				$content .= "<br />Storing the report in the reports pod failed";
			}
		}
	}

return $content;

}
add_shortcode ('advisor_report_generator', 'advisor_report_generator_func');
