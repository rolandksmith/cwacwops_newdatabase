add_action( 'wp_head', function () { ?>
<script>

jQuery.ready.then(function($) {
  var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
 $('input[name=timezone]').val(timezone);
})

</script>
<?php } );