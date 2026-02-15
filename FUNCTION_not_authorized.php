/**	Not Authorized

	Function called when a program is executed by someone without privileges
	There are no parameters
	
	Usage:
		if ($validUser == "N") {
			return notAuthorized();
		}
	or
		if ($userName === '') {
			return notAuthorized();
		}
		
*/

function notAuthorized() {

	$content	= "<h3>CW Academy</h3>
					<p>This function is only available to logged-in users.</p>
					<p>CW Academy is a non-profit academy sponsored by CWops that teaches Morse code 
					to amateur radio operators worldwide. To learn more about CW 
					Academy click <a href='https://cwops.org/cw-academy/'>CW 
					Academy</a> to display the CW Academy home page.</p>
					<p>If you have created a CW Academy account, click 
					<a href='$siteURL/login/'>HERE</a> to login</p>";
	return $content;
}
add_action('notAuthorized','notAuthorized');