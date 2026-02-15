function get_user_data($advisorCallSign, $operatingMode) {	
	$user_dal = new CWA_User_Master_DAL();
	$userReturnArray = array();
	$userData = $user_dal->get_user_master_by_callsign($advisorCallSign, $operatingMode);
	if ($userData === FALSE || $userData === NULL) {
		if ($doDebug) {
			echo "getting user_master by callsign ($advisorCallSign) returned NULL|FALSE<br >";
		}
		return FALSE;
	} else {
		if(! empty($userData)) {
			foreach($userData as $key => $value) {
				foreach($value as $thisField => $thisValue) {
					$userReturnArray[$thisField] = $thisValue;
				}
			}
		} else {
			if ($doDebug) {
				echo "no user_master datafound for $advisorCallSign<br />";
			}
			return FALSE;
		}
		
	}
	return $userReturnArray;
}
add_action('get_user_data','get_user_data');