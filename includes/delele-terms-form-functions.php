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
                'messages' => array( esc_html__( 'Something went wrong please try again!!', 'wp-bulk-delete' ) ),
            );
        }

		if ( ! empty( $term_count ) && $term_count > 0 ) {
			return  array(
    			'status' => 1,
    			'messages' => array( sprintf( esc_html__( '%d Term(s) deleted successfully.', 'wp-bulk-delete' ), $term_count )
    		) );
        } else {                
            return  array(
    			'status' => 1,
    			'messages' => array( esc_html__( 'Nothing to delete!!', 'wp-bulk-delete' ) ),
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
    <tr>
        <th scope="row">
            <?php _e('Post Taxonomy:','wp-bulk-delete'); ?>
        </th>
        <td>
            <div class="post_taxonomy">
            </div>
            <p class="description">
                <?php _e( 'Select the post taxonomy whose terms you want to delete.', 'wp-bulk-delete' ); ?>
            </p>
        </td>
    </tr>
    <script>
        jQuery(document).ready(function(){
            jQuery('#delete_post_type').trigger( 'change' );
        });
    </script>
    <?php
}
