function crosscheck_callsigns_func() {

	global $wpdb;

	$doDebug						= TRUE;
	$testMode						= FALSE;
	$context = CWA_Context::getInstance();
	$validUser 						= $context->validUser;

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($context->toArray());
		echo "</pre><br />";
	}
	$userName			= $context->userName;
	$currentTimestamp	= $context->currentTimestamp;
	$validTestmode		= $context->validTestmode;
	$siteURL			= $context->siteurl;
	$userEmail			= $context->userEmail;
	$userDisplayName	= $context->userDisplayName;
	$userRole			= $context->userRole;
	
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
	$theURL						= "$siteURL/cwa-crosscheck-callsigns/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$jobname					= "Crosscheck Callsigns V$versionNumber";
	$errors						= 0;

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
			if ($str_key 		== "inp_typerun") {
				$inp_typerun	 = $str_value;
				$inp_typerun	 = filter_var($inp_typerun,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_csv") {
				$inp_csv	 = $str_value;
				$inp_csv	 = filter_var($inp_csv,FILTER_UNSAFE_RAW);
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
		$operatingMode				= 'Testmode';
		$userTableName				= 'wpw1_users2';
		$userMetaTableName			= 'wpw1_usermeta2';
	} else {
		$extMode					= 'pd';
		$userTableName				= 'wpw1_users';
		$userMetaTableName			= 'wpw1_usermeta';
		$operatingMode				= 'Production';
	}

	$user_dal = new CWA_User_Master_DAL();
	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>This program has functions to do several different crosschecks:<br />
							<ul><li>Check that each advisor record has a user_master record 
									and that each advisor user_master record has an advisor record</li>
								<li>Check that each advisorclass record has an advisor record and that 
									there is a user_master record</li>
								<li>Check that each student record has a user_master record and that each 
									student user_master record has a student record</li>
								<li>Compares a CWOps .csv file against the user_master file to see 
									how many CWOps members came through CW Academy</li>
							</ul>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'><b>Desired Action</b></td>
								<td><input type='radio' class='formInputButton' name='inp_typerun' value='advisor'> Crosscheck Advisors<br />
									<input type='radio' class='formInputButton' name='inp_typerun' value='advisorclass'> Crosscheck Advisorclasses<br />
									<input type='radio' class='formInputButton' name='inp_typerun' value='student'> Crosscheck Students<br />
									<input type='radio' class='formInputButton' name='inp_typerun' value='cwops'> Crosscheck CWOps<br />
									<input type='text' class='formInputText' name='inp_csv' size='50' maxlength='100'>CWOps CSV File</td></tr>
							$testModeOption
							<tr><td>Save this report to the reports achive?</td>
							<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
								<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass 2 with type run $inp_typerun<br />";
			if ($inp_typerun == 'cwops') {
				echo "CWOps csv filename: $inp_csv<br />";
			}
		}
		
		$content .= "<h3>$jobname<h3>";
		if ($inp_typerun == 'advisor') {
			$content .= "<h4>Crosschecking Advisors to User Master</h4>";
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'advisor_call_sign', 'value' => '', 'compare' => '!=' ]
				]
			];
			$advisor_data = $advisor_dal->get_advisor_by_order($criteria,'advisor_call_sign','ASC',$operatingMode);
			if ($advisor_data === FALSE) {
				$content .= "<p>Attempting to get advisor data returned FALSE</p>";
			} else {
				foreach($advisor_data as $key => $value) {
					$prevAdvisor = '';
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					if ($advisor_call_sign != $prevAdvisor) {
						$prevAdvisor = $advisor_call_sign;
						// get the user_master
						$user_data = $user_dal->get_user_master_by_callsign($advisor_call_sign,$operatingMode);
						if ($user_data === NULL) {
							$content .= "<p>Attempting to get user_master for $advisor_call_sign returned NULL</p>";
							$errors++;
						} else {
							if (! empty($user_data)) {
								foreach($user_data as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
									if ($user_role != 'advisor') {
										$updateLink = "<a href='$siteURL/cwa-display-and-update-user-master/?strpass=10' target='_blank'>$user_call_sign</a>";
										$content .= "The User_Master record for advisor $updateLink says the user_role is $user_role<br />";
										$errors++;
										$users_result = $wpdb->get_results("select * from $userTableName where user_login like '%$user_call_sign%'");
										if ($users_result === FALSE) {
											$thisSQL = $wpdb->last_query;
											$content .= "attempting to get $user_call_sign from $usersTableName returned FALSE<br />$thisSQL<br />";
										} else {
											$users_id = 0;
											foreach($users_result as $thisRow) {
												$users_id = $thisRow->ID;
											}
											if ($users_id != 0) {
												$userMeta_result = $wpdb->get_results("select * from $userMetaTableName where user_id = $users_id and meta_key = 'wpw1_capabilities'");
												if ($userMeta_result === FAlSE) {
													$thisSQL = $wpdb->last_query;
													$content .= "attempting to get $user_call_sign wpw1_capabilities from $userMetaTableName returned FALSE<br />$thisSQL<br />";
												} else {
													$thisMeta = '';
													foreach($userMeta_result as $metaRow) {
														$thisMeta = addslashes($metaRow->meta_value);
													}
													if ($thisMeta != '') {
														if (str_contains($thisMeta,'student')) {
															$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;userMeta says the advisor is a student<br />";
														}
														if (str_contains($thisMeta,'advisor')) {
															$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;userMeta says the advisor is an advisor<br />";
														}
													} else {
														$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Failed to get a usermeta record from $userMetaTableName<br />";
													}
												}
											} else {
												$thisSql = $wpdb->last_query;
												$content .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Failed to get a users record from $userTableName<br />$thisSql<br />";
											}
										}
									}
								}
							} else {
								$content .= "No User_Master record found for advisor $advisor_call_sign<br />";
								$errors++;
							}
						}
					}
				}
				$content .= "<p>Finished checking advisors against the user_master</p>
							<h4>Crosschecking User_Master to Advisor</h4>";
				// get the user_master records
				$criteria = [
					'relation' => 'AND',
					'clauses' => [
						['field' => 'user_role', 'value' => 'advisor', 'compare' => '=' ]
					]
				];
				$user_data = $user_dal->get_user_master($criteria,'user_call_sign','ASC',$operatingMode);
				if ($user_data === FALSE) {
					$content .= "<p>Attempting to retrieve user_master records returned FALSE</p>";
				} else {
					if (! empty($user_data)) {
						foreach($user_data as $key => $value) {
							foreach($value as $thisField => $thisValue) {
								$$thisField = $thisValue;
							}
							// get an advisor record
							$criteria = [
								'relation' => 'AND',
								'clauses' => [
									['field' => 'advisor_call_sign', 'value' => $user_call_sign, 'compare' => '=' ]
								]
							];
							$advisor_data = $advisor_dal->get_advisor_by_order($criteria,'advisor_call_sign','ASC',$operatingMode);
							if ($advisor_data === FALSE) {
								$content .= "<p>Attempting to get advisor data for $user_call_sign returned FALSE</p>";
							} else {
								if ( empty($advisor_data)) {
									$updateLink = "<a href='$siteURL/cwa-display-and-update-user-master/?strpass=10' target='_blank'>$user_call_sign</a>";
									$content .= "User_Master says $updateLink is an advisor but no advisor record found<br />";
									$errors++;
								}
							}
						}
					}
				}
			}
		} elseif ($inp_typerun == 'student') {
			$content .= "<h4>Crosschecking Students to User Master</h4>";
			$criteria = [
				'relation' => 'AND',
				'clauses' => [
					['field' => 'student_call_sign', 'value' => '', 'compare' => '!=' ]
				]
			];
			$student_data = $student_dal->get_student_by_order($criteria,'student_call_sign,student_date_created','ASC',$operatingMode);
			if ($student_data === FALSE) {
				$content .= "<p>Attempting to get student data returned FALSE</p>";
			} else {
				foreach($student_data as $key => $value) {
					$prevStudent = '';
					foreach($value as $thisField => $thisValue) {
						$$thisField = $thisValue;
					}
					if ($student_call_sign != $prevStudent) {
						$prevStudent = $student_call_sign;
						// get the user_master
						$user_data = $user_dal->get_user_master_by_callsign($student_call_sign,$operatingMode);
						if ($user_data === NULL) {
							$content .= "<p>Attempting to get user_master for $student_call_sign returned NULL</p>";
							$errors++;
						} else {
							if (! empty($user_data)) {
								foreach($user_data as $key => $value) {
									foreach($value as $thisField => $thisValue) {
										$$thisField = $thisValue;
									}
									if ($user_role != 'student') {
										$updateLink = "<a href='$siteURL/cwa-display-and-update-user-master/?strpass=10' target='_blank'>$user_call_sign</a>";
										$content .= "The User_Master record for student $updateLink says the user_role is $user_role<br />";
										$errors++;
									}
								}
							} else {
								$content .= "No User_Master record found for student $student_call_sign<br />";
								$errors++;
							}
						}
					}
				}
				$content .= "<p>Finished checking students against the user_master</p>
							<h4>Crosschecking User_Master to Student</h4>";
				// get the user_master records
				$criteria = [
					'relation' => 'AND',
					'clauses' => [
						['field' => 'user_role', 'value' => 'advisor', 'compare' => '=' ]
					]
				];
				$user_data = $user_dal->get_user_master($criteria,'user_call_sign','ASC',$operatingMode);
				if ($user_data === FALSE) {
					$content .= "<p>Attempting to retrieve user_master records returned FALSE</p>";
				} else {
					if (! empty($user_data)) {
						foreach($user_data as $key => $value) {
							foreach($value as $thisField => $thisValue) {
								$$thisField = $thisValue;
							}
							// get a student record
							$criteria = [
								'relation' => 'AND',
								'clauses' => [
									['field' => 'student_call_sign', 'value' => $user_call_sign, 'compare' => '=' ]
								]
							];
							$student_data = $student_dal->get_student_by_order($criteria,'student_call_sign','ASC',$operatingMode);
							if ($student_data === FALSE) {
								$content .= "<p>Attempting to get student data for $user_call_sign returned FALSE</p>";
							} else {
								if ( empty($student_data)) {
									$updateLink = "<a href='$siteURL/cwa-display-and-update-user-master/?strpass=10' target='_blank'>$user_call_sign</a>";
									$content .= "User_Master says $updateLink is an student but no student record found<br />";
									$errors++;
								}
							}
						}
					}
				}
			}
		} elseif ($inp_typerun == 'cwops') {
		/**
			0 - Dues Paid Through
			1 - Number
			2 - First Name
			3 - Surname
			4 - Callsign
			5 - Nickname
			6 - Membership join date
			7 - Quit Date
			8 - DXCC
			9 - State
			10 - LIFE
		*/

			$content .= "<h4>Comparing CWOps Membership to CW Academy Membership</h4>
						<p>Processing $inp_csv file</p>";
			$cwopsRecords = 0;
			$firstTime = TRUE;
			$compareDate = strtotime("January 1, 2020");
			$joinedAfter = 0;
			$cwaMember = 0;

			$fp = @fopen($inp_csv, "r");
			if ($fp) {
				$content .= "<table style='width:auto;'>
							<tr><th>CWOps Member</th>
								<th>After</th>
								<th>CW Academy Member</th>
								</tr>";
				while (($data = fgetcsv($fp, 1000, ",")) !== FALSE) {
					$cwopsRecords++;
					if($firstTime) {
						$firstTime = FALSE;
					} else {
						$cwopsQuit = $data[7];
						$cwopsCallsign = $data[4];
						$cwopsName = "$data[3], $data[2]";
						$cwopsJoin = strtotime($data[6]);
						
						$myStr = 'No';
						if($cwopsJoin >= $compareDate) {
							$myStr = 'YES';
						}
						// see if there is a user_master record
						$user_data = $user_dal->get_user_master_by_callsign($cwopsCallsign,$operatingMode);
						if ($user_data === NULL) {
							if ($doDebug) {
								echo "attempting to get user_master for $cwopsCallsign returned NULL<br />";
							}
							goto Done;
						} else {
							if (count($user_data) > 0) {
								$myStr1 = 'YES';
							} else {
								$myStr1 = 'No';
							}
						
						}
						$content .= "<tr><td>$cwopsName ($cwopsCallsign)</td>
										<td>$myStr</td>
										<td>$myStr1</td>
										</tr>";
						if ($myStr == 'YES') {
							$joinedAfter++;
						}
						if ($myStr1 == 'YES') {
							$cwaMember++;
						}
					}
				}
				Done:
				$content .= "</table>
							<p>$cwopsRecords Records read<br />
							$joinedAfter CWOps Members who joined after January 1, 2020<br />
							$cwaMember CWops Members joining after Januarh 1, 2020 who are CWA members</p>";
				fclose($fp);
			} else {
				$content .= "<p>File not found</p>";
			}
		}
		
		$content .= "<p>End of Crosscheck. $errors Errors Found</p>";
 	
	
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
add_shortcode ('crosscheck_callsigns', 'crosscheck_callsigns_func');

