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
		global $wpdb;
		if( ! empty( $data['delete_post_type'] ) &&  ! empty( $data['delete_post_status'] ) ){

            $post_types = ( $data['delete_post_type'] ) ? $data['delete_post_type'] : array();
            if( ! is_array( $post_types ) ){
                $post_types = array( $post_types ); 
            }
            $post_types = array_map('esc_sql', $post_types );

            $post_status = ( $data['delete_post_status'] ) ? array_map('esc_sql', $data['delete_post_status'] ) : array();
            $delete_start_date = ( $data['delete_start_date'] ) ? esc_sql( $data['delete_start_date'] ) : '';
            $delete_end_date = ( $data['delete_end_date'] ) ? esc_sql( $data['delete_end_date'] ) : '';
            $delete_authors = ( $data['delete_authors'] ) ?  array_map( 'intval', $data['delete_authors'] ) : array();
            $delete_type = ( $data['delete_type'] )?$data['delete_type']:'trash';
            $limit_post = ( $data['limit_post'] ) ? absint( $data['limit_post'] ) : '';

            // BY Taxonomy.
            $post_taxonomy =  ( $data['post_taxonomy'] ) ?  esc_sql( $data['post_taxonomy'] ) : '';
            $post_taxonomy_terms =  ( $data['post_taxonomy_terms'] ) ? array_map( 'intval', $data['post_taxonomy_terms'] ) : array();

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

            if( !empty( $post_status ) ){
                $query .= " AND $wpdb->posts.post_status IN ( '" .  implode( "', '", $post_status ) . "' )";
            }
            if( $delete_start_date != ''){
                $query .= " AND $wpdb->posts.post_date >= '{$delete_start_date} 00:00:00'";
            }
            if( $delete_end_date != ''){
                $query .= " AND $wpdb->posts.post_date <= '{$delete_end_date} 23:59:59'";
            }

            if( !empty( $delete_authors ) ){
                $query .= " AND $wpdb->posts.post_author IN ( " . implode( ",", $delete_authors ). " )";
            }

            if( $limit_post != '' ){
                if( is_numeric( $limit_post ) ){
                    $query .= " LIMIT " . $limit_post;    
                }                
            }
            
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
	public function do_delete_posts( $post_ids = array(), $force_delete = 'false') {
		$post_delete_count = 0;

		if ( ! empty( $post_ids ) ){

			foreach ($post_ids as $post_id ) {
				wp_delete_post( $post_id, $force_delete );
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
                $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
                break;
            case 'auto_drafts':
                $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
                break;
            case 'trash':
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
                $posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
                if( $posts ) {
                    foreach ( $posts as $id ) {
                        wp_delete_post_revision( intval( $id ) );
                    }

                    $message = sprintf( __( '%s Revisions Cleaned up', 'wp-sweep' ), number_format_i18n( sizeof( $posts ) ) );
                }
                break;
            case 'auto_drafts':
                $posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
                if( $posts ) {
                    foreach ( $posts as $id ) {
                        wp_delete_post( intval( $id ), true );
                    }

                    $message = sprintf( __( '%s Auto Drafts Cleaned up', 'wp-sweep' ), number_format_i18n( sizeof( $posts ) ) );
                }
                break;
            case 'trash':
                $posts = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'trash' ) );
                if( $posts ) {
                    foreach ( $posts as $id ) {
                        wp_delete_post( $id, true );
                    }

                    $message = sprintf( __( '%s Trashed Posts Cleaned up', 'wp-sweep' ), number_format_i18n( sizeof( $posts ) ) );
                }
                break;
        }
        return $message;
    }
}


