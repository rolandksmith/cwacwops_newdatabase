function RKSTEST_fill_profile_fields_func() {

/*
		meta_key		meta_value	
		nickname		g2hij				already filled
		first_name		Rolando				already filled
		last_name		Smitty				already filled
		wpum_field_18	City
		wpum_field_19	State
		wpum_field_15	Country Code
		wpum_field_14	Phone number
		wpum_field_20	WhatsApp	
		wpum_field_21	Telegram
		wpum_field_22	Signal
		wpum_field_23	Messenger
		wpum_field_24	ZipCode
		
		Foreach user_ID 
			determine if a student or an advisor
			see if there is a signup record
			if so, write the userMeta records
			
		umeta_id
		user_id
		meta_key
		meta_value
		
*/

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
	$theURL						= "$siteURL/rkstest-fill-profile-fields/";
	$inp_semester				= '';
	$jobname					= "RKSTEST - Fill Profile Fields";

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
		$advisorTableName			= "wpw1_cwa_consolidated_advisor2";
		$studentTableName			= "wpw1_cwa_consolidated_student2";
		$userTableName				= "wpw1_users";
		$userMetaTableName			= "wpw1_usermeta";
		$userMasterTableName		= "wpw1_cwa_user_master2";
	} else {
		$extMode					= 'pd';
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$studentTableName			= "wpw1_cwa_consolidated_student";
		$userTableName				= "wpw1_users";
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
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2<br />";
		}

/*	lots of good code here, but I decided we didn't need to generate the userMeta data

		
		// get all user records
		$userSQL		= "select * from $userTableName 
							order by user_login";
		$userResult		= $wpdb->get_results($userSQL);
		if ($userResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numURows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $userSQL<br />and retrieved $numURows rows<br />";
			}
			if ($numURows > 0) {
				foreach($userResult as $userRow) {
					$userID			= $userRow->ID;
					$userLogin		= strtoupper($userRow->user_login);
					$userEmail		= $userRow->user_email;

					$userFirstName		= '';
					$userLastName		= '';
					$userAddress		= '';
					$userCity			= '';
					$userState			= '';
					$userCountryCode	= '';
					$userPhone			= '';
					$userWhatsApp		= '';
					$userTelegram		= '';
					$userSignal			= '';
					$userMessenger		= '';
					$userZipCode		= '';
					$haveFirstName		= FALSE;
					$haveLastName		= FALSE;
					$haveAddress		= FALSE;
					$haveCity			= FALSE;
					$haveState			= FALSE;
					$haveCountryCode	= FALSE;
					$havePhone			= FALSE;
					$haveWhatsApp		= FALSE;
					$haveTelegram		= FALSE;
					$haveSignal			= FALSE;
					$haveMessenger		= FALSE;
					$haveZipCode		= FALSE;
					$isAdmin			= FALSE;
					$isAdvisor			= FALSE;
					$isStudent			= FALSE;
					$updateParams		= array();
					
					if ($doDebug) {
						echo "<br />Processing $userLogin at id $userID with email $userEmail<br />";
					}
					
					// get the userMeta record
					$userMetaSQL		= "select * from $userMetaTableName 
											where user_id = $userID 
											order by umeta_id";
					$userMetaResult		= $wpdb->get_results($userMetaSQL);
					if ($userMetaResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numMRows 		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $userMetaSQL<br />and retrieved $numMRows rows<br />";
						}
						if ($numMRows > 0) {
							foreach($userMetaResult as $userMetaRow) {
								$userMetaID			= $userMetaRow->umeta_id;
								$userMetaUserID		= $userMetaRow->user_id;
								$userMetaKey		= $userMetaRow->meta_key;
								$userMetaValue		= $userMetaRow->meta_value;
								
								if ($userMetaKey == 'first_name') {
									$userFirstName		= $userMetaValue;
									$haveFirstName		= TRUE;
									if ($doDebug) {
										echo "have first_name: $userFirstName<br />";
									}
								}
								if ($userMetaKey == 'last_name') {
									$userLastName		= $userMetaValue;
									$haveLastName		= TRUE;
									if ($doDebug) {
										echo "have last_name: $userLastName<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_16') {
									$userAddress		= $userMetaValue;
									$haveAddress		= TRUE;
									if ($doDebug) {
										echo "have address: $userAddress<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_18') {
									$userCity			= $userMetaValue;
									$haveCity			= TRUE;
									if ($doDebug) {
										echo "have city: $userCity<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_19') {
									$userState			= $userMetaValue;
									$haveState			= TRUE;
									if ($doDebug) {
										echo "have state: $userState<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_15') {
									$userCountryCode	= $userMetaValue;
									$haveCountryCode	= TRUE;
									if ($doDebug) {
										echo "have country code: $userCountryCode<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_14') {
									$userPhone			= $userMetaValue;
									$havePhone			= TRUE;
									if ($doDebug) {
										echo "have phone: $userPhone<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_20') {
									$userWhatsApp		= $userMetaValue;
									$haveWhatsApp		= TRUE;
									if ($doDebug) {
										echo "have whatsApp: $userWhatsApp<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_21') {
									$userTelegram		= $userMetaValue;
									$haveTelegram		= TRUE;
									if ($doDebug) {
										echo "have telegram: $userTelegram<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_22') {
									$userSignal		= $userMetaValue;
									$haveSignale		= TRUE;
									if ($doDebug) {
										echo "have signal: $userSignal<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_23') {
									$userMessenger		= $userMetaValue;
									$haveMessenger		= TRUE;
									if ($doDebug) {
										echo "have messenger: $userMessenger<br />";
									}
								}
								if ($userMetaKey == 'wpum_field_24') {
									$userZipCode		= $userMetaValue;
									$haveZipCode		= TRUE;
									if ($doDebug) {
										echo "have zipcode: $userZipCode<br />";
									}
								}
								if ($userMetaKey == 'wpw1_capabilities') {
									$userCapabilities	= $userMetaValue;
									if(preg_match("/administrator/i",$userCapabilities)) {
										$isAdmin		= TRUE;
										if ($doDebug) {
											echo "isAdmin is TRUE<br />";
										}
									} elseif(preg_match("/advisor/i",$userCapabilities)) {
										$isAdvisor		= TRUE;
										if ($doDebug) {
											echo "isAdvisor is TRUE<br />";
										}
									} elseif (preg_match("/student/i",$userCapabilities)) {
										$isStudent		= TRUE;
										if ($doDebug) {
											echo "isStudent is TRUE<br />";
										}
									}
								}
							}
							
							// get the signup record
							// if admin, look first for an advisor record, then for a student record
							if ($isAdmin) {
								$advisorSQL			= "select * from $advisorTableName 
														where call_sign = '$userLogin' 
														order by date_created DESC 
														limit 1";
								$wpw1_cwa_advisor	= $wpdb->get_results($advisorSQL);
								if ($wpw1_cwa_advisor === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$lastError			= $wpdb->last_error;
									if ($lastError != '') {
										handleWPDBError($jobname,$doDebug);
										$content		.= "Fatal program error. System Admin has been notified";
										return $content;
									}
									$numARows			= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $advisorSQL<br />and found $numARows rows in $advisorTableName table<br />";
									}
									if ($numARows > 0) {
										foreach ($wpw1_cwa_advisor as $advisorRow) {
											$advisor_ID							= $advisorRow->advisor_id;
											$advisor_select_sequence 			= $advisorRow->select_sequence;
											$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
											$advisor_first_name 				= $advisorRow->first_name;
											$advisor_last_name 					= stripslashes($advisorRow->last_name);
											$advisor_email 						= strtolower($advisorRow->email);
											$advisor_phone						= $advisorRow->phone;
											$advisor_ph_code					= $advisorRow->ph_code;				// new
											$advisor_text_message 				= $advisorRow->text_message;
											$advisor_city 						= $advisorRow->city;
											$advisor_state 						= $advisorRow->state;
											$advisor_zip_code 					= $advisorRow->zip_code;
											$advisor_country 					= $advisorRow->country;
											$advisor_country_code				= $advisorRow->country_code;		// new
											$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
											$advisor_signal						= $advisorRow->signal_app;			// new
											$advisor_telegram					= $advisorRow->telegram_app;		// new
											$advisor_messenger					= $advisorRow->messenger_app;		// new
						
											$advisor_last_name 					= no_magic_quotes($advisor_last_name);
											
											if ($haveLastName) {
												if ($advisor_last_name != $userLastName) {
													$updateParams[]		= "u&last_name&$advisor_last_name&$userMetaID";
													if ($doDebug) {
														echo "advisor_last_name of $advisor_last_name does not match userLastName of $userLastName<br />";
													}
												}
											} else {
												$userLastName			= $advisor_last_name;
												$haveLastName			= TRUE;
												$updateParams[]			= "a&last_name&advisor_last_name&0";
												if ($doDebug) {
													echo "adding last_name of $advisor_last_name<br />";
												}
											}

											if ($haveFirstName) {
												if ($advisor_first_name != $userFirstName) {
													$updateParams[]		= "u&first_name&$advisor_first_name&$userMetaID";
													if ($doDebug) {
														echo "advisor_first_name of $advisor_first_name does not match userFirstName of $userFirstName<br />";
													}
												}
											} else {
												$userFirstName			= $advisor_first_name;
												$haveFirstName			= TRUE;
												$updateParams[]			= "a&first_name&advisor_first_name&0";
												if ($doDebug) {
													echo "adding first_name of $advisor_first_name<br />";
												}
											}
											
											if ($haveCity) {
												if ($advisor_city != $userCity) {
													$updateParams[]		= "u&wpum_field_18&$advisor_city&$userMetaID";
													if ($doDebug) {
														echo "advisor_city of $advisor_city does not match userCity of $userCity<br />";
													}
												}
											} else {
												$userCity			= $advisor_city;
												$haveCity			= TRUE;
												$updateParams[]			= "a&wpum_field_18&$advisor_city&0";
												if ($doDebug) {
													echo "adding wpum_field_18 of $advisor_city<br />";
												}
											}
											
											if ($haveState) {
												if ($advisor_state != $userState) {
													$updateParams[]		= "u&wpum_field_19&$advisor_state&$userMetaID";
													if ($doDebug) {
														echo "advisor_state of $advisor_state does not match userState of $userState<br />";
													}
												}
											} else {
												$userState			= $advisor_state;
												$haveState			= TRUE;
												$updateParams[]			= "a&wpum_field_19&$advisor_state&0";
												if ($doDebug) {
													echo "adding wpum_field_19 of $advisor_state<br />";
												}
											}
											
											
											if ($haveCountryCode) {
												if ($advisor_country_code != $userCountryCode) {
													$updateParams[]		= "u&wpum_field_15&$advisor_country_code&$userMetaID";
													if ($doDebug) {
														echo "advisor_country_code of $advisor_country_code does not match userCountryCode of $userCountryCode<br />";
													}
												}
											} else {
												$userCountryCode			= $advisor_country_code;
												$haveCountryCode			= TRUE;
												$updateParams[]			= "a&wpum_field_15&$advisor_country_code&0";
												if ($doDebug) {
													echo "adding wpum_field_15 of $advisor_country_code<br />";
												}
											}
											
											if ($haveZipCode) {
												if ($advisor_zip_code != $userZipCode) {
													$updateParams[]		= "u&wpum_field_24&$advisor_zip_code&$userMetaID";
													if ($doDebug) {
														echo "advisor_zip_code of $advisor_zip_code does not match userZipCode of $userZipCode<br />";
													}
												}
											} else {
												$userZipCode			= $advisor_zip_code;
												$haveZipCode			= TRUE;
												$updateParams[]			= "a&wpum_field_24&$advisor_zip_code&0";
												if ($doDebug) {
													echo "adding wpum_field_24 of $advisor_zip_code<br />";
												}
											}
											
											if ($havePhone) {
												if ($advisor_phone != $userPhone) {
													$updateParams[]		= "u&wpum_field_14&$advisor_phone&$userMetaID";
													if ($doDebug) {
														echo "advisor_phone of $advisor_phone does not match userPhone of $userPhone<br />";
													}
												}
											} else {
												$userPhone			= $advisor_phone;
												$havePhone			= TRUE;
												$updateParams[]			= "a&wpum_field_14&$advisor_phone&0";
												if ($doDebug) {
													echo "adding wpum_field_14 of $advisor_phone<br />";
												}
											}
											
											if ($haveWhatsApp) {
												if ($advisor_whatsapp != $userWhatsApp) {
													$updateParams[]		= "u&wpum_field_20&$advisor_whatsapp&$userMetaID";
													if ($doDebug) {
														echo "advisor_whatsapp of $advisor_whatsapp does not match userWhatsApp of $userWhatsApp<br />";
													}
												}
											} else {
												$userWhatsApp			= $advisor_whatsapp;
												$haveWhatsApp			= TRUE;
												$updateParams[]			= "a&wpum_field_20&$advisor_whatsapp&0";
												if ($doDebug) {
													echo "adding wpum_field_20 of $advisor_whatsapp<br />";
												}
											}
											
											
											if ($haveTelegram) {
												if ($advisor_telegram != $userTelegram) {
													$updateParams[]		= "u&wpum_field_21&$advisor_telegram&$userMetaID";
													if ($doDebug) {
														echo "advisor_telegram of $advisor_telegram does not match userTelegram of $userTelegram<br />";
													}
												}
											} else {
												$userTelegram			= $advisor_telegram;
												$haveTelegram			= TRUE;
												$updateParams[]			= "a&wpum_field_21&$advisor_telegram&0";
												if ($doDebug) {
													echo "adding wpum_field_21 of $advisor_telegram<br />";
												}
											}
											
											if ($haveSignal) {
												if ($advisor_signal != $userSignal) {
													$updateParams[]		= "u&wpum_field_22&$advisor_signal&$userMetaID";
													if ($doDebug) {
														echo "advisor_signal of $advisor_signal does not match userSignal of $userSignal<br />";
													}
												}
											} else {
												$userSignal			= $advisor_signal;
												$haveSignal			= TRUE;
												$updateParams[]			= "a&wpum_field_22&$advisor_signal&0";
												if ($doDebug) {
													echo "adding wpum_field_22 of $advisor_signal<br />";
												}
											}
											
											if ($haveMessenger) {
												if ($advisor_messenger != $userMessenger) {
													$updateParams[]		= "u&wpum_field_23&$advisor_messenger&$userMetaID";
													if ($doDebug) {
														echo "advisor_messenger of $advisor_messenger does not match userMessenger of $userMessenger<br />";
													}
												}
											} else {
												$userMessenger			= $advisor_messenger;
												$haveMessenger			= TRUE;
												$updateParams[]			= "a&wpum_field_23&$advisor_messenger&0";
												if ($doDebug) {
													echo "adding wpum_field_23 of $advisor_messenger<br />";
												}
											}
											// all fields checked. 
											if ($doDebug) {
												echo "Checked fields for $userLogin<br /><pre>";
												print_r($updateParams);
												echo "</pre><br />";
											}
										}
									} else {				// no advisor record See if a student record
										$studentSQL			= "select * from $studentTableName 
																where call_sign = '$userLogin' 
																order by date_created DESC 
																limit 1";
										$wpw1_cwa_student		= $wpdb->get_results($studentSQL);
										if ($wpw1_cwa_student === FALSE) {
											handleWPDBError($jobname,$doDebug);
										} else {
											$lastError			= $wpdb->last_error;
											if ($lastError != '') {
												handleWPDBError($jobname,$doDebug);
												$content		.= "Fatal program error. System Admin has been notified";
												return $content;
											}
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
													$student_whatsapp						= $studentRow->whatsapp_app;
													$student_signal							= $studentRow->signal_app;
													$student_telegram						= $studentRow->telegram_app;
													$student_messenger						= $studentRow->messenger_app;					
							
													$student_last_name 						= no_magic_quotes($student_last_name);
		
													if ($haveLastName) {
														if ($student_last_name != $userLastName) {
															$updateParams[]		= "u&last_name&$student_last_name&$userMetaID";
															if ($doDebug) {
																echo "student_last_name of $student_last_name does not match userLastName of $userLastName<br />";
															}
														}
													} else {
														$userLastName			= $student_last_name;
														$haveLastName			= TRUE;
														$updateParams[]			= "a&last_name&student_last_name&0";
														if ($doDebug) {
															echo "adding last_name of $student_last_name<br />";
														}
													}
		
													if ($haveFirstName) {
														if ($student_first_name != $userFirstName) {
															$updateParams[]		= "u&first_name&$student_first_name&$userMetaID";
															if ($doDebug) {
																echo "student_first_name of $student_first_name does not match userFirstName of $userFirstName<br />";
															}
														}
													} else {
														$userFirstName			= $student_first_name;
														$haveFirstName			= TRUE;
														$updateParams[]			= "a&first_name&student_first_name&0";
														if ($doDebug) {
															echo "adding first_name of $student_first_name<br />";
														}
													}
													
													if ($haveCity) {
														if ($student_city != $userCity) {
															$updateParams[]		= "u&wpum_field_18&$student_city&$userMetaID";
															if ($doDebug) {
																echo "student_city of $student_city does not match userCity of $userCity<br />";
															}
														}
													} else {
														$userCity			= $student_city;
														$haveCity			= TRUE;
														$updateParams[]			= "a&wpum_field_18&$student_city&0";
														if ($doDebug) {
															echo "adding wpum_field_18 of $student_city<br />";
														}
													}
													
													if ($haveState) {
														if ($student_state != $userState) {
															$updateParams[]		= "u&wpum_field_19&$student_state&$userMetaID";
															if ($doDebug) {
																echo "student_state of $student_state does not match userState of $userState<br />";
															}
														}
													} else {
														$userState			= $student_state;
														$haveState			= TRUE;
														$updateParams[]			= "a&wpum_field_19&$student_state&0";
														if ($doDebug) {
															echo "adding wpum_field_19 of $student_state<br />";
														}
													}
													
													
													if ($haveCountryCode) {
														if ($student_country_code != $userCountryCode) {
															$updateParams[]		= "u&wpum_field_15&$student_country_code&$userMetaID";
															if ($doDebug) {
																echo "student_country_code of $student_country_code does not match userCountryCode of $userCountryCode<br />";
															}
														}
													} else {
														$userCountryCode			= $student_country_code;
														$haveCountryCode			= TRUE;
														$updateParams[]			= "a&wpum_field_15&$student_country_code&0";
														if ($doDebug) {
															echo "adding wpum_field_15 of $student_country_code<br />";
														}
													}
													
													if ($haveZipCode) {
														if ($student_zip_code != $userZipCode) {
															$updateParams[]		= "u&wpum_field_24&$student_zip_code&$userMetaID";
															if ($doDebug) {
																echo "student_zip_code of $student_zip_code does not match userZipCode of $userZipCode<br />";
															}
														}
													} else {
														$userZipCode			= $student_zip_code;
														$haveZipCode			= TRUE;
														$updateParams[]			= "a&wpum_field_24&$student_zip_code&0";
														if ($doDebug) {
															echo "adding wpum_field_24 of $student_zip_code<br />";
														}
													}
													
													if ($havePhone) {
														if ($student_phone != $userPhone) {
															$updateParams[]		= "u&wpum_field_14&$student_phone&$userMetaID";
															if ($doDebug) {
																echo "student_phone of $student_phone does not match userPhone of $userPhone<br />";
															}
														}
													} else {
														$userPhone			= $student_phone;
														$havePhone			= TRUE;
														$updateParams[]			= "a&wpum_field_14&$student_phone&0";
														if ($doDebug) {
															echo "adding wpum_field_14 of $student_phone<br />";
														}
													}
													
													if ($haveWhatsApp) {
														if ($student_whatsapp != $userWhatsApp) {
															$updateParams[]		= "u&wpum_field_20&$student_whatsapp&$userMetaID";
															if ($doDebug) {
																echo "student_whatsapp of $student_whatsapp does not match userWhatsApp of $userWhatsApp<br />";
															}
														}
													} else {
														$userWhatsApp			= $student_whatsapp;
														$haveWhatsApp			= TRUE;
														$updateParams[]			= "a&wpum_field_20&$student_whatsapp&0";
														if ($doDebug) {
															echo "adding wpum_field_20 of $student_whatsapp<br />";
														}
													}
													
													
													if ($haveTelegram) {
														if ($student_telegram != $userTelegram) {
															$updateParams[]		= "u&wpum_field_21&$student_telegram&$userMetaID";
															if ($doDebug) {
																echo "student_telegram of $student_telegram does not match userTelegram of $userTelegram<br />";
															}
														}
													} else {
														$userTelegram			= $student_telegram;
														$haveTelegram			= TRUE;
														$updateParams[]			= "a&wpum_field_21&$student_telegram&0";
														if ($doDebug) {
															echo "adding wpum_field_21 of $student_telegram<br />";
														}
													}
													
													if ($haveSignal) {
														if ($student_signal != $userSignal) {
															$updateParams[]		= "u&wpum_field_22&$student_signal&$userMetaID";
															if ($doDebug) {
																echo "student_signal of $student_signal does not match userSignal of $userSignal<br />";
															}
														}
													} else {
														$userSignal			= $student_signal;
														$haveSignal			= TRUE;
														$updateParams[]			= "a&wpum_field_22&$student_signal&0";
														if ($doDebug) {
															echo "adding wpum_field_22 of $student_signal<br />";
														}
													}
													
													if ($haveMessenger) {
														if ($student_messenger != $userMessenger) {
															$updateParams[]		= "u&wpum_field_23&$student_messenger&$userMetaID";
															if ($doDebug) {
																echo "student_messenger of $student_messenger does not match userMessenger of $userMessenger<br />";
															}
														}
													} else {
														$userMessenger			= $student_messenger;
														$haveMessenger			= TRUE;
														$updateParams[]			= "a&wpum_field_23&$student_messenger&0";
														if ($doDebug) {
															echo "adding wpum_field_23 of $student_messenger<br />";
														}
													}
													// all fields checked. 
													if ($doDebug) {
														echo "Checked fields for $userLogin<br /><pre>";
														print_r($updateParams);
														echo "</pre><br />";
													}
												}
											} else {				// no student record found
												if ($doDebug) {
													echo "have Admin record, but no advisor nor student record found<br />";
												}
											}
										}
									}
								}

							} elseif ($isAdvisor) {
								$advisorSQL			= "select * from $advisorTableName 
														where call_sign = '$userLogin' 
														order by date_created DESC 
														limit 1";
								$wpw1_cwa_advisor	= $wpdb->get_results($advisorSQL);
								if ($wpw1_cwa_advisor === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$lastError			= $wpdb->last_error;
									if ($lastError != '') {
										handleWPDBError($jobname,$doDebug);
										$content		.= "Fatal program error. System Admin has been notified";
										return $content;
									}
									$numARows			= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $advisorSQL<br />and found $numARows rows in $advisorTableName table<br />";
									}
									if ($numARows > 0) {
										foreach ($wpw1_cwa_advisor as $advisorRow) {
											$advisor_ID							= $advisorRow->advisor_id;
											$advisor_select_sequence 			= $advisorRow->select_sequence;
											$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
											$advisor_first_name 				= $advisorRow->first_name;
											$advisor_last_name 					= stripslashes($advisorRow->last_name);
											$advisor_email 						= strtolower($advisorRow->email);
											$advisor_phone						= $advisorRow->phone;
											$advisor_ph_code					= $advisorRow->ph_code;				// new
											$advisor_text_message 				= $advisorRow->text_message;
											$advisor_city 						= $advisorRow->city;
											$advisor_state 						= $advisorRow->state;
											$advisor_zip_code 					= $advisorRow->zip_code;
											$advisor_country 					= $advisorRow->country;
											$advisor_country_code				= $advisorRow->country_code;		// new
											$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
											$advisor_signal						= $advisorRow->signal_app;			// new
											$advisor_telegram					= $advisorRow->telegram_app;		// new
											$advisor_messenger					= $advisorRow->messenger_app;		// new
						
											$advisor_last_name 					= no_magic_quotes($advisor_last_name);
											
											if ($haveLastName) {
												if ($advisor_last_name != $userLastName) {
													$updateParams[]		= "u&last_name&$advisor_last_name&$userMetaID";
													if ($doDebug) {
														echo "advisor_last_name of $advisor_last_name does not match userLastName of $userLastName<br />";
													}
												}
											} else {
												$userLastName			= $advisor_last_name;
												$haveLastName			= TRUE;
												$updateParams[]			= "a&last_name&advisor_last_name&0";
												if ($doDebug) {
													echo "adding last_name of $advisor_last_name<br />";
												}
											}

											if ($haveFirstName) {
												if ($advisor_first_name != $userFirstName) {
													$updateParams[]		= "u&first_name&$advisor_first_name&$userMetaID";
													if ($doDebug) {
														echo "advisor_first_name of $advisor_first_name does not match userFirstName of $userFirstName<br />";
													}
												}
											} else {
												$userFirstName			= $advisor_first_name;
												$haveFirstName			= TRUE;
												$updateParams[]			= "a&first_name&advisor_first_name&0";
												if ($doDebug) {
													echo "adding first_name of $advisor_first_name<br />";
												}
											}
											
											if ($haveCity) {
												if ($advisor_city != $userCity) {
													$updateParams[]		= "u&wpum_field_18&$advisor_city&$userMetaID";
													if ($doDebug) {
														echo "advisor_city of $advisor_city does not match userCity of $userCity<br />";
													}
												}
											} else {
												$userCity			= $advisor_city;
												$haveCity			= TRUE;
												$updateParams[]			= "a&wpum_field_18&$advisor_city&0";
												if ($doDebug) {
													echo "adding wpum_field_18 of $advisor_city<br />";
												}
											}
											
											if ($haveState) {
												if ($advisor_state != $userState) {
													$updateParams[]		= "u&wpum_field_19&$advisor_state&$userMetaID";
													if ($doDebug) {
														echo "advisor_state of $advisor_state does not match userState of $userState<br />";
													}
												}
											} else {
												$userState			= $advisor_state;
												$haveState			= TRUE;
												$updateParams[]			= "a&wpum_field_19&$advisor_state&0";
												if ($doDebug) {
													echo "adding wpum_field_19 of $advisor_state<br />";
												}
											}
											
											
											if ($haveCountryCode) {
												if ($advisor_country_code != $userCountryCode) {
													$updateParams[]		= "u&wpum_field_15&$advisor_country_code&$userMetaID";
													if ($doDebug) {
														echo "advisor_country_code of $advisor_country_code does not match userCountryCode of $userCountryCode<br />";
													}
												}
											} else {
												$userCountryCode			= $advisor_country_code;
												$haveCountryCode			= TRUE;
												$updateParams[]			= "a&wpum_field_15&$advisor_country_code&0";
												if ($doDebug) {
													echo "adding wpum_field_15 of $advisor_country_code<br />";
												}
											}
											
											if ($haveZipCode) {
												if ($advisor_zip_code != $userZipCode) {
													$updateParams[]		= "u&wpum_field_24&$advisor_zip_code&$userMetaID";
													if ($doDebug) {
														echo "advisor_zip_code of $advisor_zip_code does not match userZipCode of $userZipCode<br />";
													}
												}
											} else {
												$userZipCode			= $advisor_zip_code;
												$haveZipCode			= TRUE;
												$updateParams[]			= "a&wpum_field_24&$advisor_zip_code&0";
												if ($doDebug) {
													echo "adding wpum_field_24 of $advisor_zip_code<br />";
												}
											}
											
											if ($havePhone) {
												if ($advisor_phone != $userPhone) {
													$updateParams[]		= "u&wpum_field_14&$advisor_phone&$userMetaID";
													if ($doDebug) {
														echo "advisor_phone of $advisor_phone does not match userPhone of $userPhone<br />";
													}
												}
											} else {
												$userPhone			= $advisor_phone;
												$havePhone			= TRUE;
												$updateParams[]			= "a&wpum_field_14&$advisor_phone&0";
												if ($doDebug) {
													echo "adding wpum_field_14 of $advisor_phone<br />";
												}
											}
											
											if ($haveWhatsApp) {
												if ($advisor_whatsapp != $userWhatsApp) {
													$updateParams[]		= "u&wpum_field_20&$advisor_whatsapp&$userMetaID";
													if ($doDebug) {
														echo "advisor_whatsapp of $advisor_whatsapp does not match userWhatsApp of $userWhatsApp<br />";
													}
												}
											} else {
												$userWhatsApp			= $advisor_whatsapp;
												$haveWhatsApp			= TRUE;
												$updateParams[]			= "a&wpum_field_20&$advisor_whatsapp&0";
												if ($doDebug) {
													echo "adding wpum_field_20 of $advisor_whatsapp<br />";
												}
											}
											
											
											if ($haveTelegram) {
												if ($advisor_telegram != $userTelegram) {
													$updateParams[]		= "u&wpum_field_21&$advisor_telegram&$userMetaID";
													if ($doDebug) {
														echo "advisor_telegram of $advisor_telegram does not match userTelegram of $userTelegram<br />";
													}
												}
											} else {
												$userTelegram			= $advisor_telegram;
												$haveTelegram			= TRUE;
												$updateParams[]			= "a&wpum_field_21&$advisor_telegram&0";
												if ($doDebug) {
													echo "adding wpum_field_21 of $advisor_telegram<br />";
												}
											}
											
											if ($haveSignal) {
												if ($advisor_signal != $userSignal) {
													$updateParams[]		= "u&wpum_field_22&$advisor_signal&$userMetaID";
													if ($doDebug) {
														echo "advisor_signal of $advisor_signal does not match userSignal of $userSignal<br />";
													}
												}
											} else {
												$userSignal			= $advisor_signal;
												$haveSignal			= TRUE;
												$updateParams[]			= "a&wpum_field_22&$advisor_signal&0";
												if ($doDebug) {
													echo "adding wpum_field_22 of $advisor_signal<br />";
												}
											}
											
											if ($haveMessenger) {
												if ($advisor_messenger != $userMessenger) {
													$updateParams[]		= "u&wpum_field_23&$advisor_messenger&$userMetaID";
													if ($doDebug) {
														echo "advisor_messenger of $advisor_messenger does not match userMessenger of $userMessenger<br />";
													}
												}
											} else {
												$userMessenger			= $advisor_messenger;
												$haveMessenger			= TRUE;
												$updateParams[]			= "a&wpum_field_23&$advisor_messenger&0";
												if ($doDebug) {
													echo "adding wpum_field_23 of $advisor_messenger<br />";
												}
											}
											// all fields checked. 
											if ($doDebug) {
												echo "Checked fields for $userLogin<br /><pre>";
												print_r($updateParams);
												echo "</pre><br />";
											}
											
											
										}
										
									} else {
										if ($doDebug) {
											echo "No advisor record found for $userLogin<br />";
										}
									}
								}
								
							} elseif ($isStudent) {
								$studentSQL			= "select * from $studentTableName 
														where call_sign = '$userLogin' 
														order by date_created DESC 
														limit 1";
								$wpw1_cwa_student		= $wpdb->get_results($studentSQL);
								if ($wpw1_cwa_student === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									$lastError			= $wpdb->last_error;
									if ($lastError != '') {
										handleWPDBError($jobname,$doDebug);
										$content		.= "Fatal program error. System Admin has been notified";
										return $content;
									}
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
											$student_whatsapp						= $studentRow->whatsapp_app;
											$student_signal							= $studentRow->signal_app;
											$student_telegram						= $studentRow->telegram_app;
											$student_messenger						= $studentRow->messenger_app;					
					
											$student_last_name 						= no_magic_quotes($student_last_name);

											if ($haveLastName) {
												if ($student_last_name != $userLastName) {
													$updateParams[]		= "u&last_name&$student_last_name&$userMetaID";
													if ($doDebug) {
														echo "student_last_name of $student_last_name does not match userLastName of $userLastName<br />";
													}
												}
											} else {
												$userLastName			= $student_last_name;
												$haveLastName			= TRUE;
												$updateParams[]			= "a&last_name&student_last_name&0";
												if ($doDebug) {
													echo "adding last_name of $student_last_name<br />";
												}
											}

											if ($haveFirstName) {
												if ($student_first_name != $userFirstName) {
													$updateParams[]		= "u&first_name&$student_first_name&$userMetaID";
													if ($doDebug) {
														echo "student_first_name of $student_first_name does not match userFirstName of $userFirstName<br />";
													}
												}
											} else {
												$userFirstName			= $student_first_name;
												$haveFirstName			= TRUE;
												$updateParams[]			= "a&first_name&student_first_name&0";
												if ($doDebug) {
													echo "adding first_name of $student_first_name<br />";
												}
											}
											
											if ($haveCity) {
												if ($student_city != $userCity) {
													$updateParams[]		= "u&wpum_field_18&$student_city&$userMetaID";
													if ($doDebug) {
														echo "student_city of $student_city does not match userCity of $userCity<br />";
													}
												}
											} else {
												$userCity			= $student_city;
												$haveCity			= TRUE;
												$updateParams[]			= "a&wpum_field_18&$student_city&0";
												if ($doDebug) {
													echo "adding wpum_field_18 of $student_city<br />";
												}
											}
											
											if ($haveState) {
												if ($student_state != $userState) {
													$updateParams[]		= "u&wpum_field_19&$student_state&$userMetaID";
													if ($doDebug) {
														echo "student_state of $student_state does not match userState of $userState<br />";
													}
												}
											} else {
												$userState			= $student_state;
												$haveState			= TRUE;
												$updateParams[]			= "a&wpum_field_19&$student_state&0";
												if ($doDebug) {
													echo "adding wpum_field_19 of $student_state<br />";
												}
											}
											
											
											if ($haveCountryCode) {
												if ($student_country_code != $userCountryCode) {
													$updateParams[]		= "u&wpum_field_15&$student_country_code&$userMetaID";
													if ($doDebug) {
														echo "student_country_code of $student_country_code does not match userCountryCode of $userCountryCode<br />";
													}
												}
											} else {
												$userCountryCode			= $student_country_code;
												$haveCountryCode			= TRUE;
												$updateParams[]			= "a&wpum_field_15&$student_country_code&0";
												if ($doDebug) {
													echo "adding wpum_field_15 of $student_country_code<br />";
												}
											}
											
											if ($haveZipCode) {
												if ($student_zip_code != $userZipCode) {
													$updateParams[]		= "u&wpum_field_24&$student_zip_code&$userMetaID";
													if ($doDebug) {
														echo "student_zip_code of $student_zip_code does not match userZipCode of $userZipCode<br />";
													}
												}
											} else {
												$userZipCode			= $student_zip_code;
												$haveZipCode			= TRUE;
												$updateParams[]			= "a&wpum_field_24&$student_zip_code&0";
												if ($doDebug) {
													echo "adding wpum_field_24 of $student_zip_code<br />";
												}
											}
											
											if ($havePhone) {
												if ($student_phone != $userPhone) {
													$updateParams[]		= "u&wpum_field_14&$student_phone&$userMetaID";
													if ($doDebug) {
														echo "student_phone of $student_phone does not match userPhone of $userPhone<br />";
													}
												}
											} else {
												$userPhone			= $student_phone;
												$havePhone			= TRUE;
												$updateParams[]			= "a&wpum_field_14&$student_phone&0";
												if ($doDebug) {
													echo "adding wpum_field_14 of $student_phone<br />";
												}
											}
											
											if ($haveWhatsApp) {
												if ($student_whatsapp != $userWhatsApp) {
													$updateParams[]		= "u&wpum_field_20&$student_whatsapp&$userMetaID";
													if ($doDebug) {
														echo "student_whatsapp of $student_whatsapp does not match userWhatsApp of $userWhatsApp<br />";
													}
												}
											} else {
												$userWhatsApp			= $student_whatsapp;
												$haveWhatsApp			= TRUE;
												$updateParams[]			= "a&wpum_field_20&$student_whatsapp&0";
												if ($doDebug) {
													echo "adding wpum_field_20 of $student_whatsapp<br />";
												}
											}
											
											
											if ($haveTelegram) {
												if ($student_telegram != $userTelegram) {
													$updateParams[]		= "u&wpum_field_21&$student_telegram&$userMetaID";
													if ($doDebug) {
														echo "student_telegram of $student_telegram does not match userTelegram of $userTelegram<br />";
													}
												}
											} else {
												$userTelegram			= $student_telegram;
												$haveTelegram			= TRUE;
												$updateParams[]			= "a&wpum_field_21&$student_telegram&0";
												if ($doDebug) {
													echo "adding wpum_field_21 of $student_telegram<br />";
												}
											}
											
											if ($haveSignal) {
												if ($student_signal != $userSignal) {
													$updateParams[]		= "u&wpum_field_22&$student_signal&$userMetaID";
													if ($doDebug) {
														echo "student_signal of $student_signal does not match userSignal of $userSignal<br />";
													}
												}
											} else {
												$userSignal			= $student_signal;
												$haveSignal			= TRUE;
												$updateParams[]			= "a&wpum_field_22&$student_signal&0";
												if ($doDebug) {
													echo "adding wpum_field_22 of $student_signal<br />";
												}
											}
											
											if ($haveMessenger) {
												if ($student_messenger != $userMessenger) {
													$updateParams[]		= "u&wpum_field_23&$student_messenger&$userMetaID";
													if ($doDebug) {
														echo "student_messenger of $student_messenger does not match userMessenger of $userMessenger<br />";
													}
												}
											} else {
												$userMessenger			= $student_messenger;
												$haveMessenger			= TRUE;
												$updateParams[]			= "a&wpum_field_23&$student_messenger&0";
												if ($doDebug) {
													echo "adding wpum_field_23 of $student_messenger<br />";
												}
											}
											// all fields checked. 
											if ($doDebug) {
												echo "Checked fields for $userLogin<br /><pre>";
												print_r($updateParams);
												echo "</pre><br />";
											}
										}
									}				// no student record found
								}
											
											

							} else {
								if ($doDebug) {
									echo "user role is not defined<br />";
								}
							}
							
							
							// process the updateParams, if any
							$myInt			= count($updateParams);
							if ($myInt > 0) {
								if ($doDebug) {
									echo "have $myInt updateParams to process<br />";
								}
								foreach($updateParams as $thisRow) {
									$myArray			= explode("&",$thisRow);
									$thisAction			= $myArray[0];
									$thisField			= $myArray[1];
									$thisValue			= $myArray[2];
									$thisID				= $myArray[3];
									
									if ($thisAction == 'a') {		// adding a field
										$addResult		= $wpdb->insert($userMetaTableName,
																	array('user_id'=> $userID,
																		  'meta_key'=>$thisField,
																		  'meta_value'=>$thisValue),
																	array('%d','%s','%s'));
										if ($addResult === FALSE) {
											handleWPDBError($jobname,$doDebug);
											if ($doDebug) {
												echo "adding $thisField with a value of $thisValue for user_id $userID failed<br />";
											}
										} else {
											if ($doDebug) {
												echo "successfully added $thisField with a value of $thisValue for user_id $userID<br />";
											}
										}
									} else {				/// doing an update
										$updateResult		= $wpdb->update($userMetaTableName,
																		array('meta_value'=>$thisValue),
																		array('umeta_id'=>$thisID),
																		array('%s'),
																		array('%d'));
										if ($updateResult === FALSE) {
											handleWPDBError($jobname, $doDebug);
											if ($doDebug) {
												echo "Updating $thisField to a value of $thisValue for user_id $userID failed<br />";
											}
										} else {
											if ($doDebug) {
												echo "Successfully updated $thisField to a value of $thisValue for user_id $userID<br />";
											}
										}
									}
								}
							}
							
						} else {
							if ($doDebug) {
								echo "no meta records found<br />";
							}
						}
					}
				}
			}
		}
*/		
		
		// now build the user_master table
		$content			.= "<h4>Building $userMasterTableName Table</h4>";
		if ($doDebug) {
			echo "<br /><b>Building $userMasterTableName Table</b><br />Doing Advisors<br />";
		}
		// start with the advisor records
		$sql				= "select distinct(call_sign) as callsign 
								from $advisorTableName 
								order by call_sign";
		$sqlResult			= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$thisCallSign		= $sqlRow->callsign;
					
					if ($doDebug) {
						echo "<br />processing advisor $thisCallSign<br />";
					}
					// get the latest advisor record
					$advisorSQL		= "select * from $advisorTableName 
										where call_sign = '$thisCallSign' 
										order by date_created DESC 
										limit 1";
					$advisorResult	= $wpdb->get_results($advisorSQL);
					if ($advisorResult === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$numARows	= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran $advisorSQL<br />and retrieved $numARows rows<br />";
						}
						if ($numARows > 0) {
							foreach($advisorResult as $advisorRow) {
								$advisor_ID							= $advisorRow->advisor_id;
								$advisor_select_sequence 			= $advisorRow->select_sequence;
								$advisor_call_sign 					= strtoupper($advisorRow->call_sign);
								$advisor_first_name 				= $advisorRow->first_name;
								$advisor_last_name 					= stripslashes($advisorRow->last_name);
								$advisor_email 						= strtolower($advisorRow->email);
								$advisor_phone						= $advisorRow->phone;
								$advisor_ph_code					= $advisorRow->ph_code;				// new
								$advisor_text_message 				= $advisorRow->text_message;
								$advisor_city 						= $advisorRow->city;
								$advisor_state 						= $advisorRow->state;
								$advisor_zip_code 					= $advisorRow->zip_code;
								$advisor_country 					= $advisorRow->country;
								$advisor_country_code				= $advisorRow->country_code;		// new
								$advisor_whatsapp					= $advisorRow->whatsapp_app;		// new
								$advisor_signal						= $advisorRow->signal_app;			// new
								$advisor_telegram					= $advisorRow->telegram_app;		// new
								$advisor_messenger					= $advisorRow->messenger_app;		// new
			
								$advisor_last_name 					= no_magic_quotes($advisor_last_name);
								
								// insert into user_master
								
								$advisorInsert			= $wpdb->insert($userMasterTableName,
																	array('call_sign'=>$advisor_call_sign,
																		  'first_name'=>$advisor_first_name,
																		  'last_name'=>$advisor_last_name,
																		  'email'=>$advisor_email,
																		  'phone'=>$advisor_phone,
																		  'city'=>$advisor_city,
																		  'state'=>$advisor_state,
																		  'zip_code'=>$advisor_zip_code,
																		  'country_code'=>$advisor_country_code,
																		  'whatsapp_app'=>$advisor_whatsapp,
																		  'telegram_app'=>$advisor_telegram,
																		  'signal_app'=>$advisor_signal,
																		  'messenger_app'=>$advisor_messenger ),
																	  array('%s',
																	  		'%s',
																	  		'%s',
																	  		'%s',
																	  		'%s',
																	  		'%s',
																	  		'%s',
																	  		'%s',
																	  		'%s',
																	  		'%s',
																	  		'%s',
																	  		'%s',
																	  		'%s'));
								if ($advisorInsert === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									if ($doDebug) {
										echo "user_master record added for $thisCallSign<br />";
									}
								}
							}
						}
					}
				}
			}			
		}		

		// do the students
		if ($doDebug) {
			echo "<br />Advisors Done. <b>Doing Students</b><br />";
		}
		$sql				= "select distinct(call_sign) as callsign 
								from $studentTableName 
								order by call_sign";
		$sqlResult			= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$numSRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numSRows rows<br />";
			}
			if ($numSRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$thisCallSign		= $sqlRow->callsign;
					
					if ($doDebug) {
						echo "<br />processing student $thisCallSign<br />";
					}
					// see if we've already added a record for this callsign					
					$sql			= "SELECT count(call_sign) as callsign_count 
									   from $userMasterTableName 
										where call_sign = '$thisCallSign'";
					$callsign_count	= $wpdb->get_var($sql);
					if ($callsign_count == 0) {
						if ($doDebug) {
							echo "no user_master record for $thisCallSign<br />";
						}
					
						// get the latest student record
						$studentSQL		= "select * from $studentTableName 
											where call_sign = '$thisCallSign' 
											order by date_created DESC 
											limit 1";
						$studentResult	= $wpdb->get_results($studentSQL);
						if ($studentResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$numARows	= $wpdb->num_rows;
							if ($doDebug) {
								echo "ran $studentSQL<br />and retrieved $numARows rows<br />";
							}
							if ($numARows > 0) {
								foreach($studentResult as $studentRow) {
									$student_ID							= $studentRow->student_id;
									$student_call_sign 					= strtoupper($studentRow->call_sign);
									$student_first_name 				= $studentRow->first_name;
									$student_last_name 					= stripslashes($studentRow->last_name);
									$student_email 						= strtolower($studentRow->email);
									$student_phone						= $studentRow->phone;
									$student_ph_code					= $studentRow->ph_code;				
									$student_city 						= $studentRow->city;
									$student_state 						= $studentRow->state;
									$student_zip_code 					= $studentRow->zip_code;
									$student_country 					= $studentRow->country;
									$student_country_code				= $studentRow->country_code;		
									$student_whatsapp					= $studentRow->whatsapp_app;		
									$student_signal						= $studentRow->signal_app;			
									$student_telegram					= $studentRow->telegram_app;		
									$student_messenger					= $studentRow->messenger_app;		
				
									$student_last_name 					= no_magic_quotes($student_last_name);
									
									// insert into user_master
									
									$studentInsert			= $wpdb->insert($userMasterTableName,
																		array('call_sign'=>$student_call_sign,
																			  'first_name'=>$student_first_name,
																			  'last_name'=>$student_last_name,
																			  'email'=>$student_email,
																			  'phone'=>$student_phone,
																			  'city'=>$student_city,
																			  'state'=>$student_state,
																			  'zip_code'=>$student_zip_code,
																			  'country_code'=>$student_country_code,
																			  'whatsapp_app'=>$student_whatsapp,
																			  'telegram_app'=>$student_telegram,
																			  'signal_app'=>$student_signal,
																			  'messenger_app'=>$student_messenger ),
																		  array('%s',
																				'%s',
																				'%s',
																				'%s',
																				'%s',
																				'%s',
																				'%s',
																				'%s',
																				'%s',
																				'%s',
																				'%s',
																				'%s',
																				'%s'));
									if ($studentInsert === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {
										if ($doDebug) {
											echo "user_master record added for $thisCallSign<br />";
										}
									}
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "user_master record already exists for $thisCallSign<br />";
						}
					}
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
add_shortcode ('RKSTEST_fill_profile_fields', 'RKSTEST_fill_profile_fields_func');
