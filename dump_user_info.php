function dump_user_info_func() {

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
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
		$doDebug			= FALSE;
		$testMode			= FALSE;
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
	$theURL						= "$siteURL/cwa-dump-user-info/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Dump Users and UserMeta Info";

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
			if ($str_key 		== "inp_rsave") {
				$inp_rsave		 = $str_value;
				$inp_rsave		 = filter_var($inp_rsave,FILTER_UNSAFE_RAW);
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
			if ($str_key 		== "inp_user") {
				$inp_user	 = $str_value;
				$inp_user	 = filter_var($inp_user,FILTER_UNSAFE_RAW);
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
		$usersTableName				= "wpw1_users2";
		$userMetaTableName			= "wpw1_usetmeta2";
		$userMasterTableName		= "wpw1_cwa_user_master2";
	} else {
		$usersTableName				= "wpw1_users";
		$userMetaTableName			= "wpw1_usermeta";
		$userMasterTableName		= "wpw1_cwa_user_master";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>User Name</td>
								<td><input type='text' class='formInputText' name='inp_user' size='20' maxlength='20'></td></tr>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2 with $inp_user<br />";
		}
	
		$content		.= "<h3>$jobname for $inp_user</h3>";
		if ($inp_user == '') {
			$content	.= "inp_user is missing<br />";
		} else {
			$sql	= "select * from $usersTableName 
						where user_login like '$inp_user'";
			$result	= $wpdb->get_results($sql);
			if (!empty($wpdb->last_error)) {
				$lastError	= $wpdb->last_error;
				$lastQuery 	= $wpdb->last_query;
				$logMsg		= "DUmp User Info Users - Database Error: $lastError | Query: $lastQuery";
				error_log($logMsg);
	//			sendErrorEmail($logMsg);
				if ($doDebug) {
					echo "$logMsg<br />";
				}
				$result	= NULL;
			} else {
				$numRows		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					foreach($result as $usersRow) {
						$ID						= $usersRow->ID;
						$user_login				= $usersRow->user_login;
						$user_pass				= $usersRow->user_pass;
						$user_nicename			= $usersRow->user_nicename;
						$user_email				= $usersRow->user_email;
						$user_url				= $usersRow->user_url;
						$user_registered		= $usersRow->user_registered;
						$user_activation_key	= $usersRow->user_activation_key;
						$user_status			= $usersRow->user_status;
						
						$content		.= "<h4>Users Record</h4>
											<table><tr><th>Field</th>
														<th>Value</th></tr>
													<tr><td>ID</td>
														<td>$ID</td></tr>
													<tr><td>User Login</td>
														<td>$user_login</td></tr>
													<tr><td>User Pass</td>
														<td>$user_pass</td></tr>
													<tr><td>User Nicename</td>
														<td>$user_nicename</td></tr>
													<tr><td>User Email</td>
														<td>$user_email</td></tr>
													<tr><td>User URL</td>
														<td>$user_url</td></tr>
													<tr><td>User Registered</td>
														<td>$user_registered</td>
													<tr><td>User Activation Key</td>
														<td>$user_activation_key</td></tr>
													<tr><td>User Status</td>
														<td>$user_status</td><tr>
													</table>";
						$metaSQL	= "select * from $userMetaTableName 
										where user_id = $ID 
										order by umeta_id";
						$metaResult	= $wpdb->get_results($metaSQL);
						if (!empty($wpdb->last_error)) {
							$lastError	= $wpdb->last_error;
							$lastQuery 	= $wpdb->last_query;
							$logMsg		= "Dump User Info UserMeta - Database Error: $lastError | Query: $lastQuery";
							error_log($logMsg);
				//			sendErrorEmail($logMsg);
							if ($doDebug) {
								echo "$logMsg<br />";
							}
							$result	= NULL;
						} else {
							$numMRows		= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $metaSQL and retrieved $numMRows rows<br />";
							}
							if ($numMRows > 0) {
								$content	.= "<h4>User Meta for $inp_user</h4>
												<table><tr><th>Field</th>
															<th>Value</th></tr>";
								foreach($metaResult as $userMetaRow) {
									$umeta_id		= $userMetaRow->umeta_id;
									$user_id		= $userMetaRow->user_id;
									$meta_key		= $userMetaRow->meta_key;
									$meta_value		= $userMetaRow->meta_value;
									
									if ($meta_key == 'submitted') {
										$metaArray			= unserialize($meta_value);					
										$user_first_name	= $metaArray['first_name'];
										$user_last_name		= $metaArray['last_name'];
										$user_email			= $metaArray['user_email'];
										$user_phone			= $metaArray['phone_number'];
										$user_city			= $metaArray['user_city'];
										$user_state			= $metaArray['user_state'];
										$user_zip_code		= $metaArray['user_zipcode'];
										$user_country		= $metaArray['country'];
										if (array_key_exists('whatsapp',$metaArray)) {
											$user_whatsapp		= $metaArray['whatsapp'];
										} else {
											$user_whatsapp		= '';
										}
										if (array_key_exists('user_telegram',$metaArray)) {
											$user_telegram		= $metaArray['user_telegram'];
										} else {
											$user_telegram		= '';
										}
										if (array_key_exists('user_signal',$metaArray)) {
											$user_signal		= $metaArray['user_signal'];
										} else {
											$user_signal		= '';
										}
										if (array_key_exists('user_messenger',$metaArray)) {
											$user_messenger		= $metaArray['user_messenger'];
										} else {
											$user_messenger		= '';
										}
										$content	.= "<tr><td colspan='2'>Submitted</td></tr>
														<tr><td>User First Name</td>
															<td>$user_first_name</td></tr>
														<tr><td>User Last Name</td>
															<td>$user_last_name</td></tr>
														<tr><td>User Email</td>
															<td>$user_email</td></tr>
														<tr><td>User Phone</td>
															<td>$user_phone</td></tr>
														<tr><td>User City</td>
															<td>$user_city</td></tr>
														<tr><td>User State</td>
															<td>$user_state</td></tr>
														<tr><td>User Zip Code</td>
															<td>$user_zip_code</td></tr>
														<tr><td>User Country</td>
															<td>$user_country</td></tr>
														<tr><td>WhatsApp</td>
															<td>$user_whatsapp</td></tr>
														<tr><td>Signal</td>
															<td>$user_signal</td></tr>
														<tr><td>Telegram</td>
															<td>$user_telegram</td></tr>
														<tr><td>Messenger</td>
															<td>$user_messenger</td></tr>";
									} else {
										$content	.= "<tr><td style='vertical-align:top;'>$meta_key</td>
															<td>$meta_value</td></tr>";
									}
								} 
								$content		.= "</table>";
							} else {
								$content		.= "<p>No record found in $userMetaTableName for $inp_user with id $ID</p>";
							}
						}
									
					}
					// get and display the user_master record if there is one
					$sql				= "select * from $userMasterTableName 
											where user_call_sign like '$inp_user'";
					$userMasterResult	= $wpdb->get_results($sql);
					if ($userMasterResult === FALSE) {
						handleWPDBError($jobname,$doDebug,"initial read of userMasterTableName failed");
					} else {
						$numURows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $sql<br />and retrieved $numURows rows<br />";
						}
						if ($numURows > 0) {
							$gotData					= TRUE;
							foreach($userMasterResult as $sqlRow) {
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
								$content		.= "<h4>User Master Data for $inp_user</h4>
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
														<td><b>Date Updated</b><br />$user_date_updated</td></tr>
													<tr><td><b>Survey Score</b><br />$user_survey_score</td>
														<td><b>Is Admin</b><br />$user_is_admin</td>
														<td><b>Role</b><br />$user_role</td>
														<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
													<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>
													</table>";
							}
						} else {
							$content	.= "<p>No User Master Record Found</p>";
						}
					}
					
					
				} else {
					$content	.= "<p>No record found in $usersTable Name for $inp_user</p>";
				}
			
			}
		}
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";

	///// uncomment if the code to save a report is needed
	if ($doDebug) {
		echo "<br '>Checking to see if the report is to be saved. inp_rsave: $inp_rsave<br />";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Current Student and Advisor Assignments<br />";
		}
		$storeResult	= storeReportData_v2($jobname,$content);
		if ($storeResult[0] !== FALSE) {
			$reportName	= $storeResult[1];
			$reportID	= $storeResult[2];
			$content	.= "<br />Report stored in reports as $reportName<br />
							Go to'Display Saved Reports' or url<br/>
							$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID<br /><br />";
		} else {
			$content	.= "<br />Storing the report in the reports pod failed";
		}
	}

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
add_shortcode ('dump_user_info', 'dump_user_info_func');
