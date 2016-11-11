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