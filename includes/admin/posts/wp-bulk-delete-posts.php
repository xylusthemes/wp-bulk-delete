<?php
/**
 * Admin Delete Posts General Tab
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.Security.NonceVerification.Recommended
$post_by_tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : 'by_posts';
?>
<form method="post" id="delete_posts_form" class="wpbd-delete-form">
    <div class="form-table">
        <div class="wpbd-post-form-tbody">
        <?php
        if ( 'by_posts' == $post_by_tab ){

            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            do_action( 'render_form_by_posttype' );

        } elseif ( 'by_comments' == $post_by_tab ){
            
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            do_action( 'render_form_by_author' );

        } elseif ( 'by_users' == $post_by_tab ){
            
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            do_action( 'render_form_by_title' );

        } elseif( 'by_meta_fields' == $post_by_tab ){
            
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            do_action( 'render_form_by_taxonomy' );

        } elseif( 'by_terms' == $post_by_tab ){
            
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            do_action( 'render_form_general' );

        } elseif( 'by_cleanup' == $post_by_tab ){
            
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            do_action( 'render_form_general' );

        } elseif( 'by_support_help' == $post_by_tab ){
            
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            do_action( 'render_form_general' );

        } elseif( 'by_schedule-delete' == $post_by_tab ){

            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            do_action( 'render_form_by_custom_fields' );

        }
        wp_nonce_field('delete_posts_nonce', '_delete_all_actions_wpnonce' );

        ?>
        </div>
    </div>
    <p class="submit" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <input type="hidden" name="action" value="wpbd_delete_post">
        <input name="delete_posts_submit" id="delete_posts_submit" class="wpbd_button" value="<?php esc_attr_e( 'Delete Posts', 'wp-bulk-delete' ); ?>" type="button">
        <?php if ( wpbd_is_pro() ) : ?>
            <button type="button" name="export_posts_csv" id="export_posts_csv" class="wpbd_button"><?php esc_html_e( 'Export to CSV', 'wp-bulk-delete' ); ?></button>
            <button type="button" name="export_posts_xml" id="export_posts_xml" class="wpbd_button"><?php esc_html_e( 'Export to XML', 'wp-bulk-delete' ); ?></button>
        <?php else : ?>
            <button type="button" class="wpbd_button wpbd-pro-disabled" disabled style="opacity: 0.65; cursor: not-allowed; display: inline-flex; align-items: center; gap: 6px;">
                <?php esc_html_e( 'Export to CSV', 'wp-bulk-delete' ); ?>
                <span class="wpbd-pro-badge" style="background: #fff; color: #005AE0; font-size: 10px; height: 18px; line-height: 18px; padding: 0 4px; border-radius: 3px;">PRO</span>
            </button>
            <button type="button" class="wpbd_button wpbd-pro-disabled" disabled style="opacity: 0.65; cursor: not-allowed; display: inline-flex; align-items: center; gap: 6px;">
                <?php esc_html_e( 'Export to XML', 'wp-bulk-delete' ); ?>
                <span class="wpbd-pro-badge" style="background: #fff; color: #005AE0; font-size: 10px; height: 18px; line-height: 18px; padding: 0 4px; border-radius: 3px;">PRO</span>
            </button>
        <?php endif; ?>
        <span class="spinner" style="float: none;"></span>
    </p>
</form>
