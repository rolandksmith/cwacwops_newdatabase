function wpum_disable_remember_me( $fields ) {

		print_r($fields);

		if ( isset( $fields['login']['remember'] ) ) {
			echo "login -> remember is set<br />";
			unset( $fields['login']['remember'] );
		} else {
			echo "login -> remember not set<br />";
		}

		return $fields;

}
add_filter( 'login_form_fields', 'wpum_disable_remember_me' );
