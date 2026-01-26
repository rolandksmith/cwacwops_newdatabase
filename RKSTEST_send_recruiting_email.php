function rkstest_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	$versionNumber				 	= "1";
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	
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
	$theURL						= "$siteURL/rkstest/";
	$inp_rsave					= '';
	$jobname					= "RKSTest - Send Recruitment Emails";
	$debugLog					= "";
	
	// **ENTER VARIABLES NEEDING DEFINITION HERE
	
	ob_start();
	echo "<div id='cwa-admin-wrapper'>";

	if ($doDebug) {
	 	echo "Initialization Array:<br /><pre>";
		$myStr = print_r($initializationArray, TRUE);
		echo "$myStr</pre><br />";
	}
	
	if ($doDebug) {
		if (isset($_POST)) {
			echo "<br />POST Variables:<br />";
			foreach($_POST as $str_key => $str_value) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value<br />";
				} else {
					echo "Key: $str_key (array)<br />";
				}
			}
		}
		if (isset($_GET)) {
			echo "<br />GET Variables:<br />";
			foreach($_GET as $str_key => $str_value) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value<br />";
				} else {
					echo "Key: $str_key (array)<br />";
				}
			}
		}
	}
	
	$strPass = filter_input(INPUT_POST, 'strpass', FILTER_UNSAFE_RAW) ?: filter_input(INPUT_GET, 'strpass', FILTER_UNSAFE_RAW) ?: "1";
//	$inp_rsave = filter_input(INPUT_POST, 'inp_rsave', FILTER_UNSAFE_RAW) ?: "N";
	$inp_verbose = filter_input(INPUT_POST, 'inp_verbose', FILTER_UNSAFE_RAW) ?: "N";
	$inp_mode = filter_input(INPUT_POST, 'inp_mode' , FILTER_UNSAFE_RAW) ?: "Production";
	$inp_start = filter_input(INPUT_POST, 'inp_start' , FILTER_VALIDATE_INT) ?: filter_input(INPUT_GET, 'inp_start', FILTER_VALIDATE_INT) ?: 0;
	
	
	if ($inp_verbose == 'Y') {
		$doDebug = TRUE;
	}
	if ($inp_mode == 'TESTMODE') {
		$testMode = TRUE;
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
	
	

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$TableName					= "wpw1_cwa_";
		$operatingMode				= 'Testmode';
	} else {
		$extMode					= 'pd';
		$TableName					= "wpw1_cwa_";
		$operatingMode				= 'Production';
	}

//	$student_dal = new CWA_Student_DAL();
//	$advisor_dal = new CWA_Advisor_DAL();
//	$advisorclass_dal = new CWA_Advisorclass_DAL();
	$user_dal = new CWA_User_Master_DAL();

	switch ($strPass) {

		case "1":
			?>
			<div id='strPass-1'>
				<h3><?php echo $jobname; ?></h3>
				<p>Send recruitment emails to the next 100 recipients</p>
				<form method="post" action="<?php echo $theURL; ?>" 
				name="selection_form" ENCTYPE="multipart/form-data">
				<input type="hidden" name="strpass" value="2">

				<table style="border-collapse:collapse;">
				<tr><td>Starting With</td>
					<td><input type = "text" class="formInputText" name="inp_start" value = "0" size="6" maxlength="6"></td>
				<?php echo $testModeOption; ?>
				<tr><td colspan="2"><input class="formInputButton" type="submit" value="Submit" /></td></tr>
				</table>
				</form></p>
			</div>
			<?php
			break;

		case "2": 
		
			if ($doDebug) {
				echo "<br />Case 2<br />";
			}
			?>
			<div id='strapss-2'>
			<h3><?php echo $jobname; ?></h3>
			<h4>Starting Count: <?php echo $inp_start; ?></h4>
			
			<?php
			
			$sql = "select user_ID, 
						   user_call_sign, 
						   user_first_name, 
						   user_last_name, 
						   user_email
					from wpw1_cwa_user_master 
					limit $inp_start, 100";
			$result = $wpdb->get_results($sql);
			if ($result === FALSE || $result === NULL) {
				echo "ran $sql which returne FALSE|NULL<br />";
			} else {
				$numRows = $wpdb->num_rows;
				if ($numRows > 0) {
					foreach($result as $resultRow) {
						$user_ID = $resultRow->user_ID;
						$user_call_sign = $resultRow->user_call_sign;
						$user_first_name = $resultRow->user_first_name;
						$user_last_name = $resultRow->user_last_name;
						$user_email = $resultRow->user_email;
						
						$inp_start++;
						
						// check to see if the user is a bad actor
						if (! checkForBadActor($user_call_sign)) {		// not a badie
							// format the email
							$emailContent = "To: $user_last_name, $user_first_name ($user_call_sign):
<p><b>CWops</b>, the sponsoring organization of CW Academy, and <b>CW Academy</b> 
are currently seeking dedicated volunteers to provide essential technical maintenance 
for our digital platforms. If you are passionate about the amateur radio community 
and possess the technical expertise required, we invite you to apply for one of the 
following volunteer positions:</p>
<p><b>1. DX Marathon Website Maintainer</b><br />
CWops is looking for a volunteer to assist in the ongoing maintenance and optimization 
of the DX Marathon website.<br />
<u>Technical Requirements</u>: Proficiency in Java and C++.<br />
<u>Commitment</u>: Approximately three (3) to five (5) hours per week.</p>
<p><b>2. CW Academy Website Maintainer</b><br />
CW Acadent is seeking technical assistance for the CW Academy website to ensure a
seamless experience for our students and instructors.<br />
<u>Technical Requirements</u>: Strong experience with PHP. Prefer experience in a 
WordPress environment.<br />
<u>Commitment</u>: Approximately three (3) to five (5) hours per week.</p>
<h4>How to Apply</h4>
<p>If you possess the required technical skills and are able to commit the necessary 
time to support these vital programs, please reach out to us.<br /><br />
Contact: Roland Smith, K7OJL <br />Email: rolandksmith@gmail.com<br />
or<br />
Contact: Bob Carter, WR7Q <br />Email: kcgator@gmail.com</p>
<p>Thank you for your continued support of CWOps and the CW Academy mission.</p>";
						

							echo "Sending email to $user_call_sign at ID $user_ID Offset: $inp_start<br />"; 
							$theSubject = "CW Academy -- Seeking Volunteer Website Support";
							$mailResult		= emailFromCWA_v3(array('theRecipient'=>$user_email,
																		'theContent'=>$emailContent,
																		'theSubject'=>$theSubject,
																		'theCc'=>'',
																		'theBcc'=>'',
																		'theAttachment'=>'',
																		'mailCode'=>10,
																		'jobname'=>$jobname,
																		'increment'=>0,
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug));
							if ($mailResult[0] === FALSE) {
								echo "Sending the email FAILED<br />";
							}
							if ($doDebug) {
								echo "$mailResult[1]<br />";
							}
							
						} else {			/// bad actor
							echo "No email to $user_call_sign at offset $inp_start --- Bad Actor<br />";
						}
					}
				} else {
					echo "No more records to process<br />";
				}
			}
			echo "<p><a href='$theURL?inp_start=$inp_start&strpass=2'>Do Again Starting at $inp_start</a></p>";
			echo "</div>";
			break;	
	
	}
	$thisTime 		= current_time('mysql', 1);
	echo "<br /><br /><p>Prepared at $thisTime</p>";
/*
	if ($doDebug) {
		echo "<br />Checking to see if the report is to be saved. inp_rsave: $inp_rsave";
	}
	if ($inp_rsave == 'Y') {
		if ($doDebug) {
			echo "Calling function to save the report as Current Student and Advisor Assignments<br />";
		}
		$storeResult	= storeReportData_v2($jobname,$content);
		if ($storeResult[0] !== FALSE) {
			$reportName	= $storeResult[1];
			$reportID	= $storeResult[2];
			echo "<br />Report stored in reports as $reportName<br />
				  Go to'Display Saved Reports' or url<br/>
				  <a href='$siteURL/cwa-display-saved-report/?strpass=3&token=&inp_id=$reportID' 'target='_blank'>Display Report</a>";
							
							
		} else {
			echo "<br />Storing the report in the reports failed";
		}
	}
*/
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	echo "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
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
		echo "<p>writing to joblog failed</p>";
	}
	echo "</div>";
	return ob_get_clean();
}
add_shortcode ('rkstest', 'rkstest_func');
