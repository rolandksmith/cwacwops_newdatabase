add_action( 'wp_footer', function () { 
    // Only load this script if we are on the announcement management page
    if ( is_page('cwa-manage-cwa-announcements') ) { ?>
    <script>
        function runPreview() {
            var nextPass = document.getElementById('next-pass');
            var previewFlag = document.getElementById('preview-flag');
            var form = document.getElementById('ann-form');

            if (nextPass && previewFlag && form) {
                nextPass.value = '2'; // Stay in Pass 2 to show the mockup
                previewFlag.value = 'Y'; // Tell PHP to render the preview
                form.submit();
            }
        }
    </script>
<?php } 
} );
