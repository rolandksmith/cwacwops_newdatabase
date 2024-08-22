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
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
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
	$theURL						= "$siteURL/cwa-display-and-update-user-master-information/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Display and Update User Master V$versionNumber";

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
			if ($str_key == "inp_country_code") {
				$inp_country_code = $str_value;
				$inp_country_code = filter_var($inp_country_code,FILTER_UNSAFE_RAW);
			}
			if ($str_key == "inp_whatsapp_app") {
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
			if ($str_key == "inp_action_log") {
				$inp_action_log = $str_value;
				$inp_action_log = filter_var($inp_action_log,FILTER_UNSAFE_RAW);
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
	} else {
		$extMode					= 'pd';
		$userMasterTableName		= "wpw1_cwa_user_master";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
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
	
		$content			.= "<h3>Display and Update $inp_callsign User Masster Information</h3>
								<p><a href='$theURL'>Look Up a Different User</a>";
	
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
			$updateLink		= "<a href='$theURL/?strpass=3&inp_callsign=$inp_callsign'>$callsign<a/>";
			$content		.= "<h4>User Master Data</h4>
							<table style='width:900px;'>
							<tr><td><b>Callsign<br />$updateLink</b></td>
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
							<tr><td style='vertical-align:top;'><b>Date Created</b><br />$date_created</td>
								<td style='vertical-align:top;'><b>Date Updated</b><br />$date_updated</td>
								<td colspan='2' style='vertical-align:top;'><b>Action Log</b><br />$myStr</td></tr>
							</table>
							<p>Click <a href='$theURL/?strpass=3&inp_callsign=$inp_callsign'>HERE</a> to update the Student Master Data</p>";
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
			<tr><td>ph_code</td>
				<td><input type='text' class='formInputText' name='inp_ph_code' length='5' 
				maxlength='5' value='$ph_code'></td></tr>
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
			<tr><td>country_code</td>
				<td><input type='text' class='formInputText' name='inp_country_code' length='5' 
				maxlength='5' value='$country_code'></td></tr>
			<tr><td>country</td>
				<td><input type='text' class='formInputText' name='inp_country' length='50' 
				maxlength='50' value='$country'></td></tr>
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
			<tr><td style='vertical-align:top;'>action_log</td>
				<td><textarea class='formInputText' name='inp_action_log' rows='5' cols='50'>$user_action_log</textarea></td></tr>
			<tr><td>date_created</td>
				<td>$date_created</td></tr>
			<tr><td>date_updated</td>
				<td>$date_updated</td></tr>
			<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit Updates' /></td></tr>
			</table></form>";
		}
		
	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 4 with inp_callsign: $inp_callsign<br >";
		}
		
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

			$thisDate = date('Y-m-d H:i:s');
			$updateParams	= array('callsign'=>$inp_callsign,
									'action'=>'update',
									'debugging'=> $doDebug,
									'testing'=> $testMode);
			$updateLog	= " /$thisDate $userName performed the following updates:";
			if ($inp_first_name != $first_name) {
				$first_name = $inp_first_name;
				$updateParams['first_name']	= $inp_first_name;
				$content	.= "first_name updated to $inp_first_name<br />";
				$updateLog	.= " / first_name updated to $inp_first_name";
			}
			if ($inp_last_name != $last_name) {
				$last_name = $inp_last_name;
				$updateParams['last_name']	= $inp_last_name;
				$content	.= "last_name updated to $inp_last_name<br />";
				$updateLog	.= " / last_name updated to $inp_last_name";
			}
			if ($inp_email != $email) {
				$email = $inp_email;
				$updateParams['email']	= $inp_email;
				$content	.= "email updated to $inp_email<br />";
				$updateLog	.= " / email updated to $inp_email";
			}
			if ($inp_phone != $phone) {
				$phone = $inp_phone;
				$updateParams['phone']	= $inp_phone;
				$content	.= "phone updated to $inp_phone<br />";
				$updateLog	.= " / phone updated to $inp_phone";
			}
			if ($inp_city != $city) {
				$city = $inp_city;
				$updateParams['city']	= $inp_city;
				$content	.= "city updated to $inp_city<br />";
				$updateLog	.= " / city updated to $inp_city";
			}
			if ($inp_state != $state) {
				$state = $inp_state;
				$updateParams['state']	= $inp_state;
				$content	.= "state updated to $inp_state<br />";
				$updateLog	.= " / state updated to $inp_state";
			}
			if ($inp_zip_code != $zip_code) {
				$zip_code = $inp_zip_code;
				$updateParams['zip_code']	= $inp_zip_code;
				$content	.= "zip_code updated to $inp_zip_code<br />";
				$updateLog	.= " / zip_code updated to $inp_zip_code";
			}
			if ($inp_country_code != $country_code) {
				$country_code = $inp_country_code;
				$updateParams['country_code']	= $inp_country_code;
				$content	.= "country_code updated to $inp_country_code<br />";
				$updateLog	.= " / country_code updated to $inp_country_code";
			}
			if ($inp_whatsapp != $whatsapp) {
				$whatsapp = $inp_whatsapp;
				$updateParams['whatsapp']	= $inp_whatsapp;
				$content	.= "whatsapp updated to $inp_whatsapp<br />";
				$updateLog	.= " / whatsapp updated to $inp_whatsapp";
			}
			if ($inp_telegram != $telegram) {
				$telegram = $inp_telegram;
				$updateParams['telegram']	= $inp_telegram;
				$content	.= "telegram updated to $inp_telegram<br />";
				$updateLog	.= " / telegram updated to $inp_telegram";
			}
			if ($inp_signal != $signal) {
				$signal = $inp_signal;
				$updateParams['signal']	= $inp_signal;
				$content	.= "signal updated to $inp_signal<br />";
				$updateLog	.= " / signal updated to $inp_signal";
			}
			if ($inp_messenger != $messenger) {
				$messenger = $inp_messenger;
				$updateParams['messenger']	= $inp_messenger;
				$content	.= "messenger updated to $inp_messenger<br />";
				$updateLog	.= " / messenger updated to $inp_messenger";
			}
			if ($inp_action_log != $user_action_log) {
				$action_log = $inp_action_log;
				$content	.= "action_log updated to $inp_action_log<br />";
			}
			
			// if there are any updates, do the update
			if (count($updateParams) < 5) {
				$content		.= "<p>No updates requested</p>";
				if ($doDebug) {
					echo "no updates requested<br />";
				}
			} else {
				if ($doDebug) {
					echo "updateParams:<br /><pre>";
					print_r($updateParams);
					echo "</pre><br />";
				}
				$action_log		.= $updateLog;
				$updateParams['user_action_log']	= $action_log;
				
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
				$date_created	= "";
				$date_updated	= "";
		
				$dataResult				= user_master_data($updateParams);
				foreach($dataResult as $thisField => $thisValue) {
					$$thisField		= $thisValue;
				}		
				if ($result == FALSE) {
					$content		.= "Failed to update the student master information. Reason: $reason";	
					if ($doDebug) {
						echo "Failed to update the student master information. Reason: $reason<br />";
					}		
					$doProceed		= FALSE;
				}
			}

			if ($doProceed) {
				// format and display the user_master info
				$myStr			= formatActionLog($user_action_log);
				$updateLink		= "<a href='$theURL/?strpass=3&inp_callsign=$inp_callsign'>$callsign<a/>";
				$content		.= "<h4>User Master Data</h4>
								<table style='width:900px;'>
								<tr><td><b>Callsign<br />$updateLink</b></td>
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
								<tr><td style='vertical-align:top;'><b>Date Created</b><br />$date_created</td>
									<td style='vertical-align:top;'><b>Date Updated</b><br />$date_updated</td>
									<td colspan='2' style='vertical-align:top;'><b>Action Log</b><br />$myStr</td></tr>
								</table>
								<p>Click <a href='$theURL/?strpass=3&inp_callsign=$inp_callsign'>HERE</a> to update the Student Master Data</p>";
			}
		}

	} elseif ("5" == $strPass) {
	
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
