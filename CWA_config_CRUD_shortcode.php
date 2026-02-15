/**
 * WordPress shortcode registration for CWA Config CRUD
 */
function cwa_config_crud_func() {
    $crud = new CWA_Config_CRUD();
    return $crud->handle();
}
add_shortcode('cwa_config_crud', 'cwa_config_crud_func');
