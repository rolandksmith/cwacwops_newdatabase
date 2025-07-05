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
	$userName			= $initializationArray['userName'];
	
	if ($userName == '') {
		$content		.= "You are not authorized";
		return $content;
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
	$theURL						= "$siteURL/cwa-update-callsign/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Update Callsign V$versionNumber";
	$thisActionDate				= date('dMy H:i');
	$inp_old_callsign			= '';
	$inp_new_callsign			='';

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
				$inp_old_callsign	 = trim(strtoupper($str_value));
				$inp_old_callsign	 = filter_var($inp_old_callsign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_new_callsign") {
				$inp_new_callsign	 = trim(strtoupper($str_value));
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
		$extMode		= 'tm';
		// table format: table name|field to change|action log field|id field name
		$changeTables 	= array('wpw1_cwa_advisor|advisor_call_sign2|advisor_action_log|advisor_id',
								'wpw1_cwa_advisorclass2|advisorclass_call_sign|advisorclass_action_log|advisorclass_id',
								'wpw1_cwa_audio_assessment2|call_sign|assessment_notes|record_id',
								'wpw1_cwa_audit_log2|logwho&logcallsign||record_id',
								'wpw1_cwa_deleted_advisor2|advisor_call_sign|advisor_action_log|advisor_id',
								'wpw1_cwa_deleted_advisorclass2|advisorclass_call_sign|advisorclass_action_log|advisorclass_id',
								'wpw1_cwa_deleted_student2|student_call_sign|student_action_log|student_id',
								'wpw1_cwa_evaluate_advisor2|advisor_callsign&anonymous||evaluate_id',
								'wpw1_cwa_new_assessment_data2|callsign||record_id',
								'wpw1_cwa_reminders2|call_sign||record_id',
								'wpw1_cwa_replacement_requests2|call_sign||record_id|',
								'wpw1_cwa_student2|student_call_sign&student_pre_assigned_advisor&student_assigned_advisor|student_action_log|student_id',
								'wpw1_cwa_temp_data2|callsign|record_id',
								'wpw1_cwa_user_master_deleted2|user_call_sign|user_action_log|user_ID',
								'wpw1_cwa_user_master_history2|historywho&historycallsign||record_id',
								'wpw1_cwa_user_master2|user_call_sign|user_action_log|user_ID',
								'wpw1_users2|user_login&user_nicename||ID',
								'wpw1_cwa_faq2|faq_submitted_by||faq_record_id');
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		$studentTableName		= 'wpw1_cwa_student2';
		$advisorClassTableName	= 'wpw1_cwa_advisorclass2';
		
	} else {
		$extMode		= 'pd';
		// table format: table name|field to change|action log field|id field name
		$changeTables 	= array('wpw1_cwa_advisor|advisor_call_sign|advisor_action_log|advisor_id',
								'wpw1_cwa_advisorclass|advisorclass_call_sign|advisorclass_action_log|advisorclass_id',
								'wpw1_cwa_audio_assessment|call_sign|assessment_notes|record_id',
								'wpw1_cwa_audit_log|logwho&logcallsign||record_id',
								'wpw1_cwa_deleted_advisor|advisor_call_sign|advisor_action_log|advisor_id',
								'wpw1_cwa_deleted_advisorclass|advisorclass_call_sign|advisorclass_action_log|advisorclass_id',
								'wpw1_cwa_deleted_student|student_call_sign|student_action_log|student_id',
								'wpw1_cwa_evaluate_advisor|advisor_callsign&anonymous||evaluate_id',
								'wpw1_cwa_new_assessment_data|callsign||record_id',
								'wpw1_cwa_reminders|call_sign||record_id',
								'wpw1_cwa_replacement_requests|call_sign||record_id|',
								'wpw1_cwa_student|student_call_sign&student_pre_assigned_advisor&student_assigned_advisor|student_action_log|student_id',
								'wpw1_cwa_temp_data|callsign||record_id', 
								'wpw1_cwa_user_master_deleted|user_call_sign|user_action_log|user_ID',
								'wpw1_cwa_user_master_history|historywho&historycallsign||record_id',
								'wpw1_cwa_user_master|user_call_sign|user_action_log|user_ID',
								'wpw1_users|user_login&user_nicename||ID',
								'wpw1_cwa_faq|faq_submitted_by||faq_record_id');
		$userMasterTableName	= 'wpw1_cwa_user_master';
		$studentTableName		= 'wpw1_cwa_student';
		$advisorClassTableName	= 'wpw1_cwa_advisorclass';

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
		$content				.= "<h3>$jobname from $inp_old_callsign to $inp_new_callsign</h3>";
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
			// send email to Roland that a callsign change is happening
			
			$theSubject			= "CW Academy Replacement Call Sign Change";
			if ($testMode) {
				$theRecipient			= 'rolandksmith@gmail.com';
				$theSubject				= "TESTMODE $theSubject";
				$mailCode				= 2;
			} else {
				$theRecipient			= "rolandksmith@gmail.com";
				$mailCode				= 16;
			}
				
			$thisContent			= "<p>$userName has requested that the call sign of $inp_old_callsign 
										be changed to $inp_new_callsign</p>";
			$mailResult				= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
															'theSubject'=>$theSubject,
															'theContent'=>$thisContent,
															'jobname'=>$jobname,
															'mailCode'=>$mailCode,
															'testMode'=>$testMode,
															'doDebug'=>FALSE));
			// $mailResult = TRUE;
			if ($mailResult === TRUE) {
				if ($doDebug) {
					echo "call sign change emailed to Roland<br />";
				}
			} else {
				if ($doDebug) {
					echo "sending call sign change to Roland failed<br />";
				}
			}

			// first fix up the advisorClass student fields as we need to do this 
			// before the student record call sign changes
			// fix up student info in advisorClass table
			if ($doDebug) {
				echo "<br />getting assigned advisor from student table<br />";
			}
			$content			.= "<p>Processing $studentTableName Table</p>";
			$sql				= "select *  
									from $studentTableName 
									where student_call_sign='$inp_old_callsign' 
									and student_assigned_advisor != '' 
									order by student_date_created";
			$wpw1_cwa_student	= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError("$jobname 12",$doDebug);
				$content		.= "attempting to get assigned advisor for $inp_old_callsign failed<br />";
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
						$student_call_sign						= strtoupper($studentRow->student_call_sign);
						$student_action_log						= $studentRow->student_action_log;
						$student_assigned_advisor				= $studentRow->student_assigned_advisor;
						$student_assigned_advisor_class			= $studentRow->student_assigned_advisor_class;
						$student_semester						= $studentRow->student_semester;

						
						// if assigned_advisor then update the class record
						if ($student_assigned_advisor != '') {
							if ($doDebug) {
								echo "fixing up $student_assigned_advisor class record<br />";
							}
							$sql						= "select * from $advisorClassTableName 
															where advisorclass_call_sign='$student_assigned_advisor' 
																and advisorclass_sequence=$student_assigned_advisor_class 
																and advisorclass_semester='$student_semester'";
							$wpw1_cwa_advisorclass	= $wpdb->get_results($sql);
							if ($wpw1_cwa_advisorclass === FALSE) {
								handleWPDBError("$jobname 14",$doDebug);
							} else {
								$numACRows				= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $sql<br />and found $numACRows rows in $advisorClassTableName table<br />";
								}
								if ($numACRows > 0) {
									foreach ($wpw1_cwa_advisorclass as $advisorClassRow) {
										$advisorClass_ID				 		= $advisorClassRow->advisorclass_id;
										$advisorClass_semester					= $advisorClassRow->advisorclass_semester;
										$advisorClass_call_sign 				= $advisorClassRow->advisorclass_call_sign;
										$advisorClass_action_log 				= $advisorClassRow->advisorclass_action_log;
										$advisorClass_student01 				= $advisorClassRow->advisorclass_student01;
										$advisorClass_student02 				= $advisorClassRow->advisorclass_student02;
										$advisorClass_student03 				= $advisorClassRow->advisorclass_student03;
										$advisorClass_student04 				= $advisorClassRow->advisorclass_student04;
										$advisorClass_student05 				= $advisorClassRow->advisorclass_student05;
										$advisorClass_student06 				= $advisorClassRow->advisorclass_student06;
										$advisorClass_student07 				= $advisorClassRow->advisorclass_student07;
										$advisorClass_student08 				= $advisorClassRow->advisorclass_student08;
										$advisorClass_student09 				= $advisorClassRow->advisorclass_student09;
										$advisorClass_student10 				= $advisorClassRow->advisorclass_student10;
										$advisorClass_student11 				= $advisorClassRow->advisorclass_student11;
										$advisorClass_student12 				= $advisorClassRow->advisorclass_student12;
										$advisorClass_student13 				= $advisorClassRow->advisorclass_student13;
										$advisorClass_student14 				= $advisorClassRow->advisorclass_student14;
										$advisorClass_student15 				= $advisorClassRow->advisorclass_student15;
										$advisorClass_student16 				= $advisorClassRow->advisorclass_student16;
										$advisorClass_student17 				= $advisorClassRow->advisorclass_student17;
										$advisorClass_student18 				= $advisorClassRow->advisorclass_student18;
										$advisorClass_student19 				= $advisorClassRow->advisorclass_student19;
										$advisorClass_student20 				= $advisorClassRow->advisorclass_student20;
										$advisorClass_student21 				= $advisorClassRow->advisorclass_student21;
										$advisorClass_student22 				= $advisorClassRow->advisorclass_student22;
										$advisorClass_student23 				= $advisorClassRow->advisorclass_student23;
										$advisorClass_student24 				= $advisorClassRow->advisorclass_student24;
										$advisorClass_student25 				= $advisorClassRow->advisorclass_student25;
										$advisorClass_student26 				= $advisorClassRow->advisorclass_student26;
										$advisorClass_student27 				= $advisorClassRow->advisorclass_student27;
										$advisorClass_student28 				= $advisorClassRow->advisorclass_student28;
										$advisorClass_student29 				= $advisorClassRow->advisorclass_student29;
										$advisorClass_student30 				= $advisorClassRow->advisorclass_student30;
										$advisorClass_number_students			= $advisorClassRow->advisorclass_number_students;
		
										for ($snum=1;$snum<31;$snum++) {
											if ($snum < 10) {
												$strSnum 						= str_pad($snum,2,'0',STR_PAD_LEFT);
											} else {
												$strSnum						= strval($snum);
											}
											$studentCallSign					= ${'advisorClass_student' . $strSnum};
											if ($studentCallSign == $inp_old_callsign) { 		// if so, change the callsign
											$actionDate						= date('Y-m-d H:i:s');
												$advisorClass_action_log	= "$advisorClass_action_log / $thisActionDate $userName $jobname 
student$strSnum call sign changed from $inp_old_callsign to $inp_new_callsign ";
												$updateArray["advisorclass_student$strSnum"]	= $inp_new_callsign;
												$updateArray["advisorclass_action_log"]		= $advisorClass_action_log;
												$classUpdateData		= array('tableName'=>$advisorClassTableName,
																				'inp_method'=>'update',
																				'inp_data'=>$updateArray,
																				'inp_format'=>array('%s','%s'),
																				'jobname'=>$jobname,
																				'inp_id'=>$advisorClass_ID,
																				'inp_callsign'=>$advisorClass_call_sign,
																				'inp_semester'=>$advisorClass_semester,
																				'inp_sequence'=>$student_assigned_advisor_class,
																				'inp_who'=>$userName,
																				'testMode'=>$testMode,
																				'doDebug'=>$doDebug);
												$updateResult	= updateClass($classUpdateData);
												if ($updateResult[0] === FALSE) {
													handleWPDBError("$jobname 15",$doDebug);
												} else {
													if ($doDebug) {
														echo "Successfully updated callsign in advisorclass table<br />";
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
					$content		.= "$studentTableName table updated<br />
										advisorClass student records updated<br />";
				}
			}
			

			// begin modifying the tables in the changeTables array
			if ($doDebug) {
				echo "<br /><b>Starting changeTables array</b><br />";
			}
			foreach($changeTables as $thisTableValue) {
				$myArray			= explode("|",$thisTableValue);
				$tableToChange		= $myArray[0];
				$actionLogField		= $myArray[2];
				$idField			= $myArray[3];
				if ($doDebug) {
					echo "<br />Modifying $tableToChange - $myArray[0]<br />
							actionLogField: $actionLogField - $myArray[2]<br />
							fields to update: $myArray[1]<br />
							idField: $idField - $myArray[3]<br />";
				}
				$content			.= "<p>Processing table $tableToChange</p>";
				$myArray1			= explode("&",$myArray[1]);
				foreach($myArray1 as $fieldToChange) {
					if ($fieldToChange != '') {
						if ($doDebug) {
							echo "Modifying field $fieldToChange<br />";
						}
						if ($actionLogField == '') {
							if ($doDebug) {
								echo "No action log field<br />";
							}
							$updateResult	= $wpdb->update($tableToChange,
													array($fieldToChange=>$inp_new_callsign),
													array($fieldToChange=>$inp_old_callsign),
													array('%s'),
													array('%s'));
							if ($updateResult === NULL) {
								if ($doDebug) {
									$myStr	= $wpdb->last_query;
									$myStr1	= $wpdb->last_error;
									echo "attempting to run $myStr failed<br />
										  reported last error: $myStr1<br />";
								} else {
									handleWPDBError("$jobname $thisTableValue",$doDebug);
								}
								$content	.= "Updating field $fieldToChange failed<br />";
							} else {
//								$content	.= "Field $fieldToChange updated in $tableToChange<br />";
							}													
						} else {			// updating field plus action log
							if ($doDebug) {
								echo "table has an action_log<br />";
							}
							$sql			= "select $idField, 
													  $fieldToChange, 
													  $actionLogField 
													  from $tableToChange 
													  where $fieldToChange = '$inp_old_callsign'";
							$sqlResult		= $wpdb->get_results($sql);
							if ($sqlResult === FALSE) {
								handleWPDBError("$jobname $thisTableValue",$doDebug);
								$content	.= "Attempting to retrieve data from $tableToChange failed<br />";
							} else {
								$numRows	= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $sql<br />and retrieved $numRows rows<br />";
								}
								if ($numRows > 0) {
									$recordsUpdated		= 0;
									foreach($sqlResult as $sqlResultRow) {
										$thisID			= $sqlResultRow->$idField;
										$thisField		= $sqlResultRow->$fieldToChange;
										$thisActionLog	= $sqlResultRow->$actionLogField;
										$recordsUpdated++;
										if ($doDebug) {
											echo "updating record $recordsUpdated<br />";
										}
										
										$thisActionLog	.= " / $thisActionDate $jobname $userName $fieldToChange updated to $inp_new_callsign ";
										$updateSql		= "update $tableToChange 
															set $fieldToChange = '$inp_new_callsign', 
															    $actionLogField = '$thisActionLog' 
															where $idField = $thisID";
										$updateResult	= $wpdb->get_results($updateSql);
										if ($updateResult === FALSE) {
											handleWPDBError("$jobname $tableToChange",$doDebug);
											$content	.= "Unable to update $fieldToChange in $tableToChange table<br />";
										} else {
											if ($doDebug) {
												echo "Updated $tableToChange $fieldToChange to $inp_new_callsign<br />";
											}
//											$content	.= "Updated $tableToChange $fieldToChange <br />";
										}										
									}
								} else 
									$content	.= "No records in $tableToChange needed to be updated<br />";
							}
						}
						$content	.= "Finished with $tableToChange<br />";
					}
				}	
			}

			// fix up any reminder_text that has the old call sign
			$sql		= "UPDATE wpw1_cwa_reminders 
							SET reminder_text = REPLACE(reminder_text, '$inp_old_callsign', '$inp_new_callsign') 
							where resolved = 'N'";
			$remindersResult	= $wpdb->get_results($sql);
			if ($remindersResult === FALSE) {
				handleWPDBError($jobname,$doDebug,"attempting to update reminder_text");
				$content	.= "Attempting to update reminder_text failed<br />";
			} else {
				$content	.= "Any old callsigns in reminders_text have been updated<br />";
			}

			// Fix up excluded advisors
			if ($doDebug) {
				echo "<br />Checking excluded advisors in $studentTableName<br />";
			}			
			$content			.= "<p>Processing Excluded Advisors</p>";
			$sql				= "select * from $studentTableName 
									where student_excluded_advisor like '%$inp_old_callsign%' 
									order by student_date_created";
			$wpw1_cwa_student		= $wpdb->get_results($sql);
			if ($wpw1_cwa_student === FALSE) {
				handleWPDBError("$jobname 10",$doDebug);
			} else {
				$numSRows			= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and found $numSRows rows<br />";
				}
				if ($numSRows > 0) {
					$rowsUpdated			= 0;
					foreach ($wpw1_cwa_student as $studentRow) {
						$student_ID								= $studentRow->student_id;
						$student_action_log  					= $studentRow->student_action_log;
						$student_excluded_advisor  				= $studentRow->student_excluded_advisor;
									
						$student_action_log			.= " /$thisActionDate $jobname $userName excluded advisor callsign 
changed from $inp_old_callsign to $inp_new_callsign ";

						$student_excluded_advisor	= str_replace($inp_old_callsign,$inp_new_callsign,$student_excluded_advisor);
	
						$studentUpdated				= $wpdb->update($studentTableName,
																	array('student_excluded_advisor'=>$student_excluded_advisor,
																		  'student_action_log'=>$student_action_log), 
																	array('student_id'=>$student_ID), 
																	array('%s','%s'),
																	array('%d'));
						if ($studentUpdated === FALSE) {
							handleWPDBError("$jobname 11",$doDebug);
						} else {
							$rowsUpdated++;
							if ($doDebug) {
								echo "updated row $student_ID<br />";
							}
						}
					}
					$content			.= "Completed processing Excluded Advisors<br />";
					if ($doDebug) {
						echo "$rowsUpdated pre_assigned advisor rows updated in $studentTableName<br />";
					}
				}
			}
			
			
			// finally, store the previous callsign in the updated user_master record
			if ($doDebug) {
				echo "<br />updating user_master prev_callsign<br />";
			}
			$content	.= "<p>Updating Previous Callsigns in User Master</p>";
			$sql		= "select * from $userMasterTableName 
							where user_call_sign = '$inp_new_callsign'";
			$UMResult	= $wpdb->get_results($sql);
			if($UMResult === FALSE) {
				handleWPDBError("$jobname prev_callsign",$doDebug);
				$content	.= "failed getting user_master for $inp_new_callsign to update prev_callsign<br /";
			} else {
				$numRows = $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows records<br />";
				}
				if ($numRows > 0) {
					foreach($UMResult as $UMRow) {
						$user_ID			= $UMRow->user_ID;
						$user_prev_callsign	= $UMRow->user_prev_callsign;
						
						if ($user_prev_callsign == '') {
							$user_prev_callsign	= $inp_old_callsign;
						} else {
							$user_prev_callsign .= ",$inp_old_callsign";
						}
						$userMasterData			= array('tableName'=>$userMasterTableName,
													'inp_method'=>'update',
													'inp_data'=>array('user_prev_callsign'=>$user_prev_callsign),
													'inp_format'=>array('%s'),
													'jobname'=>$jobname,
													'inp_id'=>$user_ID,
													'inp_callsign'=>$inp_new_callsign,
													'inp_who'=>$userName,
													'testMode'=>$testMode,
													'doDebug'=>$doDebug);
						$updateResult	= update_user_master($userMasterData);
						if ($updateResult[0] === FALSE) {
							handleWPDBError($jobname,$doDebug);
							$content	.= "Unable to update prev_callsign in user_master for $inp_old_callsign<br />";
						} else {
							$content	.= "Updated $userMasterTableName table for $inp_new_callsign to record that the callsign had changed<br />";						
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
add_shortcode ('update_callsign', 'update_callsign_func');
