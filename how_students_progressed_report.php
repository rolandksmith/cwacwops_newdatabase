function how_students_progressed_report_func() {

/*
	I don't think this is being used

	Modified 13Jul23 by Roland to use consolidated tables
*/

	global $wpdb, $testMode, $doDebug;

	$doDebug						= TRUE;
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
	if ($validUser == "N") {
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
	$theURL						= "$siteURL/cwa-how-students-progressed-report/";
	$jobname					= "How Students Progresses Report";
	$studentArray				= array();

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

	function splitSemester($theSemester) {
	
		$convertArray			= array('jan/feb'=>1,
										'apr/may'=>2,
										'may/jun'=>3,
										'sep/oct'=>4);
	
		if ($theSemester != '') {
			$theSemester		= strtolower($theSemester);
			$myArray = explode(" ",$theSemester);
			if (count($myArray) > 1) {
				$thisMonths		= $myArray[1];
				$thisYear		= $myArray[0];
				if (array_key_exists($thisMonths,$convertArray)) {
					$thisCount	= $convertArray[$thisMonths];
					return "$thisYear-$thisCount";
				}
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	function fixSemester($theSemester) {
		$convertArray			= array(1=>'Jan/Feb',
										2=>'Apr/May',
										3=>'May/Jun',
										4=>'Sep/Oct');
		if ($theSemester != '') {
			$myArray			= explode("-",$theSemester);
			$thisYear			= $myArray[0];
			$thisCount			= $myArray[1];
			if (array_key_exists($thisCount,$convertArray)) {
				$thisMonths		= $convertArray[$thisCount];
				return "$thisYear $thisMonths";
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	if ($testMode) {
		$content	.= "<p><strong>Operating in Test Mode.</strong></p>";
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$extMode					= 'tm';
		$studentTableName			= "wpw1_cwa_consolidated_student2";
	} else {
		$extMode					= 'pd';
		$studentTableName			= "wpw1_cwa_consolidated_student";
	}



	if ("1" == $strPass) {
		if ($doDebug) {
			echo "<br />Arrived at pass $strPass<br/>";
		}
		$content 		.= "<h3>$jobname</h3>
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
	
		if ($doDebug) {
			echo "<br />Arrived at pass $strPass<br/>";
		}
		// get the students and populate the array
		$sql			= "SELECT call_sign, 
								  first_name, 
								  last_name, 
								  level, 
								  semester, 
								  response, 
								  student_status, 
								  promotable 
							FROM $studentTableName 
							where response = 'Y' 
							and (student_status = 'Y' or student_status = 'S' or student_status = '')  
							order by call_sign, 
									 request_date ASC";
		$wpw1_cwa_consolidated_student	= $wpdb->get_results($sql);
		if ($wpw1_cwa_consolidated_student === FALSE) {
			$myError			= $wpdb->last_error;
			$myQuery			= $wpdb->last_query;
			if ($doDebug) {
				echo "Reading $studentTableName table failed<br />
					  wpdb->last_query: $myQuery<br />
					  wpdb->last_error: $myError<br />";
			}
			$errorMsg			= "$jobname Reading $studentTableName failed. <p>SQL: $myQuery</p><p> Error: $myError</p>";
			sendErrorEmail($errorMsg);
		} else {
			$numPSRows									= $wpdb->num_rows;
			if ($doDebug) {
				$myStr			= $wpdb->last_query;
				echo "ran $myStr<br />and found $numPSRows rows in $studentTableName table<br />";
			}
			if ($numPSRows > 0) {
				foreach ($wpw1_cwa_consolidated_student as $studentRow) {
					$student_call_sign						= strtoupper($studentRow->call_sign);
					$student_first_name					= $studentRow->first_name;
					$student_last_name						= stripslashes($studentRow->last_name);
					$student_level  						= $studentRow->level;
					$student_semester						= $studentRow->semester;
					$student_response  					= strtoupper($studentRow->response);
					$student_student_status  				= strtoupper($studentRow->student_status);
					$student_promotable  					= $studentRow->promotable;

					$student_last_name 					= no_magic_quotes($student_last_name);

					$thisSemester								= splitSemester($student_semester);
					if ($thisSemester !== FALSE) {
						$studentArray[$student_call_sign][$thisSemester]	= "$student_last_name, $student_first_name|$student_level|$student_response|$student_student_status|$student_promotable";
					} else {
						if ($doDebug) {
							echo "$student_call_sign splitSemester for $student_semester returned FALSE<br />";
						}
					}
				}
				

				if ($doDebug) {
					echo "<b>Student Array:</b><br /><pre>";
					print_r($studentArray);
					echo "</pre><br />";
				}

				$content					.= "<h3>How Students Progressed Report</h3>
												<table>
												<tr><th style='width:200px;'>Student</th>
													<th>Semester</th>
													<th>Level</th>
													<th>Promotable</th></tr>";
				
				$tookBeg					= 0;
				$tookBegProm				= 0;
				$tookBegNProm				= 0;
				$tookBegWithd				= 0;
				$tookBegNoEval				= 0;
				$tookFun					= 0;
				$tookFunProm				= 0;
				$tookFunNProm				= 0;
				$tookFunWithd				= 0;
				$tookFunNoEval				= 0;
				$tookInt					= 0;
				$tookIntProm				= 0;
				$tookIntNProm				= 0;
				$tookIntWithd				= 0;
				$tookIntNoEval				= 0;
				$tookAdv					= 0;
				$tookAdvProm				= 0;
				$tookAdvNProm				= 0;
				$tookAdvWithd				= 0;
				$tookAdvNoEval				= 0;
				$allTheWay					= 0;
				$rptBegProm					= 0;
				$rptBegNProm				= 0;
				$rptFunProm					= 0;
				$rptFunNProm				= 0;
				$rptIntProm					= 0;
				$rptInpNProm				= 0;
				$rptAdvProm					= 0;
				$rptAdvNProm				= 0;
				$allTheWay					= 0;
				$begStop					= 0;
				$funStop					= 0;
				$intStop					= 0;
				$begStopNProm				= 0;
				$funStopNProm				= 0;
				$intStopNProm				= 0;
				
				$doOnce						= TRUE;
				$prevLevel					= '';
				
				foreach($studentArray as $thisCallsign => $thisValue) {
					$hasBeg					= FALSE;
					$hasFun					= FALSE;
					$hasInt					= FALSE;
					$hasAdv					= FALSE;
					$nPromBeg				= FALSE;
					$promBeg				= FALSE;
					$nEvalBeg				= FALSE;
					$nPromFun				= FALSE;
					$promFun				= FALSE;
					$nEvalFun				= FALSE;
					$nPromInt				= FALSE;
					$promInt				= FALSE;
					$nEvalInt				= FALSE;
					$nPromAdv				= FALSE;
					$promAdv				= FALSE;
					$nEvalAdv				= FALSE;
					$prevLevel				= '';
					$doOnce					= TRUE;
					if ($doDebug) {
						echo "<br />Process $thisCallsign<br />";
					}
					foreach($thisValue as $thisSemester=>$thisData) {
						$myArray			= explode("|",$thisData);
						$thisName			= $myArray[0];
						$thisLevel			= $myArray[1];
						$thisResponse		= $myArray[2];
						$thisStatus			= $myArray[3];
						$thisProm			= $myArray[4];
						
						if ($doDebug) {
							echo "Processing $thisSemester<br />";
						}
						$content			.= "<tr><td>";
						if ($doOnce) {
							$content		.= "$thisName ($thisCallsign)</td>";
							$doOnce			= FALSE;
						} else {
							$content		.= "&nbsp;</td>";
						}
						$convSemester		= fixSemester($thisSemester);
						$content			.= "<td>$convSemester</td>
												<td>$thisLevel</td>
												<td>$thisProm</td></tr>";
												
						/// figure out the counts
						if ($thisLevel == "Beginner") {
							if ($hasBeg) {
								if ($promBeg) {
									$rptBegProm++;
									if ($doDebug) {
										echo "rptBegProm++<br />";
									}
								} else {
									$rptBegNProm++;
									if ($doDebug) {
										echo "rptBegNprom++<br />";
									}
								}
							}
							$hasBeg				= TRUE;
							if ($doDebug) {
								echo "hasBeg now TRUE<br />";
							}
							$tookBeg++;
								if ($doDebug) {
									echo "tookBeg++<br />";
								}
							if ($thisProm == 'P') {
								$tookBegProm++;
								$promBeg		= TRUE;
								if ($doDebug) {
									echo "tookBegProm++<br />";
								}
							} elseif ($thisProm == 'N') {
								$tookBegNProm++;
								$nPromBeg		= TRUE;
								if ($doDebug) {
									echo "tookBegNProm++<br />";
								}
							} elseif ($thisProm == 'W') {
								$tookBegNProm++;
								$nPromBeg		= TRUE;
								if ($doDebug) {
									echo "tookBegNProm++<br />";
								}
							} else {
								$tookBegNoEval++;
								$nEvalBeg		= TRUE;
								if ($doDebug) {
									echo "tookBegNoEval++<br />";
								}
							}
							if (!$promBeg && !$nPromBeg && !$nEvalBeg) {
								echo "<b>ERROR</b> $thisCallsign not counted correctly<br />";
							}
						}
						if ($thisLevel == "Fundamental") {
							if ($hasFun) {
								if ($promFun) {
									$rptFunProm++;
									if ($doDebug) {
										echo "rptFunProm++<br />";
									}
								} else {
									$rptFunNProm++;
									if ($doDebug) {
										echo "rptFunNprom++<br />";
									}
								}
							}
							$hasFun				= TRUE;
							if ($doDebug) {
								echo "hasFun now TRUE<br />";
							}
							$tookFun++;
								if ($doDebug) {
									echo "tookFun++<br />";
								}
							if ($thisProm == 'P') {
								$tookFunProm++;
								$promFun		= TRUE;
								if ($doDebug) {
									echo "tookFunProm++<br />";
								}
							} elseif ($thisProm == 'N') {
								$tookFunNProm++;
								$nPromFun		= TRUE;
								if ($doDebug) {
									echo "tookFunNProm++<br />";
								}
							} elseif ($thisProm == 'W') {
								$tookFunNProm++;
								$nPromFun		= TRUE;
								if ($doDebug) {
									echo "tookFunNProm++<br />";
								}
							} else {
								$tookFunNoEval++;
								$nEvalFun		= TRUE;
								if ($doDebug) {
									echo "tookFunNoEval++<br />";
								}
							}
							if (!$promFun && !$nPromFun && !$nEvalFun) {
								echo "<b>ERROR</b> $thisCallsign not counted correctly<br />";
							}
						}
						if ($thisLevel == "Intermediate") {
							if ($hasInt) {
								if ($promInt) {
									$rptIntProm++;
									if ($doDebug) {
										echo "rptIntProm++<br />";
									}
								} else {
									$rptIntNProm++;
									if ($doDebug) {
										echo "rptIntNprom++<br />";
									}
								}
							}
							$hasInt				= TRUE;
							if ($doDebug) {
								echo "hasInt now TRUE<br />";
							}
							$tookInt++;
								if ($doDebug) {
									echo "tookInt++<br />";
								}
							if ($thisProm == 'P') {
								$tookIntProm++;
								$promInt		= TRUE;
								if ($doDebug) {
									echo "tookIntProm++<br />";
								}
							} elseif ($thisProm == 'N') {
								$tookIntNProm++;
								$nPromInt		= TRUE;
								if ($doDebug) {
									echo "tookIntNProm++<br />";
								}
							} elseif ($thisProm == 'W') {
								$tookIntNProm++;
								$nPromInt		= TRUE;
								if ($doDebug) {
									echo "tookIntNProm++<br />";
								}
							} else {
								$tookIntNoEval++;
								$nEvalInt		= TRUE;
								if ($doDebug) {
									echo "tookIntNoEval++<br />";
								}
							}
							if (!$promInt && !$nPromInt && !$nEvalInt) {
								echo "<b>ERROR</b> $thisCallsign not counted correctly<br />";
							}
						}
						if ($thisLevel == "Advanced") {
							if ($hasAdv) {
								if ($promAdv) {
									$rptAdvProm++;
									if ($doDebug) {
										echo "rptAdvProm++<br />";
									}
								} else {
									$rptAdvNProm++;
									if ($doDebug) {
										echo "rptAdvNprom++<br />";
									}
								}
							}
							$hasAdv				= TRUE;
							if ($doDebug) {
								echo "hasAdv now TRUE<br />";
							}
							$tookAdv++;
								if ($doDebug) {
									echo "tookAdv++<br />";
								}
							if ($thisProm == 'P') {
								$tookAdvProm++;
								$promAdv		= TRUE;
								if ($doDebug) {
									echo "tookAdvProm++<br />";
								}
							} elseif ($thisProm == 'N') {
								$tookAdvNProm++;
								$nPromAdv		= TRUE;
								if ($doDebug) {
									echo "tookAdvNProm++<br />";
								}
							} elseif ($thisProm == 'W') {
								$tookAdvNProm++;
								$nPromAdv		= TRUE;
								if ($doDebug) {
									echo "tookAdvNProm++<br />";
								}
							} else {
								$tookAdvNoEval++;
								$nEvalAdv			= TRUE;
								if ($doDebug) {
									echo "tookAdvNoEval++<br />";
								}
							}
							if (!$promAdv && !$nPromAdv && !$nEvalAdv) {
								echo "<b>ERROR</b> $thisCallsign not counted correctly<br />";
							} else {
								if ($doDebug) {
									echo "count OK<br />";
								}
							}
						}
						$prevLevel				= $thisLevel;
						
						
					
					
					}
					$content					.= "<tr><td colspan='4'><hr></td></tr>";
					if ($hasBeg && $hasFun && $hasInt && $hasAdv) {
						$allTheWay++;
						if ($doDebug) {
							echo "allTheWay++<br />";
						}
					}
					if ($hasBeg && !$hasFun && !$hasInt && !$hasAdv) {
						if ($promBeg) {
							$begStop++;
							if ($doDebug) {
								echo "begStop++<br />";
							}
						}
						if ($nPromBeg) {
							$begStopNProm++;
							if ($doDebug) {
								echo "begStopNProm++<br />";
							}
						}
					}
					if ($hasFun && !$hasInt && !$hasAdv) {
						if ($promFun) {
							$funStop++;
							if ($doDebug) {
								echo "funStop++<br />";
							}
						}
						if ($nPromFun) {
							$funStopNProm++;
							if ($doDebug) {
								echo "funStopNProm++<br />";
							}
						}
					}
					if ($hasInt && !$hasAdv) {
						if ($promInt) {
							$intStop++;
							if ($doDebug) {
								echo "intStop++<br />";
							}
						}
						if ($nPromInt) {
							$intStopNProm++;
							if ($doDebug) {
								echo "intStopNProm++<br />";
							}
						}
					}
				}
				$content		.= "</table>";	
			}
		}
		$bigTotal				= $tookBeg+$tookFun+$tookInt+$tookAdv;
		$content				.= "<h4>Totals</h4><table>
									<tr><th>Level</th>
										<th>Took<br />Class</th>
										<th>Promotable</th>
										<th>Not<br />Promotable</th>
										<th>Not Eval</th>
										<th>Repeat<br />Prommotable</th>
										<th>Repeat<br />Non-Prom</th>
										<th>Promoted<br />Stopped</td>
										<th>Non-Prom<br />Stopped</th></tr>
									<tr><td>Beginner</td>
										<td>$tookBeg</td>
										<td>$tookBegProm</td>
										<td>$tookBegNProm</td>
										<td>$tookBegNoEval</td>
										<td>$rptBegProm</td>
										<td>$rptBegNProm</td>
										<td>$begStop</td>
										<td>$begStopNProm</td></tr>
									<tr><td>Fundamental</td>
										<td>$tookFun</td>
										<td>$tookFunProm</td>
										<td>$tookFunNProm</td>
										<td>$tookFunNoEval</td>
										<td>$rptFunProm</td>
										<td>$rptFunNProm</td>
										<td>$funStop</td>
										<td>$funStopNProm</td></tr>
									<tr><td>Intermediate</td>
										<td>$tookInt</td>
										<td>$tookIntProm</td>
										<td>$tookIntNProm</td>
										<td>$tookIntNoEval</td>
										<td>$rptIntProm</td>
										<td>$rptIntNProm</td>
										<td>$intStop</td>
										<td>$intStopNProm</td></tr>
									<tr><td>Advanced</td>
										<td>$tookAdv</td>
										<td>$tookAdvProm</td>
										<td>$tookAdvNProm</td>
										<td>$tookAdvNoEval</td>
										<td>$rptAdvProm</td>
										<td>$rptAdvNProm</td>
										<td>--</td>
										<td>--</td></tr>
									<tr><td>Total</td>
										<td>$bigTotal</td>
										<td colspan='9'>&nbsp;</td></tr>
									<tr><td>Took all levels</td>
										<td colspan='8'>$allTheWay</td></tr></table>
									<p><b>Explanations:</b><br />
									<dl><dt>Took Class<dt>
										<dd>Registered for class, response = Y and student status = Y. Does not 
											count students who didn't respond, who were removed, or who were replaced</dd>
										<dt>Promotable</dt>
										<dd>Students who took the class and were marked as Promotable</dd>
										<dt>Not Promotable</dt>
										<dd>Students who took the class and were marked as not promotable or as withdrawn</dd>
										<dt>Not Eval</dt>
										<dd>Students in past semesters who weren't evaluated, or students in the 
											current or next semester who have not yet been evaluated</dd>
										<dt>Repeat Promotable</dt>
										<dd>Students who had taken the class level previously, were promotable, but took the 
											class again</dd>
										<dt>Repeat Non-Prom</dt>
										<dd>Students who had taken the class previously, were marked as not promotable, 
											and took the class again</dd>
										<dt>Promoted Stopped</dt>
										<dd>Students who took the class level, were marked as promotable, but didn't take 
											another class at any level</dd>
										<dt>Non-Prom Stopped</dt>
										<dd>Students who took the class level, were marked as not promotable, and didn't 
											take another class at any level</dd>
										<dt>Took All Levels</dt>
										<dd>Count of students who had taken Beginner, Fundamental, Intermediate, and Advanced</dd>";
	
	
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
	$result			= write_joblog_func("$jobname|$nowDate|$nowTime|$userName|Time|$thisStr|$strPass: $elapsedTime|$ipAddr");
	if ($result == 'FAIL') {
		$content	.= "<p>writing to joblog.txt failed</p>";
	}
	return $content;
}
add_shortcode ('how_students_progressed_report', 'how_students_progressed_report_func');
