FUNCTION get_user_role($username,$testMode=FALSE,$doDebug = FALSE) {

/*	using the username, get the user_id from wpw1_users then 
	get the capabilities from usermeta.
	
	returnarray 	= array('result'=>$result,			// either TRUE or FALSE
							'reason'=>$reason, 			// if result is false, the reason else blank
							'isAdvisor'=>$isAdvisor,	// TRUE if user is an advisor
							'isAdmin'=>$isAdmin,		// TRUE if user is an admin
							'isStudent'=>$isStudent, 	// TRUE if user is a student
							'isOther'=>$isOther);		// none of the above
*/

	if ($doDebug) {
		echo "<br /><b>FUNCTION: Get User Role</b>
			  <br />userName: $username<br />";
	}
	if ($username == '') {
		$returnArray		= array('result'=>FALSE,
								    'reason'=>'supplied username is empty');
		return $returnArray;
	}
	
	if ($testMode) {
		if ($doDebug) {
			echo "Operating in testmode<br />";
		}
		$usersTableName			= 'wpw1_users2';
		$userMetaTableName		= 'wpw1_usermeta2';
	} else {
		$usersTableName			= 'wpw1_users';
		$userMetaTableName		= 'wpw1_usermeta';
	}
	
	global $wpdb;
	
	$isAdvisor					= FALSE;
	$isAdmin					= FALSE;
	$isStudent					= FALSE;
	$isOther					= TRUE;
	// get the users record
	$sql						= "select * from $usersTableName 
									where user_login like '%$username%'";
	$usersResult				= $wpdb->get_results($sql);
	if ($usersResult === FALSE) {
		handleWPDBError("FUNCTION_get_user_role",$doDebug);
		$returnArray			= array('result'=>FALSE,
										'reason'=>"reading wpw1_users for $username returned FALSE");
		return $returnArray;
	} else {
		$numURows				= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and retrieved $numURows rows<br />";
		}
		if ($numURows > 0) {
			foreach($usersResult as $userRow) {
				$user_id		= $userRow->ID;
				
				// now get the capabilities from usermeta
				$metaSQL		= "select * from $userMetaTableName 
									where user_id = $user_id 
									and meta_key = 'wpw1_capabilities'";
				$metaResult		= $wpdb->get_results($metaSQL);
				if ($metaResult === FALSE) {
					handleWPDBError("FUNCTION_get_user_role",$doDebug);
					$returnArray 	= array('result'=>FALSE,
											'reason'=>"Reading userMeta with id $user_id for $username returned FALSE");
					return $returnArray;
				} else {
					$numMRows	= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $metaSQL<br />and retrieved $numMRows rows<br />";
					}
					if ($numMRows > 0) {
						foreach($metaResult as $metaRow) {
							$user_capabilities	= $metaRow->meta_value;
							
							if (preg_match("/advisor/i",$user_capabilities)) {
								$isAdvisor		= TRUE;
								$isOther		= FALSE;
							}
							if (preg_match("/admin/i",$user_capabilities)) {
								$isAdmin		= TRUE;
								$isOther		= FALSE;
							}
							if (preg_match("/student/i",$user_capabilities)) {
								$isStudent		= TRUE;
								$isOther		= FALSE;
							}
							
							$returnArray		= array('result'=>TRUE,
														'isAdvisor'=>$isAdvisor,
														'isAdmin'=>$isAdmin,
														'isStudent'=>$isStudent,
														'isOther'=>$isOther);
							return $returnArray;
						}
					} else {
						$returnArray			= array('result'=>FALSE,
														'reason'=>"No rows found in usermeta for id $user_id $username");
						return $returnArray;
					}
				}
			}
		} else {
			$returnArray			= array('result'=>FALSE,
											'reason'=>"No rows found in wpw1_users for username $username");
			return $returnArray;
		}
	}
	

}
add_action('get_user_role','get_user_role');