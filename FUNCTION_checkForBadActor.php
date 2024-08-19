function checkForBadActor($inp_call_sign='',$doDebug=FALSE) {

/*	checks to see if a call sign is in the bad actors table

	Input: 		call sign to be checked
	Returns:	TRUE if call sign is in the table
				FALSE if the call sign is not in the table

*/

	global $wpdb;
	
	if ($doDebug) {
		echo "<br /><b>checkForBadActor</b> called with call sign $inp_call_sign<br />";
	}

	if ($inp_call_sign == '') {
		if ($doDebug) {
			echo "call sign is empty<br />";
		}
		return FALSE;
	}
	$sql				= "select * from wpw1_cwa_bad_actor 
							where call_sign = '$inp_call_sign' 
							and status = 'A'";
	$badActorsResult	= $wpdb->get_results($sql);
	if ($badActorsResult === FALSE) {
		$myQuery		= $wpdb->last_query;
		$myError		= $wpdb->last_error;
		if ($doDebug) {
			echo "attempting to read from wpw1_cwa_bad_actor failed<br />
				  Error: $myError<br />
				  Query: $myQuery<br />";
		}
		sendErrorEmail("checkForBadActor attempting to read from wpw1_cwa_bad_actor failed\nError: $myError\nQuery: $myQuery");
		return FALSE;
	} else {
		$numRows		= $wpdb->num_rows;
		if ($doDebug) {
			$myStr		= $wpdb->last_query;
			echo "ran $myStr<br />and retrieved $numRows rows<br />";
		}
		if ($numRows > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	if ($doDebug) {
		echo "should not get here<br />";
	}
	return FALSE;
}
add_action('checkForBadActor','checkForBadActor');