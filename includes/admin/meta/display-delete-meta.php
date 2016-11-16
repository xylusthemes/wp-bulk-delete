<?php
/**
 * Admin Delete Meta
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Delete Meta Page.
 *
 * Render the delete meta page contents.
 *
 * @since 1.0
 * @return void
 */
function wpbd_delete_meta_page(){
	?>
	<div class="wrap">
		<h2><?php esc_html_e('Delete Meta','wp-bulk-delete'); ?></h2>
		<?php
		// Set Default Tab to Cleanup
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'cleanup';
		?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<div class="notice notice-warning">
					<p><strong><?php _e( 'WARNING: Before you delete any meta please first take Backup, any delete operation done is irreversible. Please use it with caution!', 'wp-bulk-delete' ); ?></strong></p>
				</div>

				<div class="delete_notice"></div>

				<div id="postbox-container-1" class="postbox-container">
					<?php do_action('wpbd_admin_sidebar'); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container">
					<h1 class="nav-tab-wrapper" style="padding-bottom: 0px">
						<a href="?page=delete_all_meta&tab=cleanup" class="nav-tab <?php echo $active_tab == 'cleanup' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'Cleanup', 'wp-bulk-delete' ); ?>
						</a>
						<a href="?page=delete_all_meta&tab=postmeta" class="nav-tab <?php echo $active_tab == 'postmeta' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'Post Meta', 'wp-bulk-delete' ); ?>
						</a>
						<a href="?page=delete_all_meta&tab=usermeta" class="nav-tab <?php echo $active_tab == 'usermeta' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'User Meta', 'wp-bulk-delete' ); ?>								
						</a>
						<a href="?page=delete_all_meta&tab=commentmeta" class="nav-tab <?php echo $active_tab == 'commentmeta' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'Comment Meta', 'wp-bulk-delete' ); ?>
						</a>
					</h1>

					<?php
					if( $active_tab == 'postmeta' || $active_tab == 'usermeta' || $active_tab == 'commentmeta' ) {
						require_once WPBD_PLUGIN_DIR . 'includes/admin/meta/wp-bulk-delete-meta.php';
					}elseif( $active_tab == 'cleanup' ){
						wpbd_cleanup_form( 'meta' );
					}
					?>
				</div>
			</div>
			<br class="clear">
		</div>
	</div><!-- /.wrap -->
	<?php
}