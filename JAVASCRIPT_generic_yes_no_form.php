add_action( 'wp_head', function () { ?>
<script Language='JavaScript' type='text/javascript'>

function generic_yes_no_form(formId, message) {

//	invoke: <input class='formInputButton' type='submit' onclick=\"return return_yes_no_form(this.form);\" name='' />

//		window.alert('arrived at generic_yes_no_form');
		console.log('arrived at generic_yes_no_form');

    // Find the form using its ID
    var form = document.getElementById(formId);
    if (!form) {
        console.log("Form with ID " + formId + " not found.");
        return false;
    }

    // Display the confirmation box with the custom message
    var userDecision = confirm(message);

    // Find the hidden input field to store the decision
    var decisionInput = form.querySelector('input[name="decision"]');

    if (decisionInput) {
        if (userDecision) {
            // User clicked 'OK'
            console.log('set decision to yes');
            decisionInput.value = 'yes';
        } else {
            // User clicked 'Cancel'
            console.log('set decision to no');
            decisionInput.value = 'no';
        }
        
        // Explicitly submit the form. This is the key change.
        form.submit();
        console.log('form has been submitted');
    } else {
        console.error("Hidden input 'decision' not found in form.");
    }
    
    // Always return false to prevent the default form submission behavior.
    // We already submitted the form ourselves.
    console.log('returning false');
    return false;
}
</script>
<?php } );
