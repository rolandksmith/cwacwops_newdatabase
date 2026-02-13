function send_an_email_to_a_list_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];
/*
	if ($validUser == 'N') {				// turn off debug and testmode
		$doDebug					= FALSE;
		$testMode					= FALSE;
	}
*/
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
	
//	CHECK THIS!								//////////////////////
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-send-an-email-to-a-list/";
	$offset						= 0;
	$emailLimit					= 100;
	$emailCount					= 0;
	$jobname					= "Send an Email to a List V$versionNumber";
	$inp_email					= '';
	$inp_attachment				= '';
	$inp_who					= '';
	$inp_where					= '';
	$increment					= 0;

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
			if ($str_key 		== "inp_subject") {
				$inp_subject	 = $str_value;
				$inp_subject	 = filter_var($inp_subject,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_text") {
				$inp_text	 = stripslashes($str_value);
				$inp_text	 = filter_var($inp_text,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_path") {
				$inp_path	 = stripslashes($str_value);
//				$inp_path	 = filter_var($inp_path,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_names") {
				$inp_names	 = $str_value;
//				$inp_names	 = filter_var($inp_names,FILTER_UNSAFE_RAW);
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

	if ("1" == $strPass) {
		$content 		.= "<h3>$jobname</h3>
							<p>This job will send an email to a comma delimited
							list of recipients. The list should come from New Student 
							Report Generator or other programs specifically designed to 
							prepare an appropriate list. Each line should contain, in this order, 
							Last Name, First Name, Callsign, Email address, Level.</p>
							<p>The list should stored in Uploads (/home/cwacwops/public_html/wp-content/uploads/)</p>
							<form method='post' action='$theURL' 
							name='main_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td style='vertical-align:top;'>Full path of recipient list and file name:</td>
								<td><input type='text'class='formInputText' name='inp_path' size='50' maxlength='100'></td></tr>
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
	

	
///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		if ($doDebug)	{
			echo "<br />Arrived at pass $strPass with<br />
					inp_path: $inp_path<br />";
		}
		$content		.= "<h3>$jobname</h3>";
		// get the contents of the file and send the emails

		$fileToRead	= "/home/cwacwops/public_html/wp-content/uploads/$inp_path";
		if ($doDebug) {
			echo "fileToRead: $fileToRead<br />";
		}
		$row = 0;
		if (($handle = fopen($fileToRead, "r")) !== FALSE) {
			if ($doDebug) {
				echo "$fileToRead is open<br />";
			}
			$content .= "<p>Deselect anyone below who should not receive an email</p>
						<p>Enter below the subject line and the text of the 
						message to be sent. To include the level in the email, use '*level*'. 
						The first line of the email will be:<br />
						To: &lt;last name&gt;, &lt;first name&gt; (callsign):</p>
						<form method='post' action='$theURL' 
						name='main_form' ENCTYPE='multipart/form-data'>
						<input type='hidden' name='strpass' value='3'><table>";
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$row++;
				if ($row > 1) {
					$num = count($data);
					if ($num == 5) {
						$last_name		= $data[0];
						$first_name		= $data[1];
						$callsign		= $data[2];
						$email			= $data[3];
						$level			= $data[4];

						$content			.= "<tr><td><input type='checkbox' class='formInputButton'
												 name='inp_names[]' 
												 value='$last_name, $first_name ($callsign)&$email&$level' 
												 checked>$last_name, $first_name ($callsign) $level</td>
													<td></td></tr>\n";
					} else {
						if ($doDebug) {
							echo "row $row did not have 4 elements, had $count elements<br />";
						}
					}
				}
			}
			fclose($handle);
			$content		.= "<tr><td style='vertical-align:top;'>Email Subject:</td>
									<td><input type='text' class='formInputText' name='inp_subject' size='50' maxlength='100'></td></tr>
								<tr><td style='vertical-align:top;'>Email Text:</td>
									<td><textarea class='formInputText' name='inp_text' rows='5' cols='50'></textarea></td></tr>
								<tr><td colspan='2'><input type='submit' class='formInputButton' value='submit' /></td></tr></table></form>";
		} else {
			if ($doDebug) {
				echo "unable to open $fileToRead<br />";
			}
			$content	.= "<p>Unable to open the input file</p>";
		}




	} elseif ("3" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass $strPass<br />";
		}
		
//		$testmode			= TRUE;
		
		$content	.= "<h3>$jobname</h3>";
		foreach($inp_names as $thisKey => $thisValue) {
			$myArray		= explode("&",$thisValue);
			$line1			= $myArray[0];
			$theRecipient	= $myArray[1];
//			$theRecipient	= "rolandksmith@gmail.com";
			$theLevel		= $myArray[2];
			$theContent		= "To: $line1<p>$inp_text</p><br />73,<br />CW Academy";
			$theContent		= str_replace("*level*","$theLevel",$theContent);
			$theCc			= '';
			$mailCode		= 13;
			$increment		= 0;
			
			if ($testMode) {
				$theRecipient	= "rolandksmith@gmail.com";
				$mailCode		= 1;
				$increment++;
				$inp_subject	= "TESTMODE $inp_subject";
			}
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
														'theSubject'=>$inp_subject,
														'theContent'=>$theContent,
														'theCc'=>$theCc,
														'theAttachment'=>'',
														'mailCode'=>$mailCode,
														'jobname'=>$jobname,
														'increment'=>$increment,
														'testMode'=>$testMode,
														'doDebug'=>$doDebug));
			if ($mailResult === FALSE) {
				if ($doDebug) {
					echo "email to $theRecipient failed<br />";
				}
				$content		.= "Email to $theRecipient failed<br />";
			} else {
				$emailCount++;
				$content		.= "Email sent to $theRecipient<br />";
			}
		}
		$content				.= "<p>$emailCount emails sent</p>";

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
	if ($result === FALSE) {
		$content	.= "<p>writing to joblog failed</p>";
	}

	return $content;


}
add_shortcode ('send_an_email_to_a_list', 'send_an_email_to_a_list_func');

