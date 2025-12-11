function get_user_master_for_display($get_type,$get_info,$admin,$operatingMode,$doDebug=FALSE) {

/** 
	gets the user_master record using the get_type and get_info
	if a record is found, formats the record for display and returns the formatted display
	
	@param string $get_type callsign|id|surname|firstname|email
	@param string $get_info the value corresponding to get_type
	@param string $admin Y|N whether or not the person requesting the displayis an admin
	@param string $operatingMode Production or Testmode
	@param bool $doDebug whether to show debugging information
	@return string|FALSE the formatted user_master display or FALSE on error
	
	NOTE: If multiple records match the criteria, multiple displays will be returned
	
*/

	global $wpdb;
	$user_master_dal = new CWA_User_Master_DAL();
	
	// verify get_type
	$get_type_array = ['callsign','id','surname','firstname','email'];
	if (! in_array($get_type,$get_type_array)) {
		return FALSE;
	}
	
	// sanitize get_info
	$get_info = filter_var($get_info,FILTER_UNSAFE_RAW);

	if ($doDebug) {
		echo "<br /><b>FUNCTION: get_user_master_for_display</b><br />
				get_type: $get_type<br />
				get_info: $get_info<br />";
	}
	
	// format the criteria
	if ($get_type == 'callsign') {
		$get_info = strtoupper($get_info);
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				[ 
					'field'   => 'user_call_sign', 
					'value'   => $get_info, 
					'compare' => '=' 
				]
			]
		];
	} elseif ($get_type == 'id') {
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				[ 
					'field'   => 'user_ID', 
					'value'   => $get_info, 
					'compare' => '=' 
				]
			]
		];
	} elseif ($get_type == 'surname') {
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				[ 
					'field'   => 'user_last_name', 
					'value'   => $get_info, 
					'compare' => '=' 
				]
			]
		];
	} elseif ($get_type == 'firstname') {
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				[ 
					'field'   => 'user_first_name', 
					'value'   => $get_info, 
					'compare' => '=' 
				]
			]
		];
	} elseif ($get_type == 'email') {
		$criteria = [
			'relation' => 'AND',
			'clauses' => [
				[ 
					'field'   => 'user_email', 
					'value'   => $get_info, 
					'compare' => '=' 
				]
			]
		];
	}
	
	// run the query
	$user_data = $user_master_dal->get_user_master($criteria, 'user_call_sign', 'ASC', $operatingMode);
	if ($user_data === FALSE) {
		return FALSE;
	}
	if (count($user_data) > 0) {
		$content = array();
		foreach($user_data as $key => $value) {
			foreach($value as $thisField => $thisValue) {
				$$thisField = $thisValue;
			}
			$myStr			= formatActionLog($user_action_log);
			$timezoneMsg	= '';
			if ($user_timezone_id == '??') {
				$timezoneMsg	= "<p><b>CRITICAL</b>The timezone identifier needs to be 
									determined. You must update the information and 
									be certain that 
									the following fields are correct and valid:<br />
									<ul><li>Country: the value in this field must 
											be a valid country name
										<li>Zip / Postal Code: the code must valid 
											for residents of the United States.
										<li>City: the value in this field must be 
											a valid city name
										<li>State / Province: Again, a valid name
									</ul>";
			}
			$thisUser		= "<h4>User Master Data for ID $user_ID</h4>
								$timezoneMsg
								<table style='width:900px;'>
								<tr><td><b>Callsign<br />$user_call_sign</b></td>
									<td><b>Name</b><br />$user_last_name, $user_first_name</td>
									<td><b>Phone</b><br />+$user_ph_code $user_phone</td>
									<td><b>Email</b><br />$user_email</td></tr>
								<tr><td><b>City</b><br />$user_city</td>
									<td><b>State</b><br />$user_state</td>
									<td><b>Zip Code</b><br />$user_zip_code</td>
									<td><b>Country</b><br />$user_country</td></tr>
								<tr><td><b>WhatsApp</b><br />$user_whatsapp</td>
									<td><b>Telegram</b><br />$user_telegram</td>
									<td><b>Signal</b><br />$user_signal</td>
									<td><b>Messenger</b><br />$user_messenger</td></tr>
								<tr><td><b>Timezone ID</b><br />$user_timezone_id</td>
									<td><b>Languages</b><br />$user_languages</td>
									<td><b>Date Created</b><br />$user_date_created</td>
									<td><b>Date Updated</b><br />$user_date_updated</td></tr>";
			if ($admin == 'Y') {
				$thisUser 	.= "<tr><td><b>Survey Score</b><br />$user_survey_score</td>
									<td><b>Is Admin</b><br />$user_is_admin</td>
									<td><b>Role</b><br />$user_role</td>
									<td><b>Prev Callsign</b><br />$user_prev_callsign</td>
								<tr><td colspan='4'><b>Action Log</b><br />$myStr</td></tr>
								<tr><td colspan='4'><hr></td></tr>";
			}
			$userData = array('user_call_sign'=>$user_call_sign,
						 	'user_first_name'=>$user_first_name,
						 	'user_last_name'=>$user_last_name);
			$content[$user_ID]['display'] = $thisUser;
			$content[$user_ID]['data'] = $userData;
		}
	} else {
		return FALSE;
	}
	return $content;
}
add_action ('get_user_master_for_display','get_user_master_for_display');