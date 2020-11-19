<?php
/**
 * Delete Comments Form Funcitons
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Delete Comments Form Funcitons
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Actions *************************************************************/
add_action( 'wpbd_delete_comments_form', 'wpdb_render_delete_comments_status' );
add_action( 'wpbd_delete_comments_form', 'wpdb_render_delete_comments_users' );
add_action( 'wpbd_delete_comments_form', 'wpdb_render_delete_comments_posts' );
add_action( 'wpbd_delete_comments_form', 'wpdb_render_delete_comments_date_interval' );

/**
 * Process Delete Comments form
 *
 *
 * @since 1.1.0
 * @param array $data Form post Data.
 * @return array | posts ID to be delete.
 */
function xt_delete_comments_form_process( $data ) {
	$error = array();
    if ( ! current_user_can( 'manage_options' ) ) {
        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
    }
    if( empty( $data['delete_comment_status'] ) ){
        $error[] = esc_html__('Please select Comment status for proceed delete operation.', 'wp-bulk-delete' );   
    }

    if ( isset( $data['_delete_comments_wpnonce'] ) && wp_verify_nonce( $data['_delete_comments_wpnonce'], 'delete_comments_nonce' ) ) {

    	if( empty( $error ) ){
            $delete_time = ( $data['delete_time'] ) ? $data['delete_time'] : 'now';
            $delete_datetime = ( $data['delete_datetime'] ) ? $data['delete_datetime'] : '';
            if( $delete_time === 'scheduled' && !empty($delete_datetime) && wpbd_is_pro() ) {
                $data['delete_entity'] = 'comment';
                return wpbd_save_scheduled_delete($data);
            }
    		
            $comment_count = wpbulkdelete()->api->do_delete_comments( $data );
            if( false === $comment_count ){
                return array(
                    'status' => 0,
                    'messages' => array( esc_html__( 'Something went wrong pelase try again!!', 'wp-bulk-delete' ) ),
                );
            }

    		if ( ! empty( $comment_count ) && $comment_count > 0 ) {
    			return  array(
	    			'status' => 1,
	    			'messages' => array( sprintf( esc_html__( '%d comment(s) deleted successfully.', 'wp-bulk-delete' ), $comment_count )
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

    } else {
        wp_die( esc_html__( 'Sorry, Your nonce did not verify.', 'wp-bulk-delete' ) );
	}
}

/**
 * Render Userroles checkboxes.
 *
 * @since 1.1.0
 * @return void
 */
function wpdb_render_delete_comments_status(){
    $comment_status = array(
                        'pending' => __( 'Pending Comments', 'wp-bulk-delete'),
                        'spam' => __( 'Spam Comments', 'wp-bulk-delete'),
                        'trash' => __( 'Trash Comments', 'wp-bulk-delete'),
                        'approved' => __( 'Approved Comments', 'wp-bulk-delete'),
                    );

    ?>
    <tr>
        <th scope="row">
            <?php _e( 'Comment Status', 'wp-bulk-delete' ); ?> :
        </th>

        <td>
            <?php
            if( ! empty( $comment_status ) ){
                foreach ($comment_status as $comment_status_value => $comment_status_name ) {
                    ?>
                    <input name="delete_comment_status[]" class="delete_comment_status" id="comment_status_<?php echo $comment_status_value; ?>" type="checkbox" value="<?php echo $comment_status_value; ?>" >
                    <label for="comment_status_<?php echo $comment_status_value; ?>">
                        <?php echo $comment_status_name . ' ' . sprintf( __( '( %s Comment(s) )', 'wp-bulk-delete' ), wpbulkdelete()->api->get_comment_count( $comment_status_value ) ); ?>
                    </label>
                    <br/>
                <?php
                }
            }
            ?>
            <p class="description">
                <?php _e('Select the comment statuses which you want to delete.','wp-bulk-delete'); ?>
            </p>
        </td>
    </tr>
    <?php
}


/**
 * Render User registration date interval.
 *
 * @since 1.0
 * @return void
 */
function wpdb_render_delete_comments_date_interval(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Comment Date :','wp-bulk-delete'); ?>
        </th>
        <td>
            <?php _e('Delete Comments which are','wp-bulk-delete'); ?> 
            <select name="date_type" class="date_type">
                <option value="older_than"><?php _e('older than','wp-bulk-delete'); ?></option>
                <option value="within_last"><?php _e('submitted within last','wp-bulk-delete'); ?></option>
                <option value="custom_date"><?php _e('submitted between','wp-bulk-delete'); ?></option>
            </select>
            <div class="wpbd_date_days wpbd_inline">
                <input type="number" id="input_days" name="input_days" class="wpbd_input_days" placeholder="0" min="0" /> <?php _e('days','wp-bulk-delete'); ?>
            </div>
            <div class="wpbd_custom_interval wpbd_inline" style="display:none;">
                <input type="text" id="delete_start_date" name="delete_start_date" class="delete_all_datepicker" placeholder="<?php _e('Start Date','wp-bulk-delete'); ?>" />
                -
                <input type="text" id="delete_end_date" name="delete_end_date" class="delete_all_datepicker" placeholder="<?php _e('End Date','wp-bulk-delete'); ?>" />
                <p class="description">
                    <?php _e('Set the date interval for comments to delete ( only delete comments between these dates ) or leave these fields blank to select all comments. The dates must be specified in the following format: <strong>YYYY-MM-DD</strong>','wp-bulk-delete'); ?>
                </p>
            </div>            
        </td>
    </tr>
    <?php
}

/**
 * Render Users Dropdown for comment authors.
 *
 * @since 1.1
 * @return void
 */
function wpdb_render_delete_comments_users(){
    global $wpdb;
    ?>
    <tr>
        <th scope="row">
            <?php _e('Comment Author','wp-bulk-delete'); ?> :
        </th>
        <td>
            <?php 
            if( ! wpbd_is_pro() ) { ?>
                <select name="sample1" class="sample1" disabled="disabled" >
                    <option value=""><?php esc_attr_e( 'Select author', 'wp-bulk-delete' ); ?></option>
                </select>
                <?php
            } else {
                $comment_query = "SELECT DISTINCT `comment_author` FROM {$wpdb->comments}";
                $comment_authors = $wpdb->get_col( $comment_query );
                if( !empty( $comment_authors ) ){
                    ?>
                    <select name="comment_author" class="chosen_select">
                        <option value=""><?php esc_attr_e( 'Select author', 'wp-bulk-delete' ); ?></option>
                        <?php
                        foreach ($comment_authors as $comment_author ) {
                            echo '<option value="' . $comment_author . '">' . $comment_author . '</option>';
                        }
                        ?>
                    </select>
                    <?php
                }
            } ?>
            <p class="description">
                <?php _e('Select comment author whose comment you want to delete.','wp-bulk-delete'); ?>
            </p>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
        </td>
    </tr>
    <?php
}

/**
 * Render Posts Dropdown for comment posts.
 *
 * @since 1.1
 * @return void
 */
function wpdb_render_delete_comments_posts(){
    global $wpdb;
    ?>
    <tr>
        <th scope="row">
            <?php _e('Comment Post','wp-bulk-delete'); ?> :
        </th>
        <td>
            <?php 
            if( ! wpbd_is_pro() ) { ?>
                <select name="sample2" class="sample2" disabled="disabled" >
                    <option value=""><?php esc_attr_e( 'Select post', 'wp-bulk-delete' ); ?></option>
                </select>
                <?php
            } else {
                $comment_query = "SELECT DISTINCT `comment_post_ID` FROM {$wpdb->comments}";
                $comment_posts = $wpdb->get_col( $comment_query );
                if( !empty( $comment_posts ) ){
                    ?>
                    <select name="comment_post" class="chosen_select">
                        <option value=""><?php esc_attr_e( 'Select post', 'wp-bulk-delete' ); ?></option>
                        <?php
                        foreach ($comment_posts as $comment_post ) {
                            echo '<option value="' . $comment_post . '">' . get_the_title( $comment_post ) . '</option>';
                        }
                        ?>
                    </select>
                    <?php
                }
            } ?>
            <p class="description">
                <?php _e('Select comment post whose comment you want to delete.','wp-bulk-delete'); ?>
            </p>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
        </td>
    </tr>
    <?php
}