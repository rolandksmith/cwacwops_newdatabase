/**
 * Backward-compatible wrapper for data_initialization_func
 * 
 * @return array All initialization data
 * @deprecated Use CWA_Context::getInstance() instead
 */
 
function data_initialization_func() {
    return CWA_Context::getInstance()->toArray();
}
add_action('data_initialization_func', 'data_initialization_func');
