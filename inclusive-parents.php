<?php
/*
Plugin Name: Inclusive Parents
Description: Allow draft, private, scheduled, and password-protected pages to be selected as parents and added to menus.
Author: sillybean
Version: 1.1
Author URI: http://stephanieleary.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Enable private and password-protected pages in page lists and dropdowns
 *
 * @param array $args
 * @return array $args
 */
function scl_list_pages_args( $args ) {
    $args['post_status'] = array( 'publish', 'private', 'password' );
    return $args;
}

add_filter( 'wp_page_menu_args', 'scl_list_pages_args' );
add_filter( 'widget_pages_args', 'scl_list_pages_args' );


/**
 * Add private/draft/future/pending pages to parent dropdown in page attributes metabox, Quick Edit, and Bulk Edit
 *
 * @param array $dropdown_args
 * @param object $post (Optional)
 * @return array $dropdown_args
 */
function scl_page_attributes_metabox_add_parents( $dropdown_args, $post = NULL ) {
	$dropdown_args['post_status'] = array( 'publish', 'draft', 'pending', 'future', 'private' );
	return $dropdown_args;
}

add_filter( 'page_attributes_dropdown_pages_args', 'scl_page_attributes_metabox_add_parents', 10, 2 ); 
add_filter( 'quick_edit_dropdown_pages_args', 'scl_page_attributes_metabox_add_parents', 10 );

/**
 * Add (Status) to titles in page parent dropdowns
 *
 * @param string $title
 * @param object $page
 * @return string $title
 */
function scl_page_parent_dropdown_status_label( $title, $page ) {
	if ( !is_admin() )
		return $title;
		
	$post_status = $page->post_status;
	if ( $post_status !== __( 'publish' ) ) {
		$status = get_post_status_object( $post_status );
		$title .= " ($status->label)";
	}
	return $title;
}

add_filter( 'list_pages', 'scl_page_parent_dropdown_status_label', 10, 2 );


/**
 * Add (Status) to titles in nav menu page checklists
 *
 * @param string $title
 * @param object $page
 * @return string $title
 */
function scl_menu_checklist_status_label( $title, $page_id ) {
	if ( empty( $page_id ) )
		return $title;
		
	if ( is_admin() && 'nav-menus' == get_current_screen()->base ) {	
		$post_status = get_post_status( $page_id );
		if ( $post_status !== __( 'publish' ) ) {
			$status = get_post_status_object( $post_status );
			$title .= " ($status->label)";
		}
	}
	return $title;
}

add_filter( 'the_title', 'scl_menu_checklist_status_label', 10, 2 );

/**
 * Filter pages metabox on menu admin screen to include private pages.
 *
 * @param object $query
 * @return object $query
 */
function scl_menu_screen_add_private_pages( $query ) {
	if ( is_admin() && 'nav-menus' == get_current_screen()->base ) {
		$query->set( 'post_status', array( 'publish', 'private', 'password' ) );
	}	
	return $query;
}

add_filter( 'pre_get_posts', 'scl_menu_screen_add_private_pages' );