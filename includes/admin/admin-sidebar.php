<?php
/**
 * Sidebar for Admin Pages
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Render Sidebar for Admin Pages.
 *
 * @since 1.1.0
 * @return void
 */
function wpbd_admin_sidebar() {
	if(!wpbd_is_pro()){
	?>
	<div class="upgrade_to_pro">
		<h2><?php esc_html_e( 'Upgrade to Pro','wp-bulk-delete'); ?></h2>
		<p><?php esc_html_e( 'Unlock more power to bulk delete operation, Upgrade today!!','wp-bulk-delete'); ?></p>
		<a class="button button-primary upgrade_button" href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>" target="_blank">
			<?php esc_html_e( 'Upgrade to Pro','wp-bulk-delete'); ?>
		</a>
	</div>

	<div class="upgrade_to_pro">
		<h2><?php esc_html_e( 'Custom WordPress Development Services','wp-bulk-delete'); ?></h2>
		<p><?php esc_html_e( "From small blog to complex web apps, we push the limits of what's possible with WordPress.","wp-bulk-delete" ); ?></p>
		<a class="button button-primary upgrade_button" href="<?php echo esc_url('https://xylusthemes.com/contact/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin'); ?>" target="_blank">
			<?php esc_html_e( 'Hire Us','wp-bulk-delete'); ?>
		</a>
	</div>

	<div>
		<p style="text-align:center">
			<strong><?php esc_html_e( 'Would you like to remove these ads?','wp-bulk-delete'); ?></strong><br>
			<a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>" target="_blank">
				<?php esc_html_e( 'Get Premium','wp-bulk-delete'); ?>
			</a>
		</p>
	</div>
	<?php
	}
}
add_action( 'wpbd_admin_sidebar', 'wpbd_admin_sidebar', 10 );