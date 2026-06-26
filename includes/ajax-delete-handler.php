<?php
/**
 * AJAX Batched Delete Handler
 *
 * Handles AJAX-based batched deletion with progress tracking.
 * This file wraps existing delete functions — no deletion logic is modified.
 *
 * @package     WP_Bulk_Delete
 * @subpackage  AJAX
 * @since       1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for batched delete operations.
 *
 * Accepts serialized form data, determines entity type from nonce fields,
 * gets matching IDs on first call, stores in transient, and deletes in batches.
 *
 * @since 1.5.0
 * @return void
 */
function wpbd_ajax_run_delete() {

	// Verify nonce.
	check_ajax_referer( 'wpbd_ajax_nonce', 'nonce' );

	// Verify capability.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'wp-bulk-delete' ) ) );
	}

	// Parse form data.
	$wpbd_args_raw = isset( $_POST['wpbd_args'] ) ? wp_unslash( $_POST['wpbd_args'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$data = array();
	parse_str( $wpbd_args_raw, $data );

	$offset     = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	$batch_size = isset( $_POST['batch_size'] ) ? absint( $_POST['batch_size'] ) : 200;
	$batch_size = apply_filters( 'wpbd_ajax_batch_size', $batch_size );

	// Determine entity type from nonce fields in form data.
	$entity_type = wpbd_detect_entity_type( $data );

	if ( ! $entity_type ) {
		wp_send_json_error( array( 'message' => __( 'Could not determine delete type.', 'wp-bulk-delete' ) ) );
	}

	// Generate a unique transient key for this operation.
	$transient_key = 'wpbd_batch_ids_' . get_current_user_id() . '_' . $entity_type;

	// First call: get all matching IDs and store them.
	if ( $offset === 0 ) {
		$all_ids = wpbd_get_all_matching_ids( $entity_type, $data );

		if ( empty( $all_ids ) ) {
			wp_send_json_success( array(
				'deleted' => 0,
				'offset'  => 0,
				'total'   => 0,
				'done'    => true,
			) );
		}

		// Store IDs in transient (1 hour expiry as safety).
		set_transient( $transient_key, $all_ids, HOUR_IN_SECONDS );

		$total = count( $all_ids );
	} else {
		// Subsequent calls: retrieve stored IDs.
		$all_ids = get_transient( $transient_key );

		if ( false === $all_ids ) {
			wp_send_json_error( array( 'message' => __( 'Session expired. Please try again.', 'wp-bulk-delete' ) ) );
		}

		$total = count( $all_ids );
	}

	// Slice the batch.
	$batch = array_slice( $all_ids, $offset, $batch_size );

	if ( empty( $batch ) ) {
		delete_transient( $transient_key );
		wp_send_json_success( array(
			'deleted' => 0,
			'offset'  => $offset,
			'total'   => $total,
			'done'    => true,
		) );
	}

	// Execute deletion for this batch using existing API methods.
	$deleted_count = wpbd_execute_batch_delete( $entity_type, $batch, $data );

	$new_offset = $offset + count( $batch );
	$done       = $new_offset >= $total;

	// Clean up transient when done.
	if ( $done ) {
		delete_transient( $transient_key );
	}

	wp_send_json_success( array(
		'deleted' => $deleted_count,
		'offset'  => $new_offset,
		'total'   => $total,
		'done'    => $done,
	) );
}
add_action( 'wp_ajax_wpbd_run_delete', 'wpbd_ajax_run_delete' );


/**
 * Detect entity type from form data nonce fields.
 *
 * @since 1.5.0
 * @param array $data Parsed form data.
 * @return string|false Entity type or false if undetectable.
 */
function wpbd_detect_entity_type( $data ) {

	if ( isset( $data['_delete_all_actions_wpnonce'] ) ) {
		return 'posts';
	}
	if ( isset( $data['_delete_comments_wpnonce'] ) ) {
		return 'comments';
	}
	if ( isset( $data['_delete_users_wpnonce'] ) ) {
		return 'users';
	}
	if ( isset( $data['_delete_terms_wpnonce'] ) ) {
		return 'terms';
	}
	if ( isset( $data['_delete_meta_wpnonce'] ) ) {
		return 'meta';
	}
	if ( isset( $data['_run_post_cleanup_wpnonce'] ) ) {
		return 'cleanup';
	}

	// Pro WooCommerce forms.
	if ( isset( $data['_delete_wca_products_wpnonce'] ) ) {
		return 'wca_products';
	}
	if ( isset( $data['_delete_wca_orders_wpnonce'] ) ) {
		return 'wca_orders';
	}
	if ( isset( $data['_delete_wca_users_wpnonce'] ) ) {
		return 'wca_users';
	}
	if ( isset( $data['_delete_wca_general_wpnonce'] ) ) {
		return 'wca_general';
	}

	return false;
}


/**
 * Get all matching IDs for the given entity type and form data.
 * This wraps existing API methods without modifying them.
 *
 * @since 1.5.0
 * @param string $entity_type Entity type.
 * @param array  $data        Parsed form data.
 * @return array Array of IDs to delete.
 */
function wpbd_get_all_matching_ids( $entity_type, $data ) {

	$api = wpbulkdelete()->api;

	switch ( $entity_type ) {
		case 'posts':
			return $api->get_delete_posts_ids( $data );

		case 'comments':
			return $api->get_delete_comment_count( $data );

		case 'users':
			return $api->get_delete_user_ids( $data );

		case 'terms':
			// Terms use a different approach — get term IDs directly.
			return wpbd_get_term_ids_for_delete( $data );

		case 'meta':
			return wpbd_get_meta_ids_for_delete( $data );

		case 'cleanup':
			return wpbd_get_cleanup_ids( $data );

		case 'wca_products':
			global $wpbd_wca;
			return isset($wpbd_wca) ? $wpbd_wca->wpbd_wca_api->get_wpbd_wca_delete_products_ids( $data ) : array();

		case 'wca_orders':
			global $wpbd_wca;
			return isset($wpbd_wca) ? $wpbd_wca->wpbd_wca_api->get_wpbd_wca_delete_orders_ids( $data ) : array();

		case 'wca_users':
			global $wpbd_wca;
			return isset($wpbd_wca) ? $wpbd_wca->wpbd_wca_api->get_wpbd_wca_delete_user_ids( $data ) : array();

		case 'wca_general':
			global $wpbd_wca;
			// General actually gets users
			return isset($wpbd_wca) ? $wpbd_wca->wpbd_wca_api->get_wpbd_wca_delete_users_whno_ids( $data ) : array();

		default:
			// Fallback filter
			return apply_filters( 'wpbd_ajax_get_matching_ids', array(), $entity_type, $data );
	}
}


/**
 * Get term IDs for deletion.
 *
 * @since 1.5.0
 * @param array $data Form data.
 * @return array Term IDs.
 */
function wpbd_get_term_ids_for_delete( $data ) {

	if ( empty( $data['delete_post_type'] ) || empty( $data['post_taxonomy'] ) ) {
		return array();
	}

	$term_args = array(
		'taxonomy'   => $data['post_taxonomy'],
		'fields'     => 'ids',
		'hide_empty' => false,
	);

	// Apply term meta filter if provided.
	$term_meta_key     = isset( $data['term_meta_key'] ) ? esc_sql( $data['term_meta_key'] ) : '';
	$term_meta_value   = isset( $data['term_meta_value'] ) ? esc_sql( $data['term_meta_value'] ) : '';
	$term_meta_compare = isset( $data['term_meta_compare'] ) ? $data['term_meta_compare'] : 'equal_to_str';

	if ( $term_meta_key != '' && ( $term_meta_value != '' || in_array( $term_meta_compare, array( 'is_null', 'is_not_null', 'not_exist' ), true ) ) ) {
		$meta_query = wpbulkdelete()->api->get_term_meta_query( $term_meta_key, $term_meta_value, $term_meta_compare );
		if ( ! empty( $meta_query ) ) {
			$term_args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}
	}

	$terms = get_terms( $term_args );

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return $terms;
}


/**
 * Get meta IDs for deletion.
 *
 * @since 1.5.0
 * @param array $data Form data.
 * @return array Meta results.
 */
function wpbd_get_meta_ids_for_delete( $data ) {

	$api       = wpbulkdelete()->api;
	$meta_type = isset( $data['meta_type'] ) ? $data['meta_type'] : '';

	switch ( $meta_type ) {
		case 'postmeta':
			return $api->get_delete_postmeta_ids( $data );
		case 'usermeta':
			return $api->get_delete_usermeta_ids( $data );
		case 'commentmeta':
			return $api->get_delete_commentmeta_ids( $data );
		default:
			return array();
	}
}


/**
 * Get cleanup items to process.
 *
 * @since 1.5.0
 * @param array $data Form data.
 * @return array Array with cleanup types as items (one per "item").
 */
function wpbd_get_cleanup_ids( $data ) {

	$cleanups = isset( $data['cleanup_post_type'] ) ? $data['cleanup_post_type'] : array();
	if ( empty( $cleanups ) ) {
		return array();
	}

	// For cleanup, each type is one "item" to process.
	return $cleanups;
}


/**
 * Execute batch deletion for the given entity type.
 *
 * @since 1.5.0
 * @param string $entity_type Entity type.
 * @param array  $batch       Batch of IDs to delete.
 * @param array  $data        Original form data.
 * @return int Number of items deleted.
 */
function wpbd_execute_batch_delete( $entity_type, $batch, $data ) {

	$api = wpbulkdelete()->api;

	switch ( $entity_type ) {
		case 'posts':
			$force_delete = false;
			if ( isset( $data['delete_type'] ) && $data['delete_type'] === 'permenant' ) {
				$force_delete = true;
			}
			$custom_query = ! empty( $data['with_custom_query'] ) ? $data['with_custom_query'] : '';
			return $api->do_delete_posts( $batch, $force_delete, $data, $custom_query );

		case 'comments':
			return $api->do_delete_comments( $batch, $data );

		case 'users':
			$reassign_user = isset( $data['reassign_user'] ) ? $data['reassign_user'] : '';
			return $api->do_delete_users( $batch, $reassign_user );

		case 'terms':
			$taxonomy = isset( $data['post_taxonomy'] ) ? $data['post_taxonomy'] : '';
			$count    = 0;
			foreach ( $batch as $term_id ) {
				$result = wp_delete_term( $term_id, $taxonomy );
				if ( ! is_wp_error( $result ) && $result !== false ) {
					$count++;
				}
			}
			return $count;

		case 'meta':
			$meta_type = isset( $data['meta_type'] ) ? $data['meta_type'] : '';
			switch ( $meta_type ) {
				case 'postmeta':
					return $api->do_delete_postmetas( $batch );
				case 'usermeta':
					return $api->do_delete_usermetas( $batch );
				case 'commentmeta':
					return $api->do_delete_commentmetas( $batch );
				default:
					return 0;
			}

		case 'cleanup':
			$count = 0;
			foreach ( $batch as $cleanup_type ) {
				$message = $api->run_cleanup( $cleanup_type );
				if ( ! empty( $message ) ) {
					$count++;
				}
			}
			return $count;

		case 'wca_products':
		case 'wca_orders':
			global $wpbd_wca;
			if ( ! isset( $wpbd_wca ) ) return 0;
			$force_delete = false;
			if ( isset( $data['delete_type'] ) && $data['delete_type'] === 'permenant' ) {
				$force_delete = true;
			}
			return $wpbd_wca->wpbd_wca_api->do_delete_wpbd_wca_posts( $batch, $force_delete, $data );

		case 'wca_users':
		case 'wca_general':
			global $wpbd_wca;
			if ( ! isset( $wpbd_wca ) ) return 0;
			$reassign_user = isset( $data['reassign_user'] ) ? $data['reassign_user'] : '';
			return $wpbd_wca->wpbd_wca_api->do_delete_wpbd_wca_users( $batch, $reassign_user, $data );

		default:
			// Fallback filter
			return apply_filters( 'wpbd_ajax_execute_batch_delete', 0, $entity_type, $batch, $data );
	}
}
