<?php
/**
 * Admin Pages
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Create the Admin menu and submenu and assign their links to global varibles.
 *
 * @since 1.0
 * @param string $hook Page hook
 * @return void
 */
function wpbd_add_menu_pages() {
	global $xt_delete_posts_page, $xt_delete_comments_page, $xt_delete_users_page, $xt_delete_meta_page, $xt_delete_taxonomy_page;
	add_menu_page( __( 'WP Bulk Delete', 'wp-bulk-delete' ), __( 'WP Bulk Delete', 'wp-bulk-delete' ), 'manage_options', 'delete_all_posts',
	'wpbd_delete_posts_page', 'dashicons-trash', '30' );

	$xt_delete_posts_page = add_submenu_page( 'delete_all_posts', __( 'Delete Posts', 'wp-bulk-delete' ), __( 'Delete Posts', 'wp-bulk-delete' ), 'manage_options', 'delete_all_posts', 'wpbd_delete_posts_page' );

	$xt_delete_comments_page = add_submenu_page( 'delete_all_posts', __( 'Delete Comments', 'wp-bulk-delete' ), __( 'Delete Comments', 'wp-bulk-delete' ), 'manage_options', 'delete_all_comments', 'wpbd_delete_comments_page' );

	$xt_delete_users_page = add_submenu_page( 'delete_all_posts', __( 'Delete Users', 'wp-bulk-delete' ), __( 'Delete Users', 'wp-bulk-delete' ), 'manage_options', 'delete_all_users', 'wpbd_delete_users_page' );

	$xt_delete_meta_page = add_submenu_page( 'delete_all_posts', __( 'Delete Meta', 'wp-bulk-delete' ), __( 'Delete Meta', 'wp-bulk-delete' ), 'manage_options', 'delete_all_meta', 'wpbd_delete_meta_page' );

	$xt_delete_taxonomy_page = add_submenu_page( 'delete_all_posts', __( 'Delete Taxonomy', 'wp-bulk-delete' ), __( 'Delete Taxonomy', 'wp-bulk-delete' ), 'manage_options', 'delete_all_taxonomy', 'wpbd_delete_taxonomy_page' );

}
add_action( 'admin_menu', 'wpbd_add_menu_pages', 10 );