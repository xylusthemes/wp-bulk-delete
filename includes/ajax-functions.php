<?php
/**
 * Ajax Functions
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Ajax Functions
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Render Taxonomy based on Post type Selection.
 *
 * @since 1.0
 * @param string $hook Page hook
 * @return void
 */
function wpbd_delete_posts_count() {
	$data = $error = $return = array();
	parse_str($_POST['form'], $data);
	if( ! empty( $data ) ){
		
		if ( ! current_user_can( 'manage_options' ) ) {
	        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
	    }

	    if ( isset( $data['_delete_all_posts_wpnonce'] ) && wp_verify_nonce( $data['_delete_all_posts_wpnonce'], 'delete_posts_nonce' ) ) {

	    	if( empty( $error ) ){
	    		
	    		// Get post_ids for delete based on user input.
		        $post_ids = wpbulkdelete()->api->get_delete_posts_ids( $data );
	    		
	    		if ( ! empty( $post_ids ) && count( $post_ids ) > 0 ) {
	    			$return = array(
		    			'status' => 1,
		    			'post_count' => count( $post_ids ),
		    		);
	            } else {                
	                $return = array(
		    			'status' => 2,
		    			'messages' => array( esc_html__( 'Nothing to delete!!', 'wp-bulk-delete' ) ),
		    		);
	            }

	    	} else {
	    		$return = array(
	    			'status' => 0,
	    			'messages' => $error[0],
	    		);
	    	}

	    } else {
	    	$error[] = esc_html__('Sorry, Your nonce did not verify.', 'wp-bulk-delete' );
	    	$return = array(
    			'status' => 0,
    			'messages' => $error[0],
    		);
		}
	}
	echo json_encode( $return );
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_delete_posts_count', 'wpbd_delete_posts_count' );


/**
 * Render Taxonomy based on Post type Selection.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_taxonomy_by_posttype() {

	$post_type  = $_REQUEST['post_type'];
	$taxonomies = array();
	if ( $post_type != '' ) {
		$taxonomies = wpbd_get_taxonomy_by_posttype( $post_type );
	}
	if( ! empty( $taxonomies ) ){
		foreach ($taxonomies as $slug => $name ) {
			?>
			<input type="radio" name="post_taxonomy" value="<?php echo $slug;?>" class="post_taxonomy_radio" title="<?php echo $name; ?>"><?php echo $name; ?> <br />
			<?php	
		}		
	}
	wp_die();
}
add_action( 'wp_ajax_render_taxonomy_by_posttype', 'wpbd_render_taxonomy_by_posttype' );


/**
 * Render Taxonomy Terms based on Texonomy Selection.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_terms_by_taxonomy() {

	$post_taxo  = esc_attr( $_REQUEST['post_taxomony'] );
	$terms = array();
	if ( $post_taxo != '' ) {
		if( taxonomy_exists( $post_taxo ) ){
			$terms = get_terms( $post_taxo, array( 'hide_empty' => false ) );
		}
	}
	if( ! empty( $terms ) ){
		foreach ($terms as $term ) {
			?>
			<input type="checkbox" name="post_taxonomy_terms[]" value="<?php echo $term->term_id;?>" class="post_taxonomy_terms" ><?php echo $term->name; ?> <br />
			<?php	
		}		
	}
	wp_die();
}
add_action( 'wp_ajax_render_terms_by_taxonomy', 'wpbd_render_terms_by_taxonomy' );
