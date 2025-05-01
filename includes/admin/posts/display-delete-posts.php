<?php
/**
 * Admin Delete Posts
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Delete Posts Page.
 *
 * Render the delete posts page contents.
 *
 * @since 1.0
 * @return void
 */
function wpbd_delete_posts_page(){
	global $wpdb;
	// Set Default Tab to Posts
	$active_tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : 'by_posts'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$gettab     = str_replace( 'by_', '', $active_tab );
	$gettab     = ucwords( str_replace( '_', ' & ', $gettab ) );
	if( $active_tab == 'by_schedule-delete' ){
		$gettab     = ucwords( str_replace( '-', ' ', $gettab ) );
		$page_title = $gettab;
	}elseif( $active_tab == 'by_schedule-delete-history' ){
		$page_title = 'Schedule Delete History';
	}elseif( $active_tab == 'by_support_help' ){
		$page_title = 'Support & Help';
	}elseif( $active_tab == 'wpbdpro-license' ){
		$page_title = 'License';
	}else{
		$page_title = "Delete " . $gettab;
	}
	$posts_header_result = wpdb_render_common_header( $page_title );
	echo esc_attr_e( $posts_header_result, 'wp-bulk-delete' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
	?>
	
	<div class="wpbd-container" style="margin-top: 60px;">
		<div class="wpbd-wrap" >
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div class="notice wpbd-notice notice-warning">
						<p><strong><?php esc_html_e( 'WARNING: Before you delete any data, please take a backup; and the deletion operation is irreversible. Please use it with caution!', 'wp-bulk-delete' ); ?></strong></p>
					</div>
					<?php 
						do_action( 'timeout_memory_is_enough'); 
						if ( wpbd_is_pro() ) {
							if ( isset($wpdb->admin_pro) && is_object($wpdb->admin_pro) && method_exists( $wpdb->admin_pro, 'wpbd_schedule_delete_page' ) ) {
								do_action( 'display_success_messages_pro'); 
								$wpdb->admin_pro->display_success_messages();
							}
						}

						if( ! empty( $_POST ) || ( isset( $_GET['message'] ) && !empty( $_GET['message'] ) ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
							do_action( 'delete_pctu_notice'); 
						}
					?>
					<div class="delete_notice"></div>
					<div id="postbox-container-2" class="postbox-container">
						<div class="wpbd-app">
							<div class="wpbd-tabs">
								<div class="tabs-scroller">
									<div class="var-tabs var-tabs--item-horizontal var-tabs--layout-horizontal-padding">
										<div class="var-tabs__tab-wrap var-tabs--layout-horizontal">
											<a href="?page=delete_all_actions&tab=by_cleanup" class="var-tab <?php echo $active_tab == 'by_cleanup' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
												<span class="tab-label"><?php esc_attr_e( 'Cleanup', 'wp-bulk-delete' ); ?></span>
											</a>
											<a href="?page=delete_all_actions&tab=by_posts" class="var-tab <?php echo $active_tab == 'by_posts' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
												<span class="tab-label"><?php esc_attr_e( 'Delete Posts', 'wp-bulk-delete' ); ?></span>
											</a>
											<a href="?page=delete_all_actions&tab=by_comments" class="var-tab <?php echo $active_tab == 'by_comments' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
												<span class="tab-label"><?php esc_attr_e( 'Delete Comments', 'wp-bulk-delete' ); ?></span>
											</a>
											<a href="?page=delete_all_actions&tab=by_users" class="var-tab <?php echo $active_tab == 'by_users' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
												<span class="tab-label"><?php esc_attr_e( 'Delete Users', 'wp-bulk-delete' ); ?></span>
											</a>
											<a href="?page=delete_all_actions&tab=by_terms" class="var-tab <?php echo $active_tab == 'by_terms' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
												<span class="tab-label"><?php esc_attr_e( 'Delete Category', 'wp-bulk-delete' ); ?></span>
											</a>
											<a href="?page=delete_all_actions&tab=by_schedule-delete" class="var-tab <?php echo ( $active_tab == 'by_schedule-delete' || $active_tab == 'by_schedule-delete-history' )  ? 'var-tab--active' : 'var-tab--inactive'; ?>">
												<span class="tab-label"><?php esc_attr_e( 'Schedule Delete', 'wp-bulk-delete' ); if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
											</a>
											<?php 
											
											do_action( 'wpbd_add_addon_tabs', $active_tab );

											if( wpbd_is_pro() ){ ?>
												<a href="?page=delete_all_actions&tab=wpbdpro-license" class="var-tab <?php echo $active_tab == 'wpbdpro-license' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
													<span class="tab-label"><?php esc_attr_e( 'License', 'wp-bulk-delete' ); ?></span>
												</a>
												<?php 
											}
											do_action( 'wpbd_pro_tabs' );
											?>
											<a href="?page=delete_all_actions&tab=by_support_help" class="var-tab <?php echo $active_tab == 'by_support_help' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
												<span class="tab-label"><?php esc_attr_e( 'Support & Help', 'wp-bulk-delete' ); ?></span>
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>

						<?php
						$valid_tabs = [ 'by_posts', 'by_comments', 'by_users', 'by_meta_fields', 'by_terms', 'by_cleanup','by_support_help', 'by_schedule-delete', 'wpbdpro-license' ];

						if( $active_tab == 'by_posts' ){
								require_once WPBD_PLUGIN_DIR . 'includes/admin/posts/wp-bulk-delete-posts.php';
						}elseif( $active_tab == 'by_comments' ){
							wpbd_delete_comments_page();
						}elseif( $active_tab == 'by_users' ){
							wpbd_delete_users_page();
						// }elseif( $active_tab == 'by_meta_fields' ){
						// 	wpbd_delete_meta_page();
						}elseif( $active_tab == 'by_terms' ){
							wpbd_delete_terms_page();
						}elseif( $active_tab == 'by_cleanup' ){
							wpbd_cleanup_form();
						}elseif( $active_tab == 'by_support_help' ){
							wpbd_render_support_page();
						}elseif( $active_tab == 'by_schedule-delete' || $active_tab == 'by_schedule-delete-history' ){

							if ( wpbd_is_pro() ) {
								if ( isset($wpdb->admin_pro) && is_object($wpdb->admin_pro) && method_exists( $wpdb->admin_pro, 'wpbd_schedule_delete_page' ) ) {
									$wpdb->admin_pro->wpbd_schedule_delete_page();
								}
							} else {
								?>
								<div class="wpbd-container">
									<div class="wpbd-wrap">
										<div id="poststuff">
											<div id="post-body" class="metabox-holder columns-2">
												<div id="postbox-container-2" class="postbox-container">
													<div class="wp-bulk-delete-pro-page">
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
								<?php
							}

						}elseif( $active_tab == 'wpbdpro-license' ){
							if ( wpbd_is_pro() ) {
								wpbd_pro_license_page();
							}
						}
						do_action( 'wpbd_add_addon_tab_page', $active_tab );
						?>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	</div>
	<?php
	$posts_footer_result = wpdb_render_common_footer();
	echo esc_attr_e( $posts_footer_result, 'wp-bulk-delete' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
}