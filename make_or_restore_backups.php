function make_or_restore_backups_func() {
	global $wpdb;

	// --- SECTION 1: INITIALIZATION ---
	$initializationArray = data_initialization_func();

	$siteURL      = $initializationArray['siteurl'];
	$userName     = $initializationArray['userName'];
	$jobname	  = "Make or Restore Backups";
	$versionNumber = '1';
	$theURL       = "$siteURL/cwa-make-or-restore-backups/"; // Update to your actual page slug
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	// Get State Variables
	if (isset($_REQUEST['strPass'])) {
		$strpass = $_REQUEST['strPass'];
		$strpass = filter_var($strpass,FILTER_UNSAFE_RAW);
	} else {
		$strpass = '1';
	}
	$mode      = filter_input(INPUT_POST, 'inp_mode') ?: "Production";
	$args = [
		'copytable' => [
			'filter' => FILTER_SANITIZE_SPECIAL_CHARS,       
			'flags'  => FILTER_REQUIRE_ARRAY  
		]
	];
	
	$copytable = filter_input_array(INPUT_POST, $args);
	
	$tablesArray = array(
						'User Related Tables	wpw1_cwa_user_master	wpw1_cwa_user_master2',
						'User Related Tables	wpw1_cwa_user_master_deleted	wpw1_cwa_user_master_deleted2',
						'User Related Tables	wpw1_cwa_user_master_history	wpw1_cwa_user_master_history2',
						'User Related Tables	wpw1_usermeta	wpw1_usermeta2',
						'User Related Tables	wpw1_users	wpw1_users2',
						'Advisor Related Tables	wpw1_cwa_advisor	wpw1_cwa_advisor2',
						'Advisor Related Tables	wpw1_cwa_advisorclass	wpw1_cwa_advisorclass2',
						'Advisor Related Tables	wpw1_cwa_deleted_advisor	wpw1_cwa_deleted_advisor2',
						'Advisor Related Tables	wpw1_cwa_deleted_advisorclass	wpw1_cwa_deleted_advisorclass2',
						'Advisor Related Tables	wpw1_cwa_evaluate_advisor	wpw1_cwa_evaluate_advisor2',
						'Student Related Tables	wpw1_cwa_deleted_student	wpw1_cwa_deleted_student2',
						'Student Related Tables	wpw1_cwa_student	wpw1_cwa_student2',
						'All Other Tables	wpw1_cwa_admin_priviliges	wpw1_cwa_admin_priviliges2',
						'All Other Tables	wpw1_cwa_announcements	wpw1_cwa_announcements2',
						'All Other Tables	wpw1_cwa_announcements_tracking	wpw1_cwa_announcements_tracking2',
						'All Other Tables	wpw1_cwa_audit_log	wpw1_cwa_audit_log2',
						'All Other Tables	wpw1_cwa_current_catalog	wpw1_cwa_current_catalog2',
						'All Other Tables	wpw1_cwa_data_log	wpw1_cwa_data_log2',
						'All Other Tables	wpw1_cwa_joblog	wpw1_cwa_joblog2',
						'All Other Tables	wpw1_cwa_new_assessment_data	wpw1_cwa_new_assessment_data2',
						'All Other Tables	wpw1_cwa_production_email	wpw1_cwa_production_email2',
						'All Other Tables	wpw1_cwa_reminders	wpw1_cwa_reminders2',
						'All Other Tables	wpw1_cwa_replacement_requests	wpw1_cwa_replacement_requests2',
						'All Other Tables	wpw1_cwa_reports	wpw1_cwa_reports2',
						'All Other Tables	wpw1_cwa_temp_data	wpw1_cwa_temp_data2',
	);	

	function doTheCopy($sourceTable, $destinationTable) {
		
		global $wpdb;
		
		$returnData = "";
	
		// truncate the destination table
		$result				= $wpdb->query("TRUNCATE $destinationTable");
		if ($result === FALSE) {
			$returnData .= "Truncating $destinationTable failed<br />
					Result: $result<br />
					wpdb->last_query: " . $wpdb->last_query . "<br />";
			if ($wpdb->last_error != '') {
				$returnData .= "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
			return array(FALSE,$returnData);	
		} else {
			$returnData .= "Successfully truncated $destinationTable<br />";
	
			//// can proceed with the copy
			$result			= $wpdb->query("insert into $destinationTable select * from $sourceTable");
			if ($result === FALSE) {
				$returnData .= "Copying from $sourceTable to $destinationTable failed<br />
						Result: $result<br />
						wpdb->last_query: " . $wpdb->last_query . "<br />";
				if ($wpdb->last_error != '') {
					$returnData .= "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
				return array(FALSE,$returnData);
			} else {
				$sql		= "select count(*) from $destinationTable";
				$myInt		= $wpdb->get_var($sql);
				$returnData .= "Successfully copied from $sourceTable to $destinationTable<br />
					  $destinationTable has $myInt records<br />";
				return array(TRUE,$returnData);
			}
		}
	}
	
	
	ob_start();
	echo "<div id='cwa-backup-wrapper'>";
	$runTheJob				= TRUE;
	////// see if this is the time to actually run
	if ($userName == '') {
		$userName			= "CRON";
		$runTheJob			= allow_job_to_run(FALSE);
	}
	if (! $runTheJob) {
		return "YOU'RE NOT AUTHORIZED!<br />Goodbye";
	}
	
	switch ($strpass) {
		
		// --- PASS 1: THE DASHBOARD / SELECTOR ---
		case "1":
			?>
			<div id='form_wraper' style="margin-bottom:20px;">
			<h3><?php echo "$jobname Selection"; ?></h3>
			<form method="post" action="<?php echo $theURL; ?>"
			name="selection_form" ENCTYPE="multipart/form-data">
			<input type="hidden" name="strPass" value="2">
			<table style='width:1200px;'>
			<tbody>
			<tr><td colspan='2'><b>All Tables</b></td></tr>
			<tr><td style='vertical-align:top;text-align:left;width:600px;'><input type='checkbox' class='formInputButton' name='copytable[]' value='copymain' >Copy All Tables to their backups</td>
				<td style='vertical-align:top;text-align:left;width:600px;'><input type='checkbox' class='formInputButton' name='copytable[]' value='restoremain'>Restore All Tables from their backups</td></tr>

			<?php
			$prevTopic = "";
			foreach($tablesArray as $key => $thisTableData) {
				$myArray = explode("\t",$thisTableData);
				$thisTopic = $myArray[0];
				$thisMainTable = $myArray[1];
				$thisBackupTable = $myArray[2];
				
				if ($thisTopic != $prevTopic) {
					$prevTopic = $thisTopic;
					echo "<tr><td colspan='2'><br /><b>$thisTopic</b></td></tr>\n";
				}
				echo "<tr><td style='vertical-align:top;text-align:left;'><input type='checkbox' class='formInputButton' name='copytable[]' value='$thisMainTable|$thisBackupTable'>Copy $thisMainTable to $thisBackupTable</td>
					  <td style='vertical-align:top;text-align:left;'><input type='checkbox' class='formInputButton' name='copytable[]' value='$thisBackupTable|$thisMainTable'>Copy $thisBackupTable to $thisMainTable</td></tr>\n";
			}
			?>
			<tr><td colspan='2'><input type="submit" name="submit" value="Submit" class="formInputButton" style="float:none; display:inline-block; background:#d9534f; color:#fff;" />


			</tbody>
			</table>
			</div><!-- form_wrapper -->
			<?php 
			break;
    
		// --- PASS 2: ACTION HANDLER (FORM OR EXECUTION) ---
		case "2":
			echo "<h3>$jobname Excution</h3>";

			if (! is_array($copytable)) {
				echo "copytable is not an array: $copytable<br />";
			} else {
//				echo "<br />copytable:<br /><pre>";
//				$myStr = print_r($copytable, TRUE);
//			 	echo "$myStr</pre><br />";
				foreach ($copytable as $key => $value) {
					foreach($value as $key1 => $tables) {
						if ($tables == 'copymain') {
							echo "<h4>Copying All Tables to Their Backup</h4>";
							foreach($tablesArray as $key => $thisTableData) {
								$myArray = explode("\t",$thisTableData);
								$thisTopic = $myArray[0];
								$sourceTable = $myArray[1];
								$destinationTable = $myArray[2];
								echo "<p>Copying $sourceTable to $destinationTable<br />";
								$copyResult = doTheCopy($sourceTable, $destinationTable);
								echo $copyResult[1];
								if ($copyResult[0] == FALSE) {
									echo "<br /><b>COPY FAILED</b></p>";
								} else {
									echo "<br />Copy was successful</p>";
								}
							}
							break;
								
						} elseif ($tables == 'restoremain') {
							echo "<h4>Copying All Table Backups to Their Main Table</h4>";
							foreach($tablesArray as $key => $thisTableData) {
								$myArray = explode("\t",$thisTableData);
								$thisTopic = $myArray[0];
								$destinationTable = $myArray[1];
								$sourceTable = $myArray[2];
								echo "<p>Copying $sourceTable to $destinationTable<br />";
								$copyResult = doTheCopy($sourceTable, $destinationTable);
								echo $copyResult[1];
								if ($copyResult[0] == FALSE) {
									echo "<br /><b>COPY FAILED</b></p>";
								} else {
									echo "<br />Copy was successful</p>";
								}
							}
							break;
							
						} else {
							$myArray = explode("|",$tables);
							$sourceTable = $myArray[0];
							$destinationTable = $myArray[1];
							echo "<p>Copying $sourceTable to $destinationTable<br />";
							$copyResult = doTheCopy($sourceTable, $destinationTable);
							echo $copyResult[1];
							if ($copyResult[0] == FALSE) {
								echo "<br /><b>COPY FAILED</b></p>";
							} else {
								echo "<br />Copy was successful</p>";
							}
						}
					}
				}
			}
			break;
			
		// --- PASS 3: do all backups
		case "3":
			echo "<h3>$jobname Daily Backup</h3>";
			foreach($tablesArray as $key => $thisTableData) {
				$myArray = explode("\t",$thisTableData);
				$thisTopic = $myArray[0];
				$sourceTable = $myArray[1];
				$destinationTable = $myArray[2];
				echo "<p>Copying $sourceTable to $destinationTable<br />";
				$copyResult = doTheCopy($sourceTable, $destinationTable);
				echo $copyResult[1];
				if ($copyResult[0] == FALSE) {
					echo "<br /><b>COPY FAILED</b></p>";
				} else {
					echo "<br />Copy was successful</p>";
				}
			}
			break;

	}
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	echo "<p>Report $jobname pass $strpass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	$ipAddr			= get_the_user_ip();
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strpass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> FALSE);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		echo"<p>writing to joblog failed</p>";
	}



	echo "</div><!-- cwa-backup-wrapper -->";
	return ob_get_clean();
}
add_shortcode('make_or_restore_backups', 'make_or_restore_backups_func');