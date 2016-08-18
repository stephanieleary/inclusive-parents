<?php
/*
Plugin Name: Inclusive Parents
Description: A tiny plugin to allow draft, private, scheduled, and password-protected pages to be selected as parents, displayed in menus, and included in page lists.
Author: sillybean
Version: 1.0.1
Author URI: http://stephanieleary.com
License: GPL2
Requires at least: 3.2
Tested up to: 4.6
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


/**
 * Enable unpublished pages in page lists and dropdowns
 *
 * @param array $args
 * @return array $args
 */
function scl_list_pages_args( $args ) {
    $args['post_status'] = array( 'publish', 'private' );
    return $args;
}

add_filter( 'wp_page_menu_args', 'scl_list_pages_args' );
add_filter( 'widget_pages_args', 'scl_list_pages_args' );

/**
 * Add private/draft/future/pending pages to parent dropdown in page attributes metabox and Quick Edit
 *
 * @param array $dropdown_args
 * @param object $post (Optional)
 * @return array $dropdown_args
 */
function scl_page_attributes_metabox_add_parents( $dropdown_args, $post = NULL ) {
	$dropdown_args['post_status'] = array('publish', 'draft', 'pending', 'future', 'private');
	return $dropdown_args;
}

add_filter( 'page_attributes_dropdown_pages_args', 'scl_page_attributes_metabox_add_parents', 10, 2 ); 
add_filter( 'quick_edit_dropdown_pages_args', 'scl_page_attributes_metabox_add_parents', 10);

/**
 * Add (status) to titles in page parent dropdowns
 *
 * @param string $title
 * @param object $page
 * @return string $title
 */
function scl_page_parent_status_filter( $title, $page ) {
	$status = $page->post_status;
	if ($status !== __('publish'))
		$title .= " ($status)";
	return $title;
}

add_filter( 'list_pages', 'scl_page_parent_status_filter', 10, 2);

/**
 * Filter public page queries to include privately published ones. 
 * Filter pages metabox on menu admin screen to include all built-in statuses.
 *
 * @param object $query
 * @return object $query
 */
function scl_private_page_query_filter($query) {
	if ( is_admin() ) {
		$screen = get_current_screen();
		if ( 'nav-menus' == $screen->base )
			$query->set( 'post_status', 'publish,private,future,pending,draft' );
	}
	else {
		$query->set( 'post_status', 'publish,private' );
	}	
	return $query;
}

add_filter('pre_get_posts', 'scl_private_page_query_filter');
