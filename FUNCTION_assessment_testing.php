function assessment_testing($inp_data='') {

/*	this is where Andrew's program do Morse code assessments stores the resulting data

	inp_data is a base64 encoded string containing:
		cs=xxxxxx&score=nn.n&level=xxxxxxx
		
		where cs is the call sign
		score is the percentage achieved in the assessment
		level is one of Beginner, Fundamental, Intermediate, Advanced
		
	The data is written to wpw1_cwa_assessment_testing
	
	Returns 0 if data was inserted into the table, otherwise returns 1
	
*/
	global $wpdb;

	$thisCallSign		= 'not supplied';
	$thisScore			= -9.99;
	$thisLevel			= 'not supplied';

	$myStr = base64_decode($inp_data);
	$myArray = explode("&",$myStr);
	foreach($myArray as $thisKey=>$thisValue) {
		${$thisKey} = $thisValue;
	}
	$thisDate = date('Y-m-d H:i:s');
	
	$result = $wpdb->insert('wpw1_cwa_assessment_testing',
							array('call_sign'=>$thisCallSign,
								  'score'=>$thisScore,
								  'level'=>$thisLevel',
								  'date_written'=>$thisDate);
	if ($result === FAlSE) {
		return 1;
	} else {
		return 0;
	}
	

}
add_action('assessment_testing','assessment_testing');