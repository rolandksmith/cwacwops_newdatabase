function resolve_reminder($inp_callsign='',$token='',$testMode=FALSE,$doDebug=FALSE) {


	global $wpdb;
	
	if ($doDebug) {
		echo "<br /><b>resolve_reminder</b><br />
				inp_callsign: $inp_callsign<br />
				token: $token<br />
				doDebug: $doDebug<br /><br />";
	}
	
	if ($inp_callsign == '' && $token == '') {
		if ($doDebug) {
			echo "either inp_callsign ($inp_callsign) or token ($token) must be specified<br />";
		}
		return FALSE;
	}
	$remindersTableName		= 'wpw1_cwa_reminders';
	if($testMode) {
		$remindersTableName	= 'wpw1_cwa_reminders2';
	}
	// do something different if inp_callsign is administrator
	if ($inp_callsign == 'administrator') {
		$myStr					= date('Y-m-d H:i:s');
		$updateResult			= $wpdb->update($remindersTableName,
												array('resolved_date'=>$myStr,
													  'resolved'=>'Y'),
												array('role'=>$inp_callsign,
													  'token'=>$token),
												array('%s','%s'),
												array('%s','%s'));
	
	} else {
		$myStr					= date('Y-m-d H:i:s');
		$updateResult			= $wpdb->update($remindersTableName,
												array('resolved_date'=>$myStr,
													  'resolved'=>'Y'),
												array('call_sign'=>$inp_callsign,
													  'token'=>$token),
												array('%s','%s'),
												array('%s','%s'));
	}
//	if ($doDebug) {
//		$lastQuery		= $wpdb->last_query;
//		$lastError		= $wpdb->last_error;
//		echo "lastQuery: $lastQuery<br />
//			   lastError: $lastError<br />
//				updateResult:<br /><pre>";
//		print_r($updateResult);
//		echo "</pre><br />";
//	}
	if ($updateResult === FALSE) {
		$lastError			= $wpdb->last_error;
		$lastQuery			= $wpdb->last_query;
		if ($doDebug) {
			echo "update using $inp_callsign and $token failed. Error: $lastError<br />Query: $lastQuery<br />";
		}
		return FALSE;
	} else {
		return TRUE;
	}

}
add_action('resolve_reminder','resolve_reminder');