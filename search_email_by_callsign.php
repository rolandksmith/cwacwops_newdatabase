function search_email_by_callsign_func() {

// modified 13Jul23 by Roland to use consolidated tables

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
	$theURL						= "$siteURL/cwa-search-saved-email-by-call-sign/";
	$inp_semester				= '';
	$inp_callsign				= '';
	$jobname					= 'Search Email by Callsign';

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
		$emailTableName				= "wpw1_cwa_testmode_email";
	} else {
		$extMode					= 'pd';
		$advisorTableName			= "wpw1_cwa_consolidated_advisor";
		$studentTableName			= "wpw1_cwa_consolidated_student";
		$emailTableName				= "wpw1_cwa_production_email";
	}
	
	$searchArray					= array($advisorTableName,$studentTableName);



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Search Email by Callsign</h3>
<p>Enter the callsign of interest. The system will obtain, if available, the email 
address by looking into the advisor, past advisor, student, and past student tables. 
The system will then display a list of emails to that person in order from newest to oldest email.</p></p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td>Call Sign</td>
	<td><input type='text' class='formInputText' name='inp_callsign' size='20' maxlength='20' required></td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "At pass 2 with $inp_callsign<br />";
		}
		
		//// search tables for the email address associated with the call sign
		$emailFound			= FALSE;
		foreach($searchArray as $tableName) {
 			if (!$emailFound) {
				$sql			= "select distinct(email) from $tableName 
									where call_sign='$inp_callsign' 
									order by date_created ";
				$result			= $wpdb->get_results($sql);
				if ($result === FALSE) {
					if ($doDebug) {
						echo "Running $sql failed<br />";
						echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
						echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
					}
				} else {
					$numRows						= $wpdb->num_rows;
					if ($numRows > 0) {
						foreach($result as $resultRows) {
							$thisEmail				= $resultRows->email;
						
							if ($doDebug) {
								echo "Found email: $thisEmail for $inp_callsign<br />";
							}
							$emailFound		= TRUE;
						}
					}
				}
			}
		}
		if ($emailFound) {
			$searchSQL		= "select * from $emailTableName 
								where email_to like '%$thisEmail%' 
								order by email_sent DESC";


			$emailResults		= $wpdb->get_results($searchSQL);
			if ($emailResults === FALSE) {
				if ($doDebug) {
					echo "Error selecting data from $emailTableName table<br />
						  wpdb->last_query: " . $wpdb->last_query . "<br />
						  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
				$content		.= "No data found in $emailTableName for $inp_callsign";
			} else {
				$numberERows	= $wpdb->num_rows;
				if ($numberERows < 1) {
					if ($doDebug) {
						echo "$numberERows found in $emailTableName table. No data to process<br />";
					}
					$content	.= "No data found in $emailTableName";				
				} else {
					/// Now display the list of subjects and allow selection
					$content			 .= "<h3>Saved Email to $inp_callsign at $thisEmail</h3>
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
			echo "arrived at pass 5 with inp_email_id: $inp_email_id and inp_forward: $inp_forward<br />";
		}
		//// get the email from the database
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
	$result			= write_joblog_func("Search Email by Callsign|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('search_email_by_callsign', 'search_email_by_callsign_func');
