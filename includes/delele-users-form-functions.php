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
add_action( 'wpbd_delete_users_advance_form', 'wpdb_render_delete_users_usermeta', 20 );
add_action( 'wpbd_delete_users_advance_form', 'wpdb_render_delete_users_useremail', 20 );
add_action( 'wpbd_delete_users_advance_form', 'wpdb_render_delete_users_assignuser', 20 );
add_action( 'wpbd_delete_users_date_form', 'wpdb_render_delete_users_date_interval', 40 );
add_action( 'wpbd_delete_users_action_limit_form', 'wpdb_render_delete_users_limit', 60 );

/**
 * Process Delete Users form form
 *
 *
 * @since 1.0
 * @param array $data Form pot Data.
 * @return array | posts ID to be delete.
 */
function xt_delete_users_form_process( $data ) {
    global $wpdb;
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

                    $delete_user_who_has_no_order = isset( $data['user_who_has_no_order'] ) ? $data['user_who_has_no_order'] : 'false';
                    if( !empty( $delete_user_who_has_no_order ) && $delete_user_who_has_no_order == 'on' ){
                        foreach( $user_ids as $user_id ) {
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wc_customer_lookup WHERE user_id = %d", $user_id ) );
                        }
                    }
                    $user_count = wpbulkdelete()->api->do_delete_users( $user_ids, (int)$data['reassign_user'] );
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                    $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '%_transient_wc_report_customers_%'");
                }
    			return  array(
	    			'status' => 1,
                    // translators: %d: Number of Users deleted. 
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('User roles ','wp-bulk-delete'); ?>
                <span class="wpbd-tooltip" >
                    <div>
                        <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                        </svg>
                        <span class="wpbd-popper">
                            <?php esc_html_e('Select the user roles from which you want to delete users.','wp-bulk-delete'); ?>
                            <div class="wpbd-popper__arrow"></div>
                        </span>
                    </div>
                </span>
            </span>
        </div>
        <div class="wpbd-inner-section-2">
            <select name="delete_user_roles[]" class="wpbd_global_multiple_select" id="delete_user_roles_multiple" multiple >
                <?php
                if( ! empty( $userroles['avail_roles'] ) ){
                    foreach ($userroles['avail_roles'] as $userrole => $count ) {
                        if( $userrole != 'none' ){
                        ?>
                        <option value="<?php echo esc_attr( $userrole ); ?>" >
                            <?php 
                                // translators: %s: Number of Users
                                echo esc_attr( $userrole ) . ' ' . sprintf( esc_attr__( '( %s Users )', 'wp-bulk-delete' ), esc_attr( $count ) ); 
                            ?>
                        </option>
                        <?php
                        }
                    }
                }
                ?>
            </select>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('User Meta ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <?php esc_html_e( 'User Meta Key', 'wp-bulk-delete' ); ?> 
            <input type="text" id="sample1" name="sample1" class="sample1" placeholder="meta_key" disabled="disabled"/>
            <select name="sample2" disabled="disabled" >
                <option value="equal_to_str"><?php esc_html_e( 'equal to ( string )', 'wp-bulk-delete' ); ?></option>
            </select>
            <?php esc_html_e( 'Value', 'wp-bulk-delete' ); ?> 
            <input type="text" id="sample3" name="sample3" class="sample3" placeholder="meta_value" disabled="disabled" /><br/>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('User Email ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <?php esc_html_e( 'User Email', 'wp-bulk-delete' ); ?> 
            <select name="sample4" disabled="disabled" >
                <option value="equal_to_str"><?php esc_html_e( 'equal to ( string )', 'wp-bulk-delete' ); ?></option>
            </select>
            <textarea name="sample5" id="sample5" cols="59" class="wp_user_email_text" placeholder="You can add multiple emails with comma(,) separator" disabled="disabled" ></textarea><br/>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('User Registration Date ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <?php esc_html_e('Delete Users which are','wp-bulk-delete'); ?> 
            <select name="date_type" class="date_type">
                <option value="older_than"><?php esc_html_e('older than','wp-bulk-delete'); ?></option>
                <option value="within_last"><?php esc_html_e('registered within last','wp-bulk-delete'); ?></option>
                <?php if( wpbd_is_pro() ) { ?>
                    <option value="onemonth"><?php esc_html_e('1 Month','wp-bulk-delete'); ?></option>
                    <option value="sixmonths"><?php esc_html_e('6 Months','wp-bulk-delete'); ?></option>
                    <option value="oneyear"><?php esc_html_e('1 Year','wp-bulk-delete'); ?></option>
                    <option value="twoyear"><?php esc_html_e('2 Years','wp-bulk-delete'); ?></option>
                <?php } ?>
                <option value="custom_date"><?php esc_html_e('registered between custom','wp-bulk-delete'); ?></option>
            </select>
            <div class="wpbd_date_days wpbd_inline">
                <input type="number" id="input_days" name="input_days" class="wpbd_input_days" placeholder="0" min="0" /> <?php esc_html_e('days','wp-bulk-delete'); ?>
            </div>
            <div class="wpbd_custom_interval wpbd_inline" style="display:none;">
                <input type="text" id="delete_start_date" name="delete_start_date" class="delete_all_datepicker" placeholder="<?php esc_html_e('Start Date','wp-bulk-delete'); ?>" />
                -
                <input type="text" id="delete_end_date" name="delete_end_date" class="delete_all_datepicker" placeholder="<?php esc_html_e('End Date','wp-bulk-delete'); ?>" />

                <span class="wpbd-tooltip" >
                    <div>
                        <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                        </svg>
                        <span class="wpbd-popper">
                            <?php 
                                // translators: %s: Date format (e.g., YYYY-MM-DD).
                                $text = esc_html__('Set the reigration date interval for users to delete ( only delete users registered between these dates ) or leave these fields blank to select all users. The dates must be specified in the following format: %s', 'wp-bulk-delete');
                                echo wp_kses(
                                    sprintf($text, '<strong>YYYY-MM-DD</strong>'),
                                    array(
                                        'strong' => array(),
                                    )
                                );
                            ?>
                            <div class="wpbd-popper__arrow"></div>
                        </span>
                    </div>
                </span>
            </div>
            <div class="wpbd_date_range wpbd_inline" style="display:none;">
                <span class="wpbd-tooltip" >
                    <div>
                        <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                        </svg>
                        <span class="wpbd-popper">
                            <?php esc_html_e('This option will work well with Scheduled Delete, which will help to delete users of the selected option from the scheduled run date.','wp-bulk-delete'); ?>
                            <div class="wpbd-popper__arrow"></div>
                        </span>
                    </div>
                </span>
            </div>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Limit ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <input type="number" min="1" id="limit_user" name="limit_user" class="limit_user_input" value="500" max="1000"/>
            <span class="wpbd-tooltip" >
                <div>
                    <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                    </svg>
                    <span class="wpbd-popper">
                        <?php esc_html_e('Set the limit over user delete. It will delete only the first limited users. This option will help you in case you have lots of users to delete and script timeout.','wp-bulk-delete'); ?>
                        <div class="wpbd-popper__arrow"></div>
                    </span>
                </div>
            </span>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Assign deleted user\'s data to ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
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
            <span class="wpbd-tooltip" >
                <div>
                    <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                    </svg>
                    <span class="wpbd-popper">
                        <?php esc_html_e('Select the user to whom you want to assign the deleted user\'s data.','wp-bulk-delete'); ?>
                        <div class="wpbd-popper__arrow"></div>
                    </span>
                </div>
            </span>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
        </div>
    </div>
    <?php
}
