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
	?>
	<div class="wrap">
		<h2><?php esc_html_e('Delete Posts','wp-bulk-delete'); ?></h2>
		<?php
		// Set Default Tab to Cleanup
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'cleanup';
		?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<div class="notice notice-warning">
					<p><strong><?php _e( 'WARNING: Before you delete any post please first take Backup, any delete operation done is irreversible. Please use it with caution!', 'wp-bulk-delete' ); ?></strong></p>
				</div>
				<?php do_action( 'timeout_memory_is_enough'); ?>

				<div class="delete_notice"></div>

				<div id="postbox-container-1" class="postbox-container">
					<?php do_action('wpbd_admin_sidebar'); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container">

					<h1 class="nav-tab-wrapper" style="padding-bottom: 0px">
						<a href="?page=delete_all_posts&tab=cleanup" class="nav-tab <?php echo $active_tab == 'cleanup' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'Cleanup', 'wp-bulk-delete' ); ?>
						</a>
						<a href="?page=delete_all_posts&tab=by_posttype" class="nav-tab <?php echo $active_tab == 'by_posttype' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'By Posttype', 'wp-bulk-delete' ); ?>
						</a>
						<a href="?page=delete_all_posts&tab=by_taxonomy" class="nav-tab <?php echo $active_tab == 'by_taxonomy' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'By Taxonomy', 'wp-bulk-delete' ); ?>								
						</a>
						<a href="?page=delete_all_posts&tab=by_author" class="nav-tab <?php echo $active_tab == 'by_author' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'By Author', 'wp-bulk-delete' ); ?>
						</a>
						<a href="?page=delete_all_posts&tab=by_title" class="nav-tab <?php echo $active_tab == 'by_title' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'By Title or Content', 'wp-bulk-delete' ); ?>
						</a>
						<a href="?page=delete_all_posts&tab=by_customfield" class="nav-tab <?php echo $active_tab == 'by_customfield' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'By Custom fields', 'wp-bulk-delete' ); ?>
						</a>
						<a href="?page=delete_all_posts&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
							<?php esc_attr_e( 'General (By All)', 'wp-bulk-delete' ); ?>
						</a>
					</h1>

					<?php
					if( $active_tab == 'general' || $active_tab == 'by_taxonomy' || $active_tab == 'by_author' || $active_tab == 'by_title' || $active_tab == 'by_posttype' || $active_tab == 'by_customfield' ) {					
						// load General Post Delete Form
						require_once WPBD_PLUGIN_DIR . 'includes/admin/posts/wp-bulk-delete-posts.php';
					}
					if( $active_tab == 'cleanup' ){
						wpbd_cleanup_form( 'post' );
					}
					?>
				</div>
			</div>
			<br class="clear">
		</div>

	</div><!-- /.wrap -->
	<?php
}