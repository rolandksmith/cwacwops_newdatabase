function student_report_generator_func() {

/*	Build Your Own Report from the Student table
 *	
 *	Select which fields to display in the report
 *	specify the data selection criteria
 * 	specify the post-selection tests
 *	display the report
 
	Modified 1Feb2021 to change student_code to messaging
	Modified 1Aug2021 by Roland to do tab delimited correctly
	Modified 4Sep2021 by Roland to add class choice UTC
	Modified 28Dec2021 by Roland to use the student table rather than student table
	Modified 18Jul2022 by Roland to add link from student call sign to display and 
		update student information
	Modified 24Sep22 by Roland for the new database fields
	Modified 17Apr23 by Roland to fix action_log
	Modified 16Jul23 by Roland to use consolidated tables
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
	$validUser = $initializationArray['validUser'];
	$userName  = $initializationArray['userName'];
	$siteURL	= $initializationArray['siteurl'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$futureSemester	= "(semester = '$nextSemester' or semester = '$semesterTwo' or semester = '$semesterThree' or semester = '$semesterFour')";
	$proximateSemester	= $initializationArray['proximateSemester'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',360);
		
	$strPass					= "1";
	$requestType				= '';

	$action_log					= '';
	$advisor					= '';
	$advisor_select_date		= '';
	$age						= '';
	$assigned_advisor			= '';
	$advisor_class_timezone		= '';
	$call_sign					= '';
	$city						= '';
	$class_priority				= '';
	$assigned_advisor_class		= '';
	$email						= '';
	$email_number				= '';
	$excluded_advisor			= '';
	$first_name					= '';
	$hold_override				= '';
	$hold_reason_code			= '';
	$id							= '';
	$intervention_required		= '';
	$last_name					= '';
	$level						= '';
	$mode_type					= '';
	$notes						= '';
	$email_sent_date			= '';
	$orderby					= '';
	$output_type				= '';
	$passed_over_count			= '';
	$phone						= '';
	$promotable					= '';
	$request_date				= '';
	$response					= '';
	$response_date				= '';
	$abandoned			= '';
	$selected_date				= '';
	$semester					= '';
	$state						= '';
	$messaging					= '';
	$student_status				= '';
	$student_survey_completion_date		= '';
	$testconditions				= '';
	$time_zone					= '';
	$available_class_days		= '';
	$welcome_date				= '';
	$where						= '';
	$wpm						= '';
	$youth						= '';
	$parent						= '';
	$parent_email				= '';
	$country					= '';
	$firstTime					= TRUE;
	$myCount					= 0;
	$inp_report					= '';
	$zip_code					= '';
	$start_time					= '';
	$first_class_choice			= '';
	$second_class_choice		= '';
	$third_class_choice			= '';
	$first_class_choice_utc		= '';
	$second_class_choice_utc	= '';
	$third_class_choice_utc		= '';
	$date_created				= '';
	$date_updated				= '';
	$ph_code					= '';
	$country_code				= '';
	$timezone_id				= '';
	$timezone_offset			= '';
	$whatsapp					= '';
	$signal						= '';
	$telegram					= '';
	$messenger					= '';
	$waiting_list				= '';
	$no_catalog					= '';
	$copy_control				= '';
	$catalog_options			= '';
	$flexible					= '';
	$theURL						= "$siteURL/cwa-student-report-generator/";
	$inp_config					= 'N';
	$inp_report_name			= '';
	$reportConfig				= array();
	
	

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if($str_key					== "inp_debug") {
				$inp_debug				 = $str_value;
				$inp_debug				 = filter_var($inp_debug,FILTER_UNSAFE_RAW);
				if ($inp_debug == 'Y') {
					$doDebug			= TRUE;
				}
			}
			if ($str_key 				== "strpass") {
				$strPass				 = $str_value;
				$strPass				 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "action_log") {
				$action_log				= 'X';
				$reportConfig['action_log']	= 'X';
			}
			if($str_key					== "theadvisor") {
				$advisor				= 'X';
				$reportConfig['advisor']	= 'X';
			}
			if($str_key					== "advisor_class_timezone") {
				$advisor_class_timezone	= 'X';
				$reportConfig['advisor_class_timezone']	= 'X';
			}
			if($str_key					== "advisor_select_date") {
				$advisor_select_date	= 'X';
				$reportConfig['advisor_select_date']	= 'X';
			}
			if($str_key					== "age") {
				$age					= 'X';
				$reportConfig['age']	= 'X';
			}
			if($str_key					== "assigned_advisor") {
				$assigned_advisor		= 'X';
				$reportConfig['assigned_advisor']	= 'X';
			}
			if($str_key					== "call_sign") {
				$call_sign				= 'X';
				$reportConfig['call_sign']	= 'X';
			}
			if($str_key					== "city") {
				$city					= 'X';
				$reportConfig['city']	= 'X';
			}
			if($str_key					== "class_priority") {
				$class_priority			= 'X';
				$reportConfig['class_priority']	= 'X';
			}
			if($str_key					== "assigned_advisor_class") {
				$assigned_advisor_class	= 'X';
				$reportConfig['assigned_advisor_class']	= 'X';
			}
			if($str_key					== "email") {
				$email					= 'X';
				$reportConfig['email']	= 'X';
			}
			if($str_key					== "email_number") {
				$email_number			= 'X';
				$reportConfig['email_number']	= 'X';
			}
			if($str_key					== "excluded_advisor") {
				$excluded_advisor		= 'X';
				$reportConfig['excluded_advisor']	= 'X';
			}
			if($str_key					== "first_name") {
				$first_name				= 'X';
				$reportConfig['first_name']	= 'X';
			}
			if($str_key					== "hold_override") {
				$hold_override			= 'X';
				$reportConfig['hold_override']	= 'X';
			}
			if($str_key					== "hold_reason_code") {
				$hold_reason_code		= 'X';
				$reportConfig['hold_reason_code']	= 'X';
			}
			if($str_key					== "id") {
				$id						= 'X';
				$reportConfig['id']	= 'X';
			}
			if($str_key					== "intervention_required") {
				$intervention_required	= 'X';
				$reportConfig['intervention_required']	= 'X';
			}
			if($str_key					== "last_name") {
				$last_name				= 'X';
				$reportConfig['last_name']	= 'X';
			}
			if($str_key					== "level") {
				$level					= 'X';
				$reportConfig['level']	= 'X';
			}
			if ($str_key 				== "mode_type") {
				$mode_type				 = $str_value;
				$mode_type				 = filter_var($mode_type,FILTER_UNSAFE_RAW);
				$reportConfig['mode_type']	= $mode_type;
			}
			if($str_key					== "notes") {
				$notes					= 'X';
				$reportConfig['notes']	= 'X';
			}
			if($str_key					== "email_sent_date") {
				$email_sent_date		= 'X';
				$reportConfig['email_sent_date']	= 'X';
			}
			if($str_key					== "orderby") {
				$orderby				= $str_value;
				$orderby				 = filter_var($orderby,FILTER_UNSAFE_RAW);
				$orderby				= stripslashes($orderby);
			}
			if($str_key					== "output_type") {
				$output_type				 = $str_value;
				$output_type				 = filter_var($output_type,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "passed_over_count") {
				$passed_over_count		= 'X';
				$reportConfig['passed_over_count']	= 'X';
			}
			if($str_key					== "phone") {
				$phone					= 'X';
				$reportConfig['phone']	= 'X';
			}
			if($str_key					== "promotable") {
				$promotable				= 'X';
				$reportConfig['promotable']	= 'X';
			}
			if($str_key					== "request_date") {
				$request_date			= 'X';
				$reportConfig['request_date']	= 'X';
			}
			if($str_key					== "response") {
				$response				= 'X';
				$reportConfig['response']	= 'X';
			}
			if($str_key					== "response_date") {
				$response_date			= 'X';
				$reportConfig['response_date']	= 'X';
			}
			if($str_key					== "abandoned") {
				$abandoned		= 'X';
				$reportConfig['abandoned']	= 'X';
			}
			if($str_key					== "selected_date") {
				$selected_date			= 'X';
				$reportConfig['selected_date']	= 'X';
			}
			if($str_key					== "semester") {
				$semester				= 'X';
				$reportConfig['semester']	= 'X';
			}
			if($str_key					== "state") {
				$state					= 'X';
				$reportConfig['state']	= 'X';
			}
			if($str_key					== "messaging") {
				$messaging			= 'X';
				$reportConfig['messaging']	= 'X';
			}
			if($str_key					== "student_status") {
				$student_status			= 'X';
				$reportConfig['student_status']	= 'X';
			}
			if($str_key					== "student_survey_completion_date") {
				$student_survey_completion_date	= 'X';
				$reportConfig['student_survey_completion_date']	= 'X';
			}
			if($str_key					== "time_zone") {
				$time_zone				= 'X';
				$reportConfig['time_zone']	= 'X';
			}
			if($str_key					== "available_class_days") {
				$available_class_days	= 'X';
				$reportConfig['available_class_cays']	= 'X';
			}
			if($str_key					== "welcome_date") {
				$welcome_date			= 'X';
				$reportConfig['welcone_date']	= 'X';
			}
			if($str_key					== "where") {
				$where					 = $str_value;
// echo "where b4 filter: $where<br />";
//				$where					 = filter_var($where,FILTER_UNSAFE_RAW);
				$where					= str_replace("&#39;","'",$where);
				$where					= stripslashes($where);
// echo "where after filter: $where<br />";
			}
			if($str_key					== "wpm") {
				$wpm					= 'X';
				$reportConfig['wpm']	= 'X';
			}
			if($str_key					== "youth") {
				$youth					= 'X';		
				$reportConfig['youth']	= 'X';
			}
			if($str_key					== "parent_email") {
				$parent_email			= 'X';		
				$reportConfig['parent_email']	= 'X';
			}
			if($str_key					== "parent") {
				$parent					= 'X';		
				$reportConfig['parent']	= 'X';
			}
			if($str_key					== "country") {
				$country					= 'X';		
				$reportConfig['country']	= 'X';
			}
			if($str_key					== "zip_code") {
				$zip_code					= 'X';		
				$reportConfig['zip_code']	= 'X';
			}
			if($str_key					== "start_time") {
				$start_time					= 'X';		
				$reportConfig['start_time']	= 'X';
			}
			if($str_key					== "first_class_choice") {
				$first_class_choice					= 'X';		
				$reportConfig['first_class_choice']	= 'X';
			}
			if($str_key					== "second_class_choice") {
				$second_class_choice					= 'X';		
				$reportConfig['second_class_choice']	= 'X';
			}
			if($str_key					== "third_class_choice") {
				$third_class_choice					= 'X';		
				$reportConfig['third_class_choice']	= 'X';
			}
			if($str_key					== "first_class_choice_utc") {
				$first_class_choice_utc					= 'X';		
				$reportConfig['first_class_choice_utc']	= 'X';
			}
			if($str_key					== "second_class_choice_utc") {
				$second_class_choice_utc					= 'X';		
				$reportConfig['second_class_choice_utc']	= 'X';
			}
			if($str_key					== "third_class_choice_utc") {
				$third_class_choice_utc					= 'X';		
				$reportConfig['third_class_choice_utc']	= 'X';
			}
			if($str_key					== "date_created") {
				$date_created					= 'X';		
				$reportConfig['date_created']	= 'X';
			}
			if($str_key					== "date_updated") {
				$date_updated					= 'X';		
				$reportConfig['date_updated']	= 'X';
			}
			if($str_key					== "ph_code") {
				$ph_code					= 'X';		
				$reportConfig['ph_code']	= 'X';
			}
			if($str_key					== "country_code") {
				$country_code					= 'X';		
				$reportConfig['country_code']	= 'X';
			}
			if($str_key					== "timezone_id") {
				$timezone_id					= 'X';		
				$reportConfig['timezone_id']	= 'X';
			}
			if($str_key					== "timezone_offset") {
				$timezone_offset					= 'X';		
				$reportConfig['timezone_offset']	= 'X';
			}
			if($str_key					== "whatsapp") {
				$whatsapp					= 'X';		
				$reportConfig['whatsapp']	= 'X';
			}
			if($str_key					== "signal") {
				$signal					= 'X';		
				$reportConfig['signal']	= 'X';
			}
			if($str_key					== "telegram") {
				$telegram					= 'X';		
				$reportConfig['telegram']	= 'X';
			}
			if($str_key					== "messenger") {
				$messenger					= 'X';		
				$reportConfig['messenger']	= 'X';
			}
			if($str_key					== "waiting_list") {
				$waiting_list					= 'X';		
				$reportConfig['waiting_list']	= 'X';
			}
			if($str_key					== "no_catalog") {
				$no_catalog					= 'X';		
				$reportConfig['no_catalog']	= 'X';
			}
			if($str_key					== "copy_control") {
				$copy_control					= 'X';		
				$reportConfig['copy_control']	= 'X';
			}
			if($str_key					== "catalog_options") {
				$catalog_options					= 'X';		
				$reportConfig['catalog_options']	= 'X';
			}
			if($str_key					== "flexible") {
				$flexible					= 'X';		
				$reportConfig['flexible']	= 'X';
			}
			if($str_key					== "inp_report") {
				$inp_report				 = $str_value;
				$inp_report				 = filter_var($inp_report,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "inp_download") {
				$inp_download				 = $str_value;
				$inp_download				 = filter_var($inp_download,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "inp_filename") {
				$inp_filename				 = $str_value;
				$inp_filename				 = filter_var($inp_filename,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "inp_config") {
				$inp_config				 = $str_value;
				$inp_config				 = filter_var($inp_config,FILTER_UNSAFE_RAW);
			}
			if($str_key					== "inp_report_name") {
				$inp_report_name				 = $str_value;
				$inp_report_name				 = filter_var($inp_report_name,FILTER_UNSAFE_RAW);
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
		$content 		.= "<h3>Build Student Report</h3>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<p>Select the fields to be on the report:
<table>
<tr><th>Report Field</th><th>Table Name</th></tr>
<tr><td><input type='checkbox' id='id' name='id' value='id'> ID</td><td>id</td></tr>
<tr><td><input type='checkbox' id='call_sign' name='call_sign' value='call_sign'> Student Call Sign</td><td>call_sign</td></tr>
<tr><td><input type='checkbox' id='first_name' name='first_name' value='first_name'> First Name</td><td>first_name</td></tr>
<tr><td><input type='checkbox' id='last_name' name='last_name' value='last_name'> Last Name</td><td>last_name</td></tr>
<tr><td><input type='checkbox' id='email' name='email' value='email'> Email</td><td>email</td></tr>
<tr><td><input type='checkbox' id='ph_code' name='ph_code' value='ph_code'> Ph Code</td><td>ph_code</td></tr>
<tr><td><input type='checkbox' id='phone' name='phone' value='phone'> Phone</td><td>phone</td></tr>
<tr><td><input type='checkbox' id='city' name='city' value='city'> City</td><td>city</td></tr>
<tr><td><input type='checkbox' id='state' name='state' value='state'> State</td><td>state</td></tr>
<tr><td><input type='checkbox' id='zip_code' name='zip_code' value='zip_code'> Zip or Postal Code</td><td>zip_code</td></tr>
<tr><td><input type='checkbox' id='country_code' name='country_code' value='country_code'> Country Code</td><td>country_code</td></tr>
<tr><td><input type='checkbox' id='country' name='country' value='country'> Country</td><td>country</td></tr>
<tr><td><input type='checkbox' id='time_zone' name='time_zone' value='time_zone'> Time Zone</td><td>time_zone</td></tr>
<tr><td><input type='checkbox' id='timezone_id' name='timezone_id' value='timezone_id'> Timezone ID</td><td>timezone_id</td></tr>
<tr><td><input type='checkbox' id='timezone_offset' name='timezone_offset' value='timezone_offset'> Timezone Offset</td><td>timezone_offset</td></tr>
<tr><td><input type='checkbox' id='whatsapp' name='whatsapp' value='whatsapp'> Whatsapp</td><td>whatsapp</td></tr>
<tr><td><input type='checkbox' id='signal' name='signal' value='signal'> Signal</td><td>signal</td></tr>
<tr><td><input type='checkbox' id='telegram' name='telegram' value='telegram'> Telegram</td><td>telegram</td></tr>
<tr><td><input type='checkbox' id='messenger' name='messenger' value='messenger'> Messenger</td><td>messenger</td></tr>
<tr><td><input type='checkbox' id='wpm' name='wpm' value='wpm'> WPM</td><td>wpm (not used)</td></tr>
<tr><td><input type='checkbox' id='youth' name='youth' value='youth'> Youth</td><td>youth</td></tr>
<tr><td><input type='checkbox' id='age' name='age' value='age'> Age</td><td>age</td></tr>
<tr><td><input type='checkbox' id='parent' name='parent' value='parent'> Student Parent</td><td>student_parent</td></tr>
<tr><td><input type='checkbox' id='parent_email' name='parent_email' value='parent_email'> Student Parent Email</td><td>student_parent_email</td></tr>
<tr><td><input type='checkbox' id='level' name='level' value='level'> Level</td><td>level</td></tr>
<tr><td><input type='checkbox' id='waiting_list' name='waiting_list' value='waiting_list'> Waiting List</td><td>waiting_list</td></tr>
<tr><td><input type='checkbox' id='request_date' name='request_date' value='request_date'> Request Date</td><td>request_date </td></tr>
<tr><td><input type='checkbox' id='semester' name='semester' value='semester'> Semester</td><td>semester</td></tr>
<tr><td><input type='checkbox' id='notes' name='notes' value='notes'> Notes</td><td>notes</td></tr>
<tr><td><input type='checkbox' id='email_sent_date' name='email_sent_date' value='email_sent_date'> Email Sent Date</td><td>email_sent_date </td></tr>
<tr><td><input type='checkbox' id='email_number' name='email_number' value='email_number'> Email Number</td><td>email_number</td></tr>
<tr><td><input type='checkbox' id='response' name='response' value='response'> Response</td><td>response</td></tr>
<tr><td><input type='checkbox' id='response_date' name='response_date' value='response_date'> Response Date</td><td>response_date </td></tr>
<tr><td><input type='checkbox' id='abandoned' name='abandoned' value='abandoned'> Abandoned</td><td>abandoned</td></tr>
<tr><td><input type='checkbox' id='student_status' name='student_status' value='student_status'> Student Status</td><td>student_status</td></tr>
<tr><td><input type='checkbox' id='action_log' name='action_log' value='action_log'> Action Log</td><td>action_log</td></tr>
<tr><td><input type='checkbox' id='theadvisor' name='theadvisor' value='theadvisor'> Pre-assigned advisor</td><td>pre_assigned_advisor</td></tr>
<tr><td><input type='checkbox' id='selected_date' name='selected_date' value='selected_date'> Selected Date</td><td>selected_date </td></tr>
<tr><td><input type='checkbox' id='welcome_date' name='welcome_date' value='welcome_date'> Welcome Date</td><td>wlcome_date </td></tr>
<tr><td><input type='checkbox' id='no_catalog' name='no_catalog' value='no_catalog'> No Catalog</td><td>no_catalog (not used)</td></tr>
<tr><td><input type='checkbox' id='hold_override' name='hold_override' value='hold_override'> Hold Override</td><td>hold_override</td></tr>
<tr><td><input type='checkbox' id='messaging' name='messaging' value='messaging'> Messaging</td><td>messaging</td></tr>
<tr><td><input type='checkbox' id='assigned_advisor' name='assigned_advisor' value='assigned_advisor'> Assigned Advisor</td><td>assigned_advisor</td></tr>
<tr><td><input type='checkbox' id='advisor_class_timezone' name='advisor_class_timezone' value='advisor_class_timezone'> Advisor Class Timezone</td><td>advisor_class_timezone</td></tr>
<tr><td><input type='checkbox' id='advisor_select_date' name='advisor_select_date' value='advisor_select_date'> Advisor Select Date</td><td>advisor_select_date </td></tr>
<tr><td><input type='checkbox' id='hold_reason_code' name='hold_reason_code' value='hold_reason_code'> Hold Reason Code</td><td>hold_reason_code</td></tr>
<tr><td><input type='checkbox' id='class_priority' name='class_priority' value='class_priority'> Class Priority</td><td>class_priority</td></tr>
<tr><td><input type='checkbox' id='assigned_advisor_class' name='assigned_advisor_class' value='assigned_advisor_class'> Assigned Advisor Class</td><td>assigned_advisor_class</td></tr>
<tr><td><input type='checkbox' id='promotable' name='promotable' value='promotable'> Promotable</td><td>promotable</td></tr>
<tr><td><input type='checkbox' id='excluded_advisor' name='excluded_advisor' value='excluded_advisor'> Excluded Advisor</td><td>excluded_advisor</td></tr>
<tr><td><input type='checkbox' id='student_survey_completion_date' name='student_survey_completion_date' value='student_survey_completion_date'> Student Survey Completion Date</td><td>student_survey_completion_date</td></tr>
<tr><td><input type='checkbox' id='available_class_days' name='available_class_days' value='available_class_days'> Available Class Days</td><td>available_class_days</td></tr>
<tr><td><input type='checkbox' id='intervention_required' name='intervention_required' value='intervention_required'> Intervention Required</td><td>intervention_required</td></tr>
<tr><td><input type='checkbox' id='copy_control' name='copy_control' value='copy_control'> Copy Control</td><td>copy_control</td></tr>
<tr><td><input type='checkbox' id='first_class_choice' name='first_class_choice' value='first_class_choice'> First Class Choice</td><td>first_class_choice</td></tr>
<tr><td><input type='checkbox' id='second_class_choice' name='second_class_choice' value='second_class_choice'> Second Class Choice</td><td>second_class_choice</td></tr>
<tr><td><input type='checkbox' id='third_class_choice' name='third_class_choice' value='third_class_choice'> Third Class Choice</td><td>third_class_choice</td></tr>
<tr><td><input type='checkbox' id='first_class_choice_utc' name='first_class_choice_utc' value='first_class_choice_utc'> First Class Choice_utc</td><td>first_class_choice_utc</td></tr>
<tr><td><input type='checkbox' id='second_class_choice_utc' name='second_class_choice_utc' value='second_class_choice_utc'> Second Class Choice_utc</td><td>second_class_choice_utc</td></tr>
<tr><td><input type='checkbox' id='third_class_choice_utc' name='third_class_choice_utc' value='third_class_choice_utc'> Third Class Choice_utc</td><td>third_class_choice_utc</td></tr>
<tr><td><input type='checkbox' id='catalog_options' name='catalog_options' value='catalog_options'> Catalog Options</td><td>catalog_options</td></tr>
<tr><td><input type='checkbox' id='flexible' name='flexible' value='flexible'> Flexible</td><td>flexible</td></tr>
<tr><td><input type='checkbox' id='date_created' name='date_created' value='date_created'> Date Created</td><td>date_created</td></tr>
<tr><td><input type='checkbox' id='date_updated' name='date_updated' value='date_updated'> Date Updated</td><td>date_updated</td></tr>
</table>
</p><p>Select Output Format<br />
<input type='radio' id='table' name='output_type' value='table' checked='checked'> Table Report<br />
<input type='radio' id='comma' name='output_type' value='comma'> Tab Delimited Report<br />
</p><p>Which Table to Read<br />
<input type='radio' id='student' name='mode_type' value='wpw1_cwa_consolidated_student' checked='checked'> Student<br />
<input type='radio' id='student2' name='mode_type' value='wpw1_cwa_consolidated_student2'> Student2<br />
<input type='radio' id='student2' name='mode_type' value='wpw1_cwa_consolidated_student3'> Student3<br />
<input type='radio' id='student2' name='mode_type' value='wpw1_cwa_old_student'> Old Student<br />
</p><p>Enter the 'Where' clause:<br />
<textarea class='formInputText' id='where' name='where' rows='5' cols='80'></textarea><br />
</p><p>Enter the 'Orderby' clause:<br />
<textarea class='formInputText' id='orderby' name='orderby' rows='5' cols='80'>call_sign</textarea><br /></p>
<p>Save the report configuration?<br />
<input type='radio' id='inp_config' name='inp_config' value='N' checked='checked'> Do not save report configuration<br />
<input type='radio' id='inp_config' name='inp_config' value='Y' > Save report configuration (enter report name:)<br />
<input type='text' class='formInputText' size='50' maxlength='100' name='inp_report_name'></p>
<p>Verbose Debugging?<br />
<input type='radio' id='inp_debug' name='inp_debug' value='N' checked='checked'> Debugging off<br />
<input type='radio' id='inp_debug' name='inp_debug' value='Y' > Turn Debugging on<br /></p>
<p>Save Report to Reports Table? <br />
<input type='radio' id='inp_report' name='inp_report' value='N' checked='checked'> Do not save<br />
<input type='radio' id='inp_report' name='inp_report' value='Y' > Save the report<br /></p>
<p><input class='formInputButton' type='submit' value='Submit' />
</form></p>
<br /><br /><p>Examples of the 'where' clause:<br />
To include students for a particular semester: <em>semester='2021 Apr/May'</em><br /><br />
To include students assigned to a specific advisor: <em>assigned_advisor='WR7Q'</em><br /><br />
To include students for a particular semester but exclude students with a response of 'R': 
<em>semester='2021 Apr/May' and response != 'R'</em><br /><br />
Include all students with the phrase 'not promotable' in the action log: <em>action_log 
like '%not promotable%'</em><br /><br />
To include all future semesters, use <em>futureSemester</em>. For example: level = 'Fundamental' and futureSemester <br /><br />
To search current or upcoming semester use <em>proximateSemester</em> <br />
</p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
	
		$modeArray	= array('wpw1_cwa_consolidated_student','wpw1_cwa_consolidated_student2','wpw1_cwa_consolidated_student3', 'wpw1_cwa_old_student');
		if (in_array($mode_type,$modeArray)) {
			$studentTableName		= $mode_type;
		} else {
			$content			.= "Invalid information provided.";
			return $content;
		}
	
	
// Array to convert database name to display name
		$nameConversionArray = array();
		$nameConversionArray['action_log'] 						= 'Action Log';
		$nameConversionArray['advisor'] 						= 'Pre-assigned Advisor';
		$nameConversionArray['advisor_class_timezone'] 			= 'Advisor Class Timezone';
		$nameConversionArray['advisor_select_date'] 			= 'Advisor Select Date';
		$nameConversionArray['age'] 							= 'Age';
		$nameConversionArray['assigned_advisor'] 				= 'Assigned Advisor';
		$nameConversionArray['call_sign'] 						= 'Call Sign';
		$nameConversionArray['city'] 							= 'City';
		$nameConversionArray['class_priority'] 					= 'Class Priority';
		$nameConversionArray['assigned_advisor_class'] 			= 'Assigned Advisor Class';
		$nameConversionArray['email'] 							= 'Email';
		$nameConversionArray['email_number'] 					= 'Email Number';
		$nameConversionArray['email_sent_date'] 				= 'Email Sent Date';
		$nameConversionArray['excluded_advisor'] 				= 'excluded Advisor';
		$nameConversionArray['first_name'] 						= 'First Name';
		$nameConversionArray['hold_override'] 					= 'Hold Override';
		$nameConversionArray['hold_reason_code'] 				= 'Hold Reason Code';
		$nameConversionArray['intervention_required'] 			= 'Intervention Required';
		$nameConversionArray['id']								= 'ID';
		$nameConversionArray['last_name'] 						= 'Last Name';
		$nameConversionArray['level'] 							= 'Level';
		$nameConversionArray['notes'] 							= 'Notes';
		$nameConversionArray['no_catalog']		 				= 'No Catalog';
		$nameConversionArray['phone'] 							= 'Phone';
		$nameConversionArray['promotable'] 						= 'Promotable';
		$nameConversionArray['request_date'] 					= 'Request Date';
		$nameConversionArray['response'] 						= 'Response';
		$nameConversionArray['response_date'] 					= 'Response Date';
		$nameConversionArray['abandoned'] 				= 'Abandoned';
		$nameConversionArray['selected_date'] 					= 'Selected Date';
		$nameConversionArray['semester'] 						= 'Semester';
		$nameConversionArray['state'] 							= 'State';
		$nameConversionArray['messaging'] 						= 'Messaging';
		$nameConversionArray['student_status'] 					= 'Student Status';
		$nameConversionArray['student_survey_completion_date'] 	= 'Survey Completion Date';
		$nameConversionArray['time_zone'] 						= 'Time Zone';
		$nameConversionArray['available_class_days'] 			= 'Available Class Days';
		$nameConversionArray['welcome_date'] 					= 'Welcome Date';
		$nameConversionArray['wpm'] 							= 'WPM';
		$nameConversionArray['youth'] 							= 'Youth';
		$nameConversionArray['parent'] 							= 'Parent';
		$nameConversionArray['parent_email'] 					= 'Parent Email';
		$nameConversionArray['country']							= 'Country';
		$nameConversionArray['zip_code']						= 'Zip or Postal Code';
		$nameConversionArray['waiting_list']					= 'Waiting List';
		$nameConversionArray['first_class_choice']				= 'First Class Choice';
		$nameConversionArray['second_class_choice']				= 'Second Class Choice';
		$nameConversionArray['third_class_choice']				= 'Third Class Choice';
		$nameConversionArray['first_class_choice_utc']			= 'First Class Choice UTC';
		$nameConversionArray['second_class_choice_utc']			= 'Second Class Choice UTC';
		$nameConversionArray['third_class_choice_utc']			= 'Third Class Choice UTC';
		$nameConversionArray['date_created']					= 'Date Created';
		$nameConversionArray['date_updated']					= 'Date Updated';
		$nameConversionArray['ph_code']							= 'Ph Code';
		$nameConversionArray['country_code']					= 'Country Code';
		$nameConversionArray['timezone_id']						= 'Timezone ID';
		$nameConversionArray['timezone_offset']					= 'Timezone Offset';
		$nameConversionArray['whatsapp']						= 'Whatsapp';
		$nameConversionArray['signal']							= 'Signal';
		$nameConversionArray['telegram']						= 'Telegram';
		$nameConversionArray['messenger']						= 'Messenger';
		$nameConversionArray['waiting_list']					= 'Waiting List';
		$nameConversionArray['copy_control']					= 'Copy Control';
		$nameConversionArray['catalog_options']					= 'Catalog Optioins';
		$nameConversionArray['flexible']						= 'Flexible';

		// Begin the Report Output
		
		$myInt = strpos($where,'futureSemester');
		if ($myInt !== FALSE) {
			$where = str_replace('futureSemester',$futureSemester,$where);
		}
		$myInt = strpos($where,'proximateSemester');
		if ($myInt !== FALSE) {
			$where = str_replace('proximateSemester',$proximateSemester,$where);
		}

		if ($inp_config == 'Y') {		// saving the report configuration
			if ($inp_report_name != '') {
				$whereStr					= htmlentities($where,ENT_QUOTES);
				$reportConfig['where']		= $whereStr;
				$reportConfig['orderby']	= $orderby;
				$reportConfig['tg_table']	= $studentTableName;
				$reportConfig['type']		= $output_type;
				$myStr						= date('Y-m-d H:i:s');
				$rg_config					= addslashes(json_encode($reportConfig));
				
				// if the report name alreaady exists, update else insert
				$reportNameCount			= $wpdb->get_var("select count(rg_report_name) from wpw1_cwa_report_configurations where rg_report_name = '$inp_report_name'");
				if ($reportNameCount == 0) {
					if ($doDebug) {
						echo "report name $inp_report_name is new<br />";
					}
					$reportQuery = "insert into wpw1_cwa_report_configurations 
(rg_report_name, rg_table, rg_config, date_written) values 
('$inp_report_name', '$studentTableName', '$rg_config', '$myStr')";
				} else {
					if ($doDebug) {
						echo "report name $inp_report_name is being updated<br />";
					}
					$reportQuery = "update wpw1_cwa_report_configurations 
set rg_table = '$studentTableName', 
	rg_config = '$rg_config', 
	date_written = '$myStr' 
where rg_report_name = '$inp_report_name'";
				}
				
				if ($doDebug) {
					echo "Preparing to save the report configuration. SQL: $reportQuery<br />";
				}
				// run the reportQuery
				$reportResult	= $wpdb->query($reportQuery);
				if ($reportResult === FALSE) {
					if ($doDebug) {
						$thisError	= $wpdb->last_error;
						$thisSQL	= $wpdb->last_query;
						echo "writing to wpw1_cwa_report_configurations failed. Error: $thisError<br />SQL: $thisSQL<br />";
					}
					$content		.= "<p>Unable to store report configuration</p>";
				}
			}
		}
		
		$content				.= "<h2>Generated Report from the $studentTableName Table</h2>
									<p>Where: $where<br />
									Ordered By: $orderby<br />
									Debugging: $inp_debug<br />
									Save report: $inp_report<br />";

		$sql = "select * from $studentTableName";
		if ($where != '') {
			$sql	= "$sql where $where ";
		}
		if ($orderby != '') {
			$sql	= "$sql order by $orderby";
		}
		$content	.= "SQL: $sql</p>";
		if ($output_type == 'table') {
			$content .= "<table><tr>";
			if ($id == 'X') {
				$headerName = $nameConversionArray['id'];
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
			if ($country_code == 'X') {
				$headerName = $nameConversionArray['country_code'];
				$content .= "<th>$headerName</th>";
			}
			if ($country == 'X') {
				$headerName = $nameConversionArray['country'];
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
			if ($wpm == 'X') {
				$headerName = $nameConversionArray['wpm'];
				$content .= "<th>$headerName</th>";
			}
			if ($youth == 'X') {
				$headerName = $nameConversionArray['youth'];
				$content .= "<th>$headerName</th>";
			}
			if ($age == 'X') {
				$headerName = $nameConversionArray['age'];
				$content .= "<th>$headerName</th>";
			}
			if ($parent == 'X') {
				$headerName = $nameConversionArray['parent'];
				$content .= "<th>$headerName</th>";
			}
			if ($parent_email == 'X') {
				$headerName = $nameConversionArray['parent_email'];
				$content .= "<th>$headerName</th>";
			}
			if ($level == 'X') {
				$headerName = $nameConversionArray['level'];
				$content .= "<th>$headerName</th>";
			}
			if ($waiting_list == 'X') {
				$headerName = $nameConversionArray['waiting_list'];
				$content .= "<th>$headerName</th>";
			}
			if ($request_date == 'X') {
				$headerName = $nameConversionArray['request_date'];
				$content .= "<th>$headerName</th>";
			}
			if ($semester == 'X') {
				$headerName = $nameConversionArray['semester'];
				$content .= "<th>$headerName</th>";
			}
			if ($notes == 'X') {
				$headerName = $nameConversionArray['notes'];
				$content .= "<th>$headerName</th>";
			}
			if ($email_sent_date == 'X') {
				$headerName = $nameConversionArray['email_sent_date'];
				$content .= "<th>$headerName</th>";
			}
			if ($email_number == 'X') {
				$headerName = $nameConversionArray['email_number'];
				$content .= "<th>$headerName</th>";
			}
			if ($response == 'X') {
				$headerName = $nameConversionArray['response'];
				$content .= "<th>$headerName</th>";
			}
			if ($response_date == 'X') {
				$headerName = $nameConversionArray['response_date'];
				$content .= "<th>$headerName</th>";
			}
			if ($abandoned == 'X') {
				$headerName = $nameConversionArray['abandoned'];
				$content .= "<th>$headerName</th>";
			}
			if ($student_status == 'X') {
				$headerName = $nameConversionArray['student_status'];
				$content .= "<th>$headerName</th>";
			}
			if ($action_log == 'X') {
				$headerName = $nameConversionArray['action_log'];
				$content .= "<th>$headerName</th>";
			}
			if ($advisor == 'X') {
				$headerName = $nameConversionArray['advisor'];
				$content .= "<th>$headerName</th>";
			}
			if ($selected_date == 'X') {
				$headerName = $nameConversionArray['selected_date'];
				$content .= "<th>$headerName</th>";
			}
			if ($welcome_date == 'X') {
				$headerName = $nameConversionArray['welcome_date'];
				$content .= "<th>$headerName</th>";
			}
			if ($no_catalog == 'X') {
				$headerName = $nameConversionArray['no_catalog'];
				$content .= "<th>$headerName</th>";
			}
			if ($hold_override == 'X') {
				$headerName = $nameConversionArray['hold_override'];
				$content .= "<th>$headerName</th>";
			}
			if ($messaging == 'X') {
				$headerName = $nameConversionArray['messaging'];
				$content .= "<th>$headerName</th>";
			}
			if ($assigned_advisor == 'X') {
				$headerName = $nameConversionArray['assigned_advisor'];
				$content .= "<th>$headerName</th>";
			}
			if ($start_time == 'X') {
				$headerName = $nameConversionArray['start_time'];
				$content .= "<th>$headerName</th>";
			}
			if ($advisor_class_timezone == 'X') {
				$headerName = $nameConversionArray['advisor_class_timezone'];
				$content .= "<th>$headerName</th>";
			}
			if ($advisor_select_date == 'X') {
				$headerName = $nameConversionArray['advisor_select_date'];
				$content .= "<th>$headerName</th>";
			}
			if ($hold_reason_code == 'X') {
				$headerName = $nameConversionArray['hold_reason_code'];
				$content .= "<th>$headerName</th>";
			}
			if ($class_priority == 'X') {
				$headerName = $nameConversionArray['class_priority'];
				$content .= "<th>$headerName</th>";
			}
			if ($assigned_advisor_class == 'X') {
				$headerName = $nameConversionArray['assigned_advisor_class'];
				$content .= "<th>$headerName</th>";
			}
			if ($promotable == 'X') {
				$headerName = $nameConversionArray['promotable'];
				$content .= "<th>$headerName</th>";
			}
			if ($excluded_advisor == 'X') {
				$headerName = $nameConversionArray['excluded_advisor'];
				$content .= "<th>$headerName</th>";
			}
			if ($student_survey_completion_date == 'X') {
				$headerName = $nameConversionArray['student_survey_completion_date'];
				$content .= "<th>$headerName</th>";
			}
			if ($available_class_days == 'X') {
				$headerName = $nameConversionArray['available_class_days'];
				$content .= "<th>$headerName</th>";
			}
			if ($intervention_required == 'X') {
				$headerName = $nameConversionArray['intervention_required'];
				$content .= "<th>$headerName</th>";
			}	
			if ($copy_control == 'X') {
				$headerName = $nameConversionArray['copy_control'];
				$content .= "<th>$headerName</th>";
			}	
			if ($first_class_choice == 'X') {
				$headerName = $nameConversionArray['first_class_choice'];
				$content .= "<th>$headerName</th>";
			}	
			if ($second_class_choice == 'X') {
				$headerName = $nameConversionArray['second_class_choice'];
				$content .= "<th>$headerName</th>";
			}	
			if ($third_class_choice == 'X') {
				$headerName = $nameConversionArray['third_class_choice'];
				$content .= "<th>$headerName</th>";
			}
			if ($first_class_choice_utc == 'X') {
				$headerName = $nameConversionArray['first_class_choice_utc'];
				$content .= "<th>$headerName</th>";
			}	
			if ($second_class_choice_utc == 'X') {
				$headerName = $nameConversionArray['second_class_choice_utc'];
				$content .= "<th>$headerName</th>";
			}	
			if ($third_class_choice_utc == 'X') {
				$headerName = $nameConversionArray['third_class_choice_utc'];
				$content .= "<th>$headerName</th>";
			}
			if ($catalog_options == 'X') {
				$headerName = $nameConversionArray['catalog_options'];
				$content .= "<th>$headerName</th>";
			}	
			if ($flexible == 'X') {
				$headerName = $nameConversionArray['flexible'];
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
			$content		.= "<pre>";
			if ($id == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "id";
				$needComma = TRUE;
			}
			if ($call_sign == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "call_sign";
				$needComma = TRUE;
			}
			if ($first_name == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "first_name";
				$needComma = TRUE;
			}
			if ($last_name == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "last_name";
				$needComma = TRUE;
			}
			if ($email == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "email";
				$needComma = TRUE;
			}
			if ($ph_code == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "ph_code";
				$needComma = TRUE;
			}
			if ($phone == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "phone";
				$needComma = TRUE;
			}
			if ($city == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "city";
				$needComma = TRUE;
			}
			if ($state == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "state";
				$needComma = TRUE;
			}
			if ($zip_code == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "zip_code";
				$needComma = TRUE;
			}
			if ($country_code == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "country_code";
				$needComma = TRUE;
			}
			if ($country == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "country";
				$needComma = TRUE;
			}
			if ($time_zone == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "time_zone";
				$needComma = TRUE;
			}
			if ($timezone_id == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "timezone_id";
				$needComma = TRUE;
			}
			if ($timezone_offset == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "timezone_offset";
				$needComma = TRUE;
			}
			if ($whatsapp == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "whatsapp";
				$needComma = TRUE;
			}
			if ($signal == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "signal";
				$needComma = TRUE;
			}
			if ($telegram == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "telegram";
				$needComma = TRUE;
			}
			if ($messenger == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "messenger";
				$needComma = TRUE;
			}
			if ($wpm == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "wpm";
				$needComma = TRUE;
			}
			if ($youth == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "youth";
				$needComma = TRUE;
			}
			if ($age == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "age";
				$needComma = TRUE;
			}
			if ($age == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "parent";
				$needComma = TRUE;
			}
			if ($age == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "parent_email";
				$needComma = TRUE;
			}
			if ($level == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "level";
				$needComma = TRUE;
			}
			if ($waiting_list == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "waiting_list";
				$needComma = TRUE;
			}
			if ($request_date == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "request_date";
				$needComma = TRUE;
			}
			if ($semester == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "semester";
				$needComma = TRUE;
			}
			if ($notes == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "notes";
				$needComma = TRUE;
			}
			if ($email_sent_date == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "email_sent_date";
				$needComma = TRUE;
			}
			if ($email_number == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "email_number";
				$needComma = TRUE;
			}
			if ($response == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "response";
				$needComma = TRUE;
			}
			if ($response_date == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "response_date";
				$needComma = TRUE;
			}
			if ($abandoned == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "abandoned";
				$needComma = TRUE;
			}
			if ($student_status == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "student_status";
				$needComma = TRUE;
			}
			if ($action_log == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "action_log";
				$needComma = TRUE;
			}
			if ($advisor == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "advisor";
				$needComma = TRUE;
			}
			if ($selected_date == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "selected_date";
				$needComma = TRUE;
			}
			if ($welcome_date == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "welcome_date";
				$needComma = TRUE;
			}
			if ($no_catalog == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "no_catalog";
				$needComma = TRUE;
			}
			if ($hold_override == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "hold_override";
				$needComma = TRUE;
			}
			if ($messaging == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "messaging";
				$needComma = TRUE;
			}
			if ($assigned_advisor == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "assigned_advisor";
				$needComma = TRUE;
			}
			if ($start_time == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "start_time";
				$needComma = TRUE;
			}
			if ($advisor_class_timezone == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "advisor_class_timezone";
				$needComma = TRUE;
			}
			if ($advisor_select_date == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "advisor_select_date";
				$needComma = TRUE;
			}
			if ($hold_reason_code == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "hold_reason_code";
				$needComma = TRUE;
			}
			if ($class_priority == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "class_priority";
				$needComma = TRUE;
			}
			if ($assigned_advisor_class == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "assigned_advisor_class";
				$needComma = TRUE;
			}
			if ($promotable == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "promotable";
				$needComma = TRUE;
			}
			if ($excluded_advisor == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "excluded_advisor";
				$needComma = TRUE;
			}
			if ($student_survey_completion_date == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "student_survey_completion_date";
				$needComma = TRUE;
			}
			if ($available_class_days == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "available_class_days";
				$needComma = TRUE;
			}
			if ($intervention_required == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "intervention_required";
				$needComma = TRUE;
			}
			if ($copy_control == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "copy_control";
				$needComma = TRUE;
			}
			if ($first_class_choice == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "first_class_choice";
				$needComma = TRUE;
			}
			if ($second_class_choice == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "second_class_choice";
				$needComma = TRUE;
			}
			if ($third_class_choice == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "third_class_choice";
				$needComma = TRUE;
			}
			if ($first_class_choice_utc == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "first_class_choice_utc";
				$needComma = TRUE;
			}
			if ($second_class_choice_utc == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "second_class_choice_utc";
				$needComma = TRUE;
			}
			if ($third_class_choice_utc == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "third_class_choice_utc";
				$needComma = TRUE;
			}
			if ($catalog_options == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "catalog_options";
				$needComma = TRUE;
			}
			if ($flexible == 'X') {
				if ($needComma) {
					$content .= '	';
				}
				$content .= "flexible";
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
			$content	.= "\n";
		}
///// read the student table
		$wpw1_cwa_student				= $wpdb->get_results($sql);
		if ($doDebug) {
			echo "Reading $mode_type table<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			if ($wpdb->last_error != '') {
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		}
		if (FALSE === $wpw1_cwa_student) {		// no record found
			if ($doDebug) {
				echo "FUNCTION: No data found in $mode_type<br />";
			}
		} else {
			$numSRows										= $wpdb->num_rows;
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_ph_code						= $studentRow->ph_code;
					$student_phone  						= $studentRow->phone;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country_code					= $studentRow->country_code;
					$student_country  						= $studentRow->country;
					$student_time_zone  					= $studentRow->time_zone;
					$student_timezone_id					= $studentRow->timezone_id;
					$student_timezone_offset				= $studentRow->timezone_offset;
					$student_whatsapp						= $studentRow->whatsapp_app;
					$student_signal							= $studentRow->signal_app;
					$student_telegram						= $studentRow->telegram_app;
					$student_messenger						= $studentRow->messenger_app;					
					$student_wpm 	 						= $studentRow->wpm;
					$student_youth  						= $studentRow->youth;
					$student_age  							= $studentRow->age;
					$student_student_parent 				= $studentRow->student_parent;
					$student_student_parent_email  			= strtolower($studentRow->student_parent_email);
					$student_level  						= $studentRow->level;
					$student_waiting_list 					= $studentRow->waiting_list;
					$student_request_date  					= $studentRow->request_date;
					$student_semester						= $studentRow->semester;
					$student_notes  						= $studentRow->notes;
					$student_welcome_date  					= $studentRow->welcome_date;
					$student_email_sent_date  				= $studentRow->email_sent_date;
					$student_email_number  					= $studentRow->email_number;
					$student_response  						= strtoupper($studentRow->response);
					$student_response_date  				= $studentRow->response_date;
					$student_abandoned  					= $studentRow->abandoned;
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_action_log  					= $studentRow->action_log;
					$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
					$student_selected_date  				= $studentRow->selected_date;
					$student_no_catalog			 			= $studentRow->no_catalog;
					$student_hold_override  				= $studentRow->hold_override;
					$student_messaging  					= $studentRow->messaging;
					$student_assigned_advisor  				= $studentRow->assigned_advisor;
					$student_advisor_select_date  			= $studentRow->advisor_select_date;
					$student_advisor_class_timezone 		= $studentRow->advisor_class_timezone;
					$student_hold_reason_code  				= $studentRow->hold_reason_code;
					$student_class_priority  				= $studentRow->class_priority;
					$student_assigned_advisor_class 		= $studentRow->assigned_advisor_class;
					$student_promotable  					= $studentRow->promotable;
					$student_excluded_advisor  				= $studentRow->excluded_advisor;
					$student_student_survey_completion_date	= $studentRow->student_survey_completion_date;
					$student_available_class_days  			= $studentRow->available_class_days;
					$student_intervention_required  		= $studentRow->intervention_required;
					$student_copy_control  					= $studentRow->copy_control;
					$student_first_class_choice  			= $studentRow->first_class_choice;
					$student_second_class_choice  			= $studentRow->second_class_choice;
					$student_third_class_choice  			= $studentRow->third_class_choice;
					$student_first_class_choice_utc  		= $studentRow->first_class_choice_utc;
					$student_second_class_choice_utc  		= $studentRow->second_class_choice_utc;
					$student_third_class_choice_utc  		= $studentRow->third_class_choice_utc;
					$student_catalog_options				= $studentRow->catalog_options;
					$student_flexible						= $studentRow->flexible;
					$student_date_created 					= $studentRow->date_created;
					$student_date_updated			  		= $studentRow->date_updated;

					$myCount++;

					if ($doDebug) {
						echo "Processing $student_call_sign<br />";
					}
					if ($output_type == 'table') {
						$content	.= "<tr>";
						if ($id 	== 'X') {
							$content .= "<td style='vertical-align:top;'>$student_ID</td>";
						}
						if ($call_sign == 'X') {
							$content .= "<td style='vertical-align:top;'><a href='$siteURL/cwa-display-and-update-student-information/?request_type=callsign&request_info=$student_call_sign&request_table=$mode_type&strpass=2' target='_blank'>$student_call_sign</a></td>";
						}
						if ($first_name == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_first_name</td>";
						}
						if ($last_name == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_last_name</td>";
						}
						if ($email == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_email</td>";
						}
						if ($ph_code == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_ph_code</td>";
						}
						if ($phone == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_phone</td>";
						}
						if ($city == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_city</td>";
						}
						if ($state == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_state</td>";
						}
						if ($zip_code == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_zip_code</td>";
						}
						if ($country_code == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_country_code</td>";
						}
						if ($country == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_country</td>";
						}
						if ($time_zone == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_time_zone</td>";
						}
						if ($timezone_id == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_timezone_id</td>";
						}
						if ($timezone_offset == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_timezone_offset</td>";
						}
						if ($whatsapp == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_whatsapp</td>";
						}
						if ($signal == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_signal</td>";
						}
						if ($telegram == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_telegram</td>";
						}
						if ($messenger == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_messenger</td>";
						}
						if ($wpm == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_wpm</td>";
						}
						if ($youth == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_youth</td>";
						}
						if ($age == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_age</td>";
						}
						if ($parent == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_student_parent</td>";
						}
						if ($parent_email == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_student_parent_email</td>";
						}
						if ($level == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_level</td>";
						}
						if ($waiting_list == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_waiting_list</td>";
						}
						if ($request_date == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_request_date</td>";
						}
						if ($semester == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_semester</td>";
						}
						if ($notes == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_notes</td>";
						}
						if ($email_sent_date == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_email_sent_date</td>";
						}
						if ($email_number == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_email_number</td>";
						}
						if ($response == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_response</td>";
						}
						if ($response_date == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_response_date</td>";
						}
						if ($abandoned == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_abandoned</td>";
						}
						if ($student_status == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_student_status</td>";
						}
						if ($action_log == 'X') {
							$newActionLog = formatActionLog($student_action_log);
							$content .= "<td style='vertical-align:top;'>$newActionLog</td>";
						}
						if ($advisor == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_pre_assigned_advisor</td>";
						}
						if ($selected_date == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_selected_date</td>";
						}
						if ($welcome_date == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_welcome_date</td>";
						}
						if ($no_catalog == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_no_catalog</td>";
						}
						if ($hold_override == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_hold_override</td>";
						}
						if ($messaging == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_messaging</td>";
						}
						if ($assigned_advisor == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_assigned_advisor</td>";
						}
						if ($start_time == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_start_time</td>";
						}
						if ($advisor_class_timezone == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_advisor_class_timezone</td>";
						}
						if ($advisor_select_date == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_advisor_select_date</td>";
						}
						if ($hold_reason_code == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_hold_reason_code</td>";
						}
						if ($class_priority == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_class_priority</td>";
						}
						if ($assigned_advisor_class == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_assigned_advisor_class</td>";
						}
						if ($promotable == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_promotable</td>";
						}
						if ($excluded_advisor == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_excluded_advisor</td>";
						}
						if ($student_survey_completion_date == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_student_survey_completion_date</td>";
						}
						if ($available_class_days == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_available_class_days</td>";
						}
						if ($intervention_required == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_intervention_required</td>";
						}
						if ($copy_control == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_copy_control</td>";
						}
						if ($first_class_choice == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_first_class_choice</td>";
						}
						if ($second_class_choice == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_second_class_choice</td>";
						}
						if ($third_class_choice == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_third_class_choice</td>";
						}
						if ($first_class_choice_utc == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_first_class_choice_utc</td>";
						}
						if ($second_class_choice_utc == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_second_class_choice_utc</td>";
						}
						if ($third_class_choice_utc == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_third_class_choice_utc</td>";
						}
						if ($catalog_options == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_catalog_options</td>";
						}
						if ($flexible == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_flexible</td>";
						}
						if ($date_created == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_date_created</td>";
						}
						if ($date_updated == 'X') {
							$content .= "<td style='vertical-align:top;'>$student_date_updated</td>";
						}
						$content	.= "</tr>\n";
					} else {			// output will be a comma separated file
						$needComma = FALSE;
						if ($id == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_ID";
							$needComma = TRUE;
						}
						if ($call_sign == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_call_sign";
							$needComma = TRUE;
						}
						if ($first_name == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_first_name";
							$needComma = TRUE;
						}
						if ($last_name == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_last_name";
							$needComma = TRUE;
						}
						if ($email == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_email";
							$needComma = TRUE;
						}
						if ($ph_code == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_ph_code";
							$needComma = TRUE;
						}
						if ($phone == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_phone";
							$needComma = TRUE;
						}
						if ($city == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_city";
							$needComma = TRUE;
						}
						if ($state == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_state";
							$needComma = TRUE;
						}
						if ($country_code == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_country_code";
							$needComma = TRUE;
						}
						if ($country == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_country";
							$needComma = TRUE;
						}
						if ($time_zone == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_time_zone";
							$needComma = TRUE;
						}
						if ($timezone_id == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_timezone_id";
							$needComma = TRUE;
						}
						if ($timezone_offset == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_timezone_offset";
							$needComma = TRUE;
						}
						if ($whatsapp == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_whatsapp";
							$needComma = TRUE;
						}
						if ($signal == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_signal";
							$needComma = TRUE;
						}
						if ($telegram == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_telegram";
							$needComma = TRUE;
						}
						if ($messenger == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_messenger";
							$needComma = TRUE;
						}
						if ($wpm == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_wpm";
							$needComma = TRUE;
						}
						if ($youth == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_youth";
							$needComma = TRUE;
						}
						if ($age == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_age";
							$needComma = TRUE;
						}
						if ($parent == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_student_parent";
							$needComma = TRUE;
						}
						if ($parent_email == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_student_parent_email";
							$needComma = TRUE;
						}
						if ($level == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_level";
							$needComma = TRUE;
						}
						if ($waiting_list == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_waiting_list";
							$needComma = TRUE;
						}
						if ($request_date == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_request_date";
							$needComma = TRUE;
						}
						if ($semester == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_semester";
							$needComma = TRUE;
						}
						if ($notes == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_notes";
							$needComma = TRUE;
						}
						if ($email_sent_date == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_email_sent_date";
							$needComma = TRUE;
						}
						if ($email_number == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_email_number";
							$needComma = TRUE;
						}
						if ($response == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_response";
							$needComma = TRUE;
						}
						if ($response_date == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_response_date";
							$needComma = TRUE;
						}
						if ($abandoned == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_abandoned";
							$needComma = TRUE;
						}
						if ($student_status == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_student_status";
							$needComma = TRUE;
						}
						if ($action_log == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_action_log";
							$needComma = TRUE;
						}
						if ($advisor == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_pre_assigned_advisor";
							$needComma = TRUE;
						}
						if ($selected_date == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_selected_date";
							$needComma = TRUE;
						}
						if ($welcome_date == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_welcome_date";
							$needComma = TRUE;
						}
						if ($no_catalog == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_no_catalog";
							$needComma = TRUE;
						}
						if ($hold_override == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_hold_override";
							$needComma = TRUE;
						}
						if ($messaging == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_messaging";
							$needComma = TRUE;
						}
						if ($assigned_advisor == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_assigned_advisor";
							$needComma = TRUE;
						}
						if ($advisor_class_timezone == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_advisor_class_timezone";
							$needComma = TRUE;
						}
						if ($advisor_select_date == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_advisor_select_date";
							$needComma = TRUE;
						}
						if ($hold_reason_code == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_hold_reason_code";
							$needComma = TRUE;
						}
						if ($class_priority == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_class_priority";
							$needComma = TRUE;
						}
						if ($assigned_advisor_class == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_assigned_advisor_class";
							$needComma = TRUE;
						}
						if ($promotable == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_promotable";
							$needComma = TRUE;
						}
						if ($excluded_advisor == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_excluded_advisor";
							$needComma = TRUE;
						}
						if ($student_survey_completion_date == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_student_survey_completion_date";
							$needComma = TRUE;
						}
						if ($available_class_days == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_available_class_days";
							$needComma = TRUE;
						}
						if ($intervention_required == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_intervention_required";
							$needComma = TRUE;
						}
						if ($copy_control == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_ ";
							$needComma = TRUE;
						}
						if ($first_class_choice == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_first_class_choice";
							$needComma = TRUE;
						}
						if ($second_class_choice == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_second_class_choice";
							$needComma = TRUE;
						}
						if ($third_class_choice == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_third_class_choice";
							$needComma = TRUE;
						}
						if ($first_class_choice_utc == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_first_class_choice_utc";
							$needComma = TRUE;
						}
						if ($second_class_choice_utc == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_second_class_choice_utc";
							$needComma = TRUE;
						}
						if ($third_class_choice_utc == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_third_class_choice_utc";
							$needComma = TRUE;
						}
						if ($catalog_options == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_ ";
							$needComma = TRUE;
						}
						if ($flexible == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_ ";
							$needComma = TRUE;
						}
						if ($date_created == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_date_created";
							$needComma = TRUE;
						}
						if ($date_updated == 'X') {
							if ($needComma) {
								$content .= '	';
							}
							$content .= "$student_date_updated";
							$needComma = TRUE;
						}
						$content	.= "\n";
					}				
				}
			}
			if ($output_type == 'table') {
				$content	.= "</table>";
			} else {
				$content	.= "</pre>";
			}
			$content		.= "<br /><br />$myCount records printed<br />";
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
	$result			= write_joblog_func("Student Report Generator|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	if ($doDebug) {
		echo "<br />Testing to save report: $inp_report<br />";
	}
	if ($inp_report == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Student Report Generator<br />";
		}
		$storeResult	= storeReportData_v2("Student Report Generator",$content);
		if ($storeResult !== FALSE) {
			$content	.= "<br />Report stored in reports table as $storeResult[1]";
		} else {
			$content	.= "<br />Storing the report in the reports table failed";
		}

/*		
		$content		.= "<p><b>Download the report named $storeResult</b><br />
If the report is not going to be downloaded, you can either submit 'No' or close 
this window.</p>
<p><form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='3'>
<input type='hidden' name='inp_filename' value='$storeResult'>
Download this Report?<br />
<input type='radio' class='formInputButton' name='inp_download' value='Y' checked='checked'> Yes<br />
<input type='radio' class='formInputButton' name='inp_download' value='N'> No<br /><br />
<input class='formInputButton' type='submit' value='Submit' />
</form></p>";
*/

///////		pass 3
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "Arrived at pass three with inp_download of $inp_download<br />";
		}
		$content			.= "<h3>Downloading the File</h3>";
		if ($inp_download == 'Y') {
			$inp_filename	= str_replace(" ","",$inp_filename);
			$inp_filename	= str_replace(":","",$inp_filename); 
			$inp_filename	= str_replace("/","",$inp_filename); 
			$inp_filename	= $inp_filename . ".html";
// echo "inp_filename after replacements: $inp_filename<br />";
			$fullPath	= "/home/cwopsorg/CWAT/$inp_filename";
			if(file_exists($fullPath)) {
				//Define header information
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: 0");
				header('Content-Disposition: attachment; filename="'.$fullPath.'"');
				header('Content-Length: ' . filesize($fullPath));
				header('Pragma: public');

				//Clear system output buffer
//				flush();

				//Read the size of the file
				readfile($fullPath);

				$content		.= "<p>File $fullPath was downloaded<p>";
			} else {
				$content		.= "<p>File $fullPath does not exist.</p>";
			}
		}
	}
		
	return $content;
}
add_shortcode ('student_report_generator', 'student_report_generator_func');
