<?php
/**
 * Admin Cleanup page
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Cleanup Page
 *
 * Render the Cleanup page
 *
 * @since 1.1.0
 * @return void
 */
function wpbd_render_cleanup_page(){
    ?>
    <div class="wrap">
        <h2><?php esc_html_e('Cleanup','wp-bulk-delete'); ?></h2>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">

                <div class="notice notice-warning">
                    <p><strong><?php _e( 'WARNING: Before you clean up any data please first take a Backup, any delete operation done is irreversible. Please use it with caution!', 'wp-bulk-delete' ); ?></strong></p>
                </div>
                <?php do_action( 'timeout_memory_is_enough' ); ?>

                <div class="delete_notice"></div>

                <div id="postbox-container-1" class="postbox-container">
                    <?php do_action('wpbd_admin_sidebar'); ?>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <?php 
                    wpbd_cleanup_form( 'general' );
                    ?>
                </div>
            </div>
            <br class="clear">
        </div>

    </div><!-- /.wrap -->
    <?php
}
