/**
 * WordPress shortcode registration for User Master CRUD
 */
function display_and_update_user_master_func() {
    $crud = new CWA_User_Master_CRUD();
    return $crud->handle();
}
add_shortcode('display_and_update_user_master', 'display_and_update_user_master_func');
