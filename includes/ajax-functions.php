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
	parse_str($_POST['form'], $data); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if( ! empty( $data ) ){
		
		if ( ! current_user_can( 'manage_options' ) ) {
	        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
	    }

	    if ( isset( $data['_delete_all_actions_wpnonce'] ) && wp_verify_nonce( $data['_delete_all_actions_wpnonce'], 'delete_posts_nonce' ) ) {

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
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized access' );
        wp_die();
    }
    // Sanitize input
    $post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
    $taxonomies = array();
    if ( $post_type !== '' ) {
        $taxonomies = wpbd_get_taxonomy_by_posttype( $post_type );
    }

    if ( ! empty( $taxonomies ) ) {
        foreach ( $taxonomies as $slug => $name ) {
            ?>
            <input type="radio" name="post_taxonomy" 
                   value="<?php echo esc_attr( $slug ); ?>" 
                   class="post_taxonomy_radio" 
                   title="<?php echo esc_attr( $name ); ?>">
            <?php echo esc_html( $name ); ?><br />
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
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized access' );
        wp_die();
    }

    // Sanitize input
    $post_taxo = isset( $_REQUEST['post_taxomony'] ) ? $_REQUEST['post_taxomony'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended

    $terms = array();
    if ( $post_taxo !== '' && taxonomy_exists( $post_taxo ) ) {
        $terms = get_terms( array(
            'taxonomy'   => $post_taxo,
            'hide_empty' => true,
        ) );
    }

    if ( ! empty( $terms ) ) {
        ?>
        <select name="post_taxonomy_terms[]" class="taxonomy_terms_select" multiple="multiple">
            <?php foreach ( $terms as $term ) : ?>
                <option value="<?php echo esc_attr( $term->term_id ); ?>">
                    <?php echo esc_html( $term->name ); ?>
                </option>
            <?php endforeach; ?>
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
	parse_str($_POST['form'], $data); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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
	parse_str($_POST['form'], $data); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if( ! empty( $data ) ){
		
		if ( ! current_user_can( 'manage_options' ) ) {
	        $error[] = esc_html__('You don\'t have enough permission for this operation.', 'wp-bulk-delete' );
	    }
	    
	    if( empty( $data['delete_comment_status'] ) ){
	        $error[] = esc_html__('Please select Comment status for proceed delete operation.', 'wp-bulk-delete' );  
	    }

	    if ( isset( $data['_delete_comments_wpnonce'] ) && wp_verify_nonce( $data['_delete_comments_wpnonce'], 'delete_comments_nonce' ) ) {

	    	if( empty( $error ) ){
	    		// Get comment_ids for delete based on form data
		        $comment_ids = wpbulkdelete()->api->get_delete_comment_count( $data );
	    		
	    		if ( ! empty( $comment_ids ) && count( $comment_ids ) > 0 ) {
	    			$return = array(
		    			'status' => 1,
		    			'post_count' => count( $comment_ids ),
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
	parse_str($_POST['form'], $data); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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
	parse_str($_POST['form'], $data); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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
	                    'messages' => array( esc_html__( 'Something went wrong please try again!!', 'wp-bulk-delete' ) ),
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

	if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized access' );
        wp_die();
    }

	$post_type  = $_REQUEST['post_type']; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
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
				<option value="<?php echo esc_attr( $post->ID ); ?>"><?php echo esc_attr( $post->post_title ); ?></option>
				<?php	
			} ?>
		</select>
		<?php
	}
	wp_die();
}
add_action( 'wp_ajax_render_postdropdown_by_posttype', 'wpbd_render_postdropdown_by_posttype' );

/**
 * Render Taxonomy Term Meta Keys based on Taxonomy Selection.
 *
 * @since 1.2
 * @return void
 */
function wpbd_render_termmeta_keys_by_taxonomy() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized access' );
        wp_die();
    }

    // Sanitize input
    $taxonomy = isset( $_REQUEST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['taxonomy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

    $meta_keys = array();
    if ( $taxonomy !== '' && taxonomy_exists( $taxonomy ) ) {
        global $wpdb;
        // Get all terms for this taxonomy
        $terms = get_terms( array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'fields'     => 'ids',
        ) );
        
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            // Get distinct meta keys from termmeta table for these terms
            $term_ids_placeholder = implode( ',', array_fill( 0, count( $terms ), '%d' ) );
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $meta_keys = $wpdb->get_col( $wpdb->prepare(
                "SELECT DISTINCT meta_key FROM {$wpdb->termmeta} WHERE term_id IN ( {$term_ids_placeholder} ) AND meta_key != '' ORDER BY meta_key ASC",
                ...$terms
            ) );
        }
    }

    if ( ! empty( $meta_keys ) ) {
        foreach ( $meta_keys as $meta_key ) {
            echo '<option value="' . esc_attr( $meta_key ) . '">' . esc_html( $meta_key ) . '</option>';
        }
    }

    wp_die();
}
add_action( 'wp_ajax_render_termmeta_keys_by_taxonomy', 'wpbd_render_termmeta_keys_by_taxonomy' );


/**
 * Get counts for each post type under Cleanup options.
 *
 * @since 1.5.0
 * @return void
 */
function wpbd_ajax_get_cleanup_post_type_counts() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized' );
        wp_die();
    }

    global $wpdb;
    $counts = array(
        'revision'    => array(),
        'trash'       => array(),
        'auto_drafts' => array(),
        'meta'        => array(),
    );

    // 1. Revision counts
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $revisions_query = $wpdb->get_results(
        "SELECT p.post_type, COUNT(r.ID) as cnt 
        FROM {$wpdb->posts} r 
        INNER JOIN {$wpdb->posts} p ON r.post_parent = p.ID 
        WHERE r.post_type = 'revision' 
        GROUP BY p.post_type"
    );
    if ( $revisions_query ) {
        foreach ( $revisions_query as $row ) {
            $counts['revision'][ $row->post_type ] = intval( $row->cnt );
        }
    }

    // 2. Trash counts
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $trash_query = $wpdb->get_results(
        "SELECT post_type, COUNT(ID) as cnt 
        FROM {$wpdb->posts} 
        WHERE post_status = 'trash' 
        GROUP BY post_type"
    );
    if ( $trash_query ) {
        foreach ( $trash_query as $row ) {
            $counts['trash'][ $row->post_type ] = intval( $row->cnt );
        }
    }

    // 3. Auto drafts counts
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $auto_drafts_query = $wpdb->get_results(
        "SELECT post_type, COUNT(ID) as cnt 
        FROM {$wpdb->posts} 
        WHERE post_status = 'auto-draft' 
        GROUP BY post_type"
    );
    if ( $auto_drafts_query ) {
        foreach ( $auto_drafts_query as $row ) {
            $counts['auto_drafts'][ $row->post_type ] = intval( $row->cnt );
        }
    }

    // 4. Duplicate post metadata count
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $dup_postmeta_query = $wpdb->get_results(
        "SELECT p.post_type, SUM(pm_count.cnt - 1) as cnt
        FROM (
            SELECT post_id, COUNT(*) as cnt 
            FROM {$wpdb->postmeta} 
            GROUP BY post_id, meta_key, meta_value 
            HAVING cnt > 1
        ) pm_count
        INNER JOIN {$wpdb->posts} p ON pm_count.post_id = p.ID
        GROUP BY p.post_type"
    );
    if ( $dup_postmeta_query ) {
        foreach ( $dup_postmeta_query as $row ) {
            $post_type = $row->post_type;
            if ( ! isset( $counts['meta'][ $post_type ] ) ) {
                $counts['meta'][ $post_type ] = 0;
            }
            $counts['meta'][ $post_type ] += intval( $row->cnt );
        }
    }

    // 5. Duplicate comment metadata count
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $dup_commmeta_query = $wpdb->get_results(
        "SELECT p.post_type, SUM(cm_count.cnt - 1) as cnt
        FROM (
            SELECT comment_id, COUNT(*) as cnt 
            FROM {$wpdb->commentmeta} 
            GROUP BY comment_id, meta_key, meta_value 
            HAVING cnt > 1
        ) cm_count
        INNER JOIN {$wpdb->comments} c ON cm_count.comment_id = c.comment_ID
        INNER JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID
        GROUP BY p.post_type"
    );
    if ( $dup_commmeta_query ) {
        foreach ( $dup_commmeta_query as $row ) {
            $post_type = $row->post_type;
            if ( ! isset( $counts['meta'][ $post_type ] ) ) {
                $counts['meta'][ $post_type ] = 0;
            }
            $counts['meta'][ $post_type ] += intval( $row->cnt );
        }
    }

    wp_send_json_success( $counts );
    wp_die();
}
add_action( 'wp_ajax_wpbd_get_cleanup_post_type_counts', 'wpbd_ajax_get_cleanup_post_type_counts' );