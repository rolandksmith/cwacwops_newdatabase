function sendErrorEmail($errorMessage) {

// for production mailCode should be 18

	$thisDate		= date('Y-m-d H:i:s');
	
	$errorMessage	= "$thisDate $errorMessage";

	$mailResult		= emailFromCWA_v2(array('theRecipient'=>'rolandksmith@gmail.com',
											'theSubject'=>'CW Academy - ERROR Report',
										    'theContent'=>$errorMessage,
										    'theCc'=>'',
										    'mailCode'=>16,
										    'jobname'=>'sendErrorEmail',
										    'increment'=>0,
										    'testMode'=>FALSE,
										    'doDebug'=>FALSE));
	return;
}
add_action ('sendErrorEmail', 'sendErrorEmail');
