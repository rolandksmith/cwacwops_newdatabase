function formatActionLog($action_log='') {

/* 	Returns a formatted action log

	Input: 	the raw action log
	Returns:	an action log one line for each entry
	
*/

	if ($action_log == '') {
		return $action_log;
	}

	$action_log		= htmlspecialchars_decode($action_log);
	$action_log		= stripslashes($action_log);
	$myArray		= explode(" /",$action_log);
	$newLog			= "";
	foreach($myArray as $thisLine) {
		$newLog		.= "$thisLine<br />\n";
	}
	return $newLog;

}