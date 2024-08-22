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

		
		// now build the user_master table
		$content			.= "<h4>Building $userMasterTableName Table</h4>";
		
		// first truncate the table
		if ($doDebug) {
			echo "truncating $userMasterTableName<br />";
		}
		$deleteUsers 		= $wpdb->query("TRUNCATE TABLE $userMasterTableName");
		if ($deleteUsers === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$content		.= "$userMasterTableName table has been truncated<br />";
		}
		
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
								
								$thisDate	 	= date('Y-m-d H:i:s');
								$user_action_log	= "/ $thisDate $userName record created ";

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
																		  'messenger_app'=>$advisor_messenger,
																		  'user_action_log'=>$user_action_log ),
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
									
									$thisDate	 	= date('Y-m-d H:i:s');
									$user_action_log	= "/ $thisDate $userName record created ";
									
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
																			  'messenger_app'=>$student_messenger,
																			  'user_action_log'=>$user_action_log ),
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
