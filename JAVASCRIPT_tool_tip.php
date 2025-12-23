add_action( 'wp_head', function () { ?>
<script>

document.addEventListener('DOMContentLoaded', function() {
    const asterisks = document.querySelectorAll('.info-asterisk');

    asterisks.forEach(el => {
        // Create the tooltip element
        const tooltip = document.createElement('div');
        tooltip.className = 'hover-popup';
        tooltip.innerText = el.getAttribute('data-title');
        
        // Append it to the asterisk span
        el.appendChild(tooltip);
    });
});

</script>
<?php } );