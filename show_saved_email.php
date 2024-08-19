function show_saved_email_func() {

/*	Change log
	Modified 23Nov23 by Roland to have two capabilities:
		Show emails by Subject line
		Show most recent emails
*/

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
	$versionNumber		= '2';
	
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
	$theURL						= "$siteURL/cwa-show-saved-email/";
	$subjectCount				= 0;
	$emailSubjectArray			= array();
	$theSubject					= "";
	$endTheSession			 	= FALSE;
	$inp_forward				= '';
	$inp_additional				= "";
	$jobname					= "Show Saved Email v$versionNumber";
	$jobNameArray				= array();
	$offset						= 0;

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
			if ($str_key 		== "the_subject") {
				$the_subject	 = $str_value;
				$the_subject	 = filter_var($the_subject,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "the_jobname") {
				$the_jobname	 = $str_value;
				$the_jobname	 = filter_var($the_jobname,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_email_id") {
				$inp_email_id	 = $str_value;
				$inp_email_id	 = filter_var($inp_email_id,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_forward") {
				$inp_forward	 = $str_value;
				$inp_forward	 = filter_var($inp_forward,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_run_type") {
				$inp_run_type	 = $str_value;
				$inp_run_type	 = filter_var($inp_run_type,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_additional") {
				$inp_additional	 = $str_value;
				$inp_additional	 = filter_var($inp_additional,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "offset") {
				$offset	 = $str_value;
				$offset	 = filter_var($offset,FILTER_UNSAFE_RAW);
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
		$emailTableName				= "wpw1_cwa_testmode_email";
	} else {
		$extMode					= 'pd';
		$emailTableName				= "wpw1_cwa_production_email";
	}



	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
<p>Select the display method. Display by Email Subject will show all subjects 
and the latest few emails with that subject. Clicking on the count will show 
a full list of all emails with that subject.</p>
<p>Display Most Recent Emails will show a list of the 50 most recent 
emails, newest to oldest. There is a link at the bottom of the page to 
display the next 50 emails.</p>
<form method='post' action='$theURL' 
name='selection_form' ENCTYPE='multipart/form-data'>
<input type='hidden' name='strpass' value='2'>
<table style='border-collapse:collapse;'>
<tr><td style='vertical-align:top;'>Display Method</td>
	<td><input type='radio' class='formInputButton' name='inp_run_type' value='subject'> Display by Email Subject<br />
		<input type='radio' class='formInputButton' name='inp_run_type' value='date'> Display 50 Most Recent Emails</td></tr>
$testModeOption
<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
</form></p>";

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at Pass $strPass with<br />
					inp_run_type: $inp_run_type<br />";
		}
		
		
		if ($inp_run_type == 'subject') {	
			$content		.= "<h3>Show Saved Email</h3>
								<p>Click on the count to see all emails from the job name with the subject</p>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='4'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<table style='border-collapse:collapse;'>
								<tr><th>Job Name</th>
									<th>Subject</th>
									<th>Count</th></tr>";
			$jobNameArray			= array();
			$sql				= "SELECT email_jobname,email_subject 
									FROM $emailTableName 
									order by email_jobname, 
										email_subject, 
										email_sent DESC";
			if ($doDebug) {
				echo "<br />doing first pass<br />";
			}
			$emailSubjects			= $wpdb->get_results($sql);
			if ($emailSubjects === FALSE) {
				if ($doDebug) {
					$lastError		= $wpdb->last_error;
					$lastQuery		= $wpdb->last_query;
					echo "No data found in $emailTableName table. Error: $lastError<br />
							SQL: $lastQuery";
				}
				$content			.= "No data found in $emailTableName";
			} else {
				$numberERows		= $wpdb->num_rows;
				if ($numberERows < 1) {
					if ($doDebug) {
						echo "$numberERows found in $emailTableName table. No data to process<br />";
					}
					$content		.= "No data found in $emailTableName";				
				} else {
					$jobNameIndex			= 0;
					$prevJobName			= '';
					$prevSubject			= '';
					foreach($emailSubjects as $emailSubjectRow) {
						$email_subject		= $emailSubjectRow->email_subject;
						$email_jobname		= $emailSubjectRow->email_jobname;
					
						if ($doDebug) {
							echo "<br />Have a jobname of $email_jobname and a subject of $email_subject<br />";
						}
						if ($email_jobname != $prevJobName || $email_subject != $prevSubject) {
							$prevJobName										= $email_jobname;
							$prevSubject										= $email_subject;
							$jobNameIndex++;
							if (!array_key_exists($email_jobname,$jobNameArray)) {
								$jobNameArray[$email_jobname][$jobNameIndex]	= $email_subject;
								if ($doDebug) {
									echo "added $email_jobname to jobNameArray<br />";
									echo "added $email_subject at index $jobNameIndex to $email_jobname array<br />";
								}
							} else {
								$jobNameArray[$email_jobname][$jobNameIndex]	= $email_subject;
								if ($doDebug) {
									echo "added $email_subject at index $jobNameIndex to $email_jobname array<br />";
								}
							}
						}
					}
				}
			}
			ksort($jobNameArray);
			if ($doDebug) {
				echo "<br />jobNameArray:<br /><pre>";
				print_r($jobNameArray);
				echo "</pre><br />";
			}

			$prevSubject					= '';			
			foreach($jobNameArray as $thisKey=>$thisValue) {						
				if ($doDebug) {
					echo "<br />Got jobname of $thisKey<br />";						
				}
				foreach($thisValue as $myInt=>$thisSubject) {
					//// count the number of emails for this subject and put in subject array
					$sql			= "SELECT count(email_id) as email_count 
										from $emailTableName 
										where email_jobname='$thisKey' 
										and email_subject = '$thisSubject'";
					$email_count	= $wpdb->get_var($sql);
					$content		.= "<tr><td><b>$thisKey</b></td>
										<td><b>$thisSubject</b></td>
										<td text-align:center;'><a href='$theURL?the_subject=$thisSubject&the_jobname=$thisKey&strpass=3&inp_mode=$inp_mode&inp_verbose=$inp_verbose'>$email_count</a></td></tr>";
					/// Now get the first five emails
					$sql			= "select email_id, 
											  email_to, 
											  email_sent 
										from $emailTableName 
										where email_jobname='$thisKey' 
										and email_subject='$thisSubject' 
										order by email_sent DESC 
										limit 5"; 
					$emailSubjects			= $wpdb->get_results($sql);
					if ($emailSubjects === FALSE) {
						if ($doDebug) {
							$lastError		= $wpdb->last_error;
							$lastQuery		= $wpdb->last_query;
							echo "No data found in $emailTableName table. Error: $lastError<br />
									SQL: $lastQuery<br />";
						}
						$content			.= "No data found in $emailTableName";
					} else {
						$numberERows		= $wpdb->num_rows;
						if ($numberERows < 1) {
							if ($doDebug) {
								echo "$numberERows found in $emailTableName table. No data to process<br />";
							}
							$content		.= "No data found in $emailTableName";				
						} else {
							$content	.= "<tr><td colspan='3'><table style='border-collapse:collapse;'>";
							foreach($emailSubjects as $emailSubjectRow) {
								$email_id	= $emailSubjectRow->email_id;
								$email_to	= trim($emailSubjectRow->email_to);
								$email_sent	= trim($emailSubjectRow->email_sent);
						
								$content	.= "<tr><td style='width:50px;'><input type='radio' style='formInputButton' name='inp_email_id' value='$email_id'></td>
												<td style='width:440px;'>To: $email_to</td>
												<td>Date: $email_sent</td></tr>";
							}
							$content				.= "</table></tr></td>";
						}
					}
				}
			}
			$content	.= "<tr><td colspan='3'><input class='formInputButton' type='submit' value='Submit' /></td></tr>
							</table></form>";
		} elseif ($inp_run_type == 'date') {


			$content	.= "<h3>$jobname</h3>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='4'>
							<input type='hidden' name='inp_mode' value='$inp_mode'>
							<input type='hidden' name='inp_verbose' value='$inp_verbose'>
							<table style='border-collapse:collapse;width:1200px;'>
							<tr><th>Job Name</th>
								<th>Subject</th>
								<th>To</th>
								<th>Date Sent</th></tr>";

			$sql				= "SELECT * FROM $emailTableName 
									order by email_sent DESC 
										limit 50 offset $offset";
			$emailSubjects			= $wpdb->get_results($sql);
			if ($emailSubjects === FALSE) {
				if ($doDebug) {
					$lastError		= $wpdb->last_error;
					$lastQuery		= $wpdb->last_query;
					echo "No data found in $emailTableName table. Error: $lastError<br />
							SQL: $lastQuery";
				}
				$content			.= "No data found in $emailTableName";
			} else {
				$numberERows		= $wpdb->num_rows;
				if ($numberERows < 1) {
					if ($doDebug) {
						echo "$numberERows found in $emailTableName table. No data to process<br />";
					}
					$content		.= "No data found in $emailTableName";				
				} else {
					foreach($emailSubjects as $emailSubjectRow) {
						$email_id			= $emailSubjectRow->email_id;
						$email_subject		= $emailSubjectRow->email_subject;
						$email_jobname		= $emailSubjectRow->email_jobname;
						$email_to			= $emailSubjectRow->email_to;
						$email_sent			= $emailSubjectRow->email_sent;
					
						if ($doDebug) {
							echo "<br />Have a jobname of $email_jobname and a subject of $email_subject<br />";
						}
						$content	.= "<tr><td><input type='radio' style='formInputButton' name='inp_email_id' value='$email_id'> $email_jobname</td>
											<td>$email_subject</td>
											<td>$email_to</td>
											<td>$email_sent</td></tr>";
						$offset++;
					}
					$content		.= "<tr><td colspan='4'><input type='submit' class='formInputButton' name='submit' value='Display Selected Email'></td></tr>
										</table></form><br /><br />
										<form method='post' action='$theURL' 
										name='selection_form' ENCTYPE='multipart/form-data'>
										<input type='hidden' name='strpass' value='2'>
										<input type='hidden' name='inp_mode' value='$inp_mode'>
										<input type='hidden' name='inp_verbose' value='$inp_verbose'>
										<input type='hidden' name='offset' value='$offset'>
										<input type='hidden' name='inp_run_type' value='$inp_run_type'>
										<table style='border-collapse:collapse;'>
										<tr><td><input type='submit' class='formInputButton' name='submit' value='Show Next 50'></td></tr>
										</table></form>";
				}
			}
		} else {
			$content		.= "Nothing requested";
		}
		
		
	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />at pass $strPass with<br />
					the_subject of $the_subject<br />
					the_jobname of $the_jobname<br />";
		}
		/// get the requested emails and display. 
		$sql			= "select * from $emailTableName 
							where email_subject = '$the_subject' 
							and email_jobname = '$the_jobname' 
							order by email_sent DESC";
		$emailData		= $wpdb->get_results($sql);
		if ($emailData === FALSE) {
			if ($doDebug) {
				$lastError		= $wpdb->last_error;
				$lastQuery		= $wpdb->last_query;
				echo "No data found in $emailTableName table. Error: $lastError<br />
						SQL: $lastQuery";
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
				if ($doDebug) {
					echo "reading from $emailTableName retrieved $numberERows records<br />";
				}
				$content	.= "<h3>Show Saved Email - $the_jobname - $the_subject</h3>
								<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='4'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
								<table>
								<tr><th>Select</th>
									<th>Date</th>
									<th>To</th>
									<th>CC</th>
									<th>BCC</th></tr>";
				foreach($emailData as $emailDataRow) {
					$email_ID			= $emailDataRow->email_id;
					$email_sent			= $emailDataRow->email_sent;
					$email_to			= $emailDataRow->email_to;
					$email_cc			= $emailDataRow->email_cc;
					$email_bcc			= $emailDataRow->email_bcc;
					$email_tcc			= $emailDataRow->email_tcc;
					$email_content		= $emailDataRow->email_content;
					$email_jobname		= $emailDataRow->email_jobname;

//					$emailSnippet		= substr($email_content,0,100);
					$content			.= "<tr><td style='vertical-align:top;'><input type='radio' style='formInputButton' name='inp_email_id' value='$email_ID' required></td>
												<td style='vertical-align:top;'>$email_sent</td>
												<td style='vertical-align:top;'>$email_to</td>
												<td style='vertical-align:top;'>$email_cc</td>
												<td style='vertical-align:top;'>$email_bcc</td></tr>";
				}
				$content				.= "<tr><td colspan='6'>
											<input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
											</form>";
			}
		}	





	} elseif ("4" == $strPass) {
		if ($doDebug) {
			echo "at pass $strPass with email_id of $inp_email_id<br />";
		}
		$sql			= "select * from $emailTableName 
							where email_id = $inp_email_id";
		$emailData		= $wpdb->get_results($sql);
		if ($emailData === FALSE) {
			if ($doDebug) {
				$lastError		= $wpdb->last_error;
				$lastQuery		= $wpdb->last_query;
				echo "No data found in $emailTableName table. Error: $lastError<br />
						SQL: $last_query";
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
				$content	.= "<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>
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
			echo "arrived at pass $strPass with<br />
				inp_email_id: $inp_email_id<br />
				inp_forward: $inp_forward<br />";
		}
		//// get the email from the database
		$sql			= "select * from $emailTableName 
							where email_id = $inp_email_id";
		$emailData		= $wpdb->get_results($sql);
		if ($emailData === FALSE) {
			if ($doDebug) {
				$lastError			= $wpdb->last_error;
				$lastQuery			= $wpdb->last_query;
				echo "No data found in $emailTableName table. Error: $lastError<br />
						SQL: $lastQuery";
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
				$content	.= "<form method='post' action='$theURL' 
								name='selection_form' ENCTYPE='multipart/form-data'>
								<input type='hidden' name='strpass' value='3'>
								<input type='hidden' name='inp_mode' value='$inp_mode'>
								<input type='hidden' name='inp_verbose' value='$inp_verbose'>'";

				foreach($emailData as $emailDataRow) {
					$email_ID			= $emailDataRow->email_id;
					$email_sent			= $emailDataRow->email_sent;
					$email_subject		= $emailDataRow->email_subject;
					$email_to			= $emailDataRow->email_to;
					$email_cc			= $emailDataRow->email_cc;
					$email_bcc			= $emailDataRow->email_bcc;
					$email_tcc			= $emailDataRow->email_tcc;
					$email_content		= $emailDataRow->email_content;
					
					$theContent			= "$inp_additional
											<table>
											<tr><td style='width:100px;vertical-align:top;'><b>Date:</b></td>
												<td>$email_sent</td></tr>
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('show_saved_email', 'show_saved_email_func');
