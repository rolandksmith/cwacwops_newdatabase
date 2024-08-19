function update_callsign_func() {

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
//	if ($validUser == "N") {
//		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
//	}

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
	$theURL						= "$siteURL/cwa-update-callsign/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Update Callsign V$versionNumber";

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
			if ($str_key 		== "inp_old_callsign") {
				$inp_old_callsign	 = strtoupper($str_value);
				$inp_old_callsign	 = filter_var($inp_old_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_new_callsign") {
				$inp_new_callsign	 = strtoupper($str_value);
				$inp_new_callsign	 = filter_var($inp_new_callsign,FILTER_UNSAFE_RAW);
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
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass2";
		$studentTableName			= "wpw1_cwa_consolidated_student2";
		$audioAssessmentTableName 	= "wpw1_cwa_audio_assessment2";
		$newAssessmentTableName 	= "wpw1_cwa_new_assessment_data2";
		$tempDataTableName			= "wpw1_cwa_temp_data2";
		$userTableName				= " wpw1_users";
		
		
		
	} else {
		$extMode					= 'pd';
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$advisorClassTableName		= "wpw1_cwa_consolidated_advisorclass";
		$studentTableName			= "wpw1_cwa_consolidated_student";
		$audioAssessmentTableName 	= "wpw1_cwa_audio_assessment";
		$newAssessmentTableName 	= "wpw1_cwa_new_assessment_data";
		$tempDataTableName			= "wpw1_cwa_temp_data";
		$userTableName				= " wpw1_users";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass 1<br />";
		}
		
		if ($userRole == 'administrator') {
			$currentCallSign	= "<td><input class='formInputText' type='text' size= '30' maxlength='30' name='inp_old_callsign' autofocus></td></tr>";
			$hiddenCallSign		= '';
		} else {
			$currentCallSign	= "<td>$userName</td></tr>";
			$hiddenCallSign		= "<input type='hidden' name='inp_old_callsign' value='$userName'>";
		}
		$content .= "<h3>$jobname</h3>
					<p>Enter the current call sign and the new call sign.</p>
					<form method='post' action='$theURL' 
					name='selection_form' ENCTYPE='multipart/form-data'>
					<input type='hidden' name='strpass' value='2'>
					$hiddenCallSign
					<table style='border-collapse:collapse;'>
					<tr><td style='width:150px;'>Current Call Sign:</td>
						$currentCallSign
					<tr><td style='width:150px;'>New Call Sign:</td>
						<td><input class='formInputText' type='text' size= '30' maxlength='30' name='inp_new_callsign'></td></tr>
					$testModeOption
					<tr><td>&nbsp;</td>
						<td><input class='formInputButton' type='submit' value='Change Call Sign' /></td></tr>
					</table>
					</form>";

	} elseif ("2" == $strPass) {				// make the call sign change
		if ($doDebug) {
			echo "At pass 2 with previous call sign $inp_old_callsign and new call sign $inp_new_callsign<br />";
		}
		$thisActionDate			= date('Y-m-e H:i');
		$content				.= "<h3>$jobname From $inp_old_callsign to $inp_new_callsign</h3>";
		$doContinue				= TRUE;
		if ($inp_old_callsign == '' || $inp_new_callsign == '') {
			$content			.= "Either the previous call sign or the new call sign missing";
			$doContinue			= FALSE;
		} elseif ($inp_old_callsign == $inp_new_callsign) {
			$content			.= "New callsign must be different than old callsign";
			$doContinue			= FALSE;
		}
		if ($userRole == 'administrator' && $inp_old_callsign == $userName) {
			$content			.= "As an administrator you can't change your own callsign";
			$doContinue			= FALSE;
		}
		if ($doContinue) {
		
			/// See if there are any advisor records with the old callsign
			if ($doDebug) {
				echo "<br />Checking $advisorTableName<br />";
			}
			$sql				= "select * from $advisorTableName 
									where call_sign = '$inp_old_callsign' 
									order by date_created";
			$wpw1_cwa_advisor	= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisor === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numARows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numARows rows in $advisorTableName table<br />";
				}
				if ($numARows > 0) {
					$rowsUpdated				= 0;
					foreach ($wpw1_cwa_advisor as $advisorRow) {
						$advisor_ID				= $advisorRow->advisor_id;
						$advisor_call_sign 		= strtoupper($advisorRow->call_sign);
						$advisor_action_log 	= $advisorRow->action_log;
						
						$advisor_action_log		.= " /$thisActionDate $userName callsign changed from 
$inp_old_callsign to $inp_new_callsign ";
						
						$advisorUpdate			= $wpdb->update($advisorTableName,
																array('call_sign'=>$inp_new_callsign,
																	  'action_log'=>$advisor_action_log),
																array('advisor_id'=>$advisor_ID),
																array('%s','%s'),
																array('%d'));
						if ($advisorUpdate === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$rowsUpdated++;
							if ($doDebug) {
								echo "updated record $advisor_ID<br />";
							}
						}
					}
					$content			.= "<b>$advisorTableName: </b>
											$rowsUpdated out of $numARows updated<br />";
					if ($doDebug) {
						echo "updated $rowsUpdated rows in $advisorTableName<br />";
					}
				}
			}	
		
			// advisorClass callsign records
			if ($doDebug) {
				echo "<br />Checking $advisorClassTableName<br />";
			}
			$sql				= "select * from $advisorClassTableName 
									where advisor_call_sign = '$inp_old_callsign' 
									order by date_created";
			$wpw1_cwa_advisorclass				= $wpdb->get_results($sql);
			if ($wpw1_cwa_advisorclass === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numACRows						= $wpdb->num_rows;
				if ($doDebug) {
					$myStr						= $wpdb->last_query;
					echo "ran $myStr<br />and found $numACRows rows<br />";
				}
				if ($numACRows > 0) {
					$rowsUpdated				= 0;
					foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
						$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
						$advisorClass_advisor_call_sign 		= $advisorClassRow->advisor_call_sign;
						$advisorClass_action_log 				= $advisorClassRow->action_log;

						$advisorClass_action_log		.= " /$thisActionDate $userName advisor call sign 
changed from $inp_old_callsign to $inp_new_callsign ";

						$advisorClass_update			= $wpdb->update($advisorClassTableName,
																		array('advisor_call_sign'=>$inp_new_callsign,
																			  'action_log'=>$advisorClass_action_log),
																		array('advisorclass_id'=>$advisorClass_ID),
																		array('%s','%s'),
																		array('%d'));
						if ($advisorClass_update === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$rowsUpdated++;
							if ($doDebug) {
								echo "Updated record $advisorClass_ID<br />";
							}
						}
					}
					$content			.= "$rowsUpdated rows of $numACRows updated in $advisorClassTableName<br />";
					if ($doDebug) {
						echo "Updated $rowsUpdated in $advisorClassTableName<br />";
					}
				}
			}
			
			// update assigned advisors in $studentTableName
			if ($doDebug) {
				echo "<br />Checking assigned advisors in $studentTableName<br />";
			}
			
			$sql				= "select * from $studentTableName 
									where assigned_advisor = '$inp_old_callsign' 
									order by date_created";

			$wpw1_cwa_student		= $wpdb->get_results($sql);
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
					$rowsUpdated			= 0;
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_action_log  					= $studentRow->action_log;
						$student_assigned_advisor  				= $studentRow->assigned_advisor;

						$student_action_log			.= " /$thisActionDate $userName assigned advisor callsign 
changed from $inp_old_callsign to $inp_new_callsign ";

						$studentUpdated				= $wpdb->update($studentTableName,
																	array('assigned_advisor'=>$inp_new_callsign,
																		  'action_log'=>$student_action_log), 
																	array('student_id'=>$student_ID), 
																	array('%s','%s'),
																	array('%d'));
						if ($studentUpdated === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$rowsUpdated++;
							if ($doDebug) {
								echo "updated row $student_ID<br />";
							}
						}
					}
					$content			.= "$rowsUpdated rows out of $numSRows assigned Advisor rows updated in $studentTableName<br />";
					if ($doDebug) {
						echo "$rowsUpdated assigned advisor rows updated in $studentTableName<br />";
					}
				}
			}
			
			
			// Fix up pre-assigned advisors
			if ($doDebug) {
				echo "<br />Checking pre-assigned advisors in $studentTableName<br />";
			}			
			$sql				= "select * from $studentTableName 
									where pre_assigned_advisor = '$inp_old_callsign' 
									order by date_created";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					$rowsUpdated			= 0;
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_action_log  					= $studentRow->action_log;
						$student_pre_assigned_advisor  			= $studentRow->pre_assigned_advisor;
									
						$student_action_log			.= " /$thisActionDate $userName  pre-assigned advisor callsign 
changed from $inp_old_callsign to $inp_new_callsign ";
	
						$studentUpdated				= $wpdb->update($studentTableName,
																	array('pre_assigned_advisor'=>$inp_new_callsign,
																		  'action_log'=>$student_action_log), 
																	array('student_id'=>$student_ID), 
																	array('%s','%s'),
																	array('%d'));
						if ($studentUpdated === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$rowsUpdated++;
							if ($doDebug) {
								echo "updated row $student_ID<br />";
							}
						}
					}
					$content			.= "$rowsUpdated Rows out of $numSRows assigned Advisor rows updated in $studentTableName<br />";
					if ($doDebug) {
						echo "$rowsUpdated pre_assigned advisor rows updated in $studentTableName<br />";
					}
				}
			}
			

			// Fix up excluded advisors
			if ($doDebug) {
				echo "<br />Checking excluded advisors in $studentTableName<br />";
			}			
			$sql				= "select * from $studentTableName 
									where excluded_advisor like '%$inp_old_callsign%' 
									order by date_created";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					$myStr			= $wpdb->last_query;
					echo "ran $myStr<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					$rowsUpdated			= 0;
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_action_log  					= $studentRow->action_log;
						$student_excluded_advisor  				= $studentRow->excluded_advisor;
									
						$student_action_log			.= " /$thisActionDate $userName excluded advisor callsign 
changed from $inp_old_callsign to $inp_new_callsign ";

						$student_excluded_advisor	= str_replace($inp_old_callsign,$inp_new_callsign,$student_excluded_advisor);
	
						$studentUpdated				= $wpdb->update($studentTableName,
																	array('excluded_advisor'=>$student_excluded_advisor,
																		  'action_log'=>$student_action_log), 
																	array('student_id'=>$student_ID), 
																	array('%s','%s'),
																	array('%d'));
						if ($studentUpdated === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$rowsUpdated++;
							if ($doDebug) {
								echo "updated row $student_ID<br />";
							}
						}
					}
					$content			.= "$rowsUpdated Rows out of $numSRows excluded Advisor rows updated in $studentTableName<br />";
					if ($doDebug) {
						echo "$rowsUpdated pre_assigened advisor rows updated in $studentTableName<br />";
					}
				}
			}

									
		
			//// check studentTableName 
			if ($doDebug) {
				echo "<br />Checking $studentTableName student callsigns<br />";
			}
			$sql				= "select *  
									from $studentTableName 
									where call_sign='$inp_old_callsign' 
									order by date_created";
			$wpw1_cwa_student	= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError("$jobname MGMT 96",$doDebug);
			} else {
				$numSRows		= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numSRows rows from $studentTableName table<br />";
				}
				if ($numSRows > 0) {
					$rowsUpdated			= 0;
					$advisorClassRecords	= 0;
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_call_sign						= strtoupper($studentRow->call_sign);
						$student_action_log						= $studentRow->action_log;
						$student_assigned_advisor				= $studentRow->assigned_advisor;
						$student_assigned_advisor_class			= $studentRow->assigned_advisor_class;
						$student_semester						= $studentRow->semester;

						/// make the change
						$student_action_log			.= " /$thisActionDate $userName 
changed student callsign from $inp_old_callsign to $inp_new_callsign ";

						$studentUpdate			= $wpdb->update($studentTableName,
															array('call_sign' => $inp_new_callsign,
																  'action_log' => $student_action_log),
															array('student_id'=>$student_ID),
															array('%s','%s'),
															array('%d'));
						if ($studentUpdate === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							$rowsUpdated++;
							if ($doDebug) {
								echo "row $student_ID updated<br />";
							}
						}
						
						// if assigned_advisor then update the class record
						if ($student_assigned_advisor != '') {
							if ($doDebug) {
								echo "fixing up $student_assigned_advisor class record<br />";
							}

							$sql						= "select * from $advisorClassTableName 
															where advisor_call_sign='$student_assigned_advisor' 
																and sequence=$student_assigned_advisor_class 
																and semester='$student_semester'";
							$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
							if ($wpw1_cwa_advisorclass === FALSE) {
								handleWPDBError("$jobname MGMT 96",$doDebug);
							} else {
								$numACRows				= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $sql<br />and found $numACRows rows in $advisorClassTableName table<br />";
								}
								if ($numACRows > 0) {
									foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
										$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
										$advisorClass_advisor_callsign 		= $advisorClassRow->advisor_call_sign;
										$advisorClass_advisor_first_name 		= $advisorClassRow->advisor_first_name;
										$advisorClass_advisor_last_name 		= stripslashes($advisorClassRow->advisor_last_name);
										$advisorClass_advisor_id 				= $advisorClassRow->advisor_id;
										$advisorClass_sequence 				= $advisorClassRow->sequence;
										$advisorClass_semester 				= $advisorClassRow->semester;
										$advisorClass_timezone 				= $advisorClassRow->time_zone;
										$advisorClass_timezone_id				= $advisorClassRow->timezone_id;		// new
										$advisorClass_timezone_offset			= $advisorClassRow->timezone_offset;	// new
										$advisorClass_level 					= $advisorClassRow->level;
										$advisorClass_class_size 				= $advisorClassRow->class_size;
										$advisorClass_class_schedule_days 		= $advisorClassRow->class_schedule_days;
										$advisorClass_class_schedule_times 	= $advisorClassRow->class_schedule_times;
										$advisorClass_class_schedule_days_utc 	= $advisorClassRow->class_schedule_days_utc;
										$advisorClass_class_schedule_times_utc	= $advisorClassRow->class_schedule_times_utc;
										$advisorClass_action_log 				= $advisorClassRow->action_log;
										$advisorClass_class_incomplete 		= $advisorClassRow->class_incomplete;
										$advisorClass_date_created				= $advisorClassRow->date_created;
										$advisorClass_date_updated				= $advisorClassRow->date_updated;
										$advisorClass_student01 				= $advisorClassRow->student01;
										$advisorClass_student02 				= $advisorClassRow->student02;
										$advisorClass_student03 				= $advisorClassRow->student03;
										$advisorClass_student04 				= $advisorClassRow->student04;
										$advisorClass_student05 				= $advisorClassRow->student05;
										$advisorClass_student06 				= $advisorClassRow->student06;
										$advisorClass_student07 				= $advisorClassRow->student07;
										$advisorClass_student08 				= $advisorClassRow->student08;
										$advisorClass_student09 				= $advisorClassRow->student09;
										$advisorClass_student10 				= $advisorClassRow->student10;
										$advisorClass_student11 				= $advisorClassRow->student11;
										$advisorClass_student12 				= $advisorClassRow->student12;
										$advisorClass_student13 				= $advisorClassRow->student13;
										$advisorClass_student14 				= $advisorClassRow->student14;
										$advisorClass_student15 				= $advisorClassRow->student15;
										$advisorClass_student16 				= $advisorClassRow->student16;
										$advisorClass_student17 				= $advisorClassRow->student17;
										$advisorClass_student18 				= $advisorClassRow->student18;
										$advisorClass_student19 				= $advisorClassRow->student19;
										$advisorClass_student20 				= $advisorClassRow->student20;
										$advisorClass_student21 				= $advisorClassRow->student21;
										$advisorClass_student22 				= $advisorClassRow->student22;
										$advisorClass_student23 				= $advisorClassRow->student23;
										$advisorClass_student24 				= $advisorClassRow->student24;
										$advisorClass_student25 				= $advisorClassRow->student25;
										$advisorClass_student26 				= $advisorClassRow->student26;
										$advisorClass_student27 				= $advisorClassRow->student27;
										$advisorClass_student28 				= $advisorClassRow->student28;
										$advisorClass_student29 				= $advisorClassRow->student29;
										$advisorClass_student30 				= $advisorClassRow->student30;
										$class_number_students					= $advisorClassRow->number_students;
										$class_evaluation_complete 				= $advisorClassRow->evaluation_complete;
										$class_comments							= $advisorClassRow->class_comments;
										$copycontrol							= $advisorClassRow->copy_control;
		
										$advisorClass_advisor_last_name  		= no_magic_quotes($advisorClass_advisor_last_name);
		
										for ($snum=1;$snum<31;$snum++) {
											if ($snum < 10) {
												$strSnum 						= str_pad($snum,2,'0',STR_PAD_LEFT);
											} else {
												$strSnum						= strval($snum);
											}
											$studentCallSign					= ${'advisorClass_student' . $strSnum};
											if ($studentCallSign == $inp_old_callsign) { 		// if so, change the callsign
												$advisorClass_action_log	= "$advisorClass_action_log / $actionDate $userName MGNT95 
student$strSnum call sign changed from $inp_old_callsign to $inp_new_callsign ";
												$updateArray["student$strSnum"]	= $inp_new_callsign;
												$updateArray["action_log"]		= $advisorClass_action_log;
												$classUpdateData		= array('tableName'=>$advisorClassTableName,
																				'inp_method'=>'update',
																				'inp_data'=>$updateArray,
																				'inp_format'=>array('%s','%s'),
																				'jobname'=>'MGMT95',
																				'inp_id'=>$advisorClass_ID,
																				'inp_callsign'=>$advisorClass_advisor_callsign,
																				'inp_semester'=>$advisorClass_semester,
																				'inp_who'=>$userName,
																				'testMode'=>$testMode,
																				'doDebug'=>$doDebug);
												$updateResult	= updateClass($classUpdateData);
												if ($updateResult[0] === FALSE) {
													handleWPDBError("$jobname MGMT 96",$doDebug);
												} else {
													if ($doDebug) {
														echo "Successfully updated $past_student_call_sign record at $past_student_ID<br />";
													}
													$advisorClassRecords++;
												}
											}
										}
									}
								}
							}
						} else {
							if ($doDebug) {
								echo "no assigned advisor<br />";
							}
						}
						
					}
					$content		.= "$rowsUpdated rows out of $numSRows updated in $studentTableName<br />
										$advisorClassRecords advisorClass records updated<br />";
				}
			}
			
			// update any audio assessment records
			if ($doDebug) {
				echo "<br />Checking $audioAssessmentTableName<br />";
			}
			$sql			= "select * from $audioAssessmentTableName 
								where call_sign = '$inp_old_callsign'";
			$audioResult	= $wpdb->get_results($sql);
			if ($audioResult === FALSE) {
				handleWPDBError("$jobname MGMT 96",$doDebug);
			} else {
				$numRows				= $wpdb->num_rows;
				if ($doDebug) {
					echo "Ran $sql<br />and retrieved $numRows records from $audioAssessmentTableName<br />";
				}
				if ($numRows > 0) {
					$rowsUpdated			=0;
					foreach ($audioResult as $audioRow) {
						$assessment_record_id				= $audioRow->record_id;
						$assessment_call_sign				= $audioRow->call_sign;
						$assessment_assessment_date			= $audioRow->assessment_date;
						$assessment_assessment_level		= $audioRow->assessment_level;
						$assessment_assessment_clip			= $audioRow->assessment_clip;
						$assessment_assessment_score		= $audioRow->assessment_score;
						$assessment_assessment_clip_name	= $audioRow->assessment_clip_name;
						$assessment_assessment_notes		= $audioRow->assessment_notes;
						$assessment_assessment_program		= $audioRow->assessment_program;

						$assessmentResult					= $wpdb->update($audioAssessmentTableName,
																			array('call_sign'=>$inp_new_callsign),
																			array('record_id'=>$assessment_record_id),
																			array('%s'),
																			array('%d'));
						if ($assessmentResult === FALSE) {
							handleWPDBError("$jobname MGMT 96",$doDebug);
						} else {
							if ($doDebug) {
								echo "row $assessment_record_id updated<br />";
							}
							$rowsUpdated++;

						}
					}
					$content		.= "$rowsUpdated rows out of $numRows updated in $audioAssessmentTableName<br />";
				}
			}
			// update any new assessment data records
			if ($doDebug) {
				echo "<br />Checking $newAssessmentTableName<br />";
			}
			$sql 				= "select * from $newAssessmentTableName 
									where callsign = '$inp_old_callsign'";
			$assessmentResult	= $wpdb->get_results($sql);
			if ($assessmentResult === FALSE) {
				handleWPDBError("$jobname pass 96",$doDebug);
			} else {
				$numRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					$rowsUpdated		= 0;
					foreach($assessmentResult as $assessmentRow) {
						$record_id		= $assessmentRow->record_id;
						$thisCallsign	= $assessmentRow->callsign;
						
						$updateResult	= $wpdb->update($newAssessmentTableName,
												array('callsign'=>$inp_new_callsign),
												array('record_id'=>$record_id),
												array("%s"),
												array('%d'));
						if ($updateResult === FALSE) {
							handleWPDBError("$jobname pass 96",$doDebug);
						} else {
							if ($doDebug) {
								echo "row $record_id updated<br />";
							}
							$rowsUpdated++;
						}
					}
					$content		.= "$rowsUpdated rows out of $numRows updated in $newAssessmentTableName<br />";
					if ($doDebug) {
						echo "$rowsUpdated rows updated in $newAssessmentTableName<br />";
					}
				}
			}
			
			// fix up temp_data
			if ($doDebug) {
				echo "<br />Checking $tempDataTableName<br />";
			}
			$sql 				= "select * from $tempDataTableName 
									where callsign = '$inp_old_callsign'";
			$tempResult			= $wpdb->get_results($sql);
			if ($tempResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					$rowsUpdated		= 0;
					foreach($tempResult as $tempRow) {
						$record_id		= $tempRow->record_id;
						$thisCallsign	= $tempRow->callsign;
						
						$updateResult	= $wpdb->update($tempDataTableName,
												array('callsign'=>$inp_new_callsign),
												array('record_id'=>$record_id),
												array('%s'),
												array('%d'));
						if ($updateResult === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							if ($doDebug) {
								echo "row $record_id updated<br />";
							}
							$rowsUpdated++;
						}
					}
					$content		.= "$rowsUpdated rows out of $numRows updated in $tempDataTableName<br />";
				}
			}
			// now change the user login
			if ($doDebug) {
				echo "<br />Checking user_login<br />";
			}
			$sql			= "select * from $userTableName 
								where user_login like '$inp_old_callsign'";
			$userResult		= $wpdb->get_results($sql);
			if ($userResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numURows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numURows rows<br />";
				}
				if ($numURows > 0) {
					$rowsUpdated		= 0;
					foreach($userResult as $userResultRow) {
						$thisID			= $userResultRow->ID;
						$thisLogin		= $userResultRow->user_login;
						
						$userUpdateSQL	= "update $userTableName 
											set user_login = '$inp_new_callsign' 
											where ID = $thisID";
						$userUpdate		= $wpdb->get_results($userUpdateSQL);
						if ($userUpdate === FALSE) {
							handleWPDBError($jobname,$doDebug);
						} else {
							if ($doDebug) {
								echo "row $thisID updated<br />";
							}
							$rowsUpdated++;
						}
					}
					$content			.= "$rowsUpdated rows out of $numURows updated in $userTableName<br />";
					if ($doDebug) {
						echo "$rowsUpdated rows updated in $userTableName<br />";
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
add_shortcode ('update_callsign', 'update_callsign_func');
