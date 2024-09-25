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
 * @return void
 */

function wpbd_add_menu_pages(){
	add_menu_page( __( 'WP Bulk Delete', 'wp-bulk-delete' ), __( 'WP Bulk Delete', 'wp-bulk-delete' ), 'manage_options', 'delete_all_actions', 'wpbd_delete_posts_page', 'dashicons-trash', '30' );
	global $submenu;	
	$submenu['delete_all_actions'][] = array( __( 'Cleanup', 'wp-bulk-delete' ), 'manage_options',admin_url( 'admin.php?page=delete_all_actions&tab=by_cleanup' ));
	$submenu['delete_all_actions'][] = array( __( 'Delete Posts', 'wp-bulk-delete' ), 'manage_options',admin_url( 'admin.php?page=delete_all_actions&tab=by_posts' ) );
	$submenu['delete_all_actions'][] = array( __( 'Delete Comments', 'wp-bulk-delete' ), 'manage_options',admin_url( 'admin.php?page=delete_all_actions&tab=by_comments' ) );
	$submenu['delete_all_actions'][] = array( __( 'Delete Users', 'wp-bulk-delete' ), 'manage_options',admin_url( 'admin.php?page=delete_all_actions&tab=by_users' ));
	$submenu['delete_all_actions'][] = array( __( 'Delete Category', 'wp-bulk-delete' ), 'manage_options',admin_url( 'admin.php?page=delete_all_actions&tab=by_terms' ));
	$submenu['delete_all_actions'][] = array( __( 'Scheduled Delete', 'wp-bulk-delete' ), 'manage_options',admin_url( 'admin.php?page=delete_all_actions&tab=by_schedule-delete' ));
	if( wpbd_is_pro() ){
		$submenu['delete_all_actions'][] = array( __( 'License', 'wp-bulk-delete' ), 'manage_options',admin_url( 'admin.php?page=delete_all_actions&tab=wpbdpro-license' ) );
	}
	
	$submenu['delete_all_actions'][] = array( __( 'Support & Help', 'wp-bulk-delete' ), 'manage_options',admin_url( 'admin.php?page=delete_all_actions&tab=by_support_help' ));

	if( !wpbd_is_pro() ){
		$submenu['delete_all_actions'][] = array( '<li class="current" style="background: #1da867;">' . __( 'Upgrade to Pro', 'wp-bulk-delete' ) . '</li>', 'manage_options', esc_url( "https://xylusthemes.com/plugins/wp-bulk-delete/"));
	}
}

add_action( 'admin_menu', 'wpbd_add_menu_pages', 10 );

/**
 * Tab Submenu got selected.
 *
 * @since 1.2
 * @return void
 */
function get_selected_tab_submenu( $submenu_file ){
	if( !empty( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) == 'delete_all_actions' ){
		$allowed_tabs = array( 'by_posts', 'by_comments', 'by_users', 'by_terms', 'by_cleanup', 'by_support_help', 'by_schedule-delete', 'by_schedule-delete-history', 'wpbdpro-license' );
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'by_cleanup';

		if( $tab == 'by_schedule-delete-history' ){
			$tab = 'by_schedule-delete';
		}
		if( in_array( $tab, $allowed_tabs ) ){
			$submenu_file = admin_url( 'admin.php?page=delete_all_actions&tab='.$tab );
		}
	}
	return $submenu_file;
}
add_filter( 'submenu_file', 'get_selected_tab_submenu' );
