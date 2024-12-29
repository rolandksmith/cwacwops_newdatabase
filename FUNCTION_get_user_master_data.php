function get_user_master_data($dataArray=array()) {

/*	Input data array: 	
			getMethod			One of 'callsign', 'id', 'surname', 'email'
			getInfo				the info to be used
			doDebug				value of doDebug
			testMode			value of testMode
			
	The program first looks in user_master for the requested getInfo. 

	If no record is found and the getMethod is 'callsign' it looks in wpw1_users 
	to get the user's id, then reads the data from wpw1_usermeta, and creates the 
	user_master record.
	
	If no record is found and the getMethod is not 'callsign', the program returns 
	FALSE with a no record found reason.
	
			
	Example of a call to get data for a user (the dataArray can be in any order):
		
		$dataArray			= array('getMethod'=>'surname',
									'getInfo'=>'Smith',
									'doDebug'=> $doDebug,
									'testMode'=> $testMode);
		$dataResult			= get_user_master_data($dataArray);
		// unpack the data
		$result				= $dataResult['result'];
		$reason				= $dataResult['reason'];
		$count				= $dataResult['count'];
		
		if ($result === FALSE) {
			// failure actions
			$content		.= "getting data for $callsign failed.<br />Reason: $reason<br />";
		} else {
			if ($doDebug) {
				echo "call to get data for $last_name returned $count rows of data<br />";
			}
			for ($ii=0;$ii<$count;$ii++) {
				$user_id 			= $dataResult[$ii]['user_id'];
				$user_call_sign 	= $dataResult[$ii]['user_call_sign'];
				$user_first_name 	= $dataResult[$ii]['user_first_name'];
				$user_last_name 	= $dataResult[$ii]['user_last_name'];
				$user_email 		= $dataResult[$ii]['user_email'];
				$user_phone 		= $dataResult[$ii]['user_phone'];
				$user_ph_code 		= $dataResult[$ii]['user_ph_code'];
				$user_city 			= $dataResult[$ii]['user_city'];
				$user_state 		= $dataResult[$ii]['user_state'];
				$user_zip_code 		= $dataResult[$ii]['user_zip_code'];
				$user_country_code 	= $dataResult[$ii]['user_country_code'];
				$user_country 		= $dataResult[$ii]['user_country'];
				$user_whatsapp 		= $dataResult[$ii]['user_whatsapp'];
				$user_telegram 		= $dataResult[$ii]['user_telegram'];
				$user_signal 		= $dataResult[$ii]['user_signal'];
				$user_messenger 	= $dataResult[$ii]['user_messenger'];
				$user_action_log	= $dataResult[$ii]['user_action_log'];
				$user_timezone_id	= $dataResult[$ii]['user_timezone_id'];
				$user_languages 	= $dataResult[$ii]['user_languages'];
				$user_survey_score	= $dataResult[$ii]['user_survey_score'];
				$user_is_admin		= $dataResult[$ii]['user_is_admin'];
				$user_role			= $dataResult[$ii]['user_role'];
				$user_prev_callsign	= $dataResult[$ii]['user_prev_callsign'];
				$user_date_created 	= $dataResult[$ii]['user_date_created'];
				$user_date_updated 	= $dataResult[$ii]['user_date_updated'];
				
				/// do something
			}
		}
		
		
	If the user_master record doesn't exist and is being created from 
	wpw1_users, the program attempts to figure out the timezone_id. If 
	the country_code is US, then the zip code is used. Otherwise, only 
	the country_code. If a country has more than one timezone_id, the 
	program sets the timezone_id to 'XX' and it up to the calling program 
	to figure out the appropriate timezone_id.
*/

	global $wpdb;	
	
	$initializationArray 			= data_initialization_func();
	$userName						= $initializationArray['userName'];
	$thisDate						= date('Y-m-d H:i:s');

	$getMethod		= '';
	$getInfo		= '';
	$doDebug		= TRUE;
	$testMode		= FALSE;
	
	$returnArray	= array();
	

	// unpack the dataArray
	if (count($dataArray) == 0) {
		$returnArray		= array('result'=>FALSE,
									'reason'=>'dataArray is empty', 
									'count'=>0);
		return $returnArray;
	}
	
	foreach($dataArray as $thisField=>$thisValue) {
		$$thisField		= $thisValue;
	}
	if ($doDebug) {
		echo "<br /><b>FUNCTION Get User Master Data</b><br />
			  dataArray:<br /><pre>";
		print_r($dataArray);
		echo "</pre><br />";
	}	

	$getMethodArray		= array('callsign', 
								'id', 
								'surname', 
								'email');
	if (!in_array($getMethod,$getMethodArray)) {
		$returnArray		= array('result'=>FALSE,
									'reason'=>'invalid getMethod', 
									'count'=>0);
		return $returnArray;		
	}

	if ($getInfo == '') {
		$returnArray		= array('result'=>FALSE,
									'reason'=>'getInfo is missing', 
									'count'=>0);
		return $returnArray;
	}
	
	// set up the tables
	if ($testMode) {
		if ($doDebug) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$userTableName				= "wpw1_users";
		$userMetaTableName			= "wpw1_usermeta2";
		$userMasterTableName		= "wpw1_cwa_user_master2";
		$countryCodesTableName		= "wpw1_cwa_country_codes";
	} else {
		$userTableName				= "wpw1_users";
		$userMetaTableName			= "wpw1_usermeta";
		$userMasterTableName		= "wpw1_cwa_user_master";
		$countryCodesTableName		= "wpw1_cwa_country_codes";
	}
	
	// read user_master. If record found, check if there is registration 
	// info in wpw1_user / usermeta. If so, update the user_master with 
	// the changed informtion 
	// then populate returnArray for each record found
	
	if ($getMethod == 'callsign') {
		$sql			= "select * from $userMasterTableName 
							where user_call_sign = '$getInfo'";
		$callsign		= $getInfo;
	}
	if ($getMethod == 'id') {
		$sql			= "select * from $userMasterTableName 
							where user_ID = $getInfo";
	}
	if ($getMethod == 'email') {
		$sql			= "select * from $userMasterTableName 
							where user_email = '$getInfo'";
	}
	if ($getMethod == 'surname') {
		$sql			= "select * from $userMasterTableName 
							where user_last_name like '$getInfo' 
							order by user_call_sign";
	}
	$sqlResult		= $wpdb->get_results($sql);
	if ($sqlResult === FALSE) {
		handleWPDBError("FUNCTION User Master Data",$doDebug);
		$returnArray		= array('result'=>FALSE,
									'reason'=>"reading $userMasterTableName for $callsign failed", 
									'count'=>0);
		return $returnArray;
	} else {
		$numRows	= $wpdb->num_rows;
		if ($doDebug) {
			echo "ran $sql<br />and retrieved $numRows rows<br />";
			
		}
		if ($numRows > 0) {
			$returnArray['result']		= TRUE;
			$returnArray['reason']		= '';
			$returnArray['count']		= $numRows;
			$ii							= -1;
			foreach($sqlResult as $sqlRow) {
				$user_id				= $sqlRow->user_ID;
				$user_call_sign			= $sqlRow->user_call_sign;
				$user_first_name		= $sqlRow->user_first_name;
				$user_last_name			= $sqlRow->user_last_name;
				$user_email				= $sqlRow->user_email;
				$user_ph_code			= $sqlRow->user_ph_code;
				$user_phone				= $sqlRow->user_phone;
				$user_city				= $sqlRow->user_city;
				$user_state				= $sqlRow->user_state;
				$user_zip_code			= $sqlRow->user_zip_code;
				$user_country_code		= $sqlRow->user_country_code;
				$user_country			= $sqlRow->user_country;
				$user_whatsapp			= $sqlRow->user_whatsapp;
				$user_telegram			= $sqlRow->user_telegram;
				$user_signal			= $sqlRow->user_signal;
				$user_messenger			= $sqlRow->user_messenger;
				$user_action_log		= $sqlRow->user_action_log;
				$user_timezone_id		= $sqlRow->user_timezone_id;
				$user_languages			= $sqlRow->user_languages;
				$user_survey_score		= $sqlRow->user_survey_score;
				$user_is_admin			= $sqlRow->user_is_admin;
				$user_role				= $sqlRow->user_role;
				$user_prev_callsign		= $sqlRow->user_prev_callsign;
				$user_date_created		= $sqlRow->user_date_created;
				$user_date_updated		= $sqlRow->user_date_updated;

/*
				$countrySQL		= "select * from $countryCodesTableName 
									where country_code = '$user_country_code'";
				$countrySQLResult	= $wpdb->get_results($countrySQL);
				if ($countrySQLResult === FALSE) {
					handleWPDBError("FUNCTION User Master Data",$doDebug);
					$user_country		= "UNKNOWN";
					$user_ph_code		= "";
				} else {
					$numCRows		= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
					}
					if($numCRows > 0) {
						foreach($countrySQLResult as $countryRow) {
							$user_country		= $countryRow->country_name;
							$user_ph_code		= $countryRow->ph_code;
						}
					} else {
						$user_country			= "Unknown";
						$user_ph_code			= "";
					}
				}
*/								
				$roleArray				= get_user_role($user_call_sign,$testMode,$doDebug);
				if ($doDebug) {
					echo "returned from get_user_role<br /><br />";
				}
				
				// see if there is new data in wpw1_users
				if ($doDebug) {
					echo "looking in wpw1_users for new info on $user_call_sign<br />";
				}
				$this_last_name			= "";
				$this_first_name		= "";
				$this_city				= "";
				$this_state				= "";
				$this_country_code		= "";
				$this_zip_code			= "";
				$this_phone				= "";
				$this_whatsapp			= "";
				$this_telegram			= "";
				$this_signal			= "";
				$this_messenger			= "";
				$this_languages			= "";
				$this_is_admin			= "";
				$this_role				= "";
				$this_ph_code			= "";
				$this_timezone_id		= "";

				$checkSQL		= "select * from $userTableName 
									where user_login like '$user_call_sign'";
				$checkResult	= $wpdb->get_results($checkSQL);
				if ($checkResult === FALSE) {
					handleWPDBError("FUNCTION Get User Master Data",$jobName);
				} else {
					$numRows	= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $checkSQL<br />and retrieved $numRows rows<br />";
					}
					if ($numRows > 0) {
						foreach($checkResult as $checkResultRow) {
							$thisID			= $checkResultRow->ID;
							$this_email		= $checkResultRow->user_email;
							
							
							//  now get the usermeta data for this ID
							$metaSQL		= "select * from $userMetaTableName 
												where user_id = $thisID";
							$metaResult		= $wpdb->get_results($metaSQL);
							if ($metaResult === FALSE) {
								handleWPDBError("FUNCTION User Master Data",$doDebug);
								if ($doDebug) {
									echo "running $metaSQL returned a result of FALSE<br />";
								}
							} else {
								$numMRows	= $wpdb->num_rows;
								if ($doDebug) {
									echo "ran $metaSQL<br />and retrieved $numMRows rows<br />";
								}
								if ($numMRows > 0) {
									foreach($metaResult as $metaRow) {
										$this_umeta_id		= $metaRow->umeta_id;
										$this_user_id		= $metaRow->user_id;
										$this_meta_key		= $metaRow->meta_key;
										$this_meta_value	= $metaRow->meta_value;
										
										$deleteThisRow		= FALSE;
										
										switch($this_meta_key) {
											case "last_name" :
												$this_last_name		= $this_meta_value;
												if ($doDebug) {
													echo "got $this_last_name $this_last_name from last_name<br />";
												}
												break;
											case "first_name" :
												$this_first_name		= $this_meta_value;
												if ($doDebug) {
													echo "got $this_first_name $this_first_name from first_name<br />";
												}
												break;
											case "wpum_field_20" :
												$this_city			= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_city $this_city from field_18<br />";
												}
												break;
											case "wpum_field_21" :
												$this_state			= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_state $this_state from field_19<br />";
												}
												break;
											case "wpum_field_22" :
												$this_country_code	= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_country_code $this_country_code from field_15<br />";
												}
												break;
											case "wpum_field_28" :
												$this_zip_code		= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_zip+code $this_zip_code from field_24<br />";
												}
												break;
											case "wpum_field_23" :
												$this_phone			= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_phone $this_phone from field_14<br />";
												}
												break;
											case "wpum_field_24" :
												$this_whatsapp		= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_whatsapp $this_whatsapp from field_20<br />";
												}
												break;
											case "wpum_field_25" :
												$this_telegram		= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_telegram $this_telegram from field_21<br />";
												}
												break;
											case "wpum_field_26" :
												$this_signal			= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_signal $this_signal from field_22<br />";
												}
												break;
											case "wpum_field_27" :
												$this_messenger		= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_messenger $this_messenger from field_23<br />";
												}
												break;
											case "wpum_field_30" :
												$this_languages		= $this_meta_value;
												$deleteThisRow	= TRUE;
												if ($doDebug) {
													echo "got $this_languages $this_languages from field_27<br />";
												}
												break;
											case "wpw1_capabilities" :
												if (preg_match('/administrator/i',$this_meta_value)) {
													$this_is_admin	= 'Y';
												} else {
													$vis_admin	= 'N';
												}
												if (preg_match('/student/i',$this_meta_value)) {
													$this_role		= 'student';
												} elseif (preg_match('/advisor/i',$this_meta_value)) {
													$this_role		= 'advisor';
												} else {
													$this_role		= 'other';
												}
												$deleteThisRow	= FALSE;
												if ($doDebug) {
													echo "got $this_role $this_role from capabilities<br />";
												}
												break;
											default :
												$deleteThisRow	= FALSE;
										}
										if ($deleteThisRow) {
											$metaDelete			= $wpdb->delete($userMetaTableName,
																			array('umeta_id'=>$this_umeta_id),
																			array('%d'));
											if ($metaDelete === FALSE) {
												handleWPDBError("FUNCTION User Master Data",$doDebug);
												if ($doDebug) {
													echo "attempting to delete umeta_id $this_umeta_id returned a result of FALSE<br />";
												}
											} else {
												if ($doDebug) {
													echo "deleted $userMetaTableName record for umeta_id of $this_umeta_id<br />";
												}
											}
										}
									}		// finished with the usermeta fields
									// get the phone code and country name, if possible
									if ($this_country_code != '') {
										$countrySQL		= "select * from $countryCodesTableName 
															where country_code = '$this_country_code'";
										$countrySQLResult	= $wpdb->get_results($countrySQL);
										if ($countrySQLResult === FALSE) {
											handleWPDBError("FUNCTION User Master Data",$doDebug);
											$this_country		= "UNKNOWN";
											$this_ph_code		= "";
										} else {
											$numCRows		= $wpdb->num_rows;
											if ($doDebug) {
												echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
											}
											if($numCRows > 0) {
												foreach($countrySQLResult as $countryRow) {
													$this_country		= $countryRow->country_name;
													$this_ph_code		= $countryRow->ph_code;
												}
											} else {
												$this_country			= "Unknown";
												$this_ph_code			= "";
											}
										}
 									} 
 									// finally, get the role and is_admin
									$roleArray				= get_user_role($user_call_sign,$testMode,$doDebug);
									if ($doDebug) {
										echo "returned from get_user_role<br /><br />
											  roleArray:<br /><pre>";
										print_r($roleArray);
										echo "</pre><br />";
									}
									$result					= "";
									$reason					= "";
									$isAdvisor				= FALSE;
									$isAdmin				= FALSE;
									$isStudent				= FALSE;
									$isOther				= FALSE;
									if ($doDebug) {
									}
									foreach($roleArray as $thisField => $thisValue) {
										${$thisField}		= $thisValue;
									}
									if ($result === FALSE) {
										if ($doDebug) {
											echo "get_user_role returned FALSE<br />Reason: $reason<br />";
										}
										sendErrorEmail("FUNCTION_user_master_data: get_user_role returned FALSE for $user_call_sign");
									}
									if ($isAdmin && $user_is_admin != 'Y') {
										$this_is_admin						= 'Y';
									}
									if ($isStudent && $user_role != 'student') {
										$this_role							= 'student';
									} 
									if ($isAdvisor && $user_role != 'advisor') {
										$this_role							= 'advisor';
									} 
									if ($isOther && $role != 'other') {
										$this_role							= 'other';
									} 
									// have all the data. See if anything has changed
									// if so, update the user_master record
									// then return the data
									
									$updateParams				= array();
									$doUpdate					= FALSE;
									$sigChange					= FALSE;
									$countryChanged				= FALSE;
									$zipChanged					= FALSE;
									
									$thisActionLog				= "";
									if ($this_last_name != '') {
										if ($this_last_name != $user_last_name) {
											$user_last_name		= $this_last_name;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_last_name|$user_last_name|s";
											$thisActionLog		.= " updated last_name to $user_last_name. ";
										}
									}
									if ($this_first_name != '') {
										if ($this_first_name != $user_first_name) {
											$user_first_name		= $this_first_name;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_first_name|$user_first_name|s";
											$thisActionLog		.= " updated first_name to $user_first_name. ";
										}
									}
									if ($this_city != '') {
										if ($this_city != $user_city) {
											$user_city		= $this_city;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_city|$user_city|s";
											$thisActionLog		.= " updated city to $user_city. ";
										}
									}
									if ($this_state != '') {
										if ($this_state != $user_state) {
											$user_state		= $this_state;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_state|$user_state|s";
											$thisActionLog		.= " updated state to $user_state. ";
										}
									}
									if ($this_country_code != '') {
										if ($this_country_code != $user_country_code) {
											$user_country_code		= $this_country_code;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_country_code|$user_country_code|s";
											$thisActionLog		.= " updated country_code to $user_country_code. ";
											$sigChange			= TRUE;
											$countryChanged		= TRUE;
											$updateParams		= "$user_country|$thisCountry|s";
										}
									}
									if ($this_zip_code != '') {
										if ($this_zip_code != $user_zip_code) {
											$user_zip_code		= $this_zip_code;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_zip_code|$user_zip_code|s";
											$thisActionLog		.= " updated zip_code to $user_zip_code. ";
											if ($user_country_code == 'US') {
												$sigChange		= TRUE;
												$zipChanged		= TRUE;
											}
										}
									}
									if ($this_phone != '') {
										if ($this_phone != $user_phone) {
											$user_phone		= $this_phone;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_phone|$user_phone|s";
											$thisActionLog		.= " updated phone to $user_phone. ";
										}
									}
									if ($this_ph_code != '') {
										if ($this_ph_code != $user_ph_code) {
											$user_ph_code		= $this_ph_code;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_ph_code|$user_ph_code|s";
											$thisActionLog		.= " updated ph_code to $user_ph_code. ";
										}
									}
									if ($this_whatsapp != '') {
										if ($this_whatsapp != $user_whatsapp) {
											$user_whatsapp		= $this_whatsapp;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_whatsapp|$user_whatsapp|s";
											$thisActionLog		.= " updated whatsapp to $user_whatsapp. ";
										}
									}
									if ($this_telegram != '') {
										if ($this_telegram != $user_telegram) {
											$user_telegram		= $this_telegram;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_telegram|$user_telegram|s";
											$thisActionLog		.= " updated telegram to $user_telegram. ";
										}
									}
									if ($this_signal != '') {
										if ($this_signal != $user_signal) {
											$user_signal		= $this_signal;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_signal|$user_signal|s";
											$thisActionLog		.= " updated signal to $user_signal. ";
										}
									}
									if ($this_messenger != '') {
										if ($this_messenger != $user_messenger) {
											$user_messenger		= $this_messenger;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_messenger|$user_messenger|s";
											$thisActionLog		.= " updated messenger to $user_messenger. ";
										}
									}
									if ($this_languages != '') {
										if ($this_languages != $user_languages) {
											$user_languages		= $this_languages;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_languages|$user_languages|s";
											$thisActionLog		.= " updated languages to $user_languages. ";
										}
									}
									if ($this_is_admin != '') {
										if ($this_is_admin != $user_is_admin) {
											$user_is_admin		= $this_is_admin;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_is_admin|$user_is_admin|s";
											$thisActionLog		.= " updated is_admin to $user_is_admin. ";
										}
									}
									if ($this_role != '') {
										if ($this_role != $user_role) {
											$user_role		= $this_role;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_role|$user_role|s";
											$thisActionLog		.= " updated role to $user_role. ";
										}
									}
									
									// if significant change then figure out the timezone ID
									if ($sigChange) {
										if ($doDebug) {
											echo "figuring out the timezone_id. Country_code: $user_country_code<br />";
										}
										if ($user_country_code == '') {
											$this_timezone_id 	= 'XX';
										} elseif ($user_country_code == 'US') {
											if ($doDebug) {
												echo "have a country code of US, verifying the zip code<br />";
											}
											$zipResult		= getOffsetFromZipCode($user_zip_code,'',TRUE,$testMode,$doDebug);
											if ($zipResult[0] == 'NOK') {
												$this_timezone_id		="XX";
											} else {
												$this_timezone_id		= $zipResult[1];
											}
										} else {		// country code not US. Figure out the timezone and offset
											if ($doDebug) {
												echo "dealing with a non-us country of $user_country_code<br />";
											}
											$timezone_identifiers 		= DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $user_country_code );
											$myInt						= count($timezone_identifiers);
											if ($doDebug) {
												echo "found $myInt identifiers for country $user_country_code";
											}
											
											if ($myInt == 1) {									//  only 1 found. Use that and continue
												$this_timezone_id		= $timezone_identifiers[0];
											} else {
												$timezoneSelector			= array();		// localDateTime => $myCity
												$ii							= 1;
												if ($doDebug) {
													echo "have the list of identifiers for $user_country_code<br />";
												}
												$oneChecked				= FALSE;
												foreach ($timezone_identifiers as $thisID) {
													if ($doDebug) {
														echo "Processing $thisID<br />";
													}
													$selector			= "";
													if (isset($browser_timezone_id)) {
														if ($browser_timezone_id == $thisID) {
															$selector		= "checked";
															$oneChecked		= TRUE;
														}
													}
													$dateTimeZoneLocal 	= new DateTimeZone($thisID);
													$dateTimeLocal 		= new DateTime("now",$dateTimeZoneLocal);
													$localDateTime 		= $dateTimeLocal->format('h:i A');
													$myInt				= strpos($thisID,"/");
													$myCity				= substr($thisID,$myInt+1);
													
													if (!array_key_exists($localDateTime,$timezoneSelector)) {
														$timezoneSelector[$localDateTime] 	= $thisID;
													}
												} 
												if (count($timezoneSelector) == 0) {
													$this_timezone_id			= 'XX';
												}
												if (count($timezoneSelector) == 1) {
													foreach($timezoneSelector as $thisDateTime => $thisID);
														$this_timezone_id			= $thisID;
												}
												if (count($timezoneSelector) > 1) {
													$this_timezone_id			= "XX";
												}
											}
										}
										if ($this_timezone_id != $user_timezone_id) {
											$user_timezone_id		= $this_timezone_id;
											$doUpdate			= TRUE;
											$updateParams[]		= "user_timezone_id|$user_timezone_id|s";
											$thisActionLog		.= " updated timezone_id to $user_timezone_id. ";
										
										}
									}			// end of sigChange
									// are there any updates?
									if ($doUpdate) {
										if ($doDebug) {
											echo "there are updates:<br /><pre>";
											print_r($updateParams);
											echo "</pre><br />";
										}
										$actionDate				= date('dMy H:i');
										$user_action_log		.= " / $actionDate FUNCTION_get_user_master_data $thisActionLog ";
										$updateParams[]			= "user_action_log|$user_action_log|s";
										$userMasterData			= array('tableName'=>$userMasterTableName,
																		'inp_method'=>'update',
																		'inp_data'=>$updateParams,
																		'inp_format'=>array(''),
																		'jobname'=>"FUNCTION_get_user_master_data",
																		'inp_id'=>$user_id,
																		'inp_callsign'=>$user_call_sign,
																		'inp_who'=>"unknown",
																		'testMode'=>$testMode,
																		'doDebug'=>$doDebug);
										$updateResult	= update_user_master($userMasterData);
										if ($updateResult[0] === FALSE) {
											handleWPDBError($jobname,$doDebug);
											$returnArray		= array('result'=>FALSE,
																		'reason'=>'updating user_master failed', 
																		'count'=>0);
											return $returnArray;
										} 
									} else {
										if ($doDebug) {
											echo "no updates requested<br />";
										}
									}
								}
							}
						}
					}
				}
				
				
				/// have all the available data
				if ($doDebug) {
					echo "<br />have all available data for this record<br />Returning<br /><br />";
				}
				$ii++;
				$returnArray[$ii]		= array('user_id'=>$user_id,
												'user_call_sign'=>$user_call_sign,
												'user_first_name'=>$user_first_name,
												'user_last_name'=>$user_last_name,
												'user_email'=>$user_email,
												'user_phone'=>$user_phone,
												'user_ph_code'=>$user_ph_code,
												'user_city'=>$user_city,
												'user_state'=>$user_state,
												'user_zip_code'=>$user_zip_code,
												'user_country_code'=>$user_country_code,
												'user_country'=>$user_country,
												'user_whatsapp'=>$user_whatsapp,
												'user_telegram'=>$user_telegram,
												'user_signal'=>$user_signal,
												'user_messenger'=>$user_messenger,
												'user_action_log'=>$user_action_log,
												'user_timezone_id'=>$user_timezone_id,
												'user_languages'=>$user_languages,
												'user_survey_score'=>$user_survey_score,
												'user_is_admin'=>$user_is_admin,
												'user_role'=>$user_role,
												'user_prev_callsign'=>$user_prev_callsign,
												'user_date_created'=>$user_date_created,
												'user_date_updated'=>$user_date_updated);
			}
			return $returnArray;


		} else {			// no user_master record. If getMethod is callsign, 
							// look in userMeta
							// if the record is found there, create the user_master 
							// record and delete the userMeta records
			if ($getMethod == 'callsign') {
				if ($doDebug) {
					echo "no user_master record for $callsign. Looking in userMeta<br />";
				}
				$verifiedUser	= TRUE;
				$user_id				= "";
				$user_first_name		= "";
				$user_last_name		= "";
				$user_email			= "";
				$user_phone			= "";
				$user_ph_code		= "";
				$user_city			= "";
				$user_state			= "";
				$user_zip_code		= "";
				$user_country_code	= "";
				$user_country		= "";
				$user_whatsapp		= "";
				$user_telegram		= "";
				$user_signal			= "";
				$user_messenger		= "";
				$user_action_log	= "";
				$user_survey_score	= 0;
				$user_is_admin		= "";
				$user_role			= "";
				$user_timezone_id	= "";
				$user_languages		= "";

				$userSQL			= "select * from $userTableName 
										where user_login like '$getInfo'";
				$userResult		= $wpdb->get_results($userSQL);
				if ($userResult === FALSE) {
					handleWPDBError("FUNCTION User Master Data",$doDebug);
					if ($doDebug) {
						echo "running $userSQL returned a result of FALSE<br />";
					}
					$returnArray			= array('result'=>FALSE,
													'reason'=>"attempting to query $userTableName returned FALSE",
													'count'=>0);
					return $returnArray;
				} else {
					$numURows	= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $userSQL<br />and retrieved $numURows rows<br />";
					}
					if ($numURows > 0) {			// have a users record. Get the ID
						$returnArray['result']		= TRUE;
						$returnArray['reason']		= '';
						$returnArray['count']		= $numURows;
						foreach($userResult as $userRow) {
							$thisID			= $userRow->ID;
							$user_call_sign	= strtoupper($userRow->user_login);
							$user_email		= $userRow->user_email;
							
							if ($doDebug) {
								echo "have a ID of $thisID for $callsign<br />";
							}
							
							// first determine if this is a verified user
							$verifiedData	= $wpdb->get_var("select meta_value 
												from $userMetaTableName 
												where user_id = $thisID 
												and meta_key = 'wumuv_needs_verification'");
							if ($doDebug) {
								$lastQuery	= $wpdb->last_query;
								echo "ran $lastQuery. verifiedData:<br ><pre>";
								print_r($verifiedData);
								echo "</pre><br />";
							}
							if ($verifiedData == NULL) {
								if ($doDebug) {
									echo "verifiedData is NULL<br />";
								}
							} elseif ($verifiedData == "1") {
								$verifiedUser	= FALSE;
								if ($doDebug) {
									echo "verifiedData is 1<br />";
								}
							}
							// if user not verified, return no record found
							if (!$verifiedUser) {
								if ($doDebug) {
									echo "unverified user, so no record for this callsign<br />";
								}
								$returnArray			= array('result'=>FALSE,
																'reason'=>"unverified user",
																'count'=>0);
								return $returnArray;
							} else {
								// now with this ID, get the data from userMeta
								$metaSQL		= "select * from $userMetaTableName 
													where user_id = $thisID";
								$metaResult		= $wpdb->get_results($metaSQL);
								if ($metaResult === FALSE) {
									handleWPDBError("FUNCTION User Master Data",$doDebug);
									if ($doDebug) {
										echo "running $metaSQL returned a result of FALSE<br />";
									}
									$returnArray			= array('result'=>FALSE,
																	'reason'=>"attempting to read $userMetaTableName returned FALSE");
									return $returnArray;
								} else {
									$numMRows	= $wpdb->num_rows;
									if ($doDebug) {
										echo "ran $metaSQL<br />and retrieved $numMRows rows<br />";
									}
									if ($numMRows > 0) {
										foreach($metaResult as $metaRow) {
											$this_umeta_id		= $metaRow->umeta_id;
											$this_user_id		= $metaRow->user_id;
											$this_meta_key		= $metaRow->meta_key;
											$this_meta_value	= $metaRow->meta_value;
											
											$deleteThisRow		= FALSE;
											
											switch($this_meta_key) {
												case "last_name" :
													$user_last_name		= $this_meta_value;
													if ($doDebug) {
														echo "got user_last_name $user_last_name from last_name<br />";
													}
													break;
												case "first_name" :
													$user_first_name		= $this_meta_value;
													if ($doDebug) {
														echo "got user_first_name $user_first_name from first_name<br />";
													}
													break;
												case "wpum_field_20" :
													$user_city			= $this_meta_value;
													$deleteThisRow	= TRUE;
		 											if ($doDebug) {
														echo "got user_city $user_city from field_18<br />";
													}
													break;
												case "wpum_field_21" :
													$user_state			= $this_meta_value;
													$deleteThisRow	= TRUE;
													if ($doDebug) {
														echo "got user_state $user_state from field_19<br />";
													}
													break;
												case "wpum_field_22" :
													$user_country_code	= $this_meta_value;
													$deleteThisRow	= TRUE;
													if ($doDebug) {
														echo "got user_country_code $user_country_code from field_15<br />";
													}
													break;
												case "wpum_field_28" :
													$user_zip_code		= $this_meta_value;
													$deleteThisRow	= TRUE;
													if ($doDebug) {
														echo "got user_zip+code $user_zip_code from field_24<br />";
													}
													break;
												case "wpum_field_23" :
													$user_phone			= $this_meta_value;
													$deleteThisRow	= TRUE;
													if ($doDebug) {
														echo "got user_phone $user_phone from field_14<br />";
													}
													break;
												case "wpum_field_24" :
													$user_whatsapp		= $this_meta_value;
													$deleteThisRow	= TRUE;
													if ($doDebug) {
														echo "got user_whatsapp $user_whatsapp from field_20<br />";
													}
													break;
												case "wpum_field_25" :
													$user_telegram		= $this_meta_value;
													$deleteThisRow	= TRUE;
													if ($doDebug) {
														echo "got user_telegram $user_telegram from field_21<br />";
													}
													break;
												case "wpum_field_26" :
													$user_signal			= $this_meta_value;
													$deleteThisRow	= TRUE;
													if ($doDebug) {
														echo "got user_signal $user_signal from field_22<br />";
													}
													break;
												case "wpum_field_27" :
													$user_messenger		= $this_meta_value;
													$deleteThisRow	= TRUE;
													if ($doDebug) {
														echo "got user_messenger $user_messenger from field_23<br />";
													}
													break;
												case "wpum_field_30" :
													$user_languages		= $this_meta_value;
													$deleteThisRow	= TRUE;
													if ($doDebug) {
														echo "got user_languages $user_languages from field_27<br />";
													}
													break;
												case "wpw1_capabilities" :
													if (preg_match('/administrator/i',$this_meta_value)) {
														$user_is_admin	= 'Y';
													} else {
														$vis_admin	= 'N';
													}
													if (preg_match('/student/i',$this_meta_value)) {
														$user_role		= 'student';
													} elseif (preg_match('/advisor/i',$this_meta_value)) {
														$user_role		= 'advisor';
													} else {
														$user_role		= 'other';
													}
													$deleteThisRow	= FALSE;
													if ($doDebug) {
														echo "got user_role $user_role from capabilities<br />";
													}
													break;
												default :
													$deleteThisRow	= FALSE;
											}
											if ($deleteThisRow) {
												$metaDelete			= $wpdb->delete($userMetaTableName,
																				array('umeta_id'=>$this_umeta_id),
																				array('%d'));
												if ($metaDelete === FALSE) {
													handleWPDBError("FUNCTION User Master Data",$doDebug);
													if ($doDebug) {
														echo "attempting to delete umeta_id $this_umeta_id returned a result of FALSE<br />";
													}
												} else {
													if ($doDebug) {
														echo "deleted $userMetaTableName record for umeta_id of $this_umeta_id<br />";
													}
												}
											}
										}
										
										if ($user_country_code != '') {
											// get the country and ph_code
											$countrySQL		= "select * from $countryCodesTableName 
																where country_code = '$user_country_code'";
											$countrySQLResult		= $wpdb->get_results($countrySQL);
											if ($countrySQLResult === FALSE) {
												handleWPDBError($jobname,$doDebug);
												$user_country		= "UNKNOWN";
												$user_ph_code		= "";
											} else {
												$numCRows		= $wpdb->num_rows;
												if ($doDebug) {
													echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
												}
												if($numCRows > 0) {
													foreach($countrySQLResult as $countryRow) {
														$user_country		= $countryRow->country_name;
														$user_ph_code		= $countryRow->ph_code;
													}
												} else {
													$user_country			= "Unknown";
													$user_ph_code			= "";
												}
											}
										}
										// figure out the timezone_id
										if ($doDebug) {
											echo "figuring out the timezone_id. Country_code: $user_country_code<br />";
										}
										if ($user_country_code == '') {
											$user_timezone_id 	= 'XX';
										} else {
											if ($user_country_code == 'US') {
												if ($doDebug) {
													echo "have a country code of US, verifying the zip code<br />";
												}
												$zipResult		= getOffsetFromZipCode($user_zip_code,'',TRUE,$testMode,$doDebug);
												if ($zipResult[0] == 'NOK') {
													$user_timezone_id		="XX";
												} else {
													$user_timezone_id		= $zipResult[1];
												}
											} else {		// country code not US. Figure out the timezone and offset
												if ($doDebug) {
													echo "dealing with a non-us country of $user_country_code<br />";
												}
												$timezone_identifiers 		= DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $user_country_code );
												$myInt						= count($timezone_identifiers);
												if ($doDebug) {
													echo "found $myInt identifiers for country $user_country_code";
												}
												
												if ($myInt == 1) {									//  only 1 found. Use that and continue
													$timezone_id		= $timezone_identifiers[0];
												} else {
													$timezoneSelector			= array();		// localDateTime => $myCity
													$ii							= 1;
													if ($doDebug) {
														echo "have the list of identifiers for $user_country_code<br />";
													}
													$oneChecked				= FALSE;
													foreach ($timezone_identifiers as $thisID) {
														if ($doDebug) {
															echo "Processing $thisID<br />";
														}
														$selector			= "";
														if (isset($browser_timezone_id)) {
															if ($browser_timezone_id == $thisID) {
																$selector		= "checked";
																$oneChecked		= TRUE;
															}
														}
														$dateTimeZoneLocal 	= new DateTimeZone($thisID);
														$dateTimeLocal 		= new DateTime("now",$dateTimeZoneLocal);
														$localDateTime 		= $dateTimeLocal->format('h:i A');
														$myInt				= strpos($thisID,"/");
														$myCity				= substr($thisID,$myInt+1);
														
														if (!array_key_exists($localDateTime,$timezoneSelector)) {
															$timezoneSelector[$localDateTime] 	= $thisID;
														}
													} 
													if (count($timezoneSelector) == 0) {
														$user_timezone_id			= 'XX';
													}
													if (count($timezoneSelector) == 1) {
														foreach($timezoneSelector as $thisDateTime => $thisID);
															$user_timezone_id			= $thisID;
													}
													if (count($timezoneSelector) > 1) {
														$user_timezone_id			= "XX";
													}
												}
											}
										}
										
										// have all the data. Insert the record into user_master
										$user_action_log	= "/ $thisDate $userName Record created ";
										$user_prev_callsign	= '';
										$userInsert			= $wpdb->insert($userMasterTableName,
																			array('user_call_sign'=>$user_call_sign,
																				  'user_first_name'=>$user_first_name,
																				  'user_last_name'=>$user_last_name,
																				  'user_email'=>$user_email,
																				  'user_ph_code'=>$user_ph_code,
																				  'user_phone'=>$user_phone,
																				  'user_city'=>$user_city,
																				  'user_state'=>$user_state,
																				  'user_zip_code'=>$user_zip_code,
																				  'user_country_code'=>$user_country_code,
																				  'user_country'=>$user_country,
																				  'user_whatsapp'=>$user_whatsapp,
																				  'user_telegram'=>$user_telegram,
																				  'user_signal'=>$user_signal,
																				  'user_messenger'=>$user_messenger,
																				  'user_action_log'=>$user_action_log,
																				  'user_survey_score'=>0,
																				  'user_timezone_id'=>$user_timezone_id,
																				  'user_languages'=>$user_languages, 
																				  'user_is_admin'=>$user_is_admin,
																				  'user_prev_callsign'=>'', 
																				  'user_role'=>$user_role ),
																			  array('%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%d',
																					'%s',
																					'%s',
																					'%s',
																					'%s',
																					'%s'));
										if ($userInsert === FALSE) {
											handleWPDBError("FUNCTION_User_Master_Data",$doDebug);
											if ($doDebug) {
												echo "attempting to insert data for $callsign into $userMasterTableName returned FALSE<br />";
											}
											$returnArray				= array('result'=>FALSE,
																				'reason'=>"unable to insert $callsign info into $userMasterTableName table",
																				'count'=>0);
											return $returnArray;
										} else {
											$id			= $wpdb->insert_id;
											if ($doDebug) {
												echo "user_master record added for $callsign at id $id<br />";
											}
											// get the country and ph_code
											$countrySQL		= "select * from $countryCodesTableName 
																where country_code = '$user_country_code'";
											$countrySQLResult	= $wpdb->get_results($countrySQL);
											if ($countrySQLResult === FALSE) {
												handleWPDBError("FUNCTION_User_Master_Data",$doDebug);
												$country		= "UNKNOWN";
												$ph_code		= "";
											} else {
												$numCRows		= $wpdb->num_rows;
												if ($doDebug) {
													echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
												}
												if($numCRows > 0) {
													foreach($countrySQLResult as $countryRow) {
														$user_country		= $countryRow->country_name;
														$user_ph_code		= $countryRow->ph_code;
													}
												} else {
													$user_country			= "Unknown";
													$user_ph_code			= "";
												}
											}
	
											// now return the data
											$myDate					= date('Y-m-d H:i:s');
											$returnArray[0]			= array('user_id'=>$id,
																			'user_call_sign'=>$user_call_sign,
																			'user_first_name'=>$user_first_name,
																			'user_last_name'=>$user_last_name,
																			'user_country'=>$user_country,
																			'user_email'=>$user_email,
																			'user_phone'=>$user_phone,
																			'user_ph_code'=>$user_ph_code,
																			'user_city'=>$user_city,
																			'user_state'=>$user_state,
																			'user_zip_code'=>$user_zip_code,
																			'user_country_code'=>$user_country_code,
																			'user_country'=>$user_country,
																			'user_whatsapp'=>$user_whatsapp,
																			'user_telegram'=>$user_telegram,
																			'user_signal'=>$user_signal,
																			'user_messenger'=>$user_messenger,
																			'user_action_log'=>$user_action_log,
																			'user_languages'=>$user_languages,
																			'user_timezone_id'=>$user_timezone_id,
																			'user_survey_score'=>$user_survey_score,
																			'user_is_admin'=>$user_is_admin,
																			'user_role'=>$user_role,
																			'user_prev_callsign'=>$user_prev_callsign,
																			'user_date_created'=>$myDate,
																			'user_date_updated'=>$myDate);
											if ($doDebug) {
												echo "have all the data. Returning<br /><br />";
											}
											return $returnArray;
										}
									} else {
										if ($doDebug) {
											echo "no userMeta records for $callsign<br />";
										}
										$returnArray			= array('result'=>FALSE,
																		'reason'=>"no records found for $callsign in $userMetaTableName table",
																		'count'=>0);
										return $returnArray;
									}
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "no userMeta record, so no record for this callsign<br />";
						}
						$returnArray			= array('result'=>FALSE,
														'reason'=>"no record for $callsign", 
														'count'=>0);
						return $returnArray;
					}
				}
				
			} else {			/// no record found and getMethod not callsign
				$returnArray			= array('result'=>FALSE,
												'reason'=>"No user_master record found", 
												'count'=>0);
			}
		}
	}
}
add_action('get_user_master_data','get_user_master_data');
