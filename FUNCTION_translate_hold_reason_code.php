function translate_hold_reason_code($holdReasonCode='',$doDebug=FALSE) {

/* translates hold reason code into a value

	Created 17July2024 by Roland
	
*/

	$translationArray		= array('B'=>'B-Bad Actor',
									'E'=>'E-Not Evaluated',
									'H'=>'H-Not Promotable',
									'M'=>'M-Student Moved',
									'Q'=>'Q-Advisor Quit',
									'X'=>'X-Excluded Advisors',
									'W'=>'W-Student Withdrew');

	if ($doDebug) {
		echo "<br /><b>Arrived at Translate Hold Reason Code</b> with a code of $holdReasonCode<br />";
	}
	if ($holdReasonCode == '') {
		if ($doDebug) {
			echo "holdReasonCode was empty<br />";
		}
		$returnValue		= '';
	}
	
	if (array_key_exists($holdReasonCode,$translationArray)) {
		if ($doDebug) {
			echo "Found holdReasonCode of $holdReasonCode in the array<br />";
		}
		$returnValue		= $translationArray[$holdReasonCode];
	} else {
		if ($doDebug) {
			echo "Did NOT find holdReasonCode of $holdReasonCode in the array<br />";
		}
		$returnValue		= FALSE;
	}

	return $returnValue;
}
add_action('translate_hold_reason_code','translate_hold_reason_code');