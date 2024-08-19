function user_master_data($dataArray=array()) {

/*	Input data array: 	
			callsign			the user_login callsign
			action				either 'get' or 'update'
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
										'date_created'=>(value),
										'date_updated'=>(value));

	if no data is found, the returned array will look like this:
		$returnArray			= array('result'=>FALSE);
	
			
	Example of a call to get data for a user (the dataArray can be in any order):
		$dataArray			= array('callsign'=>$user_login,
									'action'=>'get',
									'debugging'=> $doDebug,
									'testing'=> $testMode);
		$dataResult				= $user_master_data($dataArray);
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
									'debugging'=> $doDebug,
									'testing'=> $testMode);
		$dataResult				= $user_master_data($dataArray);
		if ($dataResult['result'] === TRUE) {
			unpack the data
		} else {
			$failureReason		= $dataResult['reason'];
		}
	
*/

	global $wpdb;

	$action			= "";
	$debugging		= FALSE;
	$testing		= FALSE;
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
												'date_created'=>$date_created,
												'date_updated'=>$date_updated);
				return $returnArray;


			} else {			// no user_master record. Look in userMeta
								// if the record is found there, create the user_master 
								// record and delete the userMeta records
				if ($debugging) {
					echo "no user_master record for $callsign. Looking in userMeta<br />";
				}
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
											default :
												$deleteTHisRow	= FALSE;
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
									// have all the data. Insert the record into user_master
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
																			  'messenger_app'=>$messenger ),
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
																				'%s'));
									if ($userInsert === FALSE) {
										handleWPDBError("FUNCTION User Master Data",$debugging);
										if (debugging) {
											echo "attempting to insert data for $callsign into $userMasterTableName returned FALSE<br />";
										}
										$returnArray				= array('result'=>FALSE,
																			'reason'=>"unable to insert $callsign info into 4userMasterTableName table");
										return $returnArray;
									} else {
										if ($debugging) {
											echo "user_master record added for $callsign<br />";
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
				case "action":
					$doingNothin	= TRUE;
					break;
				case "debugging":
					$doingNothin	= TRUE;
					break;
				case "testing":
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