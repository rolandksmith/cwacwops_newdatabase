function user_admin_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];

	if ($validUser == 'N') {				
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	if ($userRole != 'administrator') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
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
	$theURL						= "$siteURL/cwa-user-administration/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "User Administration V$versionNumber";

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
			if ($str_key 		== "inp_callsign") {
				$inp_callsign	 = $str_value;
				$inp_callsign	 = filter_var($inp_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "token") {
				$token	 = $str_value;
				$token	 = filter_var($token,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_direction") {
				$inp_direction	 = $str_value;
				$inp_direction	 = filter_var($inp_direction,FILTER_UNSAFE_RAW);
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
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$userTableName				= "wpw1_users";
		$tempTableName				= 'wpw1_cwa_temp_data';
	} else {
		$extMode					= 'pd';
		$userTableName				= "wpw1_users";
		$tempTableName				= 'wpw1_cwa_temp_data';
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Callsign to be managed</td>
								<td style='vertical-align:top;'><input type='text' class='formInputText' size='30' maxlength='30' name='inp_callsign'></td></tr>
							<tr><td style='vertical-align:top;'>Action</td>
								<td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_direction' value='copy' checked>Take over account<br />
															    <input type='radio' class='formInputButton' name='inp_direction' value='restore'>Restore account</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass with inp_callsign: $inp_callsign and direction: $inp_direction<br />";
		}	
		$content			.= "<h3>$jobname</h3>";
		if ($inp_direction == 'copy') {
			if ($doDebug) {
				echo "Taking over $inp_callsign account<br />";
			}
			$content		.= "Taking over $inp_callsign account<br />";
			// Get the user record
			$sql			= "select * from $userTableName 
								where user_login = '$inp_callsign'";
			$userResult		= $wpdb->get_results($sql);
			if ($userResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numURows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numURows rows<br />";
				}
				if ($numURows > 0) {
					foreach($userResult as $userResultRow) {
						$id						= $userResultRow->ID;
						$user_login				= $userResultRow->user_login;
						$user_pass				= $userResultRow->user_pass;
						$user_nicename			= $userResultRow->user_nicename;
						$user_email				= $userResultRow->user_email;
						$user_url				= $userResultRow->user_url;
						$user_registered		= $userResultRow->user_registered;
						$user_activiatioin_key	= $userResultRow->user_activation_key;
						$user_status			= $userResultRow->user_status;
						$display_name			= $userResultRow->display_name;
					}
					if ($doDebug) {
						echo "have a user record at id:$id<br />";
					}
					$content			.= "User data for $inp_callsign retrieved<br />";
					
					// make sure this user hasn't already been taken over
					$sql				= "select count(record_id) 
											from $tempTableName 
											where callsign = '$inp_callsign' 
											and token = 'admin'";
					$thisInt			= $wpdb->get_var($sql);
					if ($thisInt == NULL || $thisInt == 0) {
						if ($doDebug) {
							echo "ran $sql<br />and found count was $thisInt<br />";
						}
						$content		.= "$inp_callsign eligible to take over<br />";															
					
						// set up the data to be saved in temp_data
						$dataArray			= array('email'=>$user_email,
													'password'=>$user_pass);
						$jsonData			= json_encode($dataArray);
						$dateWritten		= date('Y-m-d H:i:s');
						$addResult			= $wpdb->insert($tempTableName,
													array('callsign'=>$inp_callsign,
														  'token'=>'admin',
														  'temp_data'=>$jsonData,
														  'date_written'=>$dateWritten),
													array('%s','%s','%s','%s'));
						if ($addResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							if ($doDebug) {
								echo "added temp_data record<br />";
							}
						}
						$content			.= "$inp_callsign user data saved. Taking over the account<br />";
						
						$newPass			= '$P$B1tU0lmTWuzzNd35QIy5rAIfTrPV7O1';
						$newEmail			= '';
						if ($userName == 'K7OJL') {
							$newEmail		= "rolandksmith+$user_login@gmail.com";
						}
						if ($userName == 'WR7Q') {
							$newEmail		= "kcgator+$user_login@gmail.com";
							
						}
						if ($newEmail == '') {
							$content		.= "No valid adminstrator found. Done<br />";
							
						} else {
							$updateResult	= $wpdb->update($userTableName,
															array('user_pass'=>$newPass,
																   'user_email'=>$newEmail),
															array('ID'=>$id),
															array('%s','%s'),
															array('%d'));
							if ($updateResult === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$content	.= "User record updated and available for login<br />
												Username: $user_login<br />
												Password: N3wPass2993<br />
												Email: $newEmail<br />";
							}
						}
					} else {
						$content			.= "$inp_callsign is already taken over<br />";
					}					
				} else {
					$content					= "No $userTableName record for $inp_callsign<br />";
				}
			}
		} elseif ($inp_direction == 'restore') {
			if ($doDebug) {
				echo "restoring $inp_callsign<br />";
			}
			$content				.= "Restoring $inp_callsign Account<br />";
			// get the temp_data record for this user
			$sql					= "select * from $tempTableName 
										where callsign = '$inp_callsign' 
										and token = 'admin'";
			$tempResult				= $wpdb->get_results($sql);
			if ($tempResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numTRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numTRows rows<br />";
				}
				if ($numTRows > 0) {
					foreach($tempResult as $tempResultRow) {
						$record_id		= $tempResultRow->record_id;
						$callsign		= $tempResultRow->callsign;
						$token			= $tempResultRow->token;
						$temp_data		= $tempResultRow->temp_data;
						$date_written	= $tempResultRow->date_written;
					}
					// unpack temp_data
					$myArray			= json_decode($temp_data,TRUE);
					$oldEmail			= $myArray['email'];
					$oldPassword		= $myArray['password'];
					
					// now get the user record
					$sql			= "select * from $userTableName 
										where user_login = '$inp_callsign'";
					$userResult		= $wpdb->get_results($sql);
					if ($userResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numURows	= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and retrieved $numURows rows<br />";
						}
						if ($numURows > 0) {
							foreach($userResult as $userResultRow) {
								$id						= $userResultRow->ID;
								$user_login				= $userResultRow->user_login;
								$user_pass				= $userResultRow->user_pass;
								$user_nicename			= $userResultRow->user_nicename;
								$user_email				= $userResultRow->user_email;
								$user_url				= $userResultRow->user_url;
								$user_registered		= $userResultRow->user_registered;
								$user_activiatioin_key	= $userResultRow->user_activation_key;
								$user_status			= $userResultRow->user_status;
								$display_name			= $userResultRow->display_name;
							}
							if ($doDebug) {
								echo "have a user record at id:$id<br />";
							}
							// update the user record
							$updateResult				= $wpdb->update($userTableName,
																		array('user_email'=>$oldEmail,
																		      'user_pass'=>$oldPassword),
																		array('ID'=>$id),
																		array('%s','%s'),
																		array('%d'));
							if ($updateResult === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								if ($doDebug) {
									echo "updating $userTableName modified $updateResult rows<br />";
								}
								$content				.= "$inp_callsign user data has been restored<br />";
								
								// now delete the temp_data record
								$deleteResult			= $wpdb->delete($tempTableName, 
																	array('callsign'=>$inp_callsign,
																		  'token'=>'admin'),
																	array('%s','%s'));
								if ($deleteResult === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									if ($doDebug) {
										echo "deleting $inp_callsign admin token resulted in deleting $deleteResult rows<br />";
									}
									$content			.= "Temp_data record for $inp_callsign has been deleted<br />
															$inp_callsign user record has been restored<br />";
								}
							}
						} else {
							$content					.= "No user record found to restore for $inp_callsign<br />";
						}
					}
				} else {
					$content							.= "No temp_data record found for $inp_callsign to restore<br />";
				}
			}
		} else {
			$content									.= "Invalid information provided<br />";
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
add_shortcode ('user_admin', 'user_admin_func');
