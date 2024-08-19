function display_student_evaluation_of_advisors_func() {

/*

	Modified 13Jul23 by Roland to use consolidated tables
*/

	global $wpdb;

	$doDebug						= FALSE;
	$testMode						= FALSE;
	$initializationArray 			= data_initialization_func();
	if ($doDebug) {
		echo "Initialization Array:<br /><pre>";
		print_r($initializationArray);
		echo "</pre><br />";
	}
	$validUser = $initializationArray['validUser'];
	if ($validUser == "N") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodby";
	}
	$validTestmode				= $initializationArray['validTestmode'];

	$userName					= $initializationArray['userName'];
//	if ($doDebug) {
		ini_set('display_errors','1');
		error_reporting(E_ALL);	
//	}

	$siteURL			= $initializationArray['siteurl'];

/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	$strPass					= "1";
	$requestType				= '';
	$evaluateid					= '';
	$theSemester				= '';
	$newSemester				= '';
	$theURL						= "$siteURL/cwa-display-student-evaluation-of-advisors/";

// get the input information
	if (isset($_REQUEST)) {
		foreach($_REQUEST as $str_key => $str_value) {
			if ($doDebug) {
				echo "Key: $str_key | Value: $str_value <br />\n";
			}
			if ($str_key 		== "strpass") {
				$strPass		 = $str_value;
				$strPass		 = filter_var($strPass,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "requestType") {
				$requestType	 = $str_value;
				$requestType	 = filter_var($requestType,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "evaluateid") {
				$evaluateid	 = $str_value;
				$evaluated	 = filter_var($evaluateid,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "theSemester") {
				$theSemester	 = strtoupper($str_value);
				$evaluated	 = filter_var($theSemester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "newSemester") {
				$newSemester	 = strtoupper($str_value);
				$evaluated	 = filter_var($newSemester,FILTER_UNSAFE_RAW);
			}
			if ($str_key 		== "inp_verbose") {
				$inp_verbose	 = $str_value;
				$inp_verbose	 = filter_var($inp_verbose,FILTER_UNSAFE_RAW);
				if ($inp_verbose == 'Y') {
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

table{font:'Times New Roman', sans-serif;background-image:none;}

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

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$evaluateAdvisorTableName				= "wpw1_cwa_evaluate_advisor2";
		$advisorTableName					= "wpw1_cwa_consolidated_advisor2";
	} else {
		$evaluateAdvisorTableName				= "wpw1_cwa_evaluate_advisor";
		$advisorTableName					= "wpw1_cwa_consolidated_advisor";
	}

	if (in_array($userName,$validTestmode)) {			// give option to run in test mode 
		$testModeOption	 = "<tr><td>Operation Mode</td>
							<td><input type='radio' class='formInputButton' name='inp_mode' value='Production' checked='checked'> Production<br />
								<input type='radio' class='formInputButton' name='inp_mode' value='TESTMODE'> TESTMODE</td></tr>
							<tr><td>Verbose Debugging?</td>
								<td><input type='radio' class='formInputButton' name='inp_verbose' value='N' checked='checked'> Standard Output<br />
									<input type='radio' class='formInputButton' name='inp_verbose' value='Y'> Turn on Debugging </td></tr>";
	} else {
		$testModeOption	= '';
	}


	if ("1" == $strPass) {
		if ($doDebug) {
			echo "Function starting.<br />";
		}
		$currentSemester	= $initializationArray['currentSemester'];
		$prevSemester		= $initializationArray['prevSemester'];
		if ($currentSemester == 'Not in Session') {
			$theSemester	= $prevSemester;
		} else {
			$theSemester	= $currentSemester;
		}
		$content 		.= "<h3>Display Student Evaluation of Advisors</h3>
							<p>Specify the semester and click Submit to Start the Process</p>
							<p><form method='post' action='$theURL' 
							name='selection_form' ENCTYPE='multipart/form-data'>
							<input type='hidden' name='strpass' value='2'>
							<table>
							<tr><td style='width:150px;'>Semester</td>
								<td><input type='radio' name='theSemester' value='$theSemester' class='formInputButton' checked> $theSemester<br />
									<input type='radio' name='theSemester' value='specified' class='formInputButton'> Specify semester:<br />
									<input type='text' name='newSemester' class='formInputText' size='15' maxlength='15'></td></tr>
							$testModeOption
							<tr><td colspan='2'><input class='formInputButton' type='submit' value='Submit' /></td></tr></table>
							</form></p>";
		return $content;

///// Pass 2 -- do the work


	} elseif ("2" == $strPass) {
		$countsArray				= array();
		$totalResponses				= 0;
		$anonymousResponses			= 0;
		$nonAnonymousResponses		= 0;
		$responseRate				= 0;
		$beginnerCounts				= 0;
		$fundamentalCounts				= 0;
		$intermediateCounts			= 0;
		$advancedCounts				= 0;
		$beginnerComments			= array();
		$fundamentalComments				= array();
		$intermediateComments		= array();
		$advancedComments			= array();
		$studentRespondingArray		= array();
		$beginnerApplications		= array();
		$fundamentalApplications			= array();
		$intermediateApplications	= array();
		$advancedApplications		= array();

// Set up the studentRespondingArray
		$arrayCategories = array(
'effective',
'expectations',
'curriculum',
'scales',
'morse_runner',
'morse_trainer',
'rufzxp',
'lcwo',
'cwt',
'qsos',
'short_stories',
'enjoy_class'
);
		$arrayTypes = array(
'Beginner',
'Fundamental',
'Intermediate',
'Advanced'
);
		foreach($arrayTypes as $value) {
			foreach($arrayCategories as $value1) {
				$countsArray[$value][$value1]['responses'] = 0;
				$countsArray[$value][$value1]['Very Much'] = 0;
				$countsArray[$value][$value1]['Mostly'] = 0;
				$countsArray[$value][$value1]['Somewhat'] = 0;
				$countsArray[$value][$value1]['Not Really'] = 0;
				$countsArray[$value][$value1]['Not Applicable'] = 0;
				$studentRespondingArray[$value][$value1]['Very Much'] = '';
				$studentRespondingArray[$value][$value1]['Mostly'] = '';
				$studentRespondingArray[$value][$value1]['Somewhat'] = '';
				$studentRespondingArray[$value][$value1]['Not Really'] = '';
				$studentRespondingArray[$value][$value1]['Not Applicable'] = '';
			}
		}
		if ($doDebug) {
			echo "countsArray:<br /><pre>";
			print_r($countsArray);
			echo "</pre><br />";
			echo "<br />studentRespondingArray:<br /><pre>";
			print_r($studentRespondingArray);
			echo "</pre><br />";
		}

// Get the semester info
		if ($theSemester == 'SPECIFIED') { 		// semester info in newSemester
			$theSemester			= $newSemester;	
		}
		if ($doDebug) {
			echo "Using $theSemester as the semester<br />";
		}
	
		$sql						= "select * from $evaluateAdvisorTableName where advisor_semester='$theSemester' order by advisor_callsign";
		$wpw1_cwa_evaluate_advisor	= $wpdb->get_results($sql);
		if ($wpw1_cwa_evaluate_advisor === FALSE) {
			if ($doDebug) {
				echo "Reading $evaluateAdvisorTableName table failed<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
			}
		} else {
			$numARows				= $wpdb->num_rows;
			if ($doDebug) {
				$myStr				= $wpdb->last_query;
				echo "ran $myStr<br />and found $numARows rows in $evaluateAdvisorTableName table<br />";
			}
			if ($numARows > 0) {
				foreach ($wpw1_cwa_evaluate_advisor as $evaluateAdvisorRow) {
					$evaluateAdvisor_evaluate_id			=  $evaluateAdvisorRow->evaluate_id;
					$evaluateAdvisor_callsign				=  $evaluateAdvisorRow-> advisor_callsign;
					$evaluateAdvisor_semester				=  $evaluateAdvisorRow->advisor_semester;
					$evaluateAdvisor_class					=  $evaluateAdvisorRow->advisor_class;
					$evaluateAdvisor_survey_id				=  $evaluateAdvisorRow->survey_id;
					$evaluateAdvisor_anonymous				=  $evaluateAdvisorRow->anonymous;
					$evaluateAdvisor_create_date			=  $evaluateAdvisorRow->create_date;
					$evaluateAdvisor_level					=  $evaluateAdvisorRow->level;
					$evaluateAdvisor_expectations			=  $evaluateAdvisorRow->expectations;
					$evaluateAdvisor_effective				=  $evaluateAdvisorRow->effective;
					$evaluateAdvisor_curriculum				=  $evaluateAdvisorRow->curriculum;
					$evaluateAdvisor_scales					=  $evaluateAdvisorRow->scales;
					$evaluateAdvisor_morse_trainer			=  $evaluateAdvisorRow->morse_trainer;
					$evaluateAdvisor_morse_runner			=  $evaluateAdvisorRow->morse_runner;
					$evaluateAdvisor_rufzxp					=  $evaluateAdvisorRow->rufzxp;
					$evaluateAdvisor_numorse_pro			=  $evaluateAdvisorRow->numorse_pro;
					$evaluateAdvisor_lcwo					=  $evaluateAdvisorRow->lcwo;
					$evaluateAdvisor_cwt					=  $evaluateAdvisorRow->cwt;
					$evaluateAdvisor_applications			=  $evaluateAdvisorRow->applications;
					$evaluateAdvisor_qsos					=  $evaluateAdvisorRow->qsos;
					$evaluateAdvisor_short_stories			=  $evaluateAdvisorRow->short_stories;
					$evaluateAdvisor_enjoy_class			=  $evaluateAdvisorRow->enjoy_class;
					$evaluateAdvisor_student_comments		=  $evaluateAdvisorRow->student_comments;

					if ($doDebug) {
						echo "<br />Record ID: $evaluateAdvisor_evaluate_id | $evaluateAdvisor_callsign | $evaluateAdvisor_level<br />";
					}

					$totalResponses++;
					if ($evaluateAdvisor_student_comments != '') {
						$evaluateAdvisor_student_comments	= str_replace("<p>","",$evaluateAdvisor_student_comments);
						$evaluateAdvisor_student_comments	= str_replace("</p>","",$evaluateAdvisor_student_comments);
						$evaluateAdvisor_student_comments	= stripslashes($evaluateAdvisor_student_comments);
					}
					if ($evaluateAdvisor_applications != '') {
						$evaluateAdvisor_applications	= str_replace("<p>","",$evaluateAdvisor_applications);
						$evaluateAdvisor_applications	= str_replace("</p>","",$evaluateAdvisor_applications);
						$evaluateAdvisor_applications	= stripslashes($evaluateAdvisor_applications);
					}
					if ($evaluateAdvisor_student_comments != '') {
						if ($evaluateAdvisor_level == 'Beginner') {
							$beginnerComments[]		= "$evaluateAdvisor_evaluate_id|$evaluateAdvisor_anonymous|$evaluateAdvisor_callsign|$evaluateAdvisor_student_comments";
							if ($doDebug) {
								echo "Added beginner comment<br />";
							}
						} elseif ($evaluateAdvisor_level == 'Fundamental') {
							$fundamentalComments[]		= "$evaluateAdvisor_evaluate_id|$evaluateAdvisor_anonymous|$evaluateAdvisor_callsign|$evaluateAdvisor_student_comments";
							if ($doDebug) {
								echo "Added fundamental comment<br />";
							}
						} elseif ($evaluateAdvisor_level == 'Intermediate') {
							$intermediateComments[]		= "$evaluateAdvisor_evaluate_id|$evaluateAdvisor_anonymous|$evaluateAdvisor_callsign|$evaluateAdvisor_student_comments";
							if ($doDebug) {
								echo "Added intermediate comment<br />";
							}
						} elseif ($evaluateAdvisor_level == 'Advanced') {
							$advancedComments[]		= "$evaluateAdvisor_evaluate_id|$evaluateAdvisor_anonymous|$evaluateAdvisor_callsign|$evaluateAdvisor_student_comments";
								if ($doDebug) {
								echo "Added advanced comment<br />";
							}
						}
					}
					if ($evaluateAdvisor_applications != '') {
						if ($evaluateAdvisor_level == 'Beginner') {
							$beginnerApplications[]		= "$evaluateAdvisor_evaluate_id|$evaluateAdvisor_anonymous|$evaluateAdvisor_callsign|$evaluateAdvisor_applications";
							if ($doDebug) {
								echo "Added beginner applications<br />";
							}
						} elseif ($evaluateAdvisor_level == 'Fundamental') {
							$fundamentalApplications[]		= "$evaluateAdvisor_evaluate_id|$evaluateAdvisor_anonymous|$evaluateAdvisor_callsign|$evaluateAdvisor_applications";
							if ($doDebug) {
								echo "Added fundamental applications<br />";
							}
						} elseif ($evaluateAdvisor_level == 'Intermediate') {
							$intermediateApplications[]		= "$evaluateAdvisor_evaluate_id|$evaluateAdvisor_anonymous|$evaluateAdvisor_callsign|$evaluateAdvisor_applications";
							if ($doDebug) {
								echo "Added intermediate applications<br />";
							}
						} elseif ($evaluateAdvisor_level == 'Advanced') {
							$advancedApplications[]		= "$evaluateAdvisor_evaluate_id|$evaluateAdvisor_anonymous|$evaluateAdvisor_callsign|$evaluateAdvisor_applications";
								if ($doDebug) {
								echo "Added advanced applications<br />";
							}
						}
					}
					if ($evaluateAdvisor_anonymous == '') {
						$anonymousResponses++;
						if ($doDebug) {
							echo "anonymous response<br />";
						}
					} else {
						$nonAnonymousResponses++;
						if ($doDebug) {
							echo "non-anonymous response<br />";
						}
					}
				
					if ($evaluateAdvisor_level == 'Beginner') {
						$beginnerCounts++;
						if ($doDebug) {
							echo "beginner counted<br />";
						}
					} elseif ($evaluateAdvisor_level == 'Fundamental') {
						$fundamentalCounts++;
						if ($doDebug) {
							echo "fundamental counted<br />";
						}
					} elseif ($evaluateAdvisor_level == 'Intermediate') {
						$intermediateCounts++;
						if ($doDebug) {
							echo "intermediate counted<br />";
						}
					} elseif ($evaluateAdvisor_level == 'Advanced') {
						$advancedCounts++;
						if ($doDebug) {
							echo "advanced counted<br />";
						}
					}
//////////////
					foreach($arrayCategories as $theCategory) {
						if ($doDebug) {
							echo "Doing evaluateAdvisor_$theCategory: ${'evaluateAdvisor_' . $theCategory}<br />";
						}
						if (${'evaluateAdvisor_' . $theCategory} != '') {
							$myString			= ${'evaluateAdvisor_' . $theCategory};
							$countsArray[$evaluateAdvisor_level][$theCategory]['responses']++;
							$countsArray[$evaluateAdvisor_level][$theCategory][$myString]++;
							$studentRespondingArray[$evaluateAdvisor_level][$theCategory][$myString]	.= "$evaluateAdvisor_evaluate_id|";
											 
						 }
					}
				}			// end of while loop
				if ($doDebug) {
					echo "<br />All records processed. Doing calculations.<br />";
				}
			} else {		// end of number of records loop
				$content	.= "<p>No records found in the evaluate_advisor table</p>";
			}
		}
		if ($doDebug) {
			echo "$totalResponses Total Responses<br />
				  $anonymousResponses Anonymous Responses<br />
				  $nonAnonymousResponses Non-anonymous Responses <br />
		 		  $responseRate Response Rate<br />
				  $beginnerCounts Beginners<br />
				  $fundamentalCounts Fundamentals<br />
				  $intermediateCounts Intermediates<br />
				  $advancedCounts Advanceds<br />";	
			echo "<p>Counts Array:<br /><pre>";
			print_r($countsArray);
			echo "</pre><br />";

			echo "studentsResponding array:<br /><pre>";
			print_r($studentRespondingArray);
			echo "</pre><br />";
/*
			echo "<br />Beginner Comments Array:<br />";
			foreach($beginnerComments as $key=>$value) {
				echo "$key: $value<br />";
			}
			echo "<br />Fundamental Comments Array<br />";
			foreach($fundamentalComments as $key=>$value) {
				echo "$key: $value<br />";
			}
			echo "<br />intermediate Comments Array<br />";
			foreach($intermediateComments as $key=>$value) {
				echo "$key: $value<br />";
			}
			echo "<br />Advanced Comments Array<br />";
			foreach($advancedComments as $key=>$value) {
				echo "$key: $value<br />";
			}
*/
		}
		if ($totalResponses > 0) {
			$beginnerPC		= number_format(($beginnerCounts/$totalResponses*100),1);
			$fundamentalPC		= number_format(($fundamentalCounts/$totalResponses*100),1);
			$intermediatePC	= number_format(($intermediateCounts/$totalResponses*100),1);
			$advancedPC		= number_format(($advancedCounts/$totalResponses*100),1);
			$anonymousPC	= number_format(($anonymousResponses/$totalResponses*100),1);
			$nonAnonymousPC	= number_format(($nonAnonymousResponses/$totalResponses*100),1);
			$responseRate	= number_format(($totalResponses/305*100),1);
		} else {
			$beginnerPC		= 0;
			$fundamentalPC		= 0;
			$intermediatePC	= 0;
			$advancedPC		= 0;
			$anonymousPC	= 0;
			$nonAnonymousPC	= 0;
			$responseRate	= 0;
		}
		
		
		$content	.= "<h3>Display Student Evaluations of Advisors</h3>
						<table style='width:900px;'>
						<tr><td colspan='2'>Total Responses</td>
							<td style='text-align:center;'>$totalResponses</td>
							<td colspan='5' style='text-align:left;'>$responseRate% Response Rate</td></tr>
						<tr><td colspan='2'>Beginners</td>
							<td style='text-align:center;'>$beginnerCounts</td>
							<td colspan='5' style='text-align:left;'>$beginnerPC%</td></tr>
						<tr><td colspan='2'>Fundamental</td>
							<td style='text-align:center;'>$fundamentalCounts</td>
							<td colspan='5' style='text-align:left;'>$fundamentalPC%</td></tr>
						<tr><td colspan='2'>Intermediate</td>
							<td style='text-align:center;'>$intermediateCounts</td>
							<td colspan='5' style='text-align:left;'>$intermediatePC%</td></tr>
						<tr><td colspan='2'>Advanced</td>
							<td style='text-align:center;'>$advancedCounts</td>
							<td colspan='5' style='text-align:left;'>$advancedPC%</td></tr>
						<tr><td colspan='2'>Anonymous</td>
							<td style='text-align:center;'>$anonymousResponses</td>
							<td colspan='5' style='text-align:left;'>$anonymousPC%</td></tr>
						<tr><td colspan='2'>Non-anonymous</td>
							<td style='text-align:center;'>$nonAnonymousResponses</td>
							<td colspan='5' style='text-align:left;'>$nonAnonymousPC%</td></tr>
						<tr><td colspan='8'><hr></td></tr>
						<tr><td colspan='8'><b>Category Ratings</b></td></tr>";

		$reportArray	= array(
'effective'=>'Capable & Effective',
'expectations'=>'Met Expectations',
'curriculum'=>'Curriculum',
'enjoy_class'=>'Enjoy Class',
'scales'=>'Scales',
'morse_trainer'=>'Morse Trainer',
'rufzxp'=>'RufzXP',
'morse_runner'=>'Morse Runner',
'lcwo'=>'LCWO',
'short_stories'=>'Short Stories',
'qsos'=>'QSOs',
'cwt'=>'CWT'
);

		$sequenceArray	= array(
'Beginner'=>'Beginner',
'Fundamental'=>'Fundamental',
'Intermediate'=>'Intermediate',
'Advanced'=>'Advanced'
);

		$countNames		= array(
'Very Much',
'Mostly',
'Somewhat',
'Not Really',
'Not Applicable'
);		
		foreach($reportArray as $category=>$heading) {
			if ($doDebug) {
				echo "Doing counts for $category ($heading)<br />";
			}
			$segmentTotals				= array(
'Very Much'=>0,
'Mostly'=>0,
'Somewhat'=>0,
'Not Really'=>0,
'Not Applicable'=>0			
);
			$segmentTotalTotal			= 0;
			$content					.= "<tr><td colspan='2' style='width:150px;'><b>$heading</b></td>
												<td style='text-align:center;'><b>Responses</b></td>
												<td style='text-align:center;'><b>Very Much</b></td>
												<td style='text-align:center;'><b>Mostly</b></td>
												<td style='text-align:center;'><b>Somewhat</b></td>
												<td style='text-align:center;'><b>Not Really</b></td>
												<td style='text-align:center;'><b>N/A</b></td>
											</tr>";
		
			foreach($sequenceArray as $level=>$sequenceHeading) {
				if ($doDebug) {
					echo "&nbsp;&nbsp;&nbsp;Doing counts for $level ($sequenceHeading)<br />";
				}
				$content	.= "<tr><td style='width:40px;'>$sequenceHeading</td>
									<td style='text-align:left;width:100px;'></td>
									<td style='text-align:center;width:100px;'>";
				if (isset($countsArray[$level][$category]['responses'])) {
					$content			.= $countsArray[$level][$category]['responses'];
					$theDenominator		= $countsArray[$level][$category]['responses'];
					$segmentResponses	= $theDenominator;
					$segmentTotalTotal	= $segmentTotalTotal + $theDenominator;
				} else {
					$content			.= "&nbsp;";
					$theDenominator 	= 0;
					$segmentResponses	= 0;
				}
		    	$content	.= "</td>";
				foreach($countNames as $theName) {
					if ($doDebug) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Doing counts for $theName<br />";
					}
					$content		.= "<td style='text-align:center;width:100px;'>";
					if (isset($countsArray[$level][$category][$theName])) {
						$theNumerator		= $countsArray[$level][$category][$theName];
						$segmentTotals[$theName]	= $segmentTotals[$theName] + $theNumerator;
						if ($doDebug) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$theName Count: $segmentTotals[$theName]<br />";
						}
						if ($theNumerator > 0) {
							if ($theDenominator != 0) {
								$thePC = number_format(($theNumerator/$theDenominator*100),1);
								$respondingList	= $studentRespondingArray[$level][$category][$theName];
								$content		.= "<a href='$theURL?evaluateid=$respondingList&strpass=3' target='_blank'>$thePC%</a>";
							} else {
								$content		.= $countsArray[$level][$category][$theName];
							}
						} else {
								$content		.= "&nbsp;";
						}
					} else {	
						$content			.= "&nbsp;";
						$segmentTotals[$theName]	= 0;
					}
		    		$content				.= "</td>";
		    	}
		    }
		    if ($doDebug) {
		    	echo "Finished with $category/$level. Time for totals<br /><pre>";
		    	print_r($segmentTotals);
		    	echo "</pre><br />";
		    	echo "TotalTotal: $segmentTotalTotal<br />";
		    }
		    $content		.= "<tr><td style='width:40px;'>&nbsp;</td>
									<td style='text-align:left;'>Total</td>
									<td style='text-align:center;'>$segmentTotalTotal</td>";
			foreach($segmentTotals as $segmentName=>$segmentCount) {
				if ($segmentResponses != 0) {
					$thisCount			= $segmentCount;
					if ($thisCount != 0) {
						$thePC			= number_format(($thisCount/$segmentTotalTotal*100),1);
						$content		.= "<td style='text-align:center;'>$thePC%</td>";
					} else {
						$content		.= "<td>&nbsp;</td>";
					}
				} else {
					$content		.= "<td style='text-align:center;'>&nbsp;</td>";
				}
			}
			$content				.= "</td></tr><tr><td colspan='8'><hr></td></tr>";
		}

	$content				.= "</table>";

// list the comments
	$content				.= "<h4>Beginner Comments</h4>";
	foreach ($beginnerComments as $theComment) {
		$commentArray		= explode("|",$theComment);
		$content			.= "<p>From $commentArray[1] about $commentArray[2]:&nbsp;$commentArray[3]&nbsp;
(<a href='$theURL?evaluateid=$commentArray[0]&strpass=3' target='_blank'>link</a>)</p>";
	}
	$content				.= "<h4>Fundamental Comments</h4>";
	foreach ($fundamentalComments as $theComment) {
		$commentArray		= explode("|",$theComment);
		$content			.= "<p>From $commentArray[1] about $commentArray[2]:&nbsp;$commentArray[3]&nbsp; 
(<a href='$theURL?evaluateid=$commentArray[0]&strpass=3' target='_blank'>link</a>)</p>";
	}
	$content				.= "<h4>Intermediate Comments</h4>";
	foreach ($intermediateComments as $theComment) {
		$commentArray		= explode("|",$theComment);
		$content			.= "<p>From $commentArray[1] about $commentArray[2]:&nbsp;$commentArray[3]&nbsp; 
(<a href='$theURL?evaluateid=$commentArray[0]&strpass=3' target='_blank'>link</a>)</p>";
	}
	$content				.= "<h4>Advanced Comments</h4>";
	foreach ($advancedComments as $theComment) {
		$commentArray		= explode("|",$theComment);
		$content			.= "<p>From $commentArray[1] about $commentArray[2]:&nbsp;$commentArray[3]&nbsp; 
(<a href='$theURL?evaluateid=$commentArray[0]&strpass=3' target='_blank'>link</a>)</p>";
	}

// list the applications
	$content				.= "<h4>Beginner Applications</h4>";
	foreach ($beginnerApplications as $theApplications) {
		$applicationsArray		= explode("|",$theApplications);
		$content			.= "<p>From $applicationsArray[1] about $applicationsArray[2]:&nbsp;$applicationsArray[3]&nbsp;
(<a href='$theURL?evaluateid=$applicationsArray[0]&strpass=3' target='_blank'>link</a>)</p>";
	}
	$content				.= "<h4>Fundamental Applications</h4>";
	foreach ($fundamentalApplications as $theApplications) {
		$applicationsArray		= explode("|",$theApplications);
		$content			.= "<p>From $applicationsArray[1] about $applicationsArray[2]:&nbsp;$applicationsArray[3]&nbsp; 
(<a href='$theURL?evaluateid=$applicationsArray[0]&strpass=3' target='_blank'>link</a>)</p>";
	}
	$content				.= "<h4>Intermediate Applications</h4>";
	foreach ($intermediateApplications as $theApplications) {
		$applicationsArray		= explode("|",$theApplications);
		$content			.= "<p>From $applicationsArray[1] about $applicationsArray[2]:&nbsp;$applicationsArray[3]&nbsp; 
(<a href='$theURL?evaluateid=$applicationsArray[0]&strpass=3' target='_blank'>link</a>)</p>";
	}
	$content				.= "<h4>Advanced Applications</h4>";
	foreach ($advancedApplications as $theApplications) {
		$applicationsArray		= explode("|",$theApplications);
		$content			.= "<p>From $applicationsArray[1] about $applicationsArray[2]:&nbsp;$applicationsArray[3]&nbsp; 
(<a href='$theURL?evaluateid=$applicationsArray[0]&strpass=3' target='_blank'>link</a>)</p>";
	}


	
	} elseif ("3" == $strPass) {
// get the record based on the evaluateid
		if ($evaluateid == '') {
			$content							.= "Incorrect input parameter";
		} else {
			$idArray							=explode("|",$evaluateid);
			$content							.= "<h4>Details of Responding Students</h4>";
			foreach($idArray as $theID) {
				if ($theID != '') {
					$sql 						= "select * from $evaluateAdvisorTableName where evaluate_id=$theID order by advisor_callsign";
					$wpw1_cwa_evaluate_advisor	= $wpdb->get_results($sql);
					if ($wpw1_cwa_evaluate_advisor === FALSE) {
						if ($doDebug) {
							echo "Reading $evaluateAdvisorTableName table failed<br />";
							echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
							echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
						}
					} else {
				 		$numARows				= $wpdb->num_rows;
						if ($doDebug) {
							$myStr				= $wpdb->last_query;
							echo "found $numARows rows in $evaluateAdvisorTableName table<br />";
						}
						if ($numARows > 0) {
							foreach ($wpw1_cwa_evaluate_advisor as $evaluateAdvisorRow) {
								$evaluateAdvisor_evaluate_id			=  $evaluateAdvisorRow->evaluate_id;
								$evaluateAdvisor_callsign				=  $evaluateAdvisorRow-> advisor_callsign;
								$evaluateAdvisor_semester				=  $evaluateAdvisorRow->advisor_semester;
								$evaluateAdvisor_class					=  $evaluateAdvisorRow->advisor_class;
								$evaluateAdvisor_survey_id				=  $evaluateAdvisorRow->survey_id;
								$evaluateAdvisor_anonymous				=  $evaluateAdvisorRow->anonymous;
								$evaluateAdvisor_create_date			=  $evaluateAdvisorRow->create_date;
								$evaluateAdvisor_level					=  $evaluateAdvisorRow->level;
								$evaluateAdvisor_expectations			=  $evaluateAdvisorRow->expectations;
								$evaluateAdvisor_effective				=  $evaluateAdvisorRow->effective;
								$evaluateAdvisor_curriculum				=  $evaluateAdvisorRow->curriculum;
								$evaluateAdvisor_scales					=  $evaluateAdvisorRow->scales;
								$evaluateAdvisor_morse_trainer			=  $evaluateAdvisorRow->morse_trainer;
								$evaluateAdvisor_morse_runner			=  $evaluateAdvisorRow->morse_runner;
								$evaluateAdvisor_rufzxp					=  $evaluateAdvisorRow->rufzxp;
								$evaluateAdvisor_numorse_pro			=  $evaluateAdvisorRow->numorse_pro;
								$evaluateAdvisor_lcwo					=  $evaluateAdvisorRow->lcwo;
								$evaluateAdvisor_cwt					=  $evaluateAdvisorRow->cwt;
								$evaluateAdvisor_applications			=  $evaluateAdvisorRow->applications;
								$evaluateAdvisor_qsos					=  $evaluateAdvisorRow->qsos;
								$evaluateAdvisor_short_stories			=  $evaluateAdvisorRow->short_stories;
								$evaluateAdvisor_enjoy_class			=  $evaluateAdvisorRow->enjoy_class;
								$evaluateAdvisor_student_comments		=  $evaluateAdvisorRow->student_comments;

								if ($evaluateAdvisor_student_comments != '') {
									$evaluateAdvisor_student_comments	= str_replace("<p>","",$evaluateAdvisor_student_comments);
									$evaluateAdvisor_student_comments	= str_replace("</p>","",$evaluateAdvisor_student_comments);
									$evaluateAdvisor_student_comments	= stripslashes($evaluateAdvisor_student_comments);
								}
								if ($evaluateAdvisor_applications != '') {
									$evaluateAdvisor_applications	= str_replace("<p>","",$evaluateAdvisor_applications);
									$evaluateAdvisor_applications	= str_replace("</p>","",$evaluateAdvisor_applications);
									$evaluateAdvisor_applications	= stripslashes($evaluateAdvisor_applications);
								}
						
								//// get the advisor name
								$mylink 			= $wpdb->get_results( "SELECT first_name, 
																				  last_name 
																			FROM $advisorTableName 
																			where call_sign='$evaluateAdvisor_callsign' 
																				and semester='$evaluateAdvisor_semester'");
								if ($mylink === FALSE) {
									if ($doDebug) {
										echo "Reading $evaluateAdvisorTableName table failed<br />";
										echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
											echo "<b>wpdb->last_error: " . $wpdb->last_error . "</b><br />";
																
										echo "mylink:<br /><pre>";
										print_r($mylink);
										echo "</pre><br /><br />";
									}
								} else {
									foreach($mylink as $advisorRow) {

										$advisor_first_name	= $advisorRow->first_name;
										$advisor_last_name 	= stripslashes($advisorRow->last_name);
									}
									$content	.= "<table style='width:900px;'>
													<tr><th style='width:200px;'>Field</th><th>Value</th></tr>
													<tr><td>Advisor Call Sign:</td><td>$evaluateAdvisor_callsign</td></tr>
													<tr><td>Advisor Name:</td><td>$advisor_last_name, $advisor_first_name</td></tr>
													<tr><td>Student ID:</td><td>$evaluateAdvisor_survey_id</td></tr>
													<tr><td>Anonymous:</td><td>$evaluateAdvisor_anonymous</td></tr>
													<tr><td>Create Date:</td><td>$evaluateAdvisor_create_date</td></tr>
													<tr><td>Level:</td><td>$evaluateAdvisor_level</td></tr>
													<tr><td>Semester:</td><td>$evaluateAdvisor_semester</td></tr>
													<tr><td>Expectations:</td><td>$evaluateAdvisor_expectations</td></tr>
													<tr><td>Effective:</td><td>$evaluateAdvisor_effective</td></tr>
													<tr><td>Curriculum:</td><td>$evaluateAdvisor_curriculum</td></tr>
													<tr><td>Scales:</td><td>$evaluateAdvisor_scales</td></tr>
													<tr><td>Morse Trainer:</td><td>$evaluateAdvisor_morse_trainer</td></tr>
													<tr><td>Morse Runner:</td><td>$evaluateAdvisor_morse_trainer</td></tr>
													<tr><td>LCWO:</td><td>$evaluateAdvisor_lcwo</td></tr>
													<tr><td>RufzXP:</td><td>$evaluateAdvisor_rufzxp</td></tr>
													<tr><td>CWT:</td><td>$evaluateAdvisor_cwt</td></tr>
													<tr><td>QSOs</td><td>$evaluateAdvisor_qsos</td></tr>
													<tr><td>Short Stories</td><td>$evaluateAdvisor_short_stories</td></tr>
													<tr><td>Enjoy Class</td><td>$evaluateAdvisor_enjoy_class</td></tr>
													<tr><td style='vertical-align:top;'>Student Comments:</td><td>$evaluateAdvisor_student_comments</td></tr>
													<tr><td style='vertical-align:top;'>Student Applicationss:</td><td>$evaluateAdvisor_applications</td></tr>
													</table>";
	
								}
							}
						} else {
							$content	.= "Record not found for some reason";
						}
					}
				}
			}
			$content			.= "<br />To go back to resubmit the report click <a href='$theURL'>HERE</a>";
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
	$thisStr			= 'Production';
	if ($testMode) {
		$thisStr		= 'Testmode';
	}
	$result			= write_joblog_func("CWA - Display Student Evaluation of Advisor|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('display_student_evaluation_of_advisors', 'display_student_evaluation_of_advisors_func');
