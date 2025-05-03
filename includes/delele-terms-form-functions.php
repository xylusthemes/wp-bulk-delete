<?php
/**
 * Delete Terms Form Funcitons
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Delete Terms Form Funcitons
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Actions *************************************************************/
add_action( 'wpbd_delete_terms_form', 'wpbd_render_form_posttype_dropdown' );
add_action( 'wpbd_delete_terms_form', 'wpbd_render_delete_terms_taxonomy' );

/**
 * Process Delete Terms form
 *
 *
 * @since 1.1.0
 * @param array $data Form post Data.
 * @return array | posts ID to be delete.
 */
function xt_delete_terms_form_process( $data ) {
	$error = array();
    if ( ! current_user_can( 'manage_options' ) ) {
        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
    }
    if( $data['delete_post_type'] == '' ||  $data['post_taxonomy'] == '' ){
        $error[] = esc_html__('Please select required fields for proceed delete operation.', 'wp-bulk-delete' );  
    }

    if ( !isset( $data['_delete_terms_wpnonce'] ) || !wp_verify_nonce( $data['_delete_terms_wpnonce'], 'delete_terms_nonce' ) ) {
        wp_die( esc_html__( 'Sorry, Your nonce did not verify.', 'wp-bulk-delete' ) );
    }

	if( empty( $error ) ){
		
        $term_count = wpbulkdelete()->api->do_delete_terms( $data );
        if( false === $term_count ){
            return array(
                'status' => 0,
                'messages' => array( esc_html_e( 'Something went wrong please try again!!', 'wp-bulk-delete' ) ),
            );
        }

		if ( ! empty( $term_count ) && $term_count > 0 ) {
			return  array(
    			'status' => 1,
                // translators: %d: Number of terms deleted.
    			'messages' => array( sprintf( esc_html__( '%d Term(s) deleted successfully.', 'wp-bulk-delete' ), $term_count )
    		) );
        } else {                
            return  array(
    			'status' => 1,
    			'messages' => array( esc_html_e( 'Nothing to delete!!', 'wp-bulk-delete' ) ),
    		);
        }

	} else {
		return array(
			'status' => 0,
			'messages' => $error,
		);
	}
}


/**
 * Render taxonomies for terms.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_delete_terms_taxonomy(){?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" >
                <?php esc_html_e('Post Taxonomy ','wp-bulk-delete'); ?>
                <span class="wpbd-tooltip" >
                    <div>
                        <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                        </svg>
                        <span class="wpbd-popper">
                            <?php esc_html_e( 'Select the post taxonomy whose terms you want to delete.', 'wp-bulk-delete' ); ?>
                            <div class="wpbd-popper__arrow"></div>
                        </span>
                    </div>
                </span>
            </span>
        </div>
        <div class="wpbd-inner-section-2">
            <div class="post_taxonomy">
            </div>
        </div>
    </div>
    <script>
        jQuery(document).ready(function(){
            jQuery('#delete_post_type').trigger( 'change' );
        });
    </script>
    <?php
}
