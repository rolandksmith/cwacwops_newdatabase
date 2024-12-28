function display_and_update_user_master_func() {

/*

	Modified 7Nov24 by Roland to include previous callsigns
*/

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
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	

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
				$inp_id = strtoupper($str_value);
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
		</style>";	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode</strong></p>";
		}
		$extMode					= 'tm';
		$userMasterTableName		= "wpw1_cwa_user_master2";
		$studentTableName			= "wpw1_cwa_student2";
		$countryCodesTableName		= 'wpw1_cwa_country_codes';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass2';
		$userHistoryTableName		= 'wpw1_cwa_user_master_history2';
	} else {
		$extMode					= 'pd';
		$userMasterTableName		= "wpw1_cwa_user_master";
		$studentTableName			= "wpw1_cwa_student";
		$countryCodesTableName		= 'wpw1_cwa_country_codes';
		$advisorClassTableName		= 'wpw1_cwa_advisorclass';
		$userHistoryTableName		= 'wpw1_cwa_user_master_history';
	}

	if ($strPass == "1") {
		if ($userRole == 'administrator') {
			$strPass					= "1";
		} else {
			$inp_callsign				= strtoupper($userName);
			$strPass					= "2";
			$callSignMethod				= TRUE;
			$runByUser					= TRUE;	// job run from link on user's portal
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
	

///// Pass 2 -- do the work


	} 
	if ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2<br />";
		}
		$doProceed					= TRUE;
		$displayedAlready			= FALSE;

		if ($callSignMethod) {
			$request_type					= 'callsign';
			$request_info				= $inp_callsign;
			if ($doDebug) {
				echo "callSignMethod is TRUE. Setting request_type to $request_type and request_info to $request_info<br />";
			}
		} else {
			if ($doDebug) {
				echo "callSignMethod is FALSE. request_type is set to $request_type and request_info is set to $request_info<br />";
			}
		}

	
		$content			.= "<h3>$jobname</h3>";
		if ($userRole == 'administrator') {
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
			$sql			= "select * from $userMasterTableName 
								where user_call_sign = '$request_info'";
		} elseif ($request_type == 'id') {
			$sql			= "select * from $userMasterTableName 
								where user_ID = $request_info";
		} elseif ($request_type == 'surname') {
			$sql			= "select * from $userMasterTableName 
								where user_last_name like '%$request_info%' 
								order by user_call_sign";
		} elseif ($request_type == 'given') {
			$sql			= "select * from $userMasterTableName 
								where user_first_name like '%$request_info%' 
								order by user_call_sign";
		} elseif ($request_type == 'email') {
			$sql			= "select * from $userMasterTableName 
								where user_email = '$request_info' 
								order by user_call_sign";
		} else {
			if ($doDebug) {
				echo "invalid request_type of $request_type<br />";
			}
			$content	.= "<p>Sorry. Invalid method entered</p>";
			$doProceed	= FALSE;
		}
		
		if ($doProceed) {
		
			$sqlResult		= $wpdb->get_results($sql);
			if ($sqlResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"pass2. attempting to get results from $sql. UserName: $userName");
			} else {
				$numRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					$haveUserMaster				= TRUE;
					foreach($sqlResult as $sqlRow) {
						$user_id				= $sqlRow->user_ID;
						$user_call_sign			= $sqlRow->user_call_sign;
						$user_first_name		= $sqlRow->user_first_name;
						$user_last_name			= $sqlRow->user_last_name;
						$user_email				= $sqlRow->user_email;
						$user_ph_code			= $sqlRow->user_ph_code;
						$user_phone				= $sqlRow->user_phone;
						$user_city				= $sqlRow->user_city;
						$user_state				= $sqlRow->user_state;
						$user_zip_code			= $sqlRow->user_zip_code;
						$user_country_code		= $sqlRow->user_country_code;
						$user_country			= $sqlRow->user_country;
						$user_whatsapp			= $sqlRow->user_whatsapp;
						$user_telegram			= $sqlRow->user_telegram;
						$user_signal			= $sqlRow->user_signal;
						$user_messenger			= $sqlRow->user_messenger;
						$user_action_log		= $sqlRow->user_action_log;
						$user_timezone_id		= $sqlRow->user_timezone_id;
						$user_languages			= $sqlRow->user_languages;
						$user_survey_score		= $sqlRow->user_survey_score;
						$user_is_admin			= $sqlRow->user_is_admin;
						$user_role				= $sqlRow->user_role;
						$user_prev_callsign		= $sqlRow->user_prev_callsign;
						$user_date_created		= $sqlRow->user_date_created;
						$user_date_updated		= $sqlRow->user_date_updated;

						$myStr			= formatActionLog($user_action_log);
						$content		.= "<h4>User Master Data</h4>
											<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='3'>
											<input type='hidden' name='inp_id' value='$user_id'>
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
						if ($userRole == 'administrator') {
							$content 	.= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
												<td><b>Is Admin</b><br />$user_is_admin</td>
												<td><b>Role</b><br />$user_role</td>
												<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
											<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
						}
						$content		.= "<tr><td colspan='4'><hr></td></tr>
											<tr><td colspan='4'><input type='submit' class='formInputButton' name='submit' value='Update $user_call_sign' /></td></tr>
											</table></form>";
						if ($userRole == 'administrator') {
							$content	.= "<p>To List User Master Change History for $user_call_sign click 
											<a href='$siteURL/cwa-list-user-master-callsign-history/?strpass=2&inp_callsign=$user_call_sign' 
											target='_blank'>HERE</a></p>";
						}
						
						$displayedAlready	= TRUE;
						$haveUserMaster		= TRUE;
					}
				} else {
					$content		.= "<p>No User Master record found for $request_info</p>";
					if ($request_type == 'callsign') {
						// see if this is a previous callsign
						if ($doDebug) {
							echo "no record found running $sql. Looking for a previous callsign<br />";
						}
						$sql1		= "select * from $userMasterTableName 
										where user_prev_callsign like '%$request_info%'";
						$sqlResult		= $wpdb->get_results($sql1);
						if ($sqlResult === FALSE) {
							handleWPDBError($jobname,$doDebug,"pass2. No record using callsign. Trying to get results from $sql1. UserName: $userName");
						} else {
							$numRows	= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $sql<br />and retrieved $numRows rows<br />";
							}
							if ($numRows > 0) {
								$haveUserMaster				= TRUE;
								foreach($sqlResult as $sqlRow) {
									$user_id				= $sqlRow->user_ID;
									$user_call_sign			= $sqlRow->user_call_sign;
									$user_first_name		= $sqlRow->user_first_name;
									$user_last_name			= $sqlRow->user_last_name;
									$user_email				= $sqlRow->user_email;
									$user_ph_code			= $sqlRow->user_ph_code;
									$user_phone				= $sqlRow->user_phone;
									$user_city				= $sqlRow->user_city;
									$user_state				= $sqlRow->user_state;
									$user_zip_code			= $sqlRow->user_zip_code;
									$user_country_code		= $sqlRow->user_country_code;
									$user_country			= $sqlRow->user_country;
									$user_whatsapp			= $sqlRow->user_whatsapp;
									$user_telegram			= $sqlRow->user_telegram;
									$user_signal			= $sqlRow->user_signal;
									$user_messenger			= $sqlRow->user_messenger;
									$user_action_log		= $sqlRow->user_action_log;
									$user_timezone_id		= $sqlRow->user_timezone_id;
									$user_languages			= $sqlRow->user_languages;
									$user_survey_score		= $sqlRow->user_survey_score;
									$user_is_admin			= $sqlRow->user_is_admin;
									$user_role				= $sqlRow->user_role;
									$user_prev_callsign		= $sqlRow->user_prev_callsign;
									$user_date_created		= $sqlRow->user_date_created;
									$user_date_updated		= $sqlRow->user_date_updated;
			
									$content		.= "<p>Callsign $request_info has been changed to $user_call_sign</p>";
			
									$myStr			= formatActionLog($user_action_log);
									$content		.= "<h4>User Master Data</h4>
														<form method='post' action='$theURL' 
														name='selection_form' ENCTYPE='multipart/form-data'>
														<input type='hidden' name='strpass' value='3'>
														<input type='hidden' name='inp_id' value='$user_id'>
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
									if ($userRole == 'administrator') {
										$content 	.= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
															<td><b>Is Admin</b><br />$user_is_admin</td>
															<td><b>Role</b><br />$user_role</td>
															<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
														<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
									}
									$content		.= "<tr><td colspan='4'><hr></td></tr>
														<tr><td colspan='4'><input type='submit' class='formInputButton' name='submit' value='Update $user_call_sign' /></td></tr>
														</table></form>";
									if ($userRole == 'administrator') {
										$content	.= "<p>To List User Master Change History for $user_call_sign click 
														<a href='$siteURL/cwa-list-user-master-callsign-history/?strpass=2&inp_callsign=$user_call_sign' 
														target='_blank'>HERE</a></p>";
									}
									
									$displayedAlready	= TRUE;
									$haveUserMaster		= TRUE;
								}
							} else {
//								sendErrorEmail("$jobname. Pass2. No User Master record running $sql1. UserName: $userName");
								$haveUserMaster			= FALSE;
								$content				.= "<p>No User Master record found searching by callsign and searching previous callsign</p>";
							}
						}
					}
				}
			}
			if ($haveUserMaster && !$displayedAlready) {
				$myStr			= formatActionLog($user_action_log);
				$updateLink		= "<a href='$theURL/?strpass=3&inp_id=$user_id'>$user_id<a/>";
				$content		.= "<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='3'>
									<input type='hidden' name='inp_id' value='$user_id'>
									<input type='hidden' name='inp_call_sign' value='$user_call_sign'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<h4>User Master Data for $user_call_sign</h4>
									<table style='width:900px;'>";
				if ($userRole == 'administrator') {
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
				if ($userRole == 'administrator') {
					$content 	.= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
										<td><b>Is Admin</b><br />$user_is_admin</td>
										<td><b>Role</b><br />$user_role</td>
										<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
									<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
				}
				$content		.= "<tr><td colspan='4'><input type='submit' class='formInputButton' name='submit' value='Update This Information' /></td></tr>
									</table></form>";
				if ($userRole == 'administrator') {
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
			echo "<br />arrived at pass3 with inp_call_sign: $inp_call_sign<br />";
		}
		$content		.= "<h3>$jobname for $inp_call_sign</h3>";
		
		// get the record and display it for update
		$sql			= "select * from $userMasterTableName 
							where user_call_sign = '$inp_call_sign'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_call_sign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_ph_code			= $sqlRow->user_ph_code;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_country			= $sqlRow->user_country;
					$user_whatsapp			= $sqlRow->user_whatsapp;
					$user_telegram			= $sqlRow->user_telegram;
					$user_signal			= $sqlRow->user_signal;
					$user_messenger			= $sqlRow->user_messenger;
					$user_action_log		= $sqlRow->user_action_log;
					$user_timezone_id		= $sqlRow->user_timezone_id;
					$user_languages			= $sqlRow->user_languages;
					$user_survey_score		= $sqlRow->user_survey_score;
					$user_is_admin			= $sqlRow->user_is_admin;
					$user_role				= $sqlRow->user_role;
					$user_prev_callsign		= $sqlRow->user_prev_callsign;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;
	
					// get the country codes and country information
					$countryOptionList			= "";
					$option1					= "";
					$countrySQL		= "select * from wpw1_cwa_country_codes 
										order by country_name";
					$countryResult	= $wpdb->get_results($countrySQL);
					if ($countryResult=== FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numCRows	= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if ($numCRows > 0) {
							foreach($countryResult as $countryRow) {
								$record_id			= $countryRow->record_id;
								$thisCountryCode	= $countryRow->country_code;
								$thisCountryName		= $countryRow->country_name;
								$thisPhCode			= $countryRow->ph_code;
								
								if ($user_country_code == $thisCountryCode) {
									$option1		= "<option value='$thisCountryCode|$thisCountryName|$thisPhCode' selected>$thisCountryName</option>\n";
								}
								
								$countryOptionList	.= "<option value='$thisCountryCode|$thisCountryName|$thisPhCode'>$thisCountryName</option>\n";
								
							}
						}
					}

					$content	.= "<form method='post' action='$theURL' 
									name='deletion_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='5'>
									<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
									<input type='hidden' name='inp_id' value='$user_id'>
									<input class='formInputButton' type='submit' 
									onclick=\"return confirm('Are you sure?');\"  
									value='Delete This Record' />
									</form><br /><br />
									<form method='post' action='$theURL' 
									name='selection_form' ENCTYPE='multipart/form-data'>
									<input type='hidden' name='strpass' value='4'>
									<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
									<input type='hidden' name='inp_mode' value='$inp_mode'>
									<input type='hidden' name='inp_verbose' value='$inp_verbose'>
									<table style='width:900px;'>
									<tr><td>ID</td>
										<td>$user_id</td></tr>
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
										<td><input type='text' class='formInputText' name='inp_city' length='30' 
										maxlength='30' value='$user_city'></td></tr>
									<tr><td>State</td>
										<td><input type='text' class='formInputText' name='inp_state' length='30' 
										maxlength='30' value='$user_state'></td></tr>
									<tr><td>Zip Code</td>
										<td><input type='text' class='formInputText' name='inp_zip_code' length='20' 
										maxlength='20' value='$user_zip_code'></td></tr>
									<tr><td>Country</td>
										<td><select name='inp_country_data' class='formSelect' size='5'>
											$option1
											$countryOptionList
											</select></td></tr>
									<tr><td>Whatsapp</td>
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
					if ($userRole == 'administrator') {
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
						if ($user_role == 'student') {
							$roleStudent	= " checked ";
						} elseif ($user_role == 'advisor') {
							$roleAdvisor	= " checked ";
						} elseif ($user_role == 'administrator') {
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
											<td>$user_date_created</td></tr>
										<tr><td>Date Updated</td>
											<td>$user_date_updated</td></tr>";
					}
					$content	.= "<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit Updates' /></td></tr>
									</table></form>";
				}
			} else {
				if ($doDebug) {
					echo "No record found in $userMasterTableName for $inp_call_sign<br />";
				}
				$content		.= "<p>No record found in $userMasterTableName for $inp_call_sign</p>";
			}
		}
		
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 4 with inp_call_sign: $inp_call_sign<br >";
		}
		
		$strPass			= "4B";
		
		$doProceed			= TRUE;
		$content			.= "<h3>Display and Update User $inp_call_sign Master Information</h3>
								<h4>Updated User Master Data</h4>";

		// get the user_master data
		if ($doDebug) {
			echo "getting the user_master data<br />";
		}
		$sql			= "select * from $userMasterTableName 
							where user_call_sign = '$inp_call_sign'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_call_sign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_ph_code			= $sqlRow->user_ph_code;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_country			= $sqlRow->user_country;
					$user_whatsapp			= $sqlRow->user_whatsapp;
					$user_telegram			= $sqlRow->user_telegram;
					$user_signal			= $sqlRow->user_signal;
					$user_messenger			= $sqlRow->user_messenger;
					$user_action_log		= $sqlRow->user_action_log;
					$user_timezone_id		= $sqlRow->user_timezone_id;
					$user_languages			= $sqlRow->user_languages;
					$user_survey_score		= $sqlRow->user_survey_score;
					$user_is_admin			= $sqlRow->user_is_admin;
					$user_role				= $sqlRow->user_role;
					$user_prev_callsign		= $sqlRow->user_prev_callsign;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;
	

					$thisDate = date('dMy H:i');
					$updateParams	= array();
					$updateFormat	= array();
					$updateLog	= " /$thisDate $userName performed the following updates:";
					$updateContent					= "";
					$significantChange				= FALSE;
					$changeZip						= FALSE;
					$changeCity						= FALSE;
					$changeCountry					= FALSE;
					if ($doDebug) {
						echo "determining what has been changed<br />";
					}
					if ($inp_first_name != $user_first_name) {
						$updateParams['user_first_name']	= $inp_first_name;
						$updateFormat[]				= "%s";
						$updateContent				.= "first_name updated to $inp_first_name<br />";
						$updateLog					.= " / first_name updated to $inp_first_name";
					}
					if ($inp_last_name != $user_last_name) {
						$updateParams['user_last_name']	= $inp_last_name;
						$updateFormat[]			 	= '%s';
						$updateContent				.= "last_name updated to $inp_last_name<br />";
						$updateLog					.= " / last_name updated to $inp_last_name";
					}
					if ($inp_email != $user_email) {
						$updateParams['user_email']	= $inp_email;
						$updateFormat[]			= '%s';
						$updateContent			.= "email updated to $inp_email<br />";
						$updateLog				.= " / email updated to $inp_email";
					}
					if ($inp_phone != $user_phone) {
						$updateParams['user_phone']	= $inp_phone;
						$updateFormat[]			= '%s';
						$updateContent			.= "phone updated to $inp_phone<br />";
						$updateLog				.= " / phone updated to $inp_phone";
					}
//					if ($inp_ph_code != $user_ph_code) {
//						$updateParams['user_ph_code']	= $inp_ph_code;
//						$updateFormat[]			= '%s';
//						$updateContent			.= "ph_code updated to $inp_ph_code<br />";
//						$updateLog				.= " / ph_code updated to $inp_ph_code";
//					}
					if ($inp_city != $user_city) {
						$updateParams['user_city']	= $inp_city;
						$updateFormat[]			= '%s';
						$updateContent			.= "city updated to $inp_city<br />";
						$updateLog				.= " / city updated to $inp_city";
						$signifcantChange		= TRUE;
						$changeCity				= TRUE;
					}
					if ($inp_state != $user_state) {
						$updateParams['user_state']	= $inp_state;
						$updateFormat[]			= '%s';
						$updateContent			.= "state updated to $inp_state<br />";
						$updateLog				.= " / state updated to $inp_state";
					}
					if ($inp_zip_code != $user_zip_code) {
						$updateParams['user_zip_code']	= $inp_zip_code;
						$updateFormat[]				='%s';
						$updateContent				.= "zip_code updated to $inp_zip_code<br />";
						$updateLog					.= " / zip_code updated to $inp_zip_code";
						$significantChange			= TRUE;
						$changeZip					= TRUE;
					}
					if ($inp_country_code != $user_country_code) {
						$updateParams['user_country_code']	= $inp_country_code;
						$updateFormat[]					= '%s';
						$updateContent					.= "country_code updated to $inp_country_code<br />";
						$updateLog						.= " / country_code updated to $inp_country_code";
						$significantChange				= TRUE;
						$changeCountry					= TRUE;
					}
					if ($inp_country != $user_country) {
						$updateParams['user_country']	= $inp_country;
						$updateFormat[]					= '%s';
						$updateContent					.= "country updated to $inp_country<br />";
						$updateLog						.= " / country updated to $inp_country";
					}
					if ($inp_whatsapp != $user_whatsapp) {
						$updateParams['user_whatsapp']	= $inp_whatsapp;
						$updateFormat[]				= '%s';
						$updateContent				.= "whatsapp updated to $inp_whatsapp<br />";
						$updateLog					.= " / whatsapp updated to $inp_whatsapp";
					}
					if ($inp_telegram != $user_telegram) {
						$updateParams['user__telegram']	= $inp_telegram;
						$updateFormat[]				= '%s';
						$updateContent				.= "telegram updated to $inp_telegram<br />";
						$updateLog					.= " / telegram updated to $inp_telegram";
					}
					if ($inp_signal != $user_signal) {
						$updateParams['user_signal']	= $inp_signal;
						$updateFormat[]			= '%s';
						$updateContent			.= "signal updated to $inp_signal<br />";
						$updateLog				.= " / signal updated to $inp_signal";
					}				
					if ($inp_messenger != $user_messenger) {
						$updateParams['user_messenger']	= $inp_messenger;
						$updateFormat[]				= '%s';
						$updateContent				.= "messenger updated to $inp_messenger<br />";
						$updateLog					.= " / messenger updated to $inp_messenger";
					}
					if ($inp_languages != $user_languages) {
						$updateParams['user_languages']	= $inp_languages;
						$updateFormat[]				= '%s';
						$updateContent				.= "languages updated to $inp_languages<br />";
						$updateLog					.= " / languages updated to $inp_languages";
					}
					
					
					if ($userRole == 'administrator') {				
						if ($inp_survey_score != $user_survey_score) {
							$updateParams['user_survey_score']	= $inp_survey_score;
							$updateFormat[]				= '%d';
							$updateContent				.= "survey_score updated to $inp_survey_score<br />";
							$updateLog					.= " / survey_score updated to $inp_survey_score";
						}
						if ($inp_timezone_id != $user_timezone_id) {
							$updateParams['user_timezone_id']	= $inp_timezone_id;
							$updateFormat[]				= '%s';
							$updateContent				.= "timezone_id updated to $inp_timezone_id<br />";
							$updateLog					.= " / timezone_id updated to $inp_timezone_id";
						}
						if ($inp_is_admin != $user_is_admin) {
							$updateParams['user_is_admin']	= $inp_is_admin;
							$updateFormat[]				= '%s';
							$updateContent				.= "is_admin updated to $inp_is_admin<br />";
							$updateLog					.= " / is_admin updated to $inp_is_admin";
						}
						if ($inp_role != $user_role) {
							$updateParams['user_role']	= $inp_role;
							$updateFormat[]				= '%s';
							$updateContent				.= "role updated to $inp_role<br />";
							$updateLog					.= " / role updated to $inp_role";
						}
						if ($inp_prev_callsign != $user_prev_callsign) {
							$updateParams['user_prev_callsign']	= $inp_prev_callsign;
							$updateFormat[]				= '%s';
							$updateContent				.= "prev_callsign updated to $inp_prev_callsign<br />";
							$updateLog					.= " / prev_callsign updated to $inp_prev_callsign";
						}
						if ($inp_action_log != $user_action_log) {
							$updateContent				.= "action_log updated to $inp_action_log<br />";
							$updateParams['user_action_log']	= $inp_action_log;
							$updateFormat[]				= '%s';
							$user_action_log			= $inp_action_log;
						}
						
					} else {
						if ($doDebug) {
							echo "skipping survey_score, timezone_id, role, prev_callsign, and action_log as user is not an admin<br />"; 
						}
					}
					if ($user_timezone_id == 'XX') {
						$significantChange	= TRUE;
						if ($doDebug) {
							echo "user_timezone_id is XX. Setting significantChange to TRUE<br />";
						}
					}
					if ($user_timezone_id == '') {
						$significantChange	= TRUE;
						if ($doDebug) {
							echo "user_timezone_id is empty. Setting significantChange to TRUE<br />";
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
						} else {		/// treat any of the changes as significant
							$doSignificantChange		= TRUE;
						}
						if ($doSignificantChange) {
							// figure out the timezone_id
							if ($doDebug) {
								echo "figuring out the timezone_id. Country_code: $inp_country_code<br />";
							}
							// if the country code is US get the timezone info from the zipcode
							if ($inp_country_code == 'US') {
								if ($doDebug) {
									echo "have a country code of US, verifying the zip code<br />";
								}
								$zipResult		= getOffsetFromZipCode($inp_zip_code,'',TRUE,$testMode,$doDebug);
								if ($zipResult[0] == 'NOK') {
									$inp_timezone_id		="XX";
									if ($doDebug) {
										echo "zip_code of $inp_zip_code is possibly invalid. Could not get a timezone_id<br />";
									}
									sendErrorEmail("$jobname US Country Code: zip_code of $inp_zip_code is possibly invalid. Could not get a timezone_id");
								} else {
									$inp_timezone_id		= $zipResult[1];
									if ($doDebug) {
										echo "using zipcode $inp_zip_code, have timezone_id of $inp_timezone_id<br />";
									}
								}
								if ($doDebug) {
									echo "done with US country code. Have determined inp_timezone_id to be $inp_timezone_id<br />";
								}
							} else {		// country code not US. Figure out the timezone and offset
								if ($doDebug) {
									echo "dealing with a non-us country of $inp_country_code<br />";
								}
								$timezone_identifiers 		= DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $inp_country_code );
								$myInt						= count($timezone_identifiers);
								if ($doDebug) {
									echo "found $myInt identifiers for country code $inp_country_code";
								}
								
								if ($myInt == 1) {									//  only 1 found. Use that and continue
									$inp_timezone_id		= $timezone_identifiers[0];
									if ($doDebug) {
										echo "for country code of $inp_country_code only one timezone id: $inp_timezone_id<br />";
									}
									$updateParams['user_timezone_id']	= $inp_timezone_id;
									$updateFormat[]			= '%s';
									$strPass				= "4B";
								} else {
									$timezoneSelector			= "<table>";
									$ii							= 1;
									if ($doDebug) {
										echo "Multiple timezones for $inp_country_code. have the list of identifiers<br />";
									}
									foreach ($timezone_identifiers as $thisID) {
										if ($doDebug) {
											echo "Processing $thisID<br />";
										}
										$dateTimeZoneLocal 	= new DateTimeZone($thisID);
										$dateTimeLocal 		= new DateTime("now",$dateTimeZoneLocal);
										$localDateTime 		= $dateTimeLocal->format('h:i A');
										$myInt				= strpos($thisID,"/");
										$myCity				= substr($thisID,$myInt+1);
										switch($ii) {
											case 1:
												$timezoneSelector	.= "<tr><td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='$thisID'>$myCity<br />$localDateTime</td>";
												$ii++;
												break;
											case 2:
												$timezoneSelector	.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='$thisID'>$myCity<br />$localDateTime</td>";
												$ii++;
												break;
											case 3:
												$timezoneSelector	.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='$thisID'>$myCity<br />$localDateTime</td></tr>";
												$ii					= 1;
												break;
										}
									}
									if ($ii == 2) {			// need two blank cells
										$timezoneSelector			.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='None'>None</td><td>&nbsp;</td></tr>";
									} elseif ($ii == 3) {	// need one blank cell
										$timezoneSelector			.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='None'>None</td></tr>";
									} else {				// need new row
										$timezoneSelector			.= "<td><input type='radio' class='formInputButton' id='chk_timezone_id' name='inp_timezone_id' value='None'>None</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									}
									if ($doDebug) {
										echo "Putting the form together<br />";
									}
									$theseUpdateParams	= json_encode($updateParams);
									$content		.= "<h3>Select Time Zone City</h3>
														<p>Please select the city that best represents the timezone you will be in during the class. 
														The current local time is displayed underneath the city.</p>
														<form method='post' action='$theURL' 
														name='tzselection' ENCTYPE='multipart/form-data'>
															<input type='hidden' name='strpass' value='4A'>
															<input type='hidden' name='inp_mode' value='$inp_mode'>
															<input type='hidden' name='inp_verbose' value='$inp_verbose'>
															<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
															<input type='hidden' name='theseUpdateParams' value='$theseUpdateParams'>
															<input type='hidden' name='doSignificantChange' value='$doSignificantChange'>
															<input type='hidden' name='updateContent' value='$updateContent'>
															<input type='hidden' name='updateLog' value='$updateLog'>
														$timezoneSelector
														<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
														</table></form>
														<p>NOTE: If the program is asking you this question and you live in the United States, then you selected 
														the wrong country on the previous page. Start over with the sign up program from the 
														beginning.</p>";
									
									
								}
							}
						}				
					}
				}
			} else {
				if($doDebug) {
					echo "no record found in $userMasterTableName for $inp_call_sign<br />";
				}
				$content		.= "<p>No record found in $userMasterTableName for $inp_call_sign</p>";
			}
		}
	}
	if ($strPass == "4A") {
		if ($doDebug) {
			echo "<br />Pass 4A<br />decoding updateParams<br />";
		}
		$updateParams	= json_decode(stripslashes($theseUpdateParams),TRUE);
//		$updateParams['user_timezone_id']  = $inp_timezone_id;
//		$updateFormat[]					= '%s';
		$strPass	= "4B";
	}
	
	if ($strPass == "4B") {
		if ($doDebug) {
			echo "<br />at pass 4B<br />
				  <br />updateParams:<br /><pre>";
			print_r($updateParams);
			echo "</pre><br />
				  updateContent: $updateContent<br >
				  updateLog: $updateLog<br />";
		}
		
		// get the data once again
		$sql			= "select * from $userMasterTableName 
							where user_call_sign = '$inp_call_sign'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_call_sign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_ph_code			= $sqlRow->user_ph_code;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
 					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_country			= $sqlRow->user_country;
					$user_whatsapp			= $sqlRow->user_whatsapp;
					$user_telegram			= $sqlRow->user_telegram;
					$user_signal			= $sqlRow->user_signal;
					$user_messenger			= $sqlRow->user_messenger;
					$user_action_log		= $sqlRow->user_action_log;
					$user_timezone_id		= $sqlRow->user_timezone_id;
					$user_languages			= $sqlRow->user_languages;
					$user_survey_score		= $sqlRow->user_survey_score;
					$user_is_admin			= $sqlRow->user_is_admin;
					$user_role				= $sqlRow->user_role;
					$user_prev_callsign		= $sqlRow->user_prev_callsign;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;
	

					// see if the timezone_id has changed
				
					if ($doDebug) {
						echo "checking to see if the timezone_id has changed<br >";
					}
					if (isset($inp_timezone_id) && $inp_timezone_id != '') {
						if ($doDebug) {
							echo "inp_timezone_id is set to $inp_timezone_id<br >
								  current timezone_id: $user_timezone_id<br />";
						}
						if ($inp_timezone_id != $user_timezone_id) {
							if ($doDebug) {
								echo "updating timezone_id to $inp_timezone_id<br >";
							}
							$updateParams['user_timezone_id']	= $inp_timezone_id;
							$updateFormat[]					= '%s';
							$updateContent					.= "timezone_id updated to $inp_timezone_id<br />";
							$updateLog						.= " / timezone_id updated to $inp_timezone_id";
							
							if ($user_role == 'student') {
								if ($doDebug) {
									echo "timezone has changed. Updating current and future student records<br />";
								}					
								// now update the offset in any current or future student records
								$studentSQL		= "select * from $studentTableName 
													where student_call_sign = '$inp_call_sign' and 
													(student_semester = '$currentSemester' or 
													student_semester = '$nextSemester' or 
													student_semester = '$semesterTwo' or
													student_semester = '$semesterThree' or 
													student_semester = '$semesterFour')";
								$studentResult	= $wpdb->get_results($studentSQL);
								if ($studentResult === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$numSRows	= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $studentSQL<br />and retrieved $numSRows rows<br />";
									}
									if ($numSRows > 0) {		// have a record to update
										foreach($studentResult as $studentRow) {
											$student_ID								= $studentRow->student_id;
											$student_call_sign						= strtoupper($studentRow->student_call_sign);
											$student_time_zone  					= $studentRow->student_time_zone;
											$student_timezone_offset				= $studentRow->student_timezone_offset;
											$student_semester						= $studentRow->student_semester;
											$student_action_log  					= $studentRow->student_action_log;
				
											$myArray				= explode(" ",$student_semester);
											$thisYear				= $myArray[0];
											$thisMonDay				= $myArray[1];
											$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
											$myMonDay				= $myConvertArray[$thisMonDay];
											$thisNewDate			= "$thisYear$myMonDay 00:00:00";
											if ($doDebug) {
												echo "converted $student_semester to $thisNewDate<br />";
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
											$studentUpdate			= $wpdb->update($studentTableName, 
																				$thisUpdateParams,
																				array('student_id'=>$student_ID),
																				array('%s','%f','%s'),
																				array('%d'));
											if ($studentUpdate === FALSE) {
												handleWPDBError($jobname,$doDebug);
											} else {
												if ($doDebug) {
													echo "updated timezone to $inp_timezone_id and offset to $inp_timezone_offset for $inp_call_sign<br >";
												}
											}
										}
									}
								}
							} elseif ($user_role == 'advisor') {
								if ($doDebug) {
									echo "timezone has changed. updating offset in current and future advisorClass records<br />";
								}
								$sql 		= "select * from $advisorClassTableName 
												where advisorclass_call_sign = '$inp_call_sign' 
													and (advisorclass_semester = '$currentSemester' or 
													advisorclass_semester = '$nextSemester' or 
													advisorclass_semester = '$semesterTwo' or
													advisorclass_semester = '$semesterThree' or 
													advisorclass_semester = '$semesterFour')";
								$advisorClassResult	= $wpdb->get_results($sql);
								if ($advisorClassResult === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$numACRows		= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $sql<br />and retrieved $numACRows rows<br />";
									}
									if ($numACRows > 0) {
										foreach($advisorClassResult as $advisorClassRow) {
											$advisorClass_id		= $advisorClassRow->advisorclass_id;
											$advisorClass_semester	= $advisorClassRow->advisorclass_semester;
											$timezone_offset		= $advisorClassRow->advisorclass_timezone_offset;
											$advisorClassActionLog	= $advisorClassRow->advisorclass_action_log;
											
											if ($doDebug) {
												echo "have advisorClass_id of $advisorClass_id to update<br />";
											}
											$myArray				= explode(" ",$advisorClass_semester);
											$thisYear				= $myArray[0];
											$thisMonDay				= $myArray[1];
											$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
											$myMonDay				= $myConvertArray[$thisMonDay];
											$thisNewDate			= "$thisYear$myMonDay 00:00:00";
											if ($doDebug) {
												echo "converted $advisorClass_semester to $thisNewDate<br />";
											}
											$dateTimeZoneLocal 		= new DateTimeZone($inp_timezone_id);
											$dateTimeZoneUTC 		= new DateTimeZone("UTC");
											$dateTimeLocal 			= new DateTime($thisNewDate,$dateTimeZoneLocal);
											$dateTimeUTC			= new DateTime($thisNewDate,$dateTimeZoneUTC);
											$php2 					= $dateTimeZoneLocal->getOffset($dateTimeUTC);
											$inp_timezone_offset 	= $php2/3600;

											$thisDate				= date('dMy H:i');

											$advisorClassActionLog	.= "/ $thisDate $userName $jobname: updated timezone offset to $inp_timezone_offset ";
											$ACUpdateParams			= array('advisorclass_timezone_offset'=>$inp_timezone_offset, 
																			'advisorclass_action_log'=>$advisorClassActionLog);
											$ACUpdateFormat			= array('%f','%s');
											$ACUpdateResult			= $wpdb->update($advisorClassTableName,
																					$ACUpdateParams,
																					array('advisorclass_id'=>$advisorClass_id),
																					$ACUpdateFormat,
																					array('%d'));
											if ($ACUpdateResult === FALSE) {
												handleWPDBError($jobname,$doDebug);
											} else {
												if ($doDebug) {
													echo "$advisorClass_id id was updated<br />";
												}
											}
										}
									}
								}
							}
						}
					}
		
				
					if (array_key_exists('advisor_action_log',$updateParams)) {
						$user_action_log	= $updateParams['advisor_action_log'];
					}
			
					$doProceed			= TRUE;
					// if there are any updates, do the update
					if (count($updateParams) == 0) {
						$content		.= "<p>No updates requested</p>";
						if ($doDebug) {
							echo "no updates requested<br />";
						}
					} else {
						$user_action_log					.= " / $actionLogDate $userName $jobname $updateLog";
						$updateParams['user_action_log']	= $user_action_log;
						$updateFormat[]						= '%s';
						$userMasterData			= array('tableName'=>$userMasterTableName,
														'inp_method'=>'update',
														'inp_data'=>$updateParams,
														'inp_format'=>$updateFormat,
														'jobname'=>$jobname,
														'inp_id'=>$user_id,
														'inp_callsign'=>$user_call_sign,
														'inp_who'=>$userName,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug);
						$updateResult	= update_user_master($userMasterData);
						if ($updateResult[0] === FALSE) {
							$content			.= "Updating the user_master record for $user_call_sign failed<br />";
							handleWPDBError($jobname,$doDebug);
							$doProceed				= FALSE;
						} else {
							if ($doDebug) {
								echo "updating the user master information for $user_call_sign was successful<br />";
							}
						}
					}
		
					if ($doProceed) {
						////// get user_master
						$sql			= "select * from $userMasterTableName 
											where user_call_sign = '$inp_call_sign'";
						$sqlResult		= $wpdb->get_results($sql);
						if ($sqlResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numRows	= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $sql<br />and retrieved $numRows rows<br />";
							}
							if ($numRows > 0) {
								foreach($sqlResult as $sqlRow) {
									$user_id				= $sqlRow->user_ID;
									$user_call_sign			= $sqlRow->user_call_sign;
									$user_first_name		= $sqlRow->user_first_name;
									$user_last_name			= $sqlRow->user_last_name;
									$user_email				= $sqlRow->user_email;
									$user_ph_code			= $sqlRow->user_ph_code;
									$user_phone				= $sqlRow->user_phone;
									$user_city				= $sqlRow->user_city;
									$user_state				= $sqlRow->user_state;
									$user_zip_code			= $sqlRow->user_zip_code;
									$user_country_code		= $sqlRow->user_country_code;
									$user_country			= $sqlRow->user_country;
									$user_whatsapp			= $sqlRow->user_whatsapp;
									$user_telegram			= $sqlRow->user_telegram;
									$user_signal			= $sqlRow->user_signal;
									$user_messenger			= $sqlRow->user_messenger;
									$user_action_log		= $sqlRow->user_action_log;
									$user_timezone_id		= $sqlRow->user_timezone_id;
									$user_languages			= $sqlRow->user_languages;
									$user_survey_score		= $sqlRow->user_survey_score;
									$user_is_admin			= $sqlRow->user_is_admin;
									$user_role				= $sqlRow->user_role;
									$user_prev_callsign		= $sqlRow->user_prev_callsign;
									$user_date_created		= $sqlRow->user_date_created;
									$user_date_updated		= $sqlRow->user_date_updated;
					
								
									//// display user_master
									$myStr			= formatActionLog($user_action_log);
									$content		.= "<h4>User Master Data</h4>
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
									if ($userRole == 'administrator') {
										$content .= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
														<td><b>Is Admin</b><br />$user_is_admin</td>
														<td><b>Role</b><br />$user_role</td>
														<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
													<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>";
									}
									$content	.= "</table>
													<p>Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$user_call_sign&inp_depth=one$doDebug=$doDebug&testMode=$testMode' 
													target='_blank'>HERE</a> to update the advisor Master Data</p>";
									if ($userRole == 'administrator') {
										$content	.= "<p>To List User Master Change History for $user_call_sign click 
														<a href='$siteURL/cwa-list-user-master-callsign-history/?strpass=2&inp_callsign=$user_call_sign' 
														target='_blank'>HERE</a></p>
														<p>To show a different callsign, click <a href='$theURL'>HERE</a></p>";
									}
								}
							} else {
								if ($doDebug) {
									echo "No record found in $userMasterTableName for $inp_call_sign<br />";
								}
								$content			.= "<p>No record found in $userMasterTableName for $inp_call_sign</p>";
							}
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
	} 
	if ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 5 to delete $inp_call_sign at $inp_id id record<br />";
		}
		$doProceed		= TRUE;
		$content		.= "<h3>$jobname for $inp_call_sign</h3>
							<h4>Deleting the User Master Record</h4>";

		// get the user_master record
		$sql			= "select * from $userMasterTableName 
							where user_id = '$inp_id'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$user_id				= $sqlRow->user_ID;
					$user_call_sign			= $sqlRow->user_call_sign;
					$user_first_name		= $sqlRow->user_first_name;
					$user_last_name			= $sqlRow->user_last_name;
					$user_email				= $sqlRow->user_email;
					$user_ph_code			= $sqlRow->user_ph_code;
					$user_phone				= $sqlRow->user_phone;
					$user_city				= $sqlRow->user_city;
					$user_state				= $sqlRow->user_state;
 					$user_zip_code			= $sqlRow->user_zip_code;
					$user_country_code		= $sqlRow->user_country_code;
					$user_country			= $sqlRow->user_country;
					$user_whatsapp			= $sqlRow->user_whatsapp;
					$user_telegram			= $sqlRow->user_telegram;
					$user_signal			= $sqlRow->user_signal;
					$user_messenger			= $sqlRow->user_messenger;
					$user_action_log		= $sqlRow->user_action_log;
					$user_timezone_id		= $sqlRow->user_timezone_id;
					$user_languages			= $sqlRow->user_languages;
					$user_survey_score		= $sqlRow->user_survey_score;
					$user_is_admin			= $sqlRow->user_is_admin;
					$user_role				= $sqlRow->user_role;
					$user_prev_callsign		= $sqlRow->user_prev_callsign;
					$user_date_created		= $sqlRow->user_date_created;
					$user_date_updated		= $sqlRow->user_date_updated;

					if ($doDebug) {
						echo "we have a user_master record that can be deleted<br />";
					}
					$userMasterData			= array('tableName'=>$userMasterTableName,
													'inp_method'=>'delete',
													'inp_data'=>array(),
													'inp_format'=>array(),
													'jobname'=>$jobname,
													'inp_id'=>$user_id,
													'inp_callsign'=>$user_call_sign,
													'inp_who'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
					$updateResult	= update_user_master($userMasterData);
					if ($updateResult[0] === FALSE) {
						$content			.= "The attempt to delete user master record for $inp_call_sign at $inp_id idfailed<br />";
						handleWPDBError($jobname,$doDebug);
					} else {
						$content		.= "The user master record id $inp_id for $inp_call_sign has been deleted<br />";
					}
				}
			} else {
				if ($doDebug) {
					echo "No record found in $userMasterTableName for $inp_id<br />";
				}
				$content			.= "<p>No record found in $userMasterTableName for $inp_id</p>";
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
add_shortcode ('display_and_update_user_master', 'display_and_update_user_master_func');