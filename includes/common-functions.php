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
	?>
	<span style="color: red"><?php _e('Available in Pro version.','wp-bulk-delete'); ?></span>
	<a href="<?php echo esc_url(WPBD_PLUGIN_BUY_NOW_URL); ?>"><?php _e('Buy Now','wp-bulk-delete'); ?></a>
	<?php
}
add_action( 'wpbd_display_available_in_pro', 'wpbd_display_available_in_pro' );

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