<?php
/**
 * Scripts
 *
 * @package     WP_Bulk_Delete
 * @subpackage  Delerte API
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPBD_Delete_API class
 *
 * @since 1.0.0
 */
class WPBD_Delete_API {

	/**
	 * Get things going
	 *
	 * @since 1.0.0
	 */
	public function __construct() { /* */ }

	/**
	 * Get posts Ids.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data Delete posts form data.
	 * @return array | Posts Id.
	 */
	public function get_delete_posts_ids( $data = array() ) {
        if( wpbd_is_pro() && class_exists('WPBD_Delete_API_Pro', false) ){
            $wpbdpro = new WPBD_Delete_API_Pro();
            return $wpbdpro->get_delete_posts_ids( $data );
        }
		global $wpdb;
		if( ! empty( $data['delete_post_type'] ) &&  ! empty( $data['delete_post_status'] ) ){

            $post_types = isset( $data['delete_post_type'] ) ? $data['delete_post_type'] : array();
            if( ! is_array( $post_types ) ){
                $post_types = array( $post_types ); 
            }
            $post_types = array_map('esc_sql', $post_types );

            $post_status = isset( $data['delete_post_status'] ) ? array_map('esc_sql', $data['delete_post_status'] ) : array();
            $delete_start_date = isset( $data['delete_start_date'] ) ? esc_sql( $data['delete_start_date'] ) : '';
            $delete_end_date = isset( $data['delete_end_date'] ) ? esc_sql( $data['delete_end_date'] ) : '';
            $delete_authors = isset( $data['delete_authors'] ) ?  array_map( 'intval', $data['delete_authors'] ) : array();
            $delete_type = isset( $data['delete_type'] ) ? $data['delete_type'] : 'trash';
            $post_media = isset( $data['post_media'] ) ? $data['post_media'] : 'no';
            $limit_post = !empty( $data['limit_post'] ) ? absint( $data['limit_post'] ) : '10000';
            $date_type = isset( $data['date_type'] ) ? esc_sql( $data['date_type'] ) : 'custom_date';
            $input_days = isset( $data['input_days'] ) ? esc_sql( $data['input_days'] ) : '';
            if( $date_type === 'older_than') {
                $delete_start_date = $delete_end_date = '';
                if( $input_days === "0" || $input_days > 0){
                    $delete_end_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
                }
            } else if( $date_type === 'within_last') {
                $delete_start_date = $delete_end_date = '';
                if( $input_days === "0" || $input_days > 0){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
                }
            }

            $mdelete_start_date = isset( $data['mdelete_start_date'] ) ? esc_sql( $data['mdelete_start_date'] ) : '';
            $mdelete_end_date = isset( $data['mdelete_end_date'] ) ? esc_sql( $data['mdelete_end_date'] ) : '';
            $mdate_type = isset( $data['mdate_type'] ) ? esc_sql( $data['mdate_type'] ) : 'mcustom_date';
            $minput_days = isset( $data['minput_days'] ) ? esc_sql( $data['minput_days'] ) : '';
            if( $mdate_type === 'molder_than') {
                $mdelete_start_date = $mdelete_end_date = '';
                if( $minput_days === "0" || $minput_days > 0){
                    $mdelete_end_date = gmdate('Y-m-d', strtotime("-{$minput_days} days", strtotime(current_time('Y-m-d'))));
                }
            } else if( $mdate_type === 'mwithin_last') {
                $mdelete_start_date = $mdelete_end_date = '';
                if( $minput_days === "0" || $minput_days > 0){
                    $mdelete_start_date = gmdate('Y-m-d', strtotime("-{$minput_days} days", strtotime(current_time('Y-m-d'))));
                }
            }

            // BY Taxonomy.
            $post_taxonomy =  isset( $data['post_taxonomy'] ) ?  esc_sql( $data['post_taxonomy'] ) : '';
            $post_taxonomy_terms =  isset( $data['post_taxonomy_terms'] ) ? array_map( 'intval', $data['post_taxonomy_terms'] ) : array();
            $d_selected_category =  isset( $data['delete_selected_category'] ) ? esc_sql( $data['delete_selected_category'] )  : '';            

            if( empty( $post_types ) || empty( $post_status ) ){
                return array();
            }

            // Query Generation.
            $query = "SELECT DISTINCT $wpdb->posts.ID FROM $wpdb->posts ";

            if( $post_taxonomy != '' && ! empty( $post_taxonomy_terms ) ) {
                
                $query .= " LEFT JOIN $wpdb->term_relationships ON( $wpdb->posts.ID = $wpdb->term_relationships.object_id )";
                $query .= " LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";
                $query .= " LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)";
            }
            
            $query .= " WHERE $wpdb->posts.post_type IN ( '" . implode( "', '", $post_types ) . "' )";  

            if( $post_taxonomy != '' && ! empty( $post_taxonomy_terms ) ) {
                $query .= " AND ( $wpdb->terms.term_id IN ( " . implode( ", ", $post_taxonomy_terms ). " )";
                $query .= " AND $wpdb->term_taxonomy.taxonomy  = '{$post_taxonomy}' )";
            }

            if( $post_taxonomy != '' && ! empty( $post_taxonomy_terms ) && !empty( $d_selected_category ) ){
                $query .= "AND $wpdb->posts.ID NOT IN (
                    SELECT $wpdb->posts.ID
                    FROM $wpdb->posts
                    LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
                    LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
                    LEFT JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id )
                    WHERE $wpdb->term_taxonomy.taxonomy = '{$post_taxonomy}' AND $wpdb->terms.term_id NOT IN ( " . implode( ", ", $post_taxonomy_terms ). " ) )";
            }

            if( !empty( $post_status ) ){
                $query .= " AND $wpdb->posts.post_status IN ( '" .  implode( "', '", $post_status ) . "' )";
            }
            if( $delete_start_date != ''){
                $query .= " AND $wpdb->posts.post_date >= '{$delete_start_date} 00:00:00'";
            }
            if( $delete_end_date != ''){
                $query .= " AND $wpdb->posts.post_date <= '{$delete_end_date} 23:59:59'";
            }
            if( $mdelete_start_date != ''){
                $query .= " AND $wpdb->posts.post_modified >= '{$mdelete_start_date} 00:00:00'";
            }
            if( $mdelete_end_date != ''){
                $query .= " AND $wpdb->posts.post_modified <= '{$mdelete_end_date} 23:59:59'";
            }

            if( !empty( $delete_authors ) ){
                $query .= " AND $wpdb->posts.post_author IN ( " . implode( ",", $delete_authors ). " )";
            }

            if( is_numeric( $limit_post ) ){
                $query .= " LIMIT " . $limit_post;
            }
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $posts = $wpdb->get_col( $query );
            return $posts;

        }else{
            return array();
        }
	}

    /**
	 * Do Delete operation on posts.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data Posts Id.
	 * @return array | deleted posts count.
	 */
	public function do_delete_posts( $post_ids = array(), $force_delete = false, $item = array(), $custom_query = null ) {
		global $wpdb;
        $post_delete_count = 0;

        set_time_limit(0); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        $xt_memory_limit = (int)str_replace( 'M', '',ini_get('memory_limit' ) );
        if( $xt_memory_limit < 512 ){
            ini_set('memory_limit', '512M'); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        }

        if( ! empty( $post_ids ) && count( $post_ids ) > 0 ) {
                    
            if( $custom_query == 'custom_query' ){
                
                foreach( $post_ids as $post_id ){
                    if( isset( $item['post_media'] ) && $item['post_media'] === 'yes' ){
                        $post_attachment_id = get_post_meta( $post_id, '_thumbnail_id', true );
                        if( !empty( $post_attachment_id ) ){
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                            $attachment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value = %d", $post_attachment_id ) );
                            if( !empty( $attachment_ids ) && count( $attachment_ids ) <= 1 ){

                                $attachment_metadata = wp_get_attachment_metadata( $post_attachment_id );
                                if ( !empty($attachment_metadata['sizes'] ) ) {
                                    //Getting file path
                                    $upload_dir = wp_upload_dir();
                                    $file_path  = $upload_dir['basedir'] . '/' . dirname( $attachment_metadata['file'] ) . '/';
                                    
                                    //Removing all image sizes
                                    foreach( $attachment_metadata['sizes'] as $size_info ){
                                        $file = $file_path . $size_info['file'];
                                        //file check and remove it
                                        if ( file_exists( $file ) ) {
                                            wp_delete_file( $file );
                                        }
                                    }
                                }

                                if ( !empty( $attachment_metadata['file'] ) ) {
                                    $file_path = $upload_dir['basedir'] . '/' . $attachment_metadata['file'];
                                    //file check and remove it
                                    if (file_exists($file_path)) {
                                        wp_delete_file($file_path);
                                    }
                                }
                                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE ID = %d", $post_attachment_id ) );
                                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d", $post_attachment_id ) );
                            }
                        }
                    }
                }

                $post_ids_sanitized = array_map( 'intval', $post_ids );
                $placeholders       = implode( ',', array_fill( 0, count( $post_ids_sanitized ), '%d' ) );
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $query = $wpdb->prepare(
                    "DELETE p, pt, pm FROM {$wpdb->posts} p 
                    LEFT JOIN {$wpdb->term_relationships} pt ON pt.object_id = p.ID 
                    LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID 
                    WHERE p.ID IN ( $placeholders )", /// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
                    $post_ids_sanitized
                );
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $wpdb->query( $query );

            }else{
                foreach ($post_ids as $post_id ){
                    if( isset( $item['post_media'] ) && $item['post_media'] === 'yes' ){
                        $post_attachment_id = get_post_meta( $post_id, '_thumbnail_id', true );
                        if( !empty( $post_attachment_id ) ){
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                            $attachment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value = %d", $post_attachment_id ) );
                            if( count( $attachment_ids ) <= 1 ){
                                wp_delete_attachment( $post_attachment_id, $force_delete );
                            }
                        }
                    }
                
                    if( $force_delete === false ){
                        wp_trash_post( $post_id );
                    }else{
                        wp_delete_post( $post_id, true );
                    }
                }
            }
            $post_delete_count = count( $post_ids );

		}
		return $post_delete_count;
	}

    /**
     * Get Post Count by Posttype
     *
     * @access public
     * @since 1.0
     * @param array $post_type Post type.
     * @return int | posts count.
     */
    public function get_post_count( $counttype = '' ) {
        global $wpdb;

        $count = 0;

        switch( $counttype ) {
            case 'revision':
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
                break;
            case 'auto_drafts':
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
                break;
            case 'trash':
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = %s", 'trash' ) );
                break;
        }
        return $count;
    }

    /**
     * Run Cleanup
     *
     * @access public
     * @since 1.0
     * @param array $cleanuptype Cleanup type.
     * @return string | message.
     */
    public function run_cleanup( $cleanuptype = '' ){
        global $wpdb;
        $message = '';

        switch( $cleanuptype ) {
            case 'revision':
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
                if( $posts ) {
                    foreach ( $posts as $id ) {
                        wp_delete_post_revision( intval( $id ) );
                    }

                    // translators: %s: Number Of Revisions Cleaned up.
                    $message = sprintf( __( '%s Revisions Cleaned up', 'wp-bulk-delete' ), number_format_i18n( sizeof( $posts ) ) );
                }
                break;
            case 'auto_drafts':
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
                if( $posts ) {
                    foreach ( $posts as $id ) {
                        wp_delete_post( intval( $id ), true );
                    }

                    // translators: %s: Number Of Auto Drafts Cleaned up.
                    $message = sprintf( __( '%s Auto Drafts Cleaned up', 'wp-bulk-delete' ), number_format_i18n( sizeof( $posts ) ) );
                }
                break;
            case 'trash':
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'trash' ) );
                if( $posts ) {
                    foreach ( $posts as $id ) {
                        wp_delete_post( $id, true );
                    }

                    // translators: %s: Number Of Posts Cleaned up.
                    $message = sprintf( __( '%s Trashed Posts Cleaned up', 'wp-bulk-delete' ), number_format_i18n( sizeof( $posts ) ) );
                }
                break;

            //Delete all orphan and duplicate
            case 'all_orphan_duplicate':
                $dp = $ocm = $oum = $otm = $dpm = $dcm = $dum = $dtm = 0;
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $query1 = $wpdb->get_results( "SELECT post_id, meta_key FROM $wpdb->postmeta WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts)" );
                if( $query1 ) {
                    foreach ( $query1 as $meta ) {
                        $post_id = intval( $meta->post_id );
                        if( $post_id === 0 ) {
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, $meta->meta_key ) );
                        } else {
                            delete_post_meta( $post_id, $meta->meta_key );
                        }
                    }
                    $dp = number_format_i18n( sizeof( $query1 ) );
                }

                //Orphan Comment Meta
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $query2 = $wpdb->get_results( "SELECT comment_id, meta_key FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)" );
                if( $query2 ) {
                    foreach ( $query2 as $meta ) {
                        $comment_id = intval( $meta->comment_id );
                        if( $comment_id === 0 ) {
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->commentmeta WHERE comment_id = %d AND meta_key = %s", $comment_id, $meta->meta_key ) );
                        } else {
                            delete_comment_meta( $comment_id, $meta->meta_key );
                        }
                    }
                    $ocm = number_format_i18n( sizeof( $query2 ) );
                }

                //Orphan User Meta
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $query3 = $wpdb->get_results( "SELECT user_id, meta_key FROM $wpdb->usermeta WHERE user_id NOT IN (SELECT ID FROM $wpdb->users)" );
                if( $query3 ) {
                    foreach ( $query3 as $meta ) {
                        $user_id = intval( $meta->user_id );
                        if( $user_id === 0 ) {
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s", $user_id, $meta->meta_key ) );
                        } else {
                            delete_user_meta( $user_id, $meta->meta_key );
                        }
                    }
                    $oum = number_format_i18n( sizeof( $query3 ) );
                }
                
                //Orphan Term Meta
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $query4 = $wpdb->get_results( "SELECT term_id, meta_key FROM $wpdb->termmeta WHERE term_id NOT IN (SELECT term_id FROM $wpdb->terms)" );
                if( $query4 ) {
                    foreach ( $query4 as $meta ) {
                        $term_id = intval( $meta->term_id );
                        if( $term_id === 0 ) {
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE term_id = %d AND meta_key = %s", $term_id, $meta->meta_key ) );
                        } else {
                            delete_term_meta( $term_id, $meta->meta_key );
                        }
                    }
                    $otm = number_format_i18n( sizeof( $query4 ) );
                }
            
                //Duplicate Post Meta
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $query5 = $wpdb->get_results( $wpdb->prepare( "SELECT GROUP_CONCAT(meta_id ORDER BY meta_id DESC) AS ids, post_id, COUNT(*) AS count FROM $wpdb->postmeta GROUP BY post_id, meta_key, meta_value HAVING count > %d", 1 ) );
                if( $query5 ) {
                    foreach ( $query5 as $meta ) {
                        $ids = array_map( 'intval', explode( ',', $meta->ids ) );
                        array_pop( $ids );
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_id IN (" . implode( ',', $ids ) . ") AND post_id = %d", intval( $meta->post_id ) ) );
                    }
                    $dpm = number_format_i18n( sizeof( $query5 ) );
                }

                //Duplicate Comment Meta
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $query6 = $wpdb->get_results( $wpdb->prepare( "SELECT GROUP_CONCAT(meta_id ORDER BY meta_id DESC) AS ids, comment_id, COUNT(*) AS count FROM $wpdb->commentmeta GROUP BY comment_id, meta_key, meta_value HAVING count > %d", 1 ) );
                if( $query6 ) {
                    foreach ( $query6 as $meta ) {
                        $ids = array_map( 'intval', explode( ',', $meta->ids ) );
                        array_pop( $ids );
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->commentmeta WHERE meta_id IN (" . implode( ',', $ids ) . ") AND comment_id = %d", intval( $meta->comment_id ) ) );
                    }
                    $dcm = number_format_i18n( sizeof( $query6 ) );
                }

                //Duplicate user Meta
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $query7 = $wpdb->get_results( $wpdb->prepare( "SELECT GROUP_CONCAT(umeta_id ORDER BY umeta_id DESC) AS ids, user_id, COUNT(*) AS count FROM $wpdb->usermeta GROUP BY user_id, meta_key, meta_value HAVING count > %d", 1 ) );
                if( $query7 ) {
                    foreach ( $query7 as $meta ) {
                        $ids = array_map( 'intval', explode( ',', $meta->ids ) );
                        array_pop( $ids );
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE umeta_id IN (" . implode( ',', $ids ) . ") AND user_id = %d", intval( $meta->user_id ) ) );
                    }
                    $dum = number_format_i18n( sizeof( $query7 ) );
                }

                //Duplicate term Meta
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $query8 = $wpdb->get_results( $wpdb->prepare( "SELECT GROUP_CONCAT(meta_id ORDER BY meta_id DESC) AS ids, term_id, COUNT(*) AS count FROM $wpdb->termmeta GROUP BY term_id, meta_key, meta_value HAVING count > %d", 1 ) );
                if( $query8 ) {
                    foreach ( $query8 as $meta ) {
                        $ids = array_map( 'intval', explode( ',', $meta->ids ) );
                        array_pop( $ids );
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                        $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE meta_id IN (" . implode( ',', $ids ) . ") AND term_id = %d", intval( $meta->term_id ) ) );
                    }
                    $dtm = number_format_i18n( sizeof( $query7 ) );
                }
                $odsum = $dp + $ocm + $oum + $otm + $dpm + $dcm + $dum + $dtm;
                // translators: %s: Number Of Orphan and Duplicate Meta Cleaned up.
                $message = sprintf( __( '%s Orphan and Duplicate Meta Cleaned up', 'wp-bulk-delete' ), number_format_i18n( $odsum ) );
                break;

        }
        return $message;
    }

    /**
     * Get Users Ids.
     *
     * @access public
     * @since 1.0
     * @param array $data Delete User form data.
     * @return array | Users Id.
     */
    public function get_delete_user_ids( $data = array() ){
        if( wpbd_is_pro() && class_exists( 'WPBD_Delete_API_Pro', false ) ){
            $wpbdpro = new WPBD_Delete_API_Pro();
            return $wpbdpro->get_delete_user_ids( $data );
        }
        global $wpdb;

        if( empty( $data['delete_user_roles'] ) && ( $data['user_meta_key'] == '' || $data['user_meta_value'] == '' ) ){
            return array();
        }
        if( empty( $data['delete_user_roles'] ) && $data['user_email'] == '' ){
            return array();
        }
        $delete_user_roles = isset( $data['delete_user_roles'] ) ? $data['delete_user_roles'] : array();
        $delete_user_roles = array_map('esc_sql', $delete_user_roles );
        $delete_start_date = isset( $data['delete_start_date'] ) ? esc_sql( $data['delete_start_date'] ) : '';
        $delete_end_date = isset( $data['delete_end_date'] ) ? esc_sql( $data['delete_end_date'] ) : '';
        $limit_user = isset( $data['limit_user'] ) ? absint( $data['limit_user'] ) : '';
        $date_type = isset( $data['date_type'] ) ? esc_sql( $data['date_type'] ) : 'custom_date';
        $input_days = isset( $data['input_days'] ) ? esc_sql( $data['input_days'] ) : '';
        if( $date_type === 'older_than') {
            $delete_start_date = $delete_end_date = '';
            if( $input_days === "0" || $input_days > 0){
                $delete_end_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
            }
        } else if( $date_type === 'within_last') {
            $delete_start_date = $delete_end_date = '';
            if( $input_days === "0" || $input_days > 0){
                $delete_start_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
            }
        } else if( $date_type === 'onemonth' || $date_type === 'sixmonths' || $date_type === 'oneyear' || $date_type === 'twoyear' ) {
            $delete_end_date = gmdate( 'Y-m-d', strtotime( current_time('Y-m-d') ) );
            if( $date_type === 'onemonth' ){
                $delete_start_date = gmdate('Y-m-d', strtotime("-30 days", strtotime(current_time('Y-m-d'))));
            }elseif( $date_type === 'sixmonths' ){
                $delete_start_date = gmdate('Y-m-d', strtotime("-6 months", strtotime(current_time('Y-m-d'))));
            }elseif( $date_type === 'oneyear' ){
                $delete_start_date = gmdate('Y-m-d', strtotime("-1 year", strtotime(current_time('Y-m-d'))));
            }elseif( $date_type === 'twoyear' ){
                $delete_start_date = gmdate('Y-m-d', strtotime("-2 years", strtotime(current_time('Y-m-d'))));
            }
        }

        // By Usermeta.
        $user_meta_key =  isset( $data['user_meta_key'] ) ? esc_sql( $data['user_meta_key'] ) : '';
        $user_meta_value =  isset( $data['user_meta_value'] ) ? esc_sql( $data['user_meta_value'] ) : '';
        $user_meta_compare =  isset( $data['user_meta_compare'] ) ? $data['user_meta_compare'] : 'equal_to_str';
            
        // By Useremail.
        $user_email =  isset( $data['user_email'] ) ? esc_sql( $data['user_email'] ) : '';
        $user_email_compare =  isset( $data['user_email_compare'] ) ? $data['user_email_compare'] : 'equal_to_str';

        // Query Generation.
        $query = "SELECT DISTINCT $wpdb->users.ID FROM $wpdb->users ";

        if ( $user_meta_key != '' && $user_meta_compare != '' && $user_meta_value != '' ) {
            $query .= " INNER JOIN $wpdb->usermeta ON( $wpdb->users.ID = $wpdb->usermeta.user_id )";
        }

        if( !empty( $delete_user_roles ) ){
            $query .= " INNER JOIN $wpdb->usermeta AS mt_roles ON ( $wpdb->users.ID = mt_roles.user_id )";
        }

        $query .= ' WHERE 1=1 ';

        if ( $user_meta_key != '' && $user_meta_compare != '' && $user_meta_value != '' ) {
            $query .= " AND ( $wpdb->usermeta.meta_key = '" . $user_meta_key. "'";
            switch ( $user_meta_compare ) {
                case 'equal_to_str':
                    $query .= " AND $wpdb->usermeta.meta_value = '{$user_meta_value}' )"; 
                    break;

                case 'notequal_to_str':
                    $query .= " AND $wpdb->usermeta.meta_value != '{$user_meta_value}' )"; 
                    break;

                case 'like_str':
                    $query .= " AND $wpdb->usermeta.meta_value LIKE '%{$user_meta_value}%' )"; 
                    break;

                case 'notlike_str':
                    $query .= " AND $wpdb->usermeta.meta_value NOT LIKE '%{$user_meta_value}%' )"; 
                    break;

                case 'equal_to_date':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS DATE) = '{$user_meta_value}' )"; 
                    break;

                case 'notequal_to_date':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS DATE) != '{$user_meta_value}' )"; 
                    break;

                case 'lessthen_date':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS DATE) < '{$user_meta_value}' )"; 
                    break;
                    
                case 'lessthenequal_date':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS DATE) <= '{$user_meta_value}' )"; 
                    break;
                
                case 'greaterthen_date':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS DATE) > '{$user_meta_value}' )"; 
                    break;

                case 'greaterthenequal_date':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS DATE) >= '{$user_meta_value}' )"; 
                    break;

                case 'equal_to_number':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS SIGNED) = '{$user_meta_value}' )"; 
                    break;

                case 'notequal_to_number':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS SIGNED) != '{$user_meta_value}' )"; 
                    break;

                case 'lessthen_number':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS SIGNED) < '{$user_meta_value}' )"; 
                    break;
                    
                case 'lessthenequal_number':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS SIGNED) <= '{$user_meta_value}' )"; 
                    break;
                
                case 'greaterthen_number':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS SIGNED) > '{$user_meta_value}' )"; 
                    break;

                case 'greaterthenequal_number':
                    $query .= " AND CAST($wpdb->usermeta.meta_value AS SIGNED) >= '{$user_meta_value}' )"; 
                    break;

                default:
                    $query .= "  AND $wpdb->usermeta.meta_value = '{$user_meta_value}' )";
                    break;
            }
        }

        if ( !empty( $user_email ) && !empty( $user_email_compare ) ) {
            $user_email = preg_replace('/\s+/', '', explode( ",", str_replace( '\r\n', '', $user_email ) ) );

            if( count( $user_email ) > 1 ){
                $imp = "'" . implode( "','", $user_email ) . "'";
                switch ( $user_email_compare ) {
                    case 'equal_to_str':
                        $query .= " AND $wpdb->users.user_email IN ( $imp )";
                        break;

                    case 'notequal_to_str':
                        $query .= " AND $wpdb->users.user_email NOT IN ( $imp )";
                        break;

                    default:
                        $query .= " AND $wpdb->users.user_email IN ( $imp )";
                        break;
                }
            }else{
                $imp = implode( ",", $user_email );
                switch ( $user_email_compare ) {
                    case 'equal_to_str':
                        $query .= " AND ( $wpdb->users.user_email = '{$imp}' )"; 
                        break;

                    case 'notequal_to_str':
                        $query .= " AND ( $wpdb->users.user_email != '{$imp}' )"; 
                        break;

                    default:
                        $query .= "  AND ( $wpdb->users.user_email = '{$imp}' )";
                        break;
                }
            }
        }

        if( !empty( $delete_user_roles ) ){
            $subquery = array();
            foreach ($delete_user_roles as $delete_user_role ) {
                $subquery[] = "( mt_roles.meta_key = '{$wpdb->prefix}capabilities' AND mt_roles.meta_value LIKE '%".'\"'.$delete_user_role.'\"%'."' )";
            }
            $subquery = implode( ' OR ',  $subquery );
            $query .= " AND ( {$subquery} )";
        }

        if( $delete_start_date != ''){
            $query .= " AND ( $wpdb->users.user_registered >= '{$delete_start_date} 00:00:00' )";
        }
        if( $delete_end_date != ''){
            $query .= " AND ( $wpdb->users.user_registered <= '{$delete_end_date} 23:59:59' )";
        }

        $query .= " AND $wpdb->users.ID NOT IN ( ".get_current_user_id()." )";

        if( !empty( $limit_user ) ){
            if( is_numeric( $limit_user ) ){
                $query .= " ORDER BY $wpdb->users.ID ASC LIMIT " . $limit_user;    
            }
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
        $users = $wpdb->get_col( $query );
        return $users;
    }

    /**
     * Do Delete operation on users.
     *
     * @access public
     * @since 1.0
     * @param array $data Posts Id.
     * @return array | deleted posts count.
     */
    public function do_delete_users( $user_ids = array(), $reassign_user = '') {
        $user_delete_count = 0;
        require_once(ABSPATH.'wp-admin/includes/user.php' );
        $current_user = wp_get_current_user();
        $user_ids = array_diff( $user_ids, array( $current_user->ID ) );
        if ( ! empty( $user_ids ) ){
            foreach ($user_ids as $user_id ) {
                if( $reassign_user != '' && $reassign_user > 0 ){
                    wp_delete_user( $user_id , $reassign_user );    
                }else{
                    wp_delete_user( $user_id );
                }
                
            }
            $user_delete_count = count( $user_ids );

        }
        return $user_delete_count;
    }

    /**
    * Get Comment count to be deleted.
    *
    * @access public
    * @since 1.1.0
    * @param array $data Delete comment form data.
    * @return comment count.
    */
    public function get_delete_comment_count( $data = array() ){
        if( wpbd_is_pro() && class_exists('WPBD_Delete_API_Pro', false) ){
            $wpbdpro = new WPBD_Delete_API_Pro();
            return $wpbdpro->get_delete_comment_count( $data );
        }
        global $wpdb;
        $comment_delete_count = 0;
        $delete_comment_status = isset( $data['delete_comment_status'] ) ? $data['delete_comment_status'] : array();
        $delete_comment_status = array_map('esc_sql', $delete_comment_status );
        $delete_start_date = isset( $data['delete_start_date'] ) ? esc_sql( $data['delete_start_date'] ) : '';
        $delete_end_date = isset( $data['delete_end_date'] ) ? esc_sql( $data['delete_end_date'] ) : '';
        $date_type = isset( $data['date_type'] ) ? esc_sql( $data['date_type'] ) : 'custom_date';
        $input_days = isset( $data['input_days'] ) ? esc_sql( $data['input_days'] ) : '';
        $limit_comment = isset( $data['limit_comment'] ) ? esc_sql( $data['limit_comment'] ) : 5000;
        if( $date_type === 'older_than') {
            $delete_start_date = $delete_end_date = '';
            if( $input_days === "0" || $input_days > 0){
                $delete_end_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
            }
        } else if( $date_type === 'within_last') {
            $delete_start_date = $delete_end_date = '';
            if( $input_days === "0" || $input_days > 0){
                $delete_start_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
            }
        }

        if ( ! empty( $data ) ){

            $temp_delete_query = array();

            if( ! empty( $delete_comment_status ) ){
                foreach ( $delete_comment_status as $comment_status ) {

                    switch( $comment_status ) {            
                        case 'moderated':
                            $temp_delete_query[] = "comment_approved = '0'";
                            break;
                        
                        case 'spam':
                            $temp_delete_query[] = "comment_approved = 'spam'";
                            break;
                        
                        case 'trash':
                            $temp_delete_query[] = "comment_approved = 'trash' OR comment_approved = 'post-trashed'";
                            break;

                        case 'approved':
                            $temp_delete_query[] = "comment_approved = '1'";
                            break;
                    }
                    
                }
                if( !empty( $temp_delete_query ) ) {
                    $delete_comment_query = "SELECT comment_ID FROM $wpdb->comments WHERE 1=1";
                    $delete_comment_query .= " AND (" . implode( " OR ", $temp_delete_query ) . ")";
                }
            }
 
            if( $delete_start_date != ''){
                $delete_comment_query .= " AND ( comment_date >= '{$delete_start_date} 00:00:00' )";
            }
            if( $delete_end_date != ''){
                $delete_comment_query .= " AND ( comment_date <= '{$delete_end_date} 23:59:59' )";
            }
            if( is_numeric( $limit_comment ) ){
                $delete_comment_query .= " LIMIT " . $limit_comment;
            }
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $comment_delete_count = $wpdb->query( $delete_comment_query );
        }
        return $comment_delete_count;
    }

    /**
     * Do Delete operation on comments.
     *
     * @access public
     * @since 1.1.0
     * @param array $data $_POST.
     * @return deleted comments count.
     */
    public function do_delete_comments( $data = array() ) {
        global $wpdb;
        if( wpbd_is_pro() && class_exists('WPBD_Delete_API_Pro', false) ){
            $wpbdpro = new WPBD_Delete_API_Pro();
            return $wpbdpro->do_delete_comments( $data );
        }

        $comment_delete_count = 0;
        $delete_comment_status = isset( $data['delete_comment_status'] ) ? $data['delete_comment_status'] : array();
        $delete_comment_status = array_map('esc_sql', $delete_comment_status );
        $delete_start_date = isset( $data['delete_start_date'] ) ? esc_sql( $data['delete_start_date'] ) : '';
        $delete_end_date = isset( $data['delete_end_date'] ) ? esc_sql( $data['delete_end_date'] ) : '';
        $date_type = isset( $data['date_type'] ) ? esc_sql( $data['date_type'] ) : 'custom_date';
        $input_days = isset( $data['input_days'] ) ? esc_sql( $data['input_days'] ) : '';
        $limit_comment = isset( $data['limit_comment'] ) ? esc_sql( $data['limit_comment'] ) : 5000;
        if( $date_type === 'older_than') {
            $delete_start_date = $delete_end_date = '';
            if( $input_days === "0" || $input_days > 0){
                $delete_end_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
            }
        } else if( $date_type === 'within_last') {
            $delete_start_date = $delete_end_date = '';
            if( $input_days === "0" || $input_days > 0){
                $delete_start_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
            }
        }

        if ( ! empty( $data ) ){

            $temp_delete_query = array();

            if( ! empty( $delete_comment_status ) ){
                foreach ( $delete_comment_status as $comment_status ) {

                    switch( $comment_status ) {            
                        case 'moderated':
                            $temp_delete_query[] = "comment_approved = '0'";
                            break;
                        
                        case 'spam':
                            $temp_delete_query[] = "comment_approved = 'spam'";
                            break;
                        
                        case 'trash':
                            $temp_delete_query[] = "comment_approved = 'trash' OR comment_approved = 'post-trashed'";
                            break;
                            break;

                        case 'approved':
                            $temp_delete_query[] = "comment_approved = '1'";
                            break;
                    }
                    
                }
                if( !empty( $temp_delete_query ) ) {
                    $delete_comment_query = "DELETE FROM $wpdb->comments WHERE 1=1";
                    $delete_comment_query .= " AND (" . implode( " OR ", $temp_delete_query ) . ")";
                }
            }
 
            if( $delete_start_date != ''){
                $delete_comment_query .= " AND ( comment_date >= '{$delete_start_date} 00:00:00' )";
            }
            if( $delete_end_date != ''){
                $delete_comment_query .= " AND ( comment_date <= '{$delete_end_date} 23:59:59' )";
            }
            if( is_numeric( $limit_comment ) ){
                $delete_comment_query .= " LIMIT " . $limit_comment;
            }

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $comment_delete_count = $wpdb->query( $delete_comment_query );
            delete_transient('wc_count_comments');
        }
        return $comment_delete_count;
    }

    /**
     * Get postmeta Ids.
     *
     * @access public
     * @since 1.1.0
     * @param array $data Delete postmeta form data.
     * @return array | Posts Id.
     */
    public function get_delete_postmeta_ids( $data = array() ) {
        global $wpdb;
        if( wpbd_is_pro() && class_exists('WPBD_Delete_API_Pro', false) ){
            $wpbdpro = new WPBD_Delete_API_Pro();
            return $wpbdpro->get_delete_postmeta_ids( $data );
        }
        if( ! empty( $data['meta_post_type'] ) &&  ! empty( $data['custom_field_key'] ) ){

            $post_type = isset( $data['meta_post_type'] ) ? esc_sql( $data['meta_post_type'] ) : '';
            $delete_start_date = isset( $data['delete_start_date'] ) ? esc_sql( $data['delete_start_date'] ) : '';
            $delete_end_date = isset( $data['delete_end_date'] ) ? esc_sql( $data['delete_end_date'] ) : '';
            $date_type = isset( $data['date_type'] ) ? esc_sql( $data['date_type'] ) : 'custom_date';
            $input_days = isset( $data['input_days'] ) ? esc_sql( $data['input_days'] ) : '';
            if( $date_type === 'older_than') {
                $delete_start_date = $delete_end_date = '';
                if( $input_days === "0" || $input_days > 0){
                    $delete_end_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
                }
            } else if( $date_type === 'within_last') {
                $delete_start_date = $delete_end_date = '';
                if( $input_days === "0" || $input_days > 0){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
                }
            }

            // Post Query Generation.
            $postquery = "SELECT DISTINCT $wpdb->posts.ID FROM $wpdb->posts ";

            $postquery .= " WHERE $wpdb->posts.post_type = '" . $post_type . "'";  

            if( $delete_start_date != ''){
                $postquery .= " AND ( $wpdb->posts.post_date >= '{$delete_start_date} 00:00:00' )";
            }
            if( $delete_end_date != ''){
                $postquery .= " AND ( $wpdb->posts.post_date <= '{$delete_end_date} 23:59:59' )";
            }

            $metaQuery = $metaWhere = "";
            $metaWhere = $this->get_meta_where( $data );

            if( $metaWhere != '' ){
                $metaQuery = "SELECT post_id, meta_key FROM $wpdb->postmeta WHERE post_id IN ( {$postquery} ) AND ( {$metaWhere} )";    
            }else{
                return array();   
            }
            
            if( $metaQuery == '' ){
                return array();
            }
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $meta_results = $wpdb->get_results( $metaQuery );
            return $meta_results;
        }else{
            return array();
        }
    }

    /**
     * Do Delete operation on postmetas.
     *
     * @access public
     * @since 1.1.0
     * @param array $data Postmeta results
     * @return array | deleted postmetas count.
     */
    public function do_delete_postmetas( $meta_results = array() ) {
        $post_delete_count = 0;

        if ( ! empty( $meta_results ) ){

            foreach ($meta_results as $meta ) {
                $post_id = intval( $meta->post_id );
                if( $post_id === 0 ) {
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                    $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $post_id, $meta->meta_key ) );
                } else {
                    delete_post_meta( $post_id, $meta->meta_key );
                }
            }
            $post_delete_count = number_format_i18n( sizeof( $meta_results ) );

        }
        return $post_delete_count;
    }

    /**
     * Get commentmeta Ids.
     *
     * @access public
     * @since 1.1.0
     * @param array $data Delete commentmeta form data.
     * @return array | Meta results.
     */
    public function get_delete_commentmeta_ids( $data = array() ) {
        global $wpdb;
        if( $data['custom_field_key'] != '' ){

            $delete_start_date = isset( $data['delete_start_date'] ) ? esc_sql( $data['delete_start_date'] ) : '';
            $delete_end_date = isset( $data['delete_end_date'] ) ? esc_sql( $data['delete_end_date'] ) : '';
            $date_type = isset( $data['date_type'] ) ? esc_sql( $data['date_type'] ) : 'custom_date';
            $input_days = isset( $data['input_days'] ) ? esc_sql( $data['input_days'] ) : '';
            if( $date_type === 'older_than') {
                $delete_start_date = $delete_end_date = '';
                if( $input_days === "0" || $input_days > 0){
                    $delete_end_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
                }
            } else if( $date_type === 'within_last') {
                $delete_start_date = $delete_end_date = '';
                if( $input_days === "0" || $input_days > 0){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
                }
            } else if( $date_type === 'onemonth' || $date_type === 'sixmonths' || $date_type === 'oneyear' || $date_type === 'twoyear' ) {
                $delete_end_date = gmdate( 'Y-m-d', strtotime( current_time('Y-m-d') ) );
                if( $date_type === 'onemonth' ){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-30 days", strtotime(current_time('Y-m-d'))));
                }elseif( $date_type === 'sixmonths' ){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-6 months", strtotime(current_time('Y-m-d'))));
                }elseif( $date_type === 'oneyear' ){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-1 year", strtotime(current_time('Y-m-d'))));
                }elseif( $date_type === 'twoyear' ){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-2 years", strtotime(current_time('Y-m-d'))));
                }
            }

            // Post Query Generation.
            $commentquery = "SELECT DISTINCT $wpdb->comments.comment_ID FROM $wpdb->comments WHERE 1 = 1";

            if( $delete_start_date != ''){
                $commentquery .= " AND ( $wpdb->comments.comment_date >= '{$delete_start_date} 00:00:00' )";
            }
            if( $delete_end_date != ''){
                $commentquery .= " AND ( $wpdb->comments.comment_date <= '{$delete_end_date} 23:59:59' )";
            }
           
            $metaQuery = $metaWhere = "";
            $metaWhere = $this->get_meta_where( $data );

            if( $metaWhere != '' ){
                $metaQuery = "SELECT comment_id, meta_key FROM $wpdb->commentmeta WHERE comment_id IN ( {$commentquery} ) AND ( {$metaWhere} )";    
            }else{
                return array();   
            }

            if( $metaQuery == '' ){
                return array();
            }
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $meta_results = $wpdb->get_results( $metaQuery );
            return $meta_results;
        }else{
            return array();
        }
    }

    /**
     * Do Delete operation on commentmetas.
     *
     * @access public
     * @since 1.1.0
     * @param array $data commentmeta results
     * @return array | deleted commentmeta count.
     */
    public function do_delete_commentmetas( $meta_results = array() ) {
        $post_delete_count = 0;

        if ( ! empty( $meta_results ) ){

            foreach ($meta_results as $meta ) {
                $comment_id = intval( $meta->comment_id );
                if( $comment_id === 0 ) {
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                    $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->commentmeta WHERE comment_id = %d AND meta_key = %s", $comment_id, $meta->meta_key ) );
                } else {
                    delete_comment_meta( $comment_id, $meta->meta_key );
                }
            }
            $post_delete_count = number_format_i18n( sizeof( $meta_results ) );

        }
        return $post_delete_count;
    }

    /**
     * Get usermeta Ids.
     *
     * @access public
     * @since 1.1.0
     * @param array $data Delete usermeta form data.
     * @return array | Meta results.
     */
    public function get_delete_usermeta_ids( $data = array() ) {
        global $wpdb;
        if( $data['custom_field_key'] != '' && !empty( $data['delete_user_roles'] ) ){
            
            $delete_user_roles = isset( $data['delete_user_roles'] ) ? $data['delete_user_roles'] : array();
            $delete_start_date = isset( $data['delete_start_date'] ) ? esc_sql( $data['delete_start_date'] ) : '';
            $delete_end_date = isset( $data['delete_end_date'] ) ? esc_sql( $data['delete_end_date'] ) : '';
            $date_type = isset( $data['date_type'] ) ? esc_sql( $data['date_type'] ) : 'custom_date';
            $input_days = isset( $data['input_days'] ) ? esc_sql( $data['input_days'] ) : '';
            if( $date_type === 'older_than') {
                $delete_start_date = $delete_end_date = '';
                if( $input_days === "0" || $input_days > 0){
                    $delete_end_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
                }
            } else if( $date_type === 'within_last') {
                $delete_start_date = $delete_end_date = '';
                if( $input_days === "0" || $input_days > 0){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-{$input_days} days", strtotime(current_time('Y-m-d'))));
                }
            } else if( $date_type === 'onemonth' || $date_type === 'sixmonths' || $date_type === 'oneyear' || $date_type === 'twoyear' ) {
                $delete_end_date = gmdate( 'Y-m-d', strtotime( current_time('Y-m-d') ) );
                if( $date_type === 'onemonth' ){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-30 days", strtotime(current_time('Y-m-d'))));
                }elseif( $date_type === 'sixmonths' ){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-6 months", strtotime(current_time('Y-m-d'))));
                }elseif( $date_type === 'oneyear' ){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-1 year", strtotime(current_time('Y-m-d'))));
                }elseif( $date_type === 'twoyear' ){
                    $delete_start_date = gmdate('Y-m-d', strtotime("-2 years", strtotime(current_time('Y-m-d'))));
                }
            }

            $userquery = "SELECT DISTINCT $wpdb->users.ID FROM $wpdb->users ";

            if( !empty( $delete_user_roles ) ){
                $i = 1;
                foreach ($delete_user_roles as $delete_user_role ) {
                    $userquery .= " INNER JOIN $wpdb->usermeta AS mt{$i} ON ( $wpdb->users.ID = mt{$i}.user_id )";    
                    $i++;
                }
            }
            $userquery .= " WHERE 1 = 1";
            if( !empty( $delete_user_roles ) ){
                $j = 1;
                $subquery = array();
                foreach ($delete_user_roles as $delete_user_role ) {
                    $subquery[]= "( mt{$j}.meta_key = '{$wpdb->prefix}capabilities' AND mt{$j}.meta_value LIKE '%\"{$delete_user_role}\"%' )";
                    $j++;
                }
                $subquery = implode( ' OR ',  $subquery );
                $userquery .= " AND ( {$subquery} )";
            }

            // User Query Generation.
            if( $delete_start_date != '' || $delete_end_date != '' ){
                
                if( $delete_start_date != '' ){
                    $userquery .= " AND ( $wpdb->users.user_registered >= '{$delete_start_date} 00:00:00' )";
                }
                if( $delete_end_date != '' ){
                    $userquery .= " AND ( $wpdb->users.user_registered <= '{$delete_end_date} 23:59:59' )";
                }
            }
           
            $metaQuery = $metaWhere = "";
            $metaWhere = $this->get_meta_where( $data );

            if( $metaWhere != '' ){
                    $metaQuery = "SELECT user_id, meta_key FROM $wpdb->usermeta WHERE user_id IN ( {$userquery} ) AND ( {$metaWhere} )";        
               
            }else{
                return array();   
            }

            if( $metaQuery == '' ){
                return array();
            }
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $meta_results = $wpdb->get_results( $metaQuery );
            return $meta_results;

        }else{
            return array();
        }
    }

    /**
     * Do Delete operation on usermetas.
     *
     * @access public
     * @since 1.1.0
     * @param array $data usermeta results
     * @return array | deleted usermeta count.
     */
    public function do_delete_usermetas( $meta_results = array() ) {
        $usermeta_delete_count = 0;
        if ( ! empty( $meta_results ) ){

            foreach ($meta_results as $meta ) {
                $user_id = intval( $meta->user_id );
                if( $user_id === 0 ) {
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                    $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s", $user_id, $meta->meta_key ) );
                } else {
                    delete_user_meta( $user_id, $meta->meta_key );
                }
            }
            $usermeta_delete_count = number_format_i18n( sizeof( $meta_results ) );

        }
        return $usermeta_delete_count;
    }

    /**
     * Get delete Term count.
     *
     * @access public
     * @since 1.1.0
     * @param array $data Delete terms form data.
     * @return int | Term count.
     */
    public function get_delete_term_count( $data = array() ){

        if( $data['delete_post_type'] == '' &&  $data['post_taxonomy'] == '' ){
            return 0; 
        }

        return $numTerms = wp_count_terms( $data['post_taxonomy'] );

    }

    /**
     * Do Delete operation on taxonomy terms.
     *
     * @access public
     * @since 1.1.0
     * @param array $data Delete terms form data.
     * @return array | deleted terms count.
     */
    public function do_delete_terms( $data = array() ) {
        $terms_delete_count = 0;
        if ( ! empty( $data ) ){

            if( $data['delete_post_type'] == '' ||  $data['post_taxonomy'] == '' ){
                return $terms_delete_count;
            }

            $terms = get_terms( array( 'taxonomy'   => $data['post_taxonomy'], 'fields' => 'ids', 'hide_empty' => false ) );
            foreach ( $terms as $value ) {
               wp_delete_term( $value, $data['post_taxonomy'] );
            }
            $terms_delete_count = number_format_i18n( sizeof( $terms ) );
        }
        return $terms_delete_count;
    }

    /**
     * Generate WHERE condition for meta delete.
     *
     * @access public
     * @since 1.1.0
     * @param array $data Delete form data.
     * @return string | WHERE Condition for query.
     */
    function get_meta_where( $data ){
        // Costomfields
        $custom_field_key =  ( $data['custom_field_key'] ) ? esc_sql( $data['custom_field_key'] ) : '';
        $custom_field_value =  ( $data['custom_field_value'] ) ? esc_sql( $data['custom_field_value'] ) : '';
        $custom_field_compare =  ( $data['custom_field_compare'] ) ? $data['custom_field_compare'] : 'equal_to_str';
        $metaWhere = '';

        if ( $custom_field_key != '' ) {
                $metaWhere .= "( meta_key = '" . $custom_field_key. "'";

                if( $custom_field_compare != '' && $custom_field_value != '' ){
                switch ( $custom_field_compare ) {
                    case 'equal_to_str':
                        $metaWhere .= " AND meta_value = '{$custom_field_value}' )"; 
                        break;

                    case 'notequal_to_str':
                        $metaWhere .= " AND meta_value != '{$custom_field_value}' )"; 
                        break;

                    case 'like_str':
                        $metaWhere .= " AND meta_value LIKE '%{$custom_field_value}%' )"; 
                        break;

                    case 'notlike_str':
                        $metaWhere .= " AND meta_value NOT LIKE '%{$custom_field_value}%' )"; 
                        break;

                    case 'equal_to_date':
                        $metaWhere .= " AND CAST(meta_value AS DATE) = '{$custom_field_value}' )"; 
                        break;

                    case 'notequal_to_date':
                        $metaWhere .= " AND CAST(meta_value AS DATE) != '{$custom_field_value}' )"; 
                        break;

                    case 'lessthen_date':
                        $metaWhere .= " AND CAST(meta_value AS DATE) < '{$custom_field_value}' )"; 
                        break;
                        
                    case 'lessthenequal_date':
                        $metaWhere .= " AND CAST(meta_value AS DATE) <= '{$custom_field_value}' )"; 
                        break;
                    
                    case 'greaterthen_date':
                        $metaWhere .= " AND CAST(meta_value AS DATE) > '{$custom_field_value}' )"; 
                        break;

                    case 'greaterthenequal_date':
                        $metaWhere .= " AND CAST(meta_value AS DATE) >= '{$custom_field_value}' )"; 
                        break;

                    case 'equal_to_number':
                        $metaWhere .= " AND CAST(meta_value AS SIGNED) = '{$custom_field_value}' )"; 
                        break;

                    case 'notequal_to_number':
                        $metaWhere .= " AND CAST(meta_value AS SIGNED) != '{$custom_field_value}' )"; 
                        break;

                    case 'lessthen_number':
                        $metaWhere .= " AND CAST(meta_value AS SIGNED) < '{$custom_field_value}' )"; 
                        break;
                        
                    case 'lessthenequal_number':
                        $metaWhere .= " AND CAST(meta_value AS SIGNED) <= '{$custom_field_value}' )"; 
                        break;
                    
                    case 'greaterthen_number':
                        $metaWhere .= " AND CAST(meta_value AS SIGNED) > '{$custom_field_value}' )"; 
                        break;

                    case 'greaterthenequal_number':
                        $metaWhere .= " AND CAST(meta_value AS SIGNED) >= '{$custom_field_value}' )"; 
                        break;

                    default:
                        $metaWhere .= "  AND meta_value = '{$custom_field_value}' )";
                        break;     
                    }               
                }else{
                    $metaWhere .=" )";
                }
        }
        return $metaWhere;
    }

    /**
     * Get Plugin array
     *
     * @since 1.1.0
     * @return array
     */
    public function get_xyuls_themes_plugins(){
        return array(
            'wp-event-aggregator' => array( 'plugin_name' => esc_html__( 'WP Event Aggregator', 'wp-bulk-delete' ), 'description' => 'WP Event Aggregator: Easy way to import Facebook Events, Eventbrite events, MeetUp events into your WordPress Event Calendar.' ),
            'import-facebook-events' => array( 'plugin_name' => esc_html__( 'Import Social Events', 'wp-bulk-delete' ), 'description' => 'Import Facebook events into your WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.' ),
            'import-eventbrite-events' => array( 'plugin_name' => esc_html__( 'Import Eventbrite Events', 'wp-bulk-delete' ), 'description' => 'Import Eventbrite Events into WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.' ),
            'import-meetup-events' => array( 'plugin_name' => esc_html__( 'Import Meetup Events', 'wp-bulk-delete' ), 'description' => 'Import Meetup Events allows you to import Meetup (meetup.com) events into your WordPress site effortlessly.' ),
            'event-schema' => array( 'plugin_name' => esc_html__( 'Event Schema / Structured Data', 'wp-bulk-delete' ), 'description' => 'Automatically Google Event Rich Snippet Schema Generator. This plug-in generates complete JSON-LD based schema (structured data for Rich Snippet) for events.' ),
            'wp-smart-import' => array( 'plugin_name' => esc_html__( 'WP Smart Import : Import any XML File to WordPress', 'wp-bulk-delete' ), 'description' => 'The most powerful solution for importing any CSV files to WordPress. Create Posts and Pages any Custom Posttype with content from any CSV file.' ),
        );
    }
}
