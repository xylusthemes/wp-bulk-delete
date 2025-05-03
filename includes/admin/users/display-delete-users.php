<?php
/**
 * Admin Delete Users
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Delete Users Page.
 *
 * Render the delete users page contents.
 *
 * @since 1.0
 * @return void
 */
function wpbd_delete_users_page(){
	?>
	<div class="wpbd-container">
		<div class="wpbd-wrap" >
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-2" class="postbox-container">
						<form method="post" id="delete_users_form">
							<div class="form-table">
							
								<div class="wpbd-card" >
									<div class="header toggles" >
										<div class="text" >
											<div class="header-icon" ></div>
											<div class="header-title" >
												<span><?php esc_html_e('Users Filter ','wp-bulk-delete'); ?></span>
											</div>
											<div class="header-extra" ></div>
										</div>
										<svg viewBox="0 0 24 24" fill="none"
											xmlns="http://www.w3.org/2000/svg" class="wpbd-caret rotated">
											<path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
										</svg>
									</div>
									<div class="content"  aria-expanded="true" style="">
										<?php 
											do_action( 'wpbd_delete_users_form' );
										?>
									</div>
								</div>

								<div class="wpbd-card" style="margin-top:10px;">
									<div class="header toggles" >
										<div class="text" >
											<div class="header-icon" ></div>
											<div class="header-title" >
												<span><?php esc_html_e('Date Filter ','wp-bulk-delete'); ?></span>
											</div>
											<div class="header-extra" ></div>
										</div>
										<svg viewBox="0 0 24 24" fill="none"
											xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
											<path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
										</svg>
									</div>
									<div class="content"  aria-expanded="false" style="display:none;">
										<?php 
											do_action( 'wpbd_delete_users_date_form' );
										?>
									</div>
								</div>

								<div class="wpbd-card" style="margin-top:10px;">
									<div class="header toggles" >
										<div class="text" >
											<div class="header-icon" ></div>
											<div class="header-title" >
												<span><?php esc_html_e('Advance Users Filter ','wp-bulk-delete'); if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
											</div>
											<div class="header-extra" ></div>
										</div>
										<svg viewBox="0 0 24 24" fill="none"
											xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
											<path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
										</svg>
									</div>
									<div class="content"  aria-expanded="false" style="display:none;">
										<?php 
											if( !wpbd_is_pro() ){
												?>
												<div class="wpbd-blur-filter" >
													<div class="wpbd-blur" >
														<div class="wpbd-blur-filter-option">
															<?php
																wpbd_render_form_custom_fields();
																wpbd_render_form_post_contains();
															?>
														</div>
													</div>
													<div class="wpbd-blur-filter-cta" >
													<span style="color: red"><?php echo esc_html_e( 'Available in Pro version.', 'wp-bulk-delete' ); ?>  </span><a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>"><?php echo esc_html_e( 'Buy Now', 'wp-bulk-delete' ); ?></a>
													</div>
												</div>
												<?php
											}else{
												do_action( 'wpbd_delete_users_advance_form' );
											}
										?>
									</div>
								</div>

								<div class="wpbd-card" style="margin-top:10px;">
									<div class="header toggles" >
										<div class="text" >
											<div class="header-icon" ></div>
											<div class="header-title" >
												<span><?php esc_html_e('Action ','wp-bulk-delete'); ?></span>
											</div>
											<div class="header-extra" ></div>
										</div>
										<svg viewBox="0 0 24 24" fill="none"
											xmlns="http://www.w3.org/2000/svg" class="wpbd-caret rotated">
											<path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
										</svg>
									</div>
									<div class="content"  aria-expanded="true" style="">
										<?php 
											do_action( 'wpbd_delete_users_action_limit_form' );
											wpbd_render_delete_time();
										?>
									</div>
								</div>

							</div>
							<?php
							esc_attr( wp_nonce_field('delete_users_nonce', '_delete_users_wpnonce' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
							?>
							<p class="submit">
								<input type="hidden" name="action" value="wpbd_delete_post">
								<input name="delete_users_submit" id="delete_users_submit" class="wpbd_button" value="<?php esc_html_e('Delete Users', 'wp-bulk-delete');?>" type="button">
								<span class="spinner" style="float: none;"></span>
							</p>
						</form>
					</div>
				</div>
				<br class="clear">
			</div>
		</div><!-- /.wrap -->
	</div>
	<?php
}