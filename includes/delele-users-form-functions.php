<?php
/**
 * Delete Users Form Funcitons
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Delete Users Form Funcitons
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Actions *************************************************************/
add_action( 'wpbd_delete_users_form', 'wpdb_render_delete_users_userroles', 10 );
add_action( 'wpbd_delete_users_form', 'wpdb_render_delete_users_usermeta', 20 );
add_action( 'wpbd_delete_users_form', 'wpdb_render_delete_users_useremail', 20 );
add_action( 'wpbd_delete_users_form', 'wpdb_render_delete_users_assignuser', 30 );
add_action( 'wpbd_delete_users_form', 'wpdb_render_delete_users_date_interval', 40 );
add_action( 'wpbd_delete_users_form', 'wpdb_render_delete_users_who_has_no_order', 50 );
add_action( 'wpbd_delete_users_form', 'wpdb_render_delete_users_limit', 60 );

/**
 * Process Delete Users form form
 *
 *
 * @since 1.0
 * @param array $data Form pot Data.
 * @return array | posts ID to be delete.
 */
function xt_delete_users_form_process( $data ) {
    $error = array();
    if ( ! current_user_can( 'delete_users' ) ) {
        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
    }
    if( empty( $data['delete_user_roles'] ) && ( $data['user_meta_key'] == '' || $data['user_meta_value'] == '' ) ){
        $error[] = esc_html__('Please select user role or add user meta key and value.', 'wp-bulk-delete' );   
    }

    if ( isset( $data['_delete_users_wpnonce'] ) && wp_verify_nonce( $data['_delete_users_wpnonce'], 'delete_users_nonce' ) ) {

    	if( empty( $error ) ){
            $delete_time = ( $data['delete_time'] ) ? $data['delete_time'] : 'now';
            $delete_datetime = isset( $data['delete_datetime'] ) ? $data['delete_datetime'] : '';
            if( $delete_time === 'scheduled' && !empty($delete_datetime) && wpbd_is_pro() ) {
                $data['delete_entity'] = 'user';
                return wpbd_save_scheduled_delete($data);
            }
    		
    		// Get post_ids for delete based on user input.
    		$user_ids = wpbulkdelete()->api->get_delete_user_ids( $data );
    		if ( ! empty( $user_ids ) && count( $user_ids ) > 0 ) {
                if( !wpbd_is_pro() ){
                    $user_count = wpbulkdelete()->api->do_delete_users( $user_ids );
                } else {
                    $user_count = wpbulkdelete()->api->do_delete_users( $user_ids, (int)$data['reassign_user'] );
                }
    			return  array(
	    			'status' => 1,
	    			'messages' => array( sprintf( esc_html__( '%d User(s) deleted successfully.', 'wp-bulk-delete' ), $user_count )
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
 * @since 1.0
 * @return void
 */
function wpdb_render_delete_users_userroles(){
    $userroles = count_users();
    ?>
    <tr>
        <th scope="row">
            <?php _e( 'User roles', 'wp-bulk-delete' ); ?> :
        </th>
        <td>
            <?php
            if( ! empty( $userroles['avail_roles'] ) ){
                foreach ($userroles['avail_roles'] as $userrole => $count ) {
                    if( $userrole != 'none' ){
                    ?>
                    <input name="delete_user_roles[]" class="delete_user_roles" id="user_role_<?php echo $userrole; ?>" type="checkbox" value="<?php echo $userrole; ?>" >
                    <label for="user_role_<?php echo $userrole; ?>">
                        <?php echo $userrole . ' ' . sprintf( __( '( %s Users )', 'wp-bulk-delete' ), $count ); ?>
                    </label><br/>
                    <?php
                    }
                }
            }
            ?>
            <p class="description">
                <?php _e('Select the user roles from which you want to delete users.','wp-bulk-delete'); ?>
            </p>
        </td>
    </tr>
    <?php
}


/**
 * Render Userroles checkboxes.
 *
 * @since 1.0
 * @return void
 */
function wpdb_render_delete_users_usermeta(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('User Meta','wp-bulk-delete'); ?> :
        </th>
        <td>
            <?php esc_html_e( 'User Meta Key', 'wp-bulk-delete' ); ?> 
            <input type="text" id="sample1" name="sample1" class="sample1" placeholder="meta_key" disabled="disabled"/>
            <select name="sample2" disabled="disabled" >
                <option value="equal_to_str"><?php esc_html_e( 'equal to ( string )', 'wp-bulk-delete' ); ?></option>
            </select>
            <?php esc_html_e( 'Value', 'wp-bulk-delete' ); ?> 
            <input type="text" id="sample3" name="sample3" class="sample3" placeholder="meta_value" disabled="disabled" /><br/>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
        </td>
    </tr>
    <?php
}

/**
 * Render Userroles checkboxes.
 *
 * @since 1.0
 * @return void
 */
function wpdb_render_delete_users_useremail(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('User Email','wp-bulk-delete'); ?> :
        </th>
        <td>
            <?php esc_html_e( 'User Email', 'wp-bulk-delete' ); ?> 
            <select name="sample4" disabled="disabled" >
                <option value="equal_to_str"><?php esc_html_e( 'equal to ( string )', 'wp-bulk-delete' ); ?></option>
            </select>
            <textarea name="sample5" id="sample5" cols="59" class="wp_user_email_text" placeholder="You can add multiple emails with comma(,) separator" disabled="disabled" ></textarea><br/>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
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
function wpdb_render_delete_users_date_interval(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('User Registration Date:','wp-bulk-delete'); ?>
        </th>
        <td>
            <?php _e('Delete Users which are','wp-bulk-delete'); ?> 
            <select name="date_type" class="date_type">
                <option value="older_than"><?php _e('older than','wp-bulk-delete'); ?></option>
                <option value="within_last"><?php _e('registered within last','wp-bulk-delete'); ?></option>
                <?php if( wpbd_is_pro() ) { ?>
                    <option value="onemonth"><?php _e('1 Month','wp-bulk-delete'); ?></option>
                    <option value="sixmonths"><?php _e('6 Months','wp-bulk-delete'); ?></option>
                    <option value="oneyear"><?php _e('1 Year','wp-bulk-delete'); ?></option>
                    <option value="twoyear"><?php _e('2 Years','wp-bulk-delete'); ?></option>
                <?php } ?>
                <option value="custom_date"><?php _e('registered between custom','wp-bulk-delete'); ?></option>
            </select>
            <div class="wpbd_date_days wpbd_inline">
                <input type="number" id="input_days" name="input_days" class="wpbd_input_days" placeholder="0" min="0" /> <?php _e('days','wp-bulk-delete'); ?>
            </div>
            <div class="wpbd_custom_interval wpbd_inline" style="display:none;">
                <input type="text" id="delete_start_date" name="delete_start_date" class="delete_all_datepicker" placeholder="<?php _e('Start Date','wp-bulk-delete'); ?>" />
                -
                <input type="text" id="delete_end_date" name="delete_end_date" class="delete_all_datepicker" placeholder="<?php _e('End Date','wp-bulk-delete'); ?>" />
                <p class="description">
                    <?php _e('Set the reigration date interval for users to delete ( only delete users registered between these dates ) or leave these fields blank to select all users. The dates must be specified in the following format: <strong>YYYY-MM-DD</strong>','wp-bulk-delete'); ?>
                </p>
            </div>
            <div class="wpbd_date_range wpbd_inline" style="display:none;">
                <p class="description">
                    <?php _e('This option will work well with Scheduled Delete, which will help to delete users of the selected option from the scheduled run date.','wp-bulk-delete'); ?>
                </p>
            </div>
        </td>
    </tr>
    <?php
}

/**
 * Render User delete limit.
 *
 * @since 1.0
 * @return void
 */
function wpdb_render_delete_users_limit(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Limit :','wp-bulk-delete'); ?>
        </th>
        <td>
            <input type="number" min="1" id="limit_user" name="limit_user" class="limit_user_input" />
            <p class="description">
                <?php _e('Set the limit over user delete. It will delete only the first limited users. This option will help you in case you have lots of users to delete and script timeout.','wp-bulk-delete'); ?>
            </p>
        </td>
    </tr>
    <?php
}

/**
 * Render Users Dropdown for assign user.
 *
 * @since 1.1
 * @return void
 */
function wpdb_render_delete_users_assignuser(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Assign deleted user\'s data to','wp-bulk-delete'); ?> :
        </th>
        <td>
            <?php 
            if( wpbd_is_pro() ) {
                wp_dropdown_users( array( 'show_option_none' => esc_attr__( 'Select User', 'wp-bulk-delete'), 'name' => 'reassign_user', 'role__in' => array( 'author', 'editor', 'administrator', 'contributor' ) ) );
            } else {
                ?>
                <select name="sample_user" disabled="disabled">
                    <option value=""><?php esc_attr_e( 'Select User','wp-bulk-delete'); ?></option>
                </select>
                <?php
            }
            ?>
            <p class="description">
                <?php _e('Select the user to whom you want to assign deleted user\'s data.','wp-bulk-delete'); ?>
            </p>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
        </td>
    </tr>
    <?php
}

/**
 * Render Delete posts who has no orders
 *
 * @since 1.2.6
 * @return void
 */
function wpdb_render_delete_users_who_has_no_order(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('User Who Has No Order','wp-bulk-delete'); ?> :
        </th>
        <td>
            <fieldset>
            <label for="delete_post_status" >
                <input name="" id="" type="checkbox" <?php echo( ( ! wpbd_is_pro() ) ? 'disabled="disabled"' : '' ); ?> >
                <?php _e( 'Delete WooCommerce Customer Who has no Order', 'wp-bulk-delete' ); ?>
            </label>
            <p class="description">
                <?php _e( "Select users who have no order in WooCommerce ( it's only for the customer role )", 'wp-bulk-delete' ); ?>
            </p>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
        </td>
    </tr>
    <?php
}