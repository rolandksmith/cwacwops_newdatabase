add_action( 'wp_head', function () { ?>
<script Language='JavaScript' type='text/javascript'>

jQuery('#wpum-submit-registration-form input[name=username]').bind('blur input keyup propertychange', function(e) {
  var el = jQuery(this);
  var value = el.val();

  if (value && !(/^[a-zA-Z0-9]{1,3}[0-9][a-zA-Z0-9]{0,3}[a-zA-Z]+$/).test(value)) {
    el.css({
      border: '1px solid #900',
      backgroundColor: '#FFDDDD'
    });

    jQuery('#wpum-submit-registration-form > input[type=submit]')
      .css({ backgroundColor: '#FFDDDD', color: '#FF9999' });

    window.isValidated = false;
  }
  else {
    el.css({
      border: '1px solid #bbb',
      backgroundColor: '#FFFFFF'
    });

    jQuery('#wpum-submit-registration-form > input[type=submit]')
      .css({ backgroundColor: '#000000', color: '#FFFFFF' });

    window.isValidated = true;
  }
});

jQuery('#wpum-submit-registration-form').submit(function(e) {
  if (!window.isValidated) {
    alert('Invalid Callsign');
    e.preventDefault();
  }
}); 
</script>
<?php } );
