function storeReportData_v2($reportName='',$reportData='',$testMode=FALSE,$doDebug=FALSE) {

/*	This function can be called from any other snippet
	
	Two arguments are required:
		the report name
		the report data
		
	
	Upon success, the function returns an array (TRUE/FALSE,report name/fail reason,report_id)
	If either the report name or the report data is missing, or the save failed, the 
	function will return array(FALSE,reason)

*/

	global $wpdb;

	$initializationArray	= data_initialization_func();
	$currentDate			= $initializationArray['currentDate'];
	$currentTimestamp		= $initializationArray['currentTimestamp'];
	if ($doDebug) {
		echo "<br />storeReportData: Function has been called.<br />";
	}
	if ($reportName == '') {
		if ($doDebug) {
			echo "storePreportData: report name empty. Returning FALSE";
		}
		return array(FALSE,'report name empty','');
	}
	if ($reportData == '') {
		if ($doDebug) {
			echo "storePreportData: report data empty. Returning FALSE";
		}
		return array(FALSE,'report data empty','');
	}
	$reportsTableName		= 'wpw1_cwa_reports';
	if ($testMode) {
		$reportsTableName	= 'wpw1_cwa_reports2';
	}
		
	// Put the info in the report table	
	$myDate		= date('Y-m-d H:i:s',$currentTimestamp);
	$fileDate	= date('Ymdhis');
	$reportFile	= "";
	$fullPath	= "";
	$updateParam	= array('report_name'=>$reportName,
							'report_date'=>$myDate,
							'report_path'=>"",
							'report_url'=>"",
							'report_filename'=>"",
							'report_data'=>$reportData);
	$updateFormat	= array('%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s');
	$result			= $wpdb->insert($reportsTableName,
									$updateParam,
									$updateFormat);
	if ($result === FALSE) {
		if ($doDebug) {
			$lastError		= $wpdb->last_error;
			$lastQuery		= $wpdb->last_query;
			echo "Inserting $reportsTableName record for $reportFile failed. Error: $lastError<br />SQL: $lastQuery<br />";
		}
		return array(FALSE,'insert failed. Error: $lastError','');
	} else {
		$nextID		= $wpdb->insert_id;
		if ($doDebug) {
			echo "Successfully inserted $reportsTableName record for $reportFile at $nextID<br />";
		}
		return array(TRUE,$reportName,$nextID);
	}
}
add_action('storeReportData_v2','storeReportData_v2');
