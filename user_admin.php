function user_admin_func() {

/*	Interaction between programs

	When taking over an account
		temp_data token is admin, callsign is the callsign of the account being taken over
		the reminderToken is in the json variable
		set up the reminder with the reminderToken
			the link goes to pass 2 with the
				inp_callsign being the account to recover
				inp_direction set to 'recover'
				token set to reminderToken

*/
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

	if ($userName == '') {				
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
	$token						= '';
	$inp_direction				= 'pass1';
	$jobcomments				= '';

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
	
	$content = "";

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$userTableName				= "wpw1_users";
		$tempTableName				= 'wpw1_cwa_temp_data';
		$operatingMode				= 'Testmode';
	} else {
		$extMode					= 'pd';
		$userTableName				= "wpw1_users";
		$tempTableName				= 'wpw1_cwa_temp_data';
		$operatingMode				= 'Production';
	}

	$user_dal = new CWA_User_Master_DAL();


	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<input type='hidden' name='token' value='$token'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Callsign to be managed</td>
								<td style='vertical-align:top;'><input type='text' class='formInputText' size='30' maxlength='30' name='inp_callsign' autofocus></td></tr>
							<tr><td style='vertical-align:top;'>Action</td>
								<td style='vertical-align:top;'><input type='radio' class='formInputButton' name='inp_direction' value='copy' checked>Take over account<br />
															    <input type='radio' class='formInputButton' name='inp_direction' value='restore'>Restore account</td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass with inp_callsign: $inp_callsign<br />
				  direction: $inp_direction<br />
				  token: $token<br />";
		}	
		$content			.= "<h3>$jobname</h3>";
		$jobcomments		= $inp_callsign;
		$doProceed			= TRUE;
		$myStr				= strtolower($userName);
		$myStr1				= strtolower($inp_callsign);
		if ($myStr1 == $myStr) {
			$content		.= "You can't take over your own account";
			$doProceed		= FALSE;
		}
		if ($doProceed) {
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
							
							// setup reminder to restore account
							$effective_date		 	= date('Y-m-d 00:00:00');
							$closeStr				= strtotime("+10 days");
							$close_date				= date('Y-m-d H:i:s', $closeStr);
							$email_text				= "<p></p>";
							$reminderToken			= mt_rand();
							$reminder_text			= "<p><b>Restore Account:</b> Advisor $inp_callsign account has been taken 
														over by $userName. Click <a href='$theURL?strpass=2&inp_callsign=$inp_callsign&inp_direction=restore&token=$reminderToken' target='_blank'>HERE</a> to restore 
														the account</p>";
							$inputParams		= array("effective_date|$effective_date|s",
														"close_date|$close_date|s",
														"resolved_date||s",
														"send_reminder|N|s",
														"send_once|Y|s",
														"call_sign|$userName|s",
														"role||s",
														"email_text|$email_text|s",
														"reminder_text|$reminder_text|s",
														"resolved||s",
														"token|$reminderToken|s");
							$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
							if ($insertResult[0] === FALSE) {
								if ($doDebug) {
									echo "inserting reminder failed: $insertResult[1]<br />";
								}
								$content		.= "Inserting reminder failed: $insertResult[1]<br />";
							} else {
								$content		.= "Reminder successfully added<br />";
							}
																					
						
							// set up the data to be saved in temp_data
							$dataArray			= array('email'=>$user_email,
														'password'=>$user_pass, 
														'username'=>$userName,
														'reminderToken'=>$reminderToken);
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
							
							$newPass			= '$2y$10$19e.6giNP9fjBP57QhkGHOu8YLv7cLrPpwAqt/DIzCf2xHesCqW2K';
							$updateResult	= $wpdb->update($userTableName,
															array('user_pass'=>$newPass,
																   'user_email'=>$userEmail),
															array('ID'=>$id),
															array('%s','%s'),
															array('%d'));
							if ($updateResult === FALSE) {
								handleWPDBError($jobname,$doDebug);
							} else {
								$content	.= "User record updated and available for login<br /><br />
												Username: $user_login<br />
 	 											Password: Udxa&dxcc1<br />";
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
							$tempToken		= $tempResultRow->token;
							$temp_data		= $tempResultRow->temp_data;
							$date_written	= $tempResultRow->date_written;
						}
						// unpack temp_data
						$myArray			= json_decode($temp_data,TRUE);
						$oldEmail			= $myArray['email'];
						$oldPassword		= $myArray['password'];
						$oldUserName		= $myArray['username'];
						$reminderToken		= $myArray['reminderToken'];
						
						if ($doDebug) {
							echo "got the following from temp_data:<br />
									oldEmail: $oldEmail<br />
									oldPassword: $oldPassword<br />
									oldUserName: $oldUserName<br />
									reminderToken: $reminderToken<br />";
						}
						
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
									
									if ($doDebug) {
										$content .= "updating user_master email<br />";
									}
									$myStr = strtoupper($user_login);
									$userMasterData = $user_dal->get_user_master_by_callsign($myStr, $operatingMode);
									if ($userMasterData === FALSE || $userMasterData === NULL) {
										$content .= "Attempt to get User_Master for $myStr failed<br />";
									} else {
										if (!empty($userMasterData)) {
											foreach($userMasterData as $key => $value) {
												$$key = $value;
											}
											if (! isset($user_call_sign)) {
												if ($doDebug) {
													echo "user_dal returned data but user_call_sign not set<br />";
												}
												$content .= "Unable to obtain User_Master for $myStr<br />";
											} else {	// update the user_master email
												$updateData = array('user_email' => $oldEmail);
												$updateResult = $user_dal->update($user_ID, $updateData, $operatingMode);
												if ($updateResult === FALSE || $updateResult === NULL) {
													if ($doDebug) {
														echo "attempt to update user_master for id $user_ID failed<br />";
													}
													$content .= "Attempt to update user_master for id $user_ID failed<br />";
												} else {
													$content .= "User_Master email address updated to $oldEmail<br />";
													// resolve the reminder
													$resolveResult				= resolve_reminder($userName,$reminderToken,$testMode,$doDebug);
													if ($resolveResult === FALSE) {
														if ($doDebug) {
															echo "resolve_reminder for $inp_callsign and $reminderToken failed<br />";
														}
													} else {
														$content	.= "Reminder has been resolved<br />";
													}
				
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
														$content			.= "Temp_data record for $inp_callsign has been deleted<br /><br />
																				$inp_callsign user record has been restored<br />";
													}
												}
											}
										} else {
											if ($doDebug) {
												echo "getting user_master for $myStr returned an empty dataset<br />";
											}
										}
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
	}
	$thisTime 		= current_time('mysql', 1);
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
							'jobmode'		=> $inp_direction,
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> $jobcomments,
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}
	return $content;
}
add_shortcode ('user_admin', 'user_admin_func');
