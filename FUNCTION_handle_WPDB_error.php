function handleWPDBError($jobname,$doDebug=FALSE,$info='') {

	global $wpdb;
	
	$context = CWA_Context::getInstance();
	$userName				= $context->userName;
	
	
	$lastError		= $wpdb->last_error;
	$lastQuery		= $wpdb->last_query;
	if ($doDebug) {
		echo "Database error. Last Query: $lastQuery<br />Last Error: $lastError<br />";	
	} else {
		sendErrorEmail("$jobname Database Error. Last Query: $lastQuery. Error: $lastError. 
Info: $info. UserName: $userName");
	}
	
	return TRUE;
}
add_action('handleWPDBError','handleWPDGError');