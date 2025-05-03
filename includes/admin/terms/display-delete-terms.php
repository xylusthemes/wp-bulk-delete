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
	?>
	<div class="wpbd-container">
		<div class="wpbd-wrap">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-2" class="postbox-container">
						<form method="post" id="delete_terms_form">
							<div class="wpbd-card" style="margin-top:20px;">
								<div class="content" >
									<?php do_action( 'wpbd_delete_terms_form' ); ?>
								</div>
							</div>
							<?php
								esc_attr( wp_nonce_field('delete_terms_nonce', '_delete_terms_wpnonce' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
							?>
							<p class="submit">
								<input name="delete_terms_submit" id="delete_terms_submit" class="wpbd_button" value="<?php esc_html_e('Delete Terms', 'wp-bulk-delete');?>" type="button">
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