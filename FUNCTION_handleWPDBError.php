function handleWPDBError($jobname,$doDebug) {

	global $wpdb;
	
	$lastError		= $wpdb->last_error;
	$lastQuery		= $wpdb->last_query;
	if ($doDebug) {
		echo "Database error. Last Query: $lastQuery<br />Last Error: $lastError<br />";	
	} else {
	sendErrorEmail("$jobname Database Error. Last Query: $lastQuery. Error: $lastError");
	}
	
	return TRUE;
}
add_action('handleWPDBError','handleWPDGError');