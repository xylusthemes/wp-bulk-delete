<?php
/**
 * Admin Delete Comments
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Delete Comments Page.
 *
 * Render the delete comments page contents.
 *
 * @since 1.1.0
 * @return void
 */
function wpbd_delete_comments_page(){

	if(  ! empty( $_POST ) && isset( $_POST['_delete_comments_wpnonce'] ) ){
    
	    // Get comment_result for delete based on user input.
	    $comment_result = xt_delete_comments_form_process( $_POST );
	    wpbd_display_admin_notice( $comment_result );

	}
	?>
	<div class="wrap">
		<h2><?php esc_html_e('Delete Comments','wp-bulk-delete'); ?></h2>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<div class="notice notice-warning">
					<p><strong><?php _e( 'WARNING: Before you delete any comment please first take Backup, any delete operation done is irreversible. Please use it with caution!', 'wp-bulk-delete' ); ?></strong></p>
				</div>

				<div class="delete_notice"></div>

				<div id="postbox-container-1" class="postbox-container">
					<?php do_action('wpbd_admin_sidebar'); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container">

					<form method="post" id="delete_comments_form">
    					<table class="form-table">
							<?php 
							do_action( 'wpbd_delete_comments_form' );
							
							wpbd_render_delete_time();
							?>
    					</table>
    					<?php
    					echo wp_nonce_field('delete_comments_nonce', '_delete_comments_wpnonce' );
    					?>
    					<p class="submit">
					        <input name="delete_comments_submit" id="delete_comments_submit" class="button button-primary" value="<?php esc_html_e('Delete Comments', 'wp-bulk-delete');?>" type="button">
					        <span class="spinner" style="float: none;"></span>
					    </p>
    				</form>

				</div>
			</div>
			<br class="clear">
		</div>

	</div><!-- /.wrap -->
	<?php
}