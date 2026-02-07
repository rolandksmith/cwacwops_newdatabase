/* WordPress shortcode registration
 */
function advisor_registration_func() {
    $registration = new CWA_Advisor_Registration();
    return $registration->handle();
}
add_shortcode('advisor_registration', 'advisor_registration_func');
