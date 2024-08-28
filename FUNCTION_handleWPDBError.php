function handleWPDBError($jobname,$doDebug) {

	global $wpdb;
	
	$initializationArray = data_initialization_func();
	$userName				= $initializationArray['userName'];
	
	
	$lastError		= $wpdb->last_error;
	$lastQuery		= $wpdb->last_query;
	if ($doDebug) {
		echo "Database error. Last Query: $lastQuery<br />Last Error: $lastError<br />";	
	} else {
	sendErrorEmail("$jobname Database Error. Last Query: $lastQuery. Error: $lastError. UserName: $userName");
	}
	
	return TRUE;
}
add_action('handleWPDBError','handleWPDGError');