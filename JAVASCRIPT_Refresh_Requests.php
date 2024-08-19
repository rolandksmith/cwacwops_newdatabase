add_action( 'wp_head', function () { ?>
<script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery(".refresh").each(function() {
      var el = jQuery(this);
      var data = el.data();
      var url = data.url;
      var seconds = parseInt(data.seconds, 10) || 60;

      delete data.url;
      delete data.seconds;

      if (url) {
        fetchContent();
      }

      function fetchContent() {
//       console.log('FETCH', data);
        jQuery.post(url, data, null, 'html')
          .done(function(html) {
//            console.log('RETURNED', html);
            el.html(html);
            setTimeout(fetchContent, seconds * 1000);
          })
          .fail(function(error) {
//             console.log('ERROR', error);
          });
      }
    });
  });
</script>
<?php } );