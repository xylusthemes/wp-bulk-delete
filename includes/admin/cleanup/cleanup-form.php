<?php
/**
 * Admin Cleanup form
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Cleanup form
 * 
 * Render the Cleanup form
 *
 * @since 1.1.0
 * @return void
 */
function wpbd_cleanup_form( $type = 'general' ){

    if(  ! empty( $_POST ) && isset( $_POST['run_post_cleanup'] ) ){
        $messages = $error = array();
        if ( ! current_user_can( 'manage_options' ) ) {
            $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
        }

        if ( isset( $_POST['_run_post_cleanup_wpnonce'] ) && wp_verify_nonce( $_POST['_run_post_cleanup_wpnonce'], 'run_post_cleanup_nonce' ) && empty( $error ) ) {
            $cleanups = $_POST['cleanup_post_type'];
            if( ! empty( $cleanups ) ){
                foreach ($cleanups as $cleanuptype ) {
                    $messages[] = wpbulkdelete()->api->run_cleanup( $cleanuptype );                
                }
            }
        }else{
            wp_die( esc_html__( 'Sorry, Your nonce did not verify.', 'wp-bulk-delete' ) );
        }    

        if( !empty( $error ) ){
            foreach ( $error as $err ) {
                ?>
                <div class="notice notice-error">
                    <p><strong><?php echo $err; ?></strong></p>
                </div>
                <?php
            }
        }
        if( ! empty( $messages ) ){
            if (strlen(implode($messages)) == 0 ){
                ?>
            <div class="notice notice-success">
                <p><strong><?php esc_html_e( 'Nothing to cleanup!!', 'wp-bulk-delete' ); ?></strong></p>
            </div>
            <?php
            }else{
                foreach ( $messages as $message ) {
                    if( $message != '' ){
                        ?>
                        <div class="notice notice-success">
                            <p><strong><?php echo $message; ?></strong></p>
                        </div>
                        <?php
                    }
                }    
            }            
        }  
    } 
    ?>
    <form method="post" id="cleanup">
        <table class="form-table">
            <tbody>
            <?php
            if( 'general' == $type ) {

                wpbd_render_post_cleanup();
                wpbd_render_meta_cleanup();

            }elseif( 'post' == $type ) {

                wpbd_render_post_cleanup();
                
            }elseif( 'meta' == $type ) {

                wpbd_render_meta_cleanup();
                
            }
            wp_nonce_field('run_post_cleanup_nonce', '_run_post_cleanup_wpnonce' );

            ?>
            </tbody>
        </table>
        <?php
            submit_button( __('Run Cleanup','wp-bulk-delete'), 'primary','run_post_cleanup');
        ?>
    </form>
<?php 
}
