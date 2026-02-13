function RKSTEST_fill_profile_fields_func() {

/*
		meta_key		meta_value	
		nickname		g2hij				already filled
		first_name		Rolando				already filled
		last_name		Smitty				already filled
		wpum_field_20	City
		wpum_field_21	State
		wpum_field_22	Country Code
		wpum_field_23	Phone number
		wpum_field_24	WhatsApp	
		wpum_field_25	Telegram
		wpum_field_27	Signal
		wpum_field_27	Messenger
		wpum_field_28	ZipCode
		wpum_field_30	Languages
		
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
	$theURL						= "$siteURL/rkstest-fill-profile-fields/";
	$inp_semester				= '';
	$jobname					= "RKSTEST - Fill Profile Fields";
	$addedCount					= 0;

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
	
	
	$content = "";	

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
		$countryCodesTableName		= "wpw1_cwa_country_codes";
		$userMasterHistoryTableName	= "wpw1_cwa_user_master_history2";
	} else {
		$extMode					= 'pd';
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$studentTableName			= "wpw1_cwa_consolidated_student";
		$userTableName				= "wpw1_users";
		$userMetaTableName			= "wpw1_usermeta";
		$userMasterTableName		= "wpw1_cwa_user_master";
		$countryCodesTableName		= "wpw1_cwa_country_codes";
		$userMasterHistoryTableName	= "wpw1_cwa_user_master_history";
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

		$actionDate			= date('Y-m-d H:i:s');
		// now build the user_master table
		$content			.= "<h4>Building $userMasterTableName Table</h4>";
		
		// first truncate the user_master table
		if ($doDebug) {
			echo "truncating $userMasterTableName<br />";
		}
		$deleteUsers 		= $wpdb->query("TRUNCATE TABLE $userMasterTableName");
		if ($deleteUsers === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$content		.= "$userMasterTableName table has been truncated<br />";
		}
		// now truncate the user_master_history table
		if ($doDebug) {
			echo "truncating $userMasterTableName<br />";
		}
		$deleteUsers 		= $wpdb->query("TRUNCATE TABLE $userMasterHistoryTableName");
		if ($deleteUsers === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$content		.= "$userMasterHistoryTableName table has been truncated<br />";
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
				$content			.= "<p>Have $numSRows of unique advisors</p>";
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
								$advisor_time_zone 					= $advisorRow->time_zone;
								$advisor_timezone_id				= $advisorRow->timezone_id;			// new
								$advisor_timezone_offset			= $advisorRow->timezone_offset;		// new
								$advisor_semester 					= $advisorRow->semester;
								$advisor_survey_score 				= $advisorRow->survey_score;
								$advisor_languages 					= $advisorRow->languages;
								$advisor_fifo_date 					= $advisorRow->fifo_date;
								$advisor_welcome_email_date 		= $advisorRow->welcome_email_date;
								$advisor_verify_email_date 			= $advisorRow->verify_email_date;
								$advisor_verify_email_number 		= $advisorRow->verify_email_number;
								$advisor_verify_response 			= strtoupper($advisorRow->verify_response);
								$advisor_action_log 				= $advisorRow->action_log;
								$advisor_class_verified 			= $advisorRow->class_verified;
								$advisor_control_code 				= $advisorRow->control_code;
								$advisor_date_created 				= $advisorRow->date_created;
								$advisor_date_updated 				= $advisorRow->date_updated;
								$advisor_replacement_status 		= $advisorRow->replacement_status;
			
								$advisor_last_name 					= no_magic_quotes($advisor_last_name);
								
								if ($doDebug) {
									echo "got advisor info for $advisor_call_sign<br />";
								}
								
								// get the role
								$roleArray				= get_user_role($advisor_call_sign,$testMode,$doDebug);
								$result					= "";
								$reason					= "";
								$isAdvisor				= FALSE;
								$isAdmin				= FALSE;
								$isStudent				= FALSE;
								$isOther				= FALSE;
								$updateParams			= array();
								$updateFormat			= array();
								
								if ($doDebug) {
									echo "Have roleArray<br /><pre>";
									print_r($roleArray);
									echo "</pre><br />";
								}

								foreach($roleArray as $thisField => $thisValue) {
									$$thisField			= $thisValue;
								}
								if ($result === FALSE) {
									if ($doDebug) {
										echo "get_user_role returned FALSE<br />Reason: $reason<br />";
									}
//									sendErrorEmail("FUNCTION_user_master_data: get_user_role returned FALSE for $callsign");
									$is_admin						='N';
									$role							= 'student';
								} else {
									if ($isAdmin) {
										$is_admin						= 'Y';
									}
									if ($isStudent) {
										$role							= 'student';
									} 
									if ($isAdvisor) {
										$role							= 'advisor';
									} 
									if ($isOther) {
										$role							= 'other';
									} 
								}								
								
								// is the country code valid?
								
								$countrySQL		= "select * from $countryCodesTableName 
													where country_code = '$advisor_country_code'";
								$countrySQLResult	= $wpdb->get_results($countrySQL);
								if ($countrySQLResult === FALSE) {
									handleWPDBError("FUNCTION User Master Data",$doDebug);
									$country		= "UNKNOWN";
									$ph_code		= "";
								} else {
									$numCRows		= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
									}
									if($numCRows > 0) {
										foreach($countrySQLResult as $countryRow) {
											$country		= $countryRow->country_name;
											$ph_code		= $countryRow->ph_code;
											
											if ($country != $advisor_country) {
												$content	.= "<b>ERROR</b> Advisor $advisor_call_sign Country of $advisor_country doesn't match the advisor's country_code of $advisor_country_code<br />";
												$advisor_country_code 	= 'XX';
											}
											if ($advisor_ph_code == '') {
												$advisor_ph_code 	= $ph_code;
											} else {
												if ($ph_code != $advisor_ph_code) {
//													$content	.= "<b>ERROR</b> Advisor $advisor_call_sign ph_code of $advisor_ph_code doesn't match country $country<br />";
													$advisor_ph_code		= $ph_code;
												}
											}
										}
									} else {
										$content		.= "<b>ERROR</b> advisor $advisor_call_sign country code of $advisor_country_code is not in the country code table<br />";
										$advisor_country_code		= "XX";
										$advisor_ph_code			= "99";
									}
								}	
								
								$thisDate	 	= date('Y-m-d H:i:s');
								$user_action_log	= "/ $thisDate $userName record created ";

								// insert into user_master
								
								$updateParams			= array('user_call_sign'=>$advisor_call_sign,
															  'user_first_name'=>$advisor_first_name,
															  'user_last_name'=>$advisor_last_name,
															  'user_email'=>$advisor_email,
															  'user_phone'=>$advisor_phone,
															  'user_city'=>$advisor_city,
															  'user_state'=>$advisor_state,
															  'user_zip_code'=>$advisor_zip_code,
															  'user_country_code'=>$advisor_country_code,
															  'user_whatsapp'=>$advisor_whatsapp,
															  'user_telegram'=>$advisor_telegram,
															  'user_signal'=>$advisor_signal,
															  'user_messenger'=>$advisor_messenger,
															  'user_timezone_id'=>$advisor_timezone_id,
															  'user_languages'=>$advisor_languages,
															  'user_survey_score'=>$advisor_survey_score,
															  'user_action_log'=>"$actionDate $userName Create User Master - record created", 
															  'user_is_admin'=>$is_admin,
															  'user_role'=>'advisor' );
								$updateFormat			= array('%s',
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
																'%s',
																'%s',
																'%d',
																'%s',
																'%s',
																'%s');
								$userMasterData			= array('tableName'=>$userMasterTableName,
																'inp_method'=>'add',
																'inp_data'=>$updateParams,
																'inp_format'=>$updateFormat,
																'jobname'=>$jobname,
																'inp_id'=>0,
																'inp_callsign'=>$advisor_call_sign,
																'inp_who'=>$userName,
																'testMode'=>$testMode,
																'doDebug'=>$doDebug);
								$updateResult	= update_user_master($userMasterData);
								if ($updateResult === FALSE) {
									handleWPDBError($jobname,$doDebug);
								} else {
									if ($doDebug) {
										echo "user_master record added for $thisCallSign<br />";
									}
									$addedCount++;
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
				$content			.= "<p>Have $numSRows of unique students. Some are also advisors</p>";
				foreach($sqlResult as $sqlRow) {
					$thisCallSign		= $sqlRow->callsign;
					
					if ($doDebug) {
						echo "<br />processing student $thisCallSign<br />";
					}
					// see if we've already added a record for this callsign					
					$sql			= "SELECT count(user_call_sign) as callsign_count 
									   from $userMasterTableName 
										where user_call_sign = '$thisCallSign'";
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
									$student_timezone_id				= $studentRow->timezone_id;
				
									$student_last_name 					= no_magic_quotes($student_last_name);
									
									// get the role
									$roleArray				= get_user_role($advisor_call_sign);
									$result					= "";
									$reason					= "";
									$isAdvisor				= FALSE;
									$isAdmin				= FALSE;
									$isStudent				= FALSE;
									$isOther				= FALSE;
									$updateParams			= array();
									$updateFormat			= array();
									$doUpdate				= FALSE;
									foreach($roleArray as $thisField => $thisValue) {
										$$thisField			= $thisValue;
									}
									if ($result === FALSE) {
										if ($doDebug) {
											echo "get_user_role returned FALSE<br />Reason: $reason<br />";
										}
//										sendErrorEmail("FUNCTION_user_master_data: get_user_role returned FALSE for $callsign");
										$is_admin						= 'N';
										$role							= 'student';
									} else {
										if ($isAdmin) {
											$is_admin						= 'Y';
										}
										if ($isStudent) {
											$role							= 'student';
										} 
										if ($isAdvisor) {
											$role							= 'advisor';
										} 
										if ($isOther) {
											$role							= 'other';
										} 
									}
									// is the country code valid?
									
									$countrySQL		= "select * from $countryCodesTableName 
														where country_code = '$student_country_code'";
									$countrySQLResult	= $wpdb->get_results($countrySQL);
									if ($countrySQLResult === FALSE) {
										handleWPDBError("FUNCTION User Master Data",$doDebug);
										$country		= "UNKNOWN";
										$ph_code		= "";
									} else {
										$numCRows		= $wpdb->num_rows;
										if ($doDebug) {
											echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
										}
										if($numCRows > 0) {
											foreach($countrySQLResult as $countryRow) {
												$country		= $countryRow->country_name;
												$ph_code		= $countryRow->ph_code;
												
												if ($country != $student_country) {
													if ($doDebug) {
														echo "student country $student_country doesn't match the retrieved country $country<br >";
													}
													$content	.= "<b>ERROR</b> Student $student_call_sign Country of $student_country doesn't match the student's country_code of $student_country_code<br />";
													$student_country_code 	= 'XX';
												}
												if ($student_ph_code == '') {
													if ($doDebug) {
														echo "student_ph_code is empty<br />";
													}
													$student_ph_code 	= $ph_code;
													if ($doDebug) {
														echo "student_ph_code is empty. Set it to $ph_code<br />";
													}
												} else {
													if ($ph_code != $student_ph_code) {
//														$content	.= "<b>ERROR</b> Advisor $student_call_sign ph_code of $student_ph_code doesn't match country $country<br />";
														$student_ph_code		= $ph_code;
													}
												}
											}
										} else {
											$content		.= "<b>ERROR</b> Student $student_call_sign country code of $student_country_code is not in the country code table<br />";
											$student_country_code		= "XX";
											$student_ph_code			= "99";
										}
									}	


									$thisDate	 	= date('Y-m-d H:i:s');
									$user_action_log	= "/ $thisDate $userName record created ";
									
									// insert into user_master
									
									$updateParams			= array('user_call_sign'=>$student_call_sign,
																	  'user_first_name'=>$student_first_name,
																	  'user_last_name'=>$student_last_name,
																	  'user_email'=>$student_email,
																	  'user_phone'=>$student_phone,
																	  'user_city'=>$student_city,
																	  'user_state'=>$student_state,
																	  'user_zip_code'=>$student_zip_code,
																	  'user_country_code'=>$student_country_code,
																	  'user_whatsapp'=>$student_whatsapp,
																	  'user_telegram'=>$student_telegram,
																	  'user_signal'=>$student_signal,
																	  'user_messenger'=>$student_messenger,
																	  'user_timezone_id'=>$student_timezone_id,
																	  'user_action_log'=>"$actionDate $userName Create User Master - record created", 
																	  'user_is_admin'=>$is_admin,
																	  'user_role'=>'student' );
									$updateFormat			= array('%s',
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
																	'%s',
																	'%s',
																	'%s',
																	'%s');
									$userMasterData			= array('tableName'=>$userMasterTableName,
																	'inp_method'=>'add',
																	'inp_data'=>$updateParams,
																	'inp_format'=>$updateFormat,
																	'jobname'=>$jobname,
																	'inp_id'=>0,
																	'inp_callsign'=>$student_call_sign,
																	'inp_who'=>$userName,
																	'testMode'=>$testMode,
																	'doDebug'=>$doDebug);
									$updateResult	= update_user_master($userMasterData);
									if ($updateResult === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {
										if ($doDebug) {
											echo "user_master record added for $thisCallSign<br />";
										}
										$addedCount++;
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
		$content			.= "<p>$addedCount records added to user_master table</p>";
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

