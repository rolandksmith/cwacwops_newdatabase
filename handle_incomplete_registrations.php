function handle_incomplete_registrations_func() {

/*

run (sql1) select * from wpw1_cwa_temp_data where token = 'tracking' and temp_data = 'NYYYNN' order by callsign

For each of the returns
	run (sql2) select * from wpw1_cwa_temp_data where callsign = 'xxx' and token = 'tracking' order by date_written DESC limit 1
	
	If temp_data = 'NYYYNN' 
	run (sql3) select * from wpw1_cwa_temp_data where callsign = 'xxx' and token = 'register' and temp_data = 'student'
	
	if there is a record and 
	if date_written older than 10 days, 
	run (sql4) select * from wpw1_cwa_temp_data where callsign = 'xxx' and token = 'reminder'
	
	if no record found
	run (sql5) select * from wpw1_users where user_login like 'xxx'
	
	get the id and user_email
	run (sql6) select * from wpw1_usermeta where user_id = (id)
	
	get the first_name and last_name
	
	display user_login, user_email, last_name, first_name
	
	(sql7) insert callsign = 'xxx' and token = 'reminder' and date_written into wpw1_cwa_temp_data
	
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

//	if (!in_array($userName,$validTestmode) && $doDebug) {	// turn off doDebug if not a testmode user
//		$doDebug			= FALSE;
//		$testMode			= FALSE;
//	}

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
	$theURL						= "$siteURL/cwa-handle-incomplete-registrations/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Handle Incomplete Registrations V$versionNumber";
	$currentDate				= date('Y-m-d H:i:s');
	$outCount					= 0;

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
		$tempDataTableName			= "wpw1_cwa_temp_data2";
		$usersTableName				= 'wpw1_users';
		$userMetaTableName			= 'wpw1_usermeta';
	} else {
		$extMode					= 'pd';
		$tempDataTableName			= "wpw1_cwa_temp_data";
		$usersTableName				= 'wpw1_users';
		$userMetaTableName			= 'wpw1_usermeta';
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 2<br />";
		}

		$content		.= "<h3>$jobname</h3><pre>";
		
		$sql1			= "select * from wpw1_cwa_temp_data where token = 'tracking' and temp_data = 'NYYYNN' order by callsign";
		$readSQL1		= $wpdb->get_results($sql1);
		if ($readSQL1 === FALSE) {
			handleWPDBError($jobname,$doDebug);
		} else {
			$num1Rows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran sql1: $sql1<br />and retrieved $num1Rows rows<br />";
			}
			if ($num1Rows > 0) {
				foreach ($readSQL1 as $readSQL1Row) {
					$sql1Callsign		= $readSQL1Row->callsign;
					if ($doDebug) {
						echo "<br />have callsign $sql1Callsign with temp_data of NYYYNN<br />";
					}
					$sql2		= "select * from wpw1_cwa_temp_data where callsign = '$sql1Callsign' and token = 'tracking' order by date_written DESC limit 1";
					$sql2Result	= $wpdb->get_results($sql2);
					if ($sql2Result === FALSE) {
						handleWPDBError($jobname,$doDebug);
					} else {
						$num2Rows		= $wpdb->num_rows;
						if ($doDebug) {
							echo "ran sql2: $sql2<br />and retrieved $num2Rows rows<br />";
						}
						if ($num2Rows > 0) {
							foreach ($sql2Result as $sql2ResultRow) {
								$sql2Temp_data		= $sql2ResultRow->temp_data;
								if ($doDebug) {
									echo "have a temp_data of $sql2Temp_data<br />";
								}
								if ($sql2Temp_data == 'NYYYNN') {
									$sql3		= "select * from wpw1_cwa_temp_data where callsign = '$sql1Callsign' and token = 'register' and temp_data = 'student'";
									$sql3Result	= $wpdb->get_results($sql3);
									if ($sql3Result === FALSE) {
										handleWPDBError($jobname,$doDebug);
									} else {
										$num3Rows		= $wpdb->num_rows;
										if ($doDebug) {
											echo "ran sql3: $sql3<br />and retrieved $num3Rows rows<br />";
										}
										if ($num3Rows > 0) {
											foreach ($sql3Result as $sql3ResultRow) {
												$sql3Date_written		= $sql3ResultRow->date_written;
												
												$futureDate = date('Y-m-d H:i:s', strtotime($sql3Date_written . ' +10 days'));
												if ($currentDate > $futureDate) {
													if ($doDebug) {
														echo "date_written of $sql3Date_written more than 10 days old<br />";
													}
													$sql4			= "select * from wpw1_cwa_temp_data where callsign = '$sql1Callsign' and token = 'reminder'";
													$sql4Result		= $wpdb->get_results($sql4);
													if ($sql4Result === FALSE) {
														handleWPDBError($jobname,$doDebug);
													} else {
														$num4Rows	= $wpdb->num_rows;
														if ($doDebug) {
															echo "ran sql4: $sql4<br />and retrieved $num4Rows rows<br />";
														}
														if ($num4Rows > 0) {
															if ($doDebug) {
																echo "reminder set. No further action<br />";
															}
														} else {
															if ($doDebug) {
																echo "no reminder set<br />";
															}
															$sql5		= "select * from wpw1_users where user_login like '$sql1Callsign'";
															$sql5Result	= $wpdb->get_results($sql5);
															if ($sql5Result === FALSE) {
																handleWPDBError($jobname,$doDebug);
															} else {
																$num5Rows		= $wpdb->num_rows;
																if ($doDebug) {
																	echo "ran sql5: $sql5<br />and retrieved $num5Rows rows<br />";
																}
																if ($num5Rows > 0) {
																	foreach($sql5Result as $sql5ResultRow) {
																		$sql5ID			= $sql5ResultRow->ID;
																		$sql5Email		= $sql5ResultRow->user_email;
																		
																		if ($doDebug) {
																			echo "have ID of $sql5ID and email of $sql5Email for $sql1Callsign<br />";
																		}
																		
																		$sql6			= "select * from wpw1_usermeta where user_id = $sql5ID and meta_key = 'last_name'";
																		$sql6Result		= $wpdb->get_results($sql6);
																		if ($sql6Result === FALSE) {
																			handleWPDBError($jobname,$doDebug);
																		} else {
																			$num6Rows	= $wpdb->num_rows;
																			if ($doDebug) {
																				echo "ran sql6: $sql6<br >and retrieved $num6Rows rows<br />";
																			}
																			if ($num6Rows > 0) {
																				foreach ($sql6Result as $sql6ResultRow) {
																					$sql6Last_name		= $sql6ResultRow->meta_value;
																				}
																			} else {
																				$sql6Last_name			= '';
																				if ($doDebug) {
																					echo "no last_name record found for $sql1Callsign<br />";
																				}
																			}
																			$sql6a			= "select * from wpw1_usermeta where user_id = $sql5ID and meta_key = 'first_name'";
																			$sql6aResult	= $wpdb->get_results($sql6a);
																			if ($sql6aResult === FALSE) {
																				handleWPDBError($jobname,$doDebug);
																			} else {
																				$num6aRows	= $wpdb->num_rows;
																				if ($doDebug) {
																					echo "ran sql6a: $sql6a<br />and retrieved $num6aRows rows<br />";
																				}
																				if ($num6aRows > 0) {
																					foreach ($sql6aResult as $sql6aResultRow) {
																						$sql6aFirst_name		= $sql6aResultRow->meta_value;
																					}
																				} else {
																					if ($doDebug) {
																						echo "no first_name record found for $sql1Callsign<br />";
																					}
																					$sql6aFirst_name				= '';
																				}
																			}
																		}
																		/// have all the data
																		$content		.= "$sql1Callsign\t$sql5Email\t$sql6Last_name\t$sql6aFirst_name\n";
																		$outCount++;
																		
																		// insert the reminder record
																		$thisDate		= date('Y-m-d H:i:s');
																		$sql7Result		= $wpdb->insert($tempDataTableName,
																										array('callsign'=>$sql1Callsign,
																											  'token'=>'reminder',
																											  'temp_data'=>'reminder sent',
																											  'date_written'=>$thisDate),
																										array('%s','%s','%s','%s'));
																		if ($sql7Result === FALSE) {
																			handleWPDBError($jobname,$doDebug);
																		}
																	}
																} else {
																	$content		.= "ERROR! No user-login found for $sql1Callsign<br />";
																	if ($doDebug) {
																		echo "no wpw1_users record found for $sql1Callsign<br />";
																	}
																}
															}
														}
													}
													
													
												} else {
													if ($doDebug) {
														echo "date_written or $sql3Date_written less than 10 days ago<br />";
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			} else {
				$content	.= "No data found matching $sql1<br />";
			}
		}
		$content			.= "</pre><p>$outCount records printed<br />";
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
add_shortcode ('handle_incomplete_registrations', 'handle_incomplete_registrations_func');
