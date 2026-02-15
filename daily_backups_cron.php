function daily_backups_cron_func() {

/*	Run as part of the daily cron process
	makes backup copies of 
		wpw1_cwa_advisor
		wpw1_cwa_advisorclass
		wpw1_cwa_student
		wpw1_cwa_temp_data
		wpw1_cwa_user_master
		wpw1_usermeta
		wpw1_users
		
	created 12Oct24 by Roland
	
*/

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
	$userRole			= $context->userRole;
	
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	$wpdb->show_errors();

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$jobname					= "Daily Backups Cron V$versionNumber";

	
	
	$content = "";	

	$runTheJob				= TRUE;
	if ($userName != '') {
		echo "<h3>$jobname Executed by $userName</h3>";
	} else {
		echo "<h3>$jobname Process Automatically Executed</h3>";
		$userName			= "CRON";
		$runTheJob			= allow_job_to_run($doDebug);
	}
	
	if ($runTheJob) {
		$copyArray		= array('wpw1_cwa_advisor|wpw1_cwa_advisor2', 
								'wpw1_cwa_advisorclass|wpw1_cwa_advisorclass2', 
								'wpw1_cwa_student|wpw1_cwa_student2', 
								'wpw1_cwa_temp_data|wpw1_cwa_temp_data2', 
								'wpw1_cwa_user_master|wpw1_cwa_user_master2', 
								'wpw1_usermeta|wpw1_usermeta2', 
								'wpw1_users|wpw1_users2');
		
		echo "<h3>$jobname</h3>";
		foreach ($copyArray as $myValue) {
			$myArray			= explode("|",$myValue);
			$sourceTable		= $myArray[0];
			$destinationTable	= $myArray[1];
				echo "<p>Copying $sourceTable to $destinationTable<br />";

			// truncate the destination table
			$result				= $wpdb->query("TRUNCATE $destinationTable");
			if ($result === FALSE) {
				echo "Truncating $destinationTable failed<br />
						Result: $result<br />
						wpdb->last_query: " . $wpdb->last_query . "<br />";
					if ($wpdb->last_error != '') {
						echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
					}
			} else {
				echo "Successfully truncated $destinationTable<br />";
	
				//// can proceed with the copy
				$result			= $wpdb->query("insert into $destinationTable select * from $sourceTable");
				if ($result === FALSE) {
					echo "Copying from $sourceTable to $destinationTable failed<br />
							Result: $result<br />
							wpdb->last_query: " . $wpdb->last_query . "<br />";
						if ($wpdb->last_error != '') {
							echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
						}
				} else {
					$sql		= "select count(*) from $destinationTable";
					$myInt		= $wpdb->get_var($sql);
					echo "Successfully copied from $sourceTable to $destinationTable<br />
						  $destinationTable has $myInt records<br />";
				}
			}
		}
	}
	
	

	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$elapsedTime|$ipAddr");
	return $content;
}
add_shortcode ('daily_backups_cron', 'daily_backups_cron_func');

