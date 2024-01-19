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
			$terms = get_terms( $taxonomy, array( 'hide_empty' => true ) );
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
                <div class="notice notice-success">
                    <p><strong><?php echo $smessages; ?></strong></p>
                </div>
                <?php
            }
        }  
    } elseif ( ! empty( $notice_result ) && $notice_result['status'] == 0 ){

        if( !empty( $notice_result['messages'] ) ){
            foreach ( $notice_result['messages'] as $emessages ) {
                ?>
                <div class="notice notice-error">
                    <p><strong><?php echo $emessages; ?></strong></p>
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
		<span style="color: red"><?php _e('Available in Pro version.','wp-bulk-delete'); ?></span>
		<a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>"><?php _e('Buy Now','wp-bulk-delete'); ?></a>
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
		<div class="notice notice-warning is-dismissible">
			<p><strong><?php _e( 'Attention: The server PHP memory limit is set to '.$memory_limit.'M, which is less than the recommended 512M. This may cause slow deletion progress if deleting large data.', 'wp-bulk-delete' ); ?></strong></p>
		</div>
		<?php
	}
	if( $timeout_limit < '300' ){
		?>
		<div class="notice notice-warning is-dismissible">
		<p><strong><?php _e( 'Attention: The server PHP timeout limit is set to '.$timeout_limit.' seconds, which is less than the recommended 300 seconds. This may cause slow deletion progress if deleting large data.', 'wp-bulk-delete' ); ?></strong></p>
		</div>
		<?php
	}
	return false;
}
add_action( 'timeout_memory_is_enough', 'timeout_memory_limit_is_enough' );

/**
 * Return post count from posttype
 *
 * @since 1.0
 * @return void
 */
function wpbd_get_posttype_post_count( $posttye ){
	if( $posttye != '' ){
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = %s AND `post_status` NOT IN ('trash', 'auto-draft')", esc_attr( $posttye ) ) );
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
		print_r( $data );
	} else {
		echo $data; 
	}
	echo '</pre>';
	if ( $exit ) {
		exit();
	}

}