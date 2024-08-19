function emailFromCWA_v2($mailParameters) {

/*
	mailParameters:
		theRecipient	default: rolandksmith@gmail.com
		theSubject		default: CW Academy
		theContent		default: blank
		theCc			default: blank
		theBcc			default: blank
		theAttachment	default: empty array
		mailCode		default: 1
		jobname			default: blank
		increment		default: 0
		testMode		default: FALSE
		doDebug			default: FALSE
		
	Parameters can be in any order but must have these names. for example:
			$mailResult		= emailFromCWA_v2(array('theRecipient'=>$theRecipient,
													    'theSubject'=>$theSubject,
													    'theContent'=>$theContent,
													    'theCc'=>$theCc,
													    'theBcc'=>$theBcc,
													    'theAttachment'=>$theAttachment,
													    'mailCode'=>$mailCode,
													    'jobname'=>$jobname,
													    'increment'=>$increment,
													    'testMode'=>$testMode,
													    'doDebug'=>$doDebug));
	
	any attachment must be uploaded to the media library. theAttachment is the path to 
	the attachment. If multiple, the links must be in an array
	$theAttachment	= array(WP_CONTENT_DIR . "/uploads/<filename>");

	mailCode is an integer of the following
	1 Test Mode to Roland
	2 Test Mode to Roland, Bob
	3 Test Mode to Andy
	4 Test Mode to Andy and Bob
	5 Test Mode to Andy, Roland, Bob
	6 Test Mode to Bob tcc Roland, Andy

	10 to theRecipient tcc Roland
	11 to theRecipient tcc Roland, Bob, Andy
	12 to theRecipient  tcc Joe, Roland, Bob, Andy
	13 to theRecipient bcc Roland, Bob, Tcc Andy
	14 to theRecipient Tcc Roland, Bob, Andy
	15 to theRecipient Tcc Roland, Bob, Andy	(same as 11)
	16 to Roland
	17 to Bob, tcc Roland, Andy
	18 to Bob, Roland, Andy
	19 to Bob tcc Roland, Andy
	20 to Bob, Joe Tcc Roland, Andy
	21 to theRecipient Bcc Bob, Roland, Andy
	99 to Roland (dockerMode)
	
	In testmode, if the increment is greater than 10, the email is not sent nor stored
	
*/

	global $wpdb;

	$theRecipient			= "rolandksmith@gmail.com";
	$theSubject				= "CW Academy";
	$theContent				= "";
	$theCc					= "";
	$theBcc					= "";
	$theAttachment			= array();
	$mailCode				= 1;
	$jobname				= '';
	$increment				= 0;
	$testMode				= FALSE;
	$doDebug				= TRUE;

	if (isset($mailParameters)) {
		foreach($mailParameters as $myKey=>$myValue) {
			$$myKey			= $myValue;
		}
	} else {
		echo "mailParameters doesn't exist<br />";
		return FALSE;
	}
// $doDebug = TRUE;	
	if ($doDebug) {
		echo "<br />Entering FUNCTION emailFromCWA<br />
				theRecipient: $theRecipient<br />
				theSubject: $theSubject<br />
				mailCode: $mailCode<br />
				jobname: $jobname<br />
				increment: $increment<br />";
	}


	$initializationArray 	= data_initialization_func();
	$siteURL				= $initializationArray['siteurl'];


	if ($testMode) {
		$emailTableName		= "wpw1_cwa_testmode_email";
	} else {
		$emailTableName		= "wpw1_cwa_production_email";
	}

	ini_set('display_errors','1');
	error_reporting(E_ALL);	



	$myHeaders 		= array('Content-Type: text/html; charset=UTF-8',
						    'From: CW Academy <cwacademy@cwa.cwops.org>',
							'Reply-To: no reply <noreply@cwa.cwops.org>');

							
	$roland			= "rolandksmith@gmail.com";
	$bob			= "kcgator@gmail.com";
	$andy		 	= "abunker@gmail.com";

	$thisTo			= '';
	$theCc			= '';
	$thisBcc		= '';
	$thisTcc		= '';


	$myInt					= strpos($siteURL,'localhost');
	if ($myInt !== FALSE) {
		$mailCode			= 99;
		
		if ($doDebug) {
			echo "dockerMode. setting mailCode to 99<br />";
		}
	}

	switch($mailCode) {
		case 1:								// Test Mode to Roland
			if ($doDebug) {
				echo "doing case 1<br />";
			}
			$thisTo			= "$roland";
			$thisBcc		= "";
			$thisTcc		= "";
			break;
		case 2: 							// Test Mode to Roland, Bob
			if ($doDebug) {
				echo "doing case 2<br />";
			}
			$thisTo			= "$roland,$bob";
			$thisBcc		= "";
			$thisTcc		= "";
			break;
		case 3:								// Test Mode to Andy
			if ($doDebug) {
				echo "doing case 3<br />";
			}
			$thisTo			= "$andy";
			$thisBcc		= "";
			$thisTcc		= "";
			break;
		case 4:								// Test Mode to Andy and Bob
			if ($doDebug) {
				echo "doing case 4<br />";
			}
			$thisTo			= "$andy,$bob";
			$thisBcc		= "";
			$thisTcc		= "";
			break;
		case 5:								// Test Mode to Andy, Roland, and Bob
			if ($doDebug) {
				echo "doing case 5<br />";
			}
			$thisTo			= "$roland,$bob,$andy";
			$thisBcc		= "";
			$thisTcc		= "";
			break;
		case 6:								// Test Mode to Bob tcc Roland Andy
			if ($doDebug) {
				echo "doing case 5<br />";
			}
			$thisTo			= "$bob";
			$thisBcc		= "";
			$thisTcc		= "$roland,$andy";
			break;


		case 10:					// to theRecipient tcc Roland
			if ($doDebug) {
				echo "doing case 10<br />";
			}
			$thisTo			= $theRecipient;
			$thisBcc		= "";
			$thisTcc		= "$roland";
			break;
		case 11:					// to theRecipient tcc Roland, Bob, Andy
			if ($doDebug) {
				echo "doing case 11<br />";
			}
			$thisTo			= $theRecipient;
			$thisBcc		= "";
			$thisTcc		= "$roland,$bob,$andy";
			break;
		case 12:					// to theRecipient tcc Roland, Bob, Andy
			if ($doDebug) {
				echo "doing case 12<br />";
			}
			$thisTo			= $theRecipient;
			$thisBcc		= '';
			$thisTcc		= "$bob,$roland,$andy";
			break;
		case 13:					// to theRecipient Bcc Roland table Bob, Andy
			if ($doDebug) {
				echo "doing case 13<br />";
			}
			$thisTo			= $theRecipient;
			$thisBcc		= "$roland";
			$thisTcc		= "$bob,$andy";
			break;
		case 14:					// to theRecipient table Roland, Bob, Andy
			if ($doDebug) {
				echo "doing case 14<br />";
			}
			$thisTo			= $theRecipient;
			$thisBcc		= "";
			$thisTcc		= "$bob,$roland,$andy";
			break;
		case 15:					// to theRecipient table Roland, Bob, Andy
			if ($doDebug) {
				echo "doing case 15<br />";
			}
			$thisTo			= $theRecipient;
			$thisBcc		= "";
			$thisTcc		= "$bob,$roland,$andy";
			break;
		case 16:					// to Roland
			if ($doDebug) {
				echo "doing case 16<br />";
			}
			$thisTo			= $roland;
			$thisBcc		= "";
			$thisTcc		= "";
			break;
		case 17:					// to Bob, tcc Roland, Andy
			if ($doDebug) {
				echo "doing case 17<br />";
			}
			$thisTo			= $bob;
			$thisBcc		= "";
			$thisTcc		= "$roland,$andy";
			break;
		case 18:					// to Bob, Roland, Andy
			if ($doDebug) {
				echo "doing case 18<br />";
			}
			$thisTo			= "$bob,$roland,$andy";
			$thisBcc		= "";
			$thisTcc		= "";
			break;
		case 19:					// to Bob, Kate tcc Roland, Andy
			if ($doDebug) {
				echo "doing case 19<br />";
			}
			$thisTo			= "$bob";
			$thisBcc		= "";
			$thisTcc		= "$roland,$andy";
			break;
		case 20:					// to Bob, Joe table Roland, Andy
			if ($doDebug) {
				echo "doing case 20<br />";
			}
			$thisTo			= "$bob";
			$thisBcc		= "";
			$thisTcc		= "$roland,$andy";
			break;
		case 21:					// to Bob, Joe table Roland, Andy
			if ($doDebug) {
				echo "doing case 21<br />";
			}
			$thisTo			= $theRecipient;
			$thisBcc		= "$bob,$roland,$andy";
			$thisTcc		= "";
			break;
		case 99:					// docker mode. Send only to Roland
			if ($doDebug) {
				echo "doing case 99 (docker mode)<br />";
			}
			$thisTo			= $roland;
			$thisBcc		= "";
			$thisTcc		= "";
			break;
		
		default:
			if ($doDebug) {
				echo "Fell through to default<br />";
			}
			$thisTo			= $theRecipient;
			$thisBcc		= "";
			$thisTcc		= $roland;
			$theSubject		= "TESTMODE $theSubject";	
			break;
	}
	
	if ($thisTo == '') {
		$thisTo				= $roland;
		if ($doDebug) {
			echo "thisTo empty. assumed roland<br />";
		}
	}

	$sendEmail				= TRUE;
							
	if ($testMode) {
		if ($doDebug) {
			echo "Operating in test mode with mailCode of $mailCode. Headers:<br /><pre>";
			print_r($myHeaders);
			echo "</pre><br />";
		}
		if ($increment > 10) {
			$sendEmail		= FALSE;
			if ($doDebug) {
				echo "in testMode and increment gt 10. No email sent<br />";
			}
		}
	} else {
		if ($theCc != '') {
			$myHeaders[]	= "Cc: $theCc";
		}
		if ($thisBcc != '') {
			$myHeaders[]	= "Bcc: $thisBcc";
		}
		if ($doDebug) {
			echo "Operating in production mode with mailCode of $mailCode<br />
			      thisTo of $thisTo<br />
			      theSubject of $theSubject<br />
			      Headers:<br /><pre>";
			print_r($myHeaders);
			echo "</pre><br />";
		}
	}

	if ($doDebug) {
		echo "theAttachment:<br /><pre>";
		print_r($theAttachment);
		echo "</pre><br />";
	}

	if ($sendEmail) {
		$result			= wp_mail($thisTo,$theSubject,$theContent,$myHeaders,$theAttachment);
		if ($result === FALSE) {
			if ($doDebug) {
				echo "Sending the email to $thisTo failed<br />
					   thisTo: $thisTo<br />
					   theSubject; $theSubject<br />
					   Headers:<br /><pre>";
				print_r($myHeaders);
				echo "</pre><br />";
//				echo "Mailer Error: " . $wp_error->get_error_message() ."<br />";

			}
			return FALSE;
		} else {
			if ($doDebug) {
				echo "sending email worked ... writing to email log<br />";
			}
			$emailresult= $wpdb->insert($emailTableName,
										array('email_to'=>$thisTo,
											  'email_cc'=>$theCc,
											  'email_bcc'=>$thisBcc,
											  'email_tcc'=>$thisTcc,
											  'email_subject'=>$theSubject,
											  'email_jobname'=>$jobname,
											  'email_content'=>$theContent),
										array('%s','%s','%s','%s','%s','%s','%s'));
			if ($emailresult === FALSE) {
				if ($doDebug) {
						echo "Inserting record into $emailTableName table failed<br />
							  wpdb->last_query: " . $wpdb->last_query . "<br />
							  <b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
				}
			} else {
				if ($doDebug) {
					echo "Successfully inserted record into $emailTableName table<br />";
				}
			}
		}
		return TRUE;
	}
	return TRUE;
}
add_action ('emailFromCWA_v2', 'emailFromCWA_V2');
