function this_is_a_function_func() {

	global $wpdb, $doDebug, $debugLog;

	$doDebug						= TRUE;
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

	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
		$wpdb->show_errors();
	} else {
		$wpdb->hide_errors();
	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/CHANGE THIS/";
	$inp_rsave					= '';
	$jobname					= "FIX THIS V$versionNumber";
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
					echo "Key: $str_key | Value: $str_value";
				} else {
					echo "Key: $str_key (array)";
				}
			}
		}
		if (isset($_GET)) {
			echo "<br />GET Variables:<br />";
			foreach($_GET as $str_key => $str_value) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value";
				} else {
					echo "Key: $str_key (array)";
				}
			}
		}
	}
	
	$strPass = filter_input(INPUT_POST, 'strpass', FILTER_UNSAFE_RAW) ?: filter_input(INPUT_GET, 'strpass', FILTER_UNSAFE_RAW) ?: "1";
	$inp_rsave = filter_input(INPUT_POST, 'inp_rsave', FILTER_UNSAFE_RAW) ?: "N";
	$inp_verbose = filter_input(INPUT_POST, 'inp_verbose', FILTER_UNSAFE_RAW) ?: "N";
	$inp_mode = filter_input(INPUT_POST, 'inp_mode' , FILTER_UNSAFE_RAW) ?: "Production";
	
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

	$student_dal = new CWA_Student_DAL();
	$advisor_dal = new CWA_Advisor_DAL();
	$advisorclass_dal = new CWA_Advisorclass_DAL();
	$user_dal = new CWA_User_Master_DAL();

	switch ($strPass) {

		case "1":
			?>
			<div id='strPass-1'>
				<h3><?php echo $jobname; ?></h3>
				<p>**PROGRAM EXPLANATION HERE</p>
				<form method='post' action='$theURL' 
				name='selection_form' ENCTYPE='multipart/form-data'>
				<input type='hidden' name='strpass' value='2'>

				<table style='border-collapse:collapse;'>

				<?php echo $testModeOption; ?>
				<tr><td>Save this report to the reports achive?</td>
				<td><input type='radio' class='formInputButton' name='inp_rsave' value='N' checked='checked'> Do not save the report<br />
					<input type='radio' class='formInputButton' name='inp_rsave' value='Y'> Save a copy of the report the report</td></tr>
				<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
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
			
			
			</div>
			<?php 
			break;	
	
	}
	$thisTime 		= current_time('mysql', 1);
	echo "<br /><br /><p>Prepared at $thisTime</p>";

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
add_shortcode ('this_is_a_function', 'this_is_a_function_func');
