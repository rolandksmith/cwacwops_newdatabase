function search_sent_email_by_callsign_or_email_func() {

// modified 13Jul23 by Roland to use consolidated tables
// Created from Search Email by Callsign on 5Feb24 by Roland
// Modified 25Oct24 by Roland for new database

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-search-sent-email-by-callsign-or-email/";
	$inp_semester				= '';
	$inp_callsign				= '';
	$inp_email					= '';
	$jobname					= 'Search Sent Email by Callsign or Email';

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
			if ($str_key 		== "inp_email_id") {
				$inp_email_id	 = $str_value;
				$inp_email_id	 = filter_var($inp_email_id,FILTER_UNSAFE_RAW);
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
				$inp_callsign	 = strtoupper(filter_var($inp_callsign,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_email") {
				$inp_email	 = $str_value;
				$inp_email	 = strtoupper(filter_var($inp_email,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_additional") {
				$inp_additional	 = $str_value;
				$inp_additional	 = strtoupper(filter_var($inp_additional,FILTER_UNSAFE_RAW));
			}
			if ($str_key 		== "inp_forward") {
				$inp_forward	 = $str_value;
				$inp_forward	 = strtoupper(filter_var($inp_forward,FILTER_UNSAFE_RAW));
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
		$userMasterTableName		= 'wpw1_cwa_user_master2';
		$emailTableName				= "wpw1_cwa_testmode_email";
	} else {
		$extMode					= 'pd';
		$userMasterTableName		= 'wpw1_cwa_user_master';
		$emailTableName				= "wpw1_cwa_production_email";
	}
	

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Search Sent Email by Callsign or Email</h3>
							<p>If searching by callsign, enter the callsign of interest. The system will obtain, if available, the email 
							address by looking into the user_master tables.</p>
							<p>Otherwise, enter the email address of interest.</p> 
							The system will then display a list of emails to that person in order from newest to oldest email.</p></p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Call Sign</td>
								<td><input type='text' class='formInputText' name='inp_callsign' size='20' maxlength='20' autofocus></td></tr>
							<tr><td>Email Address</td>
								<td><input type='text' class='formInputText' name='inp_email' size='20' maxlength='50'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />At pass 2 with callsign $inp_callsign or email $inp_email<br />";
		}
		$thisReqStr					= '';
		
		if ($inp_callsign != '') {
			// look in user_master to get the email address
			$sql 			= "select * from $userMasterTableName 
								where user_call_sign = '$inp_callsign'";
			$sqlResult		= $wpdb->get_results($sql);
			if ($sqlResult === FALSE) {
				handleWPDBError($jobname,$doDebug);
			} else {
				$numRows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $sql<br />and retrieved $numRows rows<br />";
				}
				if ($numRows > 0) {
					foreach($sqlResult as $sqlRow) {
						$user_id				= $sqlRow->user_ID;
						$user_callsign			= $sqlRow->user_call_sign;
						$user_first_name		= $sqlRow->user_first_name;
						$user_last_name			= $sqlRow->user_last_name;
						$user_email				= $sqlRow->user_email;
						$user_phone				= $sqlRow->user_phone;
						$user_city				= $sqlRow->user_city;
						$user_state				= $sqlRow->user_state;
						$user_zip_code			= $sqlRow->user_zip_code;
						$user_country_code		= $sqlRow->user_country_code;
						$user_whatsapp			= $sqlRow->user_whatsapp;
						$user_telegram			= $sqlRow->user_telegram;
						$user_signal			= $sqlRow->user_signal;
						$user_messenger			= $sqlRow->user_messenger;
						$user_action_log		= $sqlRow->user_action_log;
						$user_timezone_id		= $sqlRow->user_timezone_id;
						$user_languages			= $sqlRow->user_languages;
						$user_survey_score		= $sqlRow->user_survey_score;
						$user_is_admin			= $sqlRow->user_is_admin;
						$user_role				= $sqlRow->user_role;
						$user_date_created		= $sqlRow->user_date_created;
						$user_date_updated		= $sqlRow->user_date_updated;
						
						$thisEmail				= $user_email;
						$emailFound				= TRUE;
						$thisReqStr				= $inp_callsign;
					}
				}
			}

		} elseif ($inp_email != '') {
			$thisEmail		= $inp_email;
			$emailFound		= TRUE;
			$thisReqStr		= $inp_email;
		}
		if ($emailFound) {
			$searchSQL		= "select * from $emailTableName 
								where email_to like '%$thisEmail%' 
								order by email_sent DESC";


			$emailResults		= $wpdb->get_results($searchSQL);
			if ($emailResults === FALSE) {
				handleWPDBError($jobname,$doDebug);
				$content		.= "No data found in $emailTableName for $inp_callsign";
			} else {
				$numberERows	= $wpdb->num_rows;
				if ($doDebug) {
					echo "ran $searchSQL<br />and $numberERows found in $emailTableName table. No data to process<br />";
				}
				if ($numberERows < 1) {
					$content	.= "No data found in $emailTableName";				
				} else {
					/// Now display the list of subjects and allow selection
					$content			 .= "<h3>Saved Email to $thisReqStr</h3>
												<p>The email sent to $inp_callsign are below listed from newest to oldest.  
												Select the email of interest and click 'Submit' or just press Enter</p>
												<form method='post' action='$theURL' 
												name='selection_form' ENCTYPE='multipart/form-data'>
												<input type='hidden' name='strpass' value='4'>
												<table>
												<tr><th>Select</th>
													<th>Date</th>
													<th>To</th>
													<th>CC</th>
													<th>Subject</th>
													<th>Job Name</th></tr>";

					foreach($emailResults as $emailDataRow) {
						$email_ID			= $emailDataRow->email_id;
						$email_sent			= $emailDataRow->email_sent;
						$email_subject		= $emailDataRow->email_subject;
						$email_to			= $emailDataRow->email_to;
						$email_cc			= $emailDataRow->email_cc;
						$email_jobname		= $emailDataRow->email_jobname;
						$content			.= "<tr><td style='vertical-align:top;'><input type='radio' style='formInputButton' name='inp_email_id' value='$email_ID' required></td>
													<td style='vertical-align:top;'>$email_sent</td>
													<td style='vertical-align:top;'>$email_to</td>
													<td style='vertical-align:top;'>$email_cc</td>
													<td style='vertical-align:top;'>$email_subject</td>
													<td style='vertical-align:top;'>$email_jobname</td></tr>";
					}
					$content				.= "<tr><td colspan='6'>
												<input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
												</form>";
				}
			}
		} else {
			$content		.= "No email address found for $inp_callsign";
		}
	
	
	
	} elseif ("4" == $strPass) {	
		if ($doDebug) {
			echo "at pass 4 with email_id of $inp_email_id<br />";
		}
		$sql			= "select * from $emailTableName 
							where email_id = $inp_email_id";
		$emailData		= $wpdb->get_results($sql);
		if ($emailData === FALSE) {
			if ($doDebug) {
				echo "No data found in $emailTableName table<br />
					  wpdb->last_query: " . $wpdb->last_query . "<br />
					  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
			$content		.= "No data found in $emailTableName";
		} else {
			$numberERows	= $wpdb->num_rows;
			if ($numberERows < 1) {
				if ($doDebug) {
					echo "$numberERows found in $emailTableName table. No data to process<br />";
				}
				$content	.= "No data found in $emailTableName";				
			} else {
				$content				.= "<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='5'>";

				foreach($emailData as $emailDataRow) {
					$email_ID			= $emailDataRow->email_id;
					$email_sent			= $emailDataRow->email_sent;
					$email_to			= $emailDataRow->email_to;
					$email_subject		= $emailDataRow->email_subject;
					$email_cc			= $emailDataRow->email_cc;
					$email_bcc			= $emailDataRow->email_bcc;
					$email_tcc			= $emailDataRow->email_tcc;
					$email_content		= $emailDataRow->email_content;
					$email_jobname		= $emailDataRow->email_jobname;

					$content			.= "<h3>Show Saved Email</h3>
											<table>
											<tr><td style='width:100px;vertical-align:top;'><b>Date:</b></td>
												<td>$email_sent</td></tr>
											<tr><td style='vertical-align:top;'><b>Job Name:</td>
												<td>$email_jobname</td></tr>
											<tr><td style='vertical-align:top;'><b>Subject:</td>
												<td>$email_subject</td></tr>
											<tr><td style='vertical-align:top;'><b>To:</b></td>
												<td>$email_to</td></tr>
											<tr><td style='vertical-align:top;'><b>CC:</b></td>
												<td>$email_cc</td></tr>
											<tr><td style='vertical-align:top;'><b>Bcc:</b></td>
												<td>$email_bcc</td></tr>
											<tr><td style='vertical-align:top;'><b>Tcc:</b></td>
												<td>$email_tcc</td></tr>
											<tr><td style='vertical-align:top;'><b>Content:</b></td>
												<td>$email_content</td></tr>
											</table>
											<input type='hidden' name='inp_email_id' value='$email_ID'>
											<p>To forward this email, enter the email address(s) and click on 'Forward'. Otherwise 
											you can close this window.
											<br /><input type='text' class='formInputText' name='inp_forward' size='50' maxlength='150'>
											<br />Additional Comments:<br />
											<textarea class='formInputText' name='inp_additional' rows='5' cols='50'></textarea>
											<br /><br /><input class='formInputButton' type='submit' value='Forward' /></form></p>";
				}
			}
		}
	} elseif ("5" == $strPass) {
		if ($doDebug) {
			echo "<br />arrived at pass 5 with inp_email_id: $inp_email_id and inp_forward: $inp_forward<br />";
		}
		//// get the email from the database
		$sql			= "select * from $emailTableName 
							where email_id = $inp_email_id";
		$emailData		= $wpdb->get_results($sql);
		if ($emailData === FALSE) {
			handleWPDBError($jobname,$doDebug);
			$content		.= "No data found in $emailTableName";
		} else {
			$numberERows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and $numberERows records were found<br />";
			}
			if ($numberERows < 1) {
				$content	.= "No data found in $emailTableName";				
			} else {
				$content				.= "<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='3'>";

				foreach($emailData as $emailDataRow) {
					$email_ID			= $emailDataRow->email_id;
					$email_sent			= $emailDataRow->email_sent;
					$email_subject		= $emailDataRow->email_subject;
					$email_to			= $emailDataRow->email_to;
					$email_cc			= $emailDataRow->email_cc;
					$email_bcc			= $emailDataRow->email_bcc;
					$email_tcc			= $emailDataRow->email_tcc;
					$email_content		= $emailDataRow->email_content;
					$email_jobname		= $emailDataRow->email_jobname;
					
					$theContent			= "$inp_additional
											<table>
											<tr><td style='width:100px;vertical-align:top;'><b>Date:</b></td>
												<td>$email_sent</td></tr>
											<tr><td style='vertical-align:top;'><b>Job Name:</td>
												<td>$email_jobname</td></tr>
											<tr><td style='vertical-align:top;'><b>Subject:</td>
												<td>$email_subject</td></tr>
											<tr><td style='vertical-align:top;'><b>To:</b></td>
												<td>$email_to</td></tr>
											<tr><td style='vertical-align:top;'><b>CC:</b></td>
												<td>$email_cc</td></tr>
											<tr><td style='vertical-align:top;'><b>Content:</b></td>
												<td>$email_content</td></tr>
											</table>";
					$theSubject		= "FORWARD: $email_subject";
					if ($testMode) {
						$emailCode	= 5;
						$theSubject	= "TESTMODE $theSubject";
					} else {
						$mailCode	= 15;
					}
					$increment		= 0;
					$emailArray		= array('theRecipient'=>$inp_forward,
											'theSubject'=>$theSubject,
											'jobname'=>$jobname,
											'theContent'=>$theContent,
											'mailCode'=>$mailCode,
											'increment'=>$increment,
											'testMode'=>$testMode,
											'doDebug'=>$doDebug);
					$mailResult		= emailFromCWA_v2($emailArray);
					if ($mailResult === FALSE) {
						$content	.= "Forwarded email failed to send";
					} else {
						$content	.= "Email forwarded to $inp_forward";
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
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
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
add_shortcode ('search_sent_email_by_callsign_or_email', 'search_sent_email_by_callsign_or_email_func');

