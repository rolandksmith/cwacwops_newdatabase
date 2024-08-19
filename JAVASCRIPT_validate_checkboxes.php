add_action( 'wp_head', function () { ?>
<script Language='JavaScript' type='text/javascript'>
    function validate_checkboxes() {
    
//    alert('validate_checkboxes running');
    
      var checkboxes = document.querySelectorAll('input[name="inp_sked_times[]"]:checked');

      if (checkboxes.length > 0) {
//        alert('At least one checkbox is checked.');
        return true;
      } else {
        alert('At least one class schedule must be selected\n');
        return false;
      }
    }
 </script>
<?php } );
