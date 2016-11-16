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
$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'cleanup';

if(  ! empty( $_POST ) && isset( $_POST['meta_type'] ) ){
    
    // Get meta_result for delete based on user input.
    $meta_result = wpbd_delete_meta_form_process( $_POST );
    wpbd_display_admin_notice( $meta_result );

}

?>
<form method="post" id="delete_meta_form">
    <table class="form-table">
        <tbody>
        <?php
        if ( 'postmeta' == $active_tab ){
            do_action( 'render_postmeta_form' );
            ?><input type="hidden" name="meta_type" value="postmeta" ><?php

        } elseif ( 'usermeta' == $active_tab ){
            do_action( 'render_usermeta_form' );
            ?><input type="hidden" name="meta_type" value="usermeta" ><?php

        } elseif ( 'commentmeta' == $active_tab ){
            do_action( 'render_commentmeta_form' );
            ?><input type="hidden" name="meta_type" value="commentmeta" ><?php

        }
        wp_nonce_field('delete_meta_nonce', '_delete_meta_wpnonce' );
        ?>
        </tbody>
    </table>
    <p class="submit">
        <input name="delete_meta_submit" id="delete_meta_submit" class="button button-primary" value="Delete Meta" type="button">
        <span class="spinner" style="float: none;"></span>
    </p>
</form>
<?php
        
