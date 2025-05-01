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
add_action( 'wpbd_delete_comments_form', 'wpdb_render_delete_comments_type' );
add_action( 'wpbd_delete_comments_form', 'wpdb_render_delete_comments_users' );
add_action( 'wpbd_delete_comments_form', 'wpdb_render_delete_comments_posts' );
add_action( 'wpbd_delete_comments_form', 'wpdb_render_delete_comments_date_interval' );
add_action( 'wpbd_delete_comments_form', 'wpdb_render_delete_comments_limit' );

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
            $delete_datetime = isset( $data['delete_datetime'] ) ? $data['delete_datetime'] : '';
            if( $delete_time === 'scheduled' && !empty($delete_datetime) && wpbd_is_pro() ) {
                $data['delete_entity'] = 'comment';
                return wpbd_save_scheduled_delete( $data );
            }
    		
            $comment_count = wpbulkdelete()->api->do_delete_comments( $data );
            if( false === $comment_count ){
                return array(
                    'status' => 0,
                    'messages' => array( esc_html__( 'Something went wrong please try again!!', 'wp-bulk-delete' ) ),
                );
            }

    		if ( ! empty( $comment_count ) && $comment_count > 0 ) {
    			return  array(
	    			'status' => 1,
                    // translators: %d = number of comments deleted
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
    global $wpdb;
    $comment_status = array(
        'moderated' => esc_attr__( 'Pending Comments', 'wp-bulk-delete'),
        'spam' => esc_attr__( 'Spam Comments', 'wp-bulk-delete'),
        'trash' => esc_attr__( 'Trash Comments', 'wp-bulk-delete'),
        'approved' => esc_attr__( 'Approved Comments', 'wp-bulk-delete'),
    );

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
        <div class="content"  aria-expanded="true" >
            <div class="wpbd-inner-main-section">
                <div class="wpbd-inner-section-1" >
                    <span class="wpbd-title-text" >
                        <?php esc_html_e('Comment Status ','wp-bulk-delete'); ?>
                        <span class="wpbd-tooltip">
                            <div>
                                <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                                </svg>
                                <span class="wpbd-popper">
                                    <?php esc_html_e('Select the comment statuses that you want to delete.','wp-bulk-delete'); ?>
                                    <div class="wpbd-popper__arrow"></div>
                                </span>
                            </div>
                        </span>
                    </span>
                </div>
                <div class="wpbd-inner-section-2">
                    <?php
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                        $get_counts = $wpdb->get_results( "SELECT comment_approved AS status, COUNT(*) AS count FROM {$wpdb->comments} GROUP BY comment_approved" );
                        
                        $comment_status_counts = array();
                        // Loop through the results to build a dynamic array
                        if ( ! empty( $get_counts ) ) {
                            foreach ( $get_counts as $status_data ) {
                                $status_data->status = ($status_data->status == '0') ? 'moderated' : (($status_data->status == '1') ? 'approved' : $status_data->status);
                                $comment_status_counts[ $status_data->status ] = intval( $status_data->count );
                            }
                        }

                        if( ! empty( $comment_status ) ){
                            foreach ($comment_status as $comment_status_value => $comment_status_name ) {
                                ?>
                                <div>
                                    <input name="delete_comment_status[]" class="delete_comment_status" id="comment_status_<?php echo esc_attr( $comment_status_value ); ?>" type="checkbox" value="<?php echo esc_attr( $comment_status_value ); ?>" >
                                    <span for="comment_status_<?php echo esc_attr( $comment_status_value ); ?>">
                                    <?php echo esc_attr( $comment_status_name ) . ' ' . sprintf( esc_attr__( '( %s Comment(s) )', 'wp-bulk-delete' ), isset( $comment_status_counts[$comment_status_value] ) ? esc_attr( $comment_status_counts[$comment_status_value] ) : '0' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.MissingTranslatorsComment	 ?>
                                    </span>
                                </div>
                            <?php
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}


/**
 * Render Comment Type Dropdown.
 *
 * @since 1.1.0
 * @return void
 */
function wpdb_render_delete_comments_type(){
    global $wpdb;
    ?>
    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Comment Type ','wp-bulk-delete');  if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display:none;" >
            <?php 
                if( wpbd_is_pro() && class_exists( 'WP_Bulk_Delete_Pro_Common' ) ){
                        $wpdb->common_pro->wpdb_render_delete_comments_types_pro();
                }else{
                    ?>
                        <div class="wpbd-blur-filter" >
                            <div class="wpbd-blur" >
                                <div class="wpbd-blur-filter-option">
                                    <?php
                                        wpdb_render_delete_comments_types();
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
    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Date Filter ','wp-bulk-delete'); ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="true" style="display:none;" >
            <div class="wpbd-inner-main-section">
                <div class="wpbd-inner-section-1" >
                    <span class="wpbd-title-text" >
                        <?php esc_html_e('Comment Date ','wp-bulk-delete'); ?>
                    </span>
                </div>
                <div class="wpbd-inner-section-2">
                    <?php esc_html_e('Delete Comments which are','wp-bulk-delete'); ?> 
                    <select name="date_type" class="date_type">
                        <option value="older_than"><?php esc_html_e('older than','wp-bulk-delete'); ?></option>
                        <option value="within_last"><?php esc_html_e('submitted within last','wp-bulk-delete'); ?></option>
                        <?php if( wpbd_is_pro() ) { ?>
                            <option value="onemonth"><?php esc_html_e('1 Month','wp-bulk-delete'); ?></option>
                            <option value="sixmonths"><?php esc_html_e('6 Months','wp-bulk-delete'); ?></option>
                            <option value="oneyear"><?php esc_html_e('1 Year','wp-bulk-delete'); ?></option>
                            <option value="twoyear"><?php esc_html_e('2 Years','wp-bulk-delete'); ?></option>
                        <?php } ?>
                        <option value="custom_date"><?php esc_html_e('submitted between custom','wp-bulk-delete'); ?></option>
                    </select>
                    <div class="wpbd_date_days wpbd_inline">
                        <input type="number" id="input_days" name="input_days" class="wpbd_input_days" placeholder="0" min="0" /> <?php esc_html_e('days','wp-bulk-delete'); ?>
                    </div>
                    <div class="wpbd_custom_interval wpbd_inline" style="display:none;">
                        <input type="text" id="delete_start_date" name="delete_start_date" class="delete_all_datepicker" placeholder="<?php esc_html_e('Start Date','wp-bulk-delete'); ?>" />
                        -
                        <input type="text" id="delete_end_date" name="delete_end_date" class="delete_all_datepicker" placeholder="<?php esc_html_e('End Date','wp-bulk-delete'); ?>" />
                        <span class="wpbd-tooltip">
                            <div>
                                <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                                </svg>
                                <span class="wpbd-popper">
                                    <?php 
                                        // translators: %s: date format.
                                        $text = esc_html__('Set the date interval for comments to delete ( only delete comments between these dates ) or leave these fields blank to select all comments. The dates must be specified in the following format: %s', 'wp-bulk-delete'); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
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
                        <span class="wpbd-tooltip">
                            <div>
                                <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                                </svg>
                                <span class="wpbd-popper">
                                    <?php esc_html_e('This option will work well with Scheduled Delete, which will help to delete comments of the selected option from the scheduled run date.','wp-bulk-delete'); ?>
                                    <div class="wpbd-popper__arrow"></div>
                                </span>
                            </div>
                        </span>
                    </div> 
                </div>
            </div>
        </div>
    </div>
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

    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Author Filter ','wp-bulk-delete');  if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display:none;" >
            <?php 
                if( wpbd_is_pro() && class_exists( 'WP_Bulk_Delete_Pro_Common' ) ){
                        $wpdb->common_pro->wpbd_render_delete_comment_author_pro();
                }else{
                    ?>
                        <div class="wpbd-blur-filter" >
                            <div class="wpbd-blur" >
                                <div class="wpbd-blur-filter-option">
                                    <?php
                                        wpbd_render_delete_comment_author();
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
    <div class="wpbd-card" >
        <div class="header toggles" >
            <div class="text" >
                <div class="header-icon" ></div>
                <div class="header-title" >
                    <span><?php esc_html_e('Post Filter ','wp-bulk-delete');  if( !wpbd_is_pro() ){ echo '<div class="wpbd-pro-badge"> PRO </div>'; } ?></span>
                </div>
                <div class="header-extra" ></div>
            </div>
            <svg viewBox="0 0 24 24" fill="none"
                xmlns="http://www.w3.org/2000/svg" class="wpbd-caret">
                <path d="M16.59 8.29492L12 12.8749L7.41 8.29492L6 9.70492L12 15.7049L18 9.70492L16.59 8.29492Z" fill="currentColor"></path>
            </svg>
        </div>
        <div class="content"  aria-expanded="false" style="display:none;" >
            <?php 
                if( wpbd_is_pro() && class_exists( 'WP_Bulk_Delete_Pro_Common' ) ){
                        $wpdb->common_pro->wpbd_render_delete_comment_posts_pro();
                }else{
                    ?>
                        <div class="wpbd-blur-filter" >
                            <div class="wpbd-blur" >
                                <div class="wpbd-blur-filter-option">
                                    <?php
                                        wpbd_render_delete_comment_posts();
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
    <?php
}

/**
 * Render Comments limit.
 *
 * @since 1.0
 * @return void
 */
function wpdb_render_delete_comments_limit(){
    ?>


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
        <div class="content"  aria-expanded="true" style="" >
            <div class="wpbd-inner-main-section">
                <div class="wpbd-inner-section-1" >
                    <span class="wpbd-title-text" >
                        <?php esc_html_e('Limit ','wp-bulk-delete'); ?>
                    </span>
                </div>
                <div class="wpbd-inner-section-2">
                    <input type="number" min="1" id="limit_comment" name="limit_comment" class="limit_comment_input" max="5000" value="1000" />
                    <span class="wpbd-tooltip" >
                        <div>
                            <svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                            </svg>
                            <span class="wpbd-popper">
                                <?php esc_html_e('Set the limit over comments delete. It will delete only the first limit comments. This option will help you in case you have lots of comments to delete and script timeout.','wp-bulk-delete'); ?>
                                <div class="wpbd-popper__arrow"></div>
                            </span>
                        </div>
                    </span>
                </div>
            </div>
            <?php
                wpbd_render_delete_time();
            ?>
        </div>
    </div>

    <?php
}


/**
 * Render Comment Author
 */
function wpbd_render_delete_comment_author(){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" >
                <?php esc_html_e('Comment Author ','wp-bulk-delete'); ?>
            </span>
        </div>
        <div class="wpbd-inner-section-2">
            <select name="sample1" class="comment_author" disabled="disabled" >
                <option value=""><?php esc_attr_e( 'Select author', 'wp-bulk-delete' ); ?></option>
            </select>
        </div>
    </div>
    <?php
}

/**
 * Render Comment Author
 */
function wpdb_render_delete_comments_types(){
    ?>
        <div class="wpbd-inner-main-section">
            <div class="wpbd-inner-section-1" >
                <span class="wpbd-title-text" >
                    <?php esc_html_e('Comment Type ','wp-bulk-delete'); ?>
                </span>
            </div>
            <div class="wpbd-inner-section-2">
                <select name="sample1" class="comment_author" disabled="disabled" >
                    <option value=""><?php esc_attr_e( 'Select Comment Type', 'wp-bulk-delete' ); ?></option>
                </select>
            </div>
        </div>
    <?php
}

/**
 * Render Comment Posts
 */
function wpbd_render_delete_comment_posts(){
    ?>
    <div class="wpbd-inner-main-section">
        <div class="wpbd-inner-section-1" >
            <span class="wpbd-title-text" >
                <?php esc_html_e('Comment Posts ','wp-bulk-delete'); ?>
            </span>
        </div>
        <div class="wpbd-inner-section-2">
            <select  name="sample2" class="comment_author" disabled="disabled" >
                <option value=""><?php esc_attr_e( 'Select post', 'wp-bulk-delete' ); ?></option>
            </select>
        </div>
    </div>
    <?php
}