/**
 * WordPress shortcode registration for User Master CRUD
 */
function cwa_user_master_crud_func() {
    $crud = new CWA_User_Master_CRUD();
    return $crud->handle();
}
add_shortcode('cwa_user_master_crud', 'cwa_user_master_crud_func');
