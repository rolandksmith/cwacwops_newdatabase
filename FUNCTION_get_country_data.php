function getCountryData($inp_info,$inp_type='countrycode',$doDebug=FALSE) {

/* 	returns array of country data

	input:	inp_info:	either the country name or the country code
			inp_type:	either 'countrycode' if supplying the country code or 'countryname'
						if supplying the name
			doDebug		TRUE or FALSE
	
	returns;	array	country_code
						country_name
						ph_code
	
	If no data found or bad input, country_code will be FALSE
*/
	
	global $wpdb;

	$gbArray				= array('England'=>'GB',
									'Scotland'=>'GB',
									'Wales'=>'GB',
									'Northern Ireland'=>'GB');
	if ($inp_type === 'countryname') {
		if (array_key_exists($inp_info,$gbArray)) {
			$inp_info		= $gbArray[$inp_info];
			$inp_type		= 'countrycode';
		}
	}

	if ($inp_type == 'countrycode') {
		$sql				= "select * from wpw1_cwa_country_codes where country_code='$inp_info'";
	} elseif ($inp_type == 'countryname') {
		$sql				= "select * from wpw1_cwa_country_codes where country_name='$inp_info'";
	} else {
		return				array(FALSE,'','','');
	}
	$phoneResult			= $wpdb->get_results($sql);
	if ($phoneResult === FALSE) {
		if ($doDebug) {
			echo "getting phone code from wpw1_cwa_country_codes table for country_code $inp_country_code failed<br />";
			echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
			echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
		}
	} else {
		$numCCRows			= $wpdb->num_rows;
		if ($numCCRows > 0) {
			foreach($phoneResult as $countryCodeRows) {
				$countryCode_ID				= $countryCodeRows->record_id;
				$countryCode_country_code	= $countryCodeRows->country_code;
				$countryCode_country_name	= $countryCodeRows->country_name;
				$countryCode_ph_code		= $countryCodeRows->ph_code;
				$countryCode_date_updated	= $countryCodeRows->date_updated;
			}
			return	array($countryCode_country_code,$countryCode_country_name,$countryCode_ph_code);
		} else {
			return	array(FALSE,'','','');
		}
	}
}
add_action('getCountryData','getCountryData');