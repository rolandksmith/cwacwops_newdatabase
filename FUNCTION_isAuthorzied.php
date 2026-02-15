function isAuthorized() {

	$context = CWA_Context::getInstance();
	$validUser = $context->validUser;
	if ($validUser == "N") {
		http_response_code(403);
		echo "You're not authorized";
		exit;
	}
	return "Authorized User";	
}
add_shortcode('isAuthorized','isAuthorized');