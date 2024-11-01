<?php
/**
 *	Uninstall
 *
 *	Deletes all the plugin data
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function ymid_uninstall_plugin() {
	global $wpdb;

	$post_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_type = 'YMID-post' LIMIT 1" );

	if ( $post_id ) {
		// There may have too many post meta. delete them first in one query.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d", $post_id ) );
		
		wp_delete_post( $post_id, true );
	}

    delete_option('ymid_captcha_key');
    delete_option('ymid_captcha_secret');
    delete_option('ymid_forms');
    delete_option('ymid_failed_login_allow');
    delete_option('ymid_working');
    delete_option('ymid_error');
    delete_option('ymid_ym_login');
    delete_option('ymid_redirect_option');
    delete_option('ymid_redirect_page');
}

ymid_uninstall_plugin();

