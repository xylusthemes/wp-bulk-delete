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
 * Get delete posts count for delete confirmation.
 *
 * @since 1.0
 * @return array
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
			$terms = get_terms( $post_taxo, array( 'hide_empty' => true ) );
		}
	}
	if( ! empty( $terms ) ){
		?>
		<select name="post_taxonomy_terms[]" class="taxonomy_terms_select" multiple="multiple">
			<?php
			foreach ($terms as $term ) {
				?>
				<option value="<?php echo $term->term_id ?>"><?php echo $term->name; ?></option>
				<?php	
			} ?>
		</select>
		<?php
	}
	wp_die();
}
add_action( 'wp_ajax_render_terms_by_taxonomy', 'wpbd_render_terms_by_taxonomy' );


/**
 * Delete Users count for delete confirmation.
 *
 * @since 1.1.0
 * @return array
 */
function wpbd_delete_users_count() {
	$data = $error = $return = array();
	parse_str($_POST['form'], $data);
	if( ! empty( $data ) ){
		
		if ( ! current_user_can( 'manage_options' ) ) {
	        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
	    }

	    if ( isset( $data['_delete_users_wpnonce'] ) && wp_verify_nonce( $data['_delete_users_wpnonce'], 'delete_users_nonce' ) ) {

	    	if( empty( $error ) ){
	    		// Get post_ids for delete based on user input.
		        $post_ids = wpbulkdelete()->api->get_delete_user_ids( $data );
	    		
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
add_action( 'wp_ajax_delete_users_count', 'wpbd_delete_users_count' );

/**
 * Delete comments count for delete confirmation.
 *
 * @since 1.1.0
 * @return array
 */
function wpbd_delete_comments_count() {
	$data = $error = $return = array();
	parse_str($_POST['form'], $data);
	if( ! empty( $data ) ){
		
		if ( ! current_user_can( 'manage_options' ) ) {
	        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
	    }
	    
	    if( empty( $data['delete_comment_status'] ) ){
	        $error[] = esc_html__('Please select Comment status for proceed delete operation.', 'wp-bulk-delete' );  
	    }

	    if ( isset( $data['_delete_comments_wpnonce'] ) && wp_verify_nonce( $data['_delete_comments_wpnonce'], 'delete_comments_nonce' ) ) {

	    	if( empty( $error ) ){
	    		
	    		// Get delete comment count based on form data
		        $deletecomment_count = wpbulkdelete()->api->get_delete_comment_count( $data );
	    		
	    		if( false === $deletecomment_count ){
	                $return = array(
	                    'status' => 0,
	                    'messages' => array( esc_html__( 'Something went wrong pelase try again!!', 'wp-bulk-delete' ) ),
	                );
	            }

	    		if ( $deletecomment_count > 0 ) {
	    			$return = array(
		    			'status' => 1,
		    			'post_count' => $deletecomment_count,
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
add_action( 'wp_ajax_delete_comments_count', 'wpbd_delete_comments_count' );


/**
 * Get delete meta count for delete confirmation.
 *
 * @since 1.0
 * @return array
 */
function wpbd_delete_meta_count() {
	$data = $error = $return = array();
	parse_str($_POST['form'], $data);
	if( ! empty( $data ) ){
		
		if ( ! current_user_can( 'manage_options' ) ) {
	        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
	    }

	    if( $data['custom_field_key'] == '' ){
	    	$error[] = esc_html__('Please select all required fields.', 'wp-bulk-delete' );
	    }

	    if( $data['meta_type'] == 'postmeta' ){
	    	if( $data['meta_post_type'] == '' ){
		    	$error[] = esc_html__('Please select all required fields.', 'wp-bulk-delete' );
		    }
	    }

	     if( $data['meta_type'] == 'usermeta' ){
	    	if( empty( $data['delete_user_roles'] ) ){
		    	$error[] = esc_html__('Please select all required fields.', 'wp-bulk-delete' );
		    }
	    }

	    if ( isset( $data['_delete_meta_wpnonce'] ) && wp_verify_nonce( $data['_delete_meta_wpnonce'], 'delete_meta_nonce' ) ) {

	    	if( empty( $error ) ){
	    		// Get meta_ids for delete based on user input.
	    		$meta_ids = array();
	    		if( 'postmeta' == $data['meta_type'] ) {
	    			$meta_ids = wpbulkdelete()->api->get_delete_postmeta_ids( $data );

	    		} elseif('usermeta' == $data['meta_type'] ) {
	    			$meta_ids = wpbulkdelete()->api->get_delete_usermeta_ids( $data );	

	    		} elseif('commentmeta' == $data['meta_type'] ) {
	    			$meta_ids = wpbulkdelete()->api->get_delete_commentmeta_ids( $data );	
	    		}		        
	    		
	    		if ( ! empty( $meta_ids ) && count( $meta_ids ) > 0 ) {
	    			$return = array(
		    			'status' => 1,
		    			'post_count' => count( $meta_ids ),
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
add_action( 'wp_ajax_delete_meta_count', 'wpbd_delete_meta_count' );


/**
 * Delete comments count for delete confirmation.
 *
 * @since 1.1.0
 * @return array
 */
function wpbd_delete_terms_count() {
	$data = $error = $return = array();
	parse_str($_POST['form'], $data);
	if( ! empty( $data ) ){
		
		if ( ! current_user_can( 'manage_options' ) ) {
	        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
	    }
	    
	    if( $data['delete_post_type'] == '' ||  $data['post_taxonomy'] == '' ){
	        $error[] = esc_html__('Please select required fields for proceed delete operation.', 'wp-bulk-delete' );  
	    }

	    if ( isset( $data['_delete_terms_wpnonce'] ) && wp_verify_nonce( $data['_delete_terms_wpnonce'], 'delete_terms_nonce' ) ) {

	    	if( empty( $error ) ){
	    		
	    		// Get delete comment count based on form data
		        $deleteterms_count = wpbulkdelete()->api->get_delete_term_count( $data );
	    		
	    		if( false === $deleteterms_count ){
	                $return = array(
	                    'status' => 0,
	                    'messages' => array( esc_html__( 'Something went wrong pelase try again!!', 'wp-bulk-delete' ) ),
	                );
	            }

	    		if ( $deleteterms_count > 0 ) {
	    			$return = array(
		    			'status' => 1,
		    			'post_count' => $deleteterms_count,
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
add_action( 'wp_ajax_delete_terms_count', 'wpbd_delete_terms_count' );


/**
 * Render Taxonomy based on Post type Selection.
 *
 * @since 1.0
 * @return void
 */
function wpbd_render_postdropdown_by_posttype() {

	$post_type  = $_REQUEST['post_type'];
	$posts = array();
	if ( $post_type != '' ) {
		$posts = get_posts(
	        array(
	            'post_type'  => $post_type,
	            'numberposts' => -1,
	        )
	    );
	}
	if( ! empty( $posts ) ){
		?>
		<select name="post_for_meta[]" class="post_for_meta" multiple="multiple">
			<?php
			foreach ($posts as $post ) {
				?>
				<option value="<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></option>
				<?php	
			} ?>
		</select>
		<?php
	}
	wp_die();
}
add_action( 'wp_ajax_render_postdropdown_by_posttype', 'wpbd_render_postdropdown_by_posttype' );