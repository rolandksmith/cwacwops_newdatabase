function tracking_data($thisProgram='',$thisMode='',$thisCallSign='',$thisIP,$thisData='') {

/*	writes a record to the data tracking table

	input:	
		thisProgram:	Name of the program writing the data
		thisMode:		testMode TRUE or FALSE
		thisCallSign	the call sign for this transaction
		thisIP			IP address from the browser
		thisData		free-form text field with the information to be tracked
		
	returns:
		array			TRUE/FALSE
						if FALSE, the reason
						
	Example:
		$trackingResult			= tracking_data("Student Registration",$testMode,$inp_callsign,$thisIP,"pass 101 with a level of $inp_level");
		if ($trackingResult === FALSE) {
			if ($doDebug) {
				echo "called tracking_data which failed.";
			}
		}
		if ($trackingResult[0] == FALSE) {
			if ($doDebug) {
				echo "tracking data insert failed.<br />$trackingResult[1]<br />";
			}
		} else {
			if ($doDebug) {
				echo "tracking data written<br />";
			}
		}
						
						
*/

		global $wpdb;


		if ($thisMode) {
			$myMode			= 'TestMode';
		} else {
			$myMode			= 'Production';
		}
		$thisData			= $thisData;

		$insertResult		= $wpdb->insert('wpw1_cwa_data_tracking',
											array('tracking_program'=>$thisProgram,
												   'tracking_mode'=>$myMode,
												  'tracking_callsign'=>$thisCallSign,
												  'tracking_ip'=>$thisIP,
												  'tracking_data'=>$thisData),
											array('%s','%s','%s','%s',));
		if ($insertResult == FALSE) {
			$myQuery		= $wpdb->last_query;
			$myError		= $wpdb->last_error;
			return array(FALSE,"insert into data_tracking failed.<br />Query: $myQuery<br />Error: $myError");
		} else {
			return array(TRUE,'');
		}
}
add_action('tracking_data','tracking_data');