function program_list_func() {


	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray = data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= strtoupper($initializationArray['userName']);
	$siteURL			= $initializationArray['siteurl'];
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		$content		= "You must be logged in to access this information<br />
							Click <a href='$siteURL/login/'>HERE</a> to log into CW Academy<br /><br />
							Click <a href='$siteURL/register/'>HERE</a> to register";
		return $content;
	}
	
	$userRole			= $initializationArray['userRole'];
	$userEmail			= $initializationArray['userEmail'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$strPass			= "0";
	$currentSemester	= $initializationArray['currentSemester'];
	$nextSemester		= $initializationArray['nextSemester'];
	$semesterTwo		= $initializationArray['semesterTwo'];
	$semesterThree		= $initializationArray['semesterThree'];
	$semesterFour		= $initializationArray['semesterFour'];
	$jobname			= "Program List";
	
	$criticalZipCodeMsg	= "<p><b>CRITICAL!</b> Your ZipCode is missing. As a result, the system is unable to properly 
								calculate your time offset to UTC. Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$userName&testMode=$testMode&doDebug=$doDebug' 
								target='_blank'><b>HERE</b></a> to update your ZipCode. When that is complete, please close that tab and refresh this tab.</p>";
	$criticalTimezoneMsg	= "<p><b>CRITICAL!</b> There are multiple timezone identifiers for your 
									location. Please click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$userName&testMode=$testMode&doDebug=$doDebug' 
									target='_blank'><b>HERE</b></a> to verify your User Master information and select the timezone identifier 
									that best fits where you live. When that is complete, please close that tab and refresh this tab.</p>";
	$criticalUpdateMsg	= "<p><b>CRITICAL!</b> The timezone ID in your record needs to be updated. Until that is done, the system 
								is unable to properly calculate your time offset to UTC. Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$userName&testMode=$testMode&doDebug=$doDebug' 
								target='_blank'><b>HERE</b></a> to update display your information. Update anything that has changed (if anything) 
								and click on 'Submit'. The system will update the timezone ID. When that is complete, please close that tab and refresh this tab.</p>";

	

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);


// get the input information
	if (isset($_REQUEST)) {
// echo "<br />REQUEST is set<br />";
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br />\n";
				}
			}
			if ($str_key 		== "validUser") {
				$validUser		 = $str_value;
				$validUser		 = filter_var($validUser,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "userName") {
				$userName		 = $str_value;
				$userName		 = filter_var($userName,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "userRole") {
				$userRole		 = $str_value;
				$userRole		 = filter_var($userRole,FILTER_UNSAFE_RAW);
			}
		}
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
		echo "<br /><b>Operating in TestMode</b><br />";
		$studentTableName		= 'wpw1_cwa_student2';
		$advisorTableName		= 'wpw1_cwa_advisor2';
		$userMasterTableName	= 'wpw1_cwa_user_master2';
		$usersTableName = 'wpw1_users2';
		$operatingMode = 'Testmode';
	} else {
		$studentTableName		= 'wpw1_cwa_student';
		$advisorTableName		= 'wpw1_cwa_advisor';
		$userMasterTableName	= 'wpw1_cwa_user_master';
		$usersTableName = 'wpw1_users';
		$operatingMode = 'Production';
	}
	
	$user_dal = new CWA_User_Master_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$student_dal = new CWA_Student_DAL();

	// get the user_master information	
	
	$badTimezoneID		= TRUE;
	$missingZipCode		= TRUE;
	$xxTimezoneID		= TRUE;
	$gotData			= FALSE;
	$result				= FALSE;
	$reason				= "";
	$user_call_sign 	= "unknown";
	$user_first_name 	= "unknown";
	$user_last_name 	= "unknown";
	$user_email 		= "unknown";
	$user_phone 		= "unknown";
	$user_ph_code 		= "";
	$user_city 			= "";
	$user_state 		= "";
	$user_zip_code 		= "";
	$user_country_code 	= "XX";
	$user_country 		= "";
	$user_whatsapp 		= "";
	$user_telegram 		= "";
	$user_signal 		= "";
	$user_messenger 	= "";
	$user_action_log	= "unknown";
	$user_timezone_id	 = "??";
	$user_languages 	= "";
	$user_role			= "";
	$user_prev_callsign	= "";
	$user_date_created 	= "";
	$user_date_updated 	= "";
	
	// get the user_master record
	$userMasterData = $user_dal->get_user_master_by_callsign($userName,$operatingMode);
	if ($userMasterData === FALSE) {
		if ($doDebug) {
			echo "Attempting to get user_master for $userName returned FALSE<br />";
		}
	} else {
		if (! empty($userMasterData)) {
			$updateUserMaster = FALSE;
			$updateUserMasterParams = array();
			$updateUsers = FALSE;
			$updateUsersParams = array();
			foreach($userMasterData as $key => $value) {
				foreach($value as $thisField => $thisValue) {
					$$thisField = $thisValue;
				}
				$gotData = TRUE;
				// check the user_master email and role
				if ($userRole != 'administrator') {
					if ($user_role != $userRole) {
						if ($doDebug) {
							echo "User_master role ($user_role) not same as Users ($userRole)<br />";
						}
						$updateUserMasterParams['user_role'] = $userRole;
						$user_role = $userRole;
						$updateUserMaster = TRUE;
						$updateUsers = TRUE;
						$updateUsersParams['user_registered'] = date('Y-m-d H:i:s');
					}
				}
				if ($user_email != $userEmail) {
					if ($doDebug) {
						echo "user_master email ($user_email) not same as users ($userEmail)<br />";
					}
					$updateUserMasterParams['user_email'] = $userEmail;
					$user_email = $userEmail;
					$updateUserMaster = TRUE;
				}
			}
			if ($updateUserMaster) {
				$updateResult = $user_dal->update($user_ID,$updateUserMasterParams,$operatingMode);
				if ($updateResult === FALSE) {
					if ($doDebug) {
						echo "Attempting to update user_master id $user_ID returned FALSE<br />";
					}
				} else {
					if ($doDebug) {
						echo "updated $updateResult rows<br />";
					}
				}
			}
			if ($updateUsers) {
				$myStr = date('Y-m-d H:i:s');
				$usersUpdateResult = $wpdb->get_results("update $usersTableName 
												set user_registered = '$myStr' 
												where user_login = '$userName'");
				if ($usersUpdateResult === FALSE) {
					$myStr = $wpdb->last_query;
					if ($doDebug) {
						echo "Attempting to update users for $userName returned FALSE<br />";
					}
					error_log("Program List for $userName ERROR attempting to update users returned FALSE\nSQL: $myStr");
				} else {
					if ($usersUpdateResult === 0) {
						if ($doDebug) {
							echo "Attempt to update users for $userName affected $usersUpdateResult rows<br />";
						}
						error_log("Program List for $userName ERROR attempting to update users affected $usersUpdateResult rows\nSQL: $myStr");
					}
				}
			}
		}
	}
	if (! $gotData) {
		// no userMaster record found. See if one needs to be created
		$dataArray					= array('getMethod'=>'callsign',
											'getInfo'=>$userName,
											'doDebug'=> $doDebug,
											'testMode'=> $testMode);
		$dataResult			= get_user_master_data($dataArray);

		// unpack the data
		$result				= $dataResult['result'];
		$reason				= $dataResult['reason'];
		
		if ($result === FALSE) {
			// failure actions
			$content		.= "getting data for $userName failed.<br />Reason: $reason<br />";
		} else {
			foreach($dataResult as $key => $value) {
				$$key = $value;
			}
			$gotData				= TRUE;
		}
	}
	if ($gotData) {
		$missingZipCode				= FALSE;
		$badTimezoneID				= FALSE;
		$xxTimezoneID				= FALSE;
		if ($user_country_code == 'US' && $user_zip_code == '') {
			$missingZipCode			= TRUE;
			$badTimezoneID			= TRUE;
		}
		if ($user_timezone_id == '??') {
			$xxTimezoneID			= TRUE;
			$badTimezoneID			= TRUE;
		} 
		if ($user_timezone_id == '') {
			$badTimezoneID			= TRUE;
		}
	} else {
		sendErrorEmail("$jobname no userMaster record and no userMeta data for $userName. Aborting");
		$content	.= "<p><b>FATAL ERROR</b> SysAdmin has been notified</p>";
		goto bypass;
	}

	if ($userRole == 'administrator') {
		$doProceed			= TRUE;
		if (!$badTimezoneID) {
			$returnInfo		= display_reminders('administrator',$userName,$doDebug);
			if ($returnInfo[0] === FALSE) {
				if ($doDebug) {
					echo "<br /><b>display_reminders</b> returned $returnInfo[1]<br />";
				}
			} else {
				if ($doDebug) {
					echo "<br /><b>display_reminders</b> returned $returnInfo[1]<br /><hr><br />";
				}
			
				$content	.= "<h3>Administrator Menu for $userName</h3>
								<div class='refresh' data-callsign='$userName' data-role='administrator' data-url='/wp-content/uploads/refreshapi.php' data-seconds='60'>
								$returnInfo[1]
								</div>";
			}
		} else {
			//// display user_master
			$content		.= "<h4>Advisor Master Data</h4>
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
								<td><b>Date Created</b><br />$user_date_created</td>
								<td><b>Date Updated</b><br />$user_date_updated</td>
								<td></td></tr>
							</table>";
			if ($xxTimezoneID) {
				if ($doDebug) {
					echo "timezone_id is ??. Giving update user master message<br />";
				}
				$content	.= "<p><b>CRITICAL!</b> There are multiple timezone identifiers for your 
								location. Please click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$userName&testMode=$testMode&doDebug=$doDebug' 
								target='_blank'>HERE</a> to verify your User Master information and select the timezone identifier 
								that best fits where you live. When that is complete, please close that tab and refresh this tab.</p>";
				$doProceed	= FALSE;
			} elseif ($missingZipCode) {
				if ($doDebug) {
					echo "Zipcode is missing for a US location. Giving the update user master message<br />";
				}
				$content	.= "<p><b>CRITICAL!</b> Your record is missing a zipcode. As a result, the system is unable to properly 
								calculate your time offset to UTC. Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$userName&testMode=$testMode&doDebug=$doDebug'
								target='_blank'>HERE</a> to update the Master Data with your zipcode. When that is complete, please close that tab and refresh this tab.</p>";
				$doProceed	= FALSE;
			} else {
				if ($doDebug) {
					echo "need to figure out the user's timezone ID. Giving the update user master message<br />";
				}
				$content	.= "<p><b>CRITICAL!</b> The timezone ID in your record needs to be updated. Until that is done, the system 
								is unable to properly calculate your time offset to UTC. Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$userName&testMode=$testMode&doDebug=$doDebug' 
								target='_blank'>HERE</a> to update display your information. Update anything that has changed (if anything) 
								and click on 'Submit'. The system will update the timezone ID. When that is complete, please close that tab and refresh this tab.</p>";
				$doProceed	= FALSE;
			}
		}
		$content		.= "<table style='width:1200px;'>
							<tr><td style='width:33%;vertical-align:top;'>
							<h4>Alphabetical Program List</h4>
							<ul>
							<li><a href='$siteURL/cwa-advisor-class-history/' target='_blank'>Advisor Class History</a>
							<li><a href='$siteURL/cwa-advisor-class-report/' target='_blank'>Advisor Class Report</a>
							<li><a href='$siteURL/cwa-advisor-registration/' target='_blank'>Advisor Registration</a>
							<li><a href='$siteURL/cwa-advisor-report-generator/' target='_blank'>Advisor Report Generator</a>
							<li><a href='$siteURL/cwa-advisor-request-student-assessment/?strpass=15' target='_blank'>Advisor Request Student Assessment</a>
							<li><a href='$siteURL/cwa-advisor-statistics/' target='_blank'>Advisor Statistics</a>
							<li><a href='$siteURL/cwa-advisorclass-report-generator/' target='_blank'>AdvisorClass Report Generator</a>
							<li><a href='$siteURL/cwa-advisors-with-unconfirmed-students/' target='_blank'>Advisors With Unconfirmed Students</a>
							<li><a href='$siteURL/cwa-assign-students-to-advisors-sequence/' target='_blank'>Assign Students to Advisors Sequence (TestMode)</a>
							<li><a href='$siteURL/cwa-assign-students-to-advisors/' target='_blank'>Assign Students to Advisors</a>
							<li><a href='$siteURL/cwa-automatic-student-backfill-v1/' target='_blank'>Automatic Student Backfill V1</a>
							<li><a href='$siteURL/bad-actors/' target='_blank'>Bad Actors</a>
							<li><a href='$siteURL/cwa-calculate-cwops-yield/' target='_blank'>Calculate CWOps Yield</a>
							<li><a href='$siteURL/cwa-calculate-marketing-statistics/' target='_blank'>Calculate Marketing Statistics</a>
							<li><a href='$siteURL/cwa-check-student-status/' target='_blank'>Check Student Status</a>
							<li><a href='$siteURL/cwa-daily-advisor-cron-process/' target='_blank'>Daily Advisor Cron Process</a>
							<li><a href='$siteURL/cwa-daily-backups-cron/' target='_blank'>Daily Backups Cron</a>
							<li><a href='$siteURL/cwa-daily-catalog-cron-process/' target='_blank'>Daily Catalog Cron Process</a>
							<li><a href='$siteURL/cwa-daily-cron-process/' target='_blank'>Daily Cron Process</a>
							<li><a href='$siteURL/cwa-daily-replacement-cron/' target='_blank'>Daily Replacement Cron</a>
							<li><a href='$siteURL/cwa-daily-student-cron/' target='_blank'>Daily Student Cron</a>
							<li><a href='$siteURL/cwa-daily-temp-data-cleanup/' target='_blank'>Daily Temp Data Cleanup</a>
							<li><a href='$siteURL/cwa-daily-uploads-cleanup/' target='_blank'>Daily Uploads Clenup</a>
							<li><a href='$siteURL/cwa-decline-student-reassignment/' target='_blank'>Decline Student Reassignment</a>
							<li><a href='$siteURL/cwa-detailed-history-for-an-advisor/' target='_blank'>Detailed History for an Advisor</a>
							<li><a href='$siteURL/cwa-display-advisor-evaluation-statistics/' target='_blank'>Display Advisor Evaluation Statistics</a>
							<li><a href='$siteURL/cwa-display-all-advisors/' target='_blank'>Display All Advisors</a>
							<li><a href='$siteURL/cwa-display-all-students/' target='_blank'>Display All Students</a>
							<li><a href='$siteURL/cwa-display-and-update-advisor-signup-info/' target='_blank'>Display and Update Advisor Signup Info</a>
							<li><a href='$siteURL/cwa-display-and-update-reminders/' target='_blank'>Display and Update Reminders</a>
							<li><a href='$siteURL/cwa-display-and-update-student-signup-information/' target='_blank'>Display and Update Student Signup Information</a>
							<li><a href='$siteURL/cwa-display-and-update-user-master-information/' target='_blank'>Display and Update User Master Information</a>
							<li><a href='$siteURL/cwa-display-catalog-for-a-timezone/' target='_blank'>Display Catalog for a Timezone</a>
							<li><a href='$siteURL/cwa-display-chronological-action-log/' target='_blank'>Display Chronological Action Log</a>
							<li><a href='$siteURL/cwa-display-current-and-future-students-and-errors/' target='_blank'>Display Current and Future Students and Errors</a>
							<li><a href='$siteURL/cwa-display-cw-assessment-for-a-callsign/' target='_blank'>Display CW Assessment for a Callsign</a>
							<li><a href='$siteURL/cwa-display-data-log' target='_blank'>Display Data Log</a>
							<li><a href='$siteURL/cwa-display-evaluations-for-an-advisor/' target='_blank'>Display Evaluations for an Advisor</a>
							<li><a href='$siteURL/cwa-display-initialization-array/' target='_blank'>Display Initialization Array</a>
							<li><a href='$siteURL/cwa-display-recent-reminders/' target='_blank'>Display Recent Reminders</a>
							<li><a href='$siteURL/cwa-display-reminders-for-a-callsign/' target='_blank'>Display Reminders for a Callsign</a>
							<li><a href='$siteURL/cwa-display-replacement-requests/' target='_blank'>Display Replacement Requests</a>
							<li><a href='$siteURL/cwa-display-saved-report/' target='_blank'>Display Saved Report</a>
							<li><a href='$siteURL/cwa-display-student-evaluation-of-advisors/' target='_blank'>Display Student Evaluation of Advisors</a>
							<li><a href='$siteURL/cwa-display-student-history/' target='_blank'>Display Student History</a>
							<li><a href='$siteURL/cwa-evaluate-student/' target='_blank'>Evaluate Student</a>
							<li><a href='$siteURL/cwa-frequently-asked-questions/' target='_blank'>Frequently Asked Questions</a>
							<li><a href='$siteURL/cwa-gather-and-display-student-statistics/' target='_blank'>Gather and Display Student Statistics</a>
							<li><a href='$siteURL/cwa-generate-advisor-overall-statistics/' target='_blank'>Generate Advisor Overall Statistics</a>
							<li><a href='$siteURL/cwa-how-students-progressed-report/' target='_blank'>How Students Progressed Report</a>
							<li><a href='$siteURL/cwa-legends-codes-and-things/' target='_blank'>Legends Codes and Things</a>
							<li><a href='$siteURL/cwa-list-advisors-with-incomplete-evaluations/' target='_blank'>List Advisors With Incomplete Evaluations</a>
							<li><a href='$siteURL/cwa-list-advisors-with-s-students/' target='_blank'>List Advisors With S Students</a>
							<li><a href='$siteURL/cwa-list-all-students/' target='_blank'>List All Students</a>
							<li><a href='$siteURL/cwa-list-past-advisors-registration-info/' target='_blank'>List Past Advisors Registration Info</a>
							<li><a href='$siteURL/cwa-list-student-responders/' target='_blank'>List Student Responders</a>
							<li><a href='$siteURL/cwa-list-student-self-assessments/' target='_blank'>List Student Self Assessments</a>
							<li><a href='$siteURL/cwa-list-students-for-a-semester/' target='_blank'>List Students for a Semester</a>
							<li><a href='$siteURL/cwa-list-user-master-callsign-history' target='_blank'>List User Master Callsign History</a>
							<li><a href='$siteURL/cwa-maintenance-mode/' target='_blank'>Maintenance Mode</a>
							<li><a href='$siteURL/cwa-make-or-restore-backups/' target='_blank'>Make or Restore Backups</a>
							<li><a href='$siteURL/cwa-manage-advisor-class-assignments/' target='_blank'>Manage Advisor Class Assignments</a>
							<li><a href='$siteURL/cwa-manage-temp-data/' target='_blank'>Manage Temp Data</a>
							<li><a href='$siteURL/cwa-move-student-to-different-semester/' target='_blank'>Move Student to Different Semester</a>
							<li><a href='$siteURL/cwa-move-unassigned-students-to-next-semester/' target='_blank'>Move Unassigned Students to Next Semester</a>
							<li><a href='$siteURL/cwa-new-usernames-report/' target='_blank'>New Usernames Report</a>
							<li><a href='$siteURL/cwa-practice-assessment/' target='_blank'>Practice Assessment</a>
							<li><a href='$siteURL/cwa-prepare-advisors-for-student-assignments/' target='_blank'>Prepare Advisors for Student Assignments</a>
							<li><a href='$siteURL/cwa-prepare-students-for-assignment-to-advisors/' target='_blank'>Prepare Students for Assignment to Advisors</a>
							<li><a href='$siteURL/cwa-process-advisor-verification/' target='_blank'>Process Advisor Verification</a>
							<li><a href='$siteURL/cwa-promotable-students-repeating/' target='_blank'>Promotable Students Repeating</a>
							<li><a href='$siteURL/cwa-push-advisor-class/' target='_blank'>Push Advisor Class</a>
							<li><a href='$siteURL/cwa-recover-deleted-record/' target='_blank'>Recover Deleted Record</a>
							<li><a href='$siteURL/cwa-remove-item/' target='_blank'>Remove Item</a>
							<li><a href='$siteURL/cwa-repeating-student-statistics/' target='_blank'>Repeating Student Statistics</a>
							<li><a href='$siteURL/cwa-search-audit-log/' target='_blank'>Search Audit Log</a>
							<li><a href='$siteURL/cwa-search-joblog/' target='_blank'>Search Joblog</a>
							<li><a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/' target='_blank'>Search Sent Email by Callsign or Email</a>
							<li><a href='$siteURL/cwa-search-tracking-data/' target='_blank'>Search Tracking Data</a>
							<li><a href='$siteURL/cwa-semester-raw-comparison-statistics/' target='_blank'>Semester Raw Comparison Statistics</a>
							<li><a href='$siteURL/cwa-send-advisor-email-to-view-student-evaluations/' target='_blank'>Send Advisor Email to View Student Evaluations</a>
							<li><a href='$siteURL/cwa-send-an-email/' target='_blank'>Send an Email</a>
							<li><a href='$siteURL/cwa-send-congratulations-email-to-students/' target='_blank'>Send Congratulations Email to Students</a>
							<li><a href='$siteURL/cwa-send-email-to-student-to-evaluate-advisor/' target='_blank'>Send Email to Student to Evaluate Advisor</a>
							<li><a href='$siteURL/cwa-send-end-of-semester-assessment-email-to-advisors/' target='_blank'>Send End of Semester Assessment Email to Advisors</a>
							<li><a href='$siteURL/cwa-send-evaluation-email-to-advisors/' target='_blank'>Send Evaluation Email to Advisors</a>
							<li><a href='$siteURL/cwa-send-mid-term-verification-email/' target='_blank'>Send Mid-term Verification Email</a>
							<li><a href='$siteURL/cwa-show-advisor-class-assignments/' target='_blank'>Show Advisor Class Assignments</a>
							<li><a href='$siteURL/cwa-show-callsign-audit-log-for-a-semester/' target='_blank'>Show Callsign Audit Log for a Semester</a>
							<li><a href='$siteURL/cwa-show-detailed-history-for-student/' target='_blank'>Show Detailed History for Student</a>
							<li><a href='$siteURL/cwa-show-timezone-information-for-a-location/' target='_blank'>Show Timezone Information for a Location</a>
							<li><a href='$siteURL/cwa-show-saved-email/' target='_blank'>Show Saved Email</a>
							<li><a href='$siteURL/cwa-student-and-advisor-assignments/' target='_blank'>Student and Advisor Assignments (#1 Report)</a>
							<li><a href='$siteURL/cwa-student-and-advisor-color-chart/' target='_blank'>Student and Advisor Color Chart</a>
							<li><a href='$siteURL/cwa-student-evaluation-of-advisor/' target='_blank'>Student Evaluation of Advisor</a>
							<li><a href='$siteURL/cwa-student-management/' target='_blank'>Student Management</a>
							<li><a href='$siteURL/cwa-student-progression-report/' target='_blank'>Student Progression Report</a>
							<li><a href='$siteURL/cwa-student-registration/' target='_blank'>Student Registration</a>
							<li><a href='$siteURL/cwa-student-report-generator/' target='_blank'>Student Report Generator</a>
							<li><a href='$siteURL/cwa-thank-you-remove/' target='_blank'>Thank You Remove</a>
							<li><a href='$siteURL/cwa-thank-you-yes/' target='_blank'>Thank You Yes</a>
							<li><a href='$siteURL/cwa-update-callsign/' target='_blank'>Update Callsign</a>
							<li><a href='$siteURL/cwa-update-unassigned-student-information/' target='_blank'>Update Unassigned Student Information</a>
							<li><a href='$siteURL/cwa-user-administration/' target='_blank'>User Administration</a>
							<li><a href='$siteURL/cwa-user-master-report-generator/' target='_blank'>User Master Report Generator</a>
							<li><a href='$siteURL/cwa-verify-advisor-class/' target='_blank'>Verify Advisor Class</a>
							<li><a href='$siteURL/cwa-verify-temp-data/' target='_blank'>Verify Temp Data</a>
							<li><a href='$siteURL/cwa-view-a-student-assessment/' target='_blank'>View Your Student Assessment</a>
							<li><a href='$siteURL/program-list/' target='_blank'>Program List</a>
							<li><a href='$siteURL/rkstest-function-test/' target='_blank'>RKSTEST Function Test</a>
							<li><a href='$siteURL/rkstest-run-arbitrary-php-code/' target='_blank'>RKSTEST Run Arbitrary PHP Code</a>
							<li><a href='$siteURL/utility-calculate-utc/' target='_blank'>UTILITY: Calculate UTC</a>
							<li><a href='$siteURL/utility-list-available-shortcodes/' target='_blank'>UTILITY - List Available Shortcodes</a>
							<li><a href='$siteURL/utility-show-offsets-for-a-country-or-zip-code/' target='_blank'>UTILITY Show Offsets for a Country or Zip Code</a><br /><br />
							
							<li><a href='$siteURL/cwa-student-management/?strpass=110' target='_blank'>Add Excluded Advisor to a Student</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=40' target='_blank'>Add Unassigned Student to an Advisor's Class</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=90' target='_blank'>Assign a Student to an Advisor regardless</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=85' target='_blank'>Confirm Attendance for One or More Students</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=5' target='_blank'>Delete Student's Pre-assigned Advisor</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=20' target='_blank'>Exclude an Advisor from being Assigned to a Student</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=70' target='_blank'>Find Possible Classes for a Student</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=80' target='_blank'>Find Possible Students for an Advisor's Class</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=7' target='_blank'>List Students Needing Intervention</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=35' target='_blank'>Move Student to a Different Level and Unassign</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=2' target='_blank'>Pre-assign Student to an Advisor</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=55' target='_blank'>Re-assign a Student to Another Advisor</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=60' target='_blank'>Remove and/or Unassign a Student</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=120' target='_blank'>Remove Excluded Advisor from a Student</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=25' target='_blank'>Resolve Student Hold</a>
							<li><a href='$siteURL/cwa-student-management/' target='_blank'>Student Management</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=50' target='_blank'>Unassign a Student Regardless of Status</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=85' target='_blank'>Confirm Attendance for One or More Students</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=100' target='_blank'>Verify One or More Students</a>
							</ul>
				
							<td style='width:33%;vertical-align:top;'>
			
							<h4>Statistics</h4>
							<ul>
							<li><a href='$siteURL/cwa-advisor-statistics/' target='_blank'>Advisor Statistics</a>
							<li><a href='$siteURL/cwa-display-advisor-evaluation-statistics/' target='_blank'>Display Advisor Evaluation Statistics</a>
							<li><a href='$siteURL/cwa-calculate-cwops-yield/' target='_blank'>Calculate CWOps Yield</a>
							<li><a href='$siteURL/cwa-calculate-marketing-statistics/' target='_blank'>Calculate Marketing Statistics</a>
							<li><a href='$siteURL/cwa-gather-and-display-student-statistics/' target='_blank'>Gather and Display Student Statistics</a>
							<li><a href='$siteURL/cwa-generate-advisor-overall-statistics/' target='_blank'>Generate Advisor Overall Statistics</a>
							<li><a href='$siteURL/cwa-how-students-progressed-report/' target='_blank'>How Students Progressed Report</a>
							<li><a href='$siteURL/cwa-repeating-student-statistics/' target='_blank'>Repeating Student Statistics</a>
							<li><a href='$siteURL/cwa-semester-raw-comparison-statistics/' target='_blank'>Semester Raw Comparison Statistics</a>
							</ul>
			
							<h4>Reminder Management</h4>
							<ul>
							<li><a href='$siteURL/cwa-display-recent-reminders/' target='_blank'>Display Recent Reminders</a>
							<li><a href='$siteURL/cwa-display-reminders-for-a-callsign/' target='_blank'>Display Reminders for a Callsign</a>
							<li><a href='$siteURL/cwa-display-and-update-reminders/' target='_blank'>Display and Update Reminders</a><br />
							</ul>
			
							<h4>User Master</h4>
							<ul>
							<li><a href='$siteURL/cwa-display-and-update-user-master-information/' target='_blank'>Display and Update User Master Information</a>
							<li><a href='$siteURL/cwa-list-user-master-callsign-history' target='_blank'>List User Master Callsign History</a>
							<li><a href='$siteURL/cwa-user-master-report-generator/' target='_blank'>User Master Report Generator</a>
							</ul>
							
							<h4>Advisor Portal Programs</h4>
							<ul>
							<li><a href='$siteURL/cwa-advisor-registration/' target='_blank'>Advisor Registration</a>
							<li><a href='$siteURL/cwa-advisor-request-student-assessment/' target='_blank'>Advisor Request Student Assessment</a>
							<li><a href='$siteURL/cwa-display-recent-reminders/' target='_blank'>Display Recent Reminders</a>
							<li><a href='$siteURL/cwa-manage-advisor-class-assignments/' target='_blank'>Manage Advisor Class Assignments</a>
							<li><a href='$siteURL/cwa-show-advisor-class-assignments/' target='_blank'>Show Advisor Class Assignments</a>
							<li><a href='$siteURL/cwa-update-callsign/' target='_blank'>Update Callsign</a>
							<li><a href='$siteURL/cwa-view-a-student-assessment/' target='_blank'>View Your Student Assessment</a><br /><br />
							</ul>
							
							<h4>Advisor Reports</h4>
							<ul>
							<li><a href='$siteURL/cwa-advisor-class-history/' target='_blank'>Advisor Class History</a>
							<li><a href='$siteURL/cwa-advisor-class-report/' target='_blank'>Advisor Class Report</a>
							<li><a href='$siteURL/cwa-advisor-statistics/' target='_blank'>Advisor Statistics</a>
							<li><a href='$siteURL/cwa-advisors-with-unconfirmed-students/' target='_blank'>Advisors With Unconfirmed Students</a>
							<li><a href='$siteURL/cwa-detailed-history-for-an-advisor/' target='_blank'>Detailed History for an Advisor</a>
							<li><a href='$siteURL/cwa-display-advisor-evaluation-statistics/' target='_blank'>Display Advisor Evaluation Statistics</a>
							<li><a href='$siteURL/cwa-display-all-advisors/' target='_blank'>Display All Advisors</a>
							<li><a href='$siteURL/cwa-display-evaluations-for-an-advisor/' target='_blank'>Display Evaluations for an Advisor</a>
							<li><a href='$siteURL/cwa-display-replacement-requests/' target='_blank'>Display Replacement Requests</a>
							<li><a href='$siteURL/cwa-generate-advisor-overall-statistics/' target='_blank'>Generate Advisor Overall Statistics</a>
							<li><a href='$siteURL/cwa-list-advisors-with-incomplete-evaluations/' target='_blank'>List Advisors With Incomplete Evaluations</a>
							<li><a href='$siteURL/cwa-list-advisors-with-s-students/' target='_blank'>List Advisors With S Students</a>
							<li><a href='$siteURL/cwa-list-past-advisors-registration-info/' target='_blank'>List Past Advisors Registration Info</a>
							<li><a href='$siteURL/cwa-show-advisor-class-assignments/' target='_blank'>Show Advisor Class Assignments</a>
							</ul>
			
							<h4>Advisor Utilities</h4>
							<ul>
							<li><a href='$siteURL/cwa-advisor-report-generator/' target='_blank'>Advisor Report Generator</a>
							<li><a href='$siteURL/cwa-advisorclass-report-generator/' target='_blank'>AdvisorClass Report Generator</a>
							<li><a href='$siteURL/cwa-display-and-update-advisor-signup-info/' target='_blank'>Display and Update Advisor Signup Info</a><br /><br />
							</ul>
			
							<h4>Student Portal Programs</h4>
							<ul>
							<li><a href='$siteURL/cwa-student-registration/' target='_blank'>Student Registration</a>
							<li><a href='$siteURL/cwa-display-and-update-user-master-information/' target='_blank'>Display and Update User Master Information</a>
							<li><a href='$siteURL/cwa-check-student-status/' target='_blank'>Check Student Status</a>
							<li><a href='$siteURL/cwa-update-callsign/' target='_blank'>Update Callsign</a>
							</ul>
							
							<h4>Student Reporting Programs</h4>
							<ul>
							<li><a href='$siteURL/cwa-check-student-status/' target='_blank'>Check Student Status</a>
							<li><a href='$siteURL/cwa-display-all-students/' target='_blank'>Display All Students</a>
							<li><a href='$siteURL/cwa-display-current-and-future-students-and-errors/' target='_blank'>Display Current and Future Students and Errors</a>
							<li><a href='$siteURL/cwa-display-cw-assessment-for-a-callsign/' target='_blank'>Display CW Assessment for a Callsign</a>
							<li><a href='$siteURL/cwa-display-student-history/' target='_blank'>Display Student History</a>
							<li><a href='$siteURL/cwa-gather-and-display-student-statistics/' target='_blank'>Gather and Display Student Statistics</a>
							<li><a href='$siteURL/cwa-how-students-progressed-report/' target='_blank'>How Students Progressed Report</a>
							<li><a href='$siteURL/cwa-list-all-students/' target='_blank'>List All Students</a>
							<li><a href='$siteURL/cwa-list-student-responders/' target='_blank'>List Student Responders</a>
							<li><a href='$siteURL/cwa-list-student-self-assessments/' target='_blank'>List Student Self Assessments</a>
							<li><a href='$siteURL/cwa-list-students-for-a-semester/' target='_blank'>List Students for a Semester</a>
							<li><a href='$siteURL/cwa-practice-assessment/' target='_blank'>Practice Assessment</a>
							<li><a href='$siteURL/cwa-promotable-students-repeating/' target='_blank'>Promotable Students Repeating</a>
							<li><a href='$siteURL/cwa-repeating-student-statistics/' target='_blank'>Repeating Student Statistics</a>
							<li><a href='$siteURL/cwa-show-detailed-history-for-student/' target='_blank'>Show Detailed History for Student</a>
							<li><a href='$siteURL/cwa-student-progression-report/' target='_blank'>Student Progression Report</a>
							</ul>

							<h4>Student Utilities Programs</h4>
							<ul>
							<li><a href='$siteURL/cwa-display-and-update-student-signup-information/' target='_blank'>Display and Update Student Signup Information</a>
							<li><a href='$siteURL/cwa-student-report-generator/' target='_blank'>Student Report Generator</a>
							</ul>

							<h4>Programs Run via Link</h4>
							<ul>
							<li><a href='$siteURL/cwa-evaluate-student/' target='_blank'>Evaluate Student</a>
							<li><a href='$siteURL/cwa-process-advisor-verification/' target='_blank'>Process Advisor Verification</a>
							<li><a href='$siteURL/cwa-remove-item/' target='_blank'>Remove Item</a>
							<li><a href='$siteURL/cwa-student-evaluation-of-advisor/' target='_blank'>Student Evaluation of Advisor</a>
							<li><a href='$siteURL/cwa-thank-you-remove/' target='_blank'>Thank You Remove</a>
							<li><a href='$siteURL/cwa-thank-you-yes/' target='_blank'>Thank You Yes</a>
							<li><a href='$siteURL/cwa-verify-advisor-class/' target='_blank'>Verify Advisor Class</a>
							<li><a href='$siteURL/cwa-decline-student-reassignment/' target='_blank'>Decline Student Reassignment</a><br /><br />
							</ul>
			
							<h4>Roland Programs</h4>
							<ul>
							<li><a href='$siteURL/cwa-advisor-service-report/' target='_blank'>Advisor Service Report</a>
							<li><a href='$siteURL/cwa-advisor-session-info/' target='_blank'>Advisor Session Info</a>
							<li><a href='$siteURL/cwa-cross-check-student-assignments/' target='_blank'>Cross Check Student Assignments</a>
							<li><a href='$siteURL/cwa-daily-cron-debug/' target='_blank'>Daily Cron Debug</a>
							<li><a href='$siteURL/cwa-dump-user-info/' target='_blank'>Dump User Info</a>
							<li><a href='$siteURL/cwa-generic-updater/' target='_blank'>Generic Updater</a>
							<li><a href='$siteURL/cwa-list-all-pages/' target='_blank'>List All Pages</a>
							<li><a href='$siteURL/cwa-run-saved-report-generator/' target='_blank'>Run Saved Report Generator</a>
							<li><a href='$siteURL/cwa-send-an-email-to-a-list/' target='_blank'>Send an Email to a List</a>
							<li><a href='$siteURL/cwa-update-advisor-service/' target='_blank'>Update Advisor Service</a>
							<li><a href='$siteURL/rkstest-function-test/' target='_blank'>RKSTEST Function Test</a>
							<li><a href='$siteURL/rkstest-run-arbitrary-php-code/' target='_blank'>RKSTEST Run Arbitrary PHP Code</a><br /><br />
							<li><a href='$siteURL/cwa-manage-directory/' target='_blank'>Manage Directory</a>
							</ul>
							
							<h4>UNDER DEVELOPMENT</h4>
							<ul>
							<li><a href='$siteURL/cwa-advisor-request-student-survey/' target='_blank'>Advisor Request Student Survey</a>
							<li><a href='$siteURL/cwa-setup-survey/' target='_blank'>Setup Survey</a>
							<li><a href='$siteURL/cwa-display-survey/' target='_blank'>Display Survey</a>
							<li><a href='$siteURL/cwa-display-survey-results/' target='_blank'>Display Survey Results</a>
							</ul>
								
							<td style='vertical-align:top;'>
							<h4>Common Student / Advisor Management Tasks</h4>
							<li><a href='$siteURL/cwa-make-or-restore-backups/' target='_blank'>Make or Restore Backups</a><br /><br />
							<li><a href='$siteURL/cwa-student-and-advisor-assignments/' target='_blank'>Student and Advisor Assignments (#1 Report)</a>
							<li><a href='$siteURL/cwa-student-and-advisor-color-chart/' target='_blank'>Student and Advisor Color Chart</a><br /><br />
							<li><a href='$siteURL/cwa-display-and-update-user-master-information/' target='_blank'>Display and Update User Master Information</a>
							<li><a href='$siteURL/cwa-display-and-update-advisor-signup-info/' target='_blank'>Display and Update Advisor Signup Info</a>
							<li><a href='$siteURL/cwa-display-and-update-student-signup-information/' target='_blank'>Display and Update Student Signup Information</a><br /><br />
							<li><a href='$siteURL/cwa-push-advisor-class/' target='_blank'>Push Advisor Class</a><br /><br />
							<li><a href='$siteURL/cwa-user-administration/' target='_blank'>User Administration</a> (Take Over an Acct)<br /><br />
							<li><a href='$siteURL/cwa-student-management/?strpass=90' target='_blank'>Assign a Student to an Advisor regardless</a>
							<li><a href='$siteURL/cwa-display-cw-assessment-for-a-callsign/' target='_blank'>Display CW Assessment for a Callsign</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=70' target='_blank'>Find Possible Classes for a Student</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=80' target='_blank'>Find Possible Students for an Advisor's Class</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=50' target='_blank'>Unassign a Student Regardless of Status</a>
							<li><a href='$siteURL/cwa-move-student-to-different-semester/' target='_blank'>Move Student to Different Semester</a>
							<li><a href='$siteURL/cwa-student-management/?strpass=55' target='_blank'>Re-assign a Student to Another Advisor</a>
							<li><a href='$siteURL/cwa-automatic-student-backfill-v1/' target='_blank'>Automatic Student Backfill V1</a><br /><br />
							<li><a href='$siteURL/wp-admin/index.php' target='_blank'>Dashboard<br /><br />
							<li><a href='$siteURL/cwa-display-saved-report/' target='_blank'>Display Saved Report</a>
							<li><a href='$siteURL/program-list/' target='_blank'>Program List</a>
							<li><a href='$siteURL/cwa-frequently-asked-questions/' target='_blank'>Frequently Asked Questions</a>
							<li><a href='$siteURL/wp-login.php/?action=logout'>Logout<br ><br />
							</ul>
			
			
							
							<h4>Scheduled Programs</h4>
							<ul>
							<li><a href='$siteURL/cwa-daily-cron-process/' target='_blank'>Daily Cron Process</a>
							<ul>
							<li><a href='$siteURL/cwa-daily-backups-cron/' target='_blank'>Daily Backups Cron</a>
							<li><a href='$siteURL/cwa-daily-advisor-cron-process/' target='_blank'>Daily Advisor Cron Process</a>
							<li><a href='$siteURL/cwa-daily-catalog-cron-process/' target='_blank'>Daily Catalog Cron Process</a>
							<li><a href='$siteURL/cwa-daily-student-cron/' target='_blank'>Daily Student Cron</a>
							<li><a href='$siteURL/cwa-daily-replacement-cron/' target='_blank'>Daily Replacement Cron</a>
							<li><a href='$siteURL/cwa-new-usernames-report/' target='_blank'>New Usernames Report</a>
							<li><a href='$siteURL/cwa-daily-temp-data-cleanup/' target='_blank'>Daily Temp Data Cleanup</a>
							<li><a href='$siteURL/cwa-daily-uploads-cleanup/' target='_blank'>Daily Uploads Clenup</a>
							<br /><br />
							</ul>
							<li><a href='$siteURL/cwa-assign-students-to-advisors-sequence/' target='_blank'>Assign Students to Advisors Sequence (TestMode)</a>
							<ul>
							<li><a href='$siteURL/cwa-prepare-students-for-assignment-to-advisors/' target='_blank'>Prepare Students for Assignment to Advisors</a>
							<li><a href='$siteURL/cwa-prepare-advisors-for-student-assignments/' target='_blank'>Prepare Advisors for Student Assignments</a>
							<li><a href='$siteURL/cwa-assign-students-to-advisors/' target='_blank'>Assign Students to Advisors</a><br /><br />
							</ul>
							<li><a href='$siteURL/cwa-move-unassigned-students-to-next-semester/' target='_blank'>Move Unassigned Students to Next Semester</a>
							<li><a href='$siteURL/cwa-send-mid-term-verification-email/' target='_blank'>Send Mid-term Verification Email</a>
							<li><a href='$siteURL/cwa-send-end-of-semester-assessment-email-to-advisors/' target='_blank'>Send End of Semester Assessment Email to Advisors</a>
							<li><a href='$siteURL/cwa-send-evaluation-email-to-advisors/' target='_blank'>Send Evaluation Email to Advisors</a>
							<li><a href='$siteURL/cwa-send-email-to-student-to-evaluate-advisor/' target='_blank'>Send Email to Student to Evaluate Advisor</a>
							<li><a href='$siteURL/cwa-send-advisor-email-to-view-student-evaluations/' target='_blank'>Send Advisor Email to View Student Evaluations</a>
							<li><a href='$siteURL/cwa-send-congratulations-email-to-students/' target='_blank'>Send Congratulations Email to Students</a><br /><br />
							</ul>
				
							<h4>Student Management Functions</h4>
							<ul>
							<li><strong>Useful Functions Before Students are Assigned to Advisors</strong>
							<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>
							
								<li><a href='$siteURL/cwa-student-management/?strpass=2' target='_blank'>Pre-assign Student to an Advisor</a>
								<li><a href='$siteURL/cwa-student-management/?strpass=5' target='_blank'>Delete Student&apos;s Pre-assigned Advisor</a>
								<li><a href='$siteURL/cwa-student-and-advisor-color-chart' target='_blank'><b>Color Chart</b> - Display Student and Advisor Statistics</a>
								<li><a href='$siteURL/cwa-student-management/?strpass=7' target='_blank'>List Students Needing Intervention</a>
								<li><a href='$siteURL/cwa-student-management/?strpass=25' target='_blank'>Resolve Student Hold<a/>
								<li><a href='$siteURL/cwa-student-management/?strpass=100' target='_blank'>Verify One or More Students</a>
								<li><a href='$siteURL/cwa-student-management/?strpass=110' target='_blank'>Add Excluded Advisor to a Student</a>
								<li><a href='$siteURL/cwa-student-management/?strpass=120' target='_blank'>Remove Excluded Advisor from a Student</a>
								</ol>
							
								<li><strong>Useful Functions Any Time</strong>
								<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>
								
									<li><a href='$siteURL/cwa-show-detailed-history-for-student/' target='_blank'>Show Detailed History for a Student</a>
									<li><a href='$siteURL/cwa-move-student-to-different-semester/' target='_blank'>Change unassigned student's semester</a>
									<li><a href='$siteURL/cwa-update-unassigned-student-information/' target='_blank'>Update Unassigned Student Info</a>
									<li><a href='$siteURL/cwa-student-management/?strpass=60' target='_blank'>Unassign and Remove a Student</a>
									<li><a href='$siteURL/cwa-student-management/?strpass=20' target='_blank'>Exclude an Advisor from being Assigned to a Specific Student</a>
									</ol>
								
								<li><strong>Useful Functions After Students Have Been Assigned to Advisors</strong>
								<ol style='list-style-type: lower-alpha; padding-bottom: 0;'>
								
									<li><a href='$siteURL/cwa-student-management/?strpass=35' target='_blank'>Move Student to a Different Level and Unassign</a>
									<li><a href='$siteURL/cwa-student-management/?strpass=40' target='_blank'>Add Unassigned Student to an Advisor's Class</a>
									<li><a href='$siteURL/cwa-student-management/?strpass=50' target='_blank'>Unassign a Student Regardless of Status</a>
									<li><a href='$siteURL/cwa-student-management/?strpass=55' target='_blank'>Re-assign a Student to Another Advisor</a>
									<li><a href='$siteURL/cwa-student-management/?strpass=70' target='_blank'>Find Possible Classes for a Student</a>
									<li><a href='$siteURL/cwa-student-management/?strpass=80' target='_blank'>Find Possible Students for an Advisor's Class</a>
									<li><a href='$siteURL/cwa-student-management/?strpass=90' target='_blank'>Assign a Student to an Advisor</a> regardless of status or semester
									<li><a href='$siteURL/cwa-student-management/?strpass=85' target='_blank'>Confirm Attendance for One or More Students</a>
									</ol>
								</ul>
				
							<h4>Other Utilities Programs</h4>
							<ul>
							<li><a href='$siteURL/bad-actors/' target='_blank'>Bad Actors</a>
							<li><a href='$siteURL/cwa-display-catalog-for-a-timezone/' target='_blank'>Display Catalog for a Timezone</a>
							<li><a href='$siteURL/cwa-display-chronological-action-log/' target='_blank'>Display Chronological Action Log</a>
							<li><a href='$siteURL/cwa-display-data-log' target='_blank'>Display Data Log</a>
							<li><a href='$siteURL/cwa-display-initialization-array/' target='_blank'>Display Initialization Array</a>
							<li><a href='$siteURL/cwa-display-replacement-requests/' target='_blank'>Display Replacement Requests</a>
							<li><a href='$siteURL/cwa-display-saved-report/' target='_blank'>Display Saved Report</a>
							<li><a href='$siteURL/cwa-legends-codes-and-things/' target='_blank'>Legends Codes and Things</a>
							<li><a href='$siteURL/cwa-maintenance-mode/' target='_blank'>Maintenance Mode</a>
							<li><a href='$siteURL/cwa-manage-temp-data/' target='_blank'>Manage Temp Data</a>
							<li><a href='$siteURL/cwa-recover-deleted-record/' target='_blank'>Recover Deleted Record</a>
							<li><a href='$siteURL/cwa-run-saved-report-generator/' target='_blank'>Run Saved Report Generator</a>
							<li><a href='$siteURL/cwa-search-audit-log/' target='_blank'>Search Audit Log</a>
							<li><a href='$siteURL/cwa-search-joblog/' target='_blank'>Search Joblog</a>
							<li><a href='$siteURL/cwa-search-sent-email-by-callsign-or-email/' target='_blank'>Search Sent Email by Callsign or Email</a>
							<li><a href='$siteURL/cwa-search-tracking-data/' target='_blank'>Search Tracking Data</a>
							<li><a href='$siteURL/cwa-send-an-email/' target='_blank'>Send an Email</a>
							<li><a href='$siteURL/cwa-show-callsign-audit-log-for-a-semester/' target='_blank'>Show Callsign Audit Log for a Semester</a>
							<li><a href='$siteURL/cwa-show-saved-email/' target='_blank'>Show Saved Email</a>
							<li><a href='$siteURL/cwa-show-timezone-information-for-a-location/' target='_blank'>Show Timezone Information for a Location</a>
							<li><a href='$siteURL/cwa-update-unassigned-student-information/' target='_blank'>Update Unassigned Student Information</a>
							<li><a href='$siteURL/utility-calculate-utc/' target='_blank'>UTILITY: Calculate UTC</a>
							<li><a href='$siteURL/utility-list-available-shortcodes/' target='_blank'>UTILITY - List Available Shortcodes</a>
							<li><a href='$siteURL/utility-show-offsets-for-a-country-or-zip-code/' target='_blank'>UTILITY Show Offsets for a Country or Zip Code</a><br /><br />
							</ul>
							</td>
							</tr>
							</table>";
	}
	if ($userRole == 'advisor' || $userRole == 'administrator') {
		$doProceed		= TRUE;
		if (!$badTimezoneID) {
			$returnInfo		= display_reminders('advisor',$userName,$doDebug);
			if ($returnInfo[0] === FALSE) {
				if ($doDebug) {
					echo "display_reminders returned $returnInfo[1]<br />";
				}
				$content	.= "<h3>Advisor Portal for $userName</h3>";
			} else {
				$content	.= "<h3>Advisor Portal for $userName</h3>
								<div class='refresh' data-callsign='$userName' data-role='advisor' data-url='/wp-content/uploads/refreshapi.php' data-seconds='60'>
								$returnInfo[1]
								</div>";
			}
		}
		//// display user_master
		$content		.= "<h4>Advisor Master Data</h4>
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
							<td><b>Date Created</b><br />$user_date_created</td>
							<td><b>Date Updated</b><br />$user_date_updated</td>
							<td></td></tr>
						</table>";
		if ($badTimezoneID) {
			if ($xxTimezoneID && $missingZipCode) {
				if ($doDebug) {
					echo "Zipcode is missing for a US location. Giving the update user master message<br />";
				}
				$content	.= "$criticalZipCodeMsg";
				$doProceed	= FALSE;
			} elseif ($xxTimezoneID && !$missingZipCode) {
				if ($doDebug) {
					echo "timezone_id is ??. Giving update user master message<br />";
				}
				$content	.= "$criticalTimezoneMsg";
				$doProceed	= FALSE;				
			} else {
				if ($doDebug) {
					echo "need to figure out the user's timezone ID. Giving the update user master message<br />";
				}
				$content	.= "$criticalUpdateMsg";
				$doProceed	= FALSE;
			}
		}
		if ($doProceed) {
			$content	.= "<p><b>Is Your Advisor Master Data Correct?</b><br />
							Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$userName&testMode=$testMode&doDebug=$doDebug' 
							target='_blank'>HERE</a> to update the Master Data</p>";
			if (!$userRole == 'advisor') {
				// see if the advisor has a advisor record. If not, indicate that they must sign up
				
				$criteria = [
					'relation' => 'AND',
					'clauses' => [
						['field' => 'advisor_call_sign', 'value' => $userName, 'compare' => '=' ]
					]
				];
				$advisor_data = $advisor_dal->get_advisor($criteria,$operatingMode);
				if ($advisor_data === FALSE) {
					if ($doDebug) {
						echo "Attempting to get advisor info for $userName returned FALSE<br />";
					}
				} else {
					if ( empty($advisor_data)) {
						$content	.= "<p><b>IMPORTANT!</b> You have obtained a username and password. 
										You must next sign up to advise one or more classes in order to proceed.</p>";
					}
				}
			}
			$content	.= "<h4>Advisor Programs</h4>
							<p><a href='$siteURL/cwa-manage-advisor-class-assignments/' target='_blank'>Manage Advisor Class Assignments</a><br />
								This program displays the current class makeup and allows the advisor to specify student's status,
								request replacement students, check Morse Code Proficency Assessments.</p>
							<p><a href='$siteURL/cwa-view-a-student-assessment/' target='_blank'>View Your Student's Morse Code Assessments</a><br />
								This program allows the advisor to view one of his student's Morse Code Proficiency Assessments.</p>
							<p><a href='$siteURL/cwa-advisor-registration/' target='_blank'>Sign up for an Advisor Class</a><br />
								This program provides the ability to sign up for an upcoming semester and specify 
								what class(es) the advisor wishes to hold, and when those classes would be held. 
								It also provides the ability for the advisor to update that information as needed.</p>
							<p><a href='$siteURL/cwa-show-advisor-class-assignments/?strpass=2&inp_callsign=$userName&inp_verified=Y' target='_blank'>Show Advisor Class Assignments</a><br />
								This program shows upcoming advisor registrations (if any), current class makeup, and 
								all past students</p>
							<p><a href='$siteURL/cwa-advisor-request-student-assessment/' target='_blank'>Order Morse Code Proficiency Assessments</a><br />
								This program allows the advisor to specify the parameters for the assessment and 
								which students are to take the assessment.</p>
							<p><a href='$siteURL/cwa-update-callsign/' target='_blank'>Update Callsign</a><br />
								This program will change your callsign to your new callsign in the CW Academy database.</p>
							<p><a href='$siteURL/cwa-frequently-asked-questions/' target='_blank'>Frequently Asked Questions</a><br />
								This program allows you to ask a question, display the questions and answers, and search questions.</p>
							<p><a href='https://cwops.org/cw-academy/cwa-advisor-resources/' target='_blank'>Advisor Resources</a><br />
								Use this link to access the advisor resources hosted on cwops.org</p>";
		}
	}

	
	if ($userRole == 'student' || $userRole == 'administrator') {
		$doProceed		= TRUE;
		if (!$badTimezoneID) {
			$returnInfo		= display_reminders('student',$userName,$doDebug);
			if ($returnInfo[0] === FALSE) {
				if ($doDebug) {
					echo "display_reminders returned $returnInfo[1]<br />";
				}
				$content	.= "<h3>Student Portal for $userName</h3>";
			} else {
				$content	.= "<h3>Student Portal for $userName</h3>
								<div class='refresh' data-callsign='$userName' data-role='student' data-url='/wp-content/uploads/refreshapi.php' data-seconds='60'>
								$returnInfo[1]
								</div>";
			}
		}
		//// display user_master
		$content		.= "<h4>Student Master Data</h4>
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
							<td><b>Date Created</b><br />$user_date_created</td>
							<td><b>Date Updated</b><br />$user_date_updated</td>
							<td></td></tr>
						</table>";
		if ($badTimezoneID) {
			if ($xxTimezoneID && $missingZipCode) {
				if ($doDebug) {
					echo "Zipcode is missing for a US location. Giving the update user master message<br />";
				}
				$content	.= "$criticalZipCodeMsg";
				$doProceed	= FALSE;
			} elseif ($xxTimezoneID && !$missingZipCode) {
				if ($doDebug) {
					echo "timezone_id is ??. Giving update user master message<br />";
				}
				$content	.= "$criticalTimezoneMsg";
				$doProceed	= FALSE;				
			} else {
				if ($doDebug) {
					echo "need to figure out the user's timezone ID. Giving the update user master message<br />";
				}
				$content	.= "$criticalUpdateMsg";
				$doProceed	= FALSE;
			}
		}
		if ($doProceed) {
			$content	.= "<p><b>Is Your Student Master Data Correct?</b><br />
							Click <a href='$siteURL/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info=$userName&testMode=$testMode&doDebug=$doDebug' 
							target='_blank'>HERE</a> to update the Master Data</p>";
			if ($userRole == 'student') {
				if ($doDebug) {
					echo "checking to see if student has signed up<br />";
				}
				// see if the student has a student record. If not, indicate that they must sign up
				$criteria = [
					'relation' => 'AND',
					'clauses' => [
						// field1 = $value1
						['field' => 'student_call_sign', 'value' => $userName, 'compare' => '='],
						
						// (field2 = $value2 OR field2 = $value3)
						[
							'relation' => 'OR',
							'clauses' => [
								['field' => 'student_semester', 'value' => $currentSemester, 'compare' => '='],
								['field' => 'student_semester', 'value' => $nextSemester, 'compare' => '='],
								['field' => 'student_semester', 'value' => $semesterTwo, 'compare' => '='],
								['field' => 'student_semester', 'value' => $semesterThree, 'compare' => '='],
								['field' => 'student_semester', 'value' => $semesterFour, 'compare' => '=']
							]
						]
					]
				];
				$studentData = $student_dal->get_student($criteria,'student_call_sign','ASC',$operatingMode);
				if ($studentData === FAlSE) {
					if ($doDebug) {
						echo "Attempting to retrieve student data for $userName returned FALSE<br />";
					}
				} else {
					if (empty($studentData)) {
						$content	.= "<p><b>IMPORTANT!</b> You should next sign up for a class.</p>";
					}
				}
			}
			$content	.= "<h4>Student Programs</h4>
							<p><a href='$siteURL/cwa-student-registration/' target='_blank'>Student Sign up</a><br />
								Use this program to sign up for a CW Academy Class; modify your class details; or take a Morse code proficiency assessment 
								details.</p>
							<p><a href='$siteURL/cwa-display-and-update-user-master-information' target='_blank'>Update User Master Information</a><br />
								The program allows you to maintain your personal information such as address, phone number, and text messaging 
								information</p>
							<p><a href='$siteURL/cwa-check-student-status/' target='_blank'>Check Student Status</a><br />
								The program displays the current status of your CW Academy class request.</p>
							<p><a href='$siteURL/cwa-update-callsign/' target='_blank'>Update Callsign</a><br />
								This program will change your callsign to your new callsign in the CW Academy database.</p>
							<p><a href='$siteURL/cwa-frequently-asked-questions/' target='_blank'>Frequently Asked Questions</a><br />
								This program allows you to ask a question, display the questions and answers, and search questions.</p>
							<p><a href='https://cwops.org/cw-academy/cw-academy-student-resources/' target='_blank'>Student Resources</a><br />
								Use this link to access the curriculum and various practice files. This information is hosted on cwops.org.</p>";
		}	
	}
	bypass:
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><a href='$siteURL/wp-login.php/?action=logout'>Logout</a><br /></p>
						<p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
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
add_shortcode ('program_list', 'program_list_func');
