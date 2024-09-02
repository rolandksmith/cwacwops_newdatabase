function user_master_data($dataArray=array()) {

/*	Input data array: 	
			callsign			the user_login callsign
			action				either 'get' or 'update' or 'delete'
			update data			the data to be updated if the action is update
			debugging			value of doDebug
			testing				value of testMode

	If the action is 'get', the program first looks in wpw1_cwa_user_master(2) for the 
	requested callsign. If no record is found, it looks in wpw1_users to get the user's id 
	and then reads the data from wpw1_usermeta.
	
	The data is returned in an array consisting of the fieldname => value. If no record 
	was found, then the array will consist of one item: 'result'=>FALSE
	
	If the data is found, the returned array will look like this:
		$returnArray			= array('result'=>TRUE,
										'id'=>(value),
										'callsign'=>(value),
										'first_name'=>(value),
										'last_name'=>(value),
										'email'=>(value),
										'phone'=>(value),
										'ph_code'=>(value),
										'city'=>(value),
										'state'=>(value),
										'zip_code'=>(value),
										'country_code'=>(value),
										'country'=>(value),
										'whatsapp'=>(value),
										'telegram'=>(value),
										'signal'=>(value),
										'messenger'=>(value),
										'user_action_log'=>(value),
										'timezone_id'=>(value),
										'languates'=>(value),
										'date_created'=>(value),
										'date_updated'=>(value));

	if no data is found, the returned array will look like this:
		$returnArray			= array('result'=>FALSE);
	
			
	Example of a call to get data for a user (the dataArray can be in any order):
		$dataArray			= array('callsign'=>$user_login,
									'action'=>'get',
									'debugging'=> $debugging,
									'testing'=> $testMode);
		$dataResult				= user_master_data($dataArray);
		if ($dataResult['result'] === TRUE) {
			unpack the data
		} else {
			do the no data action
		}

	If the action is 'update', the updateArray is processed and any data found will 
	be updated. Only fields to be updated should be included in the updateArray
	
	If the update suceeds, the returnArray will look like this:
		$returnArray				= array('result'=>TRUE);
		
	If the update fails, the return will look like this:
		$returnArray				= array('result'=>FALSE,
											 'reason'=>(any iformation about the reason for the failure));
		
	
	Example of a call to update data for a user (the data can be in any order)
		$dataArray			= array('callsign'=>$user_login,
									'action'=>'update',
									'first_name'=>(value),
									'messenger'=>(value),
									'debugging'=> $debugging,
									'testing'=> $testMode);
		$dataResult				= user_master_data($dataArray);
		if ($dataResult['result'] === TRUE) {
			unpack the data
		} else {
			$failureReason		= $dataResult['reason'];
		}
	
	If the action is 'delete' then the data is removed from wpw1_cwa_user_master and 
	written to 'wpw1_cwa_deleted_user_master
	
	To call for a delete:
		$dataArray			= array('callsign'=>$user_login,
									'action'=>'delete',
									'debugging'=> $debugging,
									'testing'=> $testMode);
		$dataResult				= $user_master_data($dataArray);
		foreach($dataResult as $thisField => $thisValue) {
			$$thisField		= $thisValue;
		}
		if ($result == FALSE) {
			do actions if deletion failed. Field 'reason' has the reason info
		} else {	
			Go on with the program
		}
*/

	global $wpdb;	
	
	$initializationArray 			= data_initialization_func();
	$userName						= $initializationArray['userName'];

	$action			= "";
	$debugging		= FALSE;
	$testing		= FALSE;
	$id				= "";
	$callsign		= "";
	$first_name		= "";
	$last_name		= "";
	$email			= "";
	$phone			= "";
	$ph_code		= "";
	$city			= "";
	$state			= "";
	$zip_code		= "";
	$country_code	= "";
	$country		= "";
	$whatsapp		= "";
	$telegram		= "";
	$signal			= "";
	$messenger		= "";
	$user_action_log	= "";
	$timezone_id	= "";
	$languages		= "";
	$date_created	= "";
	$date_updated	= "";
	

	// unpack the dataArray
	if (count($dataArray) == 0) {
		$returnArray		= array('result'=>FALSE,
									'reason'=>'dataArray is empty');
		return $returnArray;
	}
	
	foreach($dataArray as $thisField=>$thisValue) {
		$$thisField		= $thisValue;
	}
	if ($debugging) {
		echo "<br /><b>FUNCTION User Master Data</b><br />
			  dataArray:<br /><pre>";
		print_r($dataArray);
		echo "</pre><br />";
	}	
	
	// set up the tables
	if ($testing) {
		if ($debugging) {
			echo "<p><strong>Operating in Test Mode.</strong></p>";
		}
		$userTableName				= "wpw1_users";
		$userMetaTableName			= "wpw1_usermeta2";
		$userMasterTableName		= "wpw1_cwa_user_master2";
		$countryCodesTableName		= "wpw1_cwa_country_codes";
		$userDeletedTableName		= "wpw1_cwa_deleted_user_master2";
	} else {
		$userTableName				= "wpw1_users";
		$userMetaTableName			= "wpw1_usermeta";
		$userMasterTableName		= "wpw1_cwa_user_master";
		$countryCodesTableName		= "wpw1_cwa_country_codes";
		$userDeletedTableName		= "wpw1_cwa_deleted_user_master";
	}
	

	if ($action == 'get') {
		if ($callsign == '') {
			if ($debugging) {
				echo "callsign is empty<br />";
			}
			$returnArray		= array('result'=>FALSE,
										'reason'=>"callsign is missing");
			return $returnArray;
		}
		// read user_master. If record found, populate returnArray and return
		
		$sql			= "select * from $userMasterTableName 
							where call_sign = '$callsign'";
		$sqlResult		= $wpdb->get_results($sql);
		if ($sqlResult === FALSE) {
			handleWPDBError("FUNCTION User Master Data",$debugging);
			$returnArray		= array('result'=>FALSE,
										'reason'=>"reading $userMasterTableName for $callsign failed");
			return $returnArray;
		} else {
			$numRows	= $wpdb->num_rows;
			if ($debugging) {
				echo "ran $sql<br />and retrieved $numRows rows<br />";
			}
			if ($numRows > 0) {
				foreach($sqlResult as $sqlRow) {
					$id				= $sqlRow->ID;
					$callsign		= $sqlRow->call_sign;
					$first_name		= $sqlRow->first_name;
					$last_name		= $sqlRow->last_name;
					$email			= $sqlRow->email;
					$phone			= $sqlRow->phone;
					$city			= $sqlRow->city;
					$state			= $sqlRow->state;
					$zip_code		= $sqlRow->zip_code;
					$country_code	= $sqlRow->country_code;
					$whatsapp		= $sqlRow->whatsapp_app;
					$telegram		= $sqlRow->telegram_app;
					$signal			= $sqlRow->signal_app;
					$messenger		= $sqlRow->messenger_app;
					$user_action_log	= $sqlRow->user_action_log;
					$timezone_id	= $sqlRow->timezone_id;
					$languages		= $sqlRow->languages;
					$date_created	= $sqlRow->date_created;
					$date_updated	= $sqlRow->date_updated;

					$countrySQL		= "select * from $countryCodesTableName 
										where country_code = '$country_code'";
					$countrySQLResult	= $wpdb->get_results($countrySQL);
					if ($countrySQLResult === FALSE) {
						handleWPDBError("FUNCTION User Master Data",$debugging);
						$country		= "UNKNOWN";
						$ph_code		= "";
					} else {
						$numCRows		= $wpdb->num_rows;
						if ($debugging) {
							echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
						}
						if($numRows > 0) {
							foreach($countrySQLResult as $countryRow) {
								$country		= $countryRow->country_name;
								$ph_code		= $countryRow->ph_code;
							}
						} else {
							$country			= "Unknown";
							$ph_code			= "";
						}
					}
					
				}
				/// have all the available data
				if ($debugging) {
					echo "have all available data. Returning<br /><br />";
				}
				$returnArray			= array('result'=>TRUE,
												'id'=>$id,
												'callsign'=>$callsign,
												'first_name'=>$first_name,
												'last_name'=>$last_name,
												'email'=>$email,
												'phone'=>$phone,
												'ph_code'=>$ph_code,
												'city'=>$city,
												'state'=>$state,
												'zip_code'=>$zip_code,
												'country_code'=>$country_code,
												'country'=>$country,
												'whatsapp'=>$whatsapp,
												'telegram'=>$telegram,
												'signal'=>$signal,
												'messenger'=>$messenger,
												'user_action_log'=>$user_action_log,
												'timezone_id'=>$timezone_id,
												'languages'=>$languages,
												'date_created'=>$date_created,
												'date_updated'=>$date_updated);
				return $returnArray;


			} else {			// no user_master record. Look in userMeta
								// if the record is found there, create the user_master 
								// record and delete the userMeta records
				if ($debugging) {
					echo "no user_master record for $callsign. Looking in userMeta<br />";
				}
				$verifiedUser	= TRUE;
				$id				= "";
				$first_name		= "";
				$last_name		= "";
				$email			= "";
				$phone			= "";
				$ph_code		= "";
				$city			= "";
				$state			= "";
				$zip_code		= "";
				$country_code	= "";
				$country		= "";
				$whatsapp		= "";
				$telegram		= "";
				$signal			= "";
				$messenger		= "";
				$user_action_log	= "";
				$timezone_id	= "";
				$languages		= "";

				$userSQL			= "select * from $userTableName 
										where user_login like '$callsign'";
				$userResult		= $wpdb->get_results($userSQL);
				if ($userResult === FALSE) {
				 	handleWPDBError("FUNCTION User Master Data",$debugging);
				 	if ($debugging) {
				 		echo "running $userSQL returned a result of FALSE<br />";
				 	}
				 	$returnArray			= array('result'=>FALSE,
				 									'reason'=>"attempting to query $userTableName returned FALSE");
				 	return $returnArray;
				} else {
					$numURows	= $wpdb->num_rows;
					if ($debugging) {
						echo "ran $userSQL<br />and retrieved $numURows rows<br />";
					}
					if ($numURows > 0) {			// have a users record. Get the ID
						foreach($userResult as $userRow) {
							$thisID			= $userRow->ID;
							$email			= $userRow->user_email;
							
							if ($debugging) {
								echo "have a ID of $thisID for $callsign<br />";
							}
							
							// first determine if this is a verified user
							$verifiedData	= $wpdb->get_var("select meta_value 
												from $userMetaTableName 
												where user_id = $thisID 
												and meta_key = 'wumuv_needs_verification'");
							if ($debugging) {
								$lastQuery	= $wpdb->last_query;
								echo "ran $lastQuery. verifiedData:<br ><pre>";
								print_r($verifiedData);
								echo "</pre><br />";
							}
							if ($verifiedData == NULL) {
								if ($debugging) {
									echo "verifiedData is NULL<br />";
								}
							} elseif ($verifiedData == "1") {
								$verifiedUser	= FALSE;
								if ($debugging) {
									echo "verifiedData is 1<br />";
								}
							}
							// if user not verified, return no record found
							if (!$verifiedUser) {
								if ($debugging) {
									echo "unverified user, so no record for this callsign<br />";
								}
								$returnArray			= array('result'=>FALSE,
																'reason'=>"unverified user");
								return $returnArray;
							} else {
								// now with this ID, get the data from userMeta
								$metaSQL		= "select * from $userMetaTableName 
													where user_id = $thisID";
								$metaResult		= $wpdb->get_results($metaSQL);
								if ($metaResult === FALSE) {
									handleWPDBError("FUNCTION User Master Data",$debugging);
									if ($debugging) {
										echo "running $metaSQL returned a result of FALSE<br />";
									}
									$returnArray			= array('result'=>FALSE,
																	'reason'=>"attempting to read $userMetaTableName returned FALSE");
									return $returnArray;
								} else {
									$numMRows	= $wpdb->num_rows;
									if ($debugging) {
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
													$last_name		= $this_meta_value;
													break;
												case "first_name" :
													$first_name		= $this_meta_value;
													break;
												case "wpum_field_18" :
													$city			= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												case "wpum_field_19" :
													$state			= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												case "wpum_field_15" :
													$country_code	= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												case "wpum_field_24" :
													$zip_code		= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												case "wpum_field_14" :
													$phone			= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												case "wpum_field_20" :
													$whatsapp		= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												case "wpum_field_21" :
													$telegram		= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												case "wpum_field_22" :
													$signal			= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												case "wpum_field_23" :
													$messenger		= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												case "wpum_field_27" :
													$languages		= $this_meta_value;
													$deleteThisRow	= TRUE;
													break;
												default :
													$deleteThisRow	= FALSE;
											}
											if ($deleteThisRow) {
												$metaDelete			= $wpdb->delete($userMetaTableName,
																				array('umeta_id'=>$this_umeta_id),
																				array('%d'));
												if ($metaDelete === FALSE) {
													handleWPDBError("FUNCTION User Master Data",$debugging);
													if ($debugging) {
														echo "attempting to delete umeta_id $this_umeta_id returned a result of FALSE<br />";
													}
												} else {
													if ($debugging) {
														echo "deleted $userMetaTableName record for umeta_id of $this_umeta_id<br />";
													}
												}
											}
										}
										
										// figure out the timezone_id
										if ($debugging) {
											echo "figuring out the timezone_id. Country_code: $country_code<br />";
										}
										// if the country code is US get the timezone info from the zipcode
										if ($country_code == 'US') {
											if ($debugging) {
												echo "have a country code of US, verifying the zip code<br />";
											}
											$zipResult		= getOffsetFromZipCode($zip_code,'',TRUE,$testing,$debugging);
											if ($zipResult[0] == 'NOK') {
												$timezone_id		="XX";
											} else {
												$timezone_id		= $zipResult[1];
											}
										} else {		// country code not US. Figure out the timezone and offset
											if ($debugging) {
												echo "dealing with a non-us country of $country_code<br />";
											}
											$timezone_identifiers 		= DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country_code );
											$myInt						= count($timezone_identifiers);
											if ($debugging) {
												echo "found $myInt identifiers for country code $country_code";
											}
											
											if ($myInt == 1) {									//  only 1 found. Use that and continue
												$timezone_id		= $timezone_identifiers[0];
											} else {
												$timezoneSelector			= array();		// localDateTime => $myCity
												$ii							= 1;
												if ($debugging) {
													echo "have the list of identifiers for $inp_country_code<br />";
												}
												$oneChecked				= FALSE;
												foreach ($timezone_identifiers as $thisID) {
													if ($debugging) {
														echo "Processing $thisID<br />";
													}
													$selector			= "";
													if ($browser_timezone_id == $thisID) {
														$selector		= "checked";
														$oneChecked		= TRUE;
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
													$timezone_id			= 'XX';
												}
												if (count($timezoneSelector) == 1) {
													foreach($timezoneSelector as $thisDateTime => $thisID);
														$timezone_id			= $thisID;
												}
												if (count($timezoneSelector) > 1) {
													$timezone_id			= "XX";
												}
											}
										}
										
										
										// have all the data. Insert the record into user_master
										$thisDate			= date('Y-m-d H:i:s');
										$user_action_log	= "/ $thisDate $userName Record created ";
										$userInsert			= $wpdb->insert($userMasterTableName,
																			array('call_sign'=>$callsign,
																				  'first_name'=>$first_name,
																				  'last_name'=>$last_name,
																				  'email'=>$email,
																				  'phone'=>$phone,
																				  'city'=>$city,
																				  'state'=>$state,
																				  'zip_code'=>$zip_code,
																				  'country_code'=>$country_code,
																				  'whatsapp_app'=>$whatsapp,
																				  'telegram_app'=>$telegram,
																				  'signal_app'=>$signal,
																				  'messenger_app'=>$messenger,
																				  'user_action_log'=>$user_action_log,
																				  'timezone_id'=>$timezone_id,
																				  'languages'=>$languages ),
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
																					'%s'));
										if ($userInsert === FALSE) {
											handleWPDBError("FUNCTION User Master Data",$debugging);
											if ($debugging) {
												echo "attempting to insert data for $callsign into $userMasterTableName returned FALSE<br />";
											}
											$returnArray				= array('result'=>FALSE,
																				'reason'=>"unable to insert $callsign info into 4userMasterTableName table");
											return $returnArray;
										} else {
											$id			= $wpdb->insert_id;
											if ($debugging) {
												echo "user_master record added for $callsign at id $id<br />";
											}
											// get the country and ph_code
											$countrySQL		= "select * from $countryCodesTableName 
																where country_code = '$country_code'";
											$countrySQLResult	= $wpdb->get_results($countrySQL);
											if ($countrySQLResult === FALSE) {
												handleWPDBError("FUNCTION User Master Data",$debugging);
												$country		= "UNKNOWN";
												$ph_code		= "";
											} else {
												$numCRows		= $wpdb->num_rows;
												if ($debugging) {
													echo "ran $countrySQL<br />and retrieved $numCRows rows<br />";
												}
												if($numCRows > 0) {
													foreach($countrySQLResult as $countryRow) {
														$country		= $countryRow->country_name;
														$ph_code		= $countryRow->ph_code;
													}
												} else {
													$country			= "Unknown";
													$ph_code			= "";
												}
											}
	
											// now return the data
											$myDate					= date('Y-m-d H:i:s');
											$returnArray			= array('result'=>TRUE,
																			'id'=>$id,
																			'callsign'=>$callsign,
																			'first_name'=>$first_name,
																			'last_name'=>$last_name,
																			'email'=>$email,
																			'phone'=>$phone,
																			'ph_code'=>$ph_code,
																			'city'=>$city,
																			'state'=>$state,
																			'zip_code'=>$zip_code,
																			'country_code'=>$country_code,
																			'country'=>$country,
																			'whatsapp'=>$whatsapp,
																			'telegram'=>$telegram,
																			'signal'=>$signal,
																			'messenger'=>$messenger,
																			'user_action_log'=>$user_action_log,
																			'languages'=>$languages,
																			'timezone_id'=>$timezone_id,
																			'date_created'=>$myDate,
																			'date_updated'=>$myDate);
											if ($debugging) {
												echo "have all the data. Returning<br /><br />";
											}
											return $returnArray;
										}
									} else {
										if ($debugging) {
											echo "no userMeta records for $callsign<br />";
										}
										$returnArray			= array('result'=>FALSE,
																		'reason'=>"no records found for $callsign in $userMetaTableName table");
										return $returnArray;
									}
								}
							}
						}
					} else {
						if ($debugging) {
							echo "no userMeta record, so no record for this callsign<br />";
						}
						$returnArray			= array('result'=>FALSE,
														'reason'=>"no record for $callsign");
						return $returnArray;
					}
				 }				
			}
		}
		
		
	} elseif ($action == 'update') {
		// unpack the input data
		$id				= "";
		$callsign		= "";
		$first_name		= "";
		$last_name		= "";
		$email			= "";
		$phone			= "";
		$ph_code		= "";
		$city			= "";
		$state			= "";
		$zip_code		= "";
		$country_code	= "";
		$country		= "";
		$whatsapp		= "";
		$telegram		= "";
		$signal			= "";
		$messenger		= "";
		$user_action_log	= "";
		$languages		= "";
		$timezone_id	= "";
		
		$have_callsign	= FALSE;
		$updateParams	= array();
		$updateFormat	= array();
		
		foreach($dataArray as $thisField => $thisValue) {
			if ($debugging) {
				echo "field: $thisField; value: $thisValue<br />";
			}
			switch ($thisField) {
				case "callsign":
					$callsign		= $thisValue;
					$have_calsign	= TRUE;
					break;
				case "last_name":
					$last_name		= $thisValue;
					$updateParams['last_name']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "first_name":
					$first_name		= $thisValue;
					$updateParams['first_name']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "email":
					$email		= $thisValue;
					$updateParams['email']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "phone":
					$phone		= $thisValue;
					$updateParams['phone']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "city":
					$city		= $thisValue;
					$updateParams['city']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "state":
					$state		= $thisValue;
					$updateParams['state']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "zip_code":
					$zip_code		= $thisValue;
					$updateParams['zip_code']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "country_code":
					$country_code		= $thisValue;
					$updateParams['country_code']	= $thisValue;
					$updateFormat[]	= "%s";
					break;			
				case "whatsapp":
					$whatsapp		= $thisValue;
					$updateParams['whatsapp_app']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "telegram":
					$telegram		= $thisValue;
					$updateParams['telegram_app']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "signal":
					$signal		= $thisValue;
					$updateParams['signal_app']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "messenger":
					$messenger		= $thisValue;
					$updateParams['messenger_app']	= $thisValue;
					$updateFormat[]	= "%s";
					break;
				case "user_action_log":
					$user_action_log	= $thisValue;
					$updateParams['user_action_log']	= $thisValue;
					$updateFormat[]						= '%s';
					break;
				case "languages":
					$languages	= $thisValue;
					$updateParams['languages']	= $thisValue;
					$updateFormat[]						= '%s';
					break;
				case "timezone_id":
					$timezone_id	= $thisValue;
					$updateParams['timezone_id']	= $thisValue;
					$updateFormat[]						= '%s';
					break;
				case "action":
					$doingNothin	= TRUE;
					break;
				case "debugging":
					$doingNothin	= TRUE;
					break;
				case "testing":
					$doingNothin	= TRUE;
					break;
				case "id":
					$doingNothin	= TRUE;
					break;
				default:
					if ($debugging) {
						echo "have an unknown field: $thisField value: $thisValue<br />";
					}
			}
		}
		if ($debugging) {
			echo "<br />updateParams:<br /><pre>";
			print_r($updateParams);
			echo "</pre><br />";
		}
		// if there are updates, do the update
		if (count($updateParams) > 0) {
			$updateResult		= $wpdb->update($userMasterTableName, 
											$updateParams,
											array('call_sign'=>$callsign),
											$updateFormat,
											array('%s'));
			if ($updateResult === FALSE) {
				handleWPDBError("FUNCTION User Master Data",$debugging);
				if ($debugging) {
					echo "attempting to update $userMasterTableName for $callsign returned FALSE<br />";
				}
				$returnArray			= array('result'=>FALSE,
												'reason'=>"tempting to update $userMasterTableName for $callsign returned FALSE");
				return $returnArray;
			} else {
				if ($debugging) {
					echo "successfully updated $userMasterTableName table for $callsign<br />";
				}
				$returnArray			= array('result'=>TRUE);
				return $returnArray;
			
			}
		}
		
	} elseif ($action == 'delete') {
		if ($debugging) {
			echo "<br />performing delete action for callsign $callsign<br />";
		}
		
		// get the action log from the current user_master record and update it
		$thisActionLog			= $wpdb->get_var("select user_action_log from $userMasterTableName 
											where call_sign = '$callsign'");
		if ($thisActionLog === NULL) {		/// no data was returned
			handleWPDBError("FUNCTION: User Master Data",$debugging);
			$returnArray			= array('result'=>FALSE,
											'reason'=>"trying to get action log for $callsign returned NULL");
			return $returnArray;
		}									
		// have the action log. Update it
		$thisDate		= date('Y-m-d H:i:s');
		$thisActionLog	.= " / $thisDate record was deleted by $userName ";
		$updateResult	= $wpdb->update($userMasterTableName, 
										array('user_action_log'=>$thisActionLog),
										array('call_sign'=>$callsign),
										array('%s'),
										array('%s'));
		if ($updateResult === FALSE) {
			handleWPDBError("FUNCTION: User Master Data",$debugging);
			$returnArray	= array('result'=>FALSE,
									'reason'=>"updating user_action_log in $userMasterTableName table for $callsign failed");
		}
		if ($debugging) {
			echo "updated user_action_log to say who did the delete and when<br />";
		}		
		$sql		= "insert into $userDeletedTableName select * from $userMasterTableName where call_sign = '$callsign'";
		$myResult	= $wpdb->get_results($sql);
		if ($myResult === FALSE) {
			$thisLastError			= $wpdb->last_error;
			if ($debugging) {
				echo "adding $advisor_call_sign to $advisorNewDeletedTableName table<br />";
				echo "wpdb->last_query: " . $wpdb->last_query . "<br />";
				echo "wpdb->last_error: $thisLastError<br />";
			}
			handleWPDBError("FUNCTION: User Master Data",$debugging);
			$returnArray			= array('result'=>FALSE,
											'reason'=>$thisLastError);
			return $returnArray;
		} else {
			if ($debugging) {
				echo "moving the data was successful. Deleting user_master record<br />";
			}
			$deleteResult	= $wpdb->delete($userMasterTableName,array('call_sign'=>$callsign),array('%s'));
			if ($deleteResult === FALSE) {
				handleWPDBError("FUNCTION: User Master Data",$debugging);
			} else {
				if ($debugging) {
					echo "deletion from $userMasterTableName table was successful<br />";
				}
				
				$returnArray		= array('result'=>TRUE);
				return $returnArray;
			}
		}

	} else {
		if ($debugging) {
			echo "action of $action is invalid<br />";
		}
		$returnArray		= array('result'=>FALSE,
									'reason'=>"invalid action value: $action");
		return $returnArray;
	}

}
add_action('user_master_data','user_master_data');