function displayAssessment($inp_callsign='',$inp_token='',$doDebug=FALSE) {

/*	Obtains the new assessment information, prepares the display, and returns that
	to the calling program to be displayed
	
	Input:		callsign 
				and / or				// either callsign or token or both, but at least one
				token 
			
	Returns an array:
				TRUE / FALSE			
				A formated display
				or
				Reason for FALSE
				A string containing				// separated by &
					bestResultBeginner=0
					didBeginner=TRUE/FALSE
					bestResultFundamental=0
					didFundamental=TRUE/FALSE
					bestResultIntermediate=0
					didIntermediate=TRUE/FALSE
					bestResultAdvanced=0
					didAdvanced=TRUE/FALSE

	Sample code:
		$bestResultBeginner		= 0;
		$didBeginner			= FALSE;
		$bestResultFundamental	= 0;
		$didFundamental			= FALSE;
		$bestResultIntermediate	= 0;
		$didIntermediate		= FALSE;
		$bestResultAdvanced		= 0;
		$didAdvanced			= FALSE;
		$retVal			= displayAssessment($inp_callsign,$inp_token,$doDebug);
		if ($retVal[0] === FALSE) {
			if ($doDebug) {
				echo "displayAssessment returned FALSE. Called with $inp_callsign, $inp_token<br />";
			}
			$content	.= "No data to display.<br />Reason: $retVal[1]";
		} else {
			$content	.= $retVal[1];
			$myArray	= explode("&",$retVal[2]);
			foreach($myArray as $thisValue) {
				$myArray1	= explode("=",$thisValue);
				$thisKey	= $myArray1[0];
				$thisData	= $myArray1[1];
				$$thisKey	= $thisData;
				if ($doDebug) {
					echo "$thisKey = $thisValue<br />";
				}
			}
			$content		.= "<p>You have completed the Morse Code Proficiency 
								assessment.<br />";
			if ($didBeginner) {
				$content	.= "Your Beginner Level assessment score was $bestResultBeginner<br />";
			}
			if ($didFundamental) {
				$content	.= "Your Fundamental Level assessment score was $bestResultFundamental<br />";
			}
			if ($didIntermediate) {
				$content	.= "Your Intermediate Level assessment score was $bestResultIntermediate<br />";
			}
			if ($didAdvanced) {
				$content	.= "Your Advanced Level assessment score was $bestResultAdvanced<br />";
			}
			


*/

	global $wpdb;

	$bestResultBeginner		= 0;
	$didBeginner			= FALSE;
	$bestResultFundamental	= 0;
	$didFundamental			= FALSE;
	$bestResultIntermediate	= 0;
	$didIntermediate		= FALSE;
	$bestResultAdvanced		= 0;
	$didAdvanced			= FALSE;
	$retVal					= array(FALSE,'unknown error','');

	
	if ($inp_callsign == '' && $inp_token == '') {
		$retVar					= array(FALSE,'callsign and token missing','');
	} else {
		$sql					= "select * from wpw1_cwa_new_assessment_data 
									where callsign = '$inp_callsign' 
									and token = '$inp_token' 
									order by date_written";
		if ($inp_token == '') {
			$sql					= "select * from wpw1_cwa_new_assessment_data 
										where callsign = '$inp_callsign' 
										order by date_written";
		}
		if ($inp_callsign == '') {
			$sql					= "select * from wpw1_cwa_new_assessment_data 
										where token = '$inp_token'  
										order by date_written";
		}
		$assessmentResult		= $wpdb->get_results($sql);
		if ($assessmentResult === FALSE) {
			$thisError			= $wpdb->last_error;
			if ($doDebug) {
				echo "attempting to read from wpw1_cwa_new_assessment_data table failed. Error: $lastError<br />SQL: $sql<br />";
			}
			sendErrorEmail("FUNCTION displayAssessment Attempting to read from wpw1_cwa_new_assessment_data failed. Error: $lastError. SQL: $sql");
			$retVar			= array(FALSE,'reading assessment data failed','');
		} else {
			$numASRows		= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numASRows rows<br />";
			}
			if ($numASRows > 0) {
				$report				= "<h4>Assessment Details for $inp_callsign</h4>
										<table style='width:auto;'>
										<tr><th>Score</th>
											<th>Level</th>
											<th>Date</th>
											<th>Char Speed</th>
											<th>Eff Speed</th>
											<th>Qs</th>
											<th>Words</th>
											<th>Word Length</th>
											<th>Callsigns</th>
											<th>Answrs</th>
											<th>Vocab</th>
											<th>Info</th>
											<th>Q</th>
											<th>What Was Sent</th>
											<th>What Was Copied</th>
											<th>Points Gained</th></tr>";
				foreach($assessmentResult as $newAssessment) {				
					$record_id		= $newAssessment->record_id;
					$thiscallsign	= $newAssessment->callsign;
					$thisLevel		= $newAssessment->level;
					$thiscpm		= $newAssessment->cpm;
					$thiseff		= $newAssessment->eff;
					$thisfreq		= $newAssessment->freq;
					$thisquestions	= $newAssessment->questions;
					$thiswords		= $newAssessment->words;
					$thischars		= $newAssessment->characters;
					$thisCS			= $newAssessment->callsigns;
					$thisAnswers	= $newAssessment->answers;
					$thisVocab		= $newAssessment->vocab;
					$thisInfor		= $newAssessment->infor;
					$thisScore		= $newAssessment->score;
					$thisDetail		= $newAssessment->details;
					$thisDate		= $newAssessment->date_written;
					
					if ($thisLevel == 'Beginner') {
						$didBeginner	= TRUE;
						if ($thisScore > $bestResultBeginner) {
							$bestResultBeginner	= $thisScore;
						}
					}
					if ($thisLevel == 'Fundamental') {
						$didFundamental	= TRUE;
						if ($thisScore > $bestResultFundamental) {
							$bestResultFundamental	= $thisScore;
						}
					}
					if ($thisLevel == 'Intermediate') {
						$didIntermediate	= TRUE;
						if ($thisScore > $bestResultIntermediate) {
							$bestResultIntermediate	= $thisScore;
						}
					}
					if ($thisLevel == 'Advanced') {
						$didAdvanced	= TRUE;
						if ($thisScore > $bestResultAdvanced) {
							$bestResultAdvanced	= $thisScore;
						}
					}
					$stubDate		= substr($thisDate,0,10);
					$report			.= "<tr><td style='text-align:center;vertical-align:top;'>$thisScore</td>
											<td style='vertical-align:top;'>$thisLevel</td>
											<td style='text-align:center;vertical-align:top;'>$stubDate</td>
											<td style='text-align:center;vertical-align:top;'>$thiscpm</td>
											<td style='text-align:center;vertical-align:top;'>$thiseff</td>
											<td style='text-align:center;vertical-align:top;'>$thisquestions</td>
											<td style='text-align:center;vertical-align:top;'>$thiswords</td>
											<td style='text-align:center;vertical-align:top;'>$thischars</td>
											<td style='text-align:center;vertical-align:top;'>$thisCS</td>
											<td style='text-align:center;vertical-align:top;'>$thisAnswers</td>
											<td style='text-align:center;vertical-align:top;'>$thisVocab</td>
											<td style='vertical-align:top;'>$thisInfor</td>";

					$firstTime		= TRUE;					
					$detailsArray	= json_decode($thisDetail,TRUE);
					foreach($detailsArray as $thisKey => $thisValue) {
						$thisSent		= $thisValue['sent'];
						$thisCopied		= $thisValue['copied'];
						$thisPoints		= $thisValue['points'];
						if ($firstTime) {
							$firstTime	= FALSE;
							$report		.= "<td>$thisKey</td>
												<td>$thisSent</td>
												<td>$thisCopied</td>
												<td>$thisPoints</td></tr>\n";
						} else {
							$report			.= "<tr><td style='vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td style='text-align:center;vertical-align:top;'></td>
													<td>$thisKey</td>
													<td>$thisSent</td>
													<td>$thisCopied</td>
													<td>$thisPoints</td></tr>\n";
						}
					}
				}
				$report					.= "</table>";
				$retVal					= array(TRUE,
												$report,
												"bestResultBeginner=$bestResultBeginner&didBeginner=$didBeginner&bestResultFundamental=$bestResultFundamental&didFundamental=$didFundamental&bestResultIntermediate=$bestResultIntermediate&didIntermediate=$didIntermediate&bestResultAdvanced=$bestResultAdvanced&didAdvanced=$didAdvanced");

			} else {
				$retvar					= array(FALSE,'no assessment records found','');
			}
		}
	}
	return $retVal;
}
add_action('displayAssessment','displayAssessment');