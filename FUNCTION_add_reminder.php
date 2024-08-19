function add_reminder($inp_data=array(),$testMode=FALSE,$doDebug=FALSE) {

/*
		$effective_date		 	= date('Y-m-d H:i:s');
		$closeStr				= strtotime("+5 days");
		$close_date				= date('Y-m-d H:i:s', $closeStr);
		$token					= mt_rand();
		$email_text				= "<p></p>";
		$reminder_text			= "<p><b>:</b> </p>";"
		$inputParams		= array("effective_date|$effective_date|s",
									"close_date|$close_date|s",
									"resolved_date|$resolved_date|s",
									"send_reminder|$send_reminder|s",
									"send_once|$send_once|s",
									"call_sign|$call_sign|s",
									"role|$role|s",
									"email_text|$email_text|s",
									"reminder_text|$reminder_text|s",
									"resolved|$resolved|s",
									"token||s");
		$insertResult		= add_reminder($inputParams,$testMode,$doDebug);
		if ($insertResult[0] === FALSE) {
			if ($doDebug) {
				echo "inserting reminder failed: $insertResult[1]<br />";
			}
			$content		.= "Inserting reminder failed: $insertResult[1]<br />";
		} else {
			$content		.= "Reminder successfully added<br />";
		}
		
		
		If role is administrator and call_sign is blank, a reminder will be entered 
		for each person in the administratorArray array
		
*/

// $doDebug = TRUE;
 
	global $wpdb;

	if ($testMode) {
		$remindersTableName		= 'wpw1_cwa_reminders2';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor2';
		$studentTableName		= 'wpw1_cwa_consolidated_student2';
	} else {
		$remindersTableName		= 'wpw1_cwa_reminders';
		$advisorTableName		= 'wpw1_cwa_consolidated_advisor';
		$studentTableName		= 'wpw1_cwa_consolidated_student';
	}
	
	$administratorArray			= array('WR7Q','K7OJL');
	$doingAdministrator			= FALSE;
	
// $doDebug = TRUE;	
	if ($doDebug) {
		echo "<br /><b>Add Reminder inp_data:</b><br /><pre>";
		print_r($inp_data);
		echo "</pre><br />";
	}
	

	foreach($inp_data as $myValue) {
		$myArray				= explode("|",$myValue);
		$field					= $myArray[0];
		$fieldValue				= $myArray[1];

		if ($field == 'call_sign') {
			$fieldValue			= strtoupper($fieldValue);
			$thisCallSign		= $fieldValue;
			if ($doDebug) {
				echo "Updated call_sign to $fieldValue<br />";
			}
			$call_sign			= $fieldValue;
		}

		if ($field === 'role') {
			$thisRole			= $fieldValue;
		}

		$fieldFormat			= $myArray[2];
		$updateParams[$field]	= $fieldValue;
		$updateFormat[]			= "%$fieldFormat";
	}
	
	if ($thisRole == 'administrator' && $thisCallSign == '') {
		if ($doDebug) {
			echo "Have role as $thisRole and callsign is blank<br />";
		}
		$doingAdministrator		= TRUE;
	}
	
	
	
	$updateParams['date_created']	= date('Y-m-d H:i:s');
	$updateFormat[]					= '%s';
	$updateParams['date_modified']	= date('Y-m-d H:i:s');
	$updateFormat[]					= '%s';
	$updateIssue					= FALSE;

	if ($doingAdministrator) {
		foreach($administratorArray as $thisAdmin) {
			if ($doDebug) {
				echo "doing administrator for call_sign: $thisAdmin<br />";
			}
			$updateParams['role']			= '';
			$updateParams['call_sign']		= $thisAdmin;
			$myStr							= $updateParams['reminder_text'];
			$myStr1							= str_replace('XXXXX',$thisAdmin,$myStr);
			$updateParams['reminder_text']	= $myStr1;
			if ($doDebug) {
				echo "set reminder_text to $myStr<br />";
			}

			if ($doDebug) {
				echo "ready to update doingAdministrator<br /><pre>";
				print_r($updateParams);
				echo "</pre><br />Done<br />";
			}
			$result				= $wpdb->insert($remindersTableName,
												$updateParams,
												$updateFormat);
			if ($result === FALSE) {
				handleWPDBError("FUNCTION Add Reminder",$doDebug);
				$updateIssue	= TRUE;
			}
			$updateParams['reminder_text']		= $myStr;
			if ($doDebug) {
				echo "reset reminder_text to $myStr<br />";
			}
		}
	} else {
		if ($doDebug) {
			echo "ready to update<br /><pre>";
			print_r($updateParams);
			echo "</pre><br />Done<br />";
		}
		$result				= $wpdb->insert($remindersTableName,
											$updateParams,
											$updateFormat);
		if ($result === FALSE) {
			handleWPDBError("FUNCTION Add Reminder",$doDebug);
			$updateIssue 	= TRUE;
		}
	}
	if ($doDebug) {
		echo "returning<br /><hr><br />";
	}
	if ($updateIssue) {	
		return array(FALSE,"Inserting failed. Error:$lastError<br />Query: $lastQuery");
	} else {
		return array(TRUE,'');
	}

}
add_action('add_reminder','add_reminder');