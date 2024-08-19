function display_and_update_student_info_func() {

/*
 * Function to display and update the information about a student record
 *


	modified 2020-01-08 by Roland to allow deletion of a record
	modified 2020-02-04 by Roland to allow selection of table to be accessed
	modified 2020-02-12 by Roland to allow update of past_student to wr7q only
	mod 15feb20 Bob c - add find by email also
	mod 18feb20 Bob c - stop delete cababilities
	mod 3Mar20 Roland changed class_completed_date to excluded_advisor
		and take_previous_class to hold_reason_code
	mod 5Mar20 to replace last_passed_over_date with hold_override
	mod 27May20 by Roland to add radio buttons for which student table to be processed
	mod 1Jun20 by Roland to allow everyone access to past_student
	mod 6Aug20 by Roland to allow display of the post title
	Modified 15Dec2020 by Roland to add the new student fields
	Modified 1Feb2021 to change student_code to messaging
	Modified 23Nov2021 by Roland to create v2 to see if that solves the 500 error problem
	Modified 26Dec2021 by Roland to use database tables rather than the student table
	Modified 31Dec21 by Roland to use student table instead of tables
	Modified 24May22 by Roland to copy record to a deleted table before deleting
	Modified 15Apr23 by Roland to fix action_log
	Modified 17May23 by Roland to add ability to move a student to past_student
	Modified 13Jul23 by Roland to use consolidated tables
	Modified 3Sep23 by Roland to add catalog_options and flexible fields

*/

//	echo "at the beginning of the snippet<br />";

	global $wpdb;

	$doDebug 						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$userName  						= $initializationArray['userName'];
	$validTestmode					= $initializationArray['validTestmode'];
	$siteURL						= $initializationArray['siteurl'];

	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}


// initial values	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$requestType					= "";
	$requestInfo					= "";
	$strPass						= "1";
	$actionDate						= date('dMY H:i');
	$theURL							= "$siteURL/cwa-display-and-update-student-information/";
	$fieldTest						= array('action_log','post_status','post_title','control_code');
	$logDate						= date('Y-m-d H:i');
	$inp_first_class_choice_utc		= "";
	$inp_second_class_choice_utc	= "";
	$inp_third_class_choice_utc		= "";	
	$inp_verbose					= "";
	$inp_level						= "";
	$jobname						= "Display and Update Student Info";
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
			}
			if ($str_key 		== "studentid") {
				$studentID		 = $str_value;
			}
			if ($str_key 		== "request_info") {
				$requestInfo	 = trim($str_value);
//				$requestInfo	 = filter_var($requestInfo,FILTER_UNSAFE_RAW);
				$requestInfo	 = no_magic_quotes($requestInfo);
			}
			if ($str_key 		== "request_type") {
				$requestType	 = $str_value;
			}
			if ($str_key 		== trim("inp_call_sign")) {
				$inp_call_sign		= strtoupper($str_value);
			}
			if ($str_key 		== "inp_first_name") {
				$inp_first_name		= $str_value;
				$inp_first_name		= filter_var($inp_first_name,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_last_name") {
				$inp_last_name		= trim($str_value);
				$inp_last_name		= no_magic_quotes($inp_last_name);
			}
			if ($str_key 		== "inp_email") {
				$inp_email		= trim($str_value);
			}
			if ($str_key 		== "inp_phone") {
				$inp_phone		= trim($str_value);
			}
			if ($str_key 		== "inp_city") {
				$inp_city		= trim($str_value);
			}
			if ($str_key 		== "inp_state") {
				$inp_state		= trim($str_value);
			}
			if ($str_key 		== "inp_zip_code") {
				$inp_zip_code		= trim($str_value);
			}
			if ($str_key 		== "inp_country") {
				$inp_country	= trim($str_value);
			}
			if ($str_key 		== "inp_time_zone") {
				$inp_time_zone		= trim($str_value);
			}
			if ($str_key 		== "inp_wpm") {
				$inp_wpm		= $str_value;
			}
			if ($str_key 		== "inp_youth") {
				$inp_youth		= $str_value;
			}
			if ($str_key 		== "inp_age") {
				$inp_age		= $str_value;
			}
			if ($str_key 		== "inp_student_parent") {
				$inp_student_parent		= $str_value;
			}
			if ($str_key 		== "inp_student_parent_email") {
				$inp_student_parent_email		= trim($str_value);
			}
			if ($str_key 		== "inp_level") {
				$inp_level		= trim($str_value);
			}
			if ($str_key 		== "inp_request_date") {
				$inp_request_date		= $str_value;
			}
			if ($str_key 		== "inp_semester") {
				$inp_semester		= trim($str_value);
			}
			if ($str_key 		== "inp_notes") {
				$inp_notes		= addslashes($str_value);
			}
			if ($str_key 		== "inp_email_sent_date") {
				$inp_email_sent_date		= $str_value;
			}
			if ($str_key 		== "inp_email_number") {
				$inp_email_number		= $str_value;
			}
			if ($str_key 		== "inp_response") {
				$inp_response		= strtoupper($str_value);
			}
			if ($str_key 		== "inp_response_date") {
				$inp_response_date		= $str_value;
			}
			if ($str_key 		== "inp_abandoned") {
				$inp_abandoned		= $str_value;
			}
			if ($str_key 		== "inp_student_status") {
				$inp_student_status	= strtoupper($str_value);
			}
			if ($str_key 		== "inp_action_log") {
				$inp_action_log	= $str_value;
			}
			if ($str_key 		== "inp_pre_assigned_advisor") {
				$inp_pre_assigned_advisor		= trim(strtoupper($str_value));
			}
			if ($str_key 		== "inp_selected_date") {
				$inp_selected_date		= $str_value;
			}
			if ($str_key 		== "inp_welcome_date") {
				$inp_welcome_date		= $str_value;
			}
			if ($str_key 		== "inp_no_catalog") {
				$inp_no_catalog		= $str_value;
			}
			if ($str_key 		== "inp_hold_override") {
				$inp_hold_override		= $str_value;
			}
			if ($str_key 		== "inp_messaging") {
				$inp_messaging	= $str_value;
			}
			if ($str_key 		== "inp_assigned_advisor") {
				$inp_assigned_advisor	= strtoupper(trim($str_value));
			}
			if ($str_key 		== "inp_hold_reason_code") {
				$inp_hold_reason_code		= $str_value;
			}
			if ($str_key 		== "inp_class_priority") {
				$inp_class_priority		= $str_value;
			}
			if ($str_key 		== "inp_assigned_advisor_class") {
				$inp_assigned_advisor_class		= $str_value;
			}
			if ($str_key 		== "inp_promotable") {
				$inp_promotable		= $str_value;
			}
			if ($str_key 		== "inp_excluded_advisor") {
				$inp_excluded_advisor		= strtoupper(trim($str_value));
			}
			if ($str_key 		== "inp_student_survey_completion_date") {
				$inp_student_survey_completion_date		= $str_value;
			}
			if ($str_key 		== "inp_available_class_days") {
				$inp_available_class_days		= $str_value;
			}
			if ($str_key 		== "request_table") {
				$request_table	= $str_value;
				if ($request_table == 'wpw1_cwa_consolidated_student') {
					$studentDeletedTableName	= 'wpw1_cwa_student_deleted';
				} else {
					$studentDeletedTableName	= 'wpw1_cwa_student_deleted2';
					$testMode					= TRUE;
				}
			}
			if ($str_key 		== "inp_waiting_list") {
				$inp_waiting_list	= $str_value;
			}
			if ($str_key 		== "inp_advisor_class_timezone") {
				$inp_advisor_class_timezone	= $str_value;
			}
			if ($str_key 		== "inp_advisor_select_date") {
				$inp_advisor_select_date	= $str_value;
			}
			if ($str_key 		== "inp_intervention_required") {
				$inp_intervention_required	= $str_value;
			}
			if ($str_key 		== "inp_first_class_choice") {
				$inp_first_class_choice	= $str_value;
			}
			if ($str_key 		== "inp_second_class_choice") {
				$inp_second_class_choice	= $str_value;
			}
			if ($str_key 		== "inp_third_class_choice") {
				$inp_third_class_choice	= $str_value;
			}
			if ($str_key 		== "inp_first_class_choice_utc") {
				$inp_first_class_choice_utc	= $str_value;
			}
			if ($str_key 		== "inp_second_class_choice_utc") {
				$inp_second_class_choice_utc	= $str_value;
			}
			if ($str_key 		== "inp_third_class_choice_utc") {
				$inp_third_class_choice_utc	= $str_value;
			}
			if ($str_key 		== "inp_timezone_id") {
				$inp_timezone_id	= $str_value;
			}
			if ($str_key 		== "inp_timezone_offset") {
				$inp_timezone_offset	= $str_value;
			}
			if ($str_key 		== "inp_whatsapp") {
				$inp_whatsapp	= $str_value;
			}
			if ($str_key 		== "inp_signal") {
				$inp_signal	= $str_value;
			}
			if ($str_key 		== "inp_messenger") {
				$inp_messenger	= $str_value;
			}
			if ($str_key 		== "inp_ph_code") {
				$inp_ph_code	= $str_value;
			}
			if ($str_key 		== "inp_country_code") {
				$inp_country_code	= $str_value;
			}
			if ($str_key 		== "inp_telegram") {
				$inp_telegram	= $str_value;
			}
			if ($str_key 		== "inp_catalog_options") {
				$inp_catalog_options	 = $str_value;
				$inp_catalog_options	 = filter_var($inp_catalog_options,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_flexible") {
				$inp_flexible	 = $str_value;
				$inp_flexible	 = filter_var($inp_flexible,FILTER_UNSAFE_RAW);
			}
		}
	}
	
//	echo "including styles<br />";
	
	
// The content to be returned initially includes the special style information.
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

table{font:'Times New Roman', sans-serif;background-image:none;}

th {color:#ffff;background-color:#000;padding:5px;font-size:small;}

td {padding:5px;font-size:small;}
</style>";	
	
if ($testMode) {
	$content		.= "<p><b>Operating in testMode</b></p>";
}
	
	
/*
 * When strPass is equal to 1 then get the information needed to access the student
 * The student can be accessed by the studentID, callsign, surname or email
 *
*/
	if ("1" == $strPass) {
	
		$content .= "<p>Please select the type of request and enter the value to be searched 
in the Student Table. Call sign can be either upper case or lower case. Last name must be 
an exact match.</p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data''>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td style='width:150px;'>Request Type</td>
	<td><input class='formInputButton' type='radio' name='request_type' value='callsign' checked>Call Sign<br />
		<input class='formInputButton' type='radio' name='request_type' value='studentid'>Student ID<br />
		<input class='formInputButton' type='radio' name='request_type' value='surname'>Surname<br />
		<input class='formInputButton' type='radio' name='request_type' value='email'>Email</td></tr>
<tr><td>RequestInfo</td>
	<td><input class='formInputText' type='text' maxlength='50' name='request_info' size='30' autofocus></td></tr>
<tr><td>Table</td>
	<td><input class='formInputButton' type='radio' name='request_table' value='wpw1_cwa_consolidated_student' checked>Student Table<br />
		<input class='formInputButton' type='radio' name='request_table' value='wpw1_cwa_consolidated_student2' >Student2 Table<br />
		<input class='formInputButton' type='radio' name='request_table' value='wpw1_cwa_old_student' >Old Student Table</td></tr>";

		if (in_array($userName,$validTestmode)) {
			$content .= "<tr><td>Verbose Debugging?</td>
						<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
							<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
		}

		$content .= "<tr><td>&nbsp;</td><td><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form>";
		
		
//		echo "content should be returned<br />";
			
//// pass 2 display student information		
		
	} elseif ("2" == $strPass) {
	
		if ($requestType == "callsign") {
			$requestInfo = strtoupper($requestInfo);
		}
		if ($doDebug) {
			echo "Supplied input: Request Type: $requestType; Request Info: $requestInfo<br />";
		}

// Set up the data request
		if ($requestType == "callsign") {
			$sql	= "select * from $request_table 
						where call_sign='$requestInfo' 
						order by date_created DESC";
		} elseif ($requestType == "studentid") {
			$sql	= "select * from $request_table 
						where student_ID=$requestInfo";
		} elseif ($requestType == "surname") {	
			$myInt	= strpos($requestInfo,"'");
			if ($myInt !== FALSE) {
				$new_info	= substr($requestInfo,$myInt+1);
				$new_info	= "%$new_info%";
				if ($doDebug) {
					echo "requestInfo has an apostrophe. Searching for $new_info<br />";
				}
			} else {
				$new_info	= "%$requestInfo%";
			}
			$sql	= "select * from $request_table where 
						last_name like '$new_info' 
						order by date_created DESC";
		} elseif ($requestType == "email") {	
			$sql	= "select * from $request_table 
						where email='$requestInfo' 
						order by date_created DESC, call_sign";
		} else {
			echo "Hmmm ... requestType of $requestType didn't compute<br />";
		}

		$wpw1_cwa_student				= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $studentTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_phone  						= $studentRow->phone;
					$student_ph_code						= $studentRow->ph_code;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_country_code					= $studentRow->country_code;
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

					$student_last_name 						= no_magic_quotes($student_last_name);
					$newActionLog							= formatActionLog($student_action_log);
					$content .= "<h3>Database Content for $student_call_sign in Table $request_table</h3>
								<p><a href='$theURL'>Look Up Another Student</a></p><br />
								<table style='border-collapse:collapse;'>
								<tr><th style='width:200px;'>Field</th><th>Value</th></tr>
								<tr><td>Student ID</td><td><a href='$theURL?studentid=$student_ID&strpass=3&request_table=$request_table&inp_verbose=$inp_verbose'>$student_ID</a></td></tr>
								<tr><td>Call Sign</td><td>$student_call_sign</td></tr>
								<tr><td>First Name</td><td>$student_first_name</td></tr>
								<tr><td>Last Name</td><td>$student_last_name</td></tr>
								<tr><td>Email</td><td>$student_email</td></tr>
								<tr><td>Ph Code</td><td>$student_ph_code</td></tr>
								<tr><td>Phone</td><td>$student_phone</td></tr>
								<tr><td>City</td><td>$student_city</td></tr>
								<tr><td>State</td><td>$student_state</td></tr>
								<tr><td>Zip Code</td><td>$student_zip_code</td></tr>
								<tr><td>Country</td><td>$student_country</td></tr>
								<tr><td>Country Code</td><td>$student_country_code</td></tr>
								<tr><td>Time Zone<td>$student_time_zone</td></tr>
								<tr><td>Timezone ID<td>$student_timezone_id</td></tr>
								<tr><td>Timezone Offset<td>$student_timezone_offset</td></tr>
								<tr><td>Whatsapp</td><td>$student_whatsapp</td></tr>
								<tr><td>Signal</td><td>$student_signal</td></tr>
								<tr><td>Telegram</td><td>$student_telegram</td></tr>
								<tr><td>Messenger</td><td>$student_messenger</td></tr>
								<tr><td>WPM</td><td>$student_wpm</td></tr>
								<tr><td>Youth</td><td>$student_youth</td></tr>
								<tr><td>Age</td><td>$student_age</td></tr>
								<tr><td>Parent</td><td>$student_student_parent</td></tr>
								<tr><td>Parent Email</td><td>$student_student_parent_email</td></tr>
								<tr><td>Level</td><td>$student_level</td></tr>
								<tr><td>Waiting List</td><td>$student_waiting_list</td></tr>
								<tr><td>Request Date</td><td>$student_request_date</td></tr>
								<tr><td>Semester</td><td>$student_semester</td></tr>
								<tr><td>Advisor Notes</td><td>$student_notes</td></tr>
								<tr><td>Email Sent Date</td><td>$student_email_sent_date</td></tr>
								<tr><td>Email Number</td><td>$student_email_number</td></tr>
								<tr><td>Response</td><td>$student_response</td></tr>
								<tr><td>Response Date</td><td>$student_response_date</td></tr>
								<tr><td>Abandoned</td><td>$student_abandoned</td></tr>
								<tr><td>Student Status</td><td>$student_student_status</td></tr>
								<tr><td style='vertical-align:top;'>Action Log</td><td>$newActionLog</td></tr>
								<tr><td>Pre-assigned Advisor</td><td>$student_pre_assigned_advisor</td></tr>
								<tr><td>Selected Date</td><td>$student_selected_date</td></tr>
								<tr><td>Welcome Date</td><td>$student_welcome_date</td></tr>
								<tr><td>No Catalog</td><td>$student_no_catalog</td></tr>
								<tr><td>Hold Override</td><td>$student_hold_override</td></tr>
								<tr><td>Messaging</td><td>$student_messaging</td></tr>
								<tr><td>Assigned Advisor</td><td>$student_assigned_advisor</td></tr>
								<tr><td>Advisor Class Timezone</td><td>$student_advisor_class_timezone</td></tr>
								<tr><td>Advisor Select Date</td><td>$student_advisor_select_date</td></tr>
								<tr><td>Hold Reason Code</td><td>$student_hold_reason_code</td></tr>
								<tr><td>Class Priority</td><td>$student_class_priority</td></tr>
								<tr><td>Assigned Advisor Class</td><td>$student_assigned_advisor_class</td></tr>
								<tr><td>Promotable</td><td>$student_promotable</td></tr>
								<tr><td>Excluded Advisor</td><td>$student_excluded_advisor</td></tr>
								<tr><td>Student Survey Completion Date</td><td>$student_student_survey_completion_date</td></tr>
								<tr><td>Available Class Days</td><td>$student_available_class_days</td></tr>
								<tr><td>Intervention Required</td><td>$student_intervention_required</td></tr>
								<tr><td>First Class Choice</td><td>$student_first_class_choice</td></tr>
								<tr><td>Second Class Choice</td><td>$student_second_class_choice</td></tr>
								<tr><td>Third Class Choice</td><td>$student_third_class_choice</td></tr>
								<tr><td>First Class Choice UTC</td><td>$student_first_class_choice_utc</td></tr>
								<tr><td>Second Class Choice UTC</td><td>$student_second_class_choice_utc</td></tr>
								<tr><td>Third Class Choice UTC</td><td>$student_third_class_choice_utc</td></tr>
								<tr><td>Catalog Options</td><td>$student_catalog_options</td></tr>
								<tr><td>Flexible</td><td>$student_flexible</td></tr>
								<tr><td>Date Created</td><td>$student_date_created</td></tr>
								<tr><td>Date Updated</td><td>$student_date_updated</td></tr>
								</table>";

						$content			.= "<table>
												<tr><td style='width:150px;'><form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data''>
													<input type='hidden' name='strpass' value='3'>
													<input type='hidden' name='request_table' value='$request_table'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='studentid' value='$student_ID'>
													<input type='submit' class='formInputButton' value='UPDATE Student'></form></td>
												<td style='width:150px;'><form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data''>
													<input type='hidden' name='strpass' value='5'>
													<input type='hidden' name='request_table' value='$request_table'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='studentid' value='$student_ID'>
													<input type='hidden' name='inp_semester' value='$student_semester'>
													<input type='hidden' name='inp_call_sign' value='$student_call_sign'>
													<input type='hidden' name='inp_assigned_advisor' value='$student_assigned_advisor'>
													<input type='hidden' name='inp_assigned_advisor_class' value='$student_assigned_advisor_class'>
													<input type='submit' class='formInputButton' 
													onclick=\"return confirm('Are you sure you want to delete this student id?');\" value='DELETE Student'></form></td>
												</tr>
												</table>";
				}	
			} else {
				if ($doDebug) {
					echo "No records found matching $requestInfo criteria<br />";
				}
				$content			.= "<b>No</b> record found in $request_table for $requestInfo<br />";
			}
		}

////// pass 3 update the student information


	} elseif ("3" == $strPass) {

// display the request record to be modified
		$tableCount 							= 1;	
		$sql						= "select * from $request_table 
										where student_id=$studentID";
		$wpw1_cwa_student				= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $studentTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_phone  						= $studentRow->phone;
					$student_ph_code						= $studentRow->ph_code;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_country_code					= $studentRow->country_code;
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

					$student_last_name 						= no_magic_quotes($student_last_name);

					$content 							.= "<h3>Update $student_call_sign Student Information in Table $request_table</h3><br />";
					$beginnerChecked		= '';
					$fundamentalChecked			= '';
					$intermediateChecked	= '';
					$advancedChecked		= '';
					if ($student_level == 'Beginner') {
						$beginnerChecked	= "checked='checked'";
					} elseif ($student_level == 'Fundamental') {
						$fundamentalChecked		= "checked='checked'";
					} elseif ($student_level == 'Intermediate') {
						$intermediateChecked		= "checked='checked'";
					} elseif ($student_level == 'Advanced') {
						$advancedChecked		= "checked='checked'";
					}	
					$yYesChecked			= '';
					$yNoChecked				= "checked=checked";
					if ($student_youth == 'No') {
						$yNoChecked			= "checked=checked";
						$yYesChecked		= '';
					} elseif ($student_youth == 'Yes') {
						$yYesChecked		= "checked=checked";
						$yNoChecked			= "";
					}

					$content .= "<table style='border-collapse:collapse;'>
								<p><a href='$theURL'>Look Up Another Student</a></p><br />
								<form method='post' name='selection_form' action='$theURL' ENCTYPE='multipart/form-data'>
								<tr><th style='width:200px;'>Field</th><th>Value</th></tr>\n
								<input type='hidden' name='strpass' value='4'>
								<input type='hidden' name='studentid' value='$student_ID'>
								<input type='hidden' name='request_table' value='$request_table'>
								<input type='hidden' name='inp_first_class_choice_utc' value='$student_first_class_choice_utc'>
								<input type='hidden' name='inp_second_class_choice_utc' value='$student_second_class_choice_utc'>
								<input type='hidden' name='inp_third_class_choice_utc' value='$student_third_class_choice_utc'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<tr><td>Call Sign</td>
									<td><input class='formInputText' type='text' name='inp_call_sign' size='15' maxlenth='15' value='$student_call_sign'></td></tr>\n
								<tr><td>First Name</td><td>
									<input class='formInputText' type='text' name='inp_first_name' size='50' maxlenth='100' value='$student_first_name'></td></tr>\n
								<tr><td>Last Name</td>
									<td><input class='formInputText' type='text' name='inp_last_name' size='50' maxlenth='100' value=\"$student_last_name\"></td></tr>\n
								<tr><td>Email</td>
									<td><input class='formInputText' type='text' name='inp_email' size='50' maxlenth=100' value='$student_email'></td></tr>\n
								<tr><td>Ph Code</td>
									<td><input class='formInputText' type='text' name='inp_ph_code' size='5' maxlenth='5' value='$student_ph_code'></td></tr>\n
								<tr><td>Phone</td>
									<td><input class='formInputText' type='text' name='inp_phone' size='20' maxlenth='50' value='$student_phone'></td></tr>\n
								<tr><td>City</td>
									<td><input class='formInputText' type='text' name='inp_city' size='50' maxlenth='100' value='$student_city'></td></tr>\n
								<tr><td>State</td>
									<td><input class='formInputText' type='text' name='inp_state' size='50' maxlenth='100' value='$student_state'</td></tr>\n
								<tr><td>Zip Code</td>
									<td><input class='formInputText' type='text' name='inp_zip_code' size='10' maxlenth='15' value='$student_zip_code'</td></tr>\n
								<tr><td>Country</td>
									<td><input class='formInputText' type='text' name='inp_country' size='50' maxlenth='100' value='$student_country'</td></tr>\n
								<tr><td>Country Code</td>
									<td><input class='formInputText' type='text' name='inp_country_code' size='5' maxlenth='5' value='$student_country_code'</td></tr>\n
								<tr><td>Time Zone</td>
									<td><input class='formInputText' type='text' name='inp_time_zone' size='5' maxlenth='5' value='$student_time_zone'></td></tr>\n
								<tr><td>Timezone ID</td>
									<td><input class='formInputText' type='text' name='inp_timezone_id' size='50' maxlenth='50' value='$student_timezone_id'></td></tr>\n
								<tr><td>Timezone Offset</td>
									<td><input class='formInputText' type='text' name='inp_timezone_offset' size='10' maxlenth='10' value='$student_timezone_offset'></td></tr>\n
								<tr><td>Whatsapp</td>
									<td><input class='formInputText' type='text' name='inp_whatsapp' size='50' maxlenth='50' value='$student_whatsapp'></td></tr>\n
								<tr><td>Signal</td>
									<td><input class='formInputText' type='text' name='inp_signal' size='50' maxlenth='50' value='$student_signal'></td></tr>\n
								<tr><td>Telegram</td>
									<td><input class='formInputText' type='text' name='inp_telegram' size='50' maxlenth='50' value='$student_telegram'></td></tr>\n
								<tr><td>Messenger</td>
									<td><input class='formInputText' type='text' name='inp_messenger' size='50' maxlenth='50' value='$student_messenger'></td></tr>\n
								<tr><td>WPM</td>
									<td><input class='formInputText' type='text' name='inp_wpm' size='5' maxlenth='5' value='$student_wpm'></td></tr>\n
								<tr><td style='vertical-align:top;'>Youth</td>
									<td><input class='formInputText' type='radio' name='inp_youth' value='Yes' $yYesChecked>Yes<br />
										<input class='formInputText' type='radio' name='inp_youth' value='No' $yNoChecked>No</td></tr>
								<tr><td>Age</td>
									<td><input class='formInputText' type='text' name='inp_age' size='5' maxlength='5' value='$student_age'></td></tr>\n
								<tr><td>Parent</td>
									<td><input class='formInputText' type='text' name='inp_student_parent' size='50' maxlenth='50' value='$student_student_parent'</td></tr>
								<tr><td>Parent Email</td>
									<td><input class='formInputText' type='text' name='inp_student_parent_email' size='50' maxlength='100' value='$student_student_parent_email'></td></tr>
								<tr><td style='vertical-align:top;'>Level</td>
									<td><input class='formInputText' type='radio' name='inp_level' value='Beginner' $beginnerChecked>Beginner<br />
										<input class='formInputText' type='radio' name='inp_level' value='Fundamental' $fundamentalChecked>Fundamental<br />
										<input class='formInputText' type='radio' name='inp_level' value='Intermediate' $intermediateChecked>Intermediate<br />
										<input class='formInputText' type='radio' name='inp_level' value='Advanced' $advancedChecked>Advanced</td></tr>
								<tr><td>Waiting List</td>
									<td><input class='formInputText' type='text' name='inp_waiting_list' size='5' maxlength='5' value='$student_waiting_list'></td></tr>\n
								<tr><td>Request Date</td>
									<td><input class='formInputText' type='text' name='inp_request_date' size='20' maxlength='20' value='$student_request_date'></td></tr>\n
								<tr><td>Semester</td>
									<td><input class='formInputText' type='text' name='inp_semester' size='15' maxlength='15' value='$student_semester'></td></tr>\n
								<tr><td style='vertical-align:top;'>Advisor Notes</td>
									<td><textarea rows='4' cols='50' name='inp_notes' class='formInputText'>$student_notes</textarea></td></tr>\n
								<tr><td>Email Sent Date</td>
									<td><input class='formInputText' type='text' name='inp_email_sent_date' size='10' maxlength='10' value='$student_email_sent_date'></td></tr>\n
								<tr><td>Email Number</td>
									<td><input class='formInputText' type='text' name='inp_email_number' size='5' maxlength='5' value='$student_email_number'></td></tr>\n
								<tr><td>Response</td>
									<td><input class='formInputText' type='text' name='inp_response' size='2' maxlenth='2' value='$student_response'></td></tr>\n
								<tr><td>Response Date</td>
									<td><input class='formInputText' type='text' name='inp_response_date' size='10' maxlength='10' value='$student_response_date'></td></tr>\n
								<tr><td>Abandoned</td>
									<td><input class='formInputText' type='text' name='inp_abandoned' size='5' maxlength='5' value='$student_abandoned'></td></tr>\n
								<tr><td>Student Status</td>
									<td><input class='formInputText' type='text' name='inp_student_status' size='50' maxlength='100' value='$student_student_status'></td></tr>\n
								<tr><td style='vertical-align:top;'>Action Log</td>
									<td><textarea rows='10' cols='50' class='formInputText' name='inp_action_log'>$student_action_log</textarea></td></tr>\n
								<tr><td>Pre-assigned Advisor</td>
									<td><input class='formInputText' type='text' name='inp_pre_assigned_advisor' length='50' maxlength='100' value='$student_pre_assigned_advisor'></td></tr>\n
								<tr><td>Selected Date</td>
									<td><input class='formInputText' type='text' name='inp_selected_date' size='10' maxlength-'10' value='$student_selected_date'></td></tr>\n
								<tr><td>Welcome Date</td>
									<td><input class='formInputText' type='text' name='inp_welcome_date' size='10' maxlength='10' value='$student_welcome_date'></td></tr>\n
								<tr><td>No Catalog</td>
									<td><input class='formInputText' type='text' name='inp_no_catalog' size='5' maxlength='5' value='$student_no_catalog'></td></tr>\n
								<tr><td>Hold Override</td>
									<td><input class='formInputText' type='text' name='inp_hold_override' size='10' maxlength='10' value='$student_hold_override'></td></tr>\n
								<tr><td>Messaging</td>
									<td><input class='formInputText' type='text' name='inp_messaging' size='10' maxlength='10' value='$student_messaging'></td></tr>\n
								<tr><td>Assigned Advisor</td>
									<td><input class='formInputText' type='text' name='inp_assigned_advisor' size='10' maxlength='10' value='$student_assigned_advisor'></td></tr>\n
								<tr><td>Advisor Class Timezone</td>
									<td><input class='formInputText' type='text' name='inp_advisor_class_timezone' size='10' maxlength='10' value='$student_advisor_class_timezone'></td></tr>\n
								<tr><td>Advisor Select Date</td>
									<td><input class='formInputText' type='text' name='inp_advisor_select_date' size='15' maxlength='15' value='$student_advisor_select_date'></td></tr>\n
								<tr><td>Hold Reason Code</td>
									<td><input class='formInputText' type='text' name='inp_hold_reason_code' size='2' maxlength='2' value='$student_hold_reason_code'></td></tr>\n
								<tr><td>Class Priority</td>
									<td><input class='formInputText' type='text' name='inp_class_priority' size='5' maxlength='5' value='$student_class_priority'</td></tr>\n
								<tr><td>Assigned Advisor Class</td>
									<td><input class='formInputText' type='text' name='inp_assigned_advisor_class' size='5' maxlength='5' value='$student_assigned_advisor_class'></td></tr>\n
								<tr><td>Promotable</td>
									<td><input class='formInputText' type='text' name='inp_promotable' size='2' maxlength='2' value='$student_promotable'></td></tr>\n
								<tr><td>Excluded Advisor</td>
									<td><input class='formInputText' type='text' name='inp_excluded_advisor' size='50' maxlength='160' value='$student_excluded_advisor'></td></tr>\n
								<tr><td>Student Survey Completion Date</td>
									<td><input class='formInputText' type='text' name='inp_student_survey_completion_date' size='10' maxlength='10' value='$student_student_survey_completion_date'></td></tr>\n
								<tr><td>Available Class Days</td>
									<td><input class='formInputText' type='text' name='inp_available_class_days' size='100' maxlength='255' value='$student_available_class_days'></td></tr>\n
								<tr><td>Intervention Required</td>
									<td><input class='formInputText' type='text' name='inp_intervention_required' size='5' maxlength='5' value='$student_intervention_required'></td></tr>\n
								<tr><td>First Class Choice</td>
									<td><input class='formInputText' type='text' name='inp_first_class_choice' size='50' maxlength='50' value='$student_first_class_choice'></td></tr>\n
								<tr><td>Second Class Choice</td>
									<td><input class='formInputText' type='text' name='inp_second_class_choice' size='50' maxlength='50' value='$student_second_class_choice'></td></tr>\n
								<tr><td>Third Class Choice</td>
									<td><input class='formInputText' type='text' name='inp_third_class_choice' size='50' maxlength='50' value='$student_third_class_choice'></td></tr>\n
								<tr><td>First Class Choice UTC</td>
									<td><input class='formInputText' type='text' name='inp_first_class_choice_utc' size='50' maxlength='50' value='$student_first_class_choice_utc'></td></tr>\n
								<tr><td>Second Class Choice UTC</td>
									<td><input class='formInputText' type='text' name='inp_second_class_choice_utc' size='50' maxlength='50' value='$student_second_class_choice_utc'></td></tr>\n
								<tr><td>Third Class Choice UTC</td>
									<td><input class='formInputText' type='text' name='inp_third_class_choice_utc' size='50' maxlength='50' value='$student_third_class_choice_utc'></td></tr>\n
								<tr><td>Catalog Options</td>
									<td><input class='formInputText' type='text' name='inp_catalog_options' size='50' maxlength='100' value='$student_catalog_options'></td></tr>\n
								<tr><td>Flexible</td>
									<td><input class='formInputText' type='text' name='inp_flexible' size='5' maxlength='5' value='$student_flexible'></td></tr>\n
								<tr><td>&nbsp;</td>
									<td><input class='formInputButton' type='submit' value='Submit' /></td></tr>
								</table>\n
								<p>Note: UTC times are not automatically calculated. The program assumes you know what you are doing.</p>";
				}
			} else {
				if ($doDebug) {
					echo "no $request_table record found with id $studentID<br />";
				}
				$content		.= "No record found with id $studentID<br />";
			}
		}
	
////// pass 4 write the updated information to the student table

	} elseif ("4" == $strPass) {
		$content					.= "<h3>Updating Student Record</h3>";
		$tableCount 				= 1;	
		$sql						= "select * from $request_table where student_id=$studentID";
		$wpw1_cwa_student				= $wpdb->get_results($sql);
		if ($doDebug) {
			echo "Reading $request_table table for student_id of $studentID<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "wpdb->last_error: " . $wpdb->last_error . "<br />";
		}
		if ($wpw1_cwa_student !== FALSE) {
			$numSRows									= $wpdb->num_rows;
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_phone  						= $studentRow->phone;
					$student_ph_code						= $studentRow->ph_code;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_country_code					= $studentRow->country_code;
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

					$student_last_name 						= no_magic_quotes($student_last_name);
			
					$content								.= "<h3>Results of the Update of Student ID $student_ID ($student_call_sign) in table $request_table</h3>";
					$doTheUpdate 							= FALSE;
					$updateData								= array();
					$updateArray							= array();
					$updateFormat							= array();
					$significantChange						= FALSE;
					$zipUpdate								= FALSE;
					$timezoneChange							= FALSE;
					$classChoiceUpdate						= FALSE;
					if ($inp_call_sign != $student_call_sign) { 
						$doTheUpdate						= TRUE;
						$updateData['call_sign']			= $inp_call_sign;
						$updateArray[]						= "call_sign|$inp_call_sign|s";
						$content 							.= "Updating current call sign of $student_call_sign to $inp_call_sign.<br />";
						if ($doDebug) {
							echo "Changed call_sign from $student_call_sign to $inp_call_sign<br />";
						}
					}
					if ($inp_first_name != $student_first_name) { 
						$doTheUpdate							= TRUE;
						$updateData['first_name']				= $inp_first_name;
						$updateArray[]							= "first_name|$inp_first_name|s";
						$content 								.= "Updating current first name of $student_first_name to $inp_first_name.<br />";
						if ($doDebug) {
							echo "Changed first_name from $student_first_name to $inp_first_name<br />";
						}
					}
					if ($inp_last_name != $student_last_name) { 
						$doTheUpdate							= TRUE;
						$newName								= $inp_last_name;
						$oldName								= $student_last_name;
						$inp_last_name							= addslashes($inp_last_name);
						$updateData['last_name']				= $inp_last_name;
						$updateArray[]							= "last_name|$inp_last_name|s";
						$content 								.= "Updating current last name of $oldName to $newName.<br />";
						if ($doDebug) {
							echo "Changed last_name from $student_last_name to $inp_last_name<br />";
						}
					}
					if ($inp_email != $student_email) { 
						$doTheUpdate							= TRUE;
						$updateData['email']					= $inp_email;
						$updateArray[]							= "email|$inp_email|s";
						$content 								.= "Updating current email of $student_email to $inp_email.<br />";
						if ($doDebug) {
							echo "Changed email from $student_email to $inp_email<br />";
						}
					}
					if (strcmp($inp_ph_code,$student_ph_code) != 0) { 
						$doTheUpdate							= TRUE;
						$updateData['ph_code']					= $inp_ph_code;
						$updateArray[]							= "ph_code|$inp_ph_code|s";
						$content 								.= "Updating current ph_code of $student_ph_code to $inp_ph_code.<br />";
						if ($doDebug) {
							echo "Changed ph_code from $student_ph_code to $inp_ph_code<br />";
						}
					}
					if (strcmp($inp_phone,$student_phone) != 0) { 
						$doTheUpdate							= TRUE;
						$updateData['phone']					= $inp_phone;
						$updateArray[]							= "phone|$inp_phone|s";
						$content 								.= "Updating current phone of $student_phone to $inp_phone.<br />";
						if ($doDebug) {
							echo "Changed phone from $student_phone to $inp_phone<br />";
						}
					}
					if ($inp_city != $student_city) {
						$doTheUpdate							= TRUE;
						$updateData['city']						= $inp_city;
						$updateArray[]							= "city|$inp_city|s";
						$content 								.= "Updating current city of $student_city to $inp_city.<br />";
						if ($doDebug) {
							echo "Changed city from $student_city to $inp_city<br />";
						}
					}
					if ($inp_state != $student_state) { 
						$doTheUpdate							= TRUE;
						$updateData['state']					= $inp_state;
						$updateArray[]							= "state|$inp_state|s";
						$content 								.= "Updating current state of $student_state to $inp_state.<br />";
						if ($doDebug) {
							echo "Changed state from $student_state to $inp_state<br />";
						}
					}
					if ($inp_zip_code != $student_zip_code) { 
						$doTheUpdate							= TRUE;
						$updateData['zip_code']					= $inp_zip_code;
						$updateArray[]							= "zip_code|$inp_zip_code|s";
						$content 								.= "Updating current zip_code of $student_zip_code to $inp_zip_code.<br />";
						if ($doDebug) {
							echo "Changed zip_code from $student_zip_code to $inp_zip_code<br />";
						}
						$significantChange						= TRUE;
						$zipUpdate								= TRUE;
					}
					if ($inp_country_code != $student_country_code) { 
						$doTheUpdate							= TRUE;
						$updateData['country_code']				= $inp_country_code;
						$updateArray[]							= "country_code|$inp_country_code|s";
						$content 								.= "Updating current country_code of $student_country_code to $inp_country_code.<br />";
						if ($doDebug) {
							echo "Changed country_code from $student_country_code to $inp_country_code<br />";
						}
					}
					if ($inp_country != $student_country) { 
						$doTheUpdate							= TRUE;
						$updateData['country']					= $inp_country;
						$updateArray[]							= "country|$inp_country|s";
						$content 								.= "Updating current country of $student_country to $inp_country.<br />";
						if ($doDebug) {
							echo "Changed country from $student_country to $inp_country<br />";
						}
					}
					if ($inp_time_zone != $student_time_zone) { 
						$doTheUpdate							= TRUE;
						if ($inp_time_zone == '') {
							$inp_time_zone						= -99;
						}
						$updateData['time_zone']				= $inp_time_zone;
						$updateArray[]							= "time_zone|$inp_time_zone|d";
						$content 								.= "Updating current time zone of $student_time_zone to $inp_time_zone.<br />";
						if ($doDebug) {
							echo "Changed time_zone from $student_time_zone to $inp_time_zone<br />";
						}
					}
					if ($inp_timezone_id != $student_timezone_id) { 
						$doTheUpdate							= TRUE;
						$updateData['timezone_id']				= $inp_timezone_id;
						$updateArray[]							= "timezone_id|$inp_timezone_id|s";
						$content 								.= "Updating current time zone ID of $student_timezone_id to $inp_timezone_id.<br />";
						if ($doDebug) {
							echo "Changed timezone_id from $student_timezone_id to $inp_timezone_id<br />";
						}
					}
					if ($inp_timezone_offset != $student_timezone_offset) { 
						$doTheUpdate							= TRUE;
						if ($inp_timezone_offset == '') {
							$inp_timezone_offset				= -99;
						}
						$updateData['timezone_offset']			= $inp_timezone_offset;
						$updateArray[]							= "timezone_offset|$inp_timezone_offset|f";
						$content 								.= "Updating current time zone of $student_timezone_offset to $inp_timezone_offset.<br />";
						if ($doDebug) {
							echo "Changed timezone_offset from $student_timezone_offset to $inp_timezone_offset<br />";
						}
						$significantChange						= TRUE;
						$timezoneUpdate							= TRUE;
					}
					if ($inp_whatsapp != $student_whatsapp) { 
						$doTheUpdate							= TRUE;
						$updateData['whatsapp_app']				= $inp_whatsapp;
						$updateArray[]							= "whatsapp_app|$inp_whatsapp|s";
						$content 								.= "Updating current whatsapp of $student_whatsapp to $inp_whatsapp.<br />";
						if ($doDebug) {
							echo "Changed whatsapp from $student_whatsapp to $inp_whatsapp<br />";
						}
					}
					if ($inp_signal != $student_signal) { 
						$doTheUpdate							= TRUE;
						$updateData['signal_app']				= $inp_signal;
						$updateArray[]							= "signal_app|$inp_signal|s";
						$content 								.= "Updating current signal of $student_signal to $inp_signal.<br />";
						if ($doDebug) {
							echo "Changed signal from $student_signal to $inp_signal<br />";
						}
					}
					if ($inp_telegram != $student_telegram) { 
						$doTheUpdate							= TRUE;
						$updateData['telegram_app']				= $inp_telegram;
						$updateArray[]							= "telegram_app|$inp_telegram|s";
						$content 								.= "Updating current telegram of $student_telegram to $inp_telegram.<br />";
						if ($doDebug) {
							echo "Changed telegram from $student_telegram to $inp_telegram<br />";
						}
					}
					if ($inp_messenger != $student_messenger) { 
						$doTheUpdate							= TRUE;
						$updateData['messenger_app']				= $inp_messenger;
						$updateArray[]							= "messenger_app|$inp_messenger|s";
						$content 								.= "Updating current messenger of $student_messenger to $inp_messenger.<br />";
						if ($doDebug) {
							echo "Changed messenger from $student_messenger to $inp_messenger<br />";
						}
					}
					if ($inp_wpm != $student_wpm) { 
						$doTheUpdate							= TRUE;
						$updateData['wpm']						= $inp_wpm;
						$updateArray[]							= "wpm|$inp_wpm|s";
						$content 								.= "Updating current WPM of $student_wpm to $inp_wpm.<br />";
						if ($doDebug) {
							echo "Changed wpm from $student_wpm to $inp_wpm<br />";
						}
					}
					if ($inp_youth != $student_youth) { 
						$doTheUpdate							= TRUE;
						$updateData['youth']					= $inp_youth;
						$updateArray[]							= "youth|$inp_youth|s";
						$content 								.= "Updating current youth of $student_youth to $inp_youth.<br />";
						if ($doDebug) {
							echo "Changed youth from $student_youth to $inp_youth<br />";
						}
					}
					if ($inp_age != $student_age) {
						$doTheUpdate							= TRUE;
						$updateData['age']						= $inp_age;
						$updateArray[]							= "age|$inp_age|s";
						$content 								.= "Updating current age of $student_age to $inp_age.<br />";
						if ($doDebug) {
							echo "Changed age from $student_age to $inp_age<br />";
						}
					}
					if ($inp_student_parent != $student_student_parent) {
						$doTheUpdate							= TRUE;
						$updateData['student_parent']			= $inp_student_parent;
						$updateArray[]							= "student_parent|$inp_student_parent|s";
						$content 								.= "Updating current student_parent of $student_student_parent to $inp_student_parent.<br />";
						if ($doDebug) {
							echo "Changed student_parent from $student_student_parent to $inp_student_parent<br />";
						}
					}
					if ($inp_student_parent_email != $student_student_parent_email) {
						$doTheUpdate							= TRUE;
						$updateData['student_parent_email']		= $inp_student_parent_email;
						$updateArray[]							= "student_parent_email|$inp_student_parent_email|s";
						$content 								.= "Updating current student_parent_email of $student_student_parent_email to $inp_student_parent_email.<br />";
						if ($doDebug) {
							echo "Changed student_parent_email from $student_student_parent_email to $inp_student_parent_email<br />";
						}
					}
					if ($inp_level != $student_level) { 
						$doTheUpdate							= TRUE;
						$updateData['level']					= $inp_level;
						$updateArray[]							= "level|$inp_level|s";
						$content 								.= "Updating current level of $student_level to $inp_level.<br />";
						if ($doDebug) {
							echo "Changed level from $student_level to $inp_level<br />";
						}
					}
					if ($inp_advisor_class_timezone != $student_advisor_class_timezone) { 
						$doTheUpdate							= TRUE;
						if ($inp_advisor_class_timezone == '') {
							$inp_advisor_class_timezone			= 0;
						}
						$updateData['advisor_class_timezone']	= $inp_advisor_class_timezone;
						$updateArray[]							= "advisor_class_timezone|$inp_advisor_class_timezone|d";
						$content 							.= "Updating current advisor_class_timezone of $student_advisor_class_timezone to $inp_advisor_class_timezone.<br />";
						if ($doDebug) {
							echo "Changed advisor_class_timezone from $student_advisor_class_timezone to $inp_advisor_class_timezone<br />";
						}
					}
					if ($inp_waiting_list != $student_waiting_list) { 
						$doTheUpdate							= TRUE;
						$updateData['waiting_list']				= $inp_waiting_list;
						$updateArray[]							= "waiting_list|$inp_waiting_list|s";
						$content 								.= "Updating current waiting_list of $student_waiting_list to $inp_waiting_list.<br />";
						if ($doDebug) {
							echo "Changed waiting_list from $student_waiting_list to $inp_waiting_list<br />";
						}
					}
					if ($inp_request_date != $student_request_date) { 
						$doTheUpdate							= TRUE;
						if ($inp_request_date == " ") {
							$inp_request_date 					= "";
						}
						if ($inp_request_date != '') {
							$thisTime							= strtotime($inp_request_date);
							$inp_request_date					= date('Y-m-d H:i:s',$thisTime);
						}
						$updateData['request_date']				= $inp_request_date;
						$updateArray[]							= "request_date|$inp_request_date|s";
						$content 								.= "Updating current request date of $student_request_date to $inp_request_date.<br />";
						if ($doDebug) {
							echo "Changed request_date from $student_request_date to $inp_request_date<br />";
						}
					}
					if ($inp_semester != $student_semester) { 
						$doTheUpdate							= TRUE;
						$updateData['semester']					= $inp_semester;
						$updateArray[]							= "semester|$inp_semester|s";
						$content 								.= "Updating current semester of $student_semester to $inp_semester.<br />";
						if ($doDebug) {
							echo "Changed semester from $student_semester to $inp_semester<br />";
						}
						$student_semester						= $inp_semester;
					}
					if ($inp_notes != $student_notes) { 
						$doTheUpdate							= TRUE;
						$updateData['notes']					= $inp_notes;
						$updateArray[]							= "notes|$inp_notes|s";
						$content 								.= "Updating current notes of $student_notes to $inp_notes.<br />";
						if ($doDebug) {
							echo "Changed notes from $student_notes to $inp_notes<br />";
						}
					}
					if ($inp_email_sent_date != $student_email_sent_date) { 
						$doTheUpdate							= TRUE;
						if ($inp_email_sent_date == " ") {
							$inp_email_sent_date 				= "";
						}
						if ($inp_email_sent_date != '') {
							$thisTime							= strtotime($inp_email_sent_date);
							$inp_email_sent_date 				= date('Y-m-d H:i:s',$thisTime);
						}
						$updateData['email_sent_date']			= $inp_email_sent_date;
						$updateArray[]							= "email_sent_date|$inp_email_sent_date|s";
						$content 								.= "Updating current email sent date of $student_email_sent_date to $inp_email_sent_date.<br />";
						if ($doDebug) {
							echo "Changed email_sent_date from $student_email_sent_date to $inp_email_sent_date<br />";
						}
					}
					if ($inp_email_number != $student_email_number) { 
						$doTheUpdate							= TRUE;
						if ($inp_email_number == '') {
							$inp_email_number					= 0;
						}
						$updateData['email_number']				= $inp_email_number;
						$updateArray[]							= "email_number|$inp_email_number|d";
						$content 								.= "Updating current email number of $student_email_number to $inp_email_number.<br />";
						if ($doDebug) {
							echo "Changed email_number from $student_email_number to $inp_email_number<br />";
						}
					}
					if ($inp_response != $student_response) { 
						$doTheUpdate							= TRUE;
						$updateData['response']					= $inp_response;
						$updateArray[]							= "response|$inp_response|s";
						$content 								.= "Updating current response of $student_response to $inp_response.<br />";
						if ($doDebug) {
							echo "Changed response from $student_response to $inp_response<br />";
						}
					}
					if ($inp_response_date != $student_response_date) { 
						$doTheUpdate							= TRUE;
						if ($inp_response_date == " ") {
							$inp_response_date 					= "";
						}
						if ($inp_response_date != '') {
							$thisTime							= strtotime($inp_response_date);
							$inp_response_date  				= date('Y-m-d H:i:s', $thisTime);
						}
						$updateData['response_date']			= $inp_response_date;
		 				$updateArray[]							= "response_date|$inp_response_date|s";
						$content 								.= "Updating current response date of $student_response_date to $inp_response_date.<br />";
						if ($doDebug) {
							echo "Changed response_date from $student_response_date to $inp_response_date<br />";
						}
					}
					if ($inp_abandoned != $student_abandoned) { 
						$doTheUpdate							= TRUE;
						if ($inp_abandoned == '') {
							$inp_abandoned				= 'N';
						}
						$updateData['abandoned']			= $inp_abandoned;
						$updateArray[]							= "abandoned|$inp_abandoned|s";
						$content 								.= "Updating current Abandoned of $student_abandoned to $inp_abandoned.<br />";
						if ($doDebug) {
							echo "Changed abandoned from $student_abandoned to $inp_abandoned<br />";
						}
					}
					if ($inp_student_status != $student_student_status) { 
						$doTheUpdate							= TRUE;
						$updateData['student_status']			= $inp_student_status;
						$updateArray[]							= "student_status|$inp_student_status|s";
						$content 								.= "Updating current student status of $student_student_status to $inp_student_status.<br />";
						if ($doDebug) {
							echo "Changed status from $student_student_status to $inp_student_status<br />";
						}
					}
					if ($inp_action_log != $student_action_log) { 
						$doTheUpdate							= TRUE;
						$student_action_log						= $inp_action_log;
						if ($doDebug) {
							echo "Changed action_log from $student_action_log <br />to <br />$inp_action_log<br />";
						}
					}
					if ($inp_pre_assigned_advisor != $student_pre_assigned_advisor) { 
						$doTheUpdate							= TRUE;
						if ($inp_pre_assigned_advisor == " ") {
							$inp_pre_assigned_advisor 			= "";
						}
						$updateData['pre_assigned_advisor']		= $inp_pre_assigned_advisor;
						$updateArray[]							= "pre_assigned_advisor|$inp_pre_assigned_advisor|s";
						$content 								.= "Updating current pre_assigned_advisor of $student_pre_assigned_advisor to $inp_pre_assigned_advisor.<br />";
						if ($doDebug) {
							echo "Changed pre_assigned_advisor from $student_pre_assigned_advisor to $inp_pre_assigned_advisor<br />";
						}
					}
					if ($inp_selected_date != $student_selected_date) { 
						$doTheUpdate							= TRUE;
						if ($inp_selected_date != '') {
							$thisTime							= strtotime($inp_selected_date);
							$inp_selected_date					= date('Y-m-d H:i:s',$thisTime);
						}
						$updateData['selected_date']			= $inp_selected_date;
						$updateArray[]							= "selected_date|$inp_selected_date|s";
						$content 								.= "Updating current selected date of $student_selected_date to $inp_selected_date.<br />";
						if ($doDebug) {
							echo "Changed selected_date from $student_selected_date to $inp_selected_date<br />";
						}
					}
					if ($inp_welcome_date != $student_welcome_date) { 
						$doTheUpdate							= TRUE;
						if ($inp_welcome_date == " ") {
							$inp_welcome_date 					= "";
						}
						if ($inp_welcome_date != '') {
							$thisTime							= strtotime($inp_welcome_date);
							$inp_welcome_date					= date('Y-m-d H:i:s', $thisTime);
						}
						$updateData['welcome_date']				= $inp_welcome_date;
						$updateArray[]							= "welcome_date|$inp_welcome_date|s";
						$content 								.= "Updating current welcome date of $student_welcome_date to $inp_welcome_date.<br />";
						if ($doDebug) {
							echo "Changed welcome_date from $student_welcome_date to $inp_welcome_date<br />";
						}
					}
					if ($inp_no_catalog != $student_no_catalog) { 
						$doTheUpdate							= TRUE;
						$updateData['no_catalog']				= $inp_no_catalog;
						$updateArray[]							= "no_catalog|$inp_no_catalog|s";
						$content 								.= "Updating current no catalog of $student_no_catalog to $inp_no_catalog.<br />";
						if ($doDebug) {
							echo "Changed no_catalog from $student_no_catalog to $inp_no_catalog<br />";
						}
					}
					if ($inp_hold_override != $student_hold_override) { 
						$doTheUpdate							= TRUE;
						$updateData['hold_override']			= $inp_hold_override;
						$updateArray[]							= "hold_override|$inp_hold_override|s";
						$content 								.= "Updating current Hold Override of $student_hold_override to $inp_hold_override.<br />";
						if ($doDebug) {
							echo "Changed hold_override from $student_hold_override to $inp_hold_override<br />";
						}
					}
					if ($inp_messaging != $student_messaging) { 
						$doTheUpdate							= TRUE;
						$updateData['messaging']				= $inp_messaging;
						$updateArray[]							= "messaging|$inp_messaging|s";
						$content 								.= "Updating Messaging of $student_messaging to $inp_messaging.<br />";
						if ($doDebug) {
							echo "Changed messaging from $student_messaging to $inp_messaging<br />";
						}
					}
					if ($inp_assigned_advisor != $student_assigned_advisor) { 
						$doTheUpdate							= TRUE;
						$updateData['assigned_advisor']			= $inp_assigned_advisor;
						$updateArray[]							= "assigned_advisor|$inp_assigned_advisor|s";
						$content 								.= "Updating current assigned advisor of $student_assigned_advisor to $inp_assigned_advisor.<br />";
						if ($doDebug) {
							echo "Changed assigned_advisor from $student_assigned_advisor to $inp_assigned_advisor<br />";
						}
					}
					if ($inp_advisor_class_timezone != $student_advisor_class_timezone) { 
						$doTheUpdate							= TRUE;
						if ($inp_advisor_class_timezone == '') {
							$inp_advisor_class_timezone			= -99;
						}
						$updateData['advisor_class_timezone']	= $inp_advisor_class_timezone;
						$updateArray[]							= "advisor_class_timezone|$inp_advisor_class_timezone|d";
						$content 								.= "Updating current advisor_class_timezone of $student_advisor_class_timezone to $inp_advisor_class_timezone.<br />";
						if ($doDebug) {
							echo "Changed advisor_class_timezone from $student_advisor_class_timezone to $inp_advisor_class_timezone<br />";
						}
					}
					if ($inp_advisor_select_date != $student_advisor_select_date) { 
						$doTheUpdate							= TRUE;
						if ($inp_advisor_select_date != '') {
							$thisTime							= strtotime($inp_advisor_select_date);
							$inp_advisor_select_date			= date('Y-m-d H:i:s', $thisTime);
						}
						$updateData['advisor_select_date']		= $inp_advisor_select_date;
						$updateArray[]							= "advisor_select_date|$inp_advisor_select_date|s";
						$content 								.= "Updating current advisor_select_date of $student_advisor_select_date to $inp_advisor_select_date.<br />";
						if ($doDebug) {
							echo "Changed advisor_select_date from $student_advisor_select_date to $inp_advisor_select_date<br />";
						}
					}
					if ($inp_hold_reason_code != $student_hold_reason_code) { 
						$doTheUpdate							= TRUE;
						$updateData['hold_reason_code']			= $inp_hold_reason_code;
						$content 								.= "Updating Hold Reason Code of $student_hold_reason_code to $inp_hold_reason_code.<br />";
						$updateArray[]							= "hold_reason_code|$inp_hold_reason_code|s";
						if ($doDebug) {
							echo "Changed hold_reason_code from $student_hold_reason_code to $inp_hold_reason_code<br />";
						}
					}
					if ($inp_class_priority != $student_class_priority) { 
						$doTheUpdate							= TRUE;
						if ($inp_class_priority == '') {
							$inp_class_priority					= 0;
						}
						$updateData['class_priority']			= $inp_class_priority;
						$updateArray[]							= "class_priority|$inp_class_priority|d";
						$content 								.= "Updating current class priority of $student_class_priority to $inp_class_priority.<br />";
						if ($doDebug) {
							echo "Changed class_priority from $student_class_priority to $inp_class_priority<br />";
						}
					}
					if ($inp_assigned_advisor_class != $student_assigned_advisor_class) { 
						$doTheUpdate							= TRUE;
						if ($inp_assigned_advisor_class == '') {
							$inp_assigned_advisor_class			= 0;
						}
						$updateData['assigned_advisor_class']	= $inp_assigned_advisor_class;
						$updateArray[]							= "assigned_advisor_class|$inp_assigned_advisor_class|d";
						$content 								.= "Updating current assigned advisor class of $student_assigned_advisor_class to $inp_assigned_advisor_class.<br />";
						if ($doDebug) {
							echo "Changed assigned_advisor_class from $student_assigned_advisor_class to $inp_assigned_advisor_class<br />";
						}
					}
					if ($inp_promotable != $student_promotable) { 
						$doTheUpdate							= TRUE;
						$updateData['promotable']				= $inp_promotable;
						$updateArray[]							= "promotable|$inp_promotable|s";
						$content 								.= "Updating current promotable of $student_promotable to $inp_promotable.<br />";
						if ($doDebug) {
							echo "Changed promotable from $student_promotable to $inp_promotable<br />";
						}
					}
					if ($inp_excluded_advisor != $student_excluded_advisor) { 
						$doTheUpdate							= TRUE;
						if ($inp_excluded_advisor == " ") {
							$inp_excluded_advisor 				= "";
						}
						$updateData['excluded_advisor']			= $inp_excluded_advisor;
						$inp_excluded_advisor					= str_replace("|","&",$inp_excluded_advisor);
						$updateArray[]							= "excluded_advisor|$inp_excluded_advisor|s";
						$content 								.= "Updating current excluded_advisor of $student_excluded_advisor to $inp_excluded_advisor.<br />";
						if ($doDebug) {
							echo "Changed excluded_advisor from $student_excluded_advisor to $inp_excluded_advisor<br />";
						}
					}
					if ($inp_student_survey_completion_date != $student_student_survey_completion_date) { 
						$doTheUpdate							= TRUE;
						if ($inp_student_survey_completion_date == " ") {
							$inp_student_survey_completion_date = "";
						}
						$updateData['student_survey_completion_date']				= $inp_student_survey_completion_date;
						$updateArray[]							= "student_survey_completion_date|$inp_student_survey_completion_date|s";
						$content 								.= "Updating current student survey completion date of $student_student_survey_completion_date to $inp_student_survey_completion_date.<br />";
						if ($doDebug) {
							echo "Changed survey_completion_date from $student_survey_completion_date to $inp_survey_completion_date<br />";
						}
					}
					if ($inp_available_class_days != $student_available_class_days) { 
						$doTheUpdate							= TRUE;
						if ($inp_available_class_days == " ") {
							$inp_available_class_days 			= "";
						}
						$updateData['available_class_days']		= $inp_available_class_days;
						$updateArray[]							= "available_class_days|$inp_available_class_days|s";
						$content 								.= "Updating current available class days of $student_available_class_days to $inp_available_class_days.<br />";
						if ($doDebug) {
							echo "Changed available_class_days from $student_available_class_days to $inp_available_class_days<br />";
						}
					}
					if ($inp_intervention_required != $student_intervention_required) { 
						$doTheUpdate							= TRUE;
						$updateData['intervention_required']	= $inp_intervention_required;
						$updateArray[]							= "intervention_required|$inp_intervention_required|s";
						$content 								.= "Updating current intervention_required of $student_intervention_required to $inp_intervention_required.<br />";
						if ($doDebug) {
							echo "Changed intervention_required from $student_intervention_required to $inp_intervention_required<br />";
						}
					}
					if ($inp_first_class_choice != $student_first_class_choice) { 
						$doTheUpdate							= TRUE;
						$updateData['first_class_choice']		= $inp_first_class_choice;
						$updateArray[]							= "first_class_choice|$inp_first_class_choice|s";
						$content 								.= "Updating current first_class_choice of $student_first_class_choice to $inp_first_class_choice.<br />";
						$significantChange						= TRUE;
						$classChoiceUpdate						= TRUE;
						if ($doDebug) {
							echo "Changed first_class_choice from $student_first_class_choice to $inp_first_class_choice<br />";
						}
					}
					if ($inp_second_class_choice != $student_second_class_choice) { 
						$doTheUpdate							= TRUE;
						$updateData['second_class_choice']		= $inp_second_class_choice;
						$updateArray[]							= "second_class_choice|$inp_second_class_choice|s";
						$content 								.= "Updating current second_class_choice of $student_second_class_choice to $inp_second_class_choice.<br />";
						if ($doDebug) {
							echo "Changed second_class_choice from $student_second_class_choice to $inp_second_class_choice<br />";
						}
						$significantChange						= TRUE;
						$classChoiceUpdate						= TRUE;
					}
					if ($inp_third_class_choice != $student_third_class_choice) { 
						$doTheUpdate							= TRUE;
						$updateData['third_class_choice']		= $inp_third_class_choice;
						$updateArray[]							= "third_class_choice|$inp_third_class_choice|s";
						$content 								.= "Updating current third_class_choice of $student_third_class_choice to $inp_third_class_choice.<br />";
						if ($doDebug) {
							echo "Changed third_class_choice from $student_third_class_choice to $inp_third_class_choice<br />";
						}
						$significantChange						= TRUE;
						$classChoiceUpdate						= TRUE;
					}
					if ($inp_first_class_choice_utc != $student_first_class_choice_utc) { 
						$doTheUpdate							= TRUE;
						$updateData['first_class_choice_utc']	= $inp_first_class_choice_utc;
						$updateArray[]							= "first_class_choice_utc|$inp_first_class_choice_utc|s";
						$content 								.= "Updating current first_class_choice_utc of $student_first_class_choice_utc to $inp_first_class_choice_utc.<br />";
						if ($doDebug) {
							echo "Changed first_class_choice_utc from $student_first_class_choice_utc to $inp_first_class_choice_utc<br />";
						}
					}
					if ($inp_second_class_choice_utc != $student_second_class_choice_utc) { 
						$doTheUpdate							= TRUE;
						$updateData['second_class_choice_utc']	= $inp_second_class_choice_utc;
						$updateArray[]							= "second_class_choice_utc|$inp_second_class_choice_utc|s";
						$content 								.= "Updating current second_class_choice_utc of $student_second_class_choice_utc to $inp_second_class_choice_utc.<br />";
						if ($doDebug) {
							echo "Changed second_class_choice_utc from $student_second_class_choice_utc to $inp_second_class_choice_utc<br />";
						}
					}
					if ($inp_third_class_choice_utc != $student_third_class_choice_utc) { 
						$doTheUpdate							= TRUE;
						$updateData['third_class_choice_utc']	= $inp_third_class_choice_utc;
						$updateArray[]							= "third_class_choice_utc|$inp_third_class_choice_utc|s";
						$content 								.= "Updating current third_class_choice_utc of $student_third_class_choice_utc to $inp_third_class_choice_utc.<br />";
						if ($doDebug) {
							echo "Changed third_class_choice_utc from $student_third_class_choice_utc to $inp_third_class_choice_utc<br />";
						}
					}
					if ($inp_catalog_options != $student_catalog_options) { 
						$doTheUpdate							= TRUE;
						$updateData['catalog_options']	= $inp_catalog_options;
						$updateArray[]							= "catalog_options|$inp_catalog_options|s";
						$content 								.= "Updating current catalog_options of $student_catalog_options to $inp_catalog_options.<br />";
						if ($doDebug) {
							echo "Changed catalog_options from $student_catalog_options to $inp_catalog_options<br />";
						}
					}
					if ($inp_flexible != $student_flexible) { 
						$doTheUpdate							= TRUE;
						$updateData['flexible']	= $inp_flexible;
						$updateArray[]							= "flexible|$inp_flexible|s";
						$content 								.= "Updating current flexible of $student_flexible to $inp_flexible.<br />";
						if ($doDebug) {
							echo "Changed flexible from $student_flexible to $inp_flexible<br />";
						}
					}
					if ($doTheUpdate) {
						if ($doDebug) {
							echo "Doing the update.<br />";
						}

						$theActionLogUpdate		= '';
						foreach($updateArray as $myValue) {
							$myArray				= explode("|",$myValue);
							$field					= $myArray[0];
							$fieldValue				= $myArray[1];
							$theActionLogUpdate	.= "$field changed to $fieldValue; ";
						}
						$student_action_log			= "$student_action_log / $actionDate UPDATE $userName made these changes: $theActionLogUpdate ";
						$updateData['action_log'] 	= $student_action_log;
						$updateArray[]				= "action_log|$student_action_log|s";



						// call the function to update the student table

						$updateData	= array('tableName'=>$request_table,
											'inp_data'=>$updateArray,
											'jobname'=>'DISPUPD',
											'inp_method'=>'update',
											'inp_id'=>$student_ID,
											'inp_callsign'=>$student_call_sign,
											'inp_semester'=>$student_semester,
											'inp_who'=>$userName,
											'testMode'=>$testMode,
											'doDebug'=>$doDebug);
						$updateResult	= updateStudent($updateData);
						if ($updateResult[0] === FALSE) {
							if ($doDebug) {
								echo "student data table update failed<br />Error: $updateResult[1]<br />";
							}
							$content				.= "<p>Updating the student table failed.<br >Error: $updateResult[1]</p>";
						} else {
							if ($doDebug) {
								echo "updated  $request_table successfully<br />";
							}
						}

					} else {
						if ($doDebug) {
							echo "No updates were entered.<br />";
						}
						$content .= "No updates were requested.<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "no $request_table record found with id $student_ID<br />";
				}
				$content			.= "No $request_table record found with id $student_ID<br />";
			}
		}
		$sql					= "select * from $request_table where student_id = $student_ID";
		$wpw1_cwa_student		= $wpdb->get_results($sql);
		if ($wpw1_cwa_student === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $studentTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numSRows			= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach ($wpw1_cwa_student as $studentRow) {
					$student_ID								= $studentRow->student_id;
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name						= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_email  						= strtolower(strtolower($studentRow->email));
					$student_phone  						= $studentRow->phone;
					$student_ph_code						= $studentRow->ph_code;
					$student_city  							= $studentRow->city;
					$student_state  						= $studentRow->state;
					$student_zip_code  						= $studentRow->zip_code;
					$student_country  						= $studentRow->country;
					$student_country_code					= $studentRow->country_code;
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

					$student_last_name 						= no_magic_quotes($student_last_name);
					$newActionLog							= formatActionLog($student_action_log);
					$content .= "<h3>Database Content for $student_call_sign in Table $request_table</h3>
								<p><a href='$theURL'>Look Up Another Student</a></p><br />
								<table style='border-collapse:collapse;'>
								<tr><th style='width:200px;'>Field</th><th>Value</th></tr>
								<tr><td>Student ID</td><td><a href='$theURL?studentid=$student_ID&strpass=3&request_table=$request_table&inp_verbose=$inp_verbose'>$student_ID</a></td></tr>
								<tr><td>Call Sign</td><td>$student_call_sign</td></tr>
								<tr><td>First Name</td><td>$student_first_name</td></tr>
								<tr><td>Last Name</td><td>$student_last_name</td></tr>
								<tr><td>Email</td><td>$student_email</td></tr>
								<tr><td>Ph Code</td><td>$student_ph_code</td></tr>
								<tr><td>Phone</td><td>$student_phone</td></tr>
								<tr><td>City</td><td>$student_city</td></tr>
								<tr><td>State</td><td>$student_state</td></tr>
								<tr><td>Zip Code</td><td>$student_zip_code</td></tr>
								<tr><td>Country</td><td>$student_country</td></tr>
								<tr><td>Country Code</td><td>$student_country_code</td></tr>
								<tr><td>Time Zone<td>$student_time_zone</td></tr>
								<tr><td>Timezone ID<td>$student_timezone_id</td></tr>
								<tr><td>Timezone Offset<td>$student_timezone_offset</td></tr>
								<tr><td>Whatsapp</td><td>$student_whatsapp</td></tr>
								<tr><td>Signal</td><td>$student_signal</td></tr>
								<tr><td>Telegram</td><td>$student_telegram</td></tr>
								<tr><td>Messenger</td><td>$student_messenger</td></tr>
								<tr><td>WPM</td><td>$student_wpm</td></tr>
								<tr><td>Youth</td><td>$student_youth</td></tr>
								<tr><td>Age</td><td>$student_age</td></tr>
								<tr><td>Parent</td><td>$student_student_parent</td></tr>
								<tr><td>Parent Email</td><td>$student_student_parent_email</td></tr>
								<tr><td>Level</td><td>$student_level</td></tr>
								<tr><td>Waiting List</td><td>$student_waiting_list</td></tr>
								<tr><td>Request Date</td><td>$student_request_date</td></tr>
								<tr><td>Semester</td><td>$student_semester</td></tr>
								<tr><td>Advisor Notes</td><td>$student_notes</td></tr>
								<tr><td>Email Sent Date</td><td>$student_email_sent_date</td></tr>
								<tr><td>Email Number</td><td>$student_email_number</td></tr>
								<tr><td>Response</td><td>$student_response</td></tr>
								<tr><td>Response Date</td><td>$student_response_date</td></tr>
								<tr><td>Abandoned</td><td>$student_abandoned</td></tr>
								<tr><td>Student Status</td><td>$student_student_status</td></tr>
								<tr><td style='vertical-align:top;'>Action Log</td><td>$newActionLog</td></tr>
								<tr><td>Pre-assigned Advisor</td><td>$student_pre_assigned_advisor</td></tr>
								<tr><td>Selected Date</td><td>$student_selected_date</td></tr>
								<tr><td>Welcome Date</td><td>$student_welcome_date</td></tr>
								<tr><td>No Catalog</td><td>$student_no_catalog</td></tr>
								<tr><td>Hold Override</td><td>$student_hold_override</td></tr>
								<tr><td>Messaging</td><td>$student_messaging</td></tr>
								<tr><td>Assigned Advisor</td><td>$student_assigned_advisor</td></tr>
								<tr><td>Advisor Class Timezone</td><td>$student_advisor_class_timezone</td></tr>
								<tr><td>Advisor Select Date</td><td>$student_advisor_select_date</td></tr>
								<tr><td>Hold Reason Code</td><td>$student_hold_reason_code</td></tr>
								<tr><td>Class Priority</td><td>$student_class_priority</td></tr>
								<tr><td>Assigned Advisor Class</td><td>$student_assigned_advisor_class</td></tr>
								<tr><td>Promotable</td><td>$student_promotable</td></tr>
								<tr><td>Excluded Advisor</td><td>$student_excluded_advisor</td></tr>
								<tr><td>Student Survey Completion Date</td><td>$student_student_survey_completion_date</td></tr>
								<tr><td>Available Class Days</td><td>$student_available_class_days</td></tr>
								<tr><td>Intervention Required</td><td>$student_intervention_required</td></tr>
								<tr><td>First Class Choice</td><td>$student_first_class_choice</td></tr>
								<tr><td>Second Class Choice</td><td>$student_second_class_choice</td></tr>
								<tr><td>Third Class Choice</td><td>$student_third_class_choice</td></tr>
								<tr><td>First Class Choice UTC</td><td>$student_first_class_choice_utc</td></tr>
								<tr><td>Second Class Choice UTC</td><td>$student_second_class_choice_utc</td></tr>
								<tr><td>Third Class Choice UTC</td><td>$student_third_class_choice_utc</td></tr>
								<tr><td>Catalog Options</td><td>$student_catalog_options</td></tr>
								<tr><td>Flexible</td><td>$student_flexible</td></tr>
								<tr><td>Date Created</td><td>$student_date_created</td></tr>
								<tr><td>Date Updated</td><td>$student_date_updated</td></tr>
								</table>";

						$content			.= "<table style='width:300px;'>
												<tr><td style='width:150px;'><form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data''>
													<input type='hidden' name='strpass' value='3'>
													<input type='hidden' name='request_table' value='$request_table'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='studentid' value='$student_ID'>
													<input type='submit' class='formInputButton' value='UPDATE Student'></form></td>
												<td style='width:150px;'><form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data''>
													<input type='hidden' name='strpass' value='5'>
													<input type='hidden' name='request_table' value='$request_table'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<input type='hidden' name='studentid' value='$student_ID'>
													<input type='submit' class='formInputButton' 
													onclick=\"return confirm('Are you sure you want to delete this student id?');\" value='DELETE Student'></form></td></tr>
													</table>";
				}
			}
		}
		
////// pass 5: delete a student

	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 5 to delete a student<br />";
		}
		
		$content .= "<h3>Delete the Student at ID $studentID in table $request_table</h3>";
		if ($inp_assigned_advisor != '') {
			if ($doDebug) {
				echo "student is assigned to $inp_assigned_advisor $inp_assigned_advisor_class class<br />";
			}
			$inp_data			= array('inp_student'=>$inp_call_sign,
										'inp_semester'=>$inp_semester,
										'inp_assigned_advisor'=>$inp_assigned_advisor,
										'inp_assigned_advisor_class'=>$inp_assigned_advisor_class,
										'inp_remove_status'=>'',
										'inp_arbitrarily_assigned'=>'',
										'inp_method'=>'remove',
										'jobname'=>$jobname,
										'userName'=>$userName,
										'testMode'=>$testMode,
										'doDebug'=>$doDebug);
						
			$removeResult		= add_remove_student($inp_data);
			if ($removeResult[0] === FALSE) {
				$thisReason		= $removeResult[1];
				if ($doDebug) {
					echo "attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
				}
				sendErrorEmail("$jobname Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason");
				$content		.= "Attempting to remove $student_call_sign from $student_assigned_advisor class failed:<br />$thisReason<br />";
			} else {
				$content		.= "Student removed from $inp_assigned_advisor $inp_assigned_advisor_class class<br />";
			}
		}
		$studentUpdateData		= array('tableName'=>$request_table,
										'inp_method'=>'delete',
										'jobname'=>$jobname,
										'inp_id'=>$studentID,
										'inp_callsign'=>$inp_call_sign,
										'inp_semester'=>$inp_semester,
										'inp_who'=>$userName,
										'testMode'=>$testMode,
										'doDebug'=>$doDebug);
		$updateResult	= updateStudent($studentUpdateData);
		if ($updateResult[0] === FALSE) {
			$myError	= $wpdb->last_error;
			$mySql		= $wpdb->last_query;
			$errorMsg	= "$jobname Processing $student_call_sign in $studentTableName failed. Reason: $updateResult[1]<br />SQL: $mySql<br />Error: $myError<br />";
			if ($doDebug) {
				echo $errorMsg;
			}
			sendErrorEmail($errorMsg);
			$content		.= "Unable to update content in $studentTableName<br />";
		} else {
			if ($doDebug) {
				echo "successfully deleted $inp_call_sign at id $studentID<br />";
			}
			$content	.= "Student record successfully deleted<br />";
		}

	}
	
	if ("1" != $strPass) {
		$content	.= "<p><a href='$theURL'>Look Up Another Student</a></p><br />";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}

	return $content;
	
}
add_shortcode ('display_and_update_student_info', 'display_and_update_student_info_func');
