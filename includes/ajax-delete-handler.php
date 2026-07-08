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
	// Determine entity type from nonce fields in form data.
	$entity_type = wpbd_detect_entity_type( $data );
	if ( $entity_type === 'cleanup' ) {
		$batch_size = 100;
	}
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
 * AJAX handler to cancel a running delete operation.
 * Clears the stored IDs transient.
 *
 * @since 1.5.0
 * @return void
 */
function wpbd_ajax_cancel_delete() {
	check_ajax_referer( 'wpbd_ajax_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'wp-bulk-delete' ) ) );
	}
	$data = array();
	if ( isset( $_POST['wpbd_args'] ) ) {
		parse_str( wp_unslash( $_POST['wpbd_args'] ), $data ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
	$entity_type = wpbd_detect_entity_type( $data );
	if ( $entity_type ) {
		$transient_key = 'wpbd_batch_ids_' . get_current_user_id() . '_' . $entity_type;
		delete_transient( $transient_key );
	}
	wp_send_json_success();
}
add_action( 'wp_ajax_wpbd_cancel_delete', 'wpbd_ajax_cancel_delete' );


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
			$cleanup_ids = wpbd_get_cleanup_ids( $data );
			if ( ! empty( $data['limit_post'] ) ) {
				$limit = absint( $data['limit_post'] );
				$cleanup_ids = array_slice( $cleanup_ids, 0, $limit );
			}
			return $cleanup_ids;

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
			return array( 1 );

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
 * Get metadata cleanup IDs for batching.
 *
 * @since 1.5.0
 * @param array $data Form data.
 * @return array Meta IDs to delete.
 */
function wpbd_get_cleanup_meta_ids( $data ) {
	global $wpdb;
	$meta_ids = array();
	$post_types = isset( $data['cleanup_meta_post_types'] ) ? $data['cleanup_meta_post_types'] : array();

	// 1. Orphan post meta
	$query1 = $wpdb->get_col( "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})" );
	if ( ! empty( $query1 ) ) {
		foreach ( $query1 as $id ) {
			$meta_ids[] = 'meta_postmeta_' . $id;
		}
	}

	// 2. Orphan comment meta
	$query2 = $wpdb->get_col( "SELECT meta_id FROM {$wpdb->commentmeta} WHERE comment_id NOT IN (SELECT comment_ID FROM {$wpdb->comments})" );
	if ( ! empty( $query2 ) ) {
		foreach ( $query2 as $id ) {
			$meta_ids[] = 'meta_commentmeta_' . $id;
		}
	}

	// 3. Orphan user meta
	$query3 = $wpdb->get_col( "SELECT umeta_id FROM {$wpdb->usermeta} WHERE user_id NOT IN (SELECT ID FROM {$wpdb->users})" );
	if ( ! empty( $query3 ) ) {
		foreach ( $query3 as $id ) {
			$meta_ids[] = 'meta_usermeta_' . $id;
		}
	}

	// 4. Orphan term meta
	$query4 = $wpdb->get_col( "SELECT meta_id FROM {$wpdb->termmeta} WHERE term_id NOT IN (SELECT term_id FROM {$wpdb->terms})" );
	if ( ! empty( $query4 ) ) {
		foreach ( $query4 as $id ) {
			$meta_ids[] = 'meta_termmeta_' . $id;
		}
	}

	// 5. Duplicate post meta
	if ( ! empty( $post_types ) ) {
		$post_types_placeholder = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		$query5 = $wpdb->get_results( $wpdb->prepare(
			"SELECT GROUP_CONCAT(pm.meta_id ORDER BY pm.meta_id DESC) AS ids 
			FROM {$wpdb->postmeta} pm 
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
			WHERE p.post_type IN ($post_types_placeholder)
			GROUP BY pm.post_id, pm.meta_key, pm.meta_value HAVING COUNT(*) > 1",
			...$post_types
		) );
	} else {
		if ( wpbd_is_pro() ) {
			$query5 = array();
		} else {
			$query5 = $wpdb->get_results( "SELECT GROUP_CONCAT(meta_id ORDER BY meta_id DESC) AS ids FROM {$wpdb->postmeta} GROUP BY post_id, meta_key, meta_value HAVING COUNT(*) > 1" );
		}
	}
	if ( ! empty( $query5 ) ) {
		foreach ( $query5 as $meta ) {
			$ids = explode( ',', $meta->ids );
			array_pop( $ids ); // keep one
			foreach ( $ids as $id ) {
				$meta_ids[] = 'meta_postmeta_' . $id;
			}
		}
	}

	// 6. Duplicate comment meta
	if ( ! empty( $post_types ) ) {
		$post_types_placeholder = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		$query6 = $wpdb->get_results( $wpdb->prepare(
			"SELECT GROUP_CONCAT(cm.meta_id ORDER BY cm.meta_id DESC) AS ids 
			FROM {$wpdb->commentmeta} cm 
			INNER JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID 
			INNER JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID 
			WHERE p.post_type IN ($post_types_placeholder)
			GROUP BY cm.comment_id, cm.meta_key, cm.meta_value HAVING COUNT(*) > 1",
			...$post_types
		) );
	} else {
		if ( wpbd_is_pro() ) {
			$query6 = array();
		} else {
			$query6 = $wpdb->get_results( "SELECT GROUP_CONCAT(meta_id ORDER BY meta_id DESC) AS ids FROM {$wpdb->commentmeta} GROUP BY comment_id, meta_key, meta_value HAVING COUNT(*) > 1" );
		}
	}
	if ( ! empty( $query6 ) ) {
		foreach ( $query6 as $meta ) {
			$ids = explode( ',', $meta->ids );
			array_pop( $ids );
			foreach ( $ids as $id ) {
				$meta_ids[] = 'meta_commentmeta_' . $id;
			}
		}
	}

	// 7. Duplicate user meta
	$query7 = $wpdb->get_results( "SELECT GROUP_CONCAT(umeta_id ORDER BY umeta_id DESC) AS ids FROM {$wpdb->usermeta} GROUP BY user_id, meta_key, meta_value HAVING COUNT(*) > 1" );
	if ( ! empty( $query7 ) ) {
		foreach ( $query7 as $meta ) {
			$ids = explode( ',', $meta->ids );
			array_pop( $ids );
			foreach ( $ids as $id ) {
				$meta_ids[] = 'meta_usermeta_' . $id;
			}
		}
	}

	// 8. Duplicate term meta
	$query8 = $wpdb->get_results( "SELECT GROUP_CONCAT(meta_id ORDER BY meta_id DESC) AS ids FROM {$wpdb->termmeta} GROUP BY term_id, meta_key, meta_value HAVING COUNT(*) > 1" );
	if ( ! empty( $query8 ) ) {
		foreach ( $query8 as $meta ) {
			$ids = explode( ',', $meta->ids );
			array_pop( $ids );
			foreach ( $ids as $id ) {
				$meta_ids[] = 'meta_termmeta_' . $id;
			}
		}
	}

	return $meta_ids;
}

/**
 * Get cleanup items to process.
 *
 * @since 1.5.0
 * @param array $data Form data.
 * @return array Array with cleanup types as items.
 */
function wpbd_get_cleanup_ids( $data ) {
	global $wpdb;

	$cleanups = isset( $data['cleanup_post_type'] ) ? $data['cleanup_post_type'] : array();
	if ( empty( $cleanups ) ) {
		return array();
	}

	$all_ids = array();

	foreach ( $cleanups as $cleanup_type ) {
		switch ( $cleanup_type ) {
			case 'revision':
				$post_types = isset( $data['cleanup_revision_post_types'] ) ? $data['cleanup_revision_post_types'] : array();
				if ( ! empty( $post_types ) ) {
					$post_types_placeholder = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
					$query = $wpdb->prepare(
						"SELECT r.ID FROM {$wpdb->posts} r 
						INNER JOIN {$wpdb->posts} p ON r.post_parent = p.ID 
						WHERE r.post_type = 'revision' 
						AND p.post_type IN ($post_types_placeholder)
						ORDER BY r.ID ASC",
						...$post_types
					);
					$ids = $wpdb->get_col( $query );
				} else {
					if ( wpbd_is_pro() ) {
						$ids = array();
					} else {
						$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s ORDER BY ID ASC", 'revision' );
						$ids = $wpdb->get_col( $query );
					}
				}
				if ( ! empty( $ids ) ) {
					foreach ( $ids as $id ) {
						$all_ids[] = 'revision_' . $id;
					}
				}
				break;

			case 'trash':
				$post_types = isset( $data['cleanup_trash_post_types'] ) ? $data['cleanup_trash_post_types'] : array();
				if ( ! empty( $post_types ) ) {
					$post_types_placeholder = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
					$query = $wpdb->prepare(
						"SELECT ID FROM {$wpdb->posts} 
						WHERE post_status = 'trash' 
						AND post_type IN ($post_types_placeholder)
						ORDER BY ID ASC",
						...$post_types
					);
					$ids = $wpdb->get_col( $query );
				} else {
					if ( wpbd_is_pro() ) {
						$ids = array();
					} else {
						$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_status = %s ORDER BY ID ASC", 'trash' );
						$ids = $wpdb->get_col( $query );
					}
				}
				if ( ! empty( $ids ) ) {
					foreach ( $ids as $id ) {
						$all_ids[] = 'trash_' . $id;
					}
				}
				break;

			case 'auto_drafts':
				$post_types = isset( $data['cleanup_auto_drafts_post_types'] ) ? $data['cleanup_auto_drafts_post_types'] : array();
				if ( ! empty( $post_types ) ) {
					$post_types_placeholder = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
					$query = $wpdb->prepare(
						"SELECT ID FROM {$wpdb->posts} 
						WHERE post_status = 'auto-draft' 
						AND post_type IN ($post_types_placeholder)
						ORDER BY ID ASC",
						...$post_types
					);
					$ids = $wpdb->get_col( $query );
				} else {
					if ( wpbd_is_pro() ) {
						$ids = array();
					} else {
						$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_status = %s ORDER BY ID ASC", 'auto-draft' );
						$ids = $wpdb->get_col( $query );
					}
				}
				if ( ! empty( $ids ) ) {
					foreach ( $ids as $id ) {
						$all_ids[] = 'autodraft_' . $id;
					}
				}
				break;

			case 'all_orphan_duplicate':
				$meta_ids = wpbd_get_cleanup_meta_ids( $data );
				$all_ids = array_merge( $all_ids, $meta_ids );
				break;
		}
	}

	return $all_ids;
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
			global $wpdb;
			$count = 0;
			foreach ( $batch as $item ) {
				if ( strpos( $item, 'revision_' ) === 0 ) {
					$id = intval( substr( $item, 9 ) );
					wp_delete_post_revision( $id );
					$count++;
				} elseif ( strpos( $item, 'trash_' ) === 0 ) {
					$id = intval( substr( $item, 6 ) );
					wp_delete_post( $id, true );
					$count++;
				} elseif ( strpos( $item, 'autodraft_' ) === 0 ) {
					$id = intval( substr( $item, 10 ) );
					wp_delete_post( $id, true );
					$count++;
				} elseif ( strpos( $item, 'meta_postmeta_' ) === 0 ) {
					$id = intval( substr( $item, 14 ) );
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_id = %d", $id ) );
					$count++;
				} elseif ( strpos( $item, 'meta_commentmeta_' ) === 0 ) {
					$id = intval( substr( $item, 17 ) );
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->commentmeta} WHERE meta_id = %d", $id ) );
					$count++;
				} elseif ( strpos( $item, 'meta_usermeta_' ) === 0 ) {
					$id = intval( substr( $item, 14 ) );
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE umeta_id = %d", $id ) );
					$count++;
				} elseif ( strpos( $item, 'meta_termmeta_' ) === 0 ) {
					$id = intval( substr( $item, 14 ) );
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->termmeta} WHERE meta_id = %d", $id ) );
					$count++;
				}
			}
			return $count;

		case 'wca_products':
			global $wpbd_wca;
			if ( ! isset( $wpbd_wca ) ) return 0;
			$force_delete = false;
			if ( isset( $data['delete_type'] ) && $data['delete_type'] === 'permenant' ) {
				$force_delete = true;
			}
			return $wpbd_wca->wpbd_wca_api->do_delete_wca_products( $batch, $force_delete, $data );

		case 'wca_orders':
			global $wpbd_wca;
			if ( ! isset( $wpbd_wca ) ) return 0;
			$force_delete = false;
			if ( isset( $data['delete_type'] ) && $data['delete_type'] === 'permenant' ) {
				$force_delete = true;
			}
			return $wpbd_wca->wpbd_wca_api->do_delete_wca_order( $batch, $force_delete, $data );

		case 'wca_users':
			global $wpbd_wca;
			if ( ! isset( $wpbd_wca ) ) return 0;
			$reassign_user = isset( $data['reassign_user'] ) ? $data['reassign_user'] : '';
			return $wpbd_wca->wpbd_wca_api->do_wpbd_wca_delete_users( $batch, $reassign_user );

		case 'wca_general':
			global $wpbd_wca;
			if ( ! isset( $wpbd_wca ) ) return 0;
			$wpbd_wca->wpbd_wca_api->do_delete_general_action( $data );
			return 1;

		default:
			// Fallback filter
			return apply_filters( 'wpbd_ajax_execute_batch_delete', 0, $entity_type, $batch, $data );
	}
}
