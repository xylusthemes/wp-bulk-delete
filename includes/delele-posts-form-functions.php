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
add_action( 'render_form_by_posttype', 'wpbd_render_common_form', 20 );

// By Author
add_action( 'render_form_by_author', 'wpbd_render_common_form', 30 );

// By Title & Content
add_action( 'render_form_by_title', 'wpbd_render_common_form', 30 );

// By Taxonomy.
add_action( 'render_form_by_taxonomy', 'wpbd_render_common_form', 40 );

// By Custom Fields
add_action( 'render_form_by_custom_fields', 'wpbd_render_common_form', 30 );

// General
add_action( 'render_form_general', 'wpbd_render_common_form', 60 );
add_action( 'render_form_by_charector_count', 'wpdb_render_delete_users_postlinks', 10 );
add_action( 'render_form_by_charector_count', 'wpbd_render_form_post_contant_count_interval', 70 );
add_action( 'render_form_by_charector_count', 'wpbd_render_form_post_contant_word_count_interval', 80 );

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

    if ( isset( $data['_delete_all_actions_wpnonce'] ) && wp_verify_nonce( $data['_delete_all_actions_wpnonce'], 'delete_posts_nonce' ) ) {

    	if( empty( $error ) ) {
            $delete_time = ( $data['delete_time'] ) ? $data['delete_time'] : 'now';
            $delete_datetime = isset( $data['delete_datetime'] ) ? $data['delete_datetime'] : '';
            $custom_query = !empty( $data['with_custom_query'] ) ? $data['with_custom_query'] : '';
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
    			$post_count = wpbulkdelete()->api->do_delete_posts( $post_ids, $force_delete, $data, $custom_query ); 
    			return  array(
	    			'status' => 1,
                    // translators: %d: Number of Record deleted.
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
    global $wp_post_types, $wpdb;
    $ingnore_types = array( 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation', 'wpbd_scheduled', 'wp_font_face', 'wp_font_family', 'shop_order_refund', 'shop_order_placehold', 'shop_order', 'shop_order_lagecy' );
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Select post type of items to delete ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2" >
            <?php
                if( !empty( $types ) ){
                    ?>
                        <select name="delete_post_type[]" class="wpbd_global_multiple_select" id="delete_post_type" multiple>
                            <?php
                            foreach( $types as $key_type => $type ) {
                                $pselect = ( $key_type == 'post' ) ? 'selected' : '';
                                ?>
                                <option value="<?php echo esc_attr( $key_type ); ?>" <?php echo esc_attr( $pselect ); ?> >
                                    <?php echo esc_attr( $type ); ?>
                                    <?php 
                                        $post_count = wpbd_get_posttype_post_count( $key_type );
                                        if( $post_count >= 0 ){
                                            // translators: %d: Number of posts.
                                            printf( esc_attr__( ' (%d)', 'wp-bulk-delete' ), esc_attr( $post_count ) );
                                        }
                                    ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    <?php
                }else{
                    esc_html_e('No post types are there, WP Bulk Delete will not work.','wp-bulk-delete');
                }
            ?>
        </div>
    </div>
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
    $ingnore_types = array( 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation', 'wpbd_scheduled', 'wp_font_face', 'wp_font_family', 'shop_order_refund', 'shop_order_placehold', 'shop_order', 'shop_order_lagecy' );
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Select Post type ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2" >
            <select name="delete_post_type" class="delete_post_type" id="delete_post_type" required="required">
                <?php
                if( !empty( $types ) ){
                    foreach( $types as $key_type => $type ){
                        ?>
                        <fieldset>
                            <label for="delete_post_type">
                                <option value="<?php echo esc_attr( $key_type ); ?>">
                                    <?php 
                                        // translators: %s: post types.
                                        printf( esc_attr__( '%s', 'wp-bulk-delete' ), esc_attr__( $type, 'wp-bulk-delete' ) ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings, WordPress.WP.I18n.NonSingularStringLiteralText
                                    ?> 
                                </option>
                            </label>
                        </fieldset>
                        <?php
                    }
                }else{
                    esc_html_e('No post types are there, WP Bulk Delete will not work.','wp-bulk-delete');
                }
                ?>
            </select>
        </div>
    </div>
    <?php
}

/**
 * Render taxonomies.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_taxonomy( $type = 'Posts' ){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php echo esc_attr( $type ) . esc_html_e(' Taxonomy ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <div class="wpbd-texonomy-section" >
                <div class="post_taxonomy">
                </div>
                <div>
                    <div class="wpbd-inner-section-2">
                        <div class="post_taxo_terms">
                        </div>
                    </div>
                </div>
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

/**
 * Render Post Statuses.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_extra_assinged_category( $type = 'Posts' ){
    ?>
    <div class="wpbd-inner-main-section" id="delete_selected_category_section" style="display:none;">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php printf( esc_html__('Delete %s From Selected Category Only', 'wp-bulk-delete'), esc_attr( $type ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <fieldset>
                <label for="delete_post_status" >
                    <input name="delete_selected_category" id="delete_selected_category" value="d_s_c" type="checkbox" >
                </label>
                <span class="wpbd-tooltip" >
                    <div>
                        <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                        </svg>
                        <span class="wpbd-popper">
                            <?php
                                printf(
                                    // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment	
                                    esc_html__(
                                        'By selecting this option, only the selected category %1$s will be deleted, but if the %1$s has another category with the selected category, that %1$s will not be deleted (which means %1$s that have multiple categories will not be deleted)',
                                        'wp-bulk-delete'
                                    ),
                                    esc_attr( $type )
                                );
                            ?>
                            <div class="wpbd-popper__arrow"></div>
                        </span>
                    </div>
                </span>
            </fieldset>
        </div>
    </div>
    <?php
}

/**
 * Render Post Statuses.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_poststatus(){
    global $wpdb;
        //Get Post Status
        $get_post_status = get_wp_post_status();

        ?>
        <div class="wpbd-inner-main-section">
            <div class="wpbd-inner-section-1" >
                <span class="wpbd-title-text" ><?php esc_html_e('Post Status ','wp-bulk-delete'); ?></span>
            </div>
            <div class="wpbd-inner-section-2">
                <select name="delete_post_status[]" class="wpbd_global_multiple_select"  id="delete_post_status_multiple" multiple >
                    <?php
                        foreach ($get_post_status as $status => $label) {
                            $selected = ($status == 'publish') ? 'selected' : '';
                            echo '<option value="' . esc_attr($status) . '" ' . esc_attr__( $selected, 'wp-bulk-delete' ) . '>' . esc_html(ucwords(str_replace('-', ' ', $label))) . '</option>'; // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
                        }
                    ?>
                </select>
            </div>
        </div>
    <?php
        
}

/**
 * Render Post Statuses.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_custom_query(){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Post Delete from Custom Query ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <fieldset>
                <label for="delete_post_status" >
                    <input name="with_custom_query" id="with_custom_query" value="custom_query" type="checkbox" >
                    <?php esc_html_e('With Custom Query','wp-bulk-delete' ); ?>
                </label>
                <span class="wpbd-tooltip" >
                    <div>
                        <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                        </svg>
                        <span class="wpbd-popper">
                            <?php esc_html_e('Enable this option to delete posts based on custom queries. Note that this option is only effective when using the "Delete Permanently" feature.','wp-bulk-delete' ); ?>
                            <div class="wpbd-popper__arrow"></div>
                        </span>
                    </div>
                </span>
            </fieldset>
        </div>
    </div>
    <?php
}


/**
 * Render Date intervals.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_date_interval( $type = 'Posts' ){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php echo esc_attr( $type ) . esc_html_e(' Date ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <?php printf( esc_html__( 'Delete %s which are', 'wp-bulk-delete' ), esc_attr( $type ) );  // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment ?>
            <select name="date_type" class="date_type">
                <option value="older_than"><?php esc_html_e('older than','wp-bulk-delete'); ?></option>
                <option value="within_last"><?php esc_html_e('posted within last','wp-bulk-delete'); ?></option>
                <?php if( wpbd_is_pro() ) { ?>
                    <option value="onemonth"><?php esc_html_e('1 Month','wp-bulk-delete'); ?></option>
                    <option value="sixmonths"><?php esc_html_e('6 Months','wp-bulk-delete'); ?></option>
                    <option value="oneyear"><?php esc_html_e('1 Year','wp-bulk-delete'); ?></option>
                    <option value="twoyear"><?php esc_html_e('2 Years','wp-bulk-delete'); ?></option>
                <?php } ?>
                <option value="custom_date"><?php esc_html_e('posted between custom','wp-bulk-delete'); ?></option>
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
                                $text = esc_html__( 'Set the date interval for items to delete, or leave these fields blank to select all %1$s. The dates must be specified in the following format: %2$s', 'wp-bulk-delete' ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
                                echo wp_kses(
                                    sprintf(
                                        $text,
                                        esc_html( $type ),
                                        '<strong>YYYY-MM-DD</strong>'
                                    ),
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
                            <?php 
                                printf(
                                    // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
                                    esc_html__(
                                        'This option will work well with Scheduled Delete, which will help to delete %s of the selected option from the scheduled run date.',
                                        'wp-bulk-delete'
                                    ),
                                    esc_html( $type )
                                );
                            ?>
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
 * Render Modified intervals.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_modified_interval( $type = 'Posts' ){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php echo esc_attr( $type ) . esc_html_e(' Modified ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <?php printf( esc_html__( 'Delete %s which are', 'wp-bulk-delete' ), esc_attr( $type ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment ?>
            <select name="mdate_type" class="mdate_type">
                <option value="molder_than"><?php esc_html_e('older than','wp-bulk-delete'); ?></option>
                <option value="mwithin_last"><?php esc_html_e('posted within last','wp-bulk-delete'); ?></option>
                <option value="mcustom_date"><?php esc_html_e('posted between','wp-bulk-delete'); ?></option>
            </select>
            <div class="mwpbd_date_days wpbd_inline">
                <input type="number" id="minput_days" name="minput_days" class="wpbd_input_days" placeholder="0" min="0" /> <?php esc_html_e('days','wp-bulk-delete'); ?>
            </div>
            <div class="mwpbd_custom_interval wpbd_inline" style="display:none;">
                <input type="text" id="mdelete_start_date" name="mdelete_start_date" class="delete_all_datepicker" placeholder="<?php esc_html_e('Start Date','wp-bulk-delete'); ?>" />
                -
                <input type="text" id="mdelete_end_date" name="mdelete_end_date" class="delete_all_datepicker" placeholder="<?php esc_html_e('End Date','wp-bulk-delete'); ?>" />
                <span class="wpbd-tooltip" >
                    <div>
                        <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                        </svg>
                        <span class="wpbd-popper">
                            <?php 
                                $text = esc_html__( 'Set the modified date interval for items to delete, or leave these fields blank to select all %1$s. The dates must be specified in the following format: %2$s', 'wp-bulk-delete' ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
                                echo wp_kses(
                                    sprintf(
                                        $text,
                                        esc_html( $type ),
                                        '<strong>YYYY-MM-DD</strong>'
                                    ),
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
        </div>
    </div>
    <?php
}

/**
 * Render Post Contant Count.
 *
 * @since 1.2.6
 * @return void
 */
function wpbd_render_form_post_contant_count_interval(){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Post Content Count ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <?php esc_html_e('Delete Post with Content Count Limit','wp-bulk-delete'); ?> 
            <select name="disabled_sample8" disabled="disabled" >
                <option value="lessthen"><?php esc_html_e( 'Less Than.', 'wp-bulk-delete' ); ?> </option>
                <option value="greaterthen"><?php esc_html_e( "Greater Then.", "wp-bulk-delete" ); ?> </option>
            </select>
            <div class="mwpbd_date_days wpbd_inline">
                <input type="number" id="disabled_sample9"  disabled="disabled" name="disabled_sample9" class="limit_post_input" placeholder="0" min="0" /> <?php esc_html_e('Character Limit','wp-bulk-delete'); ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render Post Contant Count.
 *
 * @since 1.2.6
 * @return void
 */
function wpbd_render_form_post_contant_word_count_interval(){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Post Content Word Count ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <?php esc_html_e('Delete Post with Content Count Limit','wp-bulk-delete'); ?> 
            <select name="disabled_sample10" disabled="disabled" >
                <option value="lessthen"><?php esc_html_e( 'Less Than.', 'wp-bulk-delete' ); ?> </option>
                <option value="greaterthen"><?php esc_html_e( "Greater Then.", "wp-bulk-delete" ); ?> </option>
            </select>
            <div class="mwpbd_date_days wpbd_inline">
                <input type="number" id="disabled_sample11"  disabled="disabled" name="disabled_sample11" class="limit_post_input" placeholder="0" min="0" /> <?php esc_html_e('Word Limit','wp-bulk-delete'); ?>
            </div>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('If Post Title Contains ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <input type="text" id="disabled_sample4" name="disabled_sample4" class="disabled_sample4" disabled="disabled" />
                <?php esc_html_e( 'Then', 'wp-bulk-delete'  ); ?>
            <select name="disabled_sample5" disabled="disabled">
                <option value=""><?php esc_html_e( 'Delete It.', 'wp-bulk-delete' ); ?> </option>
                <option value=""><?php esc_html_e( "Don't delete It.", "wp-bulk-delete" ); ?> </option>
            </select>
            <br/>
        </div>
    </div>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('If Post Content Contains ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <input type="text" id="disabled_sample6" name="disabled_sample6" class="disabled_sample6" disabled="disabled" />
            <?php esc_html_e( 'Then', 'wp-bulk-delete'  ); ?>
            <select name="disabled_sample7" disabled="disabled">
                <option value=""><?php esc_html_e( 'Delete It.', 'wp-bulk-delete' ); ?> </option>
                <option value=""><?php esc_html_e( "Don't delete It.", "wp-bulk-delete" ); ?> </option>
            </select>
        </div>
    </div>
    <?php
}


/**
 * Render Post ID
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_post_ids(){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Post IDs ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <textarea name="" disabled="disabled"  id="" cols="70" style="height: 30px;" class="" placeholder="You can add multiple post IDs with comma(,) separator" ></textarea>
            <?php esc_html_e( 'Then', 'wp-bulk-delete'  ); ?>
            <select name="disabled_sample5" disabled="disabled">
                <option value=""><?php esc_html_e( 'Delete It.', 'wp-bulk-delete' ); ?> </option>
                <option value=""><?php esc_html_e( "Don't delete It.", "wp-bulk-delete" ); ?> </option>
            </select>
            <br/>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Post Delete Type ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <input type="radio" id="delete_type" name="delete_type" class="delete_type" value="trash" checked="checked"/>
            <?php esc_html_e( 'Move to Trash', 'wp-bulk-delete'  ); ?>
            &nbsp;&nbsp;<input type="radio" id="delete_type" name="delete_type" class="delete_type" value="permenant" />
            <?php esc_html_e( 'Delete permanently', 'wp-bulk-delete'  ); ?>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Authors ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <?php $args = array(
                    'orderby'      => 'display_name',
                    'order'        => 'ASC',
                    'fields'       => array( 'display_name', 'ID'),
            );
            $authors = get_users( $args );
            if( !empty($authors) ){
                ?>
                    <select name="delete_authors[]" class="wpbd_global_multiple_select" id="wpdb_post_author" multiple>
                        <?php foreach($authors as $author){
                            ?>
                            <option value="<?php echo esc_attr( $author->ID ); ?>"><?php printf( esc_attr__( '%s', 'wp-bulk-delete' ), esc_attr__( $author->display_name, 'wp-bulk-delete' ) ) ; // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.NoEmptyStrings, WordPress.WP.I18n.NonSingularStringLiteralText ?></option>
                            <?php
                        }
                        ?>
                    </select>
                <?php
            }
            ?>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Limit ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <input type="number" min="1" id="limit_post" name="limit_post" class="limit_post_input" value="500" max="10000" />
            <span class="wpbd-tooltip" >
                <div>
                    <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                    </svg>
                    <span class="wpbd-popper">
                        <?php esc_html_e('Set a limit on the number of posts to delete. Only the first set of posts up to this limit will be deleted. This option is useful if you have a large number of posts to delete and want to avoid script timeouts.','wp-bulk-delete'); ?>
                        <div class="wpbd-popper__arrow"></div>
                    </span>
                </div>
            </span>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Custom fields settings ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <?php esc_html_e( 'Custom Fields Key', 'wp-bulk-delete' ); ?> 
            <input type="text" id="disabled_sample1" name="disabled_sample1" class="disabled_sample1" disabled="disabled" />
            <select name="disabled_sample2" disabled="disabled">
                <option value="equal_to_str"><?php esc_html_e( 'equal to ( string )', 'wp-bulk-delete' ); ?></option>
            </select>
            <?php esc_html_e( 'Value', 'wp-bulk-delete' ); ?> 
            <input type="text" id="disabled_sample3" name="disabled_sample3" class="disabled_sample3" disabled="disabled" />
            <br />
        </div>
    </div>
    <?php
}

/**
 * Render Custom Fields.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_duplicate_posts(){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Duplicate Posts ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2" >
            <input type="checkbox" id="post_media" name="" class="" />
            <span class="wpbd-tooltip" >
                <div>
                    <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                    </svg>
                    <span class="wpbd-popper">
                        <?php esc_html_e( 'It enables removing duplicate posts/pages/custom post types. This option filters out duplicate posts by post titles.', 'wp-bulk-delete' ); ?>
                        <div class="wpbd-popper__arrow"></div>
                    </span>
                </div>
            </span>
        </div>
    </div>
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
    <div class="wpbd-post-form-tbody">
        <div class="wpbd-card" >
            <div class="content"  aria-expanded="true" style=" ">
                <div class="wpbd-inner-main-section">
                    <div class="wpbd-inner-section-1" >
                        <span class="wpbd-title-text" ><?php esc_html_e('Select all Cleanups ','wp-bulk-delete'); ?></span>
                    </div>
                    <div class="wpbd-inner-section-2">
                        <div class="cleanups_section" >
                            <label for="cleanup_post_type">
                                <input class="" id="select_all" type="checkbox" >
                                <?php esc_html_e( 'Select All', 'wp-bulk-delete' ); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="wpbd-card" >
            <div class="content"  aria-expanded="true" style=" ">
                <div class="wpbd-inner-main-section">
                    <div class="wpbd-inner-section-1" >
                        <span class="wpbd-title-text" ><?php esc_html_e('Cleanup Posts ','wp-bulk-delete'); ?></span>
                    </div>
                    <div class="wpbd-inner-section-2">
                        <div class="cleanups_section" >
                            <label for="cleanup_post_type">
                                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_revision" type="checkbox" value="revision" >
                                <?php printf( esc_attr__( 'Revisions (%d Revisions)', 'wp-bulk-delete' ), esc_attr__( wpbulkdelete()->api->get_post_count('revision'), 'wp-bulk-delete' ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.NonSingularStringLiteralText ?>
                            </label>
                        </div>

                        <div class="cleanups_section" >
                            <label for="cleanup_post_type">
                                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_trash" type="checkbox" value="trash" >
                                <?php printf( esc_attr__( 'Trash (Deleted Posts) (%d Trash)', 'wp-bulk-delete' ),  esc_attr__( wpbulkdelete()->api->get_post_count('trash'), 'wp-bulk-delete' ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.NonSingularStringLiteralText ?>
                            </label>
                        </div>

                        <div class="cleanups_section" >
                            <label for="cleanup_post_type">
                                <input name="cleanup_post_type[]" class="cleanup_post_type" id="cleanup_revision" type="checkbox" value="auto_drafts" >
                                <?php printf( esc_attr__( 'Auto Drafts (%d Auto Drafts)', 'wp-bulk-delete' ),  esc_attr__( wpbulkdelete()->api->get_post_count('auto_drafts'), 'wp-bulk-delete' ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.NonSingularStringLiteralText ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Delete Time ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2">
            <input type="radio" id="delete_time_now" name="delete_time" class="delete_time" value="now" checked="checked" />
            <?php esc_html_e( 'Delete now', 'wp-bulk-delete'  ); ?><br />
            <input type="radio" id="delete_time_later" name="delete_time" class="delete_time" value="scheduled" <?php echo( ( ! wpbd_is_pro() ) ? 'disabled="disabled"' : '' ); ?>/>
            <?php esc_html_e( 'Schedule delete at', 'wp-bulk-delete'  ); ?>
            <input type="text" id="delete_datetime" name="delete_datetime" class="delete_all_datetimepicker" placeholder="YYYY-MM-DD HH:mm:ss" <?php echo( ( ! wpbd_is_pro() ) ? 'disabled="disabled"' : '' ); ?>/>
            <?php 
            esc_html_e( 'repeat', 'wp-bulk-delete'  );
            wpbd_render_import_frequency();
            do_action( 'wpbd_display_available_in_pro');
            $timezone = wpbd_get_timezone_string();
            ?>
            <div>
                <strong><?php printf( esc_html__( 'Timezone: (%s)', 'wp-bulk-delete' ), esc_attr__( $timezone, 'wp-bulk-delete' ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.NonSingularStringLiteralText ?></strong>
                <span class="wpbd-tooltip" >
                    <div>
                        <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                        </svg>
                        <span class="wpbd-popper">
                            <?php esc_html_e('Scheduled deletions use cron jobs and background processes, making them ideal for handling large volumes of records or performing repetitive deletions','wp-bulk-delete'); ?>
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
        <option value='tenminutes' <?php selected( $selected, 'tenminutes' ); ?>>
            <?php esc_html_e( '10 Minutes', 'wp-bulk-delete' ); ?>
        </option>
        <option value='halfhour' <?php selected( $selected, 'halfhour' ); ?>>
            <?php esc_html_e( '30 Minutes', 'wp-bulk-delete' ); ?>
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
    <?php esc_html_e( 'Save it as ', 'wp-bulk-delete' ); ?>
    <input type="text" name="schedule_name" placeholder="<?php esc_html_e( 'eg: Daily Post Delete', 'wp-bulk-delete' ); ?>" class="wpbd_schedule_name"/>
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
function wpbd_render_common_form() {
    global $wpdb;
    ?>
    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Basic Filter ','wp-bulk-delete'); ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret rotated">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="true" style="">
            <?php 
                wpbd_render_form_posttype();
                wpbd_render_form_poststatus();
                
            ?>
        </div>
    </div> 

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Advanced Category Filter ','wp-bulk-delete'); ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display: none;">
            <?php 
                wpbd_render_form_taxonomy();
                wpbd_render_extra_assinged_category();
            ?>
        </div>
    </div>

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Author Filter ','wp-bulk-delete'); ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display: none;">
            <?php 
                wpbd_render_form_users();
            ?>
        </div>
    </div>

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Advanced Date Filter ','wp-bulk-delete'); ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display: none;">
            <?php
                wpbd_render_form_date_interval();
                wpbd_render_form_modified_interval();                
            ?>
        </div>
    </div>

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Delete Post Feature Image Filter ','wp-bulk-delete');  if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display: none;">
            <?php 
                if( wpbd_is_pro()  ){
                    wpbd_render_form_delete_media();
                }else{
                    ?>
                        <div class="wpbd-blur-filter" >
                        <div class="wpbd-blur" >
                            <div class="wpbd-blur-filter-option">
                                <?php
                                    wpbd_render_form_delete_media();
                                ?>
                            </div>
                        </div>
                        <div class="wpbd-blur-filter-cta" >
                            <span style="color: red"><?php echo esc_html_e( 'Available in Pro version.', 'wp-bulk-delete' ); ?> </span><a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>"><?php echo esc_html_e( 'Buy Now', 'wp-bulk-delete' ); ?></a>
                        </div>
                    </div>
                    <?php
                }
            ?>

        </div>
    </div>

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Custom Field Filter ','wp-bulk-delete'); if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display: none;">
            <?php 
                if( !wpbd_is_pro() ){
                    ?>
                    <div class="wpbd-blur-filter" >
                        <div class="wpbd-blur" >
                            <div class="wpbd-blur-filter-option">
                                <?php
                                    wpbd_render_form_custom_fields();
                                ?>
                            </div>
                        </div>
                        <div class="wpbd-blur-filter-cta" >
                            <span style="color: red"><?php echo esc_html_e( 'Available in Pro version.', 'wp-bulk-delete' ); ?> </span><a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>"><?php echo esc_html_e( 'Buy Now', 'wp-bulk-delete' ); ?></a>
                        </div>
                    </div>
                    <?php
                }else{
                    wpbd_render_form_custom_fields_pro();
                }
            ?>
        </div>
    </div>

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Post IDs Filter ','wp-bulk-delete'); if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display: none;">
            <?php 
                if( !wpbd_is_pro() ){
                    ?>
                    <div class="wpbd-blur-filter" >
                        <div class="wpbd-blur" >
                            <div class="wpbd-blur-filter-option">
                                <?php
                                    wpbd_render_form_post_ids();
                                ?>
                            </div>
                        </div>
                        <div class="wpbd-blur-filter-cta" >
                            <span style="color: red"><?php echo esc_html_e( 'Available in Pro version.', 'wp-bulk-delete' ); ?> </span><a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>"><?php echo esc_html_e( 'Buy Now', 'wp-bulk-delete' ); ?></a>
                        </div>
                    </div>
                    <?php
                }else{
                    wpbd_render_form_post_ids_pro();
                }
            ?>
        </div>
    </div>

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Duplicate Posts Filter ','wp-bulk-delete'); if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display: none;">
            <?php 
                if( !wpbd_is_pro() ){
                    ?>
                    <div class="wpbd-blur-filter" >
                        <div class="wpbd-blur" >
                            <div class="wpbd-blur-filter-option">
                                <?php
                                    wpbd_render_form_duplicate_posts();
                                ?>
                            </div>
                        </div>
                        <div class="wpbd-blur-filter-cta" >
                            <span style="color: red"><?php echo esc_html_e( 'Available in Pro version.', 'wp-bulk-delete' ); ?> </span><a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>"><?php echo esc_html_e( 'Buy Now', 'wp-bulk-delete' ); ?></a>
                        </div>
                    </div>
                    <?php
                }else{
                    wpbd_render_form_duplicate_posts_pro();
                }
            ?>
        </div>
    </div>

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Advanced Filter ','wp-bulk-delete'); if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display: none;">
            <?php 
                if( !wpbd_is_pro() ){
                    ?>
                    <div class="wpbd-blur-filter" >
                        <div class="wpbd-blur" >
                            <div class="wpbd-blur-filter-option">
                                <?php
                                    wpbd_render_form_post_contains();
                                    do_action( 'render_form_by_charector_count' );
                                ?>
                            </div>
                        </div>
                        <div class="wpbd-blur-filter-cta" >
                            <span style="color: red"><?php echo esc_html_e( 'Available in Pro version.', 'wp-bulk-delete' ); ?> </span><a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>"><?php echo esc_html_e( 'Buy Now', 'wp-bulk-delete' ); ?></a>
                        </div>
                    </div>
                    <?php
                }else{
                    wpbd_render_form_post_contains_pro();
                    do_action( 'render_form_by_charector_count_pro' );
                }
            ?>
        </div>
    </div>

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Custom Query Filter ','wp-bulk-delete'); ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display: none;">
            <?php                  
                wpbd_render_form_custom_query();
            ?>
        </div>
    </div>

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Action ','wp-bulk-delete'); ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret rotated">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="true" style="">
            <?php
                wpbd_render_limit_post();
                wpbd_render_form_delete_type();
                wpbd_render_delete_time();
            ?>
        </div>
    </div>
    <?php
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

/**
 * Render Userroles checkboxes.
 *
 * @since 1.2.7
 * @return void
 */
function wpdb_render_delete_users_postlinks(){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Post Links ','wp-bulk-delete'); ?></span>
        </div>
        <div class="wpbd-inner-section-2" style="display: flex;flex-direction: row;flex-wrap: nowrap;align-items: center;gap: 5px;">
            <select name="" disabled="disabled" >
                <option value=""><?php esc_html_e( 'equal to ( string )', 'wp-bulk-delete' ); ?></option>
                <option value=""><?php esc_html_e( 'not equal to ( string )', 'wp-bulk-delete' ); ?></option>
            </select>
            <textarea name="" disabled="disabled"  id="" cols="70" style="height: 30px;" class="" placeholder="You can add multiple post links with comma(,) separator" ></textarea>
        </div>
    </div>
    <?php
}

/* Render Delete Post Media.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_form_delete_media(){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" ><?php esc_html_e('Delete Post Featured image ','wp-bulk-delete'); ?></span>
        </div>
        <?php if( wpbd_is_pro() ){ ?>
            <div class="wpbd-inner-section-2" >
                <input type="checkbox" id="post_media" name="post_media" class="post_media" value="yes" />
                <span class="wpbd-tooltip" >
                    <div>
                        <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                        </svg>
                        <span class="wpbd-popper">
                            <?php esc_html_e( 'It enables the removal of the featured image of the post, if the image is a featured image of multiple posts, it will not be removed. and If the image is being used in a place other than the featured image, it will be deleted.', 'wp-bulk-delete'  ); ?>
                            <div class="wpbd-popper__arrow"></div>
                        </span>
                    </div>
                </span>
            </div>
        <?php }else{ ?>
            <div>
                <?php do_action( 'wpbd_display_available_in_pro'); ?>
            </div>
        <?php } ?>
    </div>
    <?php
}