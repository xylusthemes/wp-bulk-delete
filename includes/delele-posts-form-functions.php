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
// By Posttype
add_action( 'render_form_by_posttype', 'wpbd_render_form_posttype' );
add_action( 'render_form_by_posttype', 'wpbd_render_common_form' );

// By Author
add_action( 'render_form_by_author', 'wpbd_render_form_posttype' );
add_action( 'render_form_by_author', 'wpbd_render_form_users' );
add_action( 'render_form_by_author', 'wpbd_render_common_form' );


// By Title & Content
add_action( 'render_form_by_title', 'wpbd_render_form_posttype', 10 );
add_action( 'render_form_by_title', 'wpbd_render_form_post_contains', 20 );
add_action( 'render_form_by_title', 'wpbd_render_common_form', 30 );

// By Taxonomy.
add_action( 'render_form_by_taxonomy', 'wpbd_render_form_posttype_dropdown' );
add_action( 'render_form_by_taxonomy', 'wpbd_render_form_taxonomy' );
add_action( 'render_form_by_taxonomy', 'wpbd_render_common_form' );

// By Custom Fields
add_action( 'render_form_by_custom_fields', 'wpbd_render_form_posttype', 10 );
add_action( 'render_form_by_custom_fields', 'wpbd_render_form_custom_fields', 20 );
add_action( 'render_form_by_custom_fields', 'wpbd_render_common_form', 30 );

// General
add_action( 'render_form_general', 'wpbd_render_form_posttype_dropdown', 10 );
add_action( 'render_form_general', 'wpbd_render_form_taxonomy', 20 );
add_action( 'render_form_general', 'wpbd_render_form_users', 30 );
add_action( 'render_form_general', 'wpbd_render_form_custom_fields', 40 );
add_action( 'render_form_general', 'wpbd_render_form_post_contains', 50 );
add_action( 'render_form_general', 'wpbd_render_common_form', 60 );

/**
 * Process Delete posts form
 *
 *
 * @since 1.0
 * @param array $data Form pot Data.
 * @return array | posts ID to be delete.
 */
function xt_delete_posts_form_process( $data ) {
	$error = array();
    
    if ( ! current_user_can( 'manage_options' ) ) {
        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
    }

    if ( isset( $data['_delete_all_posts_wpnonce'] ) && wp_verify_nonce( $data['_delete_all_posts_wpnonce'], 'delete_posts_nonce' ) ) {

    	if( empty( $error ) ) {
            $delete_time = ( $data['delete_time'] ) ? $data['delete_time'] : 'now';
            $delete_datetime = ( $data['delete_datetime'] ) ? $data['delete_datetime'] : '';
            if( $delete_time === 'scheduled' && !empty($delete_datetime) && wpbd_is_pro() ) {
                $data['delete_entity'] = 'post';
                return wpbd_save_scheduled_delete($data);
            }

            // Get post_ids for delete based on user input.
    		$post_ids = wpbulkdelete()->api->get_delete_posts_ids( $data );
    		if ( ! empty( $post_ids ) && count( $post_ids ) > 0 ) {
    			$force_delete = false;
    			if ( $data['delete_type'] === 'permenant' ) {
    				$force_delete = true;
    			}
    			
    			$post_count = wpbulkdelete()->api->do_delete_posts( $post_ids, $force_delete ); 
    			return  array(
	    			'status' => 1,
	    			'messages' => array( sprintf( esc_html__( '%d Record deleted successfully.', 'wp-bulk-delete' ), $post_count)
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
 * Render Posttype checkboxes.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_posttype(){
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
                <?php _e('Post type of items to delete :','wp-bulk-delete'); ?>
            </th>
            <td>
                <?php
                if( !empty( $types ) ){
                    foreach( $types as $key_type => $type ){
                        ?>
                        <fieldset>
                            <label for="delete_post_type">
                                <input name="delete_post_type[]" class="delete_post_type" id="<?php echo $key_type; ?>" type="checkbox" value="<?php echo $key_type; ?>" <?php if( 'post' == $key_type ){ echo 'checked="checked"'; } ?>>
                                <?php printf( __( '%s', 'wp-bulk-delete' ), $type ); ?>
                                <?php $post_count = wpbd_get_posttype_post_count( $key_type );
                                if( $post_count >= 0 ){
                                	echo '('.$post_count .' '. $type .')';
                                }
                                ?>
                            </label>
                        </fieldset>
                        <?php
                    }
                }else{
                    _e('No post types are there, WP Bulk Delete will not work.','wp-bulk-delete');
                }
                ?>
            </td>
        </tr>
        <?php
}

/**
 * Render Post type Dropdown.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_posttype_dropdown(){
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
                <?php _e('Post type of items to delete :','wp-bulk-delete'); ?>
            </th>
            <td>
                <select name="delete_post_type" class="delete_post_type" id="delete_post_type" required="required">
                    <?php
                    if( !empty( $types ) ){
                        foreach( $types as $key_type => $type ){
                            ?>
                            <fieldset>
                                <label for="delete_post_type">
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
            </td>
        </tr>
        <?php
}

/**
 * Render taxonomies.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_taxonomy(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Post Taxonomy :','wp-bulk-delete'); ?>
        </th>
        <td>
            <div class="post_taxonomy">
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="taxo_terms_title">

            <?php //_e('Post Taxonomy :','wp-bulk-delete'); ?>
        </th>
        <td>
            <div class="post_taxo_terms">
            </div>
        </td>
    </tr>
    <script>
        jQuery(document).ready(function(){
            jQuery('#delete_post_type').trigger( 'change' );
        });
    </script>
    <?php
}

/**
 * Render Post Statuses.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_poststatus(){
        ?>
        <tr>
            <th scope="row">Post Status</th>
            <td>
                <fieldset>
                    <label for="delete_post_status">
                        <input name="delete_post_status[]" id="publish" value="publish" type="checkbox" checked="checked">
                        Published
                    </label>
                </fieldset>
                <fieldset>
                    <label for="delete_post_status">
                        <input name="delete_post_status[]" id="future" value="future" type="checkbox">
                        Scheduled
                    </label>
                </fieldset>
                <fieldset>
                    <label for="delete_post_status">
                        <input name="delete_post_status[]" id="draft" value="draft" type="checkbox">
                        Draft
                    </label>
                </fieldset>
                <fieldset>
                    <label for="delete_post_status">
                        <input name="delete_post_status[]" id="pending" value="pending" type="checkbox">
                        Pending
                    </label>
                </fieldset>
                <fieldset>
                    <label for="delete_post_status">
                        <input name="delete_post_status[]" id="private" value="private" type="checkbox">
                        Private
                    </label>
                </fieldset>
            </td>
        </tr>
        <?php
}

/**
 * Render Date intervals.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_date_interval(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Post Date :','wp-bulk-delete'); ?>
        </th>
        <td>
            <?php _e('Delete Posts which are','wp-bulk-delete'); ?> 
            <select name="date_type" class="date_type">
                <option value="older_than"><?php _e('older than','wp-bulk-delete'); ?></option>
                <option value="within_last"><?php _e('posted within last','wp-bulk-delete'); ?></option>
                <option value="custom_date"><?php _e('posted between','wp-bulk-delete'); ?></option>
            </select>
            <div class="wpbd_date_days wpbd_inline">
                <input type="number" id="input_days" name="input_days" class="wpbd_input_days" placeholder="0" min="0" /> <?php _e('days','wp-bulk-delete'); ?>
            </div>
            <div class="wpbd_custom_interval wpbd_inline" style="display:none;">
                <input type="text" id="delete_start_date" name="delete_start_date" class="delete_all_datepicker" placeholder="<?php _e('Start Date','wp-bulk-delete'); ?>" />
                -
                <input type="text" id="delete_end_date" name="delete_end_date" class="delete_all_datepicker" placeholder="<?php _e('End Date','wp-bulk-delete'); ?>" />
                <p class="description">
                    <?php _e('Set the date interval for items to delete, or leave these fields blank to select all posts. The dates must be specified in the following format: <strong>YYYY-MM-DD</strong>','wp-bulk-delete'); ?>
                </p>
            </div>
        </td>
    </tr>
    <?php
}

/**
 * Render Post title and content contains.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_post_contains(){
        ?>
        <tr>
            <th scope="row">
                <?php _e('If Post Title Contains :','wp-bulk-delete'); ?>
            </th>
            <td>
                <input type="text" id="disabled_sample4" name="disabled_sample4" class="disabled_sample4" disabled="disabled" />
                 <?php _e( 'Then', 'wp-bulk-delete'  ); ?>
                <select name="disabled_sample5" disabled="disabled">
                    <option value=""><?php _e( 'Delete It.', 'wp-bulk-delete' ); ?> </option>
                    <option value=""><?php _e( "Don't delete It.", "wp-bulk-delete" ); ?> </option>
                </select>
                <br/>
                <?php do_action( 'wpbd_display_available_in_pro'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <?php _e('If Post Content Contains :','wp-bulk-delete'); ?>
            </th>
            <td>
                <input type="text" id="disabled_sample6" name="disabled_sample6" class="disabled_sample6" disabled="disabled" />
                <?php _e( 'Then', 'wp-bulk-delete'  ); ?>
                <select name="disabled_sample7" disabled="disabled">
                    <option value=""><?php _e( 'Delete It.', 'wp-bulk-delete' ); ?> </option>
                    <option value=""><?php _e( "Don't delete It.", "wp-bulk-delete" ); ?> </option>
                </select>
                <br/>
                <?php do_action( 'wpbd_display_available_in_pro'); ?>
            </td>
        </tr>
        <?php
}

/**
 * Render Delete type.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_delete_type(){
        ?>
        <tr>
            <th scope="row">
                <?php _e('Post Delete Type :','wp-bulk-delete'); ?>
            </th>
            <td>
                <input type="radio" id="delete_type" name="delete_type" class="delete_type" value="trash" checked="checked"/>
                <?php _e( 'Move to Trash', 'wp-bulk-delete'  ); ?>
                &nbsp;&nbsp;<input type="radio" id="delete_type" name="delete_type" class="delete_type" value="permenant" />
                <?php _e( 'Delete permanently', 'wp-bulk-delete'  ); ?>
            </td>
        </tr>
        <?php
}

/**
 * Render Post authors.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_users(){
        ?>
        <tr>
            <th scope="row">
                <?php _e('Authors :','wp-bulk-delete'); ?>
            </th>
            <td>
                <?php $args = array(
                        'orderby'      => 'display_name',
                        'order'        => 'ASC',
                        'fields'       => array( 'display_name', 'ID'),
                );
                $authors = get_users( $args );
                if( !empty($authors) ){
                    ?>
                        <select name="delete_authors[]" multiple="multiple">
                            <?php foreach($authors as $author){
                                ?>
                                <option value="<?php echo $author->ID; ?>"><?php printf( __( '%s', 'wp-bulk-delete' ), $author->display_name ) ; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    <?php
                }
                ?>
            </td>
        </tr>


        <?php
}

/**
 * Render Post limit.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_limit_post(){
        ?>
        <tr>
            <th scope="row">
                <?php _e('Limit :','wp-bulk-delete'); ?>
            </th>
            <td>
                <input type="number" min="1" id="limit_post" name="limit_post" class="limit_post_input" />
                <p class="description">
                    <?php _e('Set the limit over post delete. It will delete only first limit posts. This option will help you in case of you have lots of posts to delete and script timeout.','wp-bulk-delete'); ?>
                </p>
            </td>
        </tr>
        <?php
}

/**
 * Render Custom Fields.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_custom_fields(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Custom fields settings :','wp-bulk-delete'); ?>
        </th>
        <td>
            <?php esc_html_e( 'Custom Fields Key', 'wp-bulk-delete' ); ?> 
            <input type="text" id="disabled_sample1" name="disabled_sample1" class="disabled_sample1" disabled="disabled" />
            <select name="disabled_sample2" disabled="disabled">
                <option value="equal_to_str"><?php esc_html_e( 'equal to ( string )', 'wp-bulk-delete' ); ?></option>
            </select>
            <?php esc_html_e( 'Value', 'wp-bulk-delete' ); ?> 
            <input type="text" id="disabled_sample3" name="disabled_sample3" class="disabled_sample3" disabled="disabled" />
            <br />
            <span style="color: red">Available in Pro version. </span><a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>">Buy Now</a>
        </td>
    </tr>
    <?php
}

/**
 * Render cleup options
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_post_cleanup(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Cleanup Posts :','wp-bulk-delete'); ?>
        </th>
        <td>
            <fieldset>
                <label for="cleanup_post_type">
                    <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_revision" type="checkbox" value="revision" checked="checked">
                    <?php printf( __( 'Revisions (%d Revisions)', 'wp-bulk-delete' ), wpbulkdelete()->api->get_post_count('revision') ); ?>
                </label>
            </fieldset>

            <fieldset>
                <label for="cleanup_post_type">
                    <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_trash" type="checkbox" value="trash" checked="checked">
                    <?php printf( __( 'Trash (Deleted Posts) (%d Trash)', 'wp-bulk-delete' ),  wpbulkdelete()->api->get_post_count('trash') ); ?>
                </label>
            </fieldset>

            <fieldset>
                <label for="cleanup_post_type">
                    <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_revision" type="checkbox" value="auto_drafts" checked="checked">
                    <?php printf( __( 'Auto Drafts (%d Auto Drafts)', 'wp-bulk-delete' ),  wpbulkdelete()->api->get_post_count('auto_drafts') ); ?>
                </label>
            </fieldset>
        </td>
    </tr>
    <?php
}

/**
 * Render Delete Time.
 *
 * @since 1.2
 * @return void
 */
function wpbd_render_delete_time(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Delete Time :','wp-bulk-delete'); ?>
        </th>
        <td>
            <input type="radio" id="delete_time_now" name="delete_time" class="delete_time" value="now" checked="checked"/>
            <?php _e( 'Delete now', 'wp-bulk-delete'  ); ?><br />
            <input type="radio" id="delete_time_later" name="delete_time" class="delete_time" value="scheduled" <?php echo( ( ! wpbd_is_pro() ) ? 'disabled="disabled"' : '' ); ?>/>
            <?php _e( 'Schedule delete at', 'wp-bulk-delete'  ); ?>
            <input type="text" id="delete_datetime" name="delete_datetime" class="delete_all_datetimepicker" placeholder="YYYY-MM-DD HH:mm:ss" <?php echo( ( ! wpbd_is_pro() ) ? 'disabled="disabled"' : '' ); ?>/>
            <?php 
            _e( 'repeat', 'wp-bulk-delete'  );
            wpbd_render_import_frequency();
            do_action( 'wpbd_display_available_in_pro');
            $timezone = wpbd_get_timezone_string();
            ?>
            <p class="description">
                <strong><?php printf( esc_html__( 'Timezone: (%s)', 'wp-bulk-delete' ), $timezone ); ?></strong><br/>
                <?php _e('Scheduled delete runs using cron and backgroud process. So, its useful for delete huge number of records and repeatative delete.','wp-bulk-delete'); ?>
            </p>
        </td>
    </tr>
    <?php
}

/**
 * Render import Frequency
 *
 * @since   1.2.0
 * @param string $selected Selected import frequency.
 * @return  void
 */
function wpbd_render_import_frequency( $selected = 'not_repeat' ) {
    ?>
    <select name="delete_frequency" class="delete_frequency" <?php echo( ( ! wpbd_is_pro() ) ? 'disabled="disabled"' : '' ); ?> >
        <option value='not_repeat' <?php selected( $selected, 'not_repeat' ); ?>>
            <?php esc_html_e( 'Don\'t repeat', 'wp-bulk-delete' ); ?>
        </option>
        <option value='hourly' <?php selected( $selected, 'hourly' ); ?>>
            <?php esc_html_e( 'Once Hourly', 'wp-bulk-delete' ); ?>
        </option>
        <option value='twicedaily' <?php selected( $selected, 'twicedaily' ); ?>>
            <?php esc_html_e( 'Twice Daily', 'wp-bulk-delete' ); ?>
        </option>
        <option value="daily" <?php selected( $selected, 'daily' ); ?> >
            <?php esc_html_e( 'Once Daily', 'wp-bulk-delete' ); ?>
        </option>
        <option value="weekly" <?php selected( $selected, 'weekly' ); ?>>
            <?php esc_html_e( 'Once Weekly', 'wp-bulk-delete' ); ?>
        </option>
        <option value="monthly" <?php selected( $selected, 'monthly' ); ?>>
            <?php esc_html_e( 'Once a Month', 'wp-bulk-delete' ); ?>
        </option>
    </select>
    <span class="wpbd_schedule_name_wrap" style="display:none;">
    <?php _e( 'Save it as ', 'wp-bulk-delete' ); ?>
    <input type="text" name="schedule_name" placeholder="<?php _e( 'eg: Daily Post Delete', 'wp-bulk-delete' ); ?>" class="wpbd_schedule_name"/>
    </span>
    <?php
}

/**
 * Render Common form.
 *
 * Render common component of form.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_common_form(){
    ?>
    <tr>
        <th scope="row">
            <?php _e('Filter your posts :','wp-bulk-delete'); ?>
        </th>
    </tr>
    <?php

    wpbd_render_form_poststatus();

    wpbd_render_form_date_interval();

    wpbd_render_form_delete_type();

    wpbd_render_limit_post();

    wpbd_render_delete_time();
}

function wpbd_get_timezone_string() {
    $timezone_string = get_option( 'timezone_string' );
 
    if ( $timezone_string ) {
        return $timezone_string;
    }
 
    $offset  = (float) get_option( 'gmt_offset' );
    $hours   = (int) $offset;
    $minutes = ( $offset - $hours );
 
    $sign      = ( $offset < 0 ) ? '-' : '+';
    $abs_hour  = abs( $hours );
    $abs_mins  = abs( $minutes * 60 );
    $tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
 
    return $tz_offset;
}