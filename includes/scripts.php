<?php
/**
 * Scripts
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @param string $hook Page hook
 * @return void
 */
function wpbd_enqueue_admin_scripts( $hook ) {

	$js_dir  = WPBD_PLUGIN_URL . 'assets/js/';
	wp_register_script( 'wp-bulk-delete', $js_dir . 'wp-bulk-delete-admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), WPBD_VERSION );
	wp_enqueue_script( 'wp-bulk-delete' );
	
}

/**
 * Load Admin Styles.
 *
 * Enqueues the required admin styles.
 *
 * @since 1.0
 * @param string $hook Page hook
 * @return void
 */
function wpbd_enqueue_admin_styles( $hook ) {

  	$css_dir = WPBD_PLUGIN_URL . 'assets/css/';
 	wp_enqueue_style('jquery-ui', $css_dir . 'jquery-ui.css', false, "1.12.0" );
 	wp_enqueue_style('wp-bulk-delete-css', $css_dir . 'wp-bulk-delete-admin.css', false, "" );
}

add_action( 'admin_enqueue_scripts', 'wpbd_enqueue_admin_scripts' );
add_action( 'admin_enqueue_scripts', 'wpbd_enqueue_admin_styles' );