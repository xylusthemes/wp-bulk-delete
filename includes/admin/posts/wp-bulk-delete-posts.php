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

$post_by_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';

if(  ! empty( $_POST ) && isset( $_POST['delete_post_type'] ) ){
    
    // Get post_result for delete based on user input.
    $post_result = xt_delete_posts_form_process( $_POST );
    wpbd_display_admin_notice( $post_result );

} 
?>
<form method="post" id="delete_posts_form">
    <table class="form-table">
        <tbody>
        <?php
        if ( 'by_posttype' == $post_by_tab ){

            do_action( 'render_form_by_posttype' );

        } elseif ( 'by_author' == $post_by_tab ){
            
            do_action( 'render_form_by_author' );

        } elseif ( 'by_title' == $post_by_tab ){
            
            do_action( 'render_form_by_title' );

        } elseif( 'by_taxonomy' == $post_by_tab ){
            
            do_action( 'render_form_by_taxonomy' );

        } elseif( 'general' == $post_by_tab ){
            
            do_action( 'render_form_general' );

        } elseif( 'by_customfield' == $post_by_tab ){
            
            do_action( 'render_form_by_custom_fields' );

        }
        wp_nonce_field('delete_posts_nonce', '_delete_all_posts_wpnonce' );

        ?>
        </tbody>
    </table>
    <p class="submit">
        <input name="delete_posts_submit" id="delete_posts_submit" class="button button-primary" value="Delete Posts" type="button">
        <span class="spinner" style="float: none;"></span>
    </p>
</form>
