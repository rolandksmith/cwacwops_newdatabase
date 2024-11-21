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
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	$versionNumber				 	= "1";
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$userName			= $initializationArray['userName'];
	$userRole			= $initializationArray['userRole'];
	
	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	$wpdb->show_errors();

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$jobname					= "Daily Backups Cron V$versionNumber";

	
	
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
