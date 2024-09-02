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
			if ($str_key == "inp_callsign") {
				$inp_callsign = strtoupper($str_value);
				$inp_callsign = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_id") {
				$inp_id = strtoupper($str_value);
				$inp_id = filter_var($inp_id,FILTER_UNSAFE_RAW);
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
			if ($str_key == "updateContent") {
				$updateContent = $str_value;
				$updateContent = filter_var($updateContent,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "updateLog") {
				$updateLog = $str_value;
				$updateLog = filter_var($updateLog,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_date_created") {
				$inp_date_created = $str_value;
				$inp_date_created = filter_var($inp_date_created,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_date_updated") {
				$inp_date_updated = $str_value;
				$inp_date_updated = filter_var($inp_date_updated,FILTER_UNSAFE_RAW);
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
	} else {
		$extMode					= 'pd';
		$userMasterTableName		= "wpw1_cwa_user_master";
		$studentTableName			= "wpw1_cwa_student";
	}

	if ($strPass == "1") {
		if ($userRole == 'administrator') {
			$strPass					= "1";
		} else {
			$inp_callsign				= strtoupper($userName);
			$strPass					= "2";
		}
	}


	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>User Callsign</td>
								<td><input type='text' class='formInputText' name='inp_callsign' size='15' maxlength='20' autofocus'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2 with inp_callsign: $inp_callsign<br />";
		}	
	
		$content			.= "<h3>Display and Update $inp_callsign User Master Information</h3>";
		if ($userRole == 'administrator') {
			$content		.= "<p><a href='$theURL'>Look Up a Different User</a>";
		} else {
			$content		.= "<p>This is your personal information. It is stored in the 
								User Master table and used throughout the CW Academy 
								system wherever your personal information is needed.</p>";
		}
		// get the user_master info and format it
		if ($doDebug) {
			echo "getting the user_master data<br />";
		}
		$result			= "";
		$reason			= "";
		$id				= "";
		$callsign		= "";
		$first_name		= "";
		$last_name		= "";
		$email			= "";
		$phone			= "";
		$ph_code		= "";
		$city			= "";
		$state			= "";
		$zip_code		= "";
		$country_code	= "";
		$country		= "";
		$whatsapp		= "";
		$telegram		= "";
		$signal			= "";
		$messenger		= "";
		$user_action_log	= "";
		$languages		= "";
		$timezone_id	= "";
		$date_created	= "";
		$date_updated	= "";

		$dataArray			= array('callsign'=>$inp_callsign,
									'action'=>'get',
									'debugging'=> $doDebug,
									'testing'=> $testMode);
		$dataResult				= user_master_data($dataArray);
		foreach($dataResult as $thisField => $thisValue) {
			$$thisField		= $thisValue;
		}		
		if ($result == FALSE) {
			$content		.= "Failed to get the student master information. Reason: $reason";			
		} else {
			$myStr			= formatActionLog($user_action_log);
			$updateLink		= "<a href='$theURL/?strpass=3&inp_id=$id'>$id<a/>";
			$content		.= "<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='3'>
								<input type='hidden' name='inp_id' value='$id'>
								<input type='hidden' name='inp_callsign' value='$inp_callsign'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<h4>User Master Data</h4>
								<table style='width:900px;'>";
			if ($userRole == 'administrator') {
				$content	.= "<tr><td colspan='4'><b>ID</b><br />$updateLink</td></tr>";
			}
			$content		.= "<tr><td><b>Callsign<br />$callsign</b></td>
									<td><b>Name</b><br />$last_name, $first_name</td>
									<td><b>Phone</b><br />+$ph_code $phone</td>
									<td><b>Email</b><br />$email</td></tr>
								<tr><td><b>City</b><br />$city</td>
									<td><b>State</b><br />$state</td>
									<td><b>Zip Code</b><br />$zip_code</td>
									<td><b>Country</b><br />$country</td></tr>
								<tr><td><b>WhatsApp</b><br />$whatsapp</td>
									<td><b>Telegram</b><br />$telegram</td>
									<td><b>Signal</b><br />$signal</td>
									<td><b>Messenger</b><br />$messenger</td></tr>
								<tr><td><b>Languages</b><br />$languages</td>";
			if ($userRole == 'administrator') {
				$content	.= "<td><b>Timezone ID</b><br />$timezone_id</td>
									<td ><b>Date Created</b><br />$date_created</td>
									<td><b>Date Updated</b><br />$date_updated</td></tr>
								<tr><td colspan='4' style='vertical-align:top;'><b>Action Log</b><br />$myStr</td></tr>";
			} else {
				 $content	.= "<td colspan='3'></td></tr>";
			}
			$content		.= "<tr><td colspan='4'><hr></td></tr>
								<tr><td colspan='4'><input type='submit' class='formInputButton' name='submit' value='Update This Information' /></td></tr>
								</table></form>";
		}
		
		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "arrived at pass3 with inp_callsign: $inp_callsign< r />";
		}
		$content		.= "<h3>Display and Update $inp_callsign User Master Information</h3>";
		
		// get the record and display it for update
		$result			= "";
		$reason			= "";
		$id				= "";
		$callsign		= "";
		$first_name		= "";
		$last_name		= "";
		$email			= "";
		$phone			= "";
		$ph_code		= "";
		$city			= "";
		$state			= "";
		$zip_code		= "";
		$country_code	= "";
		$country		= "";
		$whatsapp		= "";
		$telegram		= "";
		$signal			= "";
		$messenger		= "";
		$user_action_log		= "";
		$languages		= "";
		$timezone_id	= "";
		$date_created	= "";
		$date_updated	= "";

		$dataArray			= array('callsign'=>$inp_callsign,
									'action'=>'get',
									'debugging'=> $doDebug,
									'testing'=> $testMode);
		$dataResult				= user_master_data($dataArray);
		if ($doDebug) {
			echo "dataResult:<br /><pre>";
			print_r($dataResult);
			echo "</pre><br />";
		}
		foreach($dataResult as $thisField => $thisValue) {
			$$thisField		= $thisValue;
		}		
		if ($result == FALSE) {
			$content		.= "Failed to get the student master information. Reason: $reason";			
		} else {
		
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
						
						if ($country_code == $thisCountryCode) {
							$option1		= "<option value='$thisCountryCode|$thisCountryName|$thisPhCode' selected>$thisCountryName</option>\n";
						}
						
						$countryOptionList	.= "<option value='$thisCountryCode|$thisCountryName|$thisPhCode'>$thisCountryName</option>\n";
						
					}
				}
			}

			
			$content	.= "
			<form method='post' action='$theURL' 
			name='deletion_form' ENCTYPE='multipart/form-data'>
			<input type='hidden' name='strpass' value='5'>
			<input type='hidden' name='inp_callsign' value='$inp_callsign'>
			<input class='formInputButton' type='submit' 
			onclick=\"return confirm('Are you sure?');\"  
			value='Delete This Record' />
			</form><br /><br />
			<form method='post' action='$theURL' 
			name='selection_form' ENCTYPE='multipart/form-data'>
			<input type='hidden' name='strpass' value='4'>
			<input type='hidden' name='inp_callsign' value='$inp_callsign'>
			<input type='hidden' name='inp_mode' value='$inp_mode'>
			<input type='hidden' name='inp_verbose' value='$inp_verbose'>
			<table style='width:900px;'>
			<tr><td>ID</td>
				<td>$id</td></tr>
			<tr><td>callsign</td>
				<td>$callsign</td></tr>
			<tr><td>first_name</td>
				<td><input type='text' class='formInputText' name='inp_first_name' length='30' 
				maxlength='30' value='$first_name'></td></tr>
			<tr><td>last_name</td>
				<td><input type='text' class='formInputText' name='inp_last_name' length='50' 
				maxlength='50' value='$last_name'></td></tr>
			<tr><td>email</td>
				<td><input type='text' class='formInputText' name='inp_email' length='50' 
				maxlength='50' value='$email'></td></tr>
			<tr><td>phone</td>
				<td><input type='text' class='formInputText' name='inp_phone' length='20' 
				maxlength='20' value='$phone'></td></tr>
			<tr><td>city</td>
				<td><input type='text' class='formInputText' name='inp_city' length='30' 
				maxlength='30' value='$city'></td></tr>
			<tr><td>state</td>
				<td><input type='text' class='formInputText' name='inp_state' length='30' 
				maxlength='30' value='$state'></td></tr>
			<tr><td>zip_code</td>
				<td><input type='text' class='formInputText' name='inp_zip_code' length='20' 
				maxlength='20' value='$zip_code'></td></tr>
			<tr><td>country</td>
				<td><select name='inp_country_data' class='formSelect' size='5'>
					$option1
					$countryOptionList
					</select></td></tr>
			<tr><td>whatsapp</td>
				<td><input type='text' class='formInputText' name='inp_whatsapp' length='20' 
				maxlength='20' value='$whatsapp'></td></tr>
			<tr><td>telegram</td>
				<td><input type='text' class='formInputText' name='inp_telegram' length='20' 
				maxlength='20' value='$telegram'></td></tr>
			<tr><td>signal</td>
				<td><input type='text' class='formInputText' name='inp_signal' length='20' 
				maxlength='20' value='$signal'></td></tr>
			<tr><td>messenger</td>
				<td><input type='text' class='formInputText' name='inp_messenger' length='20' 
				maxlength='20' value='$messenger'></td></tr>
			<tr><td>Languages</td>
				<td><input type='text' class='formInputText' name='inp_languages' length='50' 
				maxlength='150' value='$languages'></td></tr>";
		if ($userRole == 'administrator') {
			$content	.= "<tr><td style='vertical-align:top;'>action_log</td>
								<td><textarea class='formInputText' name='inp_action_log' rows='5' cols='50'>$user_action_log</textarea></td></tr>
							<tr><td>date_created</td>
								<td>$date_created</td></tr>
							<tr><td>date_updated</td>
								<td>$date_updated</td></tr>";
 		}
		$content	.= "<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit Updates' /></td></tr>
			</table></form>";
		}
		
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 4 with inp_callsign: $inp_callsign<br >";
		}
		
		$strPass			= "4B";
		
		$doProceed			= TRUE;
		$content			.= "<h3>Display and Update User $inp_callsign Master Information</h3>
								<h4>Update Results</h4>";

		// get the user_master data
		if ($doDebug) {
			echo "getting the user_master data<br />";
		}
		$result			= "";
		$reason			= "";
		$id				= "";
		$callsign		= "";
		$first_name		= "";
		$last_name		= "";
		$email			= "";
		$phone			= "";
		$ph_code		= "";
		$city			= "";
		$state			= "";
		$zip_code		= "";
		$country_code	= "";
		$country		= "";
		$whatsapp		= "";
		$telegram		= "";
		$signal			= "";
		$messenger		= "";
		$user_action_log	= "";
		$languages		= "";
		$timezone_id	= "";
		$date_created	= "";
		$date_updated	= "";

		$dataArray			= array('callsign'=>$inp_callsign,
									'action'=>'get',
									'debugging'=> $doDebug,
									'testing'=> $testMode);
		$dataResult				= user_master_data($dataArray);
		foreach($dataResult as $thisField => $thisValue) {
			$$thisField		= $thisValue;
		}		
		if ($result == FALSE) {
			$content		.= "Failed to get the student master information. Reason: $reason";	
			$doProceed		= FALSE;
		} else {

			$thisDate = date('Y-m-d H:i:s');
			$updateParams	= array('callsign'=>$inp_callsign,
									'action'=>'update',
									'debugging'=> $doDebug,
									'testing'=> $testMode);
			$updateLog	= " /$thisDate $userName performed the following updates:";
			$updateContent					= "";
			$significantChange				= FALSE;
			$changeZip						= FALSE;
			$changeCity						= FALSE;
			$changeCountry					= FALSE;
			if ($inp_first_name != $first_name) {
				$updateParams['first_name']	= $inp_first_name;
				$updateContent				.= "first_name updated to $inp_first_name<br />";
				$updateLog					.= " / first_name updated to $inp_first_name";
			}
			if ($inp_last_name != $last_name) {
				$updateParams['last_name']	= $inp_last_name;
				$updateContent				.= "last_name updated to $inp_last_name<br />";
				$updateLog					.= " / last_name updated to $inp_last_name";
			}
			if ($inp_email != $email) {
				$updateParams['email']	= $inp_email;
				$updateContent			.= "email updated to $inp_email<br />";
				$updateLog				.= " / email updated to $inp_email";
			}
			if ($inp_phone != $phone) {
				$updateParams['phone']	= $inp_phone;
				$updateContent			.= "phone updated to $inp_phone<br />";
				$updateLog				.= " / phone updated to $inp_phone";
			}
			if ($inp_city != $city) {
				$updateParams['city']	= $inp_city;
				$updateContent			.= "city updated to $inp_city<br />";
				$updateLog				.= " / city updated to $inp_city";
				$signifcantChange		= TRUE;
				$changeCity				= TRUE;
			}
			if ($inp_state != $state) {
				$updateParams['state']	= $inp_state;
				$updateContent			.= "state updated to $inp_state<br />";
				$updateLog				.= " / state updated to $inp_state";
			}
			if ($inp_zip_code != $zip_code) {
				$updateParams['zip_code']	= $inp_zip_code;
				$updateContent				.= "zip_code updated to $inp_zip_code<br />";
				$updateLog					.= " / zip_code updated to $inp_zip_code";
				$significantChange			= TRUE;
				$changeZip					= TRUE;
			}
			if ($inp_country_code != $country_code) {
				$updateParams['country_code']	= $inp_country_code;
				$updateContent					.= "country_code updated to $inp_country_code<br />";
				$updateLog						.= " / country_code updated to $inp_country_code";
				$significantChange				= TRUE;
				$changeCountry					= TRUE;
			}
			if ($inp_whatsapp != $whatsapp) {
				$updateParams['whatsapp']	= $inp_whatsapp;
				$updateContent				.= "whatsapp updated to $inp_whatsapp<br />";
				$updateLog					.= " / whatsapp updated to $inp_whatsapp";
			}
			if ($inp_telegram != $telegram) {
				$updateParams['telegram']	= $inp_telegram;
				$updateContent				.= "telegram updated to $inp_telegram<br />";
				$updateLog					.= " / telegram updated to $inp_telegram";
			}
			if ($inp_signal != $signal) {
				$updateParams['signal']	= $inp_signal;
				$updateContent			.= "signal updated to $inp_signal<br />";
				$updateLog				.= " / signal updated to $inp_signal";
			}				
			if ($inp_messenger != $messenger) {
				$updateParams['messenger']	= $inp_messenger;
				$updateContent				.= "messenger updated to $inp_messenger<br />";
				$updateLog					.= " / messenger updated to $inp_messenger";
			}
			if ($inp_languages != $languages) {
				$updateParams['languages']	= $inp_languages;
				$updateContent				.= "languages updated to $inp_languages<br />";
				$updateLog					.= " / languages updated to $inp_languages";
			}
			
			if ($timezone_id == 'XX') {
				$significantChange	= TRUE;
			}
			
			if ($userRole == 'administrator') {
				if ($inp_action_log != $user_action_log) {
					$updateContent		.= "action_log updated to $inp_action_log<br />";
					$updateParams['action_log']	= $inp_action_log;
					$user_action_log	= $inp_action_log;
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
						$zipResult		= getOffsetFromZipCode($inp_zip_code,'',TRUE,$testing,$doDebug);
						if ($zipResult[0] == 'NOK') {
							$inp_timezone_id		="XX";
							if ($doDebug) {
								echo "zip_code of $inp_zip_code is possibly invalid. Could not get a timezone_id<br />";
							}
							sendErrorEmail("$jobname US Country Code: zip_code of $inp_zip_code is possibly invalid. Could not get a timezone_id");
						} else {
							$inp_timezone_id		= $zipResult[1];
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
						} else {
							$strPass					= "X";
							$timezoneSelector			= "<table>";
							$ii							= 1;
							if ($doDebug) {
								echo "have the list of identifiers for $inp_country_code<br />";
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
													<input type='hidden' name='inp_callsign' value='$inp_callsign'>
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
//								$doProceed		= FALSE;
							
							
						}
					}
				}				
			}
		}
	}
	if ($strPass == "4A") {
		if ($doDebug) {
			echo "<br />Pass 4A<br />decoding updateParams<br />";
		}
		$updateParams	= json_decode(stripslashes($theseUpdateParams),TRUE);
//		$updateParams['timezone_id']  = $inp_timezone_id;
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
		$result			= "";
		$reason			= "";
		$id				= "";
		$callsign		= "";
		$first_name		= "";
		$last_name		= "";
		$email			= "";
		$phone			= "";
		$ph_code		= "";
		$city			= "";
		$state			= "";
		$zip_code		= "";
		$country_code	= "";
		$country		= "";
		$whatsapp		= "";
		$telegram		= "";
		$signal			= "";
		$messenger		= "";
		$user_action_log	= "";
		$languages		= "";
		$timezone_id	= "";
		$date_created	= "";
		$date_updated	= "";

		$dataArray			= array('callsign'=>$inp_callsign,
									'action'=>'get',
									'debugging'=> $doDebug,
									'testing'=> $testMode);
		$dataResult				= user_master_data($dataArray);
		foreach($dataResult as $thisField => $thisValue) {
			$$thisField		= $thisValue;
		}		
		if ($result == FALSE) {
			$content		.= "Failed to get the student master information. Reason: $reason";			
		} else {

			// see if the timezone_id has changed
		
			if ($doDebug) {
				echo "checking to see if the timezone_id has changed<br >";
			}
			if (isset($inp_timezone_id)) {
				if ($doDebug) {
					echo "inp_timezone_id is set to $inp_timezone_id<br >
						  current timezone_id: $timezone_id<br />";
				}
				if ($inp_timezone_id != $timezone_id) {
					if ($doDebug) {
						echo "updating timezone_id to $inp_timezone_id<br >";
					}
					$updateParams['timezone_id']	= $inp_timezone_id;
					
					// now update the offset in any current or future student records
					$studentSQL		= "select * from $studentTableName 
										where call_sign = '$inp_callsign' and 
										(semester = '$currentSemester' or 
										semester = '$nextSemester' or 
										semester = '$semesterTwo' or
										semester = '$semesterThree' or 
										semester = '$semesterFour')";
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
								$student_call_sign						= strtoupper($studentRow->call_sign);
								$student_time_zone  					= $studentRow->time_zone;
								$student_timezone_offset				= $studentRow->timezone_offset;
								$student_semester						= $studentRow->semester;
								$student_action_log  					= $studentRow->action_log;
	
								$myArray				= explode(" ",$student_semester);
								$thisYear				= $myArray[0];
								$thisMonDay				= $myArray[1];
								$myConvertArray			= array('Jan/Feb'=>'-01-01','May/Jun'=>'-05-01','Sep/Oct'=>'-09-01');
								$myMonDay				= $myConvertArray[$thisMonDay];
								$thisNewDate			= "$thisYear$myMonDay 00:00:00";
								if ($doDebug) {
									echo "converted $inp_semester to $thisNewDate<br />";
								}
								$dateTimeZoneLocal 		= new DateTimeZone($inp_timezone_id);
								$dateTimeZoneUTC 		= new DateTimeZone("UTC");
								$dateTimeLocal 			= new DateTime($thisNewDate,$dateTimeZoneLocal);
								$dateTimeUTC			= new DateTime($thisNewDate,$dateTimeZoneUTC);
								$php2 					= $dateTimeZoneLocal->getOffset($dateTimeUTC);
								$inp_timezone_offset 	= $php2/3600;
								
								$thisDate				= date('Y-m-d H:i:s');
								$student_action_log		.= "/ $thisDate $userName $jobname: updated timezone to $inp_timezone_id and offset to $inp_timezone_offset ";
								
								$thisUpdateParams		= array('timezone'=>$inp_timezone_id,
																'timezone_offset'=>$inp_timezone_offset, 
																'action_log'=>$student_action_log);
								$studentUpdate			= $wpdb->update($studentTableName, 
																	$thisUpdateParams,
																	array('student_id'=>$student_ID),
																	array('%s','%f','%s'),
																	array('%d'));
								if ($studentUpdate === FALSE) {
									handleWPDBError($jobnamd,$doDebug);
								} else {
									if ($doDebug) {
										echo "updated timezone to $inp_timezone_id and offset to $inp_timezone_offset for $inp_callsign<br >";
									}
								}
							}
						}
					}
				}
			}
		}
		
		if (array_key_exists('action_log',$updateParams)) {
			$user_action_log	= $updateParams['action_log'];
		}

//		if ($doProceed) {
			// if there are any updates, do the update
			if (count($updateParams) < 5) {
				$content		.= "<p>No updates requested</p>";
				if ($doDebug) {
					echo "no updates requested<br />";
				}
			} else {
				$user_action_log		.= $updateLog;
				$updateParams['user_action_log']	= $user_action_log;
				
				$result			= "";
				$reason			= "";
		
				$dataResult				= user_master_data($updateParams);
				foreach($dataResult as $thisField => $thisValue) {
					$$thisField		= $thisValue;
				}		
				if ($result == FALSE) {
					$content		.= "Failed to update the user master information. Reason: $reason";	
					if ($doDebug) {
						echo "Failed to update the user master information. Reason: $reason<br />";
					}		
					$doProceed		= FALSE;
				} else {
					if ($doDebug) {
						echo "updating the user master information for $callsign was successful<br />";
					}
				}
			}

//			if ($doProceed) {
				$result			= "";
				$reason			= "";
				$id				= "";
				$callsign		= "";
				$first_name		= "";
				$last_name		= "";
				$email			= "";
				$phone			= "";
				$ph_code		= "";
				$city			= "";
				$state			= "";
				$zip_code		= "";
				$country_code	= "";
				$country		= "";
				$whatsapp		= "";
				$telegram		= "";
				$signal			= "";
				$messenger		= "";
				$user_action_log	= "";
				$languages		= "";
				$timezone_id	= "";
				$date_created	= "";
				$date_updated	= "";
		
				$dataArray			= array('callsign'=>$inp_callsign,
											'action'=>'get',
											'debugging'=> $doDebug,
											'testing'=> $testMode);
				$dataResult				= user_master_data($dataArray);
				foreach($dataResult as $thisField => $thisValue) {
					$$thisField		= $thisValue;
				}		
				if ($result == FALSE) {
					$content		.= "Failed to get the student master information. Reason: $reason";			
				} else {
					// format and display the user_master info
					$myStr			= formatActionLog($user_action_log);
					$updateLink		= "<a href='$theURL/?strpass=3&inp_id=$id'>$id<a/>";
					$content		.= "<form method='post' action='$theURL' 
										name='selection_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='3'>
										<input type='hidden' name='inp_id' value='$id'>
										<input type='hidden' name='inp_callsign' value='$inp_callsign'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<h4>User Master Data</h4>
										<table style='width:900px;'>";
					if ($userRole == 'administrator') {
						$content	.= "<tr><td colspan='4'><b>ID</b><br />$updateLink</td></tr>";
					}
					$content		.= "<tr><td><b>Callsign<br />$callsign</b></td>
											<td><b>Name</b><br />$last_name, $first_name</td>
											<td><b>Phone</b><br />+$ph_code $phone</td>
											<td><b>Email</b><br />$email</td></tr>
										<tr><td><b>City</b><br />$city</td>
											<td><b>State</b><br />$state</td>
											<td><b>Zip Code</b><br />$zip_code</td>
											<td><b>Country</b><br />$country</td></tr>
										<tr><td><b>WhatsApp</b><br />$whatsapp</td>
											<td><b>Telegram</b><br />$telegram</td>
											<td><b>Signal</b><br />$signal</td>
											<td><b>Messenger</b><br />$messenger</td></tr>
										<tr><td><b>Languages</b><br />$languages</td>";
					if ($userRole == 'administrator') {
						$content	.= "<td><b>Timezone ID</b><br />$timezone_id</td>
											<td ><b>Date Created</b><br />$date_created</td>
											<td><b>Date Updated</b><br />$date_updated</td></tr>
										<tr><td colspan='4' style='vertical-align:top;'><b>Action Log</b><br />$myStr</td></tr>";
					} else {
						 $content	.= "<td colspan='3'></td></tr>";
					}
					$content		.= "<tr><td colspan='4'><hr></td></tr>
										<tr><td colspan='4'><input type='submit' class='formInputButton' name='submit' value='Update This Information' /></td></tr>
										</table></form>";
				}
//			}
//		}

	} 
	if ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 5 to delete $inp_callsign record<br />";
		}
		$content		.= "<h3>Display and Update $inp_callsign User Master Information</h3>
							<h4>Deleting the User Master Record</h4>";
		// set up the deletion
		$dataArray			= array('callsign'=>$inp_callsign,
									'action'=>'delete',
									'debugging'=> $doDebug,
									'testing'=> $testMode);
		$dataResult				= user_master_data($dataArray);
		foreach($dataResult as $thisField => $thisValue) {
			$$thisField		= $thisValue;
		}
		if ($result == FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content		.= "<p>Deleting $inp_callsign failed. Reason: $reason</p>";
		} else {	
			$content		.= "<p>$inp_callsign record has been removed from $userMasterTableName 
								and a copy of the record has been added to the deleted user master table</p>
								<p><b>NOTE:</b> If you truly want the user deleted, you need to delete 
								the wpw1_user record and all student or advisor records for this user. 
								Otherwise, the user_master record will be automatically resurrected 
								if any program attempts to obtain the user_master data for $inp_callsign.</p>";
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
