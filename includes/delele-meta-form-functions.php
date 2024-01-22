<?php
/**
 * Form Process
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Form Process
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/** Actions *************************************************************/
// postmeta
add_action( 'render_postmeta_form', 'wpdb_render_meta_form_posttype', 10 );
add_action( 'render_postmeta_form', 'wpdb_render_meta_form_postdropdown', 10 );
add_action( 'render_postmeta_form', 'wpbd_render_meta_fields', 10 );
add_action( 'render_postmeta_form', 'wpbd_render_meta_date_interval', 10 );

// commentmeta
add_action( 'render_commentmeta_form', 'wpbd_render_meta_fields', 10 );
add_action( 'render_commentmeta_form', 'wpbd_render_meta_date_interval', 10 );

// usermeta
add_action( 'render_usermeta_form', 'wpbd_render_meta_userroles', 10 );
add_action( 'render_usermeta_form', 'wpbd_render_meta_fields', 10 );
add_action( 'render_usermeta_form', 'wpbd_render_meta_date_interval', 10 );

/**
 * Process Delete meta form
 *
 *
 * @since 1.0
 * @param array $data meta form data.
 * @return array | with status and message.
 */
function wpbd_delete_meta_form_process( $data ) {
    $error = $meta_results = array();
    $meta_count = 0;
    
    if ( ! current_user_can( 'manage_options' ) ) {
        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
    }

    if ( isset( $data['_delete_meta_wpnonce'] ) && wp_verify_nonce( $data['_delete_meta_wpnonce'], 'delete_meta_nonce' ) ) {

        if( empty( $error ) ){
            $delete_time = ( $data['delete_time'] ) ? $data['delete_time'] : 'now';
            $delete_datetime = ( $data['delete_datetime'] ) ? $data['delete_datetime'] : '';
            if( $delete_time === 'scheduled' && !empty($delete_datetime) && wpbd_is_pro() ) {
                $data['delete_entity'] = $data['meta_type'];
                return wpbd_save_scheduled_delete($data);
            }
            
            // Get meta_results for delete based on user input.
            if( 'postmeta' == $data['meta_type'] ) {
                $meta_results = wpbulkdelete()->api->get_delete_postmeta_ids( $data );

            } elseif('usermeta' == $data['meta_type'] ) {
                $meta_results = wpbulkdelete()->api->get_delete_usermeta_ids( $data );  

            } elseif('commentmeta' == $data['meta_type'] ) {
                $meta_results = wpbulkdelete()->api->get_delete_commentmeta_ids( $data );  
            }

            if ( ! empty( $meta_results ) && count( $meta_results ) > 0 ) {
                    
                if( 'postmeta' == $data['meta_type'] ) {
                    $meta_count = wpbulkdelete()->api->do_delete_postmetas( $meta_results ); 

                } elseif('usermeta' == $data['meta_type'] ) {
                    $meta_count = wpbulkdelete()->api->do_delete_usermetas( $meta_results ); 

                } elseif('commentmeta' == $data['meta_type'] ) {
                    $meta_count = wpbulkdelete()->api->do_delete_commentmetas( $meta_results ); 

                }
                
                return  array(
                    'status' => 1,
                    'messages' => array( sprintf( esc_html__( '%d Meta deleted successfully.', 'wp-bulk-delete' ), $meta_count)
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
 * Render meta cleanup options
 *
 * @since 1.1.0
 * @return void
 */
function wpbd_render_meta_cleanup(){
    // Counts
    $orphan_postmeta_count        = wpbulkdelete()->api->get_post_count('orphan_postmeta');
    $duplicated_postmeta_count    = wpbulkdelete()->api->get_post_count('duplicated_postmeta');
    $orphan_commentmeta_count     = wpbulkdelete()->api->get_post_count('orphan_commentmeta');
    $duplicated_commentmeta_count = wpbulkdelete()->api->get_post_count('duplicated_commentmeta');
    $orphan_usermeta_count        = wpbulkdelete()->api->get_post_count('orphan_usermeta');
    $duplicated_usermeta_count    = wpbulkdelete()->api->get_post_count('duplicated_usermeta');
    $orphan_termmeta_count        = wpbulkdelete()->api->get_post_count('orphan_termmeta');
    $duplicated_termmeta_count    = wpbulkdelete()->api->get_post_count('duplicated_termmeta');
    ?>
    <tr>
        <th scope="row">
            <?php _e('Cleanup Meta:','wp-bulk-delete'); ?>
        </th>
        <td>
            <fieldset>
                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_orphan_postmeta" type="checkbox" value="orphan_postmeta" >
                <label for="cleanup_orphan_postmeta">                    
                    <?php printf( __( 'Orphaned Post Meta (%d Post Meta)', 'wp-bulk-delete' ), $orphan_postmeta_count ); ?>
                </label>
            </fieldset>

            <fieldset>
                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_duplicated_postmeta" type="checkbox" value="duplicated_postmeta" >
                <label for="cleanup_duplicated_postmeta">                    
                    <?php printf( __( 'Duplicated Post Meta (%d Post Meta)', 'wp-bulk-delete' ), $duplicated_postmeta_count ); ?>
                </label>
            </fieldset>

            <fieldset>
                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_orphan_commentmeta" type="checkbox" value="orphan_commentmeta" >
                <label for="cleanup_orphan_commentmeta">                    
                    <?php printf( __( 'Orphaned Comment Meta (%d Comment Meta)', 'wp-bulk-delete' ), $orphan_commentmeta_count ); ?>
                </label>
            </fieldset>

            <fieldset>
                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_duplicated_commentmeta" type="checkbox" value="duplicated_commentmeta" >
                <label for="cleanup_duplicated_commentmeta">                    
                    <?php printf( __( 'Duplicated Comment Meta (%d Comment Meta)', 'wp-bulk-delete' ), $duplicated_commentmeta_count ); ?>
                </label>
            </fieldset>

            <fieldset>
                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_orphan_usermeta" type="checkbox" value="orphan_usermeta" >
                <label for="cleanup_orphan_usermeta">                    
                    <?php printf( __( 'Orphaned User Meta (%d User Meta)', 'wp-bulk-delete' ), $orphan_usermeta_count ); ?>
                </label>
            </fieldset>

            <fieldset>
                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_duplicated_usermeta" type="checkbox" value="duplicated_usermeta" >
                <label for="cleanup_duplicated_usermeta">                    
                    <?php printf( __( 'Duplicated User Meta (%d User Meta)', 'wp-bulk-delete' ), $duplicated_usermeta_count ); ?>
                </label>
            </fieldset>

            <fieldset>
                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_orphan_termmeta" type="checkbox" value="orphan_termmeta" >
                <label for="cleanup_orphan_termmeta">                    
                    <?php printf( __( 'Orphaned Term Meta (%d Term Meta)', 'wp-bulk-delete' ), $orphan_commentmeta_count ); ?>
                </label>
            </fieldset>

            <fieldset>
                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_duplicated_termmeta" type="checkbox" value="duplicated_termmeta" >
                <label for="cleanup_duplicated_termmeta">                    
                    <?php printf( __( 'Duplicated Term Meta (%d Term Meta)', 'wp-bulk-delete' ), $duplicated_termmeta_count ); ?>
                </label>
            </fieldset>

        </td>
    </tr>
    <?php
}

/**
 * Render post meta post options
 *
 * @since 1.1.0
 * @return void
 */
function wpdb_render_meta_form_posttype(){
    global $wp_post_types;
    $ingnore_types = array('attachment','revision','nav_menu_item');
    $types = array();
    if( !empty( $wp_post_types ) ){
        foreach( $wp_post_types as $key_type => $post_type ){
            if( in_array( $key_type, $ingnore_types ) ){
                continue;
            }else{
                $types[$key_type] = $post_type->labels->name;
            }
        }
    }
    ?>
    <tr>
        <th scope="row">
            <?php _e('Post type:','wp-bulk-delete'); ?>
        </th>
        <td>
            <select name="meta_post_type" class="meta_post_type" id="meta_post_type" required="required">
                <?php
                if( !empty( $types ) ){
                    foreach( $types as $key_type => $type ){
                        ?>
                        <fieldset>
                            <label for="meta_post_type">
                                <option value="<?php echo $key_type; ?>">
                                    <?php printf( __( '%s', 'wp-bulk-delete' ), $type ); ?> 
                                </option>
                            </label>
                        </fieldset>
                        <?php
                    }
                }else{
                    _e('No post types are there, WP Bulk Delete will not work.','wp-bulk-delete');
                }
                ?>
            </select>
            <p class="description">
                <?php esc_html_e('Select the post type whose post meta fields you want to delete.','wp-bulk-delete'); ?>
            </p>
        </td>
    </tr>
    <?php
}


/**
 * Render meta Fields.
 *
 * @since 1.1.0
 * @return void
 */
function wpbd_render_meta_fields(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Meta fields','wp-bulk-delete'); ?> :
        </th>
        <td>
            <?php esc_html_e( 'Meta key', 'wp-bulk-delete' ); ?> 
            <input type="text" id="custom_field_key" name="custom_field_key" class="custom_field_key" placeholder="<?php esc_html_e( 'Meta key (Required)', 'wp-bulk-delete' ); ?>" required="required" />
            <select name="custom_field_compare">
                <option value="equal_to_str"><?php esc_html_e( 'equal to ( string )', 'wp-bulk-delete' ); ?></option>
                <option value="notequal_to_str"><?php esc_html_e( 'not equal to ( string )', 'wp-bulk-delete' ); ?></option>
                <option value="like_str"><?php esc_html_e( 'like ( string )', 'wp-bulk-delete' ); ?></option>
                <option value="notlike_str"><?php esc_html_e( 'not like ( string )', 'wp-bulk-delete' ); ?></option><option value="equal_to_date"><?php esc_html_e( 'equal to ( date )', 'wp-bulk-delete' ); ?></option>
                <option value="notequal_to_date"><?php esc_html_e( 'not equal to ( date )', 'wp-bulk-delete' ); ?></option>
                <option value="lessthen_date"><?php esc_html_e( 'less than ( date )', 'wp-bulk-delete' ); ?></option>
                <option value="lessthenequal_date"><?php esc_html_e( 'less than and equal to ( date )', 'wp-bulk-delete' ); ?></option>
                <option value="greaterthen_date"><?php esc_html_e( 'greater than ( date )', 'wp-bulk-delete' ); ?></option>
                <option value="greaterthenequal_date"><?php esc_html_e( 'greater than and equal to ( date )', 'wp-bulk-delete' ); ?></option>
                <option value="equal_to_number"><?php esc_html_e( 'equal to ( number )', 'wp-bulk-delete' ); ?></option>
                <option value="notequal_to_number"><?php esc_html_e( 'not equal to ( number )', 'wp-bulk-delete' ); ?></option>
                <option value="lessthen_number"><?php esc_html_e( 'less than ( number )', 'wp-bulk-delete' ); ?></option>
                <option value="lessthenequal_number"><?php esc_html_e( 'less than and equal to ( number )', 'wp-bulk-delete' ); ?></option>
                <option value="greaterthen_number"><?php esc_html_e( 'greater than ( number )', 'wp-bulk-delete' ); ?></option>
                <option value="greaterthenequal_number"><?php esc_html_e( 'greater than and equal to ( number )', 'wp-bulk-delete' ); ?></option>
            </select>
            <?php esc_html_e( 'Value', 'wp-bulk-delete' ); ?> 
            <input type="text" id="custom_field_value" name="custom_field_value" class="custom_field_value" placeholder="<?php esc_html_e( 'Meta value (Optional)', 'wp-bulk-delete' ); ?>" />
            <p class="description">
                <?php esc_html_e('Enter the meta key for delete meta, please consider following points into meta delete.','wp-bulk-delete'); ?><br>
                <?php esc_html_e('1. If you want to delete meta by meta key only enter meta key.','wp-bulk-delete'); ?><br>
                <?php esc_html_e('2. If you want to delete meta by meta key and meta value than enter both values.','wp-bulk-delete'); ?><br>
            </p>
        </td>
    </tr>
    <?php
}


/**
 * Render Postmeta Date intervals.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_meta_date_interval(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Date interval :','wp-bulk-delete'); ?>
        </th>
        <td>
        <?php _e('Delete meta for posts/comments/users which are','wp-bulk-delete'); ?> 
            <select name="date_type" class="date_type">
                <option value="older_than"><?php _e('older than','wp-bulk-delete'); ?></option>
                <option value="within_last"><?php _e('created within last','wp-bulk-delete'); ?></option>
                <?php if( wpbd_is_pro() ) { ?>
                    <option value="onemonth"><?php _e('1 Month','wp-bulk-delete'); ?></option>
                    <option value="sixmonths"><?php _e('6 Months','wp-bulk-delete'); ?></option>
                    <option value="oneyear"><?php _e('1 Year','wp-bulk-delete'); ?></option>
                    <option value="twoyear"><?php _e('2 Years','wp-bulk-delete'); ?></option>
                <?php } ?>
                <option value="custom_date"><?php _e('created between custom','wp-bulk-delete'); ?></option>
            </select>
            <div class="wpbd_date_days wpbd_inline">
                <input type="number" id="input_days" name="input_days" class="wpbd_input_days" placeholder="0" min="0" /> <?php _e('days','wp-bulk-delete'); ?>
            </div>
            <div class="wpbd_custom_interval wpbd_inline" style="display:none;">
                <input type="text" id="delete_start_date" name="delete_start_date" class="delete_all_datepicker" placeholder="<?php _e('Start Date','wp-bulk-delete'); ?>" />
                -
                <input type="text" id="delete_end_date" name="delete_end_date" class="delete_all_datepicker" placeholder="<?php _e('End Date','wp-bulk-delete'); ?>" />
                <p class="description">
                    <?php _e('Set the date interval for posts/comments/users whose meta fields will be deleted, or leave these fields blank to select all meta. The dates must be specified in the following format: <strong>YYYY-MM-DD</strong>','wp-bulk-delete'); ?>
                </p>
            </div>
            <div class="wpbd_date_range wpbd_inline" style="display:none;">
                <p class="description">
                    <?php _e('This option will work well with Scheduled Delete, which will help to delete posts/comments/users of the selected option from the scheduled run date.','wp-bulk-delete'); ?>
                </p>
            </div>
        </td>
    </tr>
    <?php
}

/**
 * Render Userroles dropdown.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_meta_userroles(){
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
                    ?>
                    <input name="delete_user_roles[]" class="delete_user_roles" id="user_role_<?php echo $userrole; ?>" type="checkbox" value="<?php echo $userrole; ?>" >
                    <label for="user_role_<?php echo $userrole; ?>">
                        <?php echo $userrole . ' ' . sprintf( __( '( %s Users )', 'wp-bulk-delete' ), $count ); ?>
                    </label><br/>
                <?php
                }
            }
            ?>
            <p class="description">
                <?php _e('Select the user roles from which you want to delete user meta.','wp-bulk-delete'); ?>
            </p>
        </td>
    </tr>
    <?php
}

/**
 * Render post dropdown based on posttype selection.
 *
 * @since 1.1
 * @return void
 */
function wpdb_render_meta_form_postdropdown(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Post :','wp-bulk-delete'); ?>
        </th>
        <td>
            <div <?php if(wpbd_is_pro()){ ?>class="postdropdown_space"<?php } ?>>
                <select name="sample_post_dropdown" disabled="disabled">
                    <option value=""> <?php esc_html_e( 'Select post', 'wp-bulk-delete' ); ?></option>
                </select>
            </div>
            <p class="description">
                <?php esc_html_e('Select the post whose post meta fields you want to delete.','wp-bulk-delete'); ?>
            </p>
            <?php do_action( 'wpbd_display_available_in_pro'); ?>
        </td>
    </tr>
    <script>
        jQuery(document).ready(function(){
            jQuery('#meta_post_type').trigger( 'change' );
        });
    </script>
    <?php
}