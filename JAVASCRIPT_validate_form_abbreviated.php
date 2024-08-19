add_action( 'wp_head', function () { ?>
<script Language='JavaScript' type='text/javascript'>
	function validate_form() {
	

		window.alert('arrived at validate_form');


		var mySked1 = '';
		var gotSked1 = false;
		var needSked1 = false;
		var gotFlex = false;
	    if(document.getElementById('chk_sked1')) {
			if (document.getElementById('chk_sked1').checked == true) {
				var mySked1 = document.querySelector('input[name="inp_sked1"]:checked').value;
				console.log(mySked1)
			}
			return false;
		}
		
	}

</script>
<?php } );
