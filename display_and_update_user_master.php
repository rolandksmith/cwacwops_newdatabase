function display_and_update_user_master_func() {

	global $wpdb;

	$doDebug						= TRUE;
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
	$loginRole			= $initializationArray['userRole'];
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$proximateSemester	= $initializationArray['proximateSemester'];
	

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
	$theURL						= "$siteURL/cwa-display-and-update-user-master-information/";
	$inp_semester				= '';
	$jobname					= "Display and Update User Master V$versionNumber";
	$inp_mode					= "";
	$inp_verbose				= "";
	$actionLogDate				= date('dMy H:i');
	$callSignMethod				= FALSE;
	$runByUser					= FALSE;
  	$inp_action_log				= "";

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
			// NOTE: inp_call_sign is the name used for the actual call sign
			// in the system. inp_callsign is used when calling this program 
			// from some other program
			
			
			if ($str_key == "inp_callsign") {
				$inp_callsign = strtoupper($str_value);
				$inp_callsign = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
				if ($doDebug) {
					echo "setting callSignMethod to TRUE<br />";
				}
				$callSignMethod	= TRUE;
				$inp_call_sign	= $inp_callsign;
			}
			if ($str_key == "inp_call_sign") {
				$inp_call_sign = strtoupper($str_value);
				$inp_call_sign = filter_var($inp_call_sign,FILTER_UNSAFE_RAW);
				$inp_callsign	= $inp_call_sign;
			}
			if ($str_key == "inp_id") {
				$inp_id = $str_value;
				$inp_id = filter_var($inp_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "request_type") {
				$request_type = $str_value;
				$request_type = filter_var($request_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "request_info") {
				$request_info = $str_value;
				$request_info = filter_var($request_info,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "doDebug") {
				$doDebug = strtoupper($str_value);
				$doDebug = filter_var($doDebug,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "testMode") {
				$testMode = strtoupper($str_value);
				$testMode = filter_var($testMode,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_first_name") {
				$inp_first_name = $str_value;
				$inp_first_name = filter_var($inp_first_name,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_last_name") {
				$inp_last_name = $str_value;
				$inp_last_name = filter_var($inp_last_name,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_email") {
				$inp_email = $str_value;
				$inp_email = filter_var($inp_email,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_phone") {
				$inp_phone = $str_value;
				$inp_phone = filter_var($inp_phone,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_city") {
				$inp_city = $str_value;
				$inp_city = filter_var($inp_city,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_state") {
				$inp_state = $str_value;
				$inp_state = filter_var($inp_state,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_zip_code") {
				$inp_zip_code = $str_value;
				$inp_zip_code = filter_var($inp_zip_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_date_created") {
				$inp_date_created = $str_value;
				$inp_date_created = filter_var($inp_date_created,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_country_code") {
				$inp_country_code = $str_value;
				$inp_country_code = filter_var($inp_country_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_country_data") {
				$inp_country_data 	= $str_value;
				$inp_country_data 	= filter_var($inp_country_data,FILTER_UNSAFE_RAW);
				$myArray			= explode("|",$inp_country_data);
				if (count($myArray) > 1) {
					$inp_country_code	= $myArray[0];
					$inp_country		= $myArray[1];
					$inp_ph_code		= $myArray[2];
				} else {
					$inp_country_code	= "";
					$inp_country		= "";
					$inp_ph_code		= "";
				}
				if ($doDebug) {
					echo "broke out $inp_country_data into<br />
							inp_country_code: $inp_country_code<br />
							inp_country; $inp_country<br />
							inp_ph_code: $inp_ph_code<br />";
				}
			}
			if ($str_key == "inp_whatsapp") {
				$inp_whatsapp = $str_value;
				$inp_whatsapp = filter_var($inp_whatsapp,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_telegram") {
				$inp_telegram = $str_value;
				$inp_telegram = filter_var($inp_telegram,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_signal") {
				$inp_signal = $str_value;
				$inp_signal = filter_var($inp_signal,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_messenger") {
				$inp_messenger = $str_value;
				$inp_messenger = filter_var($inp_messenger,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_languages") {
				$inp_languages = $str_value;
				$inp_languages = filter_var($inp_languages,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "theseUpdateParams") {
				$theseUpdateParams = $str_value;
//				$theseUpdateParams = filter_var($theseUpdateParams,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_action_log") {
				$inp_action_log = $str_value;
				$inp_action_log = filter_var($inp_action_log,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_timezone_id") {
				$inp_timezone_id = $str_value;
				$inp_timezone_id = filter_var($inp_timezone_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_prev_callsign") {
				$inp_prev_callsign = $str_value;
				$inp_prev_callsign = filter_var($inp_prev_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "updateContent") {
				$updateContent = $str_value;
				$updateContent = filter_var($updateContent,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "updateLog") {
				$updateLog = $str_value;
				$updateLog = filter_var($updateLog,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_survey_score") {
				$inp_survey_score = $str_value;
				$inp_survey_score = filter_var($inp_survey_score,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_is_admin") {
			 	$inp_is_admin = $str_value;
				$inp_is_admin = filter_var($inp_is_admin,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_role") {
				$inp_role = $str_value;
				$inp_role = filter_var($inp_role,FILTER_UNSAFE_RAW);
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
		
		.info-asterisk {
			cursor: help;
			color: #d9534f;
			font-weight: bold;
			padding: 0 4px;
			position: relative; /* Keeps the tooltip anchored to this element */
			display: inline-block;
		}
		
		/* The updated tooltip box */
		.hover-popup {
			position: absolute;
			background: #333;
			color: #fff;
			padding: 8px 12px;
			border-radius: 4px;
			font-size: 13px;
			z-index: 1000;
			
			/* Position logic */
			bottom: 150%;      /* Places it above the asterisk */
			left: 0;           /* Aligns the left edge of the box with the asterisk */
			
			/* Responsive width logic */
			width: max-content; 
			max-width: 250px;   /* Prevents it from being too wide */
			white-space: normal; /* Allows text to wrap if it hits max-width */
			word-wrap: break-word;
		
			/* Smooth entry */
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.2s ease-in-out;
			box-shadow: 0 4px 8px rgba(0,0,0,0.3);
		}
		
		/* Add a small arrow pointing down */
		.hover-popup::after {
			content: '';
			position: absolute;
			top: 100%; 
			left: 10px; /* Aligns arrow with the start of the text */
			border-width: 5px;
			border-style: solid;
			border-color: #333 transparent transparent transparent;
		}
		
		/* Show on hover */
		.info-asterisk:hover .hover-popup {
			opacity: 1;
			visibility: visible;
		}		

		</style>";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode</strong></p>";
		}
		$extMode					= 'tm';
		$userHistoryTableName		= 'wpw1_cwa_user_master_history2';
		$operatingMode				= 'Testmode';
	} else {
		$extMode					= 'pd';
		$userHistoryTableName		= 'wpw1_cwa_user_master_history';
		$operatingMode				= 'Production';
	}
	
	
	$user_dal = new CWA_User_Master_DAL();
	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	
function selectCountry($inp_country) {
	global $wpdb;
	$selectList = FALSE;
	$country_sql = "select * from wpw1_cwa_country_codes order by country_code";
	$countryResult = $wpdb->get_results($country_sql);
	if ($countryResult === FALSE) {
		if ($doDebug) {
			echo "getting phone code from wpw1_cwa_country_codes table for country_code $inp_country_code failed<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
	} else {
		$numCCRows			= $wpdb->num_rows;
		if ($numCCRows > 0) {
			$selectList .= "<label for='country'>Choose a country:<br /></label>\n
							  <select id='inp_country_data' name='inp_country_data'>";
			foreach($countryResult as $countryCodeRows) {
				$countryCode_ID				= $countryCodeRows->record_id;
				$countryCode_country_code	= $countryCodeRows->country_code;
				$countryCode_country_name	= $countryCodeRows->country_name;
				$countryCode_ph_code		= $countryCodeRows->ph_code;
				$countryCode_date_updated	= $countryCodeRows->date_updated;
				
				$isSelected = '';
				if ($inp_country == $countryCode_country_name) {
					$isSelected = 'selected';
				}
				$selectList .= "<option value='$countryCode_country_code|$countryCode_country_name|$countryCode_ph_code' $isSelected>$countryCode_country_name</option>\n";
			}
		}
	}	
	return $selectList;
}


	if ($strPass == "1") {
		if ($loginRole == 'administrator') {
			$strPass					= "1";
		} else {
			$strPass					= "2";
			$runByUser					= TRUE;	// job run from link on user's portal
			$request_type				= 'callsign';
			$request_info				= strtoupper($userName);
		}
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 1<br />";
		}
		
		
		$content 		.= "<h3>$jobname</h3>
							<p>Enter how the system should search for the 
							User Master record along with the search criteria</p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Search Method</td>
								<td><input type='radio' class='formInputButton' name='request_type' value='callsign' checked>Call Sign<br />
									<input type='radio' class='formInputButton' name='request_type' value='id'>User ID<br />
									<input type='radio' class='formInputButton' name='request_type' value='surname'>Surname<br />
									<input type='radio' class='formInputButton' name='request_type' value='given'>Given Name<br />
									<input type='radio' class='formInputButton' name='request_type' value='email'>Email</td></tr>
							<tr><td style='vertical-align:top;'>Search Criteria</td>
								<td><input type='text' class='formInputText' name='request_info' size='50' maxlength='50' autofocus></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' name='submit' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	
	}
///// Pass 2 -- do the work


	if ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}
		$doProceed					= TRUE;
		$displayedAlready			= FALSE;

//		if ($callSignMethod) {
//			$request_type				= 'callsign';
//			$request_info				= $inp_callsign;
//			$criteria					= array('user_call_sign'=>$inp_callsign);
//			if ($doDebug) {
//				echo "callSignMethod is TRUE. Setting request_type to $request_type and request_info to $request_info<br />";
//			}
//		} else {
//			if ($doDebug) {
//				echo "callSignMethod is FALSE. request_type is set to $request_type and request_info is set to $request_info<br />";
//			}
//		}

	
		$content			.= "<h3>$jobname</h3>";
		if ($loginRole == 'administrator') {
			$content		.= "<p><a href='$theURL'>Look Up a Different User</a></p>";
		}
		if ($runByUser) {
			$content		.= "<p>This is your personal information. It is stored in the 
								User Master table and used throughout the CW Academy 
								system wherever your personal information is needed.</p>";
		}
		$haveUserMaster		= FALSE;
		// get the user_master info and format it
		if ($doDebug) {
			echo "getting the user_master data<br />";
		}
		if ($request_type == 'callsign') {
			$request_info	= strtoupper($request_info);
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_call_sign', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
				echo "set up the criteria for a callsign request type<br />";
			}
		} elseif ($request_type == 'id') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_ID', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
				echo "set up the criteria for an id request type<br />";
			}
		} elseif ($request_type == 'surname') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_last_name', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
				echo "set up the criteria for a surname request type<br />";
			}
		} elseif ($request_type == 'given') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_first_name', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
				echo "set up the criteria for a given name request type<br />";
			}
		} elseif ($request_type == 'email') {
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					[ 
						'field'   => 'user_email', 
						'value'   => $request_info, 
						'compare' => '=' 
					]
				]
			];
			if ($doDebug) {
				echo "set up the criteria for an email request type<br />";
			}
		} else {
			if ($doDebug) {
				echo "invalid request_type of $request_type<br />";
			}
			$content	.= "<p>Sorry. Invalid method entered</p>";
			$doProceed	= FALSE;
		}
		
		if ($doProceed) {
			// get the user_master information
			if ($doDebug) {
				echo "calling user_dal->get_user_master. criteria:<br /><pre>";
				print_r($criteria);
				echo "</pre><br />";	
			}
			$user_data = $user_dal->get_user_master($criteria,'user_call_sign','ASC',$operatingMode);
			if ($doDebug) {
				echo "returned from get_user_master. user_data:<br /><pre>";
				print_r($user_data);
				echo "</pre><br />";
			}
			if ($user_data === FALSE) {
				$content		.= "Attempting to retrieve $request_info failed<br />";
			} else {
				if (count($user_data) > 0) {
					$haveUserMaster				= TRUE;
					foreach($user_data as $key => $value) {
						foreach($value as $thisField => $thisValue) {
							$$thisField = $thisValue;
						}
						if ($doDebug) {
							echo "have the data, setting up to display<br />";
						}
						$myStr			= formatActionLog($user_action_log);
						$timezoneMsg	= '';
						if ($user_timezone_id == '??') {
							$timezoneMsg	= "<p><b>CRITICAL</b>Your timezone identifier needs to be 
												determined. You must update the information and 
												be certain that 
												the following fields are correct and valid:<br />
												<ul><li>Country: the value in this field must 
														be a valid country name
													<li>Zip / Postal Code: the code must valid 
														for residents of the United States.
													<li>City: the value in this field must be 
														a valid city name
													<li>State / Province: Again, a valid name
												</ul>";
						}
						$content		.= "<h4>User Master Data for ID $user_ID</h4>
											$timezoneMsg
											<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='3'>
											<input type='hidden' name='inp_id' value='$user_ID'>
											<input type='hidden' name='inp_call_sign' value='$user_call_sign'>
											<input type='hidden' name='inp_mode' value='$inp_mode'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<table style='width:900px;'>
											<tr><td><b>Callsign<br />$user_call_sign</b></td>
												<td><b>Name</b><br />$user_last_name, $user_first_name</td>
												<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
												<td><b>Email</b><br />$user_email</td></tr>
											<tr><td><b>City</b><br />$user_city</td>
												<td><b>State</b><br />$user_state</td>
												<td><b>Zip Code</b><br />$user_zip_code</td>
												<td><b>Country</b><br />$user_country</td></tr>
											<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
												<td><b>Telegram</b><br />$user_telegram</td>
												<td><b>Signal</b><br />$user_signal</td>
												<td><b>Messenger</b><br />$user_messenger</td></tr>
											<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
												<td><b>Languages</b><br />$user_languages</td>
												<td><b>Date Created</b><br />$user_date_created</td>
												<td><b>Date Updated</b><br />$user_date_updated</td></tr>";
						if ($loginRole == 'administrator') {
							$content 	.= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
												<td><b>Is Admin</b><br />$user_is_admin</td>
												<td><b>Role</b><br />$user_role</td>
												<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
											<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
						}
						$content		.= "<tr><td colspan='4'><hr></td></tr>
											<tr><td colspan='4'><input type='submit' class='formInputButton' name='submit' value='Update $user_call_sign' /></td></tr>
											</table></form>";
						if ($loginRole == 'administrator') {
	           						// see if there is a student or an advisor record
							$haveStudentRecord	= FALSE;
							$studentCriteria = ['relation' => 'AND',
												'clauses' => [
																['field' => 'student_call_sign', 'value' => $user_call_sign, 'compare' => '='],
																['field' => 'student_semester', 'value' => $proximateSemester, 'compare' => '=']
															]
												];
									
							$student_data = $student_dal->get_student_by_order($studentCriteria,'student_date_created','DESC',$operatingMode);
							if (count($student_data) > 0) {
								foreach($student_data as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
									$haveStudentRecord = TRUE;
								}
							}
							$haveAdvisorRecord		= FALSE;
							$advisorCriteria = [
								'relation' => 'AND',
								'clauses' => [
									[ 
										'field'   => 'advisor_call_sign', 
										'value'   => $user_call_sign, 
										'compare' => '=' 
									]
								]
							];
							$advisor_result = $advisor_dal->get_advisor_by_order($advisorCriteria, 'advisor_call_sign', 'ASC', $operatingMode);
							if (count($advisor_result) > 0) {
								foreach($advisor_result as $key=>$value) {
									foreach($value as $thisField=>$thisValue) {
										$$thisField = $thisValue;
									}
									$haveAdvisorRecord = TRUE;
								}
							}
							$content	.= "<p>To List User Master Change History for $user_call_sign click 
											<a href='$siteURL/cwa-list-user-master-callsign-history/?strpass=2&inp_callsign=$user_call_sign' 
											target='_blank'>HERE</a></p>";
							if ($haveStudentRecord) {
								$content	.= "<p>There is a <a href='$siteURL/cwa-display-and-update-student-signup-information/?strpass=2&request_type=callsign&request_info=$user_call_sign&inp_depth=all&doDebug&testMode' target='_blank'>student record</a> 
												for $student_semester semester $student_level level</p>";
							}
							if ($haveAdvisorRecord) {
								$content	.= "<p>There is an <a href='$siteURL/cwa-display-and-update-advisor-signup-info/?strpass=2&request_type=callsign&request_info=$user_call_sign&inp_depth=all&doDebug&testMode' target='_blank'>advisor record</a> 
												for $advisor_semester</p>";
							}
						}
						$displayedAlready	= TRUE;
						$haveUserMaster		= TRUE;
					}
				} else {
					$content		.= "<p>No User Master record found for $request_type - $request_info</p>";
					if ($request_type == 'callsign') {
						// see if this is a previous callsign
						if ($doDebug) {
							echo "no record found for $request_info. Looking for a previous callsign<br />";
						}
						$callsignCriteria = [
							'relation' => 'LIKE',
							'clauses' => [
								[ 
									'field'   => 'user_prev_callsign', 
									'value'   => "%$request_info%", 
									'compare' => 'LIKE' 
								]
							]
						];
						$new_user_data = $user_dal->get_user_master($callsignCriteria,'user_call_sign','ASC',$operatingMode);
						if (count($new_user_data) > 0) {
							if ($doDebug) {
								echo "found a new call sign<br />";
							}
							foreach($new_user_data as $key => $value) {
								foreach($value as $thisField => $thisValue) {
									$$thisField = $thisValue;
								}								
								$content		.= "<p>Callsign $request_info has been changed to $user_call_sign</p>";
		
								$myStr			= formatActionLog($user_action_log);
								$content		.= "<h4>User Master Data for ID $user_ID</h4>
													<form method='post' action='$theURL' 
													name='selection_form' ENCTYPE='multipart/form-data'>
													<input type='hidden' name='strpass' value='3'>
													<input type='hidden' name='inp_id' value='$user_ID'>
													<input type='hidden' name='inp_call_sign' value='$user_call_sign'>
													<input type='hidden' name='inp_mode' value='$inp_mode'>
													<input type='hidden' name='inp_verbose' value='$inp_verbose'>
													<table style='width:900px;'>
													<tr><td><b>Callsign<br />$user_call_sign</b></td>
														<td><b>Name</b><br />$user_last_name, $user_first_name</td>
														<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
														<td><b>Email</b><br />$user_email</td></tr>
													<tr><td><b>City</b><br />$user_city</td>
														<td><b>State</b><br />$user_state</td>
														<td><b>Zip Code</b><br />$user_zip_code</td>
														<td><b>Country</b><br />$user_country</td></tr>
													<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
														<td><b>Telegram</b><br />$user_telegram</td>
														<td><b>Signal</b><br />$user_signal</td>
														<td><b>Messenger</b><br />$user_messenger</td></tr>
													<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
														<td><b>Languages</b><br />$user_languages</td>
														<td><b>Date Created</b><br />$user_date_created</td>
														<td><b>Date Updated</b><br />$user_date_updated</td></tr>";
								if ($loginRole == 'administrator') {
									$content 	.= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
														<td><b>Is Admin</b><br />$user_is_admin</td>
														<td><b>Role</b><br />$user_role</td>
														<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
													<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
								}
								$content		.= "<tr><td colspan='4'><hr></td></tr>
													<tr><td colspan='4'><input type='submit' class='formInputButton' name='submit' value='Update $user_call_sign' /></td></tr>
													</table></form>";
								if ($loginRole == 'administrator') {
									$content	.= "<p>To List User Master Change History for $user_call_sign click 
													<a href='$siteURL/cwa-list-user-master-callsign-history/?strpass=2&inp_callsign=$user_call_sign' 
													target='_blank'>HERE</a></p>";
								}
								
								$displayedAlready	= TRUE;
								$haveUserMaster		= TRUE;
							}
						} else {
							$haveUserMaster			= FALSE;
							$content				.= "<p>No User Master record found searching by callsign and searching previous callsign</p>";
						}
					}
				}
			}
			if ($haveUserMaster && !$displayedAlready) {
				$myStr			= formatActionLog($user_action_log);
				$updateLink		= "<a href='$theURL/?strpass=3&inp_id=$user_id'>$user_ID<a/>";
				$content		.= "<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='3'>
									<input type='hidden' name='inp_id' value='$user_ID'>
									<input type='hidden' name='inp_call_sign' value='$user_call_sign'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<h4>User Master Data for $user_call_sign</h4>
									<table style='width:900px;'>";
				if ($loginRole == 'administrator') {
					$content	.= "<tr><td colspan='4'><b>ID</b><br />$updateLink</td></tr>";
				}
				$content		.= "<tr><td><b>Callsign<br />$user_call_sign</b></td>
										<td><b>Name</b><br />$user_last_name, $user_first_name</td>
										<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
										<td><b>Email</b><br />$user_email</td></tr>
									<tr><td><b>City</b><br />$user_city</td>
										<td><b>State</b><br />$user_state</td>
										<td><b>Zip Code</b><br />$user_zip_code</td>
										<td><b>Country</b><br />$user_country</td></tr>
									<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
										<td><b>Telegram</b><br />$user_telegram</td>
										<td><b>Signal</b><br />$user_signal</td>
										<td><b>Messenger</b><br />$user_messenger</td></tr>
									<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
										<td><b>Languages</b><br />$user_languages</td>
										<td><b>Date Created</b><br />$user_date_created</td>
										<td><b>Date Updated</b><br />$user_date_updated</td></tr>";
				if ($loginRole == 'administrator') {
					$content 	.= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
										<td><b>Is Admin</b><br />$user_is_admin</td>
										<td><b>Role</b><br />$user_role</td>
										<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
									<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
				}
				$content		.= "<tr><td colspan='4'><input type='submit' class='formInputButton' name='submit' value='Update This Information' /></td></tr>
									</table></form>";
				if ($loginRole == 'administrator') {
					$content	.= "<p>To List User Master Change History for $user_call_sign click 
									<a href='$siteURL/cwa-list-user-master-callsign-history/?strpass=2&inp_callsign=$user_call_sign' 
									target='_blank'>HERE</a></p>";
				}
			} else {
				if (!$displayedAlready) {
					if ($doDebug) {
						echo "no record found in $userMasterTableName or userMeta for $request_info<br />";
					}
					$content			.= "<p>No record found in $userMasterTableName or userMeta for $request_info</p>";
				}
			}
		}
		
		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass3 with inp_call_sign: $inp_call_sign and inp_id = $inp_id<br />";
		}
		$content		.= "<h3>$jobname for $inp_call_sign</h3>";
		
		// get the record and display it for update
		$user_data = $user_dal->get_user_master_by_id($inp_id,$operatingMode);
		if ($user_data === FALSE) {
			$content .= "FATAL ERROR: attempting to get $inp_call_sign returned FALSE<br />";
		} else {
			if (count($user_data) > 0) {
				foreach($user_data as $key => $value) {
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					$zipRequired	= '';
					if ($user_country_code == 'US') {
						$zipRequired	= ' required ';
					}
					$countrySelect = selectCountry($user_country);
					if ($countrySelect === FALSE) {
						$countrySelect = "<input type = 'text' class='formInputText' name='inp_country_code' length='5' 
										  maxlength = '5' value='$user_country_code' ><br />$user_country";
					}
 					$content	.= "<form method='post' action='$theURL' 
									name='deletion_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='5'>
									<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
									<input type='hidden' name='inp_id' value='$user_ID'>
									<input class='formInputButton' type='submit' 
									onclick=\"return confirm('Are you sure?');\"  
									value='Delete This Record' />
									</form><br /><br />
									<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='4'>
									<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
									<input type='hidden' name='inp_id' value='$user_ID'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<table style='width:900px;'>
									<tr><td>ID</td>
										<td>$user_ID</td></tr>
									<tr><td>Call Sign</td>
										<td>$user_call_sign</td></tr>
									<tr><td>First Name</td>
										<td><input type='text' class='formInputText' name='inp_first_name' length='30' 
										maxlength='30' value='$user_first_name'></td></tr>
									<tr><td>Last ame</td>
										<td><input type='text' class='formInputText' name='inp_last_name' length='50' 
										maxlength='50' value='$user_last_name'></td></tr>
									<tr><td>Email</td>
										<td><input type='text' class='formInputText' name='inp_email' length='50' 
										maxlength='50' value='$user_email'></td></tr>
									<tr><td>Ph Code</td>
										<td><input type='text' class='formInputText' name='inp_ph_code' length='5' 
										maxlength='5' value='$user_ph_code'></td></tr>
									<tr><td>Phone</td>
										<td><input type='text' class='formInputText' name='inp_phone' length='20' 
										maxlength='20' value='$user_phone'></td></tr>
									<tr><td>City</td>
										<td><input type='text' class='formInputText' name='inp_city' id='chk_city' length='30' 
										maxlength='30' value='$user_city'></td></tr>
									<tr><td>State / Province</td>
										<td><input type='text' class='formInputText' name='inp_state' id='chk_state' length='30' 
										maxlength='30' value='$user_state'></td></tr>
									<tr><td>Zip Code</td>
										<td><input type='text' class='formInputText' name='inp_zip_code' id='inp_zip_code' length='20' 
										maxlength='20' value='$user_zip_code' ></td></tr>
									<tr><td  style-'vertical-align:top;'>Country</td>
										<td>$countrySelect</td></tr>
									<tr><td>WhatsApp</td>
										<td><input type='text' class='formInputText' name='inp_whatsapp' length='20' 
										maxlength='20' value='$user_whatsapp'></td></tr>
									<tr><td>Telegram</td>
										<td><input type='text' class='formInputText' name='inp_telegram' length='20' 
										maxlength='20' value='$user_telegram'></td></tr>
									<tr><td>Signal</td>
										<td><input type='text' class='formInputText' name='inp_signal' length='20' 
										maxlength='20' value='$user_signal'></td></tr>
									<tr><td>Messenger</td>
										<td><input type='text' class='formInputText' name='inp_messenger' length='20' 
										maxlength='20' value='$user_messenger'></td></tr>
									<tr><td>Languages</td>
										<td><input type='text' class='formInputText' name='inp_languages' length='50' 
										maxlength='150' value='$user_languages'></td></tr>";
					if ($loginRole == 'administrator') {
						$adminYes			= "";
						$adminNo			= "";
						$roleStudent		= "";
						$roleAdvisor		= "";
						$roleAdmin			= "";
						if ($user_is_admin == 'Y') {
							$adminYes		= " checked ";
						} else {
							$adminNo		= " checked ";
						}
						if ($user_role == 'student' || $user_role == 'Student') {
							$roleStudent	= " checked ";
						} elseif ($user_role == 'advisor' || $user_role == 'Advisor') {
							$roleAdvisor	= " checked ";
						} elseif ($user_role == 'administrator' || $user_role == 'Administrator') {
							$roleAdmin		= " checked ";
						}
						$content 	.= "<tr><td>Survey Score</td>
											<td><input type='text' class='formInputText' name='inp_survey_score' size='5' 
												maxlength='5' value='$user_survey_score'></td></tr>
										<tr><td>Timezone ID</td>
											<td><input type='text' class='formInputText' name='inp_timezone_id' value='$user_timezone_id' size='30' maxlength='50'></td></tr>
										<tr><td style='vertical-align:top;'>is_Admin</td>
											<td><input type='radio' class='formInputButton' name='inp_is_admin' value='Y' $adminYes>Yes<br />
												<input type='radio' class='formInputButton' name='inp_is_admin' value='N' $adminNo>No</td></tr>
										<tr><td style='vertical-align:top;'>Role</td>
											<td><input type='radio' class='formInputButton' name='inp_role' value='student' $roleStudent>Student<br />
												<input type='radio' class='formInputButton' name='inp_role' value='advisor' $roleAdvisor>Advisor<br />
												<input type='radio' class='formInputButton' name='inp_role' value='administrator' $roleAdmin>Administrator</td></tr>
										<tr><td style='vertical-align:top;'>Prev Callsign</td>
												<td><textarea class='formInputText' name='inp_prev_callsign' rows='5' cols='50'>$user_prev_callsign</textarea></td></tr>
											<tr><td style='vertical-align:top;'>Action Log</td>
												<td><textarea class='formInputText' name='inp_action_log' rows='5' cols='50'>$user_action_log</textarea></td></tr>
										<tr><td>Date Created</td>
											<td><input type='text' class='formInputText' name='inp_date_created' size='20' maxlength='20' value='$user_date_created'</td></tr>
										<tr><td>Date Updated</td>
											<td>$user_date_updated</td></tr>";
					}
					$content	.= "<tr><td colspan='2'><input class='formInputButton' type='submit' onclick=\"return validate_form(this.form);\" value='Submit Updates' /></td></tr>
									</table></form>";
				}
			} else {
				if ($doDebug) {
					echo "No record found in user_master table for $inp_call_sign at id $inp_id<br />";
				}
				$content		.= "<p>No record found in user_master table for $inp_call_sign</p>";
			}
		}
		
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 4 with inp_call_sign: $inp_call_sign and inp_id: $inp_id<br >";
		}		
		$doProceed			= TRUE;
		$content			.= "<h3>Display and Update User $inp_call_sign Master Information</h3>
								<h4>Updated User Master Data</h4>";

		// get the user_master data
		if ($doDebug) {
			echo "getting the user_master data<br />";
		}
		$user_result = $user_dal->get_user_master_by_id($inp_id,$operatingMode);
		if (count($user_result) > 0) {
			foreach($user_result as $key => $value) {
				foreach($value as $thisField => $thisValue) {
					$$thisField = $thisValue;
				}
				$thisDate = date('dMy H:i');
				$updateParams	= array();
				$updateLog	= " /$thisDate $userName performed the following updates:";
				$updateContent					= "";
				$significantChange				= FALSE;
				$changeZip						= FALSE;
				$changeCity						= FALSE;
				$changeCountry					= FALSE;
				$changeTimezoneID				= FALSE;
				if ($doDebug) {
					echo "determining what has been changed<br />";
				}
				if ($inp_first_name != $user_first_name) {
					$updateParams['user_first_name']	= $inp_first_name;
					$updateContent				.= "first_name updated to $inp_first_name<br />";
					$updateLog					.= " / first_name updated to $inp_first_name";
				}
				if ($inp_last_name != $user_last_name) {
					$updateParams['user_last_name']	= $inp_last_name;
					$updateContent				.= "last_name updated to $inp_last_name<br />";
					$updateLog					.= " / last_name updated to $inp_last_name";
				}
				if ($inp_email != $user_email) {
					$updateParams['user_email']	= $inp_email;
					$updateContent			.= "email updated to $inp_email<br />";
					$updateLog				.= " / email updated to $inp_email";
				}
				if ($inp_phone != $user_phone) {
					$updateParams['user_phone']	= $inp_phone;
					$updateContent			.= "phone updated to $inp_phone<br />";
					$updateLog				.= " / phone updated to $inp_phone";
				}
				
				if ($inp_city != $user_city) {
					$updateParams['user_city']	= $inp_city;
					$updateContent			.= "city updated to $inp_city<br />";
					$updateLog				.= " / city updated to $inp_city";
					$signifcantChange		= TRUE;
					$changeCity				= TRUE;
					$user_city				= $inp_city;
				}
				if ($inp_state != $user_state) {
					$updateParams['user_state']	= $inp_state;
					$updateContent			.= "state updated to $inp_state<br />";
					$updateLog				.= " / state updated to $inp_state";
					$user_state				= $inp_state;
				}
				if ($inp_zip_code != $user_zip_code) {
					$updateParams['user_zip_code']	= $inp_zip_code;
					$updateContent				.= "zip_code updated to $inp_zip_code<br />";
					$updateLog					.= " / zip_code updated to $inp_zip_code";
					$significantChange			= TRUE;
					$changeZip					= TRUE;
					$user_zip_code				= $inp_zip_code;
				}
				if ($inp_country_code != $user_country_code) {
					$updateParams['user_country_code']	= $inp_country_code;
					$updateContent					.= "country_code updated to $inp_country_code<br />";
					$updateLog						.= " / country_code updated to $inp_country_code";
					$significantChange				= TRUE;
					$changeCountry					= TRUE;
					$user_country_code				= $inp_country_code;
				}
				if ($inp_ph_code != $user_ph_code) {
					$updateParams['user_ph_code']	= $inp_ph_code;
					$updateContent			.= "ph_code updated to $inp_ph_code<br />";
					$updateLog				.= " / ph_code updated to $inp_ph_code";
				}
				if ($inp_country != $user_country) {
					$updateParams['user_country']	= $inp_country;
					$updateContent					.= "country updated to $inp_country<br />";
					$updateLog						.= " / country updated to $inp_country";
					$user_country					= $inp_country;
				}
				if ($inp_whatsapp != $user_whatsapp) {
					$updateParams['user_whatsapp']	= $inp_whatsapp;
					$updateContent				.= "whatsapp updated to $inp_whatsapp<br />";
					$updateLog					.= " / whatsapp updated to $inp_whatsapp";
				}
				if ($inp_telegram != $user_telegram) {
					$updateParams['user_telegram']	= $inp_telegram;
					$updateContent				.= "telegram updated to $inp_telegram<br />";
					$updateLog					.= " / telegram updated to $inp_telegram";
				}
				if ($inp_signal != $user_signal) {
					$updateParams['user_signal']	= $inp_signal;
					$updateContent			.= "signal updated to $inp_signal<br />";
					$updateLog				.= " / signal updated to $inp_signal";
				}				
				if ($inp_messenger != $user_messenger) {
					$updateParams['user_messenger']	= $inp_messenger;
					$updateContent				.= "messenger updated to $inp_messenger<br />";
					$updateLog					.= " / messenger updated to $inp_messenger";
				}
				if ($inp_languages != $user_languages) {
					$updateParams['user_languages']	= $inp_languages;
					$updateContent				.= "languages updated to $inp_languages<br />";
					$updateLog					.= " / languages updated to $inp_languages";
				}
				
				if ($loginRole == 'administrator') {		
					if ($inp_survey_score != $user_survey_score) {
						$updateParams['user_survey_score']	= $inp_survey_score;
						$updateContent				.= "survey_score updated to $inp_survey_score<br />";
						$updateLog					.= " / survey_score updated to $inp_survey_score";
					}
					if ($inp_timezone_id != $user_timezone_id) {
						$updateParams['user_timezone_id']	= $inp_timezone_id;
						$updateContent				.= "timezone_id updated to $inp_timezone_id<br />";
						$updateLog					.= " / timezone_id updated to $inp_timezone_id";
						$user_timezone_id			= $inp_timezone_id;
						$changeTimezoneID			= TRUE;
						if ($significantChange) {
							$significantChange		= FALSE;
						}
					}
					if ($inp_is_admin != $user_is_admin) {
						$updateParams['user_is_admin']	= $inp_is_admin;
						$updateContent				.= "is_admin updated to $inp_is_admin<br />";
						$updateLog					.= " / is_admin updated to $inp_is_admin";
					}
					if ($inp_role != $user_role) {
						$updateParams['user_role']	= $inp_role;
						$updateContent				.= "role updated to $inp_role<br />";
						$updateLog					.= " / role updated to $inp_role";
					}
					if ($inp_action_log != $user_action_log) {
						$updateLog					.= " / Action log was updated";
						$updateContent				.= "Action log was updated<br />";
					}
					if ($inp_prev_callsign != $user_prev_callsign) {
						$updateParams['user_prev_callsign']	= $inp_prev_callsign;
						$updateContent				.= "prev_callsign updated to $inp_prev_callsign<br />";
						$updateLog					.= " / prev_callsign updated to $inp_prev_callsign";
					}
					if ($inp_date_created != $user_date_created) {
						$updateLog					.= " / date_created was updated";
						$updateContent				.= "date_created was updated<br />";
						$updateParams['user_date_created']	= $inp_date_created;
					}
					
				} else {
					if ($doDebug) {
						echo "skipping survey_score, timezone_id, role, prev_callsign, and action_log as user is not an admin<br />"; 
					}
				}
				if ($user_timezone_id == '??' || $user_timezone_id == '') {
					$significantChange	= TRUE;
					if ($doDebug) {
						echo "user_timezone_id is ?? or blank. Setting significantChange to TRUE<br />";
					}
				}
				
				if ($significantChange) {
					if ($doDebug) {
						echo "A significant change has occurred<br />";
					}
					$doSignificantChange			= FALSE;
					// If US: zip code change is significant. City change is not
					if ($inp_country_code == 'US' && $changeZip) {
						$doSignificantChange		= TRUE;
					} elseif ($inp_country_code == 'CA' && $changeZip) {
						$doSignificantChange		= TRUE;
					} else {		/// treat any of the changes as significant
						$doSignificantChange		= TRUE;
					}
					if ($doSignificantChange) {
						// figure out the timezone_id
						if ($doDebug) {
							echo "figuring out the timezone_id<br />";
						}
						$doCheck				= TRUE;
						if ($user_country_code == '') {
							$user_timezone_id 	= '??';
							$doCheck			= FALSE;
						} else {
							if ($user_zip_code != '') {
								// check using zip code
								if ($user_country_code == 'US') {
									if (strlen($user_zip_code) < 5) {
										if ($doDebug) {
											echo "Have invalid US zip code<br />";
										}
										$user_zip_code		= '??';
										$user_timezone_id	= '??';
										$doCheck		 	= FALSE;
									} else {
										$myInt			= strpos($user_zip_code,"-");
										if ($myInt === FALSE) {
											$checkStr			= array('zip'=>$user_zip_code,
																		'country'=>$user_country);
										} else {
											$newZip				= substr($user_zip_code,0,$myInt);
											$checkStr			= array('zip'=>$newZip,
																		'country'=>$user_country);
										}
									}
								} else {
									$checkStr			= array('zip'=>$user_zip_code,
																'country'=>$user_country);
								}
							} else {
								if ($user_country_code == 'US') {
									$user_zip_code 		= '??';
									$user_timezone_id	= '??';
									$doCheck			= FALSE;
									if ($doDebug) {
										echo "US country code and empty zipcode. Set zipcode to ??<br />";
									}
								}
								// no zipcode. check using address info
								$checkStr			= array('city'=>$user_city,
															'state'=>$user_state,
															'country'=>$user_country);
							}
							if ($doCheck) {
								$checkStr['doDebug']	= $doDebug;
								$user_timezone_id		= getTimeZone($checkStr);
							}
						}
						if ($user_timezone_id == '??') {		// unable to get the id
							if ($doDebug) {
								echo "timezone_id is ??<br />
									  user_zip_code: $user_zip_code<br />
									  user_city: $user_city<br >
									  user_state: $user_state<br />
									  $user_zip_code: $user_zip_code<br />
									  $user_country: $user_country<br />";
							}
							$content	.= "<h3>$jobname</h3>
											<p>The program was not able to determine your 
											timezone identifier. No updates can be made 
											until the data is correct and the timezone 
											identifier can be determined.</p>
											<p>First, click the 'Back' button and make certain 
											that the following fields are correct and valid:<br />
											<ul><li>Country: the value in this field must 
													be a valid country name
												<li>Zip / Postal Code: the code must valid 
													or, if Postal Codes are not used in 
													$user_country, this field must be empty
												<li>City: the value in this field must be 
													a valid city name
												<li>State / Province: Again, a valid name
											</ul>
											<p>If all that information is valid, the please email the 
												appropriate person at <a href='https://cwops.org/cwa-class-resolution/' 
												target='_blank'>Contact Information</a>. The process to figure out 
												a timezone identifier is quite complex and may need to be updated.";
							goto bypass;
						} else {
							$updateParams['user_timezone_id']	= $user_timezone_id;
							$inp_timezone_id					= $user_timezone_id;
							$changeTimezoneID					= TRUE;
						}
					}
				}
					
				// see if the timezone_id has changed
				if ($changeTimezoneID) {	// it has changed								
					if ($doDebug) {
						echo "timezone_id has changed<br >";
					}
					if ($user_role == 'student') {
						if ($doDebug) {
							echo "timezone has changed. Updating current and future student records<br />";
						}					
						// now update the offset in any current or future student records
						$criteria = [
							'relation' => 'AND',
							'clauses' => [
								// field1 = $value1
								['field' => 'student_call_sign', 'value' => $user_call_sign, 'compare' => '='],
								
								// (field2 = $value2 OR field2 = $value3)
								[
									'relation' => 'OR',
									'clauses' => [
										['field' => 'student_semester', 'value' => $currentSemester, 'compare' => '='],
										['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '='],
										['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '='],
										['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '='],
										['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '=']
									]
								]
							]
						];
						$student_data = $student_dal->get_student_by_order($criteria, 'student_call_sign', 'ASC', $operatingMode);
						if (count($student_data) > 0) {
							foreach($student_data as $key=>$value) {
								foreach($value as $thisField => $thisValue) {
									$thisField .= "_fix";
									$$thisField = $thisValue;
								}
								$myArray				= explode(" ",$student_semester_fix);
								$thisYear				= $myArray[0];
								$thisMonDay				= $myArray[1];
								$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
								$myMonDay				= $myConvertArray[$thisMonDay];
								$thisNewDate			= "$thisYear$myMonDay 00:00:00";
								if ($doDebug) {
									echo "converted $student_semester_fix to $thisNewDate<br />";
								}
								$dateTimeZoneLocal 		= new DateTimeZone($inp_timezone_id);
								$dateTimeZoneUTC 		= new DateTimeZone("UTC");
								$dateTimeLocal 			= new DateTime($thisNewDate,$dateTimeZoneLocal);
								$dateTimeUTC			= new DateTime($thisNewDate,$dateTimeZoneUTC);
								$php2 					= $dateTimeZoneLocal->getOffset($dateTimeUTC);
								$inp_timezone_offset 	= $php2/3600;
								
								$thisDate				= date('dMy H:i');
								$student_action_log		.= "/ $thisDate $userName $jobname: updated timezone to $inp_timezone_id and offset to $inp_timezone_offset ";
								
								$thisUpdateParams		= array('student_time_zone'=>$inp_timezone_id,
																'student_timezone_offset'=>$inp_timezone_offset, 
																'student_action_log'=>$student_action_log);
								$updateResult = $student_dal->update($student_id_fix, $thisUpdateParams, $operatingMode);
								if ($updateResult === FALSE) {
									$content .= "Failed to update TZ info for $student_call_sign_fix at id $student_id_fix<br />";
								} else {
									if ($doDebug) {
										echo "updated timezone to $inp_timezone_id and offset to $inp_timezone_offset for $inp_call_sign at id $student_id_fix<br >";
									}
								}
							}
						}
					} elseif ($user_role == 'advisor') {
						if ($doDebug) {
							echo "timezone has changed. updating offset in current and future advisorClass records<br />";
						}
						$criteria = [
							'relation' => 'AND',
							'clauses' => [
								// field1 = $value1
								['field' => 'advisorclass_call_sign', 'value' => $user_call_sign, 'compare' => '='],
								
								// (field2 = $value2 OR field2 = $value3)
								[
									'relation' => 'OR',
									'clauses' => [
										['field' => 'advisorclass_semester', 'value' => $currentSemester, 'compare' => '='],
										['field' => 'advisorclass_semester', 'value' => $nextSemester, 'compare' => '='],
										['field' => 'advisorclass_semester', 'value' => $semesterTwo, 'compare' => '='],
										['field' => 'advisorclass_semester', 'value' => $semesterThree, 'compare' => '='],
										['field' => 'advisorclass_semester', 'value' => $semesterFour, 'compare' => '=']
									]
								]
							]
						];
						$advisorClassResult = $advisorclass_dal->get_advisorclasses_by_order($criteria, 'advisorclass_call_sign', 'ASC', $operatingMode);
						if (count($advisorClassResult) > 0) {
							foreach($advisorClassResult as $key => $value) {
								foreach($value as $thisField=>$thisValue) {
									$thisField .= "_fix";
									$$thisField = $thisValue;
									if ($doDebug) {
										echo "set $thisField to $thisValue<br />";
									}
								}
								if ($doDebug) {
									echo "have advisorclass_id of $advisorclass_id_fix to update<br />";
								}
								$myArray				= explode(" ",$advisorclass_semester_fix);
								$thisYear				= $myArray[0];
								$thisMonDay				= $myArray[1];
								$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
								$myMonDay				= $myConvertArray[$thisMonDay];
								$thisNewDate			= "$thisYear$myMonDay 00:00:00";
								if ($doDebug) {
									echo "converted $advisorclass_semester_fix to $thisNewDate<br />";
								}
								$dateTimeZoneLocal 		= new DateTimeZone($inp_timezone_id);
								$dateTimeZoneUTC 		= new DateTimeZone("UTC");
								$dateTimeLocal 			= new DateTime($thisNewDate,$dateTimeZoneLocal);
								$dateTimeUTC			= new DateTime($thisNewDate,$dateTimeZoneUTC);
								$php2 					= $dateTimeZoneLocal->getOffset($dateTimeUTC);
								$inp_timezone_offset 	= $php2/3600;

								$thisDate				= date('dMy H:i');

								$advisorclass_action_log_fix	.= "/ $thisDate $userName $jobname: updated timezone offset to $inp_timezone_offset ";
								$ACUpdateParams			= array('advisorclass_timezone_offset'=>$inp_timezone_offset, 
																'advisorclass_action_log'=>$advisorclass_action_log_fix);

								$ACUpdateResult = $advisorclass_dal->update($advisorclass_id_fix,$ACUpdateParams,$operatingMode);
								if ($ACUpdateResult === FALSE) {
									$content .= "Attempting to update the advisorclass record for $user_call_sign returned FALSE<br />";
								} else {
									if ($doDebug) {
										echo "$advisorclass_id_fix id was updated<br />";
									}
								}
							}
						}
					}
				}

				if ($updateLog != '') {		/// have changes to apply
					if ($doDebug) {
						echo "<br />updateParams:<br /><pre>";
						print_r($updateParams);
						echo "</pre><br />
							  updateContent: $updateContent<br >
							  updateLog: $updateLog<br />";
					}

					$doProceed			= TRUE;
					// do the update
					$user_action_log					= $inp_action_log . " / $actionLogDate $userName $jobname $updateLog";
					$updateParams['user_action_log']	= $user_action_log;
					$updateResult = $user_dal->update($user_ID,$updateParams,$operatingMode);
					if ($updateResult === FALSE) {
						$content			.= "Updating the user_master record for $user_call_sign failed<br />";
						$doProceed				= FALSE;
					} else {
						if ($doDebug) {
							echo "updating the user master information for $user_call_sign was successful<br />";
						}
					}
				} else {
					$content		.= "<p>No updates requested</p>";
					if ($doDebug) {
						echo "no updates requested<br />";
					}
				}
				if ($doProceed) {
					////// get user_master
					$getResult		= $user_dal->get_user_master_by_id($user_ID,$operatingMode);
					if ($getResult === FALSE) {
						$content .= "Attempting to redisplay $user_ID returned FALSE<br />";
					} else {
						foreach($getResult as $key => $value) {
							foreach($value as $thisField => $thisValue) {
								$$thisField = $thisValue;
							}
							//// display user_master
							$myStr			= formatActionLog($user_action_log);
							$content		.= "<h4>User Master Data</h4>
												$updateContent
											<table style='width:900px;'>
											<tr><td><b>Callsign<br />$user_call_sign</b></td>
												<td><b>Name</b><br />$user_last_name, $user_first_name</td>
												<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
												<td><b>Email</b><br />$user_email</td></tr>
											<tr><td><b>City</b><br />$user_city</td>
												<td><b>State</b><br />$user_state</td>
												<td><b>Zip Code</b><br />$user_zip_code</td>
												<td><b>Country</b><br />$user_country</td></tr>
											<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
												<td><b>Telegram</b><br />$user_telegram</td>
												<td><b>Signal</b><br />$user_signal</td>
												<td><b>Messenger</b><br />$user_messenger</td></tr>
											<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
												<td><b>Languages</b><br />$user_languages</td>
												<td><b>Date Created</b><br />$user_date_created</td>
												<td><b>Date Updated</b><br />$user_date_updated</td></tr>";
							if ($loginRole == 'administrator') {
								$content .= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
												<td><b>Is Admin</b><br />$user_is_admin</td>
												<td><b>Role</b><br />$user_role</td>
												<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
											<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
							}
							$content	.= "</table>
											<p>Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$user_call_sign&inp_depth=one$doDebug=$doDebug&testMode=$testMode' 
											target='_blank'>HERE</a> to update the advisor Master Data</p>";
							if ($loginRole == 'administrator') {
								$content	.= "<p>To List User Master Change History for $user_call_sign click 
												<a href='$siteURL/cwa-list-user-master-callsign-history/?strpass=2&inp_callsign=$user_call_sign' 
												target='_blank'>HERE</a></p>
												<p>To show a different callsign, click <a href='$theURL'>HERE</a></p>";
							}
						}
					}
				} else {
					if ($doDebug) {
						echo "No record found in $userMasterTableName for $inp_call_sign<br />";
					}
					$content			.= "<p>No record found in $userMasterTableName for $inp_call_sign</p>";
				}
			}
		} else {
			$content		.= "<p>Unable to obtain the $userMasterTableName record for $inp_call_sign</p>";
		}
		bypass:
//////////// Pass 5 ... delete the record		
			
	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 5 to delete $inp_call_sign at $inp_id id record<br />";
		}
		$doProceed		= TRUE;
		$content		.= "<h3>$jobname for $inp_call_sign</h3>
							<h4>Deleting the User Master Record</h4>";

		// get the user_master record
		$getResult = $user_dal->get_user_master_by_id($inp_id,$operatingMode);
		if ($getResult === FALSE) {
			$content .= "Attempting to retrieve record id $user_ID returned FALSE<br />";
		} else {
			foreach($getResult as $key=>$value) {
				foreach($value as $thisField => $thisValue) {
					$$thisField = $thisValue;
				}
				if ($doDebug) {
					echo "we have a user_master record that can be deleted<br />";
				}
				$deleteResult = $user_dal->delete($inp_id,$operatingMode);
				if ($deleteResult === FALSE) {
					$content			.= "The attempt to delete user master record for $inp_call_sign at $inp_id failed<br />";
				} else {
					$content		.= "The user master record id $inp_id for $inp_call_sign has been deleted<br />";
				}
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
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}

	return $content;
}
add_shortcode ('display_and_update_user_master', 'display_and_update_user_master_func');