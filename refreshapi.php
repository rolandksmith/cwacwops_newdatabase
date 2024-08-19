<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
header("Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE");

if (isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	  // need preflight here
	  header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept' );
	  // add cache control for preflight cache
	  // @link https://httptoolkit.tech/blog/cache-your-cors/
	  header( 'Access-Control-Max-Age: 86400' );
	  header( 'Cache-Control: public, max-age=86400' );
	  header( 'Vary: origin' );
	  header("HTTP/1.1 200 OK");
	  // just exit and CORS request will be okay
	  // NOTE: We are exiting only when the OPTIONS preflight request is made
	  // because the pre-flight only checks for response header and HTTP status code.
	  exit( 0 );
}

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);


	$doDebug = FALSE;
	$callsign = '';
	$role = '';


	if ($doDebug) {
		echo "refreshapi called\n";
	}


	$doProceed = TRUE;
	$thisdate = date('Y-m-d H:i:s');
	$passwd = "7B-m)p7d2S";

	//  connect to the database
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	$link = mysqli_connect(NULL,"cwacwops_wp540",$passwd,"cwacwops_wp540");
	if ($doDebug) {
		echo "database link established\n";
	}

	/*	expecting to get two variables 
		callsign will contain the callsign info to get any remainders
		role will contain the role info to get any reminders
	*/

	if ($doDebug) {
		print_r($_POST,TRUE);
		echo "\n";
	}

	// get the data being posted
	if (isset($_POST['callsign'])) {
		$callsign =  $_POST['callsign'];
		if ($doDebug) {
			echo "retrieved callsign: $callsign\n";
		}
	}
	if (isset($_POST['role'])) {
		$role = $_POST['role'];
		if ($doDebug) {
			echo "retrieved role: $role\n";
		}
	}

	if ($callsign== '' && role == '') {
		if ($doDebug) {
			echo "callsign and role are both empty\n";
		}
		$doProceed = FALSE;
		header("HTTP/1.1 400 NO");	
	}


	if ($doProceed) {

		$callsign = strtoupper($callsign);
		$role = strtolower($role);
	
		$nowDate = date('Y-m-d H:i:s');
		$query = "SELECT * FROM `wpw1_cwa_reminders` 
					WHERE (call_sign = '$callsign' or role='$role') 
					and resolved != 'Y' 
					and effective_date <= '$nowDate' 
					and close_date > '$nowDate' 
					order by record_id;";
	
	
		if ($doDebug) {
			echo "query: $query\n";
		}
				
		$result = mysqli_query($link, $query, MYSQLI_STORE_RESULT);

		if ($result === FALSE) {
			if ($doDebug) {	
				echo "result was FALSE\n";
			}
			header("HTTP/1.1 400 NO");
		} else {
			if ($doDebug) {
				echo "result was TRUE\n";
				print_r($result);
				echo "\n";
			}
				$myDate				= date('d-M-y @  H:i');
				$returnInfo			= "
<table style='width:900px;'>
<tr><th>Reminders and Actions Requested&nbsp;&nbsp;&nbsp;&nbsp;$myDate UTC</th>
<th>Date Created</th></tr>\n";

			// get the data
			while ($row = mysqli_fetch_assoc($result)) {
				if ($row == NULL) {
					echo "fetching a row returned NULL\n";
				} else {
					$effective_date	= $row['effective_date'];
					$close_date		= $row['close_date'];
					$resolved_date	= $row['resolved_date'];
					$send_reminder	= $row['send_reminder'];
					$send_once		= $row['send_once'];
					$call_sign		= $row['call_sign'];
					$role			= $row['role'];
					$email_text		= $row['email_text'];
					$reminder_text	= stripslashes($row['reminder_text']);
					$resolved		= $row['resolved'];
					$token			= $row['token'];

					if ($doDebug) {			
						echo "\nHave Data:\n
								effective_date: $effective_date\n
								close_date: $close_date\n
								call_sign: $call_sign\n
								role: $role\n
								token: $token\n\n\n";
					}
						
						
					$reminder_text			= str_replace("\t","",$reminder_text);
					if ($call_sign != '') {
						$removeLink			= "<a href='https://cwa.cwops.org/cwa-remove-item/?inp_call_sign=$call_sign&token=$token' target='_blank'>Remove Item</a>";
					} else{
						$removeLink			= "<a href='https://cwa.cwops.org/cwa-remove-item/?inp_call_sign=$role&token=$token' target='_blank'>Remove Item</a>";
					}
					$myInt				= strrpos($reminder_text,"</p>");
					if ($myInt === FALSE) {
						$reminder_text	= "$reminder_text<br />$removeLink";
					} else {
						$myStr			= substr($reminder_text,0,$myInt);
						$reminder_text	= "$myStr<br />$removeLink</p>";
					}
					$returnInfo			.= "
<tr><td>$reminder_text</td>\n
<td style='vertical-align:top;'>$effective_date<td></tr>\n";
				}
			}
			$returnInfo					.= "</table>\n";
		}
		$link->close();
	} else {
		if ($doDebug) {
			echo "doProceed was FALSE\n";
		}
	//	header("HTTP/1.1 400 NO");
	}
	echo $returnInfo;
// echo "\ndone\n";
 exit(0);
// curl -v -X POST https://cwa.cwops.org/wp-content/uploads/refreshapi.php -d 'role=administrator&callsign=K7OJL' 
?>