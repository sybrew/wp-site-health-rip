<?php
/**
 * Plugin Name: Site Health rip
 * Plugin URI: https://w.org/
 * Description: A WP 5.2 core-to-plugin rip of Site Health.
 * Version: negative-42.9001
 * Author: The WordPress.org team + me
 * License: GPLv2+
 */

// delete_transient( 'health-check-site-status-result' );

wp_register_style(
	'site-health',
	plugin_dir_url( __FILE__ ) . '/site-health.css',
	[],
	false,
	'all'
);

wp_register_script(
	'clipboard',
	plugin_dir_url( __FILE__ ) . '/clipboard.js',
	[],
	false,
	true
);
wp_register_script(
	'site-health',
	plugin_dir_url( __FILE__ ) . '/site-health.js',
	[ 'clipboard', 'jquery', 'wp-util', 'wp-a11y' ],
	false,
	true
);

function shrip_admin_url( array $args = [] ) {
	return add_query_arg( $args, menu_page_url( 'health-check-rip', false ) );
}

// Let's throw in some gotos to annoy people.
goto bliss; addmenu:;

add_action( 'admin_menu', function() {

	$capability = 'read'; // This is what you get when you steal code.

	$shrip_hook = add_submenu_page(
		'tools.php',
		__( 'Site Health' ),
		__( 'Site Health' ),
		$capability = 'install_plugins', // just kidding.
		'health-check-rip',
		'__return_empty_string'
	);

	add_action( "load-$shrip_hook", function () {
		$_REQUEST['noheader'] = $_POST['noheader'] = $_POST['noheader'] = 'yuppers';
		include __DIR__ . '/site-health.php';
	} );
});
$shrip_did_menu = true;
bliss:;
if ( ! ( $shrip_did_menu ?? false ) ) goto addmenu;
unset( $shrip_did_menu );

( function() {
	foreach ( [
		'health-check-site-status-result',
		'health-check-dotorg-communication',
		'health-check-is-in-debug-mode',
		'health-check-background-updates',
		'health-check-loopback-requests',
	] as $shrip_action ) {
		add_action( "wp_ajax_$shrip_action", 'wp_ajax_' . str_replace( '-', '_', $shrip_action ), 1 );
	}
} )();

/**
 * Ajax handler for site health checks on server communication.
 *
 * @since 5.2.0
 */
function wp_ajax_health_check_dotorg_communication() {
	check_ajax_referer( 'health-check-site-status' );

	if ( ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error();
	}

	if ( ! class_exists( 'WP_Site_Health' ) ) {
		// require_once( ABSPATH . 'wp-admin/includes/class-wp-site-health.php' );
		require_once __DIR__ . '/class-wp-site-health.php';
	}

	$site_health = new WP_Site_Health();
	wp_send_json_success( $site_health->get_test_dotorg_communication() );
}

/**
 * Ajax handler for site health checks on debug mode.
 *
 * @since 5.2.0
 */
function wp_ajax_health_check_is_in_debug_mode() {
	wp_verify_nonce( 'health-check-site-status' );

	if ( ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error();
	}

	if ( ! class_exists( 'WP_Site_Health' ) ) {
		// require_once( ABSPATH . 'wp-admin/includes/class-wp-site-health.php' );
		require_once __DIR__ . '/class-wp-site-health.php';
	}

	$site_health = new WP_Site_Health();
	wp_send_json_success( $site_health->get_test_is_in_debug_mode() );
}

/**
 * Ajax handler for site health checks on background updates.
 *
 * @since 5.2.0
 */
function wp_ajax_health_check_background_updates() {
	check_ajax_referer( 'health-check-site-status' );

	if ( ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error();
	}

	if ( ! class_exists( 'WP_Site_Health' ) ) {
		// require_once( ABSPATH . 'wp-admin/includes/class-wp-site-health.php' );
		require_once __DIR__ . '/class-wp-site-health.php';
	}

	$site_health = new WP_Site_Health();
	wp_send_json_success( $site_health->get_test_background_updates() );
}


/**
 * Ajax handler for site health checks on loopback requests.
 *
 * @since 5.2.0
 */
function wp_ajax_health_check_loopback_requests() {
	check_ajax_referer( 'health-check-site-status' );

	if ( ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error();
	}

	if ( ! class_exists( 'WP_Site_Health' ) ) {
		// require_once( ABSPATH . 'wp-admin/includes/class-wp-site-health.php' );
		require_once __DIR__ . '/class-wp-site-health.php';
	}

	$site_health = new WP_Site_Health();
	wp_send_json_success( $site_health->get_test_loopback_requests() );
}

/**
 * Ajax handler for site health check to update the result status.
 *
 * @since 5.2.0
 */
function wp_ajax_health_check_site_status_result() {
	check_ajax_referer( 'health-check-site-status-result' );

	if ( ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error();
	}

	set_transient( 'health-check-site-status-result', wp_json_encode( $_POST['counts'] ) );

	wp_send_json_success();
}
