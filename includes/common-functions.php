<?php
/**
 * Common functions
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Common functions
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get Taxomomy from posttype.
 *
 * @since 1.0
 * @param string $post_type Post Type
 * @return array | taxonomy array
 */
function wpbd_get_taxonomy_by_posttype( $post_type = '' ) {

	$taxonomies = array();
	$ingnore_taxonomy = array( 'post_format' );
	if ( $post_type != '' ) {
		$taxonomy_objects = get_object_taxonomies( $post_type, 'objects' );
		if( !empty( $taxonomy_objects ) ){
			foreach( $taxonomy_objects as $slug => $taxonomy ){
				if( in_array( $slug, $ingnore_taxonomy ) ){
					continue;
				}else{
					$taxonomies[$slug] = $taxonomy->labels->name;
				}
			}
		}
	}
	return $taxonomies;	
}

/**
 * Get Taxomomy from posttype.
 *
 * @since 1.0
 * @param string $posttype Post Type
 * @return array | taxonomy array
 */
function wpbd_get_terms_by_taxonomy( $taxonomy = '' ) {
	$terms = array();
	if ( $taxonomy != '' ) {
		if( taxonomy_exists( $taxonomy ) ){
			$terms = get_terms( array( 'taxonomy'   => $taxonomy, 'hide_empty' => true, ) );
		}
	}
	return $terms;	
}

/**
 * Display Admin Notices
 *
 * @since 1.0
 * @param array $notice_result Status array
 * @return void
 */
function wpbd_display_admin_notice( $notice_result = array() ) {

	if ( ! empty( $notice_result ) && $notice_result['status'] == 1 ){
        if( !empty( $notice_result['messages'] ) ){
            foreach ( $notice_result['messages'] as $smessages ) {
                ?>
                <div class="notice wpbd-notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__( $smessages, 'wp-bulk-delete' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?></strong></p>
                </div>
                <?php
            }
        }  
    } elseif ( ! empty( $notice_result ) && $notice_result['status'] == 0 ){

        if( !empty( $notice_result['messages'] ) ){
            foreach ( $notice_result['messages'] as $emessages ) {
                ?>
                <div class="notice wpbd-notice notice-error is-dismissible">
                    <p><strong><?php echo esc_html__( $emessages, 'wp-bulk-delete' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?></strong></p>
                </div>
                <?php
            }
        }
    }
}

/**
 * Display Admin Notices
 *
 * @since 1.0
 * @param array $notice_result Status array
 * @return void
 */
function wpbd_display_available_in_pro() {
	if( !wpbd_is_pro() ) {
		?>
		<span style="color: red"><?php esc_html_e('Available in Pro version.','wp-bulk-delete'); ?></span>
		<a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>"><?php esc_html_e('Buy Now','wp-bulk-delete'); ?></a>
		<?php
	}
}
add_action( 'wpbd_display_available_in_pro', 'wpbd_display_available_in_pro' );


/**
 * Check timeout and memory limit
 *
 * @since  1.2.4
 * @return boolean
 */
function timeout_memory_limit_is_enough() {
	$memory_limit  = str_replace( 'M', '', ini_get('memory_limit') );
	$timeout_limit = ini_get( 'max_execution_time' );
	if( $memory_limit < '512' ){
		?>
		<div class="notice wpbd-notice notice-warning is-dismissible">
			<p><strong><?php esc_html_e( 'Attention: The server PHP memory limit is set to '.$memory_limit.'M, which is less than the recommended 512M. This may cause slow deletion progress if deleting large data.', 'wp-bulk-delete' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?></strong></p>
		</div>
		<?php
	}
	if( $timeout_limit < '300' ){
		?>
		<div class="notice wpbd-notice notice-warning is-dismissible">
		<p><strong><?php esc_html_e( 'Attention: The server PHP timeout limit is set to '.$timeout_limit.' seconds, which is less than the recommended 300 seconds. This may cause slow deletion progress if deleting large data.', 'wp-bulk-delete' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?></strong></p>
		</div>
		<?php
	}
	return false;
}
add_action( 'timeout_memory_is_enough', 'timeout_memory_limit_is_enough' );


add_action('admin_post_wpbd_delete_post', 'handle_delete_posts');
function handle_delete_posts() {
	$delete_time = isset( $_POST['delete_time'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['delete_time'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
	if ( $delete_time === 'scheduled' ) {
		if( isset( $_POST['_delete_all_actions_wpnonce'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
			$result = xt_delete_posts_form_process( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if( isset( $_POST['_delete_comments_wpnonce'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
			$result = xt_delete_comments_form_process( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if( isset( $_POST['_delete_terms_wpnonce'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
			$result = xt_delete_terms_form_process( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if( isset( $_POST['_delete_users_wpnonce'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
			$result = xt_delete_users_form_process( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		$message = $result['status'] === 1 ? 'Scheduled delete was created successfully.' : 'Error in scheduled delete.';
		wp_safe_redirect( admin_url( 'admin.php?page=delete_all_actions&message=' . $message ).'&tab=by_schedule-delete' );
	}
}

/**
 * Return post count from posttype
 *
 * @since 1.0
 * @return void
 */
function wpbd_get_posttype_post_count( $posttype ){
	if( $posttype != '' ){
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = %s AND `post_status` NOT IN ('trash', 'auto-draft')", esc_attr( $posttype ) ) );
		return $count;
	}
	return 0;
}

/**
 * Save Scheduled delete
 *
 * @param {Array} $data
 * @return {Array}
 */
function wpbd_save_scheduled_delete($data){
	$scheduled = false;
	$delete_datetime = ( $data['delete_datetime'] ) ? $data['delete_datetime'] : '';
    $delete_frequency = ( $data['delete_frequency'] ) ? $data['delete_frequency'] : 'not_repeat';
	$cron_time = strtotime($delete_datetime) - (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	$title = !empty( $data['schedule_name'] ) ? $data['schedule_name'] : __( 'Scheduled Delete - ', 'wp-bulk-delete' ) . ucfirst($data['delete_entity']);
	
	if( $delete_frequency === 'not_repeat' ){
		$insert_args = array(
			'post_type'   => 'wpbd_scheduled',
			'post_status' => 'publish',
			'post_title'  => $title,
		);

		$insert = wp_insert_post( $insert_args, true );
		if ( is_wp_error( $insert ) ) {
			return array(
				'status' => 0,
				'messages' => array( esc_html__( 'Something went wrong when saving scheduled delete.', 'wp-bulk-delete' ) ),
			);
		}
		$data['wpbd_scheduled_id'] = $insert;
		update_post_meta( $insert, 'delete_options', $data );
		$scheduled = wp_schedule_single_event($cron_time, 'wpbd_run_scheduled_delete', array('post_id' => $insert ) );
	} else {
		$insert_args = array(
			'post_type'   => 'wpbd_scheduled',
			'post_status' => 'publish',
			'post_title'  => $title,
		);

		$insert = wp_insert_post( $insert_args, true );
		if ( is_wp_error( $insert ) ) {
			return array(
				'status' => 0,
				'messages' => array( esc_html__( 'Something went wrong when saving scheduled delete.', 'wp-bulk-delete' ) ),
			);
		}
		$data['wpbd_scheduled_id'] = $insert;
		update_post_meta( $insert, 'delete_options', $data );
		$scheduled = wp_schedule_event( $cron_time, $delete_frequency, 'wpbd_run_scheduled_delete', array('post_id' => $insert));
	}
	if( $scheduled) {
		return  array(
			'status' => 1,
			'messages' => array( esc_html__( 'Delete scheduled successfully.', 'wp-bulk-delete' ) )
		);
	}else{
		return array(
			'status' => 0,
			'messages' => array( esc_html__( 'Error in scheduled delete.', 'wp-bulk-delete' ) ),
		);
	}
}

/**
 * Function only for debuging
 *
 * @since 1.1
 */
function wp_p( $data, $exit = false ){

	echo '<pre>';
	if ( is_array( $data ) || is_object( $data ) ){
		print_r( $data ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
	} else {
		echo esc_attr( $data );
	}
	echo '</pre>';
	if ( $exit ) {
		exit();
	}

}

/**
 * Render Page Footer Section
 *
 * @since 1.1
 * @return void
 */
function wpdb_render_common_footer(){
    ?>
        <div id="wpbd-footer-links" >
            <div class="wpbd-footer">
                <div><?php esc_attr_e( 'Made with ♥ by the Xylus Themes','wp-bulk-delete'); ?></div>
                <div class="wpbd-links" >
                    <a href="<?php echo esc_url( 'https://xylusthemes.com/support/' ); ?>" target="_blank" ><?php esc_attr_e( 'Support','wp-bulk-delete'); ?></a>
                    <span>/</span>
                    <a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/wp-bulk-delete/' ); ?>" target="_blank" ><?php esc_attr_e( 'Docs','wp-bulk-delete'); ?></a>
                    <span>/</span>
                    <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=xylus&tab=search&type=term' ) ); ?>" ><?php esc_attr_e( 'Free Plugins','wp-bulk-delete'); ?></a>
                </div>
                <div class="wpbd-social-links">
                    <a href="<?php echo esc_url( 'https://www.facebook.com/xylusinfo/' ); ?>" target="_blank" >
                        <svg class="wpbd-facebook">
                            <path fill="currentColor" d="M16 8.05A8.02 8.02 0 0 0 8 0C3.58 0 0 3.6 0 8.05A8 8 0 0 0 6.74 16v-5.61H4.71V8.05h2.03V6.3c0-2.02 1.2-3.15 3-3.15.9 0 1.8.16 1.8.16v1.98h-1c-1 0-1.31.62-1.31 1.27v1.49h2.22l-.35 2.34H9.23V16A8.02 8.02 0 0 0 16 8.05Z"></path>
                        </svg>
                    </a>
                    <a href="<?php echo esc_url( 'https://www.linkedin.com/company/xylus-consultancy-service-xcs-/' ); ?>" target="_blank" >
                        <svg class="wpbd-linkedin">
                            <path fill="currentColor" d="M14 1H1.97C1.44 1 1 1.47 1 2.03V14c0 .56.44 1 .97 1H14a1 1 0 0 0 1-1V2.03C15 1.47 14.53 1 14 1ZM5.22 13H3.16V6.34h2.06V13ZM4.19 5.4a1.2 1.2 0 0 1-1.22-1.18C2.97 3.56 3.5 3 4.19 3c.65 0 1.18.56 1.18 1.22 0 .66-.53 1.19-1.18 1.19ZM13 13h-2.1V9.75C10.9 9 10.9 8 9.85 8c-1.1 0-1.25.84-1.25 1.72V13H6.53V6.34H8.5v.91h.03a2.2 2.2 0 0 1 1.97-1.1c2.1 0 2.5 1.41 2.5 3.2V13Z"></path>
                        </svg>
                    </a>
                    <a href="<?php echo esc_url( 'https://x.com/XylusThemes" target="_blank' ); ?>" target="_blank" >
                        <svg class="wpbd-twitter" width="24" height="24" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="12" fill="currentColor"></circle>
                            <g>
                                <path d="M13.129 11.076L17.588 6H16.5315L12.658 10.4065L9.5665 6H6L10.676 12.664L6 17.9865H7.0565L11.1445 13.332L14.41 17.9865H17.9765L13.129 11.076ZM11.6815 12.7225L11.207 12.0585L7.4375 6.78H9.0605L12.1035 11.0415L12.576 11.7055L16.531 17.2445H14.908L11.6815 12.7225Z" fill="white"></path>
                            </g>
                        </svg>
                    </a>
                    <a href="<?php echo esc_url( 'https://www.youtube.com/@xylussupport7784' ); ?>" target="_blank" >
                        <svg class="wpbd-youtube">
                            <path fill="currentColor" d="M16.63 3.9a2.12 2.12 0 0 0-1.5-1.52C13.8 2 8.53 2 8.53 2s-5.32 0-6.66.38c-.71.18-1.3.78-1.49 1.53C0 5.2 0 8.03 0 8.03s0 2.78.37 4.13c.19.75.78 1.3 1.5 1.5C3.2 14 8.51 14 8.51 14s5.28 0 6.62-.34c.71-.2 1.3-.75 1.49-1.5.37-1.35.37-4.13.37-4.13s0-2.81-.37-4.12Zm-9.85 6.66V5.5l4.4 2.53-4.4 2.53Z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    <?php   
}

function wpbd_render_common_notice(){
	$get_posts   = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
	if( !empty( $get_posts ) ){
		if( isset( $get_posts['delete_post_type'] ) ){
			$delete_time = isset( $get_posts['delete_time'] ) ? $get_posts['delete_time'] : '';
			if ( $delete_time !== 'scheduled' && isset( $get_posts['_delete_all_actions_wpnonce'] ) ) {
				$post_result = xt_delete_posts_form_process( $get_posts );
				wpbd_display_admin_notice( $post_result );
			}
		}

		if( isset( $get_posts['_delete_comments_wpnonce'] ) ){
			$comment_result = xt_delete_comments_form_process( $get_posts );
			wpbd_display_admin_notice( $comment_result );
		}

		if( isset( $get_posts['_delete_terms_wpnonce'] ) ){
			$terms_result = xt_delete_terms_form_process( $get_posts );
			wpbd_display_admin_notice( $terms_result );
		}

		if( isset( $get_posts['_delete_users_wpnonce'] ) ){
			$user_result = xt_delete_users_form_process( $get_posts );
			wpbd_display_admin_notice( $user_result );
		}
	}

	if( !empty( $_GET['message'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$get_message      = sanitize_text_field( wp_unslash( $_GET['message'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$schedule_message = array( 'status' => 1, 'messages' => array( esc_html__( $get_message, 'wp-bulk-delete' ) ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		wpbd_display_admin_notice( $schedule_message );
	}
}
add_action( 'delete_pctu_notice', 'wpbd_render_common_notice', 100 );


/**
 * Render Page header Section
 *
 * @since 1.1
 * @return void
 */
function wpdb_render_common_header( $page_title  ){
    ?>
    <div class="wpbd-header" >
        <div class="wpbd-container" >
            <div class="wpbd-header-content" >
                <span style="font-size:18px;"><?php esc_html_e('Dashboard','wp-bulk-delete'); ?></span>
                <span class="spacer"></span>
                <span class="page-name"><?php esc_html_e( $page_title,'wp-bulk-delete');  // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?></span></span>
                <div class="header-actions" >
                    <span class="round">
                        <a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/wp-bulk-delete/' ); ?>" target="_blank">
                            <svg viewBox="0 0 20 20" fill="#000000" height="20px" xmlns="http://www.w3.org/2000/svg" class="wpbd-circle-question-mark">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
                            </svg>
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php
    
}

/**
 * Get WP Post Status
 *
 * @since 1.3.1
 * @return array
 */
function get_wp_post_status(){

	$get_post_status  = get_post_stati();
	$get_post_status  = array_filter( $get_post_status, function($key) { return strpos( $key, 'wc-' ) !== 0 && strpos( $key, 'request-' ) !== 0; }, ARRAY_FILTER_USE_KEY );
	$wpbd_post_status = array_diff_key( $get_post_status, array_flip( ['inherit', 'auto-draft'] ) );

	return $wpbd_post_status;
}