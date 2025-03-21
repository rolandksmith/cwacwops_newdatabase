function list_all_pages_func() {

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser 			= $initializationArray['validUser'];
	$userName			= $initializationArray['userName'];
	$currentTimestamp	= $initializationArray['currentTimestamp'];
	$validTestmode		= $initializationArray['validTestmode'];
	$siteURL			= $initializationArray['siteurl'];
	
//	CHECK THIS!								//////////////////////
	if ($userName == '') {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}

//	ini_set('memory_limit','256M');
//	ini_set('max_execution_time',0);
//	set_time_limit(0);

//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$theURL						= "$siteURL/cwa-list-all-pages/";
	$inp_semester				= '';
	$inp_rsave					= '';
	$existsArray				= array();
	$pageTitles					= array();

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				if (!is_array($str_value)) {
					echo "Key: $str_key | Value: $str_value <br />\n";
				} else {
					echo "Key: $str_key (array)<br />\n";
				}
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
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
				</style>";	

	function findSubstringsInBrackets($string) {
		// Use a regular expression to find substrings within brackets
		preg_match_all('/\[(.*?)\]/', $string, $matches);
		
		// Return the found substrings
		return $matches[1]; // $matches[1] contains the substrings found within the brackets
	}
				

	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$content 		.= "<h3>Click Submit to Start the Process</h3>
							<p>
							<form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table style='border-collapse:collapse;'>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";

///// Pass 2 -- do the work

	} elseif ("2" == $strPass) {

		global $shortcode_tags;
 
 		// get an array of available shotcodes
 		$availableCodesArray	= array();
 
		$skipArray = array('access',
							'audio',
							'avatar',
							'caption',
							'code_snippet_source',
							'code_snippet',
							'embed',
							'feed',
							'gallery',
							'get_avatar',
							'is_user_logged_in',
							'login-form',
							'members_access',
							'members_feed',
							'members_logged_in',
							'members_login_form',
							'members_not_logged_in',
							'playlist',
							'table-info',
							'table',
							'video',
							'wp_caption',
							'wpum_login_form',
							'wpum_login',
							'wpum_logout',
							'wpum_password_recovery',
							'wpum_profile_card',
							'wpum_profile',
							'wpum_recently_registered',
							'wpum_register',
							'wpum_restrict_logged_in',
							'wpum_restrict_logged_out',
							'wpum_restrict_to_user_roles',
							'wpum_restrict_to_users',
							'wpum_user_directory');

 
 
 		foreach($shortcode_tags as $thisCode => $thisValue) {
 			if (!in_array($thisCode,$skipArray)) {
 				$availableCodesArray[]	= $thisCode;
 			}
 		}
 		sort($availableCodesArray);
		// availableCodesArray is built

	
		// now get all the pages
		// for each page check to see if the shortcode is available
		// put the page shortcode in an array to see what shortcodes aren't needed
		
		$usedShortCodesArray	= array();
		
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
		
//		echo "pages array:<br /><pre>";
//		print_r($pages);
//		echo "</pre><br /><br />";

		$content			.= "<h3>List All Pages</h3>
								<table style='width:1000px;'>
								<tr><th>Title</th>
									<th>URL</th>
									<th>Snippet</th>
									<th style='width:100px;'>Notes</th></tr>";		

		foreach($pages as $page) { 	// $pages is array of object
			$thisTitle		= $page->post_title;
			$thisURL2a		= $page->post_name;
			$thisSnippet	= $page->post_content;
			
			$pageTitleString	= str_replace("CWA - ","",$thisTitle);
			$pageTitles[]		= $pageTitleString;
			
			$thisURL		= "$siteURL/$thisURL2a/";
			$myPos			= strpos($thisSnippet,'[');
			if ($myPos === FALSE) {
				$thisSnippet	= "Contains HTML";
			} else {			//// there is probably at least 1 snippet
				$substrings = findSubstringsInBrackets($thisSnippet);
			}

			$existsArray[]	= "[$thisURL' target='_blank'|$thisTitle]";
			
			$snippetList	= '';
			$myStr			= '';
			$firstTime		= TRUE;
			if (count($substrings) > 0) {
				foreach($substrings as $foundSnippet) {
					$ii		= strpos($foundSnippet,'/');
					if ($ii === FALSE) {
					if ($firstTime) {
							$firstTime	= FALSE;
							$snippetList = "$foundSnippet";
							if (!in_array($foundSnippet,$availableCodesArray)) {
								$myStr		.= "no shortcodes";
							}
							$usedCodesArray[]	= $foundSnippet;
						} else {
							$snippetList .= "<br />$foundSnippet";
							if (!in_array($foundSnippet,$availableCodesArray)) {
								$myStr		.= "<br />no shortcodes";
							}
							$usedCodesArray[]	= $foundSnippet;
						}
					}
				}
			}			

			
			$content		.= "<tr><td style='vertical-align:top;'>$thisTitle</td>
									<td style='vertical-align:top;'>$thisURL</td>
									<td style='vertical-align:top;'>$snippetList</td>
									<td style='vertical-align:top;'>$myStr</td></tr>";
			
		}
		$content			.= "</table><br /><br />";
		
		// see if there are any unused snippets
		$content			.= "<h4>Unused Shortcodes</h4>
								<table style='width:500px;'>";
		foreach($availableCodesArray as $thisCode) {
			$content		.= "<tr><td>$thisCode</td>";
			if (!in_array($thisCode,$usedCodesArray)) {
				$content	.= "<td>Unused</td></tr>";
			} else {
				$content	.= "<td></td</tr>";
			}
		}
		$content			.= "</table>";
		
		$content		.= "<br /><br /><h4>Alphabetic List</h4><br /><pre><code>";
		foreach($existsArray as $thisTitle) {
			$content		.= "$thisTitle<br />";
		}
		$content			.= "</code></pre>";
		
		// put out an alphabetic list of all pages
		sort($pageTitles);
		$content			.= "<h4>Page Titles</h4>";
		foreach($pageTitles as $myStr) {
			$content		.= "$myStr<br />";
		}
	
	}
	$thisTime 		= date('Y-m-d H:i:s');
	$content 		.= "<br /><br /><p>Prepared at $thisTime</p>";
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	$content		.= "<p>Report pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	if ($testMode) {
		$thisStr	= 'Testmode';
	}
	$ipAddr			= get_the_user_ip();
	$result			= write_joblog_func("List All Pages|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('list_all_pages', 'list_all_pages_func');
