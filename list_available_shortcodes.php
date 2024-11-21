function list_available_shortcodes_func() {

 global $shortcode_tags;
 
 $skipArray = array('access',
					'audio',
					'avatar',
					'caption',
					'code_snippet_source',
					'code_snippet',
					'embed',
					'feed',
					'gallery',
					'get_avatar',
					'is_user_logged_in',
					'login-form',
					'members_access',
					'members_feed',
					'members_logged_in',
					'members_login_form',
					'members_not_logged_in',
					'playlist',
					'table-info',
					'table',
					'video',
					'wp_caption',
					'wpum_account',
					'wpum_login_form',
					'wpum_login',
					'wpum_logout',
					'wpum_password_recovery',
					'wpum_profile_card',
					'wpum_profile',
					'wpum_recently_registered',
					'wpum_register',
					'wpum_restrict_logged_in',
					'wpum_restrict_logged_out',
					'wpum_restrict_to_user_roles',
					'wpum_restrict_to_users',
					'wpum_user_directory');

 
 
 		foreach($shortcode_tags as $thisCode => $thisValue) {
 			if (!in_array($thisCode,$skipArray)) {
 				echo "$thisCode<br />";	
 			}
 		}

}
add_shortcode('list_available_shortcodes','list_available_shortcodes_func');