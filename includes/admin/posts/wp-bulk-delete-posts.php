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

$post_by_tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : 'by_posts'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<form method="post" id="delete_posts_form">
    <div class="form-table">
        <div class="wpbd-post-form-tbody">
        <?php
        if ( 'by_posts' == $post_by_tab ){

            do_action( 'render_form_by_posttype' );

        } elseif ( 'by_comments' == $post_by_tab ){
            
            do_action( 'render_form_by_author' );

        } elseif ( 'by_users' == $post_by_tab ){
            
            do_action( 'render_form_by_title' );

        } elseif( 'by_meta_fields' == $post_by_tab ){
            
            do_action( 'render_form_by_taxonomy' );

        } elseif( 'by_terms' == $post_by_tab ){
            
            do_action( 'render_form_general' );

        } elseif( 'by_cleanup' == $post_by_tab ){
            
            do_action( 'render_form_general' );

        } elseif( 'by_support_help' == $post_by_tab ){
            
            do_action( 'render_form_general' );

        } elseif( 'by_schedule-delete' == $post_by_tab ){

            do_action( 'render_form_by_custom_fields' );

        }
        wp_nonce_field('delete_posts_nonce', '_delete_all_actions_wpnonce' );

        ?>
        </div>
    </div>
    <p class="submit">
        <input type="hidden" name="action" value="wpbd_delete_post">
        <input name="delete_posts_submit" id="delete_posts_submit" class="wpbd_button" value="Delete Posts" type="button">
        <span class="spinner" style="float: none;"></span>
    </p>
</form>
