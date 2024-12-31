function get_user_master_data($dataArray=array()) {

/*	Input data array: 	
			getMethod			userName
			getInfo				the value of userName
			doDebug				value of doDebug
			testMode			value of testMode
			
	The function first looks in user_master for the requested getInfo. If a record 
	is found, the function errors and returns

	If no record is found it looks in wpw1_users 
	to get the user's id, then reads the data from wpw1_usermeta, and creates the 
	user_master record.
		
	If the user_master record being created from 
	wpw1_users, the program attempts to figure out the timezone_id. If 
	the country_code is US, then the zip code is used. Otherwise, only 
	the country_code. If a country has more than one timezone_id, the 
	program sets the timezone_id to 'XX' and it up to the calling program 
	to figure out the appropriate timezone_id.
*/

	global $wpdb;	
	
	$thisDate		= date('Y-m-d H:i:s');
	$getMethod		= '';
	$getInfo		= '';
	$doDebug		= TRUE;
	$testMode		= FALSE;
	$gotError		= FALSE;
	$errors			= "";
	
	$returnArray	= array();
	

	// unpack the dataArray
	if (count($dataArray) == 0) {
		$gotError			= TRUE;
		$errors				.= "dataArray is empty<br />";
	} else {
		foreach($dataArray as $thisField=>$thisValue) {
			$$thisField		= $thisValue;
		}
		if ($doDebug) {
			echo "<br /><b>FUNCTION Get User Master Data</b><br />
				  dataArray:<br /><pre>";
			print_r($dataArray);
			echo "</pre><br />";
		}	
	
		if ($getMethod != 'callsign') {
			$gotError			= TRUE;
			$errors				.= "invalid getMethod of $getMethod<br />";
		}
	
		if ($getInfo == '') {
			$gotError			= TRUE;
			$errors				.= "getInfo is mssing<br />";
		}

	if (!$gotError) {
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
		
		// read user_master. If record found, error and return
		
		$getStr			= strtoupper($getInfo);
		$sql			= "select * from $userMasterTableName 
								where user_call_sign like '$getStr'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError("FUNCTION User Master Data",$doDebug);
			$gotError			= TRUE;
			$errors				.= "reading $userMasterTableName for $callsign failed<br />";
		} else {
			$numRows	= $wpdb->num_rows;
			if ($doDebug) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				$gotError		= TRUE;
				$errors			.= "$getInfo already has a userMaster record<br />";
			} else {
				// No userMaster record. Create it
				$verifiedUser		= TRUE;
				$user_id			= "";
				$user_first_name	= "";
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
				$user_signal		= "";
				$user_messenger		= "";
				$user_action_log	= "";
				$user_survey_score	= 0;
				$user_is_admin		= "";
				$user_role			= "";
				$user_timezone_id	= "";
				$user_languages		= "";

				$userSQL			= "select * from $userTableName 
										where user_login like '$getInfo'";
				$userResult			= $wpdb->get_results($userSQL);
				if ($userResult === FALSE) {
					handleWPDBError("FUNCTION Get User Master Data",$doDebug,"running $userSQL returned a result of FALSE");
					$gotError		= TRUE;
					$errors			.= "attempting to query $userTableName returned FALSE<br />";
				} else {
					$numURows	= $wpdb->num_rows;
					if ($doDebug) {
						echo "ran $userSQL<br />and retrieved $numURows rows<br />";
					}
					if ($numURows > 0) {			// have a users record. Get the ID
						foreach($userResult as $userRow) {
							$thisID			= $userRow->ID;
							$user_call_sign	= strtoupper($userRow->user_login);
							$user_email		= $userRow->user_email;
							
							if ($doDebug) {
								echo "have a ID of $thisID for $user_call_sign<br />";
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
								$gotError			= TRUE;
								$errors				.= "Unverified user. Treat as no record found<br />";
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
									$gotError		= TRUE;
									$errors			.= "attempting to read $userMetaTableName returned FALSE<br />";
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
														echo "got user_zip_code $user_zip_code from field_24<br />";
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
										$user_action_log	= "/ $thisDate $user_call_sign Record created ";
										$user_prev_callsign	= '';
										
										
										
										try {
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
												$gotError			= TRUE;
												$errors				.= "unable to insert $callsign info into $userMasterTableName table<br />";
											} else {
												$id			= $wpdb->insert_id;
												if ($doDebug) {
													echo "user_master record added for $user_call_sign at id $id<br />";
												}
		
												// now return the data
												$myDate					= date('Y-m-d H:i:s');
												$returnArray			= array('result'=>TRUE,
																				'reason'=>'',
																				'user_id'=>$id,
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
											}
										} catch (Exception $e) {
											$theError		= $e->getMessage();
											if ($doDebug) {
												echo "insert exception: $theError<br />";
											}
											error_log("get_user_master insert exception: $theError",1,"rolandksmith@gmail.com");
											$returnArray				= array('result'=>FALSE,
																				'reason'=>"$theError",
																				'count'=>0);
										}
									} else {
										if ($doDebug) {
											echo "no userMeta records for $callsign<br />";
										}
										$gotError			= TRUE;
										$errors				.= "no records found for $callsign in $userMetaTableName table<br />";
									}
								}
							}
						}
					} else {
						if ($doDebug) {
							echo "no users record, so no record for this callsign<br />";
						}
						$gotError		= TRUE;
						$errors			.= "No record for $getInfo in $userTableName<br />";
					}
				}				
			}
		}
	}
	if ($gotError) {
		$returnArray	= array('result'=>FALSE, 
								'reason'=>$errors);
	}
	return $returnArray;
	}
}
add_action('get_user_master_data','get_user_master_data');
