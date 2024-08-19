function display_reminders($inp_role,$inp_callsign,$doDebug=FALSE) {

/*	Reads wpw1_cwa_reminders for the specified role and/or callsign
	If the current date is equal or greater than the effective date 
		and less than the close date, and the reminder is not 
		resolved, format the return
		
	Input:	inp_role:		if the role is administrator, advisor, or student 
							select first any valid reminders for that role.
							
			inp_callsign:	if inp_callsign is specified, select any valid 
							reminders for that callsign
							
	Returns an array:		TRUE / FALSE 	whether or not the program was successful. If
											not the reason is in the returnInfo field
							returnInfo		either the reason for the failure or 
											the data to be displayed, one row for 
											each reminder in table format
											
	returnInfo format:		<table style='width:900px;'>
							<tr><th>Reminders and Actions Requested</th>
								<th>Date Created</th></tr>
							<tr><td>(reminer_text)</td>
								<td>(effective_date)</td></tr>
							<tr>....</tr>
							</table>
*/

// $doDebug = TRUE;

	global $wpdb;
	
	$initializationArray 	= data_initialization_func();
	$siteURL				= $initializationArray['siteurl'];
	
//	$inp_role				= "";
//	$inp_callsign			= "";

	if ($doDebug) {
		echo "<br /><b>FUNCTION: Display Reminders</b><br />
			 inp_role: $inp_role<br />
			 inp_callsign: $inp_callsign<br /><br />";
	}
	
	if ($inp_role == '' && $inp_callsign == '') {
		$returnInfo		= "<b>FUNCTION Display Reminders Error: </b>Either inp_role or 
							inp_callsign must be supplied";
		if ($doDebug) {
			echo "returning error as both inputs are empty<br />";
		}
		return array(FALSE,$returnInfo);
	}
	
	$returnInfo			= "<table style='width:900px;'>
							<tr><th>Reminders and Actions Requested</th>
								<th>Date Created</th></tr>";
	
	$nowTime			= date('Y-m-d H:i:s');
	
	$roleOK				= FALSE;
	if ($inp_role == 'administrator' || $inp_role == 'advisor' || $inp_role == 'student' || $inp_role == '') {
		$roleOK			= TRUE;
	}
	if (!$roleOK) {
		$returnInfo		= "<b>FUNCTION Display Reminders Error: </b>inp_role of $inp_role is invalid";
		if ($doDebug) {
			echo "returning error as both inputs are empty<br />";
		}
		return array(FALSE,$returnInfo);
	}
	
	if ($inp_role != '') {
		$sql			= "select * from wpw1_cwa_reminders 
							where role = '$inp_role'  
							and effective_date <= '$nowTime' 
							and close_date > '$nowTime' 
							and resolved != 'Y' 
							order by date_created";	
		$reminderResult	= $wpdb->get_results($sql);
		if ($reminderResult === FALSE) {
			handleWPDBError("FUNCTION Display Reminders",$doDebug);
			$returnInfo		= "<b>FUNCTION Display Reminders Error: </b>unable to access wpw1_cwa_reminders.<br />
								Error: $last_error<br />Query: $last_query";
			if ($doDebug) {
				echo "returning error as both inputs are empty<br />";
			}
			return array(FALSE,$returnInfo);
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($reminderResult as $reminderRow) {
					$record_id			= $reminderRow->record_id;
					$effective_date		= $reminderRow->effective_date;
					$close_date			= $reminderRow->close_date;
					$resolved_date		= $reminderRow->resolved_date;
					$send_reminder		= $reminderRow->send_reminder;
					$call_sign			= $reminderRow->call_sign;
					$role				= $reminderRow->role;
					$email_text			= $reminderRow->email_text;
					$reminder_text		= $reminderRow->reminder_text;
					$resolved			= $reminderRow->resolved;
					$token				= $reminderRow->token;
					$date_created		= $reminderRow->date_created;
					$date_modified		= $reminderRow->date_modified;
					
					if ($call_sign == '') {
						$call_sign		= $role;
					}

					$removeLink			= "Click <a href='$siteURL/cwa-remove-item/?inp_call_sign=$call_sign&token=$token' target='_blank'>HERE</a> to remove this Reminder</a>";
					$myInt				= strrpos($reminder_text,"</p>");
					if ($myInt === FALSE) {
						$reminder_text	= "$reminder_text<br />$removeLink";
					} else {
						$myStr			= substr($reminder_text,0,$myInt);
						$reminder_text	= "$myStr<br />$removeLink</p>";
					}
					if ($doDebug) {
						echo "formated $reminder_text<br />";
					}
					$returnInfo			.= "<tr><td>$reminder_text</td>
												<td style='vertical-align:top;'>$effective_date<td></tr>";
				}
			}
			if ($inp_callsign !== '') {
				$sql			= "select * from wpw1_cwa_reminders 
									where call_sign = '$inp_callsign'  
									and effective_date <= '$nowTime' 
									and close_date > '$nowTime' 
									and resolved != 'Y' 
									order by date_created";	
				$reminderResult	= $wpdb->get_results($sql);
				if ($reminderResult === FALSE) {
					$lastError	= $wpdb->last_error;
					$lastQuery	=	$wpdb->last_query;
					if ($doDebug) {
						echo "unable to access wpw1_cwa_reminders. Error:$lastError<br />$lastQuery<br />";
					}
					$returnInfo		= "<b>FUNCTION Display Reminders Error: </b>unable to access wpw1_cwa_reminders.<br />
										Error: $last_error<br />Query: $last_query";
					if ($doDebug) {
						echo "returning error as both inputs are empty<br />";
					}
					return array(FALSE,$returnInfo);
				} else {
					$numRows	= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $sql<br />and retrieved $numRows rows<br />";
					}
					if ($numRows > 0) {
						foreach($reminderResult as $reminderRow) {
							$record_id			= $reminderRow->record_id;
							$effective_date		= $reminderRow->effective_date;
							$close_date			= $reminderRow->close_date;
							$resolved_date		= $reminderRow->resolved_date;
							$send_reminder		= $reminderRow->send_reminder;
							$call_sign			= $reminderRow->call_sign;
							$role				= $reminderRow->role;
							$email_text			= $reminderRow->email_text;
							$reminder_text		= $reminderRow->reminder_text;
							$resolved			= $reminderRow->resolved;
							$token			 	= $reminderRow->token;
							$date_created		= $reminderRow->date_created;
							$date_modified		= $reminderRow->date_modified;
				
							$removeLink			= "Click <a href='$siteURL/cwa-remove-item/?inp_call_sign=$call_sign&token=$token' target='_blank'>HERE</a> to remove this Reminder</a>";
							$myInt				= strrpos($reminder_text,"</p>");
							if ($myInt === FALSE) {
								$reminder_text	= "$reminder_text<br />$removeLink";
							} else {
								$myStr			= substr($reminder_text,0,$myInt);
								$reminder_text	= "$myStr<br />$removeLink</p>";
							}
							if ($doDebug) {
								echo "formated $reminder_text<br />";
							}
							$returnInfo			.= "<tr><td>$reminder_text</td>
														<td style='vertical-align:top;'>$effective_date<td></tr>";
						}
					}
				}
			}
		}
	}
	$returnInfo		.= "</table>";
	return array(TRUE,$returnInfo);

} 
add_action('display_reminders','display_reminders');