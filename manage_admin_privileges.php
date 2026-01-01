function manage_admin_privileges_func() {

	global $wpdb, $doDebug, $debugLog, $siteURL;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	$validUser 						= $initializationArray['validUser'];

	$versionNumber				 	= "1";
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	$userEmail			= $initializationArray['userEmail'];
	$userDisplayName	= $initializationArray['userDisplayName'];
	$userRole			= $initializationArray['userRole'];
	
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

	ini_set('display_errors','1');
	error_reporting(E_ALL);	
	$wpdb->show_errors();

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-manage-admin-privileges/";
	$inp_semester				= '';
	$jobname					= 'Manage Admin Privileges';
	$inp_rsave					= '';
	$existsArray				= array();
	$pageTitles					= array();

	function debugReport($message) {
		global $debugLog, $doDebug;
		$timestamp = current_time('mysql', 1);
		$debugLog .= "$message ($timestamp)<br />";
		if ($doDebug) {
			echo "$message<br />";
		}
	}
	
	debugReport("Initialization Array:<br /><pre>");
	$myStr = print_r($initializationArray, TRUE);
	debugReport("$myStr</pre>");
	
	
// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					debugReport("Key: $str_key | Value: $str_value");
				} else {
					debugReport("Key: $str_key (array)");
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_call_sign") {
				$inp_call_sign		 = strtoupper($str_value);
				$inp_call_sign		 = filter_var($inp_call_sign,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "pages_selected") {
				$pages_selected		 = $str_value;
//				$pages_selected		 = filter_var($pages_selected,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_reminders") {
				$inp_reminders		 = $str_value;
				$inp_reminders		 = filter_var($inp_reminders,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_admin_reminders") {
				$inp_admin_reminders		 = $str_value;
				$inp_admin_reminders		 = filter_var($inp_admin_reminders,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_pages") {
				$inp_pages		 = $str_value;
				$inp_pages		 = filter_var($inp_pages,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'verbose') {
					$doDebug	= TRUE;
				}
			}
			if ($str_key 		== "inp_mode") {
				$inp_mode	 = $str_value;
				$inp_mode	 = filter_var($inp_mode,FILTER_UNSAFE_RAW);
				if ($inp_mode == 'TESTMODE') {
					$testMode = TRUE;
				}
			}
		}
	}
	
	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	= "<tr><td>Operation Mode</td>
								<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
									<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
	}
	
	
	$content = "<style type='text/css'>
				fieldset {font:'Times New Roman', sans-serif;color:#666;background-image:none;
				background:#efefef;padding:2px;border:solid 1px #d3dd3;}
				
				legend {font:'Times New Roman', sans-serif;color:#666;font-weight:bold;
				font-variant:small-caps;background:#d3d3d3;padding:2px 6px;margin-bottom:8px;}
				
				label {font:'Times New Roman', sans-serif;font-weight:bold;line-height:normal;
				text-align:right;margin-right:10px;position:relative;display:block;float:left;width:150px;}
				
				textarea.formInputText {font:'Times New Roman', sans-serif;color:#666;
				background:#fee;padding:2px;border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}
				
				textarea.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				textarea.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				input.formInputText {color:#666;background:#fee;padding:2px;
				border:solid 1px #f66;margin-right:5px;margin-bottom:5px;}
				
				input.formInputText:focus {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				input.formInputText:hover {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				input.formInputFile {color:#666;background:#fee;padding:2px;border:
				solid 1px #f66;margin-right:5px;margin-bottom:5px;height:20px;}
				
				input.formInputFile:focus {color:#000;background:#ffffff;border:solid 1px #006600;}
				
				select.formSelect {color:#666;background:#fee;padding:2px;
				border:solid 1px #f66;margin-right:5px;margin-bottom:5px;cursor:pointer;}
				
				select.formSelect:hover {color:#333;background:#ccffff;border:solid 1px #006600;}
				
				input.formInputButton {vertical-align:middle;font-weight:bolder;
				text-align:center;color:#300;background:#f99;padding:1px;border:solid 1px #f66;
				cursor:pointer;position:relative;float:left;}
				
				input.formInputButton:hover {color:#f8f400;}
				
				input.formInputButton:active {color:#00ffff;}
				
				tr {color:#333;background:#eee;}
				
				table{font:'Times New Roman', sans-serif;background-image:none;border-collapse:collapse;}
				
				th {color:#ffff;background-color:#000;padding:5px;font-size:small;}
				
				td {padding:5px;font-size:small;}
				
				th:first-child,
				td:first-child {
				 padding-left: 10px;
				}
				
				th:last-child,
				td:last-child {
					padding-right: 5px;
				}
		
		.info-asterisk {
			cursor: help;
			color: #d9534f;
			font-weight: bold;
			padding: 0 4px;
			position: relative; /* Keeps the tooltip anchored to this element */
			display: inline-block;
		}
		
		/* The updated tooltip box */
		.hover-popup {
			position: absolute;
			background: #333;
			color: #fff;
			padding: 8px 12px;
			border-radius: 4px;
			font-size: 13px;
			z-index: 1000;
			
			/* Position logic */
			bottom: 150%;      /* Places it above the asterisk */
			left: 0;           /* Aligns the left edge of the box with the asterisk */
			
			/* Responsive width logic */
			width: max-content; 
			max-width: 250px;   /* Prevents it from being too wide */
			white-space: normal; /* Allows text to wrap if it hits max-width */
			word-wrap: break-word;
		
			/* Smooth entry */
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.2s ease-in-out;
			box-shadow: 0 4px 8px rgba(0,0,0,0.3);
		}
		
		/* Add a small arrow pointing down */
		.hover-popup::after {
			content: '';
			position: absolute;
			top: 100%; 
			left: 10px; /* Aligns arrow with the start of the text */
			border-width: 5px;
			border-style: solid;
			border-color: #333 transparent transparent transparent;
		}
		
		/* Show on hover */
		.info-asterisk:hover .hover-popup {
			opacity: 1;
			visibility: visible;
		}		
	</style>";	

	function findSubstringsInBrackets($string) {
		// Use a regular expression to find substrings within brackets
		preg_match_all('/\[(.*?)\]/', $string, $matches);
		
		// Return the found substrings
		return $matches[1]; // $matches[1] contains the substrings found within the brackets
	}

	function generatePagesSelection($existingArray) {

	/**
		* @param array $existingArray 	array of pages currently authorized
		* @returns string $selectList	list of pages with already selected checked
		*
		* <input type='checkbox' id='page_nn' name='pages_selected[]' value='(page url)' (checked)>
		*	<label for='page_nn'>(page title)</label>
		*
	*/
			
		global $wpdb, $doDebug, $debugLog, $siteURL;

		$selectList = "";
		$pageNum = 0;
		
		// get all the pages
		
		$args = array(
		'sort_order' => 'asc',
		'sort_column' => 'post_title',
		'hierarchical' => 1,
		'exclude' => '',
		'include' => '',
		'meta_key' => '',
		'meta_value' => '',
		'authors' => '',
		'child_of' => 0,
		'parent' => -1,
		'exclude_tree' => '',
		'number' => '',
		'offset' => 0,
		'post_type' => 'page',
		'post_status' => 'publish'
		); 
		$pages = get_pages($args); // get all pages based on supplied args
		
//		debugReport("pages array:<pre>");
//		$myStr = print_r($pages, TRUE);
//		debugReport("$myStr</pre>");
		
		foreach($pages as $thisPage) {
			$thisTitle		= $thisPage->post_title;
			if (str_contains($thisTitle,'CWA - ')) {
				$thisURL2a		= $thisPage->post_name;
				
				$pageTitleString	= str_replace("CWA - ","",$thisTitle);
				$thisURL		= "$thisURL2a&$pageTitleString";
				$thisChecked = '';
				if (in_array($pageTitleString,$existingArray)) {
					$thisChecked = ' checked ';
				}
				
				$pageNum++;
				$selectListEntry = "<input type='checkbox' class='formInputButton' 
				name='pages_selected[]' value='$thisURL' $thisChecked>
				$pageTitleString<br />\n";
				$selectList .= $selectListEntry;
			}
		}
		return $selectList;
	}
				

	if ($testMode) {
		$operatingMode = 'Testmode';
		$adminTableName = 'wpw1_cwa_admin_priviliges2';
	} else {
		$operatingMode = 'Production';
		$adminTableName = 'wpw1_cwa_admin_priviliges';
	}
	
	$user_dal = new CWA_User_Master_DAL();

	if ("1" == $strPass) {
		if ($doDebug) {
			debugReport("<br />Function starting Pass 1");
		}
		$content 		.= "<h3>$jobname</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							<tr><td>Admin Callsign</td>
								<td><input type='text' class='formInputText' size='20' maxlength='20' name='inp_call_sign' required autofocus></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";

///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {

		$content .= "<h3>$jobname</h3>";

		// see if there is a record in the priviliges table for inp_call_sign
		// if so, do update
		// if not, do insert
		
		$haveAdminData = FALSE;
		if ($doDebug) {
			debugReport("seeing if there is a record for $inp_call_sign in $adminTableName");
		}
		$sql = "select * from $adminTableName where ap_call_sign = '$inp_call_sign'";
		$adminData = $wpdb->get_results($sql);
		if ($adminData === FALSE || $adminData === NULL) {
			if ($doDebug) {
				debugReport("getting admin data for $inp_call_sign returned FALSE|NULL");
			}
		} else {
			$numRows = $wpdb->num_rows;
			if ($doDebug) {
				$lastQuery = $wpdb->last_query;
				debugReport("ran $lastQuery<br />and retrieved $numRows rows");
			}
			if ($numRows > 0) {
				foreach($adminData as $adminRow) {
					$ap_id = $adminRow->ap_id;
					$ap_call_sign = $adminRow->ap_call_sign;
					$ap_pages = $adminRow->ap_pages;
					$ap_pages_select = $adminRow->ap_pages_select;
					$ap_reminders = $adminRow->ap_reminders;
					$ap_reminders_select = $adminRow->ap_reminders_select;
					$ap_date_updated = $adminRow->ap_date_updated;
					$ap_date_created = $adminRow->ap_date_created;
					
					$haveAdminData = TRUE;
					if ($doDebug) {
						debugReport("haveAdminData set to TRUE");
					}
				}
			}
		}
		
		if ($haveAdminData){
			$content .= "<h4>Current Privileges for $inp_call_sign</h4>";
		} else {
			// is inp_call_sign an admin?
			if ($doDebug) {
				debugReport("no record in $adminTableName for $inp_call_sign. Checking user_master to see if an admin");
			}
			$userData = $user_dal->get_user_master_by_callsign($inp_call_sign, $operatingMode);
			if ($userData === FALSE || $userData === NULL){
				if ($doDebug) {
					debugReport("getting user_master for $inp_call_sign returned FALSE|NULL");
				}
			} else {
				if (! empty($userData)) {
					foreach($userData as $key => $value) {
						foreach($value as $thisKey => $thisValue) {
							$$thisKey = $thisValue;
						}
						if (! isset($user_call_sign)) {
							if ($doDebug) {
								debugReport("supposedly have userData but user_call_sign not set");
							}
							$content .= "<p>No User Master record for $inp_call_sign</p>";
						} else {
							if ($user_is_admin == 'Y') {
								$content .= "<h4>Creating Record for $inp_call_sign</h4>
											<form method='post' action='$theURL' 
											name='selection_form' ENCTYPE='multipart/form-data'>
											<input type='hidden' name='strpass' value='3'>
											<input type='hidden' name='inp_verbose' value='$inp_verbose'>
											<input type='hidden' name='inp_mode' value = '$inp_mode'>
											<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
											<table style='border-collapse:collapse;'>
											<tr><td><b>Creating Record for</b></td>
												<td>$user_last_name, $user_first_name ($user_call_sign)</td></tr>
											<tr><td style='vertical-align:top;'>Pages Privileges</td>
												<td><input type='radio' class='formInputButton' name='inp_pages' value='all' required>All<br />
													<input type='radio' class='formInputButton' name='inp_pages' value='selected' required>Selected</td></tr>
											<tr><td style='vertical-align:top;'>Reminders Privileges</td>
												<td><input type='radio' class='formInputButton' name='inp_reminders' value='Yes' required>Receives Admin Reminders<br />
													<input type='radio' class='formInputButton' name='inp_reminders' value='No' required>No Admin Reminders</td></tr>
											<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
											</form></p>";
							} else {
								$content .= "<p>$inp_call_sign is not designated as an admin in User_Master</p>";
							}
						}
					}
				} else {
					$content .= "<p>No data returned from User_Master for $inp_call_sign</p>";
				}
			}
		}
	} elseif ("3" === $strPass) {
		// getting the pages and reminders for the new admin
		
		if ($doDebug) {
			debugReport("<br /> at pass 3<br />
						inp_pages: $inp_pages<br />
						inp_reminders: $inp_reminders");
		}
		$content	.= "<h3>$jobname</h3>";	
		if ($inp_pages == 'selected') {
			if ($doDebug) {
				debugReport("inp_pages is select. Creating selectionlist");
			}
			$existingArray = array('');
			$selectionList = generatePagesSelection($existingArray);
			if($doDebug) {
				debugReport("<br />selectionList:<br />$selectionList");
			}
			$content .= "<h4>Select Functions Authorized for $inp_call_sign</h4>
						<form method='post' action='$theURL' 
						name='selection_form' ENCTYPE='multipart/form-data'>
						<input type='hidden' name='strpass' value='4'>
						<input type='hidden' name='inp_verbose' value='$inp_verbose'>
						<input type='hidden' name='inp_mode' value = '$inp_mode'>
						<input type='hidden' name='inp_call_sign' value='$inp_call_sign'>
						<input type='hidden' name='inp_reminders' value='$inp_reminders'>
						<table style='border-collapse:collapse;'>
						<tr><td style=vertical-align:top;'>Available Programs</td>
							<td>$selectionList</td>
						<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
						</form></p>";
		} else {
			// have all data needed to add the record
			$insertArray = array('ap_call_sign' => $inp_call_sign, 
								 'ap_pages' => 'all', 
								 'ap_pages_select' => '', 
								 'ap_reminders' => $inp_reminders, 
								 'ap_reminders_select' => '');
			$insertResult = $wpdb->insert($adminTableName,
									$insertArray,
									array('%s', '%s', '%s', '%s', '%s'));
			if ($insertResult === FALSE || $insertResult === NULL) {
				if ($doDebug) {
					debugReport("inserting $inp_call_sign record returned FALSE|NULL<br />
						insertArray:<pre>");
					$myStr = print_r($insertArray, TRUE);
					debugReport("$myStr</pre>");
				}
				$content .= "<p>Failed to insert $inp_call_sign record</p>";
			} else {
				$insertID = $wpdb->insert_id;
				$content .= "<p>Added record for $inp_call_sign as ap_id $insertID</p> 
							 <p>Pages: All</p>
							 <p>Admin Reminders: $inp_reminders</p>";
			}
		}

	} elseif ("4" == $strPass) {	// insert the data in the table
		if ($doDebug) {
			debugReport("<br />At pass 4<br /><br />
				pages_selected:<pre>");
			$myStr = print_r($pages_selected, TRUE);
			debugReport("$myStr</pre>");
			
		}
		
		$content .= "<h3>$jobname</h3>";
		
		// format the authorized pages
		$allPages = "";
		foreach($pages_selected as $thisKey => $thisPage) {
			$myArray = explode('&',$thisPage);
			$myURL = $myArray[0];
			$myTitle = $myArray[1];
			
			$fullEntry = "<li><a href='\$siteURL/$myURL' target='_blank'>$myTitle</a>\n";
			$allPages .= $fullEntry;
		}
		
		// insert record
		$myJsonStr = json_encode($pages_selected);
		$insertArray = array('ap_call_sign' => $inp_call_sign, 
							 'ap_pages' => 'select', 
							 'ap_pages_select' => $myJsonStr, 
							 'ap_reminders' => $inp_reminders, 
							 'ap_reminders_select' => '');
		$insertResult = $wpdb->insert($adminTableName,
								$insertArray,
								array('%s', '%s', '%s', '%s', '%s'));
		if ($insertResult === FALSE || $insertResult === NULL) {
			if ($doDebug) {
				debugReport("inserting $inp_call_sign record returned FALSE|NULL<br />
					insertArray:<pre>");
				$myStr = print_r($insertArray, TRUE);
				debugReport("$myStr</pre>");
			}
			$content .= "<p>Failed to insert $inp_call_sign record</p>";
		} else {
			$insertID = $wpdb->insert_id;
			$content .= "<p>Added record for $inp_call_sign as ap_id $insertID</p> 
						 <p>Receive Admin Reminders? $inp_reminders</p>
						 <p>Authorized Programs:
						<br />$allPages</p>";
		}
	}

	
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> $doDebug);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		$content	.= "<p>writing to joblog failed</p>";
	}
	return $content;
}
add_shortcode ('manage_admin_privileges', 'manage_admin_privileges_func');
