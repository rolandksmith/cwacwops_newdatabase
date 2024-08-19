function isAuthorized() {

	$initializationArray = data_initialization_func();
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		echo "You're not authorized";
		$bigLoop = TRUE;
		while (bigLoop) {
			$ii = 0;
		}
	}
	return "Authorized User";	
}
add_shortcode('isAuthorized','isAuthorized');