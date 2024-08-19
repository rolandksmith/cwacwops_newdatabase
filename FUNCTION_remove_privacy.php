add_filter( 'wpum_get_registration_fields', function ( $fields ) {
	if ( isset( $fields['privacy'] ) ) {
		unset( $fields['privacy'] );
	}

	return $fields;
} );