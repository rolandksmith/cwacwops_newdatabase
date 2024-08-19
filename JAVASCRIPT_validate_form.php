add_action( 'wp_head', function () { ?>
<script Language='JavaScript' type='text/javascript'>
	function validate_form() {
	
		function onlyNumbers(str) {
		  return /^[0-9]+$/.test(str);
		}
	
		var errortext = '';
		var errorcount = 0;
		var myInt = 0;

//		window.alert('arrived at validate_form');
		console.log('arrived at validate_form');

	    if(document.getElementById('chk_callsign')) {
	        var myCallsign = document.getElementById('chk_callsign').value;
	        if (myCallsign == '') {
	        	errortext += 'Call Sign is a required field\n';
	        	errorcount++;
	        }
	        if (myCallsign.indexOf(' ') != -1) {
	        	errortext += 'Call Sign cannot contain a space. If you are using your surname, use only the last part of your surname without a space\n';
	        	errorcount++;
	        }
	        if (myCallsign.indexOf('/') != -1) {
	        	errortext += 'Call Sign must be your base call sign. Do not include a slash\n';
	        	errorcount++;
	        }
	        if (myCallsign.indexOf('-') != -1) {
	        	errortext += 'Call Sign can not contain a hyphen. If you are using your surname, either remove the hyphen or use one part of your surname\n';
	        	errorcount++;
	        }
		}	
	    if(document.getElementById('chk_email')) {
	        var myEmail = document.getElementById('chk_email').value;
	        if (myEmail == '') {
		        errortext += 'Email is a required field\n';
	        	errorcount++;
	        }
		}		
	    if(document.getElementById('chk_phone')) {
	        var myPhone = document.getElementById('chk_phone').value;
	        if (myPhone == '') {
	        	errortext += 'Phone is a required field\n';
	        	errorcount++;
	        } else {
	        	if (!onlyNumbers(myPhone)) {
	        		errortext += 'Phone number must be numbers only. No other characters.\n';
	        		errorcount++;
	        	}
	        	if (myPhone.length < 4) {
	        		errortext += 'Phone number must be at least 4 digits.\n';
	        		errorcount++;
	        	}
	        }
		}		

	    if(document.getElementById('chk_level')) {
			var getLevelValue = document.querySelector("input[name='inp_level']:checked");   
			if(getLevelValue == null) {   
				errortext += 'Level is a required field\n';
				errorcount++;
			}
		}

	    if(document.getElementById('chk_semester')) {
			var getSemesterValue = document.querySelector("input[name='inp_semester']:checked");   
			if(getSemesterValue == null) {   
				errortext += 'Semester is a required field\n';
				errorcount++;
			}  
		}

	    if(document.getElementById('chk_lastname')) {
	        var myLastname = document.getElementById('chk_lastname').value;
	        if (myLastname == '') {
		        errortext += 'Lastname is a required field\n';
	        	errorcount++;
	        }
		}		

	    if(document.getElementById('chk_firstname')) {
	        var myFirstname = document.getElementById('chk_firstname').value;
	        if (myFirstname == '') {
		        errortext += 'Firstname is a required field\n';
	        	errorcount++;
	        }
		}		

	    if(document.getElementById('chk_city')) {
	        var myCity = document.getElementById('chk_city').value;
	        if (myCity == '') {
		        errortext += 'City is a required field\n';
	        	errorcount++;
	        }
		}		

		var myCountryCode = '';
		var myCountry = '';
		var gotCountry = false;
		var needCountry = false;
		var gotList1Country = false;
		var gotList2Country = false;
	    if(document.getElementById('chk_countrya')) {
			needCountry = true;
			var getCountryValue = document.querySelector("input[name='inp_countrya']:checked");
	  		if (getCountryValue !== null) {
				var myCountry = getCountryValue.value;
				if (myCountry != '') {
					myCountryCode = myCountry.slice(0,2);
					if (myCountryCode != 'XX') {
						gotCountry = true;	
						gotList1Country = true;
					}
				}
//			} else {
//				alert('getCountryValue was null\n');
			}

			if (!gotCountry) {
				try {
					if(document.getElementById('chk_countryb')) {
						var getCountryb = document.getElementById('chk_countryb');
						var getCountrybValue = getCountryb.options[getCountryb.selectedIndex].value;
						if(getCountrybValue !== null) {
							myCountryCode = getCountrybValue.slice(0,2);
							if (myCountryCode != 'XX') {
								myCountry = getCountrybValue.slice(3,50);
								gotCountry = true;
								gotList2Country = true;
							} else {
								errortext	+= 'A country must be selected\n';
								$errorcount++;
							}
//						} else {
//							alert('getCountryb was null\n');
						}
					}
				} catch (e) {
					errortext += 'Country from the selection list is undefined. No country selected.\n';
					errorcount++;
				}
			}
			if (gotList2Country) {
				return confirm('You selected ' + myCountry + ' from the list of Other Countries. Is this your country? If not, then cancel and correct your country selection. To cancel any selection in the list, click on No Selection at the top of the list.');
		    }
		}
				
		if (needCountry) {
			if (!gotCountry ) {
				errortext += 'Country is required. Please select a country.\n';
				errorcount++;
			}
		}
		
		if(myCountryCode == 'US' || myCountryCode == 'CA') {
			if (document.getElementById('chk_zip')) {
				var myZipCode = document.getElementById('chk_zip').value;
				if (myZipCode == '') {
					errortext += 'Zip/Postal Code is required for US and Canadian Residents\n';
					errorcount++;
				}
			}
		}

	    if(document.getElementById('chk_days')) {
			var getDaysValue = document.querySelector("input[name='inp_days']:checked").value;   
			if(getDaysValue == null || getDaysValue == '') {   
				errortext += 'Class Teaching Days is a required field\n';
				errorcount++;
			}
		}

	    if(document.getElementById('chk_times')) {
			var getTimesValue = document.querySelector("input[name='inp_times']:checked").value;   
			if(getTimesValue == null || getTimesValue == '') {   
				errortext += 'Class Start Time is a required field\n';
				errorcount++;
			}  
		}

		var mySked1 = '';
		var mySked2 = '';
		var mySked3 = '';
		var gotSked1 = false;
		var gotSked2 = false;
		var gotSked3 = false;
		var needSked1 = false;
		var gotFlex = false;
	    if(document.getElementById('chk_sked1')) {
	    	needSked1 = true;
	    	console.log('needSked1 is true');
			mySked1 = document.querySelector('input[name="inp_sked1"]:checked').value;
			console.log(mySked1);
			if (mySked1 == 'None') {
				gotSked1 = false;
			} else {
				console.log('mySked is not None');
				gotSked1 = true;
				console.log(gotSked1);
			}

			mySked2 = document.querySelector('input[name="inp_sked2"]:checked').value;
			if (mySked2 == 'None') {
				gotSked2 = false;
				console.log('gotSked2 is None');
			} else {
				gotSked2 = true;
				console.log(mySked2);
			}

			mySked3 = document.querySelector('input[name="inp_sked3"]:checked').value;
			if (mySked3 == 'None') {
				gotSked3 = false;
				console.log('gotSked3 is None');
			} else {
				gotSked3 = true;
				console.log(mySked3);
			}


			if(needSked1) {
				if (!gotSked1) {
					errortext += 'You must select a 1st choice preference.\n';
					errorcount++;
					console.log('You must select a 1st choice preference');
				} else {
					if (gotSked2) {
						if (mySked2 == mySked1 || mySked2 == mySked3) {
							errortext += 'Second Preference must be different than First and Third Preferences.\n';
							errorcount++;
							console.log('Second Preference must be different than First and Third Preferences');
						}
					}
					if (gotSked3) {
						if (mySked3 == mySked1 || mySked3 == mySked2) {
							errortext += 'Third Preference must be different than First and Second Preferences.\n';
							errorcount++;
							console.log('Third Preference must be different than First and Second Preferences');
						}
					}
				}
			} else {
				console.log("needSked1 is true and gotFlex is true");
			}
//			return false;
		}

		// checks for the advisor verification of student
	    if(document.getElementById('chk_attend')) {
			var getAttendValue = document.querySelector("input[name='inp_attend']:checked").value;
			
//			alert('The value of inp_attend is ' + getAttendValue +'\n');
			
			if (getAttendValue == 'schedule') {
				var myScheduleInfo = '';
				if (document.getElementById('inp_comment_attend')) {
					myScheduleInfo += 'have id inp_comment_attend\n';
			        var myScheduleComment = document.getElementById('inp_comment_attend').value;
			        myScheduleInfo += 'value of inp_comment_attend is ' + myScheduleComment + '\n';
			        if (myScheduleComment == '') {
			        	errortext += 'Comments regarding the student\'s schedule issue are required.\n';
			        	errorcount++;
			        }
				} else {
					errortext += 'Comments regarding the student\'s schedule issue are required.\n';
					errorcount++;
				}
//				alert ('myScheduleInfo:\n' + myScheduleInfo);
			}
			if (getAttendValue == 'other') {
				var myOtherInfo = '';
				if (document.getElementById('inp_comment')) {
					myOtherInfo += 'have id inp_comment\n';
			        var myOtherComment = document.getElementById('inp_comment').value;
			        myOtherInfo += 'value of inp_comment is ' + myOtherComment + '\n';
			        if (myOtherComment == '') {
			        	errortext += 'Comments regarding the Other issue are required.\n';
			        	errorcount++;
			        }
				} else {
					errortext += 'Comments regarding the Other issue are required.\n';
					errorcount++;
				}
//				alert ('myOtherInfo:\n' + myOtherInfo);
			}
		}		

	    if(document.getElementById('chk_timezone_id')) {
			myTimezoneID = document.querySelector('input[name="inp_timezone_id"]:checked').value;
			console.log(myTimezoneID);
			if (myTimezoneID == 'None') {
				errortext += 'Please select a Timezone ID.\n';
				errorcount++;
			} else {
				console.log('got a timezone id');
			}
		}

			


	    if (errorcount > 0) {
	    	alert ('The following errors need to be corrected\n' + errortext);
	    	return false;
	    } else {
			// no errors,
		    return true;
		}
//		return true;
		
	}

</script>
<?php } );
