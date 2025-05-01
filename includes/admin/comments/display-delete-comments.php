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
	?>
	<div class="wpbd-container">
		<div class="wpbd-wrap" >
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-2" class="postbox-container">
						<form method="post" id="delete_comments_form">
							<div class="form-table">
								<div class="wpbd-post-form-tbody">
									<?php 
										do_action( 'wpbd_delete_comments_form' );
									?>
								</div>
							</div>
							<?php
							esc_attr( wp_nonce_field('delete_comments_nonce', '_delete_comments_wpnonce' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
							?>
							
							<p class="submit">
								<input type="hidden" name="action" value="wpbd_delete_post">
								<input name="delete_comments_submit" id="delete_comments_submit" class="wpbd_button" value="<?php esc_html_e('Delete Comments', 'wp-bulk-delete');?>" type="button">
								<span class="spinner" style="float: none;"></span>
							</p>
						</form>

					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	</div>
	<?php
}