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
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<div class="notice notice-warning">
					<p><strong><?php _e( 'WARNING: Before you delete any meta please first take Backup, any delete operation done is irreversible. Please use it with caution!', 'wp-bulk-delete' ); ?></strong></p>
				</div>

				<div class="delete_notice"></div>

				<!--<div id="postbox-container-1" class="postbox-container">

				</div>-->

				<div id="postbox-container-2" class="postbox-container">

					<h2>
						<?php esc_html_e( 'Coming soon', 'wp-bulk-delete'); ?>
					</h2>
				</div>
			</div>
			<br class="clear">
		</div>

	</div><!-- /.wrap -->
	<?php
}