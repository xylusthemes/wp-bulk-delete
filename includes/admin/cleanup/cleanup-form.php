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

    if(  ! empty( $_POST ) && ( isset( $_POST['run_post_cleanup'] ) || isset( $_POST['run_post_cleanup_submit'] ) ) ){
        $result = xt_cleanup_form_process( $_POST );
        $messages = ( $result['status'] === 1 ) ? $result['messages'] : array();
        $error = ( $result['status'] === 0 ) ? $result['messages'] : array();

        if( !empty( $error ) ){
            foreach ( $error as $err ) {
                ?>
                <div class="notice wpbd-notice notice-error">
                    <p><strong><?php echo esc_html( $err ); ?></strong></p>
                </div>
                <?php
            }
        }
        if( ! empty( $messages ) ){
            $filtered_messages = array_filter( $messages );
            if ( empty( $filtered_messages ) ){
                ?>
                <div class="notice wpbd-notice notice-success is-dismissible">
                    <p><strong><?php esc_html_e( 'Nothing to cleanup!!', 'wp-bulk-delete' ); ?></strong></p>
                </div>
                <?php
            } else {
                foreach ( $filtered_messages as $message ) {
                    ?>
                    <div class="notice wpbd-notice notice-success is-dismissible">
                        <p><strong><?php echo esc_html( $message ); ?></strong></p>
                    </div>
                    <?php
                }    
            }            
        }  
    } 
    ?>
    <div class="delete_notice cleanup_delete_notice"></div>
    <form method="post" id="cleanup" class="wpbd-delete-form">
        <div class="form-table">
            <div>
                <?php
                    if( 'general' == $type ) {
                        wpbd_render_post_cleanup();
                        wpbd_render_meta_cleanup();
                    }
                    wp_nonce_field('run_post_cleanup_nonce', '_run_post_cleanup_wpnonce' );
                ?>
            </div>
        </div>
        


        <p class="submit">
            <?php if ( wpbd_is_pro() ) : ?>
                <input name="run_post_cleanup_submit" id="run_post_cleanup_submit" class="wpbd_button" value="Run Cleanup" type="button">
                <span class="spinner" style="float: none;"></span>
            <?php else : ?>
                <input type="submit" name="run_post_cleanup" id="run_post_cleanup" class="wpbd_button" value="Run Cleanup">
            <?php endif; ?>
        </p>
    </form>
<?php 
}

/**
 * Process cleanup form.
 *
 * @since 1.5.0
 * @param array $data Form data.
 * @return array Status and messages.
 */
function xt_cleanup_form_process( $data ) {
	$error = array();
	if ( ! current_user_can( 'manage_options' ) ) {
		$error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
	}

	if ( isset( $data['_run_post_cleanup_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $data['_run_post_cleanup_wpnonce'] ) ), 'run_post_cleanup_nonce' ) ) {
		if ( empty( $error ) ) {
			$cleanups = isset( $data['cleanup_post_type'] ) ? $data['cleanup_post_type'] : array();
			$messages = array();
			foreach ( $cleanups as $cleanuptype ) {
				$messages[] = wpbulkdelete()->api->run_cleanup( $cleanuptype, $data );
			}
			return array(
				'status' => 1,
				'messages' => $messages,
			);
		} else {
			return array(
				'status' => 0,
				'messages' => $error,
			);
		}
	} else {
		wp_die( esc_html__( 'Sorry, Your nonce did not verify.', 'wp-bulk-delete' ) );
	}
}
