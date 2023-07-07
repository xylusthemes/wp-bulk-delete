<?php
/**
 * Admin Delete Taxonomy Terms
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Delete Taxonomy Page.
 *
 * Render the delete taxonomy page contents.
 *
 * @since 1.0
 * @return void
 */
function wpbd_delete_terms_page(){

	if(  ! empty( $_POST ) && isset( $_POST['_delete_terms_wpnonce'] ) ){
    
	    // Get terms_result for delete based on user input.
	    $terms_result = xt_delete_terms_form_process( $_POST );
	    wpbd_display_admin_notice( $terms_result );
	}	
	?>
	<div class="wrap">
		<h2><?php esc_html_e('Delete Taxonomy Terms','wp-bulk-delete'); ?></h2>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<div class="notice notice-warning">
					<p><strong><?php _e( 'WARNING: Before you delete any terms please first take Backup, any delete operation done is irreversible. Please use it with caution!', 'wp-bulk-delete' ); ?></strong></p>
				</div>
				<?php do_action( 'timeout_memory_is_enough' ); ?>

				<div class="delete_notice"></div>

				<div id="postbox-container-1" class="postbox-container">
					<?php do_action('wpbd_admin_sidebar'); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container">

					<form method="post" id="delete_terms_form">
    					<table class="form-table">
    						<?php do_action( 'wpbd_delete_terms_form' ); ?>
    					</table>
    					<?php
    					echo wp_nonce_field('delete_terms_nonce', '_delete_terms_wpnonce' );
    					?>
    					<p class="submit">
					        <input name="delete_terms_submit" id="delete_terms_submit" class="button button-primary" value="<?php esc_html_e('Delete Terms', 'wp-bulk-delete');?>" type="button">
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