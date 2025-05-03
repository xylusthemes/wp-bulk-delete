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
	
	do_action( 'wpbd_add_addon_menu', $submenu );
	
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
	if( !empty( $_GET['page'] ) && esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) == 'delete_all_actions' ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$allowed_tabs = array( 'by_posts', 'by_comments', 'by_users', 'by_terms', 'by_cleanup', 'by_support_help', 'by_schedule-delete', 'by_schedule-delete-history', 'wpbdpro-license' );
		$tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : 'by_cleanup'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

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


function add_wpbd_wca_menu_free() {
	global $submenu;
	if( !wpbd_is_pro() ) {
		if ( isset( $submenu['delete_all_actions'] ) ) {
			add_submenu_page(
				'delete_all_actions',
				__('WooCommerce', 'wp-bulk-delete'),
				__('WooCommerce', 'wp-bulk-delete') . '<span style="margin-left: 5px;height: 22px;border-radius: 3px;background: #005AE0;color: #FFF;font-size: 12px;line-height: 18px;font-weight: 600;display: inline-flex;padding: 0 4px;align-items: center;" >PRO</span>',
				'manage_options',
				'wpbd_wca_free',
				'wpbd_wca_callback_free'
			);
		}
	}
}
add_action( 'wpbd_add_addon_menu', 'add_wpbd_wca_menu_free' );

function wpbd_wca_callback_free(){
	$posts_header_result = wpdb_render_common_header( 'WooCommerce' );
	echo esc_attr( $posts_header_result );
	?>
	
	<div class="wpbd-container" style="margin-top: 60px;">
		<div class="wpbd-wrap" >
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div class="wpbd-container">
						<div class="wpbd-wrap">
							<div id="poststuff">
								<div class="wpbd-blur-filter" >
									<div class="wpbd-blur"  >
										<div class="wpbd-blur-filter-option">
										</div>
									</div>
									<div class="wpbd-blur-filter-cta" style="top: 40px;" >
										<span style="color: red"><?php echo esc_html_e( 'Available in Pro version.', 'wp-bulk-delete' ); ?>  </span><a href="<?php echo esc_url( WPBD_PLUGIN_BUY_NOW_URL ); ?>"><?php echo esc_html_e( 'Buy Now', 'wp-bulk-delete' ); ?></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	$posts_footer_result = wpdb_render_common_footer();
	echo esc_attr( $posts_footer_result );
}