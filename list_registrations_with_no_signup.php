function list_registrations_with_no_signup_func() {

/*	The program has to options

	The first option is to prepare a list of all user_logins found in wpw1_users who 
	created a user_login on or after a specific date but have not signed up for a class. 
	
	The second option is to prepare a list of all user_logins found in wpw1_users who 
	created a user_login before a specific date but have not signed up for a class. The 
	program can either just prepare a list, or can prepare a list and delete the user_login 
	from wpw1_users and wpw1_usermeta
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
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
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
	$theURL						= "$siteURL/cwa-list-registrations-with-no-signup/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$version					= '1';
	$jobname					= "List Registrations with no Sign-up V$version";
	$inp_date					= "";
	$inp_option					= "";

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
			if ($str_key 		== "inp_date") {
				$inp_date	 = $str_value;
				$inp_date	 = filter_var($inp_date,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_option") {
				$inp_option	 = $str_value;
				$inp_option	 = filter_var($inp_option,FILTER_UNSAFE_RAW);
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
		$userMetaTableName			= "wpw1_usermeta";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$studentTableName			= "wpw1_cwa_consolidated_student2";
	} else {
		$extMode					= 'pd';
		$userTableName				= "wpw1_users";
		$userMetaTableName			= "wpw1_usermeta";
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$studentTableName			= "wpw1_cwa_consolidated_student";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>Select the run option and enter the date
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Run Option</td>
								<td><input type='radio' class='formInputButton' name='inp_option' value='after' required>List User_Logins with no signup registered on or after a specific date<br />
									<input type='radio' class='formInputButton' name='inp_option' value='before' required>List User_Logins with no signup registered before a specific date<br />
									<input type='radio' class='formInputButton' name='inp_option' value='delete' required>List and delete User_Logins with no signup registered before a specific date</td></tr>
							<tr><td>Date</td>
								<td><input type='text' class='formInputText' name='inp_date' size='20' maxlength='20' required></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 2 with option of $inp_option and Date of $inp_date<br />";
		}
		
		$doProceed			= TRUE;
		$content			.= "<h3>$jobname</h3>";
		if ($inp_option == 'after') {
			$content		.= "<h4>Listing User_logins with No Sign-up Created After $inp_date</h4>";
		} elseif ($inp_option == 'before') {
			$content		.= "<h4>Listing User_logins with No Sign-up Created Before $inp_date</h4>";
		} elseif ($inp_option == 'delete') {
			$content		.= "<h4>List and Delete User_logins with No Sign-up Created Before $inp_date</h4>";
		} else {
			$content		.= "<p>Invalid option entered</p>";
			$doProceed		= FALSE;
		}
		
		if ($doProceed) {
			$noAdvisorSignup	= array();
			$noStudentSignup	= array();
			$errors				= array();
			$deleteArray		= array();
			
			if ($inp_option == 'after') {
				$sql				= "SELECT ID, 
											   user_login, 
											   user_email, 
											   display_name, 
											   user_registered 
										FROM `wpw1_users` 
										where user_registered >= '$inp_date' 
										order by user_login";
			} else {
				$sql				= "SELECT ID, 
											   user_login, 
											   user_email, 
											   display_name, 
											   user_registered 
										FROM `wpw1_users` 
										where user_registered < '$inp_date' 
										order by user_login";
			}
			$userResult			= $wpdb->get_results($sql);
			if ($userResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numURows		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numURows rows<br />";
				}
				if ($numURows > 0) {
					foreach($userResult as $userRow) {
						$user_ID			= $userRow->ID;
						$user_login			= $userRow->user_login;
						$user_email			= $userRow->user_email;
						$user_registered	= $userRow->user_registered;
			
						if ($doDebug) {
							echo "<br />Processing user_login: $user_login<br />";
						}
			
						$user_role			= '';
						$verifiedUser		= TRUE;
						$user_last_name		= "";
						$user_first_name	= "";
						
						// now get the meta info and decide which signup record to check
						$metaSQL		= "select meta_key, meta_value 
											from `wpw1_usermeta` 
											where user_id = $user_ID 
											and (meta_key = 'first_name' 
												or meta_key = 'last_name' 
												or meta_key = 'wpw1_capabilities' 
												or meta_key = 'wpumuv_needs_verification')";
						$metaResult		= $wpdb->get_results($metaSQL);
						if ($metaResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numMRows	= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $metaSQL<br />and retrieved $numMRows rows<br />";
							}
							if ($numMRows > 0) {
								foreach($metaResult as $metaRow) {
									$meta_key		= $metaRow->meta_key;
									$meta_value		= $metaRow->meta_value;
									
									if ($meta_key == 'last_name') {
										$user_last_name	= $meta_value;
									}
									if ($meta_key == 'first_name') {
										$user_first_name = $meta_value;
									}
									if ($meta_key == 'wpw1_capabilities') {
										$myInt			= strpos($meta_value,'administrator');
										if ($myInt !== FALSE) {
											$user_role	= 'administrator';
										}
										$myInt			= strpos($meta_value,'student');
										if ($myInt !== FALSE) {
											$user_role	= 'student';
										}
										$myInt			= strpos($meta_value,'advisor');
										if ($myInt !== FALSE) {
											$user_role	= 'advisor';
										}
									} 
									if ($meta_key == 'wpumuv_needs_verification') {
										$verifiedUser				= FALSE;
									} else {	
										$verifiedUser				= TRUE;
									} 
								}
								// got the needed user info
								if ($doDebug) {
									echo "<br />Have the following information:<br />
										  user_login: $user_login<br />
										  user_email: $user_email<br />
										  user_registered: $user_registered<br />
										  user_role: $user_role<br />";
									  if ($verifiedUser) {
										echo "verifiedUser: TRUE<br />";
									  } else {
										echo "verifiedUser: FALSE<br />";
									  }
								}
								// now get the appropriate signup info
								$user_login			= strtoupper($user_login);
								if ($user_role == 'advisor') {
									$advisorSQL		= "select count(call_sign) 
														from $advisorTableName 
														where call_sign = '$user_login'";
									$advisorResult		= $wpdb->get_var($advisorSQL);
									if ($advisorResult === NULL) {
										if ($doDebug) {
											echo "ran $advisorSQL<br />which returned NULL. Shouldn't happen<br />";
										}
										$advisorCount	= 0;
									} else {
										$advisorCount	= $advisorResult;
										if ($doDebug) {
											echo "ran $advisorSQL<br />which returned a count of $advisorCount rows<br />";
										}
									}
									if ($advisorCount == 0) {		// this is what we're looking for
										$noAdvisorSignup[]		= "$user_email\t$user_last_name, $user_first_name ($user_login)\t$user_ID";
									}
								
								} elseif ($user_role == 'student') {
									$studentSQL		= "select count(call_sign) 
														from $studentTableName 
														where call_sign = '$user_login'";
									$studentResult		= $wpdb->get_var($studentSQL);
									if ($studentResult === NULL) {
										if ($doDebug) {
											echo "ran $studentSQL<br />which returned NULL. Shouldn't happen<br />";
										}
										$studentCount	= 0;
									} else {
										$studentCount	= $studentResult;
										if ($doDebug) {
											echo "ran $studentSQL<br />which returned a count of $studentCount rows<br />";
										}
									}
									if ($studentCount == 0) {		// this is what we're looking for
										$noStudentSignup[]		= "$user_email\t$user_last_name, $user_first_name ($user_login)\t$user_ID";
										if ($inp_option == 'delete') {
											$deleteArray[]		= $user_ID;
										}
									}
								
								} elseif ($user_role == 'administrator') {
									if ($doDebug) {
										echo "User is an administrator. No action being taken<br />";
									}
								} else {
									$errors[]			= "$user_login has no role";
								}
							}
						}
					}
					if (count($noAdvisorSignup) > 0) {
						$content	.= "<h5>Advisors with No Signup</h5><p><pre>";
						foreach($noAdvisorSignup as $thisRow) {
							$content	.= "$thisRow\n";
						}
						$myInt			= count($noAdvisorSignup);
						$content		.= "</pre></p>
											<p>$myInt records listed</p>";
					} else {
						$content	.= "<p>No advisors registrations without an advisor record</p>";
					}
					if (count($noStudentSignup) > 0) {
						$content	.= "<h5>Students with No Signup</h5><p><pre>";
						foreach($noStudentSignup as $thisRow) {
							$content	.= "$thisRow\n";
						}
						$myInt			= count($noStudentSignup);
						$content		.= "</pre></p>
											<p>$myInt records listed</p>";
					} else {
						$content	.= "<p>No students registrations without an student record</p>";
					}
					
					// if option is to delete, then delete the user and usermeta records
					if ($inp_option == 'delete') {
						$content	.= "<h5>Deleting the Following User_ID's</h5>";
						if (count($deleteArray) > 0) {
							foreach($deleteArray as $thisRow) {
								$userDelete	= $wpdb->delete($userTableName, 
															array('ID'=>$thisRow),
															array('%d'));
								if ($userDelete === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									if ($userDelete > 0) {
										$content	.= "Deleted ID=$thisRow from $userTableName<br />";
										
										// now delete the userMeta records
										$metaDelete	= $wpdb->delete($userMetaTableName, 
																	array('user_id'=>$thisRow),
																	array('%d'));
										if ($metaDelete === FALSE) {
											handleWPDBError($jobname,$doDebug);
										} else {
											if ($metaDelete > 0) {
												$content	.= "Deleted $metaDelete rows for user_id=$thisRow from $userMetaTableName table<br />";
											} else {
												$content	.= "ERROR: No $userMetaTableName records found with user_id=$thisRow<br />";
												$errors[]	= "No $userMetaTableName records found with user_id=$thisRow";
											}
										}
									} else {
										$content	.= "ERROR: No $userTableName record found with ID=$thisRow<br />";
										$errors[]	= "No $userTableName record found with ID=$thisRow";
									}
								}
							}
							$myInt			= count($deleteArray);
							$content		.= "<p>$myInt records listed</p>";
						} else {
							$content	.= "<p>No records found in deleteArray</p>";
						}
					}
					if (count($errors) > 0) {
						$content	.= "<h4>Errors Encountered</h4>";
						foreach($errors as $thisRow) {
							$content	.= "$thisRow<br />";
						}
					}
				} else {
					$content		.= "<p>No data found in $userTableName table with a registration 
										date greater or equal to $inp_date</p>";
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('list_registrations_with_no_signup', 'list_registrations_with_no_signup_func');
